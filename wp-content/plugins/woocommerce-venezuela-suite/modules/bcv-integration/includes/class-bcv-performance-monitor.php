<?php
/**
 * BCV Performance Monitor - Monitor de rendimiento del sistema BCV
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monitor de rendimiento para el sistema BCV Integration.
 * Rastrea métricas de performance, disponibilidad y eficiencia.
 */
class Woocommerce_Venezuela_Suite_BCV_Performance_Monitor {

	/** @var self */
	private static $instance = null;

	/** @var array Métricas de performance */
	private $metrics = array();

	/** @var array Configuración del monitor */
	private $config = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->load_metrics();
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
	 * Carga configuración del monitor.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'enabled' => get_option( 'wvs_bcv_performance_monitoring', true ),
			'tracking_duration' => get_option( 'wvs_bcv_tracking_duration', 30 ), // días
			'alert_threshold' => get_option( 'wvs_bcv_performance_threshold', 2 ), // segundos
			'uptime_threshold' => get_option( 'wvs_bcv_uptime_threshold', 95 ), // %
		);
	}

	/**
	 * Carga métricas almacenadas.
	 *
	 * @return void
	 */
	private function load_metrics() {
		$this->metrics = get_option( 'wvs_bcv_performance_metrics', array(
			'response_times' => array(),
			'uptime_data' => array(),
			'error_counts' => array(),
			'cache_hit_rates' => array(),
			'daily_stats' => array(),
		) );
	}

	/**
	 * Registra el tiempo de respuesta de una operación.
	 *
	 * @param string $operation Tipo de operación.
	 * @param float $response_time Tiempo de respuesta en segundos.
	 * @return void
	 */
	public function record_response_time( $operation, $response_time ) {
		if ( ! $this->config['enabled'] ) {
			return;
		}

		$timestamp = current_time( 'mysql' );
		
		$this->metrics['response_times'][] = array(
			'operation' => $operation,
			'response_time' => $response_time,
			'timestamp' => $timestamp,
		);

		// Mantener solo los últimos 1000 registros
		$this->metrics['response_times'] = array_slice( $this->metrics['response_times'], -1000 );

		$this->save_metrics();
	}

	/**
	 * Registra datos de uptime del sistema.
	 *
	 * @param bool $is_up Sistema funcionando.
	 * @param string $component Componente específico.
	 * @return void
	 */
	public function record_uptime( $is_up, $component = 'overall' ) {
		if ( ! $this->config['enabled'] ) {
			return;
		}

		$timestamp = current_time( 'mysql' );
		
		if ( ! isset( $this->metrics['uptime_data'][ $component ] ) ) {
			$this->metrics['uptime_data'][ $component ] = array();
		}

		$this->metrics['uptime_data'][ $component ][] = array(
			'is_up' => $is_up,
			'timestamp' => $timestamp,
		);

		// Mantener solo los últimos 100 registros por componente
		$this->metrics['uptime_data'][ $component ] = array_slice( $this->metrics['uptime_data'][ $component ], -100 );

		$this->save_metrics();
	}

	/**
	 * Registra un error del sistema.
	 *
	 * @param string $error_type Tipo de error.
	 * @param string $error_message Mensaje del error.
	 * @param string $component Componente donde ocurrió.
	 * @return void
	 */
	public function record_error( $error_type, $error_message, $component = 'unknown' ) {
		if ( ! $this->config['enabled'] ) {
			return;
		}

		$timestamp = current_time( 'mysql' );
		
		$this->metrics['error_counts'][] = array(
			'error_type' => $error_type,
			'error_message' => $error_message,
			'component' => $component,
			'timestamp' => $timestamp,
		);

		// Mantener solo los últimos 500 errores
		$this->metrics['error_counts'] = array_slice( $this->metrics['error_counts'], -500 );

		$this->save_metrics();
	}

	/**
	 * Registra tasa de acierto del cache.
	 *
	 * @param string $cache_type Tipo de cache.
	 * @param bool $hit Cache hit o miss.
	 * @return void
	 */
	public function record_cache_hit( $cache_type, $hit ) {
		if ( ! $this->config['enabled'] ) {
			return;
		}

		$timestamp = current_time( 'mysql' );
		
		if ( ! isset( $this->metrics['cache_hit_rates'][ $cache_type ] ) ) {
			$this->metrics['cache_hit_rates'][ $cache_type ] = array();
		}

		$this->metrics['cache_hit_rates'][ $cache_type ][] = array(
			'hit' => $hit,
			'timestamp' => $timestamp,
		);

		// Mantener solo los últimos 200 registros por tipo de cache
		$this->metrics['cache_hit_rates'][ $cache_type ] = array_slice( $this->metrics['cache_hit_rates'][ $cache_type ], -200 );

		$this->save_metrics();
	}

	/**
	 * Calcula estadísticas de performance.
	 *
	 * @param int $days Número de días para análisis.
	 * @return array Estadísticas de performance.
	 */
	public function get_performance_stats( $days = 7 ) {
		$stats = array(
			'response_times' => $this->calculate_response_time_stats( $days ),
			'uptime' => $this->calculate_uptime_stats( $days ),
			'errors' => $this->calculate_error_stats( $days ),
			'cache_performance' => $this->calculate_cache_stats( $days ),
			'overall_score' => 0,
		);

		// Calcular puntuación general
		$stats['overall_score'] = $this->calculate_overall_score( $stats );

		return $stats;
	}

	/**
	 * Calcula estadísticas de tiempo de respuesta.
	 *
	 * @param int $days Número de días.
	 * @return array Estadísticas de tiempo de respuesta.
	 */
	private function calculate_response_time_stats( $days ) {
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );
		$recent_times = array_filter( $this->metrics['response_times'], function( $record ) use ( $cutoff_date ) {
			return $record['timestamp'] >= $cutoff_date;
		});

		if ( empty( $recent_times ) ) {
			return array(
				'average' => 0,
				'min' => 0,
				'max' => 0,
				'p95' => 0,
				'count' => 0,
			);
		}

		$times = array_column( $recent_times, 'response_time' );
		sort( $times );

		$count = count( $times );
		$average = array_sum( $times ) / $count;
		$min = min( $times );
		$max = max( $times );
		$p95_index = floor( $count * 0.95 );
		$p95 = $times[ $p95_index ] ?? $max;

		return array(
			'average' => round( $average, 3 ),
			'min' => round( $min, 3 ),
			'max' => round( $max, 3 ),
			'p95' => round( $p95, 3 ),
			'count' => $count,
		);
	}

	/**
	 * Calcula estadísticas de uptime.
	 *
	 * @param int $days Número de días.
	 * @return array Estadísticas de uptime.
	 */
	private function calculate_uptime_stats( $days ) {
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );
		$stats = array();

		foreach ( $this->metrics['uptime_data'] as $component => $records ) {
			$recent_records = array_filter( $records, function( $record ) use ( $cutoff_date ) {
				return $record['timestamp'] >= $cutoff_date;
			});

			if ( empty( $recent_records ) ) {
				$stats[ $component ] = array(
					'uptime_percentage' => 100,
					'total_checks' => 0,
					'uptime_count' => 0,
				);
				continue;
			}

			$total_checks = count( $recent_records );
			$uptime_count = count( array_filter( $recent_records, function( $record ) {
				return $record['is_up'];
			}));

			$uptime_percentage = $total_checks > 0 ? ( $uptime_count / $total_checks ) * 100 : 100;

			$stats[ $component ] = array(
				'uptime_percentage' => round( $uptime_percentage, 2 ),
				'total_checks' => $total_checks,
				'uptime_count' => $uptime_count,
			);
		}

		return $stats;
	}

	/**
	 * Calcula estadísticas de errores.
	 *
	 * @param int $days Número de días.
	 * @return array Estadísticas de errores.
	 */
	private function calculate_error_stats( $days ) {
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );
		$recent_errors = array_filter( $this->metrics['error_counts'], function( $record ) use ( $cutoff_date ) {
			return $record['timestamp'] >= $cutoff_date;
		});

		$error_types = array();
		$error_components = array();

		foreach ( $recent_errors as $error ) {
			$type = $error['error_type'];
			$component = $error['component'];

			$error_types[ $type ] = ( $error_types[ $type ] ?? 0 ) + 1;
			$error_components[ $component ] = ( $error_components[ $component ] ?? 0 ) + 1;
		}

		return array(
			'total_errors' => count( $recent_errors ),
			'error_types' => $error_types,
			'error_components' => $error_components,
			'errors_per_day' => count( $recent_errors ) / max( 1, $days ),
		);
	}

	/**
	 * Calcula estadísticas de cache.
	 *
	 * @param int $days Número de días.
	 * @return array Estadísticas de cache.
	 */
	private function calculate_cache_stats( $days ) {
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );
		$stats = array();

		foreach ( $this->metrics['cache_hit_rates'] as $cache_type => $records ) {
			$recent_records = array_filter( $records, function( $record ) use ( $cutoff_date ) {
				return $record['timestamp'] >= $cutoff_date;
			});

			if ( empty( $recent_records ) ) {
				$stats[ $cache_type ] = array(
					'hit_rate' => 0,
					'total_requests' => 0,
					'hits' => 0,
				);
				continue;
			}

			$total_requests = count( $recent_records );
			$hits = count( array_filter( $recent_records, function( $record ) {
				return $record['hit'];
			}));

			$hit_rate = $total_requests > 0 ? ( $hits / $total_requests ) * 100 : 0;

			$stats[ $cache_type ] = array(
				'hit_rate' => round( $hit_rate, 2 ),
				'total_requests' => $total_requests,
				'hits' => $hits,
			);
		}

		return $stats;
	}

	/**
	 * Calcula puntuación general de performance.
	 *
	 * @param array $stats Estadísticas calculadas.
	 * @return int Puntuación general (0-100).
	 */
	private function calculate_overall_score( $stats ) {
		$score = 100;

		// Penalizar por tiempo de respuesta alto
		$avg_response_time = $stats['response_times']['average'];
		if ( $avg_response_time > 2 ) {
			$score -= min( 30, ( $avg_response_time - 2 ) * 10 );
		}

		// Penalizar por uptime bajo
		$overall_uptime = $stats['uptime']['overall']['uptime_percentage'] ?? 100;
		if ( $overall_uptime < 95 ) {
			$score -= ( 95 - $overall_uptime ) * 2;
		}

		// Penalizar por muchos errores
		$errors_per_day = $stats['errors']['errors_per_day'];
		if ( $errors_per_day > 5 ) {
			$score -= min( 20, $errors_per_day * 2 );
		}

		// Bonificar por buen rendimiento de cache
		$cache_hit_rate = 0;
		$cache_count = 0;
		foreach ( $stats['cache_performance'] as $cache_stats ) {
			$cache_hit_rate += $cache_stats['hit_rate'];
			$cache_count++;
		}
		if ( $cache_count > 0 ) {
			$avg_cache_hit_rate = $cache_hit_rate / $cache_count;
			if ( $avg_cache_hit_rate > 80 ) {
				$score += min( 10, ( $avg_cache_hit_rate - 80 ) / 2 );
			}
		}

		return max( 0, min( 100, round( $score ) ) );
	}

	/**
	 * Genera reporte de performance.
	 *
	 * @param int $days Número de días para el reporte.
	 * @return array Reporte completo.
	 */
	public function generate_performance_report( $days = 30 ) {
		$stats = $this->get_performance_stats( $days );
		
		$report = array(
			'period' => "$days días",
			'generated_at' => current_time( 'mysql' ),
			'overall_score' => $stats['overall_score'],
			'status' => $this->get_performance_status( $stats['overall_score'] ),
			'statistics' => $stats,
			'recommendations' => $this->generate_recommendations( $stats ),
			'trends' => $this->calculate_trends( $days ),
		);

		return $report;
	}

	/**
	 * Determina el estado de performance basado en la puntuación.
	 *
	 * @param int $score Puntuación de performance.
	 * @return string Estado de performance.
	 */
	private function get_performance_status( $score ) {
		if ( $score >= 90 ) {
			return 'excellent';
		} elseif ( $score >= 75 ) {
			return 'good';
		} elseif ( $score >= 60 ) {
			return 'fair';
		} elseif ( $score >= 40 ) {
			return 'poor';
		} else {
			return 'critical';
		}
	}

	/**
	 * Genera recomendaciones basadas en las estadísticas.
	 *
	 * @param array $stats Estadísticas de performance.
	 * @return array Recomendaciones.
	 */
	private function generate_recommendations( $stats ) {
		$recommendations = array();

		// Recomendaciones basadas en tiempo de respuesta
		if ( $stats['response_times']['average'] > 2 ) {
			$recommendations[] = array(
				'type' => 'performance',
				'priority' => 'high',
				'title' => 'Optimizar Tiempo de Respuesta',
				'description' => 'El tiempo promedio de respuesta es alto. Considera optimizar consultas de base de datos y cache.',
			);
		}

		// Recomendaciones basadas en uptime
		foreach ( $stats['uptime'] as $component => $uptime_stats ) {
			if ( $uptime_stats['uptime_percentage'] < 95 ) {
				$recommendations[] = array(
					'type' => 'reliability',
					'priority' => 'high',
					'title' => "Mejorar Disponibilidad de $component",
					'description' => "El componente $component tiene baja disponibilidad. Revisa logs de errores y configuración.",
				);
			}
		}

		// Recomendaciones basadas en errores
		if ( $stats['errors']['errors_per_day'] > 5 ) {
			$recommendations[] = array(
				'type' => 'stability',
				'priority' => 'medium',
				'title' => 'Reducir Errores del Sistema',
				'description' => 'Se detectaron muchos errores diarios. Revisa logs y implementa mejor manejo de errores.',
			);
		}

		// Recomendaciones basadas en cache
		foreach ( $stats['cache_performance'] as $cache_type => $cache_stats ) {
			if ( $cache_stats['hit_rate'] < 70 ) {
				$recommendations[] = array(
					'type' => 'optimization',
					'priority' => 'medium',
					'title' => "Mejorar Cache $cache_type",
					'description' => "La tasa de acierto del cache $cache_type es baja. Considera ajustar la duración del cache.",
				);
			}
		}

		return $recommendations;
	}

	/**
	 * Calcula tendencias de performance.
	 *
	 * @param int $days Número de días.
	 * @return array Tendencias.
	 */
	private function calculate_trends( $days ) {
		$trends = array();

		// Calcular tendencia de tiempo de respuesta
		$response_times = array_column( $this->metrics['response_times'], 'response_time' );
		if ( count( $response_times ) >= 10 ) {
			$first_half = array_slice( $response_times, 0, count( $response_times ) / 2 );
			$second_half = array_slice( $response_times, count( $response_times ) / 2 );
			
			$first_avg = array_sum( $first_half ) / count( $first_half );
			$second_avg = array_sum( $second_half ) / count( $second_half );
			
			$trends['response_time'] = $second_avg > $first_avg ? 'increasing' : 'decreasing';
		}

		// Calcular tendencia de errores
		$error_counts = array();
		foreach ( $this->metrics['error_counts'] as $error ) {
			$date = date( 'Y-m-d', strtotime( $error['timestamp'] ) );
			$error_counts[ $date ] = ( $error_counts[ $date ] ?? 0 ) + 1;
		}

		if ( count( $error_counts ) >= 7 ) {
			$dates = array_keys( $error_counts );
			sort( $dates );
			$first_week = array_slice( $dates, 0, 7 );
			$second_week = array_slice( $dates, -7 );
			
			$first_week_errors = array_sum( array_map( function( $date ) use ( $error_counts ) {
				return $error_counts[ $date ];
			}, $first_week ) );
			
			$second_week_errors = array_sum( array_map( function( $date ) use ( $error_counts ) {
				return $error_counts[ $date ];
			}, $second_week ) );
			
			$trends['errors'] = $second_week_errors > $first_week_errors ? 'increasing' : 'decreasing';
		}

		return $trends;
	}

	/**
	 * Guarda métricas en la base de datos.
	 *
	 * @return void
	 */
	private function save_metrics() {
		update_option( 'wvs_bcv_performance_metrics', $this->metrics );
	}

	/**
	 * Limpia métricas antiguas.
	 *
	 * @param int $days Días de retención.
	 * @return void
	 */
	public function cleanup_old_metrics( $days = 30 ) {
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		// Limpiar tiempos de respuesta antiguos
		$this->metrics['response_times'] = array_filter( $this->metrics['response_times'], function( $record ) use ( $cutoff_date ) {
			return $record['timestamp'] >= $cutoff_date;
		});

		// Limpiar datos de uptime antiguos
		foreach ( $this->metrics['uptime_data'] as $component => $records ) {
			$this->metrics['uptime_data'][ $component ] = array_filter( $records, function( $record ) use ( $cutoff_date ) {
				return $record['timestamp'] >= $cutoff_date;
			});
		}

		// Limpiar errores antiguos
		$this->metrics['error_counts'] = array_filter( $this->metrics['error_counts'], function( $record ) use ( $cutoff_date ) {
			return $record['timestamp'] >= $cutoff_date;
		});

		// Limpiar datos de cache antiguos
		foreach ( $this->metrics['cache_hit_rates'] as $cache_type => $records ) {
			$this->metrics['cache_hit_rates'][ $cache_type ] = array_filter( $records, function( $record ) use ( $cutoff_date ) {
				return $record['timestamp'] >= $cutoff_date;
			});
		}

		$this->save_metrics();
	}

	/**
	 * Obtiene métricas en tiempo real.
	 *
	 * @return array Métricas en tiempo real.
	 */
	public function get_realtime_metrics() {
		return array(
			'current_response_time' => $this->get_current_response_time(),
			'current_uptime' => $this->get_current_uptime(),
			'recent_errors' => $this->get_recent_errors(),
			'cache_status' => $this->get_cache_status(),
		);
	}

	/**
	 * Obtiene tiempo de respuesta actual.
	 *
	 * @return float Tiempo de respuesta actual.
	 */
	private function get_current_response_time() {
		$start_time = microtime( true );
		
		// Simular operación de prueba
		$tracker_wrapper = Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper::get_instance();
		$tracker_wrapper->get_current_rate();
		
		return microtime( true ) - $start_time;
	}

	/**
	 * Obtiene uptime actual.
	 *
	 * @return array Estado de uptime.
	 */
	private function get_current_uptime() {
		$tracker_wrapper = Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper::get_instance();
		$is_working = $tracker_wrapper->is_tracker_working();
		
		return array(
			'overall' => $is_working,
			'tracker' => $is_working,
			'last_check' => current_time( 'mysql' ),
		);
	}

	/**
	 * Obtiene errores recientes.
	 *
	 * @return array Errores recientes.
	 */
	private function get_recent_errors() {
		$recent_errors = array_slice( $this->metrics['error_counts'], -10 );
		
		return array(
			'count' => count( $recent_errors ),
			'errors' => $recent_errors,
		);
	}

	/**
	 * Obtiene estado del cache.
	 *
	 * @return array Estado del cache.
	 */
	private function get_cache_status() {
		$cache_status = array();
		
		foreach ( $this->metrics['cache_hit_rates'] as $cache_type => $records ) {
			$recent_records = array_slice( $records, -10 );
			$hits = count( array_filter( $recent_records, function( $record ) {
				return $record['hit'];
			}));
			
			$cache_status[ $cache_type ] = array(
				'hit_rate' => count( $recent_records ) > 0 ? ( $hits / count( $recent_records ) ) * 100 : 0,
				'recent_requests' => count( $recent_records ),
			);
		}
		
		return $cache_status;
	}

	/**
	 * Actualiza configuración del monitor.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'enabled',
			'tracking_duration',
			'alert_threshold',
			'uptime_threshold',
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
}
