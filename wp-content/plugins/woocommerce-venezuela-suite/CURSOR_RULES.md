# 🎯 Reglas para Desarrollo con Cursor - WooCommerce Venezuela Suite

## 📋 Contexto del Proyecto

Este documento define las reglas específicas para el desarrollo del plugin "WooCommerce Venezuela Suite" usando Cursor AI. El objetivo es crear un plugin estable, modular y bien documentado que integre todas las funcionalidades necesarias para operar una tienda WooCommerce en Venezuela.

## 🏗️ Arquitectura y Estándares

### Estructura de Archivos Obligatoria
```
woocommerce-venezuela-suite/
├── woocommerce-venezuela-suite.php          # Plugin principal
├── includes/
│   ├── class-wcvs-main.php                   # Clase principal (Singleton)
│   ├── class-wcvs-module-manager.php         # Gestor de módulos
│   ├── class-wcvs-settings-manager.php       # Gestor de configuraciones
│   └── class-wcvs-security.php              # Seguridad y validaciones
├── modules/
│   ├── [module-name]/
│   │   ├── class-wcvs-[module-name].php      # Clase principal del módulo
│   │   └── [module-name]-hooks.php           # Hooks específicos del módulo
├── admin/
│   ├── class-wcvs-admin.php                 # Administración principal
│   ├── views/                               # Plantillas de administración
│   ├── css/                                 # Estilos de administración
│   └── js/                                  # Scripts de administración
├── public/
│   ├── class-wcvs-public.php                # Frontend
│   ├── css/                                 # Estilos públicos
│   └── js/                                  # Scripts públicos
└── languages/
    └── wcvs.pot                            # Archivo de traducción
```

### Convenciones de Nomenclatura

#### Clases
- **Prefijo**: `WCVS_` (WooCommerce Venezuela Suite)
- **Formato**: `WCVS_[Module]_[Function]`
- **Ejemplos**:
  - `WCVS_Main` (clase principal)
  - `WCVS_BCV_Sync` (módulo BCV)
  - `WCVS_Gateway_PagoMovil` (pasarela Pago Móvil)

#### Métodos y Funciones
- **Formato**: `snake_case` para métodos privados, `camelCase` para públicos
- **Ejemplos**:
  - `get_bcv_rate()` (público)
  - `_validate_rate_data()` (privado)
  - `process_payment()` (público)

#### Variables y Propiedades
- **Formato**: `snake_case`
- **Ejemplos**:
  - `$bcv_rate`
  - `$payment_gateways`
  - `$module_settings`

#### Constantes
- **Formato**: `WCVS_UPPER_CASE`
- **Ejemplos**:
  - `WCVS_VERSION`
  - `WCVS_PLUGIN_PATH`
  - `WCVS_BCV_CACHE_DURATION`

## 🔧 Reglas de Desarrollo con Cursor

### 1. Generación Atómica de Código

#### ❌ NO Hacer
```php
// Generar clases completas de una vez
// Generar archivos completos sin revisión
// Crear múltiples funcionalidades en un solo prompt
```

#### ✅ Hacer
```php
// Generar una función/método a la vez
// Explicar la lógica después de cada generación
// Revisar y validar antes de continuar
```

### 2. Prompts Específicos y Detallados

#### ❌ Prompt Genérico
```
"Crea la pasarela de Pago Móvil"
```

#### ✅ Prompt Específico
```
"Genera la clase WCVS_Gateway_PagoMovil que extienda WC_Payment_Gateway. 
Incluye los campos de configuración: title, description, beneficiary_name, 
beneficiary_id, phone_number, bank_name. El método process_payment() debe 
cambiar el estado del pedido a 'on-hold' y retornar redirect a 'thank you'. 
Usa nonces para seguridad y sanitiza todos los inputs."
```

### 3. Documentación Obligatoria

#### Cada Clase Debe Tener
```php
/**
 * Clase para [descripción específica]
 * 
 * @package WooCommerce_Venezuela_Suite
 * @subpackage [Module]
 * @since 1.0.0
 * @author Kinta Electric
 */
class WCVS_Example_Class {
    
    /**
     * [Descripción del método]
     * 
     * @since 1.0.0
     * @param string $param1 Descripción del parámetro
     * @param int $param2 Descripción del parámetro
     * @return bool|WP_Error True en éxito, WP_Error en caso de error
     */
    public function example_method($param1, $param2 = 0) {
        // Implementación
    }
}
```

