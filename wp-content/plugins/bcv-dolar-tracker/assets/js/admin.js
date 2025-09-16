/**
 * JavaScript para administración del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // ===== TOGGLE DEL CRON =====
    $('#toggle-cron').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $result = $('#cron-toggle-result');
        
        // Deshabilitar botón durante la operación
        $button.prop('disabled', true).text('Procesando...');
        $result.html('');
        
        $.post(bcv_ajax.ajax_url, {
            action: 'bcv_toggle_cron',
            nonce: bcv_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                $result.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
                
                // Actualizar botón y estado
                if (response.data.enabled) {
                    $button.removeClass('button-primary').addClass('button-secondary').text('Desactivar Cron');
                } else {
                    $button.removeClass('button-secondary').addClass('button-primary').text('Activar Cron');
                }
                
                // Actualizar estado visual
                $('.bcv-status-success, .bcv-status-error').removeClass('bcv-status-success bcv-status-error')
                    .addClass('bcv-status-' + (response.data.enabled ? 'success' : 'error'))
                    .text(response.data.status_text);
                
            } else {
                $result.html('<span style="color: red;">✗ ' + response.data + '</span>');
            }
        })
        .fail(function() {
            $result.html('<span style="color: red;">✗ Error de conexión</span>');
        })
        .always(function() {
            $button.prop('disabled', false);
        });
    });
    
    // ===== SELECTOR DE INTERVALOS PREDEFINIDOS =====
    $('#cron_interval_preset').on('change', function() {
        var preset = $(this).val();
        
        if (preset === 'custom') {
            // Mostrar campos personalizados
            $('#cron_hours, #cron_minutes, #cron_seconds').closest('tr').show();
            return;
        }
        
        if (preset === '') {
            return;
        }
        
        // Ocultar campos personalizados
        $('#cron_hours, #cron_minutes, #cron_seconds').closest('tr').hide();
        
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
    $('#cron-settings-form').on('submit', function(e) {
        var hours = parseInt($('#cron_hours').val()) || 0;
        var minutes = parseInt($('#cron_minutes').val()) || 0;
        var seconds = parseInt($('#cron_seconds').val()) || 0;
        
        // Validar que al menos haya 1 minuto
        var totalSeconds = (hours * 3600) + (minutes * 60) + seconds;
        
        if (totalSeconds < 60) {
            e.preventDefault();
            alert('El intervalo mínimo debe ser de 1 minuto (60 segundos).');
            return false;
        }
        
        // Validar rangos
        if (hours < 0 || hours > 24) {
            e.preventDefault();
            alert('Las horas deben estar entre 0 y 24.');
            return false;
        }
        
        if (minutes < 0 || minutes > 59) {
            e.preventDefault();
            alert('Los minutos deben estar entre 0 y 59.');
            return false;
        }
        
        if (seconds < 0 || seconds > 59) {
            e.preventDefault();
            alert('Los segundos deben estar entre 0 y 59.');
            return false;
        }
        
        // Mostrar indicador de guardado
        var $submit = $('#submit');
        var originalText = $submit.val();
        $submit.val('Guardando...').prop('disabled', true);
        
        // Restaurar botón después de 3 segundos
        setTimeout(function() {
            $submit.val(originalText).prop('disabled', false);
        }, 3000);
    });
    
    // ===== PRUEBA DE SCRAPING =====
    $('#test-scraping').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $result = $('#test-result');
        
        // Deshabilitar botón durante la operación
        $button.prop('disabled', true).text('Probando...');
        $result.html('');
        
        $.post(bcv_ajax.ajax_url, {
            action: 'bcv_test_scraping',
            nonce: bcv_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                $result.html('<span style="color: green;">✓ ' + response.message + '</span>');
            } else {
                $result.html('<span style="color: red;">✗ ' + response.message + '</span>');
            }
        })
        .fail(function() {
            $result.html('<span style="color: red;">✗ Error de conexión</span>');
        })
        .always(function() {
            $button.prop('disabled', false).text('Probar Scraping Manual');
        });
    });
    
    // ===== INICIALIZACIÓN =====
    
    // Ocultar campos personalizados si hay un preset seleccionado
    var preset = $('#cron_interval_preset').val();
    if (preset && preset !== 'custom' && preset !== '') {
        $('#cron_hours, #cron_minutes, #cron_seconds').closest('tr').hide();
    }
    
    // Añadir estilos CSS dinámicos
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .bcv-status-success { color: #46b450; font-weight: bold; }
            .bcv-status-error { color: #dc3232; font-weight: bold; }
            .bcv-cron-control { border-left: 4px solid #0073aa; }
            .bcv-panel { background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0; border-radius: 4px; }
            .bcv-panel h3 { margin-top: 0; color: #23282d; }
            .form-table th { width: 200px; }
            .form-table td { padding: 10px 0; }
            .description { display: block; margin-top: 5px; color: #666; font-style: italic; }
        `)
        .appendTo('head');
});