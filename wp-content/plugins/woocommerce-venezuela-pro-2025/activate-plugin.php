<?php
/**
 * Simple plugin activation script
 * Run this to activate the plugin manually
 */

// Load WordPress
require_once( '../../../wp-load.php' );

// Check if WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
	die( 'WooCommerce debe estar activo para usar este plugin.' );
}

// Load the minimal plugin
require_once plugin_dir_path( __FILE__ ) . 'woocommerce-venezuela-pro-2025-minimal.php';

// Initialize the plugin
wvp_init_minimal();

echo '<div style="padding: 20px; background: #f0f0f0; margin: 20px; border-radius: 5px; font-family: Arial, sans-serif;">';
echo '<h2>🎉 Plugin WooCommerce Venezuela Pro 2025 Activado</h2>';
echo '<p>El plugin se ha inicializado correctamente.</p>';
echo '<p><strong>Funcionalidades disponibles:</strong></p>';
echo '<ul>';
echo '<li>✅ Conversor de moneda USD ↔ VES</li>';
echo '<li>✅ Sistema de impuestos venezolanos (IVA + IGTF)</li>';
echo '<li>✅ Método de pago Pago Móvil</li>';
echo '</ul>';
echo '<hr>';
echo '<h3>🔧 Próximos pasos:</h3>';
echo '<p><a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '" style="background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Configurar métodos de pago</a></p>';
echo '<p><a href="' . admin_url( 'plugins.php' ) . '" style="background: #46b450; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Ver todos los plugins</a></p>';
echo '<p><a href="' . home_url( '/shop' ) . '" style="background: #ff6900; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Ver tienda</a></p>';
echo '</div>';
