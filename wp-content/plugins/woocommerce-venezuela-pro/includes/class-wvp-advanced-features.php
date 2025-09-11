<?php
/**
 * Funcionalidades avanzadas
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Advanced_Features {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Configuración de funcionalidades
     * 
     * @var array
     */
    private $config;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->load_config();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Funcionalidades de productos
        add_action('woocommerce_single_product_summary', array($this, 'add_product_features'), 25);
        add_action('woocommerce_after_shop_loop_item', array($this, 'add_shop_features'), 15);
        
        // Funcionalidades de carrito
        add_action('woocommerce_cart_collaterals', array($this, 'add_cart_features'), 10);
        add_action('woocommerce_cart_totals_after_order_total', array($this, 'add_cart_totals_features'), 10);
        
        // Funcionalidades de checkout
        add_action('woocommerce_checkout_before_customer_details', array($this, 'add_checkout_features'), 10);
        add_action('woocommerce_checkout_after_customer_details', array($this, 'add_checkout_after_features'), 10);
        
        // Funcionalidades de pedidos
        add_action('woocommerce_order_details_after_order_table', array($this, 'add_order_features'), 10);
        add_action('woocommerce_email_order_details', array($this, 'add_email_features'), 10, 4);
        
        // Funcionalidades de admin
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_admin_order_features'), 10, 1);
        add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'add_admin_shipping_features'), 10, 1);
        
        // Funcionalidades de widgets
        add_action('widgets_init', array($this, 'register_widgets'));
        
        // Funcionalidades de shortcodes
        add_action('init', array($this, 'register_shortcodes'));
        
        // Funcionalidades de API
        add_action('rest_api_init', array($this, 'register_api_endpoints'));
        
        // Funcionalidades de AJAX
        add_action('wp_ajax_wvp_get_product_info', array($this, 'get_product_info_ajax'));
        add_action('wp_ajax_nopriv_wvp_get_product_info', array($this, 'get_product_info_ajax'));
        add_action('wp_ajax_wvp_calculate_shipping', array($this, 'calculate_shipping_ajax'));
        add_action('wp_ajax_nopriv_wvp_calculate_shipping', array($this, 'calculate_shipping_ajax'));
    }
    
    /**
     * Cargar configuración de funcionalidades
     */
    private function load_config() {
        $this->config = array(
            'product_features' => get_option('wvp_product_features', 'yes'),
            'cart_features' => get_option('wvp_cart_features', 'yes'),
            'checkout_features' => get_option('wvp_checkout_features', 'yes'),
            'order_features' => get_option('wvp_order_features', 'yes'),
            'admin_features' => get_option('wvp_admin_features', 'yes'),
            'widget_features' => get_option('wvp_widget_features', 'yes'),
            'shortcode_features' => get_option('wvp_shortcode_features', 'yes'),
            'api_features' => get_option('wvp_api_features', 'yes'),
            'ajax_features' => get_option('wvp_ajax_features', 'yes')
        );
    }
    
    /**
     * Añadir funcionalidades a productos
     */
    public function add_product_features() {
        if ($this->config['product_features'] !== 'yes') {
            return;
        }
        
        global $product;
        
        if (!$product) {
            return;
        }
        
        // Información de conversión
        $this->display_conversion_info($product);
        
        // Información de IGTF
        $this->display_igtf_info($product);
        
        // Información de envío
        $this->display_shipping_info($product);
        
        // Información de disponibilidad
        $this->display_availability_info($product);
    }
    
    /**
     * Añadir funcionalidades a tienda
     */
    public function add_shop_features() {
        if ($this->config['product_features'] !== 'yes') {
            return;
        }
        
        global $product;
        
        if (!$product) {
            return;
        }
        
        // Botón de información rápida
        $this->display_quick_info_button($product);
        
        // Información de precio
        $this->display_price_info($product);
    }
    
    /**
     * Añadir funcionalidades al carrito
     */
    public function add_cart_features() {
        if ($this->config['cart_features'] !== 'yes') {
            return;
        }
        
        // Resumen de conversión
        $this->display_conversion_summary();
        
        // Información de IGTF
        $this->display_igtf_summary();
        
        // Información de envío
        $this->display_shipping_summary();
    }
    
    /**
     * Añadir funcionalidades a totales del carrito
     */
    public function add_cart_totals_features() {
        if ($this->config['cart_features'] !== 'yes') {
            return;
        }
        
        // Desglose de impuestos
        $this->display_tax_breakdown();
        
        // Información de descuentos
        $this->display_discount_info();
    }
    
    /**
     * Añadir funcionalidades al checkout
     */
    public function add_checkout_features() {
        if ($this->config['checkout_features'] !== 'yes') {
            return;
        }
        
        // Información de resumen
        $this->display_checkout_summary();
        
        // Información de seguridad
        $this->display_security_info();
    }
    
    /**
     * Añadir funcionalidades después del checkout
     */
    public function add_checkout_after_features() {
        if ($this->config['checkout_features'] !== 'yes') {
            return;
        }
        
        // Información de confirmación
        $this->display_confirmation_info();
    }
    
    /**
     * Añadir funcionalidades a pedidos
     */
    public function add_order_features($order) {
        if ($this->config['order_features'] !== 'yes') {
            return;
        }
        
        // Información de conversión del pedido
        $this->display_order_conversion_info($order);
        
        // Información de IGTF del pedido
        $this->display_order_igtf_info($order);
        
        // Información de envío del pedido
        $this->display_order_shipping_info($order);
    }
    
    /**
     * Añadir funcionalidades a emails
     */
    public function add_email_features($order, $sent_to_admin, $plain_text, $email) {
        if ($this->config['order_features'] !== 'yes') {
            return;
        }
        
        // Información adicional en emails
        $this->display_email_additional_info($order, $plain_text);
    }
    
    /**
     * Añadir funcionalidades a admin de pedidos
     */
    public function add_admin_order_features($order) {
        if ($this->config['admin_features'] !== 'yes') {
            return;
        }
        
        // Información de conversión en admin
        $this->display_admin_conversion_info($order);
        
        // Información de IGTF en admin
        $this->display_admin_igtf_info($order);
    }
    
    /**
     * Añadir funcionalidades a envío en admin
     */
    public function add_admin_shipping_features($order) {
        if ($this->config['admin_features'] !== 'yes') {
            return;
        }
        
        // Información de envío en admin
        $this->display_admin_shipping_info($order);
    }
    
    /**
     * Registrar widgets
     */
    public function register_widgets() {
        if ($this->config['widget_features'] !== 'yes') {
            return;
        }
        
        // Solo registrar widgets si las clases existen
        if (class_exists('WVP_Currency_Converter_Widget')) {
            register_widget('WVP_Currency_Converter_Widget');
        }
        if (class_exists('WVP_Product_Info_Widget')) {
            register_widget('WVP_Product_Info_Widget');
        }
        if (class_exists('WVP_Order_Status_Widget')) {
            register_widget('WVP_Order_Status_Widget');
        }
    }
    
    /**
     * Registrar shortcodes
     */
    public function register_shortcodes() {
        if ($this->config['shortcode_features'] !== 'yes') {
            return;
        }
        
        add_shortcode('wvp_currency_converter', array($this, 'currency_converter_shortcode'));
        add_shortcode('wvp_product_info', array($this, 'product_info_shortcode'));
        add_shortcode('wvp_order_status', array($this, 'order_status_shortcode'));
        add_shortcode('wvp_igtf_calculator', array($this, 'igtf_calculator_shortcode'));
    }
    
    /**
     * Registrar endpoints de API
     */
    public function register_api_endpoints() {
        if ($this->config['api_features'] !== 'yes') {
            return;
        }
        
        register_rest_route('wvp/v1', '/products/(?P<id>\d+)/info', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product_info_api'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wvp/v1', '/calculate-shipping', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_shipping_api'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wvp/v1', '/currency-rates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_currency_rates_api'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Obtener información de producto vía AJAX
     */
    public function get_product_info_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_ajax_nonce')) {
            wp_die('Error de seguridad');
        }
        
        $product_id = intval($_POST['product_id']);
        $product = wc_get_product($product_id);
        
        if (!$product) {
            wp_send_json_error('Producto no encontrado');
        }
        
        $info = $this->get_product_info($product);
        wp_send_json_success($info);
    }
    
    /**
     * Calcular envío vía AJAX
     */
    public function calculate_shipping_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_ajax_nonce')) {
            wp_die('Error de seguridad');
        }
        
        $postcode = sanitize_text_field($_POST['postcode']);
        $city = sanitize_text_field($_POST['city']);
        $state = sanitize_text_field($_POST['state']);
        
        $shipping_info = $this->calculate_shipping_info($postcode, $city, $state);
        wp_send_json_success($shipping_info);
    }
    
    /**
     * Mostrar información de conversión
     */
    private function display_conversion_info($product) {
        $price = $product->get_price();
        if (!$price) {
            return;
        }
        
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate) {
            return;
        }
        
        $ves_price = $price * $rate;
        
        echo '<div class="wvp-conversion-info">';
        echo '<h4>' . __('Información de Conversión', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('Precio en USD: $%s', 'wvp'),
            number_format($price, 2)
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Equivale a: %s', 'wvp'),
            $this->format_ves_price($ves_price)
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Tasa BCV: %s Bs./USD', 'wvp'),
            number_format($rate, 2, ',', '.')
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de IGTF
     */
    private function display_igtf_info($product) {
        if (!class_exists('WVP_IGTF_Manager')) {
            return;
        }
        
        $igtf_manager = new WVP_IGTF_Manager();
        if (!$igtf_manager->is_enabled()) {
            return;
        }
        
        $price = $product->get_price();
        if (!$price) {
            return;
        }
        
        $igtf_amount = $igtf_manager->calculate_igtf($price);
        
        if ($igtf_amount > 0) {
            echo '<div class="wvp-igtf-info">';
            echo '<h4>' . __('Información de IGTF', 'wvp') . '</h4>';
            echo '<p>' . sprintf(
                __('IGTF aplicable: $%s', 'wvp'),
                number_format($igtf_amount, 2)
            ) . '</p>';
            echo '<p>' . sprintf(
                __('Total con IGTF: $%s', 'wvp'),
                number_format($price + $igtf_amount, 2)
            ) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Mostrar información de envío
     */
    private function display_shipping_info($product) {
        $weight = $product->get_weight();
        $dimensions = $product->get_dimensions(false);
        
        if ($weight || $dimensions['length']) {
            echo '<div class="wvp-shipping-info">';
            echo '<h4>' . __('Información de Envío', 'wvp') . '</h4>';
            
            if ($weight) {
                echo '<p>' . sprintf(
                    __('Peso: %s kg', 'wvp'),
                    number_format($weight, 2)
                ) . '</p>';
            }
            
            if ($dimensions['length']) {
                echo '<p>' . sprintf(
                    __('Dimensiones: %s x %s x %s cm', 'wvp'),
                    $dimensions['length'],
                    $dimensions['width'],
                    $dimensions['height']
                ) . '</p>';
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Mostrar información de disponibilidad
     */
    private function display_availability_info($product) {
        $stock_status = $product->get_stock_status();
        $stock_quantity = $product->get_stock_quantity();
        
        echo '<div class="wvp-availability-info">';
        echo '<h4>' . __('Disponibilidad', 'wvp') . '</h4>';
        
        if ($stock_status === 'instock') {
            if ($stock_quantity) {
                echo '<p class="in-stock">' . sprintf(
                    __('En stock (%d disponibles)', 'wvp'),
                    $stock_quantity
                ) . '</p>';
            } else {
                echo '<p class="in-stock">' . __('En stock', 'wvp') . '</p>';
            }
        } elseif ($stock_status === 'outofstock') {
            echo '<p class="out-of-stock">' . __('Agotado', 'wvp') . '</p>';
        } else {
            echo '<p class="on-backorder">' . __('Pedido especial', 'wvp') . '</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Mostrar botón de información rápida
     */
    private function display_quick_info_button($product) {
        echo '<div class="wvp-quick-info">';
        echo '<button class="wvp-quick-info-btn" data-product-id="' . $product->get_id() . '">';
        echo __('Información Rápida', 'wvp');
        echo '</button>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de precio
     */
    private function display_price_info($product) {
        $price = $product->get_price();
        if (!$price) {
            return;
        }
        
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate) {
            return;
        }
        
        $ves_price = $price * $rate;
        
        echo '<div class="wvp-price-info">';
        echo '<p class="usd-price">$' . number_format($price, 2) . '</p>';
        echo '<p class="ves-price">' . $this->format_ves_price($ves_price) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar resumen de conversión
     */
    private function display_conversion_summary() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $total = $cart->get_total('raw');
        $rate = WVP_BCV_Integrator::get_rate();
        
        if (!$rate) {
            return;
        }
        
        $ves_total = $total * $rate;
        
        echo '<div class="wvp-conversion-summary">';
        echo '<h4>' . __('Resumen de Conversión', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('Total en USD: $%s', 'wvp'),
            number_format($total, 2)
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Equivale a: %s', 'wvp'),
            $this->format_ves_price($ves_total)
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar resumen de IGTF
     */
    private function display_igtf_summary() {
        if (!class_exists('WVP_IGTF_Manager')) {
            return;
        }
        
        $igtf_manager = new WVP_IGTF_Manager();
        if (!$igtf_manager->is_enabled()) {
            return;
        }
        
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $total = $cart->get_total('raw');
        $igtf_amount = $igtf_manager->calculate_igtf($total);
        
        if ($igtf_amount > 0) {
            echo '<div class="wvp-igtf-summary">';
            echo '<h4>' . __('Resumen de IGTF', 'wvp') . '</h4>';
            echo '<p>' . sprintf(
                __('IGTF aplicable: $%s', 'wvp'),
                number_format($igtf_amount, 2)
            ) . '</p>';
            echo '<p>' . sprintf(
                __('Total con IGTF: $%s', 'wvp'),
                number_format($total + $igtf_amount, 2)
            ) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Mostrar resumen de envío
     */
    private function display_shipping_summary() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $shipping_total = $cart->get_shipping_total();
        $shipping_tax = $cart->get_shipping_tax();
        
        if ($shipping_total > 0) {
            echo '<div class="wvp-shipping-summary">';
            echo '<h4>' . __('Resumen de Envío', 'wvp') . '</h4>';
            echo '<p>' . sprintf(
                __('Costo de envío: $%s', 'wvp'),
                number_format($shipping_total, 2)
            ) . '</p>';
            
            if ($shipping_tax > 0) {
                echo '<p>' . sprintf(
                    __('Impuesto de envío: $%s', 'wvp'),
                    number_format($shipping_tax, 2)
                ) . '</p>';
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Mostrar desglose de impuestos
     */
    private function display_tax_breakdown() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $tax_totals = $cart->get_tax_totals();
        
        if (!empty($tax_totals)) {
            echo '<div class="wvp-tax-breakdown">';
            echo '<h4>' . __('Desglose de Impuestos', 'wvp') . '</h4>';
            
            foreach ($tax_totals as $tax) {
                echo '<p>' . sprintf(
                    '%s: $%s',
                    $tax->label,
                    number_format($tax->amount, 2)
                ) . '</p>';
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Mostrar información de descuentos
     */
    private function display_discount_info() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $discount_total = $cart->get_discount_total();
        
        if ($discount_total > 0) {
            echo '<div class="wvp-discount-info">';
            echo '<h4>' . __('Descuentos Aplicados', 'wvp') . '</h4>';
            echo '<p>' . sprintf(
                __('Descuento total: $%s', 'wvp'),
                number_format($discount_total, 2)
            ) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Mostrar resumen del checkout
     */
    private function display_checkout_summary() {
        echo '<div class="wvp-checkout-summary">';
        echo '<h4>' . __('Resumen del Pedido', 'wvp') . '</h4>';
        echo '<p>' . __('Revise los detalles de su pedido antes de proceder al pago.', 'wvp') . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de seguridad
     */
    private function display_security_info() {
        echo '<div class="wvp-security-info">';
        echo '<h4>' . __('Información de Seguridad', 'wvp') . '</h4>';
        echo '<p>' . __('Su información está protegida con encriptación SSL.', 'wvp') . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de confirmación
     */
    private function display_confirmation_info() {
        echo '<div class="wvp-confirmation-info">';
        echo '<h4>' . __('Confirmación', 'wvp') . '</h4>';
        echo '<p>' . __('Al proceder, confirma que ha revisado y acepta los términos y condiciones.', 'wvp') . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de conversión del pedido
     */
    private function display_order_conversion_info($order) {
        $total = $order->get_total('raw');
        $rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$rate) {
            return;
        }
        
        $ves_total = $total * $rate;
        
        echo '<div class="wvp-order-conversion-info">';
        echo '<h4>' . __('Información de Conversión del Pedido', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('Total en USD: $%s', 'wvp'),
            number_format($total, 2)
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Equivale a: %s', 'wvp'),
            $this->format_ves_price($ves_total)
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Tasa BCV: %s Bs./USD', 'wvp'),
            number_format($rate, 2, ',', '.')
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de IGTF del pedido
     */
    private function display_order_igtf_info($order) {
        $igtf_amount = $order->get_meta('_igtf_amount');
        
        if (!$igtf_amount) {
            return;
        }
        
        echo '<div class="wvp-order-igtf-info">';
        echo '<h4>' . __('Información de IGTF del Pedido', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('IGTF aplicado: $%s', 'wvp'),
            number_format($igtf_amount, 2)
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de envío del pedido
     */
    private function display_order_shipping_info($order) {
        $shipping_method = $order->get_shipping_method();
        $shipping_total = $order->get_shipping_total();
        
        if (!$shipping_method) {
            return;
        }
        
        echo '<div class="wvp-order-shipping-info">';
        echo '<h4>' . __('Información de Envío del Pedido', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('Método de envío: %s', 'wvp'),
            $shipping_method
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Costo de envío: $%s', 'wvp'),
            number_format($shipping_total, 2)
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información adicional en emails
     */
    private function display_email_additional_info($order, $plain_text) {
        if ($plain_text) {
            echo "\n\n" . __('Información Adicional:', 'wvp') . "\n";
            echo __('Gracias por su compra. Su pedido será procesado en breve.', 'wvp') . "\n";
        } else {
            echo '<div style="background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #0073aa;">';
            echo '<h4>' . __('Información Adicional', 'wvp') . '</h4>';
            echo '<p>' . __('Gracias por su compra. Su pedido será procesado en breve.', 'wvp') . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Mostrar información de conversión en admin
     */
    private function display_admin_conversion_info($order) {
        $total = $order->get_total('raw');
        $rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$rate) {
            return;
        }
        
        $ves_total = $total * $rate;
        
        echo '<div class="wvp-admin-conversion-info">';
        echo '<h4>' . __('Información de Conversión', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('Total en USD: $%s', 'wvp'),
            number_format($total, 2)
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Equivale a: %s', 'wvp'),
            $this->format_ves_price($ves_total)
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de IGTF en admin
     */
    private function display_admin_igtf_info($order) {
        $igtf_amount = $order->get_meta('_igtf_amount');
        
        if (!$igtf_amount) {
            return;
        }
        
        echo '<div class="wvp-admin-igtf-info">';
        echo '<h4>' . __('Información de IGTF', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('IGTF aplicado: $%s', 'wvp'),
            number_format($igtf_amount, 2)
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Mostrar información de envío en admin
     */
    private function display_admin_shipping_info($order) {
        $shipping_method = $order->get_shipping_method();
        $shipping_total = $order->get_shipping_total();
        
        if (!$shipping_method) {
            return;
        }
        
        echo '<div class="wvp-admin-shipping-info">';
        echo '<h4>' . __('Información de Envío', 'wvp') . '</h4>';
        echo '<p>' . sprintf(
            __('Método: %s', 'wvp'),
            $shipping_method
        ) . '</p>';
        echo '<p>' . sprintf(
            __('Costo: $%s', 'wvp'),
            number_format($shipping_total, 2)
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Shortcode de convertidor de moneda
     */
    public function currency_converter_shortcode($atts) {
        $atts = shortcode_atts(array(
            'from' => 'USD',
            'to' => 'VES',
            'amount' => 1
        ), $atts);
        
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate) {
            return '<p>' . __('Tasa de cambio no disponible', 'wvp') . '</p>';
        }
        
        $converted = $atts['amount'] * $rate;
        
        return '<div class="wvp-currency-converter">' .
               '<p>' . sprintf(
                   __('%s %s = %s %s', 'wvp'),
                   number_format($atts['amount'], 2),
                   $atts['from'],
                   $this->format_ves_price($converted),
                   $atts['to']
               ) . '</p>' .
               '</div>';
    }
    
    /**
     * Shortcode de información de producto
     */
    public function product_info_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'show_price' => 'yes',
            'show_conversion' => 'yes',
            'show_igtf' => 'yes'
        ), $atts);
        
        $product = wc_get_product($atts['id']);
        if (!$product) {
            return '<p>' . __('Producto no encontrado', 'wvp') . '</p>';
        }
        
        $output = '<div class="wvp-product-info">';
        $output .= '<h3>' . $product->get_name() . '</h3>';
        
        if ($atts['show_price'] === 'yes') {
            $price = $product->get_price();
            $output .= '<p>' . sprintf(
                __('Precio: $%s', 'wvp'),
                number_format($price, 2)
            ) . '</p>';
        }
        
        if ($atts['show_conversion'] === 'yes') {
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate) {
                $ves_price = $product->get_price() * $rate;
                $output .= '<p>' . sprintf(
                    __('Equivale a: %s', 'wvp'),
                    $this->format_ves_price($ves_price)
                ) . '</p>';
            }
        }
        
        if ($atts['show_igtf'] === 'yes') {
            if (class_exists('WVP_IGTF_Manager')) {
                $igtf_manager = new WVP_IGTF_Manager();
                if ($igtf_manager->is_enabled()) {
                    $igtf_amount = $igtf_manager->calculate_igtf($product->get_price());
                    if ($igtf_amount > 0) {
                        $output .= '<p>' . sprintf(
                            __('IGTF: $%s', 'wvp'),
                            number_format($igtf_amount, 2)
                        ) . '</p>';
                    }
                }
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Shortcode de estado de pedido
     */
    public function order_status_shortcode($atts) {
        $atts = shortcode_atts(array(
            'order_id' => 0,
            'show_conversion' => 'yes',
            'show_igtf' => 'yes'
        ), $atts);
        
        $order = wc_get_order($atts['order_id']);
        if (!$order) {
            return '<p>' . __('Pedido no encontrado', 'wvp') . '</p>';
        }
        
        $output = '<div class="wvp-order-status">';
        $output .= '<h3>' . sprintf(
            __('Pedido #%s', 'wvp'),
            $order->get_order_number()
        ) . '</h3>';
        $output .= '<p>' . sprintf(
            __('Estado: %s', 'wvp'),
            wc_get_order_status_name($order->get_status())
        ) . '</p>';
        
        if ($atts['show_conversion'] === 'yes') {
            $rate = $order->get_meta('_bcv_rate_at_purchase');
            if ($rate) {
                $ves_total = $order->get_total('raw') * $rate;
                $output .= '<p>' . sprintf(
                    __('Total: $%s (%s)', 'wvp'),
                    number_format($order->get_total('raw'), 2),
                    $this->format_ves_price($ves_total)
                ) . '</p>';
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Shortcode de calculadora de IGTF
     */
    public function igtf_calculator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'amount' => 100,
            'rate' => 3.0
        ), $atts);
        
        $igtf_amount = ($atts['amount'] * $atts['rate']) / 100;
        $total = $atts['amount'] + $igtf_amount;
        
        return '<div class="wvp-igtf-calculator">' .
               '<h3>' . __('Calculadora de IGTF', 'wvp') . '</h3>' .
               '<p>' . sprintf(
                   __('Monto: $%s', 'wvp'),
                   number_format($atts['amount'], 2)
               ) . '</p>' .
               '<p>' . sprintf(
                   __('IGTF (%s%%): $%s', 'wvp'),
                   number_format($atts['rate'], 1),
                   number_format($igtf_amount, 2)
               ) . '</p>' .
               '<p>' . sprintf(
                   __('Total: $%s', 'wvp'),
                   number_format($total, 2)
               ) . '</p>' .
               '</div>';
    }
    
    /**
     * Obtener información de producto para API
     */
    public function get_product_info_api($request) {
        $product_id = $request['id'];
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return new WP_Error('product_not_found', 'Producto no encontrado', array('status' => 404));
        }
        
        return $this->get_product_info($product);
    }
    
    /**
     * Calcular envío para API
     */
    public function calculate_shipping_api($request) {
        $postcode = $request->get_param('postcode');
        $city = $request->get_param('city');
        $state = $request->get_param('state');
        
        return $this->calculate_shipping_info($postcode, $city, $state);
    }
    
    /**
     * Obtener tasas de cambio para API
     */
    public function get_currency_rates_api($request) {
        $rate = WVP_BCV_Integrator::get_rate();
        
        if (!$rate) {
            return new WP_Error('rate_not_available', 'Tasa de cambio no disponible', array('status' => 503));
        }
        
        return array(
            'usd_to_ves' => $rate,
            'ves_to_usd' => 1 / $rate,
            'timestamp' => current_time('mysql'),
            'source' => 'BCV'
        );
    }
    
    /**
     * Obtener información de producto
     */
    private function get_product_info($product) {
        $price = $product->get_price();
        $rate = WVP_BCV_Integrator::get_rate();
        $ves_price = $rate ? $price * $rate : 0;
        
        $info = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'price_usd' => $price,
            'price_ves' => $ves_price,
            'rate' => $rate,
            'stock_status' => $product->get_stock_status(),
            'stock_quantity' => $product->get_stock_quantity(),
            'weight' => $product->get_weight(),
            'dimensions' => $product->get_dimensions(false)
        );
        
        // Añadir información de IGTF
        if (class_exists('WVP_IGTF_Manager')) {
            $igtf_manager = new WVP_IGTF_Manager();
            if ($igtf_manager->is_enabled()) {
                $info['igtf_amount'] = $igtf_manager->calculate_igtf($price);
                $info['total_with_igtf'] = $price + $info['igtf_amount'];
            }
        }
        
        return $info;
    }
    
    /**
     * Calcular información de envío
     */
    private function calculate_shipping_info($postcode, $city, $state) {
        // Lógica de cálculo de envío
        $shipping_zones = WC_Shipping_Zones::get_zones();
        $shipping_cost = 0;
        $estimated_days = 3;
        
        // Calcular costo basado en zona
        if ($state === 'Distrito Capital') {
            $shipping_cost = 5.00;
            $estimated_days = 1;
        } elseif (in_array($state, ['Miranda', 'Vargas', 'Aragua', 'Carabobo'])) {
            $shipping_cost = 8.00;
            $estimated_days = 2;
        } else {
            $shipping_cost = 12.00;
            $estimated_days = 3;
        }
        
        return array(
            'postcode' => $postcode,
            'city' => $city,
            'state' => $state,
            'cost' => $shipping_cost,
            'estimated_days' => $estimated_days,
            'currency' => 'USD'
        );
    }
    
    /**
     * Formatear precio en VES
     */
    private function format_ves_price($ves_price) {
        if (!$ves_price) {
            return 'Bs. 0,00';
        }
        
        $formatted = number_format($ves_price, 2, ',', '.');
        return 'Bs. ' . $formatted;
    }
    
    /**
     * Obtener configuración actual
     */
    public function get_config() {
        return $this->config;
    }
    
    /**
     * Actualizar configuración
     */
    public function update_config($new_config) {
        foreach ($new_config as $key => $value) {
            if (array_key_exists($key, $this->config)) {
                $this->config[$key] = $value;
                update_option('wvp_' . $key, $value);
            }
        }
    }
}
