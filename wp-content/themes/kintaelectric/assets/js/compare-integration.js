jQuery(document).ready(function($) {
    'use strict';
    
    // Simple compare counter update
    function updateCompareCounter() {
        // Try to get count from localStorage first
        var compareList = localStorage.getItem('yith_woocompare_list');
        var count = 0;
        
        if (compareList) {
            try {
                var products = JSON.parse(compareList);
                count = products.length;
            } catch(e) {
                count = 0;
            }
        }
        
        $('.navbar-compare-count').text(count);
    }
    
    // Update counter on page load
    updateCompareCounter();
    
    // Update counter when storage changes
    $(window).on('storage', function(e) {
        if (e.originalEvent.key === 'yith_woocompare_list') {
            updateCompareCounter();
        }
    });
    
    // Listen for compare events (if available)
    $(document.body).on('added_to_compare removed_from_compare', function() {
        setTimeout(updateCompareCounter, 100);
    });
});
