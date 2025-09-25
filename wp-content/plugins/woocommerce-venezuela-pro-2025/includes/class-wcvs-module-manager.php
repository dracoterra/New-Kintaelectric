<?php

/**
 * WooCommerce Venezuela Suite 2025 - Module Manager
 *
 * Gestor de módulos activables/desactivables con sistema
 * de dependencias y conflictos.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Manager class
 */
class WCVS_Module_Manager {

	/**
	 * Registered modules
	 *
	 * @var array
	 */
	private $modules = array();

	/**
	 * Active modules
	 *
	 * @var array
	 */
	private $active_modules = array();

	/**
	 * Loaded modules
	 *
	 * @var array
	 */
	private $loaded_modules = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'load_active_modules' ) );
		add_action( 'wp_ajax_wcvs_toggle_module', array( $this, 'ajax_toggle_module' ) );
	}

	/**
	 * Register a module
	 *
	 * @param string $module_id
	 * @param array  $config
	 */
	public function register_module( $module_id, $config ) {
		$default_config = array(
			'name' => '',
			'description' => '',
			'version' => '1.0.0',
			'class' => '',
			'file' => '',
			'dependencies' => array(),
			'conflicts' => array(),
			'priority' => 10,
			'category' => 'core',
			'icon' => 'dashicons-admin-plugins',
			'help_url' => '',
			'woocommerce_settings' => array()
		);

		$this->modules[ $module_id ] = wp_parse_args( $config, $default_config );
	}

	/**
	 * Get all registered modules
	 *
	 * @return array
	 */
	public function get_modules() {
		return $this->modules;
	}

	/**
	 * Get active modules
	 *
	 * @return array
	 */
	public function get_active_modules() {
		if ( empty( $this->active_modules ) ) {
			$this->active_modules = get_option( 'wcvs_active_modules', array() );
		}
		return $this->active_modules;
	}

	/**
	 * Check if module is active
	 *
	 * @param string $module_id
	 * @return bool
	 */
	public function is_module_active( $module_id ) {
		$active_modules = $this->get_active_modules();
		return isset( $active_modules[ $module_id ] ) && $active_modules[ $module_id ];
	}

	/**
	 * Activate a module
	 *
	 * @param string $module_id
	 * @return bool|WP_Error
	 */
	public function activate_module( $module_id ) {
		if ( ! isset( $this->modules[ $module_id ] ) ) {
			return new WP_Error( 'module_not_found', __( 'Módulo no encontrado', 'woocommerce-venezuela-pro-2025' ) );
		}

		// Check dependencies
		$dependencies = $this->modules[ $module_id ]['dependencies'];
		foreach ( $dependencies as $dependency ) {
			if ( ! $this->is_module_active( $dependency ) ) {
				return new WP_Error( 'dependency_missing', 
					sprintf( __( 'El módulo requiere %s para funcionar', 'woocommerce-venezuela-pro-2025' ), 
						$this->modules[ $dependency ]['name'] ) );
			}
		}

		// Check conflicts
		$conflicts = $this->modules[ $module_id ]['conflicts'];
		foreach ( $conflicts as $conflict ) {
			if ( $this->is_module_active( $conflict ) ) {
				return new WP_Error( 'module_conflict', 
					sprintf( __( 'El módulo entra en conflicto con %s', 'woocommerce-venezuela-pro-2025' ), 
						$this->modules[ $conflict ]['name'] ) );
			}
		}

		// Activate module
		$active_modules = $this->get_active_modules();
		$active_modules[ $module_id ] = true;
		update_option( 'wcvs_active_modules', $active_modules );
		$this->active_modules = $active_modules;

		// Load module
		$this->load_module( $module_id );

		// Log activation
		do_action( 'wcvs_module_activated', $module_id );

		return true;
	}

	/**
	 * Deactivate a module
	 *
	 * @param string $module_id
	 * @return bool|WP_Error
	 */
	public function deactivate_module( $module_id ) {
		if ( ! isset( $this->modules[ $module_id ] ) ) {
			return new WP_Error( 'module_not_found', __( 'Módulo no encontrado', 'woocommerce-venezuela-pro-2025' ) );
		}

		// Check if other modules depend on this one
		foreach ( $this->modules as $id => $config ) {
			if ( in_array( $module_id, $config['dependencies'] ) && $this->is_module_active( $id ) ) {
				return new WP_Error( 'dependency_active', 
					sprintf( __( 'No se puede desactivar porque %s lo requiere', 'woocommerce-venezuela-pro-2025' ), 
						$config['name'] ) );
			}
		}

		// Deactivate module
		$active_modules = $this->get_active_modules();
		unset( $active_modules[ $module_id ] );
		update_option( 'wcvs_active_modules', $active_modules );
		$this->active_modules = $active_modules;

		// Unload module
		$this->unload_module( $module_id );

		// Log deactivation
		do_action( 'wcvs_module_deactivated', $module_id );

		return true;
	}

	/**
	 * Load a module
	 *
	 * @param string $module_id
	 * @return bool
	 */
	public function load_module( $module_id ) {
		if ( ! isset( $this->modules[ $module_id ] ) ) {
			return false;
		}

		if ( isset( $this->loaded_modules[ $module_id ] ) ) {
			return true;
		}

		$module_config = $this->modules[ $module_id ];
		$module_file = WCVS_PLUGIN_DIR . $module_config['file'];

		if ( file_exists( $module_file ) ) {
			require_once $module_file;

			if ( class_exists( $module_config['class'] ) ) {
				$module_instance = new $module_config['class']();
				$this->loaded_modules[ $module_id ] = $module_instance;

				// Initialize module
				if ( method_exists( $module_instance, 'init' ) ) {
					$module_instance->init();
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Unload a module
	 *
	 * @param string $module_id
	 */
	public function unload_module( $module_id ) {
		if ( isset( $this->loaded_modules[ $module_id ] ) ) {
			$module_instance = $this->loaded_modules[ $module_id ];

			// Deinitialize module
			if ( method_exists( $module_instance, 'deinit' ) ) {
				$module_instance->deinit();
			}

			unset( $this->loaded_modules[ $module_id ] );
		}
	}

	/**
	 * Load all active modules
	 */
	public function load_active_modules() {
		$active_modules = $this->get_active_modules();

		foreach ( $active_modules as $module_id => $is_active ) {
			if ( $is_active ) {
				$this->load_module( $module_id );
			}
		}
	}

	/**
	 * Get module instance
	 *
	 * @param string $module_id
	 * @return object|null
	 */
	public function get_module_instance( $module_id ) {
		return isset( $this->loaded_modules[ $module_id ] ) ? $this->loaded_modules[ $module_id ] : null;
	}

	/**
	 * Get modules by category
	 *
	 * @param string $category
	 * @return array
	 */
	public function get_modules_by_category( $category ) {
		$filtered_modules = array();

		foreach ( $this->modules as $module_id => $config ) {
			if ( $config['category'] === $category ) {
				$filtered_modules[ $module_id ] = $config;
			}
		}

		return $filtered_modules;
	}

	/**
	 * Get module health status
	 *
	 * @param string $module_id
	 * @return array
	 */
	public function get_module_health( $module_id ) {
		$health = array(
			'status' => 'unknown',
			'message' => '',
			'details' => array()
		);

		if ( ! isset( $this->modules[ $module_id ] ) ) {
			$health['status'] = 'error';
			$health['message'] = __( 'Módulo no encontrado', 'woocommerce-venezuela-pro-2025' );
			return $health;
		}

		$module_config = $this->modules[ $module_id ];
		$module_file = WCVS_PLUGIN_DIR . $module_config['file'];

		// Check if file exists
		if ( ! file_exists( $module_file ) ) {
			$health['status'] = 'error';
			$health['message'] = __( 'Archivo del módulo no encontrado', 'woocommerce-venezuela-pro-2025' );
			return $health;
		}

		// Check if class exists
		if ( ! class_exists( $module_config['class'] ) ) {
			$health['status'] = 'error';
			$health['message'] = __( 'Clase del módulo no encontrada', 'woocommerce-venezuela-pro-2025' );
			return $health;
		}

		// Check dependencies
		$dependencies = $module_config['dependencies'];
		foreach ( $dependencies as $dependency ) {
			if ( ! $this->is_module_active( $dependency ) ) {
				$health['status'] = 'warning';
				$health['message'] = sprintf( __( 'Dependencia faltante: %s', 'woocommerce-venezuela-pro-2025' ), 
					$this->modules[ $dependency ]['name'] );
				return $health;
			}
		}

		$health['status'] = 'healthy';
		$health['message'] = __( 'Módulo funcionando correctamente', 'woocommerce-venezuela-pro-2025' );

		return $health;
	}

	/**
	 * Load active modules
	 */
	public function load_active_modules() {
		$active_modules = $this->get_active_modules();
		
		foreach ( $active_modules as $module_id => $is_active ) {
			if ( $is_active && isset( $this->modules[ $module_id ] ) ) {
				$module_config = $this->modules[ $module_id ];
				
				// Load module file if specified
				if ( ! empty( $module_config['file'] ) ) {
					$module_file = WCVS_PLUGIN_DIR . $module_config['file'];
					if ( file_exists( $module_file ) ) {
						require_once $module_file;
					}
				}
				
				// Initialize module class if specified
				if ( ! empty( $module_config['class'] ) && class_exists( $module_config['class'] ) ) {
					$this->loaded_modules[ $module_id ] = new $module_config['class']();
				}
			}
		}
	}

	/**
	 * AJAX handler for toggling modules
	 */
	public function ajax_toggle_module() {
		check_ajax_referer( 'wcvs_toggle_module', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Permisos insuficientes', 'woocommerce-venezuela-pro-2025' ) );
		}

		$module_id = sanitize_text_field( $_POST['module_id'] );
		$action = sanitize_text_field( $_POST['action_type'] );

		if ( $action === 'activate' ) {
			$result = $this->activate_module( $module_id );
		} else {
			$result = $this->deactivate_module( $module_id );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( array(
				'message' => sprintf( __( 'Módulo %s correctamente', 'woocommerce-venezuela-pro-2025' ), 
					$action === 'activate' ? 'activado' : 'desactivado' )
			));
		}
	}
}
