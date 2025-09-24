<?php

/**
 * WooCommerce Venezuela Suite 2025 - Payment Gateways Module
 *
 * Módulo de pasarelas de pago locales para Venezuela
 * incluyendo Pago Móvil, Zelle, Binance Pay y transferencias bancarias.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment Gateways Module class
 */
class WCVS_Payment_Gateways {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_woocommerce' ) );
		add_action( 'wp_ajax_wcvs_validate_payment', array( $this, 'ajax_validate_payment' ) );
		add_action( 'wp_ajax_nopriv_wcvs_validate_payment', array( $this, 'ajax_validate_payment' ) );
	}

	/**
	 * Initialize module
	 */
	public function init() {
		// Initialize module functionality
		$this->init_payment_gateways();
	}

	/**
	 * Initialize WooCommerce integration
	 */
	public function init_woocommerce() {
		// Register payment gateways
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_payment_gateways' ) );
		
		// Add payment gateway settings
		add_action( 'woocommerce_settings_save_checkout', array( $this, 'save_payment_settings' ) );
	}

	/**
	 * Initialize payment gateways
	 */
	private function init_payment_gateways() {
		// Load payment gateway classes
		require_once WCVS_PLUGIN_DIR . 'modules/payment-gateways/gateways/class-wcvs-pago-movil.php';
		require_once WCVS_PLUGIN_DIR . 'modules/payment-gateways/gateways/class-wcvs-zelle.php';
		require_once WCVS_PLUGIN_DIR . 'modules/payment-gateways/gateways/class-wcvs-binance-pay.php';
		require_once WCVS_PLUGIN_DIR . 'modules/payment-gateways/gateways/class-wcvs-bank-transfer.php';
		require_once WCVS_PLUGIN_DIR . 'modules/payment-gateways/gateways/class-wcvs-cash-deposit.php';
		require_once WCVS_PLUGIN_DIR . 'modules/payment-gateways/gateways/class-wcvs-cashea.php';
	}

	/**
	 * Register payment gateways
	 *
	 * @param array $gateways
	 * @return array
	 */
	public function register_payment_gateways( $gateways ) {
		$gateways[] = 'WCVS_Gateway_Pago_Movil';
		$gateways[] = 'WCVS_Gateway_Zelle';
		$gateways[] = 'WCVS_Gateway_Binance_Pay';
		$gateways[] = 'WCVS_Gateway_Bank_Transfer';
		$gateways[] = 'WCVS_Gateway_Cash_Deposit';
		$gateways[] = 'WCVS_Gateway_Cashea';
		
		return $gateways;
	}

	/**
	 * Save payment settings
	 */
	public function save_payment_settings() {
		// Save payment gateway specific settings
		$this->save_pago_movil_settings();
		$this->save_zelle_settings();
		$this->save_binance_pay_settings();
		$this->save_bank_transfer_settings();
		$this->save_cash_deposit_settings();
		$this->save_cashea_settings();
	}

	/**
	 * Save Pago Móvil settings
	 */
	private function save_pago_movil_settings() {
		if ( isset( $_POST['wcvs_pago_movil_beneficiary_name'] ) ) {
			update_option( 'wcvs_pago_movil_beneficiary_name', sanitize_text_field( $_POST['wcvs_pago_movil_beneficiary_name'] ) );
		}
		
		if ( isset( $_POST['wcvs_pago_movil_beneficiary_id'] ) ) {
			update_option( 'wcvs_pago_movil_beneficiary_id', sanitize_text_field( $_POST['wcvs_pago_movil_beneficiary_id'] ) );
		}
		
		if ( isset( $_POST['wcvs_pago_movil_phone_number'] ) ) {
			update_option( 'wcvs_pago_movil_phone_number', sanitize_text_field( $_POST['wcvs_pago_movil_phone_number'] ) );
		}
		
		if ( isset( $_POST['wcvs_pago_movil_bank_name'] ) ) {
			update_option( 'wcvs_pago_movil_bank_name', sanitize_text_field( $_POST['wcvs_pago_movil_bank_name'] ) );
		}
	}

	/**
	 * Save Zelle settings
	 */
	private function save_zelle_settings() {
		if ( isset( $_POST['wcvs_zelle_email'] ) ) {
			update_option( 'wcvs_zelle_email', sanitize_email( $_POST['wcvs_zelle_email'] ) );
		}
		
		if ( isset( $_POST['wcvs_zelle_name'] ) ) {
			update_option( 'wcvs_zelle_name', sanitize_text_field( $_POST['wcvs_zelle_name'] ) );
		}
	}

	/**
	 * Save Binance Pay settings
	 */
	private function save_binance_pay_settings() {
		if ( isset( $_POST['wcvs_binance_pay_email'] ) ) {
			update_option( 'wcvs_binance_pay_email', sanitize_email( $_POST['wcvs_binance_pay_email'] ) );
		}
		
		if ( isset( $_POST['wcvs_binance_pay_name'] ) ) {
			update_option( 'wcvs_binance_pay_name', sanitize_text_field( $_POST['wcvs_binance_pay_name'] ) );
		}
	}

	/**
	 * Save Bank Transfer settings
	 */
	private function save_bank_transfer_settings() {
		if ( isset( $_POST['wcvs_bank_transfer_accounts'] ) ) {
			$accounts = array();
			foreach ( $_POST['wcvs_bank_transfer_accounts'] as $account ) {
				$accounts[] = array(
					'bank_name' => sanitize_text_field( $account['bank_name'] ),
					'account_type' => sanitize_text_field( $account['account_type'] ),
					'account_number' => sanitize_text_field( $account['account_number'] ),
					'account_holder' => sanitize_text_field( $account['account_holder'] ),
					'rif' => sanitize_text_field( $account['rif'] )
				);
			}
			update_option( 'wcvs_bank_transfer_accounts', $accounts );
		}
	}

	/**
	 * Save Cash Deposit settings
	 */
	private function save_cash_deposit_settings() {
		if ( isset( $_POST['wcvs_cash_deposit_location'] ) ) {
			update_option( 'wcvs_cash_deposit_location', sanitize_text_field( $_POST['wcvs_cash_deposit_location'] ) );
		}
		
		if ( isset( $_POST['wcvs_cash_deposit_instructions'] ) ) {
			update_option( 'wcvs_cash_deposit_instructions', wp_kses_post( $_POST['wcvs_cash_deposit_instructions'] ) );
		}
	}

	/**
	 * Save Cashea settings
	 */
	private function save_cashea_settings() {
		if ( isset( $_POST['wcvs_cashea_merchant_id'] ) ) {
			update_option( 'wcvs_cashea_merchant_id', sanitize_text_field( $_POST['wcvs_cashea_merchant_id'] ) );
		}
		
		if ( isset( $_POST['wcvs_cashea_api_key'] ) ) {
			update_option( 'wcvs_cashea_api_key', sanitize_text_field( $_POST['wcvs_cashea_api_key'] ) );
		}
	}

	/**
	 * AJAX handler for payment validation
	 */
	public function ajax_validate_payment() {
		check_ajax_referer( 'wcvs_validate_payment', 'nonce' );

		$payment_method = sanitize_text_field( $_POST['payment_method'] );
		$payment_reference = sanitize_text_field( $_POST['payment_reference'] );
		$order_id = absint( $_POST['order_id'] );

		$result = $this->validate_payment( $payment_method, $payment_reference, $order_id );

		if ( $result['valid'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Validate payment
	 *
	 * @param string $payment_method
	 * @param string $payment_reference
	 * @param int    $order_id
	 * @return array
	 */
	private function validate_payment( $payment_method, $payment_reference, $order_id ) {
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return array(
				'valid' => false,
				'message' => __( 'Pedido no encontrado', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// Validate payment reference format
		if ( ! $this->validate_payment_reference( $payment_method, $payment_reference ) ) {
			return array(
				'valid' => false,
				'message' => __( 'Formato de referencia de pago inválido', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// Check if reference already exists
		if ( $this->payment_reference_exists( $payment_reference ) ) {
			return array(
				'valid' => false,
				'message' => __( 'Esta referencia de pago ya ha sido utilizada', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// For now, auto-approve valid references
		// In production, this would integrate with bank APIs
		$this->approve_payment( $order_id, $payment_reference );

		return array(
			'valid' => true,
			'message' => __( 'Pago validado correctamente', 'woocommerce-venezuela-pro-2025' )
		);
	}

	/**
	 * Validate payment reference format
	 *
	 * @param string $payment_method
	 * @param string $payment_reference
	 * @return bool
	 */
	private function validate_payment_reference( $payment_method, $payment_reference ) {
		switch ( $payment_method ) {
			case 'wcvs_pago_movil':
				// Pago Móvil reference format (alphanumeric, 6-20 characters)
				return preg_match( '/^[A-Za-z0-9]{6,20}$/', $payment_reference );
			
			case 'wcvs_zelle':
				// Zelle confirmation number (numeric, 6-12 digits)
				return preg_match( '/^\d{6,12}$/', $payment_reference );
			
			case 'wcvs_binance_pay':
				// Binance Pay transaction ID (alphanumeric, 8-32 characters)
				return preg_match( '/^[A-Za-z0-9]{8,32}$/', $payment_reference );
			
			case 'wcvs_bank_transfer':
				// Bank transfer reference (alphanumeric, 6-20 characters)
				return preg_match( '/^[A-Za-z0-9]{6,20}$/', $payment_reference );
			
			default:
				return false;
		}
	}

	/**
	 * Check if payment reference already exists
	 *
	 * @param string $payment_reference
	 * @return bool
	 */
	private function payment_reference_exists( $payment_reference ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_payment_references';
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE reference = %s",
			$payment_reference
		) );
		
		return $exists > 0;
	}

	/**
	 * Approve payment
	 *
	 * @param int    $order_id
	 * @param string $payment_reference
	 */
	private function approve_payment( $order_id, $payment_reference ) {
		$order = wc_get_order( $order_id );
		
		if ( $order ) {
			// Update order status
			$order->payment_complete( $payment_reference );
			
			// Add order note
			$order->add_order_note( sprintf(
				__( 'Pago confirmado con referencia: %s', 'woocommerce-venezuela-pro-2025' ),
				$payment_reference
			) );
			
			// Store payment reference
			$this->store_payment_reference( $order_id, $payment_reference );
			
			// Log payment approval
			$this->core->logger->info( 'Payment approved', array(
				'order_id' => $order_id,
				'payment_reference' => $payment_reference
			) );
		}
	}

	/**
	 * Store payment reference
	 *
	 * @param int    $order_id
	 * @param string $payment_reference
	 */
	private function store_payment_reference( $order_id, $payment_reference ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_payment_references';
		$wpdb->insert(
			$table,
			array(
				'order_id' => $order_id,
				'reference' => $payment_reference,
				'created_at' => current_time( 'mysql' )
			),
			array( '%d', '%s', '%s' )
		);
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		// Add payment gateway specific frontend functionality
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout_payment' ) );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		if ( is_checkout() ) {
			wp_enqueue_script(
				'wcvs-payment-gateways',
				WCVS_PLUGIN_URL . 'modules/payment-gateways/js/payment-gateways.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-payment-gateways', 'wcvs_payment_gateways', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_validate_payment' ),
				'strings' => array(
					'validating' => __( 'Validando pago...', 'woocommerce-venezuela-pro-2025' ),
					'validated' => __( 'Pago validado', 'woocommerce-venezuela-pro-2025' ),
					'error' => __( 'Error al validar pago', 'woocommerce-venezuela-pro-2025' )
				)
			));
		}
	}

	/**
	 * Validate checkout payment
	 */
	public function validate_checkout_payment() {
		$payment_method = WC()->session->get( 'chosen_payment_method' );
		
		if ( strpos( $payment_method, 'wcvs_' ) === 0 ) {
			// Validate Venezuelan payment methods
			$this->validate_venezuelan_payment_method( $payment_method );
		}
	}

	/**
	 * Validate Venezuelan payment method
	 *
	 * @param string $payment_method
	 */
	private function validate_venezuelan_payment_method( $payment_method ) {
		switch ( $payment_method ) {
			case 'wcvs_pago_movil':
				$this->validate_pago_movil_checkout();
				break;
			case 'wcvs_zelle':
				$this->validate_zelle_checkout();
				break;
			case 'wcvs_binance_pay':
				$this->validate_binance_pay_checkout();
				break;
			case 'wcvs_bank_transfer':
				$this->validate_bank_transfer_checkout();
				break;
		}
	}

	/**
	 * Validate Pago Móvil checkout
	 */
	private function validate_pago_movil_checkout() {
		// Add specific validation for Pago Móvil
		if ( ! $this->core->settings->get_setting( 'payment_gateways', 'pago_movil_enabled' ) ) {
			wc_add_notice( __( 'Pago Móvil no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Validate Zelle checkout
	 */
	private function validate_zelle_checkout() {
		// Add specific validation for Zelle
		if ( ! $this->core->settings->get_setting( 'payment_gateways', 'zelle_enabled' ) ) {
			wc_add_notice( __( 'Zelle no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Validate Binance Pay checkout
	 */
	private function validate_binance_pay_checkout() {
		// Add specific validation for Binance Pay
		if ( ! $this->core->settings->get_setting( 'payment_gateways', 'binance_pay_enabled' ) ) {
			wc_add_notice( __( 'Binance Pay no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Validate Bank Transfer checkout
	 */
	private function validate_bank_transfer_checkout() {
		// Add specific validation for Bank Transfer
		if ( ! $this->core->settings->get_setting( 'payment_gateways', 'bank_transfer_enabled' ) ) {
			wc_add_notice( __( 'Transferencia Bancaria no está disponible', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}
}
