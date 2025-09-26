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
		// DESACTIVADO: add_action( 'woocommerce_single_product_summary', array( $this, 'add_dual_price_display' ), 10 );
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
			$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
			$plugin_version = '1.0.0';
			
			wp_enqueue_style( 'wvp-product-display', $plugin_url . 'public/css/wvp-product-display.css', array(), $plugin_version );
			wp_enqueue_script( 'wvp-product-display', $plugin_url . 'public/js/wvp-product-display.js', array( 'jquery' ), $plugin_version, true );
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
	
	/**
	 * Add currency switcher to product pages
	 */
	public function add_currency_switcher() {
		if ( is_product() ) {
			global $product;
			
			if ( $product && $product->get_price() ) {
				$usd_price = $product->get_price();
				$ves_price = $usd_price * $this->bcv_rate;
				
				echo '<div class="wvp-currency-switcher">';
				echo '<h4>Cambiar Moneda:</h4>';
				echo '<div class="wvp-currency-buttons">';
				echo '<button class="wvp-currency-btn active" data-currency="usd">USD</button>';
				echo '<button class="wvp-currency-btn" data-currency="ves">VES</button>';
				echo '</div>';
				echo '<div class="wvp-price-display">';
				echo '<span class="wvp-price-usd">$' . number_format( $usd_price, 2 ) . ' USD</span>';
				echo '<span class="wvp-price-ves" style="display: none;">' . number_format( $ves_price, 2 ) . ' VES</span>';
				echo '</div>';
				echo '<div class="wvp-rate-info">';
				echo '<small>Tipo de cambio: 1 USD = ' . number_format( $this->bcv_rate, 2 ) . ' VES</small>';
				echo '</div>';
				echo '</div>';
			}
		}
	}
	
	/**
	 * Show Venezuelan-specific information
	 */
	public function show_venezuelan_info() {
		if ( is_product() ) {
			echo '<div class="wvp-venezuelan-info">';
			echo '<h4>Información para Venezuela:</h4>';
			echo '<div class="wvp-info-items">';
			
			// Shipping info
			echo '<div class="wvp-info-item">';
			echo '<strong>Envío:</strong> Disponible en todo el país';
			echo '</div>';
			
			// Payment info
			echo '<div class="wvp-info-item">';
			echo '<strong>Pago:</strong> Transferencia bancaria, Pago Móvil';
			echo '</div>';
			
			// Tax info
			echo '<div class="wvp-info-item">';
			echo '<strong>Impuestos:</strong> IVA 16%, IGTF 3% (sobre $200)';
			echo '</div>';
			
			// Warranty info
			echo '<div class="wvp-info-item">';
			echo '<strong>Garantía:</strong> 1 año de garantía';
			echo '</div>';
			
			echo '</div>';
			echo '</div>';
		}
	}
	
	/**
	 * Get BCV rate
	 */
	public function get_bcv_rate() {
		return get_option( 'wvp_bcv_rate', $this->bcv_rate );
	}
	
	/**
	 * Set BCV rate
	 */
	public function set_bcv_rate( $rate ) {
		$this->bcv_rate = $rate;
		update_option( 'wvp_bcv_rate', $rate );
	}
	
	/**
	 * Convert USD to VES
	 */
	public function convert_usd_to_ves( $usd_amount ) {
		return round( $usd_amount * $this->get_bcv_rate(), 2 );
	}
	
	/**
	 * Convert VES to USD
	 */
	public function convert_ves_to_usd( $ves_amount ) {
		return round( $ves_amount / $this->get_bcv_rate(), 2 );
	}
	
	/**
	 * Format price in Venezuelan format
	 */
	public function format_venezuelan_price( $amount ) {
		return number_format( $amount, 2, ',', '.' );
	}
	
	/**
	 * Get dual price HTML for any amount
	 */
	public function get_dual_price_html( $usd_amount ) {
		$ves_amount = $this->convert_usd_to_ves( $usd_amount );
		
		$html = '<div class="wvp-dual-price-html">';
		$html .= '<span class="wvp-price-usd">$' . number_format( $usd_amount, 2 ) . ' USD</span>';
		$html .= '<span class="wvp-price-ves">' . $this->format_venezuelan_price( $ves_amount ) . ' VES</span>';
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Add Venezuelan product tabs
	 */
	public function add_venezuelan_tabs( $tabs ) {
		$tabs['venezuelan_info'] = array(
			'title' => 'Información Venezuela',
			'priority' => 25,
			'callback' => array( $this, 'venezuelan_info_tab_content' )
		);
		
		return $tabs;
	}
	
	/**
	 * Venezuelan info tab content
	 */
	public function venezuelan_info_tab_content() {
		echo '<div class="wvp-venezuelan-tab-content">';
		echo '<h3>Información Específica para Venezuela</h3>';
		
		echo '<div class="wvp-tab-section">';
		echo '<h4>Envío y Entrega</h4>';
		echo '<ul>';
		echo '<li>Envío disponible en todos los estados de Venezuela</li>';
		echo '<li>Tiempo de entrega: 2-7 días hábiles</li>';
		echo '<li>Envío gratis en compras superiores a $50 USD</li>';
		echo '<li>Seguimiento de envío disponible</li>';
		echo '</ul>';
		echo '</div>';
		
		echo '<div class="wvp-tab-section">';
		echo '<h4>Métodos de Pago</h4>';
		echo '<ul>';
		echo '<li>Transferencia bancaria</li>';
		echo '<li>Pago Móvil</li>';
		echo '<li>Efectivo contra entrega (solo Caracas)</li>';
		echo '</ul>';
		echo '</div>';
		
		echo '<div class="wvp-tab-section">';
		echo '<h4>Impuestos</h4>';
		echo '<ul>';
		echo '<li>IVA: 16%</li>';
		echo '<li>IGTF: 3% (solo en compras superiores a $200 USD)</li>';
		echo '<li>Los impuestos se calculan automáticamente</li>';
		echo '</ul>';
		echo '</div>';
		
		echo '</div>';
	}
	
	/**
	 * Add Venezuelan shipping info to product
	 */
	public function add_shipping_info() {
		if ( is_product() ) {
			echo '<div class="wvp-shipping-info">';
			echo '<h4>Información de Envío</h4>';
			echo '<div class="wvp-shipping-details">';
			echo '<p><strong>Disponibilidad:</strong> Todo Venezuela</p>';
			echo '<p><strong>Tiempo:</strong> 2-7 días hábiles</p>';
			echo '<p><strong>Costo:</strong> Desde $3 USD</p>';
			echo '<p><strong>Gratis:</strong> Compras superiores a $50 USD</p>';
			echo '</div>';
			echo '</div>';
		}
	}
}
