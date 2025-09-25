<?php
/**
 * Internacionalización del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar la internacionalización del plugin
 */
class WCVS_i18n {

    /**
     * Cargar el dominio de texto del plugin
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wcvs',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
