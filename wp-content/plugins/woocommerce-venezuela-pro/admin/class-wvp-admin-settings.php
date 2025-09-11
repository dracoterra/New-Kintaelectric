<?php
/**
 * Clase para configuraciones del plugin
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Admin_Settings {
    
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
        // Obtener instancia del plugin de forma segura
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Añadir página de configuración
        add_action("admin_menu", array($this, "add_admin_menu"));
        
        // Registrar configuraciones
        add_action("admin_init", array($this, "register_settings"));
        
        // Cargar scripts y estilos
        add_action("admin_enqueue_scripts", array($this, "enqueue_admin_scripts"));
        
        // Hook moderno para WooCommerce 8.0+
        add_action("woocommerce_admin_order_data_after_billing_address", array($this, "display_venezuela_order_data"));
        
        // Hook para mostrar datos venezolanos en la API
        add_filter("woocommerce_rest_prepare_shop_order_object", array($this, "add_venezuela_data_to_api"), 10, 3);
        
        // Hook para asignar números de control automáticamente
        add_action("woocommerce_order_status_completed", array($this, "assign_control_number"));
        
        // Hook para procesar el guardado de configuraciones
        add_action("admin_init", array($this, "process_settings_save"));
        
        // Registrar callback de sanitización para wvp_settings
        add_filter("pre_update_option_wvp_settings", array($this, "sanitize_settings"), 10, 2);
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_submenu_page(
            "woocommerce",
            __("Venezuela Pro", "wvp"),
            __("Venezuela Pro", "wvp"),
            "manage_woocommerce",
            "wvp-settings",
            array($this, "display_settings_page")
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        // Registrar grupo de configuraciones
        register_setting("wvp_settings", "wvp_settings");
        
        // Sección de configuraciones generales
        add_settings_section(
            "wvp_general",
            __("Configuraciones Generales", "wvp"),
            array($this, "general_section_callback"),
            "wvp-settings"
        );
        
        // Campo de formato de referencia de precio
        add_settings_field(
            "price_reference_format",
            __("Formato de Referencia de Precio", "wvp"),
            array($this, "price_reference_format_callback"),
            "wvp-settings",
            "wvp_general"
        );

        // Sección de visualización de moneda
        add_settings_section(
            "wvp_currency_display",
            __("Visualización de Moneda", "wvp"),
            array($this, "currency_display_section_callback"),
            "wvp-settings"
        );

        // Campo para activar switcher de moneda
        add_settings_field(
            "enable_currency_switcher",
            __("Activar Switcher de Moneda", "wvp"),
            array($this, "enable_currency_switcher_callback"),
            "wvp-settings",
            "wvp_currency_display"
        );

        // Campo para mostrar desglose dual
        add_settings_field(
            "enable_dual_breakdown",
            __("Mostrar Desglose Dual", "wvp"),
            array($this, "enable_dual_breakdown_callback"),
            "wvp-settings",
            "wvp_currency_display"
        );

        // Campo para facturación híbrida
        add_settings_field(
            "enable_hybrid_invoicing",
            __("Activar Facturación Híbrida", "wvp"),
            array($this, "enable_hybrid_invoicing_callback"),
            "wvp-settings",
            "wvp_currency_display"
        );
        
        // Campo de tasa IGTF
        add_settings_field(
            "igtf_rate",
            __("Tasa IGTF (%)", "wvp"),
            array($this, "igtf_rate_callback"),
            "wvp-settings",
            "wvp_general"
        );
        
        // Campo de mostrar IGTF
        add_settings_field(
            "show_igtf",
            __("Mostrar IGTF", "wvp"),
            array($this, "show_igtf_callback"),
            "wvp-settings",
            "wvp_general"
        );
        
        // Sección de facturación fiscal
        add_settings_section(
            "wvp_fiscal",
            __("Configuraciones Fiscales", "wvp"),
            array($this, "fiscal_section_callback"),
            "wvp-settings"
        );
        
        // Campo de prefijo de número de control
        add_settings_field(
            "control_number_prefix",
            __("Prefijo del Número de Control", "wvp"),
            array($this, "control_number_prefix_callback"),
            "wvp-settings",
            "wvp_fiscal"
        );
        
        // Campo de próximo número de control
        add_settings_field(
            "next_control_number",
            __("Próximo Número de Control", "wvp"),
            array($this, "next_control_number_callback"),
            "wvp-settings",
            "wvp_fiscal"
        );
        
        // Sección de notificaciones WhatsApp
        add_settings_section(
            "wvp_whatsapp",
            __("Notificaciones WhatsApp", "wvp"),
            array($this, "whatsapp_section_callback"),
            "wvp-settings"
        );
        
        // Campo de plantilla de pago verificado
        add_settings_field(
            "whatsapp_payment_template",
            __("Plantilla de Pago Verificado", "wvp"),
            array($this, "whatsapp_payment_template_callback"),
            "wvp-settings",
            "wvp_whatsapp"
        );
        
        // Campo de plantilla de envío
        add_settings_field(
            "whatsapp_shipping_template",
            __("Plantilla de Envío", "wvp"),
            array($this, "whatsapp_shipping_template_callback"),
            "wvp-settings",
            "wvp_whatsapp"
        );
    }
    
    /**
     * Mostrar página de configuraciones
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e("WooCommerce Venezuela Pro", "wvp"); ?></h1>
            
            <!-- Pestañas -->
            <nav class="nav-tab-wrapper">
                <a href="#configuraciones" class="nav-tab nav-tab-active" data-tab="configuraciones">
                    <?php _e("Configuraciones", "wvp"); ?>
                </a>
                <a href="#informacion" class="nav-tab" data-tab="informacion">
                    <?php _e("Información del Plugin", "wvp"); ?>
                </a>
                <a href="#funcionalidades" class="nav-tab" data-tab="funcionalidades">
                    <?php _e("Funcionalidades", "wvp"); ?>
                </a>
                <a href="#ayuda" class="nav-tab" data-tab="ayuda">
                    <?php _e("Ayuda", "wvp"); ?>
                </a>
            </nav>
            
            <!-- Pestaña de Configuraciones -->
            <div id="configuraciones" class="tab-content active">
                <form method="post" action="options.php">
                    <?php
                    settings_fields("wvp_settings");
                    do_settings_sections("wvp-settings");
                    submit_button();
                    ?>
                </form>
                
                <div class="wvp-status">
                    <h2><?php _e("Estado del Sistema", "wvp"); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php _e("WooCommerce", "wvp"); ?></th>
                            <td>
                                <?php if (class_exists('WooCommerce')): ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?> (v<?php echo WC()->version; ?>)</span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("BCV Dólar Tracker", "wvp"); ?></th>
                            <td>
                                <?php if (is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')): ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Tasa BCV Actual", "wvp"); ?></th>
                            <td>
                                <?php
                                $rate = WVP_BCV_Integrator::get_rate();
                                if ($rate !== null):
                                ?>
                                    <span class="wvp-status-ok"><?php echo number_format($rate, 2, ",", "."); ?> Bs./USD</span>
                                <?php else: ?>
                                    <span class="wvp-status-error"><?php _e("No disponible", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Pestaña de Información del Plugin -->
            <div id="informacion" class="tab-content">
                <?php $this->display_plugin_info_tab(); ?>
            </div>
            
            <!-- Pestaña de Funcionalidades -->
            <div id="funcionalidades" class="tab-content">
                <?php $this->display_features_tab(); ?>
            </div>
            
            <!-- Pestaña de Ayuda -->
            <div id="ayuda" class="tab-content">
                <?php $this->display_help_tab(); ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Funcionalidad de pestañas
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                var tab = $(this).data('tab');
                
                // Remover clase activa de todas las pestañas
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').removeClass('active');
                
                // Activar pestaña seleccionada
                $(this).addClass('nav-tab-active');
                $('#' + tab).addClass('active');
            });
        });
        </script>
        <?php
    }
    
    /**
     * Callback para sección general
     */
    public function general_section_callback() {
        echo '<p>' . __("Configuraciones generales del plugin.", "wvp") . '</p>';
    }
    
    /**
     * Callback para formato de referencia de precio
     */
    public function price_reference_format_callback() {
        $value = $this->plugin ? $this->plugin->get_option("price_reference_format", "(Ref. %s Bs.)") : "(Ref. %s Bs.)";
        echo '<input type="text" name="wvp_settings[price_reference_format]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __("Use %s como placeholder para el precio en bolívares.", "wvp") . '</p>';
    }

    /**
     * Procesar guardado de configuraciones
     */
    public function process_settings_save() {
        // Solo procesar si estamos en la página correcta y hay datos POST
        if (!isset($_POST['option_page']) || $_POST['option_page'] !== 'wvp_settings') {
            return;
        }
        
        if (!isset($_POST['wvp_settings']) || !isset($_POST['_wpnonce'])) {
            return;
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wvp_settings-options')) {
            return;
        }
        
        // Mostrar mensaje de éxito
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Configuraciones guardadas correctamente.', 'wvp') . '</p></div>';
        });
    }
    
    /**
     * Sanitizar configuraciones antes de guardar
     */
    public function sanitize_settings($new_value, $old_value) {
        // Procesar checkboxes - si no están marcados, establecer como "no"
        $checkbox_fields = array(
            'enable_currency_switcher',
            'enable_dual_breakdown', 
            'enable_hybrid_invoicing'
        );
        
        foreach ($checkbox_fields as $field) {
            if (!isset($new_value[$field]) || $new_value[$field] !== 'yes') {
                $new_value[$field] = 'no';
            }
        }
        
        // Limpiar caché de opciones después de sanitizar
        wp_cache_delete('wvp_settings', 'options');
        
        return $new_value;
    }

    /**
     * Callback para sección de visualización de moneda
     */
    public function currency_display_section_callback() {
        echo '<p>' . __("Configuraciones avanzadas para la visualización de precios y facturación en múltiples monedas.", "wvp") . '</p>';
    }
    
    /**
     * Renderizar campo checkbox
     */
    private function render_checkbox_field($field_name, $label, $description) {
        // Leer directamente de la base de datos para evitar problemas de caché
        $settings = get_option('wvp_settings', array());
        $value = isset($settings[$field_name]) ? $settings[$field_name] : 'no';
        $checked = checked($value, "yes", false);
        
        echo '<label>';
        echo '<input type="checkbox" name="wvp_settings[' . esc_attr($field_name) . ']" value="yes" ' . $checked . ' />';
        echo ' ' . esc_html($label);
        echo '</label>';
        echo '<p class="description">' . esc_html($description) . '</p>';
    }

    /**
     * Callback para activar switcher de moneda
     */
    public function enable_currency_switcher_callback() {
        $this->render_checkbox_field(
            'enable_currency_switcher',
            __("Activar el modo de precios interactivo (Switcher USD/VES)", "wvp"),
            __("Permite a los usuarios alternar entre precios en USD y VES en las páginas de productos y tienda.", "wvp")
        );
    }

    /**
     * Callback para mostrar desglose dual
     */
    public function enable_dual_breakdown_callback() {
        $this->render_checkbox_field(
            'enable_dual_breakdown',
            __("Mostrar desglose dual (USD y VES) en Carrito y Checkout", "wvp"),
            __("Muestra los precios de referencia en bolívares en las líneas de totales del carrito y checkout.", "wvp")
        );
    }

    /**
     * Callback para facturación híbrida
     */
    public function enable_hybrid_invoicing_callback() {
        $this->render_checkbox_field(
            'enable_hybrid_invoicing',
            __("Activar facturación híbrida (Factura en VES)", "wvp"),
            __("Muestra los montos en bolívares en correos electrónicos y facturas PDF, con nota aclaratoria del pago en USD.", "wvp")
        );
    }
    
    /**
     * Callback para tasa IGTF
     */
    public function igtf_rate_callback() {
        $value = $this->plugin ? $this->plugin->get_option("igtf_rate", "3.0") : "3.0";
        echo '<input type="number" name="wvp_settings[igtf_rate]" value="' . esc_attr($value) . '" step="0.1" min="0" max="100" />';
        echo '<p class="description">' . __("Tasa de IGTF en porcentaje.", "wvp") . '</p>';
    }
    
    /**
     * Callback para mostrar IGTF
     */
    public function show_igtf_callback() {
        $value = $this->plugin ? $this->plugin->get_option("show_igtf", "1") : "1";
        echo '<input type="checkbox" name="wvp_settings[show_igtf]" value="1" ' . checked($value, "1", false) . ' />';
        echo '<p class="description">' . __("Mostrar IGTF en el checkout.", "wvp") . '</p>';
    }
    
    /**
     * Callback para sección fiscal
     */
    public function fiscal_section_callback() {
        echo '<p>' . __("Configuraciones para facturación fiscal venezolana.", "wvp") . '</p>';
    }
    
    /**
     * Callback para prefijo de número de control
     */
    public function control_number_prefix_callback() {
        $value = $this->plugin ? $this->plugin->get_option("control_number_prefix", "00-") : "00-";
        echo '<input type="text" name="wvp_settings[control_number_prefix]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __("Prefijo para los números de control (ej: 00-).", "wvp") . '</p>';
    }
    
    /**
     * Callback para próximo número de control
     */
    public function next_control_number_callback() {
        $value = $this->plugin ? $this->plugin->get_option("next_control_number", "1") : "1";
        echo '<input type="number" name="wvp_settings[next_control_number]" value="' . esc_attr($value) . '" min="1" step="1" />';
        echo '<p class="description">' . __("Próximo número de control a asignar. Se incrementa automáticamente.", "wvp") . '</p>';
    }
    
    /**
     * Callback para sección de WhatsApp
     */
    public function whatsapp_section_callback() {
        echo '<p>' . __("Configurar plantillas de mensajes para notificaciones WhatsApp.", "wvp") . '</p>';
        echo '<p><strong>' . __("Placeholders disponibles:", "wvp") . '</strong></p>';
        echo '<ul style="margin-left: 20px;">';
        echo '<li><code>{customer_name}</code> - ' . __("Nombre del cliente", "wvp") . '</li>';
        echo '<li><code>{order_number}</code> - ' . __("Número del pedido", "wvp") . '</li>';
        echo '<li><code>{store_name}</code> - ' . __("Nombre de la tienda", "wvp") . '</li>';
        echo '<li><code>{shipping_guide}</code> - ' . __("Número de guía de envío", "wvp") . '</li>';
        echo '<li><code>{order_total}</code> - ' . __("Total del pedido", "wvp") . '</li>';
        echo '</ul>';
    }
    
    /**
     * Callback para plantilla de pago verificado
     */
    public function whatsapp_payment_template_callback() {
        $templates = get_option('wvp_whatsapp_templates', array());
        $value = isset($templates['payment_verified']) ? $templates['payment_verified'] : '¡Hola {customer_name}! 🎉 Tu pago del pedido {order_number} ha sido verificado exitosamente. Estamos preparando tu envío. ¡Gracias por tu compra en {store_name}!';
        
        echo '<textarea name="wvp_whatsapp_templates[payment_verified]" rows="4" cols="80" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __("Plantilla para notificar pago verificado.", "wvp") . '</p>';
    }
    
    /**
     * Callback para plantilla de envío
     */
    public function whatsapp_shipping_template_callback() {
        $templates = get_option('wvp_whatsapp_templates', array());
        $value = isset($templates['shipping']) ? $templates['shipping'] : '¡Hola {customer_name}! 📦 Tu pedido {order_number} ha sido enviado. Puedes rastrearlo con la guía: {shipping_guide}. ¡Gracias por comprar en {store_name}!';
        
        echo '<textarea name="wvp_whatsapp_templates[shipping]" rows="4" cols="80" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __("Plantilla para notificar envío realizado.", "wvp") . '</p>';
    }
    
    /**
     * Asignar número de control automáticamente
     * 
     * @param int $order_id ID del pedido
     */
    public function assign_control_number($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Verificar si ya tiene número de control
        $existing_control_number = $order->get_meta('_seniat_control_number');
        if (!empty($existing_control_number)) {
            return; // Ya tiene número de control
        }
        
        // Obtener configuraciones
        $prefix = $this->plugin ? $this->plugin->get_option("control_number_prefix", "00-") : "00-";
        $next_number = $this->plugin ? $this->plugin->get_option("next_control_number", "1") : "1";
        
        // Generar número de control
        $control_number = $prefix . str_pad($next_number, 8, '0', STR_PAD_LEFT);
        
        // Guardar en el pedido
        $order->update_meta_data('_seniat_control_number', $control_number);
        $order->save();
        
        // Incrementar próximo número
        $this->plugin->update_option("next_control_number", intval($next_number) + 1);
        
        // Añadir nota al pedido
        $note = sprintf(__('Número de control SENIAT asignado: %s', 'wvp'), $control_number);
        $order->add_order_note($note, false, true);
    }
    
    /**
     * Mostrar pestaña de información del plugin
     */
    public function display_plugin_info_tab() {
        $plugin_data = get_plugin_data(WVP_PLUGIN_FILE);
        $wp_version = get_bloginfo('version');
        $php_version = PHP_VERSION;
        $memory_limit = ini_get('memory_limit');
        $upload_max = ini_get('upload_max_filesize');
        $post_max = ini_get('post_max_size');
        $max_execution_time = ini_get('max_execution_time');
        
        ?>
        <div class="wvp-info-container">
            <div class="wvp-info-grid">
                <!-- Información del Plugin -->
                <div class="wvp-info-section">
                    <h2><?php _e("Información del Plugin", "wvp"); ?></h2>
                    <table class="wvp-info-table">
                        <tr>
                            <th><?php _e("Nombre:", "wvp"); ?></th>
                            <td><?php echo esc_html($plugin_data['Name']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Versión:", "wvp"); ?></th>
                            <td><?php echo esc_html($plugin_data['Version']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Descripción:", "wvp"); ?></th>
                            <td><?php echo esc_html($plugin_data['Description']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Autor:", "wvp"); ?></th>
                            <td><?php echo esc_html($plugin_data['Author']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Ruta del Plugin:", "wvp"); ?></th>
                            <td><code><?php echo esc_html(WVP_PLUGIN_PATH); ?></code></td>
                        </tr>
                        <tr>
                            <th><?php _e("URL del Plugin:", "wvp"); ?></th>
                            <td><code><?php echo esc_html(WVP_PLUGIN_URL); ?></code></td>
                        </tr>
                    </table>
                </div>
                
                <!-- Información del Sistema -->
                <div class="wvp-info-section">
                    <h2><?php _e("Información del Sistema", "wvp"); ?></h2>
                    <table class="wvp-info-table">
                        <tr>
                            <th><?php _e("WordPress:", "wvp"); ?></th>
                            <td><?php echo esc_html($wp_version); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("PHP:", "wvp"); ?></th>
                            <td><?php echo esc_html($php_version); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("WooCommerce:", "wvp"); ?></th>
                            <td>
                                <?php if (class_exists('WooCommerce')): ?>
                                    <?php echo WC()->version; ?>
                                <?php else: ?>
                                    <span class="wvp-status-error"><?php _e("No instalado", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Memoria PHP:", "wvp"); ?></th>
                            <td><?php echo esc_html($memory_limit); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Upload Max:", "wvp"); ?></th>
                            <td><?php echo esc_html($upload_max); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Post Max:", "wvp"); ?></th>
                            <td><?php echo esc_html($post_max); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Max Execution Time:", "wvp"); ?></th>
                            <td><?php echo esc_html($max_execution_time); ?>s</td>
                        </tr>
                    </table>
                </div>
                
                <!-- Estado de Dependencias -->
                <div class="wvp-info-section">
                    <h2><?php _e("Estado de Dependencias", "wvp"); ?></h2>
                    <table class="wvp-info-table">
                        <tr>
                            <th><?php _e("BCV Dólar Tracker:", "wvp"); ?></th>
                            <td>
                                <?php if (is_plugin_active('bcv-dolar-tracker/bcv-dolar-tracker.php')): ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Tasa BCV Actual:", "wvp"); ?></th>
                            <td>
                                <?php
                                $rate = WVP_BCV_Integrator::get_rate();
                                if ($rate !== null):
                                ?>
                                    <span class="wvp-status-ok"><?php echo number_format($rate, 2, ",", "."); ?> Bs./USD</span>
                                <?php else: ?>
                                    <span class="wvp-status-error"><?php _e("No disponible", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Última Actualización BCV:", "wvp"); ?></th>
                            <td>
                                <?php
                                $last_update = get_option('wvp_bcv_last_update');
                                if ($last_update):
                                    echo esc_html(date('d/m/Y H:i:s', $last_update));
                                else:
                                    echo '<span class="wvp-status-error">' . __('Nunca', 'wvp') . '</span>';
                                endif;
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Estado de Módulos -->
                <div class="wvp-info-section">
                    <h2><?php _e("Estado de Módulos", "wvp"); ?></h2>
                    <table class="wvp-info-table">
                        <tr>
                            <th><?php _e("Delivery Local:", "wvp"); ?></th>
                            <td>
                                <?php
                                $shipping_methods = WC()->shipping->get_shipping_methods();
                                $local_delivery_active = isset($shipping_methods['wvp_local_delivery']) && $shipping_methods['wvp_local_delivery']->enabled === 'yes';
                                if ($local_delivery_active):
                                ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Cashea (Compra ahora, paga después):", "wvp"); ?></th>
                            <td>
                                <?php
                                $payment_gateways = WC()->payment_gateways->payment_gateways();
                                $cashea_active = isset($payment_gateways['wvp_cashea']) && $payment_gateways['wvp_cashea']->enabled === 'yes';
                                if ($cashea_active):
                                ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Zelle:", "wvp"); ?></th>
                            <td>
                                <?php
                                $zelle_active = isset($payment_gateways['wvp_zelle']) && $payment_gateways['wvp_zelle']->enabled === 'yes';
                                if ($zelle_active):
                                ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Pago Móvil:", "wvp"); ?></th>
                            <td>
                                <?php
                                $pago_movil_active = isset($payment_gateways['wvp_pago_movil']) && $payment_gateways['wvp_pago_movil']->enabled === 'yes';
                                if ($pago_movil_active):
                                ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Efectivo USD (con IGTF):", "wvp"); ?></th>
                            <td>
                                <?php
                                $efectivo_usd_active = isset($payment_gateways['wvp_efectivo']) && $payment_gateways['wvp_efectivo']->enabled === 'yes';
                                if ($efectivo_usd_active):
                                ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Efectivo Bolívares:", "wvp"); ?></th>
                            <td>
                                <?php
                                $efectivo_bs_active = isset($payment_gateways['wvp_efectivo_bolivares']) && $payment_gateways['wvp_efectivo_bolivares']->enabled === 'yes';
                                if ($efectivo_bs_active):
                                ?>
                                    <span class="wvp-status-ok">✓ <?php _e("Activo", "wvp"); ?></span>
                                <?php else: ?>
                                    <span class="wvp-status-error">✗ <?php _e("Inactivo", "wvp"); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Estadísticas del Plugin -->
                <div class="wvp-info-section">
                    <h2><?php _e("Estadísticas del Plugin", "wvp"); ?></h2>
                    <table class="wvp-info-table">
                        <tr>
                            <th><?php _e("Pedidos con Cédula/RIF:", "wvp"); ?></th>
                            <td>
                                <?php
                                $orders_with_cedula = $this->count_orders_with_cedula();
                                echo esc_html($orders_with_cedula);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Pedidos con IGTF:", "wvp"); ?></th>
                            <td>
                                <?php
                                $orders_with_igtf = $this->count_orders_with_igtf();
                                echo esc_html($orders_with_igtf);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Pedidos con Cashea:", "wvp"); ?></th>
                            <td>
                                <?php
                                $orders_with_cashea = $this->count_orders_with_cashea();
                                echo esc_html($orders_with_cashea);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Números de Control Asignados:", "wvp"); ?></th>
                            <td>
                                <?php
                                $control_numbers = $this->count_control_numbers();
                                echo esc_html($control_numbers);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Facturas Generadas:", "wvp"); ?></th>
                            <td>
                                <?php
                                $invoices_generated = $this->count_invoices_generated();
                                echo esc_html($invoices_generated);
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar pestaña de funcionalidades
     */
    public function display_features_tab() {
        ?>
        <div class="wvp-features-container">
            <h2><?php _e("Funcionalidades del Plugin", "wvp"); ?></h2>
            
            <div class="wvp-features-grid">
                <!-- Campos Venezolanos -->
                <div class="wvp-feature-card">
                    <h3><?php _e("🏛️ Campos Venezolanos", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Campo obligatorio de Cédula/RIF en checkout", "wvp"); ?></li>
                        <li><?php _e("Validación automática de formato", "wvp"); ?></li>
                        <li><?php _e("Guardado en metadatos del pedido", "wvp"); ?></li>
                        <li><?php _e("Visualización en admin de pedidos", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Sistema de Precios -->
                <div class="wvp-feature-card">
                    <h3><?php _e("💰 Sistema de Precios", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Precios en USD con referencia en bolívares", "wvp"); ?></li>
                        <li><?php _e("Tasa BCV automática del plugin BCV Dólar Tracker", "wvp"); ?></li>
                        <li><?php _e("Formato personalizable de referencia", "wvp"); ?></li>
                        <li><?php _e("Mostrar en productos, carrito y checkout", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Pasarelas de Pago -->
                <div class="wvp-feature-card">
                    <h3><?php _e("💳 Pasarelas de Pago", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Zelle (Transferencia Digital)", "wvp"); ?></li>
                        <li><?php _e("Pago Móvil (Transferencia Digital)", "wvp"); ?></li>
                        <li><?php _e("Efectivo USD (Billetes)", "wvp"); ?></li>
                        <li><?php _e("Efectivo Bolívares (Billetes)", "wvp"); ?></li>
                        <li><?php _e("Cashea (Compra ahora, paga después)", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Sistema IGTF -->
                <div class="wvp-feature-card">
                    <h3><?php _e("📊 Sistema IGTF", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Aplicación automática según tipo de pago", "wvp"); ?></li>
                        <li><?php _e("Solo para pagos en efectivo USD", "wvp"); ?></li>
                        <li><?php _e("Tasa configurable", "wvp"); ?></li>
                        <li><?php _e("Cálculo automático en checkout", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Reportes Fiscales -->
                <div class="wvp-feature-card">
                    <h3><?php _e("📈 Reportes Fiscales", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Libro de Ventas (Nivel SENIAT)", "wvp"); ?></li>
                        <li><?php _e("Reporte de IGTF", "wvp"); ?></li>
                        <li><?php _e("Conversión automática USD → Bolívares", "wvp"); ?></li>
                        <li><?php _e("Exportación a CSV", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Verificación de Pagos -->
                <div class="wvp-feature-card">
                    <h3><?php _e("🔍 Verificación de Pagos", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Centro de conciliación de pagos", "wvp"); ?></li>
                        <li><?php _e("Lista de pedidos pendientes", "wvp"); ?></li>
                        <li><?php _e("Verificación con un clic", "wvp"); ?></li>
                        <li><?php _e("Subida de comprobantes", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Facturación Legal -->
                <div class="wvp-feature-card">
                    <h3><?php _e("🧾 Facturación Legal", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Números de control SENIAT automáticos", "wvp"); ?></li>
                        <li><?php _e("Generador de facturas PDF", "wvp"); ?></li>
                        <li><?php _e("Formato venezolano estándar", "wvp"); ?></li>
                        <li><?php _e("Metadatos fiscales completos", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Delivery Local -->
                <div class="wvp-feature-card">
                    <h3><?php _e("🚚 Delivery Local", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Sistema de zonas para Caracas y Miranda", "wvp"); ?></li>
                        <li><?php _e("Tarifas personalizables por zona", "wvp"); ?></li>
                        <li><?php _e("Selector automático en checkout", "wvp"); ?></li>
                        <li><?php _e("Cálculo dinámico de costos", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Integración Cashea -->
                <div class="wvp-feature-card">
                    <h3><?php _e("💳 Cashea (Compra ahora, paga después)", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Financiamiento flexible para clientes", "wvp"); ?></li>
                        <li><?php _e("Integración completa con API", "wvp"); ?></li>
                        <li><?php _e("Webhook seguro con validación HMAC", "wvp"); ?></li>
                        <li><?php _e("Control de montos mínimos y máximos", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Notificaciones WhatsApp -->
                <div class="wvp-feature-card">
                    <h3><?php _e("📱 Notificaciones WhatsApp", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Notificaciones automáticas a clientes", "wvp"); ?></li>
                        <li><?php _e("Plantillas personalizables", "wvp"); ?></li>
                        <li><?php _e("Botones de acción en pedidos", "wvp"); ?></li>
                        <li><?php _e("Formato internacional de números", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Administración -->
                <div class="wvp-feature-card">
                    <h3><?php _e("⚙️ Administración", "wvp"); ?></h3>
                    <ul>
                        <li><?php _e("Configuraciones centralizadas", "wvp"); ?></li>
                        <li><?php _e("Columnas personalizadas en pedidos", "wvp"); ?></li>
                        <li><?php _e("Meta box con datos venezolanos", "wvp"); ?></li>
                        <li><?php _e("Interfaz intuitiva", "wvp"); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar pestaña de ayuda
     */
    public function display_help_tab() {
        ?>
        <div class="wvp-help-container">
            <h2><?php _e("Ayuda y Soporte", "wvp"); ?></h2>
            
            <div class="wvp-help-grid">
                <!-- Guía de Configuración -->
                <div class="wvp-help-section">
                    <h3><?php _e("🚀 Configuración Inicial", "wvp"); ?></h3>
                    <ol>
                        <li><?php _e("Instalar y activar el plugin BCV Dólar Tracker", "wvp"); ?></li>
                        <li><?php _e("Configurar las pasarelas de pago en WooCommerce", "wvp"); ?></li>
                        <li><?php _e("Configurar Cashea (obtener API Keys)", "wvp"); ?></li>
                        <li><?php _e("Configurar delivery local (zonas y tarifas)", "wvp"); ?></li>
                        <li><?php _e("Establecer el prefijo y próximo número de control", "wvp"); ?></li>
                        <li><?php _e("Configurar la tasa de IGTF si es necesaria", "wvp"); ?></li>
                        <li><?php _e("Personalizar plantillas de WhatsApp", "wvp"); ?></li>
                        <li><?php _e("Probar el flujo completo de compra", "wvp"); ?></li>
                    </ol>
                </div>
                
                <!-- Flujo de Trabajo -->
                <div class="wvp-help-section">
                    <h3><?php _e("🔄 Flujo de Trabajo", "wvp"); ?></h3>
                    <ol>
                        <li><?php _e("Cliente realiza pedido con Cédula/RIF", "wvp"); ?></li>
                        <li><?php _e("Cliente selecciona método de pago venezolano", "wvp"); ?></li>
                        <li><?php _e("Sistema aplica IGTF si corresponde", "wvp"); ?></li>
                        <li><?php _e("Cliente realiza pago y pedido queda 'En Espera'", "wvp"); ?></li>
                        <li><?php _e("Admin verifica pago en 'Verificar Pagos'", "wvp"); ?></li>
                        <li><?php _e("Pedido cambia a 'Procesando'", "wvp"); ?></li>
                        <li><?php _e("Al completar se asigna número de control", "wvp"); ?></li>
                        <li><?php _e("Generar factura PDF desde el pedido", "wvp"); ?></li>
                    </ol>
                </div>
                
                <!-- Páginas Administrativas -->
                <div class="wvp-help-section">
                    <h3><?php _e("📋 Páginas Administrativas", "wvp"); ?></h3>
                    <ul>
                        <li><strong><?php _e("Venezuela Pro:", "wvp"); ?></strong> <?php _e("Configuraciones generales, fiscales y WhatsApp", "wvp"); ?></li>
                        <li><strong><?php _e("Reportes Fiscales Vzla:", "wvp"); ?></strong> <?php _e("Libro de Ventas y Reporte IGTF", "wvp"); ?></li>
                        <li><strong><?php _e("Verificar Pagos:", "wvp"); ?></strong> <?php _e("Centro de conciliación de pagos", "wvp"); ?></li>
                        <li><strong><?php _e("Envíos → Zonas:", "wvp"); ?></strong> <?php _e("Configurar delivery local por zonas", "wvp"); ?></li>
                        <li><strong><?php _e("WooCommerce → Pagos:", "wvp"); ?></strong> <?php _e("Configurar Cashea y otras pasarelas", "wvp"); ?></li>
                        <li><strong><?php _e("Pedidos:", "wvp"); ?></strong> <?php _e("Meta box con datos venezolanos y WhatsApp", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Solución de Problemas -->
                <div class="wvp-help-section">
                    <h3><?php _e("🔧 Solución de Problemas", "wvp"); ?></h3>
                    <ul>
                        <li><strong><?php _e("Tasa BCV no disponible:", "wvp"); ?></strong> <?php _e("Verificar que BCV Dólar Tracker esté activo", "wvp"); ?></li>
                        <li><strong><?php _e("IGTF no se aplica:", "wvp"); ?></strong> <?php _e("Verificar configuración de pasarelas de pago", "wvp"); ?></li>
                        <li><strong><?php _e("Cashea no aparece:", "wvp"); ?></strong> <?php _e("Verificar API Keys y montos configurados", "wvp"); ?></li>
                        <li><strong><?php _e("Delivery local no funciona:", "wvp"); ?></strong> <?php _e("Verificar que el cliente esté en DC o Miranda", "wvp"); ?></li>
                        <li><strong><?php _e("WhatsApp no envía:", "wvp"); ?></strong> <?php _e("Verificar formato de número telefónico", "wvp"); ?></li>
                        <li><strong><?php _e("Números de control no se asignan:", "wvp"); ?></strong> <?php _e("Verificar configuración en Venezuela Pro", "wvp"); ?></li>
                        <li><strong><?php _e("Facturas no se generan:", "wvp"); ?></strong> <?php _e("Verificar permisos de escritura en uploads", "wvp"); ?></li>
                    </ul>
                </div>
                
                <!-- Enlaces Útiles -->
                <div class="wvp-help-section">
                    <h3><?php _e("🔗 Enlaces Útiles", "wvp"); ?></h3>
                    <ul>
                        <li><a href="<?php echo admin_url('admin.php?page=wvp-settings'); ?>"><?php _e("Configuraciones del Plugin", "wvp"); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=wvp-reports'); ?>"><?php _e("Reportes Fiscales", "wvp"); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=wvp-verify-payments'); ?>"><?php _e("Verificar Pagos", "wvp"); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping'); ?>"><?php _e("Configurar Delivery Local", "wvp"); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout'); ?>"><?php _e("Configurar Pasarelas de Pago", "wvp"); ?></a></li>
                        <li><a href="<?php echo admin_url('edit.php?post_type=shop_order'); ?>"><?php _e("Gestión de Pedidos", "wvp"); ?></a></li>
                    </ul>
                </div>
                
                <!-- Información de Contacto -->
                <div class="wvp-help-section">
                    <h3><?php _e("📞 Soporte Técnico", "wvp"); ?></h3>
                    <p><?php _e("Para soporte técnico o consultas:", "wvp"); ?></p>
                    <ul>
                        <li><?php _e("Revisar la documentación del plugin", "wvp"); ?></li>
                        <li><?php _e("Verificar logs de WordPress", "wvp"); ?></li>
                        <li><?php _e("Comprobar configuraciones de WooCommerce", "wvp"); ?></li>
                        <li><?php _e("Verificar permisos de usuario", "wvp"); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Contar pedidos con cédula/RIF
     */
    private function count_orders_with_cedula() {
        global $wpdb;
        
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_billing_cedula_rif'
            AND pm.meta_value != ''
        ");
        
        return $count ?: 0;
    }
    
    /**
     * Contar pedidos con IGTF
     */
    private function count_orders_with_igtf() {
        global $wpdb;
        
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_igtf_amount'
            AND pm.meta_value > 0
        ");
        
        return $count ?: 0;
    }
    
    /**
     * Contar números de control asignados
     */
    private function count_control_numbers() {
        global $wpdb;
        
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_seniat_control_number'
            AND pm.meta_value != ''
        ");
        
        return $count ?: 0;
    }
    
    /**
     * Contar facturas generadas
     */
    private function count_invoices_generated() {
        global $wpdb;
        
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_invoice_url'
            AND pm.meta_value != ''
        ");
        
        return $count ?: 0;
    }
    
    /**
     * Contar pedidos con Cashea
     */
    private function count_orders_with_cashea() {
        global $wpdb;
        
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_cashea_transaction_id'
            AND pm.meta_value != ''
        ");
        
        return $count ?: 0;
    }
    
    /**
     * Cargar scripts y estilos del admin
     * 
     * @param string $hook Hook actual
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== "woocommerce_page_wvp-settings") {
            return;
        }
        
        wp_enqueue_style(
            "wvp-admin-settings",
            WVP_PLUGIN_URL . "assets/css/admin-settings.css",
            array(),
            WVP_VERSION
        );
    }
    
    /**
     * Mostrar datos venezolanos en la interfaz de administración (método moderno)
     * 
     * @param WC_Order $order Pedido
     */
    public function display_venezuela_order_data($order) {
        if (!$order || !is_a($order, 'WC_Order')) {
            return;
        }
        
        echo '<div class="wvp-order-data">';
        echo '<h3>' . __('Información Venezuela', 'wvp') . '</h3>';
        
        // Mostrar cédula/RIF
        $cedula_rif = $order->get_meta('_billing_cedula_rif');
        if ($cedula_rif) {
            echo '<p><strong>' . __('Cédula/RIF:', 'wvp') . '</strong> ' . esc_html($cedula_rif) . '</p>';
        }
        
        // Mostrar tasa BCV
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        if ($bcv_rate) {
            echo '<p><strong>' . __('Tasa BCV:', 'wvp') . '</strong> ' . number_format($bcv_rate, 2, ',', '.') . ' Bs./USD</p>';
        }
        
        // Mostrar IGTF
        $igtf_applied = $order->get_meta('_igtf_applied');
        if ($igtf_applied === 'yes') {
            $igtf_amount = $order->get_meta('_igtf_amount');
            $igtf_rate = $order->get_meta('_igtf_rate');
            echo '<p><strong>' . __('IGTF:', 'wvp') . '</strong> ' . wc_price($igtf_amount) . ' (' . $igtf_rate . '%)</p>';
        }
        
        // Mostrar información de pago
        $payment_method = $order->get_payment_method();
        if (in_array($payment_method, ['wvp_zelle', 'wvp_pago_movil'])) {
            $confirmation = $order->get_meta('_payment_reference');
            if ($confirmation) {
                echo '<p><strong>' . __('Número de Confirmación:', 'wvp') . '</strong> ' . esc_html($confirmation) . '</p>';
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Añadir datos venezolanos a la API de WooCommerce
     * 
     * @param WP_REST_Response $response Respuesta de la API
     * @param WC_Order $order Pedido
     * @param WP_REST_Request $request Request de la API
     * @return WP_REST_Response
     */
    public function add_venezuela_data_to_api($response, $order, $request) {
        if (!$order || !is_a($order, 'WC_Order')) {
            return $response;
        }
        
        $data = $response->get_data();
        
        // Añadir datos venezolanos
        $data['venezuela_data'] = array(
            'cedula_rif' => $order->get_meta('_billing_cedula_rif'),
            'bcv_rate' => $order->get_meta('_bcv_rate_at_purchase'),
            'igtf_applied' => $order->get_meta('_igtf_applied'),
            'igtf_amount' => $order->get_meta('_igtf_amount'),
            'igtf_rate' => $order->get_meta('_igtf_rate'),
            'payment_reference' => $order->get_meta('_payment_reference'),
            'total_ves' => $order->get_meta('_total_ves'),
            'total_ves_formatted' => $order->get_meta('_total_ves_formatted')
        );
        
        $response->set_data($data);
        return $response;
    }
}