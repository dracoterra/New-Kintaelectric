<?php
/**
 * Clase de Rate Limiting para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Rate_Limiter {
    
    /**
     * Verificar límite de intentos
     * 
     * @param int $user_id ID del usuario
     * @param string $action Acción a limitar
     * @param int $max_attempts Número máximo de intentos
     * @param int $time_window Ventana de tiempo en segundos
     * @return bool True si está dentro del límite
     */
    public static function check_rate_limit($user_id, $action, $max_attempts = 5, $time_window = 300) {
        if (empty($user_id) || empty($action)) {
            return false;
        }
        
        $key = "wvp_rate_limit_{$action}_{$user_id}";
        $attempts = get_transient($key) ?: 0;
        
        if ($attempts >= $max_attempts) {
            self::log_rate_limit_exceeded($user_id, $action, $attempts);
            return false;
        }
        
        set_transient($key, $attempts + 1, $time_window);
        return true;
    }
    
    /**
     * Limpiar límites de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $action Acción específica (opcional)
     */
    public static function clear_rate_limit($user_id, $action = null) {
        if (empty($user_id)) {
            return;
        }
        
        if ($action) {
            $key = "wvp_rate_limit_{$action}_{$user_id}";
            delete_transient($key);
        } else {
            // Limpiar todos los límites del usuario
            global $wpdb;
            $pattern = "wvp_rate_limit_%_{$user_id}";
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            ));
        }
    }
    
    /**
     * Obtener intentos restantes
     * 
     * @param int $user_id ID del usuario
     * @param string $action Acción a verificar
     * @param int $max_attempts Número máximo de intentos
     * @return int Intentos restantes
     */
    public static function get_remaining_attempts($user_id, $action, $max_attempts = 5) {
        if (empty($user_id) || empty($action)) {
            return 0;
        }
        
        $key = "wvp_rate_limit_{$action}_{$user_id}";
        $attempts = get_transient($key) ?: 0;
        
        return max(0, $max_attempts - $attempts);
    }
    
    /**
     * Obtener tiempo de espera restante
     * 
     * @param int $user_id ID del usuario
     * @param string $action Acción a verificar
     * @return int Tiempo en segundos
     */
    public static function get_remaining_time($user_id, $action) {
        if (empty($user_id) || empty($action)) {
            return 0;
        }
        
        $key = "wvp_rate_limit_{$action}_{$user_id}";
        $timeout = get_option("_transient_timeout_{$key}");
        
        if (!$timeout) {
            return 0;
        }
        
        return max(0, $timeout - time());
    }
    
    /**
     * Verificar límite por IP
     * 
     * @param string $ip IP del cliente
     * @param string $action Acción a limitar
     * @param int $max_attempts Número máximo de intentos
     * @param int $time_window Ventana de tiempo en segundos
     * @return bool True si está dentro del límite
     */
    public static function check_ip_rate_limit($ip, $action, $max_attempts = 10, $time_window = 300) {
        if (empty($ip) || empty($action)) {
            return false;
        }
        
        $key = "wvp_ip_rate_limit_{$action}_{$ip}";
        $attempts = get_transient($key) ?: 0;
        
        if ($attempts >= $max_attempts) {
            self::log_ip_rate_limit_exceeded($ip, $action, $attempts);
            return false;
        }
        
        set_transient($key, $attempts + 1, $time_window);
        return true;
    }
    
    /**
     * Limpiar límites por IP
     * 
     * @param string $ip IP del cliente
     * @param string $action Acción específica (opcional)
     */
    public static function clear_ip_rate_limit($ip, $action = null) {
        if (empty($ip)) {
            return;
        }
        
        if ($action) {
            $key = "wvp_ip_rate_limit_{$action}_{$ip}";
            delete_transient($key);
        } else {
            // Limpiar todos los límites de la IP
            global $wpdb;
            $pattern = "wvp_ip_rate_limit_%_{$ip}";
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            ));
        }
    }
    
    /**
     * Verificar límite global
     * 
     * @param string $action Acción a limitar
     * @param int $max_attempts Número máximo de intentos
     * @param int $time_window Ventana de tiempo en segundos
     * @return bool True si está dentro del límite
     */
    public static function check_global_rate_limit($action, $max_attempts = 100, $time_window = 300) {
        if (empty($action)) {
            return false;
        }
        
        $key = "wvp_global_rate_limit_{$action}";
        $attempts = get_transient($key) ?: 0;
        
        if ($attempts >= $max_attempts) {
            self::log_global_rate_limit_exceeded($action, $attempts);
            return false;
        }
        
        set_transient($key, $attempts + 1, $time_window);
        return true;
    }
    
    /**
     * Limpiar límites globales
     * 
     * @param string $action Acción específica (opcional)
     */
    public static function clear_global_rate_limit($action = null) {
        if ($action) {
            $key = "wvp_global_rate_limit_{$action}";
            delete_transient($key);
        } else {
            // Limpiar todos los límites globales
            global $wpdb;
            $pattern = "wvp_global_rate_limit_%";
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            ));
        }
    }
    
    /**
     * Log de límite excedido por usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $action Acción
     * @param int $attempts Número de intentos
     */
    private static function log_rate_limit_exceeded($user_id, $action, $attempts) {
        $message = "Rate limit exceeded for user {$user_id} on action {$action} ({$attempts} attempts)";
        WVP_Security_Validator::log_security_event($message, array(
            'user_id' => $user_id,
            'action' => $action,
            'attempts' => $attempts,
            'ip' => WVP_Security_Validator::get_client_ip()
        ));
    }
    
    /**
     * Log de límite excedido por IP
     * 
     * @param string $ip IP del cliente
     * @param string $action Acción
     * @param int $attempts Número de intentos
     */
    private static function log_ip_rate_limit_exceeded($ip, $action, $attempts) {
        $message = "IP rate limit exceeded for {$ip} on action {$action} ({$attempts} attempts)";
        WVP_Security_Validator::log_security_event($message, array(
            'ip' => $ip,
            'action' => $action,
            'attempts' => $attempts
        ));
    }
    
    /**
     * Log de límite global excedido
     * 
     * @param string $action Acción
     * @param int $attempts Número de intentos
     */
    private static function log_global_rate_limit_exceeded($action, $attempts) {
        $message = "Global rate limit exceeded on action {$action} ({$attempts} attempts)";
        WVP_Security_Validator::log_security_event($message, array(
            'action' => $action,
            'attempts' => $attempts,
            'ip' => WVP_Security_Validator::get_client_ip()
        ));
    }
    
    /**
     * Limpiar todos los límites expirados
     */
    public static function cleanup_expired_limits() {
        global $wpdb;
        
        $wpdb->query("
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE 'wvp_%_rate_limit_%' 
            AND option_name LIKE '%_timeout_%'
            AND option_value < UNIX_TIMESTAMP()
        ");
    }
    
    /**
     * Obtener estadísticas de rate limiting
     * 
     * @param string $action Acción específica (opcional)
     * @return array Estadísticas
     */
    public static function get_rate_limit_stats($action = null) {
        global $wpdb;
        
        $pattern = $action ? "wvp_%_rate_limit_{$action}_%" : "wvp_%_rate_limit_%";
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, option_value FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_name NOT LIKE '%_timeout_%'",
            $pattern
        ));
        
        $stats = array(
            'total_limits' => count($results),
            'by_type' => array(),
            'by_action' => array()
        );
        
        foreach ($results as $result) {
            $parts = explode('_', $result->option_name);
            
            if (count($parts) >= 4) {
                $type = $parts[1]; // user, ip, global
                $action_name = $parts[3];
                
                if (!isset($stats['by_type'][$type])) {
                    $stats['by_type'][$type] = 0;
                }
                $stats['by_type'][$type]++;
                
                if (!isset($stats['by_action'][$action_name])) {
                    $stats['by_action'][$action_name] = 0;
                }
                $stats['by_action'][$action_name]++;
            }
        }
        
        return $stats;
    }
}
