<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://artifexcodes.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Suite
 * @subpackage Woocommerce_Venezuela_Suite/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Venezuela_Suite
 * @subpackage Woocommerce_Venezuela_Suite/admin
 * @author     ronald alvarez <ronaldalv2025@gmail.com>
 */
class Woocommerce_Venezuela_Suite_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Venezuela_Suite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Venezuela_Suite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-venezuela-suite-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Venezuela_Suite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Venezuela_Suite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-venezuela-suite-admin.js', array( 'jquery' ), $this->version, false );

		// Localizar script con datos AJAX
		wp_localize_script( $this->plugin_name, 'wvs_admin_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wvs_admin_nonce' ),
			'strings' => array(
				'loading' => __( 'Cargando...', 'woocommerce-venezuela-suite' ),
				'error' => __( 'Error al procesar la solicitud', 'woocommerce-venezuela-suite' ),
				'success' => __( 'Operación completada exitosamente', 'woocommerce-venezuela-suite' ),
			),
		) );

	}

	/**
	 * Initialize AJAX handlers for admin functionality
	 */
	public function init_ajax_handlers() {
		// AJAX para actualizar tasa BCV manualmente
		add_action( 'wp_ajax_wvs_update_bcv_rate', array( $this, 'ajax_update_bcv_rate' ) );
		
		// AJAX para obtener métricas del dashboard
		add_action( 'wp_ajax_wvs_get_dashboard_metrics', array( $this, 'ajax_get_dashboard_metrics' ) );
		
		// AJAX para activar/desactivar módulos
		add_action( 'wp_ajax_wvs_toggle_module', array( $this, 'ajax_toggle_module' ) );
		
		// AJAX para obtener estado de módulos
		add_action( 'wp_ajax_wvs_get_module_status', array( $this, 'ajax_get_module_status' ) );
		
		// AJAX para probar conversión de moneda
		add_action( 'wp_ajax_wvs_test_currency_conversion', array( $this, 'ajax_test_currency_conversion' ) );
		
		// AJAX para obtener historial de tasas BCV
		add_action( 'wp_ajax_wvs_get_bcv_history', array( $this, 'ajax_get_bcv_history' ) );
		
		// AJAX para validar configuración fiscal
		add_action( 'wp_ajax_wvs_validate_fiscal_config', array( $this, 'ajax_validate_fiscal_config' ) );
		
		// AJAX para debug de módulos
		add_action( 'wp_ajax_wvs_debug_modules', array( $this, 'ajax_debug_modules' ) );
	}

	/**
	 * AJAX handler: Update BCV rate manually
	 */
	public function ajax_update_bcv_rate() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$rate = floatval( $_POST['rate'] );
		
		if ( $rate <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'La tasa debe ser mayor a 0', 'woocommerce-venezuela-suite' ) ) );
		}
		
		// Actualizar tasa manual
		update_option( 'wvs_bcv_rate_manual', $rate );
		update_option( 'wvs_bcv_rate_last_updated', current_time( 'timestamp' ) );
		
		// Actualizar cache si el módulo BCV está activo
		if ( class_exists( 'Woocommerce_Venezuela_Suite_BCV_Integration_Core' ) ) {
			$bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
			$bcv_core->set_manual_rate( $rate );
		}
		
		wp_send_json_success( array(
			'message' => __( 'Tasa BCV actualizada correctamente', 'woocommerce-venezuela-suite' ),
			'rate' => number_format( $rate, 2, ',', '.' ),
			'timestamp' => current_time( 'd/m/Y H:i' ),
		) );
	}

	/**
	 * AJAX handler: Get dashboard metrics
	 */
	public function ajax_get_dashboard_metrics() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$metrics = array();
		
		// Obtener tasa BCV actual
		$bcv_core = class_exists( 'Woocommerce_Venezuela_Suite_BCV_Integration_Core' ) ? 
			Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance() : null;
		
		$current_rate = $bcv_core ? $bcv_core->get_current_rate() : 0;
		$last_updated = get_option( 'wvs_bcv_rate_last_updated', current_time( 'timestamp' ) );
		
		$metrics['bcv_rate'] = array(
			'value' => $current_rate,
			'formatted' => number_format( $current_rate, 2, ',', '.' ),
			'last_updated' => human_time_diff( $last_updated, current_time( 'timestamp' ) ),
			'status' => $bcv_core ? 'active' : 'inactive',
		);
		
		// Obtener estado de módulos
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		$active_modules = $module_manager->get_loaded_modules();
		
		$metrics['modules'] = array(
			'active_count' => count( $active_modules ),
			'total_count' => 8,
			'active_list' => $active_modules,
		);
		
		// Obtener estado del sistema
		$metrics['system'] = array(
			'woocommerce' => class_exists( 'WooCommerce' ),
			'elementor' => class_exists( '\Elementor\Plugin' ),
			'bcv_tracker' => class_exists( 'BCV_Dolar_Tracker' ),
		);
		
		wp_send_json_success( $metrics );
	}

	/**
	 * AJAX handler: Toggle module activation
	 */
	public function ajax_toggle_module() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$module_slug = sanitize_text_field( $_POST['module'] );
		$action = sanitize_text_field( $_POST['action_type'] ); // 'activate' or 'deactivate'
		
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		
		if ( $action === 'activate' ) {
			$result = $module_manager->activate_module( $module_slug );
			$message = $result ? 
				__( 'Módulo activado correctamente', 'woocommerce-venezuela-suite' ) : 
				__( 'Error al activar el módulo', 'woocommerce-venezuela-suite' );
		} else {
			$result = $module_manager->deactivate_module( $module_slug );
			$message = $result ? 
				__( 'Módulo desactivado correctamente', 'woocommerce-venezuela-suite' ) : 
				__( 'Error al desactivar el módulo', 'woocommerce-venezuela-suite' );
		}
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => $message ) );
		} else {
			wp_send_json_error( array( 'message' => $message ) );
		}
	}

	/**
	 * AJAX handler: Get module status
	 */
	public function ajax_get_module_status() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		$active_modules = $module_manager->get_loaded_modules();
		
		$status = array();
		$all_modules = array( 'bcv-integration', 'currency-converter', 'payment-gateways', 'shipping-zones', 'fiscal-compliance' );
		
		foreach ( $all_modules as $module ) {
			$status[ $module ] = array(
				'active' => in_array( $module, $active_modules ),
				'state' => $module_manager->get_module_state( $module ),
			);
		}
		
		wp_send_json_success( $status );
	}

	/**
	 * AJAX handler: Test currency conversion
	 */
	public function ajax_test_currency_conversion() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$amount = floatval( $_POST['amount'] );
		$from_currency = sanitize_text_field( $_POST['from_currency'] );
		$to_currency = sanitize_text_field( $_POST['to_currency'] );
		
		if ( $amount <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'La cantidad debe ser mayor a 0', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$converter_core = class_exists( 'Woocommerce_Venezuela_Suite_Currency_Converter_Core' ) ? 
			Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance() : null;
		
		if ( ! $converter_core ) {
			wp_send_json_error( array( 'message' => __( 'Módulo Currency Converter no disponible', 'woocommerce-venezuela-suite' ) ) );
		}
		
		// Realizar conversión según las monedas
		if ( $from_currency === 'USD' && $to_currency === 'VES' ) {
			$result = $converter_core->convert_usd_to_ves( $amount );
		} elseif ( $from_currency === 'VES' && $to_currency === 'USD' ) {
			$result = $converter_core->convert_ves_to_usd( $amount );
		} else {
			wp_send_json_error( array( 'message' => __( 'Conversión no soportada', 'woocommerce-venezuela-suite' ) ) );
			return;
		}
		
		if ( $result !== null && $result > 0 ) {
			wp_send_json_success( array(
				'amount' => $amount,
				'from_currency' => $from_currency,
				'to_currency' => $to_currency,
				'result' => $result,
				'formatted_result' => $converter_core->format_ves_price( $result ),
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Error en la conversión de moneda', 'woocommerce-venezuela-suite' ) ) );
		}
	}

	/**
	 * AJAX handler: Get BCV rate history
	 */
	public function ajax_get_bcv_history() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$days = intval( $_POST['days'] );
		$days = max( 1, min( 30, $days ) ); // Limitar entre 1 y 30 días
		
		// Simular datos de historial (en implementación real, obtener de base de datos)
		$history = array();
		$current_rate = get_option( 'wvs_bcv_rate_manual', 36.50 );
		
		for ( $i = $days; $i >= 0; $i-- ) {
			$date = date( 'Y-m-d', strtotime( "-{$i} days" ) );
			$rate = $current_rate + ( rand( -200, 200 ) / 100 ); // Simular variación
			$rate = max( 1, $rate ); // Asegurar que sea positivo
			
			$history[] = array(
				'date' => $date,
				'rate' => round( $rate, 2 ),
				'formatted_rate' => number_format( $rate, 2, ',', '.' ),
			);
		}
		
		wp_send_json_success( array(
			'days' => $days,
			'history' => $history,
		) );
	}

	/**
	 * AJAX handler: Validate fiscal configuration
	 */
	public function ajax_validate_fiscal_config() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$igtf_enabled = intval( $_POST['igtf_enabled'] );
		$igtf_threshold = floatval( $_POST['igtf_threshold'] );
		$igtf_rate = floatval( $_POST['igtf_rate'] );
		
		$errors = array();
		$warnings = array();
		
		// Validaciones
		if ( $igtf_enabled ) {
			if ( $igtf_threshold <= 0 ) {
				$errors[] = __( 'El umbral IGTF debe ser mayor a 0', 'woocommerce-venezuela-suite' );
			}
			
			if ( $igtf_rate < 0 || $igtf_rate > 100 ) {
				$errors[] = __( 'La tasa IGTF debe estar entre 0 y 100%', 'woocommerce-venezuela-suite' );
			}
			
			if ( $igtf_threshold > 1000 ) {
				$warnings[] = __( 'Umbral IGTF muy alto, puede afectar las ventas', 'woocommerce-venezuela-suite' );
			}
		}
		
		$result = array(
			'valid' => empty( $errors ),
			'errors' => $errors,
			'warnings' => $warnings,
		);
		
		if ( empty( $errors ) ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Add main admin menu with submenus for each module.
	 */
	public function add_admin_menu() {
		// Menú principal
		add_menu_page(
			__( 'Venezuela Suite', 'woocommerce-venezuela-suite' ),
			__( 'Venezuela Suite', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-settings',
			array( $this, 'render_dashboard_page' ),
			'dashicons-admin-settings',
			56
		);

		// Submenús para cada módulo
		add_submenu_page(
			'wvs-settings',
			__( 'Dashboard', 'woocommerce-venezuela-suite' ),
			__( 'Dashboard', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-settings',
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			'wvs-settings',
			__( 'BCV Integration', 'woocommerce-venezuela-suite' ),
			__( 'BCV Integration', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-bcv-integration',
			array( $this, 'render_bcv_integration_page' )
		);

		add_submenu_page(
			'wvs-settings',
			__( 'Currency Converter', 'woocommerce-venezuela-suite' ),
			__( 'Currency Converter', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-currency-converter',
			array( $this, 'render_currency_converter_page' )
		);

		add_submenu_page(
			'wvs-settings',
			__( 'Payment Gateways', 'woocommerce-venezuela-suite' ),
			__( 'Payment Gateways', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-payment-gateways',
			array( $this, 'render_payment_gateways_page' )
		);

		add_submenu_page(
			'wvs-settings',
			__( 'Shipping Zones', 'woocommerce-venezuela-suite' ),
			__( 'Shipping Zones', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-shipping-zones',
			array( $this, 'render_shipping_zones_page' )
		);

		add_submenu_page(
			'wvs-settings',
			__( 'Fiscal Compliance', 'woocommerce-venezuela-suite' ),
			__( 'Fiscal Compliance', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-fiscal-compliance',
			array( $this, 'render_fiscal_compliance_page' )
		);

		add_submenu_page(
			'wvs-settings',
			__( 'Settings', 'woocommerce-venezuela-suite' ),
			__( 'Settings', 'woocommerce-venezuela-suite' ),
			'manage_woocommerce',
			'wvs-general-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Init settings: register settings, sections, fields.
	 */
	public function init_settings() {
		register_setting( 'wvs_settings_group', 'wvs_modules_states', array( $this, 'sanitize_modules_states' ) );
		register_setting( 'wvs_settings_group', 'wvs_bcv_rate_manual', array( $this, 'sanitize_bcv_rate' ) );
		register_setting( 'wvs_settings_group', 'wvs_igtf_enabled', array( $this, 'sanitize_igtf_enabled' ) );
		register_setting( 'wvs_settings_group', 'wvs_igtf_threshold', array( $this, 'sanitize_igtf_threshold' ) );
		register_setting( 'wvs_settings_group', 'wvs_igtf_rate', array( $this, 'sanitize_igtf_rate' ) );

		add_settings_section( 'wvs_section_modules', __( 'Módulos', 'woocommerce-venezuela-suite' ), '__return_false', 'wvs_settings_page' );
		add_settings_section( 'wvs_section_bcv', __( 'Integración BCV', 'woocommerce-venezuela-suite' ), '__return_false', 'wvs_settings_page' );
		add_settings_section( 'wvs_section_fiscal', __( 'Cumplimiento Fiscal', 'woocommerce-venezuela-suite' ), '__return_false', 'wvs_settings_page' );

		add_settings_field( 'wvs_field_module_bcv', __( 'BCV Integration', 'woocommerce-venezuela-suite' ), array( $this, 'field_module_bcv' ), 'wvs_settings_page', 'wvs_section_modules' );
		add_settings_field( 'wvs_field_module_converter', __( 'Currency Converter', 'woocommerce-venezuela-suite' ), array( $this, 'field_module_converter' ), 'wvs_settings_page', 'wvs_section_modules' );
		add_settings_field( 'wvs_field_module_shipping', __( 'Shipping Zones', 'woocommerce-venezuela-suite' ), array( $this, 'field_module_shipping' ), 'wvs_settings_page', 'wvs_section_modules' );
		add_settings_field( 'wvs_field_module_payment', __( 'Payment Gateways', 'woocommerce-venezuela-suite' ), array( $this, 'field_module_payment' ), 'wvs_settings_page', 'wvs_section_modules' );
		add_settings_field( 'wvs_field_module_fiscal', __( 'Fiscal Compliance', 'woocommerce-venezuela-suite' ), array( $this, 'field_module_fiscal' ), 'wvs_settings_page', 'wvs_section_modules' );
		
		add_settings_field( 'wvs_field_bcv_manual', __( 'Tasa manual BCV', 'woocommerce-venezuela-suite' ), array( $this, 'field_bcv_manual' ), 'wvs_settings_page', 'wvs_section_bcv' );
		
		add_settings_field( 'wvs_field_igtf_enabled', __( 'Habilitar IGTF', 'woocommerce-venezuela-suite' ), array( $this, 'field_igtf_enabled' ), 'wvs_settings_page', 'wvs_section_fiscal' );
		add_settings_field( 'wvs_field_igtf_threshold', __( 'Umbral IGTF (USD)', 'woocommerce-venezuela-suite' ), array( $this, 'field_igtf_threshold' ), 'wvs_settings_page', 'wvs_section_fiscal' );
		add_settings_field( 'wvs_field_igtf_rate', __( 'Tasa IGTF (%)', 'woocommerce-venezuela-suite' ), array( $this, 'field_igtf_rate' ), 'wvs_settings_page', 'wvs_section_fiscal' );
	}

	public function sanitize_modules_states( $value ) {
		return is_array( $value ) ? array_map( 'absint', $value ) : array();
	}

	public function sanitize_bcv_rate( $value ) {
		$value = str_replace( ',', '.', (string) $value );
		$value = (float) $value;
		return $value > 0 ? $value : '';
	}

	public function sanitize_igtf_enabled( $value ) {
		return (int) $value;
	}

	public function sanitize_igtf_threshold( $value ) {
		$value = str_replace( ',', '.', (string) $value );
		return max( 0, (float) $value );
	}

	public function sanitize_igtf_rate( $value ) {
		$value = str_replace( ',', '.', (string) $value );
		return max( 0, min( 100, (float) $value ) );
	}

	/**
	 * Field: toggle BCV module
	 */
	public function field_module_bcv() {
		$states = get_option( 'wvs_modules_states', array() );
		$checked = isset( $states['bcv-integration'] ) && (int) $states['bcv-integration'] === 1 ? 'checked' : '';
		echo '<label><input type="checkbox" name="wvs_modules_states[bcv-integration]" value="1" ' . $checked . ' /> ' . esc_html__( 'Activar', 'woocommerce-venezuela-suite' ) . '</label>';
	}

	/**
	 * Field: toggle Currency Converter
	 */
	public function field_module_converter() {
		$states = get_option( 'wvs_modules_states', array() );
		$checked = isset( $states['currency-converter'] ) && (int) $states['currency-converter'] === 1 ? 'checked' : '';
		echo '<label><input type="checkbox" name="wvs_modules_states[currency-converter]" value="1" ' . $checked . ' /> ' . esc_html__( 'Activar', 'woocommerce-venezuela-suite' ) . '</label>';
	}

	/**
	 * Field: toggle Shipping Zones
	 */
	public function field_module_shipping() {
		$states = get_option( 'wvs_modules_states', array() );
		$checked = isset( $states['shipping-zones'] ) && (int) $states['shipping-zones'] === 1 ? 'checked' : '';
		echo '<label><input type="checkbox" name="wvs_modules_states[shipping-zones]" value="1" ' . $checked . ' /> ' . esc_html__( 'Activar', 'woocommerce-venezuela-suite' ) . '</label>';
	}

	/**
	 * Field: toggle Payment Gateways
	 */
	public function field_module_payment() {
		$states = get_option( 'wvs_modules_states', array() );
		$checked = isset( $states['payment-gateways'] ) && (int) $states['payment-gateways'] === 1 ? 'checked' : '';
		echo '<label><input type="checkbox" name="wvs_modules_states[payment-gateways]" value="1" ' . $checked . ' /> ' . esc_html__( 'Activar', 'woocommerce-venezuela-suite' ) . '</label>';
	}

	/**
	 * Field: toggle Fiscal Compliance
	 */
	public function field_module_fiscal() {
		$states = get_option( 'wvs_modules_states', array() );
		$checked = isset( $states['fiscal-compliance'] ) && (int) $states['fiscal-compliance'] === 1 ? 'checked' : '';
		echo '<label><input type="checkbox" name="wvs_modules_states[fiscal-compliance]" value="1" ' . $checked . ' /> ' . esc_html__( 'Activar', 'woocommerce-venezuela-suite' ) . '</label>';
	}

	/**
	 * Field: manual BCV rate
	 */
	public function field_bcv_manual() {
		$value = get_option( 'wvs_bcv_rate_manual', '' );
		echo '<input type="number" step="0.01" name="wvs_bcv_rate_manual" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . esc_html__( 'Usada como respaldo cuando no haya tasa disponible.', 'woocommerce-venezuela-suite' ) . '</p>';
	}

	/**
	 * Field: IGTF enabled
	 */
	public function field_igtf_enabled() {
		$value = get_option( 'wvs_igtf_enabled', 1 );
		$checked = (int) $value === 1 ? 'checked' : '';
		echo '<label><input type="checkbox" name="wvs_igtf_enabled" value="1" ' . $checked . ' /> ' . esc_html__( 'Habilitar cálculo automático de IGTF', 'woocommerce-venezuela-suite' ) . '</label>';
	}

	/**
	 * Field: IGTF threshold
	 */
	public function field_igtf_threshold() {
		$value = get_option( 'wvs_igtf_threshold', 200 );
		echo '<input type="number" step="0.01" name="wvs_igtf_threshold" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . esc_html__( 'Umbral mínimo en USD para aplicar IGTF.', 'woocommerce-venezuela-suite' ) . '</p>';
	}

	/**
	 * Field: IGTF rate
	 */
	public function field_igtf_rate() {
		$value = get_option( 'wvs_igtf_rate', 3 );
		echo '<input type="number" step="0.01" min="0" max="100" name="wvs_igtf_rate" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . esc_html__( 'Porcentaje de IGTF a aplicar.', 'woocommerce-venezuela-suite' ) . '</p>';
	}

	/**
	 * Render dashboard page with real-time metrics
	 */
	public function render_dashboard_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) );
		}

		// Obtener métricas en tiempo real
		$bcv_core = class_exists( 'Woocommerce_Venezuela_Suite_BCV_Integration_Core' ) ? 
			Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance() : null;
		
		$current_rate = $bcv_core ? $bcv_core->get_current_rate() : 0;
		$last_updated = get_option( 'wvs_bcv_rate_last_updated', current_time( 'timestamp' ) );
		
		// Obtener estado de módulos
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		$active_modules = $module_manager->get_loaded_modules();
		
		?>
		<div class="wrap wvs-admin-wrap">
			<h1><?php esc_html_e( 'Dashboard - WooCommerce Venezuela Suite', 'woocommerce-venezuela-suite' ); ?></h1>
			
			<div class="wvs-dashboard-grid">
				<!-- Métricas principales -->
				<div class="wvs-dashboard-card">
					<h3><?php esc_html_e( 'Tasa BCV Actual', 'woocommerce-venezuela-suite' ); ?></h3>
					<div class="wvs-metric-value">
						<span class="rate-value"><?php echo esc_html( number_format( $current_rate, 2, ',', '.' ) ); ?></span>
						<span class="rate-currency">VES/USD</span>
					</div>
					<div class="wvs-metric-info">
						<?php esc_html_e( 'Última actualización:', 'woocommerce-venezuela-suite' ); ?>
						<?php echo esc_html( human_time_diff( $last_updated, current_time( 'timestamp' ) ) . ' ' . __( 'atrás', 'woocommerce-venezuela-suite' ) ); ?>
					</div>
				</div>

				<div class="wvs-dashboard-card">
					<h3><?php esc_html_e( 'Módulos Activos', 'woocommerce-venezuela-suite' ); ?></h3>
					<div class="wvs-metric-value">
						<span class="modules-count"><?php echo esc_html( count( $active_modules ) ); ?></span>
						<span class="modules-total">/ 8</span>
					</div>
					<div class="wvs-modules-list">
						<?php foreach ( $active_modules as $module ) : ?>
							<span class="wvs-module-badge active"><?php echo esc_html( $module ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="wvs-dashboard-card">
					<h3><?php esc_html_e( 'Estado del Sistema', 'woocommerce-venezuela-suite' ); ?></h3>
					<div class="wvs-system-status">
						<div class="status-item">
							<span class="status-label"><?php esc_html_e( 'BCV Integration:', 'woocommerce-venezuela-suite' ); ?></span>
							<span class="status-value <?php echo $bcv_core ? 'status-ok' : 'status-error'; ?>">
								<?php echo $bcv_core ? esc_html__( 'Activo', 'woocommerce-venezuela-suite' ) : esc_html__( 'Inactivo', 'woocommerce-venezuela-suite' ); ?>
							</span>
						</div>
						<div class="status-item">
							<span class="status-label"><?php esc_html_e( 'WooCommerce:', 'woocommerce-venezuela-suite' ); ?></span>
							<span class="status-value <?php echo class_exists( 'WooCommerce' ) ? 'status-ok' : 'status-error'; ?>">
								<?php echo class_exists( 'WooCommerce' ) ? esc_html__( 'Activo', 'woocommerce-venezuela-suite' ) : esc_html__( 'Inactivo', 'woocommerce-venezuela-suite' ); ?>
							</span>
						</div>
						<div class="status-item">
							<span class="status-label"><?php esc_html_e( 'Elementor:', 'woocommerce-venezuela-suite' ); ?></span>
							<span class="status-value <?php echo class_exists( '\Elementor\Plugin' ) ? 'status-ok' : 'status-warning'; ?>">
								<?php echo class_exists( '\Elementor\Plugin' ) ? esc_html__( 'Activo', 'woocommerce-venezuela-suite' ) : esc_html__( 'No instalado', 'woocommerce-venezuela-suite' ); ?>
							</span>
						</div>
					</div>
				</div>

				<!-- Acciones rápidas -->
				<div class="wvs-dashboard-card">
					<h3><?php esc_html_e( 'Acciones Rápidas', 'woocommerce-venezuela-suite' ); ?></h3>
					<div class="wvs-quick-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wvs-bcv-integration' ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'Configurar BCV', 'woocommerce-venezuela-suite' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wvs-currency-converter' ) ); ?>" class="button">
							<?php esc_html_e( 'Currency Converter', 'woocommerce-venezuela-suite' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wvs-general-settings' ) ); ?>" class="button">
							<?php esc_html_e( 'Configuración General', 'woocommerce-venezuela-suite' ); ?>
						</a>
						<button type="button" id="wvs-debug-modules" class="button">
							<?php esc_html_e( 'Debug Módulos', 'woocommerce-venezuela-suite' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Gráfico de tasa BCV (placeholder) -->
			<div class="wvs-dashboard-card wvs-chart-card">
				<h3><?php esc_html_e( 'Historial de Tasa BCV (Últimos 7 días)', 'woocommerce-venezuela-suite' ); ?></h3>
				<div class="wvs-chart-container">
					<div class="wvs-chart-placeholder">
						<?php esc_html_e( 'Gráfico de historial de tasas', 'woocommerce-venezuela-suite' ); ?>
					</div>
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Debug modules button
			$('#wvs-debug-modules').on('click', function() {
				$.post(ajaxurl, {
					action: 'wvs_debug_modules',
					nonce: '<?php echo wp_create_nonce( 'wvs_admin_nonce' ); ?>'
				}, function(response) {
					if (response.success) {
						alert('Debug Info:\n' + JSON.stringify(response.data, null, 2));
					} else {
						alert('Error: ' + response.data.message);
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Render BCV Integration page
	 */
	public function render_bcv_integration_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) );
		}

		$bcv_core = class_exists( 'Woocommerce_Venezuela_Suite_BCV_Integration_Core' ) ? 
			Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance() : null;
		
		?>
		<div class="wrap wvs-admin-wrap">
			<h1><?php esc_html_e( 'BCV Integration', 'woocommerce-venezuela-suite' ); ?></h1>
			
			<?php if ( $bcv_core ) : ?>
				<div class="wvs-module-status active">
					<h3><?php esc_html_e( 'Estado del Módulo', 'woocommerce-venezuela-suite' ); ?></h3>
					<p><?php esc_html_e( 'El módulo BCV Integration está activo y funcionando correctamente.', 'woocommerce-venezuela-suite' ); ?></p>
				</div>

				<div class="wvs-bcv-settings">
					<h3><?php esc_html_e( 'Configuración BCV', 'woocommerce-venezuela-suite' ); ?></h3>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'wvs_bcv_integration_settings' );
						do_settings_sections( 'wvs_bcv_integration' );
						submit_button( __( 'Guardar Configuración', 'woocommerce-venezuela-suite' ) );
						?>
					</form>
				</div>

				<div class="wvs-bcv-analytics">
					<h3><?php esc_html_e( 'Análisis y Métricas', 'woocommerce-venezuela-suite' ); ?></h3>
					<div class="wvs-analytics-grid">
						<div class="analytics-card">
							<h4><?php esc_html_e( 'Tasa Actual', 'woocommerce-venezuela-suite' ); ?></h4>
							<div class="analytics-value">
								<?php 
								$current_rate = $bcv_core->get_current_rate();
								echo esc_html( number_format( $current_rate, 2, ',', '.' ) . ' VES/USD' );
								?>
							</div>
						</div>
						<div class="analytics-card">
							<h4><?php esc_html_e( 'Última Actualización', 'woocommerce-venezuela-suite' ); ?></h4>
							<div class="analytics-value">
								<?php 
								$last_updated = get_option( 'wvs_bcv_rate_last_updated', current_time( 'timestamp' ) );
								echo esc_html( human_time_diff( $last_updated, current_time( 'timestamp' ) ) . ' ' . __( 'atrás', 'woocommerce-venezuela-suite' ) );
								?>
							</div>
						</div>
						<div class="analytics-card">
							<h4><?php esc_html_e( 'Fuente de Datos', 'woocommerce-venezuela-suite' ); ?></h4>
							<div class="analytics-value">
								<?php 
								$manual_rate = get_option( 'wvs_bcv_rate_manual', 0 );
								echo esc_html( $manual_rate > 0 ? __( 'Manual', 'woocommerce-venezuela-suite' ) : __( 'BCV Dólar Tracker', 'woocommerce-venezuela-suite' ) );
								?>
							</div>
						</div>
					</div>
				</div>
			<?php else : ?>
				<div class="wvs-module-status inactive">
					<h3><?php esc_html_e( 'Módulo Inactivo', 'woocommerce-venezuela-suite' ); ?></h3>
					<p><?php esc_html_e( 'El módulo BCV Integration no está activo. Actívalo desde la configuración general.', 'woocommerce-venezuela-suite' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wvs-general-settings' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Activar Módulo', 'woocommerce-venezuela-suite' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render Currency Converter page
	 */
	public function render_currency_converter_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) );
		}

		// Determinar estado por el gestor de módulos (no por existencia de clase)
		$module_manager = class_exists( 'Woocommerce_Venezuela_Suite_Module_Manager' )
			? Woocommerce_Venezuela_Suite_Module_Manager::get_instance()
			: null;
		$is_active = $module_manager
			? ( $module_manager->get_module_state( 'currency-converter' ) === 'active' || $module_manager->get_module_state( 'currency-converter' ) === 1 )
			: false;
		
		?>
		<div class="wrap wvs-admin-wrap">
			<h1><?php esc_html_e( 'Currency Converter', 'woocommerce-venezuela-suite' ); ?></h1>
			
		<?php if ( $is_active ) : ?>
				<div class="wvs-module-status active">
					<h3><?php esc_html_e( 'Estado del Módulo', 'woocommerce-venezuela-suite' ); ?></h3>
					<p><?php esc_html_e( 'El módulo Currency Converter está activo y funcionando correctamente.', 'woocommerce-venezuela-suite' ); ?></p>
				</div>

				<div class="wvs-converter-demo">
					<h3><?php esc_html_e( 'Demo del Convertidor', 'woocommerce-venezuela-suite' ); ?></h3>
					<div class="wvs-converter-widget-demo">
						<div class="converter-demo">
							<h4><?php esc_html_e( 'Convertidor USD ↔ VES', 'woocommerce-venezuela-suite' ); ?></h4>
							<p><?php esc_html_e( 'El convertidor está activo y funcionando correctamente.', 'woocommerce-venezuela-suite' ); ?></p>
							<p><strong><?php esc_html_e( 'Funciones disponibles:', 'woocommerce-venezuela-suite' ); ?></strong></p>
							<ul>
								<li><?php esc_html_e( 'Conversión USD a VES', 'woocommerce-venezuela-suite' ); ?></li>
								<li><?php esc_html_e( 'Conversión VES a USD', 'woocommerce-venezuela-suite' ); ?></li>
								<li><?php esc_html_e( 'Redondeo inteligente', 'woocommerce-venezuela-suite' ); ?></li>
								<li><?php esc_html_e( 'Formateo de precios', 'woocommerce-venezuela-suite' ); ?></li>
							</ul>
						</div>
					</div>
				</div>

				<div class="wvs-converter-settings">
					<h3><?php esc_html_e( 'Configuración del Convertidor', 'woocommerce-venezuela-suite' ); ?></h3>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'wvs_converter_settings' );
						do_settings_sections( 'wvs_converter_settings' );
						submit_button( __( 'Guardar Configuración', 'woocommerce-venezuela-suite' ) );
						?>
					</form>
				</div>
			<?php else : ?>
				<div class="wvs-module-status inactive">
					<h3><?php esc_html_e( 'Módulo Inactivo', 'woocommerce-venezuela-suite' ); ?></h3>
					<p><?php esc_html_e( 'El módulo Currency Converter no está activo. Actívalo desde la configuración general.', 'woocommerce-venezuela-suite' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wvs-general-settings' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Activar Módulo', 'woocommerce-venezuela-suite' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render Payment Gateways page
	 */
	public function render_payment_gateways_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) );
		}

		?>
		<div class="wrap wvs-admin-wrap">
			<h1><?php esc_html_e( 'Payment Gateways', 'woocommerce-venezuela-suite' ); ?></h1>
			
			<div class="wvs-payment-gateways-info">
				<h3><?php esc_html_e( 'Métodos de Pago Venezolanos', 'woocommerce-venezuela-suite' ); ?></h3>
				<p><?php esc_html_e( 'Configura los métodos de pago específicos para el mercado venezolano.', 'woocommerce-venezuela-suite' ); ?></p>
				
				<div class="wvs-gateways-list">
					<div class="gateway-item">
						<h4><?php esc_html_e( 'Pago Móvil', 'woocommerce-venezuela-suite' ); ?></h4>
						<p><?php esc_html_e( 'Método de pago móvil venezolano', 'woocommerce-venezuela-suite' ); ?></p>
						<span class="gateway-status"><?php esc_html_e( 'En desarrollo', 'woocommerce-venezuela-suite' ); ?></span>
					</div>
					<div class="gateway-item">
						<h4><?php esc_html_e( 'Transferencias Bancarias', 'woocommerce-venezuela-suite' ); ?></h4>
						<p><?php esc_html_e( 'Transferencias a bancos venezolanos', 'woocommerce-venezuela-suite' ); ?></p>
						<span class="gateway-status"><?php esc_html_e( 'En desarrollo', 'woocommerce-venezuela-suite' ); ?></span>
					</div>
					<div class="gateway-item">
						<h4><?php esc_html_e( 'Criptomonedas', 'woocommerce-venezuela-suite' ); ?></h4>
						<p><?php esc_html_e( 'Bitcoin, USDT y otras criptomonedas', 'woocommerce-venezuela-suite' ); ?></p>
						<span class="gateway-status"><?php esc_html_e( 'En desarrollo', 'woocommerce-venezuela-suite' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Shipping Zones page
	 */
	public function render_shipping_zones_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) );
		}

		?>
		<div class="wrap wvs-admin-wrap">
			<h1><?php esc_html_e( 'Shipping Zones', 'woocommerce-venezuela-suite' ); ?></h1>
			
			<div class="wvs-shipping-zones-info">
				<h3><?php esc_html_e( 'Zonas de Envío Venezolanas', 'woocommerce-venezuela-suite' ); ?></h3>
				<p><?php esc_html_e( 'Configura las zonas de envío específicas para Venezuela.', 'woocommerce-venezuela-suite' ); ?></p>
				
				<div class="wvs-zones-list">
					<div class="zone-item">
						<h4><?php esc_html_e( 'Caracas', 'woocommerce-venezuela-suite' ); ?></h4>
						<p><?php esc_html_e( 'Distrito Capital y área metropolitana', 'woocommerce-venezuela-suite' ); ?></p>
						<span class="zone-status"><?php esc_html_e( 'En desarrollo', 'woocommerce-venezuela-suite' ); ?></span>
					</div>
					<div class="zone-item">
						<h4><?php esc_html_e( 'Estados Principales', 'woocommerce-venezuela-suite' ); ?></h4>
						<p><?php esc_html_e( 'Zulia, Miranda, Carabobo, Lara', 'woocommerce-venezuela-suite' ); ?></p>
						<span class="zone-status"><?php esc_html_e( 'En desarrollo', 'woocommerce-venezuela-suite' ); ?></span>
					</div>
					<div class="zone-item">
						<h4><?php esc_html_e( 'Resto del País', 'woocommerce-venezuela-suite' ); ?></h4>
						<p><?php esc_html_e( 'Otros estados de Venezuela', 'woocommerce-venezuela-suite' ); ?></p>
						<span class="zone-status"><?php esc_html_e( 'En desarrollo', 'woocommerce-venezuela-suite' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Fiscal Compliance page
	 */
	public function render_fiscal_compliance_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) );
		}

		?>
		<div class="wrap wvs-admin-wrap">
			<h1><?php esc_html_e( 'Fiscal Compliance', 'woocommerce-venezuela-suite' ); ?></h1>
			
			<div class="wvs-fiscal-compliance-info">
				<h3><?php esc_html_e( 'Cumplimiento Fiscal SENIAT', 'woocommerce-venezuela-suite' ); ?></h3>
				<p><?php esc_html_e( 'Configuración para cumplimiento fiscal venezolano.', 'woocommerce-venezuela-suite' ); ?></p>
				
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wvs_settings_group' );
					do_settings_sections( 'wvs_settings_page' );
					submit_button( __( 'Guardar Configuración Fiscal', 'woocommerce-venezuela-suite' ) );
					?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render settings page (sin AJAX, Settings API)
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) );
		}
		settings_errors();
		echo '<div class="wrap wvs-admin-wrap">';
		echo '<h1>' . esc_html__( 'Configuración General - WooCommerce Venezuela Suite', 'woocommerce-venezuela-suite' ) . '</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'wvs_settings_group' );
		do_settings_sections( 'wvs_settings_page' );
		submit_button();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * AJAX handler: Debug modules
	 */
	public function ajax_debug_modules() {
		check_ajax_referer( 'wvs_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisos insuficientes', 'woocommerce-venezuela-suite' ) ) );
		}
		
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		$debug_info = $module_manager->debug_info();
		
		wp_send_json_success( $debug_info );
	}
}
