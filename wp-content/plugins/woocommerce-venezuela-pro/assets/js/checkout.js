/**
 * JavaScript para el checkout de WooCommerce Venezuela Pro
 * Compatible con WooCommerce v10+ (checkout blocks y clásico)
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Función para debug del DOM
    function debugDOM() {
        console.log('WVP: === DEBUG DOM ===');
        console.log('WVP: Elementos .wc-block-checkout:', $('.wc-block-checkout').length);
        console.log('WVP: Elementos .wp-block-woocommerce-checkout:', $('.wp-block-woocommerce-checkout').length);
        console.log('WVP: Elementos .wc-block-checkout__billing-address:', $('.wc-block-checkout__billing-address').length);
        console.log('WVP: Elementos .wp-block-woocommerce-checkout-billing-address-block:', $('.wp-block-woocommerce-checkout-billing-address-block').length);
        console.log('WVP: Elementos .wc-block-components-address-form:', $('.wc-block-components-address-form').length);
        console.log('WVP: Elementos .billing-address:', $('.billing-address').length);
        console.log('WVP: Elementos input[type="tel"]:', $('input[type="tel"]').length);
        console.log('WVP: Elementos [name="billing_phone"]:', $('[name="billing_phone"]').length);
        console.log('WVP: Elementos #billing_phone_field:', $('#billing_phone_field').length);
        console.log('WVP: === FIN DEBUG ===');
    }
    
    // Función para añadir el campo de Cédula/RIF (DESHABILITADA - usando campo nativo)
    function addCedulaRifField() {
        return; // Deshabilitado - usando campo nativo de WooCommerce Blocks
    }
    
    // Función para forzar placeholder en el campo de Cédula/RIF
    function forceCedulaRifPlaceholder() {
        var cedulaField = $('input[name*="cedula_rif"]');
        if (cedulaField.length > 0 && !cedulaField.attr('placeholder')) {
            cedulaField.attr('placeholder', 'V-12345678 o E-12345678');
            console.log('WVP: Placeholder forzado en campo cédula/RIF');
        }
    }
    
    // Ejecutar cuando el DOM esté listo
    forceCedulaRifPlaceholder();
    
    // Ejecutar cuando se actualice el checkout
    $(document.body).on('updated_checkout', function() {
        forceCedulaRifPlaceholder();
    });
    
    // Ejecutar cuando se carguen los bloques
    $(document.body).on('wc-blocks_checkout_updated', function() {
        forceCedulaRifPlaceholder();
    });
    
    // Ejecutar periódicamente para asegurar que se aplique
    setInterval(function() {
        forceCedulaRifPlaceholder();
    }, 1000);
    
    // Función para añadir el campo de Cédula/RIF (DESHABILITADA - usando campo nativo)
    function addCedulaRifField() {
        // Verificar si el campo ya existe
        if ($('#billing_cedula_rif_field').length > 0) {
            return;
        }
        
        // Debug del DOM en la primera ejecución
        if (!window.wvp_debug_done) {
            debugDOM();
            window.wvp_debug_done = true;
        }
        
        var cedulaField = '';
        var targetElement = null;
        
        // Detectar si estamos en checkout blocks o clásico
        if ($('.wc-block-checkout').length > 0 || $('.wp-block-woocommerce-checkout').length > 0) {
            // Checkout blocks (WooCommerce v10+)
            console.log('WVP: Detectado checkout blocks');
            
            // Buscar el contenedor de facturación en bloques (múltiples selectores)
            var billingContainer = $('.wc-block-checkout__billing-address, .wp-block-woocommerce-checkout-billing-address-block, .wc-block-components-address-form, .billing-address');
            
            if (billingContainer.length === 0) {
                // Si no encontramos el contenedor específico, buscar en todo el checkout
                billingContainer = $('.wc-block-checkout, .wp-block-woocommerce-checkout');
            }
            
            if (billingContainer.length > 0) {
                console.log('WVP: Contenedor de facturación encontrado:', billingContainer[0].className);
                
                // Buscar el campo de teléfono en bloques (múltiples selectores)
                var phoneField = billingContainer.find('#billing_phone_field, [name="billing_phone"], input[type="tel"]').closest('.form-row, .wc-block-form-field, .wc-block-components-form-field, .wp-block-woocommerce-checkout-billing-address-block');
                
                if (phoneField.length === 0) {
                    // Si no encontramos el campo de teléfono, buscar el último campo de facturación
                    phoneField = billingContainer.find('input[type="text"], input[type="email"]').last().closest('.form-row, .wc-block-form-field, .wc-block-components-form-field');
                }
                
                if (phoneField.length === 0) {
                    // Si aún no encontramos nada, buscar cualquier campo de entrada
                    phoneField = billingContainer.find('input').last().closest('div');
                }
                
                console.log('WVP: Campo objetivo encontrado:', phoneField.length > 0 ? phoneField[0].className : 'Ninguno');
                
                if (phoneField.length > 0) {
                    cedulaField = '<div class="wc-block-components-form-field wc-block-components-form-field--text billing_cedula_rif_field" id="billing_cedula_rif_field" data-priority="25">' +
                        '<label for="billing_cedula_rif" class="wc-block-components-form-field__label">Cédula o RIF <abbr class="required" title="obligatorio">*</abbr></label>' +
                        '<div class="wc-block-components-form-field__input-wrapper">' +
                        '<input type="text" class="wc-block-components-form-field__input" name="billing_cedula_rif" id="billing_cedula_rif" placeholder="V-12345678 o J-12345678-9" value="" autocomplete="cedula" data-priority="25" />' +
                        '</div>' +
                        '</div>';
                    targetElement = phoneField;
                }
            } else {
                console.log('WVP: No se encontró contenedor de facturación');
            }
        } else {
            // Checkout clásico
            console.log('WVP: Detectado checkout clásico');
            
            // Buscar el campo de teléfono para insertar después
            var phoneField = $('#billing_phone_field');
            if (phoneField.length > 0) {
                cedulaField = '<p class="form-row form-row-wide validate-required woocommerce-validated" id="billing_cedula_rif_field" data-priority="25">' +
                    '<label for="billing_cedula_rif" class="">Cédula o RIF <abbr class="required" title="obligatorio">*</abbr></label>' +
                    '<span class="woocommerce-input-wrapper">' +
                    '<input type="text" class="input-text" name="billing_cedula_rif" id="billing_cedula_rif" placeholder="V-12345678 o J-12345678-9" value="" autocomplete="cedula" data-priority="25" />' +
                    '</span>' +
                    '</p>';
                targetElement = phoneField;
            }
        }
        
        // Insertar el campo si se encontró un elemento objetivo
        if (cedulaField && targetElement && targetElement.length > 0) {
            targetElement.after(cedulaField);
            console.log('WVP: Campo de Cédula/RIF añadido con JavaScript');
        } else {
            console.log('WVP: No se pudo encontrar el elemento objetivo para insertar el campo');
        }
    }
    
    // Función para asegurar que el campo se envíe correctamente
    function ensureFieldSubmission() {
        // Interceptar el envío del formulario para asegurar que el campo se incluya
        $(document.body).on('checkout_place_order', function() {
            var cedulaValue = $('#billing_cedula_rif').val();
            if (cedulaValue) {
                // Asegurar que el campo esté en el formulario
                if ($('input[name="billing_cedula_rif"]').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'billing_cedula_rif',
                        value: cedulaValue
                    }).appendTo('form.checkout');
                }
                console.log('WVP: Campo cédula/RIF enviado:', cedulaValue);
            }
        });
        
        // Para WooCommerce Blocks, interceptar el envío AJAX
        $(document.body).on('wc-blocks_checkout_updated', function() {
            var cedulaValue = $('#billing_cedula_rif').val();
            if (cedulaValue) {
                console.log('WVP: Campo cédula/RIF actualizado en blocks:', cedulaValue);
            }
        });
        
        // Interceptar el envío del formulario de blocks
        $(document.body).on('submit', 'form', function() {
            var cedulaValue = $('#billing_cedula_rif').val();
            if (cedulaValue) {
                // Asegurar que el campo esté en el formulario
                if ($('input[name="billing_cedula_rif"]').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'billing_cedula_rif',
                        value: cedulaValue
                    }).appendTo(this);
                }
                console.log('WVP: Campo cédula/RIF enviado en submit:', cedulaValue);
            }
        });
        
        // Interceptar el envío AJAX de blocks
        $(document.body).on('click', 'button[type="submit"]', function() {
            var cedulaValue = $('#billing_cedula_rif').val();
            if (cedulaValue) {
                // Asegurar que el campo esté en el formulario
                if ($('input[name="billing_cedula_rif"]').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'billing_cedula_rif',
                        value: cedulaValue
                    }).appendTo('form');
                }
                console.log('WVP: Campo cédula/RIF enviado en click:', cedulaValue);
            }
        });
        
        // Interceptar el envío del formulario de blocks usando el evento nativo
        $(document.body).on('submit', 'form[data-block-name="woocommerce/checkout"]', function(e) {
            var cedulaValue = $('#billing_cedula_rif').val();
            if (cedulaValue) {
                // Asegurar que el campo esté en el formulario
                if ($('input[name="billing_cedula_rif"]').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'billing_cedula_rif',
                        value: cedulaValue
                    }).appendTo(this);
                }
                console.log('WVP: Campo cédula/RIF enviado en submit blocks:', cedulaValue);
            }
        });
        
        // Interceptar el envío del formulario de blocks usando el evento de WooCommerce
        $(document.body).on('checkout_place_order', function() {
            var cedulaValue = $('#billing_cedula_rif').val();
            if (cedulaValue) {
                // Asegurar que el campo esté en el formulario
                if ($('input[name="billing_cedula_rif"]').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'billing_cedula_rif',
                        value: cedulaValue
                    }).appendTo('form');
                }
                console.log('WVP: Campo cédula/RIF enviado en checkout_place_order:', cedulaValue);
            }
        });
    }
    
    // Ejecutar inmediatamente
    addCedulaRifField();
    ensureFieldSubmission();
    
    // Ejecutar después de que se actualice el checkout
    $(document.body).on('updated_checkout', function() {
        addCedulaRifField();
    });
    
    // Ejecutar después de un pequeño delay para asegurar que el DOM esté listo
    setTimeout(function() {
        addCedulaRifField();
    }, 1000);
    
    // Ejecutar cuando se cargue la página
    $(window).on('load', function() {
        addCedulaRifField();
    });
    
    // Ejecutar cuando se actualice el DOM (para bloques de React)
    if (window.MutationObserver) {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    setTimeout(addCedulaRifField, 100);
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});