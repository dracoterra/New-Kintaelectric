<?php
/**
 * Pruebas de lógica de negocio
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para probar la lógica de negocio
 */
class WVP_Business_Logic_Test {
    
    /**
     * Ejecutar todas las pruebas de lógica de negocio
     */
    public static function run_all_tests() {
        echo '<div class="wvp-business-logic-test">';
        echo '<h2>Pruebas de Lógica de Negocio</h2>';
        
        // Ejecutar pruebas individuales
        self::test_igtf_manager();
        self::test_business_validator();
        self::test_price_calculator();
        self::test_config_manager();
        self::test_integration();
        
        echo '</div>';
    }
    
    /**
     * Probar gestor de IGTF
     */
    private static function test_igtf_manager() {
        echo '<h3>1. Gestor de IGTF</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_IGTF_Manager')) {
            echo '<p>✅ Clase WVP_IGTF_Manager cargada</p>';
            
            // Crear instancia de prueba
            $igtf_manager = new WVP_IGTF_Manager();
            
            // Probar configuración
            $config = $igtf_manager->get_config();
            if (is_array($config) && !empty($config)) {
                echo '<p>✅ Configuración de IGTF cargada</p>';
            } else {
                echo '<p>❌ Configuración de IGTF no cargada</p>';
            }
            
            // Probar cálculo de IGTF
            $test_amount = 100.00;
            $igtf_amount = $igtf_manager->calculate_igtf($test_amount);
            
            if (is_numeric($igtf_amount) && $igtf_amount >= 0) {
                echo '<p>✅ Cálculo de IGTF funcionando: $' . number_format($igtf_amount, 2) . '</p>';
            } else {
                echo '<p>❌ Cálculo de IGTF fallando</p>';
            }
            
            // Probar validaciones
            $is_enabled = $igtf_manager->is_enabled();
            echo '<p>' . ($is_enabled ? '✅' : '⚠️') . ' IGTF ' . ($is_enabled ? 'habilitado' : 'deshabilitado') . '</p>';
            
        } else {
            echo '<p>❌ Clase WVP_IGTF_Manager no cargada</p>';
        }
    }
    
    /**
     * Probar validador de negocio
     */
    private static function test_business_validator() {
        echo '<h3>2. Validador de Negocio</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Business_Validator')) {
            echo '<p>✅ Clase WVP_Business_Validator cargada</p>';
            
            // Crear instancia de prueba
            $validator = new WVP_Business_Validator();
            
            // Probar configuración
            $config = $validator->get_config();
            if (is_array($config) && !empty($config)) {
                echo '<p>✅ Configuración de validaciones cargada</p>';
            } else {
                echo '<p>❌ Configuración de validaciones no cargada</p>';
            }
            
            // Probar validación de cédula/RIF
            $valid_cedula = $validator->validate_cedula_rif('V-12345678');
            $invalid_cedula = $validator->validate_cedula_rif('12345678');
            
            if ($valid_cedula && !$invalid_cedula) {
                echo '<p>✅ Validación de cédula/RIF funcionando</p>';
            } else {
                echo '<p>❌ Validación de cédula/RIF fallando</p>';
            }
            
            // Probar validación de teléfono
            $valid_phone = $validator->validate_venezuelan_phone('+584121234567');
            $invalid_phone = $validator->validate_venezuelan_phone('123456');
            
            if ($valid_phone && !$invalid_phone) {
                echo '<p>✅ Validación de teléfono funcionando</p>';
            } else {
                echo '<p>❌ Validación de teléfono fallando</p>';
            }
            
        } else {
            echo '<p>❌ Clase WVP_Business_Validator no cargada</p>';
        }
    }
    
    /**
     * Probar calculadora de precios
     */
    private static function test_price_calculator() {
        echo '<h3>3. Calculadora de Precios</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Price_Calculator')) {
            echo '<p>✅ Clase WVP_Price_Calculator cargada</p>';
            
            // Crear instancia de prueba
            $calculator = new WVP_Price_Calculator();
            
            // Probar cálculo de precio VES
            $usd_price = 100.00;
            $ves_price = $calculator->calculate_ves_price($usd_price);
            
            if (is_numeric($ves_price) && $ves_price > 0) {
                echo '<p>✅ Cálculo de precio VES funcionando: ' . $calculator->format_ves_price($ves_price) . '</p>';
            } else {
                echo '<p>❌ Cálculo de precio VES fallando</p>';
            }
            
            // Probar cálculo de IGTF
            $igtf_amount = $calculator->calculate_igtf($usd_price);
            
            if (is_numeric($igtf_amount) && $igtf_amount >= 0) {
                echo '<p>✅ Cálculo de IGTF funcionando: $' . number_format($igtf_amount, 2) . '</p>';
            } else {
                echo '<p>❌ Cálculo de IGTF fallando</p>';
            }
            
            // Probar formateo de precios
            $formatted_ves = $calculator->format_ves_price($ves_price);
            $formatted_usd = $calculator->format_usd_price($usd_price);
            
            if (!empty($formatted_ves) && !empty($formatted_usd)) {
                echo '<p>✅ Formateo de precios funcionando</p>';
            } else {
                echo '<p>❌ Formateo de precios fallando</p>';
            }
            
            // Probar estadísticas de caché
            $cache_stats = $calculator->get_cache_stats();
            if (is_array($cache_stats)) {
                echo '<p>✅ Estadísticas de caché: ' . $cache_stats['memory_cache'] . ' elementos en memoria</p>';
            } else {
                echo '<p>❌ Estadísticas de caché no disponibles</p>';
            }
            
        } else {
            echo '<p>❌ Clase WVP_Price_Calculator no cargada</p>';
        }
    }
    
    /**
     * Probar gestor de configuración
     */
    private static function test_config_manager() {
        echo '<h3>4. Gestor de Configuración</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Config_Manager')) {
            echo '<p>✅ Clase WVP_Config_Manager cargada</p>';
            
            // Crear instancia de prueba
            $config_manager = new WVP_Config_Manager();
            
            // Probar configuración
            $config = $config_manager->get_config();
            if (is_array($config) && !empty($config)) {
                echo '<p>✅ Configuración del plugin cargada</p>';
            } else {
                echo '<p>❌ Configuración del plugin no cargada</p>';
            }
            
            // Probar métodos de configuración
            $test_value = $config_manager->get('plugin_enabled', 'no');
            if (!empty($test_value)) {
                echo '<p>✅ Método get() funcionando</p>';
            } else {
                echo '<p>❌ Método get() fallando</p>';
            }
            
            // Probar configuración de IGTF
            $igtf_enabled = $config_manager->get('igtf_enabled', 'no');
            $igtf_rate = $config_manager->get('igtf_rate', 0);
            
            if ($igtf_enabled === 'yes' && $igtf_rate > 0) {
                echo '<p>✅ Configuración de IGTF válida</p>';
            } else {
                echo '<p>⚠️ Configuración de IGTF no válida</p>';
            }
            
            // Probar configuración de BCV
            $bcv_enabled = $config_manager->get('bcv_enabled', 'no');
            $bcv_fallback_rate = $config_manager->get('bcv_fallback_rate', 0);
            
            if ($bcv_enabled === 'yes' && $bcv_fallback_rate > 0) {
                echo '<p>✅ Configuración de BCV válida</p>';
            } else {
                echo '<p>⚠️ Configuración de BCV no válida</p>';
            }
            
        } else {
            echo '<p>❌ Clase WVP_Config_Manager no cargada</p>';
        }
    }
    
    /**
     * Probar integración general
     */
    private static function test_integration() {
        echo '<h3>5. Integración General</h3>';
        
        // Verificar que el plugin principal está cargado
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            echo '<p>✅ Plugin principal cargado</p>';
            
            $plugin = WooCommerce_Venezuela_Pro::get_instance();
            
            // Verificar componentes de lógica de negocio
            $components = [
                'igtf_manager' => 'WVP_IGTF_Manager',
                'business_validator' => 'WVP_Business_Validator',
                'price_calculator' => 'WVP_Price_Calculator',
                'config_manager' => 'WVP_Config_Manager'
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
            
            // Verificar hooks de WooCommerce
            $hooks = [
                'woocommerce_cart_calculate_fees',
                'woocommerce_checkout_process',
                'woocommerce_add_to_cart_validation',
                'woocommerce_checkout_order_processed',
                'woocommerce_payment_complete'
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
     * Probar funcionalidades en tiempo real
     */
    public static function test_real_time_functionality() {
        echo '<h3>6. Pruebas en Tiempo Real</h3>';
        
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
     * Probar rendimiento
     */
    public static function test_performance() {
        echo '<h3>7. Pruebas de Rendimiento</h3>';
        
        if (class_exists('WVP_Price_Calculator')) {
            $calculator = new WVP_Price_Calculator();
            
            // Probar cálculo en lote
            $start_time = microtime(true);
            $products = wc_get_products(array('limit' => 10));
            $product_ids = array_map(function($product) { return $product->get_id(); }, $products);
            
            $batch_results = $calculator->batch_calculate_prices($product_ids);
            $end_time = microtime(true);
            
            $execution_time = ($end_time - $start_time) * 1000; // en milisegundos
            
            if (is_array($batch_results) && count($batch_results) > 0) {
                echo '<p>✅ Cálculo en lote funcionando</p>';
                echo '<p>Tiempo de ejecución: ' . number_format($execution_time, 2) . ' ms</p>';
                echo '<p>Productos procesados: ' . count($batch_results) . '</p>';
            } else {
                echo '<p>❌ Cálculo en lote fallando</p>';
            }
            
            // Probar precalculo de productos populares
            $start_time = microtime(true);
            $popular_results = $calculator->precalculate_popular_products(5);
            $end_time = microtime(true);
            
            $execution_time = ($end_time - $start_time) * 1000;
            
            if (is_array($popular_results) && count($popular_results) > 0) {
                echo '<p>✅ Precalculo de productos populares funcionando</p>';
                echo '<p>Tiempo de ejecución: ' . number_format($execution_time, 2) . ' ms</p>';
                echo '<p>Productos precalculados: ' . count($popular_results) . '</p>';
            } else {
                echo '<p>❌ Precalculo de productos populares fallando</p>';
            }
            
        } else {
            echo '<p>❌ Calculadora de precios no disponible</p>';
        }
    }
}

// Función para ejecutar las pruebas desde el admin
function wvp_run_business_logic_tests() {
    if (current_user_can('manage_woocommerce')) {
        WVP_Business_Logic_Test::run_all_tests();
        WVP_Business_Logic_Test::test_real_time_functionality();
        WVP_Business_Logic_Test::test_performance();
    }
}

// Hook para mostrar las pruebas en el admin
add_action('admin_notices', function() {
    if (isset($_GET['wvp_test_business_logic']) && current_user_can('manage_woocommerce')) {
        wvp_run_business_logic_tests();
    }
});
