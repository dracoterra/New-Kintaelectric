<?php
/**
 * Test directo de la función AJAX del Analytics Dashboard
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🧪 Test Directo de AJAX Analytics</h1>";

// Verificar que el plugin esté activo
if (!class_exists('WVP_Analytics_Dashboard')) {
    echo "❌ Plugin no está activo o clase no existe<br>";
    exit;
}

// Crear instancia
$analytics = new WVP_Analytics_Dashboard();

// Simular datos POST
$_POST['period'] = '30_days';
$_POST['nonce'] = wp_create_nonce('wvp_analytics_nonce');

echo "<h2>📊 Generando datos de demostración...</h2>";

try {
    // Llamar directamente a la función
    $analytics->ajax_get_analytics_data();
    
    echo "✅ Función AJAX ejecutada correctamente<br>";
    echo "📤 Los datos deberían haberse enviado como JSON<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . "<br>";
    echo "📍 Línea: " . $e->getLine() . "<br>";
}

echo "<h2>🔍 Verificación Manual</h2>";
echo "1. Abre la página de Análisis en WordPress Admin<br>";
echo "2. Abre DevTools (F12) → Console<br>";
echo "3. Busca errores JavaScript<br>";
echo "4. Ve a Network → busca llamadas AJAX<br>";
echo "5. Verifica que Chart.js se carga correctamente<br>";

echo "<hr>";
echo "<p><strong>Test ejecutado:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
