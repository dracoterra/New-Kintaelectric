<?php
/**
 * Debug file para verificar filtros
 * Accede a: /wp-content/themes/kintaelectric/debug-filters.php
 */

// Cargar WordPress
require_once('../../../wp-load.php');

echo "<h1>Debug de Filtros</h1>";

// Verificar que WooCommerce esté activo
if (!class_exists('WooCommerce')) {
    echo "<p style='color: red;'>❌ WooCommerce no está activo</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ WooCommerce está activo</p>";
}

// Verificar que la función existe
if (function_exists('kintaelectric_apply_product_filters')) {
    echo "<p style='color: green;'>✅ Función kintaelectric_apply_product_filters existe</p>";
} else {
    echo "<p style='color: red;'>❌ Función kintaelectric_apply_product_filters NO existe</p>";
}

// Verificar que el widget existe
if (class_exists('Electro_Products_Filter_Widget')) {
    echo "<p style='color: green;'>✅ Widget Electro_Products_Filter_Widget existe</p>";
} else {
    echo "<p style='color: red;'>❌ Widget Electro_Products_Filter_Widget NO existe</p>";
}

// Verificar atributos de producto
$attributes = wc_get_attribute_taxonomies();
if (!empty($attributes)) {
    echo "<p style='color: green;'>✅ Atributos encontrados: " . count($attributes) . "</p>";
    foreach ($attributes as $attribute) {
        echo "<p>- " . $attribute->attribute_label . " (" . $attribute->attribute_name . ")</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No hay atributos configurados</p>";
}

// Verificar productos
$products = wc_get_products(array('limit' => 5));
if (!empty($products)) {
    echo "<p style='color: green;'>✅ Productos encontrados: " . count($products) . "</p>";
} else {
    echo "<p style='color: red;'>❌ No hay productos</p>";
}

echo "<p><a href='/shop/'>Ir a la tienda</a></p>";
?>
