<?php
/**
 * Sistema de Notificaciones - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar el sistema de notificaciones
 */
class WCVS_Notifications {

    /**
     * Instancia del plugin
     *
     * @var WCVS_Core
     */
    private $plugin;

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Gestores de notificaciones
     *
     * @var WCVS_Email_Manager
     */
    private $email_manager;

    /**
     * @var WCVS_SMS_Manager
     */
    private $sms_manager;

    /**
     * @var WCVS_Push_Manager
     */
    private $push_manager;

    /**
     * @var WCVS_Webhook_Manager
     */
    private $webhook_manager;

    /**
     * @var WCVS_Notification_Center
     */
    private $notification_center;

    /**
     * @var WCVS_Template_Manager
     */
    private $template_manager;

    /**
     * Tipos de notificaciones
     *
     * @var array
     */
    private $notification_types = array(
        'email' => 'Email',
        'sms' => 'SMS',
        'push' => 'Push Notification',
        'webhook' => 'Webhook',
        'admin' => 'Admin Notification'
    );

    /**
     * Eventos de notificación
     *
     * @var array
     */
    private $notification_events = array(
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

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WCVS_Core::get_instance();
        $this->settings = $this->plugin->get_settings()->get('notifications', array());
        
        $this->init_hooks();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Cargar dependencias
        $this->load_dependencies();

        // Configurar hooks de WooCommerce
        $this->setup_woocommerce_hooks();

        // Cargar scripts y estilos
        $this->enqueue_assets();

        // Inicializar gestores
        $this->init_managers();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Módulo Notifications inicializado');
    }

    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        require_once WCVS_PLUGIN_PATH . 'modules/notifications/includes/class-wcvs-email-manager.php';
        require_once WCVS_PLUGIN_PATH . 'modules/notifications/includes/class-wcvs-sms-manager.php';
        require_once WCVS_PLUGIN_PATH . 'modules/notifications/includes/class-wcvs-push-manager.php';
        require_once WCVS_PLUGIN_PATH . 'modules/notifications/includes/class-wcvs-webhook-manager.php';
        require_once WCVS_PLUGIN_PATH . 'modules/notifications/includes/class-wcvs-notification-center.php';
        require_once WCVS_PLUGIN_PATH . 'modules/notifications/includes/class-wcvs-template-manager.php';
    }

    /**
     * Inicializar gestores
     */
    private function init_managers() {
        // Inicializar gestores de notificaciones
        $this->email_manager = new WCVS_Email_Manager();
        $this->sms_manager = new WCVS_SMS_Manager();
        $this->push_manager = new WCVS_Push_Manager();
        $this->webhook_manager = new WCVS_Webhook_Manager();
        $this->notification_center = new WCVS_Notification_Center();
        $this->template_manager = new WCVS_Template_Manager();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Gestores de notificaciones inicializados');
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
        
        // Hook para enviar notificaciones
        add_action('wcvs_send_notification', array($this, 'send_notification'), 10, 3);
    }

    /**
     * Configurar hooks de WooCommerce
     */
    private function setup_woocommerce_hooks() {
        // Hooks de pedidos
        add_action('woocommerce_new_order', array($this, 'handle_order_created'));
        add_action('woocommerce_order_status_processing', array($this, 'handle_order_processing'));
        add_action('woocommerce_order_status_completed', array($this, 'handle_order_completed'));
        add_action('woocommerce_order_status_cancelled', array($this, 'handle_order_cancelled'));
        
        // Hooks de pagos
        add_action('woocommerce_payment_complete', array($this, 'handle_payment_received'));
        add_action('woocommerce_order_status_failed', array($this, 'handle_payment_failed'));
        
        // Hooks de facturación
        add_action('wcvs_invoice_generated', array($this, 'handle_invoice_generated'));
        add_action('wcvs_invoice_sent_to_seniat', array($this, 'handle_invoice_sent_to_seniat'));
        
        // Hooks de envío
        add_action('wcvs_shipment_created', array($this, 'handle_shipment_created'));
        add_action('wcvs_shipment_shipped', array($this, 'handle_shipment_shipped'));
        add_action('wcvs_shipment_delivered', array($this, 'handle_shipment_delivered'));
        
        // Hooks de productos
        add_action('woocommerce_low_stock_notification', array($this, 'handle_low_stock'));
        add_action('wcvs_price_changed', array($this, 'handle_price_change'));
        
        // Hooks de moneda
        add_action('wcvs_currency_rate_updated', array($this, 'handle_currency_rate_updated'));
        
        // Hooks de reportes
        add_action('wcvs_seniat_report_generated', array($this, 'handle_seniat_report_generated'));
    }

    /**
     * Manejar pedido creado
     *
     * @param int $order_id ID del pedido
     */
    public function handle_order_created($order_id) {
        $this->trigger_notification('order_created', $order_id);
    }

    /**
     * Manejar pedido en procesamiento
     *
     * @param int $order_id ID del pedido
     */
    public function handle_order_processing($order_id) {
        $this->trigger_notification('order_processing', $order_id);
    }

    /**
     * Manejar pedido completado
     *
     * @param int $order_id ID del pedido
     */
    public function handle_order_completed($order_id) {
        $this->trigger_notification('order_completed', $order_id);
    }

    /**
     * Manejar pedido cancelado
     *
     * @param int $order_id ID del pedido
     */
    public function handle_order_cancelled($order_id) {
        $this->trigger_notification('order_cancelled', $order_id);
    }

    /**
     * Manejar pago recibido
     *
     * @param int $order_id ID del pedido
     */
    public function handle_payment_received($order_id) {
        $this->trigger_notification('payment_received', $order_id);
    }

    /**
     * Manejar pago fallido
     *
     * @param int $order_id ID del pedido
     */
    public function handle_payment_failed($order_id) {
        $this->trigger_notification('payment_failed', $order_id);
    }

    /**
     * Manejar factura generada
     *
     * @param int $order_id ID del pedido
     */
    public function handle_invoice_generated($order_id) {
        $this->trigger_notification('invoice_generated', $order_id);
    }

    /**
     * Manejar factura enviada a SENIAT
     *
     * @param int $order_id ID del pedido
     */
    public function handle_invoice_sent_to_seniat($order_id) {
        $this->trigger_notification('invoice_sent_to_seniat', $order_id);
    }

    /**
     * Manejar envío creado
     *
     * @param int $order_id ID del pedido
     */
    public function handle_shipment_created($order_id) {
        $this->trigger_notification('shipment_created', $order_id);
    }

    /**
     * Manejar envío despachado
     *
     * @param int $order_id ID del pedido
     */
    public function handle_shipment_shipped($order_id) {
        $this->trigger_notification('shipment_shipped', $order_id);
    }

    /**
     * Manejar envío entregado
     *
     * @param int $order_id ID del pedido
     */
    public function handle_shipment_delivered($order_id) {
        $this->trigger_notification('shipment_delivered', $order_id);
    }

    /**
     * Manejar stock bajo
     *
     * @param WC_Product $product Producto
     */
    public function handle_low_stock($product) {
        $this->trigger_notification('low_stock', $product->get_id());
    }

    /**
     * Manejar cambio de precio
     *
     * @param int $product_id ID del producto
     */
    public function handle_price_change($product_id) {
        $this->trigger_notification('price_change', $product_id);
    }

    /**
     * Manejar tasa de cambio actualizada
     */
    public function handle_currency_rate_updated() {
        $this->trigger_notification('currency_rate_updated');
    }

    /**
     * Manejar reporte SENIAT generado
     *
     * @param string $report_type Tipo de reporte
     */
    public function handle_seniat_report_generated($report_type) {
        $this->trigger_notification('seniat_report_generated', null, array('report_type' => $report_type));
    }

    /**
     * Disparar notificación
     *
     * @param string $event Evento
     * @param int $object_id ID del objeto
     * @param array $data Datos adicionales
     */
    private function trigger_notification($event, $object_id = null, $data = array()) {
        // Verificar si las notificaciones están habilitadas
        if (!$this->is_notifications_enabled()) {
            return;
        }

        // Obtener configuración de notificaciones para este evento
        $event_config = $this->get_event_config($event);
        if (!$event_config) {
            return;
        }

        // Preparar datos de la notificación
        $notification_data = $this->prepare_notification_data($event, $object_id, $data);

        // Enviar notificaciones según configuración
        foreach ($event_config['channels'] as $channel => $enabled) {
            if ($enabled) {
                $this->send_notification($channel, $event, $notification_data);
            }
        }
    }

    /**
     * Verificar si las notificaciones están habilitadas
     *
     * @return bool
     */
    private function is_notifications_enabled() {
        return $this->settings['enabled'] ?? true;
    }

    /**
     * Obtener configuración de evento
     *
     * @param string $event Evento
     * @return array|false
     */
    private function get_event_config($event) {
        return $this->settings['events'][$event] ?? false;
    }

    /**
     * Preparar datos de notificación
     *
     * @param string $event Evento
     * @param int $object_id ID del objeto
     * @param array $data Datos adicionales
     * @return array
     */
    private function prepare_notification_data($event, $object_id, $data) {
        $notification_data = array(
            'event' => $event,
            'object_id' => $object_id,
            'timestamp' => current_time('mysql'),
            'data' => $data
        );

        // Añadir datos específicos según el evento
        switch ($event) {
            case 'order_created':
            case 'order_processing':
            case 'order_completed':
            case 'order_cancelled':
            case 'payment_received':
            case 'payment_failed':
                $notification_data['order'] = $this->get_order_data($object_id);
                break;
            
            case 'invoice_generated':
            case 'invoice_sent_to_seniat':
                $notification_data['invoice'] = $this->get_invoice_data($object_id);
                break;
            
            case 'shipment_created':
            case 'shipment_shipped':
            case 'shipment_delivered':
                $notification_data['shipment'] = $this->get_shipment_data($object_id);
                break;
            
            case 'low_stock':
            case 'price_change':
                $notification_data['product'] = $this->get_product_data($object_id);
                break;
            
            case 'currency_rate_updated':
                $notification_data['currency'] = $this->get_currency_data();
                break;
            
            case 'seniat_report_generated':
                $notification_data['report'] = $this->get_report_data($data['report_type'] ?? '');
                break;
        }

        return $notification_data;
    }

    /**
     * Obtener datos del pedido
     *
     * @param int $order_id ID del pedido
     * @return array|false
     */
    private function get_order_data($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        return array(
            'id' => $order->get_id(),
            'number' => $order->get_order_number(),
            'status' => $order->get_status(),
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email' => $order->get_billing_email(),
            'customer_phone' => $order->get_billing_phone(),
            'payment_method' => $order->get_payment_method_title(),
            'date_created' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'items' => $this->get_order_items($order)
        );
    }

    /**
     * Obtener items del pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function get_order_items($order) {
        $items = array();
        foreach ($order->get_items() as $item) {
            $items[] = array(
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total()
            );
        }
        return $items;
    }

    /**
     * Obtener datos de la factura
     *
     * @param int $order_id ID del pedido
     * @return array|false
     */
    private function get_invoice_data($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        return array(
            'invoice_number' => $order->get_meta('_wcvs_invoice_number'),
            'invoice_status' => $order->get_meta('_wcvs_invoice_status'),
            'seniat_status' => $order->get_meta('_wcvs_seniat_status'),
            'generated_at' => $order->get_meta('_wcvs_invoice_generated_at')
        );
    }

    /**
     * Obtener datos del envío
     *
     * @param int $order_id ID del pedido
     * @return array|false
     */
    private function get_shipment_data($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        return array(
            'tracking_number' => $order->get_meta('_tracking_number'),
            'shipment_status' => $order->get_meta('_shipment_status'),
            'shipping_method' => $order->get_shipping_method(),
            'shipping_address' => $order->get_formatted_shipping_address()
        );
    }

    /**
     * Obtener datos del producto
     *
     * @param int $product_id ID del producto
     * @return array|false
     */
    private function get_product_data($product_id) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }

        return array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'price' => $product->get_price(),
            'stock_quantity' => $product->get_stock_quantity(),
            'stock_status' => $product->get_stock_status()
        );
    }

    /**
     * Obtener datos de moneda
     *
     * @return array
     */
    private function get_currency_data() {
        $bcv_integration = $this->plugin->get_bcv_integration();
        $rate = $bcv_integration ? $bcv_integration->get_current_rate() : 0;

        return array(
            'usd_to_ves_rate' => $rate,
            'last_updated' => current_time('mysql'),
            'source' => 'BCV'
        );
    }

    /**
     * Obtener datos del reporte
     *
     * @param string $report_type Tipo de reporte
     * @return array
     */
    private function get_report_data($report_type) {
        return array(
            'type' => $report_type,
            'generated_at' => current_time('mysql'),
            'period' => date('Y-m')
        );
    }

    /**
     * Enviar notificación
     *
     * @param string $channel Canal de notificación
     * @param string $event Evento
     * @param array $data Datos de la notificación
     */
    public function send_notification($channel, $event, $data) {
        switch ($channel) {
            case 'email':
                $this->send_email_notification($event, $data);
                break;
            
            case 'sms':
                $this->send_sms_notification($event, $data);
                break;
            
            case 'push':
                $this->send_push_notification($event, $data);
                break;
            
            case 'webhook':
                $this->send_webhook_notification($event, $data);
                break;
            
            case 'admin':
                $this->send_admin_notification($event, $data);
                break;
        }
    }

    /**
     * Enviar notificación por email
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     */
    private function send_email_notification($event, $data) {
        if ($this->email_manager) {
            $this->email_manager->send_notification($event, $data);
        }
    }

    /**
     * Enviar notificación por SMS
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     */
    private function send_sms_notification($event, $data) {
        if ($this->sms_manager) {
            $this->sms_manager->send_notification($event, $data);
        }
    }

    /**
     * Enviar notificación push
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     */
    private function send_push_notification($event, $data) {
        if ($this->push_manager) {
            $this->push_manager->send_notification($event, $data);
        }
    }

    /**
     * Enviar notificación webhook
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     */
    private function send_webhook_notification($event, $data) {
        if ($this->webhook_manager) {
            $this->webhook_manager->send_webhook($event, $data);
        }
    }

    /**
     * Enviar notificación de admin
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     */
    private function send_admin_notification($event, $data) {
        if ($this->notification_center) {
            $this->notification_center->add_notification($event, $data);
        }
    }

    /**
     * Cargar assets (CSS y JS)
     */
    private function enqueue_assets() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_notification_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Encolar assets de notificaciones
     */
    public function enqueue_notification_assets() {
        wp_enqueue_style(
            'wcvs-notifications',
            WCVS_PLUGIN_URL . 'modules/notifications/css/wcvs-notifications.css',
            array(),
            WCVS_VERSION
        );

        wp_enqueue_script(
            'wcvs-notifications',
            WCVS_PLUGIN_URL . 'modules/notifications/js/wcvs-notifications.js',
            array('jquery'),
            WCVS_VERSION,
            true
        );

        wp_localize_script('wcvs-notifications', 'wcvs_notifications', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcvs_notifications_nonce'),
            'strings' => array(
                'notification_received' => __('Notificación recibida', 'wcvs'),
                'mark_as_read' => __('Marcar como leída', 'wcvs'),
                'view_details' => __('Ver detalles', 'wcvs')
            )
        ));
    }

    /**
     * Encolar assets de admin
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wcvs') !== false) {
            wp_enqueue_style(
                'wcvs-notifications-admin',
                WCVS_PLUGIN_URL . 'modules/notifications/css/wcvs-notifications-admin.css',
                array(),
                WCVS_VERSION
            );

            wp_enqueue_script(
                'wcvs-notifications-admin',
                WCVS_PLUGIN_URL . 'modules/notifications/js/wcvs-notifications-admin.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-notifications-admin', 'wcvs_notifications_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_notifications_admin_nonce'),
                'strings' => array(
                    'mark_all_read' => __('Marcar todas como leídas', 'wcvs'),
                    'clear_all' => __('Limpiar todas', 'wcvs'),
                    'test_notification' => __('Probar notificación', 'wcvs')
                )
            ));
        }
    }

    /**
     * Manejar activación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'notifications') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Módulo Notifications activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'notifications') {
            // Limpiar hooks
            remove_action('woocommerce_new_order', array($this, 'handle_order_created'));
            remove_action('woocommerce_order_status_processing', array($this, 'handle_order_processing'));
            remove_action('woocommerce_order_status_completed', array($this, 'handle_order_completed'));
            
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Módulo Notifications desactivado');
        }
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        global $wpdb;
        
        $total_notifications = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}wcvs_notifications
        ");

        $unread_notifications = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}wcvs_notifications
            WHERE is_read = 0
        ");

        $notifications_by_type = $wpdb->get_results("
            SELECT notification_type, COUNT(*) as count
            FROM {$wpdb->prefix}wcvs_notifications
            GROUP BY notification_type
        ", ARRAY_A);

        return array(
            'total_notifications' => $total_notifications ?: 0,
            'unread_notifications' => $unread_notifications ?: 0,
            'notifications_by_type' => $notifications_by_type,
            'notification_types' => $this->notification_types,
            'notification_events' => $this->notification_events,
            'settings' => $this->settings
        );
    }

    /**
     * Obtener tipos de notificaciones disponibles
     *
     * @return array
     */
    public function get_notification_types() {
        return $this->notification_types;
    }

    /**
     * Obtener eventos de notificación disponibles
     *
     * @return array
     */
    public function get_notification_events() {
        return $this->notification_events;
    }

    /**
     * Configurar notificaciones
     *
     * @param array $config Configuración
     * @return bool
     */
    public function configure_notifications($config) {
        $settings = WCVS_Core::get_instance()->get_settings();
        $settings->set('notifications', $config);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, 'Configuración de notificaciones actualizada');

        return true;
    }

    /**
     * Obtener configuración de notificaciones
     *
     * @return array
     */
    public function get_notification_config() {
        return $this->settings;
    }

    /**
     * Probar notificación
     *
     * @param string $channel Canal
     * @param string $event Evento
     * @return array
     */
    public function test_notification($channel, $event) {
        $test_data = array(
            'event' => $event,
            'object_id' => 0,
            'timestamp' => current_time('mysql'),
            'data' => array('test' => true)
        );

        try {
            $this->send_notification($channel, $event, $test_data);
            return array(
                'success' => true,
                'message' => __('Notificación de prueba enviada correctamente', 'wcvs')
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error al enviar notificación de prueba: %s', 'wcvs'), $e->getMessage())
            );
        }
    }
}
