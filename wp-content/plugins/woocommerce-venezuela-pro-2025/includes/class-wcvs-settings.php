<?php

/**
 * WooCommerce Venezuela Suite 2025 - Settings Manager
 *
 * Gestor de configuraciones del plugin con integración
 * perfecta con WooCommerce.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Manager class
 */
class WCVS_Settings {

	/**
	 * Settings options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
		$this->load_settings();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_wcvs_save_settings', array( $this, 'ajax_save_settings' ) );
	}

	/**
	 * Load settings from database
	 */
	private function load_settings() {
		$this->options = get_option( 'wcvs_settings', $this->get_default_settings() );
	}

	/**
	 * Get default settings
	 *
	 * @return array
	 */
	private function get_default_settings() {
		return array(
			// Configuración General
			'general' => array(
				'enable_debug' => false,
				'log_level' => 'info',
				'auto_update_rates' => true,
				'update_frequency' => 'hourly'
			),

			// Configuración de Moneda
			'currency' => array(
				'base_currency' => 'VES',
				'show_dual_prices' => true,
				'price_position' => 'before',
				'decimal_places' => 2,
				'thousand_separator' => '.',
				'decimal_separator' => ','
			),

			// Configuración Fiscal
			'tax' => array(
				'iva_rate' => 16,
				'igtf_rate' => 3,
				'auto_update_tax_rates' => true,
				'tax_source' => 'seniat'
			),

			// Configuración de Notificaciones
			'notifications' => array(
				'email_notifications' => true,
				'whatsapp_notifications' => false,
				'sms_notifications' => false,
				'telegram_notifications' => false
			),

			// Configuración de Facturación
			'billing' => array(
				'electronic_billing' => true,
				'auto_generate_invoices' => true,
				'digital_signature' => false,
				'seniat_validation' => true
			)
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'wcvs_settings', 'wcvs_settings', array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		foreach ( $input as $section => $settings ) {
			$sanitized[ $section ] = array();
			
			foreach ( $settings as $key => $value ) {
				$sanitized[ $section ][ $key ] = $this->sanitize_setting( $key, $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize individual setting
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return mixed
	 */
	private function sanitize_setting( $key, $value ) {
		switch ( $key ) {
			case 'enable_debug':
			case 'auto_update_rates':
			case 'show_dual_prices':
			case 'auto_update_tax_rates':
			case 'electronic_billing':
			case 'auto_generate_invoices':
			case 'digital_signature':
			case 'seniat_validation':
			case 'email_notifications':
			case 'whatsapp_notifications':
			case 'sms_notifications':
			case 'telegram_notifications':
				return (bool) $value;

			case 'iva_rate':
			case 'igtf_rate':
			case 'decimal_places':
				return absint( $value );

			case 'log_level':
			case 'update_frequency':
			case 'base_currency':
			case 'price_position':
			case 'thousand_separator':
			case 'decimal_separator':
			case 'tax_source':
				return sanitize_text_field( $value );

			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Get setting value
	 *
	 * @param string $section
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public function get_setting( $section, $key, $default = null ) {
		if ( isset( $this->options[ $section ][ $key ] ) ) {
			return $this->options[ $section ][ $key ];
		}

		$defaults = $this->get_default_settings();
		if ( isset( $defaults[ $section ][ $key ] ) ) {
			return $defaults[ $section ][ $key ];
		}

		return $default;
	}

	/**
	 * Update setting value
	 *
	 * @param string $section
	 * @param string $key
	 * @param mixed  $value
	 */
	public function update_setting( $section, $key, $value ) {
		if ( ! isset( $this->options[ $section ] ) ) {
			$this->options[ $section ] = array();
		}

		$this->options[ $section ][ $key ] = $this->sanitize_setting( $key, $value );
		update_option( 'wcvs_settings', $this->options );
	}

	/**
	 * Get all settings
	 *
	 * @return array
	 */
	public function get_all_settings() {
		return $this->options;
	}

	/**
	 * AJAX handler for saving settings
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'wcvs_save_settings', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Permisos insuficientes', 'woocommerce-venezuela-pro-2025' ) );
		}

		$settings = $_POST['settings'];
		$sanitized_settings = $this->sanitize_settings( $settings );

		update_option( 'wcvs_settings', $sanitized_settings );
		$this->options = $sanitized_settings;

		wp_send_json_success( array(
			'message' => __( 'Configuraciones guardadas correctamente', 'woocommerce-venezuela-pro-2025' )
		));
	}
}
