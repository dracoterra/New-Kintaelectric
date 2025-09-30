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
 * @package    Woocommerce_Venezuela_Suite
 * @subpackage Woocommerce_Venezuela_Suite/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Suite
 * @subpackage Woocommerce_Venezuela_Suite/includes
 * @author     ronald alvarez <ronaldalv2025@gmail.com>
 */
class Woocommerce_Venezuela_Suite_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-venezuela-suite',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
