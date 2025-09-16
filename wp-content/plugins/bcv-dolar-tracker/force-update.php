<?php
/**
 * Script para forzar la actualización del dólar en la base de datos
 * Acceder desde: /wp-content/plugins/bcv-dolar-tracker/force-update.php
 */

// Cargar WordPress
require_once('../../../wp-config.php');

// Cargar las clases necesarias
require_once('includes/class-bcv-scraper.php');
require_once('includes/class-bcv-database.php');

echo "<h1>=== FORZAR ACTUALIZACIÓN DEL DÓLAR ===</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Mostrar estadísticas ANTES de la actualización
echo "<h2>📊 Estadísticas ANTES de la actualización:</h2>";
$database = new BCV_Database();
$stats_before = $database->get_price_stats(true);
echo "<ul>";
echo "<li><strong>Total de registros:</strong> " . $stats_before['total_records'] . "</li>";
echo "<li><strong>Último precio:</strong> " . number_format($stats_before['last_price'], 4, ',', '.') . " Bs.</li>";
echo "<li><strong>Precio mínimo:</strong> " . number_format($stats_before['min_price'], 4, ',', '.') . " Bs.</li>";
echo "<li><strong>Precio máximo:</strong> " . number_format($stats_before['max_price'], 4, ',', '.') . " Bs.</li>";
echo "<li><strong>Precio promedio:</strong> " . number_format($stats_before['avg_price'], 4, ',', '.') . " Bs.</li>";
echo "</ul>";

// Paso 1: Forzar scraping del BCV
echo "<h2>🔍 Paso 1: Scraping del BCV</h2>";
$scraper = new BCV_Scraper();

// Limpiar caché para asegurar una nueva petición
$scraper->clear_cache();
echo "<p>✅ Caché limpiado</p>";

echo "<p>🔄 Iniciando scraping del BCV...</p>";
$rate = $scraper->scrape_bcv_rate();

if ($rate !== false) {
    echo "<p style='color: green;'><strong>✅ ÉXITO:</strong> Precio obtenido del BCV: <strong>" . number_format($rate, 4, ',', '.') . " Bs.</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ ERROR:</strong> No se pudo obtener el precio del BCV.</p>";
    echo "<p>Detalles del error en el debug.log</p>";
    exit;
}

// Paso 2: Forzar inserción en la base de datos (saltándose restricciones)
echo "<h2>💾 Paso 2: Inserción forzada en la base de datos</h2>";

// Obtener el último registro para comparar
global $wpdb;
$table_name = $wpdb->prefix . 'bcv_precio_dolar';
$ultimo_registro = $wpdb->get_row("SELECT * FROM {$table_name} ORDER BY datatime DESC LIMIT 1");

if ($ultimo_registro) {
    echo "<p><strong>Último registro en BD:</strong> " . number_format($ultimo_registro->precio, 4, ',', '.') . " Bs. (ID: {$ultimo_registro->id}, Fecha: {$ultimo_registro->datatime})</p>";
    
    // Comparar precios
    $precio_anterior = floatval($ultimo_registro->precio);
    $precio_nuevo = floatval($rate);
    $diferencia = abs($precio_nuevo - $precio_anterior);
    
    echo "<p><strong>Comparación:</strong></p>";
    echo "<ul>";
    echo "<li>Precio anterior: " . number_format($precio_anterior, 4, ',', '.') . " Bs.</li>";
    echo "<li>Precio nuevo: " . number_format($precio_nuevo, 4, ',', '.') . " Bs.</li>";
    echo "<li>Diferencia: " . number_format($diferencia, 4, ',', '.') . " Bs.</li>";
    echo "</ul>";
} else {
    echo "<p>⚠️ No hay registros anteriores en la base de datos</p>";
}

// Forzar inserción (saltándose la lógica de duplicados)
echo "<p>🔄 Insertando precio en la base de datos (forzado)...</p>";

