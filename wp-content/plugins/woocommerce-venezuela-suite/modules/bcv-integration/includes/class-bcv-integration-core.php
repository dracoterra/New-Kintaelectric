<?php
/**
 * BCV Integration Core - Núcleo mejorado del módulo BCV
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-bcv-tracker-wrapper.php';
require_once __DIR__ . '/class-bcv-predictive-analytics.php';
require_once __DIR__ . '/class-bcv-alert-system.php';

/**
 * Núcleo mejorado del módulo BCV Integration.
 * Integra todas las funcionalidades avanzadas según PLAN-MEJORADO.md
 */
class Woocommerce_Venezuela_Suite_BCV_Integration_Core extends Woocommerce_Venezuela_Suite_Abstract_Module {

	/** @var self */
	private static $instance = null;

	/** @var Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper */
	private $tracker_wrapper;

	/** @var Woocommerce_Venezuela_Suite_BCV_Predictive_Analytics */
	private $predictive_analytics;

	/** @var Woocommerce_Venezuela_Suite_BCV_Alert_System */
	private $alert_system;

	/** @var array Configuración del módulo */
	private $config = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->tracker_wrapper = Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper::get_instance();
		$this->predictive_analytics = Woocommerce_Venezuela_Suite_BCV_Predictive_Analytics::get_instance();
		$this->alert_system = Woocommerce_Venezuela_Suite_BCV_Alert_System::get_instance();
		$this->load_config();
		$this->init_hooks();
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
	 * Implementación del método abstracto get_module_name.
	 *
	 * @return string Nombre del módulo.
	 */
	public function get_module_name() {
		return 'BCV Integration Avanzado';
	}

	/**
	 * Implementación del método abstracto get_module_version.
	 *
	 * @return string Versión del módulo.
	 */
	public function get_module_version() {
		return '1.0.0';
	}

	/**
	 * Implementación del método abstracto get_module_description.
	 *
	 * @return string Descripción del módulo.
	 */
	public function get_module_description() {
		return 'Integración avanzada con BCV Dólar Tracker, análisis predictivo, alertas inteligentes y optimización de performance.';
	}

	/**
	 * Implementación del método abstracto get_module_dependencies.
	 *
	 * @return array Dependencias del módulo.
	 */
	public function get_module_dependencies() {
		return array();
	}

	/**
	 * Implementación del método abstracto run.
	 *
	 * @return void
	 */
	public function run() {
		// Programar tareas periódicas
		$this->schedule_periodic_tasks();
		
		// Inicializar sistema de alertas
		$this->alert_system->schedule_alerts();
		
		// Hook para verificación de alertas
		add_action( 'wvs_bcv_check_alerts', array( $this->alert_system, 'check_for_alerts' ) );
		
		// Hook para actualización periódica de predicciones
		add_action( 'wvs_bcv_update_predictions', array( $this, 'update_predictions' ) );
		
		// Hook para limpieza de cache
		add_action( 'wvs_bcv_cleanup_cache', array( $this, 'cleanup_cache' ) );
	}

	/**
	 * Carga configuración del módulo.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'update_frequency' => get_option( 'wvs_bcv_update_frequency', 30 ), // minutos
			'prediction_enabled' => get_option( 'wvs_bcv_prediction_enabled', true ),
			'alerts_enabled' => get_option( 'wvs_bcv_alerts_enabled', true ),
			'cache_duration' => get_option( 'wvs_bcv_cache_duration', 30 ), // minutos
			'performance_monitoring' => get_option( 'wvs_bcv_performance_monitoring', true ),
		);
	}

	/**
	 * Inicializa hooks del módulo.
	 *
	 * @return void
	 */
	private function init_hooks() {
		// Hook para obtener tasa actual
		add_filter( 'wvs_get_bcv_rate', array( $this, 'get_current_rate' ) );
		
		// Hook para establecer tasa manual
		add_action( 'wvs_set_bcv_rate_manual', array( $this, 'set_manual_rate' ), 10, 1 );
		
		// Hook para obtener estadísticas
		add_filter( 'wvs_get_bcv_statistics', array( $this, 'get_statistics' ) );
		
		// Hook para obtener predicciones
		add_filter( 'wvs_get_bcv_predictions', array( $this, 'get_predictions' ) );
		
		// Hook para obtener alertas
		add_filter( 'wvs_get_bcv_alerts', array( $this, 'get_alerts' ) );
		
		// Hook de activación del módulo
		add_action( 'wvs_module_activated_bcv-integration', array( $this, 'on_module_activation' ) );
		
		// Hook de desactivación del módulo
		add_action( 'wvs_module_deactivated_bcv-integration', array( $this, 'on_module_deactivation' ) );
	}

