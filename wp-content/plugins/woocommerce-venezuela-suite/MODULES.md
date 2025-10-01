# WooCommerce Venezuela Suite - Guía de Módulos

## 📋 Índice de Módulos

Esta guía proporciona información detallada sobre cada módulo del plugin WooCommerce Venezuela Suite, incluyendo su propósito, funcionalidades, archivos y configuración.

## 🔧 Core Module

### Propósito
El módulo Core es la base fundamental del plugin, proporcionando funcionalidades esenciales que todos los demás módulos requieren.

### Funcionalidades Principales
- **Gestión de Módulos**: Sistema de activación/desactivación
- **Base de Datos Central**: Gestión unificada de datos
- **Sistema de Seguridad**: Validación y protección base
- **Optimización de Rendimiento**: Cache y optimización automática
- **Sistema de Logs**: Logging centralizado
- **Gestión de Configuración**: Panel de configuración unificado

### Archivos Principales
```
core/
├── class-wvs-core.php              # Clase principal del plugin
├── class-wvs-module-manager.php    # Gestor de módulos
├── class-wvs-database.php          # Gestión de base de datos
├── class-wvs-security.php          # Sistema de seguridad
├── class-wvs-performance.php       # Optimización de rendimiento
├── class-wvs-logger.php            # Sistema de logs
└── class-wvs-config-manager.php    # Gestión de configuración
```

### Configuración
- **Obligatorio**: Sí (siempre activo)
- **Dependencias**: Ninguna
- **Configuración**: Panel principal → Core Settings

### Hooks Principales
```php
// Activación de módulo
do_action('wvs_module_activated', $module_name);

// Desactivación de módulo
do_action('wvs_module_deactivated', $module_name);

// Inicialización del plugin
do_action('wvs_plugin_initialized');
```

---

## 💰 Currency Module

### Propósito
Gestiona todas las funcionalidades relacionadas con monedas, conversiones y cálculos de precios para el mercado venezolano.

### Funcionalidades Principales
- **Integración BCV**: Conexión automática con BCV Dólar Tracker
- **Conversión USD/VES**: Cálculo automático de precios
- **Gestión de IGTF**: Cálculo del impuesto IGTF (3% por defecto)
- **Múltiples Formatos**: Diferentes formas de mostrar precios
- **Cache Inteligente**: Optimización de consultas de conversión
- **Sistema de Fallback**: Respaldo cuando BCV no está disponible

### Archivos Principales
```
modules/currency/
├── class-wvs-currency-manager.php      # Gestor principal de monedas
├── class-wvs-price-calculator.php      # Calculadora de precios
├── class-wvs-bcv-integration.php       # Integración con BCV
├── class-wvs-igtf-manager.php          # Gestión de IGTF
├── class-wvs-price-display.php         # Visualización de precios
└── class-wvs-currency-cache.php        # Cache de conversiones
```

### Configuración
- **Obligatorio**: No (opcional)
- **Dependencias**: Core Module, BCV Dólar Tracker
- **Configuración**: Panel → Currency Settings

### Configuraciones Disponibles
```php
// Tasa de IGTF (por defecto 3%)
wvs_set_option('igtf_rate', 3.0);

// Duración del cache (en segundos)
wvs_set_option('currency_cache_duration', 3600);

// Formato de visualización
wvs_set_option('price_display_format', 'both'); // 'usd', 'ves', 'both'

// Habilitar cache
wvs_set_option('enable_currency_cache', true);
```

### Hooks Principales
```php
// Conversión de precio
$converted_price = apply_filters('wvs_convert_price', $usd_price, 'USD', 'VES');

// Actualización de tasa BCV
do_action('wvs_bcv_rate_updated', $new_rate, $old_rate);

// Cálculo de IGTF
$igtf_amount = apply_filters('wvs_calculate_igtf', $amount);
```

---

## 💳 Payments Module

### Propósito
Proporciona métodos de pago específicos para el mercado venezolano, incluyendo métodos tradicionales y modernos.

