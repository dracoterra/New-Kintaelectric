<?php
/**
 * Script para verificar que no hay métodos de redondeo en el plugin
 * Acceder via: /wp-content/plugins/bcv-dolar-tracker/verify-no-rounding.php
 */

// Cargar WordPress
require_once('../../../wp-config.php');

echo "<h1>🔍 Verificación de Eliminación de Redondeos</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Incluir las clases necesarias
require_once('includes/class-bcv-security.php');
require_once('includes/class-bcv-database.php');
require_once('includes/class-bcv-scraper.php');

echo "<h2>1. Prueba de BCV_Security::sanitize_number()</h2>";

$test_prices = array(
    '161.888',
    '160.4479',
    '161.88800000',
    '160.0000',
    '161.123456789'
);

foreach ($test_prices as $price) {
    $sanitized = BCV_Security::sanitize_number($price);
    echo "<p><strong>Original:</strong> {$price} → <strong>Sanitizado:</strong> {$sanitized}</p>";
    
    // Verificar que no se redondeó
    if ($sanitized != $price) {
        echo "<p style='color: red;'>⚠️ <strong>ADVERTENCIA:</strong> El precio se modificó durante la sanitización</p>";
    } else {
        echo "<p style='color: green;'>✅ <strong>OK:</strong> El precio se preservó exactamente</p>";
    }
}

echo "<h2>2. Prueba de BCV_Security::validate_number_range()</h2>";

$test_range_prices = array(
    '161.888',
    '160.4479',
    '161.123456789'
);

foreach ($test_range_prices as $price) {
    $validated = BCV_Security::validate_number_range($price, 10, 500);
    echo "<p><strong>Original:</strong> {$price} → <strong>Validado:</strong> {$validated}</p>";
    
    // Verificar que no se redondeó
    if ($validated != $price) {
        echo "<p style='color: orange;'>ℹ️ <strong>INFO:</strong> El precio se ajustó por estar fuera del rango (10-500)</p>";
    } else {
        echo "<p style='color: green;'>✅ <strong>OK:</strong> El precio se preservó exactamente</p>";
    }
}

echo "<h2>3. Prueba de Scraping y Almacenamiento</h2>";

try {
    $scraper = new BCV_Scraper();
    $scraped_price = $scraper->scrape_bcv_rate();
    
    if ($scraped_price !== false) {
        echo "<p>✅ <strong>Precio obtenido del BCV:</strong> {$scraped_price} Bs.</p>";
        
        // Probar inserción
        $database = new BCV_Database();
        $result = $database->insert_price($scraped_price);
        
        if ($result === 'skipped') {
            echo "<p>⚠️ <strong>Precio no insertado:</strong> Lógica inteligente evitó duplicado</p>";
        } elseif ($result) {
            echo "<p>✅ <strong>Precio insertado exitosamente:</strong> ID {$result}</p>";
        } else {
            echo "<p>❌ <strong>Error al insertar precio</strong></p>";
        }
        
        // Verificar el último precio en la BD
        $latest_price = $database->get_latest_price();
        echo "<p><strong>Último precio en BD:</strong> {$latest_price} Bs.</p>";
        
        // Verificar que no se redondeó
        if ($latest_price != $scraped_price) {
            echo "<p style='color: red;'>⚠️ <strong>ADVERTENCIA:</strong> El precio se modificó durante el almacenamiento</p>";
            echo "<p><strong>Diferencia:</strong> " . abs($latest_price - $scraped_price) . "</p>";
        } else {
            echo "<p style='color: green;'>✅ <strong>OK:</strong> El precio se preservó exactamente en la BD</p>";
        }
        
    } else {
        echo "<p>❌ <strong>Error:</strong> No se pudo obtener precio del BCV</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<h2>4. Verificación de la Base de Datos</h2>";

global $wpdb;
$table_name = $wpdb->prefix . 'bcv_precio_dolar';
$latest_records = $wpdb->get_results("SELECT id, precio, datatime FROM {$table_name} ORDER BY datatime DESC LIMIT 3");

if ($latest_records) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Precio (Exacto)</th><th>Fecha</th></tr>";
    foreach ($latest_records as $record) {
        echo "<tr>";
        echo "<td>{$record->id}</td>";
        echo "<td><strong>{$record->precio}</strong></td>";
        echo "<td>{$record->datatime}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay registros en la base de datos</p>";
}

echo "<h2>5. Búsqueda de Métodos de Redondeo en el Código</h2>";

$files_to_check = array(
    'includes/class-bcv-security.php',
    'includes/class-bcv-database.php',
    'includes/class-bcv-scraper.php',
    'includes/class-bcv-cron.php',
    'admin/class-bcv-admin.php',
    'admin/class-bcv-admin-stats.php',
    'admin/class-bcv-prices-table.php'
);

$rounding_functions = array('round', 'intval', 'floor', 'ceil');

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo "<h3>Archivo: {$file}</h3>";
        
        foreach ($rounding_functions as $func) {
            $matches = preg_match_all('/' . preg_quote($func) . '\s*\([^)]*precio[^)]*\)/i', $content, $found);
            if ($matches > 0) {
                echo "<p style='color: red;'>⚠️ <strong>ADVERTENCIA:</strong> Encontrado {$func}() con 'precio' en {$file}</p>";
                foreach ($found[0] as $match) {
                    echo "<p style='margin-left: 20px; font-family: monospace;'>{$match}</p>";
                }
            }
        }
    }
}

echo "<hr>";
echo "<p><em>Este archivo se puede eliminar después de la verificación.</em></p>";
?>
