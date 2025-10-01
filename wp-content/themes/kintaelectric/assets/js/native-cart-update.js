/**
 * Native Cart Update
 * Usa los eventos nativos de WooCommerce para actualizar el carrito
 */
jQuery(document).ready(function($) {
    'use strict';
    
    
    // Función para actualizar usando fragments de WooCommerce (más eficiente)
    function updateFromFragments(fragments) {
        if (fragments) {
            // Actualizar contador si está en fragments
            if (fragments['.cart-items-count']) {
                $('.cart-items-count').html(fragments['.cart-items-count']);
            }
            // Actualizar total si está en fragments
            if (fragments['.cart-items-total-price']) {
                $('.cart-items-total-price').html(fragments['.cart-items-total-price']);
            }
            // Actualizar mini carrito si está en fragments
            if (fragments['.widget_shopping_cart_content']) {
                $('.widget_shopping_cart_content').html(fragments['.widget_shopping_cart_content']);
            }
        }
    }
    
    // Función para actualizar el contador y total del carrito via AJAX
    function updateCartDisplay() {
        $.post(cart_ajax.ajax_url, {
            action: 'kintaelectric_get_cart_count',
            nonce: cart_ajax.nonce
        }, function(response) {
            if (response.success) {
                $('.cart-items-count').text(response.data.count);
                $('.cart-items-total-price').html(response.data.total);
            }
        }).fail(function() {
            // AJAX failed
        });
    }
    
    // Actualizar al cargar la página
    updateCartDisplay();
    
    // Eventos nativos de WooCommerce - usar fragments primero
    $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        updateFromFragments(fragments);
        updateCartDisplay(); // Fallback
    });
    
    $(document.body).on('removed_from_cart', function(event, fragments, cart_hash, $button) {
        updateFromFragments(fragments);
        updateCartDisplay(); // Fallback
    });
    
    $(document.body).on('updated_cart_totals', function(event, fragments, cart_hash) {
        updateFromFragments(fragments);
        updateCartDisplay(); // Fallback
    });
    
    // Detectar clics en botones de eliminar del mini carrito
    $(document.body).on('click', '.remove_from_cart_button', function(e) {
        // No prevenir el comportamiento por defecto
        setTimeout(function() {
            updateCartDisplay();
        }, 500);
    });
    
    // Detectar cambios en cantidades (solo fuera de páginas de carrito)
    if (!$('body').hasClass('woocommerce-cart')) {
        $(document.body).on('change', 'input.qty', function() {
            setTimeout(updateCartDisplay, 1000);
        });
    }
});