### Funcionalidades Principales
- **Zelle Integration**: Integración completa con Zelle
- **Pago Móvil**: Método de pago móvil venezolano
- **Efectivo**: Pagos en efectivo (USD/VES)
- **Cashea**: Integración con plataforma Cashea
- **Criptomonedas**: Bitcoin, USDT y otras criptos
- **Verificación Automática**: Confirmación automática de pagos
- **Gestión de Comisiones**: Cálculo automático de comisiones

### Archivos Principales
```
modules/payments/
├── class-wvs-payment-manager.php       # Gestor principal de pagos
├── class-wvs-payment-verification.php  # Verificación de pagos
├── gateways/
│   ├── class-wvs-gateway-zelle.php     # Gateway Zelle
│   ├── class-wvs-gateway-pago-movil.php # Gateway Pago Móvil
│   ├── class-wvs-gateway-efectivo.php  # Gateway Efectivo
│   ├── class-wvs-gateway-cashea.php    # Gateway Cashea
│   └── class-wvs-gateway-crypto.php   # Gateway Criptomonedas
└── class-wvs-payment-notifications.php # Notificaciones de pago
```

### Configuración
- **Obligatorio**: No (opcional)
- **Dependencias**: Core Module, Currency Module
- **Configuración**: WooCommerce → Settings → Payments

### Métodos de Pago Disponibles
1. **Zelle**
   - Configuración de email
   - Verificación automática
   - Notificaciones por email

2. **Pago Móvil**
   - Configuración de números
   - Verificación por referencia
   - Notificaciones WhatsApp

3. **Efectivo**
   - Opciones USD/VES
   - Instrucciones personalizadas
   - Verificación manual

4. **Cashea**
   - API integration
   - Verificación automática
   - Reportes de transacciones

5. **Criptomonedas**
   - Bitcoin, USDT, Ethereum
   - Wallets automáticos
   - Conversión automática

### Hooks Principales
```php
// Procesamiento de pago
$result = apply_filters('wvs_process_payment', $order_id, $gateway);

// Verificación de pago
do_action('wvs_payment_verified', $order_id, $payment_data);

// Notificación de pago
do_action('wvs_payment_notification', $order_id, $status);
```

---

## 🚚 Shipping Module

### Propósito
Gestiona métodos de envío específicos para Venezuela, incluyendo envíos locales, nacionales y express.

### Funcionalidades Principales
- **Envío Local**: Entrega en Caracas y área metropolitana
- **Envío Nacional**: Cobertura de todos los estados de Venezuela
- **Envío Express**: Entrega rápida (24-48 horas)
- **Calculadora de Costos**: Cálculo automático por peso y distancia
- **Tracking de Envíos**: Seguimiento en tiempo real
- **Gestión de Zonas**: Configuración por estados y ciudades

### Archivos Principales
```
modules/shipping/
├── class-wvs-shipping-manager.php      # Gestor principal de envíos
├── class-wvs-shipping-calculator.php   # Calculadora de costos
├── class-wvs-shipping-tracking.php     # Sistema de tracking
├── methods/
│   ├── class-wvs-shipping-local.php    # Envío local
│   ├── class-wvs-shipping-national.php # Envío nacional
│   └── class-wvs-shipping-express.php  # Envío express
└── class-wvs-shipping-zones.php        # Gestión de zonas
```

### Configuración
- **Obligatorio**: No (opcional)
- **Dependencias**: Core Module
- **Configuración**: WooCommerce → Settings → Shipping

### Métodos de Envío Disponibles
1. **Envío Local (Caracas)**
   - Área metropolitana
   - Entrega en 24 horas
   - Costo fijo

2. **Envío Nacional**
   - Todos los estados
   - Entrega en 3-5 días
   - Costo por peso y distancia

3. **Envío Express**
   - Principales ciudades
   - Entrega en 24-48 horas
   - Costo premium

### Zonas de Envío
```php
// Estados de Venezuela
$venezuelan_states = [
    'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
    'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
    'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
    'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
    'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
    'Vargas', 'Yaracuy', 'Zulia'
];
```

