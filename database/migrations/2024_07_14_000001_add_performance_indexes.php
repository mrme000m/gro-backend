<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add indexes for orders table
        Schema::table('orders', function (Blueprint $table) {
            // Index for order status queries
            if (!$this->indexExists('orders', 'idx_orders_status')) {
                $table->index('order_status', 'idx_orders_status');
            }
            
            // Index for branch queries
            if (!$this->indexExists('orders', 'idx_orders_branch_id')) {
                $table->index('branch_id', 'idx_orders_branch_id');
            }
            
            // Index for user queries
            if (!$this->indexExists('orders', 'idx_orders_user_id')) {
                $table->index('user_id', 'idx_orders_user_id');
            }
            
            // Composite index for branch + status queries
            if (!$this->indexExists('orders', 'idx_orders_branch_status')) {
                $table->index(['branch_id', 'order_status'], 'idx_orders_branch_status');
            }
            
            // Index for created_at for date range queries
            if (!$this->indexExists('orders', 'idx_orders_created_at')) {
                $table->index('created_at', 'idx_orders_created_at');
            }
            
            // Index for order_type (pos/delivery)
            if (!$this->indexExists('orders', 'idx_orders_type')) {
                $table->index('order_type', 'idx_orders_type');
            }
            
            // Index for checked status
            if (!$this->indexExists('orders', 'idx_orders_checked')) {
                $table->index('checked', 'idx_orders_checked');
            }
        });

        // Add indexes for order_details table
        Schema::table('order_details', function (Blueprint $table) {
            // Index for product queries
            if (!$this->indexExists('order_details', 'idx_order_details_product_id')) {
                $table->index('product_id', 'idx_order_details_product_id');
            }
            
            // Index for order queries
            if (!$this->indexExists('order_details', 'idx_order_details_order_id')) {
                $table->index('order_id', 'idx_order_details_order_id');
            }
        });

        // Add indexes for products table
        Schema::table('products', function (Blueprint $table) {
            // Index for status queries
            if (!$this->indexExists('products', 'idx_products_status')) {
                $table->index('status', 'idx_products_status');
            }
            
            // Index for featured products
            if (!$this->indexExists('products', 'idx_products_featured')) {
                $table->index('is_featured', 'idx_products_featured');
            }
            
            // Index for daily needs
            if (!$this->indexExists('products', 'idx_products_daily_needs')) {
                $table->index('daily_needs', 'idx_products_daily_needs');
            }
            
            // Index for created_at for sorting
            if (!$this->indexExists('products', 'idx_products_created_at')) {
                $table->index('created_at', 'idx_products_created_at');
            }
        });

        // Add indexes for reviews table
        Schema::table('reviews', function (Blueprint $table) {
            // Index for product queries
            if (!$this->indexExists('reviews', 'idx_reviews_product_id')) {
                $table->index('product_id', 'idx_reviews_product_id');
            }
            
            // Index for user queries
            if (!$this->indexExists('reviews', 'idx_reviews_user_id')) {
                $table->index('user_id', 'idx_reviews_user_id');
            }
            
            // Index for active reviews
            if (!$this->indexExists('reviews', 'idx_reviews_active')) {
                $table->index('is_active', 'idx_reviews_active');
            }
            
            // Composite index for product + active status
            if (!$this->indexExists('reviews', 'idx_reviews_product_active')) {
                $table->index(['product_id', 'is_active'], 'idx_reviews_product_active');
            }
        });

        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            // Index for email queries
            if (!$this->indexExists('users', 'idx_users_email')) {
                $table->index('email', 'idx_users_email');
            }
            
            // Index for phone queries
            if (!$this->indexExists('users', 'idx_users_phone')) {
                $table->index('phone', 'idx_users_phone');
            }
            
            // Index for blocked status
            if (!$this->indexExists('users', 'idx_users_blocked')) {
                $table->index('is_block', 'idx_users_blocked');
            }
        });

        // Add indexes for categories table
        Schema::table('categories', function (Blueprint $table) {
            // Index for parent_id queries
            if (!$this->indexExists('categories', 'idx_categories_parent_id')) {
                $table->index('parent_id', 'idx_categories_parent_id');
            }
            
            // Index for status queries
            if (!$this->indexExists('categories', 'idx_categories_status')) {
                $table->index('status', 'idx_categories_status');
            }
        });

        // Add indexes for wishlists table
        Schema::table('wishlists', function (Blueprint $table) {
            // Index for user queries
            if (!$this->indexExists('wishlists', 'idx_wishlists_user_id')) {
                $table->index('user_id', 'idx_wishlists_user_id');
            }
            
            // Index for product queries
            if (!$this->indexExists('wishlists', 'idx_wishlists_product_id')) {
                $table->index('product_id', 'idx_wishlists_product_id');
            }
            
            // Composite index for user + product
            if (!$this->indexExists('wishlists', 'idx_wishlists_user_product')) {
                $table->index(['user_id', 'product_id'], 'idx_wishlists_user_product');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes for orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_branch_id');
            $table->dropIndex('idx_orders_user_id');
            $table->dropIndex('idx_orders_branch_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_type');
            $table->dropIndex('idx_orders_checked');
        });

        // Drop indexes for order_details table
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('idx_order_details_product_id');
            $table->dropIndex('idx_order_details_order_id');
        });

        // Drop indexes for products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status');
            $table->dropIndex('idx_products_featured');
            $table->dropIndex('idx_products_daily_needs');
            $table->dropIndex('idx_products_created_at');
        });

        // Drop indexes for reviews table
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_product_id');
            $table->dropIndex('idx_reviews_user_id');
            $table->dropIndex('idx_reviews_active');
            $table->dropIndex('idx_reviews_product_active');
        });

        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_phone');
            $table->dropIndex('idx_users_blocked');
        });

        // Drop indexes for categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_parent_id');
            $table->dropIndex('idx_categories_status');
        });

        // Drop indexes for wishlists table
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex('idx_wishlists_user_id');
            $table->dropIndex('idx_wishlists_product_id');
            $table->dropIndex('idx_wishlists_user_product');
        });
    }

    /**
     * Check if an index exists on a table
     *
     * @param string $table
     * @param string $index
     * @return bool
     */
    private function indexExists($table, $index)
    {
        $connection = Schema::getConnection();
        $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
        $doctrineTable = $doctrineSchemaManager->listTableDetails($table);
        
        return $doctrineTable->hasIndex($index);
    }
}
