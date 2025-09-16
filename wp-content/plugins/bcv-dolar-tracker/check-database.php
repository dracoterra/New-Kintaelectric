<?php
/**
 * Script temporal para revisar la base de datos
 * Acceder desde: /wp-content/plugins/bcv-dolar-tracker/check-database.php
 */

// Cargar WordPress
require_once('../../../wp-config.php');

// Cargar las clases necesarias
require_once('includes/class-bcv-database.php');

echo "<h1>=== REVISIÓN DE LA BASE DE DATOS ===</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

$database = new BCV_Database();

// Obtener estadísticas actuales
$stats = $database->get_price_stats(true);
echo "<h2>Estadísticas actuales:</h2>";
echo "<ul>";
echo "<li><strong>Total de registros:</strong> " . $stats['total_records'] . "</li>";
echo "<li><strong>Último precio:</strong> " . $stats['last_price'] . "</li>";
echo "<li><strong>Precio mínimo:</strong> " . $stats['min_price'] . "</li>";
echo "<li><strong>Precio máximo:</strong> " . $stats['max_price'] . "</li>";
echo "<li><strong>Precio promedio:</strong> " . $stats['avg_price'] . "</li>";
echo "</ul>";

// Buscar datos incorrectos
global $wpdb;
$table_name = $wpdb->prefix . 'bcv_precio_dolar';

echo "<h2>Búsqueda de datos incorrectos:</h2>";

// Precios muy altos (mayores a 1000)
$high_prices = $wpdb->get_results("SELECT id, precio, datatime FROM {$table_name} WHERE precio > 1000 ORDER BY precio DESC LIMIT 10");
echo "<h3>Precios muy altos (>1000):</h3>";
if ($high_prices) {
    echo "<ul>";
    foreach ($high_prices as $price) {
        echo "<li>ID: {$price->id}, Precio: {$price->precio}, Fecha: {$price->datatime}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No se encontraron precios muy altos.</p>";
}

// Precios muy bajos (menores a 1)
$low_prices = $wpdb->get_results("SELECT id, precio, datatime FROM {$table_name} WHERE precio < 1 ORDER BY precio ASC LIMIT 10");
echo "<h3>Precios muy bajos (<1):</h3>";
if ($low_prices) {
    echo "<ul>";
    foreach ($low_prices as $price) {
        echo "<li>ID: {$price->id}, Precio: {$price->precio}, Fecha: {$price->datatime}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No se encontraron precios muy bajos.</p>";
}

// Últimos 10 registros
$recent_prices = $wpdb->get_results("SELECT id, precio, datatime FROM {$table_name} ORDER BY datatime DESC LIMIT 10");
echo "<h3>Últimos 10 registros:</h3>";
echo "<ul>";
foreach ($recent_prices as $price) {
    echo "<li>ID: {$price->id}, Precio: {$price->precio}, Fecha: {$price->datatime}</li>";
}
echo "</ul>";

echo "<h2>=== FIN DE LA REVISIÓN ===</h2>";
echo "<p><em>Este archivo se puede eliminar después de la revisión.</em></p>";
?>
