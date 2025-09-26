<?php
/**
 * Script para verificar datos exactos devueltos por AJAX
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Verificación de Datos AJAX</h1>";

// Verificar si la clase existe
if ( class_exists( 'WVP_Analytics_Dashboard' ) ) {
    $analytics = new WVP_Analytics_Dashboard();
    
    echo "<h2>📊 Datos devueltos por get_analytics_data:</h2>";
    
    // Simular la llamada AJAX
    $_POST['period'] = '30_days';
    $_POST['nonce'] = wp_create_nonce( 'wvp_analytics_nonce' );
    
    // Capturar la salida
    ob_start();
    $analytics->ajax_get_analytics_data();
    $output = ob_get_clean();
    
    echo "<h3>Respuesta JSON:</h3>";
    echo "<pre>" . htmlspecialchars( $output ) . "</pre>";
    
    // Decodificar JSON para análisis
    $json_data = json_decode( $output, true );
    
    if ( $json_data && isset( $json_data['data'] ) ) {
        echo "<h3>Análisis de datos:</h3>";
        
        foreach ( $json_data['data'] as $key => $value ) {
            echo "<h4>{$key}:</h4>";
            if ( is_array( $value ) ) {
                if ( isset( $value['daily_data'] ) ) {
                    echo "- daily_data: " . count( $value['daily_data'] ) . " elementos<br>";
                    if ( count( $value['daily_data'] ) > 0 ) {
                        echo "- Primer elemento: " . print_r( $value['daily_data'][0], true ) . "<br>";
                    }
                }
                if ( isset( $value['total'] ) ) {
                    echo "- total: " . $value['total'] . "<br>";
                }
                if ( isset( $value['value'] ) ) {
                    echo "- value: " . $value['value'] . "<br>";
                }
            } else {
                echo "- Valor: " . $value . "<br>";
            }
        }
    }
    
} else {
    echo "❌ La clase WVP_Analytics_Dashboard no existe<br>";
}

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
