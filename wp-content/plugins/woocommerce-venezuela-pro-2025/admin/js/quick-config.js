/**
 * JavaScript para Configuración Rápida de WooCommerce
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Función global para configuración rápida
    window.wcvsQuickConfigure = function() {
        if (!confirm('¿Está seguro de que desea configurar WooCommerce automáticamente? Esta acción modificará la configuración actual.')) {
            return;
        }

        var $button = $('.wcvs-quick-config-button');
        var $status = $('.wcvs-config-status');
        var $results = $('.wcvs-config-results');
        
        // Mostrar estado de carga
        $button.prop('disabled', true);
        $button.find('.dashicons').addClass('wcvs-spinning');
        $status.html('<div class="wcvs-loading"><span class="dashicons dashicons-update wcvs-spinning"></span> ' + wcvs_quick_config.i18n.configuring + '</div>');
        
        $.ajax({
            url: wcvs_quick_config.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_quick_config',
                nonce: wcvs_quick_config.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Mostrar resultados exitosos
                    var successHtml = '<div class="wcvs-success-message">';
                    successHtml += '<h3>' + wcvs_quick_config.i18n.success + '</h3>';
                    successHtml += '<ul class="wcvs-config-list">';
                    
                    response.configurations.forEach(function(config) {
                        successHtml += '<li><span class="dashicons dashicons-yes-alt"></span> ' + config + '</li>';
                    });
                    
                    successHtml += '</ul>';
                    successHtml += '</div>';
                    
                    $results.html(successHtml);
                    $status.html('<div class="wcvs-success">✓ ' + wcvs_quick_config.i18n.success + '</div>');
                    
                    // Actualizar estado de configuración
                    updateConfigStatus();
                    
                } else {
                    // Mostrar errores
                    var errorHtml = '<div class="wcvs-error-message">';
                    errorHtml += '<h3>' + wcvs_quick_config.i18n.error + '</h3>';
                    errorHtml += '<ul class="wcvs-error-list">';
                    
                    response.errors.forEach(function(error) {
                        errorHtml += '<li><span class="dashicons dashicons-warning"></span> ' + error + '</li>';
                    });
                    
                    errorHtml += '</ul>';
                    errorHtml += '</div>';
                    
                    $results.html(errorHtml);
                    $status.html('<div class="wcvs-error">✗ ' + wcvs_quick_config.i18n.error + '</div>');
                }
            },
            error: function() {
                $results.html('<div class="wcvs-error-message"><h3>Error de conexión</h3><p>No se pudo conectar con el servidor.</p></div>');
                $status.html('<div class="wcvs-error">✗ Error de conexión</div>');
            },
            complete: function() {
                // Restaurar botón
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('wcvs-spinning');
            }
        });
    };

    // Función global para resetear configuración
    window.wcvsResetConfig = function() {
        if (!confirm('¿Está seguro de que desea resetear la configuración de WooCommerce? Esta acción no se puede deshacer.')) {
            return;
        }

        var $button = $('.wcvs-reset-config-button');
        
        // Mostrar estado de carga
        $button.prop('disabled', true);
        $button.find('.dashicons').addClass('wcvs-spinning');
        
        $.ajax({
            url: wcvs_quick_config.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_reset_config',
                nonce: wcvs_quick_config.reset_nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(wcvs_quick_config.i18n.reset_success);
                    location.reload();
                } else {
                    alert('Error al resetear la configuración');
                }
            },
            error: function() {
                alert('Error de conexión al resetear');
            },
            complete: function() {
                // Restaurar botón
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('wcvs-spinning');
            }
        });
    };

    // Función para actualizar estado de configuración
    function updateConfigStatus() {
        $.ajax({
            url: wcvs_quick_config.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_get_config_status',
                nonce: wcvs_quick_config.status_nonce
            },
            success: function(response) {
                if (response.success) {
                    displayConfigStatus(response.data);
                }
            }
        });
    }

    // Función para mostrar estado de configuración
    function displayConfigStatus(status) {
        var $statusContainer = $('.wcvs-config-status-overview');
        
        if ($statusContainer.length === 0) {
            return;
        }
        
        var html = '<div class="wcvs-config-overview">';
        html += '<h3>Estado de Configuración</h3>';
        html += '<div class="wcvs-config-progress">';
        html += '<div class="wcvs-progress-bar">';
        html += '<div class="wcvs-progress-fill" style="width: ' + status.overall.percentage + '%"></div>';
        html += '</div>';
        html += '<div class="wcvs-progress-text">' + status.overall.configured + ' de ' + status.overall.total + ' configurados (' + status.overall.percentage + '%)</div>';
        html += '</div>';
        
        html += '<div class="wcvs-config-details">';
        
        // Moneda
        html += '<div class="wcvs-config-item ' + (status.currency.configured ? 'configured' : 'not-configured') + '">';
        html += '<span class="wcvs-config-icon">' + (status.currency.configured ? '✓' : '✗') + '</span>';
        html += '<div class="wcvs-config-info">';
        html += '<strong>Moneda:</strong> ' + status.currency.details.currency;
        html += '</div>';
        html += '</div>';
        
        // Impuestos
        html += '<div class="wcvs-config-item ' + (status.taxes.configured ? 'configured' : 'not-configured') + '">';
        html += '<span class="wcvs-config-icon">' + (status.taxes.configured ? '✓' : '✗') + '</span>';
        html += '<div class="wcvs-config-info">';
        html += '<strong>Impuestos:</strong> ' + (status.taxes.details.enabled ? 'Habilitados' : 'Deshabilitados');
        html += '</div>';
        html += '</div>';
        
        // Pagos
        html += '<div class="wcvs-config-item ' + (status.payments.configured ? 'configured' : 'not-configured') + '">';
        html += '<span class="wcvs-config-icon">' + (status.payments.configured ? '✓' : '✗') + '</span>';
        html += '<div class="wcvs-config-info">';
        html += '<strong>Pagos:</strong> ' + (status.payments.details.bank_transfer ? 'Transferencia' : '') + 
                (status.payments.details.cash_on_delivery ? ' Contra Entrega' : '');
        html += '</div>';
        html += '</div>';
        
        // Envíos
        html += '<div class="wcvs-config-item ' + (status.shipping.configured ? 'configured' : 'not-configured') + '">';
        html += '<span class="wcvs-config-icon">' + (status.shipping.configured ? '✓' : '✗') + '</span>';
        html += '<div class="wcvs-config-info">';
        html += '<strong>Envíos:</strong> ' + (status.shipping.details.free_shipping ? 'Gratuito' : '') + 
                (status.shipping.details.flat_rate ? ' Estándar' : '');
        html += '</div>';
        html += '</div>';
        
        // Ubicación
        html += '<div class="wcvs-config-item ' + (status.location.configured ? 'configured' : 'not-configured') + '">';
        html += '<span class="wcvs-config-icon">' + (status.location.configured ? '✓' : '✗') + '</span>';
        html += '<div class="wcvs-config-info">';
        html += '<strong>Ubicación:</strong> ' + status.location.details.country;
        html += '</div>';
        html += '</div>';
        
        // Páginas
        html += '<div class="wcvs-config-item ' + (status.pages.configured ? 'configured' : 'not-configured') + '">';
        html += '<span class="wcvs-config-icon">' + (status.pages.configured ? '✓' : '✗') + '</span>';
        html += '<div class="wcvs-config-info">';
        html += '<strong>Páginas:</strong> Todas creadas';
        html += '</div>';
        html += '</div>';
        
        html += '</div>';
        html += '</div>';
        
        $statusContainer.html(html);
    }

    // Función para mostrar configuración paso a paso
    function showStepByStepConfig() {
        var $container = $('.wcvs-step-by-step-config');
        
        if ($container.length === 0) {
            return;
        }
        
        var steps = [
            {
                title: 'Moneda Base',
                description: 'Configurar moneda a USD y formato venezolano',
                action: 'configure_currency'
            },
            {
                title: 'Impuestos',
                description: 'Configurar IVA (16%) e IGTF (3%)',
                action: 'configure_taxes'
            },
            {
                title: 'Métodos de Pago',
                description: 'Habilitar transferencia bancaria y pago contra entrega',
                action: 'configure_payments'
            },
            {
                title: 'Métodos de Envío',
                description: 'Configurar envío gratuito y estándar',
                action: 'configure_shipping'
            },
            {
                title: 'Ubicación',
                description: 'Configurar Venezuela como país base',
                action: 'configure_location'
            },
            {
                title: 'Páginas',
                description: 'Crear páginas necesarias de WooCommerce',
                action: 'configure_pages'
            }
        ];
        
        var html = '<div class="wcvs-steps-container">';
        html += '<h3>Configuración Paso a Paso</h3>';
        
        steps.forEach(function(step, index) {
            html += '<div class="wcvs-step" data-step="' + step.action + '">';
            html += '<div class="wcvs-step-number">' + (index + 1) + '</div>';
            html += '<div class="wcvs-step-content">';
            html += '<h4>' + step.title + '</h4>';
            html += '<p>' + step.description + '</p>';
            html += '<button type="button" class="button button-secondary wcvs-step-button" data-action="' + step.action + '">';
            html += 'Configurar ' + step.title;
            html += '</button>';
            html += '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        
        $container.html(html);
        
        // Manejar clic en botones de paso
        $container.on('click', '.wcvs-step-button', function() {
            var action = $(this).data('action');
            configureStep(action, $(this));
        });
    }

    // Función para configurar paso individual
    function configureStep(action, $button) {
        $button.prop('disabled', true);
        $button.find('.dashicons').addClass('wcvs-spinning');
        
        $.ajax({
            url: wcvs_quick_config.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_configure_step',
                step: action,
                nonce: wcvs_quick_config.nonce
            },
            success: function(response) {
                if (response.success) {
                    $button.text('✓ Configurado');
                    $button.removeClass('button-secondary').addClass('button-primary');
                    $button.closest('.wcvs-step').addClass('completed');
                } else {
                    alert('Error al configurar: ' + response.data);
                }
            },
            error: function() {
                alert('Error de conexión');
            },
            complete: function() {
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('wcvs-spinning');
            }
        });
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        // Agregar estilos CSS
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .wcvs-quick-config-container {
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 25px;
                    margin: 20px 0;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .wcvs-quick-config-header {
                    text-align: center;
                    margin-bottom: 25px;
                }
                
                .wcvs-quick-config-header h2 {
                    color: #0073aa;
                    margin: 0 0 10px 0;
                }
                
                .wcvs-quick-config-header p {
                    color: #666;
                    margin: 0;
                }
                
                .wcvs-quick-config-actions {
                    text-align: center;
                    margin: 25px 0;
                }
                
                .wcvs-quick-config-button,
                .wcvs-reset-config-button {
                    margin: 0 10px;
                    padding: 12px 24px;
                    font-size: 16px;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .wcvs-quick-config-button {
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    border: none;
                    color: #fff;
                }
                
                .wcvs-quick-config-button:hover {
                    background: linear-gradient(135deg, #005a87 0%, #004066 100%);
                }
                
                .wcvs-reset-config-button {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    color: #495057;
                }
                
                .wcvs-reset-config-button:hover {
                    background: #e9ecef;
                }
                
                .wcvs-config-status {
                    text-align: center;
                    margin: 20px 0;
                    padding: 15px;
                    border-radius: 5px;
                }
                
                .wcvs-loading {
                    color: #0073aa;
                    font-weight: 600;
                }
                
                .wcvs-success {
                    color: #00a32a;
                    font-weight: 600;
                }
                
                .wcvs-error {
                    color: #d63638;
                    font-weight: 600;
                }
                
                .wcvs-config-results {
                    margin: 20px 0;
                }
                
                .wcvs-success-message {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    border-radius: 5px;
                    padding: 20px;
                    color: #155724;
                }
                
                .wcvs-error-message {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 5px;
                    padding: 20px;
                    color: #721c24;
                }
                
                .wcvs-config-list,
                .wcvs-error-list {
                    list-style: none;
                    padding: 0;
                    margin: 15px 0 0 0;
                }
                
                .wcvs-config-list li,
                .wcvs-error-list li {
                    padding: 5px 0;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .wcvs-config-overview {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                
                .wcvs-config-progress {
                    margin: 15px 0;
                }
                
                .wcvs-progress-bar {
                    background: #e9ecef;
                    border-radius: 10px;
                    height: 20px;
                    overflow: hidden;
                    margin-bottom: 10px;
                }
                
                .wcvs-progress-fill {
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    height: 100%;
                    transition: width 0.3s ease;
                }
                
                .wcvs-progress-text {
                    text-align: center;
                    font-weight: 600;
                    color: #495057;
                }
                
                .wcvs-config-details {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 15px;
                    margin-top: 20px;
                }
                
                .wcvs-config-item {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 15px;
                    border-radius: 5px;
                    border: 1px solid #dee2e6;
                }
                
                .wcvs-config-item.configured {
                    background: #d4edda;
                    border-color: #c3e6cb;
                }
                
                .wcvs-config-item.not-configured {
                    background: #fff3cd;
                    border-color: #ffeaa7;
                }
                
                .wcvs-config-icon {
                    font-weight: bold;
                    font-size: 18px;
                }
                
                .wcvs-config-item.configured .wcvs-config-icon {
                    color: #155724;
                }
                
                .wcvs-config-item.not-configured .wcvs-config-icon {
                    color: #856404;
                }
                
                .wcvs-config-info {
                    flex: 1;
                }
                
                .wcvs-steps-container {
                    margin: 25px 0;
                }
                
                .wcvs-step {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                    padding: 20px;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    margin-bottom: 15px;
                    background: #fff;
                }
                
                .wcvs-step.completed {
                    background: #d4edda;
                    border-color: #c3e6cb;
                }
                
                .wcvs-step-number {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: #0073aa;
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 18px;
                }
                
                .wcvs-step.completed .wcvs-step-number {
                    background: #155724;
                }
                
                .wcvs-step-content {
                    flex: 1;
                }
                
                .wcvs-step-content h4 {
                    margin: 0 0 5px 0;
                    color: #333;
                }
                
                .wcvs-step-content p {
                    margin: 0 0 10px 0;
                    color: #666;
                }
                
                .wcvs-step-button {
                    padding: 8px 16px;
                }
                
                .wcvs-spinning {
                    animation: wcvs-spin 1s linear infinite;
                }
                
                @keyframes wcvs-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                @media (max-width: 768px) {
                    .wcvs-quick-config-actions {
                        flex-direction: column;
                        gap: 10px;
                    }
                    
                    .wcvs-quick-config-button,
                    .wcvs-reset-config-button {
                        width: 100%;
                        justify-content: center;
                    }
                    
                    .wcvs-config-details {
                        grid-template-columns: 1fr;
                    }
                    
                    .wcvs-step {
                        flex-direction: column;
                        text-align: center;
                    }
                }
            `)
            .appendTo('head');
        
        // Inicializar estado de configuración
        updateConfigStatus();
        
        // Mostrar configuración paso a paso
        showStepByStepConfig();
    });

})(jQuery);
