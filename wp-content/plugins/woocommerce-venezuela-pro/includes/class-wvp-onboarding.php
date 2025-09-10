<?php
/**
 * Clase para el asistente de configuración inicial
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Onboarding {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // AJAX para ejecutar onboarding
        add_action('wp_ajax_wvp_run_onboarding', array($this, 'run_onboarding_ajax'));
        
        // Hook para mostrar notificación de onboarding
        add_action('admin_notices', array($this, 'show_onboarding_notice'));
    }
    
    /**
     * Mostrar notificación de onboarding
     */
    public function show_onboarding_notice() {
        // Solo mostrar si no se ha completado el onboarding
        $onboarding_completed = $this->plugin->get_option('onboarding_completed');
        
        if ($onboarding_completed) {
            return;
        }
        
        // Solo mostrar en páginas relevantes
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, array('dashboard', 'plugins', 'woocommerce_page_wvp-settings'))) {
            return;
        }
        
        ?>
        <div class="notice notice-info is-dismissible wvp-onboarding-notice">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 10px 0;"><?php esc_html_e('¡Bienvenido a WooCommerce Venezuela Pro!', 'wvp'); ?></h3>
                    <p style="margin: 0 0 10px 0;">
                        <?php esc_html_e('Para comenzar, ejecute el asistente de configuración inicial que configurará automáticamente su tienda para Venezuela.', 'wvp'); ?>
                    </p>
                    <p style="margin: 0;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wvp-settings&tab=general')); ?>" class="button button-primary">
                            <?php esc_html_e('Ejecutar Asistente de Configuración', 'wvp'); ?>
                        </a>
                        <a href="#" class="button button-secondary" onclick="wvpSkipOnboarding()">
                            <?php esc_html_e('Omitir por ahora', 'wvp'); ?>
                        </a>
                    </p>
                </div>
                <div style="flex-shrink: 0;">
                    <span class="dashicons dashicons-admin-settings" style="font-size: 48px; color: #007cba;"></span>
                </div>
            </div>
        </div>
        
        <script>
            function wvpSkipOnboarding() {
                jQuery.post(ajaxurl, {
                    action: 'wvp_skip_onboarding',
                    nonce: '<?php echo wp_create_nonce('wvp_skip_onboarding'); ?>'
                }, function(response) {
                    if (response.success) {
                        jQuery('.wvp-onboarding-notice').fadeOut();
                    }
                });
            }
        </script>
        <?php
    }
    
    /**
     * Ejecutar asistente de configuración vía AJAX
     */
    public function run_onboarding_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_admin_settings_nonce')) {
            wp_die('Acceso denegado');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Permisos insuficientes');
        }
        
        $result = $this->run_onboarding();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Ejecutar asistente de configuración
     * 
     * @return array Resultado del onboarding
     */
    public function run_onboarding() {
        $steps_completed = 0;
        $total_steps = 5;
        $messages = array();
        
        try {
            // Paso 1: Configurar moneda a USD
            if ($this->configure_currency()) {
                $steps_completed++;
                $messages[] = __('Moneda configurada a USD', 'wvp');
            }
            
            // Paso 2: Configurar formato de precios
            if ($this->configure_price_format()) {
                $steps_completed++;
                $messages[] = __('Formato de precios configurado', 'wvp');
            }
            
            // Paso 3: Configurar ubicación
            if ($this->configure_location()) {
                $steps_completed++;
                $messages[] = __('Ubicación configurada a Venezuela', 'wvp');
            }
            
            // Paso 4: Configurar opciones del plugin
            if ($this->configure_plugin_options()) {
                $steps_completed++;
                $messages[] = __('Opciones del plugin configuradas', 'wvp');
            }
            
            // Paso 5: Marcar onboarding como completado
            if ($this->complete_onboarding()) {
                $steps_completed++;
                $messages[] = __('Asistente de configuración completado', 'wvp');
            }
            
            return array(
                'success' => true,
                'steps_completed' => $steps_completed,
                'total_steps' => $total_steps,
                'messages' => $messages,
                'message' => sprintf(
                    __('Asistente completado: %d de %d pasos ejecutados correctamente', 'wvp'),
                    $steps_completed,
                    $total_steps
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => __('Error durante el asistente de configuración: ', 'wvp') . $e->getMessage()
            );
        }
    }
    
    /**
     * Configurar moneda
     * 
     * @return bool True si se configuró correctamente
     */
    private function configure_currency() {
        // Configurar moneda a USD
        update_option('woocommerce_currency', 'USD');
        update_option('woocommerce_currency_pos', 'right');
        
        return true;
    }
    
    /**
     * Configurar formato de precios
     * 
     * @return bool True si se configuró correctamente
     */
    private function configure_price_format() {
        // Configurar formato venezolano
        update_option('woocommerce_price_thousand_sep', '.');
        update_option('woocommerce_price_decimal_sep', ',');
        update_option('woocommerce_price_num_decimals', 2);
        
        return true;
    }
    
    /**
     * Configurar ubicación
     * 
     * @return bool True si se configuró correctamente
     */
    private function configure_location() {
        // Configurar ubicación a Venezuela
        update_option('woocommerce_default_country', 'VE');
        update_option('woocommerce_store_address', '');
        update_option('woocommerce_store_city', 'Caracas');
        update_option('woocommerce_store_postcode', '1010');
        
        return true;
    }
    
    /**
     * Configurar opciones del plugin
     * 
     * @return bool True si se configuró correctamente
     */
    private function configure_plugin_options() {
        // Configurar opciones por defecto del plugin
        $default_options = array(
            'show_ves_reference' => true,
            'show_igtf' => true,
            'igtf_rate' => 3.0,
            'price_format' => '(Ref. %s Bs.)',
            'require_cedula_rif' => true,
            'cedula_rif_placeholder' => 'V-12345678 o J-12345678-9',
            'onboarding_completed' => false // Se marcará como true en el siguiente paso
        );
        
        $current_options = $this->plugin->get_option();
        $merged_options = array_merge($current_options, $default_options);
        
        update_option('wvp_settings', $merged_options);
        
        return true;
    }
    
    /**
     * Completar onboarding
     * 
     * @return bool True si se completó correctamente
     */
    private function complete_onboarding() {
        // Marcar onboarding como completado
        $this->plugin->update_option('onboarding_completed', true);
        $this->plugin->update_option('onboarding_date', current_time('mysql'));
        
        return true;
    }
    
    /**
     * Obtener estado del onboarding
     * 
     * @return array Estado del onboarding
     */
    public function get_onboarding_status() {
        $onboarding_completed = $this->plugin->get_option('onboarding_completed');
        $onboarding_date = $this->plugin->get_option('onboarding_date');
        
        return array(
            'completed' => (bool) $onboarding_completed,
            'date' => $onboarding_date,
            'currency_configured' => get_option('woocommerce_currency') === 'USD',
            'location_configured' => get_option('woocommerce_default_country') === 'VE',
            'price_format_configured' => get_option('woocommerce_price_decimal_sep') === ','
        );
    }
    
    /**
     * Resetear onboarding
     * 
     * @return bool True si se reseteó correctamente
     */
    public function reset_onboarding() {
        $this->plugin->update_option('onboarding_completed', false);
        $this->plugin->update_option('onboarding_date', '');
        
        return true;
    }
    
    /**
     * Verificar si se necesita onboarding
     * 
     * @return bool True si se necesita onboarding
     */
    public function needs_onboarding() {
        $status = $this->get_onboarding_status();
        
        return !$status['completed'] || 
               !$status['currency_configured'] || 
               !$status['location_configured'] || 
               !$status['price_format_configured'];
    }
}
