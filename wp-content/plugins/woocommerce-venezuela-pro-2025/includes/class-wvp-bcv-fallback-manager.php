<?php
/**
 * Manages the BCV Dólar Tracker fallback system for WooCommerce Venezuela Pro 2025.
 *
 * @link       https://artifexcodes.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 */

class WVP_BCV_Fallback_Manager {

    private $default_emergency_rate = 50; // Default emergency rate if nothing else is available

    public function __construct() {
        // Ensure the emergency rate option exists
        if ( ! get_option( 'wvp_emergency_bcv_rate' ) ) {
            update_option( 'wvp_emergency_bcv_rate', $this->default_emergency_rate );
        }
    }

    /**
     * Get the BCV rate with a robust fallback system.
     *
     * @since    1.0.0
     * @return   float    The current valid BCV rate, or a fallback rate.
     */
    public function get_bcv_rate() {
        $rate = 0;

        // 1. Try to get rate from BCV Dólar Tracker plugin
        if ( class_exists( 'BCV_Dolar_Tracker' ) ) {
            $rate = BCV_Dolar_Tracker::get_rate();
            if ( $this->is_valid_rate( $rate ) ) {
                // Update last known good rate
                update_option( 'wvp_last_known_bcv_rate', $rate );
                return $rate;
            }
        }

        // 2. Fallback to last known good rate
        $rate = get_option( 'wvp_last_known_bcv_rate' );
        if ( $this->is_valid_rate( $rate ) ) {
            // Log fallback usage
            $this->log_fallback_usage( 'last_known' );
            return $rate;
        }

        // 3. Fallback to manual rate (if set by admin)
        $rate = get_option( 'wvp_manual_bcv_rate' );
        if ( $this->is_valid_rate( $rate ) ) {
            $this->log_fallback_usage( 'manual' );
            return $rate;
        }

        // 4. Fallback to emergency rate
        $rate = get_option( 'wvp_emergency_bcv_rate', $this->default_emergency_rate );
        $this->log_fallback_usage( 'emergency' );
        return $rate;
    }

    /**
     * Check if a given rate is valid (e.g., within a reasonable range).
     *
     * @since    1.0.0
     * @param    float    $rate    The rate to validate.
     * @return   boolean           True if the rate is valid, false otherwise.
     */
    private function is_valid_rate( $rate ) {
        return is_numeric( $rate ) && $rate > 0 && $rate < 1000000; // Example range, adjust as needed
    }

    /**
     * Log fallback usage for statistics.
     *
     * @since    1.0.0
     * @param    string    $type    Type of fallback used (e.g., 'last_known', 'manual', 'emergency').
     */
    private function log_fallback_usage( $type ) {
        $count = get_option( 'wvp_fallback_usage_count', 0 );
        update_option( 'wvp_fallback_usage_count', $count + 1 );
        // Optionally, log details to a custom log file or database table
        // error_log( "WVP BCV Fallback used: {$type}" );
    }

    /**
     * Set a manual BCV rate.
     *
     * @since    1.0.0
     * @param    float    $rate    The manual rate to set.
     * @return   boolean           True on success, false on failure.
     */
    public function set_manual_rate( $rate ) {
        if ( $this->is_valid_rate( $rate ) ) {
            return update_option( 'wvp_manual_bcv_rate', $rate );
        }
        return false;
    }

    /**
     * Get the manual BCV rate.
     *
     * @since    1.0.0
     * @return   float|false    The manual rate, or false if not set.
     */
    public function get_manual_rate() {
        return get_option( 'wvp_manual_bcv_rate', false );
    }

    /**
     * Set the emergency BCV rate.
     *
     * @since    1.0.0
     * @param    float    $rate    The emergency rate to set.
     * @return   boolean           True on success, false on failure.
     */
    public function set_emergency_rate( $rate ) {
        if ( $this->is_valid_rate( $rate ) ) {
            return update_option( 'wvp_emergency_bcv_rate', $rate );
        }
        return false;
    }

    /**
     * Get the emergency BCV rate.
     *
     * @since    1.0.0
     * @return   float    The emergency rate.
     */
    public function get_emergency_rate() {
        return get_option( 'wvp_emergency_bcv_rate', $this->default_emergency_rate );
    }
}