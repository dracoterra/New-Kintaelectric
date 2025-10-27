# 📱 CÓMO FUNCIONA EL PAGO MÓVIL - Guía Completa

## 🎯 CÓMO DEBE TRABAJAR

### 1️⃣ CONFIGURACIÓN (Admin)

**Ubicación:** WooCommerce > Ajustes > Pagos > Pago Móvil

```
1. Activar Pago Móvil [✓]
2. Configurar cuentas:
   - Nombre de la cuenta
   - Cédula/RIF ⭐ (REQUERIDO en Venezuela)
   - Banco (27 opciones)
   - Teléfono
   - Imagen QR (opcional)

3. Botón "Agregar Nueva Cuenta"
4. Guardar
```

**Campos de cada cuenta:**
- ✅ Nombre: Identificador (ej: "Mi Banco Principal")
- ✅ Cédula/RIF: V12345678 o J-41234567-8
- ✅ Banco: Selector con 27 bancos venezolanos
- ✅ Teléfono: Número registrado en Pago Móvil
- ✅ Imagen QR: Opcional (el admin sube QR personalizado)

---

### 2️⃣ CHECKOUT (Cliente)

**Cuando el cliente va a pagar:**

```
Paso 1: Selecciona Pago Móvil como método de pago

Paso 2: Ve las cuentas disponibles como RADIO BUTTONS:

┌───────────────────────────────────────┐
│ Selecciona tu banco:                  │
│                                       │
│ ⭕ Banesco                             │
│ ⭕ Banco de Venezuela                 │
│ ⭕ Mercantil                           │
│ ⭕ Bancamiga                            │
└───────────────────────────────────────┘

Paso 3: Al seleccionar un radio button:

[Se muestra automáticamente]
┌───────────────────────────────────────┐
│ Datos para Pago Móvil:                │
│                                       │
│ Cédula/RIF: V12345678                │
│ Cuenta: Mi Banco Principal           │
│ Banco: Banesco                        │
│ Teléfono: 04141234567                │
└───────────────────────────────────────┘

[Aparece campo de confirmación]
┌───────────────────────────────────────┐
│ Número de Confirmación *              │
│ [ABC123456                      ]    │
└───────────────────────────────────────┘

Paso 4: Ingresa código y presiona "Procesar"
```

---

### 3️⃣ VISTA PREVIA (After "Procesar")

**Inmediatamente después de procesar, aparece:**

```
┌───────────────────────────────────────────┐
│ 📱 Realiza el Pago Móvil                  │
├───────────────────────────────────────────┤
│                                           │
│ ┌──────────────┬───────────────────────┐ │
│ │ 🏦 Datos      │ 💰 Monto            │ │
│ │               │                       │ │
│ │ Cédula:       │ 2,650.00 Bs         │ │
│ │ V12345678    │ 50.00 USD            │ │
│ │               │                       │ │
│ │ Banco:        │ Tasa: 53.00         │ │
│ │ Banesco      │                       │ │
│ │               │ 📱 Escanea para pagar│ │
│ │ Teléfono:     │ ┌─────────────┐     │ │
│ │ 04141234567  │ │   [QR]       │     │ │
│ │               │ │             │     │ │
│ │               │ └─────────────┘     │ │
│ └──────────────┴───────────────────────┘ │
│                                           │
│ ✅ Tu código: [ABC123456]                │
│                                           │
│ [Esto ya fue guardado]                    │
└───────────────────────────────────────────┘
```

---

### 4️⃣ CLIENTE REALIZA PAGO

**El cliente:**
1. Abre su app del banco en su teléfono
2. Selecciona "Pago Móvil" o "Comercios"
3. Usa los datos mostrados (cédula, banco, teléfono)
4. Escanea el QR (si admin lo subió) o ingresa datos manualmente
5. Realiza el pago

---

### 5️⃣ CLIENTE CONFIRMA QUE PAGÓ (NUEVO)

**Después de realizar el pago, vuelve a la página y ve:**

```
┌───────────────────────────────────────────┐
│ [4] Confirma tu Pago                      │
├───────────────────────────────────────────┤
│ Ya pagaste? Llena estos datos:           │
│                                           │
│ Banco desde donde pagaste *                │
│ [Select a bank                     ▼]     │
│                                           │
│ Teléfono desde donde pagaste *            │
│ [+58 | 04261234567                  ]    │
│                                           │
│ Fecha *                                    │
│ [10/26/2025                       📅]     │
│                                           │
│ Referencia (últimos 6 dígitos) *          │
│ [123456                            ]      │
│                                           │
│ [✓] Términos y condiciones                │
│                                           │
│         [Cancelar] [Confirmar Pedido]     │
└───────────────────────────────────────────┘
```

---

### 6️⃣ ADMINISTRADOR VERIFICA

**El admin ve pedido "On-Hold" con:**
- Cédula/RIF de la cuenta
- Nombre de la cuenta
- Banco y teléfono
- QR code
- Código de confirmación
- Datos del cliente (banco desde donde pagó, teléfono, fecha, referencia)

**Admin:**
1. Verifica en su teléfono que recibió el pago
2. Presiona "✓ Aprobar" o "✗ Rechazar"

---

### 7️⃣ CONTINUAR FLUJO

**Si aprobado:** Processing → Preparación → Envío
**Si rechazado:** Failed → Cliente reintenta

---

## 🔑 DIFERENCIAS IMPORTANTES

### ❌ NO DEBES HACER:
- Subir QR durante el checkout
- Mostrar formulario complejo en el checkout
- Pedir muchos datos en el checkout

### ✅ LO QUE DEBE HACER:
1. **Checkout Simple**: Radio buttons con bancos
2. **Vista Previa**: Después de "Procesar"
3. **QR Subido por Admin**: No se genera dinámicamente
4. **Confirmación Posterior**: Después de que el cliente pague

---

## 📊 FLUJO VISUAL

```
ADMIN CONFIGURA
└──> Múltiples cuentas (nombre, cédula, banco, teléfono, QR)

CHECKOUT CLIENTE
└──> Ve radio buttons con bancos
└──> Selecciona uno → Ve datos
└──> Ingresa código → Procesa

VISTA PREVIA
└──> Muestra datos bancarios
└──> Muestra QR (si admin subió)
└──> Muestra monto en Bs.
└──> Muestra código ya guardado

CLIENTE PAGA
└──> Abre app del banco
└──> Paga con su teléfono

CLIENTE CONFIRMA
└──> Llena banco, teléfono, fecha, referencia
└──> Submit → Pedido On-Hold

ADMIN VERIFICA
└──> Ve todos los datos
└──> Verifica en teléfono
└──> Aprobar/Rechazar

CONTINUAR
└──> Si aprobado: Processing
```

---

## ✅ CAMBIOS IMPLEMENTADOS

1. ✅ Campo Cédula/RIF añadido
2. ✅ Guardado correcto de cuentas
3. ✅ Radio buttons en checkout
4. ✅ Cédula incluida en vista
5. ✅ QR upload funcional
6. ✅ Vista previa funcional
7. ✅ Confirmación posterior implementada
8. ✅ Verificación admin implementada

**Sistema completamente funcional según especificaciones venezolanas** 🎉

