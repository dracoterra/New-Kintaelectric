<?php
/**
 * Script para arreglar la detección de contexto
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Hook para forzar contexto correcto en shop
add_action('woocommerce_before_shop_loop', function() {
    // Forzar que el contexto sea shop_loop
    add_filter('wvp_current_context', function($context) {
        if (is_shop() || is_product_category() || is_product_tag()) {
            return 'shop_loop';
        }
        return $context;
    });
});

// Hook para forzar contexto correcto en productos individuales
add_action('woocommerce_single_product_summary', function() {
    add_filter('wvp_current_context', function($context) {
        if (is_product()) {
            return 'single_product';
        }
        return $context;
    });
});

// Modificar la función de detección de contexto
add_filter('wvp_current_context', function($context) {
    // Si ya tenemos un contexto válido, usarlo
    if (in_array($context, ['single_product', 'shop_loop', 'cart', 'checkout'])) {
        return $context;
    }
    
    // Detectar contexto de WooCommerce
    if (is_product()) {
        return 'single_product';
    } elseif (is_shop() || is_product_category() || is_product_tag()) {
        return 'shop_loop';
    } elseif (is_cart()) {
        return 'cart';
    } elseif (is_checkout()) {
        return 'checkout';
    }
    
    return $context;
});

// Función para mostrar información de contexto en admin
add_action('admin_notices', function() {
    if (current_user_can('manage_woocommerce')) {
        $context = WVP_Display_Settings::get_current_context();
        $filtered_context = apply_filters('wvp_current_context', $context);
        
        echo '<div class="notice notice-info">';
        echo '<p><strong>WVP Context Fix:</strong></p>';
        echo '<p>Contexto original: <code>' . esc_html($context) . '</code></p>';
        echo '<p>Contexto filtrado: <code>' . esc_html($filtered_context) . '</code></p>';
        echo '<p>is_shop(): ' . (is_shop() ? 'SÍ' : 'NO') . '</p>';
        echo '<p>is_product_category(): ' . (is_product_category() ? 'SÍ' : 'NO') . '</p>';
        echo '<p>is_product_tag(): ' . (is_product_tag() ? 'SÍ' : 'NO') . '</p>';
        echo '</div>';
    }
});
?>
