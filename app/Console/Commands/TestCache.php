<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\CacheService;

class TestCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test cache configuration and functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing cache configuration...');
        
        // Test basic cache functionality
        $this->testBasicCache();
        
        // Test cache service
        $this->testCacheService();
        
        // Test different cache stores
        $this->testCacheStores();
        
        $this->info('Cache testing completed!');
        return 0;
    }

    /**
     * Test basic cache functionality
     */
    private function testBasicCache(): void
    {
        $this->info('Testing basic cache functionality...');
        
        try {
            // Test default cache
            Cache::put('test_key', 'test_value', 60);
            $value = Cache::get('test_key');
            
            if ($value === 'test_value') {
                $this->info('✓ Basic cache test passed');
            } else {
                $this->error('✗ Basic cache test failed');
            }
            
            Cache::forget('test_key');
        } catch (\Exception $e) {
            $this->error('✗ Basic cache test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test cache service
     */
    private function testCacheService(): void
    {
        $this->info('Testing cache service...');
        
        try {
            $cacheService = app(CacheService::class);
            
            // Test business settings cache
            $setting = $cacheService->getBusinessSetting('currency_symbol');
            $this->info('✓ Business settings cache test passed');
            
            // Test categories cache
            $categories = $cacheService->getActiveCategories();
            $this->info('✓ Categories cache test passed');
            
        } catch (\Exception $e) {
            $this->error('✗ Cache service test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test different cache stores
     */
    private function testCacheStores(): void
    {
        $this->info('Testing cache stores...');
        
        $stores = ['products', 'users', 'orders', 'settings', 'api'];
        
        foreach ($stores as $store) {
            try {
                Cache::store($store)->put("test_{$store}", "value_{$store}", 60);
                $value = Cache::store($store)->get("test_{$store}");
                
                if ($value === "value_{$store}") {
                    $this->info("✓ {$store} store test passed");
                } else {
                    $this->warn("⚠ {$store} store test failed - value mismatch");
                }
                
                Cache::store($store)->forget("test_{$store}");
            } catch (\Exception $e) {
                $this->error("✗ {$store} store test failed: " . $e->getMessage());
            }
        }
    }
}
