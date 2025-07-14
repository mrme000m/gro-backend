<?php

return array (
  'core' => 
  array (
    'dashboard' => true,
    'authentication' => true,
    'settings' => true,
  ),
  'orders' => 
  array (
    'enabled' => true,
    'view_orders' => true,
    'manage_orders' => true,
    'order_tracking' => true,
    'order_reports' => true,
    'refunds' => true,
    'order_notifications' => true,
  ),
  'products' => 
  array (
    'enabled' => true,
    'add_products' => true,
    'edit_products' => true,
    'product_categories' => true,
    'product_attributes' => true,
    'product_reviews' => true,
    'bulk_import' => true,
    'product_seo' => true,
  ),
  'customers' => 
  array (
    'enabled' => true,
    'view_customers' => true,
    'customer_groups' => true,
    'customer_reviews' => true,
    'customer_support' => true,
    'loyalty_program' => true,
  ),
  'marketing' => 
  array (
    'enabled' => true,
    'coupons' => false,
    'discounts' => false,
    'banners' => true,
    'email_campaigns' => true,
    'push_notifications' => true,
    'social_media' => false,
    'referral_program' => false,
  ),
  'analytics' => 
  array (
    'enabled' => true,
    'sales_reports' => true,
    'customer_analytics' => true,
    'product_analytics' => true,
    'financial_reports' => true,
    'export_data' => true,
  ),
  'inventory' => 
  array (
    'enabled' => false,
    'stock_management' => false,
    'low_stock_alerts' => false,
    'supplier_management' => false,
    'purchase_orders' => false,
  ),
  'delivery' => 
  array (
    'enabled' => true,
    'delivery_zones' => true,
    'delivery_charges' => true,
    'delivery_tracking' => true,
    'delivery_partners' => true,
    'route_optimization' => true,
  ),
  'payments' => 
  array (
    'enabled' => true,
    'payment_methods' => true,
    'payment_gateways' => true,
    'transaction_logs' => true,
    'refund_management' => true,
  ),
  'content' => 
  array (
    'enabled' => true,
    'pages' => true,
    'blogs' => true,
    'faqs' => true,
    'terms_conditions' => true,
    'privacy_policy' => true,
  ),
  'system' => 
  array (
    'enabled' => true,
    'user_management' => true,
    'role_permissions' => true,
    'system_logs' => true,
    'backup_restore' => true,
    'maintenance_mode' => true,
    'api_management' => true,
  ),
  'advanced' => 
  array (
    'enabled' => true,
    'multi_vendor' => false,
    'multi_language' => false,
    'multi_currency' => false,
    'advanced_seo' => true,
    'custom_fields' => true,
    'webhooks' => true,
    'api_access' => true,
  ),
  'integrations' => 
  array (
    'enabled' => true,
    'google_analytics' => true,
    'facebook_pixel' => true,
    'whatsapp_integration' => true,
    'sms_gateway' => true,
    'accounting_software' => false,
    'crm_integration' => false,
  ),
  'mobile_app' => 
  array (
    'enabled' => true,
    'push_notifications' => true,
    'app_settings' => true,
    'app_analytics' => true,
  ),
);
