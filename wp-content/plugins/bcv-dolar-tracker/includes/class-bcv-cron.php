<?php
/**
 * Clase para gestión de tareas cron del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Cron {
    
    /**
     * Hook del cron
     * 
     * @var string
     */
    private $cron_hook = 'bcv_scrape_dollar_rate';
    
    /**
     * Configuración actual del cron
     * 
     * @var array
     */
    private $settings;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Cargar configuración
        $this->load_settings();
        
        // Hooks para el cron
        add_action($this->cron_hook, array($this, 'execute_scraping_task'));
        add_action('wp_loaded', array($this, 'maybe_schedule_cron'));
        
        // Hook para limpieza semanal de registros antiguos
        add_action('bcv_weekly_cleanup', array($this, 'execute_weekly_cleanup'));
        add_action('wp_loaded', array($this, 'maybe_schedule_weekly_cleanup'));
        
        // Registrar intervalo personalizado
        add_filter('cron_schedules', array($this, 'add_custom_cron_interval'));
        
        // Hook para limpiar cron en desactivación
        add_action('deactivate_plugin', array($this, 'clear_cron'));
    }
    
    /**
     * Cargar configuración del cron
     */
    private function load_settings() {
        $this->settings = get_option('bcv_cron_settings', array(
            'hours' => 1,
            'minutes' => 0,
            'seconds' => 0,
            'enabled' => true
        ));
    }
    
    /**
     * Configurar tarea cron
     * 
     * @param array $settings Configuración del cron (horas, minutos, segundos, enabled)
     * @return bool True si se configuró correctamente, False en caso contrario
     */
    public function setup_cron($settings = null) {
        // Limpiar cron existente
        $this->clear_cron();
        
        // Usar configuración proporcionada o la actual
        if ($settings !== null) {
            $this->settings = wp_parse_args($settings, $this->settings);
            update_option('bcv_cron_settings', $this->settings);
        }
        
        // Si el cron está deshabilitado, no programar
        if (!$this->settings['enabled']) {
            error_log('BCV Dólar Tracker: Cron deshabilitado, no se programará');
            return true;
        }
        
        // Calcular intervalo en segundos
        $interval = $this->calculate_interval();
        
        // Obtener nombre del intervalo personalizado
        $interval_name = $this->get_interval_name($interval);
        
        // Programar el cron usando el intervalo personalizado
        $scheduled = wp_schedule_event(time(), $interval_name, $this->cron_hook);
        
        if ($scheduled) {
            error_log("BCV Dólar Tracker: Cron programado exitosamente con intervalo: {$interval_name}");
            return true;
        } else {
            error_log('BCV Dólar Tracker: Error al programar el cron');
            return false;
        }
    }
    
    /**
     * Activar cron independientemente
     * 
     * @return bool True si se activó correctamente, False en caso contrario
     */
    public function enable_cron() {
        $this->settings['enabled'] = true;
        update_option('bcv_cron_settings', $this->settings);
        
        $result = $this->setup_cron();
        
        if ($result) {
            error_log('BCV Dólar Tracker: Cron activado exitosamente');
        } else {
            error_log('BCV Dólar Tracker: Error al activar el cron');
        }
        
        return $result;
    }
    
    /**
     * Desactivar cron independientemente
     * 
     * @return bool True si se desactivó correctamente, False en caso contrario
     */
    public function disable_cron() {
        $this->settings['enabled'] = false;
        update_option('bcv_cron_settings', $this->settings);
        
        $result = $this->clear_cron();
        
        if ($result) {
            error_log('BCV Dólar Tracker: Cron desactivado exitosamente');
        } else {
            error_log('BCV Dólar Tracker: Error al desactivar el cron');
        }
        
        return $result;
    }
    
    /**
     * Toggle del estado del cron
     * 
     * @return bool True si se cambió correctamente, False en caso contrario
     */
    public function toggle_cron() {
        if ($this->settings['enabled']) {
            return $this->disable_cron();
        } else {
            return $this->enable_cron();
        }
    }
    
    /**
     * Calcular intervalo del cron en segundos
     * 
     * @return int Intervalo en segundos
     */
    private function calculate_interval() {
        $hours = intval($this->settings['hours']);
        $minutes = intval($this->settings['minutes']);
        $seconds = intval($this->settings['seconds']);
        
        $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        
        // Mínimo 60 segundos (1 minuto)
        return max($total_seconds, 60);
    }
    
    /**
     * Verificar si se debe programar el cron
     */
    public function maybe_schedule_cron() {
        // Solo programar si no está ya programado
        if (!wp_next_scheduled($this->cron_hook)) {
            if ($this->settings['enabled']) {
                $this->setup_cron();
            }
        }
    }
    
    /**
     * Limpiar tarea cron
     * 
     * @return bool True si se limpió correctamente, False en caso contrario
     */
    public function clear_cron() {
        $cleared1 = wp_clear_scheduled_hook($this->cron_hook);
        $cleared2 = wp_clear_scheduled_hook('bcv_weekly_cleanup');
        
        // wp_clear_scheduled_hook devuelve false si no hay crones programados, pero eso no es un error
        $scraping_status = ($cleared1 !== false) ? 'OK' : 'No programado';
        $cleanup_status = ($cleared2 !== false) ? 'OK' : 'No programado';
        
        error_log('BCV Dólar Tracker: Crones limpiados - Scraping: ' . $scraping_status . ', Limpieza: ' . $cleanup_status);
        
        // Consideramos exitoso si no hay errores (false significa que no había crones programados)
        return true;
    }
    
    /**
     * Añadir intervalo personalizado para el cron
     * 
     * @param array $schedules Intervalos existentes
     * @return array Intervalos modificados
     */
    public function add_custom_cron_interval($schedules) {
        $interval = $this->calculate_interval();
        
        // Crear nombre del intervalo basado en el tiempo
        if ($interval < 60) {
            $interval_name = 'every_' . $interval . '_seconds';
            $display_name = $interval . ' segundos';
        } elseif ($interval < 3600) {
            $minutes = floor($interval / 60);
            $interval_name = 'every_' . $minutes . '_minutes';
            $display_name = $minutes . ' minuto' . ($minutes > 1 ? 's' : '');
        } else {
            $hours = floor($interval / 3600);
            $interval_name = 'every_' . $hours . '_hours';
            $display_name = $hours . ' hora' . ($hours > 1 ? 's' : '');
        }
        
        $schedules[$interval_name] = array(
            'interval' => $interval,
            'display' => 'Cada ' . $display_name
        );
        
        
        return $schedules;
    }
    
    /**
     * Obtener nombre del intervalo personalizado
     * 
     * @param int $interval Intervalo en segundos
     * @return string Nombre del intervalo
     */
    private function get_interval_name($interval) {
        if ($interval < 60) {
            return 'every_' . $interval . '_seconds';
        } elseif ($interval < 3600) {
            $minutes = floor($interval / 60);
            return 'every_' . $minutes . '_minutes';
        } else {
            $hours = floor($interval / 3600);
            return 'every_' . $hours . '_hours';
        }
    }
    
    /**
     * Forzar programación del cron
     * 
     * @return bool True si se programó correctamente, False en caso contrario
     */
    public function force_schedule_cron() {
        error_log('BCV Dólar Tracker: Forzando programación del cron');
        
        // Limpiar cron existente
        $this->clear_cron();
        
        // Programar nuevo cron
        $result = $this->setup_cron();
        
        if ($result) {
            error_log('BCV Dólar Tracker: Cron forzado exitosamente');
        } else {
            error_log('BCV Dólar Tracker: Error al forzar el cron');
        }
        
        return $result;
    }
    
    /**
     * Obtener configuración actual del cron
     * 
     * @return array Configuración del cron
     */
    public function get_cron_settings() {
        return $this->settings;
    }
    
    /**
     * Obtener información del cron programado
     * 
     * @return array Información del cron
     */
    public function get_cron_info() {
        $next_scheduled = wp_next_scheduled($this->cron_hook);
        $interval = $this->calculate_interval();
        
        return array(
            'next_run' => $next_scheduled ? date('Y-m-d H:i:s', $next_scheduled) : 'No programado',
            'interval_seconds' => $interval,
            'interval_formatted' => $this->format_interval($interval),
            'is_scheduled' => $next_scheduled !== false,
            'settings' => $this->settings
        );
    }
    
    /**
     * Formatear intervalo en formato legible
     * 
     * @param int $seconds Segundos totales
     * @return string Intervalo formateado
     */
    private function format_interval($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        $parts = array();
        
        if ($hours > 0) {
            $parts[] = $hours . ' hora' . ($hours > 1 ? 's' : '');
        }
        
        if ($minutes > 0) {
            $parts[] = $minutes . ' minuto' . ($minutes > 1 ? 's' : '');
        }
        
        if ($secs > 0) {
            $parts[] = $secs . ' segundo' . ($secs > 1 ? 's' : '');
        }
        
        return implode(', ', $parts);
    }
    
    /**
     * Ejecutar tarea de scraping
     */
    public function execute_scraping_task() {
        BCV_Performance_Monitor::start_timer('cron_scraping_task');
        BCV_Logger::info(BCV_Logger::CONTEXT_CRON, 'Ejecutando tarea cron de scraping');
        
        // Registrar inicio de ejecución del cron
        BCV_Logger::info(BCV_Logger::CONTEXT_CRON, 'Tarea programada iniciada');
        
        // Verificar si ya se ejecutó recientemente (evitar duplicados)
        $last_execution = get_transient('bcv_cron_last_execution');
        if ($last_execution && (time() - $last_execution) < 300) { // 5 minutos
            error_log('BCV Dólar Tracker: Cron ejecutado recientemente, saltando');
            BCV_Logger::info(BCV_Logger::CONTEXT_CRON, 'Cron ejecutado recientemente, saltando ejecución');
            return;
        }
        
        // Marcar tiempo de ejecución
        set_transient('bcv_cron_last_execution', time(), 3600);
        
        // Variable para controlar el éxito general de la operación
        $operation_success = false;
        
        // Ejecutar scraping con reintentos automáticos (sin duplicar estadísticas)
        $scraper = new BCV_Scraper();
        $result = $scraper->scrape_with_retries(3, false);
        
        if ($result !== false) {
            error_log("BCV Dólar Tracker: Scraping exitoso, precio obtenido: {$result}");
            
            // Guardar en base de datos usando singleton con lógica inteligente
            $database = BCV_Database::get_instance();
            $inserted = $database->insert_price($result);
            
            if ($inserted === 'skipped') {
                error_log('BCV Dólar Tracker: Precio no guardado - Lógica inteligente evitó duplicado');
                $operation_success = true; // Consideramos exitoso porque obtuvimos el precio correctamente
            } elseif ($inserted) {
                error_log("BCV Dólar Tracker: Precio guardado en BD con ID: {$inserted}");
                $operation_success = true;
            } else {
                error_log('BCV Dólar Tracker: Error al guardar precio en BD');
                $operation_success = false;
            }
        } else {
            error_log('BCV Dólar Tracker: Scraping falló, no se pudo obtener precio');
            $operation_success = false;
        }
        
        // Actualizar estadísticas UNA SOLA VEZ con el resultado final
        $this->update_cron_stats($operation_success);
        
        // Registrar final de ejecución del cron
        if ($operation_success) {
            if ($inserted === 'skipped') {
                BCV_Logger::info(BCV_Logger::CONTEXT_CRON, 'Tarea finalizada. El valor de la tasa no cambió.');
            } else {
                BCV_Logger::info(BCV_Logger::CONTEXT_CRON, "Tarea finalizada. Nueva tasa {$result} Bs. guardada.");
            }
        } else {
            BCV_Logger::error(BCV_Logger::CONTEXT_CRON, 'Tarea finalizada con errores. No se pudo obtener o guardar la tasa.');
        }
        
        // Reprogramar próximo ejecución
        $this->reschedule_cron();
        
        // Finalizar monitoreo de performance
        BCV_Performance_Monitor::end_timer('cron_scraping_task', array(
            'success' => $operation_success,
            'price_obtained' => $result !== false ? $result : null
        ));
    }
    
    /**
     * Reprogramar el cron para la próxima ejecución
     */
    private function reschedule_cron() {
        if (!$this->settings['enabled']) {
            return;
        }
        
        $interval = $this->calculate_interval();
        $next_run = time() + $interval;
        $interval_name = $this->get_interval_name($interval);
        
        // Limpiar y reprogramar usando el intervalo personalizado
        wp_clear_scheduled_hook($this->cron_hook);
        wp_schedule_event($next_run, $interval_name, $this->cron_hook);
        
        error_log("BCV Dólar Tracker: Cron reprogramado para: " . date('Y-m-d H:i:s', $next_run) . " usando intervalo '{$interval_name}'");
    }
    
    /**
     * Ejecutar scraping manualmente (para testing)
     * 
     * @return array Resultado de la ejecución
     */
    public function execute_manual_scraping() {
        error_log('BCV Dólar Tracker: Ejecutando scraping manual');
        
        $scraper = new BCV_Scraper();
        $result = $scraper->scrape_bcv_rate();
        
        $response = array(
            'success' => false,
            'message' => '',
            'price' => null,
            'timestamp' => current_time('mysql')
        );
        
        if ($result !== false) {
            $response['success'] = true;
            $response['price'] = $result;
            $response['message'] = "Precio obtenido exitosamente: {$result}";
            
            // Guardar en base de datos usando singleton con lógica inteligente
            $database = BCV_Database::get_instance();
            $inserted = $database->insert_price($result);
            
            if ($inserted === 'skipped') {
                $response['message'] .= " (no guardado - lógica inteligente evitó duplicado)";
            } elseif ($inserted) {
                $response['message'] .= " y guardado en BD con ID: {$inserted}";
            } else {
                $response['message'] .= " pero falló al guardar en BD";
            }
            
            error_log('BCV Dólar Tracker: Scraping manual exitoso');
        } else {
            $response['message'] = 'Error al obtener precio del BCV';
            error_log('BCV Dólar Tracker: Scraping manual falló');
        }
        
        return $response;
    }
    
    /**
     * Obtener estadísticas del cron
     * 
     * @return array Estadísticas del cron
     */
    public function get_cron_stats() {
        $last_execution = get_transient('bcv_cron_last_execution');
        $next_scheduled = wp_next_scheduled($this->cron_hook);
        
        // Obtener estadísticas con valores por defecto seguros
        $total_executions = get_option('bcv_cron_total_executions', 0);
        $successful_executions = get_option('bcv_cron_successful_executions', 0);
        $failed_executions = get_option('bcv_cron_failed_executions', 0);
        
        // Asegurar que los valores sean números
        $total_executions = intval($total_executions);
        $successful_executions = intval($successful_executions);
        $failed_executions = intval($failed_executions);
        
        return array(
            'last_execution' => $last_execution ? date('Y-m-d H:i:s', $last_execution) : 'Nunca',
            'next_execution' => $next_scheduled ? date('Y-m-d H:i:s', $next_scheduled) : 'No programado',
            'is_enabled' => $this->settings['enabled'],
            'interval_formatted' => $this->format_interval($this->calculate_interval()),
            'total_executions' => $total_executions,
            'successful_executions' => $successful_executions,
            'failed_executions' => $failed_executions
        );
    }
    
    /**
     * Actualizar estadísticas del cron
     * 
     * @param bool $success Si la ejecución fue exitosa
     */
    public function update_cron_stats($success) {
        // Obtener valores actuales
        $total = get_option('bcv_cron_total_executions', 0);
        $successful = get_option('bcv_cron_successful_executions', 0);
        $failed = get_option('bcv_cron_failed_executions', 0);
        
        // Convertir a enteros
        $total = intval($total);
        $successful = intval($successful);
        $failed = intval($failed);
        
        // Incrementar total
        $total++;
        
        // Incrementar contador correspondiente
        if ($success) {
            $successful++;
        } else {
            $failed++;
        }
        
        // Guardar valores actualizados
        update_option('bcv_cron_total_executions', $total);
        update_option('bcv_cron_successful_executions', $successful);
        update_option('bcv_cron_failed_executions', $failed);
        
        error_log("BCV Dólar Tracker: Estadísticas del cron actualizadas - Total: {$total}, Exitosas: {$successful}, Fallidas: {$failed}");
    }
    
    /**
     * Resetear estadísticas del cron
     */
    public function reset_cron_stats() {
        delete_option('bcv_cron_total_executions');
        delete_option('bcv_cron_successful_executions');
        delete_option('bcv_cron_failed_executions');
        
        error_log('BCV Dólar Tracker: Estadísticas del cron reseteadas');
    }
    
    /**
     * Verificar si se debe programar la limpieza semanal
     */
    public function maybe_schedule_weekly_cleanup() {
        // Solo programar si no está ya programado
        if (!wp_next_scheduled('bcv_weekly_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'bcv_weekly_cleanup');
            error_log('BCV Dólar Tracker: Limpieza semanal programada');
        }
    }
    
    /**
     * Ejecutar limpieza semanal de registros antiguos
     */
    public function execute_weekly_cleanup() {
        error_log('BCV Dólar Tracker: Ejecutando limpieza semanal de registros antiguos');
        
        if (!class_exists('BCV_Database')) {
            error_log('BCV Dólar Tracker: Clase BCV_Database no disponible para limpieza');
            return;
        }
        
        $database = new BCV_Database();
        $deleted = $database->cleanup_old_records(90); // Mantener solo 90 días
        
        if ($deleted !== false) {
            error_log("BCV Dólar Tracker: Limpieza semanal completada - {$deleted} registros eliminados");
        } else {
            error_log('BCV Dólar Tracker: Error en limpieza semanal');
        }
    }
}
