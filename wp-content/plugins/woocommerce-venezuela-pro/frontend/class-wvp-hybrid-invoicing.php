<?php
/**
 * Clase para facturación híbrida (Factura en VES con nota aclaratoria USD)
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Hybrid_Invoicing {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Tasa histórica actual para conversión VES
     * 
     * @var float
     */
    private $current_historical_rate;
    
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
        
        // Hooks para correos electrónicos
        add_filter("woocommerce_email_order_details", array($this, "modify_email_order_details"), 10, 4);
        add_filter("woocommerce_email_order_meta", array($this, "add_hybrid_invoicing_note"), 10, 4);
        
        // Hooks para facturas PDF (si están disponibles)
        add_action("woocommerce_invoice_created", array($this, "modify_invoice_content"), 10, 1);
        
        // Hooks para páginas de pedido
        add_action("woocommerce_order_details_after_order_table", array($this, "add_hybrid_invoicing_note_frontend"), 10, 1);
        
        // Hook para modificar totales en el admin
        add_action("woocommerce_admin_order_totals_after_total", array($this, "add_hybrid_invoicing_admin_note"), 10, 1);
    }
    
    /**
     * Cargar estilos CSS
     */
    public function enqueue_styles() {
        // Solo cargar en páginas de pedidos
        if (!is_order_received_page() && !is_view_order_page()) {
            return;
        }
        
        wp_enqueue_style(
            'wvp-hybrid-invoicing',
            WVP_PLUGIN_URL . 'assets/css/hybrid-invoicing.css',
            array(),
            WVP_VERSION
        );
    }
    
    /**
     * Verificar si la facturación híbrida está activada
     */
    private function is_hybrid_invoicing_enabled() {
        $settings = get_option('wvp_settings', array());
        return isset($settings['enable_hybrid_invoicing']) && $settings['enable_hybrid_invoicing'] === 'yes';
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
     * Modificar detalles del pedido en correos electrónicos
     * 
     * @param WC_Order $order Pedido
     * @param bool $sent_to_admin Si se envía al admin
     * @param bool $plain_text Si es texto plano
     * @param WC_Email $email Objeto del email
     */
    public function modify_email_order_details($order, $sent_to_admin, $plain_text, $email) {
        if (!$this->is_hybrid_invoicing_enabled()) {
            return;
        }
        
        // Obtener la tasa histórica del pedido
        $historical_rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$historical_rate || $historical_rate <= 0) {
            return; // No hay tasa histórica, usar precios originales
        }
        
        // Modificar los totales para mostrar en VES
        $this->modify_order_totals_for_ves($order, $historical_rate, $plain_text);
    }
    
    /**
     * Añadir nota aclaratoria de facturación híbrida
     * 
     * @param WC_Order $order Pedido
     * @param bool $sent_to_admin Si se envía al admin
     * @param bool $plain_text Si es texto plano
     * @param WC_Email $email Objeto del email
     */
    public function add_hybrid_invoicing_note($order, $sent_to_admin, $plain_text, $email) {
        // Obtener la tasa histórica del pedido
        $historical_rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$historical_rate || $historical_rate <= 0) {
            return; // No hay tasa histórica
        }
        
        // Obtener el total original en USD
        $original_total = $order->get_total();
        
        // Obtener la fecha del pedido
        $order_date = $order->get_date_created();
        $formatted_date = $order_date ? $order_date->date_i18n('d/m/Y') : date('d/m/Y');
        
        // Crear la nota aclaratoria
        $note = sprintf(
            __("Transacción procesada en Dólares (USD). Monto total pagado: $%s. Tasa de cambio aplicada BCV del día %s: 1 USD = %s Bs.", "wvp"),
            number_format($original_total, 2),
            $formatted_date,
            number_format($historical_rate, 2, ',', '.')
        );
        
        if ($plain_text) {
            echo "\n\n" . $note . "\n";
        } else {
            echo '<div style="background-color: #f8f9fa; border-left: 4px solid #0073aa; padding: 15px; margin: 20px 0; font-size: 14px; color: #333;">';
            echo '<strong>' . __("Nota Importante:", "wvp") . '</strong><br>';
            echo $note;
            echo '</div>';
        }
    }
    
    /**
     * Modificar contenido de factura PDF
     * 
     * @param WC_Order $order Pedido
     */
    public function modify_invoice_content($order) {
        // Esta función se ejecutará cuando se genere una factura PDF
        // La implementación específica dependerá del plugin de facturas utilizado
        error_log('WVP: Modificando factura PDF para pedido #' . $order->get_id());
    }
    
    /**
     * Añadir nota aclaratoria en el frontend
     * 
     * @param WC_Order $order Pedido
     */
    public function add_hybrid_invoicing_note_frontend($order) {
        // Obtener la tasa histórica del pedido
        $historical_rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$historical_rate || $historical_rate <= 0) {
            return; // No hay tasa histórica
        }
        
        // Obtener el total original en USD
        $original_total = $order->get_total();
        
        // Obtener la fecha del pedido
        $order_date = $order->get_date_created();
        $formatted_date = $order_date ? $order_date->date_i18n('d/m/Y') : date('d/m/Y');
        
        // Crear la nota aclaratoria
        $note = sprintf(
            __("Transacción procesada en Dólares (USD). Monto total pagado: $%s. Tasa de cambio aplicada BCV del día %s: 1 USD = %s Bs.", "wvp"),
            number_format($original_total, 2),
            $formatted_date,
            number_format($historical_rate, 2, ',', '.')
        );
        
        echo '<div class="wvp-hybrid-invoicing-note" style="background-color: #f8f9fa; border-left: 4px solid #0073aa; padding: 15px; margin: 20px 0; font-size: 14px; color: #333;">';
        echo '<strong>' . __("Nota Importante:", "wvp") . '</strong><br>';
        echo $note;
        echo '</div>';
    }
    
    /**
     * Modificar totales del pedido para mostrar en VES
     * 
     * @param WC_Order $order Pedido
     * @param float $historical_rate Tasa histórica
     * @param bool $plain_text Si es texto plano
     */
    private function modify_order_totals_for_ves($order, $historical_rate, $plain_text) {
        // Esta función interceptará los filtros de WooCommerce para mostrar los totales en VES
        // en lugar de USD en los correos electrónicos
        
        // Hook temporal para modificar los totales
        add_filter('woocommerce_order_formatted_line_subtotal', array($this, 'modify_line_subtotal_for_ves'), 10, 3);
        add_filter('woocommerce_order_subtotal_to_display', array($this, 'modify_subtotal_for_ves'), 10, 3);
        add_filter('woocommerce_order_shipping_to_display', array($this, 'modify_shipping_for_ves'), 10, 3);
        add_filter('woocommerce_order_tax_totals', array($this, 'modify_tax_totals_for_ves'), 10, 2);
        add_filter('woocommerce_order_total_to_display', array($this, 'modify_total_for_ves'), 10, 3);
        
        // Guardar la tasa para uso en los filtros
        $this->current_historical_rate = $historical_rate;
        
        // Añadir nota aclaratoria
        $this->add_hybrid_invoicing_note($order, false, $plain_text, null);
    }
    
    /**
     * Modificar subtotal de línea para VES
     * 
     * @param string $subtotal Subtotal formateado
     * @param WC_Order_Item $item Item del pedido
     * @param WC_Order $order Pedido
     * @return string Subtotal modificado
     */
    public function modify_line_subtotal_for_ves($subtotal, $item, $order) {
        if (!$item || !is_object($item) || !method_exists($item, 'get_subtotal')) {
            return $subtotal;
        }
        
        if (isset($this->current_historical_rate)) {
            $amount = $item->get_subtotal();
            $ves_amount = $amount * $this->current_historical_rate;
            $formatted_ves = $this->format_ves_price($ves_amount);
            return $formatted_ves;
        }
        return $subtotal;
    }
    
    /**
     * Modificar subtotal para VES
     * 
     * @param string $subtotal Subtotal formateado
     * @param WC_Order $order Pedido
     * @param string $tax_display Tipo de visualización de impuestos
     * @return string Subtotal modificado
     */
    public function modify_subtotal_for_ves($subtotal, $order, $tax_display) {
        if (!$order || !is_object($order) || !method_exists($order, 'get_subtotal')) {
            return $subtotal;
        }
        
        if (isset($this->current_historical_rate)) {
            $amount = $order->get_subtotal();
            $ves_amount = $amount * $this->current_historical_rate;
            $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_amount);
            return $formatted_ves . ' Bs.';
        }
        return $subtotal;
    }
    
    /**
     * Modificar envío para VES
     * 
     * @param string $shipping Envío formateado
     * @param WC_Order $order Pedido
     * @param string $tax_display Tipo de visualización de impuestos
     * @return string Envío modificado
     */
    public function modify_shipping_for_ves($shipping, $order, $tax_display) {
        if (!$order || !is_object($order) || !method_exists($order, 'get_shipping_total')) {
            return $shipping;
        }
        
        if (isset($this->current_historical_rate)) {
            $amount = $order->get_shipping_total();
            if ($amount > 0) {
                $ves_amount = $amount * $this->current_historical_rate;
                $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_amount);
                return $formatted_ves . ' Bs.';
            }
        }
        return $shipping;
    }
    
    /**
     * Modificar totales de impuestos para VES
     * 
     * @param array $tax_totals Totales de impuestos
     * @param WC_Order $order Pedido
     * @return array Totales modificados
     */
    public function modify_tax_totals_for_ves($tax_totals, $order) {
        if (!$order || !is_object($order)) {
            return $tax_totals;
        }
        
        if (isset($this->current_historical_rate) && !empty($tax_totals)) {
            foreach ($tax_totals as $key => $tax_total) {
                if (isset($tax_total->amount)) {
                    $ves_amount = $tax_total->amount * $this->current_historical_rate;
                    $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_amount);
                    $tax_totals[$key]->formatted_amount = $formatted_ves . ' Bs.';
                }
            }
        }
        return $tax_totals;
    }
    
    /**
     * Modificar total para VES
     * 
     * @param string $total Total formateado
     * @param WC_Order $order Pedido
     * @param string $tax_display Tipo de visualización de impuestos
     * @return string Total modificado
     */
    public function modify_total_for_ves($total, $order, $tax_display) {
        if (!$order || !is_object($order) || !method_exists($order, 'get_total')) {
            return $total;
        }
        
        if (isset($this->current_historical_rate)) {
            $amount = $order->get_total();
            $ves_amount = $amount * $this->current_historical_rate;
            $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_amount);
            return $formatted_ves . ' Bs.';
        }
        return $total;
    }
}
