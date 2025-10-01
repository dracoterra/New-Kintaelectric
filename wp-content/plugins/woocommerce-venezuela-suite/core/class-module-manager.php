<?php
/**
 * Gestor de Módulos Avanzado - Administra la carga, estado y dependencias de módulos
 *
 * @package WooCommerce_Venezuela_Suite\Core
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-module-registry.php';

/**
 * Administra el estado, carga y dependencias de módulos del plugin.
 * Implementa arquitectura modular completa según PLAN-ARQUITECTURA-MODULAR.md
 */
class Woocommerce_Venezuela_Suite_Module_Manager {

	/** @var self */
	private static $instance = null;

	/** @var Woocommerce_Venezuela_Suite_Module_Registry */
	private $registry;

	/** @var array Módulos descubiertos */
	private $discovered_modules = array();

	/** @var array Estados de módulos */
	private $module_states = array();

	/** @var array Módulos cargados */
	private $loaded_modules = array();

	/** @var array Dependencias resueltas */
	private $resolved_dependencies = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->registry = Woocommerce_Venezuela_Suite_Module_Registry::get_instance();
		$this->load_module_states();
		$this->discover_modules();
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
	 * Descubre módulos disponibles en el directorio modules/.
	 * Implementa detección automática según arquitectura modular.
	 *
	 * @return void
	 */
	public function discover_modules() {
		// Evitar descubrimiento múltiple
		if ( ! empty( $this->discovered_modules ) ) {
			return;
		}

		$modules_dir = WP_PLUGIN_DIR . '/woocommerce-venezuela-suite/modules/';
		
		if ( ! is_dir( $modules_dir ) ) {
			return;
		}

		$module_dirs = glob( $modules_dir . '*', GLOB_ONLYDIR );
		
		foreach ( $module_dirs as $module_dir ) {
			$module_slug = basename( $module_dir );
               $bootstrap_file = $module_dir . '/' . $module_slug . '-bootstrap.php';
			
			if ( file_exists( $bootstrap_file ) ) {
				$module_metadata = $this->parse_module_metadata( $bootstrap_file );
				
				$this->discovered_modules[ $module_slug ] = array(
					'slug' => $module_slug,
					'path' => $module_dir,
					'bootstrap_file' => $bootstrap_file,
					'status' => $this->get_module_state( $module_slug ),
					'metadata' => $module_metadata,
					'dependencies' => $module_metadata['dependencies'] ?? array(),
					'version' => $module_metadata['version'] ?? '1.0.0',
					'description' => $module_metadata['description'] ?? '',
				);

				// Registrar en el registry
				$this->registry->register_module( $module_slug, $module_metadata );
			}
		}

		// Resolver dependencias
		$this->resolve_dependencies();
	}

	/**
	 * Parsea metadatos del archivo bootstrap del módulo.
	 *
	 * @param string $bootstrap_file Ruta al archivo bootstrap.
	 * @return array Metadatos del módulo.
	 */
	private function parse_module_metadata( $bootstrap_file ) {
		$metadata = array(
			'name' => '',
			'version' => '1.0.0',
			'description' => '',
			'dependencies' => array(),
		);

		$file_content = file_get_contents( $bootstrap_file );
		
		// Extraer información del header del plugin
		if ( preg_match( '/Plugin Name:\s*(.+)/i', $file_content, $matches ) ) {
			$metadata['name'] = trim( $matches[1] );
		}
		
		if ( preg_match( '/Version:\s*(.+)/i', $file_content, $matches ) ) {
			$metadata['version'] = trim( $matches[1] );
		}
		
		if ( preg_match( '/Description:\s*(.+)/i', $file_content, $matches ) ) {
			$metadata['description'] = trim( $matches[1] );
		}

		// Buscar dependencias en el código
		if ( preg_match( '/dependencies.*?=>\s*array\((.*?)\)/s', $file_content, $matches ) ) {
			$deps_string = $matches[1];
			if ( preg_match_all( "/'([^']+)'/", $deps_string, $dep_matches ) ) {
				$metadata['dependencies'] = $dep_matches[1];
			}
		}

		return $metadata;
	}

