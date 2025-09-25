<?php
/**
 * Debug Menu Registration
 */

require_once( '../../../wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'No tienes permisos para acceder a esta página' );
}

echo '<h1>🔍 Debug Menu Registration</h1>';

// Forzar la inicialización del plugin
if ( function_exists( 'wvp_init_minimal' ) ) {
    echo '<p style="color: green;">✅ Función wvp_init_minimal encontrada</p>';
    
    // Verificar si WooCommerce está activo
    if ( class_exists( 'WooCommerce' ) ) {
        echo '<p style="color: green;">✅ WooCommerce está activo</p>';
        
        // Intentar inicializar manualmente
        echo '<p>🔄 Inicializando plugin manualmente...</p>';
        wvp_init_minimal();
        echo '<p style="color: green;">✅ Plugin inicializado</p>';
    } else {
        echo '<p style="color: red;">❌ WooCommerce no está activo</p>';
    }
} else {
    echo '<p style="color: red;">❌ Función wvp_init_minimal no encontrada</p>';
}

// Verificar clases
$classes_to_check = [
    'WVP_Admin_Dashboard',
    'WVP_SENIAT_Exporter',
    'WVP_Simple_Currency_Converter',
    'WVP_Venezuelan_Taxes'
];

echo '<h2>📋 Verificación de Clases:</h2>';
foreach ( $classes_to_check as $class ) {
    if ( class_exists( $class ) ) {
        echo '<p style="color: green;">✅ ' . $class . '</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $class . '</p>';
    }
}

// Verificar hooks
echo '<h2>🎣 Verificación de Hooks:</h2>';
global $wp_filter;

if ( isset( $wp_filter['admin_menu'] ) ) {
    echo '<p style="color: green;">✅ Hook admin_menu está registrado</p>';
    
    // Mostrar callbacks del admin_menu
    $admin_menu_callbacks = $wp_filter['admin_menu']->callbacks;
    echo '<h3>Callbacks de admin_menu:</h3>';
    foreach ( $admin_menu_callbacks as $priority => $callbacks ) {
        echo '<p><strong>Prioridad ' . $priority . ':</strong></p>';
        foreach ( $callbacks as $callback ) {
            if ( is_array( $callback['function'] ) ) {
                $class = is_object( $callback['function'][0] ) ? get_class( $callback['function'][0] ) : $callback['function'][0];
                $method = $callback['function'][1];
                echo '<p>- ' . $class . '::' . $method . '</p>';
            } else {
                echo '<p>- ' . $callback['function'] . '</p>';
            }
        }
    }
} else {
    echo '<p style="color: red;">❌ Hook admin_menu no está registrado</p>';
}

// Verificar menús globales
echo '<h2>📋 Menús Globales:</h2>';
global $menu, $submenu;

echo '<h3>Menú Principal:</h3>';
if ( ! empty( $menu ) ) {
    foreach ( $menu as $item ) {
        if ( isset( $item[2] ) && strpos( $item[2], 'wvp' ) !== false ) {
            echo '<p style="color: green;">✅ ' . $item[0] . ' - ' . $item[2] . '</p>';
        }
    }
} else {
    echo '<p style="color: red;">❌ No hay menús registrados</p>';
}

echo '<h3>Submenús:</h3>';
if ( ! empty( $submenu ) ) {
    foreach ( $submenu as $parent => $submenu_items ) {
        if ( strpos( $parent, 'wvp' ) !== false ) {
            echo '<p><strong>' . $parent . ':</strong></p>';
            foreach ( $submenu_items as $submenu_item ) {
                echo '<p style="color: green;">- ' . $submenu_item[0] . ' - ' . $submenu_item[2] . '</p>';
            }
        }
    }
} else {
    echo '<p style="color: red;">❌ No hay submenús registrados</p>';
}

echo '<h2>🔗 Enlaces de Prueba:</h2>';
echo '<p><a href="' . admin_url( 'admin.php?page=wvp-dashboard' ) . '" target="_blank">Dashboard WVP</a></p>';
echo '<p><a href="' . admin_url( 'admin.php?page=wvp-seniat' ) . '" target="_blank">SENIAT</a></p>';

echo '<h2>📊 Información Adicional:</h2>';
echo '<p>WordPress Version: ' . get_bloginfo( 'version' ) . '</p>';
echo '<p>PHP Version: ' . PHP_VERSION . '</p>';
echo '<p>Current User: ' . wp_get_current_user()->user_login . '</p>';
echo '<p>User Capabilities: ' . ( current_user_can( 'manage_woocommerce' ) ? 'manage_woocommerce ✅' : 'manage_woocommerce ❌' ) . '</p>';

// Verificar si el plugin está activo
$active_plugins = get_option( 'active_plugins' );
echo '<h2>🔌 Plugins Activos:</h2>';
foreach ( $active_plugins as $plugin ) {
    if ( strpos( $plugin, 'woocommerce-venezuela-pro-2025' ) !== false ) {
        echo '<p style="color: green;">✅ ' . $plugin . '</p>';
    }
}
?>
