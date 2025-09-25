<?php

/**
 * Improved Currency Converter Module
 *
 * Based on analysis of the existing plugin, this module provides
 * a more robust and efficient currency conversion system.
 *
 * @link       https://kintaelectric.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes/modules
 */

/**
 * Improved currency converter module class.
 *
 * This class handles currency conversion from USD to VES using BCV rates
 * with improved performance and reliability based on the existing plugin analysis.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes/modules
 * @author     Kinta Electric <info@kintaelectric.com>
 */
class WVP_Currency_Converter_Improved {

	/**
	 * The dependency injection container.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WVP_Dependency_Container    $container    The dependency container.
	 */
	protected $container;

	/**
	 * BCV integrator instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WVP_BCV_Integrator    $bcv_integrator    BCV integrator.
	 */
	protected $bcv_integrator;

	/**
	 * Cache for conversion rates.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $rate_cache    Cache for rates.
	 */
	protected $rate_cache = array();

	/**
	 * Cache duration in seconds.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      int    $cache_duration    Cache duration.
	 */
	protected $cache_duration = 3600; // 1 hour

	/**
	 * Whether the module has been initialized.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      boolean    $initialized    Whether the module has been initialized.
	 */
	protected $initialized = false;

	/**
	 * Initialize the improved currency converter.
	 *
	 * @since    1.0.0
	 * @param    WVP_Dependency_Container    $container    The dependency container.
	 */
	public function __construct( $container ) {
		$this->container = $container;
		$this->init_bcv_integrator();
	}

	/**
	 * Initialize BCV integrator.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_bcv_integrator() {
		// Load BCV integrator from existing plugin
		$bcv_file = plugin_dir_path( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'woocommerce-venezuela-pro/includes/class-wvp-bcv-integrator.php';
		if ( file_exists( $bcv_file ) ) {
			require_once $bcv_file;
			$this->bcv_integrator = new WVP_BCV_Integrator();
		}
	}

	/**
	 * Initialize the module.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		// Prevent multiple initializations
		if ( $this->initialized ) {
			return;
		}
		
		// Only initialize if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->initialized = true;

		// Register hooks for currency conversion
		add_filter( 'woocommerce_product_get_price', array( $this, 'convert_product_price' ), 10, 2 );
		add_filter( 'woocommerce_product_get_regular_price', array( $this, 'convert_product_price' ), 10, 2 );
		add_filter( 'woocommerce_product_get_sale_price', array( $this, 'convert_product_price' ), 10, 2 );
		
		// Add currency switcher to product pages
		add_action( 'woocommerce_single_product_summary', array( $this, 'add_currency_switcher' ), 25 );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		
		// Add scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Get BCV rate with caching.
	 *
	 * @since    1.0.0
	 * @return   float    The current BCV rate.
	 */
	public function get_bcv_rate() {
		$cache_key = 'wvp_bcv_rate';
		$cached_rate = get_transient( $cache_key );
		
		if ( $cached_rate !== false ) {
			return $cached_rate;
		}

		$rate = 0;

		// Try to get rate from BCV integrator
		if ( $this->bcv_integrator ) {
			$rate = $this->bcv_integrator->get_rate();
		}

		// Fallback to BCV Dólar Tracker plugin
		if ( ! $rate && class_exists( 'BCV_Dolar_Tracker' ) ) {
			$rate = BCV_Dolar_Tracker::get_rate();
		}

		// Final fallback to emergency rate
		if ( ! $rate ) {
			$rate = get_option( 'wvp_emergency_bcv_rate', 50 );
		}

		// Cache the rate
		set_transient( $cache_key, $rate, $this->cache_duration );

		return $rate;
	}

	/**
	 * Convert USD to VES.
	 *
	 * @since    1.0.0
	 * @param    float    $usd_amount    Amount in USD.
	 * @return   float                   Amount in VES.
	 */
	public function convert_usd_to_ves( $usd_amount ) {
		if ( empty( $usd_amount ) || ! is_numeric( $usd_amount ) ) {
			return $usd_amount;
		}

		$rate = $this->get_bcv_rate();
		return $usd_amount * $rate;
	}

