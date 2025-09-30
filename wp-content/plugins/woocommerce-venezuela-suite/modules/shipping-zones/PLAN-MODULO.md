# 🚚 **PLAN MÓDULO: Shipping Zones - MEJORADO PARA TODA VENEZUELA**

## **🎯 OBJETIVO DEL MÓDULO**

Implementar zonas de envío completas para **TODA VENEZUELA**, incluyendo los 24 estados, Distrito Capital y Dependencias Federales, con cálculo automático de costos de envío basado en distancia real, peso, volumen y tipo de servicio, proporcionando opciones de entrega realistas y económicas para todo el territorio nacional venezolano con **cobertura completa**, **servicios especializados por región** y **logística inteligente**.

---

## **📋 FUNCIONALIDADES PRINCIPALES**

## **📋 FUNCIONALIDADES PRINCIPALES MEJORADAS**

### **🗺️ 1. Cobertura Nacional Completa**
- **24 estados** + Distrito Capital + Dependencias Federales
- **335 municipios** con datos específicos
- **1,136 parroquias** detalladas
- **1,000+ ciudades** principales y secundarias
- **Códigos postales** completos por región
- **Zonas rurales** y urbanas diferenciadas
- **Áreas de difícil acceso** identificadas
- **Fronteras internacionales** marcadas
- **Zonas especiales** (penínsulas, islas)

### **🚛 2. Servicios de Courier Locales**
- **MRW** - Cobertura nacional completa
- **Tealca** - Servicio postal nacional
- **Domesa** - Courier especializado
- **ZIP** - Servicio express
- **Zoom** - Entrega rápida
- **Servicios locales** por estado
- **Motorizados** por ciudad
- **Transporte público** para envíos económicos
- **Servicios privados** especializados

### **💰 3. Cálculo de Costos Avanzado**
- **Por distancia real** usando APIs de mapas
- **Por peso** con escalas específicas
- **Por volumen** (dimensiones cúbicas)
- **Por tipo de servicio** (estándar, express, premium)
- **Por zona de dificultad** (urbana, rural, difícil acceso)
- **Por temporada** (época lluviosa, navidad)
- **Por combustible** (precio variable)
- **Por seguro** (valor del producto)
- **Por embalaje** especializado

### **📊 4. Gestión Logística Inteligente**
- **Tracking en tiempo real** con APIs de courier
- **Notificaciones automáticas** por SMS/WhatsApp/Email
- **Estimación precisa** de tiempos de entrega
- **Alertas de retrasos** automáticas
- **Reportes detallados** de entregas
- **Análisis de rutas** optimizadas
- **Gestión de inventario** por zona
- **Predicción de demanda** por región
- **Optimización de costos** automática

### **🎯 5. Servicios Especializados por Región**
- **Región Capital** (Caracas, Miranda, Vargas)
  - Motorizado mismo día
  - Entrega express 2-4 horas
  - Servicio nocturno
- **Región Central** (Carabobo, Aragua, Cojedes)
  - Entrega express 24 horas
  - Servicio interurbano
- **Región Occidental** (Zulia, Falcón, Lara)
  - Servicio marítimo
  - Entrega a zonas petroleras
- **Región Oriental** (Anzoátegui, Monagas, Sucre)
  - Servicio a zonas industriales
  - Entrega a puertos
- **Región Andina** (Mérida, Táchira, Trujillo)
  - Servicio a zonas montañosas
  - Entrega especializada
- **Región Llanera** (Barinas, Apure, Guárico)
  - Servicio rural
  - Entrega a haciendas
- **Región Guayana** (Bolívar, Amazonas, Delta Amacuro)
  - Servicio fluvial
  - Entrega a zonas mineras
- **Región Insular** (Nueva Esparta)
  - Servicio marítimo
  - Entrega a islas

---

## **🏗️ ESTRUCTURA DEL MÓDULO**

