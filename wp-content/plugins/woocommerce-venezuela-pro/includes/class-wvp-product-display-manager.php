<?php
/**
 * Gestor de Visualización de Productos - WooCommerce Venezuela Pro
 * Maneja diferentes estilos de visualización de precios y conversiones
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Product_Display_Manager {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Estilo actual de visualización
     * 
     * @var string
     */
    private $current_style;
    
    /**
     * Tema detectado para compatibilidad
     * 
     * @var string
     */
    private $theme_compatibility;
    
    /**
     * Estilos disponibles
     * 
     * @var array
     */
    private $available_styles = array(
        'minimal' => 'Minimalista',
        'modern' => 'Moderno',
        'elegant' => 'Elegante',
        'compact' => 'Compacto'
    );
    
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
        
        // Obtener configuración
        $this->current_style = get_option('wvp_display_style', 'minimal');
        $this->theme_compatibility = $this->detect_theme();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Encolar estilos y scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Modificar visualización de precios
        add_filter('woocommerce_get_price_html', array($this, 'modify_price_display'), 10, 2);
        
        // Añadir clases CSS al body
        add_filter('body_class', array($this, 'add_body_classes'));
        
        // Shortcodes
        add_shortcode('wvp_price_switcher', array($this, 'shortcode_price_switcher'));
        add_shortcode('wvp_price_display', array($this, 'shortcode_price_display'));
        add_shortcode('wvp_currency_badge', array($this, 'shortcode_currency_badge'));
        
        // AJAX para cambio de moneda
        add_action('wp_ajax_wvp_switch_currency', array($this, 'ajax_switch_currency'));
        add_action('wp_ajax_nopriv_wvp_switch_currency', array($this, 'ajax_switch_currency'));
    }
    
    /**
     * Encolar estilos y scripts
     */
    public function enqueue_assets() {
        // Solo en frontend
        if (is_admin()) {
            return;
        }
        
        // CSS base
        wp_enqueue_style(
            'wvp-product-display-base',
            WVP_PLUGIN_URL . 'assets/css/wvp-product-display-base.css',
            array(),
            WVP_VERSION
        );
        
        // CSS del estilo seleccionado
        if (isset($this->available_styles[$this->current_style])) {
            wp_enqueue_style(
                'wvp-style-' . $this->current_style,
                WVP_PLUGIN_URL . 'assets/css/styles/wvp-' . $this->current_style . '-style.css',
                array('wvp-product-display-base'),
                WVP_VERSION
            );
        }
        
        // CSS de compatibilidad con tema
        if ($this->theme_compatibility) {
            wp_enqueue_style(
                'wvp-theme-' . $this->theme_compatibility,
                WVP_PLUGIN_URL . 'assets/css/themes/wvp-' . $this->theme_compatibility . '-compat.css',
                array('wvp-product-display-base'),
                WVP_VERSION
            );
        }
        
        // JavaScript
        wp_enqueue_script(
            'wvp-product-display',
            WVP_PLUGIN_URL . 'assets/js/wvp-product-display.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        // Localizar script
        wp_localize_script('wvp-product-display', 'wvp_product_display', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_product_display_nonce'),
            'current_style' => $this->current_style,
            'strings' => array(
                'loading' => __('Cargando...', 'wvp'),
                'error' => __('Error al cargar precios', 'wvp'),
                'usd' => __('USD', 'wvp'),
                'ves' => __('VES', 'wvp')
            )
        ));
    }
    
    /**
     * Modificar visualización de precios
     */
    public function modify_price_display($price_html, $product) {
        // Solo en frontend
        if (is_admin()) {
            return $price_html;
        }
        
        // Solo en páginas de productos y tienda
        if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag()) {
            return $price_html;
        }
        
        // Obtener precio del producto
        $price = $product->get_price();
        if (!$price || $price <= 0) {
            return $price_html;
        }
        
        // Obtener tasa BCV
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate || $rate <= 0) {
            return $price_html;
        }
        
        // Generar HTML del display
        return $this->generate_price_display_html($price_html, $price, $rate, $product);
    }
    
    /**
     * Generar HTML del display de precios
     */
    private function generate_price_display_html($price_html, $price, $rate, $product) {
        // Calcular precio en VES
        $ves_price = $price * $rate;
        
        // Formatear precios
        $formatted_usd = wc_price($price);
        $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_price) . ' Bs.';
        
        // Generar HTML según el estilo
        $html = $this->generate_style_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate);
        
        return $html;
    }
    
    /**
     * Generar HTML según el estilo seleccionado
     */
    private function generate_style_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate) {
        $style_class = 'wvp-' . $this->current_style;
        
        switch ($this->current_style) {
            case 'minimal':
                return $this->generate_minimal_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class);
                
            case 'modern':
                return $this->generate_modern_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class);
                
            case 'elegant':
                return $this->generate_elegant_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class);
                
            case 'compact':
                return $this->generate_compact_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class);
                
            default:
                return $this->generate_minimal_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class);
        }
    }
    
    /**
     * Generar HTML estilo minimalista
     */
    private function generate_minimal_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class) {
        $context = $this->get_current_context();
        
        $html = '<div class="wvp-product-price-container ' . esc_attr($style_class) . '">';
        $html .= '<div class="wvp-price-display">';
        $html .= '<span class="wvp-price-usd" style="display: block;">' . $formatted_usd . '</span>';
        $html .= '<span class="wvp-price-ves" style="display: none;">' . $formatted_ves . '</span>';
        $html .= '</div>';
        
        // Solo mostrar selector si está habilitado para este contexto
        if (apply_filters('wvp_show_currency_switcher', true, $context)) {
            $html .= '<div class="wvp-currency-switcher wvp-scope-global" data-price-usd="' . esc_attr($price) . '" data-price-ves="' . esc_attr($ves_price) . '">';
            $html .= '<button class="wvp-currency-option active" data-currency="usd">USD</button>';
            $html .= '<button class="wvp-currency-option" data-currency="ves">VES</button>';
            $html .= '</div>';
        }
        
        // Solo mostrar conversión si está habilitada para este contexto
        if (apply_filters('wvp_show_currency_conversion', true, $context)) {
            $html .= '<div class="wvp-price-conversion">';
            $html .= '<span class="wvp-ves-reference">Equivale a ' . $formatted_ves . '</span>';
            $html .= '</div>';
        }
        
        // Solo mostrar tasa BCV si está habilitada para este contexto
        if (apply_filters('wvp_show_bcv_rate', false, $context)) {
            $html .= '<div class="wvp-rate-info">Tasa BCV: ' . number_format($rate, 2, ',', '.') . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generar HTML estilo moderno
     */
    private function generate_modern_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class) {
        return sprintf(
            '<div class="wvp-product-price-container %s">
                <div class="wvp-price-display">
                    <span class="wvp-price-usd" style="display: block;">%s</span>
                    <span class="wvp-price-ves" style="display: none;">%s</span>
                </div>
                <div class="wvp-currency-switcher" data-price-usd="%s" data-price-ves="%s">
                    <button class="wvp-currency-option active" data-currency="usd">
                        <span>USD</span>
                    </button>
                    <button class="wvp-currency-option" data-currency="ves">
                        <span>VES</span>
                    </button>
                </div>
                <div class="wvp-price-conversion">
                    <span class="wvp-ves-reference">Equivale a %s</span>
                </div>
                <div class="wvp-rate-info">Tasa BCV: %s</div>
            </div>',
            esc_attr($style_class),
            $formatted_usd,
            $formatted_ves,
            esc_attr($price),
            esc_attr($ves_price),
            $formatted_ves,
            number_format($rate, 2, ',', '.')
        );
    }
    
    /**
     * Generar HTML estilo elegante
     */
    private function generate_elegant_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class) {
        return sprintf(
            '<div class="wvp-product-price-container %s">
                <div class="wvp-price-display">
                    <span class="wvp-price-usd" style="display: block;">%s</span>
                    <span class="wvp-price-ves" style="display: none;">%s</span>
                </div>
                <div class="wvp-currency-switcher" data-price-usd="%s" data-price-ves="%s">
                    <button class="wvp-currency-option active" data-currency="usd">
                        <span>USD</span>
                    </button>
                    <button class="wvp-currency-option" data-currency="ves">
                        <span>VES</span>
                    </button>
                </div>
                <div class="wvp-price-conversion">
                    <span class="wvp-ves-reference">Equivale a %s</span>
                </div>
                <div class="wvp-rate-info">Tasa BCV: %s</div>
            </div>',
            esc_attr($style_class),
            $formatted_usd,
            $formatted_ves,
            esc_attr($price),
            esc_attr($ves_price),
            $formatted_ves,
            number_format($rate, 2, ',', '.')
        );
    }
    
    /**
     * Generar HTML estilo compacto
     */
    private function generate_compact_html($price_html, $formatted_usd, $formatted_ves, $price, $ves_price, $rate, $style_class) {
        return sprintf(
            '<div class="wvp-product-price-container %s">
                <div class="wvp-price-layout">
                    <div class="wvp-price-display">
                        <span class="wvp-price-usd">%s</span>
                        <span class="wvp-price-ves" style="display: none;">%s</span>
                    </div>
                    <div class="wvp-currency-switcher" data-price-usd="%s" data-price-ves="%s">
                        <button class="wvp-currency-option active" data-currency="usd">USD</button>
                        <button class="wvp-currency-option" data-currency="ves">VES</button>
                    </div>
                </div>
                <div class="wvp-price-conversion">
                    <span class="wvp-ves-reference">%s</span>
                </div>
            </div>',
            esc_attr($style_class),
            $formatted_usd,
            $formatted_ves,
            esc_attr($price),
            esc_attr($ves_price),
            $formatted_ves
        );
    }
    
    /**
     * Detectar tema para compatibilidad
     */
    private function detect_theme() {
        $theme = wp_get_theme();
        $theme_name = strtolower($theme->get('Name'));
        
        // Temas populares
        $compatible_themes = array(
            'astra' => 'astra',
            'oceanwp' => 'oceanwp',
            'storefront' => 'storefront',
            'generatepress' => 'generatepress',
            'kadence' => 'kadence',
            'neve' => 'neve'
        );
        
        foreach ($compatible_themes as $key => $value) {
            if (strpos($theme_name, $key) !== false) {
                return $value;
            }
        }
        
        return false;
    }
    
    /**
     * Añadir clases CSS al body
     */
    public function add_body_classes($classes) {
        $classes[] = 'wvp-display-style-' . $this->current_style;
        
        if ($this->theme_compatibility) {
            $classes[] = 'wvp-theme-' . $this->theme_compatibility;
        }
        
        return $classes;
    }
    
    /**
     * Shortcode para switcher de precios
     */
    public function shortcode_price_switcher($atts) {
        $atts = shortcode_atts(array(
            'style' => $this->current_style,
            'price' => '',
            'product_id' => ''
        ), $atts);
        
        // Obtener producto
        if ($atts['product_id']) {
            $product = wc_get_product($atts['product_id']);
        } else {
            global $product;
        }
        
        if (!$product) {
            return '';
        }
        
        $price = $product->get_price();
        if (!$price) {
            return '';
        }
        
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate) {
            return '';
        }
        
        $ves_price = $price * $rate;
        $formatted_usd = wc_price($price);
        $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_price) . ' Bs.';
        
        return $this->generate_style_html(
            $formatted_usd,
            $formatted_usd,
            $formatted_ves,
            $price,
            $ves_price,
            $rate
        );
    }
    
    /**
     * Shortcode para display de precios
     */
    public function shortcode_price_display($atts) {
        $atts = shortcode_atts(array(
            'style' => $this->current_style,
            'show_switcher' => 'true',
            'show_conversion' => 'true'
        ), $atts);
        
        // Implementar lógica del shortcode
        return $this->shortcode_price_switcher($atts);
    }
    
    /**
     * Shortcode para badge de moneda
     */
    public function shortcode_currency_badge($atts) {
        $atts = shortcode_atts(array(
            'currency' => 'usd',
            'style' => $this->current_style
        ), $atts);
        
        $currency = strtoupper($atts['currency']);
        $style_class = 'wvp-' . $atts['style'];
        
        return sprintf(
            '<span class="wvp-currency-badge %s wvp-%s">%s</span>',
            esc_attr($style_class),
            esc_attr($atts['currency']),
            esc_html($currency)
        );
    }
    
    /**
     * AJAX para cambio de moneda
     */
    public function ajax_switch_currency() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_product_display_nonce')) {
            wp_die('Security check failed');
        }
        
        $currency = sanitize_text_field($_POST['currency']);
        $price_usd = floatval($_POST['price_usd']);
        $price_ves = floatval($_POST['price_ves']);
        
        // Guardar preferencia del usuario
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'wvp_preferred_currency', $currency);
        } else {
            setcookie('wvp_preferred_currency', $currency, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
        }
        
        // Devolver respuesta
        wp_send_json_success(array(
            'currency' => $currency,
            'price' => $currency === 'usd' ? wc_price($price_usd) : WVP_BCV_Integrator::format_ves_price($price_ves) . ' Bs.'
        ));
    }
    
    /**
     * Obtener estilo actual
     */
    public function get_current_style() {
        return $this->current_style;
    }
    
    /**
     * Obtener estilos disponibles
     */
    public function get_available_styles() {
        return $this->available_styles;
    }
    
    /**
     * Cambiar estilo
     */
    public function set_style($style) {
        if (isset($this->available_styles[$style])) {
            $this->current_style = $style;
            update_option('wvp_display_style', $style);
            return true;
        }
        return false;
    }
    
    /**
     * Obtener contexto actual
     */
    private function get_current_context() {
        if (is_product()) {
            return 'single_product';
        } elseif (is_shop() || is_product_category() || is_product_tag()) {
            return 'shop_loop';
        } elseif (is_cart()) {
            return 'cart';
        } elseif (is_checkout()) {
            return 'checkout';
        } elseif (is_active_sidebar('sidebar-1') || is_active_sidebar('shop-sidebar')) {
            return 'widget';
        }
        
        return 'default';
    }
}
