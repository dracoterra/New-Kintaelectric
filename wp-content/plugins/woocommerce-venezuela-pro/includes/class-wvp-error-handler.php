<?php
/**
 * Sistema de Manejo de Errores - WooCommerce Venezuela Pro
 * Maneja errores y avisos de forma centralizada
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Error_Handler {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Errores capturados
     */
    private $errors = array();
    
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
        // Configurar manejo de errores
        set_error_handler(array($this, 'handle_error'));
        register_shutdown_function(array($this, 'handle_shutdown'));
        
        // Hook para limpiar errores antiguos
        add_action('wp_loaded', array($this, 'cleanup_old_errors'));
    }
    
    /**
     * Manejar errores PHP
     */
    public function handle_error($errno, $errstr, $errfile, $errline) {
        // Solo manejar errores relacionados con nuestro plugin
        if (strpos($errfile, 'woocommerce-venezuela-pro') === false) {
            return false;
        }
        
        $error = array(
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => current_time('timestamp')
        );
        
        $this->errors[] = $error;
        
        // Log del error
        error_log("WVP Error: {$errstr} in {$errfile} on line {$errline}");
        
        // No ejecutar el manejador de errores interno de PHP
        return true;
    }
    
    /**
     * Manejar errores fatales
     */
    public function handle_shutdown() {
        $error = error_get_last();
        
        if ($error && strpos($error['file'], 'woocommerce-venezuela-pro') !== false) {
            $this->errors[] = array(
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => current_time('timestamp'),
                'fatal' => true
            );
            
            error_log("WVP Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
        }
    }
    
    /**
     * Limpiar errores antiguos
     */
    public function cleanup_old_errors() {
        $max_age = 24 * HOUR_IN_SECONDS; // 24 horas
        $current_time = current_time('timestamp');
        
        $this->errors = array_filter($this->errors, function($error) use ($current_time, $max_age) {
            return ($current_time - $error['timestamp']) < $max_age;
        });
    }
    
    /**
     * Obtener errores
     */
    public function get_errors() {
        return $this->errors;
    }
    
    /**
     * Obtener errores por tipo
     */
    public function get_errors_by_type($type) {
        return array_filter($this->errors, function($error) use ($type) {
            return $error['type'] === $type;
        });
    }
    
    /**
     * Limpiar todos los errores
     */
    public function clear_errors() {
        $this->errors = array();
    }
    
    /**
     * Verificar si hay errores
     */
    public function has_errors() {
        return !empty($this->errors);
    }
    
    /**
     * Obtener estadísticas de errores
     */
    public function get_error_stats() {
        $stats = array(
            'total' => count($this->errors),
            'fatal' => 0,
            'warning' => 0,
            'notice' => 0,
            'recent' => 0
        );
        
        $recent_time = current_time('timestamp') - (HOUR_IN_SECONDS);
        
        foreach ($this->errors as $error) {
            if (isset($error['fatal']) && $error['fatal']) {
                $stats['fatal']++;
            } elseif ($error['type'] === E_WARNING) {
                $stats['warning']++;
            } elseif ($error['type'] === E_NOTICE) {
                $stats['notice']++;
            }
            
            if ($error['timestamp'] > $recent_time) {
                $stats['recent']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Validar clases antes de instanciar
     */
    public function validate_class_instantiation($class_name) {
        if (!class_exists($class_name)) {
            $this->errors[] = array(
                'type' => E_WARNING,
                'message' => "Class {$class_name} does not exist",
                'file' => __FILE__,
                'line' => __LINE__,
                'timestamp' => current_time('timestamp')
            );
            return false;
        }
        
        // Verificar si es Singleton
        $reflection = new ReflectionClass($class_name);
        $constructor = $reflection->getConstructor();
        
        if ($constructor && $constructor->isPrivate()) {
            // Es Singleton, usar get_instance()
            if (!method_exists($class_name, 'get_instance')) {
                $this->errors[] = array(
                    'type' => E_ERROR,
                    'message' => "Class {$class_name} has private constructor but no get_instance method",
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'timestamp' => current_time('timestamp')
                );
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Instanciar clase de forma segura
     */
    public function safe_instantiate($class_name) {
        if (!$this->validate_class_instantiation($class_name)) {
            return null;
        }
        
        try {
            $reflection = new ReflectionClass($class_name);
            $constructor = $reflection->getConstructor();
            
            if ($constructor && $constructor->isPrivate()) {
                // Es Singleton
                return call_user_func(array($class_name, 'get_instance'));
            } else {
                // Constructor público
                return new $class_name();
            }
        } catch (Exception $e) {
            $this->errors[] = array(
                'type' => E_ERROR,
                'message' => "Failed to instantiate {$class_name}: " . $e->getMessage(),
                'file' => __FILE__,
                'line' => __LINE__,
                'timestamp' => current_time('timestamp')
            );
            return null;
        }
    }
    
    /**
     * Generar reporte de errores
     */
    public function generate_error_report() {
        $stats = $this->get_error_stats();
        $report = array(
            'stats' => $stats,
            'errors' => $this->errors,
            'timestamp' => current_time('timestamp')
        );
        
        return $report;
    }
}

// Inicializar el manejador de errores
WVP_Error_Handler::get_instance();
?>
