<?php
/**
 * Security - Sistema de seguridad
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el sistema de seguridad
 */
class WVS_Security {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Suite::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Validación de nonces
        add_action('wp_ajax_wvs_', array($this, 'validate_ajax_nonce'));
        
        // Rate limiting
        add_action('init', array($this, 'check_rate_limit'));
        
        // Sanitización de datos
        add_filter('wvs_sanitize_input', array($this, 'sanitize_input'));
    }
    
    /**
     * Validar nonce AJAX
     */
    public function validate_ajax_nonce() {
        if (!wp_verify_nonce($_POST['nonce'], 'wvs_admin_nonce')) {
            wp_die('Security check failed');
        }
    }
    
    /**
     * Verificar rate limit
     */
    public function check_rate_limit() {
        // Solo aplicar rate limiting en admin y para usuarios no autenticados
        if (!is_admin() || is_user_logged_in()) {
            return;
        }
        
        $ip = $this->get_client_ip();
        $key = 'wvs_rate_limit_' . md5($ip);
        
        $attempts = get_transient($key);
        if ($attempts === false) {
            set_transient($key, 1, 300); // 5 minutos
        } else {
            if ($attempts >= 50) { // Aumentado a 50 intentos por 5 minutos
                wp_die('Rate limit exceeded');
            }
            set_transient($key, $attempts + 1, 300);
        }
    }
    
    /**
     * Sanitizar entrada
     * 
     * @param mixed $input
     * @return mixed
     */
    public function sanitize_input($input) {
        if (is_string($input)) {
            return sanitize_text_field($input);
        } elseif (is_array($input)) {
            return array_map(array($this, 'sanitize_input'), $input);
        }
        return $input;
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string
     */
    private function get_client_ip() {
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
}
