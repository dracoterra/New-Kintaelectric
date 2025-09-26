<?php
/**
 * Script simple para verificar el archivo
 */

echo "<h1>🔍 Verificación de Archivo</h1>";

$file_path = __DIR__ . '/includes/class-wvp-analytics-dashboard.php';

if ( file_exists( $file_path ) ) {
    echo "✅ El archivo existe<br>";
    
    $content = file_get_contents( $file_path );
    $lines = explode( "\n", $content );
    echo "✅ Líneas totales: " . count( $lines ) . "<br>";
    
    // Verificar las últimas líneas
    $last_lines = array_slice( $lines, -5 );
    echo "<h3>📄 Últimas 5 líneas:</h3>";
    echo "<pre>";
    foreach ( $last_lines as $i => $line ) {
        echo (count($lines) - 5 + $i + 1) . ": " . htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
    
    // Verificar si hay llaves de cierre
    $open_braces = substr_count( $content, '{' );
    $close_braces = substr_count( $content, '}' );
    
    echo "✅ Llaves abiertas: {$open_braces}<br>";
    echo "✅ Llaves cerradas: {$close_braces}<br>";
    
    if ( $open_braces === $close_braces ) {
        echo "✅ Las llaves están balanceadas<br>";
    } else {
        echo "❌ Las llaves NO están balanceadas<br>";
    }
    
} else {
    echo "❌ El archivo no existe<br>";
}

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
