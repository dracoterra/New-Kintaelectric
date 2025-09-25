jQuery(document).ready(function($) {
    'use strict';
    
    // Simple currency converter
    $('.wvp-currency-btn').on('click', function() {
        var $this = $(this);
        var currency = $this.data('currency');
        
        // Update button states
        $('.wvp-currency-btn').removeClass('active');
        $this.addClass('active');
        
        // Update price display
        var priceText = $this.text();
        $('.price').html('<span class="amount">' + priceText + '</span>');
        
        // Store preference
        localStorage.setItem('wvp_preferred_currency', currency);
    });
    
    // Load saved preference
    var savedCurrency = localStorage.getItem('wvp_preferred_currency');
    if (savedCurrency) {
        $('.wvp-currency-btn[data-currency="' + savedCurrency + '"]').click();
    }
});
