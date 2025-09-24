/**
 * WooCommerce Venezuela Suite 2025 - Electronic Billing Frontend JavaScript
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Electronic Billing Frontend Class
     */
    class WCVSElectronicBilling {
        constructor() {
            this.init();
        }

        /**
         * Initialize electronic billing
         */
        init() {
            this.bindEvents();
            this.initValidation();
            this.initFieldFormatting();
        }

        /**
         * Bind events
         */
        bindEvents() {
            $(document).on('blur', '#billing_rif', this.validateRIF.bind(this));
            $(document).on('blur', '#billing_cedula', this.validateCedula.bind(this));
            $(document).on('input', '#billing_rif', this.formatRIF.bind(this));
            $(document).on('input', '#billing_cedula', this.formatCedula.bind(this));
            $(document).on('change', '#billing_rif', this.handleRIFChange.bind(this));
            $(document).on('change', '#billing_cedula', this.handleCedulaChange.bind(this));
        }

        /**
         * Initialize validation
         */
        initValidation() {
            // Add custom validation rules
            if (typeof $.validator !== 'undefined') {
                $.validator.addMethod('rif', this.validateRIFFormat.bind(this), wcvs_electronic_billing.strings.invalid_rif);
                $.validator.addMethod('cedula', this.validateCedulaFormat.bind(this), wcvs_electronic_billing.strings.invalid_cedula);
            }
        }

        /**
         * Initialize field formatting
         */
        initFieldFormatting() {
            // Format RIF field
            $('#billing_rif').on('input', this.formatRIF.bind(this));
            
            // Format Cédula field
            $('#billing_cedula').on('input', this.formatCedula.bind(this));
        }

        /**
         * Validate RIF
         */
        validateRIF(event) {
            const $field = $(event.target);
            const rif = $field.val().trim();
            
            if (rif && !this.validateRIFFormat(rif)) {
                this.showFieldError($field, wcvs_electronic_billing.strings.invalid_rif);
                return false;
            } else {
                this.clearFieldError($field);
                return true;
            }
        }

        /**
         * Validate Cédula
         */
        validateCedula(event) {
            const $field = $(event.target);
            const cedula = $field.val().trim();
            
            if (cedula && !this.validateCedulaFormat(cedula)) {
                this.showFieldError($field, wcvs_electronic_billing.strings.invalid_cedula);
                return false;
            } else {
                this.clearFieldError($field);
                return true;
            }
        }

        /**
         * Format RIF input
         */
        formatRIF(event) {
            const $field = $(event.target);
            let value = $field.val().replace(/[^0-9]/g, '');
            
            if (value.length > 0) {
                // Add V- prefix if not present
                if (!value.startsWith('V')) {
                    value = 'V-' + value;
                }
                
                // Format as V-XXXXXXXX-X
                if (value.length > 2) {
                    const numbers = value.substring(2);
                    if (numbers.length > 8) {
                        value = 'V-' + numbers.substring(0, 8) + '-' + numbers.substring(8, 9);
                    } else if (numbers.length > 0) {
                        value = 'V-' + numbers;
                    }
                }
            }
            
            $field.val(value);
        }

        /**
         * Format Cédula input
         */
        formatCedula(event) {
            const $field = $(event.target);
            let value = $field.val().replace(/[^0-9]/g, '');
            
            if (value.length > 0) {
                // Add V- prefix if not present
                if (!value.startsWith('V')) {
                    value = 'V-' + value;
                }
                
                // Format as V-XXXXXXXX
                if (value.length > 2) {
                    const numbers = value.substring(2);
                    if (numbers.length > 8) {
                        value = 'V-' + numbers.substring(0, 8);
                    } else {
                        value = 'V-' + numbers;
                    }
                }
            }
            
            $field.val(value);
        }

        /**
         * Handle RIF change
         */
        handleRIFChange(event) {
            const $field = $(event.target);
            const rif = $field.val().trim();
            
            if (rif && this.validateRIFFormat(rif)) {
                // RIF is valid, could fetch company info here
                this.fetchCompanyInfo(rif);
            }
        }

        /**
         * Handle Cédula change
         */
        handleCedulaChange(event) {
            const $field = $(event.target);
            const cedula = $field.val().trim();
            
            if (cedula && this.validateCedulaFormat(cedula)) {
                // Cédula is valid, could fetch person info here
                this.fetchPersonInfo(cedula);
            }
        }

        /**
         * Validate RIF format
         */
        validateRIFFormat(rif) {
            // RIF format: V-XXXXXXXX-X (V-12345678-9)
            const rifRegex = /^V-\d{8}-\d{1}$/;
            return rifRegex.test(rif);
        }

        /**
         * Validate Cédula format
         */
        validateCedulaFormat(cedula) {
            // Cédula format: V-XXXXXXXX (V-12345678)
            const cedulaRegex = /^V-\d{8}$/;
            return cedulaRegex.test(cedula);
        }

        /**
         * Fetch company info from RIF
         */
        fetchCompanyInfo(rif) {
            // This would integrate with SENIAT API to fetch company information
            // For now, just show a loading indicator
            const $field = $('#billing_rif');
            $field.addClass('loading');
            
            setTimeout(() => {
                $field.removeClass('loading');
                // Could populate company name field here
            }, 1000);
        }

        /**
         * Fetch person info from Cédula
         */
        fetchPersonInfo(cedula) {
            // This would integrate with CNE API to fetch person information
            // For now, just show a loading indicator
            const $field = $('#billing_cedula');
            $field.addClass('loading');
            
            setTimeout(() => {
                $field.removeClass('loading');
                // Could populate person name field here
            }, 1000);
        }

        /**
         * Show field error
         */
        showFieldError($field, message) {
            this.clearFieldError($field);
            
            const $error = $('<div class="wcvs-field-error">' + message + '</div>');
            $field.after($error);
            $field.addClass('error');
        }

        /**
         * Clear field error
         */
        clearFieldError($field) {
            $field.next('.wcvs-field-error').remove();
            $field.removeClass('error');
        }

        /**
         * Show message
         */
        showMessage(message, type) {
            const $message = $('<div class="wcvs-message wcvs-message-' + type + '">' + message + '</div>');
            $('.wcvs-venezuelan-fields').prepend($message);
            
            setTimeout(() => {
                $message.fadeOut(() => {
                    $message.remove();
                });
            }, 5000);
        }

        /**
         * Validate all fields
         */
        validateAllFields() {
            let isValid = true;
            
            // Validate RIF
            const $rif = $('#billing_rif');
            if ($rif.length && $rif.val().trim()) {
                if (!this.validateRIFFormat($rif.val().trim())) {
                    this.showFieldError($rif, wcvs_electronic_billing.strings.invalid_rif);
                    isValid = false;
                }
            }
            
            // Validate Cédula
            const $cedula = $('#billing_cedula');
            if ($cedula.length && $cedula.val().trim()) {
                if (!this.validateCedulaFormat($cedula.val().trim())) {
                    this.showFieldError($cedula, wcvs_electronic_billing.strings.invalid_cedula);
                    isValid = false;
                }
            }
            
            return isValid;
        }

        /**
         * Get field value
         */
        getFieldValue(fieldName) {
            const $field = $('#' + fieldName);
            return $field.length ? $field.val().trim() : '';
        }

        /**
         * Set field value
         */
        setFieldValue(fieldName, value) {
            const $field = $('#' + fieldName);
            if ($field.length) {
                $field.val(value);
            }
        }

        /**
         * Clear all fields
         */
        clearAllFields() {
            $('#billing_rif').val('');
            $('#billing_cedula').val('');
            this.clearFieldError($('#billing_rif'));
            this.clearFieldError($('#billing_cedula'));
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        new WCVSElectronicBilling();
    });

    /**
     * Export for global access
     */
    window.WCVSElectronicBilling = WCVSElectronicBilling;

})(jQuery);
