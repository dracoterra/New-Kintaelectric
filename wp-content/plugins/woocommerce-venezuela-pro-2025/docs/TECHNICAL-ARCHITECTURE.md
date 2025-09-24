# 🏗️ Arquitectura Técnica - WooCommerce Venezuela Suite

## Documento de Arquitectura del Sistema

Este documento describe la arquitectura técnica detallada del plugin WooCommerce Venezuela Suite, incluyendo patrones de diseño, estructura de clases, flujo de datos y mejores prácticas de implementación.

---

## 1. Patrones de Diseño Implementados

### Singleton Pattern
La clase principal `Woocommerce_Venezuela_Pro_2025` implementa el patrón Singleton para asegurar una única instancia del plugin en toda la aplicación.

```php
class Woocommerce_Venezuela_Pro_2025 {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### Factory Pattern
Para la creación de módulos y componentes, se implementa el patrón Factory que permite crear objetos sin especificar sus clases exactas.

### Observer Pattern
El sistema de hooks de WordPress implementa naturalmente el patrón Observer, permitiendo que los módulos se suscriban a eventos específicos.

---

## 2. Estructura de Clases

### Jerarquía Principal

```
Woocommerce_Venezuela_Pro_2025 (Singleton)
├── Woocommerce_Venezuela_Pro_2025_Loader
├── Woocommerce_Venezuela_Pro_2025_Admin
├── Woocommerce_Venezuela_Pro_2025_Public
├── Woocommerce_Venezuela_Pro_2025_Settings
└── Modules/
    ├── WCVS_Currency_Manager
    ├── WCVS_Payment_Gateways
    ├── WCVS_Tax_Manager
    └── WCVS_Shipping_Manager
```

### Clases de Módulos

#### WCVS_Currency_Manager
```php
class WCVS_Currency_Manager {
    private $bcv_integration;
    private $conversion_cache;
    
    public function get_exchange_rate()
    public function convert_price($amount, $from_currency, $to_currency)
    public function update_rates_cron()
    public function display_dual_prices()
}
```

#### WCVS_Payment_Gateways
```php
abstract class WCVS_Payment_Gateway_Base extends WC_Payment_Gateway {
    abstract public function process_payment($order_id);
    abstract public function validate_payment_data($data);
}

class WCVS_Gateway_PagoMovil extends WCVS_Payment_Gateway_Base {
    public function process_payment($order_id)
    public function validate_payment_data($data)
    public function generate_payment_instructions()
}
```

#### WCVS_Tax_Manager
```php
class WCVS_Tax_Manager {
    public function calculate_iva($amount)
    public function calculate_igtf($amount, $payment_method)
    public function add_custom_checkout_fields()
    public function validate_venezuelan_documents($document)
}
```

#### WCVS_Shipping_Manager
```php
abstract class WCVS_Shipping_Method_Base extends WC_Shipping_Method {
    abstract public function calculate_shipping($package);
    abstract public function get_rates_table();
}

class WCVS_Shipping_MRW extends WCVS_Shipping_Method_Base {
    public function calculate_shipping($package)
    public function get_rates_table()
    public function validate_destination($destination)
}
```

---

## 3. Sistema de Módulos

### Carga Dinámica de Módulos

```php
class WCVS_Module_Loader {
    private $active_modules = array();
    
    public function load_module($module_name) {
        if ($this->is_module_active($module_name)) {
            $module_class = "WCVS_{$module_name}";
            if (class_exists($module_class)) {
                $this->active_modules[$module_name] = new $module_class();
            }
        }
    }
    
    public function is_module_active($module_name) {
        return get_option("wcvs_module_{$module_name}_active", false);
    }
}
```

### Configuración de Módulos

Cada módulo tiene su propia página de configuración que se integra en el panel de administración de WordPress:

```php
class WCVS_Settings {
    public function add_settings_page() {
        add_options_page(
            'Venezuela Suite Settings',
            'Venezuela Suite',
            'manage_options',
            'wcvs-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function register_module_settings() {
        register_setting('wcvs_modules', 'wcvs_module_currency_active');
        register_setting('wcvs_modules', 'wcvs_module_payments_active');
        register_setting('wcvs_modules', 'wcvs_module_taxes_active');
        register_setting('wcvs_modules', 'wcvs_module_shipping_active');
    }
}
```

---

## 4. Integración con WooCommerce

### Hooks Principales Utilizados

#### Para Moneda
```php
// Cambiar moneda base
add_filter('woocommerce_currency', array($this, 'set_base_currency'));

// Mostrar precios duales
add_filter('woocommerce_price_html', array($this, 'display_dual_prices'), 10, 2);

// Convertir precios en checkout
add_action('woocommerce_checkout_process', array($this, 'convert_checkout_prices'));
```

#### Para Pasarelas de Pago
```php
// Registrar pasarelas personalizadas
add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));

// Validar datos de pago
add_action('woocommerce_checkout_process', array($this, 'validate_payment_data'));

// Procesar pagos
add_action('woocommerce_payment_complete', array($this, 'handle_payment_completion'));
```

#### Para Impuestos
```php
// Calcular impuestos venezolanos
add_action('woocommerce_calculate_totals', array($this, 'calculate_venezuelan_taxes'));

// Agregar campos de checkout
add_action('woocommerce_checkout_fields', array($this, 'add_venezuelan_fields'));

// Validar documentos
add_action('woocommerce_checkout_process', array($this, 'validate_venezuelan_documents'));
```

#### Para Envíos
```php
// Registrar métodos de envío
add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));

// Calcular costos de envío
add_action('woocommerce_shipping_init', array($this, 'init_shipping_methods'));

