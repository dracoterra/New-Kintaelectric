<?php
/**
 * Gestor de Emails - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar notificaciones por email
 */
class WCVS_Email_Manager {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Plantillas de email
     *
     * @var array
     */
    private $email_templates = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('notifications', array());
        $this->load_email_templates();
    }

    /**
     * Cargar plantillas de email
     */
    private function load_email_templates() {
        $this->email_templates = array(
            'order_created' => array(
                'subject' => 'Pedido #{order_number} creado - {site_name}',
                'heading' => '¡Gracias por tu pedido!',
                'message' => 'Tu pedido #{order_number} ha sido creado exitosamente y está siendo procesado.',
                'footer' => 'Gracias por elegirnos.'
            ),
            'order_processing' => array(
                'subject' => 'Pedido #{order_number} en procesamiento - {site_name}',
                'heading' => 'Tu pedido está siendo procesado',
                'message' => 'Tu pedido #{order_number} está siendo preparado para el envío.',
                'footer' => 'Te mantendremos informado sobre el progreso.'
            ),
            'order_completed' => array(
                'subject' => 'Pedido #{order_number} completado - {site_name}',
                'heading' => '¡Tu pedido está listo!',
                'message' => 'Tu pedido #{order_number} ha sido completado y está listo para el envío.',
                'footer' => 'Gracias por tu compra.'
            ),
            'order_cancelled' => array(
                'subject' => 'Pedido #{order_number} cancelado - {site_name}',
                'heading' => 'Pedido cancelado',
                'message' => 'Tu pedido #{order_number} ha sido cancelado.',
                'footer' => 'Si tienes alguna pregunta, contáctanos.'
            ),
            'payment_received' => array(
                'subject' => 'Pago recibido para pedido #{order_number} - {site_name}',
                'heading' => 'Pago confirmado',
                'message' => 'Hemos recibido tu pago para el pedido #{order_number}.',
                'footer' => 'Procesaremos tu pedido pronto.'
            ),
            'payment_failed' => array(
                'subject' => 'Pago fallido para pedido #{order_number} - {site_name}',
                'heading' => 'Problema con el pago',
                'message' => 'No pudimos procesar el pago para tu pedido #{order_number}.',
                'footer' => 'Por favor, intenta nuevamente o contáctanos.'
            ),
            'invoice_generated' => array(
                'subject' => 'Factura #{invoice_number} generada - {site_name}',
                'heading' => 'Factura generada',
                'message' => 'Tu factura #{invoice_number} ha sido generada y está disponible para descarga.',
                'footer' => 'Conserva esta factura para tus registros.'
            ),
            'invoice_sent_to_seniat' => array(
                'subject' => 'Factura #{invoice_number} enviada a SENIAT - {site_name}',
                'heading' => 'Factura enviada a SENIAT',
                'message' => 'Tu factura #{invoice_number} ha sido enviada exitosamente a SENIAT.',
                'footer' => 'La factura está oficialmente registrada.'
            ),
            'shipment_created' => array(
                'subject' => 'Envío creado para pedido #{order_number} - {site_name}',
                'heading' => 'Envío creado',
                'message' => 'Se ha creado un envío para tu pedido #{order_number}.',
                'footer' => 'Te enviaremos el número de seguimiento pronto.'
            ),
            'shipment_shipped' => array(
                'subject' => 'Pedido #{order_number} despachado - {site_name}',
                'heading' => '¡Tu pedido está en camino!',
                'message' => 'Tu pedido #{order_number} ha sido despachado. Número de seguimiento: {tracking_number}',
                'footer' => 'Puedes rastrear tu envío en cualquier momento.'
            ),
            'shipment_delivered' => array(
                'subject' => 'Pedido #{order_number} entregado - {site_name}',
                'heading' => '¡Pedido entregado!',
                'message' => 'Tu pedido #{order_number} ha sido entregado exitosamente.',
                'footer' => 'Esperamos que disfrutes tu compra.'
            ),
            'low_stock' => array(
                'subject' => 'Stock bajo: {product_name} - {site_name}',
                'heading' => 'Alerta de stock bajo',
                'message' => 'El producto {product_name} tiene stock bajo ({stock_quantity} unidades restantes).',
                'footer' => 'Considera reponer el inventario.'
            ),
            'price_change' => array(
                'subject' => 'Cambio de precio: {product_name} - {site_name}',
                'heading' => 'Precio actualizado',
                'message' => 'El precio del producto {product_name} ha sido actualizado.',
                'footer' => 'Revisa el nuevo precio en nuestro sitio web.'
            ),
            'currency_rate_updated' => array(
                'subject' => 'Tasa de cambio actualizada - {site_name}',
                'heading' => 'Tasa de cambio actualizada',
                'message' => 'La tasa de cambio USD a VES ha sido actualizada: {exchange_rate}',
                'footer' => 'Los precios han sido actualizados automáticamente.'
            ),
            'seniat_report_generated' => array(
                'subject' => 'Reporte SENIAT generado - {site_name}',
                'heading' => 'Reporte SENIAT generado',
                'message' => 'El reporte SENIAT {report_type} ha sido generado exitosamente.',
                'footer' => 'El reporte está disponible en el panel de administración.'
            )
        );
    }

    /**
     * Enviar notificación por email
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return bool
     */
    public function send_notification($event, $data) {
        // Verificar si el email está habilitado para este evento
        if (!$this->is_email_enabled_for_event($event)) {
            return false;
        }

        // Obtener plantilla de email
        $template = $this->get_email_template($event);
        if (!$template) {
            return false;
        }

        // Preparar datos para el email
        $email_data = $this->prepare_email_data($event, $data, $template);

        // Enviar email
        return $this->send_email($email_data);
    }

    /**
     * Verificar si el email está habilitado para el evento
     *
     * @param string $event Evento
     * @return bool
     */
    private function is_email_enabled_for_event($event) {
        $email_settings = $this->settings['email'] ?? array();
        return $email_settings['enabled'] ?? true;
    }

    /**
     * Obtener plantilla de email
     *
     * @param string $event Evento
     * @return array|false
     */
    private function get_email_template($event) {
        // Verificar si hay plantilla personalizada
        $custom_template = $this->settings['email']['templates'][$event] ?? null;
        if ($custom_template) {
            return $custom_template;
        }

        // Usar plantilla por defecto
        return $this->email_templates[$event] ?? false;
    }

    /**
     * Preparar datos para el email
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @param array $template Plantilla
     * @return array
     */
    private function prepare_email_data($event, $data, $template) {
        $email_data = array(
            'to' => $this->get_recipient_email($event, $data),
            'subject' => $this->process_template($template['subject'], $data),
            'heading' => $this->process_template($template['heading'], $data),
            'message' => $this->process_template($template['message'], $data),
            'footer' => $this->process_template($template['footer'], $data),
            'attachments' => $this->get_email_attachments($event, $data)
        );

        return $email_data;
    }

    /**
     * Obtener email del destinatario
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return string
     */
    private function get_recipient_email($event, $data) {
        // Para eventos de pedido, usar email del cliente
        if (isset($data['order']['customer_email'])) {
            return $data['order']['customer_email'];
        }

        // Para eventos de admin, usar email del administrador
        if (in_array($event, array('low_stock', 'seniat_report_generated'))) {
            return get_option('admin_email');
        }

        // Email por defecto
        return get_option('admin_email');
    }

    /**
     * Procesar plantilla con datos
     *
     * @param string $template Plantilla
     * @param array $data Datos
     * @return string
     */
    private function process_template($template, $data) {
        $replacements = array(
            '{site_name}' => get_bloginfo('name'),
            '{site_url}' => get_site_url(),
            '{admin_email}' => get_option('admin_email'),
            '{current_date}' => date_i18n(get_option('date_format')),
            '{current_time}' => date_i18n(get_option('time_format'))
        );

        // Añadir datos específicos según el evento
        if (isset($data['order'])) {
            $order = $data['order'];
            $replacements = array_merge($replacements, array(
                '{order_number}' => $order['number'],
                '{order_id}' => $order['id'],
                '{order_total}' => wc_price($order['total']),
                '{order_status}' => $order['status'],
                '{customer_name}' => $order['customer_name'],
                '{customer_email}' => $order['customer_email'],
                '{payment_method}' => $order['payment_method'],
                '{order_date}' => $order['date_created']
            ));
        }

        if (isset($data['invoice'])) {
            $invoice = $data['invoice'];
            $replacements = array_merge($replacements, array(
                '{invoice_number}' => $invoice['invoice_number'],
                '{invoice_status}' => $invoice['invoice_status'],
                '{seniat_status}' => $invoice['seniat_status']
            ));
        }

        if (isset($data['shipment'])) {
            $shipment = $data['shipment'];
            $replacements = array_merge($replacements, array(
                '{tracking_number}' => $shipment['tracking_number'],
                '{shipment_status}' => $shipment['shipment_status'],
                '{shipping_method}' => $shipment['shipping_method']
            ));
        }

        if (isset($data['product'])) {
            $product = $data['product'];
            $replacements = array_merge($replacements, array(
                '{product_name}' => $product['name'],
                '{product_sku}' => $product['sku'],
                '{product_price}' => wc_price($product['price']),
                '{stock_quantity}' => $product['stock_quantity']
            ));
        }

        if (isset($data['currency'])) {
            $currency = $data['currency'];
            $replacements = array_merge($replacements, array(
                '{exchange_rate}' => number_format($currency['usd_to_ves_rate'], 2),
                '{currency_source}' => $currency['source']
            ));
        }

        if (isset($data['report'])) {
            $report = $data['report'];
            $replacements = array_merge($replacements, array(
                '{report_type}' => $report['type'],
                '{report_period}' => $report['period']
            ));
        }

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Obtener archivos adjuntos para el email
     *
     * @param string $event Evento
     * @param array $data Datos de la notificación
     * @return array
     */
    private function get_email_attachments($event, $data) {
        $attachments = array();

        // Para facturas generadas, adjuntar PDF
        if ($event === 'invoice_generated' && isset($data['invoice']['invoice_number'])) {
            $invoice_pdf = $this->get_invoice_pdf_path($data['invoice']['invoice_number']);
            if ($invoice_pdf && file_exists($invoice_pdf)) {
                $attachments[] = $invoice_pdf;
            }
        }

        return $attachments;
    }

    /**
     * Obtener ruta del PDF de factura
     *
     * @param string $invoice_number Número de factura
     * @return string|false
     */
    private function get_invoice_pdf_path($invoice_number) {
        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['basedir'] . '/wcvs-invoices/invoice-' . $invoice_number . '.pdf';
        
        return file_exists($pdf_path) ? $pdf_path : false;
    }

    /**
     * Enviar email
     *
     * @param array $email_data Datos del email
     * @return bool
     */
    private function send_email($email_data) {
        // Configurar headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

        // Generar contenido HTML
        $html_content = $this->generate_html_email($email_data);

        // Enviar email
        $sent = wp_mail(
            $email_data['to'],
            $email_data['subject'],
            $html_content,
            $headers,
            $email_data['attachments']
        );

        if ($sent) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Email enviado a: {$email_data['to']}");
        } else {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Error al enviar email a: {$email_data['to']}");
        }

        return $sent;
    }

    /**
     * Generar contenido HTML del email
     *
     * @param array $email_data Datos del email
     * @return string
     */
    private function generate_html_email($email_data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($email_data['subject']); ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .email-header {
                    background: #0073aa;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .email-body {
                    background: #f9f9f9;
                    padding: 30px;
                    border-radius: 0 0 5px 5px;
                }
                .email-heading {
                    font-size: 20px;
                    color: #0073aa;
                    margin-bottom: 20px;
                }
                .email-message {
                    font-size: 16px;
                    margin-bottom: 20px;
                }
                .email-footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 14px;
                    color: #666;
                }
                .button {
                    display: inline-block;
                    background: #0073aa;
                    color: white;
                    padding: 12px 24px;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 10px 0;
                }
                .button:hover {
                    background: #005a87;
                }
            </style>
        </head>
        <body>
            <div class="email-header">
                <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            </div>
            <div class="email-body">
                <h2 class="email-heading"><?php echo esc_html($email_data['heading']); ?></h2>
                <div class="email-message">
                    <?php echo wp_kses_post(nl2br($email_data['message'])); ?>
                </div>
                <div class="email-footer">
                    <?php echo esc_html($email_data['footer']); ?>
                    <br><br>
                    <p><strong><?php echo esc_html(get_bloginfo('name')); ?></strong><br>
                    <?php echo esc_html(get_site_url()); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Obtener plantillas de email disponibles
     *
     * @return array
     */
    public function get_email_templates() {
        return $this->email_templates;
    }

    /**
     * Guardar plantilla personalizada
     *
     * @param string $event Evento
     * @param array $template Plantilla
     * @return bool
     */
    public function save_custom_template($event, $template) {
        $settings = WCVS_Core::get_instance()->get_settings();
        $notifications_settings = $settings->get('notifications', array());
        
        if (!isset($notifications_settings['email']['templates'])) {
            $notifications_settings['email']['templates'] = array();
        }
        
        $notifications_settings['email']['templates'][$event] = $template;
        $settings->set('notifications', $notifications_settings);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Plantilla de email personalizada guardada para evento: {$event}");

        return true;
    }

    /**
     * Restaurar plantilla por defecto
     *
     * @param string $event Evento
     * @return bool
     */
    public function restore_default_template($event) {
        if (!isset($this->email_templates[$event])) {
            return false;
        }

        $settings = WCVS_Core::get_instance()->get_settings();
        $notifications_settings = $settings->get('notifications', array());
        
        if (isset($notifications_settings['email']['templates'][$event])) {
            unset($notifications_settings['email']['templates'][$event]);
            $settings->set('notifications', $notifications_settings);
        }

        WCVS_Logger::info(WCVS_Logger::CONTEXT_NOTIFICATIONS, "Plantilla de email restaurada por defecto para evento: {$event}");

        return true;
    }

    /**
     * Probar plantilla de email
     *
     * @param string $event Evento
     * @param string $test_email Email de prueba
     * @return array
     */
    public function test_template($event, $test_email) {
        $template = $this->get_email_template($event);
        if (!$template) {
            return array(
                'success' => false,
                'message' => __('Plantilla no encontrada', 'wcvs')
            );
        }

        // Datos de prueba
        $test_data = array(
            'order' => array(
                'number' => 'TEST-001',
                'id' => 999,
                'total' => 100.00,
                'status' => 'processing',
                'customer_name' => 'Cliente de Prueba',
                'customer_email' => $test_email,
                'payment_method' => 'Transferencia Bancaria',
                'date_created' => current_time('Y-m-d H:i:s')
            )
        );

        $email_data = $this->prepare_email_data($event, $test_data, $template);
        $email_data['to'] = $test_email;

        $sent = $this->send_email($email_data);

        return array(
            'success' => $sent,
            'message' => $sent ? __('Email de prueba enviado correctamente', 'wcvs') : __('Error al enviar email de prueba', 'wcvs')
        );
    }
}
