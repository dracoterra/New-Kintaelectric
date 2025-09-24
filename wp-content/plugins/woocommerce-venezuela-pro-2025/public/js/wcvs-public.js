/**
 * WooCommerce Venezuela Suite 2025 - Public JavaScript
 *
 * Funcionalidad JavaScript para el frontend del plugin
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        WCVS_Public.init();
    });

    // Main Public object
    window.WCVS_Public = {
        
        /**
         * Initialize public functionality
         */
        init: function() {
            this.initCurrencySelector();
            this.initVenezuelanFields();
            this.initPaymentGateways();
            this.initShippingMethods();
            this.initExchangeRateDisplay();
        },

        /**
         * Initialize currency selector
         */
        initCurrencySelector: function() {
            $('.wcvs-currency-option').on('click', function() {
                var $option = $(this);
                var currency = $option.data('currency');
                
                // Update active option
                $('.wcvs-currency-option').removeClass('active');
                $option.addClass('active');
                
                // Update prices
                WCVS_Public.updatePrices(currency);
                
                // Store selection
                localStorage.setItem('wcvs_selected_currency', currency);
            });
            
            // Restore previous selection
            var selectedCurrency = localStorage.getItem('wcvs_selected_currency');
            if (selectedCurrency) {
                $('.wcvs-currency-option[data-currency="' + selectedCurrency + '"]').click();
            }
        },

        /**
         * Initialize Venezuelan fields
         */
        initVenezuelanFields: function() {
            // Venezuelan ID validation
            $('input[name="wcvs_venezuelan_id"]').on('blur', function() {
                var $input = $(this);
                var value = $input.val();
                
                if (value && !WCVS_Public.validateVenezuelanId(value)) {
                    WCVS_Public.showFieldError($input, 'La cédula de identidad no es válida');
                } else {
                    WCVS_Public.clearFieldError($input);
                }
            });
            
            // RIF validation
            $('input[name="wcvs_rif"]').on('blur', function() {
                var $input = $(this);
                var value = $input.val();
                
                if (value && !WCVS_Public.validateRif(value)) {
                    WCVS_Public.showFieldError($input, 'El RIF no es válido');
                } else {
                    WCVS_Public.clearFieldError($input);
                }
            });
            
            // Venezuelan phone validation
            $('input[name="wcvs_venezuelan_phone"]').on('blur', function() {
                var $input = $(this);
                var value = $input.val();
                
                if (value && !WCVS_Public.validateVenezuelanPhone(value)) {
                    WCVS_Public.showFieldError($input, 'El número de teléfono venezolano no es válido');
                } else {
                    WCVS_Public.clearFieldError($input);
                }
            });
            
            // State selection
            $('select[name="wcvs_state"]').on('change', function() {
                var $select = $(this);
                var stateId = $select.val();
                
                WCVS_Public.loadMunicipalities(stateId);
            });
        },

        /**
         * Initialize payment gateways
         */
        initPaymentGateways: function() {
            // Payment reference validation
            $('input[name="wcvs_payment_reference"]').on('blur', function() {
                var $input = $(this);
                var value = $input.val();
                
                if (value && !WCVS_Public.validatePaymentReference(value)) {
                    WCVS_Public.showFieldError($input, 'La referencia de pago no es válida');
                } else {
                    WCVS_Public.clearFieldError($input);
                }
            });
            
            // Copy payment details
            $('.wcvs-copy-payment-detail').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var text = $button.data('text');
                
                WCVS_Public.copyToClipboard(text);
                WCVS_Public.showNotification('Copiado al portapapeles', 'success');
            });
        },

        /**
         * Initialize shipping methods
         */
        initShippingMethods: function() {
            // Shipping method selection
            $('input[name="shipping_method"]').on('change', function() {
                var $input = $(this);
                var methodId = $input.val();
                
                WCVS_Public.updateShippingInfo(methodId);
            });
        },

        /**
         * Initialize exchange rate display
         */
        initExchangeRateDisplay: function() {
            // Update exchange rate every 5 minutes
            setInterval(function() {
                WCVS_Public.updateExchangeRate();
            }, 300000);
            
            // Initial update
            WCVS_Public.updateExchangeRate();
        },

        /**
         * Update prices based on selected currency
         */
        updatePrices: function(currency) {
            $('.wcvs-dual-price').each(function() {
                var $price = $(this);
                var originalPrice = $price.data('original-price');
                var exchangeRate = $price.data('exchange-rate');
                
                if (originalPrice && exchangeRate) {
                    var convertedPrice = currency === 'USD' ? 
                        originalPrice / exchangeRate : 
                        originalPrice * exchangeRate;
                    
                    $price.text('(' + WCVS_Public.formatPrice(convertedPrice, currency) + ')');
                }
            });
        },

        /**
         * Load municipalities for selected state
         */
        loadMunicipalities: function(stateId) {
            if (!stateId) {
                $('select[name="wcvs_municipality"]').html('<option value="">Selecciona un municipio</option>');
                return;
            }
            
            var $municipalitySelect = $('select[name="wcvs_municipality"]');
            $municipalitySelect.prop('disabled', true);
            
            $.ajax({
                url: wcvs_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_municipalities',
                    state_id: stateId,
                    nonce: wcvs_public.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Selecciona un municipio</option>';
                        $.each(response.data, function(id, name) {
                            options += '<option value="' + id + '">' + name + '</option>';
                        });
                        $municipalitySelect.html(options);
                    } else {
                        WCVS_Public.showNotification(response.data, 'error');
                    }
                },
                error: function() {
                    WCVS_Public.showNotification(wcvs_public.strings.error, 'error');
                },
                complete: function() {
                    $municipalitySelect.prop('disabled', false);
                }
            });
        },

        /**
         * Update shipping information
         */
        updateShippingInfo: function(methodId) {
            $('.wcvs-shipping-method-info').hide();
            $('.wcvs-shipping-method-info[data-method="' + methodId + '"]').show();
        },

        /**
         * Update exchange rate
         */
        updateExchangeRate: function() {
            $.ajax({
                url: wcvs_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_exchange_rate',
                    nonce: wcvs_public.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.wcvs-exchange-rate .rate-value').text(response.data.rate);
                        $('.wcvs-exchange-rate .rate-update').text('Actualizado: ' + response.data.time);
                    }
                },
                error: function() {
                    // Silently fail for exchange rate updates
                }
            });
        },

        /**
         * Validate Venezuelan ID
         */
        validateVenezuelanId: function(id) {
            // Venezuelan ID validation (V-12345678-9 format)
            return /^[VvEeJjGgPp][-]?\d{7,8}[-]?\d$/.test(id);
        },

        /**
         * Validate RIF
         */
        validateRif: function(rif) {
            // RIF validation (J-12345678-9 format)
            return /^[JjGgPpCcVvEe][-]?\d{8}[-]?\d$/.test(rif);
        },

        /**
         * Validate Venezuelan phone
         */
        validateVenezuelanPhone: function(phone) {
            // Venezuelan phone validation (+58-XXX-XXXXXXX format)
            return /^(\+58|58)?[-]?\d{3}[-]?\d{7}$/.test(phone);
        },

        /**
         * Validate payment reference
         */
        validatePaymentReference: function(reference) {
            // Payment reference validation (alphanumeric, 6-20 characters)
            return /^[A-Za-z0-9]{6,20}$/.test(reference);
        },

        /**
         * Show field error
         */
        showFieldError: function($field, message) {
            WCVS_Public.clearFieldError($field);
            
            var $error = $('<div class="wcvs-field-error">' + message + '</div>');
            $field.after($error);
            $field.addClass('wcvs-field-error');
        },

        /**
         * Clear field error
         */
        clearFieldError: function($field) {
            $field.removeClass('wcvs-field-error');
            $field.siblings('.wcvs-field-error').remove();
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            var notificationHtml = '<div class="wcvs-notification ' + type + '">' + message + '</div>';
            
            // Add to top of page
            $('body').prepend(notificationHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.wcvs-notification').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Format price
         */
        formatPrice: function(price, currency) {
            var symbol = currency === 'USD' ? '$' : 'Bs.';
            var formattedPrice = parseFloat(price).toFixed(2);
            
            if (currency === 'VES') {
                formattedPrice = formattedPrice.replace('.', ',');
            }
            
            return symbol + ' ' + formattedPrice;
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
        }
    };

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
        </style>
    `;
    
    $('head').append(fieldErrorStyles);
});
