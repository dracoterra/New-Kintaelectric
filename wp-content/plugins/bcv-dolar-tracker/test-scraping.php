<?php
/**
 * Script temporal para probar el scraping del BCV
 * Este archivo se puede eliminar después de la prueba
 */

// Cargar WordPress
require_once('../../../wp-config.php');

// Cargar las clases necesarias
require_once('includes/class-bcv-scraper.php');
require_once('includes/class-bcv-database.php');

echo "=== PRUEBA DE SCRAPING AL BCV ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Crear instancia del scraper
$scraper = new BCV_Scraper();

echo "1. Iniciando scraping del BCV...\n";
$rate = $scraper->scrape_bcv_rate();

if ($rate !== false) {
    echo "✅ ÉXITO: Precio obtenido del BCV: {$rate} Bs.\n";
    
    // Mostrar detalles del scraping
    $scraping_info = $scraper->get_scraping_info();
    echo "\n--- DETALLES DEL SCRAPING ---\n";
    echo "Total de intentos: " . ($scraping_info['total_scrapings'] ?? 'N/A') . "\n";
    echo "Exitosos: " . ($scraping_info['successful_scrapings'] ?? 'N/A') . "\n";
    echo "Fallidos: " . ($scraping_info['failed_scrapings'] ?? 'N/A') . "\n";
    
    // Probar inserción en base de datos
    echo "\n2. Probando inserción en base de datos...\n";
    $database = new BCV_Database();
    $result = $database->insert_price($rate);
    
    if ($result === 'skipped') {
        echo "⚠️  Precio no guardado - Lógica inteligente evitó duplicado\n";
    } elseif ($result !== false) {
        echo "✅ Precio guardado con ID: {$result}\n";
    } else {
        echo "❌ Error al guardar precio en base de datos\n";
    }
    
    // Mostrar estadísticas actuales
    echo "\n3. Estadísticas actuales de la base de datos:\n";
    $stats = $database->get_price_stats(true);
    echo "Total de registros: " . $stats['total_records'] . "\n";
    echo "Último precio: " . $stats['last_price'] . "\n";
    echo "Precio mínimo: " . $stats['min_price'] . "\n";
    echo "Precio máximo: " . $stats['max_price'] . "\n";
    echo "Precio promedio: " . $stats['avg_price'] . "\n";
    
} else {
    echo "❌ ERROR: No se pudo obtener el precio del BCV\n";
    
    // Mostrar información de debug
    $scraping_info = $scraper->get_scraping_info();
    echo "\n--- INFORMACIÓN DE DEBUG ---\n";
    echo "Total de intentos: " . ($scraping_info['total_scrapings'] ?? 'N/A') . "\n";
    echo "Exitosos: " . ($scraping_info['successful_scrapings'] ?? 'N/A') . "\n";
    echo "Fallidos: " . ($scraping_info['failed_scrapings'] ?? 'N/A') . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
?>
