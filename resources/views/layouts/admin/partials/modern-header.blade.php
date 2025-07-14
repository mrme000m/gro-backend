<header class="modern-header">
    <div class="modern-header-left">
        <!-- Mobile Sidebar Toggle -->
        <button class="sidebar-toggle lg:hidden p-2 rounded-lg hover:bg-var(--bg-tertiary) transition-colors" 
                data-tooltip="Toggle Sidebar">
            <i class="fas fa-bars text-var(--text-primary)"></i>
        </button>

        <!-- Breadcrumb -->
        <nav class="hidden md:flex items-center space-x-2 text-sm">
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
            <div class="absolute right-0 mt-2 w-80 bg-var(--bg-card) border border-var(--border-color) rounded-lg shadow-lg z-50 hidden"
                 id="notifications-dropdown">
                <div class="p-4 border-b border-var(--border-color)">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-var(--text-primary)">{{ translate('Notifications') }}</h3>
                        <button class="text-var(--primary-color) text-sm hover:underline">
                            {{ translate('Mark all read') }}
                        </button>
                    </div>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <!-- Sample notifications -->
                    <div class="p-4 border-b border-var(--border-color) hover:bg-var(--bg-secondary) transition-colors">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-var(--success-color) rounded-full flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-var(--text-primary)">{{ translate('New Order Received') }}</p>
                                <p class="text-xs text-var(--text-secondary)">{{ translate('Order #12345 from John Doe') }}</p>
                                <p class="text-xs text-var(--text-muted) mt-1">{{ translate('2 minutes ago') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-b border-var(--border-color) hover:bg-var(--bg-secondary) transition-colors">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-var(--warning-color) rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-var(--text-primary)">{{ translate('Low Stock Alert') }}</p>
                                <p class="text-xs text-var(--text-secondary)">{{ translate('Product "Fresh Apples" is running low') }}</p>
                                <p class="text-xs text-var(--text-muted) mt-1">{{ translate('5 minutes ago') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 hover:bg-var(--bg-secondary) transition-colors">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-var(--info-color) rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-var(--text-primary)">{{ translate('New Customer Registration') }}</p>
                                <p class="text-xs text-var(--text-secondary)">{{ translate('Jane Smith just registered') }}</p>
                                <p class="text-xs text-var(--text-muted) mt-1">{{ translate('10 minutes ago') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-var(--border-color)">
                    <a href="#" class="text-var(--primary-color) text-sm hover:underline">
                        {{ translate('View all notifications') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Theme Toggle -->
        <button class="theme-toggle" data-tooltip="Toggle Theme">
            <i class="fas fa-moon"></i>
        </button>

        <!-- User Profile -->
        <div class="relative">
            <button class="flex items-center space-x-2 p-2 rounded-lg hover:bg-var(--bg-tertiary) transition-colors"
                    onclick="toggleUserMenu()"
                    data-tooltip="User Menu">
                <div class="w-8 h-8 bg-var(--primary-color) rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-sm"></i>
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-sm font-medium text-var(--text-primary)">
                        {{ auth('admin')->user()->f_name }} {{ auth('admin')->user()->l_name }}
                    </p>
                    <p class="text-xs text-var(--text-secondary)">{{ translate('Administrator') }}</p>
                </div>
                <i class="fas fa-chevron-down text-var(--text-secondary) text-sm"></i>
            </button>

            <!-- User Menu Dropdown -->
            <div class="absolute right-0 mt-2 w-48 bg-var(--bg-card) border border-var(--border-color) rounded-lg shadow-lg z-50 hidden"
                 id="user-menu-dropdown">
                <div class="p-2">
                    <a href="{{ route('admin.settings') }}" 
                       class="flex items-center space-x-2 px-3 py-2 rounded-md hover:bg-var(--bg-secondary) transition-colors">
                        <i class="fas fa-user-cog text-var(--text-secondary)"></i>
                        <span class="text-sm text-var(--text-primary)">{{ translate('Profile Settings') }}</span>
                    </a>
                    <a href="{{ route('admin.business-settings.restaurant-index') }}" 
                       class="flex items-center space-x-2 px-3 py-2 rounded-md hover:bg-var(--bg-secondary) transition-colors">
                        <i class="fas fa-cog text-var(--text-secondary)"></i>
                        <span class="text-sm text-var(--text-primary)">{{ translate('System Settings') }}</span>
                    </a>
                    <div class="border-t border-var(--border-color) my-2"></div>
                    <a href="{{ route('admin.auth.logout') }}" 
                       class="flex items-center space-x-2 px-3 py-2 rounded-md hover:bg-var(--danger-color) hover:text-white transition-colors">
                        <i class="fas fa-sign-out-alt text-var(--text-secondary)"></i>
                        <span class="text-sm text-var(--text-primary)">{{ translate('Logout') }}</span>
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
    userDropdown.classList.add('hidden');
    
    // Toggle notifications
    dropdown.classList.toggle('hidden');
}

function toggleUserMenu() {
    const dropdown = document.getElementById('user-menu-dropdown');
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    
    // Close notifications if open
    notificationsDropdown.classList.add('hidden');
    
    // Toggle user menu
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const userDropdown = document.getElementById('user-menu-dropdown');
    
    if (!event.target.closest('.relative')) {
        notificationsDropdown.classList.add('hidden');
        userDropdown.classList.add('hidden');
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
        document.getElementById('notifications-dropdown').classList.add('hidden');
        document.getElementById('user-menu-dropdown').classList.add('hidden');
    }
});
</script>
