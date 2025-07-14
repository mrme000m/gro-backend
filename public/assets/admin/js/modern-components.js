/**
 * Modern UI Components Library
 * Reusable components for the admin dashboard
 */

class ModernComponents {
    constructor() {
        this.init();
    }

    init() {
        this.initDataTables();
        this.initModals();
        this.initForms();
        this.initFileUploads();
        this.initDatePickers();
        this.initSelectBoxes();
    }

    // Enhanced DataTables
    initDataTables() {
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.modern-datatable').each(function() {
                const table = $(this);
                const options = {
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search...',
                        lengthMenu: 'Show _MENU_ entries',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        infoEmpty: 'No entries found',
                        infoFiltered: '(filtered from _MAX_ total entries)',
                        paginate: {
                            first: '<i class="fas fa-angle-double-left"></i>',
                            previous: '<i class="fas fa-angle-left"></i>',
                            next: '<i class="fas fa-angle-right"></i>',
                            last: '<i class="fas fa-angle-double-right"></i>'
                        }
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                         '<"row"<"col-sm-12"tr>>' +
                         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    drawCallback: function() {
                        // Apply modern styling after each draw
                        this.api().table().container().classList.add('modern-datatable-container');
                    }
                };

                // Merge with custom options if provided
                const customOptions = table.data('options');
                if (customOptions) {
                    Object.assign(options, customOptions);
                }

                table.DataTable(options);
            });
        }
    }

    // Modern Modal System
    initModals() {
        // Create modal backdrop
        if (!document.querySelector('.modern-modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modern-modal-backdrop';
            backdrop.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 9998;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            `;
            document.body.appendChild(backdrop);
        }

        // Handle modal triggers
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-modal-target]');
            if (trigger) {
                e.preventDefault();
                const targetId = trigger.dataset.modalTarget;
                this.openModal(targetId);
            }

            const closeBtn = e.target.closest('[data-modal-close]');
            if (closeBtn) {
                e.preventDefault();
                this.closeModal();
            }
        });

        // Close modal on backdrop click
        document.querySelector('.modern-modal-backdrop')?.addEventListener('click', () => {
            this.closeModal();
        });

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        const backdrop = document.querySelector('.modern-modal-backdrop');
        
        if (modal && backdrop) {
            modal.style.display = 'block';
            backdrop.style.visibility = 'visible';
            backdrop.style.opacity = '1';
            
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal() {
        const modals = document.querySelectorAll('.modern-modal.show');
        const backdrop = document.querySelector('.modern-modal-backdrop');
        
        modals.forEach(modal => {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        });
        
        if (backdrop) {
            backdrop.style.opacity = '0';
            backdrop.style.visibility = 'hidden';
        }
        
        document.body.style.overflow = '';
    }

    // Enhanced Form Handling
    initForms() {
        // Auto-save forms
        document.querySelectorAll('.auto-save-form').forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    this.autoSaveForm(form);
                });
            });
        });

        // Form validation
        document.querySelectorAll('.validate-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });

        // Real-time validation
        document.querySelectorAll('.validate-input').forEach(input => {
            input.addEventListener('blur', () => {
                this.validateInput(input);
            });
        });
    }

    autoSaveForm(form) {
        const formData = new FormData(form);
        const saveUrl = form.dataset.autoSaveUrl;
        
        if (saveUrl) {
            fetch(saveUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showToast('Form auto-saved', 'success');
                }
            })
            .catch(error => {
                console.error('Auto-save failed:', error);
            });
        }
    }

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('.validate-input');
        
        inputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    validateInput(input) {
        const rules = input.dataset.validate?.split('|') || [];
        let isValid = true;
        let errorMessage = '';
        
        for (const rule of rules) {
            const [ruleName, ruleValue] = rule.split(':');
            
            switch (ruleName) {
                case 'required':
                    if (!input.value.trim()) {
                        isValid = false;
                        errorMessage = 'This field is required';
                    }
                    break;
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (input.value && !emailRegex.test(input.value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                case 'min':
                    if (input.value.length < parseInt(ruleValue)) {
                        isValid = false;
                        errorMessage = `Minimum ${ruleValue} characters required`;
                    }
                    break;
                case 'max':
                    if (input.value.length > parseInt(ruleValue)) {
                        isValid = false;
                        errorMessage = `Maximum ${ruleValue} characters allowed`;
                    }
                    break;
            }
            
            if (!isValid) break;
        }
        
        this.showInputValidation(input, isValid, errorMessage);
        return isValid;
    }

    showInputValidation(input, isValid, message) {
        const container = input.closest('.form-group') || input.parentElement;
        let errorElement = container.querySelector('.validation-error');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'validation-error text-danger text-sm mt-1';
            container.appendChild(errorElement);
        }
        
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    // Modern File Upload
    initFileUploads() {
        document.querySelectorAll('.modern-file-upload').forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const preview = upload.querySelector('.file-preview');
            const dropZone = upload.querySelector('.drop-zone');
            
            if (input && dropZone) {
                // Drag and drop
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.classList.add('drag-over');
                });
                
                dropZone.addEventListener('dragleave', () => {
                    dropZone.classList.remove('drag-over');
                });
                
                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('drag-over');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        input.files = files;
                        this.handleFileUpload(input, preview);
                    }
                });
                
                // File input change
                input.addEventListener('change', () => {
                    this.handleFileUpload(input, preview);
                });
            }
        });
    }

    handleFileUpload(input, preview) {
        const files = Array.from(input.files);
        
        if (preview) {
            preview.innerHTML = '';
            
            files.forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className = 'file-thumbnail';
                    fileItem.appendChild(img);
                }
                
                const fileName = document.createElement('span');
                fileName.textContent = file.name;
                fileName.className = 'file-name';
                fileItem.appendChild(fileName);
                
                preview.appendChild(fileItem);
            });
        }
    }

    // Date Pickers
    initDatePickers() {
        if (typeof flatpickr !== 'undefined') {
            document.querySelectorAll('.modern-datepicker').forEach(input => {
                flatpickr(input, {
                    theme: 'material_blue',
                    dateFormat: 'Y-m-d',
                    ...JSON.parse(input.dataset.options || '{}')
                });
            });
        }
    }

    // Select Boxes
    initSelectBoxes() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.modern-select').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }
    }

    // Toast Notifications
    showToast(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `modern-toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${this.getToastIcon(type)}"></i>
                <span>${message}</span>
                <button class="toast-close">&times;</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        // Manual close
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    }

    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }

    // Loading States
    showLoading(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.classList.add('loading');
            element.disabled = true;
            
            const originalText = element.textContent;
            element.dataset.originalText = originalText;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        }
    }

    hideLoading(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.classList.remove('loading');
            element.disabled = false;
            
            const originalText = element.dataset.originalText;
            if (originalText) {
                element.textContent = originalText;
            }
        }
    }

    // Confirmation Dialogs
    confirm(message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, proceed!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        } else {
            if (confirm(message) && callback) {
                callback();
            }
        }
    }
}

// Initialize components when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.modernComponents = new ModernComponents();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernComponents;
}
