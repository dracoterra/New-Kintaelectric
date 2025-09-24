<?php

/**
 * WooCommerce Venezuela Suite 2025 - Deactivator
 *
 * Maneja la desactivación del plugin y limpieza
 * de eventos programados.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator class
 */
class WCVS_Deactivator {

	/**
	 * Deactivate the plugin
	 */
	public static function deactivate() {
		// Clear scheduled events
		self::clear_scheduled_events();

		// Log deactivation
		error_log( 'WooCommerce Venezuela Suite 2025 deactivated' );
	}

	/**
	 * Clear scheduled events
	 */
	private static function clear_scheduled_events() {
		// Clear exchange rate updates
		wp_clear_scheduled_hook( 'wcvs_update_exchange_rates' );

		// Clear tax rate updates
		wp_clear_scheduled_hook( 'wcvs_update_tax_rates' );

		// Clear log cleanup
		wp_clear_scheduled_hook( 'wcvs_cleanup_logs' );
	}
}
