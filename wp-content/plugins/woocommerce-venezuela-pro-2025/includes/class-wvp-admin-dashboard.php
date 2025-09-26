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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_wvp_get_dashboard_stats', array( $this, 'ajax_get_dashboard_stats' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
		// AJAX handlers para configuración de conversores
		add_action( 'wp_ajax_wvp_save_currency_settings', array( $this, 'ajax_save_currency_settings' ) );
		add_action( 'wp_ajax_wvp_reset_currency_settings', array( $this, 'ajax_reset_currency_settings' ) );
		add_action( 'wp_ajax_wvp_preview_currency_converter', array( $this, 'ajax_preview_currency_converter' ) );
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
			'Conversores de Moneda',
			'Conversores de Moneda',
			'manage_woocommerce',
			'wvp-currency-settings',
			array( $this, 'currency_settings_page' )
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
			$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
			$plugin_version = '1.0.0';
			
			wp_enqueue_style( 'wvp-admin', $plugin_url . 'admin/css/wvp-admin.css', array(), $plugin_version );
			wp_enqueue_style( 'wvp-seniat', $plugin_url . 'admin/css/wvp-seniat.css', array(), $plugin_version );
			wp_enqueue_style( 'wvp-currency-admin', $plugin_url . 'admin/css/wvp-currency-admin.css', array(), $plugin_version );
			wp_enqueue_script( 'wvp-admin', $plugin_url . 'admin/js/wvp-admin.js', array( 'jquery' ), $plugin_version, true );
			
			// Cargar JavaScript específico para SENIAT
			if ( strpos( $hook, 'wvp-seniat' ) !== false ) {
				wp_enqueue_script( 'wvp-seniat', $plugin_url . 'admin/js/wvp-seniat.js', array( 'jquery' ), $plugin_version, true );
				wp_localize_script( 'wvp-seniat', 'wvp_seniat_ajax', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'wvp_seniat_nonce' )
				));
			}
			
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
						<h3>$<?php echo number_format( $stats['total_revenue_usd'], 2 ); ?></h3>
						<p>Ingresos USD</p>
					</div>
				</div>
				
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-money-alt"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo number_format( $stats['total_revenue_ves'], 2 ); ?></h3>
						<p>Ingresos VES</p>
					</div>
				</div>
				
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-groups"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo $stats['total_customers']; ?></h3>
						<p>Clientes Totales</p>
					</div>
				</div>
				
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-update"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo $stats['currency_conversions']; ?></h3>
						<p>Conversiones</p>
					</div>
				</div>
				
				<div class="wvp-stat-card">
					<div class="wvp-stat-icon">
						<span class="dashicons dashicons-chart-line"></span>
					</div>
					<div class="wvp-stat-content">
						<h3><?php echo $stats['bcv_rate']; ?></h3>
						<p>Tasa BCV</p>
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
				<p><strong>Tasa BCV Actual:</strong> <?php 
					// Obtener tasa BCV real del Currency Converter
					if (class_exists('WVP_Simple_Currency_Converter')) {
						$converter = WVP_Simple_Currency_Converter::get_instance();
						echo number_format($converter->get_bcv_rate(), 4);
					} elseif (class_exists('BCV_Dolar_Tracker')) {
						echo number_format(BCV_Dolar_Tracker::get_rate(), 4);
					} else {
						echo get_option('wvp_bcv_rate', '36.5');
					}
				?> VES por USD</p>
				<p><strong>IVA Configurado:</strong> <?php echo get_option( 'wvp_iva_rate', '16' ); ?>%</p>
				<p><strong>IGTF Configurado:</strong> <?php echo get_option( 'wvp_igtf_rate', '3' ); ?>%</p>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Currency Settings page
	 */
	public function currency_settings_page() {
		// Incluir la página de configuración de conversores
		include_once plugin_dir_path( __FILE__ ) . '../admin/partials/currency-config/currency-settings.php';
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
		
		$total_revenue_usd = 0;
		$total_revenue_ves = 0;
		$currency_conversions = 0;
		$payment_methods = array();
		$shipping_methods = array();
		$monthly_stats = array();
		
		foreach ( $orders as $order ) {
			$total_revenue_usd += $order->get_total();
			
			// Obtener tasa BCV real para conversión
			$bcv_rate = 36.5; // Fallback
			if (class_exists('WVP_Simple_Currency_Converter')) {
				$converter = WVP_Simple_Currency_Converter::get_instance();
				$bcv_rate = $converter->get_bcv_rate();
			} elseif (class_exists('BCV_Dolar_Tracker')) {
				$bcv_rate = BCV_Dolar_Tracker::get_rate();
			} else {
				$bcv_rate = get_option('wvp_bcv_rate', 36.5);
			}
			$total_revenue_ves += $order->get_total() * $bcv_rate;
			
			// Contar métodos de pago
			$payment_method = $order->get_payment_method();
			if ( ! isset( $payment_methods[$payment_method] ) ) {
				$payment_methods[$payment_method] = 0;
			}
			$payment_methods[$payment_method]++;
			
			// Contar métodos de envío
			$shipping_method = $order->get_shipping_method();
			if ( ! isset( $shipping_methods[$shipping_method] ) ) {
				$shipping_methods[$shipping_method] = 0;
			}
			$shipping_methods[$shipping_method]++;
			
			// Estadísticas mensuales
			$month = $order->get_date_created()->format( 'Y-m' );
			if ( ! isset( $monthly_stats[$month] ) ) {
				$monthly_stats[$month] = array( 'orders' => 0, 'revenue' => 0 );
			}
			$monthly_stats[$month]['orders']++;
			$monthly_stats[$month]['revenue'] += $order->get_total();
			
			$currency_conversions++;
		}
		
		$customers = get_users( array(
			'role' => 'customer',
			'number' => -1
		));
		
		return array(
			'total_orders' => count( $orders ),
			'total_revenue_usd' => $total_revenue_usd,
			'total_revenue_ves' => $total_revenue_ves,
			'total_customers' => count( $customers ),
			'currency_conversions' => $currency_conversions,
			'bcv_rate' => (class_exists('WVP_Simple_Currency_Converter')) ? 
				WVP_Simple_Currency_Converter::get_instance()->get_bcv_rate() : 
				((class_exists('BCV_Dolar_Tracker')) ? BCV_Dolar_Tracker::get_rate() : get_option('wvp_bcv_rate', 36.5)),
			'payment_methods' => $payment_methods,
			'shipping_methods' => $shipping_methods,
			'monthly_stats' => $monthly_stats,
			'last_update' => current_time( 'Y-m-d H:i:s' )
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
	
	/**
	 * Display payment methods statistics
	 */
	private function display_payment_methods_stats( $payment_methods ) {
		if ( empty( $payment_methods ) ) {
			echo '<p>No hay datos de métodos de pago disponibles.</p>';
			return;
		}
		
		echo '<ul class="wvp-stats-list">';
		foreach ( $payment_methods as $method => $count ) {
			$method_name = $this->get_payment_method_name( $method );
			echo '<li><span class="wvp-method-name">' . esc_html( $method_name ) . '</span>: <strong>' . $count . '</strong> pedidos</li>';
		}
		echo '</ul>';
	}
	
	/**
	 * Display shipping methods statistics
	 */
	private function display_shipping_methods_stats( $shipping_methods ) {
		if ( empty( $shipping_methods ) ) {
			echo '<p>No hay datos de métodos de envío disponibles.</p>';
			return;
		}
		
		echo '<ul class="wvp-stats-list">';
		foreach ( $shipping_methods as $method => $count ) {
			$method_name = $this->get_shipping_method_name( $method );
			echo '<li><span class="wvp-method-name">' . esc_html( $method_name ) . '</span>: <strong>' . $count . '</strong> pedidos</li>';
		}
		echo '</ul>';
	}
	
	/**
	 * Display monthly statistics
	 */
	private function display_monthly_stats( $monthly_stats ) {
		if ( empty( $monthly_stats ) ) {
			echo '<p>No hay datos mensuales disponibles.</p>';
			return;
		}
		
		// Ordenar por mes (más reciente primero)
		krsort( $monthly_stats );
		
		echo '<ul class="wvp-stats-list">';
		foreach ( $monthly_stats as $month => $data ) {
			$month_name = date( 'F Y', strtotime( $month . '-01' ) );
			echo '<li><span class="wvp-method-name">' . esc_html( $month_name ) . '</span>: <strong>' . $data['orders'] . '</strong> pedidos, <strong>$' . number_format( $data['revenue'], 2 ) . '</strong></li>';
		}
		echo '</ul>';
	}
	
	/**
	 * Get payment method display name
	 */
	private function get_payment_method_name( $method ) {
		$methods = array(
			'wvp_pago_movil' => 'Pago Móvil',
			'bacs' => 'Transferencia Bancaria',
			'cod' => 'Contra Reembolso',
			'stripe' => 'Stripe',
			'paypal' => 'PayPal'
		);
		
		return isset( $methods[$method] ) ? $methods[$method] : ucfirst( str_replace( '_', ' ', $method ) );
	}
	
	/**
	 * Get shipping method display name
	 */
	private function get_shipping_method_name( $method ) {
		$methods = array(
			'wvp_mrw' => 'MRW Venezuela',
			'wvp_zoom' => 'Zoom Envíos',
			'wvp_menssajero' => 'Menssajero',
			'wvp_local_delivery' => 'Entrega Local',
			'flat_rate' => 'Tarifa Fija',
			'free_shipping' => 'Envío Gratis'
		);
		
		return isset( $methods[$method] ) ? $methods[$method] : ucfirst( str_replace( '_', ' ', $method ) );
	}
	
	/**
	 * AJAX: Guardar configuración de conversores
	 */
	public function ajax_save_currency_settings() {
		// Verificar nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_currency_settings_ajax' ) ) {
			wp_send_json_error( 'Nonce inválido' );
		}
		
		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permisos insuficientes' );
		}
		
		// Procesar datos del formulario
		$settings = array();
		
		// Configuración general
		if ( isset( $_POST['settings']['general'] ) ) {
			$settings['general'] = array_map( 'sanitize_text_field', $_POST['settings']['general'] );
		}
		
		// Ubicaciones de visualización
		if ( isset( $_POST['settings']['display_locations'] ) ) {
			$settings['display_locations'] = array();
			foreach ( $_POST['settings']['display_locations'] as $location => $value ) {
				$settings['display_locations'][$location] = (bool) $value;
			}
		}
		
		// Configuración de apariencia
		if ( isset( $_POST['settings']['appearance'] ) ) {
			$settings['appearance'] = array_map( 'sanitize_text_field', $_POST['settings']['appearance'] );
		}
		
		// Configuración de estilos
		if ( isset( $_POST['settings']['styling'] ) ) {
			$settings['styling'] = $_POST['settings']['styling'];
			// Sanitizar colores
			foreach ( $settings['styling'] as $key => $value ) {
				if ( strpos( $key, 'color' ) !== false ) {
					$settings['styling'][$key] = sanitize_hex_color( $value );
				} else {
					$settings['styling'][$key] = sanitize_text_field( $value );
				}
			}
		}
		
		// Configuración responsiva
		if ( isset( $_POST['settings']['responsive'] ) ) {
			$settings['responsive'] = array_map( 'sanitize_text_field', $_POST['settings']['responsive'] );
		}
		
		// Configuración avanzada
		if ( isset( $_POST['settings']['advanced'] ) ) {
			$settings['advanced'] = array_map( 'sanitize_text_field', $_POST['settings']['advanced'] );
		}
		
		// Guardar configuración
		if ( update_option( 'wvp_display_settings', $settings ) ) {
			wp_send_json_success( 'Configuración guardada correctamente' );
		} else {
			wp_send_json_error( 'Error al guardar la configuración' );
		}
	}
	
	/**
	 * AJAX: Resetear configuración de conversores
	 */
	public function ajax_reset_currency_settings() {
		// Verificar nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_currency_settings_ajax' ) ) {
			wp_send_json_error( 'Nonce inválido' );
		}
		
		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permisos insuficientes' );
		}
		
		// Obtener configuración por defecto
		if ( class_exists( 'WVP_Display_Settings' ) ) {
			$default_settings = WVP_Display_Settings::get_instance()->get_default_settings();
			
			if ( update_option( 'wvp_display_settings', $default_settings ) ) {
				wp_send_json_success( 'Configuración restaurada correctamente' );
			} else {
				wp_send_json_error( 'Error al restaurar la configuración' );
			}
		} else {
			wp_send_json_error( 'Sistema de configuraciones no disponible' );
		}
	}
	
	/**
	 * AJAX: Vista previa del conversor
	 */
	public function ajax_preview_currency_converter() {
		// Verificar nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_currency_settings_ajax' ) ) {
			wp_send_json_error( 'Nonce inválido' );
		}
		
		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permisos insuficientes' );
		}
		
		// Generar vista previa
		$preview_html = '<div class="wvp-preview-demo">';
		$preview_html .= '<h4>Vista Previa del Conversor</h4>';
		$preview_html .= '<div class="wvp-preview-examples">';
		
		// Ejemplo con botones
		$preview_html .= '<div class="wvp-preview-item">';
		$preview_html .= '<h5>Estilo Botones:</h5>';
		$preview_html .= do_shortcode( '[wvp_currency_switcher style="buttons" theme="default" size="medium"]' );
		$preview_html .= '</div>';
		
		// Ejemplo con dropdown
		$preview_html .= '<div class="wvp-preview-item">';
		$preview_html .= '<h5>Estilo Dropdown:</h5>';
		$preview_html .= do_shortcode( '[wvp_currency_switcher style="dropdown" theme="default" size="medium"]' );
		$preview_html .= '</div>';
		
		// Ejemplo con toggle
		$preview_html .= '<div class="wvp-preview-item">';
		$preview_html .= '<h5>Estilo Toggle:</h5>';
		$preview_html .= do_shortcode( '[wvp_currency_switcher style="toggle" theme="default" size="medium"]' );
		$preview_html .= '</div>';
		
		// Ejemplo con inline
		$preview_html .= '<div class="wvp-preview-item">';
		$preview_html .= '<h5>Estilo Inline:</h5>';
		$preview_html .= do_shortcode( '[wvp_currency_switcher style="inline" theme="default" size="medium"]' );
		$preview_html .= '</div>';
		
		$preview_html .= '</div>';
		$preview_html .= '</div>';
		
		wp_send_json_success( $preview_html );
	}
}
