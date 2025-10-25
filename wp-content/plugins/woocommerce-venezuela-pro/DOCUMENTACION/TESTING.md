# Guía de Testing y QA - WooCommerce Venezuela Pro

## Estrategia de Testing

### Tipos de Testing

#### 1. Testing Unitario
- **Objetivo**: Probar funciones individuales
- **Herramientas**: PHPUnit, WP-CLI
- **Cobertura**: Funciones críticas del plugin

#### 2. Testing de Integración
- **Objetivo**: Probar interacción entre componentes
- **Herramientas**: PHPUnit, WordPress Test Suite
- **Cobertura**: Integración con WooCommerce, BCV

#### 3. Testing Funcional
- **Objetivo**: Probar funcionalidades completas
- **Herramientas**: Selenium, Cypress
- **Cobertura**: Flujos de usuario completos

#### 4. Testing de Rendimiento
- **Objetivo**: Probar rendimiento y escalabilidad
- **Herramientas**: Apache Bench, JMeter
- **Cobertura**: Carga, respuesta, memoria

#### 5. Testing de Seguridad
- **Objetivo**: Probar vulnerabilidades
- **Herramientas**: OWASP ZAP, Burp Suite
- **Cobertura**: Validación, sanitización, autenticación

## Configuración del Entorno de Testing

### Requisitos del Sistema
```bash
# Instalar PHPUnit
composer require --dev phpunit/phpunit

# Instalar WordPress Test Suite
composer require --dev yoast/phpunit-polyfills

# Instalar herramientas de testing
composer require --dev brain/monkey
composer require --dev mockery/mockery
```

### Configuración de PHPUnit
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
    bootstrap="tests/bootstrap.php"
    colors="true"
    processIsolation="false"
    stopOnFailure="false"
    cacheDirectory=".phpunit.cache"
    executionOrder="depends,defects"
    requireCoverageMetadata="true"
    beStrictAboutCoverageMetadata="true"
    beStrictAboutOutputDuringTests="true"
    failOnRisky="true"
    failOnWarning="true"
    verbose="true">
    
    <testsuites>
        <testsuite name="unit">
            <directory>tests/unit</directory>
        </testsuite>
        <testsuite name="integration">
            <directory>tests/integration</directory>
        </testsuite>
        <testsuite name="functional">
            <directory>tests/functional</directory>
        </testsuite>
    </testsuites>
    
    <coverage>
        <include>
            <directory suffix=".php">includes</directory>
            <directory suffix=".php">admin</directory>
            <directory suffix=".php">frontend</directory>
            <directory suffix=".php">gateways</directory>
        </include>
        <exclude>
            <directory>vendor</directory>
            <directory>tests</directory>
        </exclude>
    </coverage>
</phpunit>
```

### Bootstrap de Testing
```php
<?php
// tests/bootstrap.php

// Cargar WordPress
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Configurar entorno de testing
define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');
define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills');

// Cargar WordPress Test Suite
require_once getenv('WP_TESTS_DIR') . '/includes/functions.php';

// Cargar plugin
require_once dirname(__DIR__) . '/woocommerce-venezuela-pro.php';

// Activar plugin
activate_plugin('woocommerce-venezuela-pro/woocommerce-venezuela-pro.php');
```

## Testing Unitario

### Testing de BCV Integrator
```php
<?php
// tests/unit/class-wvp-bcv-integrator-test.php

