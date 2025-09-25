/**
 * Improved Currency Converter JavaScript
 * 
 * Based on analysis of the existing plugin, this provides
 * a more robust and efficient currency conversion system.
 *
 * @package Woocommerce_Venezuela_Pro_2025
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    var wvp_currency_data = wvp_currency_improved; // Localized script data

    // Function to update prices on the page
    function updatePrices(newCurrency) {
        // Store preference in cookie
        document.cookie = 'wvp_display_currency=' + newCurrency + '; path=/; max-age=' + (30 * 24 * 60 * 60);

        // Update single product price
        if ($('.single-product div.product').length) {
            var productId = $('.single-product div.product').data('product_id');
            if (productId) {
                $.ajax({
                    url: wvp_currency_data.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wvp_convert_price',
                        nonce: wvp_currency_data.nonce,
                        product_id: productId,
                        currency: newCurrency
                    },
                    beforeSend: function() {
                        $('.price .woocommerce-Price-amount').html(wvp_currency_data.strings.loading);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.price .woocommerce-Price-amount').html(response.data.price);
                        } else {
                            $('.price .woocommerce-Price-amount').html(wvp_currency_data.strings.error);
                        }
                    },
                    error: function() {
                        $('.price .woocommerce-Price-amount').html(wvp_currency_data.strings.error);
                    }
                });
            }
        }

        // Update prices in shop loop (if applicable)
        $('.wvp-currency-select-loop').each(function() {
            var $select = $(this);
            var productId = $select.data('product-id');
            if (productId) {
                $.ajax({
                    url: wvp_currency_data.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wvp_convert_price',
                        nonce: wvp_currency_data.nonce,
                        product_id: productId,
                        currency: newCurrency
                    },
                    success: function(response) {
                        if (response.success) {
                            $select.closest('.product').find('.price .woocommerce-Price-amount').html(response.data.price);
                        }
                    }
                });
            }
        });
    }

    // Event listener for currency switcher on single product page
    $('#wvp-currency-select').on('change', function() {
        var newCurrency = $(this).val();
        updatePrices(newCurrency);
    });

    // Event listener for currency switcher in shop loop
    $(document).on('change', '.wvp-currency-select-loop', function() {
        var newCurrency = $(this).val();
        updatePrices(newCurrency);
    });

    // On page load, apply stored currency preference
    var storedCurrency = getCookie('wvp_display_currency');
    if (storedCurrency) {
        $('#wvp-currency-select').val(storedCurrency).trigger('change');
        $('.wvp-currency-select-loop').val(storedCurrency).trigger('change');
    }

    // Helper function to get cookie value
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }
});
