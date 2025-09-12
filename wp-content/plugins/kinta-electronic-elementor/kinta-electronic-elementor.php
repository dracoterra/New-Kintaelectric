<?php
/**
 * Plugin Name: Kinta Electric Elementor
 * Plugin URI: https://kintaelectric.com
 * Description: Widget de Elementor para el slider de Kinta Electric
 * Version: 1.0.0
 * Author: Kinta Electric
 * Text Domain: kinta-electric-elementor
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KEE_PLUGIN_FILE', __FILE__);
define('KEE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('KEE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KEE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class KintaElectricElementor {
    
    const VERSION = '1.0.0';
    
    /**
     * Single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_widget_categories'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('kinta-electric-elementor', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Register Elementor widgets
     */
    public function register_widgets() {
        require_once KEE_PLUGIN_PATH . 'widgets/home-slider-kintaelectic-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric02-deals-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric03-deals-and-tabs-widget.php';
        
        // Debug files removed - system is now clean and optimized

        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Home_Slider_Kintaelectic_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric02_Deals_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric03_Deals_And_Tabs_Widget());
    }
    
    /**
     * Add widget categories
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'kinta-electric',
            [
                'title' => __('Kinta Electric', 'kinta-electric-elementor'),
                'icon' => 'fa fa-bolt',
            ]
        );
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        $this->enqueue_styles();
        $this->enqueue_scripts_js();
    }
    
    /**
     * Enqueue CSS files
     */
    private function enqueue_styles() {
        // Animate.css
        wp_enqueue_style(
            'animate-css',
            'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
            array(),
            '4.1.1'
        );
        
        // Font Awesome
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
            array(),
            '6.0.0'
        );
        
        // Owl Carousel CSS
        wp_enqueue_style(
            'owl-carousel-css',
            'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css',
            array(),
            '2.3.4'
        );
        
        wp_enqueue_style(
            'owl-carousel-theme-css',
            'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css',
            array('owl-carousel-css'),
            '2.3.4'
        );
        
        // Main plugin CSS
        wp_enqueue_style(
            'kinta-electronic-elementor-style',
            KEE_PLUGIN_URL . 'assets/css/kinta-electronic-elementor.css',
            array('owl-carousel-css', 'animate-css', 'font-awesome'),
            self::VERSION
        );
    }
    
    /**
     * Enqueue JavaScript files
     */
    private function enqueue_scripts_js() {
        // Owl Carousel JS
        wp_enqueue_script(
            'owl-carousel-js',
            'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
            array('jquery'),
            '2.3.4',
            true
        );
        
        // Main plugin JS
        wp_enqueue_script(
            'kinta-electronic-elementor-script',
            KEE_PLUGIN_URL . 'assets/js/kinta-electronic-elementor.js',
            array('jquery', 'owl-carousel-js'),
            self::VERSION,
            true
        );
        
        // Countdown script and styles for deals and tabs widget
        wp_enqueue_style(
            'kintaelectric03-countdown',
            KEE_PLUGIN_URL . 'assets/css/kintaelectric03-countdown.css',
            array(),
            self::VERSION
        );
        
        wp_enqueue_script(
            'kintaelectric03-countdown',
            KEE_PLUGIN_URL . 'assets/js/kintaelectric03-countdown.js',
            array('jquery'),
            self::VERSION,
            true
        );
        
        // Localize countdown script
        wp_localize_script('kintaelectric03-countdown', 'kintaelectric03Countdown', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'texts' => array(
                'days' => __('Días', 'kinta-electric-elementor'),
                'hours' => __('Horas', 'kinta-electric-elementor'),
                'mins' => __('Min', 'kinta-electric-elementor'),
                'secs' => __('Seg', 'kinta-electric-elementor'),
                'expired' => __('¡Oferta Expirada!', 'kinta-electric-elementor')
            )
        ));
    }
}

/**
 * Initialize the plugin
 */
function kinta_electric_elementor() {
    return KintaElectricElementor::instance();
}

// Start the plugin
kinta_electric_elementor();