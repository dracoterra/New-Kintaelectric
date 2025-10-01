<?php
/**
 * Currency Converter Location Manager - Gestor de ubicaciones
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestor de ubicaciones para el Currency Converter.
 * Controla dónde se muestra el convertidor en el sitio.
 */
class Woocommerce_Venezuela_Suite_Converter_Location_Manager {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración de ubicaciones */
	private $config = array();

	/** @var array Ubicaciones registradas */
	private $registered_locations = array();

	/** @var array Hooks de WordPress registrados */
	private $registered_hooks = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->init_default_locations();
		$this->register_hooks();
	}

	/**
	 * Singleton.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Carga configuración de ubicaciones.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'shop_page_enabled' => get_option( 'wvs_converter_shop_page_enabled', true ),
			'product_page_enabled' => get_option( 'wvs_converter_product_page_enabled', true ),
			'cart_page_enabled' => get_option( 'wvs_converter_cart_page_enabled', true ),
			'checkout_page_enabled' => get_option( 'wvs_converter_checkout_page_enabled', true ),
			'header_enabled' => get_option( 'wvs_converter_header_enabled', false ),
			'footer_enabled' => get_option( 'wvs_converter_footer_enabled', false ),
			'widget_enabled' => get_option( 'wvs_converter_widget_enabled', true ),
			'shortcode_enabled' => get_option( 'wvs_converter_shortcode_enabled', true ),
			'elementor_enabled' => get_option( 'wvs_converter_elementor_enabled', true ),
			'custom_hooks_enabled' => get_option( 'wvs_converter_custom_hooks_enabled', false ),
		);
	}

	/**
	 * Inicializa ubicaciones por defecto.
	 *
	 * @return void
	 */
	private function init_default_locations() {
		$this->registered_locations = array(
			'shop_page' => array(
				'name' => __( 'Página de Tienda', 'woocommerce-venezuela-suite' ),
				'hook' => 'woocommerce_before_shop_loop',
				'priority' => 10,
				'description' => __( 'Mostrar antes de la lista de productos en la tienda', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['shop_page_enabled'],
			),
			'product_page' => array(
				'name' => __( 'Página de Producto', 'woocommerce-venezuela-suite' ),
				'hook' => 'woocommerce_single_product_summary',
				'priority' => 25,
				'description' => __( 'Mostrar en el resumen del producto individual', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['product_page_enabled'],
			),
			'cart_page' => array(
				'name' => __( 'Página del Carrito', 'woocommerce-venezuela-suite' ),
				'hook' => 'woocommerce_before_cart_table',
				'priority' => 10,
				'description' => __( 'Mostrar antes de la tabla del carrito', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['cart_page_enabled'],
			),
			'checkout_page' => array(
				'name' => __( 'Página de Checkout', 'woocommerce-venezuela-suite' ),
				'hook' => 'woocommerce_before_checkout_form',
				'priority' => 10,
				'description' => __( 'Mostrar antes del formulario de checkout', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['checkout_page_enabled'],
			),
			'header' => array(
				'name' => __( 'Header del Sitio', 'woocommerce-venezuela-suite' ),
				'hook' => 'wp_head',
				'priority' => 10,
				'description' => __( 'Mostrar en el header del sitio', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['header_enabled'],
			),
			'footer' => array(
				'name' => __( 'Footer del Sitio', 'woocommerce-venezuela-suite' ),
				'hook' => 'wp_footer',
				'priority' => 10,
				'description' => __( 'Mostrar en el footer del sitio', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['footer_enabled'],
			),
		);
	}

	/**
	 * Registra hooks de WordPress.
	 *
	 * @return void
	 */
	private function register_hooks() {
		foreach ( $this->registered_locations as $location_id => $location ) {
			if ( $location['enabled'] ) {
				$this->register_location_hook( $location_id, $location );
			}
		}
	}

	/**
	 * Registra hook para una ubicación específica.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @param array $location Configuración de la ubicación.
	 * @return void
	 */
	private function register_location_hook( $location_id, $location ) {
		$callback = array( $this, 'render_converter_at_location' );
		$priority = $location['priority'];
		
		add_action( $location['hook'], $callback, $priority );
		
		$this->registered_hooks[ $location_id ] = array(
			'hook' => $location['hook'],
			'callback' => $callback,
			'priority' => $priority,
		);
	}

	/**
	 * Renderiza el convertidor en una ubicación específica.
	 *
	 * @return void
	 */
	public function render_converter_at_location() {
		$current_location = $this->get_current_location();
		
		if ( ! $current_location ) {
			return;
		}
		
		// Verificar si el convertidor debe mostrarse en esta ubicación
		if ( ! $this->should_show_converter_at_location( $current_location ) ) {
			return;
		}
		
		// Renderizar el convertidor
		$this->render_converter( $current_location );
	}

	/**
	 * Determina la ubicación actual basada en el hook ejecutado.
	 *
	 * @return string|null ID de la ubicación actual.
	 */
	private function get_current_location() {
		$current_filter = current_filter();
		
		foreach ( $this->registered_hooks as $location_id => $hook_info ) {
			if ( $hook_info['hook'] === $current_filter ) {
				return $location_id;
			}
		}
		
		return null;
	}

	/**
	 * Verifica si el convertidor debe mostrarse en la ubicación actual.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @return bool True si debe mostrarse.
	 */
	private function should_show_converter_at_location( $location_id ) {
		// Verificar configuración general
		if ( ! $this->registered_locations[ $location_id ]['enabled'] ) {
			return false;
		}
		
		// Verificar condiciones específicas por ubicación
		switch ( $location_id ) {
			case 'shop_page':
				return is_shop() || is_product_category() || is_product_tag();
				
			case 'product_page':
				return is_product();
				
			case 'cart_page':
				return is_cart();
				
			case 'checkout_page':
				return is_checkout();
				
			case 'header':
			case 'footer':
				return true; // Siempre mostrar en header/footer si está habilitado
				
			default:
				return true;
		}
	}

	/**
	 * Renderiza el convertidor de moneda.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @return void
	 */
	private function render_converter( $location_id ) {
		$converter_public = Woocommerce_Venezuela_Suite_Converter_Public::get_instance();
		$converter_public->render_converter_widget( $location_id );
	}

	/**
	 * Registra una nueva ubicación personalizada.
	 *
	 * @param string $location_id ID único de la ubicación.
	 * @param array $location_config Configuración de la ubicación.
	 * @return bool True si se registró correctamente.
	 */
	public function register_custom_location( $location_id, $location_config ) {
		if ( ! $this->config['custom_hooks_enabled'] ) {
			return false;
		}
		
		$required_keys = array( 'name', 'hook', 'priority' );
		foreach ( $required_keys as $key ) {
			if ( ! isset( $location_config[ $key ] ) ) {
				return false;
			}
		}
		
		$default_config = array(
			'description' => '',
			'enabled' => true,
		);
		
		$location_config = array_merge( $default_config, $location_config );
		$this->registered_locations[ $location_id ] = $location_config;
		
		// Registrar hook si está habilitado
		if ( $location_config['enabled'] ) {
			$this->register_location_hook( $location_id, $location_config );
		}
		
		return true;
	}

	/**
	 * Desregistra una ubicación personalizada.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @return bool True si se desregistró correctamente.
	 */
	public function unregister_custom_location( $location_id ) {
		if ( ! isset( $this->registered_locations[ $location_id ] ) ) {
			return false;
		}
		
		// Remover hook si está registrado
		if ( isset( $this->registered_hooks[ $location_id ] ) ) {
			$hook_info = $this->registered_hooks[ $location_id ];
			remove_action( $hook_info['hook'], $hook_info['callback'], $hook_info['priority'] );
			unset( $this->registered_hooks[ $location_id ] );
		}
		
		unset( $this->registered_locations[ $location_id ] );
		
		return true;
	}

	/**
	 * Habilita/deshabilita una ubicación.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @param bool $enabled Estado habilitado.
	 * @return bool True si se actualizó correctamente.
	 */
	public function toggle_location( $location_id, $enabled ) {
		if ( ! isset( $this->registered_locations[ $location_id ] ) ) {
			return false;
		}
		
		$was_enabled = $this->registered_locations[ $location_id ]['enabled'];
		$this->registered_locations[ $location_id ]['enabled'] = $enabled;
		
		if ( $enabled && ! $was_enabled ) {
			// Habilitar: registrar hook
			$this->register_location_hook( $location_id, $this->registered_locations[ $location_id ] );
		} elseif ( ! $enabled && $was_enabled ) {
			// Deshabilitar: remover hook
			if ( isset( $this->registered_hooks[ $location_id ] ) ) {
				$hook_info = $this->registered_hooks[ $location_id ];
				remove_action( $hook_info['hook'], $hook_info['callback'], $hook_info['priority'] );
				unset( $this->registered_hooks[ $location_id ] );
			}
		}
		
		// Actualizar configuración en base de datos
		$config_key = 'wvs_converter_' . $location_id . '_enabled';
		update_option( $config_key, $enabled );
		
		return true;
	}

	/**
	 * Obtiene todas las ubicaciones registradas.
	 *
	 * @return array Ubicaciones registradas.
	 */
	public function get_registered_locations() {
		return $this->registered_locations;
	}

	/**
	 * Obtiene ubicaciones habilitadas.
	 *
	 * @return array Ubicaciones habilitadas.
	 */
	public function get_enabled_locations() {
		return array_filter( $this->registered_locations, function( $location ) {
			return $location['enabled'];
		});
	}

	/**
	 * Obtiene ubicaciones deshabilitadas.
	 *
	 * @return array Ubicaciones deshabilitadas.
	 */
	public function get_disabled_locations() {
		return array_filter( $this->registered_locations, function( $location ) {
			return ! $location['enabled'];
		});
	}

	/**
	 * Obtiene información de una ubicación específica.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @return array|null Información de la ubicación.
	 */
	public function get_location_info( $location_id ) {
		return $this->registered_locations[ $location_id ] ?? null;
	}

	/**
	 * Verifica si una ubicación está habilitada.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @return bool True si está habilitada.
	 */
	public function is_location_enabled( $location_id ) {
		return isset( $this->registered_locations[ $location_id ] ) && 
			$this->registered_locations[ $location_id ]['enabled'];
	}

	/**
	 * Obtiene ubicaciones disponibles para un tipo de página específico.
	 *
	 * @param string $page_type Tipo de página (shop, product, cart, checkout).
	 * @return array Ubicaciones disponibles.
	 */
	public function get_locations_for_page_type( $page_type ) {
		$available_locations = array();
		
		foreach ( $this->registered_locations as $location_id => $location ) {
			if ( $this->is_location_suitable_for_page_type( $location_id, $page_type ) ) {
				$available_locations[ $location_id ] = $location;
			}
		}
		
		return $available_locations;
	}

	/**
	 * Verifica si una ubicación es adecuada para un tipo de página.
	 *
	 * @param string $location_id ID de la ubicación.
	 * @param string $page_type Tipo de página.
	 * @return bool True si es adecuada.
	 */
	private function is_location_suitable_for_page_type( $location_id, $page_type ) {
		$suitability_map = array(
			'shop' => array( 'shop_page', 'header', 'footer' ),
			'product' => array( 'product_page', 'header', 'footer' ),
			'cart' => array( 'cart_page', 'header', 'footer' ),
			'checkout' => array( 'checkout_page', 'header', 'footer' ),
		);
		
		return isset( $suitability_map[ $page_type ] ) && 
			in_array( $location_id, $suitability_map[ $page_type ] );
	}

	/**
	 * Obtiene estadísticas de uso de ubicaciones.
	 *
	 * @return array Estadísticas de ubicaciones.
	 */
	public function get_location_stats() {
		$stats = array(
			'total_locations' => count( $this->registered_locations ),
			'enabled_locations' => count( $this->get_enabled_locations() ),
			'disabled_locations' => count( $this->get_disabled_locations() ),
			'custom_locations' => 0,
			'default_locations' => 0,
		);
		
		$default_location_ids = array( 'shop_page', 'product_page', 'cart_page', 'checkout_page', 'header', 'footer' );
		
		foreach ( $this->registered_locations as $location_id => $location ) {
			if ( in_array( $location_id, $default_location_ids ) ) {
				$stats['default_locations']++;
			} else {
				$stats['custom_locations']++;
			}
		}
		
		return $stats;
	}

	/**
	 * Actualiza configuración de ubicaciones.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'shop_page_enabled',
			'product_page_enabled',
			'cart_page_enabled',
			'checkout_page_enabled',
			'header_enabled',
			'footer_enabled',
			'widget_enabled',
			'shortcode_enabled',
			'elementor_enabled',
			'custom_hooks_enabled',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_converter_' . $key, $value );
				$this->config[ $key ] = $value;
				
				// Actualizar ubicación correspondiente
				$location_id = str_replace( '_enabled', '', $key );
				if ( isset( $this->registered_locations[ $location_id ] ) ) {
					$this->toggle_location( $location_id, $value );
				}
			}
		}

		return true;
	}

	/**
	 * Obtiene configuración actual.
	 *
	 * @return array Configuración actual.
	 */
	public function get_config() {
		return $this->config;
	}
}
