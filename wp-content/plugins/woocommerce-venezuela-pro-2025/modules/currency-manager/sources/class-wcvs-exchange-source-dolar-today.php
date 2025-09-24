<?php

/**
 * WooCommerce Venezuela Suite 2025 - Dólar Today Exchange Source
 *
 * Fuente de tipo de cambio de Dólar Today
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dólar Today Exchange Source class
 */
class WCVS_Exchange_Source_DolarToday {

	/**
	 * Source ID
	 *
	 * @var string
	 */
	const SOURCE_ID = 'dolar_today';

	/**
	 * Source name
	 *
	 * @var string
	 */
	const SOURCE_NAME = 'Dólar Today';

	/**
	 * Source URL
	 *
	 * @var string
	 */
	const SOURCE_URL = 'https://dolartoday.com/';

	/**
	 * API endpoint
	 *
	 * @var string
	 */
	const API_ENDPOINT = 'https://s3.amazonaws.com/dolartoday/data.json';

	/**
	 * Get exchange rate from Dólar Today
	 *
	 * @return float|false
	 */
	public static function get_exchange_rate() {
		try {
			$response = wp_remote_get( self::API_ENDPOINT, array(
				'timeout' => 30,
				'headers' => array(
					'User-Agent' => 'WooCommerce Venezuela Suite 2025'
				)
			));

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( ! $data || ! isset( $data['USD'] ) ) {
				throw new Exception( 'Invalid response format from Dólar Today' );
			}

			$rate = floatval( $data['USD']['dolartoday'] );
			
			if ( $rate <= 0 ) {
				throw new Exception( 'Invalid exchange rate from Dólar Today' );
			}

			return $rate;

		} catch ( Exception $e ) {
			error_log( 'Dólar Today Exchange Rate Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get exchange rate with fallback
	 *
	 * @return float|false
	 */
	public static function get_exchange_rate_with_fallback() {
		$rate = self::get_exchange_rate();
		
		if ( $rate ) {
			return $rate;
		}

		// Try alternative endpoint
		$rate = self::get_exchange_rate_alternative();
		
		if ( $rate ) {
			return $rate;
		}

		// Return cached rate if available
		$cached_rate = get_option( 'wcvs_dolar_today_cached_rate', false );
		if ( $cached_rate ) {
			return floatval( $cached_rate );
		}

		return false;
	}

	/**
	 * Get exchange rate from alternative endpoint
	 *
	 * @return float|false
	 */
	private static function get_exchange_rate_alternative() {
		try {
			$response = wp_remote_get( 'https://dolartoday.com/api/exchange', array(
				'timeout' => 30,
				'headers' => array(
					'User-Agent' => 'WooCommerce Venezuela Suite 2025'
				)
			));

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( ! $data || ! isset( $data['rate'] ) ) {
				throw new Exception( 'Invalid response format from Dólar Today alternative endpoint' );
			}

			$rate = floatval( $data['rate'] );
			
			if ( $rate <= 0 ) {
				throw new Exception( 'Invalid exchange rate from Dólar Today alternative endpoint' );
			}

			return $rate;

		} catch ( Exception $e ) {
			error_log( 'Dólar Today Alternative Exchange Rate Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Cache exchange rate
	 *
	 * @param float $rate
	 */
	public static function cache_exchange_rate( $rate ) {
		update_option( 'wcvs_dolar_today_cached_rate', $rate );
		update_option( 'wcvs_dolar_today_cached_rate_timestamp', current_time( 'mysql' ) );
	}

	/**
	 * Get cached exchange rate
	 *
	 * @return float|false
	 */
	public static function get_cached_exchange_rate() {
		$cached_rate = get_option( 'wcvs_dolar_today_cached_rate', false );
		$cached_timestamp = get_option( 'wcvs_dolar_today_cached_rate_timestamp', false );
		
		if ( ! $cached_rate || ! $cached_timestamp ) {
			return false;
		}

		// Check if cache is still valid (1 hour)
		$cache_age = time() - strtotime( $cached_timestamp );
		if ( $cache_age > 3600 ) {
			return false;
		}

		return floatval( $cached_rate );
	}

	/**
	 * Get source information
	 *
	 * @return array
	 */
	public static function get_source_info() {
		return array(
			'id' => self::SOURCE_ID,
			'name' => self::SOURCE_NAME,
			'url' => self::SOURCE_URL,
			'api_endpoint' => self::API_ENDPOINT,
			'priority' => 2,
			'active' => true
		);
	}

	/**
	 * Validate exchange rate
	 *
	 * @param float $rate
	 * @return bool
	 */
	public static function validate_exchange_rate( $rate ) {
		// Dólar Today rates should be reasonable (between 1000 and 100000 VES per USD)
		return $rate >= 1000 && $rate <= 100000;
	}

	/**
	 * Get exchange rate history
	 *
	 * @param int $days
	 * @return array
	 */
	public static function get_exchange_rate_history( $days = 30 ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_exchange_rates_history';
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE source = %s ORDER BY created_at DESC LIMIT %d",
			self::SOURCE_ID,
			$days
		));

		return $results;
	}

	/**
	 * Get exchange rate statistics
	 *
	 * @param int $days
	 * @return array
	 */
	public static function get_exchange_rate_statistics( $days = 30 ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_exchange_rates_history';
		$results = $wpdb->get_row( $wpdb->prepare(
			"SELECT 
				AVG(rate) as average_rate,
				MIN(rate) as min_rate,
				MAX(rate) as max_rate,
				COUNT(*) as total_rates
			FROM {$table} 
			WHERE source = %s AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
			self::SOURCE_ID,
			$days
		));

		return array(
			'average_rate' => floatval( $results->average_rate ),
			'min_rate' => floatval( $results->min_rate ),
			'max_rate' => floatval( $results->max_rate ),
			'total_rates' => intval( $results->total_rates )
		);
	}
}
