<?php
/**
 * Clase para integrar con BCV Dólar Tracker
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_BCV_Integrator {
    
    /**
     * Obtener la tasa de cambio actual del BCV
     * 
     * @return float|null Tasa de cambio o null si no está disponible
     */
    public static function get_rate() {
        global $wpdb;
        
        // Verificar si la tabla existe
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            error_log('WVP: Tabla BCV no existe');
            return null;
        }
        
        // Obtener el último precio
        $latest_price = $wpdb->get_var(
            "SELECT precio FROM $table_name ORDER BY datatime DESC LIMIT 1"
        );
        
        if ($latest_price === null) {
            error_log('WVP: No hay datos en la tabla BCV');
            return null;
        }
        
        return floatval($latest_price);
    }
    
    /**
     * Convertir USD a VES
     * 
     * @param float $usd_amount Cantidad en USD
     * @param float $rate Tasa de cambio (opcional)
     * @return float|null Cantidad en VES o null si hay error
     */
    public static function convert_to_ves($usd_amount, $rate = null) {
        if ($rate === null) {
            $rate = self::get_rate();
        }
        
        if ($rate === null || $rate <= 0) {
            return null;
        }
        
        return $usd_amount * $rate;
    }
    
    /**
     * Formatear precio en bolívares
     * 
     * @param float $ves_amount Cantidad en VES
     * @return string Precio formateado
     */
    public static function format_ves_price($ves_amount) {
        return number_format($ves_amount, 2, ",", ".");
    }
}