<?php
/**
 * Script de diagnóstico para Analytics Dashboard
 * Identifica problemas con las gráficas
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Diagnóstico de Analytics Dashboard</h1>";

// 1. Verificar si la clase existe
echo "<h2>1. Verificación de Clase</h2>";
if (class_exists('WVP_Analytics_Dashboard')) {
    echo "✅ Clase WVP_Analytics_Dashboard existe<br>";
    
    $analytics = new WVP_Analytics_Dashboard();
    echo "✅ Instancia creada correctamente<br>";
} else {
    echo "❌ Clase WVP_Analytics_Dashboard NO existe<br>";
}

// 2. Verificar función AJAX
echo "<h2>2. Verificación de Función AJAX</h2>";
if (method_exists($analytics, 'ajax_get_analytics_data')) {
    echo "✅ Método ajax_get_analytics_data existe<br>";
} else {
    echo "❌ Método ajax_get_analytics_data NO existe<br>";
}

// 3. Verificar función de datos de demostración
echo "<h2>3. Verificación de Datos de Demostración</h2>";
if (method_exists($analytics, 'generate_demo_analytics_data')) {
    echo "✅ Método generate_demo_analytics_data existe<br>";
    
    // Probar generación de datos
    try {
        $demo_data = $analytics->generate_demo_analytics_data('30_days');
        echo "✅ Datos de demostración generados correctamente<br>";
        echo "📊 Estructura de datos:<br>";
        echo "<pre>" . print_r(array_keys($demo_data), true) . "</pre>";
        
        // Verificar estructura de ventas
        if (isset($demo_data['sales']) && isset($demo_data['sales']['daily_data'])) {
            echo "✅ Datos de ventas diarias disponibles: " . count($demo_data['sales']['daily_data']) . " días<br>";
        } else {
            echo "❌ Datos de ventas diarias NO disponibles<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error al generar datos de demostración: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Método generate_demo_analytics_data NO existe<br>";
}

// 4. Verificar hooks de WordPress
echo "<h2>4. Verificación de Hooks</h2>";
global $wp_filter;
if (isset($wp_filter['wp_ajax_wvp_get_analytics_data'])) {
    echo "✅ Hook wp_ajax_wvp_get_analytics_data registrado<br>";
} else {
    echo "❌ Hook wp_ajax_wvp_get_analytics_data NO registrado<br>";
}

// 5. Simular llamada AJAX
echo "<h2>5. Simulación de Llamada AJAX</h2>";
try {
    // Simular POST data
    $_POST['period'] = '30_days';
    $_POST['nonce'] = wp_create_nonce('wvp_analytics_nonce');
    
    // Capturar output
    ob_start();
    $analytics->ajax_get_analytics_data();
    $output = ob_get_clean();
    
    echo "✅ Llamada AJAX ejecutada<br>";
    echo "📤 Respuesta: " . substr($output, 0, 200) . "...<br>";
    
} catch (Exception $e) {
    echo "❌ Error en llamada AJAX: " . $e->getMessage() . "<br>";
}

// 6. Verificar JavaScript
echo "<h2>6. Verificación de JavaScript</h2>";
echo "🔍 Revisar en el navegador:<br>";
echo "- Abrir DevTools (F12)<br>";
echo "- Ir a la pestaña Console<br>";
echo "- Recargar la página de Análisis<br>";
echo "- Buscar errores JavaScript<br>";

// 7. Verificar Chart.js
echo "<h2>7. Verificación de Chart.js</h2>";
echo "🔍 Verificar en el navegador:<br>";
echo "- Abrir DevTools (F12)<br>";
echo "- Ir a la pestaña Network<br>";
echo "- Recargar la página de Análisis<br>";
echo "- Buscar si Chart.js se carga correctamente<br>";

echo "<h2>🎯 Próximos Pasos</h2>";
echo "1. Ejecutar este script<br>";
echo "2. Revisar los resultados<br>";
echo "3. Probar la página de Análisis en el navegador<br>";
echo "4. Revisar la consola del navegador para errores JavaScript<br>";

echo "<hr>";
echo "<p><strong>Script ejecutado el:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
