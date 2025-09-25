<?php
/**
 * Calculadora de IVA - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para calcular IVA venezolano
 */
class WCVS_IVA_Calculator {

    /**
     * Configuraciones
     *
     * @var array
     */
    private $settings;

    /**
     * Tasa de IVA
     *
     * @var float
     */
    private $iva_rate = 16.0;

    /**
     * Productos exentos de IVA
     *
     * @var array
     */
    private $exempt_products = array(
        'alimentos_basicos',
        'medicamentos',
        'libros',
        'productos_agricolas',
    );

    /**
     * Constructor
     *
     * @param array $settings Configuraciones
     */
    public function __construct($settings = array()) {
        $this->settings = $settings;
        $this->iva_rate = $settings['iva_rate'] ?? 16.0;
        
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cálculo de IVA en carrito
        add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_cart_iva'));
        
        // Hook para cálculo de IVA en pedido
        add_action('woocommerce_order_calculate_totals', array($this, 'calculate_order_iva'));
        
        // Hook para mostrar IVA en checkout
        add_action('woocommerce_review_order_after_order_total', array($this, 'display_iva_breakdown'));
        
        // Hook para mostrar IVA en admin
        add_action('woocommerce_admin_order_totals_after_total', array($this, 'display_admin_iva_breakdown'));
    }

    /**
     * Calcular IVA del carrito
     */
    public function calculate_cart_iva() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        $iva_data = $this->calculate_iva_for_cart($cart);
        
