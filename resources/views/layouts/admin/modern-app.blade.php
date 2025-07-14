<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') - Modern Dashboard</title>

    @php($icon = \App\Model\BusinessSetting::where(['key' => 'fav_icon'])->first()->value)
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/app/public/restaurant/' . $icon ?? '') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('css')

    <style>
        /* Simple Modern Layout */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        /* Modern Sidebar */
        .modern-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand {
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 600;
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
        }

        .nav-icon {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        /* Submenu Styles */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0,0,0,0.1);
            margin: 0.25rem 0;
            border-radius: 0.25rem;
        }

        .submenu.show {
            max-height: 300px;
        }

        .submenu-link {
            display: block;
            padding: 0.5rem 1rem 0.5rem 3rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .submenu-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
        }

        /* Modern Header */
        .modern-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Content Area */
        .content-wrapper {
            padding: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modern-sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>

        .main-content.sidebar-collapsed {
            margin-left: 80px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        .content-wrapper {
            padding: var(--spacing-6);
        }

        .page-header {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: var(--spacing-6);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .page-title {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            margin: 0;
        }

        .page-subtitle {
            font-size: var(--font-size-base);
            color: var(--text-secondary);
            margin: var(--spacing-2) 0 0 0;
        }
    </style>

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
    <!-- Simple Modern Sidebar -->
    <div class="modern-sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <i class="fas fa-leaf"></i> GroFresh Admin
            </a>
        </div>

        <nav class="sidebar-nav">
            @foreach(admin_navigation() as $item)
                @if($item['enabled'])
                    <div class="nav-item">
                        <a href="{{ isset($item['route_params']) ? route($item['route'], $item['route_params']) : route($item['route']) }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }} nav-icon"></i>
                            {{ $item['name'] }}
                        </a>

                        @if(isset($item['submenu']) && count($item['submenu']) > 0)
                            <div class="submenu {{ $item['active'] ? 'show' : '' }}">
                                @foreach($item['submenu'] as $subitem)
                                    <a href="{{ isset($subitem['params']) ? route($subitem['route'], $subitem['params']) : route($subitem['route']) }}" class="submenu-link">
                                        {{ $subitem['name'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Simple Header -->
        <div class="modern-header">
            <h1 class="mb-0">@yield('title', 'Dashboard')</h1>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div></body>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/admin/js/vendor.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
