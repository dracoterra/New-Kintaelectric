# 🛠️ **REGLAS DE DESARROLLO - WOOCOMMERCE VENEZUELA SUITE**

## **📋 REGLAS GENERALES**

### **🎯 1. PRINCIPIOS FUNDAMENTALES**
- **Modularidad**: Cada funcionalidad debe ser un módulo independiente
- **Compatibilidad**: Máxima compatibilidad con WordPress y WooCommerce
- **Seguridad**: Implementar seguridad desde el diseño
- **Performance**: Optimización continua del rendimiento
- **Mantenibilidad**: Código limpio y bien documentado
- **Escalabilidad**: Arquitectura preparada para crecimiento

### **🏗️ 2. ARQUITECTURA DEL PLUGIN**
- **Patrón Singleton** para la clase principal
- **Patrón Factory** para creación de módulos
- **Patrón Observer** para eventos y hooks
- **Separación de responsabilidades** (MVC-like)
- **Inyección de dependencias** donde sea apropiado

---

## **📁 ESTRUCTURA DE ARCHIVOS**

### **🎯 3. ORGANIZACIÓN DE DIRECTORIOS**
```
woocommerce-venezuela-suite/
├── 📁 core/                    # Núcleo del plugin
├── 📁 modules/                 # Módulos independientes
├── 📁 includes/                # Clases compartidas
├── 📁 admin/                  # Panel de administración
├── 📁 public/                 # Frontend público
├── 📁 assets/                 # Recursos estáticos
├── 📁 languages/              # Traducciones
├── 📁 tests/                  # Pruebas
├── 📁 docs/                   # Documentación
└── 📁 tools/                  # Herramientas de desarrollo
```

### **📄 4. NOMENCLATURA DE ARCHIVOS**
- **Clases**: `class-{nombre-descriptivo}.php`
- **Interfaces**: `interface-{nombre-descriptivo}.php`
- **Traits**: `trait-{nombre-descriptivo}.php`
- **Templates**: `{nombre-descriptivo}.php`
- **Assets**: `{modulo}-{tipo}.{extension}`

---

## **💻 ESTÁNDARES DE CÓDIGO**

### **🏷️ 5. NOMENCLATURA DE CLASES**
```php
// ✅ CORRECTO
class Woocommerce_Venezuela_Suite_Module_Core {
    // Implementación
}

// ❌ INCORRECTO
class WVS_Core {
    // Implementación
}
```

### **🔧 6. NOMENCLATURA DE FUNCIONES**
```php
// ✅ CORRECTO
function woocommerce_venezuela_suite_get_current_rate() {
    // Implementación
}

// ❌ INCORRECTO
function wvs_get_rate() {
    // Implementación
}
```

### **📊 7. NOMENCLATURA DE VARIABLES**
```php
// ✅ CORRECTO
$current_exchange_rate = get_option('wvs_current_rate');
$module_settings = get_option('wvs_module_settings');

// ❌ INCORRECTO
$rate = get_option('rate');
$settings = get_option('settings');
```

### **🔒 8. NOMENCLATURA DE CONSTANTES**
```php
// ✅ CORRECTO
define('WOOCOMMERCE_VENEZUELA_SUITE_VERSION', '1.0.0');
define('WOOCOMMERCE_VENEZUELA_SUITE_PLUGIN_URL', plugin_dir_url(__FILE__));

// ❌ INCORRECTO
define('VERSION', '1.0.0');
define('URL', plugin_dir_url(__FILE__));
```

---

## **🔒 REGLAS DE SEGURIDAD**

### **🛡️ 9. VALIDACIÓN Y SANITIZACIÓN**
```php
// ✅ CORRECTO
$user_input = sanitize_text_field($_POST['user_input']);
$email = sanitize_email($_POST['email']);
$url = esc_url_raw($_POST['url']);

// ❌ INCORRECTO
$user_input = $_POST['user_input'];
$email = $_POST['email'];
$url = $_POST['url'];
```

### **🔐 10. NONCES Y CSRF**
```php
// ✅ CORRECTO
wp_nonce_field('wvs_action', 'wvs_nonce');
if (!wp_verify_nonce($_POST['wvs_nonce'], 'wvs_action')) {
    wp_die('Security check failed');
}

// ❌ INCORRECTO
// Sin verificación de nonce
```

