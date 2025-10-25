# Guía de Configuración - WooCommerce Venezuela Pro

## Configuración Inicial

### Requisitos Previos
Antes de configurar el plugin, asegúrate de tener:

- ✅ **WordPress** 5.0+ instalado y funcionando
- ✅ **WooCommerce** 5.0+ instalado y configurado
- ✅ **BCV Dólar Tracker** instalado y activo
- ✅ **PHP** 7.4+ con extensiones necesarias
- ✅ **Permisos** de administrador en WordPress

### Instalación del Plugin
1. Subir el plugin a `/wp-content/plugins/woocommerce-venezuela-pro/`
2. Activar el plugin desde el panel de administración
3. Verificar que no hay errores de activación
4. Confirmar que las dependencias están disponibles

## Panel de Administración

### Acceso al Panel
El panel de administración se encuentra en:
**WordPress Admin → WooCommerce → Venezuela Pro**

### Estructura del Panel
El panel está organizado en pestañas:

- **General**: Configuraciones básicas
- **Moneda**: Configuración de conversión BCV
- **IGTF**: Configuración de impuestos
- **Pagos**: Configuración de pasarelas
- **Envíos**: Configuración de métodos de envío
- **Reportes**: Configuración de reportes fiscales
- **Apariencia**: Personalización visual
- **Avanzado**: Configuraciones técnicas

## Configuración General

### Configuraciones Básicas

#### Información de la Empresa
```php
// Configuraciones disponibles
$general_settings = array(
    'company_name' => 'Nombre de la empresa',
    'company_rif' => 'RIF de la empresa',
    'company_address' => 'Dirección de la empresa',
    'company_phone' => 'Teléfono de contacto',
    'company_email' => 'Email de contacto'
);
```

#### Configuraciones de Moneda
```php
$currency_settings = array(
    'base_currency' => 'USD',           // Moneda base
    'display_currency' => 'VES',        // Moneda de visualización
    'currency_position' => 'after',     // Posición del símbolo
    'decimal_places' => 2,              // Decimales
    'thousand_separator' => '.',        // Separador de miles
    'decimal_separator' => ','          // Separador decimal
);
```

### Configuración de Referencias de Precio
```php
$price_reference_settings = array(
    'show_ves_reference' => true,       // Mostrar referencia en VES
    'price_format' => '(Ref. %s Bs.)',  // Formato de referencia
    'reference_position' => 'after'      // Posición de la referencia
);
```

## Configuración de Moneda

### Integración con BCV

#### Configuraciones de BCV
```php
$bcv_settings = array(
    'bcv_cache_duration' => 3600,       // Duración de caché (segundos)
    'bcv_fallback_rate' => 36.50,       // Tasa de respaldo
    'bcv_update_frequency' => 30,       // Frecuencia de actualización (minutos)
    'bcv_auto_update' => true           // Actualización automática
);
```

#### Configuración Manual de Tasa
Si necesitas establecer una tasa manual:

1. Ir a **Venezuela Pro → Moneda**
2. Desactivar **Actualización automática**
3. Establecer **Tasa manual**
4. Guardar configuraciones

### Formateo de Precios

#### Configuraciones de Formato
```php
$format_settings = array(
    'ves_symbol' => 'Bs.',              // Símbolo de VES
    'usd_symbol' => '$',                // Símbolo de USD
    'ves_position' => 'before',          // Posición símbolo VES
    'usd_position' => 'before',         // Posición símbolo USD
    'show_decimals' => true,            // Mostrar decimales
    'round_prices' => true              // Redondear precios
);
```

## Configuración de IGTF

### Configuraciones Básicas de IGTF

#### Habilitar/Deshabilitar IGTF
```php
$igtf_settings = array(
    'igtf_enabled' => true,              // Habilitar IGTF
    'igtf_rate' => 3.0,                 // Tasa de IGTF (%)
    'igtf_description' => 'Impuesto al Gran Movimiento de Transacciones Financieras',
    'igtf_minimum_amount' => 0,         // Monto mínimo para aplicar
    'igtf_maximum_amount' => 0          // Monto máximo (0 = sin límite)
);
```

