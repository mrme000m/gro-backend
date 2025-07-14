<?php

namespace App\Services;

use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Review;
use App\Model\Branch;
use App\Model\Category;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get optimized dashboard statistics for admin
     *
     * @return array
     */
    public function getAdminDashboardStats(): array
    {
        $cacheKey = 'admin_dashboard_stats_' . Carbon::now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 3600, function () {
            return [
                'top_selling_products' => $this->getTopSellingProducts(),
                'most_rated_products' => $this->getMostRatedProducts(),
                'top_customers' => $this->getTopCustomers(),
                'order_status_counts' => $this->getOrderStatusCounts(),
                'basic_counts' => $this->getBasicCounts(),
                'recent_orders' => $this->getRecentOrders(),
                'monthly_earnings' => $this->getMonthlyEarnings(),
                'monthly_order_stats' => $this->getMonthlyOrderStats(),
            ];
        });
    }

    /**
     * Get optimized dashboard statistics for branch
     *
     * @param int $branchId
     * @return array
     */
    public function getBranchDashboardStats(int $branchId): array
    {
        $cacheKey = 'branch_dashboard_stats_' . $branchId . '_' . Carbon::now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 3600, function () use ($branchId) {
            return [
                'top_selling_products' => $this->getTopSellingProducts($branchId),
                'most_rated_products' => $this->getMostRatedProducts($branchId),
                'top_customers' => $this->getTopCustomers($branchId),
                'order_status_counts' => $this->getOrderStatusCounts($branchId),
                'recent_orders' => $this->getRecentOrders($branchId),
                'monthly_earnings' => $this->getMonthlyEarnings($branchId),
                'monthly_order_stats' => $this->getMonthlyOrderStats($branchId),
            ];
        });
    }

    /**
     * Get top selling products
     *
     * @param int|null $branchId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getTopSellingProducts(?int $branchId = null, int $limit = 6): \Illuminate\Support\Collection
    {
        $query = OrderDetail::query()
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.order_status', 'delivered');

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query->select([
                'order_details.product_id',
                'products.name as product_name',
                'products.image as product_image',
                'products.price as product_price',
                DB::raw('SUM(order_details.quantity) as total_quantity'),
                DB::raw('SUM(order_details.price * order_details.quantity) as total_revenue')
            ])
            ->groupBy('order_details.product_id', 'products.name', 'products.image', 'products.price')
            ->orderBy('total_quantity', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get most rated products
     *
     * @param int|null $branchId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getMostRatedProducts(?int $branchId = null, int $limit = 6): \Illuminate\Support\Collection
    {
        $query = Review::query()
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->where('reviews.is_active', 1);

        if ($branchId) {
            $query->join('order_details', 'reviews.product_id', '=', 'order_details.product_id')
                  ->join('orders', 'order_details.order_id', '=', 'orders.id')
                  ->where('orders.branch_id', $branchId);
        }

        return $query->select([
                'reviews.product_id',
                'products.name as product_name',
                'products.image as product_image',
                'products.price as product_price',
                DB::raw('AVG(reviews.rating) as average_rating'),
                DB::raw('COUNT(DISTINCT reviews.id) as total_reviews'),
            ])
            ->groupBy('reviews.product_id', 'products.name', 'products.image', 'products.price')
            ->orderBy('total_reviews', 'desc')
            ->orderBy('average_rating', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get top customers
     *
     * @param int|null $branchId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getTopCustomers(?int $branchId = null, int $limit = 6): \Illuminate\Support\Collection
    {
        $query = Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereNotNull('orders.user_id');

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query->select([
                'orders.user_id',
                'users.f_name',
                'users.l_name',
                'users.email',
                'users.image',
                'users.phone',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.order_amount) as total_spent'),
                DB::raw('AVG(orders.order_amount) as average_order_value')
            ])
            ->groupBy('orders.user_id', 'users.f_name', 'users.l_name', 'users.email', 'users.image', 'users.phone')
            ->orderBy('total_orders', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get order status counts
     *
     * @param int|null $branchId
     * @return array
     */
    private function getOrderStatusCounts(?int $branchId = null): array
    {
        $query = Order::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $statusCounts = $query->select('order_status', DB::raw('COUNT(*) as count'))
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->toArray();

        return [
            'pending' => $statusCounts['pending'] ?? 0,
            'confirmed' => $statusCounts['confirmed'] ?? 0,
            'processing' => $statusCounts['processing'] ?? 0,
            'out_for_delivery' => $statusCounts['out_for_delivery'] ?? 0,
            'delivered' => $statusCounts['delivered'] ?? 0,
            'canceled' => $statusCounts['canceled'] ?? 0,
            'returned' => $statusCounts['returned'] ?? 0,
            'failed' => $statusCounts['failed'] ?? 0,
            'ongoing' => ($statusCounts['confirmed'] ?? 0) + 
                        ($statusCounts['processing'] ?? 0) + 
                        ($statusCounts['out_for_delivery'] ?? 0),
        ];
    }

    /**
     * Get basic counts (admin only)
     *
     * @return array
     */
    private function getBasicCounts(): array
    {
        return [
            'customers' => User::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'categories' => Category::where('parent_id', 0)->count(),
            'branches' => Branch::count(),
        ];
    }

    /**
     * Get recent orders
     *
     * @param int|null $branchId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getRecentOrders(?int $branchId = null, int $limit = 5): \Illuminate\Support\Collection
    {
        $query = Order::query()->notPos();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->select(['id', 'created_at', 'order_status', 'order_amount', 'user_id'])
            ->with(['customer:id,f_name,l_name,email'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get monthly earnings
     *
     * @param int|null $branchId
     * @return array
     */
    private function getMonthlyEarnings(?int $branchId = null): array
    {
        $from = Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        $query = Order::query()
            ->where('order_status', 'delivered')
            ->whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $earningData = $query->select([
                DB::raw('IFNULL(SUM(order_amount), 0) as total'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month')
            ])
            ->groupBy('year', 'month')
            ->get()
            ->keyBy('month')
            ->toArray();

        $earnings = [];
        for ($month = 1; $month <= 12; $month++) {
            $earnings[$month] = $earningData[$month]['total'] ?? 0;
        }

        return $earnings;
    }

    /**
     * Get monthly order statistics
     *
     * @param int|null $branchId
     * @return array
     */
    private function getMonthlyOrderStats(?int $branchId = null): array
    {
        $from = Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        $query = Order::query()
            ->where('order_status', 'delivered')
            ->whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orderData = $query->select([
                DB::raw('COUNT(id) as total'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month')
            ])
            ->groupBy('year', 'month')
            ->get()
            ->keyBy('month')
            ->toArray();

        $orders = [];
        for ($month = 1; $month <= 12; $month++) {
            $orders[$month] = $orderData[$month]['total'] ?? 0;
        }

        return $orders;
    }

    /**
     * Clear dashboard cache
     *
     * @param int|null $branchId
     * @return void
     */
    public function clearDashboardCache(?int $branchId = null): void
    {
        $currentHour = Carbon::now()->format('Y-m-d-H');
        
        if ($branchId) {
            Cache::forget('branch_dashboard_stats_' . $branchId . '_' . $currentHour);
        } else {
            Cache::forget('admin_dashboard_stats_' . $currentHour);
        }

        // Clear related caches
        Cache::forget('dashboard_customer_count');
        Cache::forget('dashboard_product_count');
        Cache::forget('dashboard_order_count');
        Cache::forget('dashboard_category_count');
        Cache::forget('dashboard_branch_count');
    }
}
