<?php

if (!function_exists('feature_enabled')) {
    /**
     * Check if a feature is enabled
     *
     * @param string $feature
     * @return bool
     */
    function feature_enabled(string $feature): bool
    {
        return app('features')->isEnabled($feature);
    }
}

if (!function_exists('feature_category_enabled')) {
    /**
     * Check if a feature category is enabled
     *
     * @param string $category
     * @return bool
     */
    function feature_category_enabled(string $category): bool
    {
        return app('features')->isCategoryEnabled($category);
    }
}

if (!function_exists('get_enabled_features')) {
    /**
     * Get enabled features for a category
     *
     * @param string $category
     * @return array
     */
    function get_enabled_features(string $category): array
    {
        return app('features')->getEnabledFeatures($category);
    }
}

if (!function_exists('feature_class')) {
    /**
     * Get CSS class based on feature state
     *
     * @param string $feature
     * @param string $enabledClass
     * @param string $disabledClass
     * @return string
     */
    function feature_class(string $feature, string $enabledClass = '', string $disabledClass = 'hidden'): string
    {
        return feature_enabled($feature) ? $enabledClass : $disabledClass;
    }
}

if (!function_exists('feature_config')) {
    /**
     * Get feature configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function feature_config(string $key, $default = null)
    {
        return config("features.{$key}", $default);
    }
}

if (!function_exists('admin_navigation')) {
    /**
     * Get admin navigation items based on enabled features
     *
     * @return array
     */
    function admin_navigation(): array
    {
        return app('features')->getNavigationItems();
    }
}

if (!function_exists('dashboard_widgets')) {
    /**
     * Get enabled dashboard widgets
     *
     * @return array
     */
    function dashboard_widgets(): array
    {
        $widgets = [];
        
        if (feature_category_enabled('orders')) {
            $widgets['orders'] = [
                'title' => 'Orders',
                'icon' => 'fas fa-shopping-cart',
                'route' => 'admin.orders.index',
                'color' => 'primary',
            ];
        }
        
        if (feature_category_enabled('products')) {
            $widgets['products'] = [
                'title' => 'Products',
                'icon' => 'fas fa-box',
                'route' => 'admin.products.index',
                'color' => 'success',
            ];
        }
        
        if (feature_category_enabled('customers')) {
            $widgets['customers'] = [
                'title' => 'Customers',
                'icon' => 'fas fa-users',
                'route' => 'admin.customers.index',
                'color' => 'info',
            ];
        }
        
        if (feature_category_enabled('analytics')) {
            $widgets['analytics'] = [
                'title' => 'Analytics',
                'icon' => 'fas fa-chart-bar',
                'route' => 'admin.analytics.index',
                'color' => 'warning',
            ];
        }
        
        if (feature_category_enabled('marketing')) {
            $widgets['marketing'] = [
                'title' => 'Marketing',
                'icon' => 'fas fa-bullhorn',
                'route' => 'admin.marketing.index',
                'color' => 'purple',
            ];
        }
        
        if (feature_category_enabled('inventory')) {
            $widgets['inventory'] = [
                'title' => 'Inventory',
                'icon' => 'fas fa-warehouse',
                'route' => 'admin.inventory.index',
                'color' => 'secondary',
            ];
        }
        
        return $widgets;
    }
}

if (!function_exists('marketing_features')) {
    /**
     * Get enabled marketing features
     *
     * @return array
     */
    function marketing_features(): array
    {
        $features = [];
        
        if (feature_enabled('marketing.coupons')) {
            $features['coupons'] = [
                'title' => 'Coupons',
                'description' => 'Create and manage discount coupons',
                'icon' => 'fas fa-ticket-alt',
                'route' => 'admin.marketing.coupons',
            ];
        }
        
        if (feature_enabled('marketing.banners')) {
            $features['banners'] = [
                'title' => 'Banners',
                'description' => 'Manage promotional banners',
                'icon' => 'fas fa-image',
                'route' => 'admin.marketing.banners',
            ];
        }
        
        if (feature_enabled('marketing.email_campaigns')) {
            $features['email_campaigns'] = [
                'title' => 'Email Campaigns',
                'description' => 'Send targeted email campaigns',
                'icon' => 'fas fa-envelope',
                'route' => 'admin.marketing.emails',
            ];
        }
        
        if (feature_enabled('marketing.push_notifications')) {
            $features['push_notifications'] = [
                'title' => 'Push Notifications',
                'description' => 'Send push notifications to mobile users',
                'icon' => 'fas fa-bell',
                'route' => 'admin.marketing.notifications',
            ];
        }
        
        return $features;
    }
}

if (!function_exists('product_features')) {
    /**
     * Get enabled product features
     *
     * @return array
     */
    function product_features(): array
    {
        return [
            'add_products' => feature_enabled('products.add_products'),
            'edit_products' => feature_enabled('products.edit_products'),
            'categories' => feature_enabled('products.product_categories'),
            'attributes' => feature_enabled('products.product_attributes'),
            'reviews' => feature_enabled('products.product_reviews'),
            'bulk_import' => feature_enabled('products.bulk_import'),
            'seo' => feature_enabled('products.product_seo'),
        ];
    }
}

if (!function_exists('customer_features')) {
    /**
     * Get enabled customer features
     *
     * @return array
     */
    function customer_features(): array
    {
        return [
            'view_customers' => feature_enabled('customers.view_customers'),
            'customer_groups' => feature_enabled('customers.customer_groups'),
            'customer_reviews' => feature_enabled('customers.customer_reviews'),
            'customer_support' => feature_enabled('customers.customer_support'),
            'loyalty_program' => feature_enabled('customers.loyalty_program'),
        ];
    }
}

if (!function_exists('analytics_features')) {
    /**
     * Get enabled analytics features
     *
     * @return array
     */
    function analytics_features(): array
    {
        return [
            'sales_reports' => feature_enabled('analytics.sales_reports'),
            'customer_analytics' => feature_enabled('analytics.customer_analytics'),
            'product_analytics' => feature_enabled('analytics.product_analytics'),
            'financial_reports' => feature_enabled('analytics.financial_reports'),
            'export_data' => feature_enabled('analytics.export_data'),
        ];
    }
}
