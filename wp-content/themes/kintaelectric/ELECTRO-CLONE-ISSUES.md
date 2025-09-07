# Electro Clone - Archivos Problemáticos Identificados

## ⚠️ Archivos que pueden causar conflictos

### 1. Archivo CSS con fuentes Roboto
**Ubicación**: `wp-content/themes/electro.madrasthemes.com/css`
**Problema**: Contiene definiciones de fuentes Roboto que pueden conflictuar con las fuentes del tema
**Contenido**:
```css
@font-face {
  font-family: 'Roboto';
  font-style: normal;
  font-weight: 300-800;
  src: url(s/roboto/v48/...) format('truetype');
}
```
**Solución**: No incluir este archivo en el tema final

### 2. Archivos de fuentes duplicados
**Ubicación**: `wp-content/themes/electro.madrasthemes.com/s/roboto/`
**Problema**: Fuentes Roboto duplicadas que pueden causar conflictos
**Solución**: Usar solo las fuentes del tema original de Electro

### 3. Archivos de imagen duplicados
**Ubicación**: `wp-content/themes/electro.madrasthemes.com/3x/`, `4x/`
**Problema**: Imágenes en diferentes resoluciones que pueden no ser necesarias
**Solución**: Usar solo las imágenes necesarias del tema original

## ✅ Archivos útiles para análisis

### 1. Páginas de inicio
- `home-v2/index.htm` - Value of the Day
- `home-v3/index.htm` - Top 100 Offers
- `home-v3-full-color-background/index.htm` - New Arrivals
- `home-v4/index.htm` - Modern Layout
- etc.

### 2. Páginas de tienda
- `shop/index.htm` - Página principal de tienda
- `product-category/` - Categorías de productos
- `product/` - Páginas individuales de productos

### 3. Páginas de blog
- `blog/index.htm` - Página principal del blog
- `blog-v2/index.htm`, `blog-v3/index.htm` - Variantes de blog

### 4. Páginas de contacto
- `contact-v1/index.htm` - Contacto v1
- `contact-v2/index.htm` - Contacto v2

## 🔧 Correcciones implementadas

### 1. Sistema de Headers y Footers por Homepage
- Cada variante de homepage ahora usa su header y footer correspondiente
- `homepage-v1` → `header-v1` + `footer-v1`
- `homepage-v2` → `header-v2` + `footer-v2`
- etc.

### 2. Eliminación de archivos problemáticos
- No se incluyen archivos CSS duplicados
- No se incluyen fuentes duplicadas
- Solo se usan los assets del tema original de Electro

### 3. Estructura correcta de templates
- Headers y footers específicos para cada variante
- Plantillas de homepage que cargan el header/footer correcto
- Sistema de personalizador que mantiene consistencia

