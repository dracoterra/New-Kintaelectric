<?php
/**
 * Compatibility - Verificación de compatibilidad
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para verificación de compatibilidad
 */
class WVS_Compatibility {
    
    /**
     * Verificar compatibilidad del sistema
     * 
     * @return bool
     */
    public static function check_system_compatibility() {
        $issues = WVS_Helper::check_compatibility();
        return empty($issues);
    }
    
    /**
     * Verificar compatibilidad con HPOS
     * 
     * @return bool
     */
    public static function check_hpos_compatibility() {
        if (!class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            return false;
        }
        
        return \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled('custom_order_tables');
    }
    
    /**
     * Verificar compatibilidad con BCV Dólar Tracker
     * 
     * @return bool
     */
    public static function check_bcv_tracker_compatibility() {
        return is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php');
    }
    
    /**
     * Obtener reporte de compatibilidad
     * 
     * @return array
     */
    public static function get_compatibility_report() {
        return array(
            'system' => self::check_system_compatibility(),
            'hpos' => self::check_hpos_compatibility(),
            'bcv_tracker' => self::check_bcv_tracker_compatibility(),
            'issues' => WVS_Helper::check_compatibility()
        );
    }
}
