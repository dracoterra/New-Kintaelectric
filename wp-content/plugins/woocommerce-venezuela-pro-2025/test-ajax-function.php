<?php
/**
 * Script para probar la función AJAX directamente
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🧪 Prueba de Función AJAX</h1>";

// Verificar si la clase existe
if ( ! class_exists( 'WVP_Analytics_Dashboard' ) ) {
    echo "❌ La clase WVP_Analytics_Dashboard no existe<br>";
    exit;
}

echo "✅ La clase WVP_Analytics_Dashboard existe<br>";

// Crear instancia
$analytics = new WVP_Analytics_Dashboard();

// Verificar si el método existe
if ( ! method_exists( $analytics, 'ajax_get_analytics_data' ) ) {
    echo "❌ El método ajax_get_analytics_data no existe<br>";
    exit;
}

echo "✅ El método ajax_get_analytics_data existe<br>";

// Simular datos POST
$_POST['period'] = 'last_30_days';
$_POST['nonce'] = wp_create_nonce( 'wvp_analytics_nonce' );

echo "<h2>🔍 Probando función AJAX...</h2>";

try {
    // Capturar output
    ob_start();
    $analytics->ajax_get_analytics_data();
    $output = ob_get_clean();
    
    echo "✅ Función ejecutada sin errores<br>";
    echo "<h3>📊 Respuesta:</h3>";
    echo "<pre>" . htmlspecialchars( $output ) . "</pre>";
    
} catch ( Exception $e ) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<hr>";
echo "<p><strong>Prueba ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
