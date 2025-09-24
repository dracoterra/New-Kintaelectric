# 🇻🇪 Project Brief: WooCommerce Venezuela Suite

## Documento Maestro de Desarrollo

Este documento sirve como la guía maestra para el desarrollo del plugin "WooCommerce Venezuela Suite". El objetivo es consolidar toda la funcionalidad necesaria para operar una tienda online en Venezuela dentro de un único plugin modular, bien documentado y siguiendo las mejores prácticas de desarrollo para WordPress y WooCommerce.

---

## 1. Visión General y Objetivos del Proyecto

### Información del Plugin
- **Nombre**: WooCommerce Venezuela Suite
- **Versión**: 1.0.0
- **Autor**: Ronald Alvarez
- **Licencia**: GPL-2.0+
- **Text Domain**: woocommerce-venezuela-pro-2025

### Misión
Ser la solución "todo en uno" definitiva para localizar una tienda WooCommerce a la normatividad y realidad comercial de Venezuela.

### Problema a Resolver
Los plugins existentes son inestables, carecen de soporte, tienen fallos de lógica y no integran todas las funcionalidades necesarias, obligando a los dueños de tiendas a instalar múltiples soluciones que a menudo entran en conflicto.

### Arquitectura Clave
El plugin será una base sólida con un sistema de módulos activables/desactivables desde el panel de administración de WordPress. Esto permite al usuario final habilitar solo las funciones que necesita, mejorando el rendimiento y la usabilidad.

---

## 2. Estructura de Módulos (Features)

Cada una de las siguientes funcionalidades será desarrollada como un módulo independiente.

### Módulo 1: Gestor de Multi-Moneda Inteligente 💵

**Objetivo**: Gestionar la dualidad Bolívar (VES) y Dólar Estadounidense (USD).

**Funcionalidades**:
- **Sincronización Automática de Tasa BCV**: Conectar vía API con el Banco Central de Venezuela para obtener la tasa de cambio oficial. Debe incluir un fallback manual en caso de que la API falle.
- **Programador de Tareas (Cron Job)**: Implementar un WP-Cron para actualizar la tasa automáticamente una o dos veces al día.
- **Visualización Dual**: Permitir que la tienda muestre los precios en ambas monedas (ej: Bs. 1.850,00 ($50.00)). El formato debe ser personalizable.
- **Selector de Moneda en el Checkout**: Permitir al cliente elegir en qué moneda desea finalizar el pago. El carrito debe recalcularse dinámicamente.

**Integración**: Compatible con el plugin BCV Dólar Tracker existente.

### Módulo 2: Pasarelas de Pago Locales 💳

**Objetivo**: Integrar los métodos de pago más comunes en Venezuela.

**Funcionalidades**:
- **Pago Móvil (C2P)**: Pasarela de pago que muestra los datos para realizar el pago móvil y un formulario para que el cliente reporte la referencia. Debe tener opción de validación manual o semi-automática.
- **Zelle**: Pasarela informativa que muestra el correo y los datos para recibir el pago. Incluye un campo para que el cliente ingrese el número de confirmación.
- **Transferencia Bancaria Nacional (VES)**: Permitir configurar múltiples cuentas bancarias venezolanas.
- **Depósito en Efectivo (USD)**: Pasarela para coordinar pagos en efectivo en cuentas custodia o contra entrega.

### Módulo 3: Impuestos y Cumplimiento Fiscal 🧾

**Objetivo**: Adaptar el sistema de impuestos de WooCommerce a las leyes venezolanas.

**Funcionalidades**:
- **Cálculo de IVA**: Implementar la tasa de IVA vigente (actualmente 16%).
- **Gestión de IGTF**: Crear una lógica que aplique el Impuesto a las Grandes Transacciones Financieras (actualmente 3%) únicamente cuando se seleccionan pasarelas de pago en divisas (Zelle, Depósito en USD, etc.).
- **Campos de Checkout Personalizados**: Agregar campos obligatorios en el formulario de pago para Cédula de Identidad y/o RIF, con validación de formato.

### Módulo 4: Integración de Envíos Nacionales 🚚

**Objetivo**: Calcular costos de envío con los principales couriers del país.

**Funcionalidades**:
- **Integración con MRW**: Crear un método de envío que permita configurar tarifas basadas en peso/destino.
- **Integración con Zoom**: Ídem anterior.
- **Integración con Tealca**: Ídem anterior.
- **Opción de "Delivery" Local**: Método de envío con tarifa plana o por zonas para despachos en la misma ciudad.

---

## 3. Mejores Prácticas y Estructura Técnica

