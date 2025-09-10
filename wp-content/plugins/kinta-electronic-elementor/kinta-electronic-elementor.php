<?php
/**
 * Plugin Name: KintaElectronic Elementor
 * Plugin URI: https://kinta-electric.com
 * Description: Clean plugin for Kinta Electric project. Widgets removed - ready for new development.
 * Version: 1.0.2
 * Author: Kinta Electric
 * Author URI: https://kinta-electric.com
 * Text Domain: kinta-electronic-elementor
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KEE_PLUGIN_FILE', __FILE__);
define('KEE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KEE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KEE_PLUGIN_VERSION', '1.0.2');

/**
 * Main KintaElectronic Elementor Plugin Class
 */
class KintaElectronicElementor {

    /**
     * Plugin version
     */
    const VERSION = '1.0.2';

    /**
     * Minimum WooCommerce version required
     */
    const MINIMUM_WOOCOMMERCE_VERSION = '8.0.0';

    /**
     * Constructor
     */
    public function __construct() {
        // Activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Elementor hooks (commented out - no widgets needed)
        // add_action('elementor/widgets/register', array($this, 'register_widgets'));
        // add_action('elementor/elements/categories_registered', array($this, 'add_widget_categories'));
        
        // Scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Shortcode removed - no widgets needed
    }

    /**
     * Activate plugin
     */
    public function activate() {
        // Check minimum requirements
        $this->check_requirements();
        
        // Create tables or options if needed
        flush_rewrite_rules();
    }

    /**
     * Deactivate plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check minimum requirements
        if (!$this->check_requirements()) {
            return;
        }

        // Load text domain
        load_plugin_textdomain('kinta-electronic-elementor', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Load development tools in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once KEE_PLUGIN_DIR . 'dev-tools.php';
        }
    }

    /**
     * Check minimum requirements
     */
    private function check_requirements() {
        // Check WooCommerce version if active
        if (class_exists('WooCommerce')) {
            if (!version_compare(WC()->version, self::MINIMUM_WOOCOMMERCE_VERSION, '>=')) {
                add_action('admin_notices', array($this, 'admin_notice_minimum_woocommerce_version'));
                return false;
            }
        }

        return true;
    }

    // Widget methods removed - no widgets needed for this project

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on frontend, not in admin
        if (is_admin()) {
            return;
        }

        // Main plugin CSS
        wp_enqueue_style(
            'kinta-electronic-elementor-style',
            KEE_PLUGIN_URL . 'assets/css/kinta-electronic-elementor.css',
            array(),
            self::VERSION
        );

        // Main plugin JavaScript
        wp_enqueue_script(
            'kinta-electronic-elementor-script',
            KEE_PLUGIN_URL . 'assets/js/kinta-electronic-elementor.js',
            array('jquery'),
            self::VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('kinta-electronic-elementor-script', 'kee_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kee_nonce'),
        ));
    }

    // Shortcode and HTML methods removed - no widgets needed

    // Elementor admin notices removed - no Elementor dependency

    /**
     * Admin notice for minimum WooCommerce version
     */
    public function admin_notice_minimum_woocommerce_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'kinta-electronic-elementor'),
            '<strong>' . esc_html__('KintaElectronic Elementor', 'kinta-electronic-elementor') . '</strong>',
            '<strong>' . esc_html__('WooCommerce', 'kinta-electronic-elementor') . '</strong>',
            self::MINIMUM_WOOCOMMERCE_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}

// Initialize the plugin
new KintaElectronicElementor();
