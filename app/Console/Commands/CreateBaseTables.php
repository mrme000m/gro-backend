<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBaseTables extends Command
{
    protected $signature = 'db:create-base-tables';
    protected $description = 'Create essential base tables for GroFresh';

    public function handle()
    {
        $this->info('🚀 Creating essential base tables...');

        try {
            // Create products table (the one causing migration issues)
            if (!Schema::hasTable('products')) {
                $this->info('Creating products table...');
                DB::unprepared("
                    CREATE TABLE `products` (
                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `name` varchar(80) DEFAULT NULL,
                        `description` text DEFAULT NULL,
                        `image` varchar(255) DEFAULT NULL,
                        `price` decimal(24,2) NOT NULL DEFAULT 0.00,
                        `variations` text DEFAULT NULL,
                        `add_ons` varchar(255) DEFAULT NULL,
                        `tax` decimal(24,2) NOT NULL DEFAULT 0.00,
                        `available_time_starts` time DEFAULT NULL,
                        `available_time_ends` time DEFAULT NULL,
                        `status` tinyint(1) NOT NULL DEFAULT 1,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        `attributes` varchar(255) DEFAULT NULL,
                        `category_ids` varchar(255) DEFAULT NULL,
                        `choice_options` text DEFAULT NULL,
                        `discount` decimal(24,2) NOT NULL DEFAULT 0.00,
                        `discount_type` varchar(20) NOT NULL DEFAULT 'percent',
                        `tax_type` varchar(20) NOT NULL DEFAULT 'percent',
                        `capacity` double(8,2) DEFAULT NULL,
                        `total_stock` int(11) DEFAULT NULL,
                        `daily_needs` tinyint(1) NOT NULL DEFAULT 0,
                        `popularity_count` int(11) NOT NULL DEFAULT 0,
                        `maximum_order_quantity` int(11) DEFAULT NULL,
                        `is_featured` tinyint(1) NOT NULL DEFAULT 0,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            // Create categories table
            if (!Schema::hasTable('categories')) {
                $this->info('Creating categories table...');
                DB::unprepared("
                    CREATE TABLE `categories` (
                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `name` varchar(100) NOT NULL,
                        `image` varchar(100) NOT NULL,
                        `parent_id` int(11) NOT NULL,
                        `position` int(11) NOT NULL,
                        `status` tinyint(1) NOT NULL DEFAULT 1,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        `priority` int(11) NOT NULL DEFAULT 0,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            // Create business_settings table
            if (!Schema::hasTable('business_settings')) {
                $this->info('Creating business_settings table...');
                DB::unprepared("
                    CREATE TABLE `business_settings` (
                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `key` varchar(191) NOT NULL,
                        `value` longtext DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            // Create users table
            if (!Schema::hasTable('users')) {
                $this->info('Creating users table...');
                DB::unprepared("
                    CREATE TABLE `users` (
                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `f_name` varchar(100) DEFAULT NULL,
                        `l_name` varchar(100) DEFAULT NULL,
                        `phone` varchar(20) DEFAULT NULL,
                        `email` varchar(100) DEFAULT NULL,
                        `image` varchar(100) DEFAULT NULL,
                        `is_phone_verified` tinyint(1) NOT NULL DEFAULT 0,
                        `email_verified_at` timestamp NULL DEFAULT NULL,
                        `password` varchar(100) NOT NULL,
                        `remember_token` varchar(100) DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        `interest` varchar(255) DEFAULT NULL,
                        `cm_firebase_token` varchar(255) DEFAULT NULL,
                        `status` tinyint(1) NOT NULL DEFAULT 1,
                        `order_count` int(11) NOT NULL DEFAULT 0,
                        `login_medium` varchar(191) DEFAULT NULL,
                        `social_id` varchar(191) DEFAULT NULL,
                        `is_block` tinyint(1) NOT NULL DEFAULT 0,
                        `wallet_balance` decimal(24,2) NOT NULL DEFAULT 0.00,
                        `loyalty_point` decimal(24,2) NOT NULL DEFAULT 0.00,
                        `ref_code` varchar(191) DEFAULT NULL,
                        `referred_by` bigint(20) UNSIGNED DEFAULT NULL,
                        `language_code` varchar(191) NOT NULL DEFAULT 'en',
                        `login_hit_count` tinyint(4) NOT NULL DEFAULT 0,
                        `is_temp_blocked` tinyint(1) NOT NULL DEFAULT 0,
                        `temp_block_time` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `users_phone_unique` (`phone`),
                        UNIQUE KEY `users_email_unique` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            // Create admins table
            if (!Schema::hasTable('admins')) {
                $this->info('Creating admins table...');
                DB::unprepared("
                    CREATE TABLE `admins` (
                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `f_name` varchar(100) DEFAULT NULL,
                        `l_name` varchar(100) DEFAULT NULL,
                        `phone` varchar(20) DEFAULT NULL,
                        `email` varchar(100) NOT NULL,
                        `image` varchar(100) DEFAULT NULL,
                        `password` varchar(100) NOT NULL,
                        `remember_token` varchar(100) DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        `fcm_token` varchar(255) DEFAULT NULL,
                        `role_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                        `status` tinyint(1) NOT NULL DEFAULT 1,
                        `identity_image` varchar(255) DEFAULT NULL,
                        `identity_type` varchar(255) DEFAULT NULL,
                        `identity_number` varchar(255) DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `admins_email_unique` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            $this->info('✅ Essential base tables created successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error creating base tables: ' . $e->getMessage());
            return 1;
        }
    }
}
