<aside class="modern-sidebar" id="modernSidebar">
    <!-- Sidebar Header -->
    <div class="modern-sidebar-header">
        @php
            $logo = \App\Model\BusinessSetting::where(['key'=>'logo'])->first()->value;
        @endphp
        <a href="{{ route('admin.dashboard') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="{{ App\CentralLogics\Helpers::onErrorImage($logo, asset('storage/app/public/restaurant') . '/' . $logo, asset('assets/admin/img/160x160/img2.jpg'), 'restaurant/') }}"
                 alt="{{ translate('logo') }}"
                 style="height: 40px; width: auto;">
            <span style="font-size: 1.25rem; font-weight: 700;" class="text-gradient">{{ config('app.name', 'GroFresh') }}</span>
        </a>
    </div>

    <!-- Search -->
    <div style="padding: 1rem;">
        <div style="position: relative;">
            <input type="text"
                   id="search-sidebar-menu"
                   placeholder="{{ translate('Search Menu...') }}"
                   style="width: 100%; padding: 8px 12px 8px 40px; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 8px; outline: none; transition: all 0.3s ease;"
                   onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(99, 102, 241, 0.1)';"
                   onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
            <kbd style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); padding: 2px 6px; font-size: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 4px;">⌘K</kbd>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="modern-sidebar-nav">
        <ul style="list-style: none; padding: 0; margin: 0;"">
            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['dashboard_management']))
            <li class="modern-nav-item">
                <a href="{{ route('admin.dashboard') }}" 
                   class="modern-nav-link {{ Request::is('admin') ? 'active' : '' }}"
                   data-tooltip="Dashboard">
                    <i class="fas fa-home modern-nav-icon"></i>
                    <span>{{ translate('dashboard') }}</span>
                </a>
            </li>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['pos_management']))
            <li class="modern-nav-item">
                <a href="{{ route('admin.pos.index') }}" 
                   class="modern-nav-link {{ Request::is('admin/pos*') ? 'active' : '' }}"
                   data-tooltip="POS System">
                    <i class="fas fa-cash-register modern-nav-icon"></i>
                    <span>{{ translate('POS') }}</span>
                </a>
            </li>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['order_management']))
            <li class="modern-nav-item">
                <div class="modern-nav-group">
                    <button class="modern-nav-link w-full flex items-center justify-between" 
                            onclick="toggleSubmenu('orders-submenu')"
                            data-tooltip="Order Management">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-cart modern-nav-icon"></i>
                            <span>{{ translate('Order Management') }}</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" id="orders-submenu-icon"></i>
                    </button>
                    <ul class="modern-submenu hidden mt-2 ml-8 space-y-1" id="orders-submenu">
                        <li>
                            <a href="{{ route('admin.orders.list', ['status' => 'all']) }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/orders*') ? 'active' : '' }}">
                                <i class="fas fa-list modern-nav-icon"></i>
                                <span>{{ translate('All Orders') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.list', ['status' => 'pending']) }}" 
                               class="modern-nav-link text-sm">
                                <i class="fas fa-clock modern-nav-icon"></i>
                                <span>{{ translate('Pending') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.list', ['status' => 'confirmed']) }}" 
                               class="modern-nav-link text-sm">
                                <i class="fas fa-check-circle modern-nav-icon"></i>
                                <span>{{ translate('Confirmed') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.list', ['status' => 'processing']) }}" 
                               class="modern-nav-link text-sm">
                                <i class="fas fa-cog modern-nav-icon"></i>
                                <span>{{ translate('Processing') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.list', ['status' => 'out_for_delivery']) }}" 
                               class="modern-nav-link text-sm">
                                <i class="fas fa-truck modern-nav-icon"></i>
                                <span>{{ translate('Out for Delivery') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.list', ['status' => 'delivered']) }}" 
                               class="modern-nav-link text-sm">
                                <i class="fas fa-check-double modern-nav-icon"></i>
                                <span>{{ translate('Delivered') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['product_management']))
            <li class="modern-nav-item">
                <div class="modern-nav-group">
                    <button class="modern-nav-link w-full flex items-center justify-between" 
                            onclick="toggleSubmenu('products-submenu')"
                            data-tooltip="Product Management">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-box modern-nav-icon"></i>
                            <span>{{ translate('Product Management') }}</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" id="products-submenu-icon"></i>
                    </button>
                    <ul class="modern-submenu hidden mt-2 ml-8 space-y-1" id="products-submenu">
                        <li>
                            <a href="{{ route('admin.category.add') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/category*') ? 'active' : '' }}">
                                <i class="fas fa-tags modern-nav-icon"></i>
                                <span>{{ translate('Categories') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.sub-category.add') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/sub-category*') ? 'active' : '' }}">
                                <i class="fas fa-layer-group modern-nav-icon"></i>
                                <span>{{ translate('Sub Categories') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.product.add-new') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/product*') ? 'active' : '' }}">
                                <i class="fas fa-plus-circle modern-nav-icon"></i>
                                <span>{{ translate('Add Product') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.product.list') }}" 
                               class="modern-nav-link text-sm">
                                <i class="fas fa-list modern-nav-icon"></i>
                                <span>{{ translate('Product List') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.product.bulk-import') }}" 
                               class="modern-nav-link text-sm">
                                <i class="fas fa-upload modern-nav-icon"></i>
                                <span>{{ translate('Bulk Import') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['customer_management']))
            <li class="modern-nav-item">
                <a href="{{ route('admin.customer.list') }}" 
                   class="modern-nav-link {{ Request::is('admin/customer*') ? 'active' : '' }}"
                   data-tooltip="Customer Management">
                    <i class="fas fa-users modern-nav-icon"></i>
                    <span>{{ translate('Customer Management') }}</span>
                </a>
            </li>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['promotion_management']))
            <li class="modern-nav-item">
                <div class="modern-nav-group">
                    <button class="modern-nav-link w-full flex items-center justify-between" 
                            onclick="toggleSubmenu('promotions-submenu')"
                            data-tooltip="Promotions">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-percentage modern-nav-icon"></i>
                            <span>{{ translate('Promotions') }}</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" id="promotions-submenu-icon"></i>
                    </button>
                    <ul class="modern-submenu hidden mt-2 ml-8 space-y-1" id="promotions-submenu">
                        <li>
                            <a href="{{ route('admin.coupon.add-new') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/coupon*') ? 'active' : '' }}">
                                <i class="fas fa-ticket-alt modern-nav-icon"></i>
                                <span>{{ translate('Coupons') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.banner.add-new') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/banner*') ? 'active' : '' }}">
                                <i class="fas fa-image modern-nav-icon"></i>
                                <span>{{ translate('Banners') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.notification.add-new') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/notification*') ? 'active' : '' }}">
                                <i class="fas fa-bell modern-nav-icon"></i>
                                <span>{{ translate('Push Notifications') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['report_and_analytics']))
            <li class="modern-nav-item">
                <div class="modern-nav-group">
                    <button class="modern-nav-link w-full flex items-center justify-between" 
                            onclick="toggleSubmenu('reports-submenu')"
                            data-tooltip="Reports & Analytics">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-bar modern-nav-icon"></i>
                            <span>{{ translate('Reports & Analytics') }}</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" id="reports-submenu-icon"></i>
                    </button>
                    <ul class="modern-submenu hidden mt-2 ml-8 space-y-1" id="reports-submenu">
                        <li>
                            <a href="{{ route('admin.report.order') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/report/order*') ? 'active' : '' }}">
                                <i class="fas fa-shopping-cart modern-nav-icon"></i>
                                <span>{{ translate('Order Report') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.report.product') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/report/product*') ? 'active' : '' }}">
                                <i class="fas fa-box modern-nav-icon"></i>
                                <span>{{ translate('Product Report') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.report.customer') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/report/customer*') ? 'active' : '' }}">
                                <i class="fas fa-users modern-nav-icon"></i>
                                <span>{{ translate('Customer Report') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if(Helpers::module_permission_check(MANAGEMENT_SECTION['system_management']))
            <li class="modern-nav-item">
                <div class="modern-nav-group">
                    <button class="modern-nav-link w-full flex items-center justify-between" 
                            onclick="toggleSubmenu('system-submenu')"
                            data-tooltip="System Settings">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cog modern-nav-icon"></i>
                            <span>{{ translate('System Settings') }}</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" id="system-submenu-icon"></i>
                    </button>
                    <ul class="modern-submenu hidden mt-2 ml-8 space-y-1" id="system-submenu">
                        <li>
                            <a href="{{ route('admin.business-settings.restaurant-index') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/business-settings/restaurant*') ? 'active' : '' }}">
                                <i class="fas fa-store modern-nav-icon"></i>
                                <span>{{ translate('Restaurant Settings') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.employee.add-new') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/employee*') ? 'active' : '' }}">
                                <i class="fas fa-user-tie modern-nav-icon"></i>
                                <span>{{ translate('Employees') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.settings') }}" 
                               class="modern-nav-link text-sm {{ Request::is('admin/settings*') ? 'active' : '' }}">
                                <i class="fas fa-sliders-h modern-nav-icon"></i>
                                <span>{{ translate('System Settings') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif
        </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="mt-auto p-4 border-t border-var(--border-color)">
        <div class="flex items-center gap-3 p-3 bg-var(--bg-tertiary) rounded-lg">
            <div class="w-8 h-8 bg-var(--primary-color) rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-var(--text-primary) truncate">
                    {{ auth('admin')->user()->f_name }} {{ auth('admin')->user()->l_name }}
                </p>
                <p class="text-xs text-var(--text-secondary) truncate">
                    {{ auth('admin')->user()->email }}
                </p>
            </div>
        </div>
    </div>
</aside>

<script>
function toggleSubmenu(submenuId) {
    const submenu = document.getElementById(submenuId);
    const icon = document.getElementById(submenuId + '-icon');
    
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        submenu.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

// Auto-expand active submenu
document.addEventListener('DOMContentLoaded', function() {
    const activeLinks = document.querySelectorAll('.modern-nav-link.active');
    activeLinks.forEach(link => {
        const submenu = link.closest('.modern-submenu');
        if (submenu) {
            submenu.classList.remove('hidden');
            const icon = document.querySelector(`#${submenu.id}-icon`);
            if (icon) {
                icon.style.transform = 'rotate(180deg)';
            }
        }
    });
});
</script>
