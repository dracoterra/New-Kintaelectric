<?php
/**
 * Clase de utilidades de seguridad para BCV Dólar Tracker
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
     * Verificar capacidades de usuario
     */
    public static function check_capability($capability = 'manage_options') {
        if (!current_user_can($capability)) {
            wp_die(
                __('No tienes permisos suficientes para realizar esta acción.', 'bcv-dolar-tracker'),
                __('Error de Permisos', 'bcv-dolar-tracker'),
                array('response' => 403)
            );
        }
    }

    /**
     * Verificar nonce
     */
    public static function verify_nonce($nonce, $action = 'bcv_admin_nonce') {
        if (!wp_verify_nonce($nonce, $action)) {
            wp_die(
                __('Nonce de seguridad inválido. Por favor, recarga la página e intenta nuevamente.', 'bcv-dolar-tracker'),
                __('Error de Seguridad', 'bcv-dolar-tracker'),
                array('response' => 403)
            );
        }
    }

    /**
     * Verificar nonce para AJAX
     */
    public static function verify_ajax_nonce($nonce, $action = 'bcv_admin_nonce') {
        if (!wp_verify_nonce($nonce, $action)) {
            wp_send_json_error(array(
                'message' => __('Nonce de seguridad inválido.', 'bcv-dolar-tracker')
            ));
        }
    }

    /**
     * Sanitizar entrada de texto
     */
    public static function sanitize_text($text) {
        return sanitize_text_field($text);
    }

    /**
     * Sanitizar entrada de número
     */
    public static function sanitize_number($number) {
        return floatval($number);
    }

    /**
     * Sanitizar entrada de email
     */
    public static function sanitize_email($email) {
        return sanitize_email($email);
    }

    /**
     * Sanitizar entrada de URL
     */
    public static function sanitize_url($url) {
        return esc_url_raw($url);
    }

    /**
     * Sanitizar entrada de texto multilínea
     */
    public static function sanitize_textarea($text) {
        return sanitize_textarea_field($text);
    }

    /**
     * Validar rango de número
     */
    public static function validate_number_range($number, $min = 0, $max = 999999) {
        $number = floatval($number);
        return ($number >= $min && $number <= $max) ? $number : $min;
    }

    /**
     * Validar formato de fecha
     */
    public static function validate_date($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Escapar salida HTML
     */
    public static function escape_html($text) {
        return esc_html($text);
    }

    /**
     * Escapar salida de atributo
     */
    public static function escape_attr($text) {
        return esc_attr($text);
    }

    /**
     * Escapar salida de URL
     */
    public static function escape_url($url) {
        return esc_url($url);
    }

    /**
     * Escapar salida de JavaScript
     */
    public static function escape_js($text) {
        return esc_js($text);
    }

    /**
     * Generar nonce para formularios
     */
    public static function get_nonce_field($action = 'bcv_admin_nonce') {
        return wp_nonce_field($action, '_wpnonce', true, false);
    }

    /**
     * Generar nonce para AJAX
     */
    public static function get_ajax_nonce($action = 'bcv_admin_nonce') {
        return wp_create_nonce($action);
    }

    /**
     * Log de eventos de seguridad
     */
    public static function log_security_event($event, $details = '') {
        $log_message = sprintf(
            '[BCV Security] %s - %s - IP: %s - User: %s - Details: %s',
            current_time('Y-m-d H:i:s'),
            $event,
            self::get_client_ip(),
            get_current_user_id(),
            $details
        );
        
        error_log($log_message);
    }

    /**
     * Obtener IP del cliente
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
     * Validar entrada de formulario completa
     */
    public static function validate_form_data($data, $rules) {
        $validated = array();
        $errors = array();

        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : '';
            
            // Sanitizar según el tipo
            switch ($rule['type']) {
                case 'text':
                    $value = self::sanitize_text($value);
                    break;
                case 'number':
                    $value = self::sanitize_number($value);
                    if (isset($rule['min']) || isset($rule['max'])) {
                        $value = self::validate_number_range($value, $rule['min'] ?? 0, $rule['max'] ?? 999999);
                    }
                    break;
                case 'email':
                    $value = self::sanitize_email($value);
                    if (!is_email($value)) {
                        $errors[$field] = __('Email inválido', 'bcv-dolar-tracker');
                    }
                    break;
                case 'url':
                    $value = self::sanitize_url($value);
                    break;
                case 'textarea':
                    $value = self::sanitize_textarea($value);
                    break;
            }

            // Validar requerido
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = sprintf(__('El campo %s es requerido', 'bcv-dolar-tracker'), $rule['label'] ?? $field);
            }

            $validated[$field] = $value;
        }

        return array(
            'data' => $validated,
            'errors' => $errors
        );
    }
}
