<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\QueueService;
use Carbon\Carbon;

class ProcessDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $operationType;
    protected $data;
    protected $options;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour for heavy data processing

    /**
     * Create a new job instance.
     *
     * @param string $operationType
     * @param array $data
     * @param array $options
     */
    public function __construct(string $operationType, array $data, array $options = [])
    {
        $this->operationType = $operationType;
        $this->data = $data;
        $this->options = $options;
        
        // Set queue for data processing
        $this->onQueue(QueueService::QUEUE_DATA_PROCESSING);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $startTime = microtime(true);
            
            Log::info('Starting data processing', [
                'operation' => $this->operationType,
                'data_count' => count($this->data),
                'options' => $this->options
            ]);

            $result = $this->processData();
            
            $executionTime = microtime(true) - $startTime;
            
            Log::info('Data processing completed', [
                'operation' => $this->operationType,
                'processed_count' => $result['processed'] ?? 0,
                'success_count' => $result['success'] ?? 0,
                'error_count' => $result['errors'] ?? 0,
                'execution_time' => round($executionTime, 2) . 's'
            ]);

        } catch (\Exception $e) {
            Log::error('Data processing failed', [
                'operation' => $this->operationType,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Process data based on operation type
     *
     * @return array
     */
    private function processData(): array
    {
        return match ($this->operationType) {
            'bulk_product_update' => $this->bulkProductUpdate(),
            'bulk_price_update' => $this->bulkPriceUpdate(),
            'inventory_sync' => $this->inventorySync(),
            'customer_data_cleanup' => $this->customerDataCleanup(),
            'order_data_aggregation' => $this->orderDataAggregation(),
            'analytics_calculation' => $this->analyticsCalculation(),
            'cache_warming' => $this->cacheWarming(),
            default => throw new \InvalidArgumentException('Unknown operation type: ' . $this->operationType),
        };
    }

    /**
     * Bulk product update
     *
     * @return array
     */
    private function bulkProductUpdate(): array
    {
        $results = ['processed' => 0, 'success' => 0, 'errors' => 0];
        
        DB::beginTransaction();
        
        try {
            foreach ($this->data as $productData) {
                $results['processed']++;
                
                try {
                    $product = \App\Model\Product::find($productData['id']);
                    
                    if ($product) {
                        $product->update($productData);
                        $results['success']++;
                    } else {
                        Log::warning('Product not found for update', ['id' => $productData['id']]);
                        $results['errors']++;
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Failed to update product', [
                        'id' => $productData['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    $results['errors']++;
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $results;
    }

    /**
     * Bulk price update
     *
     * @return array
     */
    private function bulkPriceUpdate(): array
    {
        $results = ['processed' => 0, 'success' => 0, 'errors' => 0];
        $updateType = $this->options['update_type'] ?? 'percentage'; // 'percentage' or 'fixed'
        $value = $this->options['value'] ?? 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($this->data as $productId) {
                $results['processed']++;
                
                try {
                    $product = \App\Model\Product::find($productId);
                    
                    if ($product) {
                        $newPrice = $updateType === 'percentage' 
                            ? $product->price * (1 + $value / 100)
                            : $product->price + $value;
                        
                        $product->update(['price' => max(0, $newPrice)]);
                        $results['success']++;
                    } else {
                        $results['errors']++;
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Failed to update product price', [
                        'id' => $productId,
                        'error' => $e->getMessage()
                    ]);
                    $results['errors']++;
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $results;
    }

    /**
     * Inventory synchronization
     *
     * @return array
     */
    private function inventorySync(): array
    {
        $results = ['processed' => 0, 'success' => 0, 'errors' => 0];
        
        foreach ($this->data as $inventoryData) {
            $results['processed']++;
            
            try {
                $product = \App\Model\Product::find($inventoryData['product_id']);
                
                if ($product) {
                    $product->update([
                        'total_stock' => $inventoryData['stock'],
                        'updated_at' => now()
                    ]);
                    $results['success']++;
                } else {
                    $results['errors']++;
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to sync inventory', [
                    'product_id' => $inventoryData['product_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $results['errors']++;
            }
        }
        
        return $results;
    }

    /**
     * Customer data cleanup
     *
     * @return array
     */
    private function customerDataCleanup(): array
    {
        $results = ['processed' => 0, 'success' => 0, 'errors' => 0];
        $daysOld = $this->options['days_old'] ?? 365;
        
        try {
            // Clean up inactive customers
            $cutoffDate = Carbon::now()->subDays($daysOld);
            
            $inactiveCustomers = DB::table('users')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('users.created_at', '<', $cutoffDate)
                ->whereNull('orders.id')
                ->select('users.id')
                ->get();
            
            foreach ($inactiveCustomers as $customer) {
                $results['processed']++;
                
                try {
                    // Anonymize customer data instead of deleting
                    DB::table('users')
                        ->where('id', $customer->id)
                        ->update([
                            'f_name' => 'Deleted',
                            'l_name' => 'User',
                            'email' => 'deleted_' . $customer->id . '@example.com',
                            'phone' => null,
                            'updated_at' => now()
                        ]);
                    
                    $results['success']++;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to cleanup customer data', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage()
                    ]);
                    $results['errors']++;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Customer data cleanup failed', ['error' => $e->getMessage()]);
            throw $e;
        }
        
        return $results;
    }

    /**
     * Order data aggregation
     *
     * @return array
     */
    private function orderDataAggregation(): array
    {
        $results = ['processed' => 0, 'success' => 0, 'errors' => 0];
        
        try {
            $startDate = $this->options['start_date'] ?? Carbon::now()->subMonth()->format('Y-m-d');
            $endDate = $this->options['end_date'] ?? Carbon::now()->format('Y-m-d');
            
            // Aggregate daily sales data
            $dailySales = DB::table('orders')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(order_amount) as total_amount'),
                    DB::raw('AVG(order_amount) as average_amount')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('order_status', 'delivered')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get();
            
            foreach ($dailySales as $dayData) {
                $results['processed']++;
                
                try {
                    DB::table('daily_sales_aggregates')->updateOrInsert(
                        ['date' => $dayData->date],
                        [
                            'order_count' => $dayData->order_count,
                            'total_amount' => $dayData->total_amount,
                            'average_amount' => $dayData->average_amount,
                            'updated_at' => now()
                        ]
                    );
                    
                    $results['success']++;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to aggregate daily sales', [
                        'date' => $dayData->date,
                        'error' => $e->getMessage()
                    ]);
                    $results['errors']++;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Order data aggregation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
        
        return $results;
    }

    /**
     * Analytics calculation
     *
     * @return array
     */
    private function analyticsCalculation(): array
    {
        $results = ['processed' => 0, 'success' => 0, 'errors' => 0];
        
        try {
            // Calculate product popularity scores
            $productStats = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->select(
                    'order_details.product_id',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(order_details.quantity) as total_quantity'),
                    DB::raw('AVG(order_details.quantity) as avg_quantity')
                )
                ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('order_details.product_id')
                ->get();
            
            foreach ($productStats as $stats) {
                $results['processed']++;
                
                try {
                    // Calculate popularity score (weighted formula)
                    $popularityScore = ($stats->order_count * 0.4) + ($stats->total_quantity * 0.6);
                    
                    DB::table('products')
                        ->where('id', $stats->product_id)
                        ->update([
                            'popularity_score' => $popularityScore,
                            'updated_at' => now()
                        ]);
                    
                    $results['success']++;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to calculate product analytics', [
                        'product_id' => $stats->product_id,
                        'error' => $e->getMessage()
                    ]);
                    $results['errors']++;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Analytics calculation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
        
        return $results;
    }

    /**
     * Cache warming
     *
     * @return array
     */
    private function cacheWarming(): array
    {
        $results = ['processed' => 0, 'success' => 0, 'errors' => 0];
        
        $cacheKeys = $this->data;
        
        foreach ($cacheKeys as $cacheKey) {
            $results['processed']++;
            
            try {
                // Warm specific cache based on key type
                match ($cacheKey) {
                    'featured_products' => $this->warmFeaturedProductsCache(),
                    'categories' => $this->warmCategoriesCache(),
                    'popular_products' => $this->warmPopularProductsCache(),
                    'dashboard_stats' => $this->warmDashboardStatsCache(),
                    default => Log::warning('Unknown cache key', ['key' => $cacheKey])
                };
                
                $results['success']++;
                
            } catch (\Exception $e) {
                Log::error('Failed to warm cache', [
                    'cache_key' => $cacheKey,
                    'error' => $e->getMessage()
                ]);
                $results['errors']++;
            }
        }
        
        return $results;
    }

    /**
     * Warm featured products cache
     */
    private function warmFeaturedProductsCache(): void
    {
        $featuredProducts = \App\Model\Product::where('is_featured', 1)
            ->where('status', 1)
            ->with(['category'])
            ->limit(20)
            ->get();
        
        cache()->put('featured_products', $featuredProducts, 3600);
    }

    /**
     * Warm categories cache
     */
    private function warmCategoriesCache(): void
    {
        $categories = \App\Model\Category::where('status', 1)
            ->orderBy('priority', 'ASC')
            ->get();
        
        cache()->put('active_categories', $categories, 3600);
    }

    /**
     * Warm popular products cache
     */
    private function warmPopularProductsCache(): void
    {
        $popularProducts = \App\Model\Product::where('status', 1)
            ->orderBy('popularity_score', 'desc')
            ->limit(20)
            ->get();
        
        cache()->put('popular_products', $popularProducts, 3600);
    }

    /**
     * Warm dashboard stats cache
     */
    private function warmDashboardStatsCache(): void
    {
        $dashboardService = app(\App\Services\DashboardService::class);
        $stats = $dashboardService->getAdminDashboardStats();
        
        // Cache is already handled in the service, this just triggers the calculation
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Data processing job failed permanently', [
            'operation' => $this->operationType,
            'data_count' => count($this->data),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