// Validar destinos
add_filter('woocommerce_shipping_packages', array($this, 'validate_shipping_destinations'));
```

---

## 5. Sistema de Cache y Performance

### Cache de Conversiones
```php
class WCVS_Cache_Manager {
    private $cache_group = 'wcvs_conversions';
    private $cache_expiry = 1800; // 30 minutos
    
    public function get_conversion_rate($from, $to) {
        $cache_key = "rate_{$from}_{$to}";
        $cached_rate = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $cached_rate) {
            $rate = $this->fetch_live_rate($from, $to);
            wp_cache_set($cache_key, $rate, $this->cache_group, $this->cache_expiry);
            return $rate;
        }
        
        return $cached_rate;
    }
}
```

### Optimización de Consultas
```php
class WCVS_Database_Optimizer {
    public function optimize_currency_queries() {
        // Usar transients para datos que cambian poco
        $rates = get_transient('wcvs_bcv_rates');
        
        if (false === $rates) {
            $rates = $this->fetch_bcv_rates();
            set_transient('wcvs_bcv_rates', $rates, HOUR_IN_SECONDS);
        }
        
        return $rates;
    }
}
```

---

## 6. Seguridad y Validación

### Sanitización de Datos
```php
class WCVS_Security_Manager {
    public function sanitize_payment_data($data) {
        return array(
            'reference' => sanitize_text_field($data['reference']),
            'amount' => floatval($data['amount']),
            'phone' => sanitize_text_field($data['phone']),
            'bank' => sanitize_text_field($data['bank'])
        );
    }
    
    public function validate_venezuelan_rif($rif) {
        // Validación específica para RIF venezolano
        $pattern = '/^[VEJPG]-?\d{8}-?\d$/';
        return preg_match($pattern, $rif);
    }
}
```

### Nonces y Verificación
```php
class WCVS_Nonce_Manager {
    public function verify_admin_action($nonce, $action) {
        return wp_verify_nonce($nonce, "wcvs_{$action}");
    }
    
    public function create_admin_nonce($action) {
        return wp_create_nonce("wcvs_{$action}");
    }
}
```

---

## 7. Internacionalización

### Preparación de Strings
```php
class WCVS_i18n {
    public function load_textdomain() {
        load_plugin_textdomain(
            'woocommerce-venezuela-pro-2025',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    public function get_localized_strings() {
        return array(
            'currency_selector' => __('Seleccionar Moneda', 'woocommerce-venezuela-pro-2025'),
            'payment_instructions' => __('Instrucciones de Pago', 'woocommerce-venezuela-pro-2025'),
            'shipping_calculator' => __('Calculadora de Envío', 'woocommerce-venezuela-pro-2025')
        );
    }
}
```

---

## 8. Logging y Debugging

### Sistema de Logs
```php
class WCVS_Logger {
    private $log_file;
    
    public function __construct() {
        $this->log_file = WC_LOG_DIR . 'wcvs-debug.log';
    }
    
    public function log($message, $level = 'info') {
        if (WP_DEBUG) {
            $timestamp = current_time('Y-m-d H:i:s');
            $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
            file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        }
    }
    
    public function log_payment_attempt($gateway, $order_id, $status) {
        $this->log("Payment attempt: Gateway={$gateway}, Order={$order_id}, Status={$status}");
    }
}
```

---

## 9. Testing y QA

### Estructura de Tests
```php
class WCVS_Test_Suite {
    public function test_currency_conversion() {
        $manager = new WCVS_Currency_Manager();
        $result = $manager->convert_price(100, 'USD', 'VES');
        
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }
    
    public function test_payment_gateway_validation() {
        $gateway = new WCVS_Gateway_PagoMovil();
        $valid_data = array(
            'reference' => '1234567890',
            'phone' => '+584121234567'
        );
        
        $this->assertTrue($gateway->validate_payment_data($valid_data));
    }
}
```

---

## 10. Deployment y Mantenimiento

### Versionado
```php
class WCVS_Version_Manager {
    const VERSION = '1.0.0';
    const DB_VERSION = '1.0.0';
    
    public function check_version() {
        $installed_version = get_option('wcvs_version', '0.0.0');
        
        if (version_compare($installed_version, self::VERSION, '<')) {
            $this->run_upgrade($installed_version, self::VERSION);
            update_option('wcvs_version', self::VERSION);
        }
    }
    
    private function run_upgrade($from_version, $to_version) {
        // Lógica de migración entre versiones
    }
}
```

### Backup y Restauración
```php
class WCVS_Backup_Manager {
    public function backup_settings() {
        $settings = array(
            'modules' => get_option('wcvs_modules'),
            'gateways' => get_option('wcvs_gateways'),
            'shipping' => get_option('wcvs_shipping')
        );
        
        update_option('wcvs_backup_' . time(), $settings);
    }
}
```

---

## 11. Monitoreo y Analytics

### Métricas de Performance
```php
class WCVS_Performance_Monitor {
    public function track_page_load_time($page) {
        $start_time = microtime(true);
        
        // Código de la página
        
        $load_time = microtime(true) - $start_time;
        $this->log_performance_metric($page, $load_time);
    }
    
    public function track_conversion_rate() {
        $total_orders = wc_get_orders(array('limit' => -1, 'return' => 'ids'));
        $converted_orders = wc_get_orders(array(
            'limit' => -1,
            'return' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_wcvs_currency_converted',
                    'value' => 'yes'
                )
            )
        ));
        
        $conversion_rate = count($converted_orders) / count($total_orders) * 100;
        update_option('wcvs_conversion_rate', $conversion_rate);
    }
}
```

---

*Este documento técnico será actualizado conforme evolucione la arquitectura del sistema.*
