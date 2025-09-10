<?php
/**
 * Pasarela de pago Cashea para WooCommerce Venezuela Pro
 * Integración "Compra ahora, paga después" basada en mejores prácticas
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Gateway_Cashea extends WC_Payment_Gateway {
    
    /**
     * Propiedades de la pasarela
     */
    public $environment;
    public $api_key_production;
    public $api_key_sandbox;
    public $min_amount;
    public $max_amount;
    public $order_status_after_payment;
    public $debug_mode;
    public $webhook_url;
    
    /**
     * Constructor de la pasarela
     */
    public function __construct() {
        $this->id = "wvp_cashea";
        $this->icon = WVP_PLUGIN_URL . "assets/images/cashea-logo.png";
        $this->has_fields = false;
        $this->method_title = __("Paga con Cashea", "wvp");
        $this->method_description = __("Compra ahora, paga después con Cashea. Financiamiento flexible para tus compras.", "wvp");
        
        // Cargar configuraciones
        $this->init_form_fields();
        $this->init_settings();
        
        // Obtener configuraciones
        $this->title = $this->get_option("title");
        $this->description = $this->get_option("description");
        $this->enabled = $this->get_option("enabled");
        $this->environment = $this->get_option("environment");
        $this->api_key_production = $this->get_option("api_key_production");
        $this->api_key_sandbox = $this->get_option("api_key_sandbox");
        $this->min_amount = $this->get_option("min_amount");
        $this->max_amount = $this->get_option("max_amount");
        $this->order_status_after_payment = $this->get_option("order_status_after_payment");
        $this->debug_mode = $this->get_option("debug_mode");
        
        // Generar URL del webhook
        $this->webhook_url = home_url('/?wc-api=wvp_cashea_callback');
        
        // Hooks
        add_action("woocommerce_update_options_payment_gateways_" . $this->id, array($this, "process_admin_options"));
        add_action("woocommerce_api_wvp_cashea_callback", array($this, "handle_webhook"));
        add_action("woocommerce_receipt_" . $this->id, array($this, "receipt_page"));
    }
    
    /**
     * Definir campos de configuración
     */
    public function init_form_fields() {
        // Obtener estados de pedido de WooCommerce
        $order_statuses = wc_get_order_statuses();
        
        $this->form_fields = array(
            "enabled" => array(
                "title" => __("Activar/Desactivar", "wvp"),
                "type" => "checkbox",
                "label" => __("Activar Cashea", "wvp"),
                "default" => "no",
                "description" => __("Activar el método de pago Cashea", "wvp")
            ),
            "title" => array(
                "title" => __("Título", "wvp"),
                "type" => "text",
                "description" => __("Título que se muestra al cliente", "wvp"),
                "default" => __("Paga con Cashea", "wvp"),
                "desc_tip" => true
            ),
            "description" => array(
                "title" => __("Descripción", "wvp"),
                "type" => "textarea",
                "description" => __("Descripción que se muestra al cliente", "wvp"),
                "default" => __("Compra ahora, paga después con Cashea. Financiamiento flexible para tus compras.", "wvp"),
                "desc_tip" => true
            ),
            "environment" => array(
                "title" => __("Modo de Operación", "wvp"),
                "type" => "select",
                "description" => __("Selecciona el entorno de operación", "wvp"),
                "default" => "sandbox",
                "options" => array(
                    "sandbox" => __("Sandbox / Pruebas", "wvp"),
                    "production" => __("Producción / Real", "wvp")
                ),
                "desc_tip" => true
            ),
            "api_key_production" => array(
                "title" => __("API Key de Producción", "wvp"),
                "type" => "password",
                "description" => __("Clave API para el entorno de producción", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "api_key_sandbox" => array(
                "title" => __("API Key de Sandbox", "wvp"),
                "type" => "password",
                "description" => __("Clave API para el entorno de pruebas", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "min_amount" => array(
                "title" => __("Monto Mínimo de Compra (USD)", "wvp"),
                "type" => "price",
                "description" => __("Monto mínimo en USD para que aparezca esta pasarela. Dejar en blanco para sin límite.", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "max_amount" => array(
                "title" => __("Monto Máximo de Compra (USD)", "wvp"),
                "type" => "price",
                "description" => __("Monto máximo en USD para que aparezca esta pasarela. Dejar en blanco para sin límite.", "wvp"),
                "default" => "",
                "desc_tip" => true
            ),
            "order_status_after_payment" => array(
                "title" => __("Estado del Pedido Después del Pago", "wvp"),
                "type" => "select",
                "description" => __("Estado al que cambiar el pedido después de un pago exitoso", "wvp"),
                "default" => "wc-processing",
                "options" => $order_statuses,
                "desc_tip" => true
            ),
            "debug_mode" => array(
                "title" => __("Modo Debug", "wvp"),
                "type" => "checkbox",
                "label" => __("Activar Modo Debug (Log)", "wvp"),
                "default" => "no",
                "description" => __("Registrar todas las interacciones con la API de Cashea en el log de WooCommerce", "wvp")
            ),
            "webhook_url" => array(
                "title" => __("URL del Webhook", "wvp"),
                "type" => "text",
                "description" => __("URL que debes configurar en tu panel de Cashea", "wvp"),
                "default" => $this->webhook_url,
                "custom_attributes" => array("readonly" => "readonly")
            )
        );
    }
    
    /**
     * Procesar pago
     * 
     * @param int $order_id ID del pedido
     * @return array Resultado del pago
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            $this->log_debug("Error: Pedido no encontrado - ID: " . $order_id);
            return array(
                "result" => "failure",
                "messages" => __("Pedido no encontrado", "wvp")
            );
        }
        
        // Obtener API key según el entorno
        $api_key = ($this->environment === "production") ? $this->api_key_production : $this->api_key_sandbox;
        
        // Verificar credenciales
        if (empty($api_key)) {
            $this->log_debug("Error: API Key no configurada para el entorno: " . $this->environment);
            wc_add_notice(__("Error de configuración: API Key de Cashea no configurada", "wvp"), "error");
            return array(
                "result" => "failure"
            );
        }
        
        // Preparar datos del pedido para Cashea
        $order_data = array(
            "order_id" => $order_id,
            "amount" => $order->get_total(),
            "currency" => $order->get_currency(),
            "customer" => array(
                "name" => $order->get_billing_first_name() . " " . $order->get_billing_last_name(),
                "email" => $order->get_billing_email(),
                "phone" => $order->get_billing_phone(),
                "document" => $order->get_meta('_cedula_rif') // Cédula/RIF del cliente
            ),
            "billing_address" => array(
                "address_1" => $order->get_billing_address_1(),
                "address_2" => $order->get_billing_address_2(),
                "city" => $order->get_billing_city(),
                "state" => $order->get_billing_state(),
                "postcode" => $order->get_billing_postcode(),
                "country" => $order->get_billing_country()
            ),
            "shipping_address" => array(
                "address_1" => $order->get_shipping_address_1(),
                "address_2" => $order->get_shipping_address_2(),
                "city" => $order->get_shipping_city(),
                "state" => $order->get_shipping_state(),
                "postcode" => $order->get_shipping_postcode(),
                "country" => $order->get_shipping_country()
            ),
            "items" => $this->get_order_items($order),
            "return_url" => $this->get_return_url($order),
            "cancel_url" => wc_get_checkout_url(),
            "webhook_url" => $this->webhook_url,
            "metadata" => array(
                "woocommerce_order_id" => $order_id,
                "environment" => $this->environment
            )
        );
        
        $this->log_debug("Creando transacción en Cashea", $order_data);
        
        // Crear transacción en Cashea
        $cashea_response = $this->create_cashea_transaction($order_data, $api_key);
        
        if ($cashea_response && isset($cashea_response['success']) && $cashea_response['success']) {
            // Guardar datos de la transacción
            $order->update_meta_data("_cashea_transaction_id", $cashea_response['transaction_id']);
            $order->update_meta_data("_cashea_checkout_url", $cashea_response['checkout_url']);
            $order->update_meta_data("_cashea_environment", $this->environment);
            $order->save();
            
            $this->log_debug("Transacción creada exitosamente", $cashea_response);
            
            // Redirigir a Cashea
            return array(
                "result" => "success",
                "redirect" => $cashea_response['checkout_url']
            );
        } else {
            $error_message = isset($cashea_response['message']) ? $cashea_response['message'] : __("Error al procesar el pago con Cashea", "wvp");
            $this->log_debug("Error al crear transacción", $cashea_response);
            
            wc_add_notice($error_message, "error");
            
            return array(
                "result" => "failure"
            );
        }
    }
    
    /**
     * Crear transacción en Cashea
     * 
     * @param array $order_data Datos del pedido
     * @param string $api_key API Key de Cashea
     * @return array|false Respuesta de Cashea
     */
    private function create_cashea_transaction($order_data, $api_key) {
        $api_url = $this->environment === "production" ? 
            "https://api.cashea.com/v1/orders" : 
            "https://api-sandbox.cashea.com/v1/orders";
        
        $request_data = array(
            "amount" => $order_data["amount"],
            "currency" => $order_data["currency"],
            "external_id" => $order_data["order_id"],
            "customer" => $order_data["customer"],
            "billing_address" => $order_data["billing_address"],
            "shipping_address" => $order_data["shipping_address"],
            "items" => $order_data["items"],
            "return_url" => $order_data["return_url"],
            "cancel_url" => $order_data["cancel_url"],
            "webhook_url" => $order_data["webhook_url"],
            "metadata" => $order_data["metadata"]
        );
        
        $headers = array(
            "Content-Type" => "application/json",
            "Authorization" => "Bearer " . $api_key,
            "X-Environment" => $this->environment,
            "User-Agent" => "WooCommerce-Venezuela-Pro/" . WVP_VERSION
        );
        
        $this->log_debug("Enviando request a Cashea", array(
            "url" => $api_url,
            "headers" => $headers,
            "data" => $request_data
        ));
        
        $response = wp_remote_post($api_url, array(
            "method" => "POST",
            "headers" => $headers,
            "body" => json_encode($request_data),
            "timeout" => 30
        ));
        
        if (is_wp_error($response)) {
            $this->log_debug("Error en request a Cashea", $response->get_error_message());
            return array(
                "success" => false,
                "message" => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        $this->log_debug("Respuesta de Cashea", array(
            "code" => $response_code,
            "body" => $response_data
        ));
        
        if ($response_code === 200 || $response_code === 201) {
            return array(
                "success" => true,
                "transaction_id" => $response_data["id"] ?? $response_data["transaction_id"],
                "checkout_url" => $response_data["checkout_url"] ?? $response_data["payment_url"]
            );
        } else {
            $error_message = isset($response_data["message"]) ? $response_data["message"] : __("Error en la respuesta de Cashea", "wvp");
            if (isset($response_data["errors"])) {
                $error_message .= " - " . implode(", ", $response_data["errors"]);
            }
            return array(
                "success" => false,
                "message" => $error_message
            );
        }
    }
    
    /**
     * Obtener items del pedido para Cashea
     * 
     * @param WC_Order $order Pedido
     * @return array Items formateados
     */
    private function get_order_items($order) {
        $items = array();
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $items[] = array(
                "name" => $item->get_name(),
                "description" => $product ? $product->get_short_description() : "",
                "quantity" => $item->get_quantity(),
                "unit_price" => $item->get_subtotal() / $item->get_quantity(),
                "total_price" => $item->get_subtotal(),
                "sku" => $product ? $product->get_sku() : "",
                "category" => $product ? $this->get_product_category($product) : ""
            );
        }
        
        return $items;
    }
    
    /**
     * Obtener categoría del producto
     * 
     * @param WC_Product $product Producto
     * @return string Categoría
     */
    private function get_product_category($product) {
        $categories = wp_get_post_terms($product->get_id(), 'product_cat');
        return !empty($categories) ? $categories[0]->name : "General";
    }
    
    /**
     * Registrar en log de debug
     * 
     * @param string $message Mensaje
     * @param mixed $data Datos adicionales
     */
    private function log_debug($message, $data = null) {
        if ($this->debug_mode === "yes") {
            $log_message = "[Cashea] " . $message;
            if ($data !== null) {
                $log_message .= " - Data: " . print_r($data, true);
            }
            wc_get_logger()->info($log_message, array('source' => 'cashea'));
        }
    }
    
    /**
     * Manejar webhook de Cashea
     */
    public function handle_webhook() {
        $this->log_debug("Webhook recibido", array(
            "method" => $_SERVER['REQUEST_METHOD'],
            "headers" => getallheaders(),
            "raw_input" => file_get_contents('php://input')
        ));
        
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->log_debug("Método HTTP no permitido", $_SERVER['REQUEST_METHOD']);
            http_response_code(405);
            exit;
        }
        
        // Obtener datos del webhook
        $raw_body = file_get_contents('php://input');
        $webhook_data = json_decode($raw_body, true);
        
        if (!$webhook_data) {
            $this->log_debug("Datos del webhook inválidos", $raw_body);
            http_response_code(400);
            exit;
        }
        
        // Verificar firma del webhook
        if (!$this->verify_webhook_signature($raw_body, getallheaders())) {
            $this->log_debug("Firma del webhook inválida", $webhook_data);
            http_response_code(401);
            exit;
        }
        
        // Procesar notificación
        $this->process_webhook_notification($webhook_data);
        
        $this->log_debug("Webhook procesado exitosamente");
        http_response_code(200);
        exit;
    }
    
    /**
     * Verificar firma del webhook
     * 
     * @param string $raw_body Cuerpo crudo del webhook
     * @param array $headers Headers del webhook
     * @return bool True si la firma es válida
     */
    private function verify_webhook_signature($raw_body, $headers) {
        // Obtener firma del header
        $signature = $headers['X-Cashea-Signature'] ?? $headers['x-cashea-signature'] ?? '';
        
        if (empty($signature)) {
            $this->log_debug("Firma no encontrada en headers");
            return false;
        }
        
        // Obtener API key según el entorno
        $api_key = ($this->environment === "production") ? $this->api_key_production : $this->api_key_sandbox;
        
        // Calcular firma esperada (HMAC SHA256)
        $expected_signature = hash_hmac('sha256', $raw_body, $api_key);
        
        // Comparar firmas de forma segura
        $is_valid = hash_equals($expected_signature, $signature);
        
        $this->log_debug("Verificación de firma", array(
            "expected" => $expected_signature,
            "received" => $signature,
            "valid" => $is_valid
        ));
        
        return $is_valid;
    }
    
    /**
     * Procesar notificación del webhook
     * 
     * @param array $webhook_data Datos del webhook
     */
    private function process_webhook_notification($webhook_data) {
        $this->log_debug("Procesando notificación del webhook", $webhook_data);
        
        $transaction_id = $webhook_data['id'] ?? $webhook_data['transaction_id'] ?? '';
        $status = $webhook_data['status'] ?? '';
        $external_id = $webhook_data['external_id'] ?? '';
        $order_id = $external_id;
        
        if (empty($transaction_id) || empty($order_id)) {
            $this->log_debug("Datos requeridos faltantes", array(
                "transaction_id" => $transaction_id,
                "order_id" => $order_id
            ));
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            $this->log_debug("Pedido no encontrado", $order_id);
            return;
        }
        
        // Verificar que la transacción corresponde al pedido
        $stored_transaction_id = $order->get_meta('_cashea_transaction_id');
        if ($stored_transaction_id !== $transaction_id) {
            $this->log_debug("ID de transacción no coincide", array(
                "stored" => $stored_transaction_id,
                "received" => $transaction_id
            ));
            return;
        }
        
        // Procesar según el estado
        $this->log_debug("Procesando estado", $status);
        
        switch ($status) {
            case 'approved':
            case 'completed':
            case 'paid':
                $this->handle_payment_success($order, $transaction_id, $webhook_data);
                break;
                
            case 'rejected':
            case 'failed':
            case 'declined':
                $this->handle_payment_failure($order, $transaction_id, $webhook_data);
                break;
                
            case 'pending':
            case 'processing':
                $this->handle_payment_pending($order, $transaction_id, $webhook_data);
                break;
                
            case 'cancelled':
            case 'canceled':
                $this->handle_payment_cancelled($order, $transaction_id, $webhook_data);
                break;
                
            default:
                $this->log_debug("Estado no reconocido", $status);
                break;
        }
        
        // Guardar datos de la respuesta
        $order->update_meta_data('_cashea_webhook_data', json_encode($webhook_data));
        $order->update_meta_data('_cashea_last_webhook', current_time('mysql'));
        $order->save();
    }
    
    /**
     * Manejar pago exitoso
     */
    private function handle_payment_success($order, $transaction_id, $webhook_data) {
        $order->payment_complete($transaction_id);
        
        // Cambiar al estado configurado por el admin
        if ($this->order_status_after_payment && $this->order_status_after_payment !== 'wc-processing') {
            $order->update_status(str_replace('wc-', '', $this->order_status_after_payment));
        }
        
        $order->add_order_note(sprintf(
            __('Pago aprobado por Cashea. ID de transacción: %s', 'wvp'),
            $transaction_id
        ));
        
        $this->log_debug("Pago procesado exitosamente", array(
            "order_id" => $order->get_id(),
            "transaction_id" => $transaction_id
        ));
    }
    
    /**
     * Manejar pago fallido
     */
    private function handle_payment_failure($order, $transaction_id, $webhook_data) {
        $reason = $webhook_data['reason'] ?? $webhook_data['message'] ?? '';
        $order->update_status('failed', sprintf(
            __('Pago rechazado por Cashea. ID: %s. Razón: %s', 'wvp'),
            $transaction_id,
            $reason
        ));
        
        $this->log_debug("Pago rechazado", array(
            "order_id" => $order->get_id(),
            "transaction_id" => $transaction_id,
            "reason" => $reason
        ));
    }
    
    /**
     * Manejar pago pendiente
     */
    private function handle_payment_pending($order, $transaction_id, $webhook_data) {
        $order->update_status('pending', sprintf(
            __('Pago pendiente en Cashea. ID: %s', 'wvp'),
            $transaction_id
        ));
        
        $this->log_debug("Pago pendiente", array(
            "order_id" => $order->get_id(),
            "transaction_id" => $transaction_id
        ));
    }
    
    /**
     * Manejar pago cancelado
     */
    private function handle_payment_cancelled($order, $transaction_id, $webhook_data) {
        $order->update_status('cancelled', sprintf(
            __('Pago cancelado en Cashea. ID: %s', 'wvp'),
            $transaction_id
        ));
        
        $this->log_debug("Pago cancelado", array(
            "order_id" => $order->get_id(),
            "transaction_id" => $transaction_id
        ));
    }
    
    /**
     * Página de recibo
     * 
     * @param int $order_id ID del pedido
     */
    public function receipt_page($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        $checkout_url = $order->get_meta('_cashea_checkout_url');
        
        if ($checkout_url) {
            ?>
            <div class="wvp-cashea-receipt">
                <div class="wvp-cashea-header">
                    <h3><?php _e("Redirigiendo a Cashea...", "wvp"); ?></h3>
                    <p><?php _e("Serás redirigido automáticamente a Cashea para completar tu pago.", "wvp"); ?></p>
                </div>
                
                <div class="wvp-cashea-info">
                    <p><strong><?php _e("Pedido:", "wvp"); ?></strong> #<?php echo $order->get_id(); ?></p>
                    <p><strong><?php _e("Total:", "wvp"); ?></strong> <?php echo $order->get_formatted_order_total(); ?></p>
                    <p><strong><?php _e("Método:", "wvp"); ?></strong> <?php echo $this->title; ?></p>
                </div>
                
                <div class="wvp-cashea-actions">
                    <a href="<?php echo esc_url($checkout_url); ?>" class="button button-primary button-large">
                        <?php _e("Ir a Cashea", "wvp"); ?>
                    </a>
                    <a href="<?php echo wc_get_checkout_url(); ?>" class="button">
                        <?php _e("Volver al Checkout", "wvp"); ?>
                    </a>
                </div>
                
                <div class="wvp-cashea-loading">
                    <p><?php _e("Si no eres redirigido automáticamente, haz clic en el botón de arriba.", "wvp"); ?></p>
                </div>
                
                <style>
                .wvp-cashea-receipt {
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    background: #f9f9f9;
                }
                .wvp-cashea-header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .wvp-cashea-info {
                    background: white;
                    padding: 15px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                }
                .wvp-cashea-actions {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .wvp-cashea-actions .button {
                    margin: 0 10px;
                }
                .wvp-cashea-loading {
                    text-align: center;
                    font-style: italic;
                    color: #666;
                }
                </style>
                
                <script type="text/javascript">
                setTimeout(function() {
                    window.location.href = "<?php echo esc_url($checkout_url); ?>";
                }, 5000);
                </script>
            </div>
            <?php
        } else {
            ?>
            <div class="wvp-cashea-error">
                <h3><?php _e("Error de Configuración", "wvp"); ?></h3>
                <p><?php _e("No se pudo obtener la URL de pago de Cashea. Por favor, contacta al administrador.", "wvp"); ?></p>
                <a href="<?php echo wc_get_checkout_url(); ?>" class="button button-primary">
                    <?php _e("Volver al Checkout", "wvp"); ?>
                </a>
            </div>
            <?php
        }
    }
}
