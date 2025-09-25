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
		// All shipping methods are defined in this file
		// No need to load separate files
	}
	
	/**
	 * Add shipping methods to WooCommerce
	 */
	public function add_shipping_methods( $methods ) {
		$methods['wvp_mrw'] = 'WVP_MRW_Shipping';
		$methods['wvp_zoom'] = 'WVP_Zoom_Shipping';
		$methods['wvp_menssajero'] = 'WVP_Menssajero_Shipping';
		$methods['wvp_local_delivery'] = 'WVP_Local_Delivery';
		return $methods;
	}
	
	/**
	 * Validate shipping selection
	 */
	public function validate_shipping() {
		// Add any Venezuelan-specific shipping validation here
	}
}

/**
 * MRW Shipping Method
 */
class WVP_MRW_Shipping extends WC_Shipping_Method {
	
	// Declare properties explicitly for PHP 8+ compatibility
	public $cost;
	public $free_shipping_min;
	
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wvp_mrw';
		$this->instance_id = absint( $instance_id );
		$this->method_title = 'MRW Venezuela';
		$this->method_description = 'Envío mediante MRW en Venezuela';
		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		
		$this->init();
	}
	
	public function init() {
		$this->init_form_fields();
		$this->init_settings();
		
		$this->title = $this->get_option( 'title' );
		$this->tax_status = $this->get_option( 'tax_status' );
		$this->cost = $this->get_option( 'cost' );
		$this->free_shipping_min = $this->get_option( 'free_shipping_min' );
		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	
	public function init_form_fields() {
		$this->form_fields = array(
			'title' => array(
				'title' => 'Título',
				'type' => 'text',
				'description' => 'Título que se muestra al cliente',
				'default' => 'MRW Venezuela',
				'desc_tip' => true,
			),
			'tax_status' => array(
				'title' => 'Estado de Impuestos',
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => 'Gravable',
					'none' => 'No Gravable',
				),
			),
			'cost' => array(
				'title' => 'Costo Base',
				'type' => 'number',
				'description' => 'Costo base en USD',
				'default' => '5.00',
				'desc_tip' => true,
			),
			'free_shipping_min' => array(
				'title' => 'Envío Gratis Mínimo',
				'type' => 'number',
				'description' => 'Monto mínimo para envío gratis (USD)',
				'default' => '50.00',
				'desc_tip' => true,
			),
		);
	}
	
	public function calculate_shipping( $package = array() ) {
		$cost = floatval( $this->cost );
		$free_shipping_min = floatval( $this->free_shipping_min );
		
		// Check if free shipping applies
		if ( $package['contents_cost'] >= $free_shipping_min ) {
			$cost = 0;
		}
		
		// Add weight-based cost
		$weight = $this->get_package_weight( $package );
		if ( $weight > 0 ) {
			$cost += $weight * 0.5; // $0.50 per kg
		}
		
		$rate = array(
			'id' => $this->get_rate_id(),
			'label' => $this->title,
			'cost' => $cost,
		);
		
		$this->add_rate( $rate );
	}
	
	private function get_package_weight( $package ) {
		$weight = 0;
		foreach ( $package['contents'] as $item ) {
			$weight += $item['data']->get_weight() * $item['quantity'];
		}
		return $weight;
	}
}

/**
 * Zoom Envíos Shipping Method
 */
class WVP_Zoom_Shipping extends WC_Shipping_Method {
	
	// Declare properties explicitly for PHP 8+ compatibility
	public $cost;
	public $free_shipping_min;
	
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wvp_zoom';
		$this->instance_id = absint( $instance_id );
		$this->method_title = 'Zoom Envíos Venezuela';
		$this->method_description = 'Envío mediante Zoom Envíos en Venezuela';
		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		
		$this->init();
	}
	
	public function init() {
		$this->init_form_fields();
		$this->init_settings();
		
		$this->title = $this->get_option( 'title' );
		$this->tax_status = $this->get_option( 'tax_status' );
		$this->cost = $this->get_option( 'cost' );
		$this->free_shipping_min = $this->get_option( 'free_shipping_min' );
		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	
	public function init_form_fields() {
		$this->form_fields = array(
			'title' => array(
				'title' => 'Título',
				'type' => 'text',
				'description' => 'Título que se muestra al cliente',
				'default' => 'Zoom Envíos Venezuela',
				'desc_tip' => true,
			),
			'tax_status' => array(
				'title' => 'Estado de Impuestos',
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => 'Gravable',
					'none' => 'No Gravable',
				),
			),
			'cost' => array(
				'title' => 'Costo Base',
				'type' => 'number',
				'description' => 'Costo base en USD',
				'default' => '4.50',
				'desc_tip' => true,
			),
			'free_shipping_min' => array(
				'title' => 'Envío Gratis Mínimo',
				'type' => 'number',
				'description' => 'Monto mínimo para envío gratis (USD)',
				'default' => '75.00',
				'desc_tip' => true,
			),
		);
	}
	
	public function calculate_shipping( $package = array() ) {
		$cost = floatval( $this->cost );
		$free_shipping_min = floatval( $this->free_shipping_min );
		
		// Check if free shipping applies
		if ( $package['contents_cost'] >= $free_shipping_min ) {
			$cost = 0;
		}
		
		// Add weight-based cost
		$weight = $this->get_package_weight( $package );
		if ( $weight > 0 ) {
			$cost += $weight * 0.4; // $0.40 per kg
		}
		
		$rate = array(
			'id' => $this->get_rate_id(),
			'label' => $this->title,
			'cost' => $cost,
		);
		
		$this->add_rate( $rate );
	}
	
	private function get_package_weight( $package ) {
		$weight = 0;
		foreach ( $package['contents'] as $item ) {
			$weight += $item['data']->get_weight() * $item['quantity'];
		}
		return $weight;
	}
}

