<?php
/**
 * Calculadora de ISLR - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para calcular ISLR venezolano
 */
class WCVS_ISLR_Calculator {

    /**
     * Configuraciones
     *
     * @var array
     */
    private $settings;

    /**
     * Tabla progresiva de ISLR
     *
     * @var array
     */
    private $islr_table = array(
        array('min' => 0, 'max' => 1000, 'rate' => 0.06, 'fixed' => 0),
        array('min' => 1000, 'max' => 3000, 'rate' => 0.09, 'fixed' => 60),
        array('min' => 3000, 'max' => 6000, 'rate' => 0.12, 'fixed' => 240),
        array('min' => 6000, 'max' => 10000, 'rate' => 0.16, 'fixed' => 600),
        array('min' => 10000, 'max' => 20000, 'rate' => 0.20, 'fixed' => 1240),
        array('min' => 20000, 'max' => 30000, 'rate' => 0.24, 'fixed' => 3240),
        array('min' => 30000, 'max' => 50000, 'rate' => 0.29, 'fixed' => 5640),
        array('min' => 50000, 'max' => 100000, 'rate' => 0.34, 'fixed' => 11440),
        array('min' => 100000, 'max' => PHP_FLOAT_MAX, 'rate' => 0.34, 'fixed' => 28440),
    );

    /**
     * Exenciones de ISLR
     *
     * @var array
     */
    private $exemptions = array(
        'productos_agricolas',
        'productos_artesanales',
        'servicios_educativos',
        'servicios_medicos',
    );

    /**
     * Constructor
     *
     * @param array $settings Configuraciones
     */
    public function __construct($settings = array()) {
        $this->settings = $settings;
        
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cálculo de ISLR en carrito
        add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_cart_islr'));
        
        // Hook para cálculo de ISLR en pedido
        add_action('woocommerce_order_calculate_totals', array($this, 'calculate_order_islr'));
        
        // Hook para mostrar ISLR en checkout
        add_action('woocommerce_review_order_after_order_total', array($this, 'display_islr_breakdown'));
        
        // Hook para mostrar ISLR en admin
        add_action('woocommerce_admin_order_totals_after_total', array($this, 'display_admin_islr_breakdown'));
    }

    /**
     * Calcular ISLR del carrito
     */
    public function calculate_cart_islr() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        $islr_data = $this->calculate_islr_for_cart($cart);
        
