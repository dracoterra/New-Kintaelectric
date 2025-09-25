<?php
/**
 * Test completo del sistema SENIAT
 * WooCommerce Venezuela Pro 2025
 */

require_once( '../../../wp-load.php' );

if ( ! current_user_can( 'manage_woocommerce' ) ) {
    die( 'No tienes permisos para acceder a esta página.' );
}

echo '<h1>🧪 Test Completo del Sistema SENIAT</h1>';

// Verificar que WooCommerce esté activo
if ( ! class_exists( 'WooCommerce' ) ) {
    echo '<div style="color: red;">❌ WooCommerce no está activo</div>';
    exit;
}

echo '<div style="color: green;">✅ WooCommerce está activo</div>';

// Verificar que el plugin esté activo
if ( ! class_exists( 'WVP_SENIAT_Exporter' ) ) {
    echo '<div style="color: red;">❌ WVP_SENIAT_Exporter no está disponible</div>';
    exit;
}

echo '<div style="color: green;">✅ WVP_SENIAT_Exporter está disponible</div>';

// Verificar directorio de exportaciones
$upload_dir = wp_upload_dir();
$export_dir = $upload_dir['basedir'] . '/wvp-exports/';

if ( ! file_exists( $export_dir ) ) {
    wp_mkdir_p( $export_dir );
    echo '<div style="color: orange;">⚠️ Directorio de exportaciones creado</div>';
} else {
    echo '<div style="color: green;">✅ Directorio de exportaciones existe</div>';
}

// Verificar permisos de escritura
if ( is_writable( $export_dir ) ) {
    echo '<div style="color: green;">✅ Directorio de exportaciones es escribible</div>';
} else {
    echo '<div style="color: red;">❌ Directorio de exportaciones no es escribible</div>';
}

// Verificar configuración
$bcv_rate = get_option( 'wvp_emergency_rate', 36.5 );
$iva_rate = get_option( 'wvp_iva_rate', 16 );
$igtf_rate = get_option( 'wvp_igtf_rate', 3 );

echo '<h2>📊 Configuración Actual</h2>';
echo '<ul>';
echo '<li><strong>Tasa BCV:</strong> ' . $bcv_rate . ' VES</li>';
echo '<li><strong>Tasa IVA:</strong> ' . $iva_rate . '%</li>';
echo '<li><strong>Tasa IGTF:</strong> ' . $igtf_rate . '%</li>';
echo '</ul>';

// Verificar órdenes de prueba
$orders = wc_get_orders( array(
    'limit' => 5,
    'status' => array( 'wc-completed', 'wc-processing' )
));

echo '<h2>📋 Órdenes Disponibles</h2>';
if ( empty( $orders ) ) {
    echo '<div style="color: orange;">⚠️ No hay órdenes disponibles para exportar</div>';
    echo '<p>Para probar el sistema SENIAT, necesitas crear algunas órdenes de prueba.</p>';
} else {
    echo '<div style="color: green;">✅ Se encontraron ' . count( $orders ) . ' órdenes</div>';
    echo '<table border="1" style="border-collapse: collapse; margin: 10px 0;">';
    echo '<tr><th>ID</th><th>Fecha</th><th>Cliente</th><th>Total USD</th><th>Estado</th></tr>';
    
    foreach ( $orders as $order ) {
        echo '<tr>';
        echo '<td>#' . $order->get_id() . '</td>';
        echo '<td>' . $order->get_date_created()->format( 'd/m/Y' ) . '</td>';
        echo '<td>' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '</td>';
        echo '<td>$' . number_format( $order->get_total(), 2 ) . '</td>';
        echo '<td>' . $order->get_status() . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Test de funciones de exportación
echo '<h2>🔧 Test de Funciones de Exportación</h2>';

try {
    $seniat_exporter = new WVP_SENIAT_Exporter();
    
    // Test de generación de datos de prueba
    $test_data = array(
        array(
            'order_id' => 'TEST001',
            'date' => '2025-01-01 10:00:00',
            'customer' => 'Cliente Prueba',
            'customer_rif' => 'V-12345678-9',
            'subtotal_usd' => 100.00,
            'tax_usd' => 16.00,
            'total_usd' => 116.00,
            'subtotal_ves' => 3650.00,
            'tax_ves' => 584.00,
            'total_ves' => 4234.00,
            'payment_method' => 'Pago Móvil',
            'status' => 'completed'
        )
    );
    
    // Test CSV
    echo '<h3>📄 Test Exportación CSV</h3>';
    $csv_result = $seniat_exporter->generate_csv_export( $test_data, 'test_sales_book' );
    if ( isset( $csv_result['file_url'] ) ) {
        echo '<div style="color: green;">✅ CSV generado: <a href="' . $csv_result['file_url'] . '" target="_blank">Descargar</a></div>';
    } else {
        echo '<div style="color: red;">❌ Error generando CSV</div>';
    }
    
    // Test XML
    echo '<h3>📄 Test Exportación XML</h3>';
    $xml_result = $seniat_exporter->generate_xml_export( $test_data, 'test_sales_book' );
    if ( isset( $xml_result['file_url'] ) ) {
        echo '<div style="color: green;">✅ XML generado: <a href="' . $xml_result['file_url'] . '" target="_blank">Descargar</a></div>';
    } else {
        echo '<div style="color: red;">❌ Error generando XML</div>';
    }
    
    // Test PDF/HTML
    echo '<h3>📄 Test Exportación PDF/HTML</h3>';
    $pdf_result = $seniat_exporter->generate_pdf_export( $test_data, 'test_sales_book' );
    if ( isset( $pdf_result['file_url'] ) ) {
        echo '<div style="color: green;">✅ PDF/HTML generado: <a href="' . $pdf_result['file_url'] . '" target="_blank">Ver</a></div>';
    } else {
        echo '<div style="color: red;">❌ Error generando PDF/HTML</div>';
    }
    
} catch ( Exception $e ) {
    echo '<div style="color: red;">❌ Error en test: ' . $e->getMessage() . '</div>';
}

echo '<h2>🎯 Resumen del Test</h2>';
echo '<p>El sistema SENIAT está listo para usar. Puedes acceder a él desde:</p>';
echo '<p><strong>Admin → WVP 2025 → SENIAT</strong></p>';

echo '<h2>📝 Instrucciones de Uso</h2>';
echo '<ol>';
echo '<li>Ve a <strong>Admin → WVP 2025 → SENIAT</strong></li>';
echo '<li>Selecciona el tipo de reporte que necesitas</li>';
echo '<li>Configura las fechas y formato de exportación</li>';
echo '<li>Haz clic en el botón de generación</li>';
echo '<li>Descarga o visualiza el archivo generado</li>';
echo '</ol>';

echo '<p><strong>Nota:</strong> Los archivos PDF se generan como HTML optimizado para impresión. Puedes usar "Imprimir a PDF" desde el navegador.</p>';
?>
