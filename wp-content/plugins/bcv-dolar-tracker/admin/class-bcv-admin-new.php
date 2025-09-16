<?php
/**
 * Clase principal de administración del plugin BCV Dólar Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCV_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_bcv_test_scraping', array($this, 'test_scraping'));
        add_action('wp_ajax_bcv_clear_cache', array($this, 'clear_cache'));
    }
    
    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            'BCV Dólar Tracker',
            'BCV Dólar',
            'manage_options',
            'bcv-dolar-tracker',
            array($this, 'render_main_page'),
            'dashicons-chart-line',
            30
        );
        
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
     * Cargar scripts y estilos del admin
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'bcv-dolar') === false) {
            return;
        }
        
        wp_enqueue_style('bcv-admin', BCV_DOLAR_TRACKER_PLUGIN_URL . 'assets/css/admin.css', array(), BCV_DOLAR_TRACKER_VERSION);
        wp_enqueue_script('bcv-admin', BCV_DOLAR_TRACKER_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), BCV_DOLAR_TRACKER_VERSION, true);
        
        wp_localize_script('bcv-admin', 'bcv_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bcv_admin_nonce')
        ));
    }
    
    /**
     * Renderizar página principal
     */
    public function render_main_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        // Procesar formularios
        if (isset($_POST['bcv_cron_settings'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_cron_settings')) {
                $this->process_cron_settings();
            }
        }
        
        if (isset($_POST['bcv_debug_settings'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_debug_settings')) {
                $this->process_debug_settings();
            }
        }
        
        // Obtener configuración actual
        $cron_settings = get_option('bcv_cron_settings', array());
        $debug_mode = get_option('bcv_debug_mode', false);
        
        echo '<div class="wrap">';
        echo '<h1>⚙️ Configuración del Plugin</h1>';
        echo '<p>Configura las opciones del plugin BCV Dólar Tracker.</p>';
        
        // Formulario de configuración del cron
        echo '<div class="bcv-admin-container">';
        echo '<div class="bcv-panel">';
        echo '<h2>⏰ Configuración de Actualización Automática</h2>';
        echo '<form method="post" class="bcv-form">';
        wp_nonce_field('bcv_cron_settings');
        echo '<input type="hidden" name="bcv_cron_settings" value="1">';
        
        // Estado del cron
        $cron_enabled = isset($cron_settings['enabled']) ? $cron_settings['enabled'] : true;
        echo '<div class="bcv-form-group">';
        echo '<label class="bcv-switch">';
        echo '<input type="checkbox" name="cron_enabled" value="1" ' . checked($cron_enabled, true, false) . '>';
        echo '<span class="bcv-slider"></span>';
        echo '</label>';
        echo '<span class="bcv-switch-label">Activar actualización automática</span>';
        echo '</div>';
        
        // Intervalo del cron
        $current_interval = isset($cron_settings['interval']) ? $cron_settings['interval'] : 3600;
        echo '<div class="bcv-form-group">';
        echo '<label for="cron_interval_preset">Intervalo de actualización:</label>';
        echo '<select name="cron_interval_preset" id="cron_interval_preset" class="bcv-select">';
        
        $presets = array(
            900 => 'Cada 15 minutos',
            1800 => 'Cada 30 minutos',
            3600 => 'Cada hora',
            7200 => 'Cada 2 horas',
            14400 => 'Cada 4 horas',
            86400 => 'Diariamente'
        );
        
        foreach ($presets as $seconds => $label) {
            $selected = ($current_interval == $seconds) ? 'selected' : '';
            echo "<option value=\"{$seconds}\" {$selected}>{$label}</option>";
        }
        
        echo '<option value="custom"' . ($current_interval != 900 && $current_interval != 1800 && $current_interval != 3600 && $current_interval != 7200 && $current_interval != 14400 && $current_interval != 86400 ? ' selected' : '') . '>Personalizado</option>';
        echo '</select>';
        echo '</div>';
        
        // Intervalo personalizado
        echo '<div class="bcv-form-group" id="custom-interval-group" style="display: none;">';
        echo '<label for="cron_hours">Horas:</label>';
        echo '<input type="number" name="cron_hours" id="cron_hours" min="0" max="23" value="' . esc_attr(floor($current_interval / 3600)) . '" class="bcv-input">';
        echo '<label for="cron_minutes">Minutos:</label>';
        echo '<input type="number" name="cron_minutes" id="cron_minutes" min="0" max="59" value="' . esc_attr(($current_interval % 3600) / 60) . '" class="bcv-input">';
        echo '</div>';
        
        echo '<div class="bcv-form-actions">';
        echo '<button type="submit" class="bcv-btn bcv-btn-primary">💾 Guardar Configuración</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        
        // Formulario de configuración de debug
        echo '<div class="bcv-panel">';
        echo '<h2>🐛 Configuración de Depuración</h2>';
        echo '<form method="post" class="bcv-form">';
        wp_nonce_field('bcv_debug_settings');
        echo '<input type="hidden" name="bcv_debug_settings" value="1">';
        
        echo '<div class="bcv-form-group">';
        echo '<label class="bcv-switch">';
        echo '<input type="checkbox" name="debug_mode" value="1" ' . checked($debug_mode, true, false) . '>';
        echo '<span class="bcv-slider"></span>';
        echo '</label>';
        echo '<span class="bcv-switch-label">Activar modo de depuración</span>';
        echo '</div>';
        
        echo '<div class="bcv-form-actions">';
        echo '<button type="submit" class="bcv-btn bcv-btn-primary">💾 Guardar Configuración</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        
        // Sección de pruebas
        echo '<div class="bcv-panel">';
        echo '<h2>🧪 Pruebas del Sistema</h2>';
        echo '<p>Prueba la conexión con el BCV y el funcionamiento del plugin.</p>';
        
        echo '<div class="bcv-test-actions">';
        echo '<button id="test-scraping" class="bcv-btn bcv-btn-secondary">🔍 Probar Scraping Detallado</button>';
        echo '<button id="clear-cache" class="bcv-btn bcv-btn-warning">🗑️ Limpiar Caché</button>';
        echo '</div>';
        
        echo '<div id="test-result" class="bcv-test-result" style="display: none;"></div>';
        echo '</div>';
        
        echo '</div>'; // Fin admin-container
        echo '</div>'; // Fin wrap
    }
    
    /**
     * Renderizar página de precios
     */
    public function render_prices_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        // Crear tabla de precios
        $prices_table = new BCV_Prices_Table();
        $prices_table->prepare_items();
        
        echo '<div class="wrap">';
        echo '<h1>💰 Datos de Precios del Dólar</h1>';
        echo '<p>Historial completo de los precios obtenidos del Banco Central de Venezuela.</p>';
        
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
            wp_die('Acceso denegado');
        }
        
        // Procesar acciones de limpieza
        if (isset($_POST['clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'bcv_clear_logs')) {
            $result = BCV_Logger::clear_all_logs();
            if ($result) {
                echo '<div class="notice notice-success"><p>Logs eliminados correctamente.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Error al eliminar los logs.</p></div>';
            }
        }
        
        // Crear tabla de logs
        $logs_table = new BCV_Logs_Table();
        $logs_table->prepare_items();
        
        echo '<div class="wrap">';
        echo '<h1>📋 Registro de Actividad</h1>';
        echo '<p>Registro detallado de todas las actividades del plugin.</p>';
        
        // Botón de limpieza
        echo '<form method="post" style="display: inline-block; margin-bottom: 20px;">';
        wp_nonce_field('bcv_clear_logs');
        echo '<button type="submit" name="clear_logs" class="button button-secondary" onclick="return confirm(\'¿Estás seguro? Esto eliminará todos los logs.\');">🗑️ Limpiar Todos los Logs</button>';
        echo '</form>';
        
        echo '<form method="post">';
        echo '<input type="hidden" name="page" value="bcv-logs" />';
        $logs_table->search_box('Buscar logs', 'bcv-search-logs');
        $logs_table->display();
        echo '</form>';
        
        echo '</div>';
    }
    
    /**
     * Procesar configuración del cron
     */
    private function process_cron_settings() {
        $enabled = isset($_POST['cron_enabled']);
        $interval_preset = intval($_POST['cron_interval_preset']);
        
        if ($interval_preset === 0) { // Personalizado
            $hours = intval($_POST['cron_hours']);
            $minutes = intval($_POST['cron_minutes']);
            $interval = ($hours * 3600) + ($minutes * 60);
        } else {
            $interval = $interval_preset;
        }
        
        $settings = array(
            'enabled' => $enabled,
            'interval' => $interval
        );
        
        update_option('bcv_cron_settings', $settings);
        
        // Actualizar cron
        $cron = new BCV_Cron();
        if ($enabled) {
            $cron->schedule_cron($interval);
        } else {
            $cron->clear_cron();
        }
        
        echo '<div class="notice notice-success"><p>Configuración del cron guardada correctamente.</p></div>';
    }
    
    /**
     * Procesar configuración de debug
     */
    private function process_debug_settings() {
        $debug_mode = isset($_POST['debug_mode']);
        update_option('bcv_debug_mode', $debug_mode);
        
        if ($debug_mode) {
            BCV_Logger::enable_debug_mode();
        } else {
            BCV_Logger::disable_debug_mode();
        }
        
        echo '<div class="notice notice-success"><p>Configuración de debug guardada correctamente.</p></div>';
    }
    
    /**
     * AJAX: Probar scraping
     */
    public function test_scraping() {
        // Verificar nonce y permisos
        if (!wp_verify_nonce($_POST['nonce'], 'bcv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }
        
        try {
            $scraper = new BCV_Scraper();
            $scraper->clear_cache();
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
     * AJAX: Limpiar caché
     */
    public function clear_cache() {
        // Verificar nonce y permisos
        if (!wp_verify_nonce($_POST['nonce'], 'bcv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }
        
        try {
            $scraper = new BCV_Scraper();
            $scraper->clear_cache();
            
            wp_send_json_success(array(
                'message' => 'Caché limpiado correctamente',
                'status' => 'Exitoso'
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error interno: ' . $e->getMessage());
        }
    }
}
