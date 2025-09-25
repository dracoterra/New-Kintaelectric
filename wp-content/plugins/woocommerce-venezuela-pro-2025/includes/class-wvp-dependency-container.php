<?php

/**
 * Dependency Injection Container
 *
 * Manages dependencies and provides a clean way to inject them into classes.
 *
 * @link       https://kintaelectric.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 */

/**
 * Dependency injection container class.
 *
 * This class manages dependencies and provides a clean way to inject them
 * into other classes, eliminating the need for singleton patterns.
 *
 * @since      1.0.0
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/includes
 * @author     Kinta Electric <info@kintaelectric.com>
 */
class WVP_Dependency_Container {

	/**
	 * The container's dependencies.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $dependencies    Array of registered dependencies.
	 */
	protected $dependencies = array();

	/**
	 * The container's factories.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $factories    Array of factory functions.
	 */
	protected $factories = array();

	/**
	 * Whether the container has been initialized.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      boolean    $initialized    Whether the container has been initialized.
	 */
	protected $initialized = false;

	/**
	 * Register a dependency in the container.
	 *
	 * @since    1.0.0
	 * @param    string    $name        The dependency name.
	 * @param    mixed     $dependency  The dependency value or factory function.
	 * @param    boolean   $singleton   Whether this should be treated as a singleton.
	 */
	public function register( $name, $dependency, $singleton = true ) {
		// Prevent multiple registrations
		if ( $this->initialized && isset( $this->dependencies[ $name ] ) ) {
			return;
		}
		
		if ( is_callable( $dependency ) ) {
			$this->factories[ $name ] = array(
				'factory'  => $dependency,
				'singleton' => $singleton,
			);
		} else {
			$this->dependencies[ $name ] = $dependency;
		}
		
		$this->initialized = true;
	}

	/**
	 * Get a dependency from the container.
	 *
	 * @since    1.0.0
	 * @param    string    $name    The dependency name.
	 * @return   mixed              The dependency value.
	 * @throws   Exception          If dependency is not found.
	 */
	public function get( $name ) {
		// Check if it's a direct dependency
		if ( isset( $this->dependencies[ $name ] ) ) {
			return $this->dependencies[ $name ];
		}

		// Check if it's a factory
		if ( isset( $this->factories[ $name ] ) ) {
			$factory_data = $this->factories[ $name ];
			
			// If it's a singleton and already instantiated, return the cached instance
			if ( $factory_data['singleton'] && isset( $this->dependencies[ $name ] ) ) {
				return $this->dependencies[ $name ];
			}

			// Create the instance
			$instance = call_user_func( $factory_data['factory'], $this );
			
			// If it's a singleton, cache it
			if ( $factory_data['singleton'] ) {
				$this->dependencies[ $name ] = $instance;
			}
			
			return $instance;
		}

		throw new Exception( "Dependency '{$name}' not found in container." );
	}

	/**
	 * Check if a dependency exists in the container.
	 *
	 * @since    1.0.0
	 * @param    string    $name    The dependency name.
	 * @return   boolean            True if dependency exists, false otherwise.
	 */
	public function has( $name ) {
		return isset( $this->dependencies[ $name ] ) || isset( $this->factories[ $name ] );
	}

	/**
	 * Remove a dependency from the container.
	 *
	 * @since    1.0.0
	 * @param    string    $name    The dependency name.
	 */
	public function remove( $name ) {
		unset( $this->dependencies[ $name ] );
		unset( $this->factories[ $name ] );
	}

	/**
	 * Get all registered dependency names.
	 *
	 * @since    1.0.0
	 * @return   array    Array of dependency names.
	 */
	public function get_dependency_names() {
		return array_merge( array_keys( $this->dependencies ), array_keys( $this->factories ) );
	}

	/**
	 * Clear all dependencies from the container.
	 *
	 * @since    1.0.0
	 */
	public function clear() {
		$this->dependencies = array();
		$this->factories = array();
	}

}
