<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixMigrations extends Command
{
    protected $signature = 'db:fix-migrations';
    protected $description = 'Fix migration conflicts by marking problematic migrations as run';

    public function handle()
    {
        $this->info('🔧 Fixing migration conflicts...');

        try {
            // Ensure migrations table exists
            if (!Schema::hasTable('migrations')) {
                $this->info('Creating migrations table...');
                DB::unprepared("
                    CREATE TABLE `migrations` (
                        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `migration` varchar(255) NOT NULL,
                        `batch` int(11) NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            // List of problematic migrations that should be marked as run
            $problematicMigrations = [
                '2021_03_07_065637_change_product_image_clumn_type',
                '2021_03_11_061437_add_unit_column_to_products',
                '2021_03_11_070016_add_unit_to_order_details',
                '2021_04_08_093406_add_capacity_to_products',
                '2021_04_21_081459_add_stock_to_products',
                '2021_04_21_094959_add_stock_info_to_order_details',
                '2021_07_01_160828_add_col_daily_needs_products',
                '2022_06_09_095348_add_popularity_count_to_products_table',
                '2023_02_23_150149_add_is_featured_in_products_table',
                '2023_02_26_144956_add_maximum_order_quantity_in_products_table'
            ];

            $batch = 1;
            $markedCount = 0;

            foreach ($problematicMigrations as $migration) {
                // Check if migration is already marked as run
                $exists = DB::table('migrations')
                    ->where('migration', $migration)
                    ->exists();

                if (!$exists) {
                    // Check if the table/column changes already exist
                    $shouldMark = false;

                    if (str_contains($migration, 'products')) {
                        if (Schema::hasTable('products')) {
                            $shouldMark = true;
                            
                            // Check specific columns
                            if (str_contains($migration, 'unit') && Schema::hasColumn('products', 'unit')) {
                                $shouldMark = true;
                            }
                            if (str_contains($migration, 'capacity') && Schema::hasColumn('products', 'capacity')) {
                                $shouldMark = true;
                            }
                            if (str_contains($migration, 'stock') && Schema::hasColumn('products', 'total_stock')) {
                                $shouldMark = true;
                            }
                            if (str_contains($migration, 'daily_needs') && Schema::hasColumn('products', 'daily_needs')) {
                                $shouldMark = true;
                            }
                            if (str_contains($migration, 'popularity_count') && Schema::hasColumn('products', 'popularity_count')) {
                                $shouldMark = true;
                            }
                            if (str_contains($migration, 'is_featured') && Schema::hasColumn('products', 'is_featured')) {
                                $shouldMark = true;
                            }
                            if (str_contains($migration, 'maximum_order_quantity') && Schema::hasColumn('products', 'maximum_order_quantity')) {
                                $shouldMark = true;
                            }
                        }
                    }

                    if ($shouldMark) {
                        DB::table('migrations')->insert([
                            'migration' => $migration,
                            'batch' => $batch
                        ]);
                        $markedCount++;
                        $this->info("✅ Marked migration as run: {$migration}");
                    }
                }
            }

            $this->info("✅ Fixed {$markedCount} migration conflicts");
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing migrations: ' . $e->getMessage());
            return 1;
        }
    }
}
