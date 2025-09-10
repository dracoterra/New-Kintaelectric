<?php
/**
 * Pasarela de pago Zelle para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Gateway_Zelle extends WC_Payment_Gateway {
    
    /**
     * Propiedades de la pasarela
     */
    public $zelle_email;
    public $apply_igtf;
    
    /**
     * Constructor de la pasarela
     */
    public function __construct() {
        $this->id = "wvp_zelle";
        $this->icon = "";
        $this->has_fields = true;
        $this->method_title = __("Zelle (Venezuela)", "wvp");
        $this->method_description = __("Pago mediante Zelle para clientes venezolanos.", "wvp");
        
        // Cargar configuraciones
        $this->init_form_fields();
        $this->init_settings();
        
        // Obtener configuraciones
        $this->title = $this->get_option("title");
        $this->description = $this->get_option("description");
        $this->enabled = $this->get_option("enabled");
        $this->zelle_email = $this->get_option("zelle_email");
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
                "label" => __("Activar Zelle", "wvp"),
                "default" => "no"
            ),
            "title" => array(
                "title" => __("Título", "wvp"),
                "type" => "text",
                "description" => __("Título que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Zelle", "wvp"),
                "desc_tip" => true
            ),
            "description" => array(
                "title" => __("Descripción", "wvp"),
                "type" => "textarea",
                "description" => __("Descripción que verá el cliente durante el checkout.", "wvp"),
                "default" => __("Pago mediante Zelle. Debe incluir el número de confirmación.", "wvp"),
                "desc_tip" => true
            ),
            "zelle_email" => array(
                "title" => __("Email de Zelle", "wvp"),
                "type" => "email",
                "description" => __("Email de Zelle donde recibir los pagos.", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "apply_igtf" => array(
                "title" => __("Aplicar IGTF", "wvp"),
                "type" => "checkbox",
                "label" => __("Aplicar IGTF a esta pasarela", "wvp"),
                "default" => "no",
                "description" => __("IGTF solo se aplica a pagos en efectivo (billetes). Las transferencias digitales como Zelle NO aplican IGTF.", "wvp")
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