jQuery(document).ready(function($) {
    'use strict';
    
    // Check if wvp_cart_converter_ajax is available
    if (typeof wvp_cart_converter_ajax === 'undefined') {
        console.log('WVP Cart Currency Converter: AJAX object not available');
        return;
    }
    
    console.log('WVP Cart Currency Converter: JavaScript loaded successfully');
    
    // Add VES conversions to cart items
    function addVesConversionsToCart() {
        console.log('WVP Cart Currency Converter: Adding VES conversions to cart');
        
        // Only target cart-specific tables
        var cartTableSelectors = [
            '.woocommerce-cart-form table tbody tr',
            '.shop_table tbody tr',
            '.cart tbody tr'
        ];
        
        cartTableSelectors.forEach(function(selector) {
            $(selector).each(function() {
                var $row = $(this);
                
                // Skip header row
                if ($row.find('th').length > 0) {
                    return;
                }
                
                // Only target price-related cells
                var $priceCells = $row.find('td').filter(function() {
                    var $cell = $(this);
                    var text = $cell.text();
                    var hasPrice = text.includes('$') && text.match(/\$([0-9,]+\.?[0-9]*)/);
                    var isPriceCell = $cell.hasClass('product-price') || 
                                    $cell.hasClass('product-subtotal') ||
                                    text.match(/^\$[0-9,]+\.?[0-9]*$/);
                    return hasPrice && isPriceCell;
                });
                
                $priceCells.each(function() {
                    var $priceElement = $(this);
                    
                    // Skip if already has VES conversion
                    if ($priceElement.find('.wvp-ves-conversion').length > 0) {
                        return;
                    }
                    
                    // Extract USD price from text
                    var priceText = $priceElement.text();
                    var priceMatch = priceText.match(/\$([0-9,]+\.?[0-9]*)/);
                    
                    if (priceMatch) {
                        var usdPrice = parseFloat(priceMatch[1].replace(',', ''));
                        var vesPrice = usdPrice * wvp_cart_converter_ajax.rate;
                        var vesFormatted = vesPrice.toLocaleString('es-VE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        
                        // Add VES conversion to price
                        $priceElement.append('<br><small class="wvp-ves-conversion" style="color: #27ae60;">(' + vesFormatted + ' VES)</small>');
                        console.log('WVP Cart Currency Converter: Added VES conversion to price:', usdPrice, '->', vesFormatted);
                    }
                });
            });
        });
    }
    
    // Add VES conversions to cart totals
    function addVesConversionsToTotals() {
        console.log('WVP Cart Currency Converter: Adding VES conversions to cart totals');
        
        // Only target specific cart/checkout elements
        var cartSelectors = [
            '.cart_totals td',
            '.shop_table td',
            '.woocommerce-cart-form td',
            '.cart-subtotal td',
            '.cart-total td',
            '.order-total td'
        ];
        
        cartSelectors.forEach(function(selector) {
            $(selector).each(function() {
                var $element = $(this);
                
                // Skip if already has VES conversion
                if ($element.find('.wvp-ves-conversion').length > 0) {
                    return;
                }
                
                var text = $element.text();
                var match = text.match(/\$([0-9,]+\.?[0-9]*)/);
                
                if (match) {
                    var usdTotal = parseFloat(match[1].replace(',', ''));
                    var vesTotal = usdTotal * wvp_cart_converter_ajax.rate;
                    var vesFormatted = vesTotal.toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                    $element.append('<br><small class="wvp-ves-conversion" style="color: #27ae60;">(' + vesFormatted + ' VES)</small>');
                    console.log('WVP Cart Currency Converter: Added VES conversion to total:', usdTotal, '->', vesFormatted);
                }
            });
        });
    }
    
    // Run on cart page
    if ($('body').hasClass('woocommerce-cart')) {
        console.log('WVP Cart Currency Converter: On cart page, adding conversions');
        
        // Wait for cart to load completely
        setTimeout(function() {
            addVesConversionsToCart();
            addVesConversionsToTotals();
        }, 500);
        
        // Re-run when cart updates
        $(document.body).on('updated_cart_totals', function() {
            console.log('WVP Cart Currency Converter: Cart updated, re-adding conversions');
            setTimeout(function() {
                addVesConversionsToCart();
                addVesConversionsToTotals();
            }, 100);
        });
    }
    
    // Run on checkout page
    if ($('body').hasClass('woocommerce-checkout')) {
        console.log('WVP Cart Currency Converter: On checkout page, adding conversions');
        
        // Wait for checkout to load completely
        setTimeout(function() {
            addVesConversionsToCart();
            addVesConversionsToTotals();
        }, 500);
        
        // Re-run when checkout updates (e.g., shipping method change)
        $(document.body).on('updated_checkout', function() {
            console.log('WVP Cart Currency Converter: Checkout updated, re-adding conversions');
            setTimeout(function() {
                addVesConversionsToCart();
                addVesConversionsToTotals();
            }, 100);
        });
    }
});
