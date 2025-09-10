<?php
/**
 * Clase para mostrar la tabla de precios del dólar usando WP_List_Table
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Cargar WP_List_Table si no está disponible
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class BCV_Prices_Table extends WP_List_Table {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => 'precio',
            'plural' => 'precios',
            'ajax' => false
        ));
    }
    
    /**
     * Definir columnas de la tabla
     */
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'datatime' => 'Fecha y Hora',
            'precio' => 'Precio (USD)',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado'
        );
    }
    
    /**
     * Definir columnas ordenables
     */
    public function get_sortable_columns() {
        return array(
            'id' => array('id', false),
            'datatime' => array('datatime', false),
            'precio' => array('precio', false),
            'created_at' => array('created_at', false),
            'updated_at' => array('updated_at', false)
        );
    }
    
    /**
     * Definir columnas ocultas
     */
    public function get_hidden_columns() {
        return array();
    }
    
    /**
     * Definir acciones en lote
     */
    public function get_bulk_actions() {
        return array(
            'delete' => 'Eliminar'
        );
    }
    
    /**
     * Procesar acciones en lote
     */
    public function process_bulk_action() {
        // Verificar nonce solo si existe
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
            return;
        }
        
        $action = $this->current_action();
        
        if ($action === 'delete') {
            $ids = isset($_REQUEST['precio']) ? array_map('intval', $_REQUEST['precio']) : array();
            
            if (!empty($ids)) {
                $database = new BCV_Database();
                $deleted = 0;
                
                foreach ($ids as $id) {
                    if ($database->delete_price($id)) {
                        $deleted++;
                    }
                }
                
                if ($deleted > 0) {
                    echo '<div class="notice notice-success"><p>' . sprintf('%d registro(s) eliminado(s) correctamente', $deleted) . '</p></div>';
                }
            }
        }
    }
    
    /**
     * Preparar elementos de la tabla
     */
    public function prepare_items() {
        // Procesar acciones en lote
        $this->process_bulk_action();
        
        // Obtener parámetros de paginación
        $per_page = $this->get_items_per_page('precios_per_page', 20);
        $current_page = $this->get_pagenum();
        
        // Obtener parámetros de búsqueda y ordenamiento
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'datatime';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
        
        // Validar ordenamiento
        $allowed_orderby = array('id', 'datatime', 'precio', 'created_at', 'updated_at');
        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'datatime';
        }
        
        $allowed_order = array('ASC', 'DESC');
        if (!in_array(strtoupper($order), $allowed_order)) {
            $order = 'DESC';
        }
        
        // Obtener datos de la base de datos
        $database = new BCV_Database();
        $data = $database->get_prices(array(
            'per_page' => $per_page,
            'page' => $current_page,
            'orderby' => $orderby,
            'order' => $order,
            'search' => $search
        ));
        
        // Configurar paginación
        $this->set_pagination_args(array(
            'total_items' => $data['total_items'],
            'per_page' => $per_page,
            'total_pages' => $data['total_pages']
        ));
        
        // Configurar elementos
        $this->items = $data['items'];
        
        // Configurar columnas
        $this->_column_headers = array(
            $this->get_columns(),
            $this->get_hidden_columns(),
            $this->get_sortable_columns()
        );
    }
    
    /**
     * Renderizar columna de checkbox
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="precio[]" value="%s" />',
            $item->id
        );
    }
    
    /**
     * Renderizar columna ID
     */
    public function column_id($item) {
        return '<strong>' . esc_html($item->id) . '</strong>';
    }
    
    /**
     * Renderizar columna fecha y hora
     */
    public function column_datatime($item) {
        $date = new DateTime($item->datatime);
        $formatted_date = $date->format('d/m/Y H:i:s');
        
        $actions = array(
            'edit' => sprintf(
                '<a href="%s">Editar</a>',
                add_query_arg(array(
                    'page' => 'bcv-prices-data',
                    'action' => 'edit',
                    'id' => $item->id
                ), admin_url('admin.php'))
            ),
            'delete' => sprintf(
                '<a href="%s" onclick="return confirm(\'¿Estás seguro de que quieres eliminar este registro?\')">Eliminar</a>',
                wp_nonce_url(
                    add_query_arg(array(
                        'page' => 'bcv-prices-data',
                        'action' => 'delete',
                        'id' => $item->id
                    ), admin_url('admin.php')),
                    'delete_precio_' . $item->id
                )
            )
        );
        
        return sprintf(
            '%1$s %2$s',
            '<strong>' . esc_html($formatted_date) . '</strong>',
            $this->row_actions($actions)
        );
    }
    
    /**
     * Renderizar columna precio
     */
    public function column_precio($item) {
        $formatted_price = number_format($item->precio, 4, ',', '.');
        $price_class = $this->get_price_change_class($item->id);
        
        return sprintf(
            '<span class="price-value %s">$ %s</span>',
            esc_attr($price_class),
            esc_html($formatted_price)
        );
    }
    
    /**
     * Renderizar columna fecha de creación
     */
    public function column_created_at($item) {
        $date = new DateTime($item->created_at);
        return esc_html($date->format('d/m/Y H:i:s'));
    }
    
    /**
     * Renderizar columna fecha de actualización
     */
    public function column_updated_at($item) {
        $date = new DateTime($item->updated_at);
        return esc_html($date->format('d/m/Y H:i:s'));
    }
    
    /**
     * Renderizar columna por defecto
     */
    public function column_default($item, $column_name) {
        return esc_html($item->$column_name);
    }
    
    /**
     * Obtener clase CSS para cambios de precio
     */
    private function get_price_change_class($id) {
        // Obtener precio anterior para comparar
        $database = new BCV_Database();
        $current_price = $database->get_price_by_id($id);
        
        if (!$current_price) {
            return '';
        }
        
        // Obtener precio anterior
        $previous_price = $database->get_previous_price($id);
        
        if (!$previous_price) {
            return '';
        }
        
        if ($current_price->precio > $previous_price->precio) {
            return 'price-up';
        } elseif ($current_price->precio < $previous_price->precio) {
            return 'price-down';
        } else {
            return 'price-stable';
        }
    }
    
    /**
     * Mostrar mensaje cuando no hay elementos
     */
    public function no_items() {
        echo '<tr><td colspan="6">No se encontraron precios del dólar.</td></tr>';
    }
    
    /**
     * Renderizar tabla extra
     */
    public function extra_tablenav($which) {
        if ($which === 'top') {
            echo '<div class="alignleft actions">';
            
            // Filtro de fechas
            echo '<select name="date_filter">';
            echo '<option value="">Todas las fechas</option>';
            echo '<option value="today">Hoy</option>';
            echo '<option value="yesterday">Ayer</option>';
            echo '<option value="week">Esta semana</option>';
            echo '<option value="month">Este mes</option>';
            echo '</select>';
            
            // Botón de aplicar filtros
            echo '<input type="submit" class="button" value="Aplicar Filtros">';
            
            echo '</div>';
        }
    }
    
    /**
     * Renderizar tabla
     */
    public function display() {
        // Mostrar formulario de búsqueda
        $this->search_box('Buscar precios', 'bcv-search-prices');
        
        // Mostrar tabla
        parent::display();
    }
}
