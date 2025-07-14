/**
 * GroFresh Admin Auto-Save System
 *
 * Provides automatic form saving to localStorage with conflict resolution,
 * form restoration, and user-friendly feedback.
 */

class AutoSaveManager {
    constructor(options = {}) {
        this.options = {
            saveInterval: 2000, // Auto-save every 2 seconds
            storagePrefix: 'grofresh_autosave_',
            excludeFields: ['_token', 'password', 'password_confirmation'],
            showIndicator: true,
            conflictResolution: 'ask', // 'ask', 'local', 'server'
            ...options
        };

        this.forms = new Map();
        this.saveTimeouts = new Map();
        this.indicator = null;

        this.init();
    }

    init() {
        if (this.options.showIndicator) {
            this.createIndicator();
        }

        // Auto-discover forms with auto-save attribute
        document.querySelectorAll('[data-auto-save]').forEach(form => {
            this.enableAutoSave(form);
        });

        // Handle page unload
        window.addEventListener('beforeunload', () => {
            this.saveAllForms();
        });
    }

    createIndicator() {
        this.indicator = document.createElement('div');
        this.indicator.className = 'auto-save-indicator';
        this.indicator.innerHTML = '<i class="fas fa-check"></i> Saved';
        document.body.appendChild(this.indicator);
    }

    showIndicator(status = 'saved', message = null) {
        if (!this.indicator) return;

        const messages = {
            saving: '<i class="fas fa-spinner fa-spin"></i> Saving...',
            saved: '<i class="fas fa-check"></i> Saved',
            error: '<i class="fas fa-exclamation-triangle"></i> Save failed'
        };

        this.indicator.innerHTML = message || messages[status];
        this.indicator.className = `auto-save-indicator ${status} show`;

        if (status === 'saved') {
            setTimeout(() => {
                this.indicator.classList.remove('show');
            }, 2000);
        }
    }

    enableAutoSave(form) {
        const formId = form.id || `form_${Date.now()}`;
        if (!form.id) form.id = formId;

        const config = {
            form: form,
            storageKey: this.options.storagePrefix + formId,
            lastSaved: null,
            isDirty: false
        };

        this.forms.set(formId, config);

        // Restore saved data
        this.restoreFormData(formId);

        // Set up event listeners
        this.setupFormListeners(formId);

        return formId;
    }

    setupFormListeners(formId) {
        const config = this.forms.get(formId);
        const form = config.form;

        // Listen for form changes
        form.addEventListener('input', (e) => {
            if (this.shouldSaveField(e.target)) {
                this.markDirty(formId);
                this.scheduleAutoSave(formId);
            }
        });

        form.addEventListener('change', (e) => {
            if (this.shouldSaveField(e.target)) {
                this.markDirty(formId);
                this.scheduleAutoSave(formId);
            }
        });

        // Handle form submission
        form.addEventListener('submit', () => {
            this.clearAutoSave(formId);
        });
    }

    shouldSaveField(field) {
        if (!field.name) return false;
        if (this.options.excludeFields.includes(field.name)) return false;
        if (field.type === 'file') return false;
        if (field.hasAttribute('data-no-autosave')) return false;

        return true;
    }

    markDirty(formId) {
        const config = this.forms.get(formId);
        if (config) {
            config.isDirty = true;
        }
    }

    scheduleAutoSave(formId) {
        // Clear existing timeout
        if (this.saveTimeouts.has(formId)) {
            clearTimeout(this.saveTimeouts.get(formId));
        }

        // Schedule new save
        const timeout = setTimeout(() => {
            this.saveForm(formId);
        }, this.options.saveInterval);

        this.saveTimeouts.set(formId, timeout);
    }

    saveForm(formId) {
        const config = this.forms.get(formId);
        if (!config || !config.isDirty) return;

        try {
            this.showIndicator('saving');

            const formData = this.serializeForm(config.form);
            const saveData = {
                data: formData,
                timestamp: Date.now(),
                url: window.location.href,
                formId: formId
            };

            localStorage.setItem(config.storageKey, JSON.stringify(saveData));

            config.lastSaved = Date.now();
            config.isDirty = false;

            this.showIndicator('saved');

            // Dispatch custom event
            config.form.dispatchEvent(new CustomEvent('autosaved', {
                detail: { formId, data: formData }
            }));

        } catch (error) {
            console.error('Auto-save failed:', error);
            this.showIndicator('error', 'Save failed');
        }
    }

