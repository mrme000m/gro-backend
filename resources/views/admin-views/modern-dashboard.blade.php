@extends('layouts.admin.modern-app')

@section('title', translate('Dashboard'))
@section('page-title', translate('Dashboard'))
@section('page-subtitle', translate('Welcome back! Here\'s what\'s happening with your store today.'))

@push('css')
<style>
    .metric-card {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        border: none;
    }
    
    .metric-card .stat-value {
        color: white;
    }
    
    .metric-card .stat-label {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .chart-card {
        height: 400px;
    }
    
    .quick-action-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }
    
    .recent-orders-table {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-pending { background: rgba(245, 158, 11, 0.1); color: rgb(245, 158, 11); }
    .status-confirmed { background: rgba(59, 130, 246, 0.1); color: rgb(59, 130, 246); }
    .status-processing { background: rgba(139, 69, 19, 0.1); color: rgb(139, 69, 19); }
    .status-delivered { background: rgba(16, 185, 129, 0.1); color: rgb(16, 185, 129); }
    .status-cancelled { background: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); }
</style>
@endpush

@section('content')
@if(Helpers::module_permission_check(MANAGEMENT_SECTION['dashboard_management']))

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card metric-card">
        <div class="stat-value" data-count="{{ $data['total_orders'] ?? 0 }}">{{ $data['total_orders'] ?? 0 }}</div>
        <div class="stat-label">{{ translate('Total Orders') }}</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>+12%</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-value" data-count="{{ $data['customer'] ?? 0 }}">{{ $data['customer'] ?? 0 }}</div>
        <div class="stat-label">{{ translate('Total Customers') }}</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>+8%</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-value">${{ number_format($data['total_earning'] ?? 0, 2) }}</div>
        <div class="stat-label">{{ translate('Total Revenue') }}</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>+15%</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-value" data-count="{{ $data['product'] ?? 0 }}">{{ $data['product'] ?? 0 }}</div>
        <div class="stat-label">{{ translate('Total Products') }}</div>
        <div class="stat-change negative">
            <i class="fas fa-arrow-down"></i>
            <span>-2%</span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="modern-card quick-action-card" onclick="window.location.href='{{ route('admin.pos.index') }}'">
        <div class="modern-card-body text-center">
            <div class="w-16 h-16 bg-var(--primary-color) rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-cash-register text-white text-2xl"></i>
            </div>
            <h3 class="font-semibold text-var(--text-primary) mb-2">{{ translate('POS System') }}</h3>
            <p class="text-var(--text-secondary) text-sm">{{ translate('Process orders quickly') }}</p>
        </div>
    </div>
    
    <div class="modern-card quick-action-card" onclick="window.location.href='{{ route('admin.product.add-new') }}'">
        <div class="modern-card-body text-center">
            <div class="w-16 h-16 bg-var(--success-color) rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-plus text-white text-2xl"></i>
            </div>
            <h3 class="font-semibold text-var(--text-primary) mb-2">{{ translate('Add Product') }}</h3>
            <p class="text-var(--text-secondary) text-sm">{{ translate('Add new products to inventory') }}</p>
        </div>
    </div>
    
    <div class="modern-card quick-action-card" onclick="window.location.href='{{ route('admin.orders.list', ['status' => 'all']) }}'">
        <div class="modern-card-body text-center">
            <div class="w-16 h-16 bg-var(--warning-color) rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shopping-cart text-white text-2xl"></i>
            </div>
            <h3 class="font-semibold text-var(--text-primary) mb-2">{{ translate('View Orders') }}</h3>
            <p class="text-var(--text-secondary) text-sm">{{ translate('Manage customer orders') }}</p>
        </div>
    </div>
    
    <div class="modern-card quick-action-card" onclick="window.location.href='{{ route('admin.report.order') }}'">
        <div class="modern-card-body text-center">
            <div class="w-16 h-16 bg-var(--info-color) rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chart-bar text-white text-2xl"></i>
            </div>
            <h3 class="font-semibold text-var(--text-primary) mb-2">{{ translate('Reports') }}</h3>
            <p class="text-var(--text-secondary) text-sm">{{ translate('View analytics and reports') }}</p>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue Chart -->
    <div class="chart-container chart-card">
        <div class="chart-header">
            <h3 class="chart-title">{{ translate('Revenue Overview') }}</h3>
            <select class="px-3 py-1 bg-var(--bg-tertiary) border border-var(--border-color) rounded-md text-sm">
                <option>{{ translate('Last 6 months') }}</option>
                <option>{{ translate('Last 3 months') }}</option>
                <option>{{ translate('This year') }}</option>
            </select>
        </div>
        <canvas id="revenueChart"></canvas>
    </div>
    
    <!-- Orders Chart -->
    <div class="chart-container chart-card">
        <div class="chart-header">
            <h3 class="chart-title">{{ translate('Order Status Distribution') }}</h3>
        </div>
        <canvas id="ordersChart"></canvas>
    </div>
