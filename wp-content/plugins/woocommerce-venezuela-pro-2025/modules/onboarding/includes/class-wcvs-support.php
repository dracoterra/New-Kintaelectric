<?php
/**
 * Sistema de Soporte Técnico - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar el sistema de soporte técnico
 */
class WCVS_Support {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('support', array());
        $this->init_database();
    }

    /**
     * Inicializar base de datos
     */
    private function init_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            $this->create_support_tickets_table();
        }
    }

    /**
     * Crear tabla de tickets de soporte
     */
    private function create_support_tickets_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ticket_number varchar(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            subject varchar(255) NOT NULL,
            description longtext NOT NULL,
            category varchar(100) NOT NULL,
            priority varchar(20) DEFAULT 'medium',
            status varchar(20) DEFAULT 'open',
            assigned_to bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            resolved_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY ticket_number (ticket_number),
            KEY user_id (user_id),
            KEY category (category),
            KEY priority (priority),
            KEY status (status),
            KEY assigned_to (assigned_to)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, 'Tabla de tickets de soporte creada');
    }

    /**
     * Crear ticket de soporte
     *
     * @param array $ticket_data Datos del ticket
     * @return int|false ID del ticket o false si falla
     */
    public function create_support_ticket($ticket_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        
        // Validar datos
        if (!$this->validate_ticket_data($ticket_data)) {
            return false;
        }
        
        // Generar número de ticket
        $ticket_number = $this->generate_ticket_number();
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'ticket_number' => $ticket_number,
                'user_id' => $ticket_data['user_id'],
                'subject' => sanitize_text_field($ticket_data['subject']),
                'description' => sanitize_textarea_field($ticket_data['description']),
                'category' => sanitize_text_field($ticket_data['category']),
                'priority' => $ticket_data['priority'] ?? 'medium',
                'status' => 'open',
                'created_at' => current_time('mysql')
            )
        );
        
        if ($result !== false) {
            $ticket_id = $wpdb->insert_id;
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, "Ticket de soporte creado: {$ticket_number} (ID: {$ticket_id})");
            return $ticket_id;
        }
        
        return false;
    }

    /**
     * Validar datos del ticket
     *
     * @param array $ticket_data Datos del ticket
     * @return bool
     */
    private function validate_ticket_data($ticket_data) {
        if (empty($ticket_data['user_id']) || empty($ticket_data['subject']) || empty($ticket_data['description']) || empty($ticket_data['category'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Generar número de ticket
     *
     * @return string
     */
    private function generate_ticket_number() {
        return 'WCVS-' . date('Y') . '-' . wp_rand(1000, 9999);
    }

    /**
     * Obtener ticket por ID
     *
     * @param int $ticket_id ID del ticket
     * @return array|false
     */
    public function get_support_ticket($ticket_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE id = %d
        ", $ticket_id), ARRAY_A);
        
        return $result;
    }

    /**
     * Obtener ticket por número
     *
     * @param string $ticket_number Número del ticket
     * @return array|false
     */
    public function get_support_ticket_by_number($ticket_number) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE ticket_number = %s
        ", $ticket_number), ARRAY_A);
        
        return $result;
    }

    /**
     * Obtener tickets por usuario
     *
     * @param int $user_id ID del usuario
     * @param array $args Argumentos de búsqueda
     * @return array
     */
    public function get_user_support_tickets($user_id, $args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'status' => '',
            'category' => '',
            'priority' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        $where_conditions = array($wpdb->prepare('user_id = %d', $user_id));
        
        if (!empty($args['status'])) {
            $where_conditions[] = $wpdb->prepare('status = %s', $args['status']);
        }
        
        if (!empty($args['category'])) {
            $where_conditions[] = $wpdb->prepare('category = %s', $args['category']);
        }
        
        if (!empty($args['priority'])) {
            $where_conditions[] = $wpdb->prepare('priority = %s', $args['priority']);
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE {$where_clause}
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $args['limit'], $args['offset']), ARRAY_A);
        
        return $results;
    }

    /**
     * Actualizar ticket
     *
     * @param int $ticket_id ID del ticket
     * @param array $ticket_data Datos del ticket
     * @return bool
     */
    public function update_support_ticket($ticket_id, $ticket_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        
        $update_data = array(
            'updated_at' => current_time('mysql')
        );
        
        if (isset($ticket_data['subject'])) {
            $update_data['subject'] = sanitize_text_field($ticket_data['subject']);
        }
        
        if (isset($ticket_data['description'])) {
            $update_data['description'] = sanitize_textarea_field($ticket_data['description']);
        }
        
        if (isset($ticket_data['category'])) {
            $update_data['category'] = sanitize_text_field($ticket_data['category']);
        }
        
        if (isset($ticket_data['priority'])) {
            $update_data['priority'] = sanitize_text_field($ticket_data['priority']);
        }
        
        if (isset($ticket_data['status'])) {
            $update_data['status'] = sanitize_text_field($ticket_data['status']);
            
            if ($ticket_data['status'] === 'resolved') {
                $update_data['resolved_at'] = current_time('mysql');
            }
        }
        
        if (isset($ticket_data['assigned_to'])) {
            $update_data['assigned_to'] = $ticket_data['assigned_to'];
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $ticket_id)
        );
        
        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, "Ticket de soporte actualizado: {$ticket_id}");
            return true;
        }
        
        return false;
    }

    /**
     * Cerrar ticket
     *
     * @param int $ticket_id ID del ticket
     * @return bool
     */
    public function close_support_ticket($ticket_id) {
        return $this->update_support_ticket($ticket_id, array('status' => 'closed'));
    }

    /**
     * Resolver ticket
     *
     * @param int $ticket_id ID del ticket
     * @return bool
     */
    public function resolve_support_ticket($ticket_id) {
        return $this->update_support_ticket($ticket_id, array('status' => 'resolved'));
    }

    /**
     * Asignar ticket
     *
     * @param int $ticket_id ID del ticket
     * @param int $user_id ID del usuario asignado
     * @return bool
     */
    public function assign_support_ticket($ticket_id, $user_id) {
        return $this->update_support_ticket($ticket_id, array('assigned_to' => $user_id));
    }

    /**
     * Obtener categorías de soporte
     *
     * @return array
     */
    public function get_support_categories() {
        return array(
            'general' => 'General',
            'currency' => 'Moneda y Conversión',
            'payments' => 'Pasarelas de Pago',
            'shipping' => 'Métodos de Envío',
            'taxes' => 'Sistema Fiscal',
            'billing' => 'Facturación Electrónica',
            'notifications' => 'Notificaciones',
            'reports' => 'Reportes SENIAT',
            'technical' => 'Problemas Técnicos',
            'billing_issue' => 'Problema de Facturación'
        );
    }

    /**
     * Obtener prioridades de soporte
     *
     * @return array
     */
    public function get_support_priorities() {
        return array(
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'urgent' => 'Urgente'
        );
    }

    /**
     * Obtener estados de soporte
     *
     * @return array
     */
    public function get_support_statuses() {
        return array(
            'open' => 'Abierto',
            'in_progress' => 'En Progreso',
            'resolved' => 'Resuelto',
            'closed' => 'Cerrado'
        );
    }

    /**
     * Obtener estadísticas de soporte
     *
     * @return array
     */
    public function get_support_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        
        $total_tickets = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
        ");
        
        $open_tickets = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE status = 'open'
        ");
        
        $resolved_tickets = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE status = 'resolved'
        ");
        
        $closed_tickets = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE status = 'closed'
        ");
        
        $tickets_by_category = $wpdb->get_results("
            SELECT category, COUNT(*) as count
            FROM {$table_name}
            GROUP BY category
        ", ARRAY_A);
        
        $tickets_by_priority = $wpdb->get_results("
            SELECT priority, COUNT(*) as count
            FROM {$table_name}
            GROUP BY priority
        ", ARRAY_A);
        
        $recent_tickets = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        return array(
            'total_tickets' => $total_tickets ?: 0,
            'open_tickets' => $open_tickets ?: 0,
            'resolved_tickets' => $resolved_tickets ?: 0,
            'closed_tickets' => $closed_tickets ?: 0,
            'recent_tickets' => $recent_tickets ?: 0,
            'tickets_by_category' => $tickets_by_category,
            'tickets_by_priority' => $tickets_by_priority
        );
    }

    /**
     * Obtener información del sistema para soporte
     *
     * @return array
     */
    public function get_system_info() {
        global $wpdb;
        
        $theme = wp_get_theme();
        $plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        
        $wcvs_info = array(
            'version' => WCVS_VERSION,
            'modules' => WCVS_Core::get_instance()->get_module_manager()->get_active_modules()
        );
        
        return array(
            'wordpress' => array(
                'version' => get_bloginfo('version'),
                'multisite' => is_multisite(),
                'language' => get_locale()
            ),
            'woocommerce' => array(
                'version' => WC()->version,
                'currency' => get_woocommerce_currency(),
                'country' => WC()->countries->get_base_country()
            ),
            'theme' => array(
                'name' => $theme->get('Name'),
                'version' => $theme->get('Version'),
                'author' => $theme->get('Author')
            ),
            'plugins' => array(
                'total' => count($plugins),
                'active' => count($active_plugins),
                'list' => $active_plugins
            ),
            'wcvs' => $wcvs_info,
            'server' => array(
                'php_version' => PHP_VERSION,
                'mysql_version' => $wpdb->db_version(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            )
        );
    }

    /**
     * Generar reporte de diagnóstico
     *
     * @return array
     */
    public function generate_diagnostic_report() {
        $system_info = $this->get_system_info();
        $wcvs_stats = array();
        
        // Obtener estadísticas de cada módulo
        $modules = WCVS_Core::get_instance()->get_module_manager()->get_active_modules();
        foreach ($modules as $module_key => $module_data) {
            $module_class = $module_data['class'];
            if (class_exists($module_class)) {
                $module_instance = new $module_class();
                if (method_exists($module_instance, 'get_module_stats')) {
                    $wcvs_stats[$module_key] = $module_instance->get_module_stats();
                }
            }
        }
        
        return array(
            'system_info' => $system_info,
            'wcvs_stats' => $wcvs_stats,
            'generated_at' => current_time('mysql'),
            'generated_by' => get_current_user_id()
        );
    }

    /**
     * Enviar ticket por email
     *
     * @param int $ticket_id ID del ticket
     * @return bool
     */
    public function send_ticket_email($ticket_id) {
        $ticket = $this->get_support_ticket($ticket_id);
        if (!$ticket) {
            return false;
        }
        
        $user = get_userdata($ticket['user_id']);
        if (!$user) {
            return false;
        }
        
        $subject = sprintf('[WCVS Support] Ticket #%s: %s', $ticket['ticket_number'], $ticket['subject']);
        
        $message = sprintf(
            "Hola %s,\n\n" .
            "Hemos recibido tu ticket de soporte:\n\n" .
            "Número de Ticket: %s\n" .
            "Asunto: %s\n" .
            "Categoría: %s\n" .
            "Prioridad: %s\n" .
            "Estado: %s\n\n" .
            "Descripción:\n%s\n\n" .
            "Te responderemos lo antes posible.\n\n" .
            "Saludos,\n" .
            "Equipo de Soporte WCVS",
            $user->display_name,
            $ticket['ticket_number'],
            $ticket['subject'],
            $this->get_support_categories()[$ticket['category']],
            $this->get_support_priorities()[$ticket['priority']],
            $this->get_support_statuses()[$ticket['status']],
            $ticket['description']
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        $sent = wp_mail($user->user_email, $subject, $message, $headers);
        
        if ($sent) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, "Email de ticket enviado: {$ticket['ticket_number']}");
        }
        
        return $sent;
    }

    /**
     * Buscar tickets
     *
     * @param string $query Consulta de búsqueda
     * @param array $args Argumentos de búsqueda
     * @return array
     */
    public function search_support_tickets($query, $args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'user_id' => null,
            'category' => '',
            'status' => '',
            'priority' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'wcvs_support_tickets';
        $where_conditions = array();
        
        if (!empty($query)) {
            $where_conditions[] = $wpdb->prepare('(subject LIKE %s OR description LIKE %s)', '%' . $query . '%', '%' . $query . '%');
        }
        
        if ($args['user_id'] !== null) {
            $where_conditions[] = $wpdb->prepare('user_id = %d', $args['user_id']);
        }
        
        if (!empty($args['category'])) {
            $where_conditions[] = $wpdb->prepare('category = %s', $args['category']);
        }
        
        if (!empty($args['status'])) {
            $where_conditions[] = $wpdb->prepare('status = %s', $args['status']);
        }
        
        if (!empty($args['priority'])) {
            $where_conditions[] = $wpdb->prepare('priority = %s', $args['priority']);
        }
        
        $where_clause = empty($where_conditions) ? '1=1' : implode(' AND ', $where_conditions);
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE {$where_clause}
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $args['limit'], $args['offset']), ARRAY_A);
        
        return $results;
    }
}
