<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageOptimizationService;
use App\Services\CdnService;
use App\Model\Product;
use App\Model\Category;
use Intervention\Image\Facades\Image;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize 
                            {--type=all : Type of images to optimize (all, products, categories)}
                            {--batch=50 : Number of images to process in each batch}
                            {--force : Force re-optimization of already optimized images}
                            {--cdn : Upload optimized images to CDN}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize existing images by generating multiple sizes and WebP versions';

    protected $imageOptimizationService;
    protected $cdnService;

    public function __construct(ImageOptimizationService $imageOptimizationService, CdnService $cdnService)
    {
        parent::__construct();
        $this->imageOptimizationService = $imageOptimizationService;
        $this->cdnService = $cdnService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->option('type');
        $batchSize = (int) $this->option('batch');
        $force = $this->option('force');
        $uploadToCdn = $this->option('cdn');

        $this->info('Starting image optimization...');
        $this->info("Type: {$type}, Batch size: {$batchSize}, Force: " . ($force ? 'Yes' : 'No') . ", CDN: " . ($uploadToCdn ? 'Yes' : 'No'));

        switch ($type) {
            case 'products':
                $this->optimizeProductImages($batchSize, $force, $uploadToCdn);
                break;
            case 'categories':
                $this->optimizeCategoryImages($batchSize, $force, $uploadToCdn);
                break;
            case 'all':
            default:
                $this->optimizeProductImages($batchSize, $force, $uploadToCdn);
                $this->optimizeCategoryImages($batchSize, $force, $uploadToCdn);
                break;
        }

        $this->info('Image optimization completed!');
        return 0;
    }

    /**
     * Optimize product images
     *
     * @param int $batchSize
     * @param bool $force
     * @param bool $uploadToCdn
     * @return void
     */
    private function optimizeProductImages(int $batchSize, bool $force, bool $uploadToCdn): void
    {
        $this->info('Optimizing product images...');

        Product::whereNotNull('image')
            ->where('image', '!=', '')
            ->where('image', '!=', 'def.png')
            ->chunk($batchSize, function ($products) use ($force, $uploadToCdn) {
                $bar = $this->output->createProgressBar($products->count());
                $bar->start();

                foreach ($products as $product) {
                    try {
                        $this->optimizeProductImage($product, $force, $uploadToCdn);
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->error("Failed to optimize product image {$product->id}: " . $e->getMessage());
                    }
                }

                $bar->finish();
                $this->newLine();
            });
    }

    /**
     * Optimize category images
     *
     * @param int $batchSize
     * @param bool $force
     * @param bool $uploadToCdn
     * @return void
     */
    private function optimizeCategoryImages(int $batchSize, bool $force, bool $uploadToCdn): void
    {
        $this->info('Optimizing category images...');

        Category::whereNotNull('image')
            ->where('image', '!=', '')
            ->where('image', '!=', 'def.png')
            ->chunk($batchSize, function ($categories) use ($force, $uploadToCdn) {
                $bar = $this->output->createProgressBar($categories->count());
                $bar->start();

                foreach ($categories as $category) {
                    try {
                        $this->optimizeCategoryImage($category, $force, $uploadToCdn);
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->error("Failed to optimize category image {$category->id}: " . $e->getMessage());
                    }
                }

                $bar->finish();
                $this->newLine();
            });
    }

    /**
     * Optimize single product image
     *
     * @param Product $product
     * @param bool $force
     * @param bool $uploadToCdn
     * @return void
     */
    private function optimizeProductImage(Product $product, bool $force, bool $uploadToCdn): void
    {
        $directory = 'product';
        $imagePath = $directory . '/' . $product->image;

        if (!Storage::disk('public')->exists($imagePath)) {
            $this->warn("Product image not found: {$imagePath}");
            return;
        }

        // Check if already optimized (unless force is true)
        if (!$force && $this->isImageOptimized($directory, $product->image)) {
            return;
        }

        // Create temporary uploaded file from existing image
        $tempFile = $this->createTempUploadedFile($imagePath);
        
        if (!$tempFile) {
            return;
        }

        try {
            // Generate optimized versions
            $optimizedImages = $this->imageOptimizationService->uploadAndOptimize(
                $tempFile,
                $directory,
                [
                    'filename' => pathinfo($product->image, PATHINFO_FILENAME),
                    'sizes' => ['thumbnail', 'small', 'medium', 'large'],
                    'webp' => true
                ]
            );

            // Upload to CDN if requested
            if ($uploadToCdn && $this->cdnService->isEnabled()) {
                $this->cdnService->uploadImagesToCdn($directory, $optimizedImages);
            }

            // Update product with image data
            $product->update(['image_data' => $optimizedImages]);

        } finally {
            // Clean up temp file
            if (file_exists($tempFile->getRealPath())) {
                unlink($tempFile->getRealPath());
            }
        }
    }

    /**
     * Optimize single category image
     *
     * @param Category $category
     * @param bool $force
     * @param bool $uploadToCdn
     * @return void
     */
    private function optimizeCategoryImage(Category $category, bool $force, bool $uploadToCdn): void
    {
        $directory = 'category';
        $imagePath = $directory . '/' . $category->image;

        if (!Storage::disk('public')->exists($imagePath)) {
            $this->warn("Category image not found: {$imagePath}");
            return;
        }

        // Check if already optimized (unless force is true)
        if (!$force && $this->isImageOptimized($directory, $category->image)) {
            return;
        }

        // Create temporary uploaded file from existing image
        $tempFile = $this->createTempUploadedFile($imagePath);
        
        if (!$tempFile) {
            return;
        }

        try {
            // Generate optimized versions
            $optimizedImages = $this->imageOptimizationService->uploadAndOptimize(
                $tempFile,
                $directory,
                [
                    'filename' => pathinfo($category->image, PATHINFO_FILENAME),
                    'sizes' => ['thumbnail', 'medium'],
                    'webp' => true
                ]
            );

            // Upload to CDN if requested
            if ($uploadToCdn && $this->cdnService->isEnabled()) {
                $this->cdnService->uploadImagesToCdn($directory, $optimizedImages);
            }

        } finally {
            // Clean up temp file
            if (file_exists($tempFile->getRealPath())) {
                unlink($tempFile->getRealPath());
            }
        }
    }

    /**
     * Check if image is already optimized
     *
     * @param string $directory
     * @param string $filename
     * @return bool
     */
    private function isImageOptimized(string $directory, string $filename): bool
    {
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Check if thumbnail and WebP versions exist
        $thumbnailFile = $directory . '/' . $baseName . '-thumbnail.' . $extension;
        $webpFile = $directory . '/' . $baseName . '-medium.webp';

        return Storage::disk('public')->exists($thumbnailFile) && 
               Storage::disk('public')->exists($webpFile);
    }

    /**
     * Create temporary uploaded file from existing image
     *
     * @param string $imagePath
     * @return \Illuminate\Http\UploadedFile|null
     */
    private function createTempUploadedFile(string $imagePath): ?\Illuminate\Http\UploadedFile
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            $filename = basename($imagePath);
            $mimeType = mime_content_type($fullPath);
            $fileSize = filesize($fullPath);

            // Create temporary file
            $tempPath = tempnam(sys_get_temp_dir(), 'img_opt_');
            copy($fullPath, $tempPath);

            return new \Illuminate\Http\UploadedFile(
                $tempPath,
                $filename,
                $mimeType,
                null,
                true // Mark as test file to avoid validation errors
            );

        } catch (\Exception $e) {
            $this->error("Failed to create temp file for {$imagePath}: " . $e->getMessage());
            return null;
        }
    }
}
