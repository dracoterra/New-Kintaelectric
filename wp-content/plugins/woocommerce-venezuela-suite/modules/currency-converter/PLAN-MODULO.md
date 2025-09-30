# 💱 **PLAN MÓDULO: Currency Converter - MEJORADO**

## **🎯 OBJETIVO DEL MÓDULO**

Proporcionar conversión automática entre USD y VES (Bolívares Venezolanos) en tiempo real con **ubicación personalizable**, **integración completa con Elementor**, **switches independientes** y **personalización avanzada de apariencia**, integrando con el módulo BCV Integration para obtener tipos de cambio actualizados.

---

## **📋 FUNCIONALIDADES PRINCIPALES**

## **📋 FUNCIONALIDADES PRINCIPALES MEJORADAS**

### **🎨 1. Ubicación Personalizable**
- **Selector de ubicación** en admin (Shop, Productos individuales, Elementor)
- **Exclusión automática** de header y footer
- **Posicionamiento** específico en páginas de productos
- **Integración** con hooks de WooCommerce personalizados
- **Shortcodes** para ubicaciones específicas
- **Widgets** personalizables para sidebar

### **🔧 2. Integración Completa con Elementor**
- **Widget nativo** de Elementor para Currency Converter
- **Controles avanzados** de diseño y comportamiento
- **Templates** predefinidos para diferentes estilos
- **Responsive** design automático
- **Animaciones** y efectos visuales
- **Condiciones** de visualización avanzadas

### **⚡ 3. Switches Independientes**
- **Switch principal** para activar/desactivar conversión
- **Switch por producto** individual
- **Switch por categoría** de productos
- **Switch por página** específica
- **Switch por usuario** (roles)
- **Switch por ubicación** geográfica

### **🎨 4. Personalización Avanzada de Apariencia**
- **Temas visuales** predefinidos (Minimal, Modern, Classic)
- **Editor de CSS** integrado
- **Colores** personalizables (fondo, texto, bordes)
- **Tipografías** configurables
- **Tamaños** ajustables
- **Iconos** personalizables
- **Animaciones** configurables

### **🔄 5. Conversión en Tiempo Real**
- **Conversión automática** USD ↔ VES durante checkout
- **Actualización dinámica** de precios en frontend
- **Cache inteligente** para conversiones frecuentes
- **Redondeo inteligente** para evitar decimales excesivos en VES
- **Múltiples formatos** de moneda venezolana

### **💰 6. Gestión de Precios Avanzada**
- **Dual pricing** (mostrar USD y VES)
- **Conversión automática** de productos
- **Actualización masiva** de precios
- **Historial** de conversiones por producto
- **Alertas** cuando márgenes son muy bajos
- **Márgenes** configurables por producto

---

## **🏗️ ESTRUCTURA DEL MÓDULO**

## **🏗️ ESTRUCTURA DEL MÓDULO MEJORADA**

### **📁 Archivos Principales**
```
modules/currency-converter/
├── currency-converter.php                        # 🚀 Bootstrap del módulo
├── includes/
│   ├── class-converter-core.php                  # ⚙️ Funcionalidad principal
│   ├── class-converter-calculator.php            # 🧮 Calculadora de conversiones
│   ├── class-converter-formatter.php             # 📝 Formateador de monedas
│   ├── class-converter-cache.php                 # 💾 Cache de conversiones
│   ├── class-converter-location-manager.php      # 📍 Gestor de ubicaciones
│   ├── class-converter-switch-manager.php        # ⚡ Gestor de switches
│   ├── class-converter-appearance-manager.php    # 🎨 Gestor de apariencia
│   ├── class-converter-elementor-integration.php # 🔧 Integración Elementor
│   ├── class-converter-shortcodes.php            # 📝 Shortcodes personalizados
│   ├── class-converter-widgets.php               # 📊 Widgets personalizados
│   ├── class-converter-admin.php                 # 👨‍💼 Panel administrativo
│   └── class-converter-public.php                # 🌐 Frontend público
├── admin/
│   ├── css/currency-converter-admin.css         # 🎨 Estilos admin
│   ├── js/currency-converter-admin.js           # 📱 JavaScript admin
│   └── partials/
│       ├── converter-settings.php               # ⚙️ Configuración general
│       ├── converter-location-settings.php       # 📍 Configuración ubicaciones
│       ├── converter-switch-settings.php        # ⚡ Configuración switches
│       ├── converter-appearance-settings.php    # 🎨 Configuración apariencia
│       ├── converter-elementor-settings.php     # 🔧 Configuración Elementor
│       ├── converter-status.php                  # 📊 Estado del sistema
│       ├── converter-history.php                 # 📋 Historial de conversiones
│       └── converter-bulk-update.php             # 🔄 Actualización masiva
├── public/
│   ├── css/currency-converter-public.css        # 🎨 Estilos públicos
│   ├── js/currency-converter-public.js          # 📱 JavaScript público
│   └── css/themes/
│       ├── minimal.css                          # 🎨 Tema minimal
│       ├── modern.css                           # 🎨 Tema modern
│       └── classic.css                           # 🎨 Tema classic
├── templates/
│   ├── price-display.php                        # 💰 Display de precios
│   ├── currency-selector.php                    # 🔄 Selector de moneda
│   ├── conversion-widget.php                     # 📊 Widget de conversión
│   ├── elementor-widget.php                     # 🔧 Widget Elementor
│   └── themes/
│       ├── minimal-template.php                 # 🎨 Template minimal
│       ├── modern-template.php                  # 🎨 Template modern
│       └── classic-template.php                 # 🎨 Template classic
├── elementor/
│   ├── widgets/
│   │   ├── class-currency-converter-widget.php  # 🔧 Widget Elementor
│   │   └── controls/
│   │       ├── location-control.php              # 📍 Control ubicación
│   │       ├── appearance-control.php            # 🎨 Control apariencia
│   │       └── switch-control.php                # ⚡ Control switches
│   ├── templates/
│   │   ├── currency-converter-templates.json    # 📋 Templates Elementor
│   │   └── presets/
│   │       ├── shop-preset.json                  # 🛒 Preset para shop
│   │       ├── product-preset.json              # 📦 Preset para productos
│   │       └── custom-preset.json               # ⚙️ Preset personalizado
│   └── assets/
│       ├── css/elementor-widget.css             # 🎨 Estilos widget
│       └── js/elementor-widget.js               # 📱 JavaScript widget
├── shortcodes/
│   ├── class-converter-shortcode.php             # 📝 Shortcode principal
│   ├── class-price-shortcode.php                 # 💰 Shortcode de precios
│   ├── class-selector-shortcode.php              # 🔄 Shortcode selector
│   ├── class-dolar-dia-shortcode.php              # 💵 Shortcode dólar del día
│   ├── class-dolar-historico-shortcode.php        # 📈 Shortcode histórico
│   └── class-widget-shortcode.php                 # 📊 Shortcode widget completo
├── widgets/
│   ├── class-converter-widget.php                # 📊 Widget sidebar
│   └── class-price-widget.php                    # 💰 Widget de precios
├── assets/
│   ├── images/
│   │   ├── themes/                               # 🎨 Imágenes por tema
│   │   └── icons/                                # 🎨 Iconos personalizados
│   ├── js/
│   │   ├── location-manager.js                   # 📍 JavaScript ubicaciones
│   │   ├── switch-manager.js                     # ⚡ JavaScript switches
│   │   ├── appearance-manager.js                 # 🎨 JavaScript apariencia
│   │   └── elementor-integration.js              # 🔧 JavaScript Elementor
│   └── css/
│       ├── themes/                               # 🎨 Estilos por tema
│       └── custom/                               # 🎨 Estilos personalizados
├── languages/
│   └── currency-converter.pot                    # 🌍 Traducciones
├── tests/
│   ├── test-converter-calculator.php             # 🧪 Tests del calculador
│   ├── test-converter-formatter.php              # 🧪 Tests del formateador
│   ├── test-converter-location.php               # 🧪 Tests ubicaciones
│   ├── test-converter-switches.php               # 🧪 Tests switches
│   ├── test-converter-appearance.php             # 🧪 Tests apariencia
│   ├── test-converter-elementor.php              # 🧪 Tests Elementor
│   └── test-converter-integration.php            # 🧪 Tests de integración
├── PLAN-MODULO.md                                # 📋 Este archivo
├── README.md                                      # 📖 Documentación
└── uninstall.php                                  # 🗑️ Limpieza al eliminar
```

