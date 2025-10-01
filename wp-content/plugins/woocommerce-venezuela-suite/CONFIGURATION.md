# WooCommerce Venezuela Suite - Guía de Configuración

## 📋 Configuración General

### Acceso al Panel de Configuración

1. **Desde WordPress Admin**
   - Ir a **WooCommerce → Venezuela Suite**
   - O ir a **Configuración → Venezuela Suite**

2. **Desde WooCommerce**
   - Ir a **WooCommerce → Settings → Venezuela Suite**

3. **URL directa**
   ```
   /wp-admin/admin.php?page=woocommerce-venezuela-suite
   ```

### Estructura del Panel de Configuración

```
WooCommerce Venezuela Suite
├── 📊 Dashboard
├── 🔧 Core Settings
├── 💰 Currency Settings
├── 💳 Payment Settings
├── 🚚 Shipping Settings
├── 📄 Invoicing Settings
├── 📱 Communication Settings
├── 📊 Reports Settings
├── 🎨 Widgets Settings
├── ⚡ Performance Settings
├── 🔒 Security Settings
└── 📚 Help & Support
```

## 🔧 Core Settings

### Configuración Básica

#### Información del Plugin
```php
// Configuración básica
wvs_set_option('plugin_name', 'WooCommerce Venezuela Suite');
wvs_set_option('plugin_version', '1.0.0');
wvs_set_option('plugin_author', 'Kinta Electric');
wvs_set_option('plugin_url', 'https://kinta-electric.com');
```

#### Configuración de Módulos
```php
// Estado de módulos
wvs_set_option('core_module_active', true);        // Siempre activo
wvs_set_option('currency_module_active', false);    // Opcional
wvs_set_option('payments_module_active', false);    // Opcional
wvs_set_option('shipping_module_active', false);    // Opcional
wvs_set_option('invoicing_module_active', false);   // Opcional
wvs_set_option('communication_module_active', false); // Opcional
wvs_set_option('reports_module_active', false);      // Opcional
wvs_set_option('widgets_module_active', false);      // Opcional
```

#### Configuración de Logs
```php
// Sistema de logs
wvs_set_option('log_enabled', true);
wvs_set_option('log_level', 'info'); // debug, info, warning, error
wvs_set_option('log_retention_days', 30);
wvs_set_option('log_file_size_limit', '10MB');
wvs_set_option('log_rotate_daily', true);
```

#### Configuración de Performance
```php
// Optimización de rendimiento
wvs_set_option('cache_enabled', true);
wvs_set_option('cache_duration', 3600); // segundos
wvs_set_option('minify_assets', true);
wvs_set_option('lazy_loading', true);
wvs_set_option('optimize_database', true);
```

#### Configuración de Seguridad
```php
// Seguridad base
wvs_set_option('security_enabled', true);
wvs_set_option('rate_limiting', true);
wvs_set_option('log_security_events', true);
wvs_set_option('sanitize_all_inputs', true);
wvs_set_option('validate_all_outputs', true);
```

## 💰 Currency Settings

### Configuración de Moneda

#### Moneda Base
```php
// Configuración de monedas
wvs_set_option('base_currency', 'USD');
wvs_set_option('display_currency', 'VES');
wvs_set_option('currency_symbol_position', 'after'); // before, after
wvs_set_option('currency_decimal_places', 2);
wvs_set_option('currency_thousand_separator', '.');
wvs_set_option('currency_decimal_separator', ',');
```

#### Integración BCV
```php
// Configuración BCV
wvs_set_option('bcv_integration_enabled', true);
wvs_set_option('bcv_cache_duration', 1800); // 30 minutos
wvs_set_option('bcv_fallback_rate', 36.0);
wvs_set_option('bcv_auto_update', true);
wvs_set_option('bcv_update_interval', 30); // minutos
```

#### Configuración IGTF
```php
// Configuración IGTF
wvs_set_option('igtf_enabled', true);
wvs_set_option('igtf_rate', 3.0); // 3%
wvs_set_option('igtf_auto_calculate', true);
wvs_set_option('igtf_include_in_price', false);
wvs_set_option('igtf_display_separately', true);
```

