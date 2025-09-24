<?php

/**
 * WooCommerce Venezuela Suite 2025 - HPOS Compatibility
 *
 * Compatibilidad con el Almacenamiento de Pedidos de Alto Rendimiento (HPOS) de WooCommerce
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HPOS Compatibility class
 */
class WCVS_HPOS_Compatibility {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

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
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_hpos_compatibility' ) );
		add_filter( 'woocommerce_order_data_store', array( $this, 'filter_order_data_store' ), 10, 1 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_hpos_order_processed' ), 10, 1 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'handle_hpos_order_status_changed' ), 10, 3 );
	}

	/**
	 * Declare HPOS compatibility
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WCVS_PLUGIN_FILE, true );
		}
	}

	/**
	 * Initialize HPOS compatibility
	 */
	public function init_hpos_compatibility() {
		// Check if HPOS is enabled
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		// Initialize HPOS-specific functionality
		$this->init_hpos_functionality();
	}

	/**
	 * Check if HPOS is enabled
	 *
	 * @return bool
	 */
	private function is_hpos_enabled() {
		return class_exists( '\Automattic\WooCommerce\Admin\Overrides\Order' ) && 
			   wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled();
	}

	/**
	 * Initialize HPOS-specific functionality
	 */
	private function init_hpos_functionality() {
		// Add HPOS-specific hooks
		add_action( 'woocommerce_new_order', array( $this, 'handle_hpos_new_order' ), 10, 1 );
		add_action( 'woocommerce_update_order', array( $this, 'handle_hpos_update_order' ), 10, 1 );
		
		// Add HPOS-specific filters
		add_filter( 'woocommerce_order_query_args', array( $this, 'filter_hpos_order_query_args' ), 10, 1 );
		add_filter( 'woocommerce_order_data_store_query_args', array( $this, 'filter_hpos_data_store_query_args' ), 10, 1 );
	}

	/**
	 * Filter order data store
	 *
	 * @param string $data_store
	 * @return string
	 */
	public function filter_order_data_store( $data_store ) {
		if ( $this->is_hpos_enabled() ) {
			// Use HPOS data store
			return 'Automattic\WooCommerce\Admin\Overrides\Order';
		}
		
		return $data_store;
	}

	/**
	 * Handle HPOS order processed
	 *
	 * @param int $order_id
	 */
	public function handle_hpos_order_processed( $order_id ) {
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		// Handle order processing with HPOS
		$this->process_hpos_order( $order_id );
	}

	/**
	 * Handle HPOS order status changed
	 *
	 * @param int $order_id
	 * @param string $old_status
	 * @param string $new_status
	 */
	public function handle_hpos_order_status_changed( $order_id, $old_status, $new_status ) {
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		// Handle order status change with HPOS
		$this->process_hpos_order_status_change( $order_id, $old_status, $new_status );
	}

	/**
	 * Handle HPOS new order
	 *
	 * @param int $order_id
	 */
	public function handle_hpos_new_order( $order_id ) {
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		// Handle new order creation with HPOS
		$this->process_hpos_new_order( $order_id );
	}

	/**
	 * Handle HPOS update order
	 *
	 * @param int $order_id
	 */
	public function handle_hpos_update_order( $order_id ) {
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		// Handle order update with HPOS
		$this->process_hpos_update_order( $order_id );
	}

	/**
	 * Filter HPOS order query args
	 *
	 * @param array $args
	 * @return array
	 */
	public function filter_hpos_order_query_args( $args ) {
		if ( ! $this->is_hpos_enabled() ) {
			return $args;
		}

		// Add HPOS-specific query arguments
		$args['meta_query'] = $args['meta_query'] ?? array();
		
		// Add Venezuelan-specific meta queries
		$args['meta_query'][] = array(
			'key' => '_billing_rif',
			'compare' => 'EXISTS'
		);

		return $args;
	}

	/**
	 * Filter HPOS data store query args
	 *
	 * @param array $args
	 * @return array
	 */
	public function filter_hpos_data_store_query_args( $args ) {
		if ( ! $this->is_hpos_enabled() ) {
			return $args;
		}

		// Add HPOS-specific data store query arguments
		$args['meta_query'] = $args['meta_query'] ?? array();
		
		// Add Venezuelan-specific meta queries
		$args['meta_query'][] = array(
			'key' => '_billing_cedula',
			'compare' => 'EXISTS'
		);

		return $args;
	}

	/**
	 * Process HPOS order
	 *
	 * @param int $order_id
	 */
	private function process_hpos_order( $order_id ) {
		// Get order using HPOS
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		// Process Venezuelan-specific order data
		$this->process_venezuelan_order_data( $order );
	}

