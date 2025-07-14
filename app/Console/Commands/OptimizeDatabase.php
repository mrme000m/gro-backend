<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize {--analyze : Run ANALYZE TABLE on all tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize database performance by analyzing tables and updating statistics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting database optimization...');

        // Get all table names
        $tables = $this->getAllTables();

        if ($this->option('analyze')) {
            $this->analyzeAllTables($tables);
        }

        $this->optimizeQueries();
        $this->showOptimizationTips();

        $this->info('Database optimization completed!');
        return 0;
    }

    /**
     * Get all table names
     *
     * @return array
     */
    private function getAllTables(): array
    {
        $tables = [];
        $results = DB::select('SHOW TABLES');
        
        foreach ($results as $result) {
            $tableName = array_values((array) $result)[0];
            $tables[] = $tableName;
        }

        return $tables;
    }

    /**
     * Analyze all tables to update statistics
     *
     * @param array $tables
     * @return void
     */
    private function analyzeAllTables(array $tables): void
    {
        $this->info('Analyzing tables to update statistics...');
        
        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        foreach ($tables as $table) {
            try {
                DB::statement("ANALYZE TABLE `{$table}`");
                $bar->advance();
            } catch (\Exception $e) {
                $this->warn("Failed to analyze table {$table}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Table analysis completed.');
    }

    /**
     * Optimize common queries
     *
     * @return void
     */
    private function optimizeQueries(): void
    {
        $this->info('Checking query optimization opportunities...');

        // Check for missing indexes on frequently queried columns
        $this->checkMissingIndexes();
        
        // Check for slow queries
        $this->checkSlowQueries();
    }

    /**
     * Check for missing indexes
     *
     * @return void
     */
    private function checkMissingIndexes(): void
    {
        $recommendations = [];

        // Check orders table
        if (!$this->hasIndex('orders', 'order_status')) {
            $recommendations[] = "Add index on orders.order_status";
        }
        
        if (!$this->hasIndex('orders', 'branch_id')) {
            $recommendations[] = "Add index on orders.branch_id";
        }

        if (!$this->hasIndex('orders', 'user_id')) {
            $recommendations[] = "Add index on orders.user_id";
        }

        // Check products table
        if (!$this->hasIndex('products', 'status')) {
            $recommendations[] = "Add index on products.status";
        }

        if (!$this->hasIndex('products', 'is_featured')) {
            $recommendations[] = "Add index on products.is_featured";
        }

        // Check reviews table
        if (!$this->hasIndex('reviews', 'product_id')) {
            $recommendations[] = "Add index on reviews.product_id";
        }

        if (!$this->hasIndex('reviews', 'is_active')) {
            $recommendations[] = "Add index on reviews.is_active";
        }

        if (!empty($recommendations)) {
            $this->warn('Missing indexes detected:');
            foreach ($recommendations as $recommendation) {
                $this->line('  - ' . $recommendation);
            }
            $this->info('Run: php artisan migrate to add performance indexes');
        } else {
            $this->info('All recommended indexes are present.');
        }
    }

    /**
     * Check if an index exists on a table column
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function hasIndex(string $table, string $column): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check for slow queries
     *
     * @return void
     */
    private function checkSlowQueries(): void
    {
        try {
            // Check if slow query log is enabled
            $slowLogStatus = DB::select("SHOW VARIABLES LIKE 'slow_query_log'");
            
            if (empty($slowLogStatus) || $slowLogStatus[0]->Value !== 'ON') {
                $this->warn('Slow query log is not enabled. Consider enabling it for query optimization.');
                return;
            }

            $this->info('Slow query log is enabled. Check your slow query log file for optimization opportunities.');
        } catch (\Exception $e) {
            $this->warn('Could not check slow query log status: ' . $e->getMessage());
        }
    }

    /**
     * Show optimization tips
     *
     * @return void
     */
    private function showOptimizationTips(): void
    {
        $this->newLine();
        $this->info('Database Optimization Tips:');
        $this->line('1. Run this command regularly to keep statistics updated');
        $this->line('2. Monitor slow queries and add indexes as needed');
        $this->line('3. Use Redis caching for frequently accessed data');
        $this->line('4. Consider query result caching for expensive operations');
        $this->line('5. Use eager loading to prevent N+1 query problems');
        $this->line('6. Optimize your queries to select only needed columns');
        $this->line('7. Use database query logging in development to identify bottlenecks');
        
        $this->newLine();
        $this->info('Performance Monitoring Commands:');
        $this->line('- Enable query logging: DB::enableQueryLog()');
        $this->line('- View executed queries: DB::getQueryLog()');
        $this->line('- Use Laravel Debugbar for query analysis');
        $this->line('- Monitor with: php artisan telescope:install (for detailed query analysis)');
    }
}
