# KintaElectronic Elementor

Plugin personalizado para integrar componentes HTML de Electro con Elementor, incluyendo animaciones y funcionalidades avanzadas.

## 🚀 Características

- **Widgets de Elementor personalizados** para componentes de Electro
- **Sistema de animaciones avanzado** con CSS y JavaScript
- **Integración completa con WooCommerce**
- **Diseño responsive** para todos los dispositivos
- **Shortcodes personalizados** para inserción rápida
- **Compatibilidad total con Elementor**

## 📋 Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- Elementor 3.0 o superior
- WooCommerce 4.0 o superior (opcional)

## 🛠️ Instalación

1. **Subir el plugin** a la carpeta `/wp-content/plugins/kinta-electronic-elementor/`
2. **Activar el plugin** a través del menú 'Plugins' en WordPress
3. **Verificar que Elementor esté activo** (el plugin se desactivará automáticamente si no lo está)

## 🎯 Uso

### Widgets de Elementor

El plugin añade una nueva categoría llamada "Kinta Electronic" en Elementor con los siguientes widgets:

#### 1. Electro Header Widget
- **Configuración General**: Controla la visibilidad de elementos del header
- **Logo**: Permite elegir entre texto o imagen
- **Topbar**: Configuración del barra superior
- **Búsqueda**: Personalización de la barra de búsqueda
- **Estilos**: Control completo de colores y tipografía

#### 2. Otros Widgets (Próximamente)
- Electro Hero Widget
- Electro Product Grid Widget
- Electro Category Widget
- Electro Newsletter Widget
- Electro Footer Widget

### Shortcodes

Utiliza el shortcode `[kinta_electronic]` para insertar componentes rápidamente:

```php
// Header básico
[kinta_electronic type="header"]

// Hero section
[kinta_electronic type="hero"]

// Cuadrícula de productos
[kinta_electronic type="product-grid"]

// Categorías
[kinta_electronic type="category"]

// Newsletter
[kinta_electronic type="newsletter"]

// Footer
[kinta_electronic type="footer"]

// Con clases personalizadas
[kinta_electronic type="header" class="custom-header-class"]
```

## 🎨 Personalización

### CSS Personalizado

El plugin incluye un sistema de variables CSS que puedes sobrescribir:

```css
:root {
    --kee-primary-color: #007bff;
    --kee-secondary-color: #6c757d;
    --kee-success-color: #28a745;
    --kee-danger-color: #dc3545;
    --kee-warning-color: #ffc107;
    --kee-info-color: #17a2b8;
    --kee-light-color: #f8f9fa;
    --kee-dark-color: #343a40;
    
    --kee-border-radius: 0.375rem;
    --kee-box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --kee-transition: all 0.15s ease-in-out;
}
```

### JavaScript Personalizado

Puedes extender la funcionalidad del plugin:

```javascript
// Acceder a la instancia del plugin
const keeInstance = window.keeInstance;

// Añadir funcionalidades personalizadas
keeInstance.customFunction = function() {
    console.log('Función personalizada');
};
```

## 🔧 Configuración

### Activación del Plugin

El plugin se activa automáticamente cuando:
- WordPress está funcionando correctamente
- Elementor está activo
- Los permisos de archivos son correctos

### Verificación de Funcionamiento

1. Ve a **Elementor > Editor**
2. Busca la categoría **"Kinta Electronic"**
3. Arrastra un widget (ej: Electro Header) a tu página
4. Configura las opciones según tus necesidades

## 📱 Responsive Design

El plugin incluye breakpoints automáticos:

- **Desktop**: > 1199px
- **Tablet**: 768px - 1199px
- **Mobile**: < 768px

### Comportamiento Responsive

- **Topbar**: Se oculta automáticamente en móviles
- **Búsqueda**: Se adapta al espacio disponible
- **Menú**: Cambia a formato hamburguesa en móviles
- **Dropdowns**: Cambian de hover a click en móviles

## 🎭 Animaciones

### Tipos de Animaciones Disponibles