#### Visualización de Precios
```php
// Configuración de visualización
wvs_set_option('price_display_format', 'both'); // usd, ves, both
wvs_set_option('price_display_style', 'modern'); // minimal, modern, elegant
wvs_set_option('price_display_animation', true);
wvs_set_option('price_display_tooltip', true);
```

### Configuración Avanzada

#### Cache de Conversiones
```php
// Cache de conversiones
wvs_set_option('conversion_cache_enabled', true);
wvs_set_option('conversion_cache_duration', 3600);
wvs_set_option('conversion_cache_cleanup', true);
wvs_set_option('conversion_cache_cleanup_interval', 24); // horas
```

#### Fallback y Respaldo
```php
// Sistema de respaldo
wvs_set_option('fallback_enabled', true);
wvs_set_option('fallback_rate', 36.0);
wvs_set_option('fallback_message', 'Tasa de cambio no disponible');
wvs_set_option('fallback_show_warning', true);
```

## 💳 Payment Settings

### Configuración General

#### Métodos de Pago Habilitados
```php
// Métodos de pago
wvs_set_option('zelle_enabled', true);
wvs_set_option('pago_movil_enabled', true);
wvs_set_option('efectivo_enabled', true);
wvs_set_option('cashea_enabled', false);
wvs_set_option('crypto_enabled', false);
```

#### Configuración de Comisiones
```php
// Comisiones
wvs_set_option('commission_enabled', true);
wvs_set_option('commission_rate', 2.5); // 2.5%
wvs_set_option('commission_minimum', 1.0);
wvs_set_option('commission_maximum', 50.0);
```

### Configuración por Método

#### Zelle
```php
// Configuración Zelle
wvs_set_option('zelle_email', 'your-email@example.com');
wvs_set_option('zelle_name', 'Tu Nombre');
wvs_set_option('zelle_instructions', 'Enviar comprobante a WhatsApp');
wvs_set_option('zelle_verification_required', true);
wvs_set_option('zelle_auto_verify', false);
```

#### Pago Móvil
```php
// Configuración Pago Móvil
wvs_set_option('pago_movil_numbers', [
    'Banesco' => '0412-1234567',
    'Mercantil' => '0414-1234567',
    'BBVA' => '0416-1234567'
]);
wvs_set_option('pago_movil_instructions', 'Enviar comprobante con referencia');
wvs_set_option('pago_movil_verification_required', true);
```

#### Efectivo
```php
// Configuración Efectivo
wvs_set_option('efectivo_usd_enabled', true);
wvs_set_option('efectivo_ves_enabled', true);
wvs_set_option('efectivo_instructions', 'Pago en efectivo al recibir');
wvs_set_option('efectivo_change_provided', true);
```

#### Cashea
```php
// Configuración Cashea
wvs_set_option('cashea_api_key', 'your_api_key');
wvs_set_option('cashea_secret_key', 'your_secret_key');
wvs_set_option('cashea_sandbox_mode', false);
wvs_set_option('cashea_webhook_url', 'https://yoursite.com/wvs-cashea-webhook');
```

#### Criptomonedas
```php
// Configuración Criptomonedas
wvs_set_option('crypto_bitcoin_enabled', true);
wvs_set_option('crypto_usdt_enabled', true);
wvs_set_option('crypto_ethereum_enabled', false);
wvs_set_option('crypto_wallet_address', 'your_wallet_address');
wvs_set_option('crypto_confirmation_blocks', 3);
```

### Configuración de Verificación

#### Verificación Automática
```php
// Verificación automática
wvs_set_option('auto_verification_enabled', true);
wvs_set_option('verification_timeout', 3600); // segundos
wvs_set_option('verification_retry_attempts', 3);
wvs_set_option('verification_retry_interval', 300); // segundos
```

#### Notificaciones de Pago
```php
// Notificaciones
wvs_set_option('payment_notifications_enabled', true);
wvs_set_option('payment_notification_email', 'admin@yoursite.com');
wvs_set_option('payment_notification_whatsapp', '+584121234567');
```

## 🚚 Shipping Settings

### Configuración General

