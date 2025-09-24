/**
 * WooCommerce Venezuela Suite 2025 - Payment Gateways JavaScript
 *
 * Funcionalidad JavaScript para las pasarelas de pago venezolanas
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        WCVS_Payment_Gateways.init();
    });

    // Main Payment Gateways object
    window.WCVS_Payment_Gateways = {
        
        /**
         * Initialize payment gateways functionality
         */
        init: function() {
            this.initCopyButtons();
            this.initPaymentValidation();
            this.initPaymentFields();
        },

        /**
         * Initialize copy buttons
         */
        initCopyButtons: function() {
            $('.wcvs-copy-payment-detail').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var text = $button.data('text');
                
                WCVS_Payment_Gateways.copyToClipboard(text);
                
                // Show feedback
                var originalText = $button.text();
                $button.text('Copiado!');
                
                setTimeout(function() {
                    $button.text(originalText);
                }, 2000);
            });
        },

        /**
         * Initialize payment validation
         */
        initPaymentValidation: function() {
            // Real-time validation for payment references
            $('input[name*="reference"], input[name*="confirmation"], input[name*="transaction_id"]').on('blur', function() {
                var $input = $(this);
                var value = $input.val();
                var fieldName = $input.attr('name');
                
                if (value) {
                    WCVS_Payment_Gateways.validatePaymentField($input, fieldName, value);
                } else {
                    WCVS_Payment_Gateways.clearFieldError($input);
                }
            });

            // Enhanced real-time validation
            this.initRealTimeValidation();
        },

        /**
         * Initialize real-time validation
         */
        initRealTimeValidation: function() {
            // RIF validation
            $('.wcvs-rif-field').on('input', function() {
                var $field = $(this);
                var rif = $field.val();
                var validation = WCVS_Payment_Gateways.validateRIF(rif);
                
                WCVS_Payment_Gateways.showFieldValidation($field, validation);
            });

            // Phone validation
            $('.wcvs-phone-field').on('input', function() {
                var $field = $(this);
                var phone = $field.val();
                var validation = WCVS_Payment_Gateways.validatePhone(phone);
                
                WCVS_Payment_Gateways.showFieldValidation($field, validation);
            });

            // Payment reference validation
            $('.wcvs-reference-field').on('input', function() {
                var $field = $(this);
                var reference = $field.val();
                var validation = WCVS_Payment_Gateways.validatePaymentReference(reference);
                
                WCVS_Payment_Gateways.showFieldValidation($field, validation);
            });
        },

        /**
         * Validate RIF format
         */
        validateRIF: function(rif) {
            if (!rif) {
                return { valid: false, message: 'RIF requerido' };
            }

            // Clean RIF
            rif = rif.toUpperCase().replace(/[\s\-]/g, '');
            
            // Basic format check
            if (!/^[VJPG][0-9]{9}$/.test(rif)) {
                return { valid: false, message: 'Formato inválido. Use: V-12345678-9' };
            }

            // Extract components
            var prefix = rif.charAt(0);
            var number = rif.substring(1, 9);
            var checkDigit = rif.charAt(9);

            // Validate prefix
            if (!['V', 'J', 'P', 'G'].includes(prefix)) {
                return { valid: false, message: 'Prefijo inválido. Use V, J, P o G' };
            }

            // Calculate check digit
            var calculatedDigit = WCVS_Payment_Gateways.calculateRIFCheckDigit(number);
            if (checkDigit !== calculatedDigit) {
                return { valid: false, message: 'Dígito verificador inválido' };
            }

            return { valid: true, message: 'RIF válido' };
        },

        /**
         * Calculate RIF check digit
         */
        calculateRIFCheckDigit: function(number) {
            var multipliers = [3, 2, 7, 6, 5, 4, 3, 2];
            var sum = 0;
            
            for (var i = 0; i < 8; i++) {
                sum += parseInt(number.charAt(i)) * multipliers[i];
            }
            
            var remainder = sum % 11;
            var checkDigit = 11 - remainder;
            
            if (checkDigit >= 10) {
                checkDigit = 0;
            }
            
            return checkDigit.toString();
        },

        /**
         * Validate Venezuelan phone number
         */
        validatePhone: function(phone) {
            if (!phone) {
                return { valid: false, message: 'Teléfono requerido' };
            }

            // Clean phone number
            phone = phone.replace(/[\s\-\(\)]/g, '');
            
            // Remove country code
            if (phone.startsWith('+58')) {
                phone = phone.substring(3);
            } else if (phone.startsWith('58')) {
                phone = phone.substring(2);
            }

            // Validate Venezuelan formats
            if (/^04[0-9]{8}$/.test(phone)) {
                return { 
                    valid: true, 
                    message: 'Teléfono móvil válido',
                    formatted: '+58-' + phone.substring(0, 4) + '-' + phone.substring(4)
                };
            }

            if (/^02[0-9]{8}$/.test(phone)) {
                return { 
                    valid: true, 
                    message: 'Teléfono fijo válido',
                    formatted: '+58-' + phone.substring(0, 4) + '-' + phone.substring(4)
                };
            }

            return { valid: false, message: 'Formato inválido. Use: 04XX-XXXXXXX o 02XX-XXXXXXX' };
        },

        /**
         * Validate payment reference
         */
        validatePaymentReference: function(reference) {
            if (!reference) {
                return { valid: false, message: 'Referencia requerida' };
            }

            // Check length and format
            if (reference.length < 6 || reference.length > 20) {
                return { valid: false, message: 'Referencia debe tener entre 6 y 20 caracteres' };
            }

            if (!/^[A-Za-z0-9]+$/.test(reference)) {
                return { valid: false, message: 'Solo se permiten letras y números' };
            }

            return { valid: true, message: 'Referencia válida' };
        },

        /**
         * Show field validation feedback
         */
        showFieldValidation: function($field, validation) {
            var $container = $field.closest('.wcvs-field-container');
            var $feedback = $container.find('.wcvs-field-feedback');
            
            if ($feedback.length === 0) {
                $feedback = $('<div class="wcvs-field-feedback"></div>');
                $container.append($feedback);
            }

            $feedback.removeClass('valid invalid').addClass(validation.valid ? 'valid' : 'invalid');
            $feedback.text(validation.message);

            // Update field appearance
            $field.removeClass('error success').addClass(validation.valid ? 'success' : 'error');
        },

        /**
         * Initialize payment fields
         */
        initPaymentFields: function() {
            // Show/hide fields based on payment method selection
            $('input[name="payment_method"]').on('change', function() {
                var paymentMethod = $(this).val();
                WCVS_Payment_Gateways.togglePaymentFields(paymentMethod);
            });
            
            // Initial toggle
            var selectedMethod = $('input[name="payment_method"]:checked').val();
            if (selectedMethod) {
                WCVS_Payment_Gateways.togglePaymentFields(selectedMethod);
            }
        },

        /**
         * Toggle payment fields based on selected method
         */
        togglePaymentFields: function(paymentMethod) {
            // Hide all payment info sections
            $('.wcvs-payment-info').hide();
            
            // Show relevant payment info
            if (paymentMethod && paymentMethod.startsWith('wcvs_')) {
                $('input[name="payment_method"][value="' + paymentMethod + '"]')
                    .closest('.wc_payment_method')
                    .find('.wcvs-payment-info')
                    .show();
            }
        },

        /**
         * Validate payment field
         */
        validatePaymentField: function($input, fieldName, value) {
            var isValid = false;
            var errorMessage = '';
            
            // Validate based on field type
            if (fieldName.includes('pago_movil_reference') || 
                fieldName.includes('bank_transfer_reference') || 
                fieldName.includes('cash_deposit_reference') || 
                fieldName.includes('cashea_reference')) {
                
                // Alphanumeric, 6-20 characters
                isValid = /^[A-Za-z0-9]{6,20}$/.test(value);
                errorMessage = 'La referencia debe tener entre 6 y 20 caracteres alfanuméricos';
                
            } else if (fieldName.includes('zelle_confirmation')) {
                
                // Numeric, 6-12 digits
                isValid = /^\d{6,12}$/.test(value);
                errorMessage = 'La confirmación debe tener entre 6 y 12 dígitos';
                
            } else if (fieldName.includes('binance_transaction_id')) {
                
                // Alphanumeric, 8-32 characters
                isValid = /^[A-Za-z0-9]{8,32}$/.test(value);
                errorMessage = 'El ID de transacción debe tener entre 8 y 32 caracteres alfanuméricos';
            }
            
            if (isValid) {
                WCVS_Payment_Gateways.clearFieldError($input);
            } else {
                WCVS_Payment_Gateways.showFieldError($input, errorMessage);
            }
        },

        /**
         * Show field error
         */
        showFieldError: function($input, message) {
            WCVS_Payment_Gateways.clearFieldError($input);
            
            var $error = $('<div class="wcvs-field-error">' + message + '</div>');
            $input.after($error);
            $input.addClass('wcvs-field-error');
        },

        /**
         * Clear field error
         */
        clearFieldError: function($input) {
            $input.removeClass('wcvs-field-error');
            $input.siblings('.wcvs-field-error').remove();
        },

        /**
         * Copy to clipboard
         */
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
        },

        /**
         * Validate payment before submission
         */
        validatePaymentSubmission: function() {
            var selectedMethod = $('input[name="payment_method"]:checked').val();
            var isValid = true;
            var errorMessage = '';
            
            if (selectedMethod && selectedMethod.startsWith('wcvs_')) {
                var $paymentInfo = $('input[name="payment_method"][value="' + selectedMethod + '"]')
                    .closest('.wc_payment_method')
                    .find('.wcvs-payment-info');
                
                // Check required fields
                var $requiredInput = $paymentInfo.find('input[required]');
                $requiredInput.each(function() {
                    var $input = $(this);
                    var value = $input.val();
                    
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Por favor, completa todos los campos requeridos';
                        WCVS_Payment_Gateways.showFieldError($input, 'Este campo es requerido');
                    }
                });
            }
            
            if (!isValid) {
                wc_add_notice(errorMessage, 'error');
            }
            
            return isValid;
        }
    };

    // Hook into WooCommerce checkout process
    $(document.body).on('checkout_error', function() {
        // Clear any existing errors
        $('.wcvs-field-error').removeClass('wcvs-field-error');
        $('.wcvs-field-error').remove();
    });

    // Validate before form submission
    $(document.body).on('checkout_place_order', function() {
        return WCVS_Payment_Gateways.validatePaymentSubmission();
    });

})(jQuery);

