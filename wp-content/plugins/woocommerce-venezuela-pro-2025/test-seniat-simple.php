<?php
/**
 * Test SENIAT Simple
 */

require_once( '../../../wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'No tienes permisos para acceder a esta página' );
}

echo '<h1>🧪 Test SENIAT Simple</h1>';

// Verificar que el plugin esté activo
if ( ! class_exists( 'WVP_SENIAT_Exporter' ) ) {
    echo '<p style="color: red;">❌ WVP_SENIAT_Exporter no está disponible</p>';
    exit;
}

echo '<p style="color: green;">✅ WVP_SENIAT_Exporter está disponible</p>';

// Crear instancia
$seniat = WVP_SENIAT_Exporter::get_instance();
echo '<p style="color: green;">✅ Instancia creada</p>';

// Simular datos de prueba
$test_orders = array(
    array(
        'order_id' => 1,
        'date' => '2025-01-15 10:30:00',
        'customer' => 'Juan Pérez',
        'customer_rif' => 'V-12345678',
        'subtotal_usd' => 100.00,
        'tax_usd' => 16.00,
        'total_usd' => 116.00,
        'subtotal_ves' => 3650.00,
        'tax_ves' => 584.00,
        'total_ves' => 4234.00,
        'payment_method' => 'Pago Móvil',
        'status' => 'completed'
    ),
    array(
        'order_id' => 2,
        'date' => '2025-01-16 14:20:00',
        'customer' => 'María González',
        'customer_rif' => 'V-87654321',
        'subtotal_usd' => 50.00,
        'tax_usd' => 8.00,
        'total_usd' => 58.00,
        'subtotal_ves' => 1825.00,
        'tax_ves' => 292.00,
        'total_ves' => 2117.00,
        'payment_method' => 'Zelle',
        'status' => 'processing'
    )
);

echo '<h2>📊 Datos de Prueba:</h2>';
echo '<table border="1" cellpadding="5" cellspacing="0">';
echo '<tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Total USD</th><th>Total VES</th><th>Estado</th></tr>';

foreach ( $test_orders as $order ) {
    echo '<tr>';
    echo '<td>#' . $order['order_id'] . '</td>';
    echo '<td>' . $order['date'] . '</td>';
    echo '<td>' . $order['customer'] . '</td>';
    echo '<td>$' . number_format( $order['total_usd'], 2 ) . '</td>';
    echo '<td>' . number_format( $order['total_ves'], 2 ) . '</td>';
    echo '<td>' . ucfirst( $order['status'] ) . '</td>';
    echo '</tr>';
}

echo '</table>';

// Calcular totales
$total_usd = array_sum( array_column( $test_orders, 'total_usd' ) );
$total_ves = array_sum( array_column( $test_orders, 'total_ves' ) );

echo '<h2>💰 Resumen:</h2>';
echo '<p><strong>Total Órdenes:</strong> ' . count( $test_orders ) . '</p>';
echo '<p><strong>Total USD:</strong> $' . number_format( $total_usd, 2 ) . '</p>';
echo '<p><strong>Total VES:</strong> ' . number_format( $total_ves, 2 ) . '</p>';
echo '<p><strong>Tasa BCV:</strong> ' . number_format( $total_ves / $total_usd, 2 ) . '</p>';

// Generar CSV de prueba
echo '<h2>📄 Generar CSV de Prueba:</h2>';

$upload_dir = wp_upload_dir();
$file_path = $upload_dir['basedir'] . '/wvp-exports/test_libro_ventas.csv';

// Crear directorio si no existe
if ( ! file_exists( dirname( $file_path ) ) ) {
    wp_mkdir_p( dirname( $file_path ) );
}

$file = fopen( $file_path, 'w' );

// Headers CSV
fputcsv( $file, array(
    'Pedido', 'Fecha', 'Cliente', 'RIF', 'Subtotal USD', 'Impuestos USD', 'Total USD',
    'Subtotal VES', 'Impuestos VES', 'Total VES', 'Método Pago', 'Estado'
));

// Datos
foreach ( $test_orders as $order ) {
    fputcsv( $file, array(
        $order['order_id'],
        $order['date'],
        $order['customer'],
        $order['customer_rif'],
        $order['subtotal_usd'],
        $order['tax_usd'],
        $order['total_usd'],
        $order['subtotal_ves'],
        $order['tax_ves'],
        $order['total_ves'],
        $order['payment_method'],
        $order['status']
    ));
}

fclose( $file );

if ( file_exists( $file_path ) ) {
    echo '<p style="color: green;">✅ Archivo CSV generado exitosamente</p>';
    echo '<p><a href="' . $upload_dir['baseurl'] . '/wvp-exports/test_libro_ventas.csv" download>📥 Descargar CSV de Prueba</a></p>';
} else {
    echo '<p style="color: red;">❌ Error al generar archivo CSV</p>';
}

echo '<h2>🔗 Enlaces:</h2>';
echo '<p><a href="' . admin_url( 'admin.php?page=wvp-dashboard' ) . '" target="_blank">Dashboard WVP</a></p>';
echo '<p><a href="' . admin_url( 'admin.php?page=wvp-seniat' ) . '" target="_blank">SENIAT</a></p>';

echo '<h2>📊 Información del Sistema:</h2>';
echo '<p>WordPress Version: ' . get_bloginfo( 'version' ) . '</p>';
echo '<p>PHP Version: ' . PHP_VERSION . '</p>';
echo '<p>Current User: ' . wp_get_current_user()->user_login . '</p>';
echo '<p>User Capabilities: ' . ( current_user_can( 'manage_woocommerce' ) ? 'manage_woocommerce ✅' : 'manage_woocommerce ❌' ) . '</p>';
?>
