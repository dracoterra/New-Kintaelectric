# WooCommerce Venezuela Suite - Guía de Instalación

## 📋 Requisitos del Sistema

### Requisitos Mínimos
- **WordPress**: 5.0 o superior
- **WooCommerce**: 5.0 o superior
- **PHP**: 7.4 o superior
- **MySQL**: 5.6 o superior
- **Memoria PHP**: 256MB mínimo
- **Espacio en disco**: 50MB

### Requisitos Recomendados
- **WordPress**: 6.0 o superior
- **WooCommerce**: 8.0 o superior
- **PHP**: 8.0 o superior
- **MySQL**: 8.0 o superior
- **Memoria PHP**: 512MB
- **Espacio en disco**: 100MB

### Plugins Recomendados
- **BCV Dólar Tracker**: Para integración automática con BCV
- **Elementor**: Para widgets avanzados
- **WP Rocket**: Para optimización de cache

## 🚀 Instalación Paso a Paso

### Paso 1: Preparación del Entorno

1. **Backup del sitio**
   ```bash
   # Crear backup completo
   wp db export backup_before_wvs.sql
   ```

2. **Verificar compatibilidad**
   ```php
   // Verificar versión de WordPress
   if (version_compare(get_bloginfo('version'), '5.0', '<')) {
       wp_die('WordPress 5.0 o superior requerido');
   }
   
   // Verificar WooCommerce
   if (!class_exists('WooCommerce')) {
       wp_die('WooCommerce requerido');
   }
   ```

### Paso 2: Instalación del Plugin

#### Opción A: Instalación Manual
1. **Descargar el plugin**
   ```bash
   # Clonar desde repositorio
   git clone https://github.com/kinta-electric/woocommerce-venezuela-suite.git
   ```

2. **Subir archivos**
   ```bash
   # Copiar a directorio de plugins
   cp -r woocommerce-venezuela-suite/ /path/to/wordpress/wp-content/plugins/
   ```

3. **Permisos de archivos**
   ```bash
   # Establecer permisos correctos
   chmod -R 755 /path/to/wordpress/wp-content/plugins/woocommerce-venezuela-suite/
   chown -R www-data:www-data /path/to/wordpress/wp-content/plugins/woocommerce-venezuela-suite/
   ```

#### Opción B: Instalación via WordPress Admin
1. Ir a **Plugins → Añadir nuevo**
2. Hacer clic en **Subir plugin**
3. Seleccionar archivo ZIP del plugin
4. Hacer clic en **Instalar ahora**
5. Hacer clic en **Activar plugin**

### Paso 3: Activación del Plugin

1. **Activar desde admin**
   - Ir a **Plugins → Plugins instalados**
   - Buscar "WooCommerce Venezuela Suite"
   - Hacer clic en **Activar**

2. **Verificar activación**
   ```php
   // Verificar que el plugin esté activo
   if (is_plugin_active('woocommerce-venezuela-suite/woocommerce-venezuela-suite.php')) {
       echo 'Plugin activado correctamente';
   }
   ```

### Paso 4: Configuración Inicial

1. **Acceder al panel de configuración**
   - Ir a **WooCommerce → Venezuela Suite**
   - O ir a **Configuración → Venezuela Suite**

2. **Configuración básica**
   ```php
   // Configuraciones por defecto
   wvs_set_option('plugin_version', '1.0.0');
   wvs_set_option('core_module_active', true);
   wvs_set_option('currency_module_active', false);
   wvs_set_option('payments_module_active', false);
   wvs_set_option('shipping_module_active', false);
   wvs_set_option('invoicing_module_active', false);
   wvs_set_option('communication_module_active', false);
   wvs_set_option('reports_module_active', false);
   wvs_set_option('widgets_module_active', false);
   ```

## ⚙️ Configuración Post-Instalación

### Configuración del Core Module

1. **Configuración de seguridad**
   ```php
   // Habilitar seguridad
   wvs_set_option('security_enabled', true);
   wvs_set_option('rate_limiting', true);
   wvs_set_option('log_security_events', true);
   ```

