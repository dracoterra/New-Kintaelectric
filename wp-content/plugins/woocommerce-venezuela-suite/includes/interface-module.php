<?php
/**
 * Interface de módulos para WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite\Includes
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define la interfaz base que deben implementar todos los módulos.
 */
interface Woocommerce_Venezuela_Suite_Module_Interface {
	/**
	 * Retorna el slug único del módulo.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_module_slug();

	/**
	 * Retorna el nombre legible del módulo.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_module_name();

	/**
	 * Retorna la versión del módulo.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_module_version();

	/**
	 * Retorna la descripción del módulo.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_module_description();

	/**
	 * Retorna las dependencias (slugs) de este módulo.
	 *
	 * @since 1.0.0
	 * @return array<string>
	 */
	public function get_module_dependencies();

	/**
	 * Indica si el módulo es compatible con el entorno actual.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_module_compatible();

	/**
	 * Hook de activación del módulo.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function activate_module();

	/**
	 * Hook de desactivación del módulo.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function deactivate_module();

	/**
	 * Lógica de desinstalación y limpieza del módulo.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function uninstall_module();
}


