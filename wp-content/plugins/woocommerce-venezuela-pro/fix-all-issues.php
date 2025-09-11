<?php
/**
 * Script completo para corregir todos los problemas del plugin
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    if (php_sapi_name() !== 'cli') {
        exit('Acceso directo no permitido');
    }
    
    // Cargar WordPress
    require_once dirname(__FILE__) . '/../../wp-config.php';
    require_once dirname(__FILE__) . '/../../wp-load.php';
}

echo "🔧 CORRIGIENDO TODOS LOS PROBLEMAS DEL PLUGIN\n";
echo "==============================================\n\n";

/**
 * 1. Crear tablas de base de datos
 */
function create_database_tables() {
    global $wpdb;
    
    echo "📊 Creando tablas de base de datos...\n";
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Tabla de errores
    $error_table = $wpdb->prefix . 'wvp_error_logs';
    $error_sql = "CREATE TABLE IF NOT EXISTS $error_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        timestamp datetime NOT NULL,
        level varchar(20) NOT NULL,
        message text NOT NULL,
        context longtext,
        user_id int(11) DEFAULT NULL,
        url varchar(255) DEFAULT NULL,
        ip varchar(45) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY timestamp (timestamp),
        KEY level (level),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    // Tabla de seguridad
    $security_table = $wpdb->prefix . 'wvp_security_logs';
    $security_sql = "CREATE TABLE IF NOT EXISTS $security_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        event_type varchar(100) NOT NULL,
        message text NOT NULL,
        context longtext,
        user_id bigint(20),
        ip_address varchar(45),
        PRIMARY KEY (id),
        KEY event_type (event_type),
        KEY timestamp (timestamp),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $result1 = dbDelta($error_sql);
    $result2 = dbDelta($security_sql);
    
    // Verificar creación
    $error_exists = $wpdb->get_var("SHOW TABLES LIKE '$error_table'") == $error_table;
    $security_exists = $wpdb->get_var("SHOW TABLES LIKE '$security_table'") == $security_table;
    
    if ($error_exists && $security_exists) {
        echo "✅ Tablas creadas correctamente\n";
        return true;
    } else {
        echo "❌ Error al crear tablas\n";
        echo "Error: " . $wpdb->last_error . "\n";
        return false;
    }
}

/**
 * 2. Configurar opciones por defecto
 */
function set_default_options() {
    echo "⚙️ Configurando opciones por defecto...\n";
    
    // Configuraciones de IGTF
    if (get_option('wvp_igtf_enabled') === false) {
        update_option('wvp_igtf_enabled', 'yes');
    }
    
    if (get_option('wvp_show_igtf') === false) {
        update_option('wvp_show_igtf', '1');
    }
    
    if (get_option('wvp_igtf_rate') === false) {
        update_option('wvp_igtf_rate', '3.0');
    }
    
    // Configuraciones de precios
    if (get_option('wvp_price_reference_format') === false) {
        update_option('wvp_price_reference_format', '(Ref. %s Bs.)');
    }
    
    // Configuraciones de apariencia
    if (get_option('wvp_display_style') === false) {
        update_option('wvp_display_style', 'minimal');
    }
    
    echo "✅ Opciones configuradas\n";
    return true;
}

/**
 * 3. Limpiar caché
 */
function clear_cache() {
    echo "🧹 Limpiando caché...\n";
    
    // Limpiar transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%'");
    
    // Limpiar caché de WordPress si está disponible
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    echo "✅ Caché limpiado\n";
    return true;
}

/**
 * 4. Verificar estado del plugin
 */
function verify_plugin_status() {
    echo "🔍 Verificando estado del plugin...\n";
    
    // Verificar que WooCommerce esté activo
    if (!class_exists('WooCommerce')) {
        echo "❌ WooCommerce no está activo\n";
        return false;
    }
    
    // Verificar versión de WooCommerce
    $wc_version = WC()->version;
    echo "✅ WooCommerce versión: $wc_version\n";
    
    // Verificar BCV Dólar Tracker
    if (!is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
        echo "⚠️ BCV Dólar Tracker no está activo (recomendado)\n";
    } else {
        echo "✅ BCV Dólar Tracker activo\n";
    }
    
    return true;
}

/**
 * 5. Mostrar resumen final
 */
function show_summary() {
    echo "\n📋 RESUMEN DE CORRECCIONES:\n";
    echo "============================\n";
    echo "✅ Tablas de base de datos creadas\n";
    echo "✅ Opciones por defecto configuradas\n";
    echo "✅ Caché limpiado\n";
    echo "✅ Estado del plugin verificado\n";
    echo "\n🎯 PRÓXIMOS PASOS:\n";
    echo "1. Ir a wp-admin → Venezuela Pro → Configuraciones\n";
    echo "2. Desactivar IGTF si no lo necesitas\n";
    echo "3. Configurar las pasarelas de pago\n";
    echo "4. Probar el frontend\n";
    echo "\n✅ Script completado exitosamente\n";
}

// Ejecutar todas las correcciones
$success = true;

$success &= create_database_tables();
$success &= set_default_options();
$success &= clear_cache();
$success &= verify_plugin_status();

if ($success) {
    show_summary();
} else {
    echo "\n❌ Algunas correcciones fallaron. Revisar los errores arriba.\n";
}
?>
