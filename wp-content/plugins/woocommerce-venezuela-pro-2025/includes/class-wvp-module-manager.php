<?php

/**
 * Module Manager
 *
 * Manages plugin modules and their activation/deactivation.
 *
 * @link       https://kintaelectric.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 */

/**
 * Module manager class.
 *
 * This class manages plugin modules, their activation, deactivation,
 * and dependencies.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 * @author     Kinta Electric <info@kintaelectric.com>
 */
class WVP_Module_Manager {

	/**
	 * The dependency injection container.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WVP_Dependency_Container    $container    The dependency container.
	 */
	protected $container;

	/**
	 * Available modules.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $available_modules    Array of available modules.
	 */
	protected $available_modules = array();

	/**
	 * Active modules.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $active_modules    Array of active modules.
	 */
	protected $active_modules = array();

	/**
	 * Initialize the module manager.
	 *
	 * @since    1.0.0
	 * @param    WVP_Dependency_Container    $container    The dependency container.
	 */
	public function __construct( $container ) {
		$this->container = $container;
		$this->init_available_modules();
		$this->load_active_modules();
	}

	/**
	 * Initialize available modules.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_available_modules() {
		$this->available_modules = array(
			'currency_converter' => array(
				'name' => 'Currency Converter',
				'description' => 'Converts USD prices to VES using BCV rates',
				'class' => 'WVP_Currency_Converter',
				'file' => 'modules/class-wvp-currency-converter.php',
				'dependencies' => array(),
				'woocommerce_required' => true,
			),
			'payment_gateways' => array(
				'name' => 'Payment Gateways',
				'description' => 'Venezuelan payment gateways (Pago Móvil, Zelle, etc.)',
				'class' => 'WVP_Payment_Gateways',
				'file' => 'modules/class-wvp-payment-gateways.php',
				'dependencies' => array(),
				'woocommerce_required' => true,
			),
			'shipping_methods' => array(
				'name' => 'Shipping Methods',
				'description' => 'Venezuelan shipping methods and zones',
				'class' => 'WVP_Shipping_Methods',
				'file' => 'modules/class-wvp-shipping-methods.php',
				'dependencies' => array(),
				'woocommerce_required' => true,
			),
			'tax_calculator' => array(
				'name' => 'Tax Calculator',
				'description' => 'Calculates Venezuelan taxes (IVA, IGTF)',
				'class' => 'WVP_Tax_Calculator',
				'file' => 'modules/class-wvp-tax-calculator.php',
				'dependencies' => array(),
				'woocommerce_required' => true,
			),
			'order_processor' => array(
				'name' => 'Order Processor',
				'description' => 'Processes orders with Venezuelan requirements',
				'class' => 'WVP_Order_Processor',
				'file' => 'modules/class-wvp-order-processor.php',
				'dependencies' => array( 'currency_converter', 'tax_calculator' ),
				'woocommerce_required' => true,
			),
		);
	}

	/**
	 * Load active modules from database.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_active_modules() {
		$active_modules = get_option( 'wvp_active_modules', array() );
		
		// If no modules are set, activate default modules
		if ( empty( $active_modules ) ) {
			$active_modules = array_keys( $this->available_modules );
			update_option( 'wvp_active_modules', $active_modules );
		}
		
		$this->active_modules = $active_modules;
	}

	/**
	 * Initialize WooCommerce-specific modules.
	 *
	 * @since    1.0.0
	 */
	public function init_woocommerce_modules() {
		foreach ( $this->active_modules as $module_id ) {
			if ( isset( $this->available_modules[ $module_id ] ) ) {
				$module = $this->available_modules[ $module_id ];
				
				// Check if WooCommerce is required and available
				if ( $module['woocommerce_required'] && ! class_exists( 'WooCommerce' ) ) {
					continue;
				}
				
				$this->load_module( $module_id );
			}
		}
	}

	/**
	 * Load a specific module.
	 *
	 * @since    1.0.0
	 * @param    string    $module_id    The module ID.
	 * @return   boolean                 True if module loaded successfully, false otherwise.
	 */
	public function load_module( $module_id ) {
		if ( ! isset( $this->available_modules[ $module_id ] ) ) {
			return false;
		}

		$module = $this->available_modules[ $module_id ];
		
		// Check dependencies
		if ( ! $this->check_dependencies( $module['dependencies'] ) ) {
			return false;
		}

		// Load module file
		$file_path = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/' . $module['file'];
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
			
			// Initialize module if class exists
			if ( class_exists( $module['class'] ) ) {
				$module_instance = new $module['class']( $this->container );
				$this->container->register( $module_id, $module_instance );
				
				// Initialize module if it has an init method
				if ( method_exists( $module_instance, 'init' ) ) {
					$module_instance->init();
				}
				
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if module dependencies are met.
	 *
	 * @since    1.0.0
	 * @param    array    $dependencies    Array of dependency module IDs.
	 * @return   boolean                   True if all dependencies are met, false otherwise.
	 */
	private function check_dependencies( $dependencies ) {
		foreach ( $dependencies as $dependency ) {
			if ( ! in_array( $dependency, $this->active_modules, true ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Activate a module.
	 *
	 * @since    1.0.0
	 * @param    string    $module_id    The module ID.
	 * @return   boolean                 True if module activated successfully, false otherwise.
	 */
	public function activate_module( $module_id ) {
		if ( ! isset( $this->available_modules[ $module_id ] ) ) {
			return false;
		}

		if ( ! in_array( $module_id, $this->active_modules, true ) ) {
			$this->active_modules[] = $module_id;
			update_option( 'wvp_active_modules', $this->active_modules );
			
			// Load the module if WooCommerce is available
			if ( class_exists( 'WooCommerce' ) ) {
				$this->load_module( $module_id );
			}
		}

		return true;
	}

	/**
	 * Deactivate a module.
	 *
	 * @since    1.0.0
	 * @param    string    $module_id    The module ID.
	 * @return   boolean                 True if module deactivated successfully, false otherwise.
	 */
	public function deactivate_module( $module_id ) {
		$key = array_search( $module_id, $this->active_modules, true );
		if ( $key !== false ) {
			unset( $this->active_modules[ $key ] );
			$this->active_modules = array_values( $this->active_modules );
			update_option( 'wvp_active_modules', $this->active_modules );
			
			// Remove from container
			$this->container->remove( $module_id );
		}

		return true;
	}

	/**
	 * Get available modules.
	 *
	 * @since    1.0.0
	 * @return   array    Array of available modules.
	 */
	public function get_available_modules() {
		return $this->available_modules;
	}

	/**
	 * Get active modules.
	 *
	 * @since    1.0.0
	 * @return   array    Array of active module IDs.
	 */
	public function get_active_modules() {
		return $this->active_modules;
	}

	/**
	 * Check if a module is active.
	 *
	 * @since    1.0.0
	 * @param    string    $module_id    The module ID.
	 * @return   boolean                 True if module is active, false otherwise.
	 */
	public function is_module_active( $module_id ) {
		return in_array( $module_id, $this->active_modules, true );
	}

}
