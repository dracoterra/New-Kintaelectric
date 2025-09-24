=== WooCommerce Venezuela Suite ===
Contributors: ronaldalvarez
Donate link: https://artifexcodes.com/
Tags: woocommerce, venezuela, ecommerce, payment-gateways, shipping, currency, taxes, bcv, seniat, hpos
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Solución completa y funcional para localizar WooCommerce al mercado venezolano con módulos de moneda, pagos locales, impuestos y envíos.

== Description ==

**WooCommerce Venezuela Suite 2025** es la solución "todo en uno" definitiva para localizar una tienda WooCommerce a la normatividad y realidad comercial de Venezuela. **100% COMPLETO Y FUNCIONAL**.

## 🎯 Misión

Ser la solución integral que permita a cualquier tienda online operar eficientemente en Venezuela, integrando todas las funcionalidades necesarias en un único plugin modular, bien documentado y siguiendo las mejores prácticas de desarrollo para WordPress y WooCommerce.

## ✅ Estado del Proyecto

**COMPLETADO AL 100%** - Todas las funcionalidades implementadas, probadas y operativas:
- ✅ Sin errores fatales
- ✅ Compatible con HPOS (High-Performance Order Storage)
- ✅ Integración completa con BCV Dólar Tracker
- ✅ Todos los módulos funcionales
- ✅ Documentación completa

## 🚀 Características Principales

### 💵 Módulo de Multi-Moneda Inteligente
* **Sincronización automática** con la tasa BCV via plugin `bcv-dolar-tracker`
* **Visualización dual** de precios (VES/USD) con cache inteligente
* **Selector de moneda** en checkout con recálculo dinámico
* **Fuentes múltiples**: BCV, Dólar Today, EnParaleloVzla
* **Validación robusta** de tasas de cambio
* **JavaScript optimizado** con debouncing y estados de carga

### 💳 Pasarelas de Pago Locales
* **Pago Móvil (C2P)**: Integración completa con validación de RIF y referencias
* **Zelle**: Pasarela informativa con confirmación de pago
* **Binance Pay**: Para pagos en criptomonedas (USDT, BTC)
* **Transferencias Bancarias**: Múltiples cuentas venezolanas
* **Cash Deposit USD**: Para pagos en efectivo
* **Cashea**: Para financiamiento y crédito
* **Validación robusta**: RIF, teléfonos venezolanos, referencias de pago
* **JavaScript mejorado**: Validación en tiempo real con feedback visual

### 🧾 Sistema Fiscal Venezolano
* **IVA configurable**: Integrado con sistema nativo de WooCommerce (no hardcodeado)
* **IGTF dinámico**: Configurable y aplicable solo a pagos en USD (3%)
* **Actualización automática**: Tasas desde APIs oficiales (SENIAT, BCV)
* **Campos personalizados**: Cédula/RIF en checkout con validación
* **Cálculo robusto**: Con redondeo preciso y validación
* **Reportes fiscales**: Generación de reportes de impuestos

### 🚚 Métodos de Envío Nacionales
* **MRW**: Tarifas por peso, volumen, distancia con descuentos por volumen
* **Zoom**: Integración con API y cálculo de costos avanzado
* **Tealca**: Método configurable con estimaciones de entrega
* **Local Delivery**: Entrega local con zonas urbanas
* **Pickup**: Recogida en tienda
* **Cálculo avanzado**: Peso dimensional, seguros, empaque, descuentos
* **JavaScript mejorado**: Validación de direcciones, estimaciones de entrega

### 📄 Sistema de Facturación Electrónica
* **Generación automática** de facturas con datos completos del pedido
* **Códigos QR**: Generación automática para facturas
* **Firmas digitales**: Implementación de firmas digitales
* **PDF generation**: Generación de facturas en PDF
* **Integración SENIAT**: Envío a sistemas oficiales
* **Validación RIF**: Verificación de formato venezolano
* **Reportes**: Generación de reportes de facturación

## 🏗️ Arquitectura Modular

El plugin utiliza un sistema de módulos activables/desactivables desde el panel de administración, permitiendo:

* **Mejor rendimiento**: Solo cargar funcionalidades necesarias
* **Flexibilidad**: Adaptar la tienda a necesidades específicas
* **Mantenimiento**: Actualizaciones independientes por módulo
* **Escalabilidad**: Fácil adición de nuevas funcionalidades
* **Debugging**: Aislar problemas por módulo

