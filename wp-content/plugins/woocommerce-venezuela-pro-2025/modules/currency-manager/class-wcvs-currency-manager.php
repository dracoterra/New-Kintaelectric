<?php

/**
 * WooCommerce Venezuela Suite 2025 - Currency Manager Module
 *
 * Módulo de gestión de moneda con integración BCV
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currency Manager Module
 */
class WCVS_Currency_Manager {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$all_settings = $this->core->settings->get_all_settings();
		$this->settings = $all_settings['currency'];
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Frontend hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'woocommerce_single_product_summary', array( $this, 'display_dual_pricing' ), 25 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'display_dual_pricing_loop' ), 15 );
		add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'display_cart_dual_totals' ) );
		add_action( 'woocommerce_review_order_before_order_total', array( $this, 'display_checkout_dual_totals' ) );

		// Admin hooks
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'woocommerce_product_options_pricing', array( $this, 'add_product_currency_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_currency_fields' ) );

		// AJAX hooks
		add_action( 'wp_ajax_wcvs_update_currency_display', array( $this, 'ajax_update_currency_display' ) );
		add_action( 'wp_ajax_nopriv_wcvs_update_currency_display', array( $this, 'ajax_update_currency_display' ) );

		// Currency conversion hooks
		add_filter( 'woocommerce_currency_symbol', array( $this, 'custom_currency_symbol' ), 10, 2 );
		add_filter( 'woocommerce_price_format', array( $this, 'custom_price_format' ) );
		add_filter( 'woocommerce_price_html', array( $this, 'add_dual_pricing' ), 10, 2 );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'wcvs-currency-manager',
			WCVS_PLUGIN_URL . 'modules/currency-manager/js/currency-manager.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);

		wp_enqueue_style(
			'wcvs-currency-manager',
			WCVS_PLUGIN_URL . 'modules/currency-manager/css/currency-manager.css',
			array(),
			WCVS_VERSION
		);

		wp_localize_script( 'wcvs-currency-manager', 'wcvs_currency_manager', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_currency_manager_nonce' ),
			'current_rate' => $this->core->bcv_integration->get_current_rate(),
			'base_currency' => isset( $this->settings['base_currency'] ) ? $this->settings['base_currency'] : 'VES',
			'dual_pricing' => isset( $this->settings['dual_pricing'] ) ? $this->settings['dual_pricing'] : false,
			'price_position' => isset( $this->settings['price_position'] ) ? $this->settings['price_position'] : 'before',
			'decimal_places' => isset( $this->settings['decimal_places'] ) ? $this->settings['decimal_places'] : 2,
			'thousand_separator' => isset( $this->settings['thousand_separator'] ) ? $this->settings['thousand_separator'] : '.',
			'decimal_separator' => isset( $this->settings['decimal_separator'] ) ? $this->settings['decimal_separator'] : ','
		));
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'wcvs-currency-manager-admin',
			WCVS_PLUGIN_URL . 'modules/currency-manager/js/currency-manager-admin.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);
	}

	/**
	 * Display dual pricing on single product
	 */
	public function display_dual_pricing() {
		if ( ! $this->settings['dual_pricing'] ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}

		$price_html = $this->get_dual_pricing_html( $product );
		if ( $price_html ) {
			echo '<div class="wcvs-dual-pricing">' . $price_html . '</div>';
		}
	}

	/**
	 * Display dual pricing on product loop
	 */
	public function display_dual_pricing_loop() {
		if ( ! $this->settings['dual_pricing'] ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}

		$price_html = $this->get_dual_pricing_html( $product );
		if ( $price_html ) {
			echo '<div class="wcvs-dual-pricing-loop">' . $price_html . '</div>';
		}
	}

	/**
	 * Display cart dual totals
	 */
	public function display_cart_dual_totals() {
		if ( ! $this->settings['dual_pricing'] ) {
			return;
		}

		$cart_total = WC()->cart->get_total( 'raw' );
		$base_currency = get_woocommerce_currency();
		
		echo '<div class="wcvs-cart-dual-totals">';
		echo '<h3>' . __( 'Total en ambas monedas', 'woocommerce-venezuela-pro-2025' ) . '</h3>';
		echo $this->get_total_dual_html( $cart_total, $base_currency );
		echo '</div>';
	}

	/**
	 * Display checkout dual totals
	 */
	public function display_checkout_dual_totals() {
		if ( ! $this->settings['dual_pricing'] ) {
			return;
		}

		$checkout_total = WC()->cart->get_total( 'raw' );
		$base_currency = get_woocommerce_currency();
		
		echo '<div class="wcvs-checkout-dual-totals">';
		echo '<h3>' . __( 'Total en ambas monedas', 'woocommerce-venezuela-pro-2025' ) . '</h3>';
		echo $this->get_total_dual_html( $checkout_total, $base_currency );
		echo '</div>';
	}

	/**
	 * Get dual pricing HTML
	 *
	 * @param WC_Product $product
	 * @return string
	 */
	private function get_dual_pricing_html( $product ) {
		$price = $product->get_price();
		if ( ! $price ) {
			return '';
		}

		$base_currency = get_woocommerce_currency();
		$converted_price = $this->convert_price( $price, $base_currency );
		
		if ( ! $converted_price ) {
			return '';
		}

		$html = '<div class="wcvs-price-display">';
		
		if ( $base_currency === 'USD' ) {
			$html .= '<span class="wcvs-price-usd">' . $this->format_price( $price, 'USD' ) . '</span>';
			$html .= '<span class="wcvs-price-ves">' . $this->format_price( $converted_price, 'VES' ) . '</span>';
		} else {
			$html .= '<span class="wcvs-price-ves">' . $this->format_price( $price, 'VES' ) . '</span>';
			$html .= '<span class="wcvs-price-usd">' . $this->format_price( $converted_price, 'USD' ) . '</span>';
		}
		
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Get total dual HTML
	 *
	 * @param float $total
	 * @param string $base_currency
	 * @return string
	 */
	private function get_total_dual_html( $total, $base_currency ) {
		$converted_total = $this->convert_price( $total, $base_currency );
		
		if ( ! $converted_total ) {
			return '';
		}

		$html = '<div class="wcvs-total-display">';
		
		if ( $base_currency === 'USD' ) {
			$html .= '<div class="wcvs-total-usd">' . $this->format_price( $total, 'USD' ) . '</div>';
			$html .= '<div class="wcvs-total-ves">' . $this->format_price( $converted_total, 'VES' ) . '</div>';
		} else {
			$html .= '<div class="wcvs-total-ves">' . $this->format_price( $total, 'VES' ) . '</div>';
			$html .= '<div class="wcvs-total-usd">' . $this->format_price( $converted_total, 'USD' ) . '</div>';
		}
		
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Convert price between currencies with caching
	 *
	 * @param float $price
	 * @param string $from_currency
	 * @return float|false
	 */
	private function convert_price( $price, $from_currency ) {
		if ( ! $price || $price <= 0 ) {
			return false;
		}

		// Create cache key
		$cache_key = 'wcvs_conversion_' . md5( $price . '_' . $from_currency . '_' . $this->get_current_rate_hash() );
		
		// Try to get from cache first
		$cached_result = get_transient( $cache_key );
		if ( $cached_result !== false ) {
			return $cached_result;
		}

		// Perform conversion
		$converted_price = false;
		if ( $from_currency === 'USD' ) {
			$converted_price = $this->core->bcv_integration->convert_usd_to_ves( $price );
		} elseif ( $from_currency === 'VES' ) {
			$converted_price = $this->core->bcv_integration->convert_ves_to_usd( $price );
		}

		// Cache the result for 30 minutes
		if ( $converted_price !== false ) {
			set_transient( $cache_key, $converted_price, 30 * MINUTE_IN_SECONDS );
		}

		return $converted_price;
	}

	/**
	 * Get current rate hash for cache invalidation
	 *
	 * @return string
	 */
	private function get_current_rate_hash() {
		$rate = $this->core->bcv_integration->get_current_rate();
		return md5( $rate . '_' . date( 'Y-m-d-H' ) ); // Changes every hour
	}

	/**
	 * Format price
	 *
	 * @param float $price
	 * @param string $currency
	 * @return string
	 */
	private function format_price( $price, $currency ) {
		return $this->core->bcv_integration->format_currency( $price, $currency );
	}

	/**
	 * Add product currency fields
	 */
	public function add_product_currency_fields() {
		global $post;
		$product = wc_get_product( $post->ID );
		
		if ( ! $product ) {
			return;
		}

		echo '<div class="options_group">';
		
		woocommerce_wp_text_input( array(
			'id' => '_wcvs_usd_price',
			'label' => __( 'Precio en USD', 'woocommerce-venezuela-pro-2025' ),
			'placeholder' => '0.00',
			'desc_tip' => true,
			'description' => __( 'Precio en dólares estadounidenses', 'woocommerce-venezuela-pro-2025' ),
			'type' => 'number',
			'custom_attributes' => array(
				'step' => '0.01',
				'min' => '0'
			)
		));

		woocommerce_wp_text_input( array(
			'id' => '_wcvs_ves_price',
			'label' => __( 'Precio en VES', 'woocommerce-venezuela-pro-2025' ),
			'placeholder' => '0.00',
			'desc_tip' => true,
			'description' => __( 'Precio en bolívares venezolanos', 'woocommerce-venezuela-pro-2025' ),
			'type' => 'number',
			'custom_attributes' => array(
				'step' => '0.01',
				'min' => '0'
			)
		));

		woocommerce_wp_checkbox( array(
			'id' => '_wcvs_auto_convert',
			'label' => __( 'Conversión Automática', 'woocommerce-venezuela-pro-2025' ),
			'description' => __( 'Convertir automáticamente el precio según la tasa de cambio actual', 'woocommerce-venezuela-pro-2025' )
		));

		echo '</div>';
	}

	/**
	 * Save product currency fields
	 *
	 * @param int $post_id
	 */
	public function save_product_currency_fields( $post_id ) {
		$product = wc_get_product( $post_id );
		
		if ( ! $product ) {
			return;
		}

		// Save USD price
		if ( isset( $_POST['_wcvs_usd_price'] ) ) {
			$usd_price = sanitize_text_field( $_POST['_wcvs_usd_price'] );
			$product->update_meta_data( '_wcvs_usd_price', $usd_price );
		}

		// Save VES price
		if ( isset( $_POST['_wcvs_ves_price'] ) ) {
			$ves_price = sanitize_text_field( $_POST['_wcvs_ves_price'] );
			$product->update_meta_data( '_wcvs_ves_price', $ves_price );
		}

		// Save auto convert setting
		$auto_convert = isset( $_POST['_wcvs_auto_convert'] ) ? 'yes' : 'no';
		$product->update_meta_data( '_wcvs_auto_convert', $auto_convert );

		$product->save();
	}

	/**
	 * Custom currency symbol
	 *
	 * @param string $currency_symbol
	 * @param string $currency
	 * @return string
	 */
	public function custom_currency_symbol( $currency_symbol, $currency ) {
		if ( $currency === 'VES' ) {
			return 'Bs.';
		}
		
		return $currency_symbol;
	}

	/**
	 * Custom price format
	 *
	 * @param string $format
	 * @return string
	 */
	public function custom_price_format( $format ) {
		$currency = get_woocommerce_currency();
		
		if ( $currency === 'VES' ) {
			return '%1$s%2$s';
		}
		
		return $format;
	}

	/**
	 * Add dual pricing to price HTML
	 *
	 * @param string $price_html
	 * @param WC_Product $product
	 * @return string
	 */
	public function add_dual_pricing( $price_html, $product ) {
		if ( ! $this->settings['dual_pricing'] ) {
			return $price_html;
		}

		$dual_html = $this->get_dual_pricing_html( $product );
		if ( $dual_html ) {
			$price_html .= '<div class="wcvs-dual-pricing-additional">' . $dual_html . '</div>';
		}

		return $price_html;
	}

	/**
	 * AJAX update currency display
	 */
	public function ajax_update_currency_display() {
		check_ajax_referer( 'wcvs_currency_manager_nonce', 'nonce' );

		$currency = sanitize_text_field( $_POST['currency'] );
		$rate = $this->core->bcv_integration->get_current_rate();

		wp_send_json_success( array(
			'currency' => $currency,
			'rate' => $rate,
			'timestamp' => current_time( 'mysql' )
		));
	}

	/**
	 * Get currency selector HTML
	 *
	 * @return string
	 */
	public function get_currency_selector_html() {
		$current_currency = get_woocommerce_currency();
		
		$html = '<div class="wcvs-currency-selector">';
		$html .= '<label for="wcvs-currency-select">' . __( 'Moneda:', 'woocommerce-venezuela-pro-2025' ) . '</label>';
		$html .= '<select id="wcvs-currency-select" name="wcvs-currency">';
		$html .= '<option value="VES" ' . selected( $current_currency, 'VES', false ) . '>VES - Bolívar</option>';
		$html .= '<option value="USD" ' . selected( $current_currency, 'USD', false ) . '>USD - Dólar</option>';
		$html .= '<option value="dual" ' . selected( $current_currency, 'dual', false ) . '>' . __( 'Ambas', 'woocommerce-venezuela-pro-2025' ) . '</option>';
		$html .= '</select>';
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Display currency selector
	 */
	public function display_currency_selector() {
		echo $this->get_currency_selector_html();
	}

	/**
	 * Get current rate display with validation
	 *
	 * @return string
	 */
	public function get_current_rate_display() {
		$rate = $this->core->bcv_integration->get_current_rate();
		$rate_status = $this->validate_rate( $rate );
		
		$html = '<div class="wcvs-current-rate">';
		$html .= '<span class="wcvs-rate-label">' . __( 'Tasa actual:', 'woocommerce-venezuela-pro-2025' ) . '</span>';
		
		if ( $rate_status['valid'] ) {
			$html .= '<span class="wcvs-rate-value valid">' . $this->format_price( $rate, 'VES' ) . '/USD</span>';
			$html .= '<span class="wcvs-rate-status">' . $rate_status['message'] . '</span>';
		} else {
			$html .= '<span class="wcvs-rate-value invalid">' . __( 'Tasa no disponible', 'woocommerce-venezuela-pro-2025' ) . '</span>';
			$html .= '<span class="wcvs-rate-status error">' . $rate_status['message'] . '</span>';
		}
		
		$html .= '<button class="wcvs-update-rate" type="button">' . __( 'Actualizar', 'woocommerce-venezuela-pro-2025' ) . '</button>';
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Validate exchange rate
	 *
	 * @param float $rate
	 * @return array
	 */
	private function validate_rate( $rate ) {
		if ( ! $rate || $rate <= 0 ) {
			return array(
				'valid' => false,
				'message' => __( 'Tasa de cambio no disponible', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// Check if rate is within reasonable bounds (VES/USD typically 1-1000)
		if ( $rate < 1 || $rate > 10000 ) {
			return array(
				'valid' => false,
				'message' => __( 'Tasa de cambio fuera de rango normal', 'woocommerce-venezuela-pro-2025' )
			);
		}

		// Check if rate has changed significantly from last known rate
		$last_rate = get_option( 'wcvs_last_valid_rate', 0 );
		if ( $last_rate > 0 ) {
			$change_percentage = abs( $rate - $last_rate ) / $last_rate;
			if ( $change_percentage > 0.5 ) { // More than 50% change
				return array(
					'valid' => true,
					'message' => __( 'Tasa con cambio significativo detectado', 'woocommerce-venezuela-pro-2025' )
				);
			}
		}

		// Update last valid rate
		update_option( 'wcvs_last_valid_rate', $rate );

		return array(
			'valid' => true,
			'message' => __( 'Tasa válida y actualizada', 'woocommerce-venezuela-pro-2025' )
		);
	}

	/**
	 * Display current rate
	 */
	public function display_current_rate() {
		echo $this->get_current_rate_display();
	}
}