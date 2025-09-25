<?php
/**
 * Sistema de Seguimiento de Envíos - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el seguimiento de envíos
 */
class WCVS_Shipping_Tracking {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hooks para seguimiento
        add_action('wp_ajax_wcvs_track_shipment', array($this, 'ajax_track_shipment'));
        add_action('wp_ajax_nopriv_wcvs_track_shipment', array($this, 'ajax_track_shipment'));
        
        // Hooks para admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hooks para emails
        add_action('wcvs_shipment_status_changed', array($this, 'send_status_notification'), 10, 3);
        
        // Hooks para actualización automática
        add_action('wcvs_update_shipment_status', array($this, 'update_shipment_status'), 10, 2);
    }

    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Seguimiento de Envíos',
            'Seguimiento de Envíos',
            'manage_woocommerce',
            'wcvs-shipping-tracking',
            array($this, 'admin_page')
        );
    }

    /**
     * Encolar scripts de administración
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'woocommerce_page_wcvs-shipping-tracking') {
            wp_enqueue_script(
                'wcvs-shipping-tracking-admin',
                WCVS_PLUGIN_URL . 'modules/shipping-methods/js/wcvs-shipping-tracking-admin.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-shipping-tracking-admin', 'wcvs_shipping_tracking', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_shipping_tracking_nonce'),
                'strings' => array(
                    'track' => 'Rastrear',
                    'loading' => 'Cargando...',
                    'error' => 'Error al rastrear',
                    'success' => 'Información obtenida'
                )
            ));
        }
    }

    /**
     * Página de administración
     */
    public function admin_page() {
        $shipments = $this->get_shipments();
        
        echo '<div class="wrap">';
        echo '<h1>Seguimiento de Envíos</h1>';
        
        if (empty($shipments)) {
            echo '<p>No hay envíos para rastrear.</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Pedido</th>';
            echo '<th>Cliente</th>';
            echo '<th>Método de Envío</th>';
            echo '<th>Número de Seguimiento</th>';
            echo '<th>Estado</th>';
            echo '<th>Última Actualización</th>';
            echo '<th>Acciones</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($shipments as $shipment) {
                $this->render_shipment_row($shipment);
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '</div>';
    }

    /**
     * Renderizar fila de envío
     *
     * @param array $shipment Datos del envío
     */
    private function render_shipment_row($shipment) {
        $order = wc_get_order($shipment['order_id']);
        if (!$order) {
            return;
        }
        
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $shipping_method = $order->get_shipping_method();
        $tracking_number = $shipment['tracking_number'];
        $status = $shipment['status'];
        $last_update = $shipment['last_update'];
        
        echo '<tr>';
        echo '<td><a href="' . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . '">#' . $order->get_id() . '</a></td>';
        echo '<td>' . esc_html($customer_name) . '</td>';
        echo '<td>' . esc_html($shipping_method) . '</td>';
        echo '<td>' . esc_html($tracking_number) . '</td>';
        echo '<td>' . esc_html($status) . '</td>';
        echo '<td>' . esc_html($last_update) . '</td>';
        echo '<td>';
        echo '<button class="button button-primary wcvs-track-shipment" data-tracking-number="' . esc_attr($tracking_number) . '">Rastrear</button>';
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Obtener envíos
     *
     * @return array
     */
    private function get_shipments() {
        global $wpdb;
        
        $results = $wpdb->get_results("
            SELECT 
                p.ID as order_id,
                pm1.meta_value as tracking_number,
                pm2.meta_value as shipping_method,
                pm3.meta_value as status,
                pm4.meta_value as last_update
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_tracking_number'
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_shipping_method'
            INNER JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_shipment_status'
            INNER JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_shipment_last_update'
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-processing', 'wc-shipped', 'wc-completed')
            ORDER BY p.post_date DESC
            LIMIT 50
        ");
        
        return $results;
    }

    /**
     * Rastrear envío via AJAX
     */
    public function ajax_track_shipment() {
        check_ajax_referer('wcvs_shipping_tracking_nonce', 'nonce');
        
        $tracking_number = sanitize_text_field($_POST['tracking_number'] ?? '');
        
        if (empty($tracking_number)) {
            wp_send_json_error('Número de seguimiento requerido');
        }
        
        $result = $this->track_shipment($tracking_number);
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Rastrear envío
     *
     * @param string $tracking_number Número de seguimiento
     * @return array
     */
    public function track_shipment($tracking_number) {
        // Determinar el método de envío basado en el prefijo
        $method = $this->get_shipping_method_from_tracking($tracking_number);
        
        if (!$method) {
            return array(
                'success' => false,
                'message' => 'Número de seguimiento no válido'
            );
        }
        
        // Obtener información de seguimiento según el método
        switch ($method) {
            case 'mrw':
                $tracking_info = $this->get_mrw_tracking_info($tracking_number);
                break;
            case 'zoom':
                $tracking_info = $this->get_zoom_tracking_info($tracking_number);
                break;
            case 'tealca':
                $tracking_info = $this->get_tealca_tracking_info($tracking_number);
                break;
            case 'local_delivery':
                $tracking_info = $this->get_local_delivery_tracking_info($tracking_number);
                break;
            case 'pickup':
                $tracking_info = $this->get_pickup_tracking_info($tracking_number);
                break;
            default:
                $tracking_info = array('error' => 'Método de envío no soportado');
        }
        
        if (isset($tracking_info['error'])) {
            return array(
                'success' => false,
                'message' => $tracking_info['error']
            );
        }
        
        // Actualizar estado en la base de datos
        $this->update_shipment_status($tracking_number, $tracking_info['status']);
        
        return array(
            'success' => true,
            'data' => $tracking_info
        );
    }

    /**
     * Obtener método de envío desde número de seguimiento
     *
     * @param string $tracking_number Número de seguimiento
     * @return string|null
     */
    private function get_shipping_method_from_tracking($tracking_number) {
        if (strpos($tracking_number, 'MRW') === 0) {
            return 'mrw';
        } elseif (strpos($tracking_number, 'ZOOM') === 0) {
            return 'zoom';
        } elseif (strpos($tracking_number, 'TEALCA') === 0) {
            return 'tealca';
        } elseif (strpos($tracking_number, 'LOCAL') === 0) {
            return 'local_delivery';
        } elseif (strpos($tracking_number, 'PICKUP') === 0) {
            return 'pickup';
        }
        
        return null;
    }

    /**
     * Obtener información de seguimiento MRW
     *
     * @param string $tracking_number Número de seguimiento
     * @return array
     */
    private function get_mrw_tracking_info($tracking_number) {
        // Simular información de seguimiento MRW
        // En una implementación real, esto se conectaría con la API de MRW
        return array(
            'tracking_number' => $tracking_number,
            'status' => 'En tránsito',
            'location' => 'Centro de distribución Caracas',
            'estimated_delivery' => date('Y-m-d', strtotime('+3 days')),
            'history' => array(
                array(
                    'date' => date('Y-m-d H:i:s'),
                    'status' => 'En tránsito',
                    'location' => 'Centro de distribución Caracas'
                ),
                array(
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'status' => 'Recogido',
                    'location' => 'Tienda origen'
                )
            )
        );
    }

    /**
     * Obtener información de seguimiento Zoom
     *
     * @param string $tracking_number Número de seguimiento
     * @return array
     */
    private function get_zoom_tracking_info($tracking_number) {
        // Simular información de seguimiento Zoom
        return array(
            'tracking_number' => $tracking_number,
            'status' => 'En tránsito',
            'location' => 'Centro de distribución Zoom',
            'estimated_delivery' => date('Y-m-d', strtotime('+2 days')),
            'history' => array(
                array(
                    'date' => date('Y-m-d H:i:s'),
                    'status' => 'En tránsito',
                    'location' => 'Centro de distribución Zoom'
                ),
                array(
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'status' => 'Recogido',
                    'location' => 'Tienda origen'
                )
            )
        );
    }

    /**
     * Obtener información de seguimiento Tealca
     *
     * @param string $tracking_number Número de seguimiento
     * @return array
     */
    private function get_tealca_tracking_info($tracking_number) {
        // Simular información de seguimiento Tealca
        return array(
            'tracking_number' => $tracking_number,
            'status' => 'En tránsito',
            'location' => 'Centro de distribución Tealca',
            'estimated_delivery' => date('Y-m-d', strtotime('+4 days')),
            'history' => array(
                array(
                    'date' => date('Y-m-d H:i:s'),
                    'status' => 'En tránsito',
                    'location' => 'Centro de distribución Tealca'
                ),
                array(
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'status' => 'Recogido',
                    'location' => 'Tienda origen'
                )
            )
        );
    }

    /**
     * Obtener información de seguimiento Local Delivery
     *
     * @param string $tracking_number Número de seguimiento
     * @return array
     */
    private function get_local_delivery_tracking_info($tracking_number) {
        // Simular información de seguimiento Local Delivery
        return array(
            'tracking_number' => $tracking_number,
            'status' => 'En ruta',
            'location' => 'Repartidor local',
            'estimated_delivery' => date('Y-m-d', strtotime('+1 day')),
            'history' => array(
                array(
                    'date' => date('Y-m-d H:i:s'),
                    'status' => 'En ruta',
                    'location' => 'Repartidor local'
                ),
                array(
                    'date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                    'status' => 'Recogido',
                    'location' => 'Tienda origen'
                )
            )
        );
    }

    /**
     * Obtener información de seguimiento Pickup
     *
     * @param string $tracking_number Número de seguimiento
     * @return array
     */
    private function get_pickup_tracking_info($tracking_number) {
        // Simular información de seguimiento Pickup
        return array(
            'tracking_number' => $tracking_number,
            'status' => 'Listo para recoger',
            'location' => 'Tienda principal',
            'estimated_delivery' => date('Y-m-d', strtotime('+1 day')),
            'history' => array(
                array(
                    'date' => date('Y-m-d H:i:s'),
                    'status' => 'Listo para recoger',
                    'location' => 'Tienda principal'
                ),
                array(
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'status' => 'Preparado',
                    'location' => 'Tienda principal'
                )
            )
        );
    }

    /**
     * Actualizar estado del envío
     *
     * @param string $tracking_number Número de seguimiento
     * @param string $status Estado
     */
    public function update_shipment_status($tracking_number, $status) {
        global $wpdb;
        
        // Buscar el pedido por número de seguimiento
        $order_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_tracking_number' AND meta_value = %s",
            $tracking_number
        ));
        
        if ($order_id) {
            // Actualizar estado del envío
            update_post_meta($order_id, '_shipment_status', $status);
            update_post_meta($order_id, '_shipment_last_update', current_time('mysql'));
            
            // Disparar acción para notificaciones
            do_action('wcvs_shipment_status_changed', $order_id, $tracking_number, $status);
            
            // Log de la actualización
            WCVS_Logger::info(WCVS_Logger::CONTEXT_SHIPPING, "Estado de envío actualizado: {$tracking_number} - {$status}");
        }
    }

    /**
     * Enviar notificación de cambio de estado
     *
     * @param int $order_id ID del pedido
     * @param string $tracking_number Número de seguimiento
     * @param string $status Estado
     */
    public function send_status_notification($order_id, $tracking_number, $status) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name();
        
        $subject = "Actualización de Envío - Pedido #{$order_id}";
        
        $message = "Estimado/a {$customer_name},\n\n";
        $message .= "Te informamos que el estado de tu envío ha sido actualizado.\n\n";
        $message .= "Detalles del envío:\n";
        $message .= "Pedido #{$order_id}\n";
        $message .= "Número de seguimiento: {$tracking_number}\n";
        $message .= "Estado actual: {$status}\n\n";
        $message .= "Puedes rastrear tu envío en nuestro sitio web.\n\n";
        $message .= "Si tienes alguna pregunta, no dudes en contactarnos.\n\n";
        $message .= "Saludos,\n";
        $message .= "Equipo de Kinta Electric";
        
        wp_mail($customer_email, $subject, $message);
    }

    /**
     * Generar número de seguimiento
     *
     * @param int $order_id ID del pedido
     * @param string $method Método de envío
     * @return string
     */
    public function generate_tracking_number($order_id, $method) {
        $prefixes = array(
            'mrw' => 'MRW',
            'zoom' => 'ZOOM',
            'tealca' => 'TEALCA',
            'local_delivery' => 'LOCAL',
            'pickup' => 'PICKUP'
        );
        
        $prefix = $prefixes[$method] ?? 'TRACK';
        $timestamp = date('YmdHis');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Asignar número de seguimiento a pedido
     *
     * @param int $order_id ID del pedido
     * @param string $method Método de envío
     * @return string
     */
    public function assign_tracking_number($order_id, $method) {
        $tracking_number = $this->generate_tracking_number($order_id, $method);
        
        // Guardar número de seguimiento
        update_post_meta($order_id, '_tracking_number', $tracking_number);
        update_post_meta($order_id, '_shipment_status', 'Recogido');
        update_post_meta($order_id, '_shipment_last_update', current_time('mysql'));
        
        // Log de la asignación
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SHIPPING, "Número de seguimiento asignado: {$tracking_number} para pedido #{$order_id}");
        
        return $tracking_number;
    }

    /**
     * Obtener estadísticas de seguimiento
     *
     * @return array
     */
    public function get_tracking_stats() {
        global $wpdb;
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_shipments,
                SUM(CASE WHEN pm.meta_value = 'Entregado' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN pm.meta_value = 'En tránsito' THEN 1 ELSE 0 END) as in_transit,
                SUM(CASE WHEN pm.meta_value = 'Recogido' THEN 1 ELSE 0 END) as picked_up
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_shipment_status'
            AND p.post_status IN ('wc-processing', 'wc-shipped', 'wc-completed')
        ");
        
        return array(
            'total_shipments' => intval($stats->total_shipments),
            'delivered' => intval($stats->delivered),
            'in_transit' => intval($stats->in_transit),
            'picked_up' => intval($stats->picked_up),
            'delivery_rate' => $stats->total_shipments > 0 ? 
                round(($stats->delivered / $stats->total_shipments) * 100, 2) : 0
        );
    }

    /**
     * Obtener envíos por estado
     *
     * @param string $status Estado
     * @return array
     */
    public function get_shipments_by_status($status) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.ID as order_id,
                pm1.meta_value as tracking_number,
                pm2.meta_value as shipping_method,
                pm3.meta_value as status,
                pm4.meta_value as last_update
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_tracking_number'
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_shipping_method'
            INNER JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_shipment_status'
            INNER JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_shipment_last_update'
            WHERE p.post_type = 'shop_order'
            AND pm3.meta_value = %s
            ORDER BY p.post_date DESC
        ", $status));
        
        return $results;
    }
}
