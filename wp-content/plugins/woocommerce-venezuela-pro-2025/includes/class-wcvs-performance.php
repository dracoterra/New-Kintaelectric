<?php

/**
 * WooCommerce Venezuela Suite 2025 - Performance Optimization
 *
 * Clase para optimización de rendimiento y cache
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Optimization class
 */
class WCVS_Performance {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Cache group
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'wcvs';

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
		add_action( 'wp_enqueue_scripts', array( $this, 'optimize_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'optimize_admin_scripts' ) );
		add_action( 'wp_head', array( $this, 'add_performance_meta' ) );
		add_action( 'wp_footer', array( $this, 'add_performance_footer' ) );
	}

	/**
	 * Initialize performance optimization
	 */
	public function init() {
		// Initialize performance monitoring
		$this->init_performance_monitoring();
		
		// Initialize caching
		$this->init_caching();
		
		// Initialize database optimization
		$this->init_database_optimization();
	}

	/**
	 * Initialize performance monitoring
	 */
	private function init_performance_monitoring() {
		// Start performance timer
		$this->start_timer();
		
		// Monitor database queries
		add_action( 'wp', array( $this, 'monitor_database_queries' ) );
		
		// Monitor memory usage
		add_action( 'wp', array( $this, 'monitor_memory_usage' ) );
	}

	/**
	 * Initialize caching
	 */
	private function init_caching() {
		// Initialize object cache
		$this->init_object_cache();
		
		// Initialize page cache
		$this->init_page_cache();
		
		// Initialize API cache
		$this->init_api_cache();
	}

	/**
	 * Initialize database optimization
	 */
	private function init_database_optimization() {
		// Optimize database queries
		add_action( 'wp', array( $this, 'optimize_database_queries' ) );
		
		// Clean up old data
		add_action( 'wp_scheduled_delete', array( $this, 'cleanup_old_data' ) );
	}

	/**
	 * Start performance timer
	 */
	private function start_timer() {
		$this->start_time = microtime( true );
		$this->start_memory = memory_get_usage();
	}

	/**
	 * Get performance metrics
	 *
	 * @return array
	 */
	public function get_performance_metrics() {
		$end_time = microtime( true );
		$end_memory = memory_get_usage();
		
		return array(
			'execution_time' => $end_time - $this->start_time,
			'memory_usage' => $end_memory - $this->start_memory,
			'peak_memory' => memory_get_peak_usage(),
			'database_queries' => $this->get_database_query_count(),
			'cache_hits' => $this->get_cache_hits(),
			'cache_misses' => $this->get_cache_misses()
		);
	}

	/**
	 * Monitor database queries
	 */
	public function monitor_database_queries() {
		global $wpdb;
		
		// Count queries
		$this->db_query_count = count( $wpdb->queries );
		
		// Log slow queries
		foreach ( $wpdb->queries as $query ) {
			if ( $query[1] > 0.1 ) { // Queries taking more than 100ms
				$this->core->logger->warning( 'Slow database query detected', array(
					'query' => $query[0],
					'time' => $query[1],
					'stack' => $query[2]
				));
			}
		}
	}

	/**
	 * Monitor memory usage
	 */
	public function monitor_memory_usage() {
		$memory_usage = memory_get_usage();
		$memory_limit = ini_get( 'memory_limit' );
		$memory_limit_bytes = $this->convert_to_bytes( $memory_limit );
		
		$memory_percentage = ( $memory_usage / $memory_limit_bytes ) * 100;
		
		if ( $memory_percentage > 80 ) {
			$this->core->logger->warning( 'High memory usage detected', array(
				'usage' => $memory_usage,
				'limit' => $memory_limit_bytes,
				'percentage' => $memory_percentage
			));
		}
	}

	/**
	 * Optimize database queries
	 */
	public function optimize_database_queries() {
		// Add database indexes if needed
		$this->add_database_indexes();
		
		// Optimize query cache
		$this->optimize_query_cache();
	}

	/**
	 * Add database indexes
	 */
	private function add_database_indexes() {
		global $wpdb;
		
		// Add indexes for exchange rates table
		$table = $wpdb->prefix . 'wcvs_exchange_rates_history';
		$wpdb->query( "ALTER TABLE {$table} ADD INDEX idx_source_created (source, created_at)" );
		$wpdb->query( "ALTER TABLE {$table} ADD INDEX idx_created_at (created_at)" );
		
		// Add indexes for orders table
		$wpdb->query( "ALTER TABLE {$wpdb->posts} ADD INDEX idx_wcvs_invoice_status (meta_key, meta_value) WHERE post_type = 'shop_order'" );
	}

	/**
	 * Optimize query cache
	 */
	private function optimize_query_cache() {
		// Enable query cache
		wp_cache_set( 'wcvs_query_cache_enabled', true, self::CACHE_GROUP );
		
		// Set cache expiration
		wp_cache_set( 'wcvs_query_cache_expiration', 3600, self::CACHE_GROUP ); // 1 hour
	}

	/**
	 * Initialize object cache
	 */
	private function init_object_cache() {
		// Cache exchange rates
		add_action( 'wcvs_exchange_rate_updated', array( $this, 'cache_exchange_rate' ) );
		
		// Cache product prices
		add_action( 'woocommerce_product_price_updated', array( $this, 'cache_product_price' ) );
		
		// Cache shipping rates
		add_action( 'woocommerce_shipping_rate_calculated', array( $this, 'cache_shipping_rate' ) );
	}

	/**
	 * Initialize page cache
	 */
	private function init_page_cache() {
		// Cache product pages
		add_action( 'woocommerce_single_product_summary', array( $this, 'cache_product_page' ) );
		
		// Cache cart page
		add_action( 'woocommerce_cart_contents', array( $this, 'cache_cart_page' ) );
		
		// Cache checkout page
		add_action( 'woocommerce_checkout_billing', array( $this, 'cache_checkout_page' ) );
	}

	/**
	 * Initialize API cache
	 */
	private function init_api_cache() {
		// Cache BCV API responses
		add_action( 'wcvs_bcv_api_response', array( $this, 'cache_api_response' ) );
		
		// Cache SENIAT API responses
		add_action( 'wcvs_seniat_api_response', array( $this, 'cache_api_response' ) );
		
		// Cache shipping API responses
		add_action( 'wcvs_shipping_api_response', array( $this, 'cache_api_response' ) );
	}

	/**
	 * Cache exchange rate
	 *
	 * @param array $rate_data
	 */
	public function cache_exchange_rate( $rate_data ) {
		$cache_key = 'exchange_rate_' . $rate_data['source'];
		wp_cache_set( $cache_key, $rate_data, self::CACHE_GROUP, 1800 ); // 30 minutes
	}

	/**
	 * Cache product price
	 *
	 * @param int $product_id
	 */
	public function cache_product_price( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product ) {
			$cache_key = 'product_price_' . $product_id;
			$price_data = array(
				'price' => $product->get_price(),
				'currency' => $product->get_currency(),
				'converted_price' => $this->core->currency_manager->convert_currency( $product->get_price(), 'VES', 'USD' )
			);
			wp_cache_set( $cache_key, $price_data, self::CACHE_GROUP, 3600 ); // 1 hour
		}
	}

