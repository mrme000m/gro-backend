<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') - {{ config('app.name') }}</title>
    
    @php($icon = \App\Model\BusinessSetting::where(['key' => 'fav_icon'])->first()->value)
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/app/public/restaurant/' . $icon ?? '') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/modern-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/toastr.css') }}">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('css')
    
    <style>
        /* Additional modern styles */
        .modern-tooltip {
            position: absolute;
            background: var(--bg-card);
            color: var(--text-primary);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            z-index: 9999;
            opacity: 0;
            transform: translateY(4px);
            transition: all 0.2s ease;
            pointer-events: none;
        }
        
        .modern-tooltip.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .modern-notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1rem;
            box-shadow: var(--shadow-xl);
            z-index: 9999;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
        }
        
        .modern-notification.show {
            transform: translateX(0);
        }
        
        .modern-notification.info {
            border-left: 4px solid var(--info-color);
        }
        
        .modern-notification.success {
            border-left: 4px solid var(--success-color);
        }
        
        .modern-notification.warning {
            border-left: 4px solid var(--warning-color);
        }
        
        .modern-notification.error {
            border-left: 4px solid var(--danger-color);
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--text-secondary);
            padding: 0;
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .notification-close:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }
        
        .highlight-search {
            background: rgba(99, 102, 241, 0.1) !important;
            color: var(--primary-color) !important;
        }
        
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            background: var(--bg-secondary);
            transition: margin-left var(--transition-normal);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
        
        .page-header {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        
        .page-subtitle {
            color: var(--text-secondary);
            margin: 0.5rem 0 0 0;
        }
        
        .content-wrapper {
            padding: 0 2rem 2rem 2rem;
        }
        
        /* Loading states */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-spinner {
            width: 3rem;
            height: 3rem;
            border: 3px solid var(--border-color);
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Modern Sidebar -->
    @include('layouts.admin.partials.modern-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <!-- Modern Header -->
        @include('layouts.admin.partials.modern-header')

        <!-- Page Content -->
        <main>
            @hasSection('page-header')
                <div class="page-header">
                    <h1 class="page-title">@yield('page-title')</h1>
                    @hasSection('page-subtitle')
                        <p class="page-subtitle">@yield('page-subtitle')</p>
                    @endif
                </div>
            @endif

            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('public/assets/admin/js/vendor.min.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/theme.min.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/sweet_alert.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/toastr.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/modern-dashboard.js') }}"></script>

    @stack('scripts')

    <!-- Toastr Messages -->
    {!! Toastr::message() !!}

    <script>
        // Global JavaScript utilities
        window.showLoading = function() {
            document.getElementById('loadingOverlay').classList.add('show');
        };

        window.hideLoading = function() {
            document.getElementById('loadingOverlay').classList.remove('show');
        };

        window.showNotification = function(message, type = 'info') {
            if (window.modernDashboard) {
                window.modernDashboard.showNotification(message, type);
            }
        };

        // CSRF token setup for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            hideLoading();
        });

        // Handle AJAX errors globally
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            hideLoading();
            if (xhr.status === 419) {
                showNotification('Session expired. Please refresh the page.', 'warning');
            } else if (xhr.status >= 500) {
                showNotification('Server error occurred. Please try again.', 'error');
            }
        });

        // Auto-hide loading on page load
        $(document).ready(function() {
            hideLoading();
        });
    </script>
</body>
</html>
