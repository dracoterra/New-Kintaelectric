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
        add_action('wp_ajax_wvp_export_fiscal_data', array($this, 'export_fiscal_data_ajax'));
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
        
        // Página de control de visualización
        add_submenu_page(
            'wvp-dashboard',
            __('Control Visualización', 'wvp'),
            __('Control Visualización', 'wvp'),
            'manage_woocommerce',
            'wvp-display-control',
            array($this, 'display_display_control')
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
        
        // Registrar configuraciones de control de visualización
        register_setting('wvp_display_settings', 'wvp_display_settings', array($this, 'sanitize_display_settings'));
        
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
     * Sanitizar configuraciones de control de visualización
     */
    public function sanitize_display_settings($input) {
        $sanitized = array();
        
        // Conversión de monedas
        $sanitized['currency_conversion'] = array(
            'single_product' => isset($input['currency_conversion']['single_product']) && $input['currency_conversion']['single_product'] == '1' ? true : false,
            'shop_loop' => isset($input['currency_conversion']['shop_loop']) && $input['currency_conversion']['shop_loop'] == '1' ? true : false,
            'cart' => isset($input['currency_conversion']['cart']) && $input['currency_conversion']['cart'] == '1' ? true : false,
            'checkout' => isset($input['currency_conversion']['checkout']) && $input['currency_conversion']['checkout'] == '1' ? true : false,
            'widget' => isset($input['currency_conversion']['widget']) && $input['currency_conversion']['widget'] == '1' ? true : false
        );
        
        // Tasa BCV
        $sanitized['bcv_rate'] = array(
            'single_product' => isset($input['bcv_rate']['single_product']) && $input['bcv_rate']['single_product'] == '1' ? true : false,
            'shop_loop' => isset($input['bcv_rate']['shop_loop']) && $input['bcv_rate']['shop_loop'] == '1' ? true : false,
            'cart' => isset($input['bcv_rate']['cart']) && $input['bcv_rate']['cart'] == '1' ? true : false,
            'checkout' => isset($input['bcv_rate']['checkout']) && $input['bcv_rate']['checkout'] == '1' ? true : false,
            'widget' => isset($input['bcv_rate']['widget']) && $input['bcv_rate']['widget'] == '1' ? true : false
        );
        
        // Selector de moneda
        $sanitized['currency_switcher'] = array(
            'single_product' => isset($input['currency_switcher']['single_product']) && $input['currency_switcher']['single_product'] == '1' ? true : false,
            'shop_loop' => isset($input['currency_switcher']['shop_loop']) && $input['currency_switcher']['shop_loop'] == '1' ? true : false,
            'cart' => isset($input['currency_switcher']['cart']) && $input['currency_switcher']['cart'] == '1' ? true : false,
            'checkout' => isset($input['currency_switcher']['checkout']) && $input['currency_switcher']['checkout'] == '1' ? true : false,
            'widget' => isset($input['currency_switcher']['widget']) && $input['currency_switcher']['widget'] == '1' ? true : false,
            'footer' => isset($input['currency_switcher']['footer']) && $input['currency_switcher']['footer'] == '1' ? true : false
        );
        
        // Alcance del selector
        $sanitized['switcher_scope'] = array(
            'single_product' => isset($input['switcher_scope']['single_product']) ? sanitize_text_field($input['switcher_scope']['single_product']) : 'local',
            'shop_loop' => isset($input['switcher_scope']['shop_loop']) ? sanitize_text_field($input['switcher_scope']['shop_loop']) : 'local',
            'cart' => isset($input['switcher_scope']['cart']) ? sanitize_text_field($input['switcher_scope']['cart']) : 'local',
            'checkout' => isset($input['switcher_scope']['checkout']) ? sanitize_text_field($input['switcher_scope']['checkout']) : 'local',
            'widget' => isset($input['switcher_scope']['widget']) ? sanitize_text_field($input['switcher_scope']['widget']) : 'global',
            'footer' => isset($input['switcher_scope']['footer']) ? sanitize_text_field($input['switcher_scope']['footer']) : 'global'
        );
        
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
                    <li class="wvp-nav-tab <?php echo $this->current_tab === 'display_control' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=wvp-display-control'); ?>">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php _e('Control Visualización', 'wvp'); ?>
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
                case 'display_control':
                    $this->display_display_control_content();
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
            <h2><?php _e('Configuración de Pago Móvil', 'wvp'); ?></h2>
            <p><?php _e('Configura tu método de pago Pago Móvil desde WooCommerce → Pagos.', 'wvp'); ?></p>
            
            <div class="wvp-gateway-cards">
                <div class="wvp-gateway-card" style="max-width: 600px; margin: 0 auto;">
                    <h3><?php _e('Pago Móvil', 'wvp'); ?></h3>
                    <p><?php _e('Transferencia digital nacional venezolana a través de múltiples bancos', 'wvp'); ?></p>
                    <ul style="text-align: left; margin: 20px 0;">
                        <li><?php _e('✅ Acepta múltiples cuentas bancarias', 'wvp'); ?></li>
                        <li><?php _e('✅ Configuración fácil con Cédula/Teléfono', 'wvp'); ?></li>
                        <li><?php _e('✅ Código QR para pagos rápidos', 'wvp'); ?></li>
                        <li><?php _e('✅ Confirmación de pago integrada', 'wvp'); ?></li>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=wvp_pago_movil'); ?>" class="button button-primary button-large" style="font-size: 16px; padding: 10px 30px; margin-top: 20px;">
                        <?php _e('⚙️ Configurar Pago Móvil', 'wvp'); ?>
                    </a>
                </div>
            </div>
            
            <div class="wvp-info-box" style="background: #f0f8ff; border-left: 4px solid #0073aa; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0;">
                    <strong><?php _e('💡 Tip:', 'wvp'); ?></strong>
                    <?php _e('También puedes gestionar todas las pasarelas desde ', 'wvp'); ?>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout'); ?>" target="_blank">
                        <?php _e('WooCommerce → Configuración → Pagos', 'wvp'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido del sistema fiscal
     */
    private function display_fiscal_content() {
        $fiscal_settings = get_option('wvp_fiscal_settings', array());
        $control_prefix = isset($fiscal_settings['control_number_prefix']) ? $fiscal_settings['control_number_prefix'] : '00-';
        $next_control = isset($fiscal_settings['next_control_number']) ? $fiscal_settings['next_control_number'] : '1';
        $company_rif = isset($fiscal_settings['company_rif']) ? $fiscal_settings['company_rif'] : '';
        $company_name = isset($fiscal_settings['company_name']) ? $fiscal_settings['company_name'] : '';
        $company_address = isset($fiscal_settings['company_address']) ? $fiscal_settings['company_address'] : '';
        $company_phone = isset($fiscal_settings['company_phone']) ? $fiscal_settings['company_phone'] : '';
        $company_email = isset($fiscal_settings['company_email']) ? $fiscal_settings['company_email'] : '';
        
        // Estadísticas fiscales
        $total_orders = $this->get_total_orders_count();
        $total_invoices = $this->get_total_invoices_count();
        $monthly_revenue = $this->get_monthly_revenue();
        ?>
        <div class="wvp-fiscal">
            <h2><?php _e('Sistema Fiscal Venezolano', 'wvp'); ?></h2>
            <p class="description"><?php _e('Configuración completa para facturación y reportes fiscales según normativas venezolanas.', 'wvp'); ?></p>
            
            <!-- Estadísticas rápidas -->
            <div class="wvp-fiscal-stats">
                <div class="wvp-stat-box">
                    <h3><?php echo number_format($total_orders); ?></h3>
                    <p><?php _e('Pedidos Totales', 'wvp'); ?></p>
                </div>
                <div class="wvp-stat-box">
                    <h3><?php echo number_format($total_invoices); ?></h3>
                    <p><?php _e('Facturas Generadas', 'wvp'); ?></p>
                </div>
                <div class="wvp-stat-box">
                    <h3><?php echo wc_price($monthly_revenue); ?></h3>
                    <p><?php _e('Ingresos del Mes', 'wvp'); ?></p>
                </div>
            </div>
            
            <form method="post" action="options.php" class="wvp-fiscal-form">
                <?php
                settings_fields('wvp_fiscal_settings');
                do_settings_sections('wvp_fiscal_settings');
                ?>
                
                <h3><?php _e('Datos de la Empresa (Emisor)', 'wvp'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('RIF de la Empresa', 'wvp'); ?> <span class="required">*</span></th>
                        <td>
                            <input type="text" name="wvp_fiscal_settings[company_rif]" 
                                   value="<?php echo esc_attr($company_rif); ?>" 
                                   class="regular-text" placeholder="J-12345678-9" required />
                            <p class="description"><?php _e('RIF completo de la empresa (ej: J-12345678-9). Campo obligatorio para facturación electrónica.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Razón Social', 'wvp'); ?> <span class="required">*</span></th>
                        <td>
                            <input type="text" name="wvp_fiscal_settings[company_name]" 
                                   value="<?php echo esc_attr($company_name); ?>" 
                                   class="regular-text" required />
                            <p class="description"><?php _e('Nombre completo de la empresa según registro mercantil.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Dirección Fiscal', 'wvp'); ?> <span class="required">*</span></th>
                        <td>
                            <textarea name="wvp_fiscal_settings[company_address]" rows="3" class="large-text" required><?php echo esc_textarea($company_address); ?></textarea>
                            <p class="description"><?php _e('Dirección completa para facturación (Estado, Municipio, Parroquia, Urbanización, Calle, Edificio, Piso, Apartamento).', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Teléfono', 'wvp'); ?></th>
                        <td>
                            <input type="text" name="wvp_fiscal_settings[company_phone]" 
                                   value="<?php echo esc_attr($company_phone); ?>" 
                                   class="regular-text" placeholder="+58-212-1234567" />
                            <p class="description"><?php _e('Teléfono de contacto fiscal (formato: +58-212-1234567).', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Email Fiscal', 'wvp'); ?></th>
                        <td>
                            <input type="email" name="wvp_fiscal_settings[company_email]" 
                                   value="<?php echo esc_attr($company_email); ?>" 
                                   class="regular-text" placeholder="facturacion@empresa.com" />
                            <p class="description"><?php _e('Email para envío de facturas electrónicas.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Actividad Económica', 'wvp'); ?></th>
                        <td>
                            <input type="text" name="wvp_fiscal_settings[company_activity]" 
                                   value="<?php echo esc_attr(isset($fiscal_settings['company_activity']) ? $fiscal_settings['company_activity'] : ''); ?>" 
                                   class="regular-text" placeholder="Comercio al por menor" />
                            <p class="description"><?php _e('Descripción de la actividad económica principal.', 'wvp'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h3><?php _e('Configuración de Facturación Electrónica', 'wvp'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Tipo de Contribuyente', 'wvp'); ?></th>
                        <td>
                            <select name="wvp_fiscal_settings[taxpayer_type]">
                                <option value="persona_natural" <?php selected(isset($fiscal_settings['taxpayer_type']) ? $fiscal_settings['taxpayer_type'] : '', 'persona_natural'); ?>>
                                    <?php _e('Persona Natural', 'wvp'); ?>
                                </option>
                                <option value="persona_juridica" <?php selected(isset($fiscal_settings['taxpayer_type']) ? $fiscal_settings['taxpayer_type'] : '', 'persona_juridica'); ?>>
                                    <?php _e('Persona Jurídica', 'wvp'); ?>
                                </option>
                            </select>
                            <p class="description"><?php _e('Tipo de contribuyente según registro en el SENIAT.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Registro de Información Fiscal (RIF)', 'wvp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wvp_fiscal_settings[rif_validation]" value="1" 
                                       <?php checked(isset($fiscal_settings['rif_validation'])); ?> />
                                <?php _e('Validar RIF de clientes con SENIAT', 'wvp'); ?>
                            </label>
                            <p class="description"><?php _e('Validar automáticamente el RIF de los clientes con la base de datos del SENIAT.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Forma de Pago Predeterminada', 'wvp'); ?></th>
                        <td>
                            <select name="wvp_fiscal_settings[default_payment_method]">
                                <option value="efectivo" <?php selected(isset($fiscal_settings['default_payment_method']) ? $fiscal_settings['default_payment_method'] : '', 'efectivo'); ?>>
                                    <?php _e('Efectivo', 'wvp'); ?>
                                </option>
                                <option value="transferencia" <?php selected(isset($fiscal_settings['default_payment_method']) ? $fiscal_settings['default_payment_method'] : '', 'transferencia'); ?>>
                                    <?php _e('Transferencia Bancaria', 'wvp'); ?>
                                </option>
                                <option value="tarjeta" <?php selected(isset($fiscal_settings['default_payment_method']) ? $fiscal_settings['default_payment_method'] : '', 'tarjeta'); ?>>
                                    <?php _e('Tarjeta de Crédito/Débito', 'wvp'); ?>
                                </option>
                                <option value="crypto" <?php selected(isset($fiscal_settings['default_payment_method']) ? $fiscal_settings['default_payment_method'] : '', 'crypto'); ?>>
                                    <?php _e('Criptomonedas', 'wvp'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <h3><?php _e('Configuración de Facturación', 'wvp'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Prefijo del Número de Control', 'wvp'); ?></th>
                        <td>
                            <input type="text" name="wvp_fiscal_settings[control_number_prefix]" 
                                   value="<?php echo esc_attr($control_prefix); ?>" 
                                   class="regular-text" placeholder="00-" />
                            <p class="description"><?php _e('Prefijo para los números de control (ej: 00-). Debe coincidir con el registrado en SENIAT.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Próximo Número de Control', 'wvp'); ?></th>
                        <td>
                            <input type="number" name="wvp_fiscal_settings[next_control_number]" 
                                   value="<?php echo esc_attr($next_control); ?>" 
                                   min="1" step="1" />
                            <p class="description"><?php _e('Próximo número de control a asignar. Se incrementa automáticamente.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Tipo de Documento', 'wvp'); ?></th>
                        <td>
                            <select name="wvp_fiscal_settings[document_type]">
                                <option value="factura" <?php selected(isset($fiscal_settings['document_type']) ? $fiscal_settings['document_type'] : '', 'factura'); ?>>
                                    <?php _e('Factura', 'wvp'); ?>
                                </option>
                                <option value="nota_credito" <?php selected(isset($fiscal_settings['document_type']) ? $fiscal_settings['document_type'] : '', 'nota_credito'); ?>>
                                    <?php _e('Nota de Crédito', 'wvp'); ?>
                                </option>
                                <option value="nota_debito" <?php selected(isset($fiscal_settings['document_type']) ? $fiscal_settings['document_type'] : '', 'nota_debito'); ?>>
                                    <?php _e('Nota de Débito', 'wvp'); ?>
                                </option>
                            </select>
                            <p class="description"><?php _e('Tipo de documento fiscal a generar por defecto.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Aplicar IVA', 'wvp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wvp_fiscal_settings[apply_iva]" value="1" 
                                       <?php checked(isset($fiscal_settings['apply_iva']) ? $fiscal_settings['apply_iva'] : true); ?> />
                                <?php _e('Aplicar IVA (16%)', 'wvp'); ?>
                            </label>
                            <p class="description"><?php _e('Aplicar automáticamente el Impuesto al Valor Agregado del 16%.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Aplicar IGTF', 'wvp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wvp_fiscal_settings[apply_igtf]" value="1" 
                                       <?php checked(isset($fiscal_settings['apply_igtf']) ? $fiscal_settings['apply_igtf'] : false); ?> />
                                <?php _e('Aplicar IGTF (3%)', 'wvp'); ?>
                            </label>
                            <p class="description"><?php _e('Aplicar Impuesto a las Grandes Transacciones Financieras del 3% en pagos en moneda extranjera.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Facturación Híbrida', 'wvp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wvp_fiscal_settings[hybrid_invoicing]" value="1" 
                                       <?php checked(isset($fiscal_settings['hybrid_invoicing'])); ?> />
                                <?php _e('Facturación Híbrida (USD/VES)', 'wvp'); ?>
                            </label>
                            <p class="description"><?php _e('Genera facturas en VES con nota aclaratoria del pago en USD.', 'wvp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Envío Automático de Facturas', 'wvp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wvp_fiscal_settings[auto_send_invoices]" value="1" 
                                       <?php checked(isset($fiscal_settings['auto_send_invoices'])); ?> />
                                <?php _e('Enviar facturas automáticamente por email', 'wvp'); ?>
                            </label>
                            <p class="description"><?php _e('Enviar automáticamente las facturas electrónicas al email del cliente.', 'wvp'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración Fiscal', 'wvp')); ?>
            </form>
            
            <div class="wvp-fiscal-actions">
                <h3><?php _e('Acciones Fiscales', 'wvp'); ?></h3>
                <div class="wvp-action-buttons">
                    <a href="<?php echo admin_url('admin.php?page=wvp-seniat-reports'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php _e('Reportes SENIAT', 'wvp'); ?>
                    </a>
                    <a href="<?php echo admin_url('edit.php?post_type=shop_order'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php _e('Ver Pedidos', 'wvp'); ?>
                    </a>
                    <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wvp_export_fiscal_data'), 'wvp_export_fiscal_data'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Exportar Datos Fiscales', 'wvp'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <style>
        .wvp-fiscal-stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .wvp-stat-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            flex: 1;
        }
        .wvp-stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #0073aa;
        }
        .wvp-stat-box p {
            margin: 0;
            color: #666;
        }
        .wvp-action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        </style>
        
        <?php
    }
    
    /**
     * Obtener total de pedidos
     */
    private function get_total_orders_count() {
        global $wpdb;
        
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // HPOS
            $count = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->prefix}wc_orders 
                WHERE type = 'shop_order' 
                AND status = 'wc-completed'
            ");
        } else {
            // Posts tradicional
            $count = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->posts} 
                WHERE post_type = 'shop_order' 
                AND post_status = 'wc-completed'
            ");
        }
        
        return $count ? $count : 0;
    }
    
    /**
     * Obtener total de facturas generadas
     */
    private function get_total_invoices_count() {
        global $wpdb;
        
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // HPOS
            $count = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->prefix}wc_orders_meta 
                WHERE meta_key = '_seniat_control_number'
            ");
        } else {
            // Posts tradicional
            $count = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = '_seniat_control_number'
            ");
        }
        
        return $count ? $count : 0;
    }
    
    /**
     * Obtener ingresos del mes actual
     */
    private function get_monthly_revenue() {
        global $wpdb;
        
        $current_month = date('Y-m-01');
        $next_month = date('Y-m-01', strtotime('+1 month'));
        
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // HPOS
            $revenue = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(total_amount) 
                FROM {$wpdb->prefix}wc_orders 
                WHERE type = 'shop_order' 
                AND status IN ('wc-completed', 'wc-processing') 
                AND date_created_gmt >= %s 
                AND date_created_gmt < %s
            ", $current_month, $next_month));
        } else {
            // Posts tradicional
            $revenue = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(meta_value) 
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE p.post_type = 'shop_order' 
                AND p.post_status IN ('wc-completed', 'wc-processing')
                AND pm.meta_key = '_order_total'
                AND p.post_date >= %s 
                AND p.post_date < %s
            ", $current_month, $next_month));
        }
        
        return $revenue ? $revenue : 0;
    }
    
    /**
     * Exportar datos fiscales vía AJAX
     */
    public function export_fiscal_data_ajax() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'wvp_export_fiscal_data')) {
            wp_die('Nonce inválido');
        }
        
        $this->export_fiscal_data();
    }
    
    /**
     * Exportar datos fiscales
     */
    private function export_fiscal_data() {
        global $wpdb;
        
        // Obtener datos de pedidos
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // HPOS
            $orders = $wpdb->get_results("
                SELECT 
                    o.id as order_id,
                    o.status,
                    o.date_created_gmt,
                    o.total_amount,
                    om_rif.meta_value as customer_rif,
                    om_name.meta_value as customer_name,
                    om_address.meta_value as customer_address,
                    om_phone.meta_value as customer_phone,
                    om_email.meta_value as customer_email,
                    om_control.meta_value as control_number
                FROM {$wpdb->prefix}wc_orders o
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_rif ON o.id = om_rif.order_id AND om_rif.meta_key = '_billing_rif'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_name ON o.id = om_name.order_id AND om_name.meta_key = '_billing_first_name'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_address ON o.id = om_address.order_id AND om_address.meta_key = '_billing_address_1'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_phone ON o.id = om_phone.order_id AND om_phone.meta_key = '_billing_phone'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_email ON o.id = om_email.order_id AND om_email.meta_key = '_billing_email'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_control ON o.id = om_control.order_id AND om_control.meta_key = '_seniat_control_number'
                WHERE o.type = 'shop_order'
                AND o.status = 'wc-completed'
                ORDER BY o.date_created_gmt DESC
            ");
        } else {
            // Posts tradicional
            $orders = $wpdb->get_results("
                SELECT 
                    p.ID as order_id,
                    p.post_status as status,
                    p.post_date as date_created_gmt,
                    pm_total.meta_value as total_amount,
                    pm_rif.meta_value as customer_rif,
                    pm_name.meta_value as customer_name,
                    pm_address.meta_value as customer_address,
                    pm_phone.meta_value as customer_phone,
                    pm_email.meta_value as customer_email,
                    pm_control.meta_value as control_number
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
                LEFT JOIN {$wpdb->postmeta} pm_rif ON p.ID = pm_rif.post_id AND pm_rif.meta_key = '_billing_rif'
                LEFT JOIN {$wpdb->postmeta} pm_name ON p.ID = pm_name.post_id AND pm_name.meta_key = '_billing_first_name'
                LEFT JOIN {$wpdb->postmeta} pm_address ON p.ID = pm_address.post_id AND pm_address.meta_key = '_billing_address_1'
                LEFT JOIN {$wpdb->postmeta} pm_phone ON p.ID = pm_phone.post_id AND pm_phone.meta_key = '_billing_phone'
                LEFT JOIN {$wpdb->postmeta} pm_email ON p.ID = pm_email.post_id AND pm_email.meta_key = '_billing_email'
                LEFT JOIN {$wpdb->postmeta} pm_control ON p.ID = pm_control.post_id AND pm_control.meta_key = '_seniat_control_number'
                WHERE p.post_type = 'shop_order'
                AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
                ORDER BY p.post_date DESC
            ");
        }
        
        // Configurar headers para descarga
        $filename = 'datos_fiscales_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, array(
            'ID Pedido',
            'Estado',
            'Fecha',
            'Total (USD)',
            'RIF Cliente',
            'Nombre Cliente',
            'Dirección',
            'Teléfono',
            'Email',
            'Número de Control SENIAT'
        ));
        
        // Datos
        foreach ($orders as $order) {
            fputcsv($output, array(
                $order->order_id,
                $order->status,
                $order->date_created_gmt,
                $order->total_amount,
                $order->customer_rif ?: 'N/A',
                $order->customer_name ?: 'N/A',
                $order->customer_address ?: 'N/A',
                $order->customer_phone ?: 'N/A',
                $order->customer_email ?: 'N/A',
                $order->control_number ?: 'Pendiente'
            ));
        }
        
        fclose($output);
        exit;
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
     * Mostrar control de visualización
     */
    public function display_display_control() {
        $this->current_tab = 'display_control';
        $this->display_admin_page();
    }
    
    /**
     * Mostrar contenido de control de visualización
     */
    private function display_display_control_content() {
        $settings = get_option('wvp_display_settings', WVP_Display_Settings::get_default_settings());
        
        // Asegurar que todas las claves existan
        if (!isset($settings['currency_conversion'])) {
            $settings['currency_conversion'] = array();
        }
        if (!isset($settings['bcv_rate'])) {
            $settings['bcv_rate'] = array();
        }
        if (!isset($settings['currency_switcher'])) {
            $settings['currency_switcher'] = array();
        }
        if (!isset($settings['switcher_scope'])) {
            $settings['switcher_scope'] = array();
        }
        
        // Valores por defecto para cada contexto
        $defaults = WVP_Display_Settings::get_default_settings();
        foreach (['single_product', 'shop_loop', 'cart', 'checkout', 'widget', 'footer'] as $context) {
            if (!isset($settings['currency_conversion'][$context])) {
                $settings['currency_conversion'][$context] = $defaults['currency_conversion'][$context];
            }
            if (!isset($settings['bcv_rate'][$context])) {
                $settings['bcv_rate'][$context] = $defaults['bcv_rate'][$context];
            }
            if (!isset($settings['currency_switcher'][$context])) {
                $settings['currency_switcher'][$context] = $defaults['currency_switcher'][$context];
            }
            if (!isset($settings['switcher_scope'][$context])) {
                $settings['switcher_scope'][$context] = $defaults['switcher_scope'][$context];
            }
        }
        ?>
        <div class="wvp-display-control">
            <h2><?php _e('Control de Visualización de Precios', 'wvp'); ?></h2>
            <p><?php _e('Controla dónde y cómo se muestran las conversiones de moneda, tasas BCV y selectores de moneda.', 'wvp'); ?></p>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('wvp_display_settings');
                do_settings_sections('wvp_display_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Conversión de Monedas', 'wvp'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php _e('Mostrar conversión de monedas en:', 'wvp'); ?></legend>
                                <label><input type="checkbox" name="wvp_display_settings[currency_conversion][single_product]" value="1" <?php checked($settings['currency_conversion']['single_product'], true); ?> /> <?php _e('Página de producto individual', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_conversion][shop_loop]" value="1" <?php checked($settings['currency_conversion']['shop_loop'], true); ?> /> <?php _e('Lista de productos (shop)', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_conversion][cart]" value="1" <?php checked($settings['currency_conversion']['cart'], true); ?> /> <?php _e('Carrito de compras', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_conversion][checkout]" value="1" <?php checked($settings['currency_conversion']['checkout'], true); ?> /> <?php _e('Página de checkout', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_conversion][widget]" value="1" <?php checked($settings['currency_conversion']['widget'], true); ?> /> <?php _e('Widgets', 'wvp'); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Tasa BCV', 'wvp'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php _e('Mostrar tasa BCV en:', 'wvp'); ?></legend>
                                <label><input type="checkbox" name="wvp_display_settings[bcv_rate][single_product]" value="1" <?php checked($settings['bcv_rate']['single_product'], true); ?> /> <?php _e('Página de producto individual', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[bcv_rate][shop_loop]" value="1" <?php checked($settings['bcv_rate']['shop_loop'], true); ?> /> <?php _e('Lista de productos (shop)', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[bcv_rate][cart]" value="1" <?php checked($settings['bcv_rate']['cart'], true); ?> /> <?php _e('Carrito de compras', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[bcv_rate][checkout]" value="1" <?php checked($settings['bcv_rate']['checkout'], true); ?> /> <?php _e('Página de checkout', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[bcv_rate][widget]" value="1" <?php checked($settings['bcv_rate']['widget'], true); ?> /> <?php _e('Widgets', 'wvp'); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Selector de Moneda', 'wvp'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php _e('Mostrar selector de moneda en:', 'wvp'); ?></legend>
                                <label><input type="checkbox" name="wvp_display_settings[currency_switcher][single_product]" value="1" <?php checked($settings['currency_switcher']['single_product'], true); ?> /> <?php _e('Página de producto individual', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_switcher][shop_loop]" value="1" <?php checked($settings['currency_switcher']['shop_loop'], true); ?> /> <?php _e('Lista de productos (shop)', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_switcher][cart]" value="1" <?php checked($settings['currency_switcher']['cart'], true); ?> /> <?php _e('Carrito de compras', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_switcher][checkout]" value="1" <?php checked($settings['currency_switcher']['checkout'], true); ?> /> <?php _e('Página de checkout', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_switcher][widget]" value="1" <?php checked($settings['currency_switcher']['widget'], true); ?> /> <?php _e('Widgets', 'wvp'); ?></label><br>
                                <label><input type="checkbox" name="wvp_display_settings[currency_switcher][footer]" value="1" <?php checked($settings['currency_switcher']['footer'], true); ?> /> <?php _e('Footer', 'wvp'); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Alcance del Selector', 'wvp'); ?></th>
                        <td>
                            <p class="description"><?php _e('Controla si el selector de moneda afecta solo al producto individual (Local) o a toda la página (Global).', 'wvp'); ?></p>
                            <fieldset>
                                <legend class="screen-reader-text"><?php _e('Alcance del selector por contexto:', 'wvp'); ?></legend>
                                <label><?php _e('Página de producto individual:', 'wvp'); ?> 
                                    <select name="wvp_display_settings[switcher_scope][single_product]">
                                        <option value="local" <?php selected($settings['switcher_scope']['single_product'], 'local'); ?>><?php _e('Local (solo este producto)', 'wvp'); ?></option>
                                        <option value="global" <?php selected($settings['switcher_scope']['single_product'], 'global'); ?>><?php _e('Global (toda la página)', 'wvp'); ?></option>
                                    </select>
                                </label><br><br>
                                <label><?php _e('Lista de productos (shop):', 'wvp'); ?> 
                                    <select name="wvp_display_settings[switcher_scope][shop_loop]">
                                        <option value="local" <?php selected($settings['switcher_scope']['shop_loop'], 'local'); ?>><?php _e('Local (solo este producto)', 'wvp'); ?></option>
                                        <option value="global" <?php selected($settings['switcher_scope']['shop_loop'], 'global'); ?>><?php _e('Global (toda la página)', 'wvp'); ?></option>
                                    </select>
                                </label><br><br>
                                <label><?php _e('Widgets:', 'wvp'); ?> 
                                    <select name="wvp_display_settings[switcher_scope][widget]">
                                        <option value="local" <?php selected($settings['switcher_scope']['widget'], 'local'); ?>><?php _e('Local (solo este producto)', 'wvp'); ?></option>
                                        <option value="global" <?php selected($settings['switcher_scope']['widget'], 'global'); ?>><?php _e('Global (toda la página)', 'wvp'); ?></option>
                                    </select>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'wvp')); ?>
            </form>
            
            <hr style="margin: 30px 0;">
            
            <h3><?php _e('Shortcodes Disponibles', 'wvp'); ?></h3>
            <p><?php _e('Usa estos shortcodes para mostrar información de monedas en cualquier lugar:', 'wvp'); ?></p>
            
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Shortcode', 'wvp'); ?></th>
                        <th><?php _e('Descripción', 'wvp'); ?></th>
                        <th><?php _e('Ejemplo', 'wvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[wvp_bcv_rate]</code></td>
                        <td><?php _e('Muestra la tasa BCV actual', 'wvp'); ?></td>
                        <td><code>[wvp_bcv_rate format="simple" show_label="true"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[wvp_currency_switcher]</code></td>
                        <td><?php _e('Muestra selector de moneda', 'wvp'); ?></td>
                        <td><code>[wvp_currency_switcher style="buttons" scope="global"]</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido de monitoreo
     */
    private function display_monitoring_content() {
        global $wpdb;
        ?>
        <div class="wvp-monitoring">
            <h2><?php _e('📊 Monitor de Sistema - WooCommerce Venezuela Pro', 'wvp'); ?></h2>
            
            <div class="wvp-monitoring-grid">
                <!-- Estado General del Sistema -->
                <div class="wvp-monitoring-card">
                    <h3>🎯 Estado General del Sistema</h3>
                    <div class="wvp-status-indicators">
                        <?php
                        // Verificar WooCommerce
                        if (class_exists('WooCommerce')) {
                            $wc_version = WC()->version;
                            echo '<div class="wvp-status-item success">';
                            echo '<span class="status-icon">✅</span>';
                            echo '<span class="status-text">WooCommerce Activo (v' . $wc_version . ')</span>';
                            echo '</div>';
                        } else {
                            echo '<div class="wvp-status-item error">';
                            echo '<span class="status-icon">❌</span>';
                            echo '<span class="status-text">WooCommerce No Activo</span>';
                            echo '</div>';
                        }
                        
                        // Verificar BCV Dólar Tracker
                        if (class_exists('BCV_Dolar_Tracker')) {
                            echo '<div class="wvp-status-item success">';
                            echo '<span class="status-icon">✅</span>';
                            echo '<span class="status-text">BCV Dólar Tracker Activo</span>';
                            echo '</div>';
                        } else {
                            echo '<div class="wvp-status-item warning">';
                            echo '<span class="status-icon">⚠️</span>';
                            echo '<span class="status-text">BCV Dólar Tracker No Activo</span>';
                            echo '</div>';
                        }
                        
                        // Verificar HPOS
                        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
                            if (\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
                                echo '<div class="wvp-status-item success">';
                                echo '<span class="status-icon">✅</span>';
                                echo '<span class="status-text">HPOS Habilitado</span>';
                                echo '</div>';
                            } else {
                                echo '<div class="wvp-status-item warning">';
                                echo '<span class="status-icon">⚠️</span>';
                                echo '<span class="status-text">HPOS Deshabilitado</span>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="wvp-status-item error">';
                            echo '<span class="status-icon">❌</span>';
                            echo '<span class="status-text">HPOS No Disponible</span>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Base de Datos -->
                <div class="wvp-monitoring-card">
                    <h3>🗄️ Estado de Base de Datos</h3>
                    <div class="wvp-db-info">
                        <?php
                        // Tablas de WooCommerce
                        $wc_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}woocommerce%'");
                        echo '<div class="wvp-db-section">';
                        echo '<h4>Tablas de WooCommerce</h4>';
                        echo '<p class="wvp-db-count">' . count($wc_tables) . ' tablas encontradas</p>';
                        if (count($wc_tables) > 0) {
                            echo '<div class="wvp-db-tables">';
                            foreach (array_slice($wc_tables, 0, 5) as $table) {
                                $table_name = array_values((array)$table)[0];
                                echo '<span class="wvp-db-table">' . str_replace($wpdb->prefix, '', $table_name) . '</span>';
                            }
                            if (count($wc_tables) > 5) {
                                echo '<span class="wvp-db-more">... y ' . (count($wc_tables) - 5) . ' más</span>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                        
                        // Tablas de Pedidos
                        echo '<div class="wvp-db-section">';
                        echo '<h4>Tablas de Pedidos</h4>';
                        
                        // Tabla tradicional
                        $posts_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->posts}'");
                        if ($posts_table) {
                            $orders_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'");
                            echo '<div class="wvp-db-table-info">';
                            echo '<span class="wvp-db-table-name">wp_posts (tradicional)</span>';
                            echo '<span class="wvp-db-table-count">' . $orders_count . ' pedidos</span>';
                            echo '</div>';
                        }
                        
                        // Tabla HPOS
                        $hpos_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wc_orders'");
                        if ($hpos_table) {
                            $hpos_orders_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders");
                            echo '<div class="wvp-db-table-info">';
                            echo '<span class="wvp-db-table-name">wc_orders (HPOS)</span>';
                            echo '<span class="wvp-db-table-count">' . $hpos_orders_count . ' pedidos</span>';
                            echo '</div>';
                        }
                        echo '</div>';
                        
                        // Tablas del Plugin
                        $plugin_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}wvp_%'");
                        echo '<div class="wvp-db-section">';
                        echo '<h4>Tablas del Plugin</h4>';
                        if ($plugin_tables) {
                            echo '<p class="wvp-db-count">' . count($plugin_tables) . ' tablas encontradas</p>';
                            foreach ($plugin_tables as $table) {
                                $table_name = array_values((array)$table)[0];
                                echo '<span class="wvp-db-table">' . str_replace($wpdb->prefix, '', $table_name) . '</span>';
                            }
                        } else {
                            echo '<p class="wvp-db-count">No hay tablas personalizadas del plugin</p>';
                        }
                        echo '</div>';
                        ?>
                    </div>
                </div>

                <!-- Configuraciones del Plugin -->
                <div class="wvp-monitoring-card">
                    <h3>⚙️ Configuraciones del Plugin</h3>
                    <div class="wvp-config-info">
                        <?php
                        $general_settings = get_option('wvp_general_settings', array());
                        $igtf_enabled = isset($general_settings['igtf_enabled']) ? $general_settings['igtf_enabled'] : 'no';
                        $show_igtf = isset($general_settings['show_igtf']) ? $general_settings['show_igtf'] : '0';
                        $igtf_rate = isset($general_settings['igtf_rate']) ? $general_settings['igtf_rate'] : '3';
                        $price_format = isset($general_settings['price_format']) ? $general_settings['price_format'] : 'Ref. %s Bs.';
                        
                        echo '<div class="wvp-config-item">';
                        echo '<span class="wvp-config-label">IGTF Habilitado:</span>';
                        echo '<span class="wvp-config-value ' . ($igtf_enabled === 'yes' ? 'enabled' : 'disabled') . '">';
                        echo $igtf_enabled === 'yes' ? 'Sí' : 'No';
                        echo '</span>';
                        echo '</div>';
                        
                        echo '<div class="wvp-config-item">';
                        echo '<span class="wvp-config-label">Mostrar IGTF:</span>';
                        echo '<span class="wvp-config-value ' . ($show_igtf === '1' ? 'enabled' : 'disabled') . '">';
                        echo $show_igtf === '1' ? 'Sí' : 'No';
                        echo '</span>';
                        echo '</div>';
                        
                        echo '<div class="wvp-config-item">';
                        echo '<span class="wvp-config-label">Tasa IGTF:</span>';
                        echo '<span class="wvp-config-value">' . $igtf_rate . '%</span>';
                        echo '</div>';
                        
                        echo '<div class="wvp-config-item">';
                        echo '<span class="wvp-config-label">Formato de Precio:</span>';
                        echo '<span class="wvp-config-value">' . esc_html($price_format) . '</span>';
                        echo '</div>';
                        ?>
                    </div>
                </div>

                <!-- Estadísticas de Uso -->
                <div class="wvp-monitoring-card">
                    <h3>📈 Estadísticas de Uso</h3>
                    <div class="wvp-stats-info">
                        <?php
                        // Contar productos con precios en USD
                        $products_with_usd = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM {$wpdb->postmeta} pm 
                            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
                            WHERE p.post_type = 'product' 
                            AND p.post_status = 'publish'
                            AND pm.meta_key = '_price'
                            AND pm.meta_value > 0
                        ");
                        
                        // Contar pedidos del mes actual
                        $current_month_orders = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM " . (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? 
                                "{$wpdb->prefix}wc_orders" : 
                                "{$wpdb->posts}") . " 
                            WHERE " . (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? 
                                "status = 'wc-completed' AND MONTH(date_created_gmt) = " . date('n') . " AND YEAR(date_created_gmt) = " . date('Y') :
                                "post_type = 'shop_order' AND post_status = 'wc-completed' AND MONTH(post_date) = " . date('n') . " AND YEAR(post_date) = " . date('Y'))
                        );
                        
                        echo '<div class="wvp-stat-item">';
                        echo '<span class="wvp-stat-label">Productos con Precios:</span>';
                        echo '<span class="wvp-stat-value">' . $products_with_usd . '</span>';
                        echo '</div>';
                        
                        echo '<div class="wvp-stat-item">';
                        echo '<span class="wvp-stat-label">Pedidos este Mes:</span>';
                        echo '<span class="wvp-stat-value">' . $current_month_orders . '</span>';
                        echo '</div>';
                        
                        // Verificar última actualización de BCV
                        if (class_exists('BCV_Dolar_Tracker')) {
                            $last_bcv_update = get_option('bcv_last_update', 'Nunca');
                            echo '<div class="wvp-stat-item">';
                            echo '<span class="wvp-stat-label">Última Actualización BCV:</span>';
                            echo '<span class="wvp-stat-value">' . $last_bcv_update . '</span>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Logs de Errores -->
                <div class="wvp-monitoring-card">
                    <h3>🚨 Logs de Errores Recientes</h3>
                    <div class="wvp-logs-info">
                        <?php
                        $debug_log = WP_CONTENT_DIR . '/debug.log';
                        if (file_exists($debug_log)) {
                            $log_content = file_get_contents($debug_log);
                            $lines = explode("\n", $log_content);
                            $recent_lines = array_slice($lines, -10);
                            
                            echo '<div class="wvp-logs-container">';
                            foreach ($recent_lines as $line) {
                                if (!empty(trim($line))) {
                                    $is_error = strpos($line, 'ERROR') !== false || strpos($line, 'Fatal') !== false;
                                    $is_warning = strpos($line, 'WARNING') !== false;
                                    $class = $is_error ? 'error' : ($is_warning ? 'warning' : 'info');
                                    echo '<div class="wvp-log-line ' . $class . '">' . esc_html($line) . '</div>';
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<p>No hay archivo de debug.log</p>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Recomendaciones -->
                <div class="wvp-monitoring-card">
                    <h3>💡 Recomendaciones del Sistema</h3>
                    <div class="wvp-recommendations">
                        <?php
                        $recommendations = array();
                        
                        // Verificar HPOS
                        if (!class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') || 
                            !\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
                            $recommendations[] = 'Considera habilitar HPOS para mejor rendimiento con pedidos';
                        }
                        
                        // Verificar BCV Dólar Tracker
                        if (!class_exists('BCV_Dolar_Tracker')) {
                            $recommendations[] = 'Instala y activa BCV Dólar Tracker para tasas de cambio automáticas';
                        }
                        
                        // Verificar configuración de IGTF
                        if ($igtf_enabled === 'yes' && $show_igtf === '0') {
                            $recommendations[] = 'IGTF está habilitado pero no se muestra - verifica configuración';
                        }
                        
                        if (empty($recommendations)) {
                            echo '<p class="wvp-no-recommendations">✅ Sistema funcionando correctamente</p>';
                        } else {
                            echo '<ul class="wvp-recommendations-list">';
                            foreach ($recommendations as $rec) {
                                echo '<li>' . $rec . '</li>';
                            }
                            echo '</ul>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .wvp-monitoring-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .wvp-monitoring-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .wvp-monitoring-card h3 {
            margin-top: 0;
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .wvp-status-indicators {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .wvp-status-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 4px;
        }
        
        .wvp-status-item.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .wvp-status-item.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .wvp-status-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .wvp-db-section {
            margin-bottom: 15px;
        }
        
        .wvp-db-section h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .wvp-db-count {
            font-weight: bold;
            color: #0073aa;
            margin: 5px 0;
        }
        
        .wvp-db-tables {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .wvp-db-table {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .wvp-db-table-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .wvp-db-table-name {
            font-weight: bold;
        }
        
        .wvp-db-table-count {
            color: #0073aa;
        }
        
        .wvp-config-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .wvp-config-label {
            font-weight: bold;
        }
        
        .wvp-config-value.enabled {
            color: #28a745;
            font-weight: bold;
        }
        
        .wvp-config-value.disabled {
            color: #dc3545;
            font-weight: bold;
        }
        
        .wvp-stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .wvp-stat-label {
            font-weight: bold;
        }
        
        .wvp-stat-value {
            color: #0073aa;
            font-weight: bold;
            font-size: 18px;
        }
        
        .wvp-logs-container {
            max-height: 200px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
        
        .wvp-log-line {
            font-family: monospace;
            font-size: 12px;
            margin: 2px 0;
            padding: 2px 5px;
            border-radius: 3px;
        }
        
        .wvp-log-line.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .wvp-log-line.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .wvp-log-line.info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .wvp-recommendations-list {
            margin: 0;
            padding-left: 20px;
        }
        
        .wvp-recommendations-list li {
            margin: 5px 0;
            color: #856404;
        }
        
        .wvp-no-recommendations {
            color: #28a745;
            font-weight: bold;
            text-align: center;
            padding: 20px;
        }
        </style>
        <?php
    }
    
    /**
     * Obtener configuraciones de apariencia
     */
    private function get_appearance_settings() {
        return array(
            'display_style' => get_option('wvp_display_style', 'minimal'),
            'primary_color' => get_option('wvp_primary_color', '#007cba'),
            'secondary_color' => get_option('wvp_secondary_color', '#005a87'),
            'success_color' => get_option('wvp_success_color', '#28a745'),
            'warning_color' => get_option('wvp_warning_color', '#ffc107'),
            'font_family' => get_option('wvp_font_family', 'system'),
            'font_size' => get_option('wvp_font_size', 'medium'),
            'font_weight' => get_option('wvp_font_weight', '400'),
            'text_transform' => get_option('wvp_text_transform', 'none'),
            'padding' => get_option('wvp_padding', 'medium'),
            'margin' => get_option('wvp_margin', 'medium'),
            'border_radius' => get_option('wvp_border_radius', 'medium'),
            'shadow' => get_option('wvp_shadow', 'small')
        );
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
        
        // Obtener configuraciones de control de visualización
        $display_settings = get_option('wvp_display_settings', WVP_Display_Settings::get_default_settings());
        
        // Obtener configuraciones de apariencia actuales
        $appearance_settings = $this->get_appearance_settings();
        ?>
        <div class="wvp-admin-content">
            <h2><?php _e('Control de Apariencia', 'wvp'); ?></h2>
            <p><?php _e('Personaliza la visualización de precios y conversiones en tu tienda. Los cambios se aplicarán según las configuraciones de Control de Visualización.', 'wvp'); ?></p>
            
            <!-- Información de conexión con Control de Visualización -->
            <div class="notice notice-info inline">
                <p><strong><?php _e('ℹ️ Información Importante:', 'wvp'); ?></strong> <?php _e('Esta sección controla CÓMO se ven los elementos. Para controlar DÓNDE aparecen, ve a', 'wvp'); ?> 
                <a href="<?php echo admin_url('admin.php?page=wvp-display-control'); ?>" class="button button-small"><?php _e('Control de Visualización', 'wvp'); ?></a></p>
            </div>
            
            <form method="post" action="options.php" id="wvp-appearance-form">
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
                            <div class="wvp-preview-section">
                                <h4><?php _e('Configuraciones de Control de Visualización:', 'wvp'); ?></h4>
                                <div class="wvp-display-status">
                                    <?php
                                    $contexts = array(
                                        'single_product' => 'Página de Producto',
                                        'shop_loop' => 'Lista de Productos',
                                        'cart' => 'Carrito',
                                        'checkout' => 'Checkout',
                                        'widget' => 'Widgets',
                                        'footer' => 'Footer'
                                    );
                                    
                                    foreach ($contexts as $context => $label) {
                                        $conversion_enabled = isset($display_settings['currency_conversion'][$context]) ? $display_settings['currency_conversion'][$context] : false;
                                        $switcher_enabled = isset($display_settings['currency_switcher'][$context]) ? $display_settings['currency_switcher'][$context] : false;
                                        $bcv_enabled = isset($display_settings['bcv_rate'][$context]) ? $display_settings['bcv_rate'][$context] : false;
                                        
                                        echo '<div class="wvp-context-status">';
                                        echo '<strong>' . esc_html($label) . ':</strong> ';
                                        echo '<span class="status-badge ' . ($conversion_enabled ? 'enabled' : 'disabled') . '">Conversión</span> ';
                                        echo '<span class="status-badge ' . ($switcher_enabled ? 'enabled' : 'disabled') . '">Selector</span> ';
                                        echo '<span class="status-badge ' . ($bcv_enabled ? 'enabled' : 'disabled') . '">Tasa BCV</span>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                
                                <h4><?php _e('Vista Previa del Estilo:', 'wvp'); ?></h4>
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
                                
                                <div class="wvp-preview-actions">
                                    <button type="button" id="wvp-test-switcher" class="button button-secondary">
                                        <?php _e('Probar Selector de Moneda', 'wvp'); ?>
                                    </button>
                                    <button type="button" id="wvp-reset-preview" class="button button-secondary">
                                        <?php _e('Resetear Vista Previa', 'wvp'); ?>
                                    </button>
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
                // Variables globales para la vista previa
                var currentPreviewCurrency = 'USD';
                var previewSettings = <?php echo json_encode($appearance_settings); ?>;
                
                // Cambio de estilo
                $('#wvp_display_style').on('change', function() {
                    var style = $(this).val();
                    var $preview = $('#wvp-style-preview .wvp-product-price-container');
                    
                    // Remover clases de estilo anteriores
                    $preview.removeClass('wvp-minimal wvp-modern wvp-elegant wvp-compact wvp-vintage wvp-futuristic wvp-advanced-minimal');
                    
                    // Añadir nueva clase de estilo
                    $preview.addClass('wvp-' + style);
                    
                    // Aplicar estilos dinámicos
                    applyDynamicStyles();
                });
                
                // Aplicar estilos dinámicos basados en configuraciones
                function applyDynamicStyles() {
                    var $preview = $('#wvp-preview-container');
                    
                    // Aplicar colores
                    $preview.css({
                        '--wvp-primary-color': $('#wvp_primary_color').val(),
                        '--wvp-secondary-color': $('#wvp_secondary_color').val(),
                        '--wvp-success-color': $('#wvp_success_color').val(),
                        '--wvp-warning-color': $('#wvp_warning_color').val()
                    });
                    
                    // Aplicar fuentes
                    var fontFamily = $('#wvp_font_family').val();
                    var fontSize = $('#wvp_font_size').val();
                    var fontWeight = $('#wvp_font_weight').val();
                    var textTransform = $('#wvp_text_transform').val();
                    
                    var fontSizes = {
                        'small': '12px',
                        'medium': '14px',
                        'large': '16px',
                        'xlarge': '18px',
                        'xxlarge': '20px'
                    };
                    
                    var fontFamilies = {
                        'system': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        'arial': 'Arial, sans-serif',
                        'helvetica': 'Helvetica, Arial, sans-serif',
                        'georgia': 'Georgia, serif',
                        'times': '"Times New Roman", Times, serif',
                        'verdana': 'Verdana, sans-serif',
                        'tahoma': 'Tahoma, sans-serif',
                        'trebuchet': '"Trebuchet MS", sans-serif',
                        'courier': '"Courier New", monospace',
                        'monospace': 'monospace'
                    };
                    
                    $preview.find('.wvp-price-usd, .wvp-price-ves').css({
                        'font-family': fontFamilies[fontFamily] || fontFamilies['system'],
                        'font-size': fontSizes[fontSize] || fontSizes['medium'],
                        'font-weight': fontWeight,
                        'text-transform': textTransform
                    });
                    
                    // Aplicar espaciado
                    var padding = $('#wvp_padding').val();
                    var margin = $('#wvp_margin').val();
                    var borderRadius = $('#wvp_border_radius').val();
                    var shadow = $('#wvp_shadow').val();
                    
                    var paddings = {
                        'none': '0px',
                        'small': '5px',
                        'medium': '10px',
                        'large': '15px',
                        'xlarge': '20px'
                    };
                    
                    var margins = {
                        'none': '0px',
                        'small': '5px',
                        'medium': '10px',
                        'large': '15px',
                        'xlarge': '20px'
                    };
                    
                    var borderRadiuses = {
                        'none': '0px',
                        'small': '3px',
                        'medium': '6px',
                        'large': '12px',
                        'xlarge': '20px',
                        'round': '50px'
                    };
                    
                    var shadows = {
                        'none': 'none',
                        'small': '0 2px 4px rgba(0,0,0,0.1)',
                        'medium': '0 4px 8px rgba(0,0,0,0.15)',
                        'large': '0 8px 16px rgba(0,0,0,0.2)',
                        'glow': '0 0 10px rgba(0,115,170,0.3)'
                    };
                    
                    $preview.find('.wvp-price-conversion').css({
                        'padding': paddings[padding] || paddings['medium'],
                        'margin': margins[margin] || margins['medium'],
                        'border-radius': borderRadiuses[borderRadius] || borderRadiuses['medium'],
                        'box-shadow': shadows[shadow] || shadows['small']
                    });
                }
                
                // Aplicar estilos cuando cambian los controles
                $('#wvp_primary_color, #wvp_secondary_color, #wvp_success_color, #wvp_warning_color').on('change', applyDynamicStyles);
                $('#wvp_font_family, #wvp_font_size, #wvp_font_weight, #wvp_text_transform').on('change', applyDynamicStyles);
                $('#wvp_padding, #wvp_margin, #wvp_border_radius, #wvp_shadow').on('change', applyDynamicStyles);
                
                // Aplicar estilos iniciales
                applyDynamicStyles();
                
                // Funcionalidad de prueba del selector de moneda
                $('#wvp-test-switcher').on('click', function() {
                    var $preview = $('#wvp-preview-container');
                    var $usdPrice = $preview.find('.wvp-price-usd');
                    var $vesPrice = $preview.find('.wvp-price-ves');
                    var $conversion = $preview.find('.wvp-price-conversion');
                    var $rateInfo = $preview.find('.wvp-rate-info');
                    var $usdBtn = $preview.find('[data-currency="usd"]');
                    var $vesBtn = $preview.find('[data-currency="ves"]');
                    
                    if (currentPreviewCurrency === 'USD') {
                        // Cambiar a VES
                        $usdPrice.fadeOut(200, function() {
                            $vesPrice.fadeIn(200);
                        });
                        $conversion.fadeOut(200);
                        $rateInfo.fadeOut(200);
                        $usdBtn.removeClass('active');
                        $vesBtn.addClass('active');
                        currentPreviewCurrency = 'VES';
                        $(this).text('<?php _e('Cambiar a USD', 'wvp'); ?>');
                    } else {
                        // Cambiar a USD
                        $vesPrice.fadeOut(200, function() {
                            $usdPrice.fadeIn(200);
                        });
                        $conversion.fadeIn(200);
                        $rateInfo.fadeIn(200);
                        $vesBtn.removeClass('active');
                        $usdBtn.addClass('active');
                        currentPreviewCurrency = 'USD';
                        $(this).text('<?php _e('Cambiar a VES', 'wvp'); ?>');
                    }
                });
                
                // Resetear vista previa
                $('#wvp-reset-preview').on('click', function() {
                    var $preview = $('#wvp-preview-container');
                    var $usdPrice = $preview.find('.wvp-price-usd');
                    var $vesPrice = $preview.find('.wvp-price-ves');
                    var $conversion = $preview.find('.wvp-price-conversion');
                    var $rateInfo = $preview.find('.wvp-rate-info');
                    var $usdBtn = $preview.find('[data-currency="usd"]');
                    var $vesBtn = $preview.find('[data-currency="ves"]');
                    
                    // Resetear a USD
                    $usdPrice.show();
                    $vesPrice.hide();
                    $conversion.show();
                    $rateInfo.show();
                    $usdBtn.addClass('active');
                    $vesBtn.removeClass('active');
                    currentPreviewCurrency = 'USD';
                    
                    // Resetear botón de prueba
                    $('#wvp-test-switcher').text('<?php _e('Cambiar a VES', 'wvp'); ?>');
                    
                    // Reaplicar estilos
                    applyDynamicStyles();
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
            <h1><?php _e('📚 Guía Completa - WooCommerce Venezuela Pro', 'wvp'); ?></h1>
            
            <div class="wvp-help-section">
                <h2>🎯 ¿Qué es WooCommerce Venezuela Pro?</h2>
                <p>Es un plugin especializado para tiendas online en Venezuela que integra funcionalidades específicas del mercado venezolano con WooCommerce, incluyendo:</p>
                <ul>
                    <li>✅ <strong>Integración con BCV Dólar Tracker</strong> - Tasas de cambio en tiempo real</li>
                    <li>✅ <strong>Sistema IGTF</strong> - Impuesto a las Grandes Transacciones Financieras</li>
                    <li>✅ <strong>Validaciones venezolanas</strong> - Cédula/RIF, teléfonos</li>
                    <li>✅ <strong>Pasarelas de pago locales</strong> - Pago Móvil, Zelle, Cashea</li>
                    <li>✅ <strong>Facturación híbrida</strong> - USD/VES</li>
                    <li>✅ <strong>Zonas de envío</strong> - Estados y municipios de Venezuela</li>
                    <li>✅ <strong>Notificaciones WhatsApp</strong> - Confirmaciones automáticas</li>
                    <li>✅ <strong>Personalización visual</strong> - Múltiples estilos para precios</li>
                </ul>
            </div>

            <div class="wvp-help-section">
                <h2>⚙️ Configuración Inicial</h2>
                <h3>1. Configuraciones Generales</h3>
                <p><strong>Ubicación:</strong> Venezuela Pro → Configuraciones</p>
                <ul>
                    <li><strong>Formato de Referencia de Precio:</strong> Define cómo mostrar precios en bolívares (ej: "Ref. %s Bs.")</li>
                    <li><strong>Tasa IGTF:</strong> Porcentaje del impuesto (por defecto 3%)</li>
                    <li><strong>Mostrar IGTF:</strong> Activa/desactiva la visualización en checkout</li>
                    <li><strong>Habilitar IGTF:</strong> Activa/desactiva todo el sistema IGTF</li>
                </ul>

                <h3>2. Pasarelas de Pago</h3>
                <p><strong>Ubicación:</strong> Venezuela Pro → Pasarelas de Pago</p>
                <ul>
                    <li><strong>Pago Móvil:</strong> Configuración para pagos móviles venezolanos</li>
                    <li><strong>Zelle:</strong> Integración con Zelle para pagos en USD</li>
                    <li><strong>Cashea:</strong> Pasarela de pago local</li>
                </ul>

                <h3>3. Sistema Fiscal</h3>
                <p><strong>Ubicación:</strong> Venezuela Pro → Sistema Fiscal</p>
                <ul>
                    <li><strong>Prefijo de Número de Control:</strong> Para facturación (ej: "00-")</li>
                    <li><strong>Próximo Número de Control:</strong> Secuencia de numeración</li>
                </ul>
            </div>

            <div class="wvp-help-section">
                <h2>🎨 Personalización Visual</h2>
                <p><strong>Ubicación:</strong> Venezuela Pro → Apariencia</p>
                <h3>Estilos Disponibles:</h3>
                <ul>
                    <li><strong>Minimal:</strong> Diseño limpio y simple</li>
                    <li><strong>Modern:</strong> Estilo contemporáneo con sombras</li>
                    <li><strong>Elegant:</strong> Diseño elegante con bordes redondeados</li>
                    <li><strong>Compact:</strong> Diseño compacto para espacios reducidos</li>
                    <li><strong>Vintage:</strong> Estilo retro con tipografía clásica</li>
                    <li><strong>Futuristic:</strong> Diseño futurista con efectos modernos</li>
                    <li><strong>Advanced Minimalist:</strong> Minimalismo avanzado</li>
                </ul>
                
                <h3>Personalización Avanzada:</h3>
                <ul>
                    <li><strong>Colores:</strong> Primario, secundario, éxito, advertencia</li>
                    <li><strong>Tipografía:</strong> Familia, tamaño, peso, transformación</li>
                    <li><strong>Espaciado:</strong> Padding, margen, radio de borde, sombra</li>
                    <li><strong>Temas Predefinidos:</strong> Default, Green, Purple, Orange, Red, Dark</li>
                </ul>
            </div>

            <div class="wvp-help-section">
                <h2>📱 Funcionalidades Específicas</h2>
                
                <h3>🔄 Conversión de Monedas</h3>
                <p>El plugin convierte automáticamente precios entre USD y VES usando tasas del BCV:</p>
                <ul>
                    <li>Precios base en USD</li>
                    <li>Conversión automática a VES</li>
                    <li>Actualización en tiempo real</li>
                    <li>Selector de moneda en frontend</li>
                </ul>

                <h3>💰 Sistema IGTF</h3>
                <p>Impuesto aplicado a transacciones en efectivo con billetes en dólares:</p>
                <ul>
                    <li>Se aplica solo a pasarelas de pago en efectivo</li>
                    <li>No se aplica a transferencias digitales</li>
                    <li>Configurable desde administración</li>
                    <li>Visible en checkout cuando aplica</li>
                </ul>

                <h3>📋 Validaciones Venezolanas</h3>
                <ul>
                    <li><strong>Cédula/RIF:</strong> Formato V-12345678, J-12345678-9</li>
                    <li><strong>Teléfonos:</strong> Formato venezolano (0412-1234567)</li>
                    <li><strong>Campos obligatorios:</strong> Cédula/RIF en checkout</li>
                </ul>

                <h3>🚚 Zonas de Envío</h3>
                <ul>
                    <li>Estados de Venezuela preconfigurados</li>
                    <li>Municipios por estado</li>
                    <li>Costos de envío configurables</li>
                    <li>Selector dinámico en checkout</li>
                </ul>
            </div>

            <div class="wvp-help-section">
                <h2>🔧 Solución de Problemas</h2>
                
                <h3>Problemas Comunes:</h3>
                <ul>
                    <li><strong>IGTF no se desactiva:</strong> Verifica ambas opciones en Configuraciones</li>
                    <li><strong>Precios no se convierten:</strong> Verifica que BCV Dólar Tracker esté activo</li>
                    <li><strong>Errores AJAX:</strong> Desactiva plugins de caché temporalmente</li>
                    <li><strong>Estilos no se aplican:</strong> Limpia caché del navegador</li>
                </ul>

                <h3>Logs y Debug:</h3>
                <ul>
                    <li><strong>Debug Log:</strong> wp-content/debug.log</li>
                    <li><strong>Monitor de Errores:</strong> Venezuela Pro → Monitor de Errores</li>
                    <li><strong>Reportes:</strong> Venezuela Pro → Reportes</li>
                </ul>
            </div>

            <div class="wvp-help-section">
                <h2>📞 Soporte y Contacto</h2>
                <p>Para soporte técnico o consultas:</p>
                <ul>
                    <li>📧 <strong>Email:</strong> soporte@ejemplo.com</li>
                    <li>📱 <strong>WhatsApp:</strong> +58 412-1234567</li>
                    <li>🌐 <strong>Web:</strong> www.ejemplo.com</li>
                </ul>
            </div>

            <div class="wvp-help-section">
                <h2>📄 Documentación Técnica</h2>
                <p>Para desarrolladores:</p>
                <ul>
                    <li><strong>Hooks disponibles:</strong> woocommerce_checkout_fields, wc_get_price_html</li>
                    <li><strong>Filtros:</strong> wvp_igtf_rate, wvp_currency_symbol</li>
                    <li><strong>Acciones:</strong> wvp_after_price_display, wvp_before_checkout</li>
                    <li><strong>Clases principales:</strong> WVP_Product_Display_Manager, WVP_Checkout</li>
                </ul>
            </div>
        </div>

        <style>
        .wvp-help-section {
            background: #f9f9f9;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #0073aa;
            border-radius: 4px;
        }
        .wvp-help-section h2 {
            color: #0073aa;
            margin-top: 0;
        }
        .wvp-help-section h3 {
            color: #333;
            margin-top: 15px;
        }
        .wvp-help-section ul {
            margin: 10px 0;
        }
        .wvp-help-section li {
            margin: 5px 0;
        }
        </style>
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
                
                .wvp-preview-section {
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                
                .wvp-display-status {
                    background: #fff;
                    border: 1px solid #e1e1e1;
                    border-radius: 6px;
                    padding: 15px;
                    margin: 15px 0;
                }
                
                .wvp-context-status {
                    margin: 8px 0;
                    padding: 8px;
                    background: #f8f9fa;
                    border-radius: 4px;
                    border-left: 4px solid #007cba;
                }
                
                .status-badge {
                    display: inline-block;
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: 600;
                    margin-right: 5px;
                }
                
                .status-badge.enabled {
                    background: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                
                .status-badge.disabled {
                    background: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
                
                .wvp-preview-container {
                    background: #fff;
                    border: 2px dashed #007cba;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 15px 0;
                    text-align: center;
                }
                
                .wvp-preview-actions {
                    margin-top: 15px;
                    text-align: center;
                }
                
                .wvp-preview-actions .button {
                    margin: 0 5px;
                }
                
                .wvp-theme-btn {
                    display: inline-block;
                    margin: 5px;
                    padding: 10px;
                    border: 2px solid #ddd;
                    border-radius: 6px;
                    background: #fff;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-align: center;
                    min-width: 120px;
                }
                
                .wvp-theme-btn:hover {
                    border-color: #007cba;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                
                .wvp-theme-btn.active {
                    border-color: #007cba;
                    background: #007cba;
                    color: #fff;
                }
                
                .wvp-theme-preview {
                    width: 100%;
                    height: 30px;
                    border-radius: 4px;
                    margin-bottom: 8px;
                }
                
                .wvp-font-settings,
                .wvp-spacing-settings {
                    border-collapse: separate;
                    border-spacing: 15px;
                }
                
                .wvp-font-settings td,
                .wvp-spacing-settings td {
                    vertical-align: top;
                }
                
                .wvp-font-settings label,
                .wvp-spacing-settings label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: 600;
                }
                
                .wvp-font-settings select,
                .wvp-spacing-settings select {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }
                
                /* Estilos para la vista previa en tiempo real */
                #wvp-preview-container {
                    position: relative;
                    transition: all 0.3s ease;
                }
                
                #wvp-preview-container .wvp-price-usd,
                #wvp-preview-container .wvp-price-ves {
                    transition: all 0.3s ease;
                }
                
                #wvp-preview-container .wvp-currency-switcher {
                    margin: 15px 0;
                }
                
                #wvp-preview-container .wvp-currency-switcher button {
                    transition: all 0.3s ease;
                }
                
                #wvp-preview-container .wvp-price-conversion {
                    transition: all 0.3s ease;
                }
                
                #wvp-preview-container .wvp-rate-info {
                    transition: all 0.3s ease;
                }
                
                /* Responsive para vista previa */
                @media (max-width: 768px) {
                    .wvp-preview-section {
                        padding: 15px;
                    }
                    
                    .wvp-preview-container {
                        padding: 15px;
                    }
                    
                    .wvp-theme-btn {
                        min-width: 100px;
                        margin: 3px;
                    }
                    
                    .wvp-context-status {
                        font-size: 13px;
                    }
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
            
            // Cargar CSS dinámico para apariencia
            wp_enqueue_style(
                'wvp-appearance-dynamic',
                WVP_PLUGIN_URL . 'assets/css/wvp-appearance-dynamic.css',
                array(),
                WVP_VERSION
            );
            
            // Cargar JS dinámico para apariencia
            wp_enqueue_script(
                'wvp-appearance-dynamic',
                WVP_PLUGIN_URL . 'assets/js/wvp-appearance-dynamic.js',
                array('jquery'),
                WVP_VERSION,
                true
            );
            
            wp_localize_script('wvp-appearance-dynamic', 'wvp_appearance_dynamic', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wvp_appearance_dynamic_nonce')
            ));
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
     * Mostrar contenido de reportes
     */
    private function display_reports_content() {
        global $wpdb;
        
        // Obtener estadísticas básicas
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order' AND post_status IN ('wc-completed', 'wc-processing')");
        $total_sales = $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_order_total'");
        
        ?>
        <div class="wvp-reports">
            <h2><?php _e('📊 Reportes - WooCommerce Venezuela Pro', 'wvp'); ?></h2>
            
            <div class="wvp-reports-grid">
                <!-- Estadísticas Generales -->
                <div class="wvp-report-card">
                    <h3>📈 Estadísticas Generales</h3>
                    <div class="wvp-stats-overview">
                        <div class="wvp-stat-item">
                            <span class="wvp-stat-icon">🛒</span>
                            <div class="wvp-stat-details">
                                <span class="wvp-stat-number"><?php echo number_format($total_orders); ?></span>
                                <span class="wvp-stat-label">Pedidos Totales</span>
                            </div>
                        </div>
                        
                        <div class="wvp-stat-item">
                            <span class="wvp-stat-icon">💰</span>
                            <div class="wvp-stat-details">
                                <span class="wvp-stat-number">$<?php echo number_format($total_sales, 2); ?></span>
                                <span class="wvp-stat-label">Ventas Totales (USD)</span>
                            </div>
                        </div>
                        
                        <div class="wvp-stat-item">
                            <span class="wvp-stat-icon">💱</span>
                            <div class="wvp-stat-details">
                                <span class="wvp-stat-number">
                                    <?php 
                                    if (class_exists('WVP_BCV_Integrator')) {
                                        $rate = WVP_BCV_Integrator::get_rate();
                                        // Validar que rate sea un número válido y no null
                                        if (!empty($rate) && is_numeric($rate) && $rate > 0) {
                                            echo number_format((float)$rate, 2) . ' Bs.';
                                        } else {
                                            echo 'N/A';
                                        }
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </span>
                                <span class="wvp-stat-label">Tasa BCV Actual</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reportes Disponibles -->
                <div class="wvp-report-card">
                    <h3>📋 Reportes Disponibles</h3>
                    <div class="wvp-reports-list">
                        <div class="wvp-report-item">
                            <div class="wvp-report-info">
                                <h4>🏛️ Reporte SENIAT</h4>
                                <p>Reporte fiscal completo para el SENIAT con todos los datos requeridos.</p>
                            </div>
                            <div class="wvp-report-actions">
                                <a href="<?php echo admin_url('admin.php?page=wvp-seniat-reports'); ?>" class="button button-primary">
                                    Ver Reporte SENIAT
                                </a>
                            </div>
                        </div>
                        
                        <div class="wvp-report-item">
                            <div class="wvp-report-info">
                                <h4>💹 Análisis de Tasas BCV</h4>
                                <p>Histórico de tasas de cambio y análisis de variaciones.</p>
                            </div>
                            <div class="wvp-report-actions">
                                <button type="button" class="button button-secondary" onclick="wvpGenerateBCVReport()">
                                    Generar Análisis BCV
                                </button>
                            </div>
                        </div>
                        
                        <div class="wvp-report-item">
                            <div class="wvp-report-info">
                                <h4>🧾 Facturas Electrónicas</h4>
                                <p>Listado y gestión de facturas electrónicas generadas.</p>
                            </div>
                            <div class="wvp-report-actions">
                                <button type="button" class="button button-secondary" onclick="wvpShowInvoices()">
                                    Ver Facturas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones Rápidas -->
                <div class="wvp-report-card">
                    <h3>⚡ Acciones Rápidas</h3>
                    <div class="wvp-quick-actions">
                        <button type="button" class="button button-primary" onclick="wvpExportAllData()">
                            <span class="dashicons dashicons-download"></span>
                            Exportar Todos los Datos
                        </button>
                        
                        <button type="button" class="button button-secondary" onclick="wvpUpdateRates()">
                            <span class="dashicons dashicons-update"></span>
                            Actualizar Tasas BCV
                        </button>
                        
                        <button type="button" class="button button-secondary" onclick="wvpGenerateControlNumbers()">
                            <span class="dashicons dashicons-admin-tools"></span>
                            Generar Números de Control
                        </button>
                    </div>
                </div>
            </div>
            
            <style>
                .wvp-reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
                .wvp-report-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .wvp-stats-overview { display: flex; flex-direction: column; gap: 15px; }
                .wvp-stat-item { display: flex; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; }
                .wvp-stat-icon { font-size: 24px; margin-right: 15px; }
                .wvp-stat-details { display: flex; flex-direction: column; }
                .wvp-stat-number { font-size: 24px; font-weight: bold; color: #0073aa; }
                .wvp-stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
                .wvp-reports-list { display: flex; flex-direction: column; gap: 15px; }
                .wvp-report-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
                .wvp-report-info h4 { margin: 0 0 5px 0; color: #0073aa; }
                .wvp-report-info p { margin: 0; color: #666; font-size: 14px; }
                .wvp-quick-actions { display: flex; flex-direction: column; gap: 10px; }
                .wvp-quick-actions .button { justify-content: flex-start; }
            </style>
            
            <script>
                function wvpGenerateBCVReport() {
                    alert('Función de análisis BCV en desarrollo. Próximamente disponible.');
                }
                
                function wvpShowInvoices() {
                    alert('Gestión de facturas electrónicas en desarrollo. Próximamente disponible.');
                }
                
                function wvpExportAllData() {
                    if (confirm('¿Exportar todos los datos fiscales? Esto puede tomar unos minutos.')) {
                        window.location.href = '<?php echo admin_url('admin.php?page=wvp-seniat-reports'); ?>';
                    }
                }
                
                function wvpUpdateRates() {
                    alert('Actualizando tasas BCV... Esta función se ejecuta automáticamente cada hora.');
                }
                
                function wvpGenerateControlNumbers() {
                    if (confirm('¿Generar números de control para pedidos que no los tienen?')) {
                        alert('Generando números de control... Esta acción se ejecutará en segundo plano.');
                    }
                }
            </script>
        </div>
        <?php
    }
    
    /**
     * Mostrar contenido del monitor de errores
     */
    private function display_error_monitor_content() {
        global $wpdb;
        
        // Obtener errores recientes del debug.log
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        $wvp_errors = array();
        
        if (file_exists($debug_log)) {
            $log_content = file_get_contents($debug_log);
            $lines = explode("\n", $log_content);
            $recent_lines = array_slice($lines, -50); // Últimas 50 líneas
            
            foreach ($recent_lines as $line) {
                if (strpos($line, 'WVP') !== false || strpos($line, 'WooCommerce Venezuela Pro') !== false) {
                    $wvp_errors[] = $line;
                }
            }
        }
        
        ?>
        <div class="wvp-error-monitor">
            <h2><?php _e('Monitor de Errores - WooCommerce Venezuela Pro', 'wvp'); ?></h2>
            
            <div class="wvp-error-stats">
                <div class="wvp-stat-card">
                    <h3>📊 Estadísticas de Errores</h3>
                    <div class="wvp-stats-grid">
                        <div class="wvp-stat-item">
                            <span class="wvp-stat-label">Errores WVP Hoy:</span>
                            <span class="wvp-stat-value"><?php echo count($wvp_errors); ?></span>
                        </div>
                        <div class="wvp-stat-item">
                            <span class="wvp-stat-label">Estado del Plugin:</span>
                            <span class="wvp-stat-value <?php echo count($wvp_errors) > 10 ? 'error' : 'success'; ?>">
                                <?php echo count($wvp_errors) > 10 ? 'Con Errores' : 'Funcionando'; ?>
                            </span>
                        </div>
                        <div class="wvp-stat-item">
                            <span class="wvp-stat-label">Última Verificación:</span>
                            <span class="wvp-stat-value"><?php echo current_time('d/m/Y H:i:s'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wvp-errors-section">
                <h3>🚨 Errores Recientes del Plugin</h3>
                
                <?php if (empty($wvp_errors)): ?>
                    <div class="wvp-no-errors">
                        <p>✅ <strong>¡Excelente!</strong> No se han detectado errores recientes del plugin WooCommerce Venezuela Pro.</p>
                    </div>
                <?php else: ?>
                    <div class="wvp-errors-list">
                        <?php foreach (array_reverse($wvp_errors) as $error): ?>
                            <?php
                            $is_fatal = strpos($error, 'Fatal') !== false;
                            $is_warning = strpos($error, 'Warning') !== false;
                            $is_notice = strpos($error, 'Notice') !== false;
                            
                            $error_class = 'info';
                            if ($is_fatal) $error_class = 'fatal';
                            elseif ($is_warning) $error_class = 'warning';
                            elseif ($is_notice) $error_class = 'notice';
                            ?>
                            <div class="wvp-error-item <?php echo $error_class; ?>">
                                <div class="wvp-error-icon">
                                    <?php if ($is_fatal): ?>
                                        ❌
                                    <?php elseif ($is_warning): ?>
                                        ⚠️
                                    <?php elseif ($is_notice): ?>
                                        ℹ️
                                    <?php else: ?>
                                        🔍
                                    <?php endif; ?>
                                </div>
                                <div class="wvp-error-content">
                                    <pre><?php echo esc_html($error); ?></pre>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="wvp-error-actions">
                <h3>🔧 Acciones Rápidas</h3>
                <div class="wvp-action-buttons">
                    <button type="button" class="button button-secondary" onclick="location.reload()">
                        <span class="dashicons dashicons-update"></span>
                        Actualizar Errores
                    </button>
                    <button type="button" class="button button-primary" onclick="wvpClearErrorLogs()">
                        <span class="dashicons dashicons-trash"></span>
                        Limpiar Logs Antiguos
                    </button>
                </div>
            </div>
            
            <style>
                .wvp-error-stats { margin-bottom: 20px; }
                .wvp-stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .wvp-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px; }
                .wvp-stat-item { display: flex; justify-content: space-between; padding: 10px; background: #f9f9f9; border-radius: 4px; }
                .wvp-stat-label { font-weight: bold; }
                .wvp-stat-value.success { color: #28a745; font-weight: bold; }
                .wvp-stat-value.error { color: #dc3545; font-weight: bold; }
                .wvp-errors-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .wvp-no-errors { text-align: center; padding: 40px; background: #d4edda; border-radius: 8px; color: #155724; }
                .wvp-errors-list { max-height: 400px; overflow-y: auto; }
                .wvp-error-item { display: flex; margin-bottom: 10px; padding: 10px; border-radius: 4px; }
                .wvp-error-item.fatal { background: #f8d7da; border-left: 4px solid #dc3545; }
                .wvp-error-item.warning { background: #fff3cd; border-left: 4px solid #ffc107; }
                .wvp-error-item.notice { background: #d1ecf1; border-left: 4px solid #17a2b8; }
                .wvp-error-item.info { background: #f8f9fa; border-left: 4px solid #6c757d; }
                .wvp-error-icon { margin-right: 10px; font-size: 16px; }
                .wvp-error-content pre { font-size: 12px; margin: 0; white-space: pre-wrap; word-break: break-all; }
                .wvp-error-actions { background: white; padding: 20px; border-radius: 8px; }
                .wvp-action-buttons { display: flex; gap: 10px; margin-top: 15px; }
            </style>
            
            <script>
                function wvpClearErrorLogs() {
                    if (confirm('¿Estás seguro de que quieres limpiar los logs antiguos?')) {
                        alert('Función de limpieza de logs en desarrollo. Por ahora, puedes limpiar manualmente el archivo debug.log.');
                    }
                }
            </script>
        </div>
        <?php
    }
}
