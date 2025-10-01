# WooCommerce Venezuela Suite - Guía de Desarrollo

## 📋 Información General

Esta guía está dirigida a desarrolladores que quieran contribuir, extender o personalizar el plugin WooCommerce Venezuela Suite.

## 🏗️ Arquitectura del Plugin

### Estructura de Directorios

```
woocommerce-venezuela-suite/
├── 📄 woocommerce-venezuela-suite.php    # Archivo principal del plugin
├── 📄 README.md                          # Documentación principal
├── 📄 DEVELOPMENT-PLAN.md                # Plan de desarrollo
├── 📄 MODULES.md                         # Documentación de módulos
├── 📄 INSTALLATION.md                    # Guía de instalación
├── 📄 CONFIGURATION.md                   # Guía de configuración
├── 📄 API.md                             # Documentación de API
├── 📄 DEVELOPMENT.md                     # Esta guía
├── 📄 CHANGELOG.md                       # Historial de cambios
├── 📄 LICENSE                            # Licencia del plugin
├── 📁 core/                              # Módulo core (obligatorio)
│   ├── 📄 class-wvs-core.php
│   ├── 📄 class-wvs-module-manager.php
│   ├── 📄 class-wvs-database.php
│   ├── 📄 class-wvs-security.php
│   ├── 📄 class-wvs-performance.php
│   ├── 📄 class-wvs-logger.php
│   └── 📄 class-wvs-config-manager.php
├── 📁 modules/                           # Módulos opcionales
│   ├── 📁 currency/
│   ├── 📁 payments/
│   ├── 📁 shipping/
│   ├── 📁 invoicing/
│   ├── 📁 communication/
│   ├── 📁 reports/
│   └── 📁 widgets/
├── 📁 assets/                            # Recursos estáticos
│   ├── 📁 css/
│   ├── 📁 js/
│   ├── 📁 images/
│   └── 📁 fonts/
├── 📁 languages/                         # Archivos de traducción
│   ├── 📄 woocommerce-venezuela-suite.pot
│   ├── 📄 woocommerce-venezuela-suite-es_ES.po
│   └── 📄 woocommerce-venezuela-suite-es_ES.mo
├── 📁 admin/                             # Funcionalidad administrativa
│   ├── 📄 class-wvs-admin.php
│   ├── 📄 class-wvs-admin-settings.php
│   └── 📄 class-wvs-admin-dashboard.php
├── 📁 public/                            # Funcionalidad pública
│   ├── 📄 class-wvs-public.php
│   └── 📄 class-wvs-public-assets.php
├── 📁 includes/                          # Clases compartidas
│   ├── 📄 class-wvs-loader.php
│   ├── 📄 class-wvs-i18n.php
│   └── 📄 class-wvs-activator.php
├── 📁 tests/                             # Tests unitarios
│   ├── 📄 test-wvs-core.php
│   ├── 📄 test-wvs-currency.php
│   └── 📄 test-wvs-payments.php
└── 📁 docs/                              # Documentación adicional
    ├── 📄 API.md
    ├── 📄 HOOKS.md
    └── 📄 EXAMPLES.md
```

### Patrón de Arquitectura

El plugin sigue el patrón **Modular Architecture** con los siguientes principios:

1. **Core Module**: Funcionalidad base obligatoria
2. **Feature Modules**: Módulos opcionales independientes
3. **Dependency Injection**: Inyección de dependencias entre módulos
4. **Event-Driven**: Comunicación mediante hooks y eventos
5. **Lazy Loading**: Carga de módulos solo cuando se necesitan

## 🔧 Convenciones de Código

### Nomenclatura

#### Clases
```php
// Prefijo: WVS_
// Formato: PascalCase
class WVS_Core {}
class WVS_Currency_Manager {}
class WVS_Payment_Gateway_Zelle {}
```

#### Funciones
```php
// Prefijo: wvs_
// Formato: snake_case
function wvs_get_option($name, $default = null) {}
function wvs_set_module_active($module, $active) {}
function wvs_convert_usd_to_ves($amount) {}
```

#### Constantes
```php
// Prefijo: WVS_
// Formato: UPPER_CASE
define('WVS_VERSION', '1.0.0');
define('WVS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WVS_PLUGIN_URL', plugin_dir_url(__FILE__));
```

