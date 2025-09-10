jQuery(document).ready(function($) {
    'use strict';
    
    // Generar factura PDF
    $('#wvp-generate-invoice').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var orderId = $button.data('order-id');
        
        $button.prop('disabled', true).text(wvp_invoice.i18n.generating);
        
        $.ajax({
            url: wvp_invoice.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_generate_invoice',
                nonce: wvp_invoice.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    // Abrir factura en nueva ventana
                    window.open(response.data.url, '_blank');
                    
                    // Mostrar mensaje de éxito
                    showNotice(wvp_invoice.i18n.success, 'success');
                } else {
                    showNotice(response.data.message || wvp_invoice.i18n.error, 'error');
                }
            },
            error: function() {
                showNotice(wvp_invoice.i18n.error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text('Generar Factura PDF');
            }
        });
    });
    
    // Función para mostrar notificaciones
    function showNotice(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        // Auto-remover después de 5 segundos
        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
});