### **📁 Archivos Principales**
```
modules/shipping-zones/
├── shipping-zones.php                    # 🚀 Bootstrap del módulo
├── includes/
│   ├── class-shipping-core.php           # ⚙️ Funcionalidad principal
│   ├── class-venezuela-zones.php         # 🗺️ Zonas de Venezuela
│   ├── class-shipping-calculator.php     # 🧮 Calculadora de envíos
│   ├── class-distance-calculator.php    # 📏 Calculadora de distancias
│   ├── class-shipping-services.php       # 🚛 Servicios de entrega
│   ├── class-address-validator.php       # ✅ Validador de direcciones
│   ├── class-shipping-tracker.php        # 📊 Tracking de envíos
│   └── class-shipping-admin.php          # 👨‍💼 Panel administrativo
├── admin/
│   ├── css/shipping-zones-admin.css     # 🎨 Estilos admin
│   ├── js/shipping-zones-admin.js       # 📱 JavaScript admin
│   └── partials/
│       ├── zones-settings.php           # ⚙️ Configuración de zonas
│       ├── services-settings.php        # 🚛 Configuración de servicios
│       ├── rates-settings.php           # 💰 Configuración de tarifas
│       ├── zones-map.php                # 🗺️ Mapa de zonas
│       └── shipping-reports.php         # 📊 Reportes de envíos
├── public/
│   ├── css/shipping-zones-public.css    # 🎨 Estilos públicos
│   └── js/shipping-zones-public.js      # 📱 JavaScript público
├── templates/
│   ├── shipping-calculator.php          # 🧮 Calculadora de envío
│   ├── delivery-options.php             # 🚛 Opciones de entrega
│   ├── tracking-widget.php              # 📊 Widget de tracking
│   └── address-form.php                 # 📍 Formulario de dirección
├── data/
│   ├── venezuela-states.json            # 🗺️ Estados de Venezuela
│   ├── venezuela-cities.json            # 🏙️ Ciudades principales
│   ├── postal-codes.json               # 📮 Códigos postales
│   └── shipping-rates.json             # 💰 Tarifas base
├── assets/
│   ├── images/
│   │   ├── maps/                        # 🗺️ Mapas de Venezuela
│   │   └── icons/                       # 🚛 Iconos de envío
│   └── js/
│       └── maps/                        # 🗺️ JavaScript de mapas
├── languages/
│   └── shipping-zones.pot              # 🌍 Traducciones
├── tests/
│   ├── test-venezuela-zones.php         # 🧪 Tests de zonas
│   ├── test-shipping-calculator.php     # 🧪 Tests de cálculo
│   ├── test-distance-calculator.php     # 🧪 Tests de distancias
│   └── test-shipping-integration.php    # 🧪 Tests de integración
├── PLAN-MODULO.md                       # 📋 Este archivo
├── README.md                             # 📖 Documentación
└── uninstall.php                         # 🗑️ Limpieza al eliminar
```

---

## **🔧 IMPLEMENTACIÓN TÉCNICA**

### **📊 Base de Datos**
```sql
CREATE TABLE wp_wvs_shipping_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL,
    zone_type ENUM('state', 'city', 'postal') NOT NULL,
    zone_code VARCHAR(20) NOT NULL,
    parent_zone_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_zone_code (zone_code),
    INDEX idx_parent_zone (parent_zone_id)
);

CREATE TABLE wp_wvs_shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id INT NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    weight_from DECIMAL(8,2) NOT NULL,
    weight_to DECIMAL(8,2) NOT NULL,
    distance_from INT NOT NULL,
    distance_to INT NOT NULL,
    base_rate DECIMAL(10,2) NOT NULL,
    rate_per_kg DECIMAL(10,2) NOT NULL,
    rate_per_km DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_zone_service (zone_id, service_type),
    INDEX idx_weight_range (weight_from, weight_to)
);

CREATE TABLE wp_wvs_shipping_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    tracking_number VARCHAR(100) NOT NULL,
    shipping_method VARCHAR(50) NOT NULL,
    status ENUM('pending', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    current_location VARCHAR(200),
    estimated_delivery DATE,
    actual_delivery TIMESTAMP NULL,
    tracking_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number)
);
```

