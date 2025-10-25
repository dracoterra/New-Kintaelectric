# Guía de Desarrollo - WooCommerce Venezuela Pro

## Configuración del Entorno de Desarrollo

### Requisitos del Sistema
- **WordPress**: 5.0+ (recomendado: última versión estable)
- **PHP**: 7.4+ (recomendado: 8.0+)
- **MySQL**: 5.7+ o MariaDB 10.3+
- **WooCommerce**: 5.0+ (recomendado: última versión estable)
- **BCV Dólar Tracker**: Plugin obligatorio

### Herramientas de Desarrollo Recomendadas
- **IDE**: VS Code, PhpStorm, o Sublime Text
- **Debugging**: Xdebug para PHP
- **Versionado**: Git
- **Testing**: PHPUnit, WP-CLI

### Configuración Local
```bash
# Clonar el repositorio
git clone [repository-url] woocommerce-venezuela-pro

# Navegar al directorio
cd woocommerce-venezuela-pro

# Instalar dependencias (si las hay)
composer install

# Configurar WordPress para desarrollo
# En wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

## Estándares de Código

### WordPress Coding Standards
El plugin sigue los estándares de WordPress:

```php
/**
 * Descripción breve de la función
 *
 * Descripción detallada si es necesaria.
 *
 * @since      1.0.0
 * @param      string    $param_name    Descripción del parámetro.
 * @return     mixed     Descripción del valor de retorno.
 */
function wvp_example_function($param_name) {
    // Implementación
}
```

### Nomenclatura

#### Clases
- **Prefijo**: `WVP_` para clases del plugin
- **Formato**: PascalCase
- **Ejemplos**:
  - `WVP_BCV_Integrator`
  - `WVP_IGTF_Manager`
  - `WVP_Price_Calculator`

#### Funciones
- **Prefijo**: `wvp_` para funciones del plugin
- **Formato**: snake_case
- **Ejemplos**:
  - `wvp_get_bcv_rate()`
  - `wvp_calculate_igtf()`
  - `wvp_format_price()`

#### Constantes
- **Prefijo**: `WVP_` para constantes del plugin
- **Formato**: UPPER_CASE
- **Ejemplos**:
  - `WVP_VERSION`
  - `WVP_PLUGIN_PATH`
  - `WVP_PLUGIN_URL`

#### Variables
- **Formato**: snake_case
- **Ejemplos**:
  - `$bcv_rate`
  - `$igtf_amount`
  - `$customer_data`

### Estructura de Archivos

#### Organización por Funcionalidad
```
includes/
├── class-wvp-bcv-integrator.php      # Integración BCV
├── class-wvp-igtf-manager.php         # Gestión IGTF
├── class-wvp-price-calculator.php    # Cálculo de precios
├── class-wvp-security-validator.php  # Validación de seguridad
└── class-wvp-performance-optimizer.php # Optimización
```

#### Separación de Responsabilidades
- **Core**: Funcionalidades principales
- **Admin**: Funcionalidades administrativas
- **Frontend**: Funcionalidades del frontend
- **Gateways**: Pasarelas de pago
- **Shipping**: Métodos de envío

## Patrones de Desarrollo

### 1. Singleton Pattern
Para clases que necesitan una única instancia:

```php
class WVP_Example_Singleton {
    private static $instance = null;
    
