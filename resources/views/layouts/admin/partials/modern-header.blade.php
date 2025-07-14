<header class="modern-header">
    <div class="modern-header-left">
        <!-- Mobile Sidebar Toggle -->
        <button class="sidebar-toggle"
                style="display: none; padding: 8px; border-radius: 8px; background: none; border: none; cursor: pointer; transition: background-color 0.3s ease;"
                data-tooltip="Toggle Sidebar"
                onmouseover="this.style.backgroundColor='var(--bg-tertiary)'"
                onmouseout="this.style.backgroundColor='transparent'">
            <i class="fas fa-bars" style="color: var(--text-primary);"></i>
        </button>

        <style>
        @media (max-width: 1024px) {
            .sidebar-toggle {
                display: block !important;
            }
        }
        </style>

        <!-- Breadcrumb -->
        <nav style="display: none; align-items: center; gap: 8px; font-size: 0.875rem;">

        <style>
        @media (min-width: 768px) {
            .modern-header nav {
                display: flex !important;
            }
        }
        </style>
            <a href="{{ route('admin.dashboard') }}" 
               class="text-var(--text-secondary) hover:text-var(--primary-color) transition-colors">
                {{ translate('Dashboard') }}
            </a>
            @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                @foreach($breadcrumbs as $breadcrumb)
                    <i class="fas fa-chevron-right text-var(--text-muted) text-xs"></i>
                    @if($loop->last)
                        <span class="text-var(--text-primary) font-medium">{{ $breadcrumb['title'] }}</span>
                    @else
                        <a href="{{ $breadcrumb['url'] ?? '#' }}" 
                           class="text-var(--text-secondary) hover:text-var(--primary-color) transition-colors">
                            {{ $breadcrumb['title'] }}
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>
    </div>

    <div class="modern-header-right">
        <!-- Quick Actions -->
        <div class="hidden md:flex items-center space-x-2">
            <!-- Quick Add Product -->
            <a href="{{ route('admin.product.add-new') }}" 
               class="btn-modern btn-modern-secondary"
               data-tooltip="Quick Add Product">
                <i class="fas fa-plus"></i>
                <span class="hidden lg:inline">{{ translate('Add Product') }}</span>
            </a>

            <!-- Quick POS -->
            <a href="{{ route('admin.pos.index') }}" 
               class="btn-modern btn-modern-primary"
               data-tooltip="Open POS">
                <i class="fas fa-cash-register"></i>
                <span class="hidden lg:inline">{{ translate('POS') }}</span>
            </a>
        </div>

        <!-- Search -->
        <div class="relative hidden lg:block">
            <input type="text" 
                   placeholder="{{ translate('Search orders, products...') }}" 
                   class="w-64 px-4 py-2 pl-10 bg-var(--bg-tertiary) border border-var(--border-color) rounded-lg focus:outline-none focus:ring-2 focus:ring-var(--primary-color) focus:border-transparent transition-all"
                   id="global-search">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-var(--text-secondary)"></i>
        </div>

        <!-- Notifications -->
        <div class="relative">
            <button class="p-2 rounded-lg hover:bg-var(--bg-tertiary) transition-colors relative"
                    onclick="toggleNotifications()"
                    data-tooltip="Notifications">
                <i class="fas fa-bell text-var(--text-primary)"></i>
                <span class="notification-badge absolute -top-1 -right-1 w-5 h-5 bg-var(--danger-color) text-white text-xs rounded-full flex items-center justify-center" 
                      style="display: none;">3</span>
            </button>

            <!-- Notifications Dropdown -->
            <div style="position: absolute; right: 0; margin-top: 8px; width: 320px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 50; display: none;"
                 id="notifications-dropdown">
                <div style="padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h3 style="font-weight: 600; color: #374151;">{{ translate('Notifications') }}</h3>
                        <button style="color: #6366f1; font-size: 0.875rem; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            {{ translate('Mark all read') }}
                        </button>
                    </div>
                </div>
                <div style="max-height: 384px; overflow-y: auto;">
                    <!-- Sample notifications -->
                    <div style="padding: 1rem; border-bottom: 1px solid #e5e7eb; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-shopping-cart" style="color: white; font-size: 0.875rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-size: 0.875rem; font-weight: 500; color: #374151;">{{ translate('New Order Received') }}</p>
                                <p style="font-size: 0.75rem; color: #6b7280;">{{ translate('Order #12345 from John Doe') }}</p>
                                <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 4px;">{{ translate('2 minutes ago') }}</p>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1rem; border-bottom: 1px solid #e5e7eb; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation-triangle" style="color: white; font-size: 0.875rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-size: 0.875rem; font-weight: 500; color: #374151;">{{ translate('Low Stock Alert') }}</p>
                                <p style="font-size: 0.75rem; color: #6b7280;">{{ translate('Product "Fresh Apples" is running low') }}</p>
                                <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 4px;">{{ translate('5 minutes ago') }}</p>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1rem; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user" style="color: white; font-size: 0.875rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-size: 0.875rem; font-weight: 500; color: #374151;">{{ translate('New Customer Registration') }}</p>
                                <p style="font-size: 0.75rem; color: #6b7280;">{{ translate('Jane Smith just registered') }}</p>
                                <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 4px;">{{ translate('10 minutes ago') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="#" style="color: #6366f1; font-size: 0.875rem; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                        {{ translate('View all notifications') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Theme Toggle -->
        <button class="theme-toggle"
                data-tooltip="Toggle Theme"
                style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px;"
                onmouseover="this.style.background='#ffffff'; this.style.transform='scale(1.05)'"
                onmouseout="this.style.background='#f1f5f9'; this.style.transform='scale(1)'">
            <i class="fas fa-moon" style="color: #64748b;"></i>
        </button>

        <!-- User Profile -->
        <div class="relative">
            <button style="display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 8px; background: none; border: none; cursor: pointer; transition: background-color 0.3s ease;"
                    onclick="toggleUserMenu()"
                    data-tooltip="User Menu"
                    onmouseover="this.style.backgroundColor='#f3f4f6'"
                    onmouseout="this.style.backgroundColor='transparent'">
                <div style="width: 32px; height: 32px; background: #6366f1; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user" style="color: white; font-size: 0.875rem;"></i>
                </div>
                <div style="display: none; text-align: left;">
                    <p style="font-size: 0.875rem; font-weight: 500; color: #374151;">
                        {{ auth('admin')->user()->f_name }} {{ auth('admin')->user()->l_name }}
                    </p>
                    <p style="font-size: 0.75rem; color: #6b7280;">{{ translate('Administrator') }}</p>
                </div>
                <i class="fas fa-chevron-down" style="color: #6b7280; font-size: 0.875rem;"></i>
            </button>

            <style>
            @media (min-width: 768px) {
                .modern-header button div[style*="display: none"] {
                    display: block !important;
                }
            }
            </style>

            <!-- User Menu Dropdown -->
            <div style="position: absolute; right: 0; margin-top: 8px; width: 192px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 50; display: none;"
                 id="user-menu-dropdown">
                <div style="padding: 8px;">
                    <a href="{{ route('admin.settings') }}"
                       style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 6px; text-decoration: none; transition: background-color 0.3s ease;"
                       onmouseover="this.style.backgroundColor='#f3f4f6'"
                       onmouseout="this.style.backgroundColor='transparent'">
                        <i class="fas fa-user-cog" style="color: #6b7280;"></i>
                        <span style="font-size: 0.875rem; color: #374151;">{{ translate('Profile Settings') }}</span>
                    </a>
                    <a href="{{ route('admin.business-settings.store.ecom-setup') }}"
                       style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 6px; text-decoration: none; transition: background-color 0.3s ease;"
                       onmouseover="this.style.backgroundColor='#f3f4f6'"
                       onmouseout="this.style.backgroundColor='transparent'">
                        <i class="fas fa-cog" style="color: #6b7280;"></i>
                        <span style="font-size: 0.875rem; color: #374151;">{{ translate('System Settings') }}</span>
                    </a>
                    <div style="border-top: 1px solid #e5e7eb; margin: 8px 0;"></div>
                    <a href="{{ route('admin.auth.logout') }}"
                       style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 6px; text-decoration: none; transition: all 0.3s ease;"
                       onmouseover="this.style.backgroundColor='#ef4444'; this.style.color='white'; this.querySelector('i').style.color='white'"
                       onmouseout="this.style.backgroundColor='transparent'; this.style.color='#374151'; this.querySelector('i').style.color='#6b7280'">
                        <i class="fas fa-sign-out-alt" style="color: #6b7280;"></i>
                        <span style="font-size: 0.875rem; color: #374151;">{{ translate('Logout') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notifications-dropdown');
    const userDropdown = document.getElementById('user-menu-dropdown');

    // Close user menu if open
    userDropdown.style.display = 'none';

    // Toggle notifications
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function toggleUserMenu() {
    const dropdown = document.getElementById('user-menu-dropdown');
    const notificationsDropdown = document.getElementById('notifications-dropdown');

    // Close notifications if open
    notificationsDropdown.style.display = 'none';

    // Toggle user menu
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const userDropdown = document.getElementById('user-menu-dropdown');

    // Check if click is outside both dropdowns and their triggers
    if (!event.target.closest('[onclick*="toggleNotifications"]') &&
        !event.target.closest('#notifications-dropdown') &&
        !event.target.closest('[onclick*="toggleUserMenu"]') &&
        !event.target.closest('#user-menu-dropdown')) {
        notificationsDropdown.style.display = 'none';
        userDropdown.style.display = 'none';
    }
});

// Global search functionality
document.getElementById('global-search')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    
    if (query.length > 2) {
        // Implement global search logic here
        console.log('Searching for:', query);
        
        // You can make AJAX calls to search orders, products, customers, etc.
        // Example:
        // fetch(`/admin/search?q=${encodeURIComponent(query)}`)
        //     .then(response => response.json())
        //     .then(data => {
        //         // Show search results
        //     });
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Shift + P for POS
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'P') {
        e.preventDefault();
        window.location.href = '{{ route("admin.pos.index") }}';
    }
    
    // Ctrl/Cmd + Shift + O for Orders
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'O') {
        e.preventDefault();
        window.location.href = '{{ route("admin.orders.list", ["status" => "all"]) }}';
    }
    
    // Escape to close dropdowns
    if (e.key === 'Escape') {
        document.getElementById('notifications-dropdown').style.display = 'none';
        document.getElementById('user-menu-dropdown').style.display = 'none';
    }
});
</script>
