# Arquitectura del Plugin - WooCommerce Venezuela Pro

## Visión General de la Arquitectura

El plugin WooCommerce Venezuela Pro sigue una arquitectura modular y orientada a objetos, diseñada para ser escalable, mantenible y fácil de extender. Utiliza patrones de diseño establecidos y mejores prácticas de WordPress.

## Patrones de Diseño Implementados

### 1. Singleton Pattern
La clase principal `WooCommerce_Venezuela_Pro` implementa el patrón Singleton:

```php
class WooCommerce_Venezuela_Pro {
    private static $instance = null;
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

**Ventajas**:
- Garantiza una única instancia del plugin
- Control centralizado de recursos
- Facilita el acceso global a la instancia

### 2. Factory Pattern
Los componentes del plugin se inicializan dinámicamente:

```php
private function init_components() {
    $this->bcv_integrator = new WVP_BCV_Integrator();
    $this->checkout = new WVP_Checkout();
    // ... más componentes
}
```

### 3. Observer Pattern
Utiliza hooks de WordPress para comunicación entre componentes:

```php
add_action('woocommerce_cart_calculate_fees', array($this, 'apply_igtf_fee'));
add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
```

## Estructura de Clases

### Jerarquía Principal

```
WooCommerce_Venezuela_Pro (Singleton)
├── WVP_BCV_Integrator
├── WVP_IGTF_Manager
├── WVP_Price_Calculator
├── WVP_Admin_Restructured
├── WVP_Security_Validator
├── WVP_Performance_Optimizer
└── WVP_Error_Handler
```

### Clases por Categoría

#### Core Classes (includes/)
- **WVP_BCV_Integrator**: Integración con BCV Dólar Tracker
- **WVP_IGTF_Manager**: Gestión de impuestos venezolanos
- **WVP_Price_Calculator**: Cálculo optimizado de precios
- **WVP_Security_Validator**: Validación de seguridad
- **WVP_Performance_Optimizer**: Optimización de rendimiento
- **WVP_Error_Handler**: Manejo centralizado de errores

#### Admin Classes (admin/)
- **WVP_Admin_Restructured**: Panel de administración moderno
- **WVP_Order_Meta**: Metadatos de órdenes
- **WVP_Reports**: Sistema de reportes
- **WVP_Payment_Verification**: Verificación de pagos

#### Frontend Classes (frontend/)
- **WVP_Checkout**: Personalización del checkout
- **WVP_Hybrid_Invoicing**: Facturación híbrida

#### Gateway Classes (gateways/)
- **WVP_Gateway_Pago_Movil**: Pasarela Pago Móvil
- **WVP_Gateway_Zelle**: Pasarela Zelle
- **WVP_Gateway_Efectivo**: Pasarela Efectivo
- **WVP_Gateway_Cashea**: Pasarela Cashea

## Flujo de Inicialización

### 1. Bootstrap (woocommerce-venezuela-pro.php)
```php
// Definir constantes
define('WVP_VERSION', '1.0.0');
define('WVP_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Inicializar plugin
WooCommerce_Venezuela_Pro::get_instance();
```

### 2. Verificación de Dependencias
```php
private function check_dependencies() {
    // Verificar WooCommerce
    if (!class_exists('WooCommerce')) {
        return false;
    }
    
    // Verificar BCV Dólar Tracker
    if (!is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
        return false;
    }
    
    return true;
}
```

### 3. Carga de Dependencias
```php
private function load_dependencies() {
    // Clases de seguridad (CRÍTICAS - CARGAR PRIMERO)
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-security-validator.php';
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-rate-limiter.php';
    
    // Clases de lógica de negocio
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-igtf-manager.php';
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-price-calculator.php';
    
    // ... más clases
}
```

### 4. Inicialización de Componentes
```php
private function init_components() {
    try {
        // Inicializar componentes críticos
        $this->bcv_integrator = new WVP_BCV_Integrator();
        $this->checkout = new WVP_Checkout();
        
        // Registrar hooks
        add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
        add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));
        
    } catch (Exception $e) {
        error_log('WVP Error: ' . $e->getMessage());
    }
}
```

## Sistema de Configuración

### Opciones de WordPress
El plugin utiliza el sistema de opciones de WordPress con prefijo `wvp_`:

```php
// Obtener opción
public function get_option($option_name, $default = null) {
    return get_option('wvp_' . $option_name, $default);
}

// Actualizar opción
public function update_option($option_name, $value) {
    return update_option('wvp_' . $option_name, $value);
}
```

### Configuraciones por Defecto
```php
private function set_default_options() {
    if (!get_option('wvp_igtf_rate')) {
        update_option('wvp_igtf_rate', 3.0);
    }
    
    if (!get_option('wvp_bcv_cache_duration')) {
        update_option('wvp_bcv_cache_duration', 3600);
    }
}
```

## Sistema de Base de Datos

### Tablas Personalizadas
El plugin crea tablas específicas para sus funcionalidades:

```sql
-- Tabla de logs de errores
CREATE TABLE wp_wvp_error_logs (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    timestamp datetime DEFAULT CURRENT_TIMESTAMP,
    level varchar(20) NOT NULL,
    message text NOT NULL,
    context text,
    file varchar(255),
    line int(11),
    PRIMARY KEY (id),
    KEY level (level),
    KEY timestamp (timestamp)
);

