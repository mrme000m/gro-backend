/**
 * Modern Dashboard JavaScript Framework
 * Provides theme management, animations, and interactive components
 */

class ModernDashboard {
    constructor() {
        this.theme = localStorage.getItem('dashboard-theme') || 'light';
        this.sidebarOpen = window.innerWidth > 768;
        this.init();
    }

    init() {
        this.initTheme();
        this.initSidebar();
        this.initAnimations();
        this.initComponents();
        this.initEventListeners();
        this.initTooltips();
        this.initCharts();
    }

    // Theme Management
    initTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);
        this.updateThemeToggle();
    }

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', this.theme);
        localStorage.setItem('dashboard-theme', this.theme);
        this.updateThemeToggle();
        this.animateThemeChange();
    }

    updateThemeToggle() {
        const toggleBtn = document.querySelector('.theme-toggle');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = this.theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }
    }

    animateThemeChange() {
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    }

    // Sidebar Management
    initSidebar() {
        const sidebar = document.querySelector('.modern-sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open', this.sidebarOpen);
        }
    }

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        const sidebar = document.querySelector('.modern-sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open', this.sidebarOpen);
        }
    }

    // Animations
    initAnimations() {
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all cards and components
        document.querySelectorAll('.modern-card, .stat-card, .chart-container').forEach(el => {
            observer.observe(el);
        });
    }

    // Component Initialization
    initComponents() {
        this.initCounters();
        this.initProgressBars();
        this.initSearchFilter();
        this.initNotifications();
    }

    initCounters() {
        const counters = document.querySelectorAll('.stat-value[data-count]');
        counters.forEach(counter => {
            const target = parseInt(counter.dataset.count);
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;

            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            };

            // Start animation when element is visible
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCounter();
                        observer.unobserve(entry.target);
                    }
                });
            });

            observer.observe(counter);
        });
    }

    initProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar[data-progress]');
        progressBars.forEach(bar => {
            const progress = parseInt(bar.dataset.progress);
            setTimeout(() => {
                bar.style.width = progress + '%';
            }, 500);
        });
    }

    initSearchFilter() {
        const searchInput = document.querySelector('#search-sidebar-menu');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const menuItems = document.querySelectorAll('.modern-nav-link');
                
                menuItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const parent = item.closest('.modern-nav-item');
                    if (text.includes(query)) {
                        parent.style.display = 'block';
                        item.classList.add('highlight-search');
                    } else {
                        parent.style.display = query ? 'none' : 'block';
                        item.classList.remove('highlight-search');
                    }
                });
            });
        }
    }

    initNotifications() {
        // Real-time notification system
        this.checkNotifications();
        setInterval(() => this.checkNotifications(), 30000); // Check every 30 seconds
    }

    checkNotifications() {
        // This would typically make an AJAX call to check for new notifications
        // For now, we'll simulate it
        const notificationBadge = document.querySelector('.notification-badge');
        if (notificationBadge) {
            // Simulate random notifications for demo
            const hasNewNotifications = Math.random() > 0.7;
            notificationBadge.style.display = hasNewNotifications ? 'block' : 'none';
        }
    }

    // Event Listeners
    initEventListeners() {
        // Theme toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('.theme-toggle')) {
                this.toggleTheme();
            }
        });

        // Sidebar toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('.sidebar-toggle')) {
                this.toggleSidebar();
            }
        });

        // Card hover effects
        document.querySelectorAll('.modern-card, .stat-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });

        // Responsive sidebar
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                this.sidebarOpen = false;
            } else {
                this.sidebarOpen = true;
            }
            this.initSidebar();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('#search-sidebar-menu');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Ctrl/Cmd + D for theme toggle
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                this.toggleTheme();
            }
        });
    }

    // Tooltips
    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'modern-tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        setTimeout(() => tooltip.classList.add('show'), 10);
    }

    hideTooltip() {
        const tooltip = document.querySelector('.modern-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    // Chart Initialization
    initCharts() {
        // Initialize modern charts with Chart.js or ApexCharts
        this.initRevenueChart();
        this.initOrdersChart();
        this.initCustomerChart();
    }

    initRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (ctx && typeof Chart !== 'undefined') {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    }

    initOrdersChart() {
        const ctx = document.getElementById('ordersChart');
        if (ctx && typeof Chart !== 'undefined') {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Delivered', 'Processing', 'Pending', 'Cancelled'],
                    datasets: [{
                        data: [65, 20, 10, 5],
                        backgroundColor: [
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(59, 130, 246)',
                            'rgb(239, 68, 68)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    initCustomerChart() {
        const ctx = document.getElementById('customerChart');
        if (ctx && typeof Chart !== 'undefined') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'New Customers',
                        data: [12, 19, 15, 25, 22, 30, 28],
                        backgroundColor: 'rgba(6, 182, 212, 0.8)',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    }

    // Utility Methods
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `modern-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 10);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);

        // Manual close
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
    }

    updateStatCard(selector, value, change = null) {
        const card = document.querySelector(selector);
        if (card) {
            const valueElement = card.querySelector('.stat-value');
            const changeElement = card.querySelector('.stat-change');
            
            if (valueElement) {
                valueElement.textContent = value.toLocaleString();
            }
            
            if (changeElement && change !== null) {
                changeElement.textContent = `${change > 0 ? '+' : ''}${change}%`;
                changeElement.className = `stat-change ${change > 0 ? 'positive' : 'negative'}`;
            }
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.modernDashboard = new ModernDashboard();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernDashboard;
}