	/**
	 * Obtiene la tasa actual del BCV con todas las funcionalidades avanzadas.
	 *
	 * @return float|false Tasa actual o false si no está disponible.
	 */
	public function get_current_rate() {
		// Verificar cache primero
		$cached_rate = get_transient( 'wvs_bcv_rate_current' );
		if ( false !== $cached_rate && is_numeric( $cached_rate ) ) {
			return (float) $cached_rate;
		}

		// Obtener tasa del tracker wrapper
		$rate = $this->tracker_wrapper->get_current_rate();
		
		if ( $rate !== false ) {
			// Actualizar cache
			set_transient( 'wvs_bcv_rate_current', $rate, $this->config['cache_duration'] * MINUTE_IN_SECONDS );
			
			// Actualizar última tasa conocida
			update_option( 'wvs_bcv_last_known_rate', $rate );
			update_option( 'wvs_bcv_last_update', current_time( 'mysql' ) );
			
			// Verificar alertas si está habilitado
			if ( $this->config['alerts_enabled'] ) {
				$this->alert_system->check_for_alerts();
			}
		}

		return $rate;
	}

	/**
	 * Establece una tasa manual como respaldo.
	 *
	 * @param float $rate Tasa manual.
	 * @return bool True si se estableció correctamente.
	 */
	public function set_manual_rate( $rate ) {
		$result = $this->tracker_wrapper->set_manual_rate( $rate );
		
		if ( $result ) {
			// Limpiar cache para forzar actualización
			delete_transient( 'wvs_bcv_rate_current' );
			
			// Actualizar última tasa conocida
			update_option( 'wvs_bcv_last_known_rate', $rate );
			update_option( 'wvs_bcv_last_update', current_time( 'mysql' ) );
		}
		
		return $result;
	}

	/**
	 * Obtiene estadísticas completas del sistema BCV.
	 *
	 * @return array Estadísticas del sistema.
	 */
	public function get_statistics() {
		$tracker_status = $this->tracker_wrapper->get_tracker_status();
		$rate_statistics = $this->tracker_wrapper->get_rate_statistics( 30 );
		$alert_statistics = $this->alert_system->get_alert_statistics();
		
		return array(
			'tracker_status' => $tracker_status,
			'rate_statistics' => $rate_statistics,
			'alert_statistics' => $alert_statistics,
			'config' => $this->config,
			'last_update' => get_option( 'wvs_bcv_last_update', '' ),
			'cache_status' => array(
				'current_rate_cached' => get_transient( 'wvs_bcv_rate_current' ) !== false,
				'cache_expires_in' => $this->get_cache_expiration_time(),
			),
		);
	}

	/**
	 * Obtiene predicciones del sistema de análisis predictivo.
	 *
	 * @param int $days Número de días a predecir.
	 * @return array Predicciones.
	 */
	public function get_predictions( $days = 7 ) {
		if ( ! $this->config['prediction_enabled'] ) {
			return array( 'error' => 'Predicciones deshabilitadas' );
		}

		// Verificar si hay predicciones recientes en cache
		$cached_predictions = get_transient( 'wvs_bcv_predictions_cache' );
		if ( false !== $cached_predictions ) {
			return $cached_predictions;
		}

		// Generar nuevas predicciones
		$predictions = $this->predictive_analytics->generate_predictions( $days );
		
		// Cachear predicciones por 1 hora
		set_transient( 'wvs_bcv_predictions_cache', $predictions, HOUR_IN_SECONDS );
		
		return $predictions;
	}

	/**
	 * Obtiene alertas del sistema de alertas.
	 *
	 * @param int $limit Límite de alertas.
	 * @return array Alertas.
	 */
	public function get_alerts( $limit = 50 ) {
		return array(
			'history' => $this->alert_system->get_alert_history( $limit ),
			'statistics' => $this->alert_system->get_alert_statistics(),
			'config' => $this->alert_system->get_config(),
		);
	}

