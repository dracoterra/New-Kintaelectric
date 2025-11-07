# Plan de Mejora del Sistema de IVA e IGTF

## Análisis de la Situación Actual

### Problemas Identificados
1. **IVA**: Configuración básica funcional, pero falta validación y excepciones
2. **IGTF**: Sistema básico, pero falta claridad sobre cuándo aplica según normativa venezolana
3. **Reportes**: Funcionales pero pueden mejorarse con más detalles y validaciones
4. **Configuración**: Interfaz básica, falta guía y validaciones
5. **Validaciones**: Faltan validaciones de cumplimiento normativo

### Normativa Venezolana (Basada en Investigación)

#### IVA (Impuesto al Valor Agregado)
- **Tasa General**: 16%
- **Aplicación**: Todos los productos y servicios gravables
- **Excepciones**: Productos de primera necesidad, medicinas, libros, etc.
- **Base de Cálculo**: Precio de venta sin incluir impuestos
- **Responsabilidad**: Vendedor debe recaudar y declarar

#### IGTF (Impuesto a las Grandes Transacciones Financieras)
- **Tasa**: 3%
- **Aplicación**: Solo a ciertos métodos de pago en efectivo
- **Métodos que aplican**: Pagos en efectivo con billetes en dólares (según normativa)
- **Métodos que NO aplican**: Transferencias digitales, pagos móviles, tarjetas
- **Base de Cálculo**: Monto de la transacción antes de impuestos
- **Responsabilidad**: Vendedor debe recaudar y declarar

---

## Plan de Mejora - Fase 1: Fundamentos (Prioridad Alta)

### 1.1 Mejora del Sistema de Configuración

#### Objetivo
Crear una interfaz de configuración clara y completa para IVA e IGTF.

#### Tareas
- [ ] **Panel de Configuración Unificado**
  - Crear página de configuración dedicada en el admin
  - Separar claramente configuración de IVA e IGTF
  - Agregar descripciones y ejemplos para cada opción
  - Incluir validaciones en tiempo real

- [ ] **Configuración de IVA**
  - Tasa de IVA (por defecto 16%)
  - Habilitar/deshabilitar IVA
  - Configurar clases de impuestos exentas
  - Configurar productos/categorías exentas
  - Opción para aplicar IVA a envíos

- [ ] **Configuración de IGTF**
  - Tasa de IGTF (por defecto 3%)
  - Habilitar/deshabilitar IGTF
  - Selección de métodos de pago que aplican IGTF
  - Monto mínimo/máximo para aplicar IGTF
  - Excepciones por producto/categoría/cliente

- [ ] **Validaciones de Configuración**
  - Verificar que las tasas sean válidas (0-100%)
  - Validar que al menos un método de pago esté seleccionado si IGTF está habilitado
  - Advertencias si la configuración no es óptima

#### Archivos a Modificar/Crear
- `admin/class-wvp-tax-settings.php` (NUEVO)
- `admin/class-wvp-admin-restructured.php` (MODIFICAR)

---

### 1.2 Mejora del Cálculo de IVA

#### Objetivo
Asegurar que el IVA se calcule correctamente según normativa venezolana.

#### Tareas
- [ ] **Sistema de Clases de Impuestos**
  - Crear clase de impuesto "IVA Venezuela" automáticamente
  - Permitir múltiples tasas de IVA (general, reducida, exenta)
  - Configurar productos con diferentes clases de IVA

- [ ] **Validación de Productos Gravábles**
  - Verificar que productos tengan clase de impuesto correcta
  - Validar excepciones por categoría
  - Validar excepciones por producto individual

- [ ] **Cálculo Preciso**
  - Asegurar redondeo correcto (2 decimales)
  - Calcular IVA sobre subtotal + envío (si aplica)
  - Separar IVA de productos e IVA de envío

- [ ] **Aplicación a Envíos**
  - Opción para aplicar IVA a costos de envío
  - Configuración por método de envío

#### Archivos a Modificar
- `includes/class-wvp-tax-manager.php` (MEJORAR)
- `includes/class-wvp-iva-calculator.php` (NUEVO)

---

### 1.3 Mejora del Cálculo de IGTF

#### Objetivo
Asegurar que IGTF se aplique solo cuando corresponde según normativa.

#### Tareas
- [ ] **Validación de Métodos de Pago**
  - Lista clara de métodos que aplican IGTF
  - Validación en tiempo real en checkout
  - Mensajes informativos al cliente

- [ ] **Cálculo Correcto**
  - IGTF sobre subtotal + envío (antes de IVA)
  - No aplicar IGTF sobre IGTF (no compuesto)
  - Redondeo correcto a 2 decimales

- [ ] **Excepciones y Límites**
  - Monto mínimo para aplicar IGTF
  - Monto máximo para aplicar IGTF
  - Excepciones por tipo de cliente (personas jurídicas, etc.)
  - Excepciones por producto/categoría