### Hooks Principales
```php
// Cálculo de costo de envío
$shipping_cost = apply_filters('wvs_calculate_shipping', $package, $method);

// Actualización de tracking
do_action('wvs_shipping_tracking_updated', $order_id, $tracking_data);

// Confirmación de entrega
do_action('wvs_shipping_delivered', $order_id, $delivery_data);
```

---

## 📄 Invoicing Module

### Propósito
Gestiona la facturación y cumplimiento fiscal para el mercado venezolano, incluyendo facturación híbrida y electrónica.

### Funcionalidades Principales
- **Facturación Híbrida**: Precios en USD y VES simultáneamente
- **Facturación Electrónica**: Integración con SENIAT
- **Reportes Fiscales**: Generación automática de reportes
- **Gestión de IVA**: Cálculo automático del IVA venezolano
- **Numeración Secuencial**: Sistema de numeración automática
- **Backup Automático**: Respaldo de facturas

### Archivos Principales
```
modules/invoicing/
├── class-wvs-invoice-manager.php       # Gestor principal de facturas
├── class-wvs-hybrid-invoicing.php      # Facturación híbrida
├── class-wvs-electronic-invoice.php    # Facturación electrónica
├── class-wvs-fiscal-reports.php        # Reportes fiscales
├── class-wvs-seniat-integration.php    # Integración SENIAT
├── class-wvs-invoice-generator.php     # Generador de facturas
└── class-wvs-invoice-backup.php        # Sistema de backup
```

### Configuración
- **Obligatorio**: No (opcional)
- **Dependencias**: Core Module, Currency Module
- **Configuración**: Panel → Invoicing Settings

### Tipos de Facturación
1. **Facturación Híbrida**
   - Precios en USD y VES
   - Conversión automática
   - Cumplimiento fiscal

2. **Facturación Electrónica**
   - Integración SENIAT
   - Validación automática
   - Envío electrónico

3. **Facturación Simple**
   - Facturas básicas
   - Numeración automática
   - Backup local

### Configuraciones Disponibles
```php
// Habilitar facturación híbrida
wvs_set_option('enable_hybrid_invoicing', true);

// Integración SENIAT
wvs_set_option('seniat_integration', true);
wvs_set_option('seniat_api_key', 'your_api_key');

// Configuración de IVA
wvs_set_option('iva_rate', 16.0); // 16% por defecto

// Numeración de facturas
wvs_set_option('invoice_numbering', 'sequential');
wvs_set_option('invoice_prefix', 'FAC-');
```

### Hooks Principales
```php
// Generación de factura
$invoice = apply_filters('wvs_generate_invoice', $order_id, $type);

// Envío a SENIAT
do_action('wvs_invoice_sent_to_seniat', $invoice_id, $response);

// Backup de factura
do_action('wvs_invoice_backed_up', $invoice_id, $backup_path);
```

---

## 📱 Communication Module

### Propósito
Gestiona todas las comunicaciones con clientes, incluyendo notificaciones, emails y chat en vivo.

### Funcionalidades Principales
- **Notificaciones WhatsApp**: Alertas automáticas por WhatsApp
- **Templates de Email**: Personalización completa de emails
- **SMS Integration**: Notificaciones por SMS
- **Chat en Vivo**: Soporte integrado
- **Recordatorios Automáticos**: Notificaciones programadas
- **Gestión de Comunicaciones**: Centralización de comunicaciones

### Archivos Principales
```
modules/communication/
├── class-wvs-notification-manager.php  # Gestor de notificaciones
├── class-wvs-whatsapp-integration.php  # Integración WhatsApp
├── class-wvs-email-templates.php       # Templates de email
├── class-wvs-sms-integration.php       # Integración SMS
├── class-wvs-live-chat.php             # Chat en vivo
├── class-wvs-notification-scheduler.php # Programador de notificaciones
└── class-wvs-communication-logger.php  # Log de comunicaciones
```

### Configuración
- **Obligatorio**: No (opcional)
- **Dependencias**: Core Module
- **Configuración**: Panel → Communication Settings

