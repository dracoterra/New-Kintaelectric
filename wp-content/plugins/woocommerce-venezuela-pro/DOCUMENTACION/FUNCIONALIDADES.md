# Funcionalidades Específicas para Venezuela - WooCommerce Venezuela Pro

## Resumen de Funcionalidades

El plugin WooCommerce Venezuela Pro incluye funcionalidades específicamente diseñadas para el mercado venezolano, incluyendo manejo de monedas, métodos de pago locales, impuestos venezolanos, y cumplimiento fiscal.

## 1. Integración con BCV (Banco Central de Venezuela)

### Funcionalidad Principal
- **Clase**: `WVP_BCV_Integrator`
- **Ubicación**: `includes/class-wvp-bcv-integrator.php`
- **Dependencia**: BCV Dólar Tracker

### Características
- Obtención automática de tipos de cambio del BCV
- Conversión USD a VES en tiempo real
- Sistema de caché para optimizar rendimiento
- Fallback cuando BCV no está disponible

### Implementación
```php
class WVP_BCV_Integrator {
    public static function get_rate() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        $latest_price = $wpdb->get_var(
            "SELECT precio FROM $table_name ORDER BY datatime DESC LIMIT 1"
        );
        
        return floatval($latest_price);
    }
    
    public static function convert_usd_to_ves($usd_amount, $rate = null) {
        $rate = $rate ?: self::get_rate();
        return $usd_amount * $rate;
    }
}
```

### Configuraciones
- **Duración de caché**: 1 hora por defecto
- **Tasa de fallback**: Configurable en admin
- **Actualización automática**: Cada 30 minutos

## 2. Gestión de IGTF (Impuesto a las Grandes Transacciones Financieras)

### Funcionalidad Principal
- **Clase**: `WVP_IGTF_Manager`
- **Ubicación**: `includes/class-wvp-igtf-manager.php`

### Características
- Cálculo automático de IGTF en transacciones
- Aplicación en carrito y checkout
- Configuración flexible de tasas
- Exenciones por tipo de cliente

### Implementación
```php
class WVP_IGTF_Manager {
    public function apply_igtf_fee($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        $igtf_rate = $this->get_igtf_rate();
        $subtotal = $cart->get_subtotal();
        
        if ($subtotal > 0) {
            $igtf_amount = $subtotal * ($igtf_rate / 100);
            
            $cart->add_fee(
                __('IGTF', 'wvp'),
                $igtf_amount,
                true
            );
        }
    }
}
```

### Configuraciones
- **Tasa por defecto**: 3.0%
- **Aplicación**: Automática en transacciones > $0
- **Exenciones**: Configurables por tipo de cliente

## 3. Métodos de Pago Locales

### 3.1 Pago Móvil
- **Clase**: `WVP_Gateway_Pago_Movil`
- **Ubicación**: `gateways/class-wvp-gateway-pago-movil.php`

#### Características
- Integración con sistema Pago Móvil venezolano
- Validación de datos locales (CI, teléfono, banco)
- Soporte para múltiples bancos venezolanos
- Verificación de pagos

#### Configuraciones
```php
$this->init_form_fields();
$this->form_fields = array(
    'enabled' => array(
        'title' => __('Habilitar/Deshabilitar', 'wvp'),
        'type' => 'checkbox',
        'label' => __('Habilitar Pago Móvil', 'wvp'),
        'default' => 'yes'
    ),
    'title' => array(
        'title' => __('Título', 'wvp'),
        'type' => 'text',
        'description' => __('Título que verá el cliente', 'wvp'),
        'default' => __('Pago Móvil', 'wvp')
    ),
    'ci' => array(
        'title' => __('Cédula/RIF', 'wvp'),
        'type' => 'text',
        'description' => __('Su cédula o RIF', 'wvp')
    ),
    'phone' => array(
        'title' => __('Teléfono', 'wvp'),
        'type' => 'text',
        'description' => __('Número de teléfono registrado', 'wvp')
    ),
    'bank' => array(
        'title' => __('Banco', 'wvp'),
        'type' => 'select',
        'options' => array(
            'banesco' => 'Banesco',
            'mercantil' => 'Mercantil',
            'bbva' => 'BBVA Provincial',
            'venezuela' => 'Banco de Venezuela'
        )
    )
);
```