### **⚙️ Clase Principal Shipping Core**
```php
class Woocommerce_Venezuela_Suite_Shipping_Core {
    
    private $zones;
    private $calculator;
    private $services;
    private $tracker;
    
    public function __construct() {
        $this->zones = new Woocommerce_Venezuela_Suite_Venezuela_Zones();
        $this->calculator = new Woocommerce_Venezuela_Suite_Shipping_Calculator();
        $this->services = new Woocommerce_Venezuela_Suite_Shipping_Services();
        $this->tracker = new Woocommerce_Venezuela_Suite_Shipping_Tracker();
    }
    
    public function calculate_shipping($package) {
        // Cálculo principal de envío
    }
    
    public function get_available_zones() {
        // Obtiene zonas disponibles
    }
    
    public function validate_address($address) {
        // Valida dirección venezolana
    }
    
    public function track_shipment($tracking_number) {
        // Tracking de envío
    }
}
```

### **🗺️ Venezuela Zones**
```php
class Woocommerce_Venezuela_Suite_Venezuela_Zones {
    
    private $states = array(
        'amazonas' => 'Amazonas',
        'anzoategui' => 'Anzoátegui',
        'apure' => 'Apure',
        'aragua' => 'Aragua',
        'barinas' => 'Barinas',
        'bolivar' => 'Bolívar',
        'carabobo' => 'Carabobo',
        'cojedes' => 'Cojedes',
        'delta_amacuro' => 'Delta Amacuro',
        'distrito_capital' => 'Distrito Capital',
        'falcon' => 'Falcón',
        'guarico' => 'Guárico',
        'lara' => 'Lara',
        'merida' => 'Mérida',
        'miranda' => 'Miranda',
        'monagas' => 'Monagas',
        'nueva_esparta' => 'Nueva Esparta',
        'portuguesa' => 'Portuguesa',
        'sucre' => 'Sucre',
        'tachira' => 'Táchira',
        'trujillo' => 'Trujillo',
        'vargas' => 'Vargas',
        'yaracuy' => 'Yaracuy',
        'zulia' => 'Zulia'
    );
    
    public function get_states() {
        return $this->states;
    }
    
    public function get_cities_by_state($state_code) {
        // Obtiene ciudades por estado
    }
    
    public function get_postal_codes($city_code) {
        // Obtiene códigos postales por ciudad
    }
}
```

---

## **⚙️ CONFIGURACIÓN DEL MÓDULO**

### **🎛️ Configuración General**
```php
$shipping_settings = array(
    'default_origin' => 'caracas',         // Origen por defecto
    'currency' => 'VES',                   // Moneda de envío
    'weight_unit' => 'kg',                 // Unidad de peso
    'distance_unit' => 'km',               // Unidad de distancia
    'free_shipping_threshold' => 100000,   // Umbral envío gratis (VES)
    'max_weight' => 30,                    // Peso máximo (kg)
    'max_dimensions' => array(             // Dimensiones máximas
        'length' => 100,
        'width' => 100,
        'height' => 100
    ),
    'tracking_enabled' => true,            // Habilitar tracking
    'address_validation' => true          // Validar direcciones
);
```

### **🚛 Configuración de Servicios**
```php
$services_settings = array(
    'standard' => array(
        'name' => 'Envío Estándar',
        'description' => 'Entrega en 3-5 días hábiles',
        'base_rate' => 5000,               // Tarifa base en VES
        'rate_per_kg' => 2000,            // Por kg adicional
        'rate_per_km' => 50,              // Por km adicional
        'max_weight' => 20,
        'delivery_time' => '3-5 días'
    ),
    'express' => array(
        'name' => 'Envío Express',
        'description' => 'Entrega en 1-2 días hábiles',
        'base_rate' => 10000,
        'rate_per_kg' => 3000,
        'rate_per_km' => 100,
        'max_weight' => 15,
        'delivery_time' => '1-2 días'
    ),
    'motorizado' => array(
        'name' => 'Motorizado Local',
        'description' => 'Entrega el mismo día',
        'base_rate' => 15000,
        'rate_per_kg' => 1000,
        'rate_per_km' => 200,
        'max_weight' => 10,
        'delivery_time' => 'Mismo día',
        'zones' => array('caracas', 'maracaibo', 'valencia')
    )
);
```

---

## **🔄 FLUJO DE FUNCIONAMIENTO**

### **📦 Cálculo de Envío**
1. **Cliente ingresa** dirección de entrega
2. **Sistema valida** dirección venezolana
3. **Determina zona** de envío
4. **Calcula distancia** desde origen
5. **Obtiene peso** del paquete
6. **Aplica tarifas** según servicio
7. **Muestra opciones** de envío
8. **Cliente selecciona** servicio
9. **Genera tracking** number
10. **Confirma envío**

