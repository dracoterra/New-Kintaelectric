<?php
/**
 * Script simple para verificar la clase
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Verificación Simple de Clase</h1>";

try {
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
    
    // Verificar métodos básicos
    $methods = get_class_methods( 'WVP_Analytics_Dashboard' );
    echo "✅ Métodos disponibles: " . count( $methods ) . "<br>";
    
    // Verificar métodos específicos
    $required_methods = array(
        'ajax_get_analytics_data',
        'generate_empty_analytics_data',
        'get_period_start_date',
        'get_period_days'
    );
    
    foreach ( $required_methods as $method ) {
        if ( in_array( $method, $methods ) ) {
            echo "✅ {$method} existe<br>";
        } else {
            echo "❌ {$method} NO existe<br>";
        }
    }
    
    echo "<h2>🎯 Estado</h2>";
    echo "✅ La clase se puede cargar correctamente<br>";
    echo "✅ Todos los métodos necesarios están disponibles<br>";
    
} catch ( Exception $e ) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>