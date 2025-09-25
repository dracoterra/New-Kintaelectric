<?php
/**
 * Clase para Sistema de Estadísticas y Reportes
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WCVS_Statistics {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WCVS_Core
     */
    private $core;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->core = WCVS_Core::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Añadir página de estadísticas
        add_action('admin_menu', array($this, 'add_statistics_page'));
        
        // AJAX para estadísticas
        add_action('wp_ajax_wcvs_get_statistics', array($this, 'ajax_get_statistics'));
        add_action('wp_ajax_wcvs_export_statistics', array($this, 'ajax_export_statistics'));
        add_action('wp_ajax_wcvs_get_dashboard_stats', array($this, 'ajax_get_dashboard_stats'));
        
        // Añadir estilos para estadísticas
        add_action('admin_enqueue_scripts', array($this, 'enqueue_statistics_scripts'));
    }
    
    /**
     * Añadir página de estadísticas
     */
    public function add_statistics_page() {
        add_submenu_page(
            'wcvs-dashboard',
            __('Estadísticas Venezuela', 'woocommerce-venezuela-pro-2025'),
            __('Estadísticas', 'woocommerce-venezuela-pro-2025'),
            'manage_woocommerce',
            'wcvs-statistics',
            array($this, 'display_statistics_page')
        );
    }
    
    /**
     * Mostrar página de estadísticas
     */
    public function display_statistics_page() {
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-d');
        $period = $_GET['period'] ?? 'month';
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('📊 Estadísticas Venezuela Suite', 'woocommerce-venezuela-pro-2025'); ?></h1>
            <p class="description"><?php esc_html_e('Análisis completo de ventas, conversiones y rendimiento de tu tienda venezolana.', 'woocommerce-venezuela-pro-2025'); ?></p>
            
            <!-- Filtros de fecha -->
            <div class="wcvs-statistics-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wcvs-statistics">
                    
                    <div class="wcvs-date-filters">
                        <label for="date_from"><?php _e('Desde:', 'woocommerce-venezuela-pro-2025'); ?></label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" required>
                        
                        <label for="date_to"><?php _e('Hasta:', 'woocommerce-venezuela-pro-2025'); ?></label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" required>
                        
                        <label for="period"><?php _e('Período:', 'woocommerce-venezuela-pro-2025'); ?></label>
                        <select id="period" name="period">
                            <option value="day" <?php selected($period, 'day'); ?>><?php _e('Diario', 'woocommerce-venezuela-pro-2025'); ?></option>
                            <option value="week" <?php selected($period, 'week'); ?>><?php _e('Semanal', 'woocommerce-venezuela-pro-2025'); ?></option>
                            <option value="month" <?php selected($period, 'month'); ?>><?php _e('Mensual', 'woocommerce-venezuela-pro-2025'); ?></option>
                            <option value="year" <?php selected($period, 'year'); ?>><?php _e('Anual', 'woocommerce-venezuela-pro-2025'); ?></option>
                        </select>
                        
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php _e('Generar Estadísticas', 'woocommerce-venezuela-pro-2025'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <?php
            // Mostrar estadísticas si hay fechas seleccionadas
            if ($date_from && $date_to) {
                $this->display_complete_statistics($date_from, $date_to, $period);
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Mostrar estadísticas completas
     */
    private function display_complete_statistics($date_from, $date_to, $period) {
        $stats = $this->generate_complete_statistics($date_from, $date_to, $period);
        
        ?>
        <div class="wcvs-statistics-container">
            <!-- Resumen Ejecutivo -->
            <div class="wcvs-executive-summary">
                <h2><?php _e('Resumen Ejecutivo', 'woocommerce-venezuela-pro-2025'); ?></h2>
                <div class="wcvs-summary-grid">
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Total Ventas USD:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo wc_price($stats['total_sales_usd']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Total Ventas VES:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo number_format($stats['total_sales_ves'], 2, ',', '.'); ?> Bs.</span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Total Pedidos:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo number_format($stats['total_orders']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('Promedio por Pedido:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo wc_price($stats['average_order_value']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('IVA Recaudado:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo wc_price($stats['total_iva']); ?></span>
                    </div>
                    <div class="wcvs-summary-item">
                        <span class="wcvs-summary-label"><?php _e('IGTF Recaudado:', 'woocommerce-venezuela-pro-2025'); ?></span>
                        <span class="wcvs-summary-value"><?php echo wc_price($stats['total_igtf']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos y Análisis -->
            <div class="wcvs-charts-section">
                <div class="wcvs-chart-container">
                    <h3><?php _e('Tendencia de Ventas', 'woocommerce-venezuela-pro-2025'); ?></h3>
                    <div class="wcvs-chart" id="sales-trend-chart">
                        <!-- Gráfico se carga aquí -->
                    </div>
                </div>
                
                <div class="wcvs-chart-container">
                    <h3><?php _e('Métodos de Pago', 'woocommerce-venezuela-pro-2025'); ?></h3>
                    <div class="wcvs-chart" id="payment-methods-chart">
                        <!-- Gráfico se carga aquí -->
                    </div>
                </div>
            </div>
            
            <!-- Análisis Detallado -->
            <div class="wcvs-detailed-analysis">
                <h3><?php _e('Análisis Detallado', 'woocommerce-venezuela-pro-2025'); ?></h3>
                
                <div class="wcvs-analysis-grid">
                    <!-- Productos Más Vendidos -->
                    <div class="wcvs-analysis-item">
                        <h4><?php _e('Productos Más Vendidos', 'woocommerce-venezuela-pro-2025'); ?></h4>
                        <div class="wcvs-product-list">
                            <?php foreach ($stats['top_products'] as $product): ?>
                            <div class="wcvs-product-item">
                                <span class="wcvs-product-name"><?php echo esc_html($product['name']); ?></span>
                                <span class="wcvs-product-qty"><?php echo $product['quantity']; ?> vendidos</span>
                                <span class="wcvs-product-revenue"><?php echo wc_price($product['revenue']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Clientes Principales -->
                    <div class="wcvs-analysis-item">
                        <h4><?php _e('Clientes Principales', 'woocommerce-venezuela-pro-2025'); ?></h4>
                        <div class="wcvs-customer-list">
                            <?php foreach ($stats['top_customers'] as $customer): ?>
                            <div class="wcvs-customer-item">
                                <span class="wcvs-customer-name"><?php echo esc_html($customer['name']); ?></span>
                                <span class="wcvs-customer-orders"><?php echo $customer['orders']; ?> pedidos</span>
                                <span class="wcvs-customer-total"><?php echo wc_price($customer['total']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Métodos de Pago -->
                    <div class="wcvs-analysis-item">
                        <h4><?php _e('Métodos de Pago', 'woocommerce-venezuela-pro-2025'); ?></h4>
                        <div class="wcvs-payment-list">
                            <?php foreach ($stats['payment_methods'] as $method): ?>
                            <div class="wcvs-payment-item">
                                <span class="wcvs-payment-name"><?php echo esc_html($method['name']); ?></span>
                                <span class="wcvs-payment-count"><?php echo $method['count']; ?> usos</span>
                                <span class="wcvs-payment-percentage"><?php echo $method['percentage']; ?>%</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Métodos de Envío -->
                    <div class="wcvs-analysis-item">
                        <h4><?php _e('Métodos de Envío', 'woocommerce-venezuela-pro-2025'); ?></h4>
                        <div class="wcvs-shipping-list">
                            <?php foreach ($stats['shipping_methods'] as $method): ?>
                            <div class="wcvs-shipping-item">
                                <span class="wcvs-shipping-name"><?php echo esc_html($method['name']); ?></span>
                                <span class="wcvs-shipping-count"><?php echo $method['count']; ?> usos</span>
                                <span class="wcvs-shipping-percentage"><?php echo $method['percentage']; ?>%</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botones de Acción -->
            <div class="wcvs-statistics-actions">
                <button type="button" class="button button-primary" onclick="wcvsPrintStatistics()">
                    <span class="dashicons dashicons-printer"></span>
                    <?php _e('Imprimir Reporte', 'woocommerce-venezuela-pro-2025'); ?>
                </button>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wcvs_export_statistics&date_from=' . $date_from . '&date_to=' . $date_to . '&period=' . $period), 'wcvs_export_statistics'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Exportar a Excel', 'woocommerce-venezuela-pro-2025'); ?>
                </a>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wcvs_export_statistics&format=csv&date_from=' . $date_from . '&date_to=' . $date_to . '&period=' . $period), 'wcvs_export_statistics'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-media-spreadsheet"></span>
                    <?php _e('Exportar a CSV', 'woocommerce-venezuela-pro-2025'); ?>
                </a>
            </div>
        </div>
        
        <script>
        // Datos para gráficos
        var wcvsChartData = <?php echo json_encode($stats['chart_data']); ?>;
        </script>
        <?php
    }
    
    /**
     * Generar estadísticas completas
     */
    private function generate_complete_statistics($date_from, $date_to, $period) {
        global $wpdb;
        
        // Obtener pedidos del período
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // HPOS
            $orders = $wpdb->get_results($wpdb->prepare("
                SELECT 
                    o.id as order_id,
                    o.date_created_gmt as order_date,
                    o.status,
                    o.total_amount as total_usd,
                    om_rate.meta_value as exchange_rate,
                    om_items.meta_value as order_items
                FROM {$wpdb->prefix}wc_orders o
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_rate ON o.id = om_rate.order_id AND om_rate.meta_key = '_exchange_rate_at_purchase'
                LEFT JOIN {$wpdb->prefix}wc_orders_meta om_items ON o.id = om_items.order_id AND om_items.meta_key = '_order_items'
                WHERE o.type = 'shop_order'
                AND o.status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
                AND o.date_created_gmt >= %s 
                AND o.date_created_gmt <= %s
                ORDER BY o.date_created_gmt ASC
            ", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
        } else {
            // Posts tradicional
            $orders = $wpdb->get_results($wpdb->prepare("
                SELECT 
                    p.ID as order_id,
                    p.post_date as order_date,
                    p.post_status as status,
                    pm_total.meta_value as total_usd,
                    pm_rate.meta_value as exchange_rate,
                    pm_items.meta_value as order_items
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
                LEFT JOIN {$wpdb->postmeta} pm_rate ON p.ID = pm_rate.post_id AND pm_rate.meta_key = '_exchange_rate_at_purchase'
                LEFT JOIN {$wpdb->postmeta} pm_items ON p.ID = pm_items.post_id AND pm_items.meta_key = '_order_items'
                WHERE p.post_type = 'shop_order'
                AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
                AND p.post_date >= %s 
                AND p.post_date <= %s
                ORDER BY p.post_date ASC
            ", $date_from . ' 00:00:00', $date_to . ' 23:59:59'));
        }
        
        // Procesar datos
        $total_sales_usd = 0;
        $total_sales_ves = 0;
        $total_orders = count($orders);
        $total_iva = 0;
        $total_igtf = 0;
        $products_sold = array();
        $customers_data = array();
        $payment_methods = array();
        $shipping_methods = array();
        $chart_data = array();
        
        foreach ($orders as $order) {
            $total_usd = floatval($order->total_usd);
            $exchange_rate = floatval($order->exchange_rate) ?: 1;
            $total_ves = $total_usd * $exchange_rate;
            
            $total_sales_usd += $total_usd;
            $total_sales_ves += $total_ves;
            
            // Calcular impuestos
            $iva = $total_usd * 0.16;
            $igtf = $total_usd * 0.03;
            $total_iva += $iva;
            $total_igtf += $igtf;
            
            // Datos para gráficos
            $date_key = date('Y-m-d', strtotime($order->order_date));
            if (!isset($chart_data[$date_key])) {
                $chart_data[$date_key] = array(
                    'date' => $date_key,
                    'sales_usd' => 0,
                    'sales_ves' => 0,
                    'orders' => 0
                );
            }
            $chart_data[$date_key]['sales_usd'] += $total_usd;
            $chart_data[$date_key]['sales_ves'] += $total_ves;
            $chart_data[$date_key]['orders'] += 1;
            
            // Obtener datos del pedido
            $wc_order = wc_get_order($order->order_id);
            if ($wc_order) {
                // Productos vendidos
                foreach ($wc_order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $product_name = $item->get_name();
                    $quantity = $item->get_quantity();
                    $revenue = $item->get_total();
                    
                    if (!isset($products_sold[$product_id])) {
                        $products_sold[$product_id] = array(
                            'name' => $product_name,
                            'quantity' => 0,
                            'revenue' => 0
                        );
                    }
                    $products_sold[$product_id]['quantity'] += $quantity;
                    $products_sold[$product_id]['revenue'] += $revenue;
                }
                
                // Datos de cliente
                $customer_id = $wc_order->get_customer_id();
                $customer_name = $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name();
                
                if (!isset($customers_data[$customer_id])) {
                    $customers_data[$customer_id] = array(
                        'name' => $customer_name,
                        'orders' => 0,
                        'total' => 0
                    );
                }
                $customers_data[$customer_id]['orders'] += 1;
                $customers_data[$customer_id]['total'] += $total_usd;
                
                // Métodos de pago
                $payment_method = $wc_order->get_payment_method_title();
                if (!isset($payment_methods[$payment_method])) {
                    $payment_methods[$payment_method] = array(
                        'name' => $payment_method,
                        'count' => 0
                    );
                }
                $payment_methods[$payment_method]['count'] += 1;
                
                // Métodos de envío
                $shipping_method = $wc_order->get_shipping_method();
                if (!isset($shipping_methods[$shipping_method])) {
                    $shipping_methods[$shipping_method] = array(
                        'name' => $shipping_method,
                        'count' => 0
                    );
                }
                $shipping_methods[$shipping_method]['count'] += 1;
            }
        }
        
        // Calcular promedios
        $average_order_value = $total_orders > 0 ? $total_sales_usd / $total_orders : 0;
        
        // Ordenar productos por cantidad vendida
        uasort($products_sold, function($a, $b) {
            return $b['quantity'] - $a['quantity'];
        });
        $top_products = array_slice($products_sold, 0, 10, true);
        
        // Ordenar clientes por total gastado
        uasort($customers_data, function($a, $b) {
            return $b['total'] - $a['total'];
        });
        $top_customers = array_slice($customers_data, 0, 10, true);
        
        // Calcular porcentajes para métodos de pago
        $total_payments = array_sum(array_column($payment_methods, 'count'));
        foreach ($payment_methods as &$method) {
            $method['percentage'] = $total_payments > 0 ? round(($method['count'] / $total_payments) * 100, 1) : 0;
        }
        
        // Calcular porcentajes para métodos de envío
        $total_shipping = array_sum(array_column($shipping_methods, 'count'));
        foreach ($shipping_methods as &$method) {
            $method['percentage'] = $total_shipping > 0 ? round(($method['count'] / $total_shipping) * 100, 1) : 0;
        }
        
        // Preparar datos para gráficos
        $chart_data = array_values($chart_data);
        
        return array(
            'total_sales_usd' => $total_sales_usd,
            'total_sales_ves' => $total_sales_ves,
            'total_orders' => $total_orders,
            'average_order_value' => $average_order_value,
            'total_iva' => $total_iva,
            'total_igtf' => $total_igtf,
            'top_products' => $top_products,
            'top_customers' => $top_customers,
            'payment_methods' => $payment_methods,
            'shipping_methods' => $shipping_methods,
            'chart_data' => $chart_data
        );
    }
    
    /**
     * Obtener estadísticas del dashboard
     */
    public function get_dashboard_statistics() {
        $stats = array(
            'today' => $this->get_period_statistics('today'),
            'week' => $this->get_period_statistics('week'),
            'month' => $this->get_period_statistics('month'),
            'year' => $this->get_period_statistics('year')
        );
        
        return $stats;
    }
    
    /**
     * Obtener estadísticas por período
     */
    private function get_period_statistics($period) {
        global $wpdb;
        
        $date_condition = '';
        switch ($period) {
            case 'today':
                $date_condition = "DATE(o.date_created_gmt) = CURDATE()";
                break;
            case 'week':
                $date_condition = "o.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $date_condition = "MONTH(o.date_created_gmt) = MONTH(NOW()) AND YEAR(o.date_created_gmt) = YEAR(NOW())";
                break;
            case 'year':
                $date_condition = "YEAR(o.date_created_gmt) = YEAR(NOW())";
                break;
        }
        
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            // HPOS
            $result = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(o.total_amount) as total_sales,
                    AVG(o.total_amount) as average_order
                FROM {$wpdb->prefix}wc_orders o
                WHERE o.type = 'shop_order'
                AND o.status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
                AND {$date_condition}
            "));
        } else {
            // Posts tradicional
            $result = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) as total_sales,
                    AVG(CAST(pm_total.meta_value AS DECIMAL(10,2))) as average_order
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
                WHERE p.post_type = 'shop_order'
                AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
                AND {$date_condition}
            "));
        }
        
        return array(
            'orders' => intval($result->total_orders ?? 0),
            'sales' => floatval($result->total_sales ?? 0),
            'average' => floatval($result->average_order ?? 0)
        );
    }
    
    /**
     * AJAX para obtener estadísticas
     */
    public function ajax_get_statistics() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_statistics_nonce')) {
            wp_die('Nonce inválido');
        }
        
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        $period = sanitize_text_field($_POST['period']);
        
        $stats = $this->generate_complete_statistics($date_from, $date_to, $period);
        
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX para obtener estadísticas del dashboard
     */
    public function ajax_get_dashboard_stats() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_dashboard_stats_nonce')) {
            wp_die('Nonce inválido');
        }
        
        $stats = $this->get_dashboard_statistics();
        
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX para exportar estadísticas
     */
    public function ajax_export_statistics() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'wcvs_export_statistics')) {
            wp_die('Nonce inválido');
        }
        
        $date_from = sanitize_text_field($_GET['date_from']);
        $date_to = sanitize_text_field($_GET['date_to']);
        $period = sanitize_text_field($_GET['period']);
        $format = sanitize_text_field($_GET['format'] ?? 'excel');
        
        $stats = $this->generate_complete_statistics($date_from, $date_to, $period);
        
        if ($format === 'csv') {
            $this->export_to_csv($stats, $date_from, $date_to);
        } else {
            $this->export_to_excel($stats, $date_from, $date_to);
        }
    }
    
    /**
     * Exportar a CSV
     */
    private function export_to_csv($stats, $date_from, $date_to) {
        $filename = 'estadisticas_venezuela_' . $date_from . '_' . $date_to . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
        
        // Encabezados
        fputcsv($output, array(
            'Período',
            'Total Ventas USD',
            'Total Ventas VES',
            'Total Pedidos',
            'Promedio por Pedido',
            'IVA Recaudado',
            'IGTF Recaudado'
        ));
        
        // Datos principales
        fputcsv($output, array(
            $date_from . ' - ' . $date_to,
            number_format($stats['total_sales_usd'], 2, '.', ''),
            number_format($stats['total_sales_ves'], 2, '.', ''),
            $stats['total_orders'],
            number_format($stats['average_order_value'], 2, '.', ''),
            number_format($stats['total_iva'], 2, '.', ''),
            number_format($stats['total_igtf'], 2, '.', '')
        ));
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar a Excel
     */
    private function export_to_excel($stats, $date_from, $date_to) {
        $filename = 'estadisticas_venezuela_' . $date_from . '_' . $date_to . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        echo '<table border="1">';
        echo '<tr><th colspan="7">ESTADÍSTICAS VENEZUELA SUITE</th></tr>';
        echo '<tr><th colspan="7">Período: ' . date('d/m/Y', strtotime($date_from)) . ' - ' . date('d/m/Y', strtotime($date_to)) . '</th></tr>';
        echo '<tr></tr>';
        
        // Encabezados
        echo '<tr>';
        echo '<th>Período</th>';
        echo '<th>Total Ventas USD</th>';
        echo '<th>Total Ventas VES</th>';
        echo '<th>Total Pedidos</th>';
        echo '<th>Promedio por Pedido</th>';
        echo '<th>IVA Recaudado</th>';
        echo '<th>IGTF Recaudado</th>';
        echo '</tr>';
        
        // Datos principales
        echo '<tr>';
        echo '<td>' . $date_from . ' - ' . $date_to . '</td>';
        echo '<td>' . number_format($stats['total_sales_usd'], 2, '.', '') . '</td>';
        echo '<td>' . number_format($stats['total_sales_ves'], 2, '.', '') . '</td>';
        echo '<td>' . $stats['total_orders'] . '</td>';
        echo '<td>' . number_format($stats['average_order_value'], 2, '.', '') . '</td>';
        echo '<td>' . number_format($stats['total_iva'], 2, '.', '') . '</td>';
        echo '<td>' . number_format($stats['total_igtf'], 2, '.', '') . '</td>';
        echo '</tr>';
        
        echo '</table>';
        exit;
    }
    
    /**
     * Cargar scripts para estadísticas
     */
    public function enqueue_statistics_scripts($hook) {
        if ($hook !== 'venezuela-suite_page_wcvs-statistics') {
            return;
        }
        
        wp_enqueue_style('wcvs-statistics', plugin_dir_url(__FILE__) . '../admin/css/statistics.css', array(), WCVS_VERSION);
        wp_enqueue_script('wcvs-statistics', plugin_dir_url(__FILE__) . '../admin/js/statistics.js', array('jquery'), WCVS_VERSION, true);
        
        wp_localize_script('wcvs-statistics', 'wcvs_statistics', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcvs_statistics_nonce'),
            'i18n' => array(
                'generating' => __('Generando estadísticas...', 'woocommerce-venezuela-pro-2025'),
                'error' => __('Error al generar estadísticas', 'woocommerce-venezuela-pro-2025')
            )
        ));
    }
}
