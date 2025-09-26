<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://artifexcodes.com/
 * @since             1.0.0
 * @package           Woocommerce_Venezuela_Pro_2025
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Venezuela Pro 2025
 * Plugin URI:        https://https://artifexcodes.com/plugin
 * Description:       Plugin para tiendas Venezolanas.
 * Version:           1.0.0
 * Author:            ronald alvarez
 * Author URI:        https://https://artifexcodes.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-venezuela-pro-2025
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-venezuela-pro-2025-activator.php
 */
function activate_woocommerce_venezuela_pro_2025() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-pro-2025-activator.php';
	Woocommerce_Venezuela_Pro_2025_Activator::activate();
	
	// Initialize default modules
	$active_modules = get_option( 'wvp_active_modules', array() );
	if ( empty( $active_modules ) ) {
		$default_modules = array( 'currency_converter', 'payment_gateways', 'shipping_methods', 'tax_calculator' );
		update_option( 'wvp_active_modules', $default_modules );
	}
	
	// Initialize default currency modules
	$currency_modules = get_option( 'wvp_currency_modules', array() );
	if ( empty( $currency_modules ) ) {
		$default_currency_modules = array( 'visual_converter', 'button_converter', 'cart_converter' );
		update_option( 'wvp_currency_modules', $default_currency_modules );
	}
	
	// Set default BCV emergency rate
	if ( ! get_option( 'wvp_emergency_bcv_rate' ) ) {
		update_option( 'wvp_emergency_bcv_rate', 50 );
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-venezuela-pro-2025-deactivator.php
 */
function deactivate_woocommerce_venezuela_pro_2025() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-pro-2025-deactivator.php';
	Woocommerce_Venezuela_Pro_2025_Deactivator::deactivate();
}

/* DISABLED FOR DEBUGGING - Activation hooks
register_activation_hook( __FILE__, 'activate_woocommerce_venezuela_pro_2025' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_venezuela_pro_2025' );
*/

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
/* DISABLED FOR DEBUGGING - Core plugin class
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-pro-2025.php';
*/

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
/* DISABLED FOR DEBUGGING - Plugin execution
function run_woocommerce_venezuela_pro_2025() {
	return Woocommerce_Venezuela_Pro_2025::get_instance();
}
*/

/**
 * Initialize the plugin functionality - Simplified version
 * Only loads essential Venezuelan WooCommerce features to avoid memory issues
 */
