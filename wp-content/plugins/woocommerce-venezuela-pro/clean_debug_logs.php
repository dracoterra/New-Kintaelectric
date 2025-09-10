<?php
/**
 * Script para limpiar logs de debug del plugin
 */

// Leer el archivo
$file_path = 'frontend/class-wvp-checkout.php';
$content = file_get_contents($file_path);

// Patrones de logs de debug a eliminar
$patterns = [
    "/\s*error_log\('WVP Debug:.*?'\);\s*\n/",
    "/\s*error_log\('WVP Debug:.*?'\);\s*/",
    "/\s*error_log\('WVP Debug:.*?\);\s*\n/",
    "/\s*error_log\('WVP Debug:.*?\);\s*/",
];

// Aplicar patrones
foreach ($patterns as $pattern) {
    $content = preg_replace($pattern, '', $content);
}

// Limpiar líneas vacías múltiples
$content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

// Guardar el archivo
file_put_contents($file_path, $content);

echo "Logs de debug limpiados en $file_path\n";
?>
