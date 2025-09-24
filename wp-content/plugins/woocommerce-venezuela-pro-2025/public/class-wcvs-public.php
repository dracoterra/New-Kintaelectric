<?php

/**
 * WooCommerce Venezuela Suite 2025 - Public
 *
 * Maneja la funcionalidad pública del plugin
 * incluyendo frontend y checkout.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public class
 */
class WCVS_Public {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_woocommerce_integration' ) );
	}

	/**
	 * Initialize public functionality
	 */
	public function init() {
		// Initialize public functionality
		$this->init_frontend_features();
	}

	/**
	 * Enqueue public scripts and styles
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'wcvs-public',
			WCVS_PLUGIN_URL . 'public/css/wcvs-public.css',
			array(),
			WCVS_VERSION
		);

		wp_enqueue_script(
			'wcvs-public',
			WCVS_PLUGIN_URL . 'public/js/wcvs-public.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);

		wp_localize_script( 'wcvs-public', 'wcvs_public', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_public' ),
			'strings' => array(
				'loading' => __( 'Cargando...', 'woocommerce-venezuela-pro-2025' ),
				'error' => __( 'Error', 'woocommerce-venezuela-pro-2025' ),
				'success' => __( 'Éxito', 'woocommerce-venezuela-pro-2025' )
			)
		));
	}

	/**
	 * Initialize WooCommerce integration
	 */
	public function init_woocommerce_integration() {
		// Initialize WooCommerce specific functionality
		$this->init_currency_display();
		$this->init_checkout_fields();
		$this->init_payment_gateways();
		$this->init_shipping_methods();
	}

	/**
	 * Initialize frontend features
	 */
	private function init_frontend_features() {
		// Initialize frontend features based on active modules
		$active_modules = $this->core->module_manager->get_active_modules();
		
		foreach ( $active_modules as $module_id => $is_active ) {
			if ( $is_active ) {
				$module_instance = $this->core->module_manager->get_module_instance( $module_id );
				if ( $module_instance && method_exists( $module_instance, 'init_frontend' ) ) {
					$module_instance->init_frontend();
				}
			}
		}
	}

	/**
	 * Initialize currency display
	 */
	private function init_currency_display() {
		if ( ! $this->core->module_manager->is_module_active( 'currency_manager' ) ) {
			return;
		}

		// Add dual price display
		add_filter( 'woocommerce_price_html', array( $this, 'display_dual_prices' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'display_dual_cart_price' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_dual_cart_subtotal' ), 10, 3 );
	}

	/**
	 * Initialize checkout fields
	 */
	private function init_checkout_fields() {
		if ( ! $this->core->module_manager->is_module_active( 'checkout_fields' ) ) {
			return;
		}

		// Add Venezuelan checkout fields
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_venezuelan_fields' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_venezuelan_fields' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_venezuelan_fields_admin' ) );
	}

	/**
	 * Initialize payment gateways
	 */
	private function init_payment_gateways() {
		if ( ! $this->core->module_manager->is_module_active( 'payment_gateways' ) ) {
			return;
		}

		// Payment gateways are loaded by their respective modules
	}

	/**
	 * Initialize shipping methods
	 */
	private function init_shipping_methods() {
		if ( ! $this->core->module_manager->is_module_active( 'shipping_methods' ) ) {
			return;
		}

		// Shipping methods are loaded by their respective modules
	}

	/**
	 * Display dual prices
	 *
	 * @param string $price_html
	 * @param WC_Product $product
	 * @return string
	 */
	public function display_dual_prices( $price_html, $product ) {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return $price_html;
		}

		$base_currency = $this->core->settings->get_setting( 'currency', 'base_currency', 'VES' );
		$exchange_rate = $this->get_exchange_rate( $base_currency, 'USD' );

		if ( $exchange_rate ) {
			$price = $product->get_price();
			$converted_price = $price / $exchange_rate;
			
			$price_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				wc_price( $converted_price, array( 'currency' => 'USD' ) )
			);
		}

		return $price_html;
	}

	/**
	 * Display dual cart price
	 *
	 * @param string $price_html
	 * @param array $cart_item
	 * @param string $cart_item_key
	 * @return string
	 */
	public function display_dual_cart_price( $price_html, $cart_item, $cart_item_key ) {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return $price_html;
		}

		$base_currency = $this->core->settings->get_setting( 'currency', 'base_currency', 'VES' );
		$exchange_rate = $this->get_exchange_rate( $base_currency, 'USD' );

		if ( $exchange_rate ) {
			$price = $cart_item['data']->get_price();
			$converted_price = $price / $exchange_rate;
			
			$price_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				wc_price( $converted_price, array( 'currency' => 'USD' ) )
			);
		}

		return $price_html;
	}

	/**
	 * Display dual cart subtotal
	 *
	 * @param string $subtotal_html
	 * @param array $cart_item
	 * @param string $cart_item_key
	 * @return string
	 */
	public function display_dual_cart_subtotal( $subtotal_html, $cart_item, $cart_item_key ) {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return $subtotal_html;
		}

		$base_currency = $this->core->settings->get_setting( 'currency', 'base_currency', 'VES' );
		$exchange_rate = $this->get_exchange_rate( $base_currency, 'USD' );

		if ( $exchange_rate ) {
			$subtotal = $cart_item['line_subtotal'];
			$converted_subtotal = $subtotal / $exchange_rate;
			
			$subtotal_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				wc_price( $converted_subtotal, array( 'currency' => 'USD' ) )
			);
		}

		return $subtotal_html;
	}

	/**
	 * Validate Venezuelan fields
	 */
	public function validate_venezuelan_fields() {
		// Validate Venezuelan ID (Cédula)
		if ( ! empty( $_POST['wcvs_venezuelan_id'] ) ) {
			$venezuelan_id = sanitize_text_field( $_POST['wcvs_venezuelan_id'] );
			if ( ! $this->validate_venezuelan_id( $venezuelan_id ) ) {
				wc_add_notice( __( 'La cédula de identidad no es válida.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			}
		}

		// Validate RIF (for companies)
		if ( ! empty( $_POST['wcvs_rif'] ) ) {
			$rif = sanitize_text_field( $_POST['wcvs_rif'] );
			if ( ! $this->validate_rif( $rif ) ) {
				wc_add_notice( __( 'El RIF no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			}
		}

		// Validate Venezuelan phone
		if ( ! empty( $_POST['wcvs_venezuelan_phone'] ) ) {
			$phone = sanitize_text_field( $_POST['wcvs_venezuelan_phone'] );
			if ( ! $this->validate_venezuelan_phone( $phone ) ) {
				wc_add_notice( __( 'El número de teléfono venezolano no es válido.', 'woocommerce-venezuela-pro-2025' ), 'error' );
			}
		}
	}

	/**
	 * Save Venezuelan fields
	 *
	 * @param int $order_id
	 */
	public function save_venezuelan_fields( $order_id ) {
		if ( ! empty( $_POST['wcvs_venezuelan_id'] ) ) {
			update_post_meta( $order_id, '_wcvs_venezuelan_id', sanitize_text_field( $_POST['wcvs_venezuelan_id'] ) );
		}

		if ( ! empty( $_POST['wcvs_rif'] ) ) {
			update_post_meta( $order_id, '_wcvs_rif', sanitize_text_field( $_POST['wcvs_rif'] ) );
		}

		if ( ! empty( $_POST['wcvs_venezuelan_phone'] ) ) {
			update_post_meta( $order_id, '_wcvs_venezuelan_phone', sanitize_text_field( $_POST['wcvs_venezuelan_phone'] ) );
		}
	}

	/**
	 * Display Venezuelan fields in admin
	 *
	 * @param WC_Order $order
	 */
	public function display_venezuelan_fields_admin( $order ) {
		$venezuelan_id = get_post_meta( $order->get_id(), '_wcvs_venezuelan_id', true );
		$rif = get_post_meta( $order->get_id(), '_wcvs_rif', true );
		$venezuelan_phone = get_post_meta( $order->get_id(), '_wcvs_venezuelan_phone', true );

		if ( $venezuelan_id || $rif || $venezuelan_phone ) {
			echo '<h3>' . __( 'Información Venezolana', 'woocommerce-venezuela-pro-2025' ) . '</h3>';
			echo '<p>';
			
			if ( $venezuelan_id ) {
				echo '<strong>' . __( 'Cédula:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $venezuelan_id ) . '<br/>';
			}
			
			if ( $rif ) {
				echo '<strong>' . __( 'RIF:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $rif ) . '<br/>';
			}
			
			if ( $venezuelan_phone ) {
				echo '<strong>' . __( 'Teléfono:', 'woocommerce-venezuela-pro-2025' ) . '</strong> ' . esc_html( $venezuelan_phone ) . '<br/>';
			}
			
			echo '</p>';
		}
	}

	/**
	 * Get exchange rate
	 *
	 * @param string $from_currency
	 * @param string $to_currency
	 * @return float|false
	 */
	private function get_exchange_rate( $from_currency, $to_currency ) {
		// This would integrate with the currency manager module
		// For now, return a mock rate
		if ( $from_currency === 'VES' && $to_currency === 'USD' ) {
			return 3570; // Mock rate
		}
		return false;
	}

	/**
	 * Validate Venezuelan ID
	 *
	 * @param string $id
	 * @return bool
	 */
	private function validate_venezuelan_id( $id ) {
		// Venezuelan ID validation (V-12345678-9 format)
		return preg_match( '/^[VvEeJjGgPp][-]?\d{7,8}[-]?\d$/', $id );
	}

	/**
	 * Validate RIF
	 *
	 * @param string $rif
	 * @return bool
	 */
	private function validate_rif( $rif ) {
		// RIF validation (J-12345678-9 format)
		return preg_match( '/^[JjGgPpCcVvEe][-]?\d{8}[-]?\d$/', $rif );
	}

	/**
	 * Validate Venezuelan phone
	 *
	 * @param string $phone
	 * @return bool
	 */
	private function validate_venezuelan_phone( $phone ) {
		// Venezuelan phone validation (+58-XXX-XXXXXXX format)
		return preg_match( '/^(\+58|58)?[-]?\d{3}[-]?\d{7}$/', $phone );
	}
}