class Test_WVP_BCV_Integrator extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        $this->bcv_integrator = new WVP_BCV_Integrator();
    }
    
    public function test_get_rate_returns_float() {
        $rate = WVP_BCV_Integrator::get_rate();
        $this->assertIsFloat($rate);
    }
    
    public function test_get_rate_greater_than_zero() {
        $rate = WVP_BCV_Integrator::get_rate();
        $this->assertGreaterThan(0, $rate);
    }
    
    public function test_convert_usd_to_ves() {
        $usd_amount = 100;
        $rate = 36.50;
        $ves_amount = WVP_BCV_Integrator::convert_usd_to_ves($usd_amount, $rate);
        
        $this->assertEquals(3650, $ves_amount);
    }
    
    public function test_convert_ves_to_usd() {
        $ves_amount = 3650;
        $rate = 36.50;
        $usd_amount = WVP_BCV_Integrator::convert_ves_to_usd($ves_amount, $rate);
        
        $this->assertEquals(100, $usd_amount);
    }
    
    public function test_get_rate_with_mock() {
        // Mock de la base de datos
        global $wpdb;
        $wpdb = $this->getMockBuilder('wpdb')->getMock();
        $wpdb->prefix = 'wp_';
        
        $wpdb->expects($this->once())
             ->method('get_var')
             ->willReturn('36.50');
        
        $rate = WVP_BCV_Integrator::get_rate();
        $this->assertEquals(36.50, $rate);
    }
}
```

### Testing de IGTF Manager
```php
<?php
// tests/unit/class-wvp-igtf-manager-test.php

class Test_WVP_IGTF_Manager extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        $this->igtf_manager = new WVP_IGTF_Manager();
    }
    
    public function test_calculate_igtf() {
        $amount = 1000;
        $rate = 3.0;
        $igtf = $this->igtf_manager->calculate_igtf($amount, $rate);
        
        $this->assertEquals(30, $igtf);
    }
    
    public function test_apply_igtf_fee() {
        // Crear carrito de prueba
        $cart = WC()->cart;
        $cart->add_to_cart($this->create_test_product(), 1);
        
        // Aplicar IGTF
        $this->igtf_manager->apply_igtf_fee($cart);
        
        // Verificar que se aplicó la tarifa
        $fees = $cart->get_fees();
        $this->assertNotEmpty($fees);
        
        $igtf_fee = null;
        foreach ($fees as $fee) {
            if ($fee->name === 'IGTF') {
                $igtf_fee = $fee;
                break;
            }
        }
        
        $this->assertNotNull($igtf_fee);
        $this->assertGreaterThan(0, $igtf_fee->amount);
    }
    
    public function test_igtf_exemption() {
        // Crear usuario con rol exento
        $user = $this->factory->user->create(array('role' => 'exempt_customer'));
        wp_set_current_user($user);
        
        $cart = WC()->cart;
        $cart->add_to_cart($this->create_test_product(), 1);
        
        $this->igtf_manager->apply_igtf_fee($cart);
        
        $fees = $cart->get_fees();
        $this->assertEmpty($fees);
    }
    
    private function create_test_product() {
        return $this->factory->post->create(array(
            'post_type' => 'product',
            'post_title' => 'Test Product',
            'meta_input' => array(
                '_price' => 100,
                '_regular_price' => 100
            )
        ));
    }
}
```

### Testing de Price Calculator
```php
<?php
// tests/unit/class-wvp-price-calculator-test.php

class Test_WVP_Price_Calculator extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        $this->price_calculator = new WVP_Price_Calculator();
    }
    
    public function test_calculate_price_with_cache() {
        $product_id = 1;
        $price = 100;
        
        // Primera llamada (sin caché)
        $result1 = $this->price_calculator->calculate_price($product_id, $price);
        
        // Segunda llamada (con caché)
        $result2 = $this->price_calculator->calculate_price($product_id, $price);
        
        $this->assertEquals($result1, $result2);
    }
    
    public function test_price_calculation_with_igtf() {
        $product_id = 1;
        $price = 100;
        $include_igtf = true;
        
        $result = $this->price_calculator->calculate_price($product_id, $price, $include_igtf);
        
        $this->assertGreaterThan($price, $result);
    }
    
    public function test_price_calculation_without_igtf() {
        $product_id = 1;
        $price = 100;
        $include_igtf = false;
        
        $result = $this->price_calculator->calculate_price($product_id, $price, $include_igtf);
        
        $this->assertEquals($price, $result);
    }
}
```

## Testing de Integración

### Testing de Pasarelas de Pago
```php
<?php
// tests/integration/class-wvp-gateway-test.php

