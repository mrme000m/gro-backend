<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Content Delivery Network (CDN)
    | integration. You can enable/disable CDN and configure various providers.
    |
    */

    'enabled' => env('CDN_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | CDN Provider
    |--------------------------------------------------------------------------
    |
    | Supported providers: "s3", "cloudflare", "cloudfront", "custom"
    |
    */

    'provider' => env('CDN_PROVIDER', 's3'),

    /*
    |--------------------------------------------------------------------------
    | CDN URL
    |--------------------------------------------------------------------------
    |
    | The base URL for your CDN. This will be used to generate URLs for
    | assets served through the CDN.
    |
    */

    'url' => env('CDN_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | CDN Driver
    |--------------------------------------------------------------------------
    |
    | The filesystem driver to use for CDN uploads. This should match
    | one of the drivers configured in config/filesystems.php
    |
    */

    'driver' => env('CDN_DRIVER', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Auto Upload
    |--------------------------------------------------------------------------
    |
    | Automatically upload images to CDN when they are processed.
    |
    */

    'auto_upload' => env('CDN_AUTO_UPLOAD', true),

    /*
    |--------------------------------------------------------------------------
    | CloudFlare Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CloudFlare CDN integration including cache purging.
    |
    */

    'cloudflare' => [
        'zone_id' => env('CLOUDFLARE_ZONE_ID', ''),
        'api_token' => env('CLOUDFLARE_API_TOKEN', ''),
        'email' => env('CLOUDFLARE_EMAIL', ''),
        'api_key' => env('CLOUDFLARE_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS CloudFront Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AWS CloudFront CDN integration.
    |
    */

    'cloudfront' => [
        'distribution_id' => env('CLOUDFRONT_DISTRIBUTION_ID', ''),
        'access_key_id' => env('AWS_ACCESS_KEY_ID', ''),
        'secret_access_key' => env('AWS_SECRET_ACCESS_KEY', ''),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for image optimization and processing.
    |
    */

    'image_optimization' => [
        
        /*
        |--------------------------------------------------------------------------
        | WebP Generation
        |--------------------------------------------------------------------------
        |
        | Automatically generate WebP versions of uploaded images for better
        | compression and faster loading times.
        |
        */
        
        'generate_webp' => env('IMAGE_GENERATE_WEBP', true),

        /*
        |--------------------------------------------------------------------------
        | Image Sizes
        |--------------------------------------------------------------------------
        |
        | Define the different image sizes to generate. Each size will be
        | created automatically when an image is uploaded.
        |
        */

        'sizes' => [
            'thumbnail' => ['width' => 150, 'height' => 150, 'quality' => 50],
            'small' => ['width' => 300, 'height' => 300, 'quality' => 60],
            'medium' => ['width' => 600, 'height' => 600, 'quality' => 75],
            'large' => ['width' => 1200, 'height' => 1200, 'quality' => 90],
        ],

        /*
        |--------------------------------------------------------------------------
        | Default Image Sizes
        |--------------------------------------------------------------------------
        |
        | The default sizes to generate for different types of images.
        |
        */

        'default_sizes' => [
            'product' => ['thumbnail', 'small', 'medium', 'large'],
            'category' => ['thumbnail', 'medium'],
            'banner' => ['medium', 'large'],
            'profile' => ['thumbnail', 'small'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Image Quality Settings
        |--------------------------------------------------------------------------
        |
        | Quality settings for different image formats and sizes.
        |
        */

        'quality' => [
            'jpeg' => [
                'thumbnail' => 50,
                'small' => 60,
                'medium' => 75,
                'large' => 90,
            ],
            'webp' => [
                'thumbnail' => 50,
                'small' => 60,
                'medium' => 75,
                'large' => 85,
            ],
            'png' => [
                'compression' => 6, // 0-9, where 9 is maximum compression
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Maximum File Size
        |--------------------------------------------------------------------------
        |
        | Maximum file size for image uploads in bytes.
        |
        */

        'max_file_size' => env('IMAGE_MAX_FILE_SIZE', 10485760), // 10MB

        /*
        |--------------------------------------------------------------------------
        | Allowed Formats
        |--------------------------------------------------------------------------
        |
        | Allowed image formats for upload.
        |
        */

        'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],

        /*
        |--------------------------------------------------------------------------
        | Auto Orientation
        |--------------------------------------------------------------------------
        |
        | Automatically fix image orientation based on EXIF data.
        |
        */

        'auto_orientation' => env('IMAGE_AUTO_ORIENTATION', true),

        /*
        |--------------------------------------------------------------------------
        | Strip EXIF Data
        |--------------------------------------------------------------------------
        |
        | Remove EXIF data from images for privacy and file size reduction.
        |
        */

        'strip_exif' => env('IMAGE_STRIP_EXIF', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Lazy Loading Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for lazy loading images to improve page load performance.
    |
    */

    'lazy_loading' => [
        
        /*
        |--------------------------------------------------------------------------
        | Enable Lazy Loading
        |--------------------------------------------------------------------------
        |
        | Enable lazy loading for images throughout the application.
        |
        */
        
        'enabled' => env('LAZY_LOADING_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Intersection Observer Threshold
        |--------------------------------------------------------------------------
        |
        | The threshold for the Intersection Observer API. Images will start
        | loading when they are this percentage visible in the viewport.
        |
        */

        'threshold' => env('LAZY_LOADING_THRESHOLD', 0.1),

        /*
        |--------------------------------------------------------------------------
        | Root Margin
        |--------------------------------------------------------------------------
        |
        | The root margin for the Intersection Observer. Images will start
        | loading when they are this distance from entering the viewport.
        |
        */

        'root_margin' => env('LAZY_LOADING_ROOT_MARGIN', '50px'),

        /*
        |--------------------------------------------------------------------------
        | Placeholder Type
        |--------------------------------------------------------------------------
        |
        | Type of placeholder to show while images are loading.
        | Options: 'blur', 'color', 'svg', 'thumbnail'
        |
        */

        'placeholder_type' => env('LAZY_LOADING_PLACEHOLDER', 'thumbnail'),

        /*
        |--------------------------------------------------------------------------
        | Placeholder Color
        |--------------------------------------------------------------------------
        |
        | Background color for placeholder when using 'color' type.
        |
        */

        'placeholder_color' => env('LAZY_LOADING_PLACEHOLDER_COLOR', '#f0f0f0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for image caching and cache headers.
    |
    */

    'cache' => [
        
        /*
        |--------------------------------------------------------------------------
        | Browser Cache Duration
        |--------------------------------------------------------------------------
        |
        | How long browsers should cache images (in seconds).
        |
        */
        
        'browser_cache_duration' => env('IMAGE_BROWSER_CACHE_DURATION', 2592000), // 30 days

        /*
        |--------------------------------------------------------------------------
        | CDN Cache Duration
        |--------------------------------------------------------------------------
        |
        | How long CDN should cache images (in seconds).
        |
        */

        'cdn_cache_duration' => env('IMAGE_CDN_CACHE_DURATION', 31536000), // 1 year

        /*
        |--------------------------------------------------------------------------
        | Enable ETags
        |--------------------------------------------------------------------------
        |
        | Enable ETag headers for better caching.
        |
        */

        'enable_etags' => env('IMAGE_ENABLE_ETAGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring image performance and optimization.
    |
    */

    'monitoring' => [
        
        /*
        |--------------------------------------------------------------------------
        | Enable Performance Monitoring
        |--------------------------------------------------------------------------
        |
        | Track image optimization performance and statistics.
        |
        */
        
        'enabled' => env('IMAGE_MONITORING_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Log Optimization Results
        |--------------------------------------------------------------------------
        |
        | Log the results of image optimization operations.
        |
        */

        'log_optimization' => env('IMAGE_LOG_OPTIMIZATION', true),

        /*
        |--------------------------------------------------------------------------
        | Track File Sizes
        |--------------------------------------------------------------------------
        |
        | Track original and optimized file sizes for analytics.
        |
        */

        'track_file_sizes' => env('IMAGE_TRACK_FILE_SIZES', true),
    ],

];
