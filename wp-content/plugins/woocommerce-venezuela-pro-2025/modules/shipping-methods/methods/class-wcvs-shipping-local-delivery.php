<?php

/**
 * WooCommerce Venezuela Suite 2025 - Local Delivery Shipping Method
 *
 * Método de envío para delivery local
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local Delivery Shipping Method class
 */
class WCVS_Shipping_Local_Delivery extends WC_Shipping_Method {

	/**
	 * Constructor
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wcvs_local_delivery';
		$this->instance_id = absint( $instance_id );
		$this->method_title = __( 'Delivery Local', 'woocommerce-venezuela-pro-2025' );
		$this->method_description = __( 'Método de envío para delivery local', 'woocommerce-venezuela-pro-2025' );
		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal'
		);

		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize Local Delivery shipping method
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
		$this->delivery_zones = $this->get_option( 'delivery_zones' );
		$this->delivery_time = $this->get_option( 'delivery_time' );
		$this->contact_phone = $this->get_option( 'contact_phone' );
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
				'default' => __( 'Delivery Local', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'enabled' => array(
				'title' => __( 'Activar/Desactivar', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'checkbox',
				'label' => __( 'Activar Delivery Local', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'yes'
			),
			'cost' => array(
				'title' => __( 'Costo Base', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo base del delivery en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '50000',
				'desc_tip' => true,
			),
			'free_shipping_threshold' => array(
				'title' => __( 'Umbral de Delivery Gratis', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Monto mínimo para delivery gratis en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '500000',
				'desc_tip' => true,
			),
			'base_cost' => array(
				'title' => __( 'Costo Base Delivery', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo base del delivery en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '50000',
				'desc_tip' => true,
			),
			'weight_cost' => array(
				'title' => __( 'Costo por Peso', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo adicional por kilogramo en bolívares', 'woocommerce-venezuela-pro-2025' ),
				'default' => '2000',
				'desc_tip' => true,
			),
			'delivery_zones' => array(
				'title' => __( 'Zonas de Delivery', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Zonas donde se realiza delivery (una por línea)', 'woocommerce-venezuela-pro-2025' ),
				'default' => "Caracas Centro\nCaracas Este\nCaracas Oeste\nCaracas Sur\nMiranda\nVargas",
				'desc_tip' => true,
			),
			'delivery_time' => array(
				'title' => __( 'Tiempo de Delivery', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Tiempo estimado de delivery', 'woocommerce-venezuela-pro-2025' ),
				'default' => '2-4 horas',
				'desc_tip' => true,
			),
			'contact_phone' => array(
				'title' => __( 'Teléfono de Contacto', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Teléfono para contacto del delivery', 'woocommerce-venezuela-pro-2025' ),
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
		// Check if Local Delivery is enabled
		if ( $this->enabled !== 'yes' ) {
			return;
		}

		// Check if package is for Venezuela
		if ( $package['destination']['country'] !== 'VE' ) {
			return;
		}

		// Check if destination is in delivery zones
		if ( ! $this->is_in_delivery_zone( $package['destination'] ) ) {
			return;
		}

		// Calculate shipping cost
		$cost = $this->calculate_local_delivery_cost( $package );

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
				'Local_Delivery_Time' => $this->delivery_time,
				'Local_Delivery_Phone' => $this->contact_phone
			)
		));
	}

	/**
	 * Calculate Local Delivery cost
	 *
	 * @param array $package
	 * @return float
	 */
	private function calculate_local_delivery_cost( $package ) {
		$cost = floatval( $this->base_cost );
		
		// Add weight cost
		$weight = $this->get_package_weight( $package );
		$cost += $weight * floatval( $this->weight_cost );
		
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
	 * Check if destination is in delivery zone
	 *
	 * @param array $destination
	 * @return bool
	 */
	private function is_in_delivery_zone( $destination ) {
		$delivery_zones = explode( "\n", $this->delivery_zones );
		$delivery_zones = array_map( 'trim', $delivery_zones );
		
		$destination_text = strtolower( $destination['city'] . ' ' . $destination['state'] );
		
		foreach ( $delivery_zones as $zone ) {
			if ( strpos( $destination_text, strtolower( $zone ) ) !== false ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Get delivery zones
	 *
	 * @return array
	 */
	public function get_delivery_zones() {
		$zones = explode( "\n", $this->delivery_zones );
		return array_map( 'trim', $zones );
	}

	/**
	 * Get delivery time
	 *
	 * @return string
	 */
	public function get_delivery_time() {
		return $this->delivery_time;
	}

	/**
	 * Get contact phone
	 *
	 * @return string
	 */
	public function get_contact_phone() {
		return $this->contact_phone;
	}
}
