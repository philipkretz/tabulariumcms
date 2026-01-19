<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageProcessor
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    /**
     * Validate an uploaded image file
     *
     * @param UploadedFile $file The uploaded file
     * @param int $maxSize Maximum file size in bytes
     * @throws \InvalidArgumentException if validation fails
     */
    public function validateImageFile(UploadedFile $file, int $maxSize): void
    {
        // Check if file was uploaded
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('The uploaded file is not valid.');
        }

        // Check file size
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            throw new \InvalidArgumentException("File size exceeds maximum allowed size of {$maxSizeMB}MB.");
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        // Verify it's actually an image by checking dimensions
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- UploadedFile::getPathname() is a controlled Symfony file path
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \InvalidArgumentException('The uploaded file is not a valid image.');
        }
    }

    /**
     * Resize an image to fit within maximum dimensions while maintaining aspect ratio
     *
     * @param UploadedFile $file The uploaded file
     * @param int $maxWidth Maximum width in pixels
     * @param int $maxHeight Maximum height in pixels
     * @param int $quality Quality for JPEG compression (1-100)
     * @return string The path to the resized temporary file
     * @throws \RuntimeException if image processing fails
     */
    public function resizeImage(UploadedFile $file, int $maxWidth, int $maxHeight, int $quality = 85): string
    {
        // Get original image dimensions
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- UploadedFile::getPathname() is a controlled Symfony file path
        list($originalWidth, $originalHeight, $imageType) = getimagesize($file->getPathname());

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);

        // Don't upscale images
        if ($ratio > 1) {
            $ratio = 1;
        }

        $newWidth = (int) round($originalWidth * $ratio);
        $newHeight = (int) round($originalHeight * $ratio);

        // Create image resource from source
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- UploadedFile::getPathname() is a controlled Symfony file path
        $sourceImage = match ($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($file->getPathname()),
            IMAGETYPE_PNG => imagecreatefrompng($file->getPathname()),
            IMAGETYPE_GIF => imagecreatefromgif($file->getPathname()),
            IMAGETYPE_WEBP => imagecreatefromwebp($file->getPathname()),
            default => throw new \RuntimeException('Unsupported image type.'),
        };

        if ($sourceImage === false) {
            throw new \RuntimeException('Failed to create image resource.');
        }

        // Create new image with desired dimensions
        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($destinationImage === false) {
            imagedestroy($sourceImage);
            throw new \RuntimeException('Failed to create destination image.');
        }

        // Preserve transparency for PNG and GIF
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
            imagealphablending($destinationImage, false);
            imagesavealpha($destinationImage, true);
            $transparent = imagecolorallocatealpha($destinationImage, 0, 0, 0, 127);
            imagefilledrectangle($destinationImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Copy and resize
        $success = imagecopyresampled(
            $destinationImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        if (!$success) {
            imagedestroy($sourceImage);
            imagedestroy($destinationImage);
            throw new \RuntimeException('Failed to resize image.');
        }

        // Create temporary file for the resized image
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using system temp directory
        $tempFile = tempnam(sys_get_temp_dir(), 'img_') . '.' . $file->guessExtension();

        // Save resized image
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Writing to controlled temp file path
        $saved = match ($imageType) {
            IMAGETYPE_JPEG => imagejpeg($destinationImage, $tempFile, $quality),
            IMAGETYPE_PNG => imagepng($destinationImage, $tempFile, (int) round((100 - $quality) / 11)),
            IMAGETYPE_GIF => imagegif($destinationImage, $tempFile),
            IMAGETYPE_WEBP => imagewebp($destinationImage, $tempFile, $quality),
            default => false,
        };

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($destinationImage);

        if (!$saved) {
            throw new \RuntimeException('Failed to save resized image.');
        }

        return $tempFile;
    }

    /**
     * Generate a safe filename for an uploaded file
     *
     * @param string $prefix Prefix for the filename (e.g., 'avatar', 'cover')
     * @param string $extension File extension
     * @return string Safe filename
     */
    public function generateSafeFilename(string $prefix, string $extension): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $safePrefix = $this->slugger->slug($prefix)->lower();

        return sprintf('%s-%d-%s.%s', $safePrefix, $timestamp, $random, $extension);
    }

    /**
     * Sanitize a file path to prevent path traversal attacks
     *
     * @param string $path The path to sanitize
     * @return string The sanitized path
     */
    public function sanitizePath(string $path): string
    {
        // Remove any '..' or '.' path components
        $path = str_replace(['..', './'], '', $path);

        // Remove any leading slashes
        $path = ltrim($path, '/\\');

        return $path;
    }
}
