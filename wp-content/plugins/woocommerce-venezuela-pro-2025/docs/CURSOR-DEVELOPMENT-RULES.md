# 🤖 Reglas de Desarrollo con Cursor - WooCommerce Venezuela Suite

## Guía para Desarrollo Asistido por IA

Este documento establece las reglas específicas para el desarrollo del plugin WooCommerce Venezuela Suite utilizando Cursor como herramienta de desarrollo asistido por IA.

---

## 1. Principios Fundamentales

### Generación Atómica
- **Una función/método a la vez**: Nunca generar clases completas o archivos enteros
- **Revisión obligatoria**: Cada bloque de código debe ser revisado antes de continuar
- **Testing incremental**: Probar cada función individualmente antes de integrar

### Explicación Obligatoria
Después de generar cualquier código, la IA debe explicar:
- **Lógica implementada**: Qué hace el código y por qué
- **Hooks utilizados**: Qué actions/filters de WordPress/WooCommerce usa
- **Justificación**: Por qué se eligió esa implementación específica
- **Dependencias**: Qué otras funciones o clases necesita

### Especificidad en las Peticiones
En lugar de prompts genéricos, usar descripciones detalladas:

❌ **Mal**: "Crea la pasarela de Pago Móvil"
✅ **Bueno**: "Genera una clase WCVS_Gateway_PagoMovil que extienda WC_Payment_Gateway. Incluye los campos de configuración: title, description, beneficiary_name, beneficiary_id, phone_number, bank_name. El método process_payment() debe cambiar el estado del pedido a 'on-hold', generar instrucciones de pago, y retornar el redirect a la página 'thank you'."

---

## 2. Estructura de Prompts Efectivos

### Para Funciones Individuales
```
Genera la función [nombre_función] que:
- Reciba los parámetros: [lista_parámetros]
- Valide [condiciones_específicas]
- Retorne [tipo_dato_esperado]
- Use el hook [hook_específico]
- Siga las mejores prácticas de WordPress
- Incluya documentación PHPDoc completa
```

### Para Métodos de Clase
```
Genera el método [nombre_método] para la clase [nombre_clase] que:
- Extienda [clase_padre]
- Implemente [interfaz_método]
- Maneje [casos_específicos]
- Incluya [validaciones_requeridas]
- Siga el patrón [patrón_diseño]
```

### Para Hooks y Filtros
```
Implementa el hook [tipo_hook] que:
- Se ejecute en [momento_específico]
- Modifique [elemento_objetivo]
- Valide [condiciones_previas]
- Maneje [casos_error]
- Sea compatible con [versiones_wordpress]
```

---

## 3. Reglas de Calidad de Código

### No Código Muerto
La IA debe:
- **Eliminar variables no utilizadas**
- **Remover funciones redundantes**
- **Optimizar consultas innecesarias**
- **Limpiar imports no usados**
- **Simplificar lógica compleja**

### Priorizar APIs Nativas
Siempre usar:
- **Settings API** para configuraciones
- **WC_Data** para manipular objetos WooCommerce
- **wp_cache_*** para caching
- **WP_Query** para consultas personalizadas
- **wp_remote_get/post** para requests HTTP

### Seguridad Obligatoria
Todo código debe incluir:
- **Sanitización**: `sanitize_text_field()`, `sanitize_email()`, etc.
- **Validación**: `wp_verify_nonce()`, `current_user_can()`
- **Escapado**: `esc_html()`, `esc_url()`, `esc_attr()`
- **Prepared Statements**: Para consultas de base de datos

---

## 4. Patrones de Refactorización

### Después de Generar Código
Usar este prompt estándar:
```
Analiza el siguiente código:
[pegar_código_generado]

Identifica y corrige:
1. Posibles mejoras de rendimiento
2. Problemas de seguridad
3. Violaciones de WordPress Coding Standards
4. Código redundante o innecesario
5. Oportunidades de optimización
6. Mejoras de legibilidad

Refactoriza aplicando las mejores prácticas de WordPress.
```

### Para Optimización de Performance
```
Optimiza esta función para:
- Reducir consultas a la base de datos
- Implementar caching apropiado
- Minimizar procesamiento innecesario
- Mejorar tiempo de respuesta
- Reducir uso de memoria
```

---

## 5. Testing y Validación

### Generación de Tests
Para cada función generada, crear:
```
Genera un test unitario para la función [nombre_función] que:
- Pruebe casos válidos
- Valide casos límite
- Verifique manejo de errores
- Confirme tipos de retorno
- Use datos de prueba realistas para Venezuela
```

### Validación de Integración
```
Verifica que esta implementación:
- Sea compatible con WooCommerce [versión]
- Funcione con WordPress [versión]
- No entre en conflicto con plugins populares
- Mantenga compatibilidad con temas estándar
- Siga las mejores prácticas de performance
```

---

## 6. Documentación Automática

### PHPDoc Obligatorio
Cada función debe incluir:
```php
/**
 * [Descripción breve de la función]
 *
 * @since   1.0.0
 * @package Woocommerce_Venezuela_Pro_2025
 *
 * @param   [tipo] $[parametro] [Descripción del parámetro]
 * @return  [tipo] [Descripción del valor de retorno]
 * @throws  [Exception] [Condiciones que pueden lanzar excepciones]
 */
```

