<?php
/**
 * Analytics Dashboard
 * Comprehensive analytics and reporting system for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Analytics_Dashboard {
    
    private static $instance = null;
    private $metrics = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->init_metrics();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_ajax_wvp_get_analytics_data', array( $this, 'ajax_get_analytics_data' ) );
        add_action( 'wp_ajax_wvp_export_analytics', array( $this, 'ajax_export_analytics' ) );
        add_action( 'wp_ajax_wvp_save_analytics_settings', array( $this, 'ajax_save_analytics_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_analytics_admin_menu' ), 60 );
        
        // Schedule daily analytics update
        add_action( 'wvp_daily_analytics_update', array( $this, 'update_daily_analytics' ) );
        
        if ( ! wp_next_scheduled( 'wvp_daily_analytics_update' ) ) {
            wp_schedule_event( time(), 'daily', 'wvp_daily_analytics_update' );
        }
    }
    
    /**
     * Initialize metrics
     */
    private function init_metrics() {
        $this->metrics = array(
            'sales' => array(
                'name' => 'Ventas',
                'icon' => '💰',
                'color' => '#27ae60'
            ),
            'orders' => array(
                'name' => 'Pedidos',
                'icon' => '📦',
                'color' => '#3498db'
            ),
            'customers' => array(
                'name' => 'Clientes',
                'icon' => '👥',
                'color' => '#9b59b6'
            ),
            'products' => array(
                'name' => 'Productos',
                'icon' => '🛍️',
                'color' => '#e67e22'
            ),
            'conversion_rate' => array(
                'name' => 'Tasa de Conversión',
                'icon' => '📈',
                'color' => '#e74c3c'
            ),
            'average_order_value' => array(
                'name' => 'Valor Promedio del Pedido',
                'icon' => '💵',
                'color' => '#f39c12'
            ),
            'bcv_rate' => array(
                'name' => 'Tasa BCV',
                'icon' => '💱',
                'color' => '#1abc9c'
            ),
            'tax_collected' => array(
                'name' => 'Impuestos Recaudados',
                'icon' => '🏛️',
                'color' => '#34495e'
            )
        );
    }
    
    /**
     * Get analytics data
     */
    public function get_analytics_data( $period = '30_days', $metric = 'all' ) {
        $data = array();
        
        switch ( $period ) {
            case '7_days':
                $start_date = date( 'Y-m-d', strtotime( '-7 days' ) );
                break;
            case '30_days':
                $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
                break;
            case '90_days':
                $start_date = date( 'Y-m-d', strtotime( '-90 days' ) );
                break;
            case '1_year':
                $start_date = date( 'Y-m-d', strtotime( '-1 year' ) );
                break;
            default:
                $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
        }
        
        $end_date = date( 'Y-m-d' );
        
        if ( $metric === 'all' ) {
            foreach ( $this->metrics as $metric_key => $metric_data ) {
                $data[ $metric_key ] = $this->get_metric_data( $metric_key, $start_date, $end_date );
            }
        } else {
            $data[ $metric ] = $this->get_metric_data( $metric, $start_date, $end_date );
        }
        
        return $data;
    }
    
    /**
     * Get specific metric data
     */
    private function get_metric_data( $metric, $start_date, $end_date ) {
        global $wpdb;
        
        switch ( $metric ) {
            case 'sales':
                return $this->get_sales_data( $start_date, $end_date );
                
            case 'orders':
                return $this->get_orders_data( $start_date, $end_date );
                
            case 'customers':
                return $this->get_customers_data( $start_date, $end_date );
                
            case 'products':
                return $this->get_products_data( $start_date, $end_date );
                
            case 'conversion_rate':
                return $this->get_conversion_rate_data( $start_date, $end_date );
                
            case 'average_order_value':
                return $this->get_average_order_value_data( $start_date, $end_date );
                
            case 'bcv_rate':
                return $this->get_bcv_rate_data( $start_date, $end_date );
                
            case 'tax_collected':
                return $this->get_tax_collected_data( $start_date, $end_date );
                
            default:
                return array();
        }
    }
    
    /**
     * Get sales data
     */
    private function get_sales_data( $start_date, $end_date ) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'posts';
        $meta_table = $wpdb->prefix . 'postmeta';
        
        $query = $wpdb->prepare(
            "SELECT 
                DATE(p.post_date) as date,
                SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as total_sales,
                COUNT(DISTINCT p.ID) as order_count
            FROM {$orders_table} p
            INNER JOIN {$meta_table} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND pm.meta_key = '_order_total'
            AND DATE(p.post_date) BETWEEN %s AND %s
            GROUP BY DATE(p.post_date)
            ORDER BY date ASC",
            $start_date,
            $end_date
        );
        
        $results = $wpdb->get_results( $query );
        
        $data = array(
            'total' => 0,
            'growth' => 0,
            'daily_data' => array(),
            'currency' => get_woocommerce_currency()
        );
        
        $previous_total = 0;
        
        foreach ( $results as $result ) {
            $data['daily_data'][] = array(
                'date' => $result->date,
                'value' => floatval( $result->total_sales ),
                'orders' => intval( $result->order_count )
            );
            $data['total'] += floatval( $result->total_sales );
        }
        
        // Calculate growth
        $previous_period_total = $this->get_previous_period_sales( $start_date, $end_date );
        if ( $previous_period_total > 0 ) {
            $data['growth'] = ( ( $data['total'] - $previous_period_total ) / $previous_period_total ) * 100;
        }
        
        return $data;
    }
    
    /**
     * Get orders data
     */
    private function get_orders_data( $start_date, $end_date ) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'posts';
        
        $query = $wpdb->prepare(
            "SELECT 
                DATE(post_date) as date,
                COUNT(*) as order_count,
                post_status
            FROM {$orders_table}
            WHERE post_type = 'shop_order'
            AND DATE(post_date) BETWEEN %s AND %s
            GROUP BY DATE(post_date), post_status
            ORDER BY date ASC",
            $start_date,
            $end_date
        );
        
        $results = $wpdb->get_results( $query );
        
        $data = array(
            'total' => 0,
            'growth' => 0,
            'daily_data' => array(),
            'status_breakdown' => array()
        );
        
        $daily_orders = array();
        $status_counts = array();
        
        foreach ( $results as $result ) {
            $date = $result->date;
            $status = $result->post_status;
            $count = intval( $result->order_count );
            
            if ( ! isset( $daily_orders[ $date ] ) ) {
                $daily_orders[ $date ] = 0;
            }
            $daily_orders[ $date ] += $count;
            
            if ( ! isset( $status_counts[ $status ] ) ) {
                $status_counts[ $status ] = 0;
            }
            $status_counts[ $status ] += $count;
        }
        
        foreach ( $daily_orders as $date => $count ) {
            $data['daily_data'][] = array(
                'date' => $date,
                'value' => $count
            );
            $data['total'] += $count;
        }
        
        $data['status_breakdown'] = $status_counts;
        
        // Calculate growth
        $previous_period_total = $this->get_previous_period_orders( $start_date, $end_date );
        if ( $previous_period_total > 0 ) {
            $data['growth'] = ( ( $data['total'] - $previous_period_total ) / $previous_period_total ) * 100;
        }
        
        return $data;
    }
    
    /**
     * Get customers data
     */
    private function get_customers_data( $start_date, $end_date ) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'posts';
        $meta_table = $wpdb->prefix . 'postmeta';
        
        $query = $wpdb->prepare(
            "SELECT 
                DATE(p.post_date) as date,
                COUNT(DISTINCT pm.meta_value) as new_customers
            FROM {$orders_table} p
            INNER JOIN {$meta_table} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND pm.meta_key = '_customer_user'
            AND pm.meta_value != '0'
            AND DATE(p.post_date) BETWEEN %s AND %s
            GROUP BY DATE(p.post_date)
            ORDER BY date ASC",
            $start_date,
            $end_date
        );
        
        $results = $wpdb->get_results( $query );
        
        $data = array(
            'total' => 0,
            'growth' => 0,
            'daily_data' => array(),
            'repeat_customers' => 0
        );
        
        foreach ( $results as $result ) {
            $data['daily_data'][] = array(
                'date' => $result->date,
                'value' => intval( $result->new_customers )
            );
            $data['total'] += intval( $result->new_customers );
        }
        
        // Calculate repeat customers
        $total_orders = $this->get_total_orders_in_period( $start_date, $end_date );
        $data['repeat_customers'] = $total_orders - $data['total'];
        
        return $data;
    }
    
    /**
     * Get products data
     */
    private function get_products_data( $start_date, $end_date ) {
        global $wpdb;
        
        $products_table = $wpdb->prefix . 'posts';
        
        $query = $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_products,
                post_status
            FROM {$products_table}
            WHERE post_type = 'product'
            AND post_status IN ('publish', 'private')
            GROUP BY post_status",
            $start_date,
            $end_date
        );
        
        $results = $wpdb->get_results( $query );
        
        $data = array(
            'total' => 0,
            'published' => 0,
            'private' => 0,
            'out_of_stock' => 0,
            'low_stock' => 0
        );
        
        foreach ( $results as $result ) {
            $data['total'] += intval( $result->total_products );
            if ( $result->post_status === 'publish' ) {
                $data['published'] = intval( $result->total_products );
            } elseif ( $result->post_status === 'private' ) {
                $data['private'] = intval( $result->total_products );
            }
        }
        
        // Get stock information
        $stock_data = $this->get_product_stock_data();
        $data['out_of_stock'] = $stock_data['out_of_stock'];
        $data['low_stock'] = $stock_data['low_stock'];
        
        return $data;
    }
    
    /**
     * Get conversion rate data
     */
    private function get_conversion_rate_data( $start_date, $end_date ) {
        $visitors = $this->get_visitors_data( $start_date, $end_date );
        $orders = $this->get_orders_data( $start_date, $end_date );
        
        $conversion_rate = 0;
        if ( $visitors['total'] > 0 ) {
            $conversion_rate = ( $orders['total'] / $visitors['total'] ) * 100;
        }
        
        return array(
            'rate' => round( $conversion_rate, 2 ),
            'visitors' => $visitors['total'],
            'orders' => $orders['total']
        );
    }
    
    /**
     * Get average order value data
     */
    private function get_average_order_value_data( $start_date, $end_date ) {
        $sales_data = $this->get_sales_data( $start_date, $end_date );
        $orders_data = $this->get_orders_data( $start_date, $end_date );
        
        $average_order_value = 0;
        if ( $orders_data['total'] > 0 ) {
            $average_order_value = $sales_data['total'] / $orders_data['total'];
        }
        
        return array(
            'value' => round( $average_order_value, 2 ),
            'currency' => get_woocommerce_currency(),
            'total_sales' => $sales_data['total'],
            'total_orders' => $orders_data['total']
        );
    }
    
    /**
     * Get BCV rate data
     */
    private function get_bcv_rate_data( $start_date, $end_date ) {
        $current_rate = get_option( 'wvp_bcv_rate', 36.5 );
        $rate_history = get_option( 'wvp_bcv_rate_history', array() );
        
        $data = array(
            'current_rate' => $current_rate,
            'daily_data' => array(),
            'fluctuation' => 0
        );
        
        // Get rate history for the period
        foreach ( $rate_history as $entry ) {
            if ( $entry['date'] >= $start_date && $entry['date'] <= $end_date ) {
                $data['daily_data'][] = array(
                    'date' => $entry['date'],
                    'value' => floatval( $entry['rate'] )
                );
            }
        }
        
        // Calculate fluctuation
        if ( count( $data['daily_data'] ) > 1 ) {
            $first_rate = $data['daily_data'][0]['value'];
            $last_rate = end( $data['daily_data'] )['value'];
            $data['fluctuation'] = ( ( $last_rate - $first_rate ) / $first_rate ) * 100;
        }
        
        return $data;
    }
    
    /**
     * Get tax collected data
     */
    private function get_tax_collected_data( $start_date, $end_date ) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'posts';
        $meta_table = $wpdb->prefix . 'postmeta';
        
        $query = $wpdb->prepare(
            "SELECT 
                DATE(p.post_date) as date,
                SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as total_tax
            FROM {$orders_table} p
            INNER JOIN {$meta_table} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND pm.meta_key = '_order_tax'
            AND DATE(p.post_date) BETWEEN %s AND %s
            GROUP BY DATE(p.post_date)
            ORDER BY date ASC",
            $start_date,
            $end_date
        );
        
        $results = $wpdb->get_results( $query );
        
        $data = array(
            'total' => 0,
            'growth' => 0,
            'daily_data' => array(),
            'currency' => get_woocommerce_currency()
        );
        
        foreach ( $results as $result ) {
            $data['daily_data'][] = array(
                'date' => $result->date,
                'value' => floatval( $result->total_tax )
            );
            $data['total'] += floatval( $result->total_tax );
        }
        
        return $data;
    }
    
    /**
     * Helper methods
     */
    private function get_previous_period_sales( $start_date, $end_date ) {
        $days = ( strtotime( $end_date ) - strtotime( $start_date ) ) / ( 60 * 60 * 24 );
        $prev_start = date( 'Y-m-d', strtotime( $start_date . ' -' . $days . ' days' ) );
        $prev_end = date( 'Y-m-d', strtotime( $start_date . ' -1 day' ) );
        
        $sales_data = $this->get_sales_data( $prev_start, $prev_end );
        return $sales_data['total'];
    }
    
    private function get_previous_period_orders( $start_date, $end_date ) {
        $days = ( strtotime( $end_date ) - strtotime( $start_date ) ) / ( 60 * 60 * 24 );
        $prev_start = date( 'Y-m-d', strtotime( $start_date . ' -' . $days . ' days' ) );
        $prev_end = date( 'Y-m-d', strtotime( $start_date . ' -1 day' ) );
        
        $orders_data = $this->get_orders_data( $prev_start, $prev_end );
        return $orders_data['total'];
    }
    
    private function get_total_orders_in_period( $start_date, $end_date ) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'posts';
        
        $query = $wpdb->prepare(
            "SELECT COUNT(*) as total_orders
            FROM {$orders_table}
            WHERE post_type = 'shop_order'
            AND post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND DATE(post_date) BETWEEN %s AND %s",
            $start_date,
            $end_date
        );
        
        $result = $wpdb->get_var( $query );
        return intval( $result );
    }
    
    private function get_visitors_data( $start_date, $end_date ) {
        // This would typically integrate with Google Analytics or similar
        // For now, we'll estimate based on orders and conversion rate
        $orders_data = $this->get_orders_data( $start_date, $end_date );
        $estimated_conversion_rate = 2.5; // 2.5% average e-commerce conversion rate
        
        return array(
            'total' => intval( $orders_data['total'] / ( $estimated_conversion_rate / 100 ) )
        );
    }
    
    private function get_product_stock_data() {
        global $wpdb;
        
        $meta_table = $wpdb->prefix . 'postmeta';
        $posts_table = $wpdb->prefix . 'posts';
        
        $query = "
            SELECT 
                COUNT(CASE WHEN pm.meta_value = '0' THEN 1 END) as out_of_stock,
                COUNT(CASE WHEN CAST(pm.meta_value AS UNSIGNED) BETWEEN 1 AND 5 THEN 1 END) as low_stock
            FROM {$posts_table} p
            INNER JOIN {$meta_table} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_stock'
        ";
        
        $result = $wpdb->get_row( $query );
        
        return array(
            'out_of_stock' => intval( $result->out_of_stock ),
            'low_stock' => intval( $result->low_stock )
        );
    }
    
    /**
     * Update daily analytics
     */
    public function update_daily_analytics() {
        $today = date( 'Y-m-d' );
        $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
        
        $analytics_data = $this->get_analytics_data( '7_days' );
        
        // Store daily analytics
        update_option( 'wvp_daily_analytics_' . $today, $analytics_data );
        
        // Clean old analytics (keep last 90 days)
        $this->clean_old_analytics();
    }
    
    /**
     * Clean old analytics data
     */
    private function clean_old_analytics() {
        global $wpdb;
        
        $cutoff_date = date( 'Y-m-d', strtotime( '-90 days' ) );
        
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE 'wvp_daily_analytics_%' 
             AND option_name < %s",
            'wvp_daily_analytics_' . $cutoff_date
        ) );
    }
    
    /**
     * Add analytics admin menu
     */
    public function add_analytics_admin_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Analytics y Reportes',
            'Analytics',
            'manage_options',
            'wvp-analytics',
            array( $this, 'analytics_admin_page' )
        );
    }
    
    /**
     * Analytics admin page
     */
    public function analytics_admin_page() {
        ?>
        <div class="wrap">
            <h1>📊 Analytics y Reportes - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-analytics-controls">
                <div class="wvp-period-selector">
                    <label for="analytics-period">Período:</label>
                    <select id="analytics-period">
                        <option value="7_days">Últimos 7 días</option>
                        <option value="30_days" selected>Últimos 30 días</option>
                        <option value="90_days">Últimos 90 días</option>
                        <option value="1_year">Último año</option>
                    </select>
                </div>
                
                <div class="wvp-export-controls">
                    <button class="button button-secondary" id="export-analytics">
                        📥 Exportar Datos
                    </button>
                    <button class="button button-secondary" id="refresh-analytics">
                        🔄 Actualizar
                    </button>
                </div>
            </div>
            
            <div class="wvp-analytics-metrics">
                <div class="wvp-metrics-grid" id="analytics-metrics">
                    <!-- Metrics will be loaded here via JavaScript -->
                </div>
            </div>
            
            <div class="wvp-analytics-charts">
                <div class="wvp-chart-container">
                    <h3>📈 Ventas Diarias</h3>
                    <canvas id="sales-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="wvp-chart-container">
                    <h3>📦 Pedidos por Estado</h3>
                    <canvas id="orders-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="wvp-chart-container">
                    <h3>💱 Tasa BCV</h3>
                    <canvas id="bcv-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="wvp-chart-container">
                    <h3>🏛️ Impuestos Recaudados</h3>
                    <canvas id="tax-chart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <div class="wvp-analytics-reports">
                <h2>📋 Reportes Detallados</h2>
                <div class="wvp-reports-grid">
                    <div class="wvp-report-card">
                        <h3>🏆 Top Productos</h3>
                        <div id="top-products-report">
                            <!-- Top products will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="wvp-report-card">
                        <h3>👥 Top Clientes</h3>
                        <div id="top-customers-report">
                            <!-- Top customers will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="wvp-report-card">
                        <h3>🌍 Ventas por Estado</h3>
                        <div id="sales-by-state-report">
                            <!-- Sales by state will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="wvp-report-card">
                        <h3>💳 Métodos de Pago</h3>
                        <div id="payment-methods-report">
                            <!-- Payment methods will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .wvp-analytics-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .wvp-period-selector select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .wvp-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .wvp-metric-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .wvp-metric-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .wvp-metric-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .wvp-metric-label {
            color: #666;
            font-size: 14px;
        }
        
        .wvp-metric-growth {
            font-size: 12px;
            margin-top: 5px;
        }
        
        .wvp-metric-growth.positive {
            color: #27ae60;
        }
        
        .wvp-metric-growth.negative {
            color: #e74c3c;
        }
        
        .wvp-analytics-charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .wvp-chart-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .wvp-chart-container h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .wvp-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .wvp-report-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .wvp-report-card h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .wvp-report-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .wvp-report-item:last-child {
            border-bottom: none;
        }
        
        .wvp-report-label {
            font-weight: 500;
        }
        
        .wvp-report-value {
            color: #0073aa;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .wvp-analytics-controls {
                flex-direction: column;
                gap: 15px;
            }
            
            .wvp-analytics-charts {
                grid-template-columns: 1fr;
            }
            
            .wvp-metrics-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        jQuery(document).ready(function($) {
            let currentPeriod = '30_days';
            let charts = {};
            
            // Load analytics data
            function loadAnalyticsData(period) {
                $.post(ajaxurl, {
                    action: 'wvp_get_analytics_data',
                    period: period,
                    nonce: '<?php echo wp_create_nonce( 'wvp_analytics_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        updateMetrics(response.data);
                        updateCharts(response.data);
                        updateReports(response.data);
                    }
                });
            }
            
            // Update metrics
            function updateMetrics(data) {
                const metricsContainer = $('#analytics-metrics');
                metricsContainer.empty();
                
                const metrics = <?php echo json_encode( $this->metrics ); ?>;
                
                Object.keys(metrics).forEach(metricKey => {
                    const metric = metrics[metricKey];
                    const metricData = data[metricKey];
                    
                    if (metricData) {
                        const growthClass = metricData.growth >= 0 ? 'positive' : 'negative';
                        const growthSymbol = metricData.growth >= 0 ? '+' : '';
                        
                        const metricHTML = `
                            <div class="wvp-metric-card">
                                <div class="wvp-metric-icon">${metric.icon}</div>
                                <div class="wvp-metric-value">${formatMetricValue(metricKey, metricData)}</div>
                                <div class="wvp-metric-label">${metric.name}</div>
                                ${metricData.growth !== undefined ? `<div class="wvp-metric-growth ${growthClass}">${growthSymbol}${metricData.growth.toFixed(1)}%</div>` : ''}
                            </div>
                        `;
                        
                        metricsContainer.append(metricHTML);
                    }
                });
            }
            
            // Format metric value
            function formatMetricValue(metricKey, data) {
                switch(metricKey) {
                    case 'sales':
                    case 'average_order_value':
                    case 'tax_collected':
                        return new Intl.NumberFormat('es-VE', {
                            style: 'currency',
                            currency: data.currency || 'USD'
                        }).format(data.total || data.value || 0);
                    case 'bcv_rate':
                        return data.current_rate ? data.current_rate.toFixed(2) + ' VES' : 'N/A';
                    case 'conversion_rate':
                        return (data.rate || 0).toFixed(2) + '%';
                    default:
                        return (data.total || data.value || 0).toLocaleString('es-VE');
                }
            }
            
            // Update charts
            function updateCharts(data) {
                updateSalesChart(data.sales);
                updateOrdersChart(data.orders);
                updateBCVChart(data.bcv_rate);
                updateTaxChart(data.tax_collected);
            }
            
            // Sales chart
            function updateSalesChart(salesData) {
                const ctx = document.getElementById('sales-chart').getContext('2d');
                
                if (charts.sales) {
                    charts.sales.destroy();
                }
                
                charts.sales = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: salesData.daily_data.map(d => d.date),
                        datasets: [{
                            label: 'Ventas',
                            data: salesData.daily_data.map(d => d.value),
                            borderColor: '#27ae60',
                            backgroundColor: 'rgba(39, 174, 96, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('es-VE', {
                                            style: 'currency',
                                            currency: salesData.currency || 'USD'
                                        }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Orders chart
            function updateOrdersChart(ordersData) {
                const ctx = document.getElementById('orders-chart').getContext('2d');
                
                if (charts.orders) {
                    charts.orders.destroy();
                }
                
                const statusLabels = Object.keys(ordersData.status_breakdown);
                const statusData = Object.values(ordersData.status_breakdown);
                
                charts.orders = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels.map(status => status.replace('wc-', '').toUpperCase()),
                        datasets: [{
                            data: statusData,
                            backgroundColor: [
                                '#3498db',
                                '#27ae60',
                                '#f39c12',
                                '#e74c3c',
                                '#9b59b6'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            // BCV chart
            function updateBCVChart(bcvData) {
                const ctx = document.getElementById('bcv-chart').getContext('2d');
                
                if (charts.bcv) {
                    charts.bcv.destroy();
                }
                
                charts.bcv = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: bcvData.daily_data.map(d => d.date),
                        datasets: [{
                            label: 'Tasa BCV',
                            data: bcvData.daily_data.map(d => d.value),
                            borderColor: '#1abc9c',
                            backgroundColor: 'rgba(26, 188, 156, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function(value) {
                                        return value.toFixed(2) + ' VES';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Tax chart
            function updateTaxChart(taxData) {
                const ctx = document.getElementById('tax-chart').getContext('2d');
                
                if (charts.tax) {
                    charts.tax.destroy();
                }
                
                charts.tax = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: taxData.daily_data.map(d => d.date),
                        datasets: [{
                            label: 'Impuestos',
                            data: taxData.daily_data.map(d => d.value),
                            backgroundColor: '#34495e',
                            borderColor: '#2c3e50',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('es-VE', {
                                            style: 'currency',
                                            currency: taxData.currency || 'USD'
                                        }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Update reports
            function updateReports(data) {
                // This would typically load detailed reports
                // For now, we'll show placeholder content
                $('#top-products-report').html('<p>Reporte de productos más vendidos...</p>');
                $('#top-customers-report').html('<p>Reporte de mejores clientes...</p>');
                $('#sales-by-state-report').html('<p>Reporte de ventas por estado...</p>');
                $('#payment-methods-report').html('<p>Reporte de métodos de pago...</p>');
            }
            
            // Event handlers
            $('#analytics-period').on('change', function() {
                currentPeriod = $(this).val();
                loadAnalyticsData(currentPeriod);
            });
            
            $('#refresh-analytics').on('click', function() {
                loadAnalyticsData(currentPeriod);
            });
            
            $('#export-analytics').on('click', function() {
                $.post(ajaxurl, {
                    action: 'wvp_export_analytics',
                    period: currentPeriod,
                    nonce: '<?php echo wp_create_nonce( 'wvp_analytics_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        window.open(response.data.download_url);
                    } else {
                        alert('Error al exportar datos');
                    }
                });
            });
            
            // Load initial data
            loadAnalyticsData(currentPeriod);
        });
        </script>
        <?php
    }
    
    /**
     * AJAX get analytics data
     */
    public function ajax_get_analytics_data() {
        check_ajax_referer( 'wvp_analytics_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $period = sanitize_text_field( $_POST['period'] );
        $data = $this->get_analytics_data( $period );
        
        wp_send_json_success( $data );
    }
    
    /**
     * AJAX export analytics
     */
    public function ajax_export_analytics() {
        check_ajax_referer( 'wvp_analytics_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $period = sanitize_text_field( $_POST['period'] );
        $data = $this->get_analytics_data( $period );
        
        // Create CSV file
        $filename = 'analytics_' . $period . '_' . date( 'Y-m-d' ) . '.csv';
        $filepath = wp_upload_dir()['basedir'] . '/wvp-exports/' . $filename;
        
        // Ensure directory exists
        wp_mkdir_p( dirname( $filepath ) );
        
        $csv_content = $this->generate_csv_content( $data );
        file_put_contents( $filepath, $csv_content );
        
        $download_url = wp_upload_dir()['baseurl'] . '/wvp-exports/' . $filename;
        
        wp_send_json_success( array(
            'download_url' => $download_url,
            'filename' => $filename
        ) );
    }
    
    /**
     * Generate CSV content
     */
    private function generate_csv_content( $data ) {
        $csv_content = "Métrica,Valor,Período\n";
        
        foreach ( $data as $metric_key => $metric_data ) {
            $metric_name = $this->metrics[ $metric_key ]['name'] ?? $metric_key;
            $value = $metric_data['total'] ?? $metric_data['value'] ?? $metric_data['rate'] ?? 0;
            
            $csv_content .= sprintf( "%s,%s,%s\n", 
                $metric_name, 
                $value, 
                date( 'Y-m-d H:i:s' )
            );
        }
        
        return $csv_content;
    }
    
    /**
     * AJAX save analytics settings
     */
    public function ajax_save_analytics_settings() {
        check_ajax_referer( 'wvp_analytics_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        // Save analytics settings
        $settings = array(
            'auto_update' => isset( $_POST['auto_update'] ),
            'email_reports' => isset( $_POST['email_reports'] ),
            'report_frequency' => sanitize_text_field( $_POST['report_frequency'] ?? 'weekly' )
        );
        
        update_option( 'wvp_analytics_settings', $settings );
        
        wp_send_json_success( 'Analytics settings saved successfully' );
    }
}