### Estructura de Archivos Propuesta

```
/woocommerce-venezuela-pro-2025
|-- woocommerce-venezuela-pro-2025.php  (Archivo principal del plugin)
|-- /includes
|   |-- class-woocommerce-venezuela-pro-2025.php         (Clase principal, Singleton Pattern)
|   |-- class-woocommerce-venezuela-pro-2025-activator.php    (Código de activación)
|   |-- class-woocommerce-venezuela-pro-2025-deactivator.php  (Código de desactivación)
|   |-- class-woocommerce-venezuela-pro-2025-settings.php     (Manejo de la página de ajustes)
|   |-- class-woocommerce-venezuela-pro-2025-loader.php       (Cargador de hooks)
|   |-- class-woocommerce-venezuela-pro-2025-i18n.php         (Internacionalización)
|-- /modules
|   |-- /currency-manager
|   |   |-- class-wcvs-currency.php (Lógica principal del módulo)
|   |   |-- currency-hooks.php      (Todos los add_action y add_filter del módulo)
|   |-- /payment-gateways
|   |   |-- /gateways
|   |   |   |-- class-wcvs-pago-movil.php
|   |   |   |-- class-wcvs-zelle.php
|   |   |   |-- class-wcvs-transferencia-bancaria.php
|   |   |   |-- class-wcvs-deposito-efectivo.php
|   |   |-- payment-gateways-loader.php
|   |-- /taxes
|   |   |-- class-wcvs-taxes.php
|   |   |-- taxes-hooks.php
|   |-- /shipping
|       |-- /methods
|       |   |-- class-wcvs-shipping-mrw.php
|       |   |-- class-wcvs-shipping-zoom.php
|       |   |-- class-wcvs-shipping-tealca.php
|       |   |-- class-wcvs-shipping-local.php
|       |-- shipping-loader.php
|-- /admin
|   |-- /css
|   |-- /js
|   |-- /views
|       |-- settings-page.php (Plantilla de la página de ajustes)
|-- /public
|   |-- /css
|   |-- /js
|-- /languages
    |-- woocommerce-venezuela-pro-2025.pot (Archivo de traducción)
```

### Estándares de Codificación
- **WordPress Coding Standards**: El código se adherirá estrictamente a los WordPress Coding Standards.
- **Orientación a Objetos**: El plugin se construirá sobre clases para encapsular la lógica y evitar conflictos. Se usará un patrón Singleton para la clase principal.
- **Uso de Hooks**: Toda la interacción con WooCommerce y WordPress se realizará a través de actions y filters. Se evitará la modificación directa del core.
- **Seguridad**: Se utilizarán nonces para todas las acciones de administración, se sanitizarán todas las entradas (sanitize_*) y se escaparán todas las salidas (esc_*).
- **Documentación Interna**: Cada función, clase y método deberá tener un bloque de documentación PHPDoc que explique su propósito, parámetros y lo que retorna.

---

## 4. Reglas para el Desarrollo Asistido por IA (Cursor)

### Generación Atómica
Pide a la IA que genere una función o un método a la vez, no clases o archivos completos. Esto facilita la revisión.

### Explicación Obligatoria
Después de generar un bloque de código, exige a la IA que explique la lógica, qué hooks de WooCommerce utiliza y por qué los eligió.

### Especificidad en las Peticiones
En lugar de "crea la pasarela de Pago Móvil", usa un prompt detallado: "Genera una clase WCVS_Gateway_PagoMovil que extienda WC_Payment_Gateway. Incluye los campos de configuración title, description, beneficiary_name, beneficiary_id, phone_number, y bank_name. El método process_payment() debe cambiar el estado del pedido a 'on-hold' y retornar el redirect a la página 'thank you'."

### No Código Muerto
Revisa activamente el código generado en busca de variables no utilizadas, lógica redundante o funciones que no se llaman, y pide a la IA que lo refactorice para eliminarlo.

### Priorizar APIs Nativas
La IA debe priorizar siempre el uso de las APIs de WordPress y WooCommerce (como la Settings API para los ajustes, WC_Data para manipular objetos, etc.) en lugar de soluciones personalizadas.

### Refactorización Constante
Después de generar la lógica de un módulo, usa un prompt como: "Analiza el siguiente código, identifica posibles mejoras de rendimiento, seguridad o legibilidad y refactorízalo aplicando las mejores prácticas de WordPress."

---

## 5. Cronograma de Desarrollo

### Fase 1: Estructura Base (Semana 1-2)
- [ ] Configurar estructura de archivos
- [ ] Implementar clase principal con patrón Singleton
- [ ] Crear sistema de activación/desactivación
- [ ] Configurar página de ajustes básica
- [ ] Implementar sistema de módulos

