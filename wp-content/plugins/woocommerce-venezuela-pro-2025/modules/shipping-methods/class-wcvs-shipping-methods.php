<?php

/**
 * WooCommerce Venezuela Suite 2025 - Shipping Methods Module
 *
 * Módulo de métodos de envío nacionales venezolanos
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipping Methods Module
 */
class WCVS_Shipping_Methods {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Available shipping methods
	 *
	 * @var array
	 */
	private $available_methods = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$all_settings = $this->core->settings->get_all_settings();
		$this->settings = isset( $all_settings['shipping_methods'] ) ? $all_settings['shipping_methods'] : array();
		$this->init_hooks();
		$this->load_shipping_methods();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Frontend hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_shipping_fields' ) );

		// Admin hooks
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'woocommerce_shipping_init', array( $this, 'init_shipping_methods' ) );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_methods' ) );

		// AJAX hooks
		add_action( 'wp_ajax_wcvs_calculate_shipping', array( $this, 'ajax_calculate_shipping' ) );
		add_action( 'wp_ajax_nopriv_wcvs_calculate_shipping', array( $this, 'ajax_calculate_shipping' ) );
	}

	/**
	 * Load shipping methods
	 */
	private function load_shipping_methods() {
		$method_files = array(
			'mrw' => 'class-wcvs-shipping-mrw.php',
			'zoom' => 'class-wcvs-shipping-zoom.php',
			'tealca' => 'class-wcvs-shipping-tealca.php',
			'local-delivery' => 'class-wcvs-shipping-local-delivery.php',
			'pickup' => 'class-wcvs-shipping-pickup.php'
		);

		foreach ( $method_files as $method_id => $filename ) {
			$file_path = WCVS_PLUGIN_DIR . 'modules/shipping-methods/methods/' . $filename;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
				$this->available_methods[ $method_id ] = $filename;
			}
		}
	}

	/**
	 * Initialize shipping methods
	 */
	public function init_shipping_methods() {
		// MRW Shipping Method
		if ( class_exists( 'WCVS_Shipping_MRW' ) ) {
			// Already loaded
		}

		// Zoom Shipping Method
		if ( class_exists( 'WCVS_Shipping_Zoom' ) ) {
			// Already loaded
		}

		// Tealca Shipping Method
		if ( class_exists( 'WCVS_Shipping_Tealca' ) ) {
			// Already loaded
		}

		// Local Delivery Shipping Method
		if ( class_exists( 'WCVS_Shipping_Local_Delivery' ) ) {
			// Already loaded
		}

		// Pickup Shipping Method
		if ( class_exists( 'WCVS_Shipping_Pickup' ) ) {
			// Already loaded
		}
	}

	/**
	 * Add shipping methods to WooCommerce
	 *
	 * @param array $methods
	 * @return array
	 */
	public function add_shipping_methods( $methods ) {
		// MRW
		if ( class_exists( 'WCVS_Shipping_MRW' ) ) {
			$methods['wcvs_mrw'] = 'WCVS_Shipping_MRW';
		}

		// Zoom
		if ( class_exists( 'WCVS_Shipping_Zoom' ) ) {
			$methods['wcvs_zoom'] = 'WCVS_Shipping_Zoom';
		}

		// Tealca
		if ( class_exists( 'WCVS_Shipping_Tealca' ) ) {
			$methods['wcvs_tealca'] = 'WCVS_Shipping_Tealca';
		}

		// Local Delivery
		if ( class_exists( 'WCVS_Shipping_Local_Delivery' ) ) {
			$methods['wcvs_local_delivery'] = 'WCVS_Shipping_Local_Delivery';
		}

		// Pickup
		if ( class_exists( 'WCVS_Shipping_Pickup' ) ) {
			$methods['wcvs_pickup'] = 'WCVS_Shipping_Pickup';
		}

		return $methods;
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'wcvs-shipping-methods',
			WCVS_PLUGIN_URL . 'modules/shipping-methods/js/shipping-methods.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);

		wp_enqueue_style(
			'wcvs-shipping-methods',
			WCVS_PLUGIN_URL . 'modules/shipping-methods/css/shipping-methods.css',
			array(),
			WCVS_VERSION
		);

		wp_localize_script( 'wcvs-shipping-methods', 'wcvs_shipping_methods', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_shipping_methods_nonce' ),
			'venezuelan_states' => $this->get_venezuelan_states(),
			'validation_messages' => array(
				'state_required' => __( 'El estado es requerido', 'woocommerce-venezuela-pro-2025' ),
				'city_required' => __( 'La ciudad es requerida', 'woocommerce-venezuela-pro-2025' ),
				'address_required' => __( 'La dirección es requerida', 'woocommerce-venezuela-pro-2025' ),
				'invalid_state' => __( 'Estado inválido', 'woocommerce-venezuela-pro-2025' ),
				'invalid_city' => __( 'Ciudad inválida', 'woocommerce-venezuela-pro-2025' )
			)
		));
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'wcvs-shipping-methods-admin',
			WCVS_PLUGIN_URL . 'modules/shipping-methods/js/shipping-methods-admin.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);
	}

	/**
	 * Validate shipping fields
	 */
	public function validate_shipping_fields() {
		$shipping_method = WC()->session->get( 'chosen_shipping_method' );
		
		if ( ! $this->is_venezuelan_shipping_method( $shipping_method ) ) {
			return;
		}

		// Validate state
		if ( empty( $_POST['shipping_state'] ) ) {
			wc_add_notice( __( 'El estado es requerido para envíos venezolanos.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		} elseif ( ! $this->validate_venezuelan_state( $_POST['shipping_state'] ) ) {
			wc_add_notice( __( 'El estado seleccionado no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}

		// Validate city
		if ( empty( $_POST['shipping_city'] ) ) {
			wc_add_notice( __( 'La ciudad es requerida para envíos venezolanos.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}

		// Validate address
		if ( empty( $_POST['shipping_address_1'] ) ) {
			wc_add_notice( __( 'La dirección es requerida para envíos venezolanos.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * AJAX calculate shipping
	 */
	public function ajax_calculate_shipping() {
		check_ajax_referer( 'wcvs_shipping_methods_nonce', 'nonce' );

		$state = sanitize_text_field( $_POST['state'] );
		$city = sanitize_text_field( $_POST['city'] );
		$weight = floatval( $_POST['weight'] );
		$shipping_method = sanitize_text_field( $_POST['shipping_method'] );

		$shipping_cost = $this->calculate_shipping_cost( $state, $city, $weight, $shipping_method );

		wp_send_json_success( array(
			'cost' => $shipping_cost,
			'formatted_cost' => $this->core->bcv_integration->format_currency( $shipping_cost, 'VES' ),
			'estimated_days' => $this->get_estimated_delivery_days( $state, $shipping_method )
		));
	}

	/**
	 * Calculate shipping cost
	 *
	 * @param string $state
	 * @param string $city
	 * @param float $weight
	 * @param string $shipping_method
	 * @return float
	 */
	private function calculate_shipping_cost( $state, $city, $weight, $shipping_method ) {
		$base_cost = 0;
		$weight_cost = 0;
		$distance_cost = 0;

		switch ( $shipping_method ) {
			case 'wcvs_mrw':
				$base_cost = 15; // Base cost in USD
				$weight_cost = $weight * 2; // $2 per kg
				$distance_cost = $this->get_distance_cost( $state, 'mrw' );
				break;
			case 'wcvs_zoom':
				$base_cost = 12; // Base cost in USD
				$weight_cost = $weight * 1.5; // $1.5 per kg
				$distance_cost = $this->get_distance_cost( $state, 'zoom' );
				break;
			case 'wcvs_tealca':
				$base_cost = 10; // Base cost in USD
				$weight_cost = $weight * 1.2; // $1.2 per kg
				$distance_cost = $this->get_distance_cost( $state, 'tealca' );
				break;
			case 'wcvs_local_delivery':
				$base_cost = 5; // Base cost in USD
				$weight_cost = $weight * 0.5; // $0.5 per kg
				$distance_cost = 0; // Local delivery
				break;
			case 'wcvs_pickup':
				$base_cost = 0; // Free pickup
				$weight_cost = 0;
				$distance_cost = 0;
				break;
		}

		$total_cost_usd = $base_cost + $weight_cost + $distance_cost;
		$total_cost_ves = $this->core->bcv_integration->convert_usd_to_ves( $total_cost_usd );

		return $total_cost_ves;
	}

	/**
	 * Get distance cost
	 *
	 * @param string $state
	 * @param string $shipping_method
	 * @return float
	 */
	private function get_distance_cost( $state, $shipping_method ) {
		$distance_costs = array(
			'mrw' => array(
				'Distrito Capital' => 0,
				'Miranda' => 2,
				'Vargas' => 3,
				'Aragua' => 4,
				'Carabobo' => 5,
				'default' => 8
			),
			'zoom' => array(
				'Distrito Capital' => 0,
				'Miranda' => 1.5,
				'Vargas' => 2,
				'Aragua' => 3,
				'Carabobo' => 4,
				'default' => 6
			),
			'tealca' => array(
				'Distrito Capital' => 0,
				'Miranda' => 1,
				'Vargas' => 1.5,
				'Aragua' => 2,
				'Carabobo' => 3,
				'default' => 5
			)
		);

		if ( isset( $distance_costs[ $shipping_method ][ $state ] ) ) {
			return $distance_costs[ $shipping_method ][ $state ];
		}

		return $distance_costs[ $shipping_method ]['default'];
	}

	/**
	 * Get estimated delivery days
	 *
	 * @param string $state
	 * @param string $shipping_method
	 * @return int
	 */
	private function get_estimated_delivery_days( $state, $shipping_method ) {
		$delivery_days = array(
			'mrw' => array(
				'Distrito Capital' => 1,
				'Miranda' => 1,
				'Vargas' => 1,
				'Aragua' => 2,
				'Carabobo' => 2,
				'default' => 3
			),
			'zoom' => array(
				'Distrito Capital' => 1,
				'Miranda' => 1,
				'Vargas' => 1,
				'Aragua' => 2,
				'Carabobo' => 2,
				'default' => 3
			),
			'tealca' => array(
				'Distrito Capital' => 1,
				'Miranda' => 1,
				'Vargas' => 1,
				'Aragua' => 2,
				'Carabobo' => 2,
				'default' => 3
			),
			'wcvs_local_delivery' => array(
				'Distrito Capital' => 1,
				'Miranda' => 1,
				'default' => 2
			),
			'wcvs_pickup' => array(
				'default' => 0
			)
		);

		if ( isset( $delivery_days[ $shipping_method ][ $state ] ) ) {
			return $delivery_days[ $shipping_method ][ $state ];
		}

		return $delivery_days[ $shipping_method ]['default'];
	}

	/**
	 * Get Venezuelan states
	 *
	 * @return array
	 */
	private function get_venezuelan_states() {
		return array(
			'Distrito Capital' => 'Distrito Capital',
			'Amazonas' => 'Amazonas',
			'Anzoátegui' => 'Anzoátegui',
			'Apure' => 'Apure',
			'Aragua' => 'Aragua',
			'Barinas' => 'Barinas',
			'Bolívar' => 'Bolívar',
			'Carabobo' => 'Carabobo',
			'Cojedes' => 'Cojedes',
			'Delta Amacuro' => 'Delta Amacuro',
			'Falcón' => 'Falcón',
			'Guárico' => 'Guárico',
			'Lara' => 'Lara',
			'Mérida' => 'Mérida',
			'Miranda' => 'Miranda',
			'Monagas' => 'Monagas',
			'Nueva Esparta' => 'Nueva Esparta',
			'Portuguesa' => 'Portuguesa',
			'Sucre' => 'Sucre',
			'Táchira' => 'Táchira',
			'Trujillo' => 'Trujillo',
			'Vargas' => 'Vargas',
			'Yaracuy' => 'Yaracuy',
			'Zulia' => 'Zulia'
		);
	}

	/**
	 * Check if shipping method is Venezuelan
	 *
	 * @param string $shipping_method
	 * @return bool
	 */
	private function is_venezuelan_shipping_method( $shipping_method ) {
		$venezuelan_methods = array(
			'wcvs_mrw',
			'wcvs_zoom',
			'wcvs_tealca',
			'wcvs_local_delivery',
			'wcvs_pickup'
		);

		return in_array( $shipping_method, $venezuelan_methods );
	}

	/**
	 * Validate Venezuelan state
	 *
	 * @param string $state
	 * @return bool
	 */
	private function validate_venezuelan_state( $state ) {
		$valid_states = array_keys( $this->get_venezuelan_states() );
		return in_array( $state, $valid_states );
	}

	/**
	 * Get available shipping methods
	 *
	 * @return array
	 */
	public function get_available_methods() {
		return $this->available_methods;
	}

	/**
	 * Get shipping method status
	 *
	 * @return array
	 */
	public function get_shipping_method_status() {
		$status = array();
		
		foreach ( $this->available_methods as $method_id => $filename ) {
			$status[ $method_id ] = array(
				'available' => true,
				'filename' => $filename,
				'class' => 'WCVS_Shipping_' . str_replace( '-', '_', ucwords( $method_id, '-' ) )
			);
		}
		
		return $status;
	}

	/**
	 * Get shipping zones for Venezuela
	 *
	 * @return array
	 */
	public function get_venezuelan_shipping_zones() {
		return array(
			'capital' => array(
				'name' => __( 'Zona Capital', 'woocommerce-venezuela-pro-2025' ),
				'states' => array( 'Distrito Capital', 'Miranda', 'Vargas' ),
				'multiplier' => 1.0
			),
			'central' => array(
				'name' => __( 'Zona Central', 'woocommerce-venezuela-pro-2025' ),
				'states' => array( 'Aragua', 'Carabobo', 'Cojedes' ),
				'multiplier' => 1.2
			),
			'occidental' => array(
				'name' => __( 'Zona Occidental', 'woocommerce-venezuela-pro-2025' ),
				'states' => array( 'Lara', 'Mérida', 'Táchira', 'Trujillo', 'Yaracuy', 'Zulia' ),
				'multiplier' => 1.5
			),
			'oriental' => array(
				'name' => __( 'Zona Oriental', 'woocommerce-venezuela-pro-2025' ),
				'states' => array( 'Anzoátegui', 'Monagas', 'Nueva Esparta', 'Sucre' ),
				'multiplier' => 1.3
			),
			'llanos' => array(
				'name' => __( 'Zona de los Llanos', 'woocommerce-venezuela-pro-2025' ),
				'states' => array( 'Apure', 'Barinas', 'Guárico', 'Portuguesa' ),
				'multiplier' => 1.4
			),
			'sur' => array(
				'name' => __( 'Zona Sur', 'woocommerce-venezuela-pro-2025' ),
				'states' => array( 'Amazonas', 'Bolívar', 'Delta Amacuro' ),
				'multiplier' => 1.8
			),
			'falcón' => array(
				'name' => __( 'Zona Falcón', 'woocommerce-venezuela-pro-2025' ),
				'states' => array( 'Falcón' ),
				'multiplier' => 1.6
			)
		);
	}
}