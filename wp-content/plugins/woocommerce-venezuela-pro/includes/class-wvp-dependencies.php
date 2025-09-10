<?php
/**
 * Clase para verificar dependencias del plugin
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Dependencies {
    
    /**
     * Plugins requeridos
     * 
     * @var array
     */
    private $required_plugins = array(
        'woocommerce' => array(
            'name' => 'WooCommerce',
            'file' => 'woocommerce/woocommerce.php',
            'version' => '5.0.0'
        ),
        'bcv-dolar' => array(
            'name' => 'BCV Dólar Tracker',
            'file' => 'bcv-dolar-tracker/bcv-dolar-tracker.php',
            'version' => '1.0.0'
        )
    );
    
    /**
     * Verificar todas las dependencias
     * 
     * @return bool True si todas las dependencias están disponibles
     */
    public function check_dependencies() {
        $all_ready = true;
        
        foreach ($this->required_plugins as $plugin_key => $plugin_data) {
            if (!$this->is_plugin_ready($plugin_key)) {
                $all_ready = false;
                $this->show_admin_notice($plugin_key);
            }
        }
        
        return $all_ready;
    }
    
    /**
     * Verificar si un plugin está listo
     * 
     * @param string $plugin_key Clave del plugin
     * @return bool True si está listo
     */
    private function is_plugin_ready($plugin_key) {
        switch ($plugin_key) {
            case 'woocommerce':
                return $this->is_woocommerce_ready();
            case 'bcv-dolar':
                return $this->is_bcv_dolar_ready();
            default:
                return false;
        }
    }
    
    /**
     * Verificar si WooCommerce está listo
     * 
     * @return bool True si está listo
     */
    private function is_woocommerce_ready() {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $wc_version = WC()->version;
        $required_version = $this->required_plugins['woocommerce']['version'];
        
        return version_compare($wc_version, $required_version, '>=');
    }
    
    /**
     * Verificar si BCV Dólar Tracker está listo
     * 
     * @return bool True si está listo
     */
    private function is_bcv_dolar_ready() {
        if (!$this->is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
            return false;
        }
        
        // Verificar si la tabla existe
        global $wpdb;
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        return $table_exists;
    }
    
    /**
     * Verificar si un plugin está activo
     * 
     * @param string $plugin_file Archivo del plugin
     * @return bool True si está activo
     */
    private function is_plugin_active($plugin_file) {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        return is_plugin_active($plugin_file);
    }
    
    /**
     * Mostrar aviso de administración
     * 
     * @param string $plugin_key Clave del plugin
     */
    private function show_admin_notice($plugin_key) {
        $plugin_data = $this->required_plugins[$plugin_key];
        
        add_action('admin_notices', function() use ($plugin_data) {
            echo '<div class="notice notice-error">';
            echo '<p><strong>WooCommerce Venezuela Pro:</strong> ';
            echo sprintf(
                __('Se requiere %s versión %s o superior para funcionar.', 'wvp'),
                $plugin_data['name'],
                $plugin_data['version']
            );
            echo '</p>';
            echo '</div>';
        });
    }
}