<?php
/**
 * Gestor de módulos del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar módulos del plugin
 */
class WCVS_Module_Manager {

    /**
     * Módulos registrados
     *
     * @var array
     */
    private $modules = array();

    /**
     * Módulos activos
     *
     * @var array
     */
    private $active_modules = array();

    /**
     * Instancias de módulos
     *
     * @var array
     */
    private $module_instances = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_module_configurations();
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'load_active_modules'));
        add_action('admin_init', array($this, 'handle_module_toggle'));
        add_action('wp_ajax_wcvs_toggle_module', array($this, 'ajax_toggle_module'));
    }

    /**
     * Cargar configuraciones de módulos desde la base de datos
     */
    private function load_module_configurations() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_modules';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            // Si la tabla no existe, usar configuración por defecto
            $this->modules = $this->get_default_modules();
            return;
        }

        // Cargar módulos desde la base de datos
        $results = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
        
        foreach ($results as $row) {
            $this->modules[$row['module_key']] = array(
                'name' => $row['module_name'],
                'description' => $row['module_description'],
                'class' => $row['module_class'],
                'file' => $row['module_file'],
                'enabled' => (bool) $row['is_active'],
                'settings' => maybe_unserialize($row['settings']),
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            );
        }

        // Si no hay módulos en la base de datos, usar configuración por defecto
        if (empty($this->modules)) {
            $this->modules = $this->get_default_modules();
            $this->save_modules_to_database();
        }
    }

    /**
     * Obtener módulos por defecto
     *
     * @return array
     */
    private function get_default_modules() {
        return array(
            'currency_manager' => array(
                'name' => 'Gestor de Moneda',
                'description' => 'Conversión automática USD a VES usando tasa BCV',
                'class' => 'WCVS_Currency_Manager',
                'file' => 'modules/currency-manager/class-wcvs-currency-manager.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array('bcv_integration'),
                'priority' => 1
            ),
            'payment_gateways' => array(
                'name' => 'Pasarelas de Pago',
                'description' => 'Pasarelas de pago locales para Venezuela',
                'class' => 'WCVS_Payment_Gateways',
                'file' => 'modules/payment-gateways/class-wcvs-payment-gateways.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array(),
                'priority' => 2
            ),
            'shipping_methods' => array(
                'name' => 'Métodos de Envío',
                'description' => 'Métodos de envío locales para Venezuela',
                'class' => 'WCVS_Shipping_Methods',
                'file' => 'modules/shipping-methods/class-wcvs-shipping-methods.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array(),
                'priority' => 3
            ),
            'tax_system' => array(
                'name' => 'Sistema Fiscal',
                'description' => 'Sistema fiscal venezolano (IVA, IGTF)',
                'class' => 'WCVS_Tax_System',
                'file' => 'modules/tax-system/class-wcvs-tax-system.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array(),
                'priority' => 4
            ),
            'electronic_billing' => array(
                'name' => 'Facturación Electrónica',
                'description' => 'Facturación electrónica para SENIAT',
                'class' => 'WCVS_Electronic_Billing',
                'file' => 'modules/electronic-billing/class-wcvs-electronic-billing.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array('tax_system'),
                'priority' => 5
            ),
            'seniat_reports' => array(
                'name' => 'Reportes SENIAT',
                'description' => 'Reportes fiscales para SENIAT',
                'class' => 'WCVS_SENIAT_Reports',
                'file' => 'modules/seniat-reports/class-wcvs-seniat-reports.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array('tax_system', 'electronic_billing'),
                'priority' => 6
            ),
            'price_display' => array(
                'name' => 'Visualización de Precios',
                'description' => 'Sistema avanzado de visualización de precios',
                'class' => 'WCVS_Price_Display',
                'file' => 'modules/price-display/class-wcvs-price-display.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array('currency_manager'),
                'priority' => 7
            ),
            'onboarding' => array(
                'name' => 'Configuración Rápida',
                'description' => 'Wizard de configuración rápida',
                'class' => 'WCVS_Onboarding',
                'file' => 'modules/onboarding/class-wcvs-onboarding.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array(),
                'priority' => 8
            ),
            'help_system' => array(
                'name' => 'Sistema de Ayuda',
                'description' => 'Sistema de ayuda integrado',
                'class' => 'WCVS_Help_System',
                'file' => 'modules/help-system/class-wcvs-help-system.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array(),
                'priority' => 9
            ),
            'notifications' => array(
                'name' => 'Sistema de Notificaciones',
                'description' => 'Sistema de notificaciones automáticas',
                'class' => 'WCVS_Notifications',
                'file' => 'modules/notifications/class-wcvs-notifications.php',
                'enabled' => true,
                'settings' => array(),
                'dependencies' => array(),
                'priority' => 10
            )
        );
    }

    /**
     * Registrar un módulo
     *
     * @param string $key Clave del módulo
     * @param array $config Configuración del módulo
     */
    public function register_module($key, $config) {
        $this->modules[$key] = wp_parse_args($config, array(
            'name' => '',
            'description' => '',
            'class' => '',
            'file' => '',
            'enabled' => false,
            'settings' => array(),
            'dependencies' => array(),
            'priority' => 999
        ));

        // No guardar en base de datos inmediatamente para evitar consumo de memoria
        // Se guardará cuando sea necesario
    }

    /**
     * Cargar módulos activos
     */
    public function load_active_modules() {
        // Solo cargar módulos esenciales inicialmente para evitar consumo de memoria
        $essential_modules = array('currency_manager', 'payment_gateways', 'shipping_methods');
        
        foreach ($this->modules as $key => $module) {
            if ($module['enabled'] && $this->check_dependencies($key)) {
                // Solo cargar módulos esenciales o si se solicita específicamente
                if (in_array($key, $essential_modules) || $this->is_module_requested($key)) {
                    $this->load_module($key);
                }
            }
        }

        // Ordenar por prioridad
        uasort($this->active_modules, array($this, 'sort_by_priority'));
    }

    /**
     * Verificar si un módulo es solicitado específicamente
     *
     * @param string $key Clave del módulo
     * @return bool
     */
    private function is_module_requested($key) {
        // Verificar si se está accediendo a una página específica del módulo
        if (is_admin()) {
            $current_page = $_GET['page'] ?? '';
            return strpos($current_page, 'wcvs-' . $key) !== false;
        }
        
        // Verificar si se está usando una funcionalidad específica del módulo
        return false;
    }

    /**
     * Cargar un módulo específico
     *
     * @param string $key Clave del módulo
     */
    public function load_module($key) {
        if (!isset($this->modules[$key])) {
            return;
        }

        $module = $this->modules[$key];

        // Cargar archivo del módulo
        $file_path = WCVS_PLUGIN_PATH . $module['file'];
        if (file_exists($file_path)) {
            require_once $file_path;

            // Crear instancia del módulo
            if (class_exists($module['class'])) {
                $this->module_instances[$key] = new $module['class']();
                $this->active_modules[$key] = $module;

                // Inicializar módulo si tiene método init
                if (method_exists($this->module_instances[$key], 'init')) {
                    $this->module_instances[$key]->init();
                }

                error_log("WooCommerce Venezuela Suite: Módulo '{$key}' cargado correctamente");
            } else {
                error_log("WooCommerce Venezuela Suite: Clase '{$module['class']}' no encontrada para módulo '{$key}'");
            }
        } else {
            error_log("WooCommerce Venezuela Suite: Archivo no encontrado para módulo '{$key}': {$file_path}");
        }
    }

    /**
     * Verificar dependencias de un módulo
     *
     * @param string $key Clave del módulo
     * @return bool
     */
    private function check_dependencies($key) {
        if (!isset($this->modules[$key]['dependencies'])) {
            return true;
        }

        $dependencies = $this->modules[$key]['dependencies'];
        
        foreach ($dependencies as $dependency) {
            if (!isset($this->modules[$dependency]) || !$this->modules[$dependency]['enabled']) {
                error_log("WooCommerce Venezuela Suite: Dependencia '{$dependency}' no satisfecha para módulo '{$key}'");
                return false;
            }
        }

        return true;
    }

    /**
     * Activar un módulo
     *
     * @param string $key Clave del módulo
     * @return bool
     */
    public function activate_module($key) {
        if (!isset($this->modules[$key])) {
            return false;
        }

        // Verificar dependencias
        if (!$this->check_dependencies($key)) {
            return false;
        }

        // Activar módulo
        $this->modules[$key]['enabled'] = true;
        $this->save_module_to_database($key, $this->modules[$key]);

        // Cargar módulo
        $this->load_module($key);

        // Ejecutar acción de activación
        do_action('wcvs_module_activated', $key, $this->modules[$key]);

        error_log("WooCommerce Venezuela Suite: Módulo '{$key}' activado");
        return true;
    }

    /**
     * Desactivar un módulo
     *
     * @param string $key Clave del módulo
     * @return bool
     */
    public function deactivate_module($key) {
        if (!isset($this->modules[$key])) {
            return false;
        }

        // Verificar si otros módulos dependen de este
        if ($this->has_dependents($key)) {
            return false;
        }

        // Desactivar módulo
        $this->modules[$key]['enabled'] = false;
        $this->save_module_to_database($key, $this->modules[$key]);

        // Remover de módulos activos
        unset($this->active_modules[$key]);
        unset($this->module_instances[$key]);

        // Ejecutar acción de desactivación
        do_action('wcvs_module_deactivated', $key, $this->modules[$key]);

        error_log("WooCommerce Venezuela Suite: Módulo '{$key}' desactivado");
        return true;
    }

    /**
     * Verificar si un módulo tiene dependientes
     *
     * @param string $key Clave del módulo
     * @return bool
     */
    private function has_dependents($key) {
        foreach ($this->modules as $module_key => $module) {
            if (isset($module['dependencies']) && in_array($key, $module['dependencies'])) {
                if ($module['enabled']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Toggle de estado de un módulo
     *
     * @param string $key Clave del módulo
     * @return bool
     */
    public function toggle_module($key) {
        if (!isset($this->modules[$key])) {
            return false;
        }

        if ($this->modules[$key]['enabled']) {
            return $this->deactivate_module($key);
        } else {
            return $this->activate_module($key);
        }
    }

    /**
     * Obtener información de un módulo
     *
     * @param string $key Clave del módulo
     * @return array|null
     */
    public function get_module($key) {
        return isset($this->modules[$key]) ? $this->modules[$key] : null;
    }

    /**
     * Obtener instancia de un módulo
     *
     * @param string $key Clave del módulo
     * @return object|null
     */
    public function get_module_instance($key) {
        return isset($this->module_instances[$key]) ? $this->module_instances[$key] : null;
    }

    /**
     * Verificar si un módulo está activo
     *
     * @param string $key Clave del módulo
     * @return bool
     */
    public function is_module_active($key) {
        return isset($this->modules[$key]) && $this->modules[$key]['enabled'];
    }

    /**
     * Obtener todos los módulos
     *
     * @return array
     */
    public function get_all_modules() {
        return $this->modules;
    }

    /**
     * Obtener módulos activos
     *
     * @return array
     */
    public function get_active_modules() {
        return $this->active_modules;
    }

    /**
     * Obtener módulos inactivos
     *
     * @return array
     */
    public function get_inactive_modules() {
        $inactive = array();
        foreach ($this->modules as $key => $module) {
            if (!$module['enabled']) {
                $inactive[$key] = $module;
            }
        }
        return $inactive;
    }

    /**
     * Activar módulos por defecto
     */
    public function activate_default_modules() {
        $default_modules = array(
            'currency_manager',
            'payment_gateways',
            'shipping_methods',
            'tax_system',
            'electronic_billing',
            'seniat_reports',
            'price_display',
            'onboarding',
            'help_system',
            'notifications'
        );

        foreach ($default_modules as $key) {
            if (isset($this->modules[$key])) {
                $this->activate_module($key);
            }
        }
    }

    /**
     * Notificar a módulos activos
     *
     * @param string $event Evento
     * @param array $data Datos del evento
     */
    public function notify_modules($event, $data = array()) {
        foreach ($this->module_instances as $key => $instance) {
            if (method_exists($instance, 'handle_event')) {
                $instance->handle_event($event, $data);
            }
        }
    }

    /**
     * Manejar toggle de módulo desde admin
     */
    public function handle_module_toggle() {
        if (!isset($_POST['wcvs_toggle_module']) || !wp_verify_nonce($_POST['_wpnonce'], 'wcvs_toggle_module')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }

        $module_key = sanitize_text_field($_POST['module_key']);
        $result = $this->toggle_module($module_key);

        if ($result) {
            $status = $this->is_module_active($module_key) ? 'activado' : 'desactivado';
            add_action('admin_notices', function() use ($module_key, $status) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>Módulo "' . esc_html($module_key) . '" ' . esc_html($status) . ' correctamente.</p>';
                echo '</div>';
            });
        } else {
            add_action('admin_notices', function() use ($module_key) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>Error al cambiar estado del módulo "' . esc_html($module_key) . '".</p>';
                echo '</div>';
            });
        }
    }

    /**
     * Manejar toggle de módulo via AJAX
     */
    public function ajax_toggle_module() {
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error('Acceso denegado');
        }

        $module_key = sanitize_text_field($_POST['module_key']);
        $result = $this->toggle_module($module_key);

        if ($result) {
            $status = $this->is_module_active($module_key) ? 'activado' : 'desactivado';
            wp_send_json_success(array(
                'message' => "Módulo {$status} correctamente",
                'active' => $this->is_module_active($module_key)
            ));
        } else {
            wp_send_json_error('Error al cambiar estado del módulo');
        }
    }

    /**
     * Crear tabla de módulos
     */
    private function create_modules_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_modules';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE {$table_name} (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                module_key varchar(100) NOT NULL,
                module_name varchar(255) NOT NULL,
                module_description text,
                module_class varchar(255) NOT NULL,
                module_file varchar(500) NOT NULL,
                is_active tinyint(1) DEFAULT 0,
                settings longtext,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY module_key (module_key),
                KEY is_active (is_active)
            ) {$charset_collate};";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Guardar módulo en base de datos
     *
     * @param string $key Clave del módulo
     * @param array $module Datos del módulo
     */
    private function save_module_to_database($key, $module) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_modules';
        
        // Crear tabla si no existe
        $this->create_modules_table();
        
        $data = array(
            'module_key' => $key,
            'module_name' => $module['name'],
            'module_description' => $module['description'],
            'module_class' => $module['class'],
            'module_file' => $module['file'],
            'is_active' => $module['enabled'] ? 1 : 0,
            'settings' => maybe_serialize($module['settings'])
        );

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE module_key = %s",
            $key
        ));

        if ($existing) {
            $wpdb->update($table_name, $data, array('module_key' => $key));
        } else {
            $wpdb->insert($table_name, $data);
        }
    }

    /**
     * Guardar todos los módulos en base de datos
     */
    private function save_modules_to_database() {
        foreach ($this->modules as $key => $module) {
            $this->save_module_to_database($key, $module);
        }
    }

    /**
     * Ordenar módulos por prioridad
     *
     * @param array $a Primer módulo
     * @param array $b Segundo módulo
     * @return int
     */
    private function sort_by_priority($a, $b) {
        $priority_a = isset($a['priority']) ? $a['priority'] : 999;
        $priority_b = isset($b['priority']) ? $b['priority'] : 999;
        
        return $priority_a - $priority_b;
    }

    /**
     * Obtener estadísticas de módulos
     *
     * @return array
     */
    public function get_module_stats() {
        $total = count($this->modules);
        $active = count($this->active_modules);
        $inactive = $total - $active;

        return array(
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'percentage_active' => $total > 0 ? round(($active / $total) * 100, 2) : 0
        );
    }
}
