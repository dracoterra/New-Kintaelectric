<?php
/**
 * Config Manager - Gestión de configuración
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestión de configuración
 */
class WVS_Config_Manager {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private $plugin;
    
    /**
     * Configuración por defecto
     * 
     * @var array
     */
    private $default_config = array(
        'currency_display' => 'dual',
        'default_currency' => 'USD',
        'bcv_integration' => true,
        'igtf_rate' => 3.0,
        'help_system' => true,
        'notifications' => true,
        'debug_mode' => false,
        'cache_duration' => 3600
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Suite::get_instance();
    }
    
    /**
     * Obtener configuración
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null) {
        $config = get_option('wvs_settings', $this->default_config);
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
    
    /**
     * Actualizar configuración
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value) {
        $config = $this->get();
        $config[$key] = $value;
        
        return update_option('wvs_settings', $config);
    }
    
    /**
     * Obtener configuración por defecto
     * 
     * @return array
     */
    public function get_defaults() {
        return $this->default_config;
    }
    
    /**
     * Resetear configuración
     * 
     * @return bool
     */
    public function reset() {
        return update_option('wvs_settings', $this->default_config);
    }
}
