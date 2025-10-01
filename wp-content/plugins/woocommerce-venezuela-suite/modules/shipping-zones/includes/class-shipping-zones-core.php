<?php
/**
 * Núcleo del módulo Shipping Zones (esqueleto)
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Shipping_Zones
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core functionality for Venezuelan shipping zones and rates.
 */
class Woocommerce_Venezuela_Suite_Shipping_Zones_Core extends Woocommerce_Venezuela_Suite_Abstract_Module {
	/** @var self */
	private static $instance = null;
	
	/** @var array Venezuelan states data */
	private $venezuelan_states = array();

	/**
	 * Singleton.
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->module_slug = 'shipping-zones';
		$this->module_name = __( 'Shipping Zones', 'woocommerce-venezuela-suite' );
		$this->module_version = '1.0.0';
		$this->module_description = __( 'Manages Venezuelan shipping zones and rates.', 'woocommerce-venezuela-suite' );
	}

	/**
	 * Run the module
	 */
	public function run() {
		add_action( 'woocommerce_shipping_init', array( $this, 'register_shipping_methods' ) );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_methods' ) );
		add_action( 'init', array( $this, 'init_venezuelan_states' ) );
	}

	/**
	 * Register shipping methods
	 */
	public function register_shipping_methods() {
		require_once plugin_dir_path( __FILE__ ) . 'methods/class-wc-wvs-venezuelan-shipping.php';
	}

	/**
	 * Add shipping methods to WooCommerce
	 */
	public function add_shipping_methods( $methods ) {
		$methods['wvs_venezuelan_shipping'] = 'WC_WVS_Venezuelan_Shipping';
		return $methods;
	}

	/**
	 * Initialize Venezuelan states and cities
	 */
	public function init_venezuelan_states() {
		$this->venezuelan_states = $this->get_venezuelan_states();
	}

	/**
	 * Get Venezuelan states with major cities
	 */
	private function get_venezuelan_states() {
		return array(
			'AMAZONAS' => array(
				'name' => 'Amazonas',
				'cities' => array( 'Puerto Ayacucho', 'San Carlos de Río Negro' )
			),
			'ANZOATEGUI' => array(
				'name' => 'Anzoátegui',
				'cities' => array( 'Barcelona', 'Puerto La Cruz', 'Lechería', 'El Tigre', 'Anaco' )
			),
			'APURE' => array(
				'name' => 'Apure',
				'cities' => array( 'San Fernando de Apure', 'Guasdualito', 'Elorza' )
			),
			'ARAGUA' => array(
				'name' => 'Aragua',
				'cities' => array( 'Maracay', 'Turmero', 'La Victoria', 'Villa de Cura', 'El Limón' )
			),
			'BARINAS' => array(
				'name' => 'Barinas',
				'cities' => array( 'Barinas', 'Socopó', 'Santa Bárbara' )
			),
			'BOLIVAR' => array(
				'name' => 'Bolívar',
				'cities' => array( 'Ciudad Bolívar', 'Ciudad Guayana', 'Upata', 'El Callao' )
			),
			'CARABOBO' => array(
				'name' => 'Carabobo',
				'cities' => array( 'Valencia', 'Puerto Cabello', 'Guacara', 'Mariara', 'Bejuma' )
			),
			'COJEDES' => array(
				'name' => 'Cojedes',
				'cities' => array( 'San Carlos', 'Tinaquillo', 'El Baúl' )
			),
			'DELTA_AMACURO' => array(
				'name' => 'Delta Amacuro',
				'cities' => array( 'Tucupita', 'Pedernales' )
			),
			'DISTRITO_CAPITAL' => array(
				'name' => 'Distrito Capital',
				'cities' => array( 'Caracas' )
			),
			'FALCON' => array(
				'name' => 'Falcón',
				'cities' => array( 'Coro', 'Punto Fijo', 'Valle de la Pascua' )
			),
			'GUARICO' => array(
				'name' => 'Guárico',
				'cities' => array( 'San Juan de los Morros', 'Valle de la Pascua', 'Calabozo' )
			),
			'LARA' => array(
				'name' => 'Lara',
				'cities' => array( 'Barquisimeto', 'Cabudare', 'Duaca', 'El Tocuyo' )
			),
			'MERIDA' => array(
				'name' => 'Mérida',
				'cities' => array( 'Mérida', 'El Vigía', 'Tovar', 'Ejido' )
			),
			'MIRANDA' => array(
				'name' => 'Miranda',
				'cities' => array( 'Los Teques', 'Guarenas', 'Guatire', 'Petare', 'Santa Teresa' )
			),
			'MONAGAS' => array(
				'name' => 'Monagas',
				'cities' => array( 'Maturín', 'Caripito', 'Punta de Mata' )
			),
			'NUEVA_ESPARTA' => array(
				'name' => 'Nueva Esparta',
				'cities' => array( 'La Asunción', 'Porlamar', 'Juan Griego' )
			),
			'PORTUGUESA' => array(
				'name' => 'Portuguesa',
				'cities' => array( 'Guanare', 'Acarigua', 'Araure', 'Villa Bruzual' )
			),
			'SUCRE' => array(
				'name' => 'Sucre',
				'cities' => array( 'Cumaná', 'Carúpano', 'Güiria' )
			),
			'TACHIRA' => array(
				'name' => 'Táchira',
				'cities' => array( 'San Cristóbal', 'Táriba', 'Rubio', 'Colón' )
			),
			'TRUJILLO' => array(
				'name' => 'Trujillo',
				'cities' => array( 'Trujillo', 'Valera', 'Boconó', 'La Quebrada' )
			),
			'VARGAS' => array(
				'name' => 'Vargas',
				'cities' => array( 'La Guaira', 'Maiquetía', 'Catia La Mar' )
			),
			'YARACUY' => array(
				'name' => 'Yaracuy',
				'cities' => array( 'San Felipe', 'Yaritagua', 'Chivacoa' )
			),
			'ZULIA' => array(
				'name' => 'Zulia',
				'cities' => array( 'Maracaibo', 'Cabimas', 'Ciudad Ojeda', 'San Francisco' )
			)
		);
	}

