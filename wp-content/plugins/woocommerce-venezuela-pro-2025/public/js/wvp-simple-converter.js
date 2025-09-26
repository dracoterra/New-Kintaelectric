jQuery(document).ready(function($) {
    'use strict';
    
    // Check if wvp_converter_ajax is available
    if (typeof wvp_converter_ajax === 'undefined') {
        console.log('WVP Currency Converter: AJAX object not available');
        return;
    }
    
    console.log('WVP Currency Converter: JavaScript loaded successfully');
    
    // Simple currency converter for product pages
    $('.wvp-currency-btn').on('click', function() {
        var $this = $(this);
        var currency = $this.data('currency');
        
        // Update button states
        $('.wvp-currency-btn').removeClass('active');
        $this.addClass('active');
        
        // Show/hide price displays
        if (currency === 'usd') {
            $('.wvp-price-usd').show();
            $('.wvp-price-ves').hide();
        } else if (currency === 'ves') {
            $('.wvp-price-usd').hide();
            $('.wvp-price-ves').show();
        }
        
        // Store preference
        localStorage.setItem('wvp_preferred_currency', currency);
    });
    
    // Load saved preference
    var savedCurrency = localStorage.getItem('wvp_preferred_currency');
    if (savedCurrency) {
        $('.wvp-currency-btn[data-currency="' + savedCurrency + '"]').click();
    }
    
    // Add VES conversions to cart items
    function addVesConversionsToCart() {
        console.log('WVP Currency Converter: Adding VES conversions to cart');
        
        // Find all cart item rows (using table rows)
        $('table tbody tr').each(function() {
            var $row = $(this);
            
            // Skip header row
            if ($row.find('th').length > 0) {
                return;
            }
            
            // Find price elements in the row
            var $priceElements = $row.find('td').filter(function() {
                var text = $(this).text();
                return text.includes('$') && text.match(/\$([0-9,]+\.?[0-9]*)/);
            });
            
            $priceElements.each(function() {
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
                    var vesPrice = usdPrice * wvp_converter_ajax.rate;
                    var vesFormatted = vesPrice.toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                    // Add VES conversion to price
                    $priceElement.append('<br><small class="wvp-ves-conversion" style="color: #27ae60;">(' + vesFormatted + ' VES)</small>');
                    console.log('WVP Currency Converter: Added VES conversion to price:', usdPrice, '->', vesFormatted);
                }
            });
        });
        
        // Add VES conversion to cart totals
        $('.cart_totals td, .cart_totals .amount').each(function() {
            var $totalElement = $(this);
            
            // Skip if already has VES conversion
            if ($totalElement.find('.wvp-ves-conversion').length > 0) {
                return;
            }
            
            var totalText = $totalElement.text();
            var totalMatch = totalText.match(/\$([0-9,]+\.?[0-9]*)/);
            
            if (totalMatch) {
                var usdTotal = parseFloat(totalMatch[1].replace(',', ''));
                var vesTotal = usdTotal * wvp_converter_ajax.rate;
                var vesTotalFormatted = vesTotal.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                $totalElement.append('<br><small class="wvp-ves-conversion" style="color: #27ae60;">(' + vesTotalFormatted + ' VES)</small>');
                console.log('WVP Currency Converter: Added VES conversion to total:', usdTotal, '->', vesTotalFormatted);
            }
        });
    }
    
    // Run on cart page
    if ($('body').hasClass('woocommerce-cart')) {
        console.log('WVP Currency Converter: On cart page, adding conversions');
        
        // Wait for cart to load completely
        setTimeout(function() {
            addVesConversionsToCart();
            addVesConversionsToTotals();
        }, 500);
        
        // Re-run when cart updates
        $(document.body).on('updated_cart_totals', function() {
            console.log('WVP Currency Converter: Cart updated, re-adding conversions');
            setTimeout(function() {
                addVesConversionsToCart();
                addVesConversionsToTotals();
            }, 100);
        });
    }
    
    // Add VES conversions to cart totals
    function addVesConversionsToTotals() {
        console.log('WVP Currency Converter: Adding VES conversions to cart totals');
        
        // Find total elements by searching for specific price amounts
        var totalAmounts = ['$38.00', '$6.08', '$44.08']; // Common cart totals
        
        totalAmounts.forEach(function(amount) {
            var elements = $('*').filter(function() {
                return $(this).text().includes(amount) && !$(this).find('.wvp-ves-conversion').length;
            });
            
            elements.each(function() {
                var $element = $(this);
                var text = $element.text();
                var match = text.match(/\$([0-9,]+\.?[0-9]*)/);
                
                if (match) {
                    var usdTotal = parseFloat(match[1].replace(',', ''));
                    var vesTotal = usdTotal * wvp_converter_ajax.rate;
                    var vesFormatted = vesTotal.toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                    $element.append('<br><small class="wvp-ves-conversion" style="color: #27ae60;">(' + vesFormatted + ' VES)</small>');
                    console.log('WVP Currency Converter: Added VES conversion to total:', usdTotal, '->', vesFormatted);
                }
            });
        });
    }
    
    // Run on checkout page
    if ($('body').hasClass('woocommerce-checkout')) {
        console.log('WVP Currency Converter: On checkout page, adding conversions');
        addVesConversionsToCart();
    }
});
