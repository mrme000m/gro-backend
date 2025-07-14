<?php

namespace App\Observers;

use App\Model\Product;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Product "created" event.
     *
     * @param  \App\Model\Product  $product
     * @return void
     */
    public function created(Product $product)
    {
        $this->invalidateProductCaches($product);
        Log::info('Product created, caches invalidated', ['product_id' => $product->id]);
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param  \App\Model\Product  $product
     * @return void
     */
    public function updated(Product $product)
    {
        $this->invalidateProductCaches($product);
        Log::info('Product updated, caches invalidated', ['product_id' => $product->id]);
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param  \App\Model\Product  $product
     * @return void
     */
    public function deleted(Product $product)
    {
        $this->invalidateProductCaches($product);
        Log::info('Product deleted, caches invalidated', ['product_id' => $product->id]);
    }

    /**
     * Handle the Product "restored" event.
     *
     * @param  \App\Model\Product  $product
     * @return void
     */
    public function restored(Product $product)
    {
        $this->invalidateProductCaches($product);
        Log::info('Product restored, caches invalidated', ['product_id' => $product->id]);
    }

    /**
     * Invalidate product-related caches
     *
     * @param Product $product
     * @return void
     */
    private function invalidateProductCaches(Product $product): void
    {
        try {
            // Invalidate specific product cache
            $this->cacheService->invalidateProductCaches($product->id);
            
            // If featured status changed, invalidate featured products cache
            if ($product->isDirty('is_featured')) {
                $this->cacheService->invalidateProductCaches();
            }
            
            // If daily needs status changed, invalidate daily needs cache
            if ($product->isDirty('daily_needs')) {
                $this->cacheService->invalidateProductCaches();
            }
            
            // If status changed, invalidate all product listings
            if ($product->isDirty('status')) {
                $this->cacheService->invalidateProductCaches();
            }
        } catch (\Exception $e) {
            Log::error('Failed to invalidate product caches in observer', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
