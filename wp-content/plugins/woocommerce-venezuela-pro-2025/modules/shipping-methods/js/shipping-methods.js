/**
 * WooCommerce Venezuela Suite 2025 - Shipping Methods JavaScript
 *
 * Funcionalidad JavaScript para los métodos de envío venezolanos
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        WCVS_Shipping_Methods.init();
    });

    // Main Shipping Methods object
    window.WCVS_Shipping_Methods = {
        
        /**
         * Initialize shipping methods functionality
         */
        init: function() {
            this.initShippingCalculation();
            this.initShippingInfo();
            this.initDeliveryZones();
            this.initAddressValidation();
            this.initDeliveryEstimator();
            this.initShippingBreakdown();
        },

        /**
         * Initialize shipping calculation
         */
        initShippingCalculation: function() {
            // Calculate shipping when address changes
            $('input[name="shipping_city"], input[name="shipping_state"], input[name="shipping_postcode"]').on('change', function() {
                WCVS_Shipping_Methods.calculateShipping();
            });

            // Calculate shipping when cart updates
            $(document.body).on('updated_cart_totals', function() {
                WCVS_Shipping_Methods.calculateShipping();
            });
        },

        /**
         * Initialize shipping info display
         */
        initShippingInfo: function() {
            // Show shipping info when method is selected
            $('input[name="shipping_method"]').on('change', function() {
                WCVS_Shipping_Methods.showShippingInfo($(this).val());
            });

            // Initial display
            var selectedMethod = $('input[name="shipping_method"]:checked').val();
            if (selectedMethod) {
                WCVS_Shipping_Methods.showShippingInfo(selectedMethod);
            }
        },

        /**
         * Initialize delivery zones
         */
        initDeliveryZones: function() {
            // Check if address is in delivery zone
            $('input[name="shipping_city"]').on('blur', function() {
                var city = $(this).val();
                var state = $('input[name="shipping_state"]').val();
                
                if (city && state) {
                    WCVS_Shipping_Methods.checkDeliveryZone(city, state);
                }
            });
        },

        /**
         * Calculate shipping cost
         */
        calculateShipping: function() {
            var destination = {
                city: $('input[name="shipping_city"]').val(),
                state: $('input[name="shipping_state"]').val(),
                postcode: $('input[name="shipping_postcode"]').val()
            };

            if (!destination.city || !destination.state) {
                return;
            }

            // Get package weight and dimensions
            var weight = WCVS_Shipping_Methods.getPackageWeight();
            var dimensions = WCVS_Shipping_Methods.getPackageDimensions();

            // Calculate for each shipping method
            WCVS_Shipping_Methods.calculateMethodCost('wcvs_mrw', destination, weight, dimensions);
            WCVS_Shipping_Methods.calculateMethodCost('wcvs_zoom', destination, weight, dimensions);
            WCVS_Shipping_Methods.calculateMethodCost('wcvs_tealca', destination, weight, dimensions);
            WCVS_Shipping_Methods.calculateMethodCost('wcvs_local_delivery', destination, weight, dimensions);
            WCVS_Shipping_Methods.calculateMethodCost('wcvs_pickup', destination, weight, dimensions);
        },

        /**
         * Calculate cost for specific method
         */
        calculateMethodCost: function(method, destination, weight, dimensions) {
            $.ajax({
                url: wcvs_shipping_methods.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_calculate_shipping',
                    shipping_method: method,
                    destination: destination.city + ', ' + destination.state,
                    weight: weight,
                    length: dimensions.length,
                    width: dimensions.width,
                    height: dimensions.height,
                    nonce: wcvs_shipping_methods.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WCVS_Shipping_Methods.updateShippingCost(method, response.data.cost, response.data.message);
                    } else {
                        WCVS_Shipping_Methods.hideShippingMethod(method);
                    }
                },
                error: function() {
                    WCVS_Shipping_Methods.hideShippingMethod(method);
                }
            });
        },

        /**
         * Update shipping cost display
         */
        updateShippingCost: function(method, cost, message) {
            var $method = $('input[name="shipping_method"][value="' + method + '"]');
            var $label = $method.closest('.wc_payment_method').find('label');
            
            // Update cost in label
            var costText = cost > 0 ? 'Bs. ' + WCVS_Shipping_Methods.formatNumber(cost) : 'Gratis';
            $label.find('.amount').text(costText);
            
            // Show method
            $method.closest('.wc_payment_method').show();
        },

        /**
         * Hide shipping method
         */
        hideShippingMethod: function(method) {
            var $method = $('input[name="shipping_method"][value="' + method + '"]');
            $method.closest('.wc_payment_method').hide();
        },

        /**
         * Show shipping info
         */
        showShippingInfo: function(method) {
            // Hide all shipping info
            $('.wcvs-shipping-info').hide();
            
            // Show relevant info
            if (method && method.startsWith('wcvs_')) {
                $('input[name="shipping_method"][value="' + method + '"]')
                    .closest('.wc_payment_method')
                    .find('.wcvs-shipping-info')
                    .show();
            }
        },

        /**
         * Check delivery zone
         */
        checkDeliveryZone: function(city, state) {
            var destination = city + ', ' + state;
            var isInZone = false;
            
            // Check against known delivery zones
            var deliveryZones = [
                'caracas', 'miranda', 'vargas', 'aragua', 'carabobo', 'valencia'
            ];
            
            var destinationLower = destination.toLowerCase();
            deliveryZones.forEach(function(zone) {
                if (destinationLower.includes(zone)) {
                    isInZone = true;
                }
            });
            
            if (isInZone) {
                WCVS_Shipping_Methods.showDeliveryZoneMessage('Zona de delivery disponible', 'success');
            } else {
                WCVS_Shipping_Methods.showDeliveryZoneMessage('Zona de delivery no disponible', 'warning');
            }
        },

        /**
         * Show delivery zone message
         */
        showDeliveryZoneMessage: function(message, type) {
            var $message = $('<div class="wcvs-delivery-zone-message ' + type + '">' + message + '</div>');
            
            // Remove existing message
            $('.wcvs-delivery-zone-message').remove();
            
            // Add new message
            $('input[name="shipping_city"]').after($message);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Get package weight
         */
        getPackageWeight: function() {
            var weight = 0;
            
            $('.cart_item').each(function() {
                var $item = $(this);
                var itemWeight = parseFloat($item.data('weight')) || 0;
                var quantity = parseInt($item.find('.qty').val()) || 1;
                weight += itemWeight * quantity;
            });
            
            return weight;
        },

        /**
         * Get package dimensions
         */
        getPackageDimensions: function() {
            var dimensions = {
                length: 0,
                width: 0,
                height: 0
            };
            
            $('.cart_item').each(function() {
                var $item = $(this);
                var itemDimensions = $item.data('dimensions');
                
                if (itemDimensions) {
                    dimensions.length = Math.max(dimensions.length, itemDimensions.length);
                    dimensions.width = Math.max(dimensions.width, itemDimensions.width);
                    dimensions.height += itemDimensions.height;
                }
            });
            
            return dimensions;
        },

        /**
         * Format number with Venezuelan formatting
         */
        formatNumber: function(number) {
            return number.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    };

    // Add shipping info styles dynamically
    jQuery(document).ready(function($) {
        var shippingStyles = `
            <style>
            .wcvs-shipping-info {
                background: #f0f8ff;
                border: 1px solid #b3d9ff;
                border-radius: 4px;
                padding: 15px;
                margin: 10px 0;
                display: none;
            }
            
            .wcvs-shipping-info h4 {
                margin-top: 0;
                margin-bottom: 10px;
                color: #0073aa;
            }
            
            .wcvs-shipping-info .wcvs-shipping-details {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
            }
            
            .wcvs-shipping-info .wcvs-shipping-detail {
                background: #fff;
                padding: 10px;
                border-radius: 3px;
                border: 1px solid #ddd;
                text-align: center;
            }
            
            .wcvs-shipping-info .wcvs-shipping-detail strong {
                display: block;
                color: #1d2327;
                margin-bottom: 5px;
            }
            
            .wcvs-shipping-info .wcvs-shipping-detail span {
                color: #666;
                font-size: 14px;
            }
            
            .wcvs-delivery-zone-message {
                padding: 10px;
                margin: 10px 0;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .wcvs-delivery-zone-message.success {
                background: #d1ecf1;
                border: 1px solid #00a32a;
                color: #0c5460;
            }
            
            .wcvs-delivery-zone-message.warning {
                background: #fff3cd;
                border: 1px solid #dba617;
                color: #856404;
            }
            
            .wcvs-shipping-calculator {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 4px;
                padding: 15px;
                margin: 15px 0;
            }
            
            .wcvs-shipping-calculator h4 {
                margin-top: 0;
                margin-bottom: 15px;
                color: #1d2327;
            }
            
            .wcvs-shipping-calculator .wcvs-calculate-button {
                background: #2271b1;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            }
            
            .wcvs-shipping-calculator .wcvs-calculate-button:hover {
                background: #135e96;
            }
            
            .wcvs-shipping-calculator .wcvs-calculate-button:disabled {
                background: #ccc;
                cursor: not-allowed;
            }
            
            @media (max-width: 768px) {
                .wcvs-shipping-info .wcvs-shipping-details {
                    grid-template-columns: 1fr;
                }
            }
            </style>
        `;
        
        $('head').append(shippingStyles);
    },

    /**
     * Initialize address validation
     */
    initAddressValidation: function() {
        // Validate Venezuelan addresses
        $('input[name="shipping_city"], input[name="shipping_state"]').on('blur', function() {
            var $field = $(this);
            var value = $field.val();
            var fieldName = $field.attr('name');
            
            if (value) {
                WCVS_Shipping_Methods.validateVenezuelanAddress($field, fieldName, value);
            } else {
                WCVS_Shipping_Methods.clearFieldError($field);
            }
        });
    },

    /**
     * Validate Venezuelan address
     */
    validateVenezuelanAddress: function($field, fieldName, value) {
        var isValid = true;
        var message = '';
        
        if (fieldName === 'shipping_state') {
            // Validate Venezuelan states
            var venezuelanStates = [
                'Distrito Capital', 'Miranda', 'Vargas', 'Aragua', 'Carabobo',
                'Lara', 'Zulia', 'Bolívar', 'Amazonas', 'Delta Amacuro',
                'Apure', 'Barinas', 'Cojedes', 'Falcón', 'Guárico',
                'Mérida', 'Monagas', 'Nueva Esparta', 'Portuguesa',
                'San Cristóbal', 'Sucre', 'Táchira', 'Trujillo', 'Yaracuy'
            ];
            
            if (!venezuelanStates.includes(value)) {
                isValid = false;
                message = 'Estado venezolano inválido';
            }
        } else if (fieldName === 'shipping_city') {
            // Basic city validation
            if (value.length < 2) {
                isValid = false;
                message = 'Nombre de ciudad muy corto';
            }
        }
        
        WCVS_Shipping_Methods.showFieldValidation($field, isValid, message);
    },

    /**
     * Initialize delivery estimator
     */
    initDeliveryEstimator: function() {
        // Show estimated delivery when shipping method is selected
        $('input[name="shipping_method"]').on('change', function() {
            var method = $(this).val();
            var state = $('input[name="shipping_state"]').val();
            
            if (method && state) {
                WCVS_Shipping_Methods.showDeliveryEstimate(method, state);
            }
        });
    },

    /**
     * Show delivery estimate
     */
    showDeliveryEstimate: function(method, state) {
        var estimates = {
            'wcvs_shipping_mrw': WCVS_Shipping_Methods.getMRWEstimate(state),
            'wcvs_shipping_zoom': WCVS_Shipping_Methods.getZoomEstimate(state),
            'wcvs_shipping_tealca': WCVS_Shipping_Methods.getTealcaEstimate(state),
            'wcvs_shipping_local_delivery': WCVS_Shipping_Methods.getLocalEstimate(state),
            'wcvs_shipping_pickup': WCVS_Shipping_Methods.getPickupEstimate(state)
        };
        
        var estimate = estimates[method];
        if (estimate) {
            WCVS_Shipping_Methods.displayDeliveryEstimate(estimate);
        }
    },

    /**
     * Get MRW delivery estimate
     */
    getMRWEstimate: function(state) {
        var estimates = {
            'Distrito Capital': 1,
            'Miranda': 1,
            'Vargas': 2,
            'Aragua': 2,
            'Carabobo': 3,
            'Lara': 3,
            'Zulia': 4,
            'Bolívar': 5,
            'Amazonas': 7,
            'Delta Amacuro': 6,
            'Apure': 5,
            'Barinas': 4,
            'Cojedes': 3,
            'Falcón': 4,
            'Guárico': 3,
            'Mérida': 5,
            'Monagas': 4,
            'Nueva Esparta': 3,
            'Portuguesa': 4,
            'San Cristóbal': 5,
            'Sucre': 4,
            'Táchira': 5,
            'Trujillo': 4,
            'Yaracuy': 3
        };
        
        var days = estimates[state] || 5;
        return {
            method: 'MRW',
            days: days,
            message: 'Entrega estimada: ' + days + ' día' + (days > 1 ? 's' : '') + ' hábiles'
        };
    },

    /**
     * Get Zoom delivery estimate
     */
    getZoomEstimate: function(state) {
        var estimates = {
            'Distrito Capital': 1,
            'Miranda': 1,
            'Vargas': 2,
            'Aragua': 2,
            'Carabobo': 3,
            'Lara': 3,
            'Zulia': 4,
            'Bolívar': 5,
            'Amazonas': 7,
            'Delta Amacuro': 6,
            'Apure': 5,
            'Barinas': 4,
            'Cojedes': 3,
            'Falcón': 4,
            'Guárico': 3,
            'Mérida': 5,
            'Monagas': 4,
            'Nueva Esparta': 3,
            'Portuguesa': 4,
            'San Cristóbal': 5,
            'Sucre': 4,
            'Táchira': 5,
            'Trujillo': 4,
            'Yaracuy': 3
        };
        
        var days = estimates[state] || 5;
        return {
            method: 'Zoom',
            days: days,
            message: 'Entrega estimada: ' + days + ' día' + (days > 1 ? 's' : '') + ' hábiles'
        };
    },

    /**
     * Get Tealca delivery estimate
     */
    getTealcaEstimate: function(state) {
        var estimates = {
            'Distrito Capital': 1,
            'Miranda': 1,
            'Vargas': 2,
            'Aragua': 2,
            'Carabobo': 3,
            'Lara': 3,
            'Zulia': 4,
            'Bolívar': 5,
            'Amazonas': 7,
            'Delta Amacuro': 6,
            'Apure': 5,
            'Barinas': 4,
            'Cojedes': 3,
            'Falcón': 4,
            'Guárico': 3,
            'Mérida': 5,
            'Monagas': 4,
            'Nueva Esparta': 3,
            'Portuguesa': 4,
            'San Cristóbal': 5,
            'Sucre': 4,
            'Táchira': 5,
            'Trujillo': 4,
            'Yaracuy': 3
        };
        
        var days = estimates[state] || 5;
        return {
            method: 'Tealca',
            days: days,
            message: 'Entrega estimada: ' + days + ' día' + (days > 1 ? 's' : '') + ' hábiles'
        };
    },

    /**
     * Get local delivery estimate
     */
    getLocalEstimate: function(state) {
        return {
            method: 'Entrega Local',
            days: 1,
            message: 'Entrega el mismo día (solo Caracas y área metropolitana)'
        };
    },

    /**
     * Get pickup estimate
     */
    getPickupEstimate: function(state) {
        return {
            method: 'Recogida',
            days: 0,
            message: 'Recogida inmediata en nuestro local'
        };
    },

    /**
     * Display delivery estimate
     */
    displayDeliveryEstimate: function(estimate) {
        var $estimateDiv = $('.wcvs-delivery-estimate');
        
        if ($estimateDiv.length === 0) {
            $estimateDiv = $('<div class="wcvs-delivery-estimate"></div>');
            $('.wcvs-shipping-methods').append($estimateDiv);
        }
        
        $estimateDiv.html(`
            <div class="wcvs-estimate-info">
                <h4>📅 ${estimate.method}</h4>
                <p>${estimate.message}</p>
            </div>
        `);
    },

    /**
     * Initialize shipping breakdown
     */
    initShippingBreakdown: function() {
        // Show cost breakdown when shipping method is selected
        $('input[name="shipping_method"]').on('change', function() {
            var method = $(this).val();
            if (method) {
                WCVS_Shipping_Methods.showShippingBreakdown(method);
            }
        });
    },

    /**
     * Show shipping breakdown
     */
    showShippingBreakdown: function(method) {
        var $breakdownDiv = $('.wcvs-shipping-breakdown');
        
        if ($breakdownDiv.length === 0) {
            $breakdownDiv = $('<div class="wcvs-shipping-breakdown"></div>');
            $('.wcvs-shipping-methods').append($breakdownDiv);
        }
        
        // This would be populated with actual breakdown data from the server
        $breakdownDiv.html(`
            <div class="wcvs-breakdown-info">
                <h4>💰 Desglose de Costos</h4>
                <div class="wcvs-breakdown-items">
                    <div class="wcvs-breakdown-item">
                        <span>Costo Base:</span>
                        <span>Bs. 15,000</span>
                    </div>
                    <div class="wcvs-breakdown-item">
                        <span>Por Peso:</span>
                        <span>Bs. 5,000</span>
                    </div>
                    <div class="wcvs-breakdown-item">
                        <span>Por Destino:</span>
                        <span>Bs. 3,000</span>
                    </div>
                    <div class="wcvs-breakdown-item total">
                        <span>Total:</span>
                        <span>Bs. 23,000</span>
                    </div>
                </div>
            </div>
        `);
    },

    /**
     * Show field validation feedback
     */
    showFieldValidation: function($field, isValid, message) {
        var $container = $field.closest('.form-row');
        var $feedback = $container.find('.wcvs-field-feedback');
        
        if ($feedback.length === 0) {
            $feedback = $('<div class="wcvs-field-feedback"></div>');
            $container.append($feedback);
        }

        $feedback.removeClass('valid invalid').addClass(isValid ? 'valid' : 'invalid');
        $feedback.text(message);

        // Update field appearance
        $field.removeClass('error success').addClass(isValid ? 'success' : 'error');
    },

    /**
     * Clear field error
     */
    clearFieldError: function($field) {
        var $container = $field.closest('.form-row');
        var $feedback = $container.find('.wcvs-field-feedback');
        
        $feedback.remove();
        $field.removeClass('error success');
    }
    };

    // Add CSS styles for shipping methods
    $(document).ready(function() {
        var shippingStyles = `
            <style>
            .wcvs-delivery-estimate {
                background: #e7f3ff;
                border: 1px solid #b3d9ff;
                border-radius: 6px;
                padding: 15px;
                margin: 10px 0;
            }
            
            .wcvs-estimate-info h4 {
                color: #0056b3;
                margin: 0 0 10px 0;
                font-size: 16px;
            }
            
            .wcvs-estimate-info p {
                margin: 0;
                color: #333;
                font-weight: 500;
            }
            
            .wcvs-shipping-breakdown {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                padding: 15px;
                margin: 10px 0;
            }
            
            .wcvs-breakdown-info h4 {
                color: #495057;
                margin: 0 0 15px 0;
                font-size: 16px;
            }
            
            .wcvs-breakdown-items {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .wcvs-breakdown-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 5px 0;
                border-bottom: 1px solid #e9ecef;
            }
            
            .wcvs-breakdown-item:last-child {
                border-bottom: none;
            }
            
            .wcvs-breakdown-item.total {
                font-weight: bold;
                color: #007cba;
                border-top: 2px solid #007cba;
                margin-top: 10px;
                padding-top: 10px;
            }
            
            .wcvs-field-feedback {
                margin-top: 5px;
                font-size: 12px;
                padding: 5px 8px;
                border-radius: 4px;
                transition: all 0.3s ease;
            }
            
            .wcvs-field-feedback.valid {
                color: #155724;
                background: rgba(40, 167, 69, 0.1);
                border: 1px solid rgba(40, 167, 69, 0.2);
            }
            
            .wcvs-field-feedback.invalid {
                color: #dc3545;
                background: rgba(220, 53, 69, 0.1);
                border: 1px solid rgba(220, 53, 69, 0.2);
            }
            
            .form-row input.success {
                border-color: #28a745;
                background: rgba(40, 167, 69, 0.05);
            }
            
            .form-row input.error {
                border-color: #dc3545;
                background: rgba(220, 53, 69, 0.05);
            }
            </style>
        `;
        
        $('head').append(shippingStyles);
    });

})(jQuery);
