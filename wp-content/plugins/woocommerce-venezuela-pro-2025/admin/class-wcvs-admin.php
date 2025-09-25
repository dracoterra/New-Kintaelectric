<?php
/**
 * Clase de administración del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar la administración del plugin
 */
class WCVS_Admin {

    /**
     * Versión del plugin
     *
     * @var string
     */
    private $version;

    /**
     * Instancia del plugin
     *
     * @var WCVS_Core
     */
    private $plugin;

    /**
     * Constructor
     *
     * @param string $version Versión del plugin
     */
    public function __construct($version) {
        $this->version = $version;
        $this->plugin = WCVS_Core::get_instance();
    }

    /**
     * Registrar hooks de administración
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            'WooCommerce Venezuela Suite',
            'Venezuela Suite',
            'manage_options',
            'wcvs-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-admin-site-alt3',
            30
        );

        // Submenús
        add_submenu_page(
            'wcvs-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'wcvs-dashboard',
            array($this, 'render_dashboard')
        );

        add_submenu_page(
            'wcvs-dashboard',
            'Configuración',
            'Configuración',
            'manage_options',
            'wcvs-settings',
            array($this, 'render_settings')
        );

        add_submenu_page(
            'wcvs-dashboard',
            'Módulos',
            'Módulos',
            'manage_options',
            'wcvs-modules',
            array($this, 'render_modules')
        );

        add_submenu_page(
            'wcvs-dashboard',
            'Reportes SENIAT',
            'Reportes SENIAT',
            'manage_options',
            'wcvs-seniat',
            array($this, 'render_seniat')
        );

        add_submenu_page(
            'wcvs-dashboard',
            'Estado BCV',
            'Estado BCV',
            'manage_options',
            'wcvs-bcv-status',
            array($this, 'render_bcv_status')
        );

        add_submenu_page(
            'wcvs-dashboard',
            'Ayuda',
            'Ayuda',
            'manage_options',
            'wcvs-help',
            array($this, 'render_help')
        );
    }

    /**
     * Cargar estilos de administración
     */
    public function enqueue_styles($hook) {
        // Solo cargar en páginas del plugin
        if (strpos($hook, 'wcvs-') === false) {
            return;
        }

        wp_enqueue_style(
            'wcvs-admin-styles',
            WCVS_PLUGIN_URL . 'admin/css/wcvs-admin.css',
            array(),
            $this->version
        );
    }

