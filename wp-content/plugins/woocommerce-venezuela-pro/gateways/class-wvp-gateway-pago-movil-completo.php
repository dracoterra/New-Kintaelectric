<?php
/**
 * Pasarela de pago Pago Móvil para WooCommerce Venezuela Pro - Versión Completa
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.2.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Gateway_Pago_Movil extends WC_Payment_Gateway {
    
    public $accounts;
    public $min_amount;
    public $max_amount;
    
    public function __construct() {
        $this->id = "wvp_pago_movil";
        $this->icon = "";
        $this->has_fields = true;
        $this->method_title = "Pago Móvil";
        $this->method_description = "Acepta pagos mediante Pago Móvil. Agrega múltiples cuentas bancarias.";
        
        $this->init_form_fields();
        $this->init_settings();
        
        // Soporte para Blocks
        $this->supports = array('products');
        
        $saved_title = $this->get_option("title", "");
        $this->title = !empty($saved_title) ? $saved_title : "Pago Móvil";
        
        // FORZAR título si está vacío
        if (empty($this->title)) {
            $this->title = "Pago Móvil";
        }
        
        $this->description = $this->get_option("description");
        $this->enabled = $this->get_option("enabled");
        $this->min_amount = $this->get_option("min_amount");
        $this->max_amount = $this->get_option("max_amount");
        
        $this->load_accounts();
        
        // Hooks
        add_action("woocommerce_update_options_payment_gateways_" . $this->id, array($this, "process_admin_options"));
        add_action("woocommerce_update_options_payment_gateways_" . $this->id, array($this, "save_accounts_manually"), 20);
        add_action("admin_footer", array($this, "admin_custom_fields"));
        add_filter("woocommerce_settings_api_sanitized_fields_" . $this->id, array($this, "sanitize_accounts_field"));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'), 10, 1);
        add_action('wp', array($this, 'process_confirmation_form'));
        
        // Enqueue media scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_scripts'));
    }
    
    public function save_accounts_manually() {
        // Verificar que estamos en la página correcta
        if (!isset($_GET['section']) || $_GET['section'] !== $this->id) {
            return;
        }
        
        if (isset($_POST['pago_movil_accounts'])) {
            $accounts_json = stripslashes($_POST['pago_movil_accounts']);
            
            // Intentar decodificar
            $accounts_data = json_decode($accounts_json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return;
            }
            
            if (is_array($accounts_data)) {
                // Guardar directamente en la opción del gateway usando la estructura correcta
                $option_key = $this->get_option_key();
                $current_settings = get_option($option_key, array());
                
                if (!is_array($current_settings)) {
                    $current_settings = array();
                }
                
                // Guardar como JSON string en el array de settings
                $current_settings['pago_movil_accounts'] = json_encode($accounts_data);
                
                // Actualizar la opción
                update_option($option_key, $current_settings);
            }
        }
    }
    
    public function enqueue_media_scripts($hook) {
        if ($hook === 'woocommerce_page_wc-settings' && isset($_GET['section']) && $_GET['section'] === $this->id) {
            wp_enqueue_media();
        }
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            "enabled" => array(
                "title" => __("Activar", "wvp"),
                "type" => "checkbox",
                "label" => __("Activar Pago Móvil", "wvp"),
                "default" => "no"
            ),
            "title" => array(
                "title" => __("Título", "wvp"),
                "type" => "text",
                "description" => __("Título que verá el cliente.", "wvp"),
                "default" => __("Pago Móvil", "wvp"),
                "desc_tip" => true
            ),
            "description" => array(
                "title" => __("Descripción", "wvp"),
                "type" => "textarea",
                "description" => __("Descripción que verá el cliente.", "wvp"),
                "default" => __("Pago mediante Pago Móvil.", "wvp"),
                "desc_tip" => true
            ),
            "min_amount" => array(
                "title" => __("Monto Mínimo (USD)", "wvp"),
                "type" => "price",
                "default" => "",
            ),
            "max_amount" => array(
                "title" => __("Monto Máximo (USD)", "wvp"),
                "type" => "price",
                "default" => "",
            )
        );
    }
    
    private function load_accounts() {
        $this->accounts = array();
        
        // Intentar cargar desde la opción del gateway
        $accounts_data = $this->get_option("pago_movil_accounts", '');
        
        // Si es string JSON, decodificar
        if (is_string($accounts_data) && !empty($accounts_data)) {
            $accounts_data = json_decode($accounts_data, true);
        }
        
        // Si todavía no es array, intentar desde option key directa
        if (!is_array($accounts_data)) {
            $option_key = $this->get_option_key();
            $settings = get_option($option_key, array());
            if (isset($settings['pago_movil_accounts'])) {
                $accounts_json = $settings['pago_movil_accounts'];
                if (is_string($accounts_json)) {
                    $accounts_data = json_decode($accounts_json, true);
                } elseif (is_array($accounts_json)) {
                    $accounts_data = $accounts_json;
                }
            }
        }
        
        // Cargar cuentas
        if (is_array($accounts_data)) {
            foreach ($accounts_data as $key => $account) {
                if (is_array($account) && !empty($account['name']) && !empty($account['bank']) && !empty($account['phone']) && !empty($account['ci'])) {
                    $this->accounts[] = array(
                        'id' => $key,
                        'name' => sanitize_text_field($account['name']),
                        'ci' => sanitize_text_field($account['ci']),
                        'bank' => sanitize_text_field($account['bank']),
                        'bank_name' => $this->get_bank_name($account['bank']),
                        'phone' => sanitize_text_field($account['phone']),
                        'qr_image' => isset($account['qr_image']) ? esc_url($account['qr_image']) : ''
                    );
                }
            }
        }
    }
    
    private function get_banks_list() {
        return array(
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
    }
    
    private function get_bank_name($bank_code) {
        $banks = $this->get_banks_list();
        return isset($banks[$bank_code]) ? $banks[$bank_code] : $bank_code;
    }
    
    private function get_account($account_id) {
        foreach ($this->accounts as $account) {
            if ($account['id'] == $account_id) {
                return $account;
            }
        }
        return null;
    }
    
    public function get_title() {
        $title = parent::get_title();
        if (empty($title)) {
            $title = 'Pago Móvil';
        }
        return $title;
    }
    
    public function is_available() {
        if (!$this->enabled || empty($this->accounts)) {
            return false;
        }
        
        if (!WC()->cart || WC()->cart->is_empty() || !WC()->cart->needs_payment()) {
            return false;
        }
        
        $cart_total = floatval(WC()->cart->get_total('raw'));
        
        if ($this->min_amount && $cart_total < floatval($this->min_amount)) {
            return false;
        }
        
        if ($this->max_amount && $cart_total > floatval($this->max_amount)) {
            return false;
        }
        
        return true;
    }
    
    public function payment_fields() {
        
        // Descripción simple
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
        
        // Mensaje si no hay cuentas
        if (empty($this->accounts)) {
            echo '<div style="border: 1px solid #f0ad4e; padding: 15px; margin: 15px 0; background: #fcf8e3; border-radius: 4px;">
                <p>No hay cuentas de Pago Móvil configuradas. Contacte con el administrador.</p>
            </div>';
            return;
        }
        
        // Obtener total en bolívares
        $rate = WVP_BCV_Integrator::get_rate();
        $cart_total = WC()->cart->get_total("raw");
        $ves_total = null;
        
        if ($rate !== null && $rate > 0) {
            $ves_total = WVP_BCV_Integrator::convert_to_ves($cart_total, $rate);
        } else {
            $fallback_rate = get_option('wvp_bcv_rate', null);
            if ($fallback_rate !== null && $fallback_rate > 0) {
                $rate = $fallback_rate;
                $ves_total = WVP_BCV_Integrator::convert_to_ves($cart_total, $rate);
            }
        }
        
        if ($rate !== null && $ves_total !== null) {
            echo '<div class="wvp-pago-movil-total" style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p><strong>' . __("Total a pagar:", "wvp") . '</strong> ' . WVP_BCV_Integrator::format_ves_price($ves_total) . '</p>
                <p><strong>' . __("Tasa BCV:", "wvp") . '</strong> ' . number_format($rate, 2, ",", ".") . ' Bs./USD</p>
            </div>';
        } else {
            echo '<div class="wvp-pago-movil-error" style="border: 1px solid #f0ad4e; padding: 10px; margin: 10px 0; background: #fcf8e3; border-radius: 4px;">
                <p><strong>' . __("Importante:", "wvp") . '</strong> ' . wc_price($cart_total) . ' USD</p>
            </div>';
        }
        
        // Seleccionar cuenta con radio buttons
        echo '<div class="wvp-pago-movil-accounts" style="margin: 20px 0;">
            <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 16px;">' . __("Selecciona tu banco:", "wvp") . '</label>';
        
        $account_index = 0;
        foreach ($this->accounts as $account) {
            $radio_id = 'wvp_account_' . $account_index;
            echo '<div class="wvp-account-option" style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; cursor: pointer; transition: all 0.3s;" data-account-id="' . esc_attr($account['id']) . '">';
            echo '<label style="display: flex; align-items: center; cursor: pointer; margin: 0;" for="' . $radio_id . '">';
            echo '<input type="radio" id="' . $radio_id . '" name="wvp_pago_movil_account" value="' . esc_attr($account['id']) . '" required style="margin-right: 15px; width: 20px; height: 20px;">';
            echo '<div style="flex: 1;"><strong>' . esc_html($account['bank_name']) . '</strong></div>';
            echo '</label>';
            echo '</div>';
            $account_index++;
        }
        
        echo '</div>';
        
        // Contenedor para mostrar datos bancarios seleccionados
        echo '<div id="wvp_pago_movil_account_details" style="display: none; border: 2px solid #5cb85c; padding: 20px; margin: 20px 0; background: #f9f9f9; border-radius: 5px;">
            <h4 style="margin-top: 0; color: #5cb85c;">' . __("Datos para Pago Móvil:", "wvp") . '</h4>
            <p id="wvp_account_ci"><strong>' . __("Cédula/RIF:", "wvp") . '</strong> <span class="ci-info"></span></p>
            <p id="wvp_account_name"><strong>' . __("Cuenta:", "wvp") . '</strong> <span class="account-name-info"></span></p>
            <p id="wvp_bank_name"><strong>' . __("Banco:", "wvp") . '</strong> <span class="bank-info"></span></p>
            <p id="wvp_account_phone"><strong>' . __("Teléfono:", "wvp") . '</strong> <span class="phone-info"></span></p>
        </div>';
        
        // Campo de confirmación
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent; display: none;">';
        
        do_action("woocommerce_credit_card_form_start", $this->id);
        
        echo '<div class="form-row form-row-wide">
            <label>' . __("Número de Confirmación", "wvp") . ' <span class="required">*</span></label>
            <input id="' . esc_attr($this->id) . '-confirmation" name="' . esc_attr($this->id) . '-confirmation" type="text" autocomplete="off" placeholder="' . __("Ejemplo: ABC123456", "wvp") . '" maxlength="20" />
            <small style="display: block; margin-top: 5px; color: #666;">' . __("Ingrese el número de confirmación del pago móvil", "wvp") . '</small>
        </div>';
        
        echo '<input type="hidden" id="wvp_pago_movil_selected_account_id" name="wvp_pago_movil_selected_account_id" value="">';
        
        do_action("woocommerce_credit_card_form_end", $this->id);
        
        echo '<div class="clear"></div></fieldset>';
        
        // JavaScript
        $accounts_json = array();
        foreach ($this->accounts as $account) {
            $accounts_json[$account['id']] = array(
                'ci' => $account['ci'],
                'name' => $account['name'],
                'bank_name' => $account['bank_name'],
                'phone' => $account['phone'],
                'qr_image' => $account['qr_image']
            );
        }
        
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            var accounts = ' . json_encode($accounts_json) . ';
            var gatewayId = "' . esc_js($this->id) . '";
            
            $(".wvp-account-option").on("change", "input[type=radio]", function() {
                var accountId = $(this).val();
                var account = accounts[accountId];
                
                if (account) {
                    $(".ci-info").text(account.ci);
                    $(".account-name-info").text(account.name);
                    $(".bank-info").text(account.bank_name);
                    $(".phone-info").text(account.phone);
                    
                    $("#wvp_pago_movil_account_details").show();
                    $("#wvp_pago_movil_selected_account_id").val(accountId);
                    
                    // Hacer el campo de confirmación obligatorio
                    $("#" + gatewayId + "-confirmation").prop("required", true);
                    $("#wc-" + gatewayId + "-cc-form").show();
                }
            });
            
            // Destacar opción seleccionada
            $(document).on("change", ".wvp-account-option input[type=radio]", function() {
                $(".wvp-account-option").css("border-color", "#ddd").css("background", "#fff");
                $(this).closest(".wvp-account-option").css("border-color", "#5cb85c").css("background", "#f0fff0");
            });
        });
        </script>';
    }
    
    public function validate_fields() {
        $confirmation = WVP_Security_Validator::sanitize_input($_POST[$this->id . '-confirmation'] ?? '');
        
        if (empty($confirmation)) {
            wc_add_notice(__("Debe ingresar el número de confirmación del pago móvil.", "wvp"), "error");
            return false;
        }
        
        if (!WVP_Security_Validator::validate_confirmation($confirmation)) {
            wc_add_notice(__("Número de confirmación inválido.", "wvp"), "error");
            return false;
        }
        
        $selected_account_id = $_POST['wvp_pago_movil_selected_account_id'] ?? '';
        if (empty($selected_account_id)) {
            wc_add_notice(__("Debe seleccionar un banco.", "wvp"), "error");
            return false;
        }
        
        $selected_account = $this->get_account($selected_account_id);
        if (!$selected_account) {
            wc_add_notice(__("Banco seleccionado no es válido.", "wvp"), "error");
            return false;
        }
        
        return true;
    }
    
    public function process_payment($order_id) {
        try {
            $order = wc_get_order($order_id);
            
            $confirmation = WVP_Security_Validator::sanitize_input($_POST[$this->id . '-confirmation'] ?? '');
            $selected_account_id = $_POST['wvp_pago_movil_selected_account_id'] ?? '';
            $selected_account = $this->get_account($selected_account_id);
            
            if (!$selected_account) {
                throw new Exception("Cuenta seleccionada no válida");
            }
            
            $order->update_meta_data("_payment_confirmation", $confirmation);
            $order->update_meta_data("_payment_method_title", $this->title);
            $order->update_meta_data("_payment_type", "pago_movil");
            $order->update_meta_data("_selected_account_id", $selected_account_id);
            $order->update_meta_data("_selected_account_name", $selected_account['name']);
            $order->update_meta_data("_selected_account_ci", $selected_account['ci']);
            $order->update_meta_data("_selected_account_bank", $selected_account['bank']);
            $order->update_meta_data("_selected_account_bank_name", $selected_account['bank_name']);
            $order->update_meta_data("_selected_account_phone", $selected_account['phone']);
            
            if (!empty($selected_account['qr_image'])) {
                $order->update_meta_data("_selected_account_qr_image", $selected_account['qr_image']);
            }
            
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate !== null) {
                $order->update_meta_data("_bcv_rate_at_purchase", $rate);
            }
            
            $order->update_status("on-hold", __("Pago pendiente de verificación.", "wvp"));
            $order->save();
            
            WC()->cart->empty_cart();
            
            return array(
                "result" => "success",
                "redirect" => wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url()) . '?payment_method=' . $this->id
            );
            
        } catch (Exception $e) {
            wc_add_notice(__("Error al procesar el pago.", "wvp"), "error");
            return array("result" => "fail", "redirect" => "");
        }
    }
    
    public function thankyou_page($order_id) {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_payment_method() !== $this->id) return;
        
        $account_name = $order->get_meta('_selected_account_name');
        $account_ci = $order->get_meta('_selected_account_ci');
        $account_bank = $order->get_meta('_selected_account_bank_name');
        $account_phone = $order->get_meta('_selected_account_phone');
        $account_qr_image = $order->get_meta('_selected_account_qr_image');
        $confirmation_code = $order->get_meta('_payment_confirmation');
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$account_bank || !$account_phone) return;
        
        $total_usd = $order->get_total();
        $total_ves = null;
        
        if ($bcv_rate) {
            $total_ves = $total_usd * floatval($bcv_rate);
        }
        
        echo '<div class="wvp-pago-movil-thankyou" style="background: #fff; border: 2px solid #5cb85c; padding: 30px; margin: 30px 0; border-radius: 10px;">';
        echo '<h2 style="color: #5cb85c; border-bottom: 2px solid #5cb85c; padding-bottom: 15px;">📱 ' . __("Realiza el Pago Móvil", "wvp") . '</h2>';
        
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 30px 0;">';
        
        // Datos bancarios
        echo '<div style="background: #f9f9f9; padding: 25px; border-radius: 8px;">
            <h3 style="color: #333; margin-top: 0;">🏦 ' . __("Datos para Pago Móvil", "wvp") . '</h3>
            <div style="background: #fff; border: 2px solid #5cb85c; padding: 20px; border-radius: 5px;">
                <p style="font-size: 16px; margin: 10px 0;"><strong>' . __("Cédula/RIF:", "wvp") . '</strong><br><span style="color: #5cb85c;">' . esc_html($account_ci) . '</span></p>
                <p style="font-size: 16px; margin: 10px 0;"><strong>' . __("Cuenta:", "wvp") . '</strong><br><span style="color: #5cb85c;">' . esc_html($account_name) . '</span></p>
                <p style="font-size: 16px; margin: 10px 0;"><strong>' . __("Banco:", "wvp") . '</strong><br><span style="color: #5cb85c;">' . esc_html($account_bank) . '</span></p>
                <p style="font-size: 16px; margin: 10px 0;"><strong>' . __("Teléfono:", "wvp") . '</strong><br><span style="color: #5cb85c; font-family: monospace; font-size: 18px;">' . esc_html($account_phone) . '</span></p>
            </div>
        </div>';
        
        // QR y Monto
        echo '<div style="background: #f9f9f9; padding: 25px; border-radius: 8px;">
            <h3 style="color: #333; margin-top: 0;">💰 ' . __("Monto a Pagar", "wvp") . '</h3>';
        
        if ($bcv_rate && $total_ves) {
            echo '<div style="background: #fff; border: 2px solid #f0ad4e; padding: 20px; border-radius: 5px; text-align: center; margin-bottom: 20px;">
                <p style="font-size: 24px; margin: 10px 0; color: #856404;">' . number_format($total_ves, 2, ',', '.') . ' Bs.</p>
                <p style="font-size: 14px; color: #666;">' . wc_price($total_usd) . '</p>
            </div>';
        }
        
        echo '<div style="background: #fff; padding: 20px; border-radius: 5px; text-align: center;">
            <p style="margin-bottom: 10px; font-weight: bold;">📱 ' . __("Escanea para pagar:", "wvp") . '</p>
            <div id="wvp-qr-code" style="min-height: 200px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 5px;">';
        
        if (!empty($account_qr_image)) {
            echo '<img src="' . esc_url($account_qr_image) . '" alt="QR Code" style="max-width: 250px; max-height: 250px; border: 2px solid #5cb85c; padding: 10px; background: #fff; border-radius: 10px;">';
        } else {
            echo '<p style="color: #999;">' . __("QR no disponible", "wvp") . '</p>';
        }
        
        echo '</div></div></div></div>';
        echo '</div>';
    }
    
    public function process_confirmation_form() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'wvp_confirm_payment') {
            return;
        }
        
        if (!wp_verify_nonce($_POST['wvp_payment_nonce'], 'wvp_confirm_payment')) {
            wc_add_notice(__('Error de seguridad.', 'wvp'), 'error');
            return;
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wc_add_notice(__('Pedido no encontrado.', 'wvp'), 'error');
            return;
        }
        
        $payment_from_bank = sanitize_text_field($_POST['wvp_payment_from_bank']);
        $payment_from_phone = sanitize_text_field($_POST['wvp_payment_from_phone']);
        $payment_date = sanitize_text_field($_POST['wvp_payment_date']);
        $payment_reference = sanitize_text_field($_POST['wvp_payment_reference']);
        
        if (empty($payment_from_bank) || empty($payment_from_phone) || empty($payment_date) || empty($payment_reference)) {
            wc_add_notice(__('Todos los campos son requeridos.', 'wvp'), 'error');
            return;
        }
        
        $order->update_meta_data('_payment_from_bank', $payment_from_bank);
        $order->update_meta_data('_payment_from_bank_name', $this->get_bank_name($payment_from_bank));
        $order->update_meta_data('_payment_from_phone', $payment_from_phone);
        $order->update_meta_data('_payment_date', $payment_date);
        $order->update_meta_data('_payment_reference', $payment_reference);
        
        $order->update_status('on-hold', __('Cliente confirmó el pago móvil.', 'wvp'));
        $order->save();
        
        wc_add_notice(__('¡Confirmación enviada!', 'wvp'), 'success');
        wp_safe_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
    
    public function sanitize_accounts_field($settings) {
        // Remover del filtro de sanitización ya que lo manejamos manualmente
        return $settings;
    }
    
    private function is_options_screen() {
        if (!is_admin()) return false;
        
        $screen = get_current_screen();
        if (!$screen) return false;
        
        return $screen->id === 'woocommerce_page_wc-settings' && isset($_GET['section']) && $_GET['section'] === $this->id;
    }
    
    public function admin_custom_fields() {
        if (!$this->is_options_screen()) return;
        
        $accounts_json = $this->get_option("pago_movil_accounts", '');
        $accounts = array();
        
        if (!empty($accounts_json)) {
            if (is_string($accounts_json)) {
                $accounts = json_decode($accounts_json, true);
            } elseif (is_array($accounts_json)) {
                $accounts = $accounts_json;
            }
        }
        
        if (!is_array($accounts)) {
            $accounts = array();
        }
        
        // Convertir objeto a array indexado para evitar problemas con índices numéricos
        $accounts_array = array();
        if (is_array($accounts)) {
            foreach ($accounts as $key => $account) {
                if (is_array($account)) {
                    $accounts_array[] = $account;
                }
            }
        }
        
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Evitar ejecución múltiple
            if (window.wvpAccountsInitialized) {
                console.log('WVP Accounts ya inicializado, saltando...');
                return;
            }
            window.wvpAccountsInitialized = true;
            
            var accountsData = <?php echo json_encode($accounts_array); ?>;
            var accountIndex = 0;
            
            function getBanksList() {
                return <?php echo json_encode($this->get_banks_list()); ?>;
            }
            
            function updateAccountNumbers() {
                $('#wvp-accounts-container .wvp-account-block').each(function(index) {
                    $(this).find('.account-number-display').text(index + 1);
                });
            }
            
            function createAccountBlock(accountData) {
                var banks = getBanksList();
                var banksHtml = '<select class="wvp-account-bank" style="width: 100%; padding: 8px; border: 2px solid #ddd; border-radius: 4px;">';
                for (var code in banks) {
                    var selected = (accountData && accountData.bank === code) ? 'selected' : '';
                    banksHtml += '<option value="' + code + '" ' + selected + '>' + banks[code] + '</option>';
                }
                banksHtml += '</select>';
                
                var qrImage = accountData && accountData.qr_image ? accountData.qr_image : '';
                var qrPreview = qrImage ? '<img src="' + qrImage + '" style="max-width: 200px; max-height: 200px; margin-top: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;" />' : '';
                
                var block = $('<div class="wvp-account-block" style="border: 2px solid #5cb85c; padding: 20px; margin: 15px 0; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">' +
                    '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;">' +
                        '<h3 style="margin: 0; color: #5cb85c; font-size: 18px;">📱 Cuenta #<span class="account-number-display"></span></h3>' +
                        '<button type="button" class="button button-link-delete wvp-remove-account">🗑️ Eliminar</button>' +
                    '</div>' +
                    '<table class="form-table" style="width: 100%; margin: 0;">' +
                        '<tr>' +
                            '<th scope="row" style="padding: 15px 15px 15px 0; width: 200px; vertical-align: top;"><label>Nombre *</label></th>' +
                            '<td style="padding: 15px;"><input type="text" class="wvp-account-name" style="width: 100%; padding: 8px; border: 2px solid #ddd; border-radius: 4px;" value="' + (accountData ? accountData.name : '') + '" placeholder="Mi Banco Principal" required></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row" style="padding: 15px 15px 15px 0; width: 200px; vertical-align: top;"><label>Cédula/RIF *</label></th>' +
                            '<td style="padding: 15px;"><input type="text" class="wvp-account-ci" style="width: 100%; padding: 8px; border: 2px solid #ddd; border-radius: 4px;" value="' + (accountData ? accountData.ci : '') + '" placeholder="V12345678 o J-41234567-8" required></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row" style="padding: 15px 15px 15px 0; width: 200px; vertical-align: top;"><label>Banco *</label></th>' +
                            '<td style="padding: 15px;">' + banksHtml + '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row" style="padding: 15px 15px 15px 0; width: 200px; vertical-align: top;"><label>Teléfono *</label></th>' +
                            '<td style="padding: 15px;"><input type="text" class="wvp-account-phone" style="width: 100%; padding: 8px; border: 2px solid #ddd; border-radius: 4px;" value="' + (accountData ? accountData.phone : '') + '" placeholder="04141234567" required></td>' +
                        '</tr>' +
                        '<tr>' +
                            '<th scope="row" style="padding: 15px 15px 15px 0; width: 200px; vertical-align: top;"><label>Imagen QR (Opcional)</label></th>' +
                            '<td style="padding: 15px;">' +
                                '<div class="wvp-qr-upload-wrapper">' +
                                    '<input type="hidden" class="wvp-account-qr" value="' + qrImage + '">' +
                                    '<div class="wvp-qr-upload-container" style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">' +
                                        '<button type="button" class="button wvp-upload-qr-btn">📤 Subir QR</button>' +
                                        '<button type="button" class="button button-link wvp-remove-qr-btn" ' + (qrImage ? '' : 'style="display: none;"') + '>✖️ Remover</button>' +
                                    '</div>' +
                                    '<div class="wvp-qr-preview">' + qrPreview + '</div>' +
                                '</div>' +
                            '</td>' +
                        '</tr>' +
                    '</table>' +
                    '</div>');
                
                return block;
            }
            
            setTimeout(function() {
                // Verificar si ya existe la sección para evitar duplicados
                if ($('.wvp-accounts-section-title').length > 0) {
                    console.log('La sección ya existe, saltando inicialización duplicada');
                    return;
                }
                
                // Limpiar cualquier duplicado existente
                $('.wvp-accounts-section-title').remove();
                $('.wvp-add-button-row').remove();
                $('#wvp-accounts-container').parent('tr').remove();
                
                // Crear sección de cuentas
                var sectionTitle = $('<tr class="wvp-accounts-section-title"><td colspan="2"><h2 style="margin: 20px 0 10px 0; padding: 10px; background: #f0f0f0; border-left: 4px solid #5cb85c;">🏦 ' + 'Configurar Cuentas Bancarias' + '</h2></td></tr>');
                var addButtonRow = $('<tr class="wvp-add-button-row"><td colspan="2" style="padding: 15px 0;"><button type="button" class="button button-primary wvp-add-account" style="font-size: 16px; padding: 12px 20px;">➕ Agregar Nueva Cuenta</button></td></tr>');
                var container = $('<tr><td colspan="2"><div id="wvp-accounts-container"></div></td></tr>');
                
                // Insertar después de campos generales
                var formTable = $('table.form-table');
                formTable.append(sectionTitle);
                formTable.append(addButtonRow);
                formTable.append(container);
                
                // Asegurar que el campo oculto esté dentro del form
                var hiddenField = $('#pago_movil_accounts');
                if (hiddenField.length > 0) {
                    // Mover el campo dentro del formulario si está fuera
                    var form = $('form#mainform');
                    if (hiddenField.closest(form).length === 0) {
                        hiddenField.detach().appendTo(form);
                    }
                }
                
                // Agregar cuentas existentes
                if (accountsData.length > 0) {
                    $.each(accountsData, function(index, account) {
                        $('#wvp-accounts-container').append(createAccountBlock(account));
                    });
                    updateAccountNumbers();
                } else {
                    // Si no hay cuentas, mostrar mensaje
                    $('#wvp-accounts-container').append($('<div class="notice notice-info" style="padding: 15px; margin: 15px 0; background: #e3f2fd; border-left: 4px solid #2196f3;"><p>No hay cuentas configuradas. Haz clic en "Agregar Nueva Cuenta" para comenzar.</p></div>'));
                }
                
                // Evento para agregar nueva cuenta
                $('.wvp-add-account').on('click', function() {
                    $('#wvp-accounts-container .notice').remove(); // Eliminar mensaje si existe
                    $('#wvp-accounts-container').append(createAccountBlock(null));
                    updateAccountNumbers();
                    updateAccountsField();
                });
            }, 500);
            
            function updateAccountsField() {
                var accountsArray = [];
                $('#wvp-accounts-container .wvp-account-block').each(function(index) {
                    var name = $(this).find('.wvp-account-name').val();
                    var ci = $(this).find('.wvp-account-ci').val();
                    var bank = $(this).find('.wvp-account-bank').val();
                    var phone = $(this).find('.wvp-account-phone').val();
                    var qrImage = $(this).find('.wvp-account-qr').val() || '';
                    
                    if (name && ci && bank && phone) {
                        accountsArray.push({
                            name: name,
                            ci: ci,
                            bank: bank,
                            phone: phone,
                            qr_image: qrImage
                        });
                    }
                });
                
                $('#pago_movil_accounts').val(JSON.stringify(accountsArray));
                console.log('Accounts saved:', JSON.stringify(accountsArray));
            }
            
            var mediaUploader;
            
            $(document).on('click', '.wvp-upload-qr-btn', function(e) {
                e.preventDefault();
                var button = $(this);
                var wrapper = button.closest('.wvp-qr-upload-wrapper');
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: 'Selecciona Imagen QR',
                    button: { text: 'Usar esta imagen' },
                    multiple: false,
                    library: { type: 'image' }
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    wrapper.find('.wvp-account-qr').val(attachment.url);
                    wrapper.find('.wvp-qr-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; max-height: 200px; margin-top: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;" />');
                    wrapper.find('.wvp-remove-qr-btn').show();
                    updateAccountsField();
                });
                
                mediaUploader.open();
            });
            
            $(document).on('click', '.wvp-remove-qr-btn', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.wvp-qr-upload-wrapper');
                wrapper.find('.wvp-account-qr').val('');
                wrapper.find('.wvp-qr-preview').html('');
                $(this).hide();
                updateAccountsField();
            });
            
            $(document).on('click', '.wvp-remove-account', function() {
                if (confirm('¿Eliminar esta cuenta?')) {
                    $(this).closest('.wvp-account-block').remove();
                    updateAccountNumbers();
                    updateAccountsField();
                }
            });
            
            $(document).on('change input', '.wvp-account-block input, .wvp-account-block select', function() {
                updateAccountsField();
            });
            
            setTimeout(function() {
                updateAccountsField();
            }, 600);
            
            // Asegurarse de que el campo oculto existe y está dentro del formulario
            setTimeout(function() {
                var hiddenField = $('#pago_movil_accounts');
                if (hiddenField.length === 0) {
                    console.error('Campo oculto no encontrado. Creando...');
                    $('form#mainform').append('<input type="hidden" id="pago_movil_accounts" name="pago_movil_accounts" value="<?php echo esc_attr(json_encode($accounts)); ?>">');
                }
            }, 700);
            
            // Interceptar envío del formulario para asegurar que se guarde el campo
            $('form#mainform').on('submit', function(e) {
                updateAccountsField();
                var accountsData = $('#pago_movil_accounts').val();
                console.log('Form submitting with accounts:', accountsData);
                console.log('Field exists:', $('#pago_movil_accounts').length > 0);
                console.log('Form is:', this);
                
                // Log completo de POST
                setTimeout(function() {
                    console.log('About to submit with pago_movil_accounts:', accountsData);
                }, 100);
            });
        });
        </script>
        
        <input type="hidden" id="pago_movil_accounts" name="pago_movil_accounts" value="<?php echo esc_attr(json_encode($accounts)); ?>">
        <?php
    }
}