        if ($iva_data['amount'] > 0) {
            $cart->add_fee('IVA (' . $this->iva_rate . '%)', $iva_data['amount']);
        }
    }

    /**
     * Calcular IVA para el carrito
     *
     * @param WC_Cart $cart Carrito
     * @return array
     */
    private function calculate_iva_for_cart($cart) {
        $subtotal = 0;
        $exempt_amount = 0;
        
        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            $item_total = $product->get_price() * $quantity;
            
            if ($this->is_product_exempt($product)) {
                $exempt_amount += $item_total;
            } else {
                $subtotal += $item_total;
            }
        }
        
        $iva_amount = $subtotal * ($this->iva_rate / 100);
        
        return array(
            'base_amount' => $subtotal,
            'exempt_amount' => $exempt_amount,
            'rate' => $this->iva_rate,
            'amount' => $iva_amount,
        );
    }

    /**
     * Calcular IVA del pedido
     *
     * @param WC_Order $order Pedido
     */
    public function calculate_order_iva($order) {
        $iva_data = $this->calculate_iva_for_order($order);
        
        // Guardar datos de IVA en el pedido
        $order->update_meta_data('_iva_base_amount', $iva_data['base_amount']);
        $order->update_meta_data('_iva_exempt_amount', $iva_data['exempt_amount']);
        $order->update_meta_data('_iva_rate', $iva_data['rate']);
        $order->update_meta_data('_iva_amount', $iva_data['amount']);
        
        $order->save();
    }

    /**
     * Calcular IVA para el pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function calculate_iva_for_order($order) {
        $subtotal = 0;
        $exempt_amount = 0;
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $item_total = $item->get_total();
            
            if ($this->is_product_exempt($product)) {
                $exempt_amount += $item_total;
            } else {
                $subtotal += $item_total;
            }
        }
        
        $iva_amount = $subtotal * ($this->iva_rate / 100);
        
        return array(
            'base_amount' => $subtotal,
            'exempt_amount' => $exempt_amount,
            'rate' => $this->iva_rate,
            'amount' => $iva_amount,
        );
    }

    /**
     * Verificar si un producto está exento de IVA
     *
     * @param WC_Product $product Producto
     * @return bool
     */
    private function is_product_exempt($product) {
        if (!$product) {
            return false;
        }

        // Verificar por categoría
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
        foreach ($categories as $category) {
            if (in_array($category, $this->exempt_products)) {
                return true;
            }
        }

        // Verificar por meta del producto
        $is_exempt = $product->get_meta('_iva_exempt');
        if ($is_exempt === 'yes') {
            return true;
        }

        // Verificar por clase de impuesto
        $tax_class = $product->get_tax_class();
        if ($tax_class === 'iva-exempt') {
            return true;
        }

        return false;
    }

    /**
     * Mostrar desglose de IVA en checkout
     */
    public function display_iva_breakdown() {
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        $iva_data = $this->calculate_iva_for_cart($cart);
        
        if ($iva_data['amount'] > 0) {
            ?>
            <tr class="wcvs-iva-breakdown">
                <th>Base Imponible IVA</th>
                <td><?php echo wc_price($iva_data['base_amount']); ?></td>
            </tr>
            <tr class="wcvs-iva-breakdown">
                <th>IVA (<?php echo esc_html($iva_data['rate']); ?>%)</th>
                <td><?php echo wc_price($iva_data['amount']); ?></td>
            </tr>
            <?php if ($iva_data['exempt_amount'] > 0): ?>
            <tr class="wcvs-iva-breakdown">
                <th>Exento de IVA</th>
                <td><?php echo wc_price($iva_data['exempt_amount']); ?></td>
            </tr>
            <?php endif; ?>
            <?php
        }
    }

    /**
     * Mostrar desglose de IVA en admin
     *
     * @param int $order_id ID del pedido
     */
    public function display_admin_iva_breakdown($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $iva_base = $order->get_meta('_iva_base_amount');
        $iva_exempt = $order->get_meta('_iva_exempt_amount');
        $iva_rate = $order->get_meta('_iva_rate');
        $iva_amount = $order->get_meta('_iva_amount');
        
        if ($iva_amount > 0) {
            ?>
            <tr class="wcvs-iva-breakdown">
                <td class="label">Base Imponible IVA:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($iva_base); ?></td>
            </tr>
            <tr class="wcvs-iva-breakdown">
                <td class="label">IVA (<?php echo esc_html($iva_rate); ?>%):</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($iva_amount); ?></td>
            </tr>
            <?php if ($iva_exempt > 0): ?>
            <tr class="wcvs-iva-breakdown">
                <td class="label">Exento de IVA:</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($iva_exempt); ?></td>
            </tr>
            <?php endif; ?>
            <?php
        }
    }

    /**
     * Calcular IVA para un monto específico
     *
     * @param float $amount Monto
     * @param bool $is_exempt Si está exento
     * @return array
     */
    public function calculate_iva_amount($amount, $is_exempt = false) {
        if ($is_exempt) {
            return array(
                'base_amount' => 0,
                'exempt_amount' => $amount,
                'rate' => $this->iva_rate,
                'amount' => 0,
            );
        }

        $iva_amount = $amount * ($this->iva_rate / 100);
        
        return array(
            'base_amount' => $amount,
            'exempt_amount' => 0,
            'rate' => $this->iva_rate,
            'amount' => $iva_amount,
        );
    }

    /**
     * Obtener tasa de IVA
     *
     * @return float
     */
    public function get_iva_rate() {
        return $this->iva_rate;
    }

    /**
     * Establecer tasa de IVA
     *
     * @param float $rate Tasa
     */
    public function set_iva_rate($rate) {
        $this->iva_rate = floatval($rate);
    }

    /**
     * Obtener productos exentos
     *
     * @return array
     */
    public function get_exempt_products() {
        return $this->exempt_products;
    }

    /**
     * Añadir producto exento
     *
     * @param string $product_slug Slug del producto
     */
    public function add_exempt_product($product_slug) {
        if (!in_array($product_slug, $this->exempt_products)) {
            $this->exempt_products[] = $product_slug;
        }
    }

    /**
     * Remover producto exento
     *
     * @param string $product_slug Slug del producto
     */
    public function remove_exempt_product($product_slug) {
        $key = array_search($product_slug, $this->exempt_products);
        if ($key !== false) {
            unset($this->exempt_products[$key]);
        }
    }

    /**
     * Generar reporte de IVA
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_iva_report($start_date, $end_date) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.ID as order_id,
                p.post_date as order_date,
                pm1.meta_value as iva_base_amount,
                pm2.meta_value as iva_exempt_amount,
                pm3.meta_value as iva_rate,
                pm4.meta_value as iva_amount
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_iva_base_amount'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_iva_exempt_amount'
            LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_iva_rate'
            LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_iva_amount'
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date >= %s
            AND p.post_date <= %s
            ORDER BY p.post_date DESC
        ", $start_date, $end_date));
        
        $total_base = 0;
        $total_exempt = 0;
        $total_iva = 0;
        
        foreach ($results as $result) {
            $total_base += floatval($result->iva_base_amount ?? 0);
            $total_exempt += floatval($result->iva_exempt_amount ?? 0);
            $total_iva += floatval($result->iva_amount ?? 0);
        }
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'summary' => array(
                'total_base_amount' => $total_base,
                'total_exempt_amount' => $total_exempt,
                'total_iva_collected' => $total_iva,
                'iva_rate' => $this->iva_rate,
            ),
            'orders' => $results,
        );
    }

    /**
     * Exportar reporte de IVA
     *
     * @param array $report_data Datos del reporte
     * @param string $format Formato (csv, excel, pdf)
     * @return string URL del archivo generado
     */
    public function export_iva_report($report_data, $format = 'csv') {
        $filename = 'iva_report_' . date('Y-m-d_H-i-s') . '.' . $format;
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        switch ($format) {
            case 'csv':
                $this->export_iva_csv($report_data, $file_path);
                break;
            case 'excel':
                $this->export_iva_excel($report_data, $file_path);
                break;
            case 'pdf':
                $this->export_iva_pdf($report_data, $file_path);
                break;
        }
        
        return $upload_dir['url'] . '/' . $filename;
    }

    /**
     * Exportar IVA a CSV
     *
     * @param array $report_data Datos del reporte
     * @param string $file_path Ruta del archivo
     */
    private function export_iva_csv($report_data, $file_path) {
        $file = fopen($file_path, 'w');
        
        // Encabezados
        fputcsv($file, array(
            'Pedido',
            'Fecha',
            'Base Imponible',
            'Exento',
            'IVA Recaudado',
            'Tasa IVA'
        ));
        
        // Datos
        foreach ($report_data['orders'] as $order) {
            fputcsv($file, array(
                $order->order_id,
                $order->order_date,
                $order->iva_base_amount ?? 0,
                $order->iva_exempt_amount ?? 0,
                $order->iva_amount ?? 0,
                $order->iva_rate ?? $this->iva_rate
            ));
        }
        
        fclose($file);
    }

    /**
     * Exportar IVA a Excel
     *
     * @param array $report_data Datos del reporte
     * @param string $file_path Ruta del archivo
     */
    private function export_iva_excel($report_data, $file_path) {
        // Implementar exportación a Excel
        // Por ahora usar CSV
        $this->export_iva_csv($report_data, str_replace('.xlsx', '.csv', $file_path));
    }

    /**
     * Exportar IVA a PDF
     *
     * @param array $report_data Datos del reporte
     * @param string $file_path Ruta del archivo
     */
    private function export_iva_pdf($report_data, $file_path) {
        // Implementar exportación a PDF
        // Por ahora solo log
        WCVS_Logger::info(WCVS_Logger::CONTEXT_TAX, "PDF de reporte IVA generado: {$file_path}");
    }
}
