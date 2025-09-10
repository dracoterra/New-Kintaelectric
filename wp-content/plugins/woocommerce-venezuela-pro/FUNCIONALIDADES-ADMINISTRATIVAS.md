# Funcionalidades Administrativas - WooCommerce Venezuela Pro

## 🎯 **RESUMEN EJECUTIVO**

El plugin **WooCommerce Venezuela Pro** ahora incluye un conjunto completo de funcionalidades administrativas avanzadas para la gestión fiscal y operativa en Venezuela, cumpliendo con los requisitos del SENIAT y las mejores prácticas de e-commerce local.

---

## 📊 **MÓDULO 6: CONTABILIDAD Y REPORTES FISCALES**

### **6.1. Página de Reportes Fiscales**
- **Ubicación:** WooCommerce → Reportes Fiscales Vzla
- **Acceso:** Administradores con permisos de WooCommerce
- **Funcionalidades:**
  - Selector de rango de fechas (Desde/Hasta)
  - Generación de reportes en tiempo real
  - Exportación a CSV para análisis externo

### **6.2. Libro de Ventas (Nivel SENIAT)**
- **Propósito:** Cumplimiento fiscal venezolano
- **Datos incluidos:**
  - Fecha de la venta
  - RIF/Cédula del cliente
  - Nombre del cliente
  - Número de factura (ID del pedido)
  - Número de control SENIAT
  - Total de venta con IVA (en Bolívares)
  - Ventas exentas
  - Base imponible (en Bolívares)
  - Monto del IVA (en Bolívares)
- **Conversión automática:** USD a Bolívares usando tasa BCV del momento de la compra
- **Exportación:** CSV para importar en sistemas contables

### **6.3. Reporte de IGTF**
- **Propósito:** Control de comisiones IGTF
- **Datos incluidos:**
  - Fecha del pago
  - Número de pedido
  - Método de pago utilizado
  - Monto base (antes de IGTF en USD)
  - Monto del IGTF cobrado (en USD)
- **Filtrado:** Solo pedidos que aplicaron IGTF
- **Exportación:** CSV para análisis financiero

---

## 🔍 **MÓDULO 7: VERIFICACIÓN DE PAGOS**

### **7.1. Centro de Conciliación de Pagos**
- **Ubicación:** WooCommerce → Verificar Pagos
- **Propósito:** Gestión de pagos pendientes de verificación
- **Funcionalidades:**
  - Lista de pedidos en estado "En Espera"
  - Información completa del cliente y pedido
  - Referencia de pago proporcionada por el cliente
  - Acciones de verificación por pedido

### **7.2. Proceso de Verificación**
- **Verificación de Pago:**
  - Cambia estado del pedido a "Procesando"
  - Añade nota privada con usuario y fecha
  - Registra metadatos de verificación
  - Remueve el pedido de la lista pendiente
- **Subida de Comprobantes:**
  - Soporte para imágenes (JPG, PNG, GIF) y PDFs
  - Validación de tamaño (máximo 5MB)
  - Almacenamiento seguro en directorio del plugin
  - Enlace directo para visualización

### **7.3. Seguimiento de Verificaciones**
- **Metadatos guardados:**
  - `_payment_verified`: Estado de verificación
  - `_payment_verified_by`: ID del usuario que verificó
  - `_payment_verified_date`: Fecha y hora de verificación
  - `_payment_proof_url`: URL del comprobante subido

---

## 🧾 **MÓDULO 8: FACTURACIÓN LEGAL**

### **8.1. Metadatos Fiscales Automáticos**
- **Al crear pedido se guardan:**
  - `_billing_cedula_rif`: Cédula/RIF del cliente
  - `_bcv_rate_at_purchase`: Tasa BCV del momento de compra
  - `_payment_reference`: Referencia de pago del cliente
  - `_igtf_amount`: Monto exacto de IGTF aplicado
  - `_igtf_rate`: Porcentaje de IGTF utilizado
  - `_payment_type`: Tipo de pago (efectivo/transferencia)

### **8.2. Gestión de Números de Control SENIAT**
- **Configuración:**
  - Prefijo personalizable (ej: "00-")
  - Próximo número de control
  - Incremento automático
- **Asignación automática:**
  - Se asigna al cambiar pedido a "Completado"
  - Formato: Prefijo + número de 8 dígitos (ej: "00-00000001")
  - Se guarda en metadato `_seniat_control_number`
  - Se añade nota al pedido

