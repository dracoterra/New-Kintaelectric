<?php
/**
 * Bootstrap del módulo Payment Gateways
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Payment_Gateways
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
	'payment-gateways',
	array(
		'name' => 'Payment Gateways',
		'version' => '1.0.0',
		'description' => 'Pasarelas de pago locales: Transferencia y Pago Móvil.',
		'dependencies' => array( 'currency-converter' ),
	)
);

// Solo cargar e inicializar si el módulo está activo.
if ( class_exists( 'Woocommerce_Venezuela_Suite_Module_Manager' ) ) {
	$module_manager = Woocommerce_Venezuela_Suite_Module_Manager::get_instance();
	if ( $module_manager->get_module_state( 'payment-gateways' ) === 'active' ) {
		// Cargar la clase abstracta primero
		require_once dirname( dirname( __FILE__ ) ) . '/includes/abstract-module.php';
		// Cargar core del módulo.
		require_once __DIR__ . '/includes/class-payment-gateways-core.php';
		// Inicializar
		Woocommerce_Venezuela_Suite_Payment_Gateways_Core::get_instance();
	}
}


