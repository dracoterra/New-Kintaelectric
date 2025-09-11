<?php
/**
 * Clase de Validación de Seguridad para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Security_Validator {
    
    /**
     * Validar nonce CSRF
     * 
     * @param string $nonce Nonce a validar
     * @param string $action Acción asociada al nonce
     * @return bool True si el nonce es válido
     */
    public static function validate_nonce($nonce, $action) {
        if (empty($nonce) || empty($action)) {
            return false;
        }
        
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Sanitizar datos de entrada
     * 
     * @param mixed $data Datos a sanitizar
     * @param string $type Tipo de sanitización
     * @return mixed Datos sanitizados
     */
    public static function sanitize_input($data, $type = 'text') {
        if (empty($data)) {
            return '';
        }
        
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
            case 'textarea':
                return sanitize_textarea_field($data);
            case 'key':
                return sanitize_key($data);
            default:
                return sanitize_text_field($data);
        }
    }
    
    /**
     * Validar formato de confirmación de pago
     * 
     * @param string $confirmation Número de confirmación
     * @return bool True si el formato es válido
     */
    public static function validate_confirmation($confirmation) {
        if (empty($confirmation)) {
            return false;
        }
        
        // Permitir solo caracteres alfanuméricos y guiones, 6-20 caracteres
        return preg_match('/^[A-Z0-9\-]{6,20}$/', strtoupper($confirmation));
    }
    
    /**
     * Validar formato de cédula/RIF venezolano
     * 
     * @param string $cedula_rif Cédula o RIF
     * @return bool True si el formato es válido
     */
    public static function validate_cedula_rif($cedula_rif) {
        if (empty($cedula_rif)) {
            return false;
        }
        
        // Patrón para V-12345678 o J-12345678-9
        $pattern = '/^[VJ]-[0-9]{7,8}(-[0-9])?$/';
        return preg_match($pattern, strtoupper($cedula_rif));
    }
    
    /**
     * Validar permisos del usuario
     * 
     * @param string $capability Capacidad requerida
     * @return bool True si el usuario tiene permisos
     */
    public static function validate_user_permissions($capability = 'read') {
        if (empty($capability)) {
            return false;
        }
        
        return current_user_can($capability);
    }
    
    /**
     * Validar estado del pedido
     * 
     * @param WC_Order $order Pedido a validar
     * @param string $expected_status Estado esperado
     * @return bool True si el estado es correcto
     */
    public static function validate_order_status($order, $expected_status = 'pending') {
        if (!$order || !is_a($order, 'WC_Order')) {
            return false;
        }
        
        return $order->get_status() === $expected_status;
    }
    
    /**
     * Validar que el pedido pertenece al usuario actual
     * 
     * @param WC_Order $order Pedido a validar
     * @return bool True si el pedido pertenece al usuario
     */
    public static function validate_order_ownership($order) {
        if (!$order || !is_a($order, 'WC_Order')) {
            return false;
        }
        
        // Si el usuario es administrador, permitir
        if (current_user_can('manage_woocommerce')) {
            return true;
        }
        
        // Verificar que el pedido pertenece al usuario actual
        return $order->get_customer_id() === get_current_user_id();
    }
    
    /**
     * Validar monto de pago
     * 
     * @param float $amount Monto a validar
     * @param float $min_amount Monto mínimo
     * @param float $max_amount Monto máximo
     * @return bool True si el monto es válido
     */
    public static function validate_amount($amount, $min_amount = 0, $max_amount = 0) {
        if (!is_numeric($amount) || $amount < 0) {
            return false;
        }
        
        if ($min_amount > 0 && $amount < $min_amount) {
            return false;
        }
        
        if ($max_amount > 0 && $amount > $max_amount) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar email
     * 
     * @param string $email Email a validar
     * @return bool True si el email es válido
     */
    public static function validate_email($email) {
        if (empty($email)) {
            return false;
        }
        
        return is_email($email);
    }
    
    /**
     * Validar teléfono venezolano
     * 
     * @param string $phone Teléfono a validar
     * @return bool True si el teléfono es válido
     */
    public static function validate_venezuelan_phone($phone) {
        if (empty($phone)) {
            return false;
        }
        
        // Patrón para teléfonos venezolanos: +58xxxxxxxxx o 0xxxxxxxxx
        $pattern = '/^(\+58|0)[0-9]{10}$/';
        return preg_match($pattern, preg_replace('/[^0-9+]/', '', $phone));
    }
    
    /**
     * Escapar salida HTML
     * 
     * @param string $output Salida a escapar
     * @return string Salida escapada
     */
    public static function escape_output($output) {
        return esc_html($output);
    }
    
    /**
     * Escapar atributos HTML
     * 
     * @param string $output Atributo a escapar
     * @return string Atributo escapado
     */
    public static function escape_attribute($output) {
        return esc_attr($output);
    }
    
    /**
     * Validar archivo subido
     * 
     * @param array $file Archivo a validar
     * @param array $allowed_types Tipos permitidos
     * @param int $max_size Tamaño máximo en bytes
     * @return bool True si el archivo es válido
     */
    public static function validate_uploaded_file($file, $allowed_types = array(), $max_size = 0) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        if (!empty($allowed_types) && !in_array($file['type'], $allowed_types)) {
            return false;
        }
        
        if ($max_size > 0 && $file['size'] > $max_size) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log de seguridad
     * 
     * @param string $message Mensaje a loggear
     * @param array $context Contexto adicional
     */
    public static function log_security_event($message, $context = array()) {
        $log_message = '[WVP Security] ' . $message;
        
        if (!empty($context)) {
            $log_message .= ' Context: ' . wp_json_encode($context);
        }
        
        error_log($log_message);
    }
    
    /**
     * Verificar si una IP está en lista negra
     * 
     * @param string $ip IP a verificar
     * @return bool True si la IP está bloqueada
     */
    public static function is_ip_blocked($ip = null) {
        if (empty($ip)) {
            $ip = self::get_client_ip();
        }
        
        $blocked_ips = get_option('wvp_blocked_ips', array());
        
        return in_array($ip, $blocked_ips);
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
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