### **📊 Tracking de Envío**
1. **Sistema genera** número de tracking
2. **Envía notificación** al cliente
3. **Actualiza estado** según progreso
4. **Envía alertas** de cambios
5. **Confirma entrega** final

---

## **🎨 INTEGRACIÓN CON WOOCOMMERCE**

### **🔌 Registro de Métodos de Envío**
```php
add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));

public function add_shipping_methods($methods) {
    $methods['wvs_venezuela_shipping'] = 'Woocommerce_Venezuela_Suite_Shipping_Method';
    return $methods;
}
```

### **📍 Campos de Dirección**
- **Validación** específica para Venezuela
- **Autocompletado** de estados y ciudades
- **Códigos postales** venezolanos
- **Geocodificación** automática

---

## **🧪 TESTING**

### **🔍 Casos de Prueba**
- **Validación** de direcciones venezolanas
- **Cálculo** de costos de envío
- **Detección** de zonas correctas
- **Tracking** de envíos
- **Integración** con WooCommerce

### **📊 Datos de Prueba**
- **Direcciones** de diferentes estados
- **Pesos** y dimensiones variados
- **Distancias** calculadas
- **Tarifas** aplicadas

---

## **🚨 MANEJO DE ERRORES**

### **⚠️ Errores Comunes**
- **Dirección inválida** → Validación mejorada
- **Zona no cubierta** → Mensaje informativo
- **Peso excesivo** → Opciones alternativas
- **Error de cálculo** → Tarifa manual

### **📋 Logging**
- **Cálculos** de envío
- **Errores** de validación
- **Tracking** de envíos
- **Performance** de cálculos

---

## **📈 MÉTRICAS DE ÉXITO**

### **🎯 KPIs del Módulo**
- **Precisión** de cálculo > 99%
- **Tiempo** de cálculo < 2 segundos
- **Cobertura** de zonas > 95%
- **Satisfacción** del cliente > 4.5/5

### **📊 Métricas Específicas**
- **Envíos** por zona
- **Tiempo promedio** de entrega
- **Costos** promedio por envío
- **Errores** de cálculo por día

---

## **🔗 DEPENDENCIAS**

### **📦 Módulos Requeridos**
- **Ninguno** (módulo independiente)

### **📦 Módulos que Dependen de Este**
- **Payment Gateways** (para envíos contra entrega)
- **Reports Analytics** (para métricas de envío)
- **Notifications** (para tracking)

### **🔌 Integraciones Externas**
- **Google Maps API** (para distancias)
- **Geocoding Service** (para direcciones)
- **Tracking APIs** (para seguimiento)

---

## **📅 CRONOGRAMA DE DESARROLLO**

### **📅 Semana 1: Zonas y Datos**
- **Día 1-2**: Estructura del módulo y datos de Venezuela
- **Día 3-4**: Sistema de zonas y ciudades
- **Día 5**: Validación de direcciones

### **📅 Semana 2: Cálculo de Envíos**
- **Día 1-2**: Calculadora de costos
- **Día 3-4**: Calculadora de distancias
- **Día 5**: Servicios de entrega

### **📅 Semana 3: Integración y Tracking**
- **Día 1-2**: Integración con WooCommerce
- **Día 3-4**: Sistema de tracking
- **Día 5**: Testing y optimización

---

## **🚀 PRÓXIMOS PASOS**

1. **Crear estructura** de carpetas del módulo
2. **Implementar** datos completos de Venezuela
3. **Desarrollar** sistema de zonas nacionales
4. **Crear** calculadora de envíos avanzada
5. **Implementar** servicios especializados por región
6. **Desarrollar** sistema de tracking inteligente
7. **Crear módulos** de ayuda y debug
8. **Testing** completo del módulo
9. **Documentación** y deployment

---

## **🆘 MÓDULOS DE AYUDA Y DEBUG**

