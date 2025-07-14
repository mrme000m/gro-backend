<header class="modern-header">
    <div class="header-left">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-home"></i>
                        {{ translate('Dashboard') }}
                    </a>
                </li>
                @if(Request::segment(2))
                    <li class="breadcrumb-item">
                        {{ ucfirst(str_replace('-', ' ', Request::segment(2))) }}
                    </li>
                @endif
                @if(Request::segment(3))
                    <li class="breadcrumb-item active">
                        {{ ucfirst(str_replace('-', ' ', Request::segment(3))) }}
                    </li>
                @endif
            </ol>
        </nav>
    </div>

    <div class="header-right">
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="quick-action-btn" onclick="window.location.href='{{ route('admin.pos.index') }}'"
                    data-tooltip="POS System">
                <i class="fas fa-cash-register"></i>
            </button>

            <button class="quick-action-btn" onclick="window.location.href='{{ route('admin.product.add-new') }}'"
                    data-tooltip="Add Product">
                <i class="fas fa-plus"></i>
            </button>

            <button class="quick-action-btn" onclick="window.location.href='{{ route('admin.orders.list', ['status' => 'pending']) }}'"
                    data-tooltip="Pending Orders">
                <i class="fas fa-clock"></i>
                @if(\App\Model\Order::where(['order_status'=>'pending'])->count() > 0)
                    <span class="notification-badge">{{ \App\Model\Order::where(['order_status'=>'pending'])->count() }}</span>
                @endif
            </button>
        </div>

        <!-- Theme Toggle -->
        <button class="theme-toggle" onclick="toggleTheme()" data-tooltip="Toggle Theme">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>

        <!-- User Menu -->
        <div class="user-menu-dropdown">
            <button class="user-menu-btn" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    <img src="{{ asset('assets/admin/img/160x160/img1.jpg') }}" alt="User">
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth('admin')->user()->f_name ?? 'Admin' }}</div>
                    <div class="user-role">{{ translate('Administrator') }}</div>
                </div>
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="user-menu-panel" id="userMenuPanel">
                <div class="user-menu-header">
                    <div class="user-avatar-large">
                        <img src="{{ asset('assets/admin/img/160x160/img1.jpg') }}" alt="User">
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ auth('admin')->user()->f_name ?? 'Admin' }}</div>
                        <div class="user-email">{{ auth('admin')->user()->email ?? 'admin@example.com' }}</div>
                    </div>
                </div>

                <div class="user-menu-items">
                    <a href="{{ route('admin.settings') }}" class="user-menu-item">
                        <i class="fas fa-user-cog"></i>
                        <span>{{ translate('Profile Settings') }}</span>
                    </a>

                    <a href="{{ route('admin.business-settings.restaurant-index') }}" class="user-menu-item">
                        <i class="fas fa-cog"></i>
                        <span>{{ translate('System Settings') }}</span>
                    </a>

                    <div class="menu-divider"></div>

                    <a href="{{ route('admin.auth.logout') }}" class="user-menu-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>{{ translate('Logout') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
/* Modern Header Styles */
.modern-header {
    background: var(--bg-header);
    border-bottom: 1px solid var(--border-color);
    padding: var(--spacing-4) var(--spacing-6);
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: var(--z-sticky);
    backdrop-filter: blur(8px);
    box-shadow: var(--shadow-sm);
}

.header-left {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
}

.header-right {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.mobile-menu-toggle {
    background: none;
    border: none;
    font-size: var(--font-size-lg);
    color: var(--text-primary);
    cursor: pointer;
    padding: var(--spacing-2);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
    display: none;
}

.mobile-menu-toggle:hover {
    background: var(--bg-tertiary);
}

/* Breadcrumb */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin: 0;
    padding: 0;
    list-style: none;
    font-size: var(--font-size-sm);
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item:not(:last-child)::after {
    content: '/';
    margin-left: var(--spacing-2);
    color: var(--text-muted);
}

.breadcrumb-item a {
    color: var(--text-secondary);
    text-decoration: none;
    transition: color var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
}

.breadcrumb-item a:hover {
    color: var(--brand-primary);
}

.breadcrumb-item.active {
    color: var(--text-primary);
    font-weight: var(--font-weight-medium);
}

/* Quick Actions */
.quick-actions {
    display: flex;
    gap: var(--spacing-2);
}

.quick-action-btn {
    position: relative;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2-5);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.quick-action-btn:hover {
    background: var(--brand-primary);
    color: white;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.notification-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: var(--danger-color);
    color: white;
    border-radius: var(--radius-full);
    padding: 2px 6px;
    font-size: 10px;
    font-weight: var(--font-weight-bold);
    min-width: 18px;
    text-align: center;
}

/* Theme Toggle */
.theme-toggle {
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2-5);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.theme-toggle:hover {
    background: var(--brand-primary);
    color: white;
    transform: scale(1.05);
}

/* User Menu */
.user-menu-dropdown {
    position: relative;
}

.user-menu-btn {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--spacing-2);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
}

.user-menu-btn:hover {
    background: var(--bg-tertiary);
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-full);
    overflow: hidden;
    border: 2px solid var(--border-color);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    text-align: left;
}

.user-name {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    line-height: var(--line-height-tight);
}

.user-role {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    line-height: var(--line-height-tight);
}

/* Responsive */
@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: block !important;
    }

    .breadcrumb {
        display: none;
    }

    .quick-actions {
        display: none;
    }

    .user-info {
        display: none;
    }
}
</style>

<script>
function toggleMobileSidebar() {
    if (window.modernSidebar) {
        window.modernSidebar.openMobileSidebar();
    }
}

function toggleUserMenu() {
    const panel = document.getElementById('userMenuPanel');
    if (panel) {
        panel.classList.toggle('show');
    }
}

function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('themeIcon');

    if (html.getAttribute('data-theme') === 'dark') {
        html.removeAttribute('data-theme');
        if (icon) icon.className = 'fas fa-moon';
        localStorage.setItem('theme', 'light');
    } else {
        html.setAttribute('data-theme', 'dark');
        if (icon) icon.className = 'fas fa-sun';
        localStorage.setItem('theme', 'dark');
    }
}

// Initialize theme
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    const icon = document.getElementById('themeIcon');

    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        if (icon) icon.className = 'fas fa-sun';
    }
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-menu-dropdown')) {
        const panel = document.getElementById('userMenuPanel');
        if (panel) panel.classList.remove('show');
    }
});

// Handle sidebar layout changes
window.addEventListener('sidebarToggle', function(e) {
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        if (e.detail.collapsed && !e.detail.mobile) {
            mainContent.classList.add('sidebar-collapsed');
        } else {
            mainContent.classList.remove('sidebar-collapsed');
        }
    }
});
</script>

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
