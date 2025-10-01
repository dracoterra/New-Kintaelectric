<?php
/**
 * BCV Tracker Wrapper - Integración con plugin existente
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrapper para integrar con el plugin BCV Dólar Tracker existente.
 * Proporciona una capa de abstracción y funcionalidades adicionales.
 */
class Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper {

	/** @var self */
	private static $instance = null;

	/** @var bool Plugin BCV Dólar Tracker disponible */
	private $tracker_available = false;

	/** @var string Versión del plugin tracker */
	private $tracker_version = '';

	/** @var array Configuración del wrapper */
	private $config = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->check_tracker_availability();
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
	 * Verifica si el plugin BCV Dólar Tracker está disponible.
	 *
	 * @return void
	 */
	private function check_tracker_availability() {
		// Verificar si el plugin está activo
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->tracker_available = is_plugin_active( 'bcv-dolar-tracker/bcv-dolar-tracker.php' );

		if ( $this->tracker_available ) {
			// Obtener información del plugin
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/bcv-dolar-tracker/bcv-dolar-tracker.php' );
			$this->tracker_version = $plugin_data['Version'] ?? '1.0.0';
		}
	}

	/**
	 * Carga configuración del wrapper.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'fallback_enabled' => get_option( 'wvs_bcv_fallback_enabled', true ),
			'manual_rate' => get_option( 'wvs_bcv_rate_manual', 0 ),
			'last_known_rate' => get_option( 'wvs_bcv_last_known_rate', 0 ),
			'update_frequency' => get_option( 'wvs_bcv_update_frequency', 30 ), // minutos
			'alert_threshold' => get_option( 'wvs_bcv_alert_threshold', 5 ), // %
			'cache_duration' => get_option( 'wvs_bcv_cache_duration', 30 ), // minutos
		);
	}

	/**
	 * Obtiene la tasa actual del BCV.
	 *
	 * @return float|false Tasa actual o false si no está disponible.
	 */
	public function get_current_rate() {
		// Intentar obtener del plugin BCV Dólar Tracker
		if ( $this->tracker_available && class_exists( 'BCV_Dolar_Tracker' ) ) {
			$rate = $this->get_rate_from_tracker();
			if ( $rate !== false ) {
				$this->update_last_known_rate( $rate );
				return $rate;
			}
		}

		// Fallback a tasa manual
		if ( $this->config['fallback_enabled'] && $this->config['manual_rate'] > 0 ) {
			return $this->config['manual_rate'];
		}

		// Fallback a última tasa conocida
		if ( $this->config['last_known_rate'] > 0 ) {
			return $this->config['last_known_rate'];
		}

		return false;
	}