### 3.2 Zelle
- **Clase**: `WVP_Gateway_Zelle`
- **Ubicación**: `gateways/class-wvp-gateway-zelle.php`

#### Características
- Transferencias internacionales vía Zelle
- Validación de emails de Zelle
- Verificación manual de pagos
- Soporte para múltiples cuentas

### 3.3 Efectivo
- **Clase**: `WVP_Gateway_Efectivo`
- **Ubicación**: `gateways/class-wvp-gateway-efectivo.php`

#### Características
- Pago en efectivo USD
- Pago en efectivo VES
- Gestión de entregas locales
- Confirmación manual

### 3.4 Cashea
- **Clase**: `WVP_Gateway_Cashea`
- **Ubicación**: `gateways/class-wvp-gateway-cashea.php`

#### Características
- Integración con plataforma Cashea
- Pagos digitales locales
- Procesamiento automático
- Reportes de transacciones

## 4. Métodos de Envío Locales

### Delivery Local por Zonas
- **Clase**: `WVP_Shipping_Local_Delivery`
- **Ubicación**: `shipping/class-wvp-shipping-local-delivery.php`

#### Características
- Envío por zonas en Caracas y Miranda
- Cálculo de costos por distancia
- Configuración flexible de zonas
- Tiempos de entrega estimados

#### Implementación
```php
class WVP_Shipping_Local_Delivery extends WC_Shipping_Method {
    public function calculate_shipping($package = array()) {
        $zone = $this->get_customer_zone($package);
        $cost = $this->get_zone_cost($zone);
        
        $this->add_rate(array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => $cost,
            'meta_data' => array(
                'zone' => $zone,
                'delivery_time' => $this->get_delivery_time($zone)
            )
        ));
    }
}
```

#### Configuraciones de Zonas
```php
$this->zones = array(
    'centro' => array(
        'name' => 'Centro de Caracas',
        'cost' => 5.00,
        'delivery_time' => '1-2 días'
    ),
    'este' => array(
        'name' => 'Este de Caracas',
        'cost' => 7.00,
        'delivery_time' => '2-3 días'
    ),
    'miranda' => array(
        'name' => 'Miranda',
        'cost' => 10.00,
        'delivery_time' => '3-5 días'
    )
);
```

## 5. Reportes Fiscales

### 5.1 Reportes SENIAT
- **Clase**: `WVP_SENIAT_Reports`
- **Ubicación**: `includes/class-wvp-seniat-reports.php`

#### Características
- Generación de reportes para SENIAT
- Cumplimiento fiscal venezolano
- Exportación de datos en formatos requeridos
- Cálculo automático de impuestos

#### Tipos de Reportes
1. **Reporte de IVA**: Ventas gravadas con IVA
2. **Reporte de IGTF**: Transacciones con IGTF
3. **Reporte de Retenciones**: Retenciones aplicadas
4. **Reporte de Exportaciones**: Ventas exentas

### 5.2 Facturación Electrónica
- **Clase**: `WVP_Electronic_Invoice`
- **Ubicación**: `includes/class-wvp-electronic-invoice.php`

#### Características
- Generación de facturas electrónicas
- Integración con sistemas fiscales
- Cumplimiento legal venezolano
- Numeración automática

## 6. Validaciones Específicas de Venezuela

### 6.1 Validación de Cédula/RIF
```php
public function validate_venezuelan_id($id) {
    // Validar formato de cédula (V-12345678)
    if (preg_match('/^V-\d{7,8}$/', $id)) {
        return true;
    }
    
    // Validar formato de RIF (J-12345678-9)
    if (preg_match('/^[JG]-?\d{8}-?\d$/', $id)) {
        return true;
    }
    
    return false;
}
```

### 6.2 Validación de Teléfonos Venezolanos
```php
public function validate_venezuelan_phone($phone) {
    // Formato: +58-XXX-XXXXXXX o 0XXX-XXXXXXX
    $pattern = '/^(\+58-?|0)?(4\d{2}|2\d{2})-?\d{7}$/';
    return preg_match($pattern, $phone);
}
```