	/**
	 * Actualiza predicciones del sistema.
	 *
	 * @return void
	 */
	public function update_predictions() {
		if ( $this->config['prediction_enabled'] ) {
			// Entrenar modelos
			$this->predictive_analytics->train_models();
			
			// Generar nuevas predicciones
			$predictions = $this->predictive_analytics->generate_predictions( 7 );
			
			// Cachear predicciones
			set_transient( 'wvs_bcv_predictions_cache', $predictions, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Limpia cache del sistema.
	 *
	 * @return void
	 */
	public function cleanup_cache() {
		// Limpiar transients antiguos
		delete_transient( 'wvs_bcv_rate_current' );
		delete_transient( 'wvs_bcv_predictions_cache' );
		
		// Limpiar cache de estadísticas
		delete_transient( 'wvs_bcv_statistics_cache' );
		
		// Log de limpieza
		error_log( 'WVS BCV Integration: Cache limpiado automáticamente' );
	}

	/**
	 * Programa tareas periódicas del módulo.
	 *
	 * @return void
	 */
	private function schedule_periodic_tasks() {
		// Actualización de predicciones cada 24 horas
		if ( ! wp_next_scheduled( 'wvs_bcv_update_predictions' ) ) {
			wp_schedule_event( time(), 'daily', 'wvs_bcv_update_predictions' );
		}
		
		// Limpieza de cache cada 6 horas
		if ( ! wp_next_scheduled( 'wvs_bcv_cleanup_cache' ) ) {
			wp_schedule_event( time(), 'wvs_bcv_6hourly', 'wvs_bcv_cleanup_cache' );
		}
		
		// Agregar intervalo personalizado de 6 horas
		add_filter( 'cron_schedules', function( $schedules ) {
			$schedules['wvs_bcv_6hourly'] = array(
				'interval' => 6 * HOUR_IN_SECONDS,
				'display' => __( 'Cada 6 horas', 'woocommerce-venezuela-suite' ),
			);
			return $schedules;
		});
	}

	/**
	 * Obtiene tiempo de expiración del cache.
	 *
	 * @return int Tiempo de expiración en segundos.
	 */
	private function get_cache_expiration_time() {
		$transient_timeout = get_option( '_transient_timeout_wvs_bcv_rate_current' );
		if ( $transient_timeout ) {
			return $transient_timeout - time();
		}
		return 0;
	}

	/**
	 * Maneja la activación del módulo.
	 *
	 * @return void
	 */
	public function on_module_activation() {
		// Programar tareas periódicas
		$this->schedule_periodic_tasks();
		
		// Inicializar sistema de alertas
		$this->alert_system->schedule_alerts();
		
		// Crear tablas de base de datos si es necesario
		$this->create_database_tables();
		
		// Log de activación
		error_log( 'WVS BCV Integration: Módulo activado correctamente' );
	}

	/**
	 * Maneja la desactivación del módulo.
	 *
	 * @return void
	 */
	public function on_module_deactivation() {
		// Cancelar tareas programadas
		wp_clear_scheduled_hook( 'wvs_bcv_update_predictions' );
		wp_clear_scheduled_hook( 'wvs_bcv_cleanup_cache' );
		
		// Cancelar sistema de alertas
		$this->alert_system->unschedule_alerts();
		
		// Limpiar cache
		$this->cleanup_cache();
		
		// Log de desactivación
		error_log( 'WVS BCV Integration: Módulo desactivado correctamente' );
	}

	/**
	 * Crea tablas de base de datos necesarias.
	 *
	 * @return void
	 */
	private function create_database_tables() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wvs_bcv_analytics';
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			rate decimal(10,2) NOT NULL,
			volatility decimal(5,4) DEFAULT 0,
			prediction_accuracy decimal(5,4) DEFAULT 0,
			alert_count int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY created_at (created_at)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Obtiene información completa del módulo.
	 *
	 * @return array Información del módulo.
	 */
	public function get_module_info() {
		return array(
			'name' => $this->get_module_name(),
			'version' => $this->get_module_version(),
			'description' => $this->get_module_description(),
			'dependencies' => $this->get_module_dependencies(),
			'status' => 'active',
			'config' => $this->config,
			'components' => array(
				'tracker_wrapper' => $this->tracker_wrapper->get_tracker_status(),
				'predictive_analytics' => $this->predictive_analytics->get_models_info(),
				'alert_system' => $this->alert_system->get_config(),
			),
		);
	}

	/**
	 * Actualiza configuración del módulo.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'update_frequency',
			'prediction_enabled',
			'alerts_enabled',
			'cache_duration',
			'performance_monitoring',
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
	 * Obtiene configuración actual del módulo.
	 *
	 * @return array Configuración actual.
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Verifica el estado de salud del módulo.
	 *
	 * @return array Estado de salud.
	 */
	public function get_health_status() {
		$status = array(
			'overall' => 'healthy',
			'components' => array(),
			'issues' => array(),
		);

		// Verificar tracker wrapper
		$tracker_status = $this->tracker_wrapper->get_tracker_status();
		$status['components']['tracker_wrapper'] = array(
			'status' => $tracker_status['available'] ? 'healthy' : 'warning',
			'details' => $tracker_status,
		);

		// Verificar sistema de alertas
		$alert_config = $this->alert_system->get_config();
		$status['components']['alert_system'] = array(
			'status' => $alert_config['enabled'] ? 'healthy' : 'disabled',
			'details' => $alert_config,
		);

		// Verificar análisis predictivo
		$models_info = $this->predictive_analytics->get_models_info();
		$status['components']['predictive_analytics'] = array(
			'status' => $this->config['prediction_enabled'] ? 'healthy' : 'disabled',
			'details' => $models_info,
		);

		// Determinar estado general
		$component_statuses = array_column( $status['components'], 'status' );
		if ( in_array( 'warning', $component_statuses ) ) {
			$status['overall'] = 'warning';
		} elseif ( in_array( 'error', $component_statuses ) ) {
			$status['overall'] = 'error';
		}

		return $status;
	}
}