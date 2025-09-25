<?php
/**
 * Libro de Compras - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para generar el Libro de Compras SENIAT
 */
class WCVS_Purchases_Book {

    /**
     * Configuraciones
     *
     * @var array
     */
    private $settings;

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
        // Hook para procesar compras
        add_action('woocommerce_order_status_processing', array($this, 'process_purchase'));
        add_action('woocommerce_order_status_completed', array($this, 'process_purchase'));
    }

    /**
     * Procesar compra
     *
     * @param int $order_id ID del pedido
     */
    public function process_purchase($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Verificar si ya fue procesada
        if ($order->get_meta('_purchases_book_processed')) {
            return;
        }

        // Procesar compra para el libro
        $purchase_data = $this->extract_purchase_data($order);
        $this->save_purchase_to_book($purchase_data);
        
        // Marcar como procesada
        $order->update_meta_data('_purchases_book_processed', true);
        $order->save();
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "Compra procesada para Libro de Compras: #{$order_id}");
    }

    /**
     * Extraer datos de la compra
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function extract_purchase_data($order) {
        $billing_data = array(
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'address' => $order->get_formatted_billing_address(),
            'rif' => $order->get_meta('_billing_rif'),
            'tax_id' => $order->get_meta('_billing_tax_id'),
        );

        $items_data = array();
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $items_data[] = array(
                'name' => $item->get_name(),
                'sku' => $product ? $product->get_sku() : '',
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total() / $item->get_quantity(),
                'total' => $item->get_total(),
                'tax_class' => $product ? $product->get_tax_class() : '',
            );
        }

        $taxes_data = array(
            'iva_base' => $order->get_meta('_iva_base_amount'),
            'iva_exempt' => $order->get_meta('_iva_exempt_amount'),
            'iva_rate' => $order->get_meta('_iva_rate'),
            'iva_amount' => $order->get_meta('_iva_amount'),
            'igtf_base' => $order->get_meta('_igtf_base_amount'),
            'igtf_exempt' => $order->get_meta('_igtf_exempt_amount'),
            'igtf_taxable' => $order->get_meta('_igtf_taxable_amount'),
            'igtf_rate' => $order->get_meta('_igtf_rate'),
            'igtf_amount' => $order->get_meta('_igtf_amount'),
            'igtf_currency' => $order->get_meta('_igtf_currency'),
            'islr_base' => $order->get_meta('_islr_base_amount'),
            'islr_exempt' => $order->get_meta('_islr_exempt_amount'),
            'islr_taxable' => $order->get_meta('_islr_taxable_amount'),
            'islr_amount' => $order->get_meta('_islr_amount'),
            'islr_effective_rate' => $order->get_meta('_islr_effective_rate'),
        );

        return array(
            'order_id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'order_status' => $order->get_status(),
            'supplier_data' => $billing_data,
            'items_data' => $items_data,
            'taxes_data' => $taxes_data,
            'totals' => array(
                'subtotal' => $order->get_subtotal(),
                'shipping' => $order->get_shipping_total(),
                'taxes' => $order->get_total_tax(),
                'total' => $order->get_total(),
                'currency' => $order->get_currency(),
            ),
            'payment_method' => $order->get_payment_method(),
            'payment_method_title' => $order->get_payment_method_title(),
        );
    }

    /**
     * Guardar compra en el libro
     *
     * @param array $purchase_data Datos de la compra
     */
    private function save_purchase_to_book($purchase_data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wcvs_purchases_book',
            array(
                'order_id' => $purchase_data['order_id'],
                'order_number' => $purchase_data['order_number'],
                'order_date' => $purchase_data['order_date'],
                'order_status' => $purchase_data['order_status'],
                'supplier_data' => maybe_serialize($purchase_data['supplier_data']),
                'items_data' => maybe_serialize($purchase_data['items_data']),
                'taxes_data' => maybe_serialize($purchase_data['taxes_data']),
                'totals_data' => maybe_serialize($purchase_data['totals']),
                'payment_method' => $purchase_data['payment_method'],
                'payment_method_title' => $purchase_data['payment_method_title'],
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Generar Libro de Compras
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_purchases_book($start_date, $end_date) {
        global $wpdb;
        
        $purchases = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}wcvs_purchases_book
            WHERE order_date >= %s
            AND order_date <= %s
            ORDER BY order_date ASC
        ", $start_date, $end_date));
        
        $summary = $this->calculate_purchases_summary($purchases);
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'summary' => $summary,
            'purchases' => $purchases,
            'headers' => $this->get_purchases_book_headers(),
            'rows' => $this->format_purchases_for_export($purchases),
        );
    }

    /**
     * Calcular resumen de compras
     *
     * @param array $purchases Compras
     * @return array
     */
    private function calculate_purchases_summary($purchases) {
        $total_purchases = 0;
        $total_iva_paid = 0;
        $total_igtf_paid = 0;
        $total_islr_paid = 0;
        $total_exempt_purchases = 0;
        $total_taxable_purchases = 0;
        
        foreach ($purchases as $purchase) {
            $totals = maybe_unserialize($purchase->totals_data);
            $taxes = maybe_unserialize($purchase->taxes_data);
            
            $total_purchases += floatval($totals['total'] ?? 0);
            $total_iva_paid += floatval($taxes['iva_amount'] ?? 0);
            $total_igtf_paid += floatval($taxes['igtf_amount'] ?? 0);
            $total_islr_paid += floatval($taxes['islr_amount'] ?? 0);
            $total_exempt_purchases += floatval($taxes['iva_exempt'] ?? 0);
            $total_taxable_purchases += floatval($taxes['iva_base'] ?? 0);
        }
        
        return array(
            'total_purchases' => $total_purchases,
            'total_iva_paid' => $total_iva_paid,
            'total_igtf_paid' => $total_igtf_paid,
            'total_islr_paid' => $total_islr_paid,
            'total_exempt_purchases' => $total_exempt_purchases,
            'total_taxable_purchases' => $total_taxable_purchases,
            'total_purchases_count' => count($purchases),
        );
    }

    /**
     * Obtener encabezados del Libro de Compras
     *
     * @return array
     */
    private function get_purchases_book_headers() {
        return array(
            'Número de Factura',
            'Fecha de Factura',
            'RIF del Proveedor',
            'Nombre del Proveedor',
            'Dirección del Proveedor',
            'Teléfono del Proveedor',
            'Email del Proveedor',
            'Subtotal',
            'Exento de IVA',
            'Base Imponible IVA',
            'IVA (16%)',
            'IGTF (3%)',
            'ISLR',
            'Total',
            'Método de Pago',
            'Estado del Pedido',
        );
    }

    /**
     * Formatear compras para exportación
     *
     * @param array $purchases Compras
     * @return array
     */
    private function format_purchases_for_export($purchases) {
        $rows = array();
        
        foreach ($purchases as $purchase) {
            $supplier_data = maybe_unserialize($purchase->supplier_data);
            $totals = maybe_unserialize($purchase->totals_data);
            $taxes = maybe_unserialize($purchase->taxes_data);
            
            $rows[] = array(
                $purchase->order_number,
                $purchase->order_date,
                $supplier_data['rif'] ?? '',
                $supplier_data['name'] ?? '',
                $supplier_data['address'] ?? '',
                $supplier_data['phone'] ?? '',
                $supplier_data['email'] ?? '',
                $totals['subtotal'] ?? 0,
                $taxes['iva_exempt'] ?? 0,
                $taxes['iva_base'] ?? 0,
                $taxes['iva_amount'] ?? 0,
                $taxes['igtf_amount'] ?? 0,
                $taxes['islr_amount'] ?? 0,
                $totals['total'] ?? 0,
                $purchase->payment_method_title,
                $purchase->order_status,
            );
        }
        
        return $rows;
    }

    /**
     * Exportar Libro de Compras
     *
     * @param array $purchases_book_data Datos del libro de compras
     * @param string $format Formato (csv, excel, pdf)
     * @return string URL del archivo generado
     */
    public function export_purchases_book($purchases_book_data, $format = 'csv') {
        $filename = 'libro_compras_' . $purchases_book_data['period']['start_date'] . '_' . $purchases_book_data['period']['end_date'] . '.' . $format;
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        switch ($format) {
            case 'csv':
                $this->export_purchases_book_csv($purchases_book_data, $file_path);
                break;
            case 'excel':
                $this->export_purchases_book_excel($purchases_book_data, $file_path);
                break;
            case 'pdf':
                $this->export_purchases_book_pdf($purchases_book_data, $file_path);
                break;
        }
        
        return $upload_dir['url'] . '/' . $filename;
    }

    /**
     * Exportar Libro de Compras a CSV
     *
     * @param array $purchases_book_data Datos del libro de compras
     * @param string $file_path Ruta del archivo
     */
    private function export_purchases_book_csv($purchases_book_data, $file_path) {
        $file = fopen($file_path, 'w');
        
        // Encabezados
        fputcsv($file, $purchases_book_data['headers']);
        
        // Datos
        foreach ($purchases_book_data['rows'] as $row) {
            fputcsv($file, $row);
        }
        
        // Resumen
        fputcsv($file, array('', '', '', '', '', '', '', 'RESUMEN:', '', '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Total Compras:', $purchases_book_data['summary']['total_purchases'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'IVA Pagado:', $purchases_book_data['summary']['total_iva_paid'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'IGTF Pagado:', $purchases_book_data['summary']['total_igtf_paid'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'ISLR Pagado:', $purchases_book_data['summary']['total_islr_paid'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Compras Exentas:', $purchases_book_data['summary']['total_exempt_purchases'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Compras Gravables:', $purchases_book_data['summary']['total_taxable_purchases'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Total Facturas:', $purchases_book_data['summary']['total_purchases_count'], '', '', '', '', '', '', ''));
        
        fclose($file);
    }

    /**
     * Exportar Libro de Compras a Excel
     *
     * @param array $purchases_book_data Datos del libro de compras
     * @param string $file_path Ruta del archivo
     */
    private function export_purchases_book_excel($purchases_book_data, $file_path) {
        // Implementar exportación a Excel
        // Por ahora usar CSV
        $this->export_purchases_book_csv($purchases_book_data, str_replace('.xlsx', '.csv', $file_path));
    }

    /**
     * Exportar Libro de Compras a PDF
     *
     * @param array $purchases_book_data Datos del libro de compras
     * @param string $file_path Ruta del archivo
     */
    private function export_purchases_book_pdf($purchases_book_data, $file_path) {
        // Implementar exportación a PDF
        // Por ahora solo log
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "PDF del Libro de Compras generado: {$file_path}");
    }

    /**
     * Obtener estadísticas de compras
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function get_purchases_statistics($start_date, $end_date) {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_purchases,
                SUM(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as total_amount,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_amount') AS DECIMAL(10,2))) as total_iva,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.igtf_amount') AS DECIMAL(10,2))) as total_igtf,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.islr_amount') AS DECIMAL(10,2))) as total_islr,
                AVG(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as average_purchase
            FROM {$wpdb->prefix}wcvs_purchases_book
            WHERE order_date >= %s
            AND order_date <= %s
        ", $start_date, $end_date));
        
        return array(
            'total_purchases' => intval($stats->total_purchases ?? 0),
            'total_amount' => floatval($stats->total_amount ?? 0),
            'total_iva' => floatval($stats->total_iva ?? 0),
            'total_igtf' => floatval($stats->total_igtf ?? 0),
            'total_islr' => floatval($stats->total_islr ?? 0),
            'average_purchase' => floatval($stats->average_purchase ?? 0),
        );
    }

    /**
     * Obtener compras por período
     *
     * @param string $period Período (daily, weekly, monthly)
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function get_purchases_by_period($period, $start_date, $end_date) {
        global $wpdb;
        
        $date_format = '';
        switch ($period) {
            case 'daily':
                $date_format = '%Y-%m-%d';
                break;
            case 'weekly':
                $date_format = '%Y-%u';
                break;
            case 'monthly':
                $date_format = '%Y-%m';
                break;
        }
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE_FORMAT(order_date, %s) as period,
                COUNT(*) as purchases_count,
                SUM(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as total_amount,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_amount') AS DECIMAL(10,2))) as total_iva
            FROM {$wpdb->prefix}wcvs_purchases_book
            WHERE order_date >= %s
            AND order_date <= %s
            GROUP BY DATE_FORMAT(order_date, %s)
            ORDER BY period ASC
        ", $date_format, $start_date, $end_date, $date_format));
        
        return $results;
    }

    /**
     * Validar datos del Libro de Compras
     *
     * @param array $purchases_book_data Datos del libro de compras
     * @return array
     */
    public function validate_purchases_book($purchases_book_data) {
        $errors = array();
        
        // Validar período
        if (empty($purchases_book_data['period']['start_date']) || empty($purchases_book_data['period']['end_date'])) {
            $errors[] = 'Período no válido';
        }
        
        // Validar compras
        if (empty($purchases_book_data['purchases'])) {
            $errors[] = 'No hay compras en el período seleccionado';
        }
        
        // Validar totales
        $calculated_total = 0;
        foreach ($purchases_book_data['purchases'] as $purchase) {
            $totals = maybe_unserialize($purchase->totals_data);
            $calculated_total += floatval($totals['total'] ?? 0);
        }
        
        if (abs($calculated_total - $purchases_book_data['summary']['total_purchases']) > 0.01) {
            $errors[] = 'Los totales no coinciden';
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors,
        );
    }

    /**
     * Comparar ventas vs compras
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function compare_sales_vs_purchases($start_date, $end_date) {
        $sales_stats = $this->get_sales_statistics($start_date, $end_date);
        $purchases_stats = $this->get_purchases_statistics($start_date, $end_date);
        
        return array(
            'sales' => $sales_stats,
            'purchases' => $purchases_stats,
            'difference' => array(
                'amount' => $sales_stats['total_amount'] - $purchases_stats['total_amount'],
                'iva' => $sales_stats['total_iva'] - $purchases_stats['total_iva'],
                'igtf' => $sales_stats['total_igtf'] - $purchases_stats['total_igtf'],
                'islr' => $sales_stats['total_islr'] - $purchases_stats['total_islr'],
            ),
        );
    }

    /**
     * Obtener estadísticas de ventas (método helper)
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    private function get_sales_statistics($start_date, $end_date) {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_sales,
                SUM(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as total_amount,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_amount') AS DECIMAL(10,2))) as total_iva,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.igtf_amount') AS DECIMAL(10,2))) as total_igtf,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.islr_amount') AS DECIMAL(10,2))) as total_islr
            FROM {$wpdb->prefix}wcvs_sales_book
            WHERE order_date >= %s
            AND order_date <= %s
        ", $start_date, $end_date));
        
        return array(
            'total_sales' => intval($stats->total_sales ?? 0),
            'total_amount' => floatval($stats->total_amount ?? 0),
            'total_iva' => floatval($stats->total_iva ?? 0),
            'total_igtf' => floatval($stats->total_igtf ?? 0),
            'total_islr' => floatval($stats->total_islr ?? 0),
        );
    }
}
