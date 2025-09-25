<?php
/**
 * Clase para desactivación del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar la desactivación del plugin
 */
class WCVS_Deactivator {

    /**
     * Desactivar el plugin
     */
    public static function deactivate() {
        // Limpiar cron jobs
        self::clear_cron_jobs();

        // Limpiar cache
        self::clear_cache();

        // Limpiar transients
        self::clear_transients();

        // Desactivar módulos
        self::deactivate_modules();

        // Limpiar rewrite rules
        flush_rewrite_rules();

        // Log de desactivación
        error_log('WooCommerce Venezuela Suite: Plugin desactivado correctamente');
    }

    /**
     * Limpiar cron jobs del plugin
     */
    private static function clear_cron_jobs() {
        $cron_jobs = array(
            'wcvs_bcv_sync',
            'wcvs_cache_cleanup',
            'wcvs_logs_cleanup',
            'wcvs_auto_reports'
        );

        foreach ($cron_jobs as $job) {
            wp_clear_scheduled_hook($job);
        }

        error_log('WooCommerce Venezuela Suite: Cron jobs limpiados');
    }

    /**
     * Limpiar cache del sistema
     */
    private static function clear_cache() {
        // Limpiar cache de WordPress
        wp_cache_flush();

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
     * Limpiar transients del plugin
     */
    private static function clear_transients() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wcvs_%' OR option_name LIKE '_transient_timeout_wcvs_%'"
        );

