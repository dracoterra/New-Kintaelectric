# API y Hooks - WooCommerce Venezuela Pro

## Hooks de WordPress

### Actions (Acciones)

#### Hooks de Inicialización
```php
// Plugin cargado
do_action('wvp_loaded');

// Componentes inicializados
do_action('wvp_components_loaded');

// Administración cargada
do_action('wvp_admin_loaded');
```

#### Hooks de WooCommerce
```php
// Aplicar tarifas en carrito
add_action('woocommerce_cart_calculate_fees', array($this, 'apply_igtf_fee'));

// Mostrar información antes del pago
add_action('woocommerce_review_order_before_payment', array($this, 'display_igtf_info'));

// Procesar pago
add_action('woocommerce_checkout_process', array($this, 'validate_checkout_data'));

// Después de crear orden
add_action('woocommerce_checkout_order_processed', array($this, 'handle_order_processed'));
```

#### Hooks de Administración
```php
// Añadir menú de administración
add_action('admin_menu', array($this, 'add_admin_menu'));

// Enqueue scripts de administración
add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

// Guardar configuraciones
add_action('wp_ajax_wvp_save_settings', array($this, 'save_settings'));
```

### Filters (Filtros)

#### Filtros de WooCommerce
```php
// Añadir pasarelas de pago
add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));

// Añadir métodos de envío
add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));

// Modificar formato de precio
add_filter('woocommerce_price_format', array($this, 'modify_price_format'));

// Modificar símbolo de moneda
add_filter('woocommerce_currency_symbol', array($this, 'modify_currency_symbol'), 10, 2);
```

#### Filtros Personalizados
```php
// Modificar tasa BCV
add_filter('wvp_bcv_rate', array($this, 'modify_bcv_rate'));

// Modificar tasa IGTF
add_filter('wvp_igtf_rate', array($this, 'modify_igtf_rate'));

// Modificar cálculo de precio
add_filter('wvp_price_calculation', array($this, 'modify_price_calculation'), 10, 3);

// Modificar formato de precio
add_filter('wvp_price_format', array($this, 'modify_price_format'), 10, 2);
```

## API Pública

### Funciones de Conversión de Moneda

#### Obtener Tasa BCV
```php
/**
 * Obtener la tasa de cambio actual del BCV
 * 
 * @return float|null Tasa de cambio o null si no está disponible
 */
function wvp_get_bcv_rate() {
    return WVP_BCV_Integrator::get_rate();
}
```

#### Convertir USD a VES
```php
/**
 * Convertir cantidad de USD a VES
 * 
 * @param float $usd_amount Cantidad en USD
 * @param float $rate Tasa de cambio (opcional)
 * @return float Cantidad en VES
 */
function wvp_convert_usd_to_ves($usd_amount, $rate = null) {
    return WVP_BCV_Integrator::convert_usd_to_ves($usd_amount, $rate);
}
```

#### Convertir VES a USD
```php
/**
 * Convertir cantidad de VES a USD
 * 
 * @param float $ves_amount Cantidad en VES
 * @param float $rate Tasa de cambio (opcional)
 * @return float Cantidad en USD
 */
function wvp_convert_ves_to_usd($ves_amount, $rate = null) {
    $rate = $rate ?: WVP_BCV_Integrator::get_rate();
    return $ves_amount / $rate;
}
```

### Funciones de IGTF

#### Calcular IGTF
```php
/**
 * Calcular IGTF para una cantidad
 * 
 * @param float $amount Cantidad base
 * @param float $rate Tasa de IGTF (opcional)
 * @return float Cantidad de IGTF
 */
function wvp_calculate_igtf($amount, $rate = null) {
    $rate = $rate ?: get_option('wvp_igtf_rate', 3.0);
    return $amount * ($rate / 100);
}
```

#### Aplicar IGTF a Carrito
```php
/**
 * Aplicar IGTF al carrito actual
 * 
 * @param WC_Cart $cart Carrito de WooCommerce
 * @return bool True si se aplicó correctamente
 */
function wvp_apply_igtf_to_cart($cart) {
    $igtf_manager = new WVP_IGTF_Manager();
    return $igtf_manager->apply_igtf_fee($cart);
}
```

### Funciones de Formateo

