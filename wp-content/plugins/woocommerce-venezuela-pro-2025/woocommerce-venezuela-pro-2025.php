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
 * Plugin Name:       WooCommerce Venezuela Suite
 * Plugin URI:        https://kinta-electric.com
 * Description:       Suite completa para localizar WooCommerce al mercado venezolano. Incluye gestión de moneda, pasarelas de pago locales, métodos de envío, sistema fiscal y facturación electrónica.
 * Version:           1.0.0
 * Author:            Kinta Electric
 * Author URI:        https://kinta-electric.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wcvs
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
define( 'WCVS_VERSION', '1.0.0' );
define( 'WCVS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCVS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCVS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-venezuela-pro-2025-activator.php
 */
function activate_wcvs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcvs-activator.php';
	WCVS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-venezuela-pro-2025-deactivator.php
 */
function deactivate_wcvs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcvs-deactivator.php';
	WCVS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wcvs' );
register_deactivation_hook( __FILE__, 'deactivate_wcvs' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wcvs-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wcvs() {

	$plugin = new WCVS_Core();
	$plugin->run();

}
run_wcvs();
