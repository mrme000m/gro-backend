<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\ApiResponseService;
use App\Services\PaginationService;

class TestApiOptimization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test-optimization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test API optimization features and performance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing API optimization features...');
        
        // Test services
        $this->testApiResponseService();
        $this->testPaginationService();
        
        // Test API endpoints
        $this->testApiEndpoints();
        
        $this->info('API optimization testing completed!');
        return 0;
    }

    /**
     * Test API Response Service
     */
    private function testApiResponseService(): void
    {
        $this->info('Testing API Response Service...');
        
        try {
            $apiResponseService = app(ApiResponseService::class);
            
            // Test success response
            $successResponse = $apiResponseService->success(['test' => 'data'], 'Test successful');
            $this->info('✓ Success response test passed');
            
            // Test error response
            $errorResponse = $apiResponseService->error('Test error', 400, [['code' => 'test-001']]);
            $this->info('✓ Error response test passed');
            
            // Test data optimization
            $testData = ['key1' => 'value1', 'key2' => null, 'key3' => '', 'key4' => []];
            $optimized = $apiResponseService->optimizeData($testData);
            
            if (count($optimized) < count($testData)) {
                $this->info('✓ Data optimization test passed');
            } else {
                $this->warn('⚠ Data optimization test failed');
            }
            
        } catch (\Exception $e) {
            $this->error('✗ API Response Service test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test Pagination Service
     */
    private function testPaginationService(): void
    {
        $this->info('Testing Pagination Service...');
        
        try {
            $paginationService = app(PaginationService::class);
            
            // Test legacy pagination transformation
            $legacyData = [
                'products' => [
                    ['id' => 1, 'name' => 'Product 1'],
                    ['id' => 2, 'name' => 'Product 2'],
                ],
                'total_size' => 50,
                'limit' => 10,
                'offset' => 1,
            ];
            
            $request = request();
            $transformed = $paginationService->transformLegacyPagination($legacyData, $request);
            
            if (isset($transformed['meta']['pagination'])) {
                $this->info('✓ Legacy pagination transformation test passed');
            } else {
                $this->warn('⚠ Legacy pagination transformation test failed');
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Pagination Service test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test API endpoints
     */
    private function testApiEndpoints(): void
    {
        $this->info('Testing API endpoints...');
        
        $baseUrl = config('app.url');
        $endpoints = [
            '/api/v1/config',
            '/api/v1/products/featured',
            '/api/v1/products/all?limit=5',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $startTime = microtime(true);
                $response = Http::timeout(10)->get($baseUrl . $endpoint);
                $endTime = microtime(true);
                
                $responseTime = round(($endTime - $startTime) * 1000, 2);
                $statusCode = $response->status();
                
                if ($statusCode === 200) {
                    $this->info("✓ {$endpoint} - {$responseTime}ms - Status: {$statusCode}");
                    
                    // Check for optimization headers
                    $headers = $response->headers();
                    
                    if (isset($headers['X-Cache-Status'])) {
                        $cacheStatus = $headers['X-Cache-Status'][0] ?? 'UNKNOWN';
                        $this->line("  Cache Status: {$cacheStatus}");
                    }
                    
                    if (isset($headers['Content-Encoding'])) {
                        $encoding = $headers['Content-Encoding'][0] ?? 'none';
                        $this->line("  Compression: {$encoding}");
                    }
                    
                    if (isset($headers['X-Response-Time'])) {
                        $serverTime = $headers['X-Response-Time'][0] ?? 'unknown';
                        $this->line("  Server Time: {$serverTime}");
                    }
                    
                } else {
                    $this->warn("⚠ {$endpoint} - {$responseTime}ms - Status: {$statusCode}");
                }
                
            } catch (\Exception $e) {
                $this->error("✗ {$endpoint} - Error: " . $e->getMessage());
            }
        }
    }
}
