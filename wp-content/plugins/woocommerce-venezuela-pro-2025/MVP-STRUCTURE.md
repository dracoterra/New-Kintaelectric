# 🚀 MVP - WooCommerce Venezuela Suite 2025

## Archivo Mínimo Viable

Este documento define la estructura mínima viable para el plugin WooCommerce Venezuela Suite 2025, enfocándose en componentes modulares activables/desactivables con integración perfecta a WooCommerce.

---

## 📁 Estructura de Archivos MVP

```
woocommerce-venezuela-pro-2025/
├── woocommerce-venezuela-pro-2025.php          # Bootstrap principal
├── includes/
│   ├── class-wcvs-core.php                     # Clase principal (Singleton)
│   ├── class-wcvs-module-manager.php           # Gestor de módulos
│   ├── class-wcvs-settings.php                 # Configuraciones
│   └── class-wcvs-help.php                     # Sistema de ayuda
├── modules/
│   ├── currency-manager/
│   │   ├── class-wcvs-currency-manager.php
│   │   ├── currency-hooks.php
│   │   └── currency-admin.php
│   ├── payment-gateways/
│   │   ├── class-wcvs-payment-manager.php
│   │   ├── gateways/
│   │   │   ├── class-wcvs-pago-movil.php
│   │   │   └── class-wcvs-zelle.php
│   │   └── payment-hooks.php
│   ├── tax-system/
│   │   ├── class-wcvs-tax-manager.php
│   │   ├── tax-hooks.php
│   │   └── tax-admin.php
│   └── shipping-methods/
│       ├── class-wcvs-shipping-manager.php
│       ├── methods/
│       │   ├── class-wcvs-mrw.php
│       │   └── class-wcvs-zoom.php
│       └── shipping-hooks.php
├── admin/
│   ├── class-wcvs-admin.php                    # Panel de administración
│   ├── views/
│   │   ├── modules-page.php                    # Página de módulos
│   │   ├── settings-page.php                   # Configuraciones generales
│   │   └── help-page.php                       # Página de ayuda
│   ├── css/
│   │   └── wcvs-admin.css
│   └── js/
│       └── wcvs-admin.js
├── public/
│   ├── class-wcvs-public.php                   # Funcionalidad pública
│   ├── css/
│   │   └── wcvs-public.css
│   └── js/
│       └── wcvs-public.js
├── languages/
│   └── woocommerce-venezuela-pro-2025.pot
└── docs/
    ├── README.md
    ├── PROJECT-BRIEF.md
    ├── TECHNICAL-ARCHITECTURE.md
    ├── CURSOR-DEVELOPMENT-RULES.md
    └── MODULAR-PLAN.md
```

---

## 🎯 Componentes MVP

### **Componente 1: Gestor de Moneda Inteligente** 💵
**Prioridad**: ALTA
**Funcionalidades MVP**:
- Conversión básica USD → VES
- Visualización dual de precios
- Selector de moneda en checkout
- Cache básico de conversiones

**Archivos**:
- `modules/currency-manager/class-wcvs-currency-manager.php`
- `modules/currency-manager/currency-hooks.php`
- `modules/currency-manager/currency-admin.php`

### **Componente 2: Pasarelas de Pago Locales** 💳
**Prioridad**: ALTA
**Funcionalidades MVP**:
- Pago Móvil (C2P) básico
- Zelle básico
- Validación de referencias

**Archivos**:
- `modules/payment-gateways/class-wcvs-payment-manager.php`
- `modules/payment-gateways/gateways/class-wcvs-pago-movil.php`
- `modules/payment-gateways/gateways/class-wcvs-zelle.php`
- `modules/payment-gateways/payment-hooks.php`

### **Componente 3: Sistema de Impuestos Venezolanos** 🧾
**Prioridad**: ALTA
**Funcionalidades MVP**:
- Cálculo automático de IVA (16%)
- Aplicación de IGTF (3%) para pagos en divisas
- Campos básicos de Cédula/RIF

**Archivos**:
- `modules/tax-system/class-wcvs-tax-manager.php`
- `modules/tax-system/tax-hooks.php`
- `modules/tax-system/tax-admin.php`

### **Componente 4: Envíos Nacionales** 🚚
**Prioridad**: MEDIA
**Funcionalidades MVP**:
- MRW básico
- Zoom básico
- Tarifas por peso/destino

**Archivos**:
- `modules/shipping-methods/class-wcvs-shipping-manager.php`
- `modules/shipping-methods/methods/class-wcvs-mrw.php`
- `modules/shipping-methods/methods/class-wcvs-zoom.php`
- `modules/shipping-methods/shipping-hooks.php`

---

## 🏗️ Arquitectura Técnica MVP

### Clase Principal (Singleton)
```php
class WCVS_Core {
    private static $instance = null;
    private $modules = array();
    private $module_manager;
    private $settings;
    private $help;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_modules();
    }
}
```

### Gestor de Módulos
```php
class WCVS_Module_Manager {
    private $active_modules = array();
    
    public function register_module($module_id, $module_class) {
        // Registrar módulo
    }
    
    public function activate_module($module_id) {
        // Activar módulo
    }
    
    public function deactivate_module($module_id) {
        // Desactivar módulo
    }
    
    public function is_module_active($module_id) {
        // Verificar si módulo está activo
    }
}
```