### **🔑 11. CAPACIDADES Y PERMISOS**
```php
// ✅ CORRECTO
if (!current_user_can('manage_woocommerce')) {
    wp_die('Insufficient permissions');
}

// ❌ INCORRECTO
// Sin verificación de permisos
```

### **💾 12. ESCAPE DE OUTPUT**
```php
// ✅ CORRECTO
echo esc_html($user_data);
echo esc_attr($attribute_value);
echo esc_url($link_url);

// ❌ INCORRECTO
echo $user_data;
echo $attribute_value;
echo $link_url;
```

---

## **🗄️ REGLAS DE BASE DE DATOS**

### **📊 13. NOMENCLATURA DE TABLAS**
```php
// ✅ CORRECTO
$table_name = $wpdb->prefix . 'wvs_bcv_rates';
$table_name = $wpdb->prefix . 'wvs_payment_transactions';

// ❌ INCORRECTO
$table_name = $wpdb->prefix . 'rates';
$table_name = $wpdb->prefix . 'transactions';
```

### **🔍 14. CONSULTAS PREPARADAS**
```php
// ✅ CORRECTO
$results = $wpdb->prepare(
    "SELECT * FROM {$table_name} WHERE rate_date = %s AND currency = %s",
    $date,
    $currency
);

// ❌ INCORRECTO
$results = $wpdb->query(
    "SELECT * FROM {$table_name} WHERE rate_date = '$date' AND currency = '$currency'"
);
```

### **📈 15. ÍNDICES Y OPTIMIZACIÓN**
```php
// ✅ CORRECTO
CREATE INDEX idx_rate_date ON wp_wvs_bcv_rates (rate_date);
CREATE INDEX idx_currency ON wp_wvs_bcv_rates (currency);

// ❌ INCORRECTO
// Sin índices para consultas frecuentes
```

---

## **🎨 REGLAS DE FRONTEND**

### **📱 16. RESPONSIVE DESIGN**
```css
/* ✅ CORRECTO */
.wvs-converter {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .wvs-converter {
        max-width: 100%;
        padding: 10px;
    }
}

/* ❌ INCORRECTO */
.wvs-converter {
    width: 400px;
    /* Sin responsive */
}
```

### **♿ 17. ACCESIBILIDAD**
```html
<!-- ✅ CORRECTO -->
<button type="button" aria-label="Convertir moneda" class="wvs-convert-btn">
    <span class="sr-only">Convertir moneda</span>
    <i class="icon-convert" aria-hidden="true"></i>
</button>

<!-- ❌ INCORRECTO -->
<button type="button" class="wvs-convert-btn">
    <i class="icon-convert"></i>
</button>
```

### **🎯 18. SEMÁNTICA HTML**
```html
<!-- ✅ CORRECTO -->
<section class="wvs-currency-converter" role="region" aria-labelledby="converter-title">
    <h2 id="converter-title">Conversor de Moneda</h2>
    <form class="wvs-converter-form">
        <!-- Formulario -->
    </form>
</section>

<!-- ❌ INCORRECTO -->
<div class="wvs-currency-converter">
    <div class="title">Conversor de Moneda</div>
    <div class="form">
        <!-- Formulario -->
    </div>
</div>
```

---

## **⚡ REGLAS DE PERFORMANCE**

### **💾 19. CACHING**
```php
// ✅ CORRECTO
$cached_data = get_transient('wvs_bcv_rate_' . $date);
if (false === $cached_data) {
    $cached_data = fetch_bcv_rate($date);
    set_transient('wvs_bcv_rate_' . $date, $cached_data, HOUR_IN_SECONDS);
}

// ❌ INCORRECTO
$data = fetch_bcv_rate($date); // Sin cache
```

### **📦 20. LAZY LOADING**
```php
// ✅ CORRECTO
function wvs_enqueue_scripts() {
    if (is_product() || is_shop()) {
        wp_enqueue_script('wvs-converter');
    }
}

// ❌ INCORRECTO
function wvs_enqueue_scripts() {
    wp_enqueue_script('wvs-converter'); // Siempre cargado
}
```

### **🗜️ 21. MINIFICACIÓN**
```php
// ✅ CORRECTO
wp_enqueue_script(
    'wvs-converter',
    plugin_dir_url(__FILE__) . 'assets/js/converter.min.js',
    array('jquery'),
    WOOCOMMERCE_VENEZUELA_SUITE_VERSION,
    true
);

// ❌ INCORRECTO
wp_enqueue_script(
    'wvs-converter',
    plugin_dir_url(__FILE__) . 'assets/js/converter.js',
    array('jquery'),
    '1.0.0',
    true
);
```

