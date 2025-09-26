<?php
/**
 * Script simple para verificar el problema
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Diagnóstico Simple</h1>";

// Verificar si WooCommerce está activo
if ( ! class_exists( 'WooCommerce' ) ) {
    echo "❌ WooCommerce no está activo<br>";
    exit;
}

echo "✅ WooCommerce está activo<br>";

// Verificar si la clase existe
if ( ! class_exists( 'WVP_Analytics_Dashboard' ) ) {
    echo "❌ La clase WVP_Analytics_Dashboard no existe<br>";
    exit;
}

echo "✅ La clase WVP_Analytics_Dashboard existe<br>";

// Verificar métodos
$methods = get_class_methods( 'WVP_Analytics_Dashboard' );
echo "✅ Métodos disponibles: " . implode( ', ', $methods ) . "<br>";

// Verificar si el método específico existe
if ( in_array( 'ajax_get_analytics_data', $methods ) ) {
    echo "✅ El método ajax_get_analytics_data existe<br>";
} else {
    echo "❌ El método ajax_get_analytics_data NO existe<br>";
}

// Verificar si el método generate_empty_analytics_data existe
if ( in_array( 'generate_empty_analytics_data', $methods ) ) {
    echo "✅ El método generate_empty_analytics_data existe<br>";
} else {
    echo "❌ El método generate_empty_analytics_data NO existe<br>";
}

// Verificar si el método get_period_start_date existe
if ( in_array( 'get_period_start_date', $methods ) ) {
    echo "✅ El método get_period_start_date existe<br>";
} else {
    echo "❌ El método get_period_start_date NO existe<br>";
}

// Verificar si el método get_period_days existe
if ( in_array( 'get_period_days', $methods ) ) {
    echo "✅ El método get_period_days existe<br>";
} else {
    echo "❌ El método get_period_days NO existe<br>";
}

echo "<h2>🎯 Recomendaciones</h2>";
echo "Si faltan métodos, necesitamos agregarlos a la clase<br>";
echo "Si todos los métodos existen, el problema puede estar en la lógica<br>";

echo "<hr>";
echo "<p><strong>Diagnóstico ejecutado:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
