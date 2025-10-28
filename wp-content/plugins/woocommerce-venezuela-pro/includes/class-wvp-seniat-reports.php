<?php
/**
 * Clase para reportes fiscales completos para SENIAT Venezuela
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_SENIAT_Reports {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
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
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->fiscal_settings = get_option('wvp_fiscal_settings', array());
        
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
        add_action('wp_ajax_wvp_generate_seniat_report', array($this, 'generate_seniat_report_ajax'));
        add_action('wp_ajax_wvp_export_seniat_report', array($this, 'export_seniat_report_ajax'));
        
        // Añadir estilos para reportes
        add_action('admin_enqueue_scripts', array($this, 'enqueue_seniat_reports_scripts'));
    }
    
    /**
     * Añadir página de reportes SENIAT
     */
    public function add_seniat_reports_page() {
        add_submenu_page(
            'woocommerce',
            __('Reportes SENIAT Venezuela', 'wvp'),
            __('Reportes SENIAT', 'wvp'),
            'manage_woocommerce',
            'wvp-seniat-reports',
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
            <h1><?php esc_html_e('Reportes Fiscales SENIAT Venezuela', 'wvp'); ?></h1>
            <p class="description"><?php esc_html_e('Genera reportes fiscales completos para presentar al SENIAT según las normativas venezolanas.', 'wvp'); ?></p>
            
            <!-- Filtros de fecha -->
            <div class="wvp-report-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wvp-seniat-reports">
                    
                    <div class="wvp-date-filters">
                        <label for="date_from"><?php _e('Desde:', 'wvp'); ?></label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" required>
                        
                        <label for="date_to"><?php _e('Hasta:', 'wvp'); ?></label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" required>
                        
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php _e('Generar Reporte', 'wvp'); ?>
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
        <div class="wvp-fiscal-report-container">
            <!-- Encabezado del Reporte -->
            <div class="wvp-report-header">
                <div class="wvp-report-title">
                    <h2><?php _e('REPORTE FISCAL VENEZUELA', 'wvp'); ?></h2>
                    <h3><?php _e('Libro de Ventas - SENIAT', 'wvp'); ?></h3>
                </div>
                
                <div class="wvp-report-info">
                    <div class="wvp-company-info">
                        <p><strong><?php _e('Empresa:', 'wvp'); ?></strong> <?php echo esc_html($this->fiscal_settings['company_name'] ?? 'N/A'); ?></p>
                        <p><strong><?php _e('RIF:', 'wvp'); ?></strong> <?php echo esc_html($this->fiscal_settings['company_rif'] ?? 'N/A'); ?></p>
                        <p><strong><?php _e('Período:', 'wvp'); ?></strong> <?php echo date('d/m/Y', strtotime($date_from)) . ' - ' . date('d/m/Y', strtotime($date_to)); ?></p>
                        <p><strong><?php _e('Fecha de Generación:', 'wvp'); ?></strong> <?php echo current_time('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Resumen Ejecutivo -->
            <div class="wvp-executive-summary">
                <h3><?php _e('Resumen Ejecutivo', 'wvp'); ?></h3>
                <div class="wvp-summary-grid">
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('Total de Transacciones:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo number_format($report_data['total_transactions']); ?></span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('Total Ventas USD:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo wc_price($report_data['total_sales_usd']); ?></span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('Total Ventas VES:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo number_format($report_data['total_sales_ves'], 2, ',', '.'); ?> Bs.</span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('IVA Recaudado USD:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo wc_price($report_data['total_iva_usd']); ?></span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('IGTF Recaudado USD:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo wc_price($report_data['total_igtf_usd']); ?></span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('Tasa Promedio:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo number_format($report_data['average_rate'], 2, ',', '.'); ?> Bs./USD</span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('Ticket Promedio USD:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo wc_price($report_data['average_ticket_usd']); ?></span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('Ticket Promedio VES:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo number_format($report_data['average_ticket_ves'], 2, ',', '.'); ?> Bs.</span>
                    </div>
                    <div class="wvp-summary-item">
                        <span class="wvp-summary-label"><?php _e('Días de Actividad:', 'wvp'); ?></span>
                        <span class="wvp-summary-value"><?php echo $report_data['active_days']; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Análisis Financiero -->
            <div class="wvp-financial-analysis">
                <h3><?php _e('Análisis Financiero', 'wvp'); ?></h3>
                <div class="wvp-analysis-grid">
                    <div class="wvp-analysis-item">
                        <h4><?php _e('Rentabilidad', 'wvp'); ?></h4>
                        <?php 
                        $iva_margin = $report_data['total_sales_usd'] > 0 ? ($report_data['total_iva_usd'] / $report_data['total_sales_usd']) * 100 : 0;
                        $igtf_margin = $report_data['total_sales_usd'] > 0 ? ($report_data['total_igtf_usd'] / $report_data['total_sales_usd']) * 100 : 0;
                        ?>
                        <p><strong><?php _e('Margen de IVA:', 'wvp'); ?></strong> <?php echo number_format($iva_margin, 2); ?>%</p>
                        <p><strong><?php _e('Margen de IGTF:', 'wvp'); ?></strong> <?php echo number_format($igtf_margin, 2); ?>%</p>
                        <p><strong><?php _e('Total Impuestos:', 'wvp'); ?></strong> <?php echo wc_price($report_data['total_iva_usd'] + $report_data['total_igtf_usd']); ?></p>
                    </div>
                    
                    <div class="wvp-analysis-item">
                        <h4><?php _e('Tendencias', 'wvp'); ?></h4>
                        <p><strong><?php _e('Mejor Día:', 'wvp'); ?></strong> <?php echo $report_data['best_day']['date'] . ' (' . wc_price($report_data['best_day']['amount']) . ')'; ?></p>
                        <p><strong><?php _e('Peor Día:', 'wvp'); ?></strong> <?php echo $report_data['worst_day']['date'] . ' (' . wc_price($report_data['worst_day']['amount']) . ')'; ?></p>
                        <p><strong><?php _e('Promedio Diario:', 'wvp'); ?></strong> <?php echo wc_price($report_data['daily_average']); ?></p>
                    </div>
                    
                    <div class="wvp-analysis-item">
                        <h4><?php _e('Clientes', 'wvp'); ?></h4>
                        <p><strong><?php _e('Clientes Únicos:', 'wvp'); ?></strong> <?php echo $report_data['unique_customers']; ?></p>
                        <p><strong><?php _e('Compras por Cliente:', 'wvp'); ?></strong> <?php echo number_format($report_data['purchases_per_customer'], 1); ?></p>
                        <p><strong><?php _e('Cliente Frecuente:', 'wvp'); ?></strong> <?php echo $report_data['frequent_customer']['name'] . ' (' . $report_data['frequent_customer']['purchases'] . ' compras)'; ?></p>
                    </div>
                    
                    <div class="wvp-analysis-item">
                        <h4><?php _e('Moneda', 'wvp'); ?></h4>
                        <p><strong><?php _e('Tasa Mínima:', 'wvp'); ?></strong> <?php echo number_format($report_data['min_rate'], 2, ',', '.'); ?> Bs./USD</p>
                        <p><strong><?php _e('Tasa Máxima:', 'wvp'); ?></strong> <?php echo number_format($report_data['max_rate'], 2, ',', '.'); ?> Bs./USD</p>
                        <p><strong><?php _e('Variación:', 'wvp'); ?></strong> <?php echo number_format($report_data['rate_variation'], 2); ?>%</p>
                    </div>
                </div>
            </div>
            
            <!-- Detalle de Transacciones -->
            <div class="wvp-transactions-detail">
                <h3><?php _e('Detalle de Transacciones', 'wvp'); ?></h3>
                <div class="wvp-table-container">
                    <table class="wvp-fiscal-table">
                        <thead>
                            <tr>
                                <th><?php _e('Fecha', 'wvp'); ?></th>
                                <th><?php _e('N° Control', 'wvp'); ?></th>
                                <th><?php _e('Cliente', 'wvp'); ?></th>
                                <th><?php _e('RIF/Cédula', 'wvp'); ?></th>
                                <th><?php _e('Subtotal USD', 'wvp'); ?></th>
                                <th><?php _e('IVA USD', 'wvp'); ?></th>
                                <th><?php _e('IGTF USD', 'wvp'); ?></th>
                                <th><?php _e('Total USD', 'wvp'); ?></th>
                                <th><?php _e('Total VES', 'wvp'); ?></th>
                                <th><?php _e('Tasa', 'wvp'); ?></th>
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
                            <tr class="wvp-total-row">
                                <td colspan="4"><strong><?php _e('TOTALES:', 'wvp'); ?></strong></td>
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
            
            <!-- Desglose por Mes -->
            <div class="wvp-monthly-breakdown">
                <h3><?php _e('Desglose por Mes', 'wvp'); ?></h3>
                <div class="wvp-table-container">
                    <table class="wvp-fiscal-table">
                        <thead>
                            <tr>
                                <th><?php _e('Mes', 'wvp'); ?></th>
                                <th><?php _e('Transacciones', 'wvp'); ?></th>
                                <th><?php _e('Ventas USD', 'wvp'); ?></th>
                                <th><?php _e('IVA USD', 'wvp'); ?></th>
                                <th><?php _e('IGTF USD', 'wvp'); ?></th>
                                <th><?php _e('Total USD', 'wvp'); ?></th>
                                <th><?php _e('Total VES', 'wvp'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data['monthly_breakdown'] as $month => $data): ?>
                            <tr>
                                <td><?php echo esc_html($month); ?></td>
                                <td><?php echo number_format($data['transactions']); ?></td>
                                <td><?php echo wc_price($data['sales_usd']); ?></td>
                                <td><?php echo wc_price($data['iva_usd']); ?></td>
                                <td><?php echo wc_price($data['igtf_usd']); ?></td>
                                <td><?php echo wc_price($data['total_usd']); ?></td>
                                <td><?php echo number_format($data['total_ves'], 2, ',', '.'); ?> Bs.</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Información Adicional -->
            <div class="wvp-additional-info">
                <h3><?php _e('Información Adicional', 'wvp'); ?></h3>
                <div class="wvp-info-grid">
                    <div class="wvp-info-item">
                        <h4><?php _e('Tipo de Contribuyente:', 'wvp'); ?></h4>
                        <p><?php echo esc_html($this->fiscal_settings['taxpayer_type'] ?? 'No especificado'); ?></p>
                    </div>
                    <div class="wvp-info-item">
                        <h4><?php _e('Actividad Económica:', 'wvp'); ?></h4>
                        <p><?php echo esc_html($this->fiscal_settings['company_activity'] ?? 'No especificada'); ?></p>
                    </div>
                    <div class="wvp-info-item">
                        <h4><?php _e('Dirección Fiscal:', 'wvp'); ?></h4>
                        <p><?php echo esc_html($this->fiscal_settings['company_address'] ?? 'No especificada'); ?></p>
                    </div>
                    <div class="wvp-info-item">
                        <h4><?php _e('Teléfono:', 'wvp'); ?></h4>
                        <p><?php echo esc_html($this->fiscal_settings['company_phone'] ?? 'No especificado'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Botones de Acción -->
            <div class="wvp-report-actions">
                <button type="button" class="button button-primary" onclick="wvpPrintReport()">
                    <span class="dashicons dashicons-printer"></span>
                    <?php _e('Imprimir Reporte', 'wvp'); ?>
                </button>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wvp_export_seniat_report&date_from=' . $date_from . '&date_to=' . $date_to), 'wvp_export_seniat_report'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Exportar a Excel', 'wvp'); ?>
                </a>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wvp_export_seniat_report&format=csv&date_from=' . $date_from . '&date_to=' . $date_to), 'wvp_export_seniat_report'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-media-spreadsheet"></span>
                    <?php _e('Exportar a CSV', 'wvp'); ?>
                </a>
            </div>
        </div>
        
        <style>
        .wvp-fiscal-report-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .wvp-report-header {
            border-bottom: 3px solid #0073aa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .wvp-report-title h2 {
            color: #0073aa;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .wvp-report-title h3 {
            color: #666;
            margin: 0;
            font-size: 18px;
        }
        
        .wvp-company-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .wvp-company-info p {
            margin: 5px 0;
        }
        
        .wvp-executive-summary {
            background: #f0f8ff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .wvp-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .wvp-summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #fff;
            border-radius: 3px;
            border-left: 4px solid #0073aa;
        }
        
        .wvp-summary-label {
            font-weight: bold;
            color: #333;
        }
        
        .wvp-summary-value {
            font-weight: bold;
            color: #0073aa;
            font-size: 16px;
        }
        
        .wvp-table-container {
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .wvp-fiscal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .wvp-fiscal-table th,
        .wvp-fiscal-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .wvp-fiscal-table th {
            background: #0073aa;
            color: #fff;
            font-weight: bold;
        }
        
        .wvp-fiscal-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .wvp-total-row {
            background: #e7f3ff !important;
            font-weight: bold;
        }
        
        .wvp-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .wvp-info-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        
        .wvp-info-item h4 {
            margin: 0 0 10px 0;
            color: #0073aa;
        }
        
        .wvp-info-item p {
            margin: 0;
            color: #666;
        }
        
        .wvp-report-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
        }
        
        .wvp-report-actions .button {
            margin: 0 10px;
        }
        
        @media print {
            .wvp-report-actions {
                display: none;
            }
            
            .wvp-fiscal-report-container {
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
        // No incluir wc-processing o wc-on-hold ya que son pagos no confirmados
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
                LEFT JOIN {$wpdb->postmeta} pm_rate_date ON p.ID = pm_rate.post_id AND pm_rate_date.meta_key = '_exchange_rate_date'
                WHERE p.post_type = 'shop_order'
                AND p.post_status = 'wc-completed'
                AND p.post_date >= %s 
                AND p.post_date <= %s
                ORDER BY p.post_date ASC
            ", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
        }
        
        // Procesar datos
        $processed_transactions = array();
        $monthly_data = array();
        $daily_data = array();
        $customer_data = array();
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
            
            // Agrupar por mes
            $month = date('Y-m', strtotime($transaction->order_date));
            if (!isset($monthly_data[$month])) {
                $monthly_data[$month] = array(
                    'transactions' => 0,
                    'sales_usd' => 0,
                    'iva_usd' => 0,
                    'igtf_usd' => 0,
                    'total_usd' => 0,
                    'total_ves' => 0
                );
            }
            
            $monthly_data[$month]['transactions']++;
            $monthly_data[$month]['sales_usd'] += $subtotal_usd;
            $monthly_data[$month]['iva_usd'] += $iva_usd;
            $monthly_data[$month]['igtf_usd'] += $igtf_usd;
            $monthly_data[$month]['total_usd'] += $total_usd;
            $monthly_data[$month]['total_ves'] += $total_ves;
            
            // Agrupar por día
            $day = date('Y-m-d', strtotime($transaction->order_date));
            if (!isset($daily_data[$day])) {
                $daily_data[$day] = array(
                    'transactions' => 0,
                    'total_usd' => 0
                );
            }
            $daily_data[$day]['transactions']++;
            $daily_data[$day]['total_usd'] += $total_usd;
            
            // Agrupar por cliente
            $customer_key = $transaction->customer_cedula ?: $transaction->customer_rif ?: 'N/A';
            if (!isset($customer_data[$customer_key])) {
                $customer_data[$customer_key] = array(
                    'name' => $transaction->customer_name ?: 'Cliente',
                    'purchases' => 0,
                    'total_usd' => 0
                );
            }
            $customer_data[$customer_key]['purchases']++;
            $customer_data[$customer_key]['total_usd'] += $total_usd;
        }
        
        // Calcular análisis adicionales
        $total_transactions = count($processed_transactions);
        $average_ticket_usd = $total_transactions > 0 ? $total_sales_usd / $total_transactions : 0;
        $average_ticket_ves = $total_transactions > 0 ? $total_sales_ves / $total_transactions : 0;
        $average_rate = count($rates) > 0 ? array_sum($rates) / count($rates) : 0;
        $min_rate = count($rates) > 0 ? min($rates) : 0;
        $max_rate = count($rates) > 0 ? max($rates) : 0;
        $rate_variation = $min_rate > 0 ? (($max_rate - $min_rate) / $min_rate) * 100 : 0;
        
        // Encontrar mejor y peor día
        $best_day = array('date' => 'N/A', 'amount' => 0);
        $worst_day = array('date' => 'N/A', 'amount' => PHP_FLOAT_MAX);
        foreach ($daily_data as $day => $data) {
            if ($data['total_usd'] > $best_day['amount']) {
                $best_day = array('date' => date('d/m/Y', strtotime($day)), 'amount' => $data['total_usd']);
            }
            if ($data['total_usd'] < $worst_day['amount']) {
                $worst_day = array('date' => date('d/m/Y', strtotime($day)), 'amount' => $data['total_usd']);
            }
        }
        
        // Encontrar cliente más frecuente
        $frequent_customer = array('name' => 'N/A', 'purchases' => 0);
        foreach ($customer_data as $customer) {
            if ($customer['purchases'] > $frequent_customer['purchases']) {
                $frequent_customer = $customer;
            }
        }
        
        $active_days = count($daily_data);
        $unique_customers = count($customer_data);
        $purchases_per_customer = $unique_customers > 0 ? $total_transactions / $unique_customers : 0;
        $daily_average = $active_days > 0 ? $total_sales_usd / $active_days : 0;
        
        return array(
            'total_transactions' => $total_transactions,
            'total_sales_usd' => $total_sales_usd,
            'total_iva_usd' => $total_iva_usd,
            'total_igtf_usd' => $total_igtf_usd,
            'total_general_usd' => $total_sales_usd + $total_iva_usd + $total_igtf_usd,
            'total_sales_ves' => $total_sales_ves,
            'average_rate' => $average_rate,
            'min_rate' => $min_rate,
            'max_rate' => $max_rate,
            'rate_variation' => $rate_variation,
            'average_ticket_usd' => $average_ticket_usd,
            'average_ticket_ves' => $average_ticket_ves,
            'active_days' => $active_days,
            'unique_customers' => $unique_customers,
            'purchases_per_customer' => $purchases_per_customer,
            'best_day' => $best_day,
            'worst_day' => $worst_day,
            'frequent_customer' => $frequent_customer,
            'daily_average' => $daily_average,
            'transactions' => $processed_transactions,
            'monthly_breakdown' => $monthly_data
        );
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
        if (!wp_verify_nonce($_GET['_wpnonce'], 'wvp_export_seniat_report')) {
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
     * Cargar scripts para reportes SENIAT
     */
    public function enqueue_seniat_reports_scripts($hook) {
        if ($hook !== 'woocommerce_page_wvp-seniat-reports') {
            return;
        }
        
        wp_enqueue_style('wvp-seniat-reports', WVP_PLUGIN_URL . 'assets/css/seniat-reports.css', array(), WVP_VERSION);
        wp_enqueue_script('wvp-seniat-reports', WVP_PLUGIN_URL . 'assets/js/seniat-reports.js', array('jquery'), WVP_VERSION, true);
    }
}
