<?php

/**
 * WooCommerce Venezuela Suite 2025 - Zelle Gateway
 *
 * Pasarela de pago para Zelle
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zelle Gateway class
 */
class WCVS_Gateway_Zelle extends WC_Payment_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id = 'wcvs_zelle';
		$this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/zelle-icon.png';
		$this->has_fields = true;
		$this->method_title = __( 'Zelle', 'woocommerce-venezuela-pro-2025' );
		$this->method_description = __( 'Permite pagos mediante Zelle', 'woocommerce-venezuela-pro-2025' );

		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->zelle_email = $this->get_option( 'zelle_email' );
		$this->zelle_name = $this->get_option( 'zelle_name' );
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
				'label' => __( 'Activar Zelle', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'yes'
			),
			'title' => array(
				'title' => __( 'Título', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Título que el usuario ve durante el checkout', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Zelle', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'description' => array(
				'title' => __( 'Descripción', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Descripción que el usuario ve durante el checkout', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Paga usando Zelle desde tu aplicación bancaria', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'zelle_email' => array(
				'title' => __( 'Email de Zelle', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'email',
				'description' => __( 'Dirección de email registrada en Zelle', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'zelle_name' => array(
				'title' => __( 'Nombre en Zelle', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Nombre que aparece en Zelle', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'instructions' => array(
				'title' => __( 'Instrucciones', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Instrucciones que se mostrarán al cliente', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Realiza el pago usando Zelle y proporciona el número de confirmación.', 'woocommerce-venezuela-pro-2025' ),
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
		if ( empty( $this->zelle_email ) || empty( $this->zelle_name ) ) {
			echo '<div class="wcvs-payment-error">';
			echo '<p>' . __( 'Zelle no está configurado correctamente. Por favor, contacta al administrador.', 'woocommerce-venezuela-pro-2025' ) . '</p>';
			echo '</div>';
			return;
		}

		?>
		<div class="wcvs-payment-info">
			<h4><?php _e( 'Información para Zelle', 'woocommerce-venezuela-pro-2025' ); ?></h4>
			
			<div class="wcvs-payment-details">
				<div class="wcvs-payment-detail">
					<strong><?php _e( 'Email de Zelle:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
					<span><?php echo esc_html( $this->zelle_email ); ?></span>
					<button type="button" class="wcvs-copy-payment-detail" data-text="<?php echo esc_attr( $this->zelle_email ); ?>">
						<?php _e( 'Copiar', 'woocommerce-venezuela-pro-2025' ); ?>
					</button>
				</div>
				
				<div class="wcvs-payment-detail">
					<strong><?php _e( 'Nombre en Zelle:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
					<span><?php echo esc_html( $this->zelle_name ); ?></span>
					<button type="button" class="wcvs-copy-payment-detail" data-text="<?php echo esc_attr( $this->zelle_name ); ?>">
						<?php _e( 'Copiar', 'woocommerce-venezuela-pro-2025' ); ?>
					</button>
				</div>
			</div>
			
			<div class="wcvs-payment-reference">
				<label for="wcvs_zelle_confirmation">
					<?php _e( 'Número de Confirmación de Zelle:', 'woocommerce-venezuela-pro-2025' ); ?>
					<span class="required">*</span>
				</label>
				<input type="text" id="wcvs_zelle_confirmation" name="wcvs_zelle_confirmation" 
					   placeholder="<?php _e( 'Ingresa el número de confirmación', 'woocommerce-venezuela-pro-2025' ); ?>" 
					   required />
				<p class="wcvs-field-description">
					<?php _e( 'Ingresa el número de confirmación que recibiste después de realizar el pago con Zelle.', 'woocommerce-venezuela-pro-2025' ); ?>
				</p>
			</div>
			
			<div class="wcvs-payment-note">
				<p><strong><?php _e( 'Nota:', 'woocommerce-venezuela-pro-2025' ); ?></strong></p>
				<p><?php _e( 'Asegúrate de enviar el pago al email exacto mostrado arriba. El nombre debe coincidir con el mostrado.', 'woocommerce-venezuela-pro-2025' ); ?></p>
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

		// Get payment confirmation
		$payment_confirmation = sanitize_text_field( $_POST['wcvs_zelle_confirmation'] );

		if ( empty( $payment_confirmation ) ) {
			wc_add_notice( __( 'Por favor, ingresa el número de confirmación de Zelle.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Validate payment confirmation format (numeric, 6-12 digits)
		if ( ! preg_match( '/^\d{6,12}$/', $payment_confirmation ) ) {
			wc_add_notice( __( 'El formato del número de confirmación no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Check if confirmation already exists
		if ( $this->payment_confirmation_exists( $payment_confirmation ) ) {
			wc_add_notice( __( 'Este número de confirmación ya ha sido utilizado.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'on-hold', __( 'Esperando confirmación de pago Zelle', 'woocommerce-venezuela-pro-2025' ) );

		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );

		// Remove cart
		WC()->cart->empty_cart();

		// Store payment confirmation
		update_post_meta( $order_id, '_wcvs_zelle_confirmation', $payment_confirmation );

		// Add order note
		$order->add_order_note( sprintf(
			__( 'Pago Zelle iniciado. Confirmación: %s', 'woocommerce-venezuela-pro-2025' ),
			$payment_confirmation
		) );

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

	/**
	 * Check if payment confirmation already exists
	 *
	 * @param string $payment_confirmation
	 * @return bool
	 */
	private function payment_confirmation_exists( $payment_confirmation ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_payment_references';
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE reference = %s",
			$payment_confirmation
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
		$payment_confirmation = get_post_meta( $order_id, '_wcvs_zelle_confirmation', true );

		if ( $order && $payment_confirmation ) {
			?>
			<div class="wcvs-thankyou-payment-info">
				<h3><?php _e( 'Información de Pago Zelle', 'woocommerce-venezuela-pro-2025' ); ?></h3>
				
				<div class="wcvs-payment-details">
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'Email de Zelle:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $this->zelle_email ); ?></span>
					</div>
					
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'Nombre en Zelle:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $this->zelle_name ); ?></span>
					</div>
					
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'Confirmación:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $payment_confirmation ); ?></span>
					</div>
				</div>
				
				<div class="wcvs-payment-instructions">
					<h4><?php _e( 'Instrucciones:', 'woocommerce-venezuela-pro-2025' ); ?></h4>
					<p><?php echo wpautop( wptexturize( $this->instructions ) ); ?></p>
				</div>
				
				<div class="wcvs-payment-note">
					<p><strong><?php _e( 'Importante:', 'woocommerce-venezuela-pro-2025' ); ?></strong></p>
					<p><?php _e( 'Asegúrate de enviar el pago al email exacto mostrado arriba. El nombre debe coincidir con el mostrado.', 'woocommerce-venezuela-pro-2025' ); ?></p>
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
