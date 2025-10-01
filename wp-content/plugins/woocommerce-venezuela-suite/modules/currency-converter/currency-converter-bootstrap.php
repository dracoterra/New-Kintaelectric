<?php
/**
 * Plugin Name: Currency Converter Module - WooCommerce Venezuela Suite
 * Plugin URI: https://artifexcodes.com/woocommerce-venezuela-suite
 * Description: Conversión automática USD↔VES con ubicación personalizable, integración Elementor, switches independientes y personalización avanzada de apariencia.
 * Version: 1.0.0
 * Author: Artifex Codes
 * Author URI: https://artifexcodes.com
 * Text Domain: woocommerce-venezuela-suite
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Registrar en el registry.
if ( ! class_exists( 'Woocommerce_Venezuela_Suite_Module_Registry' ) ) {
	require_once dirname( dirname( __FILE__ ) ) . '/core/class-module-registry.php';
}

Woocommerce_Venezuela_Suite_Module_Registry::get_instance()->register_module(
	'currency-converter',
	array(
		'name' => 'Currency Converter Avanzado',
		'version' => '1.0.0',
		'description' => 'Conversión automática USD↔VES con ubicación personalizable, integración Elementor, switches independientes y personalización avanzada.',
		'dependencies' => array( 'bcv-integration' ),
		'path' => plugin_dir_path( __FILE__ ),
		'main_class' => 'Woocommerce_Venezuela_Suite_Currency_Converter_Core',
	)
);

// Solo cargar e inicializar si el módulo está activo.
if ( class_exists( 'Woocommerce_Venezuela_Suite_Module_Manager' ) ) {
	$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
	if ( $module_manager->get_module_state( 'currency-converter' ) === 'active' ) {
		// Cargar la clase abstracta primero
		require_once dirname( dirname( __FILE__ ) ) . '/includes/abstract-module.php';
		// Cargar clases del módulo mejorado.
		require_once __DIR__ . '/includes/class-currency-converter-core.php';
		require_once __DIR__ . '/includes/class-converter-calculator.php';
		require_once __DIR__ . '/includes/class-converter-formatter.php';
		require_once __DIR__ . '/includes/class-converter-cache.php';
		require_once __DIR__ . '/includes/class-converter-location-manager.php';
		require_once __DIR__ . '/includes/class-converter-switch-manager.php';
		require_once __DIR__ . '/includes/class-converter-appearance-manager.php';
		// require_once __DIR__ . '/includes/class-converter-elementor-integration.php'; // Cargado condicionalmente
		// require_once __DIR__ . '/includes/class-converter-shortcodes.php'; // No existe aún
		// require_once __DIR__ . '/includes/class-converter-widgets.php'; // No existe aún
		// require_once __DIR__ . '/includes/class-converter-admin.php'; // No existe aún
		// require_once __DIR__ . '/includes/class-converter-public.php'; // No existe aún
		// Inicializar
		Woocommerce_Venezuela_Suite_Currency_Converter_Core::get_instance();
	}
}


