<?php
/**
 * Test completo del filtro con Ver más/Ver menos
 * Accede a: /wp-content/themes/kintaelectric/test-filter-complete.php
 */

// Cargar WordPress
require_once('../../../wp-load.php');

echo "<h1>Test Completo del Filtro</h1>";

// Verificar que WooCommerce esté activo
if (!class_exists('WooCommerce')) {
    echo "<p style='color: red;'>❌ WooCommerce no está activo</p>";
    exit;
}

echo "<p style='color: green;'>✅ WooCommerce está activo</p>";

// Crear una instancia del widget para probar
if (class_exists('Electro_Products_Filter_Widget')) {
    $widget = new Electro_Products_Filter_Widget();
    
    // Configuración de prueba
    $instance = array(
        'title' => 'Filtros de Prueba',
        'selected_attributes' => array('color'),
        'max_items' => 3
    );
    
    echo "<h2>Widget de Filtro (Ver más/Ver menos):</h2>";
    echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 20px 0;'>";
    
    // Simular el contexto de la tienda
    global $wp_query;
    $original_query = $wp_query;
    $wp_query = new WP_Query(array('post_type' => 'product'));
    
    // Renderizar el widget
    $widget->widget(array(
        'before_widget' => '<div class="widget_electro_products_filter">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ), $instance);
    
    echo "</div>";
    
    // Restaurar query original
    $wp_query = $original_query;
    
} else {
    echo "<p style='color: red;'>❌ Widget no disponible</p>";
}

echo "<h2>Instrucciones de Prueba:</h2>";
echo "<ol>";
echo "<li>Ve a la tienda: <a href='/shop/' target='_blank'>/shop/</a></li>";
echo "<li>Busca el widget de filtros en la sidebar</li>";
echo "<li>Si hay más de 3 opciones en un filtro, deberías ver el botón '+ Ver más'</li>";
echo "<li>Haz click en '+ Ver más' para mostrar más opciones</li>";
echo "<li>El botón debería cambiar a '- Ver menos'</li>";
echo "<li>Haz click en '- Ver menos' para ocultar las opciones adicionales</li>";
echo "</ol>";

echo "<h2>Debug:</h2>";
echo "<p>Abre la consola del navegador (F12) para ver los mensajes de debug del JavaScript.</p>";

echo "<p><a href='/shop/'>Ir a la tienda</a></p>";
?>