	/**
	 * Process HPOS order status change
	 *
	 * @param int $order_id
	 * @param string $old_status
	 * @param string $new_status
	 */
	private function process_hpos_order_status_change( $order_id, $old_status, $new_status ) {
		// Get order using HPOS
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		// Process status change for Venezuelan orders
		$this->process_venezuelan_order_status_change( $order, $old_status, $new_status );
	}

	/**
	 * Process HPOS new order
	 *
	 * @param int $order_id
	 */
	private function process_hpos_new_order( $order_id ) {
		// Get order using HPOS
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		// Process new Venezuelan order
		$this->process_new_venezuelan_order( $order );
	}

	/**
	 * Process HPOS update order
	 *
	 * @param int $order_id
	 */
	private function process_hpos_update_order( $order_id ) {
		// Get order using HPOS
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		// Process Venezuelan order update
		$this->process_venezuelan_order_update( $order );
	}

	/**
	 * Process Venezuelan order data
	 *
	 * @param WC_Order $order
	 */
	private function process_venezuelan_order_data( $order ) {
		// Process Venezuelan-specific order data
		$rif = $order->get_meta( '_billing_rif' );
		$cedula = $order->get_meta( '_billing_cedula' );
		
		if ( $rif || $cedula ) {
			// Mark as Venezuelan order
			$order->update_meta_data( '_wcvs_venezuelan_order', true );
			$order->save();
		}
	}

	/**
	 * Process Venezuelan order status change
	 *
	 * @param WC_Order $order
	 * @param string $old_status
	 * @param string $new_status
	 */
	private function process_venezuelan_order_status_change( $order, $old_status, $new_status ) {
		// Process status change for Venezuelan orders
		if ( $order->get_meta( '_wcvs_venezuelan_order' ) ) {
			// Handle Venezuelan-specific status changes
			$this->handle_venezuelan_status_change( $order, $old_status, $new_status );
		}
	}

	/**
	 * Process new Venezuelan order
	 *
	 * @param WC_Order $order
	 */
	private function process_new_venezuelan_order( $order ) {
		// Process new Venezuelan order
		if ( $order->get_meta( '_wcvs_venezuelan_order' ) ) {
			// Handle new Venezuelan order
			$this->handle_new_venezuelan_order( $order );
		}
	}

	/**
	 * Process Venezuelan order update
	 *
	 * @param WC_Order $order
	 */
	private function process_venezuelan_order_update( $order ) {
		// Process Venezuelan order update
		if ( $order->get_meta( '_wcvs_venezuelan_order' ) ) {
			// Handle Venezuelan order update
			$this->handle_venezuelan_order_update( $order );
		}
	}

	/**
	 * Handle Venezuelan status change
	 *
	 * @param WC_Order $order
	 * @param string $old_status
	 * @param string $new_status
	 */
	private function handle_venezuelan_status_change( $order, $old_status, $new_status ) {
		// Handle Venezuelan-specific status changes
		switch ( $new_status ) {
			case 'completed':
			case 'processing':
				// Generate electronic invoice
				if ( $this->core->electronic_billing ) {
					$this->core->electronic_billing->generate_electronic_invoice( $order->get_id() );
				}
				break;
		}
	}

	/**
	 * Handle new Venezuelan order
	 *
	 * @param WC_Order $order
	 */
	private function handle_new_venezuelan_order( $order ) {
		// Handle new Venezuelan order
		$this->core->logger->info( 'New Venezuelan order created', array(
			'order_id' => $order->get_id(),
			'rif' => $order->get_meta( '_billing_rif' ),
			'cedula' => $order->get_meta( '_billing_cedula' )
		));
	}

	/**
	 * Handle Venezuelan order update
	 *
	 * @param WC_Order $order
	 */
	private function handle_venezuelan_order_update( $order ) {
		// Handle Venezuelan order update
		$this->core->logger->info( 'Venezuelan order updated', array(
			'order_id' => $order->get_id(),
			'status' => $order->get_status()
		));
	}

	/**
	 * Get HPOS status
	 *
	 * @return array
	 */
	public function get_hpos_status() {
		return array(
			'enabled' => $this->is_hpos_enabled(),
			'compatible' => true,
			'version' => WCVS_VERSION
		);
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		// Add HPOS compatibility specific frontend functionality
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_hpos_scripts' ) );
	}

	/**
	 * Enqueue HPOS scripts
	 */
	public function enqueue_hpos_scripts() {
		if ( $this->is_hpos_enabled() ) {
			wp_enqueue_script(
				'wcvs-hpos-compatibility',
				WCVS_PLUGIN_URL . 'includes/js/hpos-compatibility.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);
		}
	}
}
