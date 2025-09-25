<?php
/**
 * Simple Plugin Test
 * Test if the plugin is working
 */

// Load WordPress
require_once( '../../../wp-load.php' );

echo '<!DOCTYPE html>';
echo '<html><head><title>Test Plugin</title></head><body>';
echo '<div style="padding: 20px; font-family: Arial, sans-serif;">';

echo '<h1>🧪 Test del Plugin WooCommerce Venezuela Pro 2025</h1>';

// Test 1: WordPress loaded
echo '<h2>✅ WordPress cargado correctamente</h2>';
echo '<p>Versión: ' . get_bloginfo( 'version' ) . '</p>';

// Test 2: WooCommerce
if ( class_exists( 'WooCommerce' ) ) {
	echo '<h2>✅ WooCommerce está activo</h2>';
	echo '<p>Versión: ' . WC_VERSION . '</p>';
} else {
	echo '<h2>❌ WooCommerce NO está activo</h2>';
}

// Test 3: Load our plugin
echo '<h2>🔧 Cargando plugin...</h2>';
try {
	require_once plugin_dir_path( __FILE__ ) . 'woocommerce-venezuela-pro-2025-minimal.php';
	echo '<p>✅ Archivo del plugin cargado</p>';
	
	// Initialize
	wvp_init_minimal();
	echo '<p>✅ Plugin inicializado</p>';
	
	// Check classes
	if ( class_exists( 'WVP_Simple_Currency_Converter' ) ) {
		echo '<p>✅ Conversor de moneda disponible</p>';
	}
	
	if ( class_exists( 'WVP_Venezuelan_Taxes' ) ) {
		echo '<p>✅ Sistema de impuestos disponible</p>';
	}
	
	if ( class_exists( 'WVP_Pago_Movil_Gateway' ) ) {
		echo '<p>✅ Gateway Pago Móvil disponible</p>';
	}
	
} catch ( Exception $e ) {
	echo '<p>❌ Error: ' . $e->getMessage() . '</p>';
}

echo '<hr>';
echo '<h2>🔗 Enlaces útiles:</h2>';
echo '<p><a href="' . admin_url( 'plugins.php' ) . '">Ver plugins</a></p>';
echo '<p><a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">Configurar pagos</a></p>';
echo '<p><a href="' . home_url( '/shop' ) . '">Ver tienda</a></p>';

echo '</div>';
echo '</body></html>';