### **📚 Módulo de Ayuda (Help & Support)**
```
modules/help-support/
├── help-support.php                    # 🚀 Bootstrap del módulo
├── includes/
│   ├── class-help-core.php             # ⚙️ Funcionalidad principal
│   ├── class-help-documentation.php    # 📚 Documentación
│   ├── class-help-tutorials.php        # 🎓 Tutoriales
│   ├── class-help-faq.php             # ❓ FAQ dinámico
│   ├── class-help-videos.php          # 🎥 Videos tutoriales
│   ├── class-help-chat.php            # 💬 Chat de soporte
│   ├── class-help-tickets.php         # 🎫 Sistema de tickets
│   └── class-help-admin.php           # 👨‍💼 Panel administrativo
├── admin/
│   ├── css/help-support-admin.css     # 🎨 Estilos admin
│   ├── js/help-support-admin.js       # 📱 JavaScript admin
│   └── partials/
│       ├── help-dashboard.php         # 📊 Dashboard ayuda
│       ├── documentation-manager.php # 📚 Gestor documentación
│       ├── tutorial-manager.php        # 🎓 Gestor tutoriales
│       ├── faq-manager.php            # ❓ Gestor FAQ
│       ├── video-manager.php          # 🎥 Gestor videos
│       ├── chat-settings.php          # 💬 Configuración chat
│       ├── ticket-system.php          # 🎫 Sistema tickets
│       └── help-analytics.php         # 📊 Analytics ayuda
├── public/
│   ├── css/help-support-public.css    # 🎨 Estilos públicos
│   ├── js/help-support-public.js      # 📱 JavaScript público
│   └── js/
│       ├── help-search.js             # 🔍 Búsqueda ayuda
│       ├── help-chat.js               # 💬 Chat widget
│       ├── help-tutorials.js          # 🎓 Tutoriales interactivos
│       └── help-feedback.js           # 📝 Feedback sistema
├── templates/
│   ├── help-center.php                # 🏠 Centro de ayuda
│   ├── documentation.php              # 📚 Documentación
│   ├── tutorials.php                  # 🎓 Tutoriales
│   ├── faq.php                        # ❓ FAQ
│   ├── videos.php                     # 🎥 Videos
│   ├── chat-widget.php                # 💬 Widget chat
│   ├── ticket-form.php                # 🎫 Formulario tickets
│   └── feedback-form.php              # 📝 Formulario feedback
├── assets/
│   ├── images/
│   │   ├── tutorials/                 # 🎓 Imágenes tutoriales
│   │   ├── videos/                    # 🎥 Thumbnails videos
│   │   └── icons/                     # 🆘 Iconos ayuda
│   ├── videos/
│   │   ├── tutorials/                 # 🎓 Videos tutoriales
│   │   └── demos/                     # 🎬 Demos
│   └── docs/
│       ├── pdf/                       # 📄 Documentos PDF
│       └── html/                      # 🌐 Documentación HTML
├── languages/
│   └── help-support.pot              # 🌍 Traducciones
├── tests/
│   ├── test-help-documentation.php    # 🧪 Tests documentación
│   ├── test-help-tutorials.php       # 🧪 Tests tutoriales
│   ├── test-help-chat.php            # 🧪 Tests chat
│   └── test-help-integration.php     # 🧪 Tests integración
├── PLAN-MODULO.md                     # 📋 Plan del módulo
├── README.md                           # 📖 Documentación
└── uninstall.php                       # 🗑️ Limpieza al eliminar
```

