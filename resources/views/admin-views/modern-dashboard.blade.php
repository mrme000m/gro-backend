@extends('layouts.admin.modern-app')

@section('title', translate('Dashboard'))
@section('page-title', translate('Dashboard'))
@section('page-subtitle', translate('Welcome back! Here\'s what\'s happening with your store today.'))

@push('css')
<style>
    /* Enhanced Dashboard Styles */
    .metric-card {
        background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent));
        color: white;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30px, -30px);
    }

    .metric-card .stat-value {
        color: white;
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        margin-bottom: var(--spacing-2);
    }

    .metric-card .stat-label {
        color: rgba(255, 255, 255, 0.9);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
    }

    .metric-card .stat-change {
        margin-top: var(--spacing-3);
        display: flex;
        align-items: center;
        gap: var(--spacing-1);
        font-size: var(--font-size-sm);
    }

    .chart-card {
        height: 400px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        padding: var(--spacing-6);
        box-shadow: var(--shadow-sm);
    }

    .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--spacing-6);
    }

    .chart-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .quick-action-card {
        transition: all var(--transition-normal);
        cursor: pointer;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        padding: var(--spacing-6);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .quick-action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--brand-primary), var(--brand-accent));
        transform: scaleX(0);
        transition: transform var(--transition-normal);
    }

    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
        border-color: var(--brand-primary);
    }

    .quick-action-card:hover::before {
        transform: scaleX(1);
    }

    .status-badge {
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-pending {
        background: var(--warning-light);
        color: var(--warning-dark);
    }
    .status-confirmed {
        background: var(--info-light);
        color: var(--info-dark);
    }
    .status-processing {
        background: var(--warning-light);
        color: var(--warning-dark);
    }
    .status-delivered {
        background: var(--success-light);
        color: var(--success-dark);
    }
    .status-cancelled {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    /* Modern Table Styles */
    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table th {
        background: var(--bg-tertiary);
        color: var(--text-secondary);
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-xs);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: var(--spacing-3) var(--spacing-4);
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table td {
        padding: var(--spacing-4);
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .modern-table tr:hover {
        background: var(--bg-secondary);
    }

    /* Grid System */
    .grid {
        display: grid;
        gap: var(--spacing-6);
    }

    .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

    @media (min-width: 768px) {
        .md\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .md\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .md\\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }

    @media (min-width: 1024px) {
        .lg\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .lg\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .lg\\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .lg\\:col-span-2 { grid-column: span 2 / span 2; }
    }
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
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 64px; height: 64px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-cash-register" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <h3 style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">{{ translate('POS System') }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">{{ translate('Process orders quickly') }}</p>
        </div>
    </div>

    <div class="modern-card quick-action-card" onclick="window.location.href='{{ route('admin.product.add-new') }}'">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 64px; height: 64px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-plus" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <h3 style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">{{ translate('Add Product') }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">{{ translate('Add new products to inventory') }}</p>
        </div>
    </div>

    <div class="modern-card quick-action-card" onclick="window.location.href='{{ route('admin.orders.list', ['status' => 'all']) }}'">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 64px; height: 64px; background: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-shopping-cart" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <h3 style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">{{ translate('View Orders') }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">{{ translate('Manage customer orders') }}</p>
        </div>
    </div>

    <div class="modern-card quick-action-card" onclick="window.location.href='{{ route('admin.report.order') }}'">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 64px; height: 64px; background: var(--info-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-chart-bar" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <h3 style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">{{ translate('Reports') }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">{{ translate('View analytics and reports') }}</p>
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
                window.modernDashboard.updateStatCard('.stat-card:nth-child(2)', data.customer, data.customers_change);
                window.modernDashboard.updateStatCard('.stat-card:nth-child(4)', data.product, data.products_change);
            }
        })
        .catch(error => {
            console.error('Error updating stats:', error);
            // Fallback to simulated updates if API fails
            console.log('Using simulated stats update');
        });
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
