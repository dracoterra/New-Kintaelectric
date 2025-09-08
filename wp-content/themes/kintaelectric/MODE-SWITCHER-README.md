# Electro Mode Switcher - Documentación

## 📋 Descripción
El Electro Mode Switcher es una funcionalidad que permite a los usuarios alternar entre modo claro y oscuro en el tema KintaElectric. La preferencia del usuario se guarda en localStorage y se mantiene en todas las páginas.

## 🚀 Características

### ✅ Funcionalidades Principales
- **Persistencia**: El modo seleccionado se guarda en localStorage
- **Sincronización**: Cambios en una pestaña se reflejan en otras pestañas abiertas
- **Detección del sistema**: Respeta la preferencia del sistema operativo si no hay modo guardado
- **Responsive**: Se oculta en dispositivos móviles (pantallas < 1200px)
- **Accesibilidad**: Incluye atributos ARIA y tooltips para mejor UX

### 🎨 Estilos CSS
- **Archivo**: `assets/css/electro-mode-switcher.css`
- **Clase principal**: `.electro-mode-switcher`
- **Estados**: `.active` para el botón activo
- **Transiciones**: Animaciones suaves de 0.3s
- **Responsive**: Oculto en móviles

### ⚙️ JavaScript
- **Archivo**: `assets/js/electro-mode-switcher.js`
- **Dependencias**: jQuery
- **Eventos**: Click en botones, storage changes, system preference changes
- **Clase CSS aplicada**: `electro-dark` en el body

## 🔧 Configuración

### En el Customizer
1. Ve a **Apariencia > Personalizar > Header Settings**
2. Activa/desactiva **"Enable Dark/Light Mode Switcher"**

### En el Código
```php
// En header.php
$enable_mode_switcher = get_theme_mod( 'kintaelectric_enable_mode_switcher', true );
if ( $enable_mode_switcher ) :
    // Mostrar el switcher
endif;
```

## 📁 Archivos Involucrados

### PHP
- `header.php` - HTML del switcher
- `functions.php` - Enqueue de scripts y estilos

### CSS
- `style.css` - Estilos base del tema (líneas 13579-13633)
- `assets/css/electro-mode-switcher.css` - Estilos adicionales

### JavaScript
- `assets/js/electro-mode-switcher.js` - Lógica principal

## 🎯 Cómo Funciona

### 1. Inicialización
```javascript
// Al cargar la página
const currentMode = getCurrentMode(); // Lee de localStorage
applyMode(currentMode); // Aplica la clase al body
updateButtonStates(currentMode); // Actualiza botones
```

### 2. Cambio de Modo
```javascript
// Al hacer click en un botón
switchMode(mode); // Aplica modo, guarda en localStorage, actualiza UI
```

### 3. Sincronización entre Pestañas
```javascript
// Escucha cambios en localStorage
$(window).on('storage', function(e) {
    if (e.originalEvent.key === 'electro-theme-mode') {
        // Sincroniza con otras pestañas
    }
});
```

### 4. Detección del Sistema
```javascript
// Detecta preferencia del sistema
if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    return DARK_MODE;
}
```

## 🎨 Personalización

### Cambiar Colores
```css
/* En electro-mode-switcher.css */
.electro-mode-switcher .dark {
    background-color: #tu-color-oscuro;
    color: #tu-color-texto;
}

.electro-mode-switcher .light {
    background-color: #tu-color-claro;
    color: #tu-color-texto;
}
```

### Cambiar Posición
```css
/* En style.css */
.electro-mode-switcher {
    position: fixed;
    top: 50%;
    left: 5vh; /* Cambia a right: 5vh para lado derecho */
}
```

### Cambiar Tamaño
```css
.electro-mode-switcher {
    height: 106px; /* Altura */
    width: 30px;   /* Ancho */
}
```

## 🐛 Solución de Problemas

### El switcher no aparece
1. Verifica que esté habilitado en el Customizer
2. Comprueba que la pantalla sea >= 1200px de ancho
3. Revisa la consola del navegador por errores JavaScript

### El modo no se guarda
1. Verifica que localStorage esté habilitado
2. Comprueba la consola por errores JavaScript
3. Asegúrate de que jQuery esté cargado

### Los estilos no se aplican
1. Verifica que el CSS esté enqueueado correctamente
2. Comprueba que no haya conflictos con otros estilos
3. Limpia la caché del navegador

## 📱 Responsive

### Desktop (≥1200px)
- Switcher visible
- Posición fija en el lado izquierdo
- Altura: 106px, Ancho: 30px

### Mobile (<1200px)
- Switcher oculto
- `display: none !important`

## 🔍 Debug

### Console Logs
El script incluye logs detallados:
```javascript
console.log('Electro Mode Switcher: Script loaded');
console.log('Electro Mode Switcher: Mode saved to localStorage:', mode);
console.log('Electro Mode Switcher: Dark mode applied');
```

### Verificar localStorage
```javascript
// En la consola del navegador
localStorage.getItem('electro-theme-mode');
// Debería devolver 'light' o 'dark'
```

## 🚀 Mejoras Futuras

### Posibles Mejoras
- [ ] Animaciones más suaves
- [ ] Más opciones de tema (auto, light, dark)
- [ ] Iconos en lugar de texto
- [ ] Integración con preferencias del sistema en tiempo real
- [ ] Modo de alto contraste
- [ ] Persistencia en servidor (usuario logueado)

## 📝 Notas Técnicas

### Clases CSS Aplicadas
- `electro-dark` - Se aplica al body en modo oscuro
- `active` - Se aplica al botón del modo actual
- `electro-mode-switcher` - Contenedor principal

### Claves de localStorage
- `electro-theme-mode` - Almacena 'light' o 'dark'

### Eventos JavaScript
- `click` - En botones del switcher
- `storage` - Para sincronización entre pestañas
- `change` - En media query de preferencia del sistema
