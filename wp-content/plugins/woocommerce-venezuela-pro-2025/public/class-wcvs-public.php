<?php
/**
 * Clase pública del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar la funcionalidad pública del plugin
 */
class WCVS_Public {

    /**
     * Versión del plugin
     *
     * @var string
     */
    private $version;

    /**
     * Constructor
     *
     * @param string $version Versión del plugin
     */
    public function __construct($version) {
        $this->version = $version;
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Cargar estilos públicos
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wcvs-public-styles',
            WCVS_PLUGIN_URL . 'public/css/wcvs-public.css',
            array(),
            $this->version
        );
    }

    /**
     * Cargar scripts públicos
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wcvs-public-script',
            WCVS_PLUGIN_URL . 'public/js/wcvs-public.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localizar script para AJAX
        wp_localize_script('wcvs-public-script', 'wcvs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcvs_public_nonce')
        ));
    }
}
