<?php
/**
 * Test específico para el filtro woocommerce_cart_item_subtotal
 * Verificar por qué no está aplicando conversiones VES
 */

// Cargar WordPress
require_once('C:\Users\ronal\Local Sites\new-kinta-electric\app\public\wp-config.php');

echo "🔍 TEST ESPECÍFICO - Filtro woocommerce_cart_item_subtotal\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar si el filtro está registrado
echo "1. Verificando registro del filtro:\n";
$filters = $GLOBALS['wp_filter']['woocommerce_cart_item_subtotal'] ?? null;
if ($filters) {
    echo "✅ Filtro woocommerce_cart_item_subtotal está registrado\n";
    foreach ($filters->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function']) && is_object($callback['function'][0])) {
                $class_name = get_class($callback['function'][0]);
                $method_name = $callback['function'][1];
                echo "   - Prioridad $priority: $class_name::$method_name\n";
            }
        }
    }
} else {
    echo "❌ Filtro woocommerce_cart_item_subtotal NO está registrado\n";
}

// Verificar si la clase WVP_Simple_Currency_Converter está cargada
echo "\n2. Verificando clase WVP_Simple_Currency_Converter:\n";
if (class_exists('WVP_Simple_Currency_Converter')) {
    echo "✅ Clase WVP_Simple_Currency_Converter existe\n";
    
    // Verificar si el método display_cart_item_subtotal existe
    if (method_exists('WVP_Simple_Currency_Converter', 'display_cart_item_subtotal')) {
        echo "✅ Método display_cart_item_subtotal existe\n";
    } else {
        echo "❌ Método display_cart_item_subtotal NO existe\n";
    }
} else {
    echo "❌ Clase WVP_Simple_Currency_Converter NO existe\n";
}

// Simular ejecución del filtro
echo "\n3. Simulando ejecución del filtro:\n";
if (class_exists('WVP_Simple_Currency_Converter')) {
    $converter = WVP_Simple_Currency_Converter::get_instance();
    
    // Crear datos de prueba para el carrito
    $test_cart_item = array(
        'data' => new stdClass(),
        'quantity' => 1
    );
    
    // Simular un producto con precio
    $test_cart_item['data']->price = 20.00;
    
    // Simular el método get_price
    $test_cart_item['data']->get_price = function() {
        return 20.00;
    };
    
    $test_subtotal = '$20.00';
    
    echo "   - Precio original: $test_subtotal\n";
    
    try {
        $result = $converter->display_cart_item_subtotal($test_subtotal, $test_cart_item, 'test_key');
        echo "   - Resultado del filtro: $result\n";
        
        if ($result !== $test_subtotal) {
            echo "✅ El filtro está modificando el precio\n";
        } else {
            echo "❌ El filtro NO está modificando el precio\n";
        }
    } catch (Exception $e) {
        echo "❌ Error al ejecutar el filtro: " . $e->getMessage() . "\n";
    }
}

// Verificar si hay errores en el log
echo "\n4. Verificando errores recientes:\n";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $errors = file_get_contents($error_log);
    $recent_errors = array_slice(explode("\n", $errors), -10);
    foreach ($recent_errors as $error) {
        if (trim($error) && strpos($error, 'WVP') !== false) {
            echo "   - $error\n";
        }
    }
}

echo "\n🎯 TEST COMPLETADO\n";
?>