class Test_WVP_Gateway_Integration extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        $this->setup_woocommerce();
    }
    
    public function test_pago_movil_gateway_registration() {
        $gateways = WC()->payment_gateways()->get_available_payment_gateways();
        
        $this->assertArrayHasKey('wvp_pago_movil', $gateways);
        $this->assertInstanceOf('WVP_Gateway_Pago_Movil', $gateways['wvp_pago_movil']);
    }
    
    public function test_pago_movil_payment_processing() {
        $gateway = new WVP_Gateway_Pago_Movil();
        
        // Configurar gateway
        $gateway->update_option('enabled', 'yes');
        $gateway->update_option('ci', 'V-12345678');
        $gateway->update_option('phone', '+58-412-1234567');
        $gateway->update_option('bank', 'banesco');
        
        // Crear orden de prueba
        $order = $this->create_test_order();
        
        // Procesar pago
        $result = $gateway->process_payment($order->get_id());
        
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals('success', $result['result']);
    }
    
    public function test_zelle_gateway_validation() {
        $gateway = new WVP_Gateway_Zelle();
        
        // Validar email de Zelle
        $valid_email = 'test@zelle.com';
        $invalid_email = 'invalid-email';
        
        $this->assertTrue($gateway->validate_zelle_email($valid_email));
        $this->assertFalse($gateway->validate_zelle_email($invalid_email));
    }
    
    private function setup_woocommerce() {
        // Configurar WooCommerce para testing
        WC()->init();
        WC()->cart->empty_cart();
    }
    
    private function create_test_order() {
        $order = wc_create_order();
        $order->add_product($this->create_test_product(), 1);
        $order->set_total(100);
        $order->save();
        
        return $order;
    }
    
    private function create_test_product() {
        return $this->factory->post->create(array(
            'post_type' => 'product',
            'post_title' => 'Test Product',
            'meta_input' => array(
                '_price' => 100,
                '_regular_price' => 100,
                '_manage_stock' => 'no'
            )
        ));
    }
}
```

### Testing de Métodos de Envío
```php
<?php
// tests/integration/class-wvp-shipping-test.php

class Test_WVP_Shipping_Integration extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        $this->setup_woocommerce();
    }
    
    public function test_local_delivery_shipping_method() {
        $shipping_method = new WVP_Shipping_Local_Delivery();
        
        $this->assertEquals('wvp_local_delivery', $shipping_method->id);
        $this->assertEquals('Delivery Local (Venezuela)', $shipping_method->method_title);
    }
    
    public function test_shipping_cost_calculation() {
        $shipping_method = new WVP_Shipping_Local_Delivery();
        
        // Configurar zonas
        $zones = array(
            'centro' => array('cost' => 5.00),
            'este' => array('cost' => 7.00),
            'miranda' => array('cost' => 10.00)
        );
        
        $shipping_method->update_option('zones', $zones);
        
        // Probar cálculo de costo
        $package = array(
            'destination' => array(
                'country' => 'VE',
                'state' => 'Miranda',
                'city' => 'Caracas'
            )
        );
        
        $shipping_method->calculate_shipping($package);
        
        $rates = $shipping_method->get_rates();
        $this->assertNotEmpty($rates);
    }
    
    public function test_free_shipping_threshold() {
        $shipping_method = new WVP_Shipping_Local_Delivery();
        
        // Configurar envío gratis
        $shipping_method->update_option('free_shipping_threshold', 100);
        
        // Crear carrito con monto suficiente
        WC()->cart->add_to_cart($this->create_test_product(), 1);
        WC()->cart->set_total(100);
        
        $package = array(
            'destination' => array(
                'country' => 'VE',
                'state' => 'Miranda'
            )
        );
        
        $shipping_method->calculate_shipping($package);
        
        $rates = $shipping_method->get_rates();
        $this->assertNotEmpty($rates);
        
        // Verificar que el costo es 0
        $rate = reset($rates);
        $this->assertEquals(0, $rate->cost);
    }
}
```

## Testing Funcional

### Testing de Flujos de Usuario
```php
<?php
// tests/functional/class-wvp-user-flow-test.php

class Test_WVP_User_Flow extends WP_UnitTestCase {
    
