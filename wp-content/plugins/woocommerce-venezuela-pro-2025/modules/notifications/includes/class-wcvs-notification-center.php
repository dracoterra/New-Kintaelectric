<?php
/**
 * Centro de Notificaciones - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar el centro de notificaciones
 */
class WCVS_Notification_Center {

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
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            $this->create_notifications_table();
        }
    }

    /**
     * Crear tabla de notificaciones
     */
    private function create_notifications_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            notification_type varchar(50) NOT NULL,
            event varchar(100) NOT NULL,
            title varchar(255) NOT NULL,
            message text NOT NULL,
            data longtext,
            is_read tinyint(1) DEFAULT 0,
            user_id bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            read_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY notification_type (notification_type),
            KEY event (event),
            KEY is_read (is_read),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Tabla de notificaciones creada');
    }

    /**
     * Añadir notificación
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return int|false ID de la notificación o false si falla
     */
    public function add_notification($event, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        // Preparar datos de la notificación
        $notification_data = $this->prepare_notification_data($event, $data);
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'notification_type' => $notification_data['type'],
                'event' => $event,
                'title' => $notification_data['title'],
                'message' => $notification_data['message'],
                'data' => json_encode($notification_data['data']),
                'user_id' => $notification_data['user_id'],
                'created_at' => current_time('mysql')
            )
        );
        
        if ($result !== false) {
            $notification_id = $wpdb->insert_id;
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Notificación añadida: {$event} (ID: {$notification_id})");
            return $notification_id;
        }
        
        return false;
    }

    /**
     * Preparar datos de la notificación
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return array
     */
    private function prepare_notification_data($event, $data) {
        $notification_data = array(
            'type' => 'admin',
            'title' => $this->get_notification_title($event, $data),
            'message' => $this->get_notification_message($event, $data),
            'data' => $data,
            'user_id' => null
        );

        // Determinar tipo de notificación y usuario
        switch ($event) {
            case 'order_created':
            case 'order_processing':
            case 'order_completed':
            case 'order_cancelled':
            case 'payment_received':
            case 'payment_failed':
                $notification_data['type'] = 'order';
                break;
            
            case 'invoice_generated':
            case 'invoice_sent_to_seniat':
                $notification_data['type'] = 'invoice';
                break;
            
            case 'shipment_created':
            case 'shipment_shipped':
            case 'shipment_delivered':
                $notification_data['type'] = 'shipment';
                break;
            
            case 'low_stock':
            case 'price_change':
                $notification_data['type'] = 'product';
                break;
            
            case 'currency_rate_updated':
                $notification_data['type'] = 'currency';
                break;
            
            case 'seniat_report_generated':
                $notification_data['type'] = 'report';
                break;
        }

        return $notification_data;
    }

    /**
     * Obtener título de la notificación
     *
     * @param string $event Evento
     * @param array $data Datos
     * @return string
     */
    private function get_notification_title($event, $data) {
        $titles = array(
            'order_created' => 'Nuevo Pedido',
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

        $title = $titles[$event] ?? 'Notificación';
        
        // Personalizar título con datos específicos
        if (isset($data['order'])) {
            $title .= ' #' . $data['order']['number'];
        } elseif (isset($data['invoice'])) {
            $title .= ' #' . $data['invoice']['invoice_number'];
        } elseif (isset($data['product'])) {
            $title .= ': ' . $data['product']['name'];
        }

        return $title;
    }

    /**
     * Obtener mensaje de la notificación
     *
     * @param string $event Evento
     * @param array $data Datos
     * @return string
     */
    private function get_notification_message($event, $data) {
        $messages = array(
            'order_created' => 'Se ha creado un nuevo pedido por {customer_name}. Total: {order_total}',
            'order_processing' => 'El pedido #{order_number} está siendo procesado',
            'order_completed' => 'El pedido #{order_number} ha sido completado',
            'order_cancelled' => 'El pedido #{order_number} ha sido cancelado',
            'payment_received' => 'Se ha recibido el pago para el pedido #{order_number}',
            'payment_failed' => 'Ha fallado el pago para el pedido #{order_number}',
            'invoice_generated' => 'Se ha generado la factura #{invoice_number}',
            'invoice_sent_to_seniat' => 'La factura #{invoice_number} ha sido enviada a SENIAT',
            'shipment_created' => 'Se ha creado un envío para el pedido #{order_number}',
            'shipment_shipped' => 'El pedido #{order_number} ha sido despachado. Tracking: {tracking_number}',
            'shipment_delivered' => 'El pedido #{order_number} ha sido entregado',
            'low_stock' => 'El producto {product_name} tiene stock bajo ({stock_quantity} unidades)',
            'price_change' => 'El precio del producto {product_name} ha cambiado',
            'currency_rate_updated' => 'La tasa de cambio USD a VES ha sido actualizada: {exchange_rate}',
            'seniat_report_generated' => 'Se ha generado el reporte SENIAT {report_type}'
        );

        $message = $messages[$event] ?? 'Nueva notificación';
        
        // Procesar plantilla con datos
        $message = $this->process_message_template($message, $data);
        
        return $message;
    }

    /**
     * Procesar plantilla de mensaje con datos
     *
     * @param string $template Plantilla
     * @param array $data Datos
     * @return string
     */
    private function process_message_template($template, $data) {
        $replacements = array();

        if (isset($data['order'])) {
            $order = $data['order'];
            $replacements = array_merge($replacements, array(
                '{order_number}' => $order['number'],
                '{order_total}' => wc_price($order['total']),
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
                '{stock_quantity}' => $product['stock_quantity'],
                '{product_price}' => wc_price($product['price'])
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
     * Obtener notificaciones
     *
     * @param array $args Argumentos de búsqueda
     * @return array
     */
    public function get_notifications($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'type' => '',
            'event' => '',
            'is_read' => null,
            'user_id' => null,
            'date_from' => '',
            'date_to' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        $where_conditions = array('1=1');
        
        if (!empty($args['type'])) {
            $where_conditions[] = $wpdb->prepare('notification_type = %s', $args['type']);
        }
        
        if (!empty($args['event'])) {
            $where_conditions[] = $wpdb->prepare('event = %s', $args['event']);
        }
        
        if ($args['is_read'] !== null) {
            $where_conditions[] = $wpdb->prepare('is_read = %d', $args['is_read']);
        }
        
        if ($args['user_id'] !== null) {
            $where_conditions[] = $wpdb->prepare('user_id = %d', $args['user_id']);
        }
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = $wpdb->prepare('created_at >= %s', $args['date_from'] . ' 00:00:00');
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = $wpdb->prepare('created_at <= %s', $args['date_to'] . ' 23:59:59');
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
            $result['data'] = json_decode($result['data'], true);
        }
        
        return $results;
    }

    /**
     * Marcar notificación como leída
     *
     * @param int $notification_id ID de la notificación
     * @return bool
     */
    public function mark_as_read($notification_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'is_read' => 1,
                'read_at' => current_time('mysql')
            ),
            array('id' => $notification_id)
        );
        
        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Notificación marcada como leída: {$notification_id}");
            return true;
        }
        
        return false;
    }

    /**
     * Marcar todas las notificaciones como leídas
     *
     * @param string $type Tipo de notificación (opcional)
     * @return bool
     */
    public function mark_all_as_read($type = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        $where_conditions = array('is_read = 0');
        if (!empty($type)) {
            $where_conditions[] = $wpdb->prepare('notification_type = %s', $type);
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $result = $wpdb->query($wpdb->prepare("
            UPDATE {$table_name}
            SET is_read = 1, read_at = %s
            WHERE {$where_clause}
        ", current_time('mysql')));
        
        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Marcadas como leídas {$result} notificaciones");
            return true;
        }
        
        return false;
    }

    /**
     * Eliminar notificación
     *
     * @param int $notification_id ID de la notificación
     * @return bool
     */
    public function delete_notification($notification_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $notification_id)
        );
        
        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Notificación eliminada: {$notification_id}");
            return true;
        }
        
        return false;
    }

    /**
     * Eliminar notificaciones antiguas
     *
     * @param int $days Días de antigüedad
     * @param string $type Tipo de notificación (opcional)
     * @return int Número de notificaciones eliminadas
     */
    public function cleanup_old_notifications($days = 30, $type = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $where_conditions = array($wpdb->prepare('created_at < %s', $cutoff_date));
        if (!empty($type)) {
            $where_conditions[] = $wpdb->prepare('notification_type = %s', $type);
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $deleted = $wpdb->query("
            DELETE FROM {$table_name}
            WHERE {$where_clause}
        ");
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Limpiadas {$deleted} notificaciones antiguas");
        
        return $deleted ?: 0;
    }

    /**
     * Obtener estadísticas de notificaciones
     *
     * @return array
     */
    public function get_notification_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        $total_notifications = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
        ");
        
        $unread_notifications = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE is_read = 0
        ");
        
        $notifications_by_type = $wpdb->get_results("
            SELECT notification_type, COUNT(*) as count
            FROM {$table_name}
            GROUP BY notification_type
        ", ARRAY_A);
        
        $notifications_by_event = $wpdb->get_results("
            SELECT event, COUNT(*) as count
            FROM {$table_name}
            GROUP BY event
            ORDER BY count DESC
            LIMIT 10
        ", ARRAY_A);
        
        $recent_notifications = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        return array(
            'total_notifications' => $total_notifications ?: 0,
            'unread_notifications' => $unread_notifications ?: 0,
            'recent_notifications' => $recent_notifications ?: 0,
            'notifications_by_type' => $notifications_by_type,
            'notifications_by_event' => $notifications_by_event
        );
    }

    /**
     * Obtener notificación por ID
     *
     * @param int $notification_id ID de la notificación
     * @return array|false
     */
    public function get_notification($notification_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_notifications';
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE id = %d
        ", $notification_id), ARRAY_A);
        
        if ($result) {
            $result['data'] = json_decode($result['data'], true);
        }
        
        return $result;
    }

    /**
     * Obtener notificaciones no leídas
     *
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_unread_notifications($limit = 10) {
        return $this->get_notifications(array(
            'is_read' => 0,
            'limit' => $limit
        ));
    }

    /**
     * Obtener notificaciones por tipo
     *
     * @param string $type Tipo de notificación
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_notifications_by_type($type, $limit = 20) {
        return $this->get_notifications(array(
            'type' => $type,
            'limit' => $limit
        ));
    }

    /**
     * Obtener notificaciones por evento
     *
     * @param string $event Evento
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_notifications_by_event($event, $limit = 20) {
        return $this->get_notifications(array(
            'event' => $event,
            'limit' => $limit
        ));
    }
}