### Sistema de Configuraciones
```php
class WCVS_Settings {
    public function get_module_setting($module_id, $setting_key) {
        // Obtener configuración de módulo
    }
    
    public function update_module_setting($module_id, $setting_key, $value) {
        // Actualizar configuración de módulo
    }
    
    public function get_woocommerce_integration_links() {
        // Enlaces a configuraciones de WooCommerce
    }
}
```

### Sistema de Ayuda
```php
class WCVS_Help {
    public function get_module_help($module_id) {
        // Ayuda específica del módulo
    }
    
    public function get_woocommerce_setup_guide($module_id) {
        // Guía de configuración en WooCommerce
    }
    
    public function get_troubleshooting_guide($module_id) {
        // Guía de solución de problemas
    }
}
```

---

## 🎛️ Panel de Administración MVP

### Página Principal
```php
// admin/views/modules-page.php
<div class="wrap">
    <h1>🇻🇪 WooCommerce Venezuela Suite 2025</h1>
    
    <div class="wcvs-modules-grid">
        <!-- Módulo de Moneda -->
        <div class="wcvs-module-card">
            <h3>💵 Gestor de Moneda Inteligente</h3>
            <p>Conversión automática USD/VES con visualización dual</p>
            <div class="wcvs-module-actions">
                <label>
                    <input type="checkbox" name="currency_manager_active" <?php checked($this->is_module_active('currency_manager')); ?>>
                    Activar Módulo
                </label>
                <a href="<?php echo admin_url('admin.php?page=wcvs-currency-settings'); ?>" class="button">Configurar</a>
                <a href="<?php echo admin_url('admin.php?page=wcvs-help&module=currency_manager'); ?>" class="button">Ayuda</a>
            </div>
        </div>
        
        <!-- Módulo de Pagos -->
        <div class="wcvs-module-card">
            <h3>💳 Pasarelas de Pago Locales</h3>
            <p>Pago Móvil, Zelle y transferencias bancarias</p>
            <div class="wcvs-module-actions">
                <label>
                    <input type="checkbox" name="payment_gateways_active" <?php checked($this->is_module_active('payment_gateways')); ?>>
                    Activar Módulo
                </label>
                <a href="<?php echo admin_url('admin.php?page=wc-payment-gateways'); ?>" class="button">Configurar en WooCommerce</a>
                <a href="<?php echo admin_url('admin.php?page=wcvs-help&module=payment_gateways'); ?>" class="button">Ayuda</a>
            </div>
        </div>
        
        <!-- Módulo de Impuestos -->
        <div class="wcvs-module-card">
            <h3>🧾 Sistema de Impuestos Venezolanos</h3>
            <p>IVA (16%) e IGTF (3%) automáticos</p>
            <div class="wcvs-module-actions">
                <label>
                    <input type="checkbox" name="tax_system_active" <?php checked($this->is_module_active('tax_system')); ?>>
                    Activar Módulo
                </label>
                <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=tax'); ?>" class="button">Configurar en WooCommerce</a>
                <a href="<?php echo admin_url('admin.php?page=wcvs-help&module=tax_system'); ?>" class="button">Ayuda</a>
            </div>
        </div>
        
        <!-- Módulo de Envíos -->
        <div class="wcvs-module-card">
            <h3>🚚 Envíos Nacionales</h3>
            <p>MRW, Zoom y otros couriers venezolanos</p>
            <div class="wcvs-module-actions">
                <label>
                    <input type="checkbox" name="shipping_methods_active" <?php checked($this->is_module_active('shipping_methods')); ?>>
                    Activar Módulo
                </label>
                <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping'); ?>" class="button">Configurar en WooCommerce</a>
                <a href="<?php echo admin_url('admin.php?page=wcvs-help&module=shipping_methods'); ?>" class="button">Ayuda</a>
            </div>
        </div>
    </div>
    
    <div class="wcvs-help-section">
        <h2>🔧 Configuración de WooCommerce</h2>
        <p>Para que los módulos funcionen correctamente, necesitas configurar WooCommerce:</p>
        <ul>
            <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=general'); ?>">Configurar Moneda Base (VES)</a></li>
            <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=tax'); ?>">Activar Impuestos</a></li>
            <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping'); ?>">Configurar Zonas de Envío</a></li>
            <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout'); ?>">Personalizar Checkout</a></li>
        </ul>
    </div>
</div>
```

