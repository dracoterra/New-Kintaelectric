<?php
/**
 * Módulo de Métodos de Envío - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar los métodos de envío locales
 */
class WCVS_Shipping_Methods {

    /**
     * Instancia del plugin
     *
     * @var WCVS_Core
     */
    private $plugin;

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Métodos de envío disponibles
     *
     * @var array
     */
    private $available_methods = array(
        'mrw' => 'MRW',
        'zoom' => 'Zoom',
        'tealca' => 'Tealca',
        'local_delivery' => 'Entrega Local',
        'pickup' => 'Recogida en Tienda'
    );

    /**
     * Estados de Venezuela
     *
     * @var array
     */
    private $venezuelan_states = array(
        'Amazonas' => 'Amazonas',
        'Anzoátegui' => 'Anzoátegui',
        'Apure' => 'Apure',
        'Aragua' => 'Aragua',
        'Barinas' => 'Barinas',
        'Bolívar' => 'Bolívar',
        'Carabobo' => 'Carabobo',
        'Cojedes' => 'Cojedes',
        'Delta Amacuro' => 'Delta Amacuro',
        'Distrito Capital' => 'Distrito Capital',
        'Falcón' => 'Falcón',
        'Guárico' => 'Guárico',
        'Lara' => 'Lara',
        'Mérida' => 'Mérida',
        'Miranda' => 'Miranda',
        'Monagas' => 'Monagas',
        'Nueva Esparta' => 'Nueva Esparta',
        'Portuguesa' => 'Portuguesa',
        'Sucre' => 'Sucre',
        'Táchira' => 'Táchira',
        'Trujillo' => 'Trujillo',
        'Vargas' => 'Vargas',
        'Yaracuy' => 'Yaracuy',
        'Zulia' => 'Zulia'
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Evitar referencia circular
        $this->settings = get_option('wcvs_settings', array());
        
        $this->init_hooks();
        $this->init_tracking_system();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Registrar métodos de envío
        $this->register_shipping_methods();

        // Configurar hooks de WooCommerce
        $this->setup_woocommerce_hooks();

        // Cargar scripts y estilos
        $this->enqueue_assets();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_SHIPPING, 'Módulo Shipping Methods inicializado');
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
        
