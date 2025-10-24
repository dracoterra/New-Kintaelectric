<?php
/**
 * Forzador de CSS Frontend - WooCommerce Venezuela Pro
 * Fuerza la aplicación de CSS directamente en el frontend
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Frontend_CSS_Forcer {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Constructor privado
     */
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_css'), 999);
        add_action('wp_head', array($this, 'force_css_in_head'), 1);
        add_action('wp_footer', array($this, 'force_css_in_footer'), 1);
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
     * Encolar CSS en frontend
     */
    public function enqueue_frontend_css() {
        // Crear archivo CSS dinámico
        $css_content = $this->generate_frontend_css();
        
        // Registrar estilo
        wp_register_style('wvp-frontend-forced', false);
        wp_enqueue_style('wvp-frontend-forced');
        
        // Agregar CSS inline
        wp_add_inline_style('wvp-frontend-forced', $css_content);
    }
    
    /**
     * Forzar CSS en head
     */
    public function force_css_in_head() {
        $css_content = $this->generate_frontend_css();
        echo '<style id="wvp-forced-css-head" type="text/css">' . $css_content . '</style>';
    }
    
    /**
     * Forzar CSS en footer
     */
    public function force_css_in_footer() {
        $css_content = $this->generate_frontend_css();
        echo '<style id="wvp-forced-css-footer" type="text/css">' . $css_content . '</style>';
    }
    
    /**
     * Generar CSS para frontend
     */
    private function generate_frontend_css() {
        $settings = $this->get_appearance_settings();
        $primary_rgb = $this->hex_to_rgb($settings['primary_color']);
        
        $css = '
        /* CSS Forzado para Frontend - WooCommerce Venezuela Pro */
        
        /* Variables CSS */
        :root {
            --wvp-primary-color: ' . $settings['primary_color'] . ' !important;
            --wvp-secondary-color: ' . $settings['secondary_color'] . ' !important;
            --wvp-primary-color-rgb: ' . ($primary_rgb ? $primary_rgb['r'] . ', ' . $primary_rgb['g'] . ', ' . $primary_rgb['b'] : '0, 124, 186') . ' !important;
        }
        
        /* Estilos base con máxima especificidad */
        body .wvp-product-price-container,
        .woocommerce .wvp-product-price-container,
        .woocommerce-page .wvp-product-price-container,
        .single-product .wvp-product-price-container,
        .shop .wvp-product-price-container,
        .cart .wvp-product-price-container,
        .checkout .wvp-product-price-container {
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            margin: ' . $this->get_spacings()[$settings['margin']] . ' !important;
            overflow: visible !important;
            height: auto !important;
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
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: ' . $this->get_font_sizes()[$settings['font_size']] . ' !important;
            font-weight: ' . $settings['font_weight'] . ' !important;
            text-transform: ' . $settings['text_transform'] . ' !important;
            color: #333333 !important;
            transition: all 0.3s ease !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
        }
        
        body .wvp-price-ves,
        .woocommerce .wvp-price-ves,
        .woocommerce-page .wvp-price-ves,
        .single-product .wvp-price-ves,
        .shop .wvp-price-ves,
        .cart .wvp-price-ves,
        .checkout .wvp-price-ves {
            color: ' . $settings['secondary_color'] . ' !important;
        }
        
        /* Botones de moneda */
        body .wvp-currency-switcher,
        .woocommerce .wvp-currency-switcher,
        .woocommerce-page .wvp-currency-switcher,
        .single-product .wvp-currency-switcher,
        .shop .wvp-currency-switcher,
        .cart .wvp-currency-switcher,
        .checkout .wvp-currency-switcher {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin: 8px 0 !important;
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
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
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
            background-color: ' . $settings['primary_color'] . ' !important;
            border-color: ' . $settings['primary_color'] . ' !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
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
            border-color: ' . $settings['primary_color'] . ' !important;
            color: ' . $settings['primary_color'] . ' !important;
        }
        
        /* Botones de carrito */
        body .wvp-add-to-cart,
        .woocommerce .wvp-add-to-cart,
        .woocommerce-page .wvp-add-to-cart,
        .single-product .wvp-add-to-cart,
        .shop .wvp-add-to-cart,
        .cart .wvp-add-to-cart,
        .checkout .wvp-add-to-cart {
            background-color: ' . $settings['primary_color'] . ' !important;
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
        
        body .wvp-add-to-cart:hover,
        .woocommerce .wvp-add-to-cart:hover,
        .woocommerce-page .wvp-add-to-cart:hover,
        .single-product .wvp-add-to-cart:hover,
        .shop .wvp-add-to-cart:hover,
        .cart .wvp-add-to-cart:hover,
        .checkout .wvp-add-to-cart:hover {
            background-color: ' . $settings['secondary_color'] . ' !important;
            transform: scale(1.05) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        }
        
        /* Contenedores de producto */
        body .wvp-product-container,
        .woocommerce .wvp-product-container,
        .woocommerce-page .wvp-product-container,
        .single-product .wvp-product-container,
        .shop .wvp-product-container,
        .cart .wvp-product-container,
        .checkout .wvp-product-container {
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
        
        /* Títulos de producto */
        body .wvp-product-title,
        .woocommerce .wvp-product-title,
        .woocommerce-page .wvp-product-title,
        .single-product .wvp-product-title,
        .shop .wvp-product-title,
        .cart .wvp-product-title,
        .checkout .wvp-product-title {
            color: #333333 !important;
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            margin: 8px 0 !important;
            padding: 0 !important;
            line-height: 1.3 !important;
            overflow: visible !important;
            white-space: normal !important;
            height: auto !important;
        }
        
        /* Categorías de producto */
        body .wvp-product-category,
        .woocommerce .wvp-product-category,
        .woocommerce-page .wvp-product-category,
        .single-product .wvp-product-category,
        .shop .wvp-product-category,
        .cart .wvp-product-category,
        .checkout .wvp-product-category {
            color: #999999 !important;
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            margin: 5px 0 0 0 !important;
            padding: 0 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }
        
        /* Elementos de wishlist y compare */
        body .wvp-product-actions,
        .woocommerce .wvp-product-actions,
        .woocommerce-page .wvp-product-actions,
        .single-product .wvp-product-actions,
        .shop .wvp-product-actions,
        .cart .wvp-product-actions,
        .checkout .wvp-product-actions {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin: 10px 0 !important;
            color: #999999 !important;
            font-size: 12px !important;
        }
        
        body .wvp-product-actions a,
        .woocommerce .wvp-product-actions a,
        .woocommerce-page .wvp-product-actions a,
        .single-product .wvp-product-actions a,
        .shop .wvp-product-actions a,
        .cart .wvp-product-actions a,
        .checkout .wvp-product-actions a {
            color: #999999 !important;
            text-decoration: none !important;
            transition: color 0.3s ease !important;
        }
        
        body .wvp-product-actions a:hover,
        .woocommerce .wvp-product-actions a:hover,
        .woocommerce-page .wvp-product-actions a:hover,
        .single-product .wvp-product-actions a:hover,
        .shop .wvp-product-actions a:hover,
        .cart .wvp-product-actions a:hover,
        .checkout .wvp-product-actions a:hover {
            color: ' . $settings['primary_color'] . ' !important;
        }
        
        /* Conversión de precios */
        body .wvp-price-conversion,
        .woocommerce .wvp-price-conversion,
        .woocommerce-page .wvp-price-conversion,
        .single-product .wvp-price-conversion,
        .shop .wvp-price-conversion,
        .cart .wvp-price-conversion,
        .checkout .wvp-price-conversion {
            background-color: rgba(' . ($primary_rgb ? $primary_rgb['r'] . ', ' . $primary_rgb['g'] . ', ' . $primary_rgb['b'] : '0, 124, 186') . ', 0.1) !important;
            border: 1px solid ' . $settings['primary_color'] . ' !important;
            border-radius: ' . $this->get_spacings()[$settings['border_radius']] . ' !important;
            padding: ' . $this->get_spacings()[$settings['padding']] . ' !important;
            margin: ' . $this->get_spacings()[$settings['margin']] . ' !important;
            box-shadow: ' . $this->get_shadows()[$settings['shadow']] . ' !important;
            color: ' . $settings['secondary_color'] . ' !important;
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            transition: all 0.3s ease !important;
        }
        
        /* Información de tasa */
        body .wvp-rate-info,
        .woocommerce .wvp-rate-info,
        .woocommerce-page .wvp-rate-info,
        .single-product .wvp-rate-info,
        .shop .wvp-rate-info,
        .cart .wvp-rate-info,
        .checkout .wvp-rate-info {
            color: ' . $settings['secondary_color'] . ' !important;
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: calc(' . $this->get_font_sizes()[$settings['font_size']] . ' * 0.8) !important;
            transition: all 0.3s ease !important;
        }
        ';
        
        return $css;
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

// Inicializar el forzador de CSS frontend
WVP_Frontend_CSS_Forcer::get_instance();
?>
