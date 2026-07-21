<?php

namespace App\Services\Images;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class ImageProcessingService
{
    public function persist(
        ?UploadedFile $file,
        ?string $currentPath,
        string $directory,
        Closure $persist
    ): mixed {
        $newPath = null;

        try {
            if ($file !== null) {
                $newPath = $this->store($file, $directory);
            }

            return DB::transaction(function () use ($persist, $currentPath, $newPath) {
                $result = $persist($newPath);

                if (filled($currentPath) && filled($newPath) && $currentPath !== $newPath) {
                    DB::afterCommit(function () use ($currentPath) {
                        $this->delete($currentPath);
                    });
                }

                return $result;
            });
        } catch (Throwable $throwable) {
            if (filled($newPath)) {
                $this->delete($newPath);
            }

            throw $throwable;
        }
    }

    public function store(UploadedFile $file, string $directory, array $options = []): string
    {
        $directory = $this->normalizeDirectory($directory);
        $filename = Str::uuid()->toString() . '.webp';
        $relativePath = $directory . '/' . $filename;

        $targetPaths = $this->getPublicDestinationPaths($relativePath);
        $primaryPath = $targetPaths[0];

        $this->ensureDirectory(dirname($primaryPath));
        $this->convertToWebp($file->getPathname(), $primaryPath, $options);

        if (! File::exists($primaryPath) || File::size($primaryPath) === 0) {
            throw new RuntimeException('Image processing completed, but the output file was not created.');
        }

        @chmod($primaryPath, 0644);

        foreach (array_slice($targetPaths, 1) as $additionalPath) {
            $this->ensureDirectory(dirname($additionalPath));
            File::copy($primaryPath, $additionalPath);
            @chmod($additionalPath, 0644);
        }

        return $relativePath;
    }

    public function delete(?string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        $targetPaths = $this->getPublicDestinationPaths($path);
        $deleted = false;

        foreach ($targetPaths as $absolutePath) {
            if (File::exists($absolutePath)) {
                if (File::delete($absolutePath)) {
                    $deleted = true;
                }
            }
        }

        return $deleted;
    }

    private function getPublicDestinationPaths(string $relativePath): array
    {
        $relativePath = ltrim($relativePath, '/\\');
        $paths = [
            public_path($relativePath),
        ];

        $publicHtmlDir = base_path('../public_html');
        if (File::isDirectory($publicHtmlDir)) {
            $paths[] = $publicHtmlDir . '/' . $relativePath;
        }

        return array_values(array_unique($paths));
    }

    private function convertToWebp(string $inputPath, string $outputPath, array $options = []): void
    {
        $options['max_width'] = max(1, (int) ($options['max_width'] ?? config('image-processing.max_width', 1400)));
        $options['max_height'] = max(1, (int) ($options['max_height'] ?? config('image-processing.max_height', 1400)));
        $options['quality'] = $this->normalizeQuality((int) ($options['quality'] ?? config('image-processing.quality', 82)));
        $options['timeout'] = max(1, (int) ($options['timeout'] ?? config('image-processing.timeout', 120)));

        $processors = [
            new \App\Services\Images\Processors\ImagickProcessor(),
            new \App\Services\Images\Processors\GdProcessor(),
            new \App\Services\Images\Processors\NodeProcessor(),
        ];

        $failures = [];

        foreach ($processors as $processor) {
            if (!$processor->isSupported()) {
                continue;
            }

            try {
                $processor->process($inputPath, $outputPath, $options);
                return;
            } catch (Throwable $throwable) {
                $processorName = class_basename($processor);
                $failures[] = new RuntimeException(
                    "Image processing failed via {$processorName}: " . $throwable->getMessage(),
                    0,
                    $throwable
                );

                if (File::exists($outputPath)) {
                    File::delete($outputPath);
                }
            }
        }

        $details = collect($failures)
            ->map(fn (Throwable $failure) => $failure->getMessage())
            ->implode(' | ');

        throw new RuntimeException(
            $details === ''
                ? 'Image processing failed. No supported processors are available or configured.'
                : 'Image processing failed for all available processors: ' . $details,
            0,
            $failures[0] ?? null
        );
    }

    private function ensureDirectory(string $directory): void
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            @chmod($directory, 0755);
        }
    }

    private function normalizeDirectory(string $directory): string
    {
        return trim(str_replace('\\', '/', $directory), '/');
    }

    private function normalizeQuality(int $quality): int
    {
        return max(80, min(85, $quality));
    }
}
