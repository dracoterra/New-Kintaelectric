<?php
/**
 * Uninstall - Limpieza al desinstalar el plugin
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Limpiar datos del plugin al desinstalar
 */
function wvs_uninstall_cleanup() {
    global $wpdb;
    
    // Eliminar tablas de base de datos
    $tables = array(
        $wpdb->prefix . 'wvs_modules',
        $wpdb->prefix . 'wvs_logs',
        $wpdb->prefix . 'wvs_config'
    );
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    
    // Eliminar opciones
    $options = array(
        'wvs_version',
        'wvs_activated_modules',
        'wvs_settings'
    );
    
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // Eliminar transients
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_wvs_%' 
         OR option_name LIKE '_transient_timeout_wvs_%'"
    );
    
    // Eliminar meta de pedidos
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} 
         WHERE meta_key LIKE '_wvs_%'"
    );
    
    // Eliminar meta de usuarios
    $wpdb->query(
        "DELETE FROM {$wpdb->usermeta} 
         WHERE meta_key LIKE 'wvs_%'"
    );
}

// Ejecutar limpieza
wvs_uninstall_cleanup();
