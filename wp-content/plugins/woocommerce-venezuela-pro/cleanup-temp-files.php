<?php
/**
 * Script para limpiar archivos temporales de depuración
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Función para limpiar archivos temporales
function wvp_cleanup_temp_files() {
    $temp_files = array(
        'test-currency-switcher.php',
        'debug-currency-switcher.php',
        'test-simple-display.php',
        'debug-display-manager.php',
        'test-price-display.php',
        'debug-settings.php',
        'init-display-settings.php',
        'force-display-settings.php',
        'test-price-hook.php',
        'cleanup-debug.php',
        'debug-context-detection.php',
        'force-update-settings.php',
        'cleanup-temp-files.php'
    );
    
    $plugin_path = plugin_dir_path(__FILE__);
    
    foreach ($temp_files as $file) {
        $file_path = $plugin_path . $file;
        if (file_exists($file_path)) {
            unlink($file_path);
            error_log('WVP: Archivo temporal eliminado: ' . $file);
        }
    }
}

// Ejecutar limpieza al desactivar el plugin
register_deactivation_hook(__FILE__, 'wvp_cleanup_temp_files');

// Función para mostrar botón de limpieza en admin
add_action('admin_notices', function() {
    if (current_user_can('manage_woocommerce') && isset($_GET['wvp_cleanup']) && $_GET['wvp_cleanup'] === '1') {
        wvp_cleanup_temp_files();
        echo '<div class="notice notice-success"><p>Archivos temporales eliminados correctamente.</p></div>';
    }
});

// Mostrar botón de limpieza en admin
add_action('admin_notices', function() {
    if (current_user_can('manage_woocommerce')) {
        $cleanup_url = add_query_arg('wvp_cleanup', '1', admin_url('admin.php?page=wvp-admin'));
        echo '<div class="notice notice-warning">';
        echo '<p><strong>WVP:</strong> <a href="' . esc_url($cleanup_url) . '" class="button">Limpiar archivos temporales</a></p>';
        echo '</div>';
    }
});
?>
