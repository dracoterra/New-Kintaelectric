# Sistema Avanzado de Precios y Facturación

## 🎯 **Descripción General**

El Sistema Avanzado de Precios y Facturación reemplaza el sistema de referencia estática en bolívares por un sistema interactivo y configurable que incluye:

1. **Switcher de Moneda Interactivo** - Los usuarios pueden alternar entre USD y VES
2. **Desglose Dual** - Referencias VES en carrito y checkout
3. **Facturación Híbrida** - Facturas en VES con nota aclaratoria USD

## ⚙️ **Configuración**

### **Acceso a la Configuración**
1. Ve a **WooCommerce → Venezuela Pro**
2. Busca la sección **"Visualización de Moneda"**
3. Activa las funcionalidades que desees usar

### **Opciones Disponibles**

#### **✅ Activar Switcher de Moneda**
- **Descripción:** Permite a los usuarios alternar entre precios en USD y VES
- **Ubicación:** Páginas de productos y tienda
- **Funcionamiento:** Botones interactivos que cambian la visualización
- **Persistencia:** La preferencia se guarda en localStorage

#### **✅ Mostrar Desglose Dual**
- **Descripción:** Muestra referencias en bolívares en carrito y checkout
- **Ubicación:** Todas las líneas de totales
- **Formato:** `(Ref. 92.500,00 Bs.)`
- **Cálculo:** Usa la tasa BCV del día

#### **✅ Activar Facturación Híbrida**
- **Descripción:** Muestra montos en bolívares en correos y facturas
- **Tasa:** Usa la tasa histórica del pedido (`_bcv_rate_at_purchase`)
- **Nota:** Incluye aclaración del pago original en USD

## 🔧 **Implementación Técnica**

### **Archivos Creados/Modificados**

#### **Nuevos Archivos:**
- `frontend/class-wvp-dual-breakdown.php` - Desglose dual en carrito/checkout
- `frontend/class-wvp-hybrid-invoicing.php` - Facturación híbrida
- `assets/js/price-switcher.js` - JavaScript del switcher

#### **Archivos Modificados:**
- `admin/class-wvp-admin-settings.php` - Nuevas opciones de configuración
- `frontend/class-wvp-price-display.php` - Switcher de moneda
- `assets/css/price-display.css` - Estilos del switcher
- `woocommerce-venezuela-pro.php` - Registro de nuevas clases

### **Hooks Utilizados**

#### **Switcher de Moneda:**
- `woocommerce_get_price_html` - Modifica la visualización de precios

#### **Desglose Dual:**
- `woocommerce_cart_item_price` - Precio de item en carrito
- `woocommerce_cart_item_subtotal` - Subtotal de item en carrito
- `woocommerce_cart_subtotal` - Subtotal del carrito
- `woocommerce_cart_shipping_total` - Envío del carrito
- `woocommerce_cart_tax_totals` - Impuestos del carrito
- `woocommerce_cart_totals_order_total_html` - Total del pedido

#### **Facturación Híbrida:**
- `woocommerce_email_order_details` - Detalles en correos
- `woocommerce_email_order_meta` - Meta del pedido en correos
- `woocommerce_order_details_after_order_table` - Página de pedido

## 🎨 **Interfaz de Usuario**

### **Switcher de Moneda**
```html
<div class="wvp-price-container" data-price-usd="$25.00" data-price-ves="92.500,00 Bs.">
    <span class="wvp-price-display">$25.00</span>
    <div class="wvp-switcher">
        <span class="wvp-usd active" data-currency="usd">USD</span> |
        <span class="wvp-ves" data-currency="ves">VES</span>
    </div>
</div>
```

### **Desglose Dual**
```html
<span class="price">$25.00</span>
<small class="wvp-ves-reference">(Ref. 92.500,00 Bs.)</small>
```

### **Nota de Facturación Híbrida**
```html
<div class="wvp-hybrid-invoicing-note">
    <strong>Nota Importante:</strong><br>
    Transacción procesada en Dólares (USD). Monto total pagado: $25.00. 
    Tasa de cambio aplicada BCV del día 15/12/2024: 1 USD = 3.700,00 Bs.
</div>
```

## 📱 **Responsive Design**

### **Breakpoints:**
- **Desktop:** Tamaño completo del switcher
- **Tablet (≤768px):** Switcher más compacto
- **Mobile (≤480px):** Switcher minimalista

### **Estilos Adaptativos:**
- Tamaños de fuente escalables
- Espaciado optimizado para touch
- Botones táctiles apropiados

## 🔄 **Flujo de Datos**

### **Switcher de Moneda:**
1. **PHP:** Genera HTML con datos de ambas monedas
2. **JavaScript:** Intercambia visualización según preferencia
3. **localStorage:** Guarda preferencia del usuario
4. **CSS:** Aplica estilos según estado activo

### **Desglose Dual:**
1. **Hook:** Intercepta filtros de WooCommerce
2. **Cálculo:** Convierte USD a VES usando tasa BCV
3. **Formato:** Aplica formato venezolano
4. **Inyección:** Añade referencia al HTML existente

### **Facturación Híbrida:**
1. **Tasa Histórica:** Obtiene `_bcv_rate_at_purchase` del pedido
2. **Conversión:** Recalcula todos los montos en VES
3. **Filtros:** Modifica salida de WooCommerce
4. **Nota:** Añade aclaración del pago original

## 🧪 **Testing**

### **Switcher de Moneda:**
1. Ir a página de producto
2. Verificar que aparezcan los botones USD/VES
3. Hacer clic en VES y verificar cambio de precio
4. Recargar página y verificar que se mantenga la preferencia
5. Probar en diferentes páginas de la tienda

### **Desglose Dual:**
1. Añadir productos al carrito
2. Verificar que aparezcan referencias VES
3. Ir al checkout y verificar totales
4. Completar pedido y verificar en admin

### **Facturación Híbrida:**
1. Completar un pedido
2. Verificar correo de confirmación
3. Revisar página de pedido en frontend
4. Verificar nota aclaratoria

## 🐛 **Troubleshooting**

### **Switcher no aparece:**
- Verificar que la opción esté activada en admin
- Revisar consola de JavaScript para errores
- Verificar que BCV Dólar Tracker esté funcionando

### **Referencias VES no aparecen:**
- Verificar que la opción esté activada en admin
- Revisar que la tasa BCV esté disponible
- Verificar logs de WordPress

### **Facturación híbrida no funciona:**
- Verificar que la opción esté activada en admin
- Revisar que el pedido tenga `_bcv_rate_at_purchase`
- Verificar que el pedido esté completado

## 📊 **Rendimiento**

### **Optimizaciones:**
- JavaScript se carga solo si el switcher está activo
- Cálculos VES se hacen solo cuando es necesario
- localStorage evita recálculos innecesarios
- Hooks se registran solo si las opciones están activas

### **Consideraciones:**
- Tasa BCV se obtiene una vez por página
- Cálculos se hacen en tiempo real
- No hay impacto en rendimiento del servidor
- JavaScript es ligero y eficiente

## 🔮 **Futuras Mejoras**

### **Posibles Extensiones:**
- Switcher en widgets de productos
- Integración con plugins de facturas PDF
- Cache de conversiones para mejor rendimiento
- Soporte para múltiples monedas
- Integración con APIs de cambio adicionales

### **Personalización:**
- Estilos personalizables desde admin
- Formatos de número configurables
- Posiciones del switcher personalizables
- Colores y tipografías adaptables
