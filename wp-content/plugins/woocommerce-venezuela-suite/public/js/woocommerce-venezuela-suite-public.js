/**
 * WooCommerce Venezuela Suite - Public JavaScript
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize frontend functionality
     */
    $(document).ready(function() {
        initExchangeRateDisplay();
        initPriceConversion();
    });

    /**
     * Initialize exchange rate display
     */
    function initExchangeRateDisplay() {
        if (typeof wvs_ajax !== 'undefined' && wvs_ajax.current_rate > 0) {
            updateExchangeRateDisplay();
            
            // Update every 5 minutes
            setInterval(updateExchangeRateDisplay, 300000);
        }
    }

    /**
     * Update exchange rate display
     */
    function updateExchangeRateDisplay() {
        $.ajax({
            url: wvs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wvs_get_exchange_rate',
                nonce: wvs_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.rate > 0) {
                    $('.wvs-rate-display').each(function() {
                        var $this = $(this);
                        var text = $this.text();
                        var newText = text.replace(/\d+[.,]\d+/, response.data.formatted_rate);
                        $this.text(newText);
                    });
                }
            },
            error: function() {
                console.log('Error updating exchange rate');
            }
        });
    }

    /**
     * Initialize price conversion functionality
     */
    function initPriceConversion() {
        // Add hover effect to show exact conversion
        $('.wvs-ves-price').hover(
            function() {
                var $this = $(this);
                var priceText = $this.text();
                var usdPrice = extractUSDPrice(priceText);
                
                if (usdPrice > 0 && typeof wvs_ajax !== 'undefined') {
                    var exactVES = (usdPrice * wvs_ajax.current_rate).toFixed(2);
                    var formattedVES = formatVESPrice(exactVES);
                    
                    $this.attr('title', 'Conversión exacta: ' + formattedVES);
                }
            }
        );
    }

    /**
     * Extract USD price from price text
     */
    function extractUSDPrice(priceText) {
        var match = priceText.match(/\$?(\d+[.,]\d+)/);
        return match ? parseFloat(match[1].replace(',', '.')) : 0;
    }

    /**
     * Format VES price
     */
    function formatVESPrice(amount) {
        return new Intl.NumberFormat('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount) + ' VES';
    }

    /**
     * Cart update handler
     */
    $(document.body).on('updated_cart_totals', function() {
        // Re-initialize price conversion after cart update
        initPriceConversion();
    });

    /**
     * Checkout update handler
     */
    $(document.body).on('updated_checkout', function() {
        // Re-initialize price conversion after checkout update
        initPriceConversion();
    });

})(jQuery);