/**
 * WooCommerce Venezuela Suite 2025 - Currency Converter JavaScript
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Currency Converter Class
     */
    class WCVSCurrencyConverter {
        constructor() {
            this.currentRate = wcvs_currency.current_rate || 0;
            this.fallbackRate = wcvs_currency.fallback_rate || 0;
            this.init();
        }

        /**
         * Initialize currency converter
         */
        init() {
            this.bindEvents();
            this.updateCurrencyDisplay();
            this.startRateUpdateInterval();
        }

        /**
         * Bind events
         */
        bindEvents() {
            // Currency selector change
            $(document).on('change', '.wcvs-currency-selector', this.handleCurrencyChange.bind(this));
            
            // Price update events
            $(document).on('updated_wc_div', this.updateCurrencyDisplay.bind(this));
            $(document).on('woocommerce_cart_updated', this.updateCurrencyDisplay.bind(this));
            
            // Manual rate update
            $(document).on('click', '.wcvs-update-rate', this.updateCurrentRate.bind(this));
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
            } else {
                this.showDualPrices();
            }
        }

        /**
         * Show VES prices only
         */
        showVESPrices() {
            $('.wcvs-price-usd').hide();
            $('.wcvs-price-ves').show();
            $('.wcvs-price-dual').each(function() {
                const vesPrice = $(this).data('ves-price');
                $(this).text(vesPrice);
            });
        }

        /**
         * Show USD prices only
         */
        showUSDPrices() {
            $('.wcvs-price-ves').hide();
            $('.wcvs-price-usd').show();
            $('.wcvs-price-dual').each(function() {
                const usdPrice = $(this).data('usd-price');
                $(this).text(usdPrice);
            });
        }

        /**
         * Show dual prices
         */
        showDualPrices() {
            $('.wcvs-price-ves').show();
            $('.wcvs-price-usd').show();
            $('.wcvs-price-dual').each(function() {
                const vesPrice = $(this).data('ves-price');
                const usdPrice = $(this).data('usd-price');
                $(this).text(`${vesPrice} (${usdPrice})`);
            });
        }

        /**
         * Update currency display
         */
        updateCurrencyDisplay() {
            // Update product prices
            this.updateProductPrices();
            
            // Update cart totals
            this.updateCartTotals();
            
            // Update checkout totals
            this.updateCheckoutTotals();
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
                    const vesPrice = basePrice * WCVSCurrencyConverter.prototype.currentRate;
                    $element.find('.wcvs-price-ves').text(WCVSCurrencyConverter.prototype.formatVES(vesPrice));
                    $element.find('.wcvs-price-usd').text(WCVSCurrencyConverter.prototype.formatUSD(basePrice));
                } else if (baseCurrency === 'VES') {
                    const usdPrice = basePrice / WCVSCurrencyConverter.prototype.currentRate;
                    $element.find('.wcvs-price-ves').text(WCVSCurrencyConverter.prototype.formatVES(basePrice));
                    $element.find('.wcvs-price-usd').text(WCVSCurrencyConverter.prototype.formatUSD(usdPrice));
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
                    const vesTotal = baseTotal * WCVSCurrencyConverter.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyConverter.prototype.formatVES(vesTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyConverter.prototype.formatUSD(baseTotal));
                } else if (baseCurrency === 'VES') {
                    const usdTotal = baseTotal / WCVSCurrencyConverter.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyConverter.prototype.formatVES(baseTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyConverter.prototype.formatUSD(usdTotal));
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
                    const vesTotal = baseTotal * WCVSCurrencyConverter.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyConverter.prototype.formatVES(vesTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyConverter.prototype.formatUSD(baseTotal));
                } else if (baseCurrency === 'VES') {
                    const usdTotal = baseTotal / WCVSCurrencyConverter.prototype.currentRate;
                    $element.find('.wcvs-total-ves').text(WCVSCurrencyConverter.prototype.formatVES(baseTotal));
                    $element.find('.wcvs-total-usd').text(WCVSCurrencyConverter.prototype.formatUSD(usdTotal));
                }
            });
        }

        /**
         * Update current rate
         */
        updateCurrentRate() {
            const $button = $('.wcvs-update-rate');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Actualizando...');
            
            $.ajax({
                url: wcvs_currency.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_current_rate',
                    nonce: wcvs_currency.nonce
                },
                success: (response) => {
                    if (response.success && response.data.rate) {
                        this.currentRate = response.data.rate;
                        this.updateCurrencyDisplay();
                        this.showRateUpdateSuccess();
                    } else {
                        this.showRateUpdateError();
                    }
                },
                error: () => {
                    this.showRateUpdateError();
                },
                complete: () => {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        }

        /**
         * Show rate update success
         */
        showRateUpdateSuccess() {
            $('.wcvs-rate-status').html('<span style="color: green;">✓ Tasa actualizada: ' + this.formatVES(this.currentRate) + '/USD</span>');
        }

        /**
         * Show rate update error
         */
        showRateUpdateError() {
            $('.wcvs-rate-status').html('<span style="color: red;">✗ Error al actualizar tasa</span>');
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
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        /**
         * Format USD amount
         */
        formatUSD(amount) {
            return '$' + parseFloat(amount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
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
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        new WCVSCurrencyConverter();
    });

    /**
     * Export for global access
     */
    window.WCVSCurrencyConverter = WCVSCurrencyConverter;

})(jQuery);
