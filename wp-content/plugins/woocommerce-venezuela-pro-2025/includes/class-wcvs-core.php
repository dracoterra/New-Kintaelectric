<?php

/**
 * WooCommerce Venezuela Suite 2025 - Core Class
 *
 * Clase principal del plugin con arquitectura modular
 * y gestión de módulos activables/desactivables.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin class
 */
class WCVS_Core {

	/**
	 * Single instance of the class
	 *
	 * @var WCVS_Core
	 */
	private static $instance = null;

	/**
	 * Module manager instance
	 *
	 * @var WCVS_Module_Manager
	 */
	public $module_manager;

	/**
	 * Settings manager instance
	 *
	 * @var WCVS_Settings
	 */
	public $settings;

	/**
	 * Help system instance
	 *
	 * @var WCVS_Help
	 */
	public $help;

	/**
	 * Admin instance
	 *
	 * @var WCVS_Admin
	 */
	public $admin;

	/**
	 * Public instance
	 *
	 * @var WCVS_Public
	 */
	public $public;

	/**
	 * Logger instance
	 *
	 * @var WCVS_Logger
	 */
	public $logger;

	/**
	 * HPOS Compatibility instance
	 *
	 * @var WCVS_HPOS_Compatibility
	 */
	public $hpos_compatibility;

	/**
	 * Get single instance of the class
	 *
	 * @return WCVS_Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies() {
		// Core classes
		require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-module-manager.php';
		require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-settings.php';
		require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-help.php';
		require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-logger.php';
		require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-activator.php';
		require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-deactivator.php';

		// Admin and Public classes
		require_once WCVS_PLUGIN_DIR . 'admin/class-wcvs-admin.php';
		require_once WCVS_PLUGIN_DIR . 'public/class-wcvs-public.php';

		// Initialize instances
		$this->module_manager = new WCVS_Module_Manager();
		$this->settings = new WCVS_Settings();
		$this->help = new WCVS_Help();
		$this->logger = new WCVS_Logger();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp_loaded', array( $this, 'public_init' ) );
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		// Load text domain
		$this->load_textdomain();

		// Initialize HPOS compatibility
		$this->hpos_compatibility = new WCVS_HPOS_Compatibility();

		// Initialize admin
		if ( is_admin() ) {
			$this->admin = new WCVS_Admin();
		}

		// Initialize public
		$this->public = new WCVS_Public();

		// Register modules
		$this->register_core_modules();

		// Load active modules
		$this->load_active_modules();

		// Log initialization
		$this->logger->log( 'Plugin initialized successfully', 'info' );
	}

	/**
	 * Initialize admin functionality
	 */
	public function admin_init() {
		// Check for updates
		$this->check_for_updates();

		// Initialize admin settings
		if ( $this->admin ) {
			$this->admin->init();
		}
	}

	/**
	 * Initialize public functionality
	 */
	public function public_init() {
		// Initialize public functionality
		if ( $this->public ) {
			$this->public->init();
		}
	}

