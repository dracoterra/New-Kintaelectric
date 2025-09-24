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
	 * Calculate MRW cost
	 *
	 * @param array $package
	 * @return float
	 */
	private function calculate_mrw_cost( $package ) {
		$cost = floatval( $this->base_cost );
		
		// Add weight cost
		$weight = $this->get_package_weight( $package );
		$cost += $weight * floatval( $this->weight_cost );
		
		// Add volume cost
		$volume = $this->get_package_volume( $package );
		$cost += $volume * floatval( $this->volume_cost );
		
		// Apply destination multiplier
		$destination_multiplier = $this->get_destination_multiplier( $package['destination'] );
		$cost *= $destination_multiplier;
		
		return $cost;
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
}
