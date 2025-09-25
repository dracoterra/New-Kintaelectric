<?php

/**
 * WooCommerce Venezuela Suite 2025 - Dashboard Handlers
 *
 * Handlers AJAX para las funciones del dashboard
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Handlers class
 */
class WCVS_Dashboard_Handlers {

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
		// AJAX handlers for dashboard functions
		add_action( 'wp_ajax_wcvs_configure_currency', array( $this, 'configure_currency' ) );
		add_action( 'wp_ajax_wcvs_configure_taxes', array( $this, 'configure_taxes' ) );
		add_action( 'wp_ajax_wcvs_configure_payments', array( $this, 'configure_payments' ) );
		add_action( 'wp_ajax_wcvs_configure_shipping', array( $this, 'configure_shipping' ) );
		add_action( 'wp_ajax_wcvs_configure_location', array( $this, 'configure_location' ) );
		add_action( 'wp_ajax_wcvs_configure_pages', array( $this, 'configure_pages' ) );
	}

	/**
	 * Configure currency
	 */
	public function configure_currency() {
		check_ajax_referer( 'wcvs_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'No tienes permisos para realizar esta acción' );
		}

		try {
			// Configure WooCommerce currency to USD
			update_option( 'woocommerce_currency', 'USD' );
			update_option( 'woocommerce_price_thousand_separator', '.' );
			update_option( 'woocommerce_price_decimal_separator', ',' );
			update_option( 'woocommerce_price_num_decimals', 2 );

			// Configure plugin currency settings
			$currency_settings = get_option( 'wcvs_settings_currency', array() );
			$currency_settings['base_currency'] = 'USD';
			$currency_settings['dual_pricing'] = 'yes';
			$currency_settings['price_position'] = 'after';
			$currency_settings['decimal_places'] = 2;
			$currency_settings['thousand_separator'] = '.';
			$currency_settings['decimal_separator'] = ',';
			update_option( 'wcvs_settings_currency', $currency_settings );

			wp_send_json_success( 'Moneda configurada correctamente' );
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error al configurar moneda: ' . $e->getMessage() );
		}
	}

	/**
	 * Configure taxes
	 */
	public function configure_taxes() {
		check_ajax_referer( 'wcvs_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'No tienes permisos para realizar esta acción' );
		}

		try {
			// Enable taxes in WooCommerce
			update_option( 'woocommerce_calc_taxes', 'yes' );
			update_option( 'woocommerce_tax_display_shop', 'incl' );
			update_option( 'woocommerce_tax_display_cart', 'incl' );

			// Configure plugin tax settings
			$tax_settings = get_option( 'wcvs_settings_tax', array() );
			$tax_settings['iva_rate'] = 16;
			$tax_settings['igtf_rate'] = 3;
			$tax_settings['apply_igtf_usd'] = 'yes';
			update_option( 'wcvs_settings_tax', $tax_settings );

			wp_send_json_success( 'Impuestos configurados correctamente' );
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error al configurar impuestos: ' . $e->getMessage() );
		}
	}

	/**
	 * Configure payments
	 */
	public function configure_payments() {
		check_ajax_referer( 'wcvs_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'No tienes permisos para realizar esta acción' );
		}

		try {
			// Enable cash on delivery
			$payment_gateways = get_option( 'woocommerce_payment_gateways', array() );
			if ( ! in_array( 'cod', $payment_gateways ) ) {
				$payment_gateways[] = 'cod';
				update_option( 'woocommerce_payment_gateways', $payment_gateways );
			}

			// Configure COD settings
			update_option( 'woocommerce_cod_settings', array(
				'enabled' => 'yes',
				'title' => 'Pago Contra Entrega',
				'description' => 'Paga cuando recibas tu pedido',
				'instructions' => 'Paga en efectivo cuando recibas tu pedido',
				'enable_for_methods' => array(),
				'enable_for_virtual' => 'no',
				'accept_for_countries' => array( 'VE' )
			));

			wp_send_json_success( 'Métodos de pago configurados correctamente' );
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error al configurar métodos de pago: ' . $e->getMessage() );
		}
	}

	/**
	 * Configure shipping
	 */
	public function configure_shipping() {
		check_ajax_referer( 'wcvs_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'No tienes permisos para realizar esta acción' );
		}

		try {
			// Enable shipping
			update_option( 'woocommerce_ship_to_countries', 'specific' );
			update_option( 'woocommerce_specific_ship_to_countries', array( 'VE' ) );

			// Configure free shipping
			$shipping_zones = WC_Shipping_Zones::get_zones();
			if ( empty( $shipping_zones ) ) {
				$zone = new WC_Shipping_Zone();
				$zone->set_zone_name( 'Venezuela' );
				$zone->set_zone_order( 1 );
				$zone->save();
			}

			wp_send_json_success( 'Métodos de envío configurados correctamente' );
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error al configurar métodos de envío: ' . $e->getMessage() );
		}
	}

	/**
	 * Configure location
	 */
	public function configure_location() {
		check_ajax_referer( 'wcvs_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'No tienes permisos para realizar esta acción' );
		}

		try {
			// Configure store location
			update_option( 'woocommerce_store_address', 'Caracas' );
			update_option( 'woocommerce_store_city', 'Distrito Capital' );
			update_option( 'woocommerce_default_country', 'VE:VE-A' );
			update_option( 'woocommerce_store_postcode', '1010' );

			wp_send_json_success( 'Ubicación configurada correctamente' );
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error al configurar ubicación: ' . $e->getMessage() );
		}
	}

	/**
	 * Configure pages
	 */
	public function configure_pages() {
		check_ajax_referer( 'wcvs_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'No tienes permisos para realizar esta acción' );
		}

		try {
			// Create WooCommerce pages if they don't exist
			$pages = array(
				'shop' => 'Tienda',
				'cart' => 'Carrito',
				'checkout' => 'Finalizar Compra',
				'myaccount' => 'Mi Cuenta'
			);

			foreach ( $pages as $page_key => $page_title ) {
				$page_id = get_option( 'woocommerce_' . $page_key . '_page_id' );
				if ( ! $page_id || ! get_post( $page_id ) ) {
					$page_id = wp_insert_post( array(
						'post_title' => $page_title,
						'post_content' => '',
						'post_status' => 'publish',
						'post_type' => 'page'
					));
					update_option( 'woocommerce_' . $page_key . '_page_id', $page_id );
				}
			}

			wp_send_json_success( 'Páginas creadas correctamente' );
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error al crear páginas: ' . $e->getMessage() );
		}
	}
}
