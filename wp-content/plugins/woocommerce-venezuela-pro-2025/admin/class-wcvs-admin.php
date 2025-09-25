<?php

/**
 * WooCommerce Venezuela Suite 2025 - Admin
 *
 * Maneja la funcionalidad del panel de administración
 * con dashboard modular y sistema de ayuda integrado.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class
 */
class WCVS_Admin {

	/**
	 * Core instance
	 *
	 * @var WCVS_Core
	 */
	private $core;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->core = WCVS_Core::get_instance();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Initialize admin functionality
	 */
	public function init() {
		// Initialize admin functionality
		$this->init_admin_settings();
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		// Main menu page
		add_menu_page(
			__( 'WooCommerce Venezuela Suite', 'woocommerce-venezuela-pro-2025' ),
			__( 'Venezuela Suite', 'woocommerce-venezuela-pro-2025' ),
			'manage_options',
			'wcvs-dashboard',
			array( $this, 'dashboard_page' ),
			'dashicons-admin-site-alt3',
			56
		);

		// Dashboard submenu
		add_submenu_page(
			'wcvs-dashboard',
			__( 'Dashboard', 'woocommerce-venezuela-pro-2025' ),
			__( 'Dashboard', 'woocommerce-venezuela-pro-2025' ),
			'manage_options',
			'wcvs-dashboard',
			array( $this, 'dashboard_page' )
		);

		// Modules submenu
		add_submenu_page(
			'wcvs-dashboard',
			__( 'Módulos', 'woocommerce-venezuela-pro-2025' ),
			__( 'Módulos', 'woocommerce-venezuela-pro-2025' ),
			'manage_options',
			'wcvs-modules',
			array( $this, 'modules_page' )
		);

		// Settings submenu
		add_submenu_page(
			'wcvs-dashboard',
			__( 'Configuración', 'woocommerce-venezuela-pro-2025' ),
			__( 'Configuración', 'woocommerce-venezuela-pro-2025' ),
			'manage_options',
			'wcvs-settings',
			array( $this, 'settings_page' )
		);

		// Help submenu
		add_submenu_page(
			'wcvs-dashboard',
			__( 'Ayuda', 'woocommerce-venezuela-pro-2025' ),
			__( 'Ayuda', 'woocommerce-venezuela-pro-2025' ),
			'manage_options',
			'wcvs-help',
			array( $this, 'help_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, 'wcvs-' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wcvs-admin',
			WCVS_PLUGIN_URL . 'admin/css/wcvs-admin.css',
			array(),
			WCVS_VERSION
		);

		// Enqueue simplified dashboard CSS for dashboard page
		if ( $hook === 'venezuela-suite_page_wcvs-dashboard' ) {
			wp_enqueue_style(
				'wcvs-dashboard-simplified',
				WCVS_PLUGIN_URL . 'admin/css/dashboard-simplified.css',
				array(),
				WCVS_VERSION
			);

			// Enqueue dashboard functions
			wp_enqueue_script(
				'wcvs-dashboard-functions',
				WCVS_PLUGIN_URL . 'admin/js/dashboard-functions.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-dashboard-functions', 'wcvs_admin', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_admin_nonce' )
			));
		}

		wp_enqueue_script(
			'wcvs-admin',
			WCVS_PLUGIN_URL . 'admin/js/wcvs-admin.js',
			array( 'jquery', 'wp-util' ),
			WCVS_VERSION,
			true
		);

		// Enqueue BCV rate widget script for dashboard
		if ( $hook === 'venezuela-suite_page_wcvs-dashboard' ) {
			wp_enqueue_script(
				'wcvs-bcv-rate-widget',
				WCVS_PLUGIN_URL . 'admin/js/bcv-rate-widget.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-bcv-rate-widget', 'wcvs_bcv_rate', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_currency_nonce' )
			));

			// Enqueue Quick Config script
			wp_enqueue_script(
				'wcvs-quick-config',
				WCVS_PLUGIN_URL . 'admin/js/quick-config.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-quick-config', 'wcvs_quick_config', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_quick_config_nonce' ),
				'reset_nonce' => wp_create_nonce( 'wcvs_reset_config_nonce' ),
				'status_nonce' => wp_create_nonce( 'wcvs_config_status_nonce' ),
				'i18n' => array(
					'configuring' => __('Configurando WooCommerce...', 'woocommerce-venezuela-pro-2025'),
					'success' => __('Configuración completada exitosamente', 'woocommerce-venezuela-pro-2025'),
					'error' => __('Error en la configuración', 'woocommerce-venezuela-pro-2025'),
					'reset_success' => __('Configuración reseteada', 'woocommerce-venezuela-pro-2025')
				)
			));

