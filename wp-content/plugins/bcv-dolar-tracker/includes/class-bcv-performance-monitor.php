<?php
/**
 * Clase para monitoreo de rendimiento del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Performance_Monitor {
    
    /**
     * Instancia singleton
     * 
     * @var BCV_Performance_Monitor
     */
    private static $instance = null;
    
    /**
     * Métricas de rendimiento
     * 
     * @var array
     */
    private $metrics = array();
    
    /**
     * Timers activos
     * 
     * @var array
     */
    private $timers = array();
    
    /**
     * Constructor de la clase
     */
    private function __construct() {
        // Hook para guardar métricas al final del request
        add_action('shutdown', array($this, 'save_metrics'));
        
        // Inicializar métricas básicas
        $this->init_base_metrics();
    }
    
    /**
     * Obtener instancia singleton
     * 
     * @return BCV_Performance_Monitor
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar métricas base
     */
    private function init_base_metrics() {
        $this->metrics = array(
            'request_start' => microtime(true),
            'memory_start' => memory_get_usage(true),
            'queries_start' => 0,
            'operations' => array()
        );
        
        // Obtener número de queries iniciales si wpdb está disponible
        global $wpdb;
        if (isset($wpdb)) {
            $this->metrics['queries_start'] = $wpdb->num_queries;
        }
    }
    
    /**
     * Iniciar timer para una operación
     * 
     * @param string $operation Nombre de la operación
     */
    public static function start_timer($operation) {
        $instance = self::get_instance();
        $instance->timers[$operation] = microtime(true);
        
        BCV_Logger::debug('PERFORMANCE', "Starting timer for: {$operation}");
    }
    
    /**
     * Finalizar timer y registrar métrica
     * 
     * @param string $operation Nombre de la operación
     * @param array $context Contexto adicional
     */
    public static function end_timer($operation, $context = array()) {
        $instance = self::get_instance();
        
        if (!isset($instance->timers[$operation])) {
            BCV_Logger::warning("Timer not found for operation: {$operation}");
            return;
        }
        
        $start_time = $instance->timers[$operation];
        $execution_time = microtime(true) - $start_time;
        $memory_usage = memory_get_usage(true);
        
        // Registrar métrica
        $instance->metrics['operations'][$operation] = array(
            'execution_time' => $execution_time,
            'memory_usage' => $memory_usage,
            'timestamp' => current_time('mysql'),
            'context' => $context
        );
        
        // Limpiar timer
        unset($instance->timers[$operation]);
        
        // Log de performance
        BCV_Logger::performance($operation, $start_time, $context);
        
        // Alerta si es muy lento
        if ($execution_time > 5.0) {
            BCV_Logger::warning("Slow operation detected: {$operation}", array(
                'execution_time' => $execution_time . 's',
                'memory_usage' => size_format($memory_usage)
            ));
        }
    }
    
    /**
     * Registrar métrica personalizada
     * 
     * @param string $name Nombre de la métrica
     * @param mixed $value Valor de la métrica
     * @param array $context Contexto adicional
     */
    public static function record_metric($name, $value, $context = array()) {
        $instance = self::get_instance();
        
        $instance->metrics['custom'][$name] = array(
            'value' => $value,
            'timestamp' => current_time('mysql'),
            'context' => $context
        );
        
        BCV_Logger::info('PERFORMANCE', "Metric recorded: {$name}", array(
            'value' => $value,
            'context' => $context
        ));
    }
    
    /**
     * Obtener métricas actuales
     * 
     * @return array Métricas de rendimiento
     */
    public static function get_metrics() {
        $instance = self::get_instance();
        return $instance->metrics;
    }
    
    /**
     * Obtener estadísticas de rendimiento
     * 
     * @return array Estadísticas compiladas
     */
    public static function get_performance_stats() {
        $instance = self::get_instance();
        
        $current_time = microtime(true);
        $current_memory = memory_get_usage(true);
        $peak_memory = memory_get_peak_usage(true);
        
        global $wpdb;
        $current_queries = isset($wpdb) ? $wpdb->num_queries : 0;
        
        $stats = array(
            'total_execution_time' => $current_time - $instance->metrics['request_start'],
            'memory_usage' => $current_memory,
            'memory_increase' => $current_memory - $instance->metrics['memory_start'],
            'peak_memory' => $peak_memory,
            'total_queries' => $current_queries - $instance->metrics['queries_start'],
            'operations_count' => count($instance->metrics['operations']),
            'slow_operations' => self::get_slow_operations()
        );
        
        return $stats;
    }
    
    /**
     * Obtener operaciones lentas
     * 
     * @param float $threshold Umbral de tiempo en segundos
     * @return array Operaciones que superan el umbral
     */
    private static function get_slow_operations($threshold = 1.0) {
        $instance = self::get_instance();
        $slow_operations = array();
        
        foreach ($instance->metrics['operations'] as $operation => $data) {
            if ($data['execution_time'] > $threshold) {
                $slow_operations[$operation] = $data;
            }
        }
        
        return $slow_operations;
    }
    
    /**
     * Guardar métricas en la base de datos
     */
    public function save_metrics() {
        // Solo guardar métricas si hay operaciones registradas
        if (empty($this->metrics['operations'])) {
            return;
        }
        
        $stats = self::get_performance_stats();
        
        // Guardar métricas en opciones de WordPress (rotar cada 24 horas)
        $daily_key = 'bcv_performance_' . date('Y-m-d');
        $current_data = get_option($daily_key, array());
        
        $current_data[] = array(
            'timestamp' => current_time('mysql'),
            'stats' => $stats,
            'operations' => $this->metrics['operations']
        );
        
        // Limitar a 100 entradas por día
        if (count($current_data) > 100) {
            $current_data = array_slice($current_data, -100);
        }
        
        update_option($daily_key, $current_data);
        
        // Limpiar métricas antiguas (más de 7 días)
        $this->cleanup_old_metrics();
    }
    
    /**
     * Limpiar métricas antiguas
     */
    private function cleanup_old_metrics() {
        global $wpdb;
        
        // Obtener opciones de métricas antiguas
        $old_date = date('Y-m-d', strtotime('-7 days'));
        
        $options_to_delete = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE 'bcv_performance_%' 
             AND option_name < %s",
            'bcv_performance_' . $old_date
        ));
        
        foreach ($options_to_delete as $option_name) {
            delete_option($option_name);
        }
    }
    
    /**
     * Obtener resumen de rendimiento de los últimos días
     * 
     * @param int $days Número de días a analizar
     * @return array Resumen de rendimiento
     */
    public static function get_performance_summary($days = 7) {
        $summary = array(
            'total_requests' => 0,
            'avg_execution_time' => 0,
            'avg_memory_usage' => 0,
            'avg_queries' => 0,
            'slow_operations_count' => 0,
            'most_common_operations' => array()
        );
        
        $total_execution_time = 0;
        $total_memory_usage = 0;
        $total_queries = 0;
        $operations_count = array();
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $daily_data = get_option('bcv_performance_' . $date, array());
            
            foreach ($daily_data as $entry) {
                $summary['total_requests']++;
                $total_execution_time += $entry['stats']['total_execution_time'];
                $total_memory_usage += $entry['stats']['memory_usage'];
                $total_queries += $entry['stats']['total_queries'];
                $summary['slow_operations_count'] += count($entry['stats']['slow_operations']);
                
                // Contar operaciones
                foreach ($entry['operations'] as $operation => $data) {
                    if (!isset($operations_count[$operation])) {
                        $operations_count[$operation] = 0;
                    }
                    $operations_count[$operation]++;
                }
            }
        }
        
        if ($summary['total_requests'] > 0) {
            $summary['avg_execution_time'] = $total_execution_time / $summary['total_requests'];
            $summary['avg_memory_usage'] = $total_memory_usage / $summary['total_requests'];
            $summary['avg_queries'] = $total_queries / $summary['total_requests'];
        }
        
        // Ordenar operaciones más comunes
        arsort($operations_count);
        $summary['most_common_operations'] = array_slice($operations_count, 0, 10, true);
        
        return $summary;
    }
}
