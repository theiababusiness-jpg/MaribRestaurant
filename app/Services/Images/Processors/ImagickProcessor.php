<?php

namespace App\Services\Images\Processors;

use Imagick;
use Throwable;

class ImagickProcessor implements ImageProcessorInterface
{
    public function isSupported(): bool
    {
        return extension_loaded('imagick') && class_exists(Imagick::class);
    }

    public function process(string $inputPath, string $outputPath, array $options): void
    {
        $maxWidth = (int) ($options['max_width'] ?? 1400);
        $maxHeight = (int) ($options['max_height'] ?? 1400);
        $quality = (int) ($options['quality'] ?? 82);

        $imagick = new Imagick($inputPath);

        try {
            // Auto-rotate image based on EXIF orientation metadata
            if (method_exists($imagick, 'autoOrient')) {
                $imagick->autoOrient();
            }

            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            // Resize only if image is larger than maxWidth/maxHeight (withoutEnlargement = true)
            // and fit = inside (preserve aspect ratio)
            if ($width > $maxWidth || $height > $maxHeight) {
                $scale = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = max(1, (int) round($width * $scale));
                $newHeight = max(1, (int) round($height * $scale));

                $imagick->scaleImage($newWidth, $newHeight);
            }

            // Convert to webp
            $imagick->setImageFormat('webp');
            $imagick->setImageCompressionQuality($quality);

            // Save the output
            $imagick->writeImage($outputPath);
        } finally {
            $imagick->destroy();
        }
    }
}
