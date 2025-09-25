<?php
/**
 * Integración con el plugin BCV Dólar Tracker
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar la integración con el plugin BCV
 */
class WCVS_BCV_Integration {

    /**
     * Instancia del detector BCV
     *
     * @var WCVS_BCV_Detector
     */
    private $detector;

    /**
     * Instancia del gestor BCV
     *
     * @var WCVS_BCV_Manager
     */
    private $manager;

    /**
     * Instancia del sistema de fallback
     *
     * @var WCVS_Rate_Fallback
     */
    private $fallback;

    /**
     * Constructor
     */
    public function __construct() {
        $this->detector = new WCVS_BCV_Detector();
        $this->manager = new WCVS_BCV_Manager();
        $this->fallback = new WCVS_Rate_Fallback();
        
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para sincronización automática
        add_action('wvp_bcv_rate_updated', array($this, 'handle_rate_update'), 10, 2);
        
        // Hook para cuando se activa el plugin
        add_action('wcvs_plugin_activated', array($this, 'sync_on_activation'));
        
        // Hook para sincronización periódica
        add_action('wcvs_bcv_sync', array($this, 'periodic_sync'));
        
        // Hook para limpieza de cache
        add_action('wcvs_cache_cleanup', array($this, 'cleanup_cache'));
    }

    /**
     * Inicializar la integración
     */
    public function init() {
        // Verificar disponibilidad del plugin BCV
        if ($this->detector->is_available()) {
            $this->sync_settings();
            $this->sync_current_rate();
            $this->setup_periodic_sync();
        } else {
            $this->setup_fallback_system();
        }
    }

    /**
     * Verificar si el plugin BCV está disponible
     *
     * @return bool
     */
    public function is_available() {
        return $this->detector->is_available();
    }

    /**
     * Obtener información del plugin BCV
     *
     * @return array
     */
    public function get_bcv_info() {
        return $this->detector->get_info();
    }

    /**
     * Sincronizar configuraciones con el plugin BCV
     */
    public function sync_settings() {
        if (!$this->detector->is_available()) {
            return false;
        }

        return $this->manager->sync_settings();
    }

    /**
     * Sincronizar tasa actual
     */
    public function sync_current_rate() {
        if (!$this->detector->is_available()) {
            return $this->fallback->get_current_rate();
        }

        $rate = $this->manager->get_current_rate();
        if ($rate && $rate['rate'] > 0) {
            $this->update_wcvs_rate($rate);
            return $rate;
        }

        return $this->fallback->get_current_rate();
    }

    /**
     * Manejar actualización de tasa BCV
     *
     * @param float $new_rate Nueva tasa
     * @param float $old_rate Tasa anterior
     */
    public function handle_rate_update($new_rate, $old_rate) {
        // Actualizar cache de WooCommerce Venezuela Suite
        $this->update_wcvs_rate(array(
            'rate' => $new_rate,
            'source' => 'bcv_plugin',
            'timestamp' => current_time('mysql'),
            'reliable' => true
        ));

        // Invalidar cache de conversiones
        delete_transient('wcvs_conversion_cache');

        // Notificar a módulos activos
        do_action('wcvs_bcv_rate_updated', $new_rate, $old_rate);

        // Log de sincronización
        error_log("WCVS: Tasa BCV sincronizada - {$old_rate} → {$new_rate}");
    }

    /**
     * Sincronización al activar el plugin
     */
    public function sync_on_activation() {
        if ($this->detector->is_available()) {
            $this->sync_settings();
            $this->sync_current_rate();
        }
    }

    /**
     * Sincronización periódica
     */
    public function periodic_sync() {
        if ($this->detector->is_available()) {
            $this->sync_current_rate();
        }
    }

    /**
     * Limpiar cache
     */
    public function cleanup_cache() {
        delete_transient('wcvs_conversion_cache');
        delete_transient('wcvs_bcv_status');
        delete_transient('wcvs_rate_info');
    }

    /**
     * Configurar sincronización periódica
     */
    private function setup_periodic_sync() {
        if (!wp_next_scheduled('wcvs_bcv_sync')) {
            wp_schedule_event(time(), 'hourly', 'wcvs_bcv_sync');
        }
    }

