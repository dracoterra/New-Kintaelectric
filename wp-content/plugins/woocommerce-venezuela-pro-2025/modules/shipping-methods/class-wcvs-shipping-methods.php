<?php

/**
 * WooCommerce Venezuela Suite 2025 - Shipping Methods Module
 *
 * Módulo de métodos de envío nacionales para Venezuela
 * incluyendo MRW, Zoom, Tealca y delivery local.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipping Methods Module class
 */
class WCVS_Shipping_Methods {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_woocommerce' ) );
		add_action( 'wp_ajax_wcvs_calculate_shipping', array( $this, 'ajax_calculate_shipping' ) );
		add_action( 'wp_ajax_nopriv_wcvs_calculate_shipping', array( $this, 'ajax_calculate_shipping' ) );
	}

	/**
	 * Initialize module
	 */
	public function init() {
		// Initialize module functionality
		$this->init_shipping_methods();
	}

	/**
	 * Initialize WooCommerce integration
	 */
	public function init_woocommerce() {
		// Register shipping methods
		add_filter( 'woocommerce_shipping_methods', array( $this, 'register_shipping_methods' ) );
		
		// Add shipping zones
		add_action( 'woocommerce_shipping_init', array( $this, 'init_shipping_zones' ) );
	}

	/**
	 * Initialize shipping methods
	 */
	private function init_shipping_methods() {
		// Load shipping method classes
		require_once WCVS_PLUGIN_DIR . 'modules/shipping-methods/methods/class-wcvs-shipping-mrw.php';
		require_once WCVS_PLUGIN_DIR . 'modules/shipping-methods/methods/class-wcvs-shipping-zoom.php';
		require_once WCVS_PLUGIN_DIR . 'modules/shipping-methods/methods/class-wcvs-shipping-tealca.php';
		require_once WCVS_PLUGIN_DIR . 'modules/shipping-methods/methods/class-wcvs-shipping-local-delivery.php';
		require_once WCVS_PLUGIN_DIR . 'modules/shipping-methods/methods/class-wcvs-shipping-pickup.php';
	}

	/**
	 * Register shipping methods
	 *
	 * @param array $methods
	 * @return array
	 */
	public function register_shipping_methods( $methods ) {
		$methods['wcvs_mrw'] = 'WCVS_Shipping_MRW';
		$methods['wcvs_zoom'] = 'WCVS_Shipping_Zoom';
		$methods['wcvs_tealca'] = 'WCVS_Shipping_Tealca';
		$methods['wcvs_local_delivery'] = 'WCVS_Shipping_Local_Delivery';
		$methods['wcvs_pickup'] = 'WCVS_Shipping_Pickup';
		
		return $methods;
	}

	/**
	 * Initialize shipping zones
	 */
	public function init_shipping_zones() {
		// Create Venezuelan shipping zones if they don't exist
		$this->create_venezuelan_shipping_zones();
	}

	/**
	 * Create Venezuelan shipping zones
	 */
	private function create_venezuelan_shipping_zones() {
		// Check if zones already exist
		$existing_zones = WC_Shipping_Zones::get_zones();
		$venezuela_zone_exists = false;
		
		foreach ( $existing_zones as $zone ) {
			if ( strpos( $zone['zone_name'], 'Venezuela' ) !== false ) {
				$venezuela_zone_exists = true;
				break;
			}
		}
		
		if ( ! $venezuela_zone_exists ) {
			// Create main Venezuela zone
			$venezuela_zone = new WC_Shipping_Zone();
			$venezuela_zone->set_zone_name( 'Venezuela - Nacional' );
			$venezuela_zone->set_zone_order( 1 );
			$venezuela_zone->save();
			
			// Add Venezuela to the zone
			$venezuela_zone->add_location( 'VE', 'country' );
			
			// Add shipping methods to the zone
			$this->add_shipping_methods_to_zone( $venezuela_zone );
		}
	}

	/**
	 * Add shipping methods to zone
	 *
	 * @param WC_Shipping_Zone $zone
	 */
	private function add_shipping_methods_to_zone( $zone ) {
		// Add MRW
		$mrw_instance_id = $zone->add_shipping_method( 'wcvs_mrw' );
		$mrw_settings = array(
			'title' => 'MRW',
			'enabled' => 'yes',
			'cost' => '0',
			'free_shipping_threshold' => '1000000'
		);
		$zone->save();
		
		// Add Zoom
		$zoom_instance_id = $zone->add_shipping_method( 'wcvs_zoom' );
		$zoom_settings = array(
			'title' => 'Zoom',
			'enabled' => 'yes',
			'cost' => '0',
			'free_shipping_threshold' => '1000000'
		);
		$zone->save();
		
		// Add Tealca
		$tealca_instance_id = $zone->add_shipping_method( 'wcvs_tealca' );
		$tealca_settings = array(
			'title' => 'Tealca',
			'enabled' => 'yes',
			'cost' => '0',
			'free_shipping_threshold' => '1000000'
		);
		$zone->save();
		
		// Add Local Delivery
		$local_delivery_instance_id = $zone->add_shipping_method( 'wcvs_local_delivery' );
		$local_delivery_settings = array(
			'title' => 'Delivery Local',
			'enabled' => 'yes',
			'cost' => '50000',
			'free_shipping_threshold' => '500000'
		);
		$zone->save();
		
		// Add Pickup
		$pickup_instance_id = $zone->add_shipping_method( 'wcvs_pickup' );
		$pickup_settings = array(
			'title' => 'Recogida en Tienda',
			'enabled' => 'yes',
			'cost' => '0'
		);
		$zone->save();
	}

	/**
	 * AJAX handler for shipping calculation
	 */
	public function ajax_calculate_shipping() {
		check_ajax_referer( 'wcvs_calculate_shipping', 'nonce' );

		$shipping_method = sanitize_text_field( $_POST['shipping_method'] );
		$destination = sanitize_text_field( $_POST['destination'] );
		$weight = floatval( $_POST['weight'] );
		$dimensions = array(
			'length' => floatval( $_POST['length'] ),
			'width' => floatval( $_POST['width'] ),
			'height' => floatval( $_POST['height'] )
		);

		$result = $this->calculate_shipping_cost( $shipping_method, $destination, $weight, $dimensions );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Calculate shipping cost
	 *
	 * @param string $shipping_method
	 * @param string $destination
	 * @param float  $weight
	 * @param array  $dimensions
	 * @return array
	 */
	private function calculate_shipping_cost( $shipping_method, $destination, $weight, $dimensions ) {
		switch ( $shipping_method ) {
			case 'wcvs_mrw':
				return $this->calculate_mrw_cost( $destination, $weight, $dimensions );
			case 'wcvs_zoom':
				return $this->calculate_zoom_cost( $destination, $weight, $dimensions );
			case 'wcvs_tealca':
				return $this->calculate_tealca_cost( $destination, $weight, $dimensions );
			case 'wcvs_local_delivery':
				return $this->calculate_local_delivery_cost( $destination, $weight );
			case 'wcvs_pickup':
				return $this->calculate_pickup_cost();
			default:
				return array(
					'success' => false,
					'message' => __( 'Método de envío no válido', 'woocommerce-venezuela-pro-2025' )
				);
		}
	}

	/**
	 * Calculate MRW cost
	 *
	 * @param string $destination
	 * @param float  $weight
	 * @param array  $dimensions
	 * @return array
	 */
	private function calculate_mrw_cost( $destination, $weight, $dimensions ) {
		// MRW pricing structure (simplified)
		$base_cost = 150000; // Bs. 150,000 base cost
		$weight_cost = $weight * 5000; // Bs. 5,000 per kg
		$volume_cost = ($dimensions['length'] * $dimensions['width'] * $dimensions['height']) / 1000000 * 10000; // Bs. 10,000 per m³
		
		// Destination multiplier
		$destination_multiplier = $this->get_destination_multiplier( $destination );
		
		$total_cost = ($base_cost + $weight_cost + $volume_cost) * $destination_multiplier;
		
		return array(
			'success' => true,
			'cost' => $total_cost,
			'message' => sprintf( __( 'Costo MRW: Bs. %s', 'woocommerce-venezuela-pro-2025' ), number_format( $total_cost, 2, ',', '.' ) )
		);
	}

	/**
	 * Calculate Zoom cost
	 *
	 * @param string $destination
	 * @param float  $weight
	 * @param array  $dimensions
	 * @return array
	 */
	private function calculate_zoom_cost( $destination, $weight, $dimensions ) {
		// Zoom pricing structure (simplified)
		$base_cost = 120000; // Bs. 120,000 base cost
		$weight_cost = $weight * 4500; // Bs. 4,500 per kg
		$volume_cost = ($dimensions['length'] * $dimensions['width'] * $dimensions['height']) / 1000000 * 8000; // Bs. 8,000 per m³
		
		// Destination multiplier
		$destination_multiplier = $this->get_destination_multiplier( $destination );
		
		$total_cost = ($base_cost + $weight_cost + $volume_cost) * $destination_multiplier;
		
		return array(
			'success' => true,
			'cost' => $total_cost,
			'message' => sprintf( __( 'Costo Zoom: Bs. %s', 'woocommerce-venezuela-pro-2025' ), number_format( $total_cost, 2, ',', '.' ) )
		);
	}

	/**
	 * Calculate Tealca cost
	 *
	 * @param string $destination
	 * @param float  $weight
	 * @param array  $dimensions
	 * @return array
	 */
	private function calculate_tealca_cost( $destination, $weight, $dimensions ) {
		// Tealca pricing structure (simplified)
		$base_cost = 100000; // Bs. 100,000 base cost
		$weight_cost = $weight * 4000; // Bs. 4,000 per kg
		$volume_cost = ($dimensions['length'] * $dimensions['width'] * $dimensions['height']) / 1000000 * 7000; // Bs. 7,000 per m³
		
		// Destination multiplier
		$destination_multiplier = $this->get_destination_multiplier( $destination );
		
		$total_cost = ($base_cost + $weight_cost + $volume_cost) * $destination_multiplier;
		
		return array(
			'success' => true,
			'cost' => $total_cost,
			'message' => sprintf( __( 'Costo Tealca: Bs. %s', 'woocommerce-venezuela-pro-2025' ), number_format( $total_cost, 2, ',', '.' ) )
		);
	}

	/**
	 * Calculate Local Delivery cost
	 *
	 * @param string $destination
	 * @param float  $weight
	 * @return array
	 */
	private function calculate_local_delivery_cost( $destination, $weight ) {
		// Local delivery pricing (simplified)
		$base_cost = 50000; // Bs. 50,000 base cost
		$weight_cost = $weight * 2000; // Bs. 2,000 per kg
		
		$total_cost = $base_cost + $weight_cost;
		
		return array(
			'success' => true,
			'cost' => $total_cost,
			'message' => sprintf( __( 'Costo Delivery Local: Bs. %s', 'woocommerce-venezuela-pro-2025' ), number_format( $total_cost, 2, ',', '.' ) )
		);
	}

	/**
	 * Calculate Pickup cost
	 *
	 * @return array
	 */
	private function calculate_pickup_cost() {
		return array(
			'success' => true,
			'cost' => 0,
			'message' => __( 'Recogida en tienda: Sin costo', 'woocommerce-venezuela-pro-2025' )
		);
	}

	/**
	 * Get destination multiplier
	 *
	 * @param string $destination
	 * @return float
	 */
	private function get_destination_multiplier( $destination ) {
		// Venezuelan states and their shipping multipliers
		$multipliers = array(
			'caracas' => 1.0,
			'miranda' => 1.0,
			'vargas' => 1.0,
			'aragua' => 1.2,
			'carabobo' => 1.2,
			'valencia' => 1.2,
			'lara' => 1.5,
			'barquisimeto' => 1.5,
			'zulia' => 1.8,
			'maracaibo' => 1.8,
			'merida' => 2.0,
			'tachira' => 2.0,
			'san_cristobal' => 2.0,
			'bolivar' => 2.2,
			'ciudad_guayana' => 2.2,
			'anzoategui' => 1.5,
			'barcelona' => 1.5,
			'monagas' => 1.8,
			'maturin' => 1.8,
			'sucre' => 2.0,
			'cumana' => 2.0,
			'falcon' => 1.5,
			'coro' => 1.5,
			'yaracuy' => 1.3,
			'san_felipe' => 1.3,
			'portuguesa' => 1.5,
			'acarigua' => 1.5,
			'barinas' => 1.8,
			'cojedes' => 1.5,
			'san_carlos' => 1.5,
			'guarico' => 1.8,
			'san_juan_de_los_morros' => 1.8,
			'trujillo' => 1.8,
			'valera' => 1.8,
			'apure' => 2.0,
			'san_fernando_de_apure' => 2.0,
			'delta_amacuro' => 2.5,
			'tucupita' => 2.5,
			'amazonas' => 3.0,
			'puerto_ayacucho' => 3.0
		);
		
		return isset( $multipliers[ $destination ] ) ? $multipliers[ $destination ] : 2.0;
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		// Add shipping method specific frontend functionality
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout_shipping' ) );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		if ( is_checkout() || is_cart() ) {
			wp_enqueue_script(
				'wcvs-shipping-methods',
				WCVS_PLUGIN_URL . 'modules/shipping-methods/js/shipping-methods.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-shipping-methods', 'wcvs_shipping_methods', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_calculate_shipping' ),
				'strings' => array(
					'calculating' => __( 'Calculando envío...', 'woocommerce-venezuela-pro-2025' ),
					'calculated' => __( 'Envío calculado', 'woocommerce-venezuela-pro-2025' ),
					'error' => __( 'Error al calcular envío', 'woocommerce-venezuela-pro-2025' )
				)
			));
		}
	}

	/**
	 * Validate checkout shipping
	 */
	public function validate_checkout_shipping() {
		$shipping_method = WC()->session->get( 'chosen_shipping_methods' );
		
		if ( ! empty( $shipping_method ) && strpos( $shipping_method[0], 'wcvs_' ) === 0 ) {
			// Validate Venezuelan shipping methods
			$this->validate_venezuelan_shipping_method( $shipping_method[0] );
		}
	}

	/**
	 * Validate Venezuelan shipping method
	 *
	 * @param string $shipping_method
	 */
	private function validate_venezuelan_shipping_method( $shipping_method ) {
		switch ( $shipping_method ) {
			case 'wcvs_mrw':
				$this->validate_mrw_shipping();
				break;
			case 'wcvs_zoom':
				$this->validate_zoom_shipping();
				break;
			case 'wcvs_tealca':
				$this->validate_tealca_shipping();
				break;
			case 'wcvs_local_delivery':
				$this->validate_local_delivery_shipping();
				break;
			case 'wcvs_pickup':
				$this->validate_pickup_shipping();
				break;
		}
	}

	/**
	 * Validate MRW shipping
	 */
	private function validate_mrw_shipping() {
		// Add specific validation for MRW
		if ( ! $this->core->settings->get_setting( 'shipping_methods', 'mrw_enabled' ) ) {
			wc_add_notice( __( 'MRW no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Validate Zoom shipping
	 */
	private function validate_zoom_shipping() {
		// Add specific validation for Zoom
		if ( ! $this->core->settings->get_setting( 'shipping_methods', 'zoom_enabled' ) ) {
			wc_add_notice( __( 'Zoom no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Validate Tealca shipping
	 */
	private function validate_tealca_shipping() {
		// Add specific validation for Tealca
		if ( ! $this->core->settings->get_setting( 'shipping_methods', 'tealca_enabled' ) ) {
			wc_add_notice( __( 'Tealca no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Validate Local Delivery shipping
	 */
	private function validate_local_delivery_shipping() {
		// Add specific validation for Local Delivery
		if ( ! $this->core->settings->get_setting( 'shipping_methods', 'local_delivery_enabled' ) ) {
			wc_add_notice( __( 'Delivery Local no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Validate Pickup shipping
	 */
	private function validate_pickup_shipping() {
		// Add specific validation for Pickup
		if ( ! $this->core->settings->get_setting( 'shipping_methods', 'pickup_enabled' ) ) {
			wc_add_notice( __( 'Recogida en Tienda no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}
}