## 🔧 Integración con Ecosistema

### ✅ BCV Dólar Tracker
* **Integración completa**: Plugin `bcv-dolar-tracker` completamente integrado
* **Sincronización automática**: Obtención de tasas en tiempo real
* **Fallback system**: Respaldo cuando no esté disponible
* **Cache sharing**: Aprovechamiento del cache del plugin BCV

### ✅ Compatibilidad HPOS
* **High-Performance Order Storage**: Completamente compatible
* **Declaración temprana**: Compatibilidad declarada en `before_woocommerce_init`
* **Funcionalidades compatibles**: Todas las funcionalidades del plugin

### ✅ Tema Electro
* **Optimización**: Funcionamiento óptimo garantizado
* **Personalizaciones**: Adaptado para productos eléctricos
* **Responsive**: Optimizado para dispositivos móviles

### ✅ Plugin Kinta Electronic Elementor
* **Widgets**: Integración con widgets especializados
* **Elementos**: Compatibilidad con elementos personalizados

## 🛠️ Panel de Administración

### ✅ Configuración Completa
* **Pestaña General**: Configuración básica del plugin
* **Pestaña Moneda**: Configuración de moneda y tasas
* **Pestaña Impuestos**: Configuración de IVA e IGTF
* **Pestaña Notificaciones**: Alertas y notificaciones
* **Pestaña Facturación**: Configuración de facturación electrónica
* **Estado BCV**: Visualización del estado del plugin BCV
* **Tasa actual**: Mostrar tasa de cambio actual

## 📚 Documentación Completa

### ✅ Documentación Técnica
* **Plan Integral**: Documentación completa del proyecto
* **Arquitectura Técnica**: Estructura y patrones de diseño
* **Hallazgos de Investigación**: Investigación del mercado venezolano
* **Reglas de Desarrollo**: Guías para desarrollo con Cursor

### ✅ Documentación de Correcciones
* **Correcciones Críticas**: Documentación de errores resueltos
* **Mejoras por Módulo**: Documentación de mejoras implementadas
* **Compatibilidad HPOS**: Documentación de compatibilidad
* **Corrección de Traducciones**: Documentación de problemas de traducción

## 🔒 Seguridad y Mejores Prácticas

* **Validación robusta**: Sanitización de todas las entradas
* **Nonces**: Verificación de seguridad en formularios
* **Permisos**: Validación de permisos de usuario
* **Escape**: Escapado de todas las salidas
* **Logging**: Sistema de logging para debugging
* **Testing**: Suite de pruebas incluida

== Installation ==

### Requisitos Previos

1. **WordPress**: Versión 5.0 o superior
2. **WooCommerce**: Versión 5.0 o superior
3. **PHP**: Versión 7.4 o superior
4. **Plugin BCV Dólar Tracker**: Recomendado para sincronización automática de tipos de cambio

### Pasos de Instalación

1. **Subir el Plugin**: 
   * Sube la carpeta `woocommerce-venezuela-pro-2025` al directorio `/wp-content/plugins/`
   * O instala directamente desde el repositorio de WordPress

2. **Activar el Plugin**:
   * Ve a `Plugins > Plugins Instalados` en tu panel de administración
   * Busca "WooCommerce Venezuela Suite" y haz clic en "Activar"

3. **Configuración Inicial**:
   * Ve a `WooCommerce > Configuración > Venezuela Suite`
   * Activa los módulos que necesites
   * Configura las pasarelas de pago locales
   * Establece las zonas de envío venezolanas

4. **Configurar Moneda**:
   * Ve a `WooCommerce > Configuración > General`
   * Establece Bolívares Venezolanos (VES) como moneda principal
   * Configura el formato de moneda venezolano

5. **Configurar Impuestos**:
   * Ve a `WooCommerce > Configuración > Impuestos`
   * Configura el IVA según tus productos
   * El plugin manejará automáticamente el IGTF

== Frequently Asked Questions ==

= ¿Es compatible con mi tema actual? =

Sí, el plugin está diseñado para ser compatible con cualquier tema de WordPress que siga las mejores prácticas. Sin embargo, para una experiencia óptima, recomendamos usar el tema Electro o temas compatibles con WooCommerce.

= ¿Cómo funciona la sincronización con BCV? =

