<?php
/**
 * Archivo de prueba para verificar el funcionamiento del plugin
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Función para probar el plugin
function wvp_test_plugin() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="notice notice-info">';
    echo '<h3>WooCommerce Venezuela Pro - Prueba del Plugin</h3>';
    
    // Verificar si el plugin está cargado
    if (class_exists('WooCommerce_Venezuela_Pro')) {
        echo '<p>✅ Plugin principal cargado correctamente</p>';
        
        $plugin = WooCommerce_Venezuela_Pro::get_instance();
        
        // Verificar componentes
        if (isset($plugin->bcv_integrator)) {
            echo '<p>✅ BCV Integrator cargado</p>';
        } else {
            echo '<p>❌ BCV Integrator no cargado</p>';
        }
        
        if (isset($plugin->price_display)) {
            echo '<p>✅ Price Display cargado</p>';
        } else {
            echo '<p>❌ Price Display no cargado</p>';
        }
        
        if (isset($plugin->checkout)) {
            echo '<p>✅ Checkout cargado</p>';
        } else {
            echo '<p>❌ Checkout no cargado</p>';
        }
        
        if (isset($plugin->order_meta)) {
            echo '<p>✅ Order Meta cargado</p>';
        } else {
            echo '<p>❌ Order Meta no cargado</p>';
        }
        
        if (isset($plugin->admin_settings)) {
            echo '<p>✅ Admin Settings cargado</p>';
        } else {
            echo '<p>❌ Admin Settings no cargado</p>';
        }
        
    } else {
        echo '<p>❌ Plugin principal no cargado</p>';
    }
    
    // Verificar WooCommerce
    if (class_exists('WooCommerce')) {
        echo '<p>✅ WooCommerce disponible (v' . WC()->version . ')</p>';
    } else {
        echo '<p>❌ WooCommerce no disponible</p>';
    }
    
    // Verificar BCV Dólar Tracker
    if (is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
        echo '<p>✅ BCV Dólar Tracker activo</p>';
        
        // Verificar tabla de BCV
        global $wpdb;
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if ($table_exists) {
            echo '<p>✅ Tabla BCV existe</p>';
            
            $latest_price = $wpdb->get_var("SELECT precio FROM $table_name ORDER BY datatime DESC LIMIT 1");
            if ($latest_price) {
                echo '<p>✅ Último precio BCV: ' . number_format($latest_price, 2) . ' Bs./USD</p>';
            } else {
                echo '<p>❌ No hay datos en la tabla BCV</p>';
            }
        } else {
            echo '<p>❌ Tabla BCV no existe</p>';
        }
    } else {
        echo '<p>❌ BCV Dólar Tracker no activo</p>';
    }
    
    echo '</div>';
}

// Añadir la función de prueba al admin
add_action('admin_notices', 'wvp_test_plugin');