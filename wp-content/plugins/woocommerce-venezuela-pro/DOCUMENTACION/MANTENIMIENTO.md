# Guía de Mantenimiento - WooCommerce Venezuela Pro

## Mantenimiento Preventivo

### Verificaciones Regulares

#### 1. Verificación Semanal
- **Logs de errores**: Revisar logs de WordPress y del plugin
- **Rendimiento**: Monitorear tiempos de carga
- **Actualizaciones**: Verificar actualizaciones de WordPress/WooCommerce
- **Backups**: Confirmar que los backups están funcionando

#### 2. Verificación Mensual
- **Base de datos**: Optimizar tablas de WordPress
- **Caché**: Limpiar caché del plugin
- **Logs antiguos**: Limpiar logs de más de 30 días
- **Seguridad**: Revisar logs de seguridad

#### 3. Verificación Trimestral
- **Actualización del plugin**: Verificar nuevas versiones
- **Compatibilidad**: Probar con nuevas versiones de WordPress/WooCommerce
- **Rendimiento**: Análisis completo de rendimiento
- **Seguridad**: Auditoría de seguridad

## Mantenimiento de Base de Datos

### Optimización de Tablas
```php
// Script de optimización mensual
function wvp_optimize_database() {
    global $wpdb;
    
    $tables = array(
        $wpdb->prefix . 'wvp_logs',
        $wpdb->prefix . 'wvp_stats',
        $wpdb->prefix . 'wvp_error_logs',
        $wpdb->prefix . 'wvp_security_logs'
    );
    
    foreach ($tables as $table) {
        $wpdb->query("OPTIMIZE TABLE $table");
    }
}

// Programar optimización mensual
if (!wp_next_scheduled('wvp_monthly_optimization')) {
    wp_schedule_event(time(), 'monthly', 'wvp_monthly_optimization');
}
add_action('wvp_monthly_optimization', 'wvp_optimize_database');
```

### Limpieza de Datos Antiguos
```php
// Limpiar logs antiguos
function wvp_cleanup_old_logs() {
    global $wpdb;
    
    $retention_days = get_option('wvp_log_retention_days', 30);
    $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
    
    // Limpiar logs de errores
    $wpdb->query($wpdb->prepare("
        DELETE FROM {$wpdb->prefix}wvp_error_logs 
        WHERE timestamp < %s
    ", $cutoff_date));
    
    // Limpiar logs de seguridad
    $wpdb->query($wpdb->prepare("
        DELETE FROM {$wpdb->prefix}wvp_security_logs 
        WHERE timestamp < %s
    ", $cutoff_date));
}

// Programar limpieza diaria
if (!wp_next_scheduled('wvp_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'wvp_daily_cleanup');
}
add_action('wvp_daily_cleanup', 'wvp_cleanup_old_logs');
```

## Mantenimiento de Caché

### Limpieza de Caché
```php
// Limpiar caché del plugin
function wvp_clear_plugin_cache() {
    // Limpiar transients del plugin
    global $wpdb;
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_wvp_%' 
        OR option_name LIKE '_transient_timeout_wvp_%'
    ");
    
    // Limpiar caché de objetos
    wp_cache_flush_group('wvp');
    
    // Limpiar caché de archivos
    $cache_dir = WP_CONTENT_DIR . '/cache/wvp/';
    if (is_dir($cache_dir)) {
        wvp_recursive_rmdir($cache_dir);
    }
}
```

### Invalidación Inteligente de Caché
```php
// Invalidar caché cuando sea necesario
function wvp_invalidate_cache_on_update($option_name, $old_value, $new_value) {
    if (strpos($option_name, 'wvp_') === 0) {
        wvp_clear_plugin_cache();
    }
}
add_action('updated_option', 'wvp_invalidate_cache_on_update', 10, 3);
```

## Monitoreo de Rendimiento

### Métricas Clave
```php
// Obtener métricas de rendimiento
function wvp_get_performance_metrics() {
    return array(
        'memory_usage' => memory_get_usage(true),
        'peak_memory' => memory_get_peak_usage(true),
        'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
        'database_queries' => get_num_queries(),
        'cache_hit_rate' => wvp_get_cache_hit_rate(),
        'bcv_rate_age' => wvp_get_bcv_rate_age()
    );
}
```

### Alertas de Rendimiento
```php
// Verificar rendimiento y enviar alertas
function wvp_check_performance() {
    $metrics = wvp_get_performance_metrics();
    
    // Alertar si el uso de memoria es alto
    if ($metrics['memory_usage'] > 128 * 1024 * 1024) { // 128MB
        wvp_send_performance_alert('High memory usage', $metrics);
    }
    
    // Alertar si hay muchas consultas
    if ($metrics['database_queries'] > 100) {
        wvp_send_performance_alert('High database queries', $metrics);
    }
    
    // Alertar si la tasa BCV es muy antigua
    if ($metrics['bcv_rate_age'] > 3600) { // 1 hora
        wvp_send_performance_alert('Old BCV rate', $metrics);
    }
}
```

