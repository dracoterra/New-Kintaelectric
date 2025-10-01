<?php
/**
 * Module Manager - Gestor de módulos del plugin
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
class WVS_Module_Manager {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private $plugin;
    
    /**
     * Módulos disponibles
     * 
     * @var array
     */
    private $available_modules = array();
    
    /**
     * Módulos activos
     * 
     * @var array
     */
    private $active_modules = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Suite::get_instance();
        
        // Cargar módulos disponibles
        $this->load_available_modules();
        
        // Cargar módulos activos
        $this->load_active_modules();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // AJAX para gestión de módulos
        add_action('wp_ajax_wvs_toggle_module', array($this, 'ajax_toggle_module'));
        add_action('wp_ajax_wvs_get_module_status', array($this, 'ajax_get_module_status'));
        
        // Hooks de inicialización
        add_action('init', array($this, 'init_modules'));
    }
    
    /**
     * Cargar módulos disponibles
     */
    private function load_available_modules() {
        $this->available_modules = array(
            'core' => array(
                'name' => 'Motor Principal',
                'description' => 'Motor principal de Suite Venezuela',
                'version' => '1.0.0',
                'required' => true,
                'dependencies' => array(),
                'category' => 'core'
            ),
            'currency' => array(
                'name' => 'Sistema de Moneda',
                'description' => 'Conversión automática USD/VES con tasa BCV',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core'),
                'category' => 'financial'
            ),
            'payments' => array(
                'name' => 'Métodos de Pago',
                'description' => 'Pagos locales: Zelle, Pago Móvil, Efectivo',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core', 'currency'),
                'category' => 'financial'
            ),
            'shipping' => array(
                'name' => 'Servicios de Envío',
                'description' => 'Envíos locales: MRW, Zoom, Menssajero',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core'),
                'category' => 'logistics'
            ),
            'fiscal' => array(
                'name' => 'Sistema Fiscal',
                'description' => 'Reportes SENIAT y cumplimiento fiscal',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core', 'currency'),
                'category' => 'compliance'
            ),
            'invoicing' => array(
                'name' => 'Facturación Electrónica',
                'description' => 'Generación de facturas electrónicas',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core', 'fiscal'),
                'category' => 'compliance'
            ),
            'analytics' => array(
                'name' => 'Analytics Avanzado',
                'description' => 'Reportes y análisis de ventas',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core'),
                'category' => 'analytics'
            ),
            'notifications' => array(
                'name' => 'Sistema de Notificaciones',
                'description' => 'Notificaciones inteligentes y alertas',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core'),
                'category' => 'communication'
            ),
            'help' => array(
                'name' => 'Sistema de Ayuda',
                'description' => 'Ayuda integrada y documentación',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core'),
                'category' => 'support'
            ),
            'ai' => array(
                'name' => 'Inteligencia Artificial',
                'description' => 'Funcionalidades de IA para optimización',
                'version' => '1.0.0',
                'required' => false,
                'dependencies' => array('core', 'analytics'),
                'category' => 'ai'
            )
        );
    }
    
    /**
     * Cargar módulos activos
     */
    private function load_active_modules() {
        $this->active_modules = get_option('wvs_activated_modules', array('core', 'currency', 'payments'));
        
        // Asegurar que el módulo core esté siempre activo
        if (!in_array('core', $this->active_modules)) {
            $this->active_modules[] = 'core';
            $this->save_active_modules();
        }
    }
    
    /**
     * Inicializar módulos activos
     */
    public function init_modules() {
        foreach ($this->active_modules as $module_key) {
            $this->init_module($module_key);
        }
    }
    
    /**
     * Inicializar un módulo específico
     * 
     * @param string $module_key
     */
    private function init_module($module_key) {
        if (!isset($this->available_modules[$module_key])) {
            return;
        }
        
        $module = $this->available_modules[$module_key];
        
        // Verificar dependencias
        if (!$this->check_module_dependencies($module_key)) {
            $this->log_error("Module {$module_key} dependencies not met");
            return;
        }
        
        // Cargar archivos del módulo
        $this->load_module_files($module_key);
        
        // Inicializar clase del módulo
        $this->init_module_class($module_key);
        
        // Log de inicialización
        $this->log_info("Module {$module_key} initialized successfully");
    }
    
    /**
     * Verificar dependencias de un módulo
     * 
     * @param string $module_key
     * @return bool
     */
    public function check_module_dependencies($module_key) {
        if (!isset($this->available_modules[$module_key])) {
            return false;
        }
        
        $dependencies = $this->available_modules[$module_key]['dependencies'];
        
        foreach ($dependencies as $dependency) {
            if (!in_array($dependency, $this->active_modules)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Cargar archivos de un módulo
     * 
     * @param string $module_key
     */
    private function load_module_files($module_key) {
        $module_path = WVS_PLUGIN_PATH . "modules/{$module_key}/";
        
        if (!file_exists($module_path)) {
            return;
        }
        
        // Cargar archivo principal del módulo
        $main_file = $module_path . "class-wvs-module-{$module_key}.php";
        if (file_exists($main_file)) {
            require_once $main_file;
        }
        
        // Cargar archivos adicionales
        $files = glob($module_path . "*.php");
        foreach ($files as $file) {
            if (basename($file) !== "class-wvs-module-{$module_key}.php") {
                require_once $file;
            }
        }
    }
    
    /**
     * Inicializar clase de un módulo
     * 
     * @param string $module_key
     */
    private function init_module_class($module_key) {
        $class_name = "WVS_Module_" . ucfirst(str_replace('-', '_', $module_key));
        
        if (class_exists($class_name)) {
            new $class_name();
        }
    }
    
    /**
     * Activar un módulo
     * 
     * @param string $module_key
     * @return bool
     */
    public function activate_module($module_key) {
        if (!isset($this->available_modules[$module_key])) {
            return false;
        }
        
        // Verificar si ya está activo
        if (in_array($module_key, $this->active_modules)) {
            return true;
        }
        
        // Verificar dependencias
        if (!$this->check_module_dependencies($module_key)) {
            return false;
        }
        
        // Verificar si es requerido
        if ($this->available_modules[$module_key]['required']) {
            return false; // Los módulos requeridos no se pueden activar manualmente
        }
        
        // Activar módulo
        $this->active_modules[] = $module_key;
        $this->save_active_modules();
        
        // Ejecutar hook de activación
        do_action("wvs_module_activated_{$module_key}");
        
        // Log de activación
        $this->log_info("Module {$module_key} activated");
        
        return true;
    }
    
    /**
     * Desactivar un módulo
     * 
     * @param string $module_key
     * @return bool
     */
    public function deactivate_module($module_key) {
        if (!isset($this->available_modules[$module_key])) {
            return false;
        }
        
        // Verificar si está activo
        if (!in_array($module_key, $this->active_modules)) {
            return true;
        }
        
        // Verificar si es requerido
        if ($this->available_modules[$module_key]['required']) {
            return false; // Los módulos requeridos no se pueden desactivar
        }
        
        // Verificar dependencias de otros módulos
        if ($this->has_dependent_modules($module_key)) {
            return false;
        }
        
        // Desactivar módulo
        $this->active_modules = array_diff($this->active_modules, array($module_key));
        $this->save_active_modules();
        
        // Ejecutar hook de desactivación
        do_action("wvs_module_deactivated_{$module_key}");
        
        // Log de desactivación
        $this->log_info("Module {$module_key} deactivated");
        
        return true;
    }
    
    /**
     * Verificar si un módulo tiene dependientes
     * 
     * @param string $module_key
     * @return bool
     */
    public function has_dependent_modules($module_key) {
        foreach ($this->active_modules as $active_module) {
            if (isset($this->available_modules[$active_module]['dependencies'])) {
                if (in_array($module_key, $this->available_modules[$active_module]['dependencies'])) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Guardar módulos activos
     */
    private function save_active_modules() {
        update_option('wvs_activated_modules', $this->active_modules);
    }
    
    /**
     * Obtener módulos disponibles
     * 
     * @return array
     */
    public function get_available_modules() {
        return $this->available_modules;
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
     * Obtener información de un módulo
     * 
     * @param string $module_key
     * @return array|null
     */
    public function get_module_info($module_key) {
        if (!isset($this->available_modules[$module_key])) {
            return null;
        }
        
        $module = $this->available_modules[$module_key];
        $module['active'] = in_array($module_key, $this->active_modules);
        $module['can_activate'] = !$module['required'] && $this->check_module_dependencies($module_key);
        $module['can_deactivate'] = !$module['required'] && !$this->has_dependent_modules($module_key);
        
        return $module;
    }
    
    /**
     * AJAX para alternar módulo
     */
    public function ajax_toggle_module() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvs_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad', 'wvs')));
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'wvs')));
        }
        
        $module_key = sanitize_text_field($_POST['module_key']);
        $action = sanitize_text_field($_POST['action_type']);
        
        $result = false;
        $message = '';
        
        if ($action === 'activate') {
            $result = $this->activate_module($module_key);
            $message = $result ? 'Módulo activado correctamente' : 'Error al activar el módulo';
        } elseif ($action === 'deactivate') {
            $result = $this->deactivate_module($module_key);
            $message = $result ? 'Módulo desactivado correctamente' : 'Error al desactivar el módulo';
        }
        
        if ($result) {
            wp_send_json_success(array(
                'message' => $message,
                'module_info' => $this->get_module_info($module_key)
            ));
        } else {
            wp_send_json_error(array('message' => $message));
        }
    }
    
    /**
     * AJAX para obtener estado del módulo
     */
    public function ajax_get_module_status() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvs_admin_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad', 'wvs')));
        }
        
        $module_key = sanitize_text_field($_POST['module_key']);
        $module_info = $this->get_module_info($module_key);
        
        if ($module_info) {
            wp_send_json_success($module_info);
        } else {
            wp_send_json_error(array('message' => __('Módulo no encontrado', 'wvs')));
        }
    }
    
    /**
     * Log de información
     * 
     * @param string $message
     */
    private function log_info($message) {
        if (class_exists('WVS_Logger')) {
            WVS_Logger::info($message, array('component' => 'module_manager'));
        }
    }
    
    /**
     * Log de error
     * 
     * @param string $message
     */
    private function log_error($message) {
        if (class_exists('WVS_Logger')) {
            WVS_Logger::error($message, array('component' => 'module_manager'));
        }
    }
}
