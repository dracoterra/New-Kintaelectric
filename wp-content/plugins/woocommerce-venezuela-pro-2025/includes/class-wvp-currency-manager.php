<?php
/**
 * Gestor Centralizado de Monedas - WooCommerce Venezuela Pro 2025
 * 
 * Sistema unificado para conversión de monedas USD ↔ VES
 * Basado en las mejores prácticas del plugin original
 * 
 * @package WooCommerce_Venezuela_Pro_2025
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Currency_Manager {
	
	/**
	 * Instancia única del gestor (Singleton)
	 * 
	 * @var WVP_Currency_Manager
	 */
	private static $instance = null;
	
	/**
	 * Tasa BCV actual
	 * 
	 * @var float
	 */
	private $bcv_rate = null;
	
	/**
	 * Tasa de emergencia cuando BCV no está disponible
	 * 
	 * @var float
	 */
	private $emergency_rate = 36.5;
	
	/**
	 * Duración del cache en segundos
	 * 
	 * @var int
	 */
	private $cache_duration = 3600; // 1 hora
	
	/**
	 * Obtener instancia única del gestor
	 * 
	 * @return WVP_Currency_Manager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor privado (Singleton)
	 */
	private function __construct() {
		$this->load_bcv_rate();
		$this->init_hooks();
	}
	
	/**
	 * Inicializar hooks de WordPress
	 */
	private function init_hooks() {
		// Hook para escuchar actualizaciones del BCV
		add_action( 'wvp_bcv_rate_updated', array( $this, 'refresh_bcv_rate' ), 10, 1 );
		
		// AJAX para conversión de precios
		add_action( 'wp_ajax_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		add_action( 'wp_ajax_nopriv_wvp_convert_price', array( $this, 'ajax_convert_price' ) );
		
		// Hook para limpiar cache cuando sea necesario
		add_action( 'wvp_clear_currency_cache', array( $this, 'clear_cache' ) );
	}
	
	/**
	 * Cargar tasa BCV desde BCV Dólar Tracker o fallback
	 */
	private function load_bcv_rate() {
		// Intentar obtener desde BCV Dólar Tracker primero
		if ( class_exists( 'BCV_Dolar_Tracker' ) ) {
			$rate = BCV_Dolar_Tracker::get_rate();
			if ( $rate && $rate > 0 ) {
				$this->bcv_rate = $rate;
				return;
			}
		}
		
		// Fallback a opción manual
		$manual_rate = get_option( 'wvp_bcv_rate', 0 );
		if ( $manual_rate > 0 ) {
			$this->bcv_rate = $manual_rate;
			return;
		}
		
		// Usar tasa de emergencia
		$this->bcv_rate = $this->emergency_rate;
	}
	
	/**
	 * Obtener tasa BCV actual
	 * 
	 * @return float Tasa BCV
	 */
	public function get_bcv_rate() {
		return $this->bcv_rate;
	}
	
	/**
	 * Convertir precio entre monedas
	 * 
	 * @param float $amount Cantidad a convertir
	 * @param string $from Moneda origen (USD/VES)
	 * @param string $to Moneda destino (USD/VES)
	 * @return float Cantidad convertida
	 */
	public function convert_price( $amount, $from = 'USD', $to = 'VES' ) {
		// Validar parámetros
		if ( ! is_numeric( $amount ) || $amount < 0 ) {
			return 0;
		}
		
		if ( $from === $to ) {
			return $amount;
		}
		
		// Conversión USD a VES
		if ( $from === 'USD' && $to === 'VES' ) {
			return $amount * $this->bcv_rate;
		}
		
		// Conversión VES a USD
		if ( $from === 'VES' && $to === 'USD' ) {
			return $amount / $this->bcv_rate;
		}
		
		// Conversión no soportada
		return $amount;
	}
	
	/**
	 * Formatear precio según la moneda
	 * 
	 * @param float $amount Cantidad a formatear
	 * @param string $currency Moneda (USD/VES)
	 * @param bool $show_symbol Mostrar símbolo de moneda
	 * @return string Precio formateado
	 */
	public function format_currency( $amount, $currency = 'USD', $show_symbol = true ) {
		$formatted_amount = number_format( $amount, 2, ',', '.' );
		
		if ( ! $show_symbol ) {
			return $formatted_amount;
		}
		
		switch ( $currency ) {
			case 'USD':
				return '$' . $formatted_amount;
			case 'VES':
				return 'Bs. ' . $formatted_amount;
			default:
				return $formatted_amount;
		}
	}
	
	/**
	 * Obtener símbolo de moneda
	 * 
	 * @param string $currency Moneda
	 * @return string Símbolo
	 */
	public function get_currency_symbol( $currency ) {
		switch ( $currency ) {
			case 'USD':
				return '$';
			case 'VES':
				return 'Bs.';
			default:
				return '';
		}
	}
	
	/**
	 * Verificar si la tasa BCV está disponible
	 * 
	 * @return bool True si está disponible
	 */
	public function is_rate_available() {
		return $this->bcv_rate > 0;
	}
	
	/**
	 * Obtener información de la tasa BCV
	 * 
	 * @return array Información de la tasa
	 */
	public function get_rate_info() {
		$last_update = get_option( 'bcv_last_update', '' );
		
		return array(
			'rate' => $this->bcv_rate,
			'is_available' => $this->is_rate_available(),
			'last_update' => $last_update,
			'source' => $this->get_rate_source(),
			'emergency_rate' => $this->emergency_rate
		);
	}
	
	/**
	 * Obtener fuente de la tasa
	 * 
	 * @return string Fuente de la tasa
	 */
	private function get_rate_source() {
		if ( class_exists( 'BCV_Dolar_Tracker' ) ) {
			$rate = BCV_Dolar_Tracker::get_rate();
			if ( $rate && $rate > 0 ) {
				return 'BCV_Dolar_Tracker';
			}
		}
		
		$manual_rate = get_option( 'wvp_bcv_rate', 0 );
		if ( $manual_rate > 0 ) {
			return 'Manual';
		}
		
		return 'Emergency';
	}
	
	/**
	 * Actualizar tasa BCV
	 * 
	 * @param float $new_rate Nueva tasa
	 * @return bool True si se actualizó correctamente
	 */
	public function update_bcv_rate( $new_rate ) {
		if ( ! is_numeric( $new_rate ) || $new_rate <= 0 ) {
			return false;
		}
		
		$this->bcv_rate = $new_rate;
		update_option( 'wvp_bcv_rate', $new_rate );
		
		// Disparar hook de actualización
		do_action( 'wvp_bcv_rate_updated', $new_rate );
		
		return true;
	}
	
	/**
	 * Refrescar tasa BCV cuando se actualiza
	 * 
	 * @param float $new_rate Nueva tasa
	 */
	public function refresh_bcv_rate( $new_rate ) {
		$this->bcv_rate = $new_rate;
		$this->clear_cache();
	}
	
	/**
	 * Limpiar cache de conversiones
	 */
	public function clear_cache() {
		// Limpiar transients relacionados con conversiones
		delete_transient( 'wvp_currency_cache_' . md5( 'usd_to_ves' ) );
		delete_transient( 'wvp_currency_cache_' . md5( 'ves_to_usd' ) );
		
		// Limpiar cache de BCV
		delete_transient( 'wvp_bcv_rate_cache' );
	}
	
	/**
	 * AJAX: Convertir precio
	 */
	public function ajax_convert_price() {
		// Verificar nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_currency_nonce' ) ) {
			wp_send_json_error( 'Nonce inválido' );
		}
		
		$amount = floatval( $_POST['amount'] );
		$from = sanitize_text_field( $_POST['from'] );
		$to = sanitize_text_field( $_POST['to'] );
		
		$converted_amount = $this->convert_price( $amount, $from, $to );
		$formatted_amount = $this->format_currency( $converted_amount, $to );
		
		wp_send_json_success( array(
			'converted_amount' => $converted_amount,
			'formatted_amount' => $formatted_amount,
			'rate' => $this->get_bcv_rate(),
			'rate_info' => $this->get_rate_info()
		) );
	}
	
	/**
	 * Obtener configuración de monedas soportadas
	 * 
	 * @return array Monedas soportadas
	 */
	public function get_supported_currencies() {
		return array(
			'USD' => array(
				'name' => 'Dólar Americano',
				'symbol' => '$',
				'code' => 'USD'
			),
			'VES' => array(
				'name' => 'Bolívar Venezolano',
				'symbol' => 'Bs.',
				'code' => 'VES'
			)
		);
	}
	
	/**
	 * Obtener configuración por defecto
	 * 
	 * @return array Configuración por defecto
	 */
	public function get_default_settings() {
		return array(
			'base_currency' => 'USD',
			'display_currency' => 'VES',
			'bcv_rate' => $this->emergency_rate,
			'auto_update_bcv' => true,
			'cache_duration' => $this->cache_duration,
			'format_options' => array(
				'decimal_places' => 2,
				'decimal_separator' => ',',
				'thousands_separator' => '.',
				'show_symbol' => true
			)
		);
	}
}

// Inicializar el gestor
WVP_Currency_Manager::get_instance();