-- Tabla de logs de seguridad
CREATE TABLE wp_wvp_security_logs (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    timestamp datetime DEFAULT CURRENT_TIMESTAMP,
    user_id int(11),
    ip_address varchar(45),
    action varchar(100) NOT NULL,
    details text,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY timestamp (timestamp),
    KEY action (action)
);
```

### Migración de Datos
```php
private function create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
```

## Sistema de Caché

### Estrategia de Caché Multi-Nivel
1. **Caché de Objeto**: Para datos frecuentemente accedidos
2. **Caché de Transients**: Para datos temporales
3. **Caché de Base de Datos**: Para consultas complejas

### Implementación
```php
class WVP_Advanced_Cache {
    private $cache_duration = 3600; // 1 hora
    
    public function get($key) {
        $cached = wp_cache_get($key, 'wvp');
        if (false === $cached) {
            return null;
        }
        return $cached;
    }
    
    public function set($key, $data, $duration = null) {
        $duration = $duration ?: $this->cache_duration;
        return wp_cache_set($key, $data, 'wvp', $duration);
    }
}
```

## Sistema de Seguridad

### Validación de Datos
```php
class WVP_Security_Validator {
    public static function validate_nonce($nonce, $action) {
        if (empty($nonce) || empty($action)) {
            return false;
        }
        return wp_verify_nonce($nonce, $action);
    }
    
    public static function sanitize_input($data) {
        if (is_array($data)) {
            return array_map(array(self::class, 'sanitize_input'), $data);
        }
        return sanitize_text_field($data);
    }
}
```

### Rate Limiting
```php
class WVP_Rate_Limiter {
    public function check_rate_limit($identifier, $max_attempts = 5, $time_window = 300) {
        $attempts = get_transient("wvp_rate_limit_{$identifier}");
        
        if ($attempts >= $max_attempts) {
            return false;
        }
        
        set_transient("wvp_rate_limit_{$identifier}", $attempts + 1, $time_window);
        return true;
    }
}
```

## Sistema de Logging

### Logger Centralizado
```php
class WVP_Error_Handler {
    public function log_error($level, $message, $context = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'file' => debug_backtrace()[1]['file'] ?? '',
            'line' => debug_backtrace()[1]['line'] ?? 0
        );
        
        $this->write_to_database($log_entry);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WVP [{$level}] {$message}");
        }
    }
}
```

## Sistema de Hooks y Filtros

### Hooks Principales
```php
// Hooks de WooCommerce
add_action('woocommerce_cart_calculate_fees', array($this, 'apply_igtf_fee'));
add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));

// Hooks de WordPress
add_action('admin_menu', array($this, 'add_admin_menu'));
add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

// Hooks personalizados
add_action('wvp_bcv_rate_updated', array($this, 'handle_rate_update'));
add_filter('wvp_price_calculation', array($this, 'modify_price_calculation'));
```

### Sistema de Eventos Personalizados
```php
// Disparar evento personalizado
do_action('wvp_order_processed', $order_id, $order_data);

// Escuchar evento personalizado
add_action('wvp_order_processed', 'handle_order_processed', 10, 2);
```

## Compatibilidad y Migración

### WooCommerce HPOS
```php
// Declarar compatibilidad con HPOS
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables', 
            __FILE__, 
            true
        );
    }
});
```

### Migración de Datos
```php
public function migrate_to_hpos() {
    if (get_option('wvp_hpos_migrated')) {
        return;
    }
    
    // Migrar datos de órdenes
    $this->migrate_order_data();
    
    // Marcar como migrado
    update_option('wvp_hpos_migrated', true);
}
```

## Extensibilidad

### Sistema de Extensiones
```php
// Permitir que otros plugins extiendan funcionalidades
add_action('wvp_loaded', function() {
    do_action('wvp_extensions_loaded');
});

// Hook para extensiones
add_action('wvp_extensions_loaded', 'load_custom_extensions');
```

### API Pública
```php
// Funciones públicas para desarrolladores
function wvp_get_bcv_rate() {
    return WVP_BCV_Integrator::get_rate();
}

function wvp_convert_usd_to_ves($amount) {
    return WVP_BCV_Integrator::convert_usd_to_ves($amount);
}
```

## Consideraciones de Rendimiento

### Optimización de Consultas
- Uso de índices en tablas personalizadas
- Caché de consultas frecuentes
- Lazy loading de componentes

### Gestión de Memoria
- Limpieza automática de caché
- Gestión eficiente de objetos grandes
- Optimización de arrays y objetos

### Minificación de Assets
```php
class WVP_Asset_Optimizer {
    public function minify_css($css) {
        // Remover comentarios
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remover espacios innecesarios
        $css = preg_replace('/\s+/', ' ', $css);
        
        return trim($css);
    }
}
```

## Conclusión

La arquitectura del plugin WooCommerce Venezuela Pro está diseñada para ser:

- **Modular**: Componentes independientes y reutilizables
- **Escalable**: Fácil adición de nuevas funcionalidades
- **Mantenible**: Código bien estructurado y documentado
- **Segura**: Múltiples capas de validación y seguridad
- **Optimizada**: Sistemas de caché y optimización de rendimiento
- **Extensible**: APIs públicas para desarrolladores externos

Esta arquitectura proporciona una base sólida para el desarrollo futuro y el mantenimiento del plugin.
