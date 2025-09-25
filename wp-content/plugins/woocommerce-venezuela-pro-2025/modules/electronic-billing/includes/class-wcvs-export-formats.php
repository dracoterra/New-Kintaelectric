<?php
/**
 * Formatos de Exportación - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar formatos de exportación de documentos
 */
class WCVS_Export_Formats {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Formatos de exportación disponibles
     *
     * @var array
     */
    private $export_formats = array(
        'pdf' => 'PDF',
        'xml' => 'XML',
        'json' => 'JSON',
        'csv' => 'CSV',
        'excel' => 'Excel',
        'html' => 'HTML'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('electronic_billing', array());
    }

    /**
     * Exportar documento en formato específico
     *
     * @param array $invoice_data Datos de la factura
     * @param string $format Formato de exportación
     * @param array $options Opciones de exportación
     * @return string|false
     */
    public function export_document($invoice_data, $format, $options = array()) {
        if (!isset($this->export_formats[$format])) {
            return false;
        }

        switch ($format) {
            case 'pdf':
                return $this->export_to_pdf($invoice_data, $options);
            case 'xml':
                return $this->export_to_xml($invoice_data, $options);
            case 'json':
                return $this->export_to_json($invoice_data, $options);
            case 'csv':
                return $this->export_to_csv($invoice_data, $options);
            case 'excel':
                return $this->export_to_excel($invoice_data, $options);
            case 'html':
                return $this->export_to_html($invoice_data, $options);
            default:
                return false;
        }
    }

    /**
     * Exportar a PDF
     *
     * @param array $invoice_data Datos de la factura
     * @param array $options Opciones de exportación
     * @return string|false
     */
    private function export_to_pdf($invoice_data, $options = array()) {
        // Por ahora, generar HTML que se puede convertir a PDF
        $html = $this->export_to_html($invoice_data, $options);
        
        if (!$html) {
            return false;
        }

        // Guardar HTML temporalmente
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.html';
        $filepath = $upload_dir['basedir'] . '/wcvs-exports/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        file_put_contents($filepath, $html);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "PDF generado para factura #{$invoice_data['invoice_number']}");
        
        return $filepath;
    }

    /**
     * Exportar a XML
     *
     * @param array $invoice_data Datos de la factura
     * @param array $options Opciones de exportación
     * @return string|false
     */
    private function export_to_xml($invoice_data, $options = array()) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><factura></factura>');
        
        // Datos de la empresa
        $empresa = $xml->addChild('empresa');
        $empresa->addChild('nombre', htmlspecialchars($invoice_data['company']['name']));
        $empresa->addChild('rif', htmlspecialchars($invoice_data['company']['rif']));
        $empresa->addChild('direccion', htmlspecialchars($invoice_data['company']['address']));
        $empresa->addChild('telefono', htmlspecialchars($invoice_data['company']['phone']));
        $empresa->addChild('email', htmlspecialchars($invoice_data['company']['email']));
        
        // Datos del cliente
        $cliente = $xml->addChild('cliente');
        $cliente->addChild('nombre', htmlspecialchars($invoice_data['customer']['name']));
        $cliente->addChild('rif', htmlspecialchars($invoice_data['customer']['rif']));
        $cliente->addChild('direccion', htmlspecialchars($invoice_data['customer']['address']));
        $cliente->addChild('telefono', htmlspecialchars($invoice_data['customer']['phone']));
        $cliente->addChild('email', htmlspecialchars($invoice_data['customer']['email']));
        
        // Datos de la factura
        $factura = $xml->addChild('factura');
        $factura->addChild('numero', htmlspecialchars($invoice_data['invoice_number']));
        $factura->addChild('fecha', htmlspecialchars($invoice_data['order']['date']));
        $factura->addChild('pedido', htmlspecialchars($invoice_data['order']['number']));
        $factura->addChild('moneda', htmlspecialchars($invoice_data['order']['currency']));
        
        // Items
        $items = $xml->addChild('items');
        foreach ($invoice_data['items'] as $item) {
            $item_xml = $items->addChild('item');
            $item_xml->addChild('descripcion', htmlspecialchars($item['name']));
            $item_xml->addChild('sku', htmlspecialchars($item['sku']));
            $item_xml->addChild('cantidad', $item['quantity']);
            $item_xml->addChild('precio', $item['price']);
            $item_xml->addChild('impuestos', $item['tax']);
            $item_xml->addChild('total', $item['total']);
        }
        
