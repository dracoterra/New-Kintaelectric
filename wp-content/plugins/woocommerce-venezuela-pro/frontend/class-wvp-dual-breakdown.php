<?php
/**
 * Clase para mostrar desglose dual (USD y VES) en carrito y checkout
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Dual_Breakdown {
    
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
        // Cargar CSS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        
        // Hooks para el carrito
        add_filter("woocommerce_cart_item_price", array($this, "add_ves_reference_to_cart_item_price"), 10, 3);
        add_filter("woocommerce_cart_item_subtotal", array($this, "add_ves_reference_to_cart_item_subtotal"), 10, 3);
        add_filter("woocommerce_cart_subtotal", array($this, "add_ves_reference_to_cart_subtotal"), 10, 3);
        add_filter("woocommerce_cart_shipping_total", array($this, "add_ves_reference_to_shipping"), 10, 2);
        add_filter("woocommerce_cart_tax_totals", array($this, "add_ves_reference_to_tax_totals"), 10, 2);
        add_filter("woocommerce_cart_totals_order_total_html", array($this, "add_ves_reference_to_order_total"), 10, 1);
        
        // Hooks para el checkout
        add_filter("woocommerce_checkout_cart_item_quantity", array($this, "add_ves_reference_to_checkout_item"), 10, 3);
        add_filter("woocommerce_checkout_cart_item_name", array($this, "add_ves_reference_to_checkout_item_name"), 10, 3);
        
        // Hooks para totales del checkout
        add_filter("woocommerce_review_order_after_cart_contents", array($this, "add_ves_reference_to_checkout_totals"), 10, 1);
    }
    
    /**
     * Cargar estilos CSS
     */
    public function enqueue_styles() {
        // Solo cargar en páginas de WooCommerce
        if (!is_woocommerce() && !is_cart() && !is_checkout()) {
            return;
        }
        
        wp_enqueue_style(
            'wvp-dual-breakdown',
            WVP_PLUGIN_URL . 'assets/css/dual-breakdown.css',
            array(),
            WVP_VERSION
        );
    }
    
    /**
     * Verificar si el desglose dual está activado
     */
    private function is_dual_breakdown_enabled() {
        $settings = get_option('wvp_settings', array());
        return isset($settings['enable_dual_breakdown']) && $settings['enable_dual_breakdown'] === 'yes';
    }
    
    /**
     * Obtener tasa BCV actual
     */
    private function get_bcv_rate() {
        if (class_exists('WVP_BCV_Integrator')) {
            return WVP_BCV_Integrator::get_rate();
        }
        return null;
    }
    
    /**
     * Convertir precio USD a VES
     */
    private function convert_to_ves($usd_price) {
        $rate = $this->get_bcv_rate();
        if (!$rate || $rate <= 0) {
            return null;
        }
        
        return $usd_price * $rate;
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
    
    /**
     * Añadir referencia VES al precio del item en el carrito
     * 
     * @param string $price_html HTML del precio
     * @param array $cart_item Item del carrito
     * @param string $cart_item_key Clave del item
     * @return string HTML modificado
     */
    public function add_ves_reference_to_cart_item_price($price_html, $cart_item, $cart_item_key) {
        if (!$this->is_dual_breakdown_enabled()) {
            return $price_html;
        }
        
        $product = $cart_item['data'];
        $quantity = $cart_item['quantity'];
        $price = $product->get_price();
        
        if ($price > 0) {
            $ves_reference = $this->calculate_ves_reference($price);
            $price_html .= $ves_reference;
        }
        
        return $price_html;
    }
    
    /**
     * Añadir referencia VES al subtotal del item en el carrito
     * 
     * @param string $subtotal_html HTML del subtotal
     * @param array $cart_item Item del carrito
     * @param string $cart_item_key Clave del item
     * @return string HTML modificado
     */
    public function add_ves_reference_to_cart_item_subtotal($subtotal_html, $cart_item, $cart_item_key) {
        if (!$this->is_dual_breakdown_enabled()) {
            return $subtotal_html;
        }
        
        $product = $cart_item['data'];
        $quantity = $cart_item['quantity'];
        $price = $product->get_price() * $quantity;
        
        if ($price > 0) {
            $ves_reference = $this->calculate_ves_reference($price);
            $subtotal_html .= $ves_reference;
        }
        
        return $subtotal_html;
    }
    
    /**
     * Añadir referencia VES al subtotal del carrito
     * 
     * @param string $subtotal_html HTML del subtotal
     * @param WC_Cart $cart Carrito
     * @return string HTML modificado
     */
    public function add_ves_reference_to_cart_subtotal($subtotal_html, $cart) {
        if (!$this->is_dual_breakdown_enabled()) {
            return $subtotal_html;
        }
        
        $subtotal = $cart->get_subtotal();
        
        if ($subtotal > 0) {
            $ves_reference = $this->calculate_ves_reference($subtotal);
            $subtotal_html .= $ves_reference;
        }
        
        return $subtotal_html;
    }
    
    /**
     * Añadir referencia VES al envío
     * 
     * @param string $shipping_html HTML del envío
     * @param WC_Cart $cart Carrito
     * @return string HTML modificado
     */
    public function add_ves_reference_to_shipping($shipping_html, $cart) {
        $shipping_total = $cart->get_shipping_total();
        
        if ($shipping_total > 0) {
            $ves_reference = $this->calculate_ves_reference($shipping_total);
            $shipping_html .= $ves_reference;
        }
        
        return $shipping_html;
    }
    
    /**
     * Añadir referencia VES a los impuestos
     * 
     * @param array $tax_totals Totales de impuestos
     * @param WC_Cart $cart Carrito
     * @return array Totales modificados
     */
    public function add_ves_reference_to_tax_totals($tax_totals, $cart) {
        if (!empty($tax_totals)) {
            foreach ($tax_totals as $key => $tax_total) {
                if (isset($tax_total->amount) && $tax_total->amount > 0) {
                    $ves_reference = $this->calculate_ves_reference($tax_total->amount);
                    $tax_totals[$key]->formatted_amount .= $ves_reference;
                }
            }
        }
        
        return $tax_totals;
    }
    
    /**
     * Añadir referencia VES al total del pedido
     * 
     * @param string $total_html HTML del total
     * @return string HTML modificado
     */
    public function add_ves_reference_to_order_total($total_html) {
        $total = WC()->cart->get_total("raw");
        
        if ($total > 0) {
            $ves_reference = $this->calculate_ves_reference($total);
            $total_html .= $ves_reference;
        }
        
        return $total_html;
    }
    
    /**
     * Añadir referencia VES al item en el checkout
     * 
     * @param string $quantity_html HTML de la cantidad
     * @param array $cart_item Item del carrito
     * @param string $cart_item_key Clave del item
     * @return string HTML modificado
     */
    public function add_ves_reference_to_checkout_item($quantity_html, $cart_item, $cart_item_key) {
        // Esta función se mantiene para compatibilidad, pero no modifica la cantidad
        return $quantity_html;
    }
    
    /**
     * Añadir referencia VES al nombre del item en el checkout
     * 
     * @param string $name_html HTML del nombre
     * @param array $cart_item Item del carrito
     * @param string $cart_item_key Clave del item
     * @return string HTML modificado
     */
    public function add_ves_reference_to_checkout_item_name($name_html, $cart_item, $cart_item_key) {
        $product = $cart_item['data'];
        $price = $product->get_price();
        
        if ($price > 0) {
            $ves_reference = $this->calculate_ves_reference($price);
            $name_html .= $ves_reference;
        }
        
        return $name_html;
    }
    
    /**
     * Añadir referencia VES a los totales del checkout
     * 
     * @param WC_Cart $cart Carrito
     */
    public function add_ves_reference_to_checkout_totals($cart) {
        // Esta función se ejecuta después del contenido del carrito en el checkout
        // Los hooks individuales ya manejan cada línea
    }
    
    /**
     * Calcular referencia VES para un monto
     * 
     * @param float $amount Monto en USD
     * @return string HTML de la referencia VES
     */
    private function calculate_ves_reference($amount) {
        // Obtener la tasa de cambio
        $rate = $this->get_bcv_rate();
        if (!$rate || $rate <= 0) {
            return '';
        }
        
        // Calcular el precio en bolívares
        $ves_price = $this->convert_to_ves($amount);
        if (!$ves_price) {
            return '';
        }
        
        // Formatear el precio en bolívares
        $formatted_ves = $this->format_ves_price($ves_price);
        
        // Obtener el formato de referencia
        $format = get_option('wvp_price_reference_format', '(Ref. %s)');
        
        // Crear la referencia en bolívares
        $ves_reference = sprintf($format, $formatted_ves);
        
        return '<br><small class="wvp-ves-reference">' . $ves_reference . '</small>';
    }
}
