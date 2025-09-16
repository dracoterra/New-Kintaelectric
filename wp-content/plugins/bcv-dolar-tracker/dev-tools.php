<?php
/**
 * Herramientas de desarrollo para BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Dev_Tools {
    
    /**
     * Inicializar herramientas de desarrollo
     */
    public static function init() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('admin_menu', array(__CLASS__, 'add_dev_menu'));
            add_action('wp_ajax_bcv_dev_test', array(__CLASS__, 'handle_dev_test'));
            add_action('wp_ajax_bcv_dev_scrape', array(__CLASS__, 'handle_manual_scrape'));
            add_action('wp_ajax_bcv_dev_stats', array(__CLASS__, 'handle_stats'));
        }
    }
    
    /**
     * Añadir menú de desarrollo
     */
    public static function add_dev_menu() {
        add_submenu_page(
            'tools.php',
            'BCV Dev Tools',
            'BCV Dev Tools',
            'manage_options',
            'bcv-dev-tools',
            array(__CLASS__, 'dev_tools_page')
        );
    }
    
    /**
     * Página de herramientas de desarrollo
     */
    public static function dev_tools_page() {
        ?>
        <div class="wrap">
            <h1>🛠️ BCV Dólar Tracker - Development Tools</h1>
            
            <div class="bcv-dev-tools">
                <div class="dev-section">
                    <h2>🧪 Pruebas del Sistema</h2>
                    <button id="run-database-test" class="button button-primary">Probar Base de Datos</button>
                    <button id="run-cron-test" class="button button-secondary">Probar Sistema Cron</button>
                    <button id="run-scraper-test" class="button button-secondary">Probar Scraper</button>
                    <button id="run-automated-tests" class="button button-primary">🚀 Suite de Pruebas Automatizadas</button>
                    <button id="run-load-tests" class="button button-secondary">⚡ Pruebas de Carga</button>
                </div>
                
                <div class="dev-section">
                    <h2>🔄 Operaciones Manuales</h2>
                    <button id="manual-scrape" class="button button-primary">Scraping Manual</button>
                    <button id="sync-wvp" class="button button-primary">Sincronizar con WVP</button>
                    <button id="clear-cache" class="button button-secondary">Limpiar Caché</button>
                    <button id="reset-cron" class="button button-secondary">Reiniciar Cron</button>
                </div>
                
                <div class="dev-section">
                    <h2>📊 Estadísticas y Monitoreo</h2>
                    <button id="view-stats" class="button button-secondary">Ver Estadísticas</button>
                    <button id="export-data" class="button button-secondary">Exportar Datos</button>
                    <div id="stats-container" style="display:none; margin-top: 20px;">
                        <div id="stats-content"></div>
                    </div>
                </div>
                
                <div class="dev-section">
                    <h2>🔧 Configuración de Desarrollo</h2>
                    <button id="enable-debug" class="button button-secondary">Habilitar Debug</button>
                    <button id="disable-debug" class="button button-secondary">Deshabilitar Debug</button>
                    <button id="test-email" class="button button-secondary">Probar Email</button>
                </div>
            </div>
        </div>
        
        <style>
        .bcv-dev-tools .dev-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .bcv-dev-tools .dev-section h2 {
            margin-top: 0;
            color: #23282d;
        }
        .bcv-dev-tools button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        #stats-container {
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .stat-card {
            background: #fff;
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
            $('#run-database-test').click(function() {
                runTest('database');
            });
            
            $('#run-cron-test').click(function() {
                runTest('cron');
            });
            
            $('#run-scraper-test').click(function() {
                runTest('scraper');
            });
            
            // Operaciones manuales
            $('#manual-scrape').click(function() {
                manualScrape();
            });
            
            $('#sync-wvp').click(function() {
                syncWVP();
            });
            
            $('#clear-cache').click(function() {
                clearCache();
            });
            
            $('#reset-cron').click(function() {
                resetCron();
            });
            
            // Estadísticas
            $('#view-stats').click(function() {
                viewStats();
            });
            
            $('#export-data').click(function() {
                exportData();
            });
            
            // Configuración
            $('#enable-debug').click(function() {
                toggleDebug(true);
            });
            
            $('#disable-debug').click(function() {
                toggleDebug(false);
            });
            
            $('#test-email').click(function() {
                testEmail();
            });
            
            function runTest(testType) {
                $.post(ajaxurl, {
                    action: 'bcv_dev_test',
                    test_type: testType,
                    nonce: '<?php echo wp_create_nonce('bcv_dev_test'); ?>'
                }, function(response) {
                    showTestResult(testType, response);
                });
            }
            
            function manualScrape() {
                $.post(ajaxurl, {
                    action: 'bcv_dev_scrape',
                    nonce: '<?php echo wp_create_nonce('bcv_dev_scrape'); ?>'
                }, function(response) {
                    alert('Scraping manual: ' + response.message);
                });
            }
            
            function syncWVP() {
                $.post(ajaxurl, {
                    action: 'bcv_dev_test',
                    test_type: 'sync_wvp',
                    nonce: '<?php echo wp_create_nonce('bcv_dev_test'); ?>'
                }, function(response) {
                    alert('Sincronización WVP: ' + response.message);
                });
            }
            
            function clearCache() {
                $.post(ajaxurl, {
                    action: 'bcv_dev_test',
                    test_type: 'clear_cache',
                    nonce: '<?php echo wp_create_nonce('bcv_dev_test'); ?>'
                }, function(response) {
                    alert('Caché limpiado: ' + response.message);
                });
            }
            
            function resetCron() {
                $.post(ajaxurl, {
                    action: 'bcv_dev_test',
                    test_type: 'reset_cron',
                    nonce: '<?php echo wp_create_nonce('bcv_dev_test'); ?>'
                }, function(response) {
                    alert('Cron reiniciado: ' + response.message);
                });
            }
            
            function viewStats() {
                $.post(ajaxurl, {
                    action: 'bcv_dev_stats',
                    nonce: '<?php echo wp_create_nonce('bcv_dev_stats'); ?>'
                }, function(response) {
                    $('#stats-content').html(response.html);
                    $('#stats-container').show();
                });
            }
            
            function exportData() {
                window.open('<?php echo admin_url('admin-ajax.php'); ?>?action=bcv_export_data&nonce=<?php echo wp_create_nonce('bcv_export_data'); ?>');
            }
            
            function toggleDebug(enable) {
                $.post(ajaxurl, {
                    action: 'bcv_dev_test',
                    test_type: 'toggle_debug',
                    enable: enable,
                    nonce: '<?php echo wp_create_nonce('bcv_dev_test'); ?>'
                }, function(response) {
                    alert('Debug ' + (enable ? 'habilitado' : 'deshabilitado') + ': ' + response.message);
                });
            }
            
            function testEmail() {
                $.post(ajaxurl, {
                    action: 'bcv_dev_test',
                    test_type: 'test_email',
                    nonce: '<?php echo wp_create_nonce('bcv_dev_test'); ?>'
                }, function(response) {
                    alert('Email de prueba: ' + response.message);
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
        });
        </script>
        <?php
    }
    
    /**
     * Manejar pruebas de desarrollo
     */
    public static function handle_dev_test() {
        check_ajax_referer('bcv_dev_test', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $test_type = sanitize_text_field($_POST['test_type']);
        
        switch ($test_type) {
            case 'database':
                $result = self::test_database();
                break;
            case 'cron':
                $result = self::test_cron();
                break;
            case 'scraper':
                $result = self::test_scraper();
                break;
            case 'clear_cache':
                $result = self::clear_cache();
                break;
            case 'reset_cron':
                $result = self::reset_cron();
                break;
            case 'toggle_debug':
                $enable = $_POST['enable'] === 'true';
                $result = self::toggle_debug($enable);
                break;
            case 'test_email':
                $result = self::test_email();
                break;
            case 'sync_wvp':
                $result = self::sync_with_wvp();
                break;
            case 'run_automated_tests':
                $result = self::run_automated_tests();
                break;
            case 'run_load_tests':
                $result = self::run_load_tests();
                break;
            default:
                $result = array('success' => false, 'message' => 'Tipo de prueba no válido');
        }
        
        wp_send_json($result);
    }
    
    /**
     * Manejar scraping manual
     */
    public static function handle_manual_scrape() {
        check_ajax_referer('bcv_dev_scrape', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $scraper = new BCV_Scraper();
        $result = $scraper->scrape_dollar_rate();
        
        wp_send_json($result);
    }
    
    /**
     * Manejar estadísticas
     */
    public static function handle_stats() {
        check_ajax_referer('bcv_dev_stats', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        $stats = self::get_system_stats();
        
        wp_send_json(array('html' => $stats));
    }
    
    /**
     * Probar base de datos
     */
    private static function test_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        
        // Verificar si la tabla existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return array(
                'success' => false,
                'message' => 'La tabla de base de datos no existe'
            );
        }
        
        // Verificar estructura
        $columns = $wpdb->get_results("DESCRIBE $table_name");
        $expected_columns = array('id', 'datatime', 'precio');
        $actual_columns = array_column($columns, 'Field');
        
        $missing_columns = array_diff($expected_columns, $actual_columns);
        
        if (!empty($missing_columns)) {
            return array(
                'success' => false,
                'message' => 'Faltan columnas: ' . implode(', ', $missing_columns)
            );
        }
        
        // Probar inserción
        $test_data = array(
            'datatime' => current_time('mysql'),
            'precio' => '36.50'
        );
        
        $insert_result = $wpdb->insert($table_name, $test_data);
        
        if ($insert_result === false) {
            return array(
                'success' => false,
                'message' => 'Error al insertar datos de prueba'
            );
        }
        
        // Limpiar datos de prueba
        $wpdb->delete($table_name, array('id' => $wpdb->insert_id));
        
        return array(
            'success' => true,
            'message' => 'Base de datos funcionando correctamente'
        );
    }
    
    /**
     * Probar sistema cron
     */
    private static function test_cron() {
        $cron = new BCV_Cron();
        
        // Verificar si el cron está programado
        $scheduled = wp_next_scheduled('bcv_scrape_dollar_rate');
        
        if (!$scheduled) {
            return array(
                'success' => false,
                'message' => 'El cron no está programado'
            );
        }
        
        // Verificar configuración
        $settings = get_option('bcv_cron_settings', array());
        
        if (empty($settings)) {
            return array(
                'success' => false,
                'message' => 'Configuración del cron no encontrada'
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Sistema cron funcionando correctamente'
        );
    }
    
    /**
     * Probar scraper
     */
    private static function test_scraper() {
        $scraper = new BCV_Scraper();
        $result = $scraper->scrape_dollar_rate();
        
        if ($result['success']) {
            return array(
                'success' => true,
                'message' => 'Scraper funcionando correctamente - Precio: ' . $result['price'] . ' Bs'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Error en scraper: ' . $result['message']
            );
        }
    }
    
    /**
     * Limpiar caché
     */
    private static function clear_cache() {
        // Limpiar caché de WordPress
        wp_cache_flush();
        
        // Limpiar transients
        delete_transient('bcv_dollar_rate_cache');
        delete_transient('bcv_last_scrape');
        
        return array(
            'success' => true,
            'message' => 'Caché limpiado correctamente'
        );
    }
    
    /**
     * Reiniciar cron
     */
    private static function reset_cron() {
        // Limpiar cron existente
        wp_clear_scheduled_hook('bcv_scrape_dollar_rate');
        
        // Recrear cron
        $cron = new BCV_Cron();
        $cron->schedule_cron();
        
        return array(
            'success' => true,
            'message' => 'Cron reiniciado correctamente'
        );
    }
    
    /**
     * Toggle debug
     */
    private static function toggle_debug($enable) {
        update_option('bcv_debug_mode', $enable);
        
        return array(
            'success' => true,
            'message' => 'Debug ' . ($enable ? 'habilitado' : 'deshabilitado')
        );
    }
    
    /**
     * Probar email
     */
    private static function test_email() {
        $admin_email = get_option('admin_email');
        $subject = 'BCV Dólar Tracker - Prueba de Email';
        $message = 'Este es un email de prueba del sistema BCV Dólar Tracker.';
        
        $result = wp_mail($admin_email, $subject, $message);
        
        if ($result) {
            return array(
                'success' => true,
                'message' => 'Email enviado correctamente a ' . $admin_email
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Error al enviar email'
            );
        }
    }
    
    /**
     * Sincronizar con WooCommerce Venezuela Pro
     */
    private static function sync_with_wvp() {
        if (class_exists('BCV_Dolar_Tracker')) {
            $result = BCV_Dolar_Tracker::sync_with_wvp();
            
            if ($result) {
                return array(
                    'success' => true,
                    'message' => 'Sincronización con WVP completada exitosamente'
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'No se pudo sincronizar con WVP - No hay datos disponibles'
                );
            }
        } else {
            return array(
                'success' => false,
                'message' => 'Clase BCV_Dolar_Tracker no disponible'
            );
        }
    }
    
    /**
     * Obtener estadísticas del sistema
     */
    private static function get_system_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bcv_precio_dolar';
        
        // Estadísticas básicas
        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $latest_price = $wpdb->get_row("SELECT * FROM $table_name ORDER BY datatime DESC LIMIT 1");
        $oldest_price = $wpdb->get_row("SELECT * FROM $table_name ORDER BY datatime ASC LIMIT 1");
        
        // Estadísticas por día
        $daily_stats = $wpdb->get_results("
            SELECT DATE(datatime) as date, COUNT(*) as count, AVG(precio) as avg_price
            FROM $table_name 
            WHERE datatime >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(datatime)
            ORDER BY date DESC
        ");
        
        $html = '<div class="stats-grid">';
        
        // Tarjeta de total de registros
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-value">' . $total_records . '</div>';
        $html .= '<div class="stat-label">Total Registros</div>';
        $html .= '</div>';
        
        // Tarjeta de último precio
        if ($latest_price) {
            $html .= '<div class="stat-card">';
            $html .= '<div class="stat-value">' . number_format($latest_price->precio, 2) . '</div>';
            $html .= '<div class="stat-label">Último Precio (Bs)</div>';
            $html .= '</div>';
        }
        
        // Tarjeta de registros hoy
        $today_count = $wpdb->get_var("
            SELECT COUNT(*) FROM $table_name 
            WHERE DATE(datatime) = CURDATE()
        ");
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-value">' . $today_count . '</div>';
        $html .= '<div class="stat-label">Registros Hoy</div>';
        $html .= '</div>';
        
        // Tarjeta de registros esta semana
        $week_count = $wpdb->get_var("
            SELECT COUNT(*) FROM $table_name 
            WHERE datatime >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-value">' . $week_count . '</div>';
        $html .= '<div class="stat-label">Registros (7 días)</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Tabla de estadísticas diarias
        if (!empty($daily_stats)) {
            $html .= '<h3>Estadísticas Diarias (Últimos 7 días)</h3>';
            $html .= '<table class="widefat">';
            $html .= '<thead><tr><th>Fecha</th><th>Registros</th><th>Precio Promedio</th></tr></thead>';
            $html .= '<tbody>';
            
            foreach ($daily_stats as $stat) {
                $html .= '<tr>';
                $html .= '<td>' . $stat->date . '</td>';
                $html .= '<td>' . $stat->count . '</td>';
                $html .= '<td>' . number_format($stat->avg_price, 2) . ' Bs</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        return $html;
    }
    
    /**
     * Ejecutar suite de pruebas automatizadas
     */
    private static function run_automated_tests() {
        // Cargar clase de pruebas si no está cargada
        if (!class_exists('BCV_Test_Suite')) {
            require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-test-suite.php';
        }
        
        $test_suite = new BCV_Test_Suite();
        $results = $test_suite->run_all_tests();
        
        $message = sprintf(
            'Suite de pruebas completada: %d/%d pruebas exitosas (%.1f%%)',
            $results['passed'],
            $results['total_tests'],
            $results['success_rate']
        );
        
        return array(
            'success' => $results['success_rate'] >= 80, // 80% mínimo para considerar exitoso
            'message' => $message,
            'data' => $results
        );
    }
    
    /**
     * Ejecutar pruebas de carga
     */
    private static function run_load_tests() {
        // Cargar clase de pruebas si no está cargada
        if (!class_exists('BCV_Test_Suite')) {
            require_once BCV_DOLAR_TRACKER_PLUGIN_DIR . 'includes/class-bcv-test-suite.php';
        }
        
        $test_suite = new BCV_Test_Suite();
        $iterations = isset($_POST['iterations']) ? intval($_POST['iterations']) : 10;
        $results = $test_suite->run_load_tests($iterations);
        
        $message = sprintf(
            'Pruebas de carga completadas: %d iteraciones, tiempo promedio inserción: %.4fs, memoria promedio: %s',
            $iterations,
            $results['summary']['avg_insert_time'],
            size_format($results['summary']['avg_memory_usage'])
        );
        
        return array(
            'success' => $results['summary']['avg_insert_time'] < 0.1, // Menos de 100ms por inserción
            'message' => $message,
            'data' => $results
        );
    }
}

// Inicializar herramientas de desarrollo
BCV_Dev_Tools::init();
