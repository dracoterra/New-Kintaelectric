<?php
/**
 * BCV Alert System - Sistema de alertas inteligentes
 *
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sistema de alertas inteligentes para fluctuaciones de tipos de cambio.
 * Monitorea cambios significativos y envía notificaciones automáticas.
 */
class Woocommerce_Venezuela_Suite_BCV_Alert_System {

	/** @var self */
	private static $instance = null;

	/** @var Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper */
	private $tracker_wrapper;

	/** @var array Configuración de alertas */
	private $config = array();

	/** @var array Historial de alertas */
	private $alert_history = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->tracker_wrapper = Woocommerce_Venezuela_Suite_BCV_Tracker_Wrapper::get_instance();
		$this->load_config();
		$this->load_alert_history();
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
	 * Carga configuración del sistema de alertas.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'enabled' => get_option( 'wvs_bcv_alerts_enabled', true ),
			'threshold_percentage' => get_option( 'wvs_bcv_alert_threshold', 5 ), // %
			'threshold_absolute' => get_option( 'wvs_bcv_alert_threshold_absolute', 1 ), // VES
			'email_enabled' => get_option( 'wvs_bcv_email_alerts', true ),
			'email_recipients' => get_option( 'wvs_bcv_email_recipients', array( get_option( 'admin_email' ) ) ),
			'cooldown_period' => get_option( 'wvs_bcv_alert_cooldown', 60 ), // minutos
			'alert_types' => get_option( 'wvs_bcv_alert_types', array(
				'significant_change' => true,
				'volatility_spike' => true,
				'tracker_down' => true,
				'prediction_alert' => false,
			) ),
		);
	}

	/**
	 * Carga historial de alertas.
	 *
	 * @return void
	 */
	private function load_alert_history() {
		$this->alert_history = get_option( 'wvs_bcv_alert_history', array() );
	}

	/**
	 * Verifica si hay cambios que requieren alertas.
	 *
	 * @return void
	 */
	public function check_for_alerts() {
		if ( ! $this->config['enabled'] ) {
			return;
		}

		$current_rate = $this->tracker_wrapper->get_current_rate();
		if ( ! $current_rate ) {
			$this->check_tracker_down_alert();
			return;
		}

		// Verificar cambio significativo
		if ( $this->config['alert_types']['significant_change'] ) {
			$this->check_significant_change_alert( $current_rate );
		}

		// Verificar pico de volatilidad
		if ( $this->config['alert_types']['volatility_spike'] ) {
			$this->check_volatility_spike_alert();
		}

		// Verificar alertas de predicción
		if ( $this->config['alert_types']['prediction_alert'] ) {
			$this->check_prediction_alerts();
		}
	}

	/**
	 * Verifica alerta por cambio significativo.
	 *
	 * @param float $current_rate Tasa actual.
	 * @return void
	 */
	private function check_significant_change_alert( $current_rate ) {
		$last_rate = get_option( 'wvs_bcv_last_alert_rate', $current_rate );
		$last_alert_time = get_option( 'wvs_bcv_last_alert_time', 0 );
		
		// Verificar período de cooldown
		if ( time() - $last_alert_time < $this->config['cooldown_period'] * 60 ) {
			return;
		}

		$change_percentage = abs( ( $current_rate - $last_rate ) / $last_rate ) * 100;
		$change_absolute = abs( $current_rate - $last_rate );

		// Verificar umbrales
		$threshold_percentage_met = $change_percentage >= $this->config['threshold_percentage'];
		$threshold_absolute_met = $change_absolute >= $this->config['threshold_absolute'];

		if ( $threshold_percentage_met || $threshold_absolute_met ) {
			$alert_data = array(
				'type' => 'significant_change',
				'title' => 'Cambio Significativo en Tasa BCV',
				'message' => sprintf(
					'La tasa BCV cambió de %s a %s VES (%.2f%% cambio, %s VES diferencia)',
					number_format( $last_rate, 2 ),
					number_format( $current_rate, 2 ),
					$change_percentage,
					number_format( $change_absolute, 2 )
				),
				'current_rate' => $current_rate,
				'previous_rate' => $last_rate,
				'change_percentage' => $change_percentage,
				'change_absolute' => $change_absolute,
				'timestamp' => current_time( 'mysql' ),
				'severity' => $this->calculate_severity( $change_percentage ),
			);

			$this->send_alert( $alert_data );
			$this->update_last_alert_data( $current_rate );
		}
	}

	/**
	 * Verifica alerta por pico de volatilidad.
	 *
	 * @return void
	 */
	private function check_volatility_spike_alert() {
		$history = $this->tracker_wrapper->get_rate_history( 7 ); // Últimos 7 días
		
		if ( count( $history ) < 5 ) {
			return;
		}

		$rates = array_column( $history, 'rate' );
		$volatility = $this->calculate_volatility( $rates );
		
		// Umbral de volatilidad alta (configurable)
		$volatility_threshold = get_option( 'wvs_bcv_volatility_threshold', 0.15 );
		
		if ( $volatility > $volatility_threshold ) {
			$alert_data = array(
				'type' => 'volatility_spike',
				'title' => 'Pico de Volatilidad Detectado',
				'message' => sprintf(
					'Se detectó alta volatilidad en las tasas BCV (%.2f%%). Esto puede indicar inestabilidad del mercado.',
					$volatility * 100
				),
				'volatility' => $volatility,
				'threshold' => $volatility_threshold,
				'data_points' => count( $rates ),
				'timestamp' => current_time( 'mysql' ),
				'severity' => 'high',
			);

			$this->send_alert( $alert_data );
		}
	}

	/**
	 * Verifica alerta por tracker caído.
	 *
	 * @return void
	 */
	private function check_tracker_down_alert() {
		$last_check = get_option( 'wvs_bcv_last_tracker_check', 0 );
		$current_time = time();
		
		// Solo alertar si han pasado más de 30 minutos sin datos
		if ( $current_time - $last_check < 1800 ) {
			return;
		}

		$tracker_status = $this->tracker_wrapper->get_tracker_status();
		
		if ( ! $tracker_status['available'] || ! $tracker_status['current_rate'] ) {
			$alert_data = array(
				'type' => 'tracker_down',
				'title' => 'BCV Tracker No Disponible',
				'message' => 'El sistema de obtención de tasas BCV no está funcionando correctamente. Se está usando tasa de respaldo.',
				'tracker_available' => $tracker_status['available'],
				'fallback_rate' => $tracker_status['manual_rate'] ?: $tracker_status['last_known_rate'],
				'timestamp' => current_time( 'mysql' ),
				'severity' => 'critical',
			);

			$this->send_alert( $alert_data );
		}

		update_option( 'wvs_bcv_last_tracker_check', $current_time );
	}

	/**
	 * Verifica alertas basadas en predicciones.
	 *
	 * @return void
	 */
	private function check_prediction_alerts() {
		$predictions = get_option( 'wvs_bcv_predictions', array() );
		
		if ( empty( $predictions['predictions']['consensus'] ) ) {
			return;
		}

		$consensus = $predictions['predictions']['consensus'];
		$current_rate = $this->tracker_wrapper->get_current_rate();
		
		if ( ! $current_rate ) {
			return;
		}

		// Verificar si las predicciones indican cambios significativos
		foreach ( $consensus['predictions'] as $prediction ) {
			$predicted_rate = $prediction['rate'];
			$change_percentage = abs( ( $predicted_rate - $current_rate ) / $current_rate ) * 100;
			
			// Alertar si la predicción indica un cambio mayor al 10%
			if ( $change_percentage > 10 && $prediction['confidence'] > 0.7 ) {
				$alert_data = array(
					'type' => 'prediction_alert',
					'title' => 'Predicción de Cambio Significativo',
					'message' => sprintf(
						'Los modelos predicen que la tasa BCV podría cambiar a %s VES en %d días (%.2f%% cambio desde la tasa actual)',
						number_format( $predicted_rate, 2 ),
						$prediction['day'],
						$change_percentage
					),
					'current_rate' => $current_rate,
					'predicted_rate' => $predicted_rate,
					'prediction_day' => $prediction['day'],
					'confidence' => $prediction['confidence'],
					'timestamp' => current_time( 'mysql' ),
					'severity' => 'medium',
				);

				$this->send_alert( $alert_data );
				break; // Solo una alerta de predicción por verificación
			}
		}
	}

	/**
	 * Envía una alerta.
	 *
	 * @param array $alert_data Datos de la alerta.
	 * @return bool True si se envió correctamente.
	 */
	private function send_alert( $alert_data ) {
		// Verificar si ya se envió una alerta similar recientemente
		if ( $this->is_duplicate_alert( $alert_data ) ) {
			return false;
		}

		// Registrar alerta en historial
		$this->alert_history[] = $alert_data;
		$this->save_alert_history();

		// Enviar por email si está habilitado
		if ( $this->config['email_enabled'] ) {
			$this->send_email_alert( $alert_data );
		}

		// Enviar notificación admin si está disponible
		$this->send_admin_notification( $alert_data );

		// Hook para otros sistemas
		do_action( 'wvs_bcv_alert_sent', $alert_data );

		return true;
	}

	/**
	 * Verifica si es una alerta duplicada.
	 *
	 * @param array $alert_data Datos de la alerta.
	 * @return bool True si es duplicada.
	 */
	private function is_duplicate_alert( $alert_data ) {
		$recent_alerts = array_slice( $this->alert_history, -5 ); // Últimas 5 alertas
		
		foreach ( $recent_alerts as $alert ) {
			if ( $alert['type'] === $alert_data['type'] && 
				 abs( strtotime( $alert['timestamp'] ) - strtotime( $alert_data['timestamp'] ) ) < 3600 ) { // 1 hora
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Envía alerta por email.
	 *
	 * @param array $alert_data Datos de la alerta.
	 * @return bool True si se envió correctamente.
	 */
	private function send_email_alert( $alert_data ) {
		$subject = sprintf( '[%s] %s', get_bloginfo( 'name' ), $alert_data['title'] );
		
		$message = $this->format_email_message( $alert_data );
		
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		$sent = false;
		foreach ( $this->config['email_recipients'] as $recipient ) {
			if ( is_email( $recipient ) ) {
				$sent = wp_mail( $recipient, $subject, $message, $headers ) || $sent;
			}
		}

		return $sent;
	}

	/**
	 * Formatea el mensaje de email.
	 *
	 * @param array $alert_data Datos de la alerta.
	 * @return string Mensaje formateado.
	 */
	private function format_email_message( $alert_data ) {
		$severity_colors = array(
			'low' => '#28a745',
			'medium' => '#ffc107',
			'high' => '#fd7e14',
			'critical' => '#dc3545',
		);

		$severity_color = $severity_colors[ $alert_data['severity'] ] ?? '#6c757d';
		
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $alert_data['title'] ); ?></title>
		</head>
		<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
			<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
				<div style="background: <?php echo esc_attr( $severity_color ); ?>; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
					<h2 style="margin: 0; font-size: 18px;"><?php echo esc_html( $alert_data['title'] ); ?></h2>
				</div>
				
				<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
					<p style="margin: 0; font-size: 16px;"><?php echo esc_html( $alert_data['message'] ); ?></p>
				</div>
				
				<div style="background: white; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;">
					<h3 style="margin-top: 0; color: #495057;">Detalles de la Alerta</h3>
					<ul style="margin: 0; padding-left: 20px;">
						<li><strong>Tipo:</strong> <?php echo esc_html( ucfirst( str_replace( '_', ' ', $alert_data['type'] ) ) ); ?></li>
						<li><strong>Severidad:</strong> <span style="color: <?php echo esc_attr( $severity_color ); ?>; font-weight: bold;"><?php echo esc_html( ucfirst( $alert_data['severity'] ) ); ?></span></li>
						<li><strong>Fecha:</strong> <?php echo esc_html( $alert_data['timestamp'] ); ?></li>
						<?php if ( isset( $alert_data['current_rate'] ) ): ?>
						<li><strong>Tasa Actual:</strong> <?php echo esc_html( number_format( $alert_data['current_rate'], 2 ) ); ?> VES</li>
						<?php endif; ?>
						<?php if ( isset( $alert_data['change_percentage'] ) ): ?>
						<li><strong>Cambio:</strong> <?php echo esc_html( number_format( $alert_data['change_percentage'], 2 ) ); ?>%</li>
						<?php endif; ?>
					</ul>
				</div>
				
				<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6; font-size: 12px; color: #6c757d;">
					<p>Esta alerta fue generada automáticamente por el sistema WooCommerce Venezuela Suite.</p>
					<p>Para configurar las alertas, visite el panel de administración de WordPress.</p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Envía notificación admin.
	 *
	 * @param array $alert_data Datos de la alerta.
	 * @return void
	 */
	private function send_admin_notification( $alert_data ) {
		$message = sprintf(
			'<strong>%s:</strong> %s',
			$alert_data['title'],
			$alert_data['message']
		);

		add_action( 'admin_notices', function() use ( $message, $alert_data ) {
			$class = 'notice notice-' . ( $alert_data['severity'] === 'critical' ? 'error' : 'warning' );
			printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
		});
	}

	/**
	 * Calcula la severidad de una alerta.
	 *
	 * @param float $change_percentage Porcentaje de cambio.
	 * @return string Severidad.
	 */
	private function calculate_severity( $change_percentage ) {
		if ( $change_percentage >= 20 ) {
			return 'critical';
		} elseif ( $change_percentage >= 10 ) {
			return 'high';
		} elseif ( $change_percentage >= 5 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Calcula la volatilidad de una serie de tasas.
	 *
	 * @param array $rates Serie de tasas.
	 * @return float Volatilidad.
	 */
	private function calculate_volatility( $rates ) {
		if ( count( $rates ) < 2 ) {
			return 0;
		}

		$mean = array_sum( $rates ) / count( $rates );
		$variance = array_sum( array_map( function( $rate ) use ( $mean ) {
			return pow( $rate - $mean, 2 );
		}, $rates ) ) / count( $rates );
		
		$std_dev = sqrt( $variance );
		return $std_dev / $mean;
	}

	/**
	 * Actualiza datos de la última alerta.
	 *
	 * @param float $rate Tasa actual.
	 * @return void
	 */
	private function update_last_alert_data( $rate ) {
		update_option( 'wvs_bcv_last_alert_rate', $rate );
		update_option( 'wvs_bcv_last_alert_time', time() );
	}

	/**
	 * Guarda historial de alertas.
	 *
	 * @return void
	 */
	private function save_alert_history() {
		// Mantener solo las últimas 100 alertas
		$this->alert_history = array_slice( $this->alert_history, -100 );
		update_option( 'wvs_bcv_alert_history', $this->alert_history );
	}

	/**
	 * Obtiene historial de alertas.
	 *
	 * @param int $limit Límite de alertas.
	 * @return array Historial de alertas.
	 */
	public function get_alert_history( $limit = 50 ) {
		return array_slice( $this->alert_history, -$limit );
	}

	/**
	 * Obtiene estadísticas de alertas.
	 *
	 * @return array Estadísticas.
	 */
	public function get_alert_statistics() {
		$stats = array(
			'total_alerts' => count( $this->alert_history ),
			'alerts_by_type' => array(),
			'alerts_by_severity' => array(),
			'last_alert' => null,
		);

		foreach ( $this->alert_history as $alert ) {
			// Por tipo
			$type = $alert['type'];
			$stats['alerts_by_type'][ $type ] = ( $stats['alerts_by_type'][ $type ] ?? 0 ) + 1;

			// Por severidad
			$severity = $alert['severity'];
			$stats['alerts_by_severity'][ $severity ] = ( $stats['alerts_by_severity'][ $severity ] ?? 0 ) + 1;

			// Última alerta
			if ( ! $stats['last_alert'] || strtotime( $alert['timestamp'] ) > strtotime( $stats['last_alert']['timestamp'] ) ) {
				$stats['last_alert'] = $alert;
			}
		}

		return $stats;
	}

	/**
	 * Actualiza configuración de alertas.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'enabled',
			'threshold_percentage',
			'threshold_absolute',
			'email_enabled',
			'email_recipients',
			'cooldown_period',
			'alert_types',
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
	 * @return array Configuración.
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Programa verificación periódica de alertas.
	 *
	 * @return void
	 */
	public function schedule_alerts() {
		if ( ! wp_next_scheduled( 'wvs_bcv_check_alerts' ) ) {
			wp_schedule_event( time(), 'hourly', 'wvs_bcv_check_alerts' );
		}
	}

	/**
	 * Cancela verificación periódica de alertas.
	 *
	 * @return void
	 */
	public function unschedule_alerts() {
		wp_clear_scheduled_hook( 'wvs_bcv_check_alerts' );
	}
}
