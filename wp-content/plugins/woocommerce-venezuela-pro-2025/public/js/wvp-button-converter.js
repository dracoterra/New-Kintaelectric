jQuery(document).ready(function($) {
    'use strict';
    
    // Check if wvp_button_converter_ajax is available
    if (typeof wvp_button_converter_ajax === 'undefined') {
        console.log('WVP Button Currency Converter: AJAX object not available');
        return;
    }
    
    console.log('WVP Button Currency Converter: JavaScript loaded successfully');
    
    // Currency button switcher
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
});
