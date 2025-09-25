<?php
/**
 * Pasarela de Pago Binance Pay - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para la pasarela de pago Binance Pay
 */
class WCVS_Gateway_Binance extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'wcvs_binance';
        $this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/icons/binance.png';
        $this->has_fields = true;
        $this->method_title = 'Binance Pay';
        $this->method_description = 'Permite pagos a través de Binance Pay para clientes venezolanos';

        // Configuración por defecto
        $this->init_form_fields();
        $this->init_settings();

        // Configuración
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->binance_id = $this->get_option('binance_id');
        $this->binance_email = $this->get_option('binance_email');
        $this->min_amount = $this->get_option('min_amount');
        $this->max_amount = $this->get_option('max_amount');
        $this->instructions = $this->get_option('instructions');
        $this->supported_coins = $this->get_option('supported_coins');

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
                'label' => 'Habilitar Binance Pay',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Binance Pay',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout.',
                'default' => 'Paga de forma segura usando Binance Pay con criptomonedas. Recibirás instrucciones por email después de completar tu pedido.'
            ),
            'binance_id' => array(
                'title' => 'ID de Binance',
                'type' => 'text',
                'description' => 'Tu ID de Binance para recibir pagos.',
                'default' => '',
                'desc_tip' => true
            ),
            'binance_email' => array(
                'title' => 'Email de Binance',
                'type' => 'email',
                'description' => 'Email asociado a tu cuenta de Binance.',
                'default' => '',
                'desc_tip' => true
            ),
            'supported_coins' => array(
                'title' => 'Criptomonedas Soportadas',
                'type' => 'multiselect',
                'description' => 'Selecciona las criptomonedas que aceptas.',
                'options' => array(
                    'USDT' => 'USDT (Tether)',
                    'USDC' => 'USDC (USD Coin)',
                    'BTC' => 'BTC (Bitcoin)',
                    'ETH' => 'ETH (Ethereum)',
                    'BNB' => 'BNB (Binance Coin)',
                    'BUSD' => 'BUSD (Binance USD)'
                ),
                'default' => array('USDT', 'USDC')
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
                'default' => 'Realiza el pago a través de Binance Pay y envía el comprobante con tu ID de Binance para confirmar tu pedido.'
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
                'messages' => 'El monto del pedido no está dentro del rango permitido para Binance Pay.'
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
        $order->update_status('pending', 'Esperando confirmación de pago Binance Pay.');
        
        // Reducir stock
        $order->reduce_order_stock();
        
        // Limpiar carrito
        WC()->cart->empty_cart();
        
        // Enviar email con instrucciones
        $this->send_payment_instructions($order);

        // Log del pago
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Pago Binance Pay iniciado para pedido #{$order_id}");

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
        
        $subject = "Instrucciones de Pago Binance Pay - Pedido #{$order->get_id()}";
        
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n";
        $message .= "Gracias por tu pedido. Para completar tu pago con Binance Pay, sigue estas instrucciones:\n\n";
        $message .= "1. Realiza el pago de {$currency} {$total} a través de Binance Pay\n";
        $message .= "2. Envía el comprobante de pago con tu ID de Binance\n";
        $message .= "3. Espera la confirmación del pago\n\n";
        
        // Añadir información de criptomonedas soportadas
        if (!empty($this->supported_coins)) {
            $message .= "Criptomonedas aceptadas: " . implode(', ', $this->supported_coins) . "\n\n";
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
        WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Instrucciones de pago Binance Pay enviadas para pedido #{$order->get_id()}");
    }

    /**
     * Página de agradecimiento
     *
     * @param int $order_id ID del pedido
     */
    public function thankyou_page($order_id) {
        if ($this->instructions) {
            echo '<div class="wcvs-binance-instructions">';
            echo '<h3>Instrucciones de Pago Binance Pay</h3>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            // Mostrar criptomonedas soportadas
            if (!empty($this->supported_coins)) {
                echo '<div class="wcvs-supported-coins">';
                echo '<h4>Criptomonedas Aceptadas:</h4>';
                echo '<ul>';
                foreach ($this->supported_coins as $coin) {
                    echo '<li>' . esc_html($coin) . '</li>';
                }
                echo '</ul>';
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
            echo '<h2>Instrucciones de Pago Binance Pay</h2>';
            echo '<p>' . wp_kses_post($this->instructions) . '</p>';
            
            if (!empty($this->supported_coins)) {
                echo '<p><strong>Criptomonedas aceptadas:</strong> ' . implode(', ', $this->supported_coins) . '</p>';
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
        if (empty($this->binance_id) || empty($this->binance_email)) {
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
        
        // Añadir información de criptomonedas soportadas
        if (!empty($this->supported_coins)) {
            $description .= '<br><small>Criptomonedas: ' . implode(', ', $this->supported_coins) . '</small>';
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
        
        // Mostrar información de la cuenta Binance
        if (!empty($this->binance_id)) {
            echo '<div class="wcvs-binance-info">';
            echo '<p><strong>ID de Binance:</strong> ' . esc_html($this->binance_id) . '</p>';
            if (!empty($this->binance_email)) {
                echo '<p><strong>Email:</strong> ' . esc_html($this->binance_email) . '</p>';
            }
            echo '</div>';
        }
        
        // Mostrar criptomonedas soportadas
        if (!empty($this->supported_coins)) {
            echo '<div class="wcvs-supported-coins">';
            echo '<h4>Criptomonedas Aceptadas:</h4>';
            echo '<ul>';
            foreach ($this->supported_coins as $coin) {
                echo '<li>' . esc_html($coin) . '</li>';
            }
            echo '</ul>';
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
            'binance_id' => $this->binance_id,
            'binance_email' => $this->binance_email,
            'supported_coins' => $this->supported_coins,
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
     * Obtener tasa de conversión de criptomoneda
     *
     * @param string $coin Criptomoneda
     * @return float|false
     */
    public function get_crypto_rate($coin) {
        // Implementar obtención de tasa de criptomoneda
        // Por ahora retornar tasa fija
        $rates = array(
            'USDT' => 1.00,
            'USDC' => 1.00,
            'BTC' => 0.000025, // Ejemplo
            'ETH' => 0.0004,   // Ejemplo
            'BNB' => 0.003,     // Ejemplo
            'BUSD' => 1.00
        );
        
        return $rates[$coin] ?? false;
    }

    /**
     * Calcular monto en criptomoneda
     *
     * @param float $usd_amount Monto en USD
     * @param string $coin Criptomoneda
     * @return float|false
     */
    public function calculate_crypto_amount($usd_amount, $coin) {
        $rate = $this->get_crypto_rate($coin);
        if (!$rate) {
            return false;
        }
        
        return $usd_amount / $rate;
    }
}
