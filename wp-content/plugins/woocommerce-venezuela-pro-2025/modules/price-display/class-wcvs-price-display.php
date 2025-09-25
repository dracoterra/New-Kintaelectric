<?php
/**
 * Módulo de Visualización de Precios - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar la visualización avanzada de precios
 */
class WCVS_Price_Display {

    /**
     * Instancia del plugin
     *
     * @var WCVS_Core
     */
    private $plugin;

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Estilos disponibles
     *
     * @var array
     */
    private $available_styles = array(
        'minimalist' => 'Minimalista',
        'modern' => 'Moderno',
        'elegant' => 'Elegante',
        'compact' => 'Compacto'
    );

    /**
     * Contextos disponibles
     *
     * @var array
     */
    private $available_contexts = array(
        'single_product' => 'Página de Producto',
        'shop_loop' => 'Lista de Productos',
        'cart' => 'Carrito',
        'checkout' => 'Checkout',
        'widget' => 'Widgets',
        'footer' => 'Pie de Página'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WCVS_Core::get_instance();
        $this->settings = $this->plugin->get_settings()->get('price_display', array());
        
        $this->init_hooks();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Registrar hooks de visualización
        $this->register_display_hooks();

        // Registrar shortcodes
        $this->register_shortcodes();

        // Cargar estilos CSS
        $this->enqueue_styles();

        // Cargar scripts JavaScript
        $this->enqueue_scripts();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_CURRENCY, 'Módulo Price Display inicializado');
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
    }

    /**
     * Registrar hooks de visualización
     */
    private function register_display_hooks() {
        $contexts = $this->settings['contexts'] ?? array();

        // Hook para página de producto
        if ($contexts['single_product'] ?? true) {
            add_action('woocommerce_single_product_summary', array($this, 'display_price_single_product'), 25);
        }

        // Hook para lista de productos
        if ($contexts['shop_loop'] ?? true) {
            add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_price_shop_loop'), 15);
        }

        // Hook para carrito
        if ($contexts['cart'] ?? true) {
            add_action('woocommerce_cart_item_price', array($this, 'display_price_cart'), 10, 3);
            add_action('woocommerce_cart_item_subtotal', array($this, 'display_price_cart'), 10, 3);
        }

        // Hook para checkout
        if ($contexts['checkout'] ?? true) {
            add_action('woocommerce_review_order_after_order_total', array($this, 'display_price_checkout'));
        }

        // Hook para widgets
        if ($contexts['widget'] ?? true) {
            add_action('woocommerce_widget_product_item_end', array($this, 'display_price_widget'), 10, 2);
        }

        // Hook para pie de página
        if ($contexts['footer'] ?? false) {
            add_action('wp_footer', array($this, 'display_price_footer'));
        }
    }

    /**
     * Registrar shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('wcvs_price_display', array($this, 'shortcode_price_display'));
        add_shortcode('wcvs_price_switcher', array($this, 'shortcode_price_switcher'));
        add_shortcode('wcvs_currency_badge', array($this, 'shortcode_currency_badge'));
    }

    /**
     * Cargar estilos CSS
     */
    private function enqueue_styles() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_price_display_styles'));
    }

    /**
     * Cargar scripts JavaScript
     */
    private function enqueue_scripts() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_price_display_scripts'));
    }

    /**
     * Encolar estilos de visualización de precios
     */
    public function enqueue_price_display_styles() {
        $style = $this->settings['style'] ?? 'minimalist';
        
        wp_enqueue_style(
            'wcvs-price-display',
            WCVS_PLUGIN_URL . 'modules/price-display/css/wcvs-' . $style . '-style.css',
            array(),
            WCVS_VERSION
        );

        // Cargar estilos adicionales si hay animaciones
        if ($this->settings['animation_enabled'] ?? true) {
            wp_enqueue_style(
                'wcvs-price-animations',
                WCVS_PLUGIN_URL . 'modules/price-display/css/wcvs-animations.css',
                array('wcvs-price-display'),
                WCVS_VERSION
            );
        }
    }

    /**
     * Encolar scripts de visualización de precios
     */
    public function enqueue_price_display_scripts() {
        wp_enqueue_script(
            'wcvs-price-display',
            WCVS_PLUGIN_URL . 'modules/price-display/js/wcvs-price-display.js',
            array('jquery'),
            WCVS_VERSION,
            true
        );

        // Localizar script
        wp_localize_script('wcvs-price-display', 'wcvs_price_display', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcvs_price_display_nonce'),
            'loading_text' => $this->settings['loading_text'] ?? 'Calculando...',
            'error_text' => $this->settings['error_text'] ?? 'Error al obtener precio',
            'animation_enabled' => $this->settings['animation_enabled'] ?? true,
            'current_currency' => 'USD',
            'target_currency' => 'VES'
        ));
    }

    /**
     * Mostrar precio en página de producto
     */
    public function display_price_single_product() {
        global $product;

        if (!$product) {
            return;
        }

        $this->render_price_display($product, 'single_product');
    }

    /**
     * Mostrar precio en lista de productos
     */
    public function display_price_shop_loop() {
        global $product;

        if (!$product) {
            return;
        }

        $this->render_price_display($product, 'shop_loop');
    }

    /**
     * Mostrar precio en carrito
     *
     * @param string $price_html HTML del precio
     * @param array $cart_item Item del carrito
     * @param string $cart_item_key Clave del item
     * @return string
     */
    public function display_price_cart($price_html, $cart_item, $cart_item_key) {
        $product = $cart_item['data'];
        return $this->render_price_display($product, 'cart', true);
    }

    /**
     * Mostrar precio en checkout
     */
    public function display_price_checkout() {
        $cart = WC()->cart;
        $total = $cart->get_total('raw');
        
        echo '<div class="wcvs-checkout-price-display">';
        $this->render_price_display(null, 'checkout', false, $total);
        echo '</div>';
    }

    /**
     * Mostrar precio en widget
     *
     * @param string $content Contenido del widget
     * @param object $product Producto
     * @return string
     */
    public function display_price_widget($content, $product) {
        return $this->render_price_display($product, 'widget', true);
    }

    /**
     * Mostrar precio en pie de página
     */
    public function display_price_footer() {
        echo '<div class="wcvs-footer-price-display">';
        echo '<div class="wcvs-footer-rate">';
        echo $this->get_conversion_rate_display();
        echo '</div>';
        echo '</div>';
    }

    /**
     * Renderizar visualización de precio
     *
     * @param object $product Producto
     * @param string $context Contexto
     * @param bool $return Si debe retornar HTML
     * @param float $custom_price Precio personalizado
     * @return string|void
     */
    private function render_price_display($product, $context, $return = false, $custom_price = null) {
        $style = $this->settings['style'] ?? 'minimalist';
        $show_both = $this->settings['show_both_currencies'] ?? true;
        $switcher_enabled = $this->settings['currency_switcher_enabled'] ?? true;

        // Obtener precios
        if ($custom_price !== null) {
            $usd_price = $custom_price;
        } else {
            $usd_price = $product ? $product->get_price() : 0;
        }

        $ves_price = $this->convert_price($usd_price);

        if (!$usd_price || !$ves_price) {
            return $return ? '' : null;
        }

        // Generar HTML según el estilo
        $html = $this->generate_price_html($usd_price, $ves_price, $style, $context, $show_both, $switcher_enabled);

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Generar HTML del precio según el estilo
     *
     * @param float $usd_price Precio en USD
     * @param float $ves_price Precio en VES
     * @param string $style Estilo de visualización
     * @param string $context Contexto
     * @param bool $show_both Mostrar ambas monedas
     * @param bool $switcher_enabled Selector habilitado
     * @return string
     */
    private function generate_price_html($usd_price, $ves_price, $style, $context, $show_both, $switcher_enabled) {
        $html = '<div class="wcvs-price-display wcvs-style-' . esc_attr($style) . ' wcvs-context-' . esc_attr($context) . '">';

        switch ($style) {
            case 'minimalist':
                $html .= $this->generate_minimalist_html($usd_price, $ves_price, $show_both, $switcher_enabled);
                break;
            case 'modern':
                $html .= $this->generate_modern_html($usd_price, $ves_price, $show_both, $switcher_enabled);
                break;
            case 'elegant':
                $html .= $this->generate_elegant_html($usd_price, $ves_price, $show_both, $switcher_enabled);
                break;
            case 'compact':
                $html .= $this->generate_compact_html($usd_price, $ves_price, $show_both, $switcher_enabled);
                break;
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Generar HTML estilo minimalista
     *
     * @param float $usd_price Precio en USD
     * @param float $ves_price Precio en VES
     * @param bool $show_both Mostrar ambas monedas
     * @param bool $switcher_enabled Selector habilitado
     * @return string
     */
    private function generate_minimalist_html($usd_price, $ves_price, $show_both, $switcher_enabled) {
        $html = '';

        if ($show_both) {
            $html .= '<div class="wcvs-price-container">';
            $html .= '<span class="wcvs-price-usd">$' . number_format($usd_price, 2, '.', ',') . '</span>';
            $html .= '<span class="wcvs-price-separator">|</span>';
            $html .= '<span class="wcvs-price-ves">' . number_format($ves_price, 2, ',', '.') . ' Bs.</span>';
            $html .= '</div>';
        } else {
            $html .= '<div class="wcvs-price-container">';
            $html .= '<span class="wcvs-price-usd">$' . number_format($usd_price, 2, '.', ',') . '</span>';
            $html .= '</div>';
        }

        if ($switcher_enabled) {
            $html .= $this->generate_currency_switcher('minimalist');
        }

        return $html;
    }

    /**
     * Generar HTML estilo moderno
     *
     * @param float $usd_price Precio en USD
     * @param float $ves_price Precio en VES
     * @param bool $show_both Mostrar ambas monedas
     * @param bool $switcher_enabled Selector habilitado
     * @return string
     */
    private function generate_modern_html($usd_price, $ves_price, $show_both, $switcher_enabled) {
        $html = '';

        if ($show_both) {
            $html .= '<div class="wcvs-price-container wcvs-modern-container">';
            $html .= '<div class="wcvs-price-card wcvs-price-usd-card">';
            $html .= '<div class="wcvs-price-label">USD</div>';
            $html .= '<div class="wcvs-price-value">$' . number_format($usd_price, 2, '.', ',') . '</div>';
            $html .= '</div>';
            $html .= '<div class="wcvs-price-card wcvs-price-ves-card">';
            $html .= '<div class="wcvs-price-label">VES</div>';
            $html .= '<div class="wcvs-price-value">' . number_format($ves_price, 2, ',', '.') . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="wcvs-price-container wcvs-modern-container">';
            $html .= '<div class="wcvs-price-card wcvs-price-usd-card">';
            $html .= '<div class="wcvs-price-label">USD</div>';
            $html .= '<div class="wcvs-price-value">$' . number_format($usd_price, 2, '.', ',') . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        if ($switcher_enabled) {
            $html .= $this->generate_currency_switcher('modern');
        }

        return $html;
    }

    /**
     * Generar HTML estilo elegante
     *
     * @param float $usd_price Precio en USD
     * @param float $ves_price Precio en VES
     * @param bool $show_both Mostrar ambas monedas
     * @param bool $switcher_enabled Selector habilitado
     * @return string
     */
    private function generate_elegant_html($usd_price, $ves_price, $show_both, $switcher_enabled) {
        $html = '';

        if ($show_both) {
            $html .= '<div class="wcvs-price-container wcvs-elegant-container">';
            $html .= '<div class="wcvs-price-elegant">';
            $html .= '<div class="wcvs-price-main">';
            $html .= '<span class="wcvs-price-symbol">$</span>';
            $html .= '<span class="wcvs-price-amount">' . number_format($usd_price, 2, '.', ',') . '</span>';
            $html .= '<span class="wcvs-price-currency">USD</span>';
            $html .= '</div>';
            $html .= '<div class="wcvs-price-secondary">';
            $html .= '<span class="wcvs-price-amount">' . number_format($ves_price, 2, ',', '.') . '</span>';
            $html .= '<span class="wcvs-price-currency">Bs.</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="wcvs-price-container wcvs-elegant-container">';
            $html .= '<div class="wcvs-price-elegant">';
            $html .= '<div class="wcvs-price-main">';
            $html .= '<span class="wcvs-price-symbol">$</span>';
            $html .= '<span class="wcvs-price-amount">' . number_format($usd_price, 2, '.', ',') . '</span>';
            $html .= '<span class="wcvs-price-currency">USD</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        if ($switcher_enabled) {
            $html .= $this->generate_currency_switcher('elegant');
        }

        return $html;
    }

    /**
     * Generar HTML estilo compacto
     *
     * @param float $usd_price Precio en USD
     * @param float $ves_price Precio en VES
     * @param bool $show_both Mostrar ambas monedas
     * @param bool $switcher_enabled Selector habilitado
     * @return string
     */
    private function generate_compact_html($usd_price, $ves_price, $show_both, $switcher_enabled) {
        $html = '';

        if ($show_both) {
            $html .= '<div class="wcvs-price-container wcvs-compact-container">';
            $html .= '<span class="wcvs-price-compact">';
            $html .= '<span class="wcvs-price-usd">$' . number_format($usd_price, 2, '.', ',') . '</span>';
            $html .= '<span class="wcvs-price-divider">/</span>';
            $html .= '<span class="wcvs-price-ves">' . number_format($ves_price, 0, ',', '.') . ' Bs.</span>';
            $html .= '</span>';
            $html .= '</div>';
        } else {
            $html .= '<div class="wcvs-price-container wcvs-compact-container">';
            $html .= '<span class="wcvs-price-compact">';
            $html .= '<span class="wcvs-price-usd">$' . number_format($usd_price, 2, '.', ',') . '</span>';
            $html .= '</span>';
            $html .= '</div>';
        }

        if ($switcher_enabled) {
            $html .= $this->generate_currency_switcher('compact');
        }

        return $html;
    }

    /**
     * Generar selector de moneda
     *
     * @param string $style Estilo del selector
     * @return string
     */
    private function generate_currency_switcher($style) {
        $switcher_style = $this->settings['switcher_style'] ?? 'buttons';
        
        $html = '<div class="wcvs-currency-switcher wcvs-switcher-' . esc_attr($switcher_style) . ' wcvs-style-' . esc_attr($style) . '">';
        
        switch ($switcher_style) {
            case 'buttons':
                $html .= '<button class="wcvs-currency-btn wcvs-btn-usd active" data-currency="USD">USD</button>';
                $html .= '<button class="wcvs-currency-btn wcvs-btn-ves" data-currency="VES">VES</button>';
                break;
            case 'dropdown':
                $html .= '<select class="wcvs-currency-select">';
                $html .= '<option value="USD">USD - Dólares</option>';
                $html .= '<option value="VES">VES - Bolívares</option>';
                $html .= '</select>';
                break;
            case 'toggle':
                $html .= '<label class="wcvs-toggle-label">';
                $html .= '<input type="checkbox" class="wcvs-toggle-input" data-currency="VES">';
                $html .= '<span class="wcvs-toggle-slider"></span>';
                $html .= '<span class="wcvs-toggle-text">Mostrar en Bolívares</span>';
                $html .= '</label>';
                break;
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Convertir precio usando el módulo de moneda
     *
     * @param float $price Precio a convertir
     * @return float
     */
    private function convert_price($price) {
        $currency_manager = $this->plugin->get_module_manager()->get_module_instance('currency_manager');
        if ($currency_manager) {
            return $currency_manager->convert_price($price);
        }
        return $price;
    }

    /**
     * Obtener visualización de tasa de conversión
     *
     * @return string
     */
    private function get_conversion_rate_display() {
        $rate = $this->get_conversion_rate();
        if (!$rate) {
            return '';
        }

        return '<span class="wcvs-rate-display">Tasa BCV: ' . number_format($rate, 4, ',', '.') . ' Bs./USD</span>';
    }

    /**
     * Obtener tasa de conversión
     *
     * @return float|false
     */
    private function get_conversion_rate() {
        $currency_manager = $this->plugin->get_module_manager()->get_module_instance('currency_manager');
        if ($currency_manager) {
            return $currency_manager->get_conversion_rate();
        }
        return false;
    }

    /**
     * Shortcode para mostrar precio
     *
     * @param array $atts Atributos del shortcode
     * @return string
     */
    public function shortcode_price_display($atts) {
        $atts = shortcode_atts(array(
            'price' => '',
            'style' => 'minimalist',
            'context' => 'widget',
            'show_both' => 'true',
            'switcher' => 'true',
            'class' => ''
        ), $atts);

        if (empty($atts['price']) || !is_numeric($atts['price'])) {
            return '';
        }

        $price = floatval($atts['price']);
        $converted_price = $this->convert_price($price);

        if (!$converted_price) {
            return '';
        }

        // Temporalmente cambiar configuración para el shortcode
        $original_style = $this->settings['style'];
        $this->settings['style'] = $atts['style'];
        $this->settings['show_both_currencies'] = $atts['show_both'] === 'true';
        $this->settings['currency_switcher_enabled'] = $atts['switcher'] === 'true';

        $html = $this->render_price_display(null, $atts['context'], true, $price);

        // Restaurar configuración original
        $this->settings['style'] = $original_style;

        if (!empty($atts['class'])) {
            $html = str_replace('wcvs-price-display', 'wcvs-price-display ' . esc_attr($atts['class']), $html);
        }

        return $html;
    }

    /**
     * Shortcode para selector de precio
     *
     * @param array $atts Atributos del shortcode
     * @return string
     */
    public function shortcode_price_switcher($atts) {
        $atts = shortcode_atts(array(
            'style' => 'buttons',
            'class' => ''
        ), $atts);

        $html = '<div class="wcvs-price-switcher ' . esc_attr($atts['class']) . '">';
        $html .= $this->generate_currency_switcher($atts['style']);
        $html .= '</div>';

        return $html;
    }

    /**
     * Shortcode para badge de moneda
     *
     * @param array $atts Atributos del shortcode
     * @return string
     */
    public function shortcode_currency_badge($atts) {
        $atts = shortcode_atts(array(
            'currency' => 'VES',
            'class' => ''
        ), $atts);

        $html = '<span class="wcvs-currency-badge wcvs-badge-' . esc_attr(strtolower($atts['currency'])) . ' ' . esc_attr($atts['class']) . '">';
        $html .= esc_html($atts['currency']);
        $html .= '</span>';

        return $html;
    }

    /**
     * Manejar activación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'price_display') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_CURRENCY, 'Módulo Price Display activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'price_display') {
            // Limpiar hooks
            remove_action('woocommerce_single_product_summary', array($this, 'display_price_single_product'));
            remove_action('woocommerce_after_shop_loop_item_title', array($this, 'display_price_shop_loop'));
            
            WCVS_Logger::info(WCVS_Logger::CONTEXT_CURRENCY, 'Módulo Price Display desactivado');
        }
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        return array(
            'current_style' => $this->settings['style'] ?? 'minimalist',
            'available_styles' => $this->available_styles,
            'contexts_enabled' => $this->settings['contexts'] ?? array(),
            'show_both_currencies' => $this->settings['show_both_currencies'] ?? true,
            'currency_switcher_enabled' => $this->settings['currency_switcher_enabled'] ?? true,
            'animation_enabled' => $this->settings['animation_enabled'] ?? true
        );
    }
}
