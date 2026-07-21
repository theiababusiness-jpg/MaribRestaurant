<?php

namespace App\Services\Images\Processors;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use RuntimeException;
use Throwable;

class NodeProcessor implements ImageProcessorInterface
{
    public function isSupported(): bool
    {
        $nodeBinary = (string) config('image-processing.node_binary', 'node');
        try {
            $process = new Process([$nodeBinary, '-v']);
            $process->run();
            return $process->isSuccessful();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function process(string $inputPath, string $outputPath, array $options): void
    {
        $maxWidth = (int) ($options['max_width'] ?? 1400);
        $maxHeight = (int) ($options['max_height'] ?? 1400);
        $quality = (int) ($options['quality'] ?? 82);
        $timeout = (int) ($options['timeout'] ?? 120);
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
                return; // Succeeded!
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
                ? 'Node.js image processing failed for all configured scripts.'
                : 'Node.js image processing failed: ' . $details,
            0,
            $failures[0] ?? null
        );
    }
}
