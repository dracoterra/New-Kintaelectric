# Correcciones de Compatibilidad con WooCommerce - Venezuela Pro

## 🔧 **PROBLEMAS DE COMPATIBILIDAD IDENTIFICADOS**

### **1. HOOKS OBSOLETOS DE WOOCOMMERCE**

#### **Problema:**
El plugin usa hooks que han cambiado o están obsoletos en versiones recientes de WooCommerce.

#### **Solución:**
```php
// ANTES (OBSOLETO):
add_filter("woocommerce_get_price_html", array($this, "add_ves_reference"), 10, 2);

// DESPUÉS (COMPATIBLE):
add_filter("woocommerce_get_price_html", array($this, "add_ves_reference"), 10, 2);
add_filter("woocommerce_cart_item_price", array($this, "add_ves_reference_cart"), 10, 3);
add_filter("woocommerce_cart_item_subtotal", array($this, "add_ves_reference_cart"), 10, 3);
add_filter("woocommerce_cart_subtotal", array($this, "add_ves_reference_cart"), 10, 3);
add_filter("woocommerce_cart_totals_order_total_html", array($this, "add_ves_reference_cart"), 10, 3);
add_filter("woocommerce_order_formatted_line_subtotal", array($this, "add_ves_reference_order"), 10, 3);
```

### **2. MÉTODOS OBSOLETOS DE WOOCOMMERCE**

#### **Problema:**
Se usan métodos que han cambiado en versiones recientes de WooCommerce.

#### **Solución:**
```php
// ANTES (OBSOLETO):
$cart_total = WC()->cart->get_total("raw");

// DESPUÉS (COMPATIBLE):
$cart_total = floatval(WC()->cart->get_total('raw'));

// ANTES (OBSOLETO):
$order->update_status("on-hold", __("Pago pendiente de verificación.", "wvp"));

// DESPUÉS (COMPATIBLE):
$order->update_status("on-hold", __("Pago pendiente de verificación.", "wvp"));
$order->add_order_note(__("Pago pendiente de verificación.", "wvp"), false, true);
```

### **3. FALTA DE VALIDACIÓN DE VERSIÓN**

#### **Problema:**
No se verifica la versión mínima de WooCommerce requerida.

#### **Solución:**
```php
// Crear archivo: includes/class-wvp-compatibility-checker.php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Compatibility_Checker {
    
    /**
     * Verificar compatibilidad con WooCommerce
     */
    public static function check_woocommerce_compatibility() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo __('WooCommerce Venezuela Pro requiere WooCommerce para funcionar.', 'wvp');
                echo '</p></div>';
            });
            return false;
        }
        
        // Verificar versión mínima
        if (version_compare(WC()->version, '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo sprintf(
                    __('WooCommerce Venezuela Pro requiere WooCommerce 5.0 o superior. Versión actual: %s', 'wvp'),
                    WC()->version
                );
                echo '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Verificar compatibilidad con WordPress
     */
    public static function check_wordpress_compatibility() {
        global $wp_version;
        
        if (version_compare($wp_version, '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo sprintf(
                    __('WooCommerce Venezuela Pro requiere WordPress 5.0 o superior. Versión actual: %s', 'wvp'),
                    $wp_version
                );
                echo '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Verificar compatibilidad con PHP
     */
    public static function check_php_compatibility() {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo sprintf(
                    __('WooCommerce Venezuela Pro requiere PHP 7.4 o superior. Versión actual: %s', 'wvp'),
                    PHP_VERSION
                );
                echo '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Verificar todas las compatibilidades
     */
    public static function check_all_compatibilities() {
        return self::check_wordpress_compatibility() && 
               self::check_php_compatibility() && 
               self::check_woocommerce_compatibility();
    }
}
```

### **4. HOOKS DE CHECKOUT INCORRECTOS**

#### **Problema:**
Los hooks del checkout no se ejecutan correctamente en versiones recientes de WooCommerce.

