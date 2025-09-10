<?php
/**
 * Script de prueba para verificar compatibilidad con HPOS
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_HPOS_Test {
    
    /**
     * Ejecutar todas las pruebas de compatibilidad
     */
    public static function run_all_tests() {
        echo "<h2>🧪 Pruebas de Compatibilidad HPOS - WooCommerce Venezuela Pro</h2>\n";
        
        $tests = array(
            'test_hpos_declaration' => 'Declaración de compatibilidad HPOS',
            'test_hpos_enabled' => 'HPOS habilitado',
            'test_order_meta_methods' => 'Métodos de metadatos de pedidos',
            'test_rest_api_fields' => 'Campos REST API',
            'test_migration_status' => 'Estado de migración',
            'test_plugin_functions' => 'Funciones del plugin'
        );
        
        $results = array();
        
        foreach ($tests as $test_method => $test_name) {
            echo "<h3>🔍 $test_name</h3>\n";
            $result = self::$test_method();
            $results[$test_method] = $result;
            
            if ($result['status'] === 'pass') {
                echo "<p style='color: green;'>✅ {$result['message']}</p>\n";
            } elseif ($result['status'] === 'warning') {
                echo "<p style='color: orange;'>⚠️ {$result['message']}</p>\n";
            } else {
                echo "<p style='color: red;'>❌ {$result['message']}</p>\n";
            }
            
            if (!empty($result['details'])) {
                echo "<pre style='background: #f0f0f0; padding: 10px; margin: 5px 0;'>{$result['details']}</pre>\n";
            }
        }
        
        // Resumen final
        echo "<h2>📊 Resumen de Pruebas</h2>\n";
        $passed = count(array_filter($results, function($r) { return $r['status'] === 'pass'; }));
        $warnings = count(array_filter($results, function($r) { return $r['status'] === 'warning'; }));
        $failed = count(array_filter($results, function($r) { return $r['status'] === 'fail'; }));
        
        echo "<p><strong>Total de pruebas:</strong> " . count($tests) . "</p>\n";
        echo "<p style='color: green;'><strong>Exitosas:</strong> $passed</p>\n";
        echo "<p style='color: orange;'><strong>Advertencias:</strong> $warnings</p>\n";
        echo "<p style='color: red;'><strong>Fallidas:</strong> $failed</p>\n";
        
        if ($failed === 0) {
            echo "<p style='color: green; font-weight: bold;'>🎉 ¡Todas las pruebas pasaron! El plugin es compatible con HPOS.</p>\n";
        } else {
            echo "<p style='color: red; font-weight: bold;'>⚠️ Algunas pruebas fallaron. Revisa los detalles arriba.</p>\n";
        }
    }
    
    /**
     * Probar declaración de compatibilidad HPOS
     */
    public static function test_hpos_declaration() {
        if (!class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            return array(
                'status' => 'fail',
                'message' => 'FeaturesUtil no está disponible',
                'details' => 'La clase FeaturesUtil de WooCommerce no está cargada.'
            );
        }
        
        $compatibility = \Automattic\WooCommerce\Utilities\FeaturesUtil::get_compatible_feature_plugin_info('custom_order_tables', 'woocommerce-venezuela-pro/woocommerce-venezuela-pro.php');
        
        if ($compatibility && $compatibility['compatible']) {
            return array(
                'status' => 'pass',
                'message' => 'Compatibilidad HPOS declarada correctamente',
                'details' => 'El plugin ha declarado compatibilidad con custom_order_tables.'
            );
        } else {
            return array(
                'status' => 'fail',
                'message' => 'Compatibilidad HPOS no declarada',
                'details' => 'El plugin no ha declarado compatibilidad con custom_order_tables.'
            );
        }
    }
    
    /**
     * Probar si HPOS está habilitado
     */
    public static function test_hpos_enabled() {
        if (!class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return array(
                'status' => 'fail',
                'message' => 'OrderUtil no está disponible',
                'details' => 'La clase OrderUtil de WooCommerce no está cargada.'
            );
        }
        
        $hpos_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        
        if ($hpos_enabled) {
            return array(
                'status' => 'pass',
                'message' => 'HPOS está habilitado',
                'details' => 'High-Performance Order Storage está activo en esta instalación.'
            );
        } else {
            return array(
                'status' => 'warning',
                'message' => 'HPOS no está habilitado',
                'details' => 'High-Performance Order Storage no está activo. El plugin funcionará con el sistema tradicional de pedidos.'
            );
        }
    }
    
    /**
     * Probar métodos de metadatos de pedidos
     */
    public static function test_order_meta_methods() {
        // Crear un pedido de prueba
        $order = wc_create_order();
        
        if (!$order) {
            return array(
                'status' => 'fail',
                'message' => 'No se pudo crear pedido de prueba',
                'details' => 'Error al crear un pedido para las pruebas.'
            );
        }
        
        $test_meta_key = '_wvp_test_meta';
        $test_meta_value = 'test_value_' . time();
        
        try {
            // Probar update_meta_data
            $order->update_meta_data($test_meta_key, $test_meta_value);
            $order->save();
            
            // Probar get_meta
            $retrieved_value = $order->get_meta($test_meta_key);
            
            if ($retrieved_value === $test_meta_value) {
                // Limpiar
                $order->delete_meta_data($test_meta_key);
                $order->save();
                $order->delete(true);
                
                return array(
                    'status' => 'pass',
                    'message' => 'Métodos de metadatos funcionan correctamente',
                    'details' => 'update_meta_data() y get_meta() funcionan correctamente con HPOS.'
                );
            } else {
                $order->delete(true);
                return array(
                    'status' => 'fail',
                    'message' => 'Error en métodos de metadatos',
                    'details' => "Valor esperado: $test_meta_value, Valor obtenido: $retrieved_value"
                );
            }
        } catch (Exception $e) {
            if ($order) {
                $order->delete(true);
            }
            return array(
                'status' => 'fail',
                'message' => 'Excepción en métodos de metadatos',
                'details' => $e->getMessage()
            );
        }
    }
    
    /**
     * Probar campos REST API
     */
    public static function test_rest_api_fields() {
        if (!class_exists('WVP_HPOS_Compatibility')) {
            return array(
                'status' => 'fail',
                'message' => 'Clase WVP_HPOS_Compatibility no encontrada',
                'details' => 'La clase de compatibilidad HPOS no está cargada.'
            );
        }
        
        // Verificar si los campos REST están registrados
        $rest_fields = array(
            'billing_cedula_rif',
            'bcv_rate_at_purchase',
            'igtf_amount'
        );
        
        $registered_fields = array();
        foreach ($rest_fields as $field) {
            $field_obj = get_registered_rest_field('shop_order', $field);
            if ($field_obj) {
                $registered_fields[] = $field;
            }
        }
        
        if (count($registered_fields) === count($rest_fields)) {
            return array(
                'status' => 'pass',
                'message' => 'Todos los campos REST están registrados',
                'details' => 'Campos registrados: ' . implode(', ', $registered_fields)
            );
        } else {
            return array(
                'status' => 'warning',
                'message' => 'Algunos campos REST no están registrados',
                'details' => 'Registrados: ' . implode(', ', $registered_fields) . ' | Esperados: ' . implode(', ', $rest_fields)
            );
        }
    }
    
    /**
     * Probar estado de migración
     */
    public static function test_migration_status() {
        $migration_completed = get_option('wvp_hpos_migration_completed', false);
        $migration_date = get_option('wvp_hpos_migration_date', '');
        $migration_stats = get_option('wvp_hpos_migration_stats', array());
        
        if ($migration_completed) {
            return array(
                'status' => 'pass',
                'message' => 'Migración completada',
                'details' => "Fecha: $migration_date | Estadísticas: " . json_encode($migration_stats)
            );
        } else {
            return array(
                'status' => 'warning',
                'message' => 'Migración no completada',
                'details' => 'La migración de datos a HPOS no se ha ejecutado aún.'
            );
        }
    }
    
    /**
     * Probar funciones del plugin
     */
    public static function test_plugin_functions() {
        $functions_to_test = array(
            'WooCommerce_Venezuela_Pro::get_instance',
            'WVP_HPOS_Compatibility::is_hpos_enabled',
            'WVP_HPOS_Compatibility::get_order_id',
            'WVP_HPOS_Compatibility::get_order'
        );
        
        $available_functions = array();
        $missing_functions = array();
        
        foreach ($functions_to_test as $function) {
            if (is_callable($function)) {
                $available_functions[] = $function;
            } else {
                $missing_functions[] = $function;
            }
        }
        
        if (empty($missing_functions)) {
            return array(
                'status' => 'pass',
                'message' => 'Todas las funciones están disponibles',
                'details' => 'Funciones disponibles: ' . implode(', ', $available_functions)
            );
        } else {
            return array(
                'status' => 'fail',
                'message' => 'Algunas funciones no están disponibles',
                'details' => 'Faltantes: ' . implode(', ', $missing_functions)
            );
        }
    }
}

// Ejecutar pruebas si se accede directamente
if (isset($_GET['wvp_test_hpos']) && current_user_can('manage_options')) {
    WVP_HPOS_Test::run_all_tests();
    exit;
}
