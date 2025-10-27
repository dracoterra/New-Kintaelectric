# 📱 FLUJO COMPLETO - Sistema Pago Móvil

## 🎯 CÓMO FUNCIONA EL PAGO MÓVIL

### 1️⃣ CONFIGURACIÓN EN ADMINISTRADOR

**Ubicación:** WooCommerce > Ajustes > Pagos > Pago Móvil

#### Lo que el admin configura:
```
🏦 Configurar Cuentas Bancarias
[➕ Agregar Nueva Cuenta]

┌─ Cuenta #1 ──────────────────────────────────┐
│                              [🗑️ Eliminar]   │
│                                              │
│ Nombre *:                                     │
│ [Mi Banco Principal                     ]    │
│                                              │
│ Banco *:                                      │
│ [Banesco                               ▼]    │
│                                              │
│ Teléfono *:                                   │
│ [04141234567                             ]    │
│                                              │
│ Imagen QR (Opcional):                        │
│ [📤 Subir QR] [✖️ Remover]                   │
│ [Preview de imagen]                          │
└──────────────────────────────────────────────┘
```

**El admin puede:**
- ✅ Agregar ilimitadas cuentas (Banco Venezuela, Mercantil, Banesco, etc.)
- ✅ Subir imagen QR personalizada para cada cuenta
- ✅ Eliminar cuentas
- ✅ Configurar nombre, banco, teléfono

---

### 2️⃣ CHECKOUT DEL CLIENTE

**Cuando el cliente va a pagar:**

#### Paso 1: Selecciona Pago Móvil
```
┌─────────────────────────────────────────┐
│ Métodos de Pago:                        │
│ ○ Transferencia                         │
│ ● Pago Móvil                            │
└─────────────────────────────────────────┘
```

#### Paso 2: Ve información y selecciona banco
```
┌─────────────────────────────────────────┐
│ Total a pagar: 2,650.00 Bs             │
│ Tasa BCV: 53.00 Bs./USD                │
│                                         │
│ Selecciona tu banco: *                  │
│ [Selecciona tu banco              ▼]    │
│                                         │
│ [Muestra datos bancarios al seleccionar] │
│                                         │
│ Número de Confirmación: *               │
│ [ABC123456                        ]    │
└─────────────────────────────────────────┘
```

**Lo que pasa:**
1. Cliente selecciona banco
2. Ve datos bancarios (nombre, banco, teléfono)
3. Ingresa código de confirmación
4. Presiona "Realizar Pedido"

---

### 3️⃣ PROCESAMIENTO

**El sistema:**
1. Guarda el pedido con estado "On-Hold"
2. Guarda: cuenta seleccionada, código de confirmación, tasa BCV
3. Redirige a página "Thank You"

---

### 4️⃣ VISTA POST-PAGO (Thank You Page)

**Lo que ve el cliente después del checkout:**

```
┌─────────────────────────────────────────────────────┐
│ 📱 Realiza el Pago Móvil                           │
├─────────────────────────────────────────────────────┤
│                                                     │
│ ┌──────────────┬─────────────────────────────────┐ │
│ │ 🏦 Datos      │ 💰 Monto a Pagar              │ │
│ │               │                                 │ │
│ │ Cuenta:       │ 2,650.00 Bs                    │ │
│ │ Mi Banco      │ 50.00 USD                      │ │
│ │               │                                 │ │
│ │ Banco:        │ 📱 Escanea para pagar         │ │
│ │ Banesco       │ ┌─────────────┐               │ │
│ │               │ │             │               │ │
│ │ Teléfono:     │ │   [QR]      │               │ │
│ │ 04141234567   │ │             │               │ │
│ │               │ └─────────────┘               │ │
│ └──────────────┴─────────────────────────────────┘ │
│                                                     │
│ ✅ Código confirmación: [ABC123456]              │
└─────────────────────────────────────────────────────┘
```

**Características:**
- ✅ Muestra datos bancarios seleccionados
- ✅ Muestra QR personalizado (si admin subió) o QR dinámico
- ✅ Muestra monto en Bs. con tasa BCV
- ✅ Muestra código de confirmación guardado

---

### 5️⃣ CLIENTE REALIZA EL PAGO

**El cliente:**
1. Abre su app del banco en su teléfono
2. Selecciona "Pago Móvil" o "Pago a Comercios"
3. Escanea el QR o ingresa datos manualmente
4. Realiza el pago desde su teléfono

---

### 6️⃣ CLIENTE CONFIRMA QUE PAGÓ

**Después de pagar, el cliente vuelve a la página y ve:**

```
┌─────────────────────────────────────────┐
│ [4] Confirma tu Pago                    │
├─────────────────────────────────────────┤
│                                         │
│ Ya pagaste? Llena estos datos:         │
│                                         │
│ Banco desde donde pagaste *             │
│ [Selecciona tu banco              ▼]    │
│                                         │
│ Teléfono desde donde pagaste *          │
│ [+58 | 04141234567              ]       │
│                                         │
│ Fecha del pago *                        │
│ [2025-10-26                        ]    │
│                                         │
│ Número de referencia (4 dígitos) *       │
│ [1234                             ]     │
│                                         │
│ [✓] Acepto términos y condiciones      │
│                                         │
│         [Cancelar] [Confirmar Pedido]  │
└─────────────────────────────────────────┘
```

