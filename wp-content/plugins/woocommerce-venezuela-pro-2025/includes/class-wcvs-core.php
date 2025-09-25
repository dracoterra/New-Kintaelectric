<?php
/**
 * Clase principal del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase principal del plugin WooCommerce Venezuela Suite
 */
class WCVS_Core {

    /**
     * Instancia única del plugin
     *
     * @var WCVS_Core
     */
    private static $instance = null;

    /**
     * Versión del plugin
     *
     * @var string
     */
    public $version = WCVS_VERSION;

    /**
     * Cargador de hooks
     *
     * @var WCVS_Loader
     */
    public $loader;

    /**
     * Gestor de módulos
     *
     * @var WCVS_Module_Manager
     */
    public $module_manager;

    /**
     * Gestor de configuraciones
     *
     * @var WCVS_Settings
     */
    public $settings;

    /**
     * Integración con BCV
     *
     * @var WCVS_BCV_Integration
     */
    public $bcv_integration;

    /**
     * Sistema de logging
     *
     * @var WCVS_Logger
     */
    public $logger;

    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->init_modules();
    }

    /**
     * Obtener instancia única del plugin
     *
     * @return WCVS_Core
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Cargar dependencias del plugin
     */
    private function load_dependencies() {
        // Cargar clases principales
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-loader.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-settings.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-module-manager.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-bcv-integration.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-logger.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-help.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-i18n.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-activator.php';
        require_once WCVS_PLUGIN_PATH . 'includes/class-wcvs-deactivator.php';

        // Cargar clases de administración
        if (is_admin()) {
            require_once WCVS_PLUGIN_PATH . 'admin/class-wcvs-admin.php';
        }

        // Cargar clases públicas
        require_once WCVS_PLUGIN_PATH . 'public/class-wcvs-public.php';

        // Inicializar instancias básicas
        $this->loader = new WCVS_Loader();
        $this->settings = new WCVS_Settings();
        $this->module_manager = new WCVS_Module_Manager();
        
        // Inicializar componentes adicionales de manera diferida
        add_action('wp_loaded', array($this, 'init_additional_components'));
    }

    /**
     * Configurar internacionalización
     */
    private function set_locale() {
        $plugin_i18n = new WCVS_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Registrar hooks de administración
     */
    private function define_admin_hooks() {
        if (is_admin()) {
            $plugin_admin = new WCVS_Admin($this->get_version());
            
            // Hooks de administración
            $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
            $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
            $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
            $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
            
            // Hooks AJAX
            $this->loader->add_action('wp_ajax_wcvs_save_settings', $plugin_admin, 'save_settings');
            $this->loader->add_action('wp_ajax_wcvs_test_bcv_connection', $plugin_admin, 'test_bcv_connection');
            $this->loader->add_action('wp_ajax_wcvs_sync_bcv_settings', $plugin_admin, 'sync_bcv_settings');
            $this->loader->add_action('wp_ajax_wcvs_get_module_status', $plugin_admin, 'get_module_status');
            $this->loader->add_action('wp_ajax_wcvs_toggle_module', $plugin_admin, 'toggle_module');
        }
    }

    /**
     * Registrar hooks públicos
     */
    private function define_public_hooks() {
        $plugin_public = new WCVS_Public($this->get_version());
        
        // Hooks públicos
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Hooks de WooCommerce
        $this->loader->add_action('woocommerce_loaded', $this, 'init_woocommerce_integration');
        
        // Hooks de inicialización
        $this->loader->add_action('init', $this, 'init_plugin');
        $this->loader->add_action('wp_loaded', $this, 'wp_loaded');
    }

    /**
     * Inicializar módulos del plugin
     */
    private function init_modules() {
        // Registrar solo módulos esenciales inicialmente para evitar consumo de memoria
        $this->register_essential_modules();
        
        // Registrar módulos adicionales cuando sea necesario
        add_action('wp_loaded', array($this, 'register_additional_modules'));
    }

    /**
     * Registrar módulos esenciales
     */
    private function register_essential_modules() {
        $this->module_manager->register_module('currency_manager', array(
            'name' => 'Gestor de Moneda',
            'description' => 'Conversión automática USD a VES usando tasa BCV',
            'class' => 'WCVS_Currency_Manager',
            'file' => 'modules/currency-manager/class-wcvs-currency-manager.php',
            'enabled' => true,
            'priority' => 1
        ));

        $this->module_manager->register_module('payment_gateways', array(
            'name' => 'Pasarelas de Pago',
            'description' => 'Pasarelas de pago locales para Venezuela',
            'class' => 'WCVS_Payment_Gateways',
            'file' => 'modules/payment-gateways/class-wcvs-payment-gateways.php',
            'enabled' => true,
            'priority' => 2
        ));

        $this->module_manager->register_module('shipping_methods', array(
            'name' => 'Métodos de Envío',
            'description' => 'Métodos de envío locales para Venezuela',
            'class' => 'WCVS_Shipping_Methods',
            'file' => 'modules/shipping-methods/class-wcvs-shipping-methods.php',
            'enabled' => true,
            'priority' => 3
        ));
    }

    /**
     * Inicializar componentes adicionales
     */
    public function init_additional_components() {
        $this->bcv_integration = new WCVS_BCV_Integration();
        $this->logger = new WCVS_Logger();
    }

    /**
     * Registrar módulos adicionales
     */
    public function register_additional_modules() {
        $this->module_manager->register_module('tax_system', array(
            'name' => 'Sistema Fiscal',
            'description' => 'Sistema fiscal venezolano (IVA, IGTF)',
            'class' => 'WCVS_Tax_System',
            'file' => 'modules/tax-system/class-wcvs-tax-system.php',
            'enabled' => true,
            'priority' => 4
        ));

        $this->module_manager->register_module('electronic_billing', array(
            'name' => 'Facturación Electrónica',
            'description' => 'Facturación electrónica para SENIAT',
            'class' => 'WCVS_Electronic_Billing',
            'file' => 'modules/electronic-billing/class-wcvs-electronic-billing.php',
            'enabled' => true,
            'priority' => 5
        ));

        $this->module_manager->register_module('seniat_reports', array(
            'name' => 'Reportes SENIAT',
            'description' => 'Reportes fiscales para SENIAT',
            'class' => 'WCVS_SENIAT_Reports',
            'file' => 'modules/seniat-reports/class-wcvs-seniat-reports.php',
            'enabled' => true,
            'priority' => 6
        ));

        $this->module_manager->register_module('price_display', array(
            'name' => 'Visualización de Precios',
            'description' => 'Sistema avanzado de visualización de precios',
            'class' => 'WCVS_Price_Display',
            'file' => 'modules/price-display/class-wcvs-price-display.php',
            'enabled' => true,
            'priority' => 7
        ));

        $this->module_manager->register_module('onboarding', array(
            'name' => 'Configuración Rápida',
            'description' => 'Wizard de configuración rápida',
            'class' => 'WCVS_Onboarding',
            'file' => 'modules/onboarding/class-wcvs-onboarding.php',
            'enabled' => true,
            'priority' => 8
        ));

        $this->module_manager->register_module('help_system', array(
            'name' => 'Sistema de Ayuda',
            'description' => 'Sistema de ayuda integrado',
            'class' => 'WCVS_Help_System',
            'file' => 'modules/help-system/class-wcvs-help-system.php',
            'enabled' => true,
            'priority' => 9
        ));

        $this->module_manager->register_module('notifications', array(
            'name' => 'Sistema de Notificaciones',
            'description' => 'Sistema de notificaciones automáticas',
            'class' => 'WCVS_Notifications',
            'file' => 'modules/notifications/class-wcvs-notifications.php',
            'enabled' => true,
            'priority' => 10
        ));
    }

    /**
     * Inicializar integración con WooCommerce
     */
    public function init_woocommerce_integration() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_CORE, 'WooCommerce no está activo. WooCommerce Venezuela Suite requiere WooCommerce.');
            return;
        }

        // Inicializar integración BCV si está disponible
        if ($this->bcv_integration && method_exists($this->bcv_integration, 'init')) {
            $this->bcv_integration->init();
        }

        // Cargar módulos activos
        $this->module_manager->load_active_modules();

        // Registrar hooks de WooCommerce
        $this->register_woocommerce_hooks();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_CORE, 'Integración con WooCommerce inicializada correctamente');
    }

    /**
     * Registrar hooks específicos de WooCommerce
     */
    private function register_woocommerce_hooks() {
        // Hook para cuando se actualiza un pedido
        $this->loader->add_action('woocommerce_order_status_changed', $this, 'handle_order_status_change');
        
        // Hook para cuando se crea un pedido
        $this->loader->add_action('woocommerce_checkout_order_processed', $this, 'handle_new_order');
        
        // Hook para cuando se actualiza la tasa BCV
        $this->loader->add_action('wvp_bcv_rate_updated', $this, 'handle_bcv_rate_update');
        
        // Hook para cuando se activa el plugin
        $this->loader->add_action('wcvs_plugin_activated', $this, 'handle_plugin_activation');
    }

    /**
     * Inicializar el plugin
     */
    public function init_plugin() {
        // Verificar dependencias
        if (!$this->check_dependencies()) {
            return;
        }

        // Inicializar configuración
        $this->settings->init();

        // Inicializar logging si está disponible
        if ($this->logger && method_exists($this->logger, 'init')) {
            $this->logger->init();
        }

        // Registrar hooks de inicialización
        $this->loader->add_action('wp_loaded', $this, 'wp_loaded');
        $this->loader->add_action('admin_init', $this, 'admin_init');

        WCVS_Logger::info(WCVS_Logger::CONTEXT_CORE, 'Plugin WooCommerce Venezuela Suite inicializado');
    }

    /**
     * Verificar dependencias del plugin
     *
     * @return bool
     */
    private function check_dependencies() {
        $dependencies = array(
            'WooCommerce' => class_exists('WooCommerce'),
            'WordPress' => function_exists('wp_get_current_user')
        );

        $missing = array();
        foreach ($dependencies as $name => $exists) {
            if (!$exists) {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_CORE, 'Dependencias faltantes: ' . implode(', ', $missing));
            add_action('admin_notices', array($this, 'dependency_notice'));
            return false;
        }

        return true;
    }

    /**
     * Mostrar aviso de dependencias faltantes
     */
    public function dependency_notice() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>WooCommerce Venezuela Suite:</strong> ';
        echo 'Se requieren las siguientes dependencias: WooCommerce. ';
        echo 'Por favor, instala y activa las dependencias necesarias.';
        echo '</p></div>';
    }

    /**
     * Ejecutar cuando WordPress está completamente cargado
     */
    public function wp_loaded() {
        // Sincronizar configuración BCV si es necesario
        if ($this->bcv_integration && $this->bcv_integration->is_available()) {
            $this->bcv_integration->sync_settings();
        }

        // Cargar módulos activos
        $this->module_manager->load_active_modules();

        // Ejecutar acciones de inicialización
        do_action('wcvs_wp_loaded');
    }

    /**
     * Ejecutar en admin_init
     */
    public function admin_init() {
        // Verificar si es la primera activación
        if (get_option('wcvs_first_activation', false) === false) {
            $this->handle_first_activation();
        }

        // Sincronizar configuración BCV
        if ($this->bcv_integration && $this->bcv_integration->is_available()) {
            $this->bcv_integration->sync_settings();
        }
    }

    /**
     * Manejar primera activación del plugin
     */
    private function handle_first_activation() {
        // Marcar como activado
        update_option('wcvs_first_activation', true);
        update_option('wcvs_activation_date', current_time('mysql'));

        // Configurar valores por defecto
        $this->settings->set_default_values();

        // Inicializar módulos por defecto
        $this->module_manager->activate_default_modules();

        // Ejecutar onboarding si está disponible
        if ($this->module_manager->is_module_active('onboarding')) {
            $onboarding_module = $this->module_manager->get_module_instance('onboarding');
            if ($onboarding_module && method_exists($onboarding_module, 'init')) {
                $onboarding_module->init();
            }
        }

        WCVS_Logger::info(WCVS_Logger::CONTEXT_CORE, 'Primera activación del plugin completada');
    }

    /**
     * Manejar cambio de estado de pedido
     *
     * @param int $order_id ID del pedido
     * @param string $old_status Estado anterior
     * @param string $new_status Estado nuevo
     */
    public function handle_order_status_change($order_id, $old_status, $new_status) {
        $this->logger->info("Estado de pedido cambiado: {$order_id} de {$old_status} a {$new_status}");

        // Notificar a módulos activos
        $this->module_manager->notify_modules('order_status_changed', array(
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status
        ));
    }

    /**
     * Manejar nuevo pedido
     *
     * @param int $order_id ID del pedido
     */
    public function handle_new_order($order_id) {
        $this->logger->info("Nuevo pedido creado: {$order_id}");

        // Notificar a módulos activos
        $this->module_manager->notify_modules('new_order', array(
            'order_id' => $order_id
        ));
    }

    /**
     * Manejar actualización de tasa BCV
     *
     * @param float $new_rate Nueva tasa
     * @param float $old_rate Tasa anterior
     */
    public function handle_bcv_rate_update($new_rate, $old_rate) {
        $this->logger->info("Tasa BCV actualizada: {$old_rate} → {$new_rate}");

        // Actualizar cache de conversiones
        delete_transient('wcvs_conversion_cache');

        // Notificar a módulos activos
        $this->module_manager->notify_modules('bcv_rate_updated', array(
            'new_rate' => $new_rate,
            'old_rate' => $old_rate
        ));
    }

    /**
     * Manejar activación del plugin
     */
    public function handle_plugin_activation() {
        $this->logger->info('Plugin WooCommerce Venezuela Suite activado');

        // Ejecutar acciones de activación
        do_action('wcvs_plugin_activated');
    }

    /**
     * Ejecutar el plugin
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Cargar módulo bajo demanda
     *
     * @param string $module_key Clave del módulo
     * @return bool
     */
    public function load_module_on_demand($module_key) {
        if ($this->module_manager->is_module_active($module_key)) {
            $module_instance = $this->module_manager->get_module_instance($module_key);
            if (!$module_instance) {
                $this->module_manager->load_module($module_key);
                return true;
            }
        }
        return false;
    }

    /**
     * Obtener instancia del plugin de manera segura
     *
     * @return WCVS_Core|null
     */
    public static function get_safe_instance() {
        if (self::$instance === null) {
            return null;
        }
        return self::$instance;
    }

    /**
     * Obtener configuración de manera segura
     *
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public static function get_safe_setting($key, $default = null) {
        $settings = get_option('wcvs_settings', array());
        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Obtener versión del plugin
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Obtener cargador de hooks
     *
     * @return WCVS_Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Obtener gestor de módulos
     *
     * @return WCVS_Module_Manager
     */
    public function get_module_manager() {
        return $this->module_manager;
    }

    /**
     * Obtener gestor de configuraciones
     *
     * @return WCVS_Settings
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Obtener integración BCV
     *
     * @return WCVS_BCV_Integration|null
     */
    public function get_bcv_integration() {
        return $this->bcv_integration;
    }

    /**
     * Obtener sistema de logging
     *
     * @return WCVS_Logger
     */
    public function get_logger() {
        return $this->logger;
    }
}
