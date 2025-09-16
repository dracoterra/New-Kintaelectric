<?php
/**
 * Clase para logging optimizado del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Logger {
    
    /**
     * Instancia singleton
     * 
     * @var BCV_Logger
     */
    private static $instance = null;
    
    /**
     * Niveles de logging
     */
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_INFO = 3;
    const LEVEL_DEBUG = 4;
    
    /**
     * Nivel actual de logging
     * 
     * @var int
     */
    private $log_level;
    
    /**
     * Buffer de logs para escribir en lotes
     * 
     * @var array
     */
    private $log_buffer = array();
    
    /**
     * Constructor de la clase
     */
    private function __construct() {
        // Determinar nivel de logging basado en WP_DEBUG
        $this->log_level = (defined('WP_DEBUG') && WP_DEBUG) ? self::LEVEL_DEBUG : self::LEVEL_ERROR;
        
        // Hook para escribir buffer al final del request
        add_action('shutdown', array($this, 'flush_buffer'));
    }
    
    /**
     * Obtener instancia singleton
     * 
     * @return BCV_Logger
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log de error
     * 
     * @param string $message Mensaje de error
     * @param array $context Contexto adicional
     */
    public static function error($message, $context = array()) {
        self::get_instance()->log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Log de warning
     * 
     * @param string $message Mensaje de warning
     * @param array $context Contexto adicional
     */
    public static function warning($message, $context = array()) {
        self::get_instance()->log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log de información
     * 
     * @param string $message Mensaje informativo
     * @param array $context Contexto adicional
     */
    public static function info($message, $context = array()) {
        self::get_instance()->log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log de debug
     * 
     * @param string $message Mensaje de debug
     * @param array $context Contexto adicional
     */
    public static function debug($message, $context = array()) {
        self::get_instance()->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Logging principal
     * 
     * @param int $level Nivel de logging
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    private function log($level, $message, $context = array()) {
        // Solo loguear si el nivel es apropiado
        if ($level > $this->log_level) {
            return;
        }
        
        // Preparar mensaje con timestamp y contexto
        $timestamp = current_time('Y-m-d H:i:s');
        $level_name = $this->get_level_name($level);
        
        $formatted_message = sprintf(
            '[%s] BCV Dólar Tracker [%s]: %s',
            $timestamp,
            $level_name,
            $message
        );
        
        // Añadir contexto si existe
        if (!empty($context)) {
            $formatted_message .= ' | Context: ' . json_encode($context);
        }
        
        // Añadir al buffer
        $this->log_buffer[] = $formatted_message;
        
        // Si es error crítico, escribir inmediatamente
        if ($level === self::LEVEL_ERROR) {
            $this->flush_buffer();
        }
    }
    
    /**
     * Obtener nombre del nivel
     * 
     * @param int $level Nivel numérico
     * @return string Nombre del nivel
     */
    private function get_level_name($level) {
        switch ($level) {
            case self::LEVEL_ERROR:
                return 'ERROR';
            case self::LEVEL_WARNING:
                return 'WARNING';
            case self::LEVEL_INFO:
                return 'INFO';
            case self::LEVEL_DEBUG:
                return 'DEBUG';
            default:
                return 'UNKNOWN';
        }
    }
    
    /**
     * Escribir buffer de logs
     */
    public function flush_buffer() {
        if (empty($this->log_buffer)) {
            return;
        }
        
        // Escribir todos los logs del buffer
        foreach ($this->log_buffer as $log_message) {
            error_log($log_message);
        }
        
        // Limpiar buffer
        $this->log_buffer = array();
    }
    
    /**
     * Log de rendimiento
     * 
     * @param string $operation Operación realizada
     * @param float $start_time Tiempo de inicio
     * @param array $context Contexto adicional
     */
    public static function performance($operation, $start_time, $context = array()) {
        $execution_time = microtime(true) - $start_time;
        $memory_usage = memory_get_usage(true);
        $peak_memory = memory_get_peak_usage(true);
        
        $context = array_merge($context, array(
            'execution_time' => round($execution_time, 4) . 's',
            'memory_usage' => size_format($memory_usage),
            'peak_memory' => size_format($peak_memory)
        ));
        
        self::info("Performance: {$operation}", $context);
    }
    
    /**
     * Log de base de datos con query info
     * 
     * @param string $query Query ejecutada
     * @param float $start_time Tiempo de inicio
     * @param int $num_rows Número de filas afectadas
     */
    public static function database($query, $start_time, $num_rows = null) {
        global $wpdb;
        
        $execution_time = microtime(true) - $start_time;
        
        $context = array(
            'query' => $query,
            'execution_time' => round($execution_time, 4) . 's',
            'num_queries' => $wpdb->num_queries
        );
        
        if ($num_rows !== null) {
            $context['rows_affected'] = $num_rows;
        }
        
        if ($wpdb->last_error) {
            $context['error'] = $wpdb->last_error;
            self::error("Database query failed", $context);
        } else {
            self::debug("Database query executed", $context);
        }
    }
}
