<?php
/**
 * Archivo de instalación para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función de instalación del plugin
 */
function wvp_install() {
    // Verificar versiones mínimas
    if (!wvp_check_requirements()) {
        return false;
    }
    
    // Crear tablas de base de datos si es necesario
    wvp_create_tables();
    
    // Crear opciones por defecto
    wvp_create_default_options();
    
    // Crear directorios necesarios
    wvp_create_directories();
    
    // Programar tareas cron si es necesario
    wvp_schedule_cron_events();
    
    // Marcar como instalado
    update_option('wvp_installed', true);
    update_option('wvp_install_date', current_time('mysql'));
    update_option('wvp_version', WVP_VERSION);
    
    // Log de instalación
    error_log('WooCommerce Venezuela Pro: Plugin instalado correctamente');
    
    return true;
}

/**
 * Función de desinstalación del plugin
 */
function wvp_uninstall() {
    // Limpiar opciones del plugin
    wvp_cleanup_options();
    
    // Limpiar tablas de base de datos si es necesario
    wvp_cleanup_tables();
    
    // Limpiar archivos temporales
    wvp_cleanup_files();
    
    // Limpiar eventos cron
    wvp_cleanup_cron_events();
    
    // Log de desinstalación
    error_log('WooCommerce Venezuela Pro: Plugin desinstalado correctamente');
}

/**
 * Verificar requisitos del sistema
 * 
 * @return bool True si se cumplen los requisitos
 */
function wvp_check_requirements() {
    global $wp_version;
    
    // Verificar versión de WordPress
    if (version_compare($wp_version, '5.0', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>WooCommerce Venezuela Pro requiere WordPress 5.0 o superior.</p></div>';
        });
        return false;
    }
    
    // Verificar versión de PHP
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>WooCommerce Venezuela Pro requiere PHP 7.4 o superior.</p></div>';
        });
        return false;
    }
    
    // Verificar WooCommerce
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>WooCommerce Venezuela Pro requiere WooCommerce.</p></div>';
        });
        return false;
    }
    
    // Verificar versión de WooCommerce
    if (version_compare(WC()->version, '5.0', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>WooCommerce Venezuela Pro requiere WooCommerce 5.0 o superior.</p></div>';
        });
        return false;
    }
    
    return true;
}

/**
 * Crear tablas de base de datos
 */
function wvp_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Tabla de logs del plugin
    $table_name = $wpdb->prefix . 'wvp_logs';
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        level varchar(20) NOT NULL,
        message text NOT NULL,
        context longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY level (level),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Tabla de estadísticas del plugin
    $table_name = $wpdb->prefix . 'wvp_stats';
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        stat_key varchar(100) NOT NULL,
        stat_value longtext,
        stat_date date NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY stat_key_date (stat_key, stat_date),
        KEY stat_date (stat_date)
    ) $charset_collate;";
    
    dbDelta($sql);
}

/**
 * Crear opciones por defecto
 */
function wvp_create_default_options() {
    $default_options = array(
        'version' => WVP_VERSION,
        'installed' => true,
        'install_date' => current_time('mysql'),
        'onboarding_completed' => false,
        'show_ves_reference' => true,
        'show_igtf' => true,
        'igtf_rate' => 3.0,
        'price_format' => '(Ref. %s Bs.)',
        'require_cedula_rif' => true,
        'cedula_rif_placeholder' => 'V-12345678 o J-12345678-9',
        'debug_mode' => false,
        'cache_duration' => 300,
        'currency_position' => 'after',
        'decimal_places' => 2,
        'thousand_separator' => '.',
        'decimal_separator' => ',',
        'igtf_description' => 'Impuesto al Gran Movimiento de Transacciones Financieras'
    );
    
    update_option('wvp_settings', $default_options);
}

/**
 * Crear directorios necesarios
 */
