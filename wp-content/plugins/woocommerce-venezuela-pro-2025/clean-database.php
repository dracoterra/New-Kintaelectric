<?php
/**
 * Script para limpiar datos corruptos del plugin en la base de datos
 */

// Cargar WordPress
require_once '../../../wp-config.php';

echo "=== LIMPIEZA DE BASE DE DATOS - WooCommerce Venezuela Pro 2025 ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

global $wpdb;

// 1. Limpiar opciones del plugin
echo "1. Limpiando opciones del plugin...\n";
$plugin_options = $wpdb->get_results("
    SELECT option_name 
    FROM {$wpdb->options} 
    WHERE option_name LIKE 'wvp_%' 
    OR option_name LIKE 'woocommerce_venezuela_pro_2025_%'
");

foreach ($plugin_options as $option) {
    echo "   Eliminando: " . $option->option_name . "\n";
    delete_option($option->option_name);
}

// 2. Limpiar transients del plugin
echo "\n2. Limpiando transients del plugin...\n";
$plugin_transients = $wpdb->get_results("
    SELECT option_name 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_wvp_%' 
    OR option_name LIKE '_transient_timeout_wvp_%'
    OR option_name LIKE '_transient_woocommerce_venezuela_pro_2025_%'
    OR option_name LIKE '_transient_timeout_woocommerce_venezuela_pro_2025_%'
");

foreach ($plugin_transients as $transient) {
    echo "   Eliminando: " . $transient->option_name . "\n";
    delete_option($transient->option_name);
}

// 3. Limpiar meta del plugin en posts
echo "\n3. Limpiando meta del plugin en posts...\n";
$plugin_meta = $wpdb->get_results("
    SELECT post_id, meta_key 
    FROM {$wpdb->postmeta} 
    WHERE meta_key LIKE 'wvp_%'
    OR meta_key LIKE '_wvp_%'
");

foreach ($plugin_meta as $meta) {
    echo "   Eliminando meta: " . $meta->meta_key . " del post " . $meta->post_id . "\n";
    delete_post_meta($meta->post_id, $meta->meta_key);
}

// 4. Limpiar meta del plugin en usuarios
echo "\n4. Limpiando meta del plugin en usuarios...\n";
$user_meta = $wpdb->get_results("
    SELECT user_id, meta_key 
    FROM {$wpdb->usermeta} 
    WHERE meta_key LIKE 'wvp_%'
    OR meta_key LIKE '_wvp_%'
");

foreach ($user_meta as $meta) {
    echo "   Eliminando meta: " . $meta->meta_key . " del usuario " . $meta->user_id . "\n";
    delete_user_meta($meta->user_id, $meta->meta_key);
}

// 5. Limpiar meta del plugin en términos
echo "\n5. Limpiando meta del plugin en términos...\n";
$term_meta = $wpdb->get_results("
    SELECT term_id, meta_key 
    FROM {$wpdb->termmeta} 
    WHERE meta_key LIKE 'wvp_%'
    OR meta_key LIKE '_wvp_%'
");

foreach ($term_meta as $meta) {
    echo "   Eliminando meta: " . $meta->meta_key . " del término " . $meta->term_id . "\n";
    delete_term_meta($meta->term_id, $meta->meta_key);
}

// 6. Limpiar meta del plugin en comentarios
echo "\n6. Limpiando meta del plugin en comentarios...\n";
$comment_meta = $wpdb->get_results("
    SELECT comment_id, meta_key 
    FROM {$wpdb->commentmeta} 
    WHERE meta_key LIKE 'wvp_%'
    OR meta_key LIKE '_wvp_%'
");

foreach ($comment_meta as $meta) {
    echo "   Eliminando meta: " . $meta->meta_key . " del comentario " . $meta->comment_id . "\n";
    delete_comment_meta($meta->comment_id, $meta->meta_key);
}

// 7. Verificar si hay tablas del plugin
echo "\n7. Verificando tablas del plugin...\n";
$plugin_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}wvp_%'");
foreach ($plugin_tables as $table) {
    $table_name = array_values((array)$table)[0];
    echo "   Tabla encontrada: " . $table_name . "\n";
    echo "   ¿Eliminar tabla? (S/N): ";
    // Por seguridad, no eliminamos tablas automáticamente
    echo "   [SALTADO POR SEGURIDAD]\n";
}

// 8. Limpiar cache de WordPress
echo "\n8. Limpiando cache de WordPress...\n";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "   Cache de WordPress limpiado\n";
}

// 9. Limpiar cache de WooCommerce
echo "\n9. Limpiando cache de WooCommerce...\n";
if (function_exists('wc_delete_product_transients')) {
    // Limpiar transients de productos
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_%'");
    echo "   Cache de WooCommerce limpiado\n";
}

echo "\n=== LIMPIEZA COMPLETADA ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Recomendación: Desactiva y reactiva el plugin para reinicializar datos limpios.\n";
?>
