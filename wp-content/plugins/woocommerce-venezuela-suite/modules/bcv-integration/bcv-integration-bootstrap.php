<?php
/**
 * Plugin Name: BCV Integration Module - WooCommerce Venezuela Suite
 * Plugin URI: https://artifexcodes.com/woocommerce-venezuela-suite
 * Description: Integración avanzada con BCV Dólar Tracker, análisis predictivo, alertas inteligentes y optimización de performance para tipos de cambio en Venezuela.
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
 * @package WooCommerce_Venezuela_Suite\Modules\BCV_Integration
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Registro de metadatos del módulo.
if ( ! class_exists( 'Woocommerce_Venezuela_Suite_Module_Registry' ) ) {
	require_once dirname( dirname( __FILE__ ) ) . '/core/class-module-registry.php';
}

Woocommerce_Venezuela_Suite_Module_Registry::get_instance()->register_module(
	'bcv-integration',
	array(
		'name' => 'BCV Integration Avanzado',
		'version' => '1.0.0',
		'description' => 'Integración avanzada con BCV Dólar Tracker, análisis predictivo, alertas inteligentes y optimización de performance.',
		'dependencies' => array(),
		'path' => plugin_dir_path( __FILE__ ),
		'main_class' => 'Woocommerce_Venezuela_Suite_BCV_Integration_Core',
	)
);

// Carga clases del módulo mejorado.
require_once __DIR__ . '/includes/class-bcv-integration-core.php';
require_once __DIR__ . '/includes/class-bcv-tracker-wrapper.php';
require_once __DIR__ . '/includes/class-bcv-predictive-analytics.php';
require_once __DIR__ . '/includes/class-bcv-volatility-analyzer.php';
require_once __DIR__ . '/includes/class-bcv-alert-system.php';
require_once __DIR__ . '/includes/class-bcv-performance-monitor.php';
require_once __DIR__ . '/includes/class-bcv-cache-manager.php';
require_once __DIR__ . '/includes/class-bcv-api-manager.php';

// Inicializa el módulo si está activo.
if ( class_exists( 'Woocommerce_Venezuela_Suite_Module_Manager' ) ) {
	$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
	if ( $module_manager->get_module_state( 'bcv-integration' ) === 'active' ) {
		Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
	}
}