	/**
	 * Resuelve dependencias entre módulos.
	 *
	 * @return void
	 */
	private function resolve_dependencies() {
		foreach ( $this->discovered_modules as $module_slug => $module_data ) {
			$this->resolved_dependencies[ $module_slug ] = $this->get_module_dependencies( $module_slug );
		}
	}

	/**
	 * Obtiene dependencias de un módulo recursivamente.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @param array $visited Módulos ya visitados (para evitar ciclos).
	 * @return array Lista de dependencias.
	 */
	private function get_module_dependencies( $module_slug, $visited = array() ) {
		if ( in_array( $module_slug, $visited ) ) {
			error_log( "WVS Module Manager: Dependencia circular detectada para $module_slug" );
			return array();
		}

		$visited[] = $module_slug;
		$dependencies = array();

		if ( isset( $this->discovered_modules[ $module_slug ]['dependencies'] ) ) {
			foreach ( $this->discovered_modules[ $module_slug ]['dependencies'] as $dep ) {
				$dependencies[] = $dep;
				$dependencies = array_merge( $dependencies, $this->get_module_dependencies( $dep, $visited ) );
			}
		}

		return array_unique( $dependencies );
	}

	/**
	 * Activa un módulo y sus dependencias.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @return bool True si se activó correctamente.
	 */
	public function activate_module( $module_slug ) {
		if ( ! isset( $this->discovered_modules[ $module_slug ] ) ) {
			error_log( "WVS Module Manager: Módulo $module_slug no encontrado" );
			return false;
		}

		// Verificar dependencias
		$dependencies = $this->resolved_dependencies[ $module_slug ] ?? array();
		foreach ( $dependencies as $dep ) {
			if ( $this->get_module_state( $dep ) !== 'active' && $this->get_module_state( $dep ) !== 1 ) {
				error_log( "WVS Module Manager: Dependencia $dep no está activa para $module_slug" );
				return false;
			}
		}

		$this->module_states[ $module_slug ] = 'active';
		$this->save_module_states();
		
		// Cargar módulo inmediatamente si está disponible
		$this->load_module( $module_slug );
		
		return true;
	}

	/**
	 * Desactiva un módulo y módulos que dependen de él.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @return bool True si se desactivó correctamente.
	 */
	public function deactivate_module( $module_slug ) {
		if ( ! isset( $this->discovered_modules[ $module_slug ] ) ) {
			return false;
		}

		// Verificar si otros módulos dependen de este
		$dependent_modules = $this->get_dependent_modules( $module_slug );
		if ( ! empty( $dependent_modules ) ) {
			error_log( "WVS Module Manager: No se puede desactivar $module_slug porque otros módulos dependen de él: " . implode( ', ', $dependent_modules ) );
			return false;
		}

		$this->module_states[ $module_slug ] = 'inactive';
		$this->save_module_states();
		
		return true;
	}

	/**
	 * Obtiene módulos que dependen de un módulo específico.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @return array Lista de módulos dependientes.
	 */
	private function get_dependent_modules( $module_slug ) {
		$dependent = array();
		
		foreach ( $this->resolved_dependencies as $module => $dependencies ) {
			if ( in_array( $module_slug, $dependencies ) && ( $this->get_module_state( $module ) === 'active' || $this->get_module_state( $module ) === 1 ) ) {
				$dependent[] = $module;
			}
		}
		
		return $dependent;
	}

	/**
	 * Obtiene el estado de un módulo.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @return string Estado del módulo (active|inactive|error).
	 */
	public function get_module_state( $module_slug ) {
		return isset( $this->module_states[ $module_slug ] ) ? $this->module_states[ $module_slug ] : 'inactive';
	}

	/**
	 * Carga módulos activos en orden de dependencias.
	 *
	 * @return void
	 */
	public function load_active_modules() {
		$load_order = $this->get_load_order();
		
		foreach ( $load_order as $module_slug ) {
			$state = $this->get_module_state( $module_slug );
			if ( $state === 'active' || $state === 1 ) {
				$this->load_module( $module_slug );
			}
		}
	}

	/**
	 * Determina el orden de carga basado en dependencias.
	 *
	 * @return array Orden de carga de módulos.
	 */
	private function get_load_order() {
		$load_order = array();
		$visited = array();
		
		foreach ( $this->discovered_modules as $module_slug => $module_data ) {
			if ( ! in_array( $module_slug, $visited ) ) {
				$this->topological_sort( $module_slug, $visited, $load_order );
			}
		}
		
		return array_reverse( $load_order );
	}

