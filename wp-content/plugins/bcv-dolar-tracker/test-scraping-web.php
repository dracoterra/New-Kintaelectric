<?php
/**
 * Script temporal para probar el scraping del BCV via web
 * Acceder desde: /wp-content/plugins/bcv-dolar-tracker/test-scraping-web.php
 */

// Cargar WordPress
require_once('../../../wp-config.php');

// Cargar las clases necesarias
require_once('includes/class-bcv-scraper.php');
require_once('includes/class-bcv-database.php');

echo "<h1>=== PRUEBA DE SCRAPING AL BCV ===</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Crear instancia del scraper
$scraper = new BCV_Scraper();

echo "<h2>1. Iniciando scraping del BCV...</h2>";
$rate = $scraper->scrape_bcv_rate();

if ($rate !== false) {
    echo "<p style='color: green;'><strong>✅ ÉXITO:</strong> Precio obtenido del BCV: <strong>{$rate} Bs.</strong></p>";
    
    // Mostrar detalles del scraping
    $scraping_info = $scraper->get_scraping_info();
    echo "<h3>--- DETALLES DEL SCRAPING ---</h3>";
    echo "<ul>";
    echo "<li><strong>Total de intentos:</strong> " . ($scraping_info['total_scrapings'] ?? 'N/A') . "</li>";
    echo "<li><strong>Exitosos:</strong> " . ($scraping_info['successful_scrapings'] ?? 'N/A') . "</li>";
    echo "<li><strong>Fallidos:</strong> " . ($scraping_info['failed_scrapings'] ?? 'N/A') . "</li>";
    echo "</ul>";
    
    // Probar inserción en base de datos
    echo "<h2>2. Probando inserción en base de datos...</h2>";
    $database = new BCV_Database();
    $result = $database->insert_price($rate);
    
    if ($result === 'skipped') {
        echo "<p style='color: orange;'><strong>⚠️ Precio no guardado</strong> - Lógica inteligente evitó duplicado</p>";
    } elseif ($result !== false) {
        echo "<p style='color: green;'><strong>✅ Precio guardado</strong> con ID: {$result}</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Error</strong> al guardar precio en base de datos</p>";
    }
    
    // Mostrar estadísticas actuales
    echo "<h2>3. Estadísticas actuales de la base de datos:</h2>";
    $stats = $database->get_price_stats(true);
    echo "<ul>";
    echo "<li><strong>Total de registros:</strong> " . $stats['total_records'] . "</li>";
    echo "<li><strong>Último precio:</strong> " . $stats['last_price'] . "</li>";
    echo "<li><strong>Precio mínimo:</strong> " . $stats['min_price'] . "</li>";
    echo "<li><strong>Precio máximo:</strong> " . $stats['max_price'] . "</li>";
    echo "<li><strong>Precio promedio:</strong> " . $stats['avg_price'] . "</li>";
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'><strong>❌ ERROR:</strong> No se pudo obtener el precio del BCV</p>";
    
    // Mostrar información de debug
    $scraping_info = $scraper->get_scraping_info();
    echo "<h3>--- INFORMACIÓN DE DEBUG ---</h3>";
    echo "<ul>";
    echo "<li><strong>Total de intentos:</strong> " . ($scraping_info['total_scrapings'] ?? 'N/A') . "</li>";
    echo "<li><strong>Exitosos:</strong> " . ($scraping_info['successful_scrapings'] ?? 'N/A') . "</li>";
    echo "<li><strong>Fallidos:</strong> " . ($scraping_info['failed_scrapings'] ?? 'N/A') . "</li>";
    echo "</ul>";
}

echo "<h2>=== FIN DE LA PRUEBA ===</h2>";
echo "<p><em>Este archivo se puede eliminar después de la prueba.</em></p>";
?>
