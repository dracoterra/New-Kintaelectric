<?php
/**
 * Generador de Facturas Electrónicas - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para generar facturas electrónicas
 */
class WCVS_Invoice_Generator {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Datos de la empresa
     *
     * @var array
     */
    private $company_data;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('electronic_billing', array());
        $this->company_data = $this->settings['company_data'] ?? array();
    }

    /**
     * Generar factura completa
     *
     * @param int $order_id ID del pedido
     * @param array $invoice_data Datos de la factura
     * @return array
     */
    public function generate_invoice($order_id, $invoice_data) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        // Generar número de factura si no existe
        if (empty($invoice_data['invoice_number'])) {
            $invoice_data['invoice_number'] = $this->generate_invoice_number();
        }

        // Preparar datos de la factura
        $invoice_data = $this->prepare_invoice_data($order, $invoice_data);

        // Generar documentos
        $documents = array(
            'pdf' => $this->generate_pdf($order, $invoice_data),
            'xml' => $this->generate_xml($order, $invoice_data),
            'json' => $this->generate_json($order, $invoice_data)
        );

        // Guardar documentos
        $this->save_documents($order_id, $documents);

        // Actualizar estado de la factura
        $this->update_invoice_status($order_id, 'generated');

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Factura #{$invoice_data['invoice_number']} generada completamente para pedido #{$order_id}");

        return array(
            'invoice_data' => $invoice_data,
            'documents' => $documents
        );
    }

    /**
     * Preparar datos de la factura
     *
     * @param WC_Order $order Pedido
     * @param array $invoice_data Datos de la factura
     * @return array
     */
    private function prepare_invoice_data($order, $invoice_data) {
        // Datos de la empresa
        $invoice_data['company'] = array(
            'name' => $this->company_data['name'] ?? 'Mi Empresa C.A.',
            'rif' => $this->company_data['rif'] ?? 'J-00000000-0',
            'address' => $this->company_data['address'] ?? 'Dirección de la empresa',
            'phone' => $this->company_data['phone'] ?? '0000-0000000',
            'email' => $this->company_data['email'] ?? 'info@miempresa.com',
            'website' => $this->company_data['website'] ?? 'www.miempresa.com'
        );

        // Datos del cliente
        $invoice_data['customer'] = array(
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'address' => $order->get_formatted_billing_address(),
            'rif' => $order->get_meta('_billing_rif'),
            'company' => $order->get_billing_company()
        );

        // Datos del pedido
        $invoice_data['order'] = array(
            'id' => $order->get_id(),
            'number' => $order->get_order_number(),
            'date' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'status' => $order->get_status(),
            'payment_method' => $order->get_payment_method_title(),
            'currency' => $order->get_currency()
        );

        // Items del pedido
        $invoice_data['items'] = array();
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $invoice_data['items'][] = array(
                'name' => $item->get_name(),
                'sku' => $product ? $product->get_sku() : '',
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total(),
                'tax' => $item->get_total_tax(),
                'total' => $item->get_total() + $item->get_total_tax()
            );
        }

        // Totales
        $invoice_data['totals'] = array(
            'subtotal' => $order->get_subtotal(),
            'tax_total' => $order->get_total_tax(),
            'shipping_total' => $order->get_shipping_total(),
            'total' => $order->get_total()
        );

        // Impuestos desglosados
        $invoice_data['taxes'] = array();
        foreach ($order->get_taxes() as $tax) {
            $invoice_data['taxes'][] = array(
                'name' => $tax->get_name(),
                'rate' => $tax->get_rate_percent(),
                'amount' => $tax->get_tax_total()
            );
        }

        // Datos fiscales
        $invoice_data['fiscal'] = array(
            'iva_rate' => $this->settings['iva_rate'] ?? 0.16,
            'iva_amount' => $order->get_total_tax(),
            'igtf_rate' => $this->settings['igtf_rate'] ?? 0.03,
            'igtf_amount' => $this->calculate_igtf($order),
            'islr_rate' => $this->settings['islr_rate'] ?? 0.01,
            'islr_amount' => $this->calculate_islr($order)
        );

        return $invoice_data;
    }

    /**
     * Generar número de factura
     *
     * @return string
     */
    private function generate_invoice_number() {
        $prefix = $this->settings['invoice_prefix'] ?? 'FAC';
        $year = date('Y');
        $month = date('m');
        
        // Obtener último número de factura del mes
        $last_invoice = $this->get_last_invoice_number($year, $month);
        $next_number = $last_invoice + 1;
        
        return sprintf('%s-%s%s-%06d', $prefix, $year, $month, $next_number);
    }

    /**
     * Obtener último número de factura del mes
     *
     * @param string $year Año
     * @param string $month Mes
     * @return int
     */
    private function get_last_invoice_number($year, $month) {
        global $wpdb;
        
        $meta_key = '_wcvs_invoice_number';
        $pattern = "%-{$year}{$month}-%";
        
        $result = $wpdb->get_var($wpdb->prepare("
            SELECT MAX(CAST(SUBSTRING_INDEX(meta_value, '-', -1) AS UNSIGNED))
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
            AND meta_value LIKE %s
        ", $meta_key, $pattern));
        
        return $result ?: 0;
    }

    /**
     * Calcular IGTF
     *
     * @param WC_Order $order Pedido
     * @return float
     */
    private function calculate_igtf($order) {
        $igtf_rate = $this->settings['igtf_rate'] ?? 0.03;
        $foreign_currency_gateways = $this->settings['igtf_foreign_currency_gateways'] ?? array();
        
        if (in_array($order->get_payment_method(), $foreign_currency_gateways)) {
            return $order->get_total() * $igtf_rate;
        }
        
        return 0;
    }

    /**
     * Calcular ISLR
     *
     * @param WC_Order $order Pedido
     * @return float
     */
    private function calculate_islr($order) {
        $islr_rate = $this->settings['islr_rate'] ?? 0.01;
        return $order->get_total() * $islr_rate;
    }

    /**
     * Generar PDF
     *
     * @param WC_Order $order Pedido
     * @param array $invoice_data Datos de la factura
     * @return string|false
     */
    private function generate_pdf($order, $invoice_data) {
        // Por ahora, solo generar HTML que se puede convertir a PDF
        $html = $this->generate_invoice_html($invoice_data);
        
        // Guardar HTML temporalmente
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.html';
        $filepath = $upload_dir['basedir'] . '/wcvs-invoices/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        file_put_contents($filepath, $html);
        
        return $filepath;
    }

    /**
     * Generar HTML de la factura
     *
     * @param array $invoice_data Datos de la factura
     * @return string
     */
    private function generate_invoice_html($invoice_data) {
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
                        <td><strong>IVA (<?php echo ($invoice_data['fiscal']['iva_rate'] * 100); ?>%):</strong></td>
                        <td><?php echo wc_price($invoice_data['fiscal']['iva_amount']); ?></td>
                    </tr>
                    <?php if ($invoice_data['fiscal']['igtf_amount'] > 0): ?>
                    <tr>
                        <td><strong>IGTF (<?php echo ($invoice_data['fiscal']['igtf_rate'] * 100); ?>%):</strong></td>
                        <td><?php echo wc_price($invoice_data['fiscal']['igtf_amount']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($invoice_data['fiscal']['islr_amount'] > 0): ?>
                    <tr>
                        <td><strong>ISLR (<?php echo ($invoice_data['fiscal']['islr_rate'] * 100); ?>%):</strong></td>
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
        return ob_get_clean();
    }

    /**
     * Generar XML
     *
     * @param WC_Order $order Pedido
     * @param array $invoice_data Datos de la factura
     * @return string|false
     */
    private function generate_xml($order, $invoice_data) {
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
        $filepath = $upload_dir['basedir'] . '/wcvs-invoices/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        $xml->asXML($filepath);
        
        return $filepath;
    }

    /**
     * Generar JSON
     *
     * @param WC_Order $order Pedido
     * @param array $invoice_data Datos de la factura
     * @return string|false
     */
    private function generate_json($order, $invoice_data) {
        $json_data = array(
            'invoice' => $invoice_data,
            'generated_at' => current_time('mysql'),
            'version' => '1.0',
            'format' => 'WCVS_ELECTRONIC_BILLING'
        );
        
        $json = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Guardar JSON
        $upload_dir = wp_upload_dir();
        $filename = 'invoice-' . $invoice_data['invoice_number'] . '.json';
        $filepath = $upload_dir['basedir'] . '/wcvs-invoices/' . $filename;
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($filepath));
        
        // Guardar archivo
        file_put_contents($filepath, $json);
        
        return $filepath;
    }

    /**
     * Guardar documentos
     *
     * @param int $order_id ID del pedido
     * @param array $documents Documentos generados
     */
    private function save_documents($order_id, $documents) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Guardar rutas de los documentos
        $order->update_meta_data('_wcvs_invoice_documents', $documents);
        $order->update_meta_data('_wcvs_invoice_generated_at', current_time('mysql'));
        $order->save();
    }

    /**
     * Actualizar estado de la factura
     *
     * @param int $order_id ID del pedido
     * @param string $status Estado
     */
    private function update_invoice_status($order_id, $status) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $order->update_meta_data('_wcvs_invoice_status', $status);
        $order->save();
    }

    /**
     * Obtener factura por número
     *
     * @param string $invoice_number Número de factura
     * @return array|false
     */
    public function get_invoice_by_number($invoice_number) {
        global $wpdb;
        
        $order_id = $wpdb->get_var($wpdb->prepare("
            SELECT post_id
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wcvs_invoice_number'
            AND meta_value = %s
        ", $invoice_number));
        
        if (!$order_id) {
            return false;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        
        return array(
            'order_id' => $order_id,
            'invoice_number' => $invoice_number,
            'status' => $order->get_meta('_wcvs_invoice_status'),
            'generated_at' => $order->get_meta('_wcvs_invoice_generated_at'),
            'documents' => $order->get_meta('_wcvs_invoice_documents')
        );
    }

    /**
     * Listar facturas
     *
     * @param array $args Argumentos de búsqueda
     * @return array
     */
    public function list_invoices($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'status' => '',
            'date_from' => '',
            'date_to' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array("meta_key = '_wcvs_invoice_number'");
        
        if (!empty($args['status'])) {
            $where_conditions[] = $wpdb->prepare("
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm2
                    WHERE pm2.post_id = pm.post_id
                    AND pm2.meta_key = '_wcvs_invoice_status'
                    AND pm2.meta_value = %s
                )
            ", $args['status']);
        }
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = $wpdb->prepare("
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm3
                    WHERE pm3.post_id = pm.post_id
                    AND pm3.meta_key = '_wcvs_invoice_generated_at'
                    AND pm3.meta_value >= %s
                )
            ", $args['date_from'] . ' 00:00:00');
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = $wpdb->prepare("
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm4
                    WHERE pm4.post_id = pm.post_id
                    AND pm4.meta_key = '_wcvs_invoice_generated_at'
                    AND pm4.meta_value <= %s
                )
            ", $args['date_to'] . ' 23:59:59');
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT pm.post_id, pm.meta_value as invoice_number
            FROM {$wpdb->postmeta} pm
            WHERE {$where_clause}
            ORDER BY pm.meta_value DESC
            LIMIT %d OFFSET %d
        ", $args['limit'], $args['offset']));
        
        $invoices = array();
        foreach ($results as $result) {
            $order = wc_get_order($result->post_id);
            if ($order) {
                $invoices[] = array(
                    'order_id' => $result->post_id,
                    'invoice_number' => $result->invoice_number,
                    'status' => $order->get_meta('_wcvs_invoice_status'),
                    'generated_at' => $order->get_meta('_wcvs_invoice_generated_at'),
                    'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'total' => $order->get_total()
                );
            }
        }
        
        return $invoices;
    }
}
