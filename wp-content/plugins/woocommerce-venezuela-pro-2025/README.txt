=== WooCommerce Venezuela Suite ===
Contributors: ronaldalvarez
Donate link: https://artifexcodes.com/
Tags: woocommerce, venezuela, ecommerce, payment-gateways, shipping, currency, taxes
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Solución completa para localizar WooCommerce al mercado venezolano con módulos de moneda, pagos locales, impuestos y envíos.

== Description ==

**WooCommerce Venezuela Suite** es la solución "todo en uno" definitiva para localizar una tienda WooCommerce a la normatividad y realidad comercial de Venezuela.

## 🎯 Misión

Ser la solución integral que permita a cualquier tienda online operar eficientemente en Venezuela, integrando todas las funcionalidades necesarias en un único plugin modular, bien documentado y siguiendo las mejores prácticas de desarrollo para WordPress y WooCommerce.

## 🚀 Características Principales

### 💵 Módulo de Multi-Moneda Inteligente
* Sincronización automática con la tasa BCV
* Visualización dual de precios (VES/USD)
* Selector de moneda en checkout
* Sistema de fallback para cuando BCV no esté disponible

### 💳 Pasarelas de Pago Locales
* **Pago Móvil (C2P)**: Integración completa con validación
* **Zelle**: Pasarela informativa con confirmación
* **Transferencias Bancarias**: Múltiples cuentas venezolanas
* **Depósito en Efectivo**: Coordinación de pagos USD

### 🧾 Impuestos y Cumplimiento Fiscal
* Cálculo automático de IVA (16%)
* Gestión de IGTF (3%) para pagos en divisas
* Campos personalizados para Cédula/RIF
* Validación de formatos venezolanos

### 🚚 Integración de Envíos Nacionales
* **MRW**: Tarifas basadas en peso/destino
* **Zoom**: Integración completa
* **Tealca**: Método de envío configurable
* **Delivery Local**: Tarifas por zonas urbanas

## 🏗️ Arquitectura Modular

El plugin utiliza un sistema de módulos activables/desactivables desde el panel de administración, permitiendo:

* **Mejor rendimiento**: Solo cargar funcionalidades necesarias
* **Flexibilidad**: Adaptar la tienda a necesidades específicas
* **Mantenimiento**: Actualizaciones independientes por módulo
* **Escalabilidad**: Fácil adición de nuevas funcionalidades

## 🔧 Integración con Ecosistema

* **BCV Dólar Tracker**: Sincronización automática de tipos de cambio
* **Kinta Electronic Elementor**: Widgets especializados
* **Tema Electro**: Optimización para productos eléctricos

A few notes about the sections above:

*   "Contributors" is a comma separated list of wp.org/wp-plugins.org usernames
*   "Tags" is a comma separated list of tags that apply to the plugin
*   "Requires at least" is the lowest version that the plugin will work on
*   "Tested up to" is the highest version that you've *successfully used to test the plugin*. Note that it might work on
higher versions... this is just the highest one you've verified.
*   Stable tag should indicate the Subversion "tag" of the latest stable version, or "trunk," if you use `/trunk/` for
stable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

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

== Frequently Asked Questions ==

= ¿Es compatible con mi tema actual? =

Sí, el plugin está diseñado para ser compatible con cualquier tema de WordPress que siga las mejores prácticas. Sin embargo, para una experiencia óptima, recomendamos usar el tema Electro o temas compatibles con WooCommerce.

= ¿Cómo funciona la sincronización con BCV? =

El plugin se integra automáticamente con el plugin BCV Dólar Tracker para obtener la tasa de cambio oficial del Banco Central de Venezuela. Si este plugin no está disponible, puedes configurar una tasa manual o usar una API externa.

= ¿Puedo usar solo algunos módulos? =

¡Absolutamente! El plugin está diseñado con una arquitectura modular. Puedes activar solo los módulos que necesites desde el panel de administración, mejorando el rendimiento de tu sitio.

= ¿Qué métodos de pago están disponibles? =

Incluimos los métodos más populares en Venezuela: Pago Móvil (C2P), Zelle, Transferencias Bancarias Nacionales y Depósito en Efectivo USD. Cada uno está optimizado para el mercado venezolano.

= ¿Cómo maneja los impuestos venezolanos? =

El plugin calcula automáticamente el IVA (16%) y el IGTF (3%) según corresponda. El IGTF se aplica únicamente cuando se seleccionan métodos de pago en divisas extranjeras.

= ¿Es seguro para transacciones reales? =

Sí, el plugin sigue todas las mejores prácticas de seguridad de WordPress y WooCommerce, incluyendo sanitización de datos, validación de nonces y encriptación de información sensible.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0.0 (En Desarrollo) =
* **Módulo de Multi-Moneda**: Sincronización automática con BCV
* **Pasarelas de Pago**: Pago Móvil, Zelle, Transferencias Bancarias
* **Sistema de Impuestos**: IVA e IGTF automáticos
* **Envíos Nacionales**: MRW, Zoom, Tealca integrados
* **Arquitectura Modular**: Sistema de módulos activables
* **Integración BCV**: Compatibilidad con BCV Dólar Tracker
* **Seguridad**: Implementación de mejores prácticas WordPress

= 0.9.0 (Beta) =
* Estructura base del plugin
* Sistema de activación/desactivación
* Configuración inicial de módulos
* Documentación técnica completa

== Upgrade Notice ==

= 1.0.0 =
Primera versión estable con todas las funcionalidades principales para el mercado venezolano. Recomendado para tiendas en producción.

= 0.9.0 =
Versión beta para testing. No recomendada para sitios en producción.

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`