    saveAllForms() {
        this.forms.forEach((config, formId) => {
            if (config.isDirty) {
                this.saveForm(formId);
            }
        });
    }

    serializeForm(form) {
        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            if (this.shouldSaveField({ name: key, type: 'text' })) {
                if (data[key]) {
                    // Handle multiple values (checkboxes, etc.)
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
        }

        return data;
    }

    restoreFormData(formId) {
        const config = this.forms.get(formId);
        const savedData = localStorage.getItem(config.storageKey);

        if (!savedData) return;

        try {
            const { data, timestamp, url } = JSON.parse(savedData);

            // Check if we should restore (same URL, recent save)
            const isRecentSave = Date.now() - timestamp < 24 * 60 * 60 * 1000; // 24 hours
            const isSameUrl = url === window.location.href;

            if (isRecentSave && isSameUrl) {
                if (this.options.conflictResolution === 'ask') {
                    this.showRestorePrompt(formId, data, timestamp);
                } else if (this.options.conflictResolution === 'local') {
                    this.restoreData(formId, data);
                }
            }

        } catch (error) {
            console.error('Failed to restore form data:', error);
        }
    }

    showRestorePrompt(formId, data, timestamp) {
        const config = this.forms.get(formId);
        const timeAgo = this.formatTimeAgo(timestamp);

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-history text-warning"></i>
                            Restore Previous Work?
                        </h5>
                    </div>
                    <div class="modal-body">
                        <p>We found unsaved changes from <strong>${timeAgo}</strong>.</p>
                        <p>Would you like to restore your previous work?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-action="discard">
                            Start Fresh
                        </button>
                        <button type="button" class="btn btn-primary" data-action="restore">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Handle button clicks
        modal.addEventListener('click', (e) => {
            if (e.target.dataset.action === 'restore') {
                this.restoreData(formId, data);
                this.showIndicator('saved', 'Previous work restored');
            } else if (e.target.dataset.action === 'discard') {
                this.clearAutoSave(formId);
            }

            // Remove modal
            modal.remove();
        });

        // Show modal (assuming Bootstrap is available)
        if (window.$ && $.fn.modal) {
            $(modal).modal('show');
        }
    }

    restoreData(formId, data) {
        const config = this.forms.get(formId);
        const form = config.form;

        Object.entries(data).forEach(([name, value]) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = Array.isArray(value) ? value.includes(field.value) : value === field.value;
                } else if (field.tagName === 'SELECT') {
                    if (field.multiple) {
                        Array.from(field.options).forEach(option => {
                            option.selected = Array.isArray(value) ? value.includes(option.value) : value === option.value;
                        });
                    } else {
                        field.value = value;
                    }
                } else {
                    field.value = value;
                }

                // Trigger change event for any listeners
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    clearAutoSave(formId) {
        const config = this.forms.get(formId);
        if (config) {
            localStorage.removeItem(config.storageKey);
            config.isDirty = false;
        }

        if (this.saveTimeouts.has(formId)) {
            clearTimeout(this.saveTimeouts.get(formId));
            this.saveTimeouts.delete(formId);
        }
    }

    formatTimeAgo(timestamp) {
        const now = Date.now();
        const diff = now - timestamp;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
        if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        return 'just now';
    }

    // Public API
    enable(formSelector) {
        const form = document.querySelector(formSelector);
        if (form) {
            return this.enableAutoSave(form);
        }
    }

    disable(formId) {
        this.clearAutoSave(formId);
        this.forms.delete(formId);
    }

    save(formId) {
        this.saveForm(formId);
    }

    clear(formId) {
        this.clearAutoSave(formId);
    }
}

// Initialize global auto-save manager
window.autoSaveManager = new AutoSaveManager();

// jQuery plugin for easy integration
if (window.$ && $.fn) {
    $.fn.autoSave = function(options = {}) {
        return this.each(function() {
            this.setAttribute('data-auto-save', 'true');
            window.autoSaveManager.enableAutoSave(this);
        });
    };
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutoSaveManager;
}
