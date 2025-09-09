<?php
/**
 * Test simple del filtro
 * Accede a: /wp-content/themes/kintaelectric/test-filter-simple.php
 */

// Cargar WordPress
require_once('../../../wp-load.php');

echo "<h1>Test del Filtro de Productos</h1>";

// Verificar que WooCommerce esté activo
if (!class_exists('WooCommerce')) {
    echo "<p style='color: red;'>❌ WooCommerce no está activo</p>";
    exit;
}

echo "<p style='color: green;'>✅ WooCommerce está activo</p>";

// Verificar que la función de filtros existe
if (function_exists('kintaelectric_apply_product_filters')) {
    echo "<p style='color: green;'>✅ Función de filtros existe</p>";
} else {
    echo "<p style='color: red;'>❌ Función de filtros NO existe</p>";
}

// Verificar que el widget existe
if (class_exists('Electro_Products_Filter_Widget')) {
    echo "<p style='color: green;'>✅ Widget existe</p>";
} else {
    echo "<p style='color: red;'>❌ Widget NO existe</p>";
}

// Probar la URL del filtro
echo "<h2>Probando URL del filtro:</h2>";
echo "<p>URL: <a href='/shop/?filter_color%5B%5D=beige' target='_blank'>/shop/?filter_color%5B%5D=beige</a></p>";

// Simular la consulta de productos con filtro
echo "<h2>Simulando consulta con filtro:</h2>";

// Crear una consulta de prueba
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 10,
    'meta_query' => array(),
    'tax_query' => array()
);

// Simular el parámetro GET
$_GET['filter_color'] = array('beige');

// Aplicar la función de filtros
if (function_exists('kintaelectric_apply_product_filters')) {
    $query = new WP_Query($args);
    kintaelectric_apply_product_filters($query);
    
    echo "<p>Tax Query aplicada: " . print_r($query->get('tax_query'), true) . "</p>";
    echo "<p>Meta Query aplicada: " . print_r($query->get('meta_query'), true) . "</p>";
}

echo "<p><a href='/shop/'>Ir a la tienda</a></p>";
?>
