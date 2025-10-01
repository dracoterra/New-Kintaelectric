<?php
/**
 * Plugin Name: Suite Venezuela para WooCommerce
 * Plugin URI: https://kinta-electric.com/suite-venezuela-woocommerce
 * Description: Suite completa para WooCommerce en Venezuela con arquitectura modular avanzada
 * Version: 1.0.0
 * Author: Kinta Electric
 * Author URI: https://kinta-electric.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wvs
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires PHP: 8.0
 * WC requires at least: 10.0
 * WC tested up to: 10.2
 * Network: false
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('WVS_VERSION', '1.0.0');
define('WVS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WVS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WVS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WVS_PLUGIN_FILE', __FILE__);
define('WVS_MIN_WC_VERSION', '10.0');
define('WVS_MIN_WP_VERSION', '6.0');
define('WVS_MIN_PHP_VERSION', '8.0');

/**
 * Clase principal del plugin WooCommerce Venezuela Suite
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */
class WooCommerce_Venezuela_Suite {
    
    /**
     * Instancia única del plugin (Singleton)
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private static $instance = null;
    
    /**
     * Core Engine del plugin
     * 
     * @var WVS_Core_Engine
     */
    public $core_engine;
    
    /**
     * Module Manager
     * 
     * @var WVS_Module_Manager
     */
    public $module_manager;
    
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
     * @return WooCommerce_Venezuela_Suite
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
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            return;
        }
        
        // Declarar compatibilidad con HPOS
        add_action('before_woocommerce_init', function() {
            if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
            }
        });
        
        // Cargar archivos de dependencias
        $this->load_dependencies();
        
        // Inicializar componentes
        $this->init_components();
        
        // Log de inicialización
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WooCommerce Venezuela Suite: Plugin inicializado correctamente');
        }
    }
    
    /**
     * Verificar dependencias
     * 
     * @return bool True si las dependencias están disponibles
     */
    private function check_dependencies() {
        // Verificar WordPress
        if (version_compare(get_bloginfo('version'), WVS_MIN_WP_VERSION, '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Suite Venezuela para WooCommerce requiere WordPress ' . WVS_MIN_WP_VERSION . ' o superior.</p></div>';
            });
            return false;
        }
        
        // Verificar PHP
        if (version_compare(PHP_VERSION, WVS_MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Suite Venezuela para WooCommerce requiere PHP ' . WVS_MIN_PHP_VERSION . ' o superior.</p></div>';
            });
            return false;
        }
        
        // Verificar WooCommerce
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Suite Venezuela para WooCommerce requiere WooCommerce para funcionar.</p></div>';
            });
            return false;
        }
        
        // Verificar versión mínima de WooCommerce
        if (version_compare(WC()->version, WVS_MIN_WC_VERSION, '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Suite Venezuela para WooCommerce requiere WooCommerce ' . WVS_MIN_WC_VERSION . ' o superior. Versión actual: ' . WC()->version . '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Cargar archivos de dependencias
     */
    private function load_dependencies() {
        // Core Engine
        require_once WVS_PLUGIN_PATH . 'core/class-wvs-core-engine.php';
        require_once WVS_PLUGIN_PATH . 'core/class-wvs-module-manager.php';
        require_once WVS_PLUGIN_PATH . 'core/class-wvs-database.php';
        require_once WVS_PLUGIN_PATH . 'core/class-wvs-security.php';
        require_once WVS_PLUGIN_PATH . 'core/class-wvs-performance.php';
        require_once WVS_PLUGIN_PATH . 'core/class-wvs-logger.php';
        require_once WVS_PLUGIN_PATH . 'core/class-wvs-config-manager.php';
        
        // Admin
        require_once WVS_PLUGIN_PATH . 'admin/class-wvs-admin.php';
        
        // Public
        require_once WVS_PLUGIN_PATH . 'public/class-wvs-public.php';
        
        // Includes
        require_once WVS_PLUGIN_PATH . 'includes/class-wvs-helper.php';
        require_once WVS_PLUGIN_PATH . 'includes/class-wvs-compatibility.php';
    }
    
    /**
     * Inicializar componentes del plugin
     */
    private function init_components() {
        try {
            // Inicializar Core Engine
            $this->core_engine = new WVS_Core_Engine();
            
            // Inicializar Module Manager
            $this->module_manager = new WVS_Module_Manager();
            
            // Inicializar componentes de administración
            if (is_admin()) {
                new WVS_Admin();
            }
            
            // Inicializar componentes públicos
            new WVS_Public();
            
        } catch (Exception $e) {
            error_log('WVS Error: ' . $e->getMessage());
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>Error en WooCommerce Venezuela Suite: ' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * Activar el plugin
     */
    public function activate_plugin() {
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            deactivate_plugins(WVS_PLUGIN_BASENAME);
            wp_die(
                'Suite Venezuela para WooCommerce requiere WooCommerce ' . WVS_MIN_WC_VERSION . ' o superior para funcionar.',
                'Error de activación',
                array('back_link' => true)
            );
        }
        
        // Crear tablas de base de datos
        $this->create_database_tables();
        
        // Configurar opciones por defecto
        $this->set_default_options();
        
        // Limpiar caché de rewrite rules
        flush_rewrite_rules();
        
        // Log de activación
        error_log('Suite Venezuela para WooCommerce: Plugin activado correctamente');
    }
    
    /**
     * Crear tablas de base de datos necesarias
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla de módulos
        $modules_table = $wpdb->prefix . 'wvs_modules';
        $modules_sql = "CREATE TABLE IF NOT EXISTS $modules_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            module_key varchar(100) NOT NULL,
            module_name varchar(255) NOT NULL,
            module_status varchar(20) DEFAULT 'inactive',
            module_version varchar(20) DEFAULT '1.0.0',
            module_config longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY module_key (module_key),
            KEY module_status (module_status)
        ) $charset_collate;";
        
        // Tabla de logs
        $logs_table = $wpdb->prefix . 'wvs_logs';
        $logs_sql = "CREATE TABLE IF NOT EXISTS $logs_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext,
            user_id bigint(20),
            ip_address varchar(45),
            PRIMARY KEY (id),
            KEY level (level),
            KEY timestamp (timestamp),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($modules_sql);
        dbDelta($logs_sql);
    }
    
    /**
     * Configurar opciones por defecto
     */
    private function set_default_options() {
        // Configurar opciones por defecto si no existen
        if (!get_option('wvs_version')) {
            update_option('wvs_version', WVS_VERSION);
        }
        
        if (!get_option('wvs_activated_modules')) {
            update_option('wvs_activated_modules', array('core', 'currency', 'payments'));
        }
        
        if (!get_option('wvs_settings')) {
            update_option('wvs_settings', array(
                'currency_display' => 'dual',
                'default_currency' => 'USD',
                'bcv_integration' => true,
                'igtf_rate' => 3.0,
                'help_system' => true,
                'notifications' => true
            ));
        }
    }
    
    /**
     * Desactivar el plugin
     */
    public function deactivate_plugin() {
        // Limpiar caché de rewrite rules
        flush_rewrite_rules();
        
        // Log de desactivación
        error_log('Suite Venezuela para WooCommerce: Plugin desactivado');
    }
}

// Inicializar el plugin
WooCommerce_Venezuela_Suite::get_instance();
