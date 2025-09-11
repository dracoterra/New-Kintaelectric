<?php
/**
 * Pruebas de funcionalidades no funcionales
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para probar las funcionalidades arregladas
 */
class WVP_Functionalities_Test {
    
    /**
     * Ejecutar todas las pruebas de funcionalidades
     */
    public static function run_all_tests() {
        echo '<div class="wvp-functionalities-test">';
        echo '<h2>Pruebas de Funcionalidades No Funcionales</h2>';
        
        // Ejecutar pruebas individuales
        self::test_currency_switcher();
        self::test_dual_breakdown();
        self::test_hybrid_invoicing();
        self::test_price_display();
        self::test_integration();
        
        echo '</div>';
    }
    
    /**
     * Probar switcher de moneda
     */
    private static function test_currency_switcher() {
        echo '<h3>1. Switcher de Moneda (USD ↔ VES)</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Currency_Switcher')) {
            echo '<p>✅ Clase WVP_Currency_Switcher cargada</p>';
            
            // Verificar que los archivos CSS y JS existen
            $css_path = WVP_PLUGIN_PATH . 'assets/css/currency-switcher.css';
            $js_path = WVP_PLUGIN_PATH . 'assets/js/currency-switcher.js';
            
            if (file_exists($css_path)) {
                echo '<p>✅ Archivo CSS del switcher existe</p>';
            } else {
                echo '<p>❌ Archivo CSS del switcher no existe</p>';
            }
            
            if (file_exists($js_path)) {
                echo '<p>✅ Archivo JavaScript del switcher existe</p>';
            } else {
                echo '<p>❌ Archivo JavaScript del switcher no existe</p>';
            }
            
            // Verificar hooks
            if (has_action('wp_enqueue_scripts', 'WVP_Currency_Switcher::enqueue_scripts')) {
                echo '<p>✅ Hook de scripts registrado</p>';
            } else {
                echo '<p>⚠️ Hook de scripts no registrado</p>';
            }
            
        } else {
            echo '<p>❌ Clase WVP_Currency_Switcher no cargada</p>';
        }
    }
    
    /**
     * Probar desglose dual
     */
    private static function test_dual_breakdown() {
        echo '<h3>2. Desglose Dual (USD y VES)</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Dual_Breakdown')) {
            echo '<p>✅ Clase WVP_Dual_Breakdown cargada</p>';
            
            // Verificar que el archivo CSS existe
            $css_path = WVP_PLUGIN_PATH . 'assets/css/dual-breakdown.css';
            
            if (file_exists($css_path)) {
                echo '<p>✅ Archivo CSS del desglose dual existe</p>';
            } else {
                echo '<p>❌ Archivo CSS del desglose dual no existe</p>';
            }
            
            // Verificar hooks del carrito
            $cart_hooks = [
                'woocommerce_cart_item_price',
                'woocommerce_cart_item_subtotal',
                'woocommerce_cart_subtotal',
                'woocommerce_cart_shipping_total',
                'woocommerce_cart_tax_totals',
                'woocommerce_cart_totals_order_total_html'
            ];
            
            foreach ($cart_hooks as $hook) {
                if (has_filter($hook)) {
                    echo '<p>✅ Hook ' . $hook . ' registrado</p>';
                } else {
                    echo '<p>⚠️ Hook ' . $hook . ' no registrado</p>';
                }
            }
            
        } else {
            echo '<p>❌ Clase WVP_Dual_Breakdown no cargada</p>';
        }
    }
    
    /**
     * Probar facturación híbrida
     */
    private static function test_hybrid_invoicing() {
        echo '<h3>3. Facturación Híbrida</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Hybrid_Invoicing')) {
            echo '<p>✅ Clase WVP_Hybrid_Invoicing cargada</p>';
            
            // Verificar que el archivo CSS existe
            $css_path = WVP_PLUGIN_PATH . 'assets/css/hybrid-invoicing.css';
            
            if (file_exists($css_path)) {
                echo '<p>✅ Archivo CSS de facturación híbrida existe</p>';
            } else {
                echo '<p>❌ Archivo CSS de facturación híbrida no existe</p>';
            }
            
            // Verificar hooks
            $hooks = [
                'woocommerce_email_order_details',
                'woocommerce_email_order_meta',
                'woocommerce_invoice_created',
                'woocommerce_order_details_after_order_table',
                'woocommerce_admin_order_totals_after_total'
            ];
            
            foreach ($hooks as $hook) {
                if (has_action($hook) || has_filter($hook)) {
                    echo '<p>✅ Hook ' . $hook . ' registrado</p>';
                } else {
                    echo '<p>⚠️ Hook ' . $hook . ' no registrado</p>';
                }
            }
            
        } else {
            echo '<p>❌ Clase WVP_Hybrid_Invoicing no cargada</p>';
        }
    }
    
    /**
     * Probar display de precios
     */
    private static function test_price_display() {
        echo '<h3>4. Display de Precios</h3>';
        
        // Verificar que la clase existe
        if (class_exists('WVP_Price_Display')) {
            echo '<p>✅ Clase WVP_Price_Display cargada</p>';
            
            // Verificar hooks
            $hooks = [
                'woocommerce_get_price_html',
                'wp_enqueue_scripts',
                'woocommerce_single_product_summary',
                'woocommerce_after_shop_loop_item_title'
            ];
            
            foreach ($hooks as $hook) {
                if (has_action($hook) || has_filter($hook)) {
                    echo '<p>✅ Hook ' . $hook . ' registrado</p>';
                } else {
                    echo '<p>⚠️ Hook ' . $hook . ' no registrado</p>';
                }
            }
            
        } else {
            echo '<p>❌ Clase WVP_Price_Display no cargada</p>';
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
            
            // Verificar componentes
            $components = [
                'price_display' => 'WVP_Price_Display',
                'checkout' => 'WVP_Checkout',
                'dual_breakdown' => 'WVP_Dual_Breakdown',
                'hybrid_invoicing' => 'WVP_Hybrid_Invoicing'
            ];
            
            foreach ($components as $property => $class) {
                if (isset($plugin->$property) && is_object($plugin->$property)) {
                    echo '<p>✅ Componente ' . $property . ' inicializado</p>';
                } else {
                    echo '<p>⚠️ Componente ' . $property . ' no inicializado</p>';
                }
            }
            
        } else {
            echo '<p>❌ Plugin principal no cargado</p>';
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
        
        // Crear producto de prueba
        $product = wc_get_product(1); // Usar primer producto disponible
        
        if ($product) {
            echo '<p>✅ Producto de prueba disponible</p>';
            
            // Probar display de precios
            $price_html = $product->get_price_html();
            if (strpos($price_html, 'wvp-currency-switcher') !== false) {
                echo '<p>✅ Switcher de moneda integrado en precios</p>';
            } else {
                echo '<p>⚠️ Switcher de moneda no integrado en precios</p>';
            }
            
        } else {
            echo '<p>⚠️ No hay productos disponibles para probar</p>';
        }
        
        // Probar carrito
        if (WC()->cart && !WC()->cart->is_empty()) {
            echo '<p>✅ Carrito disponible para pruebas</p>';
        } else {
            echo '<p>ℹ️ Carrito vacío - añadir productos para probar desglose dual</p>';
        }
    }
}

// Función para ejecutar las pruebas desde el admin
function wvp_run_functionality_tests() {
    if (current_user_can('manage_woocommerce')) {
        WVP_Functionalities_Test::run_all_tests();
        WVP_Functionalities_Test::test_real_time_functionality();
    }
}

// Hook para mostrar las pruebas en el admin
add_action('admin_notices', function() {
    if (isset($_GET['wvp_test_functionalities']) && current_user_can('manage_woocommerce')) {
        wvp_run_functionality_tests();
    }
});
