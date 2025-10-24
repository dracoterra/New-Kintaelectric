<?php
/**
 * Prueba de Shortcodes - WooCommerce Venezuela Pro
 * Verifica que los shortcodes funcionen con estilos dinámicos
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Test_Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wvp_test_shortcodes', array($this, 'test_shortcodes'));
        add_action('wp_ajax_nopriv_wvp_test_shortcodes', array($this, 'test_shortcodes'));
        
        // Agregar página de prueba de shortcodes
        add_action('admin_menu', array($this, 'add_test_page'));
    }
    
    /**
     * Agregar página de prueba
     */
    public function add_test_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Prueba Shortcodes',
            'Prueba Shortcodes',
            'manage_woocommerce',
            'wvp-test-shortcodes',
            array($this, 'display_test_page')
        );
    }
    
    /**
     * Mostrar página de prueba
     */
    public function display_test_page() {
        ?>
        <div class="wrap">
            <h1>Prueba de Shortcodes - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página permite probar que los shortcodes funcionen correctamente con estilos dinámicos.</p>
            </div>
            
            <h2>Configuraciones Actuales</h2>
            <?php
            $settings = array(
                'display_style' => get_option('wvp_display_style', 'minimal'),
                'primary_color' => get_option('wvp_primary_color', '#007cba'),
                'secondary_color' => get_option('wvp_secondary_color', '#005a87'),
                'font_family' => get_option('wvp_font_family', 'system'),
                'font_size' => get_option('wvp_font_size', 'medium')
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
            
            <h2>Prueba de Shortcodes</h2>
            
            <h3>1. Shortcode de Tasa BCV</h3>
            <div class="wvp-shortcode-test" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                <h4>Código:</h4>
                <code>[wvp_bcv_rate format="simple" style="default"]</code>
                <h4>Resultado:</h4>
                <?php echo do_shortcode('[wvp_bcv_rate format="simple" style="default"]'); ?>
            </div>
            
            <div class="wvp-shortcode-test" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                <h4>Código:</h4>
                <code>[wvp_bcv_rate format="detailed" style="highlight"]</code>
                <h4>Resultado:</h4>
                <?php echo do_shortcode('[wvp_bcv_rate format="detailed" style="highlight"]'); ?>
            </div>
            
            <div class="wvp-shortcode-test" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                <h4>Código:</h4>
                <code>[wvp_bcv_rate format="inline" style="minimal"]</code>
                <h4>Resultado:</h4>
                <?php echo do_shortcode('[wvp_bcv_rate format="inline" style="minimal"]'); ?>
            </div>
            
            <h3>2. Shortcode de Selector de Moneda</h3>
            <div class="wvp-shortcode-test" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                <h4>Código:</h4>
                <code>[wvp_currency_switcher style="buttons" size="medium" theme="default" scope="local"]</code>
                <h4>Resultado:</h4>
                <?php echo do_shortcode('[wvp_currency_switcher style="buttons" size="medium" theme="default" scope="local"]'); ?>
            </div>
            
            <div class="wvp-shortcode-test" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                <h4>Código:</h4>
                <code>[wvp_currency_switcher style="dropdown" size="large" theme="primary" scope="global"]</code>
                <h4>Resultado:</h4>
                <?php echo do_shortcode('[wvp_currency_switcher style="dropdown" size="large" theme="primary" scope="global"]'); ?>
            </div>
            
            <h3>3. Shortcode de Conversión de Precio</h3>
            <div class="wvp-shortcode-test" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                <h4>Código:</h4>
                <code>[wvp_price_conversion price="25.00" format="both" show_rate="yes"]</code>
                <h4>Resultado:</h4>
                <?php echo do_shortcode('[wvp_price_conversion price="25.00" format="both" show_rate="yes"]'); ?>
            </div>
            
            <h2>Prueba de Estilos Dinámicos en Shortcodes</h2>
            <p>Los shortcodes de arriba deberían mostrar los estilos dinámicos aplicados según la configuración actual:</p>
            <ul>
                <li><strong>Estilo:</strong> <?php echo esc_html($settings['display_style']); ?></li>
                <li><strong>Color Primario:</strong> <?php echo esc_html($settings['primary_color']); ?></li>
                <li><strong>Color Secundario:</strong> <?php echo esc_html($settings['secondary_color']); ?></li>
                <li><strong>Fuente:</strong> <?php echo esc_html($settings['font_family']); ?></li>
                <li><strong>Tamaño:</strong> <?php echo esc_html($settings['font_size']); ?></li>
            </ul>
            
            <h2>Prueba de Funcionalidad JavaScript</h2>
            <p>Prueba la funcionalidad de cambio de moneda en los selectores de arriba:</p>
            <div id="wvp-shortcode-js-results"></div>
            
            <script>
            jQuery(document).ready(function($) {
                // Probar funcionalidad de cambio de moneda en shortcodes
                $('.wvp-currency-switcher button').on('click', function() {
                    var $button = $(this);
                    var $container = $button.closest('.wvp-currency-switcher');
                    var currency = $button.data('currency');
                    
                    // Actualizar botones
                    $container.find('.wvp-currency-option').removeClass('active');
                    $button.addClass('active');
                    
                    // Mostrar resultado
                    $('#wvp-shortcode-js-results').html('<div class="notice notice-success"><p>✅ Cambio de moneda en shortcode funcionando: ' + currency + '</p></div>');
                });
                
                // Probar funcionalidad de dropdown
                $('.wvp-currency-switcher select').on('change', function() {
                    var currency = $(this).val();
                    $('#wvp-shortcode-js-results').html('<div class="notice notice-success"><p>✅ Cambio de moneda en dropdown funcionando: ' + currency + '</p></div>');
                });
            });
            </script>
            
            <h2>Verificación de CSS Aplicado</h2>
            <p>Verifica que el CSS dinámico se esté aplicando a los shortcodes:</p>
            <div id="wvp-shortcode-css-verification">
                <button type="button" id="wvp-check-shortcode-css" class="button button-primary">
                    Verificar CSS en Shortcodes
                </button>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#wvp-check-shortcode-css').on('click', function() {
                    var results = [];
                    
                    // Verificar elementos de shortcode
                    $('.wvp-bcv-rate').each(function() {
                        var $element = $(this);
                        var computedStyle = window.getComputedStyle(this);
                        var color = computedStyle.color;
                        var fontFamily = computedStyle.fontFamily;
                        var fontSize = computedStyle.fontSize;
                        
                        results.push('Tasa BCV - Color: ' + color + ', Fuente: ' + fontFamily + ', Tamaño: ' + fontSize);
                    });
                    
                    $('.wvp-currency-switcher').each(function() {
                        var $element = $(this);
                        var computedStyle = window.getComputedStyle(this);
                        var backgroundColor = computedStyle.backgroundColor;
                        var borderColor = computedStyle.borderColor;
                        
                        results.push('Selector Moneda - Fondo: ' + backgroundColor + ', Borde: ' + borderColor);
                    });
                    
                    var resultHtml = '<div class="notice notice-info">';
                    resultHtml += '<h4>Estilos Aplicados a Shortcodes:</h4>';
                    resultHtml += '<ul>';
                    results.forEach(function(result) {
                        resultHtml += '<li>' + result + '</li>';
                    });
                    resultHtml += '</ul>';
                    resultHtml += '</div>';
                    
                    $('#wvp-shortcode-css-verification').html(resultHtml);
                });
            });
            </script>
            
            <h2>Prueba de Diferentes Contextos</h2>
            <p>Los shortcodes deberían funcionar en diferentes contextos:</p>
            
            <h3>En Widget</h3>
            <div class="widget-context" style="background: #f0f0f0; padding: 15px; margin: 10px 0;">
                <?php echo do_shortcode('[wvp_bcv_rate format="simple" style="default"]'); ?>
            </div>
            
            <h3>En Footer</h3>
            <div class="footer-context" style="background: #333; color: #fff; padding: 15px; margin: 10px 0;">
                <?php echo do_shortcode('[wvp_bcv_rate format="inline" style="minimal"]'); ?>
            </div>
            
            <h3>En Contenido</h3>
            <div class="content-context" style="border: 1px solid #ccc; padding: 15px; margin: 10px 0;">
                <p>Este es un párrafo de ejemplo con un shortcode: <?php echo do_shortcode('[wvp_bcv_rate format="inline" style="minimal"]'); ?> integrado en el texto.</p>
            </div>
            
        </div>
        <?php
    }
    
    /**
     * Probar shortcodes via AJAX
     */
    public function test_shortcodes() {
        // Verificar que los shortcodes estén registrados
        $shortcodes_registered = shortcode_exists('wvp_bcv_rate') && shortcode_exists('wvp_currency_switcher');
        
        // Probar ejecución de shortcodes
        $bcv_shortcode_result = do_shortcode('[wvp_bcv_rate format="simple" style="default"]');
        $switcher_shortcode_result = do_shortcode('[wvp_currency_switcher style="buttons" size="medium"]');
        
        $results = array(
            'shortcodes_registered' => $shortcodes_registered,
            'bcv_shortcode_working' => !empty($bcv_shortcode_result),
            'switcher_shortcode_working' => !empty($switcher_shortcode_result),
            'bcv_result' => $bcv_shortcode_result,
            'switcher_result' => $switcher_shortcode_result
        );
        
        wp_send_json_success(array(
            'message' => 'Pruebas de shortcodes completadas',
            'results' => $results
        ));
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_Test_Shortcodes();
}
?>