2. **Configuración de performance**
   ```php
   // Habilitar optimizaciones
   wvs_set_option('cache_enabled', true);
   wvs_set_option('cache_duration', 3600);
   wvs_set_option('minify_assets', true);
   ```

3. **Configuración de logs**
   ```php
   // Configurar sistema de logs
   wvs_set_option('log_level', 'info');
   wvs_set_option('log_retention_days', 30);
   wvs_set_option('log_file_size_limit', '10MB');
   ```

### Configuración del Currency Module

1. **Instalar BCV Dólar Tracker**
   ```bash
   # Instalar plugin BCV Dólar Tracker
   wp plugin install bcv-dolar-tracker --activate
   ```

2. **Configurar integración BCV**
   ```php
   // Configurar integración
   wvs_set_option('bcv_integration_enabled', true);
   wvs_set_option('bcv_cache_duration', 1800); // 30 minutos
   wvs_set_option('bcv_fallback_rate', 36.0); // Tasa de respaldo
   ```

3. **Configurar IGTF**
   ```php
   // Configurar IGTF
   wvs_set_option('igtf_enabled', true);
   wvs_set_option('igtf_rate', 3.0); // 3%
   wvs_set_option('igtf_auto_calculate', true);
   ```

4. **Activar Currency Module**
   ```php
   // Activar módulo
   wvs_set_module_active('currency', true);
   ```

### Configuración del Payments Module

1. **Configurar métodos de pago**
   ```php
   // Habilitar métodos de pago
   wvs_set_option('zelle_enabled', true);
   wvs_set_option('pago_movil_enabled', true);
   wvs_set_option('efectivo_enabled', true);
   wvs_set_option('cashea_enabled', false);
   wvs_set_option('crypto_enabled', false);
   ```

2. **Configurar Zelle**
   ```php
   // Configuración Zelle
   wvs_set_option('zelle_email', 'your-email@example.com');
   wvs_set_option('zelle_instructions', 'Enviar comprobante a WhatsApp');
   ```

3. **Configurar Pago Móvil**
   ```php
   // Configuración Pago Móvil
   wvs_set_option('pago_movil_numbers', [
       'Banesco' => '0412-1234567',
       'Mercantil' => '0414-1234567'
   ]);
   ```

4. **Activar Payments Module**
   ```php
   // Activar módulo
   wvs_set_module_active('payments', true);
   ```

### Configuración del Shipping Module

1. **Configurar métodos de envío**
   ```php
   // Habilitar métodos de envío
   wvs_set_option('local_shipping_enabled', true);
   wvs_set_option('national_shipping_enabled', true);
   wvs_set_option('express_shipping_enabled', false);
   ```

2. **Configurar envío local**
   ```php
   // Configuración envío local
   wvs_set_option('local_shipping_cost', 5.0);
   wvs_set_option('local_shipping_days', 1);
   wvs_set_option('local_shipping_zones', ['Caracas', 'Miranda']);
   ```

3. **Configurar envío nacional**
   ```php
   // Configuración envío nacional
   wvs_set_option('national_shipping_base_cost', 10.0);
   wvs_set_option('national_shipping_per_kg', 2.0);
   wvs_set_option('national_shipping_days', 3);
   ```

4. **Activar Shipping Module**
   ```php
   // Activar módulo
   wvs_set_module_active('shipping', true);
   ```

## 🔧 Configuración Avanzada

### Configuración de Base de Datos

1. **Crear tablas necesarias**
   ```php
   // Ejecutar creación de tablas
   WVS_Database::create_tables();
   ```

2. **Configurar índices**
   ```sql
   -- Crear índices para optimización
   CREATE INDEX idx_wvs_logs_timestamp ON wp_wvs_logs(timestamp);
   CREATE INDEX idx_wvs_logs_level ON wp_wvs_logs(level);
   CREATE INDEX idx_wvs_security_logs_timestamp ON wp_wvs_security_logs(timestamp);
   ```

### Configuración de Cache

