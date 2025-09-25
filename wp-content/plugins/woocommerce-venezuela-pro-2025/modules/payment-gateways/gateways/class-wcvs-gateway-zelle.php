<?php
/**
 * Pasarela de Pago Zelle - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para la pasarela de pago Zelle
 */
class WCVS_Gateway_Zelle extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'wcvs_zelle';
        $this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/icons/zelle.png';
        $this->has_fields = true;
        $this->method_title = 'Zelle';
        $this->method_description = 'Permite pagos a través de Zelle para clientes venezolanos';

        // Configuración por defecto
        $this->init_form_fields();
        $this->init_settings();

        // Configuración
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->zelle_email = $this->get_option('zelle_email');
        $this->zelle_phone = $this->get_option('zelle_phone');
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
                'label' => 'Habilitar Zelle',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Zelle',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout.',
                'default' => 'Paga de forma segura usando Zelle. Recibirás instrucciones por email después de completar tu pedido.'
            ),
            'zelle_email' => array(
                'title' => 'Email de Zelle',
                'type' => 'email',
                'description' => 'Email de Zelle donde recibirás los pagos.',
                'default' => '',
                'desc_tip' => true
            ),
            'zelle_phone' => array(
                'title' => 'Teléfono de Zelle',
                'type' => 'text',
                'description' => 'Número de teléfono asociado a Zelle.',
                'default' => '',
                'desc_tip' => true
            ),
            'min_amount' => array(
                'title' => 'Monto Mínimo',
                'type' => 'number',
                'description' => 'Monto mínimo para usar esta pasarela.',
                'default' => 1,
                'desc_tip' => true
            ),
            'max_amount' => array(
                'title' => 'Monto Máximo',
                'type' => 'number',
                'description' => 'Monto máximo para usar esta pasarela.',
                'default' => 10000,
                'desc_tip' => true
            ),
            'instructions' => array(
                'title' => 'Instrucciones',
                'type' => 'textarea',
                'description' => 'Instrucciones que se mostrarán en la página de agradecimiento y en emails.',
                'default' => 'Realiza el pago a través de Zelle y envía el comprobante a nuestro email para confirmar tu pedido.'
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
                'messages' => 'El monto del pedido no está dentro del rango permitido para Zelle.'
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
        $order->update_status('pending', 'Esperando confirmación de pago Zelle.');
        
        // Reducir stock
        $order->reduce_order_stock();
        
        // Limpiar carrito
        WC()->cart->empty_cart();
        
        // Enviar email con instrucciones
        $this->send_payment_instructions($order);

        // Log del pago
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Pago Zelle iniciado para pedido #{$order_id}");

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
        
        // Validar teléfono
        if (empty($phone) || !$this->validate_phone($phone)) {
            return array(
                'valid' => false,
                'message' => 'Por favor ingresa un número de teléfono válido.'
            );
        }
        
        return array('valid' => true);
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
     * Enviar instrucciones de pago
     *
     * @param WC_Order $order Pedido
     */
    private function send_payment_instructions($order) {
        $total = $order->get_total();
        $currency = $order->get_currency();
        
        $subject = "Instrucciones de Pago Zelle - Pedido #{$order->get_id()}";
        
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n";
        $message .= "Gracias por tu pedido. Para completar tu pago con Zelle, sigue estas instrucciones:\n\n";
        $message .= "1. Realiza el pago de {$currency} {$total} a través de Zelle\n";
        $message .= "2. Envía el comprobante de pago a nuestro email\n";
        $message .= "3. Espera la confirmación del pago\n\n";
        $message .= "Detalles del pedido:\n";
        $message .= "Pedido #{$order->get_id()}\n";
        $message .= "Total: {$currency} {$total}\n\n";
        $message .= "Si tienes alguna pregunta, no dudes en contactarnos.\n\n";
        $message .= "Saludos,\n";
        $message .= "Equipo de Kinta Electric";
        
        // Enviar email
        wp_mail($order->get_billing_email(), $subject, $message);
        
        // Log del email
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Instrucciones de pago Zelle enviadas para pedido #{$order->get_id()}");
    }

    /**
     * Página de agradecimiento
     *
     * @param int $order_id ID del pedido
     */
    public function thankyou_page($order_id) {
        if ($this->instructions) {
            echo '<div class="wcvs-zelle-instructions">';
            echo '<h3>Instrucciones de Pago Zelle</h3>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
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
            echo '<h2>Instrucciones de Pago Zelle</h2>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
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
        if (empty($this->zelle_email) || empty($this->zelle_phone)) {
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
                $description .= 'Monto mínimo: $' . number_format($this->min_amount, 2) . ' ';
            }
            if ($this->max_amount > 0) {
                $description .= 'Monto máximo: $' . number_format($this->max_amount, 2);
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
        
        // Mostrar información de la cuenta Zelle
        if (!empty($this->zelle_email)) {
            echo '<div class="wcvs-zelle-info">';
            echo '<p><strong>Email de Zelle:</strong> ' . esc_html($this->zelle_email) . '</p>';
            if (!empty($this->zelle_phone)) {
                echo '<p><strong>Teléfono:</strong> ' . esc_html($this->zelle_phone) . '</p>';
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
        
        if (empty($phone) || !$this->validate_phone($phone)) {
            wc_add_notice('Por favor ingresa un número de teléfono válido.', 'error');
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
            'zelle_email' => $this->zelle_email,
            'zelle_phone' => $this->zelle_phone,
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
}