    private function __construct() {
        // Constructor privado
    }
    
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### 2. Factory Pattern
Para crear objetos dinámicamente:

```php
class WVP_Gateway_Factory {
    public static function create_gateway($gateway_type) {
        switch ($gateway_type) {
            case 'pago_movil':
                return new WVP_Gateway_Pago_Movil();
            case 'zelle':
                return new WVP_Gateway_Zelle();
            case 'efectivo':
                return new WVP_Gateway_Efectivo();
            default:
                throw new Exception('Gateway type not supported');
        }
    }
}
```

### 3. Observer Pattern
Usando hooks de WordPress:

```php
class WVP_Event_Observer {
    public function __construct() {
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'));
        add_action('wvp_payment_verified', array($this, 'handle_payment_verification'));
    }
    
    public function handle_order_status_change($order_id, $old_status, $new_status) {
        // Manejar cambio de estado
    }
}
```

## Manejo de Datos

### Sanitización de Entrada
```php
public function sanitize_input($data) {
    if (is_array($data)) {
        return array_map(array($this, 'sanitize_input'), $data);
    }
    
    return sanitize_text_field($data);
}
```

### Validación de Datos
```php
public function validate_email($email) {
    if (!is_email($email)) {
        throw new InvalidArgumentException('Invalid email format');
    }
    return $email;
}
```

### Escape de Salida
```php
public function display_price($price) {
    echo esc_html($this->format_price($price));
}
```

## Manejo de Errores

### Try-Catch Blocks
```php
public function process_payment($order_id) {
    try {
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Procesar pago
        $result = $this->execute_payment($order);
        
        if (!$result['success']) {
            throw new Exception($result['message']);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('WVP Payment Error: ' . $e->getMessage());
        return array(
            'success' => false,
            'message' => $e->getMessage()
        );
    }
}
```

### Logging de Errores
```php
public function log_error($level, $message, $context = array()) {
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'file' => debug_backtrace()[1]['file'] ?? '',
        'line' => debug_backtrace()[1]['line'] ?? 0
    );
    
    // Escribir a base de datos
    $this->write_to_database($log_entry);
    
    // Escribir a log de WordPress
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("WVP [{$level}] {$message}");
    }
}
```

## Testing

### Testing Unitario
```php
class Test_WVP_BCV_Integrator extends WP_UnitTestCase {
    public function test_get_rate() {
        $rate = WVP_BCV_Integrator::get_rate();
        $this->assertIsFloat($rate);
        $this->assertGreaterThan(0, $rate);
    }
    
    public function test_convert_usd_to_ves() {
        $usd_amount = 100;
        $rate = 36.50;
        $ves_amount = WVP_BCV_Integrator::convert_usd_to_ves($usd_amount, $rate);
        
        $this->assertEquals(3650, $ves_amount);
    }
}
```

### Testing de Integración
```php
class Test_WVP_IGTF_Manager extends WP_UnitTestCase {
    public function test_apply_igtf_fee() {
        // Crear carrito de prueba
        $cart = WC()->cart;
        $cart->add_to_cart($product_id, 1);
        
        // Aplicar IGTF
        $igtf_manager = new WVP_IGTF_Manager();
        $igtf_manager->apply_igtf_fee($cart);
        
        // Verificar que se aplicó la tarifa
        $fees = $cart->get_fees();
        $this->assertNotEmpty($fees);
    }
}
```

## Optimización de Rendimiento

### Caché
```php
public function get_cached_data($key) {
    $cached = wp_cache_get($key, 'wvp');
    
    if (false === $cached) {
        $cached = $this->fetch_data_from_source();
        wp_cache_set($key, $cached, 'wvp', 3600); // 1 hora
    }
    
    return $cached;
}
```

### Consultas Optimizadas
```php
public function get_recent_orders($limit = 10) {
    global $wpdb;
    
    $query = $wpdb->prepare("
        SELECT * FROM {$wpdb->posts} 
        WHERE post_type = 'shop_order' 
        AND post_status IN ('wc-processing', 'wc-completed')
        ORDER BY post_date DESC 
        LIMIT %d
    ", $limit);
    
    return $wpdb->get_results($query);
}
```

### Lazy Loading
```php
class WVP_Lazy_Loader {
    private $loaded_components = array();
    
    public function load_component($component_name) {
        if (!isset($this->loaded_components[$component_name])) {
            $this->loaded_components[$component_name] = $this->create_component($component_name);
        }
        
        return $this->loaded_components[$component_name];
    }
}
```

## Seguridad

### Validación de Nonces
```php
public function handle_ajax_request() {
    if (!wp_verify_nonce($_POST['nonce'], 'wvp_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    // Procesar request
}
```

### Rate Limiting
```php
public function check_rate_limit($identifier, $max_attempts = 5) {
    $attempts = get_transient("wvp_rate_limit_{$identifier}");
    
    if ($attempts >= $max_attempts) {
        return false;
    }
    
    set_transient("wvp_rate_limit_{$identifier}", $attempts + 1, 300); // 5 minutos
    return true;
}
```

### Sanitización de Archivos
```php
public function handle_file_upload($file) {
    $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('File type not allowed');
    }
    
    return $file;
}
```

## Internacionalización

### Preparación de Strings
```php
public function get_payment_method_title() {
    return __('Pago Móvil', 'wvp');
}

public function get_payment_description() {
    return _x('Pago mediante Pago Móvil para clientes venezolanos.', 'Payment method description', 'wvp');
}
```

### Pluralización
```php
public function get_items_count($count) {
    return sprintf(
        _n('%d item', '%d items', $count, 'wvp'),
        $count
    );
}
```

### Contextos
```php
public function get_button_text() {
    return _x('Pay', 'Button text', 'wvp');
}
```

## Debugging

### Debug Mode
```php
public function debug_log($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_message = $message;
        if ($data !== null) {
            $log_message .= ' | Data: ' . print_r($data, true);
        }
        error_log("WVP Debug: {$log_message}");
    }
}
```

### Debug Information
```php
public function get_debug_info() {
    return array(
        'plugin_version' => WVP_VERSION,
        'wordpress_version' => get_bloginfo('version'),
        'woocommerce_version' => WC()->version,
        'php_version' => PHP_VERSION,
        'bcv_rate' => WVP_BCV_Integrator::get_rate(),
        'igtf_rate' => get_option('wvp_igtf_rate'),
        'active_gateways' => $this->get_active_gateways()
    );
}
```

## Deployment

### Versionado
```php
// En el archivo principal
define('WVP_VERSION', '1.0.0');

// Verificar actualizaciones
if (version_compare(get_option('wvp_version'), WVP_VERSION, '<')) {
    $this->run_upgrade();
    update_option('wvp_version', WVP_VERSION);
}
```

### Migración de Datos
```php
public function run_upgrade() {
    $current_version = get_option('wvp_version', '0.0.0');
    
    if (version_compare($current_version, '1.0.0', '<')) {
        $this->upgrade_to_1_0_0();
    }
    
    if (version_compare($current_version, '1.1.0', '<')) {
        $this->upgrade_to_1_1_0();
    }
}
```

### Limpieza en Desinstalación
```php
// En uninstall.php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Limpiar opciones
delete_option('wvp_version');
delete_option('wvp_settings');

// Limpiar tablas
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wvp_logs");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wvp_stats");
```

## Mejores Prácticas

### 1. Código Limpio
- Funciones pequeñas y específicas
- Nombres descriptivos
- Comentarios cuando sea necesario
- Evitar código duplicado

### 2. Manejo de Recursos
- Liberar recursos cuando sea necesario
- Usar transients para datos temporales
- Limpiar caché periódicamente

### 3. Compatibilidad
- Probar con diferentes versiones de WordPress/WooCommerce
- Usar feature detection
- Mantener compatibilidad hacia atrás

### 4. Documentación
- Documentar funciones públicas
- Incluir ejemplos de uso
- Mantener changelog actualizado

## Conclusión

Esta guía de desarrollo proporciona las bases para trabajar efectivamente con el plugin WooCommerce Venezuela Pro. Siguiendo estos estándares y mejores prácticas, se asegura un código mantenible, seguro y eficiente.
