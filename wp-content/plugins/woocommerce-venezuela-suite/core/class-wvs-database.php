<?php
/**
 * Database - Gestión de base de datos
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestión de base de datos
 */
class WVS_Database {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Suite::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hooks de limpieza
        add_action('wp_scheduled_delete', array($this, 'cleanup_old_data'));
    }
    
    /**
     * Crear tablas necesarias
     */
    public function create_tables() {
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
        dbDelta($modules_sql);
        dbDelta($logs_sql);
        dbDelta($config_sql);
    }
    
    /**
     * Limpiar datos antiguos
     */
    public function cleanup_old_data() {
        // Limpiar logs antiguos
        WVS_Logger::cleanup_old_logs(30);
        
        // Limpiar transients antiguos
        $this->cleanup_old_transients();
    }
    
    /**
     * Limpiar transients antiguos
     */
    private function cleanup_old_transients() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_wvs_%' 
             AND option_name NOT LIKE '_transient_timeout_wvs_%'"
        );
    }
}
