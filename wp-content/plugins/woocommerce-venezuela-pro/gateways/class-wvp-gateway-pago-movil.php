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
    public $min_amount;
    public $max_amount;
    
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
     * Obtener nombre del banco desde su código
     * 
     * @param string $bank_code Código del banco
     * @return string Nombre del banco
     */
    private function get_bank_name($bank_code) {
        $banks = array(
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
        );
        
        return isset($banks[$bank_code]) ? $banks[$bank_code] : $bank_code;
    }
    
    /**
     * Campos de pago
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
        
        // Obtener total en bolívares con manejo de errores
        $rate = WVP_BCV_Integrator::get_rate();
        $cart_total = WC()->cart->get_total("raw");
        $ves_total = null;
        $show_rate_error = false;
        
        // Intentar obtener la tasa con fallback
        if ($rate !== null && $rate > 0) {
            $ves_total = WVP_BCV_Integrator::convert_to_ves($cart_total, $rate);
        }
        
        // Si no hay tasa disponible, intentar fallback
        if ($ves_total === null) {
            // Intentar obtener tasa de respaldo desde opciones
            $fallback_rate = get_option('wvp_bcv_rate', null);
            if ($fallback_rate !== null && $fallback_rate > 0) {
                $rate = $fallback_rate;
                $ves_total = WVP_BCV_Integrator::convert_to_ves($cart_total, $rate);
                $show_rate_error = true;
            }
        }
        
        // Mostrar información de pago
        if ($rate !== null && $ves_total !== null) {
            $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_total);
            echo '<div class="wvp-pago-movil-total">
                <p><strong>' . __("Total a pagar:", "wvp") . '</strong> ' . $formatted_ves . '</p>
                <p><strong>' . __("Tasa BCV:", "wvp") . '</strong> ' . number_format($rate, 2, ",", ".") . ' Bs./USD</p>';
            
            if ($show_rate_error) {
                echo '<p style="color: orange;"><em>' . __("Nota: Estamos usando una tasa de respaldo. Contacte con el administrador para actualizar la tasa del BCV.", "wvp") . '</em></p>';
            }
            
            echo '</div>';
        } else {
            // No hay tasa disponible
            echo '<div class="wvp-pago-movil-error" style="border: 1px solid #f0ad4e; padding: 10px; margin: 10px 0; background: #fcf8e3; border-radius: 4px;">
                <p><strong>' . __("Importante:", "wvp") . '</strong></p>
                <p>' . __("No podemos calcular el monto en bolívares en este momento. El total aproximado es:", "wvp") . ' ' . wc_price($cart_total) . ' USD</p>
                <p>' . __("Le enviaremos los datos de pago completos después de realizar el pedido.", "wvp") . '</p>
            </div>';
        }
        
        // Mostrar datos bancarios
        if (!empty($this->ci) && !empty($this->phone) && !empty($this->bank)) {
            $bank_name = $this->get_bank_name($this->bank);
            echo '<div class="wvp-pago-movil-details" style="border: 2px solid #5cb85c; padding: 15px; margin: 15px 0; background: #f9f9f9; border-radius: 5px;">
                <h4 style="margin-top: 0; color: #5cb85c;">' . __("Datos para Pago Móvil:", "wvp") . '</h4>
                <p><strong>' . __("Cédula:", "wvp") . '</strong> ' . esc_html($this->ci) . '</p>
                <p><strong>' . __("Teléfono:", "wvp") . '</strong> ' . esc_html($this->phone) . '</p>
                <p><strong>' . __("Banco:", "wvp") . '</strong> ' . esc_html($bank_name) . '</p>
                <p style="color: #d9534f; font-weight: bold;">' . __("IMPORTANTE: Guarde el número de confirmación después de realizar el pago.", "wvp") . '</p>
            </div>';
        } else {
            echo '<div class="wvp-pago-movil-warning" style="border: 1px solid #f0ad4e; padding: 10px; margin: 10px 0; background: #fcf8e3; border-radius: 4px;">
                <p>' . __("Los datos bancarios no están configurados. Contacte con el administrador.", "wvp") . '</p>
            </div>';
        }
        
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
        
        do_action("woocommerce_credit_card_form_start", $this->id);
        
        echo '<div class="form-row form-row-wide">
            <label>' . __("Número de Confirmación", "wvp") . ' <span class="required">*</span></label>
            <input id="' . esc_attr($this->id) . '-confirmation" name="' . esc_attr($this->id) . '-confirmation" type="text" autocomplete="off" placeholder="' . __("Ejemplo: ABC123456", "wvp") . '" required="required" maxlength="20" pattern="[A-Za-z0-9\-]{6,20}" title="' . __("Mínimo 6 caracteres alfanuméricos", "wvp") . '" />
            <small style="display: block; margin-top: 5px; color: #666;">' . __("Ingrese el número de confirmación que recibió después de realizar el pago móvil (6-20 caracteres alfanuméricos)", "wvp") . '</small>
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
            WVP_Security_Validator::log_security_event("CSRF validation failed in Pago Móvil gateway");
            return false;
        }
        
        // 2. Verificar permisos del usuario
        if (!WVP_Security_Validator::validate_user_permissions()) {
            wc_add_notice(__("No tiene permisos para realizar esta acción.", "wvp"), "error");
            WVP_Security_Validator::log_security_event("User permission validation failed in Pago Móvil gateway");
            return false;
        }
        
        // 3. Verificar rate limiting
        if (!WVP_Rate_Limiter::check_rate_limit(get_current_user_id(), 'payment_attempt')) {
            wc_add_notice(__("Demasiados intentos de pago. Intente más tarde.", "wvp"), "error");
            return false;
        }
        
        // 4. Sanitizar y validar datos
        $confirmation = WVP_Security_Validator::sanitize_input($_POST[$this->id . '-confirmation'] ?? '');
        
        // Verificar que no esté vacío
        if (empty($confirmation)) {
            wc_add_notice(__("Debe ingresar el número de confirmación del pago móvil.", "wvp"), "error");
            return false;
        }
        
        // Validar formato
        if (!WVP_Security_Validator::validate_confirmation($confirmation)) {
            wc_add_notice(__("Número de confirmación inválido. Debe tener entre 6-20 caracteres alfanuméricos (letras y/o números). Ejemplo: ABC123456 o 0123456789", "wvp"), "error");
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
            $order->update_meta_data("_payment_type", "pago_movil");
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
                'gateway' => 'pago_movil',
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
                'gateway' => 'pago_movil',
                'user_id' => get_current_user_id()
            ));
            
            wc_add_notice(__("Error al procesar el pago. Intente nuevamente.", "wvp"), "error");
            return array("result" => "fail", "redirect" => "");
        }
    }
}