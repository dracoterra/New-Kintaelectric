# WooCommerce Venezuela Suite - API Documentation

## 📋 Información General

Esta documentación describe la API completa del plugin WooCommerce Venezuela Suite, incluyendo funciones, hooks, filtros y clases disponibles para desarrolladores.

## 🔧 Core API

### Funciones Principales

#### Gestión de Opciones
```php
/**
 * Obtener opción del plugin
 * 
 * @param string $option_name Nombre de la opción
 * @param mixed $default Valor por defecto
 * @return mixed Valor de la opción
 */
function wvs_get_option($option_name, $default = null);

/**
 * Establecer opción del plugin
 * 
 * @param string $option_name Nombre de la opción
 * @param mixed $value Valor a establecer
 * @return bool True si se estableció correctamente
 */
function wvs_set_option($option_name, $value);

/**
 * Eliminar opción del plugin
 * 
 * @param string $option_name Nombre de la opción
 * @return bool True si se eliminó correctamente
 */
function wvs_delete_option($option_name);
```

#### Gestión de Módulos
```php
/**
 * Verificar si un módulo está activo
 * 
 * @param string $module_name Nombre del módulo
 * @return bool True si está activo
 */
function wvs_is_module_active($module_name);

/**
 * Activar módulo
 * 
 * @param string $module_name Nombre del módulo
 * @return bool True si se activó correctamente
 */
function wvs_set_module_active($module_name, $active = true);

/**
 * Obtener todos los módulos
 * 
 * @return array Lista de módulos
 */
function wvs_get_all_modules();

/**
 * Obtener módulos activos
 * 
 * @return array Lista de módulos activos
 */
function wvs_get_active_modules();
```

#### Información del Plugin
```php
/**
 * Obtener versión del plugin
 * 
 * @return string Versión del plugin
 */
function wvs_get_version();

/**
 * Obtener información del plugin
 * 
 * @return array Información del plugin
 */
function wvs_get_plugin_info();

/**
 * Verificar compatibilidad
 * 
 * @param string $component Componente a verificar
 * @return bool True si es compatible
 */
function wvs_is_compatible($component);
```

### Clases Principales

#### WVS_Core
```php
class WVS_Core {
    
    /**
     * Obtener instancia única
     * 
     * @return WVS_Core
     */
    public static function get_instance();
    
    /**
     * Inicializar el plugin
     */
    public function init();
    
    /**
     * Obtener gestor de módulos
     * 
     * @return WVS_Module_Manager
     */
    public function get_module_manager();
    
    /**
     * Obtener gestor de base de datos
     * 
     * @return WVS_Database
     */
    public function get_database();
}
```

#### WVS_Module_Manager
```php
class WVS_Module_Manager {
    
    /**
     * Registrar módulo
     * 
     * @param string $module_name Nombre del módulo
     * @param string $class_name Nombre de la clase
     * @param array $dependencies Dependencias
     */
    public function register_module($module_name, $class_name, $dependencies = array());
    
    /**
     * Cargar módulo
     * 
     * @param string $module_name Nombre del módulo
     * @return bool True si se cargó correctamente
     */
    public function load_module($module_name);
    
    /**
     * Desactivar módulo
     * 
     * @param string $module_name Nombre del módulo
     * @return bool True si se desactivó correctamente
     */
    public function deactivate_module($module_name);
    
    /**
     * Verificar dependencias
     * 
     * @param string $module_name Nombre del módulo
     * @return array Estado de dependencias
     */
    public function check_dependencies($module_name);
}
```

## 💰 Currency API

### Funciones de Conversión

#### Conversión de Moneda
```php
/**
 * Convertir precio entre monedas
 * 
 * @param float $amount Cantidad a convertir
 * @param string $from_currency Moneda origen
 * @param string $to_currency Moneda destino
 * @return float Cantidad convertida
 */
function wvs_convert_price($amount, $from_currency, $to_currency);

/**
 * Convertir USD a VES
 * 
 * @param float $usd_amount Cantidad en USD
 * @return float Cantidad en VES
 */
function wvs_convert_usd_to_ves($usd_amount);

/**
 * Convertir VES a USD
 * 
 * @param float $ves_amount Cantidad en VES
 * @return float Cantidad en USD
 */
function wvs_convert_ves_to_usd($ves_amount);
```

