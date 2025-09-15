<?php
/**
 * Clase de seguridad para BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Security {
    
    /**
     * Validar y sanitizar datos de entrada
     * 
     * @param mixed $data Datos a validar
     * @param string $type Tipo de validación
     * @return mixed Datos sanitizados o false si son inválidos
     */
    public static function sanitize_input($data, $type = 'text') {
        switch ($type) {
            case 'text':
                return sanitize_text_field($data);
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            case 'int':
                return intval($data);
            case 'float':
                return floatval($data);
            case 'price':
                $price = floatval($data);
                return ($price > 0 && $price < 1000000) ? $price : false;
            case 'date':
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data)) {
                    return sanitize_text_field($data);
                }
                return false;
            default:
                return sanitize_text_field($data);
        }
    }
    
    /**
     * Validar nonce con verificación adicional
     * 
     * @param string $nonce Nonce a verificar
     * @param string $action Acción del nonce
     * @return bool True si es válido, False en caso contrario
     */
    public static function verify_nonce($nonce, $action) {
        if (!wp_verify_nonce($nonce, $action)) {
            return false;
        }
        
        // Verificación adicional de referer
        if (!check_ajax_referer($action, 'nonce', false)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar capacidades de usuario
     * 
     * @param string $capability Capacidad requerida
     * @return bool True si tiene permisos, False en caso contrario
     */
    public static function check_capability($capability = 'manage_options') {
        if (!current_user_can($capability)) {
            return false;
        }
        
        // Verificación adicional de usuario activo
        if (!is_user_logged_in()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Rate limiting para acciones específicas
     * 
     * @param string $action Acción a limitar
     * @param int $limit Tiempo límite en segundos
     * @param int $user_id ID del usuario (opcional)
     * @return bool True si puede continuar, False si está limitado
     */
    public static function check_rate_limit($action, $limit = 60, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $transient_key = 'bcv_rate_limit_' . $action . '_' . $user_id;
        $last_action = get_transient($transient_key);
        
        if ($last_action && (time() - $last_action) < $limit) {
            return false;
        }
        
        set_transient($transient_key, time(), $limit);
        return true;
    }
    
    /**
     * Log de seguridad
     * 
     * @param string $message Mensaje a registrar
     * @param string $level Nivel de log
     */
    public static function log_security_event($message, $level = 'info') {
        $log_message = sprintf(
            '[BCV Security] [%s] %s - User: %d, IP: %s',
            strtoupper($level),
            $message,
            get_current_user_id(),
            self::get_client_ip()
        );
        
        error_log($log_message);
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string IP del cliente
     */
    public static function get_client_ip() {
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
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * Validar datos de configuración del cron
     * 
     * @param array $settings Configuración a validar
     * @return array|false Configuración validada o false si es inválida
     */
    public static function validate_cron_settings($settings) {
        if (!is_array($settings)) {
            return false;
        }
        
        $validated = array();
        
        // Validar enabled
        $validated['enabled'] = isset($settings['enabled']) ? (bool) $settings['enabled'] : false;
        
        // Validar hours
        $hours = isset($settings['hours']) ? intval($settings['hours']) : 1;
        if ($hours < 0 || $hours > 24) {
            return false;
        }
        $validated['hours'] = $hours;
        
        // Validar minutes
        $minutes = isset($settings['minutes']) ? intval($settings['minutes']) : 0;
        if ($minutes < 0 || $minutes > 59) {
            return false;
        }
        $validated['minutes'] = $minutes;
        
        // Validar seconds
        $seconds = isset($settings['seconds']) ? intval($settings['seconds']) : 0;
        if ($seconds < 0 || $seconds > 59) {
            return false;
        }
        $validated['seconds'] = $seconds;
        
        // Mínimo 1 minuto
        if ($validated['hours'] == 0 && $validated['minutes'] == 0 && $validated['seconds'] < 60) {
            $validated['seconds'] = 60;
        }
        
        return $validated;
    }
    
    /**
     * Sanitizar salida HTML
     * 
     * @param string $output Contenido a sanitizar
     * @return string Contenido sanitizado
     */
    public static function sanitize_output($output) {
        return wp_kses_post($output);
    }
    
    /**
     * Validar URL
     * 
     * @param string $url URL a validar
     * @return bool True si es válida, False en caso contrario
     */
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Generar nonce seguro
     * 
     * @param string $action Acción para el nonce
     * @return string Nonce generado
     */
    public static function create_nonce($action) {
        return wp_create_nonce($action);
    }
    
    /**
     * Verificar si el plugin está en modo debug
     * 
     * @return bool True si está en modo debug, False en caso contrario
     */
    public static function is_debug_mode() {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
    
    /**
     * Obtener configuración de seguridad
     * 
     * @return array Configuración de seguridad
     */
    public static function get_security_config() {
        return array(
            'rate_limit_enabled' => true,
            'max_requests_per_minute' => 10,
            'nonce_lifetime' => 24 * 60 * 60, // 24 horas
            'debug_mode' => self::is_debug_mode(),
            'ssl_verify' => true,
            'max_redirects' => 3
        );
    }
}
