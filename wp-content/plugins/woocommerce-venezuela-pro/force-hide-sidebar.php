<?php
/**
 * Script para forzar ocultación de selectores en sidebar
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('wp_footer', function() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function forceHideSidebarSelectors() {
                // Ocultar en sidebar específico de shop
                $('.woocommerce-shop .sidebar .wvp-currency-switcher').hide();
                $('.woocommerce-shop .sidebar .wvp-price-conversion').hide();
                $('.woocommerce-shop .sidebar .wvp-rate-info').hide();
                
                // Ocultar en cualquier sidebar
                $('.sidebar .wvp-currency-switcher').hide();
                $('.sidebar .wvp-price-conversion').hide();
                $('.sidebar .wvp-rate-info').hide();
                
                // Ocultar en widget-area
                $('.widget-area .wvp-currency-switcher').hide();
                $('.widget-area .wvp-price-conversion').hide();
                $('.widget-area .wvp-rate-info').hide();
                
                // Ocultar en aside
                $('aside .wvp-currency-switcher').hide();
                $('aside .wvp-price-conversion').hide();
                $('aside .wvp-rate-info').hide();
                
                // Ocultar en listas de productos en sidebar
                $('.sidebar ul.products .wvp-currency-switcher').hide();
                $('.sidebar ul.products .wvp-price-conversion').hide();
                $('.sidebar ul.products .wvp-rate-info').hide();
                
                // Ocultar en cualquier elemento con clase que contenga "sidebar"
                $('[class*="sidebar"] .wvp-currency-switcher').hide();
                $('[class*="sidebar"] .wvp-price-conversion').hide();
                $('[class*="sidebar"] .wvp-rate-info').hide();
                
                console.log('WVP Force Sidebar: Selectores ocultados en sidebar');
            }
            
            // Ejecutar inmediatamente
            forceHideSidebarSelectors();
            
            // Ejecutar después de un pequeño retraso
            setTimeout(forceHideSidebarSelectors, 500);
            setTimeout(forceHideSidebarSelectors, 1500);
            
            // Observar cambios en el DOM
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        forceHideSidebarSelectors();
                    }
                });
            });
            
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
    <?php
}, 9999); // Prioridad muy alta