#### Variables
```php
// Formato: snake_case
$module_name = 'currency';
$is_module_active = true;
$conversion_rate = 36.0;
```

### Estructura de Clases

#### Clase Base
```php
<?php
/**
 * Clase base para módulos del plugin
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVS_Module_Base {
    
    /**
     * Nombre del módulo
     * 
     * @var string
     */
    protected $module_name;
    
    /**
     * Versión del módulo
     * 
     * @var string
     */
    protected $version;
    
    /**
     * Estado del módulo
     * 
     * @var bool
     */
    protected $is_active;
    
    /**
     * Constructor
     * 
     * @param string $module_name Nombre del módulo
     * @param string $version Versión del módulo
     */
    public function __construct($module_name, $version) {
        $this->module_name = $module_name;
        $this->version = $version;
        $this->is_active = false;
        
        $this->init();
    }
    
    /**
     * Inicializar el módulo
     */
    protected function init() {
        // Implementar en clases hijas
    }
    
    /**
     * Activar el módulo
     */
    public function activate() {
        $this->is_active = true;
        do_action('wvs_module_activated', $this->module_name);
    }
    
    /**
     * Desactivar el módulo
     */
    public function deactivate() {
        $this->is_active = false;
        do_action('wvs_module_deactivated', $this->module_name);
    }
    
    /**
     * Verificar si el módulo está activo
     * 
     * @return bool
     */
    public function is_active() {
        return $this->is_active;
    }
    
    /**
     * Obtener nombre del módulo
     * 
     * @return string
     */
    public function get_module_name() {
        return $this->module_name;
    }
    
    /**
     * Obtener versión del módulo
     * 
     * @return string
     */
    public function get_version() {
        return $this->version;
    }
}
```

#### Clase de Módulo Específico
```php
<?php
/**
 * Módulo de gestión de monedas
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

class WVS_Currency_Manager extends WVS_Module_Base {
    
    /**
     * Instancia del integrador BCV
     * 
     * @var WVS_BCV_Integration
     */
    private $bcv_integration;
    
    /**
     * Calculadora de precios
     * 
     * @var WVS_Price_Calculator
     */
    private $price_calculator;
    
    /**
     * Inicializar el módulo
     */
    protected function init() {
        // Cargar dependencias
        $this->load_dependencies();
        
        // Inicializar componentes
        $this->init_components();
        
        // Registrar hooks
        $this->register_hooks();
    }
    
    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        require_once WVS_PLUGIN_DIR . 'modules/currency/class-wvs-bcv-integration.php';
        require_once WVS_PLUGIN_DIR . 'modules/currency/class-wvs-price-calculator.php';
    }
    
    /**
     * Inicializar componentes
     */
    private function init_components() {
        $this->bcv_integration = new WVS_BCV_Integration();
        $this->price_calculator = new WVS_Price_Calculator();
    }
    
    /**
     * Registrar hooks
     */
    private function register_hooks() {
        add_action('woocommerce_product_get_price', array($this, 'convert_product_price'), 10, 2);
        add_filter('woocommerce_currency_symbol', array($this, 'custom_currency_symbol'), 10, 2);
    }
    
    /**
     * Convertir precio del producto
     * 
     * @param float $price Precio original
     * @param WC_Product $product Producto
     * @return float Precio convertido
     */
    public function convert_product_price($price, $product) {
        if (!$this->is_active()) {
            return $price;
        }
        
        return $this->price_calculator->convert_usd_to_ves($price);
    }
    
    /**
     * Personalizar símbolo de moneda
     * 
     * @param string $currency_symbol Símbolo original
     * @param string $currency Código de moneda
     * @return string Símbolo personalizado
     */
    public function custom_currency_symbol($currency_symbol, $currency) {
        if ($currency === 'VES') {
            return 'Bs.';
        }
        
        return $currency_symbol;
    }
}
```

### Documentación PHPDoc

#### Estándar de Documentación
```php
/**
 * Descripción breve de la función/clase
 * 
 * Descripción detallada si es necesaria. Puede incluir múltiples párrafos
 * y explicar el propósito, funcionamiento y casos de uso.
 * 
 * @since 1.0.0
 * @param string $param_name Descripción del parámetro
 * @param int $param_count Descripción del parámetro con tipo
 * @return mixed Descripción del valor de retorno
 * @throws Exception Descripción de excepciones que puede lanzar
 * 
 * @example
 * $result = wvs_convert_price(100.00, 'USD', 'VES');
 * // Retorna: 3600.00
 */
function wvs_convert_price($amount, $from_currency, $to_currency) {
    // Implementación
}
```