        // Hook para cálculo de envío
        add_action('woocommerce_shipping_init', array($this, 'init_shipping_methods'));
    }

    /**
     * Inicializar sistema de seguimiento
     */
    private function init_tracking_system() {
        // Cargar clase de seguimiento
        require_once WCVS_PLUGIN_PATH . 'modules/shipping-methods/includes/class-wcvs-shipping-tracking.php';
        
        // Inicializar sistema de seguimiento
        new WCVS_Shipping_Tracking();
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SHIPPING, 'Sistema de seguimiento inicializado');
    }

    /**
     * Registrar métodos de envío
     */
    private function register_shipping_methods() {
        // Registrar cada método si está habilitado
        foreach ($this->available_methods as $method_key => $method_name) {
            $setting_key = $method_key . '_enabled';
            if ($this->settings[$setting_key] ?? false) {
                $this->register_single_method($method_key);
            }
        }
    }

    /**
     * Registrar un método específico
     *
     * @param string $method_key Clave del método
     */
    private function register_single_method($method_key) {
        $method_class = 'WCVS_Shipping_' . ucfirst(str_replace('_', '_', $method_key));
        $method_file = 'methods/class-wcvs-shipping-' . str_replace('_', '-', $method_key) . '.php';
        
        // Cargar archivo del método
        $file_path = WCVS_PLUGIN_PATH . 'modules/shipping-methods/' . $method_file;
        if (file_exists($file_path)) {
            require_once $file_path;
            
            if (class_exists($method_class)) {
                // Registrar el método con WooCommerce
                add_filter('woocommerce_shipping_methods', function($methods) use ($method_class) {
                    $methods[$method_class::METHOD_ID] = $method_class;
                    return $methods;
                });
                
                WCVS_Logger::info(WCVS_Logger::CONTEXT_SHIPPING, "Método de envío {$method_key} registrado correctamente");
            }
        }
    }

    /**
     * Configurar hooks de WooCommerce
     */
    private function setup_woocommerce_hooks() {
        // Hook para añadir métodos de envío
        add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));
        
        // Hook para cálculo de costos
        add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_shipping_fees'));
        
        // Hook para validación de envío
        add_action('woocommerce_checkout_process', array($this, 'validate_shipping_data'));
        
        // Hook para campos adicionales
        add_action('woocommerce_after_shipping_rate', array($this, 'add_shipping_fields'), 10, 2);
        
        // Hook para asignar números de seguimiento
        add_action('woocommerce_order_status_processing', array($this, 'assign_tracking_number'));
        add_action('woocommerce_order_status_shipped', array($this, 'assign_tracking_number'));
    }

    /**
     * Añadir métodos de envío
     *
     * @param array $methods Métodos existentes
     * @return array
     */
    public function add_shipping_methods($methods) {
        // Los métodos se registran individualmente en register_single_method
        return $methods;
    }

    /**
     * Calcular tarifas de envío
     */
    public function calculate_shipping_fees() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        // Calcular tarifas según el método seleccionado
        $chosen_method = WC()->session->get('chosen_shipping_methods');
        if (empty($chosen_method)) {
            return;
        }

        $method_id = $chosen_method[0] ?? '';
        if (strpos($method_id, 'wcvs_') !== 0) {
            return;
        }

        // Calcular tarifas específicas del método
        $this->calculate_method_fees($method_id, $cart);
    }

    /**
     * Calcular tarifas específicas del método
     *
     * @param string $method_id ID del método
     * @param WC_Cart $cart Carrito
     */
    private function calculate_method_fees($method_id, $cart) {
        switch ($method_id) {
            case 'wcvs_mrw':
                $this->calculate_mrw_fees($cart);
                break;
            case 'wcvs_zoom':
                $this->calculate_zoom_fees($cart);
                break;
            case 'wcvs_tealca':
                $this->calculate_tealca_fees($cart);
                break;
            case 'wcvs_local_delivery':
                $this->calculate_local_delivery_fees($cart);
                break;
            case 'wcvs_pickup':
                $this->calculate_pickup_fees($cart);
                break;
        }
    }

    /**
     * Calcular tarifas MRW
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_mrw_fees($cart) {
        $weight = $cart->get_cart_contents_weight();
        $volume = $this->calculate_volume($cart);
        
        // Tarifas por peso
        $weight_rate = $this->get_mrw_weight_rate($weight);
        
        // Tarifas por volumen
        $volume_rate = $this->get_mrw_volume_rate($volume);
        
        // Usar la tarifa más alta
        $rate = max($weight_rate, $volume_rate);
        
        if ($rate > 0) {
            $cart->add_fee('MRW - Envío', $rate);
        }
    }

    /**
     * Calcular tarifas Zoom
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_zoom_fees($cart) {
        $weight = $cart->get_cart_contents_weight();
        $volume = $this->calculate_volume($cart);
        
        // Tarifas por peso
        $weight_rate = $this->get_zoom_weight_rate($weight);
        
        // Tarifas por volumen
        $volume_rate = $this->get_zoom_volume_rate($volume);
        
        // Usar la tarifa más alta
        $rate = max($weight_rate, $volume_rate);
        
        if ($rate > 0) {
            $cart->add_fee('Zoom - Envío', $rate);
        }
    }

    /**
     * Calcular tarifas Tealca
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_tealca_fees($cart) {
        $weight = $cart->get_cart_contents_weight();
        $volume = $this->calculate_volume($cart);
        
        // Tarifas por peso
        $weight_rate = $this->get_tealca_weight_rate($weight);
        
        // Tarifas por volumen
        $volume_rate = $this->get_tealca_volume_rate($volume);
        
        // Usar la tarifa más alta
        $rate = max($weight_rate, $volume_rate);
        
        if ($rate > 0) {
            $cart->add_fee('Tealca - Envío', $rate);
        }
    }

    /**
     * Calcular tarifas Local Delivery
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_local_delivery_fees($cart) {
        $weight = $cart->get_cart_contents_weight();
        $volume = $this->calculate_volume($cart);
        
        // Tarifas por peso
        $weight_rate = $this->get_local_delivery_weight_rate($weight);
        
        // Tarifas por volumen
        $volume_rate = $this->get_local_delivery_volume_rate($volume);
        
        // Usar la tarifa más alta
        $rate = max($weight_rate, $volume_rate);
        
        if ($rate > 0) {
            $cart->add_fee('Entrega Local - Envío', $rate);
        }
    }

    /**
     * Calcular tarifas Pickup
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_pickup_fees($cart) {
        // Pickup es gratis
        $cart->add_fee('Recogida en Tienda', 0);
    }

    /**
     * Calcular volumen del carrito
     *
     * @param WC_Cart $cart Carrito
     * @return float
     */
    private function calculate_volume($cart) {
        $volume = 0;
        
        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            
            $length = $product->get_length() ?: 0;
            $width = $product->get_width() ?: 0;
            $height = $product->get_height() ?: 0;
            
            $item_volume = $length * $width * $height;
            $volume += $item_volume * $quantity;
        }
        
        return $volume;
    }

    /**
     * Obtener tarifa MRW por peso
     *
     * @param float $weight Peso en kg
     * @return float
     */
    private function get_mrw_weight_rate($weight) {
        $rates = $this->settings['mrw_weight_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($weight >= $rate['min'] && $weight <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener tarifa MRW por volumen
     *
     * @param float $volume Volumen en cm³
     * @return float
     */
    private function get_mrw_volume_rate($volume) {
        $rates = $this->settings['mrw_volume_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($volume >= $rate['min'] && $volume <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener tarifa Zoom por peso
     *
     * @param float $weight Peso en kg
     * @return float
     */
    private function get_zoom_weight_rate($weight) {
        $rates = $this->settings['zoom_weight_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($weight >= $rate['min'] && $weight <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener tarifa Zoom por volumen
     *
     * @param float $volume Volumen en cm³
     * @return float
     */
    private function get_zoom_volume_rate($volume) {
        $rates = $this->settings['zoom_volume_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($volume >= $rate['min'] && $volume <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener tarifa Tealca por peso
     *
     * @param float $weight Peso en kg
     * @return float
     */
    private function get_tealca_weight_rate($weight) {
        $rates = $this->settings['tealca_weight_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($weight >= $rate['min'] && $weight <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener tarifa Tealca por volumen
     *
     * @param float $volume Volumen en cm³
     * @return float
     */
    private function get_tealca_volume_rate($volume) {
        $rates = $this->settings['tealca_volume_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($volume >= $rate['min'] && $volume <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener tarifa Local Delivery por peso
     *
     * @param float $weight Peso en kg
     * @return float
     */
    private function get_local_delivery_weight_rate($weight) {
        $rates = $this->settings['local_delivery_weight_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($weight >= $rate['min'] && $weight <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Obtener tarifa Local Delivery por volumen
     *
     * @param float $volume Volumen en cm³
     * @return float
     */
    private function get_local_delivery_volume_rate($volume) {
        $rates = $this->settings['local_delivery_volume_rates'] ?? array();
        
        foreach ($rates as $rate) {
            if ($volume >= $rate['min'] && $volume <= $rate['max']) {
                return $rate['cost'];
            }
        }
        
        return 0;
    }

    /**
     * Validar datos de envío
     */
    public function validate_shipping_data() {
        $shipping_method = WC()->session->get('chosen_shipping_methods');
        
        if (empty($shipping_method)) {
            return;
        }

        $method_id = $shipping_method[0] ?? '';
        if (strpos($method_id, 'wcvs_') !== 0) {
            return;
        }

        // Validar datos específicos según el método
        $this->validate_method_specific_data($method_id);
    }

    /**
     * Validar datos específicos del método
     *
     * @param string $method_id ID del método
     */
    private function validate_method_specific_data($method_id) {
        switch ($method_id) {
            case 'wcvs_mrw':
                $this->validate_mrw_data();
                break;
            case 'wcvs_zoom':
                $this->validate_zoom_data();
                break;
            case 'wcvs_tealca':
                $this->validate_tealca_data();
                break;
            case 'wcvs_local_delivery':
                $this->validate_local_delivery_data();
                break;
            case 'wcvs_pickup':
                $this->validate_pickup_data();
                break;
        }
    }

    /**
     * Validar datos MRW
     */
    private function validate_mrw_data() {
        $state = $_POST['shipping_state'] ?? '';
        
        if (empty($state)) {
            wc_add_notice('Por favor selecciona un estado para el envío MRW.', 'error');
        }
    }

    /**
     * Validar datos Zoom
     */
    private function validate_zoom_data() {
        $state = $_POST['shipping_state'] ?? '';
        
        if (empty($state)) {
            wc_add_notice('Por favor selecciona un estado para el envío Zoom.', 'error');
        }
    }

    /**
     * Validar datos Tealca
     */
    private function validate_tealca_data() {
        $state = $_POST['shipping_state'] ?? '';
        
        if (empty($state)) {
            wc_add_notice('Por favor selecciona un estado para el envío Tealca.', 'error');
        }
    }

    /**
     * Validar datos Local Delivery
     */
    private function validate_local_delivery_data() {
        $state = $_POST['shipping_state'] ?? '';
        $city = $_POST['shipping_city'] ?? '';
        
        if (empty($state)) {
            wc_add_notice('Por favor selecciona un estado para la entrega local.', 'error');
        }
        
        if (empty($city)) {
            wc_add_notice('Por favor ingresa una ciudad para la entrega local.', 'error');
        }
    }

    /**
     * Validar datos Pickup
     */
    private function validate_pickup_data() {
        // Pickup no requiere validación adicional
    }

    /**
     * Añadir campos adicionales a métodos de envío
     *
     * @param WC_Shipping_Rate $method Método de envío
     * @param int $index Índice del método
     */
    public function add_shipping_fields($method, $index) {
        $method_id = $method->get_method_id();
        
        if (strpos($method_id, 'wcvs_') !== 0) {
            return;
        }

        echo '<div class="wcvs-shipping-fields" id="wcvs-shipping-fields-' . esc_attr($method_id) . '">';
        
        switch ($method_id) {
            case 'wcvs_mrw':
                $this->render_mrw_fields();
                break;
            case 'wcvs_zoom':
                $this->render_zoom_fields();
                break;
            case 'wcvs_tealca':
                $this->render_tealca_fields();
                break;
            case 'wcvs_local_delivery':
                $this->render_local_delivery_fields();
                break;
            case 'wcvs_pickup':
                $this->render_pickup_fields();
                break;
        }
        
        echo '</div>';
    }

    /**
     * Renderizar campos MRW
     */
    private function render_mrw_fields() {
        ?>
        <div class="wcvs-mrw-fields">
            <h4>Información de Envío MRW</h4>
            <p class="form-row form-row-wide">
                <label for="wcvs_mrw_state">Estado <span class="required">*</span></label>
                <select name="wcvs_mrw_state" id="wcvs_mrw_state" required>
                    <option value="">Selecciona un estado</option>
                    <?php foreach ($this->venezuelan_states as $key => $value): ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="wcvs-shipping-info">
                <strong>Instrucciones:</strong> MRW entregará tu pedido en el estado seleccionado.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos Zoom
     */
    private function render_zoom_fields() {
        ?>
        <div class="wcvs-zoom-fields">
            <h4>Información de Envío Zoom</h4>
            <p class="form-row form-row-wide">
                <label for="wcvs_zoom_state">Estado <span class="required">*</span></label>
                <select name="wcvs_zoom_state" id="wcvs_zoom_state" required>
                    <option value="">Selecciona un estado</option>
                    <?php foreach ($this->venezuelan_states as $key => $value): ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="wcvs-shipping-info">
                <strong>Instrucciones:</strong> Zoom entregará tu pedido en el estado seleccionado.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos Tealca
     */
    private function render_tealca_fields() {
        ?>
        <div class="wcvs-tealca-fields">
            <h4>Información de Envío Tealca</h4>
            <p class="form-row form-row-wide">
                <label for="wcvs_tealca_state">Estado <span class="required">*</span></label>
                <select name="wcvs_tealca_state" id="wcvs_tealca_state" required>
                    <option value="">Selecciona un estado</option>
                    <?php foreach ($this->venezuelan_states as $key => $value): ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="wcvs-shipping-info">
                <strong>Instrucciones:</strong> Tealca entregará tu pedido en el estado seleccionado.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos Local Delivery
     */
    private function render_local_delivery_fields() {
        ?>
        <div class="wcvs-local-delivery-fields">
            <h4>Información de Entrega Local</h4>
            <p class="form-row form-row-wide">
                <label for="wcvs_local_delivery_state">Estado <span class="required">*</span></label>
                <select name="wcvs_local_delivery_state" id="wcvs_local_delivery_state" required>
                    <option value="">Selecciona un estado</option>
                    <?php foreach ($this->venezuelan_states as $key => $value): ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="form-row form-row-wide">
                <label for="wcvs_local_delivery_city">Ciudad <span class="required">*</span></label>
                <input type="text" class="input-text" name="wcvs_local_delivery_city" id="wcvs_local_delivery_city" required>
            </p>
            <p class="wcvs-shipping-info">
                <strong>Instrucciones:</strong> Realizaremos la entrega local en la ciudad especificada.
            </p>
        </div>
        <?php
    }

    /**
     * Renderizar campos Pickup
     */
    private function render_pickup_fields() {
        ?>
        <div class="wcvs-pickup-fields">
            <h4>Información de Recogida</h4>
            <p class="wcvs-shipping-info">
                <strong>Instrucciones:</strong> Puedes recoger tu pedido en nuestra tienda física.
            </p>
        </div>
        <?php
    }

    /**
     * Cargar assets (CSS y JS)
     */
    private function enqueue_assets() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_shipping_assets'));
    }

    /**
     * Encolar assets de envío
     */
    public function enqueue_shipping_assets() {
        if (is_checkout()) {
            wp_enqueue_style(
                'wcvs-shipping-methods',
                WCVS_PLUGIN_URL . 'modules/shipping-methods/css/wcvs-shipping-methods.css',
                array(),
                WCVS_VERSION
            );

            wp_enqueue_script(
                'wcvs-shipping-methods',
                WCVS_PLUGIN_URL . 'modules/shipping-methods/js/wcvs-shipping-methods.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-shipping-methods', 'wcvs_shipping_methods', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_shipping_methods_nonce'),
                'strings' => array(
                    'loading' => 'Calculando envío...',
                    'error' => 'Error al calcular envío',
                    'success' => 'Envío calculado correctamente'
                )
            ));
        }
    }

    /**
     * Manejar activación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'shipping_methods') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_SHIPPING, 'Módulo Shipping Methods activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'shipping_methods') {
            // Limpiar hooks
            remove_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));
            remove_action('woocommerce_cart_calculate_fees', array($this, 'calculate_shipping_fees'));
            
            WCVS_Logger::info(WCVS_Logger::CONTEXT_SHIPPING, 'Módulo Shipping Methods desactivado');
        }
    }

    /**
     * Asignar número de seguimiento
     *
     * @param int $order_id ID del pedido
     */
    public function assign_tracking_number($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Verificar si ya tiene número de seguimiento
        $existing_tracking = $order->get_meta('_tracking_number');
        if (!empty($existing_tracking)) {
            return;
        }

        // Obtener método de envío
        $shipping_methods = $order->get_shipping_methods();
        if (empty($shipping_methods)) {
            return;
        }

        $shipping_method = reset($shipping_methods);
        $method_id = $shipping_method->get_method_id();

        // Determinar el método de envío
        $method_key = $this->get_method_key_from_id($method_id);
        if (!$method_key) {
            return;
        }

        // Generar número de seguimiento
        $tracking_system = new WCVS_Shipping_Tracking();
        $tracking_number = $tracking_system->generate_tracking_number($order_id, $method_key);

        // Guardar número de seguimiento
        $order->update_meta_data('_tracking_number', $tracking_number);
        $order->update_meta_data('_shipment_status', 'Recogido');
        $order->update_meta_data('_shipment_last_update', current_time('mysql'));
        $order->save();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_SHIPPING, "Número de seguimiento asignado: {$tracking_number} para pedido #{$order_id}");
    }

    /**
     * Obtener clave del método desde ID
     *
     * @param string $method_id ID del método
     * @return string|null
     */
    private function get_method_key_from_id($method_id) {
        $method_map = array(
            'wcvs_mrw' => 'mrw',
            'wcvs_zoom' => 'zoom',
            'wcvs_tealca' => 'tealca',
            'wcvs_local_delivery' => 'local_delivery',
            'wcvs_pickup' => 'pickup'
        );

        return $method_map[$method_id] ?? null;
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        $enabled_methods = 0;
        foreach ($this->available_methods as $method_key => $method_name) {
            $setting_key = $method_key . '_enabled';
            if ($this->settings[$setting_key] ?? false) {
                $enabled_methods++;
            }
        }

        return array(
            'total_methods' => count($this->available_methods),
            'enabled_methods' => $enabled_methods,
            'available_methods' => $this->available_methods,
            'venezuelan_states' => $this->venezuelan_states,
            'settings' => $this->settings
        );
    }
}
