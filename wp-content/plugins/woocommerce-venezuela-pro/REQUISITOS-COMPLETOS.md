# Requisitos Completos del Plugin WooCommerce Venezuela Pro

## 🎯 **OBJETIVO PRINCIPAL**
Crear un kit de localización completo para WooCommerce en Venezuela que maneje precios en USD con referencia en bolívares, campos venezolanos, pasarelas de pago locales y el sistema de IGTF correctamente.

---

## 📋 **FUNCIONALIDADES OBLIGATORIAS**

### **1. CAMPOS VENEZOLANOS EN CHECKOUT**

#### **1.1. Campo de Cédula/RIF**
- ✅ **Ubicación:** Checkout → Sección de Facturación
- ✅ **Tipo:** Campo obligatorio de texto
- ✅ **Validación:** Formato V-12345678 o J-12345678-9
- ✅ **Posición:** Después del campo de teléfono
- ✅ **Guardado:** Se guarda en el pedido como meta data
- ✅ **Admin:** Se muestra en la página del pedido

#### **1.2. Validación de Cédula/RIF**
- ✅ **Cédula:** V-12345678 o E-12345678 (7-8 dígitos)
- ✅ **RIF:** J-12345678-9 o G-12345678-9 (8 dígitos + 1 dígito verificador)
- ✅ **Mensaje de error:** Claro y específico
- ✅ **Bloqueo:** No permite continuar sin formato válido

---

### **2. SISTEMA DE PRECIOS CON REFERENCIA EN BOLÍVARES**

#### **2.1. Mostrar Precios en USD + Referencia en Bs**
- ✅ **Ubicación:** Páginas de productos, carrito, checkout
- ✅ **Formato:** `$10.00 (Ref. 1.508,00 Bs.)`
- ✅ **Personalizable:** Formato configurable en admin
- ✅ **Tasa automática:** Obtenida del plugin BCV Dólar Tracker
- ✅ **Conversión:** Automática USD → Bs.

#### **2.2. Formato de Números Venezolano**
- ✅ **Separador de miles:** Punto (.)
- ✅ **Separador decimal:** Coma (,)
- ✅ **Ejemplo:** 1.508,50 Bs.
- ✅ **Consistente:** En toda la aplicación

#### **2.3. Configuración de Formato**
- ✅ **Admin:** WooCommerce → Configuración → Venezuela Pro
- ✅ **Campo:** "Formato de Referencia de Precio"
- ✅ **Default:** `(Ref. %s Bs.)`
- ✅ **Placeholder:** `%s` para el precio en bolívares

---

### **3. INTEGRACIÓN CON BCV DÓLAR TRACKER**

#### **3.1. Obtención de Tasa de Cambio**
- ✅ **Fuente:** Plugin BCV Dólar Tracker
- ✅ **Tabla:** `wp_bcv_precio_dolar`
- ✅ **Frecuencia:** Automática cada 12 horas
- ✅ **Fallback:** Manejo de errores si no hay datos

#### **3.2. Conversión de Monedas**
- ✅ **USD → Bs:** Para mostrar referencias
- ✅ **Bs → USD:** Para cálculos internos
- ✅ **Precisión:** 2 decimales
- ✅ **Validación:** Verificar que la tasa sea válida

#### **3.3. Manejo de Errores**
- ✅ **Sin tasa:** No mostrar referencia en bolívares
- ✅ **Tasa inválida:** Log de error, continuar sin referencia
- ✅ **Plugin inactivo:** Aviso en admin

---

### **4. PASARELAS DE PAGO VENEZOLANAS**

#### **4.1. Zelle (Transferencia Digital)**
- ✅ **ID:** `wvp_zelle`
- ✅ **Título:** "Zelle (Venezuela)"
- ✅ **IGTF:** NO aplica (transferencia digital)
- ✅ **Campos:** Email de Zelle, número de confirmación
- ✅ **Validación:** Confirmación obligatoria
- ✅ **Estado:** Pendiente de verificación

#### **4.2. Pago Móvil (Transferencia Digital)**
- ✅ **ID:** `wvp_pago_movil`
- ✅ **Título:** "Pago Móvil (Venezuela)"
- ✅ **IGTF:** NO aplica (transferencia digital)
- ✅ **Campos:** Cédula, teléfono, banco, confirmación
- ✅ **Bancos:** Lista completa de bancos venezolanos
- ✅ **Total en Bs:** Muestra conversión automática

#### **4.3. Efectivo (Billetes USD)**
- ✅ **ID:** `wvp_efectivo`
- ✅ **Título:** "Efectivo (Billetes USD)"
- ✅ **IGTF:** SÍ aplica (3% por defecto)
- ✅ **Campos:** Confirmación de pago
- ✅ **Total:** Muestra subtotal + IGTF
- ✅ **Descripción:** Explica que debe pagar en billetes

#### **4.4. Efectivo (Bolívares)**
- ✅ **ID:** `wvp_efectivo_bolivares`
- ✅ **Título:** "Efectivo (Bolívares)"
- ✅ **IGTF:** NO aplica (pagos en bolívares)
- ✅ **Campos:** Confirmación de pago
- ✅ **Total:** Muestra conversión USD → Bs.
- ✅ **Tasa:** Muestra tasa BCV usada

---

### **5. SISTEMA DE IGTF INTELIGENTE**

#### **5.1. Reglas del IGTF**
- ✅ **SÍ aplica:** Pagos en efectivo con billetes en dólares
- ✅ **NO aplica:** Transferencias digitales (Zelle, Pago Móvil)
- ✅ **NO aplica:** Pagos en bolívares (efectivo o digital)
- ✅ **NO aplica:** Tarjetas de crédito/débito

