<?php
/**
 * Configuración de seguridad para BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Configuración de seguridad del plugin
define('BCV_SECURITY_CONFIG', array(
    // Rate limiting
    'rate_limit_enabled' => true,
    'max_requests_per_minute' => 10,
    'max_requests_per_hour' => 100,
    
    // Nonce security
    'nonce_lifetime' => 24 * 60 * 60, // 24 horas
    'nonce_rotation' => true,
    
    // Input validation
    'strict_validation' => true,
    'max_input_length' => 255,
    'allowed_html_tags' => array('p', 'br', 'strong', 'em', 'span'),
    
    // Database security
    'sql_injection_protection' => true,
    'prepared_statements_only' => true,
    'max_query_time' => 30, // segundos
    
    // File security
    'allowed_file_types' => array('php', 'css', 'js', 'json'),
    'max_file_size' => 1024 * 1024, // 1MB
    
    // Network security
    'ssl_verify' => true,
    'max_redirects' => 3,
    'timeout' => 30,
    
    // Logging
    'security_logging' => true,
    'log_failed_attempts' => true,
    'log_successful_actions' => false,
    
    // Debug mode restrictions
    'debug_mode_only' => array(
        'dev_tools',
        'test_functions',
        'debug_logging'
    ),
    
    // User capabilities
    'required_capabilities' => array(
        'manage_options' => array('admin_functions'),
        'edit_posts' => array('view_data'),
        'read' => array('public_functions')
    ),
    
    // IP restrictions (opcional)
    'allowed_ips' => array(), // Vacío = sin restricciones
    'blocked_ips' => array(),
    
    // Content Security Policy
    'csp_enabled' => true,
    'csp_directives' => array(
        'default-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline'",
        'style-src' => "'self' 'unsafe-inline'",
        'img-src' => "'self' data: https:",
        'connect-src' => "'self'",
        'font-src' => "'self'",
        'object-src' => "'none'",
        'media-src' => "'self'",
        'frame-src' => "'none'"
    )
));

// Función para obtener configuración de seguridad
function bcv_get_security_config($key = null) {
    $config = BCV_SECURITY_CONFIG;
    
    if ($key === null) {
        return $config;
    }
    
    return isset($config[$key]) ? $config[$key] : null;
}

// Función para verificar si una IP está permitida
function bcv_is_ip_allowed($ip = null) {
    if ($ip === null) {
        $ip = BCV_Security::get_client_ip();
    }
    
    $config = bcv_get_security_config();
    $allowed_ips = $config['allowed_ips'];
    $blocked_ips = $config['blocked_ips'];
    
    // Si hay IPs bloqueadas, verificar
    if (!empty($blocked_ips) && in_array($ip, $blocked_ips)) {
        return false;
    }
    
    // Si hay IPs permitidas, verificar
    if (!empty($allowed_ips) && !in_array($ip, $allowed_ips)) {
        return false;
    }
    
    return true;
}

// Función para aplicar CSP headers
function bcv_apply_csp_headers() {
    $config = bcv_get_security_config();
    
    if (!$config['csp_enabled']) {
        return;
    }
    
    $csp_directives = $config['csp_directives'];
    $csp_string = '';
    
    foreach ($csp_directives as $directive => $value) {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        $csp_string .= $directive . ' ' . $value . '; ';
    }
    
    header('Content-Security-Policy: ' . trim($csp_string));
}

// Hook para aplicar CSP en admin
add_action('admin_init', 'bcv_apply_csp_headers');

// Función para limpiar logs antiguos
function bcv_cleanup_security_logs() {
    $log_file = WP_CONTENT_DIR . '/debug.log';
    
    if (!file_exists($log_file)) {
        return;
    }
    
    $max_size = 10 * 1024 * 1024; // 10MB
    $max_age = 30 * 24 * 60 * 60; // 30 días
    
    $file_size = filesize($log_file);
    $file_age = time() - filemtime($log_file);
    
    if ($file_size > $max_size || $file_age > $max_age) {
        // Rotar log
        $backup_file = $log_file . '.' . date('Y-m-d');
        if (file_exists($backup_file)) {
            unlink($backup_file);
        }
        rename($log_file, $backup_file);
        
        // Crear nuevo log
        touch($log_file);
        chmod($log_file, 0644);
    }
}

// Programar limpieza de logs
if (!wp_next_scheduled('bcv_cleanup_security_logs')) {
    wp_schedule_event(time(), 'daily', 'bcv_cleanup_security_logs');
}

add_action('bcv_cleanup_security_logs', 'bcv_cleanup_security_logs');
