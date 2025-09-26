<?php
/**
 * Simple Currency Converter - Clean Version
 * Handles USD to VES conversion using BCV rates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Simple_Currency_Converter {
	
	private static $instance = null;
	private $bcv_rate = null;
	private $emergency_rate = 36.5;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->load_bcv_rate();
		$this->init_hooks();
	}
	
	private function init_hooks() {
		// Hooks esenciales
		add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
        // Hooks de WooCommerce - PROBANDO woocommerce_cart_item_subtotal
        add_action( 'woocommerce_single_product_summary', array( $this, 'add_currency_switcher' ), 25 );
        // add_filter( 'woocommerce_cart_item_price', array( $this, 'display_cart_item_price' ), 999, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_cart_item_subtotal' ), 9999, 3 );
        add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'display_converted_total' ) );
        add_action( 'woocommerce_review_order_after_order_total', array( $this, 'display_converted_total' ) );
        // add_filter( 'woocommerce_currency_symbol', array( $this, 'custom_currency_symbol' ), 999, 2 );
        // add_filter( 'woocommerce_price_format', array( $this, 'custom_price_format' ), 999, 2 );
        // add_filter( 'woocommerce_get_price_html', array( $this, 'display_dual_price' ), 999, 2 );
	}
	
	/**
	 * Load BCV rate from options or emergency rate
	 */
	private function load_bcv_rate() {
		$this->bcv_rate = get_option( 'wvp_bcv_rate', $this->emergency_rate );
		$this->emergency_rate = get_option( 'wvp_emergency_rate', 36.5 );
	}
	
	/**
	 * Get current BCV rate
	 */
	public function get_bcv_rate() {
		if ( $this->bcv_rate === null ) {
			$this->load_bcv_rate();
		}
		return $this->bcv_rate;
	}
	
	/**
	 * Convert price from USD to VES
	 */
	public function convert_price( $price, $from_currency = 'USD', $to_currency = 'VES' ) {
		if ( $from_currency === $to_currency ) {
			return $price;
		}
		
		$rate = $this->get_bcv_rate();
		
		if ( $from_currency === 'USD' && $to_currency === 'VES' ) {
			return round( $price * $rate, 2 );
		}
		
		if ( $from_currency === 'VES' && $to_currency === 'USD' ) {
			return round( $price / $rate, 2 );
		}
		
		return $price;
	}
	
	/**
	 * Convert USD to VES (alias method)
	 */
	public function convert_usd_to_ves( $usd_amount ) {
		return $this->convert_price( $usd_amount, 'USD', 'VES' );
	}
	
	/**
	 * AJAX handler for price conversion
	 */
	public function ajax_convert_price() {
		check_ajax_referer( 'wvp_convert_nonce', 'nonce' );
		
		$price = floatval( $_POST['price'] );
		$from_currency = sanitize_text_field( $_POST['from_currency'] );
		$to_currency = sanitize_text_field( $_POST['to_currency'] );
		
		$converted_price = $this->convert_price( $price, $from_currency, $to_currency );
		
		wp_send_json_success( array(
			'converted_price' => $converted_price,
			'rate' => $this->get_bcv_rate()
		));
	}
	
	/**
	 * Update BCV rate
	 */
	public function update_bcv_rate( $new_rate ) {
		update_option( 'wvp_bcv_rate', $new_rate );
		update_option( 'wvp_last_update', current_time( 'mysql' ) );
		$this->bcv_rate = $new_rate;
	}
	
	/**
	 * Get emergency rate
	 */
	public function get_emergency_rate() {
		return $this->emergency_rate;
	}
	
	/**
	 * Set emergency rate
	 */
	public function set_emergency_rate( $rate ) {
		$this->emergency_rate = $rate;
		update_option( 'wvp_emergency_rate', $rate );
	}
	
	/**
	 * Check if BCV rate is available
	 */
	public function is_rate_available() {
		return ! empty( $this->bcv_rate ) && $this->bcv_rate > 0;
	}
	
	/**
	 * Format price in VES
	 */
	public function format_ves_price( $amount ) {
		return number_format( $amount, 2, ',', '.' ) . ' VES';
	}
	
	/**
	 * Format price in USD
	 */
	public function format_usd_price( $amount ) {
		return '$' . number_format( $amount, 2, '.', ',' );
	}
	
	/**
	 * Display currency switcher on product pages
	 */
	public function add_currency_switcher() {
		if ( is_product() ) {
			global $product;
			if ( ! $product ) return;
			
			$price_usd = $product->get_price();
			$price_ves = $this->convert_price( $price_usd, 'USD', 'VES' );
			$rate = $this->get_bcv_rate();
			
			echo '<div class="wvp-currency-switcher">';
			echo '<h4>Cambiar Moneda:</h4>';
			echo '<div class="wvp-currency-buttons">';
			echo '<button class="wvp-currency-btn active" data-currency="usd">USD</button>';
			echo '<button class="wvp-currency-btn" data-currency="ves">VES</button>';
			echo '</div>';
			echo '<div class="wvp-price-display">';
			echo '<span class="wvp-price-usd">$' . number_format( $price_usd, 2 ) . ' USD</span>';
			echo '<span class="wvp-price-ves" style="display: none;">' . number_format( $price_ves, 2 ) . ' VES</span>';
			echo '</div>';
			echo '<div class="wvp-rate-info">';
			echo '<small>Tipo de cambio: 1 USD = ' . number_format( $rate, 2 ) . ' VES</small>';
			echo '</div>';
			echo '</div>';
		}
	}
	
	/**
	 * Display cart item price with conversion
	 */
    public function display_cart_item_price( $price, $cart_item, $cart_item_key ) {
        try {
            error_log( 'WVP Currency Converter: display_cart_item_price ejecutado - Precio original: ' . $price );
            
            if ( $this->is_rate_available() && isset($cart_item['data']) && is_object($cart_item['data']) ) {
                $product = $cart_item['data'];
                $usd_price = $product->get_price();
                
                if ( $usd_price && $usd_price > 0 ) {
                    $ves_price = $this->convert_price( $usd_price, 'USD', 'VES' );
                    $modified_price = $price . '<br><small style="color: #27ae60;">(' . $this->format_ves_price( $ves_price ) . ')</small>';
                    error_log( 'WVP Currency Converter: Precio modificado: ' . $modified_price );
                    return $modified_price;
                }
            }
            
            error_log( 'WVP Currency Converter: Tasa no disponible o datos inválidos, retornando precio original' );
            return $price;
        } catch ( Exception $e ) {
            error_log( 'WVP Currency Converter ERROR en display_cart_item_price: ' . $e->getMessage() );
            return $price;
        }
    }
	
	/**
	 * Display cart item subtotal with conversion
	 */
    public function display_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
        try {
            error_log( 'WVP Currency Converter: display_cart_item_subtotal ejecutado - Subtotal original: ' . $subtotal );
            error_log( 'WVP Currency Converter: Cart item data: ' . print_r($cart_item, true) );
            
            if ( $this->is_rate_available() && isset($cart_item['data']) && is_object($cart_item['data']) && isset($cart_item['quantity']) ) {
                $product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $usd_price = $product->get_price();
                
                if ( $usd_price && $usd_price > 0 && $quantity > 0 ) {
                    $usd_subtotal = $usd_price * $quantity;
                    $ves_subtotal = $this->convert_price( $usd_subtotal, 'USD', 'VES' );
                    $modified_subtotal = $subtotal . '<br><small style="color: #27ae60;">(' . $this->format_ves_price( $ves_subtotal ) . ')</small>';
                    error_log( 'WVP Currency Converter: Subtotal modificado: ' . $modified_subtotal );
                    return $modified_subtotal;
                }
            }
            
            error_log( 'WVP Currency Converter: Tasa no disponible o datos inválidos, retornando subtotal original' );
            return $subtotal;
        } catch ( Exception $e ) {
            error_log( 'WVP Currency Converter ERROR en display_cart_item_subtotal: ' . $e->getMessage() );
            return $subtotal;
        }
    }
	
	/**
	 * Display converted total in cart and checkout
	 */
	public function display_converted_total() {
		if ( $this->is_rate_available() ) {
			$cart_total = WC()->cart->get_total( 'raw' );
			$ves_total = $this->convert_usd_to_ves( $cart_total );
			
			echo '<tr class="wvp-converted-total">';
			echo '<th>Total en VES:</th>';
			echo '<td>' . $this->format_ves_price( $ves_total ) . '</td>';
			echo '</tr>';
		}
	}
	
	/**
	 * Custom currency symbol
	 */
	public function custom_currency_symbol( $currency_symbol, $currency ) {
		if ( $currency === 'USD' ) {
			return '$';
		}
		return $currency_symbol;
	}
	
	/**
	 * Custom price format
	 */
	public function custom_price_format( $format, $currency_pos ) {
		return '%1$s%2$s';
	}
	
	/**
	 * Display dual price (USD and VES)
	 */
	public function display_dual_price( $price_html, $product ) {
		if ( $this->is_rate_available() && is_product() ) {
			$usd_price = $product->get_price();
			$ves_price = $this->convert_price( $usd_price, 'USD', 'VES' );
			
			$price_html .= '<br><small class="wvp-ves-price" style="color: #27ae60;">' . $this->format_ves_price( $ves_price ) . '</small>';
		}
		return $price_html;
	}
	
	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		// Cargar en páginas de WooCommerce y productos
		if ( is_woocommerce() || is_product() || is_shop() || is_cart() || is_checkout() ) {
			$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
			$plugin_version = '1.0.0';

			wp_enqueue_style( 'wvp-simple-converter', $plugin_url . 'public/css/wvp-simple-converter.css', array(), $plugin_version );
			wp_enqueue_script( 'wvp-simple-converter', $plugin_url . 'public/js/wvp-simple-converter.js', array( 'jquery' ), $plugin_version, true );
			
			wp_localize_script( 'wvp-simple-converter', 'wvp_converter_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvp_convert_nonce' ),
				'rate' => $this->get_bcv_rate()
			));
		}
	}
}