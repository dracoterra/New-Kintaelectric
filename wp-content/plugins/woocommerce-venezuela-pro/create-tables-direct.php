<?php
// Script directo para crear tablas
require_once('../../wp-config.php');
require_once('../../wp-load.php');

global $wpdb;

// Crear tabla de errores
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

if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
    echo "✅ Tabla $table_name creada correctamente\n";
} else {
    echo "❌ Error al crear tabla $table_name\n";
    echo "Error: " . $wpdb->last_error . "\n";
}

// Crear tabla de seguridad
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

$result2 = dbDelta($security_sql);

if ($wpdb->get_var("SHOW TABLES LIKE '$security_table'") == $security_table) {
    echo "✅ Tabla $security_table creada correctamente\n";
} else {
    echo "❌ Error al crear tabla $security_table\n";
    echo "Error: " . $wpdb->last_error . "\n";
}

echo "Script completado.\n";
?>