- [ ] **Validación de Aplicación**
  - Verificar que IGTF solo se aplique a métodos de pago configurados
  - No crear datos falsos de IGTF
  - Log de cuando se aplica IGTF y por qué

#### Archivos a Modificar
- `includes/class-wvp-tax-manager.php` (MEJORAR)
- `includes/class-wvp-igtf-validator.php` (NUEVO)

---

## Plan de Mejora - Fase 2: Validaciones y Controles (Prioridad Media)

### 2.1 Sistema de Validación de Cumplimiento

#### Objetivo
Asegurar que todas las transacciones cumplan con normativa venezolana.

#### Tareas
- [ ] **Validación Pre-Orden**
  - Verificar que IVA esté calculado correctamente
  - Verificar que IGTF solo se aplique cuando corresponde
  - Validar que los totales sean correctos
  - Bloquear orden si hay inconsistencias

- [ ] **Validación Post-Orden**
  - Verificar que datos guardados sean correctos
  - Validar que meta de impuestos esté completa
  - Generar alertas si hay discrepancias

- [ ] **Auditoría de Impuestos**
  - Log de todos los cálculos de impuestos
  - Registro de cambios en configuración
  - Historial de tasas aplicadas

#### Archivos a Crear
- `includes/class-wvp-tax-validator.php` (NUEVO)
- `includes/class-wvp-tax-auditor.php` (NUEVO)

---

### 2.2 Mejora de Guardado de Datos

#### Objetivo
Asegurar que todos los datos fiscales se guarden correctamente.

#### Tareas
- [ ] **Meta Completo de Impuestos**
  - Guardar tasa de IVA aplicada
  - Guardar tasa de IGTF aplicada
  - Guardar base imponible de IVA
  - Guardar base imponible de IGTF
  - Guardar método de pago usado
  - Guardar fecha/hora de cálculo

- [ ] **Validación de Datos Guardados**
  - Verificar que todos los campos estén presentes
  - Validar que los totales coincidan
  - Regenerar datos si faltan (solo para órdenes nuevas)

- [ ] **Migración de Datos Antiguos**
  - Script para calcular y guardar datos de órdenes antiguas
  - Validación de datos existentes
  - Limpieza de datos incorrectos

#### Archivos a Modificar/Crear
- `includes/class-wvp-tax-manager.php` (MEJORAR)
- `includes/class-wvp-tax-data-migrator.php` (NUEVO)

---

## Plan de Mejora - Fase 3: Reportes y Facturación (Prioridad Media)

### 3.1 Mejora de Reportes SENIAT

#### Objetivo
Hacer los reportes más completos y precisos.

#### Tareas
- [ ] **Reporte Detallado de Productos**
  - Mostrar todos los productos con IVA individual
  - Agrupar productos por categoría
  - Mostrar base imponible por producto
  - Mostrar IVA por producto

- [ ] **Validación de Reportes**
  - Verificar que totales coincidan con órdenes
  - Alertar sobre discrepancias
  - Validar que no haya datos faltantes

- [ ] **Exportación Mejorada**
  - Formato Excel con fórmulas
  - Formato CSV con validación
  - Formato PDF para impresión
  - Formato XML para sistemas contables

- [ ] **Filtros Avanzados**
  - Filtrar por método de pago
  - Filtrar por tipo de cliente
  - Filtrar por productos/categorías
  - Filtrar por rango de montos

#### Archivos a Modificar
- `includes/class-wvp-seniat-reports.php` (MEJORAR)
- `includes/class-wvp-report-exporter.php` (NUEVO)

---

### 3.2 Integración con Facturación Electrónica

#### Objetivo
Asegurar que las facturas cumplan con normativa SENIAT.

#### Tareas
- [ ] **Factura con Desglose Completo**
  - Mostrar subtotal sin impuestos
  - Mostrar IVA por separado
  - Mostrar IGTF por separado (si aplica)
  - Mostrar total general

- [ ] **Número de Control**
  - Generar número de control único
  - Validar formato según SENIAT
  - Guardar en orden y factura

- [ ] **Datos Fiscales**
  - RIF de la empresa
  - Datos del cliente (cédula/RIF)
  - Fecha y hora de emisión
  - Tasa de cambio aplicada

#### Archivos a Modificar
- `includes/class-wvp-electronic-invoice.php` (MEJORAR)
- `includes/class-wvp-invoice-generator.php` (MEJORAR)

---

## Plan de Mejora - Fase 4: Interfaz y Experiencia (Prioridad Baja)

### 4.1 Mejora de Interfaz de Usuario

#### Objetivo
Hacer la configuración y uso más intuitivo.

#### Tareas
- [ ] **Panel de Configuración Visual**
  - Interfaz con pestañas claras
  - Guías paso a paso
  - Ejemplos visuales
  - Validación en tiempo real

