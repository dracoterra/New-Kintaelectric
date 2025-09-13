# Guía de Uso del Widget Kintaelectric05 Dynamic Products

## 🎯 **Cómo Usar el Widget en Elementor**

### **1. Acceder al Widget**

1. **Abrir Elementor**: Ve a cualquier página o entrada y haz clic en "Editar con Elementor"
2. **Buscar el Widget**: En el panel izquierdo, busca la categoría **"Kinta Electric"**
3. **Seleccionar Widget**: Arrastra **"Kintaelectric05 Dynamic Products"** a tu página

### **2. Configuración Básica**

#### **Pestaña "Contenido"**

**Configuración de Contenido:**
- **Título de la Sección**: "Best Sellers", "Productos Destacados", etc.
- **Fuente de Productos**: 
  - Productos Destacados
  - Productos en Oferta
  - Mejor Valorados
  - Más Recientes
  - Por Categoría
- **Categoría de Productos**: (Solo si seleccionaste "Por Categoría")
- **Número de Productos**: 1-50 productos

**Pestañas de Navegación:**
- **Mostrar Pestañas**: Activar/desactivar
- **Añadir Pestañas**: 
  - Título de la Pestaña
  - Enlace de la Pestaña
  - Pestaña Activa (Solo una puede estar activa)

**Configuración del Carrusel:**
- **Columnas Desktop**: 1-6 columnas
- **Columnas Tablet**: 1-4 columnas
- **Columnas Mobile**: 1-2 columnas
- **Autoplay**: Activar/desactivar
- **Tiempo de Autoplay**: 1000-10000ms
- **Mostrar Puntos de Navegación**: Activar/desactivar

#### **Pestaña "Estilo"**

**Personalización Visual:**
- **Color de Fondo de la Sección**
- **Color del Título**
- **Tipografía del Título**

### **3. Ejemplos de Configuración**

#### **Ejemplo 1: Productos Destacados para Página de Inicio**

```
Configuración de Contenido:
- Título: "Productos Destacados"
- Fuente: "Productos Destacados"
- Número de Productos: 8

Pestañas de Navegación:
- Mostrar Pestañas: Sí
- Pestaña 1: "Top 20" (Activa)
- Pestaña 2: "Smartphones" (Enlace a categoría)
- Pestaña 3: "Laptops" (Enlace a categoría)

Configuración del Carrusel:
- Columnas Desktop: 4
- Columnas Tablet: 3
- Columnas Mobile: 2
- Autoplay: No
- Mostrar Puntos: Sí
```

#### **Ejemplo 2: Ofertas Especiales**

```
Configuración de Contenido:
- Título: "Ofertas Especiales"
- Fuente: "Productos en Oferta"
- Número de Productos: 6

Pestañas de Navegación:
- Mostrar Pestañas: No

Configuración del Carrusel:
- Columnas Desktop: 3
- Columnas Tablet: 2
- Columnas Mobile: 1
- Autoplay: Sí
- Tiempo de Autoplay: 3000ms
- Mostrar Puntos: Sí
```

#### **Ejemplo 3: Productos por Categoría**

```
Configuración de Contenido:
- Título: "Smartphones"
- Fuente: "Por Categoría"
- Categoría: "Smartphones"
- Número de Productos: 12

Pestañas de Navegación:
- Mostrar Pestañas: Sí
- Pestaña 1: "Todos" (Activa)
- Pestaña 2: "Destacados" (Enlace con filtro)
- Pestaña 3: "En Oferta" (Enlace con filtro)

Configuración del Carrusel:
- Columnas Desktop: 4
- Columnas Tablet: 3
- Columnas Mobile: 2
- Autoplay: No
- Mostrar Puntos: Sí
```

### **4. Características del Widget**

#### **✅ Funcionalidades Incluidas:**
- **Carrusel Responsive**: Se adapta a todos los dispositivos
- **Múltiples Fuentes**: Destacados, ofertas, mejor valorados, recientes, por categoría
- **Pestañas de Navegación**: Con enlaces personalizables
- **Autoplay Configurable**: Con tiempo personalizable
- **Integración WooCommerce**: Precios, botones de carrito, stock
- **Plugins YITH**: Wishlist y Compare automáticos
- **Cache Inteligente**: Rendimiento optimizado

#### **🎨 Estilos del Tema:**
- **Hereda automáticamente** los estilos del tema Kinta Electric
- **Estructura HTML idéntica** al tema original
- **Clases CSS** del tema aplicadas correctamente
- **Responsive design** nativo del tema

### **5. Solución de Problemas**

#### **❌ Widget no aparece en Elementor:**
1. Verificar que el plugin esté activo
2. Verificar que Elementor esté activo
3. Verificar que WooCommerce esté activo
4. Limpiar cache del sitio

#### **❌ No se muestran productos:**
1. Verificar que hay productos publicados
2. Verificar la configuración de "Fuente de Productos"
3. Verificar que la categoría seleccionada tiene productos
4. Verificar que los productos están en stock

#### **❌ Carrusel no funciona:**
1. Verificar que Owl Carousel está cargado
2. Verificar la consola del navegador para errores
3. Verificar la configuración de columnas
4. Verificar que jQuery está cargado

#### **❌ Estilos no se aplican:**
1. Verificar que el tema Kinta Electric está activo
2. Verificar que los estilos del tema se cargan
3. Verificar conflictos con otros plugins
4. Limpiar cache del navegador

### **6. Consejos de Uso**

#### **💡 Para Mejor Rendimiento:**
- Usar **menos de 20 productos** por widget
- Activar **cache** del sitio
- Optimizar **imágenes** de productos
- Usar **lazy loading** (ya incluido)

#### **💡 Para Mejor UX:**
- Configurar **pestañas relevantes** para tu audiencia
- Usar **títulos descriptivos** y atractivos
- Ajustar **columnas** según el contenido
- Probar en **diferentes dispositivos**

#### **💡 Para Mejor Diseño:**
- Mantener **consistencia** con el tema
- Usar **colores** que combinen con tu marca
- Ajustar **espaciado** según el layout
- Probar **diferentes configuraciones**

### **7. Casos de Uso Recomendados**

#### **🏠 Página de Inicio:**
- Productos Destacados (8 productos, 4 columnas)
- Con pestañas de categorías principales
- Sin autoplay para mejor control del usuario

#### **🛍️ Página de Tienda:**
- Productos por Categoría (12 productos, 4 columnas)
- Con pestañas de filtros
- Con navegación por puntos

#### **🔥 Página de Ofertas:**
- Productos en Oferta (6 productos, 3 columnas)
- Con autoplay para crear urgencia
- Sin pestañas para simplicidad

#### **📱 Página Mobile:**
- Cualquier fuente (4-6 productos, 1-2 columnas)
- Con autoplay lento
- Con navegación táctil optimizada

---

**¡El widget está listo para usar!** 🚀

Simplemente arrastra el widget a tu página en Elementor y configura las opciones según tus necesidades. El widget se adaptará automáticamente al diseño de tu tema Kinta Electric.
