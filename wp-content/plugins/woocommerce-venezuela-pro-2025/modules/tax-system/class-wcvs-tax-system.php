<?php

/**
 * WooCommerce Venezuela Suite 2025 - Tax System Module
 *
 * Módulo de sistema fiscal venezolano (IVA, IGTF)
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tax System Module
 */
class WCVS_Tax_System {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$all_settings = $this->core->settings->get_all_settings();
		$this->settings = $all_settings['tax'];
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Frontend hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_igtf_fee' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_tax_fields' ) );

		// Admin hooks
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_tax_info_admin' ) );

		// Tax hooks
		add_filter( 'woocommerce_cart_tax_totals', array( $this, 'modify_tax_totals' ), 10, 2 );
		add_filter( 'woocommerce_order_tax_totals', array( $this, 'modify_order_tax_totals' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'process_tax_order' ), 10, 1 );

		// AJAX hooks
		add_action( 'wp_ajax_wcvs_update_tax_rates', array( $this, 'ajax_update_tax_rates' ) );
		add_action( 'wp_ajax_wcvs_calculate_igtf', array( $this, 'ajax_calculate_igtf' ) );

		// Cron hooks
		add_action( 'wcvs_update_tax_rates', array( $this, 'update_tax_rates_from_api' ) );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'wcvs-tax-system',
			WCVS_PLUGIN_URL . 'modules/tax-system/js/tax-system.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);

		wp_enqueue_style(
			'wcvs-tax-system',
			WCVS_PLUGIN_URL . 'modules/tax-system/css/tax-system.css',
			array(),
			WCVS_VERSION
		);

		wp_localize_script( 'wcvs-tax-system', 'wcvs_tax_system', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_tax_system_nonce' ),
			'iva_rate' => isset( $this->settings['iva_rate'] ) ? $this->settings['iva_rate'] : 16,
			'igtf_rate' => isset( $this->settings['igtf_rate'] ) ? $this->settings['igtf_rate'] : 3,
			'apply_igtf_usd' => isset( $this->settings['apply_igtf_usd'] ) ? $this->settings['apply_igtf_usd'] : false,
			'validation_messages' => array(
				'rif_required' => __( 'El RIF es requerido para facturación', 'woocommerce-venezuela-pro-2025' ),
				'invalid_rif' => __( 'Formato de RIF inválido', 'woocommerce-venezuela-pro-2025' ),
				'cedula_required' => __( 'La Cédula es requerida para facturación', 'woocommerce-venezuela-pro-2025' ),
				'invalid_cedula' => __( 'Formato de Cédula inválido', 'woocommerce-venezuela-pro-2025' )
			)
		));
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'wcvs-tax-system-admin',
			WCVS_PLUGIN_URL . 'modules/tax-system/js/tax-system-admin.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);
	}

	/**
	 * Add IGTF fee to cart
	 *
	 * @param WC_Cart $cart
	 */
	public function add_igtf_fee( $cart ) {
		if ( ! $this->settings['apply_igtf_usd'] ) {
			return;
		}

		$payment_method = WC()->session->get( 'chosen_payment_method' );
		$usd_payment_methods = array( 'wcvs_zelle', 'wcvs_binance_pay', 'wcvs_cash_deposit' );

		if ( ! in_array( $payment_method, $usd_payment_methods ) ) {
			return;
		}

		$cart_total = $cart->get_subtotal();
		$igtf_amount = $cart_total * ( $this->settings['igtf_rate'] / 100 );

		if ( $igtf_amount > 0 ) {
			$cart->add_fee( 
				__( 'IGTF (Impuesto a las Grandes Transacciones Financieras)', 'woocommerce-venezuela-pro-2025' ), 
				$igtf_amount 
			);
		}
	}

	/**
	 * Validate tax fields
	 */
	public function validate_tax_fields() {
		// Validate RIF for billing
		if ( empty( $_POST['billing_rif'] ) ) {
			wc_add_notice( __( 'El RIF es requerido para facturación venezolana.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		} elseif ( ! $this->validate_rif( $_POST['billing_rif'] ) ) {
			wc_add_notice( __( 'El formato del RIF no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}

		// Validate Cédula for billing
		if ( empty( $_POST['billing_cedula'] ) ) {
			wc_add_notice( __( 'La Cédula es requerida para facturación venezolana.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		} elseif ( ! $this->validate_cedula( $_POST['billing_cedula'] ) ) {
			wc_add_notice( __( 'El formato de la Cédula no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Modify tax totals
	 *
	 * @param array $tax_totals
	 * @param WC_Cart $cart
	 * @return array
	 */
	public function modify_tax_totals( $tax_totals, $cart ) {
		// Add IGTF to tax totals if applicable
		if ( $this->settings['apply_igtf_usd'] ) {
			$payment_method = WC()->session->get( 'chosen_payment_method' );
			$usd_payment_methods = array( 'wcvs_zelle', 'wcvs_binance_pay', 'wcvs_cash_deposit' );

			if ( in_array( $payment_method, $usd_payment_methods ) ) {
				$cart_total = $cart->get_subtotal();
				$igtf_amount = $cart_total * ( $this->settings['igtf_rate'] / 100 );

				if ( $igtf_amount > 0 ) {
					$tax_totals['igtf'] = array(
						'label' => __( 'IGTF', 'woocommerce-venezuela-pro-2025' ),
						'amount' => $igtf_amount,
						'formatted_amount' => wc_price( $igtf_amount )
					);
				}
			}
		}

		return $tax_totals;
	}

	/**
	 * Modify order tax totals
	 *
	 * @param array $tax_totals
	 * @param WC_Order $order
	 * @return array
	 */
	public function modify_order_tax_totals( $tax_totals, $order ) {
		// Add IGTF to order tax totals if applicable
		if ( $this->settings['apply_igtf_usd'] ) {
			$payment_method = $order->get_payment_method();
			$usd_payment_methods = array( 'wcvs_zelle', 'wcvs_binance_pay', 'wcvs_cash_deposit' );

			if ( in_array( $payment_method, $usd_payment_methods ) ) {
				$order_total = $order->get_subtotal();
				$igtf_amount = $order_total * ( $this->settings['igtf_rate'] / 100 );

				if ( $igtf_amount > 0 ) {
					$tax_totals['igtf'] = array(
						'label' => __( 'IGTF', 'woocommerce-venezuela-pro-2025' ),
						'amount' => $igtf_amount,
						'formatted_amount' => wc_price( $igtf_amount )
					);
				}
			}
		}

		return $tax_totals;
	}

	/**
	 * Process tax order
	 *
	 * @param int $order_id
	 */
	public function process_tax_order( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Save tax information
		$this->save_tax_information( $order );
		
		// Log tax processing
		$this->core->logger->info( 'Tax information processed', array(
			'order_id' => $order_id,
			'iva_rate' => $this->settings['iva_rate'],
			'igtf_rate' => $this->settings['igtf_rate'],
			'payment_method' => $order->get_payment_method()
		));
	}

	/**
	 * Save tax information
	 *
	 * @param WC_Order $order
	 */
	private function save_tax_information( $order ) {
		// Save IVA rate
		$order->update_meta_data( '_wcvs_iva_rate', $this->settings['iva_rate'] );
		
		// Save IGTF rate
		$order->update_meta_data( '_wcvs_igtf_rate', $this->settings['igtf_rate'] );
		
		// Save IGTF amount if applicable
		if ( $this->settings['apply_igtf_usd'] ) {
			$payment_method = $order->get_payment_method();
			$usd_payment_methods = array( 'wcvs_zelle', 'wcvs_binance_pay', 'wcvs_cash_deposit' );

			if ( in_array( $payment_method, $usd_payment_methods ) ) {
				$order_total = $order->get_subtotal();
				$igtf_amount = $order_total * ( $this->settings['igtf_rate'] / 100 );
				$order->update_meta_data( '_wcvs_igtf_amount', $igtf_amount );
			}
		}
		
		// Mark as Venezuelan tax order
		$order->update_meta_data( '_wcvs_venezuelan_tax_order', true );
		
		$order->save();
	}

	/**
	 * Display tax info in admin
	 *
	 * @param WC_Order $order
	 */
	public function display_tax_info_admin( $order ) {
		if ( ! $order->get_meta( '_wcvs_venezuelan_tax_order' ) ) {
			return;
		}

		echo '<div class="wcvs-admin-tax-info">';
		echo '<h4>' . __( 'Información Fiscal Venezolana', 'woocommerce-venezuela-pro-2025' ) . '</h4>';
		
		if ( $order->get_meta( '_wcvs_iva_rate' ) ) {
			echo '<p><strong>' . __( 'Tasa de IVA:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_iva_rate' ) ) . '%</p>';
		}
		
		if ( $order->get_meta( '_wcvs_igtf_rate' ) ) {
			echo '<p><strong>' . __( 'Tasa de IGTF:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_igtf_rate' ) ) . '%</p>';
		}
		
		if ( $order->get_meta( '_wcvs_igtf_amount' ) ) {
			echo '<p><strong>' . __( 'Monto IGTF:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . wc_price( $order->get_meta( '_wcvs_igtf_amount' ) ) . '</p>';
		}
		
		echo '</div>';
	}

	/**
	 * AJAX update tax rates
	 */
	public function ajax_update_tax_rates() {
		check_ajax_referer( 'wcvs_tax_system_nonce', 'nonce' );

		$updated = $this->update_tax_rates_from_api();

		wp_send_json_success( array(
			'updated' => $updated,
			'message' => $updated ? __( 'Tasas de impuestos actualizadas', 'woocommerce-venezuela-pro-2025' ) : __( 'Error al actualizar tasas', 'woocommerce-venezuela-pro-2025' )
		));
	}

	/**
	 * AJAX calculate IGTF
	 */
	public function ajax_calculate_igtf() {
		check_ajax_referer( 'wcvs_tax_system_nonce', 'nonce' );

		$amount = floatval( $_POST['amount'] );
		$payment_method = sanitize_text_field( $_POST['payment_method'] );

		$igtf_amount = $this->calculate_igtf( $amount, $payment_method );

		wp_send_json_success( array(
			'igtf_amount' => $igtf_amount,
			'formatted_amount' => wc_price( $igtf_amount ),
			'rate' => $this->settings['igtf_rate']
		));
	}

	/**
	 * Calculate IGTF with proper rounding
	 *
	 * @param float $amount
	 * @param string $payment_method
	 * @return float
	 */
	private function calculate_igtf( $amount, $payment_method ) {
		if ( ! $this->settings['apply_igtf_usd'] ) {
			return 0;
		}

		$usd_payment_methods = array( 'wcvs_zelle', 'wcvs_binance_pay', 'wcvs_cash_deposit' );

		if ( ! in_array( $payment_method, $usd_payment_methods ) ) {
			return 0;
		}

		$igtf_rate = isset( $this->settings['igtf_rate'] ) ? floatval( $this->settings['igtf_rate'] ) : 3.0;
		$tax_amount = $amount * ( $igtf_rate / 100 );
		
		// Round to 2 decimal places for VES
		return round( $tax_amount, 2 );
	}

	/**
	 * Update tax rates from API
	 *
	 * @return bool
	 */
	public function update_tax_rates_from_api() {
		// Update IVA rate from SENIAT API
		$iva_rate = $this->get_iva_rate_from_api();
		if ( $iva_rate && $iva_rate > 0 ) {
			$this->settings['iva_rate'] = $iva_rate;
			update_option( 'wcvs_tax_settings', $this->settings );
		}

		// Update IGTF rate from BCV API
		$igtf_rate = $this->get_igtf_rate_from_api();
		if ( $igtf_rate && $igtf_rate > 0 ) {
			$this->settings['igtf_rate'] = $igtf_rate;
			update_option( 'wcvs_tax_settings', $this->settings );
		}

		// Log update
		$this->core->logger->info( 'Tax rates updated from API', array(
			'iva_rate' => $iva_rate,
			'igtf_rate' => $igtf_rate
		));

		return true;
	}

	/**
	 * Get IVA rate from SENIAT API
	 *
	 * @return float|false
	 */
	private function get_iva_rate_from_api() {
		// This would integrate with SENIAT API
		// For now, return the current rate
		return $this->settings['iva_rate'];
	}

	/**
	 * Get IGTF rate from BCV API
	 *
	 * @return float|false
	 */
	private function get_igtf_rate_from_api() {
		// This would integrate with BCV API
		// For now, return the current rate
		return $this->settings['igtf_rate'];
	}

	/**
	 * Validate RIF
	 *
	 * @param string $rif
	 * @return bool
	 */
	private function validate_rif( $rif ) {
		return preg_match( '/^[JGVEP]-[0-9]{8}-[0-9]$/', $rif );
	}

	/**
	 * Validate Cédula
	 *
	 * @param string $cedula
	 * @return bool
	 */
	private function validate_cedula( $cedula ) {
		return preg_match( '/^[V]-[0-9]{7,8}$/', $cedula );
	}

	/**
	 * Get tax summary
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	public function get_tax_summary( $order ) {
		$summary = array(
			'iva_rate' => $order->get_meta( '_wcvs_iva_rate' ),
			'igtf_rate' => $order->get_meta( '_wcvs_igtf_rate' ),
			'igtf_amount' => $order->get_meta( '_wcvs_igtf_amount' ),
			'venezuelan_tax' => $order->get_meta( '_wcvs_venezuelan_tax_order' )
		);

		return $summary;
	}

	/**
	 * Get current tax rates
	 *
	 * @return array
	 */
	public function get_current_tax_rates() {
		return array(
			'iva_rate' => $this->settings['iva_rate'],
			'igtf_rate' => $this->settings['igtf_rate'],
			'apply_igtf_usd' => $this->settings['apply_igtf_usd']
		);
	}

	/**
	 * Schedule tax rate updates
	 */
	public function schedule_tax_rate_updates() {
		if ( ! wp_next_scheduled( 'wcvs_update_tax_rates' ) ) {
			wp_schedule_event( time(), 'daily', 'wcvs_update_tax_rates' );
		}
	}

	/**
	 * Clear scheduled tax rate updates
	 */
	public function clear_scheduled_tax_rate_updates() {
		wp_clear_scheduled_hook( 'wcvs_update_tax_rates' );
	}

	/**
	 * Calculate Venezuelan taxes with enhanced precision
	 *
	 * @param float $amount
	 * @param string $payment_method
	 * @param array $order_data
	 * @return array
	 */
	public function calculate_taxes( $amount, $payment_method = '', $order_data = array() ) {
		$taxes = array();
		$breakdown = array();
		
		// Get current tax rates (can be dynamic)
		$iva_rate = $this->get_current_iva_rate();
		$igtf_rate = $this->get_current_igtf_rate();
		
		// Calculate IVA with proper rounding
		$iva_amount = $this->calculate_iva( $amount, $iva_rate );
		$taxes['iva'] = array(
			'rate' => $iva_rate,
			'amount' => $iva_amount,
			'label' => __( 'IVA', 'woocommerce-venezuela-pro-2025' ),
			'type' => 'vat',
			'base_amount' => $amount
		);
		$breakdown['iva'] = $iva_amount;
		
		// Calculate IGTF for USD payments with enhanced logic
		if ( $this->should_apply_igtf( $payment_method, $amount, $order_data ) ) {
			$igtf_amount = $this->calculate_igtf( $amount, $igtf_rate );
			$taxes['igtf'] = array(
				'rate' => $igtf_rate,
				'amount' => $igtf_amount,
				'label' => __( 'IGTF', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'financial_transaction_tax',
				'base_amount' => $amount
			);
			$breakdown['igtf'] = $igtf_amount;
		}
		
		// Calculate other local taxes if applicable
		$local_taxes = $this->calculate_local_taxes( $amount, $order_data );
		foreach ( $local_taxes as $tax_key => $tax_data ) {
			$taxes[ $tax_key ] = $tax_data;
			$breakdown[ $tax_key ] = $tax_data['amount'];
		}
		
		// Calculate total taxes
		$total_taxes = array_sum( $breakdown );
		
		// Store breakdown for reporting
		$taxes['breakdown'] = $breakdown;
		$taxes['total'] = $total_taxes;
		$taxes['total_with_taxes'] = $amount + $total_taxes;
		
		return $taxes;
	}

	/**
	 * Get current IVA rate (can be dynamic from SENIAT)
	 *
	 * @return float
	 */
	private function get_current_iva_rate() {
		// Check if we have a cached rate
		$cached_rate = get_transient( 'wcvs_current_iva_rate' );
		if ( $cached_rate !== false ) {
			return floatval( $cached_rate );
		}
		
		// Use configured rate as fallback
		$rate = isset( $this->settings['iva_rate'] ) ? floatval( $this->settings['iva_rate'] ) : 16.0;
		
		// Cache for 1 hour
		set_transient( 'wcvs_current_iva_rate', $rate, HOUR_IN_SECONDS );
		
		return $rate;
	}

	/**
	 * Get current IGTF rate (can be dynamic from BCV)
	 *
	 * @return float
	 */
	private function get_current_igtf_rate() {
		// Check if we have a cached rate
		$cached_rate = get_transient( 'wcvs_current_igtf_rate' );
		if ( $cached_rate !== false ) {
			return floatval( $cached_rate );
		}
		
		// Use configured rate as fallback
		$rate = isset( $this->settings['igtf_rate'] ) ? floatval( $this->settings['igtf_rate'] ) : 3.0;
		
		// Cache for 1 hour
		set_transient( 'wcvs_current_igtf_rate', $rate, HOUR_IN_SECONDS );
		
		return $rate;
	}

	/**
	 * Calculate IVA with proper rounding
	 *
	 * @param float $amount
	 * @param float $rate
	 * @return float
	 */
	private function calculate_iva( $amount, $rate ) {
		$tax_amount = $amount * ( $rate / 100 );
		
		// Round to 2 decimal places for VES
		return round( $tax_amount, 2 );
	}


	/**
	 * Determine if IGTF should be applied
	 *
	 * @param string $payment_method
	 * @param float $amount
	 * @param array $order_data
	 * @return bool
	 */
	private function should_apply_igtf( $payment_method, $amount, $order_data ) {
		// Check if IGTF is enabled
		if ( ! isset( $this->settings['apply_igtf_usd'] ) || ! $this->settings['apply_igtf_usd'] ) {
			return false;
		}
		
		// Check if payment method is USD-based
		$usd_payment_methods = array( 'wcvs_zelle', 'wcvs_binance_pay', 'wcvs_cash_deposit' );
		if ( ! in_array( $payment_method, $usd_payment_methods ) ) {
			return false;
		}
		
		// Check minimum amount threshold (if configured)
		$minimum_amount = isset( $this->settings['igtf_minimum_amount'] ) ? floatval( $this->settings['igtf_minimum_amount'] ) : 0;
		if ( $amount < $minimum_amount ) {
			return false;
		}
		
		// Check if customer is exempt (if configured)
		if ( isset( $order_data['customer_type'] ) && $order_data['customer_type'] === 'exempt' ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Calculate local taxes (municipal, state, etc.)
	 *
	 * @param float $amount
	 * @param array $order_data
	 * @return array
	 */
	private function calculate_local_taxes( $amount, $order_data ) {
		$local_taxes = array();
		
		// Municipal tax (if applicable)
		if ( isset( $this->settings['municipal_tax_rate'] ) && $this->settings['municipal_tax_rate'] > 0 ) {
			$municipal_rate = floatval( $this->settings['municipal_tax_rate'] );
			$municipal_amount = $amount * ( $municipal_rate / 100 );
			
			$local_taxes['municipal'] = array(
				'rate' => $municipal_rate,
				'amount' => round( $municipal_amount, 2 ),
				'label' => __( 'Impuesto Municipal', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'municipal_tax',
				'base_amount' => $amount
			);
		}
		
		// State tax (if applicable)
		if ( isset( $this->settings['state_tax_rate'] ) && $this->settings['state_tax_rate'] > 0 ) {
			$state_rate = floatval( $this->settings['state_tax_rate'] );
			$state_amount = $amount * ( $state_rate / 100 );
			
			$local_taxes['state'] = array(
				'rate' => $state_rate,
				'amount' => round( $state_amount, 2 ),
				'label' => __( 'Impuesto Estadal', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'state_tax',
				'base_amount' => $amount
			);
		}
		
		return $local_taxes;
	}

	/**
	 * Generate tax report
	 *
	 * @param string $period
	 * @param array $filters
	 * @return array
	 */
	public function generate_tax_report( $period = 'month', $filters = array() ) {
		global $wpdb;
		
		// Determine date range
		$date_range = $this->get_date_range( $period );
		
		// Build query
		$query = "
			SELECT 
				DATE(post_date) as date,
				SUM(meta_value) as total_iva,
				COUNT(*) as order_count
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'shop_order'
			AND p.post_status IN ('wc-completed', 'wc-processing')
			AND pm.meta_key = '_wcvs_iva_amount'
			AND p.post_date >= %s
			AND p.post_date <= %s
			GROUP BY DATE(post_date)
			ORDER BY date ASC
		";
		
		$results = $wpdb->get_results( $wpdb->prepare( $query, $date_range['start'], $date_range['end'] ) );
		
		// Calculate totals
		$total_iva = array_sum( wp_list_pluck( $results, 'total_iva' ) );
		$total_orders = array_sum( wp_list_pluck( $results, 'order_count' ) );
		
		return array(
			'period' => $period,
			'date_range' => $date_range,
			'daily_data' => $results,
			'totals' => array(
				'total_iva' => $total_iva,
				'total_orders' => $total_orders,
				'average_iva_per_order' => $total_orders > 0 ? $total_iva / $total_orders : 0
			)
		);
	}

	/**
	 * Get date range for period
	 *
	 * @param string $period
	 * @return array
	 */
	private function get_date_range( $period ) {
		$end_date = current_time( 'Y-m-d H:i:s' );
		
		switch ( $period ) {
			case 'week':
				$start_date = date( 'Y-m-d H:i:s', strtotime( '-1 week' ) );
				break;
			case 'month':
				$start_date = date( 'Y-m-d H:i:s', strtotime( '-1 month' ) );
				break;
			case 'quarter':
				$start_date = date( 'Y-m-d H:i:s', strtotime( '-3 months' ) );
				break;
			case 'year':
				$start_date = date( 'Y-m-d H:i:s', strtotime( '-1 year' ) );
				break;
			default:
				$start_date = date( 'Y-m-d H:i:s', strtotime( '-1 month' ) );
		}
		
		return array(
			'start' => $start_date,
			'end' => $end_date
		);
	}
}
