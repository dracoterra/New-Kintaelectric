<?php
/**
 * Clase para detectar contexto de visualización de manera robusta
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WVP_Context_Detector {
    
    private static $instance = null;
    private $current_context = null;
    private $context_stack = array();
    
    /**
     * Singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para detectar contexto en diferentes momentos
        add_action('wp', array($this, 'detect_context_on_wp'), 10);
        add_action('woocommerce_before_shop_loop', array($this, 'set_shop_context'), 5);
        add_action('woocommerce_after_shop_loop', array($this, 'clear_shop_context'), 5);
        add_action('woocommerce_single_product_summary', array($this, 'set_single_product_context'), 5);
        add_action('woocommerce_after_single_product_summary', array($this, 'clear_single_product_context'), 5);
        add_action('dynamic_sidebar_before', array($this, 'set_widget_context'), 5);
        add_action('dynamic_sidebar_after', array($this, 'clear_widget_context'), 5);
        add_action('wp_footer', array($this, 'set_footer_context'), 5);
    }
    
    /**
     * Detectar contexto en wp action
     */
    public function detect_context_on_wp() {
        $this->current_context = $this->determine_context();
    }
    
    /**
     * Establecer contexto de shop
     */
    public function set_shop_context() {
        $this->push_context('shop_loop');
    }
    
    /**
     * Limpiar contexto de shop
     */
    public function clear_shop_context() {
        $this->pop_context();
    }
    
    /**
     * Establecer contexto de producto individual
     */
    public function set_single_product_context() {
        $this->push_context('single_product');
    }
    
    /**
     * Limpiar contexto de producto individual
     */
    public function clear_single_product_context() {
        $this->pop_context();
    }
    
    /**
     * Establecer contexto de widget
     */
    public function set_widget_context() {
        $this->push_context('widget');
    }
    
    /**
     * Limpiar contexto de widget
     */
    public function clear_widget_context() {
        $this->pop_context();
    }
    
    /**
     * Establecer contexto de footer
     */
    public function set_footer_context() {
        $this->push_context('footer');
    }
    
    /**
     * Agregar contexto a la pila
     */
    private function push_context($context) {
        array_push($this->context_stack, $context);
        $this->current_context = $context;
    }
    
    /**
     * Remover contexto de la pila
     */
    private function pop_context() {
        if (!empty($this->context_stack)) {
            array_pop($this->context_stack);
            $this->current_context = !empty($this->context_stack) ? end($this->context_stack) : $this->determine_context();
        }
    }
    
    /**
     * Determinar contexto actual
     */
    private function determine_context() {
        // Detectar si estamos en el footer
        if (doing_action('wp_footer') || did_action('wp_footer')) {
            return 'footer';
        }
        
        // Detectar contexto de WooCommerce
        if (is_product()) {
            return 'single_product';
        } elseif (is_shop() || is_product_category() || is_product_tag()) {
            return 'shop_loop';
        } elseif (is_cart()) {
            return 'cart';
        } elseif (is_checkout()) {
            return 'checkout';
        }
        
        // Detectar si estamos en un widget
        if (did_action('dynamic_sidebar')) {
            return 'widget';
        }
        
        return 'default';
    }
    
    /**
     * Obtener contexto actual
     */
    public function get_current_context() {
        if ($this->current_context === null) {
            $this->current_context = $this->determine_context();
        }
        
        return apply_filters('wvp_current_context', $this->current_context);
    }
    
    /**
     * Verificar si estamos en un contexto específico
     */
    public function is_context($context) {
        return $this->get_current_context() === $context;
    }
    
    /**
     * Verificar si debemos mostrar elementos en el contexto actual
     */
    public function should_show_in_context($element_type) {
        $context = $this->get_current_context();
        
        // Configuraciones por defecto
        $default_settings = array(
            'currency_conversion' => array(
                'single_product' => true,
                'shop_loop' => true,
                'cart' => true,
                'checkout' => true,
                'widget' => false,
                'footer' => false
            ),
            'bcv_rate' => array(
                'single_product' => false,
                'shop_loop' => false,
                'cart' => false,
                'checkout' => false,
                'widget' => false,
                'footer' => false
            ),
            'currency_switcher' => array(
                'single_product' => true,
                'shop_loop' => true,
                'cart' => true,
                'checkout' => true,
                'widget' => false,
                'footer' => false
            )
        );
        
        // Obtener configuraciones guardadas
        $saved_settings = get_option('wvp_display_settings', $default_settings);
        
        if (isset($saved_settings[$element_type][$context])) {
            return (bool) $saved_settings[$element_type][$context];
        }
        
        return false;
    }
}