#### Configuraciones de Exención
```php
$exemption_settings = array(
    'exempt_user_roles' => array(),      // Roles exentos
    'exempt_product_categories' => array(), // Categorías exentas
    'exempt_payment_methods' => array(), // Métodos de pago exentos
    'exempt_shipping_methods' => array() // Métodos de envío exentos
);
```

### Configuración de Aplicación

#### Cuándo Aplicar IGTF
- **En carrito**: Aplicar automáticamente en el carrito
- **En checkout**: Mostrar información antes del pago
- **En órdenes**: Incluir en el total de la orden
- **En facturas**: Mostrar en facturas generadas

## Configuración de Pagos

### Pasarelas de Pago Disponibles

#### Pago Móvil
```php
$pago_movil_settings = array(
    'enabled' => 'yes',
    'title' => 'Pago Móvil',
    'description' => 'Pago mediante Pago Móvil para clientes venezolanos',
    'ci' => 'V-12345678',               // Cédula/RIF del vendedor
    'phone' => '+58-412-1234567',       // Teléfono registrado
    'bank' => 'banesco',                // Banco
    'apply_igtf' => 'yes',              // Aplicar IGTF
    'min_amount' => 0,                  // Monto mínimo
    'max_amount' => 0                   // Monto máximo
);
```

#### Zelle
```php
$zelle_settings = array(
    'enabled' => 'yes',
    'title' => 'Zelle',
    'description' => 'Transferencia internacional vía Zelle',
    'zelle_email' => 'email@example.com', // Email de Zelle
    'instructions' => 'Instrucciones para el cliente',
    'apply_igtf' => 'yes',
    'min_amount' => 0,
    'max_amount' => 0
);
```

#### Efectivo
```php
$efectivo_settings = array(
    'enabled' => 'yes',
    'title' => 'Efectivo',
    'description' => 'Pago en efectivo al recibir',
    'currency' => 'USD',                 // Moneda de pago
    'instructions' => 'Instrucciones para entrega',
    'apply_igtf' => 'no',               // No aplicar IGTF
    'min_amount' => 0,
    'max_amount' => 0
);
```

### Configuración de Verificación

#### Verificación Manual
- **Habilitar**: Requerir verificación manual de pagos
- **Notificaciones**: Enviar notificaciones por email/WhatsApp
- **Tiempo límite**: Tiempo para verificar pagos
- **Auto-aprobación**: Aprobar automáticamente después del tiempo límite

## Configuración de Envíos

### Delivery Local

#### Configuración de Zonas
```php
$shipping_zones = array(
    'centro' => array(
        'name' => 'Centro de Caracas',
        'cost' => 5.00,
        'delivery_time' => '1-2 días',
        'free_shipping_threshold' => 100
    ),
    'este' => array(
        'name' => 'Este de Caracas',
        'cost' => 7.00,
        'delivery_time' => '2-3 días',
        'free_shipping_threshold' => 150
    ),
    'miranda' => array(
        'name' => 'Miranda',
        'cost' => 10.00,
        'delivery_time' => '3-5 días',
        'free_shipping_threshold' => 200
    )
);
```

#### Configuración de Costos
- **Costo base**: Costo base por zona
- **Costo por peso**: Costo adicional por peso
- **Costo por distancia**: Costo adicional por distancia
- **Envío gratis**: Umbral para envío gratuito

### Configuración de Tiempos de Entrega
- **Días hábiles**: Solo días hábiles para entrega
- **Horarios**: Horarios de entrega disponibles
- **Excepciones**: Días no disponibles para entrega

## Configuración de Reportes

### Reportes Fiscales

