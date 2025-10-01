<?php
/**
 * Logger - Sistema de logs del plugin
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el sistema de logs
 */
class WVS_Logger {
    
    /**
     * Niveles de log
     */
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    
    /**
     * Instancia única
     * 
     * @var WVS_Logger
     */
    private static $instance = null;
    
    /**
     * Constructor privado
     */
    private function __construct() {
        // Constructor privado para Singleton
    }
    
    /**
     * Obtener instancia única
     * 
     * @return WVS_Logger
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log de debug
     * 
     * @param string $message
     * @param array $context
     */
    public static function debug($message, $context = array()) {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Log de información
     * 
     * @param string $message
     * @param array $context
     */
    public static function info($message, $context = array()) {
        self::log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log de advertencia
     * 
     * @param string $message
     * @param array $context
     */
    public static function warning($message, $context = array()) {
        self::log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log de error
     * 
     * @param string $message
     * @param array $context
     */
    public static function error($message, $context = array()) {
        self::log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Registrar log
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private static function log($level, $message, $context = array()) {
        global $wpdb;
        
        // Solo registrar si WP_DEBUG está habilitado o es un error
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            if ($level !== self::LEVEL_ERROR) {
                return;
            }
        }
        
        // Verificar si la tabla de logs existe
        $table_name = $wpdb->prefix . 'wvs_logs';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            // Solo registrar en error_log si la tabla no existe
            if ($level === self::LEVEL_ERROR) {
                error_log("WVS Error: {$message} " . (!empty($context) ? wp_json_encode($context) : ''));
            }
            return;
        }
        
        // Preparar datos del log
        $log_data = array(
            'level' => $level,
            'message' => $message,
            'context' => !empty($context) ? wp_json_encode($context) : null,
            'user_id' => get_current_user_id(),
            'ip_address' => self::get_client_ip()
        );
        
        // Insertar en base de datos
        $wpdb->insert(
            $table_name,
            $log_data,
            array('%s', '%s', '%s', '%d', '%s')
        );
        
        // También registrar en error_log si es un error
        if ($level === self::LEVEL_ERROR) {
            error_log("WVS Error: {$message} " . (!empty($context) ? wp_json_encode($context) : ''));
        }
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string
     */
    private static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Obtener logs
     * 
     * @param array $args
     * @return array
     */
    public static function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'level' => null,
            'limit' => 100,
            'offset' => 0,
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        if ($args['level']) {
            $where_conditions[] = 'level = %s';
            $where_values[] = $args['level'];
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wvs_logs 
             {$where_clause} 
             ORDER BY timestamp {$args['order']} 
             LIMIT %d OFFSET %d",
            array_merge($where_values, array($args['limit'], $args['offset']))
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Limpiar logs antiguos
     * 
     * @param int $days
     * @return int
     */
    public static function cleanup_old_logs($days = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wvs_logs WHERE timestamp < %s",
            $cutoff_date
        ));
    }
}
