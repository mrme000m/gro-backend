/**
 * GroFresh Admin Accessibility Framework
 * 
 * Provides ARIA-compliant components, keyboard navigation,
 * screen reader support, and accessibility utilities.
 */

class AccessibilityManager {
    constructor() {
        this.focusableSelectors = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])',
            '[contenteditable="true"]'
        ].join(', ');
        
        this.init();
    }
    
    init() {
        this.setupKeyboardNavigation();
        this.setupFocusManagement();
        this.setupARIAEnhancements();
        this.setupSkipLinks();
        this.setupAnnouncements();
    }
    
    setupKeyboardNavigation() {
        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Alt + M: Focus main content
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                this.focusMainContent();
            }
            
            // Alt + N: Focus navigation
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                this.focusNavigation();
            }
            
            // Alt + S: Focus search
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                this.focusSearch();
            }
            
            // Escape: Close modals/dropdowns
            if (e.key === 'Escape') {
                this.handleEscape();
            }
        });
        
        // Enhanced table navigation
        this.setupTableNavigation();
        
        // Enhanced form navigation
        this.setupFormNavigation();
    }
    
    setupTableNavigation() {
        document.querySelectorAll('table').forEach(table => {
            table.addEventListener('keydown', (e) => {
                const cell = e.target.closest('td, th');
                if (!cell) return;
                
                const row = cell.parentElement;
                const cellIndex = Array.from(row.children).indexOf(cell);
                const rowIndex = Array.from(row.parentElement.children).indexOf(row);
                
                let targetCell = null;
                
                switch (e.key) {
                    case 'ArrowUp':
                        e.preventDefault();
                        const prevRow = row.parentElement.children[rowIndex - 1];
                        if (prevRow) {
                            targetCell = prevRow.children[cellIndex];
                        }
                        break;
                        
                    case 'ArrowDown':
                        e.preventDefault();
                        const nextRow = row.parentElement.children[rowIndex + 1];
                        if (nextRow) {
                            targetCell = nextRow.children[cellIndex];
                        }
                        break;
                        
                    case 'ArrowLeft':
                        e.preventDefault();
                        targetCell = cell.previousElementSibling;
                        break;
                        
                    case 'ArrowRight':
                        e.preventDefault();
                        targetCell = cell.nextElementSibling;
                        break;
                        
                    case 'Home':
                        e.preventDefault();
                        targetCell = row.firstElementChild;
                        break;
                        
                    case 'End':
                        e.preventDefault();
                        targetCell = row.lastElementChild;
                        break;
                }
                
                if (targetCell) {
                    const focusable = targetCell.querySelector(this.focusableSelectors) || targetCell;
                    if (focusable.tabIndex === undefined) {
                        focusable.tabIndex = 0;
                    }
                    focusable.focus();
                }
            });
        });
    }
    
    setupFormNavigation() {
        document.querySelectorAll('form').forEach(form => {
            // Add form landmarks
            if (!form.getAttribute('role')) {
                form.setAttribute('role', 'form');
            }
            
            // Enhance fieldsets
            form.querySelectorAll('fieldset').forEach(fieldset => {
                if (!fieldset.getAttribute('role')) {
                    fieldset.setAttribute('role', 'group');
                }
            });
        });
    }
    
    setupFocusManagement() {
        // Focus trap for modals
        document.addEventListener('focusin', (e) => {
            const modal = e.target.closest('.modal');
            if (modal && modal.classList.contains('show')) {
                this.trapFocus(modal, e);
            }
        });
        
        // Focus restoration
        this.setupFocusRestoration();
    }
    
    trapFocus(container, event) {
        const focusableElements = container.querySelectorAll(this.focusableSelectors);
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        if (event.shiftKey && event.target === firstFocusable) {
            event.preventDefault();
            lastFocusable.focus();
        } else if (!event.shiftKey && event.target === lastFocusable) {
            event.preventDefault();
            firstFocusable.focus();
        }
    }
    
    setupFocusRestoration() {
        let lastFocusedElement = null;
        
        // Store focus before modal opens
        document.addEventListener('show.bs.modal', (e) => {
            lastFocusedElement = document.activeElement;
        });
        
        // Restore focus when modal closes
        document.addEventListener('hidden.bs.modal', (e) => {
            if (lastFocusedElement) {
                lastFocusedElement.focus();
                lastFocusedElement = null;
            }
        });
    }
    
    setupARIAEnhancements() {
        // Auto-enhance form controls
        this.enhanceFormControls();
        
        // Auto-enhance navigation
        this.enhanceNavigation();
        
        // Auto-enhance data tables
        this.enhanceDataTables();
        
        // Auto-enhance buttons
        this.enhanceButtons();
    }
    
    enhanceFormControls() {
        document.querySelectorAll('input, select, textarea').forEach(control => {
            // Add required indicator to ARIA label
            if (control.hasAttribute('required')) {
                const label = document.querySelector(`label[for="${control.id}"]`);
                if (label && !label.textContent.includes('required')) {
                    const ariaLabel = control.getAttribute('aria-label') || label.textContent;
                    control.setAttribute('aria-label', `${ariaLabel} (required)`);
                }
            }
            
            // Enhance error states
            if (control.classList.contains('is-invalid')) {
                control.setAttribute('aria-invalid', 'true');
            }
            
            // Add descriptions for help text
            const helpText = control.parentElement.querySelector('.form-help-text, .form-text');
            if (helpText && !helpText.id) {
                const helpId = `${control.id || control.name}_help`;
                helpText.id = helpId;
                
                const describedBy = control.getAttribute('aria-describedby');
                control.setAttribute('aria-describedby', 
                    describedBy ? `${describedBy} ${helpId}` : helpId
                );
            }
        });
    }
    
    enhanceNavigation() {
        // Main navigation
        const mainNav = document.querySelector('.navbar-nav, .sidebar-nav, .modern-sidebar-nav');
        if (mainNav && !mainNav.getAttribute('role')) {
            mainNav.setAttribute('role', 'navigation');
            mainNav.setAttribute('aria-label', 'Main navigation');
        }
        
        // Breadcrumbs
        document.querySelectorAll('.breadcrumb').forEach(breadcrumb => {
            breadcrumb.setAttribute('role', 'navigation');
            breadcrumb.setAttribute('aria-label', 'Breadcrumb');
        });
        
        // Pagination
        document.querySelectorAll('.pagination').forEach(pagination => {
            pagination.setAttribute('role', 'navigation');
            pagination.setAttribute('aria-label', 'Pagination');
        });
    }
    
    enhanceDataTables() {
        document.querySelectorAll('table').forEach(table => {
            // Add table role and caption if missing
            if (!table.getAttribute('role')) {
                table.setAttribute('role', 'table');
            }
            
            // Enhance sortable columns
            table.querySelectorAll('th[data-sort], .sortable').forEach(header => {
                header.setAttribute('role', 'columnheader');
                header.setAttribute('tabindex', '0');
                
                if (!header.getAttribute('aria-sort')) {
                    header.setAttribute('aria-sort', 'none');
                }
                
                // Add keyboard support for sorting
                header.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        header.click();
                    }
                });
            });
            
            // Add row selection support
            table.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    checkbox.addEventListener('change', () => {
                        row.setAttribute('aria-selected', checkbox.checked);
                    });
                }
            });
        });
    }
    
    enhanceButtons() {
        document.querySelectorAll('button, .btn').forEach(button => {
            // Add loading state support
            if (button.hasAttribute('data-loading-text')) {
                const originalText = button.textContent;
                button.addEventListener('click', () => {
                    if (button.disabled) return;
                    
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                    button.textContent = button.getAttribute('data-loading-text');
                    
                    // Auto-restore after 5 seconds (fallback)
                    setTimeout(() => {
                        button.disabled = false;
                        button.removeAttribute('aria-busy');
                        button.textContent = originalText;
                    }, 5000);
                });
            }
            
            // Enhance toggle buttons
            if (button.hasAttribute('data-toggle')) {
                button.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    setupSkipLinks() {
        // Create skip links if they don't exist
        if (!document.querySelector('.skip-links')) {
            const skipLinks = document.createElement('div');
            skipLinks.className = 'skip-links';
            skipLinks.innerHTML = `
                <a href="#main-content" class="skip-link">Skip to main content</a>
                <a href="#navigation" class="skip-link">Skip to navigation</a>
            `;
            
            document.body.insertBefore(skipLinks, document.body.firstChild);
            
            // Add CSS for skip links
            const style = document.createElement('style');
            style.textContent = `
                .skip-links {
                    position: absolute;
                    top: -40px;
                    left: 6px;
                    z-index: 1000;
                }
                .skip-link {
                    position: absolute;
                    top: -40px;
                    left: 6px;
                    background: #000;
                    color: #fff;
                    padding: 8px;
                    text-decoration: none;
                    border-radius: 4px;
                    z-index: 1001;
                }
                .skip-link:focus {
                    top: 6px;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    setupAnnouncements() {
        // Create live region for announcements
        if (!document.getElementById('aria-live-region')) {
            const liveRegion = document.createElement('div');
            liveRegion.id = 'aria-live-region';
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            liveRegion.style.cssText = `
                position: absolute;
                left: -10000px;
                width: 1px;
                height: 1px;
                overflow: hidden;
            `;
            document.body.appendChild(liveRegion);
        }
    }
    
    // Public API
    announce(message, priority = 'polite') {
        const liveRegion = document.getElementById('aria-live-region');
        if (liveRegion) {
            liveRegion.setAttribute('aria-live', priority);
            liveRegion.textContent = message;
            
            // Clear after announcement
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }
    
    focusMainContent() {
        const main = document.querySelector('#main-content, main, [role="main"]');
        if (main) {
            main.focus();
            main.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    focusNavigation() {
        const nav = document.querySelector('#navigation, nav, [role="navigation"]');
        if (nav) {
            const firstLink = nav.querySelector('a, button');
            if (firstLink) {
                firstLink.focus();
            }
        }
    }
    
    focusSearch() {
        const search = document.querySelector('#search-sidebar-menu, [type="search"], .search-input');
        if (search) {
            search.focus();
        }
    }
    
    handleEscape() {
        // Close open modals
        const openModal = document.querySelector('.modal.show');
        if (openModal && window.$ && $.fn.modal) {
            $(openModal).modal('hide');
            return;
        }
        
        // Close open dropdowns
        const openDropdown = document.querySelector('.dropdown-menu.show');
        if (openDropdown) {
            openDropdown.classList.remove('show');
            return;
        }
        
        // Clear focus from search
        const searchInput = document.querySelector('#search-sidebar-menu:focus');
        if (searchInput) {
            searchInput.blur();
        }
    }
    
    addLandmark(element, role, label) {
        element.setAttribute('role', role);
        if (label) {
            element.setAttribute('aria-label', label);
        }
    }
    
    makeAccessible(element, options = {}) {
        const {
            role,
            label,
            describedBy,
            expanded,
            selected,
            required
        } = options;
        
        if (role) element.setAttribute('role', role);
        if (label) element.setAttribute('aria-label', label);
        if (describedBy) element.setAttribute('aria-describedby', describedBy);
        if (expanded !== undefined) element.setAttribute('aria-expanded', expanded);
        if (selected !== undefined) element.setAttribute('aria-selected', selected);
        if (required) element.setAttribute('aria-required', 'true');
    }
}

// Initialize global accessibility manager
window.accessibilityManager = new AccessibilityManager();

// jQuery plugin
if (window.$ && $.fn) {
    $.fn.makeAccessible = function(options = {}) {
        return this.each(function() {
            window.accessibilityManager.makeAccessible(this, options);
        });
    };
}
