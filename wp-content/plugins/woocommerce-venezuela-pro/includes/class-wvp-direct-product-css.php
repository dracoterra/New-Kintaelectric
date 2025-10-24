<?php
/**
 * Sistema CSS Inline Directo en Productos - WooCommerce Venezuela Pro
 * Aplica CSS directamente en el HTML de los productos
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Direct_Product_CSS {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Constructor privado
     */
    private function __construct() {
        // Hook para modificar el HTML de productos
        add_filter('woocommerce_loop_product_title', array($this, 'add_css_to_product_title'), 10, 1);
        add_filter('woocommerce_product_get_price_html', array($this, 'add_css_to_price_html'), 10, 2);
        add_action('woocommerce_after_shop_loop_item', array($this, 'inject_product_css'), 5);
        add_action('woocommerce_single_product_summary', array($this, 'inject_single_product_css'), 5);
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
     * Agregar CSS al título del producto
     */
    public function add_css_to_product_title($title) {
        $css = $this->get_product_title_css();
        return '<style>' . $css . '</style>' . $title;
    }
    
    /**
     * Agregar CSS al HTML del precio
     */
    public function add_css_to_price_html($price_html, $product) {
        $css = $this->get_price_css();
        return '<style>' . $css . '</style>' . $price_html;
    }
    
    /**
     * Inyectar CSS en productos de lista
     */
    public function inject_product_css() {
        $css = $this->get_complete_product_css();
        echo '<style id="wvp-direct-product-css">' . $css . '</style>';
    }
    
    /**
     * Inyectar CSS en producto individual
     */
    public function inject_single_product_css() {
        $css = $this->get_complete_product_css();
        echo '<style id="wvp-direct-single-product-css">' . $css . '</style>';
    }
    
    /**
     * Obtener CSS para título del producto
     */
    private function get_product_title_css() {
        $settings = $this->get_appearance_settings();
        
        return '
        .wvp-product-title {
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
            display: block !important;
        }
        ';
    }
    
    /**
     * Obtener CSS para precios
     */
    private function get_price_css() {
        $settings = $this->get_appearance_settings();
        
        return '
        .wvp-price-usd {
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: ' . $this->get_font_sizes()[$settings['font_size']] . ' !important;
            font-weight: ' . $settings['font_weight'] . ' !important;
            text-transform: ' . $settings['text_transform'] . ' !important;
            color: #333333 !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
            display: block !important;
            overflow: visible !important;
            white-space: normal !important;
            height: auto !important;
        }
        
        .wvp-price-ves {
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: ' . $this->get_font_sizes()[$settings['font_size']] . ' !important;
            font-weight: ' . $settings['font_weight'] . ' !important;
            text-transform: ' . $settings['text_transform'] . ' !important;
            color: ' . $settings['secondary_color'] . ' !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
            display: block !important;
            overflow: visible !important;
            white-space: normal !important;
            height: auto !important;
        }
        ';
    }
    
    /**
     * Obtener CSS completo para productos
     */
    private function get_complete_product_css() {
        $settings = $this->get_appearance_settings();
        $primary_rgb = $this->hex_to_rgb($settings['primary_color']);
        
        return '
        /* CSS DIRECTO EN PRODUCTOS - WooCommerce Venezuela Pro */
        
        /* RESET COMPLETO */
        .wvp-product-price-container,
        .wvp-price-usd,
        .wvp-price-ves,
        .wvp-currency-switcher,
        .wvp-currency-switcher button,
        .wvp-currency-switcher .wvp-currency-option,
        .wvp-add-to-cart,
        .wvp-product-container,
        .wvp-product-title,
        .wvp-product-category,
        .wvp-product-actions,
        .wvp-price-conversion,
        .wvp-rate-info {
            all: unset !important;
            display: revert !important;
            box-sizing: border-box !important;
        }
        
        /* CONTENEDOR DE PRECIOS */
        .wvp-product-price-container {
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            margin: ' . $this->get_spacings()[$settings['margin']] . ' !important;
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            display: block !important;
        }
        
        /* PRECIOS USD */
        .wvp-price-usd {
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: ' . $this->get_font_sizes()[$settings['font_size']] . ' !important;
            font-weight: ' . $settings['font_weight'] . ' !important;
            text-transform: ' . $settings['text_transform'] . ' !important;
            color: #333333 !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
            display: block !important;
            overflow: visible !important;
            white-space: normal !important;
            height: auto !important;
        }
        
        /* PRECIOS VES */
        .wvp-price-ves {
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: ' . $this->get_font_sizes()[$settings['font_size']] . ' !important;
            font-weight: ' . $settings['font_weight'] . ' !important;
            text-transform: ' . $settings['text_transform'] . ' !important;
            color: ' . $settings['secondary_color'] . ' !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
            display: block !important;
            overflow: visible !important;
            white-space: normal !important;
            height: auto !important;
        }
        
        /* SWITCHER DE MONEDA */
        .wvp-currency-switcher {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin: 8px 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        
        /* BOTONES DE MONEDA */
        .wvp-currency-switcher button,
        .wvp-currency-switcher .wvp-currency-option {
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
            cursor: pointer !important;
            display: inline-block !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
        }
        
        /* BOTONES DE MONEDA ACTIVOS */
        .wvp-currency-switcher button.active,
        .wvp-currency-switcher .wvp-currency-option.active {
            background-color: ' . $settings['primary_color'] . ' !important;
            border-color: ' . $settings['primary_color'] . ' !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        /* BOTONES DE MONEDA HOVER */
        .wvp-currency-switcher button:hover,
        .wvp-currency-switcher .wvp-currency-option:hover {
            border-color: ' . $settings['primary_color'] . ' !important;
            color: ' . $settings['primary_color'] . ' !important;
        }
        
        /* BOTÓN DE CARRITO */
        .wvp-add-to-cart {
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
            padding: 0 !important;
            margin: 0 !important;
            font-size: 16px !important;
        }
        
        /* BOTÓN DE CARRITO HOVER */
        .wvp-add-to-cart:hover {
            background-color: ' . $settings['secondary_color'] . ' !important;
            transform: scale(1.05) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        }
        
        /* CONTENEDOR DE PRODUCTO */
        .wvp-product-container {
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            padding: 15px !important;
            margin: 10px !important;
            background: #ffffff !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
            display: block !important;
            box-sizing: border-box !important;
        }
        
        /* TÍTULO DE PRODUCTO */
        .wvp-product-title {
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
            display: block !important;
        }
        
        /* CATEGORÍA DE PRODUCTO */
        .wvp-product-category {
            color: #999999 !important;
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            margin: 5px 0 0 0 !important;
            padding: 0 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            display: block !important;
        }
        
        /* ACCIONES DE PRODUCTO */
        .wvp-product-actions {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin: 10px 0 !important;
            color: #999999 !important;
            font-size: 12px !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
        }
        
        .wvp-product-actions a {
            color: #999999 !important;
            text-decoration: none !important;
            transition: color 0.3s ease !important;
            display: inline !important;
        }
        
        .wvp-product-actions a:hover {
            color: ' . $settings['primary_color'] . ' !important;
        }
        
        /* CONVERSIÓN DE PRECIOS */
        .wvp-price-conversion {
            background-color: rgba(' . ($primary_rgb ? $primary_rgb['r'] . ', ' . $primary_rgb['g'] . ', ' . $primary_rgb['b'] : '0, 124, 186') . ', 0.1) !important;
            border: 1px solid ' . $settings['primary_color'] . ' !important;
            border-radius: ' . $this->get_spacings()[$settings['border_radius']] . ' !important;
            padding: ' . $this->get_spacings()[$settings['padding']] . ' !important;
            margin: ' . $this->get_spacings()[$settings['margin']] . ' !important;
            box-shadow: ' . $this->get_shadows()[$settings['shadow']] . ' !important;
            color: ' . $settings['secondary_color'] . ' !important;
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            transition: all 0.3s ease !important;
            display: block !important;
        }
        
        /* INFORMACIÓN DE TASA */
        .wvp-rate-info {
            color: ' . $settings['secondary_color'] . ' !important;
            font-family: ' . $this->get_font_families()[$settings['font_family']] . ' !important;
            font-size: calc(' . $this->get_font_sizes()[$settings['font_size']] . ' * 0.8) !important;
            transition: all 0.3s ease !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        ';
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

// Inicializar el sistema CSS directo en productos
WVP_Direct_Product_CSS::get_instance();
?>
