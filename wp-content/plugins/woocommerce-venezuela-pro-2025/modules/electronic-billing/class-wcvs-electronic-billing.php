<?php

/**
 * WooCommerce Venezuela Suite 2025 - Electronic Billing Module
 *
 * Módulo de facturación electrónica venezolana (SENIAT)
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Electronic Billing Module
 */
class WCVS_Electronic_Billing {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$all_settings = $this->core->settings->get_all_settings();
		$this->settings = $all_settings['billing'];
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Frontend hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_billing_fields' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'process_electronic_billing' ), 10, 1 );

		// Admin hooks
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_billing_info_admin' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'generate_electronic_invoice' ), 10, 1 );

		// AJAX hooks
		add_action( 'wp_ajax_wcvs_validate_rif', array( $this, 'ajax_validate_rif' ) );
		add_action( 'wp_ajax_nopriv_wcvs_validate_rif', array( $this, 'ajax_validate_rif' ) );
		add_action( 'wp_ajax_wcvs_generate_invoice', array( $this, 'ajax_generate_invoice' ) );

		// Invoice hooks
		add_action( 'woocommerce_thankyou', array( $this, 'display_invoice_info' ), 10, 1 );
		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_invoice_to_email' ), 10, 3 );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'wcvs-electronic-billing',
			WCVS_PLUGIN_URL . 'modules/electronic-billing/js/electronic-billing.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);

		wp_enqueue_style(
			'wcvs-electronic-billing',
			WCVS_PLUGIN_URL . 'modules/electronic-billing/css/electronic-billing.css',
			array(),
			WCVS_VERSION
		);

