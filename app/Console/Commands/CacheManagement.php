<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:manage 
                            {action : The action to perform (clear, stats, warm, flush)}
                            {--store= : Specific cache store to target}
                            {--pattern= : Pattern for selective clearing (Redis only)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application caches with advanced options';

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $store = $this->option('store');
        $pattern = $this->option('pattern');

        switch ($action) {
            case 'clear':
                return $this->clearCache($store, $pattern);
            case 'stats':
                return $this->showCacheStats();
            case 'warm':
                return $this->warmCache();
            case 'flush':
                return $this->flushAllCaches();
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Available actions: clear, stats, warm, flush');
                return 1;
        }
    }

    /**
     * Clear cache with options
     *
     * @param string|null $store
     * @param string|null $pattern
     * @return int
     */
    private function clearCache(?string $store = null, ?string $pattern = null): int
    {
        try {
            if ($store && $pattern) {
                $this->info("Clearing cache pattern '{$pattern}' from store '{$store}'...");
                $this->clearCacheByPattern($store, $pattern);
            } elseif ($store) {
                $this->info("Clearing all cache from store '{$store}'...");
                Cache::store($store)->flush();
            } else {
                $this->info('Clearing all application caches...');
                $this->cacheService->clearAllCaches();
            }

            $this->info('Cache cleared successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show cache statistics
     *
     * @return int
     */
    private function showCacheStats(): int
    {
        try {
            $this->info('Cache Statistics:');
            $this->newLine();

            $stats = $this->cacheService->getCacheStats();

            if (empty($stats)) {
                $this->warn('No cache statistics available.');
                return 0;
            }

            $headers = ['Store', 'Memory Used', 'Keys Count'];
            $rows = [];

            foreach ($stats as $store => $data) {
                $rows[] = [
                    $store,
                    $data['memory_used'],
                    $data['keys_count']
                ];
            }

            $this->table($headers, $rows);
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to get cache stats: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Warm up cache with frequently accessed data
     *
     * @return int
     */
    private function warmCache(): int
    {
        try {
            $this->info('Warming up cache...');
            $bar = $this->output->createProgressBar(6);
            $bar->start();

            // Warm up business settings
            $this->cacheService->getAllBusinessSettings();
            $bar->advance();

            // Warm up categories
            $this->cacheService->getActiveCategories(true);
            $bar->advance();

            // Warm up featured products
            $this->cacheService->getFeaturedProducts(20);
            $bar->advance();

            // Warm up daily need products
            $this->cacheService->getDailyNeedProducts(20);
            $bar->advance();

            // Warm up branches
            $this->cacheService->getActiveBranches();
            $bar->advance();

            // Warm up common business settings
            $commonSettings = [
                'currency_symbol', 'currency_symbol_position', 'decimal_point_settings',
                'company_name', 'company_logo', 'delivery_charge', 'minimum_order_value'
            ];
            
            foreach ($commonSettings as $setting) {
                $this->cacheService->getBusinessSetting($setting);
            }
            $bar->advance();

            $bar->finish();
            $this->newLine();
            $this->info('Cache warmed up successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to warm cache: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Flush all caches
     *
     * @return int
     */
    private function flushAllCaches(): int
    {
        try {
            $this->warn('This will clear ALL application caches!');
            
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }

            $this->info('Flushing all caches...');
            $this->cacheService->clearAllCaches();
            
            // Also clear Laravel's built-in caches
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('route:clear');
            $this->call('view:clear');

            $this->info('All caches flushed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to flush caches: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Clear cache by pattern (Redis only)
     *
     * @param string $store
     * @param string $pattern
     * @return void
     */
    private function clearCacheByPattern(string $store, string $pattern): void
    {
        try {
            $redis = Cache::store($store)->getRedis();
            $keys = $redis->keys($pattern);
            
            if (!empty($keys)) {
                $redis->del($keys);
                $this->info(count($keys) . ' keys cleared.');
            } else {
                $this->info('No keys found matching the pattern.');
            }
        } catch (\Exception $e) {
            $this->error('Failed to clear cache by pattern: ' . $e->getMessage());
        }
    }
}
