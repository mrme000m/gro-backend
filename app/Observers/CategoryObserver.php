<?php

namespace App\Observers;

use App\Model\Category;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class CategoryObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Category "created" event.
     *
     * @param  \App\Model\Category  $category
     * @return void
     */
    public function created(Category $category)
    {
        $this->cacheService->invalidateCategoryCaches();
        Log::info('Category created, caches invalidated', ['category_id' => $category->id]);
    }

    /**
     * Handle the Category "updated" event.
     *
     * @param  \App\Model\Category  $category
     * @return void
     */
    public function updated(Category $category)
    {
        $this->cacheService->invalidateCategoryCaches();
        Log::info('Category updated, caches invalidated', ['category_id' => $category->id]);
    }

    /**
     * Handle the Category "deleted" event.
     *
     * @param  \App\Model\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        $this->cacheService->invalidateCategoryCaches();
        Log::info('Category deleted, caches invalidated', ['category_id' => $category->id]);
    }

    /**
     * Handle the Category "restored" event.
     *
     * @param  \App\Model\Category  $category
     * @return void
     */
    public function restored(Category $category)
    {
        $this->cacheService->invalidateCategoryCaches();
        Log::info('Category restored, caches invalidated', ['category_id' => $category->id]);
    }
}
