<?php
/**
 * BCV API Manager - Gestor de APIs avanzado
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestor de APIs para el sistema BCV Integration.
 * Proporciona endpoints REST, webhooks y integración con servicios externos.
 */
class Woocommerce_Venezuela_Suite_BCV_API_Manager {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración de APIs */
	private $config = array();

	/** @var array Endpoints registrados */
	private $endpoints = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->init_hooks();
	}

	/**
	 * Singleton.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Carga configuración de APIs.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'api_enabled' => get_option( 'wvs_bcv_api_enabled', true ),
			'api_version' => get_option( 'wvs_bcv_api_version', 'v1' ),
			'rate_limit_enabled' => get_option( 'wvs_bcv_rate_limit_enabled', true ),
			'rate_limit_requests' => get_option( 'wvs_bcv_rate_limit_requests', 100 ), // por hora
			'webhook_enabled' => get_option( 'wvs_bcv_webhook_enabled', false ),
			'webhook_url' => get_option( 'wvs_bcv_webhook_url', '' ),
			'api_key_required' => get_option( 'wvs_bcv_api_key_required', false ),
		);
	}

	/**
	 * Inicializa hooks del API Manager.
	 *
	 * @return void
	 */
	private function init_hooks() {
		if ( $this->config['api_enabled'] ) {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
			add_action( 'wvs_bcv_rate_updated', array( $this, 'trigger_webhook' ), 10, 1 );
		}
	}

	/**
	 * Registra todas las rutas de la API.
	 *
	 * @return void
	 */
	public function register_routes() {
		$namespace = 'wvs/' . $this->config['api_version'];

		// Endpoint principal para obtener tasa actual
		register_rest_route( $namespace, '/bcv/rate', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'check_permissions' ),
			'callback' => array( $this, 'get_current_rate' ),
			'args' => array(
				'format' => array(
					'type' => 'string',
					'enum' => array( 'json', 'xml', 'csv' ),
					'default' => 'json',
				),
			),
		) );

		// Endpoint para obtener historial de tasas
		register_rest_route( $namespace, '/bcv/history', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'check_permissions' ),
			'callback' => array( $this, 'get_rate_history' ),
			'args' => array(
				'days' => array(
					'type' => 'integer',
					'minimum' => 1,
					'maximum' => 365,
					'default' => 30,
				),
				'format' => array(
					'type' => 'string',
					'enum' => array( 'json', 'xml', 'csv' ),
					'default' => 'json',
				),
			),
		) );

		// Endpoint para obtener estadísticas
		register_rest_route( $namespace, '/bcv/statistics', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'check_permissions' ),
			'callback' => array( $this, 'get_statistics' ),
		) );

		// Endpoint para obtener predicciones
		register_rest_route( $namespace, '/bcv/predictions', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'check_permissions' ),
			'callback' => array( $this, 'get_predictions' ),
			'args' => array(
				'days' => array(
					'type' => 'integer',
					'minimum' => 1,
					'maximum' => 30,
					'default' => 7,
				),
			),
		) );

		// Endpoint para obtener alertas
		register_rest_route( $namespace, '/bcv/alerts', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'check_permissions' ),
			'callback' => array( $this, 'get_alerts' ),
			'args' => array(
				'limit' => array(
					'type' => 'integer',
					'minimum' => 1,
					'maximum' => 100,
					'default' => 50,
				),
			),
		) );

		// Endpoint para obtener estado del sistema
		register_rest_route( $namespace, '/bcv/status', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'check_permissions' ),
			'callback' => array( $this, 'get_system_status' ),
		) );

		// Endpoint para configurar webhook (solo admin)
		register_rest_route( $namespace, '/bcv/webhook', array(
			'methods' => 'POST',
			'permission_callback' => array( $this, 'check_admin_permissions' ),
			'callback' => array( $this, 'configure_webhook' ),
			'args' => array(
				'url' => array(
					'type' => 'string',
					'format' => 'uri',
					'required' => true,
				),
				'enabled' => array(
					'type' => 'boolean',
					'default' => true,
				),
			),
		) );

		// Endpoint para probar webhook
		register_rest_route( $namespace, '/bcv/webhook/test', array(
			'methods' => 'POST',
			'permission_callback' => array( $this, 'check_admin_permissions' ),
			'callback' => array( $this, 'test_webhook' ),
		) );
	}

	/**
	 * Verifica permisos para endpoints públicos.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True si tiene permisos.
	 */
	public function check_permissions( $request ) {
		// Verificar rate limiting
		if ( $this->config['rate_limit_enabled'] && ! $this->check_rate_limit( $request ) ) {
			return new WP_Error( 'rate_limit_exceeded', 'Rate limit exceeded', array( 'status' => 429 ) );
		}

		// Verificar API key si está habilitado
		if ( $this->config['api_key_required'] ) {
			$api_key = $request->get_header( 'X-API-Key' );
			if ( ! $this->validate_api_key( $api_key ) ) {
				return new WP_Error( 'invalid_api_key', 'Invalid API key', array( 'status' => 401 ) );
			}
		}

		return true;
	}

	/**
	 * Verifica permisos de administrador.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True si tiene permisos.
	 */
	public function check_admin_permissions( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'insufficient_permissions', 'Insufficient permissions', array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Verifica rate limiting.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True si está dentro del límite.
	 */
	private function check_rate_limit( $request ) {
		$client_ip = $this->get_client_ip( $request );
		$key = 'wvs_api_rate_limit_' . md5( $client_ip );
		
		$requests = get_transient( $key );
		if ( false === $requests ) {
			$requests = 0;
		}

		if ( $requests >= $this->config['rate_limit_requests'] ) {
			return false;
		}

		set_transient( $key, $requests + 1, HOUR_IN_SECONDS );
		return true;
	}

	/**
	 * Obtiene la IP del cliente.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return string IP del cliente.
	 */
	private function get_client_ip( $request ) {
		$ip = $request->get_header( 'X-Forwarded-For' );
		if ( ! $ip ) {
			$ip = $request->get_header( 'X-Real-IP' );
		}
		if ( ! $ip ) {
			$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		}

		return $ip;
	}

	/**
	 * Valida API key.
	 *
	 * @param string $api_key API key a validar.
	 * @return bool True si es válida.
	 */
	private function validate_api_key( $api_key ) {
		$valid_keys = get_option( 'wvs_bcv_api_keys', array() );
		return in_array( $api_key, $valid_keys, true );
	}

	/**
	 * Endpoint para obtener tasa actual.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_current_rate( $request ) {
		$core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$rate = $core->get_current_rate();
		$format = $request->get_param( 'format' );

		$data = array(
			'rate' => $rate,
			'currency' => 'VES',
			'timestamp' => current_time( 'mysql' ),
			'source' => 'BCV',
		);

		return $this->format_response( $data, $format );
	}

	/**
	 * Endpoint para obtener historial de tasas.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_rate_history( $request ) {
		$days = $request->get_param( 'days' );
		$format = $request->get_param( 'format' );
		
		$tracker_wrapper = Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper::get_instance();
		$history = $tracker_wrapper->get_rate_history( $days );

		$data = array(
			'history' => $history,
			'days' => $days,
			'total_records' => count( $history ),
		);

		return $this->format_response( $data, $format );
	}

	/**
	 * Endpoint para obtener estadísticas.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_statistics( $request ) {
		$core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$stats = $core->get_statistics();

		return rest_ensure_response( $stats );
	}

	/**
	 * Endpoint para obtener predicciones.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_predictions( $request ) {
		$days = $request->get_param( 'days' );
		$core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$predictions = $core->get_predictions( $days );

		return rest_ensure_response( $predictions );
	}

	/**
	 * Endpoint para obtener alertas.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_alerts( $request ) {
		$limit = $request->get_param( 'limit' );
		$core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$alerts = $core->get_alerts( $limit );

		return rest_ensure_response( $alerts );
	}

	/**
	 * Endpoint para obtener estado del sistema.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_system_status( $request ) {
		$core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$status = $core->get_health_status();

		return rest_ensure_response( $status );
	}

	/**
	 * Endpoint para configurar webhook.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function configure_webhook( $request ) {
		$url = $request->get_param( 'url' );
		$enabled = $request->get_param( 'enabled' );

		update_option( 'wvs_bcv_webhook_url', $url );
		update_option( 'wvs_bcv_webhook_enabled', $enabled );

		$this->config['webhook_url'] = $url;
		$this->config['webhook_enabled'] = $enabled;

		return rest_ensure_response( array(
			'success' => true,
			'message' => 'Webhook configured successfully',
			'webhook_url' => $url,
			'enabled' => $enabled,
		) );
	}

	/**
	 * Endpoint para probar webhook.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function test_webhook( $request ) {
		$result = $this->send_webhook( array(
			'event' => 'test',
			'message' => 'This is a test webhook from WooCommerce Venezuela Suite',
			'timestamp' => current_time( 'mysql' ),
		) );

		return rest_ensure_response( array(
			'success' => $result,
			'message' => $result ? 'Webhook test successful' : 'Webhook test failed',
		) );
	}

	/**
	 * Formatea la respuesta según el formato solicitado.
	 *
	 * @param array $data Datos a formatear.
	 * @param string $format Formato de respuesta.
	 * @return WP_REST_Response Response object.
	 */
	private function format_response( $data, $format ) {
		switch ( $format ) {
			case 'xml':
				return $this->format_xml_response( $data );
			case 'csv':
				return $this->format_csv_response( $data );
			default:
				return rest_ensure_response( $data );
		}
	}

	/**
	 * Formatea respuesta en XML.
	 *
	 * @param array $data Datos a formatear.
	 * @return WP_REST_Response Response object.
	 */
	private function format_xml_response( $data ) {
		$xml = new SimpleXMLElement( '<response/>' );
		$this->array_to_xml( $data, $xml );
		
		$response = new WP_REST_Response( $xml->asXML() );
		$response->header( 'Content-Type', 'application/xml' );
		
		return $response;
	}

	/**
	 * Convierte array a XML.
	 *
	 * @param array $data Datos a convertir.
	 * @param SimpleXMLElement $xml Objeto XML.
	 * @return void
	 */
	private function array_to_xml( $data, &$xml ) {
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$subnode = $xml->addChild( $key );
				$this->array_to_xml( $value, $subnode );
			} else {
				$xml->addChild( $key, htmlspecialchars( $value ) );
			}
		}
	}

	/**
	 * Formatea respuesta en CSV.
	 *
	 * @param array $data Datos a formatear.
	 * @return WP_REST_Response Response object.
	 */
	private function format_csv_response( $data ) {
		$csv = '';
		
		if ( isset( $data['history'] ) && is_array( $data['history'] ) ) {
			// CSV para historial
			$csv .= "Date,Rate\n";
			foreach ( $data['history'] as $record ) {
				$csv .= $record['date'] . ',' . $record['rate'] . "\n";
			}
		} else {
			// CSV simple para tasa actual
			$csv .= "Field,Value\n";
			foreach ( $data as $key => $value ) {
				$csv .= $key . ',' . $value . "\n";
			}
		}

		$response = new WP_REST_Response( $csv );
		$response->header( 'Content-Type', 'text/csv' );
		
		return $response;
	}

	/**
	 * Dispara webhook cuando se actualiza la tasa.
	 *
	 * @param float $rate Nueva tasa.
	 * @return void
	 */
	public function trigger_webhook( $rate ) {
		if ( ! $this->config['webhook_enabled'] || empty( $this->config['webhook_url'] ) ) {
			return;
		}

		$payload = array(
			'event' => 'rate_updated',
			'rate' => $rate,
			'currency' => 'VES',
			'timestamp' => current_time( 'mysql' ),
			'source' => 'BCV',
		);

		$this->send_webhook( $payload );
	}

	/**
	 * Envía webhook.
	 *
	 * @param array $payload Datos a enviar.
	 * @return bool True si se envió correctamente.
	 */
	private function send_webhook( $payload ) {
		$args = array(
			'method' => 'POST',
			'timeout' => 30,
			'headers' => array(
				'Content-Type' => 'application/json',
				'User-Agent' => 'WooCommerce Venezuela Suite/1.0',
			),
			'body' => wp_json_encode( $payload ),
		);

		$response = wp_remote_post( $this->config['webhook_url'], $args );

		if ( is_wp_error( $response ) ) {
			error_log( 'WVS API Manager: Webhook error - ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code < 200 || $status_code >= 300 ) {
			error_log( 'WVS API Manager: Webhook failed with status ' . $status_code );
			return false;
		}

		return true;
	}

	/**
	 * Obtiene información de la API.
	 *
	 * @return array Información de la API.
	 */
	public function get_api_info() {
		return array(
			'version' => $this->config['api_version'],
			'endpoints' => $this->get_endpoints_info(),
			'rate_limit' => array(
				'enabled' => $this->config['rate_limit_enabled'],
				'requests_per_hour' => $this->config['rate_limit_requests'],
			),
			'webhook' => array(
				'enabled' => $this->config['webhook_enabled'],
				'url' => $this->config['webhook_url'],
			),
			'api_key_required' => $this->config['api_key_required'],
		);
	}

	/**
	 * Obtiene información de endpoints.
	 *
	 * @return array Información de endpoints.
	 */
	private function get_endpoints_info() {
		$namespace = 'wvs/' . $this->config['api_version'];
		
		return array(
			'rate' => home_url( '/wp-json/' . $namespace . '/bcv/rate' ),
			'history' => home_url( '/wp-json/' . $namespace . '/bcv/history' ),
			'statistics' => home_url( '/wp-json/' . $namespace . '/bcv/statistics' ),
			'predictions' => home_url( '/wp-json/' . $namespace . '/bcv/predictions' ),
			'alerts' => home_url( '/wp-json/' . $namespace . '/bcv/alerts' ),
			'status' => home_url( '/wp-json/' . $namespace . '/bcv/status' ),
		);
	}

	/**
	 * Actualiza configuración de la API.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'api_enabled',
			'api_version',
			'rate_limit_enabled',
			'rate_limit_requests',
			'webhook_enabled',
			'webhook_url',
			'api_key_required',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_bcv_' . $key, $value );
				$this->config[ $key ] = $value;
			}
		}

		return true;
	}

	/**
	 * Obtiene configuración actual.
	 *
	 * @return array Configuración actual.
	 */
	public function get_config() {
		return $this->config;
	}
}
