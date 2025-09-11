# Instrucciones de Instalación - WooCommerce Venezuela Pro

## Requisitos Previos

Antes de instalar el plugin, asegúrate de tener:

1. **WordPress 5.0 o superior**
2. **WooCommerce 5.0 o superior**
3. **PHP 7.4 o superior**
4. **Plugin "BCV Dólar Tracker" activo**

## Paso 1: Verificar Dependencias

### 1.1 Verificar WooCommerce
- Ve a **Plugins > Plugins Instalados**
- Asegúrate de que WooCommerce esté activo
- Si no está instalado, instálalo desde **Plugins > Añadir Nuevo**

### 1.2 Verificar BCV Dólar Tracker
- Ve a **Plugins > Plugins Instalados**
- Busca "BCV Dólar Tracker" y asegúrate de que esté activo
- Si no está instalado, instálalo desde **Plugins > Añadir Nuevo**

### 1.3 Verificar la Tabla BCV
- Ve a **Herramientas > Información del Sistema**
- Busca la sección "Base de Datos"
- Verifica que exista la tabla `wp_bcv_precio_dolar`
- Si no existe, activa el plugin BCV Dólar Tracker

## Paso 2: Instalar el Plugin

### 2.1 Subir el Plugin
1. Comprime la carpeta `woocommerce-venezuela-pro` en un archivo ZIP
2. Ve a **Plugins > Añadir Nuevo > Subir Plugin**
3. Selecciona el archivo ZIP y haz clic en "Instalar Ahora"
4. Una vez instalado, haz clic en "Activar Plugin"

### 2.2 Verificar la Instalación
- Ve a **Plugins > Plugins Instalados**
- Busca "WooCommerce Venezuela Pro"
- Debe aparecer como "Activo"

## Paso 3: Configuración Inicial

### 3.1 Ejecutar el Asistente de Configuración
1. Ve a **WooCommerce > Venezuela Pro**
2. Haz clic en "Ejecutar Asistente de Configuración"
3. El asistente configurará automáticamente:
   - Moneda a USD
   - Formato de precios venezolano
   - Ubicación a Venezuela
   - Opciones del plugin

### 3.2 Configurar Pasarelas de Pago
1. Ve a **WooCommerce > Ajustes > Pagos**
2. Busca "Zelle" y haz clic en "Configurar"
3. Completa los campos:
   - **Activar/Desactivar**: Marca para activar
   - **Título**: "Zelle"
   - **Descripción**: "Pago mediante Zelle"
   - **Correo Zelle**: Tu correo de Zelle
   - **Aplicar IGTF**: Marca si deseas aplicar IGTF
4. Haz clic en "Guardar Cambios"

5. Busca "Pago Móvil" y haz clic en "Configurar"
6. Completa los campos:
   - **Activar/Desactivar**: Marca para activar
   - **Título**: "Pago Móvil"
   - **Descripción**: "Pago mediante Pago Móvil"
   - **Nombre del Banco**: "Banco de Venezuela"
   - **Cédula del Titular**: Tu cédula
   - **Teléfono del Titular**: Tu teléfono
   - **Aplicar IGTF**: Marca si deseas aplicar IGTF
7. Haz clic en "Guardar Cambios"

## Paso 4: Verificar el Funcionamiento

### 4.1 Verificar Precios en Bolívares
1. Ve a tu tienda
2. Abre cualquier producto
3. Debe aparecer el precio en USD y la referencia en bolívares

### 4.2 Verificar Checkout
1. Añade un producto al carrito
2. Ve al checkout
3. Debe aparecer el campo "Cédula o RIF"
4. Selecciona una pasarela de pago
5. Debe aparecer la información de IGTF si está configurado

### 4.3 Verificar Administración
1. Ve a **WooCommerce > Pedidos**
2. Abre cualquier pedido
3. Debe aparecer el meta box "Detalles de la Transacción (Venezuela)"

## Paso 5: Resolución de Problemas

### 5.1 Error de Dependencias
Si aparece el error "WooCommerce Venezuela Pro requiere WooCommerce y BCV Dólar para funcionar":

1. Verifica que WooCommerce esté activo
2. Verifica que BCV Dólar Tracker esté activo
3. Verifica que la tabla `wp_bcv_precio_dolar` exista
4. Si persiste el error, desactiva y reactiva el plugin

### 5.2 No Aparecen Precios en Bolívares
1. Ve a **WooCommerce > Venezuela Pro > Precios**
2. Verifica que "Mostrar referencia en bolívares" esté marcado
3. Verifica que la tasa BCV esté disponible
4. Limpia la caché del sitio

### 5.3 No Aparece el Campo Cédula/RIF
1. Ve a **WooCommerce > Venezuela Pro > Checkout**
2. Verifica que "Cédula/RIF obligatorio" esté marcado
3. Verifica que el tema sea compatible con WooCommerce
4. Prueba con un tema por defecto

### 5.4 No Aparece IGTF
1. Ve a **WooCommerce > Venezuela Pro > IGTF**
2. Verifica que "Habilitar IGTF" esté marcado
3. Verifica que la pasarela de pago tenga IGTF habilitado
4. Verifica que haya productos en el carrito

## Paso 6: Configuración Avanzada

### 6.1 Personalizar Formato de Precios
1. Ve a **WooCommerce > Venezuela Pro > Precios**
2. Modifica el "Formato de referencia en bolívares"
3. Ajusta los separadores de miles y decimales
4. Guarda los cambios

### 6.2 Configurar Reportes Fiscales
1. Ve a **WooCommerce > Reportes > Venezuela > Reporte Fiscal**
2. Selecciona el rango de fechas
3. Haz clic en "Generar Reporte"
4. Exporta los datos si es necesario

### 6.3 Configurar Correos Electrónicos
1. Ve a **WooCommerce > Ajustes > Correos**
2. Edita cualquier plantilla de correo
3. Los metadatos venezolanos se añadirán automáticamente

## Soporte Técnico

Si tienes problemas con la instalación:

1. **Revisa los logs de WordPress** en `wp-content/debug.log`
2. **Verifica la compatibilidad** del tema con WooCommerce
3. **Desactiva otros plugins** temporalmente para identificar conflictos
4. **Contacta al soporte técnico** con detalles del problema

## Información Adicional

- **Versión del Plugin**: 1.0.0
- **Versión Mínima de WordPress**: 5.0
- **Versión Mínima de WooCommerce**: 5.0
- **Versión Mínima de PHP**: 7.4
- **Idioma**: Español (España)
- **Licencia**: GPL v2 o posterior

---

**Nota**: Este plugin está diseñado específicamente para el mercado venezolano y requiere el plugin "BCV Dólar Tracker" para funcionar correctamente.