### Página de Ayuda por Módulo
```php
// admin/views/help-page.php
<div class="wrap">
    <h1>📚 Ayuda - <?php echo $this->get_module_name($module_id); ?></h1>
    
    <div class="wcvs-help-content">
        <div class="wcvs-help-section">
            <h2>¿Qué hace este módulo?</h2>
            <p><?php echo $this->get_module_description($module_id); ?></p>
        </div>
        
        <div class="wcvs-help-section">
            <h2>¿Cómo configurar en WooCommerce?</h2>
            <ol>
                <?php foreach($this->get_woocommerce_setup_steps($module_id) as $step): ?>
                    <li><?php echo $step; ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
        
        <div class="wcvs-help-section">
            <h2>Enlaces de Configuración</h2>
            <ul>
                <?php foreach($this->get_woocommerce_links($module_id) as $link): ?>
                    <li><a href="<?php echo $link['url']; ?>"><?php echo $link['title']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="wcvs-help-section">
            <h2>Problemas Comunes</h2>
            <div class="wcvs-troubleshooting">
                <?php foreach($this->get_troubleshooting_items($module_id) as $item): ?>
                    <div class="wcvs-troubleshooting-item">
                        <h4><?php echo $item['problem']; ?></h4>
                        <p><?php echo $item['solution']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
```

---

## 🔧 Integración con WooCommerce

### Hooks Principales por Módulo

#### Módulo de Moneda
```php
// currency-hooks.php
add_filter('woocommerce_currency', array($this, 'set_base_currency'));
add_filter('woocommerce_price_html', array($this, 'display_dual_prices'), 10, 2);
add_action('woocommerce_checkout_process', array($this, 'convert_checkout_prices'));
add_action('woocommerce_before_add_to_cart_form', array($this, 'add_currency_selector'));
```

#### Módulo de Pagos
```php
// payment-hooks.php
add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
add_action('woocommerce_checkout_process', array($this, 'validate_payment_data'));
add_action('woocommerce_payment_complete', array($this, 'handle_payment_completion'));
```

#### Módulo de Impuestos
```php
// tax-hooks.php
add_action('woocommerce_calculate_totals', array($this, 'calculate_venezuelan_taxes'));
add_filter('woocommerce_checkout_fields', array($this, 'add_venezuelan_fields'));
add_action('woocommerce_checkout_process', array($this, 'validate_venezuelan_documents'));
```

#### Módulo de Envíos
```php
// shipping-hooks.php
add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));
add_action('woocommerce_shipping_init', array($this, 'init_shipping_methods'));
add_filter('woocommerce_shipping_packages', array($this, 'validate_shipping_destinations'));
```

---

## 📋 Checklist de Implementación MVP

### Fase 1: Estructura Base
- [ ] Crear archivo principal `woocommerce-venezuela-pro-2025.php`
- [ ] Implementar clase `WCVS_Core` con Singleton
- [ ] Crear `WCVS_Module_Manager` para gestión de módulos
- [ ] Desarrollar `WCVS_Settings` para configuraciones
- [ ] Implementar `WCVS_Help` para sistema de ayuda

### Fase 2: Panel de Administración
- [ ] Crear página principal de módulos
- [ ] Implementar sistema de activación/desactivación
- [ ] Desarrollar páginas de configuración por módulo
- [ ] Crear sistema de ayuda integrado
- [ ] Añadir enlaces directos a WooCommerce

### Fase 3: Módulo de Moneda
- [ ] Implementar conversión básica USD/VES
- [ ] Crear visualización dual de precios
- [ ] Desarrollar selector de moneda en checkout
- [ ] Implementar cache básico de conversiones
- [ ] Crear página de configuración

### Fase 4: Módulo de Pagos
- [ ] Implementar Pago Móvil básico
- [ ] Crear pasarela Zelle básica
- [ ] Desarrollar validación de referencias
- [ ] Integrar con WooCommerce Payment Gateway
- [ ] Crear páginas de configuración

### Fase 5: Módulo de Impuestos
- [ ] Implementar cálculo de IVA (16%)
- [ ] Crear aplicación de IGTF (3%) para divisas
- [ ] Desarrollar campos de Cédula/RIF
- [ ] Integrar con WooCommerce Tax System
- [ ] Crear validación de documentos

### Fase 6: Módulo de Envíos
- [ ] Implementar MRW básico
- [ ] Crear Zoom básico
- [ ] Desarrollar tarifas por peso/destino
- [ ] Integrar con WooCommerce Shipping
- [ ] Crear zonas de envío venezolanas

### Fase 7: Testing y Documentación
- [ ] Testing de todos los módulos
- [ ] Verificar integración con WooCommerce
- [ ] Completar documentación de ayuda
- [ ] Optimizar performance
- [ ] Preparar para lanzamiento

---

## 🎯 Criterios de Éxito MVP

### Funcionalidad
- ✅ Todos los módulos se pueden activar/desactivar independientemente
- ✅ Cada módulo se integra correctamente con WooCommerce
- ✅ El sistema de ayuda guía al usuario paso a paso
- ✅ Los enlaces directos a WooCommerce funcionan correctamente

### Usabilidad
- ✅ Panel de administración intuitivo y claro
- ✅ Configuración fácil para usuarios no técnicos
- ✅ Ayuda contextual para cada módulo
- ✅ Enlaces directos a configuraciones de WooCommerce

### Técnica
- ✅ Código modular y mantenible
- ✅ Sin conflictos con otros plugins
- ✅ Performance optimizada
- ✅ Compatible con temas populares

---

*Este MVP proporciona una base sólida para el desarrollo del plugin completo, con enfoque en modularidad, integración perfecta con WooCommerce y facilidad de uso.*
