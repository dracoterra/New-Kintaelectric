<?php
/**
 * Registro de Módulos - Metadatos y consulta centralizada
 *
 * @package WooCommerce_Venezuela_Suite\Core
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mantiene metadatos declarativos de los módulos descubiertos.
 */
class Woocommerce_Venezuela_Suite_Module_Registry {
	/** @var self */
	private static $instance = null;

	/** @var array<string,array> */
	private $registry = array();

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
	 * Registra metadatos de un módulo.
	 *
	 * @param string $slug
	 * @param array  $metadata {
	 *   @type string $name
	 *   @type string $version
	 *   @type string $description
	 *   @type array<string> $dependencies
	 * }
	 * @return void
	 */
	public function register_module( $slug, $metadata ) {
		$slug = sanitize_key( $slug );
		$this->registry[ $slug ] = wp_parse_args( $metadata, array(
			'name' => $slug,
			'version' => '1.0.0',
			'description' => '',
			'dependencies' => array(),
			'loaded' => false,
		) );
	}

	/**
	 * Marca un módulo como cargado.
	 *
	 * @param string $slug Slug del módulo.
	 * @return void
	 */
	public function mark_module_loaded( $slug ) {
		$slug = sanitize_key( $slug );
		if ( isset( $this->registry[ $slug ] ) ) {
			$this->registry[ $slug ]['loaded'] = true;
		}
	}

	/**
	 * Obtiene metadatos de un módulo.
	 *
	 * @param string $slug
	 * @return array|null
	 */
	public function get( $slug ) {
		$slug = sanitize_key( $slug );
		return isset( $this->registry[ $slug ] ) ? $this->registry[ $slug ] : null;
	}

	/**
	 * Devuelve todos los módulos registrados.
	 *
	 * @return array<string,array>
	 */
	public function all() {
		return $this->registry;
	}
}