        if ($islr_data['amount'] > 0) {
            $cart->add_fee('ISLR', $islr_data['amount']);
        }
    }

    /**
     * Calcular ISLR para el carrito
     *
     * @param WC_Cart $cart Carrito
     * @return array
     */
    private function calculate_islr_for_cart($cart) {
        $subtotal = $cart->get_subtotal();
        
        // Calcular exenciones
        $exempt_amount = $this->calculate_cart_exemptions($cart);
        $taxable_amount = $subtotal - $exempt_amount;
        
        // Calcular ISLR usando tabla progresiva
        $islr_amount = $this->calculate_progressive_islr($taxable_amount);
        
        return array(
            'base_amount' => $subtotal,
            'exempt_amount' => $exempt_amount,
            'taxable_amount' => $taxable_amount,
            'amount' => $islr_amount,
            'effective_rate' => $taxable_amount > 0 ? ($islr_amount / $taxable_amount) * 100 : 0,
        );
    }

    /**
     * Calcular exenciones del carrito
     *
     * @param WC_Cart $cart Carrito
     * @return float
     */
    private function calculate_cart_exemptions($cart) {
        $exempt_amount = 0;
        
        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            $item_total = $product->get_price() * $quantity;
            
            if ($this->is_product_islr_exempt($product)) {
                $exempt_amount += $item_total;
            }
        }
        
        return $exempt_amount;
    }

    /**
     * Calcular ISLR del pedido
     *
     * @param WC_Order $order Pedido
     */
    public function calculate_order_islr($order) {
        $islr_data = $this->calculate_islr_for_order($order);
        
        // Guardar datos de ISLR en el pedido
        $order->update_meta_data('_islr_base_amount', $islr_data['base_amount']);
        $order->update_meta_data('_islr_exempt_amount', $islr_data['exempt_amount']);
        $order->update_meta_data('_islr_taxable_amount', $islr_data['taxable_amount']);
        $order->update_meta_data('_islr_amount', $islr_data['amount']);
        $order->update_meta_data('_islr_effective_rate', $islr_data['effective_rate']);
        
        $order->save();
    }

    /**
     * Calcular ISLR para el pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function calculate_islr_for_order($order) {
        $subtotal = $order->get_subtotal();
        
        // Calcular exenciones
        $exempt_amount = $this->calculate_order_exemptions($order);
        $taxable_amount = $subtotal - $exempt_amount;
        
        // Calcular ISLR usando tabla progresiva
        $islr_amount = $this->calculate_progressive_islr($taxable_amount);
        
        return array(
            'base_amount' => $subtotal,
            'exempt_amount' => $exempt_amount,
            'taxable_amount' => $taxable_amount,
            'amount' => $islr_amount,
            'effective_rate' => $taxable_amount > 0 ? ($islr_amount / $taxable_amount) * 100 : 0,
        );
    }

    /**
     * Calcular exenciones del pedido
     *
     * @param WC_Order $order Pedido
     * @return float
     */
    private function calculate_order_exemptions($order) {
        $exempt_amount = 0;
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $item_total = $item->get_total();
            
            if ($this->is_product_islr_exempt($product)) {
                $exempt_amount += $item_total;
            }
        }
        
        return $exempt_amount;
    }

    /**
     * Calcular ISLR progresivo
     *
     * @param float $amount Monto
     * @return float
     */
    private function calculate_progressive_islr($amount) {
        if ($amount <= 0) {
            return 0;
        }

        $total_tax = 0;
        $remaining_amount = $amount;

        foreach ($this->islr_table as $bracket) {
            if ($remaining_amount <= 0) {
                break;
            }

            $bracket_range = $bracket['max'] - $bracket['min'];
            $bracket_amount = min($remaining_amount, $bracket_range);
            
            if ($bracket_amount > 0) {
                $bracket_tax = ($bracket_amount * $bracket['rate']) + $bracket['fixed'];
                $total_tax += $bracket_tax;
                $remaining_amount -= $bracket_amount;
            }
        }

        return $total_tax;
    }

    /**
     * Verificar si un producto está exento de ISLR
     *
     * @param WC_Product $product Producto
     * @return bool
     */
    private function is_product_islr_exempt($product) {
        if (!$product) {
            return false;
        }

        // Verificar por categoría
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
        foreach ($categories as $category) {
            if (in_array($category, $this->exemptions)) {
                return true;
            }
        }

        // Verificar por meta del producto
        $is_exempt = $product->get_meta('_islr_exempt');
        if ($is_exempt === 'yes') {
            return true;
        }

        // Verificar por clase de impuesto
        $tax_class = $product->get_tax_class();
        if ($tax_class === 'islr-exempt') {
            return true;
        }

        return false;
    }

    /**
     * Mostrar desglose de ISLR en checkout
     */
    public function display_islr_breakdown() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        $islr_data = $this->calculate_islr_for_cart($cart);
        
        if ($islr_data['amount'] > 0) {
            ?>
            <tr class="wcvs-islr-breakdown">
                <th>Base Imponible ISLR</th>
                <td><?php echo wc_price($islr_data['base_amount']); ?></td>
            </tr>
            <?php if ($islr_data['exempt_amount'] > 0): ?>
            <tr class="wcvs-islr-breakdown">
                <th>Exento de ISLR</th>
                <td><?php echo wc_price($islr_data['exempt_amount']); ?></td>
            </tr>
            <tr class="wcvs-islr-breakdown">
                <th>Monto Gravable</th>
                <td><?php echo wc_price($islr_data['taxable_amount']); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="wcvs-islr-breakdown">
                <th>ISLR (<?php echo esc_html(number_format($islr_data['effective_rate'], 2)); ?>%)</th>
                <td><?php echo wc_price($islr_data['amount']); ?></td>
            </tr>
            <?php
        }
    }

    /**
     * Mostrar desglose de ISLR en admin
     *
     * @param int $order_id ID del pedido
     */
    public function display_admin_islr_breakdown($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $islr_base = $order->get_meta('_islr_base_amount');
        $islr_exempt = $order->get_meta('_islr_exempt_amount');
        $islr_taxable = $order->get_meta('_islr_taxable_amount');
        $islr_amount = $order->get_meta('_islr_amount');
        $islr_effective_rate = $order->get_meta('_islr_effective_rate');
        
        if ($islr_amount > 0) {
            ?>
            <tr class="wcvs-islr-breakdown">
                <td class="label">Base Imponible ISLR:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($islr_base); ?></td>
            </tr>
            <?php if ($islr_exempt > 0): ?>
            <tr class="wcvs-islr-breakdown">
                <td class="label">Exento de ISLR:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($islr_exempt); ?></td>
            </tr>
            <tr class="wcvs-islr-breakdown">
                <td class="label">Monto Gravable:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($islr_taxable); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="wcvs-islr-breakdown">
                <td class="label">ISLR (<?php echo esc_html(number_format($islr_effective_rate, 2)); ?>%):</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($islr_amount); ?></td>
            </tr>
            <?php
        }
    }

    /**
     * Calcular ISLR para un monto específico
     *
     * @param float $amount Monto
     * @param bool $is_exempt Si está exento
     * @return array
     */
    public function calculate_islr_amount($amount, $is_exempt = false) {
        if ($is_exempt) {
            return array(
                'base_amount' => $amount,
                'exempt_amount' => $amount,
                'taxable_amount' => 0,
                'amount' => 0,
                'effective_rate' => 0,
            );
        }

        $islr_amount = $this->calculate_progressive_islr($amount);
        $effective_rate = $amount > 0 ? ($islr_amount / $amount) * 100 : 0;
        
        return array(
            'base_amount' => $amount,
            'exempt_amount' => 0,
            'taxable_amount' => $amount,
            'amount' => $islr_amount,
            'effective_rate' => $effective_rate,
        );
    }

    /**
     * Obtener tabla de ISLR
     *
     * @return array
     */
    public function get_islr_table() {
        return $this->islr_table;
    }

    /**
     * Establecer tabla de ISLR
     *
     * @param array $table Tabla
     */
    public function set_islr_table($table) {
        $this->islr_table = $table;
    }

    /**
     * Obtener exenciones
     *
     * @return array
     */
    public function get_exemptions() {
        return $this->exemptions;
    }

    /**
     * Añadir exención
     *
     * @param string $exemption Exención
     */
    public function add_exemption($exemption) {
        if (!in_array($exemption, $this->exemptions)) {
            $this->exemptions[] = $exemption;
        }
    }

    /**
     * Remover exención
     *
     * @param string $exemption Exención
     */
    public function remove_exemption($exemption) {
        $key = array_search($exemption, $this->exemptions);
        if ($key !== false) {
            unset($this->exemptions[$key]);
        }
    }

    /**
     * Generar reporte de ISLR
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_islr_report($start_date, $end_date) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.ID as order_id,
                p.post_date as order_date,
                pm1.meta_value as islr_base_amount,
                pm2.meta_value as islr_exempt_amount,
                pm3.meta_value as islr_taxable_amount,
                pm4.meta_value as islr_amount,
                pm5.meta_value as islr_effective_rate
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_islr_base_amount'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_islr_exempt_amount'
            LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_islr_taxable_amount'
            LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_islr_amount'
            LEFT JOIN {$wpdb->postmeta} pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_islr_effective_rate'
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date >= %s
            AND p.post_date <= %s
            ORDER BY p.post_date DESC
        ", $start_date, $end_date));
        
        $total_base = 0;
        $total_exempt = 0;
        $total_taxable = 0;
        $total_islr = 0;
        
        foreach ($results as $result) {
            $total_base += floatval($result->islr_base_amount ?? 0);
            $total_exempt += floatval($result->islr_exempt_amount ?? 0);
            $total_taxable += floatval($result->islr_taxable_amount ?? 0);
            $total_islr += floatval($result->islr_amount ?? 0);
        }
        
        $average_rate = $total_taxable > 0 ? ($total_islr / $total_taxable) * 100 : 0;
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'summary' => array(
                'total_base_amount' => $total_base,
                'total_exempt_amount' => $total_exempt,
                'total_taxable_amount' => $total_taxable,
                'total_islr_collected' => $total_islr,
                'average_effective_rate' => $average_rate,
            ),
            'orders' => $results,
        );
    }

    /**
     * Obtener información de bracket de ISLR
     *
     * @param float $amount Monto
     * @return array
     */
    public function get_islr_bracket_info($amount) {
        foreach ($this->islr_table as $bracket) {
            if ($amount >= $bracket['min'] && $amount < $bracket['max']) {
                return array(
                    'bracket' => $bracket,
                    'tax_amount' => ($amount * $bracket['rate']) + $bracket['fixed'],
                    'effective_rate' => ($amount > 0) ? ((($amount * $bracket['rate']) + $bracket['fixed']) / $amount) * 100 : 0,
                );
            }
        }
        
        return null;
    }
}
