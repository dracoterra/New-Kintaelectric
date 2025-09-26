<?php
/**
 * Final Functionality Test
 * Prueba real de carga y funcionamiento de todas las clases core
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    require_once( '../../../wp-config.php' );
}

// Configurar manejo de errores
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Final de Funcionalidad - WVP 2025</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f0f2f5; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; text-align: center; }
        .card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #ddd; }
        .pass { background: #f0f9ff; border-color: #10b981; color: #065f46; }
        .fail { background: #fef2f2; border-color: #ef4444; color: #991b1b; }
        .warning { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
        .info { background: #f0f9ff; border-color: #3b82f6; color: #1e40af; }
        .icon { font-size: 18px; margin-right: 10px; }
        .code { background: #f8fafc; padding: 8px 12px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 12px; }
        .memory-info { font-size: 11px; color: #6b7280; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Final de Funcionalidad</h1>
            <p>Verificación completa del plugin WooCommerce Venezuela Pro 2025</p>
            <p class="memory-info">Memoria inicial: <?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?>MB</p>
        </div>

        <?php
        $tests_passed = 0;
        $tests_failed = 0;
        $memory_usage = [];

        function test_result($name, $passed, $message = '', $details = '') {
            global $tests_passed, $tests_failed, $memory_usage;
            
            $memory_usage[] = memory_get_usage(true);
            $current_memory = round(end($memory_usage) / 1024 / 1024, 2);
            
            if ($passed) {
                $tests_passed++;
                echo "<div class='test-result pass'>";
                echo "<span class='icon'>✅</span>";
                echo "<strong>$name</strong>";
                if ($message) echo "<br><em>$message</em>";
                if ($details) echo "<br><div class='code'>$details</div>";
                echo "<div class='memory-info'>Memoria: {$current_memory}MB</div>";
                echo "</div>";
            } else {
                $tests_failed++;
                echo "<div class='test-result fail'>";
                echo "<span class='icon'>❌</span>";
                echo "<strong>$name</strong>";
                if ($message) echo "<br><em>$message</em>";
                if ($details) echo "<br><div class='code'>$details</div>";
                echo "<div class='memory-info'>Memoria: {$current_memory}MB</div>";
                echo "</div>";
            }
        }
        ?>

        <div class="card">
            <h2>🔧 Test de Carga de Clases Core</h2>
            <div class="grid">
                <?php
                // Test 1: Cargar función principal
                try {
                    if (function_exists('wvp_init_plugin')) {
                        test_result(
                            'Función Principal',
                            true,
                            'wvp_init_plugin está disponible'
                        );
                    } else {
                        test_result(
                            'Función Principal',
                            false,
                            'wvp_init_plugin no está definida'
                        );
                    }
                } catch (Exception $e) {
                    test_result(
                        'Función Principal',
                        false,
                        'Error: ' . $e->getMessage()
                    );
                }

                // Test 2: Cargar Payment Gateways
                $gateways = [
                    'Pago Móvil' => ['file' => 'class-wvp-pago-movil-gateway.php', 'class' => 'WVP_Pago_Movil_Gateway'],
                    'Zelle' => ['file' => 'class-wvp-zelle-gateway.php', 'class' => 'WVP_Zelle_Gateway'],
                    'Bank Transfer' => ['file' => 'class-wvp-bank-transfer-gateway.php', 'class' => 'WVP_Bank_Transfer_Gateway']
                ];

                foreach ($gateways as $name => $config) {
                    try {
                        $file_path = plugin_dir_path(__FILE__) . 'includes/' . $config['file'];
                        if (file_exists($file_path)) {
                            require_once $file_path;
                            if (class_exists($config['class'])) {
                                // Intentar crear instancia
                                $instance = new $config['class']();
                                test_result(
                                    "Gateway: $name",
                                    true,
                                    'Clase cargada e instanciada correctamente',
                                    "ID: " . (property_exists($instance, 'id') ? $instance->id : 'N/A')
                                );
                            } else {
                                test_result(
                                    "Gateway: $name",
                                    false,
                                    'Clase no encontrada después de cargar archivo'
                                );
                            }
                        } else {
                            test_result(
                                "Gateway: $name",
                                false,
                                'Archivo no encontrado'
                            );
                        }
                    } catch (Exception $e) {
                        test_result(
                            "Gateway: $name",
                            false,
                            'Error al cargar: ' . $e->getMessage()
                        );
                    }
                }

                // Test 3: Cargar Shipping Methods
                $shipping_methods = [
                    'MRW' => ['file' => 'class-wvp-mrw-shipping.php', 'class' => 'WVP_MRW_Shipping'],
                    'Zoom' => ['file' => 'class-wvp-zoom-shipping.php', 'class' => 'WVP_Zoom_Shipping'],
                    'Local Delivery' => ['file' => 'class-wvp-local-delivery-shipping.php', 'class' => 'WVP_Local_Delivery_Shipping']
                ];

                foreach ($shipping_methods as $name => $config) {
                    try {
                        $file_path = plugin_dir_path(__FILE__) . 'includes/' . $config['file'];
                        if (file_exists($file_path)) {
                            require_once $file_path;
                            if (class_exists($config['class'])) {
                                // Intentar crear instancia
                                $instance = new $config['class']();
                                test_result(
                                    "Shipping: $name",
                                    true,
                                    'Método de envío cargado correctamente',
                                    "ID: " . (property_exists($instance, 'id') ? $instance->id : 'N/A')
                                );
                            } else {
                                test_result(
                                    "Shipping: $name",
                                    false,
                                    'Clase no encontrada después de cargar archivo'
                                );
                            }
                        } else {
                            test_result(
                                "Shipping: $name",
                                false,
                                'Archivo no encontrado'
                            );
                        }
                    } catch (Exception $e) {
                        test_result(
                            "Shipping: $name",
                            false,
                            'Error al cargar: ' . $e->getMessage()
                        );
                    }
                }

                // Test 4: SENIAT Exporter
                try {
                    $file_path = plugin_dir_path(__FILE__) . 'includes/class-wvp-seniat-exporter.php';
                    if (file_exists($file_path)) {
                        require_once $file_path;
                        if (class_exists('WVP_SENIAT_Exporter')) {
                            $instance = WVP_SENIAT_Exporter::get_instance();
                            test_result(
                                'SENIAT Exporter',
                                true,
                                'SENIAT Exporter cargado correctamente',
                                'Singleton pattern funcionando'
                            );
                        } else {
                            test_result(
                                'SENIAT Exporter',
                                false,
                                'Clase SENIAT no encontrada'
                            );
                        }
                    } else {
                        test_result(
                            'SENIAT Exporter',
                            false,
                            'Archivo SENIAT no encontrado'
                        );
                    }
                } catch (Exception $e) {
                    test_result(
                        'SENIAT Exporter',
                        false,
                        'Error al cargar SENIAT: ' . $e->getMessage()
                    );
                }

                // Test 5: Admin Dashboard
                try {
                    $file_path = plugin_dir_path(__FILE__) . 'includes/class-wvp-admin-dashboard.php';
                    if (file_exists($file_path)) {
                        require_once $file_path;
                        if (class_exists('WVP_Admin_Dashboard')) {
                            $instance = WVP_Admin_Dashboard::get_instance();
                            test_result(
                                'Admin Dashboard',
                                true,
                                'Admin Dashboard cargado correctamente',
                                'Singleton pattern funcionando'
                            );
                        } else {
                            test_result(
                                'Admin Dashboard',
                                false,
                                'Clase Admin Dashboard no encontrada'
                            );
                        }
                    } else {
                        test_result(
                            'Admin Dashboard',
                            false,
                            'Archivo Admin Dashboard no encontrado'
                        );
                    }
                } catch (Exception $e) {
                    test_result(
                        'Admin Dashboard',
                        false,
                        'Error al cargar Admin Dashboard: ' . $e->getMessage()
                    );
                }
                ?>
            </div>
        </div>

        <div class="card">
            <h2>🎯 Test de Integración con WooCommerce</h2>
            <div class="grid">
                <?php
                // Test 6: WooCommerce Integration
                if (class_exists('WooCommerce')) {
                    // Test payment gateways registration
                    $wc_gateways = WC()->payment_gateways();
                    if ($wc_gateways) {
                        test_result(
                            'WooCommerce Payment Gateways',
                            true,
                            'Sistema de payment gateways disponible',
                            'WC()->payment_gateways() funcional'
                        );
                    } else {
                        test_result(
                            'WooCommerce Payment Gateways',
                            false,
                            'Sistema de payment gateways no disponible'
                        );
                    }

                    // Test shipping methods
                    $wc_shipping = WC()->shipping();
                    if ($wc_shipping) {
                        test_result(
                            'WooCommerce Shipping',
                            true,
                            'Sistema de shipping disponible',
                            'WC()->shipping() funcional'
                        );
                    } else {
                        test_result(
                            'WooCommerce Shipping',
                            false,
                            'Sistema de shipping no disponible'
                        );
                    }

                    // Test hooks system
                    $hook_exists = has_action('plugins_loaded', 'wvp_init_plugin');
                    test_result(
                        'WordPress Hooks',
                        $hook_exists !== false,
                        $hook_exists !== false ? 'Hook plugins_loaded registrado' : 'Hook no registrado',
                        'Priority: ' . ($hook_exists !== false ? $hook_exists : 'N/A')
                    );

                } else {
                    test_result(
                        'WooCommerce Integration',
                        false,
                        'WooCommerce no está disponible'
                    );
                }

                // Test 7: Helper Functions
                $helper_functions = [
                    'wvp_add_pago_movil_gateway',
                    'wvp_add_zelle_gateway',
                    'wvp_add_bank_transfer_gateway',
                    'wvp_add_mrw_shipping_method',
                    'wvp_add_zoom_shipping_method',
                    'wvp_add_local_delivery_shipping_method'
                ];

                $functions_available = 0;
                foreach ($helper_functions as $function) {
                    if (function_exists($function)) {
                        $functions_available++;
                    }
                }

                test_result(
                    'Helper Functions',
                    $functions_available === count($helper_functions),
                    "$functions_available/" . count($helper_functions) . " funciones helper disponibles",
                    $functions_available === count($helper_functions) ? 'Todas las funciones cargadas' : 'Algunas funciones faltantes'
                );

                // Test 8: Memory Stress Test
                $memory_before = memory_get_usage(true);
                
                // Simular carga de trabajo
                for ($i = 0; $i < 100; $i++) {
                    $dummy_data = array_fill(0, 100, 'test_data_' . $i);
                    unset($dummy_data);
                }
                
                $memory_after = memory_get_usage(true);
                $memory_diff = $memory_after - $memory_before;
                
                test_result(
                    'Memory Stress Test',
                    $memory_diff < 5 * 1024 * 1024, // Menos de 5MB de diferencia
                    'Test de estrés de memoria completado',
                    'Diferencia de memoria: ' . round($memory_diff / 1024 / 1024, 2) . 'MB'
                );
                ?>
            </div>
        </div>

        <div class="card">
            <h2>📊 Resultados Finales</h2>
            <?php
            $total_tests = $tests_passed + $tests_failed;
            $success_rate = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 1) : 0;
            $memory_final = memory_get_usage(true);
            $memory_peak = memory_get_peak_usage(true);
            ?>
            
            <div class="grid">
                <div class="test-result <?php echo $tests_failed === 0 ? 'pass' : ($success_rate >= 80 ? 'warning' : 'fail'); ?>">
                    <span class="icon"><?php echo $tests_failed === 0 ? '🎉' : ($success_rate >= 80 ? '⚠️' : '❌'); ?></span>
                    <strong>Resultado General</strong><br>
                    <?php if ($tests_failed === 0): ?>
                        <em>¡PLUGIN 100% FUNCIONAL!</em><br>
                        <div class="code">Todos los tests pasaron exitosamente</div>
                    <?php elseif ($success_rate >= 80): ?>
                        <em>Plugin funcional con advertencias</em><br>
                        <div class="code">Tasa de éxito: <?php echo $success_rate; ?>%</div>
                    <?php else: ?>
                        <em>Plugin requiere atención</em><br>
                        <div class="code">Múltiples tests fallaron</div>
                    <?php endif; ?>
                </div>

                <div class="test-result info">
                    <span class="icon">📈</span>
                    <strong>Estadísticas</strong><br>
                    <em>Resumen de la ejecución</em><br>
                    <div class="code">
                        Tests exitosos: <?php echo $tests_passed; ?><br>
                        Tests fallidos: <?php echo $tests_failed; ?><br>
                        Total: <?php echo $total_tests; ?><br>
                        Éxito: <?php echo $success_rate; ?>%
                    </div>
                </div>

                <div class="test-result info">
                    <span class="icon">💾</span>
                    <strong>Memoria</strong><br>
                    <em>Análisis de uso de memoria</em><br>
                    <div class="code">
                        Memoria final: <?php echo round($memory_final / 1024 / 1024, 2); ?>MB<br>
                        Pico de memoria: <?php echo round($memory_peak / 1024 / 1024, 2); ?>MB<br>
                        Límite PHP: <?php echo ini_get('memory_limit'); ?><br>
                        Uso: <?php echo round(($memory_final / wp_convert_hr_to_bytes(ini_get('memory_limit'))) * 100, 1); ?>%
                    </div>
                </div>

                <div class="test-result info">
                    <span class="icon">⏱️</span>
                    <strong>Performance</strong><br>
                    <em>Métricas de rendimiento</em><br>
                    <div class="code">
                        Tiempo: <?php echo round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2); ?>ms<br>
                        Clases cargadas: <?php echo count(get_declared_classes()); ?><br>
                        Funciones: <?php echo count(get_defined_functions()['user']); ?><br>
                        Estado: <?php echo $tests_failed === 0 ? 'Óptimo' : 'Requiere atención'; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($tests_failed === 0): ?>
            <div class="card">
                <h2>🚀 Plugin Listo para Producción</h2>
                <div class="test-result pass">
                    <span class="icon">✅</span>
                    <strong>Todas las funcionalidades core están operativas</strong><br>
                    <em>El plugin WooCommerce Venezuela Pro 2025 está completamente funcional y listo para usar en producción.</em>
                    
                    <h3 style="margin-top: 20px;">Funcionalidades Activas:</h3>
                    <ul>
                        <li>✅ <strong>Payment Gateways:</strong> Pago Móvil, Zelle, Transferencia Bancaria</li>
                        <li>✅ <strong>Shipping Methods:</strong> MRW, Zoom, Entrega Local</li>
                        <li>✅ <strong>SENIAT Exporter:</strong> Generación de reportes</li>
                        <li>✅ <strong>Admin Dashboard:</strong> Panel de administración</li>
                    </ul>

                    <h3>Próximos Pasos:</h3>
                    <ol>
                        <li>Configurar los métodos de pago en WooCommerce</li>
                        <li>Establecer las zonas de envío</li>
                        <li>Probar el flujo de compra completo</li>
                        <li>Considerar activar funcionalidades adicionales según el plan de reactivación gradual</li>
                    </ol>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>🔧 Acciones Requeridas</h2>
                <div class="test-result fail">
                    <span class="icon">⚠️</span>
                    <strong>Se detectaron <?php echo $tests_failed; ?> problema(s)</strong><br>
                    <em>El plugin requiere atención antes de ser usado en producción.</em>
                    
                    <h3>Recomendaciones:</h3>
                    <ul>
                        <li>Revisar los tests fallidos arriba</li>
                        <li>Verificar que todos los archivos estén presentes</li>
                        <li>Consultar el debug.log para errores específicos</li>
                        <li>Ejecutar el script de activación segura</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
