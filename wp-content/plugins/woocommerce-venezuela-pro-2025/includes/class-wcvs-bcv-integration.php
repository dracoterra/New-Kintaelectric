<?php

/**
 * WooCommerce Venezuela Suite 2025 - BCV Integration
 *
 * Integración con el plugin BCV Dólar Tracker para obtener tasas de cambio automáticas
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BCV Integration class
 */
class WCVS_BCV_Integration {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * BCV plugin available
	 *
	 * @var bool
	 */
	private $bcv_available = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->check_bcv_availability();
		$this->init_hooks();
	}

	/**
	 * Check if BCV plugin is available
	 */
	private function check_bcv_availability() {
		$this->bcv_available = class_exists( 'BCV_Dolar_Tracker' );
		
		if ( $this->bcv_available ) {
			$this->core->logger->info( 'BCV Dólar Tracker plugin detected and available' );
		} else {
			$this->core->logger->warning( 'BCV Dólar Tracker plugin not available' );
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		if ( $this->bcv_available ) {
			// Hook into BCV rate updates
			add_action( 'wvp_bcv_rate_updated', array( $this, 'handle_bcv_rate_update' ), 10, 2 );
			
			// Add BCV sync to cron
			add_action( 'wcvs_bcv_sync', array( $this, 'sync_bcv_rate' ) );
			
			// Schedule BCV sync
			if ( ! wp_next_scheduled( 'wcvs_bcv_sync' ) ) {
				wp_schedule_event( time(), 'hourly', 'wcvs_bcv_sync' );
			}
		}

		// Add admin notices
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Get current USD to VES rate
	 *
	 * @return float|false Current rate or false if not available
	 */
	public function get_current_rate() {
		if ( ! $this->bcv_available ) {
			return $this->get_fallback_rate();
		}

		$rate = BCV_Dolar_Tracker::get_rate();
		
		if ( $rate && $rate > 0 ) {
			$this->core->logger->info( 'BCV rate obtained: ' . $rate );
			return $rate;
		}

		$this->core->logger->warning( 'BCV rate not available, using fallback' );
		return $this->get_fallback_rate();
	}

	/**
	 * Get fallback rate from options
	 *
	 * @return float|false Fallback rate or false
	 */
	private function get_fallback_rate() {
		$fallback_rate = get_option( 'wcvs_fallback_usd_rate', false );
		
		if ( $fallback_rate && $fallback_rate > 0 ) {
			$this->core->logger->info( 'Using fallback rate: ' . $fallback_rate );
			return floatval( $fallback_rate );
		}

		$this->core->logger->error( 'No fallback rate available' );
		return false;
	}

	/**
	 * Convert USD to VES
	 *
	 * @param float $usd_amount Amount in USD
	 * @return float|false Amount in VES or false if rate not available
	 */
	public function convert_usd_to_ves( $usd_amount ) {
		$rate = $this->get_current_rate();
		
		if ( ! $rate ) {
			return false;
		}

		$ves_amount = $usd_amount * $rate;
		
		$this->core->logger->info( sprintf( 
			'Converted USD %s to VES %s (rate: %s)', 
			$usd_amount, 
			$ves_amount, 
			$rate 
		));

		return $ves_amount;
	}

	/**
	 * Convert VES to USD
	 *
	 * @param float $ves_amount Amount in VES
	 * @return float|false Amount in USD or false if rate not available
	 */
	public function convert_ves_to_usd( $ves_amount ) {
		$rate = $this->get_current_rate();
		
		if ( ! $rate ) {
			return false;
		}

		$usd_amount = $ves_amount / $rate;
		
		$this->core->logger->info( sprintf( 
			'Converted VES %s to USD %s (rate: %s)', 
			$ves_amount, 
			$usd_amount, 
			$rate 
		));

		return $usd_amount;
	}

	/**
	 * Handle BCV rate update
	 *
	 * @param float $new_rate New rate
	 * @param float $old_rate Old rate
	 */
	public function handle_bcv_rate_update( $new_rate, $old_rate ) {
		$this->core->logger->info( sprintf( 
			'BCV rate updated from %s to %s', 
			$old_rate, 
			$new_rate 
		));

		// Update WooCommerce currency rates if needed
		$this->update_woocommerce_currency_rates( $new_rate );

		// Trigger custom action
		do_action( 'wcvs_bcv_rate_updated', $new_rate, $old_rate );
	}

	/**
	 * Update WooCommerce currency rates
	 *
	 * @param float $rate New USD to VES rate
	 */
	private function update_woocommerce_currency_rates( $rate ) {
		// Update WooCommerce currency rates
		$currency_rates = get_option( 'woocommerce_currency_rates', array() );
		$currency_rates['USD_VES'] = $rate;
		$currency_rates['VES_USD'] = 1 / $rate;
		update_option( 'woocommerce_currency_rates', $currency_rates );

		$this->core->logger->info( 'Updated WooCommerce currency rates' );
	}

	/**
	 * Sync BCV rate manually
	 */
	public function sync_bcv_rate() {
		if ( ! $this->bcv_available ) {
			return;
		}

		$success = BCV_Dolar_Tracker::sync_with_wvp();
		
		if ( $success ) {
			$this->core->logger->info( 'BCV rate sync completed successfully' );
		} else {
			$this->core->logger->error( 'BCV rate sync failed' );
		}
	}

	/**
	 * Get BCV status
	 *
	 * @return array BCV status information
	 */
	public function get_bcv_status() {
		$status = array(
			'available' => $this->bcv_available,
			'current_rate' => $this->get_current_rate(),
			'fallback_rate' => get_option( 'wcvs_fallback_usd_rate', false ),
			'last_update' => get_option( 'wcvs_last_rate_update', false ),
			'next_sync' => wp_next_scheduled( 'wcvs_bcv_sync' )
		);

		return $status;
	}

	/**
	 * Set fallback rate
	 *
	 * @param float $rate Fallback rate
	 * @return bool True if successful
	 */
	public function set_fallback_rate( $rate ) {
		if ( ! is_numeric( $rate ) || $rate <= 0 ) {
			return false;
		}

		$success = update_option( 'wcvs_fallback_usd_rate', floatval( $rate ) );
		
		if ( $success ) {
			$this->core->logger->info( 'Fallback rate set to: ' . $rate );
		}

		return $success;
	}

	/**
	 * Admin notices
	 */
	public function admin_notices() {
		if ( ! $this->bcv_available ) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php _e( 'WooCommerce Venezuela Suite:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
					<?php _e( 'El plugin BCV Dólar Tracker no está disponible. Las tasas de cambio se obtendrán de fuentes alternativas.', 'woocommerce-venezuela-pro-2025' ); ?>
					<a href="<?php echo admin_url( 'plugin-install.php?s=bcv+dolar+tracker&tab=search&type=term' ); ?>">
						<?php _e( 'Instalar BCV Dólar Tracker', 'woocommerce-venezuela-pro-2025' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get rate history
	 *
	 * @param int $days Number of days to retrieve
	 * @return array Rate history
	 */
	public function get_rate_history( $days = 7 ) {
		if ( ! $this->bcv_available ) {
			return array();
		}

		// This would integrate with BCV plugin's database
		// For now, return empty array
		return array();
	}

	/**
	 * Format currency amount
	 *
	 * @param float $amount Amount to format
	 * @param string $currency Currency code
	 * @return string Formatted amount
	 */
	public function format_currency( $amount, $currency = 'VES' ) {
		if ( $currency === 'VES' ) {
			return number_format( $amount, 2, ',', '.' ) . ' VES';
		} elseif ( $currency === 'USD' ) {
			return '$' . number_format( $amount, 2, '.', ',' );
		}

		return number_format( $amount, 2 );
	}

	/**
	 * Get currency symbol
	 *
	 * @param string $currency Currency code
	 * @return string Currency symbol
	 */
	public function get_currency_symbol( $currency ) {
		$symbols = array(
			'VES' => 'Bs.',
			'USD' => '$',
			'EUR' => '€'
		);

		return isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency;
	}

	/**
	 * Initialize frontend functionality
	 */
	public function init_frontend() {
		if ( $this->bcv_available ) {
			// Add frontend currency conversion
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
			add_action( 'wp_ajax_wcvs_get_current_rate', array( $this, 'ajax_get_current_rate' ) );
			add_action( 'wp_ajax_nopriv_wcvs_get_current_rate', array( $this, 'ajax_get_current_rate' ) );
		}
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'wcvs-currency-converter',
			WCVS_PLUGIN_URL . 'includes/js/currency-converter.js',
			array( 'jquery' ),
			WCVS_VERSION,
			true
		);

		wp_localize_script( 'wcvs-currency-converter', 'wcvs_currency', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_currency_nonce' ),
			'current_rate' => $this->get_current_rate(),
			'fallback_rate' => get_option( 'wcvs_fallback_usd_rate', false )
		));
	}

	/**
	 * AJAX get current rate
	 */
	public function ajax_get_current_rate() {
		check_ajax_referer( 'wcvs_currency_nonce', 'nonce' );

		$rate = $this->get_current_rate();
		
		wp_send_json_success( array(
			'rate' => $rate,
			'timestamp' => current_time( 'mysql' )
		));
	}
}