---

## **🧪 REGLAS DE TESTING**

### **🔬 22. PRUEBAS UNITARIAS**
```php
// ✅ CORRECTO
class Test_BCV_Rate_Calculator extends WP_UnitTestCase {
    public function test_calculate_conversion() {
        $calculator = new Woocommerce_Venezuela_Suite_BCV_Rate_Calculator();
        $result = $calculator->calculate_conversion(100, 'USD', 'VES');
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }
}

// ❌ INCORRECTO
// Sin pruebas unitarias
```

### **🔗 23. PRUEBAS DE INTEGRACIÓN**
```php
// ✅ CORRECTO
class Test_WooCommerce_Integration extends WP_UnitTestCase {
    public function test_price_display_integration() {
        $product = WC_Helper_Product::create_simple_product();
        $price_display = new Woocommerce_Venezuela_Suite_Price_Display();
        $formatted_price = $price_display->format_price($product->get_price());
        $this->assertStringContainsString('VES', $formatted_price);
    }
}

// ❌ INCORRECTO
// Sin pruebas de integración
```

---

## **📚 REGLAS DE DOCUMENTACIÓN**

### **📝 24. DOCUMENTACIÓN DE CÓDIGO**
```php
/**
 * Calcula la conversión de moneda usando la tasa BCV
 *
 * @since 1.0.0
 * @param float $amount Cantidad a convertir
 * @param string $from_currency Moneda origen
 * @param string $to_currency Moneda destino
 * @return float|false Cantidad convertida o false en caso de error
 */
public function calculate_conversion($amount, $from_currency, $to_currency) {
    // Implementación
}

// ❌ INCORRECTO
function calculate_conversion($amount, $from_currency, $to_currency) {
    // Sin documentación
}
```

### **📖 25. DOCUMENTACIÓN DE USUARIO**
```markdown
<!-- ✅ CORRECTO -->
# WooCommerce Venezuela Suite

## Instalación
1. Sube el plugin a `/wp-content/plugins/`
2. Activa el plugin desde el panel de administración
3. Configura los módulos necesarios

## Configuración
### Módulo BCV Integration
- Configura la frecuencia de actualización
- Establece fuentes de respaldo
- Configura alertas de tasa

<!-- ❌ INCORRECTO -->
# Plugin
Instala y usa
```

---

## **🌍 REGLAS DE INTERNACIONALIZACIÓN**

### **🔤 26. FUNCIONES DE TRADUCCIÓN**
```php
// ✅ CORRECTO
$text = __('Convertir Moneda', 'woocommerce-venezuela-suite');
$text = _x('Rate', 'Exchange rate', 'woocommerce-venezuela-suite');
$text = _n('1 rate', '%d rates', $count, 'woocommerce-venezuela-suite');

// ❌ INCORRECTO
$text = 'Convertir Moneda';
$text = 'Rate';
$text = '1 rate';
```

### **📅 27. FORMATEO REGIONAL**
```php
// ✅ CORRECTO
$formatted_date = date_i18n(get_option('date_format'), $timestamp);
$formatted_number = number_format_i18n($number, 2);
$formatted_currency = wc_price($amount, array('currency' => 'VES'));

// ❌ INCORRECTO
$formatted_date = date('Y-m-d', $timestamp);
$formatted_number = number_format($number, 2);
$formatted_currency = '$' . $amount;
```

---

## **🔧 REGLAS DE DESARROLLO**

### **📦 28. GESTIÓN DE DEPENDENCIAS**
```php
// ✅ CORRECTO
if (!class_exists('WooCommerce')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo __('WooCommerce Venezuela Suite requiere WooCommerce para funcionar.', 'woocommerce-venezuela-suite');
        echo '</p></div>';
    });
    return;
}

// ❌ INCORRECTO
// Sin verificación de dependencias
```

### **🔄 29. GESTIÓN DE VERSIONES**
```php
// ✅ CORRECTO
define('WOOCOMMERCE_VENEZUELA_SUITE_VERSION', '1.0.0');
define('WOOCOMMERCE_VENEZUELA_SUITE_DB_VERSION', '1.0.0');

function wvs_check_database_version() {
    $installed_version = get_option('wvs_db_version', '0.0.0');
    if (version_compare($installed_version, WOOCOMMERCE_VENEZUELA_SUITE_DB_VERSION, '<')) {
        wvs_upgrade_database();
    }
}

// ❌ INCORRECTO
// Sin gestión de versiones
```

