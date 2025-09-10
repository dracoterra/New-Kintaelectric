# Funcionalidades del Plugin WooCommerce Venezuela Pro

## 🎯 **Funcionalidades Principales**

### **1. Campo de Cédula/RIF en el Checkout**
- ✅ **Campo obligatorio** de Cédula o RIF en el checkout
- ✅ **Validación automática** del formato (V-12345678 o J-12345678-9)
- ✅ **Se guarda en el pedido** y se muestra en el admin
- ✅ **Ubicación:** Checkout → Sección de Facturación

### **2. Precios con Referencia en Bolívares**
- ✅ **Muestra precios en USD** con referencia en bolívares
- ✅ **Formato personalizable:** `(Ref. %s Bs.)`
- ✅ **Tasa BCV automática** obtenida del plugin BCV Dólar Tracker
- ✅ **Ubicación:** Páginas de productos, carrito, checkout

### **3. Pasarelas de Pago Venezolanas**

#### **3.1. Zelle (Transferencia Digital)**
- ✅ **NO aplica IGTF** (transferencias digitales)
- ✅ **Campo de confirmación** obligatorio
- ✅ **Configuración de email** de Zelle

#### **3.2. Pago Móvil (Transferencia Digital)**
- ✅ **NO aplica IGTF** (transferencias digitales)
- ✅ **Muestra total en bolívares** con tasa BCV
- ✅ **Datos bancarios** configurables
- ✅ **Campo de confirmación** obligatorio

#### **3.3. Efectivo (Billetes USD)**
- ✅ **SÍ aplica IGTF** (3% por defecto)
- ✅ **Muestra total con IGTF** incluido
- ✅ **Solo para pagos en billetes** de dólares

#### **3.4. Efectivo (Bolívares)**
- ✅ **NO aplica IGTF** (pagos en bolívares)
- ✅ **Muestra total en bolívares** con tasa BCV
- ✅ **Conversión automática** USD → Bs.

### **4. Sistema de IGTF Inteligente**
- ✅ **Solo se aplica** a pagos en efectivo con billetes en dólares
- ✅ **NO se aplica** a transferencias digitales (Zelle, Pago Móvil)
- ✅ **NO se aplica** a pagos en bolívares
- ✅ **Tasa configurable** (3% por defecto)
- ✅ **Se puede desactivar** globalmente

### **5. Integración con BCV Dólar Tracker**
- ✅ **Tasa automática** del Banco Central de Venezuela
- ✅ **Actualización cada 12 horas**
- ✅ **Conversión automática** USD ↔ Bs.
- ✅ **Formato venezolano** de números (1.234,56)

## 🔧 **Configuración del Plugin**

### **Configuraciones Generales:**
1. **Formato de Referencia:** `(Ref. %s Bs.)`
2. **Tasa IGTF:** `3,0%`
3. **Mostrar IGTF:** Activado/Desactivado

### **Configuración de Pasarelas:**
1. **Zelle:** Email de Zelle, título, descripción
2. **Pago Móvil:** Cédula, teléfono, banco, título, descripción
3. **Efectivo USD:** Título, descripción (IGTF automático)
4. **Efectivo Bs:** Título, descripción (sin IGTF)

## 📍 **Dónde Encontrar las Funcionalidades**

### **Frontend (Cliente):**
- **Páginas de productos:** Precios con referencia en bolívares
- **Carrito:** Precios con referencia en bolívares
- **Checkout:** Campo de cédula/RIF, pasarelas de pago, IGTF
- **Página de pedido:** Información de pago

### **Backend (Admin):**
- **WooCommerce → Configuración → Pagos:** Configurar pasarelas
- **WooCommerce → Pedidos:** Ver cédula/RIF, tasa BCV, IGTF
- **WooCommerce → Configuración → Venezuela Pro:** Configuraciones generales

## ⚠️ **Reglas del IGTF**

### **IGTF SÍ se aplica:**
- ✅ Pagos en efectivo con **billetes en dólares**
- ✅ Solo cuando la pasarela está configurada para aplicar IGTF

### **IGTF NO se aplica:**
- ❌ Transferencias digitales (Zelle, Pago Móvil)
- ❌ Pagos en efectivo con **billetes en bolívares**
- ❌ Transferencias bancarias
- ❌ Tarjetas de crédito/débito

## 🆕 **Funcionalidades Avanzadas (Nuevas)**

### **6. Sistema Avanzado de Precios y Facturación**

#### **6.1. Switcher de Moneda Interactivo**
- ✅ **Modo interactivo** para alternar entre USD y VES
- ✅ **Preferencia guardada** en localStorage del usuario
- ✅ **Visualización nativa** que se integra con WooCommerce
- ✅ **Ubicación:** Páginas de productos y tienda

#### **6.2. Desglose Dual en Carrito y Checkout**
- ✅ **Referencias VES** en todas las líneas de totales
- ✅ **Cálculo automático** usando tasa BCV del día
- ✅ **Formato venezolano** de números (1.234,56)
- ✅ **Ubicación:** Carrito, checkout, totales

#### **6.3. Facturación Híbrida**
- ✅ **Facturas en VES** usando tasa histórica del pedido
- ✅ **Nota aclaratoria** del pago original en USD
- ✅ **Correos electrónicos** con montos en bolívares
- ✅ **Páginas de pedido** con información híbrida

## 🚀 **Próximos Pasos**

1. **Activar las pasarelas** en WooCommerce → Configuración → Pagos
2. **Configurar los datos** de cada pasarela (email Zelle, datos bancarios, etc.)
3. **Activar funcionalidades avanzadas** en WooCommerce → Venezuela Pro → Visualización de Moneda
4. **Probar el checkout** con diferentes métodos de pago
5. **Verificar que el IGTF** se aplique solo a pagos en efectivo USD
6. **Probar el switcher de moneda** en páginas de productos
7. **Verificar facturación híbrida** en correos y pedidos

## 📞 **Soporte**

Si tienes problemas con alguna funcionalidad:
1. Revisa los logs de WordPress
2. Verifica que BCV Dólar Tracker esté activo
3. Confirma que WooCommerce esté configurado en USD
4. Contacta al administrador del sitio
