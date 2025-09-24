/**
 * WooCommerce Venezuela Suite 2025 - Currency Manager Frontend JavaScript
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Currency Manager Frontend Class
     */
    class WCVSCurrencyManager {
        constructor() {
            this.init();
        }

        /**
         * Initialize currency manager
         */
        init() {
            this.bindEvents();
            this.loadExchangeRate();
            this.initCurrencySwitcher();
        }

        /**
         * Bind events
         */
        bindEvents() {
            $(document).on('change', 'input[name="wcvs_preferred_currency"]', this.handleCurrencyChange.bind(this));
            $(document).on('click', '.wcvs-refresh-exchange-rate', this.refreshExchangeRate.bind(this));
            $(document).on('click', '.wcvs-copy-exchange-rate', this.copyExchangeRate.bind(this));
        }

        /**
         * Handle currency change
         */
        handleCurrencyChange(event) {
            const selectedCurrency = $(event.target).val();
            this.updateCurrencyDisplay(selectedCurrency);
            this.updateCartPrices(selectedCurrency);
        }

        /**
         * Update currency display
         */
        updateCurrencyDisplay(currency) {
            $('.wcvs-currency-display').removeClass('ves usd').addClass(currency.toLowerCase());
            
            // Update currency symbol
            if (currency === 'VES') {
                $('.wcvs-currency-symbol').text('Bs.');
            } else if (currency === 'USD') {
                $('.wcvs-currency-symbol').text('$');
            }
        }

        /**
         * Update cart prices
         */
        updateCartPrices(currency) {
            const exchangeRate = this.getExchangeRate();
            if (!exchangeRate) return;

            $('.wcvs-price').each(function() {
                const $price = $(this);
                const basePrice = parseFloat($price.data('base-price'));
                const baseCurrency = $price.data('base-currency');
                
                if (baseCurrency === currency) {
                    $price.text($price.data('original-price'));
                } else {
                    const convertedPrice = WCVSCurrencyManager.convertPrice(basePrice, baseCurrency, currency, exchangeRate);
                    const formattedPrice = WCVSCurrencyManager.formatPrice(convertedPrice, currency);
                    $price.text(formattedPrice);
                }
            });
        }

        /**
         * Load exchange rate
         */
        loadExchangeRate() {
            $.ajax({
                url: wcvs_currency_manager.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_exchange_rate',
                    nonce: wcvs_currency_manager.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayExchangeRate(response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading exchange rate:', error);
                }
            });
        }

        /**
         * Display exchange rate
         */
        displayExchangeRate(data) {
            $('.wcvs-exchange-rate .rate-value').text(this.formatPrice(data.rate, 'VES'));
            $('.wcvs-exchange-rate .rate-update').text('Actualizado: ' + data.updated);
            $('.wcvs-exchange-rate .rate-source').text('Fuente: ' + data.source);
        }

        /**
         * Refresh exchange rate
         */
        refreshExchangeRate(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            $button.text(wcvs_currency_manager.strings.loading).prop('disabled', true);
            
            $.ajax({
                url: wcvs_currency_manager.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_exchange_rate',
                    nonce: wcvs_currency_manager.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayExchangeRate(response.data);
                        this.showMessage(wcvs_currency_manager.strings.success, 'success');
                    } else {
                        this.showMessage(wcvs_currency_manager.strings.error, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage(wcvs_currency_manager.strings.error, 'error');
                },
                complete: () => {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        }

        /**
         * Copy exchange rate
         */
        copyExchangeRate(event) {
            event.preventDefault();
            
            const rate = $('.wcvs-exchange-rate .rate-value').text();
            navigator.clipboard.writeText(rate).then(() => {
                this.showMessage('Tipo de cambio copiado al portapapeles', 'success');
            }).catch(() => {
                this.showMessage('Error al copiar tipo de cambio', 'error');
            });
        }

        /**
         * Initialize currency switcher
         */
        initCurrencySwitcher() {
            // Add currency switcher to product pages
            if ($('.single-product').length) {
                this.addCurrencySwitcherToProduct();
            }
            
            // Add currency switcher to cart
            if ($('.woocommerce-cart').length) {
                this.addCurrencySwitcherToCart();
            }
            
            // Add currency switcher to checkout
            if ($('.woocommerce-checkout').length) {
                this.addCurrencySwitcherToCheckout();
            }
        }

        /**
         * Add currency switcher to product page
         */
        addCurrencySwitcherToProduct() {
            const $price = $('.woocommerce-Price-amount');
            if ($price.length) {
                $price.after('<div class="wcvs-currency-switcher-product"></div>');
            }
        }

        /**
         * Add currency switcher to cart
         */
        addCurrencySwitcherToCart() {
            const $cartTotals = $('.cart_totals');
            if ($cartTotals.length) {
                $cartTotals.prepend('<div class="wcvs-currency-switcher-cart"></div>');
            }
        }

        /**
         * Add currency switcher to checkout
         */
        addCurrencySwitcherToCheckout() {
            const $checkoutForm = $('.woocommerce-checkout');
            if ($checkoutForm.length) {
                $checkoutForm.prepend('<div class="wcvs-currency-switcher-checkout"></div>');
            }
        }

        /**
         * Show message
         */
        showMessage(message, type) {
            const $message = $('<div class="wcvs-message wcvs-message-' + type + '">' + message + '</div>');
            $('body').append($message);
            
            setTimeout(() => {
                $message.fadeOut(() => {
                    $message.remove();
                });
            }, 3000);
        }

        /**
         * Get exchange rate
         */
        getExchangeRate() {
            return parseFloat($('.wcvs-exchange-rate .rate-value').text().replace(/[^\d.,]/g, '').replace(',', '.'));
        }

        /**
         * Convert price
         */
        static convertPrice(amount, fromCurrency, toCurrency, exchangeRate) {
            if (fromCurrency === toCurrency) {
                return amount;
            }
            
            if (fromCurrency === 'USD' && toCurrency === 'VES') {
                return amount * exchangeRate;
            } else if (fromCurrency === 'VES' && toCurrency === 'USD') {
                return amount / exchangeRate;
            }
            
            return amount;
        }

        /**
         * Format price
         */
        static formatPrice(amount, currency) {
            if (currency === 'VES') {
                return 'Bs. ' + amount.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            } else if (currency === 'USD') {
                return '$' + amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            
            return amount;
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        new WCVSCurrencyManager();
    });

})(jQuery);
