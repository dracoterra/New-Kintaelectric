<?php
/**
 * Performance - Optimización de rendimiento
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para optimización de rendimiento
 */
class WVS_Performance {
    
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
        // Optimización de consultas
        add_action('init', array($this, 'optimize_queries'));
        
        // Cache de objetos
        add_action('wp_loaded', array($this, 'init_object_cache'));
        
        // Optimización de assets
        add_action('wp_enqueue_scripts', array($this, 'optimize_assets'));
    }
    
    /**
     * Optimizar consultas
     */
    public function optimize_queries() {
        // Solo si no estamos en admin
        if (is_admin()) {
            return;
        }
        
        // Optimizar consultas de WooCommerce
        add_action('woocommerce_product_query', array($this, 'optimize_product_query'));
    }
    
    /**
     * Optimizar consulta de productos
     * 
     * @param WP_Query $query
     */
    public function optimize_product_query($query) {
        // Añadir índices necesarios
        $query->set('meta_query', array(
            'relation' => 'AND',
            array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            )
        ));
    }
    
    /**
     * Inicializar cache de objetos
     */
    public function init_object_cache() {
        // Cache para tasa BCV
        add_filter('wvs_get_bcv_rate', array($this, 'cache_bcv_rate'));
    }
    
    /**
     * Cache para tasa BCV
     * 
     * @param float $rate
     * @return float
     */
    public function cache_bcv_rate($rate) {
        $cache_key = 'wvs_bcv_rate';
        $cached_rate = get_transient($cache_key);
        
        if ($cached_rate === false) {
            // Cache por 30 minutos
            set_transient($cache_key, $rate, 1800);
            return $rate;
        }
        
        return $cached_rate;
    }
    
    /**
     * Optimizar assets
     */
    public function optimize_assets() {
        // Solo en frontend
        if (is_admin()) {
            return;
        }
        
        // Minificar CSS si no está en debug
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            add_filter('wvs_css_url', array($this, 'minify_css_url'));
        }
    }
    
    /**
     * Minificar URL de CSS
     * 
     * @param string $url
     * @return string
     */
    public function minify_css_url($url) {
        return str_replace('.css', '.min.css', $url);
    }
}
