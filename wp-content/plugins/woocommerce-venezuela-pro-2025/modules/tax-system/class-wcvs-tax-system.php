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
	 * Calculate IGTF
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

		return $amount * ( $this->settings['igtf_rate'] / 100 );
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
}
