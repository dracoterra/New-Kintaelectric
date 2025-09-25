<?php
/**
 * Enhanced Product Display
 * Shows dual prices (USD/VES) and Venezuelan-specific information
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Product_Display {
	
	private static $instance = null;
	private $bcv_rate = 36.5; // Default rate
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->init_hooks();
	}
	
	private function init_hooks() {
		add_action( 'woocommerce_single_product_summary', array( $this, 'add_dual_price_display' ), 10 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'add_shop_dual_price' ), 15 );
		add_action( 'woocommerce_cart_item_price', array( $this, 'add_cart_dual_price' ), 10, 3 );
		add_action( 'woocommerce_cart_item_subtotal', array( $this, 'add_cart_subtotal_dual' ), 10, 3 );
		add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'add_cart_total_dual' ) );
		add_action( 'woocommerce_review_order_after_order_total', array( $this, 'add_checkout_total_dual' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}
	
	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		if ( is_woocommerce() ) {
			wp_enqueue_style( 'wvp-product-display', WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL . 'public/css/wvp-product-display.css', array(), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION );
			wp_enqueue_script( 'wvp-product-display', WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL . 'public/js/wvp-product-display.js', array( 'jquery' ), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION, true );
		}
	}
	
	/**
	 * Add dual price display to single product
	 */
	public function add_dual_price_display() {
		global $product;
		
		if ( ! $product ) {
			return;
		}
		
		$price_usd = $product->get_price();
		if ( ! $price_usd ) {
			return;
		}
		
		$price_ves = $price_usd * $this->bcv_rate;
		
		echo '<div class="wvp-dual-price-display">';
		echo '<div class="wvp-price-comparison">';
		echo '<div class="wvp-price-item usd">';
		echo '<span class="wvp-currency-label">USD</span>';
		echo '<span class="wvp-price-value">$' . number_format( $price_usd, 2 ) . '</span>';
		echo '</div>';
		echo '<div class="wvp-separator">=</div>';
		echo '<div class="wvp-price-item ves">';
		echo '<span class="wvp-currency-label">VES</span>';
		echo '<span class="wvp-price-value">' . number_format( $price_ves, 2 ) . '</span>';
		echo '</div>';
		echo '</div>';
		echo '<div class="wvp-rate-info">';
		echo '<small>Tasa BCV: 1 USD = ' . number_format( $this->bcv_rate, 2 ) . ' VES</small>';
		echo '</div>';
		echo '</div>';
	}
	
	/**
	 * Add dual price to shop loop
	 */
	public function add_shop_dual_price() {
		global $product;
		
		if ( ! $product ) {
			return;
		}
		
		$price_usd = $product->get_price();
		if ( ! $price_usd ) {
			return;
		}
		
		$price_ves = $price_usd * $this->bcv_rate;
		
		echo '<div class="wvp-shop-dual-price">';
		echo '<div class="wvp-shop-price-row">';
		echo '<span class="wvp-shop-price-usd">USD $' . number_format( $price_usd, 2 ) . '</span>';
		echo '<span class="wvp-shop-price-ves">VES ' . number_format( $price_ves, 2 ) . '</span>';
		echo '</div>';
		echo '</div>';
	}
	
	/**
	 * Add dual price to cart item
	 */
	public function add_cart_dual_price( $price, $cart_item, $cart_item_key ) {
		$product = $cart_item['data'];
		$price_usd = $product->get_price();
		
		if ( ! $price_usd ) {
			return $price;
		}
		
		$price_ves = $price_usd * $this->bcv_rate;
		
		$dual_price = '<div class="wvp-cart-dual-price">';
		$dual_price .= '<div class="wvp-cart-price-row">';
		$dual_price .= '<span class="wvp-cart-price-usd">USD $' . number_format( $price_usd, 2 ) . '</span>';
		$dual_price .= '<span class="wvp-cart-price-ves">VES ' . number_format( $price_ves, 2 ) . '</span>';
		$dual_price .= '</div>';
		$dual_price .= '</div>';
		
		return $price . $dual_price;
	}
	
	/**
	 * Add dual subtotal to cart
	 */
	public function add_cart_subtotal_dual( $subtotal, $cart_item, $cart_item_key ) {
		$product = $cart_item['data'];
		$quantity = $cart_item['quantity'];
		$price_usd = $product->get_price();
		
		if ( ! $price_usd ) {
			return $subtotal;
		}
		
		$total_usd = $price_usd * $quantity;
		$total_ves = $total_usd * $this->bcv_rate;
		
		$dual_subtotal = '<div class="wvp-cart-subtotal-dual">';
		$dual_subtotal .= '<div class="wvp-cart-subtotal-row">';
		$dual_subtotal .= '<span class="wvp-cart-subtotal-usd">USD $' . number_format( $total_usd, 2 ) . '</span>';
		$dual_subtotal .= '<span class="wvp-cart-subtotal-ves">VES ' . number_format( $total_ves, 2 ) . '</span>';
		$dual_subtotal .= '</div>';
		$dual_subtotal .= '</div>';
		
		return $subtotal . $dual_subtotal;
	}
	
	/**
	 * Add dual total to cart
	 */
	public function add_cart_total_dual() {
		$cart = WC()->cart;
		if ( ! $cart ) {
			return;
		}
		
		$total_usd = $cart->get_total( 'raw' );
		$total_ves = $total_usd * $this->bcv_rate;
		
		echo '<div class="wvp-cart-total-dual">';
		echo '<div class="wvp-cart-total-row">';
		echo '<span class="wvp-cart-total-usd">USD $' . number_format( $total_usd, 2 ) . '</span>';
		echo '<span class="wvp-cart-total-ves">VES ' . number_format( $total_ves, 2 ) . '</span>';
		echo '</div>';
		echo '</div>';
	}
	
	/**
	 * Add dual total to checkout
	 */
	public function add_checkout_total_dual() {
		$cart = WC()->cart;
		if ( ! $cart ) {
			return;
		}
		
		$total_usd = $cart->get_total( 'raw' );
		$total_ves = $total_usd * $this->bcv_rate;
		
		echo '<div class="wvp-checkout-total-dual">';
		echo '<div class="wvp-checkout-total-row">';
		echo '<span class="wvp-checkout-total-usd">USD $' . number_format( $total_usd, 2 ) . '</span>';
		echo '<span class="wvp-checkout-total-ves">VES ' . number_format( $total_ves, 2 ) . '</span>';
		echo '</div>';
		echo '</div>';
	}
}
