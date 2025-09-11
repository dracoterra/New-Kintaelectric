<?php
/**
 * Archivo de depuración para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función de depuración para verificar el estado del plugin
 */
function wvp_debug_plugin_status() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="notice notice-info">';
    echo '<h3>WooCommerce Venezuela Pro - Estado del Plugin</h3>';
    
    // Verificar si el plugin está activo
    if (class_exists('WooCommerce_Venezuela_Pro')) {
        echo '<p>✅ Plugin principal cargado correctamente</p>';
        
        $plugin = WooCommerce_Venezuela_Pro::get_instance();
        
        // Verificar dependencias
        if (isset($plugin->dependencies)) {
            echo '<p>✅ Clase de dependencias cargada</p>';
            
            $deps_status = $plugin->dependencies->get_dependency_status();
            if ($deps_status['all_dependencies_met']) {
                echo '<p>✅ Todas las dependencias están disponibles</p>';
            } else {
                echo '<p>❌ Faltan dependencias:</p>';
                echo '<ul>';
                foreach ($deps_status['missing'] as $dep) {
                    echo '<li>' . esc_html($dep['name']) . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo '<p>❌ Clase de dependencias no cargada</p>';
        }
        
        // Verificar BCV Integrator
        if (class_exists('WVP_BCV_Integrator')) {
            echo '<p>✅ BCV Integrator disponible</p>';
            
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate && $rate > 0) {
                echo '<p>✅ Tasa BCV disponible: ' . number_format($rate, 2) . ' Bs./USD</p>';
            } else {
                echo '<p>❌ No hay tasa BCV disponible</p>';
            }
        } else {
            echo '<p>❌ BCV Integrator no disponible</p>';
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
        
    } else {
        echo '<p>❌ Plugin principal no cargado</p>';
    }
    
    // Verificar opciones del plugin
    $settings = get_option('wvp_settings', array());
    if (!empty($settings)) {
        echo '<p>✅ Configuración del plugin cargada</p>';
        echo '<pre>' . print_r($settings, true) . '</pre>';
    } else {
        echo '<p>❌ No hay configuración del plugin</p>';
    }
    
    echo '</div>';
}

// Añadir la función de depuración al admin
add_action('admin_notices', 'wvp_debug_plugin_status');
