<?php
/**
 * Clase de administración limpia para BCV Dólar Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCV_Admin_Clean {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Constructor vacío - solo para compatibilidad
    }
    
    /**
     * Renderizar página de logs
     */
    public function render_logs_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        // Procesar acciones de limpieza
        if (isset($_POST['clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'bcv_clear_logs')) {
            $result = BCV_Logger::clear_all_logs();
            if ($result) {
                echo '<div class="notice notice-success"><p>Logs eliminados correctamente.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Error al eliminar los logs.</p></div>';
            }
        }
        
        // Crear tabla de logs
        $logs_table = new BCV_Logs_Table();
        $logs_table->prepare_items();
        
        echo '<div class="wrap">';
        echo '<h1>📋 Registro de Actividad</h1>';
        echo '<p>Registro detallado de todas las actividades del plugin.</p>';
        
        // Botón de limpieza
        echo '<form method="post" style="display: inline-block; margin-bottom: 20px;">';
        wp_nonce_field('bcv_clear_logs');
        echo '<button type="submit" name="clear_logs" class="button button-secondary" onclick="return confirm(\'¿Estás seguro? Esto eliminará todos los logs.\');">🗑️ Limpiar Todos los Logs</button>';
        echo '</form>';
        
        echo '<form method="post">';
        echo '<input type="hidden" name="page" value="bcv-logs" />';
        $logs_table->search_box('Buscar logs', 'bcv-search-logs');
        $logs_table->display();
        echo '</form>';
        
        echo '</div>';
    }
    
    /**
     * Recrear tabla de logs
     */
    private function recreate_logs_table() {
        global $wpdb;
        
        // Eliminar tabla existente
        $logs_table_name = $wpdb->prefix . 'bcv_logs';
        $wpdb->query("DROP TABLE IF EXISTS {$logs_table_name}");
        
        // Crear nueva tabla
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$logs_table_name} (
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
            error_log('BCV Dólar Tracker: Tabla de logs recreada correctamente');
            
            // Insertar un log de prueba para verificar que funciona
            $test_log = $wpdb->insert(
                $logs_table_name,
                array(
                    'log_level' => 'INFO',
                    'context' => 'Sistema',
                    'message' => 'Tabla de logs recreada exitosamente',
                    'user_id' => get_current_user_id(),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ),
                array('%s', '%s', '%s', '%d', '%s')
            );
            
            if ($test_log !== false) {
                error_log('BCV Dólar Tracker: Log de prueba insertado correctamente');
            } else {
                error_log('BCV Dólar Tracker: Error al insertar log de prueba: ' . $wpdb->last_error);
            }
        } else {
            error_log('BCV Dólar Tracker: Error al recrear tabla de logs: ' . $wpdb->last_error);
        }
    }
}
