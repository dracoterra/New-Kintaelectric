<?php
/**
 * Simple Currency Converter - Minimal Version
 * Handles USD to VES conversion using BCV rates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Simple_Currency_Converter {
	
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
		// Solo hooks esenciales para evitar conflictos
		add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Hooks adicionales se activarán gradualmente
		// add_action( 'woocommerce_single_product_summary', array( $this, 'add_currency_switcher' ), 25 );
		// add_filter( 'woocommerce_cart_item_price', array( $this, 'display_cart_item_price' ), 10, 3 );
		// add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_cart_item_subtotal' ), 10, 3 );
		// add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'display_converted_total' ) );
		// add_action( 'woocommerce_review_order_after_order_total', array( $this, 'display_converted_total' ) );
		// add_filter( 'woocommerce_currency_symbol', array( $this, 'custom_currency_symbol' ), 10, 2 );
		// add_filter( 'woocommerce_price_format', array( $this, 'custom_price_format' ), 10, 2 );
		// add_filter( 'woocommerce_get_price_html', array( $this, 'display_dual_price' ), 10, 2 );
	}
	
	/**
	 * Load BCV rate from options or emergency rate
	 */
	private function load_bcv_rate() {
		$this->bcv_rate = get_option( 'wvp_bcv_rate', $this->emergency_rate );
		$this->emergency_rate = get_option( 'wvp_emergency_rate', 36.5 );
	}
	
	/**
	 * Get current BCV rate
	 */
	public function get_bcv_rate() {
		if ( $this->bcv_rate === null ) {
			$this->load_bcv_rate();
		}
		return $this->bcv_rate;
	}
	
	/**
	 * Convert price from USD to VES
	 */
	public function convert_price( $price, $from_currency = 'USD', $to_currency = 'VES' ) {
		if ( $from_currency === $to_currency ) {
			return $price;
		}
		
		$rate = $this->get_bcv_rate();
		
		if ( $from_currency === 'USD' && $to_currency === 'VES' ) {
			return round( $price * $rate, 2 );
		}
		
		if ( $from_currency === 'VES' && $to_currency === 'USD' ) {
			return round( $price / $rate, 2 );
		}
		
		return $price;
	}
	
	/**
	 * AJAX handler for price conversion
	 */
	public function ajax_convert_price() {
		check_ajax_referer( 'wvp_convert_nonce', 'nonce' );
		
		$price = floatval( $_POST['price'] );
		$from_currency = sanitize_text_field( $_POST['from_currency'] );
		$to_currency = sanitize_text_field( $_POST['to_currency'] );
		
		$converted_price = $this->convert_price( $price, $from_currency, $to_currency );
		
		wp_send_json_success( array(
			'converted_price' => $converted_price,
			'rate' => $this->get_bcv_rate()
		));
	}
	
	/**
	 * Update BCV rate
	 */
	public function update_bcv_rate( $new_rate ) {
		update_option( 'wvp_bcv_rate', $new_rate );
		update_option( 'wvp_last_update', current_time( 'mysql' ) );
		$this->bcv_rate = $new_rate;
	}
	
	/**
	 * Get emergency rate
	 */
	public function get_emergency_rate() {
		return $this->emergency_rate;
	}
	
	/**
	 * Set emergency rate
	 */
	public function set_emergency_rate( $rate ) {
		$this->emergency_rate = $rate;
		update_option( 'wvp_emergency_rate', $rate );
	}
	
	/**
	 * Convert price from USD to VES (alias for convert_usd_to_ves)
	 */
	public function convert_price( $usd_amount ) {
		return $this->convert_usd_to_ves( $usd_amount );
	}
	
	/**
	 * Get current BCV rate
	 */
	public function get_bcv_rate() {
		return $this->bcv_rate;
	}
	
	/**
	 * Check if BCV rate is available
	 */
	public function is_rate_available() {
		return ! empty( $this->bcv_rate ) && $this->bcv_rate > 0;
	}
	
	/**
	 * Format price in VES
	 */
	public function format_ves_price( $amount ) {
		return number_format( $amount, 2, ',', '.' ) . ' VES';
	}
	
	/**
	 * Format price in USD
	 */
	public function format_usd_price( $amount ) {
		return '$' . number_format( $amount, 2, '.', ',' );
	}
	
	/**
	 * Display currency switcher on product pages
	 */
	public function add_currency_switcher() {
		if ( is_product() ) {
			echo '<div class="wvp-currency-switcher">';
			echo '<label for="wvp-currency-select">Moneda: </label>';
			echo '<select id="wvp-currency-select">';
			echo '<option value="USD">USD</option>';
			echo '<option value="VES">VES</option>';
			echo '</select>';
			echo '</div>';
		}
	}
	
	/**
	 * Display cart item price with conversion
	 */
	public function display_cart_item_price( $price, $cart_item, $cart_item_key ) {
		if ( $this->is_rate_available() ) {
			$product = $cart_item['data'];
			$usd_price = $product->get_price();
			$ves_price = $this->convert_usd_to_ves( $usd_price );
			
			$price .= '<br><small>(' . $this->format_ves_price( $ves_price ) . ')</small>';
		}
		return $price;
	}
	
	/**
	 * Display cart item subtotal with conversion
	 */
	public function display_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
		if ( $this->is_rate_available() ) {
			$product = $cart_item['data'];
			$quantity = $cart_item['quantity'];
			$usd_subtotal = $product->get_price() * $quantity;
			$ves_subtotal = $this->convert_usd_to_ves( $usd_subtotal );
			
			$subtotal .= '<br><small>(' . $this->format_ves_price( $ves_subtotal ) . ')</small>';
		}
		return $subtotal;
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
	 * Custom currency symbol
	 */
	public function custom_currency_symbol( $currency_symbol, $currency ) {
		if ( $currency === 'USD' ) {
			return '$';
		}
		return $currency_symbol;
	}
	
	/**
	 * Custom price format
	 */
	public function custom_price_format( $format, $currency_pos ) {
		return '%1$s%2$s';
	}
	
	/**
	 * Display dual price (USD and VES)
	 */
	public function display_dual_price( $price_html, $product ) {
		if ( $this->is_rate_available() && is_product() ) {
			$usd_price = $product->get_price();
			$ves_price = $this->convert_usd_to_ves( $usd_price );
			
			$price_html .= '<br><small class="wvp-ves-price">' . $this->format_ves_price( $ves_price ) . '</small>';
		}
		return $price_html;
	}
}