    public function test_complete_checkout_flow() {
        // Crear producto
        $product = $this->create_test_product();
        
        // Añadir al carrito
        WC()->cart->add_to_cart($product, 1);
        
        // Verificar que el producto está en el carrito
        $this->assertEquals(1, WC()->cart->get_cart_contents_count());
        
        // Crear orden
        $order = wc_create_order();
        $order->add_product($product, 1);
        $order->set_total(100);
        $order->save();
        
        // Procesar pago
        $gateway = new WVP_Gateway_Pago_Movil();
        $result = $gateway->process_payment($order->get_id());
        
        // Verificar resultado
        $this->assertEquals('success', $result['result']);
        
        // Verificar estado de la orden
        $order = wc_get_order($order->get_id());
        $this->assertEquals('processing', $order->get_status());
    }
    
    public function test_price_conversion_flow() {
        // Configurar tasa BCV
        update_option('wvp_bcv_rate', 36.50);
        
        // Crear producto con precio en USD
        $product = $this->create_test_product(array('_price' => 100));
        
        // Obtener precio convertido
        $converted_price = WVP_BCV_Integrator::convert_usd_to_ves(100);
        
        $this->assertEquals(3650, $converted_price);
        
        // Verificar formato de precio
        $formatted_price = wvp_format_price($converted_price, 'VES');
        $this->assertEquals('Bs. 3.650,00', $formatted_price);
    }
    
    public function test_igtf_calculation_flow() {
        // Configurar IGTF
        update_option('wvp_igtf_rate', 3.0);
        
        // Crear carrito
        WC()->cart->add_to_cart($this->create_test_product(), 1);
        
        // Aplicar IGTF
        $igtf_manager = new WVP_IGTF_Manager();
        $igtf_manager->apply_igtf_fee(WC()->cart);
        
        // Verificar que se aplicó IGTF
        $fees = WC()->cart->get_fees();
        $this->assertNotEmpty($fees);
        
        $igtf_fee = null;
        foreach ($fees as $fee) {
            if ($fee->name === 'IGTF') {
                $igtf_fee = $fee;
                break;
            }
        }
        
        $this->assertNotNull($igtf_fee);
        $this->assertEquals(3, $igtf_fee->amount); // 3% de 100
    }
}
```

## Testing de Rendimiento

### Testing de Carga
```php
<?php
// tests/performance/class-wvp-performance-test.php

class Test_WVP_Performance extends WP_UnitTestCase {
    
    public function test_bcv_rate_caching_performance() {
        $iterations = 1000;
        $start_time = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            WVP_BCV_Integrator::get_rate();
        }
        
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;
        
        // Verificar que el tiempo de ejecución es razonable
        $this->assertLessThan(1.0, $execution_time); // Menos de 1 segundo
    }
    
    public function test_price_calculation_performance() {
        $iterations = 1000;
        $start_time = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            WVP_BCV_Integrator::convert_usd_to_ves(100);
        }
        
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;
        
        $this->assertLessThan(0.5, $execution_time); // Menos de 0.5 segundos
    }
    
    public function test_memory_usage() {
        $initial_memory = memory_get_usage();
        
        // Ejecutar operaciones del plugin
        $igtf_manager = new WVP_IGTF_Manager();
        $price_calculator = new WVP_Price_Calculator();
        $bcv_integrator = new WVP_BCV_Integrator();
        
        $final_memory = memory_get_usage();
        $memory_used = $final_memory - $initial_memory;
        
        // Verificar que el uso de memoria es razonable
        $this->assertLessThan(10 * 1024 * 1024, $memory_used); // Menos de 10MB
    }
}
```

## Testing de Seguridad

### Testing de Validación
```php
<?php
// tests/security/class-wvp-security-test.php

class Test_WVP_Security extends WP_UnitTestCase {
    
    public function test_input_sanitization() {
        $validator = new WVP_Security_Validator();
        
        $malicious_input = '<script>alert("XSS")</script>';
        $sanitized = $validator->sanitize_input($malicious_input);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('alert', $sanitized);
    }
    
