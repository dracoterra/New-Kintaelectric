<?php
/**
 * Archivo de test DETALLADO para debugging del plugin WooCommerce Venezuela Pro 2025
 * Este archivo captura TODOS los errores posibles, incluyendo fatales
 */

// Activar TODOS los tipos de error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug-test.log');

// Variables globales para capturar errores
$GLOBALS['wvp_errors'] = [];
$GLOBALS['wvp_fatal_errors'] = [];
$GLOBALS['wvp_warnings'] = [];
$GLOBALS['wvp_notices'] = [];

// Función para capturar TODOS los tipos de errores
function wvp_detailed_error_handler($errno, $errstr, $errfile, $errline, $errcontext = null) {
    $error_info = [
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'context' => $errcontext,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    switch ($errno) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            $GLOBALS['wvp_fatal_errors'][] = $error_info;
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            $GLOBALS['wvp_warnings'][] = $error_info;
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $GLOBALS['wvp_notices'][] = $error_info;
            break;
        default:
            $GLOBALS['wvp_errors'][] = $error_info;
    }
    
    return true; // No ejecutar el handler de error interno de PHP
}

// Función para capturar errores fatales
function wvp_detailed_shutdown_handler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        $GLOBALS['wvp_fatal_errors'][] = [
            'type' => $error['type'],
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'timestamp' => date('Y-m-d H:i:s'),
            'shutdown' => true
        ];
    }
}

// Función para capturar excepciones
function wvp_exception_handler($exception) {
    $GLOBALS['wvp_fatal_errors'][] = [
        'type' => 'EXCEPTION',
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
        'timestamp' => date('Y-m-d H:i:s'),
        'exception' => true
    ];
}

// Registrar todos los handlers
set_error_handler('wvp_detailed_error_handler');
register_shutdown_function('wvp_detailed_shutdown_handler');
set_exception_handler('wvp_exception_handler');

// Función para mostrar errores de forma detallada
function wvp_show_errors($title, $errors) {
    if (empty($errors)) {
        echo "<p>✅ <strong>$title:</strong> No hay errores</p>";
        return;
    }
    
    echo "<h3 style='color: red;'>❌ $title (" . count($errors) . " errores)</h3>";
    foreach ($errors as $i => $error) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 5px; border-radius: 4px;'>";
        echo "<strong>Error #" . ($i + 1) . ":</strong><br>";
        echo "<strong>Tipo:</strong> " . $error['type'] . "<br>";
        echo "<strong>Mensaje:</strong> " . htmlspecialchars($error['message']) . "<br>";
        echo "<strong>Archivo:</strong> " . $error['file'] . "<br>";
        echo "<strong>Línea:</strong> " . $error['line'] . "<br>";
        echo "<strong>Timestamp:</strong> " . $error['timestamp'] . "<br>";
        if (isset($error['trace'])) {
            echo "<strong>Stack Trace:</strong><br><pre>" . htmlspecialchars($error['trace']) . "</pre>";
        }
        if (isset($error['shutdown'])) {
            echo "<strong>⚠️ ERROR FATAL EN SHUTDOWN</strong><br>";
        }
        if (isset($error['exception'])) {
            echo "<strong>⚠️ EXCEPCIÓN NO CAPTURADA</strong><br>";
        }
        echo "</div>";
    }
}

echo "<!DOCTYPE html><html><head><title>Test Detallado - WVP Plugin</title></head><body>";
echo "<h1>🔍 TEST DETALLADO - WooCommerce Venezuela Pro 2025</h1>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " segundos</p>";

// Cargar WordPress
echo "<h2>1. Cargando WordPress</h2>";
try {
    require_once('../../../wp-config.php');
    echo "✅ WordPress config cargado<br>";
} catch (Exception $e) {
    echo "❌ Error cargando WordPress: " . $e->getMessage() . "<br>";
}

