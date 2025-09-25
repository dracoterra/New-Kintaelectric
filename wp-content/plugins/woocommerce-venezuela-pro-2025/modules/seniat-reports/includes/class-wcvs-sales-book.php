<?php
/**
 * Libro de Ventas - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para generar el Libro de Ventas SENIAT
 */
class WCVS_Sales_Book {

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
        // Hook para procesar ventas
        add_action('woocommerce_order_status_completed', array($this, 'process_sale'));
        add_action('woocommerce_order_status_processing', array($this, 'process_sale'));
    }

    /**
     * Procesar venta
     *
     * @param int $order_id ID del pedido
     */
    public function process_sale($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Verificar si ya fue procesada
        if ($order->get_meta('_sales_book_processed')) {
            return;
        }

        // Procesar venta para el libro
        $sale_data = $this->extract_sale_data($order);
        $this->save_sale_to_book($sale_data);
        
        // Marcar como procesada
        $order->update_meta_data('_sales_book_processed', true);
        $order->save();
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "Venta procesada para Libro de Ventas: #{$order_id}");
    }

    /**
     * Extraer datos de la venta
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function extract_sale_data($order) {
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
            'customer_data' => $billing_data,
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
     * Guardar venta en el libro
     *
     * @param array $sale_data Datos de la venta
     */
    private function save_sale_to_book($sale_data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wcvs_sales_book',
            array(
                'order_id' => $sale_data['order_id'],
                'order_number' => $sale_data['order_number'],
                'order_date' => $sale_data['order_date'],
                'order_status' => $sale_data['order_status'],
                'customer_data' => maybe_serialize($sale_data['customer_data']),
                'items_data' => maybe_serialize($sale_data['items_data']),
                'taxes_data' => maybe_serialize($sale_data['taxes_data']),
                'totals_data' => maybe_serialize($sale_data['totals']),
                'payment_method' => $sale_data['payment_method'],
                'payment_method_title' => $sale_data['payment_method_title'],
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Generar Libro de Ventas
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_sales_book($start_date, $end_date) {
        global $wpdb;
        
        $sales = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}wcvs_sales_book
            WHERE order_date >= %s
            AND order_date <= %s
            ORDER BY order_date ASC
        ", $start_date, $end_date));
        
        $summary = $this->calculate_sales_summary($sales);
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'summary' => $summary,
            'sales' => $sales,
            'headers' => $this->get_sales_book_headers(),
            'rows' => $this->format_sales_for_export($sales),
        );
    }

    /**
     * Calcular resumen de ventas
     *
     * @param array $sales Ventas
     * @return array
     */
    private function calculate_sales_summary($sales) {
        $total_sales = 0;
        $total_iva_collected = 0;
        $total_igtf_collected = 0;
        $total_islr_collected = 0;
        $total_exempt_sales = 0;
        $total_taxable_sales = 0;
        
        foreach ($sales as $sale) {
            $totals = maybe_unserialize($sale->totals_data);
            $taxes = maybe_unserialize($sale->taxes_data);
            
            $total_sales += floatval($totals['total'] ?? 0);
            $total_iva_collected += floatval($taxes['iva_amount'] ?? 0);
            $total_igtf_collected += floatval($taxes['igtf_amount'] ?? 0);
            $total_islr_collected += floatval($taxes['islr_amount'] ?? 0);
            $total_exempt_sales += floatval($taxes['iva_exempt'] ?? 0);
            $total_taxable_sales += floatval($taxes['iva_base'] ?? 0);
        }
        
        return array(
            'total_sales' => $total_sales,
            'total_iva_collected' => $total_iva_collected,
            'total_igtf_collected' => $total_igtf_collected,
            'total_islr_collected' => $total_islr_collected,
            'total_exempt_sales' => $total_exempt_sales,
            'total_taxable_sales' => $total_taxable_sales,
            'total_sales_count' => count($sales),
        );
    }

    /**
     * Obtener encabezados del Libro de Ventas
     *
     * @return array
     */
    private function get_sales_book_headers() {
        return array(
            'Número de Factura',
            'Fecha de Factura',
            'RIF del Cliente',
            'Nombre del Cliente',
            'Dirección del Cliente',
            'Teléfono del Cliente',
            'Email del Cliente',
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
     * Formatear ventas para exportación
     *
     * @param array $sales Ventas
     * @return array
     */
    private function format_sales_for_export($sales) {
        $rows = array();
        
        foreach ($sales as $sale) {
            $customer_data = maybe_unserialize($sale->customer_data);
            $totals = maybe_unserialize($sale->totals_data);
            $taxes = maybe_unserialize($sale->taxes_data);
            
            $rows[] = array(
                $sale->order_number,
                $sale->order_date,
                $customer_data['rif'] ?? '',
                $customer_data['name'] ?? '',
                $customer_data['address'] ?? '',
                $customer_data['phone'] ?? '',
                $customer_data['email'] ?? '',
                $totals['subtotal'] ?? 0,
                $taxes['iva_exempt'] ?? 0,
                $taxes['iva_base'] ?? 0,
                $taxes['iva_amount'] ?? 0,
                $taxes['igtf_amount'] ?? 0,
                $taxes['islr_amount'] ?? 0,
                $totals['total'] ?? 0,
                $sale->payment_method_title,
                $sale->order_status,
            );
        }
        
        return $rows;
    }

    /**
     * Exportar Libro de Ventas
     *
     * @param array $sales_book_data Datos del libro de ventas
     * @param string $format Formato (csv, excel, pdf)
     * @return string URL del archivo generado
     */
    public function export_sales_book($sales_book_data, $format = 'csv') {
        $filename = 'libro_ventas_' . $sales_book_data['period']['start_date'] . '_' . $sales_book_data['period']['end_date'] . '.' . $format;
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        switch ($format) {
            case 'csv':
                $this->export_sales_book_csv($sales_book_data, $file_path);
                break;
            case 'excel':
                $this->export_sales_book_excel($sales_book_data, $file_path);
                break;
            case 'pdf':
                $this->export_sales_book_pdf($sales_book_data, $file_path);
                break;
        }
        
        return $upload_dir['url'] . '/' . $filename;
    }

    /**
     * Exportar Libro de Ventas a CSV
     *
     * @param array $sales_book_data Datos del libro de ventas
     * @param string $file_path Ruta del archivo
     */
    private function export_sales_book_csv($sales_book_data, $file_path) {
        $file = fopen($file_path, 'w');
        
        // Encabezados
        fputcsv($file, $sales_book_data['headers']);
        
        // Datos
        foreach ($sales_book_data['rows'] as $row) {
            fputcsv($file, $row);
        }
        
        // Resumen
        fputcsv($file, array('', '', '', '', '', '', '', 'RESUMEN:', '', '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Total Ventas:', $sales_book_data['summary']['total_sales'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'IVA Recaudado:', $sales_book_data['summary']['total_iva_collected'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'IGTF Recaudado:', $sales_book_data['summary']['total_igtf_collected'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'ISLR Recaudado:', $sales_book_data['summary']['total_islr_collected'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Ventas Exentas:', $sales_book_data['summary']['total_exempt_sales'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Ventas Gravables:', $sales_book_data['summary']['total_taxable_sales'], '', '', '', '', '', '', ''));
        fputcsv($file, array('', '', '', '', '', '', '', 'Total Facturas:', $sales_book_data['summary']['total_sales_count'], '', '', '', '', '', '', ''));
        
        fclose($file);
    }

    /**
     * Exportar Libro de Ventas a Excel
     *
     * @param array $sales_book_data Datos del libro de ventas
     * @param string $file_path Ruta del archivo
     */
    private function export_sales_book_excel($sales_book_data, $file_path) {
        // Implementar exportación a Excel
        // Por ahora usar CSV
        $this->export_sales_book_csv($sales_book_data, str_replace('.xlsx', '.csv', $file_path));
    }

    /**
     * Exportar Libro de Ventas a PDF
     *
     * @param array $sales_book_data Datos del libro de ventas
     * @param string $file_path Ruta del archivo
     */
    private function export_sales_book_pdf($sales_book_data, $file_path) {
        // Implementar exportación a PDF
        // Por ahora solo log
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "PDF del Libro de Ventas generado: {$file_path}");
    }

    /**
     * Obtener estadísticas de ventas
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function get_sales_statistics($start_date, $end_date) {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_sales,
                SUM(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as total_amount,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_amount') AS DECIMAL(10,2))) as total_iva,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.igtf_amount') AS DECIMAL(10,2))) as total_igtf,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.islr_amount') AS DECIMAL(10,2))) as total_islr,
                AVG(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as average_sale
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
            'average_sale' => floatval($stats->average_sale ?? 0),
        );
    }

    /**
     * Obtener ventas por período
     *
     * @param string $period Período (daily, weekly, monthly)
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function get_sales_by_period($period, $start_date, $end_date) {
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
                COUNT(*) as sales_count,
                SUM(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as total_amount,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_amount') AS DECIMAL(10,2))) as total_iva
            FROM {$wpdb->prefix}wcvs_sales_book
            WHERE order_date >= %s
            AND order_date <= %s
            GROUP BY DATE_FORMAT(order_date, %s)
            ORDER BY period ASC
        ", $date_format, $start_date, $end_date, $date_format));
        
        return $results;
    }

    /**
     * Validar datos del Libro de Ventas
     *
     * @param array $sales_book_data Datos del libro de ventas
     * @return array
     */
    public function validate_sales_book($sales_book_data) {
        $errors = array();
        
        // Validar período
        if (empty($sales_book_data['period']['start_date']) || empty($sales_book_data['period']['end_date'])) {
            $errors[] = 'Período no válido';
        }
        
        // Validar ventas
        if (empty($sales_book_data['sales'])) {
            $errors[] = 'No hay ventas en el período seleccionado';
        }
        
        // Validar totales
        $calculated_total = 0;
        foreach ($sales_book_data['sales'] as $sale) {
            $totals = maybe_unserialize($sale->totals_data);
            $calculated_total += floatval($totals['total'] ?? 0);
        }
        
        if (abs($calculated_total - $sales_book_data['summary']['total_sales']) > 0.01) {
            $errors[] = 'Los totales no coinciden';
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors,
        );
    }
}
