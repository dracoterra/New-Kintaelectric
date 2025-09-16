<?php
/**
 * Calculadora de precios optimizada
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Price_Calculator {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Caché de cálculos
     * 
     * @var array
     */
    private $cache;
    
    /**
     * Duración del caché en segundos
     * 
     * @var int
     */
    private $cache_duration;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->cache = array();
        $this->cache_duration = get_option('wvp_cache_duration', 3600); // 1 hora por defecto
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Limpiar caché cuando se actualiza la tasa BCV
        add_action('wvp_bcv_rate_updated', array($this, 'clear_cache'));
        
        // Limpiar caché cuando se actualiza un producto
        add_action('woocommerce_update_product', array($this, 'clear_product_cache'));
        
        // Limpiar caché cuando se actualiza el carrito
        add_action('woocommerce_cart_updated', array($this, 'clear_cart_cache'));
        
        // Hook para limpiar caché periódicamente
        add_action('wvp_cleanup_cache', array($this, 'cleanup_expired_cache'));
        
        // Programar limpieza de caché
        if (!wp_next_scheduled('wvp_cleanup_cache')) {
            wp_schedule_event(time(), 'hourly', 'wvp_cleanup_cache');
        }
    }
    
    /**
     * Calcular precio en VES con caché
     */
    public function calculate_ves_price($usd_price, $rate = null) {
        if (!$usd_price || $usd_price <= 0) {
            return 0;
        }
        
        // Obtener tasa si no se proporciona
        if (!$rate) {
            $rate = $this->get_bcv_rate();
        }
        
        if (!$rate || $rate <= 0) {
            return 0;
        }
        
        // Generar clave de caché
        $cache_key = 'ves_price_' . md5($usd_price . '_' . $rate);
        
        // Verificar caché
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        // Calcular precio en VES
        $ves_price = $usd_price * $rate;
        
        // Aplicar redondeo
        $ves_price = round($ves_price, 2);
        
        // Guardar en caché
        $this->cache[$cache_key] = $ves_price;
        
        // Guardar en caché persistente
        set_transient($cache_key, $ves_price, $this->cache_duration);
        
        return $ves_price;
    }
    
    /**
     * Calcular IGTF con caché
     */
    public function calculate_igtf($amount, $payment_method = null) {
        if (!$amount || $amount <= 0) {
            return 0;
        }
        
        // Generar clave de caché
        $cache_key = 'igtf_' . md5($amount . '_' . $payment_method);
        
        // Verificar caché
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        // Calcular IGTF
        $igtf_amount = 0;
        
        if (class_exists('WVP_IGTF_Manager')) {
            $igtf_manager = new WVP_IGTF_Manager();
            $igtf_amount = $igtf_manager->calculate_igtf($amount, $payment_method);
        }
        
        // Guardar en caché
        $this->cache[$cache_key] = $igtf_amount;
        
        // Guardar en caché persistente
        set_transient($cache_key, $igtf_amount, $this->cache_duration);
        
        return $igtf_amount;
    }
    
    /**
     * Calcular total del carrito con conversión
     */
    public function calculate_cart_total_with_conversion() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return array(
                'usd_total' => 0,
                'ves_total' => 0,
                'rate' => 0,
                'igtf_amount' => 0,
                'total_with_igtf' => 0
            );
        }
        
        // Generar clave de caché
        $cart_hash = $cart->get_cart_hash();
        $cache_key = 'cart_total_' . md5($cart_hash);
        
        // Verificar caché
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        // Calcular totales
        $usd_total = $cart->get_total('raw');
        $rate = $this->get_bcv_rate();
        $ves_total = $this->calculate_ves_price($usd_total, $rate);
        
        // Calcular IGTF
        $payment_method = WC()->session->get('chosen_payment_method');
        $igtf_amount = $this->calculate_igtf($usd_total, $payment_method);
        
        // Total con IGTF
        $total_with_igtf = $usd_total + $igtf_amount;
        
        $result = array(
            'usd_total' => $usd_total,
            'ves_total' => $ves_total,
            'rate' => $rate,
            'igtf_amount' => $igtf_amount,
            'total_with_igtf' => $total_with_igtf
        );
        
        // Guardar en caché
        $this->cache[$cache_key] = $result;
        
        // Guardar en caché persistente
        set_transient($cache_key, $result, $this->cache_duration);
        
        return $result;
    }
    
    /**
     * Calcular precio de producto con conversión
     */
    public function calculate_product_price_with_conversion($product_id, $quantity = 1) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return array(
                'usd_price' => 0,
                'ves_price' => 0,
                'rate' => 0,
                'total_usd' => 0,
                'total_ves' => 0
            );
        }
        
        // Generar clave de caché
        $cache_key = 'product_price_' . md5($product_id . '_' . $quantity);
        
        // Verificar caché
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        // Calcular precios
        $usd_price = $product->get_price();
        $rate = $this->get_bcv_rate();
        $ves_price = $this->calculate_ves_price($usd_price, $rate);
        
        $total_usd = $usd_price * $quantity;
        $total_ves = $ves_price * $quantity;
        
        $result = array(
            'usd_price' => $usd_price,
            'ves_price' => $ves_price,
            'rate' => $rate,
            'total_usd' => $total_usd,
            'total_ves' => $total_ves
        );
        
        // Guardar en caché
        $this->cache[$cache_key] = $result;
        
        // Guardar en caché persistente
        set_transient($cache_key, $result, $this->cache_duration);
        
        return $result;
    }
    
    /**
     * Formatear precio en VES
     */
    public function format_ves_price($ves_price) {
        if (!$ves_price || $ves_price <= 0) {
            return 'Bs. 0,00';
        }
        
        $formatted = number_format($ves_price, 2, ',', '.');
        return 'Bs. ' . $formatted;
    }
    
    /**
     * Formatear precio en USD
     */
    public function format_usd_price($usd_price) {
        if (!$usd_price || $usd_price <= 0) {
            return '$0.00';
        }
        
        return '$' . number_format($usd_price, 2, '.', ',');
    }
    
    /**
     * Obtener tasa BCV con caché
     */
    private function get_bcv_rate() {
        $cache_key = 'bcv_rate';
        
        // Verificar caché
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        // Obtener tasa del BCV
        $rate = 0;
        if (class_exists('WVP_BCV_Integrator')) {
            $rate = WVP_BCV_Integrator::get_rate();
        }
        
        // Guardar en caché
        $this->cache[$cache_key] = $rate;
        
        // Guardar en caché persistente
        set_transient($cache_key, $rate, $this->cache_duration);
        
        return $rate;
    }
    
    /**
     * Limpiar caché
     */
    public function clear_cache() {
        $this->cache = array();
        
        // Limpiar caché persistente
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%'");
    }
    
    /**
     * Limpiar caché de producto
     */
    public function clear_product_cache($product_id) {
        $cache_key = 'product_price_' . md5($product_id);
        unset($this->cache[$cache_key]);
        delete_transient($cache_key);
    }
    
    /**
     * Limpiar caché del carrito
     */
    public function clear_cart_cache() {
        $cache_key = 'cart_total_';
        foreach ($this->cache as $key => $value) {
            if (strpos($key, $cache_key) === 0) {
                unset($this->cache[$key]);
                delete_transient($key);
            }
        }
    }
    
    /**
     * Limpiar caché expirado
     */
    public function cleanup_expired_cache() {
        global $wpdb;
        
        // Limpiar transientes expirados
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%' AND option_value < " . time());
        
        // Obtener los nombres de transientes que tienen timeout válido
        $valid_transients = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%'");
        
        // Convertir a formato de transiente normal
        $valid_transient_names = array();
        foreach ($valid_transients as $timeout_name) {
            $transient_name = str_replace('_transient_timeout_', '_transient_', $timeout_name);
            $valid_transient_names[] = $transient_name;
        }
        
        // Si hay transientes válidos, excluirlos de la eliminación
        if (!empty($valid_transient_names)) {
            $placeholders = implode(',', array_fill(0, count($valid_transient_names), '%s'));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%' AND option_name NOT IN ($placeholders)",
                $valid_transient_names
            ));
        } else {
            // Si no hay transientes válidos, eliminar todos los transientes wvp
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%'");
        }
    }
    
    /**
     * Obtener estadísticas de caché
     */
    public function get_cache_stats() {
        global $wpdb;
        
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%'");
        $timeout_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%'");
        
        return array(
            'memory_cache' => count($this->cache),
            'persistent_cache' => $transient_count,
            'timeouts' => $timeout_count,
            'cache_duration' => $this->cache_duration
        );
    }
    
    /**
     * Optimizar cálculos en lote
     */
    public function batch_calculate_prices($products) {
        $results = array();
        
        foreach ($products as $product_id) {
            $results[$product_id] = $this->calculate_product_price_with_conversion($product_id);
        }
        
        return $results;
    }
    
    /**
     * Precalcular precios para productos populares
     */
    public function precalculate_popular_products($limit = 10) {
        // Obtener productos más vendidos
        $popular_products = wc_get_products(array(
            'limit' => $limit,
            'orderby' => 'popularity',
            'order' => 'DESC',
            'status' => 'publish'
        ));
        
        $results = array();
        foreach ($popular_products as $product) {
            $results[$product->get_id()] = $this->calculate_product_price_with_conversion($product->get_id());
        }
        
        return $results;
    }
}