#### Métodos de Envío Habilitados
```php
// Métodos de envío
wvs_set_option('local_shipping_enabled', true);
wvs_set_option('national_shipping_enabled', true);
wvs_set_option('express_shipping_enabled', false);
```

#### Configuración de Zonas
```php
// Zonas de envío
wvs_set_option('shipping_zones', [
    'local' => ['Caracas', 'Miranda'],
    'national' => ['Zulia', 'Carabobo', 'Lara', 'Aragua'],
    'remote' => ['Amazonas', 'Delta Amacuro']
]);
```

### Configuración por Método

#### Envío Local
```php
// Envío local
wvs_set_option('local_shipping_cost', 5.0);
wvs_set_option('local_shipping_free_threshold', 100.0);
wvs_set_option('local_shipping_days', 1);
wvs_set_option('local_shipping_zones', ['Caracas', 'Miranda']);
```

#### Envío Nacional
```php
// Envío nacional
wvs_set_option('national_shipping_base_cost', 10.0);
wvs_set_option('national_shipping_per_kg', 2.0);
wvs_set_option('national_shipping_free_threshold', 200.0);
wvs_set_option('national_shipping_days', 3);
```

#### Envío Express
```php
// Envío express
wvs_set_option('express_shipping_cost', 25.0);
wvs_set_option('express_shipping_per_kg', 5.0);
wvs_set_option('express_shipping_days', 1);
wvs_set_option('express_shipping_zones', ['Caracas', 'Maracaibo', 'Valencia']);
```

### Configuración de Tracking

#### Sistema de Tracking
```php
// Tracking
wvs_set_option('tracking_enabled', true);
wvs_set_option('tracking_provider', 'manual'); // manual, api
wvs_set_option('tracking_api_key', 'your_api_key');
wvs_set_option('tracking_update_interval', 3600); // segundos
```

#### Notificaciones de Envío
```php
// Notificaciones de envío
wvs_set_option('shipping_notifications_enabled', true);
wvs_set_option('shipping_notification_email', true);
wvs_set_option('shipping_notification_whatsapp', true);
wvs_set_option('shipping_notification_sms', false);
```

## 📄 Invoicing Settings

### Configuración General

#### Tipos de Facturación
```php
// Tipos de facturación
wvs_set_option('hybrid_invoicing_enabled', true);
wvs_set_option('electronic_invoicing_enabled', false);
wvs_set_option('simple_invoicing_enabled', true);
```

#### Configuración de IVA
```php
// Configuración IVA
wvs_set_option('iva_enabled', true);
wvs_set_option('iva_rate', 16.0); // 16%
wvs_set_option('iva_auto_calculate', true);
wvs_set_option('iva_include_in_price', false);
```

### Configuración Híbrida

#### Facturación Híbrida
```php
// Facturación híbrida
wvs_set_option('hybrid_usd_enabled', true);
wvs_set_option('hybrid_ves_enabled', true);
wvs_set_option('hybrid_display_both', true);
wvs_set_option('hybrid_primary_currency', 'USD');
```

#### Numeración de Facturas
```php
// Numeración
wvs_set_option('invoice_numbering', 'sequential'); // sequential, random
wvs_set_option('invoice_prefix', 'FAC-');
wvs_set_option('invoice_suffix', '');
wvs_set_option('invoice_start_number', 1);
```

### Configuración Electrónica

#### Integración SENIAT
```php
// Integración SENIAT
wvs_set_option('seniat_integration_enabled', false);
wvs_set_option('seniat_api_key', 'your_api_key');
wvs_set_option('seniat_api_url', 'https://api.seniat.gov.ve');
wvs_set_option('seniat_sandbox_mode', true);
```

#### Configuración Fiscal
```php
// Configuración fiscal
wvs_set_option('fiscal_reports_enabled', true);
wvs_set_option('fiscal_report_frequency', 'monthly'); // daily, weekly, monthly
wvs_set_option('fiscal_report_auto_generate', true);
wvs_set_option('fiscal_report_retention', 365); // días
```

### Configuración de Backup

