<?php
/**
 * Manual Activation - Activación manual para crear tablas
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función para activar manualmente el plugin y crear tablas
 */
function wvs_manual_activation() {
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos para realizar esta acción.');
    }
    
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Tabla de módulos
    $modules_table = $wpdb->prefix . 'wvs_modules';
    $modules_sql = "CREATE TABLE IF NOT EXISTS $modules_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        module_key varchar(100) NOT NULL,
        module_name varchar(255) NOT NULL,
        module_status varchar(20) DEFAULT 'inactive',
        module_version varchar(20) DEFAULT '1.0.0',
        module_config longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY module_key (module_key),
        KEY module_status (module_status)
    ) $charset_collate;";
    
    // Tabla de logs
    $logs_table = $wpdb->prefix . 'wvs_logs';
    $logs_sql = "CREATE TABLE IF NOT EXISTS $logs_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        level varchar(20) NOT NULL,
        message text NOT NULL,
        context longtext,
        user_id bigint(20),
        ip_address varchar(45),
        PRIMARY KEY (id),
        KEY level (level),
        KEY timestamp (timestamp),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    // Tabla de configuraciones
    $config_table = $wpdb->prefix . 'wvs_config';
    $config_sql = "CREATE TABLE IF NOT EXISTS $config_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        config_key varchar(100) NOT NULL,
        config_value longtext,
        config_type varchar(20) DEFAULT 'string',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY config_key (config_key)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $result1 = dbDelta($modules_sql);
    $result2 = dbDelta($logs_sql);
    $result3 = dbDelta($config_sql);
    
    // Configurar opciones por defecto
    if (!get_option('wvs_version')) {
        update_option('wvs_version', '1.0.0');
    }
    
    if (!get_option('wvs_activated_modules')) {
        update_option('wvs_activated_modules', array('core', 'currency', 'payments'));
    }
    
    if (!get_option('wvs_settings')) {
        update_option('wvs_settings', array(
            'currency_display' => 'dual',
            'default_currency' => 'USD',
            'bcv_integration' => true,
            'igtf_rate' => 3.0,
            'help_system' => true,
            'notifications' => true
        ));
    }
    
    echo '<div class="notice notice-success"><p>Suite Venezuela activado manualmente. Tablas creadas correctamente.</p></div>';
}

// Agregar página de activación manual en admin
add_action('admin_menu', function() {
    add_management_page(
        'Activar Suite Venezuela',
        'Activar Suite Venezuela',
        'manage_options',
        'wvs-manual-activation',
        'wvs_manual_activation'
    );
});
