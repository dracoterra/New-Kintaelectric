<?php
/**
 * Helper - Funciones de ayuda
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase con funciones de ayuda
 */
class WVS_Helper {
    
    /**
     * Formatear precio en VES
     * 
     * @param float $amount
     * @return string
     */
    public static function format_ves_price($amount) {
        return number_format($amount, 2, ',', '.') . ' Bs.';
    }
    
    /**
     * Formatear precio en USD
     * 
     * @param float $amount
     * @return string
     */
    public static function format_usd_price($amount) {
        return '$' . number_format($amount, 2, '.', ',');
    }
    
    /**
     * Validar RIF venezolano
     * 
     * @param string $rif
     * @return bool
     */
    public static function validate_rif($rif) {
        $pattern = '/^[JGVEP]-[0-9]{8}-[0-9]$/';
        return preg_match($pattern, $rif);
    }
    
    /**
     * Validar cédula venezolana
     * 
     * @param string $cedula
     * @return bool
     */
    public static function validate_cedula($cedula) {
        $pattern = '/^[0-9]{7,8}$/';
        return preg_match($pattern, $cedula);
    }
    
    /**
     * Obtener estados de Venezuela
     * 
     * @return array
     */
    public static function get_venezuelan_states() {
        return array(
            'Amazonas' => 'Amazonas',
            'Anzoátegui' => 'Anzoátegui',
            'Apure' => 'Apure',
            'Aragua' => 'Aragua',
            'Barinas' => 'Barinas',
            'Bolívar' => 'Bolívar',
            'Carabobo' => 'Carabobo',
            'Cojedes' => 'Cojedes',
            'Delta Amacuro' => 'Delta Amacuro',
            'Distrito Capital' => 'Distrito Capital',
            'Falcón' => 'Falcón',
            'Guárico' => 'Guárico',
            'Lara' => 'Lara',
            'Mérida' => 'Mérida',
            'Miranda' => 'Miranda',
            'Monagas' => 'Monagas',
            'Nueva Esparta' => 'Nueva Esparta',
            'Portuguesa' => 'Portuguesa',
            'Sucre' => 'Sucre',
            'Táchira' => 'Táchira',
            'Trujillo' => 'Trujillo',
            'Vargas' => 'Vargas',
            'Yaracuy' => 'Yaracuy',
            'Zulia' => 'Zulia'
        );
    }
    
    /**
     * Obtener municipios por estado
     * 
     * @param string $state
     * @return array
     */
    public static function get_municipalities_by_state($state) {
        // Implementación simplificada - en producción debería tener todos los municipios
        $municipalities = array(
            'Distrito Capital' => array(
                'Libertador' => 'Libertador'
            ),
            'Miranda' => array(
                'Baruta' => 'Baruta',
                'Chacao' => 'Chacao',
                'El Hatillo' => 'El Hatillo',
                'Sucre' => 'Sucre'
            ),
            'Zulia' => array(
                'Maracaibo' => 'Maracaibo',
                'San Francisco' => 'San Francisco'
            )
        );
        
        return $municipalities[$state] ?? array();
    }
    
    /**
     * Generar número de control SENIAT
     * 
     * @param int $order_id
     * @param string $date
     * @return string
     */
    public static function generate_control_number($order_id, $date) {
        $date_formatted = date('Y-m-d', strtotime($date));
        return $date_formatted . '-' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Calcular IGTF
     * 
     * @param float $amount
     * @param float $rate
     * @return float
     */
    public static function calculate_igtf($amount, $rate = 3.0) {
        return $amount * ($rate / 100);
    }
    
    /**
     * Calcular IVA
     * 
     * @param float $amount
     * @param float $rate
     * @return float
     */
    public static function calculate_iva($amount, $rate = 16.0) {
        return $amount * ($rate / 100);
    }
    
    /**
     * Obtener información del sistema
     * 
     * @return array
     */
    public static function get_system_info() {
        global $wp_version;
        
        return array(
            'wordpress_version' => $wp_version,
            'woocommerce_version' => defined('WC_VERSION') ? WC_VERSION : 'N/A',
            'php_version' => PHP_VERSION,
            'plugin_version' => WVS_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'mysql_version' => $GLOBALS['wpdb']->db_version(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        );
    }
    
    /**
     * Verificar compatibilidad
     * 
     * @return array
     */
    public static function check_compatibility() {
        $issues = array();
        
        // Verificar PHP
        if (version_compare(PHP_VERSION, WVS_MIN_PHP_VERSION, '<')) {
            $issues[] = sprintf(
                __('PHP %s o superior requerido. Versión actual: %s', 'wvs'),
                WVS_MIN_PHP_VERSION,
                PHP_VERSION
            );
        }
        
        // Verificar WordPress
        if (version_compare(get_bloginfo('version'), WVS_MIN_WP_VERSION, '<')) {
            $issues[] = sprintf(
                __('WordPress %s o superior requerido. Versión actual: %s', 'wvs'),
                WVS_MIN_WP_VERSION,
                get_bloginfo('version')
            );
        }
        
        // Verificar WooCommerce
        if (!class_exists('WooCommerce')) {
            $issues[] = __('WooCommerce no está instalado', 'wvs');
        } elseif (version_compare(WC()->version, WVS_MIN_WC_VERSION, '<')) {
            $issues[] = sprintf(
                __('WooCommerce %s o superior requerido. Versión actual: %s', 'wvs'),
                WVS_MIN_WC_VERSION,
                WC()->version
            );
        }
        
        return $issues;
    }
}
