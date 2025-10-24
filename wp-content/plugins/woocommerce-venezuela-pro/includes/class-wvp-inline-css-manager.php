<?php
/**
 * Sistema de CSS Inline de Alta Prioridad - WooCommerce Venezuela Pro
 * Aplica CSS directamente en el HTML para máxima especificidad
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Inline_CSS_Manager {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * CSS inline generado
     */
    private $inline_css = null;
    
    /**
     * Constructor privado
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Obtener instancia única
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar
     */
    public function init() {
        // Aplicar CSS inline en wp_head con máxima prioridad
        add_action('wp_head', array($this, 'apply_inline_css'), 1);
        
        // También aplicar en wp_footer como respaldo
        add_action('wp_footer', array($this, 'apply_inline_css_fallback'), 1);
        
        // Hook para regenerar CSS cuando cambien las configuraciones
        add_action('update_option_wvp_display_style', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_primary_color', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_secondary_color', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_success_color', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_warning_color', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_font_family', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_font_size', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_font_weight', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_text_transform', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_padding', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_margin', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_border_radius', array($this, 'regenerate_inline_css'));
        add_action('update_option_wvp_shadow', array($this, 'regenerate_inline_css'));
    }
    
    /**
     * Generar CSS inline
     */
    private function generate_inline_css() {
        // Verificar si ya está en caché
        $cache_key = 'wvp_inline_css_' . md5(serialize($this->get_appearance_settings()));
        $cached_css = get_transient($cache_key);
        
        if ($cached_css !== false) {
            $this->inline_css = $cached_css;
            return;
        }
        
        // Generar CSS inline
        $css = $this->build_inline_css();
        
        // Guardar en caché por 1 hora
        set_transient($cache_key, $css, HOUR_IN_SECONDS);
        
        $this->inline_css = $css;
    }
    
    /**
     * Construir CSS inline
     */
    private function build_inline_css() {
        $settings = $this->get_appearance_settings();
        $css = '';
        
        // Variables CSS con máxima especificidad
        $css .= $this->generate_css_variables_inline();
        
        // Estilos base con máxima especificidad
        $css .= $this->generate_base_styles_inline($settings);
        
        // Estilos específicos por estilo
        $css .= $this->generate_style_specific_inline($settings);
        
        return $css;
    }
    
    /**
     * Generar variables CSS inline
     */
    private function generate_css_variables_inline() {
        $settings = $this->get_appearance_settings();
        $primary_rgb = $this->hex_to_rgb($settings['primary_color']);
        
        $css = 'html, body {';
        $css .= '--wvp-primary-color: ' . $settings['primary_color'] . ' !important;';
        $css .= '--wvp-secondary-color: ' . $settings['secondary_color'] . ' !important;';
        $css .= '--wvp-success-color: ' . $settings['success_color'] . ' !important;';
        $css .= '--wvp-warning-color: ' . $settings['warning_color'] . ' !important;';
        
        if ($primary_rgb) {
            $css .= '--wvp-primary-color-rgb: ' . $primary_rgb['r'] . ', ' . $primary_rgb['g'] . ', ' . $primary_rgb['b'] . ' !important;';
        }
        
        // Variables de fuente
        $font_families = $this->get_font_families();
        $font_sizes = $this->get_font_sizes();
        
        $css .= '--wvp-font-family: ' . $font_families[$settings['font_family']] . ' !important;';
        $css .= '--wvp-font-size: ' . $font_sizes[$settings['font_size']] . ' !important;';
        $css .= '--wvp-font-weight: ' . $settings['font_weight'] . ' !important;';
        $css .= '--wvp-text-transform: ' . $settings['text_transform'] . ' !important;';
        
        // Variables de espaciado
        $spacings = $this->get_spacings();
        $css .= '--wvp-padding: ' . $spacings[$settings['padding']] . ' !important;';
        $css .= '--wvp-margin: ' . $spacings[$settings['margin']] . ' !important;';
        $css .= '--wvp-border-radius: ' . $spacings[$settings['border_radius']] . ' !important;';
        
        // Variables de sombra
        $shadows = $this->get_shadows();
        $css .= '--wvp-shadow: ' . $shadows[$settings['shadow']] . ' !important;';
        
        $css .= '}';
        
        return $css;
    }
    
    /**
     * Generar estilos base inline
     */
    private function generate_base_styles_inline($settings) {
        $css = '
        /* Estilos base con máxima especificidad */
        html body .wvp-product-price-container,
        html .woocommerce .wvp-product-price-container,
        html .woocommerce-page .wvp-product-price-container,
        html .single-product .wvp-product-price-container,
        html .shop .wvp-product-price-container,
        html .cart .wvp-product-price-container,
        html .checkout .wvp-product-price-container {
            font-family: var(--wvp-font-family) !important;
            margin: var(--wvp-margin) !important;
        }
        
        html body .wvp-price-usd,
        html body .wvp-price-ves,
        html .woocommerce .wvp-price-usd,
        html .woocommerce .wvp-price-ves,
        html .woocommerce-page .wvp-price-usd,
        html .woocommerce-page .wvp-price-ves,
        html .single-product .wvp-price-usd,
        html .single-product .wvp-price-ves,
        html .shop .wvp-price-usd,
        html .shop .wvp-price-ves,
        html .cart .wvp-price-usd,
        html .cart .wvp-price-ves,
        html .checkout .wvp-price-usd,
        html .checkout .wvp-price-ves {
            font-family: var(--wvp-font-family) !important;
            font-size: var(--wvp-font-size) !important;
            font-weight: var(--wvp-font-weight) !important;
            text-transform: var(--wvp-text-transform) !important;
            color: #333333 !important;
            transition: all 0.3s ease !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
        }
        
        html body .wvp-price-ves,
        html .woocommerce .wvp-price-ves,
        html .woocommerce-page .wvp-price-ves,
        html .single-product .wvp-price-ves,
        html .shop .wvp-price-ves,
        html .cart .wvp-price-ves,
        html .checkout .wvp-price-ves {
            color: var(--wvp-secondary-color) !important;
        }
        
        html body .wvp-currency-switcher button,
        html body .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-currency-switcher button,
        html .woocommerce .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-currency-switcher button,
        html .woocommerce-page .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-currency-switcher button,
        html .single-product .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-currency-switcher button,
        html .shop .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-currency-switcher button,
        html .cart .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-currency-switcher button,
        html .checkout .wvp-currency-switcher .wvp-currency-option {
            font-family: var(--wvp-font-family) !important;
            font-weight: var(--wvp-font-weight) !important;
            text-transform: var(--wvp-text-transform) !important;
            transition: all 0.3s ease !important;
        }
        
        html body .wvp-currency-switcher button.active,
        html body .wvp-currency-switcher .wvp-currency-option.active,
        html .woocommerce .wvp-currency-switcher button.active,
        html .woocommerce .wvp-currency-switcher .wvp-currency-option.active,
        html .woocommerce-page .wvp-currency-switcher button.active,
        html .woocommerce-page .wvp-currency-switcher .wvp-currency-option.active,
        html .single-product .wvp-currency-switcher button.active,
        html .single-product .wvp-currency-switcher .wvp-currency-option.active,
        html .shop .wvp-currency-switcher button.active,
        html .shop .wvp-currency-switcher .wvp-currency-option.active,
        html .cart .wvp-currency-switcher button.active,
        html .cart .wvp-currency-switcher .wvp-currency-option.active,
        html .checkout .wvp-currency-switcher button.active,
        html .checkout .wvp-currency-switcher .wvp-currency-option.active {
            background-color: var(--wvp-primary-color) !important;
            border-color: var(--wvp-primary-color) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        html body .wvp-currency-switcher button:not(.active),
        html body .wvp-currency-switcher .wvp-currency-option:not(.active),
        html .woocommerce .wvp-currency-switcher button:not(.active),
        html .woocommerce .wvp-currency-switcher .wvp-currency-option:not(.active),
        html .woocommerce-page .wvp-currency-switcher button:not(.active),
        html .woocommerce-page .wvp-currency-switcher .wvp-currency-option:not(.active),
        html .single-product .wvp-currency-switcher button:not(.active),
        html .single-product .wvp-currency-switcher .wvp-currency-option:not(.active),
        html .shop .wvp-currency-switcher button:not(.active),
        html .shop .wvp-currency-switcher .wvp-currency-option:not(.active),
        html .cart .wvp-currency-switcher button:not(.active),
        html .cart .wvp-currency-switcher .wvp-currency-option:not(.active),
        html .checkout .wvp-currency-switcher button:not(.active),
        html .checkout .wvp-currency-switcher .wvp-currency-option:not(.active) {
            background-color: #ffffff !important;
            border: 1px solid #ddd !important;
            color: #666 !important;
            font-weight: 400 !important;
        }
        
        html body .wvp-currency-switcher button:hover,
        html body .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce .wvp-currency-switcher button:hover,
        html .woocommerce .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce-page .wvp-currency-switcher button:hover,
        html .woocommerce-page .wvp-currency-switcher .wvp-currency-option:hover,
        html .single-product .wvp-currency-switcher button:hover,
        html .single-product .wvp-currency-switcher .wvp-currency-option:hover,
        html .shop .wvp-currency-switcher button:hover,
        html .shop .wvp-currency-switcher .wvp-currency-option:hover,
        html .cart .wvp-currency-switcher button:hover,
        html .cart .wvp-currency-switcher .wvp-currency-option:hover,
        html .checkout .wvp-currency-switcher button:hover,
        html .checkout .wvp-currency-switcher .wvp-currency-option:hover {
            border-color: var(--wvp-primary-color) !important;
            color: var(--wvp-primary-color) !important;
        }
        
        html body .wvp-price-conversion,
        html .woocommerce .wvp-price-conversion,
        html .woocommerce-page .wvp-price-conversion,
        html .single-product .wvp-price-conversion,
        html .shop .wvp-price-conversion,
        html .cart .wvp-price-conversion,
        html .checkout .wvp-price-conversion {
            background-color: rgba(var(--wvp-primary-color-rgb, 0, 124, 186), 0.1) !important;
            border: 1px solid var(--wvp-primary-color) !important;
            border-radius: var(--wvp-border-radius) !important;
            padding: var(--wvp-padding) !important;
            margin: var(--wvp-margin) !important;
            box-shadow: var(--wvp-shadow) !important;
            color: var(--wvp-secondary-color) !important;
            font-family: var(--wvp-font-family) !important;
            transition: all 0.3s ease !important;
        }
        
        html body .wvp-rate-info,
        html .woocommerce .wvp-rate-info,
        html .woocommerce-page .wvp-rate-info,
        html .single-product .wvp-rate-info,
        html .shop .wvp-rate-info,
        html .cart .wvp-rate-info,
        html .checkout .wvp-rate-info {
            color: var(--wvp-secondary-color) !important;
            font-family: var(--wvp-font-family) !important;
            font-size: calc(var(--wvp-font-size) * 0.8) !important;
            transition: all 0.3s ease !important;
        }
        
        /* Estilos para botones de carrito y elementos de producto */
        html body .wvp-add-to-cart,
        html .woocommerce .wvp-add-to-cart,
        html .woocommerce-page .wvp-add-to-cart,
        html .single-product .wvp-add-to-cart,
        html .shop .wvp-add-to-cart,
        html .cart .wvp-add-to-cart,
        html .checkout .wvp-add-to-cart {
            background-color: var(--wvp-primary-color) !important;
            border: none !important;
            border-radius: 50% !important;
            width: 40px !important;
            height: 40px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #ffffff !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        html body .wvp-add-to-cart:hover,
        html .woocommerce .wvp-add-to-cart:hover,
        html .woocommerce-page .wvp-add-to-cart:hover,
        html .single-product .wvp-add-to-cart:hover,
        html .shop .wvp-add-to-cart:hover,
        html .cart .wvp-add-to-cart:hover,
        html .checkout .wvp-add-to-cart:hover {
            background-color: var(--wvp-secondary-color) !important;
            transform: scale(1.05) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        }
        
        /* Estilos para contenedores de producto */
        html body .wvp-product-container,
        html .woocommerce .wvp-product-container,
        html .woocommerce-page .wvp-product-container,
        html .single-product .wvp-product-container,
        html .shop .wvp-product-container,
        html .cart .wvp-product-container,
        html .checkout .wvp-product-container {
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            padding: 15px !important;
            margin: 10px !important;
            background: #ffffff !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
        }
        
        /* Estilos para texto de producto */
        html body .wvp-product-title,
        html .woocommerce .wvp-product-title,
        html .woocommerce-page .wvp-product-title,
        html .single-product .wvp-product-title,
        html .shop .wvp-product-title,
        html .cart .wvp-product-title,
        html .checkout .wvp-product-title {
            color: #333333 !important;
            font-family: var(--wvp-font-family) !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            margin: 8px 0 !important;
            padding: 0 !important;
            line-height: 1.3 !important;
            overflow: visible !important;
            white-space: normal !important;
        }
        
        /* Estilos para categorías de producto */
        html body .wvp-product-category,
        html .woocommerce .wvp-product-category,
        html .woocommerce-page .wvp-product-category,
        html .single-product .wvp-product-category,
        html .shop .wvp-product-category,
        html .cart .wvp-product-category,
        html .checkout .wvp-product-category {
            color: #999999 !important;
            font-family: var(--wvp-font-family) !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            margin: 5px 0 0 0 !important;
            padding: 0 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }
        
        /* Estilos para elementos de wishlist y compare */
        html body .wvp-product-actions,
        html .woocommerce .wvp-product-actions,
        html .woocommerce-page .wvp-product-actions,
        html .single-product .wvp-product-actions,
        html .shop .wvp-product-actions,
        html .cart .wvp-product-actions,
        html .checkout .wvp-product-actions {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin: 10px 0 !important;
            color: #999999 !important;
            font-size: 12px !important;
        }
        
        html body .wvp-product-actions a,
        html .woocommerce .wvp-product-actions a,
        html .woocommerce-page .wvp-product-actions a,
        html .single-product .wvp-product-actions a,
        html .shop .wvp-product-actions a,
        html .cart .wvp-product-actions a,
        html .checkout .wvp-product-actions a {
            color: #999999 !important;
            text-decoration: none !important;
            transition: color 0.3s ease !important;
        }
        
        html body .wvp-product-actions a:hover,
        html .woocommerce .wvp-product-actions a:hover,
        html .woocommerce-page .wvp-product-actions a:hover,
        html .single-product .wvp-product-actions a:hover,
        html .shop .wvp-product-actions a:hover,
        html .cart .wvp-product-actions a:hover,
        html .checkout .wvp-product-actions a:hover {
            color: var(--wvp-primary-color) !important;
        }
        ';
        
        return $css;
    }
    
    /**
     * Generar estilos específicos inline
     */
    private function generate_style_specific_inline($settings) {
        $style = $settings['display_style'];
        $css = '';
        
        switch ($style) {
            case 'minimal':
                $css .= $this->get_minimal_styles_inline();
                break;
            case 'modern':
                $css .= $this->get_modern_styles_inline();
                break;
            case 'elegant':
                $css .= $this->get_elegant_styles_inline();
                break;
            case 'compact':
                $css .= $this->get_compact_styles_inline();
                break;
            case 'vintage':
                $css .= $this->get_vintage_styles_inline();
                break;
            case 'futuristic':
                $css .= $this->get_futuristic_styles_inline();
                break;
            case 'advanced-minimal':
                $css .= $this->get_advanced_minimal_styles_inline();
                break;
            default:
                $css .= $this->get_minimal_styles_inline();
        }
        
        return $css;
    }
    
    /**
     * Estilos minimalistas inline con correcciones
     */
    private function get_minimal_styles_inline() {
        return '
        html body .wvp-minimal .wvp-product-price-container,
        html .woocommerce .wvp-minimal .wvp-product-price-container,
        html .woocommerce-page .wvp-minimal .wvp-product-price-container,
        html .single-product .wvp-minimal .wvp-product-price-container,
        html .shop .wvp-minimal .wvp-product-price-container,
        html .cart .wvp-minimal .wvp-product-price-container,
        html .checkout .wvp-minimal .wvp-product-price-container {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
            overflow: visible !important;
            height: auto !important;
        }
        
        html body .wvp-minimal .wvp-currency-switcher,
        html .woocommerce .wvp-minimal .wvp-currency-switcher,
        html .woocommerce-page .wvp-minimal .wvp-currency-switcher,
        html .single-product .wvp-minimal .wvp-currency-switcher,
        html .shop .wvp-minimal .wvp-currency-switcher,
        html .cart .wvp-minimal .wvp-currency-switcher,
        html .checkout .wvp-minimal .wvp-currency-switcher {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin: 8px 0 !important;
        }
        
        html body .wvp-minimal .wvp-currency-switcher button,
        html body .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-minimal .wvp-currency-switcher button,
        html .woocommerce .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-minimal .wvp-currency-switcher button,
        html .woocommerce-page .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-minimal .wvp-currency-switcher button,
        html .single-product .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-minimal .wvp-currency-switcher button,
        html .shop .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-minimal .wvp-currency-switcher button,
        html .cart .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-minimal .wvp-currency-switcher button,
        html .checkout .wvp-minimal .wvp-currency-switcher .wvp-currency-option {
            padding: 6px 12px !important;
            border: 1px solid #ddd !important;
            background: #ffffff !important;
            color: #666 !important;
            border-radius: 4px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            min-width: 50px !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
        }
        
        html body .wvp-minimal .wvp-currency-switcher button.active,
        html body .wvp-minimal .wvp-currency-switcher .wvp-currency-option.active,
        html .woocommerce .wvp-minimal .wvp-currency-switcher button.active,
        html .woocommerce .wvp-minimal .wvp-currency-switcher .wvp-currency-option.active,
        html .woocommerce-page .wvp-minimal .wvp-currency-switcher button.active,
        html .woocommerce-page .wvp-minimal .wvp-currency-switcher .wvp-currency-option.active,
        html .single-product .wvp-minimal .wvp-currency-switcher button.active,
        html .single-product .wvp-minimal .wvp-currency-switcher .wvp-currency-option.active,
        html .shop .wvp-minimal .wvp-currency-switcher button.active,
        html .shop .wvp-minimal .wvp-currency-switcher .wvp-currency-option.active,
        html .cart .wvp-minimal .wvp-currency-switcher button.active,
        html .cart .wvp-minimal .wvp-currency-switcher .wvp-currency-option.active,
        html .checkout .wvp-minimal .wvp-currency-switcher button.active,
        html .checkout .wvp-minimal .wvp-currency-switcher .wvp-currency-option.active {
            background-color: var(--wvp-primary-color) !important;
            border-color: var(--wvp-primary-color) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }
        
        html body .wvp-minimal .wvp-price-conversion,
        html .woocommerce .wvp-minimal .wvp-price-conversion,
        html .woocommerce-page .wvp-minimal .wvp-price-conversion,
        html .single-product .wvp-minimal .wvp-price-conversion,
        html .shop .wvp-minimal .wvp-price-conversion,
        html .cart .wvp-minimal .wvp-price-conversion,
        html .checkout .wvp-minimal .wvp-price-conversion {
            background: #f8f9fa !important;
            border: 1px solid #e9ecef !important;
            font-size: 12px !important;
            font-style: italic !important;
            padding: 6px 10px !important;
            border-radius: 4px !important;
            margin: 8px 0 !important;
        }
        
        html body .wvp-minimal .wvp-product-container,
        html .woocommerce .wvp-minimal .wvp-product-container,
        html .woocommerce-page .wvp-minimal .wvp-product-container,
        html .single-product .wvp-minimal .wvp-product-container,
        html .shop .wvp-minimal .wvp-product-container,
        html .cart .wvp-minimal .wvp-product-container,
        html .checkout .wvp-minimal .wvp-product-container {
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            padding: 15px !important;
            margin: 10px !important;
            background: #ffffff !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
        }
        
        html body .wvp-minimal .wvp-product-title,
        html .woocommerce .wvp-minimal .wvp-product-title,
        html .woocommerce-page .wvp-minimal .wvp-product-title,
        html .single-product .wvp-minimal .wvp-product-title,
        html .shop .wvp-minimal .wvp-product-title,
        html .cart .wvp-minimal .wvp-product-title,
        html .checkout .wvp-minimal .wvp-product-title {
            color: #333333 !important;
            font-family: var(--wvp-font-family) !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            margin: 8px 0 !important;
            padding: 0 !important;
            line-height: 1.3 !important;
            overflow: visible !important;
            white-space: normal !important;
            height: auto !important;
        }
        ';
    }
    
    /**
     * Estilos modernos inline
     */
    private function get_modern_styles_inline() {
        return '
        html body .wvp-modern .wvp-product-price-container,
        html .woocommerce .wvp-modern .wvp-product-price-container,
        html .woocommerce-page .wvp-modern .wvp-product-price-container,
        html .single-product .wvp-modern .wvp-product-price-container,
        html .shop .wvp-modern .wvp-product-price-container,
        html .cart .wvp-modern .wvp-product-price-container,
        html .checkout .wvp-modern .wvp-product-price-container {
            background: linear-gradient(135deg, #f8f9fa, #ffffff) !important;
            border: 1px solid #e9ecef !important;
            border-radius: 12px !important;
            padding: 20px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        
        html body .wvp-modern .wvp-currency-switcher,
        html .woocommerce .wvp-modern .wvp-currency-switcher,
        html .woocommerce-page .wvp-modern .wvp-currency-switcher,
        html .single-product .wvp-modern .wvp-currency-switcher,
        html .shop .wvp-modern .wvp-currency-switcher,
        html .cart .wvp-modern .wvp-currency-switcher,
        html .checkout .wvp-modern .wvp-currency-switcher {
            display: flex !important;
            gap: 8px !important;
            margin: 15px 0 !important;
        }
        
        html body .wvp-modern .wvp-currency-switcher button,
        html body .wvp-modern .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-modern .wvp-currency-switcher button,
        html .woocommerce .wvp-modern .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-modern .wvp-currency-switcher button,
        html .woocommerce-page .wvp-modern .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-modern .wvp-currency-switcher button,
        html .single-product .wvp-modern .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-modern .wvp-currency-switcher button,
        html .shop .wvp-modern .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-modern .wvp-currency-switcher button,
        html .cart .wvp-modern .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-modern .wvp-currency-switcher button,
        html .checkout .wvp-modern .wvp-currency-switcher .wvp-currency-option {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 10px 16px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }
        
        html body .wvp-modern .wvp-currency-switcher button:hover,
        html body .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce .wvp-modern .wvp-currency-switcher button:hover,
        html .woocommerce .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce-page .wvp-modern .wvp-currency-switcher button:hover,
        html .woocommerce-page .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover,
        html .single-product .wvp-modern .wvp-currency-switcher button:hover,
        html .single-product .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover,
        html .shop .wvp-modern .wvp-currency-switcher button:hover,
        html .shop .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover,
        html .cart .wvp-modern .wvp-currency-switcher button:hover,
        html .cart .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover,
        html .checkout .wvp-modern .wvp-currency-switcher button:hover,
        html .checkout .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
        }
        
        html body .wvp-modern .wvp-price-conversion,
        html .woocommerce .wvp-modern .wvp-price-conversion,
        html .woocommerce-page .wvp-modern .wvp-price-conversion,
        html .single-product .wvp-modern .wvp-price-conversion,
        html .shop .wvp-modern .wvp-price-conversion,
        html .cart .wvp-modern .wvp-price-conversion,
        html .checkout .wvp-modern .wvp-price-conversion {
            background: linear-gradient(135deg, rgba(var(--wvp-primary-color-rgb, 0, 124, 186), 0.1), rgba(var(--wvp-primary-color-rgb, 0, 124, 186), 0.05)) !important;
            border: 1px solid var(--wvp-primary-color) !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
        }
        ';
    }
    
    /**
     * Estilos elegantes inline
     */
    private function get_elegant_styles_inline() {
        return '
        html body .wvp-elegant .wvp-product-price-container,
        html .woocommerce .wvp-elegant .wvp-product-price-container,
        html .woocommerce-page .wvp-elegant .wvp-product-price-container,
        html .single-product .wvp-elegant .wvp-product-price-container,
        html .shop .wvp-elegant .wvp-product-price-container,
        html .cart .wvp-elegant .wvp-product-price-container,
        html .checkout .wvp-elegant .wvp-product-price-container {
            background: #ffffff !important;
            border: 2px solid #f0f0f0 !important;
            border-radius: 16px !important;
            padding: 25px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08) !important;
        }
        
        html body .wvp-elegant .wvp-currency-switcher,
        html .woocommerce .wvp-elegant .wvp-currency-switcher,
        html .woocommerce-page .wvp-elegant .wvp-currency-switcher,
        html .single-product .wvp-elegant .wvp-currency-switcher,
        html .shop .wvp-elegant .wvp-currency-switcher,
        html .cart .wvp-elegant .wvp-currency-switcher,
        html .checkout .wvp-elegant .wvp-currency-switcher {
            display: flex !important;
            gap: 12px !important;
            margin: 20px 0 !important;
        }
        
        html body .wvp-elegant .wvp-currency-switcher button,
        html body .wvp-elegant .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-elegant .wvp-currency-switcher button,
        html .woocommerce .wvp-elegant .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-elegant .wvp-currency-switcher button,
        html .woocommerce-page .wvp-elegant .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-elegant .wvp-currency-switcher button,
        html .single-product .wvp-elegant .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-elegant .wvp-currency-switcher button,
        html .shop .wvp-elegant .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-elegant .wvp-currency-switcher button,
        html .cart .wvp-elegant .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-elegant .wvp-currency-switcher button,
        html .checkout .wvp-elegant .wvp-currency-switcher .wvp-currency-option {
            background: #ffffff !important;
            border: 2px solid #e0e0e0 !important;
            border-radius: 12px !important;
            padding: 12px 20px !important;
            font-weight: 500 !important;
            letter-spacing: 0.5px !important;
            transition: all 0.4s ease !important;
        }
        
        html body .wvp-elegant .wvp-price-conversion,
        html .woocommerce .wvp-elegant .wvp-price-conversion,
        html .woocommerce-page .wvp-elegant .wvp-price-conversion,
        html .single-product .wvp-elegant .wvp-price-conversion,
        html .shop .wvp-elegant .wvp-price-conversion,
        html .cart .wvp-elegant .wvp-price-conversion,
        html .checkout .wvp-elegant .wvp-price-conversion {
            background: #fafafa !important;
            border: 1px solid #e8e8e8 !important;
            border-radius: 12px !important;
            font-style: italic !important;
            letter-spacing: 0.3px !important;
        }
        ';
    }
    
    /**
     * Estilos compactos inline
     */
    private function get_compact_styles_inline() {
        return '
        html body .wvp-compact .wvp-product-price-container,
        html .woocommerce .wvp-compact .wvp-product-price-container,
        html .woocommerce-page .wvp-compact .wvp-product-price-container,
        html .single-product .wvp-compact .wvp-product-price-container,
        html .shop .wvp-compact .wvp-product-price-container,
        html .cart .wvp-compact .wvp-product-price-container,
        html .checkout .wvp-compact .wvp-product-price-container {
            background: transparent !important;
            padding: 0 !important;
            margin: 5px 0 !important;
        }
        
        html body .wvp-compact .wvp-currency-switcher,
        html .woocommerce .wvp-compact .wvp-currency-switcher,
        html .woocommerce-page .wvp-compact .wvp-currency-switcher,
        html .single-product .wvp-compact .wvp-currency-switcher,
        html .shop .wvp-compact .wvp-currency-switcher,
        html .cart .wvp-compact .wvp-currency-switcher,
        html .checkout .wvp-compact .wvp-currency-switcher {
            display: flex !important;
            gap: 4px !important;
            margin: 5px 0 !important;
        }
        
        html body .wvp-compact .wvp-currency-switcher button,
        html body .wvp-compact .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-compact .wvp-currency-switcher button,
        html .woocommerce .wvp-compact .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-compact .wvp-currency-switcher button,
        html .woocommerce-page .wvp-compact .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-compact .wvp-currency-switcher button,
        html .single-product .wvp-compact .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-compact .wvp-currency-switcher button,
        html .shop .wvp-compact .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-compact .wvp-currency-switcher button,
        html .cart .wvp-compact .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-compact .wvp-currency-switcher button,
        html .checkout .wvp-compact .wvp-currency-switcher .wvp-currency-option {
            padding: 3px 6px !important;
            border: 1px solid #ccc !important;
            background: #f9f9f9 !important;
            border-radius: 2px !important;
            font-size: 11px !important;
            min-width: 35px !important;
        }
        
        html body .wvp-compact .wvp-price-conversion,
        html .woocommerce .wvp-compact .wvp-price-conversion,
        html .woocommerce-page .wvp-compact .wvp-price-conversion,
        html .single-product .wvp-compact .wvp-price-conversion,
        html .shop .wvp-compact .wvp-price-conversion,
        html .cart .wvp-compact .wvp-price-conversion,
        html .checkout .wvp-compact .wvp-price-conversion {
            background: #f5f5f5 !important;
            border: 1px solid #ddd !important;
            border-radius: 3px !important;
            padding: 4px 8px !important;
            font-size: 11px !important;
            margin: 3px 0 !important;
        }
        ';
    }
    
    /**
     * Estilos vintage inline
     */
    private function get_vintage_styles_inline() {
        return '
        html body .wvp-vintage .wvp-product-price-container,
        html .woocommerce .wvp-vintage .wvp-product-price-container,
        html .woocommerce-page .wvp-vintage .wvp-product-price-container,
        html .single-product .wvp-vintage .wvp-product-price-container,
        html .shop .wvp-vintage .wvp-product-price-container,
        html .cart .wvp-vintage .wvp-product-price-container,
        html .checkout .wvp-vintage .wvp-product-price-container {
            background: #f7f3e9 !important;
            border: 3px solid #8b4513 !important;
            border-radius: 0 !important;
            padding: 20px !important;
            box-shadow: inset 0 0 10px rgba(139, 69, 19, 0.1) !important;
        }
        
        html body .wvp-vintage .wvp-currency-switcher,
        html .woocommerce .wvp-vintage .wvp-currency-switcher,
        html .woocommerce-page .wvp-vintage .wvp-currency-switcher,
        html .single-product .wvp-vintage .wvp-currency-switcher,
        html .shop .wvp-vintage .wvp-currency-switcher,
        html .cart .wvp-vintage .wvp-currency-switcher,
        html .checkout .wvp-vintage .wvp-currency-switcher {
            display: flex !important;
            gap: 10px !important;
            margin: 15px 0 !important;
        }
        
        html body .wvp-vintage .wvp-currency-switcher button,
        html body .wvp-vintage .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-vintage .wvp-currency-switcher button,
        html .woocommerce .wvp-vintage .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-vintage .wvp-currency-switcher button,
        html .woocommerce-page .wvp-vintage .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-vintage .wvp-currency-switcher button,
        html .single-product .wvp-vintage .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-vintage .wvp-currency-switcher button,
        html .shop .wvp-vintage .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-vintage .wvp-currency-switcher button,
        html .cart .wvp-vintage .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-vintage .wvp-currency-switcher button,
        html .checkout .wvp-vintage .wvp-currency-switcher .wvp-currency-option {
            background: #8b4513 !important;
            border: 2px solid #654321 !important;
            color: #f7f3e9 !important;
            border-radius: 0 !important;
            padding: 8px 16px !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
        }
        
        html body .wvp-vintage .wvp-price-conversion,
        html .woocommerce .wvp-vintage .wvp-price-conversion,
        html .woocommerce-page .wvp-vintage .wvp-price-conversion,
        html .single-product .wvp-vintage .wvp-price-conversion,
        html .shop .wvp-vintage .wvp-price-conversion,
        html .cart .wvp-vintage .wvp-price-conversion,
        html .checkout .wvp-vintage .wvp-price-conversion {
            background: #654321 !important;
            color: #f7f3e9 !important;
            border: 2px solid #8b4513 !important;
            border-radius: 0 !important;
            font-weight: bold !important;
        }
        ';
    }
    
    /**
     * Estilos futuristas inline
     */
    private function get_futuristic_styles_inline() {
        return '
        html body .wvp-futuristic .wvp-product-price-container,
        html .woocommerce .wvp-futuristic .wvp-product-price-container,
        html .woocommerce-page .wvp-futuristic .wvp-product-price-container,
        html .single-product .wvp-futuristic .wvp-product-price-container,
        html .shop .wvp-futuristic .wvp-product-price-container,
        html .cart .wvp-futuristic .wvp-product-price-container,
        html .checkout .wvp-futuristic .wvp-product-price-container {
            background: linear-gradient(45deg, #0a0a0a, #1a1a1a) !important;
            border: 2px solid #00ffff !important;
            border-radius: 20px !important;
            padding: 25px !important;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3) !important;
            color: #00ffff !important;
        }
        
        html body .wvp-futuristic .wvp-price-usd,
        html body .wvp-futuristic .wvp-price-ves,
        html .woocommerce .wvp-futuristic .wvp-price-usd,
        html .woocommerce .wvp-futuristic .wvp-price-ves,
        html .woocommerce-page .wvp-futuristic .wvp-price-usd,
        html .woocommerce-page .wvp-futuristic .wvp-price-ves,
        html .single-product .wvp-futuristic .wvp-price-usd,
        html .single-product .wvp-futuristic .wvp-price-ves,
        html .shop .wvp-futuristic .wvp-price-usd,
        html .shop .wvp-futuristic .wvp-price-ves,
        html .cart .wvp-futuristic .wvp-price-usd,
        html .cart .wvp-futuristic .wvp-price-ves,
        html .checkout .wvp-futuristic .wvp-price-usd,
        html .checkout .wvp-futuristic .wvp-price-ves {
            color: #00ffff !important;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5) !important;
        }
        
        html body .wvp-futuristic .wvp-currency-switcher,
        html .woocommerce .wvp-futuristic .wvp-currency-switcher,
        html .woocommerce-page .wvp-futuristic .wvp-currency-switcher,
        html .single-product .wvp-futuristic .wvp-currency-switcher,
        html .shop .wvp-futuristic .wvp-currency-switcher,
        html .cart .wvp-futuristic .wvp-currency-switcher,
        html .checkout .wvp-futuristic .wvp-currency-switcher {
            display: flex !important;
            gap: 15px !important;
            margin: 20px 0 !important;
        }
        
        html body .wvp-futuristic .wvp-currency-switcher button,
        html body .wvp-futuristic .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-futuristic .wvp-currency-switcher button,
        html .woocommerce .wvp-futuristic .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-futuristic .wvp-currency-switcher button,
        html .woocommerce-page .wvp-futuristic .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-futuristic .wvp-currency-switcher button,
        html .single-product .wvp-futuristic .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-futuristic .wvp-currency-switcher button,
        html .shop .wvp-futuristic .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-futuristic .wvp-currency-switcher button,
        html .cart .wvp-futuristic .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-futuristic .wvp-currency-switcher button,
        html .checkout .wvp-futuristic .wvp-currency-switcher .wvp-currency-option {
            background: transparent !important;
            border: 2px solid #00ffff !important;
            color: #00ffff !important;
            border-radius: 15px !important;
            padding: 10px 20px !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            transition: all 0.3s ease !important;
        }
        
        html body .wvp-futuristic .wvp-currency-switcher button:hover,
        html body .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce .wvp-futuristic .wvp-currency-switcher button:hover,
        html .woocommerce .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce-page .wvp-futuristic .wvp-currency-switcher button:hover,
        html .woocommerce-page .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover,
        html .single-product .wvp-futuristic .wvp-currency-switcher button:hover,
        html .single-product .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover,
        html .shop .wvp-futuristic .wvp-currency-switcher button:hover,
        html .shop .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover,
        html .cart .wvp-futuristic .wvp-currency-switcher button:hover,
        html .cart .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover,
        html .checkout .wvp-futuristic .wvp-currency-switcher button:hover,
        html .checkout .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover {
            background: rgba(0, 255, 255, 0.1) !important;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.5) !important;
        }
        
        html body .wvp-futuristic .wvp-price-conversion,
        html .woocommerce .wvp-futuristic .wvp-price-conversion,
        html .woocommerce-page .wvp-futuristic .wvp-price-conversion,
        html .single-product .wvp-futuristic .wvp-price-conversion,
        html .shop .wvp-futuristic .wvp-price-conversion,
        html .cart .wvp-futuristic .wvp-price-conversion,
        html .checkout .wvp-futuristic .wvp-price-conversion {
            background: rgba(0, 255, 255, 0.1) !important;
            border: 1px solid #00ffff !important;
            color: #00ffff !important;
            border-radius: 10px !important;
            text-shadow: 0 0 5px rgba(0, 255, 255, 0.3) !important;
        }
        ';
    }
    
    /**
     * Estilos minimalistas avanzados inline
     */
    private function get_advanced_minimal_styles_inline() {
        return '
        html body .wvp-advanced-minimal .wvp-product-price-container,
        html .woocommerce .wvp-advanced-minimal .wvp-product-price-container,
        html .woocommerce-page .wvp-advanced-minimal .wvp-product-price-container,
        html .single-product .wvp-advanced-minimal .wvp-product-price-container,
        html .shop .wvp-advanced-minimal .wvp-product-price-container,
        html .cart .wvp-advanced-minimal .wvp-product-price-container,
        html .checkout .wvp-advanced-minimal .wvp-product-price-container {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 10px 0 !important;
        }
        
        html body .wvp-advanced-minimal .wvp-currency-switcher,
        html .woocommerce .wvp-advanced-minimal .wvp-currency-switcher,
        html .woocommerce-page .wvp-advanced-minimal .wvp-currency-switcher,
        html .single-product .wvp-advanced-minimal .wvp-currency-switcher,
        html .shop .wvp-advanced-minimal .wvp-currency-switcher,
        html .cart .wvp-advanced-minimal .wvp-currency-switcher,
        html .checkout .wvp-advanced-minimal .wvp-currency-switcher {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin: 6px 0 !important;
        }
        
        html body .wvp-advanced-minimal .wvp-currency-switcher button,
        html body .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce .wvp-advanced-minimal .wvp-currency-switcher button,
        html .woocommerce .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option,
        html .woocommerce-page .wvp-advanced-minimal .wvp-currency-switcher button,
        html .woocommerce-page .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option,
        html .single-product .wvp-advanced-minimal .wvp-currency-switcher button,
        html .single-product .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option,
        html .shop .wvp-advanced-minimal .wvp-currency-switcher button,
        html .shop .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option,
        html .cart .wvp-advanced-minimal .wvp-currency-switcher button,
        html .cart .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option,
        html .checkout .wvp-advanced-minimal .wvp-currency-switcher button,
        html .checkout .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option {
            padding: 3px 6px !important;
            border: none !important;
            border-bottom: 2px solid transparent !important;
            background: transparent !important;
            color: #666 !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
            min-width: 40px !important;
            text-align: center !important;
        }
        
        html body .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        html body .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        html .woocommerce .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover,
        html .woocommerce-page .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        html .woocommerce-page .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover,
        html .single-product .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        html .single-product .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover,
        html .shop .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        html .shop .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover,
        html .cart .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        html .cart .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover,
        html .checkout .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        html .checkout .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover {
            border-bottom-color: var(--wvp-primary-color) !important;
            color: var(--wvp-primary-color) !important;
        }
        
        html body .wvp-advanced-minimal .wvp-price-conversion,
        html .woocommerce .wvp-advanced-minimal .wvp-price-conversion,
        html .woocommerce-page .wvp-advanced-minimal .wvp-price-conversion,
        html .single-product .wvp-advanced-minimal .wvp-price-conversion,
        html .shop .wvp-advanced-minimal .wvp-price-conversion,
        html .cart .wvp-advanced-minimal .wvp-price-conversion,
        html .checkout .wvp-advanced-minimal .wvp-price-conversion {
            background: rgba(var(--wvp-primary-color-rgb, 0, 124, 186), 0.05) !important;
            border: none !important;
            border-left: 3px solid var(--wvp-primary-color) !important;
            border-radius: 0 !important;
            padding: 6px 12px !important;
            font-size: 11px !important;
            font-style: italic !important;
            color: #666 !important;
        }
        ';
    }
    
    /**
     * Aplicar CSS inline
     */
    public function apply_inline_css() {
        // Generar CSS inline dinámicamente
        $this->generate_inline_css();
        
        if ($this->inline_css) {
            echo '<style id="wvp-inline-css-high-priority" type="text/css">' . $this->inline_css . '</style>';
        }
    }
    
    /**
     * Aplicar CSS inline como respaldo
     */
    public function apply_inline_css_fallback() {
        // Solo aplicar si no se aplicó en wp_head
        if (!$this->inline_css) {
            $this->generate_inline_css();
            
            if ($this->inline_css) {
                echo '<style id="wvp-inline-css-fallback" type="text/css">' . $this->inline_css . '</style>';
            }
        }
    }
    
    /**
     * Regenerar CSS inline
     */
    public function regenerate_inline_css() {
        $this->generate_inline_css();
    }
    
    /**
     * Obtener configuraciones de apariencia
     */
    private function get_appearance_settings() {
        return array(
            'display_style' => get_option('wvp_display_style', 'minimal'),
            'primary_color' => get_option('wvp_primary_color', '#007cba'),
            'secondary_color' => get_option('wvp_secondary_color', '#005a87'),
            'success_color' => get_option('wvp_success_color', '#28a745'),
            'warning_color' => get_option('wvp_warning_color', '#ffc107'),
            'font_family' => get_option('wvp_font_family', 'system'),
            'font_size' => get_option('wvp_font_size', 'medium'),
            'font_weight' => get_option('wvp_font_weight', '400'),
            'text_transform' => get_option('wvp_text_transform', 'none'),
            'padding' => get_option('wvp_padding', 'medium'),
            'margin' => get_option('wvp_margin', 'medium'),
            'border_radius' => get_option('wvp_border_radius', 'medium'),
            'shadow' => get_option('wvp_shadow', 'small')
        );
    }
    
    /**
     * Obtener familias de fuentes
     */
    private function get_font_families() {
        return array(
            'system' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'arial' => 'Arial, sans-serif',
            'helvetica' => 'Helvetica, Arial, sans-serif',
            'georgia' => 'Georgia, serif',
            'times' => '"Times New Roman", Times, serif',
            'verdana' => 'Verdana, sans-serif',
            'tahoma' => 'Tahoma, sans-serif',
            'trebuchet' => '"Trebuchet MS", sans-serif',
            'courier' => '"Courier New", monospace',
            'monospace' => 'monospace'
        );
    }
    
    /**
     * Obtener tamaños de fuente
     */
    private function get_font_sizes() {
        return array(
            'small' => '12px',
            'medium' => '14px',
            'large' => '16px',
            'xlarge' => '18px',
            'xxlarge' => '20px'
        );
    }
    
    /**
     * Obtener espaciados
     */
    private function get_spacings() {
        return array(
            'none' => '0px',
            'small' => '5px',
            'medium' => '10px',
            'large' => '15px',
            'xlarge' => '20px',
            'round' => '50px'
        );
    }
    
    /**
     * Obtener sombras
     */
    private function get_shadows() {
        return array(
            'none' => 'none',
            'small' => '0 2px 4px rgba(0,0,0,0.1)',
            'medium' => '0 4px 8px rgba(0,0,0,0.15)',
            'large' => '0 8px 16px rgba(0,0,0,0.2)',
            'glow' => '0 0 10px rgba(0,115,170,0.3)'
        );
    }
    
    /**
     * Convertir hex a RGB
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        if (strlen($hex) !== 6) {
            return null;
        }
        
        return array(
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        );
    }
}

// Inicializar el manejador de CSS inline
WVP_Inline_CSS_Manager::get_instance();
?>