- [ ] **Dashboard de Impuestos**
  - Resumen de IVA recaudado
  - Resumen de IGTF recaudado
  - Gráficos de tendencias
  - Alertas y notificaciones

- [ ] **Información en Checkout**
  - Mostrar desglose claro de impuestos
  - Explicar cuándo se aplica IGTF
  - Mostrar totales antes y después de impuestos

#### Archivos a Crear
- `admin/class-wvp-tax-dashboard.php` (NUEVO)
- `admin/views/tax-settings.php` (NUEVO)

---

### 4.2 Documentación y Ayuda

#### Objetivo
Facilitar el uso y configuración del sistema.

#### Tareas
- [ ] **Documentación Completa**
  - Guía de configuración paso a paso
  - Explicación de normativa venezolana
  - Preguntas frecuentes
  - Ejemplos de configuración

- [ ] **Ayuda Contextual**
  - Tooltips en configuración
  - Enlaces a documentación
  - Videos tutoriales
  - Soporte integrado

#### Archivos a Crear
- `DOCUMENTACION/GUIA-CONFIGURACION-IVA-IGTF.md` (NUEVO)
- `DOCUMENTACION/NORMATIVA-VENEZOLANA.md` (NUEVO)

---

## Plan de Mejora - Fase 5: Optimización y Mantenimiento (Prioridad Baja)

### 5.1 Optimización de Rendimiento

#### Objetivo
Asegurar que el sistema sea rápido y eficiente.

#### Tareas
- [ ] **Caché de Cálculos**
  - Cachear tasas de impuestos
  - Cachear configuraciones
  - Invalidar cache cuando sea necesario

- [ ] **Optimización de Consultas**
  - Optimizar consultas de reportes
  - Indexar tablas necesarias
  - Reducir carga de base de datos

#### Archivos a Modificar
- `includes/class-wvp-tax-manager.php` (MEJORAR)
- `includes/class-wvp-seniat-reports.php` (MEJORAR)

---

### 5.2 Pruebas y Validación

#### Objetivo
Asegurar que todo funcione correctamente.

#### Tareas
- [ ] **Suite de Pruebas**
  - Pruebas unitarias de cálculos
  - Pruebas de integración
  - Pruebas de regresión
  - Pruebas de carga

- [ ] **Validación de Cumplimiento**
  - Verificar cálculos con ejemplos reales
  - Validar reportes con contadores
  - Verificar facturas con SENIAT

#### Archivos a Crear
- `tests/test-tax-calculations.php` (NUEVO)
- `tests/test-igtf-application.php` (NUEVO)

---

## Priorización y Cronograma Sugerido

### Semana 1-2: Fase 1.1 y 1.2
- Configuración mejorada
- Cálculo de IVA mejorado

### Semana 3-4: Fase 1.3 y 2.1
- Cálculo de IGTF mejorado
- Validaciones básicas

### Semana 5-6: Fase 2.2 y 3.1
- Guardado de datos mejorado
- Reportes mejorados

### Semana 7-8: Fase 3.2 y 4.1
- Facturación mejorada
- Interfaz mejorada

### Semana 9-10: Fase 4.2 y 5.1
- Documentación
- Optimización

### Semana 11-12: Fase 5.2
- Pruebas y validación final

---

## Métricas de Éxito

### Funcionalidad
- ✅ IVA se calcula correctamente en 100% de las órdenes
- ✅ IGTF solo se aplica cuando corresponde (0% de falsos positivos)
- ✅ Reportes son 100% precisos
- ✅ Todas las órdenes tienen datos fiscales completos

### Usabilidad
- ✅ Configuración se completa en menos de 10 minutos
- ✅ Usuarios pueden generar reportes sin ayuda
- ✅ Documentación cubre 100% de casos de uso

### Rendimiento
- ✅ Cálculo de impuestos en menos de 100ms
- ✅ Generación de reportes en menos de 5 segundos
- ✅ Sin impacto negativo en velocidad del sitio

---

## Notas Importantes

1. **Cumplimiento Legal**: Todas las mejoras deben asegurar cumplimiento con normativa venezolana
2. **Retrocompatibilidad**: Las mejoras no deben romper órdenes existentes
3. **Documentación**: Cada mejora debe estar documentada
4. **Pruebas**: Cada mejora debe ser probada antes de implementar
5. **Backup**: Siempre hacer backup antes de cambios importantes

---

## Recursos Adicionales

- Documentación oficial de WooCommerce sobre impuestos
- Normativa SENIAT sobre IVA e IGTF
- Mejores prácticas de facturación electrónica en Venezuela
- Consultas con contadores venezolanos para validación

---

**Última actualización**: 2025-01-07
**Versión del Plan**: 1.0
**Estado**: Propuesta inicial

