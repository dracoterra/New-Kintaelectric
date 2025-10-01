<?php
/**
 * Public - Funcionalidades del frontend
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para funcionalidades del frontend
 */
class WVS_Public {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Suite
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Suite::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Solo en frontend
        if (is_admin()) {
            return;
        }
        
        // Encolar scripts y estilos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Hooks de WooCommerce
        add_action('woocommerce_init', array($this, 'init_woocommerce'));
        
        // Shortcodes
        add_shortcode('wvs_currency_converter', array($this, 'currency_converter_shortcode'));
        add_shortcode('wvs_bcv_rate', array($this, 'bcv_rate_shortcode'));
    }
    
    /**
     * Encolar scripts del frontend
     */
    public function enqueue_scripts() {
        // CSS del frontend
        wp_enqueue_style(
            'wvs-frontend',
            WVS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WVS_VERSION
        );
        
        // JavaScript del frontend
        wp_enqueue_script(
            'wvs-frontend',
            WVS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WVS_VERSION,
            true
        );
        
        // Localizar script
        wp_localize_script('wvs-frontend', 'wvs_frontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvs_frontend_nonce'),
            'currency_display' => $this->plugin->core_engine->get_config('currency_display', 'dual'),
            'strings' => array(
                'loading' => __('Cargando...', 'wvs'),
                'error' => __('Error al cargar', 'wvs'),
                'usd' => __('USD', 'wvs'),
                'ves' => __('VES', 'wvs')
            )
        ));
    }
    
    /**
     * Inicializar WooCommerce
     */
    public function init_woocommerce() {
        // Verificar que WooCommerce esté disponible
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Inicializar funcionalidades específicas del frontend
        $this->init_frontend_features();
    }
    
    /**
     * Inicializar funcionalidades del frontend
     */
    private function init_frontend_features() {
        // Solo si el módulo de moneda está activo
        $active_modules = get_option('wvs_activated_modules', array());
        if (!in_array('currency', $active_modules)) {
            return;
        }
        
        // Hooks específicos del frontend
        add_filter('woocommerce_get_price_html', array($this, 'modify_price_display'), 10, 2);
        add_action('woocommerce_single_product_summary', array($this, 'display_currency_info'), 25);
    }
    
    /**
     * Modificar visualización de precios
     * 
     * @param string $price_html
     * @param WC_Product $product
     * @return string
     */
    public function modify_price_display($price_html, $product) {
        // Obtener precio del producto
        $price = $product->get_price();
        if (!$price || $price <= 0) {
            return $price_html;
        }
        
        // Obtener tasa BCV
        $rate = $this->plugin->core_engine->get_bcv_rate();
        if (!$rate || $rate <= 0) {
            return $price_html;
        }
        
        // Calcular precio en VES
        $ves_price = $this->plugin->core_engine->convert_usd_to_ves($price, $rate);
        
        // Formatear precios
        $formatted_usd = wc_price($price);
        $formatted_ves = number_format($ves_price, 2, ',', '.') . ' Bs.';
        
        // Generar HTML según configuración
        $currency_display = $this->plugin->core_engine->get_config('currency_display', 'dual');
        
        switch ($currency_display) {
            case 'usd_only':
                return $formatted_usd;
                
            case 'ves_only':
                return $formatted_ves;
                
            case 'dual':
            default:
                return $formatted_usd . '<br><small class="wvs-ves-price">(' . $formatted_ves . ')</small>';
        }
    }
    
    /**
     * Mostrar información de moneda en producto individual
     */
    public function display_currency_info() {
        $rate = $this->plugin->core_engine->get_bcv_rate();
        if (!$rate || $rate <= 0) {
            return;
        }
        
        echo '<div class="wvs-currency-info">';
        echo '<p><strong>' . __('Tasa BCV:', 'wvs') . '</strong> ' . number_format($rate, 2, ',', '.') . ' Bs./USD</p>';
        echo '</div>';
    }
    
    /**
     * Shortcode para convertidor de moneda
     * 
     * @param array $atts
     * @return string
     */
    public function currency_converter_shortcode($atts) {
        $atts = shortcode_atts(array(
            'from' => 'USD',
            'to' => 'VES',
            'amount' => '1',
            'show_input' => 'true'
        ), $atts);
        
        $rate = $this->plugin->core_engine->get_bcv_rate();
        if (!$rate) {
            return '<p>' . __('Tasa de cambio no disponible', 'wvs') . '</p>';
        }
        
        $amount = floatval($atts['amount']);
        $converted = $this->plugin->core_engine->convert_usd_to_ves($amount, $rate);
        
        ob_start();
        ?>
        <div class="wvs-currency-converter">
            <?php if ($atts['show_input'] === 'true'): ?>
                <div class="wvs-converter-input">
                    <input type="number" id="wvs-amount" value="<?php echo esc_attr($amount); ?>" step="0.01" min="0">
                    <select id="wvs-from-currency">
                        <option value="USD" <?php selected($atts['from'], 'USD'); ?>>USD</option>
                        <option value="VES" <?php selected($atts['from'], 'VES'); ?>>VES</option>
                    </select>
                    <span class="wvs-converter-arrow">→</span>
                    <input type="text" id="wvs-result" readonly>
                    <select id="wvs-to-currency">
                        <option value="USD" <?php selected($atts['to'], 'USD'); ?>>USD</option>
                        <option value="VES" <?php selected($atts['to'], 'VES'); ?>>VES</option>
                    </select>
                </div>
            <?php else: ?>
                <div class="wvs-converter-result">
                    <p><?php echo esc_html($amount); ?> <?php echo esc_html($atts['from']); ?> = 
                       <strong><?php echo number_format($converted, 2, ',', '.'); ?> <?php echo esc_html($atts['to']); ?></strong></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode para mostrar tasa BCV
     * 
     * @param array $atts
     * @return string
     */
    public function bcv_rate_shortcode($atts) {
        $atts = shortcode_atts(array(
            'format' => 'full',
            'show_date' => 'true'
        ), $atts);
        
        $rate = $this->plugin->core_engine->get_bcv_rate();
        if (!$rate) {
            return '<p>' . __('Tasa BCV no disponible', 'wvs') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="wvs-bcv-rate">
            <?php if ($atts['format'] === 'full'): ?>
                <p><strong><?php _e('Tasa BCV:', 'wvs'); ?></strong> <?php echo number_format($rate, 2, ',', '.'); ?> Bs./USD</p>
            <?php else: ?>
                <span><?php echo number_format($rate, 2, ',', '.'); ?> Bs./USD</span>
            <?php endif; ?>
            
            <?php if ($atts['show_date'] === 'true'): ?>
                <small><?php _e('Actualizada:', 'wvs'); ?> <?php echo current_time('d/m/Y H:i'); ?></small>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
