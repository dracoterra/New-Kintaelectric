<?php
/**
 * Clase para mostrar logs en el panel de administración
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.1.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Incluir la clase WP_List_Table si no está disponible
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class BCV_Logs_Table extends WP_List_Table {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => 'log',
            'plural' => 'logs',
            'ajax' => false
        ));
    }
    
    /**
     * Obtener columnas de la tabla
     * 
     * @return array Array de columnas
     */
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'timestamp' => 'Fecha y Hora',
            'log_level' => 'Nivel',
            'context' => 'Contexto',
            'message' => 'Mensaje',
            'user_id' => 'Usuario',
            'ip_address' => 'IP'
        );
    }
    
    /**
     * Obtener columnas ordenables
     * 
     * @return array Array de columnas ordenables
     */
    public function get_sortable_columns() {
        return array(
            'timestamp' => array('timestamp', true),
            'log_level' => array('log_level', false),
            'context' => array('context', false),
            'user_id' => array('user_id', false)
        );
    }
    
    /**
     * Obtener columnas ocultas por defecto
     * 
     * @return array Array de columnas ocultas
     */
    public function get_hidden_columns() {
        return array('ip_address');
    }
    
    /**
     * Obtener columnas con checkbox
     * 
     * @return array Array de columnas con checkbox
     */
    public function get_bulk_actions() {
        return array(
            'delete' => 'Eliminar seleccionados',
            'export' => 'Exportar seleccionados'
        );
    }
    
    /**
     * Renderizar checkbox
     * 
     * @param object $item Item actual
     * @return string HTML del checkbox
     */
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="log_ids[]" value="%s" />', $item->id);
    }
    
    /**
     * Renderizar columna timestamp
     * 
     * @param object $item Item actual
     * @return string HTML de la columna
     */
    public function column_timestamp($item) {
        $timestamp = strtotime($item->timestamp);
        $date = date('Y-m-d', $timestamp);
        $time = date('H:i:s', $timestamp);
        
        return sprintf(
            '<strong>%s</strong><br><small>%s</small>',
            esc_html($date),
            esc_html($time)
        );
    }
    
    /**
     * Renderizar columna log_level
     * 
     * @param object $item Item actual
     * @return string HTML de la columna
     */
    public function column_log_level($item) {
        $level = $item->log_level;
        $class = '';
        
        switch ($level) {
            case 'ERROR':
                $class = 'bcv-log-error';
                break;
            case 'WARNING':
                $class = 'bcv-log-warning';
                break;
            case 'SUCCESS':
                $class = 'bcv-log-success';
                break;
            case 'INFO':
                $class = 'bcv-log-info';
                break;
            case 'DEBUG':
                $class = 'bcv-log-debug';
                break;
        }
        
        return sprintf(
            '<span class="bcv-log-level %s">%s</span>',
            esc_attr($class),
            esc_html($level)
        );
    }
    
    /**
     * Renderizar columna context
     * 
     * @param object $item Item actual
     * @return string HTML de la columna
     */
    public function column_context($item) {
        return sprintf(
            '<span class="bcv-log-context">%s</span>',
            esc_html($item->context)
        );
    }
    
    /**
     * Renderizar columna message
     * 
     * @param object $item Item actual
     * @return string HTML de la columna
     */
    public function column_message($item) {
        $message = $item->message;
        
        // Truncar mensaje si es muy largo
        if (strlen($message) > 100) {
            $message = substr($message, 0, 100) . '...';
        }
        
        return sprintf(
            '<div class="bcv-log-message" title="%s">%s</div>',
            esc_attr($item->message),
            esc_html($message)
        );
    }
    
    /**
     * Renderizar columna user_id
     * 
     * @param object $item Item actual
     * @return string HTML de la columna
     */
    public function column_user_id($item) {
        if ($item->user_id && $item->user_id > 0) {
            $user = get_user_by('id', $item->user_id);
            if ($user) {
                return sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(get_edit_user_link($item->user_id)),
                    esc_html($user->display_name)
                );
            }
        }
        
        return '<span class="bcv-no-user">Sistema</span>';
    }
    
    /**
     * Renderizar columna ip_address
     * 
     * @param object $item Item actual
     * @return string HTML de la columna
     */
    public function column_ip_address($item) {
        return sprintf(
            '<code>%s</code>',
            esc_html($item->ip_address)
        );
    }
    
    /**
     * Preparar items para mostrar
     */
    public function prepare_items() {
        // Obtener parámetros de la URL
        $per_page = $this->get_items_per_page('logs_per_page', 20);
        $current_page = $this->get_pagenum();
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'timestamp';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Obtener filtros
        $log_level = isset($_GET['log_level']) ? sanitize_text_field($_GET['log_level']) : '';
        $context = isset($_GET['context']) ? sanitize_text_field($_GET['context']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Preparar argumentos para la consulta
        $args = array(
            'per_page' => $per_page,
            'page' => $current_page,
            'orderby' => $orderby,
            'order' => $order,
            'log_level' => $log_level,
            'context' => $context,
            'search' => $search
        );
        
        // Obtener logs
        $logs_data = BCV_Logger::get_logs($args);
        
        // Configurar paginación
        $this->set_pagination_args(array(
            'total_items' => $logs_data['total_items'],
            'per_page' => $per_page,
            'total_pages' => $logs_data['total_pages']
        ));
        
        // Configurar items
        $this->items = $logs_data['items'];
        
        // Configurar columnas
        $this->_column_headers = array(
            $this->get_columns(),
            $this->get_hidden_columns(),
            $this->get_sortable_columns()
        );
    }
    
    /**
     * Mostrar mensaje cuando no hay items
     */
    public function no_items() {
        echo '<div class="bcv-no-logs">';
        echo '<h3>No hay registros de actividad</h3>';
        echo '<p>Los logs aparecerán aquí cuando el modo de depuración esté activado y ocurran eventos en el plugin.</p>';
        echo '<p><a href="' . admin_url('admin.php?page=bcv-dolar-tracker') . '" class="button button-primary">Ir a Configuración</a></p>';
        echo '</div>';
    }
    
    /**
     * Mostrar filtros adicionales
     */
    public function extra_tablenav($which) {
        if ($which === 'top') {
            echo '<div class="alignleft actions">';
            
            // Filtro por nivel de log
            $log_levels = array(
                '' => 'Todos los niveles',
                'ERROR' => 'Errores',
                'WARNING' => 'Advertencias',
                'SUCCESS' => 'Éxitos',
                'INFO' => 'Información',
                'DEBUG' => 'Depuración'
            );
            
            echo '<select name="log_level" id="log_level_filter">';
            foreach ($log_levels as $value => $label) {
                $selected = isset($_GET['log_level']) && $_GET['log_level'] === $value ? 'selected' : '';
                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, esc_html($label));
            }
            echo '</select>';
            
            // Filtro por contexto
            $contexts = array(
                '' => 'Todos los contextos',
                'Ejecución Cron' => 'Ejecución Cron',
                'Llamada API BCV' => 'Llamada API BCV',
                'Base de Datos' => 'Base de Datos',
                'Ajustes Guardados' => 'Ajustes Guardados',
                'Scraping Manual' => 'Scraping Manual',
                'Limpieza de Datos' => 'Limpieza de Datos',
                'Integración WVP' => 'Integración WVP',
                'Seguridad' => 'Seguridad'
            );
            
            echo '<select name="context" id="context_filter">';
            foreach ($contexts as $value => $label) {
                $selected = isset($_GET['context']) && $_GET['context'] === $value ? 'selected' : '';
                echo sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, esc_html($label));
            }
            echo '</select>';
            
            echo '<input type="submit" name="filter_action" id="log-query-submit" class="button" value="Filtrar">';
            echo '</div>';
        }
    }
}
