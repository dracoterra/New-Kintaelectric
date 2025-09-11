<?php
/**
 * Validador de lógica de negocio
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Business_Validator {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Configuración de validaciones
     * 
     * @var array
     */
    private $config;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->load_config();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Validaciones de checkout
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout'));
        
        // Validaciones de productos
        add_action('woocommerce_add_to_cart_validation', array($this, 'validate_add_to_cart'), 10, 3);
        
        // Validaciones de pedidos
        add_action('woocommerce_checkout_order_processed', array($this, 'validate_order'), 10, 2);
        
        // Validaciones de pago
        add_action('woocommerce_payment_complete', array($this, 'validate_payment'), 10, 1);
    }
    
    /**
     * Cargar configuración de validaciones
     */
    private function load_config() {
        $this->config = array(
            'min_order_amount' => floatval(get_option('wvp_min_order_amount', 0)),
            'max_order_amount' => floatval(get_option('wvp_max_order_amount', 0)),
            'min_product_quantity' => intval(get_option('wvp_min_product_quantity', 1)),
            'max_product_quantity' => intval(get_option('wvp_max_product_quantity', 0)),
            'require_cedula_rif' => get_option('wvp_require_cedula_rif', 'yes'),
            'validate_phone' => get_option('wvp_validate_phone', 'yes'),
            'validate_email' => get_option('wvp_validate_email', 'yes'),
            'validate_address' => get_option('wvp_validate_address', 'yes'),
            'check_inventory' => get_option('wvp_check_inventory', 'yes'),
            'check_payment_methods' => get_option('wvp_check_payment_methods', 'yes'),
            'validate_igtf' => get_option('wvp_validate_igtf', 'yes'),
            'validate_bcv_rate' => get_option('wvp_validate_bcv_rate', 'yes')
        );
    }
    
    /**
     * Validar checkout completo
     */
    public function validate_checkout() {
        $errors = array();
        
        // Validar monto mínimo del pedido
        if ($this->config['min_order_amount'] > 0) {
            $cart_total = WC()->cart->get_total('raw');
            if ($cart_total < $this->config['min_order_amount']) {
                $errors[] = sprintf(
                    __('El monto mínimo del pedido es $%s', 'wvp'),
                    number_format($this->config['min_order_amount'], 2)
                );
            }
        }
        
        // Validar monto máximo del pedido
        if ($this->config['max_order_amount'] > 0) {
            $cart_total = WC()->cart->get_total('raw');
            if ($cart_total > $this->config['max_order_amount']) {
                $errors[] = sprintf(
                    __('El monto máximo del pedido es $%s', 'wvp'),
                    number_format($this->config['max_order_amount'], 2)
                );
            }
        }
        
        // Validar cédula/RIF
        if ($this->config['require_cedula_rif'] === 'yes') {
            $cedula_rif = $_POST['billing_cedula_rif'] ?? '';
            if (empty($cedula_rif)) {
                $errors[] = __('La cédula o RIF es obligatoria', 'wvp');
            } elseif (!$this->validate_cedula_rif($cedula_rif)) {
                $errors[] = __('La cédula o RIF no tiene un formato válido', 'wvp');
            }
        }
        
        // Validar teléfono
        if ($this->config['validate_phone'] === 'yes') {
            $phone = $_POST['billing_phone'] ?? '';
            if (!empty($phone) && !$this->validate_venezuelan_phone($phone)) {
                $errors[] = __('El teléfono no tiene un formato válido para Venezuela', 'wvp');
            }
        }
        
        // Validar email
        if ($this->config['validate_email'] === 'yes') {
            $email = $_POST['billing_email'] ?? '';
            if (!empty($email) && !is_email($email)) {
                $errors[] = __('El email no tiene un formato válido', 'wvp');
            }
        }
        
        // Validar dirección
        if ($this->config['validate_address'] === 'yes') {
            $address = $_POST['billing_address_1'] ?? '';
            if (empty($address)) {
                $errors[] = __('La dirección es obligatoria', 'wvp');
            }
        }
        
        // Validar inventario
        if ($this->config['check_inventory'] === 'yes') {
            $inventory_errors = $this->validate_inventory();
            $errors = array_merge($errors, $inventory_errors);
        }
        
        // Validar métodos de pago
        if ($this->config['check_payment_methods'] === 'yes') {
            $payment_errors = $this->validate_payment_methods();
            $errors = array_merge($errors, $payment_errors);
        }
        
        // Validar IGTF
        if ($this->config['validate_igtf'] === 'yes') {
            $igtf_errors = $this->validate_igtf();
            $errors = array_merge($errors, $igtf_errors);
        }
        
        // Validar tasa BCV
        if ($this->config['validate_bcv_rate'] === 'yes') {
            $bcv_errors = $this->validate_bcv_rate();
            $errors = array_merge($errors, $bcv_errors);
        }
        
        // Mostrar errores
        foreach ($errors as $error) {
            wc_add_notice($error, 'error');
        }
    }
    
    /**
     * Validar añadir al carrito
     */
    public function validate_add_to_cart($passed, $product_id, $quantity) {
        if (!$passed) {
            return $passed;
        }
        
        // Validar cantidad mínima
        if ($this->config['min_product_quantity'] > 0 && $quantity < $this->config['min_product_quantity']) {
            wc_add_notice(sprintf(
                __('La cantidad mínima para este producto es %d', 'wvp'),
                $this->config['min_product_quantity']
            ), 'error');
            return false;
        }
        
        // Validar cantidad máxima
        if ($this->config['max_product_quantity'] > 0 && $quantity > $this->config['max_product_quantity']) {
            wc_add_notice(sprintf(
                __('La cantidad máxima para este producto es %d', 'wvp'),
                $this->config['max_product_quantity']
            ), 'error');
            return false;
        }
        
        // Validar inventario
        if ($this->config['check_inventory'] === 'yes') {
            $product = wc_get_product($product_id);
            if ($product && !$product->is_in_stock()) {
                wc_add_notice(__('Este producto no está disponible', 'wvp'), 'error');
                return false;
            }
        }
        
        return $passed;
    }
    
    /**
     * Validar pedido
     */
    public function validate_order($order_id, $posted_data) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Validar monto del pedido
        $order_total = $order->get_total('raw');
        
        if ($this->config['min_order_amount'] > 0 && $order_total < $this->config['min_order_amount']) {
            $order->add_order_note(__('Pedido rechazado: Monto mínimo no alcanzado', 'wvp'));
            $order->update_status('cancelled');
            return;
        }
        
        if ($this->config['max_order_amount'] > 0 && $order_total > $this->config['max_order_amount']) {
            $order->add_order_note(__('Pedido rechazado: Monto máximo excedido', 'wvp'));
            $order->update_status('cancelled');
            return;
        }
        
        // Validar datos del cliente
        $this->validate_customer_data($order);
        
        // Validar productos
        $this->validate_order_products($order);
    }
    
    /**
     * Validar pago
     */
    public function validate_payment($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Validar método de pago
        $payment_method = $order->get_payment_method();
        if (!$this->is_valid_payment_method($payment_method)) {
            $order->add_order_note(__('Método de pago no válido', 'wvp'));
            $order->update_status('cancelled');
            return;
        }
        
        // Validar confirmación de pago
        if (in_array($payment_method, ['wvp_zelle', 'wvp_pago_movil'])) {
            $confirmation = $order->get_meta('_payment_reference');
            if (empty($confirmation)) {
                $order->add_order_note(__('Falta confirmación de pago', 'wvp'));
                $order->update_status('pending');
                return;
            }
        }
    }
    
    /**
     * Validar cédula/RIF
     */
    private function validate_cedula_rif($cedula_rif) {
        // Patrón para cédula/RIF venezolano
        $pattern = '/^[VEJPG]-?\d{7,9}$/i';
        return preg_match($pattern, $cedula_rif);
    }
    
    /**
     * Validar teléfono venezolano
     */
    private function validate_venezuelan_phone($phone) {
        // Patrón para teléfono venezolano
        $pattern = '/^(\+58|0)?[0-9]{10}$/';
        return preg_match($pattern, $phone);
    }
    
    /**
     * Validar inventario
     */
    private function validate_inventory() {
        $errors = array();
        
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            
            if (!$product->is_in_stock()) {
                $errors[] = sprintf(
                    __('El producto "%s" no está disponible', 'wvp'),
                    $product->get_name()
                );
            } elseif ($product->get_stock_quantity() < $quantity) {
                $errors[] = sprintf(
                    __('Solo hay %d unidades disponibles de "%s"', 'wvp'),
                    $product->get_stock_quantity(),
                    $product->get_name()
                );
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar métodos de pago
     */
    private function validate_payment_methods() {
        $errors = array();
        
        $payment_method = WC()->session->get('chosen_payment_method');
        if (!$payment_method) {
            $errors[] = __('Debe seleccionar un método de pago', 'wvp');
        } elseif (!$this->is_valid_payment_method($payment_method)) {
            $errors[] = __('El método de pago seleccionado no está disponible', 'wvp');
        }
        
        return $errors;
    }
    
    /**
     * Validar IGTF
     */
    private function validate_igtf() {
        $errors = array();
        
        if (class_exists('WVP_IGTF_Manager')) {
            $igtf_manager = new WVP_IGTF_Manager();
            $payment_method = WC()->session->get('chosen_payment_method');
            
            if ($igtf_manager->applies_to_payment_method($payment_method)) {
                $cart_total = WC()->cart->get_total('raw');
                $igtf_amount = $igtf_manager->calculate_igtf($cart_total, $payment_method);
                
                if ($igtf_amount > 0) {
                    // Verificar que el IGTF se puede pagar
                    $total_with_igtf = $cart_total + $igtf_amount;
                    if ($total_with_igtf > $cart_total * 1.5) { // Límite del 50% adicional
                        $errors[] = __('El IGTF calculado es excesivo', 'wvp');
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar tasa BCV
     */
    private function validate_bcv_rate() {
        $errors = array();
        
        if (class_exists('WVP_BCV_Integrator')) {
            $rate = WVP_BCV_Integrator::get_rate();
            
            if (!$rate || $rate <= 0) {
                $errors[] = __('No se puede obtener la tasa de cambio del BCV', 'wvp');
            } elseif ($rate < 1) {
                $errors[] = __('La tasa de cambio del BCV parece incorrecta', 'wvp');
            } elseif ($rate > 1000000) {
                $errors[] = __('La tasa de cambio del BCV es demasiado alta', 'wvp');
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar datos del cliente
     */
    private function validate_customer_data($order) {
        $billing_email = $order->get_billing_email();
        if (!empty($billing_email) && !is_email($billing_email)) {
            $order->add_order_note(__('Email de facturación inválido', 'wvp'));
        }
        
        $billing_phone = $order->get_billing_phone();
        if (!empty($billing_phone) && !$this->validate_venezuelan_phone($billing_phone)) {
            $order->add_order_note(__('Teléfono de facturación inválido', 'wvp'));
        }
        
        $cedula_rif = $order->get_meta('_billing_cedula_rif');
        if (!empty($cedula_rif) && !$this->validate_cedula_rif($cedula_rif)) {
            $order->add_order_note(__('Cédula/RIF inválido', 'wvp'));
        }
    }
    
    /**
     * Validar productos del pedido
     */
    private function validate_order_products($order) {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }
            
            if (!$product->is_in_stock()) {
                $order->add_order_note(sprintf(
                    __('Producto "%s" no está disponible', 'wvp'),
                    $product->get_name()
                ));
            }
        }
    }
    
    /**
     * Verificar si un método de pago es válido
     */
    private function is_valid_payment_method($payment_method) {
        $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
        return isset($available_gateways[$payment_method]);
    }
    
    /**
     * Obtener configuración actual
     */
    public function get_config() {
        return $this->config;
    }
    
    /**
     * Actualizar configuración
     */
    public function update_config($new_config) {
        foreach ($new_config as $key => $value) {
            if (array_key_exists($key, $this->config)) {
                update_option('wvp_' . $key, $value);
                $this->config[$key] = $value;
            }
        }
    }
}
