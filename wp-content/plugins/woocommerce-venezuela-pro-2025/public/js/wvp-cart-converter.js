jQuery(document).ready(function($) {
    'use strict';
    
    // Check if wvp_cart_converter_ajax is available
    if (typeof wvp_cart_converter_ajax === 'undefined') {
        console.log('WVP Cart Currency Converter: AJAX object not available');
        return;
    }
    
    console.log('WVP Cart Currency Converter: JavaScript loaded successfully');
    
    // Flag to prevent multiple executions
    var conversionsApplied = false;
    
    // Add VES conversions to cart items
    function addVesConversionsToCart() {
        console.log('WVP Cart Currency Converter: Adding VES conversions to cart');
        
        // Remove existing conversions first to avoid duplicates
        $('.wvp-ves-conversion').remove();
        
        var conversionsAdded = 0;
        
        // Find all table rows that contain prices
        $('table tbody tr').each(function() {
            var $row = $(this);
            
            // Skip header row
            if ($row.find('th').length > 0) {
                return;
            }
            
            // Find cells that contain prices - be more specific
            $row.find('td').each(function() {
                var $cell = $(this);
                var text = $cell.text().trim();
                
                // Look for price patterns like $20.00 but avoid cells that already have VES conversions
                var priceMatch = text.match(/\$([0-9,]+\.?[0-9]*)/);
                
                if (priceMatch && !text.includes('VES') && !$cell.find('.wvp-ves-conversion').length) {
                    var usdPrice = parseFloat(priceMatch[1].replace(',', ''));
                    var vesPrice = usdPrice * wvp_cart_converter_ajax.rate;
                    var vesFormatted = vesPrice.toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                    // Add VES conversion to price
                    $cell.append('<br><small class="wvp-ves-conversion" style="color: #27ae60;">(' + vesFormatted + ' VES)</small>');
                    conversionsAdded++;
                    console.log('WVP Cart Currency Converter: Added VES conversion to price:', usdPrice, '->', vesFormatted);
                }
            });
        });
        
        return conversionsAdded;
    }
    
    // Add VES conversions to cart totals
    function addVesConversionsToTotals() {
        console.log('WVP Cart Currency Converter: Adding VES conversions to cart totals');
        
        var conversionsAdded = 0;
        
        // Find the Cart totals section
        var cartTotalsHeading = $('h2').filter(function() {
            return $(this).text().includes('Cart totals');
        });
        
        if (cartTotalsHeading.length > 0) {
            var totalsContainer = cartTotalsHeading.next();
            if (totalsContainer.length > 0) {
                totalsContainer.find('td').each(function() {
                    var $element = $(this);
                    var text = $element.text().trim();
                    
                    // Skip if already has VES conversion or doesn't contain price or already has conversion element
                    if (text.includes('VES') || !text.includes('$') || $element.find('.wvp-ves-conversion').length > 0) {
                        return;
                    }
                    
                    var match = text.match(/\$([0-9,]+\.?[0-9]*)/);
                    
                    if (match) {
                        var usdTotal = parseFloat(match[1].replace(',', ''));
                        var vesTotal = usdTotal * wvp_cart_converter_ajax.rate;
                        var vesFormatted = vesTotal.toLocaleString('es-VE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        
                        $element.append('<br><small class="wvp-ves-conversion" style="color: #27ae60;">(' + vesFormatted + ' VES)</small>');
                        conversionsAdded++;
                        console.log('WVP Cart Currency Converter: Added VES conversion to total:', usdTotal, '->', vesFormatted);
                    }
                });
            }
        }
        
        return conversionsAdded;
    }
    
    // Main function to apply conversions
    function applyConversions() {
        // Always remove existing conversions first
        $('.wvp-ves-conversion').remove();
        
        console.log('WVP Cart Currency Converter: Applying conversions...');
        
        var cartConversions = addVesConversionsToCart();
        var totalConversions = addVesConversionsToTotals();
        
        if (cartConversions > 0 || totalConversions > 0) {
            conversionsApplied = true;
            console.log('WVP Cart Currency Converter: Conversions applied successfully - Cart:', cartConversions, 'Totals:', totalConversions);
        } else {
            console.log('WVP Cart Currency Converter: No conversions applied, retrying...');
        }
    }
    
    // Run on cart page
    if ($('body').hasClass('woocommerce-cart')) {
        console.log('WVP Cart Currency Converter: On cart page, adding conversions');
        
        // Single execution with delay to ensure DOM is ready
        setTimeout(applyConversions, 1000);
        
        // Re-run when cart updates
        $(document.body).on('updated_cart_totals', function() {
            console.log('WVP Cart Currency Converter: Cart updated, re-adding conversions');
            conversionsApplied = false; // Reset flag
            setTimeout(applyConversions, 500);
        });
    }
    
    // Run on checkout page
    if ($('body').hasClass('woocommerce-checkout')) {
        console.log('WVP Cart Currency Converter: On checkout page, adding conversions');
        
        // Single execution with delay to ensure DOM is ready
        setTimeout(applyConversions, 1000);
        
        // Re-run when checkout updates (e.g., shipping method change)
        $(document.body).on('updated_checkout', function() {
            console.log('WVP Cart Currency Converter: Checkout updated, re-adding conversions');
            conversionsApplied = false; // Reset flag
            setTimeout(applyConversions, 500);
        });
    }
});
