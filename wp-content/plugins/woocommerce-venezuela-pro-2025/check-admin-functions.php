<?php
/**
 * Verificador de Funciones de Administración
 * Verifica qué funcionalidades están activas y aparecen en el admin
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    require_once( '../../../wp-config.php' );
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Funciones Admin - WVP 2025</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #16a085 0%, #f39c12 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; text-align: center; }
        .card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .function-item { padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #ddd; }
        .active { background: #f0f9ff; border-color: #10b981; color: #065f46; }
        .inactive { background: #fef2f2; border-color: #ef4444; color: #991b1b; }
        .warning { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
        .info { background: #f0f9ff; border-color: #3b82f6; color: #1e40af; }
        .icon { font-size: 18px; margin-right: 10px; }
        .code { background: #f8fafc; padding: 8px 12px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
        .menu-link { color: #3b82f6; text-decoration: none; font-weight: 500; }
        .menu-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Verificación de Funciones de Administración</h1>
            <p>WooCommerce Venezuela Pro 2025 - Estado de Funcionalidades</p>
        </div>

        <?php
        $functions_active = 0;
        $functions_inactive = 0;
        
        function check_function($name, $class_name, $description, $admin_menu = null) {
            global $functions_active, $functions_inactive;
            
            $class_exists = class_exists($class_name);
            $status = $class_exists ? 'active' : 'inactive';
            $icon = $class_exists ? '✅' : '❌';
            
            if ($class_exists) {
                $functions_active++;
            } else {
                $functions_inactive++;
            }
            
            echo "<div class='function-item $status'>";
            echo "<span class='icon'>$icon</span>";
            echo "<strong>$name</strong><br>";
            echo "<em>$description</em><br>";
            echo "<div class='code'>Clase: $class_name</div>";
            
            if ($admin_menu && $class_exists) {
                echo "<div style='margin-top: 8px;'>";
                echo "<a href='/wp-admin/admin.php?page=$admin_menu' class='menu-link'>🔗 Ir a $name</a>";
                echo "</div>";
            }
            
            echo "</div>";
        }
        ?>

        <div class="card">
            <h2>🎛️ Funcionalidades de Administración</h2>
            <div class="grid">
                <?php
                // Core Admin Functions
                check_function(
                    'Dashboard Principal',
                    'WVP_Admin_Dashboard',
                    'Panel principal de administración del plugin',
                    'wvp-dashboard'
                );
                
                check_function(
                    'SENIAT Exporter',
                    'WVP_SENIAT_Exporter',
                    'Herramienta para exportar reportes SENIAT',
                    'wvp-seniat'
                );
                
                check_function(
                    'Analytics Dashboard',
                    'WVP_Analytics_Dashboard',
                    'Dashboard de análisis y estadísticas avanzadas',
                    'wvp-analytics'
                );
                
                check_function(
                    'Setup Wizard',
                    'WVP_Setup_Wizard',
                    'Asistente de configuración inicial del plugin',
                    'wvp-setup'
                );
                
                check_function(
                    'Currency Modules Manager',
                    'WVP_Currency_Modules_Manager',
                    'Gestor de módulos de conversión de moneda',
                    'wvp-currency'
                );
                
                check_function(
                    'Venezuelan Taxes',
                    'WVP_Venezuelan_Taxes',
                    'Sistema de cálculo de impuestos venezolanos',
                    null
                );
                
                check_function(
                    'Product Display',
                    'WVP_Product_Display',
                    'Personalización de visualización de productos',
                    null
                );
                
                check_function(
                    'Notification System',
                    'WVP_Notification_System',
                    'Sistema de notificaciones del plugin',
                    null
                );
                ?>
            </div>
        </div>

        <div class="card">
            <h2>💳 Métodos de Pago</h2>
            <div class="grid">
                <?php
                check_function(
                    'Pago Móvil',
                    'WVP_Pago_Movil_Gateway',
                    'Gateway de pago móvil venezolano',
                    null
                );
                
                check_function(
                    'Zelle',
                    'WVP_Zelle_Gateway',
                    'Gateway de pagos Zelle en USD',
                    null
                );
                
                check_function(
                    'Transferencia Bancaria',
                    'WVP_Bank_Transfer_Gateway',
                    'Gateway de transferencias bancarias',
                    null
                );
                ?>
            </div>
        </div>

        <div class="card">
            <h2>🚚 Métodos de Envío</h2>
            <div class="grid">
                <?php
                check_function(
                    'MRW Shipping',
                    'WVP_MRW_Shipping',
                    'Método de envío MRW Venezuela',
                    null
                );
                
                check_function(
                    'Zoom Shipping',
                    'WVP_Zoom_Shipping',
                    'Método de envío Zoom',
                    null
                );
                
                check_function(
                    'Entrega Local',
                    'WVP_Local_Delivery_Shipping',
                    'Método de entrega local',
                    null
                );
                
                check_function(
                    'Venezuelan Shipping Manager',
                    'WVP_Venezuelan_Shipping',
                    'Gestor general de envíos venezolanos',
                    null
                );
                ?>
            </div>
        </div>

        <div class="card">
            <h2>🔧 Funcionalidades Adicionales</h2>
            <div class="grid">
                <?php
                check_function(
                    'Cache Manager',
                    'WVP_Cache_Manager',
                    'Sistema de gestión de cache',
                    null
                );
                
                check_function(
                    'Security Manager',
                    'WVP_Security_Manager',
                    'Sistema de seguridad del plugin',
                    null
                );
                
                check_function(
                    'Database Optimizer',
                    'WVP_Database_Optimizer',
                    'Optimizador de base de datos',
                    null
                );
                
                check_function(
                    'Assets Optimizer',
                    'WVP_Assets_Optimizer',
                    'Optimizador de assets (CSS/JS)',
                    null
                );
                
                check_function(
                    'Venezuelan Validator',
                    'WVP_Venezuelan_Validator',
                    'Validador de datos venezolanos (RIF, teléfonos, etc.)',
                    null
                );
                
                check_function(
                    'Testing Suite',
                    'WVP_Testing_Suite',
                    'Suite de pruebas del plugin',
                    null
                );
                ?>
            </div>
        </div>

        <div class="card">
            <h2>📊 Resumen</h2>
            <div class="grid">
                <div class="function-item info">
                    <span class="icon">📈</span>
                    <strong>Funcionalidades Activas</strong><br>
                    <em><?php echo $functions_active; ?> funcionalidades están cargadas y funcionando</em>
                </div>
                
                <div class="function-item warning">
                    <span class="icon">⏸️</span>
                    <strong>Funcionalidades Inactivas</strong><br>
                    <em><?php echo $functions_inactive; ?> funcionalidades están comentadas o desactivadas</em>
                </div>
                
                <div class="function-item <?php echo $functions_active > $functions_inactive ? 'active' : 'warning'; ?>">
                    <span class="icon"><?php echo $functions_active > $functions_inactive ? '🎉' : '⚠️'; ?></span>
                    <strong>Estado General</strong><br>
                    <em><?php echo round(($functions_active / ($functions_active + $functions_inactive)) * 100, 1); ?>% de funcionalidades activas</em>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>🔗 Enlaces Rápidos de Administración</h2>
            <div class="info">
                <p><strong>Si las funcionalidades están activas, deberías poder acceder a:</strong></p>
                <ul>
                    <li><a href="/wp-admin/admin.php?page=wvp-dashboard" class="menu-link">🏠 WVP Dashboard Principal</a></li>
                    <li><a href="/wp-admin/admin.php?page=wvp-settings" class="menu-link">⚙️ Configuración WVP</a></li>
                    <li><a href="/wp-admin/admin.php?page=wvp-seniat" class="menu-link">📊 SENIAT Exporter</a></li>
                    <li><a href="/wp-admin/admin.php?page=wvp-reports" class="menu-link">📈 Reportes WVP</a></li>
                    <li><a href="/wp-admin/admin.php?page=wc-settings&tab=checkout" class="menu-link">💳 Métodos de Pago WooCommerce</a></li>
                    <li><a href="/wp-admin/admin.php?page=wc-settings&tab=shipping" class="menu-link">🚚 Métodos de Envío WooCommerce</a></li>
                </ul>
            </div>
        </div>

        <?php if ($functions_inactive > 0): ?>
            <div class="card">
                <h2>🔄 Reactivar Más Funcionalidades</h2>
                <div class="warning">
                    <span class="icon">⚠️</span>
                    <strong>Hay <?php echo $functions_inactive; ?> funcionalidades inactivas</strong><br>
                    <em>Para activar más funcionalidades, consulta el archivo PLAN-REACTIVACION-GRADUAL.md</em>
                    
                    <h3 style="margin-top: 20px;">Funcionalidades que se pueden reactivar:</h3>
                    <ul>
                        <li><strong>Venezuelan Shipping Manager:</strong> Gestor avanzado de envíos</li>
                        <li><strong>Cache Manager:</strong> Sistema de optimización de cache</li>
                        <li><strong>Security Manager:</strong> Funciones de seguridad adicionales</li>
                        <li><strong>Database Optimizer:</strong> Optimización de base de datos</li>
                        <li><strong>Assets Optimizer:</strong> Optimización de CSS/JS</li>
                        <li><strong>Testing Suite:</strong> Herramientas de testing avanzadas</li>
                    </ul>
                    
                    <p><strong>⚠️ Importante:</strong> Reactivar una funcionalidad a la vez y probar que no cause errores de memoria.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>ℹ️ Información del Sistema</h2>
            <div class="code">
                <strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
                <strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?><br>
                <strong>WooCommerce:</strong> <?php echo class_exists('WooCommerce') ? WC()->version : 'No instalado'; ?><br>
                <strong>PHP:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>Memoria:</strong> <?php echo ini_get('memory_limit'); ?> (Usado: <?php echo round(memory_get_usage(true) / 1024 / 1024, 1); ?>MB)<br>
                <strong>Plugin WVP:</strong> v1.0.0 (Funcionalidades core + adicionales reactivadas)
            </div>
        </div>
    </div>
</body>
</html>
