<?php
/**
 * Bootstrap del módulo Fiscal Compliance (SENIAT)
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Fiscal_Compliance
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
	'fiscal-compliance',
	array(
		'name' => 'Fiscal Compliance (SENIAT)',
		'version' => '1.0.0',
		'description' => 'IVA, IGTF y validación RIF básicos.',
		'dependencies' => array( 'currency-converter' ),
	)
);

// Cargar core del módulo.
require_once __DIR__ . '/includes/class-fiscal-compliance-core.php';

// Inicializa el módulo si está activo.
if ( class_exists( 'Woocommerce_Venezuela_Suite_Module_Manager' ) ) {
	$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
	if ( $module_manager->get_module_state( 'fiscal-compliance' ) === 'active' ) {
		Woocommerce_Venezuela_Suite_Fiscal_Compliance_Core::get_instance();
	}
}


