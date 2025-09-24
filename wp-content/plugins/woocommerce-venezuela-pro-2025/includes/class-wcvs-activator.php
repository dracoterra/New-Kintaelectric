<?php

/**
 * WooCommerce Venezuela Suite 2025 - Activator
 *
 * Maneja la activación del plugin y creación
 * de tablas y configuraciones iniciales.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activator class
 */
class WCVS_Activator {

	/**
	 * Activate the plugin
	 */
	public static function activate() {
		// Check WooCommerce dependency
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( WCVS_PLUGIN_FILE ) );
			wp_die( 
				__( 'WooCommerce Venezuela Suite 2025 requiere WooCommerce para funcionar. Por favor, instala y activa WooCommerce primero.', 'woocommerce-venezuela-pro-2025' ),
				__( 'Error de Activación', 'woocommerce-venezuela-pro-2025' ),
				array( 'back_link' => true )
			);
		}

		// Create database tables
		self::create_tables();

		// Set default options
		self::set_default_options();

		// Create default pages
		self::create_default_pages();

		// Schedule cron events
		self::schedule_cron_events();

		// Log activation
		error_log( 'WooCommerce Venezuela Suite 2025 activated successfully' );
	}

	/**
	 * Create database tables
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Table for tax rates history
		$table_tax_rates = $wpdb->prefix . 'wcvs_tax_rates_history';
		$sql_tax_rates = "CREATE TABLE $table_tax_rates (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			tax_type varchar(50) NOT NULL,
			rate decimal(10,4) NOT NULL,
			effective_date datetime NOT NULL,
			source varchar(100) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY tax_type (tax_type),
			KEY effective_date (effective_date)
		) $charset_collate;";

		// Table for exchange rates history
		$table_exchange_rates = $wpdb->prefix . 'wcvs_exchange_rates_history';
		$sql_exchange_rates = "CREATE TABLE $table_exchange_rates (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			from_currency varchar(3) NOT NULL,
			to_currency varchar(3) NOT NULL,
			rate decimal(15,8) NOT NULL,
			source varchar(100) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY from_currency (from_currency),
			KEY to_currency (to_currency),
			KEY created_at (created_at)
		) $charset_collate;";

		// Table for electronic invoices
		$table_invoices = $wpdb->prefix . 'wcvs_electronic_invoices';
		$sql_invoices = "CREATE TABLE $table_invoices (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			order_id bigint(20) NOT NULL,
			invoice_number varchar(50) NOT NULL,
			invoice_date datetime NOT NULL,
			total_amount decimal(15,2) NOT NULL,
			tax_amount decimal(15,2) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			seniat_response text,
			digital_signature text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY invoice_number (invoice_number),
			KEY order_id (order_id),
			KEY status (status)
		) $charset_collate;";

		// Table for module logs
		$table_module_logs = $wpdb->prefix . 'wcvs_module_logs';
		$sql_module_logs = "CREATE TABLE $table_module_logs (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			module_id varchar(50) NOT NULL,
			action varchar(50) NOT NULL,
			status varchar(20) NOT NULL,
			message text,
			context text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY module_id (module_id),
			KEY action (action),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_tax_rates );
		dbDelta( $sql_exchange_rates );
		dbDelta( $sql_invoices );
		dbDelta( $sql_module_logs );
	}

	/**
	 * Set default options
	 */
	private static function set_default_options() {
		// Plugin version
		update_option( 'wcvs_version', WCVS_VERSION );

		// Default settings
		$default_settings = array(
			'general' => array(
				'enable_debug' => false,
				'log_level' => 'info',
				'auto_update_rates' => true,
				'update_frequency' => 'hourly'
			),
			'currency' => array(
				'base_currency' => 'VES',
				'show_dual_prices' => true,
				'price_position' => 'before',
				'decimal_places' => 2,
				'thousand_separator' => '.',
				'decimal_separator' => ','
			),
			'tax' => array(
				'iva_rate' => 16,
				'igtf_rate' => 3,
				'auto_update_tax_rates' => true,
				'tax_source' => 'seniat'
			),
			'notifications' => array(
				'email_notifications' => true,
				'whatsapp_notifications' => false,
				'sms_notifications' => false,
				'telegram_notifications' => false
			),
			'billing' => array(
				'electronic_billing' => true,
				'auto_generate_invoices' => true,
				'digital_signature' => false,
				'seniat_validation' => true
			)
		);

		update_option( 'wcvs_settings', $default_settings );

		// Active modules (core modules activated by default)
		$default_active_modules = array(
			'tax_system' => true,
			'payment_gateways' => true,
			'currency_manager' => true,
			'shipping_methods' => true,
			'checkout_fields' => true,
			'electronic_billing' => true
		);

		update_option( 'wcvs_active_modules', $default_active_modules );

		// Exchange rate sources
		$default_sources = array(
			'bcv' => array(
				'name' => 'Banco Central de Venezuela',
				'url' => 'https://www.bcv.org.ve/',
				'active' => true,
				'priority' => 1
			),
			'dolar_today' => array(
				'name' => 'Dólar Today',
				'url' => 'https://dolartoday.com/',
				'active' => true,
				'priority' => 2
			),
			'en_paralelo' => array(
				'name' => 'EnParaleloVzla',
				'url' => 'https://enparalelovzla.com/',
				'active' => true,
				'priority' => 3
			)
		);

		update_option( 'wcvs_exchange_sources', $default_sources );
	}

	/**
	 * Create default pages
	 */
	private static function create_default_pages() {
		// Create help page
		$help_page = array(
			'post_title' => __( 'Ayuda - WooCommerce Venezuela Suite', 'woocommerce-venezuela-pro-2025' ),
			'post_content' => __( 'Esta página contiene la documentación y ayuda para configurar WooCommerce Venezuela Suite.', 'woocommerce-venezuela-pro-2025' ),
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_name' => 'wcvs-help'
		);

		$help_page_id = wp_insert_post( $help_page );
		if ( $help_page_id ) {
			update_option( 'wcvs_help_page_id', $help_page_id );
		}
	}

	/**
	 * Schedule cron events
	 */
	private static function schedule_cron_events() {
		// Schedule exchange rate updates
		if ( ! wp_next_scheduled( 'wcvs_update_exchange_rates' ) ) {
			wp_schedule_event( time(), 'hourly', 'wcvs_update_exchange_rates' );
		}

		// Schedule tax rate updates
		if ( ! wp_next_scheduled( 'wcvs_update_tax_rates' ) ) {
			wp_schedule_event( time(), 'daily', 'wcvs_update_tax_rates' );
		}

		// Schedule log cleanup
		if ( ! wp_next_scheduled( 'wcvs_cleanup_logs' ) ) {
			wp_schedule_event( time(), 'weekly', 'wcvs_cleanup_logs' );
		}
	}
}
