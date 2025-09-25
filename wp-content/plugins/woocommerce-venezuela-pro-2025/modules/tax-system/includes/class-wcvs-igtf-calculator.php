<?php
/**
 * Calculadora de IGTF - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para calcular IGTF venezolano
 */
class WCVS_IGTF_Calculator {

    /**
     * Configuraciones
     *
     * @var array
     */
    private $settings;

    /**
     * Tasa de IGTF
     *
     * @var float
     */
    private $igtf_rate = 3.0;

    /**
     * Monto mínimo para aplicar IGTF
     *
     * @var float
     */
    private $minimum_amount = 100.0;

    /**
     * Monedas extranjeras sujetas a IGTF
     *
     * @var array
     */
    private $foreign_currencies = array(
        'USD' => 'Dólar Americano',
        'EUR' => 'Euro',
        'CAD' => 'Dólar Canadiense',
        'GBP' => 'Libra Esterlina',
    );

    /**
     * Exenciones de IGTF
     *
     * @var array
     */
    private $exemptions = array(
        'importaciones_medicamentos',
        'importaciones_alimentos',
        'importaciones_libros',
        'transacciones_gobierno',
    );

    /**
     * Constructor
     *
     * @param array $settings Configuraciones
     */
    public function __construct($settings = array()) {
        $this->settings = $settings;
        $this->igtf_rate = $settings['igtf_rate'] ?? 3.0;
        $this->minimum_amount = $settings['igtf_minimum_amount'] ?? 100.0;
        
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cálculo de IGTF en carrito
        add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_cart_igtf'));
        
        // Hook para cálculo de IGTF en pedido
        add_action('woocommerce_order_calculate_totals', array($this, 'calculate_order_igtf'));
        
        // Hook para mostrar IGTF en checkout
        add_action('woocommerce_review_order_after_order_total', array($this, 'display_igtf_breakdown'));
        
        // Hook para mostrar IGTF en admin
        add_action('woocommerce_admin_order_totals_after_total', array($this, 'display_admin_igtf_breakdown'));
    }

    /**
     * Calcular IGTF del carrito
     */
    public function calculate_cart_igtf() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        $igtf_data = $this->calculate_igtf_for_cart($cart);
        
