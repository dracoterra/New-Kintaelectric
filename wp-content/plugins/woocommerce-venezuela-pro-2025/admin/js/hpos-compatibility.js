/**
 * WooCommerce Venezuela Suite 2025 - HPOS Compatibility JavaScript
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * HPOS Compatibility Frontend Class
     */
    class WCVSHPOSCompatibility {
        constructor() {
            this.init();
        }

        /**
         * Initialize HPOS compatibility
         */
        init() {
            this.bindEvents();
            this.initHPOSFeatures();
        }

        /**
         * Bind events
         */
        bindEvents() {
            $(document).on('woocommerce_checkout_processed', this.handleCheckoutProcessed.bind(this));
            $(document).on('woocommerce_order_status_changed', this.handleOrderStatusChanged.bind(this));
        }

        /**
         * Initialize HPOS features
         */
        initHPOSFeatures() {
            // Add HPOS-specific functionality
            this.addHPOSOrderHandling();
            this.addHPOSStatusHandling();
        }

        /**
         * Add HPOS order handling
         */
        addHPOSOrderHandling() {
            // Handle orders with HPOS
            if (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.hpos_enabled) {
                this.enableHPOSOrderHandling();
            }
        }

        /**
         * Add HPOS status handling
         */
        addHPOSStatusHandling() {
            // Handle order status changes with HPOS
            if (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.hpos_enabled) {
                this.enableHPOSStatusHandling();
            }
        }

        /**
         * Enable HPOS order handling
         */
        enableHPOSOrderHandling() {
            // Add HPOS-specific order handling
            $(document.body).on('checkout_error', this.handleHPOSCheckoutError.bind(this));
            $(document.body).on('checkout_success', this.handleHPOSCheckoutSuccess.bind(this));
        }

        /**
         * Enable HPOS status handling
         */
        enableHPOSStatusHandling() {
            // Add HPOS-specific status handling
            $(document.body).on('order_status_changed', this.handleHPOSOrderStatusChanged.bind(this));
        }

        /**
         * Handle checkout processed
         */
        handleCheckoutProcessed(event, data) {
            if (data && data.order_id) {
                this.processHPOSOrder(data.order_id);
            }
        }

        /**
         * Handle order status changed
         */
        handleOrderStatusChanged(event, orderId, oldStatus, newStatus) {
            this.processHPOSOrderStatusChange(orderId, oldStatus, newStatus);
        }

        /**
         * Handle HPOS checkout error
         */
        handleHPOSCheckoutError(event, errorMessage) {
            console.log('HPOS Checkout Error:', errorMessage);
            this.logHPOSEvent('checkout_error', { error: errorMessage });
        }

        /**
         * Handle HPOS checkout success
         */
        handleHPOSCheckoutSuccess(event, data) {
            console.log('HPOS Checkout Success:', data);
            this.logHPOSEvent('checkout_success', data);
        }

        /**
         * Handle HPOS order status changed
         */
        handleHPOSOrderStatusChanged(event, orderId, oldStatus, newStatus) {
            console.log('HPOS Order Status Changed:', { orderId, oldStatus, newStatus });
            this.logHPOSEvent('order_status_changed', { orderId, oldStatus, newStatus });
        }

        /**
         * Process HPOS order
         */
        processHPOSOrder(orderId) {
            // Process order with HPOS compatibility
            $.ajax({
                url: wcvs_hpos_compatibility.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_process_hpos_order',
                    order_id: orderId,
                    nonce: wcvs_hpos_compatibility.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('HPOS Order processed successfully:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('HPOS Order processing error:', error);
                }
            });
        }

        /**
         * Process HPOS order status change
         */
        processHPOSOrderStatusChange(orderId, oldStatus, newStatus) {
            // Process order status change with HPOS compatibility
            $.ajax({
                url: wcvs_hpos_compatibility.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_process_hpos_order_status_change',
                    order_id: orderId,
                    old_status: oldStatus,
                    new_status: newStatus,
                    nonce: wcvs_hpos_compatibility.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('HPOS Order status change processed successfully:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('HPOS Order status change processing error:', error);
                }
            });
        }

        /**
         * Log HPOS event
         */
        logHPOSEvent(eventType, data) {
            // Log HPOS-specific events
            $.ajax({
                url: wcvs_hpos_compatibility.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_log_hpos_event',
                    event_type: eventType,
                    data: data,
                    nonce: wcvs_hpos_compatibility.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('HPOS Event logged successfully');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('HPOS Event logging error:', error);
                }
            });
        }

        /**
         * Get HPOS status
         */
        getHPOSStatus() {
            return {
                enabled: typeof wc_checkout_params !== 'undefined' && wc_checkout_params.hpos_enabled,
                compatible: true,
                version: wcvs_hpos_compatibility.version
            };
        }

        /**
         * Check HPOS compatibility
         */
        checkHPOSCompatibility() {
            const status = this.getHPOSStatus();
            
            if (status.enabled && status.compatible) {
                console.log('HPOS Compatibility: Enabled and Compatible');
                return true;
            } else if (status.enabled && !status.compatible) {
                console.warn('HPOS Compatibility: Enabled but Not Compatible');
                return false;
            } else {
                console.log('HPOS Compatibility: Not Enabled');
                return true;
            }
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        new WCVSHPOSCompatibility();
    });

    /**
     * Export for global access
     */
    window.WCVSHPOSCompatibility = WCVSHPOSCompatibility;

})(jQuery);
