<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class FeatureService
{
    /**
     * Cache key for feature configuration
     */
    const CACHE_KEY = 'admin_features_config';

    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 60;

    /**
     * Get all features configuration
     *
     * @return array
     */
    public function getAllFeatures(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            return config('features', []);
        });
    }

    /**
     * Check if a feature is enabled
     *
     * @param string $feature Feature path (e.g., 'marketing.coupons')
     * @return bool
     */
    public function isEnabled(string $feature): bool
    {
        $features = $this->getAllFeatures();

        // Split the feature path
        $parts = explode('.', $feature);
        $current = $features;

        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                return false;
            }
            $current = $current[$part];
        }

        return (bool) $current;
    }

    /**
     * Check if a feature category is enabled
     *
     * @param string $category
     * @return bool
     */
    public function isCategoryEnabled(string $category): bool
    {
        // Core category is always enabled
        if ($category === 'core') {
            return true;
        }

        return $this->isEnabled($category . '.enabled');
    }

    /**
     * Get enabled features for a category
     *
     * @param string $category
     * @return array
     */
    public function getEnabledFeatures(string $category): array
    {
        $features = $this->getAllFeatures();

        if (!isset($features[$category])) {
            return [];
        }

        // For core category, always return enabled features since core is always enabled
        // For other categories, check if the category itself is enabled
        if ($category !== 'core' && !$this->isCategoryEnabled($category)) {
            return [];
        }

        $categoryFeatures = $features[$category];
        $enabled = [];

        foreach ($categoryFeatures as $feature => $isEnabled) {
            if ($feature !== 'enabled' && $isEnabled) {
                $enabled[] = $feature;
            }
        }

        return $enabled;
    }

    /**
     * Get navigation items based on enabled features
     *
     * @return array
     */
    public function getNavigationItems(): array
    {
        $navigation = [];

        // Dashboard (always enabled)
        $navigation[] = [
            'name' => 'Dashboard',
            'icon' => 'animated-icon icon-dashboard pulse',
            'route' => 'admin.dashboard',
            'active' => request()->routeIs('admin.dashboard'),
            'enabled' => true,
        ];

        // Orders
        if ($this->isCategoryEnabled('orders')) {
            $navigation[] = [
                'name' => 'Orders',
                'icon' => 'animated-icon icon-orders bounce',
                'route' => 'admin.orders.list',
                'route_params' => ['all'],
                'active' => request()->routeIs('admin.orders.*'),
                'enabled' => true,
                'submenu' => $this->getOrdersSubmenu(),
            ];
        }

        // Products
        if ($this->isCategoryEnabled('products')) {
            $navigation[] = [
                'name' => 'Products',
                'icon' => 'animated-icon icon-products',
                'route' => 'admin.product.list',
                'active' => request()->routeIs('admin.product.*'),
                'enabled' => true,
                'submenu' => $this->getProductsSubmenu(),
            ];
        }

        // Customers
        if ($this->isCategoryEnabled('customers')) {
            $navigation[] = [
                'name' => 'Customers',
                'icon' => 'animated-icon icon-customers',
                'route' => 'admin.customer.list',
                'active' => request()->routeIs('admin.customer.*'),
                'enabled' => true,
                'submenu' => $this->getCustomersSubmenu(),
            ];
        }

        // Marketing
        if ($this->isCategoryEnabled('marketing')) {
            $navigation[] = [
                'name' => 'Marketing',
                'icon' => 'animated-icon icon-marketing shake',
                'route' => 'admin.coupon.add-new',
                'active' => request()->routeIs('admin.coupon.*') || request()->routeIs('admin.banner.*'),
                'enabled' => true,
                'submenu' => $this->getMarketingSubmenu(),
            ];
        }

        // Analytics
        if ($this->isCategoryEnabled('analytics')) {
            $navigation[] = [
                'name' => 'Analytics',
                'icon' => 'animated-icon icon-analytics',
                'route' => 'admin.analytics.keyword-search',
                'active' => request()->routeIs('admin.analytics.*') || request()->routeIs('admin.report.*'),
                'enabled' => true,
                'submenu' => $this->getAnalyticsSubmenu(),
            ];
        }

        // Inventory
        if ($this->isCategoryEnabled('inventory')) {
            $navigation[] = [
                'name' => 'Inventory',
                'icon' => 'animated-icon icon-inventory pulse',
                'route' => 'admin.product.limited-stock',
                'active' => request()->routeIs('admin.product.limited-stock'),
                'enabled' => true,
                'submenu' => $this->getInventorySubmenu(),
            ];
        }

        // Delivery Management
        if ($this->isCategoryEnabled('delivery')) {
            $navigation[] = [
                'name' => 'Delivery',
                'icon' => 'animated-icon icon-delivery bounce',
                'route' => 'admin.delivery-man.list',
                'active' => request()->routeIs('admin.delivery-man.*'),
                'enabled' => true,
                'submenu' => $this->getDeliverySubmenu(),
            ];
        }

        // Content Management
        if ($this->isCategoryEnabled('content')) {
            $navigation[] = [
                'name' => 'Content Management',
                'icon' => 'animated-icon icon-content pulse',
                'route' => 'admin.business-settings.page-setup.about-us',
                'active' => request()->routeIs('admin.business-settings.page-setup.*') || request()->routeIs('admin.business-settings.blog.*'),
                'enabled' => true,
                'submenu' => $this->getContentSubmenu(),
            ];
        }

        // POS System
        if ($this->isEnabled('system.pos_system')) {
            $navigation[] = [
                'name' => 'POS',
                'icon' => 'animated-icon icon-pos pulse',
                'route' => 'admin.pos.index',
                'active' => request()->routeIs('admin.pos.*'),
                'enabled' => true,
            ];
        }

        // Advanced Features
        if ($this->isCategoryEnabled('advanced')) {
            $navigation[] = [
                'name' => 'Advanced',
                'icon' => 'animated-icon icon-advanced rotate',
                'route' => 'admin.business-settings.web-app.system-setup.app_setting',
                'active' => request()->routeIs('admin.business-settings.web-app.language*') || request()->routeIs('admin.business-settings.currency*'),
                'enabled' => true,
                'submenu' => $this->getAdvancedSubmenu(),
            ];
        }

        // Integrations
        if ($this->isCategoryEnabled('integrations')) {
            $navigation[] = [
                'name' => 'Integrations',
                'icon' => 'animated-icon icon-integrations bounce',
                'route' => 'admin.business-settings.web-app.third-party.google-analytics',
                'active' => request()->routeIs('admin.business-settings.web-app.third-party.*') || request()->routeIs('admin.business-settings.web-app.sms-module*'),
                'enabled' => true,
                'submenu' => $this->getIntegrationsSubmenu(),
            ];
        }

        // Mobile App
        if ($this->isCategoryEnabled('mobile_app')) {
            $navigation[] = [
                'name' => 'Mobile App',
                'icon' => 'animated-icon icon-mobile pulse',
                'route' => 'admin.business-settings.web-app.system-setup.app_setting',
                'active' => request()->routeIs('admin.business-settings.web-app.system-setup.firebase*'),
                'enabled' => true,
                'submenu' => $this->getMobileAppSubmenu(),
            ];
        }

        // Settings (always enabled)
        $navigation[] = [
            'name' => 'Settings',
            'icon' => 'animated-icon icon-settings rotate',
            'route' => 'admin.settings',
            'active' => request()->routeIs('admin.settings*') || request()->routeIs('admin.business-settings.*'),
            'enabled' => true,
            'submenu' => $this->getSettingsSubmenu(),
        ];

        return $navigation;
    }

    /**
     * Get orders submenu items
     */
    private function getOrdersSubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('orders.view_orders')) {
            $submenu[] = ['name' => 'All Orders', 'route' => 'admin.orders.list', 'params' => ['all']];
        }

        if ($this->isEnabled('orders.order_tracking')) {
            $submenu[] = ['name' => 'Pending Orders', 'route' => 'admin.orders.list', 'params' => ['pending']];
        }

        if ($this->isEnabled('orders.refunds')) {
            $submenu[] = ['name' => 'Delivered Orders', 'route' => 'admin.orders.list', 'params' => ['delivered']];
        }

        return $submenu;
    }

    /**
     * Get products submenu items
     */
    private function getProductsSubmenu(): array
    {
        $submenu = [];

        $submenu[] = ['name' => 'All Products', 'route' => 'admin.product.list'];

        if ($this->isEnabled('products.add_products')) {
            $submenu[] = ['name' => 'Add Product', 'route' => 'admin.product.add-new'];
        }

        if ($this->isEnabled('products.product_categories')) {
            $submenu[] = ['name' => 'Categories', 'route' => 'admin.category.add'];
        }

        if ($this->isEnabled('products.product_attributes')) {
            $submenu[] = ['name' => 'Attributes', 'route' => 'admin.attribute.add-new'];
        }

        if ($this->isEnabled('products.bulk_import')) {
            $submenu[] = ['name' => 'Bulk Import', 'route' => 'admin.product.bulk-import'];
        }

        return $submenu;
    }

    /**
     * Get customers submenu items
     */
    private function getCustomersSubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('customers.view_customers')) {
            $submenu[] = ['name' => 'All Customers', 'route' => 'admin.customer.list'];
        }

        if ($this->isEnabled('customers.customer_support')) {
            $submenu[] = ['name' => 'Customer Support', 'route' => 'admin.message.list'];
        }

        if ($this->isEnabled('customers.customer_wallet')) {
            $submenu[] = ['name' => 'Customer Wallet', 'route' => 'admin.customer.wallet.add-fund'];
        }

        if ($this->isEnabled('customers.wallet_bonus')) {
            $submenu[] = ['name' => 'Wallet Bonus', 'route' => 'admin.customer.wallet.bonus.index'];
        }

        if ($this->isEnabled('customers.loyalty_program')) {
            $submenu[] = ['name' => 'Loyalty Program', 'route' => 'admin.customer.loyalty-point.report'];
        }

        if ($this->isEnabled('customers.customer_reviews')) {
            $submenu[] = ['name' => 'Customer Reviews', 'route' => 'admin.reviews.list'];
        }

        return $submenu;
    }

    /**
     * Get delivery submenu items
     */
    private function getDeliverySubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('delivery.delivery_partners')) {
            $submenu[] = ['name' => 'Delivery Men', 'route' => 'admin.delivery-man.list'];
            $submenu[] = ['name' => 'Add Delivery Man', 'route' => 'admin.delivery-man.add'];
            $submenu[] = ['name' => 'Pending Applications', 'route' => 'admin.delivery-man.pending'];
            $submenu[] = ['name' => 'Delivery Reviews', 'route' => 'admin.delivery-man.reviews.list'];
        }

        return $submenu;
    }

    /**
     * Get marketing submenu items
     */
    private function getMarketingSubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('marketing.coupons')) {
            $submenu[] = ['name' => 'Coupons', 'route' => 'admin.coupon.add-new'];
        }

        if ($this->isEnabled('marketing.discounts')) {
            $submenu[] = ['name' => 'Discounts', 'route' => 'admin.discount.add-new'];
        }

        if ($this->isEnabled('marketing.banners')) {
            $submenu[] = ['name' => 'Banners', 'route' => 'admin.banner.add-new'];
        }

        if ($this->isEnabled('marketing.offers')) {
            $submenu[] = ['name' => 'Flash Deals', 'route' => 'admin.offer.flash.index'];
        }

        if ($this->isEnabled('marketing.push_notifications')) {
            $submenu[] = ['name' => 'Push Notifications', 'route' => 'admin.notification.add-new'];
        }

        return $submenu;
    }

    /**
     * Get analytics submenu items
     */
    private function getAnalyticsSubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('analytics.sales_reports')) {
            $submenu[] = ['name' => 'Sales Reports', 'route' => 'admin.report.sale-report'];
        }

        if ($this->isEnabled('analytics.customer_analytics')) {
            $submenu[] = ['name' => 'Customer Search', 'route' => 'admin.analytics.customer-search'];
        }

        if ($this->isEnabled('analytics.product_analytics')) {
            $submenu[] = ['name' => 'Keyword Search', 'route' => 'admin.analytics.keyword-search'];
        }

        return $submenu;
    }

    /**
     * Get inventory submenu items
     */
    private function getInventorySubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('inventory.stock_management')) {
            $submenu[] = ['name' => 'Stock Management', 'route' => 'admin.product.list'];
        }

        if ($this->isEnabled('inventory.low_stock_alerts')) {
            $submenu[] = ['name' => 'Low Stock Products', 'route' => 'admin.product.limited-stock'];
        }

        return $submenu;
    }



    /**
     * Get content management submenu items
     */
    private function getContentSubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('content.pages')) {
            $submenu[] = ['name' => 'About Us', 'route' => 'admin.business-settings.page-setup.about-us'];
        }

        if ($this->isEnabled('content.terms_conditions')) {
            $submenu[] = ['name' => 'Terms & Conditions', 'route' => 'admin.business-settings.page-setup.terms-and-conditions'];
        }

        if ($this->isEnabled('content.privacy_policy')) {
            $submenu[] = ['name' => 'Privacy Policy', 'route' => 'admin.business-settings.page-setup.privacy-policy'];
        }

        if ($this->isEnabled('content.faqs')) {
            $submenu[] = ['name' => 'FAQs', 'route' => 'admin.business-settings.page-setup.faq'];
        }

        if ($this->isEnabled('content.blogs')) {
            $submenu[] = ['name' => 'Blogs', 'route' => 'admin.business-settings.blog.index'];
        }

        // Always include these policy pages
        $submenu[] = ['name' => 'Cancellation Policy', 'route' => 'admin.business-settings.page-setup.cancellation-policy'];
        $submenu[] = ['name' => 'Refund Policy', 'route' => 'admin.business-settings.page-setup.refund-policy'];
        $submenu[] = ['name' => 'Return Policy', 'route' => 'admin.business-settings.page-setup.return-policy'];
        $submenu[] = ['name' => 'Social Media', 'route' => 'admin.business-settings.web-app.third-party.social-media'];

        return $submenu;
    }



    /**
     * Get advanced features submenu items
     */
    private function getAdvancedSubmenu(): array
    {
        $submenu = [];

        // Only show features that have actual implementations
        if ($this->isEnabled('advanced.multi_language')) {
            $submenu[] = ['name' => 'Multi Language', 'route' => 'admin.business-settings.web-app.system-setup.language.index'];
        }

        // Note: Multi Vendor, Multi Currency, Advanced SEO, Custom Fields, Webhooks, and API Access
        // are disabled in config as they are not implemented yet

        return $submenu;
    }

    /**
     * Get integrations submenu items
     */
    private function getIntegrationsSubmenu(): array
    {
        $submenu = [];

        // All these integrations have working admin interfaces
        if ($this->isEnabled('integrations.google_analytics')) {
            $submenu[] = ['name' => 'Google Analytics', 'route' => 'admin.business-settings.web-app.third-party.google-analytics'];
        }

        if ($this->isEnabled('integrations.facebook_pixel')) {
            $submenu[] = ['name' => 'Facebook Pixel', 'route' => 'admin.business-settings.web-app.third-party.facebook-pixel'];
        }

        if ($this->isEnabled('integrations.whatsapp_integration')) {
            $submenu[] = ['name' => 'WhatsApp Integration', 'route' => 'admin.business-settings.web-app.third-party.chat-index'];
        }

        if ($this->isEnabled('integrations.sms_gateway')) {
            $submenu[] = ['name' => 'SMS Gateway', 'route' => 'admin.business-settings.web-app.sms-module'];
        }

        if ($this->isEnabled('integrations.accounting_software')) {
            $submenu[] = ['name' => 'Accounting Software', 'route' => 'admin.business-settings.web-app.third-party.accounting-software'];
        }

        if ($this->isEnabled('integrations.crm_integration')) {
            $submenu[] = ['name' => 'CRM Integration', 'route' => 'admin.business-settings.web-app.third-party.crm-integration'];
        }

        return $submenu;
    }

    /**
     * Get mobile app submenu items
     */
    private function getMobileAppSubmenu(): array
    {
        $submenu = [];

        if ($this->isEnabled('mobile_app.push_notifications')) {
            $submenu[] = ['name' => 'Push Notifications', 'route' => 'admin.business-settings.web-app.system-setup.firebase_message_config_index'];
        }

        if ($this->isEnabled('mobile_app.app_settings')) {
            $submenu[] = ['name' => 'App Settings', 'route' => 'admin.business-settings.web-app.system-setup.app_setting'];
        }

        if ($this->isEnabled('mobile_app.app_analytics')) {
            $submenu[] = ['name' => 'App Analytics', 'route' => 'admin.analytics.keyword-search'];
        }

        return $submenu;
    }

    /**
     * Get settings submenu items
     */
    private function getSettingsSubmenu(): array
    {
        $submenu = [];

        $submenu[] = ['name' => 'General Settings', 'route' => 'admin.settings'];
        $submenu[] = ['name' => 'Business Settings', 'route' => 'admin.business-settings.store.ecom-setup'];

        // System Management Features under Settings
        if ($this->isCategoryEnabled('system')) {
            if ($this->isEnabled('system.user_management')) {
                $submenu[] = ['name' => 'Employee Management', 'route' => 'admin.employee.list'];
            }

            if ($this->isEnabled('system.role_permissions')) {
                $submenu[] = ['name' => 'Role Permissions', 'route' => 'admin.custom-role.create'];
            }

            if ($this->isEnabled('system.branches')) {
                $submenu[] = ['name' => 'Branch Management', 'route' => 'admin.branch.list'];
            }

            if ($this->isEnabled('system.time_slots')) {
                $submenu[] = ['name' => 'Time Slots', 'route' => 'admin.business-settings.store.timeSlot.add-new'];
            }

            if ($this->isEnabled('system.maintenance_mode')) {
                $submenu[] = ['name' => 'Maintenance Mode', 'route' => 'admin.business-settings.store.maintenance-mode'];
            }

            if ($this->isEnabled('system.system_addons')) {
                $submenu[] = ['name' => 'System Addons', 'route' => 'admin.system-addon.index'];
            }
        }

        $submenu[] = ['name' => 'Feature Settings', 'route' => 'admin.settings.features.index'];

        return $submenu;
    }

    /**
     * Clear feature cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Update feature configuration
     *
     * @param array $features
     * @return bool
     */
    public function updateFeatures(array $features): bool
    {
        try {
            // Validate features array structure
            if (!$this->validateFeaturesStructure($features)) {
                return false;
            }

            // Update the configuration
            $configPath = config_path('features.php');
            $configContent = "<?php\n\nreturn " . var_export($features, true) . ";\n";

            // Create backup of current config
            $backupPath = $configPath . '.backup.' . time();
            if (file_exists($configPath)) {
                copy($configPath, $backupPath);
            }

            // Write new configuration
            if (file_put_contents($configPath, $configContent) === false) {
                return false;
            }

            // Clear cache
            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Failed to update features configuration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate features array structure
     *
     * @param array $features
     * @return bool
     */
    private function validateFeaturesStructure(array $features): bool
    {
        // Check if core features exist and are properly structured
        if (!isset($features['core']) || !is_array($features['core'])) {
            return false;
        }

        // Ensure core features are always enabled
        $requiredCoreFeatures = ['dashboard', 'authentication', 'settings'];
        foreach ($requiredCoreFeatures as $feature) {
            if (!isset($features['core'][$feature]) || !$features['core'][$feature]) {
                return false;
            }
        }

        return true;
    }
}
