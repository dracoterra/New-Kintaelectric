# WooCommerce Venezuela Pro 2025

**Plugin especializado para comercio electrónico en Venezuela con WooCommerce**

[![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)](https://github.com/woocommerce-venezuela-pro-2025)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-green.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL%20v2-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 🚀 Características Principales

### 💱 Conversión de Monedas
- **Conversión automática USD ↔ VES**
- **Integración con BCV** (Banco Central de Venezuela)
- **Tipos de cambio en tiempo real**
- **Sistema de respaldo** para fallos de BCV
- **Cache inteligente** para optimización

### 🏛️ Cumplimiento Fiscal Venezolano
- **Cálculo automático de IVA** (16%)
- **Cálculo de IGTF** (3%) configurable
- **Reportes SENIAT** completos
- **Libro de ventas** electrónico
- **Facturas electrónicas** en múltiples formatos

### 💳 Métodos de Pago Locales
- **Pago Móvil** venezolano
- **Transferencias bancarias** locales
- **Zelle** para pagos internacionales
- **Validación de datos** venezolanos

### 🚚 Envíos Especializados
- **MRW** - Mensajería nacional
- **Zoom** - Servicio de envíos
- **Mensajero** - Entrega local
- **Entrega local** configurable
- **Cálculo de costos** por estado

### 📊 Analytics y Reportes
- **Dashboard completo** de métricas
- **Reportes de ventas** detallados
- **Análisis de productos** populares
- **Métricas de clientes** activos
- **Exportación** en múltiples formatos

### 🔔 Sistema de Notificaciones
- **Notificaciones por email** automáticas
- **Integración WhatsApp** (opcional)
- **Alertas de stock** bajo
- **Notificaciones de pedidos**
- **Sistema multi-canal**

## 📋 Requisitos del Sistema

- **WordPress** 5.0 o superior
- **WooCommerce** 5.0 o superior
- **PHP** 7.4 o superior
- **MySQL** 5.6 o superior
- **Memoria PHP** 256MB mínimo
- **Espacio en disco** 50MB mínimo

## 🛠️ Instalación

### Instalación Manual

1. **Descargar el plugin**
   ```bash
   # Descargar desde GitHub
   git clone https://github.com/woocommerce-venezuela-pro-2025.git
   ```

2. **Subir archivos**
   - Subir la carpeta `woocommerce-venezuela-pro-2025` a `/wp-content/plugins/`
   - Verificar permisos de archivos (644 para archivos, 755 para directorios)

3. **Activar el plugin**
   - Ir a **Plugins > Plugins Instalados**
   - Activar **'WooCommerce Venezuela Pro 2025'**

4. **Configuración inicial**
   - Ir a **WooCommerce > Configuración > Venezuela Pro**
   - Configurar tipo de cambio BCV
   - Habilitar funcionalidades necesarias

### Instalación via WP-CLI

```bash
# Instalar y activar
wp plugin install woocommerce-venezuela-pro-2025.zip --activate

# Configurar opciones básicas
wp option update wvp_bcv_rate 36.5
wp option update wvp_enable_iva true
wp option update wvp_enable_igtf false
wp option update wvp_dual_currency true
```

## ⚙️ Configuración

### 1. Configuración Básica

#### Tipo de Cambio BCV
```php
// Configurar tipo de cambio actual
update_option('wvp_bcv_rate', 36.5);

// Habilitar actualización automática
update_option('wvp_auto_update_bcv', true);
```

#### Impuestos Venezolanos
```php
// Habilitar IVA (16%)
update_option('wvp_enable_iva', true);

// Habilitar IGTF (3%) - opcional
update_option('wvp_enable_igtf', false);

// Configurar base de cálculo
update_option('wvp_tax_base', 'subtotal');
```

### 2. Configuración de Monedas

```php
// Configurar monedas soportadas
update_option('woocommerce_currency', 'USD');
update_option('wvp_supported_currencies', array('USD', 'VES'));

// Configurar símbolos de moneda
update_option('wvp_currency_symbols', array(
    'USD' => '$',
    'VES' => 'Bs'
));
```

### 3. Métodos de Pago

#### Pago Móvil
```php
// Habilitar Pago Móvil
update_option('wvp_enable_pago_movil', true);

// Configurar cuentas
update_option('wvp_pago_movil_accounts', array(
    '0412-1234567' => 'Banco de Venezuela',
    '0424-9876543' => 'Banesco'
));
```

#### Transferencias Bancarias
```php
// Habilitar transferencias
update_option('wvp_enable_bank_transfer', true);

// Configurar bancos
update_option('wvp_bank_accounts', array(
    '0102-1234-5678-9012' => 'Banco de Venezuela',
    '0134-5678-9012-3456' => 'Banesco'
));
```

### 4. Métodos de Envío

#### MRW
```php
// Habilitar MRW
update_option('wvp_enable_mrw', true);

// Configurar costos por estado
update_option('wvp_mrw_costs', array(
    'Distrito Capital' => 15.00,
    'Miranda' => 18.00,
    'Carabobo' => 20.00
));
```

#### Zoom
```php
// Habilitar Zoom
update_option('wvp_enable_zoom', true);

// Configurar costos
update_option('wvp_zoom_costs', array(
    'Distrito Capital' => 12.00,
    'Miranda' => 15.00,
    'Carabobo' => 18.00
));
```

## 🎯 Uso

### Conversión de Monedas

El plugin convierte automáticamente los precios de USD a VES usando el tipo de cambio del BCV:

```php
// Conversión manual
$currency_converter = WVP_Simple_Currency_Converter::get_instance();
$ves_price = $currency_converter->convert_price(100, 'USD', 'VES');
echo "100 USD = {$ves_price} VES";
```

### Cálculo de Impuestos

```php
// Cálculo de IVA
$tax_calculator = WVP_Venezuelan_Taxes::get_instance();
$iva_amount = $tax_calculator->calculate_iva(100);
echo "IVA sobre $100: {$iva_amount}";

// Cálculo de IGTF
$igtf_amount = $tax_calculator->calculate_igtf(100);
echo "IGTF sobre $100: {$igtf_amount}";
```

### Generación de Reportes SENIAT

```php
// Generar libro de ventas
$seniat_exporter = WVP_SENIAT_Exporter::get_instance();
$sales_book = $seniat_exporter->generate_sales_book('2024-01-01', '2024-01-31');

// Generar reporte de IVA
$iva_report = $seniat_exporter->generate_iva_report('2024-01-01', '2024-01-31');
```

### Validación de Datos Venezolanos

```php
// Validar RIF
$validator = WVP_Venezuelan_Validator::get_instance();
$is_valid_rif = $validator->validate_rif('V-12345678');

// Validar número telefónico
$is_valid_phone = $validator->validate_phone_number('0412-1234567');
```

## 🔧 Desarrollo

### Estructura del Plugin

```
woocommerce-venezuela-pro-2025/
├── woocommerce-venezuela-pro-2025.php    # Archivo principal
├── includes/                             # Clases principales
│   ├── class-wvp-simple-currency-converter.php
│   ├── class-wvp-venezuelan-taxes.php
│   ├── class-wvp-venezuelan-shipping.php
│   ├── class-wvp-pago-movil-gateway.php
│   ├── class-wvp-admin-dashboard.php
│   ├── class-wvp-seniat-exporter.php
│   ├── class-wvp-cache-manager.php
│   ├── class-wvp-database-optimizer.php
│   ├── class-wvp-assets-optimizer.php
│   ├── class-wvp-security-manager.php
│   ├── class-wvp-venezuelan-validator.php
│   ├── class-wvp-setup-wizard.php
│   ├── class-wvp-notification-system.php
│   ├── class-wvp-analytics-dashboard.php
│   ├── class-wvp-final-optimizer.php
│   ├── class-wvp-testing-suite.php
│   └── class-wvp-documentation-generator.php
├── admin/                                # Funcionalidad admin
│   ├── css/
│   ├── js/
│   └── partials/
├── public/                               # Funcionalidad frontend
│   ├── css/
│   ├── js/
│   └── partials/
├── languages/                            # Traducciones
└── README.md                            # Este archivo
```

### Hooks Disponibles

#### Filtros (Filters)
```php
// Filtrar tipo de cambio antes de usar
add_filter('wvp_currency_rate', function($rate, $from, $to) {
    // Modificar tipo de cambio
    return $rate;
}, 10, 3);

// Filtrar tasa de impuesto
add_filter('wvp_tax_rate', function($rate, $tax_type) {
    // Modificar tasa de impuesto
    return $rate;
}, 10, 2);

// Filtrar costo de envío
add_filter('wvp_shipping_cost', function($cost, $method, $state) {
    // Modificar costo de envío
    return $cost;
}, 10, 3);
```

#### Acciones (Actions)
```php
// Después de conversión de moneda
add_action('wvp_currency_converted', function($original_price, $converted_price, $from, $to) {
    // Log de conversión
    error_log("Converted {$original_price} {$from} to {$converted_price} {$to}");
}, 10, 4);

// Después de cálculo de impuestos
add_action('wvp_tax_calculated', function($base_price, $tax_amount, $tax_type) {
    // Log de impuestos
    error_log("Calculated {$tax_type} on {$base_price}: {$tax_amount}");
}, 10, 3);

// Después de generar reporte
add_action('wvp_report_generated', function($report_type, $file_path) {
    // Notificar generación de reporte
    error_log("Generated {$report_type} report: {$file_path}");
}, 10, 2);
```

### AJAX Endpoints

```javascript
// Conversión de precio
jQuery.post(ajaxurl, {
    action: 'wvp_convert_price',
    price: 100,
    currency_from: 'USD',
    currency_to: 'VES',
    nonce: wvp_ajax_nonce
}, function(response) {
    console.log('Converted price:', response.data);
});

// Exportar reporte SENIAT
jQuery.post(ajaxurl, {
    action: 'wvp_export_seniat',
    report_type: 'sales_book',
    date_from: '2024-01-01',
    date_to: '2024-01-31',
    nonce: wvp_ajax_nonce
}, function(response) {
    // Descargar archivo
    window.open(response.data.download_url);
});
```

## 🧪 Testing

### Ejecutar Pruebas

```bash
# Pruebas unitarias
wp eval "WVP_Testing_Suite::get_instance()->run_unit_tests();"

# Pruebas de integración
wp eval "WVP_Testing_Suite::get_instance()->run_integration_tests();"

# Pruebas de performance
wp eval "WVP_Testing_Suite::get_instance()->run_performance_tests();"
```

### Pruebas Automatizadas

El plugin incluye un sistema de pruebas automatizadas que se ejecuta diariamente:

- **Pruebas unitarias**: Funcionalidades individuales
- **Pruebas de integración**: Integración con WooCommerce
- **Pruebas de performance**: Tiempos de carga y memoria
- **Pruebas de seguridad**: Validaciones y permisos

## 📊 Analytics

### Métricas Disponibles

- **Ventas diarias/mensuales**
- **Productos más vendidos**
- **Clientes activos**
- **Conversiones de moneda**
- **Impuestos recaudados**
- **Métodos de pago más usados**
- **Estados con más envíos**

### Acceso a Analytics

```php
// Obtener datos de analytics
$analytics = WVP_Analytics_Dashboard::get_instance();
$sales_data = $analytics->get_sales_data('7_days');
$top_products = $analytics->get_top_products('30_days');
$customer_metrics = $analytics->get_customer_metrics('30_days');
```

## 🔒 Seguridad

### Características de Seguridad

- **Validación de entrada** robusta
- **Sanitización de datos** automática
- **Verificación de nonces** en formularios
- **Verificación de permisos** de usuario
- **Escape de output** automático
- **Logging de seguridad** completo

### Configuración de Seguridad

```php
// Habilitar logging de seguridad
update_option('wvp_enable_security_logging', true);

// Configurar intentos fallidos máximos
update_option('wvp_max_failed_attempts', 5);

// Configurar duración de bloqueo
update_option('wvp_lockout_duration', 15 * MINUTE_IN_SECONDS);
```

## 🚀 Optimización

### Cache

```php
// Limpiar cache
$cache_manager = WVP_Cache_Manager::get_instance();
$cache_manager->clear_all();

// Obtener estadísticas de cache
$stats = $cache_manager->get_stats();
```

### Base de Datos

```php
// Optimizar base de datos
$db_optimizer = WVP_Database_Optimizer::get_instance();
$db_optimizer->optimize_tables();

// Limpiar datos antiguos
$db_optimizer->cleanup_old_data();
```

### Assets

```php
// Optimizar assets
$assets_optimizer = WVP_Assets_Optimizer::get_instance();
$assets_optimizer->minify_css();
$assets_optimizer->minify_js();
$assets_optimizer->combine_assets();
```

## 🐛 Solución de Problemas

### Problemas Comunes

#### Conversión de Monedas No Funciona
```bash
# Verificar configuración BCV
wp option get wvp_bcv_rate

# Limpiar cache
wp eval "WVP_Cache_Manager::get_instance()->clear_all();"

# Verificar logs
tail -f wp-content/debug.log
```

#### Impuestos No Se Calculan
```bash
# Verificar configuración de impuestos
wp option get wvp_enable_iva
wp option get wvp_enable_igtf

# Verificar configuración de WooCommerce
wp option get woocommerce_calc_taxes
```

#### Reportes SENIAT No Se Generan
```bash
# Verificar permisos de escritura
ls -la wp-content/uploads/

# Verificar logs de errores
tail -f wp-content/debug.log
```

### Debugging

```php
// Habilitar debug
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Log personalizado
error_log('WVP Debug: ' . print_r($data, true));
```

## 📞 Soporte

### Canales de Soporte

- **GitHub Issues**: [Reportar bugs](https://github.com/woocommerce-venezuela-pro-2025/issues)
- **Email**: soporte@venezuelapro.com
- **Documentación**: [docs.venezuelapro.com](https://docs.venezuelapro.com)
- **Comunidad**: [Discord](https://discord.gg/venezuelapro)

### Información del Sistema

```bash
# Generar reporte de sistema
wp eval "WVP_Final_Optimizer::get_instance()->generate_health_report();"
```

## 📄 Licencia

Este plugin está licenciado bajo la **GPL v2** o posterior.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🤝 Contribuciones

### Cómo Contribuir

1. **Fork** el repositorio
2. **Crear** una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. **Commit** tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. **Push** a la rama (`git push origin feature/nueva-funcionalidad`)
5. **Crear** un Pull Request

### Estándares de Código

- Seguir **PSR-12** para PHP
- Usar **WordPress Coding Standards**
- Documentar todas las funciones
- Incluir pruebas unitarias
- Mantener compatibilidad con versiones anteriores

## 📈 Roadmap

### Versión 2.1.0
- [ ] Integración con más bancos venezolanos
- [ ] Soporte para criptomonedas
- [ ] API REST completa
- [ ] Integración con WhatsApp Business

### Versión 2.2.0
- [ ] Dashboard móvil
- [ ] Notificaciones push
- [ ] Integración con redes sociales
- [ ] Sistema de afiliados

### Versión 3.0.0
- [ ] Arquitectura microservicios
- [ ] Integración con blockchain
- [ ] IA para análisis de ventas
- [ ] Automatización completa

## 🙏 Agradecimientos

- **WooCommerce** por la plataforma base
- **WordPress** por el CMS
- **BCV** por los tipos de cambio
- **Comunidad venezolana** por el feedback
- **Contribuidores** por las mejoras

---

**Desarrollado con ❤️ para el comercio electrónico venezolano**

*WooCommerce Venezuela Pro 2025 - Haciendo el e-commerce venezolano más fácil*
