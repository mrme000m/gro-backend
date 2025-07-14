<?php

namespace App\Observers;

use App\Model\BusinessSetting;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class BusinessSettingObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the BusinessSetting "created" event.
     *
     * @param  \App\Model\BusinessSetting  $businessSetting
     * @return void
     */
    public function created(BusinessSetting $businessSetting)
    {
        $this->cacheService->invalidateSettingsCaches($businessSetting->key);
        Log::info('Business setting created, caches invalidated', ['key' => $businessSetting->key]);
    }

    /**
     * Handle the BusinessSetting "updated" event.
     *
     * @param  \App\Model\BusinessSetting  $businessSetting
     * @return void
     */
    public function updated(BusinessSetting $businessSetting)
    {
        $this->cacheService->invalidateSettingsCaches($businessSetting->key);
        Log::info('Business setting updated, caches invalidated', ['key' => $businessSetting->key]);
    }

    /**
     * Handle the BusinessSetting "deleted" event.
     *
     * @param  \App\Model\BusinessSetting  $businessSetting
     * @return void
     */
    public function deleted(BusinessSetting $businessSetting)
    {
        $this->cacheService->invalidateSettingsCaches($businessSetting->key);
        Log::info('Business setting deleted, caches invalidated', ['key' => $businessSetting->key]);
    }

    /**
     * Handle the BusinessSetting "restored" event.
     *
     * @param  \App\Model\BusinessSetting  $businessSetting
     * @return void
     */
    public function restored(BusinessSetting $businessSetting)
    {
        $this->cacheService->invalidateSettingsCaches($businessSetting->key);
        Log::info('Business setting restored, caches invalidated', ['key' => $businessSetting->key]);
    }
}
