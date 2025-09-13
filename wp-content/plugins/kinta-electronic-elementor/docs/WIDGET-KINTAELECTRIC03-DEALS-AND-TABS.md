# Widget Kintaelectric03 - Deals and Tabs

## 📋 Descripción

Widget dinámico de Elementor que combina una sección de oferta especial con countdown timer y un sistema de tabs para mostrar productos de WooCommerce organizados por categorías.

## 🎯 Características Principales

### **Oferta Especial (Sección Izquierda)**
- ✅ **Producto destacado** seleccionable desde productos en oferta
- ✅ **Countdown timer funcional** con días configurables (1-30 días)
- ✅ **Título personalizable** para la oferta
- ✅ **Texto de ahorro** personalizable
- ✅ **Imagen del producto** automática (300x300px)
- ✅ **Precio del producto** con formato WooCommerce

### **Tabs de Productos (Sección Derecha)**
- ✅ **Múltiples tabs** configurables
- ✅ **Categorías seleccionables** por tab
- ✅ **Número de productos** por tab (1-20)
- ✅ **Ordenamiento** por fecha, popularidad, valoración, precio
- ✅ **Grid responsivo** con columnas configurables

### **Funcionalidades WooCommerce**
- ✅ **Wishlist** integrado con YITH Wishlist
- ✅ **Compare** integrado con YITH Compare
- ✅ **Add to Cart** funcional
- ✅ **Ratings** y reviews
- ✅ **SKU** del producto
- ✅ **Categorías** del producto
- ✅ **Descripción corta**

## ⚙️ Configuración del Widget

### **Sección: Oferta Especial**

| Campo | Tipo | Descripción | Valor por Defecto |
|-------|------|-------------|-------------------|
| `special_offer_title` | Text | Título de la oferta | "Special Offer" |
| `special_offer_product` | Select2 | Producto en oferta | Lista de productos en oferta |
| `countdown_days` | Number | Días para countdown | 7 |
| `savings_text` | Text | Texto de ahorro | "Save $19.00" |

### **Sección: Tabs de Productos**

| Campo | Tipo | Descripción | Valor por Defecto |
|-------|------|-------------|-------------------|
| `tabs_list` | Repeater | Lista de tabs | 3 tabs predefinidos |
| `tab_name` | Text | Nombre del tab | "Featured", "On Sale", "Top Rated" |
| `tab_categories` | Select2 | Categorías del tab | Múltiples categorías |
| `products_per_tab` | Number | Productos por tab | 8 |
| `tab_orderby` | Select | Ordenar por | date, popularity, rating, price |

### **Sección: Configuración General**

| Campo | Tipo | Descripción | Opciones |
|-------|------|-------------|----------|
| `columns_mobile` | Select | Columnas en móvil | 1, 2 |
| `columns_tablet` | Select | Columnas en tablet | 2, 3, 4 |
| `columns_desktop` | Select | Columnas en desktop | 3, 4, 5, 6 |

## 🎨 Estructura HTML

```html
<div class="home-v1-deals-and-tabs deals-and-tabs row">
    <!-- Oferta Especial -->
    <div class="deals-block col-md-6 col-lg-5 col-xl-4">
        <section class="section-onsale-product">
            <header>
                <h2 class="h1">Título de la Oferta</h2>
                <div class="savings">
                    <span class="savings-text">Texto de Ahorro</span>
                </div>
            </header>
            <div class="onsale-products">
                <div class="onsale-product">
                    <!-- Producto con countdown -->
                </div>
            </div>
        </section>
    </div>
    
    <!-- Tabs de Productos -->
    <div class="tabs-block col-md-6 col-lg-7 col-xl-8">
        <div class="products-carousel-tabs">
            <ul class="nav nav-inline">
                <!-- Tabs dinámicos -->
            </ul>
            <div class="tab-content">
                <!-- Contenido de tabs -->
            </div>
        </div>
    </div>
</div>
```

## 🔧 Funcionalidades Técnicas

### **Countdown Timer**
- **JavaScript**: `kintaelectric03-countdown.js`
- **Configuración**: Días personalizables (1-30)
- **Actualización**: Cada segundo
- **Expiración**: Mensaje personalizable cuando termina

