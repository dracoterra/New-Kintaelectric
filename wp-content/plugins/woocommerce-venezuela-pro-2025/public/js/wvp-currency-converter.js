/**
 * WooCommerce Venezuela Pro 2025 - Currency Converter
 * Frontend JavaScript for currency conversion
 */

(function($) {
    'use strict';

    var WVP_Currency_Converter = {
        
        init: function() {
            this.bindEvents();
            this.initializeCurrencySwitchers();
        },

        bindEvents: function() {
            // Currency switcher on product pages
            $(document).on('change', '#wvp-currency-select', this.handleProductCurrencyChange);
            
            // Currency switcher on shop loop
            $(document).on('change', '.wvp-currency-select-loop', this.handleLoopCurrencyChange);
            
            // Update prices on page load
            $(document).ready(this.updateAllPrices);
        },

        initializeCurrencySwitchers: function() {
            // Set initial state based on stored preference
            var preferredCurrency = localStorage.getItem('wvp_preferred_currency') || 'USD';
            $('#wvp-currency-select').val(preferredCurrency);
            $('.wvp-currency-select-loop').val(preferredCurrency);
        },

        handleProductCurrencyChange: function() {
            var currency = $(this).val();
            localStorage.setItem('wvp_preferred_currency', currency);
            
            // Update product price
            WVP_Currency_Converter.updateProductPrice(currency);
        },

        handleLoopCurrencyChange: function() {
            var currency = $(this).val();
            var productId = $(this).data('product-id');
            
            WVP_Currency_Converter.updateLoopPrice(productId, currency);
        },

        updateProductPrice: function(currency) {
            var $priceElement = $('.price .amount, .woocommerce-Price-amount');
            var originalPrice = $priceElement.text();
            
            if (currency === 'VES') {
                // Convert USD to VES
                WVP_Currency_Converter.convertPrice(0, currency, function(response) {
                    if (response.success) {
                        WVP_Currency_Converter.updatePriceDisplay($priceElement, response.data.price);
                    }
                });
            } else {
                // Show original USD price
                WVP_Currency_Converter.updatePriceDisplay($priceElement, originalPrice);
            }
        },

        updateLoopPrice: function(productId, currency) {
            var $priceElement = $('.wvp-currency-select-loop[data-product-id="' + productId + '"]').closest('.product').find('.price .amount');
            
            WVP_Currency_Converter.convertPrice(productId, currency, function(response) {
                if (response.success) {
                    WVP_Currency_Converter.updatePriceDisplay($priceElement, response.data.price);
                }
            });
        },

        updateAllPrices: function() {
            var currency = localStorage.getItem('wvp_preferred_currency') || 'USD';
            
            if (currency === 'VES') {
                // Update all visible prices to VES
                $('.price .amount').each(function() {
                    var $this = $(this);
                    var originalPrice = $this.text();
                    var usdAmount = WVP_Currency_Converter.extractPrice(originalPrice);
                    
                    if (usdAmount > 0) {
                        WVP_Currency_Converter.convertPrice(0, currency, function(response) {
                            if (response.success) {
                                WVP_Currency_Converter.updatePriceDisplay($this, response.data.price);
                            }
                        });
                    }
                });
            }
        },

        convertPrice: function(productId, currency, callback) {
            $.ajax({
                url: wvp_currency.ajax_url,
                type: 'POST',
                data: {
                    action: 'wvp_convert_price',
                    product_id: productId,
                    currency: currency,
                    nonce: wvp_currency.nonce
                },
                beforeSend: function() {
                    // Show loading indicator if needed
                },
                success: function(response) {
                    if (callback) {
                        callback(response);
                    }
                },
                error: function() {
                    console.error(wvp_currency.strings.error);
                }
            });
        },

        updatePriceDisplay: function($element, newPrice) {
            $element.html(newPrice);
        },

        extractPrice: function(priceText) {
            // Extract numeric value from price text
            var match = priceText.match(/[\d,]+\.?\d*/);
            if (match) {
                return parseFloat(match[0].replace(/,/g, ''));
            }
            return 0;
        },

        formatVESPrice: function(amount) {
            return new Intl.NumberFormat('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount) + ' VES';
        },

        formatUSDPrice: function(amount) {
            return '$' + amount.toFixed(2) + ' USD';
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WVP_Currency_Converter.init();
    });

    // Expose to global scope for debugging
    window.WVP_Currency_Converter = WVP_Currency_Converter;

})(jQuery);
