<?php

/**
 * WooCommerce Venezuela Suite 2025 - MRW Shipping Method
 *
 * Método de envío para MRW Venezuela
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MRW Shipping Method class
 */
class WCVS_Shipping_MRW extends WC_Shipping_Method {

	/**
	 * Constructor
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wcvs_mrw';
		$this->instance_id = absint( $instance_id );
		$this->method_title = __( 'MRW Venezuela', 'woocommerce-venezuela-pro-2025' );
		$this->method_description = __( 'Método de envío MRW para Venezuela', 'woocommerce-venezuela-pro-2025' );
		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal'
		);

		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize MRW shipping method
	 */
	public function init() {
		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title = $this->get_option( 'title' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->cost = $this->get_option( 'cost' );
		$this->free_shipping_threshold = $this->get_option( 'free_shipping_threshold' );
		$this->base_cost = $this->get_option( 'base_cost' );
		$this->weight_cost = $this->get_option( 'weight_cost' );
		$this->volume_cost = $this->get_option( 'volume_cost' );
		$this->tracking_enabled = $this->get_option( 'tracking_enabled' );
		$this->api_key = $this->get_option( 'api_key' );
		$this->api_secret = $this->get_option( 'api_secret' );
	}

	/**
	 * Initialize form fields
	 */
	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title' => array(
				'title' => __( 'Título', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Título que el usuario ve durante el checkout', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'MRW Venezuela', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'enabled' => array(
				'title' => __( 'Activar/Desactivar', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'checkbox',
				'label' => __( 'Activar MRW', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'yes'
			),
			'cost' => array(
				'title' => __( 'Costo Base', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo base del envío en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '150000',
				'desc_tip' => true,
			),
			'free_shipping_threshold' => array(
				'title' => __( 'Umbral de Envío Gratis', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Monto mínimo para envío gratis en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '1000000',
				'desc_tip' => true,
			),
			'base_cost' => array(
				'title' => __( 'Costo Base MRW', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo base oficial de MRW en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '150000',
				'desc_tip' => true,
			),
			'weight_cost' => array(
				'title' => __( 'Costo por Peso', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo adicional por kilogramo en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '5000',
				'desc_tip' => true,
			),
			'volume_cost' => array(
				'title' => __( 'Costo por Volumen', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo adicional por metro cúbico en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '10000',
				'desc_tip' => true,
			),
			'tracking_enabled' => array(
				'title' => __( 'Seguimiento Automático', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'checkbox',
				'label' => __( 'Activar seguimiento automático de MRW', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'no'
			),
			'api_key' => array(
				'title' => __( 'Clave API MRW', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'password',
				'description' => __( 'Clave API para integración con MRW', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'api_secret' => array(
				'title' => __( 'Secreto API MRW', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'password',
				'description' => __( 'Secreto API para integración con MRW', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
		);
	}

	/**
	 * Calculate shipping
	 *
	 * @param array $package
	 */
	public function calculate_shipping( $package = array() ) {
		// Check if MRW is enabled
		if ( $this->enabled !== 'yes' ) {
			return;
		}

		// Check if package is for Venezuela
		if ( $package['destination']['country'] !== 'VE' ) {
			return;
		}

		// Calculate shipping cost
		$cost = $this->calculate_mrw_cost( $package );

		// Check for free shipping threshold
		$cart_total = WC()->cart->get_cart_contents_total();
		if ( $cart_total >= $this->free_shipping_threshold ) {
			$cost = 0;
		}

		// Add shipping rate
		$this->add_rate( array(
			'id' => $this->get_rate_id(),
			'label' => $this->title,
			'cost' => $cost,
			'meta_data' => array(
				'MRW_Tracking' => $this->tracking_enabled === 'yes' ? 'yes' : 'no',
				'MRW_Service' => 'Estándar'
			)
		));
	}

	/**
	 * Calculate MRW cost with enhanced precision
	 *
	 * @param array $package
	 * @return float
	 */
	private function calculate_mrw_cost( $package ) {
		$cost = floatval( $this->base_cost );
		$breakdown = array();
		
		// Add weight cost with tiered pricing
		$weight = $this->get_package_weight( $package );
		$weight_cost = $this->calculate_tiered_weight_cost( $weight );
		$cost += $weight_cost;
		$breakdown['weight'] = $weight_cost;
		
		// Add volume cost with dimensional weight calculation
		$volume = $this->get_package_volume( $package );
		$volume_cost = $this->calculate_volume_cost( $volume );
		$cost += $volume_cost;
		$breakdown['volume'] = $volume_cost;
		
		// Apply destination multiplier with enhanced calculation
		$destination_multiplier = $this->get_destination_multiplier( $package['destination'] );
		$cost *= $destination_multiplier;
		$breakdown['destination_multiplier'] = $destination_multiplier;
		
		// Add distance-based cost
		$distance_cost = $this->calculate_distance_cost( $package );
		$cost += $distance_cost;
		$breakdown['distance'] = $distance_cost;
		
		// Add insurance cost if enabled
		if ( $this->insurance_enabled === 'yes' ) {
			$insurance_cost = $this->calculate_insurance_cost( $package );
			$cost += $insurance_cost;
			$breakdown['insurance'] = $insurance_cost;
		}
		
		// Add packaging cost
		$packaging_cost = $this->calculate_packaging_cost( $package );
		$cost += $packaging_cost;
		$breakdown['packaging'] = $packaging_cost;
		
		// Apply volume discount
		$discount = $this->calculate_volume_discount( $package );
		$cost -= $discount;
		$breakdown['discount'] = $discount;
		
		// Store breakdown for debugging
		$this->cost_breakdown = $breakdown;
		
		return max( $cost, floatval( $this->minimum_cost ) );
	}

	/**
	 * Get package weight
	 *
	 * @param array $package
	 * @return float
	 */
	private function get_package_weight( $package ) {
		$weight = 0;
		
		foreach ( $package['contents'] as $item ) {
			$product = $item['data'];
			$weight += $product->get_weight() * $item['quantity'];
		}
		
		return $weight;
	}

	/**
	 * Get package volume
	 *
	 * @param array $package
	 * @return float
	 */
	private function get_package_volume( $package ) {
		$volume = 0;
		
		foreach ( $package['contents'] as $item ) {
			$product = $item['data'];
			$dimensions = $product->get_dimensions();
			$volume += ($dimensions['length'] * $dimensions['width'] * $dimensions['height']) * $item['quantity'];
		}
		
		return $volume / 1000000; // Convert to cubic meters
	}

	/**
	 * Get destination multiplier
	 *
	 * @param array $destination
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
		
		$state = strtolower( str_replace( ' ', '_', $destination['state'] ) );
		return isset( $multipliers[ $state ] ) ? $multipliers[ $state ] : 2.0;
	}

	/**
	 * Get MRW tracking information
	 *
	 * @param string $tracking_number
	 * @return array
	 */
	public function get_tracking_info( $tracking_number ) {
		if ( $this->tracking_enabled !== 'yes' || empty( $this->api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'Seguimiento MRW no está configurado', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// This would integrate with MRW's actual API
		// For now, return mock data
		return array(
			'success' => true,
			'tracking_number' => $tracking_number,
			'status' => 'En tránsito',
			'location' => 'Centro de Distribución Caracas',
			'estimated_delivery' => date( 'Y-m-d', strtotime( '+3 days' ) ),
			'tracking_history' => array(
				array(
					'date' => date( 'Y-m-d H:i:s' ),
					'status' => 'En tránsito',
					'location' => 'Centro de Distribución Caracas'
				),
				array(
					'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
					'status' => 'Recogido',
					'location' => 'Oficina MRW Caracas'
				)
			)
		);
	}

	/**
	 * Generate MRW shipping label
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	public function generate_shipping_label( $order ) {
		if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {
			return array(
				'success' => false,
				'message' => __( 'API de MRW no está configurada', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// This would integrate with MRW's actual API
		// For now, return mock data
		$tracking_number = 'MRW' . str_pad( $order->get_id(), 8, '0', STR_PAD_LEFT );
		
		return array(
			'success' => true,
			'tracking_number' => $tracking_number,
			'label_url' => '#',
			'message' => __( 'Etiqueta de envío MRW generada', 'woocommerce-venezuela-pro-2025' )
		);
	}

	/**
	 * Calculate tiered weight cost
	 *
	 * @param float $weight
	 * @return float
	 */
	private function calculate_tiered_weight_cost( $weight ) {
		$cost = 0;
		
		// Tier 1: 0-5kg
		if ( $weight <= 5 ) {
			$cost = $weight * floatval( $this->weight_cost );
		}
		// Tier 2: 5-15kg (10% discount)
		elseif ( $weight <= 15 ) {
			$cost = 5 * floatval( $this->weight_cost );
			$cost += ( $weight - 5 ) * floatval( $this->weight_cost ) * 0.9;
		}
		// Tier 3: 15kg+ (20% discount)
		else {
			$cost = 5 * floatval( $this->weight_cost );
			$cost += 10 * floatval( $this->weight_cost ) * 0.9;
			$cost += ( $weight - 15 ) * floatval( $this->weight_cost ) * 0.8;
		}
		
		return $cost;
	}

	/**
	 * Calculate volume cost with dimensional weight
	 *
	 * @param float $volume
	 * @return float
	 */
	private function calculate_volume_cost( $volume ) {
		// Calculate dimensional weight (volume / 6000 for air freight)
		$dimensional_weight = $volume / 6000;
		
		// Use the higher of actual weight or dimensional weight
		$effective_weight = max( $this->get_package_weight( array() ), $dimensional_weight );
		
		return $effective_weight * floatval( $this->volume_cost );
	}

	/**
	 * Calculate distance-based cost
	 *
	 * @param array $package
	 * @return float
	 */
	private function calculate_distance_cost( $package ) {
		$destination = $package['destination']['state'] ?? '';
		
		// Distance multipliers by state (approximate distances from Caracas)
		$distance_multipliers = array(
			'Distrito Capital' => 0,
			'Miranda' => 0.1,
			'Vargas' => 0.2,
			'Aragua' => 0.3,
			'Carabobo' => 0.4,
			'Lara' => 0.6,
			'Zulia' => 0.8,
			'Bolívar' => 1.0,
			'Amazonas' => 1.2,
			'Delta Amacuro' => 1.1,
			'Apure' => 0.9,
			'Barinas' => 0.7,
			'Cojedes' => 0.5,
			'Falcón' => 0.6,
			'Guárico' => 0.5,
			'Mérida' => 0.8,
			'Monagas' => 0.9,
			'Nueva Esparta' => 0.4,
			'Portuguesa' => 0.6,
			'San Cristóbal' => 0.9,
			'Sucre' => 0.8,
			'Táchira' => 1.0,
			'Trujillo' => 0.7,
			'Yaracuy' => 0.5
		);
		
		$multiplier = $distance_multipliers[ $destination ] ?? 0.5;
		return floatval( $this->base_cost ) * $multiplier * 0.1; // 10% of base cost per distance unit
	}

	/**
	 * Calculate insurance cost
	 *
	 * @param array $package
	 * @return float
	 */
	private function calculate_insurance_cost( $package ) {
		$cart_total = WC()->cart->get_cart_contents_total();
		$insurance_rate = 0.02; // 2% of cart value
		
		return $cart_total * $insurance_rate;
	}

	/**
	 * Calculate packaging cost
	 *
	 * @param array $package
	 * @return float
	 */
	private function calculate_packaging_cost( $package ) {
		$weight = $this->get_package_weight( $package );
		$volume = $this->get_package_volume( $package );
		
		// Base packaging cost
		$cost = 5000; // 5000 VES base
		
		// Add cost for heavy items
		if ( $weight > 10 ) {
			$cost += 2000; // Additional 2000 VES for heavy items
		}
		
		// Add cost for large items
		if ( $volume > 0.1 ) {
			$cost += 3000; // Additional 3000 VES for large items
		}
		
		return $cost;
	}

	/**
	 * Calculate volume discount
	 *
	 * @param array $package
	 * @return float
	 */
	private function calculate_volume_discount( $package ) {
		$cart_total = WC()->cart->get_cart_contents_total();
		$discount = 0;
		
		// Volume discount tiers
		if ( $cart_total >= 1000000 ) { // 1M VES
			$discount = floatval( $this->base_cost ) * 0.15; // 15% discount
		} elseif ( $cart_total >= 500000 ) { // 500K VES
			$discount = floatval( $this->base_cost ) * 0.10; // 10% discount
		} elseif ( $cart_total >= 200000 ) { // 200K VES
			$discount = floatval( $this->base_cost ) * 0.05; // 5% discount
		}
		
		return $discount;
	}

	/**
	 * Get estimated delivery days
	 *
	 * @param string $destination
	 * @return int
	 */
	private function get_estimated_delivery_days( $destination ) {
		$delivery_days = array(
			'Distrito Capital' => 1,
			'Miranda' => 1,
			'Vargas' => 2,
			'Aragua' => 2,
			'Carabobo' => 3,
			'Lara' => 3,
			'Zulia' => 4,
			'Bolívar' => 5,
			'Amazonas' => 7,
			'Delta Amacuro' => 6,
			'Apure' => 5,
			'Barinas' => 4,
			'Cojedes' => 3,
			'Falcón' => 4,
			'Guárico' => 3,
			'Mérida' => 5,
			'Monagas' => 4,
			'Nueva Esparta' => 3,
			'Portuguesa' => 4,
			'San Cristóbal' => 5,
			'Sucre' => 4,
			'Táchira' => 5,
			'Trujillo' => 4,
			'Yaracuy' => 3
		);
		
		return $delivery_days[ $destination ] ?? 5;
	}
}
