# 📋 PLAN DE REVISIÓN DE FUNCIONALIDADES - WOOCOMMERCE VENEZUELA PRO

## 🎯 **OBJETIVO**
Revisar sistemáticamente cada funcionalidad del plugin para verificar que funciona correctamente y documentar los resultados esperados.

---

## 📊 **FASE 1: CONFIGURACIONES BÁSICAS**

### **1.1 Configuraciones Generales**
**Ubicación:** Venezuela Pro → Configuraciones

#### **✅ Prueba 1: Formato de Referencia de Precio**
- **Acción:** Cambiar el formato a "Ref. %s Bs." y guardar
- **Resultado Esperado:** 
  - Se guarda correctamente
  - Aparece en productos como "Ref. 1,500,000 Bs."
- **Verificación:** Ir a un producto y verificar que el formato se aplica

#### **✅ Prueba 2: Tasa IGTF**
- **Acción:** Cambiar la tasa a 5% y guardar
- **Resultado Esperado:**
  - Se guarda correctamente
  - Se aplica en checkout cuando IGTF está activo
- **Verificación:** Activar IGTF y verificar que usa la nueva tasa

#### **✅ Prueba 3: Mostrar IGTF (Checkbox)**
- **Acción:** Marcar/desmarcar checkbox y guardar
- **Resultado Esperado:**
  - Se guarda correctamente
  - Al recargar mantiene el estado
  - Controla si IGTF aparece en checkout
- **Verificación:** Recargar página y verificar estado del checkbox

#### **✅ Prueba 4: Habilitar IGTF (Checkbox)**
- **Acción:** Marcar/desmarcar checkbox y guardar
- **Resultado Esperado:**
  - Se guarda correctamente
  - Al recargar mantiene el estado
  - Controla si el sistema IGTF está activo
- **Verificación:** Recargar página y verificar estado del checkbox

---

## 🎨 **FASE 2: PERSONALIZACIÓN VISUAL**

### **2.1 Estilos de Precios**
**Ubicación:** Venezuela Pro → Apariencia

#### **✅ Prueba 5: Selección de Estilo**
- **Acción:** Cambiar entre diferentes estilos (Minimal, Modern, Elegant, etc.)
- **Resultado Esperado:**
  - Se guarda correctamente
  - Se aplica inmediatamente en preview
  - Se aplica en frontend
- **Verificación:** Ir a un producto y verificar que el estilo se aplica

#### **✅ Prueba 6: Personalización de Colores**
- **Acción:** Cambiar colores primario, secundario, éxito, advertencia
- **Resultado Esperado:**
  - Se guardan correctamente
  - Se aplican en preview en tiempo real
  - Se aplican en frontend
- **Verificación:** Verificar preview y frontend

#### **✅ Prueba 7: Personalización de Tipografía**
- **Acción:** Cambiar familia, tamaño, peso, transformación de fuente
- **Resultado Esperado:**
  - Se guardan correctamente
  - Se aplican en preview
  - Se aplican en frontend
- **Verificación:** Verificar preview y frontend

#### **✅ Prueba 8: Personalización de Espaciado**
- **Acción:** Cambiar padding, margen, radio de borde, sombra
- **Resultado Esperado:**
  - Se guardan correctamente
  - Se aplican en preview
  - Se aplican en frontend
- **Verificación:** Verificar preview y frontend

#### **✅ Prueba 9: Temas Predefinidos**
- **Acción:** Aplicar temas (Default, Green, Purple, Orange, Red, Dark)
- **Resultado Esperado:**
  - Se aplican correctamente
  - Cambian todos los colores automáticamente
  - Se guardan las configuraciones
- **Verificación:** Verificar que todos los colores cambian

---

## 💰 **FASE 3: SISTEMA IGTF**

### **3.1 Funcionalidad IGTF**
**Ubicación:** Frontend (Productos y Checkout)

#### **✅ Prueba 10: IGTF Desactivado**
- **Acción:** Desactivar ambos checkboxes de IGTF
- **Resultado Esperado:**
  - No aparece información de IGTF en productos
  - No aparece sección de IGTF en checkout
  - No se aplica comisión de IGTF
- **Verificación:** Ir a producto y checkout, verificar ausencia de IGTF

#### **✅ Prueba 11: IGTF Activado**
- **Acción:** Activar ambos checkboxes de IGTF
- **Resultado Esperado:**
  - Aparece información de IGTF en checkout
  - Se aplica comisión de IGTF (3% por defecto)
  - Se muestra mensaje explicativo
- **Verificación:** Ir a checkout, verificar presencia de IGTF

#### **✅ Prueba 12: Tasa IGTF Personalizada**
- **Acción:** Cambiar tasa a 5% y activar IGTF
- **Resultado Esperado:**
  - Se aplica la nueva tasa (5%)
  - Se calcula correctamente en checkout
- **Verificación:** Verificar cálculo en checkout

---

## 🔄 **FASE 4: CONVERSIÓN DE MONEDAS**

### **4.1 Integración BCV Dólar Tracker**
**Ubicación:** Frontend (Productos)

#### **✅ Prueba 13: Conversión USD a VES**
- **Acción:** Verificar que BCV Dólar Tracker esté activo
- **Resultado Esperado:**
  - Precios se convierten de USD a VES
  - Tasa de cambio actualizada
  - Selector de moneda funcional
