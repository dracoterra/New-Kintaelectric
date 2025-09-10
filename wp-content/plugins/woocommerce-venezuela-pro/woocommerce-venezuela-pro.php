<?php
/**
 * Plugin Name: WooCommerce Venezuela Pro
 * Plugin URI: https://kinta-electric.com
 * Description: Kit de localización para WooCommerce en Venezuela
 * Version: 1.0.0
 * Author: Kinta Electric
 * Author URI: https://kinta-electric.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wvp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 10.0
 * Network: false
 * 
 * HPOS compatibility: Yes
 * WC requires at least: 5.0
 * WC tested up to: 10.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('WVP_VERSION', '1.0.0');
define('WVP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WVP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WVP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WVP_PLUGIN_FILE', __FILE__);

/**
 * Clase principal del plugin WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */
class WooCommerce_Venezuela_Pro {
    
    /**
     * Instancia única del plugin (Singleton)
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private static $instance = null;
    
    /**
     * Componentes del plugin
     */
    public $bcv_integrator;
    public $price_display;
    public $checkout;
    public $dual_breakdown;
    public $hybrid_invoicing;
    public $order_meta;
    public $admin_settings;
    public $reports;
    public $payment_verification;
    public $invoice_generator;
    public $whatsapp_notifications;
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        // Hooks de activación y desactivación
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
        
        // Declarar compatibilidad con HPOS
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
        