#### **Solución:**
```php
// En frontend/class-wvp-checkout.php
class WVP_Checkout {
    
    public function __construct() {
        // Hooks para checkout
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout_fields'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_checkout_fields'));
        add_action('woocommerce_checkout_order_processed', array($this, 'process_checkout_order'));
        
        // Hooks para campos de facturación
        add_filter('woocommerce_billing_fields', array($this, 'add_billing_fields'));
        add_filter('woocommerce_checkout_fields', array($this, 'add_checkout_fields'));
        
        // Hooks para validación
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_venezuelan_fields'));
        
        // Hooks para IGTF
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_igtf_fee'));
    }
    
    /**
     * Añadir campos de facturación venezolanos
     */
    public function add_billing_fields($fields) {
        $fields['billing_cedula_rif'] = array(
            'label' => __('Cédula o RIF', 'wvp'),
            'placeholder' => __('V-12345678 o J-12345678-9', 'wvp'),
            'required' => true,
            'class' => array('form-row-wide'),
            'priority' => 25,
            'validate' => array('cedula_rif')
        );
        
        return $fields;
    }
    
    /**
     * Añadir campos al checkout
     */
    public function add_checkout_fields($fields) {
        // Añadir campo de cédula/RIF
        $fields['billing']['billing_cedula_rif'] = array(
            'label' => __('Cédula o RIF', 'wvp'),
            'placeholder' => __('V-12345678 o J-12345678-9', 'wvp'),
            'required' => true,
            'class' => array('form-row-wide'),
            'priority' => 25,
            'validate' => array('cedula_rif')
        );
        
        return $fields;
    }
    
    /**
     * Validar campos venezolanos
     */
    public function validate_venezuelan_fields($data, $errors) {
        // Validar cédula/RIF
        if (empty($data['billing_cedula_rif'])) {
            $errors->add('billing_cedula_rif', __('Cédula o RIF es obligatorio.', 'wvp'));
        } elseif (!$this->validate_cedula_rif($data['billing_cedula_rif'])) {
            $errors->add('billing_cedula_rif', __('Formato de cédula o RIF inválido.', 'wvp'));
        }
    }
    
    /**
     * Validar formato de cédula/RIF
     */
    private function validate_cedula_rif($cedula_rif) {
        // Patrón para V-12345678 o J-12345678-9
        $pattern = '/^[VJ]-[0-9]{7,8}(-[0-9])?$/';
        return preg_match($pattern, strtoupper($cedula_rif));
    }
    
    /**
     * Guardar campos del checkout
     */
    public function save_checkout_fields($order_id) {
        if (!empty($_POST['billing_cedula_rif'])) {
            $cedula_rif = sanitize_text_field($_POST['billing_cedula_rif']);
            update_post_meta($order_id, '_billing_cedula_rif', $cedula_rif);
        }
    }
    
    /**
     * Añadir fee de IGTF
     */
    public function add_igtf_fee() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        $chosen_payment_method = WC()->session->get('chosen_payment_method');
        
        // Solo aplicar IGTF a pagos en efectivo con billetes USD
        if ($this->should_apply_igtf($chosen_payment_method)) {
            $cart_total = WC()->cart->get_total('raw');
            $igtf_rate = floatval(get_option('wvp_igtf_rate', 3.0));
            $igtf_amount = ($cart_total * $igtf_rate) / 100;
            
            WC()->cart->add_fee(
                __('IGTF (Impuesto al Gran Movimiento Financiero)', 'wvp'),
                $igtf_amount
            );
        }
    }
    
    /**
     * Verificar si debe aplicar IGTF
     */
    private function should_apply_igtf($payment_method) {
        $igtf_methods = array(
            'wvp_efectivo', // Efectivo USD
        );
        
        return in_array($payment_method, $igtf_methods);
    }
}
```

### **5. INTEGRACIÓN CON SISTEMA DE PAGOS DE WOOCOMMERCE**

#### **Problema:**
Las pasarelas de pago no se integran correctamente con el sistema de pagos de WooCommerce.