### Tipos de Comunicación
1. **WhatsApp**
   - Notificaciones automáticas
   - Templates personalizables
   - Integración con pedidos

2. **Email**
   - Templates profesionales
   - Personalización completa
   - Envío automático

3. **SMS**
   - Notificaciones críticas
   - Integración con proveedores
   - Costo optimizado

4. **Chat en Vivo**
   - Soporte integrado
   - Historial de conversaciones
   - Escalación automática

### Configuraciones Disponibles
```php
// Configuración WhatsApp
wvs_set_option('whatsapp_enabled', true);
wvs_set_option('whatsapp_api_key', 'your_api_key');
wvs_set_option('whatsapp_phone', '+584121234567');

// Configuración Email
wvs_set_option('email_templates_enabled', true);
wvs_set_option('email_from_name', 'Kinta Electric');
wvs_set_option('email_from_email', 'noreply@kinta-electric.com');

// Configuración SMS
wvs_set_option('sms_enabled', false);
wvs_set_option('sms_provider', 'twilio');
wvs_set_option('sms_api_key', 'your_sms_api_key');
```

### Hooks Principales
```php
// Envío de notificación
do_action('wvs_send_notification', $type, $recipient, $data);

// Actualización de template
do_action('wvs_template_updated', $template_id, $template_data);

// Registro de comunicación
do_action('wvs_communication_logged', $type, $recipient, $content);
```

---

## 📊 Reports Module

### Propósito
Proporciona reportes detallados y analytics avanzados para el análisis del negocio.

### Funcionalidades Principales
- **Reportes de Ventas**: Análisis detallado de ventas
- **Reportes Fiscales**: Reportes para cumplimiento fiscal
- **Analytics Avanzado**: Métricas de negocio
- **Dashboard Ejecutivo**: Vista general del negocio
- **Exportación de Datos**: Múltiples formatos de exportación
- **Métricas de Performance**: Análisis de rendimiento

### Archivos Principales
```
modules/reports/
├── class-wvs-reports-manager.php       # Gestor principal de reportes
├── class-wvs-sales-reports.php         # Reportes de ventas
├── class-wvs-tax-reports.php           # Reportes fiscales
├── class-wvs-analytics.php             # Analytics avanzado
├── class-wvs-dashboard.php             # Dashboard ejecutivo
├── class-wvs-data-export.php           # Exportación de datos
└── class-wvs-performance-metrics.php   # Métricas de performance
```

### Configuración
- **Obligatorio**: No (opcional)
- **Dependencias**: Core Module, Currency Module, Invoicing Module
- **Configuración**: Panel → Reports Settings

### Tipos de Reportes
1. **Reportes de Ventas**
   - Ventas por período
   - Productos más vendidos
   - Análisis de clientes
   - Tendencias de ventas

2. **Reportes Fiscales**
   - Reportes SENIAT
   - Declaraciones de IVA
   - Reportes de IGTF
   - Cumplimiento fiscal

3. **Analytics Avanzado**
   - Métricas de conversión
   - Análisis de comportamiento
   - Predicciones de ventas
   - Análisis de mercado

4. **Dashboard Ejecutivo**
   - KPIs principales
   - Gráficos interactivos
   - Alertas automáticas
   - Resumen ejecutivo

### Configuraciones Disponibles
```php
// Configuración de reportes
wvs_set_option('reports_enabled', true);
wvs_set_option('dashboard_enabled', true);
wvs_set_option('analytics_enabled', true);

// Configuración de exportación
wvs_set_option('export_formats', ['pdf', 'excel', 'csv']);
wvs_set_option('export_schedule', 'daily');

// Configuración de métricas
wvs_set_option('performance_tracking', true);
wvs_set_option('metrics_retention', 365); // días
```

### Hooks Principales
```php
// Generación de reporte
$report = apply_filters('wvs_generate_report', $type, $params);

// Actualización de métricas
do_action('wvs_metrics_updated', $metric_name, $value);

// Exportación de datos
do_action('wvs_data_exported', $format, $data);
```