        // Inicializar el plugin cuando WordPress esté listo
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Obtener instancia única del plugin
     * 
     * @return WooCommerce_Venezuela_Pro
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Declarar compatibilidad con HPOS
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }
    
    /**
     * Inicializar el plugin
     */
    public function init() {
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            return;
        }
        
        // Cargar archivos de dependencias
        $this->load_dependencies();
        
        // Inicializar componentes
        $this->init_components();
        
        // Log de inicialización
        error_log('WooCommerce Venezuela Pro: Plugin inicializado correctamente');
    }
    
    /**
     * Verificar dependencias
     * 
     * @return bool True si las dependencias están disponibles
     */
    private function check_dependencies() {
        // Verificar WooCommerce
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>WooCommerce Venezuela Pro requiere WooCommerce para funcionar.</p></div>';
            });
            return false;
        }
        
        // Verificar BCV Dólar Tracker
        if (!is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>WooCommerce Venezuela Pro requiere BCV Dólar Tracker para funcionar.</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Cargar archivos de dependencias
     */
    private function load_dependencies() {
        // Archivos principales
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-dependencies.php';
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-bcv-integrator.php';
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-hpos-compatibility.php';
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-hpos-migration.php';
        
        // Archivos de frontend
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-price-display.php';
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-checkout.php';
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-dual-breakdown.php';
        require_once WVP_PLUGIN_PATH . 'frontend/class-wvp-hybrid-invoicing.php';
        
        // Archivos de administración
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-order-meta.php';
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-admin-settings.php';
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-reports.php';
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-payment-verification.php';
        
        // Generador de facturas
        require_once WVP_PLUGIN_PATH . 'includes/class-wvp-invoice-generator.php';
        
        // Métodos de envío
        require_once WVP_PLUGIN_PATH . 'shipping/class-wvp-shipping-local-delivery.php';
        
        // Pasarelas de pago
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-zelle.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-pago-movil.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-efectivo.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-efectivo-bolivares.php';
        require_once WVP_PLUGIN_PATH . 'gateways/class-wvp-gateway-cashea.php';
        
        // Notificaciones WhatsApp
        require_once WVP_PLUGIN_PATH . 'admin/class-wvp-whatsapp-notifications.php';
    }
    
    /**
     * Inicializar componentes del plugin
     */
    private function init_components() {
        try {
            // Verificar que las clases estén cargadas
            if (!class_exists('WVP_BCV_Integrator')) {
                error_log('WVP Error: WVP_BCV_Integrator no está disponible');
                return;
            }
            
            if (!class_exists('WVP_Price_Display')) {
                error_log('WVP Error: WVP_Price_Display no está disponible');
                return;
            }
            
            if (!class_exists('WVP_Checkout')) {
                error_log('WVP Error: WVP_Checkout no está disponible');
                return;
            }
            
            // Inicializar integrador BCV
            $this->bcv_integrator = new WVP_BCV_Integrator();
            
            // Inicializar compatibilidad HPOS
            if (class_exists('WVP_HPOS_Compatibility')) {
                new WVP_HPOS_Compatibility();
            }
            
            // Inicializar migración HPOS
            if (class_exists('WVP_HPOS_Migration')) {
                new WVP_HPOS_Migration();
            }
            
            // Inicializar componentes de frontend
            $this->price_display = new WVP_Price_Display();
            $this->checkout = new WVP_Checkout();
            
            // Inicializar desglose dual si está disponible
            if (class_exists('WVP_Dual_Breakdown')) {
                $this->dual_breakdown = new WVP_Dual_Breakdown();
            }
            
            // Inicializar facturación híbrida si está disponible
            if (class_exists('WVP_Hybrid_Invoicing')) {
                $this->hybrid_invoicing = new WVP_Hybrid_Invoicing();
            }
            
            // Inicializar componentes de administración
            if (is_admin()) {
                if (class_exists('WVP_Order_Meta')) {
                    $this->order_meta = new WVP_Order_Meta();
                }
                if (class_exists('WVP_Admin_Settings')) {
                    $this->admin_settings = new WVP_Admin_Settings();
                }
                if (class_exists('WVP_Reports')) {
                    $this->reports = new WVP_Reports();
                }
                if (class_exists('WVP_Payment_Verification')) {
                    $this->payment_verification = new WVP_Payment_Verification();
                }
                if (class_exists('WVP_Invoice_Generator')) {
                    $this->invoice_generator = new WVP_Invoice_Generator();
                }
                if (class_exists('WVP_WhatsApp_Notifications')) {
                    $this->whatsapp_notifications = new WVP_WhatsApp_Notifications();
                }
            }
            
            // Registrar pasarelas de pago
            add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));
            
            // Registrar métodos de envío
            add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_methods'));
            
        } catch (Exception $e) {
            error_log('WVP Error: ' . $e->getMessage());
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>Error en WooCommerce Venezuela Pro: ' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * Añadir pasarelas de pago
     * 
     * @param array $gateways Pasarelas existentes
     * @return array Pasarelas modificadas
     */
    public function add_payment_gateways($gateways) {
        $gateways[] = 'WVP_Gateway_Zelle';
        $gateways[] = 'WVP_Gateway_Pago_Movil';
        $gateways[] = 'WVP_Gateway_Efectivo';
        $gateways[] = 'WVP_Gateway_Efectivo_Bolivares';
        $gateways[] = 'WVP_Gateway_Cashea';
        return $gateways;
    }
    
    /**
     * Registrar métodos de envío
     * 
     * @param array $methods Métodos de envío existentes
     * @return array Métodos modificados
     */
    public function add_shipping_methods($methods) {
        $methods['wvp_local_delivery'] = 'WVP_Shipping_Local_Delivery';
        return $methods;
    }
    
    /**
     * Obtener opción del plugin
     * 
     * @param string $option_name Nombre de la opción
     * @param mixed $default Valor por defecto
     * @return mixed Valor de la opción
     */
    public function get_option($option_name, $default = null) {
        return get_option('wvp_' . $option_name, $default);
    }
    
    /**
     * Actualizar opción del plugin
     * 
     * @param string $option_name Nombre de la opción
     * @param mixed $value Valor a guardar
     * @return bool True si se guardó correctamente
     */
    public function update_option($option_name, $value) {
        return update_option('wvp_' . $option_name, $value);
    }
    
    /**
     * Activar el plugin
     */
    public function activate_plugin() {
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            deactivate_plugins(WVP_PLUGIN_BASENAME);
            wp_die(
                __('WooCommerce Venezuela Pro requiere WooCommerce y BCV Dólar Tracker para funcionar.', 'wvp'),
                __('Error de activación', 'wvp'),
                array('back_link' => true)
            );
        }
        
        // Limpiar caché de rewrite rules
        flush_rewrite_rules();
        
        // Log de activación
        error_log('WooCommerce Venezuela Pro: Plugin activado correctamente');
    }
    
    /**
     * Desactivar el plugin
     */
    public function deactivate_plugin() {
        // Limpiar caché de rewrite rules
        flush_rewrite_rules();
        
        // Log de desactivación
        error_log('WooCommerce Venezuela Pro: Plugin desactivado');
    }
}

// Inicializar el plugin
WooCommerce_Venezuela_Pro::get_instance();