/**
 * Menssajero Shipping Method
 */
class WVP_Menssajero_Shipping extends WC_Shipping_Method {
	
	// Declare properties explicitly for PHP 8+ compatibility
	public $cost;
	public $free_shipping_min;
	
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wvp_menssajero';
		$this->instance_id = absint( $instance_id );
		$this->method_title = 'Menssajero Venezuela';
		$this->method_description = 'Envío mediante Menssajero en Venezuela';
		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		
		$this->init();
	}
	
	public function init() {
		$this->init_form_fields();
		$this->init_settings();
		
		$this->title = $this->get_option( 'title' );
		$this->tax_status = $this->get_option( 'tax_status' );
		$this->cost = $this->get_option( 'cost' );
		$this->free_shipping_min = $this->get_option( 'free_shipping_min' );
		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	
	public function init_form_fields() {
		$this->form_fields = array(
			'title' => array(
				'title' => 'Título',
				'type' => 'text',
				'description' => 'Título que se muestra al cliente',
				'default' => 'Menssajero Venezuela',
				'desc_tip' => true,
			),
			'tax_status' => array(
				'title' => 'Estado de Impuestos',
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => 'Gravable',
					'none' => 'No Gravable',
				),
			),
			'cost' => array(
				'title' => 'Costo Base',
				'type' => 'number',
				'description' => 'Costo base en USD',
				'default' => '6.00',
				'desc_tip' => true,
			),
			'free_shipping_min' => array(
				'title' => 'Envío Gratis Mínimo',
				'type' => 'number',
				'description' => 'Monto mínimo para envío gratis (USD)',
				'default' => '100.00',
				'desc_tip' => true,
			),
		);
	}
	
	public function calculate_shipping( $package = array() ) {
		$cost = floatval( $this->cost );
		$free_shipping_min = floatval( $this->free_shipping_min );
		
		// Check if free shipping applies
		if ( $package['contents_cost'] >= $free_shipping_min ) {
			$cost = 0;
		}
		
		// Add weight-based cost
		$weight = $this->get_package_weight( $package );
		if ( $weight > 0 ) {
			$cost += $weight * 0.6; // $0.60 per kg
		}
		
		$rate = array(
			'id' => $this->get_rate_id(),
			'label' => $this->title,
			'cost' => $cost,
		);
		
		$this->add_rate( $rate );
	}
	
	private function get_package_weight( $package ) {
		$weight = 0;
		foreach ( $package['contents'] as $item ) {
			$weight += $item['data']->get_weight() * $item['quantity'];
		}
		return $weight;
	}
}

/**
 * Local Delivery Shipping Method
 */
class WVP_Local_Delivery extends WC_Shipping_Method {
	
	// Declare properties explicitly for PHP 8+ compatibility
	public $cost;
	public $free_shipping_min;
	public $delivery_areas;
	
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wvp_local_delivery';
		$this->instance_id = absint( $instance_id );
		$this->method_title = 'Entrega Local Venezuela';
		$this->method_description = 'Entrega local en Venezuela';
		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		
		$this->init();
	}
	
	public function init() {
		$this->init_form_fields();
		$this->init_settings();
		
		$this->title = $this->get_option( 'title' );
		$this->tax_status = $this->get_option( 'tax_status' );
		$this->cost = $this->get_option( 'cost' );
		$this->free_shipping_min = $this->get_option( 'free_shipping_min' );
		$this->delivery_areas = $this->get_option( 'delivery_areas' );
		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	
	public function init_form_fields() {
		$this->form_fields = array(
			'title' => array(
				'title' => 'Título',
				'type' => 'text',
				'description' => 'Título que se muestra al cliente',
				'default' => 'Entrega Local',
				'desc_tip' => true,
			),
			'tax_status' => array(
				'title' => 'Estado de Impuestos',
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => 'Gravable',
					'none' => 'No Gravable',
				),
			),
			'cost' => array(
				'title' => 'Costo Base',
				'type' => 'number',
				'description' => 'Costo base en USD',
				'default' => '2.00',
				'desc_tip' => true,
			),
			'free_shipping_min' => array(
				'title' => 'Envío Gratis Mínimo',
				'type' => 'number',
				'description' => 'Monto mínimo para envío gratis (USD)',
				'default' => '25.00',
				'desc_tip' => true,
			),
			'delivery_areas' => array(
				'title' => 'Áreas de Entrega',
				'type' => 'textarea',
				'description' => 'Lista de áreas donde se realiza entrega local (una por línea)',
				'default' => "Caracas\nValencia\nMaracaibo\nBarquisimeto",
				'desc_tip' => true,
			),
		);
	}
	
	public function calculate_shipping( $package = array() ) {
		$cost = floatval( $this->cost );
		$free_shipping_min = floatval( $this->free_shipping_min );
		
		// Check if free shipping applies
		if ( $package['contents_cost'] >= $free_shipping_min ) {
			$cost = 0;
		}
		
		// Check if delivery area is covered
		$delivery_areas = explode( "\n", $this->delivery_areas );
		$delivery_areas = array_map( 'trim', $delivery_areas );
		
		$customer_city = $package['destination']['city'];
		if ( ! in_array( $customer_city, $delivery_areas ) ) {
			return; // Don't offer this shipping method
		}
		
		$rate = array(
			'id' => $this->get_rate_id(),
			'label' => $this->title,
			'cost' => $cost,
		);
		
		$this->add_rate( $rate );
	}
}