#### Gestión de Tasas
```php
/**
 * Obtener tasa actual del BCV
 * 
 * @return float|false Tasa actual o false si no está disponible
 */
function wvs_get_bcv_rate();

/**
 * Obtener tasa de IGTF
 * 
 * @return float Tasa de IGTF
 */
function wvs_get_igtf_rate();

/**
 * Calcular IGTF
 * 
 * @param float $amount Cantidad base
 * @return float Cantidad de IGTF
 */
function wvs_calculate_igtf($amount);
```

### Clases de Moneda

#### WVS_Currency_Manager
```php
class WVS_Currency_Manager {
    
    /**
     * Convertir precio
     * 
     * @param float $amount Cantidad
     * @param string $from_currency Moneda origen
     * @param string $to_currency Moneda destino
     * @return float Cantidad convertida
     */
    public function convert_price($amount, $from_currency, $to_currency);
    
    /**
     * Obtener tasa de cambio
     * 
     * @param string $from_currency Moneda origen
     * @param string $to_currency Moneda destino
     * @return float Tasa de cambio
     */
    public function get_exchange_rate($from_currency, $to_currency);
    
    /**
     * Formatear precio
     * 
     * @param float $amount Cantidad
     * @param string $currency Moneda
     * @return string Precio formateado
     */
    public function format_price($amount, $currency);
}
```

#### WVS_Price_Calculator
```php
class WVS_Price_Calculator {
    
    /**
     * Calcular precio con IGTF
     * 
     * @param float $base_price Precio base
     * @param float $igtf_rate Tasa de IGTF
     * @return array Precio con desglose
     */
    public function calculate_price_with_igtf($base_price, $igtf_rate = null);
    
    /**
     * Calcular precio total
     * 
     * @param float $base_price Precio base
     * @param float $tax_rate Tasa de impuesto
     * @param float $igtf_rate Tasa de IGTF
     * @return array Precio total con desglose
     */
    public function calculate_total_price($base_price, $tax_rate = 0, $igtf_rate = null);
    
    /**
     * Aplicar descuento
     * 
     * @param float $price Precio original
     * @param float $discount_percentage Porcentaje de descuento
     * @return float Precio con descuento
     */
    public function apply_discount($price, $discount_percentage);
}
```

## 💳 Payments API

### Funciones de Pago

#### Gestión de Pagos
```php
/**
 * Procesar pago
 * 
 * @param int $order_id ID del pedido
 * @param string $gateway Gateway de pago
 * @param array $payment_data Datos del pago
 * @return array Resultado del procesamiento
 */
function wvs_process_payment($order_id, $gateway, $payment_data);

/**
 * Verificar pago
 * 
 * @param int $order_id ID del pedido
 * @param array $verification_data Datos de verificación
 * @return bool True si el pago es válido
 */
function wvs_verify_payment($order_id, $verification_data);

/**
 * Obtener métodos de pago disponibles
 * 
 * @return array Métodos de pago
 */
function wvs_get_available_payment_methods();
```

#### Configuración de Pagos
```php
/**
 * Configurar método de pago
 * 
 * @param string $method_name Nombre del método
 * @param array $config Configuración
 * @return bool True si se configuró correctamente
 */
function wvs_configure_payment_method($method_name, $config);

/**
 * Obtener configuración de método de pago
 * 
 * @param string $method_name Nombre del método
 * @return array Configuración del método
 */
function wvs_get_payment_method_config($method_name);
```

### Clases de Pago

#### WVS_Payment_Manager
```php
class WVS_Payment_Manager {
    
    /**
     * Registrar gateway
     * 
     * @param string $gateway_id ID del gateway
     * @param string $class_name Nombre de la clase
     */
    public function register_gateway($gateway_id, $class_name);
    
    /**
     * Obtener gateway
     * 
     * @param string $gateway_id ID del gateway
     * @return WVS_Payment_Gateway_Base
     */
    public function get_gateway($gateway_id);
    
    /**
     * Procesar pago
     * 
     * @param int $order_id ID del pedido
     * @param string $gateway_id ID del gateway
     * @param array $payment_data Datos del pago
     * @return array Resultado
     */
    public function process_payment($order_id, $gateway_id, $payment_data);
}
```

#### WVS_Payment_Gateway_Base
```php
abstract class WVS_Payment_Gateway_Base {
    
    /**
     * ID del gateway
     * 
     * @var string
     */
    public $id;
    
    /**
     * Nombre del gateway
     * 
     * @var string
     */
    public $title;
    
    /**
     * Descripción del gateway
     * 
     * @var string
     */
    public $description;
    
    /**
     * Procesar pago
     * 
     * @param int $order_id ID del pedido
     * @return array Resultado
     */
    abstract public function process_payment($order_id);
    
    /**
     * Verificar pago
     * 
     * @param int $order_id ID del pedido
     * @param array $data Datos de verificación
     * @return bool True si es válido
     */
    abstract public function verify_payment($order_id, $data);
}
```

