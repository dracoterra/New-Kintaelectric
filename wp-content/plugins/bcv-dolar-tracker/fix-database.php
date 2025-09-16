<?php
/**
 * Script de reparación rápida para la base de datos del plugin BCV Dólar Tracker
 * 
 * Este script debe ejecutarse una sola vez para arreglar los problemas de la base de datos
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    // Cargar WordPress
    require_once('../../../wp-load.php');
}

// Verificar permisos de administrador
if (!current_user_can('manage_options')) {
    wp_die('No tienes permisos para ejecutar este script.');
}

global $wpdb;

echo "<h1>🔧 Reparando Base de Datos del Plugin BCV Dólar Tracker</h1>";

// 1. Eliminar tabla de logs existente
$logs_table = $wpdb->prefix . 'bcv_logs';
$wpdb->query("DROP TABLE IF EXISTS {$logs_table}");
echo "<p>✅ Tabla de logs eliminada</p>";

// 2. Crear tabla de logs correctamente
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$logs_table} (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    log_level varchar(20) NOT NULL DEFAULT 'INFO',
    context varchar(100) NOT NULL DEFAULT '',
    message text NOT NULL,
    user_id bigint(20) unsigned DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_level (log_level),
    KEY idx_context (context),
    KEY idx_user_id (user_id),
    KEY idx_created_at (created_at),
    KEY idx_created_at_level (created_at, log_level)
) {$charset_collate};";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$result = dbDelta($sql);

if (empty($wpdb->last_error)) {
    echo "<p>✅ Tabla de logs creada correctamente</p>";
} else {
    echo "<p>❌ Error al crear tabla de logs: " . $wpdb->last_error . "</p>";
}

// 3. Verificar que la tabla existe y tiene las columnas correctas
$columns = $wpdb->get_results("DESCRIBE {$logs_table}");
echo "<h2>📋 Columnas de la tabla de logs:</h2>";
echo "<ul>";
foreach ($columns as $column) {
    echo "<li><strong>{$column->Field}</strong> - {$column->Type}</li>";
}
echo "</ul>";

// 4. Insertar un log de prueba
$test_log = $wpdb->insert(
    $logs_table,
    array(
        'log_level' => 'INFO',
        'context' => 'Reparación',
        'message' => 'Base de datos reparada exitosamente',
        'user_id' => get_current_user_id(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ),
    array('%s', '%s', '%s', '%d', '%s')
);

if ($test_log !== false) {
    echo "<p>✅ Log de prueba insertado correctamente</p>";
} else {
    echo "<p>❌ Error al insertar log de prueba: " . $wpdb->last_error . "</p>";
}

// 5. Limpiar opciones de debug
delete_option('bcv_debug_mode');
update_option('bcv_debug_mode', true);

echo "<h2>🎉 Reparación Completada</h2>";
echo "<p>La base de datos ha sido reparada. Ahora puedes:</p>";
echo "<ul>";
echo "<li>Ir al panel de administración del plugin</li>";
echo "<li>Verificar que las estadísticas funcionan correctamente</li>";
echo "<li>Probar el sistema de logs</li>";
echo "</ul>";

echo "<p><a href='" . admin_url('admin.php?page=bcv-dolar') . "'>← Volver al Plugin</a></p>";

// Eliminar este archivo después de usarlo
echo "<p><strong>⚠️ Importante:</strong> Elimina este archivo (fix-database.php) después de usarlo por seguridad.</p>";
?>