## Troubleshooting Común

### Problema: Plugin no se activa
**Síntomas**: Error al activar el plugin
**Causas posibles**:
- WooCommerce no está instalado
- BCV Dólar Tracker no está activo
- Versión de PHP incompatible
- Memoria insuficiente

**Solución**:
```php
// Verificar dependencias
function wvp_check_dependencies() {
    $errors = array();
    
    if (!class_exists('WooCommerce')) {
        $errors[] = 'WooCommerce no está instalado';
    }
    
    if (!is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
        $errors[] = 'BCV Dólar Tracker no está activo';
    }
    
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = 'PHP 7.4+ requerido';
    }
    
    return $errors;
}
```

### Problema: Precios no se convierten
**Síntomas**: Precios en USD no se convierten a VES
**Causas posibles**:
- BCV Dólar Tracker no funciona
- Caché corrupto
- Error en la integración BCV

**Solución**:
```php
// Diagnosticar problema de conversión
function wvp_diagnose_price_conversion() {
    $diagnosis = array();
    
    // Verificar tasa BCV
    $bcv_rate = WVP_BCV_Integrator::get_rate();
    if ($bcv_rate === null) {
        $diagnosis[] = 'No se puede obtener tasa BCV';
    } else {
        $diagnosis[] = "Tasa BCV: {$bcv_rate}";
    }
    
    // Verificar caché
    $cached_rate = get_transient('wvp_bcv_rate');
    if ($cached_rate) {
        $diagnosis[] = "Tasa en caché: {$cached_rate}";
    } else {
        $diagnosis[] = 'No hay tasa en caché';
    }
    
    // Probar conversión
    $test_conversion = WVP_BCV_Integrator::convert_usd_to_ves(100);
    $diagnosis[] = "Conversión de prueba (100 USD): {$test_conversion} VES";
    
    return $diagnosis;
}
```

### Problema: IGTF no se aplica
**Síntomas**: Impuesto IGTF no aparece en el carrito
**Causas posibles**:
- IGTF deshabilitado en configuración
- Error en cálculo
- Hook no se ejecuta

**Solución**:
```php
// Diagnosticar problema de IGTF
function wvp_diagnose_igtf() {
    $diagnosis = array();
    
    // Verificar configuración
    $igtf_enabled = get_option('wvp_igtf_enabled', true);
    $igtf_rate = get_option('wvp_igtf_rate', 3.0);
    
    $diagnosis[] = "IGTF habilitado: " . ($igtf_enabled ? 'Sí' : 'No');
    $diagnosis[] = "Tasa IGTF: {$igtf_rate}%";
    
    // Verificar hooks
    if (has_action('woocommerce_cart_calculate_fees')) {
        $diagnosis[] = 'Hook de cálculo de tarifas está registrado';
    } else {
        $diagnosis[] = 'Hook de cálculo de tarifas NO está registrado';
    }
    
    return $diagnosis;
}
```

### Problema: Métodos de pago no aparecen
**Síntomas**: Pasarelas de pago venezolanas no están disponibles
**Causas posibles**:
- Pasarelas deshabilitadas
- Error en registro de pasarelas
- Conflicto con otros plugins

**Solución**:
```php
// Diagnosticar métodos de pago
function wvp_diagnose_payment_gateways() {
    $diagnosis = array();
    
    $gateways = array(
        'wvp_pago_movil' => 'Pago Móvil',
        'wvp_zelle' => 'Zelle',
        'wvp_efectivo' => 'Efectivo',
        'wvp_cashea' => 'Cashea'
    );
    
    foreach ($gateways as $gateway_id => $gateway_name) {
        $gateway = new $gateway_id();
        $enabled = $gateway->enabled === 'yes';
        $diagnosis[] = "{$gateway_name}: " . ($enabled ? 'Habilitado' : 'Deshabilitado');
    }
    
    return $diagnosis;
}
```

## Logs y Debugging

### Configuración de Logs
```php
// Configurar logging detallado
function wvp_setup_debug_logging() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Habilitar logging de errores
        ini_set('log_errors', 1);
        ini_set('error_log', WP_CONTENT_DIR . '/debug.log');
        
        // Habilitar logging del plugin
        add_action('init', function() {
            if (get_option('wvp_debug_mode')) {
                error_log('WVP Debug Mode: Enabled');
            }
        });
    }
}
```

