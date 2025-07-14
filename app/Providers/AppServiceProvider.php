<?php

namespace App\Providers;

use App\Model\BusinessSetting;
use App\Model\Category;
use App\Model\Product;
use App\Observers\BusinessSettingObserver;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Services\CacheService;
use App\Services\ApiResponseService;
use App\Services\PaginationService;
use App\Traits\SystemAddonTrait;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    use SystemAddonTrait;
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register CacheService as singleton
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });

        // Register ApiResponseService as singleton
        $this->app->singleton(ApiResponseService::class, function ($app) {
            return new ApiResponseService();
        });

        // Register PaginationService as singleton
        $this->app->singleton(PaginationService::class, function ($app) {
            return new PaginationService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //for system addon
        Config::set('addon_admin_routes',$this->get_addon_admin_routes());
        Config::set('get_payment_publish_status',$this->get_payment_publish_status());

        // Register model observers for cache invalidation
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        BusinessSetting::observe(BusinessSettingObserver::class);

        try {
            $timezone = BusinessSetting::where(['key' => 'time_zone'])->first();
            if (isset($timezone)) {
                config(['app.timezone' => $timezone->value]);
                date_default_timezone_set($timezone->value);
            }
        }catch(\Exception $exception){}
        Paginator::useBootstrap();

    }
}
