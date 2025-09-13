# 🛒 Widget Kinta Electric 05 - Dynamic Products Carousel

## 📋 **Descripción**

El widget **Kinta Electric 05 - Dynamic Products Carousel** es un carrusel dinámico de productos que replica la funcionalidad del slider "Best Sellers" del tema Electro. Permite mostrar productos de WooCommerce en un carrusel responsive con navegación por pestañas y configuración completa.

## 🎯 **Características Principales**

### ✅ **Funcionalidades Implementadas:**
- **Carrusel Responsive**: Adaptable a móvil, tablet y desktop
- **Múltiples Fuentes de Productos**: Destacados, ofertas, recientes, más vendidos, por categoría
- **Navegación por Pestañas**: Enlaces a categorías de productos
- **Integración WooCommerce**: Precios, imágenes, botones de compra
- **Integración YITH**: Wishlist y Compare (si están activos)
- **Configuración Completa**: Items por pantalla, autoplay, navegación
- **Estilos del Tema**: Usa las clases CSS del tema Electro

### 🎨 **Diseño Visual:**
- Estructura idéntica al slider original del tema
- Clases CSS del tema Electro para consistencia visual
- Responsive design nativo
- Animaciones y efectos hover

## ⚙️ **Configuración del Widget**

### **1. Configuración General**
- **Título de la Sección**: Texto del encabezado (por defecto: "Best Sellers")
- **Número de Productos**: Cantidad de productos a mostrar (1-20)
- **Fuente de Productos**: 
  - Productos Destacados
  - Productos en Oferta
  - Productos Recientes
  - Más Vendidos
  - Por Categoría
- **Categoría de Productos**: Selector de categoría (si se selecciona "Por Categoría")

### **2. Configuración del Carrusel**
- **Items en Desktop**: Número de productos en pantallas grandes (1-6)
- **Items en Tablet**: Número de productos en tablets (1-4)
- **Items en Móvil**: Número de productos en móviles (1-2)
- **Autoplay**: Activar/desactivar reproducción automática
- **Tiempo de Autoplay**: Intervalo en milisegundos (1000-10000)
- **Mostrar Dots**: Mostrar indicadores de página
- **Mostrar Navegación**: Mostrar flechas de navegación

### **3. Navegación por Pestañas**
- **Mostrar Pestañas**: Activar/desactivar navegación por pestañas
- **Categorías para Pestañas**: Selección múltiple de categorías

## 🔧 **Implementación Técnica**

### **Estructura HTML:**
```html
<section class="section-product-cards-carousel home-v1-product-cards-carousel animate-in-view">
    <header class="show-nav">
        <h2 class="h1">Título</h2>
        <ul class="nav nav-inline">
            <!-- Pestañas de navegación -->
        </ul>
    </header>
    
    <div class="product-cards-carousel owl-carousel">
        <ul class="products">
            <!-- Productos de WooCommerce -->
        </ul>
    </div>
</section>
```

### **Configuración Owl Carousel:**
```javascript
{
    items: 4,
    nav: false,
    dots: true,
    autoplay: false,
    responsive: {
        '0': { items: 1 },
        '768': { items: 3 },
        '1200': { items: 4 }
    }
}
```

### **Dependencias:**
- **Scripts**: `owl-carousel`, `bootstrap-bundle`, `electro-main`
- **Estilos**: `electro-style`, `owl-carousel`
- **Plugins**: WooCommerce (requerido), YITH Wishlist/Compare (opcional)

## 📱 **Responsive Design**

### **Breakpoints:**
- **Móvil (0-767px)**: 1-2 productos por vista
- **Tablet (768-1199px)**: 2-4 productos por vista  
- **Desktop (1200px+)**: 3-6 productos por vista

### **Clases CSS Responsive:**
- `row-cols-2`: 2 columnas en móvil
- `row-cols-md-3`: 3 columnas en tablet
- `row-cols-lg-3`: 3 columnas en desktop pequeño
- `row-cols-xl-3`: 3 columnas en desktop
- `row-cols-xxl-4`: 4 columnas en pantallas extra grandes

## 🎨 **Estilos CSS**

### **Clases Principales:**
- `.section-product-cards-carousel`: Contenedor principal
- `.product-cards-carousel`: Carrusel de productos
- `.product-card`: Tarjeta individual de producto
- `.product-inner`: Contenido interno del producto
- `.card-media-left`: Imagen del producto
- `.card-body`: Información del producto
- `.hover-area`: Área de botones de acción

### **Estilos del Tema:**
El widget utiliza automáticamente los estilos del tema Electro que ya están cargados:
- `style.css` - Estilos principales del tema
- `elementor.css` - Estilos específicos de Elementor
- `owl-carousel` - Estilos del carrusel

## 🔌 **Integración con Plugins**

### **WooCommerce:**
- Consultas de productos optimizadas
- Precios y moneda automáticos
- Botones de "Add to Cart" nativos
- Clases de producto dinámicas

### **YITH WooCommerce Wishlist:**
- Botón de wishlist automático
- Integración con fragmentos AJAX
- Estilos consistentes con el tema

### **YITH WooCommerce Compare:**
- Botón de comparación automático
- Enlaces AJAX funcionales
- Tooltips informativos

## 🚀 **Uso en Elementor**

### **1. Agregar Widget:**
1. Abrir Elementor en cualquier página
2. Buscar la categoría "Kinta Electric"
3. Arrastrar "Kinta Electric 05 - Dynamic Products Carousel"

### **2. Configurar:**
1. **General**: Establecer título y fuente de productos
2. **Carrusel**: Ajustar items por pantalla y opciones
3. **Pestañas**: Configurar navegación por categorías

### **3. Personalizar:**
- Usar los controles de Elementor para ajustar colores, tipografías, espaciado
- Aplicar animaciones y efectos
- Configurar responsive design

## 🐛 **Solución de Problemas**

### **El carrusel no funciona:**
- Verificar que Owl Carousel esté cargado
- Comprobar que jQuery esté disponible
- Revisar la consola del navegador para errores

### **Los productos no se muestran:**
- Verificar que WooCommerce esté activo
- Comprobar que hay productos en la fuente seleccionada
- Revisar la configuración de visibilidad de productos

### **Los estilos no se aplican:**
- Verificar que el tema Electro esté activo
- Comprobar que los estilos del tema se estén cargando
- Revisar conflictos con otros plugins

## 📊 **Rendimiento**

### **Optimizaciones Implementadas:**
- Consultas de productos eficientes
- Cache de categorías de productos
- Carga lazy de imágenes
- Scripts solo cuando es necesario

### **Recomendaciones:**
- Limitar el número de productos (máximo 20)
- Usar productos destacados para mejor rendimiento
- Evitar autoplay en móviles para ahorrar batería

## 🔄 **Actualizaciones Futuras**

### **Funcionalidades Planificadas:**
- Filtros por atributos de producto
- Ordenamiento personalizado
- Integración con más plugins de WooCommerce
- Modo de vista alternativo (grid/list)
- Lazy loading avanzado

---

## 📝 **Notas de Desarrollo**

- **Versión**: 1.0.0
- **Compatibilidad**: WordPress 5.0+, Elementor 3.0+, WooCommerce 5.0+
- **Última actualización**: Diciembre 2024
- **Desarrollado por**: Kinta Electric Team

---

*Este widget está diseñado específicamente para el tema Kinta Electric y utiliza las clases CSS y funcionalidades del tema Electro original.*
