<?php
/**
 * Gestor de IGTF (Impuesto a las Grandes Transacciones Financieras)
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_IGTF_Manager {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Configuración de IGTF
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
        // Hook para aplicar IGTF en el carrito
        add_action('woocommerce_cart_calculate_fees', array($this, 'apply_igtf_fee'));
        
        // Hook para mostrar información de IGTF
        add_action('woocommerce_review_order_before_payment', array($this, 'display_igtf_info'));
        
        // Hook para guardar configuración de IGTF
        add_action('admin_init', array($this, 'register_igtf_settings'));
        
        // Hook para mostrar configuración en admin
        add_action('woocommerce_settings_tabs_wvp', array($this, 'display_igtf_settings'));
    }
    
    /**
     * Cargar configuración de IGTF
     */
    private function load_config() {
        $this->config = array(
            'enabled' => get_option('wvp_igtf_enabled', 'yes'),
            'rate' => floatval(get_option('wvp_igtf_rate', 3.0)),
            'min_amount' => floatval(get_option('wvp_igtf_min_amount', 0)),
            'max_amount' => floatval(get_option('wvp_igtf_max_amount', 0)),
            'payment_methods' => get_option('wvp_igtf_payment_methods', array('wvp_efectivo')),
            'exempt_products' => get_option('wvp_igtf_exempt_products', array()),
            'exempt_categories' => get_option('wvp_igtf_exempt_categories', array()),
            'exempt_users' => get_option('wvp_igtf_exempt_users', array()),
            'description' => get_option('wvp_igtf_description', 'IGTF (Impuesto a las Grandes Transacciones Financieras)'),
            'calculation_method' => get_option('wvp_igtf_calculation_method', 'percentage'),
            'rounding_method' => get_option('wvp_igtf_rounding_method', 'round')
        );
    }
    
    /**
     * Verificar si IGTF está habilitado
     */
    public function is_enabled() {
        return $this->config['enabled'] === 'yes';
    }
    
    /**
     * Verificar si un método de pago aplica IGTF
     */
    public function applies_to_payment_method($payment_method) {
        if (!$this->is_enabled()) {
            return false;
        }
        
        return in_array($payment_method, $this->config['payment_methods']);
    }
    
    /**
     * Verificar si un producto está exento de IGTF
     */
    public function is_product_exempt($product_id) {
        if (!$this->is_enabled()) {
            return true;
        }
        
        // Verificar exención por producto
        if (in_array($product_id, $this->config['exempt_products'])) {
            return true;
        }
        
        // Verificar exención por categoría
        $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
        if (!empty($product_categories)) {
            foreach ($product_categories as $category_id) {
                if (in_array($category_id, $this->config['exempt_categories'])) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Verificar si un usuario está exento de IGTF
     */
    public function is_user_exempt($user_id = null) {
        if (!$this->is_enabled()) {
            return true;
        }
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        return in_array($user_id, $this->config['exempt_users']);
    }
    
    /**
     * Calcular IGTF para un monto
     */
    public function calculate_igtf($amount, $payment_method = null) {
        if (!$this->is_enabled()) {
            return 0;
        }
        
        // Verificar si el método de pago aplica IGTF
        if ($payment_method && !$this->applies_to_payment_method($payment_method)) {
            return 0;
        }
        
        // Verificar exención del usuario
        if ($this->is_user_exempt()) {
            return 0;
        }
        
        // Verificar límites de monto
        if ($this->config['min_amount'] > 0 && $amount < $this->config['min_amount']) {
            return 0;
        }
        
        if ($this->config['max_amount'] > 0 && $amount > $this->config['max_amount']) {
            return 0;
        }
        
        // Calcular IGTF
        $igtf_amount = 0;
        
        if ($this->config['calculation_method'] === 'percentage') {
            $igtf_amount = ($amount * $this->config['rate']) / 100;
        } elseif ($this->config['calculation_method'] === 'fixed') {
            $igtf_amount = $this->config['rate'];
        }
        
        // Aplicar redondeo
        if ($this->config['rounding_method'] === 'round') {
            $igtf_amount = round($igtf_amount, 2);
        } elseif ($this->config['rounding_method'] === 'ceil') {
            $igtf_amount = ceil($igtf_amount * 100) / 100;
        } elseif ($this->config['rounding_method'] === 'floor') {
            $igtf_amount = floor($igtf_amount * 100) / 100;
        }
        
        return max(0, $igtf_amount);
    }
    
    /**
     * Aplicar IGTF como fee en el carrito
     */
    public function apply_igtf_fee() {
        if (!$this->is_enabled()) {
            return;
        }
        
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        // Obtener método de pago seleccionado
        $payment_method = WC()->session->get('chosen_payment_method');
        if (!$payment_method || !$this->applies_to_payment_method($payment_method)) {
            return;
        }
        
        // Verificar exención del usuario
        if ($this->is_user_exempt()) {
            return;
        }
        
        // Calcular total del carrito sin IGTF
        $cart_total = $cart->get_subtotal() + $cart->get_shipping_total() + $cart->get_tax_total();
        
        // Verificar si hay productos exentos
        $exempt_amount = 0;
        foreach ($cart->get_cart() as $cart_item) {
            if ($this->is_product_exempt($cart_item['product_id'])) {
                $exempt_amount += $cart_item['line_total'];
            }
        }
        
        $taxable_amount = $cart_total - $exempt_amount;
        
        if ($taxable_amount <= 0) {
            return;
        }
        
        // Calcular IGTF
        $igtf_amount = $this->calculate_igtf($taxable_amount, $payment_method);
        
        if ($igtf_amount > 0) {
            $cart->add_fee(
                $this->config['description'],
                $igtf_amount,
                true // Taxable
            );
        }
    }
    
    /**
     * Mostrar información de IGTF en checkout
     */
    public function display_igtf_info() {
        if (!$this->is_enabled()) {
            return;
        }
        
        $payment_method = WC()->session->get('chosen_payment_method');
        if (!$payment_method || !$this->applies_to_payment_method($payment_method)) {
            return;
        }
        
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $cart_total = $cart->get_subtotal() + $cart->get_shipping_total() + $cart->get_tax_total();
        $igtf_amount = $this->calculate_igtf($cart_total, $payment_method);
        
        if ($igtf_amount > 0) {
            echo '<div class="wvp-igtf-info">';
            echo '<h4>' . __('Información de IGTF', 'wvp') . '</h4>';
            echo '<p>' . sprintf(
                __('Se aplicará IGTF del %s%% sobre el monto de $%s', 'wvp'),
                number_format($this->config['rate'], 2),
                number_format($cart_total, 2)
            ) . '</p>';
            echo '<p><strong>' . sprintf(
                __('IGTF a pagar: $%s', 'wvp'),
                number_format($igtf_amount, 2)
            ) . '</strong></p>';
            echo '</div>';
        }
    }
    
    /**
     * Registrar configuraciones de IGTF
     */
    public function register_igtf_settings() {
        // Registrar opciones
        register_setting('wvp_igtf_settings', 'wvp_igtf_enabled');
        register_setting('wvp_igtf_settings', 'wvp_igtf_rate');
        register_setting('wvp_igtf_settings', 'wvp_igtf_min_amount');
        register_setting('wvp_igtf_settings', 'wvp_igtf_max_amount');
        register_setting('wvp_igtf_settings', 'wvp_igtf_payment_methods');
        register_setting('wvp_igtf_settings', 'wvp_igtf_exempt_products');
        register_setting('wvp_igtf_settings', 'wvp_igtf_exempt_categories');
        register_setting('wvp_igtf_settings', 'wvp_igtf_exempt_users');
        register_setting('wvp_igtf_settings', 'wvp_igtf_description');
        register_setting('wvp_igtf_settings', 'wvp_igtf_calculation_method');
        register_setting('wvp_igtf_settings', 'wvp_igtf_rounding_method');
    }
    
    /**
     * Mostrar configuraciones de IGTF en admin
     */
    public function display_igtf_settings() {
        ?>
        <div class="wvp-igtf-settings">
            <h3><?php _e('Configuración de IGTF', 'wvp'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Habilitar IGTF', 'wvp'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wvp_igtf_enabled" value="yes" <?php checked($this->config['enabled'], 'yes'); ?>>
                            <?php _e('Aplicar IGTF a las transacciones', 'wvp'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Tasa de IGTF (%)', 'wvp'); ?></th>
                    <td>
                        <input type="number" name="wvp_igtf_rate" value="<?php echo esc_attr($this->config['rate']); ?>" step="0.01" min="0" max="100">
                        <p class="description"><?php _e('Porcentaje de IGTF a aplicar', 'wvp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Monto Mínimo', 'wvp'); ?></th>
                    <td>
                        <input type="number" name="wvp_igtf_min_amount" value="<?php echo esc_attr($this->config['min_amount']); ?>" step="0.01" min="0">
                        <p class="description"><?php _e('Monto mínimo para aplicar IGTF (0 = sin límite)', 'wvp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Monto Máximo', 'wvp'); ?></th>
                    <td>
                        <input type="number" name="wvp_igtf_max_amount" value="<?php echo esc_attr($this->config['max_amount']); ?>" step="0.01" min="0">
                        <p class="description"><?php _e('Monto máximo para aplicar IGTF (0 = sin límite)', 'wvp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Métodos de Pago', 'wvp'); ?></th>
                    <td>
                        <?php
                        $payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();
                        foreach ($payment_gateways as $gateway) {
                            $checked = in_array($gateway->id, $this->config['payment_methods']) ? 'checked' : '';
                            echo '<label><input type="checkbox" name="wvp_igtf_payment_methods[]" value="' . esc_attr($gateway->id) . '" ' . $checked . '> ' . esc_html($gateway->get_title()) . '</label><br>';
                        }
                        ?>
                        <p class="description"><?php _e('Seleccione los métodos de pago que aplican IGTF', 'wvp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Método de Cálculo', 'wvp'); ?></th>
                    <td>
                        <select name="wvp_igtf_calculation_method">
                            <option value="percentage" <?php selected($this->config['calculation_method'], 'percentage'); ?>><?php _e('Porcentaje', 'wvp'); ?></option>
                            <option value="fixed" <?php selected($this->config['calculation_method'], 'fixed'); ?>><?php _e('Monto Fijo', 'wvp'); ?></option>
                        </select>
                        <p class="description"><?php _e('Método para calcular el IGTF', 'wvp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Método de Redondeo', 'wvp'); ?></th>
                    <td>
                        <select name="wvp_igtf_rounding_method">
                            <option value="round" <?php selected($this->config['rounding_method'], 'round'); ?>><?php _e('Redondear', 'wvp'); ?></option>
                            <option value="ceil" <?php selected($this->config['rounding_method'], 'ceil'); ?>><?php _e('Redondear hacia arriba', 'wvp'); ?></option>
                            <option value="floor" <?php selected($this->config['rounding_method'], 'floor'); ?>><?php _e('Redondear hacia abajo', 'wvp'); ?></option>
                        </select>
                        <p class="description"><?php _e('Método para redondear el IGTF', 'wvp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Descripción', 'wvp'); ?></th>
                    <td>
                        <input type="text" name="wvp_igtf_description" value="<?php echo esc_attr($this->config['description']); ?>" class="regular-text">
                        <p class="description"><?php _e('Descripción que aparecerá en el carrito y checkout', 'wvp'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
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
                update_option('wvp_igtf_' . $key, $value);
                $this->config[$key] = $value;
            }
        }
    }
}
