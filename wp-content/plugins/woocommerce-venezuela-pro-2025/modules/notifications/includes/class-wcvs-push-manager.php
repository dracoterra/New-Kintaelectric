<?php
/**
 * Gestor de Notificaciones Push - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar notificaciones push
 */
class WCVS_Push_Manager {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Plantillas de notificaciones push
     *
     * @var array
     */
    private $push_templates = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('notifications', array());
        $this->load_push_templates();
    }

    /**
     * Cargar plantillas de notificaciones push
     */
    private function load_push_templates() {
        $this->push_templates = array(
            'order_created' => array(
                'title' => 'Nuevo Pedido',
                'body' => 'Pedido #{order_number} creado por {customer_name}. Total: {order_total}',
                'icon' => 'shopping-cart',
                'badge' => '1',
                'sound' => 'default',
                'data' => array(
                    'type' => 'order',
                    'order_id' => '{order_id}',
                    'url' => '{order_url}'
                )
            ),
            'order_processing' => array(
                'title' => 'Pedido en Proceso',
                'body' => 'Tu pedido #{order_number} está siendo procesado',
                'icon' => 'clock',
                'badge' => '1',
                'sound' => 'default',
                'data' => array(
                    'type' => 'order',
                    'order_id' => '{order_id}',
                    'url' => '{order_url}'
                )
            ),
            'order_completed' => array(
                'title' => 'Pedido Completado',
                'body' => '¡Tu pedido #{order_number} está listo!',
                'icon' => 'check-circle',
                'badge' => '1',
                'sound' => 'success',
                'data' => array(
                    'type' => 'order',
                    'order_id' => '{order_id}',
                    'url' => '{order_url}'
                )
            ),
            'order_cancelled' => array(
                'title' => 'Pedido Cancelado',
                'body' => 'Tu pedido #{order_number} ha sido cancelado',
                'icon' => 'x-circle',
                'badge' => '1',
                'sound' => 'error',
                'data' => array(
                    'type' => 'order',
                    'order_id' => '{order_id}',
                    'url' => '{order_url}'
                )
            ),
            'payment_received' => array(
                'title' => 'Pago Recibido',
                'body' => 'Pago confirmado para pedido #{order_number}',
                'icon' => 'credit-card',
                'badge' => '1',
                'sound' => 'success',
                'data' => array(
                    'type' => 'payment',
                    'order_id' => '{order_id}',
                    'url' => '{order_url}'
                )
            ),
            'payment_failed' => array(
                'title' => 'Pago Fallido',
                'body' => 'Problema con el pago del pedido #{order_number}',
                'icon' => 'alert-circle',
                'badge' => '1',
                'sound' => 'error',
                'data' => array(
                    'type' => 'payment',
                    'order_id' => '{order_id}',
                    'url' => '{order_url}'
                )
            ),
            'invoice_generated' => array(
                'title' => 'Factura Generada',
                'body' => 'Factura #{invoice_number} disponible para descarga',
                'icon' => 'file-text',
                'badge' => '1',
                'sound' => 'default',
                'data' => array(
                    'type' => 'invoice',
                    'invoice_number' => '{invoice_number}',
                    'url' => '{invoice_url}'
                )
            ),
            'shipment_shipped' => array(
                'title' => 'Envío Despachado',
                'body' => 'Tu pedido #{order_number} está en camino. Tracking: {tracking_number}',
                'icon' => 'truck',
                'badge' => '1',
                'sound' => 'default',
                'data' => array(
                    'type' => 'shipment',
                    'order_id' => '{order_id}',
                    'tracking_number' => '{tracking_number}',
                    'url' => '{tracking_url}'
                )
            ),
            'shipment_delivered' => array(
                'title' => 'Pedido Entregado',
                'body' => '¡Tu pedido #{order_number} ha sido entregado!',
                'icon' => 'package',
                'badge' => '1',
                'sound' => 'success',
                'data' => array(
                    'type' => 'shipment',
                    'order_id' => '{order_id}',
                    'url' => '{order_url}'
                )
            ),
            'low_stock' => array(
                'title' => 'Stock Bajo',
                'body' => 'Alerta: {product_name} tiene stock bajo ({stock_quantity} unidades)',
                'icon' => 'alert-triangle',
                'badge' => '1',
                'sound' => 'warning',
                'data' => array(
                    'type' => 'stock',
                    'product_id' => '{product_id}',
                    'url' => '{product_url}'
                )
            ),
            'currency_rate_updated' => array(
                'title' => 'Tasa de Cambio Actualizada',
                'body' => 'Nueva tasa: 1 USD = {exchange_rate} VES',
                'icon' => 'trending-up',
                'badge' => '1',
                'sound' => 'default',
                'data' => array(
                    'type' => 'currency',
                    'url' => '{site_url}'
                )
            )
        );
    }

    /**
     * Enviar notificación push
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return bool
     */
    public function send_notification($event, $data) {
        // Verificar si las notificaciones push están habilitadas
        if (!$this->is_push_enabled_for_event($event)) {
            return false;
        }

        // Obtener plantilla de push
        $template = $this->get_push_template($event);
        if (!$template) {
            return false;
        }

        // Preparar datos para la notificación push
        $push_data = $this->prepare_push_data($event, $data, $template);

        // Enviar notificación push
        return $this->send_push_notification($push_data);
    }

    /**
     * Verificar si las notificaciones push están habilitadas para el evento
     *
     * @param string $event Evento
     * @return bool
     */
    private function is_push_enabled_for_event($event) {
        $push_settings = $this->settings['push'] ?? array();
        return $push_settings['enabled'] ?? false;
    }

    /**
     * Obtener plantilla de notificación push
     *
     * @param string $event Evento
     * @return array|false
     */
    private function get_push_template($event) {
        // Verificar si hay plantilla personalizada
        $custom_template = $this->settings['push']['templates'][$event] ?? null;
        if ($custom_template) {
            return $custom_template;
        }

        // Usar plantilla por defecto
        return $this->push_templates[$event] ?? false;
    }

    /**
     * Preparar datos para la notificación push
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @param array $template Plantilla
     * @return array
     */
    private function prepare_push_data($event, $data, $template) {
        $push_data = array(
            'title' => $this->process_template($template['title'], $data),
            'body' => $this->process_template($template['body'], $data),
            'icon' => $template['icon'],
            'badge' => $template['badge'],
            'sound' => $template['sound'],
            'data' => $this->process_template_data($template['data'], $data),
            'actions' => $this->get_push_actions($event, $data),
            'tag' => $this->get_push_tag($event),
            'requireInteraction' => $this->should_require_interaction($event),
            'silent' => $this->should_be_silent($event)
        );

        return $push_data;
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
            '{site_url}' => get_site_url(),
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
                '{customer_name}' => $order['customer_name'],
                '{order_url}' => $this->get_order_url($order['id'])
            ));
        }

        if (isset($data['invoice'])) {
            $invoice = $data['invoice'];
            $replacements = array_merge($replacements, array(
                '{invoice_number}' => $invoice['invoice_number'],
                '{invoice_url}' => $this->get_invoice_url($invoice['invoice_number'])
            ));
        }

        if (isset($data['shipment'])) {
            $shipment = $data['shipment'];
            $replacements = array_merge($replacements, array(
                '{tracking_number}' => $shipment['tracking_number'],
                '{tracking_url}' => $this->get_tracking_url($shipment['tracking_number'])
            ));
        }

        if (isset($data['product'])) {
            $product = $data['product'];
            $replacements = array_merge($replacements, array(
                '{product_name}' => $product['name'],
                '{product_id}' => $product['id'],
                '{stock_quantity}' => $product['stock_quantity'],
                '{product_url}' => $this->get_product_url($product['id'])
            ));
        }

        if (isset($data['currency'])) {
            $currency = $data['currency'];
            $replacements = array_merge($replacements, array(
                '{exchange_rate}' => number_format($currency['usd_to_ves_rate'], 2)
            ));
        }

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Procesar datos de la plantilla
     *
     * @param array $template_data Datos de la plantilla
     * @param array $data Datos
     * @return array
     */
    private function process_template_data($template_data, $data) {
        $processed_data = array();
        
        foreach ($template_data as $key => $value) {
            $processed_data[$key] = $this->process_template($value, $data);
        }
        
        return $processed_data;
    }

    /**
     * Obtener acciones de la notificación push
     *
     * @param string $event Evento
     * @param array $data Datos
     * @return array
     */
    private function get_push_actions($event, $data) {
        $actions = array();

        switch ($event) {
            case 'order_created':
            case 'order_processing':
            case 'order_completed':
            case 'order_cancelled':
                if (isset($data['order'])) {
                    $actions[] = array(
                        'action' => 'view_order',
                        'title' => 'Ver Pedido',
                        'url' => $this->get_order_url($data['order']['id'])
                    );
                }
                break;
            
            case 'invoice_generated':
                if (isset($data['invoice'])) {
                    $actions[] = array(
                        'action' => 'download_invoice',
                        'title' => 'Descargar Factura',
                        'url' => $this->get_invoice_url($data['invoice']['invoice_number'])
                    );
                }
                break;
            
            case 'shipment_shipped':
                if (isset($data['shipment'])) {
                    $actions[] = array(
                        'action' => 'track_shipment',
                        'title' => 'Rastrear Envío',
                        'url' => $this->get_tracking_url($data['shipment']['tracking_number'])
                    );
                }
                break;
            
            case 'low_stock':
                if (isset($data['product'])) {
                    $actions[] = array(
                        'action' => 'view_product',
                        'title' => 'Ver Producto',
                        'url' => $this->get_product_url($data['product']['id'])
                    );
                }
                break;
        }

        // Añadir acción por defecto
        $actions[] = array(
            'action' => 'dismiss',
            'title' => 'Cerrar'
        );

        return $actions;
    }

    /**
     * Obtener tag de la notificación push
     *
     * @param string $event Evento
     * @return string
     */
    private function get_push_tag($event) {
        $tags = array(
            'order_created' => 'order',
            'order_processing' => 'order',
            'order_completed' => 'order',
            'order_cancelled' => 'order',
            'payment_received' => 'payment',
            'payment_failed' => 'payment',
            'invoice_generated' => 'invoice',
            'shipment_shipped' => 'shipment',
            'shipment_delivered' => 'shipment',
            'low_stock' => 'stock',
            'currency_rate_updated' => 'currency'
        );

        return $tags[$event] ?? 'general';
    }

    /**
     * Verificar si la notificación debe requerir interacción
     *
     * @param string $event Evento
     * @return bool
     */
    private function should_require_interaction($event) {
        $require_interaction_events = array(
            'payment_failed',
            'order_cancelled',
            'low_stock'
        );

        return in_array($event, $require_interaction_events);
    }

    /**
     * Verificar si la notificación debe ser silenciosa
     *
     * @param string $event Evento
     * @return bool
     */
    private function should_be_silent($event) {
        $silent_events = array(
            'currency_rate_updated'
        );

        return in_array($event, $silent_events);
    }

    /**
     * Enviar notificación push
     *
     * @param array $push_data Datos de la notificación push
     * @return bool
     */
    private function send_push_notification($push_data) {
        // Obtener tokens de dispositivos
        $device_tokens = $this->get_device_tokens();
        
        if (empty($device_tokens)) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'No hay dispositivos registrados para notificaciones push');
            return false;
        }

        // Enviar a cada dispositivo
        $success_count = 0;
        foreach ($device_tokens as $token) {
            if ($this->send_to_device($token, $push_data)) {
                $success_count++;
            }
        }

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Notificación push enviada a {$success_count} de " . count($device_tokens) . " dispositivos");

        return $success_count > 0;
    }

    /**
     * Obtener tokens de dispositivos
     *
     * @return array
     */
    private function get_device_tokens() {
        global $wpdb;
        
        $tokens = $wpdb->get_col("
            SELECT device_token
            FROM {$wpdb->prefix}wcvs_push_tokens
            WHERE is_active = 1
        ");

        return $tokens ?: array();
    }

    /**
     * Enviar notificación a un dispositivo específico
     *
     * @param string $device_token Token del dispositivo
     * @param array $push_data Datos de la notificación
     * @return bool
     */
    private function send_to_device($device_token, $push_data) {
        $push_settings = $this->settings['push'] ?? array();
        $vapid_public_key = $push_settings['vapid']['public_key'] ?? '';
        $vapid_private_key = $push_settings['vapid']['private_key'] ?? '';

        if (empty($vapid_public_key) || empty($vapid_private_key)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Claves VAPID no configuradas');
            return false;
        }

        // Preparar payload
        $payload = array(
            'title' => $push_data['title'],
            'body' => $push_data['body'],
            'icon' => $push_data['icon'],
            'badge' => $push_data['badge'],
            'sound' => $push_data['sound'],
            'data' => $push_data['data'],
            'actions' => $push_data['actions'],
            'tag' => $push_data['tag'],
            'requireInteraction' => $push_data['requireInteraction'],
            'silent' => $push_data['silent']
        );

        // Enviar usando Web Push
        return $this->send_web_push($device_token, $payload, $vapid_public_key, $vapid_private_key);
    }

    /**
     * Enviar notificación usando Web Push
     *
     * @param string $endpoint Endpoint del dispositivo
     * @param array $payload Payload de la notificación
     * @param string $vapid_public_key Clave pública VAPID
     * @param string $vapid_private_key Clave privada VAPID
     * @return bool
     */
    private function send_web_push($endpoint, $payload, $vapid_public_key, $vapid_private_key) {
        // Por simplicidad, aquí se implementaría la lógica de Web Push
        // En producción se usaría una librería como web-push-php
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->generate_jwt_token($vapid_public_key, $vapid_private_key)
        );

        $response = wp_remote_post($endpoint, array(
            'headers' => $headers,
            'body' => json_encode($payload),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Error Web Push: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code >= 200 && $response_code < 300) {
            return true;
        }

        WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Error Web Push HTTP {$response_code}");
        return false;
    }

    /**
     * Generar token JWT para VAPID
     *
     * @param string $vapid_public_key Clave pública VAPID
     * @param string $vapid_private_key Clave privada VAPID
     * @return string
     */
    private function generate_jwt_token($vapid_public_key, $vapid_private_key) {
        // Implementación simplificada de JWT para VAPID
        // En producción se usaría una librería JWT completa
        
        $header = json_encode(array(
            'typ' => 'JWT',
            'alg' => 'ES256'
        ));

        $payload = json_encode(array(
            'aud' => 'https://fcm.googleapis.com',
            'exp' => time() + 3600,
            'sub' => 'mailto:' . get_option('admin_email')
        ));

        $header_encoded = base64url_encode($header);
        $payload_encoded = base64url_encode($payload);
        
        $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $vapid_private_key, true);
        $signature_encoded = base64url_encode($signature);

        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }

    /**
     * Obtener URL del pedido
     *
     * @param int $order_id ID del pedido
     * @return string
     */
    private function get_order_url($order_id) {
        return admin_url('post.php?post=' . $order_id . '&action=edit');
    }

    /**
     * Obtener URL de la factura
     *
     * @param string $invoice_number Número de factura
     * @return string
     */
    private function get_invoice_url($invoice_number) {
        return admin_url('admin-ajax.php?action=wcvs_download_invoice&invoice_number=' . $invoice_number);
    }

    /**
     * Obtener URL de seguimiento
     *
     * @param string $tracking_number Número de seguimiento
     * @return string
     */
    private function get_tracking_url($tracking_number) {
        return get_site_url() . '/tracking/' . $tracking_number;
    }

    /**
     * Obtener URL del producto
     *
     * @param int $product_id ID del producto
     * @return string
     */
    private function get_product_url($product_id) {
        return get_permalink($product_id);
    }

    /**
     * Registrar token de dispositivo
     *
     * @param string $device_token Token del dispositivo
     * @param string $user_agent User agent del dispositivo
     * @return bool
     */
    public function register_device_token($device_token, $user_agent = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_push_tokens';
        
        // Verificar si el token ya existe
        $existing_token = $wpdb->get_var($wpdb->prepare("
            SELECT id FROM {$table_name}
            WHERE device_token = %s
        ", $device_token));

        if ($existing_token) {
            // Actualizar token existente
            $result = $wpdb->update(
                $table_name,
                array(
                    'is_active' => 1,
                    'last_used' => current_time('mysql'),
                    'user_agent' => $user_agent
                ),
                array('device_token' => $device_token)
            );
        } else {
            // Insertar nuevo token
            $result = $wpdb->insert(
                $table_name,
                array(
                    'device_token' => $device_token,
                    'user_agent' => $user_agent,
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'last_used' => current_time('mysql')
                )
            );
        }

        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Token de dispositivo registrado: ' . substr($device_token, 0, 20) . '...');
            return true;
        }

        return false;
    }

    /**
     * Desregistrar token de dispositivo
     *
     * @param string $device_token Token del dispositivo
     * @return bool
     */
    public function unregister_device_token($device_token) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_push_tokens';
        
        $result = $wpdb->update(
            $table_name,
            array('is_active' => 0),
            array('device_token' => $device_token)
        );

        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Token de dispositivo desregistrado: ' . substr($device_token, 0, 20) . '...');
            return true;
        }

        return false;
    }

    /**
     * Obtener plantillas de notificaciones push disponibles
     *
     * @return array
     */
    public function get_push_templates() {
        return $this->push_templates;
    }

    /**
     * Guardar plantilla personalizada
     *
     * @param string $event Evento
     * @param array $template Plantilla
     * @return bool
     */
    public function save_custom_template($event, $template) {
        $settings = WCVS_Core::get_instance()->get_settings();
        $notifications_settings = $settings->get('notifications', array());
        
        if (!isset($notifications_settings['push']['templates'])) {
            $notifications_settings['push']['templates'] = array();
        }
        
        $notifications_settings['push']['templates'][$event] = $template;
        $settings->set('notifications', $notifications_settings);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Plantilla de push personalizada guardada para evento: {$event}");

        return true;
    }

    /**
     * Restaurar plantilla por defecto
     *
     * @param string $event Evento
     * @return bool
     */
    public function restore_default_template($event) {
        if (!isset($this->push_templates[$event])) {
            return false;
        }

        $settings = WCVS_Core::get_instance()->get_settings();
        $notifications_settings = $settings->get('notifications', array());
        
        if (isset($notifications_settings['push']['templates'][$event])) {
            unset($notifications_settings['push']['templates'][$event]);
            $settings->set('notifications', $notifications_settings);
        }

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Plantilla de push restaurada por defecto para evento: {$event}");

        return true;
    }
}

/**
 * Función auxiliar para codificación base64 URL-safe
 *
 * @param string $data Datos a codificar
 * @return string
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