### 4. Seguridad Obligatoria

#### Validaciones Requeridas
```php
// Nonces para AJAX
if (!wp_verify_nonce($_POST['nonce'], 'wcvs_action')) {
    wp_die('Acceso denegado');
}

// Permisos de usuario
if (!current_user_can('manage_options')) {
    wp_die('Permisos insuficientes');
}

// Sanitización de inputs
$clean_data = sanitize_text_field($_POST['data']);
$clean_email = sanitize_email($_POST['email']);

// Escape de outputs
echo esc_html($user_data);
echo esc_attr($attribute_value);
```

### 5. Manejo de Errores

#### Patrón Obligatorio
```php
try {
    // Operación crítica
    $result = $this->critical_operation();
    
    if (!$result) {
        throw new Exception('Operación falló');
    }
    
    return $result;
    
} catch (Exception $e) {
    // Log del error
    error_log('WCVS Error: ' . $e->getMessage());
    
    // Notificar al admin si es crítico
    if ($this->is_critical_error($e)) {
        $this->notify_admin($e);
    }
    
    // Retornar error apropiado
    return new WP_Error('wcvs_error', $e->getMessage());
}
```

### 6. Hooks de WooCommerce

#### Patrón de Implementación
```php
/**
 * Registrar hooks del módulo
 */
private function init_hooks() {
    // Solo en frontend
    if (!is_admin()) {
        add_filter('woocommerce_get_price_html', [$this, 'format_price_html'], 99, 2);
        add_filter('woocommerce_cart_item_price', [$this, 'format_price_html'], 99, 2);
    }
    
    // Solo en admin
    if (is_admin()) {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    // AJAX hooks
    add_action('wp_ajax_wcvs_action', [$this, 'handle_ajax_action']);
    add_action('wp_ajax_nopriv_wcvs_action', [$this, 'handle_ajax_action']);
}
```

### 7. Configuración de Módulos

#### Patrón de Settings
```php
/**
 * Configuración por defecto del módulo
 */
private function get_default_settings() {
    return [
        'enabled' => false,
        'title' => __('Título por Defecto', 'wcvs'),
        'description' => __('Descripción por Defecto', 'wcvs'),
        'specific_setting' => 'default_value'
    ];
}

/**
 * Obtener configuración del módulo
 */
public function get_module_setting($key, $default = null) {
    $settings = get_option('wcvs_module_' . $this->module_name, []);
    $defaults = $this->get_default_settings();
    
    return isset($settings[$key]) ? $settings[$key] : 
           (isset($defaults[$key]) ? $defaults[$key] : $default);
}
```

## 🚀 Proceso de Desarrollo por Fases

### Fase 1: Core del Plugin

#### Prompt para Cursor
```
"Crea la clase principal WCVS_Main usando patrón Singleton. 
Incluye métodos para: inicialización del plugin, carga de módulos, 
verificación de dependencias (WooCommerce), y hooks de activación/desactivación. 
Usa las constantes WCVS_VERSION, WCVS_PLUGIN_PATH, WCVS_PLUGIN_URL. 
Incluye documentación PHPDoc completa y manejo de errores."
```

#### Validaciones Post-Generación
- [ ] Singleton implementado correctamente
- [ ] Constantes definidas
- [ ] Hooks de activación/desactivación
- [ ] Verificación de dependencias
- [ ] Documentación PHPDoc completa

### Fase 2: Módulo BCV Sync

#### Prompt para Cursor
```
"Crea la clase WCVS_BCV_Sync para obtener tasa de cambio del BCV. 
Incluye métodos para: scraping de datos, cache de tasa, cron jobs automáticos, 
y API interna para otros módulos. Usa wp_remote_get() para obtener datos, 
update_option() para cache, y wp_schedule_event() para cron. 
Incluye fallback manual y logging de errores."
```

#### Validaciones Post-Generación
- [ ] Scraping funcional del BCV
- [ ] Sistema de cache implementado
- [ ] Cron jobs configurados
- [ ] API interna disponible
- [ ] Manejo de errores robusto

### Fase 3: Módulo Price Display

