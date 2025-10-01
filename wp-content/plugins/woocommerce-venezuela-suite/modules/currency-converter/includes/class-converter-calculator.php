<?php
/**
 * Currency Converter Calculator - Calculadora de conversiones avanzada
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculadora avanzada para conversiones de moneda USD ↔ VES.
 * Implementa algoritmos inteligentes de conversión y redondeo.
 */
class Woocommerce_Venezuela_Suite_Converter_Calculator {

	/** @var self */
	private static $instance = null;

	/** @var Woocommerce_Venezuela_Suite_BCV_Integration_Core */
	private $bcv_core;

	/** @var array Configuración de cálculo */
	private $config = array();

	/** @var array Historial de conversiones */
	private $conversion_history = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$this->load_config();
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
	 * Carga configuración de cálculo.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'rounding_method' => get_option( 'wvs_converter_rounding_method', 'smart' ),
			'decimal_places' => get_option( 'wvs_converter_decimal_places', 2 ),
			'margin_enabled' => get_option( 'wvs_converter_margin_enabled', false ),
			'default_margin' => get_option( 'wvs_converter_default_margin', 0 ), // %
			'min_conversion_amount' => get_option( 'wvs_converter_min_amount', 0.01 ),
			'max_conversion_amount' => get_option( 'wvs_converter_max_amount', 1000000 ),
			'conversion_fee_enabled' => get_option( 'wvs_converter_fee_enabled', false ),
			'conversion_fee_rate' => get_option( 'wvs_converter_fee_rate', 0 ), // %
		);
	}

	/**
	 * Convierte USD a VES con algoritmo inteligente.
	 *
	 * @param float $amount_usd Cantidad en USD.
	 * @param array $options Opciones de conversión.
	 * @return array Resultado de la conversión.
	 */
	public function convert_usd_to_ves( $amount_usd, $options = array() ) {
		$start_time = microtime( true );
		
		// Validar entrada
		$amount_usd = floatval( $amount_usd );
		if ( $amount_usd <= 0 ) {
			return $this->create_error_result( 'Invalid amount', $amount_usd );
		}

		// Verificar límites
		if ( $amount_usd < $this->config['min_conversion_amount'] || $amount_usd > $this->config['max_conversion_amount'] ) {
			return $this->create_error_result( 'Amount out of limits', $amount_usd );
		}

		// Obtener tasa BCV
		$bcv_rate = $this->bcv_core->get_current_rate();
		if ( ! $bcv_rate ) {
			return $this->create_error_result( 'BCV rate not available', $amount_usd );
		}

		// Aplicar margen si está habilitado
		$margin_rate = $this->get_margin_rate( $options );
		$effective_rate = $bcv_rate * ( 1 + $margin_rate / 100 );

		// Calcular conversión base
		$amount_ves_raw = $amount_usd * $effective_rate;

		// Aplicar redondeo inteligente
		$amount_ves_rounded = $this->apply_smart_rounding( $amount_ves_raw, $options );

		// Aplicar comisión si está habilitada
		$fee_amount = 0;
		if ( $this->config['conversion_fee_enabled'] && $this->config['conversion_fee_rate'] > 0 ) {
			$fee_amount = $amount_ves_rounded * ( $this->config['conversion_fee_rate'] / 100 );
			$amount_ves_rounded += $fee_amount;
		}

		// Registrar conversión
		$conversion_time = microtime( true ) - $start_time;
		$this->record_conversion( array(
			'from' => 'USD',
			'to' => 'VES',
			'amount_from' => $amount_usd,
			'amount_to' => $amount_ves_rounded,
			'rate' => $bcv_rate,
			'effective_rate' => $effective_rate,
			'margin_rate' => $margin_rate,
			'fee_amount' => $fee_amount,
			'processing_time' => $conversion_time,
			'timestamp' => current_time( 'mysql' ),
		) );

		return array(
			'success' => true,
			'amount_usd' => $amount_usd,
			'amount_ves' => $amount_ves_rounded,
			'bcv_rate' => $bcv_rate,
			'effective_rate' => $effective_rate,
			'margin_rate' => $margin_rate,
			'fee_amount' => $fee_amount,
			'rounding_method' => $this->get_rounding_method_used( $amount_ves_raw, $amount_ves_rounded ),
			'processing_time' => $conversion_time,
			'timestamp' => current_time( 'mysql' ),
		);
	}

	/**
	 * Convierte VES a USD con algoritmo inteligente.
	 *
	 * @param float $amount_ves Cantidad en VES.
	 * @param array $options Opciones de conversión.
	 * @return array Resultado de la conversión.
	 */
	public function convert_ves_to_usd( $amount_ves, $options = array() ) {
		$start_time = microtime( true );
		
		// Validar entrada
		$amount_ves = floatval( $amount_ves );
		if ( $amount_ves <= 0 ) {
			return $this->create_error_result( 'Invalid amount', $amount_ves );
		}

		// Obtener tasa BCV
		$bcv_rate = $this->bcv_core->get_current_rate();
		if ( ! $bcv_rate ) {
			return $this->create_error_result( 'BCV rate not available', $amount_ves );
		}

		// Aplicar margen si está habilitado (inverso para VES→USD)
		$margin_rate = $this->get_margin_rate( $options );
		$effective_rate = $bcv_rate * ( 1 - $margin_rate / 100 );

		// Calcular conversión base
		$amount_usd_raw = $amount_ves / $effective_rate;

		// Aplicar redondeo inteligente
		$amount_usd_rounded = $this->apply_smart_rounding( $amount_usd_raw, $options );

		// Aplicar comisión si está habilitada
		$fee_amount = 0;
		if ( $this->config['conversion_fee_enabled'] && $this->config['conversion_fee_rate'] > 0 ) {
			$fee_amount = $amount_usd_rounded * ( $this->config['conversion_fee_rate'] / 100 );
			$amount_usd_rounded += $fee_amount;
		}

		// Registrar conversión
		$conversion_time = microtime( true ) - $start_time;
		$this->record_conversion( array(
			'from' => 'VES',
			'to' => 'USD',
			'amount_from' => $amount_ves,
			'amount_to' => $amount_usd_rounded,
			'rate' => $bcv_rate,
			'effective_rate' => $effective_rate,
			'margin_rate' => $margin_rate,
			'fee_amount' => $fee_amount,
			'processing_time' => $conversion_time,
			'timestamp' => current_time( 'mysql' ),
		) );

		return array(
			'success' => true,
			'amount_ves' => $amount_ves,
			'amount_usd' => $amount_usd_rounded,
			'bcv_rate' => $bcv_rate,
			'effective_rate' => $effective_rate,
			'margin_rate' => $margin_rate,
			'fee_amount' => $fee_amount,
			'rounding_method' => $this->get_rounding_method_used( $amount_usd_raw, $amount_usd_rounded ),
			'processing_time' => $conversion_time,
			'timestamp' => current_time( 'mysql' ),
		);
	}

	/**
	 * Convierte múltiples cantidades en lote.
	 *
	 * @param array $amounts Array de cantidades a convertir.
	 * @param string $from_currency Moneda origen.
	 * @param string $to_currency Moneda destino.
	 * @param array $options Opciones de conversión.
	 * @return array Resultados de conversión.
	 */
	public function convert_batch( $amounts, $from_currency, $to_currency, $options = array() ) {
		$results = array();
		$total_time = 0;

		foreach ( $amounts as $index => $amount ) {
			$start_time = microtime( true );
			
			if ( $from_currency === 'USD' && $to_currency === 'VES' ) {
				$result = $this->convert_usd_to_ves( $amount, $options );
			} elseif ( $from_currency === 'VES' && $to_currency === 'USD' ) {
				$result = $this->convert_ves_to_usd( $amount, $options );
			} else {
				$result = $this->create_error_result( 'Unsupported currency pair', $amount );
			}

			$result['batch_index'] = $index;
			$result['batch_total'] = count( $amounts );
			$results[] = $result;
			
			$total_time += microtime( true ) - $start_time;
		}

		return array(
			'results' => $results,
			'total_conversions' => count( $amounts ),
			'successful_conversions' => count( array_filter( $results, function( $r ) { return $r['success']; } ) ),
			'total_processing_time' => $total_time,
			'average_processing_time' => $total_time / count( $amounts ),
		);
	}

	/**
	 * Aplica redondeo inteligente según configuración.
	 *
	 * @param float $amount Cantidad a redondear.
	 * @param array $options Opciones de redondeo.
	 * @return float Cantidad redondeada.
	 */
	private function apply_smart_rounding( $amount, $options = array() ) {
		$rounding_method = $options['rounding_method'] ?? $this->config['rounding_method'];
		$decimal_places = $options['decimal_places'] ?? $this->config['decimal_places'];

		switch ( $rounding_method ) {
			case 'smart':
				return $this->smart_round( $amount, $decimal_places );
			case 'round':
				return round( $amount, $decimal_places );
			case 'floor':
				return floor( $amount * pow( 10, $decimal_places ) ) / pow( 10, $decimal_places );
			case 'ceil':
				return ceil( $amount * pow( 10, $decimal_places ) ) / pow( 10, $decimal_places );
			case 'truncate':
				return intval( $amount * pow( 10, $decimal_places ) ) / pow( 10, $decimal_places );
			default:
				return round( $amount, $decimal_places );
		}
	}

	/**
	 * Redondeo inteligente para VES (evita decimales excesivos).
	 *
	 * @param float $amount Cantidad a redondear.
	 * @param int $decimal_places Decimales deseados.
	 * @return float Cantidad redondeada inteligentemente.
	 */
	private function smart_round( $amount, $decimal_places = 2 ) {
		// Para VES, usar redondeo más agresivo en cantidades grandes
		if ( $amount > 1000 ) {
			$decimal_places = max( 0, $decimal_places - 1 );
		}
		if ( $amount > 10000 ) {
			$decimal_places = max( 0, $decimal_places - 1 );
		}

		$rounded = round( $amount, $decimal_places );
		
		// Eliminar decimales innecesarios
		if ( $rounded == intval( $rounded ) ) {
			return intval( $rounded );
		}

		return $rounded;
	}

	/**
	 * Obtiene la tasa de margen aplicable.
	 *
	 * @param array $options Opciones de conversión.
	 * @return float Tasa de margen en porcentaje.
	 */
	private function get_margin_rate( $options = array() ) {
		if ( ! $this->config['margin_enabled'] ) {
			return 0;
		}

		// Margen específico en opciones
		if ( isset( $options['margin_rate'] ) ) {
			return floatval( $options['margin_rate'] );
		}

		// Margen por producto
		if ( isset( $options['product_id'] ) ) {
			$product_margin = get_post_meta( $options['product_id'], '_wvs_converter_margin', true );
			if ( $product_margin !== '' ) {
				return floatval( $product_margin );
			}
		}

		// Margen por categoría
		if ( isset( $options['category_id'] ) ) {
			$category_margin = get_term_meta( $options['category_id'], 'wvs_converter_margin', true );
			if ( $category_margin !== '' ) {
				return floatval( $category_margin );
			}
		}

		// Margen por usuario/rol
		if ( is_user_logged_in() ) {
			$user_role = $this->get_user_role();
			$role_margin = get_option( "wvs_converter_margin_role_{$user_role}", '' );
			if ( $role_margin !== '' ) {
				return floatval( $role_margin );
			}
		}

		// Margen por ubicación geográfica
		$location_margin = $this->get_location_margin();
		if ( $location_margin !== null ) {
			return $location_margin;
		}

		// Margen por defecto
		return $this->config['default_margin'];
	}

	/**
	 * Obtiene el rol del usuario actual.
	 *
	 * @return string Rol del usuario.
	 */
	private function get_user_role() {
		if ( ! is_user_logged_in() ) {
			return 'guest';
		}

		$user = wp_get_current_user();
		return $user->roles[0] ?? 'subscriber';
	}

	/**
	 * Obtiene margen basado en ubicación geográfica.
	 *
	 * @return float|null Margen de ubicación o null.
	 */
	private function get_location_margin() {
		// Detectar ubicación por IP (simplificado)
		$country_code = $this->detect_country_by_ip();
		
		if ( $country_code === 'VE' ) {
			return get_option( 'wvs_converter_margin_venezuela', null );
		}

		return null;
	}

	/**
	 * Detecta país por IP (simplificado).
	 *
	 * @return string Código de país.
	 */
	private function detect_country_by_ip() {
		// Implementación simplificada - en producción usar servicio real
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		
		// IPs de prueba para Venezuela
		$venezuela_ips = array( '190.169.0.0/16', '201.249.0.0/16' );
		
		foreach ( $venezuela_ips as $range ) {
			if ( $this->ip_in_range( $ip, $range ) ) {
				return 'VE';
			}
		}

		return 'US'; // Por defecto
	}

	/**
	 * Verifica si IP está en rango.
	 *
	 * @param string $ip IP a verificar.
	 * @param string $range Rango CIDR.
	 * @return bool True si está en rango.
	 */
	private function ip_in_range( $ip, $range ) {
		// Implementación simplificada
		return strpos( $ip, '190.169.' ) === 0 || strpos( $ip, '201.249.' ) === 0;
	}

	/**
	 * Registra conversión en historial.
	 *
	 * @param array $conversion_data Datos de la conversión.
	 * @return void
	 */
	private function record_conversion( $conversion_data ) {
		$this->conversion_history[] = $conversion_data;
		
		// Mantener solo las últimas 1000 conversiones
		if ( count( $this->conversion_history ) > 1000 ) {
			$this->conversion_history = array_slice( $this->conversion_history, -1000 );
		}

		// Guardar en base de datos periódicamente
		if ( count( $this->conversion_history ) % 100 === 0 ) {
			$this->save_conversion_history();
		}
	}

	/**
	 * Guarda historial de conversiones en base de datos.
	 *
	 * @return void
	 */
	private function save_conversion_history() {
		$history_data = get_option( 'wvs_converter_history', array() );
		$history_data = array_merge( $history_data, $this->conversion_history );
		
		// Mantener solo las últimas 5000 conversiones en BD
		if ( count( $history_data ) > 5000 ) {
			$history_data = array_slice( $history_data, -5000 );
		}
		
		update_option( 'wvs_converter_history', $history_data );
		$this->conversion_history = array();
	}

	/**
	 * Crea resultado de error.
	 *
	 * @param string $error_message Mensaje de error.
	 * @param float $amount Cantidad que causó el error.
	 * @return array Resultado de error.
	 */
	private function create_error_result( $error_message, $amount ) {
		return array(
			'success' => false,
			'error' => $error_message,
			'amount' => $amount,
			'timestamp' => current_time( 'mysql' ),
		);
	}

	/**
	 * Determina el método de redondeo usado.
	 *
	 * @param float $original Cantidad original.
	 * @param float $rounded Cantidad redondeada.
	 * @return string Método de redondeo usado.
	 */
	private function get_rounding_method_used( $original, $rounded ) {
		$difference = abs( $original - $rounded );
		
		if ( $difference < 0.001 ) {
			return 'none';
		} elseif ( $rounded > $original ) {
			return 'ceil';
		} elseif ( $rounded < $original ) {
			return 'floor';
		} else {
			return 'round';
		}
	}

	/**
	 * Obtiene estadísticas de conversión.
	 *
	 * @param int $days Días de estadísticas.
	 * @return array Estadísticas de conversión.
	 */
	public function get_conversion_statistics( $days = 30 ) {
		$history = get_option( 'wvs_converter_history', array() );
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );
		
		$recent_conversions = array_filter( $history, function( $conversion ) use ( $cutoff_date ) {
			return $conversion['timestamp'] >= $cutoff_date;
		});

		$stats = array(
			'total_conversions' => count( $recent_conversions ),
			'usd_to_ves_conversions' => 0,
			'ves_to_usd_conversions' => 0,
			'total_usd_converted' => 0,
			'total_ves_converted' => 0,
			'average_processing_time' => 0,
			'conversion_accuracy' => 0,
		);

		$processing_times = array();
		$successful_conversions = 0;

		foreach ( $recent_conversions as $conversion ) {
			if ( $conversion['from'] === 'USD' && $conversion['to'] === 'VES' ) {
				$stats['usd_to_ves_conversions']++;
				$stats['total_usd_converted'] += $conversion['amount_from'];
			} elseif ( $conversion['from'] === 'VES' && $conversion['to'] === 'USD' ) {
				$stats['ves_to_usd_conversions']++;
				$stats['total_ves_converted'] += $conversion['amount_from'];
			}

			$processing_times[] = $conversion['processing_time'];
			$successful_conversions++;
		}

		if ( ! empty( $processing_times ) ) {
			$stats['average_processing_time'] = array_sum( $processing_times ) / count( $processing_times );
		}

		$stats['conversion_accuracy'] = $successful_conversions / max( 1, count( $recent_conversions ) ) * 100;

		return $stats;
	}

	/**
	 * Obtiene historial de conversiones.
	 *
	 * @param int $limit Límite de conversiones.
	 * @return array Historial de conversiones.
	 */
	public function get_conversion_history( $limit = 100 ) {
		$history = get_option( 'wvs_converter_history', array() );
		return array_slice( $history, -$limit );
	}

	/**
	 * Actualiza configuración de cálculo.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'rounding_method',
			'decimal_places',
			'margin_enabled',
			'default_margin',
			'min_conversion_amount',
			'max_conversion_amount',
			'conversion_fee_enabled',
			'conversion_fee_rate',
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
