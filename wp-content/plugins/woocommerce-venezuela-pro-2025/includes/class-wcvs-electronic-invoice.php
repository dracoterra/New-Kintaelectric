<?php
/**
 * Clase para generación de facturas electrónicas según normativas SENIAT Venezuela
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WCVS_Electronic_Invoice {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WCVS_Core
     */
    private $core;
    
    /**
     * Configuración fiscal
     * 
     * @var array
     */
    private $fiscal_settings;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->core = WCVS_Core::get_instance();
        $this->fiscal_settings = get_option('wcvs_fiscal_settings', array());
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Generar factura electrónica SOLO al completar pedido (pago confirmado)
        add_action('woocommerce_order_status_completed', array($this, 'generate_electronic_invoice'));
        
        // Mostrar campos de facturación en admin
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_fiscal_fields_in_admin'));
        
        // AJAX para generar facturas manualmente
        add_action('wp_ajax_wcvs_generate_invoice', array($this, 'generate_invoice_ajax'));
        
        // Añadir estilos para facturación
        add_action('admin_enqueue_scripts', array($this, 'enqueue_electronic_invoice_scripts'));
    }
    
    /**
     * Mostrar campos de facturación en admin
     */
    public function display_fiscal_fields_in_admin($order) {
        $rif = $order->get_meta('_billing_rif');
        $cedula = $order->get_meta('_billing_cedula');
        $nombre_completo = $order->get_meta('_billing_nombre_completo');
        $tipo_cliente = $order->get_meta('_billing_tipo_cliente');
        $control_number = $order->get_meta('_seniat_control_number');
        
        if ($rif || $cedula || $nombre_completo || $tipo_cliente) {
            echo '<div class="address">';
            echo '<p><strong>' . __('Datos Fiscales:', 'woocommerce-venezuela-pro-2025') . '</strong></p>';
            
            if ($rif) {
                echo '<p><strong>' . __('RIF:', 'woocommerce-venezuela-pro-2025') . '</strong> ' . esc_html($rif) . '</p>';
            }
            
            if ($cedula) {
                echo '<p><strong>' . __('Cédula:', 'woocommerce-venezuela-pro-2025') . '</strong> ' . esc_html($cedula) . '</p>';
            }
            
            if ($nombre_completo) {
                echo '<p><strong>' . __('Nombre Completo:', 'woocommerce-venezuela-pro-2025') . '</strong> ' . esc_html($nombre_completo) . '</p>';
            }
            
            if ($tipo_cliente) {
                $tipo_text = $this->get_tipo_cliente_text($tipo_cliente);
                echo '<p><strong>' . __('Tipo de Cliente:', 'woocommerce-venezuela-pro-2025') . '</strong> ' . $tipo_text . '</p>';
            }
            
            if ($control_number) {
                echo '<p><strong>' . __('N° Control SENIAT:', 'woocommerce-venezuela-pro-2025') . '</strong> ' . esc_html($control_number) . '</p>';
                
                // Botón para regenerar factura
                echo '<p>';
                echo '<button type="button" class="button button-secondary" onclick="wcvsRegenerateInvoice(' . $order->get_id() . ')">';
                echo '<span class="dashicons dashicons-update"></span> ';
                echo __('Regenerar Factura', 'woocommerce-venezuela-pro-2025');
                echo '</button>';
                echo '</p>';
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Obtener texto del tipo de cliente
     */
    private function get_tipo_cliente_text($tipo_cliente) {
        $tipos = array(
            'consumidor_final' => __('Consumidor Final', 'woocommerce-venezuela-pro-2025'),
            'persona_natural' => __('Persona Natural', 'woocommerce-venezuela-pro-2025'),
            'persona_juridica' => __('Persona Jurídica', 'woocommerce-venezuela-pro-2025')
        );
        
        return isset($tipos[$tipo_cliente]) ? $tipos[$tipo_cliente] : $tipo_cliente;
    }
    
    /**
     * Generar factura electrónica (adaptado para Venezuela)
     */
    public function generate_electronic_invoice($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Solo generar factura si el cliente lo solicitó
        $tipo_cliente = $order->get_meta('_billing_tipo_cliente');
        if (empty($tipo_cliente) || $tipo_cliente === 'consumidor_final') {
            return; // No generar factura para consumidor final
        }
        
        // Verificar si ya se generó la factura
        $control_number = $order->get_meta('_seniat_control_number');
        if ($control_number) {
            return; // Ya se generó
        }
        
        // Generar número de control
        $control_number = $this->generate_control_number();
        
        // Guardar número de control
        $order->update_meta_data('_seniat_control_number', $control_number);
        $order->save();
        
        // Generar XML de la factura
        $xml_content = $this->generate_invoice_xml($order, $control_number);
        
        // Guardar XML
        $this->save_invoice_xml($order_id, $xml_content);
        
        // Generar PDF de la factura
        $pdf_path = $this->generate_invoice_pdf($order, $control_number);
        
        // Enviar por email si está habilitado
        if (isset($this->fiscal_settings['auto_send_invoices']) && $this->fiscal_settings['auto_send_invoices']) {
            $this->send_invoice_email($order, $control_number, $xml_content, $pdf_path);
        }
        
        // Añadir nota al pedido
        $order->add_order_note(sprintf(__('Factura electrónica generada. Número de control: %s', 'woocommerce-venezuela-pro-2025'), $control_number));
        
        // Log de generación
        error_log('WCVS: Factura electrónica generada para pedido ' . $order_id . ' - Control: ' . $control_number);
    }
    
    /**
     * Generar número de control
     */
    private function generate_control_number() {
        $prefix = isset($this->fiscal_settings['control_number_prefix']) ? $this->fiscal_settings['control_number_prefix'] : 'FAC-';
        $next_number = isset($this->fiscal_settings['next_control_number']) ? $this->fiscal_settings['next_control_number'] : 1;
        
        $control_number = $prefix . date('Y') . date('m') . '-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
        
        // Incrementar número para próximo uso
        $this->fiscal_settings['next_control_number'] = $next_number + 1;
        update_option('wcvs_fiscal_settings', $this->fiscal_settings);
        
        return $control_number;
    }
    
    /**
     * Generar XML de la factura (adaptado para Venezuela)
     */
    private function generate_invoice_xml($order, $control_number) {
        $company_rif = isset($this->fiscal_settings['company_rif']) ? $this->fiscal_settings['company_rif'] : '';
        $company_name = isset($this->fiscal_settings['company_name']) ? $this->fiscal_settings['company_name'] : '';
        $company_address = isset($this->fiscal_settings['company_address']) ? $this->fiscal_settings['company_address'] : '';
        
        // Obtener datos del cliente
        $customer_cedula = $order->get_meta('_billing_cedula');
        $customer_rif = $order->get_meta('_billing_rif');
        $customer_name = $order->get_meta('_billing_nombre_completo');
        $customer_address = $order->get_billing_address_1() . ', ' . $order->get_billing_city();
        $tipo_cliente = $order->get_meta('_billing_tipo_cliente');
        
        // Obtener tasa de cambio del momento de la compra
        $exchange_rate = $order->get_meta('_exchange_rate_at_purchase');
        $exchange_date = $order->get_meta('_exchange_rate_date');
        
        $subtotal_usd = $order->get_subtotal();
        $subtotal_ves = $exchange_rate ? $subtotal_usd * $exchange_rate : 0;
        
        $iva_rate = isset($this->fiscal_settings['apply_iva']) && $this->fiscal_settings['apply_iva'] ? 0.16 : 0;
        $iva_amount_usd = $subtotal_usd * $iva_rate;
        $iva_amount_ves = $exchange_rate ? $iva_amount_usd * $exchange_rate : 0;
        
        $igtf_rate = isset($this->fiscal_settings['apply_igtf']) && $this->fiscal_settings['apply_igtf'] ? 0.03 : 0;
        $igtf_amount_usd = $subtotal_usd * $igtf_rate;
        $igtf_amount_ves = $exchange_rate ? $igtf_amount_usd * $exchange_rate : 0;
        
        $total_usd = $subtotal_usd + $iva_amount_usd + $igtf_amount_usd;
        $total_ves = $exchange_rate ? $total_usd * $exchange_rate : 0;
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<FacturaElectronica>';
        $xml .= '<Encabezado>';
        $xml .= '<NumeroControl>' . esc_html($control_number) . '</NumeroControl>';
        $xml .= '<FechaEmision>' . date('d/m/Y') . '</FechaEmision>';
        $xml .= '<TipoDocumento>Factura</TipoDocumento>';
        $xml .= '<TipoCliente>' . esc_html($tipo_cliente) . '</TipoCliente>';
        $xml .= '</Encabezado>';
        
        $xml .= '<Emisor>';
        $xml .= '<RIF>' . esc_html($company_rif) . '</RIF>';
        $xml .= '<RazonSocial>' . esc_html($company_name) . '</RazonSocial>';
        $xml .= '<Direccion>' . esc_html($company_address) . '</Direccion>';
        $xml .= '</Emisor>';
        
        $xml .= '<Receptor>';
        if ($tipo_cliente === 'persona_natural' && $customer_cedula) {
            $xml .= '<Cedula>' . esc_html($customer_cedula) . '</Cedula>';
        } elseif ($tipo_cliente === 'persona_juridica' && $customer_rif) {
            $xml .= '<RIF>' . esc_html($customer_rif) . '</RIF>';
        }
        $xml .= '<Nombre>' . esc_html($customer_name) . '</Nombre>';
        $xml .= '<Direccion>' . esc_html($customer_address) . '</Direccion>';
        $xml .= '</Receptor>';
        
        $xml .= '<InformacionCambio>';
        $xml .= '<TasaCambio>' . number_format($exchange_rate, 2, '.', '') . '</TasaCambio>';
        $xml .= '<FechaCambio>' . esc_html($exchange_date) . '</FechaCambio>';
        $xml .= '</InformacionCambio>';
        
        $xml .= '<Detalles>';
        foreach ($order->get_items() as $item) {
            $item_subtotal_usd = $item->get_subtotal();
            $item_subtotal_ves = $exchange_rate ? $item_subtotal_usd * $exchange_rate : 0;
            $item_price_usd = $item_subtotal_usd / $item->get_quantity();
            $item_price_ves = $exchange_rate ? $item_price_usd * $exchange_rate : 0;
            
            $xml .= '<Item>';
            $xml .= '<Codigo>' . esc_html($item->get_product_id()) . '</Codigo>';
            $xml .= '<Descripcion>' . esc_html($item->get_name()) . '</Descripcion>';
            $xml .= '<Cantidad>' . $item->get_quantity() . '</Cantidad>';
            $xml .= '<PrecioUnitarioUSD>' . number_format($item_price_usd, 2, '.', '') . '</PrecioUnitarioUSD>';
            $xml .= '<PrecioUnitarioVES>' . number_format($item_price_ves, 2, '.', '') . '</PrecioUnitarioVES>';
            $xml .= '<SubtotalUSD>' . number_format($item_subtotal_usd, 2, '.', '') . '</SubtotalUSD>';
            $xml .= '<SubtotalVES>' . number_format($item_subtotal_ves, 2, '.', '') . '</SubtotalVES>';
            $xml .= '</Item>';
        }
        $xml .= '</Detalles>';
        
        $xml .= '<Totales>';
        $xml .= '<SubtotalUSD>' . number_format($subtotal_usd, 2, '.', '') . '</SubtotalUSD>';
        $xml .= '<SubtotalVES>' . number_format($subtotal_ves, 2, '.', '') . '</SubtotalVES>';
        $xml .= '<IVAUSD>' . number_format($iva_amount_usd, 2, '.', '') . '</IVAUSD>';
        $xml .= '<IVAVES>' . number_format($iva_amount_ves, 2, '.', '') . '</IVAVES>';
        $xml .= '<IGTFUSD>' . number_format($igtf_amount_usd, 2, '.', '') . '</IGTFUSD>';
        $xml .= '<IGTFVES>' . number_format($igtf_amount_ves, 2, '.', '') . '</IGTFVES>';
        $xml .= '<TotalUSD>' . number_format($total_usd, 2, '.', '') . '</TotalUSD>';
        $xml .= '<TotalVES>' . number_format($total_ves, 2, '.', '') . '</TotalVES>';
        $xml .= '</Totales>';
        
        $xml .= '</FacturaElectronica>';
        
        return $xml;
    }
    
    /**
     * Guardar XML de la factura
     */
    private function save_invoice_xml($order_id, $xml_content) {
        $upload_dir = wp_upload_dir();
        $invoices_dir = $upload_dir['basedir'] . '/wcvs-invoices/';
        
        // Crear directorio si no existe
        if (!file_exists($invoices_dir)) {
            wp_mkdir_p($invoices_dir);
        }
        
        $filename = 'factura_' . $order_id . '_' . date('Y-m-d_H-i-s') . '.xml';
        $file_path = $invoices_dir . $filename;
        
        file_put_contents($file_path, $xml_content);
        
        // Guardar ruta del archivo
        update_post_meta($order_id, '_invoice_xml_path', $file_path);
    }
    
    /**
     * Generar PDF de la factura
     */
    private function generate_invoice_pdf($order, $control_number) {
        $upload_dir = wp_upload_dir();
        $invoices_dir = $upload_dir['basedir'] . '/wcvs-invoices/';
        
        // Crear directorio si no existe
        if (!file_exists($invoices_dir)) {
            wp_mkdir_p($invoices_dir);
        }
        
        $filename = 'factura_' . $order->get_id() . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $file_path = $invoices_dir . $filename;
        
        // Generar contenido HTML para PDF
        $html_content = $this->generate_invoice_html($order, $control_number);
        
        // Por ahora, guardar como HTML (en producción se usaría una librería de PDF)
        $html_file = str_replace('.pdf', '.html', $file_path);
        file_put_contents($html_file, $html_content);
        
        // Guardar ruta del archivo
        $order->update_meta_data('_invoice_pdf_path', $html_file);
        $order->save();
        
        return $html_file;
    }
    
    /**
     * Generar HTML de la factura
     */
    private function generate_invoice_html($order, $control_number) {
        $company_name = isset($this->fiscal_settings['company_name']) ? $this->fiscal_settings['company_name'] : get_bloginfo('name');
        $company_rif = isset($this->fiscal_settings['company_rif']) ? $this->fiscal_settings['company_rif'] : '';
        $company_address = isset($this->fiscal_settings['company_address']) ? $this->fiscal_settings['company_address'] : '';
        
        $customer_name = $order->get_meta('_billing_nombre_completo') ?: $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $customer_rif = $order->get_meta('_billing_rif');
        $customer_cedula = $order->get_meta('_billing_cedula');
        
        $exchange_rate = $order->get_meta('_exchange_rate_at_purchase');
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura ' . $control_number . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .company-info { text-align: left; margin-bottom: 30px; }
        .customer-info { margin-bottom: 30px; }
        .invoice-details { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .totals { text-align: right; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . $company_name . '</h1>
        <h2>FACTURA ELECTRÓNICA</h2>
        <p>N° Control: ' . $control_number . '</p>
    </div>
    
    <div class="company-info">
        <p><strong>RIF:</strong> ' . $company_rif . '</p>
        <p><strong>Dirección:</strong> ' . $company_address . '</p>
    </div>
    
    <div class="customer-info">
        <h3>Datos del Cliente</h3>
        <p><strong>Nombre:</strong> ' . $customer_name . '</p>';
        
        if ($customer_rif) {
            $html .= '<p><strong>RIF:</strong> ' . $customer_rif . '</p>';
        }
        
        if ($customer_cedula) {
            $html .= '<p><strong>Cédula:</strong> ' . $customer_cedula . '</p>';
        }
        
        $html .= '<p><strong>Dirección:</strong> ' . $order->get_billing_address_1() . ', ' . $order->get_billing_city() . '</p>
    </div>
    
    <div class="invoice-details">
        <p><strong>Fecha:</strong> ' . date('d/m/Y') . '</p>
        <p><strong>Pedido:</strong> #' . $order->get_order_number() . '</p>';
        
        if ($exchange_rate) {
            $html .= '<p><strong>Tasa de Cambio:</strong> ' . number_format($exchange_rate, 2, ',', '.') . ' Bs./USD</p>';
        }
        
        $html .= '</div>
    
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit. USD</th>
                <th>Subtotal USD</th>';
        
        if ($exchange_rate) {
            $html .= '<th>Precio Unit. VES</th>
                <th>Subtotal VES</th>';
        }
        
        $html .= '</tr>
        </thead>
        <tbody>';
        
        foreach ($order->get_items() as $item) {
            $item_price_usd = $item->get_subtotal() / $item->get_quantity();
            $item_price_ves = $exchange_rate ? $item_price_usd * $exchange_rate : 0;
            $item_subtotal_ves = $exchange_rate ? $item->get_subtotal() * $exchange_rate : 0;
            
            $html .= '<tr>
                <td>' . $item->get_name() . '</td>
                <td>' . $item->get_quantity() . '</td>
                <td>$' . number_format($item_price_usd, 2, '.', ',') . '</td>
                <td>$' . number_format($item->get_subtotal(), 2, '.', ',') . '</td>';
            
            if ($exchange_rate) {
                $html .= '<td>' . number_format($item_price_ves, 2, ',', '.') . ' Bs.</td>
                    <td>' . number_format($item_subtotal_ves, 2, ',', '.') . ' Bs.</td>';
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="totals">
        <p><strong>Subtotal USD:</strong> $' . number_format($order->get_subtotal(), 2, '.', ',') . '</p>';
        
        if ($order->get_total_tax() > 0) {
            $html .= '<p><strong>IVA USD:</strong> $' . number_format($order->get_total_tax(), 2, '.', ',') . '</p>';
        }
        
        if ($exchange_rate) {
            $html .= '<p><strong>Subtotal VES:</strong> ' . number_format($order->get_subtotal() * $exchange_rate, 2, ',', '.') . ' Bs.</p>';
            
            if ($order->get_total_tax() > 0) {
                $html .= '<p><strong>IVA VES:</strong> ' . number_format($order->get_total_tax() * $exchange_rate, 2, ',', '.') . ' Bs.</p>';
            }
        }
        
        $html .= '<p><strong>Total USD:</strong> $' . number_format($order->get_total(), 2, '.', ',') . '</p>';
        
        if ($exchange_rate) {
            $html .= '<p><strong>Total VES:</strong> ' . number_format($order->get_total() * $exchange_rate, 2, ',', '.') . ' Bs.</p>';
        }
        
        $html .= '</div>
    
    <div class="footer">
        <p>Esta factura ha sido generada electrónicamente según las normativas SENIAT Venezuela</p>
        <p>Fecha de generación: ' . current_time('d/m/Y H:i:s') . '</p>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Enviar factura por email
     */
    private function send_invoice_email($order, $control_number, $xml_content, $pdf_path) {
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_meta('_billing_nombre_completo') ?: $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        
        $subject = sprintf(__('Factura Electrónica #%s - %s', 'woocommerce-venezuela-pro-2025'), $control_number, get_bloginfo('name'));
        
        $message = sprintf(__('Estimado/a %s,', 'woocommerce-venezuela-pro-2025'), $customer_name) . "\n\n";
        $message .= sprintf(__('Adjunto encontrará su factura electrónica con número de control: %s', 'woocommerce-venezuela-pro-2025'), $control_number) . "\n\n";
        $message .= __('Detalles del pedido:', 'woocommerce-venezuela-pro-2025') . "\n";
        $message .= __('Número de pedido:', 'woocommerce-venezuela-pro-2025') . ' ' . $order->get_order_number() . "\n";
        $message .= __('Total:', 'woocommerce-venezuela-pro-2025') . ' ' . $order->get_formatted_order_total() . "\n\n";
        $message .= __('Gracias por su compra.', 'woocommerce-venezuela-pro-2025') . "\n\n";
        $message .= get_bloginfo('name');
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Adjuntar archivos
        $attachments = array();
        if (file_exists($pdf_path)) {
            $attachments[] = $pdf_path;
        }
        
        wp_mail($customer_email, $subject, $message, $headers, $attachments);
    }
    
    /**
     * Generar factura vía AJAX
     */
    public function generate_invoice_ajax() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_generate_invoice_nonce')) {
            wp_die('Nonce inválido');
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error('Pedido no encontrado');
        }
        
        // Generar factura
        $this->generate_electronic_invoice($order_id);
        
        wp_send_json_success('Factura generada correctamente');
    }
    
    /**
     * Obtener estadísticas de facturación
     */
    public function get_invoice_stats() {
        global $wpdb;
        
        $total_invoices = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_seniat_control_number'
        ");
        
        $monthly_invoices = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_seniat_control_number'
            AND p.post_type = 'shop_order'
            AND p.post_date >= %s
        ", date('Y-m-01')));
        
        return array(
            'total' => $total_invoices ? $total_invoices : 0,
            'monthly' => $monthly_invoices ? $monthly_invoices : 0
        );
    }
    
    /**
     * Cargar scripts para facturación electrónica
     */
    public function enqueue_electronic_invoice_scripts($hook) {
        if (strpos($hook, 'post.php') !== false || strpos($hook, 'post-new.php') !== false) {
            wp_enqueue_script('wcvs-electronic-invoice', plugin_dir_url(__FILE__) . '../admin/js/electronic-invoice.js', array('jquery'), WCVS_VERSION, true);
            
            wp_localize_script('wcvs-electronic-invoice', 'wcvs_electronic_invoice', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_generate_invoice_nonce'),
                'i18n' => array(
                    'generating' => __('Generando factura...', 'woocommerce-venezuela-pro-2025'),
                    'success' => __('Factura generada correctamente', 'woocommerce-venezuela-pro-2025'),
                    'error' => __('Error al generar la factura', 'woocommerce-venezuela-pro-2025')
                )
            ));
        }
    }
}
