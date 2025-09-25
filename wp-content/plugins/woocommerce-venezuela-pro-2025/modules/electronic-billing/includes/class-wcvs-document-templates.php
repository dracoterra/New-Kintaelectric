<?php
/**
 * Plantillas de Documentos Electrónicos - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar plantillas de documentos electrónicos
 */
class WCVS_Document_Templates {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Plantillas disponibles
     *
     * @var array
     */
    private $templates = array(
        'invoice' => 'Factura',
        'credit_note' => 'Nota de Crédito',
        'debit_note' => 'Nota de Débito',
        'receipt' => 'Recibo',
        'proforma' => 'Proforma'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('electronic_billing', array());
    }

    /**
     * Obtener plantilla por tipo
     *
     * @param string $type Tipo de documento
     * @return array|false
     */
    public function get_template($type) {
        if (!isset($this->templates[$type])) {
            return false;
        }

        $template_data = $this->settings['templates'][$type] ?? array();
        
        // Si no existe plantilla personalizada, usar plantilla por defecto
        if (empty($template_data)) {
            $template_data = $this->get_default_template($type);
        }

        return $template_data;
    }

    /**
     * Obtener plantilla por defecto
     *
     * @param string $type Tipo de documento
     * @return array
     */
    private function get_default_template($type) {
        $default_templates = array(
            'invoice' => array(
                'name' => 'Factura',
                'header' => array(
                    'show_logo' => true,
                    'logo_position' => 'center',
                    'company_name' => true,
                    'company_rif' => true,
                    'company_address' => true,
                    'company_phone' => true,
                    'company_email' => true
                ),
                'body' => array(
                    'show_invoice_number' => true,
                    'show_date' => true,
                    'show_order_number' => true,
                    'show_customer_info' => true,
                    'show_items_table' => true,
                    'show_totals' => true,
                    'show_taxes' => true
                ),
                'footer' => array(
                    'show_payment_method' => true,
                    'show_currency' => true,
                    'show_terms' => true,
                    'show_signature' => true
                ),
                'styling' => array(
                    'font_family' => 'Arial, sans-serif',
                    'font_size' => '12px',
                    'primary_color' => '#000000',
                    'secondary_color' => '#666666',
                    'border_color' => '#dddddd',
                    'header_bg_color' => '#f2f2f2'
                )
            ),
            'credit_note' => array(
                'name' => 'Nota de Crédito',
                'header' => array(
                    'show_logo' => true,
                    'logo_position' => 'center',
                    'company_name' => true,
                    'company_rif' => true,
                    'company_address' => true,
                    'company_phone' => true,
                    'company_email' => true
                ),
                'body' => array(
                    'show_credit_note_number' => true,
                    'show_date' => true,
                    'show_original_invoice' => true,
                    'show_customer_info' => true,
                    'show_items_table' => true,
                    'show_totals' => true,
                    'show_taxes' => true
                ),
                'footer' => array(
                    'show_payment_method' => true,
                    'show_currency' => true,
                    'show_terms' => true,
                    'show_signature' => true
                ),
                'styling' => array(
                    'font_family' => 'Arial, sans-serif',
                    'font_size' => '12px',
                    'primary_color' => '#000000',
                    'secondary_color' => '#666666',
                    'border_color' => '#dddddd',
                    'header_bg_color' => '#f2f2f2'
                )
            ),
            'debit_note' => array(
                'name' => 'Nota de Débito',
                'header' => array(
                    'show_logo' => true,
                    'logo_position' => 'center',
                    'company_name' => true,
                    'company_rif' => true,
                    'company_address' => true,
                    'company_phone' => true,
                    'company_email' => true
                ),
                'body' => array(
                    'show_debit_note_number' => true,
                    'show_date' => true,
                    'show_original_invoice' => true,
                    'show_customer_info' => true,
                    'show_items_table' => true,
                    'show_totals' => true,
                    'show_taxes' => true
                ),
                'footer' => array(
                    'show_payment_method' => true,
                    'show_currency' => true,
                    'show_terms' => true,
                    'show_signature' => true
                ),
                'styling' => array(
                    'font_family' => 'Arial, sans-serif',
                    'font_size' => '12px',
                    'primary_color' => '#000000',
                    'secondary_color' => '#666666',
                    'border_color' => '#dddddd',
                    'header_bg_color' => '#f2f2f2'
                )
            ),
            'receipt' => array(
                'name' => 'Recibo',
                'header' => array(
                    'show_logo' => true,
                    'logo_position' => 'center',
                    'company_name' => true,
                    'company_rif' => true,
                    'company_address' => true,
                    'company_phone' => true,
                    'company_email' => true
                ),
                'body' => array(
                    'show_receipt_number' => true,
                    'show_date' => true,
                    'show_customer_info' => true,
                    'show_items_table' => true,
                    'show_totals' => true,
                    'show_taxes' => true
                ),
                'footer' => array(
                    'show_payment_method' => true,
                    'show_currency' => true,
                    'show_terms' => true,
                    'show_signature' => true
                ),
                'styling' => array(
                    'font_family' => 'Arial, sans-serif',
                    'font_size' => '12px',
                    'primary_color' => '#000000',
                    'secondary_color' => '#666666',
                    'border_color' => '#dddddd',
                    'header_bg_color' => '#f2f2f2'
                )
            ),
            'proforma' => array(
                'name' => 'Proforma',
                'header' => array(
                    'show_logo' => true,
                    'logo_position' => 'center',
                    'company_name' => true,
                    'company_rif' => true,
                    'company_address' => true,
                    'company_phone' => true,
                    'company_email' => true
                ),
                'body' => array(
                    'show_proforma_number' => true,
                    'show_date' => true,
                    'show_customer_info' => true,
                    'show_items_table' => true,
                    'show_totals' => true,
                    'show_taxes' => true
                ),
                'footer' => array(
                    'show_payment_method' => true,
                    'show_currency' => true,
                    'show_terms' => true,
                    'show_signature' => true
                ),
                'styling' => array(
                    'font_family' => 'Arial, sans-serif',
                    'font_size' => '12px',
                    'primary_color' => '#000000',
                    'secondary_color' => '#666666',
                    'border_color' => '#dddddd',
                    'header_bg_color' => '#f2f2f2'
                )
            )
        );

        return $default_templates[$type] ?? array();
    }