---

## **🔧 IMPLEMENTACIÓN TÉCNICA**

## **🔧 IMPLEMENTACIÓN TÉCNICA MEJORADA**

### **📊 Base de Datos Extendida**
```sql
-- Tabla principal de conversiones
CREATE TABLE wp_wvs_conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    original_price DECIMAL(10,2) NOT NULL,
    original_currency VARCHAR(3) NOT NULL,
    converted_price DECIMAL(10,2) NOT NULL,
    converted_currency VARCHAR(3) NOT NULL,
    exchange_rate DECIMAL(10,4) NOT NULL,
    conversion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_conversion_date (conversion_date)
);

-- Tabla de configuración de ubicaciones
CREATE TABLE wp_wvs_converter_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(100) NOT NULL,
    location_type ENUM('shop', 'product', 'elementor', 'shortcode', 'widget') NOT NULL,
    location_hook VARCHAR(100),
    priority INT DEFAULT 10,
    is_active BOOLEAN DEFAULT TRUE,
    conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_location_type (location_type),
    INDEX idx_is_active (is_active)
);

-- Tabla de switches independientes
CREATE TABLE wp_wvs_converter_switches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    switch_name VARCHAR(100) NOT NULL,
    switch_type ENUM('global', 'product', 'category', 'page', 'user', 'location') NOT NULL,
    target_id INT,
    is_enabled BOOLEAN DEFAULT TRUE,
    conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_switch_type (switch_type),
    INDEX idx_target_id (target_id),
    INDEX idx_is_enabled (is_enabled)
);

-- Tabla de temas y apariencia
CREATE TABLE wp_wvs_converter_themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theme_name VARCHAR(50) NOT NULL,
    theme_type ENUM('minimal', 'modern', 'classic', 'custom') NOT NULL,
    css_custom TEXT,
    colors JSON,
    fonts JSON,
    sizes JSON,
    animations JSON,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_theme_type (theme_type),
    INDEX idx_is_active (is_active)
);

-- Tabla de configuración general
CREATE TABLE wp_wvs_currency_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **⚙️ Clase Principal Converter Core Mejorada**
```php
class Woocommerce_Venezuela_Suite_Converter_Core {
    
    private $calculator;
    private $formatter;
    private $cache;
    private $location_manager;
    private $switch_manager;
    private $appearance_manager;
    private $elementor_integration;
    private $shortcodes;
    private $widgets;
    private $bcv_integration;
    
    public function __construct() {
        $this->calculator = new Woocommerce_Venezuela_Suite_Converter_Calculator();
        $this->formatter = new Woocommerce_Venezuela_Suite_Converter_Formatter();
        $this->cache = new Woocommerce_Venezuela_Suite_Converter_Cache();
        $this->location_manager = new Woocommerce_Venezuela_Suite_Converter_Location_Manager();
        $this->switch_manager = new Woocommerce_Venezuela_Suite_Converter_Switch_Manager();
        $this->appearance_manager = new Woocommerce_Venezuela_Suite_Converter_Appearance_Manager();
        $this->elementor_integration = new Woocommerce_Venezuela_Suite_Converter_Elementor_Integration();
        $this->shortcodes = new Woocommerce_Venezuela_Suite_Converter_Shortcodes();
        $this->widgets = new Woocommerce_Venezuela_Suite_Converter_Widgets();
        $this->bcv_integration = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
    }
    
