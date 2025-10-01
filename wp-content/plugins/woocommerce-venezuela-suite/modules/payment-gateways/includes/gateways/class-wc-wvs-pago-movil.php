<?php
/**
 * Gateway: Pago Móvil (WVS)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_WVS_Pago_Movil extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = 'wvs_pago_movil';
		$this->method_title       = __( 'Pago Móvil (Venezuela)', 'woocommerce-venezuela-suite' );
		$this->method_description = __( 'Permite pagos por Pago Móvil.', 'woocommerce-venezuela-suite' );
		$this->has_fields         = true;
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'title', __( 'Pago Móvil', 'woocommerce-venezuela-suite' ) );
		$this->pm_bank      = $this->get_option( 'pm_bank', '' );
		$this->pm_phone     = $this->get_option( 'pm_phone', '' );
		$this->pm_rif       = $this->get_option( 'pm_rif', '' );
		$this->instructions = $this->get_option( 'instructions', '' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Habilitar/Deshabilitar', 'woocommerce-venezuela-suite' ),
				'type'    => 'checkbox',
				'label'   => __( 'Habilitar Pago Móvil', 'woocommerce-venezuela-suite' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Título', 'woocommerce-venezuela-suite' ),
				'type'        => 'text',
				'description' => __( 'Controla el título que el usuario ve durante el checkout.', 'woocommerce-venezuela-suite' ),
				'default'     => __( 'Pago Móvil', 'woocommerce-venezuela-suite' ),
				'desc_tip'    => true,
			),
			'pm_bank' => array(
				'title'       => __( 'Banco', 'woocommerce-venezuela-suite' ),
				'type'        => 'text',
				'description' => __( 'Banco receptor del Pago Móvil.', 'woocommerce-venezuela-suite' ),
				'default'     => '',
			),
			'pm_phone' => array(
				'title'       => __( 'Teléfono', 'woocommerce-venezuela-suite' ),
				'type'        => 'text',
				'description' => __( 'Teléfono asociado al Pago Móvil (+58-XXX-XXXXXXX).', 'woocommerce-venezuela-suite' ),
				'default'     => '',
			),
			'pm_rif' => array(
				'title'       => __( 'RIF', 'woocommerce-venezuela-suite' ),
				'type'        => 'text',
				'description' => __( 'RIF del receptor (J-12345678-9).', 'woocommerce-venezuela-suite' ),
				'default'     => '',
			),
			'instructions' => array(
				'title'       => __( 'Instrucciones', 'woocommerce-venezuela-suite' ),
				'type'        => 'textarea',
				'description' => __( 'Instrucciones que se añaden a la página de gracias.', 'woocommerce-venezuela-suite' ),
				'default'     => __( 'Realice el Pago Móvil y adjunte referencia.', 'woocommerce-venezuela-suite' ),
				'css'         => 'min-height: 100px;',
			),
		);
	}

	public function payment_fields() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', __( 'Esperando confirmación de Pago Móvil.', 'woocommerce-venezuela-suite' ) );
		WC()->cart->empty_cart();
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	public function thankyou_page( $order_id ) {
		$lines = array();
		if ( ! empty( $this->pm_bank ) ) {
			$lines[] = __( 'Banco: ', 'woocommerce-venezuela-suite' ) . esc_html( $this->pm_bank );
		}
		if ( ! empty( $this->pm_phone ) ) {
			$lines[] = __( 'Teléfono: ', 'woocommerce-venezuela-suite' ) . esc_html( $this->pm_phone );
		}
		if ( ! empty( $this->pm_rif ) ) {
			$lines[] = __( 'RIF: ', 'woocommerce-venezuela-suite' ) . esc_html( $this->pm_rif );
		}
		if ( ! empty( $lines ) ) {
			echo wp_kses_post( wpautop( implode( "\n", $lines ) ) );
		}
	}
}


