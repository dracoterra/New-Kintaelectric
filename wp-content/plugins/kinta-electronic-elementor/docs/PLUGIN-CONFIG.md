# ⚙️ Configuración del Plugin - Kinta Electric Elementor

## 🔧 **Configuración del Plugin**

### **Archivo Principal:**
`kinta-electronic-elementor.php`

### **Constantes del Plugin:**
```php
define('KEE_PLUGIN_FILE', __FILE__);
define('KEE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('KEE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KEE_PLUGIN_BASENAME', plugin_basename(__FILE__));
```

### **Versión:**
```php
const VERSION = '1.0.0';
```

## 🏗️ **Estructura del Plugin**

### **Archivos Principales:**
```
kinta-electronic-elementor/
├── kinta-electronic-elementor.php          # Archivo principal
├── includes/
│   └── class-base-widget.php              # Clase base para widgets
├── widgets/
│   ├── home-slider-kintaelectic-widget.php
│   ├── kintaelectric02-deals-widget.php
│   ├── kintaelectric03-deals-and-tabs-widget.php
│   └── kintaelectric04-products-tabs-widget.php
├── assets/
│   ├── css/
│   │   ├── kinta-electronic-elementor.css
│   │   └── kintaelectric03-countdown.css
│   ├── js/
│   │   ├── kinta-electronic-elementor.js
│   │   └── kintaelectric03-countdown.js
│   └── libs/
│       └── slick/
└── docs/
    ├── README.md
    ├── ELEMENTOR-WIDGET-USAGE.md
    ├── IMPROVEMENTS-SUMMARY.md
    ├── IMPLEMENTATION-COMPLETE.md
    ├── WIDGET-STATUS-REPORT.md
    └── PLUGIN-CONFIG.md
```

## 🎯 **Configuración de Widgets**

### **Categoría de Widgets:**
```php
'kinta-electric' => [
    'title' => __('Kinta Electric', 'kinta-electric-elementor'),
    'icon' => 'fa fa-bolt',
]
```

### **Widgets Registrados:**
1. **KEE_Home_Slider_Kintaelectic_Widget** - `home_slider_kintaelectic`
2. **KEE_Kintaelectric02_Deals_Widget** - `kintaelectric02_deals`
3. **KEE_Kintaelectric03_Deals_And_Tabs_Widget** - `kintaelectric03-deals-and-tabs`
4. **KEE_Kintaelectric04_Products_Tabs_Widget** - `kintaelectric04-products-tabs`
5. **KEE_Kintaelectric05_Dynamic_Products_Widget** - `kintaelectric05_dynamic_products`

## 🛡️ **Configuración de Seguridad**

### **Roles Personalizados:**
```php
// Rol para diseñadores de Kinta Electric
add_role('kinta_designer', 'Kinta Designer', [
    'read' => true,
    'edit_kinta_widgets' => true,
    'manage_kinta_widgets' => false,
    'edit_posts' => true,
    'edit_pages' => true,
    'edit_published_posts' => true,
    'edit_published_pages' => true,
    'publish_posts' => true,
    'publish_pages' => true,
]);
```

### **Capacidades:**
- `manage_kinta_widgets` - Gestionar widgets del plugin
- `edit_kinta_widgets` - Editar widgets del plugin

### **Nonces de Seguridad:**
- `kintaelectric03_nonce` - Para operaciones AJAX del countdown

## ⚡ **Configuración de Rendimiento**

### **Sistema de Cache:**
```php
// Cache de productos en oferta (1 hora)
$cache_key = 'kee_products_on_sale';
set_transient($cache_key, $products, HOUR_IN_SECONDS);

// Cache de categorías de productos (1 hora)
$cache_key = 'kee_product_categories';
set_transient($cache_key, $categories, HOUR_IN_SECONDS);
```

### **Hooks de Limpieza de Cache:**
```php
add_action('save_post', array($this, 'clear_cache_on_product_update'));
add_action('delete_post', array($this, 'clear_cache_on_product_update'));
add_action('created_product_cat', array($this, 'clear_cache_on_category_update'));
add_action('edited_product_cat', array($this, 'clear_cache_on_category_update'));
add_action('delete_product_cat', array($this, 'clear_cache_on_category_update'));
```

## 🎨 **Configuración de Assets**

### **Estilos (CSS):**
```php
// Librerías externas (CDN)
'animate-css' => 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'
'font-awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
'owl-carousel-css' => 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css'

// Estilos locales
'kinta-electronic-elementor-style' => KEE_PLUGIN_URL . 'assets/css/kinta-electronic-elementor.css'
'kintaelectric03-countdown' => KEE_PLUGIN_URL . 'assets/css/kintaelectric03-countdown.css'
```

### **Scripts (JavaScript):**
```php
// Librerías externas (CDN)
'owl-carousel-js' => 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js'

// Scripts locales
'kinta-electronic-elementor-script' => KEE_PLUGIN_URL . 'assets/js/kinta-electronic-elementor.js'
'kintaelectric03-countdown' => KEE_PLUGIN_URL . 'assets/js/kintaelectric03-countdown.js'
```

## 🔌 **Configuración de Integraciones**

### **WooCommerce:**
- Verificación automática: `class_exists('WooCommerce')`
- Funciones utilizadas: `wc_get_products()`, `wc_get_product()`
- Hooks: `save_post`, `delete_post` para productos

### **YITH Plugins:**
- **Wishlist:** `defined('YITH_WCWL') && YITH_WCWL`
- **Compare:** `defined('YITH_WOOCOMPARE') && YITH_WOOCOMPARE`

### **Elementor:**
- Hook de registro: `elementor/widgets/register`
- Hook de categorías: `elementor/elements/categories_registered`
- Dependencias: `bootstrap-bundle`, `electro-main`

## 📱 **Configuración Responsive**

### **Breakpoints:**
- **Mobile:** 0px - 767px
- **Tablet:** 768px - 1199px
- **Desktop:** 1200px+

### **Configuración de Columnas:**
```php
// Por defecto
'columns_mobile' => '2'
'columns_tablet' => '3'
'columns_desktop' => '4'
```

## 🎯 **Configuración de Carrusel**

### **Opciones por Defecto:**
```php
$carousel_options = [
    'items' => 4,
    'nav' => false,
    'slideSpeed' => 300,
    'dots' => true,
    'rtl' => is_rtl(),
    'paginationSpeed' => 400,
    'navText' => ['', ''],
    'margin' => 0,
    'touchDrag' => true,
    'autoplay' => false,
    'responsive' => [
        '0' => ['items' => 1],
        '768' => ['items' => 2],
        '1200' => ['items' => 4],
    ],
];
```

## 🔧 **Configuración de Debug**

### **Modo Debug:**
- Solo se carga en `WP_DEBUG = true`
- Archivo de test eliminado en producción
- Logs en `wp-content/debug.log`

### **Verificación de Widgets:**
- Test automático de registro de widgets
- Verificación de dependencias
- Validación de configuración

---

**Configuración optimizada para producción** ✅