### **🔧 Módulo de Debug (Debug & Diagnostics)**
```
modules/debug-diagnostics/
├── debug-diagnostics.php              # 🚀 Bootstrap del módulo
├── includes/
│   ├── class-debug-core.php           # ⚙️ Funcionalidad principal
│   ├── class-debug-logger.php         # 📝 Logger avanzado
│   ├── class-debug-profiler.php       # ⏱️ Profiler de performance
│   ├── class-debug-monitor.php        # 📊 Monitor sistema
│   ├── class-debug-analyzer.php       # 🔍 Analizador errores
│   ├── class-debug-reporter.php       # 📤 Reporter automático
│   ├── class-debug-tools.php          # 🛠️ Herramientas debug
│   └── class-debug-admin.php          # 👨‍💼 Panel administrativo
├── admin/
│   ├── css/debug-diagnostics-admin.css # 🎨 Estilos admin
│   ├── js/debug-diagnostics-admin.js  # 📱 JavaScript admin
│   └── partials/
│       ├── debug-dashboard.php        # 📊 Dashboard debug
│       ├── log-viewer.php             # 📝 Visor logs
│       ├── performance-monitor.php    # ⏱️ Monitor performance
│       ├── error-analyzer.php         # 🔍 Analizador errores
│       ├── system-health.php          # 💚 Salud sistema
│       ├── debug-tools.php            # 🛠️ Herramientas debug
│       ├── report-generator.php       # 📤 Generador reportes
│       └── debug-settings.php         # ⚙️ Configuración debug
├── public/
│   ├── css/debug-diagnostics-public.css # 🎨 Estilos públicos
│   ├── js/debug-diagnostics-public.js  # 📱 JavaScript público
│   └── js/
│       ├── debug-console.js           # 🖥️ Consola debug
│       ├── performance-tracker.js     # ⏱️ Rastreador performance
│       ├── error-catcher.js           # 🚨 Capturador errores
│       └── debug-widget.js            # 🔧 Widget debug
├── templates/
│   ├── debug-console.php              # 🖥️ Consola debug
│   ├── log-viewer.php                 # 📝 Visor logs
│   ├── performance-dashboard.php      # ⏱️ Dashboard performance
│   ├── error-details.php              # 🔍 Detalles errores
│   ├── system-status.php              # 💚 Estado sistema
│   └── debug-report.php               # 📤 Reporte debug
├── tools/
│   ├── debug-console.php              # 🖥️ Consola debug
│   ├── log-cleaner.php                # 🧹 Limpiador logs
│   ├── performance-test.php           # ⏱️ Test performance
│   ├── error-simulator.php            # 🚨 Simulador errores
│   └── system-check.php               # 🔍 Verificador sistema
├── logs/
│   ├── error.log                      # 🚨 Log errores
│   ├── performance.log                # ⏱️ Log performance
│   ├── debug.log                      # 🔧 Log debug
│   └── system.log                     # 💚 Log sistema
├── reports/
│   ├── daily/                         # 📅 Reportes diarios
│   ├── weekly/                        # 📊 Reportes semanales
│   └── monthly/                       # 📈 Reportes mensuales
├── assets/
│   ├── images/
│   │   ├── charts/                    # 📊 Gráficos
│   │   └── icons/                     # 🔧 Iconos debug
│   └── js/
│       ├── chart.js                   # 📊 Librería gráficos
│       └── debug-utils.js             # 🛠️ Utilidades debug
├── languages/
│   └── debug-diagnostics.pot         # 🌍 Traducciones
├── tests/
│   ├── test-debug-logger.php          # 🧪 Tests logger
│   ├── test-debug-profiler.php        # 🧪 Tests profiler
│   ├── test-debug-monitor.php         # 🧪 Tests monitor
│   └── test-debug-integration.php     # 🧪 Tests integración
├── PLAN-MODULO.md                     # 📋 Plan del módulo
├── README.md                           # 📖 Documentación
└── uninstall.php                       # 🗑️ Limpieza al eliminar
```

### **📚 Funcionalidades del Módulo de Ayuda**
- **Centro de ayuda** completo con búsqueda
- **Documentación** interactiva y actualizable
- **Tutoriales** paso a paso con imágenes
- **FAQ dinámico** con categorías
- **Videos tutoriales** integrados
- **Chat de soporte** en tiempo real
- **Sistema de tickets** para soporte técnico
- **Feedback** y calificaciones
- **Analytics** de uso de ayuda

### **🔧 Funcionalidades del Módulo de Debug**
- **Logger avanzado** con múltiples niveles
- **Profiler de performance** en tiempo real
- **Monitor del sistema** con alertas
- **Analizador de errores** automático
- **Reporter automático** de problemas
- **Herramientas de debug** integradas
- **Consola de debug** en tiempo real
- **Reportes automáticos** diarios/semanales/mensuales
- **Salud del sistema** con métricas

---

*Este módulo es esencial para la logística de envíos y debe ser preciso, completo y fácil de configurar para diferentes tipos de negocios. Los módulos de ayuda y debug son fundamentales para el soporte y mantenimiento del sistema.*