## 🚚 Shipping API

### Funciones de Envío

#### Gestión de Envíos
```php
/**
 * Calcular costo de envío
 * 
 * @param array $package Paquete a enviar
 * @param string $destination Destino
 * @return float Costo de envío
 */
function wvs_calculate_shipping_cost($package, $destination);

/**
 * Obtener métodos de envío disponibles
 * 
 * @param string $destination Destino
 * @return array Métodos de envío
 */
function wvs_get_available_shipping_methods($destination);

/**
 * Obtener tiempo de entrega estimado
 * 
 * @param string $method Método de envío
 * @param string $destination Destino
 * @return int Días estimados
 */
function wvs_get_estimated_delivery_time($method, $destination);
```

#### Tracking de Envíos
```php
/**
 * Actualizar tracking
 * 
 * @param int $order_id ID del pedido
 * @param string $tracking_number Número de tracking
 * @param string $status Estado del envío
 * @return bool True si se actualizó correctamente
 */
function wvs_update_shipping_tracking($order_id, $tracking_number, $status);

/**
 * Obtener información de tracking
 * 
 * @param int $order_id ID del pedido
 * @return array Información de tracking
 */
function wvs_get_shipping_tracking($order_id);
```

### Clases de Envío

#### WVS_Shipping_Manager
```php
class WVS_Shipping_Manager {
    
    /**
     * Registrar método de envío
     * 
     * @param string $method_id ID del método
     * @param string $class_name Nombre de la clase
     */
    public function register_method($method_id, $class_name);
    
    /**
     * Calcular costo
     * 
     * @param array $package Paquete
     * @param string $destination Destino
     * @return array Costos por método
     */
    public function calculate_costs($package, $destination);
    
    /**
     * Obtener métodos disponibles
     * 
     * @param string $destination Destino
     * @return array Métodos disponibles
     */
    public function get_available_methods($destination);
}
```

## 📄 Invoicing API

### Funciones de Facturación

#### Gestión de Facturas
```php
/**
 * Generar factura
 * 
 * @param int $order_id ID del pedido
 * @param string $type Tipo de factura
 * @return array Datos de la factura
 */
function wvs_generate_invoice($order_id, $type = 'hybrid');

/**
 * Obtener factura
 * 
 * @param int $invoice_id ID de la factura
 * @return array Datos de la factura
 */
function wvs_get_invoice($invoice_id);

/**
 * Enviar factura a SENIAT
 * 
 * @param int $invoice_id ID de la factura
 * @return array Resultado del envío
 */
function wvs_send_invoice_to_seniat($invoice_id);
```

#### Reportes Fiscales
```php
/**
 * Generar reporte fiscal
 * 
 * @param string $period Período del reporte
 * @param string $type Tipo de reporte
 * @return array Datos del reporte
 */
function wvs_generate_fiscal_report($period, $type);

/**
 * Exportar reporte
 * 
 * @param int $report_id ID del reporte
 * @param string $format Formato de exportación
 * @return string Ruta del archivo exportado
 */
function wvs_export_report($report_id, $format = 'pdf');
```

### Clases de Facturación

#### WVS_Invoice_Manager
```php
class WVS_Invoice_Manager {
    
    /**
     * Crear factura
     * 
     * @param int $order_id ID del pedido
     * @param string $type Tipo de factura
     * @return WVS_Invoice
     */
    public function create_invoice($order_id, $type);
    
    /**
     * Generar número de factura
     * 
     * @return string Número de factura
     */
    public function generate_invoice_number();
    
    /**
     * Validar factura
     * 
     * @param WVS_Invoice $invoice Factura
     * @return bool True si es válida
     */
    public function validate_invoice($invoice);
}
```

## 📱 Communication API

### Funciones de Comunicación

