<?php
/**
 * BCV Volatility Analyzer - Analizador de volatilidad
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woocommerce_Venezuela_Suite_BCV_Volatility_Analyzer {

	/** @var self */
	private static $instance = null;

	/**
	 * Singleton.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Calcula un índice simple de volatilidad a partir de un historial de tasas.
	 *
	 * @param array $history Array de ['rate' => float].
	 * @return float Índice de 0 a 1.
	 */
	public function compute_volatility_index( $history ) {
		$rates = array();
		foreach ( $history as $row ) {
			if ( isset( $row['rate'] ) ) {
				$rates[] = (float) $row['rate'];
			}
		}

		if ( count( $rates ) < 2 ) {
			return 0.0;
		}

		$avg = array_sum( $rates ) / count( $rates );
		if ( $avg <= 0 ) {
			return 0.0;
		}

		$variance = array_sum( array_map( function( $r ) use ( $avg ) {
			return pow( $r - $avg, 2 );
		}, $rates ) ) / count( $rates );
		$std_dev = sqrt( $variance );

		// Normalizar asumiendo que coeficiente > 0.2 es muy volátil.
		$coefficient = $std_dev / $avg;
		return max( 0.0, min( 1.0, $coefficient / 0.2 ) );
	}
}
