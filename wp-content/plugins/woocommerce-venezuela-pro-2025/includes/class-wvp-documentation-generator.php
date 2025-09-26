<?php
/**
 * Documentation Generator
 * Automatic documentation generation for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Documentation_Generator {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_ajax_wvp_generate_docs', array( $this, 'ajax_generate_docs' ) );
        add_action( 'wp_ajax_wvp_export_docs', array( $this, 'ajax_export_docs' ) );
        add_action( 'admin_menu', array( $this, 'add_documentation_menu' ), 90 );
        
        // Generate docs on plugin activation
        add_action( 'wvp_plugin_activated', array( $this, 'generate_initial_docs' ) );
    }
    
    /**
     * Generate basic documentation without ReflectionClass
     */
    private function generate_basic_documentation() {
        $docs = array();
        
        // Documentación técnica básica
        $docs['technical'] = array(
            'overview' => array(
                'title' => 'Resumen Técnico',
                'content' => 'WooCommerce Venezuela Pro 2025 es un plugin especializado para el mercado venezolano que incluye:

- Conversión automática de monedas USD ↔ VES
- Cálculo de impuestos venezolanos (IVA, IGTF)
- Métodos de pago locales (Pago Móvil, Zelle, Transferencias)
- Métodos de envío venezolanos (MRW, Zoom, Entrega Local)
- Exportación de reportes SENIAT
- Dashboard de análisis y reportes
- Sistema de notificaciones
- Suite de pruebas automatizadas'
            ),
            'architecture' => array(
                'title' => 'Arquitectura',
                'content' => 'El plugin está estructurado en módulos independientes:

1. Currency Converter - Conversión de monedas
2. Venezuelan Taxes - Cálculo de impuestos
3. Payment Gateways - Métodos de pago locales
4. Shipping Methods - Métodos de envío
5. SENIAT Exporter - Reportes fiscales
6. Admin Dashboard - Panel de administración
7. Analytics Dashboard - Análisis y reportes
8. Notification System - Sistema de notificaciones
9. Testing Suite - Pruebas automatizadas
10. Documentation Generator - Generación de documentación'
            )
        );
        
        // Documentación de usuario
        $docs['user'] = array(
            'installation' => array(
                'title' => 'Instalación',
                'content' => '1. Subir el plugin a /wp-content/plugins/
2. Activar desde el panel de WordPress
3. Configurar los tipos de cambio BCV
4. Configurar métodos de pago locales
5. Configurar métodos de envío
6. Ejecutar el asistente de configuración'
            ),
            'configuration' => array(
                'title' => 'Configuración',
                'content' => 'Configuración básica requerida:

- Tipo de cambio BCV (automático o manual)
- Tasas de impuestos (IVA: 16%, IGTF: 3%)
- Métodos de pago habilitados
- Zonas de envío de Venezuela
- Configuración de notificaciones
- Configuración de reportes SENIAT'
            )
        );
        
        // Documentación de API
        $docs['api'] = array(
            'hooks' => array(
                'title' => 'Hooks Disponibles',
                'content' => 'Hooks principales del plugin:

- wvp_currency_converted - Después de conversión de moneda
- wvp_tax_calculated - Después de cálculo de impuestos
- wvp_order_processed - Después de procesar pedido
- wvp_seniat_report_generated - Después de generar reporte SENIAT
- wvp_notification_sent - Después de enviar notificación'
            ),
            'filters' => array(
                'title' => 'Filtros Disponibles',
                'content' => 'Filtros principales:

- wvp_currency_rate - Modificar tasa de cambio
- wvp_tax_rate - Modificar tasa de impuestos
- wvp_shipping_cost - Modificar costo de envío
- wvp_payment_methods - Modificar métodos de pago
- wvp_notification_channels - Modificar canales de notificación'
            )
        );
        
        // Guía de solución de problemas
        $docs['troubleshooting'] = array(
            'common_issues' => array(
                'title' => 'Problemas Comunes',
                'content' => 'Problemas frecuentes y soluciones:

1. Error de memoria: Aumentar memory_limit en php.ini
2. Conversión de moneda no funciona: Verificar conexión BCV
3. Reportes SENIAT vacíos: Verificar datos de pedidos
4. Notificaciones no se envían: Verificar configuración SMTP
5. Gráficos de análisis vacíos: Verificar datos de pedidos'
            ),
            'debug_mode' => array(
                'title' => 'Modo Debug',
                'content' => 'Para activar el modo debug:

1. Agregar WP_DEBUG = true en wp-config.php
2. Revisar debug.log en /wp-content/
3. Usar la suite de pruebas del plugin
4. Verificar logs de errores del servidor'
            )
        );
        
        // Almacenar documentación
        update_option( 'wvp_documentation', $docs );
        
        return $docs;
    }
    
    /**
     * Generate comprehensive documentation
     */
    public function generate_documentation() {
        $docs = array();
        
        // Generate technical documentation
        $docs['technical'] = $this->generate_technical_docs();
        
        // Generate user documentation
        $docs['user'] = $this->generate_user_docs();
        
        // Generate API documentation
        $docs['api'] = $this->generate_api_docs();
        
        // Generate installation guide
        $docs['installation'] = $this->generate_installation_guide();
        
        // Generate troubleshooting guide
        $docs['troubleshooting'] = $this->generate_troubleshooting_guide();
        
        // Store documentation
        update_option( 'wvp_documentation', $docs );
        
        return $docs;
    }
    
    /**
     * Generate technical documentation
     */
    private function generate_technical_docs() {
        $docs = array();
        
        // Plugin architecture
        $docs['architecture'] = array(
            'title' => 'Arquitectura del Plugin',
            'content' => $this->get_architecture_docs()
        );
        
        // Class documentation
        $docs['classes'] = $this->get_class_documentation();
        
        // Hook documentation
        $docs['hooks'] = $this->get_hook_documentation();
        
        // Database schema
        $docs['database'] = $this->get_database_docs();
        
        return $docs;
    }
    
    /**
     * Generate user documentation
     */
    private function generate_user_docs() {
        $docs = array();
        
        // Installation guide
        $docs['installation'] = array(
            'title' => 'Guía de Instalación',
            'content' => $this->get_installation_guide()
        );
        
        // Configuration guide
        $docs['configuration'] = array(
            'title' => 'Guía de Configuración',
            'content' => $this->get_configuration_guide()
        );
        
        // Usage guide
        $docs['usage'] = array(
            'title' => 'Guía de Uso',
            'content' => $this->get_usage_guide()
        );
        
        // Troubleshooting
        $docs['troubleshooting'] = array(
            'title' => 'Solución de Problemas',
            'content' => $this->get_troubleshooting_guide()
        );
        
        return $docs;
    }
    
    /**
     * Generate API documentation
     */
    private function generate_api_docs() {
        $docs = array();
        
        // AJAX endpoints
        $docs['ajax_endpoints'] = $this->get_ajax_endpoints();
        
        // REST API endpoints
        $docs['rest_endpoints'] = $this->get_rest_endpoints();
        
        // Filter hooks
        $docs['filter_hooks'] = $this->get_filter_hooks();
        
        // Action hooks
        $docs['action_hooks'] = $this->get_action_hooks();
        
        return $docs;
    }
    
    /**
     * Get architecture documentation
     */
    private function get_architecture_docs() {
        return "
# Arquitectura del Plugin WooCommerce Venezuela Pro 2025

## Estructura General

El plugin sigue una arquitectura modular basada en clases PHP con las siguientes características:

### Patrón Singleton
- Todas las clases principales implementan el patrón Singleton
- Garantiza una única instancia por clase
- Facilita el acceso global a funcionalidades

### Separación de Responsabilidades
- **Core**: Funcionalidades principales (conversión de moneda, impuestos)
- **Admin**: Panel de administración y configuración
- **Public**: Funcionalidades del frontend
- **Modules**: Módulos especializados (SENIAT, Analytics, etc.)

### Sistema de Hooks
- Integración completa con WordPress y WooCommerce
- Hooks personalizados para extensibilidad
- Sistema de filtros para personalización

## Clases Principales

### WVP_Simple_Currency_Converter
- Conversión de monedas USD/VES
- Integración con BCV
- Manejo de tipos de cambio

### WVP_Venezuelan_Taxes
- Cálculo de IVA (16%)
- Cálculo de IGTF (3%)
- Manejo de impuestos venezolanos

### WVP_Venezuelan_Shipping
- Métodos de envío locales
- Cálculo de costos por estado
- Integración con MRW, Zoom, etc.

### WVP_SENIAT_Exporter
- Generación de reportes fiscales
- Libro de ventas
- Reportes de IVA e IGTF

## Flujo de Datos

1. **Entrada**: Datos de productos en USD
2. **Conversión**: Aplicación de tipo de cambio BCV
3. **Cálculo**: Aplicación de impuestos venezolanos
4. **Salida**: Precios en VES con impuestos incluidos
";
    }
    
    /**
     * Get class documentation
     */
    private function get_class_documentation() {
        $classes = array(
            'WVP_Simple_Currency_Converter',
            'WVP_Venezuelan_Taxes',
            'WVP_Venezuelan_Shipping',
            'WVP_Pago_Movil_Gateway',
            'WVP_Admin_Dashboard',
            'WVP_SENIAT_Exporter',
            'WVP_Cache_Manager',
            'WVP_Database_Optimizer',
            'WVP_Assets_Optimizer',
            'WVP_Security_Manager',
            'WVP_Venezuelan_Validator',
            'WVP_Setup_Wizard',
            'WVP_Notification_System',
            'WVP_Analytics_Dashboard',
            'WVP_Final_Optimizer',
            'WVP_Testing_Suite'
        );
        
        $docs = array();
        
        foreach ( $classes as $class ) {
            if ( class_exists( $class ) ) {
                $reflection = new ReflectionClass( $class );
                $methods = $reflection->getMethods( ReflectionMethod::IS_PUBLIC );
                
                $docs[ $class ] = array(
                    'title' => $class,
                    'description' => $this->get_class_description( $class ),
                    'methods' => array()
                );
                
                foreach ( $methods as $method ) {
                    $docs[ $class ]['methods'][] = array(
                        'name' => $method->getName(),
                        'parameters' => $method->getParameters(),
                        'docblock' => $method->getDocComment()
                    );
                }
            }
        }
        
        return $docs;
    }
    
    /**
     * Get hook documentation
     */
    private function get_hook_documentation() {
        return array(
            'woocommerce_init' => array(
                'description' => 'Inicialización de funcionalidades de WooCommerce',
                'priority' => 10,
                'parameters' => 'Ninguno'
            ),
            'woocommerce_cart_calculate_fees' => array(
                'description' => 'Cálculo de impuestos en el carrito',
                'priority' => 10,
                'parameters' => 'WC_Cart $cart'
            ),
            'woocommerce_checkout_process' => array(
                'description' => 'Validación durante el proceso de checkout',
                'priority' => 10,
                'parameters' => 'Ninguno'
            ),
            'wp_ajax_wvp_convert_price' => array(
                'description' => 'Conversión de precios via AJAX',
                'priority' => 10,
                'parameters' => 'Ninguno'
            ),
            'admin_menu' => array(
                'description' => 'Registro de menús de administración',
                'priority' => 10,
                'parameters' => 'Ninguno'
            )
        );
    }
    
    /**
     * Get database documentation
     */
    private function get_database_docs() {
        return "
# Esquema de Base de Datos

## Tablas Personalizadas

### wp_wvp_currency_rates
- Almacena tipos de cambio del BCV
- Campos: id, rate, currency_from, currency_to, date_created

### wp_wvp_analytics_data
- Datos de analytics del plugin
- Campos: id, metric_name, metric_value, date_recorded

### wp_wvp_notifications
- Historial de notificaciones enviadas
- Campos: id, type, recipient, subject, message, sent_at

## Opciones de WordPress

### wvp_bcv_rate
- Tipo de cambio actual del BCV
- Valor por defecto: 36.5

### wvp_emergency_bcv_rate
- Tipo de cambio de emergencia
- Valor por defecto: 50.0

### wvp_enable_iva
- Habilitar cálculo de IVA
- Valor por defecto: true

### wvp_enable_igtf
- Habilitar cálculo de IGTF
- Valor por defecto: false

## Transients

### wvp_bcv_rate_cache
- Cache del tipo de cambio BCV
- Duración: 30 minutos

### wvp_analytics_cache
- Cache de datos de analytics
- Duración: 1 hora
";
    }
    
    /**
     * Get installation guide
     */
    private function get_installation_guide() {
        return "
# Guía de Instalación - WooCommerce Venezuela Pro 2025

## Requisitos del Sistema

- WordPress 5.0 o superior
- WooCommerce 5.0 o superior
- PHP 7.4 o superior
- MySQL 5.6 o superior
- Memoria PHP: 256MB mínimo

## Instalación Manual

1. **Descargar el plugin**
   - Descargar el archivo ZIP del plugin
   - Extraer en el directorio de plugins

2. **Subir archivos**
   - Subir la carpeta del plugin a `/wp-content/plugins/`
   - Verificar permisos de archivos (644 para archivos, 755 para directorios)

3. **Activar el plugin**
   - Ir a Plugins > Plugins Instalados
   - Activar 'WooCommerce Venezuela Pro 2025'

4. **Configuración inicial**
   - Ir a WooCommerce > Configuración > Venezuela Pro
   - Configurar tipo de cambio BCV
   - Habilitar funcionalidades necesarias

## Instalación via WP-CLI

```bash
wp plugin install woocommerce-venezuela-pro-2025.zip --activate
wp option update wvp_bcv_rate 36.5
wp option update wvp_enable_iva true
```

## Verificación de Instalación

1. Verificar que WooCommerce esté activo
2. Comprobar que el menú 'Venezuela Pro' aparezca en el admin
3. Verificar que los métodos de pago venezolanos estén disponibles
4. Comprobar que los métodos de envío locales estén configurados

## Solución de Problemas Comunes

### Error de memoria
- Aumentar memory_limit en php.ini
- Verificar que no hay otros plugins consumiendo memoria

### Error de permisos
- Verificar permisos de archivos y directorios
- Asegurar que WordPress puede escribir en wp-content

### Conflictos con otros plugins
- Desactivar otros plugins temporalmente
- Verificar compatibilidad con temas activos
";
    }
    
    /**
     * Get configuration guide
     */
    private function get_configuration_guide() {
        return "
# Guía de Configuración - WooCommerce Venezuela Pro 2025

## Configuración Básica

### 1. Tipo de Cambio BCV
- Ir a Venezuela Pro > Configuración General
- Configurar tipo de cambio actual
- Habilitar actualización automática

### 2. Impuestos Venezolanos
- Habilitar IVA (16%)
- Configurar IGTF si es necesario (3%)
- Establecer base de cálculo

### 3. Monedas Soportadas
- USD como moneda base
- VES como moneda de destino
- Configurar símbolos de moneda

## Configuración Avanzada

### 1. Métodos de Pago
- Pago Móvil
- Transferencias bancarias
- Zelle
- Configurar cuentas bancarias

### 2. Métodos de Envío
- MRW
- Zoom
- Mensajero
- Entrega local
- Configurar costos por estado

### 3. Reportes SENIAT
- Habilitar generación de reportes
- Configurar formato de exportación
- Establecer período de reportes

## Configuración de Notificaciones

### 1. Email
- Configurar SMTP
- Establecer plantillas
- Configurar destinatarios

### 2. WhatsApp (opcional)
- Configurar API de WhatsApp
- Establecer plantillas
- Configurar horarios de envío

## Configuración de Analytics

### 1. Métricas Básicas
- Ventas diarias/mensuales
- Productos más vendidos
- Clientes activos

### 2. Reportes Personalizados
- Configurar métricas específicas
- Establecer períodos de reporte
- Configurar exportación
";
    }
    
    /**
     * Get usage guide
     */
    private function get_usage_guide() {
        return "
# Guía de Uso - WooCommerce Venezuela Pro 2025

## Uso Básico

### 1. Conversión de Monedas
- Los precios se convierten automáticamente
- Tipo de cambio actualizado desde BCV
- Conversión en tiempo real

### 2. Cálculo de Impuestos
- IVA aplicado automáticamente
- IGTF configurable
- Impuestos incluidos en precios

### 3. Checkout
- Métodos de pago venezolanos
- Validación de datos locales
- Confirmación por email

## Uso Avanzado

### 1. Reportes SENIAT
- Generar libro de ventas
- Exportar reportes de IVA
- Generar facturas electrónicas

### 2. Analytics
- Ver métricas de ventas
- Analizar productos populares
- Exportar reportes

### 3. Notificaciones
- Configurar alertas automáticas
- Notificaciones por email
- Integración con WhatsApp

## Funcionalidades Especiales

### 1. Validación Venezolana
- Validación de RIF
- Validación de números telefónicos
- Validación de direcciones

### 2. Integración BCV
- Actualización automática de tipos de cambio
- Sistema de respaldo
- Notificaciones de cambios

### 3. Cache Inteligente
- Cache de conversiones
- Cache de reportes
- Optimización automática
";
    }
    
    /**
     * Get troubleshooting guide
     */
    private function get_troubleshooting_guide() {
        return "
# Guía de Solución de Problemas - WooCommerce Venezuela Pro 2025

## Problemas Comunes

### 1. Conversión de Monedas No Funciona
**Síntomas:**
- Precios no se convierten a VES
- Tipo de cambio no se actualiza

**Soluciones:**
- Verificar que BCV esté disponible
- Comprobar configuración de tipo de cambio
- Limpiar cache del plugin
- Verificar que WooCommerce esté activo

### 2. Impuestos No Se Calculan
**Síntomas:**
- IVA no se aplica
- IGTF no se calcula

**Soluciones:**
- Verificar configuración de impuestos
- Comprobar que estén habilitados
- Verificar configuración de WooCommerce
- Revisar logs de errores

### 3. Reportes SENIAT No Se Generan
**Síntomas:**
- Error al generar reportes
- Archivos no se descargan

**Soluciones:**
- Verificar permisos de escritura
- Comprobar configuración de exportación
- Verificar que hay datos para reportar
- Revisar logs de errores

## Problemas de Performance

### 1. Página Lenta
**Soluciones:**
- Habilitar cache del plugin
- Optimizar base de datos
- Verificar memoria PHP
- Revisar otros plugins

### 2. Memoria Insuficiente
**Soluciones:**
- Aumentar memory_limit
- Optimizar consultas de base de datos
- Limpiar cache regularmente
- Revisar plugins conflictivos

## Problemas de Integración

### 1. Conflictos con Otros Plugins
**Soluciones:**
- Desactivar otros plugins temporalmente
- Verificar orden de carga
- Revisar hooks conflictivos
- Contactar soporte

### 2. Problemas con Temas
**Soluciones:**
- Cambiar a tema por defecto
- Verificar compatibilidad
- Revisar CSS conflictivo
- Contactar desarrollador del tema

## Logs y Debugging

### 1. Habilitar Debug
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### 2. Revisar Logs
- wp-content/debug.log
- Logs del servidor web
- Logs de PHP

### 3. Información del Sistema
- Versión de WordPress
- Versión de WooCommerce
- Versión de PHP
- Memoria disponible
";
    }
    
    /**
     * Get AJAX endpoints
     */
    private function get_ajax_endpoints() {
        return array(
            'wvp_convert_price' => array(
                'description' => 'Convierte precio de USD a VES',
                'parameters' => array(
                    'price' => 'Precio en USD',
                    'currency_from' => 'Moneda origen (USD)',
                    'currency_to' => 'Moneda destino (VES)'
                ),
                'response' => 'Precio convertido en VES'
            ),
            'wvp_export_seniat' => array(
                'description' => 'Exporta reportes SENIAT',
                'parameters' => array(
                    'report_type' => 'Tipo de reporte',
                    'date_from' => 'Fecha inicio',
                    'date_to' => 'Fecha fin'
                ),
                'response' => 'Archivo de reporte'
            ),
            'wvp_generate_invoice' => array(
                'description' => 'Genera factura electrónica',
                'parameters' => array(
                    'order_id' => 'ID del pedido',
                    'format' => 'Formato de salida'
                ),
                'response' => 'Factura generada'
            )
        );
    }
    
    /**
     * Get REST endpoints
     */
    private function get_rest_endpoints() {
        return array(
            '/wp-json/wvp/v1/currency/convert' => array(
                'method' => 'POST',
                'description' => 'Convierte moneda via REST API',
                'parameters' => array(
                    'price' => 'Precio a convertir',
                    'from' => 'Moneda origen',
                    'to' => 'Moneda destino'
                )
            ),
            '/wp-json/wvp/v1/analytics/data' => array(
                'method' => 'GET',
                'description' => 'Obtiene datos de analytics',
                'parameters' => array(
                    'period' => 'Período de datos',
                    'metric' => 'Métrica específica'
                )
            )
        );
    }
    
    /**
     * Get filter hooks
     */
    private function get_filter_hooks() {
        return array(
            'wvp_currency_rate' => array(
                'description' => 'Filtra tipo de cambio antes de usar',
                'parameters' => 'float $rate, string $currency_from, string $currency_to',
                'return' => 'float $rate'
            ),
            'wvp_tax_rate' => array(
                'description' => 'Filtra tasa de impuesto',
                'parameters' => 'float $rate, string $tax_type',
                'return' => 'float $rate'
            ),
            'wvp_shipping_cost' => array(
                'description' => 'Filtra costo de envío',
                'parameters' => 'float $cost, string $method, string $state',
                'return' => 'float $cost'
            )
        );
    }
    
    /**
     * Get action hooks
     */
    private function get_action_hooks() {
        return array(
            'wvp_currency_converted' => array(
                'description' => 'Se ejecuta después de conversión de moneda',
                'parameters' => 'float $original_price, float $converted_price, string $from, string $to'
            ),
            'wvp_tax_calculated' => array(
                'description' => 'Se ejecuta después de cálculo de impuestos',
                'parameters' => 'float $base_price, float $tax_amount, string $tax_type'
            ),
            'wvp_report_generated' => array(
                'description' => 'Se ejecuta después de generar reporte',
                'parameters' => 'string $report_type, string $file_path'
            )
        );
    }
    
    /**
     * Get class description
     */
    private function get_class_description( $class ) {
        $descriptions = array(
            'WVP_Simple_Currency_Converter' => 'Maneja la conversión de monedas USD/VES con integración BCV',
            'WVP_Venezuelan_Taxes' => 'Calcula impuestos venezolanos (IVA, IGTF)',
            'WVP_Venezuelan_Shipping' => 'Métodos de envío específicos para Venezuela',
            'WVP_Pago_Movil_Gateway' => 'Gateway de pago móvil venezolano',
            'WVP_Admin_Dashboard' => 'Panel de administración del plugin',
            'WVP_SENIAT_Exporter' => 'Genera reportes fiscales para SENIAT',
            'WVP_Cache_Manager' => 'Maneja el sistema de cache del plugin',
            'WVP_Database_Optimizer' => 'Optimiza la base de datos',
            'WVP_Assets_Optimizer' => 'Optimiza CSS y JavaScript',
            'WVP_Security_Manager' => 'Maneja la seguridad del plugin',
            'WVP_Venezuelan_Validator' => 'Valida datos específicos de Venezuela',
            'WVP_Setup_Wizard' => 'Asistente de configuración inicial',
            'WVP_Notification_System' => 'Sistema de notificaciones multi-canal',
            'WVP_Analytics_Dashboard' => 'Dashboard de analytics y reportes',
            'WVP_Final_Optimizer' => 'Optimizaciones finales y testing',
            'WVP_Testing_Suite' => 'Suite de pruebas automatizadas'
        );
        
        return $descriptions[ $class ] ?? 'Clase del plugin WooCommerce Venezuela Pro 2025';
    }
    
    /**
     * Generate initial documentation
     */
    public function generate_initial_docs() {
        $this->generate_documentation();
    }
    
    /**
     * Add documentation admin menu
     */
    public function add_documentation_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Documentación',
            'Documentación',
            'manage_options',
            'wvp-documentation',
            array( $this, 'documentation_admin_page' )
        );
    }
    
    /**
     * Documentation admin page
     */
    public function documentation_admin_page() {
        $docs = get_option( 'wvp_documentation', array() );
        
        // Si no hay documentación, crear una básica sin usar ReflectionClass
        if ( empty( $docs ) ) {
            $docs = $this->generate_basic_documentation();
        }
        ?>
        <div class="wrap">
            <h1>📚 Documentación - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-docs-actions">
                <button class="button button-primary" id="generate-docs">
                    🔄 Regenerar Documentación
                </button>
                <button class="button button-secondary" id="export-docs">
                    📥 Exportar Documentación
                </button>
            </div>
            
            <div class="wvp-docs-content">
                <?php foreach ( $docs as $section => $section_docs ) : ?>
                    <div class="wvp-docs-section">
                        <h2><?php echo esc_html( ucfirst( $section ) ); ?></h2>
                        
                        <?php if ( is_array( $section_docs ) ) : ?>
                            <?php foreach ( $section_docs as $doc_key => $doc_content ) : ?>
                                <div class="wvp-doc-item">
                                    <h3><?php echo esc_html( $doc_content['title'] ?? ucfirst( $doc_key ) ); ?></h3>
                                    <div class="wvp-doc-content">
                                        <?php if ( is_string( $doc_content['content'] ) ) : ?>
                                            <pre><?php echo esc_html( $doc_content['content'] ); ?></pre>
                                        <?php else : ?>
                                            <pre><?php echo esc_html( print_r( $doc_content, true ) ); ?></pre>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <style>
        .wvp-docs-actions {
            margin: 20px 0;
        }
        
        .wvp-docs-section {
            margin: 30px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .wvp-docs-section h2 {
            margin: 0 0 20px 0;
            color: #333;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .wvp-doc-item {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
        }
        
        .wvp-doc-item h3 {
            margin: 0 0 15px 0;
            color: #0073aa;
        }
        
        .wvp-doc-content {
            background: #fff;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .wvp-doc-content pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.4;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-docs').on('click', function() {
                $(this).prop('disabled', true).text('Generando documentación...');
                
                $.post(ajaxurl, {
                    action: 'wvp_generate_docs',
                    nonce: '<?php echo wp_create_nonce( 'wvp_docs_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Documentación generada exitosamente');
                        location.reload();
                    } else {
                        alert('Error al generar documentación');
                    }
                }).always(function() {
                    $('#generate-docs').prop('disabled', false).text('🔄 Regenerar Documentación');
                });
            });
            
            $('#export-docs').on('click', function() {
                $(this).prop('disabled', true).text('Exportando...');
                
                $.post(ajaxurl, {
                    action: 'wvp_export_docs',
                    nonce: '<?php echo wp_create_nonce( 'wvp_docs_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        window.open(response.data.download_url);
                    } else {
                        alert('Error al exportar documentación');
                    }
                }).always(function() {
                    $('#export-docs').prop('disabled', false).text('📥 Exportar Documentación');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX generate docs
     */
    public function ajax_generate_docs() {
        check_ajax_referer( 'wvp_docs_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $docs = $this->generate_documentation();
        wp_send_json_success( $docs );
    }
    
    /**
     * AJAX export docs
     */
    public function ajax_export_docs() {
        check_ajax_referer( 'wvp_docs_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $docs = get_option( 'wvp_documentation', array() );
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/wvp-documentation-' . date( 'Y-m-d' ) . '.json';
        
        file_put_contents( $file_path, json_encode( $docs, JSON_PRETTY_PRINT ) );
        
        $download_url = $upload_dir['url'] . '/wvp-documentation-' . date( 'Y-m-d' ) . '.json';
        
        wp_send_json_success( array( 'download_url' => $download_url ) );
    }
}
