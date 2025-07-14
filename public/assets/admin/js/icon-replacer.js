/**
 * GroFresh Icon Replacer
 * 
 * Automatically replaces FontAwesome and TIO icons with modern animated icons
 * throughout the admin interface for a consistent, modern look.
 */

class IconReplacer {
    constructor() {
        this.iconMappings = {
            // FontAwesome to Animated Icon mappings
            'fas fa-tachometer-alt': 'animated-icon icon-dashboard pulse',
            'fas fa-shopping-cart': 'animated-icon icon-orders bounce',
            'fas fa-box': 'animated-icon icon-products',
            'fas fa-users': 'animated-icon icon-customers',
            'fas fa-bullhorn': 'animated-icon icon-marketing shake',
            'fas fa-chart-bar': 'animated-icon icon-analytics',
            'fas fa-warehouse': 'animated-icon icon-inventory pulse',
            'fas fa-truck': 'animated-icon icon-delivery',
            'fas fa-cog': 'animated-icon icon-settings rotate',
            'fas fa-gear': 'animated-icon icon-settings rotate',
            
            // Common action icons
            'fas fa-plus': 'animated-icon icon-add pulse',
            'fas fa-edit': 'animated-icon icon-edit',
            'fas fa-trash': 'animated-icon icon-delete',
            'fas fa-save': 'animated-icon icon-save',
            'fas fa-search': 'animated-icon icon-search',
            'fas fa-filter': 'animated-icon icon-filter',
            'fas fa-download': 'animated-icon icon-export',
            'fas fa-upload': 'animated-icon icon-import',
            
            // Status icons
            'fas fa-check': 'animated-icon icon-success',
            'fas fa-check-circle': 'animated-icon icon-success',
            'fas fa-exclamation-triangle': 'animated-icon icon-warning',
            'fas fa-times': 'animated-icon icon-error',
            'fas fa-times-circle': 'animated-icon icon-error',
            'fas fa-info-circle': 'animated-icon icon-info',
            
            // Notification icons
            'fas fa-bell': 'animated-icon icon-notification',
            'fas fa-envelope': 'animated-icon icon-message',
            
            // User icons
            'fas fa-user': 'animated-icon icon-user',
            'fas fa-sign-out-alt': 'animated-icon icon-logout',
            
            // TIO icons to Animated Icon mappings
            'tio-add': 'animated-icon icon-add pulse',
            'tio-edit': 'animated-icon icon-edit',
            'tio-delete': 'animated-icon icon-delete',
            'tio-save': 'animated-icon icon-save',
            'tio-search': 'animated-icon icon-search',
            'tio-filter': 'animated-icon icon-filter',
            'tio-download': 'animated-icon icon-export',
            'tio-upload': 'animated-icon icon-import',
            'tio-checkmark': 'animated-icon icon-success',
            'tio-clear': 'animated-icon icon-error',
            'tio-info': 'animated-icon icon-info',
            'tio-warning': 'animated-icon icon-warning',
        };
        
        this.init();
    }
    
    init() {
        // Replace icons on page load
        this.replaceAllIcons();
        
        // Set up mutation observer for dynamically added content
        this.setupMutationObserver();
        
        // Replace icons in AJAX loaded content
        this.setupAjaxHandler();
    }
    
    replaceAllIcons() {
        // Replace FontAwesome icons
        document.querySelectorAll('i[class*="fas fa-"], i[class*="far fa-"], i[class*="fab fa-"]').forEach(icon => {
            this.replaceIcon(icon);
        });
        
        // Replace TIO icons
        document.querySelectorAll('i[class*="tio-"]').forEach(icon => {
            this.replaceIcon(icon);
        });
    }
    
    replaceIcon(iconElement) {
        const classes = iconElement.className;
        let newClasses = null;
        
        // Find matching mapping
        for (const [oldClass, newClass] of Object.entries(this.iconMappings)) {
            if (classes.includes(oldClass)) {
                newClasses = newClass;
                break;
            }
        }
        
        if (newClasses) {
            // Create new span element with animated icon
            const newIcon = document.createElement('span');
            newIcon.className = newClasses;
            
            // Copy any additional classes (like nav-icon, etc.)
            const additionalClasses = classes.split(' ').filter(cls => 
                !cls.startsWith('fas') && 
                !cls.startsWith('far') && 
                !cls.startsWith('fab') && 
                !cls.startsWith('tio-') &&
                !cls.startsWith('fa-')
            );
            
            if (additionalClasses.length > 0) {
                newIcon.className += ' ' + additionalClasses.join(' ');
            }
            
            // Replace the icon
            iconElement.parentNode.replaceChild(newIcon, iconElement);
        }
    }
    
    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if the added node itself is an icon
                            if (node.tagName === 'I' && this.hasIconClass(node)) {
                                this.replaceIcon(node);
                            }
                            
                            // Check for icons within the added node
                            const icons = node.querySelectorAll && node.querySelectorAll('i[class*="fas fa-"], i[class*="far fa-"], i[class*="fab fa-"], i[class*="tio-"]');
                            if (icons) {
                                icons.forEach(icon => this.replaceIcon(icon));
                            }
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    setupAjaxHandler() {
        // Hook into jQuery AJAX if available
        if (window.$ && $.ajaxSetup) {
            $(document).ajaxComplete(() => {
                setTimeout(() => {
                    this.replaceAllIcons();
                }, 100);
            });
        }
        
        // Hook into fetch API
        const originalFetch = window.fetch;
        window.fetch = (...args) => {
            return originalFetch(...args).then(response => {
                setTimeout(() => {
                    this.replaceAllIcons();
                }, 100);
                return response;
            });
        };
    }
    
    hasIconClass(element) {
        const classes = element.className;
        return classes.includes('fas fa-') || 
               classes.includes('far fa-') || 
               classes.includes('fab fa-') || 
               classes.includes('tio-');
    }
    
    // Public API for manual icon replacement
    replaceIconsInElement(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            const icons = element.querySelectorAll('i[class*="fas fa-"], i[class*="far fa-"], i[class*="fab fa-"], i[class*="tio-"]');
            icons.forEach(icon => this.replaceIcon(icon));
        }
    }
    
    // Add new icon mapping
    addMapping(oldClass, newClass) {
        this.iconMappings[oldClass] = newClass;
    }
    
    // Remove icon mapping
    removeMapping(oldClass) {
        delete this.iconMappings[oldClass];
    }
    
    // Get all current mappings
    getMappings() {
        return { ...this.iconMappings };
    }
}

// Initialize the icon replacer when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.iconReplacer = new IconReplacer();
});

// jQuery compatibility
if (window.$ && $.fn) {
    $.fn.replaceIcons = function() {
        return this.each(function() {
            if (window.iconReplacer) {
                window.iconReplacer.replaceIconsInElement(this);
            }
        });
    };
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IconReplacer;
}
