<?php
/**
 * Gestor de SMS - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar notificaciones por SMS
 */
class WCVS_SMS_Manager {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Plantillas de SMS
     *
     * @var array
     */
    private $sms_templates = array();

    /**
     * Proveedores de SMS disponibles
     *
     * @var array
     */
    private $sms_providers = array(
        'twilio' => 'Twilio',
        'nexmo' => 'Nexmo (Vonage)',
        'local' => 'Proveedor Local',
        'custom' => 'API Personalizada'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('notifications', array());
        $this->load_sms_templates();
    }

    /**
     * Cargar plantillas de SMS
     */
    private function load_sms_templates() {
        $this->sms_templates = array(
            'order_created' => 'Pedido #{order_number} creado. Total: {order_total}. Gracias por tu compra!',
            'order_processing' => 'Tu pedido #{order_number} está siendo procesado. Te mantendremos informado.',
            'order_completed' => '¡Tu pedido #{order_number} está listo! Total: {order_total}.',
            'order_cancelled' => 'Tu pedido #{order_number} ha sido cancelado. Contactanos si necesitas ayuda.',
            'payment_received' => 'Pago recibido para pedido #{order_number}. Procesando tu pedido.',
            'payment_failed' => 'Pago fallido para pedido #{order_number}. Por favor intenta nuevamente.',
            'invoice_generated' => 'Factura #{invoice_number} generada para pedido #{order_number}. Disponible para descarga.',
            'invoice_sent_to_seniat' => 'Factura #{invoice_number} enviada a SENIAT exitosamente.',
            'shipment_created' => 'Envío creado para pedido #{order_number}. Te enviaremos el tracking pronto.',
            'shipment_shipped' => '¡Tu pedido #{order_number} está en camino! Tracking: {tracking_number}',
            'shipment_delivered' => '¡Pedido #{order_number} entregado! Esperamos que disfrutes tu compra.',
            'low_stock' => 'ALERTA: Stock bajo en {product_name}. Solo quedan {stock_quantity} unidades.',
            'price_change' => 'Precio actualizado: {product_name} ahora cuesta {product_price}.',
            'currency_rate_updated' => 'Tasa de cambio actualizada: 1 USD = {exchange_rate} VES.',
            'seniat_report_generated' => 'Reporte SENIAT {report_type} generado exitosamente.'
        );
    }

    /**
     * Enviar notificación por SMS
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return bool
     */
    public function send_notification($event, $data) {
        // Verificar si el SMS está habilitado para este evento
        if (!$this->is_sms_enabled_for_event($event)) {
            return false;
        }

        // Obtener plantilla de SMS
        $template = $this->get_sms_template($event);
        if (!$template) {
            return false;
        }

        // Preparar datos para el SMS
        $sms_data = $this->prepare_sms_data($event, $data, $template);

        // Enviar SMS
        return $this->send_sms($sms_data);
    }

    /**
     * Verificar si el SMS está habilitado para el evento
     *
     * @param string $event Evento
     * @return bool
     */
    private function is_sms_enabled_for_event($event) {
        $sms_settings = $this->settings['sms'] ?? array();
        return $sms_settings['enabled'] ?? false;
    }

    /**
     * Obtener plantilla de SMS
     *
     * @param string $event Evento
     * @return string|false
     */
    private function get_sms_template($event) {
        // Verificar si hay plantilla personalizada
        $custom_template = $this->settings['sms']['templates'][$event] ?? null;
        if ($custom_template) {
            return $custom_template;
        }

        // Usar plantilla por defecto
        return $this->sms_templates[$event] ?? false;
    }

    /**
     * Preparar datos para el SMS
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @param string $template Plantilla
     * @return array
     */
    private function prepare_sms_data($event, $data, $template) {
        $sms_data = array(
            'to' => $this->get_recipient_phone($event, $data),
            'message' => $this->process_template($template, $data),
            'from' => $this->get_sender_phone()
        );

        return $sms_data;
    }

