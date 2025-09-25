<?php
/**
 * Test SENIAT JavaScript
 */

require_once( '../../../wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'No tienes permisos para acceder a esta página' );
}

// Simular datos de órdenes para la prueba
$test_orders = array(
    array(
        'order_id' => 536,
        'date' => '2025-09-25 19:33:15',
        'customer' => 'ronald alvarez',
        'customer_rif' => 'V-12345678',
        'subtotal_usd' => 30.00,
        'tax_usd' => 6.00,
        'total_usd' => 36.00,
        'subtotal_ves' => 4800.00,
        'tax_ves' => 960.00,
        'total_ves' => 5760.00,
        'payment_method' => 'Pago Móvil',
        'status' => 'processing'
    ),
    array(
        'order_id' => 532,
        'date' => '2025-09-17 23:44:02',
        'customer' => 'ronald alvarez',
        'customer_rif' => 'V-12345678',
        'subtotal_usd' => 15.00,
        'tax_usd' => 3.00,
        'total_usd' => 18.00,
        'subtotal_ves' => 2400.00,
        'tax_ves' => 480.00,
        'total_ves' => 2880.00,
        'payment_method' => 'Zelle',
        'status' => 'processing'
    )
);

$bcv_rate = 160.00;
$total_usd = array_sum( array_column( $test_orders, 'total_usd' ) );
$total_ves = array_sum( array_column( $test_orders, 'total_ves' ) );
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test SENIAT JavaScript</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-container { max-width: 800px; margin: 0 auto; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .btn { padding: 10px 20px; margin: 5px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #005177; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .result { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Test SENIAT JavaScript</h1>
        
        <div class="test-section">
            <h2>📊 Datos de Prueba</h2>
            <p><strong>Tasa BCV:</strong> <?php echo number_format( $bcv_rate, 2 ); ?></p>
            <p><strong>Total Órdenes:</strong> <?php echo count( $test_orders ); ?></p>
            <p><strong>Total USD:</strong> $<?php echo number_format( $total_usd, 2 ); ?></p>
            <p><strong>Total VES:</strong> <?php echo number_format( $total_ves, 2 ); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total USD</th>
                        <th>Total VES</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $test_orders as $order ): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['date']; ?></td>
                        <td><?php echo $order['customer']; ?></td>
                        <td>$<?php echo number_format( $order['total_usd'], 2 ); ?></td>
                        <td><?php echo number_format( $order['total_ves'], 2 ); ?></td>
                        <td><?php echo ucfirst( $order['status'] ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="test-section">
            <h2>🔧 Test de Funciones JavaScript</h2>
            <button class="btn" onclick="testWvpFunctions()">Probar Funciones WVP</button>
            <button class="btn btn-success" onclick="testAjaxCall()">Probar Llamada AJAX</button>
            <button class="btn btn-danger" onclick="testErrorHandling()">Probar Manejo de Errores</button>
            <div id="test-results"></div>
        </div>
        
        <div class="test-section">
            <h2>📄 Test de Exportación</h2>
            <button class="btn" onclick="testCSVExport()">Generar CSV de Prueba</button>
            <button class="btn" onclick="testXMLExport()">Generar XML de Prueba</button>
            <div id="export-results"></div>
        </div>
        
        <div class="test-section">
            <h2>🔗 Enlaces</h2>
            <p><a href="<?php echo admin_url( 'admin.php?page=wvp-dashboard' ); ?>" target="_blank">Dashboard WVP</a></p>
            <p><a href="<?php echo admin_url( 'admin.php?page=wvp-seniat' ); ?>" target="_blank">SENIAT</a></p>
        </div>
    </div>

    <script>
    // Datos de prueba
    const testData = <?php echo json_encode( $test_orders ); ?>;
    const bcvRate = <?php echo $bcv_rate; ?>;
    
    function testWvpFunctions() {
        const results = document.getElementById('test-results');
        let html = '<div class="result">';
        
        // Probar si las funciones existen
        if ( typeof wvpExportSalesBook === 'function' ) {
            html += '<div class="success">✅ wvpExportSalesBook está disponible</div>';
        } else {
            html += '<div class="error">❌ wvpExportSalesBook no está disponible</div>';
        }
        
        if ( typeof wvpPreviewSalesBook === 'function' ) {
            html += '<div class="success">✅ wvpPreviewSalesBook está disponible</div>';
        } else {
            html += '<div class="error">❌ wvpPreviewSalesBook no está disponible</div>';
        }
        
        if ( typeof wvpShowLoading === 'function' ) {
            html += '<div class="success">✅ wvpShowLoading está disponible</div>';
        } else {
            html += '<div class="error">❌ wvpShowLoading no está disponible</div>';
        }
        
        html += '</div>';
        results.innerHTML = html;
    }
    
    function testAjaxCall() {
        const results = document.getElementById('test-results');
        results.innerHTML = '<div class="result">🔄 Probando llamada AJAX...</div>';
        
        // Simular llamada AJAX
        const formData = new FormData();
        formData.append('action', 'wvp_export_seniat');
        formData.append('export_type', 'sales_book');
        formData.append('preview', 'true');
        formData.append('start_date', '2025-01-01');
        formData.append('end_date', '2025-01-31');
        formData.append('nonce', '<?php echo wp_create_nonce("wvp_seniat_nonce"); ?>');
        
        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                results.innerHTML = '<div class="result success">✅ Llamada AJAX exitosa</div>';
            } else {
                results.innerHTML = '<div class="result error">❌ Error en AJAX: ' + (data.data.message || 'Error desconocido') + '</div>';
            }
        })
        .catch(error => {
            results.innerHTML = '<div class="result error">❌ Error de red: ' + error.message + '</div>';
        });
    }
    
    function testErrorHandling() {
        const results = document.getElementById('test-results');
        
        try {
            // Probar manejo de errores
            if ( typeof wvpShowLoading === 'function' ) {
                wvpShowLoading('Probando manejo de errores...');
                setTimeout(() => {
                    if ( typeof wvpHideLoading === 'function' ) {
                        wvpHideLoading();
                        results.innerHTML = '<div class="result success">✅ Manejo de errores funciona correctamente</div>';
                    } else {
                        results.innerHTML = '<div class="result error">❌ wvpHideLoading no está disponible</div>';
                    }
                }, 1000);
            } else {
                results.innerHTML = '<div class="result error">❌ wvpShowLoading no está disponible</div>';
            }
        } catch (error) {
            results.innerHTML = '<div class="result error">❌ Error: ' + error.message + '</div>';
        }
    }
    
    function testCSVExport() {
        const results = document.getElementById('export-results');
        results.innerHTML = '<div class="result">🔄 Generando CSV...</div>';
        
        // Generar CSV simple
        let csv = 'Pedido,Fecha,Cliente,Total USD,Total VES,Estado\n';
        testData.forEach(order => {
            csv += `${order.order_id},${order.date},${order.customer},${order.total_usd},${order.total_ves},${order.status}\n`;
        });
        
        // Crear y descargar archivo
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'test_libro_ventas.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        results.innerHTML = '<div class="result success">✅ CSV generado y descargado</div>';
    }
    
    function testXMLExport() {
        const results = document.getElementById('export-results');
        results.innerHTML = '<div class="result">🔄 Generando XML...</div>';
        
        // Generar XML simple
        let xml = '<?xml version="1.0" encoding="UTF-8"?>\n';
        xml += '<libro_ventas>\n';
        xml += `  <fecha_generacion>${new Date().toISOString()}</fecha_generacion>\n`;
        xml += `  <total_registros>${testData.length}</total_registros>\n`;
        
        testData.forEach(order => {
            xml += '  <venta>\n';
            xml += `    <pedido>${order.order_id}</pedido>\n`;
            xml += `    <fecha>${order.date}</fecha>\n`;
            xml += `    <cliente>${order.customer}</cliente>\n`;
            xml += `    <total_usd>${order.total_usd}</total_usd>\n`;
            xml += `    <total_ves>${order.total_ves}</total_ves>\n`;
            xml += `    <estado>${order.status}</estado>\n`;
            xml += '  </venta>\n';
        });
        
        xml += '</libro_ventas>';
        
        // Crear y descargar archivo
        const blob = new Blob([xml], { type: 'application/xml' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'test_libro_ventas.xml';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        results.innerHTML = '<div class="result success">✅ XML generado y descargado</div>';
    }
    
    // Verificar si jQuery está disponible
    if ( typeof jQuery !== 'undefined' ) {
        console.log('✅ jQuery está disponible');
    } else {
        console.log('❌ jQuery no está disponible');
    }
    
    // Verificar si las variables AJAX están disponibles
    if ( typeof wvp_seniat_ajax !== 'undefined' ) {
        console.log('✅ wvp_seniat_ajax está disponible:', wvp_seniat_ajax);
    } else {
        console.log('❌ wvp_seniat_ajax no está disponible');
    }
    </script>
</body>
</html>
