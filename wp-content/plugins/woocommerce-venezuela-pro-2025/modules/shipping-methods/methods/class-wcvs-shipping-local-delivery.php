<?php
/**
 * Método de Envío Local Delivery - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el método de envío Local Delivery
 */
class WCVS_Shipping_Local_Delivery extends WC_Shipping_Method {

    /**
     * ID del método
     */
    const METHOD_ID = 'wcvs_local_delivery';

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = self::METHOD_ID;
        $this->instance_id = absint($instance_id);
        $this->method_title = 'Local Delivery';
        $this->method_description = 'Método de entrega local para Venezuela';

        // Configuración por defecto
        $this->init_form_fields();
        $this->init_settings();

        // Configuración
        $this->title = $this->get_option('title');
        $this->enabled = $this->get_option('enabled');
        $this->cost = $this->get_option('cost');
        $this->free_shipping_threshold = $this->get_option('free_shipping_threshold');
        $this->weight_rates = $this->get_option('weight_rates');
        $this->volume_rates = $this->get_option('volume_rates');
        $this->city_rates = $this->get_option('city_rates');
        $this->delivery_zones = $this->get_option('delivery_zones');
        $this->delivery_hours = $this->get_option('delivery_hours');
        $this->contact_info = $this->get_option('contact_info');

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
                'label' => 'Habilitar Local Delivery',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Entrega Local',
                'desc_tip' => true
            ),
            'cost' => array(
                'title' => 'Costo Base',
                'type' => 'number',
                'description' => 'Costo base de la entrega local.',
                'default' => 0,
                'desc_tip' => true
            ),
            'free_shipping_threshold' => array(
                'title' => 'Umbral de Envío Gratis',
                'type' => 'number',
                'description' => 'Monto mínimo para entrega gratis.',
                'default' => 0,
                'desc_tip' => true
            ),
            'weight_rates' => array(
                'title' => 'Tarifas por Peso',
                'type' => 'textarea',
                'description' => 'Tarifas por peso en formato: min-max:cost (una por línea)',
                'default' => "0-5:3.00\n5-15:5.00\n15-30:8.00\n30-50:12.00\n50-100:18.00",
                'desc_tip' => true
            ),
            'volume_rates' => array(
                'title' => 'Tarifas por Volumen',
                'type' => 'textarea',
                'description' => 'Tarifas por volumen en formato: min-max:cost (una por línea)',
                'default' => "0-5000:3.00\n5000-15000:5.00\n15000-30000:8.00\n30000-50000:12.00\n50000-100000:18.00",
                'desc_tip' => true
            ),
            'city_rates' => array(
                'title' => 'Tarifas por Ciudad',
                'type' => 'textarea',
                'description' => 'Tarifas adicionales por ciudad en formato: city:cost (una por línea)',
                'default' => "Caracas:0.00\nMaracaibo:5.00\nValencia:3.00\nBarquisimeto:4.00\nCiudad Guayana:6.00",
                'desc_tip' => true
            ),
            'delivery_zones' => array(
                'title' => 'Zonas de Entrega',
                'type' => 'textarea',
                'description' => 'Zonas de entrega disponibles en formato: zone:description (una por línea)',
                'default' => "Centro:Centro de la ciudad\nEste:Zona este de la ciudad\nOeste:Zona oeste de la ciudad\nSur:Zona sur de la ciudad\nNorte:Zona norte de la ciudad",
                'desc_tip' => true
            ),
            'delivery_hours' => array(
                'title' => 'Horarios de Entrega',
                'type' => 'textarea',
                'description' => 'Horarios de entrega en formato: day:hours (una por línea)',
                'default' => "Lunes:9:00-17:00\nMartes:9:00-17:00\nMiércoles:9:00-17:00\nJueves:9:00-17:00\nViernes:9:00-17:00\nSábado:9:00-13:00",
                'desc_tip' => true
            ),
            'contact_info' => array(
                'title' => 'Información de Contacto',
                'type' => 'textarea',
                'description' => 'Información de contacto para entregas locales.',
                'default' => "Teléfono: +58-212-123-4567\nEmail: entregas@kinta-electric.com\nDirección: Av. Principal, Centro Comercial, Local 45",
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
        $weight = 0;
        $volume = 0;
        $city = '';

        // Calcular peso y volumen del paquete
        foreach ($package['contents'] as $item_id => $values) {
            $product = $values['data'];
            $quantity = $values['quantity'];
            
            $weight += $product->get_weight() * $quantity;
            $volume += ($product->get_length() * $product->get_width() * $product->get_height()) * $quantity;
        }

        // Obtener ciudad de destino
        if (isset($package['destination']['city'])) {
            $city = $package['destination']['city'];
        }

        // Calcular costo base
        $cost = $this->calculate_base_cost($weight, $volume, $city);

        // Verificar envío gratis
        if ($this->free_shipping_threshold > 0 && $package['contents_cost'] >= $this->free_shipping_threshold) {
            $cost = 0;
        }

        // Crear tasa de envío
        $rate = array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => $cost,
            'meta_data' => array(
                'weight' => $weight,
                'volume' => $volume,
                'city' => $city,
                'delivery_zones' => $this->delivery_zones,
                'delivery_hours' => $this->delivery_hours,
                'contact_info' => $this->contact_info
            )
        );

        $this->add_rate($rate);
    }

    /**
     * Calcular costo base
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $city Ciudad de destino
     * @return float
     */
    private function calculate_base_cost($weight, $volume, $city) {
        $cost = 0;

        // Calcular costo por peso
        $weight_cost = $this->calculate_weight_cost($weight);
        
        // Calcular costo por volumen
        $volume_cost = $this->calculate_volume_cost($volume);
        
        // Usar el costo más alto
        $cost = max($weight_cost, $volume_cost);
        
        // Añadir costo base
        $cost += $this->cost;
        
        // Añadir costo por ciudad
        $city_cost = $this->calculate_city_cost($city);
        $cost += $city_cost;
        
        return $cost;
    }

    /**
     * Calcular costo por peso
     *
     * @param float $weight Peso en kg
     * @return float
     */
    private function calculate_weight_cost($weight) {
        $rates = $this->parse_rates($this->weight_rates);
        
        foreach ($rates as $rate) {
            if ($weight >= $rate['min'] && $weight <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Calcular costo por volumen
     *
     * @param float $volume Volumen en cm³
     * @return float
     */
    private function calculate_volume_cost($volume) {
        $rates = $this->parse_rates($this->volume_rates);
        
        foreach ($rates as $rate) {
            if ($volume >= $rate['min'] && $volume <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Calcular costo por ciudad
     *
     * @param string $city Ciudad de destino
     * @return float
     */
    private function calculate_city_cost($city) {
        $rates = $this->parse_city_rates($this->city_rates);
        
        if (isset($rates[$city])) {
            return $rates[$city];
        }
        
        return 0;
    }

    /**
     * Parsear tarifas
     *
     * @param string $rates_string Cadena de tarifas
     * @return array
     */
    private function parse_rates($rates_string) {
        $rates = array();
        $lines = explode("\n", $rates_string);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            if (preg_match('/^(\d+(?:\.\d+)?)-(\d+(?:\.\d+)?):(\d+(?:\.\d+)?)$/', $line, $matches)) {
                $rates[] = array(
                    'min' => floatval($matches[1]),
                    'max' => floatval($matches[2]),
                    'cost' => floatval($matches[3])
                );
            }
        }
        
        return $rates;
    }

    /**
     * Parsear tarifas por ciudad
     *
     * @param string $rates_string Cadena de tarifas
     * @return array
     */
    private function parse_city_rates($rates_string) {
        $rates = array();
        $lines = explode("\n", $rates_string);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            if (preg_match('/^([^:]+):(\d+(?:\.\d+)?)$/', $line, $matches)) {
                $rates[trim($matches[1])] = floatval($matches[2]);
            }
        }
        
        return $rates;
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
            'weight_rates' => $this->weight_rates,
            'volume_rates' => $this->volume_rates,
            'city_rates' => $this->city_rates,
            'delivery_zones' => $this->delivery_zones,
            'delivery_hours' => $this->delivery_hours,
            'contact_info' => $this->contact_info
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
        $prefix = 'LOCAL';
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
        // Formato: LOCAL + timestamp + random
        return preg_match('/^LOCAL\d{14}\d{4}$/', $tracking_number);
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
     * Calcular tiempo de entrega estimado
     *
     * @param string $city Ciudad de destino
     * @return int
     */
    public function get_estimated_delivery_days($city) {
        $delivery_days = array(
            'Caracas' => 1,
            'Maracaibo' => 2,
            'Valencia' => 1,
            'Barquisimeto' => 2,
            'Ciudad Guayana' => 3,
            'Mérida' => 3,
            'San Cristóbal' => 3,
            'Cumana' => 2,
            'Maturín' => 2,
            'Puerto La Cruz' => 2,
            'Barcelona' => 2,
            'Valera' => 2,
            'Cabimas' => 2,
            'Punto Fijo' => 2,
            'Porlamar' => 2
        );
        
        return $delivery_days[$city] ?? 2;
    }

    /**
     * Obtener zonas de entrega disponibles
     *
     * @return array
     */
    public function get_delivery_zones() {
        $zones = array();
        $lines = explode("\n", $this->delivery_zones);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            if (preg_match('/^([^:]+):(.+)$/', $line, $matches)) {
                $zones[trim($matches[1])] = trim($matches[2]);
            }
        }
        
        return $zones;
    }

    /**
     * Obtener horarios de entrega
     *
     * @return array
     */
    public function get_delivery_hours() {
        $hours = array();
        $lines = explode("\n", $this->delivery_hours);
        
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
     * Verificar si la ciudad está disponible para entrega
     *
     * @param string $city Ciudad
     * @return bool
     */
    public function is_city_available($city) {
        $rates = $this->parse_city_rates($this->city_rates);
        return isset($rates[$city]);
    }

    /**
     * Obtener ciudades disponibles
     *
     * @return array
     */
    public function get_available_cities() {
        $rates = $this->parse_city_rates($this->city_rates);
        return array_keys($rates);
    }
}
