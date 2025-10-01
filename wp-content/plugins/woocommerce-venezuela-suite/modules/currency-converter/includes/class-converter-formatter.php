<?php
/**
 * Currency Converter Formatter - Formateador de monedas avanzado
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formateador avanzado para monedas USD y VES.
 * Proporciona múltiples formatos y personalización visual.
 */
class Woocommerce_Venezuela_Suite_Converter_Formatter {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración de formato */
	private $config = array();

	/** @var array Formatos predefinidos */
	private $predefined_formats = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->init_predefined_formats();
	}

	/**
	 * Singleton.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Carga configuración de formato.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'ves_format' => get_option( 'wvs_converter_ves_format', 'symbol' ),
			'usd_format' => get_option( 'wvs_converter_usd_format', 'symbol' ),
			'ves_symbol' => get_option( 'wvs_converter_ves_symbol', 'Bs.' ),
			'usd_symbol' => get_option( 'wvs_converter_usd_symbol', '$' ),
			'ves_position' => get_option( 'wvs_converter_ves_position', 'after' ),
			'usd_position' => get_option( 'wvs_converter_usd_position', 'before' ),
			'ves_decimals' => get_option( 'wvs_converter_ves_decimals', 2 ),
			'usd_decimals' => get_option( 'wvs_converter_usd_decimals', 2 ),
			'ves_thousand_separator' => get_option( 'wvs_converter_ves_thousand_sep', '.' ),
			'usd_thousand_separator' => get_option( 'wvs_converter_usd_thousand_sep', ',' ),
			'ves_decimal_separator' => get_option( 'wvs_converter_ves_decimal_sep', ',' ),
			'usd_decimal_separator' => get_option( 'wvs_converter_usd_decimal_sep', '.' ),
			'show_currency_code' => get_option( 'wvs_converter_show_code', false ),
			'compact_format' => get_option( 'wvs_converter_compact_format', false ),
		);
	}

	/**
	 * Inicializa formatos predefinidos.
	 *
	 * @return void
	 */
	private function init_predefined_formats() {
		$this->predefined_formats = array(
			'venezuelan_standard' => array(
				'name' => __( 'Estándar Venezolano', 'woocommerce-venezuela-suite' ),
				'ves_symbol' => 'Bs.',
				'ves_position' => 'after',
				'ves_decimals' => 2,
				'ves_thousand_separator' => '.',
				'ves_decimal_separator' => ',',
				'usd_symbol' => '$',
				'usd_position' => 'before',
				'usd_decimals' => 2,
				'usd_thousand_separator' => ',',
				'usd_decimal_separator' => '.',
			),
			'international' => array(
				'name' => __( 'Internacional', 'woocommerce-venezuela-suite' ),
				'ves_symbol' => 'VES',
				'ves_position' => 'after',
				'ves_decimals' => 2,
				'ves_thousand_separator' => ',',
				'ves_decimal_separator' => '.',
				'usd_symbol' => 'USD',
				'usd_position' => 'after',
				'usd_decimals' => 2,
				'usd_thousand_separator' => ',',
				'usd_decimal_separator' => '.',
			),
			'minimal' => array(
				'name' => __( 'Minimal', 'woocommerce-venezuela-suite' ),
				'ves_symbol' => 'Bs',
				'ves_position' => 'after',
				'ves_decimals' => 0,
				'ves_thousand_separator' => '.',
				'ves_decimal_separator' => ',',
				'usd_symbol' => '$',
				'usd_position' => 'before',
				'usd_decimals' => 0,
				'usd_thousand_separator' => ',',
				'usd_decimal_separator' => '.',
			),
			'compact' => array(
				'name' => __( 'Compacto', 'woocommerce-venezuela-suite' ),
				'ves_symbol' => 'K',
				'ves_position' => 'after',
				'ves_decimals' => 1,
				'ves_thousand_separator' => '',
				'ves_decimal_separator' => '.',
				'usd_symbol' => 'K',
				'usd_position' => 'after',
				'usd_decimals' => 1,
				'usd_thousand_separator' => '',
				'usd_decimal_separator' => '.',
			),
		);
	}

	/**
	 * Formatea cantidad en VES.
	 *
	 * @param float $amount Cantidad a formatear.
	 * @param array $options Opciones de formato.
	 * @return string Cantidad formateada.
	 */
	public function format_ves( $amount, $options = array() ) {
		$amount = floatval( $amount );
		$format_options = $this->merge_format_options( 'ves', $options );
		
		// Aplicar formato compacto si está habilitado
		if ( $format_options['compact_format'] ) {
			return $this->format_compact( $amount, $format_options );
		}

		// Formatear número
		$formatted_number = $this->format_number( $amount, $format_options );
		
		// Aplicar símbolo y posición
		return $this->apply_symbol( $formatted_number, $format_options );
	}

	/**
	 * Formatea cantidad en USD.
	 *
	 * @param float $amount Cantidad a formatear.
	 * @param array $options Opciones de formato.
	 * @return string Cantidad formateada.
	 */
	public function format_usd( $amount, $options = array() ) {
		$amount = floatval( $amount );
		$format_options = $this->merge_format_options( 'usd', $options );
		
		// Aplicar formato compacto si está habilitado
		if ( $format_options['compact_format'] ) {
			return $this->format_compact( $amount, $format_options );
		}

		// Formatear número
		$formatted_number = $this->format_number( $amount, $format_options );
		
		// Aplicar símbolo y posición
		return $this->apply_symbol( $formatted_number, $format_options );
	}

	/**
	 * Formatea ambas monedas en formato dual.
	 *
	 * @param float $amount_usd Cantidad en USD.
	 * @param float $amount_ves Cantidad en VES.
	 * @param array $options Opciones de formato.
	 * @return string Formato dual.
	 */
	public function format_dual( $amount_usd, $amount_ves, $options = array() ) {
		$usd_formatted = $this->format_usd( $amount_usd, $options );
		$ves_formatted = $this->format_ves( $amount_ves, $options );
		
		$separator = $options['separator'] ?? ' / ';
		$template = $options['template'] ?? '{usd}{separator}{ves}';
		
		return str_replace(
			array( '{usd}', '{ves}', '{separator}' ),
			array( $usd_formatted, $ves_formatted, $separator ),
			$template
		);
	}

	/**
	 * Formatea número con separadores.
	 *
	 * @param float $amount Cantidad a formatear.
	 * @param array $format_options Opciones de formato.
	 * @return string Número formateado.
	 */
	private function format_number( $amount, $format_options ) {
		$decimals = $format_options['decimals'];
		$thousand_separator = $format_options['thousand_separator'];
		$decimal_separator = $format_options['decimal_separator'];
		
		// Redondear a decimales especificados
		$rounded_amount = round( $amount, $decimals );
		
		// Formatear con separadores
		$formatted = number_format( $rounded_amount, $decimals, $decimal_separator, $thousand_separator );
		
		// Limpiar decimales innecesarios si no se requieren
		if ( $decimals > 0 ) {
			$formatted = rtrim( rtrim( $formatted, '0' ), $decimal_separator );
		}
		
		return $formatted;
	}

	/**
	 * Aplica símbolo de moneda según posición.
	 *
	 * @param string $formatted_number Número formateado.
	 * @param array $format_options Opciones de formato.
	 * @return string Número con símbolo.
	 */
	private function apply_symbol( $formatted_number, $format_options ) {
		$symbol = $format_options['symbol'];
		$position = $format_options['position'];
		$show_code = $format_options['show_currency_code'] ?? false;
		
		// Usar código de moneda si está habilitado
		if ( $show_code ) {
			$symbol = $format_options['currency_code'] ?? $symbol;
		}
		
		if ( $position === 'before' ) {
			return $symbol . $formatted_number;
		} else {
			return $formatted_number . ' ' . $symbol;
		}
	}

	/**
	 * Formatea cantidad en formato compacto (K, M, B).
	 *
	 * @param float $amount Cantidad a formatear.
	 * @param array $format_options Opciones de formato.
	 * @return string Cantidad en formato compacto.
	 */
	private function format_compact( $amount, $format_options ) {
		$abs_amount = abs( $amount );
		$sign = $amount < 0 ? '-' : '';
		
		$suffix = '';
		$divisor = 1;
		
		if ( $abs_amount >= 1000000000 ) {
			$suffix = 'B';
			$divisor = 1000000000;
		} elseif ( $abs_amount >= 1000000 ) {
			$suffix = 'M';
			$divisor = 1000000;
		} elseif ( $abs_amount >= 1000 ) {
			$suffix = 'K';
			$divisor = 1000;
		}
		
		$compact_amount = $amount / $divisor;
		$decimals = $format_options['decimals'];
		
		// Para formato compacto, usar menos decimales
		if ( $compact_amount >= 100 ) {
			$decimals = 0;
		} elseif ( $compact_amount >= 10 ) {
			$decimals = 1;
		} else {
			$decimals = 2;
		}
		
		$formatted_number = number_format( $compact_amount, $decimals, '.', '' );
		$formatted_number = rtrim( rtrim( $formatted_number, '0' ), '.' );
		
		$symbol = $format_options['symbol'];
		$position = $format_options['position'];
		
		if ( $position === 'before' ) {
			return $sign . $symbol . $formatted_number . $suffix;
		} else {
			return $sign . $formatted_number . $suffix . ' ' . $symbol;
		}
	}

	/**
	 * Combina opciones de formato con configuración por defecto.
	 *
	 * @param string $currency Tipo de moneda (usd/ves).
	 * @param array $options Opciones personalizadas.
	 * @return array Opciones combinadas.
	 */
	private function merge_format_options( $currency, $options = array() ) {
		$default_options = array(
			'symbol' => $this->config[ $currency . '_symbol' ],
			'position' => $this->config[ $currency . '_position' ],
			'decimals' => $this->config[ $currency . '_decimals' ],
			'thousand_separator' => $this->config[ $currency . '_thousand_separator' ],
			'decimal_separator' => $this->config[ $currency . '_decimal_separator' ],
			'show_currency_code' => $this->config['show_currency_code'],
			'compact_format' => $this->config['compact_format'],
			'currency_code' => strtoupper( $currency ),
		);
		
		return array_merge( $default_options, $options );
	}

	/**
	 * Aplica formato predefinido.
	 *
	 * @param string $format_name Nombre del formato predefinido.
	 * @return bool True si se aplicó correctamente.
	 */
	public function apply_predefined_format( $format_name ) {
		if ( ! isset( $this->predefined_formats[ $format_name ] ) ) {
			return false;
		}
		
		$format = $this->predefined_formats[ $format_name ];
		
		// Actualizar configuración
		foreach ( $format as $key => $value ) {
			if ( $key !== 'name' ) {
				update_option( 'wvs_converter_' . $key, $value );
				$this->config[ $key ] = $value;
			}
		}
		
		return true;
	}

	/**
	 * Obtiene formatos predefinidos disponibles.
	 *
	 * @return array Formatos predefinidos.
	 */
	public function get_predefined_formats() {
		return $this->predefined_formats;
	}

	/**
	 * Genera CSS personalizado para formato de moneda.
	 *
	 * @param array $options Opciones de estilo.
	 * @return string CSS generado.
	 */
	public function generate_custom_css( $options = array() ) {
		$css_options = wp_parse_args( $options, array(
			'font_size' => '14px',
			'font_weight' => 'normal',
			'color' => '#333333',
			'background_color' => 'transparent',
			'border_radius' => '4px',
			'padding' => '8px 12px',
			'margin' => '0',
			'text_align' => 'left',
			'currency_symbol_color' => '#666666',
			'currency_symbol_weight' => 'bold',
		) );
		
		$css = "
		.wvs-currency-converter {
			font-size: {$css_options['font_size']};
			font-weight: {$css_options['font_weight']};
			color: {$css_options['color']};
			background-color: {$css_options['background_color']};
			border-radius: {$css_options['border_radius']};
			padding: {$css_options['padding']};
			margin: {$css_options['margin']};
			text-align: {$css_options['text_align']};
			display: inline-block;
		}
		
		.wvs-currency-converter .currency-symbol {
			color: {$css_options['currency_symbol_color']};
			font-weight: {$css_options['currency_symbol_weight']};
		}
		
		.wvs-currency-converter .currency-amount {
			font-weight: {$css_options['font_weight']};
		}
		";
		
		return $css;
	}

	/**
	 * Formatea precio con información adicional.
	 *
	 * @param float $amount Cantidad a formatear.
	 * @param string $currency Tipo de moneda.
	 * @param array $options Opciones de formato.
	 * @return string Precio formateado con información adicional.
	 */
	public function format_price_with_info( $amount, $currency, $options = array() ) {
		$formatted_price = $currency === 'USD' ? 
			$this->format_usd( $amount, $options ) : 
			$this->format_ves( $amount, $options );
		
		$info_elements = array();
		
		// Agregar información de tasa si está disponible
		if ( isset( $options['show_rate'] ) && $options['show_rate'] ) {
			$bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
			$rate = $bcv_core->get_current_rate();
			if ( $rate ) {
				$info_elements[] = sprintf( 
					'<span class="rate-info">(Tasa: %s)</span>', 
					number_format( $rate, 2, ',', '.' ) 
				);
			}
		}
		
		// Agregar información de actualización
		if ( isset( $options['show_update_time'] ) && $options['show_update_time'] ) {
			$update_time = get_option( 'wvs_bcv_last_update', '' );
			if ( $update_time ) {
				$info_elements[] = sprintf( 
					'<span class="update-time">(Actualizado: %s)</span>', 
					date( 'H:i', strtotime( $update_time ) ) 
				);
			}
		}
		
		// Agregar información de margen
		if ( isset( $options['show_margin'] ) && $options['show_margin'] && isset( $options['margin_rate'] ) ) {
			$info_elements[] = sprintf( 
				'<span class="margin-info">(Margen: %s%%)</span>', 
				number_format( $options['margin_rate'], 1 ) 
			);
		}
		
		$info_html = ! empty( $info_elements ) ? 
			'<div class="price-info">' . implode( ' ', $info_elements ) . '</div>' : '';
		
		return '<div class="wvs-price-container">' . 
			'<span class="wvs-price">' . $formatted_price . '</span>' . 
			$info_html . 
			'</div>';
	}

	/**
	 * Formatea rango de precios.
	 *
	 * @param float $min_price Precio mínimo.
	 * @param float $max_price Precio máximo.
	 * @param string $currency Tipo de moneda.
	 * @param array $options Opciones de formato.
	 * @return string Rango de precios formateado.
	 */
	public function format_price_range( $min_price, $max_price, $currency, $options = array() ) {
		$min_formatted = $currency === 'USD' ? 
			$this->format_usd( $min_price, $options ) : 
			$this->format_ves( $min_price, $options );
		
		$max_formatted = $currency === 'USD' ? 
			$this->format_usd( $max_price, $options ) : 
			$this->format_ves( $max_price, $options );
		
		$separator = $options['range_separator'] ?? ' - ';
		
		return $min_formatted . $separator . $max_formatted;
	}

	/**
	 * Formatea precio con descuento.
	 *
	 * @param float $original_price Precio original.
	 * @param float $sale_price Precio de venta.
	 * @param string $currency Tipo de moneda.
	 * @param array $options Opciones de formato.
	 * @return string Precio con descuento formateado.
	 */
	public function format_sale_price( $original_price, $sale_price, $currency, $options = array() ) {
		$original_formatted = $currency === 'USD' ? 
			$this->format_usd( $original_price, $options ) : 
			$this->format_ves( $original_price, $options );
		
		$sale_formatted = $currency === 'USD' ? 
			$this->format_usd( $sale_price, $options ) : 
			$this->format_ves( $sale_price, $options );
		
		$discount_percent = round( ( ( $original_price - $sale_price ) / $original_price ) * 100 );
		
		$original_class = $options['original_class'] ?? 'original-price';
		$sale_class = $options['sale_class'] ?? 'sale-price';
		$discount_class = $options['discount_class'] ?? 'discount-percent';
		
		return sprintf(
			'<span class="%s">%s</span> <span class="%s">%s</span> <span class="%s">-%s%%</span>',
			$original_class,
			$original_formatted,
			$sale_class,
			$sale_formatted,
			$discount_class,
			$discount_percent
		);
	}

	/**
	 * Actualiza configuración de formato.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'ves_format',
			'usd_format',
			'ves_symbol',
			'usd_symbol',
			'ves_position',
			'usd_position',
			'ves_decimals',
			'usd_decimals',
			'ves_thousand_separator',
			'usd_thousand_separator',
			'ves_decimal_separator',
			'usd_decimal_separator',
			'show_currency_code',
			'compact_format',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_converter_' . $key, $value );
				$this->config[ $key ] = $value;
			}
		}

		return true;
	}

	/**
	 * Obtiene configuración actual.
	 *
	 * @return array Configuración actual.
	 */
	public function get_config() {
		return $this->config;
	}
}