        error_log('WooCommerce Venezuela Suite: Transients limpiados');
    }

    /**
     * Desactivar módulos del plugin
     */
    private static function deactivate_modules() {
        // Obtener módulos activos
        $modules = get_option('wcvs_modules', array());
        
        foreach ($modules as $module_key => $module_data) {
            if (isset($module_data['enabled']) && $module_data['enabled']) {
                // Desactivar módulo
                $modules[$module_key]['enabled'] = false;
                
                // Ejecutar desactivación específica del módulo
                self::deactivate_specific_module($module_key);
            }
        }

        // Guardar configuración actualizada
        update_option('wcvs_modules', $modules);

        error_log('WooCommerce Venezuela Suite: Módulos desactivados');
    }

    /**
     * Desactivar módulo específico
     *
     * @param string $module_key Clave del módulo
     */
    private static function deactivate_specific_module($module_key) {
        switch ($module_key) {
            case 'currency_manager':
                self::deactivate_currency_manager();
                break;
            case 'payment_gateways':
                self::deactivate_payment_gateways();
                break;
            case 'shipping_methods':
                self::deactivate_shipping_methods();
                break;
            case 'tax_system':
                self::deactivate_tax_system();
                break;
            case 'electronic_billing':
                self::deactivate_electronic_billing();
                break;
            case 'seniat_reports':
                self::deactivate_seniat_reports();
                break;
            case 'price_display':
                self::deactivate_price_display();
                break;
            case 'onboarding':
                self::deactivate_onboarding();
                break;
            case 'help_system':
                self::deactivate_help_system();
                break;
            case 'notifications':
                self::deactivate_notifications();
                break;
        }
    }

    /**
     * Desactivar gestor de moneda
     */
    private static function deactivate_currency_manager() {
        // Limpiar cache de conversiones
        delete_transient('wcvs_conversion_cache');
        
        // Limpiar hooks de conversión
        remove_action('woocommerce_product_get_price', 'wcvs_convert_price');
        remove_action('woocommerce_product_get_regular_price', 'wcvs_convert_price');
        remove_action('woocommerce_product_get_sale_price', 'wcvs_convert_price');
    }

    /**
     * Desactivar pasarelas de pago
     */
    private static function deactivate_payment_gateways() {
        // Desactivar pasarelas de pago personalizadas
        $gateways = array(
            'wcvs_zelle',
            'wcvs_binance',
            'wcvs_pago_movil',
            'wcvs_transferencias',
            'wcvs_cash_deposit'
        );

        foreach ($gateways as $gateway) {
            remove_action('woocommerce_payment_gateways', 'wcvs_add_payment_gateway');
        }
    }

    /**
     * Desactivar métodos de envío
     */
    private static function deactivate_shipping_methods() {
        // Desactivar métodos de envío personalizados
        $methods = array(
            'wcvs_mrw',
            'wcvs_zoom',
            'wcvs_tealca',
            'wcvs_local_delivery',
            'wcvs_pickup'
        );

        foreach ($methods as $method) {
            remove_action('woocommerce_shipping_methods', 'wcvs_add_shipping_method');
        }
    }

    /**
     * Desactivar sistema fiscal
     */
    private static function deactivate_tax_system() {
        // Limpiar hooks de impuestos
        remove_action('woocommerce_calculate_totals', 'wcvs_calculate_taxes');
        remove_action('woocommerce_cart_calculate_fees', 'wcvs_calculate_igtf');
    }

    /**
     * Desactivar facturación electrónica
     */
    private static function deactivate_electronic_billing() {
        // Limpiar hooks de facturación
        remove_action('woocommerce_order_status_completed', 'wcvs_generate_electronic_invoice');
        remove_action('woocommerce_order_status_processing', 'wcvs_generate_electronic_invoice');
    }

    /**
     * Desactivar reportes SENIAT
     */
    private static function deactivate_seniat_reports() {
        // Limpiar hooks de reportes
        remove_action('woocommerce_order_status_completed', 'wcvs_generate_seniat_report');
        remove_action('wcvs_daily_report', 'wcvs_generate_daily_report');
    }

    /**
     * Desactivar visualización de precios
     */
    private static function deactivate_price_display() {
        // Limpiar hooks de visualización
        remove_action('woocommerce_single_product_summary', 'wcvs_display_price_switcher');
        remove_action('woocommerce_after_shop_loop_item_title', 'wcvs_display_price_switcher');
    }

    /**
     * Desactivar onboarding
     */
    private static function deactivate_onboarding() {
        // Limpiar hooks de onboarding
        remove_action('admin_notices', 'wcvs_onboarding_notice');
        remove_action('wp_ajax_wcvs_complete_onboarding', 'wcvs_complete_onboarding');
    }

    /**
     * Desactivar sistema de ayuda
     */
    private static function deactivate_help_system() {
        // Limpiar hooks de ayuda
        remove_action('admin_menu', 'wcvs_add_help_menu');
        remove_action('contextual_help', 'wcvs_add_contextual_help');
    }

    /**
     * Desactivar sistema de notificaciones
     */
    private static function deactivate_notifications() {
        // Limpiar hooks de notificaciones
        remove_action('wcvs_bcv_rate_updated', 'wcvs_send_rate_notification');
        remove_action('woocommerce_order_status_changed', 'wcvs_send_order_notification');
    }

    /**
     * Verificar si la desactivación fue exitosa
     *
     * @return bool
     */
    public static function is_deactivation_successful() {
        // Verificar que los cron jobs estén limpiados
        $cron_jobs = array(
            'wcvs_bcv_sync',
            'wcvs_cache_cleanup',
            'wcvs_logs_cleanup',
            'wcvs_auto_reports'
        );

        foreach ($cron_jobs as $job) {
            if (wp_next_scheduled($job)) {
                return false;
            }
        }

        // Verificar que los transients estén limpiados
        global $wpdb;
        $transients = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_wcvs_%'"
        );

        if ($transients > 0) {
            return false;
        }

        return true;
    }

    /**
     * Obtener información de desactivación
     *
     * @return array
     */
    public static function get_deactivation_info() {
        return array(
            'cron_jobs_cleared' => self::count_cleared_cron_jobs(),
            'transients_cleared' => self::count_cleared_transients(),
            'modules_deactivated' => self::count_deactivated_modules(),
            'cache_cleared' => self::is_cache_cleared(),
            'rewrite_rules_flushed' => true
        );
    }

    /**
     * Contar cron jobs limpiados
     *
     * @return int
     */
    private static function count_cleared_cron_jobs() {
        $cron_jobs = array(
            'wcvs_bcv_sync',
            'wcvs_cache_cleanup',
            'wcvs_logs_cleanup',
            'wcvs_auto_reports'
        );

        $count = 0;
        foreach ($cron_jobs as $job) {
            if (!wp_next_scheduled($job)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Contar transients limpiados
     *
     * @return int
     */
    private static function count_cleared_transients() {
        global $wpdb;
        
        $before = get_option('wcvs_transients_before_deactivation', 0);
        $after = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_wcvs_%'"
        );

        return max(0, $before - $after);
    }

    /**
     * Contar módulos desactivados
     *
     * @return int
     */
    private static function count_deactivated_modules() {
        $modules = get_option('wcvs_modules', array());
        $count = 0;

        foreach ($modules as $module_data) {
            if (isset($module_data['enabled']) && !$module_data['enabled']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Verificar si el cache está limpiado
     *
     * @return bool
     */
    private static function is_cache_cleared() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/wcvs-cache/';
        
        if (!file_exists($cache_dir)) {
            return true;
        }

        $files = glob($cache_dir . '*');
        return empty($files);
    }

    /**
     * Preparar para desactivación completa (uninstall)
     */
    public static function prepare_for_uninstall() {
        // Marcar para eliminación completa
        update_option('wcvs_prepare_uninstall', true);
        update_option('wcvs_uninstall_date', current_time('mysql'));

        // Limpiar datos sensibles
        self::clear_sensitive_data();

        error_log('WooCommerce Venezuela Suite: Preparado para desinstalación completa');
    }

    /**
     * Limpiar datos sensibles
     */
    private static function clear_sensitive_data() {
        // Limpiar claves API
        $seniat_settings = get_option('wcvs_seniat', array());
        if (isset($seniat_settings['api_key'])) {
            $seniat_settings['api_key'] = '';
            update_option('wcvs_seniat', $seniat_settings);
        }

        // Limpiar datos de empresa
        $seniat_settings['company_rif'] = '';
        $seniat_settings['company_name'] = '';
        $seniat_settings['company_address'] = '';
        update_option('wcvs_seniat', $seniat_settings);

        error_log('WooCommerce Venezuela Suite: Datos sensibles limpiados');
    }
}
