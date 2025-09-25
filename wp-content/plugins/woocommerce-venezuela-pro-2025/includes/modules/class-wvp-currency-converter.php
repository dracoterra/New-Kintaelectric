<?php

/**
 * Currency Converter Module
 *
 * Handles USD to VES conversion using BCV rates.
 *
 * @link       https://kintaelectric.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes/modules
 */

/**
 * Currency converter module class.
 *
 * This class handles currency conversion from USD to VES using BCV rates
 * with fallback mechanisms for reliability.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes/modules
 * @author     Kinta Electric <info@kintaelectric.com>
 */
class WVP_Currency_Converter {

	/**
	 * The dependency injection container.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WVP_Dependency_Container    $container    The dependency container.
	 */
	protected $container;

	/**
	 * BCV fallback manager.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WVP_BCV_Fallback_Manager    $bcv_fallback    Manages BCV fallback.
	 */
	protected $bcv_fallback;

	/**
	 * Whether the module has been initialized.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      boolean    $initialized    Whether the module has been initialized.
	 */
	protected $initialized = false;

	/**
	 * Initialize the currency converter.
	 *
	 * @since    1.0.0
	 * @param    WVP_Dependency_Container    $container    The dependency container.
	 */
	public function __construct( $container ) {
		$this->container = $container;
		$this->init_bcv_fallback();
	}

	/**
	 * Initialize BCV fallback manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_bcv_fallback() {
		// Load BCV fallback manager
		require_once plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/class-wvp-bcv-fallback-manager.php';
		$this->bcv_fallback = new WVP_BCV_Fallback_Manager();
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
		add_filter( 'woocommerce_cart_item_price', array( $this, 'display_dual_price' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_dual_price' ), 10, 3 );
		add_filter( 'woocommerce_cart_subtotal', array( $this, 'display_cart_subtotal_dual' ), 10, 3 );
		add_filter( 'woocommerce_cart_total', array( $this, 'display_cart_total_dual' ), 10, 1 );
		
		// Add currency switcher to product pages
		add_action( 'woocommerce_single_product_summary', array( $this, 'add_currency_switcher' ), 25 );
		
		// Add currency switcher to shop loop
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'add_currency_switcher_loop' ), 15 );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		
		// Add scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		error_log( 'WVP: Currency Converter module init completed' );
	}

	/**
	 * Convert USD price to VES.
	 *
	 * @since    1.0.0
	 * @param    float    $usd_amount    The USD amount to convert.
	 * @param    float    $rate          Optional custom rate. If not provided, uses BCV rate.
	 * @return   float                   The converted VES amount.
	 */
	public function convert_usd_to_ves( $usd_amount, $rate = null ) {
		if ( empty( $usd_amount ) || $usd_amount <= 0 ) {
			return 0;
		}

		// Get BCV rate if not provided
		if ( $rate === null ) {
			$rate = $this->get_bcv_rate();
		}

		// Validate rate
		if ( ! $this->validate_rate( $rate ) ) {
			// Log error and use fallback rate
			error_log( 'WVP: Invalid BCV rate: ' . $rate );
			$rate = $this->get_fallback_rate();
		}

		$ves_amount = $usd_amount * $rate;
		
		// Round to 2 decimal places for VES
		return round( $ves_amount, 2 );
	}

	/**
	 * Get BCV rate with fallback.
	 *
	 * @since    1.0.0
	 * @return   float    The BCV rate.
	 */
	public function get_bcv_rate() {
		// Try to get rate from BCV Dólar Tracker plugin
		if ( class_exists( 'BCV_Dolar_Tracker' ) ) {
			$rate = BCV_Dolar_Tracker::get_rate();
			if ( $rate && $this->validate_rate( $rate ) ) {
				return $rate;
			}
		}

		// Use fallback manager
		return $this->bcv_fallback->get_bcv_rate();
	}

	/**
	 * Get fallback rate.
	 *
	 * @since    1.0.0
	 * @return   float    The fallback rate.
	 */
	private function get_fallback_rate() {
		// Get manual rate from options
		$manual_rate = get_option( 'wvp_manual_bcv_rate', 0 );
		if ( $manual_rate > 0 && $this->validate_rate( $manual_rate ) ) {
			return $manual_rate;
		}

		// Get last known rate
		$last_rate = get_option( 'wvp_last_known_bcv_rate', 0 );
		if ( $last_rate > 0 && $this->validate_rate( $last_rate ) ) {
			return $last_rate;
		}

		// Use emergency rate
		return get_option( 'wvp_emergency_bcv_rate', 50 ); // Default emergency rate
	}

