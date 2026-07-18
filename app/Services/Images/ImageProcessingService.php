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
        $absolutePath = public_path($relativePath);

        $this->ensureDirectory(dirname($absolutePath));
        $this->convertToWebp($file->getPathname(), $absolutePath, $options);

        if (! File::exists($absolutePath) || File::size($absolutePath) === 0) {
            throw new RuntimeException('Image processing completed, but the output file was not created.');
        }

        return $relativePath;
    }

    public function delete(?string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        $absolutePath = public_path(ltrim($path, '/\\'));

        if (! File::exists($absolutePath)) {
            return false;
        }

        return File::delete($absolutePath);
    }

    private function convertToWebp(string $inputPath, string $outputPath, array $options = []): void
    {
        $maxWidth = max(1, (int) ($options['max_width'] ?? config('image-processing.max_width', 1400)));
        $maxHeight = max(1, (int) ($options['max_height'] ?? config('image-processing.max_height', 1400)));
        $quality = $this->normalizeQuality((int) ($options['quality'] ?? config('image-processing.quality', 82)));
        $timeout = max(1, (int) ($options['timeout'] ?? config('image-processing.timeout', 120)));
        $nodeBinary = (string) ($options['node_binary'] ?? config('image-processing.node_binary', 'node'));
        $scriptPaths = array_values(array_filter([
            (string) config('image-processing.script_path'),
            (string) config('image-processing.fallback_script_path'),
        ]));

        if ($scriptPaths === []) {
            throw new RuntimeException('No image processor scripts are configured.');
        }

        $failures = [];

        foreach ($scriptPaths as $scriptPath) {
            if (! File::exists($scriptPath)) {
                $failures[] = new RuntimeException(sprintf('Image processor script not found: %s', $scriptPath));
                continue;
            }

            $env = null;
            if (DIRECTORY_SEPARATOR === '\\') {
                $systemRoot = getenv('SystemRoot') ?: (getenv('SYSTEMROOT') ?: ($_SERVER['SystemRoot'] ?? ($_SERVER['SYSTEMROOT'] ?? 'C:\\Windows')));
                $env = [];
                $sources = [
                    is_array(getenv()) ? getenv() : [],
                    $_ENV,
                    $_SERVER
                ];
                foreach ($sources as $source) {
                    foreach ($source as $key => $value) {
                        if (is_scalar($value)) {
                            $env[$key] = (string) $value;
                        }
                    }
                }
                $env['SystemRoot'] = $systemRoot;
            }

            $process = new Process([
                $nodeBinary,
                $scriptPath,
                '--input',
                $inputPath,
                '--output',
                $outputPath,
                '--maxWidth',
                (string) $maxWidth,
                '--maxHeight',
                (string) $maxHeight,
                '--quality',
                (string) $quality,
            ], null, $env);

            $process->setTimeout($timeout);

            try {
                $process->mustRun();
                return;
            } catch (Throwable $throwable) {
                $message = trim($throwable->getMessage() . ' ' . $process->getErrorOutput());

                $failures[] = new RuntimeException(
                    'Image processing failed via ' . basename($scriptPath) . ': ' . $message,
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
                ? 'Image processing failed for all configured processors.'
                : 'Image processing failed for all configured processors: ' . $details,
            0,
            $failures[0] ?? null
        );
    }

    private function ensureDirectory(string $directory): void
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
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
