# WooCommerce Venezuela Pro

Kit de localización completo para WooCommerce en Venezuela. Se integra con el plugin "BCV Dólar" para proporcionar la tasa de cambio del Banco Central de Venezuela y aplicar toda la lógica necesaria para adaptar la tienda online al mercado venezolano.

## Características Principales

### 💰 **Precios en Bolívares**
- Muestra precios en bolívares como referencia debajo de los precios en USD
- Formato personalizable para la referencia en bolívares
- Integración automática con la tasa de cambio del BCV

### 🛒 **Checkout Venezolano**
- Campo obligatorio de Cédula o RIF en el checkout
- Validación de formato venezolano (V-12345678, J-12345678-9)
- Cálculo automático del IGTF (3%) sobre el total del pedido

### 💳 **Pasarelas de Pago Venezolanas**
- **Zelle**: Con campo de número de confirmación
- **Pago Móvil**: Con datos bancarios y monto en bolívares
- Configuración individual de IGTF por pasarela

### 📧 **Correos Modificados**
- Incluye tasa BCV utilizada en el momento de la compra
- Desglose completo del IGTF aplicado
- Información venezolana en todos los correos transaccionales

### 📊 **Reportes Fiscales**
- Reporte de IVA e IGTF recaudados por período
- Desglose mensual de impuestos
- Exportación de datos para contabilidad

### ⚙️ **Panel de Administración**
- Metadatos venezolanos en pedidos
- Configuración completa del plugin
- Asistente de configuración inicial
- Estadísticas de uso

## Requisitos

- WordPress 5.0 o superior
- WooCommerce 5.0 o superior
- PHP 7.4 o superior
- Plugin "BCV Dólar" activo

## Instalación

1. Sube la carpeta `woocommerce-venezuela-pro` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el panel de administración de WordPress
3. Ve a **WooCommerce > Venezuela Pro** para configurar el plugin
4. Ejecuta el asistente de configuración inicial

## Configuración

### Configuración General
- **Mostrar referencia en bolívares**: Habilita/deshabilita la visualización de precios en VES
- **Habilitar IGTF**: Permite la aplicación del IGTF en las pasarelas configuradas

### Configuración de Precios
- **Formato de referencia**: Personaliza cómo se muestra el precio en bolívares
- **Posición de la moneda**: Antes o después del precio
- **Separadores**: Configura separadores de miles y decimales

### Configuración del Checkout
- **Cédula/RIF obligatorio**: Hace obligatorio el campo de identificación
- **Placeholder personalizado**: Texto de ayuda para el campo de Cédula/RIF

### Configuración de IGTF
- **Tasa de IGTF**: Porcentaje a aplicar (por defecto 3%)
- **Descripción**: Texto descriptivo del impuesto

## Pasarelas de Pago

### Zelle
- Configuración de correo electrónico Zelle
- Campo de número de confirmación obligatorio
- Opción de aplicar IGTF

### Pago Móvil
- Configuración de datos bancarios (banco, cédula, teléfono)
- Cálculo automático del monto en bolívares
- Campo de número de confirmación obligatorio

## Metadatos de Pedidos

El plugin guarda automáticamente los siguientes metadatos en cada pedido:

- `_billing_cedula_rif`: Cédula o RIF del cliente
- `_bcv_rate_at_purchase`: Tasa BCV utilizada en el momento de la compra
- `_payment_reference`: Número de confirmación del pago
- `_igtf_applied`: Si se aplicó IGTF (yes/no)
- `_igtf_amount`: Monto del IGTF aplicado
- `_total_ves`: Total del pedido en bolívares (para Pago Móvil)

## Hooks y Filtros

### Filtros
- `wvp_price_format`: Modifica el formato de la referencia en bolívares
- `wvp_igtf_rate`: Modifica la tasa de IGTF aplicada
- `wvp_cedula_rif_validation`: Modifica la validación del campo Cédula/RIF

### Acciones
- `wvp_order_metadata_saved`: Se ejecuta después de guardar metadatos venezolanos
- `wvp_igtf_calculated`: Se ejecuta después de calcular el IGTF
- `wvp_bcv_rate_updated`: Se ejecuta cuando se actualiza la tasa BCV

## Desarrollo

### Estructura del Plugin

```
woocommerce-venezuela-pro/
├── woocommerce-venezuela-pro.php    # Archivo principal
├── includes/                         # Clases principales
│   ├── class-wvp-dependencies.php
│   ├── class-wvp-bcv-integrator.php
│   ├── class-wvp-email-modifier.php
│   ├── class-wvp-fiscal-reports.php
│   └── class-wvp-order-metadata.php
├── frontend/                         # Funcionalidades del frontend
│   ├── class-wvp-price-display.php
│   └── class-wvp-checkout.php
├── admin/                           # Panel de administración
│   ├── class-wvp-order-meta.php
│   └── class-wvp-admin-settings.php
├── gateways/                        # Pasarelas de pago
│   ├── class-wvp-gateway-zelle.php
│   └── class-wvp-gateway-pago-movil.php
└── assets/                          # Recursos estáticos
    ├── css/
    └── js/
```

### Contribuir

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Añade nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un Pull Request

## Soporte

Para soporte técnico o reportar bugs, por favor:

1. Verifica que tienes las dependencias correctas instaladas
2. Revisa los logs de WordPress para errores
3. Crea un issue en el repositorio con:
   - Descripción detallada del problema
   - Pasos para reproducir
   - Información del sistema (WordPress, WooCommerce, PHP)

## Changelog

### 1.0.0
- Lanzamiento inicial
- Integración con BCV Dólar
- Pasarelas de pago Zelle y Pago Móvil
- Sistema de IGTF
- Reportes fiscales
- Panel de administración completo

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

## Créditos

Desarrollado por Kinta Electric para el mercado venezolano.

---

**Nota**: Este plugin requiere el plugin "BCV Dólar" para funcionar correctamente. Asegúrate de tenerlo instalado y activo antes de usar WooCommerce Venezuela Pro.