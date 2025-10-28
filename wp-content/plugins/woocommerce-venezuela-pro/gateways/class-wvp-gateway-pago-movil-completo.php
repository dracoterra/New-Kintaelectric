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
        
        // Hooks para mostrar información en la página de agradecimiento
        // Usar múltiples hooks para asegurar que se muestre
        add_action('woocommerce_thankyou', array($this, 'display_payment_info_in_thankyou'), 10, 1);
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_payment_info_in_thankyou'), 5, 1);
        add_action('woocommerce_order_details_before_order_table', array($this, 'display_payment_info_in_thankyou'), 5, 1);
        
        // Filtro para inyectar en el contenido si es página de agradecimiento
        add_filter('the_content', array($this, 'maybe_inject_payment_info'), 99, 1);
        
        // AJAX para procesar confirmación
        add_action('wp_ajax_wvp_confirm_payment', array($this, 'ajax_process_confirmation'));
        add_action('wp_ajax_nopriv_wvp_confirm_payment', array($this, 'ajax_process_confirmation'));
        
        // Enqueue media scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_scripts'));
        
        // Enqueue CSS para pago móvil en frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_pago_movil_styles'));
    }
    
    /**
     * Registrar estilos CSS para pago móvil
     */
    public function enqueue_pago_movil_styles() {
        wp_enqueue_style(
            'wvp-pago-movil-style',
            WVP_PLUGIN_URL . 'assets/css/pago-movil.css',
            array(),
            WVP_VERSION
        );
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
                
                // Invalidar cache de cuentas
                delete_transient('wvp_accounts_' . $this->id);
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
        // Usar cache con timeout de 1 hora
        $cache_key = 'wvp_accounts_' . $this->id;
        $cached_accounts = get_transient($cache_key);
        
        if ($cached_accounts !== false) {
            $this->accounts = $cached_accounts;
            return;
        }
        
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
        
        // Cargar y validar cuentas
        if (is_array($accounts_data)) {
            foreach ($accounts_data as $key => $account) {
                if (!is_array($account)) {
                    continue;
                }
                
                // Aceptar cuenta si tiene al menos 'bank' definido
                if (isset($account['bank'])) {
                    // Crear un ID único basado en el índice del array (no usar 0)
                    $account_id = is_numeric($key) ? ($key + 1) : $key;
                    
                    $this->accounts[] = array(
                        'id' => $account_id,
                        'name' => !empty($account['name']) ? sanitize_text_field($account['name']) : '',
                        'ci' => !empty($account['ci']) ? sanitize_text_field($account['ci']) : '',
                        'bank' => sanitize_text_field($account['bank']),
                        'bank_name' => $this->get_bank_name($account['bank']),
                        'phone' => !empty($account['phone']) ? $this->format_venezuelan_phone($account['phone']) : '',
                        'qr_image' => isset($account['qr_image']) ? esc_url($account['qr_image']) : ''
                    );
                }
            }
        }
        
        // Guardar en cache por 1 hora
        set_transient($cache_key, $this->accounts, HOUR_IN_SECONDS);
    }
    
    /**
     * Verificar si los datos de una cuenta son válidos
     * 
     * @param array $account Datos de la cuenta
     * @return bool True si es válido
     */
    private function is_valid_account_data($account) {
        if (!is_array($account)) {
            return false;
        }
        
        // Verificar campos obligatorios
        if (empty($account['name']) || empty($account['bank']) || empty($account['phone']) || empty($account['ci'])) {
            return false;
        }
        
        // Validar formato de CI/RIF
        if (!$this->validate_venezuelan_id($account['ci'])) {
            return false;
        }
        
        // Validar formato de teléfono
        if (!$this->validate_venezuelan_phone($account['phone'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Formatear teléfono móvil venezolano
     * 
     * @param string $phone Teléfono a formatear
     * @return string Teléfono formateado
     */
    private function format_venezuelan_phone($phone) {
        // Limpiar caracteres no numéricos
        $clean = preg_replace('/[^0-9]/', '', $phone);
        
        // Formatear como 04XXYYYYYYY
        if (strlen($clean) >= 10) {
            return $clean;
        }
        
        return $phone;
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
    
    /**
     * Validar formato de CI/RIF venezolano
     * 
     * @param string $ci Cédula/RIF a validar
     * @return bool True si es válido
     */
    private function validate_venezuelan_id($ci) {
        if (empty($ci)) {
            return false;
        }
        
        // Formato: V12345678, J-41234567-8, E-41234567-8, G-41234567-8, P-41234567-8
        $pattern = '/^(V|J-|E-|G-|P-)(\d{1,9})(-)?(\d{1})?$/i';
        return preg_match($pattern, trim($ci)) !== false;
    }
    
    /**
     * Validar formato de teléfono móvil venezolano
     * 
     * @param string $phone Teléfono a validar
     * @return bool True si es válido
     */
    private function validate_venezuelan_phone($phone) {
        if (empty($phone)) {
            return false;
        }
        
        // Limpiar caracteres no numéricos
        $clean = preg_replace('/[^0-9]/', '', $phone);
        
        // Formato venezolano: 04XXYYYYYYY o 04XXYYYYYYYY (10-11 dígitos)
        // Debe empezar con 04 o 042, 041, 042, 043, 044
        return preg_match('/^0[1-4]\d{8,9}$/', $clean) !== false;
    }
    
    /**
     * Formatear CI/RIF venezolano
     * 
     * @param string $ci Cédula/RIF a formatear
     * @return string CI/RIF formateado
     */
    private function format_venezuelan_id($ci) {
        // Limpiar entrada
        $ci = trim(str_replace('-', '', str_replace(' ', '', $ci)));
        
        // Detecta tipo y formatea
        if (preg_match('/^(V|v)(\d+)$/', $ci, $matches)) {
            return 'V' . $matches[2];
        }
        
        if (preg_match('/^(J|E|G|P|j|e|g|p)(\d+)$/', $ci, $matches)) {
            return strtoupper($matches[1]) . '-' . $matches[2];
        }
        
        return $ci;
    }
    
    /**
     * Obtener tasa BCV con sistema de fallback
     * 
     * @return float|null Tasa de cambio
     */
    private function get_rate_with_fallback() {
        // 1. Intentar obtener tasa actual de BCV
        $rate = WVP_BCV_Integrator::get_rate();
        
        // 2. Si no hay tasa, intentar última tasa conocida
        if ($rate === null || $rate <= 0) {
            $rate = get_option('wvp_bcv_last_rate', null);
        }
        
        // 3. Si todavía no hay, usar tasa de emergencia configurada
        if ($rate === null || $rate <= 0) {
            $rate = get_option('wvp_bcv_emergency_rate', null);
        }
        
        // 4. Registrar si estamos usando fallback
        if ($rate !== null && $rate > 0) {
            $current_bcv_rate = WVP_BCV_Integrator::get_rate();
            if ($current_bcv_rate === null) {
                error_log('WVP Pago Móvil: Usando tasa de fallback: ' . $rate);
            }
        }
        
        return $rate;
    }
    
    /**
     * Verificar rate limiting para confirmaciones
     * 
     * @param int $order_id ID del pedido
     * @return bool True si está dentro del límite
     */
    private function check_rate_limit($order_id) {
        $transient_key = 'wvp_payment_confirmation_' . $order_id;
        $attempts = get_transient($transient_key);
        
        if ($attempts === false) {
            set_transient($transient_key, 1, 300); // 5 minutos
            return true;
        }
        
        if ($attempts >= 5) {
            wc_add_notice(
                __('Demasiados intentos de confirmación. Por favor espere 5 minutos.', 'wvp'),
                'error'
            );
            return false;
        }
        
        set_transient($transient_key, $attempts + 1, 300);
        return true;
    }
    
    /**
     * Validar código de confirmación según banco
     * 
     * @param string $confirmation Código de confirmación
     * @param string $bank_code Código del banco
     * @return bool True si es válido
     */
    private function validate_confirmation_by_bank($confirmation, $bank_code) {
        if (empty($confirmation)) {
            return false;
        }
        
        // Banco de Venezuela usa 10 dígitos
        if ($bank_code === '0102' || $bank_code === '0146') {
            return preg_match('/^\d{10}$/', $confirmation);
        }
        
        // Banesco usa 8 caracteres alfanuméricos
        if ($bank_code === '0134') {
            return preg_match('/^[A-Z0-9]{8}$/i', $confirmation);
        }
        
        // Mercantil usa 12 caracteres
        if ($bank_code === '0105') {
            return preg_match('/^[A-Z0-9]{12}$/i', $confirmation);
        }
        
        // Por defecto, usar validación genérica
        return $this->validate_confirmation_generic($confirmation);
    }
    
    /**
     * Validación genérica de código de confirmación
     * 
     * @param string $confirmation Código de confirmación
     * @return bool True si es válido
     */
    private function validate_confirmation_generic($confirmation) {
        if (empty($confirmation)) {
            return false;
        }
        
        $confirmation = trim($confirmation);
        
        // Pago Móvil usa códigos de 8-12 caracteres alfanuméricos
        $pattern = '/^[A-Z0-9]{8,12}$/i';
        
        if (!preg_match($pattern, $confirmation)) {
            return false;
        }
        
        // No permitir códigos obviamente inválidos
        $invalid_codes = array('00000000', '12345678', 'TEST1234', 'ABCDEFGH');
        if (in_array(strtoupper($confirmation), $invalid_codes)) {
            return false;
        }
        
        return true;
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
        
        // DEBUG: Ver cuántas cuentas hay
        $accounts_count = is_array($this->accounts) ? count($this->accounts) : 0;
        
        // DEBUG TEMPORAL - Ver qué hay en $this->accounts
        if ($accounts_count === 0) {
            echo '<div style="border: 1px solid #f0ad4e; padding: 15px; margin: 15px 0; background: #fcf8e3; border-radius: 4px;">
                <p><strong>⚠️ No hay cuentas de Pago Móvil configuradas.</strong></p>
                <p>Por favor, agrega al menos una cuenta bancaria en la configuración de Pago Móvil.</p>
                <p style="font-size: 12px; color: #666;">Debug: Se encontraron ' . $accounts_count . ' cuentas. Tipo de dato: ' . gettype($this->accounts) . '</p>';
            
            // Intentar cargar cuentas directamente
            $accounts_data = $this->get_option("pago_movil_accounts", '');
            if (!empty($accounts_data)) {
                if (is_string($accounts_data)) {
                    $decoded = json_decode($accounts_data, true);
                    echo '<p style="font-size: 12px; color: #666;">Cuentas en option (string): ' . (is_array($decoded) ? count($decoded) : 0) . '</p>';
                } else {
                    echo '<p style="font-size: 12px; color: #666;">Cuentas en option (tipo): ' . gettype($accounts_data) . '</p>';
                }
            } else {
                echo '<p style="font-size: 12px; color: #666;">No hay cuentas en la opción pago_movil_accounts.</p>';
            }
            
            echo '</div>';
            return;
        }
        
        // Obtener total en bolívares con sistema de fallback mejorado
        $rate = $this->get_rate_with_fallback();
        $cart_total = WC()->cart->get_total("raw");
        $ves_total = null;
        
        if ($rate !== null && $rate > 0) {
                $ves_total = WVP_BCV_Integrator::convert_to_ves($cart_total, $rate);
        }
        
        if ($rate !== null && $ves_total !== null) {
            echo '<div class="wvp-pago-movil-total" style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p><strong>' . __("Total a pagar:", "wvp") . '</strong> ' . WVP_BCV_Integrator::format_ves_price($ves_total) . '</p>
                <p><strong>' . __("Tasa BCV:", "wvp") . '</strong> ' . number_format($rate, 2, ",", ".") . ' Bs./USD</p>';
            
            // Advertir si estamos usando tasa de fallback
            $current_bcv_rate = WVP_BCV_Integrator::get_rate();
            if ($current_bcv_rate === null) {
                echo '<p style="color: #856404; font-size: 12px; margin-top: 5px;">⚠️ ' . __("Usando tasa de respaldo", "wvp") . '</p>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="wvp-pago-movil-error" style="border: 1px solid #f0ad4e; padding: 10px; margin: 10px 0; background: #fcf8e3; border-radius: 4px;">
                <p><strong>' . __("Importante:", "wvp") . '</strong> ' . wc_price($cart_total) . ' USD</p>
                <p style="font-size: 12px; margin-top: 5px;">⚠️ ' . __("Tasa BCV no disponible temporalmente", "wvp") . '</p>
            </div>';
        }
        
        // Seleccionar cuenta con radio buttons (SOLO LISTA DE BANCOS)
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
        echo '<input type="hidden" id="wvp_pago_movil_selected_account_id" name="wvp_pago_movil_selected_account_id" value="">';
        
        // JavaScript simplificado - solo para guardar el banco seleccionado
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            // Guardar banco seleccionado
            $(".wvp-account-option").on("change", "input[type=radio]", function() {
                var accountId = $(this).val();
                    $("#wvp_pago_movil_selected_account_id").val(accountId);
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
        // En Blocks checkout, validate_fields() puede no ejecutarse
        // La validación se hará en process_payment()
        return true;
    }
    
    public function process_payment($order_id) {
        try {
            $order = wc_get_order($order_id);
            
            if (!$order) {
                throw new Exception("Pedido inválido");
            }
            
            // Verificar que el usuario tiene permisos para esta orden
            $user_id = get_current_user_id();
            if ($user_id > 0 && $order->get_user_id() !== $user_id) {
                throw new Exception("No tienes permisos para este pedido");
            }
            
            // Obtener banco seleccionado - intentar desde cualquier fuente
            $selected_account_id = '';
            
            // Para Blocks checkout - SANITIZAR INPUT
            if (isset($_POST['wvp_pago_movil_selected_account_id_blocks']) && !empty($_POST['wvp_pago_movil_selected_account_id_blocks'])) {
                $selected_account_id = sanitize_text_field($_POST['wvp_pago_movil_selected_account_id_blocks']);
            }
            
            // Para checkout clásico - SANITIZAR INPUT
            if (empty($selected_account_id) && isset($_POST['wvp_pago_movil_selected_account_id']) && !empty($_POST['wvp_pago_movil_selected_account_id'])) {
                $selected_account_id = sanitize_text_field($_POST['wvp_pago_movil_selected_account_id']);
            }
            
            // Si no hay banco seleccionado, usar el primero por defecto
            if (empty($selected_account_id) && !empty($this->accounts)) {
                $first_account = reset($this->accounts);
                $selected_account_id = $first_account['id'];
            }
            
            // Validar que el account_id sea seguro
            $selected_account_id = absint($selected_account_id);
            
            if (empty($selected_account_id)) {
                throw new Exception("Banco seleccionado no válido");
            }
            
            $selected_account = $this->get_account($selected_account_id);
            
            if (!$selected_account || !is_array($selected_account)) {
                throw new Exception("Banco seleccionado no válido");
            }
            
            // Guardar datos del banco en el pedido - SANITIZAR TODO
            $order->update_meta_data("_payment_type", sanitize_text_field("pago_movil"));
            $order->update_meta_data("_selected_account_id", absint($selected_account_id));
            $order->update_meta_data("_selected_account_name", sanitize_text_field($selected_account['name'] ?? ''));
            $order->update_meta_data("_selected_account_ci", sanitize_text_field($selected_account['ci'] ?? ''));
            $order->update_meta_data("_selected_account_bank", sanitize_text_field($selected_account['bank'] ?? ''));
            $order->update_meta_data("_selected_account_bank_name", sanitize_text_field($selected_account['bank_name'] ?? ''));
            $order->update_meta_data("_selected_account_phone", sanitize_text_field($selected_account['phone'] ?? ''));
            
            if (!empty($selected_account['qr_image'])) {
                $order->update_meta_data("_selected_account_qr_image", esc_url_raw($selected_account['qr_image']));
            }
            
            // Guardar tasa BCV
            $rate = $this->get_rate_with_fallback();
            if ($rate !== null && $rate > 0) {
                $order->update_meta_data("_bcv_rate_at_purchase", $rate);
            }
            
            // Crear pedido en estado on-hold (sin código de confirmación todavía)
            $order->update_status("on-hold", __("Esperando confirmación de pago móvil.", "wvp"));
            
            // IMPORTANTE: Guardar el pedido ANTES de vaciar el carrito
            $order->save();
            
            // Vaciar carrito
            WC()->cart->empty_cart();
            
            // Redirigir a página de confirmación de pago
            return array(
                "result" => "success",
                "redirect" => wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url())
            );
            
        } catch (Exception $e) {
            wc_add_notice(__("Error al procesar el pago.", "wvp"), "error");
            return array("result" => "fail", "redirect" => "");
        }
    }
    
    /**
     * Método robusto para inyectar contenido en la página de agradecimiento
     */
    public function maybe_inject_payment_info($content) {
        // Solo en páginas de agradecimiento
        if (!is_order_received_page()) {
            return $content;
        }
        
        // Obtener order_id de diferentes formas
        $order_id = false;
        
        // Método 1: De la URL
        $order_received = get_query_var('order-received');
        if ($order_received) {
            $order_id = $order_received;
        }
        
        // Método 2: De $_GET
        if (!$order_id && isset($_GET['order'])) {
            $order_id = absint($_GET['order']);
        }
        
        if (!$order_id) {
            return $content;
        }
        
        // Verificar que el pedido sea de Pago Móvil
        $order = wc_get_order($order_id);
        if (!$order || $order->get_payment_method() !== $this->id) {
            return $content;
        }
        
        // Capturar output
        ob_start();
        $this->display_payment_info_simple($order_id);
        $payment_info = ob_get_clean();
        
        // Inyectar DESPUÉS del mensaje de agradecimiento de WooCommerce
        return $content . $payment_info;
    }
    
    /**
     * Mostrar información de pago en la página de agradecimiento
     */
    public function display_payment_info_in_thankyou($order_id) {
        if (!is_numeric($order_id)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $payment_method = $order->get_payment_method();
        if ($payment_method !== $this->id) {
            return;
        }
        
        // Llamar al método de display
        $this->display_payment_info_simple($order_id);
    }
    
    public function thankyou_page($order_id) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP thankyou_page: Llamado con order_id: ' . $order_id);
        }
        
        $order = wc_get_order($order_id);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP thankyou_page: Order encontrado: ' . ($order ? 'yes' : 'no'));
            if ($order) {
                error_log('WVP thankyou_page: Payment method: ' . $order->get_payment_method());
                error_log('WVP thankyou_page: Gateway ID: ' . $this->id);
            }
        }
        
        if (!$order || $order->get_payment_method() !== $this->id) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WVP thankyou_page: No se muestra contenido - order o payment method no coincide');
            }
            return;
        }
        
        $account_name = $order->get_meta('_selected_account_name');
        $account_ci = $order->get_meta('_selected_account_ci');
        $account_bank = $order->get_meta('_selected_account_bank_name');
        $account_phone = $order->get_meta('_selected_account_phone');
        $account_qr_image = $order->get_meta('_selected_account_qr_image');
        $confirmation_code = $order->get_meta('_payment_confirmation');
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP thankyou_page: account_bank = ' . $account_bank);
            error_log('WVP thankyou_page: account_phone = ' . $account_phone);
            error_log('WVP thankyou_page: account_name = ' . $account_name);
        }
        
        if (!$account_bank || !$account_phone) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WVP thankyou_page: No se muestra contenido - account_bank o account_phone vacíos');
            }
            return;
        }
        
        $total_usd = $order->get_total();
        $total_ves = null;
        
        if ($bcv_rate) {
            $total_ves = $total_usd * floatval($bcv_rate);
        }
        
        // Verificar si ya tiene código de confirmación
        $has_confirmation = !empty($confirmation_code);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP thankyou_page: Renderizando contenido HTML');
        }
        
        ?>
        <style>
            .wvp-payment-container {
                max-width: 1200px;
                margin: 40px auto;
                padding: 20px;
            }
            .wvp-payment-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 40px;
                border-radius: 10px;
                margin-bottom: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .wvp-payment-header h2 {
                color: white;
                margin: 0 0 10px 0;
                font-size: 32px;
            }
            .wvp-payment-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-bottom: 30px;
            }
            @media (max-width: 768px) {
                .wvp-payment-grid {
                    grid-template-columns: 1fr;
                }
            }
            .wvp-payment-card {
                background: #fff;
                border-radius: 10px;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                border: 2px solid #e0e0e0;
            }
            .wvp-payment-card h3 {
                color: #333;
                margin-top: 0;
                border-bottom: 2px solid #667eea;
                padding-bottom: 15px;
            }
            .wvp-info-box {
                background: #f8f9fa;
                border: 2px solid #667eea;
                padding: 20px;
                border-radius: 8px;
                margin-top: 20px;
            }
            .wvp-info-box p {
                margin: 10px 0;
                font-size: 16px;
            }
            .wvp-info-box strong {
                color: #667eea;
                display: block;
                margin-bottom: 5px;
            }
            .wvp-total-box {
                background: #fff3cd;
                border: 2px solid #ffc107;
                padding: 30px;
                border-radius: 8px;
                text-align: center;
            }
            .wvp-total-box .amount {
                font-size: 36px;
                font-weight: bold;
                color: #856404;
                margin: 10px 0;
            }
            .wvp-qr-container {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                text-align: center;
                min-height: 300px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .wvp-qr-container img {
                max-width: 300px;
                max-height: 300px;
                border: 3px solid #667eea;
                padding: 10px;
                background: white;
                border-radius: 10px;
            }
            .wvp-form-section {
                background: #f8f9fa;
                padding: 30px;
                border-radius: 10px;
                margin-top: 30px;
            }
            .wvp-form-group {
                margin-bottom: 20px;
            }
            .wvp-form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
                color: #333;
            }
            .wvp-form-group input,
            .wvp-form-group select {
                width: 100%;
                padding: 12px;
                border: 2px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
            }
            .wvp-form-group input:focus,
            .wvp-form-group select:focus {
                border-color: #667eea;
                outline: none;
            }
            .wvp-btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 40px;
                border: none;
                border-radius: 5px;
                font-size: 18px;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s;
            }
            .wvp-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            }
            .wvp-btn-secondary {
                background: #6c757d;
                color: white;
                padding: 15px 40px;
                border: none;
                border-radius: 5px;
                font-size: 18px;
                cursor: pointer;
            }
            .wvp-instructions {
                background: #e7f3ff;
                border-left: 4px solid #2196F3;
                padding: 20px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .wvp-alert {
                background: #fff3cd;
                border: 1px solid #ffc107;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .wvp-success-box {
                background: #d4edda;
                border: 2px solid #28a745;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
        </style>
        
        <div class="wvp-payment-container">
            <div class="wvp-payment-header">
                <h2>📱 <?php _e('Realiza tu Pago Móvil', 'wvp'); ?></h2>
                <p style="margin: 0; font-size: 16px;"><?php _e('Orden #', 'wvp'); ?><?php echo esc_html($order->get_order_number()); ?></p>
            </div>
            
            <?php if ($has_confirmation): ?>
                <!-- Ya tiene código de confirmación - mostrar éxito -->
                <div class="wvp-success-box">
                    <h3 style="color: #28a745; margin-top: 0;">✅ <?php _e('Pago Confirmado', 'wvp'); ?></h3>
                    <p><strong><?php _e('Código de Confirmación:', 'wvp'); ?></strong> <?php echo esc_html($confirmation_code); ?></p>
                    <p><?php _e('Tu pago ha sido recibido y está pendiente de verificación.', 'wvp'); ?></p>
                </div>
            <?php else: ?>
                <!-- Formulario de confirmación -->
                <div class="wvp-payment-grid">
                    <!-- Datos bancarios -->
                    <div class="wvp-payment-card">
                        <h3>🏦 <?php _e('Datos para Pago Móvil', 'wvp'); ?></h3>
                        
                        <div class="wvp-info-box">
                            <p>
                                <strong><?php _e('Documento de Identificación:', 'wvp'); ?></strong>
                                <span style="color: #667eea; font-size: 18px; font-weight: bold;"><?php echo esc_html($account_ci); ?></span>
                                <?php if (strpos($account_ci, 'J') === 0): ?>
                                    <span style="color: #666; font-size: 14px;">(RIF Jurídico)</span>
                                <?php endif; ?>
                            </p>
                            <p>
                                <strong><?php _e('Cuenta:', 'wvp'); ?></strong>
                                <span style="color: #667eea; font-size: 18px;"><?php echo esc_html($account_name); ?></span>
                            </p>
                            <p>
                                <strong><?php _e('Banco:', 'wvp'); ?></strong>
                                <span style="color: #667eea; font-size: 18px;"><?php echo esc_html($account_bank); ?></span>
                            </p>
                            <p>
                                <strong><?php _e('Teléfono:', 'wvp'); ?></strong>
                                <span style="color: #667eea; font-size: 22px; font-family: monospace; font-weight: bold;"><?php echo esc_html($account_phone); ?></span>
                            </p>
                        </div>
                        
                        <?php if ($bcv_rate && $total_ves): ?>
                            <div class="wvp-total-box">
                                <p style="margin: 0;"><strong><?php _e('Monto a Pagar:', 'wvp'); ?></strong></p>
                                <div class="amount"><?php echo number_format($total_ves, 2, ',', '.'); ?> Bs.</div>
                                <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;"><?php echo wc_price($total_usd); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- QR Code y Monto -->
                    <div class="wvp-payment-card">
                        <h3>📱 <?php _e('Escanea para Pagar', 'wvp'); ?></h3>
                        
                        <div class="wvp-qr-container">
                            <?php if (!empty($account_qr_image)): ?>
                                <img src="<?php echo esc_url($account_qr_image); ?>" alt="QR Code" />
                            <?php else: ?>
                                <p style="color: #999;"><?php _e('QR no disponible', 'wvp'); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wvp-instructions">
                            <h4 style="margin-top: 0; color: #2196F3;">📝 <?php _e('Instrucciones:', 'wvp'); ?></h4>
                            <ol style="margin: 10px 0; padding-left: 20px;">
                                <li><?php _e('Abre la app de tu banco', 'wvp'); ?></li>
                                <li><?php _e('Selecciona "Pago Móvil" o "Pago de Servicios"', 'wvp'); ?></li>
                                <li><?php _e('Escanea el código QR o ingresa los datos manualmente', 'wvp'); ?></li>
                                <li><?php _e('Confirma y guarda el número de referencia', 'wvp'); ?></li>
                                <li><?php _e('Completa el formulario abajo', 'wvp'); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <!-- Formulario de Confirmación -->
                <div class="wvp-form-section">
                    <h3 style="margin-top: 0;">✅ <?php _e('Completa tu Pago', 'wvp'); ?></h3>
                    <div class="wvp-alert">
                        <p style="margin: 0;"><strong>⚠️ <?php _e('Importante:', 'wvp'); ?></strong> <?php _e('Ingresa el código de referencia que recibiste después de realizar el pago móvil.', 'wvp'); ?></p>
                    </div>
                    
                    <form method="post" action="" id="wvp-confirm-payment-form">
                        <?php wp_nonce_field('wvp_confirm_payment', 'wvp_payment_nonce'); ?>
                        <input type="hidden" name="action" value="wvp_confirm_payment">
                        <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="wvp-form-group">
                                <label><?php _e('Banco Emisor *', 'wvp'); ?></label>
                                <select name="wvp_payment_from_bank" required>
                                    <option value=""><?php _e('Selecciona tu banco', 'wvp'); ?></option>
                                    <?php foreach ($this->get_banks_list() as $code => $name): ?>
                                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="wvp-form-group">
                                <label><?php _e('Teléfono Emisor *', 'wvp'); ?></label>
                                <div style="display: flex;">
                                    <div style="padding: 12px; background: #f8f9fa; border: 2px solid #ddd; border-right: none; border-radius: 5px 0 0 5px; display: flex; align-items: center;">
                                        <span style="margin-right: 5px;">🇻🇪 +58</span>
                                    </div>
                                    <input type="text" name="wvp_payment_from_phone" placeholder="04141234567" maxlength="11" style="border-radius: 0 5px 5px 0;" required>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="wvp-form-group">
                                <label><?php _e('Fecha del Pago *', 'wvp'); ?></label>
                                <input type="date" name="wvp_payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="wvp-form-group">
                                <label><?php _e('Número de Referencia (Últimos 6 dígitos) *', 'wvp'); ?></label>
                                <input type="text" name="wvp_payment_reference" placeholder="123456" maxlength="12" required>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <button type="submit" class="wvp-btn-primary" id="wvp-submit-confirmation">
                                ✅ <?php _e('Completar Orden', 'wvp'); ?>
                            </button>
                            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="wvp-btn-secondary" style="text-decoration: none; display: inline-block;">
                                <?php _e('Cancelar', 'wvp'); ?>
                            </a>
                        </div>
                    </form>
                    
                    <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        $('#wvp-confirm-payment-form').on('submit', function(e) {
                            e.preventDefault();
                            
                            console.log('WVP: Formulario enviado');
                            
                            var $button = $('#wvp-submit-confirmation');
                            
                            $button.prop('disabled', true).text('Procesando...');
                            
                            var formData = new FormData(this);
                            formData.append('action', 'wvp_confirm_payment');
                            
                            console.log('WVP: Enviando AJAX...');
                            
                            $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    console.log('WVP: Respuesta recibida', response);
                                    if (response.success) {
                                        alert(response.data.message);
                                        window.location.reload();
        } else {
                                        alert(response.data.message || 'Error al procesar la confirmación.');
                                        $button.prop('disabled', false).html('✅ Completar Orden');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.log('WVP: Error AJAX', error);
                                    alert('Error de conexión. Por favor, intenta nuevamente.');
                                    $button.prop('disabled', false).html('✅ Completar Orden');
                                }
                            });
                            
                            return false;
                        });
                    });
                    </script>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function process_confirmation_form() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP process_confirmation_form: Llamado');
            error_log('WVP process_confirmation_form: $_POST keys: ' . print_r(array_keys($_POST), true));
        }
        
        if (!isset($_POST['order_id'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WVP process_confirmation_form: order_id no encontrado');
            }
            return;
        }
        
        if (!wp_verify_nonce($_POST['wvp_payment_nonce'], 'wvp_confirm_payment')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WVP process_confirmation_form: Error de nonce');
            }
            wc_add_notice(__('Error de seguridad.', 'wvp'), 'error');
            wp_safe_redirect(wc_get_endpoint_url('order-received', absint($_POST['order_id']), wc_get_checkout_url()));
            exit;
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP process_confirmation_form: Order ID: ' . $order_id);
            error_log('WVP process_confirmation_form: Order encontrado: ' . ($order ? 'yes' : 'no'));
        }
        
        if (!$order) {
            wc_add_notice(__('Pedido no encontrado.', 'wvp'), 'error');
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }
        
        $payment_from_bank = sanitize_text_field($_POST['wvp_payment_from_bank'] ?? '');
        $payment_from_phone = sanitize_text_field($_POST['wvp_payment_from_phone'] ?? '');
        $payment_date = sanitize_text_field($_POST['wvp_payment_date'] ?? '');
        $payment_reference = sanitize_text_field($_POST['wvp_payment_reference'] ?? '');
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP process_confirmation_form: Datos - banco: ' . $payment_from_bank . ', tel: ' . $payment_from_phone . ', fecha: ' . $payment_date . ', ref: ' . $payment_reference);
        }
        
        // Validar campos requeridos
        if (empty($payment_from_bank) || empty($payment_from_phone) || empty($payment_date) || empty($payment_reference)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WVP process_confirmation_form: Campos requeridos faltantes');
            }
            wc_add_notice(__('Todos los campos son requeridos.', 'wvp'), 'error');
            wp_safe_redirect(wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url()));
            exit;
        }
        
        // Validar formato de referencia (menos estricto)
        if (strlen($payment_reference) < 6) {
            wc_add_notice(__('El número de referencia debe tener al menos 6 dígitos.', 'wvp'), 'error');
            wp_safe_redirect(wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url()));
            exit;
        }
        
        // Guardar datos de confirmación
        $order->update_meta_data('_payment_confirmation', $payment_reference);
        $order->update_meta_data('_payment_from_bank', $payment_from_bank);
        $order->update_meta_data('_payment_from_bank_name', $this->get_bank_name($payment_from_bank));
        $order->update_meta_data('_payment_from_phone', $payment_from_phone);
        $order->update_meta_data('_payment_date', $payment_date);
        $order->update_meta_data('_payment_reference', $payment_reference);
        
        // Actualizar estado del pedido
        $order->update_status('on-hold', __('Cliente confirmó el pago móvil - pendiente de verificación.', 'wvp'));
        
        // Agregar nota al pedido
        $order->add_order_note(sprintf(
            __('Cliente confirmó pago móvil. Referencia: %s | Banco: %s | Teléfono: %s | Fecha: %s', 'wvp'),
            $payment_reference,
            $this->get_bank_name($payment_from_bank),
            $payment_from_phone,
            $payment_date
        ), false, true);
        
        $order->save();
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP process_confirmation_form: Confirmación guardada exitosamente');
        }
        
        wc_add_notice(__('¡Confirmación enviada exitosamente! Tu pedido está siendo procesado.', 'wvp'), 'success');
        
        // Redirigir de vuelta a la página de agradecimiento
        wp_safe_redirect(wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url()));
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
    
    public function display_payment_info($order) {
        if (!$order || $order->get_payment_method() !== $this->id) {
            return;
        }
        
        $account_name = $order->get_meta('_selected_account_name');
        $account_ci = $order->get_meta('_selected_account_ci');
        $account_bank = $order->get_meta('_selected_account_bank_name');
        $account_phone = $order->get_meta('_selected_account_phone');
        $account_qr_image = $order->get_meta('_selected_account_qr_image');
        $confirmation_code = $order->get_meta('_payment_confirmation');
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$account_bank || !$account_phone) {
            return;
        }
        
        $total_usd = $order->get_total();
        $total_ves = null;
        
        if ($bcv_rate) {
            $total_ves = $total_usd * floatval($bcv_rate);
        }
        
        // Verificar si ya tiene código de confirmación
        $has_confirmation = !empty($confirmation_code);
        
        ?>
        <style>
            .wvp-payment-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
            .wvp-payment-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .wvp-payment-header h2 { color: white; margin: 0 0 10px 0; font-size: 32px; }
            .wvp-payment-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
            @media (max-width: 768px) { .wvp-payment-grid { grid-template-columns: 1fr; } }
            .wvp-payment-card { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 2px solid #e0e0e0; }
            .wvp-payment-card h3 { color: #333; margin-top: 0; border-bottom: 2px solid #667eea; padding-bottom: 15px; }
            .wvp-info-box { background: #f8f9fa; border: 2px solid #667eea; padding: 20px; border-radius: 8px; margin-top: 20px; }
            .wvp-info-box p { margin: 10px 0; font-size: 16px; }
            .wvp-info-box strong { color: #667eea; display: block; margin-bottom: 5px; }
            .wvp-total-box { background: #fff3cd; border: 2px solid #ffc107; padding: 30px; border-radius: 8px; text-align: center; }
            .wvp-total-box .amount { font-size: 36px; font-weight: bold; color: #856404; margin: 10px 0; }
            .wvp-qr-container { background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; min-height: 300px; display: flex; align-items: center; justify-content: center; }
            .wvp-qr-container img { max-width: 300px; max-height: 300px; border: 3px solid #667eea; padding: 10px; background: white; border-radius: 10px; }
            .wvp-form-section { background: #f8f9fa; padding: 30px; border-radius: 10px; margin-top: 30px; }
            .wvp-form-group { margin-bottom: 20px; }
            .wvp-form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
            .wvp-form-group input, .wvp-form-group select { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; }
            .wvp-form-group input:focus, .wvp-form-group select:focus { border-color: #667eea; outline: none; }
            .wvp-btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; }
            .wvp-alert { background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .wvp-success-box { background: #d4edda; border: 2px solid #28a745; padding: 30px; border-radius: 10px; text-align: center; }
            .wvp-instructions { background: #e7f3ff; padding: 20px; border-radius: 8px; margin-top: 20px; }
            .wvp-instructions ol { margin: 10px 0; padding-left: 20px; }
            .wvp-instructions h4 { margin-top: 0; color: #2196F3; }
        </style>
        <div class="wvp-payment-container">
            <div class="wvp-payment-header">
                <h2>📱 <?php _e('Realiza tu Pago Móvil', 'wvp'); ?></h2>
                <p style="margin: 0; font-size: 16px;"><?php _e('Orden #', 'wvp'); ?><?php echo esc_html($order->get_order_number()); ?></p>
            </div>
            
            <?php if ($has_confirmation): ?>
                <div class="wvp-success-box">
                    <h3 style="color: #28a745; margin-top: 0;">✅ <?php _e('Pago Confirmado', 'wvp'); ?></h3>
                    <p><strong><?php _e('Código de Confirmación:', 'wvp'); ?></strong> <?php echo esc_html($confirmation_code); ?></p>
                    <p><?php _e('Tu pago ha sido recibido y está pendiente de verificación.', 'wvp'); ?></p>
                </div>
            <?php else: ?>
                <div class="wvp-payment-grid">
                    <div class="wvp-payment-card">
                        <h3>🏦 <?php _e('Datos para Pago Móvil', 'wvp'); ?></h3>
                        <div class="wvp-info-box">
                            <p><strong><?php _e('Documento:', 'wvp'); ?></strong> <span style="color: #667eea; font-weight: bold;"><?php echo esc_html($account_ci); ?></span></p>
                            <p><strong><?php _e('Cuenta:', 'wvp'); ?></strong> <span style="color: #667eea;"><?php echo esc_html($account_name); ?></span></p>
                            <p><strong><?php _e('Banco:', 'wvp'); ?></strong> <span style="color: #667eea;"><?php echo esc_html($account_bank); ?></span></p>
                            <p><strong><?php _e('Teléfono:', 'wvp'); ?></strong> <span style="color: #667eea; font-weight: bold;"><?php echo esc_html($account_phone); ?></span></p>
                        </div>
                        <?php if ($bcv_rate && $total_ves): ?>
                            <div class="wvp-total-box">
                                <p style="margin: 0;"><strong><?php _e('Monto a Pagar:', 'wvp'); ?></strong></p>
                                <div class="amount"><?php echo number_format($total_ves, 2, ',', '.'); ?> Bs.</div>
                                <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;"><?php echo wc_price($total_usd); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="wvp-payment-card">
                        <h3>📱 <?php _e('Escanea para Pagar', 'wvp'); ?></h3>
                        <div class="wvp-qr-container">
                            <?php if (!empty($account_qr_image)): ?>
                                <img src="<?php echo esc_url($account_qr_image); ?>" alt="QR Code" />
                            <?php else: ?>
                                <p style="color: #999;"><?php _e('QR no disponible', 'wvp'); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="wvp-instructions">
                            <h4>📝 <?php _e('Instrucciones:', 'wvp'); ?></h4>
                            <ol>
                                <li><?php _e('Abre la app de tu banco', 'wvp'); ?></li>
                                <li><?php _e('Selecciona "Pago Móvil"', 'wvp'); ?></li>
                                <li><?php _e('Escanea el código QR', 'wvp'); ?></li>
                                <li><?php _e('Confirma y guarda el número de referencia', 'wvp'); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="wvp-form-section">
                    <h3>✅ <?php _e('Completa tu Pago', 'wvp'); ?></h3>
                    <div class="wvp-alert">
                        <p style="margin: 0;"><strong>⚠️ <?php _e('Importante:', 'wvp'); ?></strong> <?php _e('Ingresa el código de referencia después de pagar.', 'wvp'); ?></p>
                    </div>
                    <form method="post" action="" id="wvp-confirm-payment-form">
                        <?php wp_nonce_field('wvp_confirm_payment', 'wvp_payment_nonce'); ?>
                        <input type="hidden" name="action" value="wvp_confirm_payment">
                        <input type="hidden" name="order_id" value="<?php echo esc_attr($order->get_id()); ?>">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="wvp-form-group">
                                <label><?php _e('Banco Emisor *', 'wvp'); ?></label>
                                <select name="wvp_payment_from_bank" required>
                                    <option value=""><?php _e('Selecciona', 'wvp'); ?></option>
                                    <?php foreach ($this->get_banks_list() as $code => $name): ?>
                                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="wvp-form-group">
                                <label><?php _e('Teléfono Emisor *', 'wvp'); ?></label>
                                <input type="text" name="wvp_payment_from_phone" placeholder="04141234567" required>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="wvp-form-group">
                                <label><?php _e('Fecha del Pago *', 'wvp'); ?></label>
                                <input type="date" name="wvp_payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="wvp-form-group">
                                <label><?php _e('Número de Referencia *', 'wvp'); ?></label>
                                <input type="text" name="wvp_payment_reference" placeholder="123456" required>
                            </div>
                        </div>
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <button type="submit" class="wvp-btn-primary">✅ <?php _e('Completar Orden', 'wvp'); ?></button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function display_payment_info_simple($order_id) {
        if (!is_numeric($order_id)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $payment_method = $order->get_payment_method();
        if ($payment_method !== $this->id) {
            return;
        }
        
        // Procesar confirmación de pago si se envió el formulario
        if (isset($_POST['wvp_confirm_action']) && $_POST['wvp_confirm_action'] === 'confirm_payment' && isset($_POST['order_id']) && $_POST['order_id'] == $order_id) {
            $this->process_payment_confirmation_simple($order_id);
        }
        
        // Si el pedido ya fue confirmado (estado processing o completed), NO mostrar el formulario
        $confirmation_code = $order->get_meta('_payment_confirmation');
        if (!empty($confirmation_code)) {
            // Mostrar mensaje de éxito en lugar de formulario
            ?>
            <div class="wvp-payment-container">
                <div class="wvp-success-box">
                    <h3>Pago Confirmado</h3>
                    <p><strong>Código de Confirmación:</strong> <?php echo esc_html($confirmation_code); ?></p>
                    <p>Tu pago ha sido recibido y está pendiente de verificación.</p>
                </div>
            </div>
            <?php
            return;
        }
        
        $account_name = $order->get_meta('_selected_account_name');
        $account_ci = $order->get_meta('_selected_account_ci');
        $account_bank = $order->get_meta('_selected_account_bank_name');
        $account_phone = $order->get_meta('_selected_account_phone');
        $account_qr_image = $order->get_meta('_selected_account_qr_image');
        $confirmation_code = $order->get_meta('_payment_confirmation');
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        
        if (!$account_bank || !$account_phone) {
            return;
        }
        
        $total_usd = $order->get_total();
        $total_ves = null;
        
        if ($bcv_rate) {
            $total_ves = $total_usd * floatval($bcv_rate);
        }
        
        $has_confirmation = !empty($confirmation_code);
        
        ?>
        <div class="wvp-payment-container">
            <div class="wvp-payment-header">
                <h2>Realiza tu Pago Móvil</h2>
                <p>Orden #<?php echo esc_html($order->get_order_number()); ?></p>
            </div>
            
            <?php if ($has_confirmation): ?>
                <div class="wvp-success-box">
                    <h3>Pago Confirmado</h3>
                    <p><strong>Código de Confirmación:</strong> <?php echo esc_html($confirmation_code); ?></p>
                    <p>Tu pago ha sido recibido y está pendiente de verificación.</p>
                </div>
            <?php else: ?>
                <div class="wvp-payment-grid">
                    <div class="wvp-payment-card">
                        <h3>Datos para Pago Móvil</h3>
                        <div class="wvp-info-box">
                            <p><strong>Documento:</strong> <?php echo esc_html($account_ci); ?></p>
                            <p><strong>Cuenta:</strong> <?php echo esc_html($account_name); ?></p>
                            <p><strong>Banco:</strong> <?php echo esc_html($account_bank); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo esc_html($account_phone); ?></p>
                        </div>
                        <?php if ($bcv_rate && $total_ves): ?>
                            <div class="wvp-total-box">
                                <p><strong>Monto a Pagar:</strong></p>
                                <div class="amount"><?php echo number_format($total_ves, 2, ',', '.'); ?> Bs.</div>
                                <p><?php echo wc_price($total_usd); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="wvp-payment-card">
                        <h3>Escanea para Pagar</h3>
                        <div class="wvp-qr-container">
                            <?php if (!empty($account_qr_image)): ?>
                                <img src="<?php echo esc_url($account_qr_image); ?>" alt="QR Code" />
                            <?php else: ?>
                                <p>QR no disponible</p>
                            <?php endif; ?>
                        </div>
                        <div class="wvp-instructions">
                            <h4>Instrucciones:</h4>
                            <ol>
                                <li>Abre la app de tu banco</li>
                                <li>Selecciona "Pago Móvil"</li>
                                <li>Escanea el código QR</li>
                                <li>Confirma y guarda el número de referencia</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="wvp-form-section">
                    <h3>Completa tu Pago</h3>
                    <div class="wvp-alert">
                        <p><strong>Importante:</strong> Ingresa el código de referencia después de pagar.</p>
                    </div>
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" id="wvp-confirm-payment-form">
                        <input type="hidden" name="wvp_payment_nonce" value="<?php echo wp_create_nonce('wvp_confirm_payment_' . $order_id); ?>">
                        <input type="hidden" name="wvp_confirm_action" value="confirm_payment">
                        <input type="hidden" name="order_id" value="<?php echo esc_attr($order->get_id()); ?>">
                        <div class="wvp-form-grid">
                            <div class="wvp-form-group">
                                <label>Banco Emisor *</label>
                                <select name="wvp_payment_from_bank" required>
                                    <option value="">Selecciona</option>
                                    <?php foreach ($this->get_banks_list() as $code => $name): ?>
                                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="wvp-form-group">
                                <label>Teléfono Emisor *</label>
                                <input type="text" name="wvp_payment_from_phone" placeholder="04141234567" required>
                            </div>
                        </div>
                        <div class="wvp-form-grid">
                            <div class="wvp-form-group">
                                <label>Fecha del Pago *</label>
                                <input type="date" name="wvp_payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="wvp-form-group">
                                <label>Número de Referencia *</label>
                                <input type="text" name="wvp_payment_reference" placeholder="123456" required>
                            </div>
                        </div>
                        <div class="wvp-form-actions">
                            <button type="submit" class="wvp-btn-primary" id="wvp-submit-confirmation">Completar Orden</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function inject_payment_info_on_thankyou_page($content) {
        if (!is_order_received_page()) {
            return $content;
        }
        
        $order_id = absint(get_query_var('order-received'));
        if (!$order_id) {
            return $content;
        }
        
        $order = wc_get_order($order_id);
        if (!$order || $order->get_payment_method() !== $this->id) {
            return $content;
        }
        
        ob_start();
        $this->display_payment_info_simple($order_id);
        $payment_content = ob_get_clean();
        
        return $content . $payment_content;
    }
    
    /**
     * Procesa el formulario de confirmación de pago (método simple con POST)
     */
    public function process_payment_confirmation_simple($order_id) {
        // Verificar que order_id es numérico y válido
        $order_id = absint($order_id);
        if ($order_id <= 0) {
            return;
        }
        
        // Verificar nonce
        if (!isset($_POST['wvp_payment_nonce']) || !wp_verify_nonce($_POST['wvp_payment_nonce'], 'wvp_confirm_payment_' . $order_id)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Verificar que la orden pertenece al usuario o es guest checkout
        $user_id = get_current_user_id();
        if ($user_id > 0) {
            $order_user_id = $order->get_user_id();
            if ($order_user_id > 0 && $order_user_id !== $user_id) {
                return;
            }
        }
        
        // Obtener datos del formulario - SANITIZAR
        $payment_from_bank = sanitize_text_field($_POST['wvp_payment_from_bank'] ?? '');
        $payment_from_phone = sanitize_text_field($_POST['wvp_payment_from_phone'] ?? '');
        $payment_date = sanitize_text_field($_POST['wvp_payment_date'] ?? '');
        $payment_reference = sanitize_text_field($_POST['wvp_payment_reference'] ?? '');
        
        // Validar campos requeridos
        if (empty($payment_from_bank) || empty($payment_from_phone) || empty($payment_date) || empty($payment_reference)) {
            return;
        }
        
        // Validación adicional: formato de referencia (solo números)
        if (!preg_match('/^[0-9]{6,20}$/', $payment_reference)) {
            return;
        }
        
        // Validación adicional: formato de teléfono venezolano
        if (!preg_match('/^(0[0-9]{2,3}[0-9]{7})$/', $payment_from_phone)) {
            return;
        }
        
        // Validación adicional: formato de fecha
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $payment_date)) {
            return;
        }
        
        // Guardar datos de confirmación
        $order->update_meta_data('_payment_confirmation', $payment_reference);
        $order->update_meta_data('_payment_from_bank', $payment_from_bank);
        $order->update_meta_data('_payment_from_bank_name', $this->get_bank_name($payment_from_bank));
        $order->update_meta_data('_payment_from_phone', $payment_from_phone);
        $order->update_meta_data('_payment_date', $payment_date);
        $order->update_meta_data('_payment_reference', $payment_reference);
        
        // Cambiar estado del pedido
        $order->update_status('processing', __('Pago confirmado por cliente', 'wvp'));
        
        // Enviar email de notificación al administrador
        WC()->mailer()->emails['WC_Email_New_Order']->trigger($order_id);
        
        // Guardar orden
        $order->save();
        
        // Agregar nota - ESCAPAR DATOS
        $order->add_order_note(sprintf(
            __('Pago confirmado - Banco: %s, Teléfono: %s, Fecha: %s, Referencia: %s', 'wvp'),
            esc_html($this->get_bank_name($payment_from_bank)),
            esc_html($payment_from_phone),
            esc_html($payment_date),
            esc_html($payment_reference)
        ));
        
        // Redirigir después del procesamiento para evitar resubmit
        $redirect_url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw($_SERVER['REQUEST_URI']) : wc_get_checkout_url();
        wp_safe_redirect($redirect_url);
        exit;
    }
    
    /**
     * Procesa el formulario de confirmación mediante AJAX
     */
    public function ajax_process_confirmation() {
        // DEBUG detallado
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WVP ajax_process_confirmation: Llamado');
            error_log('WVP ajax_process_confirmation: $_POST completo: ' . print_r($_POST, true));
        }
        
        // Verificar nonce
        if (!isset($_POST['wvp_payment_nonce'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WVP ajax_process_confirmation: No hay nonce en $_POST');
            }
            wp_send_json_error(array('message' => __('No se proporcionó nonce de seguridad.', 'wvp')));
            return;
        }
        
        if (!wp_verify_nonce($_POST['wvp_payment_nonce'], 'wvp_confirm_payment')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WVP ajax_process_confirmation: Nonce inválido');
                error_log('WVP ajax_process_confirmation: Nonce recibido: ' . $_POST['wvp_payment_nonce']);
            }
            wp_send_json_error(array('message' => __('Error de seguridad - nonce inválido.', 'wvp')));
            return;
        }
        
        if (!isset($_POST['order_id'])) {
            wp_send_json_error(array('message' => __('Pedido no encontrado.', 'wvp')));
            return;
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(array('message' => __('Pedido no encontrado.', 'wvp')));
            return;
        }
        
        if ($order->get_payment_method() !== $this->id) {
            wp_send_json_error(array('message' => __('Método de pago incorrecto.', 'wvp')));
            return;
        }
        
        // Obtener datos del formulario
        $payment_from_bank = sanitize_text_field($_POST['wvp_payment_from_bank'] ?? '');
        $payment_from_phone = sanitize_text_field($_POST['wvp_payment_from_phone'] ?? '');
        $payment_date = sanitize_text_field($_POST['wvp_payment_date'] ?? '');
        $payment_reference = sanitize_text_field($_POST['wvp_payment_reference'] ?? '');
        
        // Validar campos requeridos
        if (empty($payment_from_bank) || empty($payment_from_phone) || empty($payment_date) || empty($payment_reference)) {
            wp_send_json_error(array('message' => __('Todos los campos son requeridos.', 'wvp')));
            return;
        }
        
        // Guardar datos de confirmación
        $order->update_meta_data('_payment_confirmation', $payment_reference);
        $order->update_meta_data('_payment_from_bank', $payment_from_bank);
        $order->update_meta_data('_payment_from_bank_name', $this->get_bank_name($payment_from_bank));
        $order->update_meta_data('_payment_from_phone', $payment_from_phone);
        $order->update_meta_data('_payment_date', $payment_date);
        $order->update_meta_data('_payment_reference', $payment_reference);
        
        // Actualizar estado del pedido a "processing" y enviar correo
        // Esto es cuando realmente se procesa el pago
        $order->update_status('processing', __('Cliente confirmó el pago móvil. Pedido en proceso.', 'wvp'));
        
        // Enviar correo de nueva orden
        WC()->mailer()->emails['WC_Email_New_Order']->trigger($order->get_id());
        
        // Agregar nota al pedido
        $order->add_order_note(sprintf(
            __('Cliente confirmó pago móvil. Referencia: %s. Banco: %s. Teléfono: %s. Fecha: %s', 'wvp'),
            $payment_reference,
            $this->get_bank_name($payment_from_bank),
            $payment_from_phone,
            $payment_date
        ), false, true);
        
        $order->save();
        
        wp_send_json_success(array(
            'message' => __('¡Confirmación enviada exitosamente! Tu pedido está siendo procesado.', 'wvp')
        ));
    }
}
