<?php
/**
 * Pruebas de optimizaciones
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para probar las optimizaciones
 */
class WVP_Optimizations_Test {
    
    /**
     * Ejecutar todas las pruebas de optimizaciones
     */
    public static function run_all_tests() {
        echo '<div class="wvp-optimizations-test">';
        echo '<h2>Pruebas de Optimizaciones</h2>';
        
        // Ejecutar pruebas individuales
        self::test_performance_optimizer();
        self::test_advanced_cache();
        self::test_advanced_features();
        self::test_asset_optimizer();
        self::test_ui_enhancements();
        self::test_integration();
        
        echo '</div>';
    }
    
    /**
     * Probar optimizador de rendimiento
     */
    private static function test_performance_optimizer() {
        echo '<h3>1. Optimizador de Rendimiento</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Performance_Optimizer')) {
            echo '<p>✅ Clase WVP_Performance_Optimizer cargada</p>';
            
            // Crear instancia de prueba
            $optimizer = new WVP_Performance_Optimizer();
            
            // Probar configuración
            $config = $optimizer->get_config();
            if (is_array($config) && !empty($config)) {
                echo '<p>✅ Configuración de optimización cargada</p>';
            } else {
                echo '<p>❌ Configuración de optimización no cargada</p>';
            }
            
            // Probar métricas de rendimiento
            $metrics = $optimizer->get_performance_metrics();
            if (is_array($metrics) && isset($metrics['memory_usage'])) {
                echo '<p>✅ Métricas de rendimiento disponibles</p>';
                echo '<p>Uso de memoria: ' . size_format($metrics['memory_usage']) . '</p>';
                echo '<p>Memoria pico: ' . size_format($metrics['peak_memory']) . '</p>';
                echo '<p>Tiempo de ejecución: ' . number_format($metrics['execution_time'], 4) . 's</p>';
                echo '<p>Consultas DB: ' . $metrics['database_queries'] . '</p>';
            } else {
                echo '<p>❌ Métricas de rendimiento no disponibles</p>';
            }
            
        } else {
            echo '<p>❌ Clase WVP_Performance_Optimizer no cargada</p>';
        }
    }
    
    /**
     * Probar caché avanzado
     */
    private static function test_advanced_cache() {
        echo '<h3>2. Caché Avanzado</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Advanced_Cache')) {
            echo '<p>✅ Clase WVP_Advanced_Cache cargada</p>';
            
            // Crear instancia de prueba
            $cache = new WVP_Advanced_Cache();
            
            // Probar configuración
            $config = $cache->get_config();
            if (is_array($config) && !empty($config)) {
                echo '<p>✅ Configuración de caché cargada</p>';
            } else {
                echo '<p>❌ Configuración de caché no cargada</p>';
            }
            
            // Probar operaciones de caché
            $test_key = 'test_key_' . time();
            $test_value = 'test_value_' . time();
            
            // Probar set
            $set_result = $cache->set($test_key, $test_value, 'default', 60);
            if ($set_result) {
                echo '<p>✅ Operación set() funcionando</p>';
            } else {
                echo '<p>❌ Operación set() fallando</p>';
            }
            
            // Probar get
            $get_value = $cache->get($test_key, 'default');
            if ($get_value === $test_value) {
                echo '<p>✅ Operación get() funcionando</p>';
            } else {
                echo '<p>❌ Operación get() fallando</p>';
            }
            
            // Probar delete
            $delete_result = $cache->delete($test_key, 'default');
            if ($delete_result) {
                echo '<p>✅ Operación delete() funcionando</p>';
            } else {
                echo '<p>❌ Operación delete() fallando</p>';
            }
            
            // Probar estadísticas
            $stats = $cache->get_stats();
            if (is_array($stats)) {
                echo '<p>✅ Estadísticas de caché disponibles</p>';
                echo '<p>Hits: ' . $stats['hits'] . '</p>';
                echo '<p>Misses: ' . $stats['misses'] . '</p>';
                echo '<p>Hit Rate: ' . number_format($stats['hit_rate'], 2) . '%</p>';
            } else {
                echo '<p>❌ Estadísticas de caché no disponibles</p>';
            }
            
        } else {
            echo '<p>❌ Clase WVP_Advanced_Cache no cargada</p>';
        }
    }
    
    /**
     * Probar funcionalidades avanzadas
     */
    private static function test_advanced_features() {
        echo '<h3>3. Funcionalidades Avanzadas</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Advanced_Features')) {
            echo '<p>✅ Clase WVP_Advanced_Features cargada</p>';
            
            // Crear instancia de prueba
            $features = new WVP_Advanced_Features();
            
            // Probar configuración
            $config = $features->get_config();
            if (is_array($config) && !empty($config)) {
                echo '<p>✅ Configuración de funcionalidades cargada</p>';
            } else {
                echo '<p>❌ Configuración de funcionalidades no cargada</p>';
            }
            
            // Probar shortcodes
            $shortcodes = array(
                'wvp_currency_converter',
                'wvp_product_info',
                'wvp_order_status',
                'wvp_igtf_calculator'
            );
            
            foreach ($shortcodes as $shortcode) {
                if (shortcode_exists($shortcode)) {
                    echo '<p>✅ Shortcode ' . $shortcode . ' registrado</p>';
                } else {
                    echo '<p>❌ Shortcode ' . $shortcode . ' no registrado</p>';
                }
            }
            
            // Probar widgets
            $widgets = array(
                'WVP_Currency_Converter_Widget',
                'WVP_Product_Info_Widget',
                'WVP_Order_Status_Widget'
            );
            
            foreach ($widgets as $widget) {
                if (class_exists($widget)) {
                    echo '<p>✅ Widget ' . $widget . ' disponible</p>';
                } else {
                    echo '<p>⚠️ Widget ' . $widget . ' no disponible</p>';
                }
            }
            
        } else {
            echo '<p>❌ Clase WVP_Advanced_Features no cargada</p>';
        }
    }
    
    /**
     * Probar optimizador de assets
     */
    private static function test_asset_optimizer() {
        echo '<h3>4. Optimizador de Assets</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Asset_Optimizer')) {
            echo '<p>✅ Clase WVP_Asset_Optimizer cargada</p>';
            
            // Crear instancia de prueba
            $optimizer = new WVP_Asset_Optimizer();
            
            // Probar configuración
            $config = $optimizer->get_config();
            if (is_array($config) && !empty($config)) {
                echo '<p>✅ Configuración de assets cargada</p>';
            } else {
                echo '<p>❌ Configuración de assets no cargada</p>';
            }
            
            // Probar estadísticas de assets
            $stats = $optimizer->get_asset_stats();
            if (is_array($stats)) {
                echo '<p>✅ Estadísticas de assets disponibles</p>';
                echo '<p>Archivos CSS: ' . $stats['css_files'] . '</p>';
                echo '<p>Archivos JS: ' . $stats['js_files'] . '</p>';
                echo '<p>CSS minificado: ' . $stats['minified_css'] . '</p>';
                echo '<p>JS minificado: ' . $stats['minified_js'] . '</p>';
                echo '<p>Tamaño total: ' . size_format($stats['total_size']) . '</p>';
                echo '<p>Tamaño minificado: ' . size_format($stats['minified_size']) . '</p>';
                echo '<p>Ahorros: ' . number_format($stats['savings'], 2) . '%</p>';
            } else {
                echo '<p>❌ Estadísticas de assets no disponibles</p>';
            }
            
        } else {
            echo '<p>❌ Clase WVP_Asset_Optimizer no cargada</p>';
        }
    }
    
    /**
     * Probar mejoras de UI/UX
     */
    private static function test_ui_enhancements() {
        echo '<h3>5. Mejoras de UI/UX</h3>';
        
        // Verificar archivos CSS
        $css_files = array(
            'wvp-ui-enhanced.css',
            'currency-switcher.css',
            'dual-breakdown.css',
            'hybrid-invoicing.css'
        );
        
        foreach ($css_files as $file) {
            $file_path = WVP_PLUGIN_PATH . 'assets/css/' . $file;
            if (file_exists($file_path)) {
                echo '<p>✅ Archivo CSS ' . $file . ' existe</p>';
            } else {
                echo '<p>❌ Archivo CSS ' . $file . ' no existe</p>';
            }
        }
        
        // Verificar archivos JavaScript
        $js_files = array(
            'wvp-ui-enhanced.js',
            'currency-switcher.js'
        );
        
        foreach ($js_files as $file) {
            $file_path = WVP_PLUGIN_PATH . 'assets/js/' . $file;
            if (file_exists($file_path)) {
                echo '<p>✅ Archivo JS ' . $file . ' existe</p>';
            } else {
                echo '<p>❌ Archivo JS ' . $file . ' no existe</p>';
            }
        }
        
        // Verificar archivos minificados
        $min_files = array(
            'wvp-ui-enhanced.min.css',
            'currency-switcher.min.css',
            'dual-breakdown.min.css',
            'hybrid-invoicing.min.css',
            'wvp-ui-enhanced.min.js',
            'currency-switcher.min.js'
        );
        
        foreach ($min_files as $file) {
            $file_path = WVP_PLUGIN_PATH . 'assets/' . (strpos($file, '.css') !== false ? 'css' : 'js') . '/' . $file;
            if (file_exists($file_path)) {
                echo '<p>✅ Archivo minificado ' . $file . ' existe</p>';
            } else {
                echo '<p>⚠️ Archivo minificado ' . $file . ' no existe (se generará automáticamente)</p>';
            }
        }
    }
    
    /**
     * Probar integración general
     */
    private static function test_integration() {
        echo '<h3>6. Integración General</h3>';
        
        // Verificar que el plugin principal está cargado
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            echo '<p>✅ Plugin principal cargado</p>';
            
            $plugin = WooCommerce_Venezuela_Pro::get_instance();
            
            // Verificar componentes de optimización
            $components = [
                'performance_optimizer' => 'WVP_Performance_Optimizer',
                'advanced_cache' => 'WVP_Advanced_Cache',
                'advanced_features' => 'WVP_Advanced_Features',
                'asset_optimizer' => 'WVP_Asset_Optimizer'
            ];
            
            foreach ($components as $property => $class) {
                if (class_exists($class)) {
                    echo '<p>✅ Componente ' . $property . ' disponible</p>';
                } else {
                    echo '<p>❌ Componente ' . $property . ' no disponible</p>';
                }
            }
            
        } else {
            echo '<p>❌ Plugin principal no cargado</p>';
        }
        
        // Verificar integración con WooCommerce
        if (class_exists('WooCommerce')) {
            echo '<p>✅ WooCommerce disponible</p>';
            
            // Verificar hooks de optimización
            $hooks = [
                'wp_enqueue_scripts',
                'wp_head',
                'wp_footer',
                'wp_loaded'
            ];
            
            foreach ($hooks as $hook) {
                if (has_action($hook) || has_filter($hook)) {
                    echo '<p>✅ Hook ' . $hook . ' registrado</p>';
                } else {
                    echo '<p>⚠️ Hook ' . $hook . ' no registrado</p>';
                }
            }
            
        } else {
            echo '<p>❌ WooCommerce no disponible</p>';
        }
        
        // Verificar integración con BCV
        if (class_exists('WVP_BCV_Integrator')) {
            echo '<p>✅ Integrador BCV disponible</p>';
            
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate && $rate > 0) {
                echo '<p>✅ Tasa BCV obtenida: ' . number_format($rate, 2) . ' Bs./USD</p>';
            } else {
                echo '<p>⚠️ Tasa BCV no disponible</p>';
            }
        } else {
            echo '<p>❌ Integrador BCV no disponible</p>';
        }
    }
    
    /**
     * Probar rendimiento en tiempo real
     */
    public static function test_real_time_performance() {
        echo '<h3>7. Pruebas de Rendimiento en Tiempo Real</h3>';
        
        // Probar carrito
        if (WC()->cart && !WC()->cart->is_empty()) {
            echo '<p>✅ Carrito disponible para pruebas</p>';
            
            // Probar cálculo de totales
            if (class_exists('WVP_Price_Calculator')) {
                $calculator = new WVP_Price_Calculator();
                $cart_totals = $calculator->calculate_cart_total_with_conversion();
                
                if (is_array($cart_totals) && isset($cart_totals['usd_total'])) {
                    echo '<p>✅ Cálculo de totales del carrito funcionando</p>';
                    echo '<p>Total USD: $' . number_format($cart_totals['usd_total'], 2) . '</p>';
                    echo '<p>Total VES: ' . $calculator->format_ves_price($cart_totals['ves_total']) . '</p>';
                } else {
                    echo '<p>❌ Cálculo de totales del carrito fallando</p>';
                }
            }
            
        } else {
            echo '<p>ℹ️ Carrito vacío - añadir productos para probar cálculos</p>';
        }
        
        // Probar productos
        $products = wc_get_products(array('limit' => 3));
        if (!empty($products)) {
            echo '<p>✅ Productos disponibles para pruebas</p>';
            
            foreach ($products as $product) {
                if (class_exists('WVP_Price_Calculator')) {
                    $calculator = new WVP_Price_Calculator();
                    $price_data = $calculator->calculate_product_price_with_conversion($product->get_id());
                    
                    if (is_array($price_data) && isset($price_data['usd_price'])) {
                        echo '<p>✅ Cálculo de precios para producto "' . $product->get_name() . '" funcionando</p>';
                    } else {
                        echo '<p>❌ Cálculo de precios para producto "' . $product->get_name() . '" fallando</p>';
                    }
                }
            }
        } else {
            echo '<p>⚠️ No hay productos disponibles para probar</p>';
        }
    }
    
    /**
     * Probar optimizaciones de assets
     */
    public static function test_asset_optimizations() {
        echo '<h3>8. Pruebas de Optimizaciones de Assets</h3>';
        
        if (class_exists('WVP_Asset_Optimizer')) {
            $optimizer = new WVP_Asset_Optimizer();
            
            // Probar minificación
            $config = $optimizer->get_config();
            if ($config['minify_css'] === 'yes') {
                echo '<p>✅ Minificación de CSS habilitada</p>';
            } else {
                echo '<p>⚠️ Minificación de CSS deshabilitada</p>';
            }
            
            if ($config['minify_js'] === 'yes') {
                echo '<p>✅ Minificación de JS habilitada</p>';
            } else {
                echo '<p>⚠️ Minificación de JS deshabilitada</p>';
            }
            
            // Probar combinación
            if ($config['combine_css'] === 'yes') {
                echo '<p>✅ Combinación de CSS habilitada</p>';
            } else {
                echo '<p>⚠️ Combinación de CSS deshabilitada</p>';
            }
            
            if ($config['combine_js'] === 'yes') {
                echo '<p>✅ Combinación de JS habilitada</p>';
            } else {
                echo '<p>⚠️ Combinación de JS deshabilitada</p>';
            }
            
            // Probar lazy loading
            if ($config['lazy_load_images'] === 'yes') {
                echo '<p>✅ Lazy loading de imágenes habilitado</p>';
            } else {
                echo '<p>⚠️ Lazy loading de imágenes deshabilitado</p>';
            }
            
            // Probar preload
            if ($config['preload_critical'] === 'yes') {
                echo '<p>✅ Preload de recursos críticos habilitado</p>';
            } else {
                echo '<p>⚠️ Preload de recursos críticos deshabilitado</p>';
            }
            
        } else {
            echo '<p>❌ Optimizador de assets no disponible</p>';
        }
    }
    
    /**
     * Probar caché en tiempo real
     */
    public static function test_real_time_cache() {
        echo '<h3>9. Pruebas de Caché en Tiempo Real</h3>';
        
        if (class_exists('WVP_Advanced_Cache')) {
            $cache = new WVP_Advanced_Cache();
            
            // Probar operaciones de caché
            $test_key = 'test_performance_' . time();
            $test_value = array(
                'timestamp' => current_time('mysql'),
                'data' => 'test_data_' . time()
            );
            
            // Probar set
            $set_result = $cache->set($test_key, $test_value, 'default', 60);
            if ($set_result) {
                echo '<p>✅ Operación set() funcionando</p>';
            } else {
                echo '<p>❌ Operación set() fallando</p>';
            }
            
            // Probar get
            $get_value = $cache->get($test_key, 'default');
            if ($get_value && is_array($get_value) && isset($get_value['data'])) {
                echo '<p>✅ Operación get() funcionando</p>';
                echo '<p>Datos recuperados: ' . $get_value['data'] . '</p>';
            } else {
                echo '<p>❌ Operación get() fallando</p>';
            }
            
            // Probar remember
            $remember_value = $cache->remember('test_remember_' . time(), function() {
                return 'remembered_value_' . time();
            }, 'default', 60);
            
            if ($remember_value) {
                echo '<p>✅ Operación remember() funcionando</p>';
            } else {
                echo '<p>❌ Operación remember() fallando</p>';
            }
            
            // Probar delete
            $delete_result = $cache->delete($test_key, 'default');
            if ($delete_result) {
                echo '<p>✅ Operación delete() funcionando</p>';
            } else {
                echo '<p>❌ Operación delete() fallando</p>';
            }
            
        } else {
            echo '<p>❌ Caché avanzado no disponible</p>';
        }
    }
}

// Función para ejecutar las pruebas desde el admin
function wvp_run_optimizations_tests() {
    if (current_user_can('manage_woocommerce')) {
        WVP_Optimizations_Test::run_all_tests();
        WVP_Optimizations_Test::test_real_time_performance();
        WVP_Optimizations_Test::test_asset_optimizations();
        WVP_Optimizations_Test::test_real_time_cache();
    }
}

// Hook para mostrar las pruebas en el admin
add_action('admin_notices', function() {
    if (isset($_GET['wvp_test_optimizations']) && current_user_can('manage_woocommerce')) {
        wvp_run_optimizations_tests();
    }
});