#### Notificaciones
```php
/**
 * Enviar notificación
 * 
 * @param string $type Tipo de notificación
 * @param string $recipient Destinatario
 * @param array $data Datos de la notificación
 * @return bool True si se envió correctamente
 */
function wvs_send_notification($type, $recipient, $data);

/**
 * Enviar notificación WhatsApp
 * 
 * @param string $phone Número de teléfono
 * @param string $message Mensaje
 * @return bool True si se envió correctamente
 */
function wvs_send_whatsapp_notification($phone, $message);

/**
 * Enviar email
 * 
 * @param string $to Destinatario
 * @param string $subject Asunto
 * @param string $message Mensaje
 * @param array $headers Headers adicionales
 * @return bool True si se envió correctamente
 */
function wvs_send_email($to, $subject, $message, $headers = array());
```

#### Templates
```php
/**
 * Obtener template
 * 
 * @param string $template_name Nombre del template
 * @param array $variables Variables del template
 * @return string Template procesado
 */
function wvs_get_template($template_name, $variables = array());

/**
 * Registrar template
 * 
 * @param string $template_name Nombre del template
 * @param string $template_content Contenido del template
 * @return bool True si se registró correctamente
 */
function wvs_register_template($template_name, $template_content);
```

### Clases de Comunicación

#### WVS_Notification_Manager
```php
class WVS_Notification_Manager {
    
    /**
     * Registrar canal de notificación
     * 
     * @param string $channel_id ID del canal
     * @param string $class_name Nombre de la clase
     */
    public function register_channel($channel_id, $class_name);
    
    /**
     * Enviar notificación
     * 
     * @param string $type Tipo de notificación
     * @param string $recipient Destinatario
     * @param array $data Datos
     * @return bool True si se envió correctamente
     */
    public function send_notification($type, $recipient, $data);
    
    /**
     * Programar notificación
     * 
     * @param string $type Tipo de notificación
     * @param string $recipient Destinatario
     * @param array $data Datos
     * @param int $timestamp Timestamp de envío
     * @return bool True si se programó correctamente
     */
    public function schedule_notification($type, $recipient, $data, $timestamp);
}
```

## 📊 Reports API

### Funciones de Reportes

#### Generación de Reportes
```php
/**
 * Generar reporte de ventas
 * 
 * @param string $period Período del reporte
 * @param array $filters Filtros adicionales
 * @return array Datos del reporte
 */
function wvs_generate_sales_report($period, $filters = array());

/**
 * Generar reporte fiscal
 * 
 * @param string $period Período del reporte
 * @return array Datos del reporte fiscal
 */
function wvs_generate_tax_report($period);

/**
 * Obtener métricas de analytics
 * 
 * @param string $metric Nombre de la métrica
 * @param array $params Parámetros adicionales
 * @return mixed Valor de la métrica
 */
function wvs_get_analytics_metric($metric, $params = array());
```

#### Exportación
```php
/**
 * Exportar reporte
 * 
 * @param int $report_id ID del reporte
 * @param string $format Formato de exportación
 * @return string Ruta del archivo exportado
 */
function wvs_export_report($report_id, $format = 'pdf');

/**
 * Obtener formatos de exportación disponibles
 * 
 * @return array Formatos disponibles
 */
function wvs_get_export_formats();
```

### Clases de Reportes

#### WVS_Reports_Manager
```php
class WVS_Reports_Manager {
    
    /**
     * Registrar tipo de reporte
     * 
     * @param string $report_type Tipo de reporte
     * @param string $class_name Nombre de la clase
     */
    public function register_report_type($report_type, $class_name);
    
    /**
     * Generar reporte
     * 
     * @param string $report_type Tipo de reporte
     * @param array $params Parámetros
     * @return WVS_Report
     */
    public function generate_report($report_type, $params);
    
    /**
     * Obtener reportes disponibles
     * 
     * @return array Tipos de reportes
     */
    public function get_available_reports();
}
```

## 🎨 Widgets API

### Funciones de Widgets

#### Gestión de Widgets
```php
/**
 * Registrar widget
 * 
 * @param string $widget_id ID del widget
 * @param string $class_name Nombre de la clase
 */
function wvs_register_widget($widget_id, $class_name);

/**
 * Renderizar widget
 * 
 * @param string $widget_id ID del widget
 * @param array $args Argumentos del widget
 * @return string HTML del widget
 */
function wvs_render_widget($widget_id, $args = array());

/**
 * Obtener widgets disponibles
 * 
 * @return array Widgets disponibles
 */
function wvs_get_available_widgets();
```

