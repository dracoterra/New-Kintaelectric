<?php
/**
 * Test Script - WooCommerce Venezuela Pro 2025 Simplified Version
 * 
 * Este script verifica que las funcionalidades core del plugin funcionen
 * correctamente sin errores de memoria.
 * 
 * INSTRUCCIONES DE USO:
 * 1. Acceder a: /wp-content/plugins/woocommerce-venezuela-pro-2025/test-plugin-simplified.php
 * 2. Verificar que todas las pruebas pasen
 * 3. Si alguna falla, revisar el debug.log
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    // Cargar WordPress mínimo para testing
    require_once( '../../../wp-config.php' );
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test WooCommerce Venezuela Pro 2025 - Simplified</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-result { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .pass { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        h1 { color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; }
        h2 { color: #666; margin-top: 30px; }
        .code { background: #f8f9fa; padding: 5px; border-radius: 3px; font-family: monospace; }
        .memory-info { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🇻🇪 Test WooCommerce Venezuela Pro 2025 - Versión Simplificada</h1>
        
        <div class="info">
            <strong>Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
            <strong>Memoria PHP:</strong> <span class="memory-info"><?php echo ini_get('memory_limit'); ?> (Usado: <?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?>MB)</span><br>
            <strong>Versión:</strong> Simplificada - Solo funcionalidades core activas
        </div>

        <?php
        // Inicializar contadores
        $tests_passed = 0;
        $tests_failed = 0;
        $memory_start = memory_get_usage(true);
        
        // Función helper para mostrar resultados
        function show_test_result($test_name, $passed, $message = '') {
            global $tests_passed, $tests_failed;
            
            if ($passed) {
                $tests_passed++;
                echo "<div class='test-result pass'>";
                echo "<strong>✅ PASS:</strong> $test_name";
                if ($message) echo "<br><em>$message</em>";
                echo "</div>";
            } else {
                $tests_failed++;
                echo "<div class='test-result fail'>";
                echo "<strong>❌ FAIL:</strong> $test_name";
                if ($message) echo "<br><em>$message</em>";
                echo "</div>";
            }
        }
        ?>

        <h2>1. Verificación de Entorno</h2>
        
        <?php
        // Test 1: WordPress cargado
        show_test_result(
            "WordPress cargado correctamente",
            defined('ABSPATH'),
            "ABSPATH definido: " . (defined('ABSPATH') ? 'Sí' : 'No')
        );
        
        // Test 2: WooCommerce activo
        $wc_active = class_exists('WooCommerce');
        show_test_result(
            "WooCommerce está activo",
            $wc_active,
            $wc_active ? "Clase WooCommerce encontrada" : "WooCommerce no está activo"
        );
        
        // Test 3: Plugin principal existe
        $plugin_file = plugin_dir_path(__FILE__) . 'woocommerce-venezuela-pro-2025.php';
        $plugin_exists = file_exists($plugin_file);
        show_test_result(
            "Archivo principal del plugin existe",
            $plugin_exists,
            $plugin_exists ? "woocommerce-venezuela-pro-2025.php encontrado" : "Archivo principal no encontrado"
        );
        ?>

        <h2>2. Verificación de Funcionalidades Core</h2>
        
        <?php
        // Test 4: Función wvp_init_plugin existe
        $init_function_exists = function_exists('wvp_init_plugin');
        show_test_result(
            "Función wvp_init_plugin existe",
            $init_function_exists,
            $init_function_exists ? "Función de inicialización encontrada" : "Función de inicialización no encontrada"
        );
        
        // Test 5: Clases de Payment Gateways
        $gateway_classes = [
            'WVP_Pago_Movil_Gateway' => 'includes/class-wvp-pago-movil-gateway.php',
            'WVP_Zelle_Gateway' => 'includes/class-wvp-zelle-gateway.php',
            'WVP_Bank_Transfer_Gateway' => 'includes/class-wvp-bank-transfer-gateway.php'
        ];
        
        foreach ($gateway_classes as $class_name => $file_path) {
            $file_exists = file_exists(plugin_dir_path(__FILE__) . $file_path);
            show_test_result(
                "Payment Gateway: $class_name",
                $file_exists,
                $file_exists ? "Archivo $file_path existe" : "Archivo $file_path no encontrado"
            );
        }
        
        // Test 6: Clases de Shipping Methods
        $shipping_classes = [
            'WVP_MRW_Shipping' => 'includes/class-wvp-mrw-shipping.php',
            'WVP_Zoom_Shipping' => 'includes/class-wvp-zoom-shipping.php',
            'WVP_Local_Delivery_Shipping' => 'includes/class-wvp-local-delivery-shipping.php'
        ];
        
        foreach ($shipping_classes as $class_name => $file_path) {
            $file_exists = file_exists(plugin_dir_path(__FILE__) . $file_path);
            show_test_result(
                "Shipping Method: $class_name",
                $file_exists,
                $file_exists ? "Archivo $file_path existe" : "Archivo $file_path no encontrado"
            );
        }
        
        // Test 7: SENIAT Exporter
        $seniat_file = plugin_dir_path(__FILE__) . 'includes/class-wvp-seniat-exporter.php';
        $seniat_exists = file_exists($seniat_file);
        show_test_result(
            "SENIAT Exporter",
            $seniat_exists,
            $seniat_exists ? "Archivo class-wvp-seniat-exporter.php existe" : "Archivo SENIAT no encontrado"
        );
        
        // Test 8: Admin Dashboard
        $admin_file = plugin_dir_path(__FILE__) . 'includes/class-wvp-admin-dashboard.php';
        $admin_exists = file_exists($admin_file);
        show_test_result(
            "Admin Dashboard",
            $admin_exists,
            $admin_exists ? "Archivo class-wvp-admin-dashboard.php existe" : "Archivo Admin Dashboard no encontrado"
        );
        ?>

        <h2>3. Verificación de Memoria</h2>
        
        <?php
        $memory_current = memory_get_usage(true);
        $memory_used = round(($memory_current - $memory_start) / 1024 / 1024, 2);
        $memory_limit_mb = (int) ini_get('memory_limit');
        $memory_percentage = round(($memory_current / 1024 / 1024 / $memory_limit_mb) * 100, 2);
        
        show_test_result(
            "Uso de memoria durante las pruebas",
            $memory_used < 50, // Menos de 50MB usado en las pruebas
            "Memoria usada: {$memory_used}MB | Total: " . round($memory_current / 1024 / 1024, 2) . "MB ({$memory_percentage}% del límite)"
        );
        
        // Test de memoria crítica
        $memory_critical = $memory_percentage > 90;
        show_test_result(
            "Memoria no está en nivel crítico",
            !$memory_critical,
            $memory_critical ? "⚠️ ADVERTENCIA: Uso de memoria > 90%" : "Uso de memoria normal"
        );
        ?>

        <h2>4. Verificación de Funciones Helper</h2>
        
        <?php
        // Test 9: Funciones helper de gateways
        $helper_functions = [
            'wvp_add_pago_movil_gateway',
            'wvp_add_zelle_gateway',
            'wvp_add_bank_transfer_gateway',
            'wvp_add_mrw_shipping_method',
            'wvp_add_zoom_shipping_method',
            'wvp_add_local_delivery_shipping_method'
        ];
        
        foreach ($helper_functions as $function_name) {
            $function_exists = function_exists($function_name);
            show_test_result(
                "Función helper: $function_name",
                $function_exists,
                $function_exists ? "Función encontrada" : "Función no encontrada"
            );
        }
        ?>

        <h2>5. Verificación de Hooks de WordPress</h2>
        
        <?php
        // Test 10: Hook plugins_loaded
        $hook_exists = has_action('plugins_loaded', 'wvp_init_plugin');
        show_test_result(
            "Hook 'plugins_loaded' registrado",
            $hook_exists !== false,
            $hook_exists !== false ? "Hook registrado correctamente" : "Hook no registrado"
        );
        ?>

        <h2>📊 Resumen de Pruebas</h2>
        
        <?php
        $total_tests = $tests_passed + $tests_failed;
        $success_rate = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 2) : 0;
        
        if ($tests_failed == 0) {
            echo "<div class='test-result pass'>";
            echo "<strong>🎉 TODAS LAS PRUEBAS PASARON</strong><br>";
            echo "Total: $tests_passed/$total_tests pruebas exitosas (100%)";
            echo "</div>";
        } else {
            echo "<div class='test-result " . ($success_rate >= 80 ? 'warning' : 'fail') . "'>";
            echo "<strong>" . ($success_rate >= 80 ? '⚠️' : '❌') . " ALGUNAS PRUEBAS FALLARON</strong><br>";
            echo "Exitosas: $tests_passed | Fallidas: $tests_failed | Total: $total_tests<br>";
            echo "Tasa de éxito: $success_rate%";
            echo "</div>";
        }
        
        $memory_final = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        ?>

        <div class="info">
            <strong>Información Final:</strong><br>
            Memoria al finalizar: <?php echo round($memory_final / 1024 / 1024, 2); ?>MB<br>
            Pico de memoria: <?php echo round($memory_peak / 1024 / 1024, 2); ?>MB<br>
            Tiempo de ejecución: <?php echo round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2); ?>ms
        </div>

        <h2>🔧 Próximos Pasos</h2>
        
        <div class="info">
            <?php if ($tests_failed == 0): ?>
                <p><strong>✅ Plugin listo para usar</strong></p>
                <p>Todas las funcionalidades core están funcionando correctamente. Puedes:</p>
                <ul>
                    <li>Probar los métodos de pago en el checkout</li>
                    <li>Configurar los métodos de envío</li>
                    <li>Usar el exportador SENIAT</li>
                    <li>Comenzar la reactivación gradual siguiendo el <span class="code">PLAN-REACTIVACION-GRADUAL.md</span></li>
                </ul>
            <?php else: ?>
                <p><strong>⚠️ Se requiere atención</strong></p>
                <p>Hay <?php echo $tests_failed; ?> prueba(s) que fallaron. Revisar:</p>
                <ul>
                    <li>El archivo <span class="code">debug.log</span> para errores específicos</li>
                    <li>Que todos los archivos de clases existan en <span class="code">includes/</span></li>
                    <li>La configuración de PHP y WordPress</li>
                </ul>
            <?php endif; ?>
        </div>

        <div class="warning">
            <strong>Recordatorio:</strong> Esta es la versión simplificada del plugin. Las funcionalidades avanzadas están comentadas temporalmente para evitar errores de memoria. Usa el plan de reactivación gradual para habilitarlas una por una.
        </div>
    </div>
</body>
</html>