	/**
	 * Obtiene la tasa del plugin BCV Dólar Tracker.
	 *
	 * @return float|false Tasa del tracker o false si no está disponible.
	 */
	private function get_rate_from_tracker() {
		try {
			// Verificar si existe la clase principal del tracker
			if ( class_exists( 'BCV_Dolar_Tracker' ) ) {
				// La clase BCV_Dolar_Tracker no es singleton, usar métodos estáticos o instancia
				// Intentar obtener la tasa desde la base de datos directamente
				global $wpdb;
				$table_name = $wpdb->prefix . 'bcv_precio_dolar';
				
				if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) {
					// Consulta segura sin parámetros (no usar prepare sin marcadores)
					$latest_rate = $wpdb->get_var( "SELECT precio FROM $table_name ORDER BY created_at DESC LIMIT 1" );
					
					if ( $latest_rate ) {
						return floatval( $latest_rate );
					}
				}
			}

			// Intentar obtener desde la base de datos del tracker (método alternativo)
			global $wpdb;
			$table_name = $wpdb->prefix . 'bcv_rates';
			
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) {
				// Consulta segura sin parámetros (no usar prepare sin marcadores)
				$latest_rate = $wpdb->get_var( "SELECT rate FROM $table_name ORDER BY created_at DESC LIMIT 1" );
				
				if ( $latest_rate ) {
					return floatval( $latest_rate );
				}
			}

		} catch ( Exception $e ) {
			error_log( 'WVS BCV Wrapper: Error obteniendo tasa del tracker: ' . $e->getMessage() );
		}

		return false;
	}

	/**
	 * Actualiza la última tasa conocida.
	 *
	 * @param float $rate Nueva tasa.
	 * @return void
	 */
	private function update_last_known_rate( $rate ) {
		if ( $rate > 0 ) {
			update_option( 'wvs_bcv_last_known_rate', $rate );
			update_option( 'wvs_bcv_last_update', current_time( 'mysql' ) );
		}
	}

	/**
	 * Establece una tasa manual como respaldo.
	 *
	 * @param float $rate Tasa manual.
	 * @return bool True si se estableció correctamente.
	 */
	public function set_manual_rate( $rate ) {
		$rate = floatval( $rate );
		if ( $rate > 0 ) {
			update_option( 'wvs_bcv_rate_manual', $rate );
			$this->config['manual_rate'] = $rate;
			return true;
		}
		return false;
	}

	/**
	 * Obtiene información del estado del tracker.
	 *
	 * @return array Información del estado.
	 */
	public function get_tracker_status() {
		return array(
			'available' => $this->tracker_available,
			'version' => $this->tracker_version,
			'current_rate' => $this->get_current_rate(),
			'manual_rate' => $this->config['manual_rate'],
			'last_known_rate' => $this->config['last_known_rate'],
			'last_update' => get_option( 'wvs_bcv_last_update', '' ),
			'fallback_enabled' => $this->config['fallback_enabled'],
		);
	}

	/**
	 * Verifica si el tracker está funcionando correctamente.
	 *
	 * @return bool True si está funcionando.
	 */
	public function is_tracker_working() {
		if ( ! $this->tracker_available ) {
			return false;
		}

		$rate = $this->get_rate_from_tracker();
		return $rate !== false && $rate > 0;
	}

	/**
	 * Obtiene el historial de tasas del tracker.
	 *
	 * @param int $days Número de días de historial.
	 * @return array Historial de tasas.
	 */
	public function get_rate_history( $days = 30 ) {
		$history = array();

		if ( $this->tracker_available ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'bcv_rates';
			
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT rate, created_at FROM $table_name 
						 WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
						 ORDER BY created_at DESC",
						$days
					)
				);

				foreach ( $results as $row ) {
					$history[] = array(
						'rate' => floatval( $row->rate ),
						'date' => $row->created_at,
						'timestamp' => strtotime( $row->created_at ),
					);
				}
			}
		}

		return $history;
	}

	/**
	 * Calcula estadísticas básicas del historial.
	 *
	 * @param int $days Número de días para análisis.
	 * @return array Estadísticas.
	 */
	public function get_rate_statistics( $days = 30 ) {
		$history = $this->get_rate_history( $days );
		
		if ( empty( $history ) ) {
			return array(
				'average' => 0,
				'min' => 0,
				'max' => 0,
				'volatility' => 0,
				'trend' => 'stable',
			);
		}

		$rates = array_column( $history, 'rate' );
		$average = array_sum( $rates ) / count( $rates );
		$min = min( $rates );
		$max = max( $rates );
		
		// Calcular volatilidad (desviación estándar)
		$variance = array_sum( array_map( function( $rate ) use ( $average ) {
			return pow( $rate - $average, 2 );
		}, $rates ) ) / count( $rates );
		$volatility = sqrt( $variance );

		// Determinar tendencia
		$trend = 'stable';
		if ( count( $rates ) >= 2 ) {
			$first_half = array_slice( $rates, 0, count( $rates ) / 2 );
			$second_half = array_slice( $rates, count( $rates ) / 2 );
			
			$first_avg = array_sum( $first_half ) / count( $first_half );
			$second_avg = array_sum( $second_half ) / count( $second_half );
			
			$change_percent = ( ( $second_avg - $first_avg ) / $first_avg ) * 100;
			
			if ( $change_percent > 2 ) {
				$trend = 'increasing';
			} elseif ( $change_percent < -2 ) {
				$trend = 'decreasing';
			}
		}

		return array(
			'average' => round( $average, 2 ),
			'min' => round( $min, 2 ),
			'max' => round( $max, 2 ),
			'volatility' => round( $volatility, 2 ),
			'trend' => $trend,
			'data_points' => count( $rates ),
		);
	}

	/**
	 * Verifica si hay una nueva tasa disponible.
	 *
	 * @return bool True si hay nueva tasa.
	 */
	public function has_new_rate() {
		$last_check = get_option( 'wvs_bcv_last_check', 0 );
		$current_time = time();
		
		// Verificar cada 5 minutos
		if ( $current_time - $last_check < 300 ) {
			return false;
		}

		update_option( 'wvs_bcv_last_check', $current_time );
		
		$current_rate = $this->get_current_rate();
		$last_rate = $this->config['last_known_rate'];
		
		if ( $current_rate && $last_rate && abs( $current_rate - $last_rate ) > 0.01 ) {
			return true;
		}

		return false;
	}

	/**
	 * Obtiene la configuración del wrapper.
	 *
	 * @return array Configuración.
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Actualiza la configuración del wrapper.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'fallback_enabled',
			'manual_rate',
			'update_frequency',
			'alert_threshold',
			'cache_duration',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_bcv_' . $key, $value );
				$this->config[ $key ] = $value;
			}
		}

		return true;
	}
}