#### Configuración de SENIAT
```php
$seniat_settings = array(
    'company_rif' => 'J-12345678-9',    // RIF de la empresa
    'company_name' => 'Nombre Empresa', // Nombre de la empresa
    'fiscal_address' => 'Dirección fiscal',
    'phone' => '+58-212-1234567',       // Teléfono
    'email' => 'fiscal@empresa.com',    // Email fiscal
    'iva_rate' => 16.0,                 // Tasa de IVA
    'retention_rate' => 2.0             // Tasa de retención
);
```

#### Configuración de Facturación Electrónica
```php
$electronic_invoice_settings = array(
    'enabled' => true,                  // Habilitar facturación electrónica
    'api_endpoint' => 'https://api.seniat.gov.ve', // Endpoint de SENIAT
    'api_key' => 'your-api-key',       // Clave de API
    'certificate_path' => '/path/to/cert.pem', // Certificado
    'private_key_path' => '/path/to/key.pem'   // Clave privada
);
```

### Configuración de Exportación
- **Formato**: Excel, CSV, PDF
- **Frecuencia**: Diaria, semanal, mensual
- **Destino**: Email, FTP, descarga manual
- **Filtros**: Por fecha, estado, método de pago

## Configuración de Apariencia

### Personalización Visual

#### Configuraciones de Color
```php
$appearance_settings = array(
    'primary_color' => '#007cba',       // Color primario
    'secondary_color' => '#005a87',     // Color secundario
    'success_color' => '#28a745',       // Color de éxito
    'warning_color' => '#ffc107',       // Color de advertencia
    'error_color' => '#dc3545'         // Color de error
);
```

#### Configuraciones de Tipografía
```php
$typography_settings = array(
    'font_family' => 'system',          // Familia de fuente
    'font_size' => 'medium',            // Tamaño de fuente
    'font_weight' => '400',             // Peso de fuente
    'text_transform' => 'none'          // Transformación de texto
);
```

#### Configuraciones de Espaciado
```php
$spacing_settings = array(
    'padding' => 'medium',              // Relleno
    'margin' => 'medium',               // Margen
    'border_radius' => 'medium',        // Radio de borde
    'shadow' => 'small'                 // Sombra
);
```

### Configuración de Estilos
- **Estilo de visualización**: Minimal, estándar, personalizado
- **Tema**: Claro, oscuro, automático
- **Responsive**: Adaptación a dispositivos móviles
- **Animaciones**: Efectos de transición

## Configuración Avanzada

### Configuraciones Técnicas

#### Configuraciones de Rendimiento
```php
$performance_settings = array(
    'cache_enabled' => true,            // Habilitar caché
    'cache_duration' => 3600,           // Duración de caché
    'minify_css' => true,               // Minificar CSS
    'minify_js' => true,                // Minificar JavaScript
    'lazy_loading' => true              // Carga diferida
);
```

#### Configuraciones de Seguridad
```php
$security_settings = array(
    'rate_limiting' => true,             // Limitación de velocidad
    'max_attempts' => 5,                 // Máximo intentos
    'lockout_duration' => 300,          // Duración de bloqueo
    'log_security_events' => true,       // Registrar eventos de seguridad
    'sanitize_inputs' => true            // Sanitizar entradas
);
```

#### Configuraciones de Logging
```php
$logging_settings = array(
    'log_level' => 'info',               // Nivel de log
    'log_retention_days' => 30,         // Días de retención
    'log_errors' => true,                // Registrar errores
    'log_performance' => true,          // Registrar rendimiento
    'log_security' => true              // Registrar seguridad
);
```

### Configuraciones de Debug

#### Modo Debug
```php
$debug_settings = array(
    'debug_mode' => false,              // Modo debug
    'debug_log' => true,                // Log de debug
    'debug_display' => false,           // Mostrar debug
    'debug_queries' => false,           // Debug de consultas
    'debug_cache' => false              // Debug de caché
);
```

## Configuración por Archivo

### Archivo de Configuración Personalizado
Crear archivo `wvp-config.php` en el directorio del plugin:

