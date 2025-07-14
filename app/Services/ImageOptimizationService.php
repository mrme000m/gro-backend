<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;

class ImageOptimizationService
{
    // Image quality settings
    const QUALITY_HIGH = 90;
    const QUALITY_MEDIUM = 75;
    const QUALITY_LOW = 60;
    const QUALITY_THUMBNAIL = 50;

    // Image size presets
    const SIZES = [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'small' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 600, 'height' => 600],
        'large' => ['width' => 1200, 'height' => 1200],
        'original' => ['width' => null, 'height' => null],
    ];

    // Supported formats
    const SUPPORTED_FORMATS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const WEBP_SUPPORTED_SOURCES = ['jpg', 'jpeg', 'png'];

    /**
     * Upload and optimize image with multiple sizes and formats
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     */
    public function uploadAndOptimize(UploadedFile $file, string $directory, array $options = []): array
    {
        try {
            $originalExtension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($originalExtension, self::SUPPORTED_FORMATS)) {
                throw new \Exception('Unsupported image format: ' . $originalExtension);
            }

            $baseFilename = $this->generateFilename($options['filename'] ?? null);
            $results = [];

            // Create directory if it doesn't exist
            $this->ensureDirectoryExists($directory);

            // Load image
            $image = Image::make($file->getRealPath());
            
            // Fix orientation based on EXIF data
            $image->orientate();

            // Generate different sizes
            $sizes = $options['sizes'] ?? ['thumbnail', 'medium', 'original'];
            $generateWebP = $options['webp'] ?? true;

            foreach ($sizes as $sizeName) {
                if (!isset(self::SIZES[$sizeName])) {
                    continue;
                }

                $sizeConfig = self::SIZES[$sizeName];
                $resizedImage = clone $image;

                // Resize image if dimensions are specified
                if ($sizeConfig['width'] && $sizeConfig['height']) {
                    $resizedImage->fit($sizeConfig['width'], $sizeConfig['height'], function ($constraint) {
                        $constraint->upsize();
                    });
                }

                // Save original format
                $filename = $this->buildFilename($baseFilename, $sizeName, $originalExtension);
                $quality = $this->getQualityForSize($sizeName);
                
                $this->saveImage($resizedImage, $directory, $filename, $originalExtension, $quality);
                $results[$sizeName] = $filename;

                // Generate WebP version if supported and requested
                if ($generateWebP && in_array($originalExtension, self::WEBP_SUPPORTED_SOURCES)) {
                    $webpFilename = $this->buildFilename($baseFilename, $sizeName, 'webp');
                    $this->saveImage($resizedImage, $directory, $webpFilename, 'webp', $quality);
                    $results[$sizeName . '_webp'] = $webpFilename;
                }
            }

            // Log successful upload
            Log::info('Image optimized successfully', [
                'original_file' => $file->getClientOriginalName(),
                'directory' => $directory,
                'sizes_generated' => count($results),
                'file_size' => $file->getSize(),
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error('Image optimization failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'directory' => $directory,
            ]);
            
            throw $e;
        }
    }

    /**
     * Update existing image with optimization
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $oldFiles
     * @param array $options
     * @return array
     */
    public function updateOptimized(UploadedFile $file, string $directory, array $oldFiles = [], array $options = []): array
    {
        // Delete old files
        $this->deleteFiles($directory, $oldFiles);
        
        // Upload new optimized images
        return $this->uploadAndOptimize($file, $directory, $options);
    }

    /**
     * Generate responsive image URLs for different formats
     *
     * @param string $directory
     * @param string $filename
     * @param string $size
     * @return array
     */
    public function getResponsiveUrls(string $directory, string $filename, string $size = 'medium'): array
    {
        $baseUrl = Storage::disk('public')->url($directory);
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $urls = [];

        // Original format
        $originalFile = $this->buildFilename($baseName, $size, $extension);
        if (Storage::disk('public')->exists($directory . '/' . $originalFile)) {
            $urls['original'] = $baseUrl . '/' . $originalFile;
        }

        // WebP format
        $webpFile = $this->buildFilename($baseName, $size, 'webp');
        if (Storage::disk('public')->exists($directory . '/' . $webpFile)) {
            $urls['webp'] = $baseUrl . '/' . $webpFile;
        }

        return $urls;
    }

    /**
     * Get optimized image URL with fallback
     *
     * @param string $directory
     * @param string $filename
     * @param string $size
     * @param bool $preferWebP
     * @return string
     */
    public function getOptimizedUrl(string $directory, string $filename, string $size = 'medium', bool $preferWebP = true): string
    {
        if (!$filename) {
            return $this->getDefaultImageUrl();
        }

        $urls = $this->getResponsiveUrls($directory, $filename, $size);

        // Prefer WebP if available and requested
        if ($preferWebP && isset($urls['webp'])) {
            return $urls['webp'];
        }

        // Fallback to original format
        if (isset($urls['original'])) {
            return $urls['original'];
        }

        // Final fallback to default image
        return $this->getDefaultImageUrl();
    }

    /**
     * Delete image files
     *
     * @param string $directory
     * @param array $filenames
     * @return bool
     */
    public function deleteFiles(string $directory, array $filenames): bool
    {
        try {
            foreach ($filenames as $filename) {
                if ($filename && Storage::disk('public')->exists($directory . '/' . $filename)) {
                    Storage::disk('public')->delete($directory . '/' . $filename);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete image files', [
                'directory' => $directory,
                'files' => $filenames,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate unique filename
     *
     * @param string|null $customName
     * @return string
     */
    private function generateFilename(?string $customName = null): string
    {
        if ($customName) {
            return $customName . '-' . uniqid();
        }
        
        return Carbon::now()->format('Y-m-d') . '-' . uniqid();
    }

    /**
     * Build filename with size and extension
     *
     * @param string $baseName
     * @param string $size
     * @param string $extension
     * @return string
     */
    private function buildFilename(string $baseName, string $size, string $extension): string
    {
        if ($size === 'original') {
            return $baseName . '.' . $extension;
        }
        
        return $baseName . '-' . $size . '.' . $extension;
    }

    /**
     * Get quality setting for image size
     *
     * @param string $size
     * @return int
     */
    private function getQualityForSize(string $size): int
    {
        return match ($size) {
            'thumbnail' => self::QUALITY_THUMBNAIL,
            'small' => self::QUALITY_LOW,
            'medium' => self::QUALITY_MEDIUM,
            'large', 'original' => self::QUALITY_HIGH,
            default => self::QUALITY_MEDIUM,
        };
    }

    /**
     * Save image to storage
     *
     * @param \Intervention\Image\Image $image
     * @param string $directory
     * @param string $filename
     * @param string $format
     * @param int $quality
     * @return void
     */
    private function saveImage($image, string $directory, string $filename, string $format, int $quality): void
    {
        $path = $directory . '/' . $filename;
        
        // Encode image based on format
        $encodedImage = match ($format) {
            'webp' => $image->encode('webp', $quality),
            'png' => $image->encode('png'),
            'gif' => $image->encode('gif'),
            default => $image->encode('jpg', $quality),
        };

        Storage::disk('public')->put($path, $encodedImage);
    }

    /**
     * Ensure directory exists
     *
     * @param string $directory
     * @return void
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
    }

    /**
     * Get default image URL
     *
     * @return string
     */
    private function getDefaultImageUrl(): string
    {
        return asset('public/assets/admin/img/160x160/2.png');
    }

    /**
     * Get image information
     *
     * @param string $directory
     * @param string $filename
     * @return array
     */
    public function getImageInfo(string $directory, string $filename): array
    {
        $path = $directory . '/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            return [];
        }

        $fullPath = Storage::disk('public')->path($path);
        $imageInfo = getimagesize($fullPath);
        $fileSize = Storage::disk('public')->size($path);

        return [
            'width' => $imageInfo[0] ?? 0,
            'height' => $imageInfo[1] ?? 0,
            'mime_type' => $imageInfo['mime'] ?? '',
            'file_size' => $fileSize,
            'file_size_human' => $this->formatBytes($fileSize),
        ];
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