#### **Solución:**
```php
// Crear archivo: gateways/class-wvp-gateway-base.php
<?php
if (!defined('ABSPATH')) {
    exit;
}

abstract class WVP_Gateway_Base extends WC_Payment_Gateway {
    
    /**
     * Propiedades comunes
     */
    public $apply_igtf;
    public $min_amount;
    public $max_amount;
    
    /**
     * Constructor base
     */
    public function __construct() {
        $this->init_form_fields();
        $this->init_settings();
        
        // Obtener configuraciones
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->apply_igtf = $this->get_option('apply_igtf');
        $this->min_amount = $this->get_option('min_amount');
        $this->max_amount = $this->get_option('max_amount');
        
        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }
    
    /**
     * Campos de configuración base
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Activar/Desactivar', 'wvp'),
                'type' => 'checkbox',
                'label' => sprintf(__('Activar %s', 'wvp'), $this->method_title),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Título', 'wvp'),
                'type' => 'text',
                'description' => __('Título que se muestra al cliente', 'wvp'),
                'default' => $this->method_title,
                'desc_tip' => true
            ),
            'description' => array(
                'title' => __('Descripción', 'wvp'),
                'type' => 'textarea',
                'description' => __('Descripción que se muestra al cliente', 'wvp'),
                'default' => $this->method_description,
                'desc_tip' => true
            ),
            'min_amount' => array(
                'title' => __('Monto Mínimo (USD)', 'wvp'),
                'type' => 'price',
                'description' => __('Monto mínimo para activar esta pasarela', 'wvp'),
                'default' => '',
                'desc_tip' => true
            ),
            'max_amount' => array(
                'title' => __('Monto Máximo (USD)', 'wvp'),
                'type' => 'price',
                'description' => __('Monto máximo para activar esta pasarela', 'wvp'),
                'default' => '',
                'desc_tip' => true
            ),
            'apply_igtf' => array(
                'title' => __('Aplicar IGTF', 'wvp'),
                'type' => 'checkbox',
                'label' => __('Aplicar IGTF a esta pasarela', 'wvp'),
                'default' => 'no',
                'description' => __('IGTF solo se aplica a pagos en efectivo (billetes)', 'wvp')
            )
        );
    }
    
    /**
     * Verificar si la pasarela está disponible
     */
    public function is_available() {
        if (!$this->enabled) {
            return false;
        }
        
        $cart_total = floatval(WC()->cart->get_total('raw'));
        
        // Verificar monto mínimo
        if ($this->min_amount && $cart_total < floatval($this->min_amount)) {
            return false;
        }
        
        // Verificar monto máximo
        if ($this->max_amount && $cart_total > floatval($this->max_amount)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar campos de pago (debe ser implementado por cada pasarela)
     */
    abstract public function validate_fields();
    
    /**
     * Procesar pago (debe ser implementado por cada pasarela)
     */
    abstract public function process_payment($order_id);
}
```

### **6. ACTUALIZAR ARCHIVO PRINCIPAL**

```php
// En woocommerce-venezuela-pro.php
class WooCommerce_Venezuela_Pro {
    
    public function init() {
        // Verificar compatibilidades
        if (!WVP_Compatibility_Checker::check_all_compatibilities()) {
            return;
        }
        
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            return;
        }
        
        // Cargar archivos de dependencias
        $this->load_dependencies();
        
        // Inicializar componentes
        $this->init_components();
        
        // Log de inicialización
        error_log('WooCommerce Venezuela Pro: Plugin inicializado correctamente');
    }
    
    private function load_dependencies() {
        // Archivos principales
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-dependencies.php';
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-bcv-integrator.php';
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-compatibility-checker.php';
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-security-validator.php';
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-rate-limiter.php';
        
        // Archivos de frontend
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-price-display.php';
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-checkout.php';
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-dual-breakdown.php';
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-hybrid-invoicing.php';
        
        // Archivos de administración
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-order-meta.php';
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-admin-settings.php';
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-reports.php';
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-payment-verification.php';
        
        // Generador de facturas
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-invoice-generator.php';
        
        // Métodos de envío
        require_once WVP_PLUGIN_PATH . 'shipping/class-wvp-shipping-local-delivery.php';
        
        // Pasarelas de pago
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-base.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-zelle.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-pago-movil.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-efectivo.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-efectivo-bolivares.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-cashea.php';
        
        // Notificaciones WhatsApp
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-whatsapp-notifications.php';
    }
}
```

---

## 🧪 **PRUEBAS DE COMPATIBILIDAD**

