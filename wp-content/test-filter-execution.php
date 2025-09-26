<?php
// Test específico para simular ejecución de filtros WooCommerce
require_once('C:\Users\ronal\Local Sites\new-kinta-electric\app\public\wp-config.php');

echo "<h1>🔍 Test de Ejecución de Filtros WooCommerce</h1>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

if (class_exists('WVP_Simple_Currency_Converter')) {
    $converter = WVP_Simple_Currency_Converter::get_instance();
    
    echo "<h2>Test de métodos individuales:</h2>";
    
    // Simular datos de carrito
    $cart_item = [
        'data' => wc_get_product(65), // V-Neck T-Shirt
        'quantity' => 1
    ];
    $cart_item_key = 'test_key';
    
    if ($cart_item['data']) {
        echo "✅ Producto cargado: " . $cart_item['data']->get_name() . "<br>";
        echo "Precio USD: $" . $cart_item['data']->get_price() . "<br>";
        
        // Test del método display_cart_item_price
        $original_price = '$' . $cart_item['data']->get_price();
        echo "<h3>Test display_cart_item_price:</h3>";
        echo "Precio original: $original_price<br>";
        
        $modified_price = $converter->display_cart_item_price($original_price, $cart_item, $cart_item_key);
        echo "Precio modificado: $modified_price<br>";
        
        // Test del método display_cart_item_subtotal
        $original_subtotal = '$' . ($cart_item['data']->get_price() * $cart_item['quantity']);
        echo "<h3>Test display_cart_item_subtotal:</h3>";
        echo "Subtotal original: $original_subtotal<br>";
        
        $modified_subtotal = $converter->display_cart_item_subtotal($original_subtotal, $cart_item, $cart_item_key);
        echo "Subtotal modificado: $modified_subtotal<br>";
        
        // Test del método display_dual_price
        $original_html = '<span class="price">$' . $cart_item['data']->get_price() . '</span>';
        echo "<h3>Test display_dual_price:</h3>";
        echo "HTML original: $original_html<br>";
        
        $modified_html = $converter->display_dual_price($original_html, $cart_item['data']);
        echo "HTML modificado: $modified_html<br>";
        
    } else {
        echo "❌ No se pudo cargar el producto<br>";
    }
    
    // Test de verificación de tasa disponible
    echo "<h2>Test de verificación de tasa:</h2>";
    if ($converter->is_rate_available()) {
        echo "✅ Tasa BCV disponible<br>";
    } else {
        echo "❌ Tasa BCV NO disponible<br>";
    }
    
    $rate = $converter->get_bcv_rate();
    echo "Tasa actual: $rate<br>";
    
} else {
    echo "❌ Clase WVP_Simple_Currency_Converter NO existe<br>";
}

echo "<p><strong>Test completado: " . date('Y-m-d H:i:s') . "</strong></p>";
?>