#### **5.2. Cálculo del IGTF**
- ✅ **Tasa:** 3% por defecto (configurable)
- ✅ **Base:** Total del carrito (incluyendo envío e IVA)
- ✅ **Aplicación:** Solo cuando la pasarela lo permite
- ✅ **Mostrar:** En el checkout antes del pago

#### **5.3. Configuración del IGTF**
- ✅ **Admin:** WooCommerce → Configuración → Venezuela Pro
- ✅ **Tasa:** Campo numérico (3,0% por defecto)
- ✅ **Mostrar:** Checkbox para activar/desactivar
- ✅ **Pasarelas:** Cada pasarela puede activar/desactivar IGTF

---

### **6. INTERFAZ DE ADMINISTRACIÓN**

#### **6.1. Página de Configuración**
- ✅ **Ubicación:** WooCommerce → Configuración → Venezuela Pro
- ✅ **Campos:** Formato de precio, tasa IGTF, mostrar IGTF
- ✅ **Guardado:** Persistencia de configuraciones
- ✅ **Validación:** Campos obligatorios y formatos

#### **6.2. Información en Pedidos**
- ✅ **Cédula/RIF:** Mostrar en página del pedido
- ✅ **Tasa BCV:** Tasa usada en el momento del pedido
- ✅ **IGTF:** Si se aplicó y cuánto
- ✅ **Tipo de pago:** Efectivo, transferencia, etc.

#### **6.3. Columnas en Lista de Pedidos**
- ✅ **Cédula/RIF:** Nueva columna
- ✅ **Tasa BCV:** Nueva columna
- ✅ **IGTF:** Nueva columna
- ✅ **Ordenable:** Por todas las columnas

---

### **7. ESTILOS Y EXPERIENCIA DE USUARIO**

#### **7.1. CSS del Plugin**
- ✅ **Archivo:** `assets/css/checkout.css`
- ✅ **Estilos:** Para campos de cédula/RIF
- ✅ **Responsive:** Adaptable a móviles
- ✅ **Consistente:** Con el tema de WooCommerce

#### **7.2. JavaScript del Plugin**
- ✅ **Archivo:** `assets/js/checkout.js`
- ✅ **Funcionalidad:** Validación en tiempo real
- ✅ **AJAX:** Para actualizaciones dinámicas
- ✅ **UX:** Mensajes claros y feedback visual

---

### **8. VALIDACIONES Y SEGURIDAD**

#### **8.1. Validación de Datos**
- ✅ **Cédula/RIF:** Formato correcto
- ✅ **Confirmaciones:** Campos obligatorios
- ✅ **Sanitización:** Todos los inputs
- ✅ **Escape:** Todos los outputs

#### **8.2. Manejo de Errores**
- ✅ **Logs:** Errores en log de WordPress
- ✅ **Fallbacks:** Continuar sin funcionalidades opcionales
- ✅ **Mensajes:** Claros para el usuario
- ✅ **Debug:** Modo debug para desarrollo

---

### **9. COMPATIBILIDAD Y RENDIMIENTO**

#### **9.1. Compatibilidad**
- ✅ **WooCommerce:** 5.0+
- ✅ **WordPress:** 5.0+
- ✅ **PHP:** 7.4+
- ✅ **BCV Dólar Tracker:** Requerido

#### **9.2. Rendimiento**
- ✅ **Carga:** Solo en páginas necesarias
- ✅ **Caché:** Usar caché de WordPress
- ✅ **Consultas:** Optimizar consultas a BD
- ✅ **Assets:** Minificar CSS/JS

---

### **10. DOCUMENTACIÓN Y SOPORTE**

#### **10.1. Documentación**
- ✅ **README:** Instrucciones de instalación
- ✅ **Funcionalidades:** Lista completa de características
- ✅ **Configuración:** Guía paso a paso
- ✅ **Troubleshooting:** Solución de problemas comunes

#### **10.2. Logs y Debug**
- ✅ **Logs:** Actividad del plugin
- ✅ **Debug:** Modo debug para desarrollo
- ✅ **Errores:** Captura y reporte de errores
- ✅ **Estadísticas:** Uso del plugin

---

## 🚀 **CRITERIOS DE ÉXITO**

### **Funcionalidad Básica:**
- ✅ Campo de cédula/RIF funciona en checkout
- ✅ Precios muestran referencia en bolívares
- ✅ IGTF se aplica solo a pagos en efectivo USD
- ✅ Pasarelas de pago funcionan correctamente

### **Experiencia de Usuario:**
- ✅ Checkout fluido y sin errores
- ✅ Mensajes claros y útiles
- ✅ Validaciones en tiempo real
- ✅ Diseño responsive

### **Administración:**
- ✅ Configuración fácil e intuitiva
- ✅ Información completa en pedidos
- ✅ Logs claros para debugging
- ✅ Documentación completa

---

## ⚠️ **NOTAS IMPORTANTES**

1. **IGTF:** Solo se aplica a pagos en efectivo con billetes en dólares
2. **Transferencias:** Zelle y Pago Móvil NO aplican IGTF
3. **Bolívares:** Pagos en bolívares NO aplican IGTF
4. **BCV:** Requiere plugin BCV Dólar Tracker activo
5. **WooCommerce:** Debe estar configurado en USD

---

## 📞 **SOPORTE TÉCNICO**

- **Logs:** Revisar logs de WordPress para errores
- **Debug:** Activar modo debug en WordPress
- **Dependencias:** Verificar que BCV Dólar Tracker esté activo
- **Configuración:** Revisar configuraciones en admin
