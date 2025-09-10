<?php
/**
 * Sistema de notificaciones WhatsApp para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_WhatsApp_Notifications {
    
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
        // Añadir botones de WhatsApp en pedidos
        add_action('woocommerce_admin_order_actions', array($this, 'add_whatsapp_buttons'), 10, 2);
        
        // Añadir meta box en pedidos individuales
        add_action('add_meta_boxes', array($this, 'add_whatsapp_meta_box'));
        
        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Añadir botones de WhatsApp en la lista de pedidos
     * 
     * @param array $actions Acciones existentes
     * @param WC_Order $order Pedido
     * @return array Acciones modificadas
     */
    public function add_whatsapp_buttons($actions, $order) {
        $phone = $this->get_customer_phone($order);
        
        if (!$phone) {
            return $actions;
        }
        
        // Botón de notificación de pago verificado
        if (in_array($order->get_status(), array('processing', 'completed'))) {
            $payment_url = $this->generate_whatsapp_url($order, 'payment_verified');
            $actions['whatsapp_payment'] = array(
                'url' => $payment_url,
                'name' => __('Notificar Pago (WA)', 'wvp'),
                'action' => 'whatsapp-payment'
            );
        }
        
        // Botón de notificación de envío
        if (in_array($order->get_status(), array('completed'))) {
            $shipping_url = $this->generate_whatsapp_url($order, 'shipping');
            $actions['whatsapp_shipping'] = array(
                'url' => $shipping_url,
                'name' => __('Notificar Envío (WA)', 'wvp'),
                'action' => 'whatsapp-shipping'
            );
        }
        
        return $actions;
    }
    
    /**
     * Añadir meta box de WhatsApp en pedidos individuales
     */
    public function add_whatsapp_meta_box() {
        add_meta_box(
            'wvp-whatsapp-notifications',
            __('Notificaciones WhatsApp', 'wvp'),
            array($this, 'render_whatsapp_meta_box'),
            'shop_order',
            'side',
            'high'
        );
    }
    
    /**
     * Renderizar meta box de WhatsApp
     * 
     * @param WP_Post $post Post del pedido
     */
    public function render_whatsapp_meta_box($post) {
        $order = wc_get_order($post->ID);
        
        if (!$order) {
            return;
        }
        
        $phone = $this->get_customer_phone($order);
        
        if (!$phone) {
            echo '<p>' . __('No hay número de teléfono disponible para este cliente.', 'wvp') . '</p>';
            return;
        }
        
        ?>
        <div class="wvp-whatsapp-meta-box">
            <style>
                .wvp-whatsapp-meta-box {
                    font-size: 13px;
                }
                .wvp-whatsapp-section {
                    margin-bottom: 15px;
                    padding: 10px;
                    background: #f9f9f9;
                    border-radius: 4px;
                }
                .wvp-whatsapp-section h4 {
                    margin: 0 0 10px 0;
                    color: #0073aa;
                }
                .wvp-whatsapp-button {
                    display: inline-block;
                    padding: 8px 12px;
                    background: #25D366;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    font-size: 12px;
                    margin-right: 5px;
                    margin-bottom: 5px;
                }
                .wvp-whatsapp-button:hover {
                    background: #128C7E;
                    color: white;
                }
                .wvp-whatsapp-button:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                }
                .wvp-shipping-guide {
                    width: 100%;
                    padding: 5px;
                    margin-top: 5px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                }
                .wvp-phone-display {
                    font-family: monospace;
                    background: #f1f1f1;
                    padding: 3px 6px;
                    border-radius: 3px;
                    font-size: 11px;
                }
            </style>
            
            <div class="wvp-phone-info">
                <strong><?php _e('Teléfono del cliente:', 'wvp'); ?></strong><br>
                <span class="wvp-phone-display"><?php echo esc_html($phone); ?></span>
            </div>
            
            <div class="wvp-whatsapp-section">
                <h4><?php _e('Notificación de Pago', 'wvp'); ?></h4>
                <p><?php _e('Notificar al cliente que su pago ha sido verificado:', 'wvp'); ?></p>
                <a href="<?php echo esc_url($this->generate_whatsapp_url($order, 'payment_verified')); ?>" 
                   target="_blank" 
                   class="wvp-whatsapp-button">
                    <?php _e('📱 Notificar Pago', 'wvp'); ?>
                </a>
            </div>
            
            <div class="wvp-whatsapp-section">
                <h4><?php _e('Notificación de Envío', 'wvp'); ?></h4>
                <p><?php _e('Notificar al cliente que su pedido ha sido enviado:', 'wvp'); ?></p>
                <input type="text" 
                       id="wvp-shipping-guide-<?php echo $order->get_id(); ?>" 
                       class="wvp-shipping-guide" 
                       placeholder="<?php _e('Número de guía de envío', 'wvp'); ?>" />
                <br>
                <a href="#" 
                   id="wvp-shipping-button-<?php echo $order->get_id(); ?>" 
                   class="wvp-whatsapp-button" 
                   data-order-id="<?php echo $order->get_id(); ?>">
                    <?php _e('📦 Notificar Envío', 'wvp'); ?>
                </a>
            </div>
            
            <div class="wvp-whatsapp-section">
                <h4><?php _e('Plantillas Disponibles', 'wvp'); ?></h4>
                <p><strong><?php _e('Placeholders:', 'wvp'); ?></strong></p>
                <ul style="margin: 5px 0; padding-left: 20px; font-size: 11px;">
                    <li><code>{customer_name}</code> - <?php _e('Nombre del cliente', 'wvp'); ?></li>
                    <li><code>{order_number}</code> - <?php _e('Número del pedido', 'wvp'); ?></li>
                    <li><code>{store_name}</code> - <?php _e('Nombre de la tienda', 'wvp'); ?></li>
                    <li><code>{shipping_guide}</code> - <?php _e('Número de guía', 'wvp'); ?></li>
                    <li><code>{order_total}</code> - <?php _e('Total del pedido', 'wvp'); ?></li>
                </ul>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#wvp-shipping-button-<?php echo $order->get_id(); ?>').on('click', function(e) {
                e.preventDefault();
                
                var orderId = $(this).data('order-id');
                var shippingGuide = $('#wvp-shipping-guide-' + orderId).val();
                
                if (!shippingGuide) {
                    alert('<?php _e('Por favor ingresa el número de guía de envío', 'wvp'); ?>');
                    return;
                }
                
                // Generar URL con guía de envío
                var baseUrl = '<?php echo $this->generate_whatsapp_url($order, 'shipping', 'PLACEHOLDER_GUIDE'); ?>';
                var finalUrl = baseUrl.replace('PLACEHOLDER_GUIDE', encodeURIComponent(shippingGuide));
                
                // Abrir WhatsApp
                window.open(finalUrl, '_blank');
            });
        });
        </script>
        <?php
    }
    
    /**
     * Obtener teléfono del cliente en formato internacional
     * 
     * @param WC_Order $order Pedido
     * @return string|false Teléfono en formato internacional o false
     */
    private function get_customer_phone($order) {
        $phone = $order->get_billing_phone();
        
        if (empty($phone)) {
            return false;
        }
        
        // Limpiar número
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Si no tiene código de país, añadir +58 (Venezuela)
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '58')) {
                $phone = '+' . $phone;
            } else {
                $phone = '+58' . $phone;
            }
        }
        
        return $phone;
    }
    
    /**
     * Generar URL de WhatsApp
     * 
     * @param WC_Order $order Pedido
     * @param string $type Tipo de notificación
     * @param string $shipping_guide Guía de envío (opcional)
     * @return string URL de WhatsApp
     */
    private function generate_whatsapp_url($order, $type, $shipping_guide = '') {
        $phone = $this->get_customer_phone($order);
        
        if (!$phone) {
            return '#';
        }
        
        $message = $this->get_message_template($order, $type, $shipping_guide);
        $encoded_message = urlencode($message);
        
        return "https://wa.me/" . preg_replace('/[^0-9]/', '', $phone) . "?text=" . $encoded_message;
    }
    
    /**
     * Obtener plantilla de mensaje
     * 
     * @param WC_Order $order Pedido
     * @param string $type Tipo de notificación
     * @param string $shipping_guide Guía de envío
     * @return string Mensaje formateado
     */
    private function get_message_template($order, $type, $shipping_guide = '') {
        $templates = $this->get_whatsapp_templates();
        $template = $templates[$type] ?? $templates['default'];
        
        // Reemplazar placeholders
        $replacements = array(
            '{customer_name}' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            '{order_number}' => '#' . $order->get_id(),
            '{store_name}' => get_bloginfo('name'),
            '{shipping_guide}' => $shipping_guide,
            '{order_total}' => $order->get_formatted_order_total()
        );
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Obtener plantillas de WhatsApp
     * 
     * @return array Plantillas de mensajes
     */
    private function get_whatsapp_templates() {
        $templates = get_option('wvp_whatsapp_templates', array());
        
        $default_templates = array(
            'payment_verified' => '¡Hola {customer_name}! 🎉 Tu pago del pedido {order_number} ha sido verificado exitosamente. Estamos preparando tu envío. ¡Gracias por tu compra en {store_name}!',
            'shipping' => '¡Hola {customer_name}! 📦 Tu pedido {order_number} ha sido enviado. Puedes rastrearlo con la guía: {shipping_guide}. ¡Gracias por comprar en {store_name}!',
            'default' => 'Hola {customer_name}, información sobre tu pedido {order_number} de {store_name}.'
        );
        
        return wp_parse_args($templates, $default_templates);
    }
    
    /**
     * Enqueue scripts administrativos
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'post.php' && $hook !== 'edit.php') {
            return;
        }
        
        wp_enqueue_script(
            'wvp-whatsapp-admin',
            WVP_PLUGIN_URL . 'assets/js/admin-whatsapp.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wvp-whatsapp-admin',
            WVP_PLUGIN_URL . 'assets/css/admin-whatsapp.css',
            array(),
            WVP_VERSION
        );
    }
}
