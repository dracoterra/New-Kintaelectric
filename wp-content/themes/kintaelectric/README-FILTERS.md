# Widget de Filtros de Productos - Configuración

## Descripción
El widget "Filtros de Productos Electro" permite filtrar productos por atributos personalizados de WooCommerce:
- **Filtros con checkboxes** - Selección múltiple de opciones por atributo
- **Auto-envío de formularios** - Los filtros se aplican automáticamente al seleccionar
- **Solo atributos con productos** - Muestra únicamente atributos que tienen productos asignados
- **Conteos dinámicos** - Los números se actualizan según filtros activos
- **Funcionalidad "Ver más/Ver menos"** - Para listas largas (máximo configurable)
- **Filtros cruzados** - Al filtrar por un atributo, los otros se actualizan

## Configuración Requerida

### 1. Atributos de Producto
Para que el widget funcione correctamente, necesitas crear atributos de producto en WooCommerce:

#### Crear Atributos
1. Ve a **WooCommerce > Productos > Atributos**
2. Crea los atributos que necesites, por ejemplo:
   - **Marcas**: `pa_brands` (Apple, Samsung, LG, etc.)
   - **Colores**: `pa_color` (Black, White, Gold, etc.)
   - **Tamaños**: `pa_size` (S, M, L, XL, etc.)
   - **Materiales**: `pa_material` (Cotton, Polyester, etc.)
3. **Tipo**: `select` (lista desplegable) para todos

### 2. Asignar Atributos a Productos
1. Ve a **Productos > Todos los productos**
2. Edita cada producto
3. En la pestaña **Atributos**, asigna los atributos creados
4. Guarda los cambios

### 3. Configurar el Widget
1. Ve a **Apariencia > Widgets**
2. Busca **"Filtros de Productos Electro"**
3. Arrastra al **"Shop Sidebar"**
4. Configura las opciones:
   - **Título**: "Filtros" (por defecto)
   - **Seleccionar atributos a mostrar**: Checkboxes con todos los atributos disponibles
   - **Máximo de elementos por filtro**: 5 (por defecto)

### 4. Configuración de Atributos
- **Solo atributos con productos**: El widget muestra únicamente atributos que tienen productos asignados
- **Selección múltiple**: Marca los checkboxes de los atributos que quieres mostrar
- **Conteos dinámicos**: Los números se actualizan según los filtros activos

## Funcionalidades

### Filtros Dinámicos e Inteligentes
- **Checkboxes interactivos**: Selecciona múltiples opciones por atributo
- **Auto-envío**: Los filtros se aplican automáticamente al marcar/desmarcar
- **Atributos configurables**: Selecciona qué atributos mostrar en el widget
- **Solo atributos con productos**: Muestra únicamente atributos que tienen productos asignados
- **Conteos dinámicos**: Los números se actualizan según los filtros activos
- **Filtros cruzados**: Al filtrar por un atributo, los otros se actualizan
- **URLs inteligentes**: Los formularios generan URLs con parámetros de filtro
- **Compatible con paginación**: Funciona correctamente con la paginación de WooCommerce

### Ver Más/Ver Menos
- Listas largas se limitan al número configurado (por defecto: 5)
- Botón "+ Ver más" para expandir
- Botón "- Ver menos" para contraer
- Animación suave de 500ms

## Estructura HTML Generada
```html
<aside id="electro_products_filter-1" class="widget widget_electro_products_filter">
    <h3 class="widget-title">Filtros</h3>
    
    <!-- Brands Filter -->
    <aside class="widget woocommerce widget_layered_nav">
        <h3 class="widget-title">Brands</h3>
        <form method="get" class="woocommerce-widget-layered-nav-list">
            <ul class="woocommerce-widget-layered-nav-list">
                <li>
                    <label>
                        <input type="checkbox" name="filter_brands[]" value="apple" checked>
                        <span>Apple</span> <span class="count">(5)</span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="filter_brands[]" value="samsung">
                        <span>Samsung</span> <span class="count">(3)</span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="filter_brands[]" value="lg">
                        <span>LG</span> <span class="count">(2)</span>
                    </label>
                </li>
                <li class="maxlist-more">
                    <a href="#" class="show-more-link">+ Ver más</a>
                </li>
            </ul>
            <button type="submit">Filtrar</button>
        </form>
    </aside>
    
    <!-- Colors Filter -->
    <aside class="widget woocommerce widget_layered_nav">
        <h3 class="widget-title">Color</h3>
        <ul class="woocommerce-widget-layered-nav-list">
            <li><a href="?filter_color=black">Black</a> <span class="count">(8)</span></li>
            <li><a href="?filter_color=white">White</a> <span class="count">(6)</span></li>
            <li><a href="?filter_color=gold">Gold</a> <span class="count">(4)</span></li>
            <li class="maxlist-more" style="display: none;">
                <a href="#" class="show-more-link">+ Ver más</a>
            </li>
        </ul>
    </aside>
    
    <!-- Size Filter -->
    <aside class="widget woocommerce widget_layered_nav">
        <h3 class="widget-title">Size</h3>
        <ul class="woocommerce-widget-layered-nav-list">
            <li><a href="?filter_size=s">S</a> <span class="count">(12)</span></li>
            <li><a href="?filter_size=m">M</a> <span class="count">(15)</span></li>
            <li><a href="?filter_size=l">L</a> <span class="count">(10)</span></li>
            <li class="maxlist-more" style="display: none;">
                <a href="#" class="show-more-link">+ Ver más</a>
            </li>
        </ul>
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
