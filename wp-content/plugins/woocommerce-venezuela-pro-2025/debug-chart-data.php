<?php
/**
 * Script para verificar datos exactos de gráficas
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Verificación de Datos para Gráficas</h1>";

// Verificar si la clase existe
if ( class_exists( 'WVP_Analytics_Dashboard' ) ) {
    echo "✅ Clase WVP_Analytics_Dashboard existe<br>";
    
    try {
        $analytics = WVP_Analytics_Dashboard::get_instance();
        echo "✅ Instancia obtenida correctamente<br>";
        
        // Probar get_analytics_data directamente
        $data = $analytics->get_analytics_data( '30_days' );
        
        echo "<h2>📊 Datos completos obtenidos:</h2>";
        echo "<pre>" . print_r( $data, true ) . "</pre>";
        
        // Verificar estructura específica para gráficas
        echo "<h2>🎯 Verificación específica para gráficas:</h2>";
        
        if ( isset( $data['sales'] ) ) {
            echo "<h3>📈 Sales (Ventas Diarias):</h3>";
            echo "- Total: " . ( $data['sales']['total'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['sales']['daily_data'] ?? [] ) . "<br>";
            if ( !empty( $data['sales']['daily_data'] ) ) {
                echo "- Primer elemento: " . print_r( $data['sales']['daily_data'][0], true ) . "<br>";
            }
        }
        
        if ( isset( $data['orders'] ) ) {
            echo "<h3>📦 Orders (Pedidos por Estado):</h3>";
            echo "- Total: " . ( $data['orders']['total'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['orders']['daily_data'] ?? [] ) . "<br>";
            echo "- Status breakdown count: " . count( $data['orders']['statuses'] ?? [] ) . "<br>";
            if ( !empty( $data['orders']['daily_data'] ) ) {
                echo "- Primer elemento: " . print_r( $data['orders']['daily_data'][0], true ) . "<br>";
            }
        }
        
        if ( isset( $data['bcv_rate'] ) ) {
            echo "<h3>💱 BCV Rate (Tasa BCV):</h3>";
            echo "- Current rate: " . ( $data['bcv_rate']['current_rate'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['bcv_rate']['daily_data'] ?? [] ) . "<br>";
            if ( !empty( $data['bcv_rate']['daily_data'] ) ) {
                echo "- Primer elemento: " . print_r( $data['bcv_rate']['daily_data'][0], true ) . "<br>";
            }
        }
        
        if ( isset( $data['tax_collected'] ) ) {
            echo "<h3>🏛️ Tax Collected (Impuestos Recaudados):</h3>";
            echo "- Total: " . ( $data['tax_collected']['total'] ?? 'N/A' ) . "<br>";
            echo "- Daily data count: " . count( $data['tax_collected']['daily_data'] ?? [] ) . "<br>";
            if ( !empty( $data['tax_collected']['daily_data'] ) ) {
                echo "- Primer elemento: " . print_r( $data['tax_collected']['daily_data'][0], true ) . "<br>";
            }
        }
        
    } catch ( Exception $e ) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Clase WVP_Analytics_Dashboard NO existe<br>";
}
?>
