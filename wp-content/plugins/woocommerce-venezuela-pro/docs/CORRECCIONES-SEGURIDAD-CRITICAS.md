# Correcciones de Seguridad Críticas - WooCommerce Venezuela Pro

## 🚨 **VULNERABILIDADES CRÍTICAS IDENTIFICADAS**

### **1. FALTA DE VALIDACIÓN CSRF EN PASARELAS DE PAGO**

#### **Problema:**
Las pasarelas de pago no verifican nonces CSRF, lo que permite ataques de falsificación de solicitudes entre sitios.

#### **Archivos Afectados:**
- `gateways/class-wvp-gateway-zelle.php`
- `gateways/class-wvp-gateway-pago-movil.php`
- `gateways/class-wvp-gateway-efectivo.php`
- `gateways/class-wvp-gateway-efectivo-bolivares.php`
- `gateways/class-wvp-gateway-cashea.php`

#### **Solución Inmediata:**
```php
// En cada método validate_fields() de las pasarelas
public function validate_fields() {
    // 1. Verificar nonce CSRF
    if (!wp_verify_nonce($_POST['woocommerce-process-checkout-nonce'], 'woocommerce-process_checkout')) {
        wc_add_notice(__("Error de seguridad. Intente nuevamente.", "wvp"), "error");
        return false;
    }
    
    // 2. Sanitizar datos inmediatamente
    $confirmation = sanitize_text_field($_POST[$this->id . '-confirmation'] ?? '');
    
    // 3. Validar formato de confirmación
    if (empty($confirmation) || !preg_match('/^[A-Z0-9\-]{6,20}$/', $confirmation)) {
        wc_add_notice(__("Número de confirmación inválido. Debe contener entre 6-20 caracteres alfanuméricos.", "wvp"), "error");
        return false;
    }
    
    return true;
}
```

### **2. ACCESO DIRECTO A $_POST SIN SANITIZACIÓN**

#### **Problema:**
Se accede a `$_POST` antes de sanitizar, permitiendo inyección de código malicioso.

#### **Solución Inmediata:**
```php
// ANTES (VULNERABLE):
$confirmation = $_POST[$this->id . '-confirmation'] ?? '';

// DESPUÉS (SEGURO):
$confirmation = sanitize_text_field($_POST[$this->id . '-confirmation'] ?? '');
```

### **3. FALTA DE VALIDACIÓN DE PERMISOS**

#### **Problema:**
No se verifica si el usuario tiene permisos para realizar operaciones críticas.

#### **Solución Inmediata:**
```php
// En cada método process_payment()
public function process_payment($order_id) {
    // 1. Verificar permisos del usuario
    if (!current_user_can('read')) {
        wc_add_notice(__("No tiene permisos para realizar esta acción.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // 2. Verificar que el pedido existe y está en estado correcto
    $order = wc_get_order($order_id);
    if (!$order || $order->get_status() !== 'pending') {
        wc_add_notice(__("Este pedido ya fue procesado o no existe.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // ... resto del código
}
```

### **4. FALTA DE RATE LIMITING**

#### **Problema:**
No hay límites en el número de intentos de pago, permitiendo ataques de fuerza bruta.

#### **Solución Inmediata:**
```php
// Crear archivo: includes/class-wvp-rate-limiter.php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Rate_Limiter {
    
    /**
     * Verificar límite de intentos
     */
    public static function check_rate_limit($user_id, $action, $max_attempts = 5, $time_window = 300) {
        $key = "wvp_rate_limit_{$action}_{$user_id}";
        $attempts = get_transient($key) ?: 0;
        
        if ($attempts >= $max_attempts) {
            return false;
        }
        
        set_transient($key, $attempts + 1, $time_window);
        return true;
    }
    
    /**
     * Limpiar límites de un usuario
     */
    public static function clear_rate_limit($user_id, $action) {
        $key = "wvp_rate_limit_{$action}_{$user_id}";
        delete_transient($key);
    }
}
```

### **5. FALTA DE VALIDACIÓN DE ESTADO DEL PEDIDO**

#### **Problema:**
No se verifica si el pedido ya fue procesado, permitiendo procesamiento duplicado.

