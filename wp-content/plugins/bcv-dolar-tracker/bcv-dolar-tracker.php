<?php
/**
 * Plugin Name: BCV Dólar Tracker
 * Plugin URI: https://kinta-electric.com
 * Description: Plugin para rastrear y almacenar el precio del dólar desde el Banco Central de Venezuela (BCV) mediante tareas cron configurables.
 * Version: 1.0.0
 * Author: Kinta Electric
 * Author URI: https://kinta-electric.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bcv-dolar-tracker
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('BCV_DOLAR_TRACKER_VERSION', '1.0.0');
define('BCV_DOLAR_TRACKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BCV_DOLAR_TRACKER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BCV_DOLAR_TRACKER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Clase principal del plugin
class BCV_Dolar_Tracker {
    
    /**
     * Versión del plugin
     */
    public $version = BCV_DOLAR_TRACKER_VERSION;
    
    /**
     * Instancia de la base de datos
     */
    public $database;
    
    /**
     * Instancia del cron
     */
    public $cron;
    
    /**
     * Instancia del scraper
     */
    public $scraper;
    
    /**
     * Instancia del admin
     */
    public $admin;
    
    /**
     * Constructor del plugin
     */
    public function __construct() {
        // Hooks de activación y desactivación
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
        
        // Hook para inicialización tardía y segura
        add_action('plugins_loaded', array($this, 'init_plugin'), 10);
        
        // Forzar creación de tabla en activación
        add_action('admin_init', array($this, 'force_create_database_table'));
    }
    
    /**
     * Inicialización segura del plugin
     */
    public function init_plugin() {
        // Evitar inicializaciones múltiples
        if (did_action('bcv_plugin_initialized')) {
            return;
        }
        
        // Cargar dependencias
        $this->load_dependencies();
        
        // Inicializar el plugin
        $this->init();
    }
    
    /**
     * Forzar creación de tabla de base de datos
     */
    public function force_create_database_table() {
        // Solo ejecutar una vez por sesión
        if (get_transient('bcv_table_created')) {
            return;
        }
        
        if (isset($this->database) && method_exists($this->database, 'create_price_table')) {
            $result = $this->database->create_price_table();
            if ($result) {
                set_transient('bcv_table_created', true, 3600); // 1 hora
                error_log('BCV Dólar Tracker: Tabla creada forzadamente');
            }
        }
    }
    
    /**
     * Inicializar el plugin
     */
    public function init() {
        // Evitar inicializaciones múltiples
        if (did_action('bcv_plugin_initialized')) {
            return;
        }
        
        // Inicializar hooks
        $this->init_hooks();
        
        // Marcar como inicializado
        do_action('bcv_plugin_initialized');
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hooks de administración
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        }
        
        // Hooks de AJAX
        add_action('wp_ajax_bcv_save_cron_settings', array($this, 'save_cron_settings'));
        add_action('wp_ajax_bcv_test_scraping', array($this, 'test_scraping'));
    }
    
    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        // Cargar archivos de funcionalidad principal
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-logger.php';
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-performance-monitor.php';
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-security.php';
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-database.php';
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-cron.php';
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-scraper.php';
        
        // Cargar archivos de administración
        if (is_admin()) {
            require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'admin/class-bcv-admin.php';
        }
        
        // Cargar archivo de prueba en modo debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/test-database.php';
            require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/test-cron.php';
            require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'dev-tools.php';
        }
        
        // Inicializar instancias de las clases principales
        $this->database = new BCV_Database();
        $this->cron = new BCV_Cron();
        $this->scraper = new BCV_Scraper();
        
        if (is_admin()) {
            $this->admin = new BCV_Admin_Clean();
        }
    }
    
    /**
     * Activar el plugin
     */
    public function activate_plugin() {
        // Crear tabla de base de datos
        $this->create_database_table();
        
        // Configurar opciones por defecto
        $this->set_default_options();
        
        // Limpiar caché de rewrite rules
        flush_rewrite_rules();
        
        // Sincronizar con WooCommerce Venezuela Pro si está disponible
        if (class_exists('WVP_Price_Calculator')) {
            self::sync_with_wvp();
        }
        
        // Log de activación
        error_log('BCV Dólar Tracker: Plugin activado correctamente');
    }
    
    /**
     * Desactivar el plugin
     */
    public function deactivate_plugin() {
        // Limpiar tareas cron
        $this->clear_cron_jobs();
        
        // Limpiar caché de rewrite rules
        flush_rewrite_rules();
        
        // Log de desactivación
        error_log('BCV Dólar Tracker: Plugin desactivado correctamente');
    }
    
    /**
     * Crear tabla de base de datos
     */
    private function create_database_table() {
        if (isset($this->database)) {
            $result = $this->database->create_price_table();
            if ($result) {
                error_log('BCV Dólar Tracker: Tabla de base de datos creada correctamente');
            } else {
                error_log('BCV Dólar Tracker: Error al crear tabla de base de datos');
            }
        } else {
            error_log('BCV Dólar Tracker: Clase de base de datos no disponible');
        }
    }
    
    /**
     * Configurar opciones por defecto
     */
    private function set_default_options() {
        // Configuración por defecto del cron
        $default_cron_settings = array(
            'hours' => 1,
            'minutes' => 0,
            'seconds' => 0,
            'enabled' => true
        );
        
        add_option('bcv_cron_settings', $default_cron_settings);
        add_option('bcv_plugin_version', BCV_DOLAR_TRACKER_VERSION);
        
        error_log('BCV Dólar Tracker: Opciones por defecto configuradas');
    }
    
    /**
     * Limpiar tareas cron
     */
    private function clear_cron_jobs() {
        // Limpiar hook personalizado del cron
        wp_clear_scheduled_hook('bcv_scrape_dollar_rate');
        
        error_log('BCV Dólar Tracker: Tareas cron limpiadas');
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        // Esta función se implementará en la Fase 4
        
        // La funcionalidad del menú se maneja en la clase BCV_Admin
        // que se inicializa automáticamente en load_dependencies()
    }
    
    /**
     * Cargar assets de administración
     */
    public function enqueue_admin_assets($hook) {
        // Esta función se implementará en la Fase 4
    }
    
    /**
     * Guardar configuración del cron (AJAX)
     */
    public function save_cron_settings() {
        // Esta función se implementará en la Fase 4
        // Por ahora solo registramos que se llamó
        error_log('BCV Dólar Tracker: Función save_cron_settings() llamada');
    }
    
    /**
     * Probar scraping (AJAX)
     */
    public function test_scraping() {
        // Verificar nonce para seguridad
        if (!wp_verify_nonce($_POST['nonce'], 'bcv_test_scraping')) {
            wp_die('Acceso denegado');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Ejecutar scraping manual
        if (isset($this->cron)) {
            $result = $this->cron->execute_manual_scraping();
            
            // Devolver respuesta JSON
            wp_send_json($result);
        } else {
            wp_send_json(array(
                'success' => false,
                'message' => 'Clase de cron no disponible',
                'price' => null,
                'timestamp' => current_time('mysql')
            ));
        }
    }
    
    /**
     * Obtener la tasa actual del dólar
     * 
     * @return float|false Precio actual del dólar o false si no hay datos
     */
    public static function get_rate() {
        // Verificar si la clase de base de datos está disponible
        if (!class_exists('BCV_Database')) {
            error_log('BCV Dólar Tracker: Clase BCV_Database no disponible');
            return false;
        }
        
        // Crear instancia de la base de datos
        $database = new BCV_Database();
        
        // Obtener el precio más reciente
        $latest_price = $database->get_latest_price();
        
        if ($latest_price && $latest_price > 0) {
            return floatval($latest_price);
        }
        
        // Si no hay datos en la base de datos, intentar obtener de las opciones de WordPress
        $fallback_rate = get_option('wvp_bcv_rate', false);
        if ($fallback_rate && $fallback_rate > 0) {
            return floatval($fallback_rate);
        }
        
        error_log('BCV Dólar Tracker: No se encontró tasa de dólar disponible');
        return false;
    }
    
    /**
     * Sincronizar datos con WooCommerce Venezuela Pro
     * 
     * @return bool True si se sincronizó correctamente, False en caso contrario
     */
    public static function sync_with_wvp() {
        // Obtener el precio más reciente de la base de datos
        if (!class_exists('BCV_Database')) {
            error_log('BCV Dólar Tracker: Clase BCV_Database no disponible para sincronización');
            return false;
        }
        
        $database = new BCV_Database();
        $latest_price = $database->get_latest_price();
        
        if ($latest_price && isset($latest_price->precio)) {
            $precio = floatval($latest_price->precio);
            
            // Actualizar opción WVP
            $old_rate = get_option('wvp_bcv_rate', 0);
            update_option('wvp_bcv_rate', $precio);
            
            // Disparar hook
            do_action('wvp_bcv_rate_updated', $precio, $old_rate);
            
            error_log("BCV Dólar Tracker: Sincronización con WVP completada - Precio: {$precio}");
            return true;
        }
        
        error_log('BCV Dólar Tracker: No hay datos para sincronizar con WVP');
        return false;
    }
}

// Inicializar el plugin
new BCV_Dolar_Tracker();
