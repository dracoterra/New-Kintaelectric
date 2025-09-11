<?php
/**
 * Clase para mostrar precios en bolívares como referencia
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Price_Display {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Obtener instancia del plugin de forma segura
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Modificar la visualización de precios
        add_filter("woocommerce_get_price_html", array($this, "add_ves_reference"), 10, 2);
        
        // Añadir estilos para los precios
        add_action("wp_enqueue_scripts", array($this, "enqueue_styles"));
        
        // Hook para mostrar precios en páginas de productos
        add_action("woocommerce_single_product_summary", array($this, "display_price_with_conversion"), 10);
        
        // Hook para mostrar precios en listas de productos
        add_action("woocommerce_after_shop_loop_item_title", array($this, "display_price_with_conversion"), 10);
    }
    
    /**
     * Añadir referencia en bolívares a los precios o switcher de moneda
     * 
     * @param string $price_html HTML del precio
     * @param WC_Product $product Producto
     * @return string HTML modificado
     */
    public function add_ves_reference($price_html, $product) {
        // Solo mostrar en frontend
        if (is_admin()) {
            return $price_html;
        }
        
        // Solo procesar en páginas de productos y tienda
        $is_product_page = is_product() || 
                          is_shop() || 
                          is_product_category() || 
                          is_product_tag() ||
                          (is_singular() && get_post_type() === 'product') ||
                          (is_home() && is_front_page() && wc_get_page_id('shop') == get_option('page_on_front'));
        
        if (!$is_product_page) {
            return $price_html;
        }
        
        // SIEMPRE generar el switcher - sin depender de configuraciones
        return $this->generate_currency_switcher($price_html, $product);
    }

    /**
     * Generar switcher de moneda interactivo
     * 
     * @param string $price_html HTML del precio original
     * @param WC_Product $product Producto
     * @return string HTML del switcher
     */
    private function generate_currency_switcher($price_html, $product) {
        // Obtener el precio del producto
        $price = $product->get_price();
        if (empty($price) || $price <= 0) {
            return $price_html;
        }
        
        // Obtener la tasa de cambio
        $rate = WVP_BCV_Integrator::get_rate();
        if ($rate === null || $rate <= 0) {
            return $price_html;
        }
        
        // Calcular el precio en bolívares
        $ves_price = $price * $rate;
        
        // Formatear precios
        $formatted_usd = wc_price($price);
        $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_price) . ' Bs.';
        
        // Extraer solo el valor numérico para los atributos data
        $usd_value = strip_tags($formatted_usd);
        $ves_value = $formatted_ves;
        
        // Generar HTML del switcher que NO interfiera con WooCommerce
        $switcher_html = sprintf(
            '%s
            <div class="wvp-currency-switcher" data-price-usd="%s" data-price-ves="%s">
                <span class="wvp-usd active" data-currency="usd">USD</span> |
                <span class="wvp-ves" data-currency="ves">VES</span>
            </div>',
            $price_html,
            esc_attr($usd_value),
            esc_attr($ves_value)
        );
        
        return $switcher_html;
    }

    /**
     * Añadir referencia estática en bolívares (método original)
     * 
     * @param string $price_html HTML del precio
     * @param WC_Product $product Producto
     * @return string HTML modificado
     */
    private function add_static_ves_reference($price_html, $product) {
        // Obtener tasa de cambio
        $rate = WVP_BCV_Integrator::get_rate();
        if ($rate === null || $rate <= 0) {
            return $price_html;
        }
        
        // Obtener precio del producto
        $price = $product->get_price();
        if (empty($price)) {
            return $price_html;
        }
        
        // Convertir a bolívares
        $ves_price = WVP_BCV_Integrator::convert_to_ves($price, $rate);
        if ($ves_price === null) {
            return $price_html;
        }
        
        // Formatear precio en bolívares
        $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_price);
        
        // Obtener formato de referencia
        $reference_format = $this->plugin ? $this->plugin->get_option("price_reference_format", "(Ref. %s Bs.)") : "(Ref. %s Bs.)";
        
        // Añadir referencia
        $reference = sprintf($reference_format, $formatted_ves);
        
        return $price_html . '<span class="wvp-ves-reference">' . $reference . '</span>';
    }
    
    /**
     * Cargar estilos y scripts para los precios
     */
    public function enqueue_styles() {
        // Cargar CSS
        wp_enqueue_style(
            "wvp-price-display",
            WVP_PLUGIN_URL . "assets/css/price-display.css",
            array(),
            WVP_VERSION
        );
        
        // SIEMPRE cargar JavaScript del switcher
        $script_url = WVP_PLUGIN_URL . "assets/js/currency-switcher.js";
        error_log('WVP DEBUG: Encolando script: ' . $script_url);
        
        // Verificar si el archivo existe
        $script_path = WVP_PLUGIN_PATH . "assets/js/currency-switcher.js";
        if (file_exists($script_path)) {
            error_log('WVP DEBUG: Archivo JavaScript existe: ' . $script_path);
        } else {
            error_log('WVP DEBUG: Archivo JavaScript NO existe: ' . $script_path);
        }
        
        wp_enqueue_script(
            "wvp-currency-switcher",
            $script_url,
            array(),
            WVP_VERSION,
            false
        );
        
        error_log('WVP DEBUG: Script encolado correctamente');
    }
    
    /**
     * Mostrar precio con conversión automática
     */
    public function display_price_with_conversion() {
        global $product;
        
        if (!$product || !is_a($product, 'WC_Product')) {
            return;
        }
        
        $price = $product->get_price();
        if (!$price) {
            return;
        }
        
        // Obtener tasa BCV
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate || $rate <= 0) {
            return;
        }
        
        // Calcular precio en VES
        $ves_price = $price * $rate;
        
        // Mostrar conversión
        echo '<div class="wvp-price-conversion">';
        echo '<small class="wvp-ves-reference">';
        printf(__('Equivale a %s', 'wvp'), $this->format_ves_price($ves_price));
        echo '</small>';
        echo '</div>';
    }
    
    /**
     * Formatear precio en VES
     */
    private function format_ves_price($ves_price) {
        if (!$ves_price) {
            return '';
        }
        
        $formatted = number_format($ves_price, 2, ',', '.');
        return 'Bs. ' . $formatted;
    }
}