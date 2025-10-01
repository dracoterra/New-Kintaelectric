<?php
/**
 * Gateway: Transferencia Bancaria (WVS)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_WVS_Bank_Transfer extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = 'wvs_bank_transfer';
		$this->method_title       = __( 'Transferencia Bancaria (Venezuela)', 'woocommerce-venezuela-suite' );
		$this->method_description = __( 'Permite pagos por transferencia bancaria local.', 'woocommerce-venezuela-suite' );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title', __( 'Transferencia Bancaria', 'woocommerce-venezuela-suite' ) );
		$this->instructions = $this->get_option( 'instructions', '' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Habilitar/Deshabilitar', 'woocommerce-venezuela-suite' ),
				'type'    => 'checkbox',
				'label'   => __( 'Habilitar Transferencia Bancaria', 'woocommerce-venezuela-suite' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Título', 'woocommerce-venezuela-suite' ),
				'type'        => 'text',
				'description' => __( 'Controla el título que el usuario ve durante el checkout.', 'woocommerce-venezuela-suite' ),
				'default'     => __( 'Transferencia Bancaria', 'woocommerce-venezuela-suite' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instrucciones', 'woocommerce-venezuela-suite' ),
				'type'        => 'textarea',
				'description' => __( 'Instrucciones que se añaden a la página de gracias.', 'woocommerce-venezuela-suite' ),
				'default'     => __( 'Realice la transferencia y envíe el comprobante.', 'woocommerce-venezuela-suite' ),
				'css'         => 'min-height: 100px;',
			),
		);
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', __( 'Esperando confirmación de transferencia.', 'woocommerce-venezuela-suite' ) );
		WC()->cart->empty_cart();
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	public function thankyou_page( $order_id ) {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}
}


