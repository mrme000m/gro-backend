<?php

namespace App\Providers;

use App\Services\FeatureService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class FeatureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the FeatureService as a singleton
        $this->app->singleton(FeatureService::class, function ($app) {
            return new FeatureService();
        });

        // Register alias for easier access
        $this->app->alias(FeatureService::class, 'features');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Blade directives for feature checking
        $this->registerBladeDirectives();
        
        // Share navigation data with all admin views
        $this->shareNavigationData();
        
        // Register view composers
        $this->registerViewComposers();
    }

    /**
     * Register custom Blade directives for feature management
     */
    private function registerBladeDirectives(): void
    {
        // @feature('marketing.coupons') ... @endfeature
        Blade::directive('feature', function ($expression) {
            return "<?php if(app('features')->isEnabled($expression)): ?>";
        });

        Blade::directive('endfeature', function () {
            return "<?php endif; ?>";
        });

        // @featureCategory('marketing') ... @endfeatureCategory
        Blade::directive('featureCategory', function ($expression) {
            return "<?php if(app('features')->isCategoryEnabled($expression)): ?>";
        });

        Blade::directive('endfeatureCategory', function () {
            return "<?php endif; ?>";
        });

        // @hasFeature('marketing.coupons', 'Show this content', 'Show this instead')
        Blade::directive('hasFeature', function ($expression) {
            return "<?php echo app('features')->isEnabled($expression) ? 'enabled' : 'disabled'; ?>";
        });

        // @featureClass('marketing.coupons', 'enabled-class', 'disabled-class')
        Blade::directive('featureClass', function ($expression) {
            $parts = explode(',', $expression);
            $feature = trim($parts[0]);
            $enabledClass = isset($parts[1]) ? trim($parts[1], " '\"") : '';
            $disabledClass = isset($parts[2]) ? trim($parts[2], " '\"") : 'hidden';
            
            return "<?php echo app('features')->isEnabled($feature) ? '$enabledClass' : '$disabledClass'; ?>";
        });
    }

    /**
     * Share navigation data with admin views
     */
    private function shareNavigationData(): void
    {
        View::composer('layouts.admin.*', function ($view) {
            $featureService = app(FeatureService::class);
            $view->with('navigationItems', $featureService->getNavigationItems());
        });
    }

    /**
     * Register view composers for feature-specific data
     */
    private function registerViewComposers(): void
    {
        // Dashboard composer
        View::composer('admin-views.dashboard', function ($view) {
            $featureService = app(FeatureService::class);
            
            $dashboardFeatures = [
                'showOrdersWidget' => $featureService->isCategoryEnabled('orders'),
                'showProductsWidget' => $featureService->isCategoryEnabled('products'),
                'showCustomersWidget' => $featureService->isCategoryEnabled('customers'),
                'showAnalyticsWidget' => $featureService->isCategoryEnabled('analytics'),
                'showMarketingWidget' => $featureService->isCategoryEnabled('marketing'),
                'showInventoryWidget' => $featureService->isCategoryEnabled('inventory'),
            ];
            
            $view->with('dashboardFeatures', $dashboardFeatures);
        });

        // Marketing views composer
        View::composer('admin-views.marketing.*', function ($view) {
            $featureService = app(FeatureService::class);
            
            $marketingFeatures = [
                'coupons' => $featureService->isEnabled('marketing.coupons'),
                'banners' => $featureService->isEnabled('marketing.banners'),
                'emailCampaigns' => $featureService->isEnabled('marketing.email_campaigns'),
                'pushNotifications' => $featureService->isEnabled('marketing.push_notifications'),
                'socialMedia' => $featureService->isEnabled('marketing.social_media'),
                'referralProgram' => $featureService->isEnabled('marketing.referral_program'),
            ];
            
            $view->with('marketingFeatures', $marketingFeatures);
        });

        // Products views composer
        View::composer('admin-views.products.*', function ($view) {
            $featureService = app(FeatureService::class);
            
            $productFeatures = [
                'addProducts' => $featureService->isEnabled('products.add_products'),
                'editProducts' => $featureService->isEnabled('products.edit_products'),
                'categories' => $featureService->isEnabled('products.product_categories'),
                'attributes' => $featureService->isEnabled('products.product_attributes'),
                'reviews' => $featureService->isEnabled('products.product_reviews'),
                'bulkImport' => $featureService->isEnabled('products.bulk_import'),
                'seo' => $featureService->isEnabled('products.product_seo'),
            ];
            
            $view->with('productFeatures', $productFeatures);
        });
    }
}
