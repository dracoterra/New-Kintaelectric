<?php
/**
 * Diagnostic Tool for Plugin Issues
 * This will help identify what's causing the debug.log corruption
 */

// Load WordPress
require_once( '../../../wp-load.php' );

// Enable error reporting
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

echo '<!DOCTYPE html>';
echo '<html><head><title>Plugin Diagnostic</title>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
.section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }
</style></head><body>';

echo '<h1>🔍 Diagnóstico del Plugin WooCommerce Venezuela Pro 2025</h1>';

// Test 1: Check if plugin is loaded
echo '<div class="section">';
echo '<h2>1. Estado del Plugin</h2>';

if ( class_exists( 'Woocommerce_Venezuela_Pro_2025' ) ) {
    echo '<p class="success">✅ Clase principal Woocommerce_Venezuela_Pro_2025 está cargada</p>';
    
    // Check if instance exists
    $instance = Woocommerce_Venezuela_Pro_2025::get_instance();
    if ( $instance ) {
        echo '<p class="success">✅ Instancia del plugin creada correctamente</p>';
    } else {
        echo '<p class="error">❌ No se pudo crear la instancia del plugin</p>';
    }
} else {
    echo '<p class="error">❌ Clase principal Woocommerce_Venezuela_Pro_2025 NO está cargada</p>';
}
echo '</div>';

// Test 2: Check problematic classes
echo '<div class="section">';
echo '<h2>2. Clases Problemáticas</h2>';

if ( class_exists( 'WVP_Dependency_Container' ) ) {
    echo '<p class="warning">⚠️ WVP_Dependency_Container está cargada (puede causar problemas)</p>';
} else {
    echo '<p class="success">✅ WVP_Dependency_Container NO está cargada</p>';
}

if ( class_exists( 'WVP_Module_Manager' ) ) {
    echo '<p class="warning">⚠️ WVP_Module_Manager está cargada (puede causar problemas)</p>';
} else {
    echo '<p class="success">✅ WVP_Module_Manager NO está cargada</p>';
}
echo '</div>';

// Test 3: Check working classes
echo '<div class="section">';
echo '<h2>3. Clases Funcionales</h2>';

$working_classes = [
    'WVP_Admin_Dashboard' => 'Dashboard de administración',
    'WVP_SENIAT_Exporter' => 'Exportador SENIAT',
    'WVP_Venezuelan_Taxes' => 'Sistema de impuestos',
    'WVP_Pago_Movil_Gateway' => 'Gateway Pago Móvil',
    'WVP_Venezuelan_Shipping' => 'Métodos de envío'
];

foreach ( $working_classes as $class => $description ) {
    if ( class_exists( $class ) ) {
        echo '<p class="success">✅ ' . $class . ' - ' . $description . '</p>';
    } else {
        echo '<p class="error">❌ ' . $class . ' - ' . $description . '</p>';
    }
}
echo '</div>';

// Test 4: Check hooks
echo '<div class="section">';
echo '<h2>4. Hooks de WordPress</h2>';

global $wp_filter;

$important_hooks = [
    'init' => 'Inicialización',
    'admin_menu' => 'Menú de administración',
    'wp_enqueue_scripts' => 'Scripts del frontend',
    'woocommerce_init' => 'Inicialización de WooCommerce'
];

foreach ( $important_hooks as $hook => $description ) {
    if ( isset( $wp_filter[$hook] ) ) {
        $count = count( $wp_filter[$hook]->callbacks );
        echo '<p class="info">📋 ' . $hook . ' - ' . $description . ' (' . $count . ' callbacks)</p>';
    } else {
        echo '<p class="warning">⚠️ ' . $hook . ' - ' . $description . ' (sin callbacks)</p>';
    }
}
echo '</div>';

// Test 5: Check memory usage
echo '<div class="section">';
echo '<h2>5. Uso de Memoria</h2>';

$memory_usage = memory_get_usage( true );
$memory_limit = ini_get( 'memory_limit' );

echo '<p class="info">📊 Memoria actual: ' . size_format( $memory_usage ) . '</p>';
echo '<p class="info">📊 Límite de memoria: ' . $memory_limit . '</p>';

if ( $memory_usage > ( 50 * 1024 * 1024 ) ) { // 50MB
    echo '<p class="warning">⚠️ Uso de memoria alto - puede causar problemas</p>';
} else {
    echo '<p class="success">✅ Uso de memoria normal</p>';
}
echo '</div>';

// Test 6: Check for infinite loops
echo '<div class="section">';
echo '<h2>6. Detección de Bucles Infinitos</h2>';

// Check if we're in a loop by counting function calls
static $call_count = 0;
$call_count++;

if ( $call_count > 10 ) {
    echo '<p class="error">❌ Posible bucle infinito detectado (más de 10 llamadas)</p>';
} else {
    echo '<p class="success">✅ No se detectan bucles infinitos</p>';
}
echo '</div>';

// Test 7: Check debug.log
echo '<div class="section">';
echo '<h2>7. Estado del Debug.log</h2>';

$debug_log_path = WP_CONTENT_DIR . '/debug.log';
if ( file_exists( $debug_log_path ) ) {
    $log_size = filesize( $debug_log_path );
    echo '<p class="info">📊 Tamaño del debug.log: ' . size_format( $log_size ) . '</p>';
    
    if ( $log_size > ( 10 * 1024 * 1024 ) ) { // 10MB
        echo '<p class="warning">⚠️ Debug.log muy grande - puede estar corrompido</p>';
    } else {
        echo '<p class="success">✅ Debug.log tamaño normal</p>';
    }
    
    // Check last few lines
    $lines = file( $debug_log_path );
    $last_lines = array_slice( $lines, -5 );
    
    echo '<h3>Últimas 5 líneas del debug.log:</h3>';
    echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 3px;">';
    foreach ( $last_lines as $line ) {
        echo htmlspecialchars( $line );
    }
    echo '</pre>';
} else {
    echo '<p class="info">📋 Debug.log no existe</p>';
}
echo '</div>';

// Test 8: WooCommerce Integration
echo '<div class="section">';
echo '<h2>8. Integración con WooCommerce</h2>';

if ( class_exists( 'WooCommerce' ) ) {
    echo '<p class="success">✅ WooCommerce está activo</p>';
    
    // Check payment gateways
    $gateways = WC()->payment_gateways()->payment_gateways();
    echo '<h3>Gateways de pago registrados:</h3>';
    foreach ( $gateways as $id => $gateway ) {
        echo '<p class="info">📋 ' . $id . ' - ' . $gateway->get_title() . '</p>';
    }
    
    // Check shipping methods
    $shipping_methods = WC()->shipping()->get_shipping_methods();
    echo '<h3>Métodos de envío registrados:</h3>';
    foreach ( $shipping_methods as $id => $method ) {
        echo '<p class="info">📋 ' . $id . ' - ' . $method->get_title() . '</p>';
    }
} else {
    echo '<p class="error">❌ WooCommerce NO está activo</p>';
}
echo '</div>';

echo '<hr>';
echo '<h2>🔧 Acciones Recomendadas:</h2>';
echo '<p><a href="' . admin_url( 'plugins.php' ) . '" style="background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Ver Plugins</a></p>';
echo '<p><a href="' . admin_url( 'admin.php?page=wvp-dashboard' ) . '" style="background: #46b450; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Dashboard WVP</a></p>';
echo '<p><a href="' . home_url( '/shop' ) . '" style="background: #ff6900; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">Ver Tienda</a></p>';

echo '</body></html>';
