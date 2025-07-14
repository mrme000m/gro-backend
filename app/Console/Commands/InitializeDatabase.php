<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InitializeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:initialize {--force : Force initialization even if tables exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize database with base schema for GroFresh Restaurant Bazar';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Initializing GroFresh Database...');

        // Check if database is already initialized
        if (!$this->option('force') && $this->isDatabaseInitialized()) {
            $this->info('✅ Database already initialized (core tables exist)');
            return 0;
        }

        // Import base schema
        if ($this->importBaseSchema()) {
            $this->info('✅ Base schema imported successfully');
        } else {
            $this->error('❌ Failed to import base schema');
            return 1;
        }

        $this->info('🎉 Database initialization complete!');
        return 0;
    }

    /**
     * Check if database is already initialized
     */
    private function isDatabaseInitialized(): bool
    {
        try {
            // Check for key tables that indicate the database is set up
            $keyTables = ['products', 'categories', 'users', 'admins', 'business_settings'];
            
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

    /**
     * Import base schema from SQL file
     */
    private function importBaseSchema(): bool
    {
        $schemaFile = base_path('installation/v4.1.sql');
        
        if (!file_exists($schemaFile)) {
            $this->error("❌ Base schema file not found: {$schemaFile}");
            return false;
        }

        try {
            $this->info("📥 Importing base schema from installation/v4.1.sql...");
            
            // Read and execute SQL file
            $sql = file_get_contents($schemaFile);
            
            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($statement) {
                    return !empty($statement) && !str_starts_with($statement, '--');
                }
            );

            DB::beginTransaction();
            
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    DB::unprepared($statement);
                }
            }
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error importing schema: " . $e->getMessage());
            return false;
        }
    }
}