#### Sistema de Backup
```php
// Backup
wvs_set_option('invoice_backup_enabled', true);
wvs_set_option('invoice_backup_frequency', 'daily'); // daily, weekly, monthly
wvs_set_option('invoice_backup_retention', 365); // días
wvs_set_option('invoice_backup_location', 'local'); // local, cloud
```

## 📱 Communication Settings

### Configuración General

#### Métodos de Comunicación
```php
// Métodos de comunicación
wvs_set_option('whatsapp_enabled', true);
wvs_set_option('email_enabled', true);
wvs_set_option('sms_enabled', false);
wvs_set_option('live_chat_enabled', false);
```

### Configuración WhatsApp

#### Integración WhatsApp
```php
// Configuración WhatsApp
wvs_set_option('whatsapp_api_key', 'your_api_key');
wvs_set_option('whatsapp_phone', '+584121234567');
wvs_set_option('whatsapp_business_account', true);
wvs_set_option('whatsapp_webhook_url', 'https://yoursite.com/wvs-whatsapp-webhook');
```

#### Templates WhatsApp
```php
// Templates
wvs_set_option('whatsapp_order_confirmation', true);
wvs_set_option('whatsapp_payment_confirmation', true);
wvs_set_option('whatsapp_shipping_notification', true);
wvs_set_option('whatsapp_delivery_confirmation', true);
```

### Configuración Email

#### Configuración SMTP
```php
// Configuración SMTP
wvs_set_option('email_smtp_enabled', true);
wvs_set_option('email_smtp_host', 'smtp.gmail.com');
wvs_set_option('email_smtp_port', 587);
wvs_set_option('email_smtp_username', 'your_email@gmail.com');
wvs_set_option('email_smtp_password', 'your_password');
wvs_set_option('email_smtp_encryption', 'tls');
```

#### Templates Email
```php
// Templates de email
wvs_set_option('email_from_name', 'Kinta Electric');
wvs_set_option('email_from_email', 'noreply@kinta-electric.com');
wvs_set_option('email_reply_to', 'soporte@kinta-electric.com');
wvs_set_option('email_template_style', 'modern'); // minimal, modern, elegant
```

### Configuración SMS

#### Integración SMS
```php
// Configuración SMS
wvs_set_option('sms_provider', 'twilio'); // twilio, nexmo, local
wvs_set_option('sms_api_key', 'your_api_key');
wvs_set_option('sms_api_secret', 'your_api_secret');
wvs_set_option('sms_from_number', '+1234567890');
```

#### Configuración de Envío
```php
// Configuración de envío
wvs_set_option('sms_enabled_for_orders', true);
wvs_set_option('sms_enabled_for_payments', true);
wvs_set_option('sms_enabled_for_shipping', false);
wvs_set_option('sms_cost_per_message', 0.05);
```

## 📊 Reports Settings

### Configuración General

#### Tipos de Reportes
```php
// Tipos de reportes
wvs_set_option('sales_reports_enabled', true);
wvs_set_option('tax_reports_enabled', true);
wvs_set_option('analytics_enabled', true);
wvs_set_option('dashboard_enabled', true);
```

#### Configuración de Generación
```php
// Generación de reportes
wvs_set_option('report_auto_generate', true);
wvs_set_option('report_frequency', 'daily'); // daily, weekly, monthly
wvs_set_option('report_retention', 365); // días
wvs_set_option('report_export_formats', ['pdf', 'excel', 'csv']);
```

### Configuración de Analytics

#### Métricas Habilitadas
```php
// Métricas
wvs_set_option('conversion_tracking', true);
wvs_set_option('customer_analytics', true);
wvs_set_option('product_analytics', true);
wvs_set_option('sales_analytics', true);
wvs_set_option('performance_analytics', true);
```

#### Configuración de Dashboard
```php
// Dashboard
wvs_set_option('dashboard_widgets', [
    'sales_summary',
    'top_products',
    'recent_orders',
    'conversion_rate',
    'revenue_chart'
]);
wvs_set_option('dashboard_refresh_interval', 300); // segundos
```

## 🎨 Widgets Settings

### Configuración General

