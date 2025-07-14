<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CdnService
{
    protected $cdnEnabled;
    protected $cdnUrl;
    protected $cdnDriver;

    public function __construct()
    {
        $this->cdnEnabled = config('cdn.enabled', false);
        $this->cdnUrl = config('cdn.url', '');
        $this->cdnDriver = config('cdn.driver', 's3');
    }

    /**
     * Upload file to CDN
     *
     * @param string $localPath
     * @param string $cdnPath
     * @return bool
     */
    public function uploadToCdn(string $localPath, string $cdnPath): bool
    {
        if (!$this->cdnEnabled) {
            return false;
        }

        try {
            $localFullPath = Storage::disk('public')->path($localPath);
            
            if (!file_exists($localFullPath)) {
                Log::warning('Local file not found for CDN upload', ['path' => $localPath]);
                return false;
            }

            $fileContents = file_get_contents($localFullPath);
            $success = Storage::disk($this->cdnDriver)->put($cdnPath, $fileContents);

            if ($success) {
                Log::info('File uploaded to CDN successfully', [
                    'local_path' => $localPath,
                    'cdn_path' => $cdnPath
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('CDN upload failed', [
                'local_path' => $localPath,
                'cdn_path' => $cdnPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Upload multiple image sizes to CDN
     *
     * @param string $directory
     * @param array $imageFiles
     * @return array
     */
    public function uploadImagesToCdn(string $directory, array $imageFiles): array
    {
        $cdnUrls = [];

        foreach ($imageFiles as $size => $filename) {
            $localPath = $directory . '/' . $filename;
            $cdnPath = $directory . '/' . $filename;
            
            if ($this->uploadToCdn($localPath, $cdnPath)) {
                $cdnUrls[$size] = $this->getCdnUrl($cdnPath);
            } else {
                // Fallback to local URL
                $cdnUrls[$size] = Storage::disk('public')->url($localPath);
            }
        }

        return $cdnUrls;
    }

    /**
     * Get CDN URL for a file
     *
     * @param string $path
     * @return string
     */
    public function getCdnUrl(string $path): string
    {
        if (!$this->cdnEnabled) {
            return Storage::disk('public')->url($path);
        }

        // For S3 and similar services
        if ($this->cdnDriver === 's3') {
            return Storage::disk('s3')->url($path);
        }

        // For custom CDN URL
        if ($this->cdnUrl) {
            return rtrim($this->cdnUrl, '/') . '/' . ltrim($path, '/');
        }

        // Fallback to local storage
        return Storage::disk('public')->url($path);
    }

    /**
     * Delete file from CDN
     *
     * @param string $path
     * @return bool
     */
    public function deleteFromCdn(string $path): bool
    {
        if (!$this->cdnEnabled) {
            return false;
        }

        try {
            return Storage::disk($this->cdnDriver)->delete($path);
        } catch (\Exception $e) {
            Log::error('CDN delete failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Sync local images to CDN
     *
     * @param string $directory
     * @return array
     */
    public function syncDirectoryToCdn(string $directory): array
    {
        $results = ['success' => 0, 'failed' => 0, 'files' => []];

        if (!$this->cdnEnabled) {
            return $results;
        }

        try {
            $files = Storage::disk('public')->files($directory);

            foreach ($files as $file) {
                if ($this->uploadToCdn($file, $file)) {
                    $results['success']++;
                    $results['files'][] = $file;
                } else {
                    $results['failed']++;
                }
            }

        } catch (\Exception $e) {
            Log::error('CDN sync failed', [
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Check if CDN is enabled and configured
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->cdnEnabled;
    }

    /**
     * Get optimized image URL with CDN support
     *
     * @param string $directory
     * @param string $filename
     * @param string $size
     * @param bool $preferWebP
     * @return string
     */
    public function getOptimizedImageUrl(string $directory, string $filename, string $size = 'medium', bool $preferWebP = true): string
    {
        if (!$filename) {
            return $this->getDefaultImageUrl();
        }

        $imageOptimizationService = app(ImageOptimizationService::class);
        
        // Get responsive URLs from optimization service
        $urls = $imageOptimizationService->getResponsiveUrls($directory, $filename, $size);

        // Convert to CDN URLs if enabled
        if ($this->cdnEnabled) {
            foreach ($urls as $format => $url) {
                $path = str_replace(Storage::disk('public')->url(''), '', $url);
                $urls[$format] = $this->getCdnUrl($path);
            }
        }

        // Prefer WebP if available and requested
        if ($preferWebP && isset($urls['webp'])) {
            return $urls['webp'];
        }

        // Fallback to original format
        if (isset($urls['original'])) {
            return $urls['original'];
        }

        // Final fallback
        return $this->getDefaultImageUrl();
    }

    /**
     * Generate responsive image srcset for different sizes
     *
     * @param string $directory
     * @param string $filename
     * @param array $sizes
     * @param string $format
     * @return string
     */
    public function generateSrcSet(string $directory, string $filename, array $sizes = ['small', 'medium', 'large'], string $format = 'original'): string
    {
        $srcSet = [];
        $imageOptimizationService = app(ImageOptimizationService::class);

        foreach ($sizes as $size) {
            $urls = $imageOptimizationService->getResponsiveUrls($directory, $filename, $size);
            
            if (isset($urls[$format])) {
                $url = $this->cdnEnabled ? $this->getCdnUrl(str_replace(Storage::disk('public')->url(''), '', $urls[$format])) : $urls[$format];
                $width = ImageOptimizationService::SIZES[$size]['width'] ?? '';
                
                if ($width) {
                    $srcSet[] = $url . ' ' . $width . 'w';
                }
            }
        }

        return implode(', ', $srcSet);
    }

    /**
     * Purge CDN cache for specific files
     *
     * @param array $paths
     * @return bool
     */
    public function purgeCdnCache(array $paths): bool
    {
        if (!$this->cdnEnabled) {
            return false;
        }

        // Implementation depends on CDN provider
        // This is a placeholder for CDN-specific cache purging
        try {
            // Example for CloudFlare API
            if (config('cdn.provider') === 'cloudflare') {
                return $this->purgeCloudflareCache($paths);
            }

            // Example for AWS CloudFront
            if (config('cdn.provider') === 'cloudfront') {
                return $this->purgeCloudFrontCache($paths);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('CDN cache purge failed', [
                'paths' => $paths,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get default image URL
     *
     * @return string
     */
    private function getDefaultImageUrl(): string
    {
        $defaultPath = 'assets/admin/img/160x160/2.png';
        
        if ($this->cdnEnabled) {
            return $this->getCdnUrl($defaultPath);
        }
        
        return asset('public/' . $defaultPath);
    }

    /**
     * Purge CloudFlare cache
     *
     * @param array $paths
     * @return bool
     */
    private function purgeCloudflareCache(array $paths): bool
    {
        $zoneId = config('cdn.cloudflare.zone_id');
        $apiToken = config('cdn.cloudflare.api_token');

        if (!$zoneId || !$apiToken) {
            return false;
        }

        $urls = array_map(function ($path) {
            return $this->getCdnUrl($path);
        }, $paths);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
        ])->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", [
            'files' => $urls
        ]);

        return $response->successful();
    }

    /**
     * Purge CloudFront cache
     *
     * @param array $paths
     * @return bool
     */
    private function purgeCloudFrontCache(array $paths): bool
    {
        // Implementation for AWS CloudFront invalidation
        // This would require AWS SDK integration
        return true;
    }
}
