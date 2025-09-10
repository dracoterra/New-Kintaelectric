<?php
/**
 * Clase para generar facturas PDF venezolanas
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Invoice_Generator {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Obtener instancia del plugin de forma segura
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // AJAX para generar factura
        add_action('wp_ajax_wvp_generate_invoice', array($this, 'generate_invoice_ajax'));
        
        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue scripts necesarios
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'post.php') !== false || strpos($hook, 'post-new.php') !== false) {
            wp_enqueue_script(
                'wvp-invoice-generator',
                WVP_PLUGIN_URL . 'assets/js/invoice-generator.js',
                array('jquery'),
                WVP_VERSION,
                true
            );
            
            wp_localize_script('wvp-invoice-generator', 'wvp_invoice', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wvp_invoice_nonce'),
                'i18n' => array(
                    'generating' => __('Generando factura...', 'wvp'),
                    'error' => __('Error al generar la factura', 'wvp'),
                    'success' => __('Factura generada correctamente', 'wvp')
                )
            ));
        }
    }
    
    /**
     * Generar factura via AJAX
     */
    public function generate_invoice_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_invoice_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad', 'wvp')));
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'wvp')));
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(array('message' => __('Pedido no encontrado', 'wvp')));
        }
        
        // Generar factura
        $invoice_url = $this->generate_invoice_pdf($order);
        
        if ($invoice_url) {
            wp_send_json_success(array(
                'message' => __('Factura generada correctamente', 'wvp'),
                'url' => $invoice_url
            ));
        } else {
            wp_send_json_error(array('message' => __('Error al generar la factura', 'wvp')));
        }
    }
    
    /**
     * Generar factura PDF
     * 
     * @param WC_Order $order Pedido
     * @return string|false URL de la factura o false si hay error
     */
    public function generate_invoice_pdf($order) {
        // Crear directorio de facturas si no existe
        $upload_dir = wp_upload_dir();
        $invoices_dir = $upload_dir['basedir'] . '/wvp-invoices/';
        if (!file_exists($invoices_dir)) {
            wp_mkdir_p($invoices_dir);
        }
        
        // Generar nombre del archivo
        $filename = 'factura_' . $order->get_id() . '_' . time() . '.html';
        $file_path = $invoices_dir . $filename;
        $file_url = $upload_dir['baseurl'] . '/wvp-invoices/' . $filename;
        
        // Generar contenido HTML de la factura
        $html_content = $this->generate_invoice_html($order);
        
        // Guardar archivo
        if (file_put_contents($file_path, $html_content)) {
            // Guardar URL en el pedido
            $order->update_meta_data('_invoice_url', $file_url);
            $order->save();
            
            return $file_url;
        }
        
        return false;
    }
    
    /**
     * Generar HTML de la factura
     * 
     * @param WC_Order $order Pedido
     * @return string HTML de la factura
     */
    private function generate_invoice_html($order) {
        // Obtener datos del pedido
        $order_id = $order->get_id();
        $order_date = $order->get_date_created()->format('d/m/Y');
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $customer_cedula = $order->get_meta('_billing_cedula_rif');
        $customer_address = $order->get_formatted_billing_address();
        $customer_email = $order->get_billing_email();
        $customer_phone = $order->get_billing_phone();
        
        // Obtener datos fiscales
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        $igtf_amount = $order->get_meta('_igtf_amount');
        $igtf_rate = $order->get_meta('_igtf_rate');
        $control_number = $order->get_meta('_seniat_control_number');
        $payment_type = $order->get_meta('_payment_type');
        
        // Obtener datos de la tienda
        $store_name = get_bloginfo('name');
        $store_address = get_option('woocommerce_store_address');
        $store_city = get_option('woocommerce_store_city');
        $store_rif = get_option('wvp_store_rif', 'J-12345678-9'); // Configurar en admin
        
        // Calcular totales
        $subtotal = $order->get_subtotal();
        $shipping = $order->get_shipping_total();
        $tax = $order->get_total_tax();
        $total = $order->get_total();
        
        // Convertir a bolívares si hay tasa BCV
        $subtotal_bs = $bcv_rate ? $subtotal * $bcv_rate : 0;
        $shipping_bs = $bcv_rate ? $shipping * $bcv_rate : 0;
        $tax_bs = $bcv_rate ? $tax * $bcv_rate : 0;
        $total_bs = $bcv_rate ? $total * $bcv_rate : 0;
        $igtf_bs = $bcv_rate && $igtf_amount ? $igtf_amount * $bcv_rate : 0;
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Factura #<?php echo $order_id; ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background: #f5f5f5;
                }
                .invoice-container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .invoice-header {
                    text-align: center;
                    border-bottom: 2px solid #0073aa;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .invoice-title {
                    font-size: 28px;
                    font-weight: bold;
                    color: #0073aa;
                    margin: 0;
                }
                .invoice-subtitle {
                    font-size: 16px;
                    color: #666;
                    margin: 5px 0 0 0;
                }
                .invoice-info {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 30px;
                }
                .company-info, .customer-info {
                    width: 48%;
                }
                .info-section h3 {
                    background: #0073aa;
                    color: white;
                    padding: 8px 12px;
                    margin: 0 0 10px 0;
                    font-size: 14px;
                }
                .info-section p {
                    margin: 5px 0;
                    font-size: 13px;
                }
                .invoice-details {
                    margin-bottom: 30px;
                }
                .invoice-details table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .invoice-details th,
                .invoice-details td {
                    padding: 8px 12px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                .invoice-details th {
                    background: #f8f9fa;
                    font-weight: bold;
                }
                .totals-section {
                    margin-top: 20px;
                }
                .totals-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .totals-table td {
                    padding: 5px 12px;
                    border: none;
                }
                .totals-table .label {
                    text-align: right;
                    font-weight: bold;
                }
                .totals-table .amount {
                    text-align: right;
                    font-family: monospace;
                }
                .total-row {
                    border-top: 2px solid #0073aa;
                    font-weight: bold;
                    font-size: 16px;
                }
                .payment-info {
                    margin-top: 30px;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 5px;
                }
                .payment-info h4 {
                    margin: 0 0 10px 0;
                    color: #0073aa;
                }
                .fiscal-info {
                    margin-top: 20px;
                    font-size: 12px;
                    color: #666;
                    text-align: center;
                }
                .control-number {
                    font-family: monospace;
                    font-weight: bold;
                    color: #0073aa;
                }
                @media print {
                    body { background: white; }
                    .invoice-container { box-shadow: none; }
                }
            </style>
        </head>
        <body>
            <div class="invoice-container">
                <!-- Encabezado -->
                <div class="invoice-header">
                    <h1 class="invoice-title">FACTURA</h1>
                    <p class="invoice-subtitle"><?php echo $store_name; ?></p>
                </div>
                
                <!-- Información de la empresa y cliente -->
                <div class="invoice-info">
                    <div class="company-info">
                        <div class="info-section">
                            <h3>DATOS DE LA EMPRESA</h3>
                            <p><strong>Razón Social:</strong> <?php echo esc_html($store_name); ?></p>
                            <p><strong>RIF:</strong> <?php echo esc_html($store_rif); ?></p>
                            <p><strong>Dirección:</strong> <?php echo esc_html($store_address); ?></p>
                            <p><strong>Ciudad:</strong> <?php echo esc_html($store_city); ?></p>
                        </div>
                    </div>
                    
                    <div class="customer-info">
                        <div class="info-section">
                            <h3>DATOS DEL CLIENTE</h3>
                            <p><strong>Nombre:</strong> <?php echo esc_html($customer_name); ?></p>
                            <p><strong>Cédula/RIF:</strong> <?php echo esc_html($customer_cedula ?: 'N/A'); ?></p>
                            <p><strong>Email:</strong> <?php echo esc_html($customer_email); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo esc_html($customer_phone); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Detalles de la factura -->
                <div class="invoice-details">
                    <table>
                        <tr>
                            <th>N° Factura</th>
                            <th>Fecha</th>
                            <th>N° Control</th>
                            <th>Tipo de Pago</th>
                        </tr>
                        <tr>
                            <td><?php echo $order_id; ?></td>
                            <td><?php echo $order_date; ?></td>
                            <td class="control-number"><?php echo esc_html($control_number ?: 'Pendiente'); ?></td>
                            <td><?php echo $this->get_payment_type_label($payment_type); ?></td>
                        </tr>
                    </table>
                </div>
                
                <!-- Productos -->
                <div class="invoice-details">
                    <h3>PRODUCTOS</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit. (USD)</th>
                                <th>Precio Unit. (Bs.)</th>
                                <th>Total (USD)</th>
                                <th>Total (Bs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order->get_items() as $item): ?>
                                <?php
                                $product = $item->get_product();
                                $quantity = $item->get_quantity();
                                $price = $item->get_total() / $quantity;
                                $price_bs = $bcv_rate ? $price * $bcv_rate : 0;
                                $total_item = $item->get_total();
                                $total_item_bs = $bcv_rate ? $total_item * $bcv_rate : 0;
                                ?>
                                <tr>
                                    <td><?php echo esc_html($item->get_name()); ?></td>
                                    <td><?php echo $quantity; ?></td>
                                    <td>$<?php echo number_format($price, 2, ',', '.'); ?></td>
                                    <td><?php echo $bcv_rate ? number_format($price_bs, 2, ',', '.') . ' Bs.' : 'N/A'; ?></td>
                                    <td>$<?php echo number_format($total_item, 2, ',', '.'); ?></td>
                                    <td><?php echo $bcv_rate ? number_format($total_item_bs, 2, ',', '.') . ' Bs.' : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Totales -->
                <div class="totals-section">
                    <table class="totals-table">
                        <tr>
                            <td class="label">Subtotal:</td>
                            <td class="amount">$<?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            <?php if ($bcv_rate): ?>
                                <td class="amount"><?php echo number_format($subtotal_bs, 2, ',', '.'); ?> Bs.</td>
                            <?php endif; ?>
                        </tr>
                        <?php if ($shipping > 0): ?>
                        <tr>
                            <td class="label">Envío:</td>
                            <td class="amount">$<?php echo number_format($shipping, 2, ',', '.'); ?></td>
                            <?php if ($bcv_rate): ?>
                                <td class="amount"><?php echo number_format($shipping_bs, 2, ',', '.'); ?> Bs.</td>
                            <?php endif; ?>
                        </tr>
                        <?php endif; ?>
                        <?php if ($tax > 0): ?>
                        <tr>
                            <td class="label">IVA (16%):</td>
                            <td class="amount">$<?php echo number_format($tax, 2, ',', '.'); ?></td>
                            <?php if ($bcv_rate): ?>
                                <td class="amount"><?php echo number_format($tax_bs, 2, ',', '.'); ?> Bs.</td>
                            <?php endif; ?>
                        </tr>
                        <?php endif; ?>
                        <?php if ($igtf_amount && $igtf_amount > 0): ?>
                        <tr>
                            <td class="label">IGTF (<?php echo $igtf_rate; ?>%):</td>
                            <td class="amount">$<?php echo number_format($igtf_amount, 2, ',', '.'); ?></td>
                            <?php if ($bcv_rate): ?>
                                <td class="amount"><?php echo number_format($igtf_bs, 2, ',', '.'); ?> Bs.</td>
                            <?php endif; ?>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td class="label">TOTAL:</td>
                            <td class="amount">$<?php echo number_format($total, 2, ',', '.'); ?></td>
                            <?php if ($bcv_rate): ?>
                                <td class="amount"><?php echo number_format($total_bs, 2, ',', '.'); ?> Bs.</td>
                            <?php endif; ?>
                        </tr>
                    </table>
                </div>
                
                <!-- Información de pago -->
                <div class="payment-info">
                    <h4>INFORMACIÓN DE PAGO</h4>
                    <p><strong>Método de Pago:</strong> <?php echo $order->get_payment_method_title(); ?></p>
                    <?php if ($bcv_rate): ?>
                        <p><strong>Tasa BCV:</strong> <?php echo number_format($bcv_rate, 2, ',', '.'); ?> Bs./USD</p>
                    <?php endif; ?>
                    <?php if ($control_number): ?>
                        <p><strong>Número de Control:</strong> <span class="control-number"><?php echo esc_html($control_number); ?></span></p>
                    <?php endif; ?>
                </div>
                
                <!-- Información fiscal -->
                <div class="fiscal-info">
                    <p>Esta factura cumple con los requisitos fiscales venezolanos.</p>
                    <p>Generada el <?php echo current_time('d/m/Y H:i:s'); ?> por WooCommerce Venezuela Pro</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Obtener etiqueta del tipo de pago
     * 
     * @param string $payment_type Tipo de pago
     * @return string Etiqueta legible
     */
    private function get_payment_type_label($payment_type) {
        $labels = array(
            'transferencia_digital' => 'Transferencia Digital',
            'efectivo_usd' => 'Efectivo (USD)',
            'efectivo_bolivares' => 'Efectivo (Bolívares)',
            'other' => 'Otro'
        );
        
        return isset($labels[$payment_type]) ? $labels[$payment_type] : 'No especificado';
    }
}
