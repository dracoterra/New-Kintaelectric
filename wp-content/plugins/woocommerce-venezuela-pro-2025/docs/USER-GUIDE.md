# Guía Completa de Usuario - WooCommerce Venezuela Suite 2025

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Instalación y Activación](#instalación-y-activación)
3. [Configuración Inicial](#configuración-inicial)
4. [Módulos del Plugin](#módulos-del-plugin)
5. [Configuración de WooCommerce](#configuración-de-woocommerce)
6. [Solución de Problemas](#solución-de-problemas)
7. [Preguntas Frecuentes](#preguntas-frecuentes)

## Introducción

**WooCommerce Venezuela Suite 2025** es un plugin completamente funcional que localiza tu tienda WooCommerce para el mercado venezolano. El plugin está **100% completo y operativo** con todas las funcionalidades implementadas.

### 🎯 ¿Qué hace este plugin?

- **Convierte tu tienda** en una solución completa para Venezuela
- **Integra métodos de pago** locales (Pago Móvil, Zelle, Binance Pay, etc.)
- **Maneja la dualidad monetaria** VES/USD con tasas automáticas del BCV
- **Calcula impuestos** venezolanos (IVA e IGTF)
- **Integra envíos** con empresas nacionales (MRW, Zoom, Tealca)
- **Genera facturas** electrónicas compatibles con SENIAT

## Instalación y Activación

### Requisitos Previos

- **WordPress**: 5.0 o superior
- **WooCommerce**: 5.0 o superior
- **PHP**: 7.4 o superior
- **Plugin BCV Dólar Tracker**: Recomendado (opcional)

### Pasos de Instalación

1. **Subir el Plugin**
   ```
   wp-content/plugins/woocommerce-venezuela-pro-2025/
   ```

2. **Activar el Plugin**
   - Ve a `Plugins > Plugins Instalados`
   - Busca "WooCommerce Venezuela Suite"
   - Haz clic en "Activar"

3. **Verificar Activación**
   - Deberías ver un mensaje de éxito
   - El plugin aparecerá en el menú de WooCommerce

## Configuración Inicial

### 1. Acceder al Panel de Configuración

Ve a `WooCommerce > Configuración > Venezuela Suite`

### 2. Configuración General

#### Pestaña "General"
- **Activar módulos**: Selecciona qué módulos necesitas
- **Modo debug**: Activa solo para desarrollo
- **Logging**: Configura el nivel de logs

#### Pestaña "Moneda"
- **Moneda base**: VES (Bolívares Venezolanos)
- **Precios duales**: Mostrar USD y VES
- **Posición del precio**: Antes o después del producto
- **Decimales**: Número de decimales para VES
- **Separadores**: Configuración de formato venezolano

#### Pestaña "Impuestos"
- **IVA**: Configura la tasa de IVA (por defecto 16%)
- **IGTF**: Configura la tasa de IGTF (por defecto 3%)
- **Aplicar IGTF a USD**: Solo para pagos en divisas
- **Actualización automática**: Desde APIs oficiales

#### Pestaña "Notificaciones"
- **Cambios de tasa**: Notificar cambios significativos
- **Errores de API**: Alertas de fallos de conexión
- **Email de administrador**: Para recibir notificaciones

#### Pestaña "Facturación"
- **RIF de la empresa**: Formato J-12345678-9
- **Nombre de la empresa**: Para facturas
- **Generar automáticamente**: Al completar pedidos
- **Enviar a SENIAT**: Integración oficial

## Módulos del Plugin

### 💵 Módulo de Moneda

#### ¿Qué hace?
- Convierte precios USD a VES automáticamente
- Muestra precios en ambas monedas
- Permite al cliente elegir moneda de pago

#### ¿Cómo configurar?
1. **Activar el módulo** en la pestaña "General"
2. **Configurar moneda** en WooCommerce > Configuración > General
3. **Establecer VES** como moneda principal
4. **Configurar formato** venezolano (Bs. 1.850,00)

#### ¿Dónde encontrar?
- **Configuración**: WooCommerce > Configuración > General
- **Productos**: Los precios se muestran automáticamente
- **Checkout**: Selector de moneda disponible

### 💳 Módulo de Pasarelas de Pago

#### ¿Qué hace?
- Agrega métodos de pago locales
- Valida datos venezolanos (RIF, teléfonos)
- Procesa pagos según método seleccionado

#### Métodos Disponibles

##### Pago Móvil (C2P)
- **Configuración**: Beneficiario, teléfono, banco
- **Validación**: RIF y referencia de pago
- **Proceso**: Cliente realiza pago móvil y reporta referencia

##### Zelle
- **Configuración**: Email de Zelle
- **Proceso**: Cliente realiza pago y reporta confirmación
- **Validación**: Número de confirmación

##### Binance Pay
- **Configuración**: API de Binance
- **Proceso**: Pago en criptomonedas (USDT, BTC)
- **Validación**: ID de transacción

##### Transferencias Bancarias
- **Configuración**: Múltiples cuentas bancarias
- **Proceso**: Cliente realiza transferencia
- **Validación**: Referencia de transferencia

##### Cash Deposit USD
- **Configuración**: Cuentas en USD
- **Proceso**: Depósito en efectivo
- **Validación**: Comprobante de depósito

##### Cashea
- **Configuración**: API de Cashea
- **Proceso**: Financiamiento y crédito
- **Validación**: Aprobación de crédito

#### ¿Cómo configurar?
1. **Activar el módulo** en la pestaña "General"
2. **Configurar métodos** en WooCommerce > Configuración > Pagos
3. **Activar pasarelas** que necesites
4. **Configurar datos** específicos de cada método

#### ¿Dónde encontrar?
- **Configuración**: WooCommerce > Configuración > Pagos
- **Checkout**: Métodos disponibles según configuración

### 🧾 Módulo de Sistema Fiscal

#### ¿Qué hace?
- Calcula IVA automáticamente
- Aplica IGTF solo a pagos en USD
- Actualiza tasas desde APIs oficiales
- Genera reportes fiscales

#### ¿Cómo configurar?
1. **Activar el módulo** en la pestaña "General"
2. **Configurar IVA** en WooCommerce > Configuración > Impuestos
3. **Configurar IGTF** en la pestaña "Impuestos"
4. **Activar actualización automática** si deseas

#### ¿Dónde encontrar?
- **Configuración**: WooCommerce > Configuración > Impuestos
- **Checkout**: Impuestos calculados automáticamente
- **Reportes**: WooCommerce > Reportes > Impuestos

### 🚚 Módulo de Métodos de Envío

#### ¿Qué hace?
- Agrega métodos de envío nacionales
- Calcula costos por peso, volumen y distancia
- Proporciona estimaciones de entrega
- Valida direcciones venezolanas

#### Métodos Disponibles

##### MRW
- **Configuración**: Costo base, tarifas por peso
- **Cálculo**: Peso, volumen, distancia, seguros
- **Descuentos**: Por volumen y peso
- **Estimación**: 2-5 días hábiles

##### Zoom
- **Configuración**: API de Zoom
- **Cálculo**: Costos dinámicos
- **Estimación**: 1-3 días hábiles

##### Tealca
- **Configuración**: Tarifas por zona
- **Cálculo**: Peso y destino
- **Estimación**: 3-7 días hábiles

##### Local Delivery
- **Configuración**: Zonas urbanas
- **Cálculo**: Distancia y peso
- **Estimación**: 1-2 días

##### Pickup
- **Configuración**: Ubicación de tienda
- **Cálculo**: Sin costo de envío
- **Estimación**: Inmediato

#### ¿Cómo configurar?
1. **Activar el módulo** en la pestaña "General"
2. **Configurar métodos** en WooCommerce > Configuración > Envíos
3. **Crear zonas** para estados venezolanos
4. **Configurar tarifas** para cada método

#### ¿Dónde encontrar?
- **Configuración**: WooCommerce > Configuración > Envíos
- **Checkout**: Métodos disponibles según zona
- **Productos**: Costos calculados automáticamente

### 📄 Módulo de Facturación Electrónica

#### ¿Qué hace?
- Genera facturas electrónicas automáticamente
- Crea códigos QR para facturas
- Genera firmas digitales
- Envía a SENIAT
- Crea PDFs de facturas

#### ¿Cómo configurar?
1. **Activar el módulo** en la pestaña "General"
2. **Configurar datos** en la pestaña "Facturación"
3. **Establecer RIF** de la empresa
4. **Activar generación automática**

#### ¿Dónde encontrar?
- **Configuración**: WooCommerce > Configuración > Venezuela Suite > Facturación
- **Pedidos**: Facturas generadas automáticamente
- **Emails**: Facturas adjuntas a emails

## Configuración de WooCommerce

### Configuración de Moneda

1. **Ve a**: WooCommerce > Configuración > General
2. **Moneda**: Selecciona "Bolívares Venezolanos (VES)"
3. **Formato**: Configura formato venezolano
4. **Separador de miles**: Punto (.)
5. **Separador decimal**: Coma (,)

### Configuración de Impuestos

1. **Ve a**: WooCommerce > Configuración > Impuestos
2. **Activar impuestos**: Marca la casilla
3. **Configurar IVA**: Establece la tasa (16%)
4. **Configurar IGTF**: El plugin lo maneja automáticamente

### Configuración de Zonas de Envío

1. **Ve a**: WooCommerce > Configuración > Envíos
2. **Crear zonas**:
   - **Zona 1**: Caracas (Capital)
   - **Zona 2**: Zulia, Lara, Carabobo
   - **Zona 3**: Resto del país
3. **Agregar métodos** de envío a cada zona

### Configuración de Campos de Checkout

1. **Ve a**: WooCommerce > Configuración > Avanzado
2. **Campos de facturación**: Agregar RIF
3. **Campos de envío**: Agregar campos adicionales
4. **Validación**: El plugin valida automáticamente

## Solución de Problemas

### Problemas Comunes

#### El plugin no se activa
- **Verifica**: WooCommerce está activo
- **Verifica**: PHP 7.4 o superior
- **Verifica**: Permisos de archivos

#### Los precios no se convierten
- **Verifica**: Módulo de moneda activado
- **Verifica**: BCV Dólar Tracker instalado
- **Verifica**: Tasa de cambio configurada

#### Los métodos de pago no aparecen
- **Verifica**: Módulo de pagos activado
- **Verifica**: Métodos activados en WooCommerce
- **Verifica**: Configuración de cada método

#### Los envíos no se calculan
- **Verifica**: Módulo de envíos activado
- **Verifica**: Zonas de envío configuradas
- **Verifica**: Métodos de envío activados

#### Las facturas no se generan
- **Verifica**: Módulo de facturación activado
- **Verifica**: RIF de empresa configurado
- **Verifica**: Permisos de escritura

### Logs y Debugging

#### Activar Debugging
1. **Ve a**: WooCommerce > Configuración > Venezuela Suite
2. **Pestaña General**: Activa "Modo Debug"
3. **Revisa logs**: En `/wp-content/debug.log`

#### Verificar Estado BCV
1. **Ve a**: WooCommerce > Configuración > Venezuela Suite
2. **Pestaña Moneda**: Verifica estado BCV
3. **Tasa actual**: Debe mostrar tasa válida

## Preguntas Frecuentes

### ¿Es compatible con mi tema?
Sí, el plugin es compatible con cualquier tema de WordPress que siga las mejores prácticas.

### ¿Puedo usar solo algunos módulos?
Sí, puedes activar solo los módulos que necesites desde el panel de administración.

### ¿Cómo funciona la sincronización con BCV?
El plugin se integra automáticamente con BCV Dólar Tracker para obtener tasas oficiales.

### ¿Es seguro para transacciones reales?
Sí, el plugin sigue todas las mejores prácticas de seguridad de WordPress y WooCommerce.

### ¿Es compatible con HPOS?
Sí, el plugin es completamente compatible con High-Performance Order Storage.

### ¿Cómo maneja los impuestos?
El plugin calcula automáticamente IVA e IGTF según corresponda.

### ¿Qué métodos de pago están disponibles?
Pago Móvil, Zelle, Binance Pay, Transferencias Bancarias, Cash Deposit USD y Cashea.

### ¿Cómo funciona la facturación electrónica?
El plugin genera automáticamente facturas con códigos QR, firmas digitales y envío a SENIAT.

## Soporte y Recursos

### Documentación
- **Documentación técnica**: `/docs/` dentro del plugin
- **Guías de configuración**: En cada módulo
- **Ejemplos de código**: Para desarrolladores

### Soporte Técnico
- **Email**: soporte@artifexcodes.com
- **Website**: https://artifexcodes.com/
- **Documentación**: Completa en `/docs/`

### Actualizaciones
- **Versión actual**: 1.0.0
- **Estado**: 100% completo y funcional
- **Actualizaciones**: Automáticas desde WordPress

---

**¡El plugin está 100% completo y listo para usar en producción!** 🚀
