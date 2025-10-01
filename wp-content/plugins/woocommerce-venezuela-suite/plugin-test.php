<?php
/**
 * Plugin Test - Verificación de funcionamiento
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función para verificar el estado del plugin
 */
function wvs_check_plugin_status() {
    $status = array(
        'plugin_active' => is_plugin_active('woocommerce-venezuela-suite/woocommerce-venezuela-suite.php'),
        'classes_loaded' => array(),
        'constants_defined' => array(),
        'errors' => array()
    );
    
    // Verificar clases
    $classes = array(
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
    
    foreach ($classes as $class) {
        $status['classes_loaded'][$class] = class_exists($class);
    }
    
    // Verificar constantes
    $constants = array(
        'WVS_VERSION',
        'WVS_PLUGIN_PATH',
        'WVS_PLUGIN_URL',
        'WVS_PLUGIN_BASENAME',
        'WVS_PLUGIN_FILE',
        'WVS_MIN_WC_VERSION',
        'WVS_MIN_WP_VERSION',
        'WVS_MIN_PHP_VERSION'
    );
    
    foreach ($constants as $constant) {
        $status['constants_defined'][$constant] = defined($constant);
    }
    
    // Verificar errores
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $status['errors'] = error_get_last();
    }
    
    return $status;
}

/**
 * Función para mostrar información del plugin en admin
 */
function wvs_show_plugin_info() {
    if (!is_admin()) {
        return;
    }
    
    $status = wvs_check_plugin_status();
    
    echo '<div class="notice notice-info">';
    echo '<h3>WooCommerce Venezuela Suite - Estado del Plugin</h3>';
    echo '<p><strong>Versión:</strong> ' . (defined('WVS_VERSION') ? WVS_VERSION : 'No definida') . '</p>';
    echo '<p><strong>Plugin Activo:</strong> ' . ($status['plugin_active'] ? 'Sí' : 'No') . '</p>';
    
    echo '<h4>Clases Cargadas:</h4>';
    echo '<ul>';
    foreach ($status['classes_loaded'] as $class => $loaded) {
        echo '<li>' . $class . ': ' . ($loaded ? '✅' : '❌') . '</li>';
    }
    echo '</ul>';
    
    echo '<h4>Constantes Definidas:</h4>';
    echo '<ul>';
    foreach ($status['constants_defined'] as $constant => $defined) {
        echo '<li>' . $constant . ': ' . ($defined ? '✅' : '❌') . '</li>';
    }
    echo '</ul>';
    
    if (!empty($status['errors'])) {
        echo '<h4>Último Error:</h4>';
        echo '<pre>' . print_r($status['errors'], true) . '</pre>';
    }
    
    echo '</div>';
}

// Mostrar información en admin
add_action('admin_notices', 'wvs_show_plugin_info');