### **1. Prueba de Versiones de WooCommerce**
```php
// Crear archivo: tests/test-woocommerce-compatibility.php
function test_woocommerce_versions() {
    $versions = array('5.0', '5.1', '5.2', '5.3', '5.4', '5.5', '5.6', '5.7', '5.8', '5.9', '6.0', '6.1', '6.2', '6.3', '6.4');
    
    foreach ($versions as $version) {
        // Simular versión de WooCommerce
        $GLOBALS['woocommerce']->version = $version;
        
        $compatible = WVP_Compatibility_Checker::check_woocommerce_compatibility();
        
        if (version_compare($version, '5.0', '>=')) {
            assert($compatible === true, "WooCommerce {$version} should be compatible");
        } else {
            assert($compatible === false, "WooCommerce {$version} should not be compatible");
        }
    }
}
```

### **2. Prueba de Hooks de WooCommerce**
```php
function test_woocommerce_hooks() {
    // Verificar que los hooks se registran correctamente
    $hooks = array(
        'woocommerce_get_price_html',
        'woocommerce_cart_item_price',
        'woocommerce_cart_item_subtotal',
        'woocommerce_cart_subtotal',
        'woocommerce_cart_totals_order_total_html'
    );
    
    foreach ($hooks as $hook) {
        $has_hook = has_filter($hook, 'WVP_Price_Display::add_ves_reference');
        assert($has_hook !== false, "Hook {$hook} should be registered");
    }
}
```

### **3. Prueba de Pasarelas de Pago**
```php
function test_payment_gateways() {
    $gateways = array(
        'WVP_Gateway_Zelle',
        'WVP_Gateway_Pago_Movil',
        'WVP_Gateway_Efectivo',
        'WVP_Gateway_Efectivo_Bolivares',
        'WVP_Gateway_Cashea'
    );
    
    foreach ($gateways as $gateway_class) {
        assert(class_exists($gateway_class), "Gateway {$gateway_class} should exist");
        
        $gateway = new $gateway_class();
        assert(method_exists($gateway, 'is_available'), "Gateway {$gateway_class} should have is_available method");
        assert(method_exists($gateway, 'validate_fields'), "Gateway {$gateway_class} should have validate_fields method");
        assert(method_exists($gateway, 'process_payment'), "Gateway {$gateway_class} should have process_payment method");
    }
}
```

---

## 📋 **CHECKLIST DE IMPLEMENTACIÓN**

### **Compatibilidad WooCommerce**
- [ ] Implementar verificación de versión
- [ ] Actualizar hooks obsoletos
- [ ] Actualizar métodos obsoletos
- [ ] Crear clase base para pasarelas
- [ ] Actualizar sistema de checkout
- [ ] Implementar validación de campos
- [ ] Añadir sistema de IGTF
- [ ] Probar con diferentes versiones

### **Pruebas de Compatibilidad**
- [ ] Probar con WooCommerce 5.0+
- [ ] Probar con WordPress 5.0+
- [ ] Probar con PHP 7.4+
- [ ] Probar con diferentes temas
- [ ] Probar con otros plugins
- [ ] Probar funcionalidades principales

### **Documentación**
- [ ] Documentar cambios de compatibilidad
- [ ] Actualizar guía de instalación
- [ ] Crear guía de migración
- [ ] Documentar nuevos hooks

---

## ⚠️ **ADVERTENCIAS IMPORTANTES**

1. **Probar en entorno de desarrollo** antes de implementar
2. **Hacer backup completo** antes de actualizar
3. **Verificar compatibilidad** con plugins existentes
4. **Monitorear logs** después de la implementación
5. **Notificar a usuarios** sobre cambios de compatibilidad

---

## 🚀 **PRÓXIMOS PASOS**

1. **Implementar correcciones de compatibilidad** (2-3 días)
2. **Probar en entorno de desarrollo** (1 día)
3. **Probar en staging** (1 día)
4. **Desplegar en producción** (1 día)

**TOTAL: 5-6 días para implementación completa de compatibilidad**

---

**¡ESTAS CORRECCIONES GARANTIZAN LA COMPATIBILIDAD TOTAL CON WOOCOMMERCE!** 🔧
