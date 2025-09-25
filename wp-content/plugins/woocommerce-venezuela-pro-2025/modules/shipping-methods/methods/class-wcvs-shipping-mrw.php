<?php
/**
 * Método de Envío MRW - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el método de envío MRW
 */
class WCVS_Shipping_MRW extends WC_Shipping_Method {

    /**
     * ID del método
     */
    const METHOD_ID = 'wcvs_mrw';

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = self::METHOD_ID;
        $this->instance_id = absint($instance_id);
        $this->method_title = 'MRW';
        $this->method_description = 'Método de envío MRW para Venezuela';

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
        $this->state_rates = $this->get_option('state_rates');
        $this->tracking_enabled = $this->get_option('tracking_enabled');
        $this->insurance_enabled = $this->get_option('insurance_enabled');
        $this->insurance_rate = $this->get_option('insurance_rate');

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
                'label' => 'Habilitar MRW',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'MRW',
                'desc_tip' => true
            ),
            'cost' => array(
                'title' => 'Costo Base',
                'type' => 'number',
                'description' => 'Costo base del envío MRW.',
                'default' => 0,
                'desc_tip' => true
            ),
            'free_shipping_threshold' => array(
                'title' => 'Umbral de Envío Gratis',
                'type' => 'number',
                'description' => 'Monto mínimo para envío gratis.',
                'default' => 0,
                'desc_tip' => true
            ),
            'weight_rates' => array(
                'title' => 'Tarifas por Peso',
                'type' => 'textarea',
                'description' => 'Tarifas por peso en formato: min-max:cost (una por línea)',
                'default' => "0-1:5.00\n1-5:10.00\n5-10:15.00\n10-20:25.00\n20-50:40.00",
                'desc_tip' => true
            ),
            'volume_rates' => array(
                'title' => 'Tarifas por Volumen',
                'type' => 'textarea',
                'description' => 'Tarifas por volumen en formato: min-max:cost (una por línea)',
                'default' => "0-1000:5.00\n1000-5000:10.00\n5000-10000:15.00\n10000-20000:25.00\n20000-50000:40.00",
                'desc_tip' => true
            ),
            'state_rates' => array(
                'title' => 'Tarifas por Estado',
                'type' => 'textarea',
                'description' => 'Tarifas adicionales por estado en formato: state:cost (una por línea)',
                'default' => "Distrito Capital:0.00\nMiranda:2.00\nCarabobo:3.00\nZulia:5.00\nLara:4.00",
                'desc_tip' => true
            ),
            'tracking_enabled' => array(
                'title' => 'Habilitar Seguimiento',
                'type' => 'checkbox',
                'label' => 'Habilitar seguimiento de envíos MRW',
                'default' => 'yes'
            ),
            'insurance_enabled' => array(
                'title' => 'Habilitar Seguro',
                'type' => 'checkbox',
                'label' => 'Habilitar seguro de envío',
                'default' => 'yes'
            ),
            'insurance_rate' => array(
                'title' => 'Tasa de Seguro (%)',
                'type' => 'number',
                'description' => 'Porcentaje del valor del pedido para el seguro.',
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
        $weight = 0;
        $volume = 0;
        $state = '';

        // Calcular peso y volumen del paquete
        foreach ($package['contents'] as $item_id => $values) {
            $product = $values['data'];
            $quantity = $values['quantity'];
            
            $weight += $product->get_weight() * $quantity;
            $volume += ($product->get_length() * $product->get_width() * $product->get_height()) * $quantity;
        }

        // Obtener estado de destino
        if (isset($package['destination']['state'])) {
            $state = $package['destination']['state'];
        }

        // Calcular costo base
        $cost = $this->calculate_base_cost($weight, $volume, $state);

        // Verificar envío gratis
        if ($this->free_shipping_threshold > 0 && $package['contents_cost'] >= $this->free_shipping_threshold) {
            $cost = 0;
        }

        // Añadir costo de seguro
        if ($this->insurance_enabled && $cost > 0) {
            $insurance_cost = ($package['contents_cost'] * $this->insurance_rate) / 100;
            $cost += $insurance_cost;
        }

        // Crear tasa de envío
        $rate = array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => $cost,
            'meta_data' => array(
                'weight' => $weight,
                'volume' => $volume,
                'state' => $state,
                'tracking_enabled' => $this->tracking_enabled,
                'insurance_enabled' => $this->insurance_enabled
            )
        );

        $this->add_rate($rate);
    }

    /**
     * Calcular costo base
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $state Estado de destino
     * @return float
     */
    private function calculate_base_cost($weight, $volume, $state) {
        $cost = 0;

        // Calcular costo por peso
        $weight_cost = $this->calculate_weight_cost($weight);
        
        // Calcular costo por volumen
        $volume_cost = $this->calculate_volume_cost($volume);
        
        // Usar el costo más alto
        $cost = max($weight_cost, $volume_cost);
        
        // Añadir costo base
        $cost += $this->cost;
        
        // Añadir costo por estado
        $state_cost = $this->calculate_state_cost($state);
        $cost += $state_cost;
        
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
     * Calcular costo por estado
     *
     * @param string $state Estado de destino
     * @return float
     */
    private function calculate_state_cost($state) {
        $rates = $this->parse_state_rates($this->state_rates);
        
        if (isset($rates[$state])) {
            return $rates[$state];
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
     * Parsear tarifas por estado
     *
     * @param string $rates_string Cadena de tarifas
     * @return array
     */
    private function parse_state_rates($rates_string) {
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
            'state_rates' => $this->state_rates,
            'tracking_enabled' => $this->tracking_enabled,
            'insurance_enabled' => $this->insurance_enabled,
            'insurance_rate' => $this->insurance_rate
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
        $prefix = 'MRW';
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
        // Formato: MRW + timestamp + random
        return preg_match('/^MRW\d{14}\d{4}$/', $tracking_number);
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
     * Calcular tiempo de entrega estimado
     *
     * @param string $state Estado de destino
     * @return int
     */
    public function get_estimated_delivery_days($state) {
        $delivery_days = array(
            'Distrito Capital' => 1,
            'Miranda' => 1,
            'Vargas' => 1,
            'Carabobo' => 2,
            'Aragua' => 2,
            'Lara' => 3,
            'Yaracuy' => 3,
            'Falcón' => 4,
            'Zulia' => 4,
            'Táchira' => 5,
            'Mérida' => 5,
            'Trujillo' => 5,
            'Barinas' => 4,
            'Portuguesa' => 4,
            'Cojedes' => 3,
            'Guárico' => 3,
            'Anzoátegui' => 4,
            'Monagas' => 4,
            'Sucre' => 5,
            'Delta Amacuro' => 6,
            'Bolívar' => 5,
            'Amazonas' => 7,
            'Apure' => 6,
            'Nueva Esparta' => 3
        );
        
        return $delivery_days[$state] ?? 5;
    }

    /**
     * Obtener estados disponibles
     *
     * @return array
     */
    public function get_available_states() {
        return array(
            'Distrito Capital' => 'Distrito Capital',
            'Miranda' => 'Miranda',
            'Vargas' => 'Vargas',
            'Carabobo' => 'Carabobo',
            'Aragua' => 'Aragua',
            'Lara' => 'Lara',
            'Yaracuy' => 'Yaracuy',
            'Falcón' => 'Falcón',
            'Zulia' => 'Zulia',
            'Táchira' => 'Táchira',
            'Mérida' => 'Mérida',
            'Trujillo' => 'Trujillo',
            'Barinas' => 'Barinas',
            'Portuguesa' => 'Portuguesa',
            'Cojedes' => 'Cojedes',
            'Guárico' => 'Guárico',
            'Anzoátegui' => 'Anzoátegui',
            'Monagas' => 'Monagas',
            'Sucre' => 'Sucre',
            'Delta Amacuro' => 'Delta Amacuro',
            'Bolívar' => 'Bolívar',
            'Amazonas' => 'Amazonas',
            'Apure' => 'Apure',
            'Nueva Esparta' => 'Nueva Esparta'
        );
    }
}
