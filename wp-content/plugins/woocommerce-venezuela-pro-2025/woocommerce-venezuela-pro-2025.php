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
 * @package           Woocommerce_Venezuela_Pro_2025
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Venezuela Pro 2025
 * Plugin URI:        https://https://artifexcodes.com/plugin
 * Description:       Plugin para tiendas Venezolanas.
 * Version:           1.0.0
 * Author:            ronald alvarez
 * Author URI:        https://https://artifexcodes.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-venezuela-pro-2025
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
define( 'WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-venezuela-pro-2025-activator.php
 */
function activate_woocommerce_venezuela_pro_2025() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-pro-2025-activator.php';
	Woocommerce_Venezuela_Pro_2025_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-venezuela-pro-2025-deactivator.php
 */
function deactivate_woocommerce_venezuela_pro_2025() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-pro-2025-deactivator.php';
	Woocommerce_Venezuela_Pro_2025_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_venezuela_pro_2025' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_venezuela_pro_2025' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-venezuela-pro-2025.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_venezuela_pro_2025() {

	$plugin = new Woocommerce_Venezuela_Pro_2025();
	$plugin->run();

}
run_woocommerce_venezuela_pro_2025();