### 6.3 Validación de Direcciones Venezolanas
```php
public function validate_venezuelan_address($address) {
    $states = array(
        'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
        'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
        'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
        'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
        'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
        'Vargas', 'Yaracuy', 'Zulia'
    );
    
    foreach ($states as $state) {
        if (stripos($address, $state) !== false) {
            return true;
        }
    }
    
    return false;
}
```

## 7. Formateo de Moneda Venezolana

### Formato de Precios
```php
public function format_venezuelan_price($amount, $currency = 'VES') {
    $formatted = number_format($amount, 2, ',', '.');
    
    switch ($currency) {
        case 'VES':
            return 'Bs. ' . $formatted;
        case 'USD':
            return '$ ' . $formatted;
        default:
            return $formatted . ' ' . $currency;
    }
}
```

### Conversión de Monedas
```php
public function convert_currency($amount, $from, $to) {
    if ($from === $to) {
        return $amount;
    }
    
    if ($from === 'USD' && $to === 'VES') {
        $rate = WVP_BCV_Integrator::get_rate();
        return $amount * $rate;
    }
    
    if ($from === 'VES' && $to === 'USD') {
        $rate = WVP_BCV_Integrator::get_rate();
        return $amount / $rate;
    }
    
    return $amount;
}
```

## 8. Configuraciones Específicas de Venezuela

### Configuraciones por Defecto
```php
private function set_default_options() {
    $defaults = array(
        'wvp_igtf_rate' => 3.0,
        'wvp_bcv_cache_duration' => 3600,
        'wvp_security_log_retention' => 30,
        'wvp_currency_position' => 'after',
        'wvp_decimal_places' => 2,
        'wvp_thousand_separator' => '.',
        'wvp_decimal_separator' => ',',
        'wvp_require_cedula_rif' => true,
        'wvp_cedula_rif_placeholder' => 'V-12345678 o J-12345678-9'
    );
    
    foreach ($defaults as $option => $value) {
        if (!get_option($option)) {
            update_option($option, $value);
        }
    }
}
```

### Configuraciones de Apariencia
```php
$appearance_settings = array(
    'wvp_display_style' => 'minimal',
    'wvp_primary_color' => '#007cba',
    'wvp_secondary_color' => '#005a87',
    'wvp_success_color' => '#28a745',
    'wvp_warning_color' => '#ffc107',
    'wvp_font_family' => 'system',
    'wvp_font_size' => 'medium'
);
```

## 9. Integración con Servicios Locales

### WhatsApp Notifications
- **Clase**: `WVP_WhatsApp_Notifications`
- **Ubicación**: `admin/class-wvp-whatsapp-notifications.php`

#### Características
- Notificaciones automáticas vía WhatsApp
- Confirmación de pedidos
- Actualizaciones de estado
- Soporte al cliente

### Email Modifications
- **Clase**: `WVP_Email_Modifier`
- **Ubicación**: `includes/class-wvp-email-modifier.php`

#### Características
- Personalización de emails para Venezuela
- Información fiscal en emails
- Formato de moneda local
- Datos de contacto locales

## 10. Cumplimiento Legal Venezolano

### Requisitos Fiscales
- Numeración de facturas según normativa
- Información fiscal obligatoria
- Retenciones según tipo de cliente
- Reportes para SENIAT

### Protección de Datos
- Cumplimiento con leyes locales
- Consentimiento explícito
- Derecho al olvido
- Portabilidad de datos

## Conclusión

Las funcionalidades específicas para Venezuela del plugin WooCommerce Venezuela Pro cubren todos los aspectos necesarios para operar un e-commerce en el mercado venezolano:

- ✅ **Moneda**: Integración completa con BCV
- ✅ **Pagos**: Métodos locales y internacionales
- ✅ **Impuestos**: IGTF y otros impuestos locales
- ✅ **Envíos**: Zonas locales de Venezuela
- ✅ **Fiscal**: Reportes y facturación electrónica
- ✅ **Legal**: Cumplimiento con normativas locales
- ✅ **UX**: Experiencia adaptada al usuario venezolano

Estas funcionalidades hacen del plugin una solución completa y específica para el mercado venezolano.