### **8.3. Generador de Facturas PDF**
- **Ubicación:** Meta box "Datos Venezuela" en pedidos
- **Formato:** Factura venezolana estándar
- **Datos incluidos:**
  - Información de la empresa (RIF, dirección)
  - Datos del cliente (Cédula/RIF, contacto)
  - Detalles de la factura (número, fecha, control)
  - Lista de productos con precios en USD y Bs.
  - Desglose de totales (subtotal, envío, IVA, IGTF)
  - Información de pago y tasa BCV
- **Características:**
  - Diseño responsive y profesional
  - Conversión automática USD a Bolívares
  - Cumple estándares fiscales venezolanos
  - Generación en HTML (fácil conversión a PDF)

---

## 🎛️ **INTERFAZ ADMINISTRATIVA MEJORADA**

### **8.4. Meta Box de Pedidos**
- **Ubicación:** Panel lateral en edición de pedidos
- **Información mostrada:**
  - Cédula/RIF del cliente
  - Tasa BCV al momento de la compra
  - Referencia de pago
  - Monto y porcentaje de IGTF
  - Tipo de pago utilizado
  - Número de control SENIAT
  - Estado de verificación de pago
  - Enlace al comprobante de pago
  - Botón para generar factura PDF

### **8.5. Columnas Personalizadas en Lista de Pedidos**
- **Nuevas columnas:**
  - Cédula/RIF
  - Tasa BCV
  - Monto IGTF
  - Estado de Verificación
- **Características:**
  - Ordenables por cualquier columna
  - Información resumida para gestión rápida
  - Indicadores visuales de estado

### **8.6. Configuraciones Fiscales**
- **Nueva sección:** "Configuraciones Fiscales"
- **Campos disponibles:**
  - Prefijo del Número de Control
  - Próximo Número de Control
- **Funcionalidades:**
  - Asignación automática de números
  - Incremento automático del contador
  - Validación de formato

---

## 🔧 **CONFIGURACIÓN Y USO**

### **Configuración Inicial:**
1. **WooCommerce → Venezuela Pro**
   - Configurar prefijo de número de control
   - Establecer próximo número de control
   - Verificar configuraciones de IGTF

2. **WooCommerce → Reportes Fiscales Vzla**
   - Seleccionar rango de fechas
   - Generar Libro de Ventas
   - Generar Reporte de IGTF
   - Exportar a CSV

3. **WooCommerce → Verificar Pagos**
   - Revisar pedidos pendientes
   - Verificar pagos recibidos
   - Subir comprobantes de pago

### **Flujo de Trabajo Recomendado:**
1. **Cliente realiza pedido** → Se guardan metadatos fiscales
2. **Cliente realiza pago** → Pedido queda "En Espera"
3. **Admin verifica pago** → Cambia a "Procesando"
4. **Pedido se completa** → Se asigna número de control
5. **Generar factura** → PDF con formato venezolano
6. **Reportes fiscales** → Para cumplimiento SENIAT

---

## 📈 **BENEFICIOS EMPRESARIALES**

### **Cumplimiento Fiscal:**
- ✅ Libro de Ventas automático
- ✅ Números de control SENIAT
- ✅ Reportes de IGTF
- ✅ Facturas con formato legal

### **Gestión Operativa:**
- ✅ Verificación centralizada de pagos
- ✅ Seguimiento de comprobantes
- ✅ Metadatos fiscales completos
- ✅ Conversión automática USD/Bs.

### **Eficiencia Administrativa:**
- ✅ Reportes exportables
- ✅ Interfaz intuitiva
- ✅ Automatización de procesos
- ✅ Integración con WooCommerce

---

## 🚀 **PRÓXIMAS MEJORAS SUGERIDAS**

1. **Integración con sistemas contables venezolanos**
2. **Generación de archivos XML para SENIAT**
3. **Dashboard con métricas fiscales**
4. **Notificaciones automáticas de verificación**
5. **Plantillas de factura personalizables**
6. **Integración con bancos venezolanos**

---

## 📞 **SOPORTE TÉCNICO**

Para soporte técnico o consultas sobre las funcionalidades administrativas:
- **Documentación:** Archivos README.md y FUNCIONALIDADES.md
- **Logs:** Revisar logs de WordPress para debugging
- **Configuración:** Verificar permisos de usuario y configuraciones de WooCommerce

---

*Plugin desarrollado específicamente para el mercado venezolano, cumpliendo con todas las regulaciones fiscales y operativas locales.*
