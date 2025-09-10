# Pasarelas de Pago - WooCommerce Venezuela Pro

Este directorio contiene las pasarelas de pago específicas para Venezuela.

## Pasarelas Incluidas

### 1. Zelle (`class-wvp-gateway-zelle.php`)
- **ID**: `wvp_zelle`
- **Descripción**: Acepta pagos mediante Zelle
- **Características**:
  - Campo de número de confirmación obligatorio
  - Configuración de correo electrónico Zelle
  - Opción de aplicar IGTF
  - Instrucciones personalizables

### 2. Pago Móvil (`class-wvp-gateway-pago-movil.php`)
- **ID**: `wvp_pago_movil`
- **Descripción**: Acepta pagos mediante Pago Móvil venezolano
- **Características**:
  - Cálculo automático del monto en bolívares
  - Configuración de datos bancarios
  - Campo de número de confirmación obligatorio
  - Información de pago en tiempo real

## Configuración

### Zelle
1. Ve a **WooCommerce > Ajustes > Pagos**
2. Busca "Zelle" en la lista de métodos de pago
3. Haz clic en "Configurar"
4. Completa los campos:
   - **Activar/Desactivar**: Marca para activar
   - **Título**: Título que verá el cliente
   - **Descripción**: Descripción del método de pago
   - **Correo Zelle**: Correo electrónico de la cuenta Zelle
   - **Aplicar IGTF**: Marca si deseas aplicar IGTF
   - **Instrucciones de pago**: Instrucciones para el cliente

### Pago Móvil
1. Ve a **WooCommerce > Ajustes > Pagos**
2. Busca "Pago Móvil" en la lista de métodos de pago
3. Haz clic en "Configurar"
4. Completa los campos:
   - **Activar/Desactivar**: Marca para activar
   - **Título**: Título que verá el cliente
   - **Descripción**: Descripción del método de pago
   - **Nombre del Banco**: Nombre del banco
   - **Cédula del Titular**: Cédula del titular de la cuenta
   - **Teléfono del Titular**: Teléfono del titular
   - **Aplicar IGTF**: Marca si deseas aplicar IGTF
   - **Instrucciones de pago**: Instrucciones para el cliente

## Personalización

### Añadir Nueva Pasarela
Para añadir una nueva pasarela de pago:

1. Crea un nuevo archivo `class-wvp-gateway-nombre.php`
2. Extiende la clase `WC_Payment_Gateway`
3. Implementa los métodos requeridos:
   - `__construct()`
   - `init_form_fields()`
   - `payment_fields()`
   - `process_payment()`
   - `is_available()`

### Ejemplo Básico
```php
<?php
class WVP_Gateway_Nueva_Pasarela extends WC_Payment_Gateway {
    
    public function __construct() {
        $this->id = 'wvp_nueva_pasarela';
        $this->has_fields = true;
        $this->method_title = __('Nueva Pasarela', 'wvp');
        $this->method_description = __('Descripción de la nueva pasarela', 'wvp');
        
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Activar/Desactivar', 'wvp'),
                'type' => 'checkbox',
                'label' => __('Activar Nueva Pasarela', 'wvp'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Título', 'wvp'),
                'type' => 'text',
                'description' => __('Título que verá el cliente', 'wvp'),
                'default' => __('Nueva Pasarela', 'wvp')
            )
        );
    }
    
    public function payment_fields() {
        // Campos de pago personalizados
    }
    
    public function process_payment($order_id) {
        // Lógica de procesamiento del pago
    }
    
    public function is_available() {
        return $this->enabled === 'yes';
    }
}
```

## Hooks y Filtros

### Filtros Disponibles
- `wvp_payment_gateway_settings`: Modifica la configuración de las pasarelas
- `wvp_payment_gateway_fields`: Modifica los campos de pago
- `wvp_payment_gateway_validation`: Modifica la validación de pagos

### Acciones Disponibles
- `wvp_payment_processed`: Se ejecuta después de procesar un pago
- `wvp_payment_failed`: Se ejecuta cuando falla un pago
- `wvp_payment_gateway_loaded`: Se ejecuta cuando se carga una pasarela

## Seguridad

- Todas las pasarelas incluyen validación de nonce
- Los datos se sanitizan antes de guardar
- Se verifican permisos antes de procesar pagos
- Los campos sensibles se encriptan si es necesario

## Soporte

Para soporte técnico o reportar problemas con las pasarelas de pago:

1. Verifica que la pasarela esté correctamente configurada
2. Revisa los logs de WordPress para errores
3. Asegúrate de que todas las dependencias estén instaladas
4. Contacta al soporte técnico con detalles del problema