- **Verificación:** Ir a producto, verificar conversión y selector

#### **✅ Prueba 14: Selector de Moneda**
- **Acción:** Cambiar entre USD y VES en el selector
- **Resultado Esperado:**
  - Precios cambian instantáneamente
  - Animación suave
  - Estado se mantiene en la sesión
- **Verificación:** Probar selector en múltiples productos

---

## 📋 **FASE 5: VALIDACIONES VENEZOLANAS**

### **5.1 Campos de Checkout**
**Ubicación:** Checkout

#### **✅ Prueba 15: Campo Cédula/RIF**
- **Acción:** Completar checkout con diferentes formatos de cédula/RIF
- **Resultado Esperado:**
  - Acepta V-12345678
  - Acepta J-12345678-9
  - Rechaza formatos incorrectos
  - Campo es obligatorio
- **Verificación:** Probar diferentes formatos

#### **✅ Prueba 16: Validación de Teléfono**
- **Acción:** Completar checkout con teléfono venezolano
- **Resultado Esperado:**
  - Acepta 0412-1234567
  - Acepta 0424-1234567
  - Rechaza formatos incorrectos
- **Verificación:** Probar diferentes formatos

---

## 🚚 **FASE 6: ZONAS DE ENVÍO**

### **6.1 Sistema de Envío**
**Ubicación:** Checkout

#### **✅ Prueba 17: Selección de Estado**
- **Acción:** Cambiar estado en checkout
- **Resultado Esperado:**
  - Lista de estados de Venezuela
  - Cambio de municipios según estado
  - Cálculo de envío correcto
- **Verificación:** Probar diferentes estados

#### **✅ Prueba 18: Selección de Municipio**
- **Acción:** Seleccionar municipio después de estado
- **Resultado Esperado:**
  - Lista de municipios del estado seleccionado
  - Cálculo de envío actualizado
- **Verificación:** Probar diferentes combinaciones

---

## 📱 **FASE 7: NOTIFICACIONES**

### **7.1 WhatsApp**
**Ubicación:** Venezuela Pro → Notificaciones

#### **✅ Prueba 19: Plantillas de WhatsApp**
- **Acción:** Configurar plantillas de pago y envío
- **Resultado Esperado:**
  - Se guardan correctamente
  - Se usan en notificaciones automáticas
- **Verificación:** Realizar pedido y verificar notificación

---

## 🔧 **FASE 8: SISTEMA FISCAL**

### **8.1 Numeración de Control**
**Ubicación:** Venezuela Pro → Sistema Fiscal

#### **✅ Prueba 20: Prefijo de Número de Control**
- **Acción:** Cambiar prefijo a "01-" y guardar
- **Resultado Esperado:**
  - Se guarda correctamente
  - Se aplica en facturación
- **Verificación:** Generar factura y verificar prefijo

#### **✅ Prueba 21: Próximo Número de Control**
- **Acción:** Cambiar próximo número a 100 y guardar
- **Resultado Esperado:**
  - Se guarda correctamente
  - Se usa en la siguiente factura
- **Verificación:** Generar factura y verificar numeración

---

## 📊 **FASE 9: MONITOREO Y REPORTES**

### **9.1 Sistema de Monitoreo**
**Ubicación:** Venezuela Pro → Monitoreo

#### **✅ Prueba 22: Dashboard de Monitoreo**
- **Acción:** Verificar dashboard de monitoreo
- **Resultado Esperado:**
  - Muestra estadísticas del plugin
  - Información de uso
  - Estado del sistema
- **Verificación:** Revisar métricas mostradas

#### **✅ Prueba 23: Reportes**
- **Acción:** Verificar sección de reportes
- **Resultado Esperado:**
  - Reportes de ventas
  - Reportes de IGTF
  - Exportación de datos
- **Verificación:** Generar y revisar reportes

#### **✅ Prueba 24: Monitor de Errores**
- **Acción:** Verificar monitor de errores
- **Resultado Esperado:**
  - Lista de errores del sistema
  - Información de debug
  - Logs detallados
- **Verificación:** Revisar logs de errores

---

## ✅ **CRITERIOS DE ÉXITO**

### **Funcionalidad Correcta:**
- ✅ Configuraciones se guardan correctamente
- ✅ Cambios se aplican inmediatamente
- ✅ No hay errores en consola
- ✅ Frontend funciona como se espera
- ✅ Validaciones funcionan correctamente

### **Funcionalidad Incorrecta:**
- ❌ Configuraciones no se guardan
- ❌ Cambios no se aplican
- ❌ Errores en consola
- ❌ Frontend no funciona
- ❌ Validaciones fallan

---

## 📝 **INSTRUCCIONES DE PRUEBA**

1. **Para cada prueba:**
   - Realizar la acción especificada
   - Verificar el resultado esperado
   - Documentar si funciona o no
   - Si no funciona, anotar el error específico

2. **Orden de ejecución:**
   - Seguir el orden de las fases
   - Completar todas las pruebas de una fase antes de pasar a la siguiente
   - No saltar pruebas

3. **Documentación:**
   - Anotar resultados en cada prueba
   - Tomar capturas de pantalla si es necesario
   - Reportar errores específicos

---

**¿Estás listo para comenzar con la Fase 1? Empezaremos con las Configuraciones Básicas.**