	/**
	 * Load text domain for internationalization
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'woocommerce-venezuela-pro-2025',
			false,
			dirname( WCVS_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Register core modules
	 */
	private function register_core_modules() {
		// Module 1: Sistema Fiscal Venezolano
		$this->module_manager->register_module( 'tax_system', array(
			'name' => __( 'Sistema Fiscal Venezolano', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'IVA dinámico, IGTF configurable y facturación electrónica', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Tax_System',
			'file' => 'modules/tax-system/class-wcvs-tax-system.php',
			'dependencies' => array(),
			'priority' => 10,
			'category' => 'core'
		));

		// Module 2: Pasarelas de Pago Locales
		$this->module_manager->register_module( 'payment_gateways', array(
			'name' => __( 'Pasarelas de Pago Locales', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'Pago Móvil, Zelle, Binance Pay y transferencias bancarias', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Payment_Gateways',
			'file' => 'modules/payment-gateways/class-wcvs-payment-gateways.php',
			'dependencies' => array(),
			'priority' => 20,
			'category' => 'core'
		));

		// Module 3: Gestor de Moneda Inteligente
		$this->module_manager->register_module( 'currency_manager', array(
			'name' => __( 'Gestor de Moneda Inteligente', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'Sistema dual USD/VES con actualización automática', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Currency_Manager',
			'file' => 'modules/currency-manager/class-wcvs-currency-manager.php',
			'dependencies' => array(),
			'priority' => 30,
			'category' => 'core'
		));

		// Module 4: Envíos Nacionales
		$this->module_manager->register_module( 'shipping_methods', array(
			'name' => __( 'Envíos Nacionales', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'MRW, Zoom, Tealca y delivery local', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Shipping_Methods',
			'file' => 'modules/shipping-methods/class-wcvs-shipping-methods.php',
			'dependencies' => array(),
			'priority' => 40,
			'category' => 'core'
		));

		// Module 5: Campos de Checkout Personalizados
		$this->module_manager->register_module( 'checkout_fields', array(
			'name' => __( 'Campos de Checkout Personalizados', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'Cédula, RIF y validaciones venezolanas', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Checkout_Fields',
			'file' => 'modules/checkout-fields/class-wcvs-checkout-fields.php',
			'dependencies' => array(),
			'priority' => 50,
			'category' => 'core'
		));

		// Module 6: Sistema de Facturación Electrónica
		$this->module_manager->register_module( 'electronic_billing', array(
			'name' => __( 'Sistema de Facturación Electrónica', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'Cumplimiento SENIAT con firma digital', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Electronic_Billing',
			'file' => 'modules/electronic-billing/class-wcvs-electronic-billing.php',
			'dependencies' => array( 'tax_system' ),
			'priority' => 60,
			'category' => 'core'
		));

		// Module 7: Notificaciones Avanzadas (Opcional)
		$this->module_manager->register_module( 'notifications', array(
			'name' => __( 'Notificaciones Avanzadas', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'WhatsApp, SMS y Telegram', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Notifications',
			'file' => 'modules/notifications/class-wcvs-notifications.php',
			'dependencies' => array(),
			'priority' => 70,
			'category' => 'optional'
		));

		// Module 8: Reportes y Analytics (Opcional)
		$this->module_manager->register_module( 'reports_analytics', array(
			'name' => __( 'Reportes y Analytics', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'Dashboard ejecutivo y reportes fiscales', 'woocommerce-venezuela-pro-2025' ),
			'version' => '1.0.0',
			'class' => 'WCVS_Reports_Analytics',
			'file' => 'modules/reports-analytics/class-wcvs-reports-analytics.php',
			'dependencies' => array(),
			'priority' => 80,
			'category' => 'optional'
		));
	}

	/**
	 * Load active modules
	 */
	private function load_active_modules() {
		$active_modules = $this->module_manager->get_active_modules();
		
		foreach ( $active_modules as $module_id => $module_config ) {
			$this->module_manager->load_module( $module_id );
		}
	}

	/**
	 * Check for plugin updates
	 */
	private function check_for_updates() {
		$current_version = get_option( 'wcvs_version', '0.0.0' );
		
		if ( version_compare( $current_version, WCVS_VERSION, '<' ) ) {
			$this->run_upgrade( $current_version, WCVS_VERSION );
			update_option( 'wcvs_version', WCVS_VERSION );
		}
	}

	/**
	 * Run upgrade process
	 *
	 * @param string $from_version
	 * @param string $to_version
	 */
	private function run_upgrade( $from_version, $to_version ) {
		// Log upgrade
		$this->logger->log( "Upgrading from {$from_version} to {$to_version}", 'info' );
		
		// Run upgrade tasks
		do_action( 'wcvs_upgrade', $from_version, $to_version );
	}

	/**
	 * Get module manager instance
	 *
	 * @return WCVS_Module_Manager
	 */
	public function get_module_manager() {
		return $this->module_manager;
	}

	/**
	 * Get settings instance
	 *
	 * @return WCVS_Settings
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Get help instance
	 *
	 * @return WCVS_Help
	 */
	public function get_help() {
		return $this->help;
	}

	/**
	 * Get logger instance
	 *
	 * @return WCVS_Logger
	 */
	public function get_logger() {
		return $this->logger;
	}
}
