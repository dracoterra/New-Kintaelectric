<?php
/**
 * BCV Predictive Analytics - Análisis predictivo de tipos de cambio
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sistema de análisis predictivo para tipos de cambio BCV.
 * Implementa algoritmos básicos de Machine Learning para predicciones.
 */
class Woocommerce_Venezuela_Suite_BCV_Predictive_Analytics {

	/** @var self */
	private static $instance = null;

	/** @var Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper */
	private $tracker_wrapper;

	/** @var array Configuración del análisis */
	private $config = array();

	/** @var array Modelos de predicción */
	private $models = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->tracker_wrapper = Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper::get_instance();
		$this->load_config();
		$this->initialize_models();
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
	 * Carga configuración del análisis predictivo.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'enabled' => get_option( 'wvs_bcv_ml_enabled', true ),
			'prediction_horizon' => get_option( 'wvs_bcv_prediction_horizon', 7 ), // días
			'confidence_threshold' => get_option( 'wvs_bcv_confidence_threshold', 0.7 ),
			'min_data_points' => get_option( 'wvs_bcv_min_data_points', 30 ),
			'update_frequency' => get_option( 'wvs_bcv_ml_update_frequency', 24 ), // horas
		);
	}

	/**
	 * Inicializa modelos de predicción.
	 *
	 * @return void
	 */
	private function initialize_models() {
		$this->models = array(
			'linear_regression' => array(
				'name' => 'Regresión Lineal',
				'description' => 'Predicción basada en tendencia lineal',
				'accuracy' => 0.0,
				'last_trained' => null,
			),
			'moving_average' => array(
				'name' => 'Media Móvil',
				'description' => 'Predicción basada en promedio móvil',
				'accuracy' => 0.0,
				'last_trained' => null,
			),
			'exponential_smoothing' => array(
				'name' => 'Suavizado Exponencial',
				'description' => 'Predicción con mayor peso a datos recientes',
				'accuracy' => 0.0,
				'last_trained' => null,
			),
		);
	}

	/**
	 * Genera predicciones para los próximos días.
	 *
	 * @param int $days Número de días a predecir.
	 * @return array Predicciones con confianza.
	 */
	public function generate_predictions( $days = 7 ) {
		if ( ! $this->config['enabled'] ) {
			return array();
		}

		$history = $this->tracker_wrapper->get_rate_history( 90 ); // Últimos 90 días
		
		if ( count( $history ) < $this->config['min_data_points'] ) {
			return array(
				'error' => 'Datos insuficientes para predicción',
				'required_points' => $this->config['min_data_points'],
				'available_points' => count( $history ),
			);
		}

		$predictions = array();
		$current_rate = $this->tracker_wrapper->get_current_rate();

		// Predicción con Regresión Lineal
		$linear_prediction = $this->predict_linear_regression( $history, $days );
		$predictions['linear_regression'] = array(
			'model' => 'Regresión Lineal',
			'predictions' => $linear_prediction,
			'confidence' => $this->calculate_confidence( $history, $linear_prediction ),
			'accuracy' => $this->models['linear_regression']['accuracy'],
		);

		// Predicción con Media Móvil
		$ma_prediction = $this->predict_moving_average( $history, $days );
		$predictions['moving_average'] = array(
			'model' => 'Media Móvil',
			'predictions' => $ma_prediction,
			'confidence' => $this->calculate_confidence( $history, $ma_prediction ),
			'accuracy' => $this->models['moving_average']['accuracy'],
		);

		// Predicción con Suavizado Exponencial
		$es_prediction = $this->predict_exponential_smoothing( $history, $days );
		$predictions['exponential_smoothing'] = array(
			'model' => 'Suavizado Exponencial',
			'predictions' => $es_prediction,
			'confidence' => $this->calculate_confidence( $history, $es_prediction ),
			'accuracy' => $this->models['exponential_smoothing']['accuracy'],
		);

		// Predicción consensuada (promedio ponderado)
		$consensus_prediction = $this->generate_consensus_prediction( $predictions );
		$predictions['consensus'] = $consensus_prediction;

		// Almacenar predicciones
		$this->store_predictions( $predictions );

		return $predictions;
	}

	/**
	 * Predicción usando regresión lineal simple.
	 *
	 * @param array $history Historial de tasas.
	 * @param int $days Días a predecir.
	 * @return array Predicciones.
	 */
	private function predict_linear_regression( $history, $days ) {
		$rates = array_column( $history, 'rate' );
		$n = count( $rates );
		
		// Calcular pendiente y intercepto
		$sum_x = 0;
		$sum_y = 0;
		$sum_xy = 0;
		$sum_x2 = 0;

		for ( $i = 0; $i < $n; $i++ ) {
			$x = $i;
			$y = $rates[ $i ];
			
			$sum_x += $x;
			$sum_y += $y;
			$sum_xy += $x * $y;
			$sum_x2 += $x * $x;
		}

		$slope = ( $n * $sum_xy - $sum_x * $sum_y ) / ( $n * $sum_x2 - $sum_x * $sum_x );
		$intercept = ( $sum_y - $slope * $sum_x ) / $n;

		// Generar predicciones
		$predictions = array();
		for ( $i = 1; $i <= $days; $i++ ) {
			$predicted_rate = $intercept + $slope * ( $n + $i - 1 );
			$predictions[] = array(
				'day' => $i,
				'rate' => max( 0, $predicted_rate ), // No permitir tasas negativas
				'date' => date( 'Y-m-d', strtotime( "+$i days" ) ),
			);
		}

		return $predictions;
	}

	/**
	 * Predicción usando media móvil.
	 *
	 * @param array $history Historial de tasas.
	 * @param int $days Días a predecir.
	 * @return array Predicciones.
	 */
	private function predict_moving_average( $history, $days ) {
		$rates = array_column( $history, 'rate' );
		$window_size = min( 7, count( $rates ) / 2 ); // Ventana de 7 días o la mitad de los datos
		
		// Calcular media móvil de los últimos datos
		$recent_rates = array_slice( $rates, -$window_size );
		$base_rate = array_sum( $recent_rates ) / count( $recent_rates );
		
		// Calcular tendencia simple
		$trend = 0;
		if ( count( $rates ) >= 2 ) {
			$first_half = array_slice( $rates, 0, count( $rates ) / 2 );
			$second_half = array_slice( $rates, count( $rates ) / 2 );
			$first_avg = array_sum( $first_half ) / count( $first_half );
			$second_avg = array_sum( $second_half ) / count( $second_half );
			$trend = ( $second_avg - $first_avg ) / count( $rates );
		}

		// Generar predicciones
		$predictions = array();
		for ( $i = 1; $i <= $days; $i++ ) {
			$predicted_rate = $base_rate + ( $trend * $i );
			$predictions[] = array(
				'day' => $i,
				'rate' => max( 0, $predicted_rate ),
				'date' => date( 'Y-m-d', strtotime( "+$i days" ) ),
			);
		}

		return $predictions;
	}

	/**
	 * Predicción usando suavizado exponencial.
	 *
	 * @param array $history Historial de tasas.
	 * @param int $days Días a predecir.
	 * @return array Predicciones.
	 */
	private function predict_exponential_smoothing( $history, $days ) {
		$rates = array_column( $history, 'rate' );
		$alpha = 0.3; // Factor de suavizado
		
		// Calcular suavizado exponencial
		$smoothed = array();
		$smoothed[0] = $rates[0];
		
		for ( $i = 1; $i < count( $rates ); $i++ ) {
			$smoothed[ $i ] = $alpha * $rates[ $i ] + ( 1 - $alpha ) * $smoothed[ $i - 1 ];
		}

		// Calcular tendencia
		$trend = 0;
		if ( count( $smoothed ) >= 2 ) {
			$trend = $smoothed[ count( $smoothed ) - 1 ] - $smoothed[ count( $smoothed ) - 2 ];
		}

		// Generar predicciones
		$predictions = array();
		$last_smoothed = $smoothed[ count( $smoothed ) - 1 ];
		
		for ( $i = 1; $i <= $days; $i++ ) {
			$predicted_rate = $last_smoothed + ( $trend * $i );
			$predictions[] = array(
				'day' => $i,
				'rate' => max( 0, $predicted_rate ),
				'date' => date( 'Y-m-d', strtotime( "+$i days" ) ),
			);
		}

		return $predictions;
	}

	/**
	 * Genera predicción consensuada basada en todos los modelos.
	 *
	 * @param array $predictions Predicciones de todos los modelos.
	 * @return array Predicción consensuada.
	 */
	private function generate_consensus_prediction( $predictions ) {
		$consensus = array();
		$models = array( 'linear_regression', 'moving_average', 'exponential_smoothing' );
		
		// Obtener número de días predichos
		$days = count( $predictions['linear_regression']['predictions'] );
		
		for ( $i = 0; $i < $days; $i++ ) {
			$rates = array();
			$weights = array();
			
			foreach ( $models as $model ) {
				if ( isset( $predictions[ $model ]['predictions'][ $i ] ) ) {
					$rates[] = $predictions[ $model ]['predictions'][ $i ]['rate'];
					$weights[] = $predictions[ $model ]['accuracy'];
				}
			}
			
			if ( ! empty( $rates ) ) {
				// Promedio ponderado por precisión
				$total_weight = array_sum( $weights );
				$weighted_rate = 0;
				
				for ( $j = 0; $j < count( $rates ); $j++ ) {
					$weighted_rate += $rates[ $j ] * ( $weights[ $j ] / $total_weight );
				}
				
				$consensus[] = array(
					'day' => $i + 1,
					'rate' => round( $weighted_rate, 2 ),
					'date' => $predictions['linear_regression']['predictions'][ $i ]['date'],
					'confidence' => $this->calculate_consensus_confidence( $predictions, $i ),
				);
			}
		}
		
		return array(
			'model' => 'Consenso',
			'predictions' => $consensus,
			'confidence' => $this->calculate_overall_confidence( $predictions ),
		);
	}

	/**
	 * Calcula la confianza de una predicción.
	 *
	 * @param array $history Historial de tasas.
	 * @param array $predictions Predicciones.
	 * @return float Confianza (0-1).
	 */
	private function calculate_confidence( $history, $predictions ) {
		// Calcular volatilidad histórica
		$rates = array_column( $history, 'rate' );
		$volatility = $this->calculate_volatility( $rates );
		
		// Calcular estabilidad de la tendencia
		$trend_stability = $this->calculate_trend_stability( $rates );
		
		// Calcular cantidad de datos
		$data_confidence = min( 1.0, count( $rates ) / 100 ); // Máximo confianza con 100+ puntos
		
		// Confianza combinada
		$confidence = ( $trend_stability * 0.4 ) + ( $data_confidence * 0.3 ) + ( ( 1 - $volatility ) * 0.3 );
		
		return max( 0, min( 1, $confidence ) );
	}

	/**
	 * Calcula la volatilidad de una serie de tasas.
	 *
	 * @param array $rates Serie de tasas.
	 * @return float Volatilidad normalizada (0-1).
	 */
	private function calculate_volatility( $rates ) {
		if ( count( $rates ) < 2 ) {
			return 0.5; // Volatilidad media si no hay suficientes datos
		}

		$mean = array_sum( $rates ) / count( $rates );
		$variance = array_sum( array_map( function( $rate ) use ( $mean ) {
			return pow( $rate - $mean, 2 );
		}, $rates ) ) / count( $rates );
		
		$std_dev = sqrt( $variance );
		$coefficient = $std_dev / $mean;
		
		// Normalizar a 0-1 (asumiendo que coeficiente > 0.2 es alta volatilidad)
		return min( 1, $coefficient / 0.2 );
	}

	/**
	 * Calcula la estabilidad de la tendencia.
	 *
	 * @param array $rates Serie de tasas.
	 * @return float Estabilidad (0-1).
	 */
	private function calculate_trend_stability( $rates ) {
		if ( count( $rates ) < 10 ) {
			return 0.5; // Estabilidad media si no hay suficientes datos
		}

		// Dividir en segmentos y calcular tendencias
		$segments = array_chunk( $rates, ceil( count( $rates ) / 3 ) );
		$trends = array();
		
		foreach ( $segments as $segment ) {
			if ( count( $segment ) >= 2 ) {
				$trend = ( end( $segment ) - $segment[0] ) / count( $segment );
				$trends[] = $trend;
			}
		}
		
		if ( count( $trends ) < 2 ) {
			return 0.5;
		}
		
		// Calcular consistencia de tendencias
		$mean_trend = array_sum( $trends ) / count( $trends );
		$variance = array_sum( array_map( function( $trend ) use ( $mean_trend ) {
			return pow( $trend - $mean_trend, 2 );
		}, $trends ) ) / count( $trends );
		
		// Estabilidad inversamente proporcional a la varianza
		$stability = 1 / ( 1 + sqrt( $variance ) );
		
		return max( 0, min( 1, $stability ) );
	}

	/**
	 * Calcula confianza del consenso para un día específico.
	 *
	 * @param array $predictions Predicciones de todos los modelos.
	 * @param int $day_index Índice del día.
	 * @return float Confianza del consenso.
	 */
	private function calculate_consensus_confidence( $predictions, $day_index ) {
		$models = array( 'linear_regression', 'moving_average', 'exponential_smoothing' );
		$confidences = array();
		
		foreach ( $models as $model ) {
			if ( isset( $predictions[ $model ]['confidence'] ) ) {
				$confidences[] = $predictions[ $model ]['confidence'];
			}
		}
		
		if ( empty( $confidences ) ) {
			return 0.5;
		}
		
		// Confianza promedio de todos los modelos
		return array_sum( $confidences ) / count( $confidences );
	}

	/**
	 * Calcula confianza general de todas las predicciones.
	 *
	 * @param array $predictions Predicciones de todos los modelos.
	 * @return float Confianza general.
	 */
	private function calculate_overall_confidence( $predictions ) {
		$confidences = array();
		
		foreach ( $predictions as $model => $data ) {
			if ( isset( $data['confidence'] ) ) {
				$confidences[] = $data['confidence'];
			}
		}
		
		if ( empty( $confidences ) ) {
			return 0.5;
		}
		
		return array_sum( $confidences ) / count( $confidences );
	}

	/**
	 * Almacena predicciones en la base de datos.
	 *
	 * @param array $predictions Predicciones a almacenar.
	 * @return void
	 */
	private function store_predictions( $predictions ) {
		$prediction_data = array(
			'generated_at' => current_time( 'mysql' ),
			'predictions' => $predictions,
			'config' => $this->config,
		);
		
		update_option( 'wvs_bcv_predictions', $prediction_data );
	}

	/**
	 * Obtiene las últimas predicciones almacenadas.
	 *
	 * @return array Predicciones almacenadas.
	 */
	public function get_stored_predictions() {
		return get_option( 'wvs_bcv_predictions', array() );
	}

	/**
	 * Entrena los modelos con datos históricos.
	 *
	 * @return array Resultados del entrenamiento.
	 */
	public function train_models() {
		$history = $this->tracker_wrapper->get_rate_history( 90 );
		
		if ( count( $history ) < $this->config['min_data_points'] ) {
			return array(
				'success' => false,
				'message' => 'Datos insuficientes para entrenamiento',
			);
		}

		$results = array();
		
		// Entrenar cada modelo (simulado)
		foreach ( $this->models as $model_key => $model ) {
			$accuracy = $this->simulate_model_training( $model_key, $history );
			$this->models[ $model_key ]['accuracy'] = $accuracy;
			$this->models[ $model_key ]['last_trained'] = current_time( 'mysql' );
			
			$results[ $model_key ] = array(
				'accuracy' => $accuracy,
				'trained_at' => current_time( 'mysql' ),
			);
		}
		
		// Guardar modelos entrenados
		update_option( 'wvs_bcv_trained_models', $this->models );
		
		return array(
			'success' => true,
			'models' => $results,
		);
	}

	/**
	 * Simula el entrenamiento de un modelo (placeholder para ML real).
	 *
	 * @param string $model_key Clave del modelo.
	 * @param array $history Historial de datos.
	 * @return float Precisión simulada.
	 */
	private function simulate_model_training( $model_key, $history ) {
		// Simulación básica de precisión basada en volatilidad
		$rates = array_column( $history, 'rate' );
		$volatility = $this->calculate_volatility( $rates );
		
		// Precisión base por modelo
		$base_accuracy = array(
			'linear_regression' => 0.75,
			'moving_average' => 0.70,
			'exponential_smoothing' => 0.72,
		);
		
		// Ajustar por volatilidad (menos volatilidad = mayor precisión)
		$accuracy = $base_accuracy[ $model_key ] * ( 1 - $volatility * 0.3 );
		
		return max( 0.5, min( 0.95, $accuracy ) );
	}

	/**
	 * Obtiene información de los modelos.
	 *
	 * @return array Información de modelos.
	 */
	public function get_models_info() {
		return $this->models;
	}

	/**
	 * Actualiza configuración del análisis.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'enabled',
			'prediction_horizon',
			'confidence_threshold',
			'min_data_points',
			'update_frequency',
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
