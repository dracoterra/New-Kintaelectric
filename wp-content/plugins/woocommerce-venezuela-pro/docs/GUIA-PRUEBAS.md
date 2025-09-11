# 🧪 Guía de Pruebas - WooCommerce Venezuela Pro

## 🎯 **RESUMEN EJECUTIVO**

Esta guía proporciona instrucciones detalladas para probar cada funcionalidad del plugin **WooCommerce Venezuela Pro**, asegurando que todas las características funcionen correctamente antes de usar en producción.

---

## 📋 **ÍNDICE DE PRUEBAS**

1. [Configuración Inicial](#1-configuración-inicial)
2. [Campos Venezolanos](#2-campos-venezolanos)
3. [Sistema de Precios](#3-sistema-de-precios)
4. [Pasarelas de Pago](#4-pasarelas-de-pago)
5. [Sistema IGTF](#5-sistema-igtf)
6. [Delivery Local](#6-delivery-local)
7. [Integración Cashea](#7-integración-cashea)
8. [Notificaciones WhatsApp](#8-notificaciones-whatsapp)
9. [Reportes Fiscales](#9-reportes-fiscales)
10. [Verificación de Pagos](#10-verificación-de-pagos)
11. [Facturación Legal](#11-facturación-legal)
12. [Pruebas de Integración](#12-pruebas-de-integración)

---

## 1. CONFIGURACIÓN INICIAL

### **1.1 Verificar Instalación del Plugin**

**Objetivo:** Confirmar que el plugin está correctamente instalado y activo.

**Pasos:**
1. Ir a **Plugins → Plugins Instalados**
2. Buscar **"WooCommerce Venezuela Pro"**
3. Verificar que esté **Activo**
4. Verificar que no haya errores de activación

**Resultado Esperado:**
- ✅ Plugin aparece como "Activo"
- ✅ Sin errores en la consola
- ✅ Menú "Venezuela Pro" visible en WooCommerce

### **1.2 Verificar Dependencias**

**Objetivo:** Confirmar que todas las dependencias están instaladas.

**Pasos:**
1. Ir a **WooCommerce → Venezuela Pro → Información del Plugin**
2. Verificar estado de dependencias:
   - **WooCommerce:** Debe estar activo
   - **BCV Dólar Tracker:** Debe estar activo
   - **Tasa BCV:** Debe mostrar valor actual

**Resultado Esperado:**
- ✅ Todas las dependencias en estado "Activo"
- ✅ Tasa BCV mostrando valor numérico
- ✅ Sin errores de dependencias

---

## 2. CAMPOS VENEZOLANOS

### **2.1 Probar Campo Cédula/RIF en Checkout**

**Objetivo:** Verificar que el campo de Cédula/RIF aparece y funciona correctamente.

**Pasos:**
1. Ir a **Productos → Todos los productos**
2. Añadir un producto al carrito
3. Ir al **Checkout**
4. Verificar que aparece el campo **"Cédula/RIF"**
5. Probar diferentes formatos:
   - **V-12345678** (Válido)
   - **E-12345678** (Válido)
   - **J-12345678** (Válido)
   - **12345678** (Inválido)
   - **V-123** (Inválido)

**Resultado Esperado:**
- ✅ Campo aparece en checkout
- ✅ Validación funciona correctamente
- ✅ Mensajes de error apropiados
- ✅ Campo se guarda en el pedido

### **2.2 Verificar en Admin de Pedidos**

**Objetivo:** Confirmar que la Cédula/RIF se guarda y muestra correctamente.

**Pasos:**
1. Completar un pedido con Cédula/RIF válida
2. Ir a **WooCommerce → Pedidos**
3. Abrir el pedido creado
4. Verificar en el **Meta Box "Datos Venezuela"**
5. Confirmar que aparece la Cédula/RIF

**Resultado Esperado:**
- ✅ Cédula/RIF visible en meta box
- ✅ Formato correcto mostrado
- ✅ Datos guardados correctamente

---

## 3. SISTEMA DE PRECIOS

### **3.1 Probar Mostrar Precios en Productos**

**Objetivo:** Verificar que los precios se muestran con referencia en bolívares.

**Pasos:**
1. Ir a **Productos → Todos los productos**
2. Abrir un producto
3. Verificar que aparece:
   - Precio en USD
   - Referencia en bolívares (ej: "~ 45.000 Bs.")
4. Verificar formato configurado

**Resultado Esperado:**
- ✅ Precio principal en USD
- ✅ Referencia en bolívares visible
- ✅ Formato correcto según configuración

### **3.2 Probar en Carrito**

**Objetivo:** Verificar que los precios se muestran correctamente en el carrito.

**Pasos:**
1. Añadir productos al carrito
2. Ir al **Carrito**
3. Verificar que aparece:
   - Subtotal en USD
   - Referencia en bolívares
4. Probar con diferentes cantidades

**Resultado Esperado:**
- ✅ Precios en USD correctos
- ✅ Referencia en bolívares actualizada
- ✅ Cálculos correctos

### **3.3 Probar en Checkout**

**Objetivo:** Verificar que los precios se muestran correctamente en checkout.

**Pasos:**
1. Ir al **Checkout**
2. Verificar que aparece:
   - Subtotal en USD
   - Referencia en bolívares
   - Total en USD
3. Probar con diferentes métodos de pago

**Resultado Esperado:**
- ✅ Todos los precios en USD
- ✅ Referencia en bolívares visible
- ✅ Totales correctos

---

## 4. PASARELAS DE PAGO

### **4.1 Configurar Pasarelas**

**Objetivo:** Configurar todas las pasarelas de pago venezolanas.

**Pasos:**
1. Ir a **WooCommerce → Configuración → Pagos**
2. Configurar cada pasarela:

#### **Zelle:**
- ✅ Activar
- ✅ Configurar email de Zelle
- ✅ Desactivar IGTF (transferencia digital)

#### **Pago Móvil:**
- ✅ Activar
- ✅ Configurar CI, teléfono y banco
- ✅ Desactivar IGTF (transferencia digital)

#### **Efectivo USD:**
- ✅ Activar
- ✅ Activar IGTF (billetes USD)

#### **Efectivo Bolívares:**
- ✅ Activar
- ✅ Desactivar IGTF (billetes Bs.)

#### **Cashea:**
- ✅ Activar
- ✅ Configurar API Keys (sandbox)
- ✅ Configurar montos mínimos/máximos

**Resultado Esperado:**
- ✅ Todas las pasarelas configuradas
- ✅ Configuraciones guardadas correctamente
- ✅ Pasarelas aparecen en checkout

### **4.2 Probar Cada Pasarela**

**Objetivo:** Verificar que cada pasarela funciona correctamente.

**Pasos:**
1. Crear pedido de prueba
2. Probar cada método de pago:
   - **Zelle:** Completar pedido
   - **Pago Móvil:** Completar pedido
   - **Efectivo USD:** Completar pedido
   - **Efectivo Bolívares:** Completar pedido
   - **Cashea:** Probar redirección

**Resultado Esperado:**
- ✅ Cada pasarela procesa correctamente
- ✅ Pedidos se crean con estado correcto
- ✅ Metadatos se guardan correctamente

---

## 5. SISTEMA IGTF

### **5.1 Configurar Tasa IGTF**

**Objetivo:** Configurar la tasa de IGTF en el plugin.

**Pasos:**
1. Ir a **WooCommerce → Venezuela Pro → Configuraciones**
2. En la sección **"Configuraciones Generales"**:
   - ✅ Activar "Mostrar IGTF en checkout"
   - ✅ Configurar "Tasa IGTF (%)" (ej: 3%)
3. Guardar cambios

**Resultado Esperado:**
- ✅ Configuración guardada
- ✅ Tasa IGTF configurada correctamente

### **5.2 Probar Aplicación de IGTF**

**Objetivo:** Verificar que IGTF se aplica solo a pagos en efectivo USD.

**Pasos:**
1. Crear pedido con **Efectivo USD**
2. En checkout, verificar que aparece:
   - Subtotal en USD
   - IGTF calculado
   - Total con IGTF
3. Crear pedido con **Zelle** o **Pago Móvil**
4. Verificar que NO aparece IGTF

**Resultado Esperado:**
- ✅ IGTF aparece solo en Efectivo USD
- ✅ Cálculo correcto del IGTF
- ✅ IGTF NO aparece en transferencias digitales

### **5.3 Verificar en Admin**

**Objetivo:** Confirmar que IGTF se guarda correctamente en el pedido.

**Pasos:**
1. Completar pedido con IGTF
2. Ir a **WooCommerce → Pedidos**
3. Abrir el pedido
4. Verificar en **Meta Box "Datos Venezuela"**:
   - Monto del IGTF
   - Tasa aplicada
   - Tipo de pago

**Resultado Esperado:**
- ✅ IGTF guardado en metadatos
- ✅ Información visible en admin
- ✅ Datos correctos

---

## 6. DELIVERY LOCAL

### **6.1 Configurar Zonas de Envío**

**Objetivo:** Configurar el sistema de delivery local por zonas.

**Pasos:**
1. Ir a **WooCommerce → Configuración → Envíos**
2. Crear nueva zona: **"Caracas y Miranda"**
3. Añadir método: **"Delivery Local (Venezuela)"**
4. Configurar zonas:
   - **Chacao:** $5.00
   - **Las Mercedes:** $7.00
   - **Altamira:** $6.00
   - **Chuao:** $8.00
5. Guardar configuración

**Resultado Esperado:**
- ✅ Zona creada correctamente
- ✅ Método de envío configurado
- ✅ Zonas y tarifas guardadas

### **6.2 Probar Selector de Zonas**

**Objetivo:** Verificar que el selector de zonas aparece en checkout.

**Pasos:**
1. Crear pedido con dirección en **Distrito Capital** o **Miranda**
2. Ir al **Checkout**
3. Verificar que aparece:
   - Selector de zona de delivery
   - Lista de zonas con precios
   - Campo obligatorio
4. Seleccionar una zona
5. Verificar que el costo se actualiza

**Resultado Esperado:**
- ✅ Selector aparece para DC/Miranda
- ✅ Lista de zonas con precios
- ✅ Cálculo automático de costo
- ✅ Campo obligatorio funciona

### **6.3 Probar con Otras Ubicaciones**

**Objetivo:** Verificar que el selector NO aparece fuera de DC/Miranda.

**Pasos:**
1. Crear pedido con dirección en **Zulia** o **Lara**
2. Ir al **Checkout**
3. Verificar que NO aparece el selector de zona

**Resultado Esperado:**
- ✅ Selector NO aparece fuera de DC/Miranda
- ✅ Checkout funciona normalmente

---

## 7. INTEGRACIÓN CASHEA

### **7.1 Configurar Credenciales**

**Objetivo:** Configurar las credenciales de Cashea para pruebas.

**Pasos:**
1. Ir a **WooCommerce → Configuración → Pagos**
2. Configurar **"Paga con Cashea"**:
   - ✅ Activar
   - ✅ Modo: **Sandbox**
   - ✅ API Key Sandbox: (credencial de prueba)
   - ✅ Monto mínimo: $50
   - ✅ Monto máximo: $5000
   - ✅ Estado post-pago: **Procesando**
3. Guardar configuración

**Resultado Esperado:**
- ✅ Pasarela configurada correctamente
- ✅ Credenciales guardadas
- ✅ Configuración válida

### **7.2 Probar Creación de Transacción**

**Objetivo:** Verificar que se crea correctamente la transacción en Cashea.

**Pasos:**
1. Crear pedido con total entre $50-$5000
2. Seleccionar **"Paga con Cashea"**
3. Completar checkout
4. Verificar que:
   - Se redirige a Cashea
   - URL de checkout se genera
   - Metadatos se guardan

**Resultado Esperado:**
- ✅ Redirección a Cashea exitosa
- ✅ URL de checkout válida
- ✅ Metadatos guardados en pedido

### **7.3 Probar Webhook**

**Objetivo:** Verificar que el webhook procesa correctamente las notificaciones.

**Pasos:**
1. Configurar webhook en panel de Cashea:
   ```
   https://tusitioweb.com/?wc-api=wvp_cashea_callback
   ```
2. Simular notificación de pago aprobado
3. Verificar que el pedido cambia a **"Procesando"**
4. Revisar logs de debug

**Resultado Esperado:**
- ✅ Webhook configurado correctamente
- ✅ Notificaciones procesadas
- ✅ Estados de pedido actualizados
- ✅ Logs registrados

### **7.4 Probar Control de Montos**

**Objetivo:** Verificar que Cashea solo aparece en rangos válidos.

**Pasos:**
1. Crear pedido con total **menor a $50**
2. Verificar que Cashea NO aparece
3. Crear pedido con total **mayor a $5000**
4. Verificar que Cashea NO aparece
5. Crear pedido con total **entre $50-$5000**
6. Verificar que Cashea SÍ aparece

**Resultado Esperado:**
- ✅ Cashea aparece solo en rango válido
- ✅ Control de montos funciona correctamente

---

## 8. NOTIFICACIONES WHATSAPP

### **8.1 Configurar Plantillas**

**Objetivo:** Configurar las plantillas de mensajes de WhatsApp.

**Pasos:**
1. Ir a **WooCommerce → Venezuela Pro → Configuraciones**
2. En la sección **"Notificaciones WhatsApp"**:
   - ✅ Configurar plantilla de **Pago Verificado**
   - ✅ Configurar plantilla de **Envío**
   - ✅ Usar placeholders: `{customer_name}`, `{order_number}`, etc.
3. Guardar configuración

**Resultado Esperado:**
- ✅ Plantillas configuradas
- ✅ Placeholders funcionan
- ✅ Configuración guardada

### **8.2 Probar Botones en Pedidos**

**Objetivo:** Verificar que los botones de WhatsApp aparecen en pedidos.

**Pasos:**
1. Crear pedido con teléfono válido
2. Ir a **WooCommerce → Pedidos**
3. Abrir el pedido
4. Verificar en **Meta Box "Notificaciones WhatsApp"**:
   - Botón "Notificar Pago"
   - Campo para guía de envío
   - Botón "Notificar Envío"
5. Probar cada botón

**Resultado Esperado:**
- ✅ Botones aparecen correctamente
- ✅ URLs de WhatsApp se generan
- ✅ Mensajes se formatean correctamente

### **8.3 Probar Formato de Números**

**Objetivo:** Verificar que los números se formatean correctamente.

**Pasos:**
1. Crear pedidos con diferentes formatos de teléfono:
   - **0412-1234567** (local)
   - **+584121234567** (internacional)
   - **584121234567** (sin +)
2. Verificar que se convierten a formato internacional

**Resultado Esperado:**
- ✅ Números se formatean a +58...
- ✅ URLs de WhatsApp válidas
- ✅ Conversión automática funciona

---

## 9. REPORTES FISCALES

### **9.1 Probar Libro de Ventas**

**Objetivo:** Verificar que el reporte de Libro de Ventas se genera correctamente.

**Pasos:**
1. Crear varios pedidos completados
2. Ir a **WooCommerce → Reportes Fiscales Vzla**
3. Seleccionar rango de fechas
4. Hacer clic en **"Generar Libro de Ventas"**
5. Verificar que aparece:
   - Tabla con pedidos
   - Conversión a bolívares
   - Datos fiscales completos
6. Probar exportación a CSV

**Resultado Esperado:**
- ✅ Reporte se genera correctamente
- ✅ Datos convertidos a bolívares
- ✅ Exportación CSV funciona
- ✅ Información fiscal completa

### **9.2 Probar Reporte de IGTF**

**Objetivo:** Verificar que el reporte de IGTF se genera correctamente.

**Pasos:**
1. Crear pedidos con IGTF aplicado
2. Ir a **WooCommerce → Reportes Fiscales Vzla**
3. Seleccionar rango de fechas
4. Hacer clic en **"Generar Reporte de IGTF"**
5. Verificar que aparece:
   - Solo pedidos con IGTF
   - Montos en USD
   - Datos de transacción
6. Probar exportación a CSV

**Resultado Esperado:**
- ✅ Solo pedidos con IGTF
- ✅ Montos en USD correctos
- ✅ Exportación CSV funciona
- ✅ Datos completos

---

## 10. VERIFICACIÓN DE PAGOS

### **10.1 Probar Centro de Conciliación**

**Objetivo:** Verificar que el centro de verificación de pagos funciona.

**Pasos:**
1. Crear pedidos en estado **"En Espera"**
2. Ir a **WooCommerce → Verificar Pagos**
3. Verificar que aparece:
   - Lista de pedidos pendientes
   - Datos del cliente
   - Monto y método de pago
   - Formulario de verificación
4. Probar verificación de pago

**Resultado Esperado:**
- ✅ Lista de pedidos pendientes
- ✅ Formularios de verificación
- ✅ Proceso de verificación funciona

### **10.2 Probar Subida de Comprobantes**

**Objetivo:** Verificar que se pueden subir comprobantes de pago.

**Pasos:**
1. En el centro de verificación
2. Seleccionar archivo de comprobante
3. Hacer clic en **"Verificar Pago"**
4. Verificar que:
   - Pedido cambia a **"Procesando"**
   - Archivo se adjunta
   - Nota se añade al pedido

**Resultado Esperado:**
- ✅ Archivo se sube correctamente
- ✅ Pedido se actualiza
- ✅ Nota se añade
- ✅ Lista se actualiza

---

## 11. FACTURACIÓN LEGAL

### **11.1 Configurar Números de Control**

**Objetivo:** Configurar el sistema de números de control SENIAT.

**Pasos:**
1. Ir a **WooCommerce → Venezuela Pro → Configuraciones**
2. En la sección **"Configuraciones Fiscales"**:
   - ✅ Configurar **Prefijo:** "00-"
   - ✅ Configurar **Próximo número:** "1"
3. Guardar configuración

**Resultado Esperado:**
- ✅ Configuración guardada
- ✅ Números de control configurados

### **11.2 Probar Asignación Automática**

**Objetivo:** Verificar que los números de control se asignan automáticamente.

**Pasos:**
1. Crear pedido
2. Cambiar estado a **"Completado"**
3. Verificar que se asigna número de control
4. Verificar formato: **"00-00000001"**
5. Verificar que el próximo número se incrementa

**Resultado Esperado:**
- ✅ Número de control asignado
- ✅ Formato correcto
- ✅ Incremento automático
- ✅ Nota añadida al pedido

### **11.3 Probar Generación de Facturas**

**Objetivo:** Verificar que se pueden generar facturas PDF.

**Pasos:**
1. Ir a un pedido completado
2. En el **Meta Box "Datos Venezuela"**
3. Hacer clic en **"Generar Factura PDF"**
4. Verificar que:
   - PDF se genera
   - Contiene datos fiscales
   - Formato venezolano
   - Número de control incluido

**Resultado Esperado:**
- ✅ PDF se genera correctamente
- ✅ Datos fiscales incluidos
- ✅ Formato venezolano
- ✅ Archivo descargable

---

## 12. PRUEBAS DE INTEGRACIÓN

### **12.1 Flujo Completo de Compra**

**Objetivo:** Probar el flujo completo desde checkout hasta facturación.

**Pasos:**
1. **Checkout:**
   - Añadir productos al carrito
   - Llenar datos con Cédula/RIF válida
   - Seleccionar zona de delivery (si aplica)
   - Seleccionar método de pago
   - Completar pedido

2. **Verificación:**
   - Ir a centro de verificación
   - Verificar pago
   - Subir comprobante

3. **Completar:**
   - Cambiar estado a completado
   - Verificar número de control
   - Generar factura PDF

4. **Notificaciones:**
   - Probar notificaciones WhatsApp
   - Verificar plantillas

**Resultado Esperado:**
- ✅ Flujo completo funciona
- ✅ Todos los datos se guardan
- ✅ Procesos automáticos funcionan
- ✅ Notificaciones se envían

### **12.2 Pruebas de Rendimiento**

**Objetivo:** Verificar que el plugin no afecta el rendimiento del sitio.

**Pasos:**
1. Medir tiempo de carga del checkout
2. Probar con múltiples productos
3. Verificar que no hay errores de memoria
4. Probar con diferentes configuraciones

**Resultado Esperado:**
- ✅ Tiempo de carga aceptable
- ✅ Sin errores de memoria
- ✅ Funcionamiento estable

### **12.3 Pruebas de Compatibilidad**

**Objetivo:** Verificar compatibilidad con otros plugins.

**Pasos:**
1. Activar otros plugins de WooCommerce
2. Probar funcionalidades del plugin
3. Verificar que no hay conflictos
4. Probar con diferentes temas

**Resultado Esperado:**
- ✅ Sin conflictos con otros plugins
- ✅ Compatibilidad con temas
- ✅ Funcionamiento estable

---

## 🔧 **HERRAMIENTAS DE DEBUGGING**

### **1. Logs de WordPress**
```php
// En wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### **2. Logs de WooCommerce**
- **Ubicación:** `wp-content/uploads/wc-logs/`
- **Archivos:** `cashea-YYYY-MM-DD.log`

### **3. Logs del Plugin**
- **Ubicación:** `wp-content/uploads/wc-logs/`
- **Archivos:** `wvp-YYYY-MM-DD.log`

---

## 📊 **CHECKLIST DE PRUEBAS**

### **✅ Configuración Inicial**
- [ ] Plugin instalado y activo
- [ ] Dependencias funcionando
- [ ] Sin errores de activación

### **✅ Campos Venezolanos**
- [ ] Campo Cédula/RIF en checkout
- [ ] Validación funciona
- [ ] Datos se guardan

### **✅ Sistema de Precios**
- [ ] Precios en USD
- [ ] Referencia en bolívares
- [ ] Cálculos correctos

### **✅ Pasarelas de Pago**
- [ ] Todas configuradas
- [ ] Procesamiento funciona
- [ ] Metadatos se guardan

### **✅ Sistema IGTF**
- [ ] Solo en efectivo USD
- [ ] Cálculo correcto
- [ ] Datos guardados

### **✅ Delivery Local**
- [ ] Zonas configuradas
- [ ] Selector en checkout
- [ ] Cálculo automático

### **✅ Cashea**
- [ ] Credenciales configuradas
- [ ] Transacciones se crean
- [ ] Webhook funciona

### **✅ WhatsApp**
- [ ] Plantillas configuradas
- [ ] Botones funcionan
- [ ] Formato de números

### **✅ Reportes Fiscales**
- [ ] Libro de Ventas
- [ ] Reporte de IGTF
- [ ] Exportación CSV

### **✅ Verificación de Pagos**
- [ ] Centro de conciliación
- [ ] Subida de comprobantes
- [ ] Actualización de estados

### **✅ Facturación Legal**
- [ ] Números de control
- [ ] Generación de facturas
- [ ] Metadatos fiscales

---

## 🚨 **PROBLEMAS COMUNES Y SOLUCIONES**

### **1. Plugin No Se Activa**
- **Causa:** Dependencias faltantes
- **Solución:** Instalar WooCommerce y BCV Dólar Tracker

### **2. Tasa BCV No Disponible**
- **Causa:** BCV Dólar Tracker inactivo
- **Solución:** Activar el plugin BCV Dólar Tracker

### **3. IGTF No Se Aplica**
- **Causa:** Configuración incorrecta
- **Solución:** Verificar configuración de pasarelas

### **4. Cashea No Aparece**
- **Causa:** Monto fuera de rango
- **Solución:** Verificar montos mínimos/máximos

### **5. Delivery Local No Funciona**
- **Causa:** Cliente no está en DC/Miranda
- **Solución:** Verificar dirección del cliente

### **6. WhatsApp No Envía**
- **Causa:** Formato de número incorrecto
- **Solución:** Verificar formato internacional

---

## 📞 **SOPORTE TÉCNICO**

### **Información Necesaria:**
1. **Versión del plugin**
2. **Versión de WordPress**
3. **Versión de WooCommerce**
4. **Logs de error**
5. **Pasos para reproducir**

### **Archivos de Log:**
- `wp-content/debug.log`
- `wp-content/uploads/wc-logs/`

---

*Guía de pruebas completa para verificar todas las funcionalidades del plugin WooCommerce Venezuela Pro.*
