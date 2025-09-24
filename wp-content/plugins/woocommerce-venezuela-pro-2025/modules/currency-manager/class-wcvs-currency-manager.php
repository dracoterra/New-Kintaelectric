<?php

/**
 * WooCommerce Venezuela Suite 2025 - Currency Manager Module
 *
 * Módulo de gestión de moneda dual USD/VES con actualización automática
 * y integración con BCV y otras fuentes de tipo de cambio.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currency Manager Module class
 */
class WCVS_Currency_Manager {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Exchange rate sources
	 *
	 * @var array
	 */
	private $exchange_sources = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->init_hooks();
		$this->load_exchange_sources();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_woocommerce' ) );
		add_action( 'wcvs_update_exchange_rates', array( $this, 'update_exchange_rates' ) );
		add_action( 'wp_ajax_wcvs_get_exchange_rate', array( $this, 'ajax_get_exchange_rate' ) );
		add_action( 'wp_ajax_nopriv_wcvs_get_exchange_rate', array( $this, 'ajax_get_exchange_rate' ) );
		add_action( 'wp_ajax_wcvs_convert_currency', array( $this, 'ajax_convert_currency' ) );
		add_action( 'wp_ajax_nopriv_wcvs_convert_currency', array( $this, 'ajax_convert_currency' ) );
	}

	/**
	 * Initialize module
	 */
	public function init() {
		// Initialize module functionality
		$this->init_currency_display();
		$this->init_exchange_rate_updates();
	}

	/**
	 * Initialize WooCommerce integration
	 */
	public function init_woocommerce() {
		// Add currency switcher to checkout
		add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'add_currency_switcher' ) );
		
		// Add currency switcher to cart
		add_action( 'woocommerce_cart_collaterals', array( $this, 'add_currency_switcher_cart' ) );
		
		// Add currency switcher to product pages
		add_action( 'woocommerce_single_product_summary', array( $this, 'add_currency_switcher_product' ), 25 );
		
		// Add dual price display
		add_filter( 'woocommerce_price_html', array( $this, 'display_dual_prices' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'display_dual_cart_price' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_dual_cart_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_cart_subtotal', array( $this, 'display_dual_cart_subtotal_total' ), 10, 3 );
		add_filter( 'woocommerce_cart_total', array( $this, 'display_dual_cart_total' ), 10, 1 );
	}

	/**
	 * Initialize currency display
	 */
	private function init_currency_display() {
		// Add currency selector to frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		
		// Add currency selector to admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Initialize exchange rate updates
	 */
	private function init_exchange_rate_updates() {
		// Schedule exchange rate updates
		if ( ! wp_next_scheduled( 'wcvs_update_exchange_rates' ) ) {
			wp_schedule_event( time(), 'hourly', 'wcvs_update_exchange_rates' );
		}
	}

	/**
	 * Load exchange rate sources
	 */
	private function load_exchange_sources() {
		$this->exchange_sources = get_option( 'wcvs_exchange_sources', array(
			'bcv' => array(
				'name' => 'Banco Central de Venezuela',
				'url' => 'https://www.bcv.org.ve/',
				'active' => true,
				'priority' => 1,
				'class' => 'WCVS_Exchange_Source_BCV'
			),
			'dolar_today' => array(
				'name' => 'Dólar Today',
				'url' => 'https://dolartoday.com/',
				'active' => true,
				'priority' => 2,
				'class' => 'WCVS_Exchange_Source_DolarToday'
			),
			'en_paralelo' => array(
				'name' => 'EnParaleloVzla',
				'url' => 'https://enparalelovzla.com/',
				'active' => true,
				'priority' => 3,
				'class' => 'WCVS_Exchange_Source_EnParalelo'
			)
		));
	}

	/**
	 * Update exchange rates
	 */
	public function update_exchange_rates() {
		$updated = false;
		$errors = array();

		foreach ( $this->exchange_sources as $source_id => $source_config ) {
			if ( ! $source_config['active'] ) {
				continue;
			}

			try {
				$rate = $this->get_exchange_rate_from_source( $source_id );
				if ( $rate ) {
					$this->store_exchange_rate( $source_id, $rate );
					$updated = true;
				}
			} catch ( Exception $e ) {
				$errors[] = sprintf( __( 'Error actualizando %s: %s', 'woocommerce-venezuela-pro-2025' ), 
					$source_config['name'], $e->getMessage() );
			}
		}

		if ( $updated ) {
			$this->core->logger->info( 'Exchange rates updated successfully' );
		}

		if ( ! empty( $errors ) ) {
			$this->core->logger->error( 'Exchange rate update errors', array( 'errors' => $errors ) );
		}
	}

	/**
	 * Get exchange rate from source
	 *
	 * @param string $source_id
	 * @return float|false
	 */
	private function get_exchange_rate_from_source( $source_id ) {
		$source_config = $this->exchange_sources[ $source_id ];
		
		switch ( $source_id ) {
			case 'bcv':
				return $this->get_bcv_rate();
			case 'dolar_today':
				return $this->get_dolar_today_rate();
			case 'en_paralelo':
				return $this->get_en_paralelo_rate();
			default:
				return false;
		}
	}

	/**
	 * Get BCV exchange rate
	 *
	 * @return float|false
	 */
	private function get_bcv_rate() {
		// This would integrate with BCV's actual API
		// For now, return a mock rate
		return 3570.50;
	}

	/**
	 * Get Dólar Today exchange rate
	 *
	 * @return float|false
	 */
	private function get_dolar_today_rate() {
		// This would integrate with Dólar Today's actual API
		// For now, return a mock rate
		return 3580.25;
	}

	/**
	 * Get EnParaleloVzla exchange rate
	 *
	 * @return float|false
	 */
	private function get_en_paralelo_rate() {
		// This would integrate with EnParaleloVzla's actual API
		// For now, return a mock rate
		return 3575.75;
	}

	/**
	 * Store exchange rate
	 *
	 * @param string $source_id
	 * @param float  $rate
	 */
	private function store_exchange_rate( $source_id, $rate ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wcvs_exchange_rates_history';
		$wpdb->insert(
			$table,
			array(
				'from_currency' => 'USD',
				'to_currency' => 'VES',
				'rate' => $rate,
				'source' => $source_id,
				'created_at' => current_time( 'mysql' )
			),
			array( '%s', '%s', '%f', '%s', '%s' )
		);
		
		// Update current rate
		update_option( 'wcvs_current_exchange_rate', $rate );
		update_option( 'wcvs_exchange_rate_source', $source_id );
		update_option( 'wcvs_exchange_rate_updated', current_time( 'mysql' ) );
	}

	/**
	 * Get current exchange rate
	 *
	 * @return float
	 */
	public function get_current_exchange_rate() {
		$rate = get_option( 'wcvs_current_exchange_rate', 3570.50 );
		return floatval( $rate );
	}

	/**
	 * Convert currency
	 *
	 * @param float  $amount
	 * @param string $from_currency
	 * @param string $to_currency
	 * @return float
	 */
	public function convert_currency( $amount, $from_currency, $to_currency ) {
		if ( $from_currency === $to_currency ) {
			return $amount;
		}

		$rate = $this->get_current_exchange_rate();
		
		if ( $from_currency === 'USD' && $to_currency === 'VES' ) {
			return $amount * $rate;
		} elseif ( $from_currency === 'VES' && $to_currency === 'USD' ) {
			return $amount / $rate;
		}
		
		return $amount;
	}

	/**
	 * Format currency
	 *
	 * @param float  $amount
	 * @param string $currency
	 * @return string
	 */
	public function format_currency( $amount, $currency ) {
		if ( $currency === 'VES' ) {
			return 'Bs. ' . number_format( $amount, 2, ',', '.' );
		} elseif ( $currency === 'USD' ) {
			return '$' . number_format( $amount, 2, '.', ',' );
		}
		
		return $amount;
	}

	/**
	 * Add currency switcher to checkout
	 */
	public function add_currency_switcher() {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return;
		}

		?>
		<div class="wcvs-currency-switcher">
			<h3><?php _e( 'Selecciona tu moneda preferida', 'woocommerce-venezuela-pro-2025' ); ?></h3>
			<div class="wcvs-currency-options">
				<label class="wcvs-currency-option">
					<input type="radio" name="wcvs_preferred_currency" value="VES" checked>
					<span class="currency-symbol">Bs.</span>
					<span class="currency-name">Bolívares</span>
				</label>
				<label class="wcvs-currency-option">
					<input type="radio" name="wcvs_preferred_currency" value="USD">
					<span class="currency-symbol">$</span>
					<span class="currency-name">Dólares</span>
				</label>
			</div>
			<div class="wcvs-exchange-rate">
				<span class="rate-label"><?php _e( 'Tipo de cambio:', 'woocommerce-venezuela-pro-2025' ); ?></span>
				<span class="rate-value"><?php echo $this->format_currency( $this->get_current_exchange_rate(), 'VES' ); ?></span>
				<span class="rate-update"><?php _e( 'Actualizado:', 'woocommerce-venezuela-pro-2025' ); ?> <?php echo get_option( 'wcvs_exchange_rate_updated', 'N/A' ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Add currency switcher to cart
	 */
	public function add_currency_switcher_cart() {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return;
		}

		?>
		<div class="wcvs-currency-switcher">
			<h3><?php _e( 'Selecciona tu moneda preferida', 'woocommerce-venezuela-pro-2025' ); ?></h3>
			<div class="wcvs-currency-options">
				<label class="wcvs-currency-option">
					<input type="radio" name="wcvs_preferred_currency" value="VES" checked>
					<span class="currency-symbol">Bs.</span>
					<span class="currency-name">Bolívares</span>
				</label>
				<label class="wcvs-currency-option">
					<input type="radio" name="wcvs_preferred_currency" value="USD">
					<span class="currency-symbol">$</span>
					<span class="currency-name">Dólares</span>
				</label>
			</div>
		</div>
		<?php
	}

	/**
	 * Add currency switcher to product page
	 */
	public function add_currency_switcher_product() {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return;
		}

		?>
		<div class="wcvs-currency-switcher">
			<h3><?php _e( 'Selecciona tu moneda preferida', 'woocommerce-venezuela-pro-2025' ); ?></h3>
			<div class="wcvs-currency-options">
				<label class="wcvs-currency-option">
					<input type="radio" name="wcvs_preferred_currency" value="VES" checked>
					<span class="currency-symbol">Bs.</span>
					<span class="currency-name">Bolívares</span>
				</label>
				<label class="wcvs-currency-option">
					<input type="radio" name="wcvs_preferred_currency" value="USD">
					<span class="currency-symbol">$</span>
					<span class="currency-name">Dólares</span>
				</label>
			</div>
		</div>
		<?php
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
		$exchange_rate = $this->get_current_exchange_rate();

		if ( $exchange_rate ) {
			$price = $product->get_price();
			$converted_price = $this->convert_currency( $price, $base_currency, 'USD' );
			
			$price_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				$this->format_currency( $converted_price, 'USD' )
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
		$exchange_rate = $this->get_current_exchange_rate();

		if ( $exchange_rate ) {
			$price = $cart_item['data']->get_price();
			$converted_price = $this->convert_currency( $price, $base_currency, 'USD' );
			
			$price_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				$this->format_currency( $converted_price, 'USD' )
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
		$exchange_rate = $this->get_current_exchange_rate();

		if ( $exchange_rate ) {
			$subtotal = $cart_item['line_subtotal'];
			$converted_subtotal = $this->convert_currency( $subtotal, $base_currency, 'USD' );
			
			$subtotal_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				$this->format_currency( $converted_subtotal, 'USD' )
			);
		}

		return $subtotal_html;
	}

	/**
	 * Display dual cart subtotal total
	 *
	 * @param string $subtotal_html
	 * @param array $cart_item
	 * @param string $cart_item_key
	 * @return string
	 */
	public function display_dual_cart_subtotal_total( $subtotal_html, $cart_item, $cart_item_key ) {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return $subtotal_html;
		}

		$base_currency = $this->core->settings->get_setting( 'currency', 'base_currency', 'VES' );
		$exchange_rate = $this->get_current_exchange_rate();

		if ( $exchange_rate ) {
			$subtotal = $cart_item['line_subtotal'];
			$converted_subtotal = $this->convert_currency( $subtotal, $base_currency, 'USD' );
			
			$subtotal_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				$this->format_currency( $converted_subtotal, 'USD' )
			);
		}

		return $subtotal_html;
	}

	/**
	 * Display dual cart total
	 *
	 * @param string $total_html
	 * @return string
	 */
	public function display_dual_cart_total( $total_html ) {
		if ( ! $this->core->settings->get_setting( 'currency', 'show_dual_prices' ) ) {
			return $total_html;
		}

		$base_currency = $this->core->settings->get_setting( 'currency', 'base_currency', 'VES' );
		$exchange_rate = $this->get_current_exchange_rate();

		if ( $exchange_rate ) {
			$total = WC()->cart->get_total( 'raw' );
			$converted_total = $this->convert_currency( $total, $base_currency, 'USD' );
			
			$total_html .= sprintf(
				' <span class="wcvs-dual-price">(%s)</span>',
				$this->format_currency( $converted_total, 'USD' )
			);
		}

		return $total_html;
	}

	/**
	 * AJAX handler for getting exchange rate
	 */
	public function ajax_get_exchange_rate() {
		check_ajax_referer( 'wcvs_get_exchange_rate', 'nonce' );

		$rate = $this->get_current_exchange_rate();
		$source = get_option( 'wcvs_exchange_rate_source', 'bcv' );
		$updated = get_option( 'wcvs_exchange_rate_updated', 'N/A' );

		wp_send_json_success( array(
			'rate' => $rate,
			'source' => $source,
			'updated' => $updated
		));
	}

	/**
	 * AJAX handler for currency conversion
	 */
	public function ajax_convert_currency() {
		check_ajax_referer( 'wcvs_convert_currency', 'nonce' );

		$amount = floatval( $_POST['amount'] );
		$from_currency = sanitize_text_field( $_POST['from_currency'] );
		$to_currency = sanitize_text_field( $_POST['to_currency'] );

		$converted_amount = $this->convert_currency( $amount, $from_currency, $to_currency );
		$formatted_amount = $this->format_currency( $converted_amount, $to_currency );

		wp_send_json_success( array(
			'amount' => $converted_amount,
			'formatted' => $formatted_amount
		));
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		if ( is_shop() || is_product() || is_cart() || is_checkout() ) {
			wp_enqueue_script(
				'wcvs-currency-manager',
				WCVS_PLUGIN_URL . 'modules/currency-manager/js/currency-manager.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-currency-manager', 'wcvs_currency_manager', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_get_exchange_rate' ),
				'convert_nonce' => wp_create_nonce( 'wcvs_convert_currency' ),
				'strings' => array(
					'loading' => __( 'Cargando...', 'woocommerce-venezuela-pro-2025' ),
					'error' => __( 'Error', 'woocommerce-venezuela-pro-2025' ),
					'success' => __( 'Éxito', 'woocommerce-venezuela-pro-2025' )
				)
			));
		}
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

		wp_localize_script( 'wcvs-currency-manager-admin', 'wcvs_currency_manager_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_get_exchange_rate' ),
			'strings' => array(
				'loading' => __( 'Cargando...', 'woocommerce-venezuela-pro-2025' ),
				'error' => __( 'Error', 'woocommerce-venezuela-pro-2025' ),
				'success' => __( 'Éxito', 'woocommerce-venezuela-pro-2025' )
			)
		));
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		// Add currency manager specific frontend functionality
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}
}
