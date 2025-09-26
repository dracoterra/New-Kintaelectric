<?php
/**
 * Visual Currency Converter Module
 * Shows dual price display with visual boxes (USD = VES)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Visual_Currency_Converter {
	
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
			add_action( 'woocommerce_single_product_summary', array( $this, 'add_visual_price_display' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}
	
	/**
	 * Check if this module is active
	 */
	private function is_module_active() {
		$active_modules = get_option( 'wvp_currency_modules', array() );
		return in_array( 'visual_converter', $active_modules );
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
	 * Add visual price display to product pages
	 */
	public function add_visual_price_display() {
		if ( is_product() ) {
			global $product;
			if ( ! $product ) return;
			
			$price_usd = $product->get_price();
			if ( ! $price_usd ) return;
			
			$price_ves = $this->convert_usd_to_ves( $price_usd );
			$rate = $this->get_bcv_rate();
			
			echo '<div class="wvp-visual-price-display" style="margin: 20px 0; padding: 20px; border: 2px solid #0073aa; background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%); border-radius: 12px; box-shadow: 0 4px 8px rgba(0,115,170,0.1);">';
			echo '<div class="wvp-price-comparison" style="display: flex; align-items: center; justify-content: center; gap: 20px; margin-bottom: 15px;">';
			
			// USD Price Box
			echo '<div class="wvp-price-item usd" style="background: #fff; border: 2px solid #0073aa; border-radius: 8px; padding: 15px; text-align: center; min-width: 120px;">';
			echo '<span class="wvp-currency-label" style="display: block; font-size: 14px; font-weight: bold; color: #0073aa; margin-bottom: 5px;">USD</span>';
			echo '<span class="wvp-price-value" style="display: block; font-size: 24px; font-weight: bold; color: #2c3e50;">$' . number_format( $price_usd, 2 ) . '</span>';
			echo '</div>';
			
			// Equals sign
			echo '<div class="wvp-separator" style="font-size: 24px; font-weight: bold; color: #0073aa;">=</div>';
			
			// VES Price Box
			echo '<div class="wvp-price-item ves" style="background: #fff; border: 2px solid #27ae60; border-radius: 8px; padding: 15px; text-align: center; min-width: 120px;">';
			echo '<span class="wvp-currency-label" style="display: block; font-size: 14px; font-weight: bold; color: #27ae60; margin-bottom: 5px;">VES</span>';
			echo '<span class="wvp-price-value" style="display: block; font-size: 24px; font-weight: bold; color: #2c3e50;">' . number_format( $price_ves, 2 ) . '</span>';
			echo '</div>';
			
			echo '</div>';
			echo '<div class="wvp-rate-info" style="text-align: center; background: #0073aa; color: white; padding: 8px 15px; border-radius: 20px; font-size: 12px; font-weight: bold;">';
			echo 'Tasa BCV: 1 USD = ' . number_format( $rate, 2 ) . ' VES';
			echo '</div>';
			echo '</div>';
		}
	}
	
	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		if ( is_product() ) {
			$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
			$plugin_version = '1.0.0';

			wp_enqueue_style( 'wvp-visual-converter', $plugin_url . 'public/css/wvp-visual-converter.css', array(), $plugin_version );
		}
	}
}
