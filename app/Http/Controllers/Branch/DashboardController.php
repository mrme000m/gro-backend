<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Review;
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
        private Order $order,
        private OrderDetail $orderDetail,
        private Review $review
    ){}

    /**
     * @return Factory|View|Application
     */
    public function dashboard(): View|Factory|Application
    {
        $branchId = auth('branch')->id();

        // Optimized top selling products query for branch
        $topSell = $this->orderDetail
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.order_status', 'delivered')
            ->select(
                'order_details.product_id',
                'products.name as product_name',
                'products.image as product_image',
                DB::raw('SUM(order_details.quantity) as total_quantity')
            )
            ->groupBy('order_details.product_id', 'products.name', 'products.image')
            ->orderBy('total_quantity', 'desc')
            ->take(5)
            ->get();

        // Optimized most rated products query for branch products
        $mostRatedProducts = $this->review
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->join('order_details', 'reviews.product_id', '=', 'order_details.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('reviews.is_active', 1)
            ->select([
                'reviews.product_id',
                'products.name as product_name',
                'products.image as product_image',
                DB::raw('AVG(reviews.rating) as ratings_average'),
                DB::raw('COUNT(DISTINCT reviews.id) as total_reviews'),
            ])
            ->groupBy('reviews.product_id', 'products.name', 'products.image')
            ->orderBy('total_reviews', 'desc')
            ->orderBy('ratings_average', 'desc')
            ->take(5)
            ->get();

        // Optimized top customers query for branch
        $topCustomer = $this->order
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.branch_id', $branchId)
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
            ->take(5)
            ->get();

        $data = self::orderStatsData();

        $data['top_sell'] = $topSell;
        $data['most_rated_products'] = $mostRatedProducts;
        $data['top_customer'] = $topCustomer;

        // Optimized order status counts with single query for branch
        $orderStatusCounts = $this->order
            ->where('branch_id', $branchId)
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

        $data['recent_orders'] = $this->order->notPos()->where('branch_id', auth('branch')->id())->latest()->take(5)->get(['id', 'created_at', 'order_status']);

        $from = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        $earning = [];
        $earningData = $this->order->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])->select(
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

        $orderStatisticsChart = [];
        $orderStatisticsChartData = $this->order->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
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

        return view('branch-views.dashboard', compact('data', 'earning', 'orderStatisticsChart'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStats(Request $request): JsonResponse
    {
        session()->put('statistics_type', $request['statistics_type']);
        $data = self::orderStatsData();

        return response()->json([
            'view' => view('branch-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    /**
     * @return array
     */
    public function orderStatsData(): array
    {
        $statusCounts = $this->order
            ->where('branch_id', auth('branch')->id())
            ->when(session()->has('statistics_type') && session('statistics_type') == 'today', function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when(session()->has('statistics_type') && session('statistics_type') == 'this_month', function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->select('order_status', DB::raw('COUNT(*) as count'))
            ->groupBy('order_status')
            ->get()
            ->pluck('count', 'order_status')
            ->toArray();

        $statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'returned', 'failed', 'canceled'];

        $data = [];
        foreach ($statuses as $status) {
            $data[$status] = $statusCounts[$status] ?? 0;
        }
        $data['all'] = array_sum($data);

        return $data;
    }

    public function getOrderStatistics(Request $request): JsonResponse
    {
        $dateType = $request->type;

        $order_data = array();
        if($dateType == 'yearOrder') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $orders = $this->order->where(['order_status' => 'delivered', 'branch_id'=>auth('branch')->id()])
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

            $orders = $this->order->where(['order_status' => 'delivered', 'branch_id'=>auth('branch')->id()])
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

            $orders = $this->order->where(['order_status' => 'delivered', 'branch_id'=>auth('branch')->id()])
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
    public function getEarningStatistics(Request $request): JsonResponse
    {
        $dateType = $request->type;

        $earning_data = array();
        if($dateType == 'yearEarn') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $earning = $this->order->where(['order_status' => 'delivered', 'branch_id'=>auth('branch')->id()])
            ->select(
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
                'order_status' => 'delivered', 'branch_id'=>auth('branch')->id()
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
                'order_status' => 'delivered', 'branch_id'=>auth('branch')->id()
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

}
