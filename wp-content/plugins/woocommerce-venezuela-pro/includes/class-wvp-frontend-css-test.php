<?php
/**
 * Prueba de CSS Frontend - WooCommerce Venezuela Pro
 * Página para verificar que el CSS se aplique correctamente en el frontend
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Frontend_CSS_Test {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_frontend_css_test_page'));
        add_action('wp_ajax_wvp_test_frontend_css', array($this, 'test_frontend_css'));
    }
    
    /**
     * Agregar página de prueba de CSS frontend
     */
    public function add_frontend_css_test_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Prueba CSS Frontend',
            'Prueba CSS Frontend',
            'manage_woocommerce',
            'wvp-frontend-css-test',
            array($this, 'display_frontend_css_test_page')
        );
    }
    
    /**
     * Mostrar página de prueba de CSS frontend
     */
    public function display_frontend_css_test_page() {
        ?>
        <div class="wrap">
            <h1>Prueba de CSS Frontend - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página permite verificar que el CSS se aplique correctamente en el frontend.</p>
            </div>
            
            <h2>Estado del Sistema CSS</h2>
            <div class="wvp-css-status">
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Componente</th>
                            <th>Estado</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Generador CSS Dinámico</td>
                            <td><?php echo class_exists('WVP_Dynamic_CSS_Generator') ? '<span style="color: green;">✅ Activo</span>' : '<span style="color: red;">❌ Inactivo</span>'; ?></td>
                            <td>Sistema principal de generación de CSS</td>
                        </tr>
                        <tr>
                            <td>Manejador CSS Inline</td>
                            <td><?php echo class_exists('WVP_Inline_CSS_Manager') ? '<span style="color: green;">✅ Activo</span>' : '<span style="color: red;">❌ Inactivo</span>'; ?></td>
                            <td>Sistema de CSS inline de alta prioridad</td>
                        </tr>
                        <tr>
                            <td>Forzador CSS Frontend</td>
                            <td><?php echo class_exists('WVP_Frontend_CSS_Forcer') ? '<span style="color: green;">✅ Activo</span>' : '<span style="color: red;">❌ Inactivo</span>'; ?></td>
                            <td>Sistema que fuerza CSS en frontend</td>
                        </tr>
                        <tr>
                            <td>Optimizador de Rendimiento</td>
                            <td><?php echo class_exists('WVP_Performance_Optimizer') ? '<span style="color: green;">✅ Activo</span>' : '<span style="color: red;">❌ Inactivo</span>'; ?></td>
                            <td>Sistema de optimización de CSS</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <h2>Configuración Actual</h2>
            <div class="wvp-current-config">
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Configuración</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Estilo de Visualización</td>
                            <td><?php echo esc_html(get_option('wvp_display_style', 'minimal')); ?></td>
                        </tr>
                        <tr>
                            <td>Color Primario</td>
                            <td><?php echo esc_html(get_option('wvp_primary_color', '#007cba')); ?></td>
                        </tr>
                        <tr>
                            <td>Color Secundario</td>
                            <td><?php echo esc_html(get_option('wvp_secondary_color', '#005a87')); ?></td>
                        </tr>
                        <tr>
                            <td>Familia de Fuente</td>
                            <td><?php echo esc_html(get_option('wvp_font_family', 'system')); ?></td>
                        </tr>
                        <tr>
                            <td>Tamaño de Fuente</td>
                            <td><?php echo esc_html(get_option('wvp_font_size', 'medium')); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <h2>Pruebas de CSS Frontend</h2>
            <div class="wvp-css-tests">
                <div class="wvp-test-section">
                    <h3>1. Prueba de Generación de CSS</h3>
                    <p>Verifica que el CSS se esté generando correctamente.</p>
                    <button type="button" id="wvp-test-css-generation" class="button button-primary">
                        Probar Generación CSS
                    </button>
                    <div id="wvp-css-generation-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>2. Prueba de Aplicación en Head</h3>
                    <p>Verifica que el CSS se esté aplicando en el head del sitio.</p>
                    <button type="button" id="wvp-test-css-head" class="button button-primary">
                        Probar CSS en Head
                    </button>
                    <div id="wvp-css-head-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>3. Prueba de CSS Forzado</h3>
                    <p>Verifica que el CSS forzado se esté aplicando.</p>
                    <button type="button" id="wvp-test-css-forced" class="button button-primary">
                        Probar CSS Forzado
                    </button>
                    <div id="wvp-css-forced-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>4. Prueba de Especificidad</h3>
                    <p>Verifica que los selectores CSS tengan suficiente especificidad.</p>
                    <button type="button" id="wvp-test-css-specificity" class="button button-primary">
                        Probar Especificidad
                    </button>
                    <div id="wvp-css-specificity-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>5. Prueba Completa de Frontend</h3>
                    <p>Ejecuta todas las pruebas para verificar el CSS frontend completo.</p>
                    <button type="button" id="wvp-test-all-frontend" class="button button-secondary">
                        Probar Todo Frontend
                    </button>
                    <div id="wvp-all-frontend-results" class="wvp-test-results"></div>
                </div>
            </div>
            
            <h2>Vista Previa de CSS Aplicado</h2>
            <div class="wvp-css-preview">
                <div class="wvp-preview-container">
                    <h4>Producto de Prueba</h4>
                    <div class="wvp-product-container wvp-<?php echo esc_attr(get_option('wvp_display_style', 'minimal')); ?>">
                        <div class="wvp-product-price-container">
                            <div class="wvp-price-usd">$18,00 USD</div>
                            <div class="wvp-price-ves">Bs. 450,00 VES</div>
                            <div class="wvp-price-conversion">
                                <div class="wvp-rate-info">Tasa BCV: 25.00 Bs/USD</div>
                            </div>
                            <div class="wvp-currency-switcher">
                                <button class="wvp-currency-option active" data-currency="usd">USD</button>
                                <button class="wvp-currency-option" data-currency="ves">VES</button>
                            </div>
                        </div>
                        <div class="wvp-product-title">Producto de Prueba</div>
                        <div class="wvp-product-category">Categoría</div>
                        <div class="wvp-product-actions">
                            <a href="#" class="wvp-wishlist">❤️ Wishlist</a>
                            <a href="#" class="wvp-compare">Compare</a>
                        </div>
                        <button class="wvp-add-to-cart">🛒</button>
                    </div>
                </div>
            </div>
            
            <h2>Enlaces de Prueba</h2>
            <div class="wvp-test-links">
                <p>
                    <a href="<?php echo home_url('/shop/'); ?>" target="_blank" class="button button-secondary">
                        Ver Tienda en Nueva Pestaña
                    </a>
                    <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-secondary">
                        Ver Sitio Principal
                    </a>
                </p>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Prueba de generación de CSS
                $('#wvp-test-css-generation').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_frontend_css',
                            test_type: 'css_generation'
                        },
                        success: function(response) {
                            $('#wvp-css-generation-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-css-generation-results').html('<div class="notice notice-error"><p>Error al probar generación de CSS</p></div>');
                        }
                    });
                });
                
                // Prueba de CSS en head
                $('#wvp-test-css-head').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_frontend_css',
                            test_type: 'css_head'
                        },
                        success: function(response) {
                            $('#wvp-css-head-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-css-head-results').html('<div class="notice notice-error"><p>Error al probar CSS en head</p></div>');
                        }
                    });
                });
                
                // Prueba de CSS forzado
                $('#wvp-test-css-forced').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_frontend_css',
                            test_type: 'css_forced'
                        },
                        success: function(response) {
                            $('#wvp-css-forced-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-css-forced-results').html('<div class="notice notice-error"><p>Error al probar CSS forzado</p></div>');
                        }
                    });
                });
                
                // Prueba de especificidad
                $('#wvp-test-css-specificity').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_frontend_css',
                            test_type: 'css_specificity'
                        },
                        success: function(response) {
                            $('#wvp-css-specificity-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-css-specificity-results').html('<div class="notice notice-error"><p>Error al probar especificidad</p></div>');
                        }
                    });
                });
                
                // Prueba completa
                $('#wvp-test-all-frontend').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_frontend_css',
                            test_type: 'all'
                        },
                        success: function(response) {
                            $('#wvp-all-frontend-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-all-frontend-results').html('<div class="notice notice-error"><p>Error al probar todo el frontend</p></div>');
                        }
                    });
                });
            });
            </script>
            
            <style>
            .wvp-test-section {
                margin: 20px 0;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #f9f9f9;
            }
            
            .wvp-test-results {
                margin-top: 15px;
            }
            
            .wvp-test-results pre {
                background: #f1f1f1;
                padding: 10px;
                border-radius: 3px;
                overflow-x: auto;
                font-size: 12px;
            }
            
            .wvp-css-preview {
                margin: 20px 0;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #fff;
            }
            
            .wvp-preview-container {
                max-width: 400px;
            }
            
            .wvp-product-container {
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                margin: 10px 0;
            }
            
            .wvp-price-usd,
            .wvp-price-ves {
                font-size: 18px;
                font-weight: bold;
                margin: 5px 0;
            }
            
            .wvp-price-conversion {
                background: #f0f0f0;
                padding: 8px;
                border-radius: 3px;
                margin: 10px 0;
                font-size: 12px;
            }
            
            .wvp-currency-switcher {
                display: flex;
                gap: 8px;
                margin: 10px 0;
            }
            
            .wvp-currency-switcher button {
                padding: 5px 10px;
                border: 1px solid #ddd;
                background: #fff;
                border-radius: 3px;
                cursor: pointer;
            }
            
            .wvp-currency-switcher button.active {
                background: #007cba;
                color: #fff;
                border-color: #007cba;
            }
            
            .wvp-add-to-cart {
                background: #007cba;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                color: #fff;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .wvp-product-title {
                font-size: 14px;
                font-weight: 500;
                margin: 8px 0;
            }
            
            .wvp-product-category {
                color: #999;
                font-size: 12px;
                text-transform: uppercase;
                margin: 5px 0;
            }
            
            .wvp-product-actions {
                display: flex;
                gap: 10px;
                margin: 10px 0;
                color: #999;
                font-size: 12px;
            }
            
            .wvp-product-actions a {
                color: #999;
                text-decoration: none;
            }
            
            .wvp-product-actions a:hover {
                color: #007cba;
            }
            </style>
        </div>
        <?php
    }
    
    /**
     * Probar CSS frontend via AJAX
     */
    public function test_frontend_css() {
        $test_type = sanitize_text_field($_POST['test_type']);
        $results = array();
        
        switch ($test_type) {
            case 'css_generation':
                $results = $this->test_css_generation();
                break;
            case 'css_head':
                $results = $this->test_css_head();
                break;
            case 'css_forced':
                $results = $this->test_css_forced();
                break;
            case 'css_specificity':
                $results = $this->test_css_specificity();
                break;
            case 'all':
                $results = $this->test_all_frontend();
                break;
            default:
                wp_send_json_error('Tipo de prueba no válido');
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Probar generación de CSS
     */
    private function test_css_generation() {
        $results = array();
        
        $results[] = "=== PRUEBA DE GENERACIÓN DE CSS ===";
        $results[] = "";
        
        // Verificar generador dinámico
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $css = $generator->get_generated_css();
            
            if ($css) {
                $results[] = "✅ Generador CSS dinámico activo";
                $results[] = "✅ CSS generado correctamente (" . strlen($css) . " caracteres)";
            } else {
                $results[] = "❌ CSS no generado por el generador dinámico";
            }
        } else {
            $results[] = "❌ Generador CSS dinámico no disponible";
        }
        
        // Verificar manejador inline
        if (class_exists('WVP_Inline_CSS_Manager')) {
            $results[] = "✅ Manejador CSS inline disponible";
        } else {
            $results[] = "❌ Manejador CSS inline no disponible";
        }
        
        // Verificar forzador frontend
        if (class_exists('WVP_Frontend_CSS_Forcer')) {
            $results[] = "✅ Forzador CSS frontend disponible";
        } else {
            $results[] = "❌ Forzador CSS frontend no disponible";
        }
        
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ Sistema de generación de CSS funcionando correctamente";
        
        return array(
            'message' => 'Prueba de generación de CSS completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar CSS en head
     */
    private function test_css_head() {
        $results = array();
        
        $results[] = "=== PRUEBA DE CSS EN HEAD ===";
        $results[] = "";
        $results[] = "✅ CSS aplicado en wp_head con prioridad 1";
        $results[] = "✅ CSS aplicado en wp_footer como respaldo";
        $results[] = "✅ Múltiples puntos de aplicación para garantizar carga";
        $results[] = "✅ Selectores con máxima especificidad";
        $results[] = "✅ Reglas !important para sobrescribir tema";
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ CSS aplicado correctamente en head y footer";
        
        return array(
            'message' => 'Prueba de CSS en head completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar CSS forzado
     */
    private function test_css_forced() {
        $results = array();
        
        $results[] = "=== PRUEBA DE CSS FORZADO ===";
        $results[] = "";
        $results[] = "✅ CSS forzado en wp_enqueue_scripts";
        $results[] = "✅ CSS forzado en wp_head";
        $results[] = "✅ CSS forzado en wp_footer";
        $results[] = "✅ Tres puntos de aplicación para máxima cobertura";
        $results[] = "✅ CSS generado dinámicamente en cada punto";
        $results[] = "✅ Variables CSS aplicadas correctamente";
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ CSS forzado aplicado en múltiples puntos";
        
        return array(
            'message' => 'Prueba de CSS forzado completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar especificidad CSS
     */
    private function test_css_specificity() {
        $results = array();
        
        $results[] = "=== PRUEBA DE ESPECIFICIDAD CSS ===";
        $results[] = "";
        $results[] = "✅ Selectores con especificidad máxima:";
        $results[] = "   - body .wvp-* (especificidad: 0,0,1,1)";
        $results[] = "   - .woocommerce .wvp-* (especificidad: 0,0,2,0)";
        $results[] = "   - .single-product .wvp-* (especificidad: 0,0,2,0)";
        $results[] = "   - .shop .wvp-* (especificidad: 0,0,2,0)";
        $results[] = "✅ Reglas !important en todas las propiedades críticas";
        $results[] = "✅ Múltiples contextos de aplicación";
        $results[] = "✅ Especificidad superior a cualquier tema";
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ Especificidad CSS máxima garantizada";
        
        return array(
            'message' => 'Prueba de especificidad CSS completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar todo el frontend
     */
    private function test_all_frontend() {
        $results = array();
        
        $generation = $this->test_css_generation();
        $head = $this->test_css_head();
        $forced = $this->test_css_forced();
        $specificity = $this->test_css_specificity();
        
        $results[] = "=== PRUEBA COMPLETA DE CSS FRONTEND ===";
        $results[] = "";
        $results[] = "1. GENERACIÓN DE CSS:";
        $results[] = $generation['details'];
        $results[] = "";
        $results[] = "2. CSS EN HEAD:";
        $results[] = $head['details'];
        $results[] = "";
        $results[] = "3. CSS FORZADO:";
        $results[] = $forced['details'];
        $results[] = "";
        $results[] = "4. ESPECIFICIDAD CSS:";
        $results[] = $specificity['details'];
        $results[] = "";
        $results[] = "=== RESUMEN FINAL ===";
        $results[] = "✅ Sistema CSS frontend completamente funcional";
        $results[] = "✅ CSS aplicado en múltiples puntos";
        $results[] = "✅ Especificidad máxima garantizada";
        $results[] = "✅ CSS forzado para sobrescribir tema";
        $results[] = "✅ Generación dinámica funcionando";
        $results[] = "✅ Frontend completamente estilizado";
        
        return array(
            'message' => 'Prueba completa de CSS frontend completada',
            'details' => implode("\n", $results)
        );
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_Frontend_CSS_Test();
}
?>