        if ($igtf_data['amount'] > 0) {
            $cart->add_fee('IGTF (' . $this->igtf_rate . '%)', $igtf_data['amount']);
        }
    }

    /**
     * Calcular IGTF para el carrito
     *
     * @param WC_Cart $cart Carrito
     * @return array
     */
    private function calculate_igtf_for_cart($cart) {
        $currency = get_woocommerce_currency();
        
        // IGTF solo se aplica a monedas extranjeras
        if (!isset($this->foreign_currencies[$currency])) {
            return array(
                'base_amount' => 0,
                'exempt_amount' => 0,
                'rate' => $this->igtf_rate,
                'amount' => 0,
                'currency' => $currency,
                'applies' => false,
            );
        }

        $subtotal = $cart->get_subtotal();
        
        // Verificar monto mínimo
        if ($subtotal < $this->minimum_amount) {
            return array(
                'base_amount' => $subtotal,
                'exempt_amount' => 0,
                'rate' => $this->igtf_rate,
                'amount' => 0,
                'currency' => $currency,
                'applies' => false,
                'reason' => 'below_minimum',
            );
        }

        // Calcular exenciones
        $exempt_amount = $this->calculate_cart_exemptions($cart);
        $taxable_amount = $subtotal - $exempt_amount;
        
        $igtf_amount = $taxable_amount * ($this->igtf_rate / 100);
        
        return array(
            'base_amount' => $subtotal,
            'exempt_amount' => $exempt_amount,
            'taxable_amount' => $taxable_amount,
            'rate' => $this->igtf_rate,
            'amount' => $igtf_amount,
            'currency' => $currency,
            'applies' => true,
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
            
            if ($this->is_product_igtf_exempt($product)) {
                $exempt_amount += $item_total;
            }
        }
        
        return $exempt_amount;
    }

    /**
     * Calcular IGTF del pedido
     *
     * @param WC_Order $order Pedido
     */
    public function calculate_order_igtf($order) {
        $igtf_data = $this->calculate_igtf_for_order($order);
        
        // Guardar datos de IGTF en el pedido
        $order->update_meta_data('_igtf_base_amount', $igtf_data['base_amount']);
        $order->update_meta_data('_igtf_exempt_amount', $igtf_data['exempt_amount']);
        $order->update_meta_data('_igtf_taxable_amount', $igtf_data['taxable_amount']);
        $order->update_meta_data('_igtf_rate', $igtf_data['rate']);
        $order->update_meta_data('_igtf_amount', $igtf_data['amount']);
        $order->update_meta_data('_igtf_currency', $igtf_data['currency']);
        $order->update_meta_data('_igtf_applies', $igtf_data['applies']);
        
        $order->save();
    }

    /**
     * Calcular IGTF para el pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function calculate_igtf_for_order($order) {
        $currency = $order->get_currency();
        
        // IGTF solo se aplica a monedas extranjeras
        if (!isset($this->foreign_currencies[$currency])) {
            return array(
                'base_amount' => 0,
                'exempt_amount' => 0,
                'taxable_amount' => 0,
                'rate' => $this->igtf_rate,
                'amount' => 0,
                'currency' => $currency,
                'applies' => false,
            );
        }

        $subtotal = $order->get_subtotal();
        
        // Verificar monto mínimo
        if ($subtotal < $this->minimum_amount) {
            return array(
                'base_amount' => $subtotal,
                'exempt_amount' => 0,
                'taxable_amount' => 0,
                'rate' => $this->igtf_rate,
                'amount' => 0,
                'currency' => $currency,
                'applies' => false,
                'reason' => 'below_minimum',
            );
        }

        // Calcular exenciones
        $exempt_amount = $this->calculate_order_exemptions($order);
        $taxable_amount = $subtotal - $exempt_amount;
        
        $igtf_amount = $taxable_amount * ($this->igtf_rate / 100);
        
        return array(
            'base_amount' => $subtotal,
            'exempt_amount' => $exempt_amount,
            'taxable_amount' => $taxable_amount,
            'rate' => $this->igtf_rate,
            'amount' => $igtf_amount,
            'currency' => $currency,
            'applies' => true,
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
            
            if ($this->is_product_igtf_exempt($product)) {
                $exempt_amount += $item_total;
            }
        }
        
        return $exempt_amount;
    }

    /**
     * Verificar si un producto está exento de IGTF
     *
     * @param WC_Product $product Producto
     * @return bool
     */
    private function is_product_igtf_exempt($product) {
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
        $is_exempt = $product->get_meta('_igtf_exempt');
        if ($is_exempt === 'yes') {
            return true;
        }

        // Verificar por clase de impuesto
        $tax_class = $product->get_tax_class();
        if ($tax_class === 'igtf-exempt') {
            return true;
        }

        return false;
    }

    /**
     * Mostrar desglose de IGTF en checkout
     */
    public function display_igtf_breakdown() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        $igtf_data = $this->calculate_igtf_for_cart($cart);
        
        if ($igtf_data['applies'] && $igtf_data['amount'] > 0) {
            ?>
            <tr class="wcvs-igtf-breakdown">
                <th>Base Imponible IGTF</th>
                <td><?php echo wc_price($igtf_data['base_amount'], array('currency' => $igtf_data['currency'])); ?></td>
            </tr>
            <?php if ($igtf_data['exempt_amount'] > 0): ?>
            <tr class="wcvs-igtf-breakdown">
                <th>Exento de IGTF</th>
                <td><?php echo wc_price($igtf_data['exempt_amount'], array('currency' => $igtf_data['currency'])); ?></td>
            </tr>
            <tr class="wcvs-igtf-breakdown">
                <th>Monto Gravable</th>
                <td><?php echo wc_price($igtf_data['taxable_amount'], array('currency' => $igtf_data['currency'])); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="wcvs-igtf-breakdown">
                <th>IGTF (<?php echo esc_html($igtf_data['rate']); ?>%)</th>
                <td><?php echo wc_price($igtf_data['amount'], array('currency' => $igtf_data['currency'])); ?></td>
            </tr>
            <?php
        } elseif ($igtf_data['applies'] && isset($igtf_data['reason']) && $igtf_data['reason'] === 'below_minimum') {
            ?>
            <tr class="wcvs-igtf-breakdown">
                <th>IGTF</th>
                <td><small>No aplica (monto menor a <?php echo wc_price($this->minimum_amount, array('currency' => $igtf_data['currency'])); ?>)</small></td>
            </tr>
            <?php
        }
    }

    /**
     * Mostrar desglose de IGTF en admin
     *
     * @param int $order_id ID del pedido
     */
    public function display_admin_igtf_breakdown($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $igtf_base = $order->get_meta('_igtf_base_amount');
        $igtf_exempt = $order->get_meta('_igtf_exempt_amount');
        $igtf_taxable = $order->get_meta('_igtf_taxable_amount');
        $igtf_rate = $order->get_meta('_igtf_rate');
        $igtf_amount = $order->get_meta('_igtf_amount');
        $igtf_currency = $order->get_meta('_igtf_currency');
        $igtf_applies = $order->get_meta('_igtf_applies');
        
        if ($igtf_applies && $igtf_amount > 0) {
            ?>
            <tr class="wcvs-igtf-breakdown">
                <td class="label">Base Imponible IGTF:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($igtf_base, array('currency' => $igtf_currency)); ?></td>
            </tr>
            <?php if ($igtf_exempt > 0): ?>
            <tr class="wcvs-igtf-breakdown">
                <td class="label">Exento de IGTF:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($igtf_exempt, array('currency' => $igtf_currency)); ?></td>
            </tr>
            <tr class="wcvs-igtf-breakdown">
                <td class="label">Monto Gravable:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($igtf_taxable, array('currency' => $igtf_currency)); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="wcvs-igtf-breakdown">
                <td class="label">IGTF (<?php echo esc_html($igtf_rate); ?>%):</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($igtf_amount, array('currency' => $igtf_currency)); ?></td>
            </tr>
            <?php
        }
    }

    /**
     * Calcular IGTF para un monto específico
     *
     * @param float $amount Monto
     * @param string $currency Moneda
     * @param bool $is_exempt Si está exento
     * @return array
     */
    public function calculate_igtf_amount($amount, $currency, $is_exempt = false) {
        // Verificar si la moneda está sujeta a IGTF
        if (!isset($this->foreign_currencies[$currency])) {
            return array(
                'base_amount' => $amount,
                'exempt_amount' => 0,
                'taxable_amount' => 0,
                'rate' => $this->igtf_rate,
                'amount' => 0,
                'currency' => $currency,
                'applies' => false,
            );
        }

        // Verificar monto mínimo
        if ($amount < $this->minimum_amount) {
            return array(
                'base_amount' => $amount,
                'exempt_amount' => 0,
                'taxable_amount' => 0,
                'rate' => $this->igtf_rate,
                'amount' => 0,
                'currency' => $currency,
                'applies' => false,
                'reason' => 'below_minimum',
            );
        }

        if ($is_exempt) {
            return array(
                'base_amount' => $amount,
                'exempt_amount' => $amount,
                'taxable_amount' => 0,
                'rate' => $this->igtf_rate,
                'amount' => 0,
                'currency' => $currency,
                'applies' => true,
            );
        }

        $igtf_amount = $amount * ($this->igtf_rate / 100);
        
        return array(
            'base_amount' => $amount,
            'exempt_amount' => 0,
            'taxable_amount' => $amount,
            'rate' => $this->igtf_rate,
            'amount' => $igtf_amount,
            'currency' => $currency,
            'applies' => true,
        );
    }

    /**
     * Obtener tasa de IGTF
     *
     * @return float
     */
    public function get_igtf_rate() {
        return $this->igtf_rate;
    }

    /**
     * Establecer tasa de IGTF
     *
     * @param float $rate Tasa
     */
    public function set_igtf_rate($rate) {
        $this->igtf_rate = floatval($rate);
    }

    /**
     * Obtener monto mínimo
     *
     * @return float
     */
    public function get_minimum_amount() {
        return $this->minimum_amount;
    }

    /**
     * Establecer monto mínimo
     *
     * @param float $amount Monto
     */
    public function set_minimum_amount($amount) {
        $this->minimum_amount = floatval($amount);
    }

    /**
     * Obtener monedas extranjeras
     *
     * @return array
     */
    public function get_foreign_currencies() {
        return $this->foreign_currencies;
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
     * Generar reporte de IGTF
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_igtf_report($start_date, $end_date) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.ID as order_id,
                p.post_date as order_date,
                pm1.meta_value as igtf_base_amount,
                pm2.meta_value as igtf_exempt_amount,
                pm3.meta_value as igtf_taxable_amount,
                pm4.meta_value as igtf_rate,
                pm5.meta_value as igtf_amount,
                pm6.meta_value as igtf_currency
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_igtf_base_amount'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_igtf_exempt_amount'
            LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_igtf_taxable_amount'
            LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_igtf_rate'
            LEFT JOIN {$wpdb->postmeta} pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_igtf_amount'
            LEFT JOIN {$wpdb->postmeta} pm6 ON p.ID = pm6.post_id AND pm6.meta_key = '_igtf_currency'
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date >= %s
            AND p.post_date <= %s
            AND pm6.meta_value IN ('USD', 'EUR', 'CAD', 'GBP')
            ORDER BY p.post_date DESC
        ", $start_date, $end_date));
        
        $total_base = 0;
        $total_exempt = 0;
        $total_taxable = 0;
        $total_igtf = 0;
        
        foreach ($results as $result) {
            $total_base += floatval($result->igtf_base_amount ?? 0);
            $total_exempt += floatval($result->igtf_exempt_amount ?? 0);
            $total_taxable += floatval($result->igtf_taxable_amount ?? 0);
            $total_igtf += floatval($result->igtf_amount ?? 0);
        }
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'summary' => array(
                'total_base_amount' => $total_base,
                'total_exempt_amount' => $total_exempt,
                'total_taxable_amount' => $total_taxable,
                'total_igtf_collected' => $total_igtf,
                'igtf_rate' => $this->igtf_rate,
                'minimum_amount' => $this->minimum_amount,
            ),
            'orders' => $results,
        );
    }
}
