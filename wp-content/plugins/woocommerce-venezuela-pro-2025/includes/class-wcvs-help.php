<?php

/**
 * WooCommerce Venezuela Suite 2025 - Help System
 *
 * Sistema de ayuda integrado con guías contextuales
 * y enlaces directos a WooCommerce.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Help System class
 */
class WCVS_Help {

	/**
	 * Help data
	 *
	 * @var array
	 */
	private $help_data = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_help_data();
		$this->init_hooks();
	}

	/**
	 * Initialize help data
	 */
	private function init_help_data() {
		$this->help_data = array(
			'modules' => array(
				'tax_system' => array(
					'title' => __( 'Sistema Fiscal Venezolano', 'woocommerce-venezuela-pro-2025' ),
					'description' => __( 'Configuración de impuestos venezolanos (IVA, IGTF) y facturación electrónica', 'woocommerce-venezuela-pro-2025' ),
					'woocommerce_settings' => array(
						array(
							'title' => __( 'Configuración de Impuestos', 'woocommerce-venezuela-pro-2025' ),
							'url' => admin_url( 'admin.php?page=wc-settings&tab=tax' ),
							'description' => __( 'Configura el IVA usando el sistema nativo de WooCommerce', 'woocommerce-venezuela-pro-2025' )
						),
						array(
							'title' => __( 'Clases de Impuestos', 'woocommerce-venezuela-pro-2025' ),
							'url' => admin_url( 'admin.php?page=wc-settings&tab=tax&section=tax_classes' ),
							'description' => __( 'Crea clases de impuestos para diferentes productos', 'woocommerce-venezuela-pro-2025' )
						)
					),
					'configuration_steps' => array(
						__( 'Ve a WooCommerce > Configuración > Impuestos', 'woocommerce-venezuela-pro-2025' ),
						__( 'Activa "Calcular impuestos basados en" y selecciona "Dirección de facturación"', 'woocommerce-venezuela-pro-2025' ),
						__( 'Configura las tasas de IVA según tus productos', 'woocommerce-venezuela-pro-2025' ),
						__( 'El IGTF se aplicará automáticamente a pagos en divisas extranjeras', 'woocommerce-venezuela-pro-2025' )
					),
					'common_issues' => array(
						array(
							'problem' => __( 'Los impuestos no se calculan correctamente', 'woocommerce-venezuela-pro-2025' ),
							'solution' => __( 'Verifica que WooCommerce esté configurado para calcular impuestos y que las tasas estén activas', 'woocommerce-venezuela-pro-2025' )
						),
						array(
							'problem' => __( 'IGTF no se aplica automáticamente', 'woocommerce-venezuela-pro-2025' ),
							'solution' => __( 'El IGTF solo se aplica a métodos de pago en divisas extranjeras como Zelle o Binance Pay', 'woocommerce-venezuela-pro-2025' )
						)
					)
				),

				'payment_gateways' => array(
					'title' => __( 'Pasarelas de Pago Locales', 'woocommerce-venezuela-pro-2025' ),
					'description' => __( 'Métodos de pago populares en Venezuela', 'woocommerce-venezuela-pro-2025' ),
					'woocommerce_settings' => array(
						array(
							'title' => __( 'Métodos de Pago', 'woocommerce-venezuela-pro-2025' ),
							'url' => admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
							'description' => __( 'Configura y activa los métodos de pago locales', 'woocommerce-venezuela-pro-2025' )
						)
					),
					'configuration_steps' => array(
						__( 'Ve a WooCommerce > Configuración > Pagos', 'woocommerce-venezuela-pro-2025' ),
						__( 'Activa los métodos de pago que desees usar', 'woocommerce-venezuela-pro-2025' ),
						__( 'Configura los datos específicos de cada método (cuentas bancarias, etc.)', 'woocommerce-venezuela-pro-2025' ),
						__( 'Establece el orden de preferencia de los métodos', 'woocommerce-venezuela-pro-2025' )
					),
					'common_issues' => array(
						array(
							'problem' => __( 'Los métodos de pago no aparecen en el checkout', 'woocommerce-venezuela-pro-2025' ),
							'solution' => __( 'Verifica que los métodos estén activados y configurados correctamente', 'woocommerce-venezuela-pro-2025' )
						)
					)
				),

				'currency_manager' => array(
					'title' => __( 'Gestor de Moneda Inteligente', 'woocommerce-venezuela-pro-2025' ),
					'description' => __( 'Sistema dual USD/VES con actualización automática', 'woocommerce-venezuela-pro-2025' ),
					'woocommerce_settings' => array(
						array(
							'title' => __( 'Configuración de Moneda', 'woocommerce-venezuela-pro-2025' ),
							'url' => admin_url( 'admin.php?page=wc-settings&tab=general' ),
							'description' => __( 'Configura la moneda base de tu tienda', 'woocommerce-venezuela-pro-2025' )
						)
					),
					'configuration_steps' => array(
						__( 'Ve a WooCommerce > Configuración > General', 'woocommerce-venezuela-pro-2025' ),
						__( 'Establece VES como moneda base', 'woocommerce-venezuela-pro-2025' ),
						__( 'Configura el formato de moneda venezolano', 'woocommerce-venezuela-pro-2025' ),
						__( 'Activa la visualización dual de precios', 'woocommerce-venezuela-pro-2025' )
					)
				),

				'shipping_methods' => array(
					'title' => __( 'Envíos Nacionales', 'woocommerce-venezuela-pro-2025' ),
					'description' => __( 'Métodos de envío para Venezuela', 'woocommerce-venezuela-pro-2025' ),
					'woocommerce_settings' => array(
						array(
							'title' => __( 'Zonas de Envío', 'woocommerce-venezuela-pro-2025' ),
							'url' => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
							'description' => __( 'Configura las zonas de envío para Venezuela', 'woocommerce-venezuela-pro-2025' )
						)
					),
					'configuration_steps' => array(
						__( 'Ve a WooCommerce > Configuración > Envíos', 'woocommerce-venezuela-pro-2025' ),
						__( 'Crea zonas de envío por estados venezolanos', 'woocommerce-venezuela-pro-2025' ),
						__( 'Agrega métodos de envío locales (MRW, Zoom, etc.)', 'woocommerce-venezuela-pro-2025' ),
						__( 'Configura las tarifas según peso y destino', 'woocommerce-venezuela-pro-2025' )
					)
				),

				'checkout_fields' => array(
					'title' => __( 'Campos de Checkout Personalizados', 'woocommerce-venezuela-pro-2025' ),
					'description' => __( 'Campos específicos para Venezuela', 'woocommerce-venezuela-pro-2025' ),
					'woocommerce_settings' => array(
						array(
							'title' => __( 'Campos de Checkout', 'woocommerce-venezuela-pro-2025' ),
							'url' => admin_url( 'admin.php?page=wc-settings&tab=checkout&section=billing' ),
							'description' => __( 'Configura los campos de facturación', 'woocommerce-venezuela-pro-2025' )
						)
					)
				),

				'electronic_billing' => array(
					'title' => __( 'Sistema de Facturación Electrónica', 'woocommerce-venezuela-pro-2025' ),
					'description' => __( 'Cumplimiento con SENIAT', 'woocommerce-venezuela-pro-2025' ),
					'configuration_steps' => array(
						__( 'Configura los datos fiscales de tu empresa', 'woocommerce-venezuela-pro-2025' ),
						__( 'Establece la numeración secuencial', 'woocommerce-venezuela-pro-2025' ),
						__( 'Configura la firma digital si está disponible', 'woocommerce-venezuela-pro-2025' ),
						__( 'Activa la validación automática con SENIAT', 'woocommerce-venezuela-pro-2025' )
					)
				)
			),

			'woocommerce_integration' => array(
				'title' => __( 'Integración con WooCommerce', 'woocommerce-venezuela-pro-2025' ),
				'description' => __( 'Cómo configurar WooCommerce para Venezuela', 'woocommerce-venezuela-pro-2025' ),
				'sections' => array(
					array(
						'title' => __( 'Configuración General', 'woocommerce-venezuela-pro-2025' ),
						'url' => admin_url( 'admin.php?page=wc-settings&tab=general' ),
						'description' => __( 'Configura la moneda, país y región', 'woocommerce-venezuela-pro-2025' )
					),
					array(
						'title' => __( 'Impuestos', 'woocommerce-venezuela-pro-2025' ),
						'url' => admin_url( 'admin.php?page=wc-settings&tab=tax' ),
						'description' => __( 'Configura el sistema de impuestos', 'woocommerce-venezuela-pro-2025' )
					),
					array(
						'title' => __( 'Métodos de Pago', 'woocommerce-venezuela-pro-2025' ),
						'url' => admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
						'description' => __( 'Activa y configura los métodos de pago', 'woocommerce-venezuela-pro-2025' )
					),
					array(
						'title' => __( 'Envíos', 'woocommerce-venezuela-pro-2025' ),
						'url' => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
						'description' => __( 'Configura las zonas y métodos de envío', 'woocommerce-venezuela-pro-2025' )
					)
				)
			)
		);
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'wp_ajax_wcvs_get_help', array( $this, 'ajax_get_help' ) );
	}

	/**
	 * Get help data for a module
	 *
	 * @param string $module_id
	 * @return array|false
	 */
	public function get_module_help( $module_id ) {
		return isset( $this->help_data['modules'][ $module_id ] ) ? $this->help_data['modules'][ $module_id ] : false;
	}

	/**
	 * Get WooCommerce integration help
	 *
	 * @return array
	 */
	public function get_woocommerce_integration_help() {
		return $this->help_data['woocommerce_integration'];
	}

	/**
	 * Get all help data
	 *
	 * @return array
	 */
	public function get_all_help_data() {
		return $this->help_data;
	}

	/**
	 * AJAX handler for getting help
	 */
	public function ajax_get_help() {
		check_ajax_referer( 'wcvs_get_help', 'nonce' );

		$help_type = sanitize_text_field( $_POST['help_type'] );
		$module_id = sanitize_text_field( $_POST['module_id'] ?? '' );

		switch ( $help_type ) {
			case 'module':
				$help_data = $this->get_module_help( $module_id );
				break;
			case 'woocommerce':
				$help_data = $this->get_woocommerce_integration_help();
				break;
			default:
				$help_data = false;
		}

		if ( $help_data ) {
			wp_send_json_success( $help_data );
		} else {
			wp_send_json_error( __( 'Ayuda no encontrada', 'woocommerce-venezuela-pro-2025' ) );
		}
	}
}
