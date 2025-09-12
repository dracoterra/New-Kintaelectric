/**
 * JavaScript para ocultar selectores de moneda en contextos específicos
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Función para ocultar selectores en widgets y footer
    function hideSelectorsInContexts() {
        // Ocultar en widgets
        $('.widget .wvp-currency-switcher, .widget .wvp-price-conversion, .widget .wvp-rate-info').hide();
        
        // Ocultar en footer
        $('.footer .wvp-currency-switcher, .footer .wvp-price-conversion, .footer .wvp-rate-info').hide();
        $('#footer .wvp-currency-switcher, #footer .wvp-price-conversion, #footer .wvp-rate-info').hide();
        $('.site-footer .wvp-currency-switcher, .site-footer .wvp-price-conversion, .site-footer .wvp-rate-info').hide();
        
        // Ocultar en sidebar
        $('.sidebar .wvp-currency-switcher, .sidebar .wvp-price-conversion, .sidebar .wvp-rate-info').hide();
        $('#sidebar .wvp-currency-switcher, #sidebar .wvp-price-conversion, #sidebar .wvp-rate-info').hide();
        $('.widget-area .wvp-currency-switcher, .widget-area .wvp-price-conversion, .widget-area .wvp-rate-info').hide();
        $('aside .wvp-currency-switcher, aside .wvp-price-conversion, aside .wvp-rate-info').hide();
        
        // Ocultar en sidebar de WooCommerce shop
        $('.woocommerce-shop .sidebar .wvp-currency-switcher, .woocommerce-shop .sidebar .wvp-price-conversion, .woocommerce-shop .sidebar .wvp-rate-info').hide();
        $('.woocommerce-shop #sidebar .wvp-currency-switcher, .woocommerce-shop #sidebar .wvp-price-conversion, .woocommerce-shop #sidebar .wvp-rate-info').hide();
        $('.woocommerce-shop .widget-area .wvp-currency-switcher, .woocommerce-shop .widget-area .wvp-price-conversion, .woocommerce-shop .widget-area .wvp-rate-info').hide();
        
        // Ocultar en cualquier elemento con clase que contenga "sidebar" o "widget"
        $('[class*="sidebar"] .wvp-currency-switcher, [class*="sidebar"] .wvp-price-conversion, [class*="sidebar"] .wvp-rate-info').hide();
        $('[class*="widget"] .wvp-currency-switcher, [class*="widget"] .wvp-price-conversion, [class*="widget"] .wvp-rate-info').hide();
        
        // Ocultar en carruseles
        $('.owl-carousel .wvp-currency-switcher, .owl-carousel .wvp-price-conversion, .owl-carousel .wvp-rate-info').hide();
        $('.carousel .wvp-currency-switcher, .carousel .wvp-price-conversion, .carousel .wvp-rate-info').hide();
        
        // Ocultar en secciones promocionales
        $('.section-products-carousel .wvp-currency-switcher, .section-products-carousel .wvp-price-conversion, .section-products-carousel .wvp-rate-info').hide();
        
        // Ocultar en listas de productos en widgets
        $('.widget ul.products .wvp-currency-switcher, .widget ul.products .wvp-price-conversion, .widget ul.products .wvp-rate-info').hide();
        
        // Ocultar en listas de productos en sidebar
        $('.sidebar ul.products .wvp-currency-switcher, .sidebar ul.products .wvp-price-conversion, .sidebar ul.products .wvp-rate-info').hide();
        $('#sidebar ul.products .wvp-currency-switcher, #sidebar ul.products .wvp-price-conversion, #sidebar ul.products .wvp-rate-info').hide();
        $('.widget-area ul.products .wvp-currency-switcher, .widget-area ul.products .wvp-price-conversion, .widget-area ul.products .wvp-rate-info').hide();
        
        // Ocultar en listas de productos en footer
        $('.footer ul.products .wvp-currency-switcher, .footer ul.products .wvp-price-conversion, .footer ul.products .wvp-rate-info').hide();
        $('#footer ul.products .wvp-currency-switcher, #footer ul.products .wvp-price-conversion, #footer ul.products .wvp-rate-info').hide();
        
        // Forzar ocultación de conversión en todas las áreas no principales
        $('.wvp-price-conversion').hide();
        
        // Mostrar conversión solo en áreas principales de productos
        $('.woocommerce .products .wvp-price-conversion, .woocommerce .product .wvp-price-conversion').show();
        
    }
    
    // Ejecutar inmediatamente
    hideSelectorsInContexts();
    
    // Ejecutar después de que se carguen los productos dinámicamente
    $(document).on('DOMNodeInserted', function() {
        hideSelectorsInContexts();
    });
    
    // Ejecutar en eventos de WooCommerce
    $(document.body).on('updated_wc_div', function() {
        hideSelectorsInContexts();
    });
    
    // Ejecutar en eventos de AJAX
    $(document).ajaxComplete(function() {
        hideSelectorsInContexts();
    });
    
    // Ejecutar periódicamente para asegurar que se mantenga oculto
    setInterval(hideSelectorsInContexts, 1000);
});
