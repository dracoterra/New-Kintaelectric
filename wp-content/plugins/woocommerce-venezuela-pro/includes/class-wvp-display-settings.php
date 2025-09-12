<?php
/**
 * Configuraciones de visualización - WooCommerce Venezuela Pro
 * Maneja las configuraciones de dónde y cómo se muestran los elementos
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Display_Settings {
    
    /**
     * Configuraciones por defecto
     */
    private static $default_settings = array(
        'currency_conversion' => array(
            'single_product' => true,
            'shop_loop' => true,
            'cart' => true,
            'checkout' => true,
            'widget' => false, // Deshabilitado por defecto en widgets
            'footer' => false  // Deshabilitado por defecto en footer
        ),
        'bcv_rate' => array(
            'single_product' => false,
            'shop_loop' => false,
            'cart' => false,
            'checkout' => false,
            'widget' => true,  // Habilitado por defecto en widgets
            'footer' => false
        ),
        'currency_switcher' => array(
            'single_product' => true,
            'shop_loop' => true,
            'cart' => true,
            'checkout' => true,
            'widget' => false, // Deshabilitado por defecto en widgets
            'footer' => false  // Deshabilitado por defecto en footer
        ),
        'switcher_scope' => array(
            'single_product' => 'local',
            'shop_loop' => 'local',
            'cart' => 'local',
            'checkout' => 'local',
            'widget' => 'global',
            'footer' => 'global'
        )
    );
    
    /**
     * Obtener configuraciones actuales
     */
    public static function get_settings() {
        $settings = get_option('wvp_display_settings', self::$default_settings);
        return wp_parse_args($settings, self::$default_settings);
    }
    
    /**
     * Obtener las configuraciones por defecto
     */
    public static function get_default_settings() {
        return self::$default_settings;
    }
    
    /**
     * Verificar si se debe mostrar conversión de monedas
     */
    public static function should_show_conversion($context) {
        $settings = self::get_settings();
        return isset($settings['currency_conversion'][$context]) ? $settings['currency_conversion'][$context] : false;
    }
    
    /**
     * Verificar si se debe mostrar tasa BCV
     */
    public static function should_show_bcv_rate($context) {
        $settings = self::get_settings();
        return isset($settings['bcv_rate'][$context]) ? $settings['bcv_rate'][$context] : false;
    }
    
    /**
     * Verificar si se debe mostrar selector de moneda
     */
    public static function should_show_switcher($context) {
        $settings = self::get_settings();
        return isset($settings['currency_switcher'][$context]) ? $settings['currency_switcher'][$context] : false;
    }
    
    /**
     * Obtener alcance del selector para un contexto
     */
    public static function get_switcher_scope($context) {
        $settings = self::get_settings();
        return isset($settings['switcher_scope'][$context]) ? $settings['switcher_scope'][$context] : 'local';
    }
    
    /**
     * Obtener contexto actual
     */
    public static function get_current_context() {
        // Detectar si estamos en el footer
        if (doing_action('wp_footer') || did_action('wp_footer')) {
            return 'footer';
        }
        
        // Detectar contexto de WooCommerce PRIMERO
        if (is_product()) {
            return 'single_product';
        } elseif (is_shop() || is_product_category() || is_product_tag()) {
            return 'shop_loop';
        } elseif (is_cart()) {
            return 'cart';
        } elseif (is_checkout()) {
            return 'checkout';
        }
        
        // Detectar si estamos en un widget SOLO si no es una página de WooCommerce
        if (did_action('dynamic_sidebar') || is_active_widget(false, false, 'woocommerce_widget_cart') || is_active_widget(false, false, 'woocommerce_product_categories') || is_active_widget(false, false, 'woocommerce_products')) {
            return 'widget';
        }
        
        $context = 'default';
        
        // Aplicar filtro para permitir modificación del contexto
        return apply_filters('wvp_current_context', $context);
    }
    
    /**
     * Aplicar filtros de WordPress
     */
    public static function init_filters() {
        add_filter('wvp_show_currency_conversion', array(__CLASS__, 'should_show_conversion'), 10, 1);
        add_filter('wvp_show_bcv_rate', array(__CLASS__, 'should_show_bcv_rate'), 10, 1);
        add_filter('wvp_show_currency_switcher', array(__CLASS__, 'should_show_switcher'), 10, 1);
    }
}

// Inicializar filtros
WVP_Display_Settings::init_filters();
?>
