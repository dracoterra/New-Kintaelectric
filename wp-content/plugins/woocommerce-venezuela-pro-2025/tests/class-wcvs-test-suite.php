<?php

/**
 * WooCommerce Venezuela Suite 2025 - Test Suite
 *
 * Suite de pruebas para el plugin
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test Suite class
 */
class WCVS_Test_Suite {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Test results
	 *
	 * @var array
	 */
	private $test_results = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_wcvs_run_tests', array( $this, 'ajax_run_tests' ) );
		add_action( 'wp_ajax_wcvs_get_test_results', array( $this, 'ajax_get_test_results' ) );
	}

	/**
	 * Initialize test suite
	 */
	public function init() {
		// Initialize test environment
		$this->init_test_environment();
		
		// Initialize test data
		$this->init_test_data();
	}

	/**
	 * Initialize test environment
	 */
	private function init_test_environment() {
		// Set test mode
		define( 'WCVS_TEST_MODE', true );
		
		// Initialize test database
		$this->init_test_database();
		
		// Initialize test cache
		$this->init_test_cache();
	}

	/**
	 * Initialize test database
	 */
	private function init_test_database() {
		global $wpdb;
		
		// Create test tables
		$table = $wpdb->prefix . 'wcvs_test_results';
		$wpdb->query( "CREATE TABLE IF NOT EXISTS {$table} (
			id int(11) NOT NULL AUTO_INCREMENT,
			test_name varchar(255) NOT NULL,
			test_result varchar(50) NOT NULL,
			test_message text,
			test_data longtext,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY test_name (test_name),
			KEY test_result (test_result)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" );
	}

	/**
	 * Initialize test cache
	 */
	private function init_test_cache() {
		// Clear test cache
		wp_cache_flush_group( 'wcvs_test' );
	}

	/**
	 * Initialize test data
	 */
	private function init_test_data() {
		// Create test products
		$this->create_test_products();
		
		// Create test orders
		$this->create_test_orders();
		
		// Create test customers
		$this->create_test_customers();
	}

	/**
	 * Create test products
	 */
	private function create_test_products() {
		// Create test product
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product Venezuela' );
		$product->set_price( 100 );
		$product->set_currency( 'VES' );
		$product->set_status( 'publish' );
		$product->save();
		
		// Store test product ID
		update_option( 'wcvs_test_product_id', $product->get_id() );
	}

	/**
	 * Create test orders
	 */
	private function create_test_orders() {
		// Create test order
		$order = wc_create_order();
		$order->set_billing_first_name( 'Test' );
		$order->set_billing_last_name( 'Customer' );
		$order->set_billing_email( 'test@example.com' );
		$order->set_billing_phone( '+58-212-1234567' );
		$order->set_billing_rif( 'V-12345678-9' );
		$order->set_billing_cedula( 'V-12345678' );
		$order->set_total( 100 );
		$order->save();
		
		// Store test order ID
		update_option( 'wcvs_test_order_id', $order->get_id() );
	}

	/**
	 * Create test customers
	 */
	private function create_test_customers() {
		// Create test customer
		$customer = new WP_User();
		$customer->user_login = 'test_customer';
		$customer->user_email = 'test@example.com';
		$customer->user_pass = wp_generate_password();
		$customer->save();
		
		// Store test customer ID
		update_option( 'wcvs_test_customer_id', $customer->ID );
	}

	/**
	 * Run all tests
	 *
	 * @return array
	 */
	public function run_all_tests() {
		$tests = array(
			'test_plugin_activation',
			'test_woocommerce_integration',
			'test_currency_conversion',
			'test_payment_gateways',
			'test_shipping_methods',
			'test_electronic_billing',
			'test_performance',
			'test_security',
			'test_database',
			'test_api_integrations'
		);
		
		$results = array();
		
		foreach ( $tests as $test ) {
			$results[ $test ] = $this->run_test( $test );
		}
		
		$this->test_results = $results;
		$this->save_test_results();
		
		return $results;
	}

	/**
	 * Run single test
	 *
	 * @param string $test_name
	 * @return array
	 */
	public function run_test( $test_name ) {
		$start_time = microtime( true );
		$start_memory = memory_get_usage();
		
		try {
			$result = $this->$test_name();
			$status = 'passed';
			$message = 'Test passed successfully';
		} catch ( Exception $e ) {
			$result = false;
			$status = 'failed';
			$message = $e->getMessage();
		}
		
		$end_time = microtime( true );
		$end_memory = memory_get_usage();
		
		$test_result = array(
			'test_name' => $test_name,
			'status' => $status,
			'result' => $result,
			'message' => $message,
			'execution_time' => $end_time - $start_time,
			'memory_usage' => $end_memory - $start_memory,
			'timestamp' => current_time( 'mysql' )
		);
		
		$this->save_test_result( $test_result );
		
		return $test_result;
	}

	/**
	 * Test plugin activation
	 *
	 * @return bool
	 */
	private function test_plugin_activation() {
		// Test if plugin is active
		if ( ! is_plugin_active( WCVS_PLUGIN_BASENAME ) ) {
			throw new Exception( 'Plugin is not active' );
		}
		
		// Test if core class exists
		if ( ! class_exists( 'WCVS_Core' ) ) {
			throw new Exception( 'Core class not found' );
		}
		
		// Test if core instance exists
		if ( ! WCVS_Core::get_instance() ) {
			throw new Exception( 'Core instance not found' );
		}
		
		return true;
	}

	/**
	 * Test WooCommerce integration
	 *
	 * @return bool
	 */
	private function test_woocommerce_integration() {
		// Test if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			throw new Exception( 'WooCommerce not found' );
		}
		
		// Test if WooCommerce is loaded
		if ( ! WC() ) {
			throw new Exception( 'WooCommerce not loaded' );
		}
		
		// Test if payment gateways are registered
		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		if ( empty( $gateways ) ) {
			throw new Exception( 'No payment gateways found' );
		}
		
		return true;
	}

	/**
	 * Test currency conversion
	 *
	 * @return bool
	 */
	private function test_currency_conversion() {
		// Test currency manager
		if ( ! class_exists( 'WCVS_Currency_Manager' ) ) {
			throw new Exception( 'Currency Manager not found' );
		}
		
		// Test exchange rate
		$rate = $this->core->currency_manager->get_current_exchange_rate();
		if ( $rate <= 0 ) {
			throw new Exception( 'Invalid exchange rate' );
		}
		
		// Test currency conversion
		$converted = $this->core->currency_manager->convert_currency( 100, 'USD', 'VES' );
		if ( $converted <= 0 ) {
			throw new Exception( 'Currency conversion failed' );
		}
		
		return true;
	}

	/**
	 * Test payment gateways
	 *
	 * @return bool
	 */
	private function test_payment_gateways() {
		// Test payment gateways module
		if ( ! class_exists( 'WCVS_Payment_Gateways' ) ) {
			throw new Exception( 'Payment Gateways module not found' );
		}
		
		// Test individual gateways
		$gateways = array(
			'WCVS_Gateway_PagoMovil',
			'WCVS_Gateway_Zelle',
			'WCVS_Gateway_BinancePay',
			'WCVS_Gateway_BankTransfer',
			'WCVS_Gateway_CashDeposit',
			'WCVS_Gateway_Cashea'
		);
		
		foreach ( $gateways as $gateway ) {
			if ( ! class_exists( $gateway ) ) {
				throw new Exception( "Gateway {$gateway} not found" );
			}
		}
		
		return true;
	}

	/**
	 * Test shipping methods
	 *
	 * @return bool
	 */
	private function test_shipping_methods() {
		// Test shipping methods module
		if ( ! class_exists( 'WCVS_Shipping_Methods' ) ) {
			throw new Exception( 'Shipping Methods module not found' );
		}
		
		// Test individual shipping methods
		$methods = array(
			'WCVS_Shipping_MRW',
			'WCVS_Shipping_Zoom',
			'WCVS_Shipping_Tealca',
			'WCVS_Shipping_LocalDelivery',
			'WCVS_Shipping_Pickup'
		);
		
		foreach ( $methods as $method ) {
			if ( ! class_exists( $method ) ) {
				throw new Exception( "Shipping method {$method} not found" );
			}
		}
		
		return true;
	}

	/**
	 * Test electronic billing
	 *
	 * @return bool
	 */
	private function test_electronic_billing() {
		// Test electronic billing module
		if ( ! class_exists( 'WCVS_Electronic_Billing' ) ) {
			throw new Exception( 'Electronic Billing module not found' );
		}
		
		// Test RIF validation
		$valid_rif = 'V-12345678-9';
		$invalid_rif = 'V-12345678';
		
		// Test Cédula validation
		$valid_cedula = 'V-12345678';
		$invalid_cedula = 'V-12345678-9';
		
		return true;
	}

	/**
	 * Test performance
	 *
	 * @return bool
	 */
	private function test_performance() {
		// Test performance module
		if ( ! class_exists( 'WCVS_Performance' ) ) {
			throw new Exception( 'Performance module not found' );
		}
		
		// Test cache functionality
		$cache_key = 'test_cache_key';
		$cache_data = 'test_data';
		
		$this->core->performance->set_cached_data( $cache_key, $cache_data );
		$retrieved_data = $this->core->performance->get_cached_data( $cache_key );
		
		if ( $retrieved_data !== $cache_data ) {
			throw new Exception( 'Cache functionality failed' );
		}
		
		return true;
	}

	/**
	 * Test security
	 *
	 * @return bool
	 */
	private function test_security() {
		// Test nonce validation
		$nonce = wp_create_nonce( 'wcvs_test' );
		if ( ! wp_verify_nonce( $nonce, 'wcvs_test' ) ) {
			throw new Exception( 'Nonce validation failed' );
		}
		
		// Test sanitization
		$dirty_input = '<script>alert("xss")</script>';
		$clean_input = sanitize_text_field( $dirty_input );
		if ( $clean_input !== 'alert("xss")' ) {
			throw new Exception( 'Input sanitization failed' );
		}
		
		return true;
	}

	/**
	 * Test database
	 *
	 * @return bool
	 */
	private function test_database() {
		global $wpdb;
		
		// Test database connection
		$result = $wpdb->get_var( 'SELECT 1' );
		if ( $result !== '1' ) {
			throw new Exception( 'Database connection failed' );
		}
		
		// Test custom tables
		$table = $wpdb->prefix . 'wcvs_exchange_rates_history';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
		if ( ! $table_exists ) {
			throw new Exception( 'Custom table not found' );
		}
		
		return true;
	}

	/**
	 * Test API integrations
	 *
	 * @return bool
	 */
	private function test_api_integrations() {
		// Test BCV API
		if ( ! class_exists( 'WCVS_Exchange_Source_BCV' ) ) {
			throw new Exception( 'BCV API class not found' );
		}
		
		// Test Dólar Today API
		if ( ! class_exists( 'WCVS_Exchange_Source_DolarToday' ) ) {
			throw new Exception( 'Dólar Today API class not found' );
		}
		
		// Test EnParaleloVzla API
		if ( ! class_exists( 'WCVS_Exchange_Source_EnParalelo' ) ) {
			throw new Exception( 'EnParaleloVzla API class not found' );
		}
		
		return true;
	}

	/**
	 * Save test result
	 *
	 * @param array $test_result
	 */
	private function save_test_result( $test_result ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_test_results';
		$wpdb->insert(
			$table,
			array(
				'test_name' => $test_result['test_name'],
				'test_result' => $test_result['status'],
				'test_message' => $test_result['message'],
				'test_data' => json_encode( $test_result ),
				'created_at' => $test_result['timestamp']
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Save test results
	 */
	private function save_test_results() {
		update_option( 'wcvs_test_results', $this->test_results );
		update_option( 'wcvs_test_results_timestamp', current_time( 'mysql' ) );
	}

	/**
	 * Get test results
	 *
	 * @return array
	 */
	public function get_test_results() {
		return $this->test_results;
	}

	/**
	 * AJAX handler for running tests
	 */
	public function ajax_run_tests() {
		check_ajax_referer( 'wcvs_run_tests', 'nonce' );
		
		$results = $this->run_all_tests();
		
		wp_send_json_success( $results );
	}

	/**
	 * AJAX handler for getting test results
	 */
	public function ajax_get_test_results() {
		check_ajax_referer( 'wcvs_get_test_results', 'nonce' );
		
		$results = get_option( 'wcvs_test_results', array() );
		
		wp_send_json_success( $results );
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		// Add test suite specific frontend functionality
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_test_scripts' ) );
	}

	/**
	 * Enqueue test scripts
	 */
	public function enqueue_test_scripts() {
		// Add test suite script
		wp_enqueue_script(
			'wcvs-test-suite',
			WCVS_PLUGIN_URL . 'tests/js/test-suite.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);
	}
}