	/**
	 * Convert product price for display.
	 *
	 * @since    1.0.0
	 * @param    mixed    $price       The product price.
	 * @param    WC_Product $product   The product object.
	 * @return   mixed                 The converted price.
	 */
	public function convert_product_price( $price, $product ) {
		if ( empty( $price ) || ! is_numeric( $price ) ) {
			return $price;
		}

		// Only convert if product is priced in USD
		if ( $this->is_product_priced_in_usd( $product ) ) {
			return $this->convert_usd_to_ves( $price );
		}

		return $price;
	}

	/**
	 * Check if product is priced in USD.
	 *
	 * @since    1.0.0
	 * @param    WC_Product $product   The product object.
	 * @return   boolean               True if priced in USD.
	 */
	private function is_product_priced_in_usd( $product ) {
		// Check if product has USD pricing meta
		$currency = $product->get_meta( '_currency' );
		if ( $currency === 'USD' ) {
			return true;
		}

		// Check if product price is in USD range (typical USD prices)
		$price = $product->get_price();
		if ( $price > 0 && $price < 1000 ) {
			return true;
		}

		return false;
	}

	/**
	 * Add currency switcher to product pages.
	 *
	 * @since    1.0.0
	 */
	public function add_currency_switcher() {
		// Prevent multiple renderings
		static $rendered = false;
		if ( $rendered ) {
			return;
		}
		$rendered = true;

		$current_currency = $this->get_current_display_currency();
		$bcv_rate = $this->get_bcv_rate();

		?>
		<div class="wvp-currency-switcher">
			<label for="wvp-currency-select">Ver precios en:</label>
			<select id="wvp-currency-select" name="wvp-currency">
				<option value="USD" <?php selected( $current_currency, 'USD' ); ?>>
					USD (Dólares)
				</option>
				<option value="VES" <?php selected( $current_currency, 'VES' ); ?>>
					VES (Bolívares)
				</option>
			</select>
			<span class="wvp-rate-display">
				<?php printf( 'Tasa BCV: %s VES/USD', number_format( $bcv_rate, 2, ',', '.' ) ); ?>
			</span>
		</div>
		<?php
	}

	/**
	 * Get current display currency.
	 *
	 * @since    1.0.0
	 * @return   string    Current currency.
	 */
	private function get_current_display_currency() {
		// Check if user has a preference stored
		if ( isset( $_COOKIE['wvp_display_currency'] ) ) {
			return sanitize_text_field( $_COOKIE['wvp_display_currency'] );
		}

		// Default to VES
		return 'VES';
	}

	/**
	 * AJAX handler for price conversion.
	 *
	 * @since    1.0.0
	 */
	public function ajax_convert_price() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_convert_price' ) ) {
			wp_die( 'Security check failed' );
		}

		$product_id = intval( $_POST['product_id'] );
		$currency = sanitize_text_field( $_POST['currency'] );

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( 'Product not found' );
		}

		$price = $product->get_price();
		if ( $currency === 'VES' ) {
			$converted_price = $this->convert_usd_to_ves( $price );
			$formatted_price = number_format( $converted_price, 2, ',', '.' ) . ' VES';
		} else {
			$formatted_price = '$' . number_format( $price, 2, '.', ',' ) . ' USD';
		}

		// Set cookie for user preference
		setcookie( 'wvp_display_currency', $currency, time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );

		wp_send_json_success( array( 'price' => $formatted_price ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'wvp-currency-converter-improved',
			plugin_dir_url( __FILE__ ) . '../js/wvp-currency-converter-improved.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script( 'wvp-currency-converter-improved', 'wvp_currency_improved', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wvp_convert_price' ),
			'strings' => array(
				'loading' => 'Cargando...',
				'error' => 'Error al convertir precio'
			)
		) );

		wp_enqueue_style(
			'wvp-currency-converter-improved',
			plugin_dir_url( __FILE__ ) . '../css/wvp-currency-converter-improved.css',
			array(),
			'1.0.0'
		);
	}
}
