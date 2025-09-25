<?php
/**
 * Gestor de Webhooks - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar webhooks
 */
class WCVS_Webhook_Manager {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('notifications', array());
        $this->init_database();
    }

    /**
     * Inicializar base de datos
     */
    private function init_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            $this->create_webhooks_table();
        }
    }

    /**
     * Crear tabla de webhooks
     */
    private function create_webhooks_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            url varchar(500) NOT NULL,
            events longtext NOT NULL,
            secret_key varchar(255) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            retry_count int(11) DEFAULT 0,
            max_retries int(11) DEFAULT 3,
            timeout int(11) DEFAULT 30,
            headers longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_active (is_active),
            KEY events (events(191))
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Tabla de webhooks creada');
    }

    /**
     * Crear webhook
     *
     * @param array $webhook_data Datos del webhook
     * @return int|false ID del webhook o false si falla
     */
    public function create_webhook($webhook_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        // Validar datos
        if (!$this->validate_webhook_data($webhook_data)) {
            return false;
        }
        
        // Generar clave secreta
        $secret_key = $this->generate_secret_key();
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($webhook_data['name']),
                'url' => esc_url_raw($webhook_data['url']),
                'events' => json_encode($webhook_data['events']),
                'secret_key' => $secret_key,
                'is_active' => $webhook_data['is_active'] ? 1 : 0,
                'retry_count' => 0,
                'max_retries' => $webhook_data['max_retries'] ?? 3,
                'timeout' => $webhook_data['timeout'] ?? 30,
                'headers' => json_encode($webhook_data['headers'] ?? array()),
                'created_at' => current_time('mysql')
            )
        );
        
        if ($result !== false) {
            $webhook_id = $wpdb->insert_id;
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Webhook creado: {$webhook_data['name']} (ID: {$webhook_id})");
            return $webhook_id;
        }
        
        return false;
    }

    /**
     * Validar datos del webhook
     *
     * @param array $webhook_data Datos del webhook
     * @return bool
     */
    private function validate_webhook_data($webhook_data) {
        if (empty($webhook_data['name']) || empty($webhook_data['url']) || empty($webhook_data['events'])) {
            return false;
        }
        
        if (!filter_var($webhook_data['url'], FILTER_VALIDATE_URL)) {
            return false;
        }
        
        if (!is_array($webhook_data['events'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Generar clave secreta
     *
     * @return string
     */
    private function generate_secret_key() {
        return 'wcvs_' . wp_generate_password(32, false);
    }

    /**
     * Obtener webhook por ID
     *
     * @param int $webhook_id ID del webhook
     * @return array|false
     */
    public function get_webhook($webhook_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE id = %d
        ", $webhook_id), ARRAY_A);
        
        if ($result) {
            $result['events'] = json_decode($result['events'], true);
            $result['headers'] = json_decode($result['headers'], true);
        }
        
        return $result;
    }

    /**
     * Obtener todos los webhooks
     *
     * @param array $args Argumentos de búsqueda
     * @return array
     */
    public function get_webhooks($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'is_active' => null,
            'event' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        $where_conditions = array('1=1');
        
        if ($args['is_active'] !== null) {
            $where_conditions[] = $wpdb->prepare('is_active = %d', $args['is_active']);
        }
        
        if (!empty($args['event'])) {
            $where_conditions[] = $wpdb->prepare('events LIKE %s', '%' . $args['event'] . '%');
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE {$where_clause}
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $args['limit'], $args['offset']), ARRAY_A);
        
        // Procesar datos JSON
        foreach ($results as &$result) {
            $result['events'] = json_decode($result['events'], true);
            $result['headers'] = json_decode($result['headers'], true);
        }
        
        return $results;
    }

    /**
     * Actualizar webhook
     *
     * @param int $webhook_id ID del webhook
     * @param array $webhook_data Datos del webhook
     * @return bool
     */
    public function update_webhook($webhook_id, $webhook_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        // Validar datos
        if (!$this->validate_webhook_data($webhook_data)) {
            return false;
        }
        
        $update_data = array(
            'name' => sanitize_text_field($webhook_data['name']),
            'url' => esc_url_raw($webhook_data['url']),
            'events' => json_encode($webhook_data['events']),
            'is_active' => $webhook_data['is_active'] ? 1 : 0,
            'max_retries' => $webhook_data['max_retries'] ?? 3,
            'timeout' => $webhook_data['timeout'] ?? 30,
            'headers' => json_encode($webhook_data['headers'] ?? array()),
            'updated_at' => current_time('mysql')
        );
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $webhook_id)
        );
        
        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Webhook actualizado: {$webhook_id}");
            return true;
        }
        
        return false;
    }

    /**
     * Eliminar webhook
     *
     * @param int $webhook_id ID del webhook
     * @return bool
     */
    public function delete_webhook($webhook_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $webhook_id)
        );
        
        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Webhook eliminado: {$webhook_id}");
            return true;
        }
        
        return false;
    }

    /**
     * Activar/desactivar webhook
     *
     * @param int $webhook_id ID del webhook
     * @param bool $is_active Estado activo
     * @return bool
     */
    public function toggle_webhook($webhook_id, $is_active) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'is_active' => $is_active ? 1 : 0,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $webhook_id)
        );
        
        if ($result !== false) {
            $status = $is_active ? 'activado' : 'desactivado';
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Webhook {$status}: {$webhook_id}");
            return true;
        }
        
        return false;
    }

    /**
     * Enviar webhook
     *
     * @param string $event Evento
     * @param array $data Datos del evento
     * @return array Resultados del envío
     */
    public function send_webhook($event, $data) {
        $webhooks = $this->get_webhooks(array('is_active' => 1));
        $results = array();
        
        foreach ($webhooks as $webhook) {
            if (in_array($event, $webhook['events'])) {
                $result = $this->send_single_webhook($webhook, $event, $data);
                $results[] = array(
                    'webhook_id' => $webhook['id'],
                    'webhook_name' => $webhook['name'],
                    'success' => $result['success'],
                    'response' => $result['response'],
                    'error' => $result['error']
                );
            }
        }
        
        return $results;
    }

    /**
     * Enviar webhook individual
     *
     * @param array $webhook Datos del webhook
     * @param string $event Evento
     * @param array $data Datos del evento
     * @return array
     */
    private function send_single_webhook($webhook, $event, $data) {
        $payload = array(
            'event' => $event,
            'timestamp' => current_time('mysql'),
            'data' => $data,
            'webhook_id' => $webhook['id']
        );
        
        $headers = array(
            'Content-Type' => 'application/json',
            'User-Agent' => 'WooCommerce-Venezuela-Suite/1.0',
            'X-WCVS-Event' => $event,
            'X-WCVS-Signature' => $this->generate_signature($webhook['secret_key'], json_encode($payload))
        );
        
        // Añadir headers personalizados
        if (!empty($webhook['headers'])) {
            $headers = array_merge($headers, $webhook['headers']);
        }
        
        $args = array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => json_encode($payload),
            'timeout' => $webhook['timeout'],
            'sslverify' => true
        );
        
        $response = wp_remote_request($webhook['url'], $args);
        
        if (is_wp_error($response)) {
            $this->handle_webhook_error($webhook['id'], $response->get_error_message());
            return array(
                'success' => false,
                'response' => null,
                'error' => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code >= 200 && $response_code < 300) {
            $this->reset_webhook_retry_count($webhook['id']);
            return array(
                'success' => true,
                'response' => array(
                    'code' => $response_code,
                    'body' => $response_body
                ),
                'error' => null
            );
        } else {
            $this->handle_webhook_error($webhook['id'], "HTTP {$response_code}: {$response_body}");
            return array(
                'success' => false,
                'response' => array(
                    'code' => $response_code,
                    'body' => $response_body
                ),
                'error' => "HTTP {$response_code}: {$response_body}"
            );
        }
    }

    /**
     * Generar firma del webhook
     *
     * @param string $secret_key Clave secreta
     * @param string $payload Payload del webhook
     * @return string
     */
    private function generate_signature($secret_key, $payload) {
        return 'sha256=' . hash_hmac('sha256', $payload, $secret_key);
    }

    /**
     * Manejar error del webhook
     *
     * @param int $webhook_id ID del webhook
     * @param string $error Mensaje de error
     */
    private function handle_webhook_error($webhook_id, $error) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        // Incrementar contador de reintentos
        $wpdb->query($wpdb->prepare("
            UPDATE {$table_name}
            SET retry_count = retry_count + 1
            WHERE id = %d
        ", $webhook_id));
        
        // Verificar si se debe desactivar el webhook
        $webhook = $this->get_webhook($webhook_id);
        if ($webhook && $webhook['retry_count'] >= $webhook['max_retries']) {
            $this->toggle_webhook($webhook_id, false);
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Webhook desactivado por exceso de errores: {$webhook_id}");
        }
        
        WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Error en webhook {$webhook_id}: {$error}");
    }

    /**
     * Resetear contador de reintentos
     *
     * @param int $webhook_id ID del webhook
     */
    private function reset_webhook_retry_count($webhook_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        $wpdb->update(
            $table_name,
            array('retry_count' => 0),
            array('id' => $webhook_id)
        );
    }

    /**
     * Obtener estadísticas de webhooks
     *
     * @return array
     */
    public function get_webhook_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        
        $total_webhooks = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
        ");
        
        $active_webhooks = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE is_active = 1
        ");
        
        $inactive_webhooks = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE is_active = 0
        ");
        
        $webhooks_with_errors = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE retry_count > 0
        ");
        
        return array(
            'total_webhooks' => $total_webhooks ?: 0,
            'active_webhooks' => $active_webhooks ?: 0,
            'inactive_webhooks' => $inactive_webhooks ?: 0,
            'webhooks_with_errors' => $webhooks_with_errors ?: 0
        );
    }

    /**
     * Obtener eventos disponibles
     *
     * @return array
     */
    public function get_available_events() {
        return array(
            'order_created' => 'Pedido Creado',
            'order_processing' => 'Pedido en Procesamiento',
            'order_completed' => 'Pedido Completado',
            'order_cancelled' => 'Pedido Cancelado',
            'payment_received' => 'Pago Recibido',
            'payment_failed' => 'Pago Fallido',
            'invoice_generated' => 'Factura Generada',
            'invoice_sent_to_seniat' => 'Factura Enviada a SENIAT',
            'shipment_created' => 'Envío Creado',
            'shipment_shipped' => 'Envío Despachado',
            'shipment_delivered' => 'Envío Entregado',
            'low_stock' => 'Stock Bajo',
            'price_change' => 'Cambio de Precio',
            'currency_rate_updated' => 'Tasa de Cambio Actualizada',
            'seniat_report_generated' => 'Reporte SENIAT Generado'
        );
    }

    /**
     * Probar webhook
     *
     * @param int $webhook_id ID del webhook
     * @return array
     */
    public function test_webhook($webhook_id) {
        $webhook = $this->get_webhook($webhook_id);
        
        if (!$webhook) {
            return array(
                'success' => false,
                'error' => 'Webhook no encontrado'
            );
        }
        
        $test_data = array(
            'test' => true,
            'message' => 'Este es un webhook de prueba desde WooCommerce Venezuela Suite',
            'timestamp' => current_time('mysql')
        );
        
        return $this->send_single_webhook($webhook, 'test', $test_data);
    }

    /**
     * Limpiar webhooks antiguos
     *
     * @param int $days Días de antigüedad
     * @return int Número de webhooks eliminados
     */
    public function cleanup_old_webhooks($days = 90) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_webhooks';
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM {$table_name}
            WHERE created_at < %s AND is_active = 0
        ", $cutoff_date));
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Limpiados {$deleted} webhooks antiguos");
        
        return $deleted ?: 0;
    }
}