#### Documentación de Clases
```php
/**
 * Gestor principal de conversiones de moneda
 * 
 * Esta clase maneja todas las conversiones de moneda del plugin,
 * incluyendo integración con BCV, cálculo de IGTF y cache de conversiones.
 * 
 * @package WooCommerce_Venezuela_Suite
 * @subpackage Currency
 * @since 1.0.0
 * 
 * @example
 * $manager = new WVS_Currency_Manager();
 * $converted_price = $manager->convert_price(100.00, 'USD', 'VES');
 */
class WVS_Currency_Manager {
    // Implementación
}
```

## 🔌 Sistema de Hooks

### Hooks del Plugin

#### Hooks de Inicialización
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

#### Hooks de Moneda
```php
// Conversión de precio
$converted_price = apply_filters('wvs_convert_price', $original_price, $from_currency, $to_currency);

// Actualización de tasa BCV
do_action('wvs_bcv_rate_updated', $new_rate, $old_rate);

// Cálculo de IGTF
$igtf_amount = apply_filters('wvs_calculate_igtf', $amount, $rate);
```

#### Hooks de Pagos
```php
// Procesamiento de pago
$result = apply_filters('wvs_process_payment', $order_id, $gateway, $payment_data);

// Verificación de pago
do_action('wvs_payment_verified', $order_id, $payment_data);

// Notificación de pago
do_action('wvs_payment_notification', $order_id, $status, $gateway);
```

#### Hooks de Envíos
```php
// Cálculo de costo de envío
$shipping_cost = apply_filters('wvs_calculate_shipping', $package, $method, $destination);

// Actualización de tracking
do_action('wvs_shipping_tracking_updated', $order_id, $tracking_data);

// Confirmación de entrega
do_action('wvs_shipping_delivered', $order_id, $delivery_data);
```

### Crear Hooks Personalizados

#### Definir Hook
```php
/**
 * Hook personalizado para conversión de precios
 */
function wvs_custom_price_conversion($price, $currency) {
    // Aplicar filtro personalizado
    $converted_price = apply_filters('wvs_custom_price_conversion', $price, $currency);
    
    // Disparar acción
    do_action('wvs_price_converted', $price, $converted_price, $currency);
    
    return $converted_price;
}
```

#### Usar Hook
```php
/**
 * Usar hook personalizado
 */
add_filter('wvs_custom_price_conversion', function($price, $currency) {
    // Lógica personalizada
    if ($currency === 'VES') {
        return $price * 1.03; // Agregar 3% de margen
    }
    
    return $price;
}, 10, 2);
```

## 🗄️ Gestión de Base de Datos

### Estructura de Tablas

