<?php
/**
 * Módulo de Pasarelas de Pago - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar las pasarelas de pago locales
 */
class WCVS_Payment_Gateways {

    /**
     * Instancia del plugin
     *
     * @var WCVS_Core
     */
    private $plugin;

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Pasarelas de pago disponibles
     *
     * @var array
     */
    private $available_gateways = array(
        'zelle' => 'Zelle',
        'binance' => 'Binance Pay',
        'pago_movil' => 'Pago Móvil (C2P)',
        'transferencias' => 'Transferencias Bancarias',
        'cash_deposit' => 'Cash Deposit USD',
        'mercantil' => 'Banco Mercantil',
        'banesco' => 'Banesco',
        'bbva' => 'BBVA Provincial',
        'cashea' => 'Cashea',
        'pagoflash' => 'PagoFlash'
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Evitar referencia circular
        $this->settings = get_option('wcvs_settings', array());
        
        $this->init_hooks();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Registrar pasarelas de pago
        $this->register_payment_gateways();

        // Configurar hooks de WooCommerce
        $this->setup_woocommerce_hooks();

        // Cargar scripts y estilos
        $this->enqueue_assets();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, 'Módulo Payment Gateways inicializado');
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
        
        // Hook para validación de pagos
        add_action('woocommerce_checkout_process', array($this, 'validate_payment_data'));
        
