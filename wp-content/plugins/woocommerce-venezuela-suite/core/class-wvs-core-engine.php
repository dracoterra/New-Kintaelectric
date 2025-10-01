<?php
/**
 * Core Engine - Motor principal del plugin
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase principal del Core Engine
 */
class WVS_Core_Engine {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private $plugin;
    
    /**
     * Configuración del core
     * 
     * @var array
     */
    private $config;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Suite::get_instance();
        $this->config = get_option('wvs_settings', array());
        
        // Inicializar hooks
        $this->init_hooks();
        
        // Inicializar componentes core
        $this->init_core_components();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Hooks de inicialización
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hooks de WooCommerce
        add_action('woocommerce_init', array($this, 'init_woocommerce'));
        
        // Hooks de limpieza
        add_action('wp_loaded', array($this, 'cleanup'));
    }
    
    /**
     * Inicializar componentes core
     */
    private function init_core_components() {
        // Inicializar logger
        if (class_exists('WVS_Logger')) {
            WVS_Logger::get_instance();
        }
        
        // Inicializar seguridad
        if (class_exists('WVS_Security')) {
            new WVS_Security();
        }
        
        // Inicializar performance
        if (class_exists('WVS_Performance')) {
            new WVS_Performance();
        }
        
        // Inicializar base de datos
        if (class_exists('WVS_Database')) {
            new WVS_Database();
        }
    }
    
    /**
     * Inicialización principal
     */
    public function init() {
        // Inicializar componentes adicionales
        $this->init_additional_components();
    }
    
    /**
     * Inicializar componentes adicionales
     */
    private function init_additional_components() {
        // Solo inicializar si los módulos están activos
        $active_modules = get_option('wvs_activated_modules', array());
        
        if (in_array('currency', $active_modules)) {
            $this->init_currency_system();
        }
        
        if (in_array('payments', $active_modules)) {
            $this->init_payment_system();
        }
        
        if (in_array('shipping', $active_modules)) {
            $this->init_shipping_system();
        }
    }
    
    /**
     * Inicializar sistema de moneda
     */
    private function init_currency_system() {
        // Verificar si BCV Dólar Tracker está activo
        if (is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
            // Integrar con BCV Dólar Tracker
            add_action('wp_loaded', array($this, 'init_bcv_integration'));
        }
    }
    
    /**
     * Inicializar integración BCV
     */
    public function init_bcv_integration() {
        // Hook para obtener tasa BCV
        add_filter('wvs_get_bcv_rate', array($this, 'get_bcv_rate'));
        add_filter('wvs_convert_usd_to_ves', array($this, 'convert_usd_to_ves'), 10, 2);
    }
    
    /**
     * Obtener tasa BCV
     * 
     * @return float|null
     */
    public function get_bcv_rate() {
        global $wpdb;
        
        // Verificar si la tabla BCV existe
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            return null;
        }
        
        // Obtener el último precio
        $latest_price = $wpdb->get_var(
            "SELECT precio FROM $table_name ORDER BY datatime DESC LIMIT 1"
        );
        
        return $latest_price ? floatval($latest_price) : null;
    }
    
    /**
     * Convertir USD a VES
     * 
     * @param float $usd_amount
     * @param float $rate
     * @return float|null
     */
    public function convert_usd_to_ves($usd_amount, $rate = null) {
        if ($rate === null) {
            $rate = $this->get_bcv_rate();
        }
        
        if ($rate === null || $rate <= 0) {
            return null;
        }
        
        return $usd_amount * $rate;
    }
    
    /**
     * Inicializar sistema de pagos
     */
    private function init_payment_system() {
        // Registrar métodos de pago
        add_filter('woocommerce_payment_gateways', array($this, 'register_payment_gateways'));
    }
    
    /**
     * Registrar métodos de pago
     * 
     * @param array $gateways
     * @return array
     */
    public function register_payment_gateways($gateways) {
        // Solo cargar gateways si están activos
        $active_modules = get_option('wvs_activated_modules', array());
        
        if (in_array('payments', $active_modules)) {
            // Cargar clases de gateway dinámicamente
            $gateway_classes = $this->get_available_gateways();
            
            foreach ($gateway_classes as $gateway_class) {
                if (class_exists($gateway_class)) {
                    $gateways[] = $gateway_class;
                }
            }
        }
        
        return $gateways;
    }
    
    /**
     * Obtener gateways disponibles
     * 
     * @return array
     */
    private function get_available_gateways() {
        return array(
            'WVS_Gateway_Zelle',
            'WVS_Gateway_Pago_Movil',
            'WVS_Gateway_Efectivo_USD',
            'WVS_Gateway_Efectivo_VES',
            'WVS_Gateway_Cashea'
        );
    }
    
    /**
     * Inicializar sistema de envíos
     */
    private function init_shipping_system() {
        // Registrar métodos de envío
        add_filter('woocommerce_shipping_methods', array($this, 'register_shipping_methods'));
    }
    
    /**
     * Registrar métodos de envío
     * 
     * @param array $methods
     * @return array
     */
    public function register_shipping_methods($methods) {
        $active_modules = get_option('wvs_activated_modules', array());
        
        if (in_array('shipping', $active_modules)) {
            $methods['wvs_local_delivery'] = 'WVS_Shipping_Local_Delivery';
            $methods['wvs_mrw'] = 'WVS_Shipping_MRW';
            $methods['wvs_zoom'] = 'WVS_Shipping_Zoom';
            $methods['wvs_menssajero'] = 'WVS_Shipping_Menssajero';
        }
        
        return $methods;
    }
    
    /**
     * Inicializar WooCommerce
     */
    public function init_woocommerce() {
        // Verificar que WooCommerce esté disponible
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Inicializar funcionalidades específicas de WooCommerce
        $this->init_woocommerce_features();
    }
    
    /**
     * Inicializar funcionalidades de WooCommerce
     */
    private function init_woocommerce_features() {
        // Hooks específicos de WooCommerce
        add_filter('woocommerce_get_price_html', array($this, 'modify_price_display'), 10, 2);
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_order_meta'));
    }
    
    /**
     * Modificar visualización de precios
     * 
     * @param string $price_html
     * @param WC_Product $product
     * @return string
     */
    public function modify_price_display($price_html, $product) {
        // Solo en frontend
        if (is_admin()) {
            return $price_html;
        }
        
        // Solo si el módulo de moneda está activo
        $active_modules = get_option('wvs_activated_modules', array());
        if (!in_array('currency', $active_modules)) {
            return $price_html;
        }
        
        // Obtener precio del producto
        $price = $product->get_price();
        if (!$price || $price <= 0) {
            return $price_html;
        }
        
        // Obtener tasa BCV
        $rate = $this->get_bcv_rate();
        if (!$rate || $rate <= 0) {
            return $price_html;
        }
        
        // Calcular precio en VES
        $ves_price = $this->convert_usd_to_ves($price, $rate);
        
        // Formatear precios
        $formatted_usd = wc_price($price);
        $formatted_ves = number_format($ves_price, 2, ',', '.') . ' Bs.';
        
        // Generar HTML según configuración
        $currency_display = $this->config['currency_display'] ?? 'dual';
        
        switch ($currency_display) {
            case 'usd_only':
                return $formatted_usd;
                
            case 'ves_only':
                return $formatted_ves;
                
            case 'dual':
            default:
                return $formatted_usd . '<br><small>(' . $formatted_ves . ')</small>';
        }
    }
    
    /**
     * Validar checkout
     */
    public function validate_checkout() {
        // Validaciones específicas para Venezuela
        $this->validate_venezuelan_fields();
    }
    
    /**
     * Validar campos venezolanos
     */
    private function validate_venezuelan_fields() {
        // Validar RIF/Cédula si está presente
        $rif = $_POST['billing_rif'] ?? '';
        if (!empty($rif) && !$this->validate_rif($rif)) {
            wc_add_notice(__('El RIF ingresado no es válido.', 'wvs'), 'error');
        }
    }
    
    /**
     * Validar RIF venezolano
     * 
     * @param string $rif
     * @return bool
     */
    private function validate_rif($rif) {
        // Patrón básico para RIF venezolano
        $pattern = '/^[JGVEP]-[0-9]{8}-[0-9]$/';
        return preg_match($pattern, $rif);
    }
    
    /**
     * Guardar meta del pedido
     * 
     * @param int $order_id
     */
    public function save_order_meta($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Guardar tasa BCV al momento de la compra
        $bcv_rate = $this->get_bcv_rate();
        if ($bcv_rate) {
            $order->update_meta_data('_bcv_rate_at_purchase', $bcv_rate);
            $order->update_meta_data('_bcv_rate_date', current_time('mysql'));
        }
        
        // Guardar RIF si está presente
        if (!empty($_POST['billing_rif'])) {
            $order->update_meta_data('_billing_rif', sanitize_text_field($_POST['billing_rif']));
        }
        
        $order->save();
    }
    
    /**
     * Encolar scripts del frontend
     */
    public function enqueue_scripts() {
        // Solo en frontend
        if (is_admin()) {
            return;
        }
        
        // CSS base
        wp_enqueue_style(
            'wvs-frontend',
            WVS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WVS_VERSION
        );
        
        // JavaScript base
        wp_enqueue_script(
            'wvs-frontend',
            WVS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WVS_VERSION,
            true
        );
        
        // Localizar script
        wp_localize_script('wvs-frontend', 'wvs_frontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvs_frontend_nonce'),
            'currency_display' => $this->config['currency_display'] ?? 'dual',
            'strings' => array(
                'loading' => __('Cargando...', 'wvs'),
                'error' => __('Error al cargar', 'wvs'),
                'usd' => __('USD', 'wvs'),
                'ves' => __('VES', 'wvs')
            )
        ));
    }
    
    /**
     * Encolar scripts del admin
     */
    public function enqueue_admin_scripts($hook) {
        // Solo en páginas del plugin
        if (strpos($hook, 'wvs') === false) {
            return;
        }
        
        // CSS del admin
        wp_enqueue_style(
            'wvs-admin',
            WVS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WVS_VERSION
        );
        
        // JavaScript del admin
        wp_enqueue_script(
            'wvs-admin',
            WVS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WVS_VERSION,
            true
        );
        
        // Localizar script
        wp_localize_script('wvs-admin', 'wvs_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvs_admin_nonce'),
            'strings' => array(
                'confirm_deactivate' => __('¿Estás seguro de que quieres desactivar este módulo?', 'wvs'),
                'confirm_activate' => __('¿Estás seguro de que quieres activar este módulo?', 'wvs'),
                'loading' => __('Cargando...', 'wvs'),
                'error' => __('Error al procesar la solicitud', 'wvs')
            )
        ));
    }
    
    /**
     * Limpieza periódica
     */
    public function cleanup() {
        // Solo ejecutar ocasionalmente
        if (rand(1, 100) > 5) {
            return;
        }
        
        // Limpiar logs antiguos
        $this->cleanup_old_logs();
        
        // Limpiar cache
        $this->cleanup_cache();
    }
    
    /**
     * Limpiar logs antiguos
     */
    private function cleanup_old_logs() {
        global $wpdb;
        
        // Verificar si la tabla existe antes de limpiar
        $table_name = $wpdb->prefix . 'wvs_logs';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            return; // No hacer nada si la tabla no existe
        }
        
        $retention_days = 30; // Días de retención
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wvs_logs WHERE timestamp < %s",
            $cutoff_date
        ));
    }
    
    /**
     * Limpiar cache
     */
    private function cleanup_cache() {
        // Limpiar transients antiguos
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_wvs_%' 
             AND option_name NOT LIKE '_transient_timeout_wvs_%'"
        );
    }
    
    /**
     * Obtener configuración
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get_config($key = null, $default = null) {
        if ($key === null) {
            return $this->config;
        }
        
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Actualizar configuración
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function update_config($key, $value) {
        $this->config[$key] = $value;
        return update_option('wvs_settings', $this->config);
    }
}
