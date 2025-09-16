<?php
/**
 * Clase para sistema de logging interno del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.1.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Logger {
    
    /**
     * Niveles de log disponibles
     */
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_SUCCESS = 'SUCCESS';
    const LEVEL_DEBUG = 'DEBUG';
    
    /**
     * Contextos predefinidos
     */
    const CONTEXT_CRON = 'Ejecución Cron';
    const CONTEXT_API = 'Llamada API BCV';
    const CONTEXT_DATABASE = 'Base de Datos';
    const CONTEXT_SETTINGS = 'Ajustes Guardados';
    const CONTEXT_SCRAPING = 'Scraping Manual';
    const CONTEXT_CLEANUP = 'Limpieza de Datos';
    const CONTEXT_INTEGRATION = 'Integración WVP';
    const CONTEXT_SECURITY = 'Seguridad';
    
    /**
     * Nombre de la tabla de logs
     * 
     * @var string
     */
    private static $table_name;
    
    /**
     * Inicializar la clase
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'bcv_logs';
    }
    
    /**
     * Registrar un evento en el sistema de logs
     * 
     * @param string $level Nivel del log (INFO, WARNING, ERROR, SUCCESS, DEBUG)
     * @param string $context Contexto del evento
     * @param string $message Mensaje del evento
     * @param array $extra_data Datos adicionales (opcional)
     * @return bool True si se guardó correctamente, False en caso contrario
     */
    public static function log($level, $context, $message, $extra_data = array()) {
        error_log('BCV Dólar Tracker: BCV_Logger::log() llamado - Level: ' . $level . ', Context: ' . $context . ', Message: ' . $message);
        
        // Verificar si el modo de depuración está activado
        $debug_enabled = self::is_debug_mode_enabled();
        error_log('BCV Dólar Tracker: Debug mode habilitado: ' . ($debug_enabled ? 'Sí' : 'No'));
        
        if (!$debug_enabled) {
            error_log('BCV Dólar Tracker: Debug mode deshabilitado, no se registrará el log');
            return false;
        }
        
        // Validar nivel de log
        if (!self::is_valid_level($level)) {
            $level = self::LEVEL_INFO;
        }
        
        // Sanitizar datos
        $level = sanitize_text_field($level);
        $context = sanitize_text_field($context);
        $message = sanitize_textarea_field($message);
        
        // Obtener información del usuario actual
        $user_id = get_current_user_id();
        $ip_address = self::get_client_ip();
        
        // Preparar datos para inserción
        $data = array(
            'log_level' => $level,
            'context' => $context,
            'message' => $message,
            'user_id' => $user_id > 0 ? $user_id : null,
            'ip_address' => $ip_address
        );
        
        // Insertar en la base de datos
        global $wpdb;
        $result = $wpdb->insert(self::$table_name, $data, array('%s', '%s', '%s', '%d', '%s'));
        
        if ($result === false) {
            error_log('BCV Dólar Tracker: Error al guardar log - ' . $wpdb->last_error);
            return false;
        }
        
        // Log también en el debug.log de WordPress si está habilitado
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = "[BCV Logger] [{$level}] [{$context}] {$message}";
            if ($user_id > 0) {
                $log_message .= " [User: {$user_id}]";
            }
            error_log($log_message);
        }
        
        return true;
    }
    
    /**
     * Métodos de conveniencia para diferentes niveles
     */
    public static function info($context, $message, $extra_data = array()) {
        return self::log(self::LEVEL_INFO, $context, $message, $extra_data);
    }
    
    public static function warning($context, $message, $extra_data = array()) {
        return self::log(self::LEVEL_WARNING, $context, $message, $extra_data);
    }
    
    public static function error($context, $message, $extra_data = array()) {
        return self::log(self::LEVEL_ERROR, $context, $message, $extra_data);
    }
    
    public static function success($context, $message, $extra_data = array()) {
        return self::log(self::LEVEL_SUCCESS, $context, $message, $extra_data);
    }
    
    public static function debug($context, $message, $extra_data = array()) {
        return self::log(self::LEVEL_DEBUG, $context, $message, $extra_data);
    }
    
    /**
     * Verificar si el modo de depuración está habilitado
     * 
     * @return bool True si está habilitado, False en caso contrario
     */
    public static function is_debug_mode_enabled() {
        $debug_mode = get_option('bcv_debug_mode', false);
        error_log('BCV Dólar Tracker: Obteniendo debug mode de BD: ' . ($debug_mode ? 'true' : 'false'));
        error_log('BCV Dólar Tracker: Tipo de debug_mode: ' . gettype($debug_mode));
        error_log('BCV Dólar Tracker: Valor exacto: ' . var_export($debug_mode, true));
        
        // Verificar si es true, 1, o '1' - simplificado
        $is_enabled = !empty($debug_mode) && $debug_mode != '0' && $debug_mode != false;
        error_log('BCV Dólar Tracker: Debug mode habilitado: ' . ($is_enabled ? 'Sí' : 'No'));
        return $is_enabled;
    }
    
    /**
     * Habilitar modo de depuración
     * 
     * @return bool True si se guardó correctamente
     */
    public static function enable_debug_mode() {
        error_log('BCV Dólar Tracker: BCV_Logger::enable_debug_mode() llamado');
        
        // Verificar valor actual
        $current_value = get_option('bcv_debug_mode', false);
        error_log('BCV Dólar Tracker: Valor actual de bcv_debug_mode: ' . ($current_value ? 'true' : 'false'));
        
        // Intentar actualizar
        $result = update_option('bcv_debug_mode', true);
        error_log('BCV Dólar Tracker: update_option resultado: ' . ($result ? 'OK' : 'Error'));
        
        // Verificar si realmente se guardó
        $new_value = get_option('bcv_debug_mode', false);
        error_log('BCV Dólar Tracker: Valor después de update_option: ' . ($new_value ? 'true' : 'false'));
        
        if ($result) {
            error_log('BCV Dólar Tracker: Intentando registrar log de info');
            self::info(self::CONTEXT_SETTINGS, 'Modo de depuración habilitado');
        } else {
            error_log('BCV Dólar Tracker: update_option falló, pero el valor puede haberse guardado igual');
            // Intentar registrar el log de todas formas
            self::info(self::CONTEXT_SETTINGS, 'Modo de depuración habilitado');
        }
        return $result;
    }
    
    /**
     * Deshabilitar modo de depuración
     * 
     * @return bool True si se guardó correctamente
     */
    public static function disable_debug_mode() {
        error_log('BCV Dólar Tracker: BCV_Logger::disable_debug_mode() llamado');
        
        // Verificar valor actual
        $current_value = get_option('bcv_debug_mode', false);
        error_log('BCV Dólar Tracker: Valor actual de bcv_debug_mode: ' . ($current_value ? 'true' : 'false'));
        
        // Intentar actualizar
        $result = update_option('bcv_debug_mode', false);
        error_log('BCV Dólar Tracker: update_option resultado: ' . ($result ? 'OK' : 'Error'));
        
        // Verificar si realmente se guardó
        $new_value = get_option('bcv_debug_mode', false);
        error_log('BCV Dólar Tracker: Valor después de update_option: ' . ($new_value ? 'true' : 'false'));
        
        if ($result) {
            error_log('BCV Dólar Tracker: Intentando registrar log de info');
            self::info(self::CONTEXT_SETTINGS, 'Modo de depuración deshabilitado');
        } else {
            error_log('BCV Dólar Tracker: update_option falló, pero el valor puede haberse guardado igual');
            // Intentar registrar el log de todas formas
            self::info(self::CONTEXT_SETTINGS, 'Modo de depuración deshabilitado');
        }
        return $result;
    }
    
    /**
     * Obtener logs con paginación y filtros
     * 
     * @param array $args Argumentos de consulta
     * @return array Array con logs y paginación
     */
    public static function get_logs($args = array()) {
        global $wpdb;
        
        // Argumentos por defecto
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'log_level' => '',
            'context' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Sanitizar argumentos
        $args['per_page'] = max(1, min(100, intval($args['per_page'])));
        $args['page'] = max(1, intval($args['page']));
        $args['log_level'] = sanitize_text_field($args['log_level']);
        $args['context'] = sanitize_text_field($args['context']);
        $args['search'] = sanitize_text_field($args['search']);
        $args['date_from'] = sanitize_text_field($args['date_from']);
        $args['date_to'] = sanitize_text_field($args['date_to']);
        
        // Validar orderby y order
        $allowed_orderby = array('created_at', 'log_level', 'context', 'id');
        $allowed_order = array('ASC', 'DESC');
        
        if (!in_array($args['orderby'], $allowed_orderby)) {
            $args['orderby'] = 'created_at';
        }
        
        if (!in_array(strtoupper($args['order']), $allowed_order)) {
            $args['order'] = 'DESC';
        }
        
        // Construir consulta WHERE
        $where_clauses = array();
        $where_values = array();
        
        if (!empty($args['log_level'])) {
            $where_clauses[] = 'log_level = %s';
            $where_values[] = $args['log_level'];
        }
        
        if (!empty($args['context'])) {
            $where_clauses[] = 'context = %s';
            $where_values[] = $args['context'];
        }
        
        if (!empty($args['search'])) {
            $where_clauses[] = '(message LIKE %s OR context LIKE %s)';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        if (!empty($args['date_from'])) {
            $where_clauses[] = 'timestamp >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where_clauses[] = 'timestamp <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }
        
        // Consulta para contar total de registros
        $count_sql = "SELECT COUNT(*) FROM " . self::$table_name . " {$where_sql}";
        if (!empty($where_values)) {
            $count_sql = $wpdb->prepare($count_sql, $where_values);
        }
        
        $total_items = $wpdb->get_var($count_sql);
        
        // Calcular offset para paginación
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        // Consulta principal
        $sql = "SELECT * FROM " . self::$table_name . " {$where_sql} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
        
        $query_values = array_merge($where_values, array($args['per_page'], $offset));
        $sql = $wpdb->prepare($sql, $query_values);
        
        $items = $wpdb->get_results($sql);
        
        return array(
            'items' => $items,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $args['per_page']),
            'current_page' => $args['page'],
            'per_page' => $args['per_page']
        );
    }
    
    /**
     * Limpiar todos los logs
     * 
     * @return int|false Número de registros eliminados o false si falla
     */
    public static function clear_all_logs() {
        global $wpdb;
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM " . self::$table_name);
        
        if ($count > 0) {
            self::info(self::CONTEXT_CLEANUP, "Limpiando todos los logs ({$count} registros)");
        }
        
        $result = $wpdb->query("DELETE FROM " . self::$table_name);
        
        if ($result === false) {
            self::error(self::CONTEXT_CLEANUP, 'Error al limpiar todos los logs: ' . $wpdb->last_error);
            return false;
        }
        
        if ($result > 0) {
            self::info(self::CONTEXT_CLEANUP, "Todos los logs eliminados exitosamente ({$result} registros)");
        }
        
        return $result;
    }
    
    /**
     * Verificar si un nivel de log es válido
     * 
     * @param string $level Nivel a verificar
     * @return bool True si es válido, False en caso contrario
     */
    private static function is_valid_level($level) {
        $valid_levels = array(
            self::LEVEL_INFO,
            self::LEVEL_WARNING,
            self::LEVEL_ERROR,
            self::LEVEL_SUCCESS,
            self::LEVEL_DEBUG
        );
        
        return in_array($level, $valid_levels);
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string IP del cliente
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
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    }
}

// Inicializar la clase
BCV_Logger::init();