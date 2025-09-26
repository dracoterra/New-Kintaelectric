<?php
/**
 * Simple Currency Converter - Simplified Version
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
		// Only register essential hooks for now
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
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
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		if ( is_woocommerce() ) {
			$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
			$plugin_version = '1.0.0';
			
			wp_enqueue_style( 'wvp-simple-converter', $plugin_url . 'public/css/wvp-simple-converter.css', array(), $plugin_version );
			wp_enqueue_script( 'wvp-simple-converter', $plugin_url . 'public/js/wvp-simple-converter.js', array( 'jquery' ), $plugin_version, true );
			
			wp_localize_script( 'wvp-simple-converter', 'wvp_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvp_convert_nonce' ),
				'current_rate' => $this->get_bcv_rate()
			));
		}
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
}