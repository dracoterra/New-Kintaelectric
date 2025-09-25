<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://kintaelectric.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 * @author     Kinta Electric <info@kintaelectric.com>
 */
class Woocommerce_Venezuela_Pro_2025 {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocommerce_Venezuela_Pro_2025_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The dependency injection container.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WVP_Dependency_Container    $container    Manages dependencies.
	 */
	protected $container;

	/**
	 * The module manager.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WVP_Module_Manager    $module_manager    Manages plugin modules.
	 */
	protected $module_manager;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION' ) ) {
			$this->version = WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommerce-venezuela-pro-2025';

		$this->load_dependencies();
		$this->init_container();
		$this->init_module_manager();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_woocommerce_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_Venezuela_Pro_2025_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_Venezuela_Pro_2025_i18n. Defines internationalization functionality.
	 * - Woocommerce_Venezuela_Pro_2025_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_Venezuela_Pro_2025_Public. Defines all hooks for the public-facing side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-venezuela-pro-2025-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-venezuela-pro-2025-i18n.php';

		/**
		 * The class responsible for dependency injection.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wvp-dependency-container.php';

		/**
		 * The class responsible for module management.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wvp-module-manager.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-venezuela-pro-2025-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-venezuela-pro-2025-public.php';

		$this->loader = new Woocommerce_Venezuela_Pro_2025_Loader();

	}

	/**
	 * Initialize the dependency injection container.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_container() {
		$this->container = new WVP_Dependency_Container();
		
		// Register core dependencies
		$this->container->register( 'plugin_name', $this->plugin_name );
		$this->container->register( 'version', $this->version );
		$this->container->register( 'loader', $this->loader );
	}

	/**
	 * Initialize the module manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_module_manager() {
		$this->module_manager = new WVP_Module_Manager( $this->container );
		$this->container->register( 'module_manager', $this->module_manager );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_Venezuela_Pro_2025_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommerce_Venezuela_Pro_2025_i18n();

		$this->loader->add_action( 'wp_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woocommerce_Venezuela_Pro_2025_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocommerce_Venezuela_Pro_2025_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to WooCommerce functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_woocommerce_hooks() {
		// Only register WooCommerce hooks if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Hook into WooCommerce initialization
		$this->loader->add_action( 'woocommerce_init', $this, 'init_woocommerce_features' );
		
		// Hook into WooCommerce loaded
		$this->loader->add_action( 'woocommerce_loaded', $this, 'on_woocommerce_loaded' );
	}

	/**
	 * Initialize WooCommerce-specific features.
	 *
	 * @since    1.0.0
	 */
	public function init_woocommerce_features() {
		// Initialize modules that depend on WooCommerce
		$this->module_manager->init_woocommerce_modules();
		
		// Debug: Log module initialization
		error_log( 'WVP: Initializing WooCommerce features' );
		error_log( 'WVP: Active modules: ' . print_r( $this->module_manager->get_active_modules(), true ) );
	}

	/**
	 * Handle WooCommerce loaded event.
	 *
	 * @since    1.0.0
	 */
	public function on_woocommerce_loaded() {
		// Perform any initialization that requires WooCommerce to be fully loaded
		do_action( 'wvp_woocommerce_loaded' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_Venezuela_Pro_2025_Loader    Orchestrates hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the dependency injection container.
	 *
	 * @since     1.0.0
	 * @return    WVP_Dependency_Container    The dependency container.
	 */
	public function get_container() {
		return $this->container;
	}

	/**
	 * Get the module manager.
	 *
	 * @since     1.0.0
	 * @return    WVP_Module_Manager    The module manager.
	 */
	public function get_module_manager() {
		return $this->module_manager;
	}

}