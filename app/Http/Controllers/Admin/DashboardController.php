<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use App\Model\Branch;
use App\Model\Category;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Review;
use App\User;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
       private Admin $admin,
       private Branch $branch,
       private Category $category,
       private Order $order,
       private OrderDetail $orderDetail,
       private Product $product,
       private Review $review,
       private User $user
    ){}

    /**
     * @param $id
     * @return string
     */
    public function fcm($id): string
    {
        $adminFcmToken = $this->admin->find(auth('admin')->id())->fcm_token;
        $data = [
            'title' => 'New auto generate message arrived from admin dashboard',
            'description' => $id,
            'order_id' => '',
            'image' => '',
            'type' => 'order'
        ];

        try {
            Helpers::send_push_notif_to_device($adminFcmToken, $data);
            return "Notification sent to admin";
        } catch (\Exception $exception) {
            return "Notification send failed";
        }
    }

    /**
     * @return Factory|View|Application
     */
    public function dashboard(): View|Factory|Application
    {
        // Optimized top selling products query with proper joins
        $topSell = $this->orderDetail
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.order_status', 'delivered')
            ->select(
                'order_details.product_id',
                'products.name as product_name',
                'products.image as product_image',
                DB::raw('SUM(order_details.quantity) as total_quantity')
            )
            ->groupBy('order_details.product_id', 'products.name', 'products.image')
            ->orderBy('total_quantity', 'desc')
            ->take(6)
            ->get();

        // Optimized most rated products query
        $mostRatedProducts = $this->review
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->where('reviews.is_active', 1)
            ->select([
                'reviews.product_id',
                'products.name as product_name',
                'products.image as product_image',
                DB::raw('AVG(reviews.rating) as ratings_average'),
                DB::raw('COUNT(reviews.rating) as total_reviews'),
            ])
            ->groupBy('reviews.product_id', 'products.name', 'products.image')
            ->orderBy('total_reviews', 'desc')
            ->orderBy('ratings_average', 'desc')
            ->take(6)
            ->get();

        // Optimized top customers query
        $topCustomer = $this->order
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'orders.user_id',
                'users.f_name',
                'users.l_name',
                'users.email',
                'users.image',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.order_amount) as total_spent')
            )
            ->whereNotNull('orders.user_id')
            ->groupBy('orders.user_id', 'users.f_name', 'users.l_name', 'users.email', 'users.image')
            ->orderBy('total_orders', 'desc')
            ->take(6)
            ->get();

        $data = self::orderStatsData();

        // Cache these counts for better performance
        $data['customer'] = Cache::remember('dashboard_customer_count', 300, function() {
            return $this->user->count();
        });
        $data['product'] = Cache::remember('dashboard_product_count', 300, function() {
            return $this->product->count();
        });
        $data['order'] = Cache::remember('dashboard_order_count', 300, function() {
            return $this->order->count();
        });
        $data['category'] = Cache::remember('dashboard_category_count', 300, function() {
            return $this->category->where('parent_id', 0)->count();
        });
        $data['branch'] = Cache::remember('dashboard_branch_count', 300, function() {
            return $this->branch->count();
        });

        // Optimized order status counts with single query
        $orderStatusCounts = $this->order
            ->select('order_status', DB::raw('COUNT(*) as count'))
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->toArray();

        $data['pending_count'] = $orderStatusCounts['pending'] ?? 0;
        $data['ongoing_count'] = ($orderStatusCounts['confirmed'] ?? 0) +
                                 ($orderStatusCounts['processing'] ?? 0) +
                                 ($orderStatusCounts['out_for_delivery'] ?? 0);
        $data['delivered_count'] = $orderStatusCounts['delivered'] ?? 0;
        $data['canceled_count'] = $orderStatusCounts['canceled'] ?? 0;
        $data['returned_count'] = $orderStatusCounts['returned'] ?? 0;
        $data['failed_count'] = $orderStatusCounts['failed'] ?? 0;

        $data['recent_orders'] = $this->order->notPos()
            ->select(['id', 'created_at', 'order_status', 'order_amount'])
            ->latest()
            ->take(5)
            ->get();


        $data['top_sell'] = $topSell;
        $data['most_rated_products'] = $mostRatedProducts;
        $data['top_customer'] = $topCustomer;

        $from = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        /*earning statistics chart*/

        $earning = [];
        $earningData = $this->order->where([
            'order_status' => 'delivered'
        ])->select(
            DB::raw('IFNULL(sum(order_amount),0) as sums'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $earning[$inc] = 0;
            foreach ($earningData as $match) {
                if ($match['month'] == $inc) {
                    $earning[$inc] = $match['sums'];
                }
            }
        }

        /*order statistics chart*/

        $orderStatisticsChart = [];
        $orderStatisticsChartData = $this->order->where(['order_status' => 'delivered'])
            ->select(
                DB::raw('(count(id)) as total'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();


        for ($inc = 1; $inc <= 12; $inc++) {
            $orderStatisticsChart[$inc] = 0;
            foreach ($orderStatisticsChartData as $match) {
                if ($match['month'] == $inc) {
                    $orderStatisticsChart[$inc] = $match['total'];
                }
            }
        }

        // Prepare data for modern dashboard
        $dashboardData = [
            'total_orders' => $data['total_orders'] ?? 0,
            'total_customers' => $data['customer'] ?? 0,
            'total_revenue' => $data['total_earning'] ?? 0,
            'total_products' => $data['product'] ?? 0,
            'recent_orders' => $data['recent_orders'] ?? [],
            'top_products' => $data['top_sell'] ?? [],
            'earning_chart' => $earning,
            'order_statistics' => $orderStatisticsChart,
        ];

        // Add breadcrumbs for modern layout
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')]
        ];

        return view('admin-views.dashboard', compact('data', 'earning', 'orderStatisticsChart', 'dashboardData', 'breadcrumbs'));
    }

    /**
     * Test method for modern dashboard
     */
    public function modernTest()
    {
        // Simple test data
        $data = [
            'total_orders' => 150,
            'customer' => 89,
            'total_earning' => 12500.50,
            'product' => 45,
            'recent_orders' => [
                [
                    'id' => 1001,
                    'customer_name' => 'John Doe',
                    'order_amount' => 125.50,
                    'order_status' => 'delivered',
                    'created_at' => now()->subHours(2)
                ],
                [
                    'id' => 1002,
                    'customer_name' => 'Jane Smith',
                    'order_amount' => 89.99,
                    'order_status' => 'processing',
                    'created_at' => now()->subHours(4)
                ]
            ],
            'top_sell' => [
                [
                    'name' => 'Fresh Apples',
                    'price' => 4.99,
                    'order_count' => 25,
                    'image' => 'def.png'
                ],
                [
                    'name' => 'Organic Bananas',
                    'price' => 3.49,
                    'order_count' => 18,
                    'image' => 'def.png'
                ]
            ]
        ];

        $earning = [];
        $orderStatisticsChart = [];
        $dashboardData = $data;
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => route('admin.dashboard')]
        ];

        return view('admin-views.dashboard', compact('data', 'earning', 'orderStatisticsChart', 'dashboardData', 'breadcrumbs'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStats(Request $request): \Illuminate\Http\JsonResponse
    {
        session()->put('statistics_type', $request['statistics_type']);
        $data = self::orderStatsData();

        return response()->json([
            'view' => view('admin-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    /**
     * @return array
     */
    public function orderStatsData(): array
    {
        $today = session()->has('statistics_type') && session('statistics_type') == 'today' ? 1 : 0;
        $thisMonth = session()->has('statistics_type') && session('statistics_type') == 'this_month' ? 1 : 0;

        $pending = $this->order->where(['order_status' => 'pending'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', \Carbon\Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
        $confirmed = $this->order->where(['order_status' => 'confirmed'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
        $processing = $this->order->where(['order_status' => 'processing'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
        $outForDelivery = $this->order->where(['order_status' => 'out_for_delivery'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
        $delivered = $this->order->where(['order_status' => 'delivered'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
        $all = $this->order->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
        $returned = $this->order->where(['order_status' => 'returned'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
        $failed = $this->order->where(['order_status' => 'failed'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $canceled = $this->order->where(['order_status' => 'canceled'])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        return $data = [
            'pending' => $pending,
            'confirmed' => $confirmed,
            'processing' => $processing,
            'out_for_delivery' => $outForDelivery,
            'delivered' => $delivered,
            'all' => $all,
            'returned' => $returned,
            'failed' => $failed,
            'canceled' => $canceled
        ];

    }

    /**
     * filter order statistics in week, month, year by ajax
     */
    public function getOrderStatistics(Request $request): \Illuminate\Http\JsonResponse
    {
        $dateType = $request->type;

        $order_data = array();
        if($dateType == 'yearOrder') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $orders = $this->order->where(['order_status' => 'delivered'])
            ->select(
                DB::raw('(count(id)) as total'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $order_data[$inc] = 0;
                foreach ($orders as $match) {
                    if ($match['month'] == $inc) {
                        $order_data[$inc] = $match['total'];
                    }
                }
            }
            $key_range = array("Jan","Feb","Mar","April","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

        }elseif($dateType == 'MonthOrder') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $number = date('d',strtotime($to));
            $key_range = range(1, $number);

            $orders = $this->order->where(['order_status' => 'delivered'])
            ->select(
                DB::raw('(count(id)) as total'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->groupby('day')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $order_data[$inc] = 0;
                foreach ($orders as $match) {
                    if ($match['day'] == $inc) {
                        $order_data[$inc] = $match['total'];
                    }
                }
            }

        }elseif($dateType == 'WeekOrder') {
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);

            $from = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
            $to = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
            $date_range = CarbonPeriod::create($from, $to)->toArray();
            $day_range = array();
            foreach($date_range as $date){
                $day_range[] =$date->format('d');
            }
            $day_range = array_flip($day_range);
            $day_range_keys = array_keys($day_range);
            $day_range_values = array_values($day_range);
            $day_range_intKeys = array_map('intval', $day_range_keys);
            $day_range = array_combine($day_range_intKeys, $day_range_values);

            $orders = $this->order->where(['order_status' => 'delivered'])
            ->select(
                DB::raw('(count(id)) as total'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->groupby('day')->orderBy('created_at', 'ASC')->pluck('total', 'day')->toArray();

            $order_data = array();
            foreach($day_range as $day=>$value){
                $day_value = 0;
                $order_data[$day] = $day_value;
            }

            foreach($orders as $order_day => $order_value){
                if(array_key_exists($order_day, $order_data)){
                    $order_data[$order_day] = $order_value;
                }
            }
            $key_range = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        }

        $label = $key_range;
        $order_data_final = $order_data;

        $data = array(
            'orders_label' => $label,
            'orders' => array_values($order_data_final),
        );
        return response()->json($data);
    }

    /**
     * filter earning statistics in week, month, year by ajax
     */
    public function getEarningStatistics(Request $request): \Illuminate\Http\JsonResponse
    {
        $dateType = $request->type;

        $earning_data = array();
        if($dateType == 'yearEarn') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $earning = $this->order->where([
                'order_status' => 'delivered'
            ])->select(
                DB::raw('IFNULL(sum(order_amount),0) as sums'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $earning_data[$inc] = 0;
                foreach ($earning as $match) {
                    if ($match['month'] == $inc) {
                        $earning_data[$inc] = $match['sums'];
                    }
                }
            }
            $key_range = array("Jan","Feb","Mar","April","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");


        }elseif($dateType == 'MonthEarn') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $number = date('d',strtotime($to));
            $key_range = range(1, $number);

            $earning = $this->order->where([
                'order_status' => 'delivered'
            ])->select(
                DB::raw('IFNULL(sum(order_amount),0) as sums'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->groupby('day')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $earning_data[$inc] = 0;
                foreach ($earning as $match) {
                    if ($match['day'] == $inc) {
                        $earning_data[$inc] = $match['sums'];
                    }
                }
            }

        }elseif($dateType == 'WeekEarn') {
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);

            $from = Carbon::now()->startOfWeek()->format('Y-m-d 00:00:00');
            $to = Carbon::now()->endOfWeek()->format('Y-m-d 23:59:59');
            $date_range = CarbonPeriod::create($from, $to)->toArray();
            $day_range = array();
            foreach($date_range as $date){
                $day_range[] =$date->format('d');
            }
            $day_range = array_flip($day_range);
            $day_range_keys = array_keys($day_range);
            $day_range_values = array_values($day_range);
            $day_range_intKeys = array_map('intval', $day_range_keys);
            $day_range = array_combine($day_range_intKeys, $day_range_values);

            $earning = $this->order->where([
                'order_status' => 'delivered'
            ])->select(
                DB::raw('IFNULL(sum(order_amount),0) as sums'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->groupby('day')->orderBy('created_at', 'ASC')->pluck('sums', 'day')->toArray();

            $earning_data = array();
            foreach($day_range as $day=>$value){
                $day_value = 0;
                $earning_data[$day] = $day_value;
            }

            foreach($earning as $order_day => $order_value){
                if(array_key_exists($order_day, $earning_data)){
                    $earning_data[$order_day] = $order_value;
                }
            }

            $key_range = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        }

        $label = $key_range;
        $earning_data_final = $earning_data;

        $data = array(
            'earning_label' => $label,
            'earning' => array_values($earning_data_final),
        );
        return response()->json($data);
    }

    /**
     * Get dashboard statistics for AJAX updates
     */
    public function getStats()
    {
        $data = [];
        $data['total_orders'] = \App\Model\Order::count();
        $data['customer'] = \App\Model\User::count();
        $data['product'] = \App\Model\Product::count();
        $data['total_earning'] = \App\Model\Order::where('order_status', 'delivered')->sum('order_amount');

        // Calculate percentage changes (you can implement proper logic here)
        $data['orders_change'] = rand(-20, 30); // Simulated for now
        $data['customers_change'] = rand(-10, 25);
        $data['products_change'] = rand(-5, 15);
        $data['revenue_change'] = rand(-15, 35);

        return response()->json($data);
    }

}
