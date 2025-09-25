<?php
/**
 * Clase para reportes fiscales completos para SENIAT Venezuela
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WCVS_SENIAT_Reports {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WCVS_Core
     */
    private $core;
    
    /**
     * Configuración fiscal
     * 
     * @var array
     */
    private $fiscal_settings;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->core = WCVS_Core::get_instance();
        $this->fiscal_settings = get_option('wcvs_fiscal_settings', array());
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Añadir página de reportes SENIAT
        add_action('admin_menu', array($this, 'add_seniat_reports_page'));
        
        // AJAX para generar reportes
        add_action('wp_ajax_wcvs_generate_seniat_report', array($this, 'generate_seniat_report_ajax'));
        add_action('wp_ajax_wcvs_export_seniat_report', array($this, 'export_seniat_report_ajax'));
        
        // Añadir estilos para reportes
        add_action('admin_enqueue_scripts', array($this, 'enqueue_seniat_reports_scripts'));
    }
    
    /**
     * Añadir página de reportes SENIAT
     */
    public function add_seniat_reports_page() {
        add_submenu_page(
            'wcvs-dashboard',
            __('Reportes SENIAT Venezuela', 'woocommerce-venezuela-pro-2025'),
            __('Reportes SENIAT', 'woocommerce-venezuela-pro-2025'),
            'manage_woocommerce',
            'wcvs-seniat-reports',
            array($this, 'display_seniat_reports_page')
        );
    }
    
    /**
     * Mostrar página de reportes SENIAT
     */
    public function display_seniat_reports_page() {
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-d');
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Reportes Fiscales SENIAT Venezuela', 'woocommerce-venezuela-pro-2025'); ?></h1>
            <p class="description"><?php esc_html_e('Genera reportes fiscales completos para presentar al SENIAT según las normativas venezolanas.', 'woocommerce-venezuela-pro-2025'); ?></p>
            
            <!-- Filtros de fecha -->
            <div class="wcvs-report-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wcvs-seniat-reports">
                    
                    <div class="wcvs-date-filters">
                        <label for="date_from"><?php _e('Desde:', 'woocommerce-venezuela-pro-2025'); ?></label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" required>
                        
                        <label for="date_to"><?php _e('Hasta:', 'woocommerce-venezuela-pro-2025'); ?></label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" required>
                        
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php _e('Generar Reporte', 'woocommerce-venezuela-pro-2025'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <?php
            // Mostrar reporte si hay fechas seleccionadas
            if ($date_from && $date_to) {
                $this->display_complete_fiscal_report($date_from, $date_to);
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Mostrar reporte fiscal completo
     */
    private function display_complete_fiscal_report($date_from, $date_to) {
        $report_data = $this->generate_complete_fiscal_report($date_from, $date_to);
        
        ?>
        <div class="wcvs-fiscal-report-container">
            <!-- Encabezado del Reporte -->
            <div class="wcvs-report-header">
                <div class="wcvs-report-title">
                    <h2><?php _e('REPORTE FISCAL VENEZUELA', 'woocommerce-venezuela-pro-2025'); ?></h2>
                    <h3><?php _e('Libro de Ventas - SENIAT', 'woocommerce-venezuela-pro-2025'); ?></h3>
                </div>
                
                <div class="wcvs-report-info">
                    <div class="wcvs-company-info">
                        <p><strong><?php _e('Empresa:', 'woocommerce-venezuela-pro-2025'); ?></strong> <?php echo esc_html($this->fiscal_settings['company_name'] ?? 'N/A'); ?></p>
                        <p><strong><?php _e('RIF:', 'woocommerce-venezuela-pro-2025'); ?></strong> <?php echo esc_html($this->fiscal_settings['company_rif'] ?? 'N/A'); ?></p>
                        <p><strong><?php _e('Período:', 'woocommerce-venezuela-pro-2025'); ?></strong> <?php echo date('d/m/Y', strtotime($date_from)) . ' - ' . date('d/m/Y', strtotime($date_to)); ?></p>
                        <p><strong><?php _e('Fecha de Generación:', 'woocommerce-venezuela-pro-2025'); ?></strong> <?php echo current_time('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Resumen Ejecutivo -->
            <div class="wcvs-executive-summary">
                <h3><?php _e('Resumen Ejecutivo', 'woocommerce-venezuela-pro-2025'); ?></h3>
                <div class="wcvs-summary-grid">
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Total de Transacciones:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo number_format($report_data['total_transactions']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Total Ventas USD:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo wc_price($report_data['total_sales_usd']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Total Ventas VES:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo number_format($report_data['total_sales_ves'], 2, ',', '.'); ?> Bs.</span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('IVA Recaudado USD:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo wc_price($report_data['total_iva_usd']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('IGTF Recaudado USD:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo wc_price($report_data['total_igtf_usd']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Tasa Promedio:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo number_format($report_data['average_rate'], 2, ',', '.'); ?> Bs./USD</span>
                    </div>
                </div>
            </div>
            
            <!-- Detalle de Transacciones -->
            <div class="wcvs-transactions-detail">
                <h3><?php _e('Detalle de Transacciones', 'woocommerce-venezuela-pro-2025'); ?></h3>
                <div class="wcvs-table-container">
                    <table class="wcvs-fiscal-table">
                        <thead>
                            <tr>
                                <th><?php _e('Fecha', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('N° Control', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('Cliente', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('RIF/Cédula', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('Subtotal USD', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('IVA USD', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('IGTF USD', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('Total USD', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('Total VES', 'woocommerce-venezuela-pro-2025'); ?></th>
                                <th><?php _e('Tasa', 'woocommerce-venezuela-pro-2025'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data['transactions'] as $transaction): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($transaction['date'])); ?></td>
                                <td><?php echo esc_html($transaction['control_number']); ?></td>
                                <td><?php echo esc_html($transaction['customer_name']); ?></td>
                                <td><?php echo esc_html($transaction['customer_id']); ?></td>
                                <td><?php echo wc_price($transaction['subtotal_usd']); ?></td>
                                <td><?php echo wc_price($transaction['iva_usd']); ?></td>
                                <td><?php echo wc_price($transaction['igtf_usd']); ?></td>
                                <td><?php echo wc_price($transaction['total_usd']); ?></td>
                                <td><?php echo number_format($transaction['total_ves'], 2, ',', '.'); ?> Bs.</td>
                                <td><?php echo number_format($transaction['exchange_rate'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="wcvs-total-row">
                                <td colspan="4"><strong><?php _e('TOTALES:', 'woocommerce-venezuela-pro-2025'); ?></strong></td>
                                <td><strong><?php echo wc_price($report_data['total_sales_usd']); ?></strong></td>
                                <td><strong><?php echo wc_price($report_data['total_iva_usd']); ?></strong></td>
                                <td><strong><?php echo wc_price($report_data['total_igtf_usd']); ?></strong></td>
                                <td><strong><?php echo wc_price($report_data['total_general_usd']); ?></strong></td>
                                <td><strong><?php echo number_format($report_data['total_sales_ves'], 2, ',', '.'); ?> Bs.</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Botones de Acción -->
            <div class="wcvs-report-actions">
                <button type="button" class="button button-primary" onclick="wcvsPrintReport()">
                    <span class="dashicons dashicons-printer"></span>
                    <?php _e('Imprimir Reporte', 'woocommerce-venezuela-pro-2025'); ?>
                </button>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wcvs_export_seniat_report&date_from=' . $date_from . '&date_to=' . $date_to), 'wcvs_export_seniat_report'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Exportar a Excel', 'woocommerce-venezuela-pro-2025'); ?>
                </a>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wcvs_export_seniat_report&format=csv&date_from=' . $date_from . '&date_to=' . $date_to), 'wcvs_export_seniat_report'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-media-spreadsheet"></span>
                    <?php _e('Exportar a CSV', 'woocommerce-venezuela-pro-2025'); ?>
                </a>
            </div>
        </div>
        
        <style>
        .wcvs-fiscal-report-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .wcvs-report-header {
            border-bottom: 3px solid #0073aa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .wcvs-report-title h2 {
            color: #0073aa;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .wcvs-report-title h3 {
            color: #666;
            margin: 0;
            font-size: 18px;
        }
        
        .wcvs-company-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .wcvs-company-info p {
            margin: 5px 0;
        }
        
        .wcvs-executive-summary {
            background: #f0f8ff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .wcvs-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .wcvs-summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #fff;
            border-radius: 3px;
            border-left: 4px solid #0073aa;
        }
        
        .wcvs-summary-label {
            font-weight: bold;
            color: #333;
        }
        
        .wcvs-summary-value {
            font-weight: bold;
            color: #0073aa;
            font-size: 16px;
        }
        
        .wcvs-table-container {
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .wcvs-fiscal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .wcvs-fiscal-table th,
        .wcvs-fiscal-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .wcvs-fiscal-table th {
            background: #0073aa;
            color: #fff;
            font-weight: bold;
        }
        
        .wcvs-fiscal-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .wcvs-total-row {
            background: #e7f3ff !important;
            font-weight: bold;
        }
        
        .wcvs-report-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
        }
        
        .wcvs-report-actions .button {
            margin: 0 10px;
        }
        
        @media print {
            .wcvs-report-actions {
                display: none;
            }
            
            .wcvs-fiscal-report-container {
                border: none;
                padding: 0;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Generar reporte fiscal completo
     */
    private function generate_complete_fiscal_report($date_from, $date_to) {
        global $wpdb;
        
        // Obtener transacciones del período
        // IMPORTANTE: Solo incluir órdenes completadas (wc-completed) para SENIAT
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // HPOS
            $transactions = $wpdb->get_results($wpdb->prepare("
                SELECT 
                    o.id as order_id,
                    o.date_created_gmt as order_date,
                    o.status,
                    o.total_amount as total_usd,
                    om_control.meta_value as control_number,
                    om_cedula.meta_value as customer_cedula,
                    om_rif.meta_value as customer_rif,
                    om_nombre.meta_value as customer_name,
                    om_rate.meta_value as exchange_rate,
                    om_rate_date.meta_value as exchange_date
                FROM {$wpdb->prefix}wc_orders o
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_control ON o.id = om_control.order_id AND om_control.meta_key = '_seniat_control_number'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_cedula ON o.id = om_cedula.order_id AND om_cedula.meta_key = '_billing_cedula'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_rif ON o.id = om_rif.order_id AND om_rif.meta_key = '_billing_rif'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_nombre ON o.id = om_nombre.order_id AND om_nombre.meta_key = '_billing_nombre_completo'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_rate ON o.id = om_rate.order_id AND om_rate.meta_key = '_exchange_rate_at_purchase'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_rate_date ON o.id = om_rate_date.order_id AND om_rate_date.meta_key = '_exchange_rate_date'
                WHERE o.type = 'shop_order'
                AND o.status = 'wc-completed'
                AND o.date_created_gmt >= %s 
                AND o.date_created_gmt <= %s
                ORDER BY o.date_created_gmt ASC
            ", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
        } else {
            // Posts tradicional
            $transactions = $wpdb->get_results($wpdb->prepare("
                SELECT 
                    p.ID as order_id,
                    p.post_date as order_date,
                    p.post_status as status,
                    pm_total.meta_value as total_usd,
                    pm_control.meta_value as control_number,
                    pm_cedula.meta_value as customer_cedula,
                    pm_rif.meta_value as customer_rif,
                    pm_nombre.meta_value as customer_name,
                    pm_rate.meta_value as exchange_rate,
                    pm_rate_date.meta_value as exchange_date
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
                LEFT JOIN {$wpdb->postmeta} pm_control ON p.ID = pm_control.post_id AND pm_control.meta_key = '_seniat_control_number'
                LEFT JOIN {$wpdb->postmeta} pm_cedula ON p.ID = pm_cedula.post_id AND pm_cedula.meta_key = '_billing_cedula'
                LEFT JOIN {$wpdb->postmeta} pm_rif ON p.ID = pm_rif.post_id AND pm_rif.meta_key = '_billing_rif'
                LEFT JOIN {$wpdb->postmeta} pm_nombre ON p.ID = pm_nombre.post_id AND pm_nombre.meta_key = '_billing_nombre_completo'
                LEFT JOIN {$wpdb->postmeta} pm_rate ON p.ID = pm_rate.post_id AND pm_rate.meta_key = '_exchange_rate_at_purchase'
                LEFT JOIN {$wpdb->postmeta} pm_rate_date ON p.ID = pm_rate_date.post_id AND pm_rate_date.meta_key = '_exchange_rate_date'
                WHERE p.post_type = 'shop_order'
                AND p.post_status = 'wc-completed'
                AND p.post_date >= %s 
                AND p.post_date <= %s
                ORDER BY p.post_date ASC
            ", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
        }
        
        // Procesar datos
        $processed_transactions = array();
        $total_sales_usd = 0;
        $total_iva_usd = 0;
        $total_igtf_usd = 0;
        $total_sales_ves = 0;
        $rates = array();
        
        foreach ($transactions as $transaction) {
            $total_usd = floatval($transaction->total_usd);
            $exchange_rate = floatval($transaction->exchange_rate) ?: 1;
            $rates[] = $exchange_rate;
            
            // Calcular impuestos
            $subtotal_usd = $total_usd / 1.19; // Asumiendo 16% IVA + 3% IGTF
            $iva_usd = $subtotal_usd * 0.16;
            $igtf_usd = $subtotal_usd * 0.03;
            
            $total_ves = $total_usd * $exchange_rate;
            
            // Generar número de control si no existe
            $control_number = $transaction->control_number;
            if (empty($control_number)) {
                $control_number = $this->generate_control_number($transaction->order_id, $transaction->order_date);
                // Guardar el número de control generado
                update_post_meta($transaction->order_id, '_seniat_control_number', $control_number);
            }
            
            $processed_transaction = array(
                'date' => $transaction->order_date,
                'control_number' => $control_number,
                'customer_name' => $this->get_customer_name($transaction),
                'customer_id' => $this->get_customer_id($transaction),
                'subtotal_usd' => $subtotal_usd,
                'iva_usd' => $iva_usd,
                'igtf_usd' => $igtf_usd,
                'total_usd' => $total_usd,
                'total_ves' => $total_ves,
                'exchange_rate' => $exchange_rate
            );
            
            $processed_transactions[] = $processed_transaction;
            
            // Acumular totales
            $total_sales_usd += $subtotal_usd;
            $total_iva_usd += $iva_usd;
            $total_igtf_usd += $igtf_usd;
            $total_sales_ves += $total_ves;
        }
        
        // Calcular análisis adicionales
        $total_transactions = count($processed_transactions);
        $average_rate = count($rates) > 0 ? array_sum($rates) / count($rates) : 0;
        
        return array(
            'total_transactions' => $total_transactions,
            'total_sales_usd' => $total_sales_usd,
            'total_iva_usd' => $total_iva_usd,
            'total_igtf_usd' => $total_igtf_usd,
            'total_general_usd' => $total_sales_usd + $total_iva_usd + $total_igtf_usd,
            'total_sales_ves' => $total_sales_ves,
            'average_rate' => $average_rate,
            'transactions' => $processed_transactions
        );
    }
    
    /**
     * Generar número de control para SENIAT
     */
    private function generate_control_number($order_id, $order_date) {
        // Formato: YYYY-MM-DD-ORDERID (ej: 2025-09-12-1234)
        $date = date('Y-m-d', strtotime($order_date));
        $control_number = $date . '-' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
        
        return $control_number;
    }
    
    /**
     * Obtener nombre del cliente con fallback
     */
    private function get_customer_name($transaction) {
        if (!empty($transaction->customer_name)) {
            return $transaction->customer_name;
        }
        
        // Intentar obtener el nombre del pedido
        $order = wc_get_order($transaction->order_id);
        if ($order) {
            $first_name = $order->get_billing_first_name();
            $last_name = $order->get_billing_last_name();
            if ($first_name || $last_name) {
                return trim($first_name . ' ' . $last_name);
            }
        }
        
        return 'Cliente';
    }
    
    /**
     * Obtener ID del cliente (cédula o RIF) con fallback
     */
    private function get_customer_id($transaction) {
        // Prioridad: Cédula primero, luego RIF
        if (!empty($transaction->customer_cedula)) {
            return $transaction->customer_cedula;
        }
        
        if (!empty($transaction->customer_rif)) {
            return $transaction->customer_rif;
        }
        
        // Intentar obtener del pedido directamente
        $order = wc_get_order($transaction->order_id);
        if ($order) {
            $cedula = $order->get_meta('_billing_cedula');
            if (!empty($cedula)) {
                return $cedula;
            }
            
            $rif = $order->get_meta('_billing_rif');
            if (!empty($rif)) {
                return $rif;
            }
        }
        
        return 'N/A';
    }
    
    /**
     * Exportar reporte SENIAT vía AJAX
     */
    public function export_seniat_report_ajax() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'wcvs_export_seniat_report')) {
            wp_die('Nonce inválido');
        }
        
        $date_from = sanitize_text_field($_GET['date_from']);
        $date_to = sanitize_text_field($_GET['date_to']);
        $format = sanitize_text_field($_GET['format'] ?? 'excel');
        
        $this->export_seniat_report($date_from, $date_to, $format);
    }
    
    /**
     * Exportar reporte SENIAT
     */
    private function export_seniat_report($date_from, $date_to, $format = 'excel') {
        $report_data = $this->generate_complete_fiscal_report($date_from, $date_to);
        
        if ($format === 'csv') {
            $this->export_to_csv($report_data, $date_from, $date_to);
        } else {
            $this->export_to_excel($report_data, $date_from, $date_to);
        }
    }
    
    /**
     * Exportar a CSV
     */
    private function export_to_csv($report_data, $date_from, $date_to) {
        $filename = 'reporte_seniat_' . $date_from . '_' . $date_to . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
        
        // Encabezados
        fputcsv($output, array(
            'Fecha',
            'N° Control',
            'Cliente',
            'RIF/Cédula',
            'Subtotal USD',
            'IVA USD',
            'IGTF USD',
            'Total USD',
            'Total VES',
            'Tasa Cambio'
        ));
        
        // Datos
        foreach ($report_data['transactions'] as $transaction) {
            fputcsv($output, array(
                date('d/m/Y', strtotime($transaction['date'])),
                $transaction['control_number'],
                $transaction['customer_name'],
                $transaction['customer_id'],
                number_format($transaction['subtotal_usd'], 2, '.', ''),
                number_format($transaction['iva_usd'], 2, '.', ''),
                number_format($transaction['igtf_usd'], 2, '.', ''),
                number_format($transaction['total_usd'], 2, '.', ''),
                number_format($transaction['total_ves'], 2, '.', ''),
                number_format($transaction['exchange_rate'], 2, '.', '')
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar a Excel (formato simple)
     */
    private function export_to_excel($report_data, $date_from, $date_to) {
        $filename = 'reporte_seniat_' . $date_from . '_' . $date_to . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        echo '<table border="1">';
        echo '<tr><th colspan="10">REPORTE FISCAL VENEZUELA - SENIAT</th></tr>';
        echo '<tr><th colspan="10">Período: ' . date('d/m/Y', strtotime($date_from)) . ' - ' . date('d/m/Y', strtotime($date_to)) . '</th></tr>';
        echo '<tr><th colspan="10">Fecha de Generación: ' . current_time('d/m/Y H:i:s') . '</th></tr>';
        echo '<tr></tr>';
        
        // Encabezados
        echo '<tr>';
        echo '<th>Fecha</th>';
        echo '<th>N° Control</th>';
        echo '<th>Cliente</th>';
        echo '<th>RIF/Cédula</th>';
        echo '<th>Subtotal USD</th>';
        echo '<th>IVA USD</th>';
        echo '<th>IGTF USD</th>';
        echo '<th>Total USD</th>';
        echo '<th>Total VES</th>';
        echo '<th>Tasa Cambio</th>';
        echo '</tr>';
        
        // Datos
        foreach ($report_data['transactions'] as $transaction) {
            echo '<tr>';
            echo '<td>' . date('d/m/Y', strtotime($transaction['date'])) . '</td>';
            echo '<td>' . $transaction['control_number'] . '</td>';
            echo '<td>' . $transaction['customer_name'] . '</td>';
            echo '<td>' . $transaction['customer_id'] . '</td>';
            echo '<td>' . number_format($transaction['subtotal_usd'], 2, '.', '') . '</td>';
            echo '<td>' . number_format($transaction['iva_usd'], 2, '.', '') . '</td>';
            echo '<td>' . number_format($transaction['igtf_usd'], 2, '.', '') . '</td>';
            echo '<td>' . number_format($transaction['total_usd'], 2, '.', '') . '</td>';
            echo '<td>' . number_format($transaction['total_ves'], 2, '.', '') . '</td>';
            echo '<td>' . number_format($transaction['exchange_rate'], 2, '.', '') . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
    }
    
    /**
     * Generar reporte SENIAT vía AJAX
     */
    public function generate_seniat_report_ajax() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_seniat_report_nonce')) {
            wp_die('Nonce inválido');
        }
        
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        
        $report_data = $this->generate_complete_fiscal_report($date_from, $date_to);
        
        wp_send_json_success($report_data);
    }
    
    /**
     * Cargar scripts para reportes SENIAT
     */
    public function enqueue_seniat_reports_scripts($hook) {
        if ($hook !== 'venezuela-suite_page_wcvs-seniat-reports') {
            return;
        }
        
        wp_enqueue_style('wcvs-seniat-reports', plugin_dir_url(__FILE__) . '../admin/css/seniat-reports.css', array(), WCVS_VERSION);
        wp_enqueue_script('wcvs-seniat-reports', plugin_dir_url(__FILE__) . '../admin/js/seniat-reports.js', array('jquery'), WCVS_VERSION, true);
        
        wp_localize_script('wcvs-seniat-reports', 'wcvs_seniat_reports', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcvs_seniat_report_nonce'),
            'i18n' => array(
                'generating' => __('Generando reporte...', 'woocommerce-venezuela-pro-2025'),
                'error' => __('Error al generar el reporte', 'woocommerce-venezuela-pro-2025')
            )
        ));
    }
}
