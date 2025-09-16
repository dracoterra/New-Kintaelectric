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

class BCV_Admin {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Hooks de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_bcv_save_cron_settings', array($this, 'save_cron_settings'));
        add_action('wp_ajax_bcv_test_scraping', array($this, 'test_scraping'));
        add_action('wp_ajax_bcv_get_prices_data', array($this, 'get_prices_data'));
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
        // Verificar si se solicitó crear tabla de emergencia
        if (isset($_POST['bcv_create_table_emergency'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_create_table_emergency')) {
                $this->handle_emergency_table_creation();
            }
        }
        
        // Forzar programación del cron
        if (isset($_POST['bcv_force_cron_schedule'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_force_cron_schedule')) {
                $this->handle_force_cron_schedule();
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
        
        // Botón de emergencia para crear tabla
        echo '<div class="bcv-panel">';
        echo '<h3>🆘 Acciones de Emergencia</h3>';
        echo '<p>Si la tabla de base de datos no existe, usa este botón para crearla:</p>';
        echo '<form method="post">';
        wp_nonce_field('bcv_create_table_emergency');
        echo '<input type="submit" name="bcv_create_table_emergency" class="button button-primary" value="Crear Tabla de Base de Datos">';
        echo '</form>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Manejar creación de tabla de emergencia
     */
    private function handle_emergency_table_creation() {
        if (!class_exists('BCV_Database')) {
            echo '<div class="notice notice-error"><p>❌ Error: Clase BCV_Database no disponible</p></div>';
            return;
        }
        
        $database = new BCV_Database();
        
        if ($database->table_exists()) {
            echo '<div class="notice notice-warning"><p>⚠️ La tabla ya existe</p></div>';
            return;
        }
        
        $result = $database->create_price_table();
        
        if ($result) {
            echo '<div class="notice notice-success"><p>✅ Tabla creada exitosamente</p></div>';
            echo '<script>location.reload();</script>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Error al crear la tabla</p></div>';
        }
    }
    
    /**
     * Manejar forzar programación del cron
     */
    private function handle_force_cron_schedule() {
        if (!class_exists('BCV_Cron')) {
            echo '<div class="notice notice-error"><p>❌ Error: Clase BCV_Cron no disponible</p></div>';
            return;
        }
        
        $cron = new BCV_Cron();
        $result = $cron->force_schedule_cron();
        
        if ($result) {
            echo '<div class="notice notice-success"><p>✅ Cron forzado exitosamente</p></div>';
            echo '<script>location.reload();</script>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Error al forzar el cron</p></div>';
        }
    }
    
    /**
     * Manejar reset de estadísticas del cron
     */
    private function handle_reset_cron_stats() {
        if (!class_exists('BCV_Cron')) {
            echo '<div class="notice notice-error"><p>❌ Error: Clase BCV_Cron no disponible</p></div>';
            return;
        }
        
        $cron = new BCV_Cron();
        $result = $cron->reset_cron_stats();
        
        if ($result) {
            echo '<div class="notice notice-success"><p>✅ Estadísticas del cron reseteadas exitosamente</p></div>';
            echo '<script>location.reload();</script>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Error al resetear las estadísticas del cron</p></div>';
        }
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
                echo '<p>Último precio: <strong>$' . esc_html($stats['last_price'] ?? 'N/A') . '</strong></p>';
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
            wp_die('Acceso denegado');
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
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        // Procesar reset de estadísticas del cron
        if (isset($_POST['bcv_reset_cron_stats'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_reset_cron_stats')) {
                $this->handle_reset_cron_stats();
            }
        }
        
        // Obtener estadísticas
        $database = new BCV_Database();
        $price_stats = $database->get_price_stats();
        
        $cron = new BCV_Cron();
        $cron_stats = $cron->get_cron_stats();
        
        $scraper = new BCV_Scraper();
        $scraping_info = $scraper->get_scraping_info();
        
        echo '<div class="wrap">';
        echo '<h1>Estadísticas del Plugin</h1>';
        
        echo '<div class="bcv-admin-container">';
        
        // Estadísticas de precios
        echo '<div class="bcv-panel">';
        echo '<h2>Estadísticas de Precios</h2>';
        echo '<table class="form-table">';
        echo '<tr><th scope="row">Total de registros</th><td>' . esc_html($price_stats['total_records']) . '</td></tr>';
        echo '<tr><th scope="row">Precio mínimo</th><td>' . esc_html($price_stats['min_price']) . '</td></tr>';
        echo '<tr><th scope="row">Precio máximo</th><td>' . esc_html($price_stats['max_price']) . '</td></tr>';
        echo '<tr><th scope="row">Precio promedio</th><td>' . esc_html(round($price_stats['avg_price'], 4)) . '</td></tr>';
        echo '<tr><th scope="row">Primer registro</th><td>' . esc_html($price_stats['first_date']) . '</td></tr>';
        echo '<tr><th scope="row">Último registro</th><td>' . esc_html($price_stats['last_date']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        // Estadísticas del cron
        echo '<div class="bcv-panel">';
        echo '<h2>Estadísticas del Cron</h2>';
        echo '<table class="form-table">';
        echo '<tr><th scope="row">Total de ejecuciones</th><td>' . esc_html($cron_stats['total_executions']) . '</td></tr>';
        echo '<tr><th scope="row">Ejecuciones exitosas</th><td>' . esc_html($cron_stats['successful_executions']) . '</td></tr>';
        echo '<tr><th scope="row">Ejecuciones fallidas</th><td>' . esc_html($cron_stats['failed_executions']) . '</td></tr>';
        echo '<tr><th scope="row">Última ejecución</th><td>' . esc_html($cron_stats['last_execution']) . '</td></tr>';
        echo '<tr><th scope="row">Próxima ejecución</th><td>' . esc_html($cron_stats['next_execution']) . '</td></tr>';
        echo '</table>';
        
        // Botón para resetear estadísticas
        echo '<form method="post" style="margin-top: 15px;">';
        wp_nonce_field('bcv_reset_cron_stats');
        echo '<input type="submit" name="bcv_reset_cron_stats" class="button button-secondary" value="🔄 Resetear Estadísticas del Cron">';
        echo '</form>';
        
        // Estadísticas del scraping
        echo '<div class="bcv-panel">';
        echo '<h2>Estadísticas del Scraping</h2>';
        echo '<table class="form-table">';
        echo '<tr><th scope="row">Precio en caché</th><td>' . esc_html($scraping_info['cached_price'] ?: 'No disponible') . '</td></tr>';
        echo '<tr><th scope="row">Caché válido</th><td>' . ($scraping_info['cache_valid'] ? '✅ Sí' : '❌ No') . '</td></tr>';
        echo '<tr><th scope="row">Expiración del caché</th><td>' . esc_html($scraping_info['cache_expiry']) . '</td></tr>';
        echo '<tr><th scope="row">Último scraping</th><td>' . esc_html($scraping_info['last_scraping']) . '</td></tr>';
        echo '<tr><th scope="row">Intentos de scraping</th><td>' . esc_html($scraping_info['scraping_attempts']) . '</td></tr>';
        echo '<tr><th scope="row">Scrapings exitosos</th><td>' . esc_html($scraping_info['successful_scrapings']) . '</td></tr>';
        echo '<tr><th scope="row">Scrapings fallidos</th><td>' . esc_html($scraping_info['failed_scrapings']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '</div>'; // .bcv-admin-container
        
        echo '</div>'; // .wrap
    }
    
    /**
     * Procesar configuración del cron
     */
    private function process_cron_settings() {
        // Validar y sanitizar datos
        $enabled = isset($_POST['cron_enabled']) ? true : false;
        $hours = intval($_POST['cron_hours']);
        $minutes = intval($_POST['cron_minutes']);
        $seconds = intval($_POST['cron_seconds']);
        
        // Validaciones
        if ($hours < 0 || $hours > 24) {
            $hours = 1;
        }
        if ($minutes < 0 || $minutes > 59) {
            $minutes = 0;
        }
        if ($seconds < 0 || $seconds > 59) {
            $seconds = 0;
        }
        
        // Mínimo 1 minuto
        if ($hours == 0 && $minutes == 0 && $seconds < 60) {
            $seconds = 60;
        }
        
        // Guardar configuración
        $settings = array(
            'enabled' => $enabled,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds
        );
        
        update_option('bcv_cron_settings', $settings);
        
        // Configurar cron
        $cron = new BCV_Cron();
        $cron->setup_cron($settings);
        
        // Redirigir con mensaje de éxito
        wp_redirect(add_query_arg('settings-updated', 'true', admin_url('admin.php?page=bcv-dolar-tracker')));
        exit;
    }
    
    /**
     * Guardar configuración del cron (AJAX)
     */
    public function save_cron_settings() {
        // Verificar nonce y permisos usando la clase de seguridad
        BCV_Security::verify_ajax_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce');
        BCV_Security::check_capability('manage_options');
        
        // Log del evento
        BCV_Security::log_security_event('Cron settings save attempt', 'User: ' . get_current_user_id());
        
        // Definir reglas de validación
        $validation_rules = array(
            'enabled' => array('type' => 'checkbox'),
            'hours' => array('type' => 'number', 'min' => 0, 'max' => 24, 'required' => true, 'label' => 'Horas'),
            'minutes' => array('type' => 'number', 'min' => 0, 'max' => 59, 'required' => true, 'label' => 'Minutos'),
            'seconds' => array('type' => 'number', 'min' => 0, 'max' => 59, 'required' => true, 'label' => 'Segundos')
        );
        
        // Validar y sanitizar datos
        $validation_result = BCV_Security::validate_form_data($_POST, $validation_rules);
        
        if (!empty($validation_result['errors'])) {
            wp_send_json_error('Datos inválidos: ' . implode(', ', $validation_result['errors']));
        }
        
        // Procesar datos validados
        $settings = array(
            'enabled' => isset($_POST['enabled']) ? true : false,
            'hours' => $validation_result['data']['hours'],
            'minutes' => $validation_result['data']['minutes'],
            'seconds' => $validation_result['data']['seconds']
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
        // Verificar nonce y permisos usando la clase de seguridad
        BCV_Security::verify_ajax_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce');
        BCV_Security::check_capability('manage_options');
        
        // Log del evento
        BCV_Security::log_security_event('Manual scraping test attempt', 'User: ' . get_current_user_id());
        
        // Ejecutar scraping manual
        $cron = new BCV_Cron();
        $result = $cron->execute_manual_scraping();
        
        // Log del resultado
        if ($result['success']) {
            BCV_Security::log_security_event('Manual scraping test successful', 'Price: ' . ($result['price'] ?? 'N/A'));
        } else {
            BCV_Security::log_security_event('Manual scraping test failed', 'Error: ' . ($result['message'] ?? 'Unknown error'));
        }
        
        wp_send_json($result);
    }
    
    /**
     * Obtener datos de precios (AJAX)
     */
    public function get_prices_data() {
        // Verificar nonce y permisos usando la clase de seguridad
        BCV_Security::verify_ajax_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce');
        BCV_Security::check_capability('manage_options');
        
        // Log del evento
        BCV_Security::log_security_event('Prices data request', 'User: ' . get_current_user_id());
        
        // Definir reglas de validación para parámetros
        $validation_rules = array(
            'page' => array('type' => 'number', 'min' => 1, 'max' => 1000),
            'per_page' => array('type' => 'number', 'min' => 1, 'max' => 100),
            'search' => array('type' => 'text')
        );
        
        // Validar y sanitizar parámetros
        $validation_result = BCV_Security::validate_form_data($_POST, $validation_rules);
        
        // Obtener parámetros validados con valores por defecto
        $page = $validation_result['data']['page'] ?: 1;
        $per_page = $validation_result['data']['per_page'] ?: 20;
        $search = $validation_result['data']['search'] ?: '';
        
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
        echo '<form method="post" action="" id="cron-settings-form">';
        wp_nonce_field('bcv_cron_settings');
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="cron_enabled">Habilitar Cron</label></th>';
        echo '<td>';
        echo '<input type="checkbox" id="cron_enabled" name="cron_enabled" value="1" ' . checked($cron_settings['enabled'], true, false) . ' />';
        echo '<span class="description">Habilitar la ejecución automática del scraping</span>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="cron_hours">Horas</label></th>';
        echo '<td>';
        echo '<input type="number" id="cron_hours" name="cron_hours" value="' . esc_attr($cron_settings['hours']) . '" min="0" max="24" class="small-text" />';
        echo '<span class="description">Horas entre ejecuciones (0-24)</span>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="cron_minutes">Minutos</label></th>';
        echo '<td>';
        echo '<input type="number" id="cron_minutes" name="cron_minutes" value="' . esc_attr($cron_settings['minutes']) . '" min="0" max="59" class="small-text" />';
        echo '<span class="description">Minutos entre ejecuciones (0-59)</span>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="cron_seconds">Segundos</label></th>';
        echo '<td>';
        echo '<input type="number" id="cron_seconds" name="cron_seconds" value="' . esc_attr($cron_settings['seconds']) . '" min="0" max="59" class="small-text" />';
        echo '<span class="description">Segundos entre ejecuciones (0-59)</span>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        
        echo '<p class="submit">';
        echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Configuración">';
        echo '</p>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Renderizar botón de prueba de scraping
     */
    private function render_test_scraping() {
        echo '<div class="bcv-panel">';
        echo '<h3>🧪 Prueba de Scraping</h3>';
        echo '<p>';
        echo '<button type="button" id="test-scraping" class="button button-secondary">Probar Scraping Manual</button>';
        echo '<span id="test-result" style="margin-left: 10px;"></span>';
        echo '</p>';
        echo '</div>';
    }

    /**
     * Renderizar información del cron
     */
    private function render_cron_info() {
        echo '<div class="bcv-panel">';
        echo '<h3>⏰ Estado del Cron</h3>';
        
        if (!class_exists('BCV_Cron')) {
            echo '<p style="color: #a94442;">❌ Clase BCV_Cron no disponible</p>';
            echo '</div>';
            return;
        }
        
        $cron = new BCV_Cron();
        $cron_info = $cron->get_cron_info();
        
        if ($cron_info) {
            echo '<table class="form-table">';
            echo '<tr>';
            echo '<th scope="row">Estado</th>';
            echo '<td>' . ($cron_info['is_scheduled'] ? '✅ Programado' : '❌ No programado') . '</td>';
            echo '</tr>';
            
            if ($cron_info['is_scheduled']) {
                echo '<tr>';
                echo '<th scope="row">Próxima ejecución</th>';
                echo '<td>' . esc_html($cron_info['next_run']) . '</td>';
                echo '</tr>';
                
                echo '<tr>';
                echo '<th scope="row">Intervalo</th>';
                echo '<td>' . esc_html($cron_info['interval_formatted']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Botón para forzar programación del cron
            if (!$cron_info['is_scheduled']) {
                echo '<form method="post" style="margin-top: 15px;">';
                wp_nonce_field('bcv_force_cron_schedule');
                echo '<input type="submit" name="bcv_force_cron_schedule" class="button button-secondary" value="🔄 Forzar Programación del Cron">';
                echo '</form>';
            }
        } else {
            echo '<p style="color: #a94442;">❌ No se pudo obtener información del cron</p>';
        }
        
        echo '</div>';
    }
}
