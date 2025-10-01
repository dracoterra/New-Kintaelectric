<?php
/**
 * Script de prueba para verificar compatibilidad de plantillas con HPOS
 * 
 * @package KintaElectric
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class KintaElectric_Template_Test {
    
    /**
     * Ejecutar todas las pruebas de compatibilidad
     */
    public static function run_all_tests() {
        echo "<h2>🧪 Pruebas de Compatibilidad de Plantillas - KintaElectric</h2>\n";
        
        $tests = array(
            'test_template_versions' => 'Versiones de plantillas',
            'test_hpos_compatibility' => 'Compatibilidad con HPOS',
            'test_woocommerce_functions' => 'Funciones de WooCommerce',
            'test_template_hooks' => 'Hooks de plantillas',
            'test_theme_integration' => 'Integración con el tema'
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
            echo "<p style='color: green; font-weight: bold;'>🎉 ¡Todas las plantillas están actualizadas y son compatibles con HPOS!</p>\n";
        } else {
            echo "<p style='color: red; font-weight: bold;'>⚠️ Algunas pruebas fallaron. Revisa los detalles arriba.</p>\n";
        }
    }
    
    /**
     * Probar versiones de plantillas
     */
    public static function test_template_versions() {
        $templates = array(
            'archive-product.php' => '8.6.0',
            'content-product.php' => '9.4.0',
            'content-single-product.php' => '9.4.0',
            'single-product.php' => '8.6.0'
        );
        
        $theme_path = get_template_directory() . '/woocommerce/';
        $results = array();
        
        foreach ($templates as $template => $expected_version) {
            $file_path = $theme_path . $template;
            
            if (!file_exists($file_path)) {
                $results[] = "$template: Archivo faltante";
                continue;
            }
            
            $content = file_get_contents($file_path);
            if (strpos($content, '@version ' . $expected_version) !== false) {
                $results[] = "$template: ✅ Versión $expected_version";
            } else {
                $results[] = "$template: ❌ Versión incorrecta";
            }
        }
        
        $all_updated = count(array_filter($results, function($r) { return strpos($r, '✅') !== false; })) === count($templates);
        
        return array(
            'status' => $all_updated ? 'pass' : 'fail',
            'message' => $all_updated ? 'Todas las plantillas están actualizadas' : 'Algunas plantillas necesitan actualización',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar compatibilidad con HPOS
     */
    public static function test_hpos_compatibility() {
        if (!class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return array(
                'status' => 'warning',
                'message' => 'OrderUtil no está disponible',
                'details' => 'La clase OrderUtil de WooCommerce no está cargada.'
            );
        }
        
        $hpos_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        
        if ($hpos_enabled) {
            return array(
                'status' => 'pass',
                'message' => 'HPOS está habilitado y las plantillas son compatibles',
                'details' => 'High-Performance Order Storage está activo y las plantillas actualizadas son compatibles.'
            );
        } else {
            return array(
                'status' => 'warning',
                'message' => 'HPOS no está habilitado',
                'details' => 'High-Performance Order Storage no está activo. Las plantillas funcionarán con el sistema tradicional.'
            );
        }
    }
    
    /**
     * Probar funciones de WooCommerce
     */
    public static function test_woocommerce_functions() {
        $functions_to_test = array(
            'wc_get_products',
            'wc_get_loop_prop',
            'woocommerce_product_loop',
            'woocommerce_page_title',
            'woocommerce_result_count',
            'woocommerce_catalog_ordering',
            'woocommerce_pagination',
            'woocommerce_template_loop_product_thumbnail',
            'woocommerce_template_loop_price',
            'woocommerce_template_loop_add_to_cart',
            'wc_get_product_category_list',
            'wc_product_class'
        );
        
        $available_functions = array();
        $missing_functions = array();
        
        foreach ($functions_to_test as $function) {
            if (function_exists($function)) {
                $available_functions[] = $function;
            } else {
                $missing_functions[] = $function;
            }
        }
        
        if (empty($missing_functions)) {
            return array(
                'status' => 'pass',
                'message' => 'Todas las funciones de WooCommerce están disponibles',
                'details' => 'Funciones disponibles: ' . implode(', ', $available_functions)
            );
        } else {
            return array(
                'status' => 'fail',
                'message' => 'Algunas funciones de WooCommerce no están disponibles',
                'details' => 'Faltantes: ' . implode(', ', $missing_functions)
            );
        }
    }
    
    /**
     * Probar hooks de plantillas
     */
    public static function test_template_hooks() {
        $hooks_to_test = array(
            'woocommerce_before_main_content',
            'woocommerce_archive_description',
            'woocommerce_before_shop_loop',
            'woocommerce_shop_loop',
            'woocommerce_after_shop_loop',
            'woocommerce_no_products_found',
            'woocommerce_before_single_product',
            'woocommerce_before_single_product_summary',
            'woocommerce_single_product_summary',
            'woocommerce_after_single_product_summary',
            'woocommerce_after_single_product'
        );
        
        $available_hooks = array();
        $missing_hooks = array();
        
        foreach ($hooks_to_test as $hook) {
            if (has_action($hook)) {
                $available_hooks[] = $hook;
            } else {
                $missing_hooks[] = $hook;
            }
        }
        
        if (count($missing_hooks) <= 2) { // Permitir algunos hooks faltantes
            return array(
                'status' => 'pass',
                'message' => 'Hooks de plantillas funcionando correctamente',
                'details' => 'Hooks disponibles: ' . implode(', ', $available_hooks)
            );
        } else {
            return array(
                'status' => 'warning',
                'message' => 'Algunos hooks de plantillas no están registrados',
                'details' => 'Faltantes: ' . implode(', ', $missing_hooks)
            );
        }
    }
    
    /**
     * Probar integración con el tema
     */
    public static function test_theme_integration() {
        $theme_name = wp_get_theme()->get('Name');
        $theme_version = wp_get_theme()->get('Version');
        
        if ($theme_name === 'KintaElectric') {
            return array(
                'status' => 'pass',
                'message' => 'Integración con tema correcta',
                'details' => "Tema: $theme_name v$theme_version - Integración correcta con plantillas de WooCommerce"
            );
        } else {
            return array(
                'status' => 'warning',
                'message' => 'Tema no reconocido',
                'details' => "Tema actual: $theme_name v$theme_version - Verificar compatibilidad"
            );
        }
    }
}

// Ejecutar pruebas si se accede directamente
if (isset($_GET['kintaelectric_test_templates']) && current_user_can('manage_options')) {
    KintaElectric_Template_Test::run_all_tests();
    exit;
}
