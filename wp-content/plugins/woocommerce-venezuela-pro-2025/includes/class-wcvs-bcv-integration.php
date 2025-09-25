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
		// Check if BCV Dólar Tracker plugin is active
		$this->bcv_available = is_plugin_active( 'bcv-dolar-tracker/bcv-dolar-tracker.php' );
	}

	/**
	 * Check if BCV integration is available
	 *
	 * @return bool
	 */
	public function is_available() {
		return $this->bcv_available;
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
		
		// Add AJAX hooks for rate refresh
		add_action( 'wp_ajax_wcvs_refresh_rate', array( $this, 'ajax_refresh_rate' ) );
		add_action( 'wp_ajax_wcvs_get_rate_stats', array( $this, 'ajax_get_rate_stats' ) );
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

		// Try to get rate from BCV plugin
		$rate = false;
		
		// Method 1: Try BCV_Dolar_Tracker class
		if ( class_exists( 'BCV_Dolar_Tracker' ) ) {
			$rate = BCV_Dolar_Tracker::get_rate();
		}
		
		// Method 2: Try BCV_Dolar_Tracker function
		if ( ! $rate && function_exists( 'bcv_get_rate' ) ) {
			$rate = bcv_get_rate();
		}
		
		// Method 3: Try BCV_Dolar_Tracker option
		if ( ! $rate ) {
			$rate = get_option( 'bcv_dolar_rate', false );
		}
		
		// Method 4: Try BCV_Dolar_Tracker transient
		if ( ! $rate ) {
			$rate = get_transient( 'bcv_dolar_rate' );
		}
		
		if ( $rate && $rate > 0 ) {
			$this->core->logger->info( 'BCV rate obtained: ' . $rate );
			// Update last successful rate
			update_option( 'wcvs_last_successful_rate', $rate );
			update_option( 'wcvs_last_rate_update', current_time( 'mysql' ) );
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
			WCVS_PLUGIN_URL . 'admin/js/currency-converter.js',
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

	/**
	 * Get rate display widget HTML
	 *
	 * @return string HTML for rate display widget
	 */
	public function get_rate_display_widget() {
		$rate = $this->get_current_rate();
		$status = $this->get_bcv_status();
		$last_update = $status['last_update'];
		
		$widget_html = '<div class="wcvs-rate-widget">';
		$widget_html .= '<div class="wcvs-rate-header">';
		$widget_html .= '<h3>' . __('Dólar del Día', 'woocommerce-venezuela-pro-2025') . '</h3>';
		
		if ($status['available']) {
			$widget_html .= '<span class="wcvs-status-badge wcvs-status-success">' . __('BCV Activo', 'woocommerce-venezuela-pro-2025') . '</span>';
		} else {
			$widget_html .= '<span class="wcvs-status-badge wcvs-status-warning">' . __('Modo Respaldo', 'woocommerce-venezuela-pro-2025') . '</span>';
		}
		
		$widget_html .= '</div>';
		
		if ($rate) {
			$widget_html .= '<div class="wcvs-rate-display">';
			$widget_html .= '<div class="wcvs-rate-value">' . number_format($rate, 2, ',', '.') . '</div>';
			$widget_html .= '<div class="wcvs-rate-currency">Bs./USD</div>';
			$widget_html .= '</div>';
			
			if ($last_update) {
				$widget_html .= '<div class="wcvs-rate-info">';
				$widget_html .= '<span class="wcvs-rate-label">' . __('Última actualización:', 'woocommerce-venezuela-pro-2025') . '</span>';
				$widget_html .= '<span class="wcvs-rate-time">' . date('d/m/Y H:i', strtotime($last_update)) . '</span>';
				$widget_html .= '</div>';
			}
		} else {
			$widget_html .= '<div class="wcvs-rate-error">';
			$widget_html .= '<span class="dashicons dashicons-warning"></span>';
			$widget_html .= __('Tasa no disponible', 'woocommerce-venezuela-pro-2025');
			$widget_html .= '</div>';
		}
		
		$widget_html .= '<div class="wcvs-rate-actions">';
		$widget_html .= '<button type="button" class="button button-secondary" onclick="wcvsRefreshRate()">';
		$widget_html .= '<span class="dashicons dashicons-update"></span> ';
		$widget_html .= __('Actualizar', 'woocommerce-venezuela-pro-2025');
		$widget_html .= '</button>';
		$widget_html .= '</div>';
		
		$widget_html .= '</div>';
		
		return $widget_html;
	}

	/**
	 * Refresh rate manually
	 */
	public function refresh_rate() {
		if ($this->bcv_available) {
			// Try to trigger BCV plugin refresh
			if (class_exists('BCV_Dolar_Tracker')) {
				BCV_Dolar_Tracker::sync_with_wvp();
			}
		}
		
		// Clear any cached rates
		delete_transient('wcvs_current_rate');
		
		// Get fresh rate
		$rate = $this->get_current_rate();
		
		return $rate;
	}

	/**
	 * AJAX refresh rate
	 */
	public function ajax_refresh_rate() {
		check_ajax_referer('wcvs_currency_nonce', 'nonce');
		
		$rate = $this->refresh_rate();
		
		wp_send_json_success(array(
			'rate' => $rate,
			'formatted_rate' => $rate ? number_format($rate, 2, ',', '.') : false,
			'timestamp' => current_time('mysql')
		));
	}

	/**
	 * Get rate statistics
	 *
	 * @return array Rate statistics
	 */
	public function get_rate_statistics() {
		$stats = array(
			'current_rate' => $this->get_current_rate(),
			'fallback_rate' => get_option('wcvs_fallback_usd_rate', false),
			'last_update' => get_option('wcvs_last_rate_update', false),
			'last_successful_rate' => get_option('wcvs_last_successful_rate', false),
			'bcv_available' => $this->bcv_available,
			'next_sync' => wp_next_scheduled('wcvs_bcv_sync')
		);
		
		return $stats;
	}

	/**
	 * AJAX get rate statistics
	 */
	public function ajax_get_rate_stats() {
		check_ajax_referer('wcvs_currency_nonce', 'nonce');
		
		$stats = $this->get_rate_statistics();
		
		wp_send_json_success($stats);
	}
}
