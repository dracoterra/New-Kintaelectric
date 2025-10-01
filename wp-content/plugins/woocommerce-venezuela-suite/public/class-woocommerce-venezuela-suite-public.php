<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://artifexcodes.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Suite
 * @subpackage Woocommerce_Venezuela_Suite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woocommerce_Venezuela_Suite
 * @subpackage Woocommerce_Venezuela_Suite/public
 * @author     ronald alvarez <ronaldalv2025@gmail.com>
 */
class Woocommerce_Venezuela_Suite_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-venezuela-suite-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-venezuela-suite-public.js', array( 'jquery' ), $this->version, false );
		
		// Localize script with exchange rate data
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		if ( $module_manager->get_module_state( 'bcv-integration' ) === 'active' ) {
			$bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
			$current_rate = $bcv_core->get_current_rate();
			
			wp_localize_script( $this->plugin_name, 'wvs_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvs_frontend_nonce' ),
				'current_rate' => $current_rate,
				'currency_symbol' => get_woocommerce_currency_symbol( 'VES' ),
			));
		}
	}

	/**
	 * Initialize frontend hooks for price display and currency conversion
	 */
	public function init_frontend_hooks() {
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		
		// Only initialize if Currency Converter module is active
		if ( $module_manager->get_module_state( 'currency-converter' ) === 'active' ) {
			add_filter( 'woocommerce_price_html', array( $this, 'display_converted_price' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'display_cart_item_price' ), 10, 3 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_cart_item_subtotal' ), 10, 3 );
			add_filter( 'woocommerce_cart_subtotal', array( $this, 'display_cart_subtotal' ), 10, 3 );
			add_filter( 'woocommerce_cart_total', array( $this, 'display_cart_total' ), 10, 1 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'display_exchange_rate_info' ), 25 );
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'display_exchange_rate_info' ), 15 );
		}
	}

	/**
	 * Display converted price with VES equivalent
	 */
	public function display_converted_price( $price_html, $product ) {
		if ( ! $product || get_woocommerce_currency() !== 'USD' ) {
			return $price_html;
		}

		$currency_converter = Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();
		$price_usd = $product->get_price();
		
		if ( $price_usd > 0 ) {
			$price_ves = $currency_converter->convert_usd_to_ves( $price_usd );
			$formatted_ves = $currency_converter->format_ves_price( $price_ves );
			
			$price_html .= '<div class="wvs-price-conversion">';
			$price_html .= '<small class="wvs-ves-price">' . sprintf( __( 'Aprox. %s', 'woocommerce-venezuela-suite' ), $formatted_ves ) . '</small>';
			$price_html .= '</div>';
		}

		return $price_html;
	}

	/**
	 * Display cart item price with VES conversion
	 */
	public function display_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
		if ( get_woocommerce_currency() !== 'USD' ) {
			return $price_html;
		}

		$currency_converter = Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();
		$price_usd = $cart_item['data']->get_price();
		
		if ( $price_usd > 0 ) {
			$price_ves = $currency_converter->convert_usd_to_ves( $price_usd );
			$formatted_ves = $currency_converter->format_ves_price( $price_ves );
			
			$price_html .= '<div class="wvs-cart-price-conversion">';
			$price_html .= '<small class="wvs-ves-price">' . sprintf( __( 'Aprox. %s', 'woocommerce-venezuela-suite' ), $formatted_ves ) . '</small>';
			$price_html .= '</div>';
		}

		return $price_html;
	}

	/**
	 * Display cart item subtotal with VES conversion
	 */
	public function display_cart_item_subtotal( $subtotal_html, $cart_item, $cart_item_key ) {
		if ( get_woocommerce_currency() !== 'USD' ) {
			return $subtotal_html;
		}

		$currency_converter = Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();
		$subtotal_usd = $cart_item['line_total'];
		
		if ( $subtotal_usd > 0 ) {
			$subtotal_ves = $currency_converter->convert_usd_to_ves( $subtotal_usd );
			$formatted_ves = $currency_converter->format_ves_price( $subtotal_ves );
			
			$subtotal_html .= '<div class="wvs-cart-subtotal-conversion">';
			$subtotal_html .= '<small class="wvs-ves-price">' . sprintf( __( 'Aprox. %s', 'woocommerce-venezuela-suite' ), $formatted_ves ) . '</small>';
			$subtotal_html .= '</div>';
		}

		return $subtotal_html;
	}

	/**
	 * Display cart subtotal with VES conversion
	 */
	public function display_cart_subtotal( $subtotal_html, $compound, $cart ) {
		if ( get_woocommerce_currency() !== 'USD' ) {
			return $subtotal_html;
		}

		$currency_converter = Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();
		$subtotal_usd = $cart->get_subtotal();
		
		if ( $subtotal_usd > 0 ) {
			$subtotal_ves = $currency_converter->convert_usd_to_ves( $subtotal_usd );
			$formatted_ves = $currency_converter->format_ves_price( $subtotal_ves );
			
			$subtotal_html .= '<div class="wvs-cart-total-conversion">';
			$subtotal_html .= '<small class="wvs-ves-price">' . sprintf( __( 'Aprox. %s', 'woocommerce-venezuela-suite' ), $formatted_ves ) . '</small>';
			$subtotal_html .= '</div>';
		}

		return $subtotal_html;
	}

	/**
	 * Display cart total with VES conversion
	 */
	public function display_cart_total( $total_html ) {
		if ( get_woocommerce_currency() !== 'USD' ) {
			return $total_html;
		}

		$currency_converter = Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();
		$total_usd = WC()->cart->get_total( 'raw' );
		
		if ( $total_usd > 0 ) {
			$total_ves = $currency_converter->convert_usd_to_ves( $total_usd );
			$formatted_ves = $currency_converter->format_ves_price( $total_ves );
			
			$total_html .= '<div class="wvs-cart-total-conversion">';
			$total_html .= '<small class="wvs-ves-price">' . sprintf( __( 'Total aprox. %s', 'woocommerce-venezuela-suite' ), $formatted_ves ) . '</small>';
			$total_html .= '</div>';
		}

		return $total_html;
	}

	/**
	 * Display exchange rate information
	 */
	public function display_exchange_rate_info() {
		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		
		if ( $module_manager->get_module_state( 'bcv-integration' ) === 'active' ) {
			$bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
			$current_rate = $bcv_core->get_current_rate();
			
			if ( $current_rate > 0 ) {
				echo '<div class="wvs-exchange-rate-info">';
				echo '<small class="wvs-rate-display">';
				printf( __( 'Tipo de cambio BCV: 1 USD = %s VES', 'woocommerce-venezuela-suite' ), 
					number_format( $current_rate, 2, ',', '.' ) );
				echo '</small>';
				echo '</div>';
			}
		}
	}

	/**
	 * AJAX handler for getting exchange rate
	 */
	public function ajax_get_exchange_rate() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvs_frontend_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
		
		if ( $module_manager->get_module_state( 'bcv-integration' ) === 'active' ) {
			$bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
			$current_rate = $bcv_core->get_current_rate();
			
			if ( $current_rate > 0 ) {
				wp_send_json_success( array(
					'rate' => $current_rate,
					'formatted_rate' => number_format( $current_rate, 2, ',', '.' )
				));
			}
		}

		wp_send_json_error( 'Exchange rate not available' );
	}

}
