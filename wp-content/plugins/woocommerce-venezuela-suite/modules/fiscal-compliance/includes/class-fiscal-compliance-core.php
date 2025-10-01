<?php
/**
 * Núcleo del módulo Fiscal Compliance (SENIAT)
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Fiscal_Compliance
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IVA/IGTF y validación de RIF no intrusiva.
 */
class Woocommerce_Venezuela_Suite_Fiscal_Compliance_Core {
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
		add_action( 'init', array( $this, 'register_tax_classes' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_igtf_fee' ), 20, 1 );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_rif_checkout_field' ) );
	}

	/**
	 * Registra clases de impuestos base (placeholder; configuración final vía UI/DB).
	 *
	 * @return void
	 */
	public function register_tax_classes() {
		// Este módulo puede asegurarse que existan clases nominales en WooCommerce.
		// Implementación avanzada se hará en la fase correspondiente.
	}

	/**
	 * Aplica IGTF como fee cuando el total en USD supera umbral (configurable).

	 * @param WC_Cart $cart
	 * @return void
	 */
	public function apply_igtf_fee( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		$enabled = get_option( 'wvs_igtf_enabled', 1 );
		if ( (int) $enabled !== 1 ) {
			return;
		}
		$threshold = (float) get_option( 'wvs_igtf_threshold', 200 );
		$rate      = (float) get_option( 'wvs_igtf_rate', 3 );

		$total_usd = $this->get_cart_total_usd( $cart );
		if ( $total_usd >= $threshold && $rate > 0 ) {
			$amount = ( $total_usd * $rate ) / 100;
			$cart->add_fee( __( 'IGTF', 'woocommerce-venezuela-suite' ), $amount );
		}
	}

	/**
	 * Convierte total del carrito a USD usando Currency Converter/BCV Integration.
	 *
	 * @param WC_Cart $cart
	 * @return float
	 */
	private function get_cart_total_usd( $cart ) {
		$total_ves = (float) $cart->get_total( 'edit' );
		if ( class_exists( 'Woocommerce_Venezuela_Suite_Currency_Converter_Core' ) ) {
			$converter = \Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();
			$rate = \Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance()->get_current_rate();
			if ( ! empty( $rate ) && $rate > 0 ) {
				return round( $total_ves / $rate, 2 );
			}
		}
		return 0.0;
	}

	/**
	 * Agrega campo RIF (no obligatorio) en checkout.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function add_rif_checkout_field( $fields ) {
		$fields['billing']['billing_rif'] = array(
			'label'       => __( 'RIF (opcional)', 'woocommerce-venezuela-suite' ),
			'placeholder' => 'J-12345678-9',
			'required'    => false,
			'class'       => array( 'form-row-wide' ),
			'priority'    => 120,
			'custom_attributes' => array(
				'pattern' => '^[JGVE]-[0-9]{8}-[0-9]$'
			),
		);
		return $fields;
	}
}