function wvp_create_directories() {
    $upload_dir = wp_upload_dir();
    $wvp_dir = $upload_dir['basedir'] . '/wvp';
    
    // Crear directorio principal
    if (!file_exists($wvp_dir)) {
        wp_mkdir_p($wvp_dir);
    }
    
    // Crear subdirectorios
    $subdirs = array('logs', 'cache', 'backups', 'exports');
    
    foreach ($subdirs as $subdir) {
        $dir = $wvp_dir . '/' . $subdir;
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        
        // Crear archivo .htaccess para seguridad
        $htaccess_file = $dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Order Deny,Allow\nDeny from all");
        }
    }
}

/**
 * Programar eventos cron
 */
function wvp_schedule_cron_events() {
    // Evento para limpiar logs antiguos
    if (!wp_next_scheduled('wvp_cleanup_logs')) {
        wp_schedule_event(time(), 'daily', 'wvp_cleanup_logs');
    }
    
    // Evento para generar estadísticas
    if (!wp_next_scheduled('wvp_generate_stats')) {
        wp_schedule_event(time(), 'daily', 'wvp_generate_stats');
    }
    
    // Evento para limpiar cache
    if (!wp_next_scheduled('wvp_cleanup_cache')) {
        wp_schedule_event(time(), 'hourly', 'wvp_cleanup_cache');
    }
}

/**
 * Limpiar opciones del plugin
 */
function wvp_cleanup_options() {
    $options_to_remove = array(
        'wvp_settings',
        'wvp_installed',
        'wvp_install_date',
        'wvp_version',
        'wvp_onboarding_completed',
        'wvp_onboarding_date'
    );
    
    foreach ($options_to_remove as $option) {
        delete_option($option);
    }
}

/**
 * Limpiar tablas de base de datos
 */
function wvp_cleanup_tables() {
    global $wpdb;
    
    $tables_to_remove = array(
        $wpdb->prefix . 'wvp_logs',
        $wpdb->prefix . 'wvp_stats'
    );
    
    foreach ($tables_to_remove as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}

/**
 * Limpiar archivos temporales
 */
function wvp_cleanup_files() {
    $upload_dir = wp_upload_dir();
    $wvp_dir = $upload_dir['basedir'] . '/wvp';
    
    if (file_exists($wvp_dir)) {
        wvp_recursive_rmdir($wvp_dir);
    }
}

/**
 * Limpiar eventos cron
 */
function wvp_cleanup_cron_events() {
    $cron_events = array(
        'wvp_cleanup_logs',
        'wvp_generate_stats',
        'wvp_cleanup_cache'
    );
    
    foreach ($cron_events as $event) {
        wp_clear_scheduled_hook($event);
    }
}

/**
 * Eliminar directorio recursivamente
 * 
 * @param string $dir Directorio a eliminar
 */
function wvp_recursive_rmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) {
                    wvp_recursive_rmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rmdir($dir);
    }
}

/**
 * Verificar si el plugin está instalado
 * 
 * @return bool True si está instalado
 */
function wvp_is_installed() {
    return get_option('wvp_installed', false);
}

/**
 * Obtener versión instalada
 * 
 * @return string Versión instalada
 */
function wvp_get_installed_version() {
    return get_option('wvp_version', '0.0.0');
}

/**
 * Verificar si necesita actualización
 * 
 * @return bool True si necesita actualización
 */
function wvp_needs_update() {
    $installed_version = wvp_get_installed_version();
    return version_compare($installed_version, WVP_VERSION, '<');
}

/**
 * Actualizar plugin
 */
function wvp_update() {
    if (!wvp_needs_update()) {
        return;
    }
    
    $installed_version = wvp_get_installed_version();
    
    // Actualizar a versión 1.0.0
    if (version_compare($installed_version, '1.0.0', '<')) {
        wvp_update_to_1_0_0();
    }
    
    // Actualizar versión
    update_option('wvp_version', WVP_VERSION);
    update_option('wvp_update_date', current_time('mysql'));
    
    // Log de actualización
    error_log("WooCommerce Venezuela Pro: Actualizado de $installed_version a " . WVP_VERSION);
}

/**
 * Actualizar a versión 1.0.0
 */
function wvp_update_to_1_0_0() {
    // Crear tablas si no existen
    wvp_create_tables();
    
    // Crear directorios si no existen
    wvp_create_directories();
    
    // Programar eventos cron si no están programados
    wvp_schedule_cron_events();
}