// Verificar WordPress
if (function_exists('wp_get_current_user')) {
    echo "✅ WordPress funciones disponibles<br>";
} else {
    echo "❌ WordPress funciones no disponibles<br>";
}

// Verificar WooCommerce
if (class_exists('WooCommerce')) {
    echo "✅ WooCommerce disponible<br>";
} else {
    echo "❌ WooCommerce no disponible<br>";
}

// Cargar archivo principal del plugin
echo "<h2>2. Cargando archivo principal del plugin</h2>";
$plugin_file = __DIR__ . '/woocommerce-venezuela-pro-2025.php';
if (file_exists($plugin_file)) {
    echo "✅ Archivo principal encontrado: $plugin_file<br>";
    try {
        require_once $plugin_file;
        echo "✅ Archivo principal cargado<br>";
    } catch (Exception $e) {
        echo "❌ Error al cargar archivo principal: " . $e->getMessage() . "<br>";
    } catch (Error $e) {
        echo "❌ Error fatal al cargar archivo principal: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Archivo principal no encontrado<br>";
}

// Verificar clases definidas en el archivo principal
echo "<h2>3. Verificando clases del archivo principal</h2>";
$main_classes = ['WVP_Simple_Currency_Converter'];
foreach ($main_classes as $class_name) {
    if (class_exists($class_name)) {
        echo "✅ Clase $class_name existe<br>";
        try {
            if (method_exists($class_name, 'get_instance')) {
                $instance = $class_name::get_instance();
                echo "✅ Instancia de $class_name creada<br>";
            } else {
                echo "⚠️ Clase $class_name no tiene método get_instance<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error al instanciar $class_name: " . $e->getMessage() . "<br>";
        } catch (Error $e) {
            echo "❌ Error fatal al instanciar $class_name: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Clase $class_name no existe<br>";
    }
}

// Cargar clases externas del plugin
echo "<h2>4. Cargando clases externas del plugin</h2>";
$external_classes = [
    'WVP_Venezuelan_Taxes' => 'includes/class-wvp-venezuelan-taxes.php',
    'WVP_Admin_Dashboard' => 'includes/class-wvp-admin-dashboard.php',
    'WVP_Pago_Movil_Gateway' => 'includes/class-wvp-pago-movil-gateway.php',
    'WVP_Venezuelan_Shipping' => 'includes/class-wvp-venezuelan-shipping.php',
    'WVP_Product_Display' => 'includes/class-wvp-product-display.php',
    'WVP_SENIAT_Exporter' => 'includes/class-wvp-seniat-exporter.php',
];

foreach ($external_classes as $class_name => $file_path) {
    echo "<h3>Verificando: $class_name</h3>";
    
    $full_path = __DIR__ . '/' . $file_path;
    
    if (file_exists($full_path)) {
        echo "✅ Archivo encontrado: $file_path<br>";
        
        try {
            require_once $full_path;
            echo "✅ Archivo cargado<br>";
            
            if (class_exists($class_name)) {
                echo "✅ Clase $class_name existe<br>";
                
                try {
                    if (method_exists($class_name, 'get_instance')) {
                        $instance = $class_name::get_instance();
                        echo "✅ Instancia de $class_name creada<br>";
                    } else {
                        echo "⚠️ Clase $class_name no tiene método get_instance<br>";
                    }
                } catch (Exception $e) {
                    echo "❌ Error al instanciar $class_name: " . $e->getMessage() . "<br>";
                } catch (Error $e) {
                    echo "❌ Error fatal al instanciar $class_name: " . $e->getMessage() . "<br>";
                }
            } else {
                echo "❌ Clase $class_name no existe después de cargar el archivo<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error al cargar $file_path: " . $e->getMessage() . "<br>";
        } catch (Error $e) {
            echo "❌ Error fatal al cargar $file_path: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Archivo no encontrado: $file_path<br>";
    }
}

// Verificar funciones del plugin
echo "<h2>5. Verificando funciones del plugin</h2>";
$functions_to_check = ['wvp_init_plugin', 'wvp_add_pago_movil_gateway'];

foreach ($functions_to_check as $function_name) {
    if (function_exists($function_name)) {
        echo "✅ Función $function_name existe<br>";
    } else {
        echo "❌ Función $function_name no existe<br>";
    }
}

// Test de inicialización del plugin - PASO A PASO
echo "<h2>6. Test de inicialización del plugin - PASO A PASO</h2>";

// Primero, verificar si la función existe
if (function_exists('wvp_init_plugin')) {
    echo "✅ Función wvp_init_plugin encontrada<br>";
    
    // Capturar errores antes de ejecutar
    $errors_before = $GLOBALS['wvp_fatal_errors'];
    $warnings_before = $GLOBALS['wvp_warnings'];
    $notices_before = $GLOBALS['wvp_notices'];
    
    echo "Ejecutando inicialización...<br>";
    
    try {
        // Ejecutar la función
        wvp_init_plugin();
        echo "✅ Función wvp_init_plugin ejecutada<br>";
        
        // Verificar si se generaron errores durante la ejecución
        $errors_after = $GLOBALS['wvp_fatal_errors'];
        $warnings_after = $GLOBALS['wvp_warnings'];
        $notices_after = $GLOBALS['wvp_notices'];
        
        $new_errors = count($errors_after) - count($errors_before);
        $new_warnings = count($warnings_after) - count($warnings_before);
        $new_notices = count($notices_after) - count($notices_before);
        
        if ($new_errors > 0) {
            echo "❌ Se generaron $new_errors errores fatales durante la inicialización<br>";
        }
        if ($new_warnings > 0) {
            echo "⚠️ Se generaron $new_warnings warnings durante la inicialización<br>";
        }
        if ($new_notices > 0) {
            echo "ℹ️ Se generaron $new_notices notices durante la inicialización<br>";
        }
        
        if ($new_errors == 0 && $new_warnings == 0 && $new_notices == 0) {
            echo "✅ Inicialización completada sin errores<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error durante inicialización: " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    } catch (Error $e) {
        echo "❌ Error fatal durante inicialización: " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "❌ Función wvp_init_plugin no encontrada<br>";
}

// Test específico de cada componente
echo "<h2>6.1. Test específico de componentes</h2>";

// Test del currency converter
echo "<h3>Test Currency Converter</h3>";
try {
    if (class_exists('WVP_Simple_Currency_Converter')) {
        $converter = WVP_Simple_Currency_Converter::get_instance();
        echo "✅ Currency Converter instanciado<br>";
        
        // Test de métodos específicos
        if (method_exists($converter, 'enqueue_scripts')) {
            echo "✅ Método enqueue_scripts existe<br>";
        } else {
            echo "❌ Método enqueue_scripts NO existe<br>";
        }
        
        if (method_exists($converter, 'add_currency_switcher')) {
            echo "✅ Método add_currency_switcher existe<br>";
        } else {
            echo "❌ Método add_currency_switcher NO existe<br>";
        }
        
        if (method_exists($converter, 'ajax_convert_price')) {
            echo "✅ Método ajax_convert_price existe<br>";
        } else {
            echo "❌ Método ajax_convert_price NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Simple_Currency_Converter no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Currency Converter: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Currency Converter: " . $e->getMessage() . "<br>";
}

// Test del Admin Dashboard
echo "<h3>Test Admin Dashboard</h3>";
try {
    if (class_exists('WVP_Admin_Dashboard')) {
        $dashboard = WVP_Admin_Dashboard::get_instance();
        echo "✅ Admin Dashboard instanciado<br>";
        
        if (method_exists($dashboard, 'add_admin_menu')) {
            echo "✅ Método add_admin_menu existe<br>";
        } else {
            echo "❌ Método add_admin_menu NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Admin_Dashboard no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Admin Dashboard: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Admin Dashboard: " . $e->getMessage() . "<br>";
}

// Test del Pago Móvil Gateway
echo "<h3>Test Pago Móvil Gateway</h3>";
try {
    if (class_exists('WVP_Pago_Movil_Gateway')) {
        echo "✅ Clase WVP_Pago_Movil_Gateway existe<br>";
        
        // Los gateways se instancian de forma diferente
        $gateway = new WVP_Pago_Movil_Gateway();
        echo "✅ Pago Móvil Gateway instanciado<br>";
        
        if (method_exists($gateway, 'init_form_fields')) {
            echo "✅ Método init_form_fields existe<br>";
        } else {
            echo "❌ Método init_form_fields NO existe<br>";
        }
        
        if (method_exists($gateway, 'process_payment')) {
            echo "✅ Método process_payment existe<br>";
        } else {
            echo "❌ Método process_payment NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Pago_Movil_Gateway no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Pago Móvil Gateway: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Pago Móvil Gateway: " . $e->getMessage() . "<br>";
}

// Test de base de datos
echo "<h2>7. Test de Base de Datos</h2>";
global $wpdb;

echo "<h3>7.1. Conexión a Base de Datos</h3>";
if ($wpdb->db_connect()) {
    echo "✅ Conexión a base de datos exitosa<br>";
    echo "<strong>Host:</strong> " . DB_HOST . "<br>";
    echo "<strong>Database:</strong> " . DB_NAME . "<br>";
    echo "<strong>Charset:</strong> " . DB_CHARSET . "<br>";
} else {
    echo "❌ Error de conexión a base de datos<br>";
}

echo "<h3>7.2. Tablas de WordPress</h3>";
$wp_tables = [
    'posts', 'postmeta', 'users', 'usermeta', 'options', 
    'terms', 'termmeta', 'term_taxonomy', 'term_relationships',
    'comments', 'commentmeta'
];

foreach ($wp_tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "✅ Tabla $table_name existe ($count registros)<br>";
    } else {
        echo "❌ Tabla $table_name NO existe<br>";
    }
}

echo "<h3>7.3. Tablas de WooCommerce</h3>";
$wc_tables = [
    'woocommerce_sessions', 'woocommerce_api_keys', 'woocommerce_attribute_taxonomies',
    'woocommerce_downloadable_product_permissions', 'woocommerce_order_items',
    'woocommerce_order_itemmeta', 'woocommerce_tax_rates', 'woocommerce_tax_rate_locations',
    'woocommerce_shipping_zones', 'woocommerce_shipping_zone_locations',
    'woocommerce_shipping_zone_methods', 'woocommerce_payment_tokens',
    'woocommerce_payment_tokenmeta', 'woocommerce_log'
];

foreach ($wc_tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "✅ Tabla $table_name existe ($count registros)<br>";
    } else {
        echo "❌ Tabla $table_name NO existe<br>";
    }
}

echo "<h3>7.4. Opciones del Plugin</h3>";
$plugin_options = [
    'wvp_emergency_rate', 'wvp_iva_rate', 'wvp_igtf_rate',
    'wvp_bcv_rate', 'wvp_last_update', 'wvp_currency_settings'
];

foreach ($plugin_options as $option) {
    $value = get_option($option);
    if ($value !== false) {
        echo "✅ Opción $option: " . (is_array($value) ? print_r($value, true) : $value) . "<br>";
    } else {
        echo "⚠️ Opción $option no existe<br>";
    }
}

echo "<h3>7.5. Transients del Plugin</h3>";
$plugin_transients = [
    'wvp_bcv_rate_cache', 'wvp_currency_cache', 'wvp_dashboard_stats'
];

foreach ($plugin_transients as $transient) {
    $value = get_transient($transient);
    if ($value !== false) {
        echo "✅ Transient $transient: " . (is_array($value) ? print_r($value, true) : $value) . "<br>";
    } else {
        echo "⚠️ Transient $transient no existe<br>";
    }
}

echo "<h3>7.6. Meta Data de Productos</h3>";
$products = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'product' LIMIT 5");
if ($products) {
    echo "✅ Productos encontrados: " . count($products) . "<br>";
    foreach ($products as $product) {
        echo "&nbsp;&nbsp;- Producto ID {$product->ID}: {$product->post_title}<br>";
        
        // Verificar meta data específica
        $price_usd = get_post_meta($product->ID, '_price', true);
        $price_ves = get_post_meta($product->ID, '_price_ves', true);
        $currency = get_post_meta($product->ID, '_currency', true);
        
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Precio USD: " . ($price_usd ? $price_usd : 'No definido') . "<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Precio VES: " . ($price_ves ? $price_ves : 'No definido') . "<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Moneda: " . ($currency ? $currency : 'No definida') . "<br>";
    }
} else {
    echo "⚠️ No se encontraron productos<br>";
}

echo "<h3>7.7. Órdenes de WooCommerce</h3>";
$orders = $wpdb->get_results("SELECT ID, post_title, post_status FROM {$wpdb->posts} WHERE post_type = 'shop_order' LIMIT 5");
if ($orders) {
    echo "✅ Órdenes encontradas: " . count($orders) . "<br>";
    foreach ($orders as $order) {
        echo "&nbsp;&nbsp;- Orden ID {$order->ID}: {$order->post_title} ({$order->post_status})<br>";
        
        // Verificar meta data de orden
        $payment_method = get_post_meta($order->ID, '_payment_method', true);
        $order_total = get_post_meta($order->ID, '_order_total', true);
        $order_currency = get_post_meta($order->ID, '_order_currency', true);
        
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Método de pago: " . ($payment_method ? $payment_method : 'No definido') . "<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Total: " . ($order_total ? $order_total : 'No definido') . "<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Moneda: " . ($order_currency ? $order_currency : 'No definida') . "<br>";
    }
} else {
    echo "⚠️ No se encontraron órdenes<br>";
}

echo "<h3>7.8. Configuración de WooCommerce</h3>";
$wc_options = [
    'woocommerce_currency', 'woocommerce_price_thousand_sep', 'woocommerce_price_decimal_sep',
    'woocommerce_price_num_decimals', 'woocommerce_weight_unit', 'woocommerce_dimension_unit',
    'woocommerce_enable_myaccount_registration', 'woocommerce_enable_checkout_login_reminder'
];

foreach ($wc_options as $option) {
    $value = get_option($option);
    echo "✅ WC Opción $option: " . ($value ? $value : 'No definida') . "<br>";
}

// Información del sistema
echo "<h2>8. Información del sistema</h2>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>WordPress Version:</strong> " . (function_exists('get_bloginfo') ? get_bloginfo('version') : 'No disponible') . "<br>";
echo "<strong>WooCommerce Version:</strong> " . (class_exists('WooCommerce') ? WC()->version : 'No disponible') . "<br>";
echo "<strong>Plugin Directory:</strong> " . __DIR__ . "<br>";
echo "<strong>Memory Usage:</strong> " . memory_get_usage(true) / 1024 / 1024 . " MB<br>";
echo "<strong>Peak Memory:</strong> " . memory_get_peak_usage(true) / 1024 / 1024 . " MB<br>";
echo "<strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " segundos<br>";
echo "<strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "<br>";
echo "<strong>Post Max Size:</strong> " . ini_get('post_max_size') . "<br>";
echo "<strong>Max Input Vars:</strong> " . ini_get('max_input_vars') . "<br>";

// Test de hooks y filtros
echo "<h2>9. Test de Hooks y Filtros</h2>";

echo "<h3>9.1. Hooks del Plugin</h3>";
$plugin_hooks = [
    'wp_ajax_wvp_convert_price',
    'wp_ajax_nopriv_wvp_convert_price',
    'wp_ajax_wvp_get_dashboard_stats',
    'woocommerce_payment_gateways',
    'woocommerce_shipping_methods',
    'woocommerce_product_data_tabs',
    'woocommerce_single_product_summary',
    'woocommerce_checkout_process',
    'woocommerce_order_status_changed'
];

foreach ($plugin_hooks as $hook) {
    if (has_action($hook) || has_filter($hook)) {
        echo "✅ Hook $hook está registrado<br>";
    } else {
        echo "⚠️ Hook $hook NO está registrado<br>";
    }
}

echo "<h3>9.2. Filtros de WooCommerce</h3>";
$wc_filters = [
    'woocommerce_currency_symbol',
    'woocommerce_price_format',
    'woocommerce_get_price_html',
    'woocommerce_cart_item_price',
    'woocommerce_cart_item_subtotal',
    'woocommerce_order_formatted_line_subtotal'
];

foreach ($wc_filters as $filter) {
    if (has_filter($filter)) {
        echo "✅ Filtro $filter está registrado<br>";
    } else {
        echo "⚠️ Filtro $filter NO está registrado<br>";
    }
}

echo "<h3>9.3. Actions de WooCommerce</h3>";
$wc_actions = [
    'woocommerce_single_product_summary',
    'woocommerce_after_single_product_summary',
    'woocommerce_before_single_product_summary',
    'woocommerce_checkout_process',
    'woocommerce_order_status_changed'
];

foreach ($wc_actions as $action) {
    if (has_action($action)) {
        echo "✅ Action $action está registrado<br>";
    } else {
        echo "⚠️ Action $action NO está registrado<br>";
    }
}

// Test de funcionalidades específicas
echo "<h2>10. Test de Funcionalidades Específicas</h2>";

echo "<h3>10.1. Currency Converter</h3>";
try {
    if (class_exists('WVP_Simple_Currency_Converter')) {
        $converter = WVP_Simple_Currency_Converter::get_instance();
        
        // Test de conversión
        if (method_exists($converter, 'convert_price')) {
            echo "✅ Método convert_price existe<br>";
            
            // Test con valores específicos
            $test_price = 100;
            $converted = $converter->convert_price($test_price, 'USD', 'VES');
            echo "✅ Conversión test: $test_price USD = $converted VES<br>";
        } else {
            echo "❌ Método convert_price NO existe<br>";
        }
        
        // Test de rate BCV
        if (method_exists($converter, 'get_bcv_rate')) {
            $rate = $converter->get_bcv_rate();
            echo "✅ Rate BCV obtenido: $rate<br>";
        } else {
            echo "❌ Método get_bcv_rate NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Simple_Currency_Converter no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Currency Converter: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Currency Converter: " . $e->getMessage() . "<br>";
}

echo "<h3>10.2. Venezuelan Taxes</h3>";
try {
    if (class_exists('WVP_Venezuelan_Taxes')) {
        $taxes = WVP_Venezuelan_Taxes::get_instance();
        
        if (method_exists($taxes, 'calculate_iva')) {
            echo "✅ Método calculate_iva existe<br>";
            
            $test_amount = 100;
            $iva = $taxes->calculate_iva($test_amount);
            echo "✅ IVA calculado: $test_amount * 16% = $iva<br>";
        } else {
            echo "❌ Método calculate_iva NO existe<br>";
        }
        
        if (method_exists($taxes, 'calculate_igtf')) {
            echo "✅ Método calculate_igtf existe<br>";
            
            $test_amount = 100;
            $igtf = $taxes->calculate_igtf($test_amount);
            echo "✅ IGTF calculado: $test_amount * 3% = $igtf<br>";
        } else {
            echo "❌ Método calculate_igtf NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Venezuelan_Taxes no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Venezuelan Taxes: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Venezuelan Taxes: " . $e->getMessage() . "<br>";
}

echo "<h3>10.3. Pago Móvil Gateway</h3>";
try {
    if (class_exists('WVP_Pago_Movil_Gateway')) {
        $gateway = new WVP_Pago_Movil_Gateway();
        
        if (method_exists($gateway, 'is_available')) {
            $available = $gateway->is_available();
            echo "✅ Gateway disponible: " . ($available ? 'Sí' : 'No') . "<br>";
        } else {
            echo "❌ Método is_available NO existe<br>";
        }
        
        if (method_exists($gateway, 'get_title')) {
            $title = $gateway->get_title();
            echo "✅ Título del gateway: $title<br>";
        } else {
            echo "❌ Método get_title NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Pago_Movil_Gateway no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Pago Móvil Gateway: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Pago Móvil Gateway: " . $e->getMessage() . "<br>";
}

echo "<h3>10.4. Venezuelan Shipping</h3>";
try {
    if (class_exists('WVP_Venezuelan_Shipping')) {
        $shipping = WVP_Venezuelan_Shipping::get_instance();
        
        if (method_exists($shipping, 'calculate_shipping')) {
            echo "✅ Método calculate_shipping existe<br>";
        } else {
            echo "❌ Método calculate_shipping NO existe<br>";
        }
        
        if (method_exists($shipping, 'get_states')) {
            $states = $shipping->get_states();
            echo "✅ Estados venezolanos: " . count($states) . " estados<br>";
        } else {
            echo "❌ Método get_states NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Venezuelan_Shipping no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Venezuelan Shipping: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Venezuelan Shipping: " . $e->getMessage() . "<br>";
}

echo "<h3>10.5. Product Display</h3>";
try {
    if (class_exists('WVP_Product_Display')) {
        $display = WVP_Product_Display::get_instance();
        
        if (method_exists($display, 'add_currency_switcher')) {
            echo "✅ Método add_currency_switcher existe<br>";
        } else {
            echo "❌ Método add_currency_switcher NO existe<br>";
        }
        
        if (method_exists($display, 'show_venezuelan_info')) {
            echo "✅ Método show_venezuelan_info existe<br>";
        } else {
            echo "❌ Método show_venezuelan_info NO existe<br>";
        }
        
    } else {
        echo "❌ Clase WVP_Product_Display no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en Product Display: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Error fatal en Product Display: " . $e->getMessage() . "<br>";
}

// Test de archivos del plugin
echo "<h2>11. Test de Archivos del Plugin</h2>";

echo "<h3>11.1. Archivos CSS</h3>";
$css_files = [
    'admin/css/wvp-admin.css',
    'admin/css/wvp-seniat.css',
    'public/css/wvp-product-display.css',
    'public/css/wvp-simple-converter.css'
];

foreach ($css_files as $css_file) {
    $file_path = __DIR__ . '/' . $css_file;
    if (file_exists($file_path)) {
        $size = filesize($file_path);
        echo "✅ Archivo CSS $css_file existe (" . round($size/1024, 2) . " KB)<br>";
    } else {
        echo "❌ Archivo CSS $css_file NO existe<br>";
    }
}

echo "<h3>11.2. Archivos JavaScript</h3>";
$js_files = [
    'admin/js/wvp-admin.js',
    'admin/js/wvp-seniat.js',
    'public/js/wvp-product-display.js',
    'public/js/wvp-simple-converter.js'
];

foreach ($js_files as $js_file) {
    $file_path = __DIR__ . '/' . $js_file;
    if (file_exists($file_path)) {
        $size = filesize($file_path);
        echo "✅ Archivo JS $js_file existe (" . round($size/1024, 2) . " KB)<br>";
    } else {
        echo "❌ Archivo JS $js_file NO existe<br>";
    }
}

echo "<h3>11.3. Archivos PHP</h3>";
$php_files = [
    'woocommerce-venezuela-pro-2025.php',
    'includes/class-woocommerce-venezuela-pro-2025.php',
    'includes/class-wvp-simple-currency-converter.php',
    'includes/class-wvp-venezuelan-taxes.php',
    'includes/class-wvp-admin-dashboard.php',
    'includes/class-wvp-pago-movil-gateway.php',
    'includes/class-wvp-venezuelan-shipping.php',
    'includes/class-wvp-product-display.php',
    'includes/class-wvp-seniat-exporter.php',
    'uninstall.php'
];

foreach ($php_files as $php_file) {
    $file_path = __DIR__ . '/' . $php_file;
    if (file_exists($file_path)) {
        $size = filesize($file_path);
        echo "✅ Archivo PHP $php_file existe (" . round($size/1024, 2) . " KB)<br>";
    } else {
        echo "❌ Archivo PHP $php_file NO existe<br>";
    }
}

// Test de permisos de archivos
echo "<h3>11.4. Permisos de Archivos</h3>";
$important_files = [
    'woocommerce-venezuela-pro-2025.php',
    'includes/class-woocommerce-venezuela-pro-2025.php'
];

foreach ($important_files as $file) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        $perms = fileperms($file_path);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "✅ Permisos de $file: $perms_octal<br>";
    }
}

// Reporte final de errores capturados
echo "<h2>12. REPORTE FINAL DE ERRORES</h2>";

wvp_show_errors("ERRORES FATALES", $GLOBALS['wvp_fatal_errors']);
wvp_show_errors("WARNINGS", $GLOBALS['wvp_warnings']);
wvp_show_errors("NOTICES", $GLOBALS['wvp_notices']);
wvp_show_errors("OTROS ERRORES", $GLOBALS['wvp_errors']);

// Verificar si hay errores en el log de PHP
echo "<h2>13. Verificando log de errores PHP</h2>";
$error_log_file = ini_get('error_log');
if ($error_log_file && file_exists($error_log_file)) {
    $log_content = file_get_contents($error_log_file);
    $recent_errors = array_slice(explode("\n", $log_content), -10);
    echo "<h3>Últimos 10 errores del log PHP:</h3>";
    echo "<pre>" . htmlspecialchars(implode("\n", $recent_errors)) . "</pre>";
} else {
    echo "✅ No se encontró log de errores PHP<br>";
}

// Verificar debug.log de WordPress
echo "<h2>14. Verificando debug.log de WordPress</h2>";
$wp_debug_log = __DIR__ . '/../../../debug.log';
if (file_exists($wp_debug_log)) {
    $debug_content = file_get_contents($wp_debug_log);
    if (!empty(trim($debug_content))) {
        echo "<h3>Contenido del debug.log:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($debug_content) . "</pre>";
    } else {
        echo "✅ debug.log está vacío<br>";
    }
} else {
    echo "✅ debug.log no existe<br>";
}

echo "<hr>";
echo "<h2>🎯 RESUMEN FINAL</h2>";
$total_errors = count($GLOBALS['wvp_fatal_errors']) + count($GLOBALS['wvp_warnings']) + count($GLOBALS['wvp_notices']) + count($GLOBALS['wvp_errors']);

if ($total_errors === 0) {
    echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 15px; border-radius: 4px;'>";
    echo "<h3 style='color: #2e7d32;'>✅ PLUGIN COMPLETAMENTE FUNCIONAL</h3>";
    echo "<p>No se detectaron errores durante el test.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 15px; border-radius: 4px;'>";
    echo "<h3 style='color: #c62828;'>❌ SE DETECTARON $total_errors ERRORES</h3>";
    echo "<p>Revisa los errores detallados arriba para identificar los problemas.</p>";
    echo "</div>";
}

echo "<p><strong>Test completado:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><em>Si ves errores arriba, cópialos y envíalos para debugging.</em></p>";
echo "</body></html>";
?>
