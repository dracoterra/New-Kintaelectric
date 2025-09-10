<?php
/**
 * Herramientas de desarrollo para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Dev_Tools {
    
    /**
     * Inicializar herramientas de desarrollo
     */
    public static function init() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('admin_menu', array(__CLASS__, 'add_dev_menu'));
            add_action('wp_ajax_wvp_dev_test', array(__CLASS__, 'handle_dev_test'));
            add_action('wp_ajax_wvp_dev_logs', array(__CLASS__, 'handle_dev_logs'));
            add_action('wp_ajax_wvp_dev_clear_logs', array(__CLASS__, 'handle_clear_logs'));
        }
    }
    
    /**
     * Añadir menú de desarrollo
     */
    public static function add_dev_menu() {
        add_submenu_page(
            'woocommerce',
            'WVP Dev Tools',
            'WVP Dev Tools',
            'manage_options',
            'wvp-dev-tools',
            array(__CLASS__, 'dev_tools_page')
        );
    }
    
    /**
     * Página de herramientas de desarrollo
     */
    public static function dev_tools_page() {
        ?>
        <div class="wrap">
            <h1>🛠️ WVP Development Tools</h1>
            
            <div class="wvp-dev-tools">
                <div class="dev-section">
                    <h2>🧪 Pruebas del Sistema</h2>
                    <button id="run-hpos-test" class="button button-primary">Probar Compatibilidad HPOS</button>
                    <button id="run-plugin-test" class="button button-secondary">Probar Funcionalidades</button>
                    <button id="run-integration-test" class="button button-secondary">Probar Integraciones</button>
                </div>
                
                <div class="dev-section">
                    <h2>📊 Logs del Sistema</h2>
                    <button id="view-logs" class="button button-secondary">Ver Logs</button>
                    <button id="clear-logs" class="button button-secondary">Limpiar Logs</button>
                    <div id="logs-container" style="display:none; margin-top: 20px;">
                        <pre id="logs-content"></pre>
                    </div>
                </div>
                
                <div class="dev-section">
                    <h2>🔧 Herramientas de Debug</h2>
                    <button id="debug-order-meta" class="button button-secondary">Debug Order Meta</button>
                    <button id="debug-bcv-integration" class="button button-secondary">Debug BCV Integration</button>
                    <button id="debug-price-display" class="button button-secondary">Debug Price Display</button>
                </div>
                
                <div class="dev-section">
                    <h2>📈 Estadísticas del Sistema</h2>
                    <div id="system-stats">
                        <?php self::display_system_stats(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .wvp-dev-tools .dev-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .wvp-dev-tools .dev-section h2 {
            margin-top: 0;
            color: #23282d;
        }
        .wvp-dev-tools button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        #logs-container {
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            max-height: 400px;
            overflow-y: auto;
        }
        #logs-content {
            margin: 0;
            font-family: monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        .system-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Pruebas del sistema
            $('#run-hpos-test').click(function() {
                runTest('hpos');
            });
            
            $('#run-plugin-test').click(function() {
                runTest('plugin');
            });
            
            $('#run-integration-test').click(function() {
                runTest('integration');
            });
            
            // Logs
            $('#view-logs').click(function() {
                viewLogs();
            });
            
            $('#clear-logs').click(function() {
                clearLogs();
            });
            
            // Debug
            $('#debug-order-meta').click(function() {
                runDebug('order_meta');
            });
            
            $('#debug-bcv-integration').click(function() {
                runDebug('bcv_integration');
            });
            
            $('#debug-price-display').click(function() {
                runDebug('price_display');
            });
            
            function runTest(testType) {
                $.post(ajaxurl, {
                    action: 'wvp_dev_test',
                    test_type: testType,
                    nonce: '<?php echo wp_create_nonce('wvp_dev_test'); ?>'
                }, function(response) {
                    alert('Prueba completada: ' + response.message);
                });
            }
            
            function viewLogs() {
                $.post(ajaxurl, {
                    action: 'wvp_dev_logs',
                    nonce: '<?php echo wp_create_nonce('wvp_dev_logs'); ?>'
                }, function(response) {
                    $('#logs-content').text(response.logs);
                    $('#logs-container').show();
                });
            }
            
            function clearLogs() {
                $.post(ajaxurl, {
                    action: 'wvp_dev_clear_logs',
                    nonce: '<?php echo wp_create_nonce('wvp_dev_clear_logs'); ?>'
                }, function(response) {
                    alert('Logs limpiados');
                    $('#logs-container').hide();
                });
            }
            
            function runDebug(debugType) {
                $.post(ajaxurl, {
                    action: 'wvp_dev_test',
                    test_type: 'debug',
                    debug_type: debugType,
                    nonce: '<?php echo wp_create_nonce('wvp_dev_test'); ?>'
                }, function(response) {
                    alert('Debug completado: ' + response.message);
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Manejar pruebas de desarrollo
     */
    public static function handle_dev_test() {
        check_ajax_referer('wvp_dev_test', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $test_type = sanitize_text_field($_POST['test_type']);
        
        switch ($test_type) {
            case 'hpos':
                $result = self::test_hpos_compatibility();
                break;
            case 'plugin':
                $result = self::test_plugin_functionality();
                break;
            case 'integration':
                $result = self::test_integrations();
                break;
            case 'debug':
                $debug_type = sanitize_text_field($_POST['debug_type']);
                $result = self::run_debug($debug_type);
                break;
            default:
                $result = array('success' => false, 'message' => 'Tipo de prueba no válido');
        }
        
        wp_send_json($result);
    }
    
    /**
     * Manejar visualización de logs
     */
    public static function handle_dev_logs() {
        check_ajax_referer('wvp_dev_logs', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $log_file = WP_CONTENT_DIR . '/debug.log';
        $logs = file_exists($log_file) ? file_get_contents($log_file) : 'No hay logs disponibles';
        
        wp_send_json(array('logs' => $logs));
    }
    
    /**
     * Manejar limpieza de logs
     */
    public static function handle_clear_logs() {
        check_ajax_referer('wvp_dev_clear_logs', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $log_file = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
        }
        
        wp_send_json(array('success' => true, 'message' => 'Logs limpiados'));
    }
    
    /**
     * Probar compatibilidad HPOS
     */
    private static function test_hpos_compatibility() {
        $tests = array();
        
        // Test 1: HPOS habilitado
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            $hpos_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
            $tests[] = 'HPOS habilitado: ' . ($hpos_enabled ? 'Sí' : 'No');
        } else {
            $tests[] = 'OrderUtil no disponible';
        }
        
        // Test 2: Clases de compatibilidad
        $tests[] = 'WVP_HPOS_Compatibility: ' . (class_exists('WVP_HPOS_Compatibility') ? 'Disponible' : 'No disponible');
        $tests[] = 'WVP_HPOS_Migration: ' . (class_exists('WVP_HPOS_Migration') ? 'Disponible' : 'No disponible');
        
        // Test 3: Métodos de metadatos
        try {
            $order = wc_create_order();
            if ($order) {
                $order->update_meta_data('_test_meta', 'test_value');
                $order->save();
                $value = $order->get_meta('_test_meta');
                $order->delete(true);
                $tests[] = 'Métodos de metadatos: ' . ($value === 'test_value' ? 'Funcionando' : 'Error');
            }
        } catch (Exception $e) {
            $tests[] = 'Métodos de metadatos: Error - ' . $e->getMessage();
        }
        
        return array(
            'success' => true,
            'message' => 'Pruebas HPOS completadas',
            'details' => implode("\n", $tests)
        );
    }
    
    /**
     * Probar funcionalidades del plugin
     */
    private static function test_plugin_functionality() {
        $tests = array();
        
        // Test 1: Clases principales
        $classes = array(
            'WooCommerce_Venezuela_Pro' => 'Clase principal',
            'WVP_Checkout' => 'Checkout',
            'WVP_Price_Display' => 'Mostrar precios',
            'WVP_BCV_Integrator' => 'Integrador BCV',
            'WVP_Invoice_Generator' => 'Generador de facturas'
        );
        
        foreach ($classes as $class => $description) {
            $tests[] = "$description: " . (class_exists($class) ? 'Disponible' : 'No disponible');
        }
        
        // Test 2: Hooks registrados
        $hooks = array(
            'woocommerce_checkout_process' => 'Proceso de checkout',
            'woocommerce_checkout_update_order_meta' => 'Actualización de metadatos',
            'woocommerce_order_item_meta' => 'Metadatos de items'
        );
        
        foreach ($hooks as $hook => $description) {
            $tests[] = "$description: " . (has_action($hook) ? 'Registrado' : 'No registrado');
        }
        
        return array(
            'success' => true,
            'message' => 'Pruebas del plugin completadas',
            'details' => implode("\n", $tests)
        );
    }
    
    /**
     * Probar integraciones
     */
    private static function test_integrations() {
        $tests = array();
        
        // Test 1: WooCommerce
        $tests[] = 'WooCommerce: ' . (class_exists('WooCommerce') ? 'Disponible' : 'No disponible');
        
        // Test 2: BCV Dólar Tracker
        $tests[] = 'BCV Dólar Tracker: ' . (class_exists('BCV_Dolar_Tracker') ? 'Disponible' : 'No disponible');
        
        // Test 3: Elementor
        $tests[] = 'Elementor: ' . (did_action('elementor/loaded') ? 'Disponible' : 'No disponible');
        
        // Test 4: KintaElectronic Elementor
        $tests[] = 'KintaElectronic Elementor: ' . (class_exists('KintaElectronicElementor') ? 'Disponible' : 'No disponible');
        
        return array(
            'success' => true,
            'message' => 'Pruebas de integración completadas',
            'details' => implode("\n", $tests)
        );
    }
    
    /**
     * Ejecutar debug específico
     */
    private static function run_debug($debug_type) {
        switch ($debug_type) {
            case 'order_meta':
                return self::debug_order_meta();
            case 'bcv_integration':
                return self::debug_bcv_integration();
            case 'price_display':
                return self::debug_price_display();
            default:
                return array('success' => false, 'message' => 'Tipo de debug no válido');
        }
    }
    
    /**
     * Debug de metadatos de pedidos
     */
    private static function debug_order_meta() {
        global $wpdb;
        
        $meta_keys = array(
            '_billing_cedula_rif',
            '_bcv_rate_at_purchase',
            '_payment_reference',
            '_igtf_amount',
            '_payment_type'
        );
        
        $results = array();
        foreach ($meta_keys as $key) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                $key
            ));
            $results[] = "$key: $count registros";
        }
        
        return array(
            'success' => true,
            'message' => 'Debug de metadatos completado',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Debug de integración BCV
     */
    private static function debug_bcv_integration() {
        $results = array();
        
        // Verificar tabla BCV
        global $wpdb;
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        $results[] = "Tabla BCV: " . ($table_exists ? 'Existe' : 'No existe');
        
        if ($table_exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $results[] = "Registros BCV: $count";
            
            $latest = $wpdb->get_row("SELECT * FROM $table_name ORDER BY datatime DESC LIMIT 1");
            if ($latest) {
                $results[] = "Último precio: {$latest->precio} Bs ({$latest->datatime})";
            }
        }
        
        return array(
            'success' => true,
            'message' => 'Debug de integración BCV completado',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Debug de visualización de precios
     */
    private static function debug_price_display() {
        $results = array();
        
        // Verificar configuración de moneda
        $currency = get_woocommerce_currency();
        $results[] = "Moneda actual: $currency";
        
        // Verificar configuración de país
        $country = WC()->countries->get_base_country();
        $results[] = "País base: $country";
        
        // Verificar configuración de precios
        $price_display_suffix = get_option('woocommerce_price_display_suffix');
        $results[] = "Sufijo de precios: " . ($price_display_suffix ?: 'No configurado');
        
        return array(
            'success' => true,
            'message' => 'Debug de visualización de precios completado',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Mostrar estadísticas del sistema
     */
    private static function display_system_stats() {
        global $wpdb;
        
        // Estadísticas de pedidos
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'");
        $recent_orders = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order' AND post_date >= %s",
            date('Y-m-d', strtotime('-7 days'))
        ));
        
        // Estadísticas de productos
        $total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product'");
        
        // Estadísticas de BCV
        $bcv_table = $wpdb->prefix . 'bcv_precio_dolar';
        $bcv_records = $wpdb->get_var("SELECT COUNT(*) FROM $bcv_table");
        
        echo '<div class="system-stats">';
        echo '<div class="stat-item"><div class="stat-value">' . $total_orders . '</div><div class="stat-label">Total Pedidos</div></div>';
        echo '<div class="stat-item"><div class="stat-value">' . $recent_orders . '</div><div class="stat-label">Pedidos (7 días)</div></div>';
        echo '<div class="stat-item"><div class="stat-value">' . $total_products . '</div><div class="stat-label">Productos</div></div>';
        echo '<div class="stat-item"><div class="stat-value">' . $bcv_records . '</div><div class="stat-label">Registros BCV</div></div>';
        echo '</div>';
    }
}

// Inicializar herramientas de desarrollo
WVP_Dev_Tools::init();
