<?php
/**
 * Script para diagnosticar el problema del debug.log corrupto
 */

echo "<h1>🔍 Diagnóstico del Debug.log Corrupto</h1>";

// Verificar configuración de WordPress
echo "<h2>1. Configuración de WordPress</h2>";
echo "WP_DEBUG: " . (defined('WP_DEBUG') && WP_DEBUG ? '✅ Activado' : '❌ Desactivado') . "<br>";
echo "WP_DEBUG_LOG: " . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '✅ Activado' : '❌ Desactivado') . "<br>";
echo "WP_DEBUG_DISPLAY: " . (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? '✅ Activado' : '❌ Desactivado') . "<br>";

// Verificar ubicación del debug.log
echo "<h2>2. Ubicación del Debug.log</h2>";
$debug_log_path = WP_CONTENT_DIR . '/debug.log';
echo "Ruta esperada: " . $debug_log_path . "<br>";
echo "Existe: " . (file_exists($debug_log_path) ? '✅ Sí' : '❌ No') . "<br>";

if (file_exists($debug_log_path)) {
    echo "Tamaño: " . filesize($debug_log_path) . " bytes<br>";
    echo "Permisos: " . substr(sprintf('%o', fileperms($debug_log_path)), -4) . "<br>";
    
    // Leer primeras líneas
    $content = file_get_contents($debug_log_path);
    echo "Primeros 200 caracteres:<br>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "</pre>";
    
    // Verificar si hay caracteres extraños
    if (preg_match('/[^\x20-\x7E\s]/', $content)) {
        echo "❌ Contiene caracteres no ASCII<br>";
    } else {
        echo "✅ Solo caracteres ASCII<br>";
    }
}

// Verificar configuración de PHP
echo "<h2>3. Configuración de PHP</h2>";
echo "Versión PHP: " . phpversion() . "<br>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . "<br>";
echo "Error reporting: " . error_reporting() . "<br>";

// Verificar si hay errores recientes
echo "<h2>4. Errores Recientes</h2>";
if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error) {
        echo "Último error PHP:<br>";
        echo "Tipo: " . $last_error['type'] . "<br>";
        echo "Mensaje: " . $last_error['message'] . "<br>";
        echo "Archivo: " . $last_error['file'] . "<br>";
        echo "Línea: " . $last_error['line'] . "<br>";
    } else {
        echo "✅ No hay errores PHP recientes<br>";
    }
}

// Crear debug.log limpio
echo "<h2>5. Creando Debug.log Limpio</h2>";
$clean_content = "# WordPress Debug Log - CLEAN START\n";
$clean_content .= "# Created: " . date('Y-m-d H:i:s') . "\n";
$clean_content .= "# PHP Version: " . phpversion() . "\n";
$clean_content .= "# WordPress Version: " . get_bloginfo('version') . "\n";

if (file_put_contents($debug_log_path, $clean_content)) {
    echo "✅ Debug.log creado limpiamente<br>";
} else {
    echo "❌ No se pudo crear debug.log<br>";
}

echo "<h2>🎯 Recomendaciones</h2>";
echo "1. Verificar que WP_DEBUG esté activado en wp-config.php<br>";
echo "2. Verificar permisos de escritura en wp-content/<br>";
echo "3. Revisar si hay plugins que estén escribiendo al debug.log<br>";
echo "4. Considerar usar un plugin de logging alternativo<br>";

echo "<hr>";
echo "<p><strong>Diagnóstico ejecutado:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
