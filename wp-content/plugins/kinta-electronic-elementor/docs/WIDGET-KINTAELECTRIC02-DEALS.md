# Widget Kintaelectric02 Deals

## Descripción
Widget de Elementor que replica el bloque de ofertas/deals del tema Electro, mostrando productos en una cuadrícula responsive con opciones de descuento.

## Características
- **Diseño Responsive**: Se adapta automáticamente a diferentes tamaños de pantalla
- **Configuración de Columnas**: Control independiente para desktop, tablet y móvil
- **Sistema de Descuentos**: Opción para mostrar porcentajes de descuento
- **Enlaces Personalizables**: Cada item puede tener su propio enlace
- **Imágenes Optimizadas**: Soporte para lazy loading
- **Efectos Hover**: Animaciones suaves al pasar el mouse

## Uso en Elementor

### 1. Agregar el Widget
1. Abre el editor de Elementor
2. Busca la categoría "Kinta Electric"
3. Arrastra el widget "Kintaelectric02 Deals" a tu página

### 2. Configuración General
- **Columnas Desktop**: 2, 3, 4 o 6 columnas
- **Columnas Tablet**: 1, 2 o 3 columnas  
- **Columnas Mobile**: 1 o 2 columnas

### 3. Configuración de Items
Para cada item de oferta puedes configurar:

#### Imagen del Producto
- Sube una imagen representativa del producto
- Tamaño recomendado: 173x118px
- Se redimensiona automáticamente

#### Título Principal
- Texto principal del deal
- Soporta HTML básico (br, strong, em)
- Ejemplo: `Catch Big <br><strong>Deals</strong> on the <br>Cameras`

#### Texto de Acción
- Texto que aparece en la parte inferior
- Por defecto: "Shop now"
- Se puede personalizar

#### URL del Enlace
- Enlace de destino al hacer clic
- Soporte para enlaces externos
- Opción de abrir en nueva pestaña

#### Sistema de Descuentos
- **Mostrar Descuento**: Activar/desactivar
- **Prefijo**: "Upto", "Hasta", etc.
- **Valor**: Número del porcentaje (1-100)
- **Sufijo**: "%", "OFF", etc.

### 4. Personalización de Estilos

#### Colores
- **Color de Fondo**: Fondo del contenedor principal
- **Color de Fondo de Items**: Fondo de cada item individual
- **Color del Título**: Color del texto principal
- **Color del Texto de Acción**: Color del texto de acción

#### Tipografía
- **Tipografía del Título**: Fuente, tamaño, peso, etc.
- **Tipografía del Texto de Acción**: Fuente, tamaño, peso, etc.

#### Espaciado
- **Radio de Borde**: Bordes redondeados de los items
- **Padding de Items**: Espaciado interno de cada item

## Ejemplos de Uso

### Ejemplo 1: Ofertas de Cámaras
```
Título: Catch Big <br><strong>Deals</strong> on the <br>Cameras
Acción: Shop now
Descuento: No
```

### Ejemplo 2: Tablets con Descuento
```
Título: Tablets, <br>Smartphones <br><strong>and more</strong>
Acción: (vacío)
Descuento: Sí
Prefijo: Upto
Valor: 70
Sufijo: %
```

### Ejemplo 3: Productos Destacados
```
Título: Shop the <br><strong>Hottest</strong><br> Products
Acción: Shop now
Descuento: No
```

## Responsive Design

### Desktop (>1200px)
- Muestra el número de columnas configurado
- Layout horizontal con imágenes a la izquierda
- Efectos hover completos

### Tablet (768px - 1199px)
- Se adapta a 1-3 columnas según configuración
- Mantiene el layout horizontal
- Efectos hover reducidos

### Mobile (<768px)
- Máximo 2 columnas
- Layout vertical (imagen arriba, texto abajo)
- Sin efectos hover
- Imágenes más pequeñas

## Integración con WooCommerce

El widget está preparado para integrarse con WooCommerce:

### Hooks Disponibles
```php
// Filtrar la lista de deals
add_filter('kee_deals_list', function($deals) {
    // Modificar la lista de deals
    return $deals;
});

// Filtrar el HTML de cada deal
add_filter('kee_deal_html', function($html, $deal) {
    // Modificar el HTML de cada deal
    return $html;
}, 10, 2);
```

### Shortcode
```php
// Usar como shortcode
[kinta_electronic type="deals" columns="4"]
```

## Personalización Avanzada

### CSS Personalizado
```css
/* Personalizar el contenedor principal */
.home-v1-da-block {
    background-color: #f8f9fa;
    padding: 2rem 0;
}

/* Personalizar items individuales */
.da-inner {
    border: 2px solid #007bff;
    transition: all 0.3s ease;
}

.da-inner:hover {
    border-color: #0056b3;
    transform: scale(1.05);
}

/* Personalizar descuentos */
.upto {
    background: linear-gradient(45deg, #dc3545, #c82333);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
```

### JavaScript Personalizado
```javascript
// Acceder a la instancia del widget
document.addEventListener('DOMContentLoaded', function() {
    const dealsWidget = document.querySelector('.home-v1-da-block');
    
    if (dealsWidget) {
        // Agregar funcionalidad personalizada
        dealsWidget.addEventListener('click', function(e) {
            console.log('Deal clicked:', e.target);
        });
    }
});
```

## Solución de Problemas

### Problema: Los items no se alinean correctamente
**Solución**: Verifica que las columnas estén configuradas correctamente para cada breakpoint.

### Problema: Las imágenes no se cargan
**Solución**: Asegúrate de que las imágenes estén subidas correctamente y tengan los permisos adecuados.

### Problema: Los enlaces no funcionan
**Solución**: Verifica que las URLs estén configuradas correctamente en la configuración del widget.

### Problema: Los estilos no se aplican
**Solución**: Limpia la caché del sitio y verifica que el plugin esté activo.

## Compatibilidad

- **WordPress**: 5.0+
- **Elementor**: 3.0+
- **WooCommerce**: 4.0+ (opcional)
- **PHP**: 7.4+
- **Navegadores**: Chrome, Firefox, Safari, Edge (últimas 2 versiones)

## Changelog

### Versión 1.0.0
- Lanzamiento inicial
- Widget básico de deals
- Soporte responsive
- Sistema de descuentos
- Integración con Elementor

---

**Desarrollado por**: Kinta Electric  
**Versión**: 1.0.0  
**Última actualización**: Diciembre 2024
