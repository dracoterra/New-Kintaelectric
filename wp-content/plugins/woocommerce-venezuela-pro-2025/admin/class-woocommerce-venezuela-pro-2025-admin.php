<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://artifexcodes.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/admin
 * @author     ronald alvarez <ronaldalv2025@gmail.com>
 */
class Woocommerce_Venezuela_Pro_2025_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-venezuela-pro-2025-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-venezuela-pro-2025-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			'WooCommerce Venezuela Pro 2025',
			'Venezuela Pro',
			'manage_woocommerce',
			'wvp-dashboard',
			array( $this, 'display_dashboard_page' ),
			'dashicons-admin-site-alt3',
			56
		);

		add_submenu_page(
			'wvp-dashboard',
			'Dashboard',
			'Dashboard',
			'manage_woocommerce',
			'wvp-dashboard',
			array( $this, 'display_dashboard_page' )
		);

		add_submenu_page(
			'wvp-dashboard',
			'Configuración',
			'Configuración',
			'manage_woocommerce',
			'wvp-settings',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			'wvp-dashboard',
			'Módulos',
			'Módulos',
			'manage_woocommerce',
			'wvp-modules',
			array( $this, 'display_modules_page' )
		);
	}

	/**
	 * Display dashboard page.
	 *
	 * @since    1.0.0
	 */
	public function display_dashboard_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<div class="wvp-dashboard">
				<div class="wvp-card">
					<h2>Estado del Plugin</h2>
					<p>WooCommerce Venezuela Pro 2025 está activo y funcionando correctamente.</p>
					
					<h3>Información del Sistema</h3>
					<ul>
						<li><strong>Versión del Plugin:</strong> <?php echo esc_html( $this->version ); ?></li>
						<li><strong>WooCommerce:</strong> 
							<?php if ( class_exists( 'WooCommerce' ) ) : ?>
								<span style="color: green;">Activo</span>
							<?php else : ?>
								<span style="color: red;">No Activo</span>
							<?php endif; ?>
						</li>
						<li><strong>BCV Dólar Tracker:</strong>
							<?php if ( class_exists( 'BCV_Dolar_Tracker' ) ) : ?>
								<span style="color: green;">Disponible</span>
							<?php else : ?>
								<span style="color: orange;">No Disponible</span>
							<?php endif; ?>
						</li>
					</ul>

					<?php if ( class_exists( 'BCV_Dolar_Tracker' ) ) : ?>
						<h3>Tasa BCV Actual</h3>
						<?php
						$bcv_rate = BCV_Dolar_Tracker::get_rate();
						if ( $bcv_rate ) :
						?>
							<p><strong><?php echo number_format( $bcv_rate, 2, ',', '.' ); ?> VES</strong> por USD</p>
						<?php else : ?>
							<p style="color: orange;">No se pudo obtener la tasa BCV</p>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<div class="wvp-card">
					<h2>Próximos Pasos</h2>
					<p>El plugin está en desarrollo. Las siguientes funcionalidades estarán disponibles pronto:</p>
					<ul>
						<li>Configuración de métodos de pago venezolanos</li>
						<li>Cálculo automático de impuestos (IVA, IGTF)</li>
						<li>Conversión de precios USD a VES</li>
						<li>Métodos de envío locales</li>
						<li>Reportes para SENIAT</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p>Configuración del plugin en desarrollo...</p>
		</div>
		<?php
	}

	/**
	 * Display modules page.
	 *
	 * @since    1.0.0
	 */
	public function display_modules_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p>Gestión de módulos en desarrollo...</p>
		</div>
		<?php
	}

}
