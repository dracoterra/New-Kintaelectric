jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize product display functionality
    initWVPProductDisplay();
    
    function initWVPProductDisplay() {
        // Update dual prices when currency switcher changes
        $('.wvp-currency-btn').on('click', function() {
            updateDualPrices();
        });
        
        // Update prices on cart updates
        $(document.body).on('updated_cart_totals', function() {
            updateCartDualPrices();
        });
        
        // Update prices on checkout updates
        $(document.body).on('updated_checkout', function() {
            updateCheckoutDualPrices();
        });
        
        // Initialize tooltips for rate info
        $('.wvp-rate-info').attr('title', 'Tasa de cambio del Banco Central de Venezuela');
        
        // Add smooth animations
        addSmoothAnimations();
    }
    
    function updateDualPrices() {
        var selectedCurrency = $('.wvp-currency-btn.active').data('currency');
        var rate = parseFloat($('.wvp-rate-info small').text().match(/[\d.]+/)[0]);
        
        $('.wvp-dual-price-display, .wvp-shop-dual-price').each(function() {
            var $container = $(this);
            var $usdPrice = $container.find('.wvp-price-usd, .wvp-shop-price-usd');
            var $vesPrice = $container.find('.wvp-price-ves, .wvp-shop-price-ves');
            
            if (selectedCurrency === 'VES') {
                $usdPrice.hide();
                $vesPrice.show();
            } else {
                $usdPrice.show();
                $vesPrice.hide();
            }
        });
    }
    
    function updateCartDualPrices() {
        // This function will be called when cart totals are updated
        // The server-side hooks will handle the actual price updates
        console.log('Cart dual prices updated');
    }
    
    function updateCheckoutDualPrices() {
        // This function will be called when checkout totals are updated
        // The server-side hooks will handle the actual price updates
        console.log('Checkout dual prices updated');
    }
    
    function addSmoothAnimations() {
        // Add fade-in animation to dual price displays
        $('.wvp-dual-price-display, .wvp-shop-dual-price').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            }).delay(index * 100).animate({
                'opacity': '1'
            }, 500).css('transform', 'translateY(0)');
        });
    }
    
    // Handle currency switcher integration
    $(document).on('click', '.wvp-currency-btn', function() {
        var $this = $(this);
        var currency = $this.data('currency');
        
        // Update button states
        $('.wvp-currency-btn').removeClass('active');
        $this.addClass('active');
        
        // Update all dual price displays
        updateDualPrices();
        
        // Store preference
        localStorage.setItem('wvp_preferred_currency', currency);
        
        // Trigger custom event
        $(document).trigger('wvp_currency_changed', [currency]);
    });
    
    // Load saved currency preference
    var savedCurrency = localStorage.getItem('wvp_preferred_currency');
    if (savedCurrency) {
        $('.wvp-currency-btn[data-currency="' + savedCurrency + '"]').click();
    }
    
    // Handle responsive design
    function handleResponsiveDesign() {
        if ($(window).width() <= 768) {
            $('.wvp-price-row, .wvp-shop-price-row').addClass('wvp-mobile-layout');
        } else {
            $('.wvp-price-row, .wvp-shop-price-row').removeClass('wvp-mobile-layout');
        }
    }
    
    // Call on load and resize
    handleResponsiveDesign();
    $(window).on('resize', handleResponsiveDesign);
    
    // Add loading states
    function addLoadingStates() {
        $('.wvp-dual-price-display').each(function() {
            var $container = $(this);
            var $priceRow = $container.find('.wvp-price-row');
            
            // Add loading indicator
            $priceRow.append('<div class="wvp-loading-indicator" style="display: none;">⏳</div>');
        });
    }
    
    // Initialize loading states
    addLoadingStates();
    
    // Handle AJAX price updates
    function updatePricesViaAJAX() {
        $('.wvp-loading-indicator').show();
        
        $.ajax({
            url: wvp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_update_prices',
                nonce: wvp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update prices with new data
                    updatePricesFromResponse(response.data);
                }
            },
            error: function() {
                console.log('Error updating prices');
            },
            complete: function() {
                $('.wvp-loading-indicator').hide();
            }
        });
    }
    
    function updatePricesFromResponse(data) {
        // Update prices based on AJAX response
        if (data.rate) {
            $('.wvp-rate-info small').text('Tasa BCV: 1 USD = ' + data.rate + ' VES');
        }
    }
    
    // Auto-refresh prices every 5 minutes
    setInterval(function() {
        if ($('.wvp-dual-price-display').length > 0) {
            updatePricesViaAJAX();
        }
    }, 300000); // 5 minutes
    
    // Add hover effects
    $('.wvp-dual-price-display').hover(
        function() {
            $(this).addClass('wvp-hover-effect');
        },
        function() {
            $(this).removeClass('wvp-hover-effect');
        }
    );
    
    // Handle print styles
    window.addEventListener('beforeprint', function() {
        $('.wvp-dual-price-display, .wvp-shop-dual-price').addClass('wvp-print-mode');
    });
    
    window.addEventListener('afterprint', function() {
        $('.wvp-dual-price-display, .wvp-shop-dual-price').removeClass('wvp-print-mode');
    });
    
    // Add accessibility features
    function addAccessibilityFeatures() {
        $('.wvp-dual-price-display').attr('role', 'region');
        $('.wvp-dual-price-display').attr('aria-label', 'Precios en USD y VES');
        
        $('.wvp-price-usd, .wvp-shop-price-usd').attr('aria-label', 'Precio en dólares estadounidenses');
        $('.wvp-price-ves, .wvp-shop-price-ves').attr('aria-label', 'Precio en bolívares venezolanos');
    }
    
    // Initialize accessibility features
    addAccessibilityFeatures();
    
    // Handle theme compatibility
    function ensureThemeCompatibility() {
        // Check if we're in a WooCommerce context
        if ($('body').hasClass('woocommerce') || $('body').hasClass('woocommerce-page')) {
            // Add theme-specific adjustments
            $('.wvp-dual-price-display').addClass('wvp-theme-compatible');
        }
    }
    
    // Initialize theme compatibility
    ensureThemeCompatibility();
});
