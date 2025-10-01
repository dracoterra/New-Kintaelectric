<?php
/**
 * Currency Converter Cache - Sistema de cache avanzado
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sistema de cache avanzado para conversiones de moneda.
 * Implementa cache multi-nivel con invalidación inteligente.
 */
class Woocommerce_Venezuela_Suite_Converter_Cache {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración de cache */
	private $config = array();

	/** @var array Cache en memoria */
	private $memory_cache = array();

	/** @var array Estadísticas de cache */
	private $cache_stats = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->init_cache_stats();
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
	 * Carga configuración de cache.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'cache_enabled' => get_option( 'wvs_converter_cache_enabled', true ),
			'cache_duration' => get_option( 'wvs_converter_cache_duration', 30 * MINUTE_IN_SECONDS ),
			'memory_cache_enabled' => get_option( 'wvs_converter_memory_cache_enabled', true ),
			'memory_cache_limit' => get_option( 'wvs_converter_memory_cache_limit', 1000 ),
			'compression_enabled' => get_option( 'wvs_converter_compression_enabled', true ),
			'preload_enabled' => get_option( 'wvs_converter_preload_enabled', true ),
			'auto_cleanup_enabled' => get_option( 'wvs_converter_auto_cleanup_enabled', true ),
			'cleanup_interval' => get_option( 'wvs_converter_cleanup_interval', 24 * HOUR_IN_SECONDS ),
		);
	}

	/**
	 * Inicializa estadísticas de cache.
	 *
	 * @return void
	 */
	private function init_cache_stats() {
		$this->cache_stats = array(
			'hits' => 0,
			'misses' => 0,
			'sets' => 0,
			'deletes' => 0,
			'compressions' => 0,
			'decompressions' => 0,
			'memory_hits' => 0,
			'transient_hits' => 0,
		);
	}

	/**
	 * Obtiene conversión del cache.
	 *
	 * @param string $cache_key Clave de cache.
	 * @return array|null Datos de conversión o null si no existe.
	 */
	public function get_conversion( $cache_key ) {
		if ( ! $this->config['cache_enabled'] ) {
			return null;
		}

		// Intentar cache en memoria primero
		if ( $this->config['memory_cache_enabled'] && isset( $this->memory_cache[ $cache_key ] ) ) {
			$this->cache_stats['memory_hits']++;
			$this->cache_stats['hits']++;
			return $this->decompress_data( $this->memory_cache[ $cache_key ] );
		}

		// Intentar cache en transients
		$cached_data = get_transient( $cache_key );
		if ( false !== $cached_data ) {
			$this->cache_stats['transient_hits']++;
			$this->cache_stats['hits']++;
			
			// Mover a cache en memoria para acceso más rápido
			if ( $this->config['memory_cache_enabled'] ) {
				$this->add_to_memory_cache( $cache_key, $cached_data );
			}
			
			return $this->decompress_data( $cached_data );
		}

		$this->cache_stats['misses']++;
		return null;
	}

	/**
	 * Guarda conversión en cache.
	 *
	 * @param string $cache_key Clave de cache.
	 * @param array $conversion_data Datos de conversión.
	 * @param int $duration Duración del cache en segundos.
	 * @return bool True si se guardó correctamente.
	 */
	public function set_conversion( $cache_key, $conversion_data, $duration = null ) {
		if ( ! $this->config['cache_enabled'] ) {
			return false;
		}

		$duration = $duration ?? $this->config['cache_duration'];
		$compressed_data = $this->compress_data( $conversion_data );

		// Guardar en cache en memoria
		if ( $this->config['memory_cache_enabled'] ) {
			$this->add_to_memory_cache( $cache_key, $compressed_data );
		}

		// Guardar en transients
		$result = set_transient( $cache_key, $compressed_data, $duration );
		
		if ( $result ) {
			$this->cache_stats['sets']++;
		}

		return $result;
	}

	/**
	 * Elimina conversión del cache.
	 *
	 * @param string $cache_key Clave de cache.
	 * @return bool True si se eliminó correctamente.
	 */
	public function delete_conversion( $cache_key ) {
		$deleted = false;

		// Eliminar de cache en memoria
		if ( isset( $this->memory_cache[ $cache_key ] ) ) {
			unset( $this->memory_cache[ $cache_key ] );
			$deleted = true;
		}

		// Eliminar de transients
		if ( delete_transient( $cache_key ) ) {
			$deleted = true;
		}

		if ( $deleted ) {
			$this->cache_stats['deletes']++;
		}

		return $deleted;
	}

	/**
	 * Genera clave de cache para conversión.
	 *
	 * @param float $amount Cantidad a convertir.
	 * @param string $from_currency Moneda origen.
	 * @param string $to_currency Moneda destino.
	 * @param array $options Opciones de conversión.
	 * @return string Clave de cache.
	 */
	public function generate_cache_key( $amount, $from_currency, $to_currency, $options = array() ) {
		// Normalizar opciones para clave consistente
		$normalized_options = $this->normalize_options( $options );
		
		// Crear hash de las opciones
		$options_hash = md5( serialize( $normalized_options ) );
		
		// Crear clave de cache
		$cache_key = sprintf(
			'wvs_converter_%s_%s_%s_%s_%s',
			$from_currency,
			$to_currency,
			number_format( $amount, 2, '.', '' ),
			$options_hash,
			date( 'Y-m-d-H' ) // Invalidar cada hora
		);
		
		return $cache_key;
	}

	/**
	 * Normaliza opciones para clave de cache consistente.
	 *
	 * @param array $options Opciones a normalizar.
	 * @return array Opciones normalizadas.
	 */
	private function normalize_options( $options ) {
		$normalized = array();
		
		// Solo incluir opciones que afectan la conversión
		$relevant_keys = array(
			'rounding_method',
			'decimal_places',
			'margin_rate',
			'conversion_fee_rate',
			'product_id',
			'category_id',
		);
		
		foreach ( $relevant_keys as $key ) {
			if ( isset( $options[ $key ] ) ) {
				$normalized[ $key ] = $options[ $key ];
			}
		}
		
		// Ordenar para consistencia
		ksort( $normalized );
		
		return $normalized;
	}

	/**
	 * Agrega datos al cache en memoria.
	 *
	 * @param string $cache_key Clave de cache.
	 * @param mixed $data Datos a cachear.
	 * @return void
	 */
	private function add_to_memory_cache( $cache_key, $data ) {
		// Verificar límite de cache en memoria
		if ( count( $this->memory_cache ) >= $this->config['memory_cache_limit'] ) {
			// Eliminar el 20% más antiguo
			$keys_to_remove = array_slice( array_keys( $this->memory_cache ), 0, intval( $this->config['memory_cache_limit'] * 0.2 ) );
			foreach ( $keys_to_remove as $key ) {
				unset( $this->memory_cache[ $key ] );
			}
		}
		
		$this->memory_cache[ $cache_key ] = $data;
	}

	/**
	 * Comprime datos para ahorrar espacio.
	 *
	 * @param array $data Datos a comprimir.
	 * @return string Datos comprimidos.
	 */
	private function compress_data( $data ) {
		if ( ! $this->config['compression_enabled'] ) {
			return $data;
		}
		
		$serialized = serialize( $data );
		
		// Solo comprimir si los datos son suficientemente grandes
		if ( strlen( $serialized ) > 1000 ) {
			$compressed = gzcompress( $serialized );
			if ( false !== $compressed ) {
				$this->cache_stats['compressions']++;
				return base64_encode( $compressed );
			}
		}
		
		return $serialized;
	}

	/**
	 * Descomprime datos.
	 *
	 * @param string $compressed_data Datos comprimidos.
	 * @return array Datos descomprimidos.
	 */
	private function decompress_data( $compressed_data ) {
		if ( ! $this->config['compression_enabled'] ) {
			return unserialize( $compressed_data );
		}
		
		// Intentar descomprimir
		$decoded = base64_decode( $compressed_data );
		if ( false !== $decoded ) {
			$decompressed = gzuncompress( $decoded );
			if ( false !== $decompressed ) {
				$this->cache_stats['decompressions']++;
				return unserialize( $decompressed );
			}
		}
		
		// Fallback a deserialización directa
		return unserialize( $compressed_data );
	}

	/**
	 * Pre-carga conversiones comunes.
	 *
	 * @return void
	 */
	public function preload_common_conversions() {
		if ( ! $this->config['preload_enabled'] ) {
			return;
		}
		
		$common_amounts = array( 1, 5, 10, 25, 50, 100, 250, 500, 1000 );
		$bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$calculator = Woocommerce_Venezuela_Suite_Converter_Calculator::get_instance();
		
		foreach ( $common_amounts as $amount ) {
			$cache_key = $this->generate_cache_key( $amount, 'USD', 'VES', array() );
			
			// Solo pre-cargar si no está en cache
			if ( null === $this->get_conversion( $cache_key ) ) {
				$conversion = $calculator->convert_usd_to_ves( $amount );
				if ( $conversion['success'] ) {
					$this->set_conversion( $cache_key, $conversion );
				}
			}
		}
	}

	/**
	 * Limpia cache expirado.
	 *
	 * @return int Número de entradas eliminadas.
	 */
	public function cleanup_expired_cache() {
		if ( ! $this->config['auto_cleanup_enabled'] ) {
			return 0;
		}
		
		$deleted_count = 0;
		
		// Limpiar cache en memoria
		foreach ( $this->memory_cache as $key => $data ) {
			// Verificar si el transient correspondiente existe
			if ( false === get_transient( $key ) ) {
				unset( $this->memory_cache[ $key ] );
				$deleted_count++;
			}
		}
		
		// Limpiar transients expirados (WordPress lo hace automáticamente)
		// Pero podemos limpiar manualmente si es necesario
		
		return $deleted_count;
	}

	/**
	 * Limpia todo el cache.
	 *
	 * @return bool True si se limpió correctamente.
	 */
	public function clear_all_cache() {
		// Limpiar cache en memoria
		$this->memory_cache = array();
		
		// Limpiar transients relacionados
		global $wpdb;
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_wvs_converter_%'
			)
		);
		
		// Limpiar transients timeout
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_timeout_wvs_converter_%'
			)
		);
		
		return $deleted !== false;
	}

	/**
	 * Obtiene estadísticas de cache.
	 *
	 * @return array Estadísticas de cache.
	 */
	public function get_cache_stats() {
		$total_requests = $this->cache_stats['hits'] + $this->cache_stats['misses'];
		$hit_rate = $total_requests > 0 ? ( $this->cache_stats['hits'] / $total_requests ) * 100 : 0;
		
		return array_merge( $this->cache_stats, array(
			'total_requests' => $total_requests,
			'hit_rate' => round( $hit_rate, 2 ),
			'memory_cache_size' => count( $this->memory_cache ),
			'memory_cache_limit' => $this->config['memory_cache_limit'],
			'compression_ratio' => $this->cache_stats['compressions'] > 0 ? 
				round( $this->cache_stats['decompressions'] / $this->cache_stats['compressions'], 2 ) : 0,
		) );
	}

	/**
	 * Resetea estadísticas de cache.
	 *
	 * @return void
	 */
	public function reset_cache_stats() {
		$this->init_cache_stats();
	}

	/**
	 * Verifica si el cache está funcionando correctamente.
	 *
	 * @return array Estado del cache.
	 */
	public function get_cache_health() {
		$health = array(
			'status' => 'healthy',
			'issues' => array(),
			'recommendations' => array(),
		);
		
		// Verificar configuración
		if ( ! $this->config['cache_enabled'] ) {
			$health['status'] = 'disabled';
			$health['issues'][] = 'Cache está deshabilitado';
		}
		
		// Verificar hit rate
		$stats = $this->get_cache_stats();
		if ( $stats['hit_rate'] < 50 && $stats['total_requests'] > 100 ) {
			$health['status'] = 'warning';
			$health['issues'][] = 'Hit rate bajo: ' . $stats['hit_rate'] . '%';
			$health['recommendations'][] = 'Considerar aumentar la duración del cache';
		}
		
		// Verificar uso de memoria
		$memory_usage = count( $this->memory_cache ) / $this->config['memory_cache_limit'];
		if ( $memory_usage > 0.9 ) {
			$health['status'] = 'warning';
			$health['issues'][] = 'Cache en memoria casi lleno: ' . round( $memory_usage * 100 ) . '%';
			$health['recommendations'][] = 'Considerar aumentar el límite de cache en memoria';
		}
		
		// Verificar compresión
		if ( $this->config['compression_enabled'] && $stats['compressions'] > 0 ) {
			$compression_ratio = $stats['decompressions'] / $stats['compressions'];
			if ( $compression_ratio < 0.8 ) {
				$health['recommendations'][] = 'Considerar deshabilitar compresión para mejor rendimiento';
			}
		}
		
		return $health;
	}

	/**
	 * Programa limpieza automática de cache.
	 *
	 * @return void
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'wvs_converter_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'wvs_converter_cache_cleanup' );
		}
	}

	/**
	 * Cancela limpieza automática de cache.
	 *
	 * @return void
	 */
	public function unschedule_cleanup() {
		wp_clear_scheduled_hook( 'wvs_converter_cache_cleanup' );
	}

	/**
	 * Actualiza configuración de cache.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'cache_enabled',
			'cache_duration',
			'memory_cache_enabled',
			'memory_cache_limit',
			'compression_enabled',
			'preload_enabled',
			'auto_cleanup_enabled',
			'cleanup_interval',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_converter_' . $key, $value );
				$this->config[ $key ] = $value;
			}
		}

		// Re-programar limpieza si cambió la configuración
		if ( isset( $config['auto_cleanup_enabled'] ) ) {
			if ( $config['auto_cleanup_enabled'] ) {
				$this->schedule_cleanup();
			} else {
				$this->unschedule_cleanup();
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
