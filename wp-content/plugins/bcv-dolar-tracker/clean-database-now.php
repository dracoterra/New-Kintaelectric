<?php
/**
 * Script temporal para limpiar datos incorrectos de la base de datos
 * Acceder desde: /wp-content/plugins/bcv-dolar-tracker/clean-database-now.php
 */

// Cargar WordPress
require_once('../../../wp-config.php');

// Cargar las clases necesarias
require_once('includes/class-bcv-database.php');

echo "<h1>=== LIMPIEZA DE DATOS INCORRECTOS ===</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

$database = new BCV_Database();

// Mostrar estadísticas ANTES de la limpieza
echo "<h2>Estadísticas ANTES de la limpieza:</h2>";
$stats_before = $database->get_price_stats(true);
echo "<ul>";
echo "<li><strong>Total de registros:</strong> " . $stats_before['total_records'] . "</li>";
echo "<li><strong>Último precio:</strong> " . $stats_before['last_price'] . "</li>";
echo "<li><strong>Precio mínimo:</strong> " . $stats_before['min_price'] . "</li>";
echo "<li><strong>Precio máximo:</strong> " . $stats_before['max_price'] . "</li>";
echo "<li><strong>Precio promedio:</strong> " . $stats_before['avg_price'] . "</li>";
echo "</ul>";

// Ejecutar limpieza
echo "<h2>Ejecutando limpieza...</h2>";
$result = $database->cleanup_incorrect_data();

if ($result['success']) {
    echo "<p style='color: green;'><strong>✅ Limpieza exitosa:</strong> " . $result['message'] . "</p>";
    echo "<p><strong>Registros eliminados:</strong> " . $result['deleted_count'] . "</p>";
} else {
    echo "<p style='color: red;'><strong>❌ Error en la limpieza:</strong> " . $result['message'] . "</p>";
}

// Mostrar estadísticas DESPUÉS de la limpieza
echo "<h2>Estadísticas DESPUÉS de la limpieza:</h2>";
$stats_after = $database->get_price_stats(true);
echo "<ul>";
echo "<li><strong>Total de registros:</strong> " . $stats_after['total_records'] . "</li>";
echo "<li><strong>Último precio:</strong> " . $stats_after['last_price'] . "</li>";
echo "<li><strong>Precio mínimo:</strong> " . $stats_after['min_price'] . "</li>";
echo "<li><strong>Precio máximo:</strong> " . $stats_after['max_price'] . "</li>";
echo "<li><strong>Precio promedio:</strong> " . $stats_after['avg_price'] . "</li>";
echo "</ul>";

// Mostrar los últimos registros después de la limpieza
echo "<h2>Últimos 10 registros después de la limpieza:</h2>";
global $wpdb;
$table_name = $wpdb->prefix . 'bcv_precio_dolar';
$recent_prices = $wpdb->get_results("SELECT id, precio, datatime FROM {$table_name} ORDER BY datatime DESC LIMIT 10");
echo "<ul>";
foreach ($recent_prices as $price) {
    echo "<li>ID: {$price->id}, Precio: {$price->precio}, Fecha: {$price->datatime}</li>";
}
echo "</ul>";

// Mostrar datos que aún podrían ser problemáticos
echo "<h2>Datos que aún podrían ser problemáticos (fuera del rango 10-500 Bs.):</h2>";
$problematic_prices = $wpdb->get_results("SELECT id, precio, datatime FROM {$table_name} WHERE precio > 500 OR precio < 10 ORDER BY datatime DESC");
if ($problematic_prices) {
    echo "<ul>";
    foreach ($problematic_prices as $price) {
        echo "<li>ID: {$price->id}, Precio: {$price->precio}, Fecha: {$price->datatime}</li>";
    }
    echo "</ul>";
    echo "<p style='color: orange;'><strong>⚠️ Aún hay datos fuera del rango realista (10-500 Bs.).</strong></p>";
} else {
    echo "<p style='color: green;'><strong>✅ Todos los datos están en el rango realista (10-500 Bs.).</strong></p>";
}

echo "<h2>=== FIN DE LA LIMPIEZA ===</h2>";
echo "<p><em>Este archivo se puede eliminar después de la limpieza.</em></p>";
?>