#### Tabla Principal de Logs
```sql
CREATE TABLE IF NOT EXISTS wp_wvs_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    timestamp datetime NOT NULL,
    level varchar(20) NOT NULL,
    message text NOT NULL,
    context longtext,
    user_id bigint(20),
    ip_address varchar(45),
    url varchar(255),
    PRIMARY KEY (id),
    KEY timestamp (timestamp),
    KEY level (level),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Tabla de Configuración de Módulos
```sql
CREATE TABLE IF NOT EXISTS wp_wvs_modules (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    module_name varchar(100) NOT NULL,
    is_active tinyint(1) NOT NULL DEFAULT 0,
    version varchar(20) NOT NULL,
    settings longtext,
    created_at datetime NOT NULL,
    updated_at datetime NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY module_name (module_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Clase de Base de Datos

#### Clase Base
```php
<?php
/**
 * Clase base para gestión de base de datos
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

class WVS_Database {
    
    /**
     * Instancia global de WordPress Database
     * 
     * @var wpdb
     */
    protected $wpdb;
    
    /**
     * Prefijo de tablas
     * 
     * @var string
     */
    protected $table_prefix;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix . 'wvs_';
    }
    
    /**
     * Crear tabla
     * 
     * @param string $table_name Nombre de la tabla
     * @param array $columns Columnas de la tabla
     * @return bool True si se creó correctamente
     */
    public function create_table($table_name, $columns) {
        $full_table_name = $this->table_prefix . $table_name;
        
        $sql = "CREATE TABLE IF NOT EXISTS $full_table_name (";
        $sql .= implode(', ', $columns);
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        return dbDelta($sql);
    }
    
    /**
     * Insertar registro
     * 
     * @param string $table_name Nombre de la tabla
     * @param array $data Datos a insertar
     * @return int|false ID del registro insertado o false en caso de error
     */
    public function insert($table_name, $data) {
        $full_table_name = $this->table_prefix . $table_name;
        
        $result = $this->wpdb->insert($full_table_name, $data);
        
        if ($result === false) {
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Actualizar registro
     * 
     * @param string $table_name Nombre de la tabla
     * @param array $data Datos a actualizar
     * @param array $where Condiciones WHERE
     * @return int|false Número de filas afectadas o false en caso de error
     */
    public function update($table_name, $data, $where) {
        $full_table_name = $this->table_prefix . $table_name;
        
        return $this->wpdb->update($full_table_name, $data, $where);
    }
    
    /**
     * Eliminar registro
     * 
     * @param string $table_name Nombre de la tabla
     * @param array $where Condiciones WHERE
     * @return int|false Número de filas afectadas o false en caso de error
     */
    public function delete($table_name, $where) {
        $full_table_name = $this->table_prefix . $table_name;
        
        return $this->wpdb->delete($full_table_name, $where);
    }
    
    /**
     * Obtener registros
     * 
     * @param string $table_name Nombre de la tabla
     * @param array $where Condiciones WHERE
     * @param string $order_by Campo para ordenar
     * @param int $limit Límite de registros
     * @return array Registros encontrados
     */
    public function get($table_name, $where = array(), $order_by = '', $limit = 0) {
        $full_table_name = $this->table_prefix . $table_name;
        
        $sql = "SELECT * FROM $full_table_name";
        
        if (!empty($where)) {
            $conditions = array();
            foreach ($where as $key => $value) {
                $conditions[] = $this->wpdb->prepare("$key = %s", $value);
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if (!empty($order_by)) {
            $sql .= " ORDER BY $order_by";
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        return $this->wpdb->get_results($sql);
    }
}
```

#### Clase Específica de Módulo
```php
<?php
/**
 * Clase de base de datos para módulo de monedas
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

class WVS_Currency_Database extends WVS_Database {
    
    /**
     * Tabla de conversiones
     */
    const TABLE_CONVERSIONS = 'currency_conversions';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->create_tables();
    }
    
    /**
     * Crear tablas necesarias
     */
    private function create_tables() {
        $this->create_conversions_table();
    }
    
    /**
     * Crear tabla de conversiones
     */
    private function create_conversions_table() {
        $columns = array(
            'id bigint(20) NOT NULL AUTO_INCREMENT',
            'from_currency varchar(3) NOT NULL',
            'to_currency varchar(3) NOT NULL',
            'rate decimal(10,4) NOT NULL',
            'amount decimal(10,2) NOT NULL',
            'converted_amount decimal(10,2) NOT NULL',
            'timestamp datetime NOT NULL',
            'user_id bigint(20)',
            'PRIMARY KEY (id)',
            'KEY from_currency (from_currency)',
            'KEY to_currency (to_currency)',
            'KEY timestamp (timestamp)'
        );
        
        $this->create_table(self::TABLE_CONVERSIONS, $columns);
    }
    
    /**
     * Guardar conversión
     * 
     * @param string $from_currency Moneda origen
     * @param string $to_currency Moneda destino
     * @param float $rate Tasa de cambio
     * @param float $amount Cantidad original
     * @param float $converted_amount Cantidad convertida
     * @param int $user_id ID del usuario
     * @return int|false ID del registro o false en caso de error
     */
    public function save_conversion($from_currency, $to_currency, $rate, $amount, $converted_amount, $user_id = null) {
        $data = array(
            'from_currency' => $from_currency,
            'to_currency' => $to_currency,
            'rate' => $rate,
            'amount' => $amount,
            'converted_amount' => $converted_amount,
            'timestamp' => current_time('mysql'),
            'user_id' => $user_id
        );
        
        return $this->insert(self::TABLE_CONVERSIONS, $data);
    }
    
    /**
     * Obtener conversiones recientes
     * 
     * @param int $limit Límite de registros
     * @return array Conversiones encontradas
     */
    public function get_recent_conversions($limit = 10) {
        return $this->get(self::TABLE_CONVERSIONS, array(), 'timestamp DESC', $limit);
    }
    
    /**
     * Obtener estadísticas de conversión
     * 
     * @param string $from_currency Moneda origen
     * @param string $to_currency Moneda destino
     * @param int $days Días a considerar
     * @return array Estadísticas
     */
    public function get_conversion_stats($from_currency, $to_currency, $days = 30) {
        $full_table_name = $this->table_prefix . self::TABLE_CONVERSIONS;
        
        $sql = $this->wpdb->prepare("
            SELECT 
                COUNT(*) as total_conversions,
                AVG(rate) as avg_rate,
                MIN(rate) as min_rate,
                MAX(rate) as max_rate,
                SUM(amount) as total_amount,
                SUM(converted_amount) as total_converted_amount
            FROM $full_table_name 
            WHERE from_currency = %s 
            AND to_currency = %s 
            AND timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $from_currency, $to_currency, $days);
        
        return $this->wpdb->get_row($sql, ARRAY_A);
    }
}
```

## 🧪 Testing

### Estructura de Tests

#### Test Base
```php
<?php
/**
 * Clase base para tests
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

class WVS_Test_Base extends WP_UnitTestCase {
    
    /**
     * Configuración inicial
     */
    public function setUp(): void {
        parent::setUp();
        
        // Activar plugin
        $this->activate_plugin();
        
        // Configurar opciones por defecto
        $this->set_default_options();
    }
    
    /**
     * Limpieza después de cada test
     */
    public function tearDown(): void {
        // Limpiar datos de test
        $this->cleanup_test_data();
        
        parent::tearDown();
    }
    
    /**
     * Activar plugin
     */
    protected function activate_plugin() {
        // Implementar activación del plugin
    }
    
    /**
     * Configurar opciones por defecto
     */
    protected function set_default_options() {
        // Implementar configuración por defecto
    }
    
    /**
     * Limpiar datos de test
     */
    protected function cleanup_test_data() {
        // Implementar limpieza de datos
    }
}
```

#### Test de Módulo
```php
<?php
/**
 * Tests para módulo de monedas
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

class WVS_Currency_Test extends WVS_Test_Base {
    
    /**
     * Test de conversión básica
     */
    public function test_basic_conversion() {
        $manager = new WVS_Currency_Manager();
        
        $usd_amount = 100.00;
        $expected_ves = 3600.00; // Asumiendo tasa de 36
        
        $converted = $manager->convert_usd_to_ves($usd_amount);
        
        $this->assertEquals($expected_ves, $converted);
    }
    
    /**
     * Test de cálculo de IGTF
     */
    public function test_igtf_calculation() {
        $calculator = new WVS_Price_Calculator();
        
        $amount = 100.00;
        $igtf_rate = 3.0;
        $expected_igtf = 3.00;
        
        $igtf = $calculator->calculate_igtf($amount, $igtf_rate);
        
        $this->assertEquals($expected_igtf, $igtf);
    }
    
    /**
     * Test de integración BCV
     */
    public function test_bcv_integration() {
        $integration = new WVS_BCV_Integration();
        
        $rate = $integration->get_current_rate();
        
        $this->assertIsFloat($rate);
        $this->assertGreaterThan(0, $rate);
    }
}
```

### Ejecutar Tests

#### Comando de Test
```bash
# Ejecutar todos los tests
phpunit tests/

# Ejecutar tests específicos
phpunit tests/test-wvs-currency.php

# Ejecutar tests con cobertura
phpunit --coverage-html coverage/ tests/
```

#### Configuración de PHPUnit
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="tests/bootstrap.php"
    colors="true"
    processIsolation="false"
    stopOnFailure="false"
    syntaxCheck="false">
    
    <testsuites>
        <testsuite name="WVS Tests">
            <directory>tests/</directory>
        </testsuite>
    </testsuites>
    
    <filter>
        <whitelist>
            <directory suffix=".php">core/</directory>
            <directory suffix=".php">modules/</directory>
        </whitelist>
    </filter>
</phpunit>
```

## 📦 Empaquetado y Distribución

### Estructura de Release

#### Archivos de Release
```
woocommerce-venezuela-suite-1.0.0/
├── 📄 woocommerce-venezuela-suite.php
├── 📄 README.md
├── 📄 CHANGELOG.md
├── 📄 LICENSE
├── 📁 core/
├── 📁 modules/
├── 📁 assets/
├── 📁 languages/
├── 📁 admin/
├── 📁 public/
└── 📁 includes/
```

#### Script de Build
```bash
#!/bin/bash
# Script de build para release

VERSION="1.0.0"
PLUGIN_NAME="woocommerce-venezuela-suite"
RELEASE_DIR="releases"
BUILD_DIR="build"

# Crear directorio de build
mkdir -p $BUILD_DIR

# Copiar archivos necesarios
cp -r core/ $BUILD_DIR/
cp -r modules/ $BUILD_DIR/
cp -r assets/ $BUILD_DIR/
cp -r languages/ $BUILD_DIR/
cp -r admin/ $BUILD_DIR/
cp -r public/ $BUILD_DIR/
cp -r includes/ $BUILD_DIR/

# Copiar archivos principales
cp woocommerce-venezuela-suite.php $BUILD_DIR/
cp README.md $BUILD_DIR/
cp CHANGELOG.md $BUILD_DIR/
cp LICENSE $BUILD_DIR/

# Crear archivo ZIP
cd $BUILD_DIR
zip -r ../$RELEASE_DIR/$PLUGIN_NAME-$VERSION.zip .
cd ..

# Limpiar directorio de build
rm -rf $BUILD_DIR

echo "Release $VERSION creado en $RELEASE_DIR/$PLUGIN_NAME-$VERSION.zip"
```

### Versionado

#### Semantic Versioning
```
MAJOR.MINOR.PATCH
1.0.0
```

- **MAJOR**: Cambios incompatibles con versiones anteriores
- **MINOR**: Nueva funcionalidad compatible con versiones anteriores
- **PATCH**: Corrección de bugs compatible con versiones anteriores

#### Changelog
```markdown
# Changelog

## [1.0.0] - 2025-01-27

### Added
- Módulo Core con gestión de módulos
- Módulo Currency con integración BCV
- Módulo Payments con métodos locales
- Módulo Shipping con envíos nacionales
- Módulo Invoicing con facturación híbrida
- Módulo Communication con WhatsApp
- Módulo Reports con analytics
- Módulo Widgets con componentes especializados

### Changed
- Migración completa desde WooCommerce Venezuela Pro
- Arquitectura modular implementada
- Performance mejorado en 60%
- Seguridad reforzada

### Fixed
- Corrección de bugs de conversión de moneda
- Mejora en cálculo de IGTF
- Optimización de consultas de base de datos
```

## 🔧 Herramientas de Desarrollo

### Comandos WP-CLI

#### Comando Personalizado
```php
<?php
/**
 * Comando WP-CLI personalizado
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

if (defined('WP_CLI') && WP_CLI) {
    
    /**
     * Comando para gestionar módulos
     */
    class WVS_CLI_Command extends WP_CLI_Command {
        
        /**
         * Activar módulo
         * 
         * @param array $args Argumentos
         * @param array $assoc_args Argumentos asociativos
         */
        public function activate_module($args, $assoc_args) {
            $module_name = $args[0];
            
            if (wvs_set_module_active($module_name, true)) {
                WP_CLI::success("Módulo $module_name activado correctamente");
            } else {
                WP_CLI::error("Error al activar módulo $module_name");
            }
        }
        
        /**
         * Desactivar módulo
         * 
         * @param array $args Argumentos
         * @param array $assoc_args Argumentos asociativos
         */
        public function deactivate_module($args, $assoc_args) {
            $module_name = $args[0];
            
            if (wvs_set_module_active($module_name, false)) {
                WP_CLI::success("Módulo $module_name desactivado correctamente");
            } else {
                WP_CLI::error("Error al desactivar módulo $module_name");
            }
        }
        
        /**
         * Listar módulos
         * 
         * @param array $args Argumentos
         * @param array $assoc_args Argumentos asociativos
         */
        public function list_modules($args, $assoc_args) {
            $modules = wvs_get_all_modules();
            
            $table_data = array();
            foreach ($modules as $module) {
                $table_data[] = array(
                    'name' => $module['name'],
                    'active' => $module['active'] ? 'Sí' : 'No',
                    'version' => $module['version']
                );
            }
            
            WP_CLI\Utils\format_items('table', $table_data, array('name', 'active', 'version'));
        }
        
        /**
         * Verificar configuración
         * 
         * @param array $args Argumentos
         * @param array $assoc_args Argumentos asociativos
         */
        public function verify_config($args, $assoc_args) {
            $config_status = wvs_verify_configuration();
            
            if ($config_status['valid']) {
                WP_CLI::success('Configuración válida');
            } else {
                WP_CLI::error('Errores encontrados: ' . implode(', ', $config_status['errors']));
            }
        }
    }
    
    WP_CLI::add_command('wvs', 'WVS_CLI_Command');
}
```

#### Uso de Comandos
```bash
# Activar módulo
wp wvs activate_module currency

# Desactivar módulo
wp wvs deactivate_module payments

# Listar módulos
wp wvs list_modules

# Verificar configuración
wp wvs verify_config
```

### Debugging

#### Configuración de Debug
```php
// En wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('WVS_DEBUG', true);
define('WVS_LOG_LEVEL', 'debug');
```

#### Sistema de Logs
```php
<?php
/**
 * Sistema de logs del plugin
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

class WVS_Logger {
    
    /**
     * Niveles de log
     */
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    
    /**
     * Escribir log
     * 
     * @param string $level Nivel de log
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public static function log($level, $message, $context = array()) {
        if (!defined('WVS_DEBUG') || !WVS_DEBUG) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'url' => $_SERVER['REQUEST_URI'] ?? ''
        );
        
        // Escribir a archivo de log
        self::write_to_file($log_entry);
        
        // Escribir a base de datos
        self::write_to_database($log_entry);
    }
    
    /**
     * Escribir a archivo
     * 
     * @param array $log_entry Entrada de log
     */
    private static function write_to_file($log_entry) {
        $log_file = WP_CONTENT_DIR . '/uploads/wvs-logs/debug.log';
        
        // Crear directorio si no existe
        $log_dir = dirname($log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        $log_line = sprintf(
            "[%s] %s: %s %s\n",
            $log_entry['timestamp'],
            strtoupper($log_entry['level']),
            $log_entry['message'],
            !empty($log_entry['context']) ? json_encode($log_entry['context']) : ''
        );
        
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Escribir a base de datos
     * 
     * @param array $log_entry Entrada de log
     */
    private static function write_to_database($log_entry) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wvs_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => $log_entry['timestamp'],
                'level' => $log_entry['level'],
                'message' => $log_entry['message'],
                'context' => json_encode($log_entry['context']),
                'user_id' => $log_entry['user_id'],
                'ip_address' => $log_entry['ip_address'],
                'url' => $log_entry['url']
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );
    }
    
    /**
     * Métodos de conveniencia
     */
    public static function debug($message, $context = array()) {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }
    
    public static function info($message, $context = array()) {
        self::log(self::LEVEL_INFO, $message, $context);
    }
    
    public static function warning($message, $context = array()) {
        self::log(self::LEVEL_WARNING, $message, $context);
    }
    
    public static function error($message, $context = array()) {
        self::log(self::LEVEL_ERROR, $message, $context);
    }
}
```

## 📚 Recursos Adicionales

### Documentación Externa

- **[WordPress Plugin API](https://developer.wordpress.org/plugins/)**
- **[WooCommerce Developer Documentation](https://woocommerce.com/developers/)**
- **[PHP Standards Recommendations](https://www.php-fig.org/psr/)**
- **[WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)**

### Herramientas Recomendadas

- **IDE**: PhpStorm, VS Code
- **Version Control**: Git
- **Testing**: PHPUnit
- **Code Quality**: PHPCS, PHPMD
- **Documentation**: PHPDoc

### Comunidad

- **GitHub**: [Repositorio del plugin](https://github.com/kinta-electric/woocommerce-venezuela-suite)
- **Discord**: [Servidor de la comunidad](https://discord.gg/kinta-electric)
- **Email**: desarrollo@kinta-electric.com

---

**Última actualización**: 2025-01-27  
**Versión**: 1.0.0
