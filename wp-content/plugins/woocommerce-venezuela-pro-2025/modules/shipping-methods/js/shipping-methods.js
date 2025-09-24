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
    });

})(jQuery);