### Análisis de Logs
```php
// Analizar logs de errores
function wvp_analyze_error_logs() {
    global $wpdb;
    
    $errors = $wpdb->get_results("
        SELECT level, message, COUNT(*) as count, MAX(timestamp) as last_occurrence
        FROM {$wpdb->prefix}wvp_error_logs
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY level, message
        ORDER BY count DESC
    ");
    
    return $errors;
}
```

## Actualizaciones

### Verificación de Actualizaciones
```php
// Verificar si hay actualizaciones disponibles
function wvp_check_for_updates() {
    $current_version = WVP_VERSION;
    $latest_version = wvp_get_latest_version();
    
    if (version_compare($current_version, $latest_version, '<')) {
        return array(
            'update_available' => true,
            'current_version' => $current_version,
            'latest_version' => $latest_version
        );
    }
    
    return array('update_available' => false);
}
```

### Proceso de Actualización
```php
// Ejecutar actualización
function wvp_run_update($from_version, $to_version) {
    // Backup de configuración
    wvp_backup_configuration();
    
    // Ejecutar migraciones específicas
    if (version_compare($from_version, '1.1.0', '<')) {
        wvp_migrate_to_1_1_0();
    }
    
    if (version_compare($from_version, '1.2.0', '<')) {
        wvp_migrate_to_1_2_0();
    }
    
    // Actualizar versión
    update_option('wvp_version', $to_version);
    
    // Limpiar caché
    wvp_clear_plugin_cache();
    
    // Log de actualización
    error_log("WVP Updated from {$from_version} to {$to_version}");
}
```

## Backup y Recuperación

### Backup de Configuración
```php
// Crear backup de configuración
function wvp_backup_configuration() {
    $config = array(
        'version' => WVP_VERSION,
        'settings' => get_option('wvp_settings'),
        'igtf_rate' => get_option('wvp_igtf_rate'),
        'bcv_cache_duration' => get_option('wvp_bcv_cache_duration'),
        'gateway_settings' => array()
    );
    
    // Backup de configuraciones de pasarelas
    $gateways = array('wvp_pago_movil', 'wvp_zelle', 'wvp_efectivo', 'wvp_cashea');
    foreach ($gateways as $gateway) {
        $config['gateway_settings'][$gateway] = get_option("woocommerce_{$gateway}_settings");
    }
    
    // Guardar backup
    update_option('wvp_config_backup', $config);
    update_option('wvp_backup_date', current_time('mysql'));
}
```

### Restauración de Configuración
```php
// Restaurar configuración desde backup
function wvp_restore_configuration() {
    $backup = get_option('wvp_config_backup');
    
    if (!$backup) {
        return false;
    }
    
    // Restaurar configuraciones
    update_option('wvp_settings', $backup['settings']);
    update_option('wvp_igtf_rate', $backup['igtf_rate']);
    update_option('wvp_bcv_cache_duration', $backup['bcv_cache_duration']);
    
    // Restaurar configuraciones de pasarelas
    foreach ($backup['gateway_settings'] as $gateway => $settings) {
        update_option("woocommerce_{$gateway}_settings", $settings);
    }
    
    return true;
}
```

## Monitoreo de Seguridad

### Detección de Intrusiones
```php
// Monitorear intentos de intrusión
function wvp_monitor_security() {
    global $wpdb;
    
    // Buscar intentos de login fallidos
    $failed_logins = $wpdb->get_results("
        SELECT ip_address, COUNT(*) as attempts
        FROM {$wpdb->prefix}wvp_security_logs
        WHERE action = 'failed_login'
        AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        GROUP BY ip_address
        HAVING attempts > 5
    ");
    
    foreach ($failed_logins as $login) {
        wvp_send_security_alert("Multiple failed logins from {$login->ip_address}");
    }
}
```

### Limpieza de Datos Sensibles
```php
// Limpiar datos sensibles de logs
function wvp_clean_sensitive_data() {
    global $wpdb;
    
    // Limpiar números de tarjeta de logs
    $wpdb->query("
        UPDATE {$wpdb->prefix}wvp_security_logs
        SET details = REPLACE(details, REGEXP_SUBSTR(details, '[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}'), '****-****-****-****')
    ");
}
```

## Conclusión

El mantenimiento regular del plugin WooCommerce Venezuela Pro es esencial para:

- ✅ **Rendimiento óptimo**: Limpieza regular de caché y logs
- ✅ **Seguridad**: Monitoreo de intrusiones y limpieza de datos sensibles
- ✅ **Estabilidad**: Resolución proactiva de problemas
- ✅ **Actualizaciones**: Mantenimiento de compatibilidad
- ✅ **Backup**: Protección de configuraciones importantes

Siguiendo esta guía de mantenimiento, se asegura el funcionamiento óptimo y seguro del plugin en producción.
