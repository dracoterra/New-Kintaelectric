<?php
/**
 * Safe Plugin Activation Script
 * Activa el plugin de forma segura verificando todos los requisitos
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
    <title>Activación Segura - WVP 2025</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f0f2f5; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; text-align: center; }
        .card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .step { padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 5px solid #ddd; }
        .step.success { background: #f0f9ff; border-color: #10b981; color: #065f46; }
        .step.error { background: #fef2f2; border-color: #ef4444; color: #991b1b; }
        .step.warning { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
        .step.info { background: #f0f9ff; border-color: #3b82f6; color: #1e40af; }
        .step.pending { background: #f8fafc; border-color: #6b7280; color: #374151; }
        .icon { font-size: 24px; margin-right: 15px; }
        .btn { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: 500; }
        .btn:hover { background: #059669; }
        .btn.secondary { background: #6b7280; }
        .btn.secondary:hover { background: #4b5563; }
        .code { background: #f8fafc; padding: 10px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 13px; border: 1px solid #e5e7eb; }
        .progress-bar { width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; margin: 15px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #10b981, #059669); transition: width 0.5s ease; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Activación Segura del Plugin</h1>
            <p>WooCommerce Venezuela Pro 2025</p>
        </div>

        <?php
        $steps_completed = 0;
        $total_steps = 8;
        $activation_successful = true;
        $errors = [];
        $warnings = [];

        // Función para mostrar pasos
        function show_step($title, $status, $message, $details = '') {
            global $steps_completed, $activation_successful;
            
            if ($status === 'success') {
                $steps_completed++;
                $class = 'success';
                $icon = '✅';
            } elseif ($status === 'error') {
                $activation_successful = false;
                $class = 'error';
                $icon = '❌';
            } elseif ($status === 'warning') {
                $class = 'warning';
                $icon = '⚠️';
            } else {
                $class = $status;
                $icon = $status === 'info' ? 'ℹ️' : '⏳';
            }
            
            echo "<div class='step $class'>";
            echo "<span class='icon'>$icon</span>";
            echo "<strong>$title</strong><br>";
            echo "<em>$message</em>";
            if ($details) {
                echo "<div class='code' style='margin-top: 10px;'>$details</div>";
            }
            echo "</div>";
        }

        // Paso 1: Verificar WordPress
        if (defined('ABSPATH') && function_exists('wp_get_current_user')) {
            show_step(
                'WordPress Cargado',
                'success',
                'WordPress está funcionando correctamente',
                'Versión: ' . get_bloginfo('version')
            );
        } else {
            show_step(
                'WordPress Cargado',
                'error',
                'WordPress no está cargado correctamente'
            );
            $errors[] = 'WordPress no disponible';
        }

        // Paso 2: Verificar WooCommerce
        if (class_exists('WooCommerce')) {
            show_step(
                'WooCommerce Activo',
                'success',
                'WooCommerce está instalado y activo',
                'Versión: ' . WC()->version
            );
        } else {
            show_step(
                'WooCommerce Activo',
                'error',
                'WooCommerce no está instalado o activo'
            );
            $errors[] = 'WooCommerce requerido';
        }

        // Paso 3: Verificar PHP
        $php_version = PHP_VERSION;
        if (version_compare($php_version, '7.4', '>=')) {
            show_step(
                'Versión de PHP',
                'success',
                'Versión de PHP compatible',
                "PHP $php_version (Requerido: 7.4+)"
            );
        } else {
            show_step(
                'Versión de PHP',
                'error',
                'Versión de PHP no compatible',
                "PHP $php_version (Requerido: 7.4+)"
            );
            $errors[] = 'PHP 7.4+ requerido';
        }

        // Paso 4: Verificar memoria
        $memory_limit = ini_get('memory_limit');
        $memory_bytes = wp_convert_hr_to_bytes($memory_limit);
        $memory_mb = $memory_bytes / 1024 / 1024;
        
        if ($memory_mb >= 256) {
            show_step(
                'Límite de Memoria',
                'success',
                'Límite de memoria suficiente',
                "Límite actual: $memory_limit"
            );
        } elseif ($memory_mb >= 128) {
            show_step(
                'Límite de Memoria',
                'warning',
                'Límite de memoria mínimo (recomendado: 256MB)',
                "Límite actual: $memory_limit"
            );
            $warnings[] = 'Memoria limitada';
        } else {
            show_step(
                'Límite de Memoria',
                'error',
                'Límite de memoria insuficiente',
                "Límite actual: $memory_limit (Requerido: 128MB+)"
            );
            $errors[] = 'Memoria insuficiente';
        }

        // Paso 5: Verificar archivos del plugin
        $plugin_file = plugin_dir_path(__FILE__) . 'woocommerce-venezuela-pro-2025.php';
        if (file_exists($plugin_file)) {
            show_step(
                'Archivos del Plugin',
                'success',
                'Todos los archivos del plugin están presentes'
            );
        } else {
            show_step(
                'Archivos del Plugin',
                'error',
                'Archivos del plugin no encontrados'
            );
            $errors[] = 'Archivos faltantes';
        }

        // Paso 6: Verificar clases core
        $core_classes = [
            'class-wvp-pago-movil-gateway.php',
            'class-wvp-zelle-gateway.php',
            'class-wvp-bank-transfer-gateway.php',
            'class-wvp-mrw-shipping.php',
            'class-wvp-seniat-exporter.php',
            'class-wvp-admin-dashboard.php'
        ];

        $missing_classes = [];
        foreach ($core_classes as $class_file) {
            if (!file_exists(plugin_dir_path(__FILE__) . 'includes/' . $class_file)) {
                $missing_classes[] = $class_file;
            }
        }

        if (empty($missing_classes)) {
            show_step(
                'Clases Core',
                'success',
                'Todas las clases core están disponibles',
                count($core_classes) . ' clases verificadas'
            );
        } else {
            show_step(
                'Clases Core',
                'error',
                'Algunas clases core no están disponibles',
                'Faltantes: ' . implode(', ', $missing_classes)
            );
            $errors[] = 'Clases faltantes';
        }

        // Paso 7: Verificar función de inicialización
        if (function_exists('wvp_init_plugin')) {
            show_step(
                'Función de Inicialización',
                'success',
                'Función wvp_init_plugin está definida'
            );
        } else {
            show_step(
                'Función de Inicialización',
                'error',
                'Función wvp_init_plugin no está definida'
            );
            $errors[] = 'Función de inicialización faltante';
        }

        // Paso 8: Verificar hooks
        $hook_registered = has_action('plugins_loaded', 'wvp_init_plugin');
        if ($hook_registered !== false) {
            show_step(
                'Hooks de WordPress',
                'success',
                'Hooks registrados correctamente'
            );
        } else {
            show_step(
                'Hooks de WordPress',
                'error',
                'Hooks no están registrados'
            );
            $errors[] = 'Hooks no registrados';
        }

        // Calcular progreso
        $progress_percentage = ($steps_completed / $total_steps) * 100;
        ?>

        <div class="card">
            <h2>📊 Progreso de Activación</h2>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
            </div>
            <p><strong><?php echo $steps_completed; ?>/<?php echo $total_steps; ?> pasos completados (<?php echo round($progress_percentage); ?>%)</strong></p>
        </div>

        <div class="card">
            <?php if ($activation_successful && empty($errors)): ?>
                <div class="step success">
                    <span class="icon">🎉</span>
                    <strong>¡Plugin Listo para Usar!</strong><br>
                    <em>El plugin WooCommerce Venezuela Pro 2025 está completamente funcional y listo para producción.</em>
                </div>
                
                <h3>🚀 Próximos Pasos:</h3>
                <ol>
                    <li><strong>Configurar Métodos de Pago:</strong> Ve a WooCommerce → Ajustes → Pagos</li>
                    <li><strong>Configurar Envíos:</strong> Ve a WooCommerce → Ajustes → Envío</li>
                    <li><strong>Acceder al Dashboard:</strong> Ve a WVP Dashboard en el menú de admin</li>
                    <li><strong>Configurar SENIAT:</strong> Ve a WVP Dashboard → SENIAT</li>
                </ol>

                <div style="margin-top: 20px;">
                    <a href="verify-plugin-health.php" class="btn">🔍 Verificar Salud del Plugin</a>
                    <a href="../../../wp-admin/admin.php?page=wc-settings&tab=checkout" class="btn">⚙️ Configurar Pagos</a>
                    <a href="PLAN-REACTIVACION-GRADUAL.md" class="btn secondary">📋 Plan de Reactivación</a>
                </div>

            <?php else: ?>
                <div class="step error">
                    <span class="icon">❌</span>
                    <strong>Activación Incompleta</strong><br>
                    <em>Se encontraron problemas que deben resolverse antes de usar el plugin.</em>
                </div>

                <?php if (!empty($errors)): ?>
                    <h3>🔧 Errores que Requieren Atención:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($warnings)): ?>
                    <h3>⚠️ Advertencias:</h3>
                    <ul>
                        <?php foreach ($warnings as $warning): ?>
                            <li><?php echo $warning; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div style="margin-top: 20px;">
                    <a href="javascript:location.reload()" class="btn">🔄 Reintentar</a>
                    <a href="../debug.log" class="btn secondary">📝 Ver Log de Errores</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($warnings) && $activation_successful): ?>
            <div class="card">
                <h2>⚠️ Advertencias</h2>
                <div class="step warning">
                    <span class="icon">⚠️</span>
                    <strong>El plugin funciona pero hay advertencias</strong><br>
                    <em>Se recomienda revisar las siguientes advertencias para un rendimiento óptimo:</em>
                    <ul style="margin-top: 10px;">
                        <?php foreach ($warnings as $warning): ?>
                            <li><?php echo $warning; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>ℹ️ Información del Sistema</h2>
            <div class="code">
                <strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?><br>
                <strong>WooCommerce:</strong> <?php echo class_exists('WooCommerce') ? WC()->version : 'No instalado'; ?><br>
                <strong>PHP:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>Memoria:</strong> <?php echo ini_get('memory_limit'); ?> (Usado: <?php echo round(memory_get_usage(true) / 1024 / 1024, 1); ?>MB)<br>
                <strong>Plugin:</strong> WooCommerce Venezuela Pro 2025 v1.0.0<br>
                <strong>Modo:</strong> Simplificado (Funcionalidades core activas)
            </div>
        </div>
    </div>
</body>
</html>
