<?php
/**
 * Método de Envío Tealca - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el método de envío Tealca
 */
class WCVS_Shipping_Tealca extends WC_Shipping_Method {

    /**
     * ID del método
     */
    const METHOD_ID = 'wcvs_tealca';

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = self::METHOD_ID;
        $this->instance_id = absint($instance_id);
        $this->method_title = 'Tealca';
        $this->method_description = 'Método de envío Tealca para Venezuela';

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
        $this->fragile_handling = $this->get_option('fragile_handling');
        $this->fragile_rate = $this->get_option('fragile_rate');

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
                'label' => 'Habilitar Tealca',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Tealca',
                'desc_tip' => true
            ),
            'cost' => array(
                'title' => 'Costo Base',
                'type' => 'number',
                'description' => 'Costo base del envío Tealca.',
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
                'default' => "0-1:6.00\n1-5:12.00\n5-10:18.00\n10-20:30.00\n20-50:48.00",
                'desc_tip' => true
            ),
            'volume_rates' => array(
                'title' => 'Tarifas por Volumen',
                'type' => 'textarea',
                'description' => 'Tarifas por volumen en formato: min-max:cost (una por línea)',
                'default' => "0-1000:6.00\n1000-5000:12.00\n5000-10000:18.00\n10000-20000:30.00\n20000-50000:48.00",
                'desc_tip' => true
            ),
            'state_rates' => array(
                'title' => 'Tarifas por Estado',
                'type' => 'textarea',
                'description' => 'Tarifas adicionales por estado en formato: state:cost (una por línea)',
                'default' => "Distrito Capital:0.00\nMiranda:2.50\nCarabobo:4.00\nZulia:6.50\nLara:5.00",
                'desc_tip' => true
            ),
            'tracking_enabled' => array(
                'title' => 'Habilitar Seguimiento',
                'type' => 'checkbox',
                'label' => 'Habilitar seguimiento de envíos Tealca',
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
                'default' => 1.2,
                'desc_tip' => true
            ),
            'fragile_handling' => array(
                'title' => 'Habilitar Manejo Frágil',
                'type' => 'checkbox',
                'label' => 'Habilitar opción de manejo frágil',
                'default' => 'yes'
            ),
            'fragile_rate' => array(
                'title' => 'Tasa Manejo Frágil (%)',
                'type' => 'number',
                'description' => 'Porcentaje adicional para manejo frágil.',
                'default' => 25,
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
        $has_fragile = false;

        // Calcular peso y volumen del paquete
        foreach ($package['contents'] as $item_id => $values) {
            $product = $values['data'];
            $quantity = $values['quantity'];
            
            $weight += $product->get_weight() * $quantity;
            $volume += ($product->get_length() * $product->get_width() * $product->get_height()) * $quantity;
            
            // Verificar si hay productos frágiles
            if ($product->get_meta('_fragile') === 'yes') {
                $has_fragile = true;
            }
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

        // Añadir costo de manejo frágil
        if ($this->fragile_handling && $has_fragile && $cost > 0) {
            $fragile_cost = ($cost * $this->fragile_rate) / 100;
            $cost += $fragile_cost;
        }

        // Crear tasa de envío estándar
        $rate = array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => $cost,
            'meta_data' => array(
                'weight' => $weight,
                'volume' => $volume,
                'state' => $state,
                'tracking_enabled' => $this->tracking_enabled,
                'insurance_enabled' => $this->insurance_enabled,
                'fragile_handling' => $this->fragile_handling,
                'has_fragile' => $has_fragile
            )
        );

        $this->add_rate($rate);

        // Añadir opción de manejo frágil si está habilitada y hay productos frágiles
        if ($this->fragile_handling && $has_fragile && $cost > 0) {
            $fragile_cost = $cost + ($cost * $this->fragile_rate / 100);
            
            $fragile_rate = array(
                'id' => $this->get_rate_id() . '_fragile',
                'label' => $this->title . ' - Manejo Frágil',
                'cost' => $fragile_cost,
                'meta_data' => array(
                    'weight' => $weight,
                    'volume' => $volume,
                    'state' => $state,
                    'tracking_enabled' => $this->tracking_enabled,
                    'insurance_enabled' => $this->insurance_enabled,
                    'fragile_handling' => true,
                    'has_fragile' => true,
                    'delivery_days' => $this->get_fragile_delivery_days($state)
                )
            );

            $this->add_rate($fragile_rate);
        }
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
            'insurance_rate' => $this->insurance_rate,
            'fragile_handling' => $this->fragile_handling,
            'fragile_rate' => $this->fragile_rate
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
        $prefix = 'TEALCA';
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
        // Formato: TEALCA + timestamp + random
        return preg_match('/^TEALCA\d{14}\d{4}$/', $tracking_number);
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
        // En una implementación real, esto se conectaría con la API de Tealca
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
     * Calcular tiempo de entrega estimado
     *
     * @param string $state Estado de destino
     * @return int
     */
    public function get_estimated_delivery_days($state) {
        $delivery_days = array(
            'Distrito Capital' => 2,
            'Miranda' => 2,
            'Vargas' => 2,
            'Carabobo' => 3,
            'Aragua' => 3,
            'Lara' => 4,
            'Yaracuy' => 4,
            'Falcón' => 5,
            'Zulia' => 5,
            'Táchira' => 6,
            'Mérida' => 6,
            'Trujillo' => 6,
            'Barinas' => 5,
            'Portuguesa' => 5,
            'Cojedes' => 4,
            'Guárico' => 4,
            'Anzoátegui' => 5,
            'Monagas' => 5,
            'Sucre' => 6,
            'Delta Amacuro' => 7,
            'Bolívar' => 6,
            'Amazonas' => 8,
            'Apure' => 7,
            'Nueva Esparta' => 4
        );
        
        return $delivery_days[$state] ?? 6;
    }

    /**
     * Obtener tiempo de entrega para manejo frágil
     *
     * @param string $state Estado de destino
     * @return int
     */
    public function get_fragile_delivery_days($state) {
        $delivery_days = array(
            'Distrito Capital' => 3,
            'Miranda' => 3,
            'Vargas' => 3,
            'Carabobo' => 4,
            'Aragua' => 4,
            'Lara' => 5,
            'Yaracuy' => 5,
            'Falcón' => 6,
            'Zulia' => 6,
            'Táchira' => 7,
            'Mérida' => 7,
            'Trujillo' => 7,
            'Barinas' => 6,
            'Portuguesa' => 6,
            'Cojedes' => 5,
            'Guárico' => 5,
            'Anzoátegui' => 6,
            'Monagas' => 6,
            'Sucre' => 7,
            'Delta Amacuro' => 8,
            'Bolívar' => 7,
            'Amazonas' => 9,
            'Apure' => 8,
            'Nueva Esparta' => 5
        );
        
        return $delivery_days[$state] ?? 7;
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
