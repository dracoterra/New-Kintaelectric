<?php
/**
 * Script para verificar sintaxis PHP
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Verificación de Sintaxis</h1>";

// Verificar si hay errores de sintaxis
$file_path = plugin_dir_path( __FILE__ ) . 'includes/class-wvp-analytics-dashboard.php';

if ( file_exists( $file_path ) ) {
    echo "✅ El archivo existe<br>";
    
    // Verificar sintaxis
    $output = shell_exec( "php -l \"$file_path\" 2>&1" );
    
    if ( strpos( $output, 'No syntax errors' ) !== false ) {
        echo "✅ No hay errores de sintaxis<br>";
    } else {
        echo "❌ Errores de sintaxis encontrados:<br>";
        echo "<pre>" . htmlspecialchars( $output ) . "</pre>";
    }
} else {
    echo "❌ El archivo no existe<br>";
}

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
