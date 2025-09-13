# Clase Base para Widgets de Kinta Electric Elementor

## Descripción

La clase `KEE_Base_Widget` proporciona una base estándar para todos los widgets del plugin, asegurando que automáticamente carguen las dependencias del tema y proporcionen funcionalidades comunes.

## Características Principales

### ✅ **Dependencias Automáticas del Tema**
- **Scripts:** `bootstrap-bundle`, `electro-main`
- **Estilos:** `electro-style`
- **Bootstrap CSS:** Variables y clases disponibles automáticamente

### ✅ **Funcionalidades WooCommerce**
- Verificación de plugins activos
- Métodos helper para productos
- Integración con YITH Wishlist y Compare

### ✅ **Métodos Helper Incluidos**
- `get_products_on_sale()` - Lista de productos en oferta
- `get_product_categories()` - Lista de categorías
- `render_yith_wishlist_button()` - Botón de wishlist
- `render_yith_compare_button()` - Botón de comparar
- `get_product_image()` - Imagen del producto
- `get_product_price()` - Precio del producto
- `get_product_title()` - Título del producto
- `get_product_url()` - URL del producto

## Cómo Usar

### 1. Extender la Clase Base

```php
class KEE_Mi_Widget extends KEE_Base_Widget {
    // Tu código del widget aquí
}
```

### 2. Agregar Dependencias Específicas (Opcional)

```php
protected function get_widget_script_depends() {
    return ['mi-script-personalizado'];
}

protected function get_widget_style_depends() {
    return ['mi-estilo-personalizado'];
}
```

### 3. Usar Métodos Helper

```php
// En el método render()
$product_id = 123;
$image = $this->get_product_image($product_id, 'woocommerce_thumbnail');
$price = $this->get_product_price($product_id);
$wishlist_button = $this->render_yith_wishlist_button($product_id);
```

## Beneficios

### 🚀 **Consistencia Automática**
- Todos los widgets cargan automáticamente Bootstrap y estilos del tema
- No hay que recordar agregar dependencias manualmente
- Diseño consistente en todos los widgets

### 🚀 **Desarrollo Más Rápido**
- Métodos helper predefinidos para WooCommerce
- Verificaciones automáticas de plugins
- Integración lista con YITH

### 🚀 **Mantenimiento Simplificado**
- Cambios en dependencias se aplican automáticamente
- Código centralizado y reutilizable
- Menos duplicación de código

## Ejemplo Completo

```php
class KEE_Ejemplo_Widget extends KEE_Base_Widget {
    
    public function get_name() {
        return 'ejemplo-widget';
    }
    
    public function get_title() {
        return 'Widget de Ejemplo';
    }
    
    // Dependencias específicas del widget
    protected function get_widget_script_depends() {
        return ['mi-script'];
    }
    
    protected function get_widget_style_depends() {
        return ['mi-estilo'];
    }
    
    protected function register_controls() {
        // Controles del widget
    }
    
    protected function render() {
        // Verificar que WooCommerce esté activo
        if (!$this->is_woocommerce_active()) {
            echo 'WooCommerce no está activo';
            return;
        }
        
        // Usar métodos helper
        $products = $this->get_products_on_sale();
        $categories = $this->get_product_categories();
        
        // Renderizar HTML con clases de Bootstrap
        echo '<div class="container">';
        echo '<div class="row">';
        // ... resto del código
    }
}
```

## Notas Importantes

- **Siempre extender de `KEE_Base_Widget`** en lugar de `\Elementor\Widget_Base`
- **Las dependencias del tema se cargan automáticamente** - no es necesario agregarlas manualmente
- **Los métodos helper verifican automáticamente** si los plugins están activos
- **Bootstrap y estilos del tema están disponibles** en todos los widgets

## Migración de Widgets Existentes

Para migrar un widget existente:

1. Cambiar `extends \Elementor\Widget_Base` por `extends KEE_Base_Widget`
2. Cambiar `get_script_depends()` por `get_widget_script_depends()`
3. Cambiar `get_style_depends()` por `get_widget_style_depends()`
4. Eliminar dependencias del tema (ya se cargan automáticamente)
5. Usar métodos helper cuando sea posible
