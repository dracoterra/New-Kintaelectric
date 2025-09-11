<?php
/**
 * Archivo de configuración de prueba para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Configuración básica para pruebas
add_action('init', function() {
    // Configurar opciones por defecto si no existen
    $default_settings = array(
        'show_ves_reference' => true,
        'show_igtf' => true,
        'igtf_rate' => 3.0,
        'price_format' => '(Ref. %s Bs.)',
        'require_cedula_rif' => true,
        'cedula_rif_placeholder' => 'V-12345678 o J-12345678-9',
        'debug_mode' => true
    );
    
    $current_settings = get_option('wvp_settings', array());
    $merged_settings = array_merge($default_settings, $current_settings);
    update_option('wvp_settings', $merged_settings);
    
    // Configurar WooCommerce para Venezuela si no está configurado
    if (get_option('woocommerce_currency') !== 'USD') {
        update_option('woocommerce_currency', 'USD');
        update_option('woocommerce_currency_pos', 'right');
        update_option('woocommerce_price_thousand_sep', '.');
        update_option('woocommerce_price_decimal_sep', ',');
        update_option('woocommerce_price_num_decimals', 2);
    }
    
    // Configurar ubicación para Venezuela
    if (get_option('woocommerce_default_country') !== 'VE') {
        update_option('woocommerce_default_country', 'VE');
    }
});

// Función para probar la conexión con BCV
function wvp_test_bcv_connection() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bcv_precio_dolar';
    
    // Verificar si la tabla existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        echo '<div class="notice notice-error"><p>❌ Tabla BCV no existe: ' . $table_name . '</p></div>';
        return;
    }
    
    // Obtener el último precio
    $latest_price = $wpdb->get_var("SELECT precio FROM $table_name ORDER BY datatime DESC LIMIT 1");
    
    if ($latest_price) {
        echo '<div class="notice notice-success"><p>✅ Conexión BCV exitosa. Último precio: ' . number_format($latest_price, 2) . ' Bs./USD</p></div>';
    } else {
        echo '<div class="notice notice-warning"><p>⚠️ Tabla BCV existe pero no hay datos</p></div>';
    }
}

// Añadir la función de prueba al admin
add_action('admin_notices', 'wvp_test_bcv_connection');
