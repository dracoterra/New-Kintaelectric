<?php
/**
 * Admin - Sistema de administración del plugin
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para el sistema de administración
 */
class WVS_Admin {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Suite::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Menús de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Encolar scripts y estilos
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_wvs_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wvs_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        
        // Hooks de WooCommerce
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_order_venezuelan_data'));
    }
    
    /**
     * Añadir menús de administración
     */
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            'Suite Venezuela',
            'Suite Venezuela',
            'manage_woocommerce',
            'wvs-dashboard',
            array($this, 'display_dashboard'),
            'dashicons-admin-site-alt3',
            56
        );
        
        // Dashboard
        add_submenu_page(
            'wvs-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_woocommerce',
            'wvs-dashboard',
            array($this, 'display_dashboard')
        );
        
        // Módulos
        add_submenu_page(
            'wvs-dashboard',
            'Módulos',
            'Módulos',
            'manage_woocommerce',
            'wvs-modules',
            array($this, 'display_modules')
        );
        
        // Configuración
        add_submenu_page(
            'wvs-dashboard',
            'Configuración',
            'Configuración',
            'manage_woocommerce',
            'wvs-settings',
            array($this, 'display_settings')
        );
        
        // Reportes
        add_submenu_page(
            'wvs-dashboard',
            'Reportes',
            'Reportes',
            'manage_woocommerce',
            'wvs-reports',
            array($this, 'display_reports')
        );
        
        // Ayuda
        add_submenu_page(
            'wvs-dashboard',
            'Ayuda',
            'Ayuda',
            'manage_woocommerce',
            'wvs-help',
            array($this, 'display_help')
        );
    }
    
    /**
     * Encolar assets del admin
     */
    public function enqueue_admin_assets($hook) {
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
            array('jquery', 'wp-util'),
            WVS_VERSION,
            true
        );
        
        // Chart.js para gráficos
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '3.9.1',
            true
        );
        
        // Localizar script
        wp_localize_script('wvs-admin', 'wvs_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvs_admin_nonce'),
            'strings' => array(
                'loading' => __('Cargando...', 'wvs'),
                'error' => __('Error al procesar la solicitud', 'wvs'),
                'success' => __('Operación exitosa', 'wvs'),
                'confirm' => __('¿Estás seguro?', 'wvs'),
                'save' => __('Guardar', 'wvs'),
                'cancel' => __('Cancelar', 'wvs'),
                'activate' => __('Activar', 'wvs'),
                'deactivate' => __('Desactivar', 'wvs')
            )
        ));
    }
    
    /**
     * Mostrar dashboard
     */
    public function display_dashboard() {
        $dashboard_data = $this->get_dashboard_data();
        ?>
        <div class="wrap wvs-dashboard">
            <div class="wvs-header">
                <h1>Dashboard - Suite Venezuela</h1>
                <p class="description">Panel de control principal del plugin Suite Venezuela para WooCommerce</p>
            </div>
            
            <!-- Estadísticas principales -->
            <div class="wvs-stats-grid">
                <div class="wvs-stat-card">
                    <div class="wvs-stat-icon">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <div class="wvs-stat-content">
                        <h3><?php echo wc_price($dashboard_data['total_sales']); ?></h3>
                        <p><?php _e('Ventas Totales', 'wvs'); ?></p>
                    </div>
                </div>
                
                <div class="wvs-stat-card">
                    <div class="wvs-stat-icon">
                        <span class="dashicons dashicons-cart"></span>
                    </div>
                    <div class="wvs-stat-content">
                        <h3><?php echo number_format($dashboard_data['total_orders']); ?></h3>
                        <p><?php _e('Pedidos Totales', 'wvs'); ?></p>
                    </div>
                </div>
                
                <div class="wvs-stat-card">
                    <div class="wvs-stat-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="wvs-stat-content">
                        <h3><?php echo number_format($dashboard_data['bcv_rate'], 2, ',', '.'); ?> Bs.</h3>
                        <p><?php _e('Tasa BCV Actual', 'wvs'); ?></p>
                    </div>
                </div>
                
                <div class="wvs-stat-card">
                    <div class="wvs-stat-icon">
                        <span class="dashicons dashicons-admin-tools"></span>
                    </div>
                    <div class="wvs-stat-content">
                        <h3><?php echo count($dashboard_data['active_modules']); ?></h3>
                        <p><?php _e('Módulos Activos', 'wvs'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos -->
            <div class="wvs-charts-grid">
                <div class="wvs-chart-card">
                    <h3><?php _e('Ventas por Mes', 'wvs'); ?></h3>
                    <canvas id="sales-chart"></canvas>
                </div>
                
                <div class="wvs-chart-card">
                    <h3><?php _e('Métodos de Pago', 'wvs'); ?></h3>
                    <canvas id="payment-methods-chart"></canvas>
                </div>
            </div>
            
            <!-- Módulos activos -->
            <div class="wvs-modules-overview">
                <h3><?php _e('Módulos Activos', 'wvs'); ?></h3>
                <div class="wvs-modules-grid">
                    <?php foreach ($dashboard_data['active_modules'] as $module): ?>
                        <div class="wvs-module-card">
                            <div class="wvs-module-icon">
                                <span class="dashicons dashicons-<?php echo esc_attr($module['icon']); ?>"></span>
                            </div>
                            <div class="wvs-module-content">
                                <h4><?php echo esc_html($module['name']); ?></h4>
                                <p><?php echo esc_html($module['description']); ?></p>
                                <span class="wvs-module-status active"><?php _e('Activo', 'wvs'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Acciones rápidas -->
            <div class="wvs-quick-actions">
                <h3><?php _e('Acciones Rápidas', 'wvs'); ?></h3>
                <div class="wvs-actions-grid">
                    <a href="<?php echo admin_url('admin.php?page=wvs-modules'); ?>" class="wvs-action-button">
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php _e('Gestionar Módulos', 'wvs'); ?>
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=wvs-settings'); ?>" class="wvs-action-button">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php _e('Configuración', 'wvs'); ?>
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=wvs-reports'); ?>" class="wvs-action-button">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php _e('Ver Reportes', 'wvs'); ?>
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=wvs-help'); ?>" class="wvs-action-button">
                        <span class="dashicons dashicons-sos"></span>
                        <?php _e('Ayuda', 'wvs'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Gráfico de ventas
            const salesCtx = document.getElementById('sales-chart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dashboard_data['sales_chart']['labels']); ?>,
                    datasets: [{
                        label: '<?php _e('Ventas USD', 'wvs'); ?>',
                        data: <?php echo json_encode($dashboard_data['sales_chart']['data']); ?>,
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Gráfico de métodos de pago
            const paymentCtx = document.getElementById('payment-methods-chart').getContext('2d');
            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($dashboard_data['payment_methods']['labels']); ?>,
                    datasets: [{
                        data: <?php echo json_encode($dashboard_data['payment_methods']['data']); ?>,
                        backgroundColor: [
                            '#0073aa',
                            '#00a32a',
                            '#d63638',
                            '#ff6900',
                            '#8c8f94'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Mostrar gestión de módulos
     */
    public function display_modules() {
        $module_manager = $this->plugin->module_manager;
        $modules = $module_manager->get_available_modules();
        $active_modules = $module_manager->get_active_modules();
        ?>
        <div class="wrap wvs-modules">
            <div class="wvs-header">
                <h1><?php _e('Gestión de Módulos', 'wvs'); ?></h1>
                <p class="description"><?php _e('Activa o desactiva módulos según las necesidades de tu tienda', 'wvs'); ?></p>
            </div>
            
            <div class="wvs-modules-grid">
                <?php foreach ($modules as $module_key => $module): ?>
                    <?php
                    $is_active = in_array($module_key, $active_modules);
                    $can_activate = !$module['required'] && $module_manager->check_module_dependencies($module_key);
                    $can_deactivate = !$module['required'] && !$module_manager->has_dependent_modules($module_key);
                    ?>
                    <div class="wvs-module-card <?php echo $is_active ? 'active' : 'inactive'; ?>">
                        <div class="wvs-module-header">
                            <div class="wvs-module-icon">
                                <span class="dashicons dashicons-<?php echo esc_attr($this->get_module_icon($module_key)); ?>"></span>
                            </div>
                            <div class="wvs-module-title">
                                <h3><?php echo esc_html($module['name']); ?></h3>
                                <span class="wvs-module-version">v<?php echo esc_html($module['version']); ?></span>
                            </div>
                            <div class="wvs-module-status">
                                <?php if ($module['required']): ?>
                                    <span class="wvs-status-required"><?php _e('Requerido', 'wvs'); ?></span>
                                <?php elseif ($is_active): ?>
                                    <span class="wvs-status-active"><?php _e('Activo', 'wvs'); ?></span>
                                <?php else: ?>
                                    <span class="wvs-status-inactive"><?php _e('Inactivo', 'wvs'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="wvs-module-content">
                            <p><?php echo esc_html($module['description']); ?></p>
                            
                            <?php if (!empty($module['dependencies'])): ?>
                                <div class="wvs-module-dependencies">
                                    <strong><?php _e('Dependencias:', 'wvs'); ?></strong>
                                    <?php echo implode(', ', array_map(function($dep) use ($modules) {
                                        return $modules[$dep]['name'];
                                    }, $module['dependencies'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wvs-module-actions">
                            <?php if ($module['required']): ?>
                                <button class="button button-secondary" disabled>
                                    <?php _e('Módulo Requerido', 'wvs'); ?>
                                </button>
                            <?php elseif ($is_active && $can_deactivate): ?>
                                <button class="button button-secondary wvs-toggle-module" 
                                        data-module="<?php echo esc_attr($module_key); ?>" 
                                        data-action="deactivate">
                                    <?php _e('Desactivar', 'wvs'); ?>
                                </button>
                            <?php elseif (!$is_active && $can_activate): ?>
                                <button class="button button-primary wvs-toggle-module" 
                                        data-module="<?php echo esc_attr($module_key); ?>" 
                                        data-action="activate">
                                    <?php _e('Activar', 'wvs'); ?>
                                </button>
                            <?php else: ?>
                                <button class="button button-secondary" disabled>
                                    <?php _e('No Disponible', 'wvs'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar configuración
     */
    public function display_settings() {
        $settings = get_option('wvs_settings', array());
        ?>
        <div class="wrap wvs-settings">
            <div class="wvs-header">
                <h1><?php _e('Configuración', 'wvs'); ?></h1>
                <p class="description"><?php _e('Configura las opciones principales de Suite Venezuela', 'wvs'); ?></p>
            </div>
            
            <form id="wvs-settings-form">
                <div class="wvs-settings-grid">
                    <!-- Configuración de Moneda -->
                    <div class="wvs-settings-section">
                        <h2><?php _e('Configuración de Moneda', 'wvs'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="currency_display"><?php _e('Visualización de Moneda', 'wvs'); ?></label>
                                </th>
                                <td>
                                    <select id="currency_display" name="currency_display">
                                        <option value="dual" <?php selected($settings['currency_display'] ?? 'dual', 'dual'); ?>>
                                            <?php _e('USD y VES', 'wvs'); ?>
                                        </option>
                                        <option value="usd_only" <?php selected($settings['currency_display'] ?? 'dual', 'usd_only'); ?>>
                                            <?php _e('Solo USD', 'wvs'); ?>
                                        </option>
                                        <option value="ves_only" <?php selected($settings['currency_display'] ?? 'dual', 'ves_only'); ?>>
                                            <?php _e('Solo VES', 'wvs'); ?>
                                        </option>
                                    </select>
                                    <p class="description"><?php _e('Cómo mostrar los precios en tu tienda', 'wvs'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="default_currency"><?php _e('Moneda por Defecto', 'wvs'); ?></label>
                                </th>
                                <td>
                                    <select id="default_currency" name="default_currency">
                                        <option value="USD" <?php selected($settings['default_currency'] ?? 'USD', 'USD'); ?>>
                                            <?php _e('USD', 'wvs'); ?>
                                        </option>
                                        <option value="VES" <?php selected($settings['default_currency'] ?? 'USD', 'VES'); ?>>
                                            <?php _e('VES', 'wvs'); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="bcv_integration"><?php _e('Integración BCV', 'wvs'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="bcv_integration" name="bcv_integration" 
                                               value="1" <?php checked($settings['bcv_integration'] ?? true, true); ?>>
                                        <?php _e('Usar tasa de cambio del BCV automáticamente', 'wvs'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Configuración Fiscal -->
                    <div class="wvs-settings-section">
                        <h2><?php _e('Configuración Fiscal', 'wvs'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="igtf_rate"><?php _e('Tasa IGTF (%)', 'wvs'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="igtf_rate" name="igtf_rate" 
                                           value="<?php echo esc_attr($settings['igtf_rate'] ?? 3.0); ?>" 
                                           step="0.1" min="0" max="100">
                                    <p class="description"><?php _e('Tasa del Impuesto a las Grandes Transacciones Financieras', 'wvs'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Configuración General -->
                    <div class="wvs-settings-section">
                        <h2><?php _e('Configuración General', 'wvs'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="help_system"><?php _e('Sistema de Ayuda', 'wvs'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="help_system" name="help_system" 
                                               value="1" <?php checked($settings['help_system'] ?? true, true); ?>>
                                        <?php _e('Mostrar sistema de ayuda integrado', 'wvs'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="notifications"><?php _e('Notificaciones', 'wvs'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="notifications" name="notifications" 
                                               value="1" <?php checked($settings['notifications'] ?? true, true); ?>>
                                        <?php _e('Habilitar notificaciones del sistema', 'wvs'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="wvs-settings-actions">
                    <button type="submit" class="button button-primary">
                        <?php _e('Guardar Configuración', 'wvs'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Mostrar reportes
     */
    public function display_reports() {
        ?>
        <div class="wrap wvs-reports">
            <div class="wvs-header">
                <h1><?php _e('Reportes', 'wvs'); ?></h1>
                <p class="description"><?php _e('Reportes y análisis de Suite Venezuela', 'wvs'); ?></p>
            </div>
            
            <div class="wvs-reports-grid">
                <div class="wvs-report-card">
                    <h3><?php _e('Reportes SENIAT', 'wvs'); ?></h3>
                    <p><?php _e('Genera reportes fiscales para el SENIAT', 'wvs'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wvs-seniat-reports'); ?>" class="button button-primary">
                        <?php _e('Ver Reportes', 'wvs'); ?>
                    </a>
                </div>
                
                <div class="wvs-report-card">
                    <h3><?php _e('Analytics Avanzados', 'wvs'); ?></h3>
                    <p><?php _e('Análisis detallado de ventas y comportamiento', 'wvs'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wvs-analytics'); ?>" class="button button-primary">
                        <?php _e('Ver Analytics', 'wvs'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar ayuda
     */
    public function display_help() {
        ?>
        <div class="wrap wvs-help">
            <div class="wvs-header">
                <h1><?php _e('Ayuda y Soporte', 'wvs'); ?></h1>
                <p class="description"><?php _e('Encuentra ayuda y soporte para Suite Venezuela', 'wvs'); ?></p>
            </div>
            
            <div class="wvs-help-grid">
                <div class="wvs-help-card">
                    <h3><?php _e('Documentación', 'wvs'); ?></h3>
                    <p><?php _e('Guías completas y tutoriales', 'wvs'); ?></p>
                    <a href="#" class="button button-primary"><?php _e('Ver Documentación', 'wvs'); ?></a>
                </div>
                
                <div class="wvs-help-card">
                    <h3><?php _e('Video Tutoriales', 'wvs'); ?></h3>
                    <p><?php _e('Aprende paso a paso', 'wvs'); ?></p>
                    <a href="#" class="button button-primary"><?php _e('Ver Videos', 'wvs'); ?></a>
                </div>
                
                <div class="wvs-help-card">
                    <h3><?php _e('Soporte Técnico', 'wvs'); ?></h3>
                    <p><?php _e('Contacta con nuestro equipo', 'wvs'); ?></p>
                    <a href="#" class="button button-primary"><?php _e('Contactar', 'wvs'); ?></a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Obtener datos del dashboard
     * 
     * @return array
     */
    private function get_dashboard_data() {
        global $wpdb;
        
        // Obtener estadísticas básicas
        $total_sales = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}wc_orders WHERE status = 'wc-completed'");
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders WHERE status = 'wc-completed'");
        
        // Obtener tasa BCV
        $bcv_rate = $this->plugin->core_engine->get_bcv_rate() ?? 0;
        
        // Obtener módulos activos
        $module_manager = $this->plugin->module_manager;
        $active_modules = array();
        foreach ($module_manager->get_active_modules() as $module_key) {
            $module_info = $module_manager->get_module_info($module_key);
            if ($module_info) {
                $active_modules[] = array(
                    'name' => $module_info['name'],
                    'description' => $module_info['description'],
                    'icon' => $this->get_module_icon($module_key)
                );
            }
        }
        
        // Datos para gráficos (simplificados)
        $sales_chart = array(
            'labels' => array('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'),
            'data' => array(1000, 1500, 1200, 1800, 2000, 1600)
        );
        
        $payment_methods = array(
            'labels' => array('Zelle', 'Pago Móvil', 'Efectivo', 'Transferencia'),
            'data' => array(40, 30, 20, 10)
        );
        
        return array(
            'total_sales' => $total_sales ?? 0,
            'total_orders' => $total_orders ?? 0,
            'bcv_rate' => $bcv_rate,
            'active_modules' => $active_modules,
            'sales_chart' => $sales_chart,
            'payment_methods' => $payment_methods
        );
    }
    
    /**
     * Obtener icono de módulo
     * 
     * @param string $module_key
     * @return string
     */
    private function get_module_icon($module_key) {
        $icons = array(
            'core' => 'admin-tools',
            'currency' => 'money-alt',
            'payments' => 'bank',
            'shipping' => 'truck',
            'fiscal' => 'chart-bar',
            'invoicing' => 'media-document',
            'analytics' => 'chart-line',
            'notifications' => 'bell',
            'help' => 'sos',
            'ai' => 'admin-site-alt3'
        );
        
        return $icons[$module_key] ?? 'admin-tools';
    }
    
    /**
     * AJAX para guardar configuración
     */
    public function ajax_save_settings() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvs_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad', 'wvs')));
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'wvs')));
        }
        
        // Procesar configuración
        $settings = array(
            'currency_display' => sanitize_text_field($_POST['currency_display']),
            'default_currency' => sanitize_text_field($_POST['default_currency']),
            'bcv_integration' => isset($_POST['bcv_integration']),
            'igtf_rate' => floatval($_POST['igtf_rate']),
            'help_system' => isset($_POST['help_system']),
            'notifications' => isset($_POST['notifications'])
        );
        
        // Guardar configuración
        if (update_option('wvs_settings', $settings)) {
            wp_send_json_success(array('message' => __('Configuración guardada correctamente', 'wvs')));
        } else {
            wp_send_json_error(array('message' => __('Error al guardar la configuración', 'wvs')));
        }
    }
    
    /**
     * AJAX para obtener datos del dashboard
     */
    public function ajax_get_dashboard_data() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvs_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad', 'wvs')));
        }
        
        $data = $this->get_dashboard_data();
        wp_send_json_success($data);
    }
    
    /**
     * Mostrar datos venezolanos en pedidos
     * 
     * @param WC_Order $order
     */
    public function display_order_venezuelan_data($order) {
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        $rif = $order->get_meta('_billing_rif');
        
        if ($bcv_rate || $rif) {
            echo '<div class="wvs-order-data">';
            echo '<h4>' . __('Datos Venezolanos', 'wvs') . '</h4>';
            
            if ($bcv_rate) {
                echo '<p><strong>' . __('Tasa BCV:', 'wvs') . '</strong> ' . number_format($bcv_rate, 2, ',', '.') . ' Bs./USD</p>';
            }
            
            if ($rif) {
                echo '<p><strong>' . __('RIF:', 'wvs') . '</strong> ' . esc_html($rif) . '</p>';
            }
            
            echo '</div>';
        }
    }
}
