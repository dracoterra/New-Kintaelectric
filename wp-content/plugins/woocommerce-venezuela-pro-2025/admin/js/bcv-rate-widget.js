/**
 * JavaScript para Widget de Dólar del Día BCV
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Función global para refrescar tasa
    window.wcvsRefreshRate = function() {
        var $widget = $('.wcvs-rate-widget');
        var $button = $widget.find('.wcvs-rate-actions .button');
        var $rateValue = $widget.find('.wcvs-rate-value');
        var $rateTime = $widget.find('.wcvs-rate-time');
        var $rateError = $widget.find('.wcvs-rate-error');
        
        // Mostrar estado de carga
        $button.prop('disabled', true);
        $button.find('.dashicons').addClass('wcvs-spinning');
        
        // Ocultar error si existe
        $rateError.hide();
        
        $.ajax({
            url: wcvs_bcv_rate.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_refresh_rate',
                nonce: wcvs_bcv_rate.nonce
            },
            success: function(response) {
                if (response.success && response.data.rate) {
                    // Actualizar valor de tasa
                    $rateValue.text(response.data.formatted_rate);
                    
                    // Actualizar timestamp
                    if ($rateTime.length) {
                        $rateTime.text(response.data.timestamp);
                    } else {
                        // Crear elemento de tiempo si no existe
                        var $rateInfo = $widget.find('.wcvs-rate-info');
                        if ($rateInfo.length === 0) {
                            $widget.find('.wcvs-rate-display').after(
                                '<div class="wcvs-rate-info">' +
                                '<span class="wcvs-rate-label">Última actualización:</span> ' +
                                '<span class="wcvs-rate-time">' + response.data.timestamp + '</span>' +
                                '</div>'
                            );
                        }
                    }
                    
                    // Mostrar mensaje de éxito
                    wcvsShowMessage('Tasa actualizada correctamente', 'success');
                } else {
                    // Mostrar error
                    $rateError.show();
                    wcvsShowMessage('Error al actualizar la tasa', 'error');
                }
            },
            error: function() {
                $rateError.show();
                wcvsShowMessage('Error de conexión al actualizar la tasa', 'error');
            },
            complete: function() {
                // Restaurar botón
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('wcvs-spinning');
            }
        });
    };

    // Función para mostrar mensajes
    function wcvsShowMessage(message, type) {
        var $message = $('<div class="wcvs-message wcvs-message-' + type + '">' + message + '</div>');
        
        // Remover mensajes anteriores
        $('.wcvs-message').remove();
        
        // Añadir nuevo mensaje
        $('.wcvs-rate-widget').after($message);
        
        // Auto-ocultar después de 3 segundos
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Función para actualizar estadísticas de tasa
    function updateRateStats() {
        $.ajax({
            url: wcvs_bcv_rate.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_get_rate_stats',
                nonce: wcvs_bcv_rate.nonce
            },
            success: function(response) {
                if (response.success) {
                    var stats = response.data;
                    
                    // Actualizar información de estado
                    var $statusBadge = $('.wcvs-rate-widget .wcvs-status-badge');
                    if (stats.bcv_available) {
                        $statusBadge.removeClass('wcvs-status-warning').addClass('wcvs-status-success')
                            .text('BCV Activo');
                    } else {
                        $statusBadge.removeClass('wcvs-status-success').addClass('wcvs-status-warning')
                            .text('Modo Respaldo');
                    }
                    
                    // Actualizar próxima sincronización
                    if (stats.next_sync) {
                        var nextSync = new Date(stats.next_sync * 1000);
                        var $nextSync = $('.wcvs-next-sync');
                        if ($nextSync.length) {
                            $nextSync.text('Próxima: ' + nextSync.toLocaleString());
                        }
                    }
                }
            }
        });
    }

    // Función para formatear tasa
    function formatRate(rate) {
        if (!rate || rate <= 0) {
            return 'N/A';
        }
        return parseFloat(rate).toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Función para formatear fecha
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        var date = new Date(dateString);
        return date.toLocaleString('es-VE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Función para crear widget de tasa
    function createRateWidget() {
        var $widget = $('.wcvs-rate-widget');
        
        if ($widget.length === 0) {
            return;
        }
        
        // Agregar estilos CSS si no existen
        if ($('#wcvs-rate-widget-styles').length === 0) {
            $('<style id="wcvs-rate-widget-styles">')
                .prop('type', 'text/css')
                .html(`
                    .wcvs-rate-widget {
                        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                        border: 1px solid #dee2e6;
                        border-radius: 8px;
                        padding: 20px;
                        margin: 20px 0;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    
                    .wcvs-rate-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 15px;
                    }
                    
                    .wcvs-rate-header h3 {
                        margin: 0;
                        color: #0073aa;
                        font-size: 18px;
                        font-weight: 600;
                    }
                    
                    .wcvs-status-badge {
                        padding: 4px 8px;
                        border-radius: 4px;
                        font-size: 12px;
                        font-weight: 600;
                        text-transform: uppercase;
                    }
                    
                    .wcvs-status-success {
                        background: #d4edda;
                        color: #155724;
                        border: 1px solid #c3e6cb;
                    }
                    
                    .wcvs-status-warning {
                        background: #fff3cd;
                        color: #856404;
                        border: 1px solid #ffeaa7;
                    }
                    
                    .wcvs-rate-display {
                        text-align: center;
                        margin: 20px 0;
                    }
                    
                    .wcvs-rate-value {
                        font-size: 32px;
                        font-weight: bold;
                        color: #0073aa;
                        margin-bottom: 5px;
                    }
                    
                    .wcvs-rate-currency {
                        font-size: 14px;
                        color: #666;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                    }
                    
                    .wcvs-rate-info {
                        text-align: center;
                        margin: 15px 0;
                        font-size: 12px;
                        color: #666;
                    }
                    
                    .wcvs-rate-label {
                        font-weight: 600;
                    }
                    
                    .wcvs-rate-time {
                        color: #0073aa;
                    }
                    
                    .wcvs-rate-error {
                        text-align: center;
                        color: #d63638;
                        font-size: 14px;
                        margin: 15px 0;
                        display: none;
                    }
                    
                    .wcvs-rate-error .dashicons {
                        margin-right: 5px;
                    }
                    
                    .wcvs-rate-actions {
                        text-align: center;
                        margin-top: 15px;
                    }
                    
                    .wcvs-rate-actions .button {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                    }
                    
                    .wcvs-rate-actions .button:disabled {
                        opacity: 0.6;
                        cursor: not-allowed;
                    }
                    
                    .wcvs-spinning {
                        animation: wcvs-spin 1s linear infinite;
                    }
                    
                    @keyframes wcvs-spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    
                    .wcvs-message {
                        padding: 10px 15px;
                        border-radius: 4px;
                        margin: 10px 0;
                        font-weight: 600;
                    }
                    
                    .wcvs-message-success {
                        background: #d4edda;
                        color: #155724;
                        border: 1px solid #c3e6cb;
                    }
                    
                    .wcvs-message-error {
                        background: #f8d7da;
                        color: #721c24;
                        border: 1px solid #f5c6cb;
                    }
                    
                    .wcvs-next-sync {
                        font-size: 11px;
                        color: #999;
                        text-align: center;
                        margin-top: 10px;
                    }
                    
                    @media (max-width: 768px) {
                        .wcvs-rate-widget {
                            padding: 15px;
                        }
                        
                        .wcvs-rate-value {
                            font-size: 28px;
                        }
                        
                        .wcvs-rate-header {
                            flex-direction: column;
                            gap: 10px;
                            text-align: center;
                        }
                    }
                `)
                .appendTo('head');
        }
        
        // Configurar auto-actualización cada 5 minutos
        setInterval(function() {
            updateRateStats();
        }, 300000); // 5 minutos
        
        // Actualizar estadísticas iniciales
        updateRateStats();
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        createRateWidget();
        
        // Configurar auto-refresh cada 30 minutos
        setInterval(function() {
            if ($('.wcvs-rate-widget').length > 0) {
                wcvsRefreshRate();
            }
        }, 1800000); // 30 minutos
    });

})(jQuery);
