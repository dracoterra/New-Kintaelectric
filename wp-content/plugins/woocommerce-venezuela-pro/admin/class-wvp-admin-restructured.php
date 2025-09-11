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
        register_setting('wvp_general_settings', 'wvp_general_settings');
        register_setting('wvp_payment_settings', 'wvp_payment_settings');
        register_setting('wvp_fiscal_settings', 'wvp_fiscal_settings');
        register_setting('wvp_shipping_settings', 'wvp_shipping_settings');
        register_setting('wvp_notification_settings', 'wvp_notification_settings');
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
            case 'help':
                $this->display_help_content();
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
                                       value="1" <?php checked(get_option('wvp_show_igtf', '1'), '1'); ?> />
                                <?php _e('Mostrar IGTF en el checkout.', 'wvp'); ?>
                            </label>
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
        
        wp_enqueue_script(
            'wvp-admin-restructured',
            WVP_PLUGIN_URL . 'assets/js/admin-restructured.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        wp_localize_script('wvp-admin-restructured', 'wvp_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_admin_nonce'),
            'strings' => array(
                'saving' => __('Guardando...', 'wvp'),
                'saved' => __('Guardado correctamente', 'wvp'),
                'error' => __('Error al guardar', 'wvp')
            )
        ));
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
}
