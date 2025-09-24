<?php

/**
 * WooCommerce Venezuela Suite 2025 - Pickup Shipping Method
 *
 * Método de envío para recogida en tienda
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pickup Shipping Method class
 */
class WCVS_Shipping_Pickup extends WC_Shipping_Method {

	/**
	 * Constructor
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wcvs_pickup';
		$this->instance_id = absint( $instance_id );
		$this->method_title = __( 'Recogida en Tienda', 'woocommerce-venezuela-pro-2025' );
		$this->method_description = __( 'Método de envío para recogida en tienda', 'woocommerce-venezuela-pro-2025' );
		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal'
		);

		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize Pickup shipping method
	 */
	public function init() {
		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title = $this->get_option( 'title' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->cost = $this->get_option( 'cost' );
		$this->store_address = $this->get_option( 'store_address' );
		$this->store_hours = $this->get_option( 'store_hours' );
		$this->contact_phone = $this->get_option( 'contact_phone' );
		$this->pickup_instructions = $this->get_option( 'pickup_instructions' );
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
				'default' => __( 'Recogida en Tienda', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'enabled' => array(
				'title' => __( 'Activar/Desactivar', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'checkbox',
				'label' => __( 'Activar Recogida en Tienda', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'yes'
			),
			'cost' => array(
				'title' => __( 'Costo', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'number',
				'description' => __( 'Costo de la recogida en tienda (generalmente 0)', 'woocommerce-venezuela-pro-2025' ),
				'default' => '0',
				'desc_tip' => true,
			),
			'store_address' => array(
				'title' => __( 'Dirección de la Tienda', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Dirección completa de la tienda', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'store_hours' => array(
				'title' => __( 'Horarios de Atención', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Horarios de atención de la tienda', 'woocommerce-venezuela-pro-2025' ),
				'default' => "Lunes a Viernes: 8:00 AM - 6:00 PM\nSábados: 9:00 AM - 4:00 PM\nDomingos: Cerrado",
				'desc_tip' => true,
			),
			'contact_phone' => array(
				'title' => __( 'Teléfono de Contacto', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Teléfono para contacto de la tienda', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'pickup_instructions' => array(
				'title' => __( 'Instrucciones de Recogida', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Instrucciones para la recogida en tienda', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'Presenta tu identificación y el número de pedido para recoger tu compra.',
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
		// Check if Pickup is enabled
		if ( $this->enabled !== 'yes' ) {
			return;
		}

		// Check if package is for Venezuela
		if ( $package['destination']['country'] !== 'VE' ) {
			return;
		}

		// Calculate shipping cost (usually 0 for pickup)
		$cost = floatval( $this->cost );

		// Add shipping rate
		$this->add_rate( array(
			'id' => $this->get_rate_id(),
			'label' => $this->title,
			'cost' => $cost,
			'meta_data' => array(
				'Pickup_Address' => $this->store_address,
				'Pickup_Hours' => $this->store_hours,
				'Pickup_Phone' => $this->contact_phone,
				'Pickup_Instructions' => $this->pickup_instructions
			)
		));
	}

	/**
	 * Get store address
	 *
	 * @return string
	 */
	public function get_store_address() {
		return $this->store_address;
	}

	/**
	 * Get store hours
	 *
	 * @return string
	 */
	public function get_store_hours() {
		return $this->store_hours;
	}

	/**
	 * Get contact phone
	 *
	 * @return string
	 */
	public function get_contact_phone() {
		return $this->contact_phone;
	}

	/**
	 * Get pickup instructions
	 *
	 * @return string
	 */
	public function get_pickup_instructions() {
		return $this->pickup_instructions;
	}
}
