<?php
/**
 * Núcleo del módulo Payment Gateways
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Payment_Gateways
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registro de gateways locales básicos.
 */
class Woocommerce_Venezuela_Suite_Payment_Gateways_Core {
	/** @var self */
	private static $instance = null;

	/**
	 * Singleton.
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateways' ) );
		add_action( 'init', array( $this, 'include_gateways' ) );
	}

	/**
	 * Incluye clases de gateways.
	 *
	 * @return void
	 */
	public function include_gateways() {
		require_once __DIR__ . '/gateways/class-wc-wvs-bank-transfer.php';
		require_once __DIR__ . '/gateways/class-wc-wvs-pago-movil.php';
	}

	/**
	 * Registra los gateways en WooCommerce.
	 *
	 * @param array $methods
	 * @return array
	 */
	public function register_gateways( $methods ) {
		$methods[] = 'WC_WVS_Bank_Transfer';
		$methods[] = 'WC_WVS_Pago_Movil';
		return $methods;
	}
}


