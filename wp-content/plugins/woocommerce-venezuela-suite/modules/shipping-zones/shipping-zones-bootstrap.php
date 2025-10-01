<?php
/**
 * Bootstrap del módulo Shipping Zones
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Shipping_Zones
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
	'shipping-zones',
	array(
		'name' => 'Shipping Zones',
		'version' => '1.0.0',
		'description' => 'Zonas de envío para estados y ciudades de Venezuela.',
		'dependencies' => array(),
	)
);

// Solo cargar e inicializar si el módulo está activo.
if ( class_exists( 'Woocommerce_Venezuela_Suite_Module_Manager' ) ) {
	$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
	if ( $module_manager->get_module_state( 'shipping-zones' ) === 'active' ) {
		// Cargar la clase abstracta primero
		require_once dirname( dirname( __FILE__ ) ) . '/includes/abstract-module.php';
		// Cargar core del módulo.
		require_once __DIR__ . '/includes/class-shipping-zones-core.php';
		// Inicializar
		Woocommerce_Venezuela_Suite_Shipping_Zones_Core::get_instance();
	}
}


