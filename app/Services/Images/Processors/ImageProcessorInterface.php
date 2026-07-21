<?php

namespace App\Services\Images\Processors;

interface ImageProcessorInterface
{
    /**
     * Check if the processor is supported in the current environment.
     *
     * @return bool
     */
    public function isSupported(): bool;

    /**
     * Process the input image and output it as WebP with resize and quality options.
     *
     * @param string $inputPath
     * @param string $outputPath
     * @param array $options
     * @return void
     * @throws \Throwable
     */
    public function process(string $inputPath, string $outputPath, array $options): void;
}
