<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\QueueService;
use App\Jobs\SendEmailJob;
use Carbon\Carbon;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    protected $parameters;
    protected $requestedBy;
    protected $notifyEmail;

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
    public $timeout = 1800; // 30 minutes for large reports

    /**
     * Create a new job instance.
     *
     * @param string $reportType
     * @param array $parameters
     * @param int $requestedBy
     * @param string|null $notifyEmail
     */
    public function __construct(string $reportType, array $parameters, int $requestedBy, ?string $notifyEmail = null)
    {
        $this->reportType = $reportType;
        $this->parameters = $parameters;
        $this->requestedBy = $requestedBy;
        $this->notifyEmail = $notifyEmail;
        
        // Set queue for reports
        $this->onQueue(QueueService::QUEUE_REPORTS);
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
            
            Log::info('Starting report generation', [
                'type' => $this->reportType,
                'requested_by' => $this->requestedBy,
                'parameters' => $this->parameters
            ]);

            $reportData = $this->generateReportData();
            $filePath = $this->saveReportFile($reportData);
            
            $executionTime = microtime(true) - $startTime;
            
            Log::info('Report generated successfully', [
                'type' => $this->reportType,
                'file_path' => $filePath,
                'execution_time' => round($executionTime, 2) . 's',
                'data_rows' => count($reportData)
            ]);

            // Send notification email if requested
            if ($this->notifyEmail) {
                $this->sendReportNotification($filePath);
            }

        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'type' => $this->reportType,
                'requested_by' => $this->requestedBy,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Generate report data based on type
     *
     * @return array
     */
    private function generateReportData(): array
    {
        return match ($this->reportType) {
            'sales_report' => $this->generateSalesReport(),
            'product_report' => $this->generateProductReport(),
            'customer_report' => $this->generateCustomerReport(),
            'order_report' => $this->generateOrderReport(),
            'inventory_report' => $this->generateInventoryReport(),
            'delivery_report' => $this->generateDeliveryReport(),
            default => throw new \InvalidArgumentException('Unknown report type: ' . $this->reportType),
        };
    }

    /**
     * Generate sales report
     *
     * @return array
     */
    private function generateSalesReport(): array
    {
        $startDate = $this->parameters['start_date'] ?? Carbon::now()->subMonth()->format('Y-m-d');
        $endDate = $this->parameters['end_date'] ?? Carbon::now()->format('Y-m-d');
        $branchId = $this->parameters['branch_id'] ?? null;

        $query = \DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'delivered');

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query->select([
            'orders.id as order_id',
            'orders.created_at',
            'orders.order_amount',
            'products.name as product_name',
            'order_details.quantity',
            'order_details.price',
            'order_details.discount_on_product'
        ])->get()->toArray();
    }

    /**
     * Generate product report
     *
     * @return array
     */
    private function generateProductReport(): array
    {
        $categoryId = $this->parameters['category_id'] ?? null;
        $includeInactive = $this->parameters['include_inactive'] ?? false;

        $query = \DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select([
                'products.id',
                'products.name',
                'products.price',
                'products.total_stock',
                'products.status',
                'categories.name as category_name',
                \DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold'),
                \DB::raw('COALESCE(SUM(order_details.quantity * order_details.price), 0) as total_revenue')
            ])
            ->groupBy('products.id', 'products.name', 'products.price', 'products.total_stock', 'products.status', 'categories.name');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        if (!$includeInactive) {
            $query->where('products.status', 1);
        }

        return $query->get()->toArray();
    }

    /**
     * Generate customer report
     *
     * @return array
     */
    private function generateCustomerReport(): array
    {
        $startDate = $this->parameters['start_date'] ?? Carbon::now()->subMonth()->format('Y-m-d');
        $endDate = $this->parameters['end_date'] ?? Carbon::now()->format('Y-m-d');

        return \DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->select([
                'users.id',
                'users.f_name',
                'users.l_name',
                'users.email',
                'users.phone',
                'users.created_at as registration_date',
                \DB::raw('COUNT(orders.id) as total_orders'),
                \DB::raw('COALESCE(SUM(orders.order_amount), 0) as total_spent'),
                \DB::raw('MAX(orders.created_at) as last_order_date')
            ])
            ->whereBetween('users.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.f_name', 'users.l_name', 'users.email', 'users.phone', 'users.created_at')
            ->get()->toArray();
    }

    /**
     * Generate order report
     *
     * @return array
     */
    private function generateOrderReport(): array
    {
        $startDate = $this->parameters['start_date'] ?? Carbon::now()->subMonth()->format('Y-m-d');
        $endDate = $this->parameters['end_date'] ?? Carbon::now()->format('Y-m-d');
        $status = $this->parameters['status'] ?? null;

        $query = \DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->select([
                'orders.id',
                'orders.created_at',
                'orders.order_amount',
                'orders.order_status',
                'orders.payment_method',
                'users.f_name',
                'users.l_name',
                'users.phone',
                'branches.name as branch_name'
            ])
            ->whereBetween('orders.created_at', [$startDate, $endDate]);

        if ($status) {
            $query->where('orders.order_status', $status);
        }

        return $query->orderBy('orders.created_at', 'desc')->get()->toArray();
    }

    /**
     * Generate inventory report
     *
     * @return array
     */
    private function generateInventoryReport(): array
    {
        $lowStockThreshold = $this->parameters['low_stock_threshold'] ?? 10;

        return \DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select([
                'products.id',
                'products.name',
                'products.total_stock',
                'products.price',
                'categories.name as category_name',
                \DB::raw('CASE WHEN products.total_stock <= ' . $lowStockThreshold . ' THEN "Low Stock" ELSE "In Stock" END as stock_status')
            ])
            ->where('products.status', 1)
            ->orderBy('products.total_stock', 'asc')
            ->get()->toArray();
    }

    /**
     * Generate delivery report
     *
     * @return array
     */
    private function generateDeliveryReport(): array
    {
        $startDate = $this->parameters['start_date'] ?? Carbon::now()->subMonth()->format('Y-m-d');
        $endDate = $this->parameters['end_date'] ?? Carbon::now()->format('Y-m-d');

        return \DB::table('orders')
            ->leftJoin('delivery_men', 'orders.delivery_man_id', '=', 'delivery_men.id')
            ->select([
                'orders.id as order_id',
                'orders.created_at',
                'orders.delivery_date',
                'orders.delivery_time',
                'orders.order_status',
                'delivery_men.f_name as delivery_man_name',
                'delivery_men.phone as delivery_man_phone',
                \DB::raw('TIMESTAMPDIFF(MINUTE, orders.created_at, orders.updated_at) as delivery_time_minutes')
            ])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.order_status', ['out_for_delivery', 'delivered'])
            ->get()->toArray();
    }

    /**
     * Save report data to file
     *
     * @param array $data
     * @return string
     */
    private function saveReportFile(array $data): string
    {
        $fileName = $this->reportType . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        $filePath = 'reports/' . $fileName;

        $csvContent = $this->arrayToCsv($data);
        Storage::disk('public')->put($filePath, $csvContent);

        return $filePath;
    }

    /**
     * Convert array to CSV format
     *
     * @param array $data
     * @return string
     */
    private function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Add headers
        $headers = array_keys((array) $data[0]);
        fputcsv($output, $headers);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, (array) $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Send report notification email
     *
     * @param string $filePath
     * @return void
     */
    private function sendReportNotification(string $filePath): void
    {
        try {
            $mailable = new \App\Mail\ReportGenerated($this->reportType, $filePath);
            dispatch(new SendEmailJob($mailable, $this->notifyEmail, 'report_notification'));
        } catch (\Exception $e) {
            Log::error('Failed to send report notification', [
                'email' => $this->notifyEmail,
                'report_type' => $this->reportType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Report generation job failed permanently', [
            'type' => $this->reportType,
            'requested_by' => $this->requestedBy,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Notify the requester about the failure
        if ($this->notifyEmail) {
            try {
                $mailable = new \App\Mail\ReportGenerationFailed($this->reportType, $exception->getMessage());
                dispatch(new SendEmailJob($mailable, $this->notifyEmail, 'report_failure'));
            } catch (\Exception $e) {
                Log::error('Failed to send report failure notification', [
                    'email' => $this->notifyEmail,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
