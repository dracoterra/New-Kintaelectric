<?php
/**
 * Clase para generación de facturas electrónicas según normativas SENIAT Venezuela
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Electronic_Invoice {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
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
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->fiscal_settings = get_option('wvp_fiscal_settings', array());
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Generar factura electrónica al completar pedido
        add_action('woocommerce_order_status_completed', array($this, 'generate_electronic_invoice'));
        add_action('woocommerce_order_status_processing', array($this, 'generate_electronic_invoice'));
        
        // Añadir campos de facturación al checkout
        add_action('woocommerce_after_checkout_billing_form', array($this, 'add_billing_fiscal_fields'));
        add_action('woocommerce_checkout_process', array($this, 'validate_fiscal_fields'));
        
        // Guardar campos de facturación
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_fiscal_fields'));
        
        // Mostrar campos de facturación en admin
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_fiscal_fields_in_admin'));
    }
    
    /**
     * Añadir campos de facturación al checkout (adaptado para Venezuela)
     */
    public function add_billing_fiscal_fields($checkout) {
        // Solo mostrar si está habilitada la facturación electrónica
        if (!isset($this->fiscal_settings['enable_electronic_invoice']) || !$this->fiscal_settings['enable_electronic_invoice']) {
            return;
        }
        
        echo '<div id="wvp_fiscal_fields"><h3>' . __('Datos para Facturación (Opcional)', 'wvp') . '</h3>';
        echo '<p class="description">' . __('Si necesitas factura, completa estos datos. Si no, puedes dejarlos en blanco.', 'wvp') . '</p>';
        
        // Cédula de Identidad (más común en Venezuela)
        woocommerce_form_field('billing_cedula', array(
            'type' => 'text',
            'class' => array('form-row-wide'),
            'label' => __('Cédula de Identidad', 'wvp'),
            'placeholder' => __('V-12345678', 'wvp'),
            'required' => false,
        ), $checkout->get_value('billing_cedula'));
        
        // RIF (solo si es empresa)
        woocommerce_form_field('billing_rif', array(
            'type' => 'text',
            'class' => array('form-row-wide'),
            'label' => __('RIF (Solo si es empresa)', 'wvp'),
            'placeholder' => __('J-12345678-9', 'wvp'),
            'required' => false,
        ), $checkout->get_value('billing_rif'));
        
        // Nombre Completo
        woocommerce_form_field('billing_nombre_completo', array(
            'type' => 'text',
            'class' => array('form-row-wide'),
            'label' => __('Nombre Completo', 'wvp'),
            'placeholder' => __('Nombre y apellido completo', 'wvp'),
            'required' => false,
        ), $checkout->get_value('billing_nombre_completo'));
        
        // Dirección (simplificada)
        woocommerce_form_field('billing_direccion_simple', array(
            'type' => 'textarea',
            'class' => array('form-row-wide'),
            'label' => __('Dirección de Entrega', 'wvp'),
            'placeholder' => __('Ciudad, Estado, Dirección específica', 'wvp'),
            'required' => false,
        ), $checkout->get_value('billing_direccion_simple'));
        
        // Tipo de Cliente
        woocommerce_form_field('billing_tipo_cliente', array(
            'type' => 'select',
            'class' => array('form-row-wide'),
            'label' => __('Tipo de Cliente', 'wvp'),
            'options' => array(
                '' => __('Seleccionar...', 'wvp'),
                'consumidor_final' => __('Consumidor Final', 'wvp'),
                'persona_natural' => __('Persona Natural (con RIF)', 'wvp'),
                'persona_juridica' => __('Persona Jurídica', 'wvp'),
            ),
            'required' => false,
        ), $checkout->get_value('billing_tipo_cliente'));
        
        // Mostrar precio actual en bolívares
        $this->show_current_price_in_ves();
        
        echo '</div>';
    }
    
    /**
     * Mostrar precio actual en bolívares
     */
    private function show_current_price_in_ves() {
        if (class_exists('WVP_BCV_Integrator')) {
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate && $rate > 0) {
                echo '<div class="wvp-price-info" style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;">';
                echo '<h4>' . __('Información de Precios', 'wvp') . '</h4>';
                echo '<p><strong>' . __('Tasa BCV del día:', 'wvp') . '</strong> ' . number_format($rate, 2, ',', '.') . ' Bs./USD</p>';
                echo '<p class="description">' . __('Los precios pueden variar según la tasa de cambio del día.', 'wvp') . '</p>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Validar campos de facturación (adaptado para Venezuela)
     */
    public function validate_fiscal_fields() {
        // Solo validar si se seleccionó un tipo de cliente que requiere datos
        $tipo_cliente = isset($_POST['billing_tipo_cliente']) ? $_POST['billing_tipo_cliente'] : '';
        
        if ($tipo_cliente === 'persona_natural' || $tipo_cliente === 'persona_juridica') {
            // Validar cédula o RIF según el tipo
            if ($tipo_cliente === 'persona_natural') {
                if (empty($_POST['billing_cedula'])) {
                    wc_add_notice(__('La Cédula de Identidad es obligatoria para Persona Natural.', 'wvp'), 'error');
                } elseif (!empty($_POST['billing_cedula']) && !$this->validate_cedula_format($_POST['billing_cedula'])) {
                    wc_add_notice(__('El formato de la Cédula no es válido. Use el formato: V-12345678', 'wvp'), 'error');
                }
            } elseif ($tipo_cliente === 'persona_juridica') {
                if (empty($_POST['billing_rif'])) {
                    wc_add_notice(__('El RIF es obligatorio para Persona Jurídica.', 'wvp'), 'error');
                } elseif (!empty($_POST['billing_rif']) && !$this->validate_rif_format($_POST['billing_rif'])) {
                    wc_add_notice(__('El formato del RIF no es válido. Use el formato: J-12345678-9', 'wvp'), 'error');
                }
            }
            
            // Validar nombre completo
            if (empty($_POST['billing_nombre_completo'])) {
                wc_add_notice(__('El Nombre Completo es obligatorio para la facturación.', 'wvp'), 'error');
            }
        }
    }
    
    /**
     * Validar formato de RIF
     */
    private function validate_rif_format($rif) {
        // Patrón para RIF: J-12345678-9
        $pattern = '/^J-[0-9]{8}-[0-9]$/';
        return preg_match($pattern, strtoupper($rif));
    }
    
    /**
     * Validar formato de Cédula
     */
    private function validate_cedula_format($cedula) {
        // Patrón para Cédula: V-12345678
        $pattern = '/^V-[0-9]{8}$/';
        return preg_match($pattern, strtoupper($cedula));
    }
    
    /**
     * Guardar campos de facturación (adaptado para Venezuela)
     */
    public function save_fiscal_fields($order_id) {
        // Guardar cédula
        if (!empty($_POST['billing_cedula'])) {
            update_post_meta($order_id, '_billing_cedula', sanitize_text_field($_POST['billing_cedula']));
        }
        
        // Guardar RIF
        if (!empty($_POST['billing_rif'])) {
            update_post_meta($order_id, '_billing_rif', sanitize_text_field($_POST['billing_rif']));
        }
        
        // Guardar nombre completo
        if (!empty($_POST['billing_nombre_completo'])) {
            update_post_meta($order_id, '_billing_nombre_completo', sanitize_text_field($_POST['billing_nombre_completo']));
        }
        
        // Guardar dirección simple
        if (!empty($_POST['billing_direccion_simple'])) {
            update_post_meta($order_id, '_billing_direccion_simple', sanitize_textarea_field($_POST['billing_direccion_simple']));
        }
        
        // Guardar tipo de cliente
        if (!empty($_POST['billing_tipo_cliente'])) {
            update_post_meta($order_id, '_billing_tipo_cliente', sanitize_text_field($_POST['billing_tipo_cliente']));
        }
        
        // Guardar tasa de cambio del momento de la compra
        if (class_exists('WVP_BCV_Integrator')) {
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate && $rate > 0) {
                update_post_meta($order_id, '_exchange_rate_at_purchase', $rate);
                update_post_meta($order_id, '_exchange_rate_date', current_time('Y-m-d H:i:s'));
            }
        }
    }
    
    /**
     * Mostrar campos de facturación en admin
     */
    public function display_fiscal_fields_in_admin($order) {
        $rif = get_post_meta($order->get_id(), '_billing_rif', true);
        $razon_social = get_post_meta($order->get_id(), '_billing_razon_social', true);
        $direccion_fiscal = get_post_meta($order->get_id(), '_billing_direccion_fiscal', true);
        $tipo_contribuyente = get_post_meta($order->get_id(), '_billing_tipo_contribuyente', true);
        
        if ($rif || $razon_social || $direccion_fiscal || $tipo_contribuyente) {
            echo '<div class="address">';
            echo '<p><strong>' . __('Datos Fiscales:', 'wvp') . '</strong></p>';
            
            if ($rif) {
                echo '<p><strong>' . __('RIF/Cédula:', 'wvp') . '</strong> ' . esc_html($rif) . '</p>';
            }
            
            if ($razon_social) {
                echo '<p><strong>' . __('Razón Social:', 'wvp') . '</strong> ' . esc_html($razon_social) . '</p>';
            }
            
            if ($direccion_fiscal) {
                echo '<p><strong>' . __('Dirección Fiscal:', 'wvp') . '</strong><br>' . esc_html($direccion_fiscal) . '</p>';
            }
            
            if ($tipo_contribuyente) {
                $tipo_text = $tipo_contribuyente === 'persona_natural' ? __('Persona Natural', 'wvp') : __('Persona Jurídica', 'wvp');
                echo '<p><strong>' . __('Tipo de Contribuyente:', 'wvp') . '</strong> ' . $tipo_text . '</p>';
            }
            
            echo '</div>';
        }
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
        $tipo_cliente = get_post_meta($order_id, '_billing_tipo_cliente', true);
        if (empty($tipo_cliente) || $tipo_cliente === 'consumidor_final') {
            return; // No generar factura para consumidor final
        }
        
        // Verificar si ya se generó la factura
        $control_number = get_post_meta($order_id, '_seniat_control_number', true);
        if ($control_number) {
            return; // Ya se generó
        }
        
        // Generar número de control
        $control_number = $this->generate_control_number();
        
        // Guardar número de control
        update_post_meta($order_id, '_seniat_control_number', $control_number);
        
        // Generar XML de la factura
        $xml_content = $this->generate_invoice_xml($order, $control_number);
        
        // Guardar XML
        $this->save_invoice_xml($order_id, $xml_content);
        
        // Enviar por email si está habilitado
        if (isset($this->fiscal_settings['auto_send_invoices']) && $this->fiscal_settings['auto_send_invoices']) {
            $this->send_invoice_email($order, $control_number, $xml_content);
        }
        
        // Añadir nota al pedido
        $order->add_order_note(sprintf(__('Factura electrónica generada. Número de control: %s', 'wvp'), $control_number));
    }
    
    /**
     * Generar número de control
     */
    private function generate_control_number() {
        $prefix = isset($this->fiscal_settings['control_number_prefix']) ? $this->fiscal_settings['control_number_prefix'] : '00-';
        $next_number = isset($this->fiscal_settings['next_control_number']) ? $this->fiscal_settings['next_control_number'] : 1;
        
        $control_number = $prefix . str_pad($next_number, 8, '0', STR_PAD_LEFT);
        
        // Incrementar número para próximo uso
        $this->fiscal_settings['next_control_number'] = $next_number + 1;
        update_option('wvp_fiscal_settings', $this->fiscal_settings);
        
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
        $customer_cedula = get_post_meta($order->get_id(), '_billing_cedula', true);
        $customer_rif = get_post_meta($order->get_id(), '_billing_rif', true);
        $customer_name = get_post_meta($order->get_id(), '_billing_nombre_completo', true);
        $customer_address = get_post_meta($order->get_id(), '_billing_direccion_simple', true);
        $tipo_cliente = get_post_meta($order->get_id(), '_billing_tipo_cliente', true);
        
        // Obtener tasa de cambio del momento de la compra
        $exchange_rate = get_post_meta($order->get_id(), '_exchange_rate_at_purchase', true);
        $exchange_date = get_post_meta($order->get_id(), '_exchange_rate_date', true);
        
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
        $invoices_dir = $upload_dir['basedir'] . '/wvp-invoices/';
        
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
     * Enviar factura por email
     */
    private function send_invoice_email($order, $control_number, $xml_content) {
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        
        $subject = sprintf(__('Factura Electrónica #%s - %s', 'wvp'), $control_number, get_bloginfo('name'));
        
        $message = sprintf(__('Estimado/a %s,', 'wvp'), $customer_name) . "\n\n";
        $message .= sprintf(__('Adjunto encontrará su factura electrónica con número de control: %s', 'wvp'), $control_number) . "\n\n";
        $message .= __('Detalles del pedido:', 'wvp') . "\n";
        $message .= __('Número de pedido:', 'wvp') . ' ' . $order->get_order_number() . "\n";
        $message .= __('Total:', 'wvp') . ' ' . $order->get_formatted_order_total() . "\n\n";
        $message .= __('Gracias por su compra.', 'wvp') . "\n\n";
        $message .= get_bloginfo('name');
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Adjuntar XML
        $upload_dir = wp_upload_dir();
        $invoices_dir = $upload_dir['basedir'] . '/wvp-invoices/';
        $filename = 'factura_' . $order->get_id() . '_' . date('Y-m-d_H-i-s') . '.xml';
        $file_path = $invoices_dir . $filename;
        
        if (file_exists($file_path)) {
            $attachments = array($file_path);
        } else {
            $attachments = array();
        }
        
        wp_mail($customer_email, $subject, $message, $headers, $attachments);
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
}
