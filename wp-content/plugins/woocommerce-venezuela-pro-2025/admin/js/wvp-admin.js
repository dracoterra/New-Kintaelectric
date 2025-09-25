jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize admin interface
    initWVPAdmin();
    
    function initWVPAdmin() {
        // Add loading states to buttons
        $('.wvp-action-btn').on('click', function() {
            $(this).addClass('wvp-loading');
        });
        
        // Auto-refresh stats every 30 seconds
        if ($('.wvp-dashboard').length) {
            setInterval(refreshDashboardStats, 30000);
        }
        
        // Handle settings form
        $('.wvp-settings form').on('submit', function() {
            $(this).find('input[type="submit"]').addClass('wvp-loading');
        });
        
        // Add tooltips
        $('[data-tooltip]').each(function() {
            $(this).attr('title', $(this).data('tooltip'));
        });
    }
    
    function refreshDashboardStats() {
        $.ajax({
            url: wvp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_get_dashboard_stats',
                nonce: wvp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                }
            },
            error: function() {
                console.log('Error refreshing dashboard stats');
            }
        });
    }
    
    function updateStatsDisplay(stats) {
        // Update stat cards with new data
        $('.wvp-stat-card').each(function() {
            var $card = $(this);
            var $content = $card.find('.wvp-stat-content');
            var $h3 = $content.find('h3');
            var $p = $content.find('p');
            
            // Determine which stat this card represents
            if ($p.text().includes('Pedidos')) {
                $h3.text(stats.total_orders);
            } else if ($p.text().includes('Ingresos')) {
                $h3.text(stats.total_revenue_formatted);
            } else if ($p.text().includes('Conversión')) {
                $h3.text(stats.conversion_rate + '%');
            } else if ($p.text().includes('Clientes')) {
                $h3.text(stats.total_customers);
            }
        });
    }
    
    // Handle currency switcher in admin
    $('.wvp-currency-switcher').on('change', function() {
        var currency = $(this).val();
        var $priceElements = $('.wvp-price-display');
        
        $priceElements.each(function() {
            var $element = $(this);
            var usdPrice = $element.data('usd-price');
            var vesPrice = $element.data('ves-price');
            
            if (currency === 'VES') {
                $element.text(vesPrice);
            } else {
                $element.text(usdPrice);
            }
        });
    });
    
    // Handle tax rate updates
    $('.wvp-tax-rate-input').on('change', function() {
        var $input = $(this);
        var rate = parseFloat($input.val());
        var type = $input.data('tax-type');
        
        // Validate rate
        if (type === 'iva' && (rate < 0 || rate > 50)) {
            alert('La tasa de IVA debe estar entre 0% y 50%');
            $input.focus();
            return;
        }
        
        if (type === 'igtf' && (rate < 0 || rate > 10)) {
            alert('La tasa de IGTF debe estar entre 0% y 10%');
            $input.focus();
            return;
        }
        
        // Update preview
        updateTaxPreview();
    });
    
    function updateTaxPreview() {
        var ivaRate = parseFloat($('#wvp_iva_rate').val()) || 16;
        var igtfRate = parseFloat($('#wvp_igtf_rate').val()) || 3;
        var testAmount = 100; // $100 USD test amount
        
        var ivaAmount = (testAmount * ivaRate) / 100;
        var igtfAmount = testAmount > 200 ? (testAmount * igtfRate) / 100 : 0;
        var total = testAmount + ivaAmount + igtfAmount;
        
        var previewHtml = '<div class="wvp-tax-preview">';
        previewHtml += '<h4>Vista Previa de Impuestos:</h4>';
        previewHtml += '<p>Subtotal: $' + testAmount.toFixed(2) + ' USD</p>';
        previewHtml += '<p>IVA (' + ivaRate + '%): $' + ivaAmount.toFixed(2) + ' USD</p>';
        if (igtfAmount > 0) {
            previewHtml += '<p>IGTF (' + igtfRate + '%): $' + igtfAmount.toFixed(2) + ' USD</p>';
        }
        previewHtml += '<p><strong>Total: $' + total.toFixed(2) + ' USD</strong></p>';
        previewHtml += '</div>';
        
        $('.wvp-tax-preview').remove();
        $('.wvp-settings-section').last().after(previewHtml);
    }
    
    // Handle system status checks
    $('.wvp-status-check').on('click', function() {
        var $button = $(this);
        var $status = $button.siblings('.wvp-status-value');
        
        $button.addClass('wvp-loading');
        
        $.ajax({
            url: wvp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_check_system_status',
                nonce: wvp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.removeClass('wvp-status-ok wvp-status-warning wvp-status-error');
                    $status.addClass('wvp-status-' + response.data.status);
                    $status.text(response.data.message);
                }
            },
            error: function() {
                $status.removeClass('wvp-status-ok wvp-status-warning wvp-status-error');
                $status.addClass('wvp-status-error');
                $status.text('❌ Error');
            },
            complete: function() {
                $button.removeClass('wvp-loading');
            }
        });
    });
    
    // Handle export functionality
    $('.wvp-export-btn').on('click', function() {
        var $button = $(this);
        var exportType = $button.data('export-type');
        
        $button.addClass('wvp-loading');
        
        $.ajax({
            url: wvp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_export_data',
                export_type: exportType,
                nonce: wvp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Download file
                    var link = document.createElement('a');
                    link.href = response.data.download_url;
                    link.download = response.data.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Error al exportar datos: ' + response.data.message);
                }
            },
            error: function() {
                alert('Error al exportar datos');
            },
            complete: function() {
                $button.removeClass('wvp-loading');
            }
        });
    });
    
    // Handle notifications
    function showNotification(message, type) {
        type = type || 'info';
        
        var $notification = $('<div class="wvp-notification wvp-notification-' + type + '">' + message + '</div>');
        
        $('body').append($notification);
        
        setTimeout(function() {
            $notification.fadeOut(function() {
                $notification.remove();
            });
        }, 3000);
    }
    
    // Handle form validation
    $('.wvp-settings form').on('submit', function(e) {
        var isValid = true;
        var errors = [];
        
        // Validate required fields
        $(this).find('input[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                errors.push($(this).attr('name') + ' es requerido');
            }
        });
        
        // Validate number fields
        $(this).find('input[type="number"]').each(function() {
            var value = parseFloat($(this).val());
            var min = parseFloat($(this).attr('min'));
            var max = parseFloat($(this).attr('max'));
            
            if (value < min || value > max) {
                isValid = false;
                errors.push($(this).attr('name') + ' debe estar entre ' + min + ' y ' + max);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showNotification('Por favor, corrige los siguientes errores: ' + errors.join(', '), 'error');
        }
    });
});
