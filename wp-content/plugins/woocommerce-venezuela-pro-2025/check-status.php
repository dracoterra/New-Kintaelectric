<?php
/**
 * Plugin Status Checker
 * Check if the plugin is working correctly
 */

// Load WordPress
require_once( '../../../wp-load.php' );

echo '<div style="padding: 20px; background: #f9f9f9; margin: 20px; border-radius: 5px; font-family: Arial, sans-serif;">';
echo '<h2>🔍 Estado del Plugin WooCommerce Venezuela Pro 2025</h2>';

// Check WooCommerce
if ( class_exists( 'WooCommerce' ) ) {
	echo '<p>✅ <strong>WooCommerce:</strong> Activo</p>';
} else {
	echo '<p>❌ <strong>WooCommerce:</strong> No activo</p>';
}

// Check if our classes exist
if ( class_exists( 'WVP_Simple_Currency_Converter' ) ) {
	echo '<p>✅ <strong>Conversor de Moneda:</strong> Cargado</p>';
} else {
	echo '<p>❌ <strong>Conversor de Moneda:</strong> No cargado</p>';
}

if ( class_exists( 'WVP_Venezuelan_Taxes' ) ) {
	echo '<p>✅ <strong>Sistema de Impuestos:</strong> Cargado</p>';
} else {
	echo '<p>❌ <strong>Sistema de Impuestos:</strong> No cargado</p>';
}

if ( class_exists( 'WVP_Pago_Movil_Gateway' ) ) {
	echo '<p>✅ <strong>Pago Móvil:</strong> Cargado</p>';
} else {
	echo '<p>❌ <strong>Pago Móvil:</strong> No cargado</p>';
}

// Check if payment gateway is registered
$gateways = WC()->payment_gateways()->payment_gateways();
if ( isset( $gateways['wvp_pago_movil'] ) ) {
	echo '<p>✅ <strong>Gateway Pago Móvil:</strong> Registrado</p>';
} else {
	echo '<p>❌ <strong>Gateway Pago Móvil:</strong> No registrado</p>';
}

// Check PHP version
echo '<p>📋 <strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';

// Check WordPress version
echo '<p>📋 <strong>WordPress Version:</strong> ' . get_bloginfo( 'version' ) . '</p>';

// Check WooCommerce version
if ( defined( 'WC_VERSION' ) ) {
	echo '<p>📋 <strong>WooCommerce Version:</strong> ' . WC_VERSION . '</p>';
}

echo '<hr>';
echo '<h3>🔧 Acciones:</h3>';
echo '<p><a href="' . plugin_dir_url( __FILE__ ) . 'activate-plugin.php" style="background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Activar Plugin Manualmente</a></p>';
echo '<p><a href="' . admin_url( 'plugins.php' ) . '" style="background: #46b450; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Ver Plugins</a></p>';
echo '<p><a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '" style="background: #ff6900; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Configurar Pagos</a></p>';

echo '</div>';