    /**
     * Obtener teléfono del destinatario
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return string|false
     */
    private function get_recipient_phone($event, $data) {
        // Para eventos de pedido, usar teléfono del cliente
        if (isset($data['order']['customer_phone'])) {
            $phone = $this->format_phone_number($data['order']['customer_phone']);
            if ($phone) {
                return $phone;
            }
        }

        // Para eventos de admin, usar teléfono del administrador
        if (in_array($event, array('low_stock', 'seniat_report_generated'))) {
            $admin_phone = $this->settings['sms']['admin_phone'] ?? '';
            return $this->format_phone_number($admin_phone);
        }

        return false;
    }

    /**
     * Formatear número de teléfono
     *
     * @param string $phone Número de teléfono
     * @return string|false
     */
    private function format_phone_number($phone) {
        if (empty($phone)) {
            return false;
        }

        // Limpiar número
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si no tiene código de país, añadir +58 para Venezuela
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '58')) {
                $phone = '+' . $phone;
            } else {
                $phone = '+58' . $phone;
            }
        }

        // Validar formato venezolano
        if (preg_match('/^\+58[0-9]{10}$/', $phone)) {
            return $phone;
        }

        return false;
    }

    /**
     * Obtener teléfono del remitente
     *
     * @return string
     */
    private function get_sender_phone() {
        $sms_settings = $this->settings['sms'] ?? array();
        return $sms_settings['sender_phone'] ?? get_bloginfo('name');
    }

    /**
     * Procesar plantilla con datos
     *
     * @param string $template Plantilla
     * @param array $data Datos
     * @return string
     */
    private function process_template($template, $data) {
        $replacements = array(
            '{site_name}' => get_bloginfo('name'),
            '{current_date}' => date_i18n('d/m/Y'),
            '{current_time}' => date_i18n('H:i')
        );

        // Añadir datos específicos según el evento
        if (isset($data['order'])) {
            $order = $data['order'];
            $replacements = array_merge($replacements, array(
                '{order_number}' => $order['number'],
                '{order_id}' => $order['id'],
                '{order_total}' => wc_price($order['total']),
                '{order_status}' => $order['status'],
                '{customer_name}' => $order['customer_name'],
                '{payment_method}' => $order['payment_method']
            ));
        }

        if (isset($data['invoice'])) {
            $invoice = $data['invoice'];
            $replacements = array_merge($replacements, array(
                '{invoice_number}' => $invoice['invoice_number'],
                '{invoice_status}' => $invoice['invoice_status']
            ));
        }

        if (isset($data['shipment'])) {
            $shipment = $data['shipment'];
            $replacements = array_merge($replacements, array(
                '{tracking_number}' => $shipment['tracking_number'],
                '{shipment_status}' => $shipment['shipment_status']
            ));
        }

        if (isset($data['product'])) {
            $product = $data['product'];
            $replacements = array_merge($replacements, array(
                '{product_name}' => $product['name'],
                '{product_price}' => wc_price($product['price']),
                '{stock_quantity}' => $product['stock_quantity']
            ));
        }

        if (isset($data['currency'])) {
            $currency = $data['currency'];
            $replacements = array_merge($replacements, array(
                '{exchange_rate}' => number_format($currency['usd_to_ves_rate'], 2)
            ));
        }

        if (isset($data['report'])) {
            $report = $data['report'];
            $replacements = array_merge($replacements, array(
                '{report_type}' => $report['type']
            ));
        }

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Enviar SMS
     *
     * @param array $sms_data Datos del SMS
     * @return bool
     */
    private function send_sms($sms_data) {
        $provider = $this->get_sms_provider();
        
        switch ($provider) {
            case 'twilio':
                return $this->send_via_twilio($sms_data);
            
            case 'nexmo':
                return $this->send_via_nexmo($sms_data);
            
            case 'local':
                return $this->send_via_local_provider($sms_data);
            
            case 'custom':
                return $this->send_via_custom_api($sms_data);
            
            default:
                return false;
        }
    }

    /**
     * Obtener proveedor de SMS configurado
     *
     * @return string
     */
    private function get_sms_provider() {
        $sms_settings = $this->settings['sms'] ?? array();
        return $sms_settings['provider'] ?? 'twilio';
    }

    /**
     * Enviar SMS vía Twilio
     *
     * @param array $sms_data Datos del SMS
     * @return bool
     */
    private function send_via_twilio($sms_data) {
        $sms_settings = $this->settings['sms'] ?? array();
        $account_sid = $sms_settings['twilio']['account_sid'] ?? '';
        $auth_token = $sms_settings['twilio']['auth_token'] ?? '';
        $from_number = $sms_settings['twilio']['from_number'] ?? '';

        if (empty($account_sid) || empty($auth_token) || empty($from_number)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Configuración de Twilio incompleta');
            return false;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";
        
        $data = array(
            'From' => $from_number,
            'To' => $sms_data['to'],
            'Body' => $sms_data['message']
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => http_build_query($data),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Error Twilio: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 201) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "SMS enviado vía Twilio a: {$sms_data['to']}");
            return true;
        } else {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Error Twilio HTTP {$response_code}: {$response_body}");
            return false;
        }
    }

    /**
     * Enviar SMS vía Nexmo
     *
     * @param array $sms_data Datos del SMS
     * @return bool
     */
    private function send_via_nexmo($sms_data) {
        $sms_settings = $this->settings['sms'] ?? array();
        $api_key = $sms_settings['nexmo']['api_key'] ?? '';
        $api_secret = $sms_settings['nexmo']['api_secret'] ?? '';
        $from_number = $sms_settings['nexmo']['from_number'] ?? '';

        if (empty($api_key) || empty($api_secret) || empty($from_number)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Configuración de Nexmo incompleta');
            return false;
        }

        $url = 'https://rest.nexmo.com/sms/json';
        
        $data = array(
            'api_key' => $api_key,
            'api_secret' => $api_secret,
            'from' => $from_number,
            'to' => $sms_data['to'],
            'text' => $sms_data['message']
        );

        $response = wp_remote_post($url, array(
            'body' => $data,
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Error Nexmo: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 200) {
            $response_data = json_decode($response_body, true);
            if (isset($response_data['messages'][0]['status']) && $response_data['messages'][0]['status'] === '0') {
                WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "SMS enviado vía Nexmo a: {$sms_data['to']}");
                return true;
            }
        }

        WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Error Nexmo HTTP {$response_code}: {$response_body}");
        return false;
    }

    /**
     * Enviar SMS vía proveedor local
     *
     * @param array $sms_data Datos del SMS
     * @return bool
     */
    private function send_via_local_provider($sms_data) {
        $sms_settings = $this->settings['sms'] ?? array();
        $local_settings = $sms_settings['local'] ?? array();
        
        $api_url = $local_settings['api_url'] ?? '';
        $api_key = $local_settings['api_key'] ?? '';
        $username = $local_settings['username'] ?? '';
        $password = $local_settings['password'] ?? '';

        if (empty($api_url)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'URL de API local no configurada');
            return false;
        }

        $data = array(
            'to' => $sms_data['to'],
            'message' => $sms_data['message'],
            'from' => $sms_data['from']
        );

        $headers = array();
        if (!empty($api_key)) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        } elseif (!empty($username) && !empty($password)) {
            $headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
        }

        $response = wp_remote_post($api_url, array(
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Error proveedor local: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code >= 200 && $response_code < 300) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "SMS enviado vía proveedor local a: {$sms_data['to']}");
            return true;
        }

        WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Error proveedor local HTTP {$response_code}");
        return false;
    }

    /**
     * Enviar SMS vía API personalizada
     *
     * @param array $sms_data Datos del SMS
     * @return bool
     */
    private function send_via_custom_api($sms_data) {
        $sms_settings = $this->settings['sms'] ?? array();
        $custom_settings = $sms_settings['custom'] ?? array();
        
        $api_url = $custom_settings['api_url'] ?? '';
        $method = $custom_settings['method'] ?? 'POST';
        $headers = $custom_settings['headers'] ?? array();
        $body_template = $custom_settings['body_template'] ?? '';

        if (empty($api_url)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'URL de API personalizada no configurada');
            return false;
        }

        // Preparar cuerpo del mensaje
        $body_data = array(
            'to' => $sms_data['to'],
            'message' => $sms_data['message'],
            'from' => $sms_data['from']
        );

        if (!empty($body_template)) {
            $body = str_replace(array_keys($body_data), array_values($body_data), $body_template);
        } else {
            $body = json_encode($body_data);
        }

        $request_args = array(
            'method' => $method,
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30
        );

        $response = wp_remote_request($api_url, $request_args);

        if (is_wp_error($response)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Error API personalizada: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code >= 200 && $response_code < 300) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "SMS enviado vía API personalizada a: {$sms_data['to']}");
            return true;
        }

        WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Error API personalizada HTTP {$response_code}");
        return false;
    }

    /**
     * Obtener plantillas de SMS disponibles
     *
     * @return array
     */
    public function get_sms_templates() {
        return $this->sms_templates;
    }

    /**
     * Obtener proveedores de SMS disponibles
     *
     * @return array
     */
    public function get_sms_providers() {
        return $this->sms_providers;
    }

    /**
     * Guardar plantilla personalizada
     *
     * @param string $event Evento
     * @param string $template Plantilla
     * @return bool
     */
    public function save_custom_template($event, $template) {
        $settings = WCVS_Core::get_instance()->get_settings();
        $notifications_settings = $settings->get('notifications', array());
        
        if (!isset($notifications_settings['sms']['templates'])) {
            $notifications_settings['sms']['templates'] = array();
        }
        
        $notifications_settings['sms']['templates'][$event] = $template;
        $settings->set('notifications', $notifications_settings);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Plantilla de SMS personalizada guardada para evento: {$event}");

        return true;
    }

    /**
     * Restaurar plantilla por defecto
     *
     * @param string $event Evento
     * @return bool
     */
    public function restore_default_template($event) {
        if (!isset($this->sms_templates[$event])) {
            return false;
        }

        $settings = WCVS_Core::get_instance()->get_settings();
        $notifications_settings = $settings->get('notifications', array());
        
        if (isset($notifications_settings['sms']['templates'][$event])) {
            unset($notifications_settings['sms']['templates'][$event]);
            $settings->set('notifications', $notifications_settings);
        }

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Plantilla de SMS restaurada por defecto para evento: {$event}");

        return true;
    }

    /**
     * Probar plantilla de SMS
     *
     * @param string $event Evento
     * @param string $test_phone Teléfono de prueba
     * @return array
     */
    public function test_template($event, $test_phone) {
        $template = $this->get_sms_template($event);
        if (!$template) {
            return array(
                'success' => false,
                'message' => __('Plantilla no encontrada', 'wcvs')
            );
        }

        // Datos de prueba
        $test_data = array(
            'order' => array(
                'number' => 'TEST-001',
                'id' => 999,
                'total' => 100.00,
                'status' => 'processing',
                'customer_name' => 'Cliente de Prueba',
                'customer_phone' => $test_phone,
                'payment_method' => 'Transferencia Bancaria'
            )
        );

        $sms_data = $this->prepare_sms_data($event, $test_data, $template);
        $sms_data['to'] = $this->format_phone_number($test_phone);

        if (!$sms_data['to']) {
            return array(
                'success' => false,
                'message' => __('Número de teléfono inválido', 'wcvs')
            );
        }

        $sent = $this->send_sms($sms_data);

        return array(
            'success' => $sent,
            'message' => $sent ? __('SMS de prueba enviado correctamente', 'wcvs') : __('Error al enviar SMS de prueba', 'wcvs')
        );
    }
}
