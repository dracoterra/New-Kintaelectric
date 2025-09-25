<?php
/**
 * Módulo de Gestión de Moneda - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar la conversión de moneda
 */
class WCVS_Currency_Manager {

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
     * Cache de conversiones
     *
     * @var array
     */
    private $conversion_cache = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WCVS_Core::get_instance();
        $this->settings = $this->plugin->get_settings()->get('currency', array());
        
        $this->init_hooks();
        $this->load_conversion_cache();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Configurar moneda base de WooCommerce
        $this->setup_woocommerce_currency();

        // Registrar hooks de conversión
        $this->register_conversion_hooks();

        // Registrar hooks de visualización
        $this->register_display_hooks();

        // Registrar shortcodes
        $this->register_shortcodes();

        // Registrar widgets
        $this->register_widgets();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_CURRENCY, 'Módulo Currency Manager inicializado');
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se actualiza la tasa BCV
        add_action('wcvs_bcv_rate_updated', array($this, 'handle_rate_update'), 10, 2);
        
        // Hook para limpieza de cache
        add_action('wcvs_cache_cleanup', array($this, 'cleanup_cache'));
        
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
    }

    /**
     * Configurar moneda base de WooCommerce
     */
    private function setup_woocommerce_currency() {
        // Establecer USD como moneda base si no está configurada
        $current_currency = get_option('woocommerce_currency');
        if (empty($current_currency)) {
            update_option('woocommerce_currency', 'USD');
        }

        // Configurar formato de moneda
        $this->setup_currency_format();
    }

    /**
     * Configurar formato de moneda
     */
    private function setup_currency_format() {
        // Formato para USD
        update_option('woocommerce_currency_pos', 'left');
        update_option('woocommerce_price_thousand_sep', ',');
        update_option('woocommerce_price_decimal_sep', '.');
        update_option('woocommerce_price_num_decimals', 2);

        // Formato para VES (si se necesita)
        $ves_format = array(
            'position' => 'right',
            'thousand_sep' => '.',
            'decimal_sep' => ',',
            'decimals' => 2
        );

        update_option('wcvs_ves_format', $ves_format);
    }

    /**
     * Registrar hooks de conversión
     */
    private function register_conversion_hooks() {
        // Hooks para conversión de precios
        add_filter('woocommerce_product_get_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_get_regular_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_get_sale_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_variation_get_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_variation_get_regular_price', array($this, 'convert_price'), 10, 2);
        add_filter('woocommerce_product_variation_get_sale_price', array($this, 'convert_price'), 10, 2);

        // Hooks para conversión de totales
        add_filter('woocommerce_cart_subtotal', array($this, 'convert_cart_subtotal'), 10, 3);
        add_filter('woocommerce_cart_total', array($this, 'convert_cart_total'), 10, 1);
        add_filter('woocommerce_cart_shipping_total', array($this, 'convert_shipping_total'), 10, 1);

        // Hooks para conversión de impuestos
        add_filter('woocommerce_cart_tax_totals', array($this, 'convert_tax_totals'), 10, 2);

        // Hooks para conversión de descuentos
        add_filter('woocommerce_cart_discount_total', array($this, 'convert_discount_total'), 10, 1);
    }

    /**
     * Registrar hooks de visualización
     */
    private function register_display_hooks() {
        // Hooks para mostrar precios en ambas monedas
        if ($this->settings['display_both_currencies'] ?? true) {
            add_action('woocommerce_single_product_summary', array($this, 'display_dual_price'), 25);
            add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_dual_price'), 15);
            add_action('woocommerce_cart_item_price', array($this, 'display_dual_price_cart'), 10, 3);
            add_action('woocommerce_cart_item_subtotal', array($this, 'display_dual_price_cart'), 10, 3);
        }

        // Hook para selector de moneda
        if ($this->settings['currency_switcher_enabled'] ?? true) {
            add_action('woocommerce_single_product_summary', array($this, 'display_currency_switcher'), 20);
            add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_currency_switcher'), 10);
        }
    }

    /**
     * Registrar shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('wcvs_price_switcher', array($this, 'shortcode_price_switcher'));
        add_shortcode('wcvs_price_display', array($this, 'shortcode_price_display'));
        add_shortcode('wcvs_currency_badge', array($this, 'shortcode_currency_badge'));
        add_shortcode('wcvs_conversion_rate', array($this, 'shortcode_conversion_rate'));
    }

    /**
     * Registrar widgets
     */
    private function register_widgets() {
        add_action('widgets_init', array($this, 'register_currency_widgets'));
    }

    /**
     * Convertir precio de USD a VES
     *
     * @param mixed $price Precio original
     * @param object $product Producto (opcional)
     * @return mixed Precio convertido
     */
    public function convert_price($price, $product = null) {
        // Verificar si la conversión está habilitada
        if (!$this->settings['conversion_enabled'] ?? true) {
            return $price;
        }

        // Verificar si el precio es válido
        if (empty($price) || !is_numeric($price)) {
            return $price;
        }

        // Obtener tasa de cambio
        $rate = $this->get_conversion_rate();
        if (!$rate || $rate <= 0) {
            return $price;
        }

        // Convertir precio
        $converted_price = $price * $rate;

        // Aplicar formato
        $converted_price = $this->format_price($converted_price);

        // Cachear conversión
        $this->cache_conversion($price, $converted_price, $rate);

        return $converted_price;
    }

    /**
     * Obtener tasa de conversión
     *
     * @return float|false
     */
    private function get_conversion_rate() {
        // Verificar cache primero
        $cached_rate = get_transient('wcvs_conversion_rate');
        if ($cached_rate !== false) {
            return $cached_rate;
        }

        // Obtener tasa del plugin BCV
        $bcv_integration = $this->plugin->get_bcv_integration();
        $rate_data = $bcv_integration->get_current_rate();

        if ($rate_data && isset($rate_data['rate']) && $rate_data['rate'] > 0) {
            $rate = $rate_data['rate'];
            
            // Cachear tasa por 30 minutos
            set_transient('wcvs_conversion_rate', $rate, 1800);
            
            return $rate;
        }

        // Usar tasa de fallback
        $fallback_rate = $this->settings['fallback_rate'] ?? 36.5;
        if ($fallback_rate > 0) {
            return $fallback_rate;
        }

        return false;
    }

    /**
     * Formatear precio según configuración
     *
     * @param float $price Precio a formatear
     * @return float
     */
    private function format_price($price) {
        // Aplicar redondeo
        $decimals = $this->settings['decimal_places'] ?? 2;
        $price = round($price, $decimals);

        return $price;
    }

    /**
     * Cachear conversión
     *
     * @param float $original_price Precio original
     * @param float $converted_price Precio convertido
     * @param float $rate Tasa de cambio
     */
    private function cache_conversion($original_price, $converted_price, $rate) {
        $cache_key = 'conversion_' . md5($original_price . '_' . $rate);
        $this->conversion_cache[$cache_key] = array(
            'original' => $original_price,
            'converted' => $converted_price,
            'rate' => $rate,
            'timestamp' => current_time('mysql')
        );

        // Limitar tamaño del cache
        if (count($this->conversion_cache) > 1000) {
            $this->conversion_cache = array_slice($this->conversion_cache, -500, null, true);
        }
    }

    /**
     * Cargar cache de conversiones
     */
    private function load_conversion_cache() {
        $cache = get_transient('wcvs_conversion_cache');
        if ($cache !== false) {
            $this->conversion_cache = $cache;
        }
    }

    /**
     * Guardar cache de conversiones
     */
    private function save_conversion_cache() {
        set_transient('wcvs_conversion_cache', $this->conversion_cache, 3600); // 1 hora
    }

    /**
     * Mostrar precio en ambas monedas
     */
    public function display_dual_price() {
        global $product;

        if (!$product) {
            return;
        }

        $usd_price = $product->get_price();
        $ves_price = $this->convert_price($usd_price, $product);

        if ($usd_price && $ves_price) {
            echo '<div class="wcvs-dual-price">';
            echo '<span class="wcvs-price-usd">$' . number_format($usd_price, 2, '.', ',') . ' USD</span>';
            echo '<span class="wcvs-price-separator"> | </span>';
            echo '<span class="wcvs-price-ves">' . number_format($ves_price, 2, ',', '.') . ' Bs.</span>';
            echo '</div>';
        }
    }

    /**
     * Mostrar precio en ambas monedas en carrito
     *
     * @param string $price_html HTML del precio
     * @param array $cart_item Item del carrito
     * @param string $cart_item_key Clave del item
     * @return string
     */
    public function display_dual_price_cart($price_html, $cart_item, $cart_item_key) {
        $product = $cart_item['data'];
        $usd_price = $product->get_price();
        $ves_price = $this->convert_price($usd_price, $product);

        if ($usd_price && $ves_price) {
            $dual_price_html = '<div class="wcvs-dual-price-cart">';
            $dual_price_html .= '<div class="wcvs-price-usd">$' . number_format($usd_price, 2, '.', ',') . ' USD</div>';
            $dual_price_html .= '<div class="wcvs-price-ves">' . number_format($ves_price, 2, ',', '.') . ' Bs.</div>';
            $dual_price_html .= '</div>';
            
            return $dual_price_html;
        }

        return $price_html;
    }

    /**
     * Mostrar selector de moneda
     */
    public function display_currency_switcher() {
        $style = $this->settings['switcher_style'] ?? 'buttons';
        
        echo '<div class="wcvs-currency-switcher wcvs-switcher-' . esc_attr($style) . '">';
        
        switch ($style) {
            case 'buttons':
                $this->display_currency_buttons();
                break;
            case 'dropdown':
                $this->display_currency_dropdown();
                break;
            case 'toggle':
                $this->display_currency_toggle();
                break;
        }
        
        echo '</div>';
    }

    /**
     * Mostrar botones de moneda
     */
    private function display_currency_buttons() {
        echo '<div class="wcvs-currency-buttons">';
        echo '<button class="wcvs-currency-btn wcvs-btn-usd active" data-currency="USD">USD</button>';
        echo '<button class="wcvs-currency-btn wcvs-btn-ves" data-currency="VES">VES</button>';
        echo '</div>';
    }

    /**
     * Mostrar dropdown de moneda
     */
    private function display_currency_dropdown() {
        echo '<select class="wcvs-currency-select">';
        echo '<option value="USD">USD - Dólares</option>';
        echo '<option value="VES">VES - Bolívares</option>';
        echo '</select>';
    }

    /**
     * Mostrar toggle de moneda
     */
    private function display_currency_toggle() {
        echo '<div class="wcvs-currency-toggle">';
        echo '<label class="wcvs-toggle-label">';
        echo '<input type="checkbox" class="wcvs-toggle-input" data-currency="VES">';
        echo '<span class="wcvs-toggle-slider"></span>';
        echo '<span class="wcvs-toggle-text">Mostrar en Bolívares</span>';
        echo '</label>';
        echo '</div>';
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

        ob_start();
        echo '<div class="wcvs-price-switcher ' . esc_attr($atts['class']) . '">';
        $this->display_currency_switcher();
        echo '</div>';
        return ob_get_clean();
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
            'currency' => 'USD',
            'show_both' => 'true',
            'class' => ''
        ), $atts);

        if (empty($atts['price']) || !is_numeric($atts['price'])) {
            return '';
        }

        $price = floatval($atts['price']);
        $converted_price = $this->convert_price($price);

        ob_start();
        echo '<div class="wcvs-price-display ' . esc_attr($atts['class']) . '">';
        
        if ($atts['show_both'] === 'true') {
            echo '<span class="wcvs-price-usd">$' . number_format($price, 2, '.', ',') . ' USD</span>';
            echo '<span class="wcvs-price-separator"> | </span>';
            echo '<span class="wcvs-price-ves">' . number_format($converted_price, 2, ',', '.') . ' Bs.</span>';
        } else {
            if ($atts['currency'] === 'VES') {
                echo '<span class="wcvs-price-ves">' . number_format($converted_price, 2, ',', '.') . ' Bs.</span>';
            } else {
                echo '<span class="wcvs-price-usd">$' . number_format($price, 2, '.', ',') . ' USD</span>';
            }
        }
        
        echo '</div>';
        return ob_get_clean();
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

        ob_start();
        echo '<span class="wcvs-currency-badge wcvs-badge-' . esc_attr(strtolower($atts['currency'])) . ' ' . esc_attr($atts['class']) . '">';
        echo esc_html($atts['currency']);
        echo '</span>';
        return ob_get_clean();
    }

    /**
     * Shortcode para mostrar tasa de conversión
     *
     * @param array $atts Atributos del shortcode
     * @return string
     */
    public function shortcode_conversion_rate($atts) {
        $atts = shortcode_atts(array(
            'format' => 'full',
            'class' => ''
        ), $atts);

        $rate = $this->get_conversion_rate();
        if (!$rate) {
            return '';
        }

        ob_start();
        echo '<div class="wcvs-conversion-rate ' . esc_attr($atts['class']) . '">';
        
        if ($atts['format'] === 'full') {
            echo '<span class="wcvs-rate-label">Tasa BCV:</span> ';
            echo '<span class="wcvs-rate-value">' . number_format($rate, 4, ',', '.') . ' Bs./USD</span>';
        } else {
            echo '<span class="wcvs-rate-value">' . number_format($rate, 2, ',', '.') . '</span>';
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Registrar widgets de moneda
     */
    public function register_currency_widgets() {
        register_widget('WCVS_Currency_Widget');
        register_widget('WCVS_Conversion_Rate_Widget');
    }

    /**
     * Manejar actualización de tasa BCV
     *
     * @param float $new_rate Nueva tasa
     * @param float $old_rate Tasa anterior
     */
    public function handle_rate_update($new_rate, $old_rate) {
        // Limpiar cache de conversiones
        delete_transient('wcvs_conversion_rate');
        $this->conversion_cache = array();
        delete_transient('wcvs_conversion_cache');

        // Log de actualización
        WCVS_Logger::info(WCVS_Logger::CONTEXT_CURRENCY, "Tasa de conversión actualizada: {$old_rate} → {$new_rate}");

        // Notificar a otros módulos
        do_action('wcvs_currency_rate_updated', $new_rate, $old_rate);
    }

    /**
     * Limpiar cache
     */
    public function cleanup_cache() {
        $this->conversion_cache = array();
        delete_transient('wcvs_conversion_cache');
        delete_transient('wcvs_conversion_rate');
    }

    /**
     * Manejar activación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'currency_manager') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_CURRENCY, 'Módulo Currency Manager activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'currency_manager') {
            // Limpiar hooks
            remove_filter('woocommerce_product_get_price', array($this, 'convert_price'));
            remove_filter('woocommerce_product_get_regular_price', array($this, 'convert_price'));
            remove_filter('woocommerce_product_get_sale_price', array($this, 'convert_price'));
            
            // Limpiar cache
            $this->cleanup_cache();
            
            WCVS_Logger::info(WCVS_Logger::CONTEXT_CURRENCY, 'Módulo Currency Manager desactivado');
        }
    }

    /**
     * Convertir subtotal del carrito
     *
     * @param string $subtotal Subtotal formateado
     * @param object $cart Carrito
     * @param string $compound Si es compuesto
     * @return string
     */
    public function convert_cart_subtotal($subtotal, $cart, $compound) {
        if (!$this->settings['conversion_enabled'] ?? true) {
            return $subtotal;
        }

        $usd_subtotal = $cart->get_subtotal();
        $ves_subtotal = $this->convert_price($usd_subtotal);

        if ($usd_subtotal && $ves_subtotal) {
            return '<span class="wcvs-dual-subtotal">' .
                   '<span class="wcvs-price-usd">$' . number_format($usd_subtotal, 2, '.', ',') . ' USD</span>' .
                   '<span class="wcvs-price-separator"> | </span>' .
                   '<span class="wcvs-price-ves">' . number_format($ves_subtotal, 2, ',', '.') . ' Bs.</span>' .
                   '</span>';
        }

        return $subtotal;
    }

    /**
     * Convertir total del carrito
     *
     * @param string $total Total formateado
     * @return string
     */
    public function convert_cart_total($total) {
        if (!$this->settings['conversion_enabled'] ?? true) {
            return $total;
        }

        $cart = WC()->cart;
        $usd_total = $cart->get_total('raw');
        $ves_total = $this->convert_price($usd_total);

        if ($usd_total && $ves_total) {
            return '<span class="wcvs-dual-total">' .
                   '<span class="wcvs-price-usd">$' . number_format($usd_total, 2, '.', ',') . ' USD</span>' .
                   '<span class="wcvs-price-separator"> | </span>' .
                   '<span class="wcvs-price-ves">' . number_format($ves_total, 2, ',', '.') . ' Bs.</span>' .
                   '</span>';
        }

        return $total;
    }

    /**
     * Convertir total de envío
     *
     * @param string $shipping_total Total de envío formateado
     * @return string
     */
    public function convert_shipping_total($shipping_total) {
        if (!$this->settings['conversion_enabled'] ?? true) {
            return $shipping_total;
        }

        // Extraer número del total de envío
        preg_match('/[\d,]+\.?\d*/', $shipping_total, $matches);
        if (!empty($matches[0])) {
            $usd_shipping = floatval(str_replace(',', '', $matches[0]));
            $ves_shipping = $this->convert_price($usd_shipping);

            if ($ves_shipping) {
                return '<span class="wcvs-dual-shipping">' .
                       '<span class="wcvs-price-usd">$' . number_format($usd_shipping, 2, '.', ',') . ' USD</span>' .
                       '<span class="wcvs-price-separator"> | </span>' .
                       '<span class="wcvs-price-ves">' . number_format($ves_shipping, 2, ',', '.') . ' Bs.</span>' .
                       '</span>';
            }
        }

        return $shipping_total;
    }

    /**
     * Convertir totales de impuestos
     *
     * @param array $tax_totals Totales de impuestos
     * @param object $cart Carrito
     * @return array
     */
    public function convert_tax_totals($tax_totals, $cart) {
        if (!$this->settings['conversion_enabled'] ?? true) {
            return $tax_totals;
        }

        foreach ($tax_totals as $key => $tax_total) {
            if (isset($tax_total->amount)) {
                $usd_amount = $tax_total->amount;
                $ves_amount = $this->convert_price($usd_amount);

                if ($ves_amount) {
                    $tax_totals[$key]->formatted_amount = '<span class="wcvs-dual-tax">' .
                                                          '<span class="wcvs-price-usd">$' . number_format($usd_amount, 2, '.', ',') . ' USD</span>' .
                                                          '<span class="wcvs-price-separator"> | </span>' .
                                                          '<span class="wcvs-price-ves">' . number_format($ves_amount, 2, ',', '.') . ' Bs.</span>' .
                                                          '</span>';
                }
            }
        }

        return $tax_totals;
    }

    /**
     * Convertir total de descuento
     *
     * @param string $discount_total Total de descuento formateado
     * @return string
     */
    public function convert_discount_total($discount_total) {
        if (!$this->settings['conversion_enabled'] ?? true) {
            return $discount_total;
        }

        // Extraer número del total de descuento
        preg_match('/[\d,]+\.?\d*/', $discount_total, $matches);
        if (!empty($matches[0])) {
            $usd_discount = floatval(str_replace(',', '', $matches[0]));
            $ves_discount = $this->convert_price($usd_discount);

            if ($ves_discount) {
                return '<span class="wcvs-dual-discount">' .
                       '<span class="wcvs-price-usd">$' . number_format($usd_discount, 2, '.', ',') . ' USD</span>' .
                       '<span class="wcvs-price-separator"> | </span>' .
                       '<span class="wcvs-price-ves">' . number_format($ves_discount, 2, ',', '.') . ' Bs.</span>' .
                       '</span>';
            }
        }

        return $discount_total;
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        return array(
            'conversion_enabled' => $this->settings['conversion_enabled'] ?? true,
            'display_both_currencies' => $this->settings['display_both_currencies'] ?? true,
            'currency_switcher_enabled' => $this->settings['currency_switcher_enabled'] ?? true,
            'current_rate' => $this->get_conversion_rate(),
            'cache_size' => count($this->conversion_cache),
            'last_rate_update' => get_option('wcvs_rate_last_update')
        );
    }
}
