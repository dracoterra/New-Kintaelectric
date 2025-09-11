<?php
/**
 * Pruebas de compatibilidad con WooCommerce
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para probar la compatibilidad con WooCommerce
 */
class WVP_WooCommerce_Compatibility_Test {
    
    /**
     * Ejecutar todas las pruebas de compatibilidad
     */
    public static function run_all_tests() {
        echo '<div class="wvp-compatibility-test">';
        echo '<h2>Pruebas de Compatibilidad con WooCommerce</h2>';
        
        // Ejecutar pruebas individuales
        self::test_woocommerce_version();
        self::test_hooks_compatibility();
        self::test_payment_gateways();
        self::test_order_meta_compatibility();
        self::test_admin_interface();
        self::test_api_compatibility();
        
        echo '</div>';
    }
    
    /**
     * Probar versión de WooCommerce
     */
    private static function test_woocommerce_version() {
        echo '<h3>1. Versión de WooCommerce</h3>';
        
        if (class_exists('WooCommerce')) {
            $version = WC()->version;
            echo '<p>✅ WooCommerce detectado: ' . $version . '</p>';
            
            if (version_compare($version, '5.0', '>=')) {
                echo '<p>✅ Versión compatible (≥ 5.0)</p>';
            } else {
                echo '<p>❌ Versión incompatible (< 5.0)</p>';
            }
        } else {
            echo '<p>❌ WooCommerce no detectado</p>';
        }
    }
    
    /**
     * Probar compatibilidad de hooks
     */
    private static function test_hooks_compatibility() {
        echo '<h3>2. Compatibilidad de Hooks</h3>';
        
        // Probar hooks modernos
        $modern_hooks = [
            'woocommerce_checkout_create_order' => 'Hook moderno para crear pedidos',
            'woocommerce_store_api_checkout_update_order_meta' => 'Hook para WooCommerce Blocks',
            'woocommerce_blocks_loaded' => 'Hook para WooCommerce Blocks',
            'woocommerce_rest_prepare_shop_order_object' => 'Hook para API REST'
        ];
        
        foreach ($modern_hooks as $hook => $description) {
            if (has_action($hook)) {
                echo '<p>✅ ' . $description . ' - Activo</p>';
            } else {
                echo '<p>⚠️ ' . $description . ' - No activo</p>';
            }
        }
    }
    
    /**
     * Probar pasarelas de pago
     */
    private static function test_payment_gateways() {
        echo '<h3>3. Pasarelas de Pago</h3>';
        
        $gateways = WC()->payment_gateways()->get_available_payment_gateways();
        $wvp_gateways = [
            'wvp_zelle' => 'Zelle',
            'wvp_pago_movil' => 'Pago Móvil',
            'wvp_efectivo' => 'Efectivo USD',
            'wvp_efectivo_bolivares' => 'Efectivo Bolívares',
            'wvp_cashea' => 'Cashea'
        ];
        
        foreach ($wvp_gateways as $id => $name) {
            if (isset($gateways[$id])) {
                $gateway = $gateways[$id];
                echo '<p>✅ ' . $name . ' - Registrada</p>';
                
                // Probar métodos modernos
                if (method_exists($gateway, 'is_available')) {
                    echo '<p>  ✅ Método is_available() disponible</p>';
                } else {
                    echo '<p>  ❌ Método is_available() no disponible</p>';
                }
                
                if (method_exists($gateway, 'validate_fields')) {
                    echo '<p>  ✅ Método validate_fields() disponible</p>';
                } else {
                    echo '<p>  ❌ Método validate_fields() no disponible</p>';
                }
            } else {
                echo '<p>❌ ' . $name . ' - No registrada</p>';
            }
        }
    }
    
    /**
     * Probar compatibilidad de metadatos de pedidos
     */
    private static function test_order_meta_compatibility() {
        echo '<h3>4. Metadatos de Pedidos</h3>';
        
        // Crear pedido de prueba
        $order = wc_create_order();
        
        if ($order) {
            echo '<p>✅ Pedido de prueba creado</p>';
            
            // Probar métodos modernos
            if (method_exists($order, 'update_meta_data')) {
                echo '<p>✅ Método update_meta_data() disponible</p>';
            } else {
                echo '<p>❌ Método update_meta_data() no disponible</p>';
            }
            
            if (method_exists($order, 'get_meta')) {
                echo '<p>✅ Método get_meta() disponible</p>';
            } else {
                echo '<p>❌ Método get_meta() no disponible</p>';
            }
            
            // Limpiar pedido de prueba
            wp_delete_post($order->get_id(), true);
        } else {
            echo '<p>❌ No se pudo crear pedido de prueba</p>';
        }
    }
    
    /**
     * Probar interfaz de administración
     */
    private static function test_admin_interface() {
        echo '<h3>5. Interfaz de Administración</h3>';
        
        // Probar hooks de administración
        $admin_hooks = [
            'woocommerce_admin_order_data_after_billing_address' => 'Hook para datos de pedido',
            'woocommerce_rest_prepare_shop_order_object' => 'Hook para API REST'
        ];
        
        foreach ($admin_hooks as $hook => $description) {
            if (has_action($hook)) {
                echo '<p>✅ ' . $description . ' - Activo</p>';
            } else {
                echo '<p>⚠️ ' . $description . ' - No activo</p>';
            }
        }
    }
    
    /**
     * Probar compatibilidad con API
     */
    private static function test_api_compatibility() {
        echo '<h3>6. Compatibilidad con API</h3>';
        
        // Probar si la API REST está disponible
        if (class_exists('WP_REST_Server')) {
            echo '<p>✅ API REST de WordPress disponible</p>';
        } else {
            echo '<p>❌ API REST de WordPress no disponible</p>';
        }
        
        // Probar si WooCommerce API está disponible
        if (class_exists('WC_REST_Orders_Controller')) {
            echo '<p>✅ API REST de WooCommerce disponible</p>';
        } else {
            echo '<p>❌ API REST de WooCommerce no disponible</p>';
        }
    }
}

// Función para ejecutar las pruebas desde el admin
function wvp_run_compatibility_tests() {
    if (current_user_can('manage_woocommerce')) {
        WVP_WooCommerce_Compatibility_Test::run_all_tests();
    }
}

// Hook para mostrar las pruebas en el admin
add_action('admin_notices', function() {
    if (isset($_GET['wvp_test_compatibility']) && current_user_can('manage_woocommerce')) {
        wvp_run_compatibility_tests();
    }
});