- `slideInUp`: Desliza hacia arriba
- `fadeIn`: Aparece gradualmente
- `fadeOut`: Desaparece gradualmente
- `fadeInRight`: Aparece desde la derecha
- `fadeOutRight`: Desaparece hacia la derecha
- `fadeInLeft`: Aparece desde la izquierda
- `fadeOutLeft`: Desaparece hacia la izquierda

### Configuración de Animaciones

```html
<!-- Ejemplo de enlace con animación -->
<a class="js-animation-link" 
   data-target="#targetElement" 
   data-link-group="formGroup" 
   data-animation-in="slideInUp">
   Haz clic aquí
</a>
```

## 🔌 Integración con WooCommerce

### Funcionalidades Automáticas

- **Contador del carrito**: Se actualiza automáticamente
- **Eventos del carrito**: Respuesta a añadir/quitar productos
- **Estilos integrados**: Compatibilidad visual completa

### Hooks Disponibles

```php
// Hook personalizado para carrito
add_action('kee_cart_updated', function($cart_data) {
    // Tu código personalizado
});

// Hook para búsqueda
add_action('kee_search_performed', function($query) {
    // Tu código personalizado
});
```

## 🚨 Solución de Problemas

### Problemas Comunes

#### 1. Plugin no se activa
- **Verificar**: Elementor está activo
- **Verificar**: Permisos de archivos (755 para carpetas, 644 para archivos)
- **Verificar**: No hay errores en el log de WordPress

#### 2. Widgets no aparecen en Elementor
- **Verificar**: Plugin está activo
- **Verificar**: Elementor está actualizado
- **Verificar**: No hay conflictos con otros plugins

#### 3. Estilos no se cargan
- **Verificar**: Archivos CSS existen en `/assets/css/`
- **Verificar**: Permisos de archivos
- **Verificar**: No hay conflictos de caché

#### 4. JavaScript no funciona
- **Verificar**: jQuery está cargado
- **Verificar**: Archivos JS existen en `/assets/js/`
- **Verificar**: Consola del navegador para errores

### Logs de Depuración

Habilita el modo debug en WordPress:

```php
// En wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🔄 Actualizaciones

### Proceso de Actualización

1. **Hacer backup** de la instalación actual
2. **Desactivar** el plugin
3. **Reemplazar** archivos con la nueva versión
4. **Reactivar** el plugin
5. **Verificar** funcionalidad

### Compatibilidad

- **WordPress**: 5.0 - 6.4+
- **Elementor**: 3.0 - 3.18+
- **WooCommerce**: 4.0 - 8.0+
- **PHP**: 7.4 - 8.2+

## 📚 Desarrollo

### Estructura del Plugin

```
kinta-electronic-elementor/
├── assets/
│   ├── css/
│   │   └── kinta-electronic-elementor.css
│   └── js/
│       └── kinta-electronic-elementor.js
├── widgets/
│   └── electro-header-widget.php
├── kinta-electronic-elementor.php
└── README.md
```

### Hooks de WordPress

```php
// Hook de activación
do_action('kee_plugin_activated');

// Hook de desactivación
do_action('kee_plugin_deactivated');

// Hook de inicialización
do_action('kee_plugin_initialized');
```

### Filtros Disponibles

```php
// Filtrar configuración del widget
add_filter('kee_widget_settings', function($settings) {
    // Modificar configuración
    return $settings;
});

// Filtrar HTML renderizado
add_filter('kee_widget_html', function($html, $widget_type) {
    // Modificar HTML
    return $html;
}, 10, 2);
```

## 🤝 Soporte

### Canales de Soporte

- **Documentación**: Este README
- **Issues**: GitHub (si está disponible)
- **Email**: info@kinta-electric.com

### Información de Contacto

- **Desarrollador**: Kinta Electric
- **Website**: https://kinta-electric.com
- **Email**: info@kinta-electric.com

## 📄 Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

## 🙏 Agradecimientos

- **Elementor** por su excelente framework
- **WordPress** por la plataforma
- **Comunidad** de desarrolladores de WordPress

---

**Versión**: 1.0.0  
**Última actualización**: Agosto 2025  
**Compatibilidad**: WordPress 5.0+, Elementor 3.0+
