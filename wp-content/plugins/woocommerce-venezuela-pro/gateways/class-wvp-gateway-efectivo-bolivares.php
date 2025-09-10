<?php
/**
 * Pasarela de pago en Efectivo (Bolívares) para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Gateway_Efectivo_Bolivares extends WC_Payment_Gateway {
    
    /**
     * Propiedades de la pasarela
     */
    public $apply_igtf;
    
    /**
     * Constructor de la pasarela
     */
    public function __construct() {
        $this->id = "wvp_efectivo_bolivares";
        $this->icon = "";
        $this->has_fields = true;
        $this->method_title = __("Efectivo (Bolívares)", "wvp");
        $this->method_description = __("Pago en efectivo con billetes en bolívares. NO aplica IGTF.", "wvp");
        
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
                "label" => __("Activar Pago en Efectivo (Bolívares)", "wvp"),
                "default" => "no"
            ),
            "title" => array(
                "title" => __("Título", "wvp"),
                "type" => "text",
                "description" => __("Título que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Efectivo (Bolívares)", "wvp"),
                "desc_tip" => true
            ),
            "description" => array(
                "title" => __("Descripción", "wvp"),
                "type" => "textarea",
                "description" => __("Descripción que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Pago en efectivo con billetes en bolívares. NO se aplica IGTF.", "wvp"),
                "desc_tip" => true
            ),
            "apply_igtf" => array(
                "title" => __("Aplicar IGTF", "wvp"),
                "type" => "checkbox",
                "label" => __("Aplicar IGTF a esta pasarela", "wvp"),
                "default" => "no",
                "description" => __("IGTF NO se aplica a pagos en bolívares, solo a pagos en dólares en efectivo.", "wvp")
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
        
        // Obtener total en bolívares
        $rate = WVP_BCV_Integrator::get_rate();
        $cart_total = WC()->cart->get_total("raw");
        $ves_total = WVP_BCV_Integrator::convert_to_ves($cart_total, $rate);
        
        if ($rate !== null && $ves_total !== null) {
            $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_total);
            echo '<div class="wvp-efectivo-bolivares-total">
                <h4>' . __("Resumen de Pago:", "wvp") . '</h4>
                <p><strong>' . __("Total en USD:", "wvp") . '</strong> ' . wc_price($cart_total) . '</p>
                <p><strong>' . __("Total en Bolívares:", "wvp") . '</strong> ' . $formatted_ves . '</p>
                <p><strong>' . __("Tasa BCV:", "wvp") . '</strong> ' . number_format($rate, 2, ",", ".") . ' Bs./USD</p>
                <p><em>' . __("Debe pagar exactamente esta cantidad en billetes de bolívares.", "wvp") . '</em></p>
            </div>';
        } else {
            echo '<div class="wvp-efectivo-bolivares-total">
                <p><strong>' . __("Total a pagar:", "wvp") . '</strong> ' . wc_price($cart_total) . '</p>
                <p><em>' . __("No se pudo obtener la tasa de cambio. Contacte al administrador.", "wvp") . '</em></p>
            </div>';
        }
        
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
        $order->update_meta_data("_payment_type", "efectivo_bolivares");
        
        // Guardar tasa de cambio usada
        $rate = WVP_BCV_Integrator::get_rate();
        if ($rate !== null) {
            $order->update_meta_data("_bcv_rate_used", $rate);
        }
        
        // Marcar como pendiente de pago
        $order->update_status("on-hold", __("Pago en efectivo (bolívares) pendiente de verificación.", "wvp"));
        
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
