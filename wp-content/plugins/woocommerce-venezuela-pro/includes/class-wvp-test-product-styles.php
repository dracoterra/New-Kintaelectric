<?php
/**
 * Prueba de Estilos en Productos - WooCommerce Venezuela Pro
 * Verifica que los estilos dinámicos se apliquen correctamente
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Test_Product_Styles {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wvp_test_product_styles', array($this, 'test_product_styles'));
        add_action('wp_ajax_nopriv_wvp_test_product_styles', array($this, 'test_product_styles'));
        
        // Agregar página de prueba de productos
        add_action('admin_menu', array($this, 'add_test_page'));
    }
    
    /**
     * Agregar página de prueba
     */
    public function add_test_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Prueba Estilos Productos',
            'Prueba Estilos Productos',
            'manage_woocommerce',
            'wvp-test-product-styles',
            array($this, 'display_test_page')
        );
    }
    
    /**
     * Mostrar página de prueba
     */
    public function display_test_page() {
        ?>
        <div class="wrap">
            <h1>Prueba de Estilos en Productos - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página permite probar que los estilos dinámicos se apliquen correctamente en los productos.</p>
            </div>
            
            <h2>Configuraciones Actuales</h2>
            <?php
            $settings = array(
                'display_style' => get_option('wvp_display_style', 'minimal'),
                'primary_color' => get_option('wvp_primary_color', '#007cba'),
                'secondary_color' => get_option('wvp_secondary_color', '#005a87'),
                'font_family' => get_option('wvp_font_family', 'system'),
                'font_size' => get_option('wvp_font_size', 'medium'),
                'padding' => get_option('wvp_padding', 'medium'),
                'margin' => get_option('wvp_margin', 'medium')
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
            
            <h2>Prueba de Elementos de Producto</h2>
            <div class="wvp-test-products">
                
                <h3>Producto Individual (Estilo: <?php echo esc_html($settings['display_style']); ?>)</h3>
                <div class="wvp-product-price-container wvp-<?php echo esc_attr($settings['display_style']); ?>" style="border: 2px dashed #007cba; padding: 20px; margin: 20px 0;">
                    <div class="wvp-price-display">
                        <span class="wvp-price-usd" style="display: block;">$25.00</span>
                        <span class="wvp-price-ves" style="display: none;">Bs. 3.943,25</span>
                    </div>
                    <div class="wvp-currency-switcher" data-price-usd="25.00" data-price-ves="3943.25">
                        <button class="wvp-currency-option active" data-currency="USD">USD</button>
                        <button class="wvp-currency-option" data-currency="VES">VES</button>
                    </div>
                    <div class="wvp-price-conversion">
                        <span class="wvp-ves-reference">Equivale a Bs. 3.943,25</span>
                    </div>
                    <div class="wvp-rate-info">Tasa BCV: 157,73</div>
                </div>
                
                <h3>Lista de Productos (Shop Loop)</h3>
                <div class="wvp-product-price-container wvp-<?php echo esc_attr($settings['display_style']); ?>" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                    <div class="wvp-price-display">
                        <span class="wvp-price-usd" style="display: block;">$15.00</span>
                        <span class="wvp-price-ves" style="display: none;">Bs. 2.365,93</span>
                    </div>
                    <div class="wvp-currency-switcher" data-price-usd="15.00" data-price-ves="2365.93">
                        <button class="wvp-currency-option active" data-currency="USD">USD</button>
                        <button class="wvp-currency-option" data-currency="VES">VES</button>
                    </div>
                </div>
                
                <h3>Carrito de Compras</h3>
                <div class="wvp-product-price-container wvp-<?php echo esc_attr($settings['display_style']); ?>" style="background: #f9f9f9; padding: 10px; margin: 10px 0;">
                    <div class="wvp-price-display">
                        <span class="wvp-price-usd" style="display: block;">$45.00</span>
                        <span class="wvp-price-ves" style="display: none;">Bs. 7.097,85</span>
                    </div>
                    <div class="wvp-price-conversion">
                        <span class="wvp-ves-reference">Equivale a Bs. 7.097,85</span>
                    </div>
                </div>
                
            </div>
            
            <h2>Prueba de Cambio de Estilo</h2>
            <p>Cambia el estilo en la sección de Apariencia y regresa aquí para ver los cambios:</p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=wvp-appearance'); ?>" class="button button-primary">
                    Ir a Configuración de Apariencia
                </a>
                <button type="button" id="wvp-refresh-test" class="button button-secondary">
                    Actualizar Vista Previa
                </button>
            </p>
            
            <h2>Prueba de Funcionalidad JavaScript</h2>
            <p>Prueba el cambio de moneda en los elementos de arriba:</p>
            <div id="wvp-js-test-results"></div>
            
            <script>
            jQuery(document).ready(function($) {
                // Probar funcionalidad de cambio de moneda
                $('.wvp-currency-switcher button').on('click', function() {
                    var $button = $(this);
                    var $container = $button.closest('.wvp-product-price-container');
                    var $usdPrice = $container.find('.wvp-price-usd');
                    var $vesPrice = $container.find('.wvp-price-ves');
                    var $conversion = $container.find('.wvp-price-conversion');
                    var $rateInfo = $container.find('.wvp-rate-info');
                    
                    // Actualizar botones
                    $container.find('.wvp-currency-option').removeClass('active');
                    $button.addClass('active');
                    
                    var currency = $button.data('currency');
                    
                    if (currency === 'USD') {
                        $vesPrice.fadeOut(200, function() {
                            $usdPrice.fadeIn(200);
                        });
                        $conversion.fadeIn(200);
                        $rateInfo.fadeIn(200);
                    } else if (currency === 'VES') {
                        $usdPrice.fadeOut(200, function() {
                            $vesPrice.fadeIn(200);
                        });
                        $conversion.fadeOut(200);
                        $rateInfo.fadeOut(200);
                    }
                    
                    // Mostrar resultado
                    $('#wvp-js-test-results').html('<div class="notice notice-success"><p>✅ Cambio de moneda funcionando correctamente: ' + currency + '</p></div>');
                });
                
                // Botón de actualizar
                $('#wvp-refresh-test').on('click', function() {
                    location.reload();
                });
            });
            </script>
            
            <h2>Verificación de CSS Dinámico</h2>
            <p>Verifica que el CSS dinámico se esté aplicando:</p>
            <div id="wvp-css-verification">
                <button type="button" id="wvp-check-css" class="button button-primary">
                    Verificar CSS Dinámico
                </button>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#wvp-check-css').on('click', function() {
                    // Verificar si hay CSS dinámico aplicado
                    var hasDynamicCSS = false;
                    var cssRules = '';
                    
                    // Buscar estilos aplicados
                    for (var i = 0; i < document.styleSheets.length; i++) {
                        try {
                            var sheet = document.styleSheets[i];
                            if (sheet.href && sheet.href.indexOf('wvp') !== -1) {
                                hasDynamicCSS = true;
                                cssRules += 'Hoja encontrada: ' + sheet.href + '\n';
                            }
                        } catch(e) {
                            // Ignorar errores de CORS
                        }
                    }
                    
                    // Verificar estilos inline
                    var inlineStyles = document.querySelectorAll('style[id*="wvp"]');
                    if (inlineStyles.length > 0) {
                        hasDynamicCSS = true;
                        cssRules += 'Estilos inline encontrados: ' + inlineStyles.length + '\n';
                    }
                    
                    // Verificar variables CSS
                    var rootStyles = getComputedStyle(document.documentElement);
                    var primaryColor = rootStyles.getPropertyValue('--wvp-primary-color');
                    
                    var result = '<div class="notice ' + (hasDynamicCSS ? 'notice-success' : 'notice-warning') + '">';
                    result += '<p><strong>Estado del CSS Dinámico:</strong> ' + (hasDynamicCSS ? '✅ Aplicado' : '⚠️ No detectado') + '</p>';
                    result += '<p><strong>Color Primario:</strong> ' + (primaryColor || 'No definido') + '</p>';
                    result += '<p><strong>Detalles:</strong></p>';
                    result += '<pre>' + cssRules + '</pre>';
                    result += '</div>';
                    
                    $('#wvp-css-verification').html(result);
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Probar estilos de productos via AJAX
     */
    public function test_product_styles() {
        // Verificar que el gestor de productos esté funcionando
        $product_manager_working = class_exists('WVP_Product_Display_Manager');
        $css_generator_working = class_exists('WVP_Dynamic_CSS_Generator');
        
        $results = array(
            'product_manager' => $product_manager_working,
            'css_generator' => $css_generator_working,
            'settings_applied' => get_option('wvp_display_style', 'minimal')
        );
        
        wp_send_json_success(array(
            'message' => 'Pruebas de estilos de productos completadas',
            'results' => $results
        ));
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_Test_Product_Styles();
}
?>
