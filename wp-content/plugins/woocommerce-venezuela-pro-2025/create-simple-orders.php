<?php
/**
 * Script simple para crear pedidos de prueba
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🛒 Generador Simple de Pedidos de Prueba</h1>";

// Verificar si WooCommerce está activo
if ( ! class_exists( 'WooCommerce' ) ) {
    echo "❌ WooCommerce no está activo<br>";
    exit;
}

echo "✅ WooCommerce está activo<br>";

// Verificar si hay productos
$products = get_posts( array(
    'post_type' => 'product',
    'post_status' => 'publish',
    'numberposts' => 5
) );

if ( empty( $products ) ) {
    echo "❌ No hay productos disponibles<br>";
    exit;
}

echo "✅ Encontrados " . count( $products ) . " productos<br>";

// Crear solo 3 pedidos simples
echo "<h2>📦 Creando 3 Pedidos de Prueba...</h2>";

$created_orders = 0;

for ( $i = 1; $i <= 3; $i++ ) {
    try {
        // Crear pedido básico
        $order = wc_create_order();
        
        if ( is_wp_error( $order ) ) {
            echo "❌ Error creando pedido {$i}: " . $order->get_error_message() . "<br>";
            continue;
        }
        
        // Agregar un producto aleatorio
        $random_product = $products[ array_rand( $products ) ];
        $order->add_product( wc_get_product( $random_product->ID ), 1 );
        
        // Configurar datos básicos
        $order->set_billing_first_name( 'Cliente' );
        $order->set_billing_last_name( 'Prueba ' . $i );
        $order->set_billing_email( 'cliente' . $i . '@example.com' );
        $order->set_billing_country( 'VE' );
        
        // Configurar estado
        $statuses = array( 'wc-completed', 'wc-processing', 'wc-pending' );
        $order->set_status( $statuses[ array_rand( $statuses ) ] );
        
        // Configurar fecha (últimos 15 días)
        $days_ago = rand( 0, 15 );
        $order_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_ago} days" ) );
        $order->set_date_created( $order_date );
        
        // Calcular totales
        $order->calculate_totals();
        
        // Guardar
        $order_id = $order->save();
        
        if ( $order_id ) {
            $created_orders++;
            echo "✅ Pedido #{$order_id} creado - Total: $" . number_format( $order->get_total(), 2 ) . " - Estado: " . $order->get_status() . "<br>";
        }
        
    } catch ( Exception $e ) {
        echo "❌ Error en pedido {$i}: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>📊 Resumen</h2>";
echo "✅ Pedidos creados: {$created_orders}<br>";

if ( $created_orders > 0 ) {
    echo "<h2>🎯 Próximos Pasos</h2>";
    echo "1. Ve a <strong>WooCommerce > Pedidos</strong> para ver los pedidos<br>";
    echo "2. Ve a <strong>WVP 2025 > Análisis</strong> para ver las gráficas<br>";
    echo "3. Las gráficas ahora mostrarán datos reales<br>";
    
    echo "<h2>🔄 Verificar Datos</h2>";
    echo "<a href='check-real-data.php' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Verificar Datos</a>";
    echo "<a href='http://new-kinta-electric.local/wp-admin/admin.php?page=wvp-analytics' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Gráficas</a>";
} else {
    echo "❌ No se pudieron crear pedidos<br>";
}

echo "<hr>";
echo "<p><strong>Script ejecutado:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
