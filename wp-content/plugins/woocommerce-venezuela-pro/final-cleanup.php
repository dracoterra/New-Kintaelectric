<?php
/**
 * Script de limpieza final - Eliminar todos los archivos temporales
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Función para limpiar todos los archivos temporales
function wvp_final_cleanup() {
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
        'fix-context-detection.php',
        'force-context-shop.php',
        'cleanup-temp-files.php',
        'cleanup-all-temp.php',
        'final-cleanup.php'
    );
    
    $plugin_path = plugin_dir_path(__FILE__);
    $deleted_count = 0;
    
    foreach ($temp_files as $file) {
        $file_path = $plugin_path . $file;
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $deleted_count++;
                error_log('WVP: Archivo temporal eliminado: ' . $file);
            }
        }
    }
    
    return $deleted_count;
}

// Función para mostrar botón de limpieza final en admin
add_action('admin_notices', function() {
    if (current_user_can('manage_woocommerce')) {
        if (isset($_GET['wvp_final_cleanup']) && $_GET['wvp_final_cleanup'] === '1') {
            $deleted_count = wvp_final_cleanup();
            echo '<div class="notice notice-success"><p><strong>WVP:</strong> ' . $deleted_count . ' archivos temporales eliminados. Sistema limpio y optimizado.</p></div>';
        } else {
            $cleanup_url = add_query_arg('wvp_final_cleanup', '1', admin_url('admin.php?page=wvp-admin'));
            echo '<div class="notice notice-info">';
            echo '<p><strong>WVP:</strong> <a href="' . esc_url($cleanup_url) . '" class="button button-primary">🧹 Limpieza Final - Eliminar TODOS los archivos temporales</a></p>';
            echo '<p><em>Esto eliminará todos los scripts de depuración y archivos temporales, dejando solo el código esencial.</em></p>';
            echo '</div>';
        }
    }
});
?>