#### **Solución Inmediata:**
```php
// En cada método process_payment()
public function process_payment($order_id) {
    $order = wc_get_order($order_id);
    
    // Verificar que el pedido existe
    if (!$order) {
        wc_add_notice(__("Pedido no encontrado.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // Verificar que el pedido está en estado pendiente
    if ($order->get_status() !== 'pending') {
        wc_add_notice(__("Este pedido ya fue procesado.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // Verificar que el pedido pertenece al usuario actual
    if ($order->get_customer_id() !== get_current_user_id() && !current_user_can('manage_woocommerce')) {
        wc_add_notice(__("No tiene permisos para procesar este pedido.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // ... resto del código
}
```

---

## 🔧 **IMPLEMENTACIÓN INMEDIATA**

### **PASO 1: Crear Clase de Validación de Seguridad**

```php
// Crear archivo: includes/class-wvp-security-validator.php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Security_Validator {
    
    /**
     * Validar nonce CSRF
     */
    public static function validate_nonce($nonce, $action) {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Sanitizar datos de entrada
     */
    public static function sanitize_input($data, $type = 'text') {
        switch ($type) {
            case 'text':
                return sanitize_text_field($data);
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            case 'int':
                return intval($data);
            case 'float':
                return floatval($data);
            default:
                return sanitize_text_field($data);
        }
    }
    
    /**
     * Validar formato de confirmación
     */
    public static function validate_confirmation($confirmation) {
        if (empty($confirmation)) {
            return false;
        }
        
        // Permitir solo caracteres alfanuméricos y guiones, 6-20 caracteres
        return preg_match('/^[A-Z0-9\-]{6,20}$/', $confirmation);
    }
    
    /**
     * Validar permisos del usuario
     */
    public static function validate_user_permissions($capability = 'read') {
        return current_user_can($capability);
    }
    
    /**
     * Validar estado del pedido
     */
    public static function validate_order_status($order, $expected_status = 'pending') {
        if (!$order) {
            return false;
        }
        
        return $order->get_status() === $expected_status;
    }
}
```

### **PASO 2: Actualizar Todas las Pasarelas de Pago**

#### **Ejemplo para Zelle:**
```php
// En gateways/class-wvp-gateway-zelle.php
public function validate_fields() {
    // 1. Verificar nonce CSRF
    if (!WVP_Security_Validator::validate_nonce($_POST['woocommerce-process-checkout-nonce'], 'woocommerce-process_checkout')) {
        wc_add_notice(__("Error de seguridad. Intente nuevamente.", "wvp"), "error");
        return false;
    }
    
    // 2. Sanitizar y validar datos
    $confirmation = WVP_Security_Validator::sanitize_input($_POST[$this->id . '-confirmation'] ?? '');
    
    if (!WVP_Security_Validator::validate_confirmation($confirmation)) {
        wc_add_notice(__("Número de confirmación inválido. Debe contener entre 6-20 caracteres alfanuméricos.", "wvp"), "error");
        return false;
    }
    
    return true;
}

public function process_payment($order_id) {
    // 1. Verificar permisos
    if (!WVP_Security_Validator::validate_user_permissions()) {
        wc_add_notice(__("No tiene permisos para realizar esta acción.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // 2. Verificar rate limiting
    if (!WVP_Rate_Limiter::check_rate_limit(get_current_user_id(), 'payment_attempt')) {
        wc_add_notice(__("Demasiados intentos de pago. Intente más tarde.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // 3. Obtener y validar pedido
    $order = wc_get_order($order_id);
    if (!WVP_Security_Validator::validate_order_status($order)) {
        wc_add_notice(__("Este pedido ya fue procesado o no existe.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
    
    // 4. Validar campos
    if (!$this->validate_fields()) {
        return array("result" => "fail", "redirect" => "");
    }
    
    // 5. Procesar pago con manejo de errores
    try {
        $confirmation = WVP_Security_Validator::sanitize_input($_POST[$this->id . '-confirmation']);
        
        // Guardar información del pago
        $order->update_meta_data("_payment_confirmation", $confirmation);
        $order->update_meta_data("_payment_method_title", $this->title);
        $order->update_meta_data("_payment_type", "zelle");
        
        // Marcar como pendiente de pago
        $order->update_status("on-hold", __("Pago pendiente de verificación.", "wvp"));
        
        // Reducir stock
        $order->reduce_order_stock();
        
        // Limpiar carrito
        WC()->cart->empty_cart();
        
        // Guardar pedido
        $order->save();
        
        // Limpiar rate limit en caso de éxito
        WVP_Rate_Limiter::clear_rate_limit(get_current_user_id(), 'payment_attempt');
        
        return array(
            "result" => "success",
            "redirect" => $this->get_return_url($order)
        );
        
    } catch (Exception $e) {
        error_log('WVP Error: ' . $e->getMessage());
        wc_add_notice(__("Error al procesar el pago. Intente nuevamente.", "wvp"), "error");
        return array("result" => "fail", "redirect" => "");
    }
}
```

