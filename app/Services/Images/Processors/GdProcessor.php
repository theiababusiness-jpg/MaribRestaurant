<?php

namespace App\Services\Images\Processors;

use RuntimeException;
use Throwable;

class GdProcessor implements ImageProcessorInterface
{
    public function isSupported(): bool
    {
        return extension_loaded('gd') && function_exists('imagewebp');
    }

    public function process(string $inputPath, string $outputPath, array $options): void
    {
        $maxWidth = (int) ($options['max_width'] ?? 1400);
        $maxHeight = (int) ($options['max_height'] ?? 1400);
        $quality = (int) ($options['quality'] ?? 82);

        $info = @getimagesize($inputPath);
        if (!$info) {
            throw new RuntimeException('Failed to get image size or invalid image format.');
        }

        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];
        $image = null;

        try {
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = @imagecreatefromjpeg($inputPath);
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($inputPath);
                    break;
                case 'image/webp':
                    $image = @imagecreatefromwebp($inputPath);
                    break;
                case 'image/gif':
                    $image = @imagecreatefromgif($inputPath);
                    break;
                default:
                    $content = @file_get_contents($inputPath);
                    if ($content === false) {
                        throw new RuntimeException('Failed to read image file content.');
                    }
                    $image = @imagecreatefromstring($content);
                    break;
            }

            if (!$image) {
                throw new RuntimeException('Failed to create GD image resource.');
            }

            // Auto-rotate JPEG based on EXIF orientation
            if (($mime === 'image/jpeg' || $mime === 'image/jpg') && function_exists('exif_read_data')) {
                try {
                    $exif = @exif_read_data($inputPath);
                    if ($exif && !empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 8:
                                $rotated = imagerotate($image, 90, 0);
                                if ($rotated) {
                                    imagedestroy($image);
                                    $image = $rotated;
                                }
                                break;
                            case 3:
                                $rotated = imagerotate($image, 180, 0);
                                if ($rotated) {
                                    imagedestroy($image);
                                    $image = $rotated;
                                }
                                break;
                            case 6:
                                $rotated = imagerotate($image, -90, 0);
                                if ($rotated) {
                                    imagedestroy($image);
                                    $image = $rotated;
                                }
                                break;
                        }
                    }
                } catch (Throwable $e) {
                    // Ignore EXIF rotation errors
                }
            }

            // Get dimensions after rotation
            $width = imagesx($image);
            $height = imagesy($image);

            // Resize only if image is larger than maxWidth/maxHeight (withoutEnlargement = true)
            // and fit = inside (preserve aspect ratio)
            if ($width > $maxWidth || $height > $maxHeight) {
                $scale = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = max(1, (int) round($width * $scale));
                $newHeight = max(1, (int) round($height * $scale));

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                if (!$resizedImage) {
                    throw new RuntimeException('Failed to create GD truecolor image resource.');
                }

                // Preserve transparency for PNG and WebP
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);

                if (!imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
                    imagedestroy($resizedImage);
                    throw new RuntimeException('Failed to resize/resample image.');
                }

                imagedestroy($image);
                $image = $resizedImage;
            }

            // Save to WebP
            if (!imagewebp($image, $outputPath, $quality)) {
                throw new RuntimeException('Failed to save image as WebP.');
            }
        } finally {
            if ($image) {
                @imagedestroy($image);
            }
        }
    }
}