	/**
	 * Get states data
	 */
	public function get_states() {
		return $this->venezuelan_states;
	}

	/**
	 * Calculate shipping costs based on state and weight
	 */
	public function calculate_shipping_costs( $state_code, $weight_kg, $package_value_usd ) {
		$base_rates = $this->get_base_shipping_rates();
		
		if ( ! isset( $base_rates[ $state_code ] ) ) {
			return 0;
		}

		$base_rate = $base_rates[ $state_code ];
		$weight_rate = $this->calculate_weight_rate( $weight_kg );
		$value_rate = $this->calculate_value_rate( $package_value_usd );
		
		return $base_rate + $weight_rate + $value_rate;
	}

	/**
	 * Get base shipping rates by state (in USD)
	 */
	private function get_base_shipping_rates() {
		return array(
			'DISTRITO_CAPITAL' => 5.00,
			'MIRANDA' => 6.00,
			'VARGAS' => 7.00,
			'ARAGUA' => 8.00,
			'CARABOBO' => 9.00,
			'LARA' => 12.00,
			'YARACUY' => 10.00,
			'PORTUGUESA' => 11.00,
			'BARINAS' => 13.00,
			'COJEDES' => 10.00,
			'GUARICO' => 12.00,
			'ANZOATEGUI' => 15.00,
			'MONAGAS' => 16.00,
			'SUCRE' => 18.00,
			'BOLIVAR' => 20.00,
			'DELTA_AMACURO' => 22.00,
			'TACHIRA' => 14.00,
			'MERIDA' => 15.00,
			'TRUJILLO' => 13.00,
			'ZULIA' => 16.00,
			'FALCON' => 14.00,
			'NUEVA_ESPARTA' => 20.00,
			'APURE' => 17.00,
			'AMAZONAS' => 25.00
		);
	}

	/**
	 * Calculate additional rate based on weight
	 */
	private function calculate_weight_rate( $weight_kg ) {
		if ( $weight_kg <= 1 ) {
			return 0;
		} elseif ( $weight_kg <= 5 ) {
			return 2.00;
		} elseif ( $weight_kg <= 10 ) {
			return 4.00;
		} elseif ( $weight_kg <= 20 ) {
			return 6.00;
		} else {
			return 8.00;
		}
	}

	/**
	 * Calculate additional rate based on package value
	 */
	private function calculate_value_rate( $package_value_usd ) {
		if ( $package_value_usd <= 100 ) {
			return 0;
		} elseif ( $package_value_usd <= 500 ) {
			return 3.00;
		} elseif ( $package_value_usd <= 1000 ) {
			return 5.00;
		} else {
			return 7.00;
		}
	}
}