	/**
	 * Ordenamiento topológico para resolver dependencias.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @param array $visited Módulos visitados.
	 * @param array $load_order Orden de carga.
	 * @return void
	 */
	private function topological_sort( $module_slug, &$visited, &$load_order ) {
		$visited[] = $module_slug;
		
		$dependencies = $this->resolved_dependencies[ $module_slug ] ?? array();
		foreach ( $dependencies as $dep ) {
			if ( ! in_array( $dep, $visited ) ) {
				$this->topological_sort( $dep, $visited, $load_order );
			}
		}
		
		$load_order[] = $module_slug;
	}

	/**
	 * Carga un módulo específico.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @return bool True si se cargó correctamente.
	 */
	private function load_module( $module_slug ) {
		if ( in_array( $module_slug, $this->loaded_modules ) ) {
			return true; // Ya cargado
		}

		$module_data = $this->discovered_modules[ $module_slug ];
		
		if ( file_exists( $module_data['bootstrap_file'] ) ) {
			try {
				require_once $module_data['bootstrap_file'];
				$this->loaded_modules[] = $module_slug;
				
				// Marcar como cargado en el registry
				$this->registry->mark_module_loaded( $module_slug );
				
				return true;
			} catch ( Exception $e ) {
				error_log( "WVS Module Manager: Error cargando módulo $module_slug: " . $e->getMessage() );
				$this->module_states[ $module_slug ] = 'error';
				$this->save_module_states();
				return false;
			}
		}
		
		return false;
	}

	/**
	 * Obtiene módulos descubiertos.
	 *
	 * @return array Módulos descubiertos.
	 */
	public function get_discovered_modules() {
		return $this->discovered_modules;
	}

	/**
	 * Obtiene módulos cargados.
	 *
	 * @return array Módulos cargados.
	 */
	public function get_loaded_modules() {
		return $this->loaded_modules;
	}

	/**
	 * Verifica si un módulo está cargado.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @return bool True si está cargado.
	 */
	public function is_module_loaded( $module_slug ) {
		return in_array( $module_slug, $this->loaded_modules );
	}

	/**
	 * Obtiene información completa de un módulo.
	 *
	 * @param string $module_slug Slug del módulo.
	 * @return array|null Información del módulo o null si no existe.
	 */
	public function get_module_info( $module_slug ) {
		if ( ! isset( $this->discovered_modules[ $module_slug ] ) ) {
			return null;
		}

		$module_data = $this->discovered_modules[ $module_slug ];
		
		return array(
			'slug' => $module_slug,
			'name' => $module_data['metadata']['name'] ?? $module_slug,
			'version' => $module_data['version'],
			'description' => $module_data['description'],
			'status' => $this->get_module_state( $module_slug ),
			'loaded' => $this->is_module_loaded( $module_slug ),
			'dependencies' => $module_data['dependencies'],
			'dependent_modules' => $this->get_dependent_modules( $module_slug ),
			'path' => $module_data['path'],
		);
	}

	/**
	 * Carga estados de módulos desde la base de datos.
	 *
	 * @return void
	 */
	private function load_module_states() {
		$this->module_states = get_option( 'wvs_modules_states', array() );
	}

	/**
	 * Guarda estados de módulos en la base de datos.
	 *
	 * @return void
	 */
	private function save_module_states() {
		update_option( 'wvs_modules_states', $this->module_states );
	}

	/**
	 * Reinicia el gestor de módulos.
	 *
	 * @return void
	 */
	public function reset() {
		$this->discovered_modules = array();
		$this->loaded_modules = array();
		$this->resolved_dependencies = array();
		$this->discover_modules();
	}

	/**
	 * Método de debug para verificar el estado de los módulos.
	 *
	 * @return array Estado completo del gestor de módulos.
	 */
	public function debug_info() {
		return array(
			'discovered_modules' => $this->discovered_modules,
			'loaded_modules' => $this->loaded_modules,
			'module_states' => $this->module_states,
			'resolved_dependencies' => $this->resolved_dependencies,
		);
	}
}