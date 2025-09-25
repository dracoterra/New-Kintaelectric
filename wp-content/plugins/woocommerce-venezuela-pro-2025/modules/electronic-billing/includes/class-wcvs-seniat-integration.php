<?php
/**
 * Integración con SENIAT - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para integrar con sistemas SENIAT
 */
class WCVS_SENIAT_Integration {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * URL base de la API de SENIAT
     *
     * @var string
     */
    private $api_base_url = 'https://api.seniat.gob.ve';

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('electronic_billing', array());
    }

    /**
     * Enviar factura a SENIAT
     *
     * @param array $invoice_data Datos de la factura
     * @return array|false
     */
    public function send_invoice_to_seniat($invoice_data) {
        // Validar datos de la factura
        if (!$this->validate_invoice_data($invoice_data)) {
            return false;
        }

        // Preparar datos para SENIAT
        $seniat_data = $this->prepare_seniat_data($invoice_data);

        // Enviar a SENIAT
        $response = $this->send_to_seniat_api($seniat_data);

        // Procesar respuesta
        if ($response && $response['success']) {
            $this->update_invoice_seniat_status($invoice_data['order_id'], 'sent', $response['data']);
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Factura #{$invoice_data['invoice_number']} enviada a SENIAT correctamente");
            return $response['data'];
        } else {
            $this->update_invoice_seniat_status($invoice_data['order_id'], 'error', $response['error'] ?? 'Error desconocido');
            WCVS_Logger::error(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Error al enviar factura #{$invoice_data['invoice_number']} a SENIAT: " . ($response['error'] ?? 'Error desconocido'));
            return false;
        }
    }

    /**
     * Validar datos de la factura
     *
     * @param array $invoice_data Datos de la factura
     * @return bool
     */
    private function validate_invoice_data($invoice_data) {
        $required_fields = array('invoice_number', 'order_id', 'company', 'customer', 'items', 'totals');
        
        foreach ($required_fields as $field) {
            if (!isset($invoice_data[$field]) || empty($invoice_data[$field])) {
                WCVS_Logger::error(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Campo requerido faltante en datos de factura: {$field}");
                return false;
            }
        }

        // Validar RIF de la empresa
        if (!$this->validate_rif($invoice_data['company']['rif'])) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "RIF de empresa inválido: {$invoice_data['company']['rif']}");
            return false;
        }

        // Validar RIF del cliente
        if (!$this->validate_rif($invoice_data['customer']['rif'])) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "RIF de cliente inválido: {$invoice_data['customer']['rif']}");
            return false;
        }

        return true;
    }

    /**
     * Validar RIF
     *
     * @param string $rif RIF a validar
     * @return bool
     */
    private function validate_rif($rif) {
        // Patrón para RIF venezolano: X-XXXXXXXX-X
        $pattern = '/^[VEGJ]-[0-9]{8}-[0-9]$/';
        return preg_match($pattern, $rif);
    }

    /**
     * Preparar datos para SENIAT
     *
     * @param array $invoice_data Datos de la factura
     * @return array
     */
    private function prepare_seniat_data($invoice_data) {
        $seniat_data = array(
            'factura' => array(
                'numero' => $invoice_data['invoice_number'],
                'fecha' => $invoice_data['order']['date'],
                'tipo_documento' => '01', // Factura
                'moneda' => $invoice_data['order']['currency'],
                'tipo_cambio' => $this->get_exchange_rate($invoice_data['order']['currency'])
            ),
            'empresa' => array(
                'rif' => $invoice_data['company']['rif'],
                'razon_social' => $invoice_data['company']['name'],
                'direccion' => $invoice_data['company']['address'],
                'telefono' => $invoice_data['company']['phone'],
                'email' => $invoice_data['company']['email']
            ),
            'cliente' => array(
                'rif' => $invoice_data['customer']['rif'],
                'razon_social' => $invoice_data['customer']['name'],
                'direccion' => $invoice_data['customer']['address'],
                'telefono' => $invoice_data['customer']['phone'],
                'email' => $invoice_data['customer']['email']
            ),
            'items' => array(),
            'totales' => array(
                'subtotal' => $invoice_data['totals']['subtotal'],
                'iva' => $invoice_data['fiscal']['iva_amount'],
                'igtf' => $invoice_data['fiscal']['igtf_amount'],
                'islr' => $invoice_data['fiscal']['islr_amount'],
                'total' => $invoice_data['totals']['total']
            )
        );

        // Preparar items
        foreach ($invoice_data['items'] as $item) {
            $seniat_data['items'][] = array(
                'descripcion' => $item['name'],
                'codigo' => $item['sku'],
                'cantidad' => $item['quantity'],
                'precio_unitario' => $item['price'] / $item['quantity'],
                'precio_total' => $item['price'],
                'iva' => $item['tax'],
                'total' => $item['total']
            );
        }

        return $seniat_data;
    }

    /**
     * Obtener tasa de cambio
     *
     * @param string $currency Moneda
     * @return float
     */
    private function get_exchange_rate($currency) {
        if ($currency === 'VES') {
            return 1.0;
        }

        // Usar integración con BCV Dólar Tracker
        $bcv_integration = WCVS_Core::get_instance()->get_bcv_integration();
        if ($bcv_integration) {
            $rate = $bcv_integration->get_current_rate();
            if ($rate) {
                return $rate;
            }
        }

        // Tasa por defecto si no se puede obtener
        return 36.0;
    }

    /**
     * Enviar datos a API de SENIAT
     *
     * @param array $data Datos a enviar
     * @return array|false
     */
    private function send_to_seniat_api($data) {
        // Verificar si está en modo de prueba
        $test_mode = $this->settings['seniat']['test_mode'] ?? true;
        
        if ($test_mode) {
            return $this->simulate_seniat_response($data);
        }

        // Configurar headers
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . ($this->settings['seniat']['api_token'] ?? ''),
            'User-Agent' => 'WCVS/1.0'
        );

        // Preparar datos para envío
        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Realizar petición HTTP
        $response = wp_remote_post($this->api_base_url . '/api/facturas', array(
            'headers' => $headers,
            'body' => $json_data,
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 200) {
            $response_data = json_decode($response_body, true);
            return array(
                'success' => true,
                'data' => $response_data
            );
        } else {
            return array(
                'success' => false,
                'error' => "Error HTTP {$response_code}: {$response_body}"
            );
        }
    }

    /**
     * Simular respuesta de SENIAT (modo de prueba)
     *
     * @param array $data Datos enviados
     * @return array
     */
    private function simulate_seniat_response($data) {
        // Simular delay de red
        sleep(1);

        // Simular respuesta exitosa
        return array(
            'success' => true,
            'data' => array(
                'numero_control' => 'CTRL-' . wp_rand(100000, 999999),
                'fecha_procesamiento' => current_time('Y-m-d H:i:s'),
                'estado' => 'procesado',
                'mensaje' => 'Factura procesada correctamente',
                'codigo_respuesta' => '200',
                'numero_autorizacion' => 'AUTH-' . wp_rand(100000, 999999)
            )
        );
    }

    /**
     * Actualizar estado de factura en SENIAT
     *
     * @param int $order_id ID del pedido
     * @param string $status Estado
     * @param array $data Datos adicionales
     */
    private function update_invoice_seniat_status($order_id, $status, $data = array()) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $order->update_meta_data('_wcvs_seniat_status', $status);
        $order->update_meta_data('_wcvs_seniat_data', $data);
        $order->update_meta_data('_wcvs_seniat_updated_at', current_time('mysql'));
        $order->save();
    }

    /**
     * Consultar estado de factura en SENIAT
     *
     * @param string $invoice_number Número de factura
     * @return array|false
     */
    public function check_invoice_status($invoice_number) {
        $test_mode = $this->settings['seniat']['test_mode'] ?? true;
        
        if ($test_mode) {
            return $this->simulate_status_check($invoice_number);
        }

        // Configurar headers
        $headers = array(
            'Authorization' => 'Bearer ' . ($this->settings['seniat']['api_token'] ?? ''),
            'User-Agent' => 'WCVS/1.0'
        );

        // Realizar petición HTTP
        $response = wp_remote_get($this->api_base_url . '/api/facturas/' . urlencode($invoice_number), array(
            'headers' => $headers,
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 200) {
            return json_decode($response_body, true);
        }

        return false;
    }

    /**
     * Simular consulta de estado (modo de prueba)
     *
     * @param string $invoice_number Número de factura
     * @return array
     */
    private function simulate_status_check($invoice_number) {
        return array(
            'numero_factura' => $invoice_number,
            'estado' => 'procesado',
            'fecha_procesamiento' => current_time('Y-m-d H:i:s'),
            'numero_control' => 'CTRL-' . wp_rand(100000, 999999),
            'numero_autorizacion' => 'AUTH-' . wp_rand(100000, 999999)
        );
    }

    /**
     * Obtener reportes de SENIAT
     *
     * @param array $filters Filtros
     * @return array|false
     */
    public function get_seniat_reports($filters = array()) {
        $test_mode = $this->settings['seniat']['test_mode'] ?? true;
        
        if ($test_mode) {
            return $this->simulate_reports($filters);
        }

        // Configurar headers
        $headers = array(
            'Authorization' => 'Bearer ' . ($this->settings['seniat']['api_token'] ?? ''),
            'User-Agent' => 'WCVS/1.0'
        );

        // Construir URL con filtros
        $url = $this->api_base_url . '/api/reportes';
        if (!empty($filters)) {
            $url .= '?' . http_build_query($filters);
        }

        // Realizar petición HTTP
        $response = wp_remote_get($url, array(
            'headers' => $headers,
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 200) {
            return json_decode($response_body, true);
        }

        return false;
    }

    /**
     * Simular reportes (modo de prueba)
     *
     * @param array $filters Filtros
     * @return array
     */
    private function simulate_reports($filters) {
        return array(
            'reportes' => array(
                array(
                    'tipo' => 'resumen_ejecutivo',
                    'periodo' => $filters['periodo'] ?? date('Y-m'),
                    'total_facturas' => wp_rand(50, 200),
                    'total_ventas' => wp_rand(10000, 50000),
                    'total_iva' => wp_rand(1600, 8000)
                ),
                array(
                    'tipo' => 'libro_ventas',
                    'periodo' => $filters['periodo'] ?? date('Y-m'),
                    'total_facturas' => wp_rand(50, 200),
                    'total_ventas' => wp_rand(10000, 50000),
                    'total_iva' => wp_rand(1600, 8000)
                )
            )
        );
    }

    /**
     * Configurar integración con SENIAT
     *
     * @param array $config Configuración
     * @return bool
     */
    public function configure_seniat($config) {
        $settings = WCVS_Core::get_instance()->get_settings();
        $electronic_billing_settings = $settings->get('electronic_billing', array());
        $electronic_billing_settings['seniat'] = $config;
        $settings->set('electronic_billing', $electronic_billing_settings);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, 'Configuración de SENIAT actualizada');

        return true;
    }

    /**
     * Obtener configuración de SENIAT
     *
     * @return array
     */
    public function get_seniat_config() {
        return $this->settings['seniat'] ?? array();
    }

    /**
     * Validar configuración de SENIAT
     *
     * @param array $config Configuración
     * @return array
     */
    public function validate_seniat_config($config) {
        $errors = array();
        
        if (empty($config['api_token']) && !($config['test_mode'] ?? false)) {
            $errors[] = 'El token de API es obligatorio para el modo de producción';
        }
        
        if (empty($config['company_rif'])) {
            $errors[] = 'El RIF de la empresa es obligatorio';
        }
        
        if (!empty($config['company_rif']) && !$this->validate_rif($config['company_rif'])) {
            $errors[] = 'El formato del RIF de la empresa no es válido';
        }
        
        return $errors;
    }

    /**
     * Probar conexión con SENIAT
     *
     * @return array
     */
    public function test_seniat_connection() {
        $test_mode = $this->settings['seniat']['test_mode'] ?? true;
        
        if ($test_mode) {
            return array(
                'success' => true,
                'message' => 'Conexión de prueba exitosa',
                'mode' => 'test'
            );
        }

        // Configurar headers
        $headers = array(
            'Authorization' => 'Bearer ' . ($this->settings['seniat']['api_token'] ?? ''),
            'User-Agent' => 'WCVS/1.0'
        );

        // Realizar petición de prueba
        $response = wp_remote_get($this->api_base_url . '/api/health', array(
            'headers' => $headers,
            'timeout' => 10
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Error de conexión: ' . $response->get_error_message(),
                'mode' => 'production'
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code === 200) {
            return array(
                'success' => true,
                'message' => 'Conexión exitosa con SENIAT',
                'mode' => 'production'
            );
        } else {
            return array(
                'success' => false,
                'message' => "Error HTTP {$response_code}",
                'mode' => 'production'
            );
        }
    }

    /**
     * Obtener estadísticas de integración
     *
     * @return array
     */
    public function get_integration_stats() {
        global $wpdb;
        
        $total_sent = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wcvs_seniat_status'
            AND meta_value = 'sent'
        ");

        $total_processed = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wcvs_seniat_status'
            AND meta_value = 'processed'
        ");

        $total_errors = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wcvs_seniat_status'
            AND meta_value = 'error'
        ");

        return array(
            'total_sent' => $total_sent ?: 0,
            'total_processed' => $total_processed ?: 0,
            'total_errors' => $total_errors ?: 0,
            'success_rate' => $total_sent > 0 ? round(($total_processed / $total_sent) * 100, 2) : 0
        );
    }
}
