<?php
/**
 * Button Currency Converter Module
 * Shows currency switcher with USD/VES buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Button_Currency_Converter {
	
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
			add_action( 'woocommerce_single_product_summary', array( $this, 'add_currency_switcher' ), 25 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
			add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		}
	}
	
	/**
	 * Check if this module is active
	 */
	private function is_module_active() {
		$active_modules = get_option( 'wvp_currency_modules', array() );
		return in_array( 'button_converter', $active_modules );
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
	 * Add currency switcher to product pages
	 */
	public function add_currency_switcher() {
		if ( is_product() ) {
			global $product;
			if ( ! $product ) return;
			
			$price_usd = $product->get_price();
			$price_ves = $this->convert_usd_to_ves( $price_usd );
			$rate = $this->get_bcv_rate();
			
			echo '<div class="wvp-currency-switcher">';
			echo '<h4>Cambiar Moneda:</h4>';
			echo '<div class="wvp-currency-buttons">';
			echo '<button class="wvp-currency-btn active" data-currency="usd">USD</button>';
			echo '<button class="wvp-currency-btn" data-currency="ves">VES</button>';
			echo '</div>';
			echo '<div class="wvp-price-display">';
			echo '<span class="wvp-price-usd">$' . number_format( $price_usd, 2 ) . ' USD</span>';
			echo '<span class="wvp-price-ves" style="display: none;">' . number_format( $price_ves, 2 ) . ' VES</span>';
			echo '</div>';
			echo '<div class="wvp-rate-info">';
			echo '<small>Tipo de cambio: 1 USD = ' . number_format( $rate, 2 ) . ' VES</small>';
			echo '</div>';
			echo '</div>';
		}
	}
	
	/**
	 * AJAX handler for price conversion
	 */
	public function ajax_convert_price() {
		check_ajax_referer( 'wvp_convert_nonce', 'nonce' );
		
		$amount = floatval( $_POST['amount'] );
		$from_currency = sanitize_text_field( $_POST['from_currency'] );
		$to_currency = sanitize_text_field( $_POST['to_currency'] );
		
		if ( $from_currency === 'USD' && $to_currency === 'VES' ) {
			$converted_amount = $this->convert_usd_to_ves( $amount );
			wp_send_json_success( array(
				'converted_amount' => $converted_amount,
				'formatted_amount' => number_format( $converted_amount, 2 )
			));
		}
		
		wp_send_json_error( 'Invalid conversion' );
	}
	
	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		if ( is_product() ) {
			$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
			$plugin_version = '1.0.0';

			wp_enqueue_style( 'wvp-button-converter', $plugin_url . 'public/css/wvp-button-converter.css', array(), $plugin_version );
			wp_enqueue_script( 'wvp-button-converter', $plugin_url . 'public/js/wvp-button-converter.js', array( 'jquery' ), $plugin_version, true );
			
			wp_localize_script( 'wvp-button-converter', 'wvp_button_converter_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvp_convert_nonce' ),
				'rate' => $this->get_bcv_rate()
			));
		}
	}
}