---

## 🎨 Widgets Module

### Propósito
Proporciona widgets especializados para mostrar información relevante del negocio en el frontend.

### Funcionalidades Principales
- **Widget de Conversión**: Calculadora de moneda en tiempo real
- **Widget de Productos**: Mostrador de productos destacados
- **Widget de Estado**: Seguimiento de pedidos
- **Widget de Comparación**: Comparador de precios
- **Estilos Personalizables**: Múltiples temas visuales
- **Shortcodes Integrados**: Fácil integración en páginas

### Archivos Principales
```
modules/widgets/
├── class-wvs-widget-manager.php        # Gestor principal de widgets
├── class-wvs-widget-styles.php         # Sistema de estilos
├── widgets/
│   ├── class-wvs-currency-widget.php   # Widget de conversión
│   ├── class-wvs-product-widget.php    # Widget de productos
│   ├── class-wvs-order-status-widget.php # Widget de estado
│   └── class-wvs-price-comparison-widget.php # Widget de comparación
└── class-wvs-widget-shortcodes.php    # Shortcodes integrados
```

### Configuración
- **Obligatorio**: No (opcional)
- **Dependencias**: Core Module, Currency Module
- **Configuración**: Appearance → Widgets

### Widgets Disponibles
1. **Widget de Conversión de Moneda**
   - Conversión USD/VES en tiempo real
   - Múltiples formatos de visualización
   - Actualización automática

2. **Widget de Productos Destacados**
   - Productos más vendidos
   - Productos en oferta
   - Productos nuevos

3. **Widget de Estado de Pedidos**
   - Seguimiento de pedidos
   - Estado actual
   - Tiempo estimado de entrega

4. **Widget de Comparación de Precios**
   - Comparación con competencia
   - Análisis de mercado
   - Recomendaciones

### Estilos Disponibles
```php
// Estilos predefinidos
$available_styles = [
    'minimal' => 'Estilo minimalista',
    'modern' => 'Estilo moderno',
    'elegant' => 'Estilo elegante',
    'futuristic' => 'Estilo futurista',
    'vintage' => 'Estilo vintage'
];
```

### Shortcodes Disponibles
```php
// Widget de conversión
[wvs_currency_converter]

// Widget de productos
[wvs_products limit="5" category="electronics"]

// Widget de estado
[wvs_order_status order_id="123"]

// Widget de comparación
[wvs_price_comparison product_id="456"]
```

### Hooks Principales
```php
// Renderizado de widget
$output = apply_filters('wvs_widget_output', $widget_type, $args);

// Actualización de estilos
do_action('wvs_widget_styles_updated', $style_name, $style_data);

// Registro de shortcode
do_action('wvs_shortcode_registered', $shortcode_name, $callback);
```

---

## 🔧 Configuración Global

### Configuración de Módulos
```php
// Activar/desactivar módulos
wvs_set_module_active('currency', true);
wvs_set_module_active('payments', true);
wvs_set_module_active('shipping', false);

// Verificar estado de módulo
$is_active = wvs_is_module_active('currency');
```

### Configuración de Performance
```php
// Configuración de cache
wvs_set_option('cache_enabled', true);
wvs_set_option('cache_duration', 3600);

// Configuración de optimización
wvs_set_option('minify_assets', true);
wvs_set_option('lazy_loading', true);
```

### Configuración de Seguridad
```php
// Configuración de seguridad
wvs_set_option('security_enabled', true);
wvs_set_option('rate_limiting', true);
wvs_set_option('log_security_events', true);
```

---

## 📚 Recursos Adicionales

- **[API Documentation](docs/API.md)** - Documentación completa de la API
- **[Development Guide](docs/DEVELOPMENT.md)** - Guía para desarrolladores
- **[Configuration Guide](docs/CONFIGURATION.md)** - Guía de configuración
- **[Troubleshooting](docs/TROUBLESHOOTING.md)** - Solución de problemas

---

**Última actualización**: 2025-01-27  
**Versión**: 1.0.0
