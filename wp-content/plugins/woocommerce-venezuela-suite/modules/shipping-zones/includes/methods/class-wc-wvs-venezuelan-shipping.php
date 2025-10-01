<?php
/**
 * Venezuelan Shipping Method for WooCommerce
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Shipping_Zones\Methods
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Método de envío personalizado para Venezuela
 */
class WC_WVS_Venezuelan_Shipping extends WC_Shipping_Method {

	/**
	 * Constructor
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'wvs_venezuelan_shipping';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Envío Venezuela', 'woocommerce-venezuela-suite' );
		$this->method_description = __( 'Cálculo de envío basado en estados venezolanos.', 'woocommerce-venezuela-suite' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize shipping method
	 */
	public function init() {
		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title', $this->method_title );
		$this->tax_status           = $this->get_option( 'tax_status', 'taxable' );
		$this->cost                = $this->get_option( 'cost', '0' );
		$this->free_shipping_threshold = $this->get_option( 'free_shipping_threshold', '' );
	}

	/**
	 * Initialize form fields
	 */
	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title' => array(
				'title'       => __( 'Título', 'woocommerce-venezuela-suite' ),
				'type'        => 'text',
				'description' => __( 'Título que verá el cliente.', 'woocommerce-venezuela-suite' ),
				'default'     => $this->method_title,
				'desc_tip'    => true,
			),
			'tax_status' => array(
				'title'       => __( 'Estado de impuestos', 'woocommerce-venezuela-suite' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'default'     => 'taxable',
				'options'     => array(
					'taxable' => __( 'Gravable', 'woocommerce-venezuela-suite' ),
					'none'    => _x( 'No gravable', 'Tax status', 'woocommerce-venezuela-suite' ),
				),
			),
			'cost' => array(
				'title'       => __( 'Costo base', 'woocommerce-venezuela-suite' ),
				'type'        => 'number',
				'description' => __( 'Costo base en USD (se añadirá al cálculo por estado).', 'woocommerce-venezuela-suite' ),
				'default'     => '0',
				'desc_tip'    => true,
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
				),
			),
			'free_shipping_threshold' => array(
				'title'       => __( 'Umbral envío gratis', 'woocommerce-venezuela-suite' ),
				'type'        => 'number',
				'description' => __( 'Monto mínimo en USD para envío gratis.', 'woocommerce-venezuela-suite' ),
				'default'     => '',
				'desc_tip'    => true,
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
				),
			),
		);
	}

	/**
	 * Calculate shipping
	 */
	public function calculate_shipping( $package = array() ) {
		// Verificar si el módulo Currency Converter está activo
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		if ( $module_manager->get_module_state( 'currency-converter' ) !== 'active' ) {
			return;
		}

		$shipping_zones_core = Woocommerce_Venezuela_Suite_Shipping_Zones_Core::get_instance();
		$currency_converter = Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();

		// Obtener estado del cliente
		$state_code = $this->get_customer_state_code( $package );
		if ( ! $state_code ) {
			return;
		}

		// Calcular peso total
		$total_weight = $this->calculate_package_weight( $package );

		// Calcular valor total en USD
		$total_value_usd = $this->calculate_package_value_usd( $package, $currency_converter );

		// Verificar envío gratis
		$free_threshold = (float) $this->get_option( 'free_shipping_threshold', 0 );
		if ( $free_threshold > 0 && $total_value_usd >= $free_threshold ) {
			$rate = array(
				'id'       => $this->get_rate_id(),
				'label'    => $this->title . ' (' . __( 'Gratis', 'woocommerce-venezuela-suite' ) . ')',
				'cost'     => 0,
				'calc_tax' => 'per_item',
			);
			$this->add_rate( $rate );
			return;
		}

		// Calcular costo de envío
		$shipping_cost_usd = $shipping_zones_core->calculate_shipping_costs( $state_code, $total_weight, $total_value_usd );
		$base_cost = (float) $this->get_option( 'cost', 0 );
		$total_cost_usd = $shipping_cost_usd + $base_cost;

		// Convertir a VES si es necesario
		$shipping_cost_ves = $currency_converter->convert_usd_to_ves( $total_cost_usd );

		$rate = array(
			'id'       => $this->get_rate_id(),
			'label'    => $this->title,
			'cost'     => $shipping_cost_ves,
			'calc_tax' => 'per_item',
		);

		$this->add_rate( $rate );
	}

	/**
	 * Get customer state code from package
	 */
	private function get_customer_state_code( $package ) {
		if ( empty( $package['destination']['state'] ) ) {
			return false;
		}

		$state_name = $package['destination']['state'];
		$shipping_zones_core = Woocommerce_Venezuela_Suite_Shipping_Zones_Core::get_instance();
		$states = $shipping_zones_core->get_states();

		// Buscar estado por nombre
		foreach ( $states as $code => $state_data ) {
			if ( $state_data['name'] === $state_name ) {
				return $code;
			}
		}

		return false;
	}

	/**
	 * Calculate total package weight
	 */
	private function calculate_package_weight( $package ) {
		$total_weight = 0;

		if ( ! empty( $package['contents'] ) ) {
			foreach ( $package['contents'] as $item ) {
				$product = $item['data'];
				if ( $product->has_weight() ) {
					$total_weight += $product->get_weight() * $item['quantity'];
				}
			}
		}

		return $total_weight;
	}

	/**
	 * Calculate total package value in USD
	 */
	private function calculate_package_value_usd( $package, $currency_converter ) {
		$total_value = 0;

		if ( ! empty( $package['contents'] ) ) {
			foreach ( $package['contents'] as $item ) {
				$product = $item['data'];
				$price = $product->get_price();
				
				// Si el precio está en VES, convertir a USD
				if ( get_woocommerce_currency() === 'VES' ) {
					$price = $currency_converter->convert_ves_to_usd( $price );
				}
				
				$total_value += $price * $item['quantity'];
			}
		}

		return $total_value;
	}
}
