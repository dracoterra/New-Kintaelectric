<?php
/**
 * Método de Envío Pickup - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el método de envío Pickup
 */
class WCVS_Shipping_Pickup extends WC_Shipping_Method {

    /**
     * ID del método
     */
    const METHOD_ID = 'wcvs_pickup';

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = self::METHOD_ID;
        $this->instance_id = absint($instance_id);
        $this->method_title = 'Pickup';
        $this->method_description = 'Método de recogida en tienda para Venezuela';

        // Configuración por defecto
        $this->init_form_fields();
        $this->init_settings();

        // Configuración
        $this->title = $this->get_option('title');
        $this->enabled = $this->get_option('enabled');
        $this->cost = $this->get_option('cost');
        $this->free_shipping_threshold = $this->get_option('free_shipping_threshold');
        $this->pickup_locations = $this->get_option('pickup_locations');
        $this->pickup_hours = $this->get_option('pickup_hours');
        $this->contact_info = $this->get_option('contact_info');
        $this->instructions = $this->get_option('instructions');
        $this->notification_enabled = $this->get_option('notification_enabled');
        $this->notification_days = $this->get_option('notification_days');

        // Hooks
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Inicializar campos del formulario
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Habilitar/Deshabilitar',
                'type' => 'checkbox',
                'label' => 'Habilitar Pickup',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Recogida en Tienda',
                'desc_tip' => true
            ),
            'cost' => array(
                'title' => 'Costo Base',
                'type' => 'number',
                'description' => 'Costo base de la recogida (generalmente 0).',
                'default' => 0,
                'desc_tip' => true
            ),
            'free_shipping_threshold' => array(
                'title' => 'Umbral de Envío Gratis',
                'type' => 'number',
                'description' => 'Monto mínimo para recogida gratis.',
                'default' => 0,
                'desc_tip' => true
            ),
            'pickup_locations' => array(
                'title' => 'Ubicaciones de Recogida',
                'type' => 'textarea',
                'description' => 'Ubicaciones disponibles en formato: name:address:phone:description (una por línea)',
                'default' => "Tienda Principal:Av. Principal, Centro Comercial, Local 45:+58-212-123-4567:Tienda principal en el centro de la ciudad\nTienda Este:Av. Este, Centro Comercial Este, Local 23:+58-212-123-4568:Tienda en la zona este de la ciudad\nTienda Oeste:Av. Oeste, Centro Comercial Oeste, Local 67:+58-212-123-4569:Tienda en la zona oeste de la ciudad",
                'desc_tip' => true
            ),
            'pickup_hours' => array(
                'title' => 'Horarios de Recogida',
                'type' => 'textarea',
                'description' => 'Horarios de recogida en formato: day:hours (una por línea)',
                'default' => "Lunes:9:00-18:00\nMartes:9:00-18:00\nMiércoles:9:00-18:00\nJueves:9:00-18:00\nViernes:9:00-18:00\nSábado:9:00-17:00\nDomingo:10:00-15:00",
                'desc_tip' => true
            ),
            'contact_info' => array(
                'title' => 'Información de Contacto',
                'type' => 'textarea',
                'description' => 'Información de contacto para recogidas.',
                'default' => "Teléfono: +58-212-123-4567\nEmail: recogidas@kinta-electric.com\nWhatsApp: +58-412-123-4567",
                'desc_tip' => true
            ),
            'instructions' => array(
                'title' => 'Instrucciones de Recogida',
                'type' => 'textarea',
                'description' => 'Instrucciones que se mostrarán al cliente.',
                'default' => 'Puedes recoger tu pedido en cualquiera de nuestras tiendas. Te notificaremos cuando esté listo para recoger.',
                'desc_tip' => true
            ),
            'notification_enabled' => array(
                'title' => 'Habilitar Notificaciones',
                'type' => 'checkbox',
                'label' => 'Habilitar notificaciones de recogida',
                'default' => 'yes'
            ),
            'notification_days' => array(
                'title' => 'Días de Notificación',
                'type' => 'number',
                'description' => 'Días antes de la recogida para enviar notificación.',
                'default' => 1,
                'desc_tip' => true
            )
        );
    }

    /**
     * Calcular envío
     *
     * @param array $package Paquete a enviar
     */
    public function calculate_shipping($package = array()) {
        if (!$this->is_available()) {
            return;
        }

        $cost = 0;

        // Verificar envío gratis
        if ($this->free_shipping_threshold > 0 && $package['contents_cost'] >= $this->free_shipping_threshold) {
            $cost = 0;
        } else {
            $cost = $this->cost;
        }

        // Crear tasa de envío
        $rate = array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => $cost,
            'meta_data' => array(
                'pickup_locations' => $this->pickup_locations,
                'pickup_hours' => $this->pickup_hours,
                'contact_info' => $this->contact_info,
                'instructions' => $this->instructions,
                'notification_enabled' => $this->notification_enabled,
                'notification_days' => $this->notification_days
            )
        );

        $this->add_rate($rate);
    }

    /**
     * Verificar si el método está disponible
     *
     * @return bool
     */
    public function is_available() {
        if ($this->enabled !== 'yes') {
            return false;
        }
        
        return true;
    }

    /**
     * Obtener información del método
     *
     * @return array
     */
    public function get_method_info() {
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'enabled' => $this->enabled,
            'cost' => $this->cost,
            'free_shipping_threshold' => $this->free_shipping_threshold,
            'pickup_locations' => $this->pickup_locations,
            'pickup_hours' => $this->pickup_hours,
            'contact_info' => $this->contact_info,
            'instructions' => $this->instructions,
            'notification_enabled' => $this->notification_enabled,
            'notification_days' => $this->notification_days
        );
    }

    /**
     * Obtener estadísticas del método
     *
     * @return array
     */
    public function get_method_stats() {
        global $wpdb;
        
        $order_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN post_status = 'wc-completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN post_status = 'wc-processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN post_status = 'wc-shipped' THEN 1 ELSE 0 END) as shipped_orders
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_shipping_method'
            AND pm.meta_value = %s",
            $this->id
        ));
        
        return array(
            'total_orders' => intval($order_stats->total_orders),
            'completed_orders' => intval($order_stats->completed_orders),
            'processing_orders' => intval($order_stats->processing_orders),
            'shipped_orders' => intval($order_stats->shipped_orders),
            'success_rate' => $order_stats->total_orders > 0 ? 
                round(($order_stats->completed_orders / $order_stats->total_orders) * 100, 2) : 0
        );
    }

    /**
     * Generar número de seguimiento
     *
     * @param int $order_id ID del pedido
     * @return string
     */
    public function generate_tracking_number($order_id) {
        $prefix = 'PICKUP';
        $timestamp = date('YmdHis');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Validar número de seguimiento
     *
     * @param string $tracking_number Número de seguimiento
     * @return bool
     */
    public function validate_tracking_number($tracking_number) {
        // Formato: PICKUP + timestamp + random
        return preg_match('/^PICKUP\d{14}\d{4}$/', $tracking_number);
    }

    /**
     * Obtener información de seguimiento
     *
     * @param string $tracking_number Número de seguimiento
     * @return array
     */
    public function get_tracking_info($tracking_number) {
        if (!$this->validate_tracking_number($tracking_number)) {
            return array('error' => 'Número de seguimiento inválido');
        }
        
        // Simular información de seguimiento
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
     * Calcular tiempo de preparación estimado
     *
     * @return int
     */
    public function get_estimated_preparation_days() {
        return 1; // Generalmente 1 día para preparar el pedido
    }

    /**
     * Obtener ubicaciones de recogida disponibles
     *
     * @return array
     */
    public function get_pickup_locations() {
        $locations = array();
        $lines = explode("\n", $this->pickup_locations);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            if (preg_match('/^([^:]+):([^:]+):([^:]+):(.+)$/', $line, $matches)) {
                $locations[] = array(
                    'name' => trim($matches[1]),
                    'address' => trim($matches[2]),
                    'phone' => trim($matches[3]),
                    'description' => trim($matches[4])
                );
            }
        }
        
        return $locations;
    }

    /**
     * Obtener horarios de recogida
     *
     * @return array
     */
    public function get_pickup_hours() {
        $hours = array();
        $lines = explode("\n", $this->pickup_hours);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            if (preg_match('/^([^:]+):(.+)$/', $line, $matches)) {
                $hours[trim($matches[1])] = trim($matches[2]);
            }
        }
        
        return $hours;
    }

    /**
     * Obtener información de contacto
     *
     * @return string
     */
    public function get_contact_info() {
        return $this->contact_info;
    }

    /**
     * Obtener instrucciones de recogida
     *
     * @return string
     */
    public function get_instructions() {
        return $this->instructions;
    }

    /**
     * Verificar si las notificaciones están habilitadas
     *
     * @return bool
     */
    public function is_notification_enabled() {
        return $this->notification_enabled === 'yes';
    }

    /**
     * Obtener días de notificación
     *
     * @return int
     */
    public function get_notification_days() {
        return intval($this->notification_days);
    }

    /**
     * Enviar notificación de recogida
     *
     * @param WC_Order $order Pedido
     * @return bool
     */
    public function send_pickup_notification($order) {
        if (!$this->is_notification_enabled()) {
            return false;
        }

        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name();
        $order_id = $order->get_id();
        
        $subject = "Tu pedido #{$order_id} está listo para recoger";
        
        $message = "Estimado/a {$customer_name},\n\n";
        $message .= "Tu pedido #{$order_id} está listo para recoger en cualquiera de nuestras tiendas.\n\n";
        $message .= "Ubicaciones disponibles:\n";
        
        $locations = $this->get_pickup_locations();
        foreach ($locations as $location) {
            $message .= "- {$location['name']}: {$location['address']}\n";
            $message .= "  Teléfono: {$location['phone']}\n";
            $message .= "  {$location['description']}\n\n";
        }
        
        $message .= "Horarios de recogida:\n";
        $hours = $this->get_pickup_hours();
        foreach ($hours as $day => $time) {
            $message .= "- {$day}: {$time}\n";
        }
        
        $message .= "\nInformación de contacto:\n";
        $message .= $this->get_contact_info() . "\n\n";
        $message .= "Instrucciones:\n";
        $message .= $this->get_instructions() . "\n\n";
        $message .= "Saludos,\n";
        $message .= "Equipo de Kinta Electric";
        
        return wp_mail($customer_email, $subject, $message);
    }

    /**
     * Programar notificación de recogida
     *
     * @param WC_Order $order Pedido
     * @return bool
     */
    public function schedule_pickup_notification($order) {
        if (!$this->is_notification_enabled()) {
            return false;
        }

        $notification_days = $this->get_notification_days();
        $notification_time = strtotime("+{$notification_days} days");
        
        // Programar evento de notificación
        wp_schedule_single_event($notification_time, 'wcvs_pickup_notification', array($order->get_id()));
        
        return true;
    }

    /**
     * Obtener ubicación de recogida por defecto
     *
     * @return array
     */
    public function get_default_pickup_location() {
        $locations = $this->get_pickup_locations();
        return !empty($locations) ? $locations[0] : null;
    }

    /**
     * Verificar si una ubicación está disponible
     *
     * @param string $location_name Nombre de la ubicación
     * @return bool
     */
    public function is_location_available($location_name) {
        $locations = $this->get_pickup_locations();
        
        foreach ($locations as $location) {
            if ($location['name'] === $location_name) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtener ubicación por nombre
     *
     * @param string $location_name Nombre de la ubicación
     * @return array|null
     */
    public function get_location_by_name($location_name) {
        $locations = $this->get_pickup_locations();
        
        foreach ($locations as $location) {
            if ($location['name'] === $location_name) {
                return $location;
            }
        }
        
        return null;
    }
}