#### Shortcodes
```php
/**
 * Registrar shortcode
 * 
 * @param string $shortcode_name Nombre del shortcode
 * @param callable $callback Función callback
 */
function wvs_register_shortcode($shortcode_name, $callback);

/**
 * Procesar shortcode
 * 
 * @param array $atts Atributos del shortcode
 * @param string $content Contenido del shortcode
 * @param string $shortcode_name Nombre del shortcode
 * @return string Contenido procesado
 */
function wvs_process_shortcode($atts, $content, $shortcode_name);
```

### Clases de Widgets

#### WVS_Widget_Manager
```php
class WVS_Widget_Manager {
    
    /**
     * Registrar widget
     * 
     * @param string $widget_id ID del widget
     * @param string $class_name Nombre de la clase
     */
    public function register_widget($widget_id, $class_name);
    
    /**
     * Obtener widget
     * 
     * @param string $widget_id ID del widget
     * @return WVS_Widget_Base
     */
    public function get_widget($widget_id);
    
    /**
     * Renderizar widget
     * 
     * @param string $widget_id ID del widget
     * @param array $args Argumentos
     * @return string HTML del widget
     */
    public function render_widget($widget_id, $args);
}
```

## 🔌 Hooks y Filtros

### Hooks de Inicialización
```php
// Inicialización del plugin
do_action('wvs_plugin_initialized');

// Inicialización de módulo
do_action('wvs_module_initialized', $module_name);

// Activación de módulo
do_action('wvs_module_activated', $module_name);

// Desactivación de módulo
do_action('wvs_module_deactivated', $module_name);
```

### Filtros de Moneda
```php
// Conversión de precio
$converted_price = apply_filters('wvs_convert_price', $original_price, $from_currency, $to_currency);

// Actualización de tasa BCV
do_action('wvs_bcv_rate_updated', $new_rate, $old_rate);

// Cálculo de IGTF
$igtf_amount = apply_filters('wvs_calculate_igtf', $amount, $rate);

// Formateo de precio
$formatted_price = apply_filters('wvs_format_price', $price, $currency);
```

### Hooks de Pagos
```php
// Procesamiento de pago
$result = apply_filters('wvs_process_payment', $order_id, $gateway, $payment_data);

// Verificación de pago
do_action('wvs_payment_verified', $order_id, $payment_data);

// Notificación de pago
do_action('wvs_payment_notification', $order_id, $status, $gateway);

// Configuración de gateway
$gateway_config = apply_filters('wvs_gateway_config', $config, $gateway_id);
```

### Hooks de Envíos
```php
// Cálculo de costo de envío
$shipping_cost = apply_filters('wvs_calculate_shipping', $package, $method, $destination);

// Actualización de tracking
do_action('wvs_shipping_tracking_updated', $order_id, $tracking_data);

// Confirmación de entrega
do_action('wvs_shipping_delivered', $order_id, $delivery_data);

// Métodos de envío disponibles
$available_methods = apply_filters('wvs_available_shipping_methods', $methods, $destination);
```

### Hooks de Facturación
```php
// Generación de factura
$invoice = apply_filters('wvs_generate_invoice', $order_id, $type);

// Envío a SENIAT
do_action('wvs_invoice_sent_to_seniat', $invoice_id, $response);

// Backup de factura
do_action('wvs_invoice_backed_up', $invoice_id, $backup_path);

// Validación de factura
$is_valid = apply_filters('wvs_validate_invoice', $invoice);
```

### Hooks de Comunicación
```php
// Envío de notificación
do_action('wvs_send_notification', $type, $recipient, $data);

// Actualización de template
do_action('wvs_template_updated', $template_id, $template_data);

// Registro de comunicación
do_action('wvs_communication_logged', $type, $recipient, $content);

// Configuración de canal
$channel_config = apply_filters('wvs_channel_config', $config, $channel_id);
```

### Hooks de Reportes
```php
// Generación de reporte
$report = apply_filters('wvs_generate_report', $type, $params);

// Actualización de métricas
do_action('wvs_metrics_updated', $metric_name, $value);

// Exportación de datos
do_action('wvs_data_exported', $format, $data);

// Configuración de reporte
$report_config = apply_filters('wvs_report_config', $config, $report_type);
```

### Hooks de Widgets
```php
// Renderizado de widget
$output = apply_filters('wvs_widget_output', $widget_type, $args);

// Actualización de estilos
do_action('wvs_widget_styles_updated', $style_name, $style_data);

// Registro de shortcode
do_action('wvs_shortcode_registered', $shortcode_name, $callback);

// Configuración de widget
$widget_config = apply_filters('wvs_widget_config', $config, $widget_id);
```

## 🛠️ Utilidades

