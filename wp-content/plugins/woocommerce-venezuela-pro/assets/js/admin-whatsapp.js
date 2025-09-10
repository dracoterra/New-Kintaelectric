jQuery(document).ready(function($) {
    'use strict';
    
    // Manejar botones de WhatsApp en meta box
    $('.wvp-whatsapp-button[data-order-id]').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var orderId = $button.data('order-id');
        var shippingGuide = $('#wvp-shipping-guide-' + orderId).val();
        
        if (!shippingGuide) {
            alert('Por favor ingresa el número de guía de envío');
            return;
        }
        
        // Generar URL con guía de envío
        var baseUrl = $button.attr('href');
        var finalUrl = baseUrl.replace('PLACEHOLDER_GUIDE', encodeURIComponent(shippingGuide));
        
        // Abrir WhatsApp
        window.open(finalUrl, '_blank');
    });
    
    // Validar número de teléfono en tiempo real
    $('.wvp-phone-display').each(function() {
        var phone = $(this).text();
        if (phone && !phone.startsWith('+')) {
            $(this).addClass('wvp-phone-invalid');
            $(this).attr('title', 'Número de teléfono puede necesitar formato internacional');
        }
    });
    
    // Añadir estilos para números inválidos
    $('<style>')
        .prop('type', 'text/css')
        .html('.wvp-phone-invalid { border-left: 3px solid #ff6b6b; background: #ffe0e0; }')
        .appendTo('head');
    
    // Confirmar antes de enviar notificación
    $('.wvp-whatsapp-button').on('click', function(e) {
        var $button = $(this);
        var action = $button.text().toLowerCase();
        
        if (action.includes('notificar')) {
            if (!confirm('¿Estás seguro de que quieres enviar esta notificación por WhatsApp?')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Auto-guardar guía de envío en localStorage
    $('.wvp-shipping-guide').on('input', function() {
        var orderId = $(this).attr('id').replace('wvp-shipping-guide-', '');
        var guide = $(this).val();
        
        if (guide) {
            localStorage.setItem('wvp_shipping_guide_' + orderId, guide);
        } else {
            localStorage.removeItem('wvp_shipping_guide_' + orderId);
        }
    });
    
    // Cargar guía guardada
    $('.wvp-shipping-guide').each(function() {
        var orderId = $(this).attr('id').replace('wvp-shipping-guide-', '');
        var savedGuide = localStorage.getItem('wvp_shipping_guide_' + orderId);
        
        if (savedGuide) {
            $(this).val(savedGuide);
        }
    });
    
    // Mostrar preview del mensaje
    $('.wvp-whatsapp-button').on('mouseenter', function() {
        var $button = $(this);
        var orderId = $button.data('order-id');
        var shippingGuide = $('#wvp-shipping-guide-' + orderId).val() || '{shipping_guide}';
        
        // Crear preview del mensaje
        var preview = $button.attr('data-preview');
        if (preview) {
            preview = preview.replace('{shipping_guide}', shippingGuide);
            
            // Mostrar tooltip con preview
            $button.attr('title', 'Preview: ' + preview);
        }
    });
    
    // Añadir indicador de estado de WhatsApp
    $('.wvp-whatsapp-section').each(function() {
        var $section = $(this);
        var $button = $section.find('.wvp-whatsapp-button');
        
        if ($button.length) {
            $section.append('<div class="wvp-whatsapp-status" style="font-size: 11px; color: #666; margin-top: 5px;">📱 Listo para enviar</div>');
        }
    });
});
