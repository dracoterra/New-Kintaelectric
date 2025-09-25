<?php
/**
 * Clase para activación del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar la activación del plugin
 */
class WCVS_Activator {

    /**
     * Activar el plugin
     */
    public static function activate() {
        // Verificar dependencias
        if (!self::check_dependencies()) {
            wp_die(
                'WooCommerce Venezuela Suite requiere WooCommerce para funcionar correctamente. ' .
                'Por favor, instala y activa WooCommerce primero.',
                'Dependencias Faltantes',
                array('back_link' => true)
            );
        }

        // Crear tablas de base de datos
        self::create_database_tables();

        // Configurar opciones por defecto
        self::set_default_options();

        // Crear directorios necesarios
        self::create_directories();

        // Configurar cron jobs
        self::setup_cron_jobs();

        // Sincronizar con plugin BCV si está disponible
        self::sync_with_bcv_plugin();

        // Limpiar cache
        self::clear_cache();

        // Log de activación
        error_log('WooCommerce Venezuela Suite: Plugin activado correctamente');
    }

    /**
     * Verificar dependencias del plugin
     *
     * @return bool
     */
    private static function check_dependencies() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return false;
        }

        // Verificar versión mínima de WooCommerce
        if (version_compare(WC()->version, '3.0.0', '<')) {
            return false;
        }

        // Verificar versión mínima de WordPress
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            return false;
        }

        // Verificar versión mínima de PHP
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            return false;
        }

        return true;
    }

    /**
     * Crear tablas de base de datos necesarias
     */
    private static function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Tabla de configuración de módulos
        $table_modules = $wpdb->prefix . 'wcvs_modules';
        $sql_modules = "CREATE TABLE {$table_modules} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            module_key varchar(100) NOT NULL,
            module_name varchar(255) NOT NULL,
            module_description text,
            module_class varchar(255) NOT NULL,
            module_file varchar(500) NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            settings longtext,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY module_key (module_key),
            KEY is_active (is_active)
        ) {$charset_collate};";

        // Tabla de logs del sistema
        $table_logs = $wpdb->prefix . 'wcvs_logs';
        $sql_logs = "CREATE TABLE {$table_logs} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            log_level varchar(20) NOT NULL DEFAULT 'INFO',
            context varchar(100) NOT NULL DEFAULT '',
            message text NOT NULL,
            data longtext,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY log_level (log_level),
            KEY context (context),
            KEY user_id (user_id),
            KEY created_at (created_at),
            KEY created_at_level (created_at, log_level)
        ) {$charset_collate};";

        // Tabla de configuraciones del sistema
        $table_settings = $wpdb->prefix . 'wcvs_settings';
        $sql_settings = "CREATE TABLE {$table_settings} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext,
            setting_type varchar(50) NOT NULL DEFAULT 'string',
            is_autoload tinyint(1) NOT NULL DEFAULT 1,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key),
            KEY is_autoload (is_autoload)
        ) {$charset_collate};";

        // Tabla de reportes SENIAT
        $table_reports = $wpdb->prefix . 'wcvs_seniat_reports';
        $sql_reports = "CREATE TABLE {$table_reports} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            report_type varchar(50) NOT NULL,
            report_name varchar(255) NOT NULL,
            report_data longtext NOT NULL,
            report_period_start date NOT NULL,
            report_period_end date NOT NULL,
            generated_by bigint(20) unsigned DEFAULT NULL,
            file_path varchar(500) DEFAULT NULL,
            file_size bigint(20) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY report_type (report_type),
            KEY report_period_start (report_period_start),
            KEY report_period_end (report_period_end),
            KEY generated_by (generated_by),
            KEY created_at (created_at)
        ) {$charset_collate};";

        // Tabla de facturas electrónicas
        $table_invoices = $wpdb->prefix . 'wcvs_electronic_invoices';
        $sql_invoices = "CREATE TABLE {$table_invoices} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            invoice_number varchar(100) NOT NULL,
            invoice_data longtext NOT NULL,
            xml_data longtext,
            pdf_path varchar(500) DEFAULT NULL,
            seniat_status varchar(50) DEFAULT 'pending',
            seniat_response longtext,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY invoice_number (invoice_number),
            KEY order_id (order_id),
            KEY seniat_status (seniat_status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        // Ejecutar creación de tablas
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_modules);
        dbDelta($sql_logs);
        dbDelta($sql_settings);
        dbDelta($sql_reports);
        dbDelta($sql_invoices);

        // Verificar errores
        if (!empty($wpdb->last_error)) {
            error_log('WooCommerce Venezuela Suite: Error al crear tablas de base de datos: ' . $wpdb->last_error);
        } else {
            error_log('WooCommerce Venezuela Suite: Tablas de base de datos creadas correctamente');
        }
    }

    /**
     * Configurar opciones por defecto del plugin
     */
    private static function set_default_options() {
        // Configuración general
        $default_settings = array(
            'wcvs_general' => array(
                'plugin_version' => WCVS_VERSION,
                'activation_date' => current_time('mysql'),
                'first_activation' => true,
                'debug_mode' => false,
                'auto_updates' => true
            ),
            'wcvs_bcv' => array(
                'update_interval' => '2hours',
                'enabled' => true,
                'auto_sync' => true,
                'fallback_rate' => 36.5,
                'cache_duration' => 1800 // 30 minutos
            ),
            'wcvs_currency' => array(
                'base_currency' => 'USD',
                'display_currency' => 'VES',
                'conversion_enabled' => true,
                'display_both_currencies' => true,
                'currency_symbol_position' => 'after'
            ),
            'wcvs_taxes' => array(
                'iva_rate' => 16,
                'igtf_rate' => 3,
                'tax_inclusive' => true,
                'round_taxes' => true,
                'exempt_products' => array()
            ),
            'wcvs_payments' => array(
                'zelle_enabled' => true,
                'binance_enabled' => true,
                'pago_movil_enabled' => true,
                'transferencias_enabled' => true,
                'cash_deposit_enabled' => true
            ),
            'wcvs_shipping' => array(
                'mrw_enabled' => true,
                'zoom_enabled' => true,
                'tealca_enabled' => true,
                'local_delivery_enabled' => true,
                'pickup_enabled' => true
            ),
            'wcvs_seniat' => array(
                'company_rif' => '',
                'company_name' => '',
                'company_address' => '',
                'api_key' => '',
                'integration_enabled' => false,
                'auto_generate_invoices' => true
            ),
            'wcvs_notifications' => array(
                'bcv_rate_changes' => true,
                'payment_errors' => true,
                'low_stock' => true,
                'order_updates' => true,
                'fiscal_alerts' => true
            )
        );

        // Guardar configuraciones
        foreach ($default_settings as $key => $value) {
            add_option($key, $value);
        }

        // Configurar módulos por defecto
        $default_modules = array(
            'currency_manager' => array(
                'enabled' => true,
                'priority' => 1
            ),
            'payment_gateways' => array(
                'enabled' => true,
                'priority' => 2
            ),
            'shipping_methods' => array(
                'enabled' => true,
                'priority' => 3
            ),
            'tax_system' => array(
                'enabled' => true,
                'priority' => 4
            ),
            'electronic_billing' => array(
                'enabled' => true,
                'priority' => 5
            ),
            'seniat_reports' => array(
                'enabled' => true,
                'priority' => 6
            ),
            'price_display' => array(
                'enabled' => true,
                'priority' => 7
            ),
            'onboarding' => array(
                'enabled' => true,
                'priority' => 8
            ),
            'help_system' => array(
                'enabled' => true,
                'priority' => 9
            ),
            'notifications' => array(
                'enabled' => true,
                'priority' => 10
            )
        );

        add_option('wcvs_modules', $default_modules);

        error_log('WooCommerce Venezuela Suite: Opciones por defecto configuradas');
    }

    /**
     * Crear directorios necesarios
     */
    private static function create_directories() {
        $upload_dir = wp_upload_dir();
        $directories = array(
            $upload_dir['basedir'] . '/wcvs-exports/',
            $upload_dir['basedir'] . '/wcvs-invoices/',
            $upload_dir['basedir'] . '/wcvs-reports/',
            $upload_dir['basedir'] . '/wcvs-cache/',
            $upload_dir['basedir'] . '/wcvs-logs/'
        );

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);
                
                // Crear archivo .htaccess para protección
                $htaccess_content = "Order Deny,Allow\nDeny from all\n";
                file_put_contents($directory . '.htaccess', $htaccess_content);
                
                // Crear archivo index.php para protección
                file_put_contents($directory . 'index.php', '<?php // Silence is golden');
            }
        }

        error_log('WooCommerce Venezuela Suite: Directorios creados correctamente');
    }

    /**
     * Configurar cron jobs del plugin
     */
    private static function setup_cron_jobs() {
        // Cron para sincronización BCV
        if (!wp_next_scheduled('wcvs_bcv_sync')) {
            wp_schedule_event(time(), 'hourly', 'wcvs_bcv_sync');
        }

        // Cron para limpieza de cache
        if (!wp_next_scheduled('wcvs_cache_cleanup')) {
            wp_schedule_event(time(), 'daily', 'wcvs_cache_cleanup');
        }

        // Cron para limpieza de logs antiguos
        if (!wp_next_scheduled('wcvs_logs_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'wcvs_logs_cleanup');
        }

        // Cron para generación de reportes automáticos
        if (!wp_next_scheduled('wcvs_auto_reports')) {
            wp_schedule_event(time(), 'daily', 'wcvs_auto_reports');
        }

        error_log('WooCommerce Venezuela Suite: Cron jobs configurados');
    }

    /**
     * Sincronizar con plugin BCV si está disponible
     */
    private static function sync_with_bcv_plugin() {
        // Verificar si el plugin BCV está disponible
        if (class_exists('BCV_Dolar_Tracker')) {
            // Obtener configuración actual del plugin BCV
            $bcv_settings = get_option('bcv_cron_settings', array());
            
            if (!empty($bcv_settings)) {
                // Sincronizar configuración
                $wcvs_bcv_settings = get_option('wcvs_bcv', array());
                $wcvs_bcv_settings['bcv_plugin_available'] = true;
                $wcvs_bcv_settings['bcv_settings'] = $bcv_settings;
                update_option('wcvs_bcv', $wcvs_bcv_settings);

                // Obtener tasa actual
                $current_rate = BCV_Dolar_Tracker::get_rate();
                if ($current_rate && $current_rate > 0) {
                    update_option('wcvs_current_rate', $current_rate);
                    update_option('wcvs_rate_last_update', current_time('mysql'));
                }

                error_log('WooCommerce Venezuela Suite: Sincronización con plugin BCV completada');
            }
        } else {
            // Plugin BCV no disponible
            $wcvs_bcv_settings = get_option('wcvs_bcv', array());
            $wcvs_bcv_settings['bcv_plugin_available'] = false;
            update_option('wcvs_bcv', $wcvs_bcv_settings);

            error_log('WooCommerce Venezuela Suite: Plugin BCV no disponible');
        }
    }

    /**
     * Limpiar cache del sistema
     */
    private static function clear_cache() {
        // Limpiar cache de WordPress
        wp_cache_flush();

        // Limpiar transients del plugin
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wcvs_%' OR option_name LIKE '_transient_timeout_wcvs_%'"
        );

        // Limpiar cache de archivos
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/wcvs-cache/';
        if (file_exists($cache_dir)) {
            $files = glob($cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        error_log('WooCommerce Venezuela Suite: Cache limpiado');
    }

    /**
     * Verificar si la activación fue exitosa
     *
     * @return bool
     */
    public static function is_activation_successful() {
        // Verificar que las tablas existan
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'wcvs_modules',
            $wpdb->prefix . 'wcvs_logs',
            $wpdb->prefix . 'wcvs_settings',
            $wpdb->prefix . 'wcvs_seniat_reports',
            $wpdb->prefix . 'wcvs_electronic_invoices'
        );

        foreach ($tables as $table) {
            $result = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
            if ($result !== $table) {
                return false;
            }
        }

        // Verificar que las opciones existan
        $required_options = array(
            'wcvs_general',
            'wcvs_bcv',
            'wcvs_currency',
            'wcvs_taxes',
            'wcvs_payments',
            'wcvs_shipping',
            'wcvs_seniat',
            'wcvs_notifications',
            'wcvs_modules'
        );

        foreach ($required_options as $option) {
            if (get_option($option) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener información de activación
     *
     * @return array
     */
    public static function get_activation_info() {
        return array(
            'version' => WCVS_VERSION,
            'activation_date' => get_option('wcvs_general')['activation_date'] ?? 'N/A',
            'first_activation' => get_option('wcvs_general')['first_activation'] ?? false,
            'bcv_plugin_available' => get_option('wcvs_bcv')['bcv_plugin_available'] ?? false,
            'modules_count' => count(get_option('wcvs_modules', array())),
            'tables_created' => self::count_database_tables(),
            'directories_created' => self::count_directories(),
            'cron_jobs_setup' => self::count_cron_jobs()
        );
    }

    /**
     * Contar tablas de base de datos creadas
     *
     * @return int
     */
    private static function count_database_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'wcvs_modules',
            $wpdb->prefix . 'wcvs_logs',
            $wpdb->prefix . 'wcvs_settings',
            $wpdb->prefix . 'wcvs_seniat_reports',
            $wpdb->prefix . 'wcvs_electronic_invoices'
        );

        $count = 0;
        foreach ($tables as $table) {
            $result = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
            if ($result === $table) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Contar directorios creados
     *
     * @return int
     */
    private static function count_directories() {
        $upload_dir = wp_upload_dir();
        $directories = array(
            $upload_dir['basedir'] . '/wcvs-exports/',
            $upload_dir['basedir'] . '/wcvs-invoices/',
            $upload_dir['basedir'] . '/wcvs-reports/',
            $upload_dir['basedir'] . '/wcvs-cache/',
            $upload_dir['basedir'] . '/wcvs-logs/'
        );

        $count = 0;
        foreach ($directories as $directory) {
            if (file_exists($directory)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Contar cron jobs configurados
     *
     * @return int
     */
    private static function count_cron_jobs() {
        $cron_jobs = array(
            'wcvs_bcv_sync',
            'wcvs_cache_cleanup',
            'wcvs_logs_cleanup',
            'wcvs_auto_reports'
        );

        $count = 0;
        foreach ($cron_jobs as $job) {
            if (wp_next_scheduled($job)) {
                $count++;
            }
        }

        return $count;
    }
}