### Funciones de Utilidad
```php
/**
 * Sanitizar datos
 * 
 * @param mixed $data Datos a sanitizar
 * @param string $type Tipo de sanitización
 * @return mixed Datos sanitizados
 */
function wvs_sanitize_data($data, $type = 'text');

/**
 * Validar datos
 * 
 * @param mixed $data Datos a validar
 * @param string $type Tipo de validación
 * @return bool True si es válido
 */
function wvs_validate_data($data, $type);

/**
 * Obtener IP del cliente
 * 
 * @return string IP del cliente
 */
function wvs_get_client_ip();

/**
 * Generar nonce
 * 
 * @param string $action Acción del nonce
 * @return string Nonce generado
 */
function wvs_create_nonce($action);

/**
 * Verificar nonce
 * 
 * @param string $nonce Nonce a verificar
 * @param string $action Acción del nonce
 * @return bool True si es válido
 */
function wvs_verify_nonce($nonce, $action);
```

### Clases de Utilidad

#### WVS_Helper
```php
class WVS_Helper {
    
    /**
     * Formatear número
     * 
     * @param float $number Número a formatear
     * @param int $decimals Decimales
     * @return string Número formateado
     */
    public static function format_number($number, $decimals = 2);
    
    /**
     * Formatear fecha
     * 
     * @param string $date Fecha a formatear
     * @param string $format Formato de salida
     * @return string Fecha formateada
     */
    public static function format_date($date, $format = 'Y-m-d H:i:s');
    
    /**
     * Generar ID único
     * 
     * @param string $prefix Prefijo
     * @return string ID único
     */
    public static function generate_unique_id($prefix = '');
    
    /**
     * Obtener tamaño de archivo legible
     * 
     * @param int $bytes Bytes
     * @return string Tamaño legible
     */
    public static function format_file_size($bytes);
}
```

## 📚 Ejemplos de Uso

### Ejemplo 1: Conversión de Precio
```php
// Convertir precio de USD a VES
$usd_price = 100.00;
$ves_price = wvs_convert_usd_to_ves($usd_price);

// Calcular precio con IGTF
$igtf_amount = wvs_calculate_igtf($ves_price);
$total_price = $ves_price + $igtf_amount;

echo "Precio USD: $" . $usd_price;
echo "Precio VES: Bs. " . number_format($ves_price, 2, ',', '.');
echo "IGTF: Bs. " . number_format($igtf_amount, 2, ',', '.');
echo "Total: Bs. " . number_format($total_price, 2, ',', '.');
```

### Ejemplo 2: Procesamiento de Pago
```php
// Procesar pago con Zelle
$order_id = 123;
$gateway = 'zelle';
$payment_data = array(
    'email' => 'customer@example.com',
    'amount' => 100.00,
    'reference' => 'ZELLE123456'
);

$result = wvs_process_payment($order_id, $gateway, $payment_data);

if ($result['success']) {
    echo "Pago procesado correctamente";
} else {
    echo "Error: " . $result['message'];
}
```

### Ejemplo 3: Generación de Factura
```php
// Generar factura híbrida
$order_id = 123;
$invoice = wvs_generate_invoice($order_id, 'hybrid');

if ($invoice) {
    echo "Factura generada: " . $invoice['invoice_number'];
    echo "Precio USD: $" . $invoice['usd_amount'];
    echo "Precio VES: Bs. " . $invoice['ves_amount'];
}
```

### Ejemplo 4: Envío de Notificación
```php
// Enviar notificación WhatsApp
$phone = '+584121234567';
$message = 'Su pedido #123 ha sido confirmado. Total: Bs. 3.600,00';

$sent = wvs_send_whatsapp_notification($phone, $message);

if ($sent) {
    echo "Notificación enviada correctamente";
} else {
    echo "Error al enviar notificación";
}
```

### Ejemplo 5: Widget Personalizado
```php
// Crear widget personalizado
class WVS_Custom_Widget extends WVS_Widget_Base {
    
    public function render($args) {
        $currency_rate = wvs_get_bcv_rate();
        
        return sprintf(
            '<div class="wvs-custom-widget">
                <h3>Tasa de Cambio Actual</h3>
                <p>1 USD = %s VES</p>
            </div>',
            number_format($currency_rate, 2, ',', '.')
        );
    }
}

// Registrar widget
wvs_register_widget('custom_rate', 'WVS_Custom_Widget');
```

---

**Última actualización**: 2025-01-27  
**Versión**: 1.0.0
