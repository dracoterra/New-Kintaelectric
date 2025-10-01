<?php
/**
 * Clase abstracta base para módulos de WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite\Includes
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/interface-module.php';

/**
 * Implementación base común para módulos.
 */
abstract class Woocommerce_Venezuela_Suite_Abstract_Module implements Woocommerce_Venezuela_Suite_Module_Interface {
	/** @var string */
	protected $module_slug = '';

	/** @var string */
	protected $module_name = '';

	/** @var string */
	protected $module_version = '1.0.0';

	/** @var string */
	protected $module_description = '';

	/** @var array<string> */
	protected $module_dependencies = array();

	/**
	 * Constructor protegido para evitar instanciación directa.
	 */
	protected function __construct() {}

	/** @inheritDoc */
	public function get_module_slug() {
		return $this->module_slug;
	}

	/** @inheritDoc */
	public function get_module_name() {
		return $this->module_name;
	}

	/** @inheritDoc */
	public function get_module_version() {
		return $this->module_version;
	}

	/** @inheritDoc */
	public function get_module_description() {
		return $this->module_description;
	}

	/** @inheritDoc */
	public function get_module_dependencies() {
		return $this->module_dependencies;
	}

	/** @inheritDoc */
	public function is_module_compatible() {
		// Reglas mínimas de compatibilidad; los módulos concretos pueden sobreescribir.
		return function_exists( 'WC' );
	}

	/** @inheritDoc */
	public function activate_module() {}

	/** @inheritDoc */
	public function deactivate_module() {}

	/** @inheritDoc */
	public function uninstall_module() {}
}


