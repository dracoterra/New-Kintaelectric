# Corrección del Sistema de IVA e IGTF

## Problemas Identificados

### 1. IVA no se estaba aplicando correctamente
- **Problema**: El plugin no configuraba las tasas de impuestos de WooCommerce para aplicar IVA del 16%
- **Causa**: No había integración con el sistema de impuestos nativo de WooCommerce
- **Impacto**: Los productos no tenían IVA aplicado durante la compra

### 2. Cálculo incorrecto de IVA en reportes SENIAT
- **Problema**: Los reportes calculaban el IVA dividiendo el total por 1.19, asumiendo que el IVA ya estaba incluido
- **Causa**: No se obtenían los datos reales de IVA de las órdenes de WooCommerce
- **Impacto**: Los reportes mostraban valores incorrectos de IVA

### 3. Productos no se mostraban en reportes SENIAT
- **Problema**: Los reportes solo mostraban el total de la orden, no los productos individuales
- **Causa**: El código no obtenía los items de las órdenes
- **Impacto**: Imposible ver qué productos se vendieron en cada transacción

### 4. IGTF no funcionaba correctamente
- **Problema**: Había dos sistemas de IGTF conflictuando (WVP_Checkout y WVP_IGTF_Manager)
- **Causa**: Duplicación de lógica de cálculo de IGTF
- **Impacto**: IGTF no se aplicaba o se aplicaba incorrectamente

## Soluciones Implementadas

### 1. Nuevo Sistema de Gestión de Impuestos (`WVP_Tax_Manager`)

Se creó una nueva clase `WVP_Tax_Manager` que:

- **Configura automáticamente las tasas de IVA en WooCommerce**:
  - Crea/actualiza una tasa de impuesto del 16% para Venezuela
  - Asegura que los productos sean gravables
  - Configura WooCommerce para calcular impuestos correctamente

- **Gestiona IGTF de forma unificada**:
  - Aplica IGTF como fee en el carrito
  - Solo se aplica a métodos de pago configurados
  - Calcula sobre el subtotal + envío (antes de impuestos)

- **Guarda datos de impuestos en las órdenes**:
  - Guarda total de IVA en `_wvp_iva_total`
  - Guarda total de IGTF en `_wvp_igtf_total`
  - Guarda subtotal antes de impuestos en `_wvp_subtotal_before_taxes`
  - Guarda configuración de tasas aplicadas

### 2. Corrección de Reportes SENIAT

Los reportes ahora:

- **Obtienen datos reales de las órdenes**:
  - Usan los totales guardados en meta de la orden
  - Calculan desde los impuestos de WooCommerce si no hay meta
  - Tienen fallback al método antiguo solo como último recurso

- **Muestran productos individuales**:
  - Cada transacción muestra todos los productos comprados
  - Incluye cantidad, nombre y precio de cada producto
  - Disponible en vista HTML, CSV y Excel

- **Cálculos precisos**:
  - IVA se obtiene de los impuestos reales de WooCommerce
  - IGTF se obtiene de los fees de la orden
  - Subtotal se calcula correctamente antes de impuestos

### 3. Unificación del Sistema IGTF

- **Deshabilitado IGTF en WVP_Checkout**: Para evitar conflictos
- **IGTF manejado únicamente por WVP_Tax_Manager**: Sistema centralizado
- **Compatibilidad mantenida**: El código antiguo se mantiene comentado para referencia

## Configuración Requerida

### 1. Habilitar Impuestos en WooCommerce

El sistema automáticamente:
- Habilita el cálculo de impuestos (`woocommerce_calc_taxes = 'yes'`)
- Configura precios sin incluir impuestos
- Muestra impuestos por separado en carrito y checkout

### 2. Configurar Tasas de IVA

El sistema crea automáticamente una tasa de IVA del 16% para Venezuela. Si necesitas modificarla:

1. Ve a **WooCommerce > Configuración > Impuestos**
2. Busca la tasa "IVA" para Venezuela
3. Modifica el porcentaje si es necesario

### 3. Configurar IGTF

Para configurar IGTF:

1. Ve a **WooCommerce Venezuela Pro > Configuraciones**
2. Busca la sección de IGTF
3. Configura:
   - Tasa de IGTF (por defecto 3%)
   - Métodos de pago que aplican IGTF
   - Montos mínimos/máximos si aplica

## Archivos Modificados

1. **`includes/class-wvp-tax-manager.php`** (NUEVO)
   - Gestor centralizado de IVA e IGTF

2. **`includes/class-wvp-seniat-reports.php`**
   - Corregido cálculo de IVA e IGTF
   - Agregado productos individuales a reportes

3. **`woocommerce-venezuela-pro.php`**
   - Carga de nueva clase WVP_Tax_Manager
   - Deshabilitado WVP_IGTF_Manager para evitar conflictos

4. **`frontend/class-wvp-checkout.php`**
   - Deshabilitado sistema IGTF duplicado

## Pruebas Recomendadas

1. **Crear una orden de prueba**:
   - Verificar que IVA se aplica correctamente (16%)
   - Verificar que IGTF se aplica si el método de pago lo requiere
   - Verificar que los totales son correctos

2. **Revisar reportes SENIAT**:
   - Verificar que los productos se muestran correctamente
   - Verificar que IVA e IGTF son correctos
   - Verificar que los totales coinciden con las órdenes

3. **Verificar órdenes existentes**:
   - Las órdenes antiguas pueden no tener IVA/IGTF guardados en meta
   - El sistema calculará desde los impuestos de WooCommerce
   - Para órdenes futuras, los datos se guardarán automáticamente

## Notas Importantes

- **Órdenes antiguas**: Las órdenes creadas antes de esta actualización pueden no tener los datos de IVA/IGTF guardados en meta. El sistema intentará calcularlos desde los impuestos de WooCommerce.

- **Compatibilidad**: El código antiguo de IGTF se mantiene comentado para referencia, pero no se ejecuta para evitar conflictos.

- **Configuración de WooCommerce**: Asegúrate de que WooCommerce tenga habilitado el cálculo de impuestos. El sistema intenta habilitarlo automáticamente, pero verifica en **WooCommerce > Configuración > Impuestos**.

## Próximos Pasos Recomendados

1. **Probar con órdenes nuevas**: Crear órdenes de prueba para verificar que todo funciona correctamente
2. **Revisar órdenes existentes**: Verificar que los reportes muestran datos correctos para órdenes antiguas
3. **Configurar excepciones**: Si hay productos o categorías exentas de IVA, configurarlas en WooCommerce
4. **Documentar casos especiales**: Si hay casos especiales de cálculo, documentarlos

