<?php
/**
 * Núcleo del módulo Currency Converter
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Dependencia del módulo BCV Integration.
if ( ! class_exists( 'Woocommerce_Venezuela_Suite_BCV_Integration_Core' ) ) {
	require_once dirname( dirname( __DIR__ ) ) . '/bcv-integration/includes/class-bcv-integration-core.php';
}

/**
 * Conversión USD <-> VES con redondeo y formato local.
 */
class Woocommerce_Venezuela_Suite_Currency_Converter_Core {
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
	 * Convierte monto en USD a VES usando tasa BCV.
	 *
	 * @param float $amount_usd
	 * @return float|null
	 */
	public function convert_usd_to_ves( $amount_usd ) {
		$amount_usd = (float) $amount_usd;
		if ( $amount_usd < 0 ) {
			return null;
		}
		$rate = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance()->get_current_rate();
		if ( null === $rate || $rate <= 0 ) {
			return null;
		}
		return $this->round_ves( $amount_usd * $rate );
	}

	/**
	 * Convierte monto en VES a USD usando tasa BCV.
	 *
	 * @param float $amount_ves
	 * @return float|null
	 */
	public function convert_ves_to_usd( $amount_ves ) {
		$amount_ves = (float) $amount_ves;
		if ( $amount_ves < 0 ) {
			return null;
		}
		$rate = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance()->get_current_rate();
		if ( null === $rate || $rate <= 0 ) {
			return null;
		}
		return round( $amount_ves / $rate, 2 );
	}

	/**
	 * Redondeo apropiado para precios en VES (2 decimales, coma y punto locales en formateo aparte).
	 *
	 * @param float $amount
	 * @return float
	 */
	public function round_ves( $amount ) {
		return round( (float) $amount, 2 );
	}

	/**
	 * Formatea un precio en VES con separadores locales.
	 *
	 * @param float $amount
	 * @return string
	 */
	public function format_ves_price( $amount ) {
		$amount = $this->round_ves( $amount );
		return number_format( $amount, 2, ',', '.' ) . ' VES';
	}
}


