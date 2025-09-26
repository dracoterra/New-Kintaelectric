<?php
/**
 * Script simple para verificar datos de analytics
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Verificación Simple de Datos Analytics</h1>";

// Verificar si la clase existe
if ( class_exists( 'WVP_Analytics_Dashboard' ) ) {
    echo "✅ Clase WVP_Analytics_Dashboard existe<br>";
    
    try {
        $analytics = WVP_Analytics_Dashboard::get_instance();
        echo "✅ Instancia obtenida correctamente<br>";
        
        // Probar get_analytics_data directamente
        $data = $analytics->get_analytics_data( '30_days' );
        
        echo "<h2>📊 Datos obtenidos:</h2>";
        echo "<pre>" . print_r( $data, true ) . "</pre>";
        
        // Verificar estructura específica para gráficas
        echo "<h2>🎯 Verificación para gráficas:</h2>";
        
        if ( isset( $data['sales'] ) ) {
            echo "<h3>Sales:</h3>";
            echo "- Total: " . ( $data['sales']['total'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['sales']['daily_data'] ?? [] ) . "<br>";
            if ( isset( $data['sales']['daily_data'] ) && count( $data['sales']['daily_data'] ) > 0 ) {
                echo "- Primer elemento: " . print_r( $data['sales']['daily_data'][0], true ) . "<br>";
            }
        }
        
        if ( isset( $data['orders'] ) ) {
            echo "<h3>Orders:</h3>";
            echo "- Total: " . ( $data['orders']['total'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['orders']['daily_data'] ?? [] ) . "<br>";
        }
        
        if ( isset( $data['bcv_rate'] ) ) {
            echo "<h3>BCV Rate:</h3>";
            echo "- Current rate: " . ( $data['bcv_rate']['current_rate'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['bcv_rate']['daily_data'] ?? [] ) . "<br>";
        }
        
        if ( isset( $data['tax_collected'] ) ) {
            echo "<h3>Tax Collected:</h3>";
            echo "- Total: " . ( $data['tax_collected']['total'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['tax_collected']['daily_data'] ?? [] ) . "<br>";
        }
        
    } catch ( Exception $e ) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "❌ La clase WVP_Analytics_Dashboard no existe<br>";
}

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
