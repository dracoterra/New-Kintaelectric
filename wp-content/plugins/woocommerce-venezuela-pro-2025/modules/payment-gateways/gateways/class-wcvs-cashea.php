<?php

/**
 * WooCommerce Venezuela Suite 2025 - Cashea Gateway
 *
 * Pasarela de pago para Cashea (financiamiento a meses sin intereses)
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cashea Gateway class
 */
class WCVS_Gateway_Cashea extends WC_Payment_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id = 'wcvs_cashea';
		$this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/cashea-icon.png';
		$this->has_fields = true;
		$this->method_title = __( 'Cashea', 'woocommerce-venezuela-pro-2025' );
		$this->method_description = __( 'Permite pagos mediante Cashea (financiamiento a meses sin intereses)', 'woocommerce-venezuela-pro-2025' );

		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->merchant_id = $this->get_option( 'merchant_id' );
		$this->api_key = $this->get_option( 'api_key' );
		$this->instructions = $this->get_option( 'instructions' );

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Activar/Desactivar', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'checkbox',
				'label' => __( 'Activar Cashea', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'yes'
			),
			'title' => array(
				'title' => __( 'Título', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Título que el usuario ve durante el checkout', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Cashea', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'description' => array(
				'title' => __( 'Descripción', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Descripción que el usuario ve durante el checkout', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Financia tu compra a meses sin intereses con Cashea', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'merchant_id' => array(
				'title' => __( 'ID de Comercio', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'ID de comercio proporcionado por Cashea', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'api_key' => array(
				'title' => __( 'Clave API', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'password',
				'description' => __( 'Clave API proporcionada por Cashea', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'instructions' => array(
				'title' => __( 'Instrucciones', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Instrucciones que se mostrarán al cliente', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Completa el proceso de financiamiento con Cashea y proporciona el número de referencia.', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
		);
	}

	/**
	 * Payment fields on checkout page
	 */
	public function payment_fields() {
		if ( $this->description ) {
			echo wpautop( wptexturize( $this->description ) );
		}

		// Display payment information
		$this->display_payment_info();
	}

	/**
	 * Display payment information
	 */
	private function display_payment_info() {
		if ( empty( $this->merchant_id ) || empty( $this->api_key ) ) {
			echo '<div class="wcvs-payment-error">';
			echo '<p>' . __( 'Cashea no está configurado correctamente. Por favor, contacta al administrador.', 'woocommerce-venezuela-pro-2025' ) . '</p>';
			echo '</div>';
			return;
		}

		?>
		<div class="wcvs-payment-info">
			<h4><?php _e( 'Información para Cashea', 'woocommerce-venezuela-pro-2025' ); ?></h4>
			
			<div class="wcvs-cashea-info">
				<p><?php _e( 'Cashea te permite financiar tu compra a meses sin intereses. Completa el proceso de financiamiento y proporciona el número de referencia.', 'woocommerce-venezuela-pro-2025' ); ?></p>
			</div>
			
			<div class="wcvs-payment-reference">
				<label for="wcvs_cashea_reference">
					<?php _e( 'Número de Referencia de Cashea:', 'woocommerce-venezuela-pro-2025' ); ?>
					<span class="required">*</span>
				</label>
				<input type="text" id="wcvs_cashea_reference" name="wcvs_cashea_reference" 
					   placeholder="<?php _e( 'Ingresa el número de referencia', 'woocommerce-venezuela-pro-2025' ); ?>" 
					   required />
				<p class="wcvs-field-description">
					<?php _e( 'Ingresa el número de referencia que recibiste después de completar el proceso de financiamiento con Cashea.', 'woocommerce-venezuela-pro-2025' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return array(
				'result' => 'failure',
				'messages' => __( 'Pedido no encontrado', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// Get payment reference
		$payment_reference = sanitize_text_field( $_POST['wcvs_cashea_reference'] );

		if ( empty( $payment_reference ) ) {
			wc_add_notice( __( 'Por favor, ingresa el número de referencia de Cashea.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Validate payment reference format
		if ( ! preg_match( '/^[A-Za-z0-9]{6,20}$/', $payment_reference ) ) {
			wc_add_notice( __( 'El formato del número de referencia no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Check if reference already exists
		if ( $this->payment_reference_exists( $payment_reference ) ) {
			wc_add_notice( __( 'Este número de referencia ya ha sido utilizado.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'on-hold', __( 'Esperando confirmación de financiamiento Cashea', 'woocommerce-venezuela-pro-2025' ) );

		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );

		// Remove cart
		WC()->cart->empty_cart();

		// Store payment reference
		update_post_meta( $order_id, '_wcvs_cashea_reference', $payment_reference );

		// Add order note
		$order->add_order_note( sprintf(
			__( 'Financiamiento Cashea iniciado. Referencia: %s', 'woocommerce-venezuela-pro-2025' ),
			$payment_reference
		) );

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

	/**
	 * Check if payment reference already exists
	 *
	 * @param string $payment_reference
	 * @return bool
	 */
	private function payment_reference_exists( $payment_reference ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_payment_references';
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE reference = %s",
			$payment_reference
		) );
		
		return $exists > 0;
	}

	/**
	 * Output for the order received page
	 *
	 * @param int $order_id
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		$payment_reference = get_post_meta( $order_id, '_wcvs_cashea_reference', true );

		if ( $order && $payment_reference ) {
			?>
			<div class="wcvs-thankyou-payment-info">
				<h3><?php _e( 'Información de Financiamiento Cashea', 'woocommerce-venezuela-pro-2025' ); ?></h3>
				
				<div class="wcvs-cashea-info">
					<p><?php _e( 'Tu compra ha sido financiada a meses sin intereses con Cashea.', 'woocommerce-venezuela-pro-2025' ); ?></p>
				</div>
				
				<div class="wcvs-payment-details">
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'Referencia:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $payment_reference ); ?></span>
					</div>
				</div>
				
				<div class="wcvs-payment-instructions">
					<h4><?php _e( 'Instrucciones:', 'woocommerce-venezuela-pro-2025' ); ?></h4>
					<p><?php echo wpautop( wptexturize( $this->instructions ) ); ?></p>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Add content to the WC emails
	 *
	 * @param WC_Order $order
	 * @param bool     $sent_to_admin
	 * @param bool     $plain_text
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}
}
