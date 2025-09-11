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
    public $min_amount;
    public $max_amount;
    
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
        $this->min_amount = $this->get_option("min_amount");
        $this->max_amount = $this->get_option("max_amount");
        
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
            ),
            "min_amount" => array(
                "title" => __("Monto Mínimo (USD)", "wvp"),
                "type" => "price",
                "description" => __("Monto mínimo para activar esta pasarela", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "max_amount" => array(
                "title" => __("Monto Máximo (USD)", "wvp"),
                "type" => "price",
                "description" => __("Monto máximo para activar esta pasarela", "wvp"),
                "default" => "",
                "desc_tip" => true
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
     * Verificar si la pasarela está disponible
     */
    public function is_available() {
        if (!$this->enabled) {
            return false;
        }
        
        $cart_total = floatval(WC()->cart->get_total('raw'));
        
        // Verificar monto mínimo
        if ($this->min_amount && $cart_total < floatval($this->min_amount)) {
            return false;
        }
        
        // Verificar monto máximo
        if ($this->max_amount && $cart_total > floatval($this->max_amount)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar campos de pago
     */
    public function validate_fields() {
        // 1. Verificar nonce CSRF
        if (!WVP_Security_Validator::validate_nonce($_POST['woocommerce-process-checkout-nonce'] ?? '', 'woocommerce-process_checkout')) {
            wc_add_notice(__("Error de seguridad. Intente nuevamente.", "wvp"), "error");
            WVP_Security_Validator::log_security_event("CSRF validation failed in Zelle gateway");
            return false;
        }
        
        // 2. Verificar permisos del usuario
        if (!WVP_Security_Validator::validate_user_permissions()) {
            wc_add_notice(__("No tiene permisos para realizar esta acción.", "wvp"), "error");
            WVP_Security_Validator::log_security_event("User permission validation failed in Zelle gateway");
            return false;
        }
        
        // 3. Verificar rate limiting
        if (!WVP_Rate_Limiter::check_rate_limit(get_current_user_id(), 'payment_attempt')) {
            wc_add_notice(__("Demasiados intentos de pago. Intente más tarde.", "wvp"), "error");
            return false;
        }
        
        // 4. Sanitizar y validar datos
        $confirmation = WVP_Security_Validator::sanitize_input($_POST[$this->id . '-confirmation'] ?? '');
        
        if (!WVP_Security_Validator::validate_confirmation($confirmation)) {
            wc_add_notice(__("Número de confirmación inválido. Debe contener entre 6-20 caracteres alfanuméricos.", "wvp"), "error");
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
        try {
            // 1. Verificar permisos del usuario
            if (!WVP_Security_Validator::validate_user_permissions()) {
                wc_add_notice(__("No tiene permisos para realizar esta acción.", "wvp"), "error");
                return array("result" => "fail", "redirect" => "");
            }
            
            // 2. Obtener y validar pedido
            $order = wc_get_order($order_id);
            if (!WVP_Security_Validator::validate_order_status($order)) {
                wc_add_notice(__("Este pedido ya fue procesado o no existe.", "wvp"), "error");
                return array("result" => "fail", "redirect" => "");
            }
            
            // 3. Verificar que el pedido pertenece al usuario
            if (!WVP_Security_Validator::validate_order_ownership($order)) {
                wc_add_notice(__("No tiene permisos para procesar este pedido.", "wvp"), "error");
                return array("result" => "fail", "redirect" => "");
            }
            
            // 4. Verificar rate limiting
            if (!WVP_Rate_Limiter::check_rate_limit(get_current_user_id(), 'payment_attempt')) {
                wc_add_notice(__("Demasiados intentos de pago. Intente más tarde.", "wvp"), "error");
                return array("result" => "fail", "redirect" => "");
            }
            
            // 5. Validar campos
            if (!$this->validate_fields()) {
                return array("result" => "fail", "redirect" => "");
            }
            
            // 6. Sanitizar datos de entrada
            $confirmation = WVP_Security_Validator::sanitize_input($_POST[$this->id . '-confirmation'] ?? '');
            
            // 7. Validar monto del pedido
            $order_total = floatval($order->get_total());
            if (!WVP_Security_Validator::validate_amount($order_total, floatval($this->min_amount), floatval($this->max_amount))) {
                wc_add_notice(__("El monto del pedido no está dentro del rango permitido para esta pasarela.", "wvp"), "error");
                return array("result" => "fail", "redirect" => "");
            }
            
            // 8. Guardar información del pago
            $order->update_meta_data("_payment_confirmation", $confirmation);
            $order->update_meta_data("_payment_method_title", $this->title);
            $order->update_meta_data("_payment_type", "zelle");
            $order->update_meta_data("_payment_gateway_id", $this->id);
            
            // 9. Guardar tasa BCV utilizada
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate !== null) {
                $order->update_meta_data("_bcv_rate_at_purchase", $rate);
            }
            
            // 10. Marcar como pendiente de pago
            $order->update_status("on-hold", __("Pago pendiente de verificación.", "wvp"));
            $order->add_order_note(__("Pago pendiente de verificación.", "wvp"), false, true);
            
            // 11. Reducir stock
            $order->reduce_order_stock();
            
            // 12. Limpiar carrito
            WC()->cart->empty_cart();
            
            // 13. Guardar pedido
            $order->save();
            
            // 14. Limpiar rate limit en caso de éxito
            WVP_Rate_Limiter::clear_rate_limit(get_current_user_id(), 'payment_attempt');
            
            // 15. Log de éxito
            WVP_Security_Validator::log_security_event("Payment processed successfully", array(
                'order_id' => $order_id,
                'gateway' => 'zelle',
                'user_id' => get_current_user_id()
            ));
            
            return array(
                "result" => "success",
                "redirect" => $this->get_return_url($order)
            );
            
        } catch (Exception $e) {
            // Log del error
            WVP_Security_Validator::log_security_event("Payment processing error: " . $e->getMessage(), array(
                'order_id' => $order_id,
                'gateway' => 'zelle',
                'user_id' => get_current_user_id()
            ));
            
            wc_add_notice(__("Error al procesar el pago. Intente nuevamente.", "wvp"), "error");
            return array("result" => "fail", "redirect" => "");
        }
    }
}