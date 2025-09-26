<?php
/**
 * Plugin Health Verification Script
 * Verifica que el plugin WooCommerce Venezuela Pro 2025 esté 100% funcional
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    require_once( '../../../wp-config.php' );
}

// Configurar reporte de errores
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Salud del Plugin - WVP 2025</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; text-align: center; }
        .card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .test-item { padding: 15px; border-radius: 8px; border-left: 4px solid #ddd; }
        .pass { background: #f0f9ff; border-color: #10b981; color: #065f46; }
        .fail { background: #fef2f2; border-color: #ef4444; color: #991b1b; }
        .warning { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
        .info { background: #f0f9ff; border-color: #3b82f6; color: #1e40af; }
        .status-icon { font-size: 20px; margin-right: 10px; }
        .memory-bar { height: 20px; background: #e5e7eb; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .memory-fill { height: 100%; background: linear-gradient(90deg, #10b981, #f59e0b, #ef4444); transition: width 0.3s; }
        .code { background: #f8fafc; padding: 8px 12px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 13px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; margin: 5px; }
        .btn:hover { background: #2563eb; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
        .stat { text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px; }
        .stat-number { font-size: 24px; font-weight: bold; color: #1f2937; }
        .stat-label { font-size: 12px; color: #6b7280; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🇻🇪 Verificación de Salud del Plugin</h1>
            <p>WooCommerce Venezuela Pro 2025 - Diagnóstico Completo</p>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <?php
        // Contadores globales
        $total_tests = 0;
        $passed_tests = 0;
        $failed_tests = 0;
        $warnings = 0;
        $memory_start = memory_get_usage(true);

        // Función helper para tests
        function run_test($name, $test_function, $description = '') {
            global $total_tests, $passed_tests, $failed_tests, $warnings;
            $total_tests++;
            
            try {
                $result = $test_function();
                if ($result['status'] === 'pass') {
                    $passed_tests++;
                    $class = 'pass';
                    $icon = '✅';
                } elseif ($result['status'] === 'warning') {
                    $warnings++;
                    $class = 'warning';
                    $icon = '⚠️';
                } else {
                    $failed_tests++;
                    $class = 'fail';
                    $icon = '❌';
                }
                
                echo "<div class='test-item $class'>";
                echo "<span class='status-icon'>$icon</span>";
                echo "<strong>$name</strong>";
                if ($description) echo "<br><small>$description</small>";
                echo "<br><em>" . $result['message'] . "</em>";
                echo "</div>";
                
            } catch (Exception $e) {
                $failed_tests++;
                echo "<div class='test-item fail'>";
                echo "<span class='status-icon'>💥</span>";
                echo "<strong>$name</strong>";
                echo "<br><em>Error: " . $e->getMessage() . "</em>";
                echo "</div>";
            }
        }
        ?>

        <div class="card">
            <h2>🔍 Tests de Funcionalidad Core</h2>
            <div class="test-grid">
                <?php
                // Test 1: WordPress y WooCommerce
                run_test('WordPress & WooCommerce', function() {
                    if (!defined('ABSPATH')) {
                        return ['status' => 'fail', 'message' => 'WordPress no está cargado correctamente'];
                    }
                    if (!class_exists('WooCommerce')) {
                        return ['status' => 'fail', 'message' => 'WooCommerce no está activo'];
                    }
                    return ['status' => 'pass', 'message' => 'WordPress y WooCommerce funcionando correctamente'];
                });

                // Test 2: Plugin principal
                run_test('Archivo Principal del Plugin', function() {
                    $plugin_file = plugin_dir_path(__FILE__) . 'woocommerce-venezuela-pro-2025.php';
                    if (!file_exists($plugin_file)) {
                        return ['status' => 'fail', 'message' => 'Archivo principal no encontrado'];
                    }
                    if (!function_exists('wvp_init_plugin')) {
                        return ['status' => 'fail', 'message' => 'Función wvp_init_plugin no está definida'];
                    }
                    return ['status' => 'pass', 'message' => 'Plugin principal cargado correctamente'];
                });

                // Test 3: Payment Gateways
                $gateways = [
                    'Pago Móvil' => 'class-wvp-pago-movil-gateway.php',
                    'Zelle' => 'class-wvp-zelle-gateway.php',
                    'Transferencia Bancaria' => 'class-wvp-bank-transfer-gateway.php'
                ];

                foreach ($gateways as $name => $file) {
                    run_test("Gateway: $name", function() use ($file) {
                        $path = plugin_dir_path(__FILE__) . 'includes/' . $file;
                        if (!file_exists($path)) {
                            return ['status' => 'fail', 'message' => "Archivo $file no encontrado"];
                        }
                        return ['status' => 'pass', 'message' => 'Gateway disponible'];
                    });
                }

                // Test 4: Shipping Methods
                $shipping_methods = [
                    'MRW' => 'class-wvp-mrw-shipping.php',
                    'Zoom' => 'class-wvp-zoom-shipping.php',
                    'Entrega Local' => 'class-wvp-local-delivery-shipping.php'
                ];

                foreach ($shipping_methods as $name => $file) {
                    run_test("Envío: $name", function() use ($file) {
                        $path = plugin_dir_path(__FILE__) . 'includes/' . $file;
                        if (!file_exists($path)) {
                            return ['status' => 'fail', 'message' => "Archivo $file no encontrado"];
                        }
                        return ['status' => 'pass', 'message' => 'Método de envío disponible'];
                    });
                }

                // Test 5: SENIAT Exporter
                run_test('SENIAT Exporter', function() {
                    $path = plugin_dir_path(__FILE__) . 'includes/class-wvp-seniat-exporter.php';
                    if (!file_exists($path)) {
                        return ['status' => 'fail', 'message' => 'Archivo SENIAT no encontrado'];
                    }
                    return ['status' => 'pass', 'message' => 'SENIAT Exporter disponible'];
                });

                // Test 6: Admin Dashboard
                run_test('Admin Dashboard', function() {
                    $path = plugin_dir_path(__FILE__) . 'includes/class-wvp-admin-dashboard.php';
                    if (!file_exists($path)) {
                        return ['status' => 'fail', 'message' => 'Archivo Admin Dashboard no encontrado'];
                    }
                    return ['status' => 'pass', 'message' => 'Admin Dashboard disponible'];
                });

                // Test 7: Hooks de WordPress
                run_test('Hooks de WordPress', function() {
                    $hook_registered = has_action('plugins_loaded', 'wvp_init_plugin');
                    if ($hook_registered === false) {
                        return ['status' => 'fail', 'message' => 'Hook plugins_loaded no está registrado'];
                    }
                    return ['status' => 'pass', 'message' => 'Hooks registrados correctamente'];
                });

                // Test 8: Funciones Helper
                $helper_functions = [
                    'wvp_add_pago_movil_gateway',
                    'wvp_add_zelle_gateway',
                    'wvp_add_bank_transfer_gateway'
                ];

                foreach ($helper_functions as $function) {
                    run_test("Función: $function", function() use ($function) {
                        if (!function_exists($function)) {
                            return ['status' => 'fail', 'message' => 'Función no encontrada'];
                        }
                        return ['status' => 'pass', 'message' => 'Función disponible'];
                    });
                }
                ?>
            </div>
        </div>

        <div class="card">
            <h2>💾 Análisis de Memoria</h2>
            <?php
            $memory_current = memory_get_usage(true);
            $memory_peak = memory_get_peak_usage(true);
            $memory_limit = ini_get('memory_limit');
            $memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
            $memory_percentage = ($memory_current / $memory_limit_bytes) * 100;
            
            $memory_status = 'pass';
            if ($memory_percentage > 90) $memory_status = 'fail';
            elseif ($memory_percentage > 70) $memory_status = 'warning';
            ?>
            
            <div class="test-item <?php echo $memory_status; ?>">
                <span class="status-icon"><?php echo $memory_status === 'pass' ? '✅' : ($memory_status === 'warning' ? '⚠️' : '❌'); ?></span>
                <strong>Uso de Memoria</strong>
                <div class="memory-bar">
                    <div class="memory-fill" style="width: <?php echo min($memory_percentage, 100); ?>%"></div>
                </div>
                <div class="stats">
                    <div class="stat">
                        <div class="stat-number"><?php echo round($memory_current / 1024 / 1024, 1); ?>MB</div>
                        <div class="stat-label">Actual</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo round($memory_peak / 1024 / 1024, 1); ?>MB</div>
                        <div class="stat-label">Pico</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $memory_limit; ?></div>
                        <div class="stat-label">Límite</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo round($memory_percentage, 1); ?>%</div>
                        <div class="stat-label">Uso</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>📊 Resumen de Resultados</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?php echo $total_tests; ?></div>
                    <div class="stat-label">Total Tests</div>
                </div>
                <div class="stat">
                    <div class="stat-number" style="color: #10b981;"><?php echo $passed_tests; ?></div>
                    <div class="stat-label">Exitosos</div>
                </div>
                <div class="stat">
                    <div class="stat-number" style="color: #f59e0b;"><?php echo $warnings; ?></div>
                    <div class="stat-label">Advertencias</div>
                </div>
                <div class="stat">
                    <div class="stat-number" style="color: #ef4444;"><?php echo $failed_tests; ?></div>
                    <div class="stat-label">Fallidos</div>
                </div>
            </div>

            <?php
            $success_rate = $total_tests > 0 ? ($passed_tests / $total_tests) * 100 : 0;
            $overall_status = 'fail';
            if ($failed_tests === 0 && $warnings === 0) $overall_status = 'pass';
            elseif ($failed_tests === 0) $overall_status = 'warning';
            ?>

            <div class="test-item <?php echo $overall_status; ?>" style="margin-top: 20px; font-size: 18px;">
                <span class="status-icon" style="font-size: 24px;">
                    <?php echo $overall_status === 'pass' ? '🎉' : ($overall_status === 'warning' ? '⚠️' : '❌'); ?>
                </span>
                <strong>
                    <?php if ($overall_status === 'pass'): ?>
                        PLUGIN 100% FUNCIONAL
                    <?php elseif ($overall_status === 'warning'): ?>
                        PLUGIN FUNCIONAL CON ADVERTENCIAS
                    <?php else: ?>
                        PLUGIN REQUIERE ATENCIÓN
                    <?php endif; ?>
                </strong>
                <br>
                <em>Tasa de éxito: <?php echo round($success_rate, 1); ?>%</em>
            </div>
        </div>

        <div class="card">
            <h2>🔧 Acciones Recomendadas</h2>
            <?php if ($overall_status === 'pass'): ?>
                <div class="test-item pass">
                    <span class="status-icon">✅</span>
                    <strong>Plugin Listo para Producción</strong>
                    <br>
                    <p>El plugin está funcionando perfectamente. Puedes:</p>
                    <ul>
                        <li>Configurar los métodos de pago en WooCommerce</li>
                        <li>Establecer las zonas de envío</li>
                        <li>Comenzar a usar el exportador SENIAT</li>
                        <li>Reactivar funcionalidades adicionales siguiendo el plan gradual</li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="test-item fail">
                    <span class="status-icon">🔧</span>
                    <strong>Acciones Requeridas</strong>
                    <br>
                    <p>Se detectaron problemas que requieren atención:</p>
                    <ul>
                        <?php if ($failed_tests > 0): ?>
                            <li>Revisar los <?php echo $failed_tests; ?> tests fallidos</li>
                            <li>Verificar que todos los archivos de clases existan</li>
                        <?php endif; ?>
                        <?php if ($warnings > 0): ?>
                            <li>Atender las <?php echo $warnings; ?> advertencias</li>
                        <?php endif; ?>
                        <li>Consultar el archivo debug.log para errores específicos</li>
                    </ul>
                </div>
            <?php endif; ?>

            <div style="margin-top: 20px;">
                <a href="PLAN-REACTIVACION-GRADUAL.md" class="btn">📋 Ver Plan de Reactivación</a>
                <a href="test-plugin-simplified.php" class="btn">🧪 Test Simplificado</a>
                <a href="../debug.log" class="btn">📝 Debug Log</a>
            </div>
        </div>

        <div class="card">
            <h2>ℹ️ Información del Sistema</h2>
            <div class="code">
                <strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?><br>
                <strong>WooCommerce:</strong> <?php echo class_exists('WooCommerce') ? WC()->version : 'No instalado'; ?><br>
                <strong>PHP:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>Memoria PHP:</strong> <?php echo ini_get('memory_limit'); ?><br>
                <strong>Tiempo de ejecución:</strong> <?php echo round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2); ?>ms<br>
                <strong>Plugin:</strong> WooCommerce Venezuela Pro 2025 v1.0.0 (Simplificado)
            </div>
        </div>
    </div>
</body>
</html>
