<?php
/**
 * Limpiador Automático de Debug Log - WooCommerce Venezuela Pro
 * Limpia automáticamente el debug.log cuando se acumulan muchos errores
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Debug_Log_Cleaner {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Constructor privado
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Obtener instancia única
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar
     */
    public function init() {
        // Verificar y limpiar debug.log cada hora
        add_action('wp_loaded', array($this, 'check_and_clean_debug_log'));
        
        // Hook para limpiar cuando se acumulen muchos errores
        add_action('wvp_error_threshold_reached', array($this, 'clean_debug_log'));
    }
    
    /**
     * Verificar y limpiar debug.log
     */
    public function check_and_clean_debug_log() {
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($debug_log_path)) {
            return;
        }
        
        $file_size = filesize($debug_log_path);
        $max_size = 10 * 1024 * 1024; // 10MB
        
        // Si el archivo es muy grande, limpiarlo
        if ($file_size > $max_size) {
            $this->clean_debug_log();
            return;
        }
        
        // Verificar si hay muchos errores recientes
        $recent_errors = $this->count_recent_errors($debug_log_path);
        $max_recent_errors = 100; // Máximo 100 errores en la última hora
        
        if ($recent_errors > $max_recent_errors) {
            do_action('wvp_error_threshold_reached', $recent_errors);
        }
    }
    
    /**
     * Contar errores recientes
     */
    private function count_recent_errors($debug_log_path) {
        $content = file_get_contents($debug_log_path);
        $lines = explode("\n", $content);
        
        $recent_count = 0;
        $one_hour_ago = time() - HOUR_IN_SECONDS;
        
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            
            // Buscar timestamp en la línea
            if (preg_match('/\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2}) UTC\]/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                
                if ($timestamp > $one_hour_ago) {
                    $recent_count++;
                }
            }
        }
        
        return $recent_count;
    }
    
    /**
     * Limpiar debug.log
     */
    public function clean_debug_log() {
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($debug_log_path)) {
            return;
        }
        
        // Crear backup del archivo actual
        $backup_path = WP_CONTENT_DIR . '/debug-backup-' . date('Y-m-d-H-i-s') . '.log';
        copy($debug_log_path, $backup_path);
        
        // Limpiar el archivo actual
        file_put_contents($debug_log_path, '');
        
        // Log de la limpieza
        error_log('WVP Debug Log Cleaner: Debug log cleaned automatically');
        
        // Limpiar backups antiguos (mantener solo los últimos 5)
        $this->cleanup_old_backups();
    }
    
    /**
     * Limpiar backups antiguos
     */
    private function cleanup_old_backups() {
        $backup_files = glob(WP_CONTENT_DIR . '/debug-backup-*.log');
        
        if (count($backup_files) > 5) {
            // Ordenar por fecha de modificación
            usort($backup_files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Eliminar los más antiguos
            $files_to_delete = array_slice($backup_files, 0, count($backup_files) - 5);
            
            foreach ($files_to_delete as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Obtener estadísticas del debug.log
     */
    public function get_debug_log_stats() {
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($debug_log_path)) {
            return array(
                'exists' => false,
                'size' => 0,
                'size_mb' => 0,
                'lines' => 0,
                'recent_errors' => 0
            );
        }
        
        $content = file_get_contents($debug_log_path);
        $lines = explode("\n", $content);
        $file_size = filesize($debug_log_path);
        
        $recent_errors = $this->count_recent_errors($debug_log_path);
        
        return array(
            'exists' => true,
            'size' => $file_size,
            'size_mb' => round($file_size / 1024 / 1024, 2),
            'lines' => count($lines),
            'recent_errors' => $recent_errors
        );
    }
    
    /**
     * Limpiar debug.log manualmente
     */
    public function manual_clean() {
        $this->clean_debug_log();
        
        return array(
            'success' => true,
            'message' => 'Debug log limpiado manualmente',
            'timestamp' => current_time('timestamp')
        );
    }
}

// Inicializar el limpiador de debug log
WVP_Debug_Log_Cleaner::get_instance();
?>
