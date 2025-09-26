<?php
/**
 * Test del Sistema de Conversores de Moneda
 * WooCommerce Venezuela Pro 2025
 * 
 * Este archivo es solo para testing y debe eliminarse en producción
 */

// Verificar que WordPress esté cargado
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Solo ejecutar para administradores
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

// Verificar que las clases estén disponibles
if ( ! class_exists( 'WVP_Currency_Manager' ) ) {
    echo '<div class="notice notice-error"><p>WVP_Currency_Manager no está disponible</p></div>';
    return;
}

if ( ! class_exists( 'WVP_Display_Settings' ) ) {
    echo '<div class="notice notice-error"><p>WVP_Display_Settings no está disponible</p></div>';
    return;
}

if ( ! class_exists( 'WVP_Display_Control' ) ) {
    echo '<div class="notice notice-error"><p>WVP_Display_Control no está disponible</p></div>';
    return;
}

echo '<div class="notice notice-success"><p>Sistema de Conversores de Moneda cargado correctamente!</p></div>';

// Test del Currency Manager
$currency_manager = WVP_Currency_Manager::get_instance();
$rate = $currency_manager->get_bcv_rate();
$converted = $currency_manager->convert_price( 100, 'USD', 'VES' );

echo '<div class="notice notice-info">';
echo '<p><strong>Test Currency Manager:</strong></p>';
echo '<p>Tasa BCV: ' . number_format( $rate, 2 ) . ' Bs./USD</p>';
echo '<p>100 USD = ' . $currency_manager->format_currency( $converted, 'VES' ) . '</p>';
echo '</div>';

// Test del Display Settings
$settings = WVP_Display_Settings::get_settings();
echo '<div class="notice notice-info">';
echo '<p><strong>Test Display Settings:</strong></p>';
echo '<p>Configuraciones cargadas: ' . count( $settings ) . ' secciones</p>';
echo '<p>Ubicaciones configuradas: ' . count( $settings['display_locations'] ) . '</p>';
echo '</div>';

// Test de shortcodes
echo '<div class="notice notice-info">';
echo '<p><strong>Test Shortcodes:</strong></p>';
echo '<p>Conversor de botones: ' . do_shortcode( '[wvp_currency_switcher style="buttons"]' ) . '</p>';
echo '<p>Tasa BCV: ' . do_shortcode( '[wvp_bcv_rate]' ) . '</p>';
echo '<p>Conversor de precio: ' . do_shortcode( '[wvp_price_converter amount="50" from="USD" to="VES"]' ) . '</p>';
echo '</div>';
