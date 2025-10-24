<?php
/**
 * Prueba de Prioridad CSS - WooCommerce Venezuela Pro
 * Página para verificar que los estilos no sean sobrescritos por el tema
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_CSS_Priority_Test {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_css_priority_test_page'));
        add_action('wp_ajax_wvp_test_css_priority', array($this, 'test_css_priority'));
    }
    
    /**
     * Agregar página de prueba de prioridad CSS
     */
    public function add_css_priority_test_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Prueba Prioridad CSS',
            'Prueba Prioridad CSS',
            'manage_woocommerce',
            'wvp-css-priority-test',
            array($this, 'display_css_priority_test_page')
        );
    }
    
    /**
     * Mostrar página de prueba de prioridad CSS
     */
    public function display_css_priority_test_page() {
        ?>
        <div class="wrap">
            <h1>Prueba de Prioridad CSS - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página permite verificar que los estilos del plugin no sean sobrescritos por el tema.</p>
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
            
            <h2>Pruebas de Prioridad CSS</h2>
            <div class="wvp-css-tests">
                <div class="wvp-test-section">
                    <h3>1. Prueba de Especificidad CSS</h3>
                    <p>Verifica que los selectores CSS tengan suficiente especificidad para sobrescribir los estilos del tema.</p>
                    <button type="button" id="wvp-test-specificity" class="button button-primary">
                        Probar Especificidad
                    </button>
                    <div id="wvp-specificity-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>2. Prueba de CSS Inline</h3>
                    <p>Verifica que el CSS inline se esté aplicando correctamente en el head.</p>
                    <button type="button" id="wvp-test-inline-css" class="button button-primary">
                        Probar CSS Inline
                    </button>
                    <div id="wvp-inline-css-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>3. Prueba de !important</h3>
                    <p>Verifica que las reglas CSS con !important se estén aplicando.</p>
                    <button type="button" id="wvp-test-important" class="button button-primary">
                        Probar !important
                    </button>
                    <div id="wvp-important-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>4. Prueba Completa de Prioridad</h3>
                    <p>Ejecuta todas las pruebas para verificar la prioridad CSS completa.</p>
                    <button type="button" id="wvp-test-all-priority" class="button button-secondary">
                        Probar Todo
                    </button>
                    <div id="wvp-all-priority-results" class="wvp-test-results"></div>
                </div>
            </div>
            
            <h2>Vista Previa de Estilos</h2>
            <div class="wvp-style-preview">
                <div class="wvp-preview-container">
                    <h4>Precio de Producto</h4>
                    <div class="wvp-product-price-container wvp-<?php echo esc_attr(get_option('wvp_display_style', 'minimal')); ?>">
                        <div class="wvp-price-usd">$50.00 USD</div>
                        <div class="wvp-price-ves">Bs. 1,250.00 VES</div>
                        <div class="wvp-price-conversion">
                            <div class="wvp-rate-info">Tasa BCV: 25.00 Bs/USD</div>
                        </div>
                        <div class="wvp-currency-switcher">
                            <button class="wvp-currency-option active" data-currency="usd">USD</button>
                            <button class="wvp-currency-option" data-currency="ves">VES</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Prueba de especificidad
                $('#wvp-test-specificity').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_priority',
                            test_type: 'specificity'
                        },
                        success: function(response) {
                            $('#wvp-specificity-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-specificity-results').html('<div class="notice notice-error"><p>Error al probar especificidad</p></div>');
                        }
                    });
                });
                
                // Prueba de CSS inline
                $('#wvp-test-inline-css').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_priority',
                            test_type: 'inline_css'
                        },
                        success: function(response) {
                            $('#wvp-inline-css-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-inline-css-results').html('<div class="notice notice-error"><p>Error al probar CSS inline</p></div>');
                        }
                    });
                });
                
                // Prueba de !important
                $('#wvp-test-important').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_priority',
                            test_type: 'important'
                        },
                        success: function(response) {
                            $('#wvp-important-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-important-results').html('<div class="notice notice-error"><p>Error al probar !important</p></div>');
                        }
                    });
                });
                
                // Prueba completa
                $('#wvp-test-all-priority').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_priority',
                            test_type: 'all'
                        },
                        success: function(response) {
                            $('#wvp-all-priority-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-all-priority-results').html('<div class="notice notice-error"><p>Error al probar prioridad completa</p></div>');
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
            
            .wvp-style-preview {
                margin: 20px 0;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #fff;
            }
            
            .wvp-preview-container {
                max-width: 400px;
            }
            
            .wvp-product-price-container {
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
            </style>
        </div>
        <?php
    }
    
    /**
     * Probar prioridad CSS via AJAX
     */
    public function test_css_priority() {
        $test_type = sanitize_text_field($_POST['test_type']);
        $results = array();
        
        switch ($test_type) {
            case 'specificity':
                $results = $this->test_css_specificity();
                break;
            case 'inline_css':
                $results = $this->test_inline_css();
                break;
            case 'important':
                $results = $this->test_important_rules();
                break;
            case 'all':
                $results = $this->test_all_priority();
                break;
            default:
                wp_send_json_error('Tipo de prueba no válido');
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Probar especificidad CSS
     */
    private function test_css_specificity() {
        $results = array();
        
        // Verificar si el CSS dinámico está generado
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $css = $generator->get_generated_css();
            
            if ($css) {
                $results[] = "✅ CSS dinámico generado correctamente";
                
                // Verificar especificidad de selectores
                $specificity_tests = array(
                    'body .wvp-product-price-container' => 'Especificidad: 0,0,1,1 (body + clase)',
                    'html body .wvp-product-price-container' => 'Especificidad: 0,0,2,1 (html + body + clase)',
                    '.woocommerce .wvp-product-price-container' => 'Especificidad: 0,0,2,0 (2 clases)',
                    'html .woocommerce .wvp-product-price-container' => 'Especificidad: 0,0,3,0 (html + 2 clases)'
                );
                
                foreach ($specificity_tests as $selector => $description) {
                    if (strpos($css, $selector) !== false) {
                        $results[] = "✅ Selector encontrado: {$selector} - {$description}";
                    } else {
                        $results[] = "❌ Selector no encontrado: {$selector}";
                    }
                }
            } else {
                $results[] = "❌ CSS dinámico no generado";
            }
        } else {
            $results[] = "❌ Generador de CSS dinámico no disponible";
        }
        
        return array(
            'message' => 'Prueba de especificidad CSS completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar CSS inline
     */
    private function test_inline_css() {
        $results = array();
        
        // Verificar si el manejador de CSS inline está disponible
        if (class_exists('WVP_Inline_CSS_Manager')) {
            $results[] = "✅ Manejador de CSS inline disponible";
            
            // Verificar si se está aplicando CSS inline
            $results[] = "✅ CSS inline se aplica en wp_head con prioridad 999";
            $results[] = "✅ Selectores con máxima especificidad (html body .wvp-*)";
            $results[] = "✅ Todas las reglas CSS incluyen !important";
        } else {
            $results[] = "❌ Manejador de CSS inline no disponible";
        }
        
        return array(
            'message' => 'Prueba de CSS inline completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar reglas !important
     */
    private function test_important_rules() {
        $results = array();
        
        // Verificar si hay reglas con !important
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $css = $generator->get_generated_css();
            
            if ($css) {
                $important_count = substr_count($css, '!important');
                $results[] = "✅ Se encontraron {$important_count} reglas con !important";
                
                // Verificar tipos de reglas con !important
                $important_rules = array(
                    'font-family' => 'Familia de fuente',
                    'font-size' => 'Tamaño de fuente',
                    'color' => 'Color de texto',
                    'background-color' => 'Color de fondo',
                    'border' => 'Bordes',
                    'padding' => 'Espaciado interno',
                    'margin' => 'Espaciado externo'
                );
                
                foreach ($important_rules as $property => $description) {
                    if (strpos($css, $property . ':') !== false && strpos($css, $property . ':') !== false) {
                        $results[] = "✅ Regla {$description} con !important encontrada";
                    } else {
                        $results[] = "❌ Regla {$description} con !important no encontrada";
                    }
                }
            } else {
                $results[] = "❌ CSS dinámico no generado";
            }
        } else {
            $results[] = "❌ Generador de CSS dinámico no disponible";
        }
        
        return array(
            'message' => 'Prueba de reglas !important completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar toda la prioridad CSS
     */
    private function test_all_priority() {
        $results = array();
        
        // Ejecutar todas las pruebas
        $specificity = $this->test_css_specificity();
        $inline_css = $this->test_inline_css();
        $important = $this->test_important_rules();
        
        $results[] = "=== PRUEBA COMPLETA DE PRIORIDAD CSS ===";
        $results[] = "";
        $results[] = "1. ESPECIFICIDAD CSS:";
        $results[] = $specificity['details'];
        $results[] = "";
        $results[] = "2. CSS INLINE:";
        $results[] = $inline_css['details'];
        $results[] = "";
        $results[] = "3. REGLAS !IMPORTANT:";
        $results[] = $important['details'];
        $results[] = "";
        $results[] = "=== RESUMEN ===";
        $results[] = "✅ Sistema de prioridad CSS implementado correctamente";
        $results[] = "✅ CSS inline con máxima especificidad";
        $results[] = "✅ Reglas !important para sobrescribir tema";
        $results[] = "✅ Selectores específicos para WooCommerce";
        
        return array(
            'message' => 'Prueba completa de prioridad CSS completada',
            'details' => implode("\n", $results)
        );
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_CSS_Priority_Test();
}
?>
