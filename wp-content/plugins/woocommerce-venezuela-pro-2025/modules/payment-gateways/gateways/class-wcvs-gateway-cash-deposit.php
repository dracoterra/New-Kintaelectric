<?php
/**
 * Pasarela de Cash Deposit USD - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para la pasarela de depósito en efectivo USD
 */
class WCVS_Gateway_Cash_Deposit extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'wcvs_cash_deposit';
        $this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/icons/cash-deposit.png';
        $this->has_fields = true;
        $this->method_title = 'Cash Deposit USD';
        $this->method_description = 'Permite pagos mediante depósito en efectivo USD en ubicaciones específicas';

        // Configuración por defecto
        $this->init_form_fields();
        $this->init_settings();

        // Configuración
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->deposit_locations = $this->get_option('deposit_locations');
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
                'label' => 'Habilitar Cash Deposit USD',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Cash Deposit USD',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout.',
                'default' => 'Paga en efectivo USD en nuestras ubicaciones autorizadas. Recibirás instrucciones por email después de completar tu pedido.'
            ),
            'deposit_locations' => array(
                'title' => 'Ubicaciones de Depósito',
                'type' => 'textarea',
                'description' => 'Configura las ubicaciones disponibles. Una por línea en formato: Ciudad|Dirección|Teléfono|Horario|Contacto',
                'default' => "Caracas|Av. Francisco de Miranda, Torre Parque Cristal, Piso 15|+58-212-123-4567|Lunes a Viernes 9:00 AM - 5:00 PM|Juan Pérez\nMaracaibo|C.C. Sambil Maracaibo, Local 45|+58-261-123-4567|Lunes a Sábado 10:00 AM - 6:00 PM|María González\nValencia|C.C. Sambil Valencia, Local 23|+58-241-123-4567|Lunes a Sábado 10:00 AM - 6:00 PM|Carlos Rodríguez",
                'desc_tip' => true
            ),
            'min_amount' => array(
                'title' => 'Monto Mínimo (USD)',
                'type' => 'number',
                'description' => 'Monto mínimo en USD para usar esta pasarela.',
                'default' => 10,
                'desc_tip' => true
            ),
            'max_amount' => array(
                'title' => 'Monto Máximo (USD)',
                'type' => 'number',
                'description' => 'Monto máximo en USD para usar esta pasarela.',
                'default' => 5000,
                'desc_tip' => true
            ),
            'instructions' => array(
                'title' => 'Instrucciones',
                'type' => 'textarea',
                'description' => 'Instrucciones que se mostrarán en la página de agradecimiento y en emails.',
                'default' => 'Realiza el depósito en efectivo USD en la ubicación seleccionada y envía el comprobante para confirmar tu pedido.'
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
                'messages' => 'El monto del pedido no está dentro del rango permitido para Cash Deposit USD.'
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
        $order->update_status('pending', 'Esperando confirmación de depósito en efectivo USD.');
        
        // Reducir stock
        $order->reduce_order_stock();
        
        // Limpiar carrito
        WC()->cart->empty_cart();
        
        // Enviar email con instrucciones
        $this->send_payment_instructions($order);

        // Log del pago
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Cash Deposit USD iniciado para pedido #{$order_id}");

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
        
        $subject = "Instrucciones de Cash Deposit USD - Pedido #{$order->get_id()}";
        
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n";
        $message .= "Gracias por tu pedido. Para completar tu depósito en efectivo USD, sigue estas instrucciones:\n\n";
        $message .= "1. Realiza el depósito de {$currency} {$total} en la ubicación seleccionada\n";
        $message .= "2. Envía el comprobante del depósito\n";
        $message .= "3. Espera la confirmación del pago\n\n";
        
        // Añadir información de las ubicaciones
        $locations = $this->get_deposit_locations();
        if (!empty($locations)) {
            $message .= "Ubicaciones disponibles para depósito:\n";
            foreach ($locations as $location) {
                $message .= "- {$location['city']}: {$location['address']}\n";
                $message .= "  Teléfono: {$location['phone']}\n";
                $message .= "  Horario: {$location['schedule']}\n";
                $message .= "  Contacto: {$location['contact']}\n\n";
            }
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
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Instrucciones de Cash Deposit USD enviadas para pedido #{$order->get_id()}");
    }

    /**
     * Obtener ubicaciones de depósito
     *
     * @return array
     */
    private function get_deposit_locations() {
        $locations = array();
        $lines = explode("\n", $this->deposit_locations);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            $parts = explode('|', $line);
            if (count($parts) >= 5) {
                $locations[] = array(
                    'city' => trim($parts[0]),
                    'address' => trim($parts[1]),
                    'phone' => trim($parts[2]),
                    'schedule' => trim($parts[3]),
                    'contact' => trim($parts[4])
                );
            }
        }
        
        return $locations;
    }

    /**
     * Página de agradecimiento
     *
     * @param int $order_id ID del pedido
     */
    public function thankyou_page($order_id) {
        if ($this->instructions) {
            echo '<div class="wcvs-cash-deposit-instructions">';
            echo '<h3>Instrucciones de Cash Deposit USD</h3>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            // Mostrar ubicaciones de depósito
            $locations = $this->get_deposit_locations();
            if (!empty($locations)) {
                echo '<div class="wcvs-deposit-locations">';
                echo '<h4>Ubicaciones Disponibles para Depósito:</h4>';
                echo '<table class="wcvs-deposit-locations-table">';
                echo '<thead><tr><th>Ciudad</th><th>Dirección</th><th>Teléfono</th><th>Horario</th><th>Contacto</th></tr></thead>';
                echo '<tbody>';
                foreach ($locations as $location) {
                    echo '<tr>';
                    echo '<td>' . esc_html($location['city']) . '</td>';
                    echo '<td>' . esc_html($location['address']) . '</td>';
                    echo '<td>' . esc_html($location['phone']) . '</td>';
                    echo '<td>' . esc_html($location['schedule']) . '</td>';
                    echo '<td>' . esc_html($location['contact']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
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
            echo '<h2>Instrucciones de Cash Deposit USD</h2>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            $locations = $this->get_deposit_locations();
            if (!empty($locations)) {
                echo '<h3>Ubicaciones Disponibles para Depósito:</h3>';
                echo '<table>';
                echo '<thead><tr><th>Ciudad</th><th>Dirección</th><th>Teléfono</th><th>Horario</th><th>Contacto</th></tr></thead>';
                echo '<tbody>';
                foreach ($locations as $location) {
                    echo '<tr>';
                    echo '<td>' . esc_html($location['city']) . '</td>';
                    echo '<td>' . esc_html($location['address']) . '</td>';
                    echo '<td>' . esc_html($location['phone']) . '</td>';
                    echo '<td>' . esc_html($location['schedule']) . '</td>';
                    echo '<td>' . esc_html($location['contact']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
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
        if (empty($this->deposit_locations)) {
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
        
        // Mostrar ubicaciones de depósito disponibles
        $locations = $this->get_deposit_locations();
        if (!empty($locations)) {
            echo '<div class="wcvs-deposit-locations">';
            echo '<h4>Ubicaciones Disponibles para Depósito:</h4>';
            echo '<table class="wcvs-deposit-locations-table">';
            echo '<thead><tr><th>Ciudad</th><th>Dirección</th><th>Teléfono</th><th>Horario</th><th>Contacto</th></tr></thead>';
            echo '<tbody>';
            foreach ($locations as $location) {
                echo '<tr>';
                echo '<td>' . esc_html($location['city']) . '</td>';
                echo '<td>' . esc_html($location['address']) . '</td>';
                echo '<td>' . esc_html($location['phone']) . '</td>';
                echo '<td>' . esc_html($location['schedule']) . '</td>';
                echo '<td>' . esc_html($location['contact']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
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
            'deposit_locations' => $this->deposit_locations,
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
     * Generar referencia de depósito
     *
     * @param int $order_id ID del pedido
     * @return string
     */
    public function generate_deposit_reference($order_id) {
        $prefix = 'CD';
        $timestamp = date('YmdHis');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Validar referencia de depósito
     *
     * @param string $reference Referencia
     * @return bool
     */
    public function validate_deposit_reference($reference) {
        // Formato: CD + timestamp + random
        return preg_match('/^CD\d{14}\d{4}$/', $reference);
    }
}