### **PASO 3: Actualizar Archivo Principal**

```php
// En woocommerce-venezuela-pro.php
private function load_dependencies() {
    // Archivos principales
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-dependencies.php';
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-bcv-integrator.php';
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-security-validator.php'; // NUEVO
    require_once WVP_PLUGIN_PATH . 'includes/class-wvp-rate-limiter.php'; // NUEVO
    
    // ... resto de archivos
}
```

---

## 🧪 **PRUEBAS DE SEGURIDAD**

### **1. Prueba de CSRF**
```php
// Crear archivo: tests/test-csrf.php
function test_csrf_protection() {
    // Simular ataque CSRF sin nonce
    $_POST['woocommerce-process-checkout-nonce'] = 'invalid_nonce';
    $_POST['wvp_zelle-confirmation'] = 'TEST123';
    
    $gateway = new WVP_Gateway_Zelle();
    $result = $gateway->validate_fields();
    
    assert($result === false, 'CSRF protection should block invalid nonce');
}
```

### **2. Prueba de Sanitización**
```php
function test_input_sanitization() {
    $malicious_input = '<script>alert("XSS")</script>TEST123';
    $sanitized = WVP_Security_Validator::sanitize_input($malicious_input);
    
    assert($sanitized === 'TEST123', 'Input should be sanitized');
    assert(strpos($sanitized, '<script>') === false, 'Script tags should be removed');
}
```

### **3. Prueba de Rate Limiting**
```php
function test_rate_limiting() {
    $user_id = 1;
    $action = 'payment_attempt';
    
    // Hacer 6 intentos (límite es 5)
    for ($i = 0; $i < 6; $i++) {
        $result = WVP_Rate_Limiter::check_rate_limit($user_id, $action, 5, 300);
        if ($i < 5) {
            assert($result === true, 'Should allow attempts within limit');
        } else {
            assert($result === false, 'Should block attempts beyond limit');
        }
    }
}
```

---

## 📋 **CHECKLIST DE IMPLEMENTACIÓN**

### **Seguridad Crítica**
- [ ] Implementar validación CSRF en todas las pasarelas
- [ ] Añadir sanitización inmediata de datos
- [ ] Implementar validación de permisos
- [ ] Añadir rate limiting
- [ ] Validar estado del pedido
- [ ] Crear clase de validación de seguridad
- [ ] Actualizar todas las pasarelas de pago
- [ ] Implementar manejo de errores robusto

### **Pruebas de Seguridad**
- [ ] Probar protección CSRF
- [ ] Probar sanitización de datos
- [ ] Probar rate limiting
- [ ] Probar validación de permisos
- [ ] Probar validación de estado del pedido

### **Documentación**
- [ ] Documentar cambios de seguridad
- [ ] Actualizar guía de desarrollo
- [ ] Crear guía de pruebas de seguridad
- [ ] Documentar nuevas clases

---

## ⚠️ **ADVERTENCIAS IMPORTANTES**

1. **NO implementar en producción** sin pruebas exhaustivas
2. **Hacer backup completo** antes de implementar cambios
3. **Probar en entorno de staging** primero
4. **Monitorear logs** después de la implementación
5. **Notificar a usuarios** sobre cambios de seguridad

---

## 🚀 **PRÓXIMOS PASOS**

1. **Implementar correcciones de seguridad** (1-2 días)
2. **Probar en entorno de desarrollo** (1 día)
3. **Desplegar en staging** (1 día)
4. **Probar en staging** (1 día)
5. **Desplegar en producción** (1 día)

**TOTAL: 5 días para implementación completa de seguridad**

---

**¡ESTAS CORRECCIONES DEBEN IMPLEMENTARSE INMEDIATAMENTE PARA PROTEGER LA TIENDA!** 🛡️