		wp_localize_script( 'wcvs-electronic-billing', 'wcvs_electronic_billing', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_electronic_billing_nonce' ),
			'company_rif' => isset( $this->settings['company_rif'] ) ? $this->settings['company_rif'] : '',
			'company_name' => isset( $this->settings['company_name'] ) ? $this->settings['company_name'] : '',
			'validation_messages' => array(
				'rif_required' => __( 'El RIF es requerido para facturación', 'woocommerce-venezuela-pro-2025' ),
				'invalid_rif' => __( 'Formato de RIF inválido', 'woocommerce-venezuela-pro-2025' ),
				'cedula_required' => __( 'La Cédula es requerida para facturación', 'woocommerce-venezuela-pro-2025' ),
				'invalid_cedula' => __( 'Formato de Cédula inválido', 'woocommerce-venezuela-pro-2025' ),
				'company_rif_required' => __( 'El RIF de la empresa debe estar configurado', 'woocommerce-venezuela-pro-2025' )
			)
		));
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'wcvs-electronic-billing-admin',
			WCVS_PLUGIN_URL . 'modules/electronic-billing/js/electronic-billing-admin.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);
	}

	/**
	 * Validate billing fields
	 */
	public function validate_billing_fields() {
		if ( ! $this->settings['electronic_billing'] ) {
			return;
		}

		// Validate company RIF
		if ( empty( $this->settings['company_rif'] ) ) {
			wc_add_notice( __( 'El RIF de la empresa debe estar configurado para facturación electrónica.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return;
		}

		// Validate customer RIF
		if ( empty( $_POST['billing_rif'] ) ) {
			wc_add_notice( __( 'El RIF es requerido para facturación electrónica.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		} elseif ( ! $this->validate_rif( $_POST['billing_rif'] ) ) {
			wc_add_notice( __( 'El formato del RIF no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}

		// Validate customer Cédula
		if ( empty( $_POST['billing_cedula'] ) ) {
			wc_add_notice( __( 'La Cédula es requerida para facturación electrónica.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		} elseif ( ! $this->validate_cedula( $_POST['billing_cedula'] ) ) {
			wc_add_notice( __( 'El formato de la Cédula no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
		}
	}

	/**
	 * Process electronic billing
	 *
	 * @param int $order_id
	 */
	public function process_electronic_billing( $order_id ) {
		if ( ! $this->settings['electronic_billing'] ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Save billing information
		$this->save_billing_information( $order );
		
		// Log billing processing
		$this->core->logger->info( 'Electronic billing processed', array(
			'order_id' => $order_id,
			'company_rif' => $this->settings['company_rif'],
			'customer_rif' => $order->get_meta( '_billing_rif' ),
			'customer_cedula' => $order->get_meta( '_billing_cedula' )
		));
	}

	/**
	 * Save billing information
	 *
	 * @param WC_Order $order
	 */
	private function save_billing_information( $order ) {
		// Save company information
		$order->update_meta_data( '_wcvs_company_rif', $this->settings['company_rif'] );
		$order->update_meta_data( '_wcvs_company_name', $this->settings['company_name'] );
		
		// Save customer information
		if ( ! empty( $_POST['billing_rif'] ) ) {
			$order->update_meta_data( '_billing_rif', sanitize_text_field( $_POST['billing_rif'] ) );
		}
		
		if ( ! empty( $_POST['billing_cedula'] ) ) {
			$order->update_meta_data( '_billing_cedula', sanitize_text_field( $_POST['billing_cedula'] ) );
		}
		
		// Mark as electronic billing order
		$order->update_meta_data( '_wcvs_electronic_billing_order', true );
		
		$order->save();
	}

	/**
	 * Generate electronic invoice
	 *
	 * @param int $order_id
	 */
	public function generate_electronic_invoice( $order_id ) {
		if ( ! $this->settings['electronic_billing'] ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->get_meta( '_wcvs_electronic_billing_order' ) ) {
			return;
		}

		// Generate invoice number
		$invoice_number = $this->generate_invoice_number( $order );
		
		// Generate invoice data
		$invoice_data = $this->generate_invoice_data( $order, $invoice_number );
		
		// Save invoice information
		$order->update_meta_data( '_wcvs_invoice_number', $invoice_number );
		$order->update_meta_data( '_wcvs_invoice_data', $invoice_data );
		$order->update_meta_data( '_wcvs_invoice_generated', current_time( 'mysql' ) );
		
		$order->save();
		
		// Log invoice generation
		$this->core->logger->info( 'Electronic invoice generated', array(
			'order_id' => $order_id,
			'invoice_number' => $invoice_number
		));
	}

	/**
	 * Generate invoice number
	 *
	 * @param WC_Order $order
	 * @return string
	 */
	private function generate_invoice_number( $order ) {
		$year = date( 'Y' );
		$month = date( 'm' );
		$sequence = $this->get_next_invoice_sequence( $year, $month );
		
		return sprintf( 'F-%s%s-%06d', $year, $month, $sequence );
	}

	/**
	 * Get next invoice sequence
	 *
	 * @param string $year
	 * @param string $month
	 * @return int
	 */
	private function get_next_invoice_sequence( $year, $month ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		$last_sequence = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) 
			FROM {$table_name} 
			WHERE invoice_number LIKE %s",
			"F-{$year}{$month}-%"
		));
		
		return ( $last_sequence ? $last_sequence + 1 : 1 );
	}

	/**
	 * Generate invoice data
	 *
	 * @param WC_Order $order
	 * @param string $invoice_number
	 * @return array
	 */
	private function generate_invoice_data( $order, $invoice_number ) {
		$invoice_data = array(
			'invoice_number' => $invoice_number,
			'invoice_date' => current_time( 'Y-m-d' ),
			'invoice_time' => current_time( 'H:i:s' ),
			'company' => array(
				'rif' => $this->settings['company_rif'],
				'name' => $this->settings['company_name'],
				'address' => get_option( 'woocommerce_store_address' ),
				'city' => get_option( 'woocommerce_store_city' ),
				'state' => get_option( 'woocommerce_store_state' ),
				'postcode' => get_option( 'woocommerce_store_postcode' ),
				'country' => get_option( 'woocommerce_store_country' )
			),
			'customer' => array(
				'rif' => $order->get_meta( '_billing_rif' ),
				'cedula' => $order->get_meta( '_billing_cedula' ),
				'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'email' => $order->get_billing_email(),
				'phone' => $order->get_billing_phone(),
				'address' => $order->get_billing_address_1(),
				'city' => $order->get_billing_city(),
				'state' => $order->get_billing_state(),
				'postcode' => $order->get_billing_postcode(),
				'country' => $order->get_billing_country()
			),
			'order' => array(
				'id' => $order->get_id(),
				'number' => $order->get_order_number(),
				'date' => $order->get_date_created()->format( 'Y-m-d' ),
				'total' => $order->get_total(),
				'subtotal' => $order->get_subtotal(),
				'tax_total' => $order->get_total_tax(),
				'shipping_total' => $order->get_shipping_total(),
				'payment_method' => $order->get_payment_method_title()
			),
			'items' => array()
		);

		// Add order items
		foreach ( $order->get_items() as $item_id => $item ) {
			$invoice_data['items'][] = array(
				'name' => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'price' => $item->get_total(),
				'tax' => $item->get_total_tax()
			);
		}

		return $invoice_data;
	}

	/**
	 * Display billing info in admin
	 *
	 * @param WC_Order $order
	 */
	public function display_billing_info_admin( $order ) {
		if ( ! $order->get_meta( '_wcvs_electronic_billing_order' ) ) {
			return;
		}

		echo '<div class="wcvs-admin-billing-info">';
		echo '<h4>' . __( 'Información de Facturación Electrónica', 'woocommerce-venezuela-pro-2025' ) . '</h4>';
		
		if ( $order->get_meta( '_wcvs_company_rif' ) ) {
			echo '<p><strong>' . __( 'RIF Empresa:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_company_rif' ) ) . '</p>';
		}
		
		if ( $order->get_meta( '_wcvs_company_name' ) ) {
			echo '<p><strong>' . __( 'Nombre Empresa:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_company_name' ) ) . '</p>';
		}
		
		if ( $order->get_meta( '_wcvs_invoice_number' ) ) {
			echo '<p><strong>' . __( 'Número de Factura:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_invoice_number' ) ) . '</p>';
		}
		
		if ( $order->get_meta( '_wcvs_invoice_generated' ) ) {
			echo '<p><strong>' . __( 'Factura Generada:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_invoice_generated' ) ) . '</p>';
		}
		
		echo '</div>';
	}

	/**
	 * Display invoice info on thank you page
	 *
	 * @param int $order_id
	 */
	public function display_invoice_info( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->get_meta( '_wcvs_electronic_billing_order' ) ) {
			return;
		}

		echo '<div class="wcvs-invoice-info">';
		echo '<h3>' . __( 'Información de Facturación', 'woocommerce-venezuela-pro-2025' ) . '</h3>';
		
		if ( $order->get_meta( '_wcvs_invoice_number' ) ) {
			echo '<p><strong>' . __( 'Número de Factura:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_invoice_number' ) ) . '</p>';
		}
		
		if ( $order->get_meta( '_wcvs_invoice_generated' ) ) {
			echo '<p><strong>' . __( 'Fecha de Factura:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $order->get_meta( '_wcvs_invoice_generated' ) ) . '</p>';
		}
		
		echo '<p>' . __( 'La factura electrónica será enviada por email.', 'woocommerce-venezuela-pro-2025' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Attach invoice to email
	 *
	 * @param array $attachments
	 * @param string $status
	 * @param WC_Order $order
	 * @return array
	 */
	public function attach_invoice_to_email( $attachments, $status, $order ) {
		if ( $status === 'customer_completed_order' && $order->get_meta( '_wcvs_electronic_billing_order' ) ) {
			$invoice_path = $this->generate_invoice_pdf( $order );
			if ( $invoice_path ) {
				$attachments[] = $invoice_path;
			}
		}
		
		return $attachments;
	}

	/**
	 * Generate invoice PDF
	 *
	 * @param WC_Order $order
	 * @return string|false
	 */
	private function generate_invoice_pdf( $order ) {
		// This would generate a PDF invoice
		// For now, return false
		return false;
	}

	/**
	 * AJAX validate RIF
	 */
	public function ajax_validate_rif() {
		check_ajax_referer( 'wcvs_electronic_billing_nonce', 'nonce' );

		$rif = sanitize_text_field( $_POST['rif'] );
		$is_valid = $this->validate_rif( $rif );

		wp_send_json_success( array(
			'valid' => $is_valid,
			'message' => $is_valid ? __( 'RIF válido', 'woocommerce-venezuela-pro-2025' ) : __( 'Formato de RIF inválido', 'woocommerce-venezuela-pro-2025' )
		));
	}

	/**
	 * AJAX generate invoice
	 */
	public function ajax_generate_invoice() {
		check_ajax_referer( 'wcvs_electronic_billing_nonce', 'nonce' );

		$order_id = intval( $_POST['order_id'] );
		$order = wc_get_order( $order_id );

		if ( ! $order || ! $order->get_meta( '_wcvs_electronic_billing_order' ) ) {
			wp_send_json_error( array( 'message' => __( 'Pedido no válido', 'woocommerce-venezuela-pro-2025' ) ) );
		}

		$this->generate_electronic_invoice( $order_id );

		wp_send_json_success( array(
			'message' => __( 'Factura generada exitosamente', 'woocommerce-venezuela-pro-2025' ),
			'invoice_number' => $order->get_meta( '_wcvs_invoice_number' )
		));
	}

	/**
	 * Validate RIF
	 *
	 * @param string $rif
	 * @return bool
	 */
	private function validate_rif( $rif ) {
		return preg_match( '/^[JGVEP]-[0-9]{8}-[0-9]$/', $rif );
	}

	/**
	 * Validate Cédula
	 *
	 * @param string $cedula
	 * @return bool
	 */
	private function validate_cedula( $cedula ) {
		return preg_match( '/^[V]-[0-9]{7,8}$/', $cedula );
	}

	/**
	 * Get billing summary
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	public function get_billing_summary( $order ) {
		$summary = array(
			'company_rif' => $order->get_meta( '_wcvs_company_rif' ),
			'company_name' => $order->get_meta( '_wcvs_company_name' ),
			'customer_rif' => $order->get_meta( '_billing_rif' ),
			'customer_cedula' => $order->get_meta( '_billing_cedula' ),
			'invoice_number' => $order->get_meta( '_wcvs_invoice_number' ),
			'invoice_generated' => $order->get_meta( '_wcvs_invoice_generated' ),
			'electronic_billing' => $order->get_meta( '_wcvs_electronic_billing_order' )
		);

		return $summary;
	}

	/**
	 * Get current billing settings
	 *
	 * @return array
	 */
	public function get_current_billing_settings() {
		return array(
			'electronic_billing' => $this->settings['electronic_billing'],
			'company_rif' => $this->settings['company_rif'],
			'company_name' => $this->settings['company_name']
		);
	}

	/**
	 * Create invoices table
	 */
	public function create_invoices_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			order_id bigint(20) NOT NULL,
			invoice_number varchar(50) NOT NULL,
			invoice_data longtext NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY invoice_number (invoice_number),
			KEY order_id (order_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Drop invoices table
	 */
	public function drop_invoices_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
	}
}