#### Widgets Habilitados
```php
// Widgets
wvs_set_option('currency_widget_enabled', true);
wvs_set_option('product_widget_enabled', true);
wvs_set_option('order_status_widget_enabled', true);
wvs_set_option('price_comparison_widget_enabled', false);
```

#### Configuración de Estilos
```php
// Estilos
wvs_set_option('widget_style', 'modern'); // minimal, modern, elegant, futuristic, vintage
wvs_set_option('widget_animation', true);
wvs_set_option('widget_responsive', true);
wvs_set_option('widget_custom_css', '');
```

### Configuración por Widget

#### Widget de Conversión
```php
// Widget de conversión
wvs_set_option('currency_widget_currencies', ['USD', 'VES']);
wvs_set_option('currency_widget_auto_update', true);
wvs_set_option('currency_widget_update_interval', 300); // segundos
wvs_set_option('currency_widget_show_igtf', true);
```

#### Widget de Productos
```php
// Widget de productos
wvs_set_option('product_widget_limit', 5);
wvs_set_option('product_widget_category', '');
wvs_set_option('product_widget_show_price', true);
wvs_set_option('product_widget_show_image', true);
```

#### Widget de Estado
```php
// Widget de estado
wvs_set_option('order_status_widget_show_tracking', true);
wvs_set_option('order_status_widget_show_estimated_delivery', true);
wvs_set_option('order_status_widget_show_contact_info', true);
```

## ⚡ Performance Settings

### Configuración de Cache

#### Cache General
```php
// Cache general
wvs_set_option('cache_enabled', true);
wvs_set_option('cache_duration', 3600); // segundos
wvs_set_option('cache_cleanup_enabled', true);
wvs_set_option('cache_cleanup_interval', 24); // horas
```

#### Cache de Base de Datos
```php
// Cache de BD
wvs_set_option('db_cache_enabled', true);
wvs_set_option('db_cache_duration', 1800); // segundos
wvs_set_option('db_cache_queries', true);
wvs_set_option('db_cache_results', true);
```

#### Cache de Assets
```php
// Cache de assets
wvs_set_option('asset_cache_enabled', true);
wvs_set_option('asset_minification', true);
wvs_set_option('asset_compression', true);
wvs_set_option('asset_cdn_enabled', false);
```

### Configuración de Optimización

#### Optimización de Base de Datos
```php
// Optimización de BD
wvs_set_option('db_optimization_enabled', true);
wvs_set_option('db_optimization_frequency', 'weekly'); // daily, weekly, monthly
wvs_set_option('db_cleanup_enabled', true);
wvs_set_option('db_cleanup_retention', 90); // días
```

#### Optimización de Consultas
```php
// Optimización de consultas
wvs_set_option('query_optimization_enabled', true);
wvs_set_option('query_caching_enabled', true);
wvs_set_option('query_monitoring_enabled', true);
wvs_set_option('slow_query_threshold', 1.0); // segundos
```

## 🔒 Security Settings

### Configuración General

#### Seguridad Base
```php
// Seguridad base
wvs_set_option('security_enabled', true);
wvs_set_option('security_logging_enabled', true);
wvs_set_option('security_monitoring_enabled', true);
wvs_set_option('security_alerts_enabled', true);
```

#### Rate Limiting
```php
// Rate limiting
wvs_set_option('rate_limiting_enabled', true);
wvs_set_option('rate_limit_requests_per_minute', 60);
wvs_set_option('rate_limit_requests_per_hour', 1000);
wvs_set_option('rate_limit_requests_per_day', 10000);
```

#### Validación de Datos
```php
// Validación
wvs_set_option('input_validation_enabled', true);
wvs_set_option('output_sanitization_enabled', true);
wvs_set_option('sql_injection_protection', true);
wvs_set_option('xss_protection', true);
```

### Configuración de Logs

#### Logs de Seguridad
```php
// Logs de seguridad
wvs_set_option('security_log_enabled', true);
wvs_set_option('security_log_level', 'warning'); // debug, info, warning, error
wvs_set_option('security_log_retention', 90); // días
wvs_set_option('security_log_rotation', true);
```

