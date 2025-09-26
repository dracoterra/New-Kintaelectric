<?php
/**
 * Test simple para verificar errores
 */

// Activar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Simple - Verificando errores</h1>";

// Cargar WordPress
echo "<h2>1. Cargando WordPress</h2>";
try {
    require_once('../../../wp-config.php');
    echo "✅ WordPress config cargado<br>";
} catch (Exception $e) {
    echo "❌ Error cargando WordPress: " . $e->getMessage() . "<br>";
}

// Verificar WordPress
if (function_exists('wp_get_current_user')) {
    echo "✅ WordPress funciones disponibles<br>";
} else {
    echo "❌ WordPress funciones no disponibles<br>";
}

// Cargar archivo principal del plugin
echo "<h2>2. Cargando archivo principal del plugin</h2>";
$plugin_file = __DIR__ . '/woocommerce-venezuela-pro-2025.php';
if (file_exists($plugin_file)) {
    echo "✅ Archivo principal encontrado<br>";
    try {
        require_once $plugin_file;
        echo "✅ Archivo principal cargado<br>";
    } catch (Exception $e) {
        echo "❌ Error al cargar archivo principal: " . $e->getMessage() . "<br>";
    } catch (Error $e) {
        echo "❌ Error fatal al cargar archivo principal: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Archivo principal no encontrado<br>";
}

// Verificar clases
echo "<h2>3. Verificando clases</h2>";
$classes = ['WVP_Simple_Currency_Converter', 'WVP_Venezuelan_Taxes', 'WVP_Venezuelan_Shipping', 'WVP_Product_Display'];

foreach ($classes as $class_name) {
    if (class_exists($class_name)) {
        echo "✅ Clase $class_name existe<br>";
    } else {
        echo "❌ Clase $class_name NO existe<br>";
    }
}

echo "<h2>4. Test completado</h2>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
?>
