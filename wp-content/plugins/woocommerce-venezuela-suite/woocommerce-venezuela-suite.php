<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://artifexcodes.com/
 * @since             1.0.0
 * @package           Woocommerce_Venezuela_Suite
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Venezuela Suite
 * Plugin URI:        https://https://artifexcodes.com/plugin
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            ronald alvarez
 * Author URI:        https://https://artifexcodes.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-venezuela-suite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCOMMERCE_VENEZUELA_SUITE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-venezuela-suite-activator.php
 */
function activate_woocommerce_venezuela_suite() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-suite-activator.php';
	Woocommerce_Venezuela_Suite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-venezuela-suite-deactivator.php
 */
function deactivate_woocommerce_venezuela_suite() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-suite-deactivator.php';
	Woocommerce_Venezuela_Suite_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_venezuela_suite' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_venezuela_suite' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-suite.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_venezuela_suite() {

	$plugin = new Woocommerce_Venezuela_Suite();
	$plugin->run();

}
run_woocommerce_venezuela_suite();