</div>

<!-- Recent Orders and Top Products -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Orders -->
    <div class="lg:col-span-2">
        <div class="modern-card">
            <div class="modern-card-header">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-var(--text-primary)">{{ translate('Recent Orders') }}</h3>
                    <a href="{{ route('admin.orders.list', ['status' => 'all']) }}" 
                       class="text-var(--primary-color) hover:underline text-sm">
                        {{ translate('View All') }}
                    </a>
                </div>
            </div>
            <div class="modern-card-body p-0">
                <div class="overflow-x-auto">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>{{ translate('Order ID') }}</th>
                                <th>{{ translate('Customer') }}</th>
                                <th>{{ translate('Amount') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['recent_orders'] ?? [] as $order)
                            <tr>
                                <td style="font-weight: 500;">#{{ $order['id'] }}</td>
                                <td>{{ $order['customer_name'] ?? 'Guest' }}</td>
                                <td style="font-weight: 500;">${{ number_format($order['order_amount'], 2) }}</td>
                                <td>
                                    <span class="status-badge status-{{ $order['order_status'] }}">
                                        {{ translate($order['order_status']) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    {{ translate('No recent orders found') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="lg:col-span-1">
        <div class="modern-card">
            <div class="modern-card-header">
                <h3 class="text-lg font-semibold text-var(--text-primary)">{{ translate('Top Products') }}</h3>
            </div>
            <div class="modern-card-body">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @forelse($data['top_sell'] ?? [] as $product)
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="{{ asset('storage/app/public/product/' . $product['image']) }}"
                             alt="{{ $product['name'] }}"
                             style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover;"
                             onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                        <div style="flex: 1; min-width: 0;">
                            <p style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $product['name'] }}
                            </p>
                            <p style="font-size: 0.75rem; color: var(--text-secondary);">
                                {{ $product['order_count'] ?? 0 }} {{ translate('sold') }}
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <p style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary);">
                                ${{ number_format($product['price'], 2) }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        {{ translate('No products found') }}
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@else
<div class="modern-card">
    <div class="modern-card-body text-center py-16">
        <i class="fas fa-lock text-6xl text-var(--text-muted) mb-4"></i>
        <h3 class="text-xl font-semibold text-var(--text-primary) mb-2">
            {{ translate('Access Denied') }}
        </h3>
        <p class="text-var(--text-secondary)">
            {{ translate('You don\'t have permission to access the dashboard.') }}
        </p>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Update stats periodically
    setInterval(updateDashboardStats, 30000); // Update every 30 seconds
    
    // Initialize real-time features
    initializeRealTimeUpdates();
});

function updateDashboardStats() {
    // Fetch updated statistics
    fetch('{{ route("admin.dashboard.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (window.modernDashboard) {
                window.modernDashboard.updateStatCard('.stat-card:nth-child(1)', data.total_orders, data.orders_change);
                window.modernDashboard.updateStatCard('.stat-card:nth-child(2)', data.total_customers, data.customers_change);
                window.modernDashboard.updateStatCard('.stat-card:nth-child(4)', data.total_products, data.products_change);
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

function initializeRealTimeUpdates() {
    // This would typically connect to a WebSocket or use Server-Sent Events
    // For now, we'll simulate real-time updates
    
    // Simulate new order notifications
    setInterval(() => {
        if (Math.random() > 0.9) { // 10% chance every interval
            if (window.modernDashboard) {
                window.modernDashboard.showNotification(
                    'New order received! Order #' + Math.floor(Math.random() * 10000),
                    'success'
                );
            }
        }
    }, 10000);
}

// Keyboard shortcuts for quick actions
document.addEventListener('keydown', function(e) {
    if (e.altKey) {
        switch(e.key) {
            case '1':
                e.preventDefault();
                window.location.href = '{{ route("admin.pos.index") }}';
                break;
            case '2':
                e.preventDefault();
                window.location.href = '{{ route("admin.product.add-new") }}';
                break;
            case '3':
                e.preventDefault();
                window.location.href = '{{ route("admin.orders.list", ["status" => "all"]) }}';
                break;
            case '4':
                e.preventDefault();
                window.location.href = '{{ route("admin.report.order") }}';
                break;
        }
    }
});
</script>
@endpush
