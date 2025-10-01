<?php
/**
 * BCV Cache Manager - Gestor de cache avanzado
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestor de cache avanzado para el sistema BCV Integration.
 * Implementa cache multi-nivel con estrategias inteligentes.
 */
class Woocommerce_Venezuela_Suite_BCV_Cache_Manager {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración del cache */
	private $config = array();

	/** @var array Estadísticas del cache */
	private $stats = array();

	/** @var array Cache en memoria */
	private $memory_cache = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->load_stats();
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
	 * Carga configuración del cache.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'enabled' => get_option( 'wvs_bcv_cache_enabled', true ),
			'default_ttl' => get_option( 'wvs_bcv_cache_default_ttl', 30 ), // minutos
			'memory_cache_enabled' => get_option( 'wvs_bcv_memory_cache_enabled', true ),
			'memory_cache_size' => get_option( 'wvs_bcv_memory_cache_size', 100 ), // elementos
			'compression_enabled' => get_option( 'wvs_bcv_cache_compression', true ),
			'preload_enabled' => get_option( 'wvs_bcv_cache_preload', false ),
		);
	}

	/**
	 * Carga estadísticas del cache.
	 *
	 * @return void
	 */
	private function load_stats() {
		$this->stats = get_option( 'wvs_bcv_cache_stats', array(
			'hits' => 0,
			'misses' => 0,
			'sets' => 0,
			'deletes' => 0,
			'compressions' => 0,
			'last_reset' => current_time( 'mysql' ),
		) );
	}

	/**
	 * Obtiene un valor del cache.
	 *
	 * @param string $key Clave del cache.
	 * @param string $group Grupo del cache.
	 * @return mixed Valor del cache o false si no existe.
	 */
	public function get( $key, $group = 'default' ) {
		if ( ! $this->config['enabled'] ) {
			return false;
		}

		$cache_key = $this->build_cache_key( $key, $group );
		
		// Intentar obtener de cache en memoria primero
		if ( $this->config['memory_cache_enabled'] && isset( $this->memory_cache[ $cache_key ] ) ) {
			$this->record_hit( 'memory' );
			return $this->decompress_value( $this->memory_cache[ $cache_key ]['value'] );
		}

		// Intentar obtener de WordPress transients
		$value = get_transient( $cache_key );
		if ( false !== $value ) {
			$this->record_hit( 'transient' );
			
			// Almacenar en cache en memoria si está habilitado
			if ( $this->config['memory_cache_enabled'] ) {
				$this->store_in_memory_cache( $cache_key, $value );
			}
			
			return $this->decompress_value( $value );
		}

		$this->record_miss();
		return false;
	}

	/**
	 * Almacena un valor en el cache.
	 *
	 * @param string $key Clave del cache.
	 * @param mixed $value Valor a almacenar.
	 * @param string $group Grupo del cache.
	 * @param int $ttl Tiempo de vida en segundos.
	 * @return bool True si se almacenó correctamente.
	 */
	public function set( $key, $value, $group = 'default', $ttl = null ) {
		if ( ! $this->config['enabled'] ) {
			return false;
		}

		$cache_key = $this->build_cache_key( $key, $group );
		$ttl = $ttl ?: $this->config['default_ttl'] * MINUTE_IN_SECONDS;
		
		// Comprimir valor si está habilitado
		$compressed_value = $this->compress_value( $value );
		
		// Almacenar en WordPress transients
		$result = set_transient( $cache_key, $compressed_value, $ttl );
		
		if ( $result ) {
			$this->record_set();
			
			// Almacenar en cache en memoria si está habilitado
			if ( $this->config['memory_cache_enabled'] ) {
				$this->store_in_memory_cache( $cache_key, $compressed_value, $ttl );
			}
		}
		
		return $result;
	}

	/**
	 * Elimina un valor del cache.
	 *
	 * @param string $key Clave del cache.
	 * @param string $group Grupo del cache.
	 * @return bool True si se eliminó correctamente.
	 */
	public function delete( $key, $group = 'default' ) {
		if ( ! $this->config['enabled'] ) {
			return false;
		}

		$cache_key = $this->build_cache_key( $key, $group );
		
		// Eliminar de WordPress transients
		$result = delete_transient( $cache_key );
		
		// Eliminar de cache en memoria
		if ( $this->config['memory_cache_enabled'] && isset( $this->memory_cache[ $cache_key ] ) ) {
			unset( $this->memory_cache[ $cache_key ] );
		}
		
		if ( $result ) {
			$this->record_delete();
		}
		
		return $result;
	}

	/**
	 * Limpia todo el cache de un grupo.
	 *
	 * @param string $group Grupo del cache.
	 * @return bool True si se limpió correctamente.
	 */
	public function flush_group( $group = 'default' ) {
		if ( ! $this->config['enabled'] ) {
			return false;
		}

		$pattern = $this->build_cache_key( '*', $group );
		$deleted_count = 0;
		
		// Eliminar de WordPress transients
		global $wpdb;
		$transients = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
				'_transient_' . $pattern
			)
		);
		
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient->option_name );
			if ( delete_transient( $transient_name ) ) {
				$deleted_count++;
			}
		}
		
		// Eliminar de cache en memoria
		if ( $this->config['memory_cache_enabled'] ) {
			foreach ( $this->memory_cache as $key => $data ) {
				if ( strpos( $key, $group ) !== false ) {
					unset( $this->memory_cache[ $key ] );
				}
			}
		}
		
		return $deleted_count > 0;
	}

	/**
	 * Limpia todo el cache.
	 *
	 * @return bool True si se limpió correctamente.
	 */
	public function flush_all() {
		if ( ! $this->config['enabled'] ) {
			return false;
		}

		// Limpiar cache en memoria
		if ( $this->config['memory_cache_enabled'] ) {
			$this->memory_cache = array();
		}
		
		// Limpiar transients relacionados con BCV
		global $wpdb;
		$transients = $wpdb->get_results(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_wvs_bcv_%'"
		);
		
		$deleted_count = 0;
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient->option_name );
			if ( delete_transient( $transient_name ) ) {
				$deleted_count++;
			}
		}
		
		return $deleted_count > 0;
	}

	/**
	 * Construye la clave del cache.
	 *
	 * @param string $key Clave base.
	 * @param string $group Grupo del cache.
	 * @return string Clave completa del cache.
	 */
	private function build_cache_key( $key, $group ) {
		return "wvs_bcv_{$group}_{$key}";
	}

	/**
	 * Almacena valor en cache en memoria.
	 *
	 * @param string $cache_key Clave del cache.
	 * @param mixed $value Valor a almacenar.
	 * @param int $ttl Tiempo de vida.
	 * @return void
	 */
	private function store_in_memory_cache( $cache_key, $value, $ttl = null ) {
		// Verificar límite de tamaño
		if ( count( $this->memory_cache ) >= $this->config['memory_cache_size'] ) {
			// Eliminar el elemento más antiguo
			$oldest_key = array_key_first( $this->memory_cache );
			unset( $this->memory_cache[ $oldest_key ] );
		}
		
		$this->memory_cache[ $cache_key ] = array(
			'value' => $value,
			'expires' => $ttl ? time() + $ttl : null,
		);
	}

	/**
	 * Comprime un valor si está habilitado.
	 *
	 * @param mixed $value Valor a comprimir.
	 * @return mixed Valor comprimido o original.
	 */
	private function compress_value( $value ) {
		if ( ! $this->config['compression_enabled'] ) {
			return $value;
		}
		
		// Solo comprimir strings largos
		if ( is_string( $value ) && strlen( $value ) > 1000 ) {
			$compressed = gzcompress( $value, 6 );
			if ( $compressed !== false ) {
				$this->stats['compressions']++;
				return 'gz:' . base64_encode( $compressed );
			}
		}
		
		return $value;
	}

	/**
	 * Descomprime un valor si está comprimido.
	 *
	 * @param mixed $value Valor a descomprimir.
	 * @return mixed Valor descomprimido o original.
	 */
	private function decompress_value( $value ) {
		if ( ! is_string( $value ) || ! str_starts_with( $value, 'gz:' ) ) {
			return $value;
		}
		
		$compressed = base64_decode( substr( $value, 3 ) );
		if ( $compressed !== false ) {
			$decompressed = gzuncompress( $compressed );
			if ( $decompressed !== false ) {
				return $decompressed;
			}
		}
		
		return $value;
	}

	/**
	 * Registra un hit del cache.
	 *
	 * @param string $type Tipo de hit (memory, transient).
	 * @return void
	 */
	private function record_hit( $type = 'transient' ) {
		$this->stats['hits']++;
		$this->save_stats();
	}

	/**
	 * Registra un miss del cache.
	 *
	 * @return void
	 */
	private function record_miss() {
		$this->stats['misses']++;
		$this->save_stats();
	}

	/**
	 * Registra una operación de set.
	 *
	 * @return void
	 */
	private function record_set() {
		$this->stats['sets']++;
		$this->save_stats();
	}

	/**
	 * Registra una operación de delete.
	 *
	 * @return void
	 */
	private function record_delete() {
		$this->stats['deletes']++;
		$this->save_stats();
	}

	/**
	 * Guarda estadísticas en la base de datos.
	 *
	 * @return void
	 */
	private function save_stats() {
		update_option( 'wvs_bcv_cache_stats', $this->stats );
	}

	/**
	 * Obtiene estadísticas del cache.
	 *
	 * @return array Estadísticas del cache.
	 */
	public function get_stats() {
		$total_requests = $this->stats['hits'] + $this->stats['misses'];
		$hit_rate = $total_requests > 0 ? ( $this->stats['hits'] / $total_requests ) * 100 : 0;
		
		return array(
			'hits' => $this->stats['hits'],
			'misses' => $this->stats['misses'],
			'sets' => $this->stats['sets'],
			'deletes' => $this->stats['deletes'],
			'compressions' => $this->stats['compressions'],
			'hit_rate' => round( $hit_rate, 2 ),
			'total_requests' => $total_requests,
			'memory_cache_size' => count( $this->memory_cache ),
			'last_reset' => $this->stats['last_reset'],
		);
	}

	/**
	 * Resetea estadísticas del cache.
	 *
	 * @return void
	 */
	public function reset_stats() {
		$this->stats = array(
			'hits' => 0,
			'misses' => 0,
			'sets' => 0,
			'deletes' => 0,
			'compressions' => 0,
			'last_reset' => current_time( 'mysql' ),
		);
		$this->save_stats();
	}

	/**
	 * Pre-carga datos importantes en el cache.
	 *
	 * @return void
	 */
	public function preload_cache() {
		if ( ! $this->config['preload_enabled'] ) {
			return;
		}

		$preload_data = array(
			'current_rate' => 'rates',
			'rate_history' => 'rates',
			'statistics' => 'analytics',
			'predictions' => 'analytics',
		);

		foreach ( $preload_data as $key => $group ) {
			// Solo pre-cargar si no existe en cache
			if ( $this->get( $key, $group ) === false ) {
				// Aquí se cargarían los datos desde las fuentes originales
				// Por ahora, solo marcamos que se intentó pre-cargar
				error_log( "WVS Cache Manager: Pre-cargando $key en grupo $group" );
			}
		}
	}

	/**
	 * Optimiza el cache eliminando elementos expirados.
	 *
	 * @return int Número de elementos eliminados.
	 */
	public function optimize_cache() {
		$deleted_count = 0;
		
		// Limpiar cache en memoria
		if ( $this->config['memory_cache_enabled'] ) {
			foreach ( $this->memory_cache as $key => $data ) {
				if ( $data['expires'] && time() > $data['expires'] ) {
					unset( $this->memory_cache[ $key ] );
					$deleted_count++;
				}
			}
		}
		
		// Limpiar transients expirados relacionados con BCV
		global $wpdb;
		$expired_transients = $wpdb->get_results(
			"SELECT option_name FROM $wpdb->options 
			 WHERE option_name LIKE '_transient_timeout_wvs_bcv_%' 
			 AND option_value < UNIX_TIMESTAMP()"
		);
		
		foreach ( $expired_transients as $transient ) {
			$transient_name = str_replace( '_transient_timeout_', '', $transient->option_name );
			if ( delete_transient( $transient_name ) ) {
				$deleted_count++;
			}
		}
		
		return $deleted_count;
	}

	/**
	 * Obtiene información del cache en memoria.
	 *
	 * @return array Información del cache en memoria.
	 */
	public function get_memory_cache_info() {
		$info = array(
			'enabled' => $this->config['memory_cache_enabled'],
			'size' => count( $this->memory_cache ),
			'max_size' => $this->config['memory_cache_size'],
			'usage_percentage' => 0,
			'keys' => array(),
		);
		
		if ( $this->config['memory_cache_enabled'] ) {
			$info['usage_percentage'] = ( count( $this->memory_cache ) / $this->config['memory_cache_size'] ) * 100;
			$info['keys'] = array_keys( $this->memory_cache );
		}
		
		return $info;
	}

	/**
	 * Obtiene información de transients relacionados con BCV.
	 *
	 * @return array Información de transients.
	 */
	public function get_transients_info() {
		global $wpdb;
		
		$transients = $wpdb->get_results(
			"SELECT option_name, option_value FROM $wpdb->options 
			 WHERE option_name LIKE '_transient_wvs_bcv_%' 
			 OR option_name LIKE '_transient_timeout_wvs_bcv_%'"
		);
		
		$info = array(
			'total_transients' => 0,
			'active_transients' => 0,
			'expired_transients' => 0,
			'transients' => array(),
		);
		
		foreach ( $transients as $transient ) {
			$info['total_transients']++;
			
			if ( strpos( $transient->option_name, '_transient_timeout_' ) === false ) {
				$info['active_transients']++;
				$info['transients'][] = array(
					'name' => $transient->option_name,
					'value_length' => strlen( $transient->option_value ),
				);
			} else {
				$info['expired_transients']++;
			}
		}
		
		return $info;
	}

	/**
	 * Actualiza configuración del cache.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'enabled',
			'default_ttl',
			'memory_cache_enabled',
			'memory_cache_size',
			'compression_enabled',
			'preload_enabled',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_bcv_' . $key, $value );
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

	/**
	 * Programa limpieza automática del cache.
	 *
	 * @return void
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'wvs_bcv_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'wvs_bcv_cache_cleanup' );
		}
	}

	/**
	 * Cancela limpieza automática del cache.
	 *
	 * @return void
	 */
	public function unschedule_cleanup() {
		wp_clear_scheduled_hook( 'wvs_bcv_cache_cleanup' );
	}

	/**
	 * Ejecuta limpieza del cache.
	 *
	 * @return void
	 */
	public function run_cleanup() {
		$deleted_count = $this->optimize_cache();
		error_log( "WVS Cache Manager: Limpieza automática completada. $deleted_count elementos eliminados." );
	}
}
