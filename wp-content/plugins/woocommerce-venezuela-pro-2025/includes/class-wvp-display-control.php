<?php
/**
 * Control de Visualización - WooCommerce Venezuela Pro 2025
 * 
 * Controla dónde y cómo se muestran los conversores de moneda
 * Basado en las mejores prácticas del plugin original
 * 
 * @package WooCommerce_Venezuela_Pro_2025
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Display_Control {
	
	/**
	 * Instancia única (Singleton)
	 * 
	 * @var WVP_Display_Control
	 */
	private static $instance = null;
	
	/**
	 * Gestor de monedas
	 * 
	 * @var WVP_Currency_Manager
	 */
	private $currency_manager;
	
	/**
	 * Configuraciones de visualización
	 * 
	 * @var array
	 */
	private $display_settings;
	
	/**
	 * Obtener instancia única
	 * 
	 * @return WVP_Display_Control
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor privado
	 */
	private function __construct() {
		$this->currency_manager = WVP_Currency_Manager::get_instance();
		$this->display_settings = WVP_Display_Settings::get_settings();
		$this->init_hooks();
	}
	
	/**
	 * Inicializar hooks
	 */
	private function init_hooks() {
		// Enqueue assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Shortcodes
		add_shortcode( 'wvp_currency_switcher', array( $this, 'currency_switcher_shortcode' ) );
		add_shortcode( 'wvp_bcv_rate', array( $this, 'bcv_rate_shortcode' ) );
		add_shortcode( 'wvp_price_converter', array( $this, 'price_converter_shortcode' ) );
		
		// Hooks de WooCommerce según configuración
		$this->init_woocommerce_hooks();
		
		// Filtros para controlar visualización
		add_filter( 'wvp_show_currency_converter', array( $this, 'should_show_converter' ), 10, 2 );
		add_filter( 'wvp_converter_style', array( $this, 'get_converter_style' ), 10, 2 );
		
		// CSS personalizado en el head
		add_action( 'wp_head', array( $this, 'output_custom_css' ) );
	}
	
	/**
	 * Inicializar hooks de WooCommerce según configuración
	 */
	private function init_woocommerce_hooks() {
		// Páginas de producto individual
		if ( WVP_Display_Settings::should_display_in_context( 'single_product' ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'display_product_converter' ), 25 );
		}
		
		// Lista de productos (shop)
		if ( WVP_Display_Settings::should_display_in_context( 'shop_loop' ) ) {
			add_filter( 'woocommerce_get_price_html', array( $this, 'add_price_converter_to_loop' ), 10, 2 );
		}
		
		// Carrito
		if ( WVP_Display_Settings::should_display_in_context( 'cart' ) ) {
			add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'display_cart_converter' ) );
		}
		
		// Checkout
		if ( WVP_Display_Settings::should_display_in_context( 'checkout' ) ) {
			add_action( 'woocommerce_review_order_after_order_total', array( $this, 'display_checkout_converter' ) );
		}
		
		// Mini carrito
		if ( WVP_Display_Settings::should_display_in_context( 'mini_cart' ) ) {
			add_action( 'woocommerce_widget_shopping_cart_total', array( $this, 'display_mini_cart_converter' ) );
		}
	}
	
	/**
	 * Enqueue scripts y estilos
	 */
	public function enqueue_scripts() {
		// Estilos
		wp_enqueue_style(
			'wvp-display-control',
			plugin_dir_url( __FILE__ ) . '../public/css/wvp-display-control.css',
			array(),
			WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION
		);
		
		// Scripts
		wp_enqueue_script(
			'wvp-display-control',
			plugin_dir_url( __FILE__ ) . '../public/js/wvp-display-control.js',
			array( 'jquery' ),
			WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION,
			true
		);
		
		// Localizar script
		wp_localize_script( 'wvp-display-control', 'wvp_display', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wvp_display_nonce' ),
			'currency_rate' => $this->currency_manager->get_bcv_rate(),
			'settings' => WVP_Display_Settings::get_js_config(),
			'strings' => array(
				'converting' => __( 'Convirtiendo...', 'woocommerce-venezuela-pro-2025' ),
				'error' => __( 'Error en la conversión', 'woocommerce-venezuela-pro-2025' ),
				'rate_unavailable' => __( 'Tasa no disponible', 'woocommerce-venezuela-pro-2025' )
			)
		) );
	}
	
	/**
	 * Shortcode: Selector de moneda
	 * 
	 * @param array $atts Atributos del shortcode
	 * @return string HTML del selector
	 */
	public function currency_switcher_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'style' => WVP_Display_Settings::get_setting( 'appearance', 'style', 'buttons' ),
			'theme' => WVP_Display_Settings::get_setting( 'appearance', 'theme', 'default' ),
			'size' => WVP_Display_Settings::get_setting( 'appearance', 'size', 'medium' ),
			'show_labels' => WVP_Display_Settings::get_setting( 'appearance', 'show_labels', true ),
			'show_rate' => WVP_Display_Settings::get_setting( 'appearance', 'show_rate', false ),
			'animation' => WVP_Display_Settings::get_setting( 'appearance', 'animation', 'fade' ),
			'scope' => 'global' // global, local
		), $atts );
		
		if ( ! $this->currency_manager->is_rate_available() ) {
			return '<div class="wvp-currency-error">' . __( 'Tasa BCV no disponible', 'woocommerce-venezuela-pro-2025' ) . '</div>';
		}
		
		$current_currency = $this->get_current_currency();
		$rate = $this->currency_manager->get_bcv_rate();
		
		$css_classes = array(
			'wvp-currency-switcher',
			'wvp-style-' . $atts['style'],
			'wvp-theme-' . $atts['theme'],
			'wvp-size-' . $atts['size'],
			'wvp-scope-' . $atts['scope'],
			'wvp-animation-' . $atts['animation']
		);
		
		$output = '<div class="' . implode( ' ', $css_classes ) . '" data-rate="' . esc_attr( $rate ) . '">';
		
		switch ( $atts['style'] ) {
			case 'buttons':
				$output .= $this->render_button_switcher( $current_currency, $atts );
				break;
			case 'dropdown':
				$output .= $this->render_dropdown_switcher( $current_currency, $atts );
				break;
			case 'toggle':
				$output .= $this->render_toggle_switcher( $current_currency, $atts );
				break;
			case 'inline':
				$output .= $this->render_inline_switcher( $current_currency, $atts );
				break;
		}
		
		// Mostrar tasa si está habilitado
		if ( $atts['show_rate'] ) {
			$output .= $this->render_rate_display( $rate );
		}
		
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Shortcode: Tasa BCV
	 * 
	 * @param array $atts Atributos del shortcode
	 * @return string HTML de la tasa
	 */
	public function bcv_rate_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'format' => 'simple', // simple, detailed, inline
			'show_label' => true,
			'show_date' => false,
			'style' => 'default' // default, minimal, highlight
		), $atts );
		
		$rate = $this->currency_manager->get_bcv_rate();
		
		if ( ! $rate ) {
			return '<span class="wvp-bcv-error">' . __( 'Tasa BCV no disponible', 'woocommerce-venezuela-pro-2025' ) . '</span>';
		}
		
		$css_class = 'wvp-bcv-rate wvp-bcv-' . $atts['style'];
		$output = '';
		
		switch ( $atts['format'] ) {
			case 'simple':
				$output = $this->format_simple_rate( $rate, $atts );
				break;
			case 'detailed':
				$output = $this->format_detailed_rate( $rate, $atts );
				break;
			case 'inline':
				$output = $this->format_inline_rate( $rate, $atts );
				break;
		}
		
		return '<div class="' . $css_class . '">' . $output . '</div>';
	}
	
	/**
	 * Shortcode: Conversor de precio
	 * 
	 * @param array $atts Atributos del shortcode
	 * @return string HTML del conversor
	 */
	public function price_converter_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'amount' => 0,
			'from' => 'USD',
			'to' => 'VES',
			'show_input' => false,
			'show_both' => true
		), $atts );
		
		$amount = floatval( $atts['amount'] );
		$converted = $this->currency_manager->convert_price( $amount, $atts['from'], $atts['to'] );
		
		$output = '<div class="wvp-price-converter">';
		
		if ( $atts['show_input'] ) {
			$output .= '<input type="number" class="wvp-converter-input" value="' . $amount . '" data-from="' . $atts['from'] . '" data-to="' . $atts['to'] . '">';
		}
		
		if ( $atts['show_both'] ) {
			$output .= '<span class="wvp-price-original">' . $this->currency_manager->format_currency( $amount, $atts['from'] ) . '</span>';
			$output .= '<span class="wvp-price-separator"> = </span>';
		}
		
		$output .= '<span class="wvp-price-converted">' . $this->currency_manager->format_currency( $converted, $atts['to'] ) . '</span>';
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Mostrar conversor en página de producto
	 */
	public function display_product_converter() {
		if ( ! is_product() ) {
			return;
		}
		
		echo $this->currency_switcher_shortcode( array(
			'scope' => 'local',
			'style' => WVP_Display_Settings::get_setting( 'appearance', 'style', 'buttons' )
		) );
	}
	
	/**
	 * Añadir conversor a precio en loop de productos
	 * 
	 * @param string $price_html HTML del precio
	 * @param WC_Product $product Producto
	 * @return string HTML modificado
	 */
	public function add_price_converter_to_loop( $price_html, $product ) {
		if ( ! is_shop() && ! is_product_category() && ! is_search() ) {
			return $price_html;
		}
		
		$position = WVP_Display_Settings::get_setting( 'appearance', 'position', 'after_price' );
		
		$converter = '<div class="wvp-loop-converter" data-product-id="' . $product->get_id() . '">';
		$converter .= $this->currency_switcher_shortcode( array(
			'scope' => 'local',
			'size' => 'small',
			'style' => 'inline'
		) );
		$converter .= '</div>';
		
		switch ( $position ) {
			case 'before_price':
				return $converter . $price_html;
			case 'after_price':
				return $price_html . $converter;
			case 'replace_price':
				return $converter;
			default:
				return $price_html . $converter;
		}
	}
	
	/**
	 * Mostrar conversor en carrito
	 */
	public function display_cart_converter() {
		echo '<tr class="wvp-cart-converter-row">';
		echo '<th colspan="2">' . __( 'Total en VES', 'woocommerce-venezuela-pro-2025' ) . '</th>';
		echo '<td data-title="' . __( 'Total en VES', 'woocommerce-venezuela-pro-2025' ) . '">';
		
		$cart_total = WC()->cart->get_total( 'raw' );
		$converted_total = $this->currency_manager->convert_price( $cart_total, 'USD', 'VES' );
		
		echo '<strong>' . $this->currency_manager->format_currency( $converted_total, 'VES' ) . '</strong>';
		echo '</td>';
		echo '</tr>';
	}
	
	/**
	 * Mostrar conversor en checkout
	 */
	public function display_checkout_converter() {
		$this->display_cart_converter();
	}
	
	/**
	 * Mostrar conversor en mini carrito
	 */
	public function display_mini_cart_converter() {
		$cart_total = WC()->cart->get_total( 'raw' );
		$converted_total = $this->currency_manager->convert_price( $cart_total, 'USD', 'VES' );
		
		echo '<div class="wvp-mini-cart-converter">';
		echo '<small>' . __( 'Total en VES:', 'woocommerce-venezuela-pro-2025' ) . ' ';
		echo '<strong>' . $this->currency_manager->format_currency( $converted_total, 'VES' ) . '</strong>';
		echo '</small>';
		echo '</div>';
	}
	
	/**
	 * Renderizar selector de botones
	 */
	private function render_button_switcher( $current_currency, $atts ) {
		$output = '<div class="wvp-currency-buttons">';
		
		// Botón USD
		$usd_class = $current_currency === 'USD' ? 'active' : '';
		$output .= '<button type="button" class="wvp-currency-btn wvp-usd-btn ' . $usd_class . '" data-currency="USD">';
		if ( $atts['show_labels'] ) {
			$output .= '<span class="wvp-currency-label">USD</span>';
		}
		$output .= '<span class="wvp-currency-symbol">$</span>';
		$output .= '</button>';
		
		// Botón VES
		$ves_class = $current_currency === 'VES' ? 'active' : '';
		$output .= '<button type="button" class="wvp-currency-btn wvp-ves-btn ' . $ves_class . '" data-currency="VES">';
		if ( $atts['show_labels'] ) {
			$output .= '<span class="wvp-currency-label">VES</span>';
		}
		$output .= '<span class="wvp-currency-symbol">Bs.</span>';
		$output .= '</button>';
		
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Renderizar selector dropdown
	 */
	private function render_dropdown_switcher( $current_currency, $atts ) {
		$output = '<select class="wvp-currency-dropdown" data-current="' . $current_currency . '">';
		$output .= '<option value="USD"' . selected( $current_currency, 'USD', false ) . '>USD ($)</option>';
		$output .= '<option value="VES"' . selected( $current_currency, 'VES', false ) . '>VES (Bs.)</option>';
		$output .= '</select>';
		
		return $output;
	}
	
	/**
	 * Renderizar selector toggle
	 */
	private function render_toggle_switcher( $current_currency, $atts ) {
		$output = '<div class="wvp-currency-toggle">';
		$output .= '<input type="checkbox" id="wvp-currency-toggle-' . uniqid() . '" class="wvp-toggle-input" ' . checked( $current_currency, 'VES', false ) . '>';
		$output .= '<label for="wvp-currency-toggle-' . uniqid() . '" class="wvp-toggle-label">';
		$output .= '<span class="wvp-toggle-slider"></span>';
		if ( $atts['show_labels'] ) {
			$output .= '<span class="wvp-toggle-text">USD / VES</span>';
		}
		$output .= '</label>';
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Renderizar selector inline
	 */
	private function render_inline_switcher( $current_currency, $atts ) {
		$output = '<span class="wvp-currency-inline">';
		$output .= '<span class="wvp-currency-current">' . $current_currency . '</span>';
		$output .= '<span class="wvp-currency-switch-link" data-toggle-currency="' . ( $current_currency === 'USD' ? 'VES' : 'USD' ) . '">';
		$output .= '→ ' . ( $current_currency === 'USD' ? 'VES' : 'USD' );
		$output .= '</span>';
		$output .= '</span>';
		
		return $output;
	}
	
	/**
	 * Renderizar display de tasa
	 */
	private function render_rate_display( $rate ) {
		$output = '<div class="wvp-rate-display">';
		$output .= '<small>' . __( 'Tasa BCV:', 'woocommerce-venezuela-pro-2025' ) . ' ';
		$output .= number_format( $rate, 2, ',', '.' ) . ' Bs./USD</small>';
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Formatear tasa simple
	 */
	private function format_simple_rate( $rate, $atts ) {
		$output = '';
		
		if ( $atts['show_label'] ) {
			$output .= __( 'Tasa BCV:', 'woocommerce-venezuela-pro-2025' ) . ' ';
		}
		
		$output .= number_format( $rate, 2, ',', '.' ) . ' Bs./USD';
		
		if ( $atts['show_date'] ) {
			$last_update = get_option( 'bcv_last_update', '' );
			if ( ! empty( $last_update ) ) {
				$output .= ' (' . date( 'd/m/Y', strtotime( $last_update ) ) . ')';
			}
		}
		
		return $output;
	}
	
	/**
	 * Formatear tasa detallada
	 */
	private function format_detailed_rate( $rate, $atts ) {
		$output = '<div class="wvp-bcv-detailed">';
		
		if ( $atts['show_label'] ) {
			$output .= '<div class="wvp-bcv-title">' . __( 'Tasa de Cambio BCV', 'woocommerce-venezuela-pro-2025' ) . '</div>';
		}
		
		$output .= '<div class="wvp-bcv-rate-main">' . number_format( $rate, 2, ',', '.' ) . ' Bs./USD</div>';
		
		if ( $atts['show_date'] ) {
			$last_update = get_option( 'bcv_last_update', '' );
			if ( ! empty( $last_update ) ) {
				$output .= '<div class="wvp-bcv-date">' . __( 'Actualizado:', 'woocommerce-venezuela-pro-2025' ) . ' ' . date( 'd/m/Y H:i', strtotime( $last_update ) ) . '</div>';
			}
		}
		
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Formatear tasa inline
	 */
	private function format_inline_rate( $rate, $atts ) {
		$output = '';
		
		if ( $atts['show_label'] ) {
			$output .= '1 USD = ';
		}
		
		$output .= number_format( $rate, 2, ',', '.' ) . ' Bs.';
		
		return $output;
	}
	
	/**
	 * Obtener moneda actual
	 * 
	 * @return string Moneda actual
	 */
	private function get_current_currency() {
		return isset( $_COOKIE['wvp_currency'] ) ? $_COOKIE['wvp_currency'] : 'USD';
	}
	
	/**
	 * Verificar si mostrar conversor
	 * 
	 * @param bool $show Mostrar por defecto
	 * @param string $context Contexto
	 * @return bool True si debe mostrar
	 */
	public function should_show_converter( $show, $context ) {
		return WVP_Display_Settings::should_display_in_context( $context );
	}
	
	/**
	 * Obtener estilo del conversor
	 * 
	 * @param string $style Estilo por defecto
	 * @param string $context Contexto
	 * @return string Estilo a usar
	 */
	public function get_converter_style( $style, $context ) {
		return WVP_Display_Settings::get_setting( 'appearance', 'style', $style );
	}
	
	/**
	 * Output CSS personalizado en el head
	 */
	public function output_custom_css() {
		$custom_css = WVP_Display_Settings::generate_custom_css();
		
		if ( ! empty( $custom_css ) ) {
			echo '<style id="wvp-custom-css">' . $custom_css . '</style>';
		}
	}
}

// Inicializar la clase
WVP_Display_Control::get_instance();
