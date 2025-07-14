/**
 * GroFresh Admin Form Validation Framework
 * 
 * Provides comprehensive client-side validation with real-time feedback,
 * accessibility support, and consistent error messaging.
 */

class FormValidator {
    constructor(options = {}) {
        this.options = {
            validateOnInput: true,
            validateOnBlur: true,
            showSuccessStates: true,
            scrollToFirstError: true,
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            ...options
        };
        
        this.validators = new Map();
        this.forms = new Map();
        
        this.initDefaultValidators();
        this.init();
    }
    
    init() {
        // Auto-discover forms with validation
        document.querySelectorAll('[data-validate]').forEach(form => {
            this.enableValidation(form);
        });
    }
    
    initDefaultValidators() {
        // Required field validator
        this.addValidator('required', {
            validate: (value, field) => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    return field.checked;
                }
                return value && value.trim().length > 0;
            },
            message: 'This field is required'
        });
        
        // Email validator
        this.addValidator('email', {
            validate: (value) => {
                if (!value) return true; // Allow empty unless required
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(value);
            },
            message: 'Please enter a valid email address'
        });
        
        // Phone validator
        this.addValidator('phone', {
            validate: (value) => {
                if (!value) return true;
                const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                return phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''));
            },
            message: 'Please enter a valid phone number'
        });
        
        // URL validator
        this.addValidator('url', {
            validate: (value) => {
                if (!value) return true;
                try {
                    new URL(value);
                    return true;
                } catch {
                    return false;
                }
            },
            message: 'Please enter a valid URL'
        });
        
        // Number validators
        this.addValidator('min', {
            validate: (value, field, params) => {
                if (!value) return true;
                const num = parseFloat(value);
                return !isNaN(num) && num >= parseFloat(params);
            },
            message: (params) => `Value must be at least ${params}`
        });
        
        this.addValidator('max', {
            validate: (value, field, params) => {
                if (!value) return true;
                const num = parseFloat(value);
                return !isNaN(num) && num <= parseFloat(params);
            },
            message: (params) => `Value must be no more than ${params}`
        });
        
        // Length validators
        this.addValidator('minlength', {
            validate: (value, field, params) => {
                if (!value) return true;
                return value.length >= parseInt(params);
            },
            message: (params) => `Must be at least ${params} characters`
        });
        
        this.addValidator('maxlength', {
            validate: (value, field, params) => {
                if (!value) return true;
                return value.length <= parseInt(params);
            },
            message: (params) => `Must be no more than ${params} characters`
        });
        
        // Pattern validator
        this.addValidator('pattern', {
            validate: (value, field, params) => {
                if (!value) return true;
                const regex = new RegExp(params);
                return regex.test(value);
            },
            message: 'Please match the required format'
        });
        
        // Confirmation validator (for password confirmation)
        this.addValidator('confirm', {
            validate: (value, field, params) => {
                const targetField = field.form.querySelector(`[name="${params}"]`);
                return targetField ? value === targetField.value : false;
            },
            message: (params) => `Must match ${params}`
        });
        
        // File validators
        this.addValidator('file-size', {
            validate: (value, field, params) => {
                if (!field.files || field.files.length === 0) return true;
                const maxSize = parseInt(params) * 1024 * 1024; // Convert MB to bytes
                return Array.from(field.files).every(file => file.size <= maxSize);
            },
            message: (params) => `File size must be less than ${params}MB`
        });
        
        this.addValidator('file-type', {
            validate: (value, field, params) => {
                if (!field.files || field.files.length === 0) return true;
                const allowedTypes = params.split(',').map(type => type.trim().toLowerCase());
                return Array.from(field.files).every(file => {
                    const fileType = file.type.toLowerCase();
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                    return allowedTypes.some(type => 
                        fileType.includes(type) || fileExt === type
                    );
                });
            },
            message: (params) => `Only ${params} files are allowed`
        });
    }
    
    addValidator(name, config) {
        this.validators.set(name, config);
    }
    
    enableValidation(form) {
        const formId = form.id || `form_${Date.now()}`;
        if (!form.id) form.id = formId;
        
        const config = {
            form: form,
            fields: new Map(),
            isValid: true
        };
        
        this.forms.set(formId, config);
        
        // Find all fields with validation rules
        this.setupFormFields(formId);
        
        // Set up form submission handler
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(formId)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
        
        return formId;
    }
    
    setupFormFields(formId) {
        const config = this.forms.get(formId);
        const form = config.form;
        
        // Find fields with validation attributes
        const fields = form.querySelectorAll('[data-validate], [required], [pattern], [min], [max], [minlength], [maxlength]');
        
        fields.forEach(field => {
            const fieldConfig = {
                element: field,
                rules: this.parseValidationRules(field),
                isValid: true,
                errorElement: null
            };
            
            config.fields.set(field.name || field.id, fieldConfig);
            
            // Set up event listeners
            if (this.options.validateOnInput) {
                field.addEventListener('input', () => {
                    this.validateField(formId, field.name || field.id);
                });
            }
            
            if (this.options.validateOnBlur) {
                field.addEventListener('blur', () => {
                    this.validateField(formId, field.name || field.id);
                });
            }
            
            // Create error element
            this.createErrorElement(field);
        });
    }
    
    parseValidationRules(field) {
        const rules = [];
        
        // Parse data-validate attribute
        const validateAttr = field.getAttribute('data-validate');
        if (validateAttr) {
            validateAttr.split('|').forEach(rule => {
                const [name, params] = rule.split(':');
                rules.push({ name: name.trim(), params: params?.trim() });
            });
        }
        
        // Parse HTML5 validation attributes
        if (field.hasAttribute('required')) {
            rules.push({ name: 'required' });
        }
        
        if (field.type === 'email') {
            rules.push({ name: 'email' });
        }
        
        if (field.hasAttribute('pattern')) {
            rules.push({ name: 'pattern', params: field.getAttribute('pattern') });
        }
        
        if (field.hasAttribute('min')) {
            rules.push({ name: 'min', params: field.getAttribute('min') });
        }
        
        if (field.hasAttribute('max')) {
            rules.push({ name: 'max', params: field.getAttribute('max') });
        }
        
        if (field.hasAttribute('minlength')) {
            rules.push({ name: 'minlength', params: field.getAttribute('minlength') });
        }
        
        if (field.hasAttribute('maxlength')) {
            rules.push({ name: 'maxlength', params: field.getAttribute('maxlength') });
        }
        
        return rules;
    }
    
    createErrorElement(field) {
        const errorId = `${field.id || field.name}_error`;
        let errorElement = document.getElementById(errorId);
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.id = errorId;
            errorElement.className = 'invalid-feedback-enhanced';
            errorElement.setAttribute('role', 'alert');
            errorElement.setAttribute('aria-live', 'polite');
            
            // Insert after the field or its parent group
            const insertAfter = field.closest('.form-group, .form-group-enhanced') || field;
            insertAfter.parentNode.insertBefore(errorElement, insertAfter.nextSibling);
        }
        
        // Set ARIA attributes
        field.setAttribute('aria-describedby', errorId);
        
        return errorElement;
    }
    
    validateField(formId, fieldName) {
        const config = this.forms.get(formId);
        const fieldConfig = config.fields.get(fieldName);
        
        if (!fieldConfig) return true;
        
        const field = fieldConfig.element;
        const value = field.value;
        let isValid = true;
        let errorMessage = '';
        
        // Run all validation rules
        for (const rule of fieldConfig.rules) {
            const validator = this.validators.get(rule.name);
            if (validator) {
                const result = validator.validate(value, field, rule.params);
                if (!result) {
                    isValid = false;
                    errorMessage = typeof validator.message === 'function' 
                        ? validator.message(rule.params)
                        : validator.message;
                    break;
                }
            }
        }
        
        // Update field state
        this.updateFieldState(field, isValid, errorMessage);
        fieldConfig.isValid = isValid;
        
        // Update form validity
        this.updateFormValidity(formId);
        
        return isValid;
    }
    
    updateFieldState(field, isValid, errorMessage) {
        const errorElement = document.getElementById(`${field.id || field.name}_error`);
        
        // Remove existing classes
        field.classList.remove(this.options.errorClass, this.options.successClass);
        
        if (isValid) {
            if (this.options.showSuccessStates && field.value) {
                field.classList.add(this.options.successClass);
            }
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
        } else {
            field.classList.add(this.options.errorClass);
            if (errorElement) {
                errorElement.textContent = errorMessage;
                errorElement.style.display = 'block';
            }
        }
    }
    
    validateForm(formId) {
        const config = this.forms.get(formId);
        let isValid = true;
        let firstErrorField = null;
        
        // Validate all fields
        config.fields.forEach((fieldConfig, fieldName) => {
            const fieldValid = this.validateField(formId, fieldName);
            if (!fieldValid && !firstErrorField) {
                firstErrorField = fieldConfig.element;
            }
            isValid = isValid && fieldValid;
        });
        
        // Scroll to first error if needed
        if (!isValid && firstErrorField && this.options.scrollToFirstError) {
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstErrorField.focus();
        }
        
        return isValid;
    }
    
    updateFormValidity(formId) {
        const config = this.forms.get(formId);
        let isValid = true;
        
        config.fields.forEach(fieldConfig => {
            if (!fieldConfig.isValid) {
                isValid = false;
            }
        });
        
        config.isValid = isValid;
        
        // Dispatch custom event
        config.form.dispatchEvent(new CustomEvent('validationchange', {
            detail: { formId, isValid }
        }));
    }
    
    // Public API
    enable(formSelector) {
        const form = document.querySelector(formSelector);
        if (form) {
            return this.enableValidation(form);
        }
    }
    
    validate(formId) {
        return this.validateForm(formId);
    }
    
    isValid(formId) {
        const config = this.forms.get(formId);
        return config ? config.isValid : false;
    }
    
    reset(formId) {
        const config = this.forms.get(formId);
        if (config) {
            config.fields.forEach(fieldConfig => {
                this.updateFieldState(fieldConfig.element, true, '');
                fieldConfig.isValid = true;
            });
            this.updateFormValidity(formId);
        }
    }
}

// Initialize global form validator
window.formValidator = new FormValidator();

// jQuery plugin
if (window.$ && $.fn) {
    $.fn.validate = function(options = {}) {
        return this.each(function() {
            this.setAttribute('data-validate', 'true');
            window.formValidator.enableValidation(this);
        });
    };
}
