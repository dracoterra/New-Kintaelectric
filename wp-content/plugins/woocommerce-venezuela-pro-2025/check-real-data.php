<?php
/**
 * Script para verificar datos reales de WooCommerce
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Verificación de Datos Reales de WooCommerce</h1>";

// Verificar si WooCommerce está activo
if ( ! class_exists( 'WooCommerce' ) ) {
    echo "❌ WooCommerce no está activo<br>";
    exit;
}

echo "✅ WooCommerce está activo<br>";

global $wpdb;

// 1. Verificar pedidos
echo "<h2>📦 Pedidos</h2>";
$orders_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'" );
echo "Total de pedidos: " . $orders_count . "<br>";

if ( $orders_count > 0 ) {
    // Pedidos por estado
    $orders_by_status = $wpdb->get_results( "
        SELECT post_status, COUNT(*) as count 
        FROM {$wpdb->posts} 
        WHERE post_type = 'shop_order' 
        GROUP BY post_status
    " );
    
    echo "Pedidos por estado:<br>";
    foreach ( $orders_by_status as $status ) {
        echo "- " . $status->post_status . ": " . $status->count . "<br>";
    }
    
    // Ventas totales
    $total_sales = $wpdb->get_var( "
        SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2))) 
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
        AND pm.meta_key = '_order_total'
    " );
    
    echo "Ventas totales: $" . number_format( $total_sales, 2 ) . "<br>";
} else {
    echo "❌ No hay pedidos en el sistema<br>";
}

// 2. Verificar productos
echo "<h2>🛍️ Productos</h2>";
$products_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product'" );
echo "Total de productos: " . $products_count . "<br>";

if ( $products_count > 0 ) {
    $products_by_status = $wpdb->get_results( "
        SELECT post_status, COUNT(*) as count 
        FROM {$wpdb->posts} 
        WHERE post_type = 'product' 
        GROUP BY post_status
    " );
    
    echo "Productos por estado:<br>";
    foreach ( $products_by_status as $status ) {
        echo "- " . $status->post_status . ": " . $status->count . "<br>";
    }
} else {
    echo "❌ No hay productos en el sistema<br>";
}

// 3. Verificar clientes
echo "<h2>👥 Clientes</h2>";
$customers_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
echo "Total de usuarios: " . $customers_count . "<br>";

// Clientes con pedidos
$customers_with_orders = $wpdb->get_var( "
    SELECT COUNT(DISTINCT pm.meta_value) 
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = 'shop_order'
    AND pm.meta_key = '_customer_user'
    AND pm.meta_value != '0'
" );

echo "Clientes con pedidos: " . $customers_with_orders . "<br>";

// 4. Verificar datos recientes
echo "<h2>📅 Datos Recientes</h2>";
$recent_orders = $wpdb->get_results( "
    SELECT post_date, post_status 
    FROM {$wpdb->posts} 
    WHERE post_type = 'shop_order' 
    ORDER BY post_date DESC 
    LIMIT 5
" );

if ( $recent_orders ) {
    echo "Últimos 5 pedidos:<br>";
    foreach ( $recent_orders as $order ) {
        echo "- " . $order->post_date . " (" . $order->post_status . ")<br>";
    }
} else {
    echo "❌ No hay pedidos recientes<br>";
}

// 5. Verificar configuración de WooCommerce
echo "<h2>⚙️ Configuración de WooCommerce</h2>";
echo "Moneda: " . get_woocommerce_currency() . "<br>";
echo "Símbolo de moneda: " . get_woocommerce_currency_symbol() . "<br>";
echo "País: " . get_option( 'woocommerce_default_country' ) . "<br>";

echo "<h2>🎯 Recomendaciones</h2>";
if ( $orders_count == 0 ) {
    echo "⚠️ Para ver datos reales en las gráficas, necesitas:<br>";
    echo "1. Crear algunos productos en WooCommerce<br>";
    echo "2. Crear algunos pedidos de prueba<br>";
    echo "3. Las gráficas mostrarán datos reales automáticamente<br>";
} else {
    echo "✅ Tienes datos reales disponibles<br>";
    echo "Las gráficas deberían mostrar datos reales en lugar de datos de demostración<br>";
}

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
