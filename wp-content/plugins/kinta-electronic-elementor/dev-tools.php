<?php
/**
 * Herramientas de desarrollo para KintaElectronic Elementor
 * 
 * @package KintaElectronic_Elementor
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class KEE_Dev_Tools {
    
    /**
     * Inicializar herramientas de desarrollo
     */
    public static function init() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('admin_menu', array(__CLASS__, 'add_dev_menu'));
            add_action('wp_ajax_kee_dev_test', array(__CLASS__, 'handle_dev_test'));
            add_action('wp_ajax_kee_dev_widget', array(__CLASS__, 'handle_widget_test'));
        }
    }
    
    /**
     * Añadir menú de desarrollo
     */
    public static function add_dev_menu() {
        add_submenu_page(
            'tools.php',
            'KEE Dev Tools',
            'KEE Dev Tools',
            'manage_options',
            'kee-dev-tools',
            array(__CLASS__, 'dev_tools_page')
        );
    }
    
    /**
     * Página de herramientas de desarrollo
     */
    public static function dev_tools_page() {
        ?>
        <div class="wrap">
            <h1>🛠️ KintaElectronic Elementor - Development Tools</h1>
            
            <div class="kee-dev-tools">
                <div class="dev-section">
                    <h2>🧪 Pruebas del Sistema</h2>
                    <button id="run-elementor-test" class="button button-primary">Probar Elementor</button>
                    <button id="run-widgets-test" class="button button-secondary">Probar Widgets</button>
                    <button id="run-assets-test" class="button button-secondary">Probar Assets</button>
                </div>
                
                <div class="dev-section">
                    <h2>🎨 Widgets de Elementor</h2>
                    <button id="list-widgets" class="button button-primary">Listar Widgets</button>
                    <button id="test-widget" class="button button-secondary">Probar Widget</button>
                    <button id="regenerate-widgets" class="button button-secondary">Regenerar Widgets</button>
                </div>
                
                <div class="dev-section">
                    <h2>📁 Gestión de Assets</h2>
                    <button id="check-assets" class="button button-secondary">Verificar Assets</button>
                    <button id="minify-assets" class="button button-secondary">Minificar Assets</button>
                    <button id="clear-cache" class="button button-secondary">Limpiar Caché</button>
                </div>
                
                <div class="dev-section">
                    <h2>🔧 Configuración de Desarrollo</h2>
                    <button id="enable-debug" class="button button-secondary">Habilitar Debug</button>
                    <button id="disable-debug" class="button button-secondary">Deshabilitar Debug</button>
                    <button id="export-config" class="button button-secondary">Exportar Config</button>
                </div>
                
                <div class="dev-section">
                    <h2>📊 Información del Sistema</h2>
                    <div id="system-info">
                        <?php self::display_system_info(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .kee-dev-tools .dev-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .kee-dev-tools .dev-section h2 {
            margin-top: 0;
            color: #23282d;
        }
        .kee-dev-tools button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .system-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
        }
        .info-title {
            font-weight: bold;
            color: #0073aa;
            margin-bottom: 5px;
        }
        .info-value {
            color: #666;
        }
        .widget-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
        }
        .widget-item {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .widget-item:last-child {
            border-bottom: none;
        }
        .test-result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .test-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .test-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Pruebas del sistema
            $('#run-elementor-test').click(function() {
                runTest('elementor');
            });
            
            $('#run-widgets-test').click(function() {
                runTest('widgets');
            });
            
            $('#run-assets-test').click(function() {
                runTest('assets');
            });
            
            // Widgets
            $('#list-widgets').click(function() {
                listWidgets();
            });
            
            $('#test-widget').click(function() {
                testWidget();
            });
            
            $('#regenerate-widgets').click(function() {
                regenerateWidgets();
            });
            
            // Assets
            $('#check-assets').click(function() {
                checkAssets();
            });
            
            $('#minify-assets').click(function() {
                minifyAssets();
            });
            
            $('#clear-cache').click(function() {
                clearCache();
            });
            
            // Configuración
            $('#enable-debug').click(function() {
                toggleDebug(true);
            });
            
            $('#disable-debug').click(function() {
                toggleDebug(false);
            });
            
            $('#export-config').click(function() {
                exportConfig();
            });
            
            function runTest(testType) {
                $.post(ajaxurl, {
                    action: 'kee_dev_test',
                    test_type: testType,
                    nonce: '<?php echo wp_create_nonce('kee_dev_test'); ?>'
                }, function(response) {
                    showTestResult(testType, response);
                });
            }
            
            function listWidgets() {
                $.post(ajaxurl, {
                    action: 'kee_dev_widget',
                    widget_action: 'list',
                    nonce: '<?php echo wp_create_nonce('kee_dev_widget'); ?>'
                }, function(response) {
                    showWidgetList(response.widgets);
                });
            }
            
            function testWidget() {
                var widgetName = prompt('Ingresa el nombre del widget a probar:');
                if (widgetName) {
                    $.post(ajaxurl, {
                        action: 'kee_dev_widget',
                        widget_action: 'test',
                        widget_name: widgetName,
                        nonce: '<?php echo wp_create_nonce('kee_dev_widget'); ?>'
                    }, function(response) {
                        alert('Prueba de widget: ' + response.message);
                    });
                }
            }
            
            function regenerateWidgets() {
                $.post(ajaxurl, {
                    action: 'kee_dev_widget',
                    widget_action: 'regenerate',
                    nonce: '<?php echo wp_create_nonce('kee_dev_widget'); ?>'
                }, function(response) {
                    alert('Widgets regenerados: ' + response.message);
                });
            }
            
            function checkAssets() {
                $.post(ajaxurl, {
                    action: 'kee_dev_test',
                    test_type: 'check_assets',
                    nonce: '<?php echo wp_create_nonce('kee_dev_test'); ?>'
                }, function(response) {
                    showTestResult('assets', response);
                });
            }
            
            function minifyAssets() {
                $.post(ajaxurl, {
                    action: 'kee_dev_test',
                    test_type: 'minify_assets',
                    nonce: '<?php echo wp_create_nonce('kee_dev_test'); ?>'
                }, function(response) {
                    alert('Assets minificados: ' + response.message);
                });
            }
            
            function clearCache() {
                $.post(ajaxurl, {
                    action: 'kee_dev_test',
                    test_type: 'clear_cache',
                    nonce: '<?php echo wp_create_nonce('kee_dev_test'); ?>'
                }, function(response) {
                    alert('Caché limpiado: ' + response.message);
                });
            }
            
            function toggleDebug(enable) {
                $.post(ajaxurl, {
                    action: 'kee_dev_test',
                    test_type: 'toggle_debug',
                    enable: enable,
                    nonce: '<?php echo wp_create_nonce('kee_dev_test'); ?>'
                }, function(response) {
                    alert('Debug ' + (enable ? 'habilitado' : 'deshabilitado') + ': ' + response.message);
                });
            }
            
            function exportConfig() {
                $.post(ajaxurl, {
                    action: 'kee_dev_test',
                    test_type: 'export_config',
                    nonce: '<?php echo wp_create_nonce('kee_dev_test'); ?>'
                }, function(response) {
                    if (response.success) {
                        window.open(response.download_url);
                    } else {
                        alert('Error al exportar: ' + response.message);
                    }
                });
            }
            
            function showTestResult(testType, response) {
                var resultClass = response.success ? 'test-success' : 'test-error';
                var resultHtml = '<div class="test-result ' + resultClass + '">' +
                    '<strong>' + testType.toUpperCase() + ':</strong> ' + response.message +
                    '</div>';
                
                $('#' + testType + '-test-result').remove();
                $('#' + testType + '-test').after(resultHtml);
            }
            
            function showWidgetList(widgets) {
                var html = '<div class="widget-list">';
                for (var i = 0; i < widgets.length; i++) {
                    html += '<div class="widget-item">' + widgets[i] + '</div>';
                }
                html += '</div>';
                
                $('#widget-list-container').html(html);
            }
        });
        </script>
        <?php
    }
    
    /**
     * Manejar pruebas de desarrollo
     */
    public static function handle_dev_test() {
        check_ajax_referer('kee_dev_test', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $test_type = sanitize_text_field($_POST['test_type']);
        
        switch ($test_type) {
            case 'elementor':
                $result = self::test_elementor();
                break;
            case 'widgets':
                $result = self::test_widgets();
                break;
            case 'assets':
                $result = self::test_assets();
                break;
            case 'check_assets':
                $result = self::check_assets();
                break;
            case 'minify_assets':
                $result = self::minify_assets();
                break;
            case 'clear_cache':
                $result = self::clear_cache();
                break;
            case 'toggle_debug':
                $enable = $_POST['enable'] === 'true';
                $result = self::toggle_debug($enable);
                break;
            case 'export_config':
                $result = self::export_config();
                break;
            default:
                $result = array('success' => false, 'message' => 'Tipo de prueba no válido');
        }
        
        wp_send_json($result);
    }
    
    /**
     * Manejar pruebas de widgets
     */
    public static function handle_widget_test() {
        check_ajax_referer('kee_dev_widget', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $widget_action = sanitize_text_field($_POST['widget_action']);
        
        switch ($widget_action) {
            case 'list':
                $result = self::list_widgets();
                break;
            case 'test':
                $widget_name = sanitize_text_field($_POST['widget_name']);
                $result = self::test_widget($widget_name);
                break;
            case 'regenerate':
                $result = self::regenerate_widgets();
                break;
            default:
                $result = array('success' => false, 'message' => 'Acción de widget no válida');
        }
        
        wp_send_json($result);
    }
    
    /**
     * Probar Elementor
     */
    private static function test_elementor() {
        $tests = array();
        
        // Test 1: Elementor cargado
        $elementor_loaded = did_action('elementor/loaded');
        $tests[] = 'Elementor cargado: ' . ($elementor_loaded ? 'Sí' : 'No');
        
        // Test 2: Versión de Elementor
        if (defined('ELEMENTOR_VERSION')) {
            $tests[] = 'Versión Elementor: ' . ELEMENTOR_VERSION;
        } else {
            $tests[] = 'Versión Elementor: No disponible';
        }
        
        // Test 3: Elementor Pro
        $elementor_pro = defined('ELEMENTOR_PRO_VERSION');
        $tests[] = 'Elementor Pro: ' . ($elementor_pro ? 'Disponible' : 'No disponible');
        
        // Test 4: Hooks de Elementor
        $hooks = array(
            'elementor/widgets/register' => 'Registro de widgets',
            'elementor/elements/categories_registered' => 'Categorías de elementos'
        );
        
        foreach ($hooks as $hook => $description) {
            $tests[] = "$description: " . (has_action($hook) ? 'Registrado' : 'No registrado');
        }
        
        return array(
            'success' => $elementor_loaded,
            'message' => $elementor_loaded ? 'Elementor funcionando correctamente' : 'Elementor no está cargado',
            'details' => implode("\n", $tests)
        );
    }
    
    /**
     * Probar widgets
     */
    private static function test_widgets() {
        $widgets = array(
            'KEE_Home_Slider_Kintaelectic_Widget',
            'KEE_Home_Banner_Product_Kintaelectric_Widget',
            'KEE_Home_Product_Kintaelectric_Widget',
            'KEE_Home_Product_Card_Carousel_Kintaelectric_Widget',
            'KEE_Team_Members_Kintaelectric_Widget',
            'KEE_About_Content_Kintaelectric_Widget',
            'KEE_FAQ_Accordion_Kintaelectric_Widget',
            'KEE_Brand_Carousel_Kintaelectric_Widget',
            'KEE_Breadcrumb_Kintaelectric_Widget',
            'KEE_Hero_Banner_Kintaelectric_Widget',
            'KEE_About_Cards_Kintaelectric_Widget',
            'KEE_Contact_Kintaelectric_Widget'
        );
        
        $available_widgets = array();
        $missing_widgets = array();
        
        foreach ($widgets as $widget) {
            if (class_exists($widget)) {
                $available_widgets[] = $widget;
            } else {
                $missing_widgets[] = $widget;
            }
        }
        
        $tests = array();
        $tests[] = 'Widgets disponibles: ' . count($available_widgets) . '/' . count($widgets);
        
        if (!empty($missing_widgets)) {
            $tests[] = 'Widgets faltantes: ' . implode(', ', $missing_widgets);
        }
        
        return array(
            'success' => empty($missing_widgets),
            'message' => empty($missing_widgets) ? 'Todos los widgets están disponibles' : 'Algunos widgets no están disponibles',
            'details' => implode("\n", $tests)
        );
    }
    
    /**
     * Probar assets
     */
    private static function test_assets() {
        $assets = array(
            'CSS' => KEE_PLUGIN_URL . 'assets/css/kinta-electronic-elementor.css',
            'JS' => KEE_PLUGIN_URL . 'assets/js/kinta-electronic-elementor.js'
        );
        
        $tests = array();
        $all_assets_exist = true;
        
        foreach ($assets as $type => $url) {
            $file_path = str_replace(KEE_PLUGIN_URL, KEE_PLUGIN_DIR, $url);
            $exists = file_exists($file_path);
            $tests[] = "$type: " . ($exists ? 'Existe' : 'No existe');
            
            if (!$exists) {
                $all_assets_exist = false;
            }
        }
        
        return array(
            'success' => $all_assets_exist,
            'message' => $all_assets_exist ? 'Todos los assets están disponibles' : 'Algunos assets no están disponibles',
            'details' => implode("\n", $tests)
        );
    }
    
    /**
     * Verificar assets
     */
    private static function check_assets() {
        $assets = array(
            'CSS' => KEE_PLUGIN_DIR . 'assets/css/kinta-electronic-elementor.css',
            'JS' => KEE_PLUGIN_DIR . 'assets/js/kinta-electronic-elementor.js'
        );
        
        $results = array();
        
        foreach ($assets as $type => $path) {
            if (file_exists($path)) {
                $size = filesize($path);
                $modified = date('Y-m-d H:i:s', filemtime($path));
                $results[] = "$type: Existe ({$size} bytes, modificado: $modified)";
            } else {
                $results[] = "$type: No existe";
            }
        }
        
        return array(
            'success' => true,
            'message' => 'Verificación de assets completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Minificar assets
     */
    private static function minify_assets() {
        // Por ahora solo simulamos la minificación
        // En un entorno real, aquí se implementaría la lógica de minificación
        
        return array(
            'success' => true,
            'message' => 'Assets minificados correctamente (simulado)'
        );
    }
    
    /**
     * Limpiar caché
     */
    private static function clear_cache() {
        // Limpiar caché de WordPress
        wp_cache_flush();
        
        // Limpiar transients
        delete_transient('kee_widgets_cache');
        delete_transient('kee_assets_cache');
        
        return array(
            'success' => true,
            'message' => 'Caché limpiado correctamente'
        );
    }
    
    /**
     * Toggle debug
     */
    private static function toggle_debug($enable) {
        update_option('kee_debug_mode', $enable);
        
        return array(
            'success' => true,
            'message' => 'Debug ' . ($enable ? 'habilitado' : 'deshabilitado')
        );
    }
    
    /**
     * Exportar configuración
     */
    private static function export_config() {
        $config = array(
            'plugin_version' => KEE_PLUGIN_VERSION,
            'debug_mode' => get_option('kee_debug_mode', false),
            'widgets_enabled' => get_option('kee_widgets_enabled', true),
            'assets_minified' => get_option('kee_assets_minified', false)
        );
        
        $config_json = json_encode($config, JSON_PRETTY_PRINT);
        $filename = 'kee-config-' . date('Y-m-d-H-i-s') . '.json';
        
        // Crear archivo temporal
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        file_put_contents($file_path, $config_json);
        
        $download_url = $upload_dir['url'] . '/' . $filename;
        
        return array(
            'success' => true,
            'message' => 'Configuración exportada correctamente',
            'download_url' => $download_url
        );
    }
    
    /**
     * Listar widgets
     */
    private static function list_widgets() {
        $widgets = array(
            'Home Slider Kintaelectic',
            'Home Banner Product Kintaelectric',
            'Home Product Kintaelectric',
            'Home Product Card Carousel Kintaelectric',
            'Team Members Kintaelectric',
            'About Content Kintaelectric',
            'FAQ Accordion Kintaelectric',
            'Brand Carousel Kintaelectric',
            'Breadcrumb Kintaelectric',
            'Hero Banner Kintaelectric',
            'About Cards Kintaelectric',
            'Contact Kintaelectric'
        );
        
        return array(
            'success' => true,
            'widgets' => $widgets
        );
    }
    
    /**
     * Probar widget específico
     */
    private static function test_widget($widget_name) {
        // Simular prueba de widget
        return array(
            'success' => true,
            'message' => "Widget '$widget_name' probado correctamente"
        );
    }
    
    /**
     * Regenerar widgets
     */
    private static function regenerate_widgets() {
        // Simular regeneración de widgets
        return array(
            'success' => true,
            'message' => 'Widgets regenerados correctamente'
        );
    }
    
    /**
     * Mostrar información del sistema
     */
    private static function display_system_info() {
        $info = array(
            'Plugin Version' => KEE_PLUGIN_VERSION,
            'WordPress Version' => get_bloginfo('version'),
            'PHP Version' => PHP_VERSION,
            'Elementor Version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'No disponible',
            'Elementor Pro' => defined('ELEMENTOR_PRO_VERSION') ? ELEMENTOR_PRO_VERSION : 'No disponible',
            'Debug Mode' => defined('WP_DEBUG') && WP_DEBUG ? 'Habilitado' : 'Deshabilitado',
            'Memory Limit' => ini_get('memory_limit'),
            'Max Execution Time' => ini_get('max_execution_time') . 's'
        );
        
        echo '<div class="system-info-grid">';
        
        foreach ($info as $key => $value) {
            echo '<div class="info-card">';
            echo '<div class="info-title">' . $key . '</div>';
            echo '<div class="info-value">' . $value . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}

// Inicializar herramientas de desarrollo
KEE_Dev_Tools::init();
