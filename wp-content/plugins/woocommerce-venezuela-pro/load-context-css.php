<?php
/**
 * Script para cargar CSS específico por contexto
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Cargar CSS específico por contexto
add_action('wp_enqueue_scripts', function() {
    if (class_exists('WooCommerce_Venezuela_Pro')) {
        wp_enqueue_style(
            'wvp-context-specific',
            plugin_dir_url(__FILE__) . 'assets/css/wvp-context-specific.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_script(
            'wvp-context-hider',
            plugin_dir_url(__FILE__) . 'assets/js/wvp-context-hider.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
});
?>
