<?php
/**
 * Script simple para verificar pedidos
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🔍 Verificación Simple de Pedidos</h1>";

global $wpdb;

// Verificar todas las tablas que contengan 'order'
echo "<h2>📋 Tablas relacionadas con pedidos:</h2>";
$tables = $wpdb->get_results( "SHOW TABLES LIKE '%order%'" );
foreach ( $tables as $table ) {
    $table_name = array_values( (array) $table )[0];
    echo "- {$table_name}<br>";
}

// Verificar posts con post_type shop_order
echo "<h2>📦 Posts con post_type = 'shop_order':</h2>";
$orders = $wpdb->get_results( "
    SELECT ID, post_title, post_status, post_date 
    FROM {$wpdb->posts} 
    WHERE post_type = 'shop_order'
    ORDER BY post_date DESC
" );

if ( $orders ) {
    echo "Encontrados " . count( $orders ) . " pedidos:<br>";
    foreach ( $orders as $order ) {
        echo "- ID: {$order->ID}, Título: {$order->post_title}, Estado: {$order->post_status}, Fecha: {$order->post_date}<br>";
    }
} else {
    echo "❌ No se encontraron pedidos con post_type = 'shop_order'<br>";
}

// Verificar si hay pedidos en otras tablas
echo "<h2>🔍 Verificando otras posibles ubicaciones:</h2>";

// Verificar si existe la tabla wc_orders (HPOS)
if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_orders'" ) ) {
    echo "✅ Existe tabla {$wpdb->prefix}wc_orders<br>";
    $wc_orders_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders" );
    echo "Pedidos en wc_orders: {$wc_orders_count}<br>";
    
    if ( $wc_orders_count > 0 ) {
        $wc_orders = $wpdb->get_results( "
            SELECT id, status, date_created_gmt, total_amount 
            FROM {$wpdb->prefix}wc_orders 
            ORDER BY date_created_gmt DESC 
            LIMIT 5
        " );
        
        echo "Últimos 5 pedidos en wc_orders:<br>";
        foreach ( $wc_orders as $order ) {
            echo "- ID: {$order->id}, Estado: {$order->status}, Total: {$order->total_amount}, Fecha: {$order->date_created_gmt}<br>";
        }
    }
} else {
    echo "❌ No existe tabla {$wpdb->prefix}wc_orders<br>";
}

// Verificar configuración de HPOS
echo "<h2>⚙️ Configuración de HPOS:</h2>";
$hpos_enabled = get_option( 'woocommerce_custom_orders_table_enabled' );
echo "HPOS habilitado: " . ( $hpos_enabled ? 'Sí' : 'No' ) . "<br>";

echo "<hr>";
echo "<p><strong>Verificación ejecutada:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
