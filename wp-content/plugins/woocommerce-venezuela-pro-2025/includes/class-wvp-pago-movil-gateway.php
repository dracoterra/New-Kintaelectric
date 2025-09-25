<?php
/**
 * Pago Móvil Payment Gateway
 * Venezuelan mobile payment method
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Pago_Movil_Gateway extends WC_Payment_Gateway {
	
	// Declare properties explicitly for PHP 8+ compatibility
	public $phone_number;
	public $cedula;
	public $bank;
	
	public function __construct() {
		$this->id = 'wvp_pago_movil';
		$this->icon = '';
		$this->has_fields = true;
		$this->method_title = 'Pago Móvil Venezuela';
		$this->method_description = 'Permite pagos mediante Pago Móvil venezolano';
		
		// Load settings
		$this->init_form_fields();
		$this->init_settings();
		
		// Define user set variables
		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->phone_number = $this->get_option( 'phone_number' );
		$this->cedula = $this->get_option( 'cedula' );
		$this->bank = $this->get_option( 'bank' );
		
		// Save settings
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	
	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => 'Habilitar/Deshabilitar',
				'type' => 'checkbox',
				'label' => 'Habilitar Pago Móvil',
				'default' => 'yes'
			),
			'title' => array(
				'title' => 'Título',
				'type' => 'text',
				'description' => 'Título que el usuario ve durante el checkout',
				'default' => 'Pago Móvil',
				'desc_tip' => true,
			),
			'description' => array(
				'title' => 'Descripción',
				'type' => 'textarea',
				'description' => 'Descripción que el usuario ve durante el checkout',
				'default' => 'Paga usando Pago Móvil venezolano'
			),
			'phone_number' => array(
				'title' => 'Número de Teléfono',
				'type' => 'text',
				'description' => 'Número de teléfono para Pago Móvil',
				'default' => '',
				'desc_tip' => true,
			),
			'cedula' => array(
				'title' => 'Cédula',
				'type' => 'text',
				'description' => 'Cédula de identidad',
				'default' => '',
				'desc_tip' => true,
			),
			'bank' => array(
				'title' => 'Banco',
				'type' => 'select',
				'description' => 'Banco para Pago Móvil',
				'default' => 'banesco',
				'options' => array(
					'banesco' => 'Banesco',
					'mercantil' => 'Mercantil',
					'bbva' => 'BBVA Provincial',
					'venezuela' => 'Banco de Venezuela',
					'bnc' => 'Banco Nacional de Crédito',
					'100banco' => '100% Banco',
					'banplus' => 'Banplus',
					'bod' => 'BOD',
					'exterior' => 'Banco del Exterior',
					'plaza' => 'Banco Plaza',
					'sofitasa' => 'Sofitasa',
					'venezolano' => 'Banco Venezolano de Crédito'
				)
			)
		);
	}
	
	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
		if ( $this->description ) {
			echo wpautop( wptexturize( $this->description ) );
		}
		
		echo '<div class="wvp-pago-movil-fields">';
		echo '<p class="form-row form-row-wide">';
		echo '<label for="' . $this->id . '_phone">Número de Teléfono <span class="required">*</span></label>';
		echo '<input type="tel" class="input-text" name="' . $this->id . '_phone" id="' . $this->id . '_phone" placeholder="0412-1234567" />';
		echo '</p>';
		
		echo '<p class="form-row form-row-wide">';
		echo '<label for="' . $this->id . '_cedula">Cédula <span class="required">*</span></label>';
		echo '<input type="text" class="input-text" name="' . $this->id . '_cedula" id="' . $this->id . '_cedula" placeholder="V-12345678" />';
		echo '</p>';
		
		echo '<p class="form-row form-row-wide">';
		echo '<label for="' . $this->id . '_bank">Banco <span class="required">*</span></label>';
		echo '<select name="' . $this->id . '_bank" id="' . $this->id . '_bank">';
		foreach ( $this->form_fields['bank']['options'] as $key => $value ) {
			echo '<option value="' . $key . '">' . $value . '</option>';
		}
		echo '</select>';
		echo '</p>';
		echo '</div>';
	}
	
	/**
	 * Process the payment
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		
		// Get payment data
		$phone = sanitize_text_field( $_POST[ $this->id . '_phone' ] );
		$cedula = sanitize_text_field( $_POST[ $this->id . '_cedula' ] );
		$bank = sanitize_text_field( $_POST[ $this->id . '_bank' ] );
		
		// Validate data
		if ( empty( $phone ) || empty( $cedula ) || empty( $bank ) ) {
			wc_add_notice( 'Por favor, completa todos los campos de Pago Móvil.', 'error' );
			return array(
				'result' => 'fail',
				'redirect' => ''
			);
		}
		
		// Validate phone format
		if ( ! $this->validate_venezuelan_phone( $phone ) ) {
			wc_add_notice( 'Formato de teléfono inválido. Use el formato: 0412-1234567', 'error' );
			return array(
				'result' => 'fail',
				'redirect' => ''
			);
		}
		
		// Validate cedula format
		if ( ! $this->validate_venezuelan_cedula( $cedula ) ) {
			wc_add_notice( 'Formato de cédula inválido. Use el formato: V-12345678', 'error' );
			return array(
				'result' => 'fail',
				'redirect' => ''
			);
		}
		
		// Store payment data
		$order->update_meta_data( '_pago_movil_phone', $phone );
		$order->update_meta_data( '_pago_movil_cedula', $cedula );
		$order->update_meta_data( '_pago_movil_bank', $bank );
		$order->update_meta_data( '_pago_movil_reference', $this->generate_reference() );
		$order->save();
		
		// Mark as pending payment
		$order->update_status( 'pending', 'Esperando confirmación de Pago Móvil' );
		
		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );
		
		// Remove cart
		WC()->cart->empty_cart();
		
		// Return success
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}
	
	/**
	 * Validate Venezuelan phone number
	 */
	private function validate_venezuelan_phone( $phone ) {
		// Remove spaces and dashes
		$phone = preg_replace( '/[\s\-]/', '', $phone );
		
		// Check if it starts with 04 and has 11 digits
		return preg_match( '/^04\d{9}$/', $phone );
	}
	
	/**
	 * Validate Venezuelan cedula
	 */
	private function validate_venezuelan_cedula( $cedula ) {
		// Remove spaces and dashes
		$cedula = preg_replace( '/[\s\-]/', '', $cedula );
		
		// Check format: V followed by 7-8 digits
		return preg_match( '/^V\d{7,8}$/', $cedula );
	}
	
	/**
	 * Generate payment reference
	 */
	private function generate_reference() {
		return 'PM' . date( 'Ymd' ) . rand( 1000, 9999 );
	}
}
