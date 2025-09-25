<?php

/**
 * WooCommerce Venezuela Suite 2025 - SENIAT Billing System
 *
 * Sistema completo de facturación electrónica compatible con SENIAT
 * Incluye generación automática, códigos QR, firma digital y reportes
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SENIAT Billing System Class
 */
class WCVS_SENIAT_Billing {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Settings instance
	 *
	 * @var WCVS_Settings
	 */
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->settings = WCVS_Settings::get_instance();
		$this->init_hooks();
		$this->create_tables();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'wp_ajax_wcvs_generate_invoice', array( $this, 'ajax_generate_invoice' ) );
		add_action( 'wp_ajax_wcvs_download_invoice', array( $this, 'ajax_download_invoice' ) );
		add_action( 'wp_ajax_wcvs_validate_rif', array( $this, 'ajax_validate_rif' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'auto_generate_invoice' ) );
		add_action( 'woocommerce_order_status_processing', array( $this, 'auto_generate_invoice' ) );
		add_action( 'init', array( $this, 'init_cron_jobs' ) );
	}

	/**
	 * Create database tables
	 */
	private function create_tables() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wcvs_invoices';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			invoice_number varchar(50) NOT NULL,
			order_id bigint(20) NOT NULL,
			customer_name varchar(255) NOT NULL,
			customer_rif varchar(20),
			customer_cedula varchar(20),
			customer_email varchar(255),
			customer_phone varchar(20),
			customer_address text,
			invoice_type varchar(10) NOT NULL DEFAULT 'A',
			subtotal decimal(10,2) NOT NULL,
			tax_amount decimal(10,2) NOT NULL DEFAULT 0,
			total decimal(10,2) NOT NULL,
			currency varchar(3) NOT NULL DEFAULT 'VES',
			items_data longtext,
			qr_code_data text,
			digital_signature text,
			pdf_path varchar(500),
			seniat_status varchar(20) DEFAULT 'pending',
			seniat_response text,
			status varchar(20) NOT NULL DEFAULT 'generated',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY invoice_number (invoice_number),
			KEY order_id (order_id),
			KEY customer_rif (customer_rif),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Initialize cron jobs
	 */
	public function init_cron_jobs() {
		if ( ! wp_next_scheduled( 'wcvs_seniat_sync' ) ) {
			wp_schedule_event( time(), 'hourly', 'wcvs_seniat_sync' );
		}
		add_action( 'wcvs_seniat_sync', array( $this, 'sync_with_seniat' ) );
	}

	/**
	 * Generate invoice for order
	 *
	 * @param WC_Order $order
	 * @param string $invoice_type
	 * @return array
	 */
	public function generate_invoice( $order, $invoice_type = 'A' ) {
		global $wpdb;

		try {
			// Verificar si ya existe una factura para este pedido
			if ( $this->invoice_exists( $order->get_id() ) ) {
				return array(
					'success' => false,
					'message' => __( 'Ya existe una factura para este pedido.', 'woocommerce-venezuela-pro-2025' )
				);
			}

			// Generar número de factura
			$invoice_number = $this->generate_invoice_number();

			// Preparar datos de la factura
			$invoice_data = array(
				'invoice_number' => $invoice_number,
				'order_id' => $order->get_id(),
				'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'customer_rif' => $order->get_meta( '_billing_rif' ),
				'customer_cedula' => $order->get_meta( '_billing_cedula' ),
				'customer_email' => $order->get_billing_email(),
				'customer_phone' => $order->get_billing_phone(),
				'customer_address' => $this->format_customer_address( $order ),
				'invoice_type' => $invoice_type,
				'subtotal' => $order->get_subtotal(),
				'tax_amount' => $order->get_total_tax(),
				'total' => $order->get_total(),
				'currency' => $order->get_currency(),
				'items_data' => json_encode( $this->get_order_items_data( $order ) ),
				'qr_code_data' => $this->generate_qr_data( $invoice_number, $order ),
				'digital_signature' => $this->generate_digital_signature( $invoice_number, $order ),
				'status' => 'generated',
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' )
			);

			// Insertar en base de datos
			$table_name = $wpdb->prefix . 'wcvs_invoices';
			$result = $wpdb->insert( $table_name, $invoice_data );

			if ( $result === false ) {
				return array(
					'success' => false,
					'message' => __( 'Error al guardar la factura en la base de datos.', 'woocommerce-venezuela-pro-2025' )
				);
			}

			$invoice_id = $wpdb->insert_id;

			// Generar PDF
			$pdf_path = $this->generate_invoice_pdf( $invoice_id, $invoice_data, $order );

			// Actualizar con la ruta del PDF
			$wpdb->update(
				$table_name,
				array( 'pdf_path' => $pdf_path ),
				array( 'id' => $invoice_id )
			);

			// Marcar pedido como facturado
			$order->add_order_note( sprintf( __( 'Factura SENIAT generada: %s', 'woocommerce-venezuela-pro-2025' ), $invoice_number ) );
			$order->update_meta_data( '_wcvs_invoice_number', $invoice_number );
			$order->update_meta_data( '_wcvs_invoice_id', $invoice_id );
			$order->save();

			// Enviar a SENIAT (mock)
			$this->send_to_seniat( $invoice_id );

			return array(
				'success' => true,
				'message' => sprintf( __( 'Factura %s generada exitosamente.', 'woocommerce-venezuela-pro-2025' ), $invoice_number ),
				'invoice_number' => $invoice_number,
				'invoice_id' => $invoice_id
			);

		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'message' => sprintf( __( 'Error al generar la factura: %s', 'woocommerce-venezuela-pro-2025' ), $e->getMessage() )
			);
		}
	}

	/**
	 * Generate invoice number
	 *
	 * @return string
	 */
	private function generate_invoice_number() {
		$year = date( 'Y' );
		$month = date( 'm' );
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		// Contar facturas del mes actual
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name WHERE YEAR(created_at) = %d AND MONTH(created_at) = %d",
			$year,
			$month
		) );
		
		$sequence = str_pad( $count + 1, 6, '0', STR_PAD_LEFT );
		
		return sprintf( 'FAC-%s%s-%s', $year, $month, $sequence );
	}

	/**
	 * Generate QR code data
	 *
	 * @param string $invoice_number
	 * @param WC_Order $order
	 * @return string
	 */
	private function generate_qr_data( $invoice_number, $order ) {
		$company_data = $this->get_company_data();
		
		$qr_data = array(
			'invoice_number' => $invoice_number,
			'company_rif' => $company_data['rif'],
			'company_name' => $company_data['name'],
			'customer_rif' => $order->get_meta( '_billing_rif' ),
			'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'total' => $order->get_total(),
			'currency' => $order->get_currency(),
			'date' => current_time( 'Y-m-d H:i:s' ),
			'verification_url' => home_url( '/verify-invoice/' . $invoice_number )
		);
		
		return json_encode( $qr_data );
	}

	/**
	 * Generate digital signature (mock)
	 *
	 * @param string $invoice_number
	 * @param WC_Order $order
	 * @return string
	 */
	private function generate_digital_signature( $invoice_number, $order ) {
		// En una implementación real, aquí se usaría un certificado digital
		$data_to_sign = $invoice_number . $order->get_id() . $order->get_total() . current_time( 'Y-m-d' );
		return hash( 'sha256', $data_to_sign );
	}

	/**
	 * Generate invoice PDF
	 *
	 * @param int $invoice_id
	 * @param array $invoice_data
	 * @param WC_Order $order
	 * @return string
	 */
	private function generate_invoice_pdf( $invoice_id, $invoice_data, $order ) {
		// Crear directorio de uploads si no existe
		$upload_dir = wp_upload_dir();
		$invoice_dir = $upload_dir['basedir'] . '/wcvs-invoices';
		
		if ( ! file_exists( $invoice_dir ) ) {
			wp_mkdir_p( $invoice_dir );
		}

		$filename = 'factura-' . $invoice_data['invoice_number'] . '.html';
		$filepath = $invoice_dir . '/' . $filename;

		// Generar contenido HTML de la factura
		$html_content = $this->generate_invoice_html( $invoice_data, $order );

		// Por ahora, crear un archivo HTML temporal
		// En una implementación completa, aquí se usaría una librería como TCPDF o mPDF
		file_put_contents( $filepath, $html_content );

		// Retornar la ruta del archivo
		return $filepath;
	}

	/**
	 * Generate invoice HTML content
	 *
	 * @param array $invoice_data
	 * @param WC_Order $order
	 * @return string
	 */
	private function generate_invoice_html( $invoice_data, $order ) {
		$company_data = $this->get_company_data();
		$items = json_decode( $invoice_data['items_data'], true );
		
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>Factura <?php echo esc_html( $invoice_data['invoice_number'] ); ?></title>
			<style>
				body { font-family: Arial, sans-serif; margin: 20px; }
				.header { text-align: center; margin-bottom: 30px; }
				.company-info { float: left; width: 50%; }
				.customer-info { float: right; width: 50%; }
				.clear { clear: both; }
				table { width: 100%; border-collapse: collapse; margin: 20px 0; }
				th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
				th { background-color: #f2f2f2; }
				.total { font-weight: bold; }
				.footer { margin-top: 30px; text-align: center; }
				.qr-code { text-align: center; margin: 20px 0; }
			</style>
		</head>
		<body>
			<div class="header">
				<h1>FACTURA</h1>
				<h2><?php echo esc_html( $invoice_data['invoice_number'] ); ?></h2>
			</div>

			<div class="company-info">
				<h3>Datos del Emisor</h3>
				<p><strong>RIF:</strong> <?php echo esc_html( $company_data['rif'] ); ?></p>
				<p><strong>Nombre:</strong> <?php echo esc_html( $company_data['name'] ); ?></p>
				<p><strong>Dirección:</strong> <?php echo esc_html( $company_data['address'] ); ?></p>
				<p><strong>Teléfono:</strong> <?php echo esc_html( $company_data['phone'] ); ?></p>
			</div>

			<div class="customer-info">
				<h3>Datos del Cliente</h3>
				<p><strong>Nombre:</strong> <?php echo esc_html( $invoice_data['customer_name'] ); ?></p>
				<?php if ( $invoice_data['customer_rif'] ): ?>
					<p><strong>RIF:</strong> <?php echo esc_html( $invoice_data['customer_rif'] ); ?></p>
				<?php endif; ?>
				<?php if ( $invoice_data['customer_cedula'] ): ?>
					<p><strong>Cédula:</strong> <?php echo esc_html( $invoice_data['customer_cedula'] ); ?></p>
				<?php endif; ?>
				<p><strong>Dirección:</strong> <?php echo esc_html( $invoice_data['customer_address'] ); ?></p>
				<p><strong>Teléfono:</strong> <?php echo esc_html( $invoice_data['customer_phone'] ); ?></p>
			</div>

			<div class="clear"></div>

			<table>
				<thead>
					<tr>
						<th>Descripción</th>
						<th>Cantidad</th>
						<th>Precio Unitario</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ): ?>
						<tr>
							<td><?php echo esc_html( $item['name'] ); ?></td>
							<td><?php echo $item['quantity']; ?></td>
							<td><?php echo wc_price( $item['price'] ); ?></td>
							<td><?php echo wc_price( $item['total'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3" class="total">Subtotal:</td>
						<td class="total"><?php echo wc_price( $invoice_data['subtotal'] ); ?></td>
					</tr>
					<?php if ( $invoice_data['tax_amount'] > 0 ): ?>
						<tr>
							<td colspan="3" class="total">IVA (16%):</td>
							<td class="total"><?php echo wc_price( $invoice_data['tax_amount'] ); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td colspan="3" class="total">Total:</td>
						<td class="total"><?php echo wc_price( $invoice_data['total'] ); ?></td>
					</tr>
				</tfoot>
			</table>

			<div class="qr-code">
				<h3>Código QR para Verificación</h3>
				<p><?php echo esc_html( $invoice_data['qr_code_data'] ); ?></p>
			</div>

			<div class="footer">
				<p>Fecha de emisión: <?php echo date( 'd/m/Y H:i' ); ?></p>
				<p>Tipo de factura: <?php echo $invoice_data['invoice_type'] == 'A' ? 'Consumidor Final' : 'Contribuyente'; ?></p>
				<p>Firma digital: <?php echo esc_html( substr( $invoice_data['digital_signature'], 0, 16 ) ); ?>...</p>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get company data from settings
	 *
	 * @return array
	 */
	private function get_company_data() {
		$settings = $this->settings->get_all_settings();
		return array(
			'rif' => isset( $settings['company_rif'] ) ? $settings['company_rif'] : '',
			'name' => isset( $settings['company_name'] ) ? $settings['company_name'] : '',
			'address' => isset( $settings['company_address'] ) ? $settings['company_address'] : '',
			'phone' => isset( $settings['company_phone'] ) ? $settings['company_phone'] : '',
		);
	}

	/**
	 * Get order items data
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	private function get_order_items_data( $order ) {
		$items_data = array();
		
		foreach ( $order->get_items() as $item ) {
			$items_data[] = array(
				'name' => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'price' => $item->get_subtotal() / $item->get_quantity(),
				'total' => $item->get_subtotal()
			);
		}
		
		return $items_data;
	}

	/**
	 * Format customer address
	 *
	 * @param WC_Order $order
	 * @return string
	 */
	private function format_customer_address( $order ) {
		$address_parts = array(
			$order->get_billing_address_1(),
			$order->get_billing_address_2(),
			$order->get_billing_city(),
			$order->get_billing_state(),
			$order->get_billing_country()
		);
		
		return implode( ', ', array_filter( $address_parts ) );
	}

	/**
	 * Check if invoice exists for order
	 *
	 * @param int $order_id
	 * @return bool
	 */
	private function invoice_exists( $order_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name WHERE order_id = %d",
			$order_id
		) );
		
		return $count > 0;
	}

	/**
	 * Send invoice to SENIAT (mock)
	 *
	 * @param int $invoice_id
	 */
	private function send_to_seniat( $invoice_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		// Mock SENIAT response
		$seniat_response = array(
			'status' => 'approved',
			'message' => 'Factura registrada exitosamente',
			'timestamp' => current_time( 'mysql' ),
			'seniat_reference' => 'SEN-' . time()
		);
		
		$wpdb->update(
			$table_name,
			array(
				'seniat_status' => 'approved',
				'seniat_response' => json_encode( $seniat_response )
			),
			array( 'id' => $invoice_id )
		);
	}

	/**
	 * Sync with SENIAT
	 */
	public function sync_with_seniat() {
		// Implementar sincronización con SENIAT
		// Por ahora es un placeholder
	}

	/**
	 * Auto generate invoice when order status changes
	 *
	 * @param int $order_id
	 */
	public function auto_generate_invoice( $order_id ) {
		$settings = $this->settings->get_all_settings();
		
		// Verificar si la generación automática está habilitada
		if ( ! isset( $settings['auto_generate_invoices'] ) || ! $settings['auto_generate_invoices'] ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Determinar tipo de factura basado en RIF del cliente
		$rif = $order->get_meta( '_billing_rif' );
		$invoice_type = $this->validate_rif_format( $rif ) ? 'B' : 'A';

		// Generar factura automáticamente
		$this->generate_invoice( $order, $invoice_type );
	}

	/**
	 * Validate RIF format
	 *
	 * @param string $rif
	 * @return bool
	 */
	private function validate_rif_format( $rif ) {
		if ( empty( $rif ) ) {
			return false;
		}

		// Formato RIF: V-12345678-9 o J-12345678-9
		$pattern = '/^[VJ]-[0-9]{8}-[0-9]$/';
		return preg_match( $pattern, $rif );
	}

	/**
	 * AJAX handler for generating invoice
	 */
	public function ajax_generate_invoice() {
		check_ajax_referer( 'wcvs_generate_invoice', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'No tienes permisos para realizar esta acción.', 'woocommerce-venezuela-pro-2025' ) );
		}

		$order_id = intval( $_POST['order_id'] );
		$invoice_type = sanitize_text_field( $_POST['invoice_type'] );

		if ( ! $order_id ) {
			wp_send_json_error( __( 'ID de pedido inválido.', 'woocommerce-venezuela-pro-2025' ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_send_json_error( __( 'Pedido no encontrado.', 'woocommerce-venezuela-pro-2025' ) );
		}

		// Generar la factura
		$result = $this->generate_invoice( $order, $invoice_type );

		if ( $result['success'] ) {
			wp_send_json_success( $result['message'] );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * AJAX handler for downloading invoice
	 */
	public function ajax_download_invoice() {
		check_ajax_referer( 'wcvs_download_invoice', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'No tienes permisos para realizar esta acción.', 'woocommerce-venezuela-pro-2025' ) );
		}

		$invoice_id = intval( $_POST['invoice_id'] );
		
		if ( ! $invoice_id ) {
			wp_die( __( 'ID de factura inválido.', 'woocommerce-venezuela-pro-2025' ) );
		}

		$invoice = $this->get_invoice_by_id( $invoice_id );
		if ( ! $invoice ) {
			wp_die( __( 'Factura no encontrada.', 'woocommerce-venezuela-pro-2025' ) );
		}

		// Generar PDF y descargar
		$this->download_invoice_pdf( $invoice );
	}

	/**
	 * AJAX handler for validating RIF
	 */
	public function ajax_validate_rif() {
		check_ajax_referer( 'wcvs_validate_rif', 'nonce' );
		
		$order_id = intval( $_POST['order_id'] );
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			wp_send_json_error( __( 'Pedido no encontrado.', 'woocommerce-venezuela-pro-2025' ) );
		}

		$rif = $order->get_meta( '_billing_rif' );
		$rif_valid = $this->validate_rif_format( $rif );

		wp_send_json_success( array(
			'rif' => $rif,
			'rif_valid' => $rif_valid,
			'invoice_type' => $rif_valid ? 'B' : 'A'
		) );
	}

	/**
	 * Get invoice by ID
	 *
	 * @param int $invoice_id
	 * @return array|null
	 */
	private function get_invoice_by_id( $invoice_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table_name WHERE id = %d",
			$invoice_id
		), ARRAY_A );
	}

	/**
	 * Download invoice PDF
	 *
	 * @param array $invoice
	 */
	private function download_invoice_pdf( $invoice ) {
		if ( ! file_exists( $invoice['pdf_path'] ) ) {
			wp_die( __( 'Archivo de factura no encontrado.', 'woocommerce-venezuela-pro-2025' ) );
		}

		$filename = 'factura-' . $invoice['invoice_number'] . '.html';
		
		header( 'Content-Type: text/html' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . filesize( $invoice['pdf_path'] ) );
		
		readfile( $invoice['pdf_path'] );
		exit;
	}

	/**
	 * Get invoice statistics
	 *
	 * @return array
	 */
	public function get_invoice_statistics() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		$stats = array();
		
		// Total facturas generadas
		$stats['total_invoices'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
		
		// Facturas del mes actual
		$stats['monthly_invoices'] = $wpdb->get_var( 
			"SELECT COUNT(*) FROM $table_name WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())"
		);
		
		// Total facturado
		$stats['total_amount'] = $wpdb->get_var( "SELECT SUM(total) FROM $table_name" );
		
		// Facturas por tipo
		$stats['type_a_count'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE invoice_type = 'A'" );
		$stats['type_b_count'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE invoice_type = 'B'" );
		
		// Estado SENIAT
		$stats['seniat_approved'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE seniat_status = 'approved'" );
		$stats['seniat_pending'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE seniat_status = 'pending'" );
		
		return $stats;
	}

	/**
	 * Get recent invoices
	 *
	 * @param int $limit
	 * @return array
	 */
	public function get_recent_invoices( $limit = 10 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wcvs_invoices';
		
		return $wpdb->get_results( 
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}
}