#### Formatear Precio Venezolano
```php
/**
 * Formatear precio en formato venezolano
 * 
 * @param float $amount Cantidad a formatear
 * @param string $currency Moneda (VES, USD)
 * @return string Precio formateado
 */
function wvp_format_price($amount, $currency = 'VES') {
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

#### Formatear Referencia de Precio
```php
/**
 * Formatear referencia de precio en VES
 * 
 * @param float $usd_price Precio en USD
 * @param string $format Formato de salida
 * @return string Referencia formateada
 */
function wvp_format_price_reference($usd_price, $format = '(Ref. %s Bs.)') {
    $ves_price = wvp_convert_usd_to_ves($usd_price);
    $formatted_price = wvp_format_price($ves_price, 'VES');
    
    return sprintf($format, $formatted_price);
}
```

### Funciones de Validación

#### Validar Cédula/RIF Venezolano
```php
/**
 * Validar formato de cédula o RIF venezolano
 * 
 * @param string $id Cédula o RIF a validar
 * @return bool True si es válido
 */
function wvp_validate_venezuelan_id($id) {
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

#### Validar Teléfono Venezolano
```php
/**
 * Validar formato de teléfono venezolano
 * 
 * @param string $phone Teléfono a validar
 * @return bool True si es válido
 */
function wvp_validate_venezuelan_phone($phone) {
    // Formato: +58-XXX-XXXXXXX o 0XXX-XXXXXXX
    $pattern = '/^(\+58-?|0)?(4\d{2}|2\d{2})-?\d{7}$/';
    return preg_match($pattern, $phone);
}
```

### Funciones de Configuración

#### Obtener Configuración del Plugin
```php
/**
 * Obtener configuración del plugin
 * 
 * @param string $option_name Nombre de la opción
 * @param mixed $default Valor por defecto
 * @return mixed Valor de la configuración
 */
function wvp_get_option($option_name, $default = null) {
    return get_option('wvp_' . $option_name, $default);
}
```

#### Actualizar Configuración del Plugin
```php
/**
 * Actualizar configuración del plugin
 * 
 * @param string $option_name Nombre de la opción
 * @param mixed $value Valor a guardar
 * @return bool True si se guardó correctamente
 */
function wvp_update_option($option_name, $value) {
    return update_option('wvp_' . $option_name, $value);
}
```

### Funciones de Logging

#### Registrar Log
```php
/**
 * Registrar mensaje en log del plugin
 * 
 * @param string $level Nivel de log (error, warning, info, debug)
 * @param string $message Mensaje a registrar
 * @param array $context Contexto adicional
 */
function wvp_log($level, $message, $context = array()) {
    $error_handler = WVP_Error_Handler::get_instance();
    $error_handler->log_error($level, $message, $context);
}
```

#### Obtener Logs
```php
/**
 * Obtener logs del plugin
 * 
 * @param string $level Nivel de log (opcional)
 * @param int $limit Límite de resultados
 * @return array Array de logs
 */
function wvp_get_logs($level = null, $limit = 100) {
    global $wpdb;
    
    $where = '';
    if ($level) {
        $where = $wpdb->prepare("WHERE level = %s", $level);
    }
    
    return $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}wvp_error_logs
        {$where}
        ORDER BY timestamp DESC
        LIMIT %d
    ", $limit));
}
```

## Hooks Personalizados

### Eventos de Pago

#### Pago Verificado
```php
/**
 * Disparar cuando se verifica un pago
 * 
 * @param int $order_id ID de la orden
 * @param string $gateway_id ID de la pasarela
 * @param array $payment_data Datos del pago
 */
do_action('wvp_payment_verified', $order_id, $gateway_id, $payment_data);
```

#### Pago Fallido
```php
/**
 * Disparar cuando falla un pago
 * 
 * @param int $order_id ID de la orden
 * @param string $gateway_id ID de la pasarela
 * @param string $error_message Mensaje de error
 */
do_action('wvp_payment_failed', $order_id, $gateway_id, $error_message);
```

### Eventos de BCV

#### Tasa BCV Actualizada
```php
/**
 * Disparar cuando se actualiza la tasa BCV
 * 
 * @param float $old_rate Tasa anterior
 * @param float $new_rate Nueva tasa
 * @param string $source Fuente de la actualización
 */
do_action('wvp_bcv_rate_updated', $old_rate, $new_rate, $source);
```

#### Error al Obtener Tasa BCV
```php
/**
 * Disparar cuando hay error al obtener tasa BCV
 * 
 * @param string $error_message Mensaje de error
 * @param array $context Contexto del error
 */
do_action('wvp_bcv_error', $error_message, $context);
```

### Eventos de IGTF

#### IGTF Aplicado
```php
/**
 * Disparar cuando se aplica IGTF
 * 
 * @param float $amount Cantidad base
 * @param float $igtf_amount Cantidad de IGTF
 * @param float $rate Tasa aplicada
 */
do_action('wvp_igtf_applied', $amount, $igtf_amount, $rate);
```

#### IGTF Exento
```php
/**
 * Disparar cuando se exime IGTF
 * 
 * @param float $amount Cantidad exenta
 * @param string $reason Razón de la exención
 */
do_action('wvp_igtf_exempted', $amount, $reason);
```

## Filtros Personalizados

### Filtros de Cálculo

#### Modificar Cálculo de Precio
```php
/**
 * Filtrar cálculo de precio
 * 
 * @param float $price Precio calculado
 * @param float $base_price Precio base
 * @param string $currency Moneda
 * @return float Precio modificado
 */
apply_filters('wvp_price_calculation', $price, $base_price, $currency);
```

#### Modificar Tasa BCV
```php
/**
 * Filtrar tasa BCV
 * 
 * @param float $rate Tasa BCV
 * @return float Tasa modificada
 */
apply_filters('wvp_bcv_rate', $rate);
```

#### Modificar Tasa IGTF
```php
/**
 * Filtrar tasa IGTF
 * 
 * @param float $rate Tasa IGTF
 * @return float Tasa modificada
 */
apply_filters('wvp_igtf_rate', $rate);
```

### Filtros de Formateo

#### Modificar Formato de Precio
```php
/**
 * Filtrar formato de precio
 * 
 * @param string $formatted_price Precio formateado
 * @param float $amount Cantidad
 * @param string $currency Moneda
 * @return string Formato modificado
 */
apply_filters('wvp_price_format', $formatted_price, $amount, $currency);
```

#### Modificar Formato de Referencia
```php
/**
 * Filtrar formato de referencia de precio
 * 
 * @param string $reference Referencia formateada
 * @param float $usd_price Precio en USD
 * @param float $ves_price Precio en VES
 * @return string Referencia modificada
 */
apply_filters('wvp_price_reference_format', $reference, $usd_price, $ves_price);
```

## Ejemplos de Uso

### Ejemplo 1: Hook Personalizado
```php
// Escuchar cuando se verifica un pago
add_action('wvp_payment_verified', function($order_id, $gateway_id, $payment_data) {
    // Enviar notificación por WhatsApp
    if ($gateway_id === 'wvp_pago_movil') {
        wvp_send_whatsapp_notification($order_id, 'Pago verificado');
    }
    
    // Actualizar inventario
    wvp_update_inventory($order_id);
}, 10, 3);
```

### Ejemplo 2: Filtro Personalizado
```php
// Modificar tasa BCV para aplicar margen
add_filter('wvp_bcv_rate', function($rate) {
    $margin = 0.05; // 5% de margen
    return $rate * (1 + $margin);
});
```

### Ejemplo 3: API Pública
```php
// En un tema o plugin externo
function mostrar_precio_venezolano($precio_usd) {
    $precio_ves = wvp_convert_usd_to_ves($precio_usd);
    $precio_formateado = wvp_format_price($precio_ves, 'VES');
    
    echo "Precio: {$precio_formateado}";
}
```

### Ejemplo 4: Validación Personalizada
```php
// Validar datos de checkout
add_action('woocommerce_checkout_process', function() {
    $cedula = $_POST['billing_cedula_rif'] ?? '';
    
    if (!wvp_validate_venezuelan_id($cedula)) {
        wc_add_notice('Cédula o RIF inválido', 'error');
    }
});
```

## Conclusión

La API y sistema de hooks del plugin WooCommerce Venezuela Pro proporciona:

- ✅ **Flexibilidad**: Hooks para personalizar comportamiento
- ✅ **Extensibilidad**: API pública para desarrolladores
- ✅ **Integración**: Fácil integración con otros plugins
- ✅ **Mantenibilidad**: Código modular y bien documentado
- ✅ **Compatibilidad**: Compatible con estándares de WordPress

Esta documentación permite a los desarrolladores aprovechar al máximo las capacidades del plugin y crear extensiones personalizadas.
