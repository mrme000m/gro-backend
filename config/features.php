<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Toggle Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file allows you to enable/disable features in the
    | admin dashboard. Set any feature to false to hide it from the UI
    | while keeping all the underlying code intact.
    |
    */

    // Core Features (Always enabled - cannot be disabled)
    'core' => [
        'dashboard' => true,
        'authentication' => true,
        'settings' => true,
    ],

    // Order Management
    'orders' => [
        'enabled' => true,
        'view_orders' => true,
        'manage_orders' => true,
        'order_tracking' => true,
        'order_reports' => true,
        'refunds' => true,
        'order_notifications' => true,
    ],

    // Product Management
    'products' => [
        'enabled' => true,
        'add_products' => true,
        'edit_products' => true,
        'product_categories' => true,
        'product_attributes' => true,
        'product_reviews' => true,
        'bulk_import' => true,
        'product_seo' => true,
    ],

    // Customer Management
    'customers' => [
        'enabled' => true,
        'view_customers' => true,
        'customer_groups' => true,
        'customer_reviews' => true,
        'customer_support' => true,
        'loyalty_program' => true,
    ],

    // Marketing Features
    'marketing' => [
        'enabled' => false,  // Disabled for testing
        'coupons' => false,  // Disabled for testing
        'discounts' => true,
        'banners' => false,  // Disabled for testing
        'email_campaigns' => true,
        'push_notifications' => true,
        'social_media' => true,
        'referral_program' => true,
    ],

    // Analytics & Reports
    'analytics' => [
        'enabled' => true,
        'sales_reports' => true,
        'customer_analytics' => true,
        'product_analytics' => true,
        'financial_reports' => true,
        'export_data' => true,
    ],

    // Inventory Management
    'inventory' => [
        'enabled' => false,  // Disabled for testing
        'stock_management' => true,
        'low_stock_alerts' => true,
        'supplier_management' => true,
        'purchase_orders' => true,
    ],

    // Delivery & Logistics
    'delivery' => [
        'enabled' => true,
        'delivery_zones' => true,
        'delivery_charges' => true,
        'delivery_tracking' => true,
        'delivery_partners' => true,
        'route_optimization' => true,
    ],

    // Payment Features
    'payments' => [
        'enabled' => true,
        'payment_methods' => true,
        'payment_gateways' => true,
        'transaction_logs' => true,
        'refund_management' => true,
    ],

    // Content Management
    'content' => [
        'enabled' => true,
        'pages' => true,
        'blogs' => true,
        'faqs' => true,
        'terms_conditions' => true,
        'privacy_policy' => true,
    ],

    // System Features
    'system' => [
        'enabled' => true,
        'user_management' => true,
        'role_permissions' => true,
        'system_logs' => true,
        'backup_restore' => true,
        'maintenance_mode' => true,
        'api_management' => true,
    ],

    // Advanced Features
    'advanced' => [
        'enabled' => false, // Disabled by default for basic clients
        'multi_vendor' => false,
        'multi_language' => false,
        'multi_currency' => false,
        'advanced_seo' => false,
        'custom_fields' => false,
        'webhooks' => false,
        'api_access' => false,
    ],

    // Third-party Integrations
    'integrations' => [
        'enabled' => true,
        'google_analytics' => true,
        'facebook_pixel' => true,
        'whatsapp_integration' => true,
        'sms_gateway' => true,
        'accounting_software' => false,
        'crm_integration' => false,
    ],

    // Mobile App Features (if applicable)
    'mobile_app' => [
        'enabled' => false,
        'push_notifications' => false,
        'app_settings' => false,
        'app_analytics' => false,
    ],
];
