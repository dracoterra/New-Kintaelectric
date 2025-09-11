<?php
/**
 * Administración Reestructurada con Pestañas Funcionales
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Admin_Restructured {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Pestaña actual
     * 
     * @var string
     */
    private $current_tab;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wvp_save_tab_settings', array($this, 'save_tab_settings'));
        add_action('wp_ajax_wvp_get_tab_content', array($this, 'get_tab_content'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WooCommerce Venezuela Pro', 'wvp'),
            __('Venezuela Pro', 'wvp'),
            'manage_woocommerce',
            'wvp-dashboard',
            array($this, 'display_dashboard'),
            'dashicons-admin-site-alt3',
            56
        );
        
        // Submenús
        add_submenu_page(
            'wvp-dashboard',
            __('Dashboard', 'wvp'),
            __('Dashboard', 'wvp'),
            'manage_woocommerce',
            'wvp-dashboard',
            array($this, 'display_dashboard')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Configuraciones', 'wvp'),
            __('Configuraciones', 'wvp'),
            'manage_woocommerce',
            'wvp-settings',
            array($this, 'display_settings')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Pasarelas de Pago', 'wvp'),
            __('Pasarelas de Pago', 'wvp'),
            'manage_woocommerce',
            'wvp-payment-gateways',
            array($this, 'display_payment_gateways')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Sistema Fiscal', 'wvp'),
            __('Sistema Fiscal', 'wvp'),
            'manage_woocommerce',
            'wvp-fiscal',
            array($this, 'display_fiscal')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Envíos y Zonas', 'wvp'),
            __('Envíos y Zonas', 'wvp'),
            'manage_woocommerce',
            'wvp-shipping',
            array($this, 'display_shipping')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Notificaciones', 'wvp'),
            __('Notificaciones', 'wvp'),
            'manage_woocommerce',
            'wvp-notifications',
            array($this, 'display_notifications')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Monitoreo', 'wvp'),
            __('Monitoreo', 'wvp'),
            'manage_woocommerce',
            'wvp-monitoring',
            array($this, 'display_monitoring')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Reportes', 'wvp'),
            __('Reportes', 'wvp'),
            'manage_woocommerce',
            'wvp-reports',
            array($this, 'display_reports')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Monitor de Errores', 'wvp'),
            __('Monitor de Errores', 'wvp'),
            'manage_woocommerce',
            'wvp-error-monitor',
            array($this, 'display_error_monitor')
        );
        
        // Añadir página de prueba de checkboxes (temporal)
        add_submenu_page(
            'wvp-dashboard',
            __('Prueba Checkboxes', 'wvp'),
            __('Prueba Checkboxes', 'wvp'),
            'manage_options',
            'wvp-test-checkboxes',
            array($this, 'display_test_checkboxes')
        );
        
        // Añadir página de debug del formulario principal (temporal)
        add_submenu_page(
            'wvp-dashboard',
            __('Debug Formulario', 'wvp'),
            __('Debug Formulario', 'wvp'),
            'manage_options',
            'wvp-debug-main-form',
            array($this, 'display_debug_main_form')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Apariencia', 'wvp'),
            __('Apariencia', 'wvp'),
            'manage_woocommerce',
            'wvp-appearance',
            array($this, 'display_appearance')
        );
        
        add_submenu_page(
            'wvp-dashboard',
            __('Ayuda', 'wvp'),
            __('Ayuda', 'wvp'),
            'manage_woocommerce',
            'wvp-help',
            array($this, 'display_help')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        // Configuraciones generales
        register_setting('wvp_general_settings', 'wvp_general_settings', array(
            'sanitize_callback' => array($this, 'sanitize_general_settings')
        ));
        register_setting('wvp_payment_settings', 'wvp_payment_settings');
        register_setting('wvp_fiscal_settings', 'wvp_fiscal_settings');
        register_setting('wvp_shipping_settings', 'wvp_shipping_settings');
        register_setting('wvp_notification_settings', 'wvp_notification_settings');
        register_setting('wvp_appearance_settings', 'wvp_display_style');
        register_setting('wvp_appearance_settings', 'wvp_primary_color');
        register_setting('wvp_appearance_settings', 'wvp_secondary_color');
        register_setting('wvp_appearance_settings', 'wvp_success_color');
        register_setting('wvp_appearance_settings', 'wvp_warning_color');
        register_setting('wvp_appearance_settings', 'wvp_font_family');
        register_setting('wvp_appearance_settings', 'wvp_font_size');
        register_setting('wvp_appearance_settings', 'wvp_font_weight');
        register_setting('wvp_appearance_settings', 'wvp_text_transform');
        register_setting('wvp_appearance_settings', 'wvp_padding');
        register_setting('wvp_appearance_settings', 'wvp_margin');
        register_setting('wvp_appearance_settings', 'wvp_border_radius');
        register_setting('wvp_appearance_settings', 'wvp_shadow');
        
        // Añadir callback para procesar configuraciones
        add_action('update_option_wvp_general_settings', array($this, 'process_general_settings'), 10, 2);
        
        // Hook para procesar formulario nativo de WordPress
        add_action('admin_init', array($this, 'process_form_submission'));
        
        // Deshabilitar AJAX temporalmente para evitar errores 400
        // add_action('wp_ajax_wvp_save_tab_settings', array($this, 'save_tab_settings'));
        // add_action('wp_ajax_wvp_get_tab_content', array($this, 'get_tab_content'));
    }
    
    /**
     * Procesar configuraciones generales
     */
    public function process_general_settings($old_value, $new_value) {
        // Procesar checkbox de mostrar IGTF
        if (isset($new_value['show_igtf']) && $new_value['show_igtf'] === '1') {
            update_option('wvp_show_igtf', '1');
        } else {
            update_option('wvp_show_igtf', '0');
        }
        
        // Procesar checkbox de habilitar IGTF
        if (isset($new_value['igtf_enabled']) && $new_value['igtf_enabled'] === 'yes') {
            update_option('wvp_igtf_enabled', 'yes');
        } else {
            update_option('wvp_igtf_enabled', 'no');
        }
        
        // Procesar otros campos
        if (isset($new_value['price_reference_format'])) {
            update_option('wvp_price_reference_format', sanitize_text_field($new_value['price_reference_format']));
        }
        
        if (isset($new_value['igtf_rate'])) {
            update_option('wvp_igtf_rate', floatval($new_value['igtf_rate']));
        }
        
        // Forzar actualización de caché
        wp_cache_delete('wvp_show_igtf', 'options');
        wp_cache_delete('wvp_igtf_enabled', 'options');
    }
    
    /**
     * Procesar envío de formulario nativo de WordPress
     */
    public function process_form_submission() {
        // Solo procesar si estamos en la página correcta
        if (!isset($_POST['option_page']) || $_POST['option_page'] !== 'wvp_general_settings') {
            return;
        }
        
        // Verificar nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wvp_general_settings-options')) {
            return;
        }
        
        // Procesar checkboxes manualmente
        $show_igtf = isset($_POST['wvp_general_settings']['show_igtf']) ? '1' : '0';
        $igtf_enabled = isset($_POST['wvp_general_settings']['igtf_enabled']) ? 'yes' : 'no';
        
        // Procesar otros campos
        $igtf_rate = isset($_POST['wvp_general_settings']['igtf_rate']) ? floatval($_POST['wvp_general_settings']['igtf_rate']) : 3.0;
        $price_reference_format = isset($_POST['wvp_general_settings']['price_reference_format']) ? sanitize_text_field($_POST['wvp_general_settings']['price_reference_format']) : 'USD';
        
        // Guardar todas las opciones
        update_option('wvp_show_igtf', $show_igtf);
        update_option('wvp_igtf_enabled', $igtf_enabled);
        update_option('wvp_igtf_rate', $igtf_rate);
        update_option('wvp_price_reference_format', $price_reference_format);
        
        // Crear/actualizar configuraciones generales
        $general_settings = array(
            'show_igtf' => $show_igtf,
            'igtf_enabled' => $igtf_enabled,
            'igtf_rate' => $igtf_rate,
            'price_reference_format' => $price_reference_format
        );
        update_option('wvp_general_settings', $general_settings);
        
        // Limpiar caché
        wp_cache_delete('wvp_show_igtf', 'options');
        wp_cache_delete('wvp_igtf_enabled', 'options');
        wp_cache_delete('wvp_igtf_rate', 'options');
        wp_cache_delete('wvp_price_reference_format', 'options');
        wp_cache_delete('wvp_general_settings', 'options');
        
        // Mostrar mensaje de éxito
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Configuraciones guardadas correctamente.', 'wvp') . '</p></div>';
        });
    }
    
    /**
     * Sanitizar configuraciones generales
     */
    public function sanitize_general_settings($input) {
        // Procesar checkboxes - si no están marcados, establecer como "0" o "no"
        $sanitized = array();
        
        // Mostrar IGTF
        $sanitized['show_igtf'] = isset($input['show_igtf']) && $input['show_igtf'] === '1' ? '1' : '0';
        
        // Habilitar IGTF
        $sanitized['igtf_enabled'] = isset($input['igtf_enabled']) && $input['igtf_enabled'] === 'yes' ? 'yes' : 'no';
        
        // Tasa IGTF
        $sanitized['igtf_rate'] = isset($input['igtf_rate']) ? floatval($input['igtf_rate']) : 3.0;
        
        // Formato de referencia de precio
        $sanitized['price_reference_format'] = isset($input['price_reference_format']) ? sanitize_text_field($input['price_reference_format']) : 'USD';
        
        // Guardar en opciones individuales también
        update_option('wvp_show_igtf', $sanitized['show_igtf']);
        update_option('wvp_igtf_enabled', $sanitized['igtf_enabled']);
        update_option('wvp_igtf_rate', $sanitized['igtf_rate']);
        update_option('wvp_price_reference_format', $sanitized['price_reference_format']);
        
        return $sanitized;
    }
    
    /**
     * Mostrar dashboard principal
     */
    public function display_dashboard() {
        $this->current_tab = 'dashboard';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar configuraciones
     */
    public function display_settings() {
        $this->current_tab = 'settings';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar pasarelas de pago
     */
    public function display_payment_gateways() {
        $this->current_tab = 'payment-gateways';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar sistema fiscal
     */
    public function display_fiscal() {
        $this->current_tab = 'fiscal';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar configuración de apariencia
     */
    public function display_appearance() {
        $this->current_tab = 'appearance';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar envíos y zonas
     */
    public function display_shipping() {
        $this->current_tab = 'shipping';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar notificaciones
     */
    public function display_notifications() {
        $this->current_tab = 'notifications';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar monitoreo
     */
    public function display_monitoring() {
        $this->current_tab = 'monitoring';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar ayuda
     */
    public function display_help() {
        $this->current_tab = 'help';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar página de administración
     */
    private function display_admin_page() {
        ?>
        <div class="wrap wvp-admin-container">
            <div class="wvp-admin-header">
                <h1 class="wvp-admin-title">
                    <span class="dashicons dashicons-admin-site-alt3"></span>
                    <?php _e('WooCommerce Venezuela Pro', 'wvp'); ?>
                </h1>
                <div class="wvp-admin-actions">
                    <button type="button" class="button button-secondary" id="wvp-refresh-status">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Actualizar Estado', 'wvp'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Navegación por pestañas -->
            <nav class="wvp-admin-nav">
                <ul class="wvp-nav-tabs">
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'dashboard' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-dashboard'); ?>">
                            <span class="dashicons dashicons-dashboard"></span>
                            <?php _e('Dashboard', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'settings' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-settings'); ?>">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php _e('Configuraciones', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'payment-gateways' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-payment-gateways'); ?>">
                            <span class="dashicons dashicons-money-alt"></span>
                            <?php _e('Pasarelas de Pago', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'fiscal' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-fiscal'); ?>">
                            <span class="dashicons dashicons-media-document"></span>
                            <?php _e('Sistema Fiscal', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'shipping' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-shipping'); ?>">
                            <span class="dashicons dashicons-location"></span>
                            <?php _e('Envíos y Zonas', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'notifications' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-notifications'); ?>">
                            <span class="dashicons dashicons-email-alt"></span>
                            <?php _e('Notificaciones', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'monitoring' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-monitoring'); ?>">
                            <span class="dashicons dashicons-chart-line"></span>
                            <?php _e('Monitoreo', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'appearance' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-appearance'); ?>">
                            <span class="dashicons dashicons-admin-appearance"></span>
                            <?php _e('Apariencia', 'wvp'); ?>
                        </a>
                    </li>
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'help' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-help'); ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                            <?php _e('Ayuda', 'wvp'); ?>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Contenido de la pestaña actual -->
            <div class="wvp-admin-content">
                <?php $this->display_tab_content(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de la pestaña actual
     */
    private function display_tab_content() {
        switch ($this->current_tab) {
            case 'dashboard':
                $this->display_dashboard_content();
                break;
            case 'settings':
                $this->display_settings_content();
                break;
            case 'payment-gateways':
                $this->display_payment_gateways_content();
                break;
            case 'fiscal':
                $this->display_fiscal_content();
                break;
            case 'shipping':
                $this->display_shipping_content();
                break;
            case 'notifications':
                $this->display_notifications_content();
                break;
                case 'monitoring':
                    $this->display_monitoring_content();
                    break;
                case 'appearance':
                    $this->display_appearance_content();
                    break;
                case 'help':
                    $this->display_help_content();
                    break;
                case 'reports':
                    $this->display_reports_content();
                    break;
                case 'error-monitor':
                    $this->display_error_monitor_content();
                    break;
        }
    }
    
    /**
     * Mostrar contenido del dashboard
     */
    private function display_dashboard_content() {
        $error_monitor = new WVP_Error_Monitor();
        $error_summary = $error_monitor->get_error_summary();
        ?>
        <div class="wvp-dashboard">
            <!-- Estado del sistema -->
            <div class="wvp-status-cards">
                <div class="wvp-status-card wvp-status-success">
                    <div class="wvp-status-icon">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                    <div class="wvp-status-content">
                        <h3><?php _e('Estado del Sistema', 'wvp'); ?></h3>
                        <p><?php _e('Plugin funcionando correctamente', 'wvp'); ?></p>
                    </div>
                </div>
                
                <div class="wvp-status-card wvp-status-info">
                    <div class="wvp-status-icon">
                        <span class="dashicons dashicons-chart-bar"></span>
                    </div>
                    <div class="wvp-status-content">
                        <h3><?php _e('Errores Hoy', 'wvp'); ?></h3>
                        <p><?php echo $error_summary['errors_today']; ?> <?php _e('errores registrados', 'wvp'); ?></p>
                    </div>
                </div>
                
                <div class="wvp-status-card wvp-status-warning">
                    <div class="wvp-status-icon">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <div class="wvp-status-content">
                        <h3><?php _e('Tasa BCV', 'wvp'); ?></h3>
                        <p>
                            <?php
                            $rate = WVP_BCV_Integrator::get_rate();
                            if ($rate) {
                                echo number_format($rate, 2, ',', '.') . ' Bs./USD';
                            } else {
                                _e('No disponible', 'wvp');
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Acciones rápidas -->
            <div class="wvp-quick-actions">
                <h2><?php _e('Acciones Rápidas', 'wvp'); ?></h2>
                <div class="wvp-action-buttons">
                    <a href="<?php echo admin_url('admin.php?page=wvp-settings'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php _e('Configuraciones', 'wvp'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wvp-payment-gateways'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php _e('Pasarelas de Pago', 'wvp'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wvp-monitoring'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-chart-line"></span>
                        <?php _e('Monitoreo', 'wvp'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Información del sistema -->
            <div class="wvp-system-info">
                <h2><?php _e('Información del Sistema', 'wvp'); ?></h2>
                <div class="wvp-info-grid">
                    <div class="wvp-info-item">
                        <strong><?php _e('WordPress:', 'wvp'); ?></strong>
                        <span><?php echo get_bloginfo('version'); ?></span>
                    </div>
                    <div class="wvp-info-item">
                        <strong><?php _e('WooCommerce:', 'wvp'); ?></strong>
                        <span><?php echo class_exists('WooCommerce') ? WC()->version : __('No instalado', 'wvp'); ?></span>
                    </div>
                    <div class="wvp-info-item">
                        <strong><?php _e('PHP:', 'wvp'); ?></strong>
                        <span><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="wvp-info-item">
                        <strong><?php _e('BCV Dólar Tracker:', 'wvp'); ?></strong>
                        <span><?php echo is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php') ? __('Activo', 'wvp') : __('Inactivo', 'wvp'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de configuraciones
     */
    private function display_settings_content() {
        ?>
        <div class="wvp-settings">
            <h2><?php _e('Configuraciones Generales', 'wvp'); ?></h2>
            <form method="post" action="options.php" class="wvp-settings-form">
                <?php
                settings_fields('wvp_general_settings');
                do_settings_sections('wvp_general_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Formato de Referencia de Precio', 'wvp'); ?></th>
                        <td>
                            <input type="text" name="wvp_general_settings[price_reference_format]" 
                                   value="<?php echo esc_attr(get_option('wvp_price_reference_format', '(Ref. %s Bs.)')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Use %s como placeholder para el precio en bolívares.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Tasa IGTF (%)', 'wvp'); ?></th>
                        <td>
                            <input type="number" name="wvp_general_settings[igtf_rate]" 
                                   value="<?php echo esc_attr(get_option('wvp_igtf_rate', '3.0')); ?>" 
                                   step="0.1" min="0" max="100" />
                            <p class="description"><?php _e('Tasa de IGTF en porcentaje.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Mostrar IGTF', 'wvp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wvp_general_settings[show_igtf]" 
                                       value="1" <?php checked(get_option('wvp_show_igtf', '0'), '1'); ?> />
                                <?php _e('Mostrar IGTF en el checkout.', 'wvp'); ?>
                            </label>
                            <p class="description"><?php _e('Desmarca esta opción para ocultar el IGTF en el checkout.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Habilitar IGTF', 'wvp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wvp_general_settings[igtf_enabled]" 
                                       value="yes" <?php checked(get_option('wvp_igtf_enabled', 'no'), 'yes'); ?> />
                                <?php _e('Activar sistema de IGTF.', 'wvp'); ?>
                            </label>
                            <p class="description"><?php _e('Desmarca esta opción para desactivar completamente el sistema de IGTF.', 'wvp'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de pasarelas de pago
     */
    private function display_payment_gateways_content() {
        ?>
        <div class="wvp-payment-gateways">
            <h2><?php _e('Configuración de Pasarelas de Pago', 'wvp'); ?></h2>
            <p><?php _e('Configura las pasarelas de pago venezolanas desde WooCommerce → Pagos.', 'wvp'); ?></p>
            
            <div class="wvp-gateway-cards">
                <div class="wvp-gateway-card">
                    <h3><?php _e('Zelle', 'wvp'); ?></h3>
                    <p><?php _e('Transferencia digital internacional', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=wvp_zelle'); ?>" class="button">
                        <?php _e('Configurar', 'wvp'); ?>
                    </a>
                </div>
                
                <div class="wvp-gateway-card">
                    <h3><?php _e('Pago Móvil', 'wvp'); ?></h3>
                    <p><?php _e('Transferencia digital nacional', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=wvp_pago_movil'); ?>" class="button">
                        <?php _e('Configurar', 'wvp'); ?>
                    </a>
                </div>
                
                <div class="wvp-gateway-card">
                    <h3><?php _e('Cashea', 'wvp'); ?></h3>
                    <p><?php _e('Compra ahora, paga después', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=wvp_cashea'); ?>" class="button">
                        <?php _e('Configurar', 'wvp'); ?>
                    </a>
                </div>
                
                <div class="wvp-gateway-card">
                    <h3><?php _e('Efectivo USD', 'wvp'); ?></h3>
                    <p><?php _e('Pago en efectivo con IGTF', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=wvp_efectivo'); ?>" class="button">
                        <?php _e('Configurar', 'wvp'); ?>
                    </a>
                </div>
                
                <div class="wvp-gateway-card">
                    <h3><?php _e('Efectivo Bolívares', 'wvp'); ?></h3>
                    <p><?php _e('Pago en efectivo en bolívares', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=wvp_efectivo_bolivares'); ?>" class="button">
                        <?php _e('Configurar', 'wvp'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido del sistema fiscal
     */
    private function display_fiscal_content() {
        ?>
        <div class="wvp-fiscal">
            <h2><?php _e('Sistema Fiscal Venezolano', 'wvp'); ?></h2>
            <form method="post" action="options.php" class="wvp-fiscal-form">
                <?php
                settings_fields('wvp_fiscal_settings');
                do_settings_sections('wvp_fiscal_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Prefijo del Número de Control', 'wvp'); ?></th>
                        <td>
                            <input type="text" name="wvp_fiscal_settings[control_number_prefix]" 
                                   value="<?php echo esc_attr(get_option('wvp_control_number_prefix', '00-')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Prefijo para los números de control (ej: 00-).', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Próximo Número de Control', 'wvp'); ?></th>
                        <td>
                            <input type="number" name="wvp_fiscal_settings[next_control_number]" 
                                   value="<?php echo esc_attr(get_option('wvp_next_control_number', '1')); ?>" 
                                   min="1" step="1" />
                            <p class="description"><?php _e('Próximo número de control a asignar. Se incrementa automáticamente.', 'wvp'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <div class="wvp-fiscal-actions">
                <h3><?php _e('Acciones Fiscales', 'wvp'); ?></h3>
                <div class="wvp-action-buttons">
                    <a href="<?php echo admin_url('admin.php?page=wvp-reports'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php _e('Reportes Fiscales', 'wvp'); ?>
                    </a>
                    <a href="<?php echo admin_url('edit.php?post_type=shop_order'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php _e('Ver Pedidos', 'wvp'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de envíos y zonas
     */
    private function display_shipping_content() {
        ?>
        <div class="wvp-shipping">
            <h2><?php _e('Configuración de Envíos y Zonas', 'wvp'); ?></h2>
            <p><?php _e('Configura las zonas de envío y delivery local desde WooCommerce → Envíos.', 'wvp'); ?></p>
            
            <div class="wvp-shipping-cards">
                <div class="wvp-shipping-card">
                    <h3><?php _e('Delivery Local', 'wvp'); ?></h3>
                    <p><?php _e('Sistema de envío para Caracas y Miranda', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping&section=wvp_local_delivery'); ?>" class="button">
                        <?php _e('Configurar', 'wvp'); ?>
                    </a>
                </div>
                
                <div class="wvp-shipping-card">
                    <h3><?php _e('Zonas de Envío', 'wvp'); ?></h3>
                    <p><?php _e('Configurar zonas de envío venezolanas', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping'); ?>" class="button">
                        <?php _e('Configurar', 'wvp'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de notificaciones
     */
    private function display_notifications_content() {
        ?>
        <div class="wvp-notifications">
            <h2><?php _e('Configuración de Notificaciones', 'wvp'); ?></h2>
            <form method="post" action="options.php" class="wvp-notifications-form">
                <?php
                settings_fields('wvp_notification_settings');
                do_settings_sections('wvp_notification_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Plantilla de Pago Verificado', 'wvp'); ?></th>
                        <td>
                            <textarea name="wvp_notification_settings[whatsapp_payment_template]" 
                                      rows="4" cols="80" class="large-text"><?php 
                                echo esc_textarea(get_option('wvp_whatsapp_payment_template', '¡Hola {customer_name}! 🎉 Tu pago del pedido {order_number} ha sido verificado exitosamente. Estamos preparando tu envío. ¡Gracias por tu compra en {store_name}!')); 
                            ?></textarea>
                            <p class="description"><?php _e('Plantilla para notificar pago verificado.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Plantilla de Envío', 'wvp'); ?></th>
                        <td>
                            <textarea name="wvp_notification_settings[whatsapp_shipping_template]" 
                                      rows="4" cols="80" class="large-text"><?php 
                                echo esc_textarea(get_option('wvp_whatsapp_shipping_template', '¡Hola {customer_name}! 📦 Tu pedido {order_number} ha sido enviado. Puedes rastrearlo con la guía: {shipping_guide}. ¡Gracias por comprar en {store_name}!')); 
                            ?></textarea>
                            <p class="description"><?php _e('Plantilla para notificar envío realizado.', 'wvp'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de monitoreo
     */
    private function display_monitoring_content() {
        $error_monitor = new WVP_Error_Monitor();
        $error_summary = $error_monitor->get_error_summary();
        ?>
        <div class="wvp-monitoring">
            <h2><?php _e('Monitoreo del Sistema', 'wvp'); ?></h2>
            
            <div class="wvp-monitoring-cards">
                <div class="wvp-monitoring-card">
                    <h3><?php _e('Errores del Sistema', 'wvp'); ?></h3>
                    <p><?php echo $error_summary['total_errors']; ?> <?php _e('errores totales', 'wvp'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wvp-error-monitor'); ?>" class="button">
                        <?php _e('Ver Detalles', 'wvp'); ?>
                    </a>
                </div>
                
                <div class="wvp-monitoring-card">
                    <h3><?php _e('Estado de Dependencias', 'wvp'); ?></h3>
                    <p><?php _e('Verificar estado de plugins requeridos', 'wvp'); ?></p>
                    <button type="button" class="button" id="wvp-check-dependencies">
                        <?php _e('Verificar', 'wvp'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de apariencia
     */
    private function display_appearance_content() {
        $current_style = get_option('wvp_display_style', 'minimal');
        $available_styles = array(
            'minimal' => 'Minimalista',
            'modern' => 'Moderno',
            'elegant' => 'Elegante',
            'compact' => 'Compacto',
            'vintage' => 'Vintage (Retro)',
            'futuristic' => 'Futurista',
            'advanced-minimal' => 'Minimalista Avanzado'
        );
        ?>
        <div class="wvp-admin-content">
            <h2><?php _e('Control de Apariencia', 'wvp'); ?></h2>
            <p><?php _e('Personaliza la visualización de precios y conversiones en tu tienda.', 'wvp'); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields('wvp_appearance_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Estilo de Visualización', 'wvp'); ?></th>
                        <td>
                            <select name="wvp_display_style" id="wvp_display_style">
                                <?php foreach ($available_styles as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($current_style, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Selecciona el estilo de visualización de precios.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Temas de Color', 'wvp'); ?></th>
                        <td>
                            <div class="wvp-color-themes">
                                <button type="button" class="wvp-theme-btn" data-theme="default">
                                    <div class="wvp-theme-preview" style="background: linear-gradient(45deg, #007cba, #005a87);"></div>
                                    <span>Azul Clásico</span>
                                </button>
                                <button type="button" class="wvp-theme-btn" data-theme="green">
                                    <div class="wvp-theme-preview" style="background: linear-gradient(45deg, #28a745, #1e7e34);"></div>
                                    <span>Verde Natural</span>
                                </button>
                                <button type="button" class="wvp-theme-btn" data-theme="purple">
                                    <div class="wvp-theme-preview" style="background: linear-gradient(45deg, #6f42c1, #5a32a3);"></div>
                                    <span>Púrpura Elegante</span>
                                </button>
                                <button type="button" class="wvp-theme-btn" data-theme="orange">
                                    <div class="wvp-theme-preview" style="background: linear-gradient(45deg, #fd7e14, #e55100);"></div>
                                    <span>Naranja Vibrante</span>
                                </button>
                                <button type="button" class="wvp-theme-btn" data-theme="red">
                                    <div class="wvp-theme-preview" style="background: linear-gradient(45deg, #dc3545, #c82333);"></div>
                                    <span>Rojo Intenso</span>
                                </button>
                                <button type="button" class="wvp-theme-btn" data-theme="dark">
                                    <div class="wvp-theme-preview" style="background: linear-gradient(45deg, #343a40, #212529);"></div>
                                    <span>Oscuro Profesional</span>
                                </button>
                            </div>
                            <p class="description"><?php _e('Selecciona un tema de color predefinido o personaliza los colores manualmente.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Colores Personalizados', 'wvp'); ?></th>
                        <td>
                            <table class="wvp-color-settings">
                                <tr>
                                    <td>
                                        <label for="wvp_primary_color"><?php _e('Color Primario', 'wvp'); ?></label>
                                        <input type="color" id="wvp_primary_color" name="wvp_primary_color" 
                                               value="<?php echo esc_attr(get_option('wvp_primary_color', '#007cba')); ?>">
                                    </td>
                                    <td>
                                        <label for="wvp_secondary_color"><?php _e('Color Secundario', 'wvp'); ?></label>
                                        <input type="color" id="wvp_secondary_color" name="wvp_secondary_color" 
                                               value="<?php echo esc_attr(get_option('wvp_secondary_color', '#005a87')); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="wvp_success_color"><?php _e('Color de Éxito', 'wvp'); ?></label>
                                        <input type="color" id="wvp_success_color" name="wvp_success_color" 
                                               value="<?php echo esc_attr(get_option('wvp_success_color', '#28a745')); ?>">
                                    </td>
                                    <td>
                                        <label for="wvp_warning_color"><?php _e('Color de Advertencia', 'wvp'); ?></label>
                                        <input type="color" id="wvp_warning_color" name="wvp_warning_color" 
                                               value="<?php echo esc_attr(get_option('wvp_warning_color', '#ffc107')); ?>">
                                    </td>
                                </tr>
                            </table>
                            <p class="description"><?php _e('Personaliza los colores del display de precios manualmente.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Configuración de Fuentes', 'wvp'); ?></th>
                        <td>
                            <table class="wvp-font-settings">
                                <tr>
                                    <td>
                                        <label for="wvp_font_family"><?php _e('Familia de Fuente', 'wvp'); ?></label>
                                        <select id="wvp_font_family" name="wvp_font_family">
                                            <?php
                                            $font_families = array(
                                                'system' => 'Sistema (Recomendado)',
                                                'arial' => 'Arial',
                                                'helvetica' => 'Helvetica',
                                                'georgia' => 'Georgia',
                                                'times' => 'Times New Roman',
                                                'verdana' => 'Verdana',
                                                'tahoma' => 'Tahoma',
                                                'trebuchet' => 'Trebuchet MS',
                                                'courier' => 'Courier New',
                                                'monospace' => 'Monospace'
                                            );
                                            $current_font = get_option('wvp_font_family', 'system');
                                            foreach ($font_families as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_font, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <label for="wvp_font_size"><?php _e('Tamaño de Fuente', 'wvp'); ?></label>
                                        <select id="wvp_font_size" name="wvp_font_size">
                                            <?php
                                            $font_sizes = array(
                                                'small' => 'Pequeño (12px)',
                                                'medium' => 'Mediano (14px)',
                                                'large' => 'Grande (16px)',
                                                'xlarge' => 'Extra Grande (18px)',
                                                'xxlarge' => 'Muy Grande (20px)'
                                            );
                                            $current_size = get_option('wvp_font_size', 'medium');
                                            foreach ($font_sizes as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_size, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="wvp_font_weight"><?php _e('Peso de Fuente', 'wvp'); ?></label>
                                        <select id="wvp_font_weight" name="wvp_font_weight">
                                            <?php
                                            $font_weights = array(
                                                '300' => 'Ligero (300)',
                                                '400' => 'Normal (400)',
                                                '500' => 'Medio (500)',
                                                '600' => 'Semi-Bold (600)',
                                                '700' => 'Bold (700)',
                                                '800' => 'Extra Bold (800)'
                                            );
                                            $current_weight = get_option('wvp_font_weight', '400');
                                            foreach ($font_weights as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_weight, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <label for="wvp_text_transform"><?php _e('Transformación de Texto', 'wvp'); ?></label>
                                        <select id="wvp_text_transform" name="wvp_text_transform">
                                            <?php
                                            $text_transforms = array(
                                                'none' => 'Normal',
                                                'uppercase' => 'MAYÚSCULAS',
                                                'lowercase' => 'minúsculas',
                                                'capitalize' => 'Primera Letra Mayúscula'
                                            );
                                            $current_transform = get_option('wvp_text_transform', 'none');
                                            foreach ($text_transforms as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_transform, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <p class="description"><?php _e('Personaliza la tipografía del display de precios.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Configuración de Espaciado', 'wvp'); ?></th>
                        <td>
                            <table class="wvp-spacing-settings">
                                <tr>
                                    <td>
                                        <label for="wvp_padding"><?php _e('Padding Interno', 'wvp'); ?></label>
                                        <select id="wvp_padding" name="wvp_padding">
                                            <?php
                                            $padding_options = array(
                                                'none' => 'Sin padding (0px)',
                                                'small' => 'Pequeño (5px)',
                                                'medium' => 'Mediano (10px)',
                                                'large' => 'Grande (15px)',
                                                'xlarge' => 'Extra Grande (20px)'
                                            );
                                            $current_padding = get_option('wvp_padding', 'medium');
                                            foreach ($padding_options as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_padding, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <label for="wvp_margin"><?php _e('Margen Externo', 'wvp'); ?></label>
                                        <select id="wvp_margin" name="wvp_margin">
                                            <?php
                                            $margin_options = array(
                                                'none' => 'Sin margen (0px)',
                                                'small' => 'Pequeño (5px)',
                                                'medium' => 'Mediano (10px)',
                                                'large' => 'Grande (15px)',
                                                'xlarge' => 'Extra Grande (20px)'
                                            );
                                            $current_margin = get_option('wvp_margin', 'medium');
                                            foreach ($margin_options as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_margin, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="wvp_border_radius"><?php _e('Radio de Borde', 'wvp'); ?></label>
                                        <select id="wvp_border_radius" name="wvp_border_radius">
                                            <?php
                                            $border_radius_options = array(
                                                'none' => 'Sin bordes redondeados (0px)',
                                                'small' => 'Pequeño (3px)',
                                                'medium' => 'Mediano (6px)',
                                                'large' => 'Grande (12px)',
                                                'xlarge' => 'Extra Grande (20px)',
                                                'round' => 'Completamente redondeado (50px)'
                                            );
                                            $current_radius = get_option('wvp_border_radius', 'medium');
                                            foreach ($border_radius_options as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_radius, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <label for="wvp_shadow"><?php _e('Sombra', 'wvp'); ?></label>
                                        <select id="wvp_shadow" name="wvp_shadow">
                                            <?php
                                            $shadow_options = array(
                                                'none' => 'Sin sombra',
                                                'small' => 'Sombra pequeña',
                                                'medium' => 'Sombra mediana',
                                                'large' => 'Sombra grande',
                                                'glow' => 'Efecto de resplandor'
                                            );
                                            $current_shadow = get_option('wvp_shadow', 'small');
                                            foreach ($shadow_options as $value => $label) {
                                                echo '<option value="' . esc_attr($value) . '" ' . selected($current_shadow, $value, false) . '>' . esc_html($label) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <p class="description"><?php _e('Personaliza el espaciado y efectos visuales del display de precios.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Vista Previa', 'wvp'); ?></th>
                        <td>
                            <div id="wvp-style-preview" class="wvp-preview-container">
                                <div class="wvp-product-price-container wvp-<?php echo esc_attr($current_style); ?>" 
                                     id="wvp-preview-container">
                                    <div class="wvp-price-display">
                                        <span class="wvp-price-usd" style="display: block;">$15.00</span>
                                        <span class="wvp-price-ves" style="display: none;">Bs. 2.365,93</span>
                                    </div>
                                    <div class="wvp-currency-switcher" data-price-usd="15.00" data-price-ves="2365.93">
                                        <button class="wvp-currency-option active" data-currency="usd">USD</button>
                                        <button class="wvp-currency-option" data-currency="ves">VES</button>
                                    </div>
                                    <div class="wvp-price-conversion">
                                        <span class="wvp-ves-reference">Equivale a Bs. 2.365,93</span>
                                    </div>
                                    <div class="wvp-rate-info">Tasa BCV: 157,73</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'wvp')); ?>
            </form>
            
            <hr style="margin: 30px 0;">
            
            <h3><?php _e('Exportar/Importar Configuraciones', 'wvp'); ?></h3>
            <p><?php _e('Guarda tus configuraciones personalizadas o restaura configuraciones guardadas.', 'wvp'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Exportar Configuración', 'wvp'); ?></th>
                    <td>
                        <button type="button" id="wvp-export-config" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span> <?php _e('Exportar Configuración', 'wvp'); ?>
                        </button>
                        <p class="description"><?php _e('Descarga un archivo JSON con tu configuración actual.', 'wvp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Importar Configuración', 'wvp'); ?></th>
                    <td>
                        <input type="file" id="wvp-import-file" accept=".json" style="margin-bottom: 10px;">
                        <br>
                        <button type="button" id="wvp-import-config" class="button button-secondary" disabled>
                            <span class="dashicons dashicons-upload"></span> <?php _e('Importar Configuración', 'wvp'); ?>
                        </button>
                        <p class="description"><?php _e('Selecciona un archivo JSON de configuración para importar.', 'wvp'); ?></p>
                    </td>
                </tr>
            </table>
            
            <script>
            jQuery(document).ready(function($) {
                // Cambio de estilo
                $('#wvp_display_style').on('change', function() {
                    var style = $(this).val();
                    var $preview = $('#wvp-style-preview .wvp-product-price-container');
                    
                    // Remover clases de estilo anteriores
                    $preview.removeClass('wvp-minimal wvp-modern wvp-elegant wvp-compact wvp-vintage wvp-futuristic wvp-advanced-minimal');
                    
                    // Añadir nueva clase de estilo
                    $preview.addClass('wvp-' + style);
                });
                
                // Temas de color predefinidos
                $('.wvp-theme-btn').on('click', function() {
                    var theme = $(this).data('theme');
                    var $preview = $('#wvp-preview-container');
                    
                    // Remover tema anterior
                    $('.wvp-theme-btn').removeClass('active');
                    $(this).addClass('active');
                    
                    // Aplicar tema
                    var themes = {
                        'default': {
                            primary: '#007cba',
                            secondary: '#005a87',
                            success: '#28a745',
                            warning: '#ffc107'
                        },
                        'green': {
                            primary: '#28a745',
                            secondary: '#1e7e34',
                            success: '#20c997',
                            warning: '#ffc107'
                        },
                        'purple': {
                            primary: '#6f42c1',
                            secondary: '#5a32a3',
                            success: '#28a745',
                            warning: '#fd7e14'
                        },
                        'orange': {
                            primary: '#fd7e14',
                            secondary: '#e55100',
                            success: '#28a745',
                            warning: '#ffc107'
                        },
                        'red': {
                            primary: '#dc3545',
                            secondary: '#c82333',
                            success: '#28a745',
                            warning: '#ffc107'
                        },
                        'dark': {
                            primary: '#343a40',
                            secondary: '#212529',
                            success: '#28a745',
                            warning: '#ffc107'
                        }
                    };
                    
                    if (themes[theme]) {
                        // Actualizar inputs de color
                        $('#wvp_primary_color').val(themes[theme].primary);
                        $('#wvp_secondary_color').val(themes[theme].secondary);
                        $('#wvp_success_color').val(themes[theme].success);
                        $('#wvp_warning_color').val(themes[theme].warning);
                        
                        // Aplicar colores en preview
                        $preview.css('--wvp-primary-color', themes[theme].primary);
                        $preview.css('--wvp-secondary-color', themes[theme].secondary);
                        $preview.css('--wvp-success-color', themes[theme].success);
                        $preview.css('--wvp-warning-color', themes[theme].warning);
                    }
                });
                
                // Cambio de colores
                $('input[type="color"]').on('change', function() {
                    var color = $(this).val();
                    var colorType = $(this).attr('name').replace('wvp_', '').replace('_color', '');
                    var $preview = $('#wvp-preview-container');
                    
                    // Remover tema activo
                    $('.wvp-theme-btn').removeClass('active');
                    
                    // Aplicar color en tiempo real
                    $preview.css('--wvp-' + colorType + '-color', color);
                });
                
                // Cambio de fuentes
                $('#wvp_font_family, #wvp_font_size, #wvp_font_weight, #wvp_text_transform').on('change', function() {
                    var $preview = $('#wvp-preview-container');
                    var fontFamily = $('#wvp_font_family').val();
                    var fontSize = $('#wvp_font_size').val();
                    var fontWeight = $('#wvp_font_weight').val();
                    var textTransform = $('#wvp_text_transform').val();
                    
                    // Aplicar fuentes en tiempo real
                    var fontFamilyMap = {
                        'system': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        'arial': 'Arial, sans-serif',
                        'helvetica': 'Helvetica, Arial, sans-serif',
                        'georgia': 'Georgia, serif',
                        'times': 'Times New Roman, serif',
                        'verdana': 'Verdana, sans-serif',
                        'tahoma': 'Tahoma, sans-serif',
                        'trebuchet': 'Trebuchet MS, sans-serif',
                        'courier': 'Courier New, monospace',
                        'monospace': 'monospace'
                    };
                    
                    var fontSizeMap = {
                        'small': '12px',
                        'medium': '14px',
                        'large': '16px',
                        'xlarge': '18px',
                        'xxlarge': '20px'
                    };
                    
                    $preview.css({
                        'font-family': fontFamilyMap[fontFamily] || fontFamily,
                        'font-size': fontSizeMap[fontSize] || fontSize,
                        'font-weight': fontWeight,
                        'text-transform': textTransform
                    });
                });
                
                // Cambio de espaciado
                $('#wvp_padding, #wvp_margin, #wvp_border_radius, #wvp_shadow').on('change', function() {
                    var $preview = $('#wvp-preview-container');
                    var padding = $('#wvp_padding').val();
                    var margin = $('#wvp_margin').val();
                    var borderRadius = $('#wvp_border_radius').val();
                    var shadow = $('#wvp_shadow').val();
                    
                    // Aplicar espaciado en tiempo real
                    var paddingMap = {
                        'none': '0px',
                        'small': '5px',
                        'medium': '10px',
                        'large': '15px',
                        'xlarge': '20px'
                    };
                    
                    var marginMap = {
                        'none': '0px',
                        'small': '5px',
                        'medium': '10px',
                        'large': '15px',
                        'xlarge': '20px'
                    };
                    
                    var borderRadiusMap = {
                        'none': '0px',
                        'small': '3px',
                        'medium': '6px',
                        'large': '12px',
                        'xlarge': '20px',
                        'round': '50px'
                    };
                    
                    var shadowMap = {
                        'none': 'none',
                        'small': '0 2px 4px rgba(0,0,0,0.1)',
                        'medium': '0 4px 8px rgba(0,0,0,0.15)',
                        'large': '0 8px 16px rgba(0,0,0,0.2)',
                        'glow': '0 0 20px rgba(0,123,186,0.3)'
                    };
                    
                    $preview.css({
                        'padding': paddingMap[padding] || padding,
                        'margin': marginMap[margin] || margin,
                        'border-radius': borderRadiusMap[borderRadius] || borderRadius,
                        'box-shadow': shadowMap[shadow] || shadow
                    });
                });
                
                // Switcher de moneda en preview
                $('#wvp-preview-container .wvp-currency-switcher button').on('click', function(e) {
                    e.preventDefault();
                    var currency = $(this).data('currency');
                    var $container = $(this).closest('.wvp-product-price-container');
                    var $usdPrice = $container.find('.wvp-price-usd');
                    var $vesPrice = $container.find('.wvp-price-ves');
                    var $conversion = $container.find('.wvp-price-conversion');
                    
                    // Actualizar botones
                    $container.find('.wvp-currency-option').removeClass('active');
                    $(this).addClass('active');
                    
                    // Cambiar precios
                    if (currency === 'usd') {
                        $vesPrice.fadeOut(200, function() {
                            $usdPrice.fadeIn(200);
                        });
                        $conversion.fadeIn(200);
                    } else {
                        $usdPrice.fadeOut(200, function() {
                            $vesPrice.fadeIn(200);
                        });
                        $conversion.fadeOut(200);
                    }
                });
                
                // Exportar configuración
                $('#wvp-export-config').on('click', function() {
                    var config = {
                        display_style: $('#wvp_display_style').val(),
                        primary_color: $('#wvp_primary_color').val(),
                        secondary_color: $('#wvp_secondary_color').val(),
                        success_color: $('#wvp_success_color').val(),
                        warning_color: $('#wvp_warning_color').val(),
                        font_family: $('#wvp_font_family').val(),
                        font_size: $('#wvp_font_size').val(),
                        font_weight: $('#wvp_font_weight').val(),
                        text_transform: $('#wvp_text_transform').val(),
                        padding: $('#wvp_padding').val(),
                        margin: $('#wvp_margin').val(),
                        border_radius: $('#wvp_border_radius').val(),
                        shadow: $('#wvp_shadow').val(),
                        export_date: new Date().toISOString(),
                        plugin_version: '<?php echo WVP_VERSION; ?>'
                    };
                    
                    var dataStr = JSON.stringify(config, null, 2);
                    var dataBlob = new Blob([dataStr], {type: 'application/json'});
                    var url = URL.createObjectURL(dataBlob);
                    
                    var link = document.createElement('a');
                    link.href = url;
                    link.download = 'wvp-appearance-config-' + new Date().toISOString().split('T')[0] + '.json';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
                
                // Importar configuración
                $('#wvp-import-file').on('change', function() {
                    var file = this.files[0];
                    if (file) {
                        $('#wvp-import-config').prop('disabled', false);
                    } else {
                        $('#wvp-import-config').prop('disabled', true);
                    }
                });
                
                $('#wvp-import-config').on('click', function() {
                    var file = $('#wvp-import-file')[0].files[0];
                    if (!file) return;
                    
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            var config = JSON.parse(e.target.result);
                            
                            // Validar que sea una configuración válida
                            if (config.display_style && config.primary_color) {
                                // Aplicar configuración
                                $('#wvp_display_style').val(config.display_style);
                                $('#wvp_primary_color').val(config.primary_color);
                                $('#wvp_secondary_color').val(config.secondary_color);
                                $('#wvp_success_color').val(config.success_color);
                                $('#wvp_warning_color').val(config.warning_color);
                                $('#wvp_font_family').val(config.font_family);
                                $('#wvp_font_size').val(config.font_size);
                                $('#wvp_font_weight').val(config.font_weight);
                                $('#wvp_text_transform').val(config.text_transform);
                                $('#wvp_padding').val(config.padding);
                                $('#wvp_margin').val(config.margin);
                                $('#wvp_border_radius').val(config.border_radius);
                                $('#wvp_shadow').val(config.shadow);
                                
                                // Actualizar preview
                                $('#wvp_display_style').trigger('change');
                                $('input[type="color"]').trigger('change');
                                $('#wvp_font_family, #wvp_font_size, #wvp_font_weight, #wvp_text_transform').trigger('change');
                                $('#wvp_padding, #wvp_margin, #wvp_border_radius, #wvp_shadow').trigger('change');
                                
                                alert('Configuración importada exitosamente. Recuerda guardar los cambios.');
                            } else {
                                alert('Error: Archivo de configuración inválido.');
                            }
                        } catch (error) {
                            alert('Error al leer el archivo: ' + error.message);
                        }
                    };
                    reader.readAsText(file);
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de ayuda
     */
    private function display_help_content() {
        ?>
        <div class="wvp-help">
            <h2><?php _e('Ayuda y Soporte', 'wvp'); ?></h2>
            
            <div class="wvp-help-sections">
                <div class="wvp-help-section">
                    <h3><?php _e('🚀 Configuración Inicial', 'wvp'); ?></h3>
                    <ol>
                        <li><?php _e('Instalar y activar el plugin BCV Dólar Tracker', 'wvp'); ?></li>
                        <li><?php _e('Configurar las pasarelas de pago en WooCommerce', 'wvp'); ?></li>
                        <li><?php _e('Configurar Cashea (obtener API Keys)', 'wvp'); ?></li>
                        <li><?php _e('Configurar delivery local (zonas y tarifas)', 'wvp'); ?></li>
                        <li><?php _e('Establecer el prefijo y próximo número de control', 'wvp'); ?></li>
                        <li><?php _e('Configurar la tasa de IGTF si es necesaria', 'wvp'); ?></li>
                        <li><?php _e('Personalizar plantillas de WhatsApp', 'wvp'); ?></li>
                        <li><?php _e('Probar el flujo completo de compra', 'wvp'); ?></li>
                    </ol>
                </div>
                
                <div class="wvp-help-section">
                    <h3><?php _e('🔧 Solución de Problemas', 'wvp'); ?></h3>
                    <ul>
                        <li><strong><?php _e('Tasa BCV no disponible:', 'wvp'); ?></strong> <?php _e('Verificar que BCV Dólar Tracker esté activo', 'wvp'); ?></li>
                        <li><strong><?php _e('IGTF no se aplica:', 'wvp'); ?></strong> <?php _e('Verificar configuración de pasarelas de pago', 'wvp'); ?></li>
                        <li><strong><?php _e('Cashea no aparece:', 'wvp'); ?></strong> <?php _e('Verificar API Keys y montos configurados', 'wvp'); ?></li>
                        <li><strong><?php _e('Delivery local no funciona:', 'wvp'); ?></strong> <?php _e('Verificar que el cliente esté en DC o Miranda', 'wvp'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Cargar scripts del admin
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wvp-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wvp-admin-restructured',
            WVP_PLUGIN_URL . 'assets/css/admin-restructured.css',
            array(),
            WVP_VERSION
        );
        
        // CSS adicional para panel de apariencia
        if (isset($_GET['page']) && $_GET['page'] === 'wvp-appearance') {
            wp_add_inline_style('wvp-admin-restructured', '
                .wvp-color-settings {
                    border-collapse: separate;
                    border-spacing: 15px;
                }
                .wvp-color-settings td {
                    vertical-align: top;
                }
                .wvp-color-settings label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: 600;
                }
                .wvp-color-settings input[type="color"] {
                    width: 60px;
                    height: 40px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                .wvp-preview-container {
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    margin-top: 10px;
                }
                #wvp-preview-container {
                    max-width: 300px;
                    margin: 0 auto;
                }
                .wvp-preview-container .wvp-currency-switcher button {
                    cursor: pointer;
                }
                .wvp-font-settings, .wvp-spacing-settings {
                    border-collapse: separate;
                    border-spacing: 15px;
                }
                .wvp-font-settings td, .wvp-spacing-settings td {
                    vertical-align: top;
                }
                .wvp-font-settings label, .wvp-spacing-settings label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: 600;
                }
                .wvp-font-settings select, .wvp-spacing-settings select {
                    width: 100%;
                    padding: 5px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }
                .wvp-color-themes {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-bottom: 15px;
                }
                .wvp-theme-btn {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 10px;
                    border: 2px solid #ddd;
                    border-radius: 8px;
                    background: white;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    min-width: 80px;
                }
                .wvp-theme-btn:hover {
                    border-color: #007cba;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                .wvp-theme-btn.active {
                    border-color: #007cba;
                    background: #f8f9fa;
                    box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
                }
                .wvp-theme-preview {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    margin-bottom: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .wvp-theme-btn span {
                    font-size: 11px;
                    text-align: center;
                    color: #666;
                    font-weight: 500;
                }
            ');
        }
        
        // Deshabilitar AJAX temporalmente para evitar errores 400
        // wp_enqueue_script(
        //     'wvp-admin-restructured',
        //     WVP_PLUGIN_URL . 'assets/js/admin-restructured.js',
        //     array('jquery'),
        //     WVP_VERSION,
        //     true
        // );
        
        // wp_localize_script('wvp-admin-restructured', 'wvp_admin', array(
        //     'ajax_url' => admin_url('admin-ajax.php'),
        //     'nonce' => wp_create_nonce('wvp_admin_nonce'),
        //     'strings' => array(
        //         'saving' => __('Guardando...', 'wvp'),
        //         'saved' => __('Guardado correctamente', 'wvp'),
        //         'error' => __('Error al guardar', 'wvp')
        //     )
        // ));
    }
    
    /**
     * AJAX: Guardar configuraciones de pestaña
     */
    public function save_tab_settings() {
        check_ajax_referer('wvp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'wvp'));
        }
        
        $tab = sanitize_text_field($_POST['tab']);
        $settings = $_POST['settings'];
        
        // Sanitizar configuraciones según la pestaña
        switch ($tab) {
            case 'general':
                $sanitized_settings = array(
                    'price_reference_format' => sanitize_text_field($settings['price_reference_format']),
                    'igtf_rate' => floatval($settings['igtf_rate']),
                    'show_igtf' => isset($settings['show_igtf']) ? '1' : '0'
                );
                update_option('wvp_general_settings', $sanitized_settings);
                break;
                
            case 'fiscal':
                $sanitized_settings = array(
                    'control_number_prefix' => sanitize_text_field($settings['control_number_prefix']),
                    'next_control_number' => intval($settings['next_control_number'])
                );
                update_option('wvp_fiscal_settings', $sanitized_settings);
                break;
                
            case 'notifications':
                $sanitized_settings = array(
                    'whatsapp_payment_template' => sanitize_textarea_field($settings['whatsapp_payment_template']),
                    'whatsapp_shipping_template' => sanitize_textarea_field($settings['whatsapp_shipping_template'])
                );
                update_option('wvp_notification_settings', $sanitized_settings);
                break;
        }
        
        wp_send_json_success(array(
            'message' => __('Configuraciones guardadas correctamente.', 'wvp')
        ));
    }
    
    /**
     * AJAX: Obtener contenido de pestaña
     */
    public function get_tab_content() {
        check_ajax_referer('wvp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'wvp'));
        }
        
        $tab = sanitize_text_field($_POST['tab']);
        
        ob_start();
        $this->current_tab = $tab;
        $this->display_tab_content();
        $content = ob_get_clean();
        
        wp_send_json_success(array(
            'content' => $content
        ));
    }
    
    /**
     * Mostrar reportes
     */
    public function display_reports() {
        $this->current_tab = 'reports';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar monitor de errores
     */
    public function display_error_monitor() {
        $this->current_tab = 'error-monitor';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar página de prueba de checkboxes
     */
    public function display_test_checkboxes() {
        include WVP_PLUGIN_PATH . 'test-checkboxes-admin.php';
    }
    
    /**
     * Mostrar página de debug del formulario principal
     */
    public function display_debug_main_form() {
        include WVP_PLUGIN_PATH . 'debug-main-form.php';
    }
    
    /**
     * Mostrar contenido de reportes
     */
    private function display_reports_content() {
        ?>
        <div class="wvp-reports">
            <h2><?php _e('Reportes', 'wvp'); ?></h2>
            <p><?php _e('Aquí puedes ver los reportes del plugin.', 'wvp'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido del monitor de errores
     */
    private function display_error_monitor_content() {
        ?>
        <div class="wvp-error-monitor">
            <h2><?php _e('Monitor de Errores', 'wvp'); ?></h2>
            <p><?php _e('Aquí puedes monitorear los errores del plugin.', 'wvp'); ?></p>
        </div>
        <?php
    }
}
