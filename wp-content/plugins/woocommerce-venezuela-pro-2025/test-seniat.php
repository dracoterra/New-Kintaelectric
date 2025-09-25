<?php
/**
 * Test SENIAT Integration
 */

require_once( '../../../wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'No tienes permisos para acceder a esta página' );
}

echo '<h1>🧪 Test SENIAT Integration</h1>';

// Verificar que el plugin esté activo
if ( ! class_exists( 'WVP_SENIAT_Exporter' ) ) {
    echo '<p style="color: red;">❌ WVP_SENIAT_Exporter no está disponible</p>';
} else {
    echo '<p style="color: green;">✅ WVP_SENIAT_Exporter está disponible</p>';
}

// Verificar que el admin dashboard esté activo
if ( ! class_exists( 'WVP_Admin_Dashboard' ) ) {
    echo '<p style="color: red;">❌ WVP_Admin_Dashboard no está disponible</p>';
} else {
    echo '<p style="color: green;">✅ WVP_Admin_Dashboard está disponible</p>';
}

// Verificar menús registrados
global $menu, $submenu;

echo '<h2>📋 Menús Registrados:</h2>';

if ( isset( $submenu['wvp-dashboard'] ) ) {
    echo '<p style="color: green;">✅ Submenús de wvp-dashboard encontrados:</p>';
    echo '<ul>';
    foreach ( $submenu['wvp-dashboard'] as $submenu_item ) {
        echo '<li>' . $submenu_item[0] . ' - ' . $submenu_item[2] . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p style="color: red;">❌ No se encontraron submenús para wvp-dashboard</p>';
}

// Verificar si el menú principal existe
$wvp_menu_found = false;
foreach ( $menu as $menu_item ) {
    if ( isset( $menu_item[2] ) && $menu_item[2] === 'wvp-dashboard' ) {
        $wvp_menu_found = true;
        echo '<p style="color: green;">✅ Menú principal wvp-dashboard encontrado: ' . $menu_item[0] . '</p>';
        break;
    }
}

if ( ! $wvp_menu_found ) {
    echo '<p style="color: red;">❌ Menú principal wvp-dashboard no encontrado</p>';
}

// Verificar plugins activos
echo '<h2>🔌 Plugins Activos:</h2>';
$active_plugins = get_option( 'active_plugins' );
foreach ( $active_plugins as $plugin ) {
    if ( strpos( $plugin, 'woocommerce-venezuela-pro-2025' ) !== false ) {
        echo '<p style="color: green;">✅ ' . $plugin . '</p>';
    }
}

// Verificar WooCommerce
if ( class_exists( 'WooCommerce' ) ) {
    echo '<p style="color: green;">✅ WooCommerce está activo</p>';
} else {
    echo '<p style="color: red;">❌ WooCommerce no está activo</p>';
}

echo '<h2>🔗 Enlaces de Prueba:</h2>';
echo '<p><a href="' . admin_url( 'admin.php?page=wvp-dashboard' ) . '" target="_blank">Dashboard WVP</a></p>';
echo '<p><a href="' . admin_url( 'admin.php?page=wvp-seniat' ) . '" target="_blank">SENIAT</a></p>';

echo '<h2>📊 Información del Sistema:</h2>';
echo '<p>WordPress Version: ' . get_bloginfo( 'version' ) . '</p>';
echo '<p>PHP Version: ' . PHP_VERSION . '</p>';
echo '<p>Current User: ' . wp_get_current_user()->user_login . '</p>';
echo '<p>User Capabilities: ' . ( current_user_can( 'manage_woocommerce' ) ? 'manage_woocommerce ✅' : 'manage_woocommerce ❌' ) . '</p>';
?>
