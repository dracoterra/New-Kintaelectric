<?php
/**
 * Rate Limit Cleaner - Limpiar rate limits
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función para limpiar rate limits
 */
function wvs_clear_rate_limits() {
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos para realizar esta acción.');
    }
    
    global $wpdb;
    
    // Limpiar transients de rate limiting
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_wvs_rate_limit_%' 
         OR option_name LIKE '_transient_timeout_wvs_rate_limit_%'"
    );
    
    echo '<div class="notice notice-success"><p>Rate limits de Suite Venezuela limpiados correctamente.</p></div>';
}

// Agregar página de limpieza en admin
add_action('admin_menu', function() {
    add_management_page(
        'Limpiar Rate Limits - Suite Venezuela',
        'Limpiar Rate Limits',
        'manage_options',
        'wvs-clear-rate-limits',
        'wvs_clear_rate_limits'
    );
});
