<?php
/**
 * Archivo de Prueba para CSS Dinámico - WooCommerce Venezuela Pro
 * Este archivo se puede usar para probar la funcionalidad
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Test_Dynamic_CSS {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wvp_test_dynamic_css', array($this, 'test_dynamic_css'));
        add_action('wp_ajax_nopriv_wvp_test_dynamic_css', array($this, 'test_dynamic_css'));
        
        // Agregar página de prueba en admin
        add_action('admin_menu', array($this, 'add_test_page'));
    }
    
    /**
     * Agregar página de prueba
     */
    public function add_test_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Prueba CSS Dinámico',
            'Prueba CSS Dinámico',
            'manage_woocommerce',
            'wvp-test-dynamic-css',
            array($this, 'display_test_page')
        );
    }
    
    /**
     * Mostrar página de prueba
     */
    public function display_test_page() {
        ?>
        <div class="wrap">
            <h1>Prueba de CSS Dinámico - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página permite probar la funcionalidad de CSS dinámico.</p>
            </div>
            
            <h2>Configuraciones Actuales</h2>
            <?php
            $settings = array(
                'display_style' => get_option('wvp_display_style', 'minimal'),
                'primary_color' => get_option('wvp_primary_color', '#007cba'),
                'secondary_color' => get_option('wvp_secondary_color', '#005a87'),
                'success_color' => get_option('wvp_success_color', '#28a745'),
                'warning_color' => get_option('wvp_warning_color', '#ffc107'),
                'font_family' => get_option('wvp_font_family', 'system'),
                'font_size' => get_option('wvp_font_size', 'medium'),
                'font_weight' => get_option('wvp_font_weight', '400'),
                'text_transform' => get_option('wvp_text_transform', 'none'),
                'padding' => get_option('wvp_padding', 'medium'),
                'margin' => get_option('wvp_margin', 'medium'),
                'border_radius' => get_option('wvp_border_radius', 'medium'),
                'shadow' => get_option('wvp_shadow', 'small')
            );
            
            echo '<table class="widefat">';
            echo '<thead><tr><th>Configuración</th><th>Valor</th></tr></thead>';
            echo '<tbody>';
            foreach ($settings as $key => $value) {
                echo '<tr><td>' . esc_html($key) . '</td><td>' . esc_html($value) . '</td></tr>';
            }
            echo '</tbody>';
            echo '</table>';
            ?>
            
            <h2>CSS Generado</h2>
            <?php
            if (class_exists('WVP_Dynamic_CSS_Generator')) {
                $generator = WVP_Dynamic_CSS_Generator::get_instance();
                $css = $generator->get_generated_css();
                
                if ($css) {
                    echo '<textarea readonly style="width: 100%; height: 300px; font-family: monospace;">' . esc_textarea($css) . '</textarea>';
                } else {
                    echo '<p class="notice notice-warning">No se pudo generar CSS dinámico.</p>';
                }
            } else {
                echo '<p class="notice notice-error">La clase WVP_Dynamic_CSS_Generator no está disponible.</p>';
            }
            ?>
            
            <h2>Prueba de Elementos</h2>
            <div class="wvp-test-container">
                <h3>Producto de Prueba</h3>
                <div class="wvp-product-price-container wvp-minimal">
                    <div class="wvp-price-display">
                        <span class="wvp-price-usd" style="display: block;">$15.00</span>
                        <span class="wvp-price-ves" style="display: none;">Bs. 2.365,93</span>
                    </div>
                    <div class="wvp-currency-switcher" data-price-usd="15.00" data-price-ves="2365.93">
                        <button class="wvp-currency-option active" data-currency="USD">USD</button>
                        <button class="wvp-currency-option" data-currency="VES">VES</button>
                    </div>
                    <div class="wvp-price-conversion">
                        <span class="wvp-ves-reference">Equivale a Bs. 2.365,93</span>
                    </div>
                    <div class="wvp-rate-info">Tasa BCV: 157,73</div>
                </div>
                
                <h3>Shortcode de Prueba</h3>
                <div class="wvp-bcv-rate wvp-minimal">
                    Tasa BCV: 157,73 Bs./USD
                </div>
                
                <h3>Selector de Moneda de Prueba</h3>
                <div class="wvp-currency-switcher wvp-minimal">
                    <button class="wvp-currency-option active" data-currency="USD">USD</button>
                    <button class="wvp-currency-option" data-currency="VES">VES</button>
                </div>
            </div>
            
            <h2>Acciones de Prueba</h2>
            <p>
                <button type="button" id="wvp-test-generation" class="button button-primary">
                    Probar Generación de CSS
                </button>
                <button type="button" id="wvp-test-regenerate" class="button button-secondary">
                    Regenerar CSS Dinámico
                </button>
                <button type="button" id="wvp-test-clear-cache" class="button button-secondary">
                    Limpiar Caché
                </button>
                <button type="button" id="wvp-test-performance" class="button button-secondary">
                    Ver Estadísticas de Rendimiento
                </button>
            </p>
            
            <div id="wvp-test-results"></div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#wvp-test-generation').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_dynamic_css',
                            test_type: 'test_generation'
                        },
                        success: function(response) {
                            var html = '<div class="notice notice-success"><p>' + response.data.message + '</p>';
                            html += '<h4>Resultados de las Pruebas:</h4>';
                            html += '<ul>';
                            html += '<li><strong>Longitud del CSS:</strong> ' + response.data.tests.css_length + ' caracteres</li>';
                            html += '<li><strong>Variables CSS:</strong> ' + (response.data.tests.has_variables ? '✅ Encontradas' : '❌ No encontradas') + '</li>';
                            html += '<li><strong>Estilos de Productos:</strong> ' + (response.data.tests.has_product_styles ? '✅ Encontrados' : '❌ No encontrados') + '</li>';
                            html += '<li><strong>Estilos de Shortcodes:</strong> ' + (response.data.tests.has_shortcode_styles ? '✅ Encontrados' : '❌ No encontrados') + '</li>';
                            html += '</ul>';
                            html += '<h4>Muestra del CSS:</h4>';
                            html += '<textarea readonly style="width: 100%; height: 100px; font-family: monospace;">' + response.data.tests.css_sample + '</textarea>';
                            html += '</div>';
                            $('#wvp-test-results').html(html);
                        },
                        error: function() {
                            $('#wvp-test-results').html('<div class="notice notice-error"><p>Error al probar generación de CSS</p></div>');
                        }
                    });
                });
                
                $('#wvp-test-regenerate').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_dynamic_css',
                            test_type: 'regenerate'
                        },
                        success: function(response) {
                            $('#wvp-test-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            location.reload();
                        },
                        error: function() {
                            $('#wvp-test-results').html('<div class="notice notice-error"><p>Error al regenerar CSS</p></div>');
                        }
                    });
                });
                
                $('#wvp-test-clear-cache').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_dynamic_css',
                            test_type: 'clear_cache'
                        },
                        success: function(response) {
                            $('#wvp-test-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                        },
                        error: function() {
                            $('#wvp-test-results').html('<div class="notice notice-error"><p>Error al limpiar caché</p></div>');
                        }
                    });
                });
                
                $('#wvp-test-performance').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_dynamic_css',
                            test_type: 'performance_stats'
                        },
                        success: function(response) {
                            var html = '<div class="notice notice-info"><p>' + response.data.message + '</p>';
                            html += '<h4>Estadísticas de Rendimiento:</h4>';
                            html += '<ul>';
                            html += '<li><strong>Tamaño del CSS:</strong> ' + response.data.stats.css_size_kb + ' KB</li>';
                            html += '<li><strong>Necesita Optimización:</strong> ' + (response.data.stats.needs_optimization ? 'Sí' : 'No') + '</li>';
                            html += '<li><strong>Minificación Habilitada:</strong> ' + (response.data.stats.optimization_enabled ? 'Sí' : 'No') + '</li>';
                            html += '<li><strong>Compresión Habilitada:</strong> ' + (response.data.stats.compression_enabled ? 'Sí' : 'No') + '</li>';
                            html += '<li><strong>Preload Habilitado:</strong> ' + (response.data.stats.preload_enabled ? 'Sí' : 'No') + '</li>';
                            html += '<li><strong>CSS Crítico Habilitado:</strong> ' + (response.data.stats.critical_css_enabled ? 'Sí' : 'No') + '</li>';
                            html += '</ul>';
                            html += '</div>';
                            $('#wvp-test-results').html(html);
                        },
                        error: function() {
                            $('#wvp-test-results').html('<div class="notice notice-error"><p>Error al obtener estadísticas</p></div>');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Probar CSS dinámico via AJAX
     */
    public function test_dynamic_css() {
        $test_type = sanitize_text_field($_POST['test_type']);
        
        switch ($test_type) {
            case 'regenerate':
                if (class_exists('WVP_Dynamic_CSS_Generator')) {
                    $generator = WVP_Dynamic_CSS_Generator::get_instance();
                    $generator->force_regenerate();
                    wp_send_json_success(array('message' => 'CSS dinámico regenerado correctamente'));
                } else {
                    wp_send_json_error('Generador de CSS no disponible');
                }
                break;
                
            case 'clear_cache':
                if (class_exists('WVP_Dynamic_CSS_Generator')) {
                    $generator = WVP_Dynamic_CSS_Generator::get_instance();
                    $generator->clear_css_cache();
                    wp_send_json_success(array('message' => 'Caché de CSS limpiado correctamente'));
                } else {
                    wp_send_json_error('Generador de CSS no disponible');
                }
                break;
                
            case 'test_generation':
                if (class_exists('WVP_Dynamic_CSS_Generator')) {
                    $generator = WVP_Dynamic_CSS_Generator::get_instance();
                    $css = $generator->get_generated_css();
                    
                    if (empty($css)) {
                        wp_send_json_error('No se pudo generar CSS dinámico');
                    }
                    
                    $tests = array();
                    $tests['css_length'] = strlen($css);
                    $tests['has_variables'] = strpos($css, '--wvp-primary-color') !== false;
                    $tests['has_product_styles'] = strpos($css, '.wvp-product-price-container') !== false;
                    $tests['has_shortcode_styles'] = strpos($css, '.wvp-bcv-rate') !== false;
                    $tests['css_sample'] = substr($css, 0, 500);
                    
                    wp_send_json_success(array(
                        'message' => 'Pruebas de generación completadas',
                        'tests' => $tests
                    ));
                } else {
                    wp_send_json_error('Generador de CSS no disponible');
                }
                break;
                
            case 'performance_stats':
                if (class_exists('WVP_Performance_Optimizer')) {
                    $optimizer = WVP_Performance_Optimizer::get_instance();
                    $stats = $optimizer->get_performance_stats();
                    
                    wp_send_json_success(array(
                        'message' => 'Estadísticas de rendimiento obtenidas',
                        'stats' => $stats
                    ));
                } else {
                    wp_send_json_error('Optimizador de rendimiento no disponible');
                }
                break;
                
            default:
                wp_send_json_error('Tipo de prueba no válido');
        }
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_Test_Dynamic_CSS();
}
?>