function wvp_init_plugin() {
	// Verificar que WooCommerce esté activo
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	
	// =============================================================
	// FUNCIONALIDADES ESENCIALES - ACTIVAS
	// =============================================================
	
	// 1. PAYMENT GATEWAYS VENEZOLANOS
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-pago-movil-gateway.php';
		if ( class_exists( 'WVP_Pago_Movil_Gateway' ) ) {
			add_filter( 'woocommerce_payment_gateways', 'wvp_add_pago_movil_gateway' );
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Pago Móvil Gateway error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-zelle-gateway.php';
		if ( class_exists( 'WVP_Zelle_Gateway' ) ) {
			add_filter( 'woocommerce_payment_gateways', 'wvp_add_zelle_gateway' );
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Zelle Gateway error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-bank-transfer-gateway.php';
		if ( class_exists( 'WVP_Bank_Transfer_Gateway' ) ) {
			add_filter( 'woocommerce_payment_gateways', 'wvp_add_bank_transfer_gateway' );
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Bank Transfer Gateway error: ' . $e->getMessage() );
	}
	
	// 2. SHIPPING METHODS VENEZOLANOS
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-mrw-shipping.php';
		if ( class_exists( 'WVP_MRW_Shipping' ) ) {
			add_action( 'woocommerce_shipping_init', 'wvp_init_mrw_shipping' );
			add_filter( 'woocommerce_shipping_methods', 'wvp_add_mrw_shipping_method' );
		}
	} catch ( Exception $e ) {
		error_log( 'WVP MRW Shipping error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-zoom-shipping.php';
		if ( class_exists( 'WVP_Zoom_Shipping' ) ) {
			add_action( 'woocommerce_shipping_init', 'wvp_init_zoom_shipping' );
			add_filter( 'woocommerce_shipping_methods', 'wvp_add_zoom_shipping_method' );
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Zoom Shipping error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-local-delivery-shipping.php';
		if ( class_exists( 'WVP_Local_Delivery_Shipping' ) ) {
			add_action( 'woocommerce_shipping_init', 'wvp_init_local_delivery_shipping' );
			add_filter( 'woocommerce_shipping_methods', 'wvp_add_local_delivery_shipping_method' );
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Local Delivery Shipping error: ' . $e->getMessage() );
	}
	
	// 3. SENIAT EXPORTER
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-seniat-exporter.php';
		if ( class_exists( 'WVP_SENIAT_Exporter' ) ) {
			WVP_SENIAT_Exporter::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP SENIAT Exporter error: ' . $e->getMessage() );
	}
	
	// 4. ADMIN DASHBOARD BÁSICO
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-admin-dashboard.php';
		if ( class_exists( 'WVP_Admin_Dashboard' ) ) {
			WVP_Admin_Dashboard::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Admin Dashboard error: ' . $e->getMessage() );
	}
	
	// =============================================================
	// FUNCIONALIDADES COMENTADAS TEMPORALMENTE
	// Reactivar una por una para identificar problemas
	// =============================================================
	
	// STEP 1 - CURRENCY CONVERTER MODULES - REACTIVADO
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-currency-modules-manager.php';
		if ( class_exists( 'WVP_Currency_Modules_Manager' ) ) {
			WVP_Currency_Modules_Manager::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Currency Modules Manager error: ' . $e->getMessage() );
	}
	
	// STEP 2 - VENEZUELAN TAXES - REACTIVADO
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-venezuelan-taxes.php';
		if ( class_exists( 'WVP_Venezuelan_Taxes' ) ) {
			WVP_Venezuelan_Taxes::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Venezuelan Taxes error: ' . $e->getMessage() );
	}
	
	/* STEP 3 - VENEZUELAN SHIPPING MANAGER
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-venezuelan-shipping.php';
		if ( class_exists( 'WVP_Venezuelan_Shipping' ) ) {
			WVP_Venezuelan_Shipping::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Venezuelan Shipping error: ' . $e->getMessage() );
	}
	*/
	
	// STEP 4 - PRODUCT DISPLAY - REACTIVADO
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-product-display.php';
		if ( class_exists( 'WVP_Product_Display' ) ) {
			WVP_Product_Display::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Product Display error: ' . $e->getMessage() );
	}
	
	/* STEP 5 - OPTIMIZATION SYSTEMS
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-cache-manager.php';
		if ( class_exists( 'WVP_Cache_Manager' ) ) {
			WVP_Cache_Manager::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Cache Manager error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-database-optimizer.php';
		if ( class_exists( 'WVP_Database_Optimizer' ) ) {
			WVP_Database_Optimizer::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Database Optimizer error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-assets-optimizer.php';
		if ( class_exists( 'WVP_Assets_Optimizer' ) ) {
			WVP_Assets_Optimizer::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Assets Optimizer error: ' . $e->getMessage() );
	}
	*/
	
	/* STEP 6 - SECURITY SYSTEMS
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-security-manager.php';
		if ( class_exists( 'WVP_Security_Manager' ) ) {
			WVP_Security_Manager::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Security Manager error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-venezuelan-validator.php';
		if ( class_exists( 'WVP_Venezuelan_Validator' ) ) {
			WVP_Venezuelan_Validator::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Venezuelan Validator error: ' . $e->getMessage() );
	}
	*/
	
	// STEP 7 - ADDITIONAL SYSTEMS - REACTIVADO
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-setup-wizard.php';
		if ( class_exists( 'WVP_Setup_Wizard' ) ) {
			WVP_Setup_Wizard::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Setup Wizard error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-notification-system.php';
		if ( class_exists( 'WVP_Notification_System' ) ) {
			WVP_Notification_System::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Notification System error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-analytics-dashboard.php';
		if ( class_exists( 'WVP_Analytics_Dashboard' ) ) {
			WVP_Analytics_Dashboard::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Analytics Dashboard error: ' . $e->getMessage() );
	}
	
	/* STEP 8 - FINAL SYSTEMS
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-final-optimizer.php';
		if ( class_exists( 'WVP_Final_Optimizer' ) ) {
			WVP_Final_Optimizer::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Final Optimizer error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-testing-suite.php';
		if ( class_exists( 'WVP_Testing_Suite' ) ) {
			WVP_Testing_Suite::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Testing Suite error: ' . $e->getMessage() );
	}
	
	try {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-documentation-generator.php';
		if ( class_exists( 'WVP_Documentation_Generator' ) ) {
			WVP_Documentation_Generator::get_instance();
		}
	} catch ( Exception $e ) {
		error_log( 'WVP Documentation Generator error: ' . $e->getMessage() );
	}
	*/
}
// Initialize the plugin - SIMPLIFIED VERSION ACTIVE
add_action( 'plugins_loaded', 'wvp_init_plugin' );

/**
 * Add Pago Móvil gateway to WooCommerce
 */
function wvp_add_pago_movil_gateway( $gateways ) {
	$gateways[] = 'WVP_Pago_Movil_Gateway';
	return $gateways;
}

/**
 * Add Zelle gateway to WooCommerce
 */
function wvp_add_zelle_gateway( $gateways ) {
	$gateways[] = 'WVP_Zelle_Gateway';
	return $gateways;
}

/**
 * Add Bank Transfer gateway to WooCommerce
 */
function wvp_add_bank_transfer_gateway( $gateways ) {
	$gateways[] = 'WVP_Bank_Transfer_Gateway';
	return $gateways;
}

/**
 * Initialize MRW Shipping
 */
function wvp_init_mrw_shipping() {
	// Class already loaded
}

/**
 * Add MRW Shipping method to WooCommerce
 */
function wvp_add_mrw_shipping_method( $methods ) {
	$methods['wvp_mrw'] = 'WVP_MRW_Shipping';
	return $methods;
}

/**
 * Initialize Zoom Shipping
 */
function wvp_init_zoom_shipping() {
	// Class already loaded
}

/**
 * Add Zoom Shipping method to WooCommerce
 */
function wvp_add_zoom_shipping_method( $methods ) {
	$methods['wvp_zoom'] = 'WVP_Zoom_Shipping';
	return $methods;
}

/**
 * Initialize Local Delivery Shipping
 */
function wvp_init_local_delivery_shipping() {
	// Class already loaded
}

/**
 * Add Local Delivery Shipping method to WooCommerce
 */
function wvp_add_local_delivery_shipping_method( $methods ) {
	$methods['wvp_local_delivery'] = 'WVP_Local_Delivery_Shipping';
	return $methods;
}

// Initialize the plugin - Only basic functionality for debugging
// run_woocommerce_venezuela_pro_2025();