    public function convert_price($amount, $from_currency, $to_currency) {
        // Conversión principal de precios
    }
    
    public function format_price($amount, $currency) {
        // Formateo de precios según moneda
    }
    
    public function get_exchange_rate($from_currency, $to_currency) {
        // Obtención de tipo de cambio
    }
    
    public function should_display_converter($context = '') {
        // Verifica si debe mostrar el conversor según switches
        return $this->switch_manager->should_display($context);
    }
    
    public function get_display_location($context = '') {
        // Obtiene ubicación de visualización
        return $this->location_manager->get_location($context);
    }
    
    public function get_appearance_settings() {
        // Obtiene configuración de apariencia
        return $this->appearance_manager->get_settings();
    }
}
```

### **📍 Location Manager**
```php
class Woocommerce_Venezuela_Suite_Converter_Location_Manager {
    
    private $locations = array();
    
    public function __construct() {
        $this->load_locations();
        $this->register_hooks();
    }
    
    public function register_location($name, $type, $hook = '', $priority = 10, $conditions = '') {
        // Registra nueva ubicación
        $this->locations[$name] = array(
            'type' => $type,
            'hook' => $hook,
            'priority' => $priority,
            'conditions' => $conditions,
            'active' => true
        );
        
        if ($hook) {
            add_action($hook, array($this, 'render_converter'), $priority);
        }
    }
    
    public function get_location($context = '') {
        // Obtiene ubicación según contexto
        foreach ($this->locations as $name => $location) {
            if ($this->matches_context($location, $context)) {
                return $location;
            }
        }
        return null;
    }
    
    public function render_converter($context = '') {
        // Renderiza el conversor en la ubicación especificada
        if ($this->should_display_converter($context)) {
            $appearance = $this->appearance_manager->get_settings();
            $this->load_template($appearance['theme'], $context);
        }
    }
    