#### Monitoreo de Eventos
```php
// Monitoreo
wvs_set_option('event_monitoring_enabled', true);
wvs_set_option('failed_login_monitoring', true);
wvs_set_option('suspicious_activity_monitoring', true);
wvs_set_option('admin_action_monitoring', true);
```

## 📚 Help & Support

### Configuración de Soporte

#### Información de Contacto
```php
// Información de contacto
wvs_set_option('support_email', 'soporte@kinta-electric.com');
wvs_set_option('support_phone', '+58-412-123-4567');
wvs_set_option('support_whatsapp', '+58-412-123-4567');
wvs_set_option('support_website', 'https://kinta-electric.com');
```

#### Configuración de Tickets
```php
// Sistema de tickets
wvs_set_option('ticket_system_enabled', true);
wvs_set_option('ticket_auto_response', true);
wvs_set_option('ticket_escalation_enabled', true);
wvs_set_option('ticket_priority_levels', ['low', 'medium', 'high', 'critical']);
```

### Configuración de Documentación

#### Documentación Integrada
```php
// Documentación
wvs_set_option('help_docs_enabled', true);
wvs_set_option('help_docs_url', 'https://docs.kinta-electric.com');
wvs_set_option('help_videos_enabled', true);
wvs_set_option('help_videos_url', 'https://videos.kinta-electric.com');
```

## 🔄 Configuración por Programación

### Funciones de Configuración

#### Establecer Opciones
```php
// Establecer opción
wvs_set_option($option_name, $value);

// Establecer múltiples opciones
wvs_set_options([
    'option1' => 'value1',
    'option2' => 'value2'
]);

// Establecer opción con validación
wvs_set_option_validated($option_name, $value, $validation_callback);
```

#### Obtener Opciones
```php
// Obtener opción
$value = wvs_get_option($option_name, $default_value);

// Obtener múltiples opciones
$options = wvs_get_options(['option1', 'option2']);

// Obtener todas las opciones
$all_options = wvs_get_all_options();
```

#### Eliminar Opciones
```php
// Eliminar opción
wvs_delete_option($option_name);

// Eliminar múltiples opciones
wvs_delete_options(['option1', 'option2']);

// Eliminar todas las opciones
wvs_delete_all_options();
```

### Validación de Configuración

#### Validadores Disponibles
```php
// Validadores
wvs_validate_email($email);
wvs_validate_phone($phone);
wvs_validate_currency($currency);
wvs_validate_percentage($percentage);
wvs_validate_url($url);
wvs_validate_api_key($api_key);
```

#### Configuración con Validación
```php
// Configuración con validación
wvs_set_option_with_validation('email', $email, 'wvs_validate_email');
wvs_set_option_with_validation('phone', $phone, 'wvs_validate_phone');
wvs_set_option_with_validation('api_key', $api_key, 'wvs_validate_api_key');
```

## 🧪 Testing de Configuración

### Verificación de Configuración

#### Verificar Configuración Completa
```php
// Verificar configuración
$config_status = wvs_verify_configuration();
if ($config_status['valid']) {
    echo 'Configuración válida';
} else {
    echo 'Errores encontrados: ' . implode(', ', $config_status['errors']);
}
```

#### Verificar Módulos
```php
// Verificar módulos
$modules_status = wvs_verify_modules();
foreach ($modules_status as $module => $status) {
    if ($status['valid']) {
        echo "Módulo {$module}: OK";
    } else {
        echo "Módulo {$module}: ERROR - " . $status['error'];
    }
}
```

#### Verificar Dependencias
```php
// Verificar dependencias
$dependencies_status = wvs_verify_dependencies();
foreach ($dependencies_status as $dependency => $status) {
    if ($status['met']) {
        echo "Dependencia {$dependency}: OK";
    } else {
        echo "Dependencia {$dependency}: FALTANTE";
    }
}
```

### Testing de Funcionalidades

#### Test de Configuración
```php
// Ejecutar tests
WVS_Config_Test::run_all_tests();
WVS_Config_Test::test_currency_config();
WVS_Config_Test::test_payment_config();
WVS_Config_Test::test_shipping_config();
```

---

**Última actualización**: 2025-01-27  
**Versión**: 1.0.0
