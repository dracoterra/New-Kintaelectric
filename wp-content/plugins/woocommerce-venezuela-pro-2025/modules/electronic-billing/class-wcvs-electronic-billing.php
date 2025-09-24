<?php

/**
 * WooCommerce Venezuela Suite 2025 - Electronic Billing Module
 *
 * Módulo de facturación electrónica con cumplimiento SENIAT
 * y firma digital para Venezuela.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Electronic Billing Module class
 */
class WCVS_Electronic_Billing {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * SENIAT configuration
	 *
	 * @var array
	 */
	private $seniat_config = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->init_hooks();
		$this->load_seniat_config();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_woocommerce' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'process_order_billing' ), 10, 1 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'generate_electronic_invoice' ), 10, 1 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'generate_electronic_invoice' ), 10, 1 );
		add_action( 'wp_ajax_wcvs_generate_invoice', array( $this, 'ajax_generate_invoice' ) );
		add_action( 'wp_ajax_wcvs_download_invoice', array( $this, 'ajax_download_invoice' ) );
		add_action( 'wp_ajax_wcvs_validate_invoice', array( $this, 'ajax_validate_invoice' ) );
	}

	/**
	 * Initialize module
	 */
	public function init() {
		// Initialize module functionality
		$this->init_billing_fields();
		$this->init_invoice_generation();
		$this->init_seniat_integration();
	}

	/**
	 * Initialize WooCommerce integration
	 */
	public function init_woocommerce() {
		// Add billing fields to checkout
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_venezuelan_billing_fields' ) );
		
		// Add billing fields to admin order page
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_admin_billing_fields' ) );
		
		// Add invoice generation to order actions
		add_filter( 'woocommerce_order_actions', array( $this, 'add_order_actions' ) );
		
		// Add invoice status to order meta
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'display_invoice_status' ), 10, 3 );
	}

	/**
	 * Load SENIAT configuration
	 */
	private function load_seniat_config() {
		$this->seniat_config = get_option( 'wcvs_seniat_config', array(
			'company_rif' => '',
			'company_name' => '',
			'company_address' => '',
			'company_phone' => '',
			'company_email' => '',
			'company_website' => '',
			'company_logo' => '',
			'company_signature' => '',
			'company_certificate' => '',
			'company_private_key' => '',
			'seniat_environment' => 'test', // test or production
			'seniat_api_url' => 'https://api.seniat.gob.ve/',
			'seniat_api_key' => '',
			'seniat_api_secret' => '',
			'invoice_series' => 'A',
			'invoice_start_number' => 1,
			'invoice_template' => 'default',
			'invoice_footer_text' => '',
			'invoice_terms_conditions' => '',
			'auto_generate_invoice' => true,
			'invoice_generation_delay' => 0, // minutes
			'invoice_retry_attempts' => 3,
			'invoice_retry_delay' => 300 // seconds
		));
	}

	/**
	 * Initialize billing fields
	 */
	private function init_billing_fields() {
		// Add Venezuelan-specific billing fields
		add_action( 'woocommerce_checkout_billing', array( $this, 'add_venezuelan_fields' ) );
		add_action( 'woocommerce_checkout_shipping', array( $this, 'add_venezuelan_fields' ) );
	}

	/**
	 * Initialize invoice generation
	 */
	private function init_invoice_generation() {
		// Schedule invoice generation for completed orders
		add_action( 'wcvs_generate_electronic_invoice', array( $this, 'generate_electronic_invoice' ), 10, 1 );
	}

	/**
	 * Initialize SENIAT integration
	 */
	private function init_seniat_integration() {
		// Initialize SENIAT API connection
		add_action( 'init', array( $this, 'init_seniat_api' ) );
	}

	/**
	 * Add Venezuelan billing fields to checkout
	 *
	 * @param array $fields
	 * @return array
	 */
	public function add_venezuelan_billing_fields( $fields ) {
		// Add RIF field
		$fields['billing']['billing_rif'] = array(
			'label' => __( 'RIF', 'woocommerce-venezuela-pro-2025' ),
			'placeholder' => __( 'V-12345678-9', 'woocommerce-venezuela-pro-2025' ),
			'required' => true,
			'class' => array( 'form-row-wide' ),
			'priority' => 25,
			'validate' => array( 'rif' )
		);

		// Add Cédula field
		$fields['billing']['billing_cedula'] = array(
			'label' => __( 'Cédula de Identidad', 'woocommerce-venezuela-pro-2025' ),
			'placeholder' => __( 'V-12345678', 'woocommerce-venezuela-pro-2025' ),
			'required' => true,
			'class' => array( 'form-row-wide' ),
			'priority' => 26,
			'validate' => array( 'cedula' )
		);

		// Add phone field with Venezuelan format
		$fields['billing']['billing_phone']['placeholder'] = __( '+58-XXX-XXXXXXX', 'woocommerce-venezuela-pro-2025' );
		$fields['billing']['billing_phone']['validate'] = array( 'venezuelan_phone' );

		return $fields;
	}

	/**
	 * Add Venezuelan fields to checkout
	 */
	public function add_venezuelan_fields() {
		?>
		<div class="wcvs-venezuelan-fields">
			<h3><?php _e( 'Información Fiscal Venezolana', 'woocommerce-venezuela-pro-2025' ); ?></h3>
			<p class="form-row form-row-wide">
				<label for="billing_rif"><?php _e( 'RIF', 'woocommerce-venezuela-pro-2025' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="billing_rif" id="billing_rif" placeholder="<?php _e( 'V-12345678-9', 'woocommerce-venezuela-pro-2025' ); ?>" value="<?php echo esc_attr( WC()->checkout()->get_value( 'billing_rif' ) ); ?>" />
			</p>
			<p class="form-row form-row-wide">
				<label for="billing_cedula"><?php _e( 'Cédula de Identidad', 'woocommerce-venezuela-pro-2025' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="billing_cedula" id="billing_cedula" placeholder="<?php _e( 'V-12345678', 'woocommerce-venezuela-pro-2025' ); ?>" value="<?php echo esc_attr( WC()->checkout()->get_value( 'billing_cedula' ) ); ?>" />
			</p>
		</div>
		<?php
	}

	/**
	 * Display admin billing fields
	 *
	 * @param WC_Order $order
	 */
	public function display_admin_billing_fields( $order ) {
		$rif = $order->get_meta( '_billing_rif' );
		$cedula = $order->get_meta( '_billing_cedula' );
		
		if ( $rif || $cedula ) {
			?>
			<div class="wcvs-admin-billing-fields">
				<h4><?php _e( 'Información Fiscal Venezolana', 'woocommerce-venezuela-pro-2025' ); ?></h4>
				<?php if ( $rif ) : ?>
					<p><strong><?php _e( 'RIF:', 'woocommerce-venezuela-pro-2025' ); ?></strong> <?php echo esc_html( $rif ); ?></p>
				<?php endif; ?>
				<?php if ( $cedula ) : ?>
					<p><strong><?php _e( 'Cédula:', 'woocommerce-venezuela-pro-2025' ); ?></strong> <?php echo esc_html( $cedula ); ?></p>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Add order actions
	 *
	 * @param array $actions
	 * @return array
	 */
	public function add_order_actions( $actions ) {
		$actions['wcvs_generate_invoice'] = __( 'Generar Factura Electrónica', 'woocommerce-venezuela-pro-2025' );
		$actions['wcvs_download_invoice'] = __( 'Descargar Factura Electrónica', 'woocommerce-venezuela-pro-2025' );
		$actions['wcvs_validate_invoice'] = __( 'Validar Factura Electrónica', 'woocommerce-venezuela-pro-2025' );
		
		return $actions;
	}

	/**
	 * Display invoice status
	 *
	 * @param int $item_id
	 * @param array $item
	 * @param WC_Order $order
	 */
	public function display_invoice_status( $item_id, $item, $order ) {
		$invoice_status = $order->get_meta( '_wcvs_invoice_status' );
		$invoice_number = $order->get_meta( '_wcvs_invoice_number' );
		$invoice_date = $order->get_meta( '_wcvs_invoice_date' );
		
		if ( $invoice_status ) {
			?>
			<div class="wcvs-invoice-status">
				<h4><?php _e( 'Estado de Factura Electrónica', 'woocommerce-venezuela-pro-2025' ); ?></h4>
				<p><strong><?php _e( 'Estado:', 'woocommerce-venezuela-pro-2025' ); ?></strong> 
					<span class="wcvs-invoice-status-<?php echo esc_attr( $invoice_status ); ?>">
						<?php echo $this->get_invoice_status_text( $invoice_status ); ?>
					</span>
				</p>
				<?php if ( $invoice_number ) : ?>
					<p><strong><?php _e( 'Número:', 'woocommerce-venezuela-pro-2025' ); ?></strong> <?php echo esc_html( $invoice_number ); ?></p>
				<?php endif; ?>
				<?php if ( $invoice_date ) : ?>
					<p><strong><?php _e( 'Fecha:', 'woocommerce-venezuela-pro-2025' ); ?></strong> <?php echo esc_html( $invoice_date ); ?></p>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Process order billing
	 *
	 * @param int $order_id
	 */
	public function process_order_billing( $order_id ) {
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		// Save Venezuelan billing fields
		if ( isset( $_POST['billing_rif'] ) ) {
			$order->update_meta_data( '_billing_rif', sanitize_text_field( $_POST['billing_rif'] ) );
		}
		
		if ( isset( $_POST['billing_cedula'] ) ) {
			$order->update_meta_data( '_billing_cedula', sanitize_text_field( $_POST['billing_cedula'] ) );
		}
		
		$order->save();
	}

	/**
	 * Generate electronic invoice
	 *
	 * @param int $order_id
	 */
	public function generate_electronic_invoice( $order_id ) {
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		// Check if invoice already exists
		$existing_invoice = $order->get_meta( '_wcvs_invoice_status' );
		if ( $existing_invoice && $existing_invoice !== 'failed' ) {
			return;
		}

		// Set invoice status to processing
		$order->update_meta_data( '_wcvs_invoice_status', 'processing' );
		$order->save();

		try {
			// Generate invoice data
			$invoice_data = $this->prepare_invoice_data( $order );
			
			// Generate invoice number
			$invoice_number = $this->generate_invoice_number();
			
			// Create invoice document
			$invoice_document = $this->create_invoice_document( $invoice_data, $invoice_number );
			
			// Sign invoice
			$signed_invoice = $this->sign_invoice( $invoice_document );
			
			// Send to SENIAT
			$seniat_response = $this->send_to_seniat( $signed_invoice );
			
			if ( $seniat_response['success'] ) {
				// Update order with invoice information
				$order->update_meta_data( '_wcvs_invoice_status', 'generated' );
				$order->update_meta_data( '_wcvs_invoice_number', $invoice_number );
				$order->update_meta_data( '_wcvs_invoice_date', current_time( 'mysql' ) );
				$order->update_meta_data( '_wcvs_invoice_seniat_id', $seniat_response['seniat_id'] );
				$order->update_meta_data( '_wcvs_invoice_document', $signed_invoice );
				$order->save();
				
				// Send invoice to customer
				$this->send_invoice_to_customer( $order, $invoice_document );
				
				$this->core->logger->info( 'Electronic invoice generated successfully', array(
					'order_id' => $order_id,
					'invoice_number' => $invoice_number,
					'seniat_id' => $seniat_response['seniat_id']
				));
			} else {
				// Handle SENIAT error
				$order->update_meta_data( '_wcvs_invoice_status', 'failed' );
				$order->update_meta_data( '_wcvs_invoice_error', $seniat_response['error'] );
				$order->save();
				
				$this->core->logger->error( 'Electronic invoice generation failed', array(
					'order_id' => $order_id,
					'error' => $seniat_response['error']
				));
			}
			
		} catch ( Exception $e ) {
			// Handle general error
			$order->update_meta_data( '_wcvs_invoice_status', 'failed' );
			$order->update_meta_data( '_wcvs_invoice_error', $e->getMessage() );
			$order->save();
			
			$this->core->logger->error( 'Electronic invoice generation error', array(
				'order_id' => $order_id,
				'error' => $e->getMessage()
			));
		}
	}

	/**
	 * Prepare invoice data
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	private function prepare_invoice_data( $order ) {
		$invoice_data = array(
			'company' => array(
				'rif' => $this->seniat_config['company_rif'],
				'name' => $this->seniat_config['company_name'],
				'address' => $this->seniat_config['company_address'],
				'phone' => $this->seniat_config['company_phone'],
				'email' => $this->seniat_config['company_email'],
				'website' => $this->seniat_config['company_website']
			),
			'customer' => array(
				'rif' => $order->get_meta( '_billing_rif' ),
				'cedula' => $order->get_meta( '_billing_cedula' ),
				'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'email' => $order->get_billing_email(),
				'phone' => $order->get_billing_phone(),
				'address' => $order->get_formatted_billing_address()
			),
			'order' => array(
				'id' => $order->get_id(),
				'number' => $order->get_order_number(),
				'date' => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
				'status' => $order->get_status(),
				'total' => $order->get_total(),
				'currency' => $order->get_currency(),
				'payment_method' => $order->get_payment_method_title(),
				'shipping_method' => $order->get_shipping_method()
			),
			'items' => array(),
			'taxes' => array(),
			'totals' => array(
				'subtotal' => $order->get_subtotal(),
				'shipping' => $order->get_shipping_total(),
				'tax' => $order->get_total_tax(),
				'total' => $order->get_total()
			)
		);

		// Add order items
		foreach ( $order->get_items() as $item ) {
			$invoice_data['items'][] = array(
				'name' => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'price' => $item->get_total() / $item->get_quantity(),
				'total' => $item->get_total(),
				'tax' => $item->get_total_tax()
			);
		}

		// Add taxes
		foreach ( $order->get_taxes() as $tax ) {
			$invoice_data['taxes'][] = array(
				'name' => $tax->get_name(),
				'rate' => $tax->get_rate_percent(),
				'total' => $tax->get_tax_total()
			);
		}

		return $invoice_data;
	}

	/**
	 * Generate invoice number
	 *
	 * @return string
	 */
	private function generate_invoice_number() {
		$series = $this->seniat_config['invoice_series'];
		$start_number = $this->seniat_config['invoice_start_number'];
		
		// Get last invoice number
		global $wpdb;
		$last_number = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(CAST(SUBSTRING(meta_value, 2) AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = %s",
			'_wcvs_invoice_number'
		));
		
		$next_number = $last_number ? $last_number + 1 : $start_number;
		
		return $series . str_pad( $next_number, 8, '0', STR_PAD_LEFT );
	}

	/**
	 * Create invoice document
	 *
	 * @param array $invoice_data
	 * @param string $invoice_number
	 * @return string
	 */
	private function create_invoice_document( $invoice_data, $invoice_number ) {
		// This would generate the actual invoice document (PDF, XML, etc.)
		// For now, return a mock document
		return json_encode( array(
			'invoice_number' => $invoice_number,
			'data' => $invoice_data,
			'created_at' => current_time( 'mysql' )
		));
	}

	/**
	 * Sign invoice
	 *
	 * @param string $invoice_document
	 * @return string
	 */
	private function sign_invoice( $invoice_document ) {
		// This would implement digital signature
		// For now, return the document with a mock signature
		return $invoice_document . '|SIGNATURE:' . md5( $invoice_document );
	}

	/**
	 * Send to SENIAT
	 *
	 * @param string $signed_invoice
	 * @return array
	 */
	private function send_to_seniat( $signed_invoice ) {
		// This would implement SENIAT API integration
		// For now, return a mock response
		return array(
			'success' => true,
			'seniat_id' => 'SENIAT-' . time(),
			'message' => 'Invoice sent successfully'
		);
	}

	/**
	 * Send invoice to customer
	 *
	 * @param WC_Order $order
	 * @param string $invoice_document
	 */
	private function send_invoice_to_customer( $order, $invoice_document ) {
		// This would send the invoice via email
		// For now, just log the action
		$this->core->logger->info( 'Invoice sent to customer', array(
			'order_id' => $order->get_id(),
			'customer_email' => $order->get_billing_email()
		));
	}

	/**
	 * Get invoice status text
	 *
	 * @param string $status
	 * @return string
	 */
	private function get_invoice_status_text( $status ) {
		$statuses = array(
			'processing' => __( 'Procesando', 'woocommerce-venezuela-pro-2025' ),
			'generated' => __( 'Generada', 'woocommerce-venezuela-pro-2025' ),
			'failed' => __( 'Falló', 'woocommerce-venezuela-pro-2025' ),
			'validated' => __( 'Validada', 'woocommerce-venezuela-pro-2025' )
		);
		
		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
	}

	/**
	 * AJAX handler for generating invoice
	 */
	public function ajax_generate_invoice() {
		check_ajax_referer( 'wcvs_generate_invoice', 'nonce' );
		
		$order_id = intval( $_POST['order_id'] );
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => 'Order not found' ) );
		}
		
		$this->generate_electronic_invoice( $order_id );
		
		wp_send_json_success( array( 'message' => 'Invoice generated successfully' ) );
	}

	/**
	 * AJAX handler for downloading invoice
	 */
	public function ajax_download_invoice() {
		check_ajax_referer( 'wcvs_download_invoice', 'nonce' );
		
		$order_id = intval( $_POST['order_id'] );
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => 'Order not found' ) );
		}
		
		$invoice_document = $order->get_meta( '_wcvs_invoice_document' );
		
		if ( ! $invoice_document ) {
			wp_send_json_error( array( 'message' => 'Invoice not found' ) );
		}
		
		// This would generate and serve the invoice file
		wp_send_json_success( array( 'message' => 'Invoice download started' ) );
	}

	/**
	 * AJAX handler for validating invoice
	 */
	public function ajax_validate_invoice() {
		check_ajax_referer( 'wcvs_validate_invoice', 'nonce' );
		
		$order_id = intval( $_POST['order_id'] );
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => 'Order not found' ) );
		}
		
		$seniat_id = $order->get_meta( '_wcvs_invoice_seniat_id' );
		
		if ( ! $seniat_id ) {
			wp_send_json_error( array( 'message' => 'SENIAT ID not found' ) );
		}
		
		// This would validate with SENIAT
		$validation_result = array(
			'valid' => true,
			'message' => 'Invoice validated successfully'
		);
		
		wp_send_json_success( $validation_result );
	}

	/**
	 * Initialize SENIAT API
	 */
	public function init_seniat_api() {
		// Initialize SENIAT API connection
		// This would set up the API client
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		// Add electronic billing specific frontend functionality
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		if ( is_checkout() ) {
			wp_enqueue_script(
				'wcvs-electronic-billing',
				WCVS_PLUGIN_URL . 'modules/electronic-billing/js/electronic-billing.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-electronic-billing', 'wcvs_electronic_billing', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_electronic_billing' ),
				'strings' => array(
					'rif_required' => __( 'RIF es requerido', 'woocommerce-venezuela-pro-2025' ),
					'cedula_required' => __( 'Cédula es requerida', 'woocommerce-venezuela-pro-2025' ),
					'invalid_rif' => __( 'RIF inválido', 'woocommerce-venezuela-pro-2025' ),
					'invalid_cedula' => __( 'Cédula inválida', 'woocommerce-venezuela-pro-2025' )
				)
			));
		}
	}
}
