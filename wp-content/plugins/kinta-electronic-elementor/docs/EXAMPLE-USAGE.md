# Ejemplo de Uso - Widget Home Slider Kintaelectic

## 🎯 Uso Básico en Elementor

### 1. Agregar el Widget
1. Abrir Elementor Editor
2. Buscar "Home Slider Kintaelectic" en la categoría "Kinta Electric"
3. Arrastrar el widget a la página

### 2. Configurar el Slider
1. **Imagen de Fondo**: Seleccionar imagen de fondo para todo el slider
2. **Agregar Slides**: Usar el botón "+" para agregar nuevos slides
3. **Configurar cada slide**:
   - Título Principal
   - Subtítulo
   - Precio (desde y centavos)
   - Texto del botón
   - URL del botón
   - Imagen del slide

### 3. Personalizar Estilos
- Los estilos se aplican automáticamente usando las clases CSS del theme original
- No se requiere configuración adicional de CSS

## 🔧 Uso Avanzado con JavaScript

### Inicializar Slider Personalizado
```javascript
// Inicializar con configuración personalizada
KintaSlider.init('.mi-slider-personalizado', {
    autoplay: true,
    autoplaySpeed: 4000,
    dots: true,
    arrows: true,
    fade: true
});
```

### Configurar por Tipo de Slider
```javascript
// Usar configuración predefinida
KintaSlider.init('.home-hero-slider', 'home-hero');
KintaSlider.init('.product-gallery', 'product-gallery');
KintaSlider.init('.testimonials-slider', 'testimonials');
```

### Destruir y Reinicializar
```javascript
// Destruir slider
KintaSlider.destroy('.mi-slider');

// Reinicializar con nueva configuración
KintaSlider.reinit('.mi-slider', {
    autoplay: false,
    slidesToShow: 3
});
```

### Obtener Configuraciones
```javascript
// Obtener configuración por defecto
var defaultConfig = KintaSlider.getDefaultConfig();

// Obtener configuración por tipo
var heroConfig = KintaSlider.getConfigByType('home-hero');

// Actualizar configuración global
KintaSlider.updateConfig({
    defaultSettings: {
        autoplaySpeed: 5000,
        pauseOnHover: false
    }
});
```

## 🎨 Personalización CSS

### Variables CSS Disponibles
```css
:root {
  --primary: #ccc634;        /* Color primario */
  --secondary: #77838f;      /* Color secundario */
  --success: #00c9a7;        /* Color de éxito */
  --info: #00dffc;           /* Color de información */
  --warning: #ffc107;        /* Color de advertencia */
  --danger: #de4437;         /* Color de peligro */
  --light: #f8f9fa;          /* Color claro */
  --dark: #333e48;           /* Color oscuro */
  --white: #fff;             /* Blanco */
}
```

### Clases CSS Principales
```css
/* Contenedor del slider */
.u-slick { }

/* Slide individual */
.js-slide { }

/* Imagen de fondo */
.bg-img-hero { }

/* Tipografía */
.font-size-64 { }    /* Título principal */
.font-size-50 { }    /* Precio */
.font-size-15 { }    /* Subtítulo */
.font-size-13 { }    /* Texto pequeño */

/* Layout */
.min-height-420 { }  /* Altura mínima */
.container { }       /* Contenedor */
.row { }            /* Fila */
.col-xl-4 { }       /* Columna 4/12 */
.col-xl-5 { }       /* Columna 5/12 */

/* Botones */
.btn-primary { }     /* Botón primario */
.transition-3d-hover { } /* Efecto hover 3D */

/* Animaciones */
.animated { }        /* Elemento animado */
.fadeInUp { }        /* Animación fadeInUp */
.zoomIn { }          /* Animación zoomIn */
```

## 📱 Responsive Design

### Breakpoints Disponibles
```css
/* Extra Small devices (portrait phones) */
@media (max-width: 575.98px) { }

/* Small devices (landscape phones) */
@media (min-width: 576px) and (max-width: 767.98px) { }

/* Medium devices (tablets) */
@media (min-width: 768px) and (max-width: 991.98px) { }

/* Large devices (desktops) */
@media (min-width: 992px) and (max-width: 1199.98px) { }

/* Extra large devices (large desktops) */
@media (min-width: 1200px) { }
```

### Configuración Responsive del Slider
```javascript
var responsiveConfig = {
    responsive: [
        {
            breakpoint: 1200,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 992,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 768,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false
            }
        },
        {
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                dots: true
            }
        }
    ]
};
```

## 🎭 Animaciones

### Tipos de Animación Disponibles
- `fadeInUp` - Aparece desde abajo
- `zoomIn` - Zoom desde el centro
- `slideInLeft` - Desliza desde la izquierda
- `slideInRight` - Desliza desde la derecha

### Aplicar Animaciones
```html
<!-- En el HTML del widget -->
<h1 data-animation="fadeInUp">Título Animado</h1>
<img data-animation="zoomIn" src="imagen.jpg" alt="Imagen">
```

### Configurar Animaciones
```javascript
// Personalizar duración y delay
KintaSlider.updateConfig({
    animations: {
        fadeInUp: {
            duration: 1500,  // 1.5 segundos
            delay: 200,      // 200ms de delay
            easing: 'ease-out'
        }
    }
});
```

## 🔧 Integración con Elementor

### Hooks de Elementor
```javascript
// Reinicializar después de cambios en Elementor
elementorFrontend.hooks.addAction('frontend/element_ready/global', function($scope) {
    KintaSlider.init($scope.find('.js-slick-carousel'));
});
```

### Contenido Dinámico
El plugin maneja automáticamente:
- Reinicialización cuando se agrega contenido dinámico
- Compatibilidad con widgets de Elementor
- Previsualización en tiempo real

## 🐛 Debugging

### Habilitar Modo Debug
```javascript
// En la consola del navegador
KintaElectric.config({ debug: true });
```

### Verificar Estado del Sistema
```javascript
// Verificar estado de inicialización
console.log(KintaElectric.getStatus());

// Verificar si está inicializado
console.log(KintaElectric.isInitialized());
```

### Logs de Debug
Con debug habilitado, verás en la consola:
- Estado de componentes del sistema
- Errores de inicialización
- Confirmación de carga exitosa

## 📋 Checklist de Implementación

- [ ] Plugin activado en WordPress
- [ ] Widget visible en Elementor
- [ ] Librerías externas descargadas (Animate.css, Font Awesome, etc.)
- [ ] Slider se inicializa correctamente
- [ ] Animaciones funcionan
- [ ] Responsive design funciona
- [ ] Compatibilidad con Elementor verificada
- [ ] Personalizaciones aplicadas

## 🚀 Próximos Pasos

1. **Probar en diferentes páginas** y configuraciones
2. **Personalizar estilos** según necesidades del proyecto
3. **Optimizar rendimiento** si es necesario
4. **Crear widgets adicionales** basados en este patrón
5. **Documentar configuraciones** específicas del cliente

---

**¡El widget está listo para usar!** 🎉
