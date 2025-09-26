<?php
/**
 * Venezuelan Shipping Methods
 * MRW, Zoom Envíos, Menssajero, and local delivery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Venezuelan_Shipping {
	
	private static $instance = null;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->init_hooks();
	}
	
	private function init_hooks() {
		add_action( 'woocommerce_shipping_init', array( $this, 'init_shipping_methods' ) );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_methods' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_shipping' ) );
	}
	
	/**
	 * Initialize shipping methods
	 */
	public function init_shipping_methods() {
		// All shipping methods are defined in separate files
		// This prevents class conflicts
	}
	
	/**
	 * Add shipping methods to WooCommerce
	 */
	public function add_shipping_methods( $methods ) {
		// Only add methods if their classes exist
		if ( class_exists( 'WVP_MRW_Shipping' ) ) {
		$methods['wvp_mrw'] = 'WVP_MRW_Shipping';
		}
		if ( class_exists( 'WVP_Zoom_Shipping' ) ) {
		$methods['wvp_zoom'] = 'WVP_Zoom_Shipping';
		}
		if ( class_exists( 'WVP_Menssajero_Shipping' ) ) {
		$methods['wvp_menssajero'] = 'WVP_Menssajero_Shipping';
		}
		if ( class_exists( 'WVP_Local_Delivery_Shipping' ) ) {
			$methods['wvp_local_delivery'] = 'WVP_Local_Delivery_Shipping';
		}
		return $methods;
	}
	
	/**
	 * Validate shipping selection
	 */
	public function validate_shipping() {
		// Add any Venezuelan-specific shipping validation here
	}
	
	/**
	 * Calculate shipping cost for a package
	 */
	public function calculate_shipping( $package = array() ) {
		$destination = $package['destination'];
		$weight = $package['contents_weight'];
		$cost = $package['contents_cost'];
		
		// Base shipping cost
		$shipping_cost = 5.00; // Base cost in USD
		
		// Add weight-based cost
		if ( $weight > 0 ) {
			$shipping_cost += ( $weight * 0.5 ); // $0.50 per kg
		}
		
		// Add distance-based cost based on state
		$state_multiplier = $this->get_state_multiplier( $destination['state'] );
		$shipping_cost *= $state_multiplier;
		
		// Free shipping for orders over $50
		if ( $cost >= 50 ) {
			$shipping_cost = 0;
		}
		
		return round( $shipping_cost, 2 );
	}
	
	/**
	 * Get Venezuelan states
	 */
	public function get_states() {
		return array(
			'DC' => 'Distrito Capital',
			'AM' => 'Amazonas',
			'AN' => 'Anzoátegui',
			'AP' => 'Apure',
			'AR' => 'Aragua',
			'BA' => 'Barinas',
			'BO' => 'Bolívar',
			'CA' => 'Carabobo',
			'CO' => 'Cojedes',
			'DA' => 'Delta Amacuro',
			'FA' => 'Falcón',
			'GU' => 'Guárico',
			'LA' => 'Lara',
			'ME' => 'Mérida',
			'MI' => 'Miranda',
			'MO' => 'Monagas',
			'NE' => 'Nueva Esparta',
			'PO' => 'Portuguesa',
			'SU' => 'Sucre',
			'TA' => 'Táchira',
			'TR' => 'Trujillo',
			'VA' => 'Vargas',
			'YA' => 'Yaracuy',
			'ZU' => 'Zulia'
		);
	}
	
	/**
	 * Get state multiplier for shipping cost
	 */
	private function get_state_multiplier( $state ) {
		$multipliers = array(
			'DC' => 1.0,  // Caracas - base rate
			'MI' => 1.0,  // Miranda - same as Caracas
			'VA' => 1.0,  // Vargas - same as Caracas
			'AR' => 1.2,  // Aragua - nearby
			'CA' => 1.2,  // Carabobo - nearby
			'CO' => 1.3,  // Cojedes - nearby
			'GU' => 1.3,  // Guárico - nearby
			'LA' => 1.4,  // Lara - medium distance
			'PO' => 1.4,  // Portuguesa - medium distance
			'YA' => 1.4,  // Yaracuy - medium distance
			'FA' => 1.5,  // Falcón - medium distance
			'SU' => 1.5,  // Sucre - medium distance
			'AN' => 1.6,  // Anzoátegui - far
			'MO' => 1.6,  // Monagas - far
			'NE' => 1.7,  // Nueva Esparta - island
			'BA' => 1.8,  // Barinas - far
			'ME' => 1.8,  // Mérida - far
			'TA' => 1.8,  // Táchira - far
			'TR' => 1.8,  // Trujillo - far
			'ZU' => 1.9,  // Zulia - very far
			'BO' => 2.0,  // Bolívar - very far
			'AP' => 2.0,  // Apure - very far
			'DA' => 2.0,  // Delta Amacuro - very far
			'AM' => 2.5   // Amazonas - extremely far
		);
		
		return isset( $multipliers[ $state ] ) ? $multipliers[ $state ] : 2.0;
	}
	
	/**
	 * Get shipping methods available for a state
	 */
	public function get_available_methods( $state ) {
		$methods = array();
		
		// MRW is available in most states
		if ( $state !== 'AM' && $state !== 'DA' ) {
			$methods[] = 'mrw';
		}
		
		// Zoom is available in major cities
		$zoom_states = array( 'DC', 'MI', 'VA', 'AR', 'CA', 'LA', 'ZU', 'AN', 'SU' );
		if ( in_array( $state, $zoom_states ) ) {
			$methods[] = 'zoom';
		}
		
		// Menssajero is available in most states
		if ( $state !== 'AM' ) {
			$methods[] = 'menssajero';
		}
		
		// Local delivery is available in major cities
		$local_states = array( 'DC', 'MI', 'VA', 'AR', 'CA', 'LA', 'ZU', 'AN', 'SU', 'FA' );
		if ( in_array( $state, $local_states ) ) {
			$methods[] = 'local_delivery';
		}
		
		return $methods;
	}
	
	/**
	 * Get estimated delivery time for a method and state
	 */
	public function get_delivery_time( $method, $state ) {
		$times = array(
			'mrw' => array(
				'DC' => '1-2 días',
				'MI' => '1-2 días',
				'VA' => '1-2 días',
				'AR' => '2-3 días',
				'CA' => '2-3 días',
				'LA' => '3-4 días',
				'ZU' => '4-5 días',
				'default' => '5-7 días'
			),
			'zoom' => array(
				'DC' => '1 día',
				'MI' => '1 día',
				'VA' => '1 día',
				'AR' => '1-2 días',
				'CA' => '1-2 días',
				'LA' => '2-3 días',
				'ZU' => '3-4 días',
				'default' => '4-6 días'
			),
			'menssajero' => array(
				'DC' => '1-2 días',
				'MI' => '1-2 días',
				'VA' => '1-2 días',
				'AR' => '2-3 días',
				'CA' => '2-3 días',
				'LA' => '3-4 días',
				'ZU' => '4-5 días',
				'default' => '5-7 días'
			),
			'local_delivery' => array(
				'DC' => 'Mismo día',
				'MI' => '1 día',
				'VA' => '1 día',
				'AR' => '1-2 días',
				'CA' => '1-2 días',
				'LA' => '2-3 días',
				'ZU' => '2-3 días',
				'default' => '3-5 días'
			)
		);
		
		if ( isset( $times[ $method ][ $state ] ) ) {
			return $times[ $method ][ $state ];
		}
		
		return $times[ $method ]['default'];
	}
	
	/**
	 * Get shipping cost for a specific method and package
	 */
	public function get_method_cost( $method, $package ) {
		$base_costs = array(
			'mrw' => 3.00,
			'zoom' => 2.50,
			'menssajero' => 4.00,
			'local_delivery' => 2.00
		);
		
		$base_cost = isset( $base_costs[ $method ] ) ? $base_costs[ $method ] : 3.00;
		$state_multiplier = $this->get_state_multiplier( $package['destination']['state'] );
		
		return round( $base_cost * $state_multiplier, 2 );
	}
}
