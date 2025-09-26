<?php
/**
 * Script para crear pedidos de prueba con datos realistas
 */

// Incluir WordPress
require_once '../../../wp-config.php';

echo "<h1>🛒 Generador de Pedidos de Prueba</h1>";

// Verificar si WooCommerce está activo
if ( ! class_exists( 'WooCommerce' ) ) {
    echo "❌ WooCommerce no está activo<br>";
    exit;
}

// Verificar si hay productos
$products = get_posts( array(
    'post_type' => 'product',
    'post_status' => 'publish',
    'numberposts' => 10
) );

if ( empty( $products ) ) {
    echo "❌ No hay productos disponibles para crear pedidos<br>";
    exit;
}

echo "✅ Encontrados " . count( $products ) . " productos<br>";

// Crear pedidos de prueba
$order_statuses = array( 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending' );
$payment_methods = array( 'bacs', 'cod', 'cheque' );

echo "<h2>📦 Creando Pedidos de Prueba...</h2>";

$created_orders = 0;
$total_sales = 0;

// Crear pedidos para los últimos 30 días
for ( $i = 0; $i < 15; $i++ ) {
    // Fecha aleatoria en los últimos 30 días
    $days_ago = rand( 0, 30 );
    $order_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_ago} days" ) );
    
    // Productos aleatorios
    $product_count = rand( 1, 3 );
    $selected_products = array_slice( $products, 0, $product_count );
    
    // Crear pedido
    $order = wc_create_order();
    
    if ( is_wp_error( $order ) ) {
        echo "❌ Error creando pedido: " . $order->get_error_message() . "<br>";
        continue;
    }
    
    // Agregar productos
    $order_total = 0;
    foreach ( $selected_products as $product ) {
        $quantity = rand( 1, 3 );
        $order->add_product( wc_get_product( $product->ID ), $quantity );
        $order_total += wc_get_product( $product->ID )->get_price() * $quantity;
    }
    
    // Configurar dirección de envío
    $order->set_billing_first_name( 'Cliente' );
    $order->set_billing_last_name( 'Prueba ' . ($i + 1) );
    $order->set_billing_email( 'cliente' . ($i + 1) . '@example.com' );
    $order->set_billing_phone( '+58-412-' . str_pad( rand( 1000000, 9999999 ), 7, '0', STR_PAD_LEFT ) );
    $order->set_billing_address_1( 'Av. Principal' );
    $order->set_billing_city( 'Caracas' );
    $order->set_billing_state( 'Distrito Capital' );
    $order->set_billing_postcode( '1010' );
    $order->set_billing_country( 'VE' );
    
    // Configurar dirección de envío
    $order->set_shipping_first_name( 'Cliente' );
    $order->set_shipping_last_name( 'Prueba ' . ($i + 1) );
    $order->set_shipping_address_1( 'Av. Principal' );
    $order->set_shipping_city( 'Caracas' );
    $order->set_shipping_state( 'Distrito Capital' );
    $order->set_shipping_postcode( '1010' );
    $order->set_shipping_country( 'VE' );
    
    // Configurar método de pago
    $payment_method = $payment_methods[ array_rand( $payment_methods ) ];
    $order->set_payment_method( $payment_method );
    $order->set_payment_method_title( ucfirst( $payment_method ) );
    
    // Configurar estado
    $status = $order_statuses[ array_rand( $order_statuses ) ];
    $order->set_status( $status );
    
    // Configurar fecha
    $order->set_date_created( $order_date );
    
    // Calcular totales
    $order->calculate_totals();
    
    // Guardar pedido
    $order_id = $order->save();
    
    if ( $order_id ) {
        $created_orders++;
        $total_sales += $order->get_total();
        echo "✅ Pedido #{$order_id} creado - Total: $" . number_format( $order->get_total(), 2 ) . " - Estado: {$status} - Fecha: {$order_date}<br>";
    }
}

echo "<h2>📊 Resumen</h2>";
echo "✅ Pedidos creados: {$created_orders}<br>";
echo "💰 Total en ventas: $" . number_format( $total_sales, 2 ) . "<br>";

if ( $created_orders > 0 ) {
    echo "<h2>🎯 Próximos Pasos</h2>";
    echo "1. Ve a <strong>WooCommerce > Pedidos</strong> para ver los pedidos creados<br>";
    echo "2. Ve a <strong>WVP 2025 > Análisis</strong> para ver las gráficas con datos reales<br>";
    echo "3. Las gráficas ahora mostrarán datos reales en lugar de datos de demostración<br>";
    
    echo "<h2>🔄 Actualizar Gráficas</h2>";
    echo "<a href='http://new-kinta-electric.local/wp-admin/admin.php?page=wvp-analytics' target='_blank' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Gráficas con Datos Reales</a>";
} else {
    echo "❌ No se pudieron crear pedidos<br>";
}

echo "<hr>";
echo "<p><strong>Script ejecutado:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
