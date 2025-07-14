<aside class="modern-sidebar" id="modernSidebar">
    <!-- Sidebar Header -->
    <div class="modern-sidebar-header">
        @php
            $logo = \App\Model\BusinessSetting::where(['key'=>'logo'])->first()->value;
        @endphp
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <div class="brand-logo">
                <img src="{{ App\CentralLogics\Helpers::onErrorImage($logo, asset('storage/app/public/restaurant') . '/' . $logo, asset('assets/admin/img/160x160/img2.jpg'), 'restaurant/') }}"
                     alt="{{ translate('logo') }}"
                     class="logo-image">
            </div>
            <div class="brand-text">
                <span class="brand-name">{{ config('app.name', 'GroFresh') }}</span>
                <span class="brand-tagline">{{ translate('Admin Panel') }}</span>
            </div>
        </a>

        <!-- Sidebar Toggle -->
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Quick Search -->
    <div class="sidebar-search">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text"
                   id="search-sidebar-menu"
                   placeholder="{{ translate('Search Menu...') }}"
                   class="search-input"
                   autocomplete="off">
            <kbd class="search-shortcut">⌘K</kbd>
        </div>
        <div class="search-results" id="searchResults" style="display: none;"></div>
    </div>

    <!-- Navigation -->
    <nav class="modern-sidebar-nav">
        <!-- Main Navigation -->
        <div class="nav-section">
            <div class="nav-section-title">{{ translate('Main') }}</div>

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['dashboard_management']))
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ Request::is('admin') ? 'active' : '' }}"
                   data-tooltip="Dashboard">
                    <div class="nav-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="nav-text">{{ translate('Dashboard') }}</span>
                    <div class="nav-indicator"></div>
                </a>
            </div>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['pos_management']))
            <div class="nav-item">
                <a href="{{ route('admin.pos.index') }}"
                   class="nav-link {{ Request::is('admin/pos*') ? 'active' : '' }}"
                   data-tooltip="Point of Sale">
                    <div class="nav-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <span class="nav-text">{{ translate('POS System') }}</span>
                    <div class="nav-indicator"></div>
                </a>
            </div>
            @endif
        </div>

        <!-- Order Management -->
        @if(Helpers::module_permission_check(MANAGEMENT_SECTION['order_management']))
        <div class="nav-section">
            <div class="nav-section-title">{{ translate('Order Management') }}</div>

            <div class="nav-item">
                <button class="nav-link nav-toggle {{ Request::is('admin/orders*') ? 'active' : '' }}"
                        onclick="toggleSubmenu('orders-submenu')"
                        data-tooltip="Orders">
                    <div class="nav-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span class="nav-text">{{ translate('Orders') }}</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right" id="orders-submenu-icon"></i>
                    </div>
                </button>
                <div class="nav-submenu {{ Request::is('admin/orders*') ? 'expanded' : '' }}" id="orders-submenu">
                    <a href="{{ route('admin.orders.list', ['status' => 'all']) }}"
                       class="submenu-link {{ Request::is('admin/orders/list/all') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('All Orders') }}</span>
                        <span class="submenu-badge">{{\App\Model\Order::notPos()->count()}}</span>
                    </a>
                    <a href="{{ route('admin.orders.list', ['status' => 'pending']) }}"
                       class="submenu-link {{ Request::is('admin/orders/list/pending') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Pending') }}</span>
                        <span class="submenu-badge warning">{{\App\Model\Order::where(['order_status'=>'pending'])->count()}}</span>
                    </a>
                    <a href="{{ route('admin.orders.list', ['status' => 'confirmed']) }}"
                       class="submenu-link {{ Request::is('admin/orders/list/confirmed') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Confirmed') }}</span>
                        <span class="submenu-badge success">{{\App\Model\Order::where(['order_status'=>'confirmed'])->count()}}</span>
                    </a>
                    <a href="{{ route('admin.orders.list', ['status' => 'processing']) }}"
                       class="submenu-link {{ Request::is('admin/orders/list/processing') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Processing') }}</span>
                        <span class="submenu-badge info">{{\App\Model\Order::where(['order_status'=>'processing'])->count()}}</span>
                    </a>
                    <a href="{{ route('admin.orders.list', ['status' => 'out_for_delivery']) }}"
                       class="submenu-link {{ Request::is('admin/orders/list/out_for_delivery') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Out for Delivery') }}</span>
                        <span class="submenu-badge info">{{\App\Model\Order::where(['order_status'=>'out_for_delivery'])->count()}}</span>
                    </a>
                    <a href="{{ route('admin.orders.list', ['status' => 'delivered']) }}"
                       class="submenu-link {{ Request::is('admin/orders/list/delivered') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Delivered') }}</span>
                        <span class="submenu-badge success">{{\App\Model\Order::notPos()->where(['order_status'=>'delivered'])->count()}}</span>
                    </a>
                    <a href="{{ route('admin.orders.list', ['status' => 'canceled']) }}"
                       class="submenu-link {{ Request::is('admin/orders/list/canceled') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Canceled') }}</span>
                        <span class="submenu-badge danger">{{\App\Model\Order::where(['order_status'=>'canceled'])->count()}}</span>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Product Management -->
        @if(Helpers::module_permission_check(MANAGEMENT_SECTION['product_management']))
        <div class="nav-section">
            <div class="nav-section-title">{{ translate('Product Management') }}</div>

            <div class="nav-item">
                <button class="nav-link nav-toggle {{ Request::is('admin/category*') ? 'active' : '' }}"
                        onclick="toggleSubmenu('category-submenu')"
                        data-tooltip="Categories">
                    <div class="nav-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <span class="nav-text">{{ translate('Categories') }}</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right" id="category-submenu-icon"></i>
                    </div>
                </button>
                <div class="nav-submenu {{ Request::is('admin/category*') ? 'expanded' : '' }}" id="category-submenu">
                    <a href="{{ route('admin.category.add') }}"
                       class="submenu-link {{ Request::is('admin/category/add') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Main Categories') }}</span>
                    </a>
                    <a href="{{ route('admin.category.add-sub-category') }}"
                       class="submenu-link {{ Request::is('admin/category/add-sub-category') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Sub Categories') }}</span>
                    </a>
                </div>
            </div>

            <div class="nav-item">
                <button class="nav-link nav-toggle {{ Request::is('admin/product*') || Request::is('admin/attribute*') ? 'active' : '' }}"
                        onclick="toggleSubmenu('product-submenu')"
                        data-tooltip="Products">
                    <div class="nav-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <span class="nav-text">{{ translate('Products') }}</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right" id="product-submenu-icon"></i>
                    </div>
                </button>
                <div class="nav-submenu {{ Request::is('admin/product*') || Request::is('admin/attribute*') ? 'expanded' : '' }}" id="product-submenu">
                    <a href="{{ route('admin.attribute.add-new') }}"
                       class="submenu-link {{ Request::is('admin/attribute*') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Attributes') }}</span>
                    </a>
                    <a href="{{ route('admin.product.list') }}"
                       class="submenu-link {{ Request::is('admin/product/list*') || Request::is('admin/product/add-new') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Product List') }}</span>
                    </a>
                    <a href="{{ route('admin.product.bulk-import') }}"
                       class="submenu-link {{ Request::is('admin/product/bulk-import') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Bulk Import') }}</span>
                    </a>
                    <a href="{{ route('admin.product.bulk-export-index') }}"
                       class="submenu-link {{ Request::is('admin/product/bulk-export-index') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Bulk Export') }}</span>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Customer Management -->
        @if(Helpers::module_permission_check(MANAGEMENT_SECTION['user_management']))
        <div class="nav-section">
            <div class="nav-section-title">{{ translate('Customer Management') }}</div>

            <div class="nav-item">
                <a href="{{ route('admin.customer.list') }}"
                   class="nav-link {{ Request::is('admin/customer*') ? 'active' : '' }}"
                   data-tooltip="Customers">
                    <div class="nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="nav-text">{{ translate('Customers') }}</span>
                    <div class="nav-indicator"></div>
                </a>
            </div>
        </div>
        @endif

        <!-- Marketing & Promotions -->
        @if(Helpers::module_permission_check(MANAGEMENT_SECTION['promotion_management']))
        <div class="nav-section">
            <div class="nav-section-title">{{ translate('Marketing') }}</div>

            <div class="nav-item">
                <a href="{{ route('admin.coupon.add-new') }}"
                   class="nav-link {{ Request::is('admin/coupon*') ? 'active' : '' }}"
                   data-tooltip="Coupons">
                    <div class="nav-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <span class="nav-text">{{ translate('Coupons') }}</span>
                    <div class="nav-indicator"></div>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.banner.add-new') }}"
                   class="nav-link {{ Request::is('admin/banner*') ? 'active' : '' }}"
                   data-tooltip="Banners">
                    <div class="nav-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <span class="nav-text">{{ translate('Banners') }}</span>
                    <div class="nav-indicator"></div>
                </a>
            </div>
        </div>
        @endif

        <!-- Reports & Analytics -->
        @if(Helpers::module_permission_check(MANAGEMENT_SECTION['report_management']))
        <div class="nav-section">
            <div class="nav-section-title">{{ translate('Analytics') }}</div>

            <div class="nav-item">
                <button class="nav-link nav-toggle {{ Request::is('admin/report*') ? 'active' : '' }}"
                        onclick="toggleSubmenu('reports-submenu')"
                        data-tooltip="Reports">
                    <div class="nav-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <span class="nav-text">{{ translate('Reports') }}</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right" id="reports-submenu-icon"></i>
                    </div>
                </button>
                <div class="nav-submenu {{ Request::is('admin/report*') ? 'expanded' : '' }}" id="reports-submenu">
                    <a href="{{ route('admin.report.order') }}"
                       class="submenu-link {{ Request::is('admin/report/order*') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Order Reports') }}</span>
                    </a>
                    <a href="{{ route('admin.report.earning') }}"
                       class="submenu-link {{ Request::is('admin/report/earning*') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Earning Reports') }}</span>
                    </a>
                    <a href="{{ route('admin.report.sale-report') }}"
                       class="submenu-link {{ Request::is('admin/report/sale-report*') ? 'active' : '' }}">
                        <span class="submenu-text">{{ translate('Sale Reports') }}</span>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- System Settings -->
        @if(Helpers::module_permission_check(MANAGEMENT_SECTION['system_management']))
        <div class="nav-section">
            <div class="nav-section-title">{{ translate('System') }}</div>

            <div class="nav-item">
                <a href="{{ route('admin.business-settings.restaurant-index') }}"
                   class="nav-link {{ Request::is('admin/business-settings*') ? 'active' : '' }}"
                   data-tooltip="Settings">
                    <div class="nav-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="nav-text">{{ translate('Settings') }}</span>
                    <div class="nav-indicator"></div>
                </a>
            </div>
        </div>
        @endif
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="footer-user">
            <div class="user-avatar">
                <img src="{{ asset('assets/admin/img/160x160/img1.jpg') }}" alt="User">
            </div>
            <div class="user-info">
                <div class="user-name">{{ auth('admin')->user()->f_name ?? 'Admin' }}</div>
                <div class="user-role">{{ translate('Administrator') }}</div>
            </div>
        </div>

        <div class="footer-actions">
            <button class="action-btn" onclick="toggleTheme()" data-tooltip="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            <a href="{{ route('admin.auth.logout') }}" class="action-btn" data-tooltip="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>
