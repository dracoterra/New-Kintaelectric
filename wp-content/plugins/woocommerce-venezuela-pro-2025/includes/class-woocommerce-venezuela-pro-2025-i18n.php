<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://artifexcodes.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 * @author     ronald alvarez <ronaldalv2025@gmail.com>
 */
class Woocommerce_Venezuela_Pro_2025_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		// Temporarily disable text domain loading to avoid early loading warnings
		// This will be re-enabled in a future version when the plugin is more stable
		return;
		
		// Future implementation:
		// load_plugin_textdomain(
		//     'woocommerce-venezuela-pro-2025',
		//     false,
		//     dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		// );
	}



}