**El cliente:**
1. Llena el formulario con sus datos
2. Presiona "Confirmar Pedido"
3. El sistema guarda: banco, teléfono, fecha, referencia
4. El pedido queda "On-Hold" pendiente de verificación

---

### 7️⃣ ADMINISTRADOR VERIFICA

**El admin ve el pedido en WooCommerce:**

```
Pedidos → [Pedido On-Hold]

┌─────────────────────────────────────────┐
│ 📋 Detalle del Pedido                   │
├─────────────────────────────────────────┤
│ Estado: On-Hold                         │
│ Método: Pago Móvil                     │
│ Total: $50.00 (2,650.00 Bs.)          │
│                                         │
│ ┌─────────────────────────────────────┐│
│ │ Verificación de Pago Móvil          ││
│ ├─────────────────────────────────────┤│
│ │ Datos de la cuenta:                 ││
│ │ Cuenta: Mi Banco Principal          ││
│ │ Banco: Banesco                      ││
│ │ Teléfono: 04141234567              ││
│ │                                     ││
│ │ Código de confirmación:            ││
│ │ [ABC123456]                         ││
│ │                                     ││
│ │ Datos del cliente:                  ││
│ │ Ban co pagó: Banco de Venezuela    ││
│ │ Teléfono: 04261234567              ││
│ │ Fecha: 2025-10-26                  ││
│ │ Referencia: 1234                   ││
│ │                                     ││
│ │ [✓ Aprobar] [✗ Rechazar]           ││
│ └─────────────────────────────────────┘│
└─────────────────────────────────────────┘
```

**El admin:**
1. Ve todos los datos del pago
2. Abre su app del banco
3. Verifica que recibió el pago
4. Presiona "✓ Aprobar" o "✗ Rechazar"

---

### 8️⃣ CONTINUAR FLUJO

**Si se aprueba:**
- Pedido cambia a "Processing"
- Se envía email al cliente
- Continúa con preparación/envío

**Si se rechaza:**
- Pedido cambia a "Failed"
- Se envía email al cliente
- Cliente puede intentar nuevamente

---

## 📊 DIAGRAMA DE FLUJO

```
┌─────────────────────────────────────────────────┐
│ 1. ADMIN CONFIGURA                              │
│    - Agrega cuentas bancarias                   │
│    - Sube imágenes QR                           │
│    - Configura nombre, banco, teléfono          │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ 2. CLIENTE EN CHECKOUT                         │
│    - Selecciona Pago Móvil                     │
│    - Selecciona banco                           │
│    - Ingresa código confirmación                │
│    - Procesa pedido                             │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ 3. VISTA POST-PAGO                              │
│    - Muestra datos bancarios                    │
│    - Muestra QR code                            │
│    - Muestra monto en Bs.                       │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ 4. CLIENTE PAGA                                 │
│    - Abre app del banco                         │
│    - Escanea QR / Ingresa datos                │
│    - Realiza pago móvil                         │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ 5. CLIENTE CONFIRMA                             │
│    - Llena formulario: banco, teléfono, fecha  │
│    - Ingresa referencia (4 dígitos)           │
│    - Confirma pedido                             │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ 6. ADMIN VERIFICA                               │
│    - Abre app del banco                         │
│    - Verifica que recibió el pago              │
│    - Aprobar o Rechazar                         │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ 7. CONTINUAR FLUJO                              │
│    - Si aprobado: Processing → Shipping         │
│    - Si rechazado: Failed → Cliente reintenta │
└─────────────────────────────────────────────────┘
```

---

## ✅ CARACTERÍSTICAS PRINCIPALES

### Configuración Admin:
- ✅ Múltiples cuentas bancarias (ilimitadas)
- ✅ Subir imágenes QR personalizadas
- ✅ Nombre, Banco, Teléfono

### Checkout Cliente:
- ✅ Selector de banco dinámico
- ✅ Muestra datos bancarios al seleccionar
- ✅ Calculo automático en Bs. con tasa BCV
- ✅ Validación de código de confirmación

### Vista Post-Pago:
- ✅ Muestra datos bancarios
- ✅ QR code (personalizado o dinámico)
- ✅ Monto en Bs. con tasa BCV
- ✅ Código de confirmación

### Confirmación Cliente:
- ✅ Banco desde donde pagó
- ✅ Teléfono desde donde pagó
- ✅ Fecha del pago
- ✅ Número de referencia (4 dígitos)

### Verificación Admin:
- ✅ Ve todos los datos del pago
- ✅ Verifica en teléfono
- ✅ Aprobar o Rechazar con un click

---

**Sistema completamente funcional y listo para usar** 🎉

