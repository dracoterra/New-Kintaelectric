<?php
/**
 * Script para corregir tablas de base de datos faltantes
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    // Si se ejecuta desde línea de comandos
    if (php_sapi_name() !== 'cli') {
        exit('Acceso directo no permitido');
    }
    
    // Cargar WordPress
    require_once dirname(__FILE__) . '/../../wp-config.php';
    require_once dirname(__FILE__) . '/../../wp-load.php';
}

/**
 * Crear tabla de logs de errores
 */
function wvp_create_error_logs_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'wvp_error_logs';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result = dbDelta($sql);
    
    // Verificar si la tabla se creó correctamente
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        echo "✅ Tabla $table_name creada correctamente\n";
        return true;
    } else {
        echo "❌ Error al crear tabla $table_name\n";
        return false;
    }
}

/**
 * Crear tabla de logs de seguridad
 */
function wvp_create_security_logs_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'wvp_security_logs';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
    $result = dbDelta($sql);
    
    // Verificar si la tabla se creó correctamente
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        echo "✅ Tabla $table_name creada correctamente\n";
        return true;
    } else {
        echo "❌ Error al crear tabla $table_name\n";
        return false;
    }
}

/**
 * Verificar estado de las tablas
 */
function wvp_check_tables_status() {
    global $wpdb;
    
    $tables = array(
        'wvp_error_logs',
        'wvp_security_logs'
    );
    
    echo "\n📊 ESTADO DE LAS TABLAS:\n";
    echo "========================\n";
    
    foreach ($tables as $table) {
        $table_name = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            echo "✅ $table_name - Existe ($count registros)\n";
        } else {
            echo "❌ $table_name - No existe\n";
        }
    }
}

/**
 * Ejecutar correcciones
 */
function wvp_fix_database_issues() {
    echo "🔧 CORRIGIENDO PROBLEMAS DE BASE DE DATOS\n";
    echo "==========================================\n\n";
    
    // Crear tablas
    $error_table_created = wvp_create_error_logs_table();
    $security_table_created = wvp_create_security_logs_table();
    
    // Verificar estado
    wvp_check_tables_status();
    
    // Resultado final
    echo "\n📋 RESUMEN:\n";
    echo "===========\n";
    
    if ($error_table_created && $security_table_created) {
        echo "✅ Todas las tablas creadas correctamente\n";
        echo "✅ Los errores de base de datos deberían estar resueltos\n";
        return true;
    } else {
        echo "❌ Algunas tablas no se pudieron crear\n";
        echo "❌ Revisar permisos de base de datos\n";
        return false;
    }
}

// Ejecutar si se llama directamente
if (php_sapi_name() === 'cli' || (isset($_GET['action']) && $_GET['action'] === 'fix_tables')) {
    wvp_fix_database_issues();
}
