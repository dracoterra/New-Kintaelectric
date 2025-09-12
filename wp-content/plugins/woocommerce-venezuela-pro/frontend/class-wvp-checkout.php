<?php
/**
 * Clase para modificar el checkout con campos venezolanos e IGTF
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Checkout {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Obtener instancia del plugin de forma segura
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Hooks para el campo de cédula/RIF - USAR SOLO UNO PARA EVITAR DUPLICACIÓN
        add_filter("woocommerce_checkout_fields", array($this, "add_cedula_rif_field_checkout"));
        // DESHABILITADO - Evitar duplicación: add_filter("woocommerce_billing_fields", array($this, "add_cedula_rif_field"));
        add_action("woocommerce_checkout_process", array($this, "validate_cedula_rif_advanced"));
        add_action("woocommerce_checkout_create_order", array($this, "save_cedula_rif_field"));
        add_action("woocommerce_admin_order_data_after_billing_address", array($this, "display_cedula_rif_in_admin"));
        
        // Hook moderno para guardar campos personalizados (compatible con WooCommerce 8.0+)
        add_action("woocommerce_checkout_create_order", array($this, "save_cedula_rif_field_modern"));
        
        // Hook específico para WooCommerce Blocks
        add_action("woocommerce_store_api_checkout_update_order_meta", array($this, "save_cedula_rif_field_blocks"), 10, 1);
        
        // Registrar campo nativo en WooCommerce Blocks
        add_action("woocommerce_blocks_loaded", array($this, "register_cedula_rif_block_field"));
        
        // DESHABILITADO - Evitar duplicación: add_action("woocommerce_checkout_fields", array($this, "ensure_cedula_rif_field_registration"));
        
        // Hook para mostrar selector de zona de delivery
        add_action("woocommerce_before_checkout_billing_form", array($this, "display_delivery_zone_selector"));
        
        // Hook para guardar zona seleccionada
        add_action("woocommerce_checkout_process", array($this, "save_delivery_zone"));
        
        // Hook para actualizar totales cuando cambie la zona
        add_action("wp_ajax_wvp_update_delivery_zone", array($this, "update_delivery_zone_ajax"));
        add_action("wp_ajax_nopriv_wvp_update_delivery_zone", array($this, "update_delivery_zone_ajax"));
        
        // Añadir comisión IGTF
        add_action("woocommerce_cart_calculate_fees", array($this, "add_igtf_fee"));
        
        // Mostrar información de IGTF en el checkout
        add_action("woocommerce_review_order_before_payment", array($this, "display_igtf_info"));
        
        // DESHABILITADO TEMPORALMENTE - Investigar duplicación de campos
        // add_action("wp_enqueue_scripts", array($this, "enqueue_checkout_scripts"));
        
        // Hook para mostrar información de pago móvil
        add_action("woocommerce_review_order_after_payment", array($this, "display_pago_movil_info"));
    }
    
    
    /**
     * Añadir campo de cédula/RIF al checkout
     * 
     * @param array $fields Campos del checkout
     * @return array Campos modificados
     */
    public function add_cedula_rif_field($fields) {
        // Asegurar que el campo se añada a la sección de facturación
        if (!isset($fields['billing'])) {
            $fields['billing'] = array();
        }
        
        $fields['billing']['billing_cedula_rif'] = array(
            'label'       => __('Cédula o RIF', 'wvp'),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'clear'       => true,
            'priority'    => 115, // Después del teléfono
            'type'        => 'text',
            'custom_attributes' => array(
                'pattern' => '[VvEe]-[0-9]{7,8}|[JjGgPp]-[0-9]{8}-[0-9]',
                'title'   => __('Formato: V-12345678 o J-12345678-9', 'wvp')
            )
        );
        
        return $fields;
    }
    
    /**
     * Añadir campo de cédula/RIF usando woocommerce_checkout_fields
     * 
     * @param array $fields Campos del checkout
     * @return array Campos modificados
     */
    public function add_cedula_rif_field_checkout($fields) {
        // Asegurar que el campo se añada a la sección de facturación
        if (!isset($fields['billing'])) {
            $fields['billing'] = array();
        }
        
        $fields['billing']['billing_cedula_rif'] = array(
            'label'       => __('Cédula o RIF ', 'wvp'),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'clear'       => true,
            'priority'    => 115, // Después del teléfono
            'type'        => 'text',
            'custom_attributes' => array(
                'pattern' => '[VvEe]-[0-9]{7,8}|[JjGgPp]-[0-9]{8}-[0-9]',
                'title'   => __('Formato: V-12345678 o J-12345678-9', 'wvp')
            )
        );
        
        return $fields;
    }
    
    
    /**
     * Asegurar que el campo de cédula/RIF se registre correctamente
     */
    public function ensure_cedula_rif_field_registration() {
        // Verificar si el campo ya existe
        $checkout_fields = WC()->checkout->get_checkout_fields();
        if (isset($checkout_fields['billing']['billing_cedula_rif'])) {
            return;
        }
        
        // Añadir el campo manualmente si no existe
        $fields = WC()->checkout->get_checkout_fields();
        $fields['billing']['billing_cedula_rif'] = array(
            'label'       => __('Cédula o RIF', 'wvp'),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'clear'       => true,
            'priority'    => 115,
            'type'        => 'text',
            'custom_attributes' => array(
                'pattern' => '[VvEe]-[0-9]{7,8}|[JjGgPp]-[0-9]{8}-[0-9]',
                'title'   => __('Formato: V-12345678 o J-12345678-9', 'wvp')
            )
        );
        
        // Actualizar los campos del checkout
        WC()->checkout->set_checkout_fields($fields);
    }
    
    /**
     * Validar campo de cédula/RIF (Versión avanzada)
     * 
     * Comprueba si el campo está vacío y si tiene un formato válido.
     */
    public function validate_cedula_rif_advanced() {
        // Debug: Verificar que la función se ejecuta
        
        // Verificar si estamos en el checkout
        if (!is_checkout()) {
            return;
        }
        
        // Obtenemos el valor del campo de Cédula del formulario
        $cedula_rif = isset($_POST['billing_cedula_rif']) ? trim($_POST['billing_cedula_rif']) : '';
        
        
        // VALIDACIÓN 1: Comprobar si está vacío (si es un campo requerido)
        if (empty($cedula_rif)) {
                wc_add_notice(__('Por favor, introduce tu número de Cédula o RIF.', 'wvp'), 'error');
            return; // Detenemos las demás validaciones si está vacío
        }
        
        // VALIDACIÓN 2: Comprobar un formato específico (Cédula Venezolana)
        // Patrón para V-12345678, E-12345678, J-12345678-9, etc.
        if (!preg_match('/^[VEJPG]-?[0-9]{7,9}$/i', $cedula_rif)) {
            wc_add_notice(__('Por favor, introduce un cédula o RIF válido. Formato: V-12345678, E-12345678, J-12345678, P-12345678, G-12345678', 'wvp'), 'error');
        }
    }
    
    
    
    /**
     * Registrar campo nativo en WooCommerce Blocks
     */
    public function register_cedula_rif_block_field() {
        
        // Registrar el campo usando register_additional_checkout_field para Blocks
        add_action('woocommerce_init', array($this, 'register_cedula_rif_field_init'));
        
    }
    
    /**
     * Registrar campo usando register_additional_checkout_field para Blocks
     */
    public function register_cedula_rif_checkout_field() {
        
        // Verificar si la función existe
        if (function_exists('woocommerce_register_additional_checkout_field')) {
            woocommerce_register_additional_checkout_field(array(
                'id' => 'wvp/billing_cedula_rif',
                'label' => __('Cédula o RIF', 'wvp'),
                'optionalLabel' => __('Cédula o RIF (opcional)', 'wvp'),
                'location' => 'address',
                'required' => true,
                'description' => __('Formato: V-12345678, E-12345678, J-12345678, P-12345678, G-12345678', 'wvp'),
                'attributes' => array(
                    'autocomplete' => 'off',
                    'pattern' => '[VEJPG]-?[0-9]{7,9}',
                    'title' => __('Formato: V-12345678, E-12345678, J-12345678, P-12345678, G-12345678', 'wvp'),
                ),
            ));
            
        } else {
        }
    }
    
    /**
     * Registrar campo usando woocommerce_init para asegurar que se ejecute
     */
    public function register_cedula_rif_field_init() {
        
        // Verificar si la función existe
        if (function_exists('woocommerce_register_additional_checkout_field')) {
            woocommerce_register_additional_checkout_field(array(
                'id' => 'wvp/billing_cedula_rif',
                'label' => __('Cédula o RIF', 'wvp'),
                'optionalLabel' => __('Cédula o RIF (opcional)', 'wvp'),
                'location' => 'address',
                'required' => true,
                'description' => __('Formato: V-12345678, E-12345678, J-12345678, P-12345678, G-12345678', 'wvp'),
                'attributes' => array(
                    'autocomplete' => 'off',
                    'pattern' => '[VEJPG]-?[0-9]{7,9}',
                    'title' => __('Formato: V-12345678, E-12345678, J-12345678, P-12345678, G-12345678', 'wvp'),
                ),
            ));
            
        } else {
        }
    }
    
    /**
     * Validar campo de cédula/RIF
     */
    public function validate_cedula_rif_field($value) {
        error_log('WVP Debug: Validando campo cédula/RIF - Valor: ' . $value);
        
        if (empty($value)) {
            return new WP_Error('cedula_rif_required', __('El campo Cédula o RIF es obligatorio.', 'wvp'));
        }
        
        // Validar formato básico
        if (!preg_match('/^[VEJPG]-?[0-9]{7,9}$/i', $value)) {
            return new WP_Error('cedula_rif_invalid', __('Por favor, introduce un cédula o RIF válido. Formato: V-12345678, E-12345678, J-12345678, P-12345678, G-12345678', 'wvp'));
        }
        
        return true;
    }
    
    /**
     * Guardar campo de cédula/RIF
     */
    public function save_cedula_rif_field_callback($value, $order) {
        error_log('WVP Debug: Guardando campo cédula/RIF - Valor: ' . $value . ' - Order ID: ' . $order->get_id());
        
        $order->update_meta_data('_billing_cedula_rif', sanitize_text_field($value));
        $order->save();
        
        error_log('WVP Debug: Campo cédula/RIF guardado correctamente');
    }
    
    /**
     * Registrar campo en el schema de checkout para Blocks
     */
    public function register_cedula_rif_checkout_schema() {
        error_log('WVP Debug: Registrando campo en schema de checkout para Blocks');
        
        // Registrar el campo en el schema de checkout
        add_filter('woocommerce_store_api_checkout_schema', array($this, 'add_cedula_rif_to_checkout_schema'));
        
        error_log('WVP Debug: Campo en schema de checkout registrado correctamente');
    }
    
    /**
     * Añadir campo al schema de checkout
     */
    public function add_cedula_rif_to_checkout_schema($schema) {
        error_log('WVP Debug: Añadiendo campo al schema de checkout');
        
        if (isset($schema['billing_address']['properties'])) {
            $schema['billing_address']['properties']['cedula_rif'] = array(
                'type' => 'string',
                'description' => __('Cédula o RIF del cliente', 'wvp'),
                'context' => array('view', 'edit')
            );
        }
        
        error_log('WVP Debug: Campo añadido al schema de checkout');
        return $schema;
    }
    
    /**
     * Registrar campo REST para WooCommerce Blocks
     */
    public function register_cedula_rif_rest_field() {
        error_log('WVP Debug: Registrando campo REST para WooCommerce Blocks');
        
        // Registrar el campo en el schema de la API REST
        register_rest_field('order', 'billing_cedula_rif', array(
            'get_callback' => array($this, 'get_cedula_rif_rest_field'),
            'update_callback' => array($this, 'update_cedula_rif_rest_field'),
            'schema' => array(
                'description' => __('Cédula o RIF del cliente', 'wvp'),
                'type' => 'string',
                'context' => array('view', 'edit')
            )
        ));
        
        error_log('WVP Debug: Campo REST registrado correctamente');
    }
    
    /**
     * Obtener campo REST
     */
    public function get_cedula_rif_rest_field($object) {
        return get_post_meta($object['id'], '_billing_cedula_rif', true);
    }
    
    /**
     * Actualizar campo REST
     */
    public function update_cedula_rif_rest_field($value, $object) {
        error_log('WVP Debug: Actualizando campo REST - Valor: ' . $value);
        update_post_meta($object->ID, '_billing_cedula_rif', sanitize_text_field($value));
        return true;
    }
    
    /**
     * Añadir campo de cédula/RIF a los datos enviados
     * 
     * @param array $data Datos del checkout
     * @return array Datos modificados
     */
    public function add_cedula_rif_to_posted_data($data) {
        error_log('WVP Debug: add_cedula_rif_to_posted_data ejecutado');
        
        // Asegurar que el campo esté en los datos
        if (!isset($data['billing_cedula_rif'])) {
            $data['billing_cedula_rif'] = '';
        }
        
        error_log('WVP Debug: Campo billing_cedula_rif añadido a posted data');
        return $data;
    }
    
    /**
     * Validar campo de cédula/RIF nativo
     */
    public function validate_cedula_rif_native() {
        error_log('WVP Debug: ===== VALIDACIÓN NATIVA CÉDULA/RIF =====');
        
        // Obtener el valor del campo
        $cedula_rif = isset($_POST['billing_cedula_rif']) ? trim($_POST['billing_cedula_rif']) : '';
        
        error_log('WVP Debug: Valor del campo nativo: "' . $cedula_rif . '"');
        
        // VALIDACIÓN 1: Comprobar si está vacío
        if (empty($cedula_rif)) {
            error_log('WVP Debug: Campo vacío nativo - mostrando error');
            wc_add_notice(__('Por favor, introduce tu número de Cédula o RIF.', 'wvp'), 'error');
            return;
        }
        
        // VALIDACIÓN 2: Comprobar formato
        if (!preg_match('/^[VEJPG]-?[0-9]{7,9}$/i', $cedula_rif)) {
            error_log('WVP Debug: Formato inválido nativo - mostrando error');
            wc_add_notice(__('Por favor, introduce un cédula o RIF válido. Formato: V-12345678, E-12345678, J-12345678, P-12345678, G-12345678', 'wvp'), 'error');
            return;
        }
        
        error_log('WVP Debug: Campo válido nativo');
        error_log('WVP Debug: ===== FIN VALIDACIÓN NATIVA CÉDULA/RIF =====');
    }
    
    
    
    /**
     * Guardar campo de cédula/RIF en el pedido
     * 
     * @param WC_Order $order Pedido
     */
    public function save_cedula_rif_field($order) {
        error_log('WVP Debug: ===== GUARDAR CÉDULA/RIF =====');
        
        // Debug completo de $_POST
        error_log('WVP Debug: $_POST completo: ' . print_r($_POST, true));
        
        // Obtener datos del request actual usando múltiples fuentes
        $cedula_rif = '';
        
        // 1. Buscar en $_POST
        if (isset($_POST['billing_cedula_rif'])) {
            $cedula_rif = trim($_POST['billing_cedula_rif']);
        } elseif (isset($_POST['cedula_rif'])) {
            $cedula_rif = trim($_POST['cedula_rif']);
        }
        
        // 2. Si no está en $_POST, buscar en el request actual
        if (empty($cedula_rif)) {
            $request = \WP_REST_Request::from_url($_SERVER['REQUEST_URI']);
            if ($request) {
                $data = $request->get_json_params();
                if (isset($data['billing']['cedula_rif'])) {
                    $cedula_rif = trim($data['billing']['cedula_rif']);
                } elseif (isset($data['billing']['billing_cedula_rif'])) {
                    $cedula_rif = trim($data['billing']['billing_cedula_rif']);
                }
            }
        }
        
        error_log('WVP Debug: Valor del campo cedula_rif: "' . $cedula_rif . '"');
        
        if (!empty($cedula_rif)) {
            $order->update_meta_data('_billing_cedula_rif', sanitize_text_field($cedula_rif));
            // MAPEAR TAMBIÉN A _billing_cedula para compatibilidad con reportes SENIAT
            $order->update_meta_data('_billing_cedula', sanitize_text_field($cedula_rif));
            error_log('WVP Debug: Campo guardado en pedido: ' . $order->get_id());
        } else {
            error_log('WVP Debug: Campo vacío - no se guardó');
        }
        
        error_log('WVP Debug: ===== FIN GUARDAR CÉDULA/RIF =====');
    }
    
    /**
     * Guardar campo de cédula/RIF usando método moderno (compatible con WC 8.0+)
     * 
     * @param WC_Order $order Pedido
     */
    public function save_cedula_rif_field_modern($order) {
        // Verificar que el pedido existe
        if (!$order || !is_a($order, 'WC_Order')) {
            return;
        }
        
        // Obtener datos del request actual
        $cedula_rif = '';
        
        // 1. Buscar en $_POST (checkout clásico)
        if (isset($_POST['billing_cedula_rif'])) {
            $cedula_rif = sanitize_text_field($_POST['billing_cedula_rif']);
        } elseif (isset($_POST['cedula_rif'])) {
            $cedula_rif = sanitize_text_field($_POST['cedula_rif']);
        }
        
        // 2. Buscar en datos del pedido (WooCommerce Blocks)
        if (empty($cedula_rif)) {
            $cedula_rif = $order->get_meta('billing_cedula_rif');
        }
        
        // 3. Buscar en datos de facturación
        if (empty($cedula_rif)) {
            $cedula_rif = $order->get_meta('_billing_cedula_rif');
        }
        
        // Guardar si existe
        if (!empty($cedula_rif)) {
            $order->update_meta_data('_billing_cedula_rif', $cedula_rif);
            // MAPEAR TAMBIÉN A _billing_cedula para compatibilidad con reportes SENIAT
            $order->update_meta_data('_billing_cedula', $cedula_rif);
            
            // GUARDAR TASA DE CAMBIO ACTUAL PARA REPORTES SENIAT
            if (class_exists('WVP_BCV_Integrator')) {
                $rate = WVP_BCV_Integrator::get_rate();
                if ($rate && $rate > 0) {
                    $order->update_meta_data('_exchange_rate_at_purchase', $rate);
                    $order->update_meta_data('_exchange_rate_date', current_time('Y-m-d H:i:s'));
                }
            }
            
            $order->save();
        }
    }
    
    /**
     * Guardar campo de cédula/RIF para WooCommerce Blocks
     * 
     * @param WC_Order $order Pedido
     */
    public function save_cedula_rif_field_blocks($order) {
        error_log('WVP Debug: ===== GUARDAR CÉDULA/RIF BLOCKS =====');
        
        // Debug completo de $_POST
        error_log('WVP Debug: $_POST completo: ' . print_r($_POST, true));
        
        // Obtener datos del request actual usando múltiples fuentes
        $cedula_rif = '';
        
        // 1. Buscar en $_POST
        if (isset($_POST['billing_cedula_rif'])) {
            $cedula_rif = trim($_POST['billing_cedula_rif']);
        } elseif (isset($_POST['cedula_rif'])) {
            $cedula_rif = trim($_POST['cedula_rif']);
        }
        
        // 2. Si no está en $_POST, buscar en el request actual
        if (empty($cedula_rif)) {
            $request = \WP_REST_Request::from_url($_SERVER['REQUEST_URI']);
            if ($request) {
                $data = $request->get_json_params();
                if (isset($data['billing']['cedula_rif'])) {
                    $cedula_rif = trim($data['billing']['cedula_rif']);
                } elseif (isset($data['billing']['billing_cedula_rif'])) {
                    $cedula_rif = trim($data['billing']['billing_cedula_rif']);
                }
            }
        }
        
        // 3. Buscar en los datos del pedido (puede que ya esté guardado)
        if (empty($cedula_rif)) {
            $cedula_rif = $order->get_meta('_billing_cedula_rif');
        }
        
        error_log('WVP Debug: Valor del campo cedula_rif: "' . $cedula_rif . '"');
        
        if (!empty($cedula_rif)) {
            $order->update_meta_data('_billing_cedula_rif', sanitize_text_field($cedula_rif));
            error_log('WVP Debug: Campo guardado en pedido blocks: ' . $order->get_id());
        } else {
            error_log('WVP Debug: Campo vacío - no se guardó en blocks');
        }
        
        error_log('WVP Debug: ===== FIN GUARDAR CÉDULA/RIF BLOCKS =====');
        
        // Guardar tasa BCV al momento de la compra
        $bcv_rate = WVP_BCV_Integrator::get_rate();
        if ($bcv_rate !== null) {
            $order->update_meta_data("_bcv_rate_at_purchase", $bcv_rate);
        }
        
        // Guardar referencia de pago si existe
        $chosen_payment_method = WC()->session->get("chosen_payment_method");
        if ($chosen_payment_method) {
            $payment_reference = $_POST[$chosen_payment_method . '-confirmation'] ?? '';
            if (!empty($payment_reference)) {
                $order->update_meta_data("_payment_reference", sanitize_text_field($payment_reference));
            }
        }
        
        // Guardar monto de IGTF si se aplicó
        if ($this->should_apply_igtf()) {
            $cart_total = WC()->cart->get_total("raw");
            $igtf_rate = $this->get_igtf_rate();
            $igtf_amount = ($cart_total * $igtf_rate) / 100;
            
            if ($igtf_amount > 0) {
                $order->update_meta_data("_igtf_amount", $igtf_amount);
                $order->update_meta_data("_igtf_rate", $igtf_rate);
            }
        }
        
        // Guardar tipo de pago
        if ($chosen_payment_method) {
            $payment_type = $this->get_payment_type($chosen_payment_method);
            if ($payment_type) {
                $order->update_meta_data("_payment_type", $payment_type);
            }
        }
    }
    
    /**
     * Obtener tipo de pago basado en el método seleccionado
     * 
     * @param string $payment_method Método de pago
     * @return string Tipo de pago
     */
    private function get_payment_type($payment_method) {
        $payment_types = array(
            'wvp_zelle' => 'transferencia_digital',
            'wvp_pago_movil' => 'transferencia_digital',
            'wvp_efectivo' => 'efectivo_usd',
            'wvp_efectivo_bolivares' => 'efectivo_bolivares'
        );
        
        return isset($payment_types[$payment_method]) ? $payment_types[$payment_method] : 'other';
    }
    
    /**
     * Mostrar selector de zona de delivery
     */
    public function display_delivery_zone_selector() {
        // Verificar si el método de envío local está activo
        $shipping_methods = WC()->shipping->get_shipping_methods();
        if (!isset($shipping_methods['wvp_local_delivery']) || $shipping_methods['wvp_local_delivery']->enabled !== 'yes') {
            return;
        }
        
        // Verificar si el estado es Distrito Capital o Miranda
        $billing_state = WC()->checkout->get_value('billing_state');
        if (!in_array($billing_state, array('DC', 'Miranda'))) {
            return;
        }
        
        // Obtener zonas configuradas
        $zones = $this->get_delivery_zones();
        if (empty($zones)) {
            return;
        }
        
        $selected_zone = WC()->session->get('wvp_selected_delivery_zone');
        ?>
        <div class="wvp-delivery-zone-selector">
            <h3><?php _e("Zona de Delivery", "wvp"); ?></h3>
            <p class="form-row form-row-wide">
                <label for="wvp_delivery_zone"><?php _e("Selecciona tu zona de delivery:", "wvp"); ?> <span class="required">*</span></label>
                <select name="wvp_delivery_zone" id="wvp_delivery_zone" class="wvp-delivery-zone-select" required>
                    <option value=""><?php _e("Selecciona una zona", "wvp"); ?></option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?php echo esc_attr($zone['name']); ?>" 
                                data-rate="<?php echo esc_attr($zone['rate']); ?>"
                                <?php selected($selected_zone, $zone['name']); ?>>
                            <?php echo esc_html($zone['name']); ?> - $<?php echo number_format($zone['rate'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#wvp_delivery_zone').on('change', function() {
                var selectedZone = $(this).val();
                
                if (selectedZone) {
                    // Guardar en sesión via AJAX
                    $.ajax({
                        url: wc_checkout_params.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wvp_update_delivery_zone',
                            zone: selectedZone,
                            nonce: '<?php echo wp_create_nonce('wvp_delivery_zone_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Actualizar totales del checkout
                                $('body').trigger('update_checkout');
                            }
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Obtener zonas de delivery configuradas
     */
    private function get_delivery_zones() {
        $shipping_methods = WC()->shipping->get_shipping_methods();
        if (isset($shipping_methods['wvp_local_delivery'])) {
            return $shipping_methods['wvp_local_delivery']->get_option('zones', array());
        }
        return array();
    }
    
    /**
     * Guardar zona de delivery seleccionada
     */
    public function save_delivery_zone() {
        if (!empty($_POST['wvp_delivery_zone'])) {
            WC()->session->set('wvp_selected_delivery_zone', sanitize_text_field($_POST['wvp_delivery_zone']));
        }
    }
    
    /**
     * Actualizar zona de delivery via AJAX
     */
    public function update_delivery_zone_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_delivery_zone_nonce')) {
            wp_die('Error de seguridad');
        }
        
        $zone = sanitize_text_field($_POST['zone']);
        WC()->session->set('wvp_selected_delivery_zone', $zone);
        
        wp_send_json_success(array('zone' => $zone));
    }
    
    /**
     * Mostrar campo de cédula/RIF en el admin
     * 
     * @param WC_Order $order Pedido
     */
    public function display_cedula_rif_in_admin($order) {
        $cedula_rif = $order->get_meta('_billing_cedula_rif');
        
        if ($cedula_rif) {
            echo '<p><strong>' . __('Cédula o RIF:', 'wvp') . '</strong> ' . esc_html($cedula_rif) . '</p>';
        }
    }
    
    
    /**
     * Añadir comisión IGTF al carrito
     * 
     * @param WC_Cart $cart Carrito
     */
    public function add_igtf_fee($cart) {
        // Solo añadir IGTF si hay productos en el carrito
        if (is_admin() && !defined("DOING_AJAX")) {
            return;
        }
        
        // Verificar si se debe aplicar IGTF
        if (!$this->should_apply_igtf()) {
            return;
        }
        
        // Obtener total del carrito (incluyendo envío e IVA)
        $cart_total = $cart->get_total("raw");
        
        // Calcular IGTF (3%)
        $igtf_rate = $this->get_igtf_rate();
        $igtf_amount = ($cart_total * $igtf_rate) / 100;
        
        if ($igtf_amount > 0) {
            $cart->add_fee(
                sprintf(__("IGTF (%s%%)", "wvp"), $igtf_rate),
                $igtf_amount,
                false
            );
        }
    }
    
    /**
     * Verificar si se debe aplicar IGTF
     * 
     * @return bool True si se debe aplicar IGTF
     */
    private function should_apply_igtf() {
        // Verificar si el sistema de IGTF está habilitado
        $igtf_enabled = get_option('wvp_igtf_enabled', 'yes') === 'yes';
        if (!$igtf_enabled) {
            error_log('WVP DEBUG: IGTF deshabilitado - wvp_igtf_enabled = ' . get_option('wvp_igtf_enabled', 'not_set'));
            return false;
        }
        
        // Verificar si se debe mostrar IGTF
        $show_igtf = get_option('wvp_show_igtf', '1') === '1';
        if (!$show_igtf) {
            error_log('WVP DEBUG: IGTF no se debe mostrar - wvp_show_igtf = ' . get_option('wvp_show_igtf', 'not_set'));
            return false;
        }
        
        // Verificar si hay una pasarela de pago seleccionada que aplique IGTF
        $chosen_payment_method = WC()->session->get("chosen_payment_method");
        if (empty($chosen_payment_method)) {
            return false;
        }
        
        // IGTF solo se aplica a pagos en efectivo con billetes en dólares
        // NO se aplica a transferencias digitales ni pagos en bolívares
        return $this->gateway_applies_igtf($chosen_payment_method);
    }
    
    /**
     * Verificar si una pasarela de pago aplica IGTF
     * 
     * @param string $gateway_id ID de la pasarela
     * @return bool True si la pasarela aplica IGTF
     */
    private function gateway_applies_igtf($gateway_id) {
        // Obtener configuración de la pasarela
        $gateway_settings = get_option("woocommerce_" . $gateway_id . "_settings", array());
        
        // Verificar si la pasarela tiene la opción de IGTF habilitada
        return isset($gateway_settings["apply_igtf"]) && $gateway_settings["apply_igtf"] === "yes";
    }
    
    /**
     * Obtener tasa de IGTF
     * 
     * @return float Tasa de IGTF
     */
    private function get_igtf_rate() {
        $rate = $this->plugin ? $this->plugin->get_option("igtf_rate") : null;
        if ($rate === null) {
            return 3.0; // Tasa por defecto
        }
        return floatval($rate);
    }
    
    /**
     * Mostrar información de IGTF en el checkout
     */
    public function display_igtf_info() {
        if (!$this->should_apply_igtf()) {
            return;
        }
        
        $igtf_rate = $this->get_igtf_rate();
        $cart_total = WC()->cart->get_total("raw");
        $igtf_amount = ($cart_total * $igtf_rate) / 100;
        
        if ($igtf_amount > 0) {
            ?>
            <div class="wvp-igtf-info">
                <p><strong><?php _e("IGTF aplicado:", "wvp"); ?></strong> <?php echo wc_price($igtf_amount); ?></p>
                <p><em><?php _e("IGTF se aplica solo a pagos en efectivo con billetes en dólares.", "wvp"); ?></em></p>
            </div>
            <?php
        }
    }
    
    /**
     * Mostrar información de pago móvil
     */
    public function display_pago_movil_info() {
        $chosen_payment_method = WC()->session->get("chosen_payment_method");
        
        if ($chosen_payment_method !== "wvp_pago_movil") {
            return;
        }
        
        // Obtener tasa de cambio
        $rate = WVP_BCV_Integrator::get_rate();
        if ($rate === null || $rate <= 0) {
            return;
        }
        
        // Calcular total en bolívares
        $cart_total = WC()->cart->get_total("raw");
        $ves_total = WVP_BCV_Integrator::convert_to_ves($cart_total, $rate);
        
        if ($ves_total === null) {
            return;
        }
        
        $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_total);
        ?>
        <div class="wvp-pago-movil-info">
            <h3><?php _e("Información de Pago Móvil", "wvp"); ?></h3>
            <p><strong><?php _e("Total a pagar:", "wvp"); ?></strong> <?php echo $formatted_ves; ?></p>
            <p><strong><?php _e("Tasa BCV:", "wvp"); ?></strong> <?php echo number_format($rate, 2, ",", "."); ?> Bs./USD</p>
        </div>
        <?php
    }
    
    /**
     * Cargar scripts y estilos del checkout
     */
    public function enqueue_checkout_scripts() {
        if (!is_checkout()) {
            return;
        }
        
        wp_enqueue_style(
            "wvp-checkout",
            WVP_PLUGIN_URL . "assets/css/checkout.css",
            array(),
            WVP_VERSION
        );
        
        wp_enqueue_script(
            "wvp-checkout",
            WVP_PLUGIN_URL . "assets/js/checkout.js",
            array("jquery"),
            WVP_VERSION,
            true
        );
        
        wp_localize_script("wvp-checkout", "wvp_checkout", array(
            "ajax_url" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("wvp_checkout_nonce"),
            "igtf_rate" => $this->get_igtf_rate(),
            "currency_symbol" => get_woocommerce_currency_symbol(),
            "i18n" => array(
                "igtf_applied" => __("IGTF aplicado", "wvp"),
                "igtf_removed" => __("IGTF removido", "wvp"),
                "calculating" => __("Calculando...", "wvp")
            )
        ));
        
        // Asegurar que el script se ejecute
        add_action('wp_footer', function() {
            if (is_checkout()) {
                echo '<script type="text/javascript">console.log("WVP: Script de checkout cargado");</script>';
            }
        });
    }
    
}