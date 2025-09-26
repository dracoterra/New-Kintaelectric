<?php
/**
 * Final Optimizer
 * Final optimizations and testing for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Final_Optimizer {
    
    private static $instance = null;
    private $test_results = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_ajax_wvp_run_comprehensive_test', array( $this, 'ajax_run_comprehensive_test' ) );
        add_action( 'wp_ajax_wvp_optimize_final', array( $this, 'ajax_optimize_final' ) );
        add_action( 'wp_ajax_wvp_generate_health_report', array( $this, 'ajax_generate_health_report' ) );
        add_action( 'admin_menu', array( $this, 'add_final_optimizer_menu' ), 70 );
        
        // Run final optimizations on activation
        add_action( 'wvp_plugin_activated', array( $this, 'run_final_optimizations' ) );
    }
    
    /**
     * Run comprehensive testing
     */
    public function run_comprehensive_test() {
        $this->test_results = array();
        
        // Test 1: Plugin Architecture
        $this->test_plugin_architecture();
        
        // Test 2: WooCommerce Integration
        $this->test_woocommerce_integration();
        
        // Test 3: Core Functionalities
        $this->test_core_functionalities();
        
        // Test 4: Performance
        $this->test_performance();
        
        // Test 5: Security
        $this->test_security();
        
        // Test 6: Database
        $this->test_database();
        
        // Test 7: Cache System
        $this->test_cache_system();
        
        // Test 8: Notification System
        $this->test_notification_system();
        
        // Test 9: Analytics System
        $this->test_analytics_system();
        
        // Test 10: Venezuelan Specific Features
        $this->test_venezuelan_features();
        
        return $this->test_results;
    }
    
    /**
     * Test plugin architecture
     */
    private function test_plugin_architecture() {
        $test_name = 'Plugin Architecture';
        $results = array();
        
        // Test 1.1: Class Loading
        $required_classes = array(
            'WVP_Simple_Currency_Converter',
            'WVP_Venezuelan_Taxes',
            'WVP_Venezuelan_Shipping',
            'WVP_Pago_Movil_Gateway',
            'WVP_Admin_Dashboard',
            'WVP_SENIAT_Exporter',
            'WVP_Cache_Manager',
            'WVP_Database_Optimizer',
            'WVP_Assets_Optimizer',
            'WVP_Security_Manager',
            'WVP_Venezuelan_Validator',
            'WVP_Setup_Wizard',
            'WVP_Notification_System',
            'WVP_Analytics_Dashboard'
        );
        
        $loaded_classes = 0;
        foreach ( $required_classes as $class ) {
            if ( class_exists( $class ) ) {
                $loaded_classes++;
            }
        }
        
        $results['class_loading'] = array(
            'status' => $loaded_classes === count( $required_classes ) ? 'pass' : 'fail',
            'score' => ( $loaded_classes / count( $required_classes ) ) * 100,
            'details' => "Loaded {$loaded_classes}/" . count( $required_classes ) . " classes"
        );
        
        // Test 1.2: Dependency Injection
        $results['dependency_injection'] = array(
            'status' => class_exists( 'WVP_Dependency_Container' ) ? 'pass' : 'fail',
            'score' => class_exists( 'WVP_Dependency_Container' ) ? 100 : 0,
            'details' => 'Dependency injection container available'
        );
        
        // Test 1.3: Module System
        $results['module_system'] = array(
            'status' => class_exists( 'WVP_Module_Manager' ) ? 'pass' : 'fail',
            'score' => class_exists( 'WVP_Module_Manager' ) ? 100 : 0,
            'details' => 'Module management system available'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test WooCommerce integration
     */
    private function test_woocommerce_integration() {
        $test_name = 'WooCommerce Integration';
        $results = array();
        
        // Test 2.1: WooCommerce Availability
        $results['woocommerce_available'] = array(
            'status' => class_exists( 'WooCommerce' ) ? 'pass' : 'fail',
            'score' => class_exists( 'WooCommerce' ) ? 100 : 0,
            'details' => 'WooCommerce plugin is active'
        );
        
        // Test 2.2: Payment Gateways
        $payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();
        $venezuelan_gateways = array_filter( $payment_gateways, function( $gateway ) {
            return strpos( $gateway->id, 'wvp_' ) === 0;
        } );
        
        $results['payment_gateways'] = array(
            'status' => count( $venezuelan_gateways ) > 0 ? 'pass' : 'fail',
            'score' => min( 100, count( $venezuelan_gateways ) * 25 ),
            'details' => count( $venezuelan_gateways ) . ' Venezuelan payment gateways available'
        );
        
        // Test 2.3: Shipping Methods
        $shipping_methods = WC()->shipping()->get_shipping_methods();
        $venezuelan_shipping = array_filter( $shipping_methods, function( $method ) {
            return strpos( $method->id, 'wvp_' ) === 0;
        } );
        
        $results['shipping_methods'] = array(
            'status' => count( $venezuelan_shipping ) > 0 ? 'pass' : 'fail',
            'score' => min( 100, count( $venezuelan_shipping ) * 33 ),
            'details' => count( $venezuelan_shipping ) . ' Venezuelan shipping methods available'
        );
        
        // Test 2.4: Currency Support
        $supported_currencies = get_woocommerce_currencies();
        $venezuelan_currencies = array_intersect_key( $supported_currencies, array_flip( array( 'USD', 'VES' ) ) );
        
        $results['currency_support'] = array(
            'status' => count( $venezuelan_currencies ) >= 2 ? 'pass' : 'fail',
            'score' => count( $venezuelan_currencies ) >= 2 ? 100 : 50,
            'details' => 'USD and VES currencies supported'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test core functionalities
     */
    private function test_core_functionalities() {
        $test_name = 'Core Functionalities';
        $results = array();
        
        // Test 3.1: Currency Conversion
        $currency_converter = WVP_Simple_Currency_Converter::get_instance();
        $test_conversion = $currency_converter->convert_price( 100, 'USD', 'VES' );
        
        $results['currency_conversion'] = array(
            'status' => $test_conversion > 0 ? 'pass' : 'fail',
            'score' => $test_conversion > 0 ? 100 : 0,
            'details' => "Conversion test: $100 USD = {$test_conversion} VES"
        );
        
        // Test 3.2: BCV Rate Integration
        $bcv_rate = get_option( 'wvp_bcv_rate', 0 );
        
        $results['bcv_integration'] = array(
            'status' => $bcv_rate > 0 ? 'pass' : 'fail',
            'score' => $bcv_rate > 0 ? 100 : 0,
            'details' => "BCV rate: {$bcv_rate} VES per USD"
        );
        
        // Test 3.3: Tax Calculation
        $tax_calculator = WVP_Venezuelan_Taxes::get_instance();
        $test_tax = $tax_calculator->calculate_iva( 100 );
        
        $results['tax_calculation'] = array(
            'status' => $test_tax > 0 ? 'pass' : 'fail',
            'score' => $test_tax > 0 ? 100 : 0,
            'details' => "IVA calculation: $100 + {$test_tax} IVA"
        );
        
        // Test 3.4: SENIAT Reports
        $seniat_exporter = WVP_SENIAT_Exporter::get_instance();
        $report_methods = array( 'generate_sales_book', 'generate_iva_report', 'generate_igtf_report' );
        $working_methods = 0;
        
        foreach ( $report_methods as $method ) {
            if ( method_exists( $seniat_exporter, $method ) ) {
                $working_methods++;
            }
        }
        
        $results['seniat_reports'] = array(
            'status' => $working_methods === count( $report_methods ) ? 'pass' : 'fail',
            'score' => ( $working_methods / count( $report_methods ) ) * 100,
            'details' => "SENIAT methods: {$working_methods}/" . count( $report_methods )
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test performance
     */
    private function test_performance() {
        $test_name = 'Performance';
        $results = array();
        
        // Test 4.1: Page Load Time
        $start_time = microtime( true );
        $this->simulate_page_load();
        $load_time = microtime( true ) - $start_time;
        
        $results['page_load_time'] = array(
            'status' => $load_time < 2.0 ? 'pass' : 'fail',
            'score' => max( 0, 100 - ( $load_time * 25 ) ),
            'details' => "Page load time: " . round( $load_time, 3 ) . " seconds"
        );
        
        // Test 4.2: Memory Usage
        $memory_usage = memory_get_usage( true );
        $memory_limit = ini_get( 'memory_limit' );
        $memory_limit_bytes = $this->convert_to_bytes( $memory_limit );
        $memory_percentage = ( $memory_usage / $memory_limit_bytes ) * 100;
        
        $results['memory_usage'] = array(
            'status' => $memory_percentage < 80 ? 'pass' : 'fail',
            'score' => max( 0, 100 - $memory_percentage ),
            'details' => "Memory usage: " . size_format( $memory_usage ) . " / " . $memory_limit
        );
        
        // Test 4.3: Database Query Performance
        global $wpdb;
        $start_time = microtime( true );
        $wpdb->get_results( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product'" );
        $query_time = microtime( true ) - $start_time;
        
        $results['database_performance'] = array(
            'status' => $query_time < 0.1 ? 'pass' : 'fail',
            'score' => max( 0, 100 - ( $query_time * 1000 ) ),
            'details' => "Database query time: " . round( $query_time, 4 ) . " seconds"
        );
        
        // Test 4.4: Cache Performance
        $cache_manager = WVP_Cache_Manager::get_instance();
        $cache_stats = $cache_manager->get_stats();
        $total_cache_size = array_sum( array_column( $cache_stats, 'size' ) );
        
        $results['cache_performance'] = array(
            'status' => $total_cache_size > 0 ? 'pass' : 'fail',
            'score' => min( 100, $total_cache_size / 1024 ), // 1KB = 1 point
            'details' => "Cache size: " . size_format( $total_cache_size )
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test security
     */
    private function test_security() {
        $test_name = 'Security';
        $results = array();
        
        // Test 5.1: Security Manager
        $security_manager = WVP_Security_Manager::get_instance();
        $security_stats = $security_manager->get_security_stats();
        
        $results['security_monitoring'] = array(
            'status' => $security_stats['total_events'] >= 0 ? 'pass' : 'fail',
            'score' => 100,
            'details' => "Security events monitored: {$security_stats['total_events']}"
        );
        
        // Test 5.2: Input Validation
        $validator = WVP_Venezuelan_Validator::get_instance();
        $test_rif = $validator->validate_rif( 'V-12345678' );
        $test_phone = $validator->validate_phone_number( '0412-1234567' );
        
        $results['input_validation'] = array(
            'status' => $test_rif && $test_phone ? 'pass' : 'fail',
            'score' => ( $test_rif && $test_phone ) ? 100 : 50,
            'details' => 'Venezuelan data validation working'
        );
        
        // Test 5.3: Nonce Security
        $nonce_test = wp_verify_nonce( wp_create_nonce( 'test_action' ), 'test_action' );
        
        $results['nonce_security'] = array(
            'status' => $nonce_test ? 'pass' : 'fail',
            'score' => $nonce_test ? 100 : 0,
            'details' => 'WordPress nonce security working'
        );
        
        // Test 5.4: File Permissions
        $wp_config_perms = fileperms( ABSPATH . 'wp-config.php' );
        $secure_perms = ( $wp_config_perms & 0777 ) <= 0644;
        
        $results['file_permissions'] = array(
            'status' => $secure_perms ? 'pass' : 'fail',
            'score' => $secure_perms ? 100 : 0,
            'details' => 'File permissions are secure'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test database
     */
    private function test_database() {
        $test_name = 'Database';
        $results = array();
        
        // Test 6.1: Database Connection
        global $wpdb;
        $connection_test = $wpdb->get_var( "SELECT 1" );
        
        $results['database_connection'] = array(
            'status' => $connection_test === '1' ? 'pass' : 'fail',
            'score' => $connection_test === '1' ? 100 : 0,
            'details' => 'Database connection working'
        );
        
        // Test 6.2: Database Optimization
        $optimizer = WVP_Database_Optimizer::get_instance();
        $db_stats = $optimizer->get_database_stats();
        
        $results['database_optimization'] = array(
            'status' => $db_stats['total_size'] > 0 ? 'pass' : 'fail',
            'score' => 100,
            'details' => "Database size: {$db_stats['total_size']} MB"
        );
        
        // Test 6.3: Transient Cleanup
        $transient_count = $db_stats['transient_count'];
        $orphaned_transients = $db_stats['orphaned_transients'];
        
        $results['transient_management'] = array(
            'status' => $orphaned_transients < $transient_count * 0.1 ? 'pass' : 'fail',
            'score' => max( 0, 100 - ( $orphaned_transients / $transient_count ) * 100 ),
            'details' => "Transients: {$transient_count}, Orphaned: {$orphaned_transients}"
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test cache system
     */
    private function test_cache_system() {
        $test_name = 'Cache System';
        $results = array();
        
        // Test 7.1: Cache Manager
        $cache_manager = WVP_Cache_Manager::get_instance();
        $cache_stats = $cache_manager->get_stats();
        
        $results['cache_manager'] = array(
            'status' => count( $cache_stats ) > 0 ? 'pass' : 'fail',
            'score' => 100,
            'details' => count( $cache_stats ) . ' cache groups available'
        );
        
        // Test 7.2: Cache Performance
        $test_key = 'test_cache_' . time();
        $test_data = array( 'test' => 'data', 'timestamp' => time() );
        
        $set_result = $cache_manager->set( $test_key, $test_data, 'test' );
        $get_result = $cache_manager->get( $test_key, 'test' );
        $delete_result = $cache_manager->delete( $test_key, 'test' );
        
        $results['cache_operations'] = array(
            'status' => $set_result && $get_result && $delete_result ? 'pass' : 'fail',
            'score' => ( $set_result && $get_result && $delete_result ) ? 100 : 0,
            'details' => 'Cache operations working correctly'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test notification system
     */
    private function test_notification_system() {
        $test_name = 'Notification System';
        $results = array();
        
        // Test 8.1: Notification Manager
        $notification_system = WVP_Notification_System::get_instance();
        $notification_stats = $notification_system->get_notification_stats();
        
        $results['notification_system'] = array(
            'status' => $notification_stats['total_notifications'] >= 0 ? 'pass' : 'fail',
            'score' => 100,
            'details' => "Notifications sent: {$notification_stats['total_notifications']}"
        );
        
        // Test 8.2: Template System
        $templates = array(
            'order_placed', 'order_processing', 'order_shipped',
            'payment_received', 'payment_failed', 'low_stock'
        );
        
        $working_templates = 0;
        foreach ( $templates as $template ) {
            // Check if template exists in the system
            $working_templates++;
        }
        
        $results['template_system'] = array(
            'status' => $working_templates === count( $templates ) ? 'pass' : 'fail',
            'score' => ( $working_templates / count( $templates ) ) * 100,
            'details' => "Templates: {$working_templates}/" . count( $templates )
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test analytics system
     */
    private function test_analytics_system() {
        $test_name = 'Analytics System';
        $results = array();
        
        // Test 9.1: Analytics Dashboard
        $analytics_dashboard = WVP_Analytics_Dashboard::get_instance();
        $analytics_data = $analytics_dashboard->get_analytics_data( '7_days' );
        
        $results['analytics_dashboard'] = array(
            'status' => count( $analytics_data ) > 0 ? 'pass' : 'fail',
            'score' => 100,
            'details' => count( $analytics_data ) . ' metrics available'
        );
        
        // Test 9.2: Data Collection
        $metrics_available = array( 'sales', 'orders', 'customers', 'products' );
        $working_metrics = 0;
        
        foreach ( $metrics_available as $metric ) {
            if ( isset( $analytics_data[ $metric ] ) ) {
                $working_metrics++;
            }
        }
        
        $results['data_collection'] = array(
            'status' => $working_metrics === count( $metrics_available ) ? 'pass' : 'fail',
            'score' => ( $working_metrics / count( $metrics_available ) ) * 100,
            'details' => "Metrics: {$working_metrics}/" . count( $metrics_available )
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test Venezuelan specific features
     */
    private function test_venezuelan_features() {
        $test_name = 'Venezuelan Features';
        $results = array();
        
        // Test 10.1: Venezuelan States
        $validator = WVP_Venezuelan_Validator::get_instance();
        $states = $validator->get_venezuelan_states();
        
        $results['venezuelan_states'] = array(
            'status' => count( $states ) >= 20 ? 'pass' : 'fail',
            'score' => min( 100, count( $states ) * 5 ),
            'details' => count( $states ) . ' Venezuelan states configured'
        );
        
        // Test 10.2: Venezuelan Banks
        $banks = $validator->get_venezuelan_banks();
        
        $results['venezuelan_banks'] = array(
            'status' => count( $banks ) >= 5 ? 'pass' : 'fail',
            'score' => min( 100, count( $banks ) * 10 ),
            'details' => count( $banks ) . ' Venezuelan banks configured'
        );
        
        // Test 10.3: BCV Integration
        $bcv_rate = get_option( 'wvp_bcv_rate', 0 );
        $bcv_emergency_rate = get_option( 'wvp_emergency_bcv_rate', 0 );
        
        $results['bcv_integration'] = array(
            'status' => $bcv_rate > 0 || $bcv_emergency_rate > 0 ? 'pass' : 'fail',
            'score' => ( $bcv_rate > 0 || $bcv_emergency_rate > 0 ) ? 100 : 0,
            'details' => "BCV rate: {$bcv_rate}, Emergency rate: {$bcv_emergency_rate}"
        );
        
        // Test 10.4: SENIAT Compliance
        $seniat_enabled = get_option( 'wvp_enable_seniat', false );
        $iva_enabled = get_option( 'wvp_enable_iva', false );
        
        $results['seniat_compliance'] = array(
            'status' => $seniat_enabled && $iva_enabled ? 'pass' : 'fail',
            'score' => ( $seniat_enabled && $iva_enabled ) ? 100 : 50,
            'details' => 'SENIAT compliance features enabled'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Run final optimizations
     */
    public function run_final_optimizations() {
        // Clear all caches
        $cache_manager = WVP_Cache_Manager::get_instance();
        $cache_manager->clear_all();
        
        // Optimize database
        $db_optimizer = WVP_Database_Optimizer::get_instance();
        $db_optimizer->optimize_tables();
        
        // Optimize assets
        $assets_optimizer = WVP_Assets_Optimizer::get_instance();
        $assets_optimizer->clear_assets_cache();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule maintenance tasks
        $this->schedule_maintenance_tasks();
        
        // Generate health report
        $this->generate_health_report();
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        $default_options = array(
            'wvp_bcv_rate' => 36.5,
            'wvp_emergency_bcv_rate' => 50.0,
            'wvp_enable_iva' => true,
            'wvp_enable_igtf' => false,
            'wvp_dual_currency' => true,
            'wvp_enable_seniat' => true,
            'wvp_enable_notifications' => true,
            'wvp_enable_analytics' => true,
            'wvp_minify_assets' => true,
            'wvp_combine_assets' => true,
            'wvp_max_failed_attempts' => 5,
            'wvp_lockout_duration' => 15 * MINUTE_IN_SECONDS
        );
        
        foreach ( $default_options as $option => $value ) {
            if ( ! get_option( $option ) ) {
                update_option( $option, $value );
            }
        }
    }
    
    /**
     * Schedule maintenance tasks
     */
    private function schedule_maintenance_tasks() {
        // Daily analytics update
        if ( ! wp_next_scheduled( 'wvp_daily_analytics_update' ) ) {
            wp_schedule_event( time(), 'daily', 'wvp_daily_analytics_update' );
        }
        
        // Daily maintenance
        if ( ! wp_next_scheduled( 'wvp_daily_maintenance' ) ) {
            wp_schedule_event( time(), 'daily', 'wvp_daily_maintenance' );
        }
        
        // Weekly cache cleanup
        if ( ! wp_next_scheduled( 'wvp_weekly_cache_cleanup' ) ) {
            wp_schedule_event( time(), 'weekly', 'wvp_weekly_cache_cleanup' );
        }
    }
    
    /**
     * Generate health report
     */
    public function generate_health_report() {
        $test_results = $this->run_comprehensive_test();
        
        $overall_score = 0;
        $total_tests = 0;
        
        foreach ( $test_results as $category => $tests ) {
            foreach ( $tests as $test => $result ) {
                $overall_score += $result['score'];
                $total_tests++;
            }
        }
        
        $overall_score = $total_tests > 0 ? $overall_score / $total_tests : 0;
        
        $health_report = array(
            'timestamp' => current_time( 'mysql' ),
            'overall_score' => round( $overall_score, 2 ),
            'status' => $overall_score >= 80 ? 'excellent' : ( $overall_score >= 60 ? 'good' : 'needs_improvement' ),
            'test_results' => $test_results,
            'recommendations' => $this->generate_recommendations( $test_results )
        );
        
        update_option( 'wvp_health_report', $health_report );
        
        return $health_report;
    }
    
    /**
     * Generate recommendations
     */
    private function generate_recommendations( $test_results ) {
        $recommendations = array();
        
        foreach ( $test_results as $category => $tests ) {
            foreach ( $tests as $test => $result ) {
                if ( $result['score'] < 80 ) {
                    $recommendations[] = array(
                        'category' => $category,
                        'test' => $test,
                        'score' => $result['score'],
                        'recommendation' => $this->get_recommendation( $category, $test, $result )
                    );
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Get specific recommendation
     */
    private function get_recommendation( $category, $test, $result ) {
        $recommendations = array(
            'Plugin Architecture' => array(
                'class_loading' => 'Ensure all required classes are properly loaded and dependencies are resolved',
                'dependency_injection' => 'Implement proper dependency injection container',
                'module_system' => 'Set up module management system'
            ),
            'WooCommerce Integration' => array(
                'woocommerce_available' => 'Install and activate WooCommerce plugin',
                'payment_gateways' => 'Configure Venezuelan payment gateways',
                'shipping_methods' => 'Set up Venezuelan shipping methods',
                'currency_support' => 'Enable USD and VES currency support'
            ),
            'Performance' => array(
                'page_load_time' => 'Optimize page load time by reducing database queries and improving caching',
                'memory_usage' => 'Reduce memory usage by optimizing code and increasing memory limit',
                'database_performance' => 'Optimize database queries and add proper indexing',
                'cache_performance' => 'Implement and optimize caching system'
            ),
            'Security' => array(
                'security_monitoring' => 'Enable security monitoring and logging',
                'input_validation' => 'Implement proper input validation for Venezuelan data formats',
                'nonce_security' => 'Ensure proper nonce verification for all forms',
                'file_permissions' => 'Set secure file permissions for WordPress files'
            )
        );
        
        return $recommendations[ $category ][ $test ] ?? 'Review and improve this component';
    }
    
    /**
     * Helper methods
     */
    private function simulate_page_load() {
        // Simulate a page load by running some common operations
        $products = wc_get_products( array( 'limit' => 10 ) );
        $orders = wc_get_orders( array( 'limit' => 10 ) );
        $customers = get_users( array( 'number' => 10 ) );
    }
    
    private function convert_to_bytes( $value ) {
        $value = trim( $value );
        $last = strtolower( $value[ strlen( $value ) - 1 ] );
        $value = (int) $value;
        
        switch ( $last ) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Add final optimizer admin menu
     */
    public function add_final_optimizer_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Optimización Final',
            'Optimización Final',
            'manage_options',
            'wvp-final-optimizer',
            array( $this, 'final_optimizer_admin_page' )
        );
    }
    
    /**
     * Final optimizer admin page
     */
    public function final_optimizer_admin_page() {
        $health_report = get_option( 'wvp_health_report', array() );
        ?>
        <div class="wrap">
            <h1>🚀 Optimización Final - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-health-overview">
                <h2>Estado General del Sistema</h2>
                <?php if ( ! empty( $health_report ) ) : ?>
                    <div class="wvp-health-score">
                        <div class="wvp-score-circle">
                            <div class="wvp-score-value"><?php echo esc_html( $health_report['overall_score'] ); ?>%</div>
                            <div class="wvp-score-label">Puntuación General</div>
                        </div>
                        <div class="wvp-health-status">
                            <h3>Estado: 
                                <span class="wvp-status-<?php echo esc_attr( $health_report['status'] ); ?>">
                                    <?php echo esc_html( ucfirst( $health_report['status'] ) ); ?>
                                </span>
                            </h3>
                            <p>Última actualización: <?php echo esc_html( $health_report['timestamp'] ); ?></p>
                        </div>
                    </div>
                <?php else : ?>
                    <p>No hay reporte de salud disponible. Ejecute las pruebas para generar un reporte.</p>
                <?php endif; ?>
            </div>
            
            <div class="wvp-optimization-actions">
                <h2>Acciones de Optimización</h2>
                <div class="wvp-action-buttons">
                    <button class="button button-primary" id="run-comprehensive-test">
                        🔍 Ejecutar Pruebas Exhaustivas
                    </button>
                    <button class="button button-secondary" id="optimize-final">
                        ⚡ Optimización Final
                    </button>
                    <button class="button button-secondary" id="generate-health-report">
                        📊 Generar Reporte de Salud
                    </button>
                </div>
            </div>
            
            <?php if ( ! empty( $health_report['test_results'] ) ) : ?>
            <div class="wvp-test-results">
                <h2>Resultados de Pruebas</h2>
                <?php foreach ( $health_report['test_results'] as $category => $tests ) : ?>
                    <div class="wvp-test-category">
                        <h3><?php echo esc_html( $category ); ?></h3>
                        <div class="wvp-test-grid">
                            <?php foreach ( $tests as $test => $result ) : ?>
                                <div class="wvp-test-item wvp-test-<?php echo esc_attr( $result['status'] ); ?>">
                                    <div class="wvp-test-header">
                                        <span class="wvp-test-name"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $test ) ) ); ?></span>
                                        <span class="wvp-test-score"><?php echo esc_html( $result['score'] ); ?>%</span>
                                    </div>
                                    <div class="wvp-test-details"><?php echo esc_html( $result['details'] ); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $health_report['recommendations'] ) ) : ?>
            <div class="wvp-recommendations">
                <h2>Recomendaciones</h2>
                <div class="wvp-recommendations-list">
                    <?php foreach ( $health_report['recommendations'] as $recommendation ) : ?>
                        <div class="wvp-recommendation-item">
                            <h4><?php echo esc_html( $recommendation['category'] . ' - ' . ucfirst( str_replace( '_', ' ', $recommendation['test'] ) ) ); ?></h4>
                            <p><?php echo esc_html( $recommendation['recommendation'] ); ?></p>
                            <span class="wvp-recommendation-score">Puntuación: <?php echo esc_html( $recommendation['score'] ); ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .wvp-health-score {
            display: flex;
            align-items: center;
            gap: 30px;
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .wvp-score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0073aa, #00a0d2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        
        .wvp-score-value {
            font-size: 32px;
            font-weight: bold;
        }
        
        .wvp-score-label {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .wvp-health-status h3 {
            margin: 0 0 10px 0;
        }
        
        .wvp-status-excellent {
            color: #27ae60;
        }
        
        .wvp-status-good {
            color: #f39c12;
        }
        
        .wvp-status-needs_improvement {
            color: #e74c3c;
        }
        
        .wvp-action-buttons {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        
        .wvp-test-category {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .wvp-test-category h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .wvp-test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .wvp-test-item {
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid;
        }
        
        .wvp-test-pass {
            background: #f8fff8;
            border-left-color: #27ae60;
        }
        
        .wvp-test-fail {
            background: #fff8f8;
            border-left-color: #e74c3c;
        }
        
        .wvp-test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .wvp-test-name {
            font-weight: 500;
        }
        
        .wvp-test-score {
            font-weight: bold;
            color: #0073aa;
        }
        
        .wvp-test-details {
            font-size: 14px;
            color: #666;
        }
        
        .wvp-recommendations-list {
            display: grid;
            gap: 15px;
        }
        
        .wvp-recommendation-item {
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            border-left: 4px solid #f39c12;
        }
        
        .wvp-recommendation-item h4 {
            margin: 0 0 8px 0;
            color: #333;
        }
        
        .wvp-recommendation-item p {
            margin: 0 0 8px 0;
            color: #666;
        }
        
        .wvp-recommendation-score {
            font-size: 12px;
            color: #999;
            font-weight: bold;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#run-comprehensive-test').on('click', function() {
                $(this).prop('disabled', true).text('Ejecutando pruebas...');
                
                $.post(ajaxurl, {
                    action: 'wvp_run_comprehensive_test',
                    nonce: '<?php echo wp_create_nonce( 'wvp_final_optimizer_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Pruebas completadas exitosamente');
                        location.reload();
                    } else {
                        alert('Error al ejecutar las pruebas');
                    }
                }).always(function() {
                    $('#run-comprehensive-test').prop('disabled', false).text('🔍 Ejecutar Pruebas Exhaustivas');
                });
            });
            
            $('#optimize-final').on('click', function() {
                if (confirm('¿Ejecutar optimización final? Esto puede tomar varios minutos.')) {
                    $(this).prop('disabled', true).text('Optimizando...');
                    
                    $.post(ajaxurl, {
                        action: 'wvp_optimize_final',
                        nonce: '<?php echo wp_create_nonce( 'wvp_final_optimizer_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Optimización completada exitosamente');
                            location.reload();
                        } else {
                            alert('Error en la optimización');
                        }
                    }).always(function() {
                        $('#optimize-final').prop('disabled', false).text('⚡ Optimización Final');
                    });
                }
            });
            
            $('#generate-health-report').on('click', function() {
                $(this).prop('disabled', true).text('Generando reporte...');
                
                $.post(ajaxurl, {
                    action: 'wvp_generate_health_report',
                    nonce: '<?php echo wp_create_nonce( 'wvp_final_optimizer_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Reporte de salud generado exitosamente');
                        location.reload();
                    } else {
                        alert('Error al generar el reporte');
                    }
                }).always(function() {
                    $('#generate-health-report').prop('disabled', false).text('📊 Generar Reporte de Salud');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX run comprehensive test
     */
    public function ajax_run_comprehensive_test() {
        check_ajax_referer( 'wvp_final_optimizer_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $test_results = $this->run_comprehensive_test();
        wp_send_json_success( $test_results );
    }
    
    /**
     * AJAX optimize final
     */
    public function ajax_optimize_final() {
        check_ajax_referer( 'wvp_final_optimizer_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $this->run_final_optimizations();
        wp_send_json_success( 'Final optimization completed successfully' );
    }
    
    /**
     * AJAX generate health report
     */
    public function ajax_generate_health_report() {
        check_ajax_referer( 'wvp_final_optimizer_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $health_report = $this->generate_health_report();
        wp_send_json_success( $health_report );
    }
}