### **🗑️ 30. LIMPIEZA AL DESINSTALAR**
```php
// ✅ CORRECTO
// uninstall.php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Eliminar opciones
delete_option('wvs_settings');
delete_option('wvs_db_version');

// Eliminar tablas
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wvs_bcv_rates");

// ❌ INCORRECTO
// Sin limpieza al desinstalar
```

---

## **🎯 REGLAS ESPECÍFICAS DEL PROYECTO**

### **🇻🇪 31. ESPECIFICIDADES VENEZOLANAS**
```php
// ✅ CORRECTO
class Woocommerce_Venezuela_Suite_Venezuelan_Validator {
    public function validate_rif($rif) {
        // Validación específica para RIF venezolano
        $pattern = '/^[JGVE]-[0-9]{8}-[0-9]$/';
        return preg_match($pattern, $rif);
    }
    
    public function validate_cedula($cedula) {
        // Validación específica para cédula venezolana
        $pattern = '/^[VE]-[0-9]{7,8}$/';
        return preg_match($pattern, $cedula);
    }
}

// ❌ INCORRECTO
// Sin validaciones específicas venezolanas
```

### **💰 32. MANEJO DE MONEDAS**
```php
// ✅ CORRECTO
class Woocommerce_Venezuela_Suite_Currency_Handler {
    public function format_ves_price($amount) {
        return number_format($amount, 2, ',', '.') . ' VES';
    }
    
    public function convert_usd_to_ves($usd_amount) {
        $rate = $this->get_bcv_rate();
        return $usd_amount * $rate;
    }
}

// ❌ INCORRECTO
// Sin manejo específico de monedas venezolanas
```

### **📊 33. INTEGRACIÓN CON BCV**
```php
// ✅ CORRECTO
class Woocommerce_Venezuela_Suite_BCV_Integration {
    public function get_current_rate() {
        $cached_rate = get_transient('wvs_bcv_rate');
        if (false === $cached_rate) {
            $cached_rate = $this->fetch_bcv_rate();
            set_transient('wvs_bcv_rate', $cached_rate, 30 * MINUTE_IN_SECONDS);
        }
        return $cached_rate;
    }
}

// ❌ INCORRECTO
// Sin integración con BCV
```

---

## **✅ CHECKLIST DE CUMPLIMIENTO**

### **🔍 34. VERIFICACIÓN PRE-COMMIT**
- [ ] **Seguridad**: Validación y sanitización implementada
- [ ] **Performance**: Cache y optimizaciones aplicadas
- [ ] **Accesibilidad**: Atributos ARIA y semántica HTML
- [ ] **Responsive**: Diseño adaptable a móviles
- [ ] **Internacionalización**: Strings traducibles
- [ ] **Documentación**: PHPDoc completo
- [ ] **Testing**: Pruebas unitarias pasando
- [ ] **Estándares**: Código siguiendo WordPress Coding Standards

### **🚀 35. VERIFICACIÓN PRE-RELEASE**
- [ ] **Compatibilidad**: Probado con WordPress/WooCommerce
- [ ] **Performance**: Tiempo de carga < 2 segundos
- [ ] **Seguridad**: Sin vulnerabilidades críticas
- [ ] **Usabilidad**: Interfaz intuitiva y clara
- [ ] **Documentación**: Manual de usuario completo
- [ ] **Testing**: Pruebas de integración pasando
- [ ] **Limpieza**: Código sin comentarios de debug
- [ ] **Versionado**: Changelog actualizado

---

## **🎯 CONCLUSIÓN**

**Estas reglas garantizan**:
- ✅ **Código limpio** y mantenible
- ✅ **Seguridad robusta** desde el diseño
- ✅ **Performance optimizado** para producción
- ✅ **Compatibilidad** con WordPress/WooCommerce
- ✅ **Escalabilidad** para futuras funcionalidades
- ✅ **Cumplimiento** con estándares de la industria
- ✅ **Especificidad** para el mercado venezolano

**¿Te gustaría que procedamos con la implementación siguiendo estas reglas o prefieres ajustar algún aspecto específico?**
