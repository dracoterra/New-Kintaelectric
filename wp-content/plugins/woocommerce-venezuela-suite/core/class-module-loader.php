<?php
/**
 * Cargador de Módulos - Inicializa módulos activos en el arranque del plugin
 *
 * @package WooCommerce_Venezuela_Suite\Core
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-module-manager.php';

/**
 * Orquesta la carga de módulos activos.
 */
class Woocommerce_Venezuela_Suite_Module_Loader {
	/** @var self */
	private static $instance = null;

	/** @var Woocommerce_Venezuela_Suite_Module_Manager */
	private $manager;

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
	}

	/**
	 * Singleton.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Ejecuta el flujo de carga de módulos activos.
	 * Método principal para inicializar el sistema modular.
	 * Solo se ejecuta una vez por sesión.
	 *
	 * @return void
	 */
	public function boot() {
		// Usar constante de WordPress para evitar ejecución múltiple
		if ( defined( 'WVS_MODULE_LOADER_BOOTED' ) ) {
			return;
		}
		
		define( 'WVS_MODULE_LOADER_BOOTED', true );
		
		// Descubrir módulos disponibles
		$this->manager->discover_modules();
		
		// Cargar módulos activos en orden de dependencias
		$this->manager->load_active_modules();
		
		// Log de módulos cargados para debugging
		$loaded_modules = $this->manager->get_loaded_modules();
		if ( ! empty( $loaded_modules ) ) {
			error_log( 'WVS Module Loader: Módulos cargados: ' . implode( ', ', $loaded_modules ) );
		}
	}
}


