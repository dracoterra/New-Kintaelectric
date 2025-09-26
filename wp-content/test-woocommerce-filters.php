<?php
// Test específico para verificar filtros de WooCommerce del Currency Converter
require_once('C:\Users\ronal\Local Sites\new-kinta-electric\app\public\wp-config.php');

echo "<h1>🔍 Test de Filtros WooCommerce - Currency Converter</h1>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

// Verificar si el plugin está cargado
if (class_exists('WVP_Simple_Currency_Converter')) {
    echo "✅ Clase WVP_Simple_Currency_Converter existe<br>";
    
    $converter = WVP_Simple_Currency_Converter::get_instance();
    echo "✅ Instancia creada<br>";
    
    // Verificar métodos específicos
    if (method_exists($converter, 'display_cart_item_price')) {
        echo "✅ Método display_cart_item_price existe<br>";
    } else {
        echo "❌ Método display_cart_item_price NO existe<br>";
    }
    
    if (method_exists($converter, 'display_cart_item_subtotal')) {
        echo "✅ Método display_cart_item_subtotal existe<br>";
    } else {
        echo "❌ Método display_cart_item_subtotal NO existe<br>";
    }
    
    if (method_exists($converter, 'display_dual_price')) {
        echo "✅ Método display_dual_price existe<br>";
    } else {
        echo "❌ Método display_dual_price NO existe<br>";
    }
    
    // Verificar si los filtros están registrados
    echo "<h2>Verificando filtros registrados:</h2>";
    
    global $wp_filter;
    
    $filters_to_check = [
        'woocommerce_cart_item_price',
        'woocommerce_cart_item_subtotal', 
        'woocommerce_get_price_html',
        'woocommerce_currency_symbol',
        'woocommerce_price_format'
    ];
    
    foreach ($filters_to_check as $filter) {
        if (isset($wp_filter[$filter])) {
            echo "✅ Filtro '$filter' está registrado<br>";
            
            // Verificar si nuestro callback está en el filtro
            $found = false;
            foreach ($wp_filter[$filter]->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    if (is_array($callback['function']) && 
                        is_object($callback['function'][0]) && 
                        get_class($callback['function'][0]) === 'WVP_Simple_Currency_Converter') {
                        $found = true;
                        echo "&nbsp;&nbsp;✅ Nuestro callback encontrado en prioridad $priority<br>";
                        break 2;
                    }
                }
            }
            
            if (!$found) {
                echo "&nbsp;&nbsp;❌ Nuestro callback NO encontrado en el filtro<br>";
            }
        } else {
            echo "❌ Filtro '$filter' NO está registrado<br>";
        }
    }
    
    // Test de conversión
    echo "<h2>Test de conversión:</h2>";
    $rate = $converter->get_bcv_rate();
    echo "Tasa BCV: $rate<br>";
    
    $test_price = 20.00;
    $converted = $converter->convert_price($test_price, 'USD', 'VES');
    echo "Conversión test: $test_price USD = $converted VES<br>";
    
    // Verificar si el método format_ves_price existe
    if (method_exists($converter, 'format_ves_price')) {
        $formatted = $converter->format_ves_price($converted);
        echo "Precio formateado: $formatted<br>";
    } else {
        echo "❌ Método format_ves_price NO existe<br>";
    }
    
} else {
    echo "❌ Clase WVP_Simple_Currency_Converter NO existe<br>";
}

echo "<h2>Información del sistema:</h2>";
echo "WordPress Version: " . get_bloginfo('version') . "<br>";
echo "WooCommerce Version: " . (defined('WC_VERSION') ? WC_VERSION : 'No disponible') . "<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";

echo "<p><strong>Test completado: " . date('Y-m-d H:i:s') . "</strong></p>";
?>