```php
<?php
// Configuración personalizada de WooCommerce Venezuela Pro

// Configuraciones de BCV
define('WVP_BCV_CACHE_DURATION', 1800); // 30 minutos
define('WVP_BCV_FALLBACK_RATE', 36.50);

// Configuraciones de IGTF
define('WVP_IGTF_RATE', 3.0);
define('WVP_IGTF_ENABLED', true);

// Configuraciones de rendimiento
define('WVP_CACHE_ENABLED', true);
define('WVP_MINIFY_ASSETS', true);

// Configuraciones de seguridad
define('WVP_RATE_LIMITING', true);
define('WVP_MAX_ATTEMPTS', 5);
?>
```

## Validación de Configuración

### Verificación de Configuraciones
```php
// Verificar configuraciones críticas
function wvp_validate_configuration() {
    $errors = array();
    
    // Verificar BCV
    if (!get_option('wvp_bcv_enabled')) {
        $errors[] = 'BCV no está habilitado';
    }
    
    // Verificar IGTF
    $igtf_rate = get_option('wvp_igtf_rate');
    if ($igtf_rate < 0 || $igtf_rate > 100) {
        $errors[] = 'Tasa de IGTF inválida';
    }
    
    // Verificar pasarelas
    $gateways = array('wvp_pago_movil', 'wvp_zelle', 'wvp_efectivo');
    $active_gateways = 0;
    
    foreach ($gateways as $gateway) {
        $settings = get_option("woocommerce_{$gateway}_settings");
        if ($settings && $settings['enabled'] === 'yes') {
            $active_gateways++;
        }
    }
    
    if ($active_gateways === 0) {
        $errors[] = 'No hay pasarelas de pago activas';
    }
    
    return $errors;
}
```

## Respaldo de Configuración

### Exportar Configuración
```php
// Exportar todas las configuraciones
function wvp_export_configuration() {
    $config = array(
        'version' => WVP_VERSION,
        'general' => get_option('wvp_general_settings'),
        'currency' => get_option('wvp_currency_settings'),
        'igtf' => get_option('wvp_igtf_settings'),
        'gateways' => array(),
        'shipping' => array(),
        'reports' => get_option('wvp_reports_settings'),
        'appearance' => get_option('wvp_appearance_settings')
    );
    
    // Exportar configuraciones de pasarelas
    $gateways = array('wvp_pago_movil', 'wvp_zelle', 'wvp_efectivo', 'wvp_cashea');
    foreach ($gateways as $gateway) {
        $config['gateways'][$gateway] = get_option("woocommerce_{$gateway}_settings");
    }
    
    return $config;
}
```

### Importar Configuración
```php
// Importar configuración desde archivo
function wvp_import_configuration($config_file) {
    $config = json_decode(file_get_contents($config_file), true);
    
    if (!$config) {
        return false;
    }
    
    // Importar configuraciones
    update_option('wvp_general_settings', $config['general']);
    update_option('wvp_currency_settings', $config['currency']);
    update_option('wvp_igtf_settings', $config['igtf']);
    update_option('wvp_reports_settings', $config['reports']);
    update_option('wvp_appearance_settings', $config['appearance']);
    
    // Importar configuraciones de pasarelas
    foreach ($config['gateways'] as $gateway => $settings) {
        update_option("woocommerce_{$gateway}_settings", $settings);
    }
    
    return true;
}
```

## Conclusión

La configuración del plugin WooCommerce Venezuela Pro es flexible y permite:

- ✅ **Personalización completa**: Todas las funcionalidades son configurables
- ✅ **Configuración por archivo**: Para entornos de producción
- ✅ **Validación automática**: Verificación de configuraciones
- ✅ **Respaldo y restauración**: Protección de configuraciones
- ✅ **Interfaz intuitiva**: Panel de administración fácil de usar

Siguiendo esta guía, podrás configurar el plugin según las necesidades específicas de tu tienda venezolana.
