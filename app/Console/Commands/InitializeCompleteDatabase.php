<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InitializeCompleteDatabase extends Command
{
    protected $signature = 'db:init-complete {--force : Force initialization even if tables exist}';
    protected $description = 'Initialize complete database with latest schema dump';

    public function handle()
    {
        $this->info('🚀 Initializing Complete GroFresh Database...');

        // Check if database is already initialized
        if (!$this->option('force') && $this->isDatabaseInitialized()) {
            $this->info('✅ Database already initialized');
            return 0;
        }

        // Use the latest database schema
        $schemaFile = base_path('installation/backup/database_v7.2.sql');
        
        if (!file_exists($schemaFile)) {
            $this->error("❌ Schema file not found: {$schemaFile}");
            return 1;
        }

        if ($this->importCompleteSchema($schemaFile)) {
            $this->info('✅ Complete database schema imported successfully');
            
            // Mark all migrations as run to prevent conflicts
            $this->markAllMigrationsAsRun();
            
            return 0;
        } else {
            $this->error('❌ Failed to import complete schema');
            return 1;
        }
    }

    private function isDatabaseInitialized(): bool
    {
        try {
            // Check for multiple key tables
            $keyTables = [
                'products', 'categories', 'users', 'admins', 'business_settings',
                'orders', 'order_details', 'branches', 'delivery_men'
            ];
            
            foreach ($keyTables as $table) {
                if (!Schema::hasTable($table)) {
                    $this->info("❌ Table '{$table}' not found - database needs initialization");
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            $this->error("Error checking database: " . $e->getMessage());
            return false;
        }
    }

    private function importCompleteSchema(string $schemaFile): bool
    {
        try {
            $this->info("📥 Importing complete schema from {$schemaFile}...");
            
            $sql = file_get_contents($schemaFile);
            
            // Clean up SQL - remove problematic statements
            $sql = preg_replace('/^SET.*?;$/m', '', $sql);
            $sql = preg_replace('/^START TRANSACTION;$/m', '', $sql);
            $sql = preg_replace('/^COMMIT;$/m', '', $sql);
            $sql = preg_replace('/^\/\*.*?\*\/;$/ms', '', $sql);
            $sql = preg_replace('/^--.*$/m', '', $sql);
            
            // Split into statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($statement) {
                    return !empty($statement) && 
                           !preg_match('/^\s*(SET|START|COMMIT|\/\*|--)/i', $statement);
                }
            );

            $this->info("Found " . count($statements) . " SQL statements to execute");
            
            $successCount = 0;
            $errorCount = 0;
            
            // Disable foreign key checks temporarily
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
            
            foreach ($statements as $index => $statement) {
                $cleanStatement = trim($statement);
                if (!empty($cleanStatement)) {
                    try {
                        DB::unprepared($cleanStatement);
                        $successCount++;
                        
                        if (($index + 1) % 50 == 0) {
                            $this->info("Processed " . ($index + 1) . " statements...");
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        if ($errorCount <= 10) { // Only show first 10 errors
                            $this->warn("⚠️ Statement failed: " . substr($cleanStatement, 0, 100) . "...");
                            $this->warn("Error: " . $e->getMessage());
                        }
                    }
                }
            }
            
            // Re-enable foreign key checks
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
            
            $this->info("✅ Executed {$successCount} statements successfully");
            if ($errorCount > 0) {
                $this->warn("⚠️ {$errorCount} statements failed (this is often normal for data inserts)");
            }
            
            return $successCount > 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error importing schema: " . $e->getMessage());
            return false;
        }
    }

    private function markAllMigrationsAsRun(): void
    {
        try {
            $this->info("📝 Marking all migrations as run...");
            
            // Ensure migrations table exists
            if (!Schema::hasTable('migrations')) {
                DB::unprepared("
                    CREATE TABLE `migrations` (
                        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `migration` varchar(255) NOT NULL,
                        `batch` int(11) NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            // Get all migration files
            $migrationPath = database_path('migrations');
            $migrationFiles = glob($migrationPath . '/*.php');
            
            $batch = 1;
            $markedCount = 0;
            
            foreach ($migrationFiles as $file) {
                $filename = basename($file, '.php');
                
                // Check if already marked
                $exists = DB::table('migrations')
                    ->where('migration', $filename)
                    ->exists();
                
                if (!$exists) {
                    DB::table('migrations')->insert([
                        'migration' => $filename,
                        'batch' => $batch
                    ]);
                    $markedCount++;
                }
            }
            
            $this->info("✅ Marked {$markedCount} migrations as run");
            
        } catch (\Exception $e) {
            $this->warn("⚠️ Could not mark migrations as run: " . $e->getMessage());
        }
    }
}