#### Prompt para Cursor
```
"Crea la clase WCVS_Price_Display para mostrar precios duales. 
Implementa hooks: woocommerce_get_price_html, woocommerce_cart_item_price, 
woocommerce_cart_subtotal, woocommerce_cart_total. 
Permite formato personalizable con placeholders {bs_price} y {usd_price}. 
Incluye opciones de redondeo y posición de monedas."
```

#### Validaciones Post-Generación
- [ ] Hooks de WooCommerce implementados
- [ ] Formato dual funcional
- [ ] Personalización de formato
- [ ] Opciones de redondeo
- [ ] Performance optimizada

### Fase 4: Pasarelas de Pago

#### Prompt para Cursor
```
"Crea la clase WCVS_Gateway_PagoMovil que extienda WC_Payment_Gateway. 
Incluye campos: beneficiary_name, beneficiary_id, phone_number, bank_name. 
El método process_payment() debe cambiar estado a 'on-hold' y mostrar 
instrucciones de pago. Incluye formulario para reportar referencia de pago. 
Usa nonces, sanitización y validación completa."
```

#### Validaciones Post-Generación
- [ ] Extensión correcta de WC_Payment_Gateway
- [ ] Campos de configuración implementados
- [ ] Procesamiento de pago funcional
- [ ] Formulario de referencia
- [ ] Seguridad implementada

### Fase 5: Módulo IGTF

#### Prompt para Cursor
```
"Crea la clase WCVS_Fees para cálculo automático de IGTF. 
Implementa hook woocommerce_cart_calculate_fees. 
Aplica IGTF solo a pasarelas seleccionadas por admin. 
Calcula 3% del total del carrito. Incluye configuración de pasarelas 
y porcentaje personalizable. Maneja excepciones de productos."
```

#### Validaciones Post-Generación
- [ ] Hook woocommerce_cart_calculate_fees implementado
- [ ] Cálculo correcto de IGTF
- [ ] Configuración por pasarela
- [ ] Porcentaje personalizable
- [ ] Excepciones de productos

## 🔍 Checklist de Revisión

### Antes de Continuar con Siguiente Fase
- [ ] Código generado funciona sin errores
- [ ] Documentación PHPDoc completa
- [ ] Seguridad implementada (nonces, sanitización, escape)
- [ ] Manejo de errores robusto
- [ ] Hooks de WooCommerce correctos
- [ ] Performance optimizada
- [ ] Testing básico realizado

### Antes de Commit
- [ ] Código sigue WordPress Coding Standards
- [ ] Sin código muerto o comentado
- [ ] Variables no utilizadas eliminadas
- [ ] Funciones no llamadas removidas
- [ ] Logs de debug removidos
- [ ] Documentación actualizada

## 🚨 Errores Comunes a Evitar

### 1. Código Muerto
```php
// ❌ NO hacer
// $unused_variable = 'value';
// if (false) { ... }
```

### 2. Dependencias Circulares
```php
// ❌ NO hacer
// Clase A requiere Clase B, Clase B requiere Clase A
```

### 3. Hooks Incorrectos
```php
// ❌ NO hacer
// add_action('init', [$this, 'admin_function']); // admin_function solo para admin
```

### 4. Falta de Validación
```php
// ❌ NO hacer
// $data = $_POST['data']; // Sin sanitización
// echo $user_input; // Sin escape
```

## 📊 Métricas de Calidad

### Código
- 0 errores de PHP
- 0 warnings de WordPress
- 100% documentación PHPDoc
- 0 código muerto

### Performance
- Tiempo de carga < 200ms
- Uso de memoria < 50MB
- Consultas de DB optimizadas
- Cache implementado

### Seguridad
- 100% inputs sanitizados
- 100% outputs escapados
- Nonces en todas las acciones
- Permisos verificados

## 🎯 Objetivos de Desarrollo

### Estabilidad
- Plugin funciona sin errores críticos
- Manejo robusto de fallos de API
- Fallbacks para todas las operaciones críticas
- Logging detallado para debugging

### Mantenibilidad
- Código limpio y bien documentado
- Arquitectura modular clara
- Separación de responsabilidades
- Tests automatizados

### Usabilidad
- Configuración intuitiva
- Interfaz de administración clara
- Documentación de usuario completa
- Soporte técnico eficiente

---

**Recuerda**: Cada fase debe ser completamente funcional antes de continuar con la siguiente. La calidad es más importante que la velocidad.
