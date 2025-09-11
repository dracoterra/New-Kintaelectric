<?php
/**
 * Clase para el switcher de moneda (USD ↔ VES)
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Currency_Switcher {
    
    private $plugin;
    private $current_currency;
    
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->current_currency = $this->get_user_preferred_currency();
        
        // Hooks para cargar scripts y estilos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'add_switcher_script'));
        
        // Hook para modificar precios
        add_filter('woocommerce_get_price_html', array($this, 'add_currency_switcher'), 10, 2);
        
        // AJAX para guardar preferencia
        add_action('wp_ajax_wvp_save_currency_preference', array($this, 'save_currency_preference'));
        add_action('wp_ajax_nopriv_wvp_save_currency_preference', array($this, 'save_currency_preference'));
        
        // Hook para mostrar switcher en páginas de productos
        add_action('woocommerce_single_product_summary', array($this, 'display_currency_switcher'), 25);
    }
    
    /**
     * Cargar scripts y estilos
     */
    public function enqueue_scripts() {
        // Solo cargar en páginas de productos y tienda
        if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag()) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'wvp-currency-switcher',
            WVP_PLUGIN_URL . 'assets/css/currency-switcher.css',
            array(),
            WVP_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'wvp-currency-switcher',
            WVP_PLUGIN_URL . 'assets/js/currency-switcher.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        // Localizar script
        wp_localize_script('wvp-currency-switcher', 'wvp_currency', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_currency_switcher'),
            'current_currency' => $this->current_currency,
            'strings' => array(
                'usd' => __('USD', 'wvp'),
                'ves' => __('VES', 'wvp'),
                'loading' => __('Cargando...', 'wvp'),
                'error' => __('Error al cambiar moneda', 'wvp')
            )
        ));
    }
    
    /**
     * Añadir switcher de moneda a los precios
     */
    public function add_currency_switcher($price_html, $product) {
        // Solo mostrar en frontend
        if (is_admin()) {
            return $price_html;
        }
        
        // Solo en páginas de productos y tienda
        if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag()) {
            return $price_html;
        }
        
        // Obtener precio del producto
        $price = $product->get_price();
        if (!$price) {
            return $price_html;
        }
        
        // Obtener tasa BCV
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate) {
            return $price_html;
        }
        
        // Generar HTML del switcher
        $switcher_html = $this->generate_currency_switcher_html($price, $rate);
        
        return $price_html . $switcher_html;
    }
    
    /**
     * Mostrar switcher de moneda en páginas de productos
     */
    public function display_currency_switcher() {
        if (is_admin()) {
            return;
        }
        
        // Obtener producto actual
        global $product;
        if (!$product) {
            return;
        }
        
        $price = $product->get_price();
        if (!$price) {
            return;
        }
        
        // Obtener tasa BCV
        $rate = WVP_BCV_Integrator::get_rate();
        if (!$rate) {
            return;
        }
        
        // Generar HTML del switcher
        $switcher_html = $this->generate_currency_switcher_html($price, $rate);
        
        echo '<div class="wvp-currency-switcher-container">';
        echo $switcher_html;
        echo '</div>';
    }
    
    /**
     * Generar HTML del switcher de moneda
     */
    private function generate_currency_switcher_html($price, $rate) {
        $ves_price = WVP_BCV_Integrator::convert_to_ves($price, $rate);
        $formatted_ves = WVP_BCV_Integrator::format_ves_price($ves_price);
        
        ob_start();
        ?>
        <div class="wvp-currency-switcher" data-price-usd="<?php echo esc_attr($price); ?>" data-price-ves="<?php echo esc_attr($ves_price); ?>" data-rate="<?php echo esc_attr($rate); ?>">
            <div class="wvp-currency-options">
                <label class="wvp-currency-option <?php echo $this->current_currency === 'USD' ? 'active' : ''; ?>">
                    <input type="radio" name="wvp_currency" value="USD" <?php checked($this->current_currency, 'USD'); ?>>
                    <span class="wvp-currency-label">USD</span>
                    <span class="wvp-currency-price"><?php echo wc_price($price); ?></span>
                </label>
                <label class="wvp-currency-option <?php echo $this->current_currency === 'VES' ? 'active' : ''; ?>">
                    <input type="radio" name="wvp_currency" value="VES" <?php checked($this->current_currency, 'VES'); ?>>
                    <span class="wvp-currency-label">VES</span>
                    <span class="wvp-currency-price"><?php echo $formatted_ves; ?></span>
                </label>
            </div>
            <div class="wvp-currency-info">
                <small class="wvp-rate-info">
                    <?php printf(__('Tasa BCV: %s Bs./USD', 'wvp'), number_format($rate, 2, ',', '.')); ?>
                </small>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Obtener moneda preferida del usuario
     */
    private function get_user_preferred_currency() {
        // Verificar cookie
        if (isset($_COOKIE['wvp_currency'])) {
            return sanitize_text_field($_COOKIE['wvp_currency']);
        }
        
        // Verificar sesión
        if (isset($_SESSION['wvp_currency'])) {
            return sanitize_text_field($_SESSION['wvp_currency']);
        }
        
        // Verificar configuración del plugin
        $default_currency = get_option('wvp_default_currency', 'USD');
        return $default_currency;
    }
    
    /**
     * Guardar preferencia de moneda
     */
    public function save_currency_preference() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_currency_switcher')) {
            wp_die('Error de seguridad');
        }
        
        $currency = sanitize_text_field($_POST['currency']);
        
        if (!in_array($currency, ['USD', 'VES'])) {
            wp_die('Moneda inválida');
        }
        
        // Guardar en cookie
        setcookie('wvp_currency', $currency, time() + (30 * 24 * 60 * 60), '/');
        
        // Guardar en sesión
        if (!session_id()) {
            session_start();
        }
        $_SESSION['wvp_currency'] = $currency;
        
        // Respuesta
        wp_send_json_success(array(
            'currency' => $currency,
            'message' => __('Preferencia de moneda guardada', 'wvp')
        ));
    }
    
    /**
     * Añadir script JavaScript al footer
     */
    public function add_switcher_script() {
        if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag()) {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Manejar cambio de moneda
            $('.wvp-currency-switcher input[name="wvp_currency"]').on('change', function() {
                var currency = $(this).val();
                var switcher = $(this).closest('.wvp-currency-switcher');
                
                // Mostrar loading
                switcher.addClass('loading');
                
                // Guardar preferencia
                $.ajax({
                    url: wvp_currency.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wvp_save_currency_preference',
                        currency: currency,
                        nonce: wvp_currency.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Actualizar display
                            switcher.removeClass('loading');
                            switcher.find('.wvp-currency-option').removeClass('active');
                            switcher.find('input[value="' + currency + '"]').closest('.wvp-currency-option').addClass('active');
                            
                            // Recargar página para aplicar cambios
                            location.reload();
                        } else {
                            alert(wvp_currency.strings.error);
                        }
                    },
                    error: function() {
                        alert(wvp_currency.strings.error);
                        switcher.removeClass('loading');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
