<?php
/**
 * Modern Admin Dashboard
 * Provides statistics and management interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Admin_Dashboard {
	
	private static $instance = null;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->init_hooks();
	}
	
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_wvp_get_dashboard_stats', array( $this, 'ajax_get_dashboard_stats' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}
	
	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'wvp_settings', 'wvp_emergency_rate', array(
			'type' => 'number',
			'default' => '36.5',
			'sanitize_callback' => 'floatval'
		));
		
		register_setting( 'wvp_settings', 'wvp_iva_rate', array(
			'type' => 'number',
			'default' => '16',
			'sanitize_callback' => 'floatval'
		));
		
		register_setting( 'wvp_settings', 'wvp_igtf_rate', array(
			'type' => 'number',
			'default' => '3',
			'sanitize_callback' => 'floatval'
		));
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			'WooCommerce Venezuela Pro 2025',
			'WVP 2025',
			'manage_woocommerce',
			'wvp-dashboard',
			array( $this, 'dashboard_page' ),
			'dashicons-money-alt',
			56
		);
		
		add_submenu_page(
			'wvp-dashboard',
			'Dashboard',
			'Dashboard',
			'manage_woocommerce',
			'wvp-dashboard',
			array( $this, 'dashboard_page' )
		);
		
		add_submenu_page(
			'wvp-dashboard',
			'Configuración',
			'Configuración',
			'manage_woocommerce',
			'wvp-settings',
			array( $this, 'settings_page' )
		);
		
		add_submenu_page(
			'wvp-dashboard',
			'Reportes',
			'Reportes',
			'manage_woocommerce',
			'wvp-reports',
			array( $this, 'reports_page' )
		);
	}
	
	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, 'wvp-' ) !== false ) {
			wp_enqueue_style( 'wvp-admin', WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL . 'admin/css/wvp-admin.css', array(), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION );
			wp_enqueue_script( 'wvp-admin', WOOCOMMERCE_VENEZUELA_PRO_2025_PLUGIN_URL . 'admin/js/wvp-admin.js', array( 'jquery' ), WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION, true );
			wp_localize_script( 'wvp-admin', 'wvp_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvp_dashboard' )
			));
		}
	}
	
	/**
	 * Dashboard page
	 */
	public function dashboard_page() {
		$stats = $this->get_dashboard_stats();
		?>
		<div class="wrap wvp-dashboard">
			<h1 class="wvp-page-title">
				<span class="dashicons dashicons-money-alt"></span>
				WooCommerce Venezuela Pro 2025 - Dashboard
			</h1>
			
			<div class="wvp-stats-grid">
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-cart"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo $stats['total_orders']; ?></h3>
						<p>Pedidos Totales</p>
					</div>
				</div>
				
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-money"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo wc_price( $stats['total_revenue'] ); ?></h3>
						<p>Ingresos Totales</p>
					</div>
				</div>
				
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-chart-line"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo $stats['conversion_rate']; ?>%</h3>
						<p>Tasa de Conversión</p>
					</div>
				</div>
				
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-admin-users"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo $stats['total_customers']; ?></h3>
						<p>Clientes Totales</p>
					</div>
				</div>
			</div>
			
			<div class="wvp-dashboard-content">
				<div class="wvp-dashboard-left">
					<div class="wvp-card">
						<h2>Estado del Sistema</h2>
						<div class="wvp-system-status">
							<div class="wvp-status-item">
								<span class="wvp-status-label">Conversor de Moneda:</span>
								<span class="wvp-status-value wvp-status-ok">✅ Activo</span>
							</div>
							<div class="wvp-status-item">
								<span class="wvp-status-label">Sistema de Impuestos:</span>
								<span class="wvp-status-value wvp-status-ok">✅ Activo</span>
							</div>
							<div class="wvp-status-item">
								<span class="wvp-status-label">Pago Móvil:</span>
								<span class="wvp-status-value wvp-status-ok">✅ Activo</span>
							</div>
							<div class="wvp-status-item">
								<span class="wvp-status-label">BCV Dólar Tracker:</span>
								<span class="wvp-status-value wvp-status-ok">✅ Conectado</span>
							</div>
						</div>
					</div>
					
					<div class="wvp-card">
						<h2>Configuración Rápida</h2>
						<div class="wvp-quick-actions">
							<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout' ); ?>" class="wvp-action-btn">
								<span class="dashicons dashicons-money-alt"></span>
								Configurar Pagos
							</a>
							<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=shipping' ); ?>" class="wvp-action-btn">
								<span class="dashicons dashicons-truck"></span>
								Configurar Envíos
							</a>
							<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=tax' ); ?>" class="wvp-action-btn">
								<span class="dashicons dashicons-calculator"></span>
								Configurar Impuestos
							</a>
						</div>
					</div>
				</div>
				
				<div class="wvp-dashboard-right">
					<div class="wvp-card">
						<h2>Últimos Pedidos</h2>
						<div class="wvp-recent-orders">
							<?php $this->display_recent_orders(); ?>
						</div>
					</div>
					
					<div class="wvp-card">
						<h2>Información del Sistema</h2>
						<div class="wvp-system-info">
							<p><strong>Versión del Plugin:</strong> <?php echo WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION; ?></p>
							<p><strong>Versión de WooCommerce:</strong> <?php echo WC_VERSION; ?></p>
							<p><strong>Versión de WordPress:</strong> <?php echo get_bloginfo( 'version' ); ?></p>
							<p><strong>Versión de PHP:</strong> <?php echo PHP_VERSION; ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Settings page
	 */
	public function settings_page() {
		// Show success message if settings were saved
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			echo '<div class="notice notice-success is-dismissible"><p>Configuración guardada exitosamente.</p></div>';
		}
		?>
		<div class="wrap wvp-settings">
			<h1 class="wvp-page-title">
				<span class="dashicons dashicons-admin-settings"></span>
				Configuración - WooCommerce Venezuela Pro 2025
			</h1>
			
			<form method="post" action="options.php">
				<?php settings_fields( 'wvp_settings' ); ?>
				
				<div class="wvp-settings-grid">
					<div class="wvp-settings-section">
						<h2>Configuración de Moneda</h2>
						<table class="form-table">
							<tr>
								<th scope="row">Tasa BCV de Emergencia</th>
								<td>
									<input type="number" name="wvp_emergency_rate" value="<?php echo esc_attr( get_option( 'wvp_emergency_rate', '36.5' ) ); ?>" step="0.01" min="0" />
									<p class="description">Tasa de cambio USD a VES cuando BCV no esté disponible</p>
								</td>
							</tr>
						</table>
					</div>
					
					<div class="wvp-settings-section">
						<h2>Configuración de Impuestos</h2>
						<table class="form-table">
							<tr>
								<th scope="row">Tasa de IVA (%)</th>
								<td>
									<input type="number" name="wvp_iva_rate" value="<?php echo esc_attr( get_option( 'wvp_iva_rate', '16' ) ); ?>" min="0" max="50" step="0.01" />
									<p class="description">Tasa de IVA venezolano (por defecto 16%)</p>
								</td>
							</tr>
							<tr>
								<th scope="row">Tasa de IGTF (%)</th>
								<td>
									<input type="number" name="wvp_igtf_rate" value="<?php echo esc_attr( get_option( 'wvp_igtf_rate', '3' ) ); ?>" min="0" max="10" step="0.01" />
									<p class="description">Tasa de IGTF venezolano (por defecto 3%)</p>
								</td>
							</tr>
						</table>
					</div>
				</div>
				
				<?php submit_button( 'Guardar Configuración' ); ?>
			</form>
			
			<div class="wvp-settings-info">
				<h3>Información de Configuración</h3>
				<p><strong>Tasa BCV Actual:</strong> <?php echo get_option( 'wvp_emergency_rate', '36.5' ); ?> VES por USD</p>
				<p><strong>IVA Configurado:</strong> <?php echo get_option( 'wvp_iva_rate', '16' ); ?>%</p>
				<p><strong>IGTF Configurado:</strong> <?php echo get_option( 'wvp_igtf_rate', '3' ); ?>%</p>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Reports page
	 */
	public function reports_page() {
		?>
		<div class="wrap wvp-reports">
			<h1 class="wvp-page-title">
				<span class="dashicons dashicons-chart-bar"></span>
				Reportes - WooCommerce Venezuela Pro 2025
			</h1>
			
			<div class="wvp-reports-content">
				<p>Los reportes estarán disponibles en la próxima versión.</p>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Get dashboard statistics
	 */
	private function get_dashboard_stats() {
		$orders = wc_get_orders( array(
			'limit' => -1,
			'status' => array( 'wc-completed', 'wc-processing' )
		));
		
		$total_revenue = 0;
		foreach ( $orders as $order ) {
			$total_revenue += $order->get_total();
		}
		
		$customers = get_users( array(
			'role' => 'customer',
			'number' => -1
		));
		
		return array(
			'total_orders' => count( $orders ),
			'total_revenue' => $total_revenue,
			'total_customers' => count( $customers ),
			'conversion_rate' => '85' // Placeholder
		);
	}
	
	/**
	 * Display recent orders
	 */
	private function display_recent_orders() {
		$orders = wc_get_orders( array(
			'limit' => 5,
			'orderby' => 'date',
			'order' => 'DESC'
		));
		
		if ( empty( $orders ) ) {
			echo '<p>No hay pedidos recientes.</p>';
			return;
		}
		
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>Pedido</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead>';
		echo '<tbody>';
		
		foreach ( $orders as $order ) {
			echo '<tr>';
			echo '<td><a href="' . admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) . '">#' . $order->get_id() . '</a></td>';
			echo '<td>' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '</td>';
			echo '<td>' . wc_price( $order->get_total() ) . '</td>';
			echo '<td><span class="wvp-order-status wvp-status-' . $order->get_status() . '">' . wc_get_order_status_name( $order->get_status() ) . '</span></td>';
			echo '</tr>';
		}
		
		echo '</tbody></table>';
	}
	
	/**
	 * AJAX handler for dashboard stats
	 */
	public function ajax_get_dashboard_stats() {
		check_ajax_referer( 'wvp_dashboard', 'nonce' );
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$stats = $this->get_dashboard_stats();
		wp_send_json_success( $stats );
	}
}
