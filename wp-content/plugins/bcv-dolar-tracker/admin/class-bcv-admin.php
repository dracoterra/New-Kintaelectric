<?php
/**
 * Clase para administración del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Admin_Clean {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Hooks de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_bcv_save_cron_settings', array($this, 'save_cron_settings'));
        add_action('wp_ajax_bcv_test_scraping', array($this, 'test_scraping'));
        add_action('wp_ajax_bcv_clear_cache', array($this, 'clear_cache'));
        add_action('wp_ajax_bcv_get_prices_data', array($this, 'get_prices_data'));
        add_action('wp_ajax_bcv_toggle_cron', array($this, 'toggle_cron'));
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            'BCV Dólar Tracker',
            'BCV Dólar',
            'manage_options',
            'bcv-dolar-tracker',
            array($this, 'render_main_page'),
            'dashicons-chart-line',
            30
        );
        
        // Submenús
        add_submenu_page(
            'bcv-dolar-tracker',
            'Configuración',
            'Configuración',
            'manage_options',
            'bcv-dolar-tracker',
            array($this, 'render_main_page')
        );
        
        add_submenu_page(
            'bcv-dolar-tracker',
            'Datos de Precios',
            'Datos de Precios',
            'manage_options',
            'bcv-prices-data',
            array($this, 'render_prices_page')
        );
        
        add_submenu_page(
            'bcv-dolar-tracker',
            'Estadísticas',
            'Estadísticas',
            'manage_options',
            'bcv-statistics',
            array($this, 'render_statistics_page')
        );
        
        add_submenu_page(
            'bcv-dolar-tracker',
            'Registro de Actividad',
            'Registro de Actividad',
            'manage_options',
            'bcv-logs',
            array($this, 'render_logs_page')
        );
    }
    
    /**
     * Cargar assets de administración
     */
    public function enqueue_admin_assets($hook) {
        // Solo cargar en páginas del plugin
        if (strpos($hook, 'bcv-dolar-tracker') === false && strpos($hook, 'bcv-prices-data') === false && strpos($hook, 'bcv-statistics') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'bcv-admin-styles',
            BCV_DOLAR_TRACKER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            BCV_DOLAR_TRACKER_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'bcv-admin-script',
            BCV_DOLAR_TRACKER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            BCV_DOLAR_TRACKER_VERSION,
            true
        );
        
        // Localizar script para AJAX
        wp_localize_script('bcv-admin-script', 'bcv_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bcv_admin_nonce'),
            'strings' => array(
                'saving' => 'Guardando...',
                'saved' => 'Configuración guardada',
                'error' => 'Error al guardar',
                'testing' => 'Probando scraping...',
                'testing_success' => 'Scraping exitoso',
                'testing_error' => 'Error en scraping'
            )
        ));
    }
    
    /**
     * Renderizar página principal de administración
     */
    public function render_main_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Lo siento, no tienes permiso para acceder a esta página.');
        }
        
        // Procesar formulario de configuración del cron
        if (isset($_POST['cron_interval_preset']) || isset($_POST['cron_hours']) || isset($_POST['cron_minutes']) || isset($_POST['cron_seconds'])) {
            $this->process_cron_settings();
        }
        
        // Procesar formulario de configuración de depuración
        if (isset($_POST['save_debug_settings'])) {
            $this->process_debug_settings();
        }
        
        // Recrear tabla de logs si es necesario
        if (isset($_POST['recreate_logs_table'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_recreate_logs_table')) {
                $this->recreate_logs_table();
            }
        }
        
        // Mostrar mensajes de resultado
        if (isset($_GET['settings-updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>✅ Configuración de cron guardada exitosamente</p></div>';
        }
        
        if (isset($_GET['settings-error'])) {
            echo '<div class="notice notice-error is-dismissible"><p>❌ Error al guardar la configuración de cron</p></div>';
        }
        
        if (isset($_GET['debug-updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>✅ Configuración de debug guardada exitosamente</p></div>';
        }
        
        if (isset($_GET['validation-error'])) {
            $error_type = $_GET['validation-error'];
            if ($error_type === 'interval-too-short') {
                echo '<div class="notice notice-error is-dismissible"><p>⚠️ <strong>Error de Validación:</strong> El intervalo mínimo debe ser de 1 minuto (60 segundos).</p></div>';
            }
        }
        
        echo '<div class="wrap">';
        echo '<h1>BCV Dólar Tracker</h1>';
        echo '<p>Configuración del plugin para rastrear precios del dólar del BCV.</p>';
        
        // Mostrar estado de la base de datos
        $this->render_database_status();
        
        // Mostrar formulario de configuración del cron
        $this->render_cron_settings();
        
        // Mostrar botón de prueba de scraping
        $this->render_test_scraping();
        
        // Mostrar información del cron
        $this->render_cron_info();
        
        // Mostrar configuración de depuración
        $this->render_debug_settings();
        
        echo '</div>';
    }
    
    /**
     * Renderizar estado de la base de datos
     */
    private function render_database_status() {
        echo '<div class="bcv-panel">';
        echo '<h3>📊 Estado de la Base de Datos</h3>';
        
        if (!class_exists('BCV_Database')) {
            echo '<p style="color: #a94442;">❌ Clase BCV_Database no disponible</p>';
            echo '</div>';
            return;
        }
        
        $database = new BCV_Database();
        
        if ($database->table_exists()) {
            echo '<p style="color: #3c763d;">✅ Tabla wp_bcv_precio_dolar existe</p>';
            
            // Obtener estadísticas básicas
            $stats = $database->get_price_stats();
            if ($stats && is_array($stats)) {
                echo '<p>Total de registros: <strong>' . esc_html($stats['total_records'] ?? '0') . '</strong></p>';
                echo '<p>Último precio: <strong>1 USD = ' . esc_html(number_format($stats['last_price'] ?? 0, 4, ',', '.')) . ' Bs.</strong></p>';
                echo '<p>Fecha del último precio: <strong>' . esc_html($stats['last_date'] ?? 'N/A') . '</strong></p>';
            } else {
                echo '<p>No hay estadísticas disponibles</p>';
            }
        } else {
            echo '<p style="color: #a94442;">❌ Tabla wp_bcv_precio_dolar NO existe</p>';
            echo '<p>Esta es la razón por la que no se pueden guardar datos.</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Renderizar página de datos de precios
     */
    public function render_prices_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Lo siento, no tienes permiso para acceder a esta página.');
        }
        
        // Crear instancia de la tabla de precios
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'admin/class-bcv-prices-table.php';
        $prices_table = new BCV_Prices_Table();
        
        // Preparar la tabla
        $prices_table->prepare_items();
        
        echo '<div class="wrap">';
        echo '<h1>Datos de Precios del Dólar</h1>';
        
        // Formulario de filtros
        echo '<form method="post">';
        echo '<input type="hidden" name="page" value="bcv-prices-data" />';
        $prices_table->search_box('Buscar precios', 'bcv-search-prices');
        $prices_table->display();
        echo '</form>';
        
        echo '</div>';
    }
    
    /**
     * Renderizar página de estadísticas
     */
    public function render_statistics_page() {
        // Incluir la clase de estadísticas
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'admin/class-bcv-admin-stats.php';
        
        // Usar la nueva clase para renderizar
        BCV_Admin_Stats::render_statistics_page();
    }
    
    /**
     * Renderizar página de logs
     */
    public function render_logs_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Lo siento, no tienes permiso para acceder a esta página.');
        }
        
        // Procesar acciones de limpieza
        if (isset($_POST['clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'bcv_clear_logs')) {
            $result = BCV_Logger::clear_all_logs();
            if ($result !== false) {
                echo '<div class="notice notice-success is-dismissible"><p>✅ Logs eliminados exitosamente (' . $result . ' registros)</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>❌ Error al eliminar logs</p></div>';
            }
        }
        
        // Procesar acciones masivas
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['log_ids'])) {
            $this->handle_bulk_delete_logs($_POST['log_ids']);
        }
        
        // Crear instancia de la tabla de logs
        require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'admin/class-bcv-logs-table.php';
        $logs_table = new BCV_Logs_Table();
        
        // Preparar la tabla
        $logs_table->prepare_items();
        
        echo '<div class="wrap">';
        echo '<h1>📋 Registro de Actividad</h1>';
        echo '<p style="color: #666; font-size: 14px; margin-bottom: 30px;">Aquí puedes ver todo lo que ha estado haciendo el plugin: actualizaciones de precios, errores, configuraciones, etc.</p>';
        
        // Mostrar estado del modo de depuración
        $debug_mode = BCV_Logger::is_debug_mode_enabled();
        echo '<div class="bcv-panel" style="background: ' . ($debug_mode ? '#e8f5e8' : '#fff3cd') . '; border-left: 4px solid ' . ($debug_mode ? '#28a745' : '#ffc107') . ';">';
        echo '<h3 style="margin-top: 0;">🔧 Estado del Registro</h3>';
        echo '<p><strong>Registro de actividad:</strong> ';
        if ($debug_mode) {
            echo '<span style="color: #28a745; font-weight: bold;">✅ ACTIVO</span>';
            echo '<br><small style="color: #666;">El plugin está registrando todas las actividades importantes.</small>';
        } else {
            echo '<span style="color: #dc3545; font-weight: bold;">❌ INACTIVO</span>';
            echo '<br><small style="color: #666;">Para ver la actividad, activa el modo de depuración en la configuración.</small>';
        }
        echo '</p>';
        
        if (!$debug_mode) {
            echo '<p><a href="' . admin_url('admin.php?page=bcv-dolar-tracker') . '" class="button button-primary">⚙️ Ir a Configuración</a></p>';
        }
        echo '</div>';
        
        // Formulario de limpieza
        echo '<div class="bcv-panel">';
        echo '<h3>🧹 Gestión de Logs</h3>';
        echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
        wp_nonce_field('bcv_clear_logs');
        echo '<input type="submit" name="clear_logs" class="button button-secondary" value="Limpiar Todos los Logs" onclick="return confirm(\'¿Estás seguro de que quieres eliminar todos los logs? Esta acción no se puede deshacer.\');">';
        echo '</form>';
        
        echo '<a href="' . admin_url('admin.php?page=bcv-dolar-tracker') . '" class="button">Ir a Configuración</a>';
        echo '</div>';
        
        // Formulario de la tabla
        echo '<form method="post">';
        echo '<input type="hidden" name="page" value="bcv-logs" />';
        $logs_table->search_box('Buscar en logs', 'bcv-search-logs');
        $logs_table->display();
        echo '</form>';
        
        echo '</div>';
    }
    
    /**
     * Manejar eliminación masiva de logs
     * 
     * @param array $log_ids IDs de logs a eliminar
     */
    private function handle_bulk_delete_logs($log_ids) {
        if (empty($log_ids) || !is_array($log_ids)) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bcv_logs';
        
        $placeholders = implode(',', array_fill(0, count($log_ids), '%d'));
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE id IN ({$placeholders})",
            $log_ids
        ));
        
        if ($result !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>✅ ' . $result . ' logs eliminados exitosamente</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>❌ Error al eliminar logs seleccionados</p></div>';
        }
    }
    
    /**
     * Procesar configuración del cron
     */
    private function process_cron_settings() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'bcv_cron_settings')) {
            wp_die('Acceso denegado - Error de nonce en cron');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Obtener configuración actual para mantener el estado enabled
        $current_settings = get_option('bcv_cron_settings', array(
            'enabled' => true,
            'hours' => 1,
            'minutes' => 0,
            'seconds' => 0
        ));
        
        // Procesar intervalo predefinido si se seleccionó
        if (isset($_POST['cron_interval_preset']) && !empty($_POST['cron_interval_preset']) && $_POST['cron_interval_preset'] !== 'custom') {
            $total_seconds = intval($_POST['cron_interval_preset']);
            $hours = floor($total_seconds / 3600);
            $minutes = floor(($total_seconds % 3600) / 60);
            $seconds = $total_seconds % 60;
        } else {
            // Usar valores personalizados
            $hours = intval($_POST['cron_hours']);
            $minutes = intval($_POST['cron_minutes']);
            $seconds = intval($_POST['cron_seconds']);
        }
        
        // Validaciones robustas
        $hours = max(0, min(24, $hours));
        $minutes = max(0, min(59, $minutes));
        $seconds = max(0, min(59, $seconds));
        
        // Calcular total de segundos
        $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        
        // Validar mínimo 1 minuto (60 segundos)
        if ($total_seconds < 60) {
            // Redirigir con mensaje de error específico
            wp_redirect(add_query_arg('validation-error', 'interval-too-short', admin_url('admin.php?page=bcv-dolar-tracker')));
            exit;
        }
        
        // Guardar configuración
        $settings = array(
            'enabled' => !empty($current_settings['enabled']) ? $current_settings['enabled'] : true,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds
        );
        
        $saved = update_option('bcv_cron_settings', $settings);
        
        // Configurar cron
        $cron = new BCV_Cron();
        $result = $cron->setup_cron($settings);
        
        // Redirigir con mensaje de éxito o error
        if ($result) {
            wp_redirect(add_query_arg('settings-updated', 'true', admin_url('admin.php?page=bcv-dolar-tracker')));
        } else {
            wp_redirect(add_query_arg('settings-error', 'true', admin_url('admin.php?page=bcv-dolar-tracker')));
        }
        exit;
    }
    
    /**
     * Procesar configuración de depuración
     */
    private function process_debug_settings() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'bcv_debug_settings')) {
            wp_die('Acceso denegado - Error de nonce');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Procesar toggle de modo de depuración
        $debug_mode = isset($_POST['debug_mode']) ? true : false;
        
        if ($debug_mode) {
            $result = BCV_Logger::enable_debug_mode();
        } else {
            $result = BCV_Logger::disable_debug_mode();
        }
        
        // Redirigir con mensaje de éxito
        wp_redirect(add_query_arg('debug-updated', 'true', admin_url('admin.php?page=bcv-dolar-tracker')));
        exit;
    }
    
    /**
     * Guardar configuración del cron (AJAX)
     */
    public function save_cron_settings() {
        // Verificar nonce y permisos
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }
        
        // Procesar datos
        $settings = array(
            'enabled' => isset($_POST['enabled']) ? true : false,
            'hours' => intval($_POST['hours'] ?? 1),
            'minutes' => intval($_POST['minutes'] ?? 0),
            'seconds' => intval($_POST['seconds'] ?? 0)
        );
        
        // Mínimo 1 minuto
        if ($settings['hours'] == 0 && $settings['minutes'] == 0 && $settings['seconds'] < 60) {
            $settings['seconds'] = 60;
        }
        
        // Guardar configuración
        update_option('bcv_cron_settings', $settings);
        
        // Configurar cron
        $cron = new BCV_Cron();
        $result = $cron->setup_cron($settings);
        
        if ($result) {
            wp_send_json_success('Configuración guardada correctamente');
        } else {
            wp_send_json_error('Error al configurar el cron');
        }
    }
    
    /**
     * Probar scraping (AJAX)
     */
    public function test_scraping() {
        // Verificar nonce y permisos
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }
        
        try {
            // Crear instancia del scraper
            $scraper = new BCV_Scraper();
            
            // Limpiar caché antes del test
            $scraper->clear_cache();
            
            // Ejecutar scraping
            $rate = $scraper->scrape_bcv_rate();
            
            if ($rate !== false) {
                wp_send_json_success(array(
                    'message' => "Scraping exitoso. Tipo de cambio obtenido: 1 USD = {$rate} Bs.",
                    'price' => '1 USD = ' . number_format($rate, 4, ',', '.') . ' Bs.',
                    'status' => 'Exitoso',
                    'raw_price' => $rate
                ));
            } else {
                wp_send_json_error('No se pudo obtener el precio del BCV. Revisa el debug.log para más detalles.');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Error interno: ' . $e->getMessage());
        }
    }
    
    /**
     * Limpiar caché (AJAX)
     */
    public function clear_cache() {
        // Verificar nonce y permisos
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }
        
        try {
            // Crear instancia del scraper
            $scraper = new BCV_Scraper();
            
            // Limpiar caché
            $scraper->clear_cache();
            
            wp_send_json_success(array(
                'message' => 'Caché limpiado correctamente',
                'status' => 'Exitoso'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error interno: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener datos de precios (AJAX)
     */
    public function get_prices_data() {
        // Verificar nonce y permisos
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }
        
        // Obtener parámetros
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 20);
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        // Obtener datos
        $database = new BCV_Database();
        $data = $database->get_prices(array(
            'page' => $page,
            'per_page' => $per_page,
            'search' => $search
        ));
        
        wp_send_json_success($data);
    }
    
    /**
     * Toggle del cron (AJAX)
     */
    public function toggle_cron() {
        // Verificar nonce y permisos
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }
        
        if (!class_exists('BCV_Cron')) {
            wp_send_json_error('Clase BCV_Cron no disponible');
        }
        
        $cron = new BCV_Cron();
        $result = $cron->toggle_cron();
        
        if ($result) {
            $settings = $cron->get_cron_settings();
            $status = $settings['enabled'] ? 'activado' : 'desactivado';
            
            wp_send_json_success(array(
                'message' => 'Cron ' . $status . ' exitosamente',
                'enabled' => $settings['enabled'],
                'status_text' => $settings['enabled'] ? 'Activo' : 'Inactivo'
            ));
        } else {
            wp_send_json_error('Error al cambiar el estado del cron');
        }
    }
    
    /**
     * Renderizar formulario de configuración del cron
     */
    private function render_cron_settings() {
        // Obtener configuración actual
        $cron_settings = get_option('bcv_cron_settings', array(
            'hours' => 1,
            'minutes' => 0,
            'seconds' => 0,
            'enabled' => true
        ));
        
        echo '<div class="bcv-panel">';
        echo '<h3>⚙️ Configuración del Cron</h3>';
        
        // Estado del cron
        $cron_status = wp_next_scheduled('bcv_scrape_dollar_rate') ? 'Activo' : 'Inactivo';
        echo '<div class="bcv-status-info">';
        echo '<span class="bcv-status-label">Estado:</span>';
        echo '<span class="bcv-status-value bcv-status-' . (wp_next_scheduled('bcv_scrape_dollar_rate') ? 'active' : 'inactive') . '">' . $cron_status . '</span>';
        echo '</div>';
        
        // Formulario de configuración
        echo '<form method="post" class="bcv-form" id="cron-settings-form">';
        wp_nonce_field('bcv_cron_settings');
        
        echo '<div class="bcv-input-group">';
        echo '<label for="cron_interval_preset">Intervalo Predefinido</label>';
        echo '<select id="cron_interval_preset" name="cron_interval_preset" class="bcv-select">';
        echo '<option value="">Seleccionar...</option>';
        
        // Calcular el intervalo actual en segundos
        $current_seconds = ($cron_settings['hours'] * 3600) + ($cron_settings['minutes'] * 60) + $cron_settings['seconds'];
        
        // Opciones predefinidas
        $presets = array(
            '300' => 'Cada 5 minutos',
            '900' => 'Cada 15 minutos', 
            '1800' => 'Cada 30 minutos',
            '3600' => 'Cada hora',
            '7200' => 'Cada 2 horas',
            '21600' => 'Cada 6 horas',
            '43200' => 'Cada 12 horas',
            '86400' => 'Diariamente',
            'custom' => 'Personalizado'
        );
        
        foreach ($presets as $value => $label) {
            $selected = '';
            if ($value === 'custom') {
                // Si no coincide con ningún preset, es personalizado
                if (!in_array($current_seconds, array_keys(array_slice($presets, 0, -1)))) {
                    $selected = 'selected';
                }
            } else {
                // Si coincide con un preset
                if ($current_seconds == intval($value)) {
                    $selected = 'selected';
                }
            }
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        echo '<div class="bcv-time-inputs" id="time-inputs" style="display: none;">';
        echo '<div class="bcv-input-row">';
        echo '<div class="bcv-input-group">';
        echo '<label for="cron_hours">Horas</label>';
        echo '<input type="number" id="cron_hours" name="cron_hours" value="' . esc_attr($cron_settings['hours']) . '" min="0" max="24" class="bcv-input" />';
        echo '</div>';
        echo '<div class="bcv-input-group">';
        echo '<label for="cron_minutes">Minutos</label>';
        echo '<input type="number" id="cron_minutes" name="cron_minutes" value="' . esc_attr($cron_settings['minutes']) . '" min="0" max="59" class="bcv-input" />';
        echo '</div>';
        echo '<div class="bcv-input-group">';
        echo '<label for="cron_seconds">Segundos</label>';
        echo '<input type="number" id="cron_seconds" name="cron_seconds" value="' . esc_attr($cron_settings['seconds']) . '" min="0" max="59" class="bcv-input" />';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="bcv-form-buttons">';
        echo '<button type="submit" name="submit" class="bcv-save-btn">Guardar Configuración</button>';
        echo '<button type="button" id="toggle-cron" class="bcv-toggle-btn">' . ($cron_settings['enabled'] ? 'Desactivar' : 'Activar') . ' Cron</button>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Renderizar botón de prueba de scraping
     */
    private function render_test_scraping() {
        echo '<div class="bcv-panel">';
        echo '<h3>🧪 Prueba de Scraping Detallada</h3>';
        echo '<p>Haz clic en el botón para probar la conexión con el BCV y obtener el precio actual del dólar en bolívares.</p>';
        
        echo '<div class="bcv-test-section">';
        echo '<button type="button" id="test-scraping" class="bcv-test-btn">🔍 Probar Scraping Detallado</button>';
        echo '<button type="button" id="clear-cache" class="bcv-test-btn" style="background: #ffc107; color: #000; margin-left: 10px;">🗑️ Limpiar Caché</button>';
        echo '<div id="test-result" class="bcv-result" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; display: none;"></div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Renderizar información del cron
     */
    private function render_cron_info() {
        echo '<div class="bcv-panel">';
        echo '<h3>⏰ Estado del Cron</h3>';
        
        if (!class_exists('BCV_Cron')) {
            echo '<p class="bcv-error">❌ Clase BCV_Cron no disponible</p>';
            echo '</div>';
            return;
        }
        
        $cron = new BCV_Cron();
        $cron_info = $cron->get_cron_info();
        
        if ($cron_info) {
            echo '<div class="bcv-info-list">';
            
            // Estado
            echo '<div class="bcv-info-item">';
            echo '<span class="bcv-info-label">Estado:</span>';
            if ($cron_info['is_scheduled']) {
                echo '<span class="bcv-status-active">✅ Programado</span>';
            } else {
                echo '<span class="bcv-status-inactive">❌ No programado</span>';
            }
            echo '</div>';
            
            if ($cron_info['is_scheduled']) {
                // Próxima ejecución
                echo '<div class="bcv-info-item">';
                echo '<span class="bcv-info-label">Próxima ejecución:</span>';
                echo '<span class="bcv-info-value">' . esc_html($cron_info['next_run']) . '</span>';
                echo '</div>';
                
                // Intervalo
                echo '<div class="bcv-info-item">';
                echo '<span class="bcv-info-label">Intervalo:</span>';
                echo '<span class="bcv-info-value">' . esc_html($cron_info['interval_formatted']) . '</span>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<p class="bcv-error">❌ No se pudo obtener información del cron</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Renderizar configuración de depuración
     */
    private function render_debug_settings() {
        $debug_mode = BCV_Logger::is_debug_mode_enabled();
        
        echo '<div class="bcv-panel">';
        echo '<h3>🐛 Modo de Depuración</h3>';
        
        echo '<form method="post" class="bcv-form">';
        wp_nonce_field('bcv_debug_settings');
        
        echo '<div class="bcv-checkbox-group">';
        echo '<label class="bcv-checkbox-label">';
        echo '<input type="checkbox" name="debug_mode" value="1" ' . checked($debug_mode, true, false) . ' />';
        echo '<span class="bcv-checkbox-text">Habilitar registro de eventos internos</span>';
        echo '</label>';
        echo '<p class="bcv-help-text">Cuando está habilitado, el plugin registrará todas las operaciones importantes en la base de datos.</p>';
        echo '</div>';
        
        echo '<div class="bcv-form-buttons">';
        echo '<button type="submit" name="save_debug_settings" class="bcv-save-btn">Guardar</button>';
        
        if ($debug_mode) {
            echo '<a href="' . admin_url('admin.php?page=bcv-logs') . '" class="bcv-link-btn">Ver Logs</a>';
        }
        echo '</div>';
        
        echo '</form>';
        
        // Botón para recrear tabla de logs (formulario separado)
        echo '<div class="bcv-debug-tools">';
        echo '<h4>🔧 Herramientas de Debug</h4>';
        echo '<form method="post" style="display: inline; margin-right: 10px;">';
        wp_nonce_field('bcv_recreate_logs_table');
        echo '<button type="submit" name="recreate_logs_table" class="bcv-action-btn" onclick="return confirm(\'¿Estás seguro? Esto eliminará todos los logs existentes.\');">Recrear Tabla de Logs</button>';
        echo '</form>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Recrear tabla de logs
     */
    private function recreate_logs_table() {
        global $wpdb;
        
        // Eliminar tabla existente
        $logs_table_name = $wpdb->prefix . 'bcv_logs';
        $wpdb->query("DROP TABLE IF EXISTS {$logs_table_name}");
        
        // Crear nueva tabla
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$logs_table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            log_level varchar(20) NOT NULL DEFAULT 'INFO',
            context varchar(100) NOT NULL DEFAULT '',
            message text NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_log_level (log_level),
            KEY idx_context (context),
            KEY idx_user_id (user_id),
            KEY idx_created_at (created_at),
            KEY idx_created_at_level (created_at, log_level)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        if (empty($wpdb->last_error)) {
            error_log('BCV Dólar Tracker: Tabla de logs recreada correctamente');
            
            // Insertar un log de prueba para verificar que funciona
            $test_log = $wpdb->insert(
                $logs_table_name,
                array(
                    'log_level' => 'INFO',
                    'context' => 'Sistema',
                    'message' => 'Tabla de logs recreada exitosamente',
                    'user_id' => get_current_user_id(),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ),
                array('%s', '%s', '%s', '%d', '%s')
            );
            
            if ($test_log !== false) {
                error_log('BCV Dólar Tracker: Log de prueba insertado correctamente');
            } else {
                error_log('BCV Dólar Tracker: Error al insertar log de prueba: ' . $wpdb->last_error);
            }
        } else {
            error_log('BCV Dólar Tracker: Error al recrear tabla de logs: ' . $wpdb->last_error);
        }
    }
}