    /**
     * Configurar sistema de fallback
     */
    private function setup_fallback_system() {
        // Configurar fallback cuando BCV no esté disponible
        $fallback_rate = get_option('wcvs_bcv')['fallback_rate'] ?? 36.5;
        
        $this->update_wcvs_rate(array(
            'rate' => $fallback_rate,
            'source' => 'fallback',
            'timestamp' => current_time('mysql'),
            'reliable' => false
        ));
    }

    /**
     * Actualizar tasa en WooCommerce Venezuela Suite
     *
     * @param array $rate_data Datos de la tasa
     */
    private function update_wcvs_rate($rate_data) {
        update_option('wcvs_current_rate', $rate_data['rate']);
        update_option('wcvs_rate_last_update', $rate_data['timestamp']);
        update_option('wcvs_rate_source', $rate_data['source']);
        update_option('wcvs_rate_reliable', $rate_data['reliable']);
    }

    /**
     * Obtener tasa actual
     *
     * @return array
     */
    public function get_current_rate() {
        if ($this->detector->is_available()) {
            return $this->manager->get_current_rate();
        }

        return $this->fallback->get_current_rate();
    }

    /**
     * Obtener estado de la integración
     *
     * @return array
     */
    public function get_integration_status() {
        return array(
            'bcv_available' => $this->detector->is_available(),
            'bcv_info' => $this->detector->get_info(),
            'current_rate' => $this->get_current_rate(),
            'last_sync' => get_option('wcvs_rate_last_update'),
            'sync_enabled' => wp_next_scheduled('wcvs_bcv_sync') !== false,
            'fallback_active' => !$this->detector->is_available()
        );
    }
}

/**
 * Clase para detectar el plugin BCV
 */
class WCVS_BCV_Detector {

    /**
     * Verificar si el plugin BCV está disponible
     *
     * @return bool
     */
    public static function is_available() {
        return class_exists('BCV_Dolar_Tracker') && 
               class_exists('BCV_Database') && 
               class_exists('BCV_Cron');
    }

    /**
     * Obtener versión del plugin BCV
     *
     * @return string|false
     */
    public static function get_version() {
        if (defined('BCV_DOLAR_TRACKER_VERSION')) {
            return BCV_DOLAR_TRACKER_VERSION;
        }
        return false;
    }

    /**
     * Verificar si la tabla BCV existe
     *
     * @return bool
     */
    public static function check_table_exists() {
        if (!self::is_available()) {
            return false;
        }

        $database = new BCV_Database();
        return $database->table_exists();
    }

    /**
     * Obtener información completa del plugin BCV
     *
     * @return array
     */
    public function get_info() {
        $info = array(
            'available' => self::is_available(),
            'version' => self::get_version(),
            'table_exists' => self::check_table_exists(),
            'cron_active' => false,
            'last_update' => null,
            'current_rate' => null,
            'total_records' => 0
        );

        if ($info['available']) {
            // Verificar estado del cron
            $info['cron_active'] = wp_next_scheduled('bcv_scrape_dollar_rate') !== false;

            // Obtener información de la base de datos
            if ($info['table_exists']) {
                $database = new BCV_Database();
                $stats = $database->get_price_stats();
                
                $info['last_update'] = $stats['last_date'];
                $info['current_rate'] = $stats['last_price'];
                $info['total_records'] = $stats['total_records'];
            }

            // Obtener configuración del cron
            $cron_settings = get_option('bcv_cron_settings', array());
            $info['cron_settings'] = $cron_settings;
        }

        return $info;
    }
}

/**
 * Clase para gestionar la sincronización con BCV
 */
class WCVS_BCV_Manager {

    /**
     * Sincronizar configuraciones
     *
     * @return bool
     */
    public function sync_settings() {
        if (!WCVS_BCV_Detector::is_available()) {
            return false;
        }

        // Obtener configuración del usuario desde WooCommerce Venezuela Suite
        $wcvs_settings = get_option('wcvs_bcv', array());
        
        if (empty($wcvs_settings)) {
            return false;
        }

        // Convertir configuración a formato BCV
        $bcv_settings = $this->convert_to_bcv_format($wcvs_settings);

        // Aplicar configuración al plugin BCV
        $cron = new BCV_Cron();
        $result = $cron->setup_cron($bcv_settings);

        if ($result) {
            // Guardar configuración sincronizada
            update_option('wcvs_bcv_synced', true);
            update_option('wcvs_bcv_sync_date', current_time('mysql'));
        }

        return $result;
    }

