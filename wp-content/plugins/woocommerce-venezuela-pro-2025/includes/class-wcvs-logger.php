<?php
/**
 * Sistema de logging del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar logs del plugin
 */
class WCVS_Logger {

    /**
     * Contextos de logging
     */
    const CONTEXT_GENERAL = 'General';
    const CONTEXT_BCV = 'BCV';
    const CONTEXT_CURRENCY = 'Currency';
    const CONTEXT_PAYMENTS = 'Payments';
    const CONTEXT_SHIPPING = 'Shipping';
    const CONTEXT_TAXES = 'Taxes';
    const CONTEXT_SENIAT = 'SENIAT';
    const CONTEXT_MODULES = 'Modules';
    const CONTEXT_ADMIN = 'Admin';
    const CONTEXT_ERROR = 'Error';

    /**
     * Niveles de logging
     */
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_SUCCESS = 'SUCCESS';

    /**
     * Modo de debug habilitado
     *
     * @var bool
     */
    private $debug_mode = false;

    /**
     * Constructor
     */
    public function __construct() {
        $this->debug_mode = get_option('wcvs_general')['debug_mode'] ?? false;
    }

    /**
     * Inicializar el sistema de logging
     */
    public function init() {
        // Verificar si el modo de debug está habilitado
        $this->debug_mode = get_option('wcvs_general')['debug_mode'] ?? false;
    }

    /**
     * Registrar mensaje de información
     *
     * @param string $context Contexto del log
     * @param string $message Mensaje
     * @param array $data Datos adicionales
     */
    public static function info($context, $message, $data = array()) {
        self::log(self::LEVEL_INFO, $context, $message, $data);
    }

    /**
     * Registrar mensaje de advertencia
     *
     * @param string $context Contexto del log
     * @param string $message Mensaje
     * @param array $data Datos adicionales
     */
    public static function warning($context, $message, $data = array()) {
        self::log(self::LEVEL_WARNING, $context, $message, $data);
    }

    /**
     * Registrar mensaje de error
     *
     * @param string $context Contexto del log
     * @param string $message Mensaje
     * @param array $data Datos adicionales
     */
    public static function error($context, $message, $data = array()) {
        self::log(self::LEVEL_ERROR, $context, $message, $data);
    }

    /**
     * Registrar mensaje de éxito
     *
     * @param string $context Contexto del log
     * @param string $message Mensaje
     * @param array $data Datos adicionales
     */
    public static function success($context, $message, $data = array()) {
        self::log(self::LEVEL_SUCCESS, $context, $message, $data);
    }

    /**
     * Registrar log en la base de datos
     *
     * @param string $level Nivel del log
     * @param string $context Contexto del log
     * @param string $message Mensaje
     * @param array $data Datos adicionales
     */
    private static function log($level, $context, $message, $data = array()) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wcvs_logs';

        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            // Si la tabla no existe, usar error_log como fallback
            error_log("WCVS [{$level}] {$context}: {$message}");
            return;
        }

        // Insertar log en la base de datos
        $wpdb->insert(
            $table_name,
            array(
                'log_level' => $level,
                'context' => $context,
                'message' => $message,
                'data' => maybe_serialize($data),
                'user_id' => get_current_user_id(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s')
        );

        // También registrar en error_log si está en modo debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WCVS [{$level}] {$context}: {$message}");
        }
    }

    /**
     * Habilitar modo de debug
     *
     * @return bool
     */
    public static function enable_debug_mode() {
        $general_settings = get_option('wcvs_general', array());
        $general_settings['debug_mode'] = true;
        update_option('wcvs_general', $general_settings);

        return true;
    }

    /**
     * Deshabilitar modo de debug
     *
     * @return bool
     */
    public static function disable_debug_mode() {
        $general_settings = get_option('wcvs_general', array());
        $general_settings['debug_mode'] = false;
        update_option('wcvs_general', $general_settings);

        return true;
    }

    /**
     * Verificar si el modo de debug está habilitado
     *
     * @return bool
     */
    public static function is_debug_mode_enabled() {
        $general_settings = get_option('wcvs_general', array());
        return $general_settings['debug_mode'] ?? false;
    }

    /**
     * Limpiar todos los logs
     *
     * @return int|false Número de logs eliminados o false si falla
     */
    public static function clear_all_logs() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wcvs_logs';

        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            return false;
        }

        $result = $wpdb->query("DELETE FROM {$table_name}");

        return $result !== false ? $result : false;
    }

    /**
     * Obtener logs con filtros
     *
     * @param array $args Argumentos de filtro
     * @return array
     */
    public static function get_logs($args = array()) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wcvs_logs';

        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            return array();
        }

        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'level' => '',
            'context' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => ''
        );

        $args = wp_parse_args($args, $defaults);

        // Construir consulta WHERE
        $where_clauses = array();
        $where_values = array();

        if (!empty($args['level'])) {
            $where_clauses[] = 'log_level = %s';
            $where_values[] = $args['level'];
        }

        if (!empty($args['context'])) {
            $where_clauses[] = 'context = %s';
            $where_values[] = $args['context'];
        }

        if (!empty($args['search'])) {
            $where_clauses[] = 'message LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }

        if (!empty($args['date_from'])) {
            $where_clauses[] = 'created_at >= %s';
            $where_values[] = $args['date_from'];
        }

        if (!empty($args['date_to'])) {
            $where_clauses[] = 'created_at <= %s';
            $where_values[] = $args['date_to'];
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        // Consulta para contar total de registros
        $count_sql = "SELECT COUNT(*) FROM {$table_name} {$where_sql}";
        if (!empty($where_values)) {
            $count_sql = $wpdb->prepare($count_sql, $where_values);
        }

        $total_items = $wpdb->get_var($count_sql);

        // Calcular offset para paginación
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Consulta principal
        $sql = "SELECT * FROM {$table_name} {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";

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
     * Obtener estadísticas de logs
     *
     * @return array
     */
    public static function get_log_stats() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wcvs_logs';

        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            return array(
                'total_logs' => 0,
                'info_logs' => 0,
                'warning_logs' => 0,
                'error_logs' => 0,
                'success_logs' => 0,
                'last_log' => null
            );
        }

        $sql = "SELECT 
                    COUNT(*) as total_logs,
                    SUM(CASE WHEN log_level = 'INFO' THEN 1 ELSE 0 END) as info_logs,
                    SUM(CASE WHEN log_level = 'WARNING' THEN 1 ELSE 0 END) as warning_logs,
                    SUM(CASE WHEN log_level = 'ERROR' THEN 1 ELSE 0 END) as error_logs,
                    SUM(CASE WHEN log_level = 'SUCCESS' THEN 1 ELSE 0 END) as success_logs,
                    MAX(created_at) as last_log
                FROM {$table_name}";

        $stats = $wpdb->get_row($sql, ARRAY_A);

        return array(
            'total_logs' => intval($stats['total_logs']),
            'info_logs' => intval($stats['info_logs']),
            'warning_logs' => intval($stats['warning_logs']),
            'error_logs' => intval($stats['error_logs']),
            'success_logs' => intval($stats['success_logs']),
            'last_log' => $stats['last_log']
        );
    }
}
