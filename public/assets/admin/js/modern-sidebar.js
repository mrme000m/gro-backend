/**
 * Modern Sidebar JavaScript
 * Enhanced navigation functionality for GroFresh Admin Panel
 */

class ModernSidebar {
    constructor() {
        this.sidebar = document.getElementById('modernSidebar');
        this.searchInput = document.getElementById('search-sidebar-menu');
        this.searchResults = document.getElementById('searchResults');
        this.isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        this.isMobile = window.innerWidth <= 768;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupSearch();
        this.setupKeyboardShortcuts();
        this.restoreState();
        this.setupResponsive();
    }

    setupEventListeners() {
        // Toggle sidebar
        const toggleBtn = document.getElementById('sidebarToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleSidebar());
        }

        // Handle submenu toggles
        document.querySelectorAll('.nav-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleSubmenu(toggle);
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => this.handleResize());

        // Handle clicks outside sidebar on mobile
        document.addEventListener('click', (e) => this.handleOutsideClick(e));
    }

    setupSearch() {
        if (!this.searchInput) return;

        this.searchInput.addEventListener('input', (e) => {
            this.performSearch(e.target.value);
        });

        this.searchInput.addEventListener('focus', () => {
            this.searchResults.style.display = 'block';
        });

        this.searchInput.addEventListener('blur', () => {
            setTimeout(() => {
                this.searchResults.style.display = 'none';
            }, 200);
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Cmd/Ctrl + K for search
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                this.focusSearch();
            }

            // Escape to close search
            if (e.key === 'Escape') {
                this.clearSearch();
            }

            // Cmd/Ctrl + B to toggle sidebar
            if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
                e.preventDefault();
                this.toggleSidebar();
            }
        });
    }

    toggleSidebar() {
        this.isCollapsed = !this.isCollapsed;
        
        if (this.isCollapsed) {
            this.sidebar.classList.add('collapsed');
        } else {
            this.sidebar.classList.remove('collapsed');
        }

        localStorage.setItem('sidebarCollapsed', this.isCollapsed);
        this.updateLayout();
    }

    toggleSubmenu(toggle) {
        const submenuId = toggle.getAttribute('onclick')?.match(/toggleSubmenu\('([^']+)'\)/)?.[1];
        if (!submenuId) return;

        const submenu = document.getElementById(submenuId);
        const icon = document.getElementById(submenuId + '-icon');
        
        if (!submenu) return;

        const isExpanded = submenu.classList.contains('expanded');
        
        // Close all other submenus
        document.querySelectorAll('.nav-submenu.expanded').forEach(menu => {
            if (menu !== submenu) {
                menu.classList.remove('expanded');
                const menuIcon = document.querySelector(`#${menu.id}-icon`);
                if (menuIcon) {
                    menuIcon.style.transform = 'rotate(0deg)';
                }
            }
        });

        // Toggle current submenu
        if (isExpanded) {
            submenu.classList.remove('expanded');
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            submenu.classList.add('expanded');
            if (icon) icon.style.transform = 'rotate(90deg)';
        }

        // Update toggle state
        toggle.classList.toggle('expanded', !isExpanded);
    }

    performSearch(query) {
        if (!query.trim()) {
            this.searchResults.innerHTML = '';
            this.searchResults.style.display = 'none';
            return;
        }

        const results = this.searchMenuItems(query);
        this.displaySearchResults(results);
    }

    searchMenuItems(query) {
        const menuItems = [];
        
        // Collect all navigation items
        document.querySelectorAll('.nav-link, .submenu-link').forEach(link => {
            const text = link.textContent.trim();
            const href = link.getAttribute('href');
            
            if (text.toLowerCase().includes(query.toLowerCase()) && href) {
                menuItems.push({
                    text: text,
                    href: href,
                    icon: link.querySelector('i')?.className || 'fas fa-link'
                });
            }
        });

        return menuItems.slice(0, 8); // Limit results
    }

    displaySearchResults(results) {
        if (results.length === 0) {
            this.searchResults.innerHTML = '<div class="search-no-results">No results found</div>';
        } else {
            this.searchResults.innerHTML = results.map(result => `
                <a href="${result.href}" class="search-result-item">
                    <i class="${result.icon}"></i>
                    <span>${result.text}</span>
                </a>
            `).join('');
        }
        
        this.searchResults.style.display = 'block';
    }

    focusSearch() {
        if (this.searchInput) {
            this.searchInput.focus();
            this.searchInput.select();
        }
    }

    clearSearch() {
        if (this.searchInput) {
            this.searchInput.value = '';
            this.searchResults.style.display = 'none';
        }
    }

    restoreState() {
        if (this.isCollapsed && !this.isMobile) {
            this.sidebar.classList.add('collapsed');
        }
        
        // Restore expanded submenus
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-submenu').forEach(submenu => {
            const hasActiveLink = submenu.querySelector('.submenu-link.active');
            if (hasActiveLink) {
                submenu.classList.add('expanded');
                const toggle = document.querySelector(`[onclick*="${submenu.id}"]`);
                if (toggle) {
                    toggle.classList.add('expanded');
                    const icon = document.getElementById(submenu.id + '-icon');
                    if (icon) icon.style.transform = 'rotate(90deg)';
                }
            }
        });
    }

    setupResponsive() {
        this.handleResize();
    }

    handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth <= 768;
        
        if (this.isMobile && !wasMobile) {
            // Switched to mobile
            this.sidebar.classList.remove('collapsed');
            this.sidebar.classList.remove('mobile-open');
        } else if (!this.isMobile && wasMobile) {
            // Switched to desktop
            if (this.isCollapsed) {
                this.sidebar.classList.add('collapsed');
            }
        }
        
        this.updateLayout();
    }

    handleOutsideClick(e) {
        if (this.isMobile && 
            this.sidebar.classList.contains('mobile-open') && 
            !this.sidebar.contains(e.target)) {
            this.closeMobileSidebar();
        }
    }

    openMobileSidebar() {
        if (this.isMobile) {
            this.sidebar.classList.add('mobile-open');
            this.createOverlay();
        }
    }

    closeMobileSidebar() {
        if (this.isMobile) {
            this.sidebar.classList.remove('mobile-open');
            this.removeOverlay();
        }
    }

    createOverlay() {
        if (document.querySelector('.sidebar-overlay')) return;
        
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay active';
        overlay.addEventListener('click', () => this.closeMobileSidebar());
        document.body.appendChild(overlay);
    }

    removeOverlay() {
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 300);
        }
    }

    updateLayout() {
        // Trigger layout update event for other components
        window.dispatchEvent(new CustomEvent('sidebarToggle', {
            detail: { 
                collapsed: this.isCollapsed,
                mobile: this.isMobile 
            }
        }));
    }
}

// Global functions for backward compatibility
function toggleSidebar() {
    if (window.modernSidebar) {
        window.modernSidebar.toggleSidebar();
    }
}

function toggleSubmenu(submenuId) {
    if (window.modernSidebar) {
        const toggle = document.querySelector(`[onclick*="${submenuId}"]`);
        if (toggle) {
            window.modernSidebar.toggleSubmenu(toggle);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.modernSidebar = new ModernSidebar();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernSidebar;
}
