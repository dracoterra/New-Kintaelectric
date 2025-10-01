<?php
/**
 * Test File - Archivo de prueba para verificar funcionamiento
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función de prueba para verificar que el plugin funciona
 */
function wvs_test_plugin_functionality() {
    // Verificar que las clases principales existen
    $classes_to_check = array(
        'WooCommerce_Venezuela_Suite',
        'WVS_Core_Engine',
        'WVS_Module_Manager',
        'WVS_Admin',
        'WVS_Public',
        'WVS_Logger',
        'WVS_Database',
        'WVS_Security',
        'WVS_Performance',
        'WVS_Config_Manager',
        'WVS_Helper',
        'WVS_Compatibility'
    );
    
    $results = array();
    
    foreach ($classes_to_check as $class) {
        $results[$class] = class_exists($class);
    }
    
    return $results;
}

/**
 * Función para mostrar información del plugin
 */
function wvs_get_plugin_info() {
    return array(
        'version' => WVS_VERSION,
        'plugin_path' => WVS_PLUGIN_PATH,
        'plugin_url' => WVS_PLUGIN_URL,
        'plugin_basename' => WVS_PLUGIN_BASENAME,
        'min_wc_version' => WVS_MIN_WC_VERSION,
        'min_wp_version' => WVS_MIN_WP_VERSION,
        'min_php_version' => WVS_MIN_PHP_VERSION
    );
}

/**
 * Función para verificar compatibilidad
 */
function wvs_check_compatibility() {
    $issues = array();
    
    // Verificar PHP
    if (version_compare(PHP_VERSION, WVS_MIN_PHP_VERSION, '<')) {
        $issues[] = 'PHP version too low';
    }
    
    // Verificar WordPress
    if (version_compare(get_bloginfo('version'), WVS_MIN_WP_VERSION, '<')) {
        $issues[] = 'WordPress version too low';
    }
    
    // Verificar WooCommerce
    if (!class_exists('WooCommerce')) {
        $issues[] = 'WooCommerce not installed';
    } elseif (version_compare(WC()->version, WVS_MIN_WC_VERSION, '<')) {
        $issues[] = 'WooCommerce version too low';
    }
    
    return array(
        'compatible' => empty($issues),
        'issues' => $issues
    );
}