	/**
	 * Validate BCV rate.
	 *
	 * @since    1.0.0
	 * @param    float    $rate    The rate to validate.
	 * @return   boolean           True if rate is valid, false otherwise.
	 */
	private function validate_rate( $rate ) {
		$min_rate = 20; // Minimum VES per USD
		$max_rate = 100; // Maximum VES per USD
		
		return ( $rate >= $min_rate && $rate <= $max_rate );
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
	 * Display dual price (USD and VES).
	 *
	 * @since    1.0.0
	 * @param    string    $price_html    The price HTML.
	 * @param    array     $cart_item     The cart item.
	 * @param    string    $cart_item_key The cart item key.
	 * @return   string                   The modified price HTML.
	 */
	public function display_dual_price( $price_html, $cart_item, $cart_item_key ) {
		$product = $cart_item['data'];
		
		if ( ! $this->is_product_priced_in_usd( $product ) ) {
			return $price_html;
		}

		$usd_price = $product->get_price();
		$ves_price = $this->convert_usd_to_ves( $usd_price );
		$bcv_rate = $this->get_bcv_rate();

		$dual_price_html = sprintf(
			'<span class="wvp-dual-price">
				<span class="wvp-usd-price">$%s USD</span>
				<span class="wvp-separator"> / </span>
				<span class="wvp-ves-price">%s VES</span>
				<span class="wvp-rate-info">(Tasa: %s)</span>
			</span>',
			number_format( $usd_price, 2 ),
			number_format( $ves_price, 2, ',', '.' ),
			number_format( $bcv_rate, 2, ',', '.' )
		);

		return $dual_price_html;
	}

	/**
	 * Check if product is priced in USD.
	 *
	 * @since    1.0.0
	 * @param    WC_Product $product    The product object.
	 * @return   boolean                True if product is priced in USD, false otherwise.
	 */
	private function is_product_priced_in_usd( $product ) {
		// Check if product has USD pricing meta
		$currency = $product->get_meta( '_wvp_currency', true );
		return ( $currency === 'USD' );
	}

	/**
	 * Format VES price for display.
	 *
	 * @since    1.0.0
	 * @param    float    $amount    The amount to format.
	 * @return   string              The formatted price.
	 */
	public function format_ves_price( $amount ) {
		return number_format( $amount, 2, ',', '.' ) . ' VES';
	}

	/**
	 * Display cart subtotal with dual currency.
	 *
	 * @since    1.0.0
	 * @param    string    $subtotal    The subtotal HTML.
	 * @param    WC_Cart   $cart        The cart object.
	 * @param    boolean   $compound    Whether this is a compound total.
	 * @return   string                 The modified subtotal HTML.
	 */
	public function display_cart_subtotal_dual( $subtotal, $compound, $cart ) {
		if ( ! $this->should_show_dual_currency() ) {
			return $subtotal;
		}

		// Check if cart is a valid WC_Cart object
		if ( ! is_object( $cart ) || ! method_exists( $cart, 'get_subtotal' ) ) {
			return $subtotal;
		}

		$usd_subtotal = $cart->get_subtotal();
		$ves_subtotal = $this->convert_usd_to_ves( $usd_subtotal );
		$bcv_rate = $this->get_bcv_rate();

		$dual_subtotal = sprintf(
			'<span class="wvp-dual-price">
				<span class="wvp-usd-price">$%s USD</span>
				<span class="wvp-separator"> / </span>
				<span class="wvp-ves-price">%s VES</span>
				<span class="wvp-rate-info">(Tasa: %s)</span>
			</span>',
			number_format( $usd_subtotal, 2 ),
			number_format( $ves_subtotal, 2, ',', '.' ),
			number_format( $bcv_rate, 2, ',', '.' )
		);

		return $dual_subtotal;
	}

	/**
	 * Display cart total with dual currency.
	 *
	 * @since    1.0.0
	 * @param    string    $total    The total HTML.
	 * @return   string              The modified total HTML.
	 */
	public function display_cart_total_dual( $total ) {
		if ( ! $this->should_show_dual_currency() ) {
			return $total;
		}

		$cart = WC()->cart;
		if ( ! $cart || ! is_object( $cart ) || ! method_exists( $cart, 'get_total' ) ) {
			return $total;
		}

		$usd_total = $cart->get_total( 'raw' );
		$ves_total = $this->convert_usd_to_ves( $usd_total );
		$bcv_rate = $this->get_bcv_rate();

		$dual_total = sprintf(
			'<span class="wvp-dual-price">
				<span class="wvp-usd-price">$%s USD</span>
				<span class="wvp-separator"> / </span>
				<span class="wvp-ves-price">%s VES</span>
				<span class="wvp-rate-info">(Tasa: %s)</span>
			</span>',
			number_format( $usd_total, 2 ),
			number_format( $ves_total, 2, ',', '.' ),
			number_format( $bcv_rate, 2, ',', '.' )
		);

		return $dual_total;
	}

	/**
	 * Add currency switcher to product pages.
	 *
	 * @since    1.0.0
	 */
	public function add_currency_switcher() {
		if ( ! $this->should_show_currency_switcher() ) {
			return;
		}

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
	 * Add currency switcher to shop loop.
	 *
	 * @since    1.0.0
	 */
	public function add_currency_switcher_loop() {
		if ( ! $this->should_show_currency_switcher() ) {
			return;
		}

		$current_currency = $this->get_current_display_currency();
		$bcv_rate = $this->get_bcv_rate();

		?>
		<div class="wvp-currency-switcher-loop">
			<select class="wvp-currency-select-loop" data-product-id="<?php echo get_the_ID(); ?>">
				<option value="USD" <?php selected( $current_currency, 'USD' ); ?>>
					USD
				</option>
				<option value="VES" <?php selected( $current_currency, 'VES' ); ?>>
					VES
				</option>
			</select>
		</div>
		<?php
	}

	/**
	 * AJAX handler for price conversion.
	 *
	 * @since    1.0.0
	 */
	public function ajax_convert_price() {
		check_ajax_referer( 'wvp_currency_nonce', 'nonce' );

		$product_id = intval( $_POST['product_id'] );
		$currency = sanitize_text_field( $_POST['currency'] );

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_die( 'Product not found' );
		}

		$price = $product->get_price();
		$converted_price = ( $currency === 'VES' ) ? $this->convert_usd_to_ves( $price ) : $price;
		$formatted_price = ( $currency === 'VES' ) ? $this->format_ves_price( $converted_price ) : '$' . number_format( $price, 2 ) . ' USD';

		wp_send_json_success( array(
			'price' => $formatted_price,
			'raw_price' => $converted_price,
			'currency' => $currency
		) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'wvp-currency-converter', plugin_dir_url( __FILE__ ) . '../js/wvp-currency-converter.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_style( 'wvp-currency-converter', plugin_dir_url( __FILE__ ) . '../css/wvp-currency-converter.css', array(), '1.0.0' );

		wp_localize_script( 'wvp-currency-converter', 'wvp_currency', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wvp_currency_nonce' ),
			'strings' => array(
				'loading' => 'Cargando...',
				'error' => 'Error al convertir precio'
			)
		) );
	}

	/**
	 * Check if dual currency should be shown.
	 *
	 * @since    1.0.0
	 * @return   boolean    True if dual currency should be shown, false otherwise.
	 */
	private function should_show_dual_currency() {
		return get_option( 'wvp_show_dual_currency', true );
	}

	/**
	 * Check if currency switcher should be shown.
	 *
	 * @since    1.0.0
	 * @return   boolean    True if currency switcher should be shown, false otherwise.
	 */
	private function should_show_currency_switcher() {
		return get_option( 'wvp_show_currency_switcher', true );
	}

	/**
	 * Get current display currency.
	 *
	 * @since    1.0.0
	 * @return   string    Current display currency (USD or VES).
	 */
	private function get_current_display_currency() {
		return get_option( 'wvp_default_currency', 'USD' );
	}

	/**
	 * Get conversion statistics.
	 *
	 * @since    1.0.0
	 * @return   array    Array of conversion statistics.
	 */
	public function get_conversion_stats() {
		return array(
			'current_rate' => $this->get_bcv_rate(),
			'last_update' => get_option( 'wvp_last_bcv_update', '' ),
			'total_conversions' => get_option( 'wvp_total_conversions', 0 ),
			'fallback_usage' => get_option( 'wvp_fallback_usage_count', 0 ),
		);
	}

}
