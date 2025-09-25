<?php
/**
 * Pasarela de Pago Móvil (C2P) - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para la pasarela de pago móvil venezolana
 */
class WCVS_Gateway_Pago_Movil extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'wcvs_pago_movil';
        $this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/icons/pago-movil.png';
        $this->has_fields = true;
        $this->method_title = 'Pago Móvil (C2P)';
        $this->method_description = 'Permite pagos a través del sistema de pago móvil venezolano (C2P)';

        // Configuración por defecto
        $this->init_form_fields();
        $this->init_settings();

        // Configuración
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->phone_number = $this->get_option('phone_number');
        $this->rif_number = $this->get_option('rif_number');
        $this->bank_name = $this->get_option('bank_name');
        $this->min_amount = $this->get_option('min_amount');
        $this->max_amount = $this->get_option('max_amount');
        $this->instructions = $this->get_option('instructions');

        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
    }

    /**
     * Inicializar campos del formulario
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Habilitar/Deshabilitar',
                'type' => 'checkbox',
                'label' => 'Habilitar Pago Móvil',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Pago Móvil (C2P)',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout.',
                'default' => 'Paga de forma segura usando el sistema de pago móvil venezolano. Recibirás instrucciones por email después de completar tu pedido.'
            ),
            'phone_number' => array(
                'title' => 'Número de Teléfono',
                'type' => 'text',
                'description' => 'Número de teléfono para recibir pagos móviles.',
                'default' => '',
                'desc_tip' => true
            ),
            'rif_number' => array(
                'title' => 'RIF',
                'type' => 'text',
                'description' => 'RIF para recibir pagos móviles.',
                'default' => '',
                'desc_tip' => true
            ),
            'bank_name' => array(
                'title' => 'Banco',
                'type' => 'select',
                'description' => 'Banco asociado al pago móvil.',
                'options' => array(
                    'banesco' => 'Banesco',
                    'mercantil' => 'Banco Mercantil',
                    'bbva' => 'BBVA Provincial',
                    'venezuela' => 'Banco de Venezuela',
                    'bicentenario' => 'Banco Bicentenario',
                    '100_banco' => '100% Banco',
                    'banco_venezuela' => 'Banco de Venezuela',
                    'banco_exterior' => 'Banco Exterior',
                    'banco_caribe' => 'Banco del Caribe',
                    'banco_plaza' => 'Banco Plaza',
                    'banco_sofitasa' => 'Banco Sofitasa',
                    'banco_occidental' => 'Banco Occidental de Descuento',
                    'banco_venezolano' => 'Banco Venezolano de Crédito',
                    'banco_activo' => 'Banco Activo',
                    'banco_banplus' => 'Banplus',
                    'banco_bnc' => 'BNC Banco Nacional de Crédito',
                    'banco_bod' => 'BOD',
                    'banco_del_tesoro' => 'Banco del Tesoro',
                    'banco_industrial' => 'Banco Industrial de Venezuela',
                    'banco_lara' => 'Banco de Lara',
                    'banco_mi_banco' => 'Mi Banco',
                    'banco_nacional' => 'Banco Nacional de Crédito',
                    'banco_occidental' => 'Banco Occidental de Descuento',
                    'banco_plaza' => 'Banco Plaza',
                    'banco_sofitasa' => 'Banco Sofitasa',
                    'banco_venezolano' => 'Banco Venezolano de Crédito'
                ),
                'default' => 'banesco'
            ),
            'min_amount' => array(
                'title' => 'Monto Mínimo (Bs.)',
                'type' => 'number',
                'description' => 'Monto mínimo en bolívares para usar esta pasarela.',
                'default' => 1,
                'desc_tip' => true
            ),
            'max_amount' => array(
                'title' => 'Monto Máximo (Bs.)',
                'type' => 'number',
                'description' => 'Monto máximo en bolívares para usar esta pasarela.',
                'default' => 1000000,
                'desc_tip' => true
            ),
            'instructions' => array(
                'title' => 'Instrucciones',
                'type' => 'textarea',
                'description' => 'Instrucciones que se mostrarán en la página de agradecimiento y en emails.',
                'default' => 'Realiza el pago móvil y envía el comprobante con la referencia para confirmar tu pedido.'
            )
        );
    }

    /**
     * Procesar pago
     *
     * @param int $order_id ID del pedido
     * @return array
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return array(
                'result' => 'failure',
                'messages' => 'Error: Pedido no encontrado.'
            );
        }

        // Validar monto
        if (!$this->validate_amount($order->get_total())) {
            return array(
                'result' => 'failure',
                'messages' => 'El monto del pedido no está dentro del rango permitido para Pago Móvil.'
            );
        }

        // Validar datos del cliente
        $validation_result = $this->validate_customer_data($order);
        if (!$validation_result['valid']) {
            return array(
                'result' => 'failure',
                'messages' => $validation_result['message']
            );
        }

        // Marcar como pendiente de pago
        $order->update_status('pending', 'Esperando confirmación de pago móvil.');
        
        // Reducir stock
        $order->reduce_order_stock();
        
        // Limpiar carrito
        WC()->cart->empty_cart();
        
        // Enviar email con instrucciones
        $this->send_payment_instructions($order);

        // Log del pago
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Pago móvil iniciado para pedido #{$order_id}");

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    /**
     * Validar monto
     *
     * @param float $amount Monto
     * @return bool
     */
    private function validate_amount($amount) {
        $min_amount = floatval($this->min_amount);
        $max_amount = floatval($this->max_amount);
        
        return $amount >= $min_amount && $amount <= $max_amount;
    }

    /**
     * Validar datos del cliente
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function validate_customer_data($order) {
        $email = $order->get_billing_email();
        $phone = $order->get_billing_phone();
        
        // Validar email
        if (empty($email) || !is_email($email)) {
            return array(
                'valid' => false,
                'message' => 'Por favor ingresa un email válido.'
            );
        }
        
        // Validar teléfono venezolano
        if (empty($phone) || !$this->validate_venezuelan_phone($phone)) {
            return array(
                'valid' => false,
                'message' => 'Por favor ingresa un número de teléfono venezolano válido.'
            );
        }
        
        return array('valid' => true);
    }

    /**
     * Validar teléfono venezolano
     *
     * @param string $phone Teléfono
     * @return bool
     */
    private function validate_venezuelan_phone($phone) {
        // Formato: +58-XXX-XXXXXXX o 58-XXX-XXXXXXX o XXX-XXXXXXX
        return preg_match('/^(\+58|58)?[\s\-]?(\d{3})[\s\-]?(\d{7})$/', $phone);
    }

    /**
     * Validar RIF venezolano
     *
     * @param string $rif RIF
     * @return bool
     */
    private function validate_rif($rif) {
        // Formato: V-12345678-9, E-12345678-9, P-12345678-9, etc.
        return preg_match('/^[VEPGJC][\d]{8}[\d]$/', $rif);
    }

    /**
     * Enviar instrucciones de pago
     *
     * @param WC_Order $order Pedido
     */
    private function send_payment_instructions($order) {
        $total = $order->get_total();
        $currency = $order->get_currency();
        
        $subject = "Instrucciones de Pago Móvil - Pedido #{$order->get_id()}";
        
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n";
        $message .= "Gracias por tu pedido. Para completar tu pago móvil, sigue estas instrucciones:\n\n";
        $message .= "1. Realiza el pago móvil de {$currency} {$total}\n";
        $message .= "2. Envía el comprobante de pago con la referencia\n";
        $message .= "3. Espera la confirmación del pago\n\n";
        
        // Añadir información de la cuenta
        if (!empty($this->phone_number)) {
            $message .= "Información de la cuenta:\n";
            $message .= "Teléfono: {$this->phone_number}\n";
            if (!empty($this->rif_number)) {
                $message .= "RIF: {$this->rif_number}\n";
            }
            if (!empty($this->bank_name)) {
                $message .= "Banco: {$this->bank_name}\n";
            }
            $message .= "\n";
        }
        
        $message .= "Detalles del pedido:\n";
        $message .= "Pedido #{$order->get_id()}\n";
        $message .= "Total: {$currency} {$total}\n\n";
        $message .= "Si tienes alguna pregunta, no dudes en contactarnos.\n\n";
        $message .= "Saludos,\n";
        $message .= "Equipo de Kinta Electric";
        
        // Enviar email
        wp_mail($order->get_billing_email(), $subject, $message);
        
        // Log del email
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Instrucciones de pago móvil enviadas para pedido #{$order->get_id()}");
    }

    /**
     * Página de agradecimiento
     *
     * @param int $order_id ID del pedido
     */
    public function thankyou_page($order_id) {
        if ($this->instructions) {
            echo '<div class="wcvs-pago-movil-instructions">';
            echo '<h3>Instrucciones de Pago Móvil</h3>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            // Mostrar información de la cuenta
            if (!empty($this->phone_number)) {
                echo '<div class="wcvs-pago-movil-info">';
                echo '<h4>Información de la Cuenta:</h4>';
                echo '<p><strong>Teléfono:</strong> ' . esc_html($this->phone_number) . '</p>';
                if (!empty($this->rif_number)) {
                    echo '<p><strong>RIF:</strong> ' . esc_html($this->rif_number) . '</p>';
                }
                if (!empty($this->bank_name)) {
                    echo '<p><strong>Banco:</strong> ' . esc_html($this->bank_name) . '</p>';
                }
                echo '</div>';
            }
            
            echo '</div>';
        }
    }

    /**
     * Instrucciones en email
     *
     * @param WC_Order $order Pedido
     * @param bool $sent_to_admin Si se envió al admin
     * @param bool $plain_text Si es texto plano
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false) {
        if ($this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method()) {
            echo '<h2>Instrucciones de Pago Móvil</h2>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            if (!empty($this->phone_number)) {
                echo '<p><strong>Teléfono:</strong> ' . esc_html($this->phone_number) . '</p>';
                if (!empty($this->rif_number)) {
                    echo '<p><strong>RIF:</strong> ' . esc_html($this->rif_number) . '</p>';
                }
                if (!empty($this->bank_name)) {
                    echo '<p><strong>Banco:</strong> ' . esc_html($this->bank_name) . '</p>';
                }
            }
        }
    }

    /**
     * Verificar si la pasarela está disponible
     *
     * @return bool
     */
    public function is_available() {
        // Verificar si está habilitada
        if ($this->enabled !== 'yes') {
            return false;
        }
        
        // Verificar configuración mínima
        if (empty($this->phone_number) || empty($this->rif_number)) {
            return false;
        }
        
        // Verificar monto del carrito
        $cart_total = WC()->cart->get_total('raw');
        if (!$this->validate_amount($cart_total)) {
            return false;
        }
        
        return true;
    }

    /**
     * Obtener descripción de la pasarela
     *
     * @return string
     */
    public function get_description() {
        $description = $this->description;
        
        // Añadir información de montos si está configurado
        if ($this->min_amount > 0 || $this->max_amount > 0) {
            $description .= '<br><small>';
            if ($this->min_amount > 0) {
                $description .= 'Monto mínimo: ' . number_format($this->min_amount, 2, ',', '.') . ' Bs. ';
            }
            if ($this->max_amount > 0) {
                $description .= 'Monto máximo: ' . number_format($this->max_amount, 2, ',', '.') . ' Bs.';
            }
            $description .= '</small>';
        }
        
        return $description;
    }

    /**
     * Campos de pago
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
        
        // Mostrar información de la cuenta
        if (!empty($this->phone_number)) {
            echo '<div class="wcvs-pago-movil-info">';
            echo '<p><strong>Teléfono:</strong> ' . esc_html($this->phone_number) . '</p>';
            if (!empty($this->rif_number)) {
                echo '<p><strong>RIF:</strong> ' . esc_html($this->rif_number) . '</p>';
            }
            if (!empty($this->bank_name)) {
                echo '<p><strong>Banco:</strong> ' . esc_html($this->bank_name) . '</p>';
            }
            echo '</div>';
        }
    }

    /**
     * Validar campos de pago
     */
    public function validate_fields() {
        $email = $_POST['billing_email'] ?? '';
        $phone = $_POST['billing_phone'] ?? '';
        
        if (empty($email) || !is_email($email)) {
            wc_add_notice('Por favor ingresa un email válido.', 'error');
        }
        
        if (empty($phone) || !$this->validate_venezuelan_phone($phone)) {
            wc_add_notice('Por favor ingresa un número de teléfono venezolano válido.', 'error');
        }
    }

    /**
     * Obtener icono de la pasarela
     *
     * @return string
     */
    public function get_icon() {
        $icon_html = '<img src="' . esc_url($this->icon) . '" alt="' . esc_attr($this->title) . '" />';
        return apply_filters('woocommerce_gateway_icon', $icon_html, $this->id);
    }

    /**
     * Obtener título de la pasarela
     *
     * @return string
     */
    public function get_title() {
        return apply_filters('woocommerce_gateway_title', $this->title, $this->id);
    }

    /**
     * Obtener descripción de la pasarela
     *
     * @return string
     */
    public function get_method_description() {
        return apply_filters('woocommerce_gateway_description', $this->get_description(), $this->id);
    }

    /**
     * Verificar si la pasarela está habilitada
     *
     * @return bool
     */
    public function is_enabled() {
        return $this->enabled === 'yes';
    }

    /**
     * Obtener configuración de la pasarela
     *
     * @return array
     */
    public function get_gateway_settings() {
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'enabled' => $this->enabled,
            'phone_number' => $this->phone_number,
            'rif_number' => $this->rif_number,
            'bank_name' => $this->bank_name,
            'min_amount' => $this->min_amount,
            'max_amount' => $this->max_amount,
            'instructions' => $this->instructions
        );
    }

    /**
     * Obtener estadísticas de la pasarela
     *
     * @return array
     */
    public function get_gateway_stats() {
        global $wpdb;
        
        $order_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN post_status = 'wc-completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN post_status = 'wc-pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN post_status = 'wc-cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_payment_method'
            AND pm.meta_value = %s",
            $this->id
        ));
        
        return array(
            'total_orders' => intval($order_stats->total_orders),
            'completed_orders' => intval($order_stats->completed_orders),
            'pending_orders' => intval($order_stats->pending_orders),
            'cancelled_orders' => intval($order_stats->cancelled_orders),
            'success_rate' => $order_stats->total_orders > 0 ? 
                round(($order_stats->completed_orders / $order_stats->total_orders) * 100, 2) : 0
        );
    }

    /**
     * Generar referencia de pago móvil
     *
     * @param int $order_id ID del pedido
     * @return string
     */
    public function generate_payment_reference($order_id) {
        $prefix = 'PM';
        $timestamp = date('YmdHis');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Validar referencia de pago móvil
     *
     * @param string $reference Referencia
     * @return bool
     */
    public function validate_payment_reference($reference) {
        // Formato: PM + timestamp + random
        return preg_match('/^PM\d{14}\d{4}$/', $reference);
    }
}
