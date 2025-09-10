jQuery(document).ready(function($) {
    'use strict';
    
    // Verificar pago
    $('.wvp-verify-payment-btn').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(wvp_verification.i18n.confirm_verification)) {
            return;
        }
        
        var $button = $(this);
        var orderId = $button.data('order-id');
        var $row = $button.closest('tr');
        
        $button.prop('disabled', true).text(wvp_verification.i18n.verifying);
        
        $.ajax({
            url: wvp_verification.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_verify_payment',
                nonce: wvp_verification.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    // Remover fila de la tabla
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Verificar si no hay más pedidos pendientes
                        if ($('.wvp-verify-payment-btn').length === 0) {
                            $('.wvp-pending-orders-table').html(
                                '<div class="wvp-no-pending-orders">' +
                                '<p>No hay pedidos pendientes de verificación en este momento.</p>' +
                                '</div>'
                            );
                        }
                    });
                    
                    // Mostrar mensaje de éxito
                    showNotice(wvp_verification.i18n.success, 'success');
                } else {
                    showNotice(response.data.message || wvp_verification.i18n.error, 'error');
                    $button.prop('disabled', false).text('Verificar Pago');
                }
            },
            error: function() {
                showNotice(wvp_verification.i18n.error, 'error');
                $button.prop('disabled', false).text('Verificar Pago');
            }
        });
    });
    
    // Subir comprobante de pago
    $('.wvp-upload-proof-btn').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var orderId = $button.data('order-id');
        var $fileInput = $('#wvp-proof-' + orderId);
        var file = $fileInput[0].files[0];
        
        if (!file) {
            alert('Por favor seleccione un archivo');
            return;
        }
        
        // Validar tipo de archivo
        var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (allowedTypes.indexOf(file.type) === -1) {
            alert('Tipo de archivo no permitido. Solo se permiten imágenes y PDFs');
            return;
        }
        
        // Validar tamaño (máximo 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('El archivo es demasiado grande. Máximo 5MB');
            return;
        }
        
        $button.prop('disabled', true).text(wvp_verification.i18n.uploading);
        
        var formData = new FormData();
        formData.append('action', 'wvp_upload_proof');
        formData.append('nonce', wvp_verification.nonce);
        formData.append('order_id', orderId);
        formData.append('proof_file', file);
        
        $.ajax({
            url: wvp_verification.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotice(wvp_verification.i18n.upload_success, 'success');
                    
                    // Limpiar input de archivo
                    $fileInput.val('');
                    
                    // Mostrar enlace al archivo si se proporciona
                    if (response.data.file_url) {
                        var $proofLink = $('<a href="' + response.data.file_url + '" target="_blank" class="wvp-proof-link">Ver Comprobante</a>');
                        $button.after('<br>').after($proofLink);
                    }
                } else {
                    showNotice(response.data.message || wvp_verification.i18n.upload_error, 'error');
                }
            },
            error: function() {
                showNotice(wvp_verification.i18n.upload_error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text('Subir Comprobante');
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
