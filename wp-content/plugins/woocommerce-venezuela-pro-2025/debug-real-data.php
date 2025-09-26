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
    
    // Pedidos recientes
    $recent_orders = $wpdb->get_results( "
        SELECT p.ID, p.post_date, p.post_status, pm.meta_value as total
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
        WHERE p.post_type = 'shop_order'
        ORDER BY p.post_date DESC
        LIMIT 5
    " );
    
    echo "<h3>📅 Últimos 5 pedidos:</h3>";
    foreach ( $recent_orders as $order ) {
        echo "- Pedido #{$order->ID}: $" . number_format( $order->total, 2 ) . " - {$order->post_status} - {$order->post_date}<br>";
    }
} else {
    echo "❌ No hay pedidos en el sistema<br>";
}

// 2. Verificar productos
echo "<h2>🛍️ Productos</h2>";
$products_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product'" );
echo "Total de productos: " . $products_count . "<br>";

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

// 4. Probar la función get_analytics_data directamente
echo "<h2>🧪 Prueba de Función get_analytics_data</h2>";

if ( class_exists( 'WVP_Analytics_Dashboard' ) ) {
    $analytics = new WVP_Analytics_Dashboard();
    
    // Probar con diferentes parámetros
    $periods = array( '30_days', 'last_30_days', '7_days', 'last_7_days' );
    
    foreach ( $periods as $period ) {
        echo "<h3>Probando período: {$period}</h3>";
        try {
            $data = $analytics->get_analytics_data( $period );
            
            if ( ! empty( $data ) ) {
                echo "✅ Datos obtenidos:<br>";
                foreach ( $data as $key => $value ) {
                    if ( is_array( $value ) && isset( $value['total'] ) ) {
                        echo "- {$key}: " . $value['total'] . "<br>";
                    }
                }
            } else {
                echo "❌ No se obtuvieron datos<br>";
            }
        } catch ( Exception $e ) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "❌ La clase WVP_Analytics_Dashboard no existe<br>";
}

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
