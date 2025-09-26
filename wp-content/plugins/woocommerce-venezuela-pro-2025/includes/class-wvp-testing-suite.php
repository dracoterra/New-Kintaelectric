<?php
/**
 * Testing Suite
 * Automated testing system for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Testing_Suite {
    
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
        add_action( 'wp_ajax_wvp_run_unit_tests', array( $this, 'ajax_run_unit_tests' ) );
        add_action( 'wp_ajax_wvp_run_integration_tests', array( $this, 'ajax_run_integration_tests' ) );
        add_action( 'wp_ajax_wvp_run_performance_tests', array( $this, 'ajax_run_performance_tests' ) );
        add_action( 'admin_menu', array( $this, 'add_testing_menu' ), 80 );
        
        // Schedule automated tests
        add_action( 'wvp_daily_testing', array( $this, 'run_daily_tests' ) );
        if ( ! wp_next_scheduled( 'wvp_daily_testing' ) ) {
            wp_schedule_event( time(), 'daily', 'wvp_daily_testing' );
        }
    }
    
    /**
     * Run unit tests
     */
    public function run_unit_tests() {
        $this->test_results = array();
        
        // Test currency conversion
        $this->test_currency_conversion();
        
        // Test tax calculations
        $this->test_tax_calculations();
        
        // Test Venezuelan validators
        $this->test_venezuelan_validators();
        
        // Test SENIAT exports
        $this->test_seniat_exports();
        
        return $this->test_results;
    }
    
    /**
     * Run integration tests
     */
    public function run_integration_tests() {
        $this->test_results = array();
        
        // Test WooCommerce integration
        $this->test_woocommerce_integration();
        
        // Test payment gateways
        $this->test_payment_gateways();
        
        // Test shipping methods
        $this->test_shipping_methods();
        
        // Test admin dashboard
        $this->test_admin_dashboard();
        
        return $this->test_results;
    }
    
    /**
     * Run performance tests
     */
    public function run_performance_tests() {
        $this->test_results = array();
        
        // Test page load times
        $this->test_page_load_times();
        
        // Test memory usage
        $this->test_memory_usage();
        
        // Test database performance
        $this->test_database_performance();
        
        // Test cache performance
        $this->test_cache_performance();
        
        return $this->test_results;
    }
    
    /**
     * Test currency conversion
     */
    private function test_currency_conversion() {
        $test_name = 'Currency Conversion';
        $results = array();
        
        $currency_converter = WVP_Simple_Currency_Converter::get_instance();
        
        // Test 1: Basic conversion
        $usd_price = 100;
        $ves_price = $currency_converter->convert_price( $usd_price, 'USD', 'VES' );
        
        $results['basic_conversion'] = array(
            'status' => $ves_price > 0 ? 'pass' : 'fail',
            'score' => $ves_price > 0 ? 100 : 0,
            'details' => "Converted $100 USD to {$ves_price} VES"
        );
        
        // Test 2: Zero price handling
        $zero_price = $currency_converter->convert_price( 0, 'USD', 'VES' );
        
        $results['zero_price'] = array(
            'status' => $zero_price === 0 ? 'pass' : 'fail',
            'score' => $zero_price === 0 ? 100 : 0,
            'details' => "Zero price conversion: {$zero_price}"
        );
        
        // Test 3: Negative price handling
        $negative_price = $currency_converter->convert_price( -10, 'USD', 'VES' );
        
        $results['negative_price'] = array(
            'status' => $negative_price <= 0 ? 'pass' : 'fail',
            'score' => $negative_price <= 0 ? 100 : 0,
            'details' => "Negative price conversion: {$negative_price}"
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test tax calculations
     */
    private function test_tax_calculations() {
        $test_name = 'Tax Calculations';
        $results = array();
        
        $tax_calculator = WVP_Venezuelan_Taxes::get_instance();
        
        // Test 1: IVA calculation
        $base_price = 100;
        $iva_amount = $tax_calculator->calculate_iva( $base_price );
        $expected_iva = $base_price * 0.16; // 16% IVA
        
        $results['iva_calculation'] = array(
            'status' => abs( $iva_amount - $expected_iva ) < 0.01 ? 'pass' : 'fail',
            'score' => abs( $iva_amount - $expected_iva ) < 0.01 ? 100 : 0,
            'details' => "IVA: {$iva_amount}, Expected: {$expected_iva}"
        );
        
        // Test 2: IGTF calculation
        $igtf_amount = $tax_calculator->calculate_igtf( $base_price );
        $expected_igtf = $base_price * 0.03; // 3% IGTF
        
        $results['igtf_calculation'] = array(
            'status' => abs( $igtf_amount - $expected_igtf ) < 0.01 ? 'pass' : 'fail',
            'score' => abs( $igtf_amount - $expected_igtf ) < 0.01 ? 100 : 0,
            'details' => "IGTF: {$igtf_amount}, Expected: {$expected_igtf}"
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test Venezuelan validators
     */
    private function test_venezuelan_validators() {
        $test_name = 'Venezuelan Validators';
        $results = array();
        
        $validator = WVP_Venezuelan_Validator::get_instance();
        
        // Test 1: RIF validation
        $valid_rif = $validator->validate_rif( 'V-12345678' );
        $invalid_rif = $validator->validate_rif( 'INVALID' );
        
        $results['rif_validation'] = array(
            'status' => $valid_rif && !$invalid_rif ? 'pass' : 'fail',
            'score' => ( $valid_rif && !$invalid_rif ) ? 100 : 0,
            'details' => "Valid RIF: {$valid_rif}, Invalid RIF: {$invalid_rif}"
        );
        
        // Test 2: Phone validation
        $valid_phone = $validator->validate_phone_number( '0412-1234567' );
        $invalid_phone = $validator->validate_phone_number( 'INVALID' );
        
        $results['phone_validation'] = array(
            'status' => $valid_phone && !$invalid_phone ? 'pass' : 'fail',
            'score' => ( $valid_phone && !$invalid_phone ) ? 100 : 0,
            'details' => "Valid phone: {$valid_phone}, Invalid phone: {$invalid_phone}"
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test SENIAT exports
     */
    private function test_seniat_exports() {
        $test_name = 'SENIAT Exports';
        $results = array();
        
        $seniat_exporter = WVP_SENIAT_Exporter::get_instance();
        
        // Test 1: Sales book generation
        $sales_book_method = method_exists( $seniat_exporter, 'generate_sales_book' );
        
        $results['sales_book'] = array(
            'status' => $sales_book_method ? 'pass' : 'fail',
            'score' => $sales_book_method ? 100 : 0,
            'details' => 'Sales book generation method available'
        );
        
        // Test 2: IVA report generation
        $iva_report_method = method_exists( $seniat_exporter, 'generate_iva_report' );
        
        $results['iva_report'] = array(
            'status' => $iva_report_method ? 'pass' : 'fail',
            'score' => $iva_report_method ? 100 : 0,
            'details' => 'IVA report generation method available'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test WooCommerce integration
     */
    private function test_woocommerce_integration() {
        $test_name = 'WooCommerce Integration';
        $results = array();
        
        // Test 1: WooCommerce availability
        $wc_available = class_exists( 'WooCommerce' );
        
        $results['woocommerce_available'] = array(
            'status' => $wc_available ? 'pass' : 'fail',
            'score' => $wc_available ? 100 : 0,
            'details' => 'WooCommerce plugin is active'
        );
        
        // Test 2: Currency support
        $supported_currencies = get_woocommerce_currencies();
        $usd_supported = isset( $supported_currencies['USD'] );
        $ves_supported = isset( $supported_currencies['VES'] );
        
        $results['currency_support'] = array(
            'status' => $usd_supported && $ves_supported ? 'pass' : 'fail',
            'score' => ( $usd_supported && $ves_supported ) ? 100 : 50,
            'details' => 'USD and VES currencies supported'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test payment gateways
     */
    private function test_payment_gateways() {
        $test_name = 'Payment Gateways';
        $results = array();
        
        $payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();
        $venezuelan_gateways = array_filter( $payment_gateways, function( $gateway ) {
            return strpos( $gateway->id, 'wvp_' ) === 0;
        } );
        
        $results['venezuelan_gateways'] = array(
            'status' => count( $venezuelan_gateways ) > 0 ? 'pass' : 'fail',
            'score' => min( 100, count( $venezuelan_gateways ) * 25 ),
            'details' => count( $venezuelan_gateways ) . ' Venezuelan payment gateways available'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test shipping methods
     */
    private function test_shipping_methods() {
        $test_name = 'Shipping Methods';
        $results = array();
        
        $shipping_methods = WC()->shipping()->get_shipping_methods();
        $venezuelan_shipping = array_filter( $shipping_methods, function( $method ) {
            return strpos( $method->id, 'wvp_' ) === 0;
        } );
        
        $results['venezuelan_shipping'] = array(
            'status' => count( $venezuelan_shipping ) > 0 ? 'pass' : 'fail',
            'score' => min( 100, count( $venezuelan_shipping ) * 33 ),
            'details' => count( $venezuelan_shipping ) . ' Venezuelan shipping methods available'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test admin dashboard
     */
    private function test_admin_dashboard() {
        $test_name = 'Admin Dashboard';
        $results = array();
        
        $admin_dashboard = WVP_Admin_Dashboard::get_instance();
        $menu_method = method_exists( $admin_dashboard, 'add_admin_menu' );
        
        $results['admin_menu'] = array(
            'status' => $menu_method ? 'pass' : 'fail',
            'score' => $menu_method ? 100 : 0,
            'details' => 'Admin menu method available'
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test page load times
     */
    private function test_page_load_times() {
        $test_name = 'Page Load Times';
        $results = array();
        
        $start_time = microtime( true );
        $this->simulate_page_load();
        $load_time = microtime( true ) - $start_time;
        
        $results['page_load'] = array(
            'status' => $load_time < 2.0 ? 'pass' : 'fail',
            'score' => max( 0, 100 - ( $load_time * 25 ) ),
            'details' => "Page load time: " . round( $load_time, 3 ) . " seconds"
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test memory usage
     */
    private function test_memory_usage() {
        $test_name = 'Memory Usage';
        $results = array();
        
        $memory_usage = memory_get_usage( true );
        $memory_limit = ini_get( 'memory_limit' );
        $memory_limit_bytes = $this->convert_to_bytes( $memory_limit );
        $memory_percentage = ( $memory_usage / $memory_limit_bytes ) * 100;
        
        $results['memory_usage'] = array(
            'status' => $memory_percentage < 80 ? 'pass' : 'fail',
            'score' => max( 0, 100 - $memory_percentage ),
            'details' => "Memory usage: " . size_format( $memory_usage ) . " / " . $memory_limit
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test database performance
     */
    private function test_database_performance() {
        $test_name = 'Database Performance';
        $results = array();
        
        global $wpdb;
        $start_time = microtime( true );
        $wpdb->get_results( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product'" );
        $query_time = microtime( true ) - $start_time;
        
        $results['query_performance'] = array(
            'status' => $query_time < 0.1 ? 'pass' : 'fail',
            'score' => max( 0, 100 - ( $query_time * 1000 ) ),
            'details' => "Database query time: " . round( $query_time, 4 ) . " seconds"
        );
        
        $this->test_results[ $test_name ] = $results;
    }
    
    /**
     * Test cache performance
     */
    private function test_cache_performance() {
        $test_name = 'Cache Performance';
        $results = array();
        
        $cache_manager = WVP_Cache_Manager::get_instance();
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
     * Run daily tests
     */
    public function run_daily_tests() {
        $unit_results = $this->run_unit_tests();
        $integration_results = $this->run_integration_tests();
        $performance_results = $this->run_performance_tests();
        
        $all_results = array_merge( $unit_results, $integration_results, $performance_results );
        
        // Store results
        update_option( 'wvp_daily_test_results', array(
            'timestamp' => current_time( 'mysql' ),
            'results' => $all_results
        ) );
        
        // Send notification if tests fail
        $this->check_test_failures( $all_results );
    }
    
    /**
     * Check for test failures and send notifications
     */
    private function check_test_failures( $results ) {
        $failures = array();
        
        foreach ( $results as $category => $tests ) {
            foreach ( $tests as $test => $result ) {
                if ( $result['status'] === 'fail' ) {
                    $failures[] = array(
                        'category' => $category,
                        'test' => $test,
                        'details' => $result['details']
                    );
                }
            }
        }
        
        if ( ! empty( $failures ) ) {
            $notification_system = WVP_Notification_System::get_instance();
            $notification_system->send_notification(
                'test_failures',
                array(
                    'subject' => 'WVP Test Failures Detected',
                    'message' => 'The following tests failed: ' . json_encode( $failures ),
                    'recipients' => array( get_option( 'admin_email' ) )
                )
            );
        }
    }
    
    /**
     * Helper methods
     */
    private function simulate_page_load() {
        $products = wc_get_products( array( 'limit' => 10 ) );
        $orders = wc_get_orders( array( 'limit' => 10 ) );
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
     * Add testing admin menu
     */
    public function add_testing_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Testing Suite',
            'Testing Suite',
            'manage_options',
            'wvp-testing-suite',
            array( $this, 'testing_admin_page' )
        );
    }
    
    /**
     * Testing admin page
     */
    public function testing_admin_page() {
        $daily_results = get_option( 'wvp_daily_test_results', array() );
        ?>
        <div class="wrap">
            <h1>🧪 Testing Suite - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-testing-actions">
                <h2>Ejecutar Pruebas</h2>
                <div class="wvp-test-buttons">
                    <button class="button button-primary" id="run-unit-tests">
                        🔬 Pruebas Unitarias
                    </button>
                    <button class="button button-secondary" id="run-integration-tests">
                        🔗 Pruebas de Integración
                    </button>
                    <button class="button button-secondary" id="run-performance-tests">
                        ⚡ Pruebas de Performance
                    </button>
                </div>
            </div>
            
            <?php if ( ! empty( $daily_results ) ) : ?>
            <div class="wvp-daily-results">
                <h2>Resultados Diarios</h2>
                <p>Última ejecución: <?php echo esc_html( $daily_results['timestamp'] ); ?></p>
                
                <div class="wvp-test-results">
                    <?php foreach ( $daily_results['results'] as $category => $tests ) : ?>
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
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .wvp-test-buttons {
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
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#run-unit-tests').on('click', function() {
                $(this).prop('disabled', true).text('Ejecutando pruebas unitarias...');
                
                $.post(ajaxurl, {
                    action: 'wvp_run_unit_tests',
                    nonce: '<?php echo wp_create_nonce( 'wvp_testing_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Pruebas unitarias completadas');
                        location.reload();
                    } else {
                        alert('Error en las pruebas unitarias');
                    }
                }).always(function() {
                    $('#run-unit-tests').prop('disabled', false).text('🔬 Pruebas Unitarias');
                });
            });
            
            $('#run-integration-tests').on('click', function() {
                $(this).prop('disabled', true).text('Ejecutando pruebas de integración...');
                
                $.post(ajaxurl, {
                    action: 'wvp_run_integration_tests',
                    nonce: '<?php echo wp_create_nonce( 'wvp_testing_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Pruebas de integración completadas');
                        location.reload();
                    } else {
                        alert('Error en las pruebas de integración');
                    }
                }).always(function() {
                    $('#run-integration-tests').prop('disabled', false).text('🔗 Pruebas de Integración');
                });
            });
            
            $('#run-performance-tests').on('click', function() {
                $(this).prop('disabled', true).text('Ejecutando pruebas de performance...');
                
                $.post(ajaxurl, {
                    action: 'wvp_run_performance_tests',
                    nonce: '<?php echo wp_create_nonce( 'wvp_testing_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Pruebas de performance completadas');
                        location.reload();
                    } else {
                        alert('Error en las pruebas de performance');
                    }
                }).always(function() {
                    $('#run-performance-tests').prop('disabled', false).text('⚡ Pruebas de Performance');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handlers
     */
    public function ajax_run_unit_tests() {
        check_ajax_referer( 'wvp_testing_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $test_results = $this->run_unit_tests();
        wp_send_json_success( $test_results );
    }
    
    public function ajax_run_integration_tests() {
        check_ajax_referer( 'wvp_testing_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $test_results = $this->run_integration_tests();
        wp_send_json_success( $test_results );
    }
    
    public function ajax_run_performance_tests() {
        check_ajax_referer( 'wvp_testing_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $test_results = $this->run_performance_tests();
        wp_send_json_success( $test_results );
    }
}
