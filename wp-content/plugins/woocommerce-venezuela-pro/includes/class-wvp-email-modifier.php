<?php
/**
 * Clase para modificar correos transaccionales con información venezolana
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Email_Modifier {
    
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
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Modificar correos de pedidos
        add_action('woocommerce_email_before_order_table', array($this, 'add_venezuela_info_to_emails'), 10, 3);
        
        // Modificar asunto de correos
        add_filter('woocommerce_email_subject_new_order', array($this, 'modify_new_order_subject'), 10, 2);
        add_filter('woocommerce_email_subject_customer_processing_order', array($this, 'modify_processing_order_subject'), 10, 2);
        add_filter('woocommerce_email_subject_customer_completed_order', array($this, 'modify_completed_order_subject'), 10, 2);
        add_filter('woocommerce_email_subject_customer_invoice', array($this, 'modify_invoice_subject'), 10, 2);
        
        // Añadir información venezolana a los correos
        add_action('woocommerce_email_order_details', array($this, 'add_venezuela_order_details'), 10, 4);
    }
    
    /**
     * Añadir información venezolana a los correos
     * 
     * @param WC_Order $order Pedido
     * @param bool $sent_to_admin Si se envía al admin
     * @param bool $plain_text Si es texto plano
     * @param WC_Email $email Instancia del correo
     */
    public function add_venezuela_info_to_emails($order, $sent_to_admin, $plain_text, $email) {
        // Solo procesar correos de pedidos
        if (!in_array($email->id, array('new_order', 'customer_processing_order', 'customer_completed_order', 'customer_invoice'))) {
            return;
        }
        
        // Obtener información venezolana del pedido
        $venezuela_info = $this->get_order_venezuela_info($order);
        
        if (empty($venezuela_info)) {
            return;
        }
        
        if ($plain_text) {
            $this->add_venezuela_info_plain_text($venezuela_info, $order);
        } else {
            $this->add_venezuela_info_html($venezuela_info, $order);
        }
    }
    
    /**
     * Añadir información venezolana en texto plano
     * 
     * @param array $venezuela_info Información venezolana
     * @param WC_Order $order Pedido
     */
    private function add_venezuela_info_plain_text($venezuela_info, $order) {
        echo "\n" . __('INFORMACIÓN VENEZOLANA', 'wvp') . "\n";
        echo str_repeat('=', 30) . "\n\n";
        
        if (!empty($venezuela_info['cedula_rif'])) {
            echo __('Cédula o RIF:', 'wvp') . ' ' . $venezuela_info['cedula_rif'] . "\n";
        }
        
        if (!empty($venezuela_info['bcv_rate'])) {
            echo __('Tasa BCV al momento de la compra:', 'wvp') . ' ' . number_format($venezuela_info['bcv_rate'], 2, ',', '.') . ' Bs./USD' . "\n";
        }
        
        if (!empty($venezuela_info['payment_reference'])) {
            echo __('Referencia de pago:', 'wvp') . ' ' . $venezuela_info['payment_reference'] . "\n";
        }
        
        if ($venezuela_info['igtf_applied']) {
            echo __('IGTF aplicado:', 'wvp') . ' Sí';
            if (!empty($venezuela_info['igtf_amount'])) {
                echo ' (' . wc_price($venezuela_info['igtf_amount']) . ')';
            }
            echo "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Añadir información venezolana en HTML
     * 
     * @param array $venezuela_info Información venezolana
     * @param WC_Order $order Pedido
     */
    private function add_venezuela_info_html($venezuela_info, $order) {
        ?>
        <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 15px 0; border-radius: 4px;">
            <h3 style="margin: 0 0 15px 0; color: #495057;"><?php esc_html_e('Información Venezolana', 'wvp'); ?></h3>
            
            <div style="background: white; padding: 10px; border-radius: 4px;">
                <?php if (!empty($venezuela_info['cedula_rif'])): ?>
                    <p style="margin: 0 0 5px 0;"><strong><?php esc_html_e('Cédula o RIF:', 'wvp'); ?></strong> <?php echo esc_html($venezuela_info['cedula_rif']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($venezuela_info['bcv_rate'])): ?>
                    <p style="margin: 0 0 5px 0;"><strong><?php esc_html_e('Tasa BCV al momento de la compra:', 'wvp'); ?></strong> <?php echo esc_html(number_format($venezuela_info['bcv_rate'], 2, ',', '.')); ?> Bs./USD</p>
                <?php endif; ?>
                
                <?php if (!empty($venezuela_info['payment_reference'])): ?>
                    <p style="margin: 0 0 5px 0;"><strong><?php esc_html_e('Referencia de pago:', 'wvp'); ?></strong> <?php echo esc_html($venezuela_info['payment_reference']); ?></p>
                <?php endif; ?>
                
                <p style="margin: 0;">
                    <strong><?php esc_html_e('IGTF aplicado:', 'wvp'); ?></strong> 
                    <?php if ($venezuela_info['igtf_applied']): ?>
                        <?php esc_html_e('Sí', 'wvp'); ?>
                        <?php if (!empty($venezuela_info['igtf_amount'])): ?>
                            (<?php echo wc_price($venezuela_info['igtf_amount']); ?>)
                        <?php endif; ?>
                    <?php else: ?>
                        <?php esc_html_e('No', 'wvp'); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Añadir detalles venezolanos a los correos
     * 
     * @param WC_Order $order Pedido
     * @param bool $sent_to_admin Si se envía al admin
     * @param bool $plain_text Si es texto plano
     * @param WC_Email $email Instancia del correo
     */
    public function add_venezuela_order_details($order, $sent_to_admin, $plain_text, $email) {
        // Solo procesar correos de pedidos
        if (!in_array($email->id, array('new_order', 'customer_processing_order', 'customer_completed_order', 'customer_invoice'))) {
            return;
        }
        
        // Obtener información de IGTF
        $igtf_info = $this->get_order_igtf_info($order);
        
        if (empty($igtf_info)) {
            return;
        }
        
        if ($plain_text) {
            $this->add_igtf_info_plain_text($igtf_info);
        } else {
            $this->add_igtf_info_html($igtf_info);
        }
    }
    
    /**
     * Añadir información de IGTF en texto plano
     * 
     * @param array $igtf_info Información de IGTF
     */
    private function add_igtf_info_plain_text($igtf_info) {
        echo "\n" . __('DESGLOSE DE IMPUESTOS', 'wvp') . "\n";
        echo str_repeat('=', 30) . "\n\n";
        
        foreach ($igtf_info as $fee) {
            echo $fee['name'] . ': ' . wc_price($fee['total']) . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Añadir información de IGTF en HTML
     * 
     * @param array $igtf_info Información de IGTF
     */
    private function add_igtf_info_html($igtf_info) {
        ?>
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #856404;"><?php esc_html_e('Desglose de Impuestos', 'wvp'); ?></h4>
            
            <div style="background: white; padding: 10px; border-radius: 4px;">
                <?php foreach ($igtf_info as $fee): ?>
                    <p style="margin: 0 0 5px 0;">
                        <strong><?php echo esc_html($fee['name']); ?>:</strong> 
                        <?php echo wc_price($fee['total']); ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Modificar asunto del correo de nuevo pedido
     * 
     * @param string $subject Asunto original
     * @param WC_Order $order Pedido
     * @return string Asunto modificado
     */
    public function modify_new_order_subject($subject, $order) {
        $venezuela_info = $this->get_order_venezuela_info($order);
        
        if (!empty($venezuela_info['cedula_rif'])) {
            $subject = sprintf(
                __('[%s] Nuevo pedido #%s - %s', 'wvp'),
                get_bloginfo('name'),
                $order->get_order_number(),
                $venezuela_info['cedula_rif']
            );
        }
        
        return $subject;
    }
    
    /**
     * Modificar asunto del correo de pedido en procesamiento
     * 
     * @param string $subject Asunto original
     * @param WC_Order $order Pedido
     * @return string Asunto modificado
     */
    public function modify_processing_order_subject($subject, $order) {
        $venezuela_info = $this->get_order_venezuela_info($order);
        
        if (!empty($venezuela_info['bcv_rate'])) {
            $subject = sprintf(
                __('[%s] Pedido #%s en procesamiento (Tasa BCV: %s)', 'wvp'),
                get_bloginfo('name'),
                $order->get_order_number(),
                number_format($venezuela_info['bcv_rate'], 2, ',', '.')
            );
        }
        
        return $subject;
    }
    
    /**
     * Modificar asunto del correo de pedido completado
     * 
     * @param string $subject Asunto original
     * @param WC_Order $order Pedido
     * @return string Asunto modificado
     */
    public function modify_completed_order_subject($subject, $order) {
        $venezuela_info = $this->get_order_venezuela_info($order);
        
        if (!empty($venezuela_info['payment_reference'])) {
            $subject = sprintf(
                __('[%s] Pedido #%s completado (Ref: %s)', 'wvp'),
                get_bloginfo('name'),
                $order->get_order_number(),
                $venezuela_info['payment_reference']
            );
        }
        
        return $subject;
    }
    
    /**
     * Modificar asunto del correo de factura
     * 
     * @param string $subject Asunto original
     * @param WC_Order $order Pedido
     * @return string Asunto modificado
     */
    public function modify_invoice_subject($subject, $order) {
        $venezuela_info = $this->get_order_venezuela_info($order);
        
        if (!empty($venezuela_info['igtf_applied']) && $venezuela_info['igtf_applied']) {
            $subject = sprintf(
                __('[%s] Factura #%s (Incluye IGTF)', 'wvp'),
                get_bloginfo('name'),
                $order->get_order_number()
            );
        }
        
        return $subject;
    }
    
    /**
     * Obtener información venezolana del pedido
     * 
     * @param WC_Order $order Pedido
     * @return array Información venezolana
     */
    private function get_order_venezuela_info($order) {
        $cedula_rif = get_post_meta($order->get_id(), '_billing_cedula_rif', true);
        $bcv_rate = get_post_meta($order->get_id(), '_bcv_rate_at_purchase', true);
        $payment_reference = get_post_meta($order->get_id(), '_payment_reference', true);
        $igtf_amount = get_post_meta($order->get_id(), '_igtf_amount', true);
        $igtf_applied = get_post_meta($order->get_id(), '_igtf_applied', true);
        
        return array(
            'cedula_rif' => $cedula_rif,
            'bcv_rate' => $bcv_rate ? floatval($bcv_rate) : null,
            'payment_reference' => $payment_reference,
            'igtf_amount' => $igtf_amount ? floatval($igtf_amount) : null,
            'igtf_applied' => $igtf_applied === 'yes'
        );
    }
    
    /**
     * Obtener información de IGTF del pedido
     * 
     * @param WC_Order $order Pedido
     * @return array Información de IGTF
     */
    private function get_order_igtf_info($order) {
        $igtf_info = array();
        
        // Buscar fees de IGTF
        foreach ($order->get_fees() as $fee) {
            if (strpos($fee->get_name(), 'IGTF') !== false) {
                $igtf_info[] = array(
                    'name' => $fee->get_name(),
                    'amount' => $fee->get_amount(),
                    'total' => $fee->get_total()
                );
            }
        }
        
        return $igtf_info;
    }
}
