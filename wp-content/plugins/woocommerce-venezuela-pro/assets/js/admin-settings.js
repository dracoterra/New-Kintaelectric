/**
 * JavaScript para la página de ajustes de WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Inicializar funcionalidades de la página de ajustes
    initAdminSettings();
    
    /**
     * Inicializar funcionalidades de la página de ajustes
     */
    function initAdminSettings() {
        // Manejar clics en botones de acción
        $(document).on('click', '.wvp-action-button', function(e) {
            e.preventDefault();
            
            var action = $(this).data('action');
            var button = $(this);
            
            switch (action) {
                case 'test-bcv':
                    testBCVConnection(button);
                    break;
                case 'run-onboarding':
                    runOnboarding(button);
                    break;
                case 'clear-cache':
                    clearCache(button);
                    break;
            }
        });
        
        // Validar formularios
        $('form').on('submit', function() {
            return validateForm($(this));
        });
        
        // Actualizar vista previa del formato de precio
        $('#price_format').on('input', function() {
            updatePriceFormatPreview();
        });
    }
    
    /**
     * Probar conexión con BCV
     */
    function testBCVConnection(button) {
        var originalText = button.text();
        button.prop('disabled', true).text(wvp_admin_settings.i18n.testing);
        
        $.ajax({
            url: wvp_admin_settings.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_test_bcv_connection',
                nonce: wvp_admin_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', wvp_admin_settings.i18n.success + ': ' + response.data.message);
                } else {
                    showNotice('error', wvp_admin_settings.i18n.error + ': ' + response.data.message);
                }
            },
            error: function() {
                showNotice('error', wvp_admin_settings.i18n.error + ': Error de conexión');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    }
    
    /**
     * Ejecutar asistente de configuración
     */
    function runOnboarding(button) {
        var originalText = button.text();
        button.prop('disabled', true).text(wvp_admin_settings.i18n.testing);
        
        $.ajax({
            url: wvp_admin_settings.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_run_onboarding',
                nonce: wvp_admin_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    // Recargar la página después de un breve delay
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showNotice('error', wvp_admin_settings.i18n.error + ': ' + response.data.message);
                }
            },
            error: function() {
                showNotice('error', wvp_admin_settings.i18n.error + ': Error de conexión');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    }
    
    /**
     * Limpiar cache
     */
    function clearCache(button) {
        var originalText = button.text();
        button.prop('disabled', true).text(wvp_admin_settings.i18n.testing);
        
        $.ajax({
            url: wvp_admin_settings.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_clear_cache',
                nonce: wvp_admin_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Cache limpiado correctamente');
                } else {
                    showNotice('error', wvp_admin_settings.i18n.error + ': ' + response.data.message);
                }
            },
            error: function() {
                showNotice('error', wvp_admin_settings.i18n.error + ': Error de conexión');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    }
    
    /**
     * Actualizar vista previa del formato de precio
     */
    function updatePriceFormatPreview() {
        var format = $('#price_format').val();
        var preview = format.replace('%s', '1.234.567,89');
        
        if (!$('#price-format-preview').length) {
            $('#price_format').after('<div id="price-format-preview" style="margin-top: 5px; font-style: italic; color: #666;"></div>');
        }
        
        $('#price-format-preview').text('Vista previa: ' + preview);
    }
    
    /**
     * Validar formulario
     */
    function validateForm(form) {
        var isValid = true;
        var errors = [];
        
        // Validar formato de precio
        var priceFormat = $('#price_format').val();
        if (priceFormat && !priceFormat.includes('%s')) {
            errors.push('El formato de precio debe contener %s como marcador de posición');
            isValid = false;
        }
        
        // Validar tasa de IGTF
        var igtfRate = parseFloat($('#igtf_rate').val());
        if (isNaN(igtfRate) || igtfRate < 0 || igtfRate > 100) {
            errors.push('La tasa de IGTF debe ser un número entre 0 y 100');
            isValid = false;
        }
        
        // Mostrar errores si los hay
        if (!isValid) {
            showNotice('error', 'Errores de validación:<br>• ' + errors.join('<br>• '));
        }
        
        return isValid;
    }
    
    /**
     * Mostrar notificación
     */
    function showNotice(type, message) {
        // Remover notificaciones anteriores
        $('.wvp-notice').remove();
        
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible wvp-notice"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    /**
     * Funciones globales para botones
     */
    window.wvpTestBCVConnection = function() {
        testBCVConnection($('button[onclick="wvpTestBCVConnection()"]'));
    };
    
    window.wvpRunOnboarding = function() {
        runOnboarding($('button[onclick="wvpRunOnboarding()"]'));
    };
    
    window.wvpClearCache = function() {
        clearCache($('button[onclick="wvpClearCache()"]'));
    };
});
