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
        error_log('BCV Dólar Tracker: Renderizando página principal');
        error_log('BCV Dólar Tracker: POST data: ' . print_r($_POST, true));
        
        // Procesar formulario de configuración del cron
        if (isset($_POST['cron_interval_preset']) || isset($_POST['cron_hours']) || isset($_POST['cron_minutes']) || isset($_POST['cron_seconds'])) {
            error_log('BCV Dólar Tracker: Procesando formulario de cron - Campos de cron detectados');
            $this->process_cron_settings();
        }
        
        // Procesar formulario de configuración de depuración
        if (isset($_POST['save_debug_settings'])) {
            error_log('BCV Dólar Tracker: Procesando formulario de debug');
            $this->process_debug_settings();
        }
        
        // Recrear tabla de logs si es necesario
        if (isset($_POST['recreate_logs_table'])) {
            error_log('BCV Dólar Tracker: Procesando recreación de tabla de logs');
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_recreate_logs_table')) {
                $this->recreate_logs_table();
            } else {
                error_log('BCV Dólar Tracker: Error de nonce en recreación de tabla');
            }
        }
        
        // Prueba simple de formulario
        if (isset($_POST['test_form'])) {
            error_log('BCV Dólar Tracker: Formulario de prueba recibido');
            echo '<div class="notice notice-success is-dismissible"><p>✅ Formulario de prueba funcionando correctamente</p></div>';
        }
        
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
        echo '<h1>📊 Resumen del Plugin</h1>';
        echo '<p style="color: #666; font-size: 14px; margin-bottom: 30px;">Aquí puedes ver cómo está funcionando el plugin y el estado de tu sistema.</p>';
        
        echo '<div class="bcv-admin-container">';
        
        // Resumen ejecutivo
        echo '<div class="bcv-panel" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 30px;">';
        echo '<h2 style="color: white; margin-top: 0;">🎯 Estado General</h2>';
        
        $current_price = $price_stats['last_price'] ?: 'No disponible';
        $price_trend = $this->get_price_trend($price_stats);
        $system_health = $this->get_system_health($cron_stats, $scraping_info);
        
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">';
        
        // Precio actual
        echo '<div style="text-align: center; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;">';
        echo '<div style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">💰 Precio Actual</div>';
        echo '<div style="font-size: 32px; font-weight: bold;">$' . esc_html($current_price) . '</div>';
        echo '<div style="font-size: 14px; opacity: 0.8;">' . $price_trend . '</div>';
        echo '</div>';
        
        // Estado del sistema
        echo '<div style="text-align: center; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;">';
        echo '<div style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">🔧 Estado</div>';
        echo '<div style="font-size: 32px; font-weight: bold;">' . $system_health['icon'] . '</div>';
        echo '<div style="font-size: 14px; opacity: 0.8;">' . $system_health['text'] . '</div>';
        echo '</div>';
        
        // Tareas automáticas
        $cron_status = wp_next_scheduled('bcv_scrape_dollar_rate') !== false ? '✅ Activo' : '❌ Inactivo';
        echo '<div style="text-align: center; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;">';
        echo '<div style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">⏰ Automático</div>';
        echo '<div style="font-size: 32px; font-weight: bold;">' . $cron_status . '</div>';
        echo '<div style="font-size: 14px; opacity: 0.8;">Actualización automática</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        // Información de precios - Rediseñada
        echo '<div class="bcv-panel">';
        echo '<h2>💰 Historial de Precios del Dólar</h2>';
        echo '<p style="color: #666; margin-bottom: 20px;">Resumen de todos los precios que hemos registrado del Banco Central de Venezuela.</p>';
        
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">';
        
        // Total de registros
        echo '<div style="text-align: center; background: #e3f2fd; padding: 20px; border-radius: 12px; border-left: 4px solid #2196f3;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">📊</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #1976d2;">' . esc_html($price_stats['total_records']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Registros guardados</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Desde que empezamos a rastrear</div>';
        echo '</div>';
        
        // Precio más alto
        echo '<div style="text-align: center; background: #fff3e0; padding: 20px; border-radius: 12px; border-left: 4px solid #ff9800;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">📈</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #f57c00;">$' . esc_html($price_stats['max_price']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Precio más alto</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">El valor máximo registrado</div>';
        echo '</div>';
        
        // Precio más bajo
        echo '<div style="text-align: center; background: #e8f5e8; padding: 20px; border-radius: 12px; border-left: 4px solid #4caf50;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">📉</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #388e3c;">$' . esc_html($price_stats['min_price']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Precio más bajo</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">El valor mínimo registrado</div>';
        echo '</div>';
        
        // Precio promedio
        echo '<div style="text-align: center; background: #f3e5f5; padding: 20px; border-radius: 12px; border-left: 4px solid #9c27b0;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">📊</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #7b1fa2;">$' . esc_html(round($price_stats['avg_price'], 2)) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Precio promedio</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Promedio de todos los registros</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Información de fechas
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #6c757d;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #495057;">📅 Período de Registro</h4>';
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">';
        echo '<div><strong>Primer precio registrado:</strong><br><span style="color: #666;">' . esc_html($price_stats['first_date']) . '</span></div>';
        echo '<div><strong>Último precio registrado:</strong><br><span style="color: #666;">' . esc_html($price_stats['last_date']) . '</span></div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Tareas automáticas - Rediseñadas
        echo '<div class="bcv-panel">';
        echo '<h2>⏰ Tareas Automáticas</h2>';
        echo '<p style="color: #666; margin-bottom: 20px;">El plugin actualiza automáticamente el precio del dólar cada cierto tiempo. Aquí puedes ver cómo está funcionando.</p>';
        
        // Calcular porcentaje de éxito
        $success_rate = $cron_stats['total_executions'] > 0 ? round(($cron_stats['successful_executions'] / $cron_stats['total_executions']) * 100, 1) : 0;
        
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">';
        
        // Total de actualizaciones
        echo '<div style="text-align: center; background: #e3f2fd; padding: 20px; border-radius: 12px; border-left: 4px solid #2196f3;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">🔄</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #1976d2;">' . esc_html($cron_stats['total_executions']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Actualizaciones realizadas</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Intentos de obtener precio</div>';
        echo '</div>';
        
        // Actualizaciones exitosas
        echo '<div style="text-align: center; background: #e8f5e8; padding: 20px; border-radius: 12px; border-left: 4px solid #4caf50;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">✅</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #388e3c;">' . esc_html($cron_stats['successful_executions']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Actualizaciones exitosas</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Precios obtenidos correctamente</div>';
        echo '</div>';
        
        // Actualizaciones fallidas
        $failed_color = $cron_stats['failed_executions'] > 0 ? '#ffebee' : '#f8f9fa';
        $failed_border = $cron_stats['failed_executions'] > 0 ? '#f44336' : '#6c757d';
        $failed_text = $cron_stats['failed_executions'] > 0 ? '#d32f2f' : '#666';
        
        echo '<div style="text-align: center; background: ' . $failed_color . '; padding: 20px; border-radius: 12px; border-left: 4px solid ' . $failed_border . ';">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">' . ($cron_stats['failed_executions'] > 0 ? '❌' : '✅') . '</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: ' . $failed_text . ';">' . esc_html($cron_stats['failed_executions']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Actualizaciones fallidas</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">' . ($cron_stats['failed_executions'] > 0 ? 'Hubo problemas al obtener precio' : '¡Todo funcionando bien!') . '</div>';
        echo '</div>';
        
        // Porcentaje de éxito
        $success_color = $success_rate >= 80 ? '#4caf50' : ($success_rate >= 60 ? '#ff9800' : '#f44336');
        echo '<div style="text-align: center; background: #f8f9fa; padding: 20px; border-radius: 12px; border-left: 4px solid ' . $success_color . ';">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">📊</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: ' . $success_color . ';">' . $success_rate . '%</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Tasa de éxito</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Porcentaje de actualizaciones exitosas</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Información de horarios
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #6c757d;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #495057;">🕒 Horarios de Actualización</h4>';
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">';
        echo '<div><strong>Última actualización:</strong><br><span style="color: #666;">' . esc_html($cron_stats['last_execution']) . '</span></div>';
        echo '<div><strong>Próxima actualización:</strong><br><span style="color: #666;">' . esc_html($cron_stats['next_execution']) . '</span></div>';
        echo '</div>';
        echo '</div>';
        
        // Botón para resetear estadísticas
        echo '<div style="margin-top: 20px; text-align: center;">';
        echo '<form method="post" style="display: inline-block;">';
        wp_nonce_field('bcv_reset_cron_stats');
        echo '<button type="submit" name="bcv_reset_cron_stats" class="button button-secondary" style="background: #6c757d; border-color: #6c757d; color: white; padding: 8px 16px; border-radius: 6px;">🔄 Reiniciar Contadores</button>';
        echo '</form>';
        echo '<p style="font-size: 12px; color: #999; margin-top: 8px;">Esto reiniciará todos los contadores a cero</p>';
        echo '</div>';
        
        echo '</div>';
        
        // Conexión con el Banco Central - Rediseñada
        echo '<div class="bcv-panel">';
        echo '<h2>🌐 Conexión con el Banco Central</h2>';
        echo '<p style="color: #666; margin-bottom: 20px;">El plugin se conecta con el sitio web del Banco Central de Venezuela para obtener el precio del dólar. Aquí puedes ver el estado de esa conexión.</p>';
        
        // Determinar estado general de la conexión
        $connection_health = 'good';
        $connection_message = 'Conexión funcionando correctamente';
        
        if ($scraping_info['failed_scrapings'] > $scraping_info['successful_scrapings']) {
            $connection_health = 'bad';
            $connection_message = 'Hay problemas con la conexión';
        } elseif ($scraping_info['failed_scrapings'] > 0) {
            $connection_health = 'warning';
            $connection_message = 'Conexión con algunos problemas';
        }
        
        $health_colors = array(
            'good' => array('bg' => '#e8f5e8', 'border' => '#4caf50', 'text' => '#388e3c'),
            'warning' => array('bg' => '#fff3e0', 'border' => '#ff9800', 'text' => '#f57c00'),
            'bad' => array('bg' => '#ffebee', 'border' => '#f44336', 'text' => '#d32f2f')
        );
        
        $health_icons = array(
            'good' => '✅',
            'warning' => '⚠️',
            'bad' => '❌'
        );
        
        echo '<div style="background: ' . $health_colors[$connection_health]['bg'] . '; padding: 20px; border-radius: 12px; border-left: 4px solid ' . $health_colors[$connection_health]['border'] . '; margin-bottom: 20px;">';
        echo '<div style="display: flex; align-items: center; gap: 15px;">';
        echo '<div style="font-size: 48px;">' . $health_icons[$connection_health] . '</div>';
        echo '<div>';
        echo '<h3 style="margin: 0 0 5px 0; color: ' . $health_colors[$connection_health]['text'] . ';">Estado de la Conexión</h3>';
        echo '<p style="margin: 0; color: #666; font-size: 16px;">' . $connection_message . '</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">';
        
        // Precio actual en caché
        echo '<div style="text-align: center; background: #e3f2fd; padding: 20px; border-radius: 12px; border-left: 4px solid #2196f3;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">💰</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #1976d2;">$' . esc_html($scraping_info['cached_price'] ?: 'N/A') . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Precio actual guardado</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Último precio obtenido</div>';
        echo '</div>';
        
        // Estado del caché
        $cache_status = $scraping_info['cache_valid'] ? 'Válido' : 'Expirado';
        $cache_color = $scraping_info['cache_valid'] ? '#4caf50' : '#f44336';
        $cache_bg = $scraping_info['cache_valid'] ? '#e8f5e8' : '#ffebee';
        $cache_icon = $scraping_info['cache_valid'] ? '✅' : '❌';
        
        echo '<div style="text-align: center; background: ' . $cache_bg . '; padding: 20px; border-radius: 12px; border-left: 4px solid ' . $cache_color . ';">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">' . $cache_icon . '</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: ' . $cache_color . ';">' . $cache_status . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Estado del precio guardado</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">' . ($scraping_info['cache_valid'] ? 'Precio actualizado recientemente' : 'Precio necesita actualización') . '</div>';
        echo '</div>';
        
        // Intentos de conexión
        echo '<div style="text-align: center; background: #f8f9fa; padding: 20px; border-radius: 12px; border-left: 4px solid #6c757d;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">🔄</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #495057;">' . esc_html($scraping_info['scraping_attempts']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Intentos de conexión</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Veces que intentó obtener precio</div>';
        echo '</div>';
        
        // Conexiones exitosas
        echo '<div style="text-align: center; background: #e8f5e8; padding: 20px; border-radius: 12px; border-left: 4px solid #4caf50;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">✅</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #388e3c;">' . esc_html($scraping_info['successful_scrapings']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Conexiones exitosas</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Precios obtenidos correctamente</div>';
        echo '</div>';
        
        // Conexiones fallidas
        $failed_color = $scraping_info['failed_scrapings'] > 0 ? '#f44336' : '#6c757d';
        $failed_bg = $scraping_info['failed_scrapings'] > 0 ? '#ffebee' : '#f8f9fa';
        
        echo '<div style="text-align: center; background: ' . $failed_bg . '; padding: 20px; border-radius: 12px; border-left: 4px solid ' . $failed_color . ';">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">' . ($scraping_info['failed_scrapings'] > 0 ? '❌' : '✅') . '</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: ' . $failed_color . ';">' . esc_html($scraping_info['failed_scrapings']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Conexiones fallidas</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">' . ($scraping_info['failed_scrapings'] > 0 ? 'Problemas al conectar' : '¡Todo funcionando!') . '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Información de fechas
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #6c757d;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #495057;">🕒 Última Actividad</h4>';
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">';
        echo '<div><strong>Última conexión exitosa:</strong><br><span style="color: #666;">' . esc_html($scraping_info['last_scraping']) . '</span></div>';
        echo '<div><strong>Precio expira:</strong><br><span style="color: #666;">' . esc_html($scraping_info['cache_expiry']) . '</span></div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Resumen de actividad - Rediseñado
        echo '<div class="bcv-panel">';
        echo '<h2>📈 Actividad de Hoy</h2>';
        echo '<p style="color: #666; margin-bottom: 20px;">Resumen de lo que ha pasado hoy con el plugin.</p>';
        
        // Obtener estadísticas de logs simplificadas
        global $wpdb;
        $logs_table = $wpdb->prefix . 'bcv_logs';
        
        $recent_activity = array(
            'total_events' => $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table}"),
            'events_today' => $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table} WHERE DATE(created_at) = CURDATE()"),
            'errors_today' => $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table} WHERE log_level = 'ERROR' AND DATE(created_at) = CURDATE()"),
            'success_today' => $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table} WHERE log_level = 'SUCCESS' AND DATE(created_at) = CURDATE()"),
            'last_activity' => $wpdb->get_var("SELECT created_at FROM {$logs_table} ORDER BY created_at DESC LIMIT 1")
        );
        
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-top: 20px;">';
        
        // Eventos de hoy
        echo '<div style="text-align: center; background: #e3f2fd; padding: 20px; border-radius: 12px; border-left: 4px solid #2196f3;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">📊</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #1976d2;">' . esc_html($recent_activity['events_today']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Eventos de hoy</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Actividades registradas hoy</div>';
        echo '</div>';
        
        // Errores de hoy
        $error_color = $recent_activity['errors_today'] > 0 ? '#f44336' : '#6c757d';
        $error_bg = $recent_activity['errors_today'] > 0 ? '#ffebee' : '#f8f9fa';
        
        echo '<div style="text-align: center; background: ' . $error_bg . '; padding: 20px; border-radius: 12px; border-left: 4px solid ' . $error_color . ';">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">' . ($recent_activity['errors_today'] > 0 ? '❌' : '✅') . '</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: ' . $error_color . ';">' . esc_html($recent_activity['errors_today']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Errores de hoy</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">' . ($recent_activity['errors_today'] > 0 ? 'Problemas detectados' : '¡Todo funcionando!') . '</div>';
        echo '</div>';
        
        // Éxitos de hoy
        echo '<div style="text-align: center; background: #e8f5e8; padding: 20px; border-radius: 12px; border-left: 4px solid #4caf50;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">✅</div>';
        echo '<div style="font-size: 28px; font-weight: bold; color: #388e3c;">' . esc_html($recent_activity['success_today']) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Éxitos de hoy</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Operaciones exitosas</div>';
        echo '</div>';
        
        // Última actividad
        $last_activity_time = $recent_activity['last_activity'] ? date('H:i', strtotime($recent_activity['last_activity'])) : 'N/A';
        echo '<div style="text-align: center; background: #f8f9fa; padding: 20px; border-radius: 12px; border-left: 4px solid #6c757d;">';
        echo '<div style="font-size: 32px; margin-bottom: 10px;">🕒</div>';
        echo '<div style="font-size: 24px; font-weight: bold; color: #495057;">' . esc_html($last_activity_time) . '</div>';
        echo '<div style="font-size: 14px; color: #666; margin-top: 5px;">Última actividad</div>';
        echo '<div style="font-size: 12px; color: #999; margin-top: 5px;">Hora de la última acción</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Información adicional
        if ($recent_activity['total_events'] > 0) {
            echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #6c757d;">';
            echo '<h4 style="margin: 0 0 10px 0; color: #495057;">📋 Resumen Total</h4>';
            echo '<p style="margin: 0; color: #666; font-size: 14px;">Desde que se instaló el plugin, se han registrado <strong>' . esc_html($recent_activity['total_events']) . ' eventos</strong> en total.</p>';
            echo '</div>';
        }
        
        echo '</div>';
        
        echo '</div>'; // .bcv-admin-container
        
        echo '</div>'; // .wrap
    }
    
    /**
     * Obtener tendencia del precio
     */
    private function get_price_trend($price_stats) {
        if ($price_stats['total_records'] < 2) {
            return '📊 Datos insuficientes';
        }
        
        // Simular tendencia basada en precio promedio vs último precio
        $avg = $price_stats['avg_price'];
        $last = $price_stats['last_price'];
        
        if ($last > $avg * 1.05) {
            return '📈 Subiendo';
        } elseif ($last < $avg * 0.95) {
            return '📉 Bajando';
        } else {
            return '➡️ Estable';
        }
    }
    
    /**
     * Obtener estado de salud del sistema
     */
    private function get_system_health($cron_stats, $scraping_info) {
        $errors = 0;
        $warnings = 0;
        
        // Verificar scraping
        if ($scraping_info['failed_scrapings'] > $scraping_info['successful_scrapings']) {
            $errors++;
        }
        
        // Verificar cron
        if ($cron_stats['failed_executions'] > 0) {
            $warnings++;
        }
        
        // Verificar caché
        if (!$scraping_info['cache_valid']) {
            $warnings++;
        }
        
        if ($errors > 0) {
            return array('icon' => '❌', 'text' => 'Necesita atención');
        } elseif ($warnings > 0) {
            return array('icon' => '⚠️', 'text' => 'Funcionando con advertencias');
        } else {
            return array('icon' => '✅', 'text' => 'Todo funcionando bien');
        }
    }
    
    /**
     * Renderizar configuración de depuración
     */
    private function render_debug_settings() {
        $debug_mode = BCV_Logger::is_debug_mode_enabled();
        
        echo '<div class="bcv-simple-panel">';
        echo '<h3>🐛 Modo de Depuración</h3>';
        
        echo '<form method="post" class="bcv-simple-form">';
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
        
        // Botón de prueba
        echo '<form method="post" style="display: inline;">';
        echo '<button type="submit" name="test_form" class="bcv-test-btn">Probar Formulario</button>';
        echo '</form>';
        echo '</div>';
        
        echo '</div>';
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
        
        // Explicación de la tabla
        echo '<div class="bcv-panel" style="background: #f8f9fa; border: 1px solid #e9ecef;">';
        echo '<h3 style="margin-top: 0;">📖 Cómo leer esta tabla</h3>';
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">';
        
        echo '<div>';
        echo '<strong>📅 Cuándo:</strong><br>';
        echo '<small>Fecha y hora del evento</small>';
        echo '</div>';
        
        echo '<div>';
        echo '<strong>🔍 Tipo:</strong><br>';
        echo '<small>❌ Error, ⚠️ Advertencia, ✅ Éxito, ℹ️ Info, 🐛 Debug</small>';
        echo '</div>';
        
        echo '<div>';
        echo '<strong>📝 Qué pasó:</strong><br>';
        echo '<small>Descripción del evento en lenguaje simple</small>';
        echo '</div>';
        
        echo '<div>';
        echo '<strong>📍 Dónde:</strong><br>';
        echo '<small>Parte del sistema donde ocurrió</small>';
        echo '</div>';
        
        echo '<div>';
        echo '<strong>👤 Quién:</strong><br>';
        echo '<small>Usuario que realizó la acción</small>';
        echo '</div>';
        
        echo '</div>';
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
        error_log('BCV Dólar Tracker: Procesando configuración de cron');
        error_log('BCV Dólar Tracker: POST data para cron: ' . print_r($_POST, true));
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'bcv_cron_settings')) {
            error_log('BCV Dólar Tracker: Error de nonce en configuración de cron');
            wp_die('Acceso denegado - Error de nonce en cron');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            error_log('BCV Dólar Tracker: Permisos insuficientes para configuración de cron');
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
            error_log('BCV Dólar Tracker: Usando intervalo predefinido - Total segundos: ' . $total_seconds . ', Horas: ' . $hours . ', Minutos: ' . $minutes . ', Segundos: ' . $seconds);
        } else {
            // Usar valores personalizados
            $hours = intval($_POST['cron_hours']);
            $minutes = intval($_POST['cron_minutes']);
            $seconds = intval($_POST['cron_seconds']);
            error_log('BCV Dólar Tracker: Usando valores personalizados - Horas: ' . $hours . ', Minutos: ' . $minutes . ', Segundos: ' . $seconds);
        }
        
        // Validaciones robustas
        $hours = max(0, min(24, $hours));
        $minutes = max(0, min(59, $minutes));
        $seconds = max(0, min(59, $seconds));
        
        // Calcular total de segundos
        $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        
        // Mínimo 1 minuto (60 segundos)
        if ($total_seconds < 60) {
            $hours = 0;
            $minutes = 1;
            $seconds = 0;
        }
        
        // Guardar configuración
        $settings = array(
            'enabled' => !empty($current_settings['enabled']) ? $current_settings['enabled'] : true, // Mantener estado actual o true por defecto
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds
        );
        
        error_log('BCV Dólar Tracker: Configuración a guardar: ' . print_r($settings, true));
        
        // Verificar configuración actual
        $current_cron_settings = get_option('bcv_cron_settings', array());
        error_log('BCV Dólar Tracker: Configuración actual de cron: ' . print_r($current_cron_settings, true));
        
        $saved = update_option('bcv_cron_settings', $settings);
        error_log('BCV Dólar Tracker: update_option resultado: ' . ($saved ? 'OK' : 'Error'));
        
        // Verificar si realmente se guardó
        $new_cron_settings = get_option('bcv_cron_settings', array());
        error_log('BCV Dólar Tracker: Configuración después de guardar: ' . print_r($new_cron_settings, true));
        
        // Configurar cron
        $cron = new BCV_Cron();
        $result = $cron->setup_cron($settings);
        error_log('BCV Dólar Tracker: Cron configurado: ' . ($result ? 'OK' : 'Error'));
        
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
        error_log('BCV Dólar Tracker: Iniciando procesamiento de debug settings');
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'bcv_debug_settings')) {
            error_log('BCV Dólar Tracker: Error de nonce en debug settings');
            wp_die('Acceso denegado - Error de nonce');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            error_log('BCV Dólar Tracker: Permisos insuficientes en debug settings');
            wp_die('Permisos insuficientes');
        }
        
        // Procesar toggle de modo de depuración
        $debug_mode = isset($_POST['debug_mode']) ? true : false;
        error_log('BCV Dólar Tracker: Debug mode checkbox presente: ' . (isset($_POST['debug_mode']) ? 'Sí' : 'No'));
        error_log('BCV Dólar Tracker: Debug mode valor: ' . ($debug_mode ? 'true' : 'false'));
        
        if ($debug_mode) {
            error_log('BCV Dólar Tracker: Intentando habilitar debug mode');
            $result = BCV_Logger::enable_debug_mode();
            error_log('BCV Dólar Tracker: Debug mode habilitado: ' . ($result ? 'OK' : 'Error'));
        } else {
            error_log('BCV Dólar Tracker: Intentando deshabilitar debug mode');
            $result = BCV_Logger::disable_debug_mode();
            error_log('BCV Dólar Tracker: Debug mode deshabilitado: ' . ($result ? 'OK' : 'Error'));
        }
        
        // Redirigir con mensaje de éxito
        wp_redirect(add_query_arg('debug-updated', 'true', admin_url('admin.php?page=bcv-dolar-tracker')));
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
     * Toggle del cron (AJAX)
     */
    public function toggle_cron() {
        // Verificar nonce y permisos usando la clase de seguridad
        BCV_Security::verify_ajax_nonce($_POST['nonce'] ?? '', 'bcv_admin_nonce');
        BCV_Security::check_capability('manage_options');
        
        // Log del evento
        BCV_Security::log_security_event('Cron toggle attempt', 'User: ' . get_current_user_id());
        
        if (!class_exists('BCV_Cron')) {
            wp_send_json_error('Clase BCV_Cron no disponible');
        }
        
        $cron = new BCV_Cron();
        $result = $cron->toggle_cron();
        
        if ($result) {
            $settings = $cron->get_cron_settings();
            $status = $settings['enabled'] ? 'activado' : 'desactivado';
            
            BCV_Security::log_security_event('Cron toggle successful', 'Status: ' . $status);
            wp_send_json_success(array(
                'message' => 'Cron ' . $status . ' exitosamente',
                'enabled' => $settings['enabled'],
                'status_text' => $settings['enabled'] ? 'Activo' : 'Inactivo'
            ));
        } else {
            BCV_Security::log_security_event('Cron toggle failed', 'Error: Unknown error');
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
        
        echo '<div class="bcv-simple-panel">';
        echo '<h3>⚙️ Configuración del Cron</h3>';
        
        // Estado del cron
        $cron_status = wp_next_scheduled('bcv_scrape_dollar_rate') ? 'Activo' : 'Inactivo';
        echo '<div class="bcv-status-info">';
        echo '<span class="bcv-status-label">Estado:</span>';
        echo '<span class="bcv-status-value bcv-status-' . (wp_next_scheduled('bcv_scrape_dollar_rate') ? 'active' : 'inactive') . '">' . $cron_status . '</span>';
        echo '</div>';
        
        // Formulario de configuración
        echo '<form method="post" class="bcv-simple-form">';
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
        echo '<div class="bcv-simple-panel">';
        echo '<h3>🧪 Prueba de Scraping</h3>';
        echo '<p>Haz clic en el botón para probar la conexión con el BCV y obtener el precio actual del dólar.</p>';
        
        echo '<div class="bcv-test-section">';
        echo '<button type="button" id="test-scraping" class="bcv-test-btn">Probar Scraping</button>';
        echo '<span id="test-result" class="bcv-result"></span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Renderizar información del cron
     */
    private function render_cron_info() {
        echo '<div class="bcv-simple-panel">';
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
            
            // Botón para forzar programación del cron
            if (!$cron_info['is_scheduled']) {
                echo '<form method="post" class="bcv-inline-form">';
                wp_nonce_field('bcv_force_cron_schedule');
                echo '<button type="submit" name="bcv_force_cron_schedule" class="bcv-action-btn">Forzar Programación</button>';
                echo '</form>';
            }
        } else {
            echo '<p class="bcv-error">❌ No se pudo obtener información del cron</p>';
        }
        
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
            echo '<div class="notice notice-success is-dismissible"><p>✅ Tabla de logs recreada correctamente</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>❌ Error al recrear tabla de logs: ' . $wpdb->last_error . '</p></div>';
        }
    }
}
