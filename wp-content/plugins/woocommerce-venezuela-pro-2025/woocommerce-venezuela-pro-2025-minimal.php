<?php
/**
 * Plugin Name: WooCommerce Venezuela Pro 2025 - Minimal
 * Plugin URI: https://kintaelectric.com
 * Description: Plugin mínimo para WooCommerce Venezuela - Solo conversión de moneda
 * Version: 1.0.0
 * Author: Kinta Electric
 * License: GPL v2 or later
 * Text Domain: woocommerce-venezuela-pro-2025
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Use constants from main file if not already defined
if ( ! defined( 'WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION' ) ) {
	define( 'WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION', '1.0.0' );
}
if ( ! defined( 'WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_DIR' ) ) {
	define( 'WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL' ) ) {
	define( 'WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
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
			wp_enqueue_style( 'wvp-simple-converter', WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL . 'public/css/wvp-simple-converter.css', array(), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION );
			wp_enqueue_script( 'wvp-simple-converter', WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL . 'public/js/wvp-simple-converter.js', array( 'jquery' ), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION, true );
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
		
		echo '<div class="wvp-currency-switcher" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">';
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
 * Initialize the plugin
 */
function wvp_init_minimal() {
	if ( class_exists( 'WooCommerce' ) ) {
		WVP_Simple_Currency_Converter::get_instance();
	}
}
add_action( 'plugins_loaded', 'wvp_init_minimal' );

/**
 * Activation hook
 */
function wvp_activate_minimal() {
	// Simple activation - no complex setup
}
register_activation_hook( __FILE__, 'wvp_activate_minimal' );

/**
 * Deactivation hook
 */
function wvp_deactivate_minimal() {
	// Simple deactivation - no cleanup needed
}
register_deactivation_hook( __FILE__, 'wvp_deactivate_minimal' );
