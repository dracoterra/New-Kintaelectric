<?php
/**
 * Pasarela de pago Pago Móvil para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Gateway_Pago_Movil extends WC_Payment_Gateway {
    
    /**
     * Propiedades de la pasarela
     */
    public $ci;
    public $phone;
    public $bank;
    public $apply_igtf;
    
    /**
     * Constructor de la pasarela
     */
    public function __construct() {
        $this->id = "wvp_pago_movil";
        $this->icon = "";
        $this->has_fields = true;
        $this->method_title = __("Pago Móvil (Venezuela)", "wvp");
        $this->method_description = __("Pago mediante Pago Móvil para clientes venezolanos.", "wvp");
        
        // Cargar configuraciones
        $this->init_form_fields();
        $this->init_settings();
        
        // Obtener configuraciones
        $this->title = $this->get_option("title");
        $this->description = $this->get_option("description");
        $this->enabled = $this->get_option("enabled");
        $this->ci = $this->get_option("ci");
        $this->phone = $this->get_option("phone");
        $this->bank = $this->get_option("bank");
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
                "label" => __("Activar Pago Móvil", "wvp"),
                "default" => "no"
            ),
            "title" => array(
                "title" => __("Título", "wvp"),
                "type" => "text",
                "description" => __("Título que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Pago Móvil", "wvp"),
                "desc_tip" => true
            ),
            "description" => array(
                "title" => __("Descripción", "wvp"),
                "type" => "textarea",
                "description" => __("Descripción que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Pago mediante Pago Móvil. Debe incluir el número de confirmación.", "wvp"),
                "desc_tip" => true
            ),
            "ci" => array(
                "title" => __("Cédula de Identidad", "wvp"),
                "type" => "text",
                "description" => __("Cédula de identidad del titular de la cuenta.", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "phone" => array(
                "title" => __("Teléfono", "wvp"),
                "type" => "text",
                "description" => __("Número de teléfono registrado en Pago Móvil.", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "bank" => array(
                "title" => __("Banco", "wvp"),
                "type" => "select",
                "description" => __("Banco para Pago Móvil.", "wvp"),
                "default" => "",
                "options" => array(
                    "0102" => "Banco de Venezuela",
                    "0104" => "Venezolano de Crédito",
                    "0105" => "Mercantil",
                    "0108" => "Provincial",
                    "0114" => "Bancaribe",
                    "0115" => "Exterior",
                    "0116" => "Occidental de Descuento",
                    "0128" => "Banco Caroní",
                    "0134" => "Banesco",
                    "0137" => "Sofitasa",
                    "0138" => "Banco Plaza",
                    "0146" => "Banco de Venezuela",
                    "0151" => "100% Banco",
                    "0156" => "100% Banco",
                    "0157" => "Del Sur",
                    "0163" => "Banco del Tesoro",
                    "0166" => "Banco Agrícola de Venezuela",
                    "0168" => "Bancrecer",
                    "0169" => "Mi Banco",
                    "0171" => "Banco Activo",
                    "0172" => "Bancamiga",
                    "0173" => "Banco Internacional de Desarrollo",
                    "0174" => "Banplus",
                    "0175" => "Bicentenario del Pueblo",
                    "0176" => "Banco Espirito Santo",
                    "0177" => "Banco de la Fuerza Armada Nacional Bolivariana",
                    "0190" => "Citibank"
                ),
                "desc_tip" => true
            ),
            "apply_igtf" => array(
                "title" => __("Aplicar IGTF", "wvp"),
                "type" => "checkbox",
                "label" => __("Aplicar IGTF a esta pasarela", "wvp"),
                "default" => "no",
                "description" => __("IGTF solo se aplica a pagos en efectivo (billetes). Las transferencias digitales como Pago Móvil NO aplican IGTF.", "wvp")
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
            echo '<div class="wvp-pago-movil-total">
                <p><strong>' . __("Total a pagar:", "wvp") . '</strong> ' . $formatted_ves . '</p>
                <p><strong>' . __("Tasa BCV:", "wvp") . '</strong> ' . number_format($rate, 2, ",", ".") . ' Bs./USD</p>
            </div>';
        }
        
        // Mostrar datos bancarios
        if (!empty($this->ci) && !empty($this->phone) && !empty($this->bank)) {
            echo '<div class="wvp-pago-movil-details">
                <h4>' . __("Datos para Pago Móvil:", "wvp") . '</h4>
                <p><strong>' . __("Cédula:", "wvp") . '</strong> ' . esc_html($this->ci) . '</p>
                <p><strong>' . __("Teléfono:", "wvp") . '</strong> ' . esc_html($this->phone) . '</p>
                <p><strong>' . __("Banco:", "wvp") . '</strong> ' . esc_html($this->get_option("bank")) . '</p>
            </div>';
        }
        
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
        
        do_action("woocommerce_credit_card_form_start", $this->id);
        
        echo '<div class="form-row form-row-wide">
            <label>' . __("Número de Confirmación", "wvp") . ' <span class="required">*</span></label>
            <input id="' . esc_attr($this->id) . '-confirmation" name="' . esc_attr($this->id) . '-confirmation" type="text" autocomplete="off" />
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
            wc_add_notice(__("El número de confirmación es obligatorio.", "wvp"), "error");
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
        
        // Obtener número de confirmación
        $confirmation = sanitize_text_field($_POST[$this->id . '-confirmation']);
        
        // Guardar información del pago
        $order->update_meta_data("_payment_confirmation", $confirmation);
        $order->update_meta_data("_payment_method_title", $this->title);
        
        // Marcar como pendiente de pago
        $order->update_status("on-hold", __("Pago pendiente de verificación.", "wvp"));
        
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