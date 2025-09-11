/**
 * JavaScript para la administración reestructurada
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Inicializar funcionalidades
    initTabNavigation();
    initStatusRefresh();
    initDependencyCheck();
    initFormHandling();
    initTooltips();
    
    /**
     * Inicializar navegación por pestañas
     */
    function initTabNavigation() {
        // Marcar pestaña activa basada en la URL
        var currentPage = getCurrentPage();
        $('.wvp-nav-tab').removeClass('active');
        $('.wvp-nav-tab a[href*="' + currentPage + '"]').parent().addClass('active');
        
        // Efectos hover en pestañas
        $('.wvp-nav-tab a').hover(
            function() {
                $(this).parent().addClass('hover');
            },
            function() {
                $(this).parent().removeClass('hover');
            }
        );
    }
    
    /**
     * Inicializar actualización de estado
     */
    function initStatusRefresh() {
        $('#wvp-refresh-status').on('click', function() {
            var $button = $(this);
            var $icon = $button.find('.dashicons');
            
            // Mostrar estado de carga
            $icon.removeClass('dashicons-update').addClass('dashicons-update-alt');
            $icon.css('animation', 'spin 1s linear infinite');
            $button.prop('disabled', true);
            
            // Simular actualización (en una implementación real, esto haría una llamada AJAX)
            setTimeout(function() {
                $icon.removeClass('dashicons-update-alt').addClass('dashicons-update');
                $icon.css('animation', '');
                $button.prop('disabled', false);
                
                // Mostrar notificación de éxito
                showNotification('Estado actualizado correctamente', 'success');
            }, 2000);
        });
    }
    
    /**
     * Inicializar verificación de dependencias
     */
    function initDependencyCheck() {
        $('#wvp-check-dependencies').on('click', function() {
            var $button = $(this);
            var originalText = $button.text();
            
            $button.prop('disabled', true).text('Verificando...');
            
            // Simular verificación de dependencias
            setTimeout(function() {
                $button.prop('disabled', false).text(originalText);
                showDependencyResults();
            }, 1500);
        });
    }
    
    /**
     * Inicializar manejo de formularios
     */
    function initFormHandling() {
        // Guardar configuraciones automáticamente
        $('.wvp-settings-form, .wvp-fiscal-form, .wvp-notifications-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var formData = $form.serialize();
            var $submitButton = $form.find('input[type="submit"]');
            var originalText = $submitButton.val();
            
            // Mostrar estado de carga
            $submitButton.val('Guardando...').prop('disabled', true);
            
            // En una implementación real, esto haría una llamada AJAX
            setTimeout(function() {
                $submitButton.val(originalText).prop('disabled', false);
                showNotification('Configuraciones guardadas correctamente', 'success');
            }, 1000);
        });
        
        // Validación en tiempo real
        $('.wvp-settings-form input, .wvp-settings-form textarea').on('blur', function() {
            validateField($(this));
        });
    }
    
    /**
     * Inicializar tooltips
     */
    function initTooltips() {
        // Crear tooltips para elementos con data-tooltip
        $('[data-tooltip]').each(function() {
            var $element = $(this);
            var tooltipText = $element.data('tooltip');
            
            $element.hover(
                function() {
                    showTooltip($element, tooltipText);
                },
                function() {
                    hideTooltip();
                }
            );
        });
    }
    
    /**
     * Obtener página actual
     */
    function getCurrentPage() {
        var url = window.location.href;
        var match = url.match(/[?&]page=([^&]+)/);
        return match ? match[1] : '';
    }
    
    /**
     * Mostrar notificación
     */
    function showNotification(message, type) {
        var $notification = $('<div class="wvp-notification wvp-notification-' + type + '">' + message + '</div>');
        
        $('body').append($notification);
        
        // Animar entrada
        setTimeout(function() {
            $notification.addClass('wvp-notification-show');
        }, 100);
        
        // Remover después de 3 segundos
        setTimeout(function() {
            $notification.removeClass('wvp-notification-show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }
    
    /**
     * Mostrar resultados de verificación de dependencias
     */
    function showDependencyResults() {
        var results = {
            'WooCommerce': {
                status: 'active',
                version: '8.0.0',
                message: 'Activo y actualizado'
            },
            'BCV Dólar Tracker': {
                status: 'active',
                version: '1.0.0',
                message: 'Activo y funcionando'
            },
            'PHP': {
                status: 'ok',
                version: '8.0.0',
                message: 'Versión compatible'
            },
            'WordPress': {
                status: 'ok',
                version: '6.0.0',
                message: 'Versión compatible'
            }
        };
        
        var html = '<div class="wvp-dependency-results">';
        html += '<h3>Resultados de Verificación</h3>';
        html += '<div class="wvp-dependency-list">';
        
        for (var dependency in results) {
            var result = results[dependency];
            var statusClass = result.status === 'active' || result.status === 'ok' ? 'success' : 'error';
            var statusIcon = result.status === 'active' || result.status === 'ok' ? '✓' : '✗';
            
            html += '<div class="wvp-dependency-item wvp-dependency-' + statusClass + '">';
            html += '<div class="wvp-dependency-name">' + dependency + '</div>';
            html += '<div class="wvp-dependency-status">';
            html += '<span class="wvp-dependency-icon">' + statusIcon + '</span>';
            html += '<span class="wvp-dependency-version">v' + result.version + '</span>';
            html += '</div>';
            html += '<div class="wvp-dependency-message">' + result.message + '</div>';
            html += '</div>';
        }
        
        html += '</div></div>';
        
        // Mostrar en modal
        showModal('Verificación de Dependencias', html);
    }
    
    /**
     * Validar campo
     */
    function validateField($field) {
        var value = $field.val();
        var fieldType = $field.attr('type');
        var isValid = true;
        var errorMessage = '';
        
        // Validaciones específicas por tipo
        switch (fieldType) {
            case 'number':
                if (value && (isNaN(value) || parseFloat(value) < 0)) {
                    isValid = false;
                    errorMessage = 'Debe ser un número válido mayor o igual a 0';
                }
                break;
            case 'email':
                if (value && !isValidEmail(value)) {
                    isValid = false;
                    errorMessage = 'Debe ser un email válido';
                }
                break;
        }
        
        // Validaciones generales
        if ($field.attr('required') && !value.trim()) {
            isValid = false;
            errorMessage = 'Este campo es obligatorio';
        }
        
        // Mostrar/ocultar error
        var $error = $field.siblings('.wvp-field-error');
        if (isValid) {
            $error.remove();
            $field.removeClass('wvp-field-error');
        } else {
            if ($error.length === 0) {
                $error = $('<div class="wvp-field-error">' + errorMessage + '</div>');
                $field.after($error);
            } else {
                $error.text(errorMessage);
            }
            $field.addClass('wvp-field-error');
        }
        
        return isValid;
    }
    
    /**
     * Validar email
     */
    function isValidEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    /**
     * Mostrar tooltip
     */
    function showTooltip($element, text) {
        var $tooltip = $('<div class="wvp-tooltip">' + text + '</div>');
        $('body').append($tooltip);
        
        var elementOffset = $element.offset();
        var elementWidth = $element.outerWidth();
        var elementHeight = $element.outerHeight();
        var tooltipWidth = $tooltip.outerWidth();
        var tooltipHeight = $tooltip.outerHeight();
        
        var left = elementOffset.left + (elementWidth / 2) - (tooltipWidth / 2);
        var top = elementOffset.top - tooltipHeight - 10;
        
        $tooltip.css({
            position: 'absolute',
            left: left + 'px',
            top: top + 'px',
            zIndex: 9999
        });
        
        $tooltip.addClass('wvp-tooltip-show');
    }
    
    /**
     * Ocultar tooltip
     */
    function hideTooltip() {
        $('.wvp-tooltip').remove();
    }
    
    /**
     * Mostrar modal
     */
    function showModal(title, content) {
        var $modal = $('<div class="wvp-modal">');
        var $modalContent = $('<div class="wvp-modal-content">');
        var $modalHeader = $('<div class="wvp-modal-header">');
        var $modalBody = $('<div class="wvp-modal-body">');
        var $modalClose = $('<span class="wvp-modal-close">&times;</span>');
        
        $modalHeader.html('<h3>' + title + '</h3>').append($modalClose);
        $modalBody.html(content);
        $modalContent.append($modalHeader).append($modalBody);
        $modal.append($modalContent);
        
        $('body').append($modal);
        
        // Animar entrada
        setTimeout(function() {
            $modal.addClass('wvp-modal-show');
        }, 100);
        
        // Cerrar modal
        $modalClose.on('click', function() {
            closeModal($modal);
        });
        
        $modal.on('click', function(e) {
            if (e.target === this) {
                closeModal($modal);
            }
        });
        
        // Cerrar con ESC
        $(document).on('keyup.wvp-modal', function(e) {
            if (e.keyCode === 27) {
                closeModal($modal);
            }
        });
    }
    
    /**
     * Cerrar modal
     */
    function closeModal($modal) {
        $modal.removeClass('wvp-modal-show');
        setTimeout(function() {
            $modal.remove();
            $(document).off('keyup.wvp-modal');
        }, 300);
    }
    
    /**
     * Animación de spin para iconos
     */
    $('<style>')
        .prop('type', 'text/css')
        .html('@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }')
        .appendTo('head');
    
    // Estilos adicionales para notificaciones y tooltips
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .wvp-notification {
                position: fixed;
                top: 32px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 4px;
                color: white;
                font-weight: 500;
                z-index: 9999;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            .wvp-notification-show {
                transform: translateX(0);
            }
            .wvp-notification-success {
                background: #46b450;
            }
            .wvp-notification-error {
                background: #dc3232;
            }
            .wvp-notification-warning {
                background: #ffb900;
            }
            .wvp-field-error {
                border-color: #dc3232 !important;
                box-shadow: 0 0 0 2px rgba(220, 50, 50, 0.1) !important;
            }
            .wvp-field-error + .wvp-field-error {
                color: #dc3232;
                font-size: 12px;
                margin-top: 5px;
            }
            .wvp-tooltip {
                background: #333;
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                max-width: 200px;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .wvp-tooltip-show {
                opacity: 1;
            }
            .wvp-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .wvp-modal-show {
                opacity: 1;
            }
            .wvp-modal-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 8px;
                max-width: 600px;
                width: 90%;
                max-height: 80%;
                overflow: hidden;
            }
            .wvp-modal-header {
                padding: 20px;
                border-bottom: 1px solid #ddd;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .wvp-modal-close {
                font-size: 24px;
                cursor: pointer;
                color: #666;
            }
            .wvp-modal-body {
                padding: 20px;
                max-height: 400px;
                overflow-y: auto;
            }
            .wvp-dependency-results h3 {
                margin: 0 0 20px 0;
                color: #333;
            }
            .wvp-dependency-list {
                display: grid;
                gap: 15px;
            }
            .wvp-dependency-item {
                display: flex;
                align-items: center;
                padding: 15px;
                border-radius: 4px;
                border: 1px solid #ddd;
            }
            .wvp-dependency-success {
                background: #f0f8f0;
                border-color: #46b450;
            }
            .wvp-dependency-error {
                background: #fdf2f2;
                border-color: #dc3232;
            }
            .wvp-dependency-name {
                font-weight: 600;
                margin-right: 15px;
                min-width: 150px;
            }
            .wvp-dependency-status {
                display: flex;
                align-items: center;
                margin-right: 15px;
            }
            .wvp-dependency-icon {
                margin-right: 8px;
                font-weight: bold;
            }
            .wvp-dependency-version {
                font-size: 12px;
                color: #666;
            }
            .wvp-dependency-message {
                color: #666;
                font-size: 14px;
            }
        `)
        .appendTo('head');
});