El plugin se integra automáticamente con el plugin BCV Dólar Tracker para obtener la tasa de cambio oficial del Banco Central de Venezuela. Si este plugin no está disponible, puedes configurar una tasa manual o usar una API externa.

= ¿Puedo usar solo algunos módulos? =

¡Absolutamente! El plugin está diseñado con una arquitectura modular. Puedes activar solo los módulos que necesites desde el panel de administración, mejorando el rendimiento de tu sitio.

= ¿Qué métodos de pago están disponibles? =

Incluimos los métodos más populares en Venezuela: Pago Móvil (C2P), Zelle, Binance Pay, Transferencias Bancarias Nacionales, Cash Deposit USD y Cashea. Cada uno está optimizado para el mercado venezolano.

= ¿Cómo maneja los impuestos venezolanos? =

El plugin calcula automáticamente el IVA (configurable via WooCommerce) y el IGTF (3% configurable) según corresponda. El IGTF se aplica únicamente cuando se seleccionan métodos de pago en divisas extranjeras.

= ¿Es seguro para transacciones reales? =

Sí, el plugin sigue todas las mejores prácticas de seguridad de WordPress y WooCommerce, incluyendo sanitización de datos, validación de nonces y encriptación de información sensible.

= ¿Es compatible con HPOS? =

Sí, el plugin es completamente compatible con High-Performance Order Storage (HPOS) de WooCommerce, declarando su compatibilidad automáticamente.

= ¿Cómo funciona la facturación electrónica? =

El plugin genera automáticamente facturas electrónicas con códigos QR, firmas digitales y envío a SENIAT. Incluye validación de RIF y generación de PDF.

== Screenshots ==

1. Panel de administración del plugin con configuración de módulos
2. Configuración de pasarelas de pago locales
3. Configuración de métodos de envío nacionales
4. Sistema de facturación electrónica
5. Integración con BCV Dólar Tracker

== Changelog ==

= 1.0.0 (Completo y Funcional) =
* **Módulo de Multi-Moneda**: Sincronización automática con BCV, cache inteligente, validación robusta
* **Pasarelas de Pago**: Pago Móvil, Zelle, Binance Pay, Transferencias Bancarias, Cash Deposit, Cashea
* **Sistema de Impuestos**: IVA configurable, IGTF dinámico, actualización automática
* **Métodos de Envío**: MRW, Zoom, Tealca, Local Delivery, Pickup con cálculos avanzados
* **Facturación Electrónica**: Generación automática, códigos QR, firmas digitales, PDF
* **Arquitectura Modular**: Sistema de módulos activables/desactivables
* **Integración BCV**: Compatibilidad completa con BCV Dólar Tracker
* **Compatibilidad HPOS**: High-Performance Order Storage compatible
* **Seguridad**: Implementación de mejores prácticas WordPress
* **Documentación**: Documentación técnica completa
* **Correcciones**: Todos los errores críticos resueltos

= 0.9.0 (Beta) =
* Estructura base del plugin
* Sistema de activación/desactivación
* Configuración inicial de módulos
* Documentación técnica completa

== Upgrade Notice ==

= 1.0.0 =
Primera versión estable con todas las funcionalidades principales para el mercado venezolano. **100% COMPLETO Y FUNCIONAL**. Recomendado para tiendas en producción.

= 0.9.0 =
Versión beta para testing. No recomendada para sitios en producción.

== Support ==

Para soporte técnico, documentación completa y actualizaciones, visita:
* **Documentación**: `/docs/` dentro del plugin
* **Soporte**: https://artifexcodes.com/
* **GitHub**: [Repositorio del proyecto]

== Development ==

### Para Desarrolladores

El plugin está completamente documentado y sigue las mejores prácticas de WordPress:

* **Código limpio**: Siguiendo estándares de WordPress
* **Documentación PHPDoc**: Todas las funciones documentadas
* **Testing**: Suite de pruebas incluida
* **Logging**: Sistema de logging para debugging
* **Modular**: Arquitectura modular para fácil mantenimiento

### Estructura del Código

```
woocommerce-venezuela-pro-2025/
├── includes/                 # Clases principales
├── modules/                  # Módulos activables
├── admin/                    # Funcionalidad admin
├── public/                   # Funcionalidad pública
├── docs/                     # Documentación completa
└── tests/                    # Suite de pruebas
```

== License ==

Este plugin está licenciado bajo GPLv2 o posterior. Ver LICENSE para más detalles.