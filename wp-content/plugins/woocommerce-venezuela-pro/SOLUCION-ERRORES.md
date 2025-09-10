# Solución de Errores - WooCommerce Venezuela Pro

## 🔧 **ERRORES CORREGIDOS**

### **1. Errores de Deprecación de PHP 8.2+**

#### **Problema:**
```
PHP Deprecated: Creation of dynamic property WVP_Gateway_Zelle::$zelle_email is deprecated
```

#### **Causa:**
En PHP 8.2+ se desaconseja la creación de propiedades dinámicas sin declararlas explícitamente en la clase.

#### **Solución Aplicada:**
Se declararon todas las propiedades dinámicas en cada clase de pasarela de pago:

**Zelle:**
```php
class WVP_Gateway_Zelle extends WC_Payment_Gateway {
    public $zelle_email;
    public $apply_igtf;
    // ...
}
```

**Pago Móvil:**
```php
class WVP_Gateway_Pago_Movil extends WC_Payment_Gateway {
    public $ci;
    public $phone;
    public $bank;
    public $apply_igtf;
    // ...
}
```

**Efectivo USD:**
```php
class WVP_Gateway_Efectivo extends WC_Payment_Gateway {
    public $apply_igtf;
    // ...
}
```

**Efectivo Bolívares:**
```php
class WVP_Gateway_Efectivo_Bolivares extends WC_Payment_Gateway {
    public $apply_igtf;
    // ...
}
```

---

## 🚨 **ERRORES COMUNES Y SOLUCIONES**

### **2. Error de Headers Ya Enviados**

#### **Problema:**
```
Warning: Cannot modify header information - headers already sent
```

#### **Causa:**
- Espacios en blanco antes de `<?php`
- Caracteres BOM (Byte Order Mark) en archivos
- Salida antes de headers

#### **Solución:**
1. **Verificar archivos PHP:**
   - Asegurar que no hay espacios antes de `<?php`
   - Eliminar caracteres BOM
   - Usar editor que guarde en UTF-8 sin BOM

2. **Verificar logs de error:**
   ```bash
   tail -f /path/to/wordpress/wp-content/debug.log
   ```

### **3. Plugin No Se Carga**

#### **Problema:**
```
Fatal error: Class 'WooCommerce_Venezuela_Pro' not found
```

#### **Causa:**
- Archivo principal no se carga correctamente
- Dependencias faltantes
- Errores de sintaxis

#### **Solución:**
1. **Verificar archivo principal:**
   ```php
   // Verificar que existe y es válido
   if (file_exists(WVP_PLUGIN_FILE)) {
       require_once WVP_PLUGIN_FILE;
   }
   ```

2. **Verificar dependencias:**
   - WooCommerce activo
   - BCV Dólar Tracker activo
   - PHP 7.4+

### **4. Tasa BCV No Disponible**

#### **Problema:**
```
Tasa BCV: No disponible
```

#### **Causa:**
- BCV Dólar Tracker inactivo
- Error en conexión con BCV
- Cache expirado

#### **Solución:**
1. **Verificar BCV Dólar Tracker:**
   ```php
   if (is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
       $rate = WVP_BCV_Integrator::get_rate();
   }
   ```

2. **Limpiar cache:**
   ```php
   delete_transient('wvp_bcv_rate');
   ```

### **5. IGTF No Se Aplica**

#### **Problema:**
IGTF no se calcula en el checkout

#### **Causa:**
- Configuración incorrecta de pasarelas
- Lógica de aplicación incorrecta
- Tasa IGTF no configurada

#### **Solución:**
1. **Verificar configuración:**
   - WooCommerce → Configuración → Pagos
   - Activar pasarelas venezolanas
   - Configurar "Aplicar IGTF" correctamente

2. **Verificar lógica:**
   ```php
   private function should_apply_igtf() {
       $chosen_payment_method = WC()->session->get("chosen_payment_method");
       return $this->gateway_applies_igtf($chosen_payment_method);
   }
   ```

### **6. Números de Control No Se Asignan**

#### **Problema:**
Números de control SENIAT no se generan automáticamente

#### **Causa:**
- Configuración no establecida
- Hook no se ejecuta
- Permisos insuficientes

#### **Solución:**
1. **Verificar configuración:**
   - Venezuela Pro → Configuraciones Fiscales
   - Establecer prefijo y próximo número

2. **Verificar hook:**
   ```php
   add_action("woocommerce_order_status_completed", array($this, "assign_control_number"));
   ```

### **7. Facturas No Se Generan**

#### **Problema:**
Error al generar facturas PDF

#### **Causa:**
- Permisos de escritura insuficientes
- Directorio no existe
- Error en generación HTML

#### **Solución:**
1. **Verificar permisos:**
   ```bash
   chmod 755 /wp-content/uploads/
   chmod 755 /wp-content/uploads/wvp-invoices/
   ```

2. **Crear directorio:**
   ```php
   $invoices_dir = $upload_dir['basedir'] . '/wvp-invoices/';
   if (!file_exists($invoices_dir)) {
       wp_mkdir_p($invoices_dir);
   }
   ```

---

## 🔍 **HERRAMIENTAS DE DEBUGGING**

### **1. Logs de WordPress**
```php
// En wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### **2. Verificar Estado del Plugin**
```php
// En la pestaña "Información del Plugin"
- Estado de dependencias
- Estadísticas del plugin
- Información del sistema
```

### **3. Verificar Metadatos de Pedidos**
```php
// En pedidos individuales
- Meta box "Datos Venezuela"
- Verificar metadatos guardados
- Comprobar números de control
```

---

## 📞 **SOPORTE TÉCNICO**

### **Información Necesaria para Soporte:**
1. **Versión de PHP:** `php -v`
2. **Versión de WordPress:** En admin
3. **Versión de WooCommerce:** En admin
4. **Logs de error:** `/wp-content/debug.log`
5. **Configuración del plugin:** Captura de pantalla

### **Pasos de Verificación:**
1. ✅ Verificar que no hay errores de deprecación
2. ✅ Comprobar que todas las dependencias están activas
3. ✅ Verificar configuraciones del plugin
4. ✅ Probar flujo completo de compra
5. ✅ Generar reportes fiscales

---

## 🚀 **OPTIMIZACIONES RECOMENDADAS**

### **1. Configuración de PHP**
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
```

### **2. Configuración de WordPress**
```php
// En wp-config.php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

### **3. Configuración de WooCommerce**
- Usar caché de objetos
- Optimizar base de datos
- Limpiar pedidos antiguos

---

*Documento actualizado: Septiembre 2025*
*Plugin: WooCommerce Venezuela Pro v1.0.0*
