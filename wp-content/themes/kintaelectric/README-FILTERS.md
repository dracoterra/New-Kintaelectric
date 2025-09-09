# Widget de Filtros de Productos - Configuración

## Descripción
El widget "Electro Products Filter" replica exactamente la funcionalidad del HTML original, incluyendo:
- Filtro de marcas (Brands)
- Filtro de colores (Colors) 
- Filtro de precios con slider
- Funcionalidad "Show more/Show less" para listas largas

## Configuración Requerida

### 1. Atributos de Producto
Para que el widget funcione correctamente, necesitas crear los siguientes atributos de producto en WooCommerce:

#### Marcas (Brands)
1. Ve a **WooCommerce > Productos > Atributos**
2. Crea un nuevo atributo:
   - **Nombre**: `Brands` o `Marcas`
   - **Slug**: `pa_brands`
   - **Tipo**: `select` (lista desplegable)
3. Configura los términos (marcas) como: Apple, Samsung, LG, etc.

#### Colores (Colors)
1. Crea otro atributo:
   - **Nombre**: `Color` o `Colores`
   - **Slug**: `pa_color`
   - **Tipo**: `select` (lista desplegable)
2. Configura los términos (colores) como: Black, White, Gold, etc.

### 2. Asignar Atributos a Productos
1. Ve a **Productos > Todos los productos**
2. Edita cada producto
3. En la pestaña **Atributos**, asigna los atributos creados
4. Guarda los cambios

### 3. Configurar el Widget
1. Ve a **Apariencia > Widgets**
2. Busca **"Electro Products Filter"**
3. Arrastra al **"Shop Sidebar"**
4. Configura las opciones:
   - **Título**: "Filters" (por defecto)
   - **Mostrar filtro de marcas**: ✓
   - **Mostrar filtro de colores**: ✓
   - **Mostrar filtro de precios**: ✓
   - **Máximo de elementos**: 5 (por defecto)

## Funcionalidades

### Filtros Dinámicos
- Los filtros se actualizan automáticamente según los productos disponibles
- Los enlaces generan URLs con parámetros de filtro
- Compatible con la paginación de WooCommerce

### Slider de Precios
- Utiliza el slider nativo de WooCommerce
- Se calcula automáticamente el rango de precios
- Incluye botón "Filter" para aplicar filtros

### Show More/Less
- Listas largas se limitan a 5 elementos por defecto
- Botón "+ Show more" para expandir
- Botón "- Show less" para contraer
- Animación suave de 500ms

## Estructura HTML Generada
```html
<aside id="electro_products_filter-1" class="widget widget_electro_products_filter">
    <h3 class="widget-title">Filters</h3>
    
    <!-- Brands Filter -->
    <aside class="widget woocommerce widget_layered_nav">
        <h3 class="widget-title">Brands</h3>
        <ul class="woocommerce-widget-layered-nav-list">
            <li><a href="?filter_brands=apple">Apple</a> <span class="count">(5)</span></li>
            <!-- Más marcas... -->
        </ul>
    </aside>
    
    <!-- Colors Filter -->
    <aside class="widget woocommerce widget_layered_nav">
        <h3 class="widget-title">Color</h3>
        <ul class="woocommerce-widget-layered-nav-list">
            <li><a href="?filter_color=black">Black</a> <span class="count">(3)</span></li>
            <!-- Más colores... -->
        </ul>
    </aside>
    
    <!-- Price Filter -->
    <aside class="widget woocommerce widget_price_filter">
        <h3 class="widget-title">Price</h3>
        <form method="get" action="/shop/">
            <div class="price_slider_wrapper">
                <div class="price_slider"></div>
                <div class="price_slider_amount">
                    <input type="text" name="min_price" value="50">
                    <input type="text" name="max_price" value="3490">
                    <button type="submit" class="button">Filter</button>
                </div>
            </div>
        </form>
    </aside>
</aside>
```

## Archivos Incluidos
- `class-electro-products-filter-widget.php` - Widget principal
- `hidemaxlistitem.min.js` - Script para show more/less
- CSS incluido en `hello-commerce-woocommerce.css`

## Compatibilidad
- WordPress 5.0+
- WooCommerce 3.0+
- jQuery 1.7+
- Compatible con temas que usen la estructura estándar de WooCommerce