        // Totales
        $totales = $xml->addChild('totales');
        $totales->addChild('subtotal', $invoice_data['totals']['subtotal']);
        $totales->addChild('envio', $invoice_data['totals']['shipping_total']);
        $totales->addChild('iva', $invoice_data['fiscal']['iva_amount']);
        $totales->addChild('igtf', $invoice_data['fiscal']['igtf_amount']);
        $totales->addChild('islr', $invoice_data['fiscal']['islr_amount']);
        $totales->addChild('total', $invoice_data['totals']['total']);
        
        // Guardar XML
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.xml';
        $filepath = $upload_dir['basedir'] . '/wcvs-exports/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        $xml->asXML($filepath);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "XML generado para factura #{$invoice_data['invoice_number']}");
        
        return $filepath;
    }

    /**
     * Exportar a JSON
     *
     * @param array $invoice_data Datos de la factura
     * @param array $options Opciones de exportación
     * @return string|false
     */
    private function export_to_json($invoice_data, $options = array()) {
        $json_data = array(
            'factura' => $invoice_data,
            'exportado_en' => current_time('mysql'),
            'formato' => 'JSON',
            'version' => '1.0'
        );
        
        $json = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Guardar JSON
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.json';
        $filepath = $upload_dir['basedir'] . '/wcvs-exports/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        file_put_contents($filepath, $json);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "JSON generado para factura #{$invoice_data['invoice_number']}");
        
        return $filepath;
    }

    /**
     * Exportar a CSV
     *
     * @param array $invoice_data Datos de la factura
     * @param array $options Opciones de exportación
     * @return string|false
     */
    private function export_to_csv($invoice_data, $options = array()) {
        $csv_data = array();
        
        // Encabezados
        $csv_data[] = array(
            'Campo',
            'Valor'
        );
        
        // Datos de la empresa
        $csv_data[] = array('Empresa - Nombre', $invoice_data['company']['name']);
        $csv_data[] = array('Empresa - RIF', $invoice_data['company']['rif']);
        $csv_data[] = array('Empresa - Dirección', $invoice_data['company']['address']);
        $csv_data[] = array('Empresa - Teléfono', $invoice_data['company']['phone']);
        $csv_data[] = array('Empresa - Email', $invoice_data['company']['email']);
        
        // Datos del cliente
        $csv_data[] = array('Cliente - Nombre', $invoice_data['customer']['name']);
        $csv_data[] = array('Cliente - RIF', $invoice_data['customer']['rif']);
        $csv_data[] = array('Cliente - Dirección', $invoice_data['customer']['address']);
        $csv_data[] = array('Cliente - Teléfono', $invoice_data['customer']['phone']);
        $csv_data[] = array('Cliente - Email', $invoice_data['customer']['email']);
        
        // Datos de la factura
        $csv_data[] = array('Factura - Número', $invoice_data['invoice_number']);
        $csv_data[] = array('Factura - Fecha', $invoice_data['order']['date']);
        $csv_data[] = array('Factura - Pedido', $invoice_data['order']['number']);
        $csv_data[] = array('Factura - Moneda', $invoice_data['order']['currency']);
        
        // Items
        $csv_data[] = array('', ''); // Línea en blanco
        $csv_data[] = array('Items', '');
        $csv_data[] = array('Descripción', 'SKU', 'Cantidad', 'Precio', 'Impuestos', 'Total');
        
        foreach ($invoice_data['items'] as $item) {
            $csv_data[] = array(
                $item['name'],
                $item['sku'],
                $item['quantity'],
                $item['price'],
                $item['tax'],
                $item['total']
            );
        }
        
        // Totales
        $csv_data[] = array('', ''); // Línea en blanco
        $csv_data[] = array('Totales', '');
        $csv_data[] = array('Subtotal', $invoice_data['totals']['subtotal']);
        $csv_data[] = array('Envío', $invoice_data['totals']['shipping_total']);
        $csv_data[] = array('IVA', $invoice_data['fiscal']['iva_amount']);
        $csv_data[] = array('IGTF', $invoice_data['fiscal']['igtf_amount']);
        $csv_data[] = array('ISLR', $invoice_data['fiscal']['islr_amount']);
        $csv_data[] = array('Total', $invoice_data['totals']['total']);
        
        // Convertir a CSV
        $csv_content = '';
        foreach ($csv_data as $row) {
            $csv_content .= implode(',', array_map(array($this, 'escape_csv_field'), $row)) . "\n";
        }
        
        // Guardar CSV
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.csv';
        $filepath = $upload_dir['basedir'] . '/wcvs-exports/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        file_put_contents($filepath, $csv_content);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "CSV generado para factura #{$invoice_data['invoice_number']}");
        
        return $filepath;
    }

    /**
     * Escapar campo CSV
     *
     * @param mixed $field Campo a escapar
     * @return string
     */
    private function escape_csv_field($field) {
        $field = (string) $field;
        
        // Si contiene comas, comillas o saltos de línea, envolver en comillas
        if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
            $field = '"' . str_replace('"', '""', $field) . '"';
        }
        
        return $field;
    }

    /**
     * Exportar a Excel
     *
     * @param array $invoice_data Datos de la factura
     * @param array $options Opciones de exportación
     * @return string|false
     */
    private function export_to_excel($invoice_data, $options = array()) {
        // Por ahora, generar CSV que se puede abrir en Excel
        $csv_file = $this->export_to_csv($invoice_data, $options);
        
        if (!$csv_file) {
            return false;
        }

        // Renombrar archivo a .xlsx (en producción se usaría una librería como PhpSpreadsheet)
        $excel_file = str_replace('.csv', '.xlsx', $csv_file);
        rename($csv_file, $excel_file);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Excel generado para factura #{$invoice_data['invoice_number']}");
        
        return $excel_file;
    }

    /**
     * Exportar a HTML
     *
     * @param array $invoice_data Datos de la factura
     * @param array $options Opciones de exportación
     * @return string|false
     */
    private function export_to_html($invoice_data, $options = array()) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Factura <?php echo esc_html($invoice_data['invoice_number']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company-info { margin-bottom: 20px; }
                .invoice-info { margin-bottom: 20px; }
                .customer-info { margin-bottom: 20px; }
                .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .items-table th { background-color: #f2f2f2; }
                .totals { margin-top: 20px; }
                .totals table { width: 100%; }
                .totals td { padding: 5px; }
                .totals .total-row { font-weight: bold; border-top: 2px solid #000; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>FACTURA</h1>
                <h2><?php echo esc_html($invoice_data['company']['name']); ?></h2>
                <p>RIF: <?php echo esc_html($invoice_data['company']['rif']); ?></p>
            </div>

            <div class="company-info">
                <h3>Datos de la Empresa</h3>
                <p><strong>Nombre:</strong> <?php echo esc_html($invoice_data['company']['name']); ?></p>
                <p><strong>RIF:</strong> <?php echo esc_html($invoice_data['company']['rif']); ?></p>
                <p><strong>Dirección:</strong> <?php echo esc_html($invoice_data['company']['address']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo esc_html($invoice_data['company']['phone']); ?></p>
                <p><strong>Email:</strong> <?php echo esc_html($invoice_data['company']['email']); ?></p>
            </div>

            <div class="invoice-info">
                <h3>Información de la Factura</h3>
                <p><strong>Número:</strong> <?php echo esc_html($invoice_data['invoice_number']); ?></p>
                <p><strong>Fecha:</strong> <?php echo esc_html($invoice_data['order']['date']); ?></p>
                <p><strong>Pedido:</strong> #<?php echo esc_html($invoice_data['order']['number']); ?></p>
            </div>

            <div class="customer-info">
                <h3>Datos del Cliente</h3>
                <p><strong>Nombre:</strong> <?php echo esc_html($invoice_data['customer']['name']); ?></p>
                <p><strong>RIF:</strong> <?php echo esc_html($invoice_data['customer']['rif']); ?></p>
                <p><strong>Dirección:</strong> <?php echo esc_html($invoice_data['customer']['address']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo esc_html($invoice_data['customer']['phone']); ?></p>
                <p><strong>Email:</strong> <?php echo esc_html($invoice_data['customer']['email']); ?></p>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>SKU</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Impuestos</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoice_data['items'] as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item['name']); ?></td>
                        <td><?php echo esc_html($item['sku']); ?></td>
                        <td><?php echo esc_html($item['quantity']); ?></td>
                        <td><?php echo wc_price($item['price']); ?></td>
                        <td><?php echo wc_price($item['tax']); ?></td>
                        <td><?php echo wc_price($item['total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals">
                <table>
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td><?php echo wc_price($invoice_data['totals']['subtotal']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Envío:</strong></td>
                        <td><?php echo wc_price($invoice_data['totals']['shipping_total']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>IVA (16%):</strong></td>
                        <td><?php echo wc_price($invoice_data['fiscal']['iva_amount']); ?></td>
                    </tr>
                    <?php if ($invoice_data['fiscal']['igtf_amount'] > 0): ?>
                    <tr>
                        <td><strong>IGTF (3%):</strong></td>
                        <td><?php echo wc_price($invoice_data['fiscal']['igtf_amount']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($invoice_data['fiscal']['islr_amount'] > 0): ?>
                    <tr>
                        <td><strong>ISLR (1%):</strong></td>
                        <td><?php echo wc_price($invoice_data['fiscal']['islr_amount']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td><strong>TOTAL:</strong></td>
                        <td><?php echo wc_price($invoice_data['totals']['total']); ?></td>
                    </tr>
                </table>
            </div>

            <div style="margin-top: 40px; text-align: center;">
                <p><strong>Método de Pago:</strong> <?php echo esc_html($invoice_data['order']['payment_method']); ?></p>
                <p><strong>Moneda:</strong> <?php echo esc_html($invoice_data['order']['currency']); ?></p>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();
        
        // Guardar HTML
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.html';
        $filepath = $upload_dir['basedir'] . '/wcvs-exports/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        file_put_contents($filepath, $html);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "HTML generado para factura #{$invoice_data['invoice_number']}");
        
        return $filepath;
    }

    /**
     * Obtener formatos de exportación disponibles
     *
     * @return array
     */
    public function get_available_formats() {
        return $this->export_formats;
    }

    /**
     * Descargar archivo exportado
     *
     * @param string $filepath Ruta del archivo
     * @param string $filename Nombre del archivo
     * @return void
     */
    public function download_file($filepath, $filename) {
        if (!file_exists($filepath)) {
            wp_die(__('Archivo no encontrado.', 'wcvs'));
        }

        // Determinar tipo MIME
        $mime_type = $this->get_mime_type($filepath);
        
        // Configurar headers para descarga
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Leer y enviar archivo
        readfile($filepath);
        exit;
    }

    /**
     * Obtener tipo MIME del archivo
     *
     * @param string $filepath Ruta del archivo
     * @return string
     */
    private function get_mime_type($filepath) {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        $mime_types = array(
            'pdf' => 'application/pdf',
            'xml' => 'application/xml',
            'json' => 'application/json',
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'html' => 'text/html'
        );
        
        return $mime_types[$extension] ?? 'application/octet-stream';
    }

    /**
     * Limpiar archivos de exportación antiguos
     *
     * @param int $days Días de antigüedad
     * @return int Número de archivos eliminados
     */
    public function cleanup_old_exports($days = 7) {
        $upload_dir = wp_upload_dir();
        $exports_dir = $upload_dir['basedir'] . '/wcvs-exports/';
        
        if (!is_dir($exports_dir)) {
            return 0;
        }
        
        $files = glob($exports_dir . '*');
        $deleted_count = 0;
        $cutoff_time = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff_time) {
                if (unlink($file)) {
                    $deleted_count++;
                }
            }
        }
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Limpiados {$deleted_count} archivos de exportación antiguos");
        
        return $deleted_count;
    }

    /**
     * Obtener estadísticas de exportación
     *
     * @return array
     */
    public function get_export_stats() {
        $upload_dir = wp_upload_dir();
        $exports_dir = $upload_dir['basedir'] . '/wcvs-exports/';
        
        if (!is_dir($exports_dir)) {
            return array(
                'total_files' => 0,
                'total_size' => 0,
                'formats' => array()
            );
        }
        
        $files = glob($exports_dir . '*');
        $total_size = 0;
        $formats = array();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $total_size += filesize($file);
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $formats[$extension] = ($formats[$extension] ?? 0) + 1;
            }
        }
        
        return array(
            'total_files' => count($files),
            'total_size' => $total_size,
            'formats' => $formats
        );
    }
}
