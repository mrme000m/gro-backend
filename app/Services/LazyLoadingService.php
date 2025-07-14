<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class LazyLoadingService
{
    protected $cdnService;
    protected $imageOptimizationService;

    public function __construct(CdnService $cdnService, ImageOptimizationService $imageOptimizationService)
    {
        $this->cdnService = $cdnService;
        $this->imageOptimizationService = $imageOptimizationService;
    }

    /**
     * Generate lazy loading image HTML
     *
     * @param string $directory
     * @param string $filename
     * @param array $options
     * @return string
     */
    public function generateLazyImage(string $directory, string $filename, array $options = []): string
    {
        $alt = $options['alt'] ?? '';
        $class = $options['class'] ?? 'lazy-image';
        $sizes = $options['sizes'] ?? ['small', 'medium', 'large'];
        $defaultSize = $options['default_size'] ?? 'medium';
        $preferWebP = $options['prefer_webp'] ?? true;

        // Generate placeholder (low quality image)
        $placeholderUrl = $this->generatePlaceholder($directory, $filename);
        
        // Generate main image URL
        $mainImageUrl = $this->cdnService->getOptimizedImageUrl($directory, $filename, $defaultSize, $preferWebP);
        
        // Generate srcset for responsive images
        $srcSet = $this->generateResponsiveSrcSet($directory, $filename, $sizes, $preferWebP);
        
        // Generate sizes attribute
        $sizesAttr = $this->generateSizesAttribute($options['responsive_sizes'] ?? []);

        $html = '<img ';
        $html .= 'src="' . $placeholderUrl . '" ';
        $html .= 'data-src="' . $mainImageUrl . '" ';
        
        if ($srcSet) {
            $html .= 'data-srcset="' . $srcSet . '" ';
        }
        
        if ($sizesAttr) {
            $html .= 'sizes="' . $sizesAttr . '" ';
        }
        
        $html .= 'alt="' . htmlspecialchars($alt) . '" ';
        $html .= 'class="' . htmlspecialchars($class) . '" ';
        $html .= 'loading="lazy" ';
        $html .= 'decoding="async" ';
        $html .= '/>';

        return $html;
    }

    /**
     * Generate picture element with WebP support
     *
     * @param string $directory
     * @param string $filename
     * @param array $options
     * @return string
     */
    public function generatePictureElement(string $directory, string $filename, array $options = []): string
    {
        $alt = $options['alt'] ?? '';
        $class = $options['class'] ?? 'lazy-image';
        $sizes = $options['sizes'] ?? ['small', 'medium', 'large'];
        $defaultSize = $options['default_size'] ?? 'medium';

        $html = '<picture>';

        // WebP source
        $webpSrcSet = $this->generateResponsiveSrcSet($directory, $filename, $sizes, true);
        if ($webpSrcSet) {
            $html .= '<source data-srcset="' . $webpSrcSet . '" type="image/webp" />';
        }

        // Fallback source (original format)
        $originalSrcSet = $this->generateResponsiveSrcSet($directory, $filename, $sizes, false);
        if ($originalSrcSet) {
            $html .= '<source data-srcset="' . $originalSrcSet . '" />';
        }

        // Fallback img element
        $placeholderUrl = $this->generatePlaceholder($directory, $filename);
        $mainImageUrl = $this->cdnService->getOptimizedImageUrl($directory, $filename, $defaultSize, false);
        
        $html .= '<img ';
        $html .= 'src="' . $placeholderUrl . '" ';
        $html .= 'data-src="' . $mainImageUrl . '" ';
        $html .= 'alt="' . htmlspecialchars($alt) . '" ';
        $html .= 'class="' . htmlspecialchars($class) . '" ';
        $html .= 'loading="lazy" ';
        $html .= 'decoding="async" ';
        $html .= '/>';

        $html .= '</picture>';

        return $html;
    }

    /**
     * Generate responsive image data for API
     *
     * @param string $directory
     * @param string $filename
     * @param array $options
     * @return array
     */
    public function generateResponsiveImageData(string $directory, string $filename, array $options = []): array
    {
        $sizes = $options['sizes'] ?? ['thumbnail', 'small', 'medium', 'large'];
        $includeWebP = $options['include_webp'] ?? true;

        $imageData = [
            'placeholder' => $this->generatePlaceholder($directory, $filename),
            'formats' => []
        ];

        foreach ($sizes as $size) {
            $urls = $this->imageOptimizationService->getResponsiveUrls($directory, $filename, $size);
            
            foreach ($urls as $format => $url) {
                // Convert to CDN URL if enabled
                if ($this->cdnService->isEnabled()) {
                    $path = str_replace(Storage::disk('public')->url(''), '', $url);
                    $url = $this->cdnService->getCdnUrl($path);
                }

                if (!isset($imageData['formats'][$format])) {
                    $imageData['formats'][$format] = [];
                }

                $imageData['formats'][$format][$size] = [
                    'url' => $url,
                    'width' => ImageOptimizationService::SIZES[$size]['width'] ?? null,
                    'height' => ImageOptimizationService::SIZES[$size]['height'] ?? null,
                ];
            }
        }

        return $imageData;
    }

    /**
     * Generate JavaScript for lazy loading
     *
     * @return string
     */
    public function generateLazyLoadingScript(): string
    {
        return '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if ("IntersectionObserver" in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            
                            // Handle img elements
                            if (img.tagName === "IMG") {
                                if (img.dataset.srcset) {
                                    img.srcset = img.dataset.srcset;
                                }
                                if (img.dataset.src) {
                                    img.src = img.dataset.src;
                                }
                            }
                            
                            // Handle source elements in picture
                            if (img.tagName === "SOURCE") {
                                if (img.dataset.srcset) {
                                    img.srcset = img.dataset.srcset;
                                }
                            }
                            
                            img.classList.remove("lazy-image");
                            img.classList.add("lazy-loaded");
                            observer.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll(".lazy-image, source[data-srcset]").forEach(img => {
                    imageObserver.observe(img);
                });
            } else {
                // Fallback for browsers without IntersectionObserver
                document.querySelectorAll(".lazy-image").forEach(img => {
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                    }
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                    }
                    img.classList.remove("lazy-image");
                    img.classList.add("lazy-loaded");
                });
            }
        });
        </script>';
    }

    /**
     * Generate CSS for lazy loading
     *
     * @return string
     */
    public function generateLazyLoadingCSS(): string
    {
        return '
        <style>
        .lazy-image {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            background-color: #f0f0f0;
        }
        
        .lazy-loaded {
            opacity: 1;
        }
        
        .lazy-image::before {
            content: "";
            display: block;
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #ddd;
            border-top-color: #666;
            border-radius: 50%;
            animation: lazy-spinner 1s linear infinite;
        }
        
        @keyframes lazy-spinner {
            to { transform: rotate(360deg); }
        }
        
        .lazy-loaded::before {
            display: none;
        }
        </style>';
    }

    /**
     * Generate placeholder image URL
     *
     * @param string $directory
     * @param string $filename
     * @return string
     */
    private function generatePlaceholder(string $directory, string $filename): string
    {
        // Try to get thumbnail version first
        $thumbnailUrl = $this->cdnService->getOptimizedImageUrl($directory, $filename, 'thumbnail', false);
        
        if ($thumbnailUrl !== $this->getDefaultImageUrl()) {
            return $thumbnailUrl;
        }

        // Generate a data URL placeholder
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="#f0f0f0"/>
                <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#999">Loading...</text>
            </svg>'
        );
    }

    /**
     * Generate responsive srcset
     *
     * @param string $directory
     * @param string $filename
     * @param array $sizes
     * @param bool $preferWebP
     * @return string
     */
    private function generateResponsiveSrcSet(string $directory, string $filename, array $sizes, bool $preferWebP): string
    {
        $srcSet = [];

        foreach ($sizes as $size) {
            $url = $this->cdnService->getOptimizedImageUrl($directory, $filename, $size, $preferWebP);
            $width = ImageOptimizationService::SIZES[$size]['width'] ?? null;
            
            if ($width && $url !== $this->getDefaultImageUrl()) {
                $srcSet[] = $url . ' ' . $width . 'w';
            }
        }

        return implode(', ', $srcSet);
    }

    /**
     * Generate sizes attribute
     *
     * @param array $responsiveSizes
     * @return string
     */
    private function generateSizesAttribute(array $responsiveSizes): string
    {
        if (empty($responsiveSizes)) {
            return '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw';
        }

        $sizes = [];
        foreach ($responsiveSizes as $breakpoint => $size) {
            $sizes[] = "(max-width: {$breakpoint}px) {$size}";
        }

        return implode(', ', $sizes);
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
}
