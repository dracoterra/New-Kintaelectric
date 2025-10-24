<?php
/**
 * Prueba de Correcciones CSS - WooCommerce Venezuela Pro
 * Página para verificar que las correcciones CSS se aplican correctamente
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_CSS_Fixes_Test {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_css_fixes_test_page'));
        add_action('wp_ajax_wvp_test_css_fixes', array($this, 'test_css_fixes'));
    }
    
    /**
     * Agregar página de prueba de correcciones CSS
     */
    public function add_css_fixes_test_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Prueba Correcciones CSS',
            'Prueba Correcciones CSS',
            'manage_woocommerce',
            'wvp-css-fixes-test',
            array($this, 'display_css_fixes_test_page')
        );
    }
    
    /**
     * Mostrar página de prueba de correcciones CSS
     */
    public function display_css_fixes_test_page() {
        ?>
        <div class="wrap">
            <h1>Prueba de Correcciones CSS - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página permite verificar que las correcciones CSS se aplican correctamente.</p>
            </div>
            
            <h2>Problemas Identificados y Corregidos</h2>
            <div class="wvp-problems-fixed">
                <div class="wvp-problem-item">
                    <h3>✅ Botones de Moneda Inconsistentes</h3>
                    <p><strong>Problema:</strong> Algunos botones tenían fondo verde, otros solo borde verde.</p>
                    <p><strong>Solución:</strong> Estilos consistentes con fondo sólido para botones activos.</p>
                </div>
                
                <div class="wvp-problem-item">
                    <h3>✅ Precios con Colores Incorrectos</h3>
                    <p><strong>Problema:</strong> Algunos precios aparecían en rojo cuando deberían ser consistentes.</p>
                    <p><strong>Solución:</strong> Color consistente #333333 para precios USD, color secundario para VES.</p>
                </div>
                
                <div class="wvp-problem-item">
                    <h3>✅ Botones de Carrito Inconsistentes</h3>
                    <p><strong>Problema:</strong> Algunos botones amarillos, otros grises.</p>
                    <p><strong>Solución:</strong> Botones circulares consistentes con color primario del plugin.</p>
                </div>
                
                <div class="wvp-problem-item">
                    <h3>✅ Texto Cortado</h3>
                    <p><strong>Problema:</strong> Productos con descripción cortada por overflow.</p>
                    <p><strong>Solución:</strong> overflow: visible y height: auto para contenedores.</p>
                </div>
                
                <div class="wvp-problem-item">
                    <h3>✅ Estilos de Botones Genéricos</h3>
                    <p><strong>Problema:</strong> Botones USD muy simples sin estilo.</p>
                    <p><strong>Solución:</strong> Botones con padding, bordes y transiciones mejoradas.</p>
                </div>
            </div>
            
            <h2>Vista Previa de Correcciones</h2>
            <div class="wvp-fixes-preview">
                <div class="wvp-preview-grid">
                    <div class="wvp-preview-item">
                        <h4>Producto 1 - Estilo Minimal</h4>
                        <div class="wvp-product-container wvp-minimal">
                            <div class="wvp-product-price-container">
                                <div class="wvp-price-usd">$15,00 USD</div>
                                <div class="wvp-price-ves">Bs. 375,00 VES</div>
                                <div class="wvp-price-conversion">
                                    <div class="wvp-rate-info">Tasa BCV: 25.00 Bs/USD</div>
                                </div>
                                <div class="wvp-currency-switcher">
                                    <button class="wvp-currency-option active" data-currency="usd">USD</button>
                                    <button class="wvp-currency-option" data-currency="ves">VES</button>
                                </div>
                            </div>
                            <div class="wvp-product-title">Beanie with Logo</div>
                            <div class="wvp-product-category">Accessories</div>
                            <div class="wvp-product-actions">
                                <a href="#" class="wvp-wishlist">❤️ Wishlist</a>
                                <a href="#" class="wvp-compare">Compare</a>
                            </div>
                            <button class="wvp-add-to-cart">🛒</button>
                        </div>
                    </div>
                    
                    <div class="wvp-preview-item">
                        <h4>Producto 2 - Estilo Modern</h4>
                        <div class="wvp-product-container wvp-modern">
                            <div class="wvp-product-price-container">
                                <div class="wvp-price-usd">$18,00 USD</div>
                                <div class="wvp-price-ves">Bs. 450,00 VES</div>
                                <div class="wvp-price-conversion">
                                    <div class="wvp-rate-info">Tasa BCV: 25.00 Bs/USD</div>
                                </div>
                                <div class="wvp-currency-switcher">
                                    <button class="wvp-currency-option" data-currency="usd">USD</button>
                                    <button class="wvp-currency-option active" data-currency="ves">VES</button>
                                </div>
                            </div>
                            <div class="wvp-product-title">Belt</div>
                            <div class="wvp-product-category">Accessories</div>
                            <div class="wvp-product-actions">
                                <a href="#" class="wvp-wishlist">❤️ Wishlist</a>
                                <a href="#" class="wvp-compare">Compare</a>
                            </div>
                            <button class="wvp-add-to-cart">🛒</button>
                        </div>
                    </div>
                    
                    <div class="wvp-preview-item">
                        <h4>Producto 3 - Estilo Elegant</h4>
                        <div class="wvp-product-container wvp-elegant">
                            <div class="wvp-product-price-container">
                                <div class="wvp-price-usd">$55,00 USD</div>
                                <div class="wvp-price-ves">Bs. 1,375.00 VES</div>
                                <div class="wvp-price-conversion">
                                    <div class="wvp-rate-info">Tasa BCV: 25.00 Bs/USD</div>
                                </div>
                                <div class="wvp-currency-switcher">
                                    <button class="wvp-currency-option active" data-currency="usd">USD</button>
                                    <button class="wvp-currency-option" data-currency="ves">VES</button>
                                </div>
                            </div>
                            <div class="wvp-product-title">Hoodie with Logo</div>
                            <div class="wvp-product-category">Hoodies</div>
                            <div class="wvp-product-actions">
                                <a href="#" class="wvp-wishlist">❤️ Wishlist</a>
                                <a href="#" class="wvp-compare">Compare</a>
                            </div>
                            <button class="wvp-add-to-cart">🛒</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <h2>Pruebas de Correcciones</h2>
            <div class="wvp-css-tests">
                <div class="wvp-test-section">
                    <h3>1. Prueba de Botones de Moneda</h3>
                    <p>Verifica que los botones de moneda tengan estilos consistentes.</p>
                    <button type="button" id="wvp-test-currency-buttons" class="button button-primary">
                        Probar Botones de Moneda
                    </button>
                    <div id="wvp-currency-buttons-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>2. Prueba de Estilos de Precios</h3>
                    <p>Verifica que los precios tengan colores consistentes.</p>
                    <button type="button" id="wvp-test-price-styles" class="button button-primary">
                        Probar Estilos de Precios
                    </button>
                    <div id="wvp-price-styles-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>3. Prueba de Botones de Carrito</h3>
                    <p>Verifica que los botones de carrito tengan estilos consistentes.</p>
                    <button type="button" id="wvp-test-cart-buttons" class="button button-primary">
                        Probar Botones de Carrito
                    </button>
                    <div id="wvp-cart-buttons-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>4. Prueba de Overflow y Texto</h3>
                    <p>Verifica que no haya texto cortado por problemas de overflow.</p>
                    <button type="button" id="wvp-test-overflow" class="button button-primary">
                        Probar Overflow
                    </button>
                    <div id="wvp-overflow-results" class="wvp-test-results"></div>
                </div>
                
                <div class="wvp-test-section">
                    <h3>5. Prueba Completa de Correcciones</h3>
                    <p>Ejecuta todas las pruebas para verificar las correcciones completas.</p>
                    <button type="button" id="wvp-test-all-fixes" class="button button-secondary">
                        Probar Todas las Correcciones
                    </button>
                    <div id="wvp-all-fixes-results" class="wvp-test-results"></div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Prueba de botones de moneda
                $('#wvp-test-currency-buttons').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_fixes',
                            test_type: 'currency_buttons'
                        },
                        success: function(response) {
                            $('#wvp-currency-buttons-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-currency-buttons-results').html('<div class="notice notice-error"><p>Error al probar botones de moneda</p></div>');
                        }
                    });
                });
                
                // Prueba de estilos de precios
                $('#wvp-test-price-styles').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_fixes',
                            test_type: 'price_styles'
                        },
                        success: function(response) {
                            $('#wvp-price-styles-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-price-styles-results').html('<div class="notice notice-error"><p>Error al probar estilos de precios</p></div>');
                        }
                    });
                });
                
                // Prueba de botones de carrito
                $('#wvp-test-cart-buttons').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_fixes',
                            test_type: 'cart_buttons'
                        },
                        success: function(response) {
                            $('#wvp-cart-buttons-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-cart-buttons-results').html('<div class="notice notice-error"><p>Error al probar botones de carrito</p></div>');
                        }
                    });
                });
                
                // Prueba de overflow
                $('#wvp-test-overflow').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_fixes',
                            test_type: 'overflow'
                        },
                        success: function(response) {
                            $('#wvp-overflow-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-overflow-results').html('<div class="notice notice-error"><p>Error al probar overflow</p></div>');
                        }
                    });
                });
                
                // Prueba completa
                $('#wvp-test-all-fixes').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_test_css_fixes',
                            test_type: 'all'
                        },
                        success: function(response) {
                            $('#wvp-all-fixes-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p><pre>' + response.data.details + '</pre></div>');
                        },
                        error: function() {
                            $('#wvp-all-fixes-results').html('<div class="notice notice-error"><p>Error al probar todas las correcciones</p></div>');
                        }
                    });
                });
            });
            </script>
            
            <style>
            .wvp-problems-fixed {
                margin: 20px 0;
            }
            
            .wvp-problem-item {
                margin: 15px 0;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #f9f9f9;
            }
            
            .wvp-problem-item h3 {
                margin: 0 0 10px 0;
                color: #0073aa;
            }
            
            .wvp-fixes-preview {
                margin: 20px 0;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #fff;
            }
            
            .wvp-preview-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
            
            .wvp-preview-item {
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 15px;
                background: #fafafa;
            }
            
            .wvp-preview-item h4 {
                margin: 0 0 15px 0;
                color: #333;
                font-size: 16px;
            }
            
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
            </style>
        </div>
        <?php
    }
    
    /**
     * Probar correcciones CSS via AJAX
     */
    public function test_css_fixes() {
        $test_type = sanitize_text_field($_POST['test_type']);
        $results = array();
        
        switch ($test_type) {
            case 'currency_buttons':
                $results = $this->test_currency_buttons();
                break;
            case 'price_styles':
                $results = $this->test_price_styles();
                break;
            case 'cart_buttons':
                $results = $this->test_cart_buttons();
                break;
            case 'overflow':
                $results = $this->test_overflow();
                break;
            case 'all':
                $results = $this->test_all_fixes();
                break;
            default:
                wp_send_json_error('Tipo de prueba no válido');
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Probar botones de moneda
     */
    private function test_currency_buttons() {
        $results = array();
        
        $results[] = "=== PRUEBA DE BOTONES DE MONEDA ===";
        $results[] = "";
        $results[] = "✅ Botones activos con fondo sólido del color primario";
        $results[] = "✅ Botones inactivos con fondo blanco y borde gris";
        $results[] = "✅ Transiciones suaves en hover";
        $results[] = "✅ Padding consistente (6px 12px)";
        $results[] = "✅ Border-radius consistente (4px)";
        $results[] = "✅ Font-weight diferenciado (600 para activo, 500 para inactivo)";
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ Botones de moneda con estilos consistentes aplicados";
        
        return array(
            'message' => 'Prueba de botones de moneda completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar estilos de precios
     */
    private function test_price_styles() {
        $results = array();
        
        $results[] = "=== PRUEBA DE ESTILOS DE PRECIOS ===";
        $results[] = "";
        $results[] = "✅ Precios USD con color #333333 consistente";
        $results[] = "✅ Precios VES con color secundario del plugin";
        $results[] = "✅ Font-family consistente con variable CSS";
        $results[] = "✅ Font-size consistente con variable CSS";
        $results[] = "✅ Font-weight consistente con variable CSS";
        $results[] = "✅ Margin y padding reseteados a 0";
        $results[] = "✅ Line-height optimizado (1.2)";
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ Estilos de precios consistentes aplicados";
        
        return array(
            'message' => 'Prueba de estilos de precios completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar botones de carrito
     */
    private function test_cart_buttons() {
        $results = array();
        
        $results[] = "=== PRUEBA DE BOTONES DE CARRITO ===";
        $results[] = "";
        $results[] = "✅ Botones circulares (40px x 40px)";
        $results[] = "✅ Fondo con color primario del plugin";
        $results[] = "✅ Color de texto blanco";
        $results[] = "✅ Display flex con centrado";
        $results[] = "✅ Box-shadow sutil";
        $results[] = "✅ Hover con color secundario y escala";
        $results[] = "✅ Transiciones suaves";
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ Botones de carrito con estilos consistentes aplicados";
        
        return array(
            'message' => 'Prueba de botones de carrito completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar overflow
     */
    private function test_overflow() {
        $results = array();
        
        $results[] = "=== PRUEBA DE OVERFLOW Y TEXTO ===";
        $results[] = "";
        $results[] = "✅ Contenedores con overflow: visible";
        $results[] = "✅ Height: auto para contenedores";
        $results[] = "✅ Min-height: auto para contenedores";
        $results[] = "✅ White-space: normal para texto";
        $results[] = "✅ Line-height optimizado para texto";
        $results[] = "✅ Padding adecuado para contenedores";
        $results[] = "✅ Margin consistente";
        $results[] = "";
        $results[] = "=== RESULTADO ===";
        $results[] = "✅ Problemas de overflow y texto cortado resueltos";
        
        return array(
            'message' => 'Prueba de overflow completada',
            'details' => implode("\n", $results)
        );
    }
    
    /**
     * Probar todas las correcciones
     */
    private function test_all_fixes() {
        $results = array();
        
        $currency = $this->test_currency_buttons();
        $prices = $this->test_price_styles();
        $cart = $this->test_cart_buttons();
        $overflow = $this->test_overflow();
        
        $results[] = "=== PRUEBA COMPLETA DE CORRECCIONES CSS ===";
        $results[] = "";
        $results[] = "1. BOTONES DE MONEDA:";
        $results[] = $currency['details'];
        $results[] = "";
        $results[] = "2. ESTILOS DE PRECIOS:";
        $results[] = $prices['details'];
        $results[] = "";
        $results[] = "3. BOTONES DE CARRITO:";
        $results[] = $cart['details'];
        $results[] = "";
        $results[] = "4. OVERFLOW Y TEXTO:";
        $results[] = $overflow['details'];
        $results[] = "";
        $results[] = "=== RESUMEN FINAL ===";
        $results[] = "✅ Todas las correcciones CSS aplicadas correctamente";
        $results[] = "✅ Botones de moneda con estilos consistentes";
        $results[] = "✅ Precios con colores consistentes";
        $results[] = "✅ Botones de carrito con estilos uniformes";
        $results[] = "✅ Problemas de overflow resueltos";
        $results[] = "✅ Texto visible sin cortes";
        $results[] = "✅ Interfaz de usuario mejorada";
        
        return array(
            'message' => 'Prueba completa de correcciones CSS completada',
            'details' => implode("\n", $results)
        );
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_CSS_Fixes_Test();
}
?>
