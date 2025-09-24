<?php

/**
 * WooCommerce Venezuela Suite 2025 - Plugin Bootstrap
 *
 * Plugin modular para localizar WooCommerce al mercado venezolano
 * con cumplimiento total de regulaciones fiscales y legales.
 *
 * @link              https://artifexcodes.com/
 * @since             1.0.0
 * @package           WooCommerce_Venezuela_Suite_2025
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Venezuela Suite 2025
 * Plugin URI:        https://artifexcodes.com/woocommerce-venezuela-suite
 * Description:       Solución completa para localizar WooCommerce al mercado venezolano con módulos de moneda, pagos locales, impuestos, envíos y facturación electrónica.
 * Version:           1.0.0
 * Author:            Ronald Alvarez
 * Author URI:        https://artifexcodes.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-venezuela-pro-2025
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 * WC requires at least: 5.0
 * WC tested up to:   10.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'WCVS_VERSION', '1.0.0' );
define( 'WCVS_PLUGIN_FILE', __FILE__ );
define( 'WCVS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCVS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCVS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Check WooCommerce dependency
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action( 'admin_notices', 'wcvs_woocommerce_missing_notice' );
	return;
}

/**
 * Display WooCommerce missing notice
 */
function wcvs_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong>WooCommerce Venezuela Suite 2025</strong> requiere WooCommerce para funcionar.
			Por favor, instala y activa WooCommerce primero.
		</p>
	</div>
	<?php
}

/**
 * The code that runs during plugin activation
 */
function wcvs_activate_plugin() {
	require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-activator.php';
	WCVS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation
 */
function wcvs_deactivate_plugin() {
	require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-deactivator.php';
	WCVS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'wcvs_activate_plugin' );
register_deactivation_hook( __FILE__, 'wcvs_deactivate_plugin' );

/**
 * Load plugin text domain
 */
function wcvs_load_textdomain() {
	load_plugin_textdomain( 'woocommerce-venezuela-pro-2025', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Initialize the plugin
 */
function wcvs_init_plugin() {
	// Load text domain first
	wcvs_load_textdomain();
	
	// Load the core plugin class
	require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-core.php';
	
	// Load HPOS compatibility
	require_once WCVS_PLUGIN_DIR . 'includes/class-wcvs-hpos-compatibility.php';
	
	$plugin = WCVS_Core::get_instance();
	$plugin->init();
}

// Declare HPOS compatibility early
add_action( 'before_woocommerce_init', 'wcvs_declare_hpos_compatibility' );

/**
 * Declare HPOS compatibility
 */
function wcvs_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WCVS_PLUGIN_FILE, true );
	}
}

// Initialize plugin after WordPress is loaded
add_action( 'init', 'wcvs_init_plugin' );