    /**
     * Guardar plantilla personalizada
     *
     * @param string $type Tipo de documento
     * @param array $template_data Datos de la plantilla
     * @return bool
     */
    public function save_template($type, $template_data) {
        if (!isset($this->templates[$type])) {
            return false;
        }

        // Validar datos de la plantilla
        if (!$this->validate_template_data($template_data)) {
            return false;
        }

        // Guardar plantilla
        $settings = WCVS_Core::get_instance()->get_settings();
        $electronic_billing_settings = $settings->get('electronic_billing', array());
        $electronic_billing_settings['templates'][$type] = $template_data;
        $settings->set('electronic_billing', $electronic_billing_settings);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Plantilla {$type} guardada correctamente");

        return true;
    }

    /**
     * Validar datos de la plantilla
     *
     * @param array $template_data Datos de la plantilla
     * @return bool
     */
    private function validate_template_data($template_data) {
        $required_sections = array('header', 'body', 'footer', 'styling');
        
        foreach ($required_sections as $section) {
            if (!isset($template_data[$section])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generar HTML de la plantilla
     *
     * @param string $type Tipo de documento
     * @param array $data Datos del documento
     * @return string
     */
    public function generate_html($type, $data) {
        $template = $this->get_template($type);
        if (!$template) {
            return '';
        }

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo esc_html($template['name']); ?> <?php echo esc_html($data['invoice_number'] ?? ''); ?></title>
            <style>
                <?php echo $this->generate_css($template); ?>
            </style>
        </head>
        <body>
            <?php echo $this->generate_header_html($template, $data); ?>
            <?php echo $this->generate_body_html($template, $data); ?>
            <?php echo $this->generate_footer_html($template, $data); ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Generar CSS de la plantilla
     *
     * @param array $template Plantilla
     * @return string
     */
    private function generate_css($template) {
        $styling = $template['styling'];
        
        return "
            body {
                font-family: {$styling['font_family']};
                font-size: {$styling['font_size']};
                color: {$styling['primary_color']};
                margin: 0;
                padding: 20px;
                line-height: 1.4;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                background-color: {$styling['header_bg_color']};
                padding: 20px;
                border-radius: 5px;
            }
            .header h1 {
                color: {$styling['primary_color']};
                margin: 0 0 10px 0;
                font-size: 24px;
            }
            .header h2 {
                color: {$styling['secondary_color']};
                margin: 0;
                font-size: 18px;
            }
            .company-info {
                margin-bottom: 20px;
                padding: 15px;
                border: 1px solid {$styling['border_color']};
                border-radius: 5px;
            }
            .company-info h3 {
                margin-top: 0;
                color: {$styling['primary_color']};
            }
            .invoice-info {
                margin-bottom: 20px;
                padding: 15px;
                border: 1px solid {$styling['border_color']};
                border-radius: 5px;
            }
            .invoice-info h3 {
                margin-top: 0;
                color: {$styling['primary_color']};
            }
            .customer-info {
                margin-bottom: 20px;
                padding: 15px;
                border: 1px solid {$styling['border_color']};
                border-radius: 5px;
            }
            .customer-info h3 {
                margin-top: 0;
                color: {$styling['primary_color']};
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                border: 1px solid {$styling['border_color']};
            }
            .items-table th,
            .items-table td {
                border: 1px solid {$styling['border_color']};
                padding: 8px;
                text-align: left;
            }
            .items-table th {
                background-color: {$styling['header_bg_color']};
                color: {$styling['primary_color']};
                font-weight: bold;
            }
            .totals {
                margin-top: 20px;
                padding: 15px;
                border: 1px solid {$styling['border_color']};
                border-radius: 5px;
            }
            .totals table {
                width: 100%;
            }
            .totals td {
                padding: 5px;
            }
            .totals .total-row {
                font-weight: bold;
                border-top: 2px solid {$styling['primary_color']};
                color: {$styling['primary_color']};
            }
            .footer {
                margin-top: 40px;
                text-align: center;
                padding: 20px;
                border-top: 1px solid {$styling['border_color']};
            }
            .footer p {
                margin: 5px 0;
                color: {$styling['secondary_color']};
            }
        ";
    }

    /**
     * Generar HTML del header
     *
     * @param array $template Plantilla
     * @param array $data Datos del documento
     * @return string
     */
    private function generate_header_html($template, $data) {
        $header = $template['header'];
        $html = '<div class="header">';
        
        if ($header['show_logo'] && $header['logo_position'] === 'center') {
            $html .= '<img src="' . esc_url($this->get_company_logo()) . '" alt="Logo" style="max-height: 80px; margin-bottom: 10px;">';
        }
        
        if ($header['show_company_name']) {
            $html .= '<h1>' . esc_html($data['company']['name']) . '</h1>';
        }
        
        if ($header['show_company_rif']) {
            $html .= '<h2>RIF: ' . esc_html($data['company']['rif']) . '</h2>';
        }
        
        $html .= '</div>';
        
        // Información de la empresa
        if ($header['show_company_address'] || $header['show_company_phone'] || $header['show_company_email']) {
            $html .= '<div class="company-info">';
            $html .= '<h3>Datos de la Empresa</h3>';
            
            if ($header['show_company_address']) {
                $html .= '<p><strong>Dirección:</strong> ' . esc_html($data['company']['address']) . '</p>';
            }
            
            if ($header['show_company_phone']) {
                $html .= '<p><strong>Teléfono:</strong> ' . esc_html($data['company']['phone']) . '</p>';
            }
            
            if ($header['show_company_email']) {
                $html .= '<p><strong>Email:</strong> ' . esc_html($data['company']['email']) . '</p>';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }

    /**
     * Generar HTML del body
     *
     * @param array $template Plantilla
     * @param array $data Datos del documento
     * @return string
     */
    private function generate_body_html($template, $data) {
        $body = $template['body'];
        $html = '';
        
        // Información del documento
        if ($body['show_invoice_number'] || $body['show_date'] || $body['show_order_number']) {
            $html .= '<div class="invoice-info">';
            $html .= '<h3>Información del Documento</h3>';
            
            if ($body['show_invoice_number']) {
                $html .= '<p><strong>Número:</strong> ' . esc_html($data['invoice_number']) . '</p>';
            }
            
            if ($body['show_date']) {
                $html .= '<p><strong>Fecha:</strong> ' . esc_html($data['order']['date']) . '</p>';
            }
            
            if ($body['show_order_number']) {
                $html .= '<p><strong>Pedido:</strong> #' . esc_html($data['order']['number']) . '</p>';
            }
            
            $html .= '</div>';
        }
        
        // Información del cliente
        if ($body['show_customer_info']) {
            $html .= '<div class="customer-info">';
            $html .= '<h3>Datos del Cliente</h3>';
            $html .= '<p><strong>Nombre:</strong> ' . esc_html($data['customer']['name']) . '</p>';
            $html .= '<p><strong>RIF:</strong> ' . esc_html($data['customer']['rif']) . '</p>';
            $html .= '<p><strong>Dirección:</strong> ' . esc_html($data['customer']['address']) . '</p>';
            $html .= '<p><strong>Teléfono:</strong> ' . esc_html($data['customer']['phone']) . '</p>';
            $html .= '<p><strong>Email:</strong> ' . esc_html($data['customer']['email']) . '</p>';
            $html .= '</div>';
        }
        
        // Tabla de items
        if ($body['show_items_table']) {
            $html .= '<table class="items-table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>Descripción</th>';
            $html .= '<th>SKU</th>';
            $html .= '<th>Cantidad</th>';
            $html .= '<th>Precio</th>';
            $html .= '<th>Impuestos</th>';
            $html .= '<th>Total</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($data['items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($item['name']) . '</td>';
                $html .= '<td>' . esc_html($item['sku']) . '</td>';
                $html .= '<td>' . esc_html($item['quantity']) . '</td>';
                $html .= '<td>' . wc_price($item['price']) . '</td>';
                $html .= '<td>' . wc_price($item['tax']) . '</td>';
                $html .= '<td>' . wc_price($item['total']) . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
        }
        
        // Totales
        if ($body['show_totals']) {
            $html .= '<div class="totals">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<td><strong>Subtotal:</strong></td>';
            $html .= '<td>' . wc_price($data['totals']['subtotal']) . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td><strong>Envío:</strong></td>';
            $html .= '<td>' . wc_price($data['totals']['shipping_total']) . '</td>';
            $html .= '</tr>';
            
            if ($body['show_taxes']) {
                $html .= '<tr>';
                $html .= '<td><strong>IVA (16%):</strong></td>';
                $html .= '<td>' . wc_price($data['fiscal']['iva_amount']) . '</td>';
                $html .= '</tr>';
                
                if ($data['fiscal']['igtf_amount'] > 0) {
                    $html .= '<tr>';
                    $html .= '<td><strong>IGTF (3%):</strong></td>';
                    $html .= '<td>' . wc_price($data['fiscal']['igtf_amount']) . '</td>';
                    $html .= '</tr>';
                }
                
                if ($data['fiscal']['islr_amount'] > 0) {
                    $html .= '<tr>';
                    $html .= '<td><strong>ISLR (1%):</strong></td>';
                    $html .= '<td>' . wc_price($data['fiscal']['islr_amount']) . '</td>';
                    $html .= '</tr>';
                }
            }
            
            $html .= '<tr class="total-row">';
            $html .= '<td><strong>TOTAL:</strong></td>';
            $html .= '<td>' . wc_price($data['totals']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '</div>';
        }
        
        return $html;
    }

    /**
     * Generar HTML del footer
     *
     * @param array $template Plantilla
     * @param array $data Datos del documento
     * @return string
     */
    private function generate_footer_html($template, $data) {
        $footer = $template['footer'];
        $html = '<div class="footer">';
        
        if ($footer['show_payment_method']) {
            $html .= '<p><strong>Método de Pago:</strong> ' . esc_html($data['order']['payment_method']) . '</p>';
        }
        
        if ($footer['show_currency']) {
            $html .= '<p><strong>Moneda:</strong> ' . esc_html($data['order']['currency']) . '</p>';
        }
        
        if ($footer['show_terms']) {
            $html .= '<p><strong>Términos y Condiciones:</strong> ' . esc_html($this->get_terms_and_conditions()) . '</p>';
        }
        
        if ($footer['show_signature']) {
            $html .= '<p><strong>Firma Digital:</strong> ' . esc_html($this->get_digital_signature()) . '</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Obtener logo de la empresa
     *
     * @return string
     */
    private function get_company_logo() {
        $logo_url = $this->settings['company_data']['logo_url'] ?? '';
        
        if (empty($logo_url)) {
            // Logo por defecto
            $logo_url = WCVS_PLUGIN_URL . 'assets/images/default-logo.png';
        }
        
        return $logo_url;
    }

    /**
     * Obtener términos y condiciones
     *
     * @return string
     */
    private function get_terms_and_conditions() {
        return $this->settings['terms_and_conditions'] ?? 'Términos y condiciones aplicables según la legislación venezolana.';
    }

    /**
     * Obtener firma digital
     *
     * @return string
     */
    private function get_digital_signature() {
        return $this->settings['digital_signature'] ?? 'Documento firmado digitalmente según normativas SENIAT.';
    }

    /**
     * Obtener todas las plantillas
     *
     * @return array
     */
    public function get_all_templates() {
        $templates = array();
        
        foreach ($this->templates as $type => $name) {
            $templates[$type] = array(
                'name' => $name,
                'template' => $this->get_template($type)
            );
        }
        
        return $templates;
    }

    /**
     * Restaurar plantilla por defecto
     *
     * @param string $type Tipo de documento
     * @return bool
     */
    public function restore_default_template($type) {
        if (!isset($this->templates[$type])) {
            return false;
        }

        $default_template = $this->get_default_template($type);
        return $this->save_template($type, $default_template);
    }

    /**
     * Exportar plantilla
     *
     * @param string $type Tipo de documento
     * @return string|false
     */
    public function export_template($type) {
        $template = $this->get_template($type);
        if (!$template) {
            return false;
        }

        $export_data = array(
            'type' => $type,
            'name' => $template['name'],
            'template' => $template,
            'exported_at' => current_time('mysql'),
            'version' => '1.0'
        );

        return json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Importar plantilla
     *
     * @param string $json_data Datos JSON de la plantilla
     * @return bool
     */
    public function import_template($json_data) {
        $data = json_decode($json_data, true);
        if (!$data || !isset($data['type']) || !isset($data['template'])) {
            return false;
        }

        return $this->save_template($data['type'], $data['template']);
    }
}