### Fase 2: Módulo de Moneda (Semana 3-4)
- [ ] Integración con BCV Dólar Tracker
- [ ] Sistema de conversión automática
- [ ] Selector de moneda en checkout
- [ ] Visualización dual de precios
- [ ] Cron job para actualización automática

### Fase 3: Pasarelas de Pago (Semana 5-7)
- [ ] Pago Móvil (C2P)
- [ ] Zelle
- [ ] Transferencias Bancarias
- [ ] Depósito en Efectivo
- [ ] Sistema de validación de pagos

### Fase 4: Impuestos y Fiscal (Semana 8-9)
- [ ] Cálculo automático de IVA
- [ ] Gestión de IGTF
- [ ] Campos personalizados de checkout
- [ ] Validación de formatos venezolanos

### Fase 5: Envíos Nacionales (Semana 10-12)
- [ ] Integración MRW
- [ ] Integración Zoom
- [ ] Integración Tealca
- [ ] Delivery local
- [ ] Cálculo de tarifas por zona

### Fase 6: Testing y Optimización (Semana 13-14)
- [ ] Testing integral
- [ ] Optimización de performance
- [ ] Documentación final
- [ ] Preparación para lanzamiento

---

## 6. Consideraciones Específicas del Mercado Venezolano

### Moneda y Economía
- **Inflación**: Implementar actualización frecuente de precios
- **Reducción de Decimales**: Manejar redondeo apropiado para VES
- **Cache**: Implementar cache inteligente para conversiones
- **Fallback**: Sistema de respaldo cuando BCV no esté disponible

### Métodos de Pago
- **Pago Móvil**: Sistema más popular en Venezuela
- **Transferencias**: Bancos principales (Banesco, Mercantil, BBVA)
- **Efectivo**: Importante para transacciones locales
- **Criptomonedas**: Considerar para el futuro

### Envíos
- **Estados**: Configurar todos los estados de Venezuela
- **Ciudades Principales**: Caracas, Maracaibo, Valencia, Barquisimeto
- **Costos**: Diferentes tarifas según distancia y peso
- **Tiempos**: Estimaciones realistas de entrega

### Cumplimiento Legal
- **IVA**: 16% actualmente
- **IGTF**: 3% para transacciones en divisas
- **Datos Personales**: Cumplir con regulaciones locales
- **Facturación**: Sistema compatible con regulaciones venezolanas

---

## 7. Integración con Ecosistema Existente

### Plugin BCV Dólar Tracker
- **Sincronización**: Usar API del plugin existente
- **Fallback**: Sistema de respaldo si no está disponible
- **Cache**: Aprovechar cache del plugin BCV

### Tema Electro
- **Compatibilidad**: Asegurar funcionamiento óptimo
- **Personalizaciones**: Adaptar para productos eléctricos
- **Responsive**: Optimizar para dispositivos móviles

### Plugin Kinta Electronic Elementor
- **Widgets**: Integrar widgets especializados
- **Elementos**: Compatibilidad con elementos personalizados
- **Funcionalidades**: Aprovechar funcionalidades existentes

---

## 8. Métricas de Éxito

### Técnicas
- **Performance**: Tiempo de carga < 3 segundos
- **Compatibilidad**: Funcionamiento en 95% de temas populares
- **Estabilidad**: 0 errores críticos en producción
- **Seguridad**: Pasar auditorías de seguridad WordPress

### Negocio
- **Adopción**: 100+ tiendas usando el plugin
- **Satisfacción**: Rating > 4.5 estrellas
- **Soporte**: Tiempo de respuesta < 24 horas
- **Documentación**: 100% de funcionalidades documentadas

---

## 9. Recursos y Referencias

### Documentación WordPress
- [WordPress Plugin API](https://developer.wordpress.org/plugins/)
- [WooCommerce Developer Resources](https://woocommerce.com/developers/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

### Referencias Venezolanas
- [Banco Central de Venezuela](https://www.bcv.org.ve/)
- [SUDEBAN](https://www.sudeban.gob.ve/)
- [SENIAT](https://www.seniat.gob.ve/)

### Plugins de Referencia
- [WooCommerce](https://woocommerce.com/)
- [BCV Dólar Tracker](mdc:plugins/bcv-dolar-tracker/)
- [WooCommerce Venezuela Pro](mdc:plugins/woocommerce-venezuela-pro/)

---

*Este documento será actualizado conforme avance el desarrollo del proyecto.*