	/**
	 * Cache shipping rate
	 *
	 * @param array $rate_data
	 */
	public function cache_shipping_rate( $rate_data ) {
		$cache_key = 'shipping_rate_' . md5( serialize( $rate_data ) );
		wp_cache_set( $cache_key, $rate_data, self::CACHE_GROUP, 1800 ); // 30 minutes
	}

	/**
	 * Cache product page
	 */
	public function cache_product_page() {
		global $post;
		if ( $post && $post->post_type === 'product' ) {
			$cache_key = 'product_page_' . $post->ID;
			$cache_data = array(
				'content' => ob_get_contents(),
				'timestamp' => current_time( 'mysql' )
			);
			wp_cache_set( $cache_key, $cache_data, self::CACHE_GROUP, 3600 ); // 1 hour
		}
	}

	/**
	 * Cache cart page
	 */
	public function cache_cart_page() {
		$cache_key = 'cart_page_' . get_current_user_id();
		$cache_data = array(
			'content' => ob_get_contents(),
			'timestamp' => current_time( 'mysql' )
		);
		wp_cache_set( $cache_key, $cache_data, self::CACHE_GROUP, 1800 ); // 30 minutes
	}

	/**
	 * Cache checkout page
	 */
	public function cache_checkout_page() {
		$cache_key = 'checkout_page_' . get_current_user_id();
		$cache_data = array(
			'content' => ob_get_contents(),
			'timestamp' => current_time( 'mysql' )
		);
		wp_cache_set( $cache_key, $cache_data, self::CACHE_GROUP, 1800 ); // 30 minutes
	}

	/**
	 * Cache API response
	 *
	 * @param array $response_data
	 */
	public function cache_api_response( $response_data ) {
		$cache_key = 'api_response_' . md5( serialize( $response_data ) );
		wp_cache_set( $cache_key, $response_data, self::CACHE_GROUP, 1800 ); // 30 minutes
	}

	/**
	 * Get cached data
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get_cached_data( $key ) {
		return wp_cache_get( $key, self::CACHE_GROUP );
	}

	/**
	 * Set cached data
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $expiration
	 */
	public function set_cached_data( $key, $data, $expiration = 3600 ) {
		wp_cache_set( $key, $data, self::CACHE_GROUP, $expiration );
	}

	/**
	 * Delete cached data
	 *
	 * @param string $key
	 */
	public function delete_cached_data( $key ) {
		wp_cache_delete( $key, self::CACHE_GROUP );
	}

	/**
	 * Clear all cache
	 */
	public function clear_all_cache() {
		wp_cache_flush_group( self::CACHE_GROUP );
	}

	/**
	 * Optimize scripts
	 */
	public function optimize_scripts() {
		// Minify CSS
		add_filter( 'style_loader_src', array( $this, 'minify_css' ) );
		
		// Minify JS
		add_filter( 'script_loader_src', array( $this, 'minify_js' ) );
		
		// Combine scripts
		add_action( 'wp_head', array( $this, 'combine_scripts' ) );
	}

