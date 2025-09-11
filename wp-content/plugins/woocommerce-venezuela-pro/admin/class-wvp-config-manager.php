<?php
/**
 * Gestor de configuración del plugin
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Config_Manager {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Configuraciones del plugin
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
        // Registrar página de configuración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Registrar configuraciones
        add_action('admin_init', array($this, 'register_settings'));
        
        // Cargar scripts y estilos
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hook para guardar configuraciones
        add_action('wp_ajax_wvp_save_config', array($this, 'save_config'));
        
        // Hook para resetear configuraciones
        add_action('wp_ajax_wvp_reset_config', array($this, 'reset_config'));
        
        // Hook para exportar configuraciones
        add_action('wp_ajax_wvp_export_config', array($this, 'export_config'));
        
        // Hook para importar configuraciones
        add_action('wp_ajax_wvp_import_config', array($this, 'import_config'));
    }
    
    /**
     * Cargar configuración del plugin
     */
    private function load_config() {
        $this->config = array(
            // Configuración general
            'plugin_enabled' => get_option('wvp_plugin_enabled', 'yes'),
            'debug_mode' => get_option('wvp_debug_mode', 'no'),
            'cache_duration' => get_option('wvp_cache_duration', 3600),
            'auto_update_rates' => get_option('wvp_auto_update_rates', 'yes'),
            
            // Configuración de BCV
            'bcv_enabled' => get_option('wvp_bcv_enabled', 'yes'),
            'bcv_cache_duration' => get_option('wvp_bcv_cache_duration', 3600),
            'bcv_fallback_rate' => get_option('wvp_bcv_fallback_rate', 36.0),
            'bcv_retry_attempts' => get_option('wvp_bcv_retry_attempts', 3),
            
            // Configuración de IGTF
            'igtf_enabled' => get_option('wvp_igtf_enabled', 'yes'),
            'igtf_rate' => get_option('wvp_igtf_rate', 3.0),
            'igtf_min_amount' => get_option('wvp_igtf_min_amount', 0),
            'igtf_max_amount' => get_option('wvp_igtf_max_amount', 0),
            'igtf_payment_methods' => get_option('wvp_igtf_payment_methods', array('wvp_efectivo')),
            
            // Configuración de validaciones
            'min_order_amount' => get_option('wvp_min_order_amount', 0),
            'max_order_amount' => get_option('wvp_max_order_amount', 0),
            'require_cedula_rif' => get_option('wvp_require_cedula_rif', 'yes'),
            'validate_phone' => get_option('wvp_validate_phone', 'yes'),
            'validate_email' => get_option('wvp_validate_email', 'yes'),
            
            // Configuración de funcionalidades
            'currency_switcher_enabled' => get_option('wvp_currency_switcher_enabled', 'yes'),
            'dual_breakdown_enabled' => get_option('wvp_dual_breakdown_enabled', 'yes'),
            'hybrid_invoicing_enabled' => get_option('wvp_hybrid_invoicing_enabled', 'yes'),
            'price_display_enabled' => get_option('wvp_price_display_enabled', 'yes'),
            
            // Configuración de notificaciones
            'email_notifications' => get_option('wvp_email_notifications', 'yes'),
            'whatsapp_notifications' => get_option('wvp_whatsapp_notifications', 'no'),
            'admin_notifications' => get_option('wvp_admin_notifications', 'yes'),
            
            // Configuración de seguridad
            'rate_limiting_enabled' => get_option('wvp_rate_limiting_enabled', 'yes'),
            'rate_limit_attempts' => get_option('wvp_rate_limit_attempts', 5),
            'rate_limit_period' => get_option('wvp_rate_limit_period', 300),
            'security_logging' => get_option('wvp_security_logging', 'yes'),
            
            // Configuración de rendimiento
            'enable_caching' => get_option('wvp_enable_caching', 'yes'),
            'cache_ttl' => get_option('wvp_cache_ttl', 3600),
            'lazy_loading' => get_option('wvp_lazy_loading', 'yes'),
            'minify_assets' => get_option('wvp_minify_assets', 'no')
        );
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WooCommerce Venezuela Pro', 'wvp'),
            __('Venezuela Pro', 'wvp'),
            'manage_woocommerce',
            'wvp-settings',
            array($this, 'display_settings_page'),
            'dashicons-admin-site-alt3',
            56
        );
        
        add_submenu_page(
            'wvp-settings',
            __('Configuración General', 'wvp'),
            __('Configuración', 'wvp'),
            'manage_woocommerce',
            'wvp-settings',
            array($this, 'display_settings_page')
        );
        
        add_submenu_page(
            'wvp-settings',
            __('IGTF', 'wvp'),
            __('IGTF', 'wvp'),
            'manage_woocommerce',
            'wvp-igtf-settings',
            array($this, 'display_igtf_settings')
        );
        
        add_submenu_page(
            'wvp-settings',
            __('Validaciones', 'wvp'),
            __('Validaciones', 'wvp'),
            'manage_woocommerce',
            'wvp-validation-settings',
            array($this, 'display_validation_settings')
        );
        
        add_submenu_page(
            'wvp-settings',
            __('Rendimiento', 'wvp'),
            __('Rendimiento', 'wvp'),
            'manage_woocommerce',
            'wvp-performance-settings',
            array($this, 'display_performance_settings')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        // Registrar todas las opciones
        foreach ($this->config as $key => $value) {
            register_setting('wvp_settings', 'wvp_' . $key);
        }
    }
    
    /**
     * Cargar scripts y estilos del admin
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wvp-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wvp-admin-config',
            WVP_PLUGIN_URL . 'assets/css/admin-config.css',
            array(),
            WVP_VERSION
        );
        
        wp_enqueue_script(
            'wvp-admin-config',
            WVP_PLUGIN_URL . 'assets/js/admin-config.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        wp_localize_script('wvp-admin-config', 'wvp_config', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_config_nonce'),
            'strings' => array(
                'saving' => __('Guardando...', 'wvp'),
                'saved' => __('Configuración guardada', 'wvp'),
                'error' => __('Error al guardar', 'wvp'),
                'confirm_reset' => __('¿Está seguro de que desea resetear la configuración?', 'wvp'),
                'confirm_export' => __('¿Está seguro de que desea exportar la configuración?', 'wvp'),
                'confirm_import' => __('¿Está seguro de que desea importar la configuración?', 'wvp')
            )
        ));
    }
    
    /**
     * Mostrar página de configuración principal
     */
    public function display_settings_page() {
        ?>
        <div class="wrap wvp-config-page">
            <h1><?php _e('Configuración - WooCommerce Venezuela Pro', 'wvp'); ?></h1>
            
            <div class="wvp-config-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'wvp'); ?></a>
                    <a href="#bcv" class="nav-tab"><?php _e('BCV', 'wvp'); ?></a>
                    <a href="#igtf" class="nav-tab"><?php _e('IGTF', 'wvp'); ?></a>
                    <a href="#validation" class="nav-tab"><?php _e('Validaciones', 'wvp'); ?></a>
                    <a href="#features" class="nav-tab"><?php _e('Funcionalidades', 'wvp'); ?></a>
                    <a href="#notifications" class="nav-tab"><?php _e('Notificaciones', 'wvp'); ?></a>
                    <a href="#security" class="nav-tab"><?php _e('Seguridad', 'wvp'); ?></a>
                    <a href="#performance" class="nav-tab"><?php _e('Rendimiento', 'wvp'); ?></a>
                </nav>
                
                <form method="post" action="options.php" id="wvp-config-form">
                    <?php settings_fields('wvp_settings'); ?>
                    
                    <div id="general" class="tab-content active">
                        <?php $this->display_general_settings(); ?>
                    </div>
                    
                    <div id="bcv" class="tab-content">
                        <?php $this->display_bcv_settings(); ?>
                    </div>
                    
                    <div id="igtf" class="tab-content">
                        <?php $this->display_igtf_settings(); ?>
                    </div>
                    
                    <div id="validation" class="tab-content">
                        <?php $this->display_validation_settings(); ?>
                    </div>
                    
                    <div id="features" class="tab-content">
                        <?php $this->display_features_settings(); ?>
                    </div>
                    
                    <div id="notifications" class="tab-content">
                        <?php $this->display_notifications_settings(); ?>
                    </div>
                    
                    <div id="security" class="tab-content">
                        <?php $this->display_security_settings(); ?>
                    </div>
                    
                    <div id="performance" class="tab-content">
                        <?php $this->display_performance_settings(); ?>
                    </div>
                    
                    <div class="wvp-config-actions">
                        <button type="submit" class="button button-primary"><?php _e('Guardar Configuración', 'wvp'); ?></button>
                        <button type="button" class="button" id="wvp-reset-config"><?php _e('Resetear', 'wvp'); ?></button>
                        <button type="button" class="button" id="wvp-export-config"><?php _e('Exportar', 'wvp'); ?></button>
                        <button type="button" class="button" id="wvp-import-config"><?php _e('Importar', 'wvp'); ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar configuración general
     */
    private function display_general_settings() {
        ?>
        <h2><?php _e('Configuración General', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Habilitar Plugin', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_plugin_enabled" value="yes" <?php checked($this->config['plugin_enabled'], 'yes'); ?>>
                        <?php _e('Activar WooCommerce Venezuela Pro', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Modo Debug', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_debug_mode" value="yes" <?php checked($this->config['debug_mode'], 'yes'); ?>>
                        <?php _e('Activar modo debug (solo para desarrollo)', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Duración del Caché (segundos)', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_cache_duration" value="<?php echo esc_attr($this->config['cache_duration']); ?>" min="60" max="86400">
                    <p class="description"><?php _e('Tiempo de vida del caché en segundos (60-86400)', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Actualización Automática de Tasas', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_auto_update_rates" value="yes" <?php checked($this->config['auto_update_rates'], 'yes'); ?>>
                        <?php _e('Actualizar tasas de cambio automáticamente', 'wvp'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Mostrar configuración de BCV
     */
    private function display_bcv_settings() {
        ?>
        <h2><?php _e('Configuración de BCV', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Habilitar BCV', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_bcv_enabled" value="yes" <?php checked($this->config['bcv_enabled'], 'yes'); ?>>
                        <?php _e('Activar integración con BCV', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Duración del Caché BCV (segundos)', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_bcv_cache_duration" value="<?php echo esc_attr($this->config['bcv_cache_duration']); ?>" min="300" max="86400">
                    <p class="description"><?php _e('Tiempo de vida del caché de tasas BCV (300-86400)', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Tasa de Respaldo', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_bcv_fallback_rate" value="<?php echo esc_attr($this->config['bcv_fallback_rate']); ?>" step="0.01" min="0">
                    <p class="description"><?php _e('Tasa de cambio de respaldo si BCV no está disponible', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Intentos de Reintento', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_bcv_retry_attempts" value="<?php echo esc_attr($this->config['bcv_retry_attempts']); ?>" min="1" max="10">
                    <p class="description"><?php _e('Número de intentos para obtener tasa del BCV (1-10)', 'wvp'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Mostrar configuración de IGTF
     */
    private function display_igtf_settings() {
        ?>
        <h2><?php _e('Configuración de IGTF', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Habilitar IGTF', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_igtf_enabled" value="yes" <?php checked($this->config['igtf_enabled'], 'yes'); ?>>
                        <?php _e('Activar IGTF', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Tasa de IGTF (%)', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_igtf_rate" value="<?php echo esc_attr($this->config['igtf_rate']); ?>" step="0.01" min="0" max="100">
                    <p class="description"><?php _e('Porcentaje de IGTF a aplicar', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Monto Mínimo', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_igtf_min_amount" value="<?php echo esc_attr($this->config['igtf_min_amount']); ?>" step="0.01" min="0">
                    <p class="description"><?php _e('Monto mínimo para aplicar IGTF (0 = sin límite)', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Monto Máximo', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_igtf_max_amount" value="<?php echo esc_attr($this->config['igtf_max_amount']); ?>" step="0.01" min="0">
                    <p class="description"><?php _e('Monto máximo para aplicar IGTF (0 = sin límite)', 'wvp'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Mostrar configuración de validaciones
     */
    private function display_validation_settings() {
        ?>
        <h2><?php _e('Configuración de Validaciones', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Monto Mínimo del Pedido', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_min_order_amount" value="<?php echo esc_attr($this->config['min_order_amount']); ?>" step="0.01" min="0">
                    <p class="description"><?php _e('Monto mínimo para procesar un pedido (0 = sin límite)', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Monto Máximo del Pedido', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_max_order_amount" value="<?php echo esc_attr($this->config['max_order_amount']); ?>" step="0.01" min="0">
                    <p class="description"><?php _e('Monto máximo para procesar un pedido (0 = sin límite)', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Requerir Cédula/RIF', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_require_cedula_rif" value="yes" <?php checked($this->config['require_cedula_rif'], 'yes'); ?>>
                        <?php _e('Cédula o RIF es obligatorio', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Validar Teléfono', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_validate_phone" value="yes" <?php checked($this->config['validate_phone'], 'yes'); ?>>
                        <?php _e('Validar formato de teléfono venezolano', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Validar Email', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_validate_email" value="yes" <?php checked($this->config['validate_email'], 'yes'); ?>>
                        <?php _e('Validar formato de email', 'wvp'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Mostrar configuración de funcionalidades
     */
    private function display_features_settings() {
        ?>
        <h2><?php _e('Configuración de Funcionalidades', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Switcher de Moneda', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_currency_switcher_enabled" value="yes" <?php checked($this->config['currency_switcher_enabled'], 'yes'); ?>>
                        <?php _e('Activar switcher de moneda (USD ↔ VES)', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Desglose Dual', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_dual_breakdown_enabled" value="yes" <?php checked($this->config['dual_breakdown_enabled'], 'yes'); ?>>
                        <?php _e('Mostrar precios en USD y VES', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Facturación Híbrida', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_hybrid_invoicing_enabled" value="yes" <?php checked($this->config['hybrid_invoicing_enabled'], 'yes'); ?>>
                        <?php _e('Generar facturas en VES con nota USD', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Display de Precios', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_price_display_enabled" value="yes" <?php checked($this->config['price_display_enabled'], 'yes'); ?>>
                        <?php _e('Mostrar conversión automática de precios', 'wvp'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Mostrar configuración de notificaciones
     */
    private function display_notifications_settings() {
        ?>
        <h2><?php _e('Configuración de Notificaciones', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Notificaciones por Email', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_email_notifications" value="yes" <?php checked($this->config['email_notifications'], 'yes'); ?>>
                        <?php _e('Enviar notificaciones por email', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Notificaciones por WhatsApp', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_whatsapp_notifications" value="yes" <?php checked($this->config['whatsapp_notifications'], 'yes'); ?>>
                        <?php _e('Enviar notificaciones por WhatsApp', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Notificaciones de Admin', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_admin_notifications" value="yes" <?php checked($this->config['admin_notifications'], 'yes'); ?>>
                        <?php _e('Notificar al administrador', 'wvp'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Mostrar configuración de seguridad
     */
    private function display_security_settings() {
        ?>
        <h2><?php _e('Configuración de Seguridad', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Rate Limiting', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_rate_limiting_enabled" value="yes" <?php checked($this->config['rate_limiting_enabled'], 'yes'); ?>>
                        <?php _e('Activar limitación de velocidad', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Intentos Máximos', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_rate_limit_attempts" value="<?php echo esc_attr($this->config['rate_limit_attempts']); ?>" min="1" max="100">
                    <p class="description"><?php _e('Número máximo de intentos por período', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Período de Limitación (segundos)', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_rate_limit_period" value="<?php echo esc_attr($this->config['rate_limit_period']); ?>" min="60" max="3600">
                    <p class="description"><?php _e('Período de tiempo para la limitación (60-3600)', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Log de Seguridad', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_security_logging" value="yes" <?php checked($this->config['security_logging'], 'yes'); ?>>
                        <?php _e('Registrar eventos de seguridad', 'wvp'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Mostrar configuración de rendimiento
     */
    private function display_performance_settings() {
        ?>
        <h2><?php _e('Configuración de Rendimiento', 'wvp'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Habilitar Caché', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_enable_caching" value="yes" <?php checked($this->config['enable_caching'], 'yes'); ?>>
                        <?php _e('Activar sistema de caché', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('TTL del Caché (segundos)', 'wvp'); ?></th>
                <td>
                    <input type="number" name="wvp_cache_ttl" value="<?php echo esc_attr($this->config['cache_ttl']); ?>" min="60" max="86400">
                    <p class="description"><?php _e('Tiempo de vida del caché (60-86400)', 'wvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Carga Perezosa', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_lazy_loading" value="yes" <?php checked($this->config['lazy_loading'], 'yes'); ?>>
                        <?php _e('Activar carga perezosa de recursos', 'wvp'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Minificar Assets', 'wvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvp_minify_assets" value="yes" <?php checked($this->config['minify_assets'], 'yes'); ?>>
                        <?php _e('Minificar archivos CSS y JavaScript', 'wvp'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Guardar configuración
     */
    public function save_config() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_config_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Procesar configuración
        $new_config = array();
        foreach ($this->config as $key => $value) {
            if (isset($_POST['wvp_' . $key])) {
                $new_config[$key] = sanitize_text_field($_POST['wvp_' . $key]);
            } else {
                $new_config[$key] = 'no';
            }
        }
        
        // Guardar configuración
        foreach ($new_config as $key => $value) {
            update_option('wvp_' . $key, $value);
        }
        
        // Actualizar configuración local
        $this->config = $new_config;
        
        // Respuesta
        wp_send_json_success(array(
            'message' => __('Configuración guardada correctamente', 'wvp')
        ));
    }
    
    /**
     * Resetear configuración
     */
    public function reset_config() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_config_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Resetear a valores por defecto
        $default_config = array(
            'plugin_enabled' => 'yes',
            'debug_mode' => 'no',
            'cache_duration' => 3600,
            'auto_update_rates' => 'yes',
            'bcv_enabled' => 'yes',
            'bcv_cache_duration' => 3600,
            'bcv_fallback_rate' => 36.0,
            'bcv_retry_attempts' => 3,
            'igtf_enabled' => 'yes',
            'igtf_rate' => 3.0,
            'igtf_min_amount' => 0,
            'igtf_max_amount' => 0,
            'igtf_payment_methods' => array('wvp_efectivo'),
            'min_order_amount' => 0,
            'max_order_amount' => 0,
            'require_cedula_rif' => 'yes',
            'validate_phone' => 'yes',
            'validate_email' => 'yes',
            'currency_switcher_enabled' => 'yes',
            'dual_breakdown_enabled' => 'yes',
            'hybrid_invoicing_enabled' => 'yes',
            'price_display_enabled' => 'yes',
            'email_notifications' => 'yes',
            'whatsapp_notifications' => 'no',
            'admin_notifications' => 'yes',
            'rate_limiting_enabled' => 'yes',
            'rate_limit_attempts' => 5,
            'rate_limit_period' => 300,
            'security_logging' => 'yes',
            'enable_caching' => 'yes',
            'cache_ttl' => 3600,
            'lazy_loading' => 'yes',
            'minify_assets' => 'no'
        );
        
        // Guardar configuración por defecto
        foreach ($default_config as $key => $value) {
            update_option('wvp_' . $key, $value);
        }
        
        // Actualizar configuración local
        $this->config = $default_config;
        
        // Respuesta
        wp_send_json_success(array(
            'message' => __('Configuración reseteada correctamente', 'wvp')
        ));
    }
    
    /**
     * Exportar configuración
     */
    public function export_config() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_config_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Generar archivo de configuración
        $config_data = array(
            'version' => WVP_VERSION,
            'timestamp' => current_time('mysql'),
            'config' => $this->config
        );
        
        $filename = 'wvp-config-' . date('Y-m-d-H-i-s') . '.json';
        
        // Enviar archivo
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($config_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Importar configuración
     */
    public function import_config() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_config_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Procesar archivo subido
        if (!isset($_FILES['config_file']) || $_FILES['config_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array(
                'message' => __('Error al subir archivo', 'wvp')
            ));
        }
        
        $file_content = file_get_contents($_FILES['config_file']['tmp_name']);
        $config_data = json_decode($file_content, true);
        
        if (!$config_data || !isset($config_data['config'])) {
            wp_send_json_error(array(
                'message' => __('Archivo de configuración inválido', 'wvp')
            ));
        }
        
        // Validar configuración
        $imported_config = $config_data['config'];
        foreach ($imported_config as $key => $value) {
            if (!array_key_exists($key, $this->config)) {
                unset($imported_config[$key]);
            }
        }
        
        // Guardar configuración importada
        foreach ($imported_config as $key => $value) {
            update_option('wvp_' . $key, $value);
        }
        
        // Actualizar configuración local
        $this->config = array_merge($this->config, $imported_config);
        
        // Respuesta
        wp_send_json_success(array(
            'message' => __('Configuración importada correctamente', 'wvp')
        ));
    }
    
    /**
     * Obtener configuración actual
     */
    public function get_config() {
        return $this->config;
    }
    
    /**
     * Obtener valor de configuración
     */
    public function get($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
    
    /**
     * Establecer valor de configuración
     */
    public function set($key, $value) {
        if (array_key_exists($key, $this->config)) {
            $this->config[$key] = $value;
            update_option('wvp_' . $key, $value);
        }
    }
}
