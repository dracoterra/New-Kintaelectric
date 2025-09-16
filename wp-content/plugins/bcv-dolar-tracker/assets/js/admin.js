/**
 * JavaScript simplificado para BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.1.0
 */

jQuery(document).ready(function($) {
    
    // ===== TOGGLE CRON =====
    $('#toggle-cron').on('click', function() {
        var $button = $(this);
        var $result = $('#cron-toggle-result');
        
        $button.prop('disabled', true).text('Cambiando...');
        
        $.post(bcv_ajax.ajax_url, {
            action: 'bcv_toggle_cron',
            nonce: bcv_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                $result.html('<span class="bcv-result success">✓ ' + response.data.message + '</span>');
                
                // Actualizar botón
                if (response.data.enabled) {
                    $button.text('Desactivar Cron').removeClass('bcv-toggle-btn').addClass('bcv-toggle-btn');
                } else {
                    $button.text('Activar Cron').removeClass('bcv-toggle-btn').addClass('bcv-toggle-btn');
                }
                
                // Actualizar estado visual
                $('.bcv-status-value').removeClass('bcv-status-active bcv-status-inactive')
                    .addClass('bcv-status-' + (response.data.enabled ? 'active' : 'inactive'))
                    .text(response.data.status_text);
                
            } else {
                $result.html('<span class="bcv-result error">✗ ' + response.data + '</span>');
            }
        })
        .fail(function() {
            $result.html('<span class="bcv-result error">✗ Error de conexión</span>');
        })
        .always(function() {
            $button.prop('disabled', false);
        });
    });
    
    // ===== SELECTOR DE INTERVALOS =====
    $('#cron_interval_preset').on('change', function() {
        var preset = $(this).val();
        var $timeInputs = $('#time-inputs');
        
        if (preset === 'custom') {
            $timeInputs.show();
            return;
        }
        
        if (preset === '') {
            $timeInputs.hide();
            return;
        }
        
        // Ocultar campos personalizados
        $timeInputs.hide();
        
        // Convertir segundos a horas, minutos, segundos
        var totalSeconds = parseInt(preset);
        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        
        // Actualizar campos
        $('#cron_hours').val(hours);
        $('#cron_minutes').val(minutes);
        $('#cron_seconds').val(seconds);
    });
    
    // ===== VALIDACIÓN DEL FORMULARIO DE CRON =====
    $('#cron-settings-form').on('submit', function(e) {
        var hours = parseInt($('#cron_hours').val()) || 0;
        var minutes = parseInt($('#cron_minutes').val()) || 0;
        var seconds = parseInt($('#cron_seconds').val()) || 0;
        var totalSeconds = (hours * 3600) + (minutes * 60) + seconds;
        
        if (totalSeconds < 60) {
            showValidationError('El intervalo mínimo debe ser de 1 minuto (60 segundos).');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // ===== FUNCIÓN PARA MOSTRAR ERRORES DE VALIDACIÓN =====
    function showValidationError(message) {
        // Remover mensajes anteriores
        $('.bcv-validation-error').remove();
        
        // Crear mensaje de error elegante
        var errorHtml = '<div class="bcv-validation-error" style="' +
            'background: #f8d7da; ' +
            'color: #721c24; ' +
            'border: 1px solid #f5c6cb; ' +
            'border-radius: 8px; ' +
            'padding: 15px 20px; ' +
            'margin: 15px 0; ' +
            'display: flex; ' +
            'align-items: center; ' +
            'gap: 10px; ' +
            'box-shadow: 0 2px 8px rgba(220, 53, 69, 0.1); ' +
            'animation: slideDown 0.3s ease-out;' +
        '">';
        errorHtml += '<span style="font-size: 18px;">⚠️</span>';
        errorHtml += '<div>';
        errorHtml += '<strong>Error de Validación</strong><br>';
        errorHtml += '<span style="font-size: 14px;">' + message + '</span>';
        errorHtml += '</div>';
        errorHtml += '</div>';
        
        // Insertar mensaje después del formulario
        $('form').after(errorHtml);
        
        // Scroll al mensaje
        $('html, body').animate({
            scrollTop: $('.bcv-validation-error').offset().top - 100
        }, 500);
        
        // Auto-ocultar después de 8 segundos
        setTimeout(function() {
            $('.bcv-validation-error').fadeOut(500, function() {
                $(this).remove();
            });
        }, 8000);
    }
    
    // ===== PRUEBA DE SCRAPING DETALLADA =====
    $('#test-scraping').on('click', function() {
        var $button = $(this);
        var $result = $('#test-result');
        
        $button.prop('disabled', true).text('🔍 Probando...');
        $result.show().html('<div style="color: #666;">🔄 Conectando con el BCV...</div>');
        
        $.post(bcv_ajax.ajax_url, {
            action: 'bcv_test_scraping',
            nonce: bcv_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                var resultHtml = '<div style="color: #28a745; font-weight: bold;">✅ Scraping Exitoso</div>';
                resultHtml += '<div style="margin-top: 10px; font-size: 14px;">';
                resultHtml += '<strong>Tipo de cambio:</strong> ' + response.data.price + '<br>';
                resultHtml += '<strong>Estado:</strong> ' + response.data.status + '<br>';
                resultHtml += '<strong>Mensaje:</strong> ' + response.data.message;
                resultHtml += '</div>';
                $result.html(resultHtml);
            } else {
                $result.html('<div style="color: #dc3545; font-weight: bold;">❌ Error en Scraping</div><div style="margin-top: 10px;">' + response.data + '</div>');
            }
        })
        .fail(function() {
            $result.html('<div style="color: #dc3545; font-weight: bold;">❌ Error de Conexión</div><div style="margin-top: 10px;">No se pudo conectar con el servidor</div>');
        })
        .always(function() {
            $button.prop('disabled', false).text('🔍 Probar Scraping Detallado');
        });
    });
    
    // ===== LIMPIAR CACHÉ =====
    $('#clear-cache').on('click', function() {
        var $button = $(this);
        var $result = $('#test-result');
        
        $button.prop('disabled', true).text('🗑️ Limpiando...');
        $result.show().html('<div style="color: #666;">🔄 Limpiando caché...</div>');
        
        $.post(bcv_ajax.ajax_url, {
            action: 'bcv_clear_cache',
            nonce: bcv_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                $result.html('<div style="color: #28a745; font-weight: bold;">✅ Caché Limpiado</div><div style="margin-top: 10px;">' + response.data.message + '</div>');
            } else {
                $result.html('<div style="color: #dc3545; font-weight: bold;">❌ Error</div><div style="margin-top: 10px;">' + response.data + '</div>');
            }
        })
        .fail(function() {
            $result.html('<div style="color: #dc3545; font-weight: bold;">❌ Error de Conexión</div>');
        })
        .always(function() {
            $button.prop('disabled', false).text('🗑️ Limpiar Caché');
        });
    });
    
    // ===== INICIALIZACIÓN =====
    
    // Ocultar campos personalizados si hay un preset seleccionado
    var presetValue = $('#cron_interval_preset').val();
    if (presetValue && presetValue !== 'custom') {
        $('#time-inputs').hide();
    }
    
    // Auto-ocultar resultados después de 5 segundos
    setTimeout(function() {
        $('.bcv-result').fadeOut();
    }, 5000);
    
});