    /**
     * Convertir configuración a formato BCV
     *
     * @param array $wcvs_settings Configuración de WCVS
     * @return array
     */
    private function convert_to_bcv_format($wcvs_settings) {
        $interval_map = array(
            '30min' => array('hours' => 0, 'minutes' => 30, 'seconds' => 0),
            '1hour' => array('hours' => 1, 'minutes' => 0, 'seconds' => 0),
            '2hours' => array('hours' => 2, 'minutes' => 0, 'seconds' => 0),
            '6hours' => array('hours' => 6, 'minutes' => 0, 'seconds' => 0),
            '12hours' => array('hours' => 12, 'minutes' => 0, 'seconds' => 0),
            '24hours' => array('hours' => 24, 'minutes' => 0, 'seconds' => 0)
        );

        $interval = $wcvs_settings['update_interval'] ?? '2hours';
        $bcv_interval = $interval_map[$interval] ?? $interval_map['2hours'];

        return array(
            'enabled' => $wcvs_settings['enabled'] ?? true,
            'hours' => $bcv_interval['hours'],
            'minutes' => $bcv_interval['minutes'],
            'seconds' => $bcv_interval['seconds']
        );
    }

    /**
     * Obtener tasa actual del plugin BCV
     *
     * @return array|false
     */
    public function get_current_rate() {
        if (!WCVS_BCV_Detector::is_available()) {
            return false;
        }

        // Prioridad 1: Método estático del plugin BCV
        $rate = BCV_Dolar_Tracker::get_rate();
        if ($rate && $rate > 0) {
            return array(
                'rate' => $rate,
                'source' => 'bcv_plugin',
                'timestamp' => current_time('mysql'),
                'reliable' => true
            );
        }

        // Prioridad 2: Base de datos BCV directa
        if (WCVS_BCV_Detector::check_table_exists()) {
            $database = new BCV_Database();
            $rate = $database->get_latest_price();
            if ($rate && $rate > 0) {
                return array(
                    'rate' => $rate,
                    'source' => 'bcv_database',
                    'timestamp' => current_time('mysql'),
                    'reliable' => true
                );
            }
        }

        return false;
    }
}

/**
 * Clase para sistema de fallback de tasas
 */
class WCVS_Rate_Fallback {

    /**
     * Obtener tasa usando sistema de fallback
     *
     * @return array|false
     */
    public function get_current_rate() {
        // Prioridad 1: Opción WVP (fallback)
        $wvp_rate = get_option('wvp_bcv_rate', 0);
        if ($wvp_rate && $wvp_rate > 0) {
            return array(
                'rate' => $wvp_rate,
                'source' => 'wvp_fallback',
                'timestamp' => current_time('mysql'),
                'reliable' => false
            );
        }

        // Prioridad 2: Tasa de fallback configurada
        $fallback_rate = get_option('wcvs_bcv')['fallback_rate'] ?? 36.5;
        if ($fallback_rate && $fallback_rate > 0) {
            return array(
                'rate' => $fallback_rate,
                'source' => 'configured_fallback',
                'timestamp' => current_time('mysql'),
                'reliable' => false
            );
        }

        // Prioridad 3: Scraping directo (último recurso)
        return $this->scrape_rate_directly();
    }

    /**
     * Scraping directo como último recurso
     *
     * @return array|false
     */
    private function scrape_rate_directly() {
        if (!WCVS_BCV_Detector::is_available()) {
            return false;
        }

        $scraper = new BCV_Scraper();
        $rate = $scraper->scrape_bcv_rate();

        if ($rate && $rate > 0) {
            return array(
                'rate' => $rate,
                'source' => 'direct_scraping',
                'timestamp' => current_time('mysql'),
                'reliable' => false
            );
        }

        return false;
    }
}
