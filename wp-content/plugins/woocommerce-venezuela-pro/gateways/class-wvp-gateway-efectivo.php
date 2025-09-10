<?php
/**
 * Pasarela de pago en Efectivo (Billetes) para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Gateway_Efectivo extends WC_Payment_Gateway {
    
    /**
     * Propiedades de la pasarela
     */
    public $apply_igtf;
    
    /**
     * Constructor de la pasarela
     */
    public function __construct() {
        $this->id = "wvp_efectivo";
        $this->icon = "";
        $this->has_fields = true;
        $this->method_title = __("Efectivo (Billetes USD)", "wvp");
        $this->method_description = __("Pago en efectivo con billetes en dólares. Aplica IGTF.", "wvp");
        
        // Cargar configuraciones
        $this->init_form_fields();
        $this->init_settings();
        
        // Obtener configuraciones
        $this->title = $this->get_option("title");
        $this->description = $this->get_option("description");
        $this->enabled = $this->get_option("enabled");
        $this->apply_igtf = $this->get_option("apply_igtf");
        
        // Hooks
        add_action("woocommerce_update_options_payment_gateways_" . $this->id, array($this, "process_admin_options"));
    }
    
    /**
     * Inicializar campos del formulario
     */
    public function init_form_fields() {
        $this->form_fields = array(
            "enabled" => array(
                "title" => __("Activar/Desactivar", "wvp"),
                "type" => "checkbox",
                "label" => __("Activar Pago en Efectivo", "wvp"),
                "default" => "no"
            ),
            "title" => array(
                "title" => __("Título", "wvp"),
                "type" => "text",
                "description" => __("Título que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Efectivo (Billetes USD)", "wvp"),
                "desc_tip" => true
            ),
            "description" => array(
                "title" => __("Descripción", "wvp"),
                "type" => "textarea",
                "description" => __("Descripción que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Pago en efectivo con billetes en dólares. Se aplicará IGTF del 3%.", "wvp"),
                "desc_tip" => true
            ),
            "apply_igtf" => array(
                "title" => __("Aplicar IGTF", "wvp"),
                "type" => "checkbox",
                "label" => __("Aplicar IGTF a esta pasarela", "wvp"),
                "default" => "yes",
                "description" => __("IGTF se aplica automáticamente a pagos en efectivo (billetes).", "wvp")
            )
        );
    }
    
    /**
     * Campos de pago
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
        
        // Obtener total con IGTF
        $cart_total = WC()->cart->get_total("raw");
        $igtf_rate = 3.0; // 3% IGTF
        $igtf_amount = ($cart_total * $igtf_rate) / 100;
        $total_with_igtf = $cart_total + $igtf_amount;
        
        echo '<div class="wvp-efectivo-total">
            <h4>' . __("Resumen de Pago:", "wvp") . '</h4>
            <p><strong>' . __("Subtotal:", "wvp") . '</strong> ' . wc_price($cart_total) . '</p>
            <p><strong>' . __("IGTF (3%):", "wvp") . '</strong> ' . wc_price($igtf_amount) . '</p>
            <p><strong>' . __("Total a pagar:", "wvp") . '</strong> ' . wc_price($total_with_igtf) . '</p>
            <p><em>' . __("Debe pagar exactamente esta cantidad en billetes de dólares.", "wvp") . '</em></p>
        </div>';
        
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
        
        do_action("woocommerce_credit_card_form_start", $this->id);
        
        echo '<div class="form-row form-row-wide">
            <label>' . __("Confirmación de Pago", "wvp") . ' <span class="required">*</span></label>
            <input id="' . esc_attr($this->id) . '-confirmation" name="' . esc_attr($this->id) . '-confirmation" type="text" autocomplete="off" placeholder="' . __("Escriba CONFIRMADO", "wvp") . '" />
        </div>';
        
        do_action("woocommerce_credit_card_form_end", $this->id);
        
        echo '<div class="clear"></div>
        </fieldset>';
    }
    
    /**
     * Validar campos de pago
     */
    public function validate_fields() {
        $confirmation = $_POST[$this->id . '-confirmation'] ?? '';
        
        if (empty($confirmation)) {
            wc_add_notice(__("Debe confirmar el pago en efectivo.", "wvp"), "error");
            return false;
        }
        
        if (strtoupper($confirmation) !== "CONFIRMADO") {
            wc_add_notice(__("Debe escribir 'CONFIRMADO' para confirmar el pago.", "wvp"), "error");
            return false;
        }
        
        return true;
    }
    
    /**
     * Procesar pago
     * 
     * @param int $order_id ID del pedido
     * @return array Resultado del pago
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return array(
                "result" => "fail",
                "redirect" => ""
            );
        }
        
        // Validar campos
        if (!$this->validate_fields()) {
            return array(
                "result" => "fail",
                "redirect" => ""
            );
        }
        
        // Obtener confirmación
        $confirmation = sanitize_text_field($_POST[$this->id . '-confirmation']);
        
        // Guardar información del pago
        $order->update_meta_data("_payment_confirmation", $confirmation);
        $order->update_meta_data("_payment_method_title", $this->title);
        $order->update_meta_data("_payment_type", "efectivo_billetes");
        
        // Marcar como pendiente de pago
        $order->update_status("on-hold", __("Pago en efectivo pendiente de verificación.", "wvp"));
        
        // Reducir stock
        $order->reduce_order_stock();
        
        // Limpiar carrito
        WC()->cart->empty_cart();
        
        // Guardar pedido
        $order->save();
        
        return array(
            "result" => "success",
            "redirect" => $this->get_return_url($order)
        );
    }
}