### Comentarios Inline
Para lógica compleja:
```php
// Validar formato de RIF venezolano
// Patrón: V-12345678-9 o J-12345678-9
$rif_pattern = '/^[VEJPG]-?\d{8}-?\d$/';
```

---

## 7. Manejo de Errores

### Estrategia de Fallback
```
Implementa un sistema de fallback que:
- Detecte cuando BCV no está disponible
- Use último tipo de cambio conocido
- Muestre mensaje informativo al usuario
- Permita configuración manual de emergencia
- Registre el error en logs para debugging
```

### Logging Inteligente
```
Crea un sistema de logging que:
- Registre solo errores importantes en producción
- Incluya contexto suficiente para debugging
- Use diferentes niveles (debug, info, warning, error)
- Rote logs automáticamente
- Sea compatible con plugins de logging populares
```

---

## 8. Integración con Ecosistema

### Compatibilidad con BCV Dólar Tracker
```
Verifica integración con BCV Dólar Tracker:
- Use la API del plugin existente
- Maneje casos cuando no esté disponible
- Implemente fallback a configuración manual
- Mantenga compatibilidad con futuras versiones
- Documente dependencias claramente
```

### Compatibilidad con Tema Electro
```
Asegura compatibilidad con tema Electro:
- Use hooks estándar de WooCommerce
- No modifique templates directamente
- Implemente personalizaciones vía filters
- Mantenga responsive design
- Optimice para productos eléctricos
```

---

## 9. Prompts Específicos por Módulo

### Módulo de Moneda
```
Genera función de conversión de moneda que:
- Use API de BCV Dólar Tracker
- Implemente cache de 30 minutos
- Maneje conversión USD a VES
- Valide tipos de cambio válidos
- Incluya logging de conversiones
- Tenga fallback a tasa manual
```

### Módulo de Pagos
```
Crea pasarela de pago que:
- Extienda WC_Payment_Gateway
- Valide datos específicos de Venezuela
- Genere instrucciones de pago claras
- Maneje estados de pedido apropiados
- Incluya confirmación de pago
- Sea compatible con checkout de WooCommerce
```

### Módulo de Impuestos
```
Implementa cálculo de impuestos que:
- Calcule IVA (16%) automáticamente
- Aplique IGTF (3%) solo para pagos en divisas
- Valide documentos venezolanos (RIF, Cédula)
- Integre con sistema de impuestos de WooCommerce
- Maneje exenciones apropiadas
- Genere reportes fiscales
```

### Módulo de Envíos
```
Crea método de envío que:
- Calcule costos por peso y destino
- Integre con APIs de couriers venezolanos
- Valide códigos postales venezolanos
- Maneje zonas de envío por estado
- Incluya tiempos de entrega estimados
- Genere etiquetas de envío
```

---

## 10. Checklist de Calidad

### Antes de Aceptar Código
- [ ] ¿Está documentado con PHPDoc?
- [ ] ¿Incluye sanitización y validación?
- [ ] ¿Usa APIs nativas de WordPress/WooCommerce?
- [ ] ¿Maneja errores apropiadamente?
- [ ] ¿Es compatible con versiones requeridas?
- [ ] ¿Incluye logging para debugging?
- [ ] ¿Sigue WordPress Coding Standards?
- [ ] ¿Está optimizado para performance?
- [ ] ¿Es compatible con el ecosistema existente?
- [ ] ¿Incluye casos de prueba?

### Después de Implementar
- [ ] ¿Funciona en entorno de testing?
- [ ] ¿No genera errores PHP?
- [ ] ¿Es compatible con otros plugins?
- [ ] ¿Mantiene funcionalidad existente?
- [ ] ¿Mejora la experiencia del usuario?
- [ ] ¿Está listo para producción?

---

## 11. Ejemplos de Prompts Exitosos

### Generar Función de Conversión
```
Genera la función convert_usd_to_ves que:
- Reciba un parámetro $usd_amount (float)
- Use BCV_Dolar_Tracker::get_current_rate() para obtener tasa
- Implemente cache usando wp_cache_get/set con clave 'wcvs_conversion_rate'
- Valide que la tasa sea mayor a 0
- Retorne el monto convertido (float)
- Incluya logging usando error_log() si WP_DEBUG está activo
- Maneje excepción si BCV_Dolar_Tracker no está disponible
- Siga WordPress Coding Standards
- Incluya documentación PHPDoc completa
```

### Generar Método de Validación
```
Genera el método validate_venezuelan_rif para la clase WCVS_Tax_Manager que:
- Reciba parámetro $rif (string)
- Valide formato venezolano: V-12345678-9, J-12345678-9, etc.
- Use regex para validación
- Retorne boolean (true si válido)
- Incluya sanitización con sanitize_text_field()
- Maneje casos de entrada vacía o nula
- Registre intentos de validación en logs
- Siga principios de seguridad de WordPress
- Incluya documentación PHPDoc
```

---

*Estas reglas garantizan que el código generado por Cursor sea de alta calidad, seguro, mantenible y específico para el mercado venezolano.*
