<?php

/**
 * BCV Fallback Manager
 *
 * Manages fallback mechanisms when BCV Dólar Tracker is not available.
 *
 * @link       https://kintaelectric.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 */

/**
 * BCV fallback manager class.
 *
 * This class provides fallback mechanisms when BCV Dólar Tracker is not available
 * or fails to provide a valid rate.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 * @author     Kinta Electric <info@kintaelectric.com>
 */
class WVP_BCV_Fallback_Manager {

	/**
	 * Fallback sources in order of preference.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $fallback_sources    Array of fallback sources.
	 */
	protected $fallback_sources = array(
		'bcv_primary' => 'get_bcv_primary_rate',
		'bcv_backup' => 'get_bcv_backup_rate',
		'manual_rate' => 'get_manual_rate',
		'last_known' => 'get_last_known_rate',
	);

	/**
	 * Get a safe BCV rate using fallback mechanisms.
	 *
	 * @since    1.0.0
	 * @return   float    The BCV rate.
	 */
	public function get_safe_rate() {
		foreach ( $this->fallback_sources as $source => $method ) {
			$rate = $this->$method();
			if ( $rate && $this->validate_rate( $rate ) ) {
				$this->log_fallback_usage( $source, $rate );
				return $rate;
			}
		}

		// If all fallbacks fail, return emergency rate
		return $this->get_emergency_rate();
	}

	/**
	 * Get primary BCV rate from BCV Dólar Tracker.
	 *
	 * @since    1.0.0
	 * @return   float|false    The BCV rate or false if not available.
	 */
	private function get_bcv_primary_rate() {
		if ( class_exists( 'BCV_Dolar_Tracker' ) ) {
			$rate = BCV_Dolar_Tracker::get_rate();
			if ( $rate && is_numeric( $rate ) ) {
				return (float) $rate;
			}
		}
		return false;
	}

	/**
	 * Get backup BCV rate by direct scraping.
	 *
	 * @since    1.0.0
	 * @return   float|false    The BCV rate or false if not available.
	 */
	private function get_bcv_backup_rate() {
		// Try to scrape BCV directly as backup
		$bcv_url = 'https://www.bcv.org.ve/';
		
		$response = wp_remote_get( $bcv_url, array(
			'timeout' => 10,
			'user-agent' => 'WooCommerce Venezuela Pro 2025',
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return false;
		}

		// Simple regex to extract dollar rate from BCV page
		// This is a basic implementation and may need adjustment based on BCV page structure
		if ( preg_match( '/USD\s*=\s*([0-9,]+\.?[0-9]*)/i', $body, $matches ) ) {
			$rate = str_replace( ',', '', $matches[1] );
			return (float) $rate;
		}

		return false;
	}

	/**
	 * Get manual rate set by user.
	 *
	 * @since    1.0.0
	 * @return   float|false    The manual rate or false if not set.
	 */
	private function get_manual_rate() {
		$manual_rate = get_option( 'wvp_manual_bcv_rate', 0 );
		return ( $manual_rate > 0 ) ? (float) $manual_rate : false;
	}

	/**
	 * Get last known valid rate.
	 *
	 * @since    1.0.0
	 * @return   float|false    The last known rate or false if not available.
	 */
	private function get_last_known_rate() {
		$last_rate = get_option( 'wvp_last_known_bcv_rate', 0 );
		return ( $last_rate > 0 ) ? (float) $last_rate : false;
	}

	/**
	 * Get emergency rate.
	 *
	 * @since    1.0.0
	 * @return   float    The emergency rate.
	 */
	private function get_emergency_rate() {
		$emergency_rate = get_option( 'wvp_emergency_bcv_rate', 50 );
		$this->log_fallback_usage( 'emergency', $emergency_rate );
		return (float) $emergency_rate;
	}

	/**
	 * Validate BCV rate.
	 *
	 * @since    1.0.0
	 * @param    float    $rate    The rate to validate.
	 * @return   boolean           True if rate is valid, false otherwise.
	 */
	private function validate_rate( $rate ) {
		$min_rate = 20; // Minimum VES per USD
		$max_rate = 100; // Maximum VES per USD
		
		return ( $rate >= $min_rate && $rate <= $max_rate );
	}

	/**
	 * Log fallback usage.
	 *
	 * @since    1.0.0
	 * @param    string    $source    The fallback source used.
	 * @param    float     $rate      The rate obtained.
	 */
	private function log_fallback_usage( $source, $rate ) {
		// Update fallback usage count
		$usage_count = get_option( 'wvp_fallback_usage_count', 0 );
		update_option( 'wvp_fallback_usage_count', $usage_count + 1 );

		// Log the usage
		error_log( sprintf( 
			'WVP BCV Fallback: Used %s source with rate %s', 
			$source, 
			$rate 
		) );

		// Update last known rate if it's a valid rate
		if ( $this->validate_rate( $rate ) && $source !== 'emergency' ) {
			update_option( 'wvp_last_known_bcv_rate', $rate );
			update_option( 'wvp_last_bcv_update', current_time( 'mysql' ) );
		}
	}

	/**
	 * Set manual BCV rate.
	 *
	 * @since    1.0.0
	 * @param    float    $rate    The manual rate to set.
	 * @return   boolean           True if rate was set successfully, false otherwise.
	 */
	public function set_manual_rate( $rate ) {
		if ( ! $this->validate_rate( $rate ) ) {
			return false;
		}

		update_option( 'wvp_manual_bcv_rate', $rate );
		update_option( 'wvp_last_known_bcv_rate', $rate );
		update_option( 'wvp_last_bcv_update', current_time( 'mysql' ) );

		return true;
	}

	/**
	 * Set emergency BCV rate.
	 *
	 * @since    1.0.0
	 * @param    float    $rate    The emergency rate to set.
	 * @return   boolean           True if rate was set successfully, false otherwise.
	 */
	public function set_emergency_rate( $rate ) {
		if ( ! $this->validate_rate( $rate ) ) {
			return false;
		}

		update_option( 'wvp_emergency_bcv_rate', $rate );
		return true;
	}

	/**
	 * Get fallback statistics.
	 *
	 * @since    1.0.0
	 * @return   array    Array of fallback statistics.
	 */
	public function get_fallback_stats() {
		return array(
			'usage_count' => get_option( 'wvp_fallback_usage_count', 0 ),
			'last_known_rate' => get_option( 'wvp_last_known_bcv_rate', 0 ),
			'last_update' => get_option( 'wvp_last_bcv_update', '' ),
			'manual_rate' => get_option( 'wvp_manual_bcv_rate', 0 ),
			'emergency_rate' => get_option( 'wvp_emergency_bcv_rate', 50 ),
		);
	}

	/**
	 * Reset fallback statistics.
	 *
	 * @since    1.0.0
	 */
	public function reset_stats() {
		update_option( 'wvp_fallback_usage_count', 0 );
		update_option( 'wvp_last_known_bcv_rate', 0 );
		update_option( 'wvp_last_bcv_update', '' );
	}

}
