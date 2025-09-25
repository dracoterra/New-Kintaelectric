<?php
/**
 * Calculadora de Costos de Envío - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para calcular costos de envío
 */
class WCVS_Shipping_Calculator {

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
        // Hooks para cálculo de envío
        add_action('wp_ajax_wcvs_calculate_shipping', array($this, 'ajax_calculate_shipping'));
        add_action('wp_ajax_nopriv_wcvs_calculate_shipping', array($this, 'ajax_calculate_shipping'));
        
        // Hooks para comparación de métodos
        add_action('wp_ajax_wcvs_compare_shipping_methods', array($this, 'ajax_compare_shipping_methods'));
        add_action('wp_ajax_nopriv_wcvs_compare_shipping_methods', array($this, 'ajax_compare_shipping_methods'));
        
        // Hooks para estimación de tiempo
        add_action('wp_ajax_wcvs_estimate_delivery_time', array($this, 'ajax_estimate_delivery_time'));
        add_action('wp_ajax_nopriv_wcvs_estimate_delivery_time', array($this, 'ajax_estimate_delivery_time'));
    }

    /**
     * Calcular costo de envío via AJAX
     */
    public function ajax_calculate_shipping() {
        check_ajax_referer('wcvs_shipping_calculator_nonce', 'nonce');
        
        $weight = floatval($_POST['weight'] ?? 0);
        $volume = floatval($_POST['volume'] ?? 0);
        $destination = sanitize_text_field($_POST['destination'] ?? '');
        $method = sanitize_text_field($_POST['method'] ?? '');
        $value = floatval($_POST['value'] ?? 0);
        
        $result = $this->calculate_shipping_cost($weight, $volume, $destination, $method, $value);
        
        wp_send_json_success($result);
    }

    /**
     * Comparar métodos de envío via AJAX
     */
    public function ajax_compare_shipping_methods() {
        check_ajax_referer('wcvs_shipping_calculator_nonce', 'nonce');
        
        $weight = floatval($_POST['weight'] ?? 0);
        $volume = floatval($_POST['volume'] ?? 0);
        $destination = sanitize_text_field($_POST['destination'] ?? '');
        $value = floatval($_POST['value'] ?? 0);
        
        $result = $this->compare_shipping_methods($weight, $volume, $destination, $value);
        
        wp_send_json_success($result);
    }

    /**
     * Estimar tiempo de entrega via AJAX
     */
    public function ajax_estimate_delivery_time() {
        check_ajax_referer('wcvs_shipping_calculator_nonce', 'nonce');
        
        $method = sanitize_text_field($_POST['method'] ?? '');
        $destination = sanitize_text_field($_POST['destination'] ?? '');
        
        $result = $this->estimate_delivery_time($method, $destination);
        
        wp_send_json_success($result);
    }

    /**
     * Calcular costo de envío
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $destination Destino
     * @param string $method Método de envío
     * @param float $value Valor del pedido
     * @return array
     */
    public function calculate_shipping_cost($weight, $volume, $destination, $method, $value) {
        $cost = 0;
        $details = array();
        
        switch ($method) {
            case 'mrw':
                $result = $this->calculate_mrw_cost($weight, $volume, $destination, $value);
                break;
            case 'zoom':
                $result = $this->calculate_zoom_cost($weight, $volume, $destination, $value);
                break;
            case 'tealca':
                $result = $this->calculate_tealca_cost($weight, $volume, $destination, $value);
                break;
            case 'local_delivery':
                $result = $this->calculate_local_delivery_cost($weight, $volume, $destination, $value);
                break;
            case 'pickup':
                $result = $this->calculate_pickup_cost($weight, $volume, $destination, $value);
                break;
            default:
                $result = array(
                    'cost' => 0,
                    'details' => array(),
                    'error' => 'Método de envío no válido'
                );
        }
        
        return $result;
    }

    /**
     * Calcular costo MRW
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $destination Destino
     * @param float $value Valor del pedido
     * @return array
     */
    private function calculate_mrw_cost($weight, $volume, $destination, $value) {
        $cost = 0;
        $details = array();
        
        // Tarifas por peso
        $weight_rates = array(
            array('min' => 0, 'max' => 1, 'cost' => 5.00),
            array('min' => 1, 'max' => 5, 'cost' => 10.00),
            array('min' => 5, 'max' => 10, 'cost' => 15.00),
            array('min' => 10, 'max' => 20, 'cost' => 25.00),
            array('min' => 20, 'max' => 50, 'cost' => 40.00)
        );
        
        // Tarifas por volumen
        $volume_rates = array(
            array('min' => 0, 'max' => 1000, 'cost' => 5.00),
            array('min' => 1000, 'max' => 5000, 'cost' => 10.00),
            array('min' => 5000, 'max' => 10000, 'cost' => 15.00),
            array('min' => 10000, 'max' => 20000, 'cost' => 25.00),
            array('min' => 20000, 'max' => 50000, 'cost' => 40.00)
        );
        
        // Calcular costo por peso
        $weight_cost = $this->calculate_rate_cost($weight, $weight_rates);
        $details['weight_cost'] = $weight_cost;
        
        // Calcular costo por volumen
        $volume_cost = $this->calculate_rate_cost($volume, $volume_rates);
        $details['volume_cost'] = $volume_cost;
        
        // Usar el costo más alto
        $cost = max($weight_cost, $volume_cost);
        
        // Añadir costo por destino
        $destination_cost = $this->get_destination_cost($destination, 'mrw');
        $details['destination_cost'] = $destination_cost;
        $cost += $destination_cost;
        
        // Añadir seguro (1% del valor)
        $insurance_cost = $value * 0.01;
        $details['insurance_cost'] = $insurance_cost;
        $cost += $insurance_cost;
        
        $details['total_cost'] = $cost;
        $details['method'] = 'MRW';
        $details['estimated_days'] = $this->get_estimated_delivery_days($destination, 'mrw');
        
        return array(
            'cost' => $cost,
            'details' => $details
        );
    }

    /**
     * Calcular costo Zoom
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $destination Destino
     * @param float $value Valor del pedido
     * @return array
     */
    private function calculate_zoom_cost($weight, $volume, $destination, $value) {
        $cost = 0;
        $details = array();
        
        // Tarifas por peso
        $weight_rates = array(
            array('min' => 0, 'max' => 1, 'cost' => 4.50),
            array('min' => 1, 'max' => 5, 'cost' => 9.00),
            array('min' => 5, 'max' => 10, 'cost' => 14.00),
            array('min' => 10, 'max' => 20, 'cost' => 24.00),
            array('min' => 20, 'max' => 50, 'cost' => 38.00)
        );
        
        // Tarifas por volumen
        $volume_rates = array(
            array('min' => 0, 'max' => 1000, 'cost' => 4.50),
            array('min' => 1000, 'max' => 5000, 'cost' => 9.00),
            array('min' => 5000, 'max' => 10000, 'cost' => 14.00),
            array('min' => 10000, 'max' => 20000, 'cost' => 24.00),
            array('min' => 20000, 'max' => 50000, 'cost' => 38.00)
        );
        
        // Calcular costo por peso
        $weight_cost = $this->calculate_rate_cost($weight, $weight_rates);
        $details['weight_cost'] = $weight_cost;
        
        // Calcular costo por volumen
        $volume_cost = $this->calculate_rate_cost($volume, $volume_rates);
        $details['volume_cost'] = $volume_cost;
        
        // Usar el costo más alto
        $cost = max($weight_cost, $volume_cost);
        
        // Añadir costo por destino
        $destination_cost = $this->get_destination_cost($destination, 'zoom');
        $details['destination_cost'] = $destination_cost;
        $cost += $destination_cost;
        
        // Añadir seguro (0.8% del valor)
        $insurance_cost = $value * 0.008;
        $details['insurance_cost'] = $insurance_cost;
        $cost += $insurance_cost;
        
        $details['total_cost'] = $cost;
        $details['method'] = 'Zoom';
        $details['estimated_days'] = $this->get_estimated_delivery_days($destination, 'zoom');
        
        return array(
            'cost' => $cost,
            'details' => $details
        );
    }

    /**
     * Calcular costo Tealca
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $destination Destino
     * @param float $value Valor del pedido
     * @return array
     */
    private function calculate_tealca_cost($weight, $volume, $destination, $value) {
        $cost = 0;
        $details = array();
        
        // Tarifas por peso
        $weight_rates = array(
            array('min' => 0, 'max' => 1, 'cost' => 6.00),
            array('min' => 1, 'max' => 5, 'cost' => 12.00),
            array('min' => 5, 'max' => 10, 'cost' => 18.00),
            array('min' => 10, 'max' => 20, 'cost' => 30.00),
            array('min' => 20, 'max' => 50, 'cost' => 48.00)
        );
        
        // Tarifas por volumen
        $volume_rates = array(
            array('min' => 0, 'max' => 1000, 'cost' => 6.00),
            array('min' => 1000, 'max' => 5000, 'cost' => 12.00),
            array('min' => 5000, 'max' => 10000, 'cost' => 18.00),
            array('min' => 10000, 'max' => 20000, 'cost' => 30.00),
            array('min' => 20000, 'max' => 50000, 'cost' => 48.00)
        );
        
        // Calcular costo por peso
        $weight_cost = $this->calculate_rate_cost($weight, $weight_rates);
        $details['weight_cost'] = $weight_cost;
        
        // Calcular costo por volumen
        $volume_cost = $this->calculate_rate_cost($volume, $volume_rates);
        $details['volume_cost'] = $volume_cost;
        
        // Usar el costo más alto
        $cost = max($weight_cost, $volume_cost);
        
        // Añadir costo por destino
        $destination_cost = $this->get_destination_cost($destination, 'tealca');
        $details['destination_cost'] = $destination_cost;
        $cost += $destination_cost;
        
        // Añadir seguro (1.2% del valor)
        $insurance_cost = $value * 0.012;
        $details['insurance_cost'] = $insurance_cost;
        $cost += $insurance_cost;
        
        $details['total_cost'] = $cost;
        $details['method'] = 'Tealca';
        $details['estimated_days'] = $this->get_estimated_delivery_days($destination, 'tealca');
        
        return array(
            'cost' => $cost,
            'details' => $details
        );
    }

    /**
     * Calcular costo Local Delivery
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $destination Destino
     * @param float $value Valor del pedido
     * @return array
     */
    private function calculate_local_delivery_cost($weight, $volume, $destination, $value) {
        $cost = 0;
        $details = array();
        
        // Tarifas por peso
        $weight_rates = array(
            array('min' => 0, 'max' => 5, 'cost' => 3.00),
            array('min' => 5, 'max' => 15, 'cost' => 5.00),
            array('min' => 15, 'max' => 30, 'cost' => 8.00),
            array('min' => 30, 'max' => 50, 'cost' => 12.00),
            array('min' => 50, 'max' => 100, 'cost' => 18.00)
        );
        
        // Tarifas por volumen
        $volume_rates = array(
            array('min' => 0, 'max' => 5000, 'cost' => 3.00),
            array('min' => 5000, 'max' => 15000, 'cost' => 5.00),
            array('min' => 15000, 'max' => 30000, 'cost' => 8.00),
            array('min' => 30000, 'max' => 50000, 'cost' => 12.00),
            array('min' => 50000, 'max' => 100000, 'cost' => 18.00)
        );
        
        // Calcular costo por peso
        $weight_cost = $this->calculate_rate_cost($weight, $weight_rates);
        $details['weight_cost'] = $weight_cost;
        
        // Calcular costo por volumen
        $volume_cost = $this->calculate_rate_cost($volume, $volume_rates);
        $details['volume_cost'] = $volume_cost;
        
        // Usar el costo más alto
        $cost = max($weight_cost, $volume_cost);
        
        // Añadir costo por destino
        $destination_cost = $this->get_destination_cost($destination, 'local_delivery');
        $details['destination_cost'] = $destination_cost;
        $cost += $destination_cost;
        
        $details['total_cost'] = $cost;
        $details['method'] = 'Local Delivery';
        $details['estimated_days'] = $this->get_estimated_delivery_days($destination, 'local_delivery');
        
        return array(
            'cost' => $cost,
            'details' => $details
        );
    }

    /**
     * Calcular costo Pickup
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $destination Destino
     * @param float $value Valor del pedido
     * @return array
     */
    private function calculate_pickup_cost($weight, $volume, $destination, $value) {
        $cost = 0;
        $details = array();
        
        // Pickup es gratis
        $details['total_cost'] = $cost;
        $details['method'] = 'Pickup';
        $details['estimated_days'] = 1; // 1 día para preparar
        
        return array(
            'cost' => $cost,
            'details' => $details
        );
    }

    /**
     * Calcular costo según tarifas
     *
     * @param float $value Valor a calcular
     * @param array $rates Tarifas
     * @return float
     */
    private function calculate_rate_cost($value, $rates) {
        foreach ($rates as $rate) {
            if ($value >= $rate['min'] && $value <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener costo por destino
     *
     * @param string $destination Destino
     * @param string $method Método de envío
     * @return float
     */
    private function get_destination_cost($destination, $method) {
        $destination_costs = array(
            'mrw' => array(
                'Distrito Capital' => 0.00,
                'Miranda' => 2.00,
                'Carabobo' => 3.00,
                'Zulia' => 5.00,
                'Lara' => 4.00
            ),
            'zoom' => array(
                'Distrito Capital' => 0.00,
                'Miranda' => 1.50,
                'Carabobo' => 2.50,
                'Zulia' => 4.50,
                'Lara' => 3.50
            ),
            'tealca' => array(
                'Distrito Capital' => 0.00,
                'Miranda' => 2.50,
                'Carabobo' => 4.00,
                'Zulia' => 6.50,
                'Lara' => 5.00
            ),
            'local_delivery' => array(
                'Caracas' => 0.00,
                'Maracaibo' => 5.00,
                'Valencia' => 3.00,
                'Barquisimeto' => 4.00,
                'Ciudad Guayana' => 6.00
            )
        );
        
        return $destination_costs[$method][$destination] ?? 0;
    }

    /**
     * Obtener días estimados de entrega
     *
     * @param string $destination Destino
     * @param string $method Método de envío
     * @return int
     */
    private function get_estimated_delivery_days($destination, $method) {
        $delivery_days = array(
            'mrw' => array(
                'Distrito Capital' => 1,
                'Miranda' => 1,
                'Carabobo' => 2,
                'Zulia' => 4,
                'Lara' => 3
            ),
            'zoom' => array(
                'Distrito Capital' => 1,
                'Miranda' => 1,
                'Carabobo' => 2,
                'Zulia' => 3,
                'Lara' => 2
            ),
            'tealca' => array(
                'Distrito Capital' => 2,
                'Miranda' => 2,
                'Carabobo' => 3,
                'Zulia' => 5,
                'Lara' => 4
            ),
            'local_delivery' => array(
                'Caracas' => 1,
                'Maracaibo' => 2,
                'Valencia' => 1,
                'Barquisimeto' => 2,
                'Ciudad Guayana' => 3
            )
        );
        
        return $delivery_days[$method][$destination] ?? 5;
    }

    /**
     * Comparar métodos de envío
     *
     * @param float $weight Peso en kg
     * @param float $volume Volumen en cm³
     * @param string $destination Destino
     * @param float $value Valor del pedido
     * @return array
     */
    public function compare_shipping_methods($weight, $volume, $destination, $value) {
        $methods = array('mrw', 'zoom', 'tealca', 'local_delivery', 'pickup');
        $comparison = array();
        
        foreach ($methods as $method) {
            $result = $this->calculate_shipping_cost($weight, $volume, $destination, $method, $value);
            $comparison[] = $result;
        }
        
        // Ordenar por costo
        usort($comparison, function($a, $b) {
            return $a['cost'] <=> $b['cost'];
        });
        
        return $comparison;
    }

    /**
     * Estimar tiempo de entrega
     *
     * @param string $method Método de envío
     * @param string $destination Destino
     * @return array
     */
    public function estimate_delivery_time($method, $destination) {
        $days = $this->get_estimated_delivery_days($destination, $method);
        $estimated_date = date('Y-m-d', strtotime("+{$days} days"));
        
        return array(
            'method' => $method,
            'destination' => $destination,
            'estimated_days' => $days,
            'estimated_date' => $estimated_date,
            'estimated_date_formatted' => date_i18n(get_option('date_format'), strtotime($estimated_date))
        );
    }

    /**
     * Obtener métodos de envío disponibles
     *
     * @return array
     */
    public function get_available_methods() {
        return array(
            'mrw' => 'MRW',
            'zoom' => 'Zoom',
            'tealca' => 'Tealca',
            'local_delivery' => 'Entrega Local',
            'pickup' => 'Recogida en Tienda'
        );
    }

    /**
     * Obtener destinos disponibles
     *
     * @return array
     */
    public function get_available_destinations() {
        return array(
            'Distrito Capital' => 'Distrito Capital',
            'Miranda' => 'Miranda',
            'Carabobo' => 'Carabobo',
            'Zulia' => 'Zulia',
            'Lara' => 'Lara',
            'Caracas' => 'Caracas',
            'Maracaibo' => 'Maracaibo',
            'Valencia' => 'Valencia',
            'Barquisimeto' => 'Barquisimeto',
            'Ciudad Guayana' => 'Ciudad Guayana'
        );
    }

    /**
     * Validar datos de entrada
     *
     * @param array $data Datos a validar
     * @return array
     */
    public function validate_input_data($data) {
        $errors = array();
        
        if (!isset($data['weight']) || $data['weight'] < 0) {
            $errors[] = 'Peso inválido';
        }
        
        if (!isset($data['volume']) || $data['volume'] < 0) {
            $errors[] = 'Volumen inválido';
        }
        
        if (!isset($data['destination']) || empty($data['destination'])) {
            $errors[] = 'Destino requerido';
        }
        
        if (!isset($data['method']) || empty($data['method'])) {
            $errors[] = 'Método de envío requerido';
        }
        
        if (!isset($data['value']) || $data['value'] < 0) {
            $errors[] = 'Valor del pedido inválido';
        }
        
        return $errors;
    }
}