			// Enqueue Statistics script
			wp_enqueue_script(
				'wcvs-statistics-dashboard',
				WCVS_PLUGIN_URL . 'admin/js/statistics-dashboard.js',
				array( 'jquery' ),
				WCVS_VERSION,
				true
			);

			wp_localize_script( 'wcvs-statistics-dashboard', 'wcvs_statistics_dashboard', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wcvs_dashboard_stats_nonce' ),
				'i18n' => array(
					'loading' => __('Cargando estadísticas...', 'woocommerce-venezuela-pro-2025'),
					'error' => __('Error al cargar estadísticas', 'woocommerce-venezuela-pro-2025')
				)
			));
		}

		wp_localize_script( 'wcvs-admin', 'wcvs_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wcvs_admin' ),
			'toggle_module_nonce' => wp_create_nonce( 'wcvs_toggle_module' ),
			'save_settings_nonce' => wp_create_nonce( 'wcvs_save_settings' ),
			'get_help_nonce' => wp_create_nonce( 'wcvs_get_help' ),
			'strings' => array(
				'confirm_deactivate' => __( '¿Estás seguro de que quieres desactivar este módulo?', 'woocommerce-venezuela-pro-2025' ),
				'confirm_activate' => __( '¿Estás seguro de que quieres activar este módulo?', 'woocommerce-venezuela-pro-2025' ),
				'saving' => __( 'Guardando...', 'woocommerce-venezuela-pro-2025' ),
				'saved' => __( 'Guardado', 'woocommerce-venezuela-pro-2025' ),
				'error' => __( 'Error', 'woocommerce-venezuela-pro-2025' )
			)
		));
	}

	/**
	 * Display admin notices
	 */
	public function admin_notices() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php _e( 'WooCommerce Venezuela Suite 2025', 'woocommerce-venezuela-pro-2025' ); ?></strong>
					<?php _e( ' requiere WooCommerce para funcionar. Por favor, instala y activa WooCommerce primero.', 'woocommerce-venezuela-pro-2025' ); ?>
				</p>
			</div>
			<?php
		}

		// Check for updates
		$current_version = get_option( 'wcvs_version', '0.0.0' );
		if ( version_compare( $current_version, WCVS_VERSION, '<' ) ) {
			?>
			<div class="notice notice-info">
				<p>
					<strong><?php _e( 'WooCommerce Venezuela Suite 2025', 'woocommerce-venezuela-pro-2025' ); ?></strong>
					<?php printf( __( ' se ha actualizado a la versión %s. Algunas configuraciones pueden haber cambiado.', 'woocommerce-venezuela-pro-2025' ), WCVS_VERSION ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Dashboard page
	 */
	public function dashboard_page() {
		$active_modules = $this->core->module_manager->get_active_modules();
		$modules = $this->core->module_manager->get_modules();
		$settings = $this->core->settings->get_all_settings();
		?>
		<div class="wrap wcvs-dashboard">
			<div class="wcvs-dashboard-header">
				<h1><?php _e( '🇻🇪 WooCommerce Venezuela Suite 2025', 'woocommerce-venezuela-pro-2025' ); ?></h1>
				<p class="wcvs-dashboard-subtitle"><?php _e('Tu tienda venezolana completamente configurada y lista para vender', 'woocommerce-venezuela-pro-2025'); ?></p>
			</div>
			
			<!-- Estado General del Sistema -->
			<div class="wcvs-system-status">
				<div class="wcvs-status-item">
					<span class="wcvs-status-icon dashicons dashicons-yes-alt"></span>
					<span class="wcvs-status-text"><?php _e('Plugin Activo', 'woocommerce-venezuela-pro-2025'); ?></span>
				</div>
				<div class="wcvs-status-item">
					<span class="wcvs-status-icon dashicons dashicons-yes-alt"></span>
					<span class="wcvs-status-text"><?php _e('WooCommerce Integrado', 'woocommerce-venezuela-pro-2025'); ?></span>
				</div>
				<div class="wcvs-status-item">
					<span class="wcvs-status-icon dashicons dashicons-warning"></span>
					<span class="wcvs-status-text"><?php _e('BCV Dólar Tracker', 'woocommerce-venezuela-pro-2025'); ?></span>
				</div>
			</div>
			
			<div class="wcvs-dashboard-grid">
				<!-- Columna Principal -->
				<div class="wcvs-main-column">
					<!-- Widget de Dólar del Día Simplificado -->
					<div class="wcvs-dashboard-card wcvs-rate-card">
						<div class="wcvs-card-header">
							<h2><?php _e('💵 Dólar del Día', 'woocommerce-venezuela-pro-2025'); ?></h2>
						</div>
						<div class="wcvs-rate-display-simple">
							<div class="wcvs-rate-value"><?php echo $this->get_current_rate_display(); ?></div>
							<div class="wcvs-rate-status"><?php echo $this->get_rate_status(); ?></div>
							<button type="button" class="button button-secondary wcvs-refresh-rate" onclick="wcvsRefreshRate()">
								<span class="dashicons dashicons-update"></span>
								<?php _e('Actualizar', 'woocommerce-venezuela-pro-2025'); ?>
							</button>
						</div>
					</div>

					<!-- Widget de Configuración Simplificado -->
					<div class="wcvs-dashboard-card wcvs-config-card">
						<div class="wcvs-card-header">
							<h2><?php _e('⚡ Configuración Rápida', 'woocommerce-venezuela-pro-2025'); ?></h2>
						</div>
						<div class="wcvs-config-progress">
							<div class="wcvs-progress-bar">
								<div class="wcvs-progress-fill" style="width: 83%"></div>
							</div>
							<div class="wcvs-progress-text">5 de 6 configuraciones completadas</div>
						</div>
						<div class="wcvs-config-actions">
							<button type="button" class="button button-primary wcvs-quick-config-button" onclick="wcvsQuickConfigure()">
								<span class="dashicons dashicons-admin-settings"></span>
								<?php _e('Completar Configuración', 'woocommerce-venezuela-pro-2025'); ?>
							</button>
							<a href="<?php echo admin_url('admin.php?page=wcvs-settings'); ?>" class="button button-secondary">
								<span class="dashicons dashicons-admin-generic"></span>
								<?php _e('Configuración Avanzada', 'woocommerce-venezuela-pro-2025'); ?>
							</a>
						</div>
					</div>

					<!-- Widget de Estadísticas Simplificado -->
					<div class="wcvs-dashboard-card wcvs-stats-card">
						<div class="wcvs-card-header">
							<h2><?php _e('📊 Resumen de Ventas', 'woocommerce-venezuela-pro-2025'); ?></h2>
						</div>
						<div class="wcvs-stats-grid">
							<div class="wcvs-stat-item">
								<div class="wcvs-stat-value" id="today-sales">$0.00</div>
								<div class="wcvs-stat-label"><?php _e('Hoy', 'woocommerce-venezuela-pro-2025'); ?></div>
							</div>
							<div class="wcvs-stat-item">
								<div class="wcvs-stat-value" id="month-sales">$0.00</div>
								<div class="wcvs-stat-label"><?php _e('Este Mes', 'woocommerce-venezuela-pro-2025'); ?></div>
							</div>
							<div class="wcvs-stat-item">
								<div class="wcvs-stat-value" id="total-orders">0</div>
								<div class="wcvs-stat-label"><?php _e('Pedidos', 'woocommerce-venezuela-pro-2025'); ?></div>
							</div>
						</div>
						<div class="wcvs-stats-actions">
							<a href="<?php echo admin_url('admin.php?page=wcvs-statistics'); ?>" class="button button-secondary">
								<span class="dashicons dashicons-chart-bar"></span>
								<?php _e('Ver Estadísticas Completas', 'woocommerce-venezuela-pro-2025'); ?>
							</a>
						</div>
					</div>
				</div>

				<!-- Columna Lateral -->
				<div class="wcvs-sidebar-column">
					<!-- Módulos Activos -->
					<div class="wcvs-dashboard-card wcvs-modules-card">
						<div class="wcvs-card-header">
							<h2><?php _e('📦 Módulos Activos', 'woocommerce-venezuela-pro-2025'); ?></h2>
						</div>
						<div class="wcvs-modules-list">
							<?php foreach ( $active_modules as $module_id => $is_active ): ?>
								<?php if ( $is_active && isset( $modules[ $module_id ] ) ): ?>
									<div class="wcvs-module-item">
										<div class="wcvs-module-icon">
											<span class="dashicons dashicons-yes-alt"></span>
										</div>
										<div class="wcvs-module-info">
											<div class="wcvs-module-name"><?php echo esc_html( $modules[ $module_id ]['name'] ); ?></div>
											<div class="wcvs-module-status"><?php _e('Activo', 'woocommerce-venezuela-pro-2025'); ?></div>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
						<div class="wcvs-modules-actions">
							<a href="<?php echo admin_url('admin.php?page=wcvs-modules'); ?>" class="button button-secondary">
								<span class="dashicons dashicons-admin-settings"></span>
								<?php _e('Gestionar Módulos', 'woocommerce-venezuela-pro-2025'); ?>
							</a>
						</div>
					</div>

					<!-- Acciones Rápidas -->
					<div class="wcvs-dashboard-card wcvs-actions-card">
						<div class="wcvs-card-header">
							<h2><?php _e('🚀 Acciones Rápidas', 'woocommerce-venezuela-pro-2025'); ?></h2>
						</div>
						<div class="wcvs-actions-list">
							<a href="<?php echo admin_url('admin.php?page=wcvs-statistics'); ?>" class="wcvs-action-item">
								<span class="dashicons dashicons-chart-bar"></span>
								<span class="wcvs-action-text"><?php _e('Ver Estadísticas', 'woocommerce-venezuela-pro-2025'); ?></span>
							</a>
							<a href="<?php echo admin_url('admin.php?page=wcvs-seniat-reports'); ?>" class="wcvs-action-item">
								<span class="dashicons dashicons-media-document"></span>
								<span class="wcvs-action-text"><?php _e('Reportes SENIAT', 'woocommerce-venezuela-pro-2025'); ?></span>
							</a>
							<a href="<?php echo admin_url('admin.php?page=wcvs-help'); ?>" class="wcvs-action-item">
								<span class="dashicons dashicons-editor-help"></span>
								<span class="wcvs-action-text"><?php _e('Ayuda y Soporte', 'woocommerce-venezuela-pro-2025'); ?></span>
							</a>
						</div>
					</div>
				</div>
			</div>

			<!-- Configuración Paso a Paso -->
			<div class="wcvs-dashboard-card">
				<h2><?php _e( '🔧 Configuración Paso a Paso', 'woocommerce-venezuela-pro-2025' ); ?></h2>
				<div class="wcvs-step-config">
					<div class="wcvs-step-item">
						<div class="wcvs-step-number">1</div>
						<div class="wcvs-step-content">
							<h3><?php _e( 'Moneda Base', 'woocommerce-venezuela-pro-2025' ); ?></h3>
							<p><?php _e( 'Configurar moneda a USD y formato venezolano', 'woocommerce-venezuela-pro-2025' ); ?></p>
							<button type="button" class="button button-secondary" onclick="wcvsConfigureCurrency()">
								<?php _e( 'Configurar Moneda Base', 'woocommerce-venezuela-pro-2025' ); ?>
							</button>
						</div>
					</div>
					<div class="wcvs-step-item">
						<div class="wcvs-step-number">2</div>
						<div class="wcvs-step-content">
							<h3><?php _e( 'Impuestos', 'woocommerce-venezuela-pro-2025' ); ?></h3>
							<p><?php _e( 'Configurar IVA (16%) e IGTF (3%)', 'woocommerce-venezuela-pro-2025' ); ?></p>
							<button type="button" class="button button-secondary" onclick="wcvsConfigureTaxes()">
								<?php _e( 'Configurar Impuestos', 'woocommerce-venezuela-pro-2025' ); ?>
							</button>
						</div>
					</div>
					<div class="wcvs-step-item">
						<div class="wcvs-step-number">3</div>
						<div class="wcvs-step-content">
							<h3><?php _e( 'Métodos de Pago', 'woocommerce-venezuela-pro-2025' ); ?></h3>
							<p><?php _e( 'Habilitar transferencia bancaria y pago contra entrega', 'woocommerce-venezuela-pro-2025' ); ?></p>
							<button type="button" class="button button-secondary" onclick="wcvsConfigurePayments()">
								<?php _e( 'Configurar Métodos de Pago', 'woocommerce-venezuela-pro-2025' ); ?>
							</button>
						</div>
					</div>
					<div class="wcvs-step-item">
						<div class="wcvs-step-number">4</div>
						<div class="wcvs-step-content">
							<h3><?php _e( 'Métodos de Envío', 'woocommerce-venezuela-pro-2025' ); ?></h3>
							<p><?php _e( 'Configurar envío gratuito y estándar', 'woocommerce-venezuela-pro-2025' ); ?></p>
							<button type="button" class="button button-secondary" onclick="wcvsConfigureShipping()">
								<?php _e( 'Configurar Métodos de Envío', 'woocommerce-venezuela-pro-2025' ); ?>
							</button>
						</div>
					</div>
					<div class="wcvs-step-item">
						<div class="wcvs-step-number">5</div>
						<div class="wcvs-step-content">
							<h3><?php _e( 'Ubicación', 'woocommerce-venezuela-pro-2025' ); ?></h3>
							<p><?php _e( 'Configurar Venezuela como país base', 'woocommerce-venezuela-pro-2025' ); ?></p>
							<button type="button" class="button button-secondary" onclick="wcvsConfigureLocation()">
								<?php _e( 'Configurar Ubicación', 'woocommerce-venezuela-pro-2025' ); ?>
							</button>
						</div>
					</div>
					<div class="wcvs-step-item">
						<div class="wcvs-step-number">6</div>
						<div class="wcvs-step-content">
							<h3><?php _e( 'Páginas', 'woocommerce-venezuela-pro-2025' ); ?></h3>
							<p><?php _e( 'Crear páginas necesarias de WooCommerce', 'woocommerce-venezuela-pro-2025' ); ?></p>
							<button type="button" class="button button-secondary" onclick="wcvsConfigurePages()">
								<?php _e( 'Configurar Páginas', 'woocommerce-venezuela-pro-2025' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Resumen Ejecutivo -->
			<div class="wcvs-dashboard-card">
				<h2><?php _e( '📊 Resumen Ejecutivo', 'woocommerce-venezuela-pro-2025' ); ?></h2>
				<div class="wcvs-stats">
					<div class="wcvs-stat">
						<span class="wcvs-stat-label"><?php _e( 'Módulos Activos', 'woocommerce-venezuela-pro-2025' ); ?></span>
						<span class="wcvs-stat-value"><?php echo count( array_filter( $active_modules ) ); ?></span>
					</div>
					<div class="wcvs-stat">
						<span class="wcvs-stat-label"><?php _e( 'Versión', 'woocommerce-venezuela-pro-2025' ); ?></span>
						<span class="wcvs-stat-value"><?php echo WCVS_VERSION; ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get current rate display
	 */
	private function get_current_rate_display() {
		$rate = $this->core->bcv_integration->get_current_rate();
		if ( $rate && $rate > 0 ) {
			return '<span class="wcvs-rate-number">' . number_format( $rate, 2, ',', '.' ) . '</span> <span class="wcvs-rate-currency">VES</span>';
		}
		return '<span class="wcvs-rate-unavailable">' . __( 'No disponible', 'woocommerce-venezuela-pro-2025' ) . '</span>';
	}

	/**
	 * Get rate status
	 */
	private function get_rate_status() {
		if ( $this->core->bcv_integration->is_available() ) {
			return '<span class="wcvs-status-online">' . __( 'BCV Activo', 'woocommerce-venezuela-pro-2025' ) . '</span>';
		}
		return '<span class="wcvs-status-offline">' . __( 'Modo Respaldo', 'woocommerce-venezuela-pro-2025' ) . '</span>';
	}

	/**
	 * Modules page
	 */
	public function modules_page() {
		$modules = $this->core->module_manager->get_modules();
		$active_modules = $this->core->module_manager->get_active_modules();
		?>
		<div class="wrap wcvs-modules">
			<h1><?php _e( '📦 Gestión de Módulos', 'woocommerce-venezuela-pro-2025' ); ?></h1>
			
			<div class="wcvs-modules-grid">
				<?php foreach ( $modules as $module_id => $module_config ): ?>
					<div class="wcvs-module-card <?php echo $this->core->module_manager->is_module_active( $module_id ) ? 'active' : 'inactive'; ?>">
						<div class="wcvs-module-header">
							<h3><?php echo esc_html( $module_config['name'] ); ?></h3>
							<span class="wcvs-module-category"><?php echo esc_html( $module_config['category'] ); ?></span>
						</div>
						
						<div class="wcvs-module-body">
							<p><?php echo esc_html( $module_config['description'] ); ?></p>
							
							<?php if ( ! empty( $module_config['dependencies'] ) ): ?>
								<div class="wcvs-module-dependencies">
									<strong><?php _e( 'Dependencias:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
									<?php foreach ( $module_config['dependencies'] as $dependency ): ?>
										<span class="wcvs-dependency"><?php echo esc_html( $modules[ $dependency ]['name'] ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
						
						<div class="wcvs-module-footer">
							<button class="wcvs-toggle-module button <?php echo $this->core->module_manager->is_module_active( $module_id ) ? 'button-secondary' : 'button-primary'; ?>" 
									data-module="<?php echo esc_attr( $module_id ); ?>"
									data-action="<?php echo $this->core->module_manager->is_module_active( $module_id ) ? 'deactivate' : 'activate'; ?>">
								<?php echo $this->core->module_manager->is_module_active( $module_id ) ? __( 'Desactivar', 'woocommerce-venezuela-pro-2025' ) : __( 'Activar', 'woocommerce-venezuela-pro-2025' ); ?>
							</button>
							<a href="#" class="wcvs-help-link" data-module="<?php echo esc_attr( $module_id ); ?>">
								<?php _e( 'Ayuda', 'woocommerce-venezuela-pro-2025' ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Settings page
	 */
	public function settings_page() {
		$settings = $this->core->settings->get_all_settings();
		?>
		<div class="wrap wcvs-settings">
			<h1><?php _e( '⚙️ Configuración', 'woocommerce-venezuela-pro-2025' ); ?></h1>
			
			<form id="wcvs-settings-form">
				<div class="wcvs-settings-tabs">
					<nav class="wcvs-tab-nav">
						<a href="#general" class="wcvs-tab-link active"><?php _e( 'General', 'woocommerce-venezuela-pro-2025' ); ?></a>
						<a href="#currency" class="wcvs-tab-link"><?php _e( 'Moneda', 'woocommerce-venezuela-pro-2025' ); ?></a>
						<a href="#tax" class="wcvs-tab-link"><?php _e( 'Impuestos', 'woocommerce-venezuela-pro-2025' ); ?></a>
						<a href="#notifications" class="wcvs-tab-link"><?php _e( 'Notificaciones', 'woocommerce-venezuela-pro-2025' ); ?></a>
						<a href="#billing" class="wcvs-tab-link"><?php _e( 'Facturación', 'woocommerce-venezuela-pro-2025' ); ?></a>
					</nav>
					
					<div class="wcvs-tab-content">
						<div id="general" class="wcvs-tab-panel active">
							<h2><?php _e( 'Configuración General', 'woocommerce-venezuela-pro-2025' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Modo Debug', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="general[enable_debug]" value="1" <?php checked( $settings['general']['enable_debug'] ); ?>>
											<?php _e( 'Activar modo debug', 'woocommerce-venezuela-pro-2025' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Nivel de Log', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<select name="general[log_level]">
											<option value="emergency" <?php selected( $settings['general']['log_level'], 'emergency' ); ?>><?php _e( 'Emergency', 'woocommerce-venezuela-pro-2025' ); ?></option>
											<option value="alert" <?php selected( $settings['general']['log_level'], 'alert' ); ?>><?php _e( 'Alert', 'woocommerce-venezuela-pro-2025' ); ?></option>
											<option value="critical" <?php selected( $settings['general']['log_level'], 'critical' ); ?>><?php _e( 'Critical', 'woocommerce-venezuela-pro-2025' ); ?></option>
											<option value="error" <?php selected( $settings['general']['log_level'], 'error' ); ?>><?php _e( 'Error', 'woocommerce-venezuela-pro-2025' ); ?></option>
											<option value="warning" <?php selected( $settings['general']['log_level'], 'warning' ); ?>><?php _e( 'Warning', 'woocommerce-venezuela-pro-2025' ); ?></option>
											<option value="notice" <?php selected( $settings['general']['log_level'], 'notice' ); ?>><?php _e( 'Notice', 'woocommerce-venezuela-pro-2025' ); ?></option>
											<option value="info" <?php selected( $settings['general']['log_level'], 'info' ); ?>><?php _e( 'Info', 'woocommerce-venezuela-pro-2025' ); ?></option>
											<option value="debug" <?php selected( $settings['general']['log_level'], 'debug' ); ?>><?php _e( 'Debug', 'woocommerce-venezuela-pro-2025' ); ?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>

						<div id="currency" class="wcvs-tab-panel">
							<h2><?php _e( 'Configuración de Moneda', 'woocommerce-venezuela-pro-2025' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Moneda Base', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<select name="currency[base_currency]">
											<option value="VES" <?php selected( $settings['currency']['base_currency'], 'VES' ); ?>>VES - Bolívar Venezolano</option>
											<option value="USD" <?php selected( $settings['currency']['base_currency'], 'USD' ); ?>>USD - Dólar Estadounidense</option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Mostrar Precios Duales', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="currency[dual_pricing]" value="1" <?php checked( isset( $settings['currency']['dual_pricing'] ) ? $settings['currency']['dual_pricing'] : false ); ?>>
											<?php _e( 'Mostrar precios en ambas monedas', 'woocommerce-venezuela-pro-2025' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Tasa de Cambio Manual', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<input type="number" name="currency[manual_rate]" value="<?php echo esc_attr( isset( $settings['currency']['manual_rate'] ) ? $settings['currency']['manual_rate'] : 0 ); ?>" step="0.01" min="0">
										<p class="description"><?php _e( 'Tasa manual USD/VES (se usa si BCV no está disponible)', 'woocommerce-venezuela-pro-2025' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Estado BCV Dólar Tracker', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<?php 
										$bcv_status = $this->core->bcv_integration->get_bcv_status();
										if ( $bcv_status['available'] ): ?>
											<span style="color: green;">✓ <?php _e( 'Plugin BCV disponible', 'woocommerce-venezuela-pro-2025' ); ?></span><br>
											<strong><?php _e( 'Tasa actual:', 'woocommerce-venezuela-pro-2025' ); ?></strong> <?php echo $bcv_status['current_rate'] ? number_format( $bcv_status['current_rate'], 2 ) . ' VES/USD' : __( 'No disponible', 'woocommerce-venezuela-pro-2025' ); ?>
										<?php else: ?>
											<span style="color: red;">✗ <?php _e( 'Plugin BCV no disponible', 'woocommerce-venezuela-pro-2025' ); ?></span><br>
											<a href="<?php echo admin_url( 'plugin-install.php?s=bcv+dolar+tracker&tab=search&type=term' ); ?>" class="button button-secondary">
												<?php _e( 'Instalar BCV Dólar Tracker', 'woocommerce-venezuela-pro-2025' ); ?>
											</a>
										<?php endif; ?>
									</td>
								</tr>
							</table>
						</div>

						<div id="tax" class="wcvs-tab-panel">
							<h2><?php _e( 'Configuración de Impuestos', 'woocommerce-venezuela-pro-2025' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Tasa de IVA', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<input type="number" name="tax[iva_rate]" value="<?php echo esc_attr( $settings['tax']['iva_rate'] ); ?>" step="0.01" min="0" max="100">
										<span>%</span>
										<p class="description"><?php _e( 'Configura el IVA en WooCommerce > Configuración > Impuestos', 'woocommerce-venezuela-pro-2025' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Tasa de IGTF', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<input type="number" name="tax[igtf_rate]" value="<?php echo esc_attr( $settings['tax']['igtf_rate'] ); ?>" step="0.01" min="0" max="100">
										<span>%</span>
										<p class="description"><?php _e( 'Impuesto a las Grandes Transacciones Financieras', 'woocommerce-venezuela-pro-2025' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Aplicar IGTF a Pagos en USD', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="tax[apply_igtf_usd]" value="1" <?php checked( isset( $settings['tax']['apply_igtf_usd'] ) ? $settings['tax']['apply_igtf_usd'] : false ); ?>>
											<?php _e( 'Aplicar IGTF cuando el pago sea en dólares', 'woocommerce-venezuela-pro-2025' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>

						<div id="notifications" class="wcvs-tab-panel">
							<h2><?php _e( 'Configuración de Notificaciones', 'woocommerce-venezuela-pro-2025' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Notificaciones por Email', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="notifications[email_notifications]" value="1" <?php checked( $settings['notifications']['email_notifications'] ); ?>>
											<?php _e( 'Enviar notificaciones por email', 'woocommerce-venezuela-pro-2025' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Notificaciones de Cambio de Tasa', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="notifications[rate_change_notifications]" value="1" <?php checked( isset( $settings['notifications']['rate_change_notifications'] ) ? $settings['notifications']['rate_change_notifications'] : false ); ?>>
											<?php _e( 'Notificar cambios significativos en la tasa de cambio', 'woocommerce-venezuela-pro-2025' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>

						<div id="billing" class="wcvs-tab-panel">
							<h2><?php _e( 'Configuración de Facturación', 'woocommerce-venezuela-pro-2025' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Facturación Electrónica', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="billing[electronic_billing]" value="1" <?php checked( $settings['billing']['electronic_billing'] ); ?>>
											<?php _e( 'Activar facturación electrónica', 'woocommerce-venezuela-pro-2025' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'RIF de la Empresa', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<input type="text" name="billing[company_rif]" value="<?php echo esc_attr( isset( $settings['billing']['company_rif'] ) ? $settings['billing']['company_rif'] : '' ); ?>" placeholder="J-12345678-9">
										<p class="description"><?php _e( 'RIF de la empresa para facturación', 'woocommerce-venezuela-pro-2025' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Nombre de la Empresa', 'woocommerce-venezuela-pro-2025' ); ?></th>
									<td>
										<input type="text" name="billing[company_name]" value="<?php echo esc_attr( isset( $settings['billing']['company_name'] ) ? $settings['billing']['company_name'] : '' ); ?>">
										<p class="description"><?php _e( 'Nombre de la empresa para facturación', 'woocommerce-venezuela-pro-2025' ); ?></p>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				
				<?php submit_button( __( 'Guardar Configuración', 'woocommerce-venezuela-pro-2025' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Help page
	 */
	public function help_page() {
		$help_data = $this->core->help->get_all_help_data();
		?>
		<div class="wrap wcvs-help">
			<h1><?php _e( '❓ Ayuda y Documentación', 'woocommerce-venezuela-pro-2025' ); ?></h1>
			
			<div class="wcvs-help-content">
				<div class="wcvs-help-sidebar">
					<h3><?php _e( 'Índice', 'woocommerce-venezuela-pro-2025' ); ?></h3>
					<ul class="wcvs-help-menu">
						<li><a href="#woocommerce-integration"><?php _e( 'Integración con WooCommerce', 'woocommerce-venezuela-pro-2025' ); ?></a></li>
						<?php foreach ( $help_data['modules'] as $module_id => $module_help ): ?>
							<li><a href="#module-<?php echo esc_attr( $module_id ); ?>"><?php echo esc_html( $module_help['title'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
				
				<div class="wcvs-help-main">
					<div id="woocommerce-integration" class="wcvs-help-section">
						<h2><?php echo esc_html( $help_data['woocommerce_integration']['title'] ); ?></h2>
						<p><?php echo esc_html( $help_data['woocommerce_integration']['description'] ); ?></p>
						
						<h3><?php _e( 'Configuraciones de WooCommerce', 'woocommerce-venezuela-pro-2025' ); ?></h3>
						<ul>
							<?php foreach ( $help_data['woocommerce_integration']['sections'] as $section ): ?>
								<li>
									<a href="<?php echo esc_url( $section['url'] ); ?>" target="_blank">
										<?php echo esc_html( $section['title'] ); ?>
									</a>
									- <?php echo esc_html( $section['description'] ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					
					<?php foreach ( $help_data['modules'] as $module_id => $module_help ): ?>
						<div id="module-<?php echo esc_attr( $module_id ); ?>" class="wcvs-help-section">
							<h2><?php echo esc_html( $module_help['title'] ); ?></h2>
							<p><?php echo esc_html( $module_help['description'] ); ?></p>
							
							<?php if ( ! empty( $module_help['woocommerce_settings'] ) ): ?>
								<h3><?php _e( 'Configuraciones de WooCommerce', 'woocommerce-venezuela-pro-2025' ); ?></h3>
								<ul>
									<?php foreach ( $module_help['woocommerce_settings'] as $setting ): ?>
										<li>
											<a href="<?php echo esc_url( $setting['url'] ); ?>" target="_blank">
												<?php echo esc_html( $setting['title'] ); ?>
											</a>
											- <?php echo esc_html( $setting['description'] ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							
							<?php if ( ! empty( $module_help['configuration_steps'] ) ): ?>
								<h3><?php _e( 'Pasos de Configuración', 'woocommerce-venezuela-pro-2025' ); ?></h3>
								<ol>
									<?php foreach ( $module_help['configuration_steps'] as $step ): ?>
										<li><?php echo esc_html( $step ); ?></li>
									<?php endforeach; ?>
								</ol>
							<?php endif; ?>
							
							<?php if ( ! empty( $module_help['common_issues'] ) ): ?>
								<h3><?php _e( 'Problemas Comunes', 'woocommerce-venezuela-pro-2025' ); ?></h3>
								<dl>
									<?php foreach ( $module_help['common_issues'] as $issue ): ?>
										<dt><?php echo esc_html( $issue['problem'] ); ?></dt>
										<dd><?php echo esc_html( $issue['solution'] ); ?></dd>
									<?php endforeach; ?>
								</dl>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Initialize admin settings
	 */
	private function init_admin_settings() {
		// Initialize admin settings functionality
	}
}
