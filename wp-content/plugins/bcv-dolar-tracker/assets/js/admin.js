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
    
    // ===== VALIDACIÓN DEL FORMULARIO =====
    $('form').on('submit', function(e) {
        var hours = parseInt($('#cron_hours').val()) || 0;
        var minutes = parseInt($('#cron_minutes').val()) || 0;
        var seconds = parseInt($('#cron_seconds').val()) || 0;
        var totalSeconds = (hours * 3600) + (minutes * 60) + seconds;
        
        if (totalSeconds < 60) {
            alert('El intervalo mínimo debe ser de 1 minuto.');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
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