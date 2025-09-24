/**
 * WooCommerce Venezuela Suite 2025 - Currency Manager JavaScript
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Currency Manager Class
     */
    class WCVSCurrencyManager {
        constructor() {
            this.currentRate = wcvs_currency_manager.current_rate || 0;
            this.baseCurrency = wcvs_currency_manager.base_currency || 'VES';
            this.dualPricing = wcvs_currency_manager.dual_pricing || false;
            this.pricePosition = wcvs_currency_manager.price_position || 'before';
            this.decimalPlaces = wcvs_currency_manager.decimal_places || 2;
            this.thousandSeparator = wcvs_currency_manager.thousand_separator || '.';
            this.decimalSeparator = wcvs_currency_manager.decimal_separator || ',';
            
            this.init();
        }

    /**
     * Initialize currency manager
     */
    init() {
        this.bindEvents();
        this.updateCurrencyDisplay();
        this.startRateUpdateInterval();
        this.initializeLoadingStates();
    }

        /**
         * Bind events
         */
        bindEvents() {
            // Currency selector change
            $(document).on('change', '.wcvs-currency-selector select', this.handleCurrencyChange.bind(this));
            
            // Update rate button
            $(document).on('click', '.wcvs-update-rate', this.updateCurrentRate.bind(this));
            
            // WooCommerce events
            $(document).on('updated_wc_div', this.updateCurrencyDisplay.bind(this));
            $(document).on('woocommerce_cart_updated', this.updateCurrencyDisplay.bind(this));
            $(document).on('woocommerce_checkout_updated', this.updateCurrencyDisplay.bind(this));
            
            // Product price updates
            $(document).on('woocommerce_variation_has_changed', this.updateProductPrices.bind(this));
        }

        /**
         * Handle currency change
         */
        handleCurrencyChange(event) {
            const selectedCurrency = $(event.target).val();
            this.switchCurrency(selectedCurrency);
        }

        /**
         * Switch currency display
         */
        switchCurrency(currency) {
            if (currency === 'VES') {
                this.showVESPrices();
            } else if (currency === 'USD') {
                this.showUSDPrices();
            } else if (currency === 'dual') {
                this.showDualPrices();
            }
        }

        /**
         * Show VES prices only
         */
        showVESPrices() {
            $('.wcvs-price-usd').hide();
            $('.wcvs-price-ves').show();
            $('.wcvs-price-display').each(function() {
                const $display = $(this);
                const vesPrice = $display.data('ves-price');
                if (vesPrice) {
                    $display.find('.wcvs-current-price').text(vesPrice);
                }
            });
        }

        /**
         * Show USD prices only
         */
        showUSDPrices() {
            $('.wcvs-price-ves').hide();
            $('.wcvs-price-usd').show();
            $('.wcvs-price-display').each(function() {
                const $display = $(this);
                const usdPrice = $display.data('usd-price');
                if (usdPrice) {
                    $display.find('.wcvs-current-price').text(usdPrice);
                }
            });
        }

        /**
         * Show dual prices
         */
        showDualPrices() {
            $('.wcvs-price-ves').show();
            $('.wcvs-price-usd').show();
            $('.wcvs-price-display').each(function() {
                const $display = $(this);
                const vesPrice = $display.data('ves-price');
                const usdPrice = $display.data('usd-price');
                if (vesPrice && usdPrice) {
                    $display.find('.wcvs-current-price').text(`${vesPrice} (${usdPrice})`);
                }
            });
        }

        /**
         * Update currency display with debouncing
         */
        updateCurrencyDisplay() {
            // Clear existing timeout
            if (this.updateTimeout) {
                clearTimeout(this.updateTimeout);
            }
            
            // Debounce the update to avoid excessive calls
            this.updateTimeout = setTimeout(() => {
                this.updateProductPrices();
                this.updateCartTotals();
                this.updateCheckoutTotals();
            }, 300); // 300ms debounce
        }

        /**
         * Update product prices
         */
        updateProductPrices() {
            $('.wcvs-product-price').each(function() {
                const $element = $(this);
                const basePrice = parseFloat($element.data('base-price'));
                const baseCurrency = $element.data('base-currency');
                
                if (baseCurrency === 'USD') {
                    const vesPrice = basePrice * WCVSCurrencyManager.prototype.currentRate;
                    $element.find('.wcvs-price-ves').text(WCVSCurrencyManager.prototype.formatVES(vesPrice));
                    $element.find('.wcvs-price-usd').text(WCVSCurrencyManager.prototype.formatUSD(basePrice));
                } else if (baseCurrency === 'VES') {
                    const usdPrice = basePrice / WCVSCurrencyManager.prototype.currentRate;
                    $element.find('.wcvs-price-ves').text(WCVSCurrencyManager.prototype.formatVES(basePrice));
                    $element.find('.wcvs-price-usd').text(WCVSCurrencyManager.prototype.formatUSD(usdPrice));
                }
            });
        }

        /**
         * Update cart totals
         */
        updateCartTotals() {
            $('.wcvs-cart-total').each(function() {
                const $element = $(this);
                const baseTotal = parseFloat($element.data('base-total'));
                const baseCurrency = $element.data('base-currency');
                
                if (baseCurrency === 'USD') {
                    const vesTotal = baseTotal * WCVSCurrencyManager.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyManager.prototype.formatVES(vesTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyManager.prototype.formatUSD(baseTotal));
                } else if (baseCurrency === 'VES') {
                    const usdTotal = baseTotal / WCVSCurrencyManager.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyManager.prototype.formatVES(baseTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyManager.prototype.formatUSD(usdTotal));
                }
            });
        }

        /**
         * Update checkout totals
         */
        updateCheckoutTotals() {
            $('.wcvs-checkout-total').each(function() {
                const $element = $(this);
                const baseTotal = parseFloat($element.data('base-total'));
                const baseCurrency = $element.data('base-currency');
                
                if (baseCurrency === 'USD') {
                    const vesTotal = baseTotal * WCVSCurrencyManager.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyManager.prototype.formatVES(vesTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyManager.prototype.formatUSD(baseTotal));
                } else if (baseCurrency === 'VES') {
                    const usdTotal = baseTotal / WCVSCurrencyManager.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyManager.prototype.formatVES(baseTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyManager.prototype.formatUSD(usdTotal));
                }
            });
        }

        /**
         * Update current rate with improved error handling
         */
        updateCurrentRate() {
            const $button = $('.wcvs-update-rate');
            const originalText = $button.text();
            
            // Show loading state
            this.showLoadingState($button, 'Actualizando...');
            
            $.ajax({
                url: wcvs_currency_manager.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_update_currency_display',
                    nonce: wcvs_currency_manager.nonce
                },
                timeout: 10000, // 10 second timeout
                success: (response) => {
                    if (response.success && response.data.rate) {
                        this.currentRate = response.data.rate;
                        this.updateCurrencyDisplay();
                        this.showRateUpdateSuccess(response.data);
                    } else {
                        this.showRateUpdateError(response.data?.message || 'Error desconocido');
                    }
                },
                error: (xhr, status, error) => {
                    let errorMessage = 'Error de conexión';
                    if (status === 'timeout') {
                        errorMessage = 'Tiempo de espera agotado';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Error de permisos';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Error del servidor';
                    }
                    this.showRateUpdateError(errorMessage);
                },
                complete: () => {
                    this.hideLoadingState($button, originalText);
                }
            });
        }

        /**
         * Show rate update success
         */
        showRateUpdateSuccess(data) {
            const message = data?.message || `Tasa actualizada: ${this.formatVES(this.currentRate)}/USD`;
            $('.wcvs-rate-status').html(`<span class="wcvs-success-message">✓ ${message}</span>`);
            
            // Auto-hide success message after 5 seconds
            setTimeout(() => {
                $('.wcvs-rate-status').fadeOut();
            }, 5000);
        }

        /**
         * Show rate update error
         */
        showRateUpdateError(message) {
            $('.wcvs-rate-status').html(`<span class="wcvs-error-message">✗ ${message}</span>`);
            
            // Auto-hide error message after 10 seconds
            setTimeout(() => {
                $('.wcvs-rate-status').fadeOut();
            }, 10000);
        }

        /**
         * Show loading state
         */
        showLoadingState($element, text) {
            $element.prop('disabled', true)
                   .addClass('wcvs-loading')
                   .data('original-text', $element.text())
                   .html(`<span class="wcvs-spinner"></span> ${text}`);
        }

        /**
         * Hide loading state
         */
        hideLoadingState($element, originalText) {
            $element.prop('disabled', false)
                   .removeClass('wcvs-loading')
                   .text(originalText);
        }

        /**
         * Initialize loading states
         */
        initializeLoadingStates() {
            // Add loading states to currency selector
            $('.wcvs-currency-selector select').on('change', (e) => {
                const $select = $(e.target);
                this.showLoadingState($select, 'Cambiando...');
                
                setTimeout(() => {
                    this.hideLoadingState($select, $select.val());
                }, 1000);
            });
        }

        /**
         * Start rate update interval
         */
        startRateUpdateInterval() {
            // Update rate every 30 minutes
            setInterval(() => {
                this.updateCurrentRate();
            }, 30 * 60 * 1000);
        }

        /**
         * Format VES amount
         */
        formatVES(amount) {
            return 'Bs. ' + parseFloat(amount).toLocaleString('es-VE', {
                minimumFractionDigits: this.decimalPlaces,
                maximumFractionDigits: this.decimalPlaces
            });
        }

        /**
         * Format USD amount
         */
        formatUSD(amount) {
            return '$' + parseFloat(amount).toLocaleString('en-US', {
                minimumFractionDigits: this.decimalPlaces,
                maximumFractionDigits: this.decimalPlaces
            });
        }

        /**
         * Convert USD to VES
         */
        convertUSDtoVES(usdAmount) {
            return usdAmount * this.currentRate;
        }

        /**
         * Convert VES to USD
         */
        convertVEStoUSD(vesAmount) {
            return vesAmount / this.currentRate;
        }

        /**
         * Get current rate
         */
        getCurrentRate() {
            return this.currentRate;
        }

        /**
         * Set current rate
         */
        setCurrentRate(rate) {
            this.currentRate = rate;
            this.updateCurrencyDisplay();
        }

        /**
         * Add currency selector to page
         */
        addCurrencySelector() {
            if ($('.wcvs-currency-selector').length === 0) {
                const selectorHtml = `
                    <div class="wcvs-currency-selector">
                        <label for="wcvs-currency-select">Moneda:</label>
                        <select id="wcvs-currency-select" name="wcvs-currency">
                            <option value="VES">VES - Bolívar</option>
                            <option value="USD">USD - Dólar</option>
                            <option value="dual">Ambas</option>
                        </select>
                    </div>
                `;
                
                $('.woocommerce-cart-form').prepend(selectorHtml);
                $('.woocommerce-checkout-form').prepend(selectorHtml);
            }
        }

        /**
         * Add rate display to page
         */
        addRateDisplay() {
            if ($('.wcvs-current-rate').length === 0) {
                const rateHtml = `
                    <div class="wcvs-current-rate">
                        <span class="wcvs-rate-label">Tasa actual:</span>
                        <span class="wcvs-rate-value">${this.formatVES(this.currentRate)}/USD</span>
                        <button class="wcvs-update-rate" type="button">Actualizar</button>
                        <div class="wcvs-rate-status"></div>
                    </div>
                `;
                
                $('.woocommerce-cart-form').prepend(rateHtml);
                $('.woocommerce-checkout-form').prepend(rateHtml);
            }
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        new WCVSCurrencyManager();
    });

    /**
     * Export for global access
     */
    window.WCVSCurrencyManager = WCVSCurrencyManager;

})(jQuery);