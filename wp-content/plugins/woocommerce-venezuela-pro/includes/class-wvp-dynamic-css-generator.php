<?php
/**
 * Sistema de CSS Dinámico - WooCommerce Venezuela Pro
 * Genera CSS dinámico basado en las configuraciones de apariencia
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Dynamic_CSS_Generator {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Configuraciones de apariencia
     */
    private $appearance_settings = null;
    
    /**
     * CSS generado
     */
    private $generated_css = null;
    
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
        // Cargar configuraciones
        $this->load_appearance_settings();
        
        // Generar CSS dinámico
        $this->generate_dynamic_css();
        
        // Aplicar CSS en el frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dynamic_css'));
        
        // Hook para regenerar CSS cuando cambien las configuraciones
        add_action('update_option_wvp_display_style', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_primary_color', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_secondary_color', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_success_color', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_warning_color', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_font_family', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_font_size', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_font_weight', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_text_transform', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_padding', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_margin', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_border_radius', array($this, 'clear_css_cache'));
        add_action('update_option_wvp_shadow', array($this, 'clear_css_cache'));
    }
    
    /**
     * Cargar configuraciones de apariencia
     */
    private function load_appearance_settings() {
        $this->appearance_settings = array(
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
     * Generar CSS dinámico
     */
    private function generate_dynamic_css() {
        // Verificar si ya está en caché
        $cache_key = 'wvp_dynamic_css_' . md5(serialize($this->appearance_settings));
        $cached_css = get_transient($cache_key);
        
        if ($cached_css !== false) {
            $this->generated_css = $cached_css;
            return;
        }
        
        // Generar CSS
        $css = $this->build_dynamic_css();
        
        // Guardar en caché por 1 hora
        set_transient($cache_key, $css, HOUR_IN_SECONDS);
        
        $this->generated_css = $css;
    }
    
    /**
     * Construir CSS dinámico
     */
    private function build_dynamic_css() {
        $css = '';
        
        // Variables CSS
        $css .= $this->generate_css_variables();
        
        // Estilos base
        $css .= $this->generate_base_styles();
        
        // Estilos específicos por estilo
        $css .= $this->generate_style_specific_css();
        
        // Estilos para elementos específicos
        $css .= $this->generate_element_specific_css();
        
        // Estilos responsive
        $css .= $this->generate_responsive_css();
        
        return $css;
    }
    
    /**
     * Generar variables CSS
     */
    private function generate_css_variables() {
        $primary_rgb = $this->hex_to_rgb($this->appearance_settings['primary_color']);
        
        $css = ':root {';
        $css .= '--wvp-primary-color: ' . $this->appearance_settings['primary_color'] . ';';
        $css .= '--wvp-secondary-color: ' . $this->appearance_settings['secondary_color'] . ';';
        $css .= '--wvp-success-color: ' . $this->appearance_settings['success_color'] . ';';
        $css .= '--wvp-warning-color: ' . $this->appearance_settings['warning_color'] . ';';
        
        if ($primary_rgb) {
            $css .= '--wvp-primary-color-rgb: ' . $primary_rgb['r'] . ', ' . $primary_rgb['g'] . ', ' . $primary_rgb['b'] . ';';
        }
        
        // Variables de fuente
        $font_families = $this->get_font_families();
        $font_sizes = $this->get_font_sizes();
        
        $css .= '--wvp-font-family: ' . $font_families[$this->appearance_settings['font_family']] . ';';
        $css .= '--wvp-font-size: ' . $font_sizes[$this->appearance_settings['font_size']] . ';';
        $css .= '--wvp-font-weight: ' . $this->appearance_settings['font_weight'] . ';';
        $css .= '--wvp-text-transform: ' . $this->appearance_settings['text_transform'] . ';';
        
        // Variables de espaciado
        $spacings = $this->get_spacings();
        $css .= '--wvp-padding: ' . $spacings[$this->appearance_settings['padding']] . ';';
        $css .= '--wvp-margin: ' . $spacings[$this->appearance_settings['margin']] . ';';
        $css .= '--wvp-border-radius: ' . $spacings[$this->appearance_settings['border_radius']] . ';';
        
        // Variables de sombra
        $shadows = $this->get_shadows();
        $css .= '--wvp-shadow: ' . $shadows[$this->appearance_settings['shadow']] . ';';
        
        $css .= '}';
        
        return $css;
    }
    
    /**
     * Generar estilos base con alta especificidad
     */
    private function generate_base_styles() {
        $css = '
        /* Estilos base para elementos WVP con alta especificidad */
        body .wvp-product-price-container,
        .woocommerce .wvp-product-price-container,
        .woocommerce-page .wvp-product-price-container,
        .single-product .wvp-product-price-container,
        .shop .wvp-product-price-container,
        .cart .wvp-product-price-container,
        .checkout .wvp-product-price-container {
            font-family: var(--wvp-font-family) !important;
            margin: var(--wvp-margin) !important;
        }
        
        body .wvp-price-usd,
        body .wvp-price-ves,
        .woocommerce .wvp-price-usd,
        .woocommerce .wvp-price-ves,
        .woocommerce-page .wvp-price-usd,
        .woocommerce-page .wvp-price-ves,
        .single-product .wvp-price-usd,
        .single-product .wvp-price-ves,
        .shop .wvp-price-usd,
        .shop .wvp-price-ves,
        .cart .wvp-price-usd,
        .cart .wvp-price-ves,
        .checkout .wvp-price-usd,
        .checkout .wvp-price-ves {
            font-family: var(--wvp-font-family) !important;
            font-size: var(--wvp-font-size) !important;
            font-weight: var(--wvp-font-weight) !important;
            text-transform: var(--wvp-text-transform) !important;
            color: #333 !important;
            transition: all 0.3s ease !important;
        }
        
        body .wvp-price-ves,
        .woocommerce .wvp-price-ves,
        .woocommerce-page .wvp-price-ves,
        .single-product .wvp-price-ves,
        .shop .wvp-price-ves,
        .cart .wvp-price-ves,
        .checkout .wvp-price-ves {
            color: var(--wvp-secondary-color) !important;
        }
        
        body .wvp-currency-switcher button,
        body .wvp-currency-switcher .wvp-currency-option,
        .woocommerce .wvp-currency-switcher button,
        .woocommerce .wvp-currency-switcher .wvp-currency-option,
        .woocommerce-page .wvp-currency-switcher button,
        .woocommerce-page .wvp-currency-switcher .wvp-currency-option,
        .single-product .wvp-currency-switcher button,
        .single-product .wvp-currency-switcher .wvp-currency-option,
        .shop .wvp-currency-switcher button,
        .shop .wvp-currency-switcher .wvp-currency-option,
        .cart .wvp-currency-switcher button,
        .cart .wvp-currency-switcher .wvp-currency-option,
        .checkout .wvp-currency-switcher button,
        .checkout .wvp-currency-switcher .wvp-currency-option {
            font-family: var(--wvp-font-family) !important;
            font-weight: var(--wvp-font-weight) !important;
            text-transform: var(--wvp-text-transform) !important;
            transition: all 0.3s ease !important;
        }
        
        body .wvp-currency-switcher button.active,
        body .wvp-currency-switcher .wvp-currency-option.active,
        .woocommerce .wvp-currency-switcher button.active,
        .woocommerce .wvp-currency-switcher .wvp-currency-option.active,
        .woocommerce-page .wvp-currency-switcher button.active,
        .woocommerce-page .wvp-currency-switcher .wvp-currency-option.active,
        .single-product .wvp-currency-switcher button.active,
        .single-product .wvp-currency-switcher .wvp-currency-option.active,
        .shop .wvp-currency-switcher button.active,
        .shop .wvp-currency-switcher .wvp-currency-option.active,
        .cart .wvp-currency-switcher button.active,
        .cart .wvp-currency-switcher .wvp-currency-option.active,
        .checkout .wvp-currency-switcher button.active,
        .checkout .wvp-currency-switcher .wvp-currency-option.active {
            background-color: var(--wvp-primary-color) !important;
            border-color: var(--wvp-primary-color) !important;
            color: #ffffff !important;
        }
        
        body .wvp-currency-switcher button:hover,
        body .wvp-currency-switcher .wvp-currency-option:hover,
        .woocommerce .wvp-currency-switcher button:hover,
        .woocommerce .wvp-currency-switcher .wvp-currency-option:hover,
        .woocommerce-page .wvp-currency-switcher button:hover,
        .woocommerce-page .wvp-currency-switcher .wvp-currency-option:hover,
        .single-product .wvp-currency-switcher button:hover,
        .single-product .wvp-currency-switcher .wvp-currency-option:hover,
        .shop .wvp-currency-switcher button:hover,
        .shop .wvp-currency-switcher .wvp-currency-option:hover,
        .cart .wvp-currency-switcher button:hover,
        .cart .wvp-currency-switcher .wvp-currency-option:hover,
        .checkout .wvp-currency-switcher button:hover,
        .checkout .wvp-currency-switcher .wvp-currency-option:hover {
            border-color: var(--wvp-primary-color) !important;
            color: var(--wvp-primary-color) !important;
        }
        
        body .wvp-price-conversion,
        .woocommerce .wvp-price-conversion,
        .woocommerce-page .wvp-price-conversion,
        .single-product .wvp-price-conversion,
        .shop .wvp-price-conversion,
        .cart .wvp-price-conversion,
        .checkout .wvp-price-conversion {
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
        
        body .wvp-rate-info,
        .woocommerce .wvp-rate-info,
        .woocommerce-page .wvp-rate-info,
        .single-product .wvp-rate-info,
        .shop .wvp-rate-info,
        .cart .wvp-rate-info,
        .checkout .wvp-rate-info {
            color: var(--wvp-secondary-color) !important;
            font-family: var(--wvp-font-family) !important;
            font-size: calc(var(--wvp-font-size) * 0.8) !important;
            transition: all 0.3s ease !important;
        }
        ';
        
        return $css;
    }
    
    /**
     * Generar estilos específicos por estilo
     */
    private function generate_style_specific_css() {
        $style = $this->appearance_settings['display_style'];
        $css = '';
        
        switch ($style) {
            case 'minimal':
                $css .= $this->get_minimal_styles();
                break;
            case 'modern':
                $css .= $this->get_modern_styles();
                break;
            case 'elegant':
                $css .= $this->get_elegant_styles();
                break;
            case 'compact':
                $css .= $this->get_compact_styles();
                break;
            case 'vintage':
                $css .= $this->get_vintage_styles();
                break;
            case 'futuristic':
                $css .= $this->get_futuristic_styles();
                break;
            case 'advanced-minimal':
                $css .= $this->get_advanced_minimal_styles();
                break;
            default:
                $css .= $this->get_minimal_styles();
        }
        
        return $css;
    }
    
    /**
     * Estilos minimalistas con alta especificidad
     */
    private function get_minimal_styles() {
        return '
        body .wvp-minimal .wvp-product-price-container,
        .woocommerce .wvp-minimal .wvp-product-price-container,
        .woocommerce-page .wvp-minimal .wvp-product-price-container,
        .single-product .wvp-minimal .wvp-product-price-container,
        .shop .wvp-minimal .wvp-product-price-container,
        .cart .wvp-minimal .wvp-product-price-container,
        .checkout .wvp-minimal .wvp-product-price-container {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        
        body .wvp-minimal .wvp-currency-switcher,
        .woocommerce .wvp-minimal .wvp-currency-switcher,
        .woocommerce-page .wvp-minimal .wvp-currency-switcher,
        .single-product .wvp-minimal .wvp-currency-switcher,
        .shop .wvp-minimal .wvp-currency-switcher,
        .cart .wvp-minimal .wvp-currency-switcher,
        .checkout .wvp-minimal .wvp-currency-switcher {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin: 8px 0 !important;
        }
        
        body .wvp-minimal .wvp-currency-switcher button,
        body .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        .woocommerce .wvp-minimal .wvp-currency-switcher button,
        .woocommerce .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        .woocommerce-page .wvp-minimal .wvp-currency-switcher button,
        .woocommerce-page .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        .single-product .wvp-minimal .wvp-currency-switcher button,
        .single-product .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        .shop .wvp-minimal .wvp-currency-switcher button,
        .shop .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        .cart .wvp-minimal .wvp-currency-switcher button,
        .cart .wvp-minimal .wvp-currency-switcher .wvp-currency-option,
        .checkout .wvp-minimal .wvp-currency-switcher button,
        .checkout .wvp-minimal .wvp-currency-switcher .wvp-currency-option {
            padding: 4px 8px !important;
            border: 1px solid #ddd !important;
            background: transparent !important;
            color: #666 !important;
            border-radius: 3px !important;
            font-size: 13px !important;
            min-width: 50px !important;
            text-align: center !important;
        }
        
        body .wvp-minimal .wvp-price-conversion,
        .woocommerce .wvp-minimal .wvp-price-conversion,
        .woocommerce-page .wvp-minimal .wvp-price-conversion,
        .single-product .wvp-minimal .wvp-price-conversion,
        .shop .wvp-minimal .wvp-price-conversion,
        .cart .wvp-minimal .wvp-price-conversion,
        .checkout .wvp-minimal .wvp-price-conversion {
            background: #f8f9fa !important;
            border: 1px solid #e9ecef !important;
            font-size: 12px !important;
            font-style: italic !important;
        }
        ';
    }
    
    /**
     * Estilos modernos
     */
    private function get_modern_styles() {
        return '
        .wvp-modern .wvp-product-price-container {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .wvp-modern .wvp-currency-switcher {
            display: flex;
            gap: 8px;
            margin: 15px 0;
        }
        
        .wvp-modern .wvp-currency-switcher button,
        .wvp-modern .wvp-currency-switcher .wvp-currency-option {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .wvp-modern .wvp-currency-switcher button:hover,
        .wvp-modern .wvp-currency-switcher .wvp-currency-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .wvp-modern .wvp-price-conversion {
            background: linear-gradient(135deg, rgba(var(--wvp-primary-color-rgb, 0, 124, 186), 0.1), rgba(var(--wvp-primary-color-rgb, 0, 124, 186), 0.05));
            border: 1px solid var(--wvp-primary-color);
            border-radius: 8px;
            font-weight: 500;
        }
        ';
    }
    
    /**
     * Estilos elegantes
     */
    private function get_elegant_styles() {
        return '
        .wvp-elegant .wvp-product-price-container {
            background: #ffffff;
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        }
        
        .wvp-elegant .wvp-currency-switcher {
            display: flex;
            gap: 12px;
            margin: 20px 0;
        }
        
        .wvp-elegant .wvp-currency-switcher button,
        .wvp-elegant .wvp-currency-switcher .wvp-currency-option {
            background: #ffffff;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.4s ease;
        }
        
        .wvp-elegant .wvp-price-conversion {
            background: #fafafa;
            border: 1px solid #e8e8e8;
            border-radius: 12px;
            font-style: italic;
            letter-spacing: 0.3px;
        }
        ';
    }
    
    /**
     * Estilos compactos
     */
    private function get_compact_styles() {
        return '
        .wvp-compact .wvp-product-price-container {
            background: transparent;
            padding: 0;
            margin: 5px 0;
        }
        
        .wvp-compact .wvp-currency-switcher {
            display: flex;
            gap: 4px;
            margin: 5px 0;
        }
        
        .wvp-compact .wvp-currency-switcher button,
        .wvp-compact .wvp-currency-switcher .wvp-currency-option {
            padding: 3px 6px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            border-radius: 2px;
            font-size: 11px;
            min-width: 35px;
        }
        
        .wvp-compact .wvp-price-conversion {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 4px 8px;
            font-size: 11px;
            margin: 3px 0;
        }
        ';
    }
    
    /**
     * Estilos vintage
     */
    private function get_vintage_styles() {
        return '
        .wvp-vintage .wvp-product-price-container {
            background: #f7f3e9;
            border: 3px solid #8b4513;
            border-radius: 0;
            padding: 20px;
            box-shadow: inset 0 0 10px rgba(139, 69, 19, 0.1);
        }
        
        .wvp-vintage .wvp-currency-switcher {
            display: flex;
            gap: 10px;
            margin: 15px 0;
        }
        
        .wvp-vintage .wvp-currency-switcher button,
        .wvp-vintage .wvp-currency-switcher .wvp-currency-option {
            background: #8b4513;
            border: 2px solid #654321;
            color: #f7f3e9;
            border-radius: 0;
            padding: 8px 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .wvp-vintage .wvp-price-conversion {
            background: #654321;
            color: #f7f3e9;
            border: 2px solid #8b4513;
            border-radius: 0;
            font-weight: bold;
        }
        ';
    }
    
    /**
     * Estilos futuristas
     */
    private function get_futuristic_styles() {
        return '
        .wvp-futuristic .wvp-product-price-container {
            background: linear-gradient(45deg, #0a0a0a, #1a1a1a);
            border: 2px solid #00ffff;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
            color: #00ffff;
        }
        
        .wvp-futuristic .wvp-price-usd,
        .wvp-futuristic .wvp-price-ves {
            color: #00ffff;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }
        
        .wvp-futuristic .wvp-currency-switcher {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        
        .wvp-futuristic .wvp-currency-switcher button,
        .wvp-futuristic .wvp-currency-switcher .wvp-currency-option {
            background: transparent;
            border: 2px solid #00ffff;
            color: #00ffff;
            border-radius: 15px;
            padding: 10px 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
        }
        
        .wvp-futuristic .wvp-currency-switcher button:hover,
        .wvp-futuristic .wvp-currency-switcher .wvp-currency-option:hover {
            background: rgba(0, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        }
        
        .wvp-futuristic .wvp-price-conversion {
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid #00ffff;
            color: #00ffff;
            border-radius: 10px;
            text-shadow: 0 0 5px rgba(0, 255, 255, 0.3);
        }
        ';
    }
    
    /**
     * Estilos minimalistas avanzados
     */
    private function get_advanced_minimal_styles() {
        return '
        .wvp-advanced-minimal .wvp-product-price-container {
            background: transparent;
            border: none;
            padding: 0;
            margin: 10px 0;
        }
        
        .wvp-advanced-minimal .wvp-currency-switcher {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 6px 0;
        }
        
        .wvp-advanced-minimal .wvp-currency-switcher button,
        .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option {
            padding: 3px 6px;
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #666;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            min-width: 40px;
            text-align: center;
        }
        
        .wvp-advanced-minimal .wvp-currency-switcher button:hover,
        .wvp-advanced-minimal .wvp-currency-switcher .wvp-currency-option:hover {
            border-bottom-color: var(--wvp-primary-color);
            color: var(--wvp-primary-color);
        }
        
        .wvp-advanced-minimal .wvp-price-conversion {
            background: rgba(var(--wvp-primary-color-rgb, 0, 124, 186), 0.05);
            border: none;
            border-left: 3px solid var(--wvp-primary-color);
            border-radius: 0;
            padding: 6px 12px;
            font-size: 11px;
            font-style: italic;
            color: #666;
        }
        ';
    }
    
    /**
     * Generar estilos para elementos específicos
     */
    private function generate_element_specific_css() {
        return '
        /* Estilos para shortcodes */
        .wvp-bcv-rate {
            font-family: var(--wvp-font-family);
            font-size: var(--wvp-font-size);
            font-weight: var(--wvp-font-weight);
            color: var(--wvp-secondary-color);
        }
        
        .wvp-bcv-rate.wvp-highlight {
            background: linear-gradient(135deg, var(--wvp-primary-color), var(--wvp-secondary-color));
            color: white;
            border-radius: var(--wvp-border-radius);
            padding: var(--wvp-padding);
            font-weight: 600;
            box-shadow: var(--wvp-shadow);
        }
        
        /* Estilos para widgets */
        .widget .wvp-product-price-container {
            margin: var(--wvp-margin);
        }
        
        .widget .wvp-price-conversion {
            font-size: calc(var(--wvp-font-size) * 0.9);
        }
        
        /* Estilos para footer */
        .footer .wvp-product-price-container {
            margin: calc(var(--wvp-margin) * 0.8);
        }
        
        /* Estilos para carrito */
        .woocommerce-cart .wvp-product-price-container {
            margin: calc(var(--wvp-margin) * 0.7);
        }
        
        /* Estilos para checkout */
        .woocommerce-checkout .wvp-product-price-container {
            margin: calc(var(--wvp-margin) * 0.7);
        }
        ';
    }
    
    /**
     * Generar estilos responsive
     */
    private function generate_responsive_css() {
        return '
        @media (max-width: 768px) {
            .wvp-product-price-container {
                margin: calc(var(--wvp-margin) * 0.8);
            }
            
            .wvp-price-usd,
            .wvp-price-ves {
                font-size: calc(var(--wvp-font-size) * 0.9);
            }
            
            .wvp-price-conversion {
                padding: calc(var(--wvp-padding) * 0.8);
                font-size: calc(var(--wvp-font-size) * 0.8);
            }
            
            .wvp-currency-switcher {
                flex-direction: column;
                gap: 6px;
            }
            
            .wvp-currency-switcher button,
            .wvp-currency-switcher .wvp-currency-option {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .wvp-product-price-container {
                margin: calc(var(--wvp-margin) * 0.6);
            }
            
            .wvp-price-usd,
            .wvp-price-ves {
                font-size: calc(var(--wvp-font-size) * 0.8);
            }
            
            .wvp-price-conversion {
                padding: calc(var(--wvp-padding) * 0.6);
                font-size: calc(var(--wvp-font-size) * 0.7);
            }
        }
        ';
    }
    
    /**
     * Cargar CSS dinámico en el frontend
     */
    public function enqueue_dynamic_css() {
        if ($this->generated_css) {
            // Aplicar filtros de optimización
            $css = apply_filters('wvp_generated_css', $this->generated_css);
            
            // Verificar si hay CSS optimizado disponible
            if (class_exists('WVP_Performance_Optimizer')) {
                $optimizer = WVP_Performance_Optimizer::get_instance();
                $optimized_css = $optimizer->get_optimized_css();
                
                if ($optimized_css) {
                    $css = $optimized_css;
                }
            }
            
            wp_add_inline_style('woocommerce-general', $css);
            
            // El CSS inline se aplica automáticamente con máxima prioridad
            // También mantener el CSS en archivo para compatibilidad
        }
    }
    
    /**
     * Limpiar caché de CSS
     */
    public function clear_css_cache() {
        $cache_key = 'wvp_dynamic_css_' . md5(serialize($this->appearance_settings));
        delete_transient($cache_key);
        
        // Regenerar CSS
        $this->load_appearance_settings();
        $this->generate_dynamic_css();
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
    
    /**
     * Obtener CSS generado
     */
    public function get_generated_css() {
        return $this->generated_css;
    }
    
    /**
     * Forzar regeneración de CSS
     */
    public function force_regenerate() {
        $this->load_appearance_settings();
        $this->generate_dynamic_css();
    }
}

// Inicializar el generador de CSS dinámico
WVP_Dynamic_CSS_Generator::get_instance();
?>