    /**
     * Cargar scripts de administración
     */
    public function enqueue_scripts($hook) {
        // Solo cargar en páginas del plugin
        if (strpos($hook, 'wcvs-') === false) {
            return;
        }

        wp_enqueue_script(
            'wcvs-admin-script',
            WCVS_PLUGIN_URL . 'admin/js/wcvs-admin.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localizar script para AJAX
        wp_localize_script('wcvs-admin-script', 'wcvs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcvs_admin_nonce'),
            'strings' => array(
                'saving' => 'Guardando...',
                'saved' => 'Configuración guardada',
                'error' => 'Error al guardar',
                'testing' => 'Probando conexión...',
                'testing_success' => 'Conexión exitosa',
                'testing_error' => 'Error en conexión',
                'confirm_reset' => '¿Estás seguro de que quieres restablecer la configuración?',
                'confirm_delete' => '¿Estás seguro de que quieres eliminar este elemento?'
            )
        ));
    }

    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        // Registrar configuraciones de cada sección
        $sections = array(
            'general' => 'Configuración General',
            'bcv' => 'Configuración BCV',
            'currency' => 'Configuración de Moneda',
            'taxes' => 'Configuración Fiscal',
            'payments' => 'Configuración de Pagos',
            'shipping' => 'Configuración de Envíos',
            'seniat' => 'Configuración SENIAT',
            'reports' => 'Configuración de Reportes',
            'price_display' => 'Configuración de Visualización',
            'notifications' => 'Configuración de Notificaciones',
            'onboarding' => 'Configuración de Onboarding',
            'help' => 'Configuración de Ayuda'
        );

        foreach ($sections as $section => $title) {
            add_settings_section(
                'wcvs_' . $section,
                $title,
                null,
                'wcvs_settings'
            );
        }
    }

    /**
     * Mostrar avisos de administración
     */
    public function admin_notices() {
        // Verificar si WooCommerce está activo
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>WooCommerce Venezuela Suite:</strong> ';
            echo 'Se requiere WooCommerce para funcionar correctamente. ';
            echo '<a href="' . admin_url('plugin-install.php?s=woocommerce&tab=search&type=term') . '">Instalar WooCommerce</a>';
            echo '</p></div>';
        }

        // Verificar si es la primera activación
        if (get_option('wcvs_first_activation', false)) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>¡Bienvenido a WooCommerce Venezuela Suite!</strong> ';
            echo 'Configura tu tienda para Venezuela en minutos. ';
            echo '<a href="' . admin_url('admin.php?page=wcvs-onboarding') . '">Comenzar configuración</a></p>';
            echo '</div>';
        }

        // Verificar estado del plugin BCV
        $bcv_status = $this->plugin->get_bcv_integration()->get_integration_status();
        if (!$bcv_status['bcv_available']) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Plugin BCV Dólar Tracker:</strong> ';
            echo 'Para obtener tasas de cambio automáticas, instala el plugin BCV Dólar Tracker. ';
            echo '<a href="' . admin_url('plugin-install.php?s=bcv+dolar+tracker&tab=search&type=term') . '">Instalar Plugin BCV</a></p>';
            echo '</div>';
        }
    }

    /**
     * Renderizar dashboard principal
     */
    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die('Lo siento, no tienes permiso para acceder a esta página.');
        }

        // Obtener estadísticas
        $stats = $this->get_dashboard_stats();
        $bcv_status = $this->plugin->get_bcv_integration()->get_integration_status();
        $module_stats = $this->plugin->get_module_manager()->get_module_stats();

        ?>
        <div class="wrap wcvs-dashboard">
            <h1>🇻🇪 WooCommerce Venezuela Suite</h1>
            <p class="description">Suite completa para localizar WooCommerce al mercado venezolano</p>

            <!-- Estadísticas principales -->
            <div class="wcvs-stats-grid">
                <div class="wcvs-stat-card">
                    <div class="wcvs-stat-icon">📊</div>
                    <div class="wcvs-stat-content">
                        <h3><?php echo esc_html($module_stats['active']); ?></h3>
                        <p>Módulos Activos</p>
                    </div>
                </div>

                <div class="wcvs-stat-card">
                    <div class="wcvs-stat-icon">💰</div>
                    <div class="wcvs-stat-content">
                        <h3><?php echo esc_html(number_format($bcv_status['current_rate']['rate'], 2, ',', '.')); ?></h3>
                        <p>Tasa BCV Actual (Bs.)</p>
                    </div>
                </div>

                <div class="wcvs-stat-card">
                    <div class="wcvs-stat-icon">📈</div>
                    <div class="wcvs-stat-content">
                        <h3><?php echo esc_html($stats['total_orders']); ?></h3>
                        <p>Pedidos Procesados</p>
                    </div>
                </div>

                <div class="wcvs-stat-card">
                    <div class="wcvs-stat-icon">🏢</div>
                    <div class="wcvs-stat-content">
                        <h3><?php echo esc_html($stats['seniat_reports']); ?></h3>
                        <p>Reportes SENIAT</p>
                    </div>
                </div>
            </div>

            <!-- Estado del sistema -->
            <div class="wcvs-status-section">
                <h2>Estado del Sistema</h2>
                
                <div class="wcvs-status-grid">
                    <div class="wcvs-status-card">
                        <h3>🔄 Plugin BCV</h3>
                        <div class="wcvs-status-content">
                            <?php if ($bcv_status['bcv_available']): ?>
                                <span class="wcvs-status-badge wcvs-status-success">✅ Disponible</span>
                                <p>Versión: <?php echo esc_html($bcv_status['bcv_info']['version']); ?></p>
                                <p>Última actualización: <?php echo esc_html($bcv_status['bcv_info']['last_update']); ?></p>
                            <?php else: ?>
                                <span class="wcvs-status-badge wcvs-status-error">❌ No disponible</span>
                                <p>Usando sistema de fallback</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="wcvs-status-card">
                        <h3>📦 Módulos</h3>
                        <div class="wcvs-status-content">
                            <span class="wcvs-status-badge wcvs-status-info">ℹ️ <?php echo esc_html($module_stats['percentage_active']); ?>% Activos</span>
                            <p>Total: <?php echo esc_html($module_stats['total']); ?> módulos</p>
                            <p>Activos: <?php echo esc_html($module_stats['active']); ?></p>
                        </div>
                    </div>

                    <div class="wcvs-status-card">
                        <h3>⚙️ Configuración</h3>
                        <div class="wcvs-status-content">
                            <?php
                            $settings_stats = $this->plugin->get_settings()->get_settings_stats();
                            $config_percentage = $settings_stats['configuration_percentage'];
                            ?>
                            <span class="wcvs-status-badge wcvs-status-<?php echo $config_percentage > 50 ? 'success' : 'warning'; ?>">
                                <?php echo $config_percentage > 50 ? '✅' : '⚠️'; ?> <?php echo esc_html($config_percentage); ?>%
                            </span>
                            <p>Configurado: <?php echo esc_html($settings_stats['configured_settings']); ?> de <?php echo esc_html($settings_stats['total_settings']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="wcvs-quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="wcvs-action-buttons">
                    <a href="<?php echo admin_url('admin.php?page=wcvs-settings'); ?>" class="button button-primary">
                        ⚙️ Configuración
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wcvs-modules'); ?>" class="button">
                        📦 Módulos
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wcvs-seniat'); ?>" class="button">
                        🏢 Reportes SENIAT
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wcvs-bcv-status'); ?>" class="button">
                        🔄 Estado BCV
                    </a>
                    <button type="button" class="button" onclick="wcvsSyncBCV()">
                        🔄 Sincronizar BCV
                    </button>
                </div>
            </div>

            <!-- Información del sistema -->
            <div class="wcvs-system-info">
                <h2>Información del Sistema</h2>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td><strong>Versión del Plugin</strong></td>
                            <td><?php echo esc_html(WCVS_VERSION); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Versión de WooCommerce</strong></td>
                            <td><?php echo esc_html(WC()->version); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Versión de WordPress</strong></td>
                            <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Versión de PHP</strong></td>
                            <td><?php echo esc_html(PHP_VERSION); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Activación</strong></td>
                            <td><?php echo esc_html(get_option('wcvs_general')['activation_date'] ?? 'N/A'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizar página de estado BCV
     */
    public function render_bcv_status() {
        if (!current_user_can('manage_options')) {
            wp_die('Lo siento, no tienes permiso para acceder a esta página.');
        }

        $bcv_status = $this->plugin->get_bcv_integration()->get_integration_status();

        ?>
        <div class="wrap wcvs-bcv-status">
            <h1>🔄 Estado del Plugin BCV Dólar Tracker</h1>
            <p class="description">Monitoreo en tiempo real del plugin BCV y sincronización de tasas</p>

            <?php if ($bcv_status['bcv_available']): ?>
                <!-- Plugin BCV disponible -->
                <div class="wcvs-bcv-available">
                    <div class="wcvs-status-header">
                        <h2>✅ Plugin BCV Disponible</h2>
                        <p>El plugin BCV Dólar Tracker está instalado y funcionando correctamente</p>
                    </div>

                    <div class="wcvs-bcv-info-grid">
                        <div class="wcvs-info-card">
                            <h3>📊 Información del Plugin</h3>
                            <table class="widefat">
                                <tbody>
                                    <tr>
                                        <td><strong>Versión</strong></td>
                                        <td><?php echo esc_html($bcv_status['bcv_info']['version']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tabla de Base de Datos</strong></td>
                                        <td><?php echo $bcv_status['bcv_info']['table_exists'] ? '✅ Existe' : '❌ No existe'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cron Activo</strong></td>
                                        <td><?php echo $bcv_status['bcv_info']['cron_active'] ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total de Registros</strong></td>
                                        <td><?php echo esc_html($bcv_status['bcv_info']['total_records']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="wcvs-info-card">
                            <h3>💰 Tasa Actual</h3>
                            <div class="wcvs-rate-display">
                                <div class="wcvs-rate-value">
                                    <?php echo esc_html(number_format($bcv_status['current_rate']['rate'], 4, ',', '.')); ?>
                                </div>
                                <div class="wcvs-rate-label">Bolívares por Dólar</div>
                                <div class="wcvs-rate-source">
                                    Fuente: <?php echo esc_html($bcv_status['current_rate']['source']); ?>
                                </div>
                                <div class="wcvs-rate-timestamp">
                                    Última actualización: <?php echo esc_html($bcv_status['current_rate']['timestamp']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="wcvs-info-card">
                            <h3>⚙️ Configuración del Cron</h3>
                            <?php if (!empty($bcv_status['bcv_info']['cron_settings'])): ?>
                                <table class="widefat">
                                    <tbody>
                                        <tr>
                                            <td><strong>Estado</strong></td>
                                            <td><?php echo $bcv_status['bcv_info']['cron_settings']['enabled'] ? '✅ Habilitado' : '❌ Deshabilitado'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Intervalo</strong></td>
                                            <td><?php echo esc_html($bcv_status['bcv_info']['cron_settings']['hours']); ?>h <?php echo esc_html($bcv_status['bcv_info']['cron_settings']['minutes']); ?>m</td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No hay configuración de cron disponible</p>
                            <?php endif; ?>
                        </div>

                        <div class="wcvs-info-card">
                            <h3>🔄 Sincronización</h3>
                            <div class="wcvs-sync-info">
                                <p><strong>Estado:</strong> <?php echo $bcv_status['sync_enabled'] ? '✅ Activa' : '❌ Inactiva'; ?></p>
                                <p><strong>Última sincronización:</strong> <?php echo esc_html($bcv_status['last_sync']); ?></p>
                                <p><strong>Fallback activo:</strong> <?php echo $bcv_status['fallback_active'] ? '❌ Sí' : '✅ No'; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="wcvs-bcv-actions">
                        <h3>Acciones</h3>
                        <div class="wcvs-action-buttons">
                            <button type="button" class="button button-primary" onclick="wcvsSyncBCV()">
                                🔄 Sincronizar Ahora
                            </button>
                            <button type="button" class="button" onclick="wcvsTestBCV()">
                                🧪 Probar Conexión
                            </button>
                            <a href="<?php echo admin_url('admin.php?page=bcv-dolar-tracker'); ?>" class="button">
                                ⚙️ Configurar Plugin BCV
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Plugin BCV no disponible -->
                <div class="wcvs-bcv-unavailable">
                    <div class="wcvs-status-header">
                        <h2>❌ Plugin BCV No Disponible</h2>
                        <p>El plugin BCV Dólar Tracker no está instalado o no está funcionando correctamente</p>
                    </div>

                    <div class="wcvs-fallback-info">
                        <h3>🔄 Sistema de Fallback Activo</h3>
                        <p>WooCommerce Venezuela Suite está usando el sistema de fallback para obtener tasas de cambio:</p>
                        
                        <div class="wcvs-fallback-rate">
                            <div class="wcvs-rate-value">
                                <?php echo esc_html(number_format($bcv_status['current_rate']['rate'], 4, ',', '.')); ?>
                            </div>
                            <div class="wcvs-rate-label">Bolívares por Dólar (Fallback)</div>
                            <div class="wcvs-rate-source">
                                Fuente: <?php echo esc_html($bcv_status['current_rate']['source']); ?>
                            </div>
                        </div>

                        <div class="wcvs-install-bcv">
                            <h3>📥 Instalar Plugin BCV</h3>
                            <p>Para obtener tasas de cambio automáticas y en tiempo real, instala el plugin BCV Dólar Tracker:</p>
                            
                            <div class="wcvs-install-actions">
                                <a href="<?php echo admin_url('plugin-install.php?s=bcv+dolar+tracker&tab=search&type=term'); ?>" class="button button-primary">
                                    🔍 Buscar Plugin BCV
                                </a>
                                <a href="https://kinta-electric.com/plugins/bcv-dolar-tracker" target="_blank" class="button">
                                    📥 Descargar Manualmente
                                </a>
                            </div>

                            <div class="wcvs-bcv-benefits">
                                <h4>Beneficios del Plugin BCV:</h4>
                                <ul>
                                    <li>✅ Tasas de cambio automáticas cada hora</li>
                                    <li>✅ Datos oficiales del Banco Central de Venezuela</li>
                                    <li>✅ Historial de tasas de cambio</li>
                                    <li>✅ Sistema de cache inteligente</li>
                                    <li>✅ Notificaciones de cambios de tasa</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Obtener estadísticas del dashboard
     *
     * @return array
     */
    private function get_dashboard_stats() {
        global $wpdb;

        // Obtener estadísticas de pedidos
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'");
        
        // Obtener estadísticas de reportes SENIAT
        $seniat_reports = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wcvs_seniat_reports");

        return array(
            'total_orders' => $total_orders,
            'seniat_reports' => $seniat_reports
        );
    }

    /**
     * Guardar configuraciones via AJAX
     */
    public function save_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $section = sanitize_text_field($_POST['section']);
        $settings = $_POST['settings'];

        $this->plugin->get_settings()->set_multiple($section, $settings);

        wp_send_json_success('Configuraciones guardadas correctamente');
    }

    /**
     * Probar conexión BCV via AJAX
     */
    public function test_bcv_connection() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $bcv_integration = $this->plugin->get_bcv_integration();
        $rate = $bcv_integration->get_current_rate();

        if ($rate && $rate['rate'] > 0) {
            wp_send_json_success(array(
                'message' => 'Conexión exitosa',
                'rate' => $rate['rate'],
                'source' => $rate['source']
            ));
        } else {
            wp_send_json_error('No se pudo obtener la tasa de cambio');
        }
    }

    /**
     * Sincronizar configuración BCV via AJAX
     */
    public function sync_bcv_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $bcv_integration = $this->plugin->get_bcv_integration();
        $result = $bcv_integration->sync_settings();

        if ($result) {
            wp_send_json_success('Configuración BCV sincronizada correctamente');
        } else {
            wp_send_json_error('Error al sincronizar configuración BCV');
        }
    }

    /**
     * Obtener estado de módulo via AJAX
     */
    public function get_module_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $module_key = sanitize_text_field($_POST['module_key']);
        $module_manager = $this->plugin->get_module_manager();
        
        $module = $module_manager->get_module($module_key);
        $is_active = $module_manager->is_module_active($module_key);

        wp_send_json_success(array(
            'module' => $module,
            'active' => $is_active
        ));
    }

    /**
     * Toggle de módulo via AJAX
     */
    public function toggle_module() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $module_key = sanitize_text_field($_POST['module_key']);
        $module_manager = $this->plugin->get_module_manager();
        
        $result = $module_manager->toggle_module($module_key);

        if ($result) {
            $status = $module_manager->is_module_active($module_key) ? 'activado' : 'desactivado';
            wp_send_json_success(array(
                'message' => "Módulo {$status} correctamente",
                'active' => $module_manager->is_module_active($module_key)
            ));
        } else {
            wp_send_json_error('Error al cambiar estado del módulo');
        }
    }

    /**
     * Renderizar página de configuración
     */
    public function render_settings() {
        // Implementar página de configuración
        echo '<div class="wrap"><h1>Configuración</h1><p>Página de configuración en desarrollo...</p></div>';
    }

    /**
     * Renderizar página de módulos
     */
    public function render_modules() {
        // Implementar página de módulos
        echo '<div class="wrap"><h1>Módulos</h1><p>Página de módulos en desarrollo...</p></div>';
    }

    /**
     * Renderizar página de reportes SENIAT
     */
    public function render_seniat() {
        // Implementar página de reportes SENIAT
        echo '<div class="wrap"><h1>Reportes SENIAT</h1><p>Página de reportes SENIAT en desarrollo...</p></div>';
    }

    /**
     * Renderizar página de ayuda
     */
    public function render_help() {
        // Implementar página de ayuda
        echo '<div class="wrap"><h1>Ayuda</h1><p>Página de ayuda en desarrollo...</p></div>';
    }
}
