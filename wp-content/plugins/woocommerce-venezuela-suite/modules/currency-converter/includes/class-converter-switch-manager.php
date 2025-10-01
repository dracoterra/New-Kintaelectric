<?php
/**
 * Currency Converter Switch Manager - Gestor de switches independientes
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestor de switches independientes para el Currency Converter.
 * Controla cuándo y dónde se muestra el convertidor.
 */
class Woocommerce_Venezuela_Suite_Converter_Switch_Manager {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración de switches */
	private $config = array();

	/** @var array Switches registrados */
	private $registered_switches = array();

	/** @var array Condiciones de evaluación */
	private $evaluation_cache = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->init_default_switches();
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
	 * Carga configuración de switches.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'main_switch_enabled' => get_option( 'wvs_converter_main_switch_enabled', true ),
			'product_switch_enabled' => get_option( 'wvs_converter_product_switch_enabled', true ),
			'category_switch_enabled' => get_option( 'wvs_converter_category_switch_enabled', true ),
			'page_switch_enabled' => get_option( 'wvs_converter_page_switch_enabled', true ),
			'user_role_switch_enabled' => get_option( 'wvs_converter_user_role_switch_enabled', true ),
			'location_switch_enabled' => get_option( 'wvs_converter_location_switch_enabled', true ),
			'device_switch_enabled' => get_option( 'wvs_converter_device_switch_enabled', true ),
			'time_switch_enabled' => get_option( 'wvs_converter_time_switch_enabled', false ),
		);
	}

	/**
	 * Inicializa switches por defecto.
	 *
	 * @return void
	 */
	private function init_default_switches() {
		$this->registered_switches = array(
			'main' => array(
				'name' => __( 'Switch Principal', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación general del convertidor', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['main_switch_enabled'],
				'priority' => 100,
				'callback' => array( $this, 'evaluate_main_switch' ),
			),
			'product' => array(
				'name' => __( 'Switch por Producto', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación por producto individual', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['product_switch_enabled'],
				'priority' => 90,
				'callback' => array( $this, 'evaluate_product_switch' ),
			),
			'category' => array(
				'name' => __( 'Switch por Categoría', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación por categoría de productos', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['category_switch_enabled'],
				'priority' => 80,
				'callback' => array( $this, 'evaluate_category_switch' ),
			),
			'page' => array(
				'name' => __( 'Switch por Página', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación por página específica', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['page_switch_enabled'],
				'priority' => 70,
				'callback' => array( $this, 'evaluate_page_switch' ),
			),
			'user_role' => array(
				'name' => __( 'Switch por Usuario/Rol', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación según el rol del usuario', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['user_role_switch_enabled'],
				'priority' => 60,
				'callback' => array( $this, 'evaluate_user_role_switch' ),
			),
			'location' => array(
				'name' => __( 'Switch por Ubicación Geográfica', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación según la ubicación del usuario', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['location_switch_enabled'],
				'priority' => 50,
				'callback' => array( $this, 'evaluate_location_switch' ),
			),
			'device' => array(
				'name' => __( 'Switch por Dispositivo', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación según el tipo de dispositivo', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['device_switch_enabled'],
				'priority' => 40,
				'callback' => array( $this, 'evaluate_device_switch' ),
			),
			'time' => array(
				'name' => __( 'Switch por Horario', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Controla la activación según el horario', 'woocommerce-venezuela-suite' ),
				'enabled' => $this->config['time_switch_enabled'],
				'priority' => 30,
				'callback' => array( $this, 'evaluate_time_switch' ),
			),
		);
	}

	/**
	 * Evalúa si el convertidor debe mostrarse.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool True si debe mostrarse.
	 */
	public function should_show_converter( $context = array() ) {
		// Ordenar switches por prioridad (mayor a menor)
		$sorted_switches = $this->get_sorted_switches();
		
		foreach ( $sorted_switches as $switch_id => $switch ) {
			if ( ! $switch['enabled'] ) {
				continue;
			}
			
			$result = $this->evaluate_switch( $switch_id, $context );
			
			// Si algún switch devuelve false, el convertidor no se muestra
			if ( false === $result ) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Obtiene switches ordenados por prioridad.
	 *
	 * @return array Switches ordenados.
	 */
	private function get_sorted_switches() {
		$switches = $this->registered_switches;
		uasort( $switches, function( $a, $b ) {
			return $b['priority'] - $a['priority'];
		});
		
		return $switches;
	}

	/**
	 * Evalúa un switch específico.
	 *
	 * @param string $switch_id ID del switch.
	 * @param array $context Contexto de evaluación.
	 * @return bool|null Resultado de la evaluación.
	 */
	private function evaluate_switch( $switch_id, $context ) {
		// Verificar cache
		$cache_key = $switch_id . '_' . md5( serialize( $context ) );
		if ( isset( $this->evaluation_cache[ $cache_key ] ) ) {
			return $this->evaluation_cache[ $cache_key ];
		}
		
		$switch = $this->registered_switches[ $switch_id ];
		$result = call_user_func( $switch['callback'], $context );
		
		// Cachear resultado por 5 minutos
		$this->evaluation_cache[ $cache_key ] = $result;
		
		return $result;
	}

	/**
	 * Evalúa el switch principal.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool True si está habilitado.
	 */
	public function evaluate_main_switch( $context ) {
		return $this->config['main_switch_enabled'];
	}

	/**
	 * Evalúa el switch por producto.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool|null True si habilitado, false si deshabilitado, null si no aplica.
	 */
	public function evaluate_product_switch( $context ) {
		$product_id = $context['product_id'] ?? get_the_ID();
		
		if ( ! $product_id || ! is_product() ) {
			return null;
		}
		
		$product_switch = get_post_meta( $product_id, '_wvs_converter_enabled', true );
		
		// Si no hay configuración específica, usar configuración por defecto
		if ( $product_switch === '' ) {
			return null;
		}
		
		return $product_switch === 'yes';
	}

	/**
	 * Evalúa el switch por categoría.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool|null True si habilitado, false si deshabilitado, null si no aplica.
	 */
	public function evaluate_category_switch( $context ) {
		$product_id = $context['product_id'] ?? get_the_ID();
		
		if ( ! $product_id ) {
			return null;
		}
		
		$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		
		if ( is_wp_error( $product_categories ) || empty( $product_categories ) ) {
			return null;
		}
		
		foreach ( $product_categories as $category_id ) {
			$category_switch = get_term_meta( $category_id, 'wvs_converter_enabled', true );
			
			if ( $category_switch !== '' ) {
				return $category_switch === 'yes';
			}
		}
		
		return null;
	}

	/**
	 * Evalúa el switch por página.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool|null True si habilitado, false si deshabilitado, null si no aplica.
	 */
	public function evaluate_page_switch( $context ) {
		$page_id = $context['page_id'] ?? get_the_ID();
		
		if ( ! $page_id ) {
			return null;
		}
		
		$page_switch = get_post_meta( $page_id, '_wvs_converter_enabled', true );
		
		if ( $page_switch === '' ) {
			return null;
		}
		
		return $page_switch === 'yes';
	}

	/**
	 * Evalúa el switch por usuario/rol.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool|null True si habilitado, false si deshabilitado, null si no aplica.
	 */
	public function evaluate_user_role_switch( $context ) {
		if ( ! is_user_logged_in() ) {
			$role = 'guest';
		} else {
			$user = wp_get_current_user();
			$role = $user->roles[0] ?? 'subscriber';
		}
		
		$role_switch = get_option( "wvs_converter_role_{$role}_enabled", '' );
		
		if ( $role_switch === '' ) {
			return null;
		}
		
		return $role_switch === 'yes';
	}

	/**
	 * Evalúa el switch por ubicación geográfica.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool|null True si habilitado, false si deshabilitado, null si no aplica.
	 */
	public function evaluate_location_switch( $context ) {
		$country_code = $this->detect_country_by_ip();
		
		$location_switch = get_option( "wvs_converter_location_{$country_code}_enabled", '' );
		
		if ( $location_switch === '' ) {
			return null;
		}
		
		return $location_switch === 'yes';
	}

	/**
	 * Evalúa el switch por dispositivo.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool|null True si habilitado, false si deshabilitado, null si no aplica.
	 */
	public function evaluate_device_switch( $context ) {
		$device_type = $this->detect_device_type();
		
		$device_switch = get_option( "wvs_converter_device_{$device_type}_enabled", '' );
		
		if ( $device_switch === '' ) {
			return null;
		}
		
		return $device_switch === 'yes';
	}

	/**
	 * Evalúa el switch por horario.
	 *
	 * @param array $context Contexto de evaluación.
	 * @return bool|null True si habilitado, false si deshabilitado, null si no aplica.
	 */
	public function evaluate_time_switch( $context ) {
		$current_hour = intval( current_time( 'H' ) );
		$current_day = intval( current_time( 'w' ) ); // 0 = Domingo, 6 = Sábado
		
		// Verificar horario de trabajo (Lunes a Viernes, 8:00 - 18:00)
		$work_hours_enabled = get_option( 'wvs_converter_work_hours_enabled', '' );
		if ( $work_hours_enabled === 'yes' ) {
			$is_work_hour = ( $current_day >= 1 && $current_day <= 5 ) && ( $current_hour >= 8 && $current_hour < 18 );
			return $is_work_hour;
		}
		
		// Verificar horario específico
		$start_hour = get_option( 'wvs_converter_start_hour', '' );
		$end_hour = get_option( 'wvs_converter_end_hour', '' );
		
		if ( $start_hour !== '' && $end_hour !== '' ) {
			$start = intval( $start_hour );
			$end = intval( $end_hour );
			
			if ( $start <= $end ) {
				return $current_hour >= $start && $current_hour < $end;
			} else {
				// Horario que cruza medianoche
				return $current_hour >= $start || $current_hour < $end;
			}
		}
		
		return null;
	}

	/**
	 * Detecta país por IP (simplificado).
	 *
	 * @return string Código de país.
	 */
	private function detect_country_by_ip() {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		
		// IPs de prueba para Venezuela
		$venezuela_ips = array( '190.169.0.0/16', '201.249.0.0/16' );
		
		foreach ( $venezuela_ips as $range ) {
			if ( $this->ip_in_range( $ip, $range ) ) {
				return 'VE';
			}
		}
		
		return 'US'; // Por defecto
	}

	/**
	 * Detecta tipo de dispositivo.
	 *
	 * @return string Tipo de dispositivo.
	 */
	private function detect_device_type() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		
		if ( preg_match( '/Mobile|Android|iPhone|iPad/', $user_agent ) ) {
			return 'mobile';
		} elseif ( preg_match( '/Tablet|iPad/', $user_agent ) ) {
			return 'tablet';
		} else {
			return 'desktop';
		}
	}

	/**
	 * Verifica si IP está en rango.
	 *
	 * @param string $ip IP a verificar.
	 * @param string $range Rango CIDR.
	 * @return bool True si está en rango.
	 */
	private function ip_in_range( $ip, $range ) {
		// Implementación simplificada
		return strpos( $ip, '190.169.' ) === 0 || strpos( $ip, '201.249.' ) === 0;
	}

	/**
	 * Registra un switch personalizado.
	 *
	 * @param string $switch_id ID único del switch.
	 * @param array $switch_config Configuración del switch.
	 * @return bool True si se registró correctamente.
	 */
	public function register_custom_switch( $switch_id, $switch_config ) {
		$required_keys = array( 'name', 'callback' );
		foreach ( $required_keys as $key ) {
			if ( ! isset( $switch_config[ $key ] ) ) {
				return false;
			}
		}
		
		$default_config = array(
			'description' => '',
			'enabled' => true,
			'priority' => 10,
		);
		
		$switch_config = array_merge( $default_config, $switch_config );
		$this->registered_switches[ $switch_id ] = $switch_config;
		
		return true;
	}

	/**
	 * Desregistra un switch personalizado.
	 *
	 * @param string $switch_id ID del switch.
	 * @return bool True si se desregistró correctamente.
	 */
	public function unregister_custom_switch( $switch_id ) {
		if ( ! isset( $this->registered_switches[ $switch_id ] ) ) {
			return false;
		}
		
		unset( $this->registered_switches[ $switch_id ] );
		
		return true;
	}

	/**
	 * Habilita/deshabilita un switch.
	 *
	 * @param string $switch_id ID del switch.
	 * @param bool $enabled Estado habilitado.
	 * @return bool True si se actualizó correctamente.
	 */
	public function toggle_switch( $switch_id, $enabled ) {
		if ( ! isset( $this->registered_switches[ $switch_id ] ) ) {
			return false;
		}
		
		$this->registered_switches[ $switch_id ]['enabled'] = $enabled;
		
		// Actualizar configuración en base de datos
		$config_key = 'wvs_converter_' . $switch_id . '_switch_enabled';
		update_option( $config_key, $enabled );
		
		return true;
	}

	/**
	 * Obtiene todos los switches registrados.
	 *
	 * @return array Switches registrados.
	 */
	public function get_registered_switches() {
		return $this->registered_switches;
	}

	/**
	 * Obtiene switches habilitados.
	 *
	 * @return array Switches habilitados.
	 */
	public function get_enabled_switches() {
		return array_filter( $this->registered_switches, function( $switch ) {
			return $switch['enabled'];
		});
	}

	/**
	 * Obtiene información de un switch específico.
	 *
	 * @param string $switch_id ID del switch.
	 * @return array|null Información del switch.
	 */
	public function get_switch_info( $switch_id ) {
		return $this->registered_switches[ $switch_id ] ?? null;
	}

	/**
	 * Verifica si un switch está habilitado.
	 *
	 * @param string $switch_id ID del switch.
	 * @return bool True si está habilitado.
	 */
	public function is_switch_enabled( $switch_id ) {
		return isset( $this->registered_switches[ $switch_id ] ) && 
			$this->registered_switches[ $switch_id ]['enabled'];
	}

	/**
	 * Obtiene estadísticas de switches.
	 *
	 * @return array Estadísticas de switches.
	 */
	public function get_switch_stats() {
		$stats = array(
			'total_switches' => count( $this->registered_switches ),
			'enabled_switches' => count( $this->get_enabled_switches() ),
			'disabled_switches' => count( $this->registered_switches ) - count( $this->get_enabled_switches() ),
			'custom_switches' => 0,
			'default_switches' => 0,
		);
		
		$default_switch_ids = array_keys( $this->config );
		
		foreach ( $this->registered_switches as $switch_id => $switch ) {
			if ( in_array( $switch_id . '_switch_enabled', $default_switch_ids ) ) {
				$stats['default_switches']++;
			} else {
				$stats['custom_switches']++;
			}
		}
		
		return $stats;
	}

	/**
	 * Limpia cache de evaluación.
	 *
	 * @return void
	 */
	public function clear_evaluation_cache() {
		$this->evaluation_cache = array();
	}

	/**
	 * Actualiza configuración de switches.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'main_switch_enabled',
			'product_switch_enabled',
			'category_switch_enabled',
			'page_switch_enabled',
			'user_role_switch_enabled',
			'location_switch_enabled',
			'device_switch_enabled',
			'time_switch_enabled',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_converter_' . $key, $value );
				$this->config[ $key ] = $value;
				
				// Actualizar switch correspondiente
				$switch_id = str_replace( '_switch_enabled', '', $key );
				if ( isset( $this->registered_switches[ $switch_id ] ) ) {
					$this->registered_switches[ $switch_id ]['enabled'] = $value;
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