        // Hook para procesamiento de pagos
        add_action('woocommerce_payment_complete', array($this, 'handle_payment_complete'));
    }

    /**
     * Registrar pasarelas de pago
     */
    private function register_payment_gateways() {
        // Registrar cada pasarela si está habilitada
        foreach ($this->available_gateways as $gateway_key => $gateway_name) {
            $setting_key = $gateway_key . '_enabled';
            if ($this->settings[$setting_key] ?? false) {
                $this->register_single_gateway($gateway_key);
            }
        }
    }

    /**
     * Registrar una pasarela específica
     *
     * @param string $gateway_key Clave de la pasarela
     */
    private function register_single_gateway($gateway_key) {
        $gateway_class = 'WCVS_Gateway_' . ucfirst(str_replace('_', '_', $gateway_key));
        $gateway_file = 'gateways/class-wcvs-gateway-' . str_replace('_', '-', $gateway_key) . '.php';
        
        // Cargar archivo de la pasarela
        $file_path = WCVS_PLUGIN_PATH . 'modules/payment-gateways/' . $gateway_file;
        if (file_exists($file_path)) {
            require_once $file_path;
            
            if (class_exists($gateway_class)) {
                // Registrar la pasarela con WooCommerce
                add_filter('woocommerce_payment_gateways', function($gateways) use ($gateway_class) {
                    $gateways[] = $gateway_class;
                    return $gateways;
                });
                
                WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Pasarela {$gateway_key} registrada correctamente");
            }
        }
    }

    /**
     * Configurar hooks de WooCommerce
     */
    private function setup_woocommerce_hooks() {
        // Hook para añadir pasarelas
        add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
        
        // Hook para validación de checkout
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout'));
        
        // Hook para campos adicionales
        add_action('woocommerce_checkout_billing', array($this, 'add_checkout_fields'));
        
        // Hook para procesamiento de pedidos
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'), 10, 3);
    }

    /**
     * Añadir pasarelas de pago
     *
     * @param array $gateways Pasarelas existentes
     * @return array
     */
    public function add_payment_gateways($gateways) {
        // Las pasarelas se registran individualmente en register_single_gateway
        return $gateways;
    }

    /**
     * Validar datos de checkout
     */
    public function validate_checkout() {
        $payment_method = WC()->session->get('chosen_payment_method');
        
        if (!$payment_method || !$this->is_wcvs_gateway($payment_method)) {
            return;
        }

        // Validar datos específicos según la pasarela
        $this->validate_gateway_specific_data($payment_method);
    }

    /**
     * Validar datos específicos de la pasarela
     *
     * @param string $payment_method Método de pago
     */
    private function validate_gateway_specific_data($payment_method) {
        switch ($payment_method) {
            case 'wcvs_zelle':
                $this->validate_zelle_data();
                break;
            case 'wcvs_binance':
                $this->validate_binance_data();
                break;
            case 'wcvs_pago_movil':
                $this->validate_pago_movil_data();
                break;
            case 'wcvs_transferencias':
                $this->validate_transferencias_data();
                break;
            case 'wcvs_cash_deposit':
                $this->validate_cash_deposit_data();
                break;
        }
    }

    /**
     * Validar datos de Zelle
     */
    private function validate_zelle_data() {
        $email = $_POST['wcvs_zelle_email'] ?? '';
        $phone = $_POST['wcvs_zelle_phone'] ?? '';
        
        if (empty($email) || !is_email($email)) {
            wc_add_notice('Por favor ingresa un email válido para Zelle.', 'error');
        }
        
        if (empty($phone) || !$this->validate_phone($phone)) {
            wc_add_notice('Por favor ingresa un número de teléfono válido para Zelle.', 'error');
        }
    }

    /**
     * Validar datos de Binance
     */
    private function validate_binance_data() {
        $binance_id = $_POST['wcvs_binance_id'] ?? '';
        
        if (empty($binance_id)) {
            wc_add_notice('Por favor ingresa tu ID de Binance.', 'error');
        }
    }

    /**
     * Validar datos de Pago Móvil
     */
    private function validate_pago_movil_data() {
        $phone = $_POST['wcvs_pago_movil_phone'] ?? '';
        $rif = $_POST['wcvs_pago_movil_rif'] ?? '';
        
        if (empty($phone) || !$this->validate_venezuelan_phone($phone)) {
            wc_add_notice('Por favor ingresa un número de teléfono venezolano válido.', 'error');
        }
        
        if (empty($rif) || !$this->validate_rif($rif)) {
            wc_add_notice('Por favor ingresa un RIF válido.', 'error');
        }
    }

    /**
     * Validar datos de Transferencias
     */
    private function validate_transferencias_data() {
        $bank = $_POST['wcvs_transferencias_bank'] ?? '';
        $account = $_POST['wcvs_transferencias_account'] ?? '';
        
        if (empty($bank)) {
            wc_add_notice('Por favor selecciona un banco.', 'error');
        }
        
        if (empty($account)) {
            wc_add_notice('Por favor ingresa el número de cuenta.', 'error');
        }
    }

    /**
     * Validar datos de Cash Deposit
     */
    private function validate_cash_deposit_data() {
        $location = $_POST['wcvs_cash_deposit_location'] ?? '';
        
        if (empty($location)) {
            wc_add_notice('Por favor selecciona una ubicación para el depósito.', 'error');
        }
    }

    /**
     * Añadir campos adicionales al checkout
     */
    public function add_checkout_fields() {
        $payment_method = WC()->session->get('chosen_payment_method');
        
        if (!$payment_method || !$this->is_wcvs_gateway($payment_method)) {
            return;
        }

        echo '<div class="wcvs-payment-fields" id="wcvs-payment-fields-' . esc_attr($payment_method) . '">';
        
        switch ($payment_method) {
            case 'wcvs_zelle':
                $this->render_zelle_fields();
                break;
            case 'wcvs_binance':
                $this->render_binance_fields();
                break;
            case 'wcvs_pago_movil':
                $this->render_pago_movil_fields();
                break;
            case 'wcvs_transferencias':
                $this->render_transferencias_fields();
                break;
            case 'wcvs_cash_deposit':
                $this->render_cash_deposit_fields();
                break;
        }
        
        echo '</div>';
    }

    /**
     * Renderizar campos de Zelle
     */
    private function render_zelle_fields() {
        ?>
        <div class="wcvs-zelle-fields">
            <h3>Información de Zelle</h3>
            <p class="form-row form-row-wide">
                <label for="wcvs_zelle_email">Email de Zelle <span class="required">*</span></label>
                <input type="email" class="input-text" name="wcvs_zelle_email" id="wcvs_zelle_email" required>
            </p>
            <p class="form-row form-row-wide">
                <label for="wcvs_zelle_phone">Teléfono <span class="required">*</span></label>
                <input type="tel" class="input-text" name="wcvs_zelle_phone" id="wcvs_zelle_phone" required>
            </p>
            <p class="wcvs-payment-info">
                <strong>Instrucciones:</strong> Realiza el pago a través de Zelle y envía el comprobante a nuestro email.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos de Binance
     */
    private function render_binance_fields() {
        ?>
        <div class="wcvs-binance-fields">
            <h3>Información de Binance Pay</h3>
            <p class="form-row form-row-wide">
                <label for="wcvs_binance_id">ID de Binance <span class="required">*</span></label>
                <input type="text" class="input-text" name="wcvs_binance_id" id="wcvs_binance_id" required>
            </p>
            <p class="wcvs-payment-info">
                <strong>Instrucciones:</strong> Realiza el pago a través de Binance Pay y envía el comprobante.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos de Pago Móvil
     */
    private function render_pago_movil_fields() {
        ?>
        <div class="wcvs-pago-movil-fields">
            <h3>Información de Pago Móvil</h3>
            <p class="form-row form-row-wide">
                <label for="wcvs_pago_movil_phone">Teléfono <span class="required">*</span></label>
                <input type="tel" class="input-text" name="wcvs_pago_movil_phone" id="wcvs_pago_movil_phone" 
                       placeholder="+58-XXX-XXXXXXX" required>
            </p>
            <p class="form-row form-row-wide">
                <label for="wcvs_pago_movil_rif">RIF <span class="required">*</span></label>
                <input type="text" class="input-text" name="wcvs_pago_movil_rif" id="wcvs_pago_movil_rif" 
                       placeholder="V-12345678-9" required>
            </p>
            <p class="wcvs-payment-info">
                <strong>Instrucciones:</strong> Realiza el pago móvil y envía el comprobante con la referencia.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos de Transferencias
     */
    private function render_transferencias_fields() {
        ?>
        <div class="wcvs-transferencias-fields">
            <h3>Información de Transferencia Bancaria</h3>
            <p class="form-row form-row-wide">
                <label for="wcvs_transferencias_bank">Banco <span class="required">*</span></label>
                <select name="wcvs_transferencias_bank" id="wcvs_transferencias_bank" required>
                    <option value="">Selecciona un banco</option>
                    <option value="mercantil">Banco Mercantil</option>
                    <option value="banesco">Banesco</option>
                    <option value="bbva">BBVA Provincial</option>
                    <option value="venezuela">Banco de Venezuela</option>
                    <option value="bicentenario">Banco Bicentenario</option>
                </select>
            </p>
            <p class="form-row form-row-wide">
                <label for="wcvs_transferencias_account">Número de Cuenta <span class="required">*</span></label>
                <input type="text" class="input-text" name="wcvs_transferencias_account" id="wcvs_transferencias_account" required>
            </p>
            <p class="wcvs-payment-info">
                <strong>Instrucciones:</strong> Realiza la transferencia bancaria y envía el comprobante.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos de Cash Deposit
     */
    private function render_cash_deposit_fields() {
        ?>
        <div class="wcvs-cash-deposit-fields">
            <h3>Información de Depósito en Efectivo</h3>
            <p class="form-row form-row-wide">
                <label for="wcvs_cash_deposit_location">Ubicación <span class="required">*</span></label>
                <select name="wcvs_cash_deposit_location" id="wcvs_cash_deposit_location" required>
                    <option value="">Selecciona una ubicación</option>
                    <option value="caracas">Caracas</option>
                    <option value="maracaibo">Maracaibo</option>
                    <option value="valencia">Valencia</option>
                    <option value="barquisimeto">Barquisimeto</option>
                    <option value="ciudad_guayana">Ciudad Guayana</option>
                </select>
            </p>
            <p class="wcvs-payment-info">
                <strong>Instrucciones:</strong> Realiza el depósito en efectivo en la ubicación seleccionada.
            </p>
        </div>
        <?php
    }

    /**
     * Manejar cambio de estado de pedido
     *
     * @param int $order_id ID del pedido
     * @param string $old_status Estado anterior
     * @param string $new_status Estado nuevo
     */
    public function handle_order_status_change($order_id, $old_status, $new_status) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $payment_method = $order->get_payment_method();
        if (!$this->is_wcvs_gateway($payment_method)) {
            return;
        }

        // Manejar estados específicos
        switch ($new_status) {
            case 'processing':
                $this->handle_processing_status($order);
                break;
            case 'completed':
                $this->handle_completed_status($order);
                break;
            case 'cancelled':
                $this->handle_cancelled_status($order);
                break;
        }
    }

    /**
     * Manejar estado de procesamiento
     *
     * @param WC_Order $order Pedido
     */
    private function handle_processing_status($order) {
        // Enviar instrucciones de pago
        $this->send_payment_instructions($order);
    }

    /**
     * Manejar estado completado
     *
     * @param WC_Order $order Pedido
     */
    private function handle_completed_status($order) {
        // Enviar confirmación de pago
        $this->send_payment_confirmation($order);
    }

    /**
     * Manejar estado cancelado
     *
     * @param WC_Order $order Pedido
     */
    private function handle_cancelled_status($order) {
        // Enviar notificación de cancelación
        $this->send_cancellation_notification($order);
    }

    /**
     * Enviar instrucciones de pago
     *
     * @param WC_Order $order Pedido
     */
    private function send_payment_instructions($order) {
        $payment_method = $order->get_payment_method();
        $instructions = $this->get_payment_instructions($payment_method, $order);
        
        if ($instructions) {
            // Enviar email con instrucciones
            $this->send_payment_email($order, 'instructions', $instructions);
        }
    }

    /**
     * Obtener instrucciones de pago
     *
     * @param string $payment_method Método de pago
     * @param WC_Order $order Pedido
     * @return string
     */
    private function get_payment_instructions($payment_method, $order) {
        $total = $order->get_total();
        $currency = $order->get_currency();
        
        switch ($payment_method) {
            case 'wcvs_zelle':
                return "Instrucciones para pago con Zelle:\n\n" .
                       "1. Realiza el pago de {$currency} {$total} a través de Zelle\n" .
                       "2. Envía el comprobante a nuestro email\n" .
                       "3. Espera la confirmación del pago";
                       
            case 'wcvs_binance':
                return "Instrucciones para pago con Binance Pay:\n\n" .
                       "1. Realiza el pago de {$currency} {$total} a través de Binance Pay\n" .
                       "2. Envía el comprobante con tu ID de Binance\n" .
                       "3. Espera la confirmación del pago";
                       
            case 'wcvs_pago_movil':
                return "Instrucciones para Pago Móvil:\n\n" .
                       "1. Realiza el pago móvil de {$currency} {$total}\n" .
                       "2. Envía el comprobante con la referencia\n" .
                       "3. Espera la confirmación del pago";
                       
            case 'wcvs_transferencias':
                return "Instrucciones para Transferencia Bancaria:\n\n" .
                       "1. Realiza la transferencia de {$currency} {$total}\n" .
                       "2. Envía el comprobante de la transferencia\n" .
                       "3. Espera la confirmación del pago";
                       
            case 'wcvs_cash_deposit':
                return "Instrucciones para Depósito en Efectivo:\n\n" .
                       "1. Realiza el depósito de {$currency} {$total} en la ubicación indicada\n" .
                       "2. Envía el comprobante del depósito\n" .
                       "3. Espera la confirmación del pago";
        }
        
        return '';
    }

    /**
     * Enviar email de pago
     *
     * @param WC_Order $order Pedido
     * @param string $type Tipo de email
     * @param string $content Contenido del email
     */
    private function send_payment_email($order, $type, $content) {
        $subject = "Instrucciones de Pago - Pedido #{$order->get_id()}";
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n" . $content;
        
        // Enviar email
        wp_mail($order->get_billing_email(), $subject, $message);
    }

    /**
     * Cargar assets (CSS y JS)
     */
    private function enqueue_assets() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_payment_assets'));
    }

    /**
     * Encolar assets de pago
     */
    public function enqueue_payment_assets() {
        if (is_checkout()) {
            wp_enqueue_style(
                'wcvs-payment-gateways',
                WCVS_PLUGIN_URL . 'modules/payment-gateways/css/wcvs-payment-gateways.css',
                array(),
                WCVS_VERSION
            );

            wp_enqueue_script(
                'wcvs-payment-gateways',
                WCVS_PLUGIN_URL . 'modules/payment-gateways/js/wcvs-payment-gateways.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-payment-gateways', 'wcvs_payment_gateways', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_payment_gateways_nonce'),
                'strings' => array(
                    'loading' => 'Procesando...',
                    'error' => 'Error al procesar el pago',
                    'success' => 'Pago procesado correctamente'
                )
            ));
        }
    }

    /**
     * Verificar si es una pasarela WCVS
     *
     * @param string $payment_method Método de pago
     * @return bool
     */
    private function is_wcvs_gateway($payment_method) {
        return strpos($payment_method, 'wcvs_') === 0;
    }

    /**
     * Validar teléfono
     *
     * @param string $phone Teléfono
     * @return bool
     */
    private function validate_phone($phone) {
        return preg_match('/^\+?[\d\s\-\(\)]+$/', $phone);
    }

    /**
     * Validar teléfono venezolano
     *
     * @param string $phone Teléfono
     * @return bool
     */
    private function validate_venezuelan_phone($phone) {
        return preg_match('/^(\+58|58)?[\s\-]?(\d{3})[\s\-]?(\d{7})$/', $phone);
    }

    /**
     * Validar RIF venezolano
     *
     * @param string $rif RIF
     * @return bool
     */
    private function validate_rif($rif) {
        return preg_match('/^[VEPGJC][\d]{8}[\d]$/', $rif);
    }

    /**
     * Manejar activación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'payment_gateways') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_PAYMENTS, 'Módulo Payment Gateways activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'payment_gateways') {
            // Limpiar hooks
            remove_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
            remove_action('woocommerce_checkout_process', array($this, 'validate_checkout'));
            
            WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, 'Módulo Payment Gateways desactivado');
        }
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        $enabled_gateways = 0;
        foreach ($this->available_gateways as $gateway_key => $gateway_name) {
            $setting_key = $gateway_key . '_enabled';
            if ($this->settings[$setting_key] ?? false) {
                $enabled_gateways++;
            }
        }

        return array(
            'total_gateways' => count($this->available_gateways),
            'enabled_gateways' => $enabled_gateways,
            'available_gateways' => $this->available_gateways,
            'settings' => $this->settings
        );
    }
}
