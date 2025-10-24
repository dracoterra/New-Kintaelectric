<?php
/**
 * Sistema CSS Básico y Funcional - WooCommerce Venezuela Pro
 * Sistema simple que funciona correctamente
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Basic_CSS {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Constructor privado
     */
    private function __construct() {
        // Solo aplicar en frontend
        if (!is_admin()) {
            add_action('wp_head', array($this, 'add_css'), 999);
        }
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
     * Agregar CSS al head
     */
    public function add_css() {
        $settings = $this->get_appearance_settings();
        
        echo '<style id="wvp-basic-css" type="text/css">
        /* CSS Básico WooCommerce Venezuela Pro */
        
        /* Botones de moneda */
        .wvp-currency-switcher {
            display: flex !important;
            gap: 8px !important;
            margin: 8px 0 !important;
        }
        
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
            cursor: pointer !important;
            transition: all 0.3s ease !important;
        }
        
        .wvp-currency-switcher button.active,
        .wvp-currency-switcher .wvp-currency-option.active {
            background-color: ' . $settings['primary_color'] . ' !important;
            border-color: ' . $settings['primary_color'] . ' !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }
        
        .wvp-currency-switcher button:hover,
        .wvp-currency-switcher .wvp-currency-option:hover {
            border-color: ' . $settings['primary_color'] . ' !important;
            color: ' . $settings['primary_color'] . ' !important;
        }
        
        /* Precios */
        .wvp-price-usd {
            color: #333333 !important;
            font-size: 16px !important;
            font-weight: bold !important;
            margin: 5px 0 !important;
        }
        
        .wvp-price-ves {
            color: ' . $settings['secondary_color'] . ' !important;
            font-size: 16px !important;
            font-weight: bold !important;
            margin: 5px 0 !important;
        }
        
        /* Botón de carrito */
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
        }
        
        .wvp-add-to-cart:hover {
            background-color: ' . $settings['secondary_color'] . ' !important;
            transform: scale(1.05) !important;
        }
        
        /* Contenedor de producto */
        .wvp-product-container {
            overflow: visible !important;
            height: auto !important;
            padding: 15px !important;
            margin: 10px !important;
            background: #ffffff !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
        }
        
        /* Título de producto */
        .wvp-product-title {
            color: #333333 !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            margin: 8px 0 !important;
            line-height: 1.3 !important;
            overflow: visible !important;
            white-space: normal !important;
            height: auto !important;
        }
        
        /* Categoría de producto */
        .wvp-product-category {
            color: #999999 !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            margin: 5px 0 0 0 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }
        
        /* Acciones de producto */
        .wvp-product-actions {
            display: flex !important;
            gap: 10px !important;
            margin: 10px 0 !important;
            color: #999999 !important;
            font-size: 12px !important;
        }
        
        .wvp-product-actions a {
            color: #999999 !important;
            text-decoration: none !important;
            transition: color 0.3s ease !important;
        }
        
        .wvp-product-actions a:hover {
            color: ' . $settings['primary_color'] . ' !important;
        }
        
        /* Conversión de precios */
        .wvp-price-conversion {
            background-color: rgba(0, 124, 186, 0.1) !important;
            border: 1px solid ' . $settings['primary_color'] . ' !important;
            border-radius: 4px !important;
            padding: 8px !important;
            margin: 8px 0 !important;
            color: ' . $settings['secondary_color'] . ' !important;
            font-size: 12px !important;
        }
        
        /* Información de tasa */
        .wvp-rate-info {
            color: ' . $settings['secondary_color'] . ' !important;
            font-size: 11px !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Contenedor de precios */
        .wvp-product-price-container {
            margin: 10px 0 !important;
            overflow: visible !important;
            height: auto !important;
        }
        </style>';
    }
    
    /**
     * Obtener configuraciones de apariencia
     */
    private function get_appearance_settings() {
        return array(
            'primary_color' => get_option('wvp_primary_color', '#007cba'),
            'secondary_color' => get_option('wvp_secondary_color', '#005a87'),
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
}

// Inicializar el sistema CSS básico
WVP_Basic_CSS::get_instance();
?>
