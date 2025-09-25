<?php
/**
 * Sistema de configuración del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar configuraciones del plugin
 */
class WCVS_Settings {

    /**
     * Configuraciones del plugin
     *
     * @var array
     */
    private $settings = array();

    /**
     * Configuraciones por defecto
     *
     * @var array
     */
    private $default_settings = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_default_settings();
        $this->load_settings();
        $this->init_hooks();
    }

    /**
     * Inicializar configuraciones por defecto
     */
    private function init_default_settings() {
        $this->default_settings = array(
            'general' => array(
                'plugin_version' => WCVS_VERSION,
                'activation_date' => current_time('mysql'),
                'first_activation' => true,
                'debug_mode' => false,
                'auto_updates' => true,
                'log_level' => 'INFO',
                'cache_enabled' => true,
                'cache_duration' => 1800 // 30 minutos
            ),
            'bcv' => array(
                'update_interval' => '2hours',
                'enabled' => true,
                'auto_sync' => true,
                'fallback_rate' => 36.5,
                'cache_duration' => 1800,
                'api_timeout' => 30,
                'retry_attempts' => 3,
                'notification_on_change' => true,
                'change_threshold' => 0.01 // 1%
            ),
            'currency' => array(
                'base_currency' => 'USD',
                'display_currency' => 'VES',
                'conversion_enabled' => true,
                'display_both_currencies' => true,
                'currency_symbol_position' => 'after',
                'decimal_places' => 2,
                'thousand_separator' => '.',
                'decimal_separator' => ',',
                'auto_update_prices' => true,
                'price_cache_duration' => 3600 // 1 hora
            ),
            'taxes' => array(
                'iva_rate' => 16,
                'igtf_rate' => 3,
                'tax_inclusive' => true,
                'round_taxes' => true,
                'exempt_products' => array(),
                'tax_display' => 'both',
                'auto_calculate' => true,
                'apply_igtf_usd_only' => true,
                'tax_rounding_method' => 'round'
            ),
            'payments' => array(
                'zelle_enabled' => true,
                'binance_enabled' => true,
                'pago_movil_enabled' => true,
                'transferencias_enabled' => true,
                'cash_deposit_enabled' => true,
                'mercantil_enabled' => false,
                'banesco_enabled' => false,
                'bbva_enabled' => false,
                'cashea_enabled' => false,
                'pagoflash_enabled' => false,
                'min_amount_usd' => 1,
                'max_amount_usd' => 10000,
                'min_amount_ves' => 50,
                'max_amount_ves' => 500000
            ),
            'shipping' => array(
                'mrw_enabled' => true,
                'zoom_enabled' => true,
                'tealca_enabled' => true,
                'local_delivery_enabled' => true,
                'pickup_enabled' => true,
                'auto_calculate_costs' => true,
                'include_insurance' => true,
                'insurance_rate' => 0.5, // 0.5%
                'dimensional_weight_factor' => 5000,
                'free_shipping_threshold' => 100,
                'free_shipping_threshold_currency' => 'USD'
            ),
            'seniat' => array(
                'company_rif' => '',
                'company_name' => '',
                'company_address' => '',
                'company_phone' => '',
                'company_email' => '',
                'api_key' => '',
                'integration_enabled' => false,
                'auto_generate_invoices' => true,
                'invoice_prefix' => 'FAC',
                'invoice_number_start' => 1,
                'send_to_seniat' => false,
                'backup_invoices' => true,
                'invoice_retention_days' => 365
            ),
            'reports' => array(
                'auto_generate_daily' => false,
                'auto_generate_weekly' => true,
                'auto_generate_monthly' => true,
                'report_retention_days' => 730, // 2 años
                'export_formats' => array('excel', 'csv', 'pdf'),
                'email_reports' => false,
                'email_recipients' => array(),
                'report_schedule_time' => '09:00'
            ),
            'price_display' => array(
                'style' => 'minimalist',
                'show_both_currencies' => true,
                'currency_switcher_enabled' => true,
                'switcher_style' => 'buttons',
                'contexts' => array(
                    'single_product' => true,
                    'shop_loop' => true,
                    'cart' => true,
                    'checkout' => true,
                    'widget' => true,
                    'footer' => false
                ),
                'animation_enabled' => true,
                'loading_text' => 'Calculando...',
                'error_text' => 'Error al obtener precio'
            ),
            'notifications' => array(
                'bcv_rate_changes' => true,
                'payment_errors' => true,
                'low_stock' => true,
                'order_updates' => true,
                'fiscal_alerts' => true,
                'system_errors' => true,
                'email_notifications' => true,
                'admin_email' => get_option('admin_email'),
                'notification_frequency' => 'immediate'
            ),
            'onboarding' => array(
                'completed' => false,
                'current_step' => 1,
                'steps_completed' => array(),
                'skip_onboarding' => false,
                'show_welcome_message' => true,
                'auto_detect_settings' => true
            ),
            'help' => array(
                'contextual_help_enabled' => true,
                'tooltips_enabled' => true,
                'video_tutorials_enabled' => true,
                'documentation_language' => 'es',
                'support_email' => 'soporte@kinta-electric.com',
                'faq_enabled' => true,
                'search_enabled' => true
            )
        );
    }

    /**
     * Cargar configuraciones desde la base de datos
     */
    private function load_settings() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_settings';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            // Si la tabla no existe, usar configuraciones por defecto
            $this->settings = $this->default_settings;
            return;
        }

        // Cargar configuraciones desde la base de datos
        $results = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
        
        // Inicializar con valores por defecto
        $this->settings = $this->default_settings;
        
        // Sobrescribir con valores de la base de datos
        foreach ($results as $row) {
            $key_parts = explode('_', $row['setting_key'], 2);
            if (count($key_parts) === 2) {
                $section = $key_parts[0];
                $key = $key_parts[1];
                
                if (isset($this->settings[$section])) {
                    $value = maybe_unserialize($row['setting_value']);
                    $this->settings[$section][$key] = $value;
                }
            }
        }
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_wcvs_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wcvs_reset_settings', array($this, 'ajax_reset_settings'));
        add_action('wp_ajax_wcvs_export_settings', array($this, 'ajax_export_settings'));
        add_action('wp_ajax_wcvs_import_settings', array($this, 'ajax_import_settings'));
    }

    /**
     * Registrar configuraciones con WordPress
     */
    public function register_settings() {
        foreach ($this->default_settings as $section => $settings) {
            register_setting(
                'wcvs_settings_' . $section,
                'wcvs_' . $section,
                array($this, 'sanitize_settings')
            );
        }
    }

    /**
     * Obtener una configuración específica
     *
     * @param string $section Sección de configuración
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function get($section, $key = null, $default = null) {
        if ($key === null) {
            return isset($this->settings[$section]) ? $this->settings[$section] : $default;
        }

        if (isset($this->settings[$section][$key])) {
            return $this->settings[$section][$key];
        }

        return $default;
    }

    /**
     * Establecer una configuración específica
     *
     * @param string $section Sección de configuración
     * @param string $key Clave de configuración
     * @param mixed $value Valor a establecer
     */
    public function set($section, $key, $value) {
        if (!isset($this->settings[$section])) {
            $this->settings[$section] = array();
        }

        $this->settings[$section][$key] = $value;
        $this->save_setting($section, $key, $value);
    }

    /**
     * Establecer múltiples configuraciones
     *
     * @param string $section Sección de configuración
     * @param array $settings Configuraciones a establecer
     */
    public function set_multiple($section, $settings) {
        if (!isset($this->settings[$section])) {
            $this->settings[$section] = array();
        }

        foreach ($settings as $key => $value) {
            $this->settings[$section][$key] = $value;
            $this->save_setting($section, $key, $value);
        }
    }

    /**
     * Obtener todas las configuraciones
     *
     * @return array
     */
    public function get_all() {
        return $this->settings;
    }

    /**
     * Establecer todas las configuraciones
     *
     * @param array $settings Configuraciones completas
     */
    public function set_all($settings) {
        $this->settings = $settings;
        $this->save_all_settings();
    }

    /**
     * Restablecer configuraciones a valores por defecto
     *
     * @param string $section Sección específica (opcional)
     */
    public function reset($section = null) {
        if ($section === null) {
            $this->settings = $this->default_settings;
            $this->save_all_settings();
        } else {
            if (isset($this->default_settings[$section])) {
                $this->settings[$section] = $this->default_settings[$section];
                $this->save_section_settings($section);
            }
        }
    }

    /**
     * Establecer valores por defecto
     */
    public function set_default_values() {
        $this->settings = $this->default_settings;
        $this->save_all_settings();
    }

    /**
     * Verificar si una configuración existe
     *
     * @param string $section Sección de configuración
     * @param string $key Clave de configuración
     * @return bool
     */
    public function has($section, $key = null) {
        if ($key === null) {
            return isset($this->settings[$section]);
        }

        return isset($this->settings[$section][$key]);
    }

    /**
     * Eliminar una configuración
     *
     * @param string $section Sección de configuración
     * @param string $key Clave de configuración
     */
    public function delete($section, $key) {
        if (isset($this->settings[$section][$key])) {
            unset($this->settings[$section][$key]);
            $this->delete_setting($section, $key);
        }
    }

    /**
     * Sanitizar configuraciones
     *
     * @param array $input Configuraciones de entrada
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        foreach ($input as $key => $value) {
            // Sanitizar según el tipo de dato
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } elseif (is_numeric($value)) {
                $sanitized[$key] = floatval($value);
            } elseif (is_bool($value)) {
                $sanitized[$key] = (bool) $value;
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }

        return $sanitized;
    }

    /**
     * Guardar una configuración en la base de datos
     *
     * @param string $section Sección de configuración
     * @param string $key Clave de configuración
     * @param mixed $value Valor a guardar
     */
    private function save_setting($section, $key, $value) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_settings';
        $setting_key = $section . '_' . $key;
        
        $data = array(
            'setting_key' => $setting_key,
            'setting_value' => maybe_serialize($value),
            'setting_type' => $this->get_setting_type($value),
            'is_autoload' => 1
        );

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE setting_key = %s",
            $setting_key
        ));

        if ($existing) {
            $wpdb->update($table_name, $data, array('setting_key' => $setting_key));
        } else {
            $wpdb->insert($table_name, $data);
        }
    }

    /**
     * Guardar todas las configuraciones en la base de datos
     */
    private function save_all_settings() {
        foreach ($this->settings as $section => $section_settings) {
            $this->save_section_settings($section);
        }
    }

    /**
     * Guardar configuraciones de una sección
     *
     * @param string $section Sección de configuración
     */
    private function save_section_settings($section) {
        foreach ($this->settings[$section] as $key => $value) {
            $this->save_setting($section, $key, $value);
        }
    }

    /**
     * Eliminar una configuración de la base de datos
     *
     * @param string $section Sección de configuración
     * @param string $key Clave de configuración
     */
    private function delete_setting($section, $key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_settings';
        $setting_key = $section . '_' . $key;
        
        $wpdb->delete($table_name, array('setting_key' => $setting_key));
    }

    /**
     * Obtener tipo de configuración
     *
     * @param mixed $value Valor de la configuración
     * @return string
     */
    private function get_setting_type($value) {
        if (is_array($value)) {
            return 'array';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_bool($value)) {
            return 'boolean';
        } else {
            return 'string';
        }
    }

    /**
     * Guardar configuraciones via AJAX
     */
    public function ajax_save_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $section = sanitize_text_field($_POST['section']);
        $settings = $_POST['settings'];

        // Sanitizar configuraciones
        $sanitized_settings = $this->sanitize_settings($settings);

        // Guardar configuraciones
        $this->set_multiple($section, $sanitized_settings);

        wp_send_json_success(array(
            'message' => 'Configuraciones guardadas correctamente',
            'settings' => $sanitized_settings
        ));
    }

    /**
     * Restablecer configuraciones via AJAX
     */
    public function ajax_reset_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $section = sanitize_text_field($_POST['section']);
        $this->reset($section);

        wp_send_json_success(array(
            'message' => 'Configuraciones restablecidas correctamente',
            'settings' => $this->get($section)
        ));
    }

    /**
     * Exportar configuraciones via AJAX
     */
    public function ajax_export_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $export_data = array(
            'version' => WCVS_VERSION,
            'export_date' => current_time('mysql'),
            'settings' => $this->get_all()
        );

        $filename = 'wcvs-settings-' . date('Y-m-d-H-i-s') . '.json';
        $filepath = wp_upload_dir()['basedir'] . '/wcvs-exports/' . $filename;

        file_put_contents($filepath, json_encode($export_data, JSON_PRETTY_PRINT));

        wp_send_json_success(array(
            'message' => 'Configuraciones exportadas correctamente',
            'download_url' => wp_upload_dir()['baseurl'] . '/wcvs-exports/' . $filename
        ));
    }

    /**
     * Importar configuraciones via AJAX
     */
    public function ajax_import_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        if (!isset($_FILES['settings_file'])) {
            wp_send_json_error('Archivo no proporcionado');
        }

        $file = $_FILES['settings_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('Error al subir archivo');
        }

        $content = file_get_contents($file['tmp_name']);
        $import_data = json_decode($content, true);

        if (!$import_data || !isset($import_data['settings'])) {
            wp_send_json_error('Archivo de configuración inválido');
        }

        // Validar versión
        if (isset($import_data['version']) && version_compare($import_data['version'], WCVS_VERSION, '>')) {
            wp_send_json_error('Archivo de configuración incompatible con la versión actual');
        }

        // Importar configuraciones
        $this->set_all($import_data['settings']);

        wp_send_json_success(array(
            'message' => 'Configuraciones importadas correctamente',
            'imported_count' => count($import_data['settings'])
        ));
    }

    /**
     * Obtener estadísticas de configuraciones
     *
     * @return array
     */
    public function get_settings_stats() {
        $total_settings = 0;
        $configured_settings = 0;

        foreach ($this->default_settings as $section => $settings) {
            $total_settings += count($settings);
            foreach ($settings as $key => $default_value) {
                $current_value = $this->get($section, $key);
                if ($current_value !== $default_value) {
                    $configured_settings++;
                }
            }
        }

        return array(
            'total_settings' => $total_settings,
            'configured_settings' => $configured_settings,
            'default_settings' => $total_settings - $configured_settings,
            'configuration_percentage' => $total_settings > 0 ? round(($configured_settings / $total_settings) * 100, 2) : 0
        );
    }

    /**
     * Inicializar el sistema de configuración
     */
    public function init() {
        // Verificar si es la primera inicialización
        if (!$this->has('general', 'plugin_version')) {
            $this->set_default_values();
        }

        // Actualizar versión si es necesario
        $current_version = $this->get('general', 'plugin_version');
        if (version_compare($current_version, WCVS_VERSION, '<')) {
            $this->set('general', 'plugin_version', WCVS_VERSION);
            $this->set('general', 'last_update', current_time('mysql'));
        }
    }
}