    private function matches_context($location, $context) {
        // Verifica si la ubicación coincide con el contexto
        if (empty($location['conditions'])) {
            return true;
        }
        
        $conditions = json_decode($location['conditions'], true);
        
        foreach ($conditions as $condition => $value) {
            if (!$this->check_condition($condition, $value, $context)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function check_condition($condition, $value, $context) {
        // Verifica condición específica
        switch ($condition) {
            case 'is_shop':
                return is_shop() === $value;
            case 'is_product':
                return is_product() === $value;
            case 'is_product_category':
                return is_product_category($value);
            case 'is_page':
                return is_page($value);
            case 'user_role':
                return current_user_can($value);
            default:
                return true;
        }
    }
}
```

### **⚡ Switch Manager**
```php
class Woocommerce_Venezuela_Suite_Converter_Switch_Manager {
    
    private $switches = array();
    
    public function __construct() {
        $this->load_switches();
    }
    
    public function add_switch($name, $type, $target_id = null, $conditions = '') {
        // Añade nuevo switch
        $this->switches[$name] = array(
            'type' => $type,
            'target_id' => $target_id,
            'conditions' => $conditions,
            'enabled' => true
        );
    }
    
    public function should_display($context = '') {
        // Verifica si debe mostrar el conversor
        foreach ($this->switches as $name => $switch) {
            if (!$this->check_switch($switch, $context)) {
                return false;
            }
        }
        return true;
    }
    
    private function check_switch($switch, $context) {
        // Verifica switch específico
        if (!$switch['enabled']) {
            return false;
        }
        
        switch ($switch['type']) {
            case 'global':
                return true;
            case 'product':
                return is_product() && get_the_ID() == $switch['target_id'];
            case 'category':
                return is_product_category($switch['target_id']);
            case 'page':
                return is_page($switch['target_id']);
            case 'user':
                return current_user_can($switch['target_id']);
            case 'location':
                return $this->check_location_condition($switch['target_id'], $context);
            default:
                return true;
        }
    }
    
    private function check_location_condition($location, $context) {
        // Verifica condición de ubicación geográfica
        // Implementar según necesidades específicas
        return true;
    }
}
```

### **🎨 Appearance Manager**
```php
class Woocommerce_Venezuela_Suite_Converter_Appearance_Manager {
    
    private $themes = array();
    private $current_theme = 'modern';
    
    public function __construct() {
        $this->load_themes();
        $this->register_theme_hooks();
    }
    
    public function get_settings() {
        // Obtiene configuración de apariencia actual
        $theme = $this->get_current_theme();
        return array(
            'theme' => $theme['name'],
            'colors' => $theme['colors'],
            'fonts' => $theme['fonts'],
            'sizes' => $theme['sizes'],
            'animations' => $theme['animations'],
            'css_custom' => $theme['css_custom']
        );
    }
    
    public function apply_theme($theme_name) {
        // Aplica tema específico
        if (isset($this->themes[$theme_name])) {
            $this->current_theme = $theme_name;
            $this->enqueue_theme_assets();
        }
    }
    
    public function customize_appearance($customizations) {
        // Aplica personalizaciones
        $theme = $this->get_current_theme();
        
        if (isset($customizations['colors'])) {
            $theme['colors'] = array_merge($theme['colors'], $customizations['colors']);
        }
        
        if (isset($customizations['fonts'])) {
            $theme['fonts'] = array_merge($theme['fonts'], $customizations['fonts']);
        }
        
        if (isset($customizations['css_custom'])) {
            $theme['css_custom'] = $customizations['css_custom'];
        }
        
        return $theme;
    }
    
    private function load_themes() {
        // Carga temas disponibles
        $this->themes = array(
            'minimal' => array(
                'name' => 'Minimal',
                'colors' => array(
                    'primary' => '#333333',
                    'secondary' => '#666666',
                    'background' => '#ffffff',
                    'border' => '#e0e0e0'
                ),
                'fonts' => array(
                    'family' => 'Arial, sans-serif',
                    'size' => '14px',
                    'weight' => '400'
                ),
                'sizes' => array(
                    'padding' => '10px',
                    'margin' => '5px',
                    'border_radius' => '3px'
                ),
                'animations' => array(
                    'hover' => 'fade',
                    'transition' => '0.3s ease'
                ),
                'css_custom' => ''
            ),
            'modern' => array(
                'name' => 'Modern',
                'colors' => array(
                    'primary' => '#007cba',
                    'secondary' => '#005a87',
                    'background' => '#f8f9fa',
                    'border' => '#dee2e6'
                ),
                'fonts' => array(
                    'family' => 'Segoe UI, sans-serif',
                    'size' => '16px',
                    'weight' => '500'
                ),
                'sizes' => array(
                    'padding' => '15px',
                    'margin' => '10px',
                    'border_radius' => '8px'
                ),
                'animations' => array(
                    'hover' => 'scale',
                    'transition' => '0.2s ease'
                ),
                'css_custom' => ''
            ),
            'classic' => array(
                'name' => 'Classic',
                'colors' => array(
                    'primary' => '#2c3e50',
                    'secondary' => '#34495e',
                    'background' => '#ecf0f1',
                    'border' => '#bdc3c7'
                ),
                'fonts' => array(
                    'family' => 'Georgia, serif',
                    'size' => '15px',
                    'weight' => '400'
                ),
                'sizes' => array(
                    'padding' => '12px',
                    'margin' => '8px',
                    'border_radius' => '5px'
                ),
                'animations' => array(
                    'hover' => 'slide',
                    'transition' => '0.4s ease'
                ),
                'css_custom' => ''
            )
        );
    }
    
    private function enqueue_theme_assets() {
        // Encola assets del tema
        $theme = $this->get_current_theme();
        wp_enqueue_style(
            'wvs-converter-theme-' . $theme['name'],
            plugin_dir_url(__FILE__) . 'public/css/themes/' . strtolower($theme['name']) . '.css',
            array(),
            WOOCOMMERCE_VENEZUELA_SUITE_VERSION
        );
    }
}
```

### **💵 Dólar del Día Shortcode**
```php
class Woocommerce_Venezuela_Suite_Dolar_Dia_Shortcode {
    
    public function __construct() {
        add_shortcode('wvs_dolar_dia', array($this, 'render_dolar_dia'));
    }
    
    public function render_dolar_dia($atts) {
        // Atributos por defecto
        $atts = shortcode_atts(array(
            'format' => 'full',           // full, simple, compact
            'show_date' => true,          // Mostrar fecha
            'show_time' => true,          // Mostrar hora
            'show_source' => true,        // Mostrar fuente (BCV)
            'theme' => 'default',         // Tema visual
            'currency' => 'VES',          // Moneda de destino
            'decimals' => 2,              // Decimales
            'prefix' => '',               // Prefijo personalizado
            'suffix' => '',               // Sufijo personalizado
            'update_interval' => 30,      // Intervalo de actualización (minutos)
            'cache_duration' => 1800,     // Duración del cache (segundos)
            'fallback_text' => 'Tipo de cambio no disponible', // Texto de respaldo
            'css_class' => 'wvs-dolar-dia', // Clase CSS personalizada
            'inline' => false,            // Mostrar inline
            'responsive' => true          // Diseño responsive
        ), $atts);
        
        // Obtener tipo de cambio actual
        $bcv_integration = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
        $current_rate = $bcv_integration->get_current_rate();
        
        if (!$current_rate) {
            return $this->render_fallback($atts);
        }
        
        // Formatear según configuración
        $formatted_rate = $this->format_rate($current_rate, $atts);
        
        // Generar HTML
        return $this->generate_html($formatted_rate, $atts);
    }
    
    private function format_rate($rate, $atts) {
        $formatted = array();
        
        // Formatear número
        $formatted['rate'] = number_format($rate, $atts['decimals'], ',', '.');
        
        // Agregar prefijo y sufijo
        $formatted['display'] = $atts['prefix'] . $formatted['rate'] . $atts['suffix'];
        
        // Agregar información adicional
        if ($atts['show_date']) {
            $formatted['date'] = date('d/m/Y');
        }
        
        if ($atts['show_time']) {
            $formatted['time'] = date('H:i');
        }
        
        if ($atts['show_source']) {
            $formatted['source'] = 'BCV';
        }
        
        return $formatted;
    }
    
    private function generate_html($formatted, $atts) {
        $css_class = $atts['css_class'];
        $theme_class = 'wvs-theme-' . $atts['theme'];
        $format_class = 'wvs-format-' . $atts['format'];
        $responsive_class = $atts['responsive'] ? 'wvs-responsive' : '';
        
        $html = '<div class="' . esc_attr($css_class . ' ' . $theme_class . ' ' . $format_class . ' ' . $responsive_class) . '"';
        
        if ($atts['update_interval'] > 0) {
            $html .= ' data-update-interval="' . esc_attr($atts['update_interval']) . '"';
        }
        
        $html .= '>';
        
        switch ($atts['format']) {
            case 'simple':
                $html .= '<span class="wvs-rate">' . esc_html($formatted['display']) . '</span>';
                break;
                
            case 'compact':
                $html .= '<span class="wvs-rate-compact">' . esc_html($formatted['rate']) . '</span>';
                if ($atts['show_source']) {
                    $html .= '<span class="wvs-source">' . esc_html($formatted['source']) . '</span>';
                }
                break;
                
            case 'full':
            default:
                $html .= '<div class="wvs-rate-container">';
                $html .= '<span class="wvs-rate">' . esc_html($formatted['display']) . '</span>';
                
                if ($atts['show_date'] || $atts['show_time'] || $atts['show_source']) {
                    $html .= '<div class="wvs-meta">';
                    
                    if ($atts['show_date']) {
                        $html .= '<span class="wvs-date">' . esc_html($formatted['date']) . '</span>';
                    }
                    
                    if ($atts['show_time']) {
                        $html .= '<span class="wvs-time">' . esc_html($formatted['time']) . '</span>';
                    }
                    
                    if ($atts['show_source']) {
                        $html .= '<span class="wvs-source">' . esc_html($formatted['source']) . '</span>';
                    }
                    
                    $html .= '</div>';
                }
                
                $html .= '</div>';
                break;
        }
        
        $html .= '</div>';
        
        // Encolar estilos si es necesario
        $this->enqueue_styles($atts);
        
        return $html;
    }
    
    private function render_fallback($atts) {
        $css_class = $atts['css_class'] . ' wvs-fallback';
        
        return '<div class="' . esc_attr($css_class) . '">' . 
               esc_html($atts['fallback_text']) . 
               '</div>';
    }
    
    private function enqueue_styles($atts) {
        // Encolar estilos del tema
        wp_enqueue_style(
            'wvs-dolar-dia-' . $atts['theme'],
            plugin_dir_url(__FILE__) . 'public/css/shortcodes/dolar-dia-' . $atts['theme'] . '.css',
            array(),
            WOOCOMMERCE_VENEZUELA_SUITE_VERSION
        );
        
        // Encolar JavaScript para actualización automática
        if ($atts['update_interval'] > 0) {
            wp_enqueue_script(
                'wvs-dolar-dia-auto-update',
                plugin_dir_url(__FILE__) . 'public/js/shortcodes/dolar-dia-auto-update.js',
                array('jquery'),
                WOOCOMMERCE_VENEZUELA_SUITE_VERSION,
                true
            );
            
            wp_localize_script('wvs-dolar-dia-auto-update', 'wvs_dolar_dia', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wvs_dolar_dia_nonce'),
                'update_interval' => $atts['update_interval'] * 60 * 1000 // Convertir a milisegundos
            ));
        }
    }
}
```

### **📈 Dólar Histórico Shortcode**
```php
class Woocommerce_Venezuela_Suite_Dolar_Historico_Shortcode {
    
    public function __construct() {
        add_shortcode('wvs_dolar_historico', array($this, 'render_dolar_historico'));
    }
    
    public function render_dolar_historico($atts) {
        // Atributos por defecto
        $atts = shortcode_atts(array(
            'days' => 7,                 // Días de historial
            'format' => 'table',         // table, chart, list
            'show_chart' => true,        // Mostrar gráfico
            'chart_type' => 'line',      // line, bar, area
            'theme' => 'default',        // Tema visual
            'responsive' => true,         // Diseño responsive
            'css_class' => 'wvs-dolar-historico' // Clase CSS personalizada
        ), $atts);
        
        // Obtener historial
        $bcv_integration = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
        $history = $bcv_integration->get_rate_history($atts['days']);
        
        if (empty($history)) {
            return '<div class="' . esc_attr($atts['css_class']) . ' wvs-no-data">No hay datos históricos disponibles</div>';
        }
        
        // Generar HTML según formato
        switch ($atts['format']) {
            case 'chart':
                return $this->render_chart($history, $atts);
            case 'list':
                return $this->render_list($history, $atts);
            case 'table':
            default:
                return $this->render_table($history, $atts);
        }
    }
    
    private function render_table($history, $atts) {
        $html = '<div class="' . esc_attr($atts['css_class']) . ' wvs-table-format">';
        $html .= '<table class="wvs-history-table">';
        $html .= '<thead><tr><th>Fecha</th><th>Tipo de Cambio</th><th>Variación</th></tr></thead>';
        $html .= '<tbody>';
        
        $previous_rate = null;
        foreach ($history as $record) {
            $date = date('d/m/Y', strtotime($record->created_at));
            $rate = number_format($record->usd_to_ves, 2, ',', '.');
            
            $variation = '';
            if ($previous_rate !== null) {
                $change = $record->usd_to_ves - $previous_rate;
                $change_percent = ($change / $previous_rate) * 100;
                $variation_class = $change >= 0 ? 'wvs-positive' : 'wvs-negative';
                $variation = '<span class="' . $variation_class . '">' . 
                           ($change >= 0 ? '+' : '') . number_format($change_percent, 2) . '%</span>';
            }
            
            $html .= '<tr>';
            $html .= '<td>' . esc_html($date) . '</td>';
            $html .= '<td>' . esc_html($rate) . ' VES</td>';
            $html .= '<td>' . $variation . '</td>';
            $html .= '</tr>';
            
            $previous_rate = $record->usd_to_ves;
        }
        
        $html .= '</tbody></table></div>';
        
        $this->enqueue_styles($atts);
        return $html;
    }
    
    private function render_chart($history, $atts) {
        $html = '<div class="' . esc_attr($atts['css_class']) . ' wvs-chart-format">';
        $html .= '<canvas id="wvs-dolar-chart-' . uniqid() . '" width="400" height="200"></canvas>';
        $html .= '</div>';
        
        // Preparar datos para el gráfico
        $chart_data = array(
            'labels' => array(),
            'data' => array()
        );
        
        foreach ($history as $record) {
            $chart_data['labels'][] = date('d/m', strtotime($record->created_at));
            $chart_data['data'][] = $record->usd_to_ves;
        }
        
        // Encolar Chart.js y datos
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        wp_enqueue_script('wvs-dolar-chart', plugin_dir_url(__FILE__) . 'public/js/shortcodes/dolar-chart.js', array('chart-js'), WOOCOMMERCE_VENEZUELA_SUITE_VERSION, true);
        
        wp_localize_script('wvs-dolar-chart', 'wvs_chart_data', array(
            'labels' => $chart_data['labels'],
            'data' => $chart_data['data'],
            'chart_type' => $atts['chart_type']
        ));
        
        $this->enqueue_styles($atts);
        return $html;
    }
    
    private function render_list($history, $atts) {
        $html = '<div class="' . esc_attr($atts['css_class']) . ' wvs-list-format">';
        $html .= '<ul class="wvs-history-list">';
        
        foreach ($history as $record) {
            $date = date('d/m/Y', strtotime($record->created_at));
            $rate = number_format($record->usd_to_ves, 2, ',', '.');
            
            $html .= '<li class="wvs-history-item">';
            $html .= '<span class="wvs-date">' . esc_html($date) . '</span>';
            $html .= '<span class="wvs-rate">' . esc_html($rate) . ' VES</span>';
            $html .= '</li>';
        }
        
        $html .= '</ul></div>';
        
        $this->enqueue_styles($atts);
        return $html;
    }
    
    private function enqueue_styles($atts) {
        wp_enqueue_style(
            'wvs-dolar-historico-' . $atts['theme'],
            plugin_dir_url(__FILE__) . 'public/css/shortcodes/dolar-historico-' . $atts['theme'] . '.css',
            array(),
            WOOCOMMERCE_VENEZUELA_SUITE_VERSION
        );
    }
}
```

---

## **⚙️ CONFIGURACIÓN DEL MÓDULO**

## **⚙️ CONFIGURACIÓN DEL MÓDULO MEJORADA**

### **🎛️ Opciones de Configuración Avanzadas**
```php
$converter_settings = array(
    // Configuración general
    'base_currency' => 'USD',                    // Moneda base del sistema
    'display_currency' => 'VES',                 // Moneda de visualización
    'rounding_method' => 'smart',                 // Método de redondeo
    'decimal_places_ves' => 2,                   // Decimales para VES
    'decimal_places_usd' => 2,                   // Decimales para USD
    'margin_threshold' => 0.05,                  // Umbral de margen mínimo
    'cache_duration' => 1800,                    // Duración del cache (30 min)
    'auto_update_prices' => true,                 // Actualización automática
    'show_dual_pricing' => true,                 // Mostrar ambas monedas
    'currency_symbol_position' => 'before',       // Posición del símbolo
    
    // Configuración de ubicaciones
    'location_settings' => array(
        'shop_enabled' => true,                   // Habilitar en shop
        'product_enabled' => true,                // Habilitar en productos
        'elementor_enabled' => true,              // Habilitar en Elementor
        'shortcode_enabled' => true,              // Habilitar shortcodes
        'widget_enabled' => true,                 // Habilitar widgets
        'exclude_header_footer' => true,          // Excluir header/footer
        'custom_hooks' => array(),                // Hooks personalizados
        'priority' => 10                          // Prioridad de hooks
    ),
    
    // Configuración de switches
    'switch_settings' => array(
        'global_switch' => true,                  // Switch global
        'product_switch' => true,                 // Switch por producto
        'category_switch' => true,                // Switch por categoría
        'page_switch' => true,                    // Switch por página
        'user_switch' => true,                    // Switch por usuario
        'location_switch' => false,               // Switch por ubicación geográfica
        'default_state' => 'enabled'              // Estado por defecto
    ),
    
    // Configuración de apariencia
    'appearance_settings' => array(
        'default_theme' => 'modern',              // Tema por defecto
        'themes_available' => array(              // Temas disponibles
            'minimal' => true,
            'modern' => true,
            'classic' => true,
            'custom' => true
        ),
        'custom_css_enabled' => true,             // Habilitar CSS personalizado
        'color_customization' => true,             // Habilitar personalización de colores
        'font_customization' => true,             // Habilitar personalización de fuentes
        'size_customization' => true,             // Habilitar personalización de tamaños
        'animation_customization' => true,         // Habilitar personalización de animaciones
        'responsive_design' => true               // Diseño responsive
    ),
    
    // Configuración de Elementor
    'elementor_settings' => array(
        'widget_enabled' => true,                 // Habilitar widget Elementor
        'category_name' => 'WooCommerce Venezuela Suite', // Nombre de categoría
        'templates_enabled' => true,               // Habilitar templates
        'presets_enabled' => true,                // Habilitar presets
        'custom_controls' => true,                // Habilitar controles personalizados
        'responsive_controls' => true             // Habilitar controles responsive
    ),
    
    // Configuración de shortcodes
    'shortcode_settings' => array(
        'converter_shortcode' => '[wvs_converter]',        // Shortcode principal
        'price_shortcode' => '[wvs_price]',                // Shortcode de precios
        'selector_shortcode' => '[wvs_currency_selector]', // Shortcode selector
        'dolar_dia_shortcode' => '[wvs_dolar_dia]',        // Shortcode dólar del día
        'dolar_historico_shortcode' => '[wvs_dolar_historico]', // Shortcode histórico
        'widget_shortcode' => '[wvs_converter_widget]',    // Shortcode widget completo
        'shortcodes_enabled' => true,                       // Habilitar shortcodes
        'shortcode_security' => true                        // Seguridad de shortcodes
    ),
    
    // Configuración de widgets
    'widget_settings' => array(
        'sidebar_widget_enabled' => true,         // Habilitar widget sidebar
        'price_widget_enabled' => true,           // Habilitar widget de precios
        'widget_cache_enabled' => true,           // Habilitar cache de widgets
        'widget_responsive' => true               // Widget responsive
    ),
    
    // Configuración de performance
    'performance_settings' => array(
        'lazy_loading' => true,                   // Carga perezosa
        'minification' => true,                   // Minificación de assets
        'compression' => true,                    // Compresión de datos
        'cdn_enabled' => false,                   // Habilitar CDN
        'cache_optimization' => true              // Optimización de cache
    )
);
```

### **📝 Ejemplos de Uso de Shortcodes**

#### **💵 Shortcode Dólar del Día**
```php
// Uso básico
[wvs_dolar_dia]

// Formato simple
[wvs_dolar_dia format="simple"]

// Formato compacto con fuente
[wvs_dolar_dia format="compact" show_source="true"]

// Formato completo personalizado
[wvs_dolar_dia format="full" show_date="true" show_time="true" show_source="true"]

// Con actualización automática cada 15 minutos
[wvs_dolar_dia update_interval="15"]

// Con tema personalizado
[wvs_dolar_dia theme="modern" css_class="mi-dolar-personalizado"]

// Con prefijo y sufijo
[wvs_dolar_dia prefix="USD = " suffix=" VES"]

// Solo número con 4 decimales
[wvs_dolar_dia format="simple" decimals="4" show_date="false" show_time="false" show_source="false"]

// Texto de respaldo personalizado
[wvs_dolar_dia fallback_text="Consultando tipo de cambio..."]
```

#### **📈 Shortcode Dólar Histórico**
```php
// Tabla de últimos 7 días
[wvs_dolar_historico]

// Gráfico de últimos 30 días
[wvs_dolar_historico days="30" format="chart"]

// Lista de últimos 14 días
[wvs_dolar_historico days="14" format="list"]

// Gráfico de barras
[wvs_dolar_historico format="chart" chart_type="bar"]

// Gráfico de área
[wvs_dolar_historico format="chart" chart_type="area"]

// Con tema personalizado
[wvs_dolar_historico theme="modern" css_class="mi-historico"]
```

#### **💰 Shortcode de Precios**
```php
// Precio de producto actual
[wvs_price product_id="123"]

// Precio con conversión específica
[wvs_price amount="100" from_currency="USD" to_currency="VES"]

// Precio con formato personalizado
[wvs_price amount="50" format="dual" show_symbol="true"]
```

#### **🔄 Shortcode Selector de Moneda**
```php
// Selector básico
[wvs_currency_selector]

// Selector con estilos personalizados
[wvs_currency_selector theme="modern" css_class="mi-selector"]

// Selector con monedas específicas
[wvs_currency_selector currencies="USD,VES,EUR"]
```

#### **📊 Shortcode Widget Completo**
```php
// Widget completo con conversor y selector
[wvs_converter_widget]

// Widget con configuración específica
[wvs_converter_widget theme="minimal" show_selector="true" show_history="true"]
```

---

## **🔄 FLUJO DE FUNCIONAMIENTO**

## **🔄 FLUJO DE FUNCIONAMIENTO MEJORADO**

### **📍 Gestión de Ubicaciones**
1. **Detección** de contexto actual (shop, producto, página)
2. **Verificación** de switches independientes
3. **Aplicación** de condiciones de ubicación
4. **Exclusión** automática de header/footer
5. **Renderizado** en ubicación específica
6. **Aplicación** de tema y personalizaciones

### **⚡ Sistema de Switches Independientes**
1. **Verificación** de switch global
2. **Evaluación** de switches específicos (producto, categoría, página)
3. **Verificación** de switches de usuario (roles)
4. **Aplicación** de condiciones geográficas
5. **Decisión** final de visualización
6. **Registro** de decisiones en logs

### **🎨 Personalización de Apariencia**
1. **Carga** del tema seleccionado
2. **Aplicación** de personalizaciones de colores
3. **Aplicación** de personalizaciones de fuentes
4. **Aplicación** de personalizaciones de tamaños
5. **Aplicación** de animaciones personalizadas
6. **Inyección** de CSS personalizado
7. **Optimización** responsive automática

### **🔧 Integración con Elementor**
1. **Registro** del widget en Elementor
2. **Carga** de controles personalizados
3. **Aplicación** de templates predefinidos
4. **Renderizado** con configuración de Elementor
5. **Sincronización** con configuración global
6. **Preview** en tiempo real

### **💱 Conversión de Precios**
1. **Recibe** precio original y monedas
2. **Verifica cache** para conversión reciente
3. **Obtiene** tipo de cambio del BCV Integration
4. **Calcula** conversión con redondeo inteligente
5. **Almacena** resultado en cache
6. **Registra** conversión en base de datos
7. **Retorna** precio convertido

### **🔄 Actualización Automática**
1. **Cron job** ejecuta cada hora
2. **Obtiene** productos con precios USD
3. **Calcula** nuevos precios en VES
4. **Verifica** márgenes mínimos
5. **Actualiza** precios si es necesario
6. **Envía alertas** si márgenes son bajos

---

## **🎨 INTEGRACIÓN CON WOOCOMMERCE**

### **🔌 Hooks de WooCommerce**
```php
// Filtros para modificar precios
add_filter('woocommerce_product_get_price', array($this, 'convert_product_price'), 10, 2);
add_filter('woocommerce_product_get_regular_price', array($this, 'convert_product_price'), 10, 2);
add_filter('woocommerce_product_get_sale_price', array($this, 'convert_product_price'), 10, 2);

// Filtros para formateo de precios
add_filter('woocommerce_price_format', array($this, 'custom_price_format'));
add_filter('woocommerce_currency_symbol', array($this, 'custom_currency_symbol'), 10, 2);

// Actions para actualización de precios
add_action('woocommerce_product_object_updated_props', array($this, 'update_converted_prices'), 10, 2);
```

### **💰 Display de Precios**
- **Precio único** en moneda seleccionada
- **Precio dual** (USD y VES)
- **Selector** de moneda en frontend
- **Widget** de conversión en tiempo real
- **Tooltip** con información de conversión

---

## **🧪 TESTING**

### **🔍 Casos de Prueba**
- **Conversión** USD a VES con diferentes valores
- **Redondeo** inteligente para VES
- **Cache** de conversiones
- **Formateo** de monedas
- **Márgenes** y alertas
- **Actualización** masiva de precios

### **📊 Datos de Prueba**
- **Productos** con precios USD
- **Tipos de cambio** simulados
- **Diferentes** valores de redondeo
- **Escenarios** de margen bajo

---

## **🚨 MANEJO DE ERRORES**

### **⚠️ Errores Comunes**
- **BCV no disponible** → Usar último tipo de cambio
- **Tipo de cambio inválido** → Usar conversión manual
- **Error de cálculo** → Log y notificación
- **Cache corrupto** → Regenerar cache

### **📋 Logging**
- **Conversiones** exitosas y fallidas
- **Cambios** de tipos de cambio
- **Alertas** de márgenes
- **Performance** de conversiones

---

## **📈 MÉTRICAS DE ÉXITO**

## **📈 MÉTRICAS DE ÉXITO MEJORADAS**

### **🎯 KPIs del Módulo Avanzados**
- **Precisión** de conversiones > 99.9%
- **Tiempo de conversión** < 50ms
- **Cache hit rate** > 90%
- **Uptime** del módulo > 99.95%
- **Tiempo de carga** de ubicaciones < 100ms
- **Performance** de switches < 10ms
- **Tiempo de renderizado** de temas < 200ms
- **Compatibilidad** con Elementor > 99%

### **📊 Métricas Específicas Detalladas**
- **Ubicaciones** configuradas y activas
- **Switches** independientes funcionando
- **Temas** aplicados correctamente
- **Widgets Elementor** renderizados
- **Shortcodes** utilizados
- **Personalizaciones** de apariencia aplicadas
- **Conversiones** por ubicación
- **Performance** por tema
- **Uso** de cada switch
- **Errores** de renderizado por ubicación
- **Tiempo** de carga por tema
- **Satisfacción** del usuario con apariencia

### **🔍 Métricas de Calidad**
- **Cobertura** de tests > 95%
- **Tiempo** de ejecución de tests < 45 segundos
- **Métricas** de performance en producción
- **Logs** de errores por día
- **Uso** de memoria optimizado
- **Tiempo** de carga de admin < 2 segundos
- **Responsive** design funcionando
- **Cross-browser** compatibility

---

## **🔗 DEPENDENCIAS**

## **🔗 DEPENDENCIAS MEJORADAS**

### **📦 Módulos Requeridos**
- **BCV Integration** (para tipos de cambio) - REQUERIDO

### **📦 Módulos que Dependen de Este**
- **Payment Gateways** (para conversiones en checkout)
- **Fiscal Compliance** (para cálculos fiscales)
- **Reports Analytics** (para métricas de conversión)

### **🔌 Integraciones con WooCommerce**
- **Product pricing** system
- **Cart/Checkout** pricing
- **Order** pricing
- **Currency** formatting
- **Hooks** personalizados

### **🔧 Integraciones con Elementor**
- **Elementor Pro** (recomendado)
- **Elementor Free** (compatible)
- **Widgets** nativos de Elementor
- **Templates** de Elementor

### **📚 Librerías y Dependencias**
- **WordPress 5.0+**
- **WooCommerce 5.0+**
- **PHP 7.4+**
- **MySQL 5.6+**
- **jQuery 3.0+**
- **Elementor 3.0+** (opcional)

---

## **📅 CRONOGRAMA DE DESARROLLO MEJORADO**

### **📅 Semana 1: Fundación y Ubicaciones**
- **Día 1-2**: Estructura del módulo y Location Manager
- **Día 3-4**: Switch Manager y sistema de switches independientes
- **Día 5**: Integración básica con WooCommerce

### **📅 Semana 2: Apariencia y Elementor**
- **Día 1-2**: Appearance Manager y sistema de temas
- **Día 3-4**: Integración completa con Elementor
- **Día 5**: Widgets y shortcodes personalizados

### **📅 Semana 3: Funcionalidades Avanzadas**
- **Día 1-2**: Panel de administración avanzado
- **Día 3-4**: Sistema de cache y performance
- **Día 5**: Testing integral y optimización

---

## **🚀 PRÓXIMOS PASOS DETALLADOS**

### **📅 Implementación Inmediata (Próximos 7 días)**
1. **Crear estructura** completa de carpetas del módulo
2. **Implementar** Location Manager básico
3. **Desarrollar** Switch Manager independiente
4. **Crear** sistema de temas básico
5. **Testing** de ubicaciones y switches

### **📅 Desarrollo Avanzado (Semanas 2-3)**
1. **Implementar** integración completa con Elementor
2. **Desarrollar** sistema de personalización avanzado
3. **Crear** widgets y shortcodes personalizados
4. **Implementar** panel de administración completo
5. **Testing** integral y optimización

### **📅 Deployment y Monitoreo**
1. **Deployment** en ambiente de staging
2. **Testing** de carga y performance
3. **Monitoreo** de métricas en producción
4. **Documentación** técnica completa
5. **Training** para usuarios finales

---

## **💡 INNOVACIONES ÚNICAS**

### **📍 Sistema de Ubicaciones Inteligente**
- **Detección automática** de contexto
- **Exclusión** automática de header/footer
- **Hooks personalizados** para ubicaciones específicas
- **Condiciones** avanzadas de visualización

### **⚡ Switches Independientes**
- **Control granular** por producto, categoría, página
- **Switches por usuario** y roles
- **Condiciones geográficas** avanzadas
- **Estado persistente** entre sesiones

### **🎨 Personalización Avanzada**
- **Temas predefinidos** profesionales
- **Editor visual** de apariencia
- **CSS personalizado** integrado
- **Responsive** design automático

### **🔧 Integración Elementor Nativa**
- **Widget nativo** con controles avanzados
- **Templates** predefinidos
- **Presets** para diferentes contextos
- **Preview** en tiempo real

---

*Este módulo mejorado transforma el Currency Converter en una solución completa, flexible y altamente personalizable, proporcionando control total sobre ubicación, apariencia y comportamiento para el e-commerce venezolano.*
