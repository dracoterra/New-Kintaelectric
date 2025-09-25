<?php
/**
 * Pasarela de Transferencias Bancarias - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para la pasarela de transferencias bancarias venezolanas
 */
class WCVS_Gateway_Transferencias extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'wcvs_transferencias';
        $this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/icons/transferencias.png';
        $this->has_fields = true;
        $this->method_title = 'Transferencias Bancarias';
        $this->method_description = 'Permite pagos a través de transferencias bancarias venezolanas';

        // Configuración por defecto
        $this->init_form_fields();
        $this->init_settings();

        // Configuración
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->bank_accounts = $this->get_option('bank_accounts');
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
                'label' => 'Habilitar Transferencias Bancarias',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Transferencias Bancarias',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout.',
                'default' => 'Paga de forma segura usando transferencias bancarias. Recibirás instrucciones por email después de completar tu pedido.'
            ),
            'bank_accounts' => array(
                'title' => 'Cuentas Bancarias',
                'type' => 'textarea',
                'description' => 'Configura las cuentas bancarias disponibles. Una por línea en formato: Banco|Tipo|Número|Titular|RIF',
                'default' => "Banesco|Corriente|0134-1234-56-1234567890|Kinta Electric C.A.|J-12345678-9\nBanco Mercantil|Ahorros|0105-1234-56-1234567890|Kinta Electric C.A.|J-12345678-9",
                'desc_tip' => true
            ),
            'min_amount' => array(
                'title' => 'Monto Mínimo (USD)',
                'type' => 'number',
                'description' => 'Monto mínimo en USD para usar esta pasarela.',
                'default' => 1,
                'desc_tip' => true
            ),
            'max_amount' => array(
                'title' => 'Monto Máximo (USD)',
                'type' => 'number',
                'description' => 'Monto máximo en USD para usar esta pasarela.',
                'default' => 10000,
                'desc_tip' => true
            ),
            'instructions' => array(
                'title' => 'Instrucciones',
                'type' => 'textarea',
                'description' => 'Instrucciones que se mostrarán en la página de agradecimiento y en emails.',
                'default' => 'Realiza la transferencia bancaria y envía el comprobante para confirmar tu pedido.'
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
                'messages' => 'El monto del pedido no está dentro del rango permitido para transferencias bancarias.'
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
        $order->update_status('pending', 'Esperando confirmación de transferencia bancaria.');
        
        // Reducir stock
        $order->reduce_order_stock();
        
        // Limpiar carrito
        WC()->cart->empty_cart();
        
        // Enviar email con instrucciones
        $this->send_payment_instructions($order);

        // Log del pago
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Transferencia bancaria iniciada para pedido #{$order_id}");

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
        
        // Validar email
        if (empty($email) || !is_email($email)) {
            return array(
                'valid' => false,
                'message' => 'Por favor ingresa un email válido.'
            );
        }
        
        return array('valid' => true);
    }

    /**
     * Enviar instrucciones de pago
     *
     * @param WC_Order $order Pedido
     */
    private function send_payment_instructions($order) {
        $total = $order->get_total();
        $currency = $order->get_currency();
        
        $subject = "Instrucciones de Transferencia Bancaria - Pedido #{$order->get_id()}";
        
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n";
        $message .= "Gracias por tu pedido. Para completar tu transferencia bancaria, sigue estas instrucciones:\n\n";
        $message .= "1. Realiza la transferencia de {$currency} {$total}\n";
        $message .= "2. Envía el comprobante de la transferencia\n";
        $message .= "3. Espera la confirmación del pago\n\n";
        
        // Añadir información de las cuentas bancarias
        $bank_accounts = $this->get_bank_accounts();
        if (!empty($bank_accounts)) {
            $message .= "Cuentas bancarias disponibles:\n";
            foreach ($bank_accounts as $account) {
                $message .= "- {$account['bank']} ({$account['type']}): {$account['number']}\n";
                $message .= "  Titular: {$account['holder']}\n";
                $message .= "  RIF: {$account['rif']}\n\n";
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
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Instrucciones de transferencia bancaria enviadas para pedido #{$order->get_id()}");
    }

    /**
     * Obtener cuentas bancarias
     *
     * @return array
     */
    private function get_bank_accounts() {
        $accounts = array();
        $lines = explode("\n", $this->bank_accounts);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            $parts = explode('|', $line);
            if (count($parts) >= 5) {
                $accounts[] = array(
                    'bank' => trim($parts[0]),
                    'type' => trim($parts[1]),
                    'number' => trim($parts[2]),
                    'holder' => trim($parts[3]),
                    'rif' => trim($parts[4])
                );
            }
        }
        
        return $accounts;
    }

    /**
     * Página de agradecimiento
     *
     * @param int $order_id ID del pedido
     */
    public function thankyou_page($order_id) {
        if ($this->instructions) {
            echo '<div class="wcvs-transferencias-instructions">';
            echo '<h3>Instrucciones de Transferencia Bancaria</h3>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            // Mostrar cuentas bancarias
            $bank_accounts = $this->get_bank_accounts();
            if (!empty($bank_accounts)) {
                echo '<div class="wcvs-bank-accounts">';
                echo '<h4>Cuentas Bancarias Disponibles:</h4>';
                echo '<table class="wcvs-bank-accounts-table">';
                echo '<thead><tr><th>Banco</th><th>Tipo</th><th>Número</th><th>Titular</th><th>RIF</th></tr></thead>';
                echo '<tbody>';
                foreach ($bank_accounts as $account) {
                    echo '<tr>';
                    echo '<td>' . esc_html($account['bank']) . '</td>';
                    echo '<td>' . esc_html($account['type']) . '</td>';
                    echo '<td>' . esc_html($account['number']) . '</td>';
                    echo '<td>' . esc_html($account['holder']) . '</td>';
                    echo '<td>' . esc_html($account['rif']) . '</td>';
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
            echo '<h2>Instrucciones de Transferencia Bancaria</h2>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            $bank_accounts = $this->get_bank_accounts();
            if (!empty($bank_accounts)) {
                echo '<h3>Cuentas Bancarias Disponibles:</h3>';
                echo '<table>';
                echo '<thead><tr><th>Banco</th><th>Tipo</th><th>Número</th><th>Titular</th><th>RIF</th></tr></thead>';
                echo '<tbody>';
                foreach ($bank_accounts as $account) {
                    echo '<tr>';
                    echo '<td>' . esc_html($account['bank']) . '</td>';
                    echo '<td>' . esc_html($account['type']) . '</td>';
                    echo '<td>' . esc_html($account['number']) . '</td>';
                    echo '<td>' . esc_html($account['holder']) . '</td>';
                    echo '<td>' . esc_html($account['rif']) . '</td>';
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
        if (empty($this->bank_accounts)) {
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
        
        // Mostrar cuentas bancarias disponibles
        $bank_accounts = $this->get_bank_accounts();
        if (!empty($bank_accounts)) {
            echo '<div class="wcvs-bank-accounts">';
            echo '<h4>Cuentas Bancarias Disponibles:</h4>';
            echo '<table class="wcvs-bank-accounts-table">';
            echo '<thead><tr><th>Banco</th><th>Tipo</th><th>Número</th><th>Titular</th><th>RIF</th></tr></thead>';
            echo '<tbody>';
            foreach ($bank_accounts as $account) {
                echo '<tr>';
                echo '<td>' . esc_html($account['bank']) . '</td>';
                echo '<td>' . esc_html($account['type']) . '</td>';
                echo '<td>' . esc_html($account['number']) . '</td>';
                echo '<td>' . esc_html($account['holder']) . '</td>';
                echo '<td>' . esc_html($account['rif']) . '</td>';
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
        
        if (empty($email) || !is_email($email)) {
            wc_add_notice('Por favor ingresa un email válido.', 'error');
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
            'bank_accounts' => $this->bank_accounts,
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
     * Generar referencia de transferencia
     *
     * @param int $order_id ID del pedido
     * @return string
     */
    public function generate_transfer_reference($order_id) {
        $prefix = 'TR';
        $timestamp = date('YmdHis');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Validar referencia de transferencia
     *
     * @param string $reference Referencia
     * @return bool
     */
    public function validate_transfer_reference($reference) {
        // Formato: TR + timestamp + random
        return preg_match('/^TR\d{14}\d{4}$/', $reference);
    }
}
