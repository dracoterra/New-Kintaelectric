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

register_activation_hook( __FILE__, 'activate_woocommerce_venezuela_pro_2025' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_venezuela_pro_2025' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-pro-2025.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_venezuela_pro_2025() {
	return Woocommerce_Venezuela_Pro_2025::get_instance();
}

/**
 * Initialize the plugin functionality - Debug version
 */
function wvp_init_plugin() {
	if ( class_exists( 'WooCommerce' ) ) {
		// Initialize the currency converter - REACTIVANDO CON ARCHIVO LIMPIADO
		// Load Currency Modules Manager
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-currency-modules-manager.php';
			if ( class_exists( 'WVP_Currency_Modules_Manager' ) ) {
				WVP_Currency_Modules_Manager::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Currency Modules Manager error: ' . $e->getMessage() );
		}
		
		// Load individual currency modules
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-wvp-visual-currency-converter.php';
			if ( class_exists( 'WVP_Visual_Currency_Converter' ) ) {
				WVP_Visual_Currency_Converter::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Visual Currency Converter error: ' . $e->getMessage() );
		}
		
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-wvp-button-currency-converter.php';
			if ( class_exists( 'WVP_Button_Currency_Converter' ) ) {
				WVP_Button_Currency_Converter::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Button Currency Converter error: ' . $e->getMessage() );
		}
		
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-wvp-cart-currency-converter.php';
			if ( class_exists( 'WVP_Cart_Currency_Converter' ) ) {
				WVP_Cart_Currency_Converter::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Cart Currency Converter error: ' . $e->getMessage() );
		}
		
		// Load classes one by one to identify the problematic one
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-venezuelan-taxes.php';
			if ( class_exists( 'WVP_Venezuelan_Taxes' ) ) {
				WVP_Venezuelan_Taxes::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Venezuelan Taxes error: ' . $e->getMessage() );
		}
		
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-admin-dashboard.php';
			if ( class_exists( 'WVP_Admin_Dashboard' ) ) {
				WVP_Admin_Dashboard::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Admin Dashboard error: ' . $e->getMessage() );
		}
		
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-pago-movil-gateway.php';
			if ( class_exists( 'WVP_Pago_Movil_Gateway' ) ) {
				add_filter( 'woocommerce_payment_gateways', 'wvp_add_pago_movil_gateway' );
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Pago Móvil Gateway error: ' . $e->getMessage() );
		}
		
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-venezuelan-shipping.php';
			if ( class_exists( 'WVP_Venezuelan_Shipping' ) ) {
				WVP_Venezuelan_Shipping::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Venezuelan Shipping error: ' . $e->getMessage() );
		}
		
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-product-display.php';
			if ( class_exists( 'WVP_Product_Display' ) ) {
				WVP_Product_Display::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP Product Display error: ' . $e->getMessage() );
		}
		
		try {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-seniat-exporter.php';
			if ( class_exists( 'WVP_SENIAT_Exporter' ) ) {
				WVP_SENIAT_Exporter::get_instance();
			}
		} catch ( Exception $e ) {
			error_log( 'WVP SENIAT Exporter error: ' . $e->getMessage() );
		}
	}
}
add_action( 'plugins_loaded', 'wvp_init_plugin' );

/**
 * Add Pago Móvil gateway to WooCommerce
 */
function wvp_add_pago_movil_gateway( $gateways ) {
	$gateways[] = 'WVP_Pago_Movil_Gateway';
	return $gateways;
}

// Initialize the plugin - Only basic functionality for debugging
// run_woocommerce_venezuela_pro_2025();