$datatime = current_time('mysql');
$inserted = $wpdb->insert(
    $table_name,
    array(
        'datatime' => $datatime,
        'precio' => $rate
    ),
    array('%s', '%s')
);

if ($inserted) {
    $new_id = $wpdb->insert_id;
    echo "<p style='color: green;'><strong>✅ ÉXITO:</strong> Precio insertado con ID: <strong>{$new_id}</strong></p>";
    echo "<p><strong>Datos insertados:</strong></p>";
    echo "<ul>";
    echo "<li>ID: {$new_id}</li>";
    echo "<li>Precio: " . number_format($rate, 4, ',', '.') . " Bs.</li>";
    echo "<li>Fecha: {$datatime}</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>❌ ERROR:</strong> No se pudo insertar el precio en la base de datos.</p>";
    echo "<p>Error: " . $wpdb->last_error . "</p>";
}

// Paso 3: Mostrar estadísticas DESPUÉS de la actualización
echo "<h2>📊 Estadísticas DESPUÉS de la actualización:</h2>";
$stats_after = $database->get_price_stats(true); // Forzar recarga
echo "<ul>";
echo "<li><strong>Total de registros:</strong> " . $stats_after['total_records'] . "</li>";
echo "<li><strong>Último precio:</strong> " . number_format($stats_after['last_price'], 4, ',', '.') . " Bs.</li>";
echo "<li><strong>Precio mínimo:</strong> " . number_format($stats_after['min_price'], 4, ',', '.') . " Bs.</li>";
echo "<li><strong>Precio máximo:</strong> " . number_format($stats_after['max_price'], 4, ',', '.') . " Bs.</li>";
echo "<li><strong>Precio promedio:</strong> " . number_format($stats_after['avg_price'], 4, ',', '.') . " Bs.</li>";
echo "</ul>";

// Paso 4: Mostrar los últimos 5 registros
echo "<h2>📋 Últimos 5 registros en la base de datos:</h2>";
$recent_prices = $wpdb->get_results("SELECT id, precio, datatime FROM {$table_name} ORDER BY datatime DESC LIMIT 5");
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
echo "<thead><tr style='background: #f0f0f0;'><th>ID</th><th>Precio (Bs.)</th><th>Fecha y Hora</th></tr></thead>";
echo "<tbody>";
foreach ($recent_prices as $price) {
    $is_new = ($price->id == $new_id) ? " style='background: #d4edda; font-weight: bold;'" : "";
    echo "<tr{$is_new}>";
    echo "<td>{$price->id}</td>";
    echo "<td>" . number_format($price->precio, 4, ',', '.') . "</td>";
    echo "<td>{$price->datatime}</td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";

// Paso 5: Sincronizar con WooCommerce Venezuela Pro
echo "<h2>🔄 Paso 5: Sincronización con WooCommerce Venezuela Pro</h2>";
if (class_exists('WVP_Price_Calculator')) {
    // Actualizar la opción que usa WooCommerce Venezuela Pro
    update_option('wvp_bcv_rate', $rate);
    
    // Disparar el hook para que WVP actualice su caché
    do_action('wvp_bcv_rate_updated', $rate);
    
    echo "<p style='color: green;'><strong>✅ Sincronización exitosa:</strong> El precio se ha actualizado en WooCommerce Venezuela Pro</p>";
    echo "<p><strong>Precio sincronizado:</strong> " . number_format($rate, 4, ',', '.') . " Bs.</p>";
} else {
    echo "<p style='color: orange;'><strong>⚠️ Advertencia:</strong> WooCommerce Venezuela Pro no está disponible</p>";
}

echo "<h2>=== FIN DE LA ACTUALIZACIÓN FORZADA ===</h2>";
echo "<p><em>Este archivo se puede eliminar después de la prueba.</em></p>";
echo "<p><a href='admin.php?page=bcv-dolar-tracker' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Volver al Plugin</a></p>";
?>
