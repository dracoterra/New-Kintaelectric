<?php
/**
 * Clase para reportes fiscales de IVA e IGTF
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Fiscal_Reports {
    
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
        // Añadir reporte fiscal a WooCommerce
        add_filter('woocommerce_admin_reports', array($this, 'add_fiscal_report'));
        
        // Añadir página de reportes fiscales
        add_action('admin_menu', array($this, 'add_fiscal_reports_page'));
        
        // AJAX para generar reportes
        add_action('wp_ajax_wvp_generate_fiscal_report', array($this, 'generate_fiscal_report_ajax'));
        
        // Añadir estilos para reportes
        add_action('admin_enqueue_scripts', array($this, 'enqueue_fiscal_reports_scripts'));
    }
    
    /**
     * Añadir reporte fiscal a WooCommerce
     * 
     * @param array $reports Reportes existentes
     * @return array Reportes modificados
     */
    public function add_fiscal_report($reports) {
        $reports['venezuela'] = array(
            'title' => __('Venezuela', 'wvp'),
            'reports' => array(
                'fiscal' => array(
                    'title' => __('Reporte Fiscal', 'wvp'),
                    'description' => __('Reporte de IVA e IGTF recaudados', 'wvp'),
                    'hide_title' => true,
                    'callback' => array($this, 'display_fiscal_report')
                )
            )
        );
        
        return $reports;
    }
    
    /**
     * Añadir página de reportes fiscales
     */
    public function add_fiscal_reports_page() {
        add_submenu_page(
            'woocommerce',
            __('Reportes Fiscales Venezuela', 'wvp'),
            __('Reportes Fiscales', 'wvp'),
            'manage_woocommerce',
            'wvp-fiscal-reports',
            array($this, 'display_fiscal_reports_page')
        );
    }
    
    /**
     * Mostrar página de reportes fiscales
     */
    public function display_fiscal_reports_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Reportes Fiscales Venezuela', 'wvp'); ?></h1>
            
            <div class="wvp-fiscal-reports-container">
                <div class="wvp-filters">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="wvp-fiscal-reports">
                        
                        <div class="wvp-filter-row">
                            <label for="date_from"><?php esc_html_e('Desde:', 'wvp'); ?></label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($_GET['date_from'] ?? ''); ?>">
                            
                            <label for="date_to"><?php esc_html_e('Hasta:', 'wvp'); ?></label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($_GET['date_to'] ?? ''); ?>">
                            
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Generar Reporte', 'wvp'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="wvp-report-content">
                    <?php $this->display_fiscal_report(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Mostrar reporte fiscal
     */
    public function display_fiscal_report() {
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-d');
        
        // Obtener datos del reporte
        $report_data = $this->generate_fiscal_report($date_from, $date_to);
        
        ?>
        <div class="wvp-fiscal-report">
            <div class="wvp-report-header">
                <h2><?php esc_html_e('Reporte Fiscal Venezuela', 'wvp'); ?></h2>
                <p class="wvp-report-period">
                    <?php 
                    printf(
                        esc_html__('Período: %s al %s', 'wvp'),
                        date('d/m/Y', strtotime($date_from)),
                        date('d/m/Y', strtotime($date_to))
                    );
                    ?>
                </p>
            </div>
            
            <div class="wvp-report-summary">
                <div class="wvp-summary-cards">
                    <div class="wvp-summary-card">
                        <h3><?php esc_html_e('Total de Pedidos', 'wvp'); ?></h3>
                        <span class="wvp-summary-value"><?php echo esc_html($report_data['total_orders']); ?></span>
                    </div>
                    
                    <div class="wvp-summary-card">
                        <h3><?php esc_html_e('Total IVA (USD)', 'wvp'); ?></h3>
                        <span class="wvp-summary-value"><?php echo wc_price($report_data['total_tax']); ?></span>
                    </div>
                    
                    <div class="wvp-summary-card">
                        <h3><?php esc_html_e('Total IGTF (USD)', 'wvp'); ?></h3>
                        <span class="wvp-summary-value"><?php echo wc_price($report_data['total_igtf']); ?></span>
                    </div>
                    
                    <div class="wvp-summary-card">
                        <h3><?php esc_html_e('Total General (USD)', 'wvp'); ?></h3>
                        <span class="wvp-summary-value"><?php echo wc_price($report_data['total_general']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="wvp-report-details">
                <h3><?php esc_html_e('Desglose por Mes', 'wvp'); ?></h3>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Mes', 'wvp'); ?></th>
                            <th><?php esc_html_e('Pedidos', 'wvp'); ?></th>
                            <th><?php esc_html_e('IVA (USD)', 'wvp'); ?></th>
                            <th><?php esc_html_e('IGTF (USD)', 'wvp'); ?></th>
                            <th><?php esc_html_e('Total (USD)', 'wvp'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data['monthly_breakdown'] as $month => $data): ?>
                            <tr>
                                <td><?php echo esc_html($month); ?></td>
                                <td><?php echo esc_html($data['orders']); ?></td>
                                <td><?php echo wc_price($data['tax']); ?></td>
                                <td><?php echo wc_price($data['igtf']); ?></td>
                                <td><?php echo wc_price($data['total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="wvp-report-actions">
                <button type="button" class="button button-secondary" onclick="wvpExportReport()">
                    <?php esc_html_e('Exportar a CSV', 'wvp'); ?>
                </button>
                <button type="button" class="button button-secondary" onclick="wvpPrintReport()">
                    <?php esc_html_e('Imprimir', 'wvp'); ?>
                </button>
            </div>
        </div>
        
        <style>
            .wvp-fiscal-reports-container {
                margin-top: 20px;
            }
            
            .wvp-filters {
                background: #f9f9f9;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            
            .wvp-filter-row {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .wvp-filter-row label {
                font-weight: bold;
            }
            
            .wvp-report-header {
                background: #007cba;
                color: white;
                padding: 20px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            
            .wvp-report-header h2 {
                margin: 0 0 10px 0;
            }
            
            .wvp-report-period {
                margin: 0;
                opacity: 0.9;
            }
            
            .wvp-summary-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .wvp-summary-card {
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
                text-align: center;
            }
            
            .wvp-summary-card h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                color: #666;
            }
            
            .wvp-summary-value {
                font-size: 24px;
                font-weight: bold;
                color: #007cba;
            }
            
            .wvp-report-details {
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .wvp-report-details h3 {
                margin: 0 0 15px 0;
            }
            
            .wvp-report-actions {
                text-align: right;
            }
            
            .wvp-report-actions .button {
                margin-left: 10px;
            }
        </style>
        
        <script>
            function wvpExportReport() {
                // Implementar exportación a CSV
                alert('<?php esc_html_e('Función de exportación en desarrollo', 'wvp'); ?>');
            }
            
            function wvpPrintReport() {
                window.print();
            }
        </script>
        <?php
    }
    
    /**
     * Generar reporte fiscal
     * 
     * @param string $date_from Fecha desde
     * @param string $date_to Fecha hasta
     * @return array Datos del reporte
     */
    public function generate_fiscal_report($date_from, $date_to) {
        global $wpdb;
        
        // Obtener pedidos del período (compatible con HPOS)
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // Usar HPOS
            $orders = $wpdb->get_results($wpdb->prepare("
                SELECT id, date_created_gmt, status
                FROM {$wpdb->prefix}wc_orders
                WHERE status = 'wc-completed'
                AND date_created_gmt >= %s
                AND date_created_gmt <= %s
                ORDER BY date_created_gmt ASC
            ", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
        } else {
            // Usar método tradicional
            $orders = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_date, p.post_status
                FROM {$wpdb->posts} p
                WHERE p.post_type = 'shop_order'
                AND p.post_status = 'wc-completed'
                AND p.post_date >= %s
                AND p.post_date <= %s
                ORDER BY p.post_date ASC
            ", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
        }
        
        $report_data = array(
            'total_orders' => 0,
            'total_tax' => 0,
            'total_igtf' => 0,
            'total_general' => 0,
            'monthly_breakdown' => array()
        );
        
        foreach ($orders as $order_data) {
            // Obtener ID del pedido según el método usado
            if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
                $order_id = $order_data->id;
            } else {
                $order_id = $order_data->ID;
            }
            
            // Obtener el objeto pedido
            $order = wc_get_order($order_id);
            if (!$order) {
                continue;
            }
            
            $report_data['total_orders']++;
            
            // Obtener IVA
            $tax_total = $order->get_total_tax();
            $report_data['total_tax'] += $tax_total;
            
            // Obtener IGTF
            $igtf_total = 0;
            foreach ($order->get_fees() as $fee) {
                if (strpos($fee->get_name(), 'IGTF') !== false) {
                    $igtf_total += $fee->get_total();
                }
            }
            $report_data['total_igtf'] += $igtf_total;
            
            // Total general
            $order_total = $order->get_total();
            $report_data['total_general'] += $order_total;
            
            // Agrupar por mes
            $order_date = isset($order_data->post_date) ? $order_data->post_date : $order->get_date_created()->format('Y-m-d H:i:s');
            $month = date('Y-m', strtotime($order_date));
            if (!isset($report_data['monthly_breakdown'][$month])) {
                $report_data['monthly_breakdown'][$month] = array(
                    'orders' => 0,
                    'tax' => 0,
                    'igtf' => 0,
                    'total' => 0
                );
            }
            
            $report_data['monthly_breakdown'][$month]['orders']++;
            $report_data['monthly_breakdown'][$month]['tax'] += $tax_total;
            $report_data['monthly_breakdown'][$month]['igtf'] += $igtf_total;
            $report_data['monthly_breakdown'][$month]['total'] += $order_total;
        }
        
        return $report_data;
    }
    
    /**
     * Generar reporte fiscal vía AJAX
     */
    public function generate_fiscal_report_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_fiscal_report_nonce')) {
            wp_die('Acceso denegado');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Permisos insuficientes');
        }
        
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        
        $report_data = $this->generate_fiscal_report($date_from, $date_to);
        
        wp_send_json_success($report_data);
    }
    
    /**
     * Cargar scripts para reportes fiscales
     * 
     * @param string $hook Hook actual
     */
    public function enqueue_fiscal_reports_scripts($hook) {
        if ($hook !== 'woocommerce_page_wvp-fiscal-reports') {
            return;
        }
        
        wp_enqueue_script(
            'wvp-fiscal-reports',
            WVP_PLUGIN_URL . 'assets/js/fiscal-reports.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        wp_localize_script('wvp-fiscal-reports', 'wvp_fiscal_reports', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_fiscal_report_nonce'),
            'i18n' => array(
                'generating' => __('Generando reporte...', 'wvp'),
                'error' => __('Error al generar el reporte', 'wvp')
            )
        ));
    }
    
    /**
     * Obtener estadísticas fiscales rápidas
     * 
     * @param string $period Período (today, week, month, year)
     * @return array Estadísticas
     */
    public function get_quick_fiscal_stats($period = 'month') {
        $date_ranges = array(
            'today' => array(
                'from' => date('Y-m-d'),
                'to' => date('Y-m-d')
            ),
            'week' => array(
                'from' => date('Y-m-d', strtotime('monday this week')),
                'to' => date('Y-m-d', strtotime('sunday this week'))
            ),
            'month' => array(
                'from' => date('Y-m-01'),
                'to' => date('Y-m-t')
            ),
            'year' => array(
                'from' => date('Y-01-01'),
                'to' => date('Y-12-31')
            )
        );
        
        if (!isset($date_ranges[$period])) {
            $period = 'month';
        }
        
        $range = $date_ranges[$period];
        $report_data = $this->generate_fiscal_report($range['from'], $range['to']);
        
        return array(
            'period' => $period,
            'orders' => $report_data['total_orders'],
            'tax' => $report_data['total_tax'],
            'igtf' => $report_data['total_igtf'],
            'total' => $report_data['total_general']
        );
    }
}
