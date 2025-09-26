<?php
/**
 * Venezuelan Tax System
 * Handles IVA (16%) and IGTF (3%) calculations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Venezuelan_Taxes {
	
	private static $instance = null;
	private $iva_rate = 16; // 16% IVA
	private $igtf_rate = 3;  // 3% IGTF
	
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
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_venezuelan_taxes' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout' ) );
		add_filter( 'woocommerce_cart_totals_order_total_html', array( $this, 'display_tax_breakdown' ) );
		add_action( 'wp_ajax_wvp_update_tax_rates', array( $this, 'ajax_update_tax_rates' ) );
		add_action( 'wp_ajax_nopriv_wvp_update_tax_rates', array( $this, 'ajax_update_tax_rates' ) );
	}
	
	/**
	 * Add Venezuelan taxes to cart
	 */
	public function add_venezuelan_taxes( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		
		$subtotal = $cart->get_subtotal();
		
		// Calculate IVA (16%)
		$iva_amount = ( $subtotal * $this->iva_rate ) / 100;
		
		// Calculate IGTF (3%) - only on amounts over $200 USD
		$igtf_amount = 0;
		if ( $subtotal > 200 ) {
			$igtf_amount = ( $subtotal * $this->igtf_rate ) / 100;
		}
		
		// Add IVA fee
		if ( $iva_amount > 0 ) {
			$cart->add_fee( 'IVA (16%)', $iva_amount );
		}
		
		// Add IGTF fee
		if ( $igtf_amount > 0 ) {
			$cart->add_fee( 'IGTF (3%)', $igtf_amount );
		}
	}
	
	/**
	 * Validate checkout process
	 */
	public function validate_checkout() {
		// Add any Venezuelan-specific validation here
		if ( ! $this->is_valid_venezuelan_checkout() ) {
			wc_add_notice( 'Por favor, completa todos los campos requeridos para Venezuela.', 'error' );
		}
	}
	
	/**
	 * Check if checkout is valid for Venezuela
	 */
	private function is_valid_venezuelan_checkout() {
		// Add validation logic here
		return true;
	}
	
	/**
	 * Display tax breakdown in cart totals
	 */
	public function display_tax_breakdown( $total_html ) {
		$cart = WC()->cart;
		if ( ! $cart ) {
			return $total_html;
		}
		
		$subtotal = $cart->get_subtotal();
		$iva_amount = ( $subtotal * $this->iva_rate ) / 100;
		$igtf_amount = 0;
		
		if ( $subtotal > 200 ) {
			$igtf_amount = ( $subtotal * $this->igtf_rate ) / 100;
		}
		
		$breakdown = '<div class="wvp-tax-breakdown" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 5px;">';
		$breakdown .= '<h4>Desglose de Impuestos:</h4>';
		$breakdown .= '<p>Subtotal: ' . wc_price( $subtotal ) . '</p>';
		$breakdown .= '<p>IVA (16%): ' . wc_price( $iva_amount ) . '</p>';
		
		if ( $igtf_amount > 0 ) {
			$breakdown .= '<p>IGTF (3%): ' . wc_price( $igtf_amount ) . '</p>';
		}
		
		$total = $subtotal + $iva_amount + $igtf_amount;
		$breakdown .= '<p><strong>Total: ' . wc_price( $total ) . '</strong></p>';
		$breakdown .= '</div>';
		
		return $total_html . $breakdown;
	}
	
	/**
	 * Update tax rates via AJAX
	 */
	public function ajax_update_tax_rates() {
		check_ajax_referer( 'wvp_update_tax_rates', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$iva_rate = floatval( $_POST['iva_rate'] );
		$igtf_rate = floatval( $_POST['igtf_rate'] );
		
		// Validate rates
		if ( $iva_rate < 0 || $iva_rate > 50 || $igtf_rate < 0 || $igtf_rate > 10 ) {
			wp_send_json_error( 'Rates must be between 0-50% for IVA and 0-10% for IGTF' );
		}
		
		$this->iva_rate = $iva_rate;
		$this->igtf_rate = $igtf_rate;
		
		// Save to options
		update_option( 'wvp_iva_rate', $iva_rate );
		update_option( 'wvp_igtf_rate', $igtf_rate );
		
		wp_send_json_success( array(
			'message' => 'Tax rates updated successfully',
			'iva_rate' => $iva_rate,
			'igtf_rate' => $igtf_rate
		));
	}
	
	/**
	 * Get current tax rates
	 */
	public function get_tax_rates() {
		return array(
			'iva_rate' => get_option( 'wvp_iva_rate', $this->iva_rate ),
			'igtf_rate' => get_option( 'wvp_igtf_rate', $this->igtf_rate )
		);
	}
	
	/**
	 * Calculate IVA amount
	 */
	public function calculate_iva( $amount ) {
		$iva_rate = get_option( 'wvp_iva_rate', $this->iva_rate );
		return round( ( $amount * $iva_rate ) / 100, 2 );
	}
	
	/**
	 * Calculate IGTF amount
	 */
	public function calculate_igtf( $amount ) {
		$igtf_rate = get_option( 'wvp_igtf_rate', $this->igtf_rate );
		
		// IGTF only applies to amounts over $200 USD
		if ( $amount <= 200 ) {
			return 0;
		}
		
		return round( ( $amount * $igtf_rate ) / 100, 2 );
	}
	
	/**
	 * Calculate total taxes (IVA + IGTF)
	 */
	public function calculate_total_taxes( $amount ) {
		$iva = $this->calculate_iva( $amount );
		$igtf = $this->calculate_igtf( $amount );
		
		return $iva + $igtf;
	}
	
	/**
	 * Get tax breakdown for an amount
	 */
	public function get_tax_breakdown( $amount ) {
		return array(
			'subtotal' => $amount,
			'iva' => $this->calculate_iva( $amount ),
			'igtf' => $this->calculate_igtf( $amount ),
			'total_taxes' => $this->calculate_total_taxes( $amount ),
			'total' => $amount + $this->calculate_total_taxes( $amount )
		);
	}
	
	/**
	 * Format tax amount for display
	 */
	public function format_tax_amount( $amount, $currency = 'USD' ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $amount );
		}
		
		return '$' . number_format( $amount, 2 );
	}
	
	/**
	 * Check if IGTF applies to amount
	 */
	public function is_igtf_applicable( $amount ) {
		return $amount > 200;
	}
	
	/**
	 * Get IVA rate
	 */
	public function get_iva_rate() {
		return get_option( 'wvp_iva_rate', $this->iva_rate );
	}
	
	/**
	 * Get IGTF rate
	 */
	public function get_igtf_rate() {
		return get_option( 'wvp_igtf_rate', $this->igtf_rate );
	}
	
	/**
	 * Set IVA rate
	 */
	public function set_iva_rate( $rate ) {
		$this->iva_rate = $rate;
		update_option( 'wvp_iva_rate', $rate );
	}
	
	/**
	 * Set IGTF rate
	 */
	public function set_igtf_rate( $rate ) {
		$this->igtf_rate = $rate;
		update_option( 'wvp_igtf_rate', $rate );
	}
}
