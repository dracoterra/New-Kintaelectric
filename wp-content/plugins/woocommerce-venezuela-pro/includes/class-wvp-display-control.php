<?php
/**
 * Control granular de visualización de precios y conversiones
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Display_Control {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init_hooks'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Inicializar hooks
     */
    public function init_hooks() {
        // Shortcodes
        add_shortcode('wvp_bcv_rate', array($this, 'bcv_rate_shortcode'));
        add_shortcode('wvp_currency_switcher', array($this, 'currency_switcher_shortcode'));
        
        // Filtros para controlar dónde se muestra
        add_filter('wvp_show_currency_conversion', array($this, 'should_show_conversion'), 10, 2);
        add_filter('wvp_show_bcv_rate', array($this, 'should_show_bcv_rate'), 10, 2);
        add_filter('wvp_show_currency_switcher', array($this, 'should_show_switcher'), 10, 2);
    }
    
    /**
     * Cargar scripts y estilos
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'wvp-display-control',
            WVP_PLUGIN_URL . 'assets/css/wvp-display-control.css',
            array(),
            WVP_VERSION
        );
        
        wp_enqueue_script(
            'wvp-display-control',
            WVP_PLUGIN_URL . 'assets/js/wvp-display-control.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        wp_localize_script('wvp-display-control', 'wvp_display_control', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_display_control_nonce'),
            'current_currency' => $this->get_current_currency(),
            'settings' => $this->get_display_settings()
        ));
    }
    
    /**
     * Shortcode para tasa BCV
     * 
     * @param array $atts Atributos del shortcode
     * @return string HTML del shortcode
     */
    public function bcv_rate_shortcode($atts) {
        $atts = shortcode_atts(array(
            'format' => 'simple', // simple, detailed, inline
            'show_label' => 'true',
            'show_date' => 'false',
            'style' => 'default' // default, minimal, highlight
        ), $atts);
        
        $bcv_rate = $this->get_bcv_rate();
        if (!$bcv_rate) {
            return '<span class="wvp-bcv-error">Tasa BCV no disponible</span>';
        }
        
        $output = '';
        $css_class = 'wvp-bcv-rate wvp-bcv-' . $atts['style'];
        
        switch ($atts['format']) {
            case 'simple':
                $output = $this->format_simple_rate($bcv_rate, $atts);
                break;
            case 'detailed':
                $output = $this->format_detailed_rate($bcv_rate, $atts);
                break;
            case 'inline':
                $output = $this->format_inline_rate($bcv_rate, $atts);
                break;
        }
        
        return '<span class="' . $css_class . '">' . $output . '</span>';
    }
    
    /**
     * Shortcode para selector de moneda
     * 
     * @param array $atts Atributos del shortcode
     * @return string HTML del shortcode
     */
    public function currency_switcher_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'buttons', // buttons, dropdown, toggle
            'show_labels' => 'true',
            'show_rates' => 'false',
            'size' => 'medium', // small, medium, large
            'theme' => 'default', // default, minimal, modern
            'scope' => 'global' // global, local
        ), $atts);
        
        $current_currency = $this->get_current_currency();
        $bcv_rate = $this->get_bcv_rate();
        
        if (!$bcv_rate) {
            return '<div class="wvp-currency-error">Tasa BCV no disponible</div>';
        }
        
        $output = '<div class="wvp-currency-switcher wvp-style-' . $atts['style'] . ' wvp-size-' . $atts['size'] . ' wvp-theme-' . $atts['theme'] . ' wvp-scope-' . $atts['scope'] . '">';
        
        switch ($atts['style']) {
            case 'buttons':
                $output .= $this->render_button_switcher($current_currency, $bcv_rate, $atts);
                break;
            case 'dropdown':
                $output .= $this->render_dropdown_switcher($current_currency, $bcv_rate, $atts);
                break;
            case 'toggle':
                $output .= $this->render_toggle_switcher($current_currency, $bcv_rate, $atts);
                break;
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Renderizar selector de botones
     */
    private function render_button_switcher($current_currency, $bcv_rate, $atts) {
        $output = '<div class="wvp-currency-buttons">';
        
        // Botón USD
        $usd_class = $current_currency === 'USD' ? 'active' : '';
        $output .= '<button type="button" class="wvp-currency-btn wvp-usd-btn ' . $usd_class . '" data-currency="USD">';
        if ($atts['show_labels'] === 'true') {
            $output .= '<span class="wvp-currency-label">USD</span>';
        }
        $output .= '<span class="wvp-currency-symbol">$</span>';
        $output .= '</button>';
        
        // Botón VES
        $ves_class = $current_currency === 'VES' ? 'active' : '';
        $output .= '<button type="button" class="wvp-currency-btn wvp-ves-btn ' . $ves_class . '" data-currency="VES">';
        if ($atts['show_labels'] === 'true') {
            $output .= '<span class="wvp-currency-label">VES</span>';
        }
        $output .= '<span class="wvp-currency-symbol">Bs.</span>';
        $output .= '</button>';
        
        $output .= '</div>';
        
        // Mostrar tasa si está habilitado
        if ($atts['show_rates'] === 'true') {
            $output .= '<div class="wvp-rate-display">';
            $output .= 'Tasa: ' . number_format($bcv_rate, 2, ',', '.') . ' Bs./USD';
            $output .= '</div>';
        }
        
        return $output;
    }
    
    /**
     * Renderizar selector dropdown
     */
    private function render_dropdown_switcher($current_currency, $bcv_rate, $atts) {
        $output = '<select class="wvp-currency-dropdown">';
        $output .= '<option value="USD"' . selected($current_currency, 'USD', false) . '>USD ($)</option>';
        $output .= '<option value="VES"' . selected($current_currency, 'VES', false) . '>VES (Bs.)</option>';
        $output .= '</select>';
        
        if ($atts['show_rates'] === 'true') {
            $output .= '<div class="wvp-rate-display">';
            $output .= 'Tasa: ' . number_format($bcv_rate, 2, ',', '.') . ' Bs./USD';
            $output .= '</div>';
        }
        
        return $output;
    }
    
    /**
     * Renderizar selector toggle
     */
    private function render_toggle_switcher($current_currency, $bcv_rate, $atts) {
        $output = '<div class="wvp-currency-toggle">';
        $output .= '<input type="checkbox" id="wvp-currency-toggle" class="wvp-toggle-input" ' . checked($current_currency, 'VES', false) . '>';
        $output .= '<label for="wvp-currency-toggle" class="wvp-toggle-label">';
        $output .= '<span class="wvp-toggle-slider"></span>';
        $output .= '<span class="wvp-toggle-text">USD / VES</span>';
        $output .= '</label>';
        $output .= '</div>';
        
        if ($atts['show_rates'] === 'true') {
            $output .= '<div class="wvp-rate-display">';
            $output .= 'Tasa: ' . number_format($bcv_rate, 2, ',', '.') . ' Bs./USD';
            $output .= '</div>';
        }
        
        return $output;
    }
    
    /**
     * Formatear tasa simple
     */
    private function format_simple_rate($rate, $atts) {
        $output = '';
        
        if ($atts['show_label'] === 'true') {
            $output .= 'Tasa BCV: ';
        }
        
        $output .= number_format($rate, 2, ',', '.') . ' Bs./USD';
        
        if ($atts['show_date'] === 'true') {
            $last_update = get_option('bcv_last_update', '');
            if (!empty($last_update)) {
                $output .= ' (' . date('d/m/Y', strtotime($last_update)) . ')';
            }
        }
        
        return $output;
    }
    
    /**
     * Formatear tasa detallada
     */
    private function format_detailed_rate($rate, $atts) {
        $output = '<div class="wvp-bcv-detailed">';
        
        if ($atts['show_label'] === 'true') {
            $output .= '<div class="wvp-bcv-title">Tasa de Cambio BCV</div>';
        }
        
        $output .= '<div class="wvp-bcv-rate-main">' . number_format($rate, 2, ',', '.') . ' Bs./USD</div>';
        
        if ($atts['show_date'] === 'true') {
            $last_update = get_option('bcv_last_update', '');
            if (!empty($last_update)) {
                $output .= '<div class="wvp-bcv-date">Actualizado: ' . date('d/m/Y H:i', strtotime($last_update)) . '</div>';
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Formatear tasa inline
     */
    private function format_inline_rate($rate, $atts) {
        $output = '';
        
        if ($atts['show_label'] === 'true') {
            $output .= '1 USD = ';
        }
        
        $output .= number_format($rate, 2, ',', '.') . ' Bs.';
        
        return $output;
    }
    
    /**
     * Obtener moneda actual
     */
    private function get_current_currency() {
        return isset($_COOKIE['wvp_currency']) ? $_COOKIE['wvp_currency'] : 'USD';
    }
    
    /**
     * Obtener tasa BCV
     */
    private function get_bcv_rate() {
        if (class_exists('BCV_Dolar_Tracker')) {
            $rate = BCV_Dolar_Tracker::get_rate();
            if ($rate && $rate > 0) {
                return $rate;
            }
        }
        
        $rate = get_option('wvp_bcv_rate', 0);
        return $rate > 0 ? $rate : false;
    }
    
    /**
     * Obtener configuraciones de visualización
     */
    private function get_display_settings() {
        // Usar las configuraciones centralizadas de WVP_Display_Settings
        if (class_exists('WVP_Display_Settings')) {
            return WVP_Display_Settings::get_settings();
        }
        
        // Fallback con configuraciones consistentes
        return get_option('wvp_display_settings', array(
            'currency_conversion' => array(
                'single_product' => true,
                'shop_loop' => true,
                'cart' => true,
                'checkout' => true,
                'widget' => false,  // DESHABILITADO en widgets
                'footer' => false   // DESHABILITADO en footer
            ),
            'bcv_rate' => array(
                'single_product' => false,
                'shop_loop' => false,
                'cart' => false,
                'checkout' => false,
                'widget' => false,  // DESHABILITADO en widgets
                'footer' => false   // DESHABILITADO en footer
            ),
            'currency_switcher' => array(
                'single_product' => true,
                'shop_loop' => true,
                'cart' => true,
                'checkout' => true,
                'widget' => false,  // DESHABILITADO en widgets
                'footer' => false   // DESHABILITADO en footer
            )
        ));
    }
    
    /**
     * Controlar si mostrar conversión
     */
    public function should_show_conversion($show, $context) {
        $settings = $this->get_display_settings();
        
        if (isset($settings['currency_conversion'][$context])) {
            return $settings['currency_conversion'][$context];
        }
        
        return $show;
    }
    
    /**
     * Controlar si mostrar tasa BCV
     */
    public function should_show_bcv_rate($show, $context) {
        $settings = $this->get_display_settings();
        
        if (isset($settings['bcv_rate'][$context])) {
            return $settings['bcv_rate'][$context];
        }
        
        return $show;
    }
    
    /**
     * Controlar si mostrar selector de moneda
     */
    public function should_show_switcher($show, $context) {
        $settings = $this->get_display_settings();
        
        if (isset($settings['currency_switcher'][$context])) {
            return $settings['currency_switcher'][$context];
        }
        
        return $show;
    }
}

// Inicializar la clase
new WVP_Display_Control();
?>