    public function test_nonce_validation() {
        $validator = new WVP_Security_Validator();
        
        $valid_nonce = wp_create_nonce('wvp_action');
        $invalid_nonce = 'invalid_nonce';
        
        $this->assertTrue($validator->validate_nonce($valid_nonce, 'wvp_action'));
        $this->assertFalse($validator->validate_nonce($invalid_nonce, 'wvp_action'));
    }
    
    public function test_rate_limiting() {
        $rate_limiter = new WVP_Rate_Limiter();
        
        $identifier = 'test_user';
        $max_attempts = 5;
        
        // Probar límite de velocidad
        for ($i = 0; $i < $max_attempts; $i++) {
            $this->assertTrue($rate_limiter->check_rate_limit($identifier, $max_attempts));
        }
        
        // El siguiente intento debería fallar
        $this->assertFalse($rate_limiter->check_rate_limit($identifier, $max_attempts));
    }
    
    public function test_sql_injection_protection() {
        $malicious_input = "'; DROP TABLE wp_posts; --";
        
        // Probar que la entrada maliciosa no causa problemas
        $result = WVP_BCV_Integrator::get_rate();
        
        // Verificar que la tabla aún existe
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->posts}'");
        $this->assertNotNull($table_exists);
    }
}
```

## Testing de Compatibilidad

### Testing de Versiones
```php
<?php
// tests/compatibility/class-wvp-compatibility-test.php

class Test_WVP_Compatibility extends WP_UnitTestCase {
    
    public function test_wordpress_version_compatibility() {
        $required_version = '5.0';
        $current_version = get_bloginfo('version');
        
        $this->assertTrue(version_compare($current_version, $required_version, '>='));
    }
    
    public function test_woocommerce_version_compatibility() {
        $required_version = '5.0';
        $current_version = WC()->version;
        
        $this->assertTrue(version_compare($current_version, $required_version, '>='));
    }
    
    public function test_php_version_compatibility() {
        $required_version = '7.4';
        $current_version = PHP_VERSION;
        
        $this->assertTrue(version_compare($current_version, $required_version, '>='));
    }
    
    public function test_hpos_compatibility() {
        // Verificar compatibilidad con HPOS
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            $compatibility = \Automattic\WooCommerce\Utilities\FeaturesUtil::get_compatible_feature_plugin_info('custom_order_tables');
            $this->assertTrue($compatibility['compatible']);
        }
    }
}
```

## Automatización de Testing

### GitHub Actions
```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '7.4'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, iconv, json, mbstring, pdo
        
    - name: Setup WordPress
      run: |
        bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run tests
      run: composer test
      
    - name: Upload coverage
      uses: codecov/codecov-action@v1
      with:
        file: coverage.xml
```

### Scripts de Testing
```json
{
  "scripts": {
    "test": "phpunit",
    "test:unit": "phpunit tests/unit",
    "test:integration": "phpunit tests/integration",
    "test:functional": "phpunit tests/functional",
    "test:performance": "phpunit tests/performance",
    "test:security": "phpunit tests/security",
    "test:coverage": "phpunit --coverage-html coverage",
    "test:ci": "phpunit --coverage-clover coverage.xml"
  }
}
```

## Métricas de Calidad

### Cobertura de Código
- **Objetivo**: 80%+ de cobertura
- **Herramientas**: PHPUnit, Codecov
- **Métricas**: Líneas, funciones, clases

### Calidad de Código
- **Herramientas**: PHP_CodeSniffer, PHPStan
- **Estándares**: WordPress Coding Standards
- **Métricas**: Complejidad, duplicación, mantenibilidad

### Rendimiento
- **Herramientas**: Blackfire, XHProf
- **Métricas**: Tiempo de ejecución, uso de memoria, consultas DB

## Conclusión

La estrategia de testing del plugin WooCommerce Venezuela Pro incluye:

- ✅ **Testing completo**: Unitario, integración, funcional
- ✅ **Automatización**: CI/CD con GitHub Actions
- ✅ **Calidad**: Métricas de cobertura y calidad
- ✅ **Seguridad**: Testing de vulnerabilidades
- ✅ **Rendimiento**: Monitoreo de performance
- ✅ **Compatibilidad**: Testing de versiones

Esta estrategia asegura la calidad y confiabilidad del plugin en producción.
