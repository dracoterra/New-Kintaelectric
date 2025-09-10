<?php
/**
 * Clase para generar reportes fiscales venezolanos
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Reports {
    
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
        // Añadir menú de reportes
        add_action('admin_menu', array($this, 'add_reports_menu'));
        
        // Manejar AJAX para generar reportes
        add_action('wp_ajax_wvp_generate_sales_book', array($this, 'generate_sales_book'));
        add_action('wp_ajax_wvp_generate_igtf_report', array($this, 'generate_igtf_report'));
        
        // Enqueue scripts y estilos
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Añadir menú de reportes fiscales
     */
    public function add_reports_menu() {
        add_submenu_page(
            'woocommerce',
            __('Reportes Fiscales Vzla', 'wvp'),
            __('Reportes Fiscales Vzla', 'wvp'),
            'manage_woocommerce',
            'wvp-reports',
            array($this, 'render_reports_page')
        );
    }
    
    /**
     * Renderizar página de reportes
     */
    public function render_reports_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Reportes Fiscales Venezuela', 'wvp'); ?></h1>
            
            <div class="wvp-reports-container">
                <div class="wvp-reports-header">
                    <h2><?php _e('Generar Reportes', 'wvp'); ?></h2>
                    <p><?php _e('Seleccione el rango de fechas y el tipo de reporte a generar.', 'wvp'); ?></p>
                </div>
                
                <form id="wvp-reports-form" class="wvp-reports-form">
                    <div class="wvp-date-range">
                        <label for="wvp-date-from"><?php _e('Desde:', 'wvp'); ?></label>
                        <input type="date" id="wvp-date-from" name="date_from" required>
                        
                        <label for="wvp-date-to"><?php _e('Hasta:', 'wvp'); ?></label>
                        <input type="date" id="wvp-date-to" name="date_to" required>
                    </div>
                    
                    <div class="wvp-report-buttons">
                        <button type="button" id="wvp-generate-sales-book" class="button button-primary">
                            <?php _e('Generar Libro de Ventas', 'wvp'); ?>
                        </button>
                        
                        <button type="button" id="wvp-generate-igtf-report" class="button button-primary">
                            <?php _e('Generar Reporte de IGTF', 'wvp'); ?>
                        </button>
                    </div>
                </form>
                
                <div id="wvp-reports-results" class="wvp-reports-results" style="display: none;">
                    <h3><?php _e('Resultados del Reporte', 'wvp'); ?></h3>
                    <div id="wvp-reports-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue scripts y estilos administrativos
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'woocommerce_page_wvp-reports') {
            return;
        }
        
        wp_enqueue_script(
            'wvp-reports-admin',
            WVP_PLUGIN_URL . 'assets/js/admin-reports.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wvp-reports-admin',
            WVP_PLUGIN_URL . 'assets/css/admin-reports.css',
            array(),
            WVP_VERSION
        );
        
        wp_localize_script('wvp-reports-admin', 'wvp_reports', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_reports_nonce'),
            'i18n' => array(
                'generating' => __('Generando reporte...', 'wvp'),
                'error' => __('Error al generar el reporte', 'wvp'),
                'no_data' => __('No hay datos para el rango seleccionado', 'wvp')
            )
        ));
    }
    
    /**
     * Generar Libro de Ventas
     */
    public function generate_sales_book() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_reports_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        
        // Obtener pedidos completados en el rango de fechas
        $orders = $this->get_completed_orders($date_from, $date_to);
        
        if (empty($orders)) {
            wp_send_json_error(array('message' => __('No hay pedidos completados en el rango seleccionado', 'wvp')));
        }
        
        // Generar datos del reporte
        $report_data = $this->prepare_sales_book_data($orders);
        
        // Generar HTML del reporte
        $html = $this->render_sales_book_html($report_data, $date_from, $date_to);
        
        wp_send_json_success(array('html' => $html, 'data' => $report_data));
    }
    
    /**
     * Generar Reporte de IGTF
     */
    public function generate_igtf_report() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_reports_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        
        // Obtener pedidos con IGTF en el rango de fechas
        $orders = $this->get_orders_with_igtf($date_from, $date_to);
        
        if (empty($orders)) {
            wp_send_json_error(array('message' => __('No hay pedidos con IGTF en el rango seleccionado', 'wvp')));
        }
        
        // Generar datos del reporte
        $report_data = $this->prepare_igtf_report_data($orders);
        
        // Generar HTML del reporte
        $html = $this->render_igtf_report_html($report_data, $date_from, $date_to);
        
        wp_send_json_success(array('html' => $html, 'data' => $report_data));
    }
    
    /**
     * Obtener pedidos completados en rango de fechas
     */
    private function get_completed_orders($date_from, $date_to) {
        $args = array(
            'status' => 'completed',
            'date_created' => $date_from . '...' . $date_to,
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'ASC'
        );
        
        return wc_get_orders($args);
    }
    
    /**
     * Obtener pedidos con IGTF en rango de fechas
     */
    private function get_orders_with_igtf($date_from, $date_to) {
        $args = array(
            'status' => array('completed', 'processing'),
            'date_created' => $date_from . '...' . $date_to,
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_igtf_amount',
                    'value' => 0,
                    'compare' => '>'
                )
            )
        );
        
        return wc_get_orders($args);
    }
    
    /**
     * Preparar datos del Libro de Ventas
     */
    private function prepare_sales_book_data($orders) {
        $data = array();
        
        foreach ($orders as $order) {
            $cedula_rif = $order->get_meta('_billing_cedula_rif');
            $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
            $igtf_amount = $order->get_meta('_igtf_amount');
            
            // Convertir total a bolívares
            $total_usd = $order->get_total();
            $total_bs = $bcv_rate ? $total_usd * $bcv_rate : 0;
            
            // Calcular IVA (asumiendo 16% en Venezuela)
            $iva_rate = 0.16;
            $base_imponible = $total_bs / (1 + $iva_rate);
            $monto_iva = $total_bs - $base_imponible;
            
            $data[] = array(
                'fecha' => $order->get_date_created()->format('d/m/Y'),
                'cedula_rif' => $cedula_rif ?: 'N/A',
                'nombre_cliente' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'numero_factura' => $order->get_id(),
                'numero_control' => $order->get_meta('_seniat_control_number') ?: '',
                'total_venta_iva' => $total_bs,
                'ventas_exentas' => 0, // Por ahora siempre 0
                'base_imponible' => $base_imponible,
                'monto_iva' => $monto_iva,
                'bcv_rate' => $bcv_rate
            );
        }
        
        return $data;
    }
    
    /**
     * Preparar datos del Reporte de IGTF
     */
    private function prepare_igtf_report_data($orders) {
        $data = array();
        
        foreach ($orders as $order) {
            $igtf_amount = $order->get_meta('_igtf_amount');
            $payment_method = $order->get_payment_method_title();
            
            // Calcular monto base (total - IGTF)
            $total_usd = $order->get_total();
            $monto_base = $total_usd - $igtf_amount;
            
            $data[] = array(
                'fecha' => $order->get_date_created()->format('d/m/Y'),
                'numero_pedido' => $order->get_id(),
                'metodo_pago' => $payment_method,
                'monto_base' => $monto_base,
                'monto_igtf' => $igtf_amount
            );
        }
        
        return $data;
    }
    
    /**
     * Renderizar HTML del Libro de Ventas
     */
    private function render_sales_book_html($data, $date_from, $date_to) {
        ob_start();
        ?>
        <div class="wvp-report-header">
            <h3><?php _e('Libro de Ventas', 'wvp'); ?></h3>
            <p><strong><?php _e('Período:', 'wvp'); ?></strong> <?php echo $date_from; ?> - <?php echo $date_to; ?></p>
            <button type="button" id="wvp-export-sales-csv" class="button button-secondary">
                <?php _e('Exportar a CSV', 'wvp'); ?>
            </button>
        </div>
        
        <div class="wvp-report-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Fecha', 'wvp'); ?></th>
                        <th><?php _e('RIF/Cédula', 'wvp'); ?></th>
                        <th><?php _e('Nombre del Cliente', 'wvp'); ?></th>
                        <th><?php _e('N° Factura', 'wvp'); ?></th>
                        <th><?php _e('N° Control', 'wvp'); ?></th>
                        <th><?php _e('Total Venta c/IVA (Bs.)', 'wvp'); ?></th>
                        <th><?php _e('Ventas Exentas (Bs.)', 'wvp'); ?></th>
                        <th><?php _e('Base Imponible (Bs.)', 'wvp'); ?></th>
                        <th><?php _e('Monto IVA (Bs.)', 'wvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row['fecha']); ?></td>
                        <td><?php echo esc_html($row['cedula_rif']); ?></td>
                        <td><?php echo esc_html($row['nombre_cliente']); ?></td>
                        <td><?php echo esc_html($row['numero_factura']); ?></td>
                        <td><?php echo esc_html($row['numero_control']); ?></td>
                        <td><?php echo number_format($row['total_venta_iva'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($row['ventas_exentas'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($row['base_imponible'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($row['monto_iva'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizar HTML del Reporte de IGTF
     */
    private function render_igtf_report_html($data, $date_from, $date_to) {
        ob_start();
        ?>
        <div class="wvp-report-header">
            <h3><?php _e('Reporte de IGTF', 'wvp'); ?></h3>
            <p><strong><?php _e('Período:', 'wvp'); ?></strong> <?php echo $date_from; ?> - <?php echo $date_to; ?></p>
            <button type="button" id="wvp-export-igtf-csv" class="button button-secondary">
                <?php _e('Exportar a CSV', 'wvp'); ?>
            </button>
        </div>
        
        <div class="wvp-report-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Fecha', 'wvp'); ?></th>
                        <th><?php _e('N° Pedido', 'wvp'); ?></th>
                        <th><?php _e('Método de Pago', 'wvp'); ?></th>
                        <th><?php _e('Monto Base (USD)', 'wvp'); ?></th>
                        <th><?php _e('Monto IGTF (USD)', 'wvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row['fecha']); ?></td>
                        <td><?php echo esc_html($row['numero_pedido']); ?></td>
                        <td><?php echo esc_html($row['metodo_pago']); ?></td>
                        <td><?php echo '$' . number_format($row['monto_base'], 2, ',', '.'); ?></td>
                        <td><?php echo '$' . number_format($row['monto_igtf'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }
}
