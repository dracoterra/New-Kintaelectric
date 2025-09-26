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
 * Simple currency converter class
 */
class WVP_Simple_Currency_Converter {
	
	private static $instance = null;
	private $bcv_rate = 36.5; // Default rate
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->init_hooks();
	}
	
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_single_product_summary', array( $this, 'add_currency_switcher' ), 25 );
		add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
	}
	
	public function enqueue_scripts() {
		if ( is_product() ) {
			wp_enqueue_style( 'wvp-simple-converter', plugin_dir_url( __FILE__ ) . 'public/css/wvp-simple-converter.css', array(), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION );
			wp_enqueue_script( 'wvp-simple-converter', plugin_dir_url( __FILE__ ) . 'public/js/wvp-simple-converter.js', array( 'jquery' ), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION, true );
			wp_localize_script( 'wvp-simple-converter', 'wvp_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvp_convert_price' ),
				'rate' => $this->bcv_rate
			));
		}
	}
	
	public function add_currency_switcher() {
		static $rendered = false;
		if ( $rendered ) return;
		$rendered = true;
		
		global $product;
		if ( ! $product ) return;
		
		$price_usd = $product->get_price();
		$price_ves = $price_usd * $this->bcv_rate;
		
		echo '<div class="wvp-currency-switcher">';
		echo '<h4>Seleccionar Moneda:</h4>';
		echo '<button class="wvp-currency-btn active" data-currency="USD">USD $' . number_format( $price_usd, 2 ) . '</button>';
		echo '<button class="wvp-currency-btn" data-currency="VES">VES ' . number_format( $price_ves, 2 ) . '</button>';
		echo '</div>';
	}
	
	public function ajax_convert_price() {
		check_ajax_referer( 'wvp_convert_price', 'nonce' );
		
		$product_id = intval( $_POST['product_id'] );
		$currency = sanitize_text_field( $_POST['currency'] );
		
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_die( 'Product not found' );
		}
		
		$price_usd = $product->get_price();
		
		if ( $currency === 'VES' ) {
			$price = $price_usd * $this->bcv_rate;
			$formatted = 'VES ' . number_format( $price, 2 );
		} else {
			$price = $price_usd;
			$formatted = 'USD $' . number_format( $price, 2 );
		}
		
		wp_send_json_success( array(
			'price' => $price,
			'formatted' => $formatted
		));
	}
}

/**
 * Initialize the plugin functionality - Debug version
 */
function wvp_init_plugin() {
	if ( class_exists( 'WooCommerce' ) ) {
        // Initialize the currency converter - DISABLED FOR NOW
        /*
        try {
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-wvp-simple-currency-converter.php';
            if ( class_exists( 'WVP_Simple_Currency_Converter' ) ) {
                WVP_Simple_Currency_Converter::get_instance();
            }
        } catch ( Exception $e ) {
            error_log( 'WVP Simple Currency Converter error: ' . $e->getMessage() );
        }
        */
		
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
				// WVP_SENIAT_Exporter::get_instance();
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