### **Consultas WooCommerce**
```php
$args = array(
    'post_type' => 'product',
    'posts_per_page' => $limit,
    'tax_query' => array(
        array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => $category_ids
        )
    ),
    'meta_query' => array(
        array(
            'key' => '_stock_status',
            'value' => 'instock'
        )
    )
);
```

### **Imágenes Optimizadas**
- **Tamaño**: 300x300px automático
- **Lazy Loading**: Habilitado
- **Formato**: WooCommerce thumbnail
- **Fallback**: Placeholder si no hay imagen

### **Integración YITH**
- **Wishlist**: `[yith_wcwl_add_to_wishlist product_id="ID"]` shortcode
- **Compare**: `[yith_compare_button product="ID"]` shortcode
- **Compatibilidad**: Verificación con `defined('YITH_WCWL')` y `defined('YITH_WOOCOMPARE')`

## 📱 Responsive Design

### **Breakpoints**
- **Móvil**: 1-2 columnas
- **Tablet**: 2-4 columnas  
- **Desktop**: 3-6 columnas
- **Extra Large**: Hasta 6 columnas

### **Clases CSS**
```css
.row-cols-1 .row-cols-md-2 .row-cols-lg-3 .row-cols-xl-4 .row-cols-xxl-6
```

## 🚀 Uso del Widget

### **1. Configuración Básica**
1. Arrastra el widget a tu página
2. Configura el título de la oferta
3. Selecciona un producto en oferta
4. Establece los días del countdown

### **2. Configuración de Tabs**
1. Añade tabs en el repeater
2. Asigna categorías a cada tab
3. Configura el número de productos
4. Selecciona el ordenamiento

### **3. Configuración Responsive**
1. Ajusta las columnas para cada dispositivo
2. Prueba en diferentes tamaños de pantalla
3. Optimiza según tu diseño

## 🔍 Debugging

### **Verificar Funcionalidades**
```javascript
// Verificar countdown
console.log($('.deal-countdown-timer .deal-countdown').length);

// Reinicializar countdown
kintaelectric03ReinitCountdown($('.home-v1-deals-and-tabs'));
```

### **Verificar WooCommerce**
```php
// Verificar productos en oferta
$products = wc_get_products(['meta_query' => [['key' => '_sale_price', 'value' => '', 'compare' => '!=']]]);
var_dump(count($products));
```

## 📋 Requisitos

- ✅ **WordPress**: 5.0+
- ✅ **Elementor**: 3.0+
- ✅ **WooCommerce**: 5.0+
- ✅ **YITH Wishlist**: Opcional (recomendado)
- ✅ **YITH Compare**: Opcional (recomendado)

## 🎯 Casos de Uso

1. **Página de Inicio**: Destacar ofertas especiales
2. **Páginas de Categoría**: Mostrar productos por categoría
3. **Landing Pages**: Promociones con countdown
4. **Páginas de Producto**: Productos relacionados
5. **Blog Posts**: Productos destacados

## 🔧 Personalización

### **CSS Personalizado**
```css
.home-v1-deals-and-tabs .deal-countdown-timer {
    /* Estilos personalizados */
}

.home-v1-deals-and-tabs .products .product {
    /* Estilos de productos */
}
```

### **JavaScript Personalizado**
```javascript
// Hook para después de inicializar countdown
$(document).on('kintaelectric03:countdown:init', function(e, $countdown) {
    // Código personalizado
});
```

## 📈 Rendimiento

- ✅ **Consultas optimizadas** con índices de base de datos
- ✅ **Lazy loading** de imágenes
- ✅ **JavaScript modular** y eficiente
- ✅ **CSS del theme** reutilizado
- ✅ **Caché compatible** con plugins de caché

## 🐛 Solución de Problemas

### **Countdown no funciona**
- Verificar que jQuery esté cargado
- Comprobar consola de errores
- Verificar configuración de días

### **Productos no se muestran**
- Verificar que WooCommerce esté activo
- Comprobar categorías seleccionadas
- Verificar stock de productos

### **Wishlist/Compare no funciona**
- Verificar que YITH plugins estén activos
- Comprobar configuración de plugins
- Verificar permisos de usuario

---

**Versión**: 1.0.0  
**Autor**: Kinta Electric Team  
**Última actualización**: Diciembre 2024
