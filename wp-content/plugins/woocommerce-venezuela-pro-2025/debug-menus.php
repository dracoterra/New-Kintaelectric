<?php
/**
 * Debug Menu Creator - Identifica qué está creando menús duplicados
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    require_once( '../../../wp-config.php' );
}

// Hook para capturar todos los menús que se están registrando
add_action('admin_menu', function() {
    global $menu, $submenu;
    
    echo "<div style='background: white; padding: 20px; margin: 20px; border: 1px solid #ccc;'>";
    echo "<h2>🔍 Debug: Menús Registrados</h2>";
    
    echo "<h3>Menús Principales:</h3>";
    foreach ($menu as $item) {
        if (strpos($item[0], 'Analytics') !== false || strpos($item[0], 'Análisis') !== false || strpos($item[0], 'WVP') !== false) {
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px;'>";
            echo "<strong>Título:</strong> " . strip_tags($item[0]) . "<br>";
            echo "<strong>Slug:</strong> " . $item[2] . "<br>";
            echo "<strong>Capacidad:</strong> " . $item[1] . "<br>";
            echo "<strong>Posición:</strong> " . $item[4] . "<br>";
            echo "</div>";
        }
    }
    
    echo "<h3>Submenús de WVP:</h3>";
    if (isset($submenu['wvp-dashboard'])) {
        foreach ($submenu['wvp-dashboard'] as $subitem) {
            echo "<div style='background: #e8f5e8; padding: 10px; margin: 5px;'>";
            echo "<strong>Título:</strong> " . strip_tags($subitem[0]) . "<br>";
            echo "<strong>Slug:</strong> " . $subitem[2] . "<br>";
            echo "<strong>Capacidad:</strong> " . $subitem[1] . "<br>";
            echo "</div>";
        }
    }
    
    echo "<h3>Todos los Submenús:</h3>";
    foreach ($submenu as $parent_slug => $subitems) {
        foreach ($subitems as $subitem) {
            if (strpos($subitem[0], 'Analytics') !== false || strpos($subitem[0], 'Análisis') !== false) {
                echo "<div style='background: #fff3cd; padding: 10px; margin: 5px;'>";
                echo "<strong>Padre:</strong> " . $parent_slug . "<br>";
                echo "<strong>Título:</strong> " . strip_tags($subitem[0]) . "<br>";
                echo "<strong>Slug:</strong> " . $subitem[2] . "<br>";
                echo "<strong>Capacidad:</strong> " . $subitem[1] . "<br>";
                echo "</div>";
            }
        }
    }
    
    echo "</div>";
}, 999); // Prioridad alta para ejecutar después de todos los otros menús

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Menús - WVP 2025</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .info { background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .success { background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Debug de Menús Duplicados</h1>
            <p>Identificando qué está creando los menús de Analytics/Análisis</p>
        </div>
        
        <div class="info">
            <h3>📋 Instrucciones:</h3>
            <ol>
                <li>Ve a cualquier página de administración de WordPress</li>
                <li>Los menús registrados aparecerán abajo</li>
                <li>Busca duplicados de "Analytics" o "Análisis"</li>
                <li>Identifica qué clase/función los está creando</li>
            </ol>
        </div>
        
        <div class="warning">
            <h3>⚠️ Problema Identificado:</h3>
            <p>Tienes dos secciones de análisis en el menú:</p>
            <ul>
                <li><strong>"Analytics"</strong> - Funciona correctamente</li>
                <li><strong>"Análisis"</strong> - No funciona (probablemente de WooCommerce)</li>
            </ul>
        </div>
        
        <div class="success">
            <h3>✅ Solución:</h3>
            <p>Una vez identificado el origen del menú duplicado, podremos:</p>
            <ul>
                <li>Eliminar el menú que no funciona</li>
                <li>Integrar ambos en uno solo</li>
                <li>Renombrar para evitar confusión</li>
            </ul>
        </div>
        
        <div class="info">
            <h3>🔗 Enlaces para Probar:</h3>
            <ul>
                <li><a href="/wp-admin/admin.php?page=wvp-dashboard">WVP Dashboard</a></li>
                <li><a href="/wp-admin/admin.php?page=wvp-analytics">WVP Analytics</a></li>
                <li><a href="/wp-admin/admin.php?page=wc-admin&path=/analytics/overview">WooCommerce Analytics</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
