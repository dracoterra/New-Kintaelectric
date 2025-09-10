<?php
/**
 * Plugin Name: KintaElectronic Elementor
 * Plugin URI: https://kinta-electric.com
 * Description: Plugin personalizado para integrar componentes HTML de Electro con Elementor, incluyendo animaciones y funcionalidades avanzadas.
 * Version: 1.0.0
 * Author: Kinta Electric
 * Author URI: https://kinta-electric.com
 * Text Domain: kinta-electronic-elementor
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Elementor tested up to: 3.18
 * Elementor Pro tested up to: 3.18
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('KEE_PLUGIN_FILE', __FILE__);
define('KEE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KEE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KEE_PLUGIN_VERSION', '1.0.0');

/**
 * Clase principal del plugin KintaElectronic Elementor
 */
class KintaElectronicElementor {

    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Hooks de activación/desactivación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Inicializar el plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Hooks para Elementor
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_widget_categories'));
        
        // Hooks para scripts y estilos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Registrar shortcode
        add_shortcode('kinta_electronic', array($this, 'render_shortcode'));
    }

    /**
     * Activar el plugin
     */
    public function activate() {
        // Verificar si Elementor está activo
        if (!did_action('elementor/loaded')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Este plugin requiere Elementor para funcionar.');
        }
        
        // Crear tablas o opciones si es necesario
        flush_rewrite_rules();
    }

    /**
     * Desactivar el plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Inicializar el plugin
     */
    public function init() {
        // Verificar si Elementor está activo
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', array($this, 'admin_notice_missing_elementor'));
            return;
        }

        // Cargar traducciones
        load_plugin_textdomain('kinta-electric-elementor', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Cargar herramientas de desarrollo en modo debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            require_once KEE_PLUGIN_DIR . 'dev-tools.php';
        }
    }

    /**
     * Cargar archivos del widget
     */
    private function load_widget_files() {
        require_once KEE_PLUGIN_DIR . 'widgets/home-slider-kintaelectic-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/home-banner-product-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/home-product-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/home-product-card-carousel-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/team-members-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/about-content-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/faq-accordion-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/brand-carousel-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/breadcrumb-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/hero-banner-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/about-cards-kintaelectric-widget.php';
        require_once KEE_PLUGIN_DIR . 'widgets/contact-kintaelectric-widget.php';
    }

    /**
     * Registrar widgets de Elementor
     */
    public function register_widgets($widgets_manager) {
        // Cargar archivos del widget antes de registrar
        $this->load_widget_files();
        
        // Registrar el widget Home Slider Kintaelectic
        $widgets_manager->register(new \KEE_Home_Slider_Kintaelectic_Widget());
        
        // Registrar el widget Home Banner and Product Kintaelectric
        $widgets_manager->register(new \KEE_Home_Banner_Product_Kintaelectric_Widget());
        
        // Registrar el widget Home Product Kintaelectric
        $widgets_manager->register(new \KEE_Home_Product_Kintaelectric_Widget());
        
        // Registrar el widget Home Product Card Carousel Kintaelectric
        $widgets_manager->register(new \KEE_Home_Product_Card_Carousel_Kintaelectric_Widget());
        
        // Registrar el widget Team Members Kintaelectric
        $widgets_manager->register(new \KEE_Team_Members_Kintaelectric_Widget());
        
        // Registrar el widget About Content Kintaelectric
        $widgets_manager->register(new \KEE_About_Content_Kintaelectric_Widget());
        
        // Registrar el widget FAQ Accordion Kintaelectric
        $widgets_manager->register(new \KEE_FAQ_Accordion_Kintaelectric_Widget());
        
        // Registrar el widget Brand Carousel Kintaelectric
        $widgets_manager->register(new \KEE_Brand_Carousel_Kintaelectric_Widget());
        
        // Registrar el widget Breadcrumb Kintaelectric
        $widgets_manager->register(new \KEE_Breadcrumb_Kintaelectric_Widget());
        
        // Registrar el widget Hero Banner Kintaelectric
        $widgets_manager->register(new \KEE_Hero_Banner_Kintaelectric_Widget());
        
        // Registrar el widget About Cards Kintaelectric
        $widgets_manager->register(new \KEE_About_Cards_Kintaelectric_Widget());
        
        // Registrar el widget Contact Kintaelectric
        $widgets_manager->register(new \KEE_Contact_Kintaelectric_Widget());
    }

    /**
     * Añadir categorías de widgets personalizadas
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'kinta-electric',
            [
                'title' => esc_html__('Kinta Electric Widget', 'kinta-electric-elementor'),
                'icon' => 'eicon-plug',
            ]
        );
    }

    /**
     * Encolar scripts y estilos
     */
    public function enqueue_scripts() {
        // Solo cargar si estamos en el frontend y no en admin
        if (is_admin()) {
            return;
        }

        // CSS principal del plugin
        wp_enqueue_style(
            'kinta-electronic-elementor-style',
            KEE_PLUGIN_URL . 'assets/css/kinta-electronic-elementor.css',
            array(),
            KEE_PLUGIN_VERSION
        );

        // JavaScript principal del plugin
        wp_enqueue_script(
            'kinta-electronic-elementor-script',
            KEE_PLUGIN_URL . 'assets/js/kinta-electronic-elementor.js',
            array('jquery'),
            KEE_PLUGIN_VERSION,
            true
        );

        // Localizar script para AJAX
        wp_localize_script('kinta-electronic-elementor-script', 'kee_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kee_nonce'),
        ));
    }

    /**
     * Renderizar shortcode
     */
    public function render_shortcode($atts) {
        // Atributos por defecto
        $atts = shortcode_atts(array(
            'type' => 'slider',
            'class' => '',
        ), $atts, 'kinta_electronic');

        // Obtener el HTML según el tipo
        $html = $this->get_electro_html($atts['type']);
        
        if ($html) {
            return '<div class="kinta-electronic-wrapper ' . esc_attr($atts['class']) . '">' . $html . '</div>';
        }

        return '';
    }

    /**
     * Obtener HTML de Electro según el tipo
     */
    private function get_electro_html($type) {
        switch ($type) {
            case 'slider':
                return $this->get_slider_html();
            case 'banner':
                return $this->get_banner_html();
            default:
                return '';
        }
    }

    /**
     * Obtener HTML del slider
     */
    private function get_slider_html() {
        ob_start();
        ?>
        <!-- Slider Section -->
        <div class="mb-5">
            <div class="js-slick-carousel u-slick" data-pagi-classes="text-center position-absolute right-0 bottom-0 left-0 u-slick__pagination u-slick__pagination--long justify-content-start mb-3 mb-md-4 offset-xl-3 pl-2 pb-1">
                <div class="js-slide bg-img-hero" style="background-image: url(<?php echo KEE_PLUGIN_URL; ?>assets/images/slider-placeholder.jpg);">
                    <div class="container min-height-420 overflow-hidden">
                        <div class="row min-height-420 py-7 py-md-0">
                            <div class="offset-xl-3 col-xl-4 col-6 mt-md-8">
                                <h1 class="font-size-64 text-lh-57 font-weight-light" data-scs-animation-in="fadeInUp">
                                    THE NEW STANDARD
                                </h1>
                                <h6 class="font-size-15 font-weight-bold mb-3" data-scs-animation-in="fadeInUp" data-scs-animation-delay="200">
                                    UNDER FAVORABLE SMARTWATCHES
                                </h6>
                                <div class="mb-4" data-scs-animation-in="fadeInUp" data-scs-animation-delay="300">
                                    <span class="font-size-13">FROM</span>
                                    <div class="font-size-50 font-weight-bold text-lh-45">
                                        <sup class="">$</sup>749<sup class="">99</sup>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-primary transition-3d-hover rounded-lg font-weight-normal py-2 px-md-7 px-3 font-size-16" data-scs-animation-in="fadeInUp" data-scs-animation-delay="400">
                                    Start Buying
                                </a>
                            </div>
                            <div class="col-xl-5 col-6 d-flex align-items-center" data-scs-animation-in="zoomIn" data-scs-animation-delay="500">
                                <img class="img-fluid" src="<?php echo KEE_PLUGIN_URL; ?>assets/images/product-placeholder.jpg" alt="Image Description">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Slider Section -->
        <?php
        return ob_get_clean();
    }

    /**
     * Obtener HTML del banner
     */
    private function get_banner_html() {
        ob_start();
        ?>
        <!-- Banner Section -->
        <div class="container">
            <div class="mb-5">
                <div class="row">
                    <div class="col-md-6 mb-4 mb-xl-0 col-xl-3">
                        <a href="#" class="d-black text-gray-90">
                            <div class="min-height-132 py-1 d-flex bg-gray-1 align-items-center">
                                <div class="col-6 col-xl-5 col-wd-6 pr-0">
                                    <img class="img-fluid" src="<?php echo KEE_PLUGIN_URL; ?>assets/images/banner-placeholder.jpg" alt="Image Description">
                                </div>
                                <div class="col-6 col-xl-7 col-wd-6">
                                    <div class="mb-2 pb-1 font-size-18 font-weight-light text-ls-n1 text-lh-23">
                                        CATCH BIG DEALS ON THE CAMERAS
                                    </div>
                                    <div class="link text-gray-90 font-weight-bold font-size-15" href="#">
                                        Shop now
                                        <span class="link__icon ml-1">
                                            <span class="link__icon-inner"><i class="ec ec-arrow-right-categproes"></i></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Banner Section -->
        <?php
        return ob_get_clean();
    }

    /**
     * Mostrar aviso de administración si Elementor no está activo
     */
    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requiere "%2$s" para funcionar.', 'kinta-electronic-elementor'),
            '<strong>' . esc_html__('KintaElectronic Elementor', 'kinta-electric-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'kinta-electric-elementor') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}

// Inicializar el plugin
new KintaElectronicElementor();