// Add field error styles dynamically
jQuery(document).ready(function($) {
    var fieldErrorStyles = `
        <style>
        .wcvs-field-error {
            border-color: #d63384 !important;
            box-shadow: 0 0 0 1px #d63384 !important;
        }
        
        .wcvs-field-error + .wcvs-field-error {
            color: #d63384;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .wcvs-copy-payment-detail {
            background: #2271b1;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .wcvs-copy-payment-detail:hover {
            background: #135e96;
        }
        
        .wcvs-payment-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .wcvs-payment-info h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #1d2327;
            font-size: 18px;
        }
        
        .wcvs-payment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .wcvs-payment-detail {
            background: #fff;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        
        .wcvs-payment-detail strong {
            display: block;
            color: #1d2327;
            margin-bottom: 5px;
        }
        
        .wcvs-payment-detail span {
            color: #666;
            font-family: monospace;
            font-size: 14px;
        }
        
        .wcvs-payment-reference {
            margin-top: 15px;
        }
        
        .wcvs-payment-reference label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #1d2327;
        }
        
        .wcvs-payment-reference input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .wcvs-field-description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .wcvs-payment-note {
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
        }
        
        .wcvs-payment-note p {
            margin: 0;
            color: #856404;
        }
        
        .wcvs-payment-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .wcvs-payment-error p {
            margin: 0;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .wcvs-payment-details {
                grid-template-columns: 1fr;
            }
        }
        </style>
    `;
    
    $('head').append(fieldErrorStyles);
});
