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
        add_action('init', array($this, 'add_custom_capabilities'));
        add_action('wp_ajax_kintaelectric03_countdown', array($this, 'handle_countdown_ajax'));
        add_action('wp_ajax_nopriv_kintaelectric03_countdown', array($this, 'handle_countdown_ajax'));
        
        // Hooks para limpiar cache
        add_action('save_post', array($this, 'clear_cache_on_product_update'));
        add_action('delete_post', array($this, 'clear_cache_on_product_update'));
        add_action('created_product_cat', array($this, 'clear_cache_on_category_update'));
        add_action('edited_product_cat', array($this, 'clear_cache_on_category_update'));
        add_action('delete_product_cat', array($this, 'clear_cache_on_category_update'));
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
        // Cargar clase base primero
        require_once KEE_PLUGIN_PATH . 'includes/class-base-widget.php';
        
        // Cargar widgets
        require_once KEE_PLUGIN_PATH . 'widgets/home-slider-kintaelectic-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric02-deals-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric03-deals-and-tabs-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric04-products-tabs-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric05-dynamic-products-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric06-banner-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric07-recently-added-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric08-about-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric09-about-features-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric10-about-team-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric13-brands-carousel-widget.php';
        require_once KEE_PLUGIN_PATH . 'widgets/kintaelectric14-about-text-accordion-widget.php';
        
        // Debug files removed - system is now clean and optimized

        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Home_Slider_Kintaelectic_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric02_Deals_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric03_Deals_And_Tabs_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric04_Products_Tabs_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric05_Dynamic_Products_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric06_Banner_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric07_Recently_Added_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric08_About_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric09_About_Features_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric10_About_Team_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric13_Brands_Carousel_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register(new KEE_Kintaelectric14_About_Text_Accordion_Widget());
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
        // Slick Carousel CSS
        wp_enqueue_style(
            'slick-carousel',
            KEE_PLUGIN_URL . 'assets/css/slick.css',
            array(),
            '1.8.1'
        );
        
        // Slick Theme CSS
        wp_enqueue_style(
            'slick-theme',
            KEE_PLUGIN_URL . 'assets/css/slick-theme.css',
            array('slick-carousel'),
            '1.8.1'
        );
        
        // Animate.css
        wp_enqueue_style(
            'animate-css',
            KEE_PLUGIN_URL . 'assets/css/animate.min.css',
            array(),
            '3.7.2'
        );
        
        // CSS específico del home slider
        wp_enqueue_style(
            'kinta-home-slider-css',
            KEE_PLUGIN_URL . 'assets/css/home-slider.css',
            array('slick-theme', 'animate-css'),
            self::VERSION
        );
    }
    
    /**
     * Enqueue JavaScript files
     */
    private function enqueue_scripts_js() {
        // Slick Carousel JS
        wp_enqueue_script(
            'slick-carousel',
            KEE_PLUGIN_URL . 'assets/js/slick.min.js',
            array('jquery'),
            '1.8.1',
            true
        );
        
        // JavaScript específico del home slider
        wp_enqueue_script(
            'kinta-home-slider-js',
            KEE_PLUGIN_URL . 'assets/js/home-slider.js',
            array('jquery', 'slick-carousel'),
            self::VERSION,
            true
        );
    }
    
    /**
     * Add custom capabilities for plugin management
     */
    public function add_custom_capabilities() {
        $roles = ['administrator', 'editor', 'author'];
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->add_cap('manage_kinta_widgets');
                $role->add_cap('edit_kinta_widgets');
            }
        }
        
        // Rol específico para diseñadores de Kinta Electric
        add_role('kinta_designer', 'Kinta Designer', [
            'read' => true,
            'edit_kinta_widgets' => true,
            'manage_kinta_widgets' => false,
            'edit_posts' => true,
            'edit_pages' => true,
            'edit_published_posts' => true,
            'edit_published_pages' => true,
            'publish_posts' => true,
            'publish_pages' => true,
        ]);
    }
    
    /**
     * Handle countdown AJAX requests with security
     */
    public function handle_countdown_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'kintaelectric03_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('read')) {
            wp_die('Insufficient permissions');
        }
        
        $product_id = intval($_POST['product_id']);
        $end_time = sanitize_text_field($_POST['end_time']);
        
        // Validate and process countdown data
        $response = array(
            'success' => true,
            'product_id' => $product_id,
            'end_time' => $end_time,
            'current_time' => current_time('timestamp')
        );
        
        wp_send_json($response);
    }
    
    /**
     * Check if current user can manage widgets
     */
    public function can_manage_widgets() {
        return current_user_can('manage_kinta_widgets');
    }
    
    /**
     * Check if current user can edit widgets
     */
    public function can_edit_widgets() {
        return current_user_can('edit_kinta_widgets');
    }
    
    /**
     * Clear cache when products are updated
     */
    public function clear_cache_on_product_update($post_id) {
        if (get_post_type($post_id) === 'product') {
            KEE_Base_Widget::clear_cache();
        }
    }
    
    /**
     * Clear cache when categories are updated
     */
    public function clear_cache_on_category_update() {
        KEE_Base_Widget::clear_cache();
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