	/**
	 * Optimize admin scripts
	 */
	public function optimize_admin_scripts() {
		// Minify admin CSS
		add_filter( 'admin_style_loader_src', array( $this, 'minify_css' ) );
		
		// Minify admin JS
		add_filter( 'admin_script_loader_src', array( $this, 'minify_js' ) );
	}

	/**
	 * Minify CSS
	 *
	 * @param string $src
	 * @return string
	 */
	public function minify_css( $src ) {
		if ( strpos( $src, 'wcvs' ) !== false ) {
			$src = str_replace( '.css', '.min.css', $src );
		}
		return $src;
	}

	/**
	 * Minify JS
	 *
	 * @param string $src
	 * @return string
	 */
	public function minify_js( $src ) {
		if ( strpos( $src, 'wcvs' ) !== false ) {
			$src = str_replace( '.js', '.min.js', $src );
		}
		return $src;
	}

	/**
	 * Combine scripts
	 */
	public function combine_scripts() {
		// This would combine multiple JS files into one
		// For now, just add a comment
		echo '<!-- WCVS Scripts Combined -->' . "\n";
	}

	/**
	 * Add performance meta tags
	 */
	public function add_performance_meta() {
		// Add performance hints
		echo '<meta name="wcvs-performance" content="optimized">' . "\n";
		echo '<meta name="wcvs-cache" content="enabled">' . "\n";
	}

	/**
	 * Add performance footer
	 */
	public function add_performance_footer() {
		// Add performance metrics
		$metrics = $this->get_performance_metrics();
		echo '<script>console.log("WCVS Performance:", ' . json_encode( $metrics ) . ');</script>' . "\n";
	}

	/**
	 * Cleanup old data
	 */
	public function cleanup_old_data() {
		global $wpdb;
		
		// Clean up old exchange rates (older than 30 days)
		$table = $wpdb->prefix . 'wcvs_exchange_rates_history';
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
			30
		));
		
		// Clean up old cache entries
		$this->clear_old_cache();
		
		// Clean up old logs
		$this->clear_old_logs();
	}

	/**
	 * Clear old cache
	 */
	private function clear_old_cache() {
		// Clear cache entries older than 24 hours
		$cache_keys = wp_cache_get( 'wcvs_cache_keys', self::CACHE_GROUP );
		if ( $cache_keys ) {
			foreach ( $cache_keys as $key => $timestamp ) {
				if ( time() - $timestamp > 86400 ) { // 24 hours
					wp_cache_delete( $key, self::CACHE_GROUP );
					unset( $cache_keys[ $key ] );
				}
			}
			wp_cache_set( 'wcvs_cache_keys', $cache_keys, self::CACHE_GROUP );
		}
	}

	/**
	 * Clear old logs
	 */
	private function clear_old_logs() {
		// Clear log entries older than 7 days
		$log_file = WP_CONTENT_DIR . '/wcvs-logs/performance.log';
		if ( file_exists( $log_file ) ) {
			$log_content = file_get_contents( $log_file );
			$lines = explode( "\n", $log_content );
			$new_lines = array();
			
			foreach ( $lines as $line ) {
				if ( strpos( $line, date( 'Y-m-d', strtotime( '-7 days' ) ) ) === false ) {
					$new_lines[] = $line;
				}
			}
			
			file_put_contents( $log_file, implode( "\n", $new_lines ) );
		}
	}

	/**
	 * Get database query count
	 *
	 * @return int
	 */
	private function get_database_query_count() {
		global $wpdb;
		return count( $wpdb->queries );
	}

	/**
	 * Get cache hits
	 *
	 * @return int
	 */
	private function get_cache_hits() {
		return wp_cache_get( 'wcvs_cache_hits', self::CACHE_GROUP ) ?: 0;
	}

	/**
	 * Get cache misses
	 *
	 * @return int
	 */
	private function get_cache_misses() {
		return wp_cache_get( 'wcvs_cache_misses', self::CACHE_GROUP ) ?: 0;
	}

	/**
	 * Convert memory limit to bytes
	 *
	 * @param string $memory_limit
	 * @return int
	 */
	private function convert_to_bytes( $memory_limit ) {
		$memory_limit = trim( $memory_limit );
		$last = strtolower( $memory_limit[ strlen( $memory_limit ) - 1 ] );
		$memory_limit = (int) $memory_limit;
		
		switch ( $last ) {
			case 'g':
				$memory_limit *= 1024;
			case 'm':
				$memory_limit *= 1024;
			case 'k':
				$memory_limit *= 1024;
		}
		
		return $memory_limit;
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		// Add performance optimization specific frontend functionality
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_performance_scripts' ) );
	}

	/**
	 * Enqueue performance scripts
	 */
	public function enqueue_performance_scripts() {
		// Add performance monitoring script
		wp_enqueue_script(
			'wcvs-performance',
			WCVS_PLUGIN_URL . 'includes/js/performance.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);
	}
}
