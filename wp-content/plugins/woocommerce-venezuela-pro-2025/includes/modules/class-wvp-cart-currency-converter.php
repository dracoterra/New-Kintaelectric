<?php
/**
 * Cart Currency Converter Module
 * Shows VES conversions in cart and checkout pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Cart_Currency_Converter {
	
	private static $instance = null;
	private $bcv_rate = null;
	private $emergency_rate = 36.5;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->load_bcv_rate();
		$this->init_hooks();
	}
	
	private function init_hooks() {
		// Solo mostrar si el módulo está activo
		if ( $this->is_module_active() ) {
			add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'display_converted_total' ) );
			add_action( 'woocommerce_review_order_after_order_total', array( $this, 'display_converted_total' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}
	
	/**
	 * Check if this module is active
	 */
	private function is_module_active() {
		$active_modules = get_option( 'wvp_currency_modules', array() );
		return in_array( 'cart_converter', $active_modules );
	}
	
	/**
	 * Load BCV rate from options or emergency rate
	 */
	private function load_bcv_rate() {
		$this->bcv_rate = get_option( 'wvp_bcv_rate', $this->emergency_rate );
	}
	
	/**
	 * Get BCV rate
	 */
	public function get_bcv_rate() {
		return $this->bcv_rate;
	}
	
	/**
	 * Convert USD to VES
	 */
	public function convert_usd_to_ves( $usd_amount ) {
		return $usd_amount * $this->bcv_rate;
	}
	
	/**
	 * Format VES price
	 */
	public function format_ves_price( $amount ) {
		return number_format( $amount, 2, ',', '.' ) . ' VES';
	}
	
	/**
	 * Display converted total in cart and checkout
	 */
	public function display_converted_total() {
		if ( $this->is_rate_available() ) {
			$cart_total = WC()->cart->get_total( 'raw' );
			$ves_total = $this->convert_usd_to_ves( $cart_total );
			
			echo '<tr class="wvp-converted-total">';
			echo '<th>Total en VES:</th>';
			echo '<td>' . $this->format_ves_price( $ves_total ) . '</td>';
			echo '</tr>';
		}
	}
	
	/**
	 * Check if BCV rate is available
	 */
	private function is_rate_available() {
		return $this->bcv_rate && $this->bcv_rate > 0;
	}
	
	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		if ( is_cart() || is_checkout() ) {
			$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
			$plugin_version = '1.0.0';

			wp_enqueue_style( 'wvp-cart-converter', $plugin_url . 'public/css/wvp-cart-converter.css', array(), $plugin_version );
			wp_enqueue_script( 'wvp-cart-converter', $plugin_url . 'public/js/wvp-cart-converter.js', array( 'jquery' ), $plugin_version, true );
			
			wp_localize_script( 'wvp-cart-converter', 'wvp_cart_converter_ajax', array(
				'rate' => $this->get_bcv_rate()
			));
		}
	}
}
