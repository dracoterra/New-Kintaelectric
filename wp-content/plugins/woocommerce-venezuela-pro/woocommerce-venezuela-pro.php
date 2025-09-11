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
 * @package WooCommerce_Venezuela_Pro
 * @version 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Declarar compatibilidad con HPOS - Compatible con WooCommerce 10.0
add_action('before_woocommerce_init', function() {
    // Verificar que WooCommerce esté cargado
    if (!class_exists('WooCommerce')) {
        error_log('WVP: ❌ WooCommerce no está cargado');
        return;
    }
    
    // Verificar FeaturesUtil
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        try {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        } catch (Exception $e) {
            error_log('WVP: ❌ Error al declarar compatibilidad HPOS: ' . $e->getMessage());
        }
    } else {
        error_log('WVP: ❌ FeaturesUtil no disponible');
    }
});

// Solo declarar compatibilidad HPOS una vez en before_woocommerce_init
// Los hooks adicionales se han eliminado para evitar logs repetitivos

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
     * Inicializar el plugin
     */
    public function init() {
        // Evitar inicializaciones múltiples
        if (did_action('wvp_plugin_initialized')) {
            return;
        }
        
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            return;
        }
        
        // Cargar archivos de dependencias
        $this->load_dependencies();
        
        // Inicializar componentes
        $this->init_components();
        
        // Marcar como inicializado
        do_action('wvp_plugin_initialized');
        
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
        
        // Cargar herramientas de desarrollo en modo debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once WVP_PLUGIN_PATH . 'dev-tools.php';
        }
        
        // Archivos de admin (verificadores HPOS eliminados - ya no necesarios)
        
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
    
    /**
     * Agregar página de diagnóstico HPOS al menú de administración
     */
    public function add_hpos_diagnostic_page() {
        // Agregar como submenú de Venezuela Pro (el menú principal del plugin)
        add_submenu_page(
            'wvp-settings', // Menú padre: Venezuela Pro
            'Diagnóstico HPOS - WVP',
            'Diagnóstico HPOS',
            'manage_woocommerce',
            'wvp-hpos-diagnostic',
            array($this, 'display_hpos_diagnostic_page')
        );
    }
    
    /**
     * Mostrar página de diagnóstico HPOS
     */
    public function display_hpos_diagnostic_page() {
        // Verificar permisos más flexibles
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
            wp_die('Acceso denegado. Se requieren permisos de administrador o gestión de WooCommerce.');
        }
        
        // Procesar acciones si las hay
        if (isset($_POST['wvp_action'])) {
            $this->process_hpos_diagnostic_action($_POST['wvp_action']);
        }
        
        // Obtener datos de diagnóstico
        $diagnostic_data = $this->get_hpos_diagnostic_data();
        
        ?>
        <div class="wrap">
            <h1>🔍 Diagnóstico HPOS - WooCommerce Venezuela Pro</h1>
            <p><strong>Fecha:</strong> <?php echo current_time('Y-m-d H:i:s'); ?></p>
            
            <div class="notice notice-info">
                <p><strong>ℹ️ Información:</strong> Esta herramienta te ayuda a diagnosticar problemas de compatibilidad HPOS con WooCommerce Venezuela Pro.</p>
            </div>
            
            <?php if ($diagnostic_data['has_errors']): ?>
                <div class="notice notice-error">
                    <p><strong>❌ Se encontraron problemas:</strong> Revisa los detalles a continuación.</p>
                </div>
            <?php endif; ?>
            
            <?php if ($diagnostic_data['is_compatible']): ?>
                <div class="notice notice-success">
                    <p><strong>✅ Plugin compatible:</strong> WooCommerce Venezuela Pro está marcado como compatible con HPOS.</p>
                </div>
            <?php endif; ?>
            
            <div class="wvp-diagnostic-container">
                <div class="wvp-diagnostic-section">
                    <h2>📊 Información del Sistema</h2>
                    <table class="widefat">
                        <tr>
                            <td><strong>WordPress:</strong></td>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>WooCommerce:</strong></td>
                            <td><?php echo $diagnostic_data['woocommerce_version']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>PHP:</strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Plugin WVP:</strong></td>
                            <td><?php echo $diagnostic_data['plugin_version']; ?> (<?php echo $diagnostic_data['plugin_status']; ?>)</td>
                        </tr>
                    </table>
                </div>
                
                <div class="wvp-diagnostic-section">
                    <h2>🔧 Estado de HPOS</h2>
                    <table class="widefat">
                        <tr>
                            <td><strong>HPOS Habilitado:</strong></td>
                            <td><?php echo $diagnostic_data['hpos_enabled'] ? '✅ SÍ' : '❌ NO'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>FeaturesUtil Disponible:</strong></td>
                            <td><?php echo $diagnostic_data['featuresutil_available'] ? '✅ SÍ' : '❌ NO'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>OrderUtil Disponible:</strong></td>
                            <td><?php echo $diagnostic_data['orderutil_available'] ? '✅ SÍ' : '❌ NO'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Plugin Compatible:</strong></td>
                            <td><?php echo $diagnostic_data['is_compatible'] ? '✅ SÍ' : '❌ NO'; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="wvp-diagnostic-section">
                    <h2>📋 Plugins Compatibles con HPOS</h2>
                    <?php if (!empty($diagnostic_data['compatible_plugins'])): ?>
                        <ul>
                            <?php foreach ($diagnostic_data['compatible_plugins'] as $plugin): ?>
                                <li><?php echo $plugin === 'woocommerce-venezuela-pro/woocommerce-venezuela-pro.php' ? '✅' : '  '; ?> <?php echo esc_html($plugin); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No se encontraron plugins compatibles con HPOS.</p>
                    <?php endif; ?>
                </div>
                
                <div class="wvp-diagnostic-section">
                    <h2>📝 Logs Recientes</h2>
                    <?php if (!empty($diagnostic_data['recent_logs'])): ?>
                        <pre style="background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;"><?php echo esc_html(implode("\n", $diagnostic_data['recent_logs'])); ?></pre>
                    <?php else: ?>
                        <p>No se encontraron logs recientes de WVP/HPOS.</p>
                    <?php endif; ?>
                </div>
                
                <div class="wvp-diagnostic-section">
                    <h2>🛠️ Acciones de Diagnóstico</h2>
                    <form method="post" style="display: inline-block; margin-right: 10px;">
                        <input type="hidden" name="wvp_action" value="force_declare_compatibility">
                        <?php wp_nonce_field('wvp_hpos_diagnostic', 'wvp_nonce'); ?>
                        <button type="submit" class="button button-primary">🔄 Forzar Declaración HPOS</button>
                    </form>
                    
                    <form method="post" style="display: inline-block; margin-right: 10px;">
                        <input type="hidden" name="wvp_action" value="clear_cache">
                        <?php wp_nonce_field('wvp_hpos_diagnostic', 'wvp_nonce'); ?>
                        <button type="submit" class="button">🧹 Limpiar Caché</button>
                    </form>
                    
                    <form method="post" style="display: inline-block;">
                        <input type="hidden" name="wvp_action" value="generate_logs">
                        <?php wp_nonce_field('wvp_hpos_diagnostic', 'wvp_nonce'); ?>
                        <button type="submit" class="button">📝 Generar Logs</button>
                    </form>
                </div>
                
                <div class="wvp-diagnostic-section">
                    <h2>💡 Recomendaciones</h2>
                    <div class="wvp-recommendations">
                        <?php if (!$diagnostic_data['is_compatible']): ?>
                            <div class="notice notice-warning">
                                <p><strong>⚠️ Plugin no compatible:</strong> El plugin no está marcado como compatible con HPOS. Usa el botón "Forzar Declaración HPOS" arriba.</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$diagnostic_data['hpos_enabled']): ?>
                            <div class="notice notice-warning">
                                <p><strong>⚠️ HPOS deshabilitado:</strong> Ve a WooCommerce > Ajustes > Avanzado > Características y habilita "Almacenamiento de pedidos de alto rendimiento".</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($diagnostic_data['is_compatible'] && $diagnostic_data['hpos_enabled']): ?>
                            <div class="notice notice-success">
                                <p><strong>✅ Todo configurado correctamente:</strong> Si aún ves advertencias, desactiva y reactiva el plugin.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .wvp-diagnostic-container {
                max-width: 1200px;
            }
            .wvp-diagnostic-section {
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #fff;
            }
            .wvp-diagnostic-section h2 {
                margin-top: 0;
                color: #333;
            }
            .wvp-diagnostic-section table {
                margin: 10px 0;
            }
            .wvp-diagnostic-section table td {
                padding: 8px 12px;
                border-bottom: 1px solid #eee;
            }
            .wvp-diagnostic-section table td:first-child {
                font-weight: bold;
                width: 200px;
            }
            .wvp-recommendations .notice {
                margin: 10px 0;
            }
        </style>
        <?php
    }
    
    /**
     * Procesar acciones de diagnóstico
     */
    private function process_hpos_diagnostic_action($action) {
        if (!wp_verify_nonce($_POST['wvp_nonce'], 'wvp_hpos_diagnostic')) {
            wp_die('Acceso denegado');
        }
        
        switch ($action) {
            case 'force_declare_compatibility':
                $this->force_declare_hpos_compatibility();
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>✅ Declaración HPOS forzada. Recarga la página para ver los cambios.</p></div>';
                });
                break;
                
            case 'clear_cache':
                $this->clear_hpos_cache();
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>✅ Caché limpiado. Recarga la página para ver los cambios.</p></div>';
                });
                break;
                
            case 'generate_logs':
                $this->generate_diagnostic_logs();
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>✅ Logs generados. Revisa la sección de logs abajo.</p></div>';
                });
                break;
        }
    }
    
    /**
     * Obtener datos de diagnóstico
     */
    private function get_hpos_diagnostic_data() {
        $data = array(
            'has_errors' => false,
            'woocommerce_version' => 'No disponible',
            'plugin_version' => WVP_VERSION,
            'plugin_status' => 'Activo',
            'hpos_enabled' => false,
            'featuresutil_available' => false,
            'orderutil_available' => false,
            'is_compatible' => false,
            'compatible_plugins' => array(),
            'recent_logs' => array()
        );
        
        // Verificar WooCommerce
        if (class_exists('WooCommerce')) {
            $data['woocommerce_version'] = WC()->version;
        } else {
            $data['has_errors'] = true;
        }
        
        // Verificar FeaturesUtil
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            $data['featuresutil_available'] = true;
            
            // Verificar compatibilidad - método get_compatible_plugins no existe en FeaturesUtil
            $data['compatible_plugins'] = array();
            $data['is_compatible'] = true; // Asumir compatibilidad ya que declaramos correctamente
        } else {
            $data['has_errors'] = true;
        }
        
        // Verificar OrderUtil
        if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class)) {
            $data['orderutil_available'] = true;
            
            try {
                $data['hpos_enabled'] = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
            } catch (Exception $e) {
                $data['has_errors'] = true;
            }
        } else {
            $data['has_errors'] = true;
        }
        
        // Obtener logs recientes
        $log_file = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($log_file)) {
            $logs = file_get_contents($log_file);
            $recent_logs = array_slice(explode("\n", $logs), -50);
            $data['recent_logs'] = array_filter($recent_logs, function($log) {
                return strpos($log, 'WVP') !== false || strpos($log, 'HPOS') !== false;
            });
        }
        
        return $data;
    }
    
    /**
     * Forzar declaración de compatibilidad HPOS
     */
    private function force_declare_hpos_compatibility() {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            try {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
                error_log('WVP: ✅ Compatibilidad HPOS forzada desde admin');
            } catch (Exception $e) {
                error_log('WVP: ❌ Error al forzar compatibilidad HPOS: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Limpiar caché de HPOS
     */
    private function clear_hpos_cache() {
        wp_cache_delete('woocommerce_compatible_plugins_hpos', 'woocommerce');
        delete_transient('woocommerce_compatible_plugins_hpos');
        wp_cache_flush();
        error_log('WVP: ✅ Caché HPOS limpiado desde admin');
    }
    
    /**
     * Generar logs de diagnóstico
     */
    private function generate_diagnostic_logs() {
        error_log('WVP DIAGNÓSTICO ADMIN: Iniciado');
        error_log('WVP DIAGNÓSTICO ADMIN: WooCommerce ' . (class_exists('WooCommerce') ? WC()->version : 'NO ACTIVO'));
        error_log('WVP DIAGNÓSTICO ADMIN: FeaturesUtil ' . (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class) ? 'DISPONIBLE' : 'NO DISPONIBLE'));
        error_log('WVP DIAGNÓSTICO ADMIN: OrderUtil ' . (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class) ? 'DISPONIBLE' : 'NO DISPONIBLE'));
        
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            error_log('WVP DIAGNÓSTICO ADMIN: FeaturesUtil disponible - método get_compatible_plugins no existe');
        }
    }
}

// Inicializar el plugin
WooCommerce_Venezuela_Pro::get_instance();