1. **Configurar cache de WordPress**
   ```php
   // Configurar transients
   wvs_set_option('transient_cache_enabled', true);
   wvs_set_option('transient_cache_duration', 3600);
   ```

2. **Configurar cache de objetos**
   ```php
   // Configurar object cache
   wvs_set_option('object_cache_enabled', true);
   wvs_set_option('object_cache_duration', 1800);
   ```

### Configuración de Seguridad

1. **Configurar rate limiting**
   ```php
   // Configurar límites de tasa
   wvs_set_option('rate_limit_requests_per_minute', 60);
   wvs_set_option('rate_limit_requests_per_hour', 1000);
   ```

2. **Configurar validación**
   ```php
   // Configurar validación
   wvs_set_option('strict_validation', true);
   wvs_set_option('sanitize_all_inputs', true);
   ```

## 🧪 Verificación de Instalación

### Verificación Básica

1. **Verificar activación**
   ```php
   // Verificar que el plugin esté activo
   if (function_exists('wvs_get_version')) {
       echo 'Plugin activado: ' . wvs_get_version();
   }
   ```

2. **Verificar módulos**
   ```php
   // Verificar módulos activos
   $active_modules = wvs_get_active_modules();
   foreach ($active_modules as $module) {
       echo "Módulo activo: {$module}\n";
   }
   ```

3. **Verificar base de datos**
   ```php
   // Verificar tablas creadas
   $tables = WVS_Database::get_plugin_tables();
   foreach ($tables as $table) {
       echo "Tabla creada: {$table}\n";
   }
   ```

### Verificación Avanzada

1. **Test de funcionalidades**
   ```php
   // Ejecutar tests
   WVS_Test_Suite::run_all_tests();
   ```

2. **Verificar performance**
   ```php
   // Verificar métricas de performance
   $metrics = WVS_Performance::get_metrics();
   echo "Tiempo de carga: {$metrics['load_time']}ms\n";
   echo "Memoria usada: {$metrics['memory_usage']}MB\n";
   ```

3. **Verificar logs**
   ```php
   // Verificar sistema de logs
   $logs = WVS_Logger::get_recent_logs(10);
   foreach ($logs as $log) {
       echo "Log: {$log['message']}\n";
   }
   ```

## 🚨 Solución de Problemas

### Problemas Comunes

1. **Plugin no se activa**
   ```php
   // Verificar errores
   if (is_wp_error($result)) {
       echo "Error: " . $result->get_error_message();
   }
   ```

2. **Módulos no se cargan**
   ```php
   // Verificar dependencias
   $dependencies = WVS_Module_Manager::check_dependencies('currency');
   if (!$dependencies['met']) {
       echo "Dependencias faltantes: " . implode(', ', $dependencies['missing']);
   }
   ```

3. **Errores de base de datos**
   ```php
   // Verificar permisos de BD
   if (!WVS_Database::can_create_tables()) {
       echo "Sin permisos para crear tablas";
   }
   ```

### Logs de Debug

1. **Habilitar debug**
   ```php
   // En wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WVS_DEBUG', true);
   ```

2. **Verificar logs**
   ```bash
   # Ver logs de WordPress
   tail -f /path/to/wordpress/wp-content/debug.log
   
   # Ver logs del plugin
   tail -f /path/to/wordpress/wp-content/uploads/wvs-logs/debug.log
   ```

## 📞 Soporte

### Recursos de Ayuda

1. **Documentación**
   - [Guía de Módulos](MODULES.md)
   - [Guía de Configuración](CONFIGURATION.md)
   - [API Documentation](API.md)

2. **Soporte Técnico**
   - Email: soporte@kinta-electric.com
   - WhatsApp: +58-412-123-4567
   - Website: [kinta-electric.com](https://kinta-electric.com)

3. **Comunidad**
   - GitHub Issues: [Reportar problemas](https://github.com/kinta-electric/woocommerce-venezuela-suite/issues)
   - Discord: [Servidor de la comunidad](https://discord.gg/kinta-electric)

---

**Última actualización**: 2025-01-27  
**Versión**: 1.0.0
