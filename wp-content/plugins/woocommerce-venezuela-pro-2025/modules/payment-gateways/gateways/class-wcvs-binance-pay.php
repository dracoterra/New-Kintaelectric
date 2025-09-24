<?php

/**
 * WooCommerce Venezuela Suite 2025 - Binance Pay Gateway
 *
 * Pasarela de pago para Binance Pay (USDT, BTC)
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Binance Pay Gateway class
 */
class WCVS_Gateway_Binance_Pay extends WC_Payment_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id = 'wcvs_binance_pay';
		$this->icon = WCVS_PLUGIN_URL . 'modules/payment-gateways/assets/binance-pay-icon.png';
		$this->has_fields = true;
		$this->method_title = __( 'Binance Pay', 'woocommerce-venezuela-pro-2025' );
		$this->method_description = __( 'Permite pagos mediante Binance Pay (USDT, BTC)', 'woocommerce-venezuela-pro-2025' );

		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->binance_email = $this->get_option( 'binance_email' );
		$this->binance_name = $this->get_option( 'binance_name' );
		$this->supported_coins = $this->get_option( 'supported_coins' );
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
				'label' => __( 'Activar Binance Pay', 'woocommerce-venezuela-pro-2025' ),
				'default' => 'yes'
			),
			'title' => array(
				'title' => __( 'Título', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Título que el usuario ve durante el checkout', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Binance Pay', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'description' => array(
				'title' => __( 'Descripción', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Descripción que el usuario ve durante el checkout', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Paga usando Binance Pay con USDT o BTC', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
			'binance_email' => array(
				'title' => __( 'Email de Binance', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'email',
				'description' => __( 'Dirección de email registrada en Binance', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'binance_name' => array(
				'title' => __( 'Nombre en Binance', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'text',
				'description' => __( 'Nombre que aparece en Binance', 'woocommerce-venezuela-pro-2025' ),
				'default' => '',
				'desc_tip' => true,
			),
			'supported_coins' => array(
				'title' => __( 'Criptomonedas Soportadas', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'multiselect',
				'description' => __( 'Selecciona las criptomonedas que aceptas', 'woocommerce-venezuela-pro-2025' ),
				'default' => array( 'USDT', 'BTC' ),
				'options' => $this->get_supported_coins(),
				'desc_tip' => true,
			),
			'instructions' => array(
				'title' => __( 'Instrucciones', 'woocommerce-venezuela-pro-2025' ),
				'type' => 'textarea',
				'description' => __( 'Instrucciones que se mostrarán al cliente', 'woocommerce-venezuela-pro-2025' ),
				'default' => __( 'Realiza el pago usando Binance Pay y proporciona el ID de transacción.', 'woocommerce-venezuela-pro-2025' ),
				'desc_tip' => true,
			),
		);
	}

	/**
	 * Get supported coins
	 *
	 * @return array
	 */
	private function get_supported_coins() {
		return array(
			'USDT' => __( 'USDT (Tether)', 'woocommerce-venezuela-pro-2025' ),
			'BTC' => __( 'BTC (Bitcoin)', 'woocommerce-venezuela-pro-2025' ),
			'ETH' => __( 'ETH (Ethereum)', 'woocommerce-venezuela-pro-2025' ),
			'BNB' => __( 'BNB (Binance Coin)', 'woocommerce-venezuela-pro-2025' ),
			'BUSD' => __( 'BUSD (Binance USD)', 'woocommerce-venezuela-pro-2025' ),
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
		if ( empty( $this->binance_email ) || empty( $this->binance_name ) ) {
			echo '<div class="wcvs-payment-error">';
			echo '<p>' . __( 'Binance Pay no está configurado correctamente. Por favor, contacta al administrador.', 'woocommerce-venezuela-pro-2025' ) . '</p>';
			echo '</div>';
			return;
		}

		$supported_coins = $this->get_option( 'supported_coins', array( 'USDT', 'BTC' ) );
		$coin_options = $this->get_supported_coins();
		?>
		<div class="wcvs-payment-info">
			<h4><?php _e( 'Información para Binance Pay', 'woocommerce-venezuela-pro-2025' ); ?></h4>
			
			<div class="wcvs-payment-details">
				<div class="wcvs-payment-detail">
					<strong><?php _e( 'Email de Binance:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
					<span><?php echo esc_html( $this->binance_email ); ?></span>
					<button type="button" class="wcvs-copy-payment-detail" data-text="<?php echo esc_attr( $this->binance_email ); ?>">
						<?php _e( 'Copiar', 'woocommerce-venezuela-pro-2025' ); ?>
					</button>
				</div>
				
				<div class="wcvs-payment-detail">
					<strong><?php _e( 'Nombre en Binance:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
					<span><?php echo esc_html( $this->binance_name ); ?></span>
					<button type="button" class="wcvs-copy-payment-detail" data-text="<?php echo esc_attr( $this->binance_name ); ?>">
						<?php _e( 'Copiar', 'woocommerce-venezuela-pro-2025' ); ?>
					</button>
				</div>
			</div>
			
			<div class="wcvs-crypto-selection">
				<label for="wcvs_crypto_coin">
					<?php _e( 'Criptomoneda:', 'woocommerce-venezuela-pro-2025' ); ?>
					<span class="required">*</span>
				</label>
				<select id="wcvs_crypto_coin" name="wcvs_crypto_coin" required>
					<option value=""><?php _e( 'Selecciona una criptomoneda', 'woocommerce-venezuela-pro-2025' ); ?></option>
					<?php foreach ( $supported_coins as $coin ): ?>
						<option value="<?php echo esc_attr( $coin ); ?>">
							<?php echo esc_html( $coin_options[ $coin ] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			
			<div class="wcvs-payment-reference">
				<label for="wcvs_binance_transaction_id">
					<?php _e( 'ID de Transacción de Binance:', 'woocommerce-venezuela-pro-2025' ); ?>
					<span class="required">*</span>
				</label>
				<input type="text" id="wcvs_binance_transaction_id" name="wcvs_binance_transaction_id" 
					   placeholder="<?php _e( 'Ingresa el ID de transacción', 'woocommerce-venezuela-pro-2025' ); ?>" 
					   required />
				<p class="wcvs-field-description">
					<?php _e( 'Ingresa el ID de transacción que recibiste después de realizar el pago con Binance Pay.', 'woocommerce-venezuela-pro-2025' ); ?>
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

		// Get crypto coin and transaction ID
		$crypto_coin = sanitize_text_field( $_POST['wcvs_crypto_coin'] );
		$transaction_id = sanitize_text_field( $_POST['wcvs_binance_transaction_id'] );

		if ( empty( $crypto_coin ) ) {
			wc_add_notice( __( 'Por favor, selecciona una criptomoneda.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		if ( empty( $transaction_id ) ) {
			wc_add_notice( __( 'Por favor, ingresa el ID de transacción de Binance.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Validate transaction ID format (alphanumeric, 8-32 characters)
		if ( ! preg_match( '/^[A-Za-z0-9]{8,32}$/', $transaction_id ) ) {
			wc_add_notice( __( 'El formato del ID de transacción no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Check if transaction ID already exists
		if ( $this->transaction_id_exists( $transaction_id ) ) {
			wc_add_notice( __( 'Este ID de transacción ya ha sido utilizado.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			return array(
				'result' => 'failure'
			);
		}

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'on-hold', __( 'Esperando confirmación de pago Binance Pay', 'woocommerce-venezuela-pro-2025' ) );

		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );

		// Remove cart
		WC()->cart->empty_cart();

		// Store payment information
		update_post_meta( $order_id, '_wcvs_binance_coin', $crypto_coin );
		update_post_meta( $order_id, '_wcvs_binance_transaction_id', $transaction_id );

		// Add order note
		$order->add_order_note( sprintf(
			__( 'Pago Binance Pay iniciado. Criptomoneda: %s, ID: %s', 'woocommerce-venezuela-pro-2025' ),
			$crypto_coin,
			$transaction_id
		) );

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

	/**
	 * Check if transaction ID already exists
	 *
	 * @param string $transaction_id
	 * @return bool
	 */
	private function transaction_id_exists( $transaction_id ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_payment_references';
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE reference = %s",
			$transaction_id
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
		$crypto_coin = get_post_meta( $order_id, '_wcvs_binance_coin', true );
		$transaction_id = get_post_meta( $order_id, '_wcvs_binance_transaction_id', true );

		if ( $order && $transaction_id ) {
			$coin_options = $this->get_supported_coins();
			?>
			<div class="wcvs-thankyou-payment-info">
				<h3><?php _e( 'Información de Pago Binance Pay', 'woocommerce-venezuela-pro-2025' ); ?></h3>
				
				<div class="wcvs-payment-details">
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'Email de Binance:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $this->binance_email ); ?></span>
					</div>
					
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'Nombre en Binance:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $this->binance_name ); ?></span>
					</div>
					
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'Criptomoneda:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $coin_options[ $crypto_coin ] ); ?></span>
					</div>
					
					<div class="wcvs-payment-detail">
						<strong><?php _e( 'ID de Transacción:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span><?php echo esc_html( $transaction_id ); ?></span>
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
