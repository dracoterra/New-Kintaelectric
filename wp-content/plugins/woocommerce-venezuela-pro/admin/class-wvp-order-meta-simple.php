<?php
// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Order_Meta {
    
    private $plugin;
    
    public function __construct() {
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action("add_meta_boxes", array($this, "add_order_meta_box"));
        add_filter("manage_shop_order_posts_columns", array($this, "add_order_columns"));
        add_action("manage_shop_order_posts_custom_column", array($this, "display_order_columns"), 10, 2);
    }
    
    public function add_order_meta_box() {
        add_meta_box(
            "wvp-venezuela-details",
            __("Detalles de la Transacción (Venezuela)", "wvp"),
            array($this, "display_order_meta_box"),
            "shop_order",
            "side",
            "high"
        );
    }
    
    public function display_order_meta_box($post) {
        $order = wc_get_order($post->ID);
        if (!$order) {
            return;
        }
        
        $cedula_rif = get_post_meta($post->ID, "_billing_cedula_rif", true);
        $bcv_rate = get_post_meta($post->ID, "_bcv_rate_at_purchase", true);
        $igtf_applied = get_post_meta($post->ID, "_igtf_applied", true);
        
        // Datos de Pago Móvil
        $payment_method = $order->get_payment_method();
        $payment_confirmation = $order->get_meta("_payment_confirmation");
        $payment_from_bank = $order->get_meta("_payment_from_bank_name");
        $payment_from_phone = $order->get_meta("_payment_from_phone");
        $payment_date = $order->get_meta("_payment_date");
        $payment_reference = $order->get_meta("_payment_reference");
        
        // Datos de cuenta seleccionada (si aplica para pago móvil)
        $selected_account_name = $order->get_meta("_selected_account_name");
        $selected_account_ci = $order->get_meta("_selected_account_ci");
        $selected_account_bank = $order->get_meta("_selected_account_bank_name");
        $selected_account_phone = $order->get_meta("_selected_account_phone");
        
        ?>
        <div class="wvp-order-meta">
            <h3 style="margin-top: 0;"><?php _e("Información del Pedido", "wvp"); ?></h3>
            
            <p><strong><?php _e("Cédula/RIF:", "wvp"); ?></strong> 
                <?php echo !empty($cedula_rif) ? esc_html($cedula_rif) : __("No especificado", "wvp"); ?>
            </p>
            <p><strong><?php _e("Tasa BCV:", "wvp"); ?></strong> 
                <?php echo !empty($bcv_rate) ? esc_html(number_format($bcv_rate, 2, ",", ".")) . " Bs./USD" : __("No disponible", "wvp"); ?>
            </p>
            <p><strong><?php _e("IGTF Aplicado:", "wvp"); ?></strong> 
                <?php echo $igtf_applied === "yes" ? __("Sí", "wvp") : __("No", "wvp"); ?>
            </p>
            
            <?php if ($payment_method === 'wvp_pago_movil'): ?>
                <hr style="margin: 15px 0; border: 0; border-top: 1px solid #ddd;">
                <h3 style="margin: 15px 0 10px 0; color: #ccc634;"><?php _e("Pago Móvil - Cuenta a Pagar", "wvp"); ?></h3>
                
                <?php if (!empty($selected_account_name)): ?>
                    <p><strong><?php _e("Titular:", "wvp"); ?></strong> <?php echo esc_html($selected_account_name); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($selected_account_ci)): ?>
                    <p><strong><?php _e("C.I.:", "wvp"); ?></strong> <?php echo esc_html($selected_account_ci); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($selected_account_bank)): ?>
                    <p><strong><?php _e("Banco:", "wvp"); ?></strong> <?php echo esc_html($selected_account_bank); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($selected_account_phone)): ?>
                    <p><strong><?php _e("Teléfono:", "wvp"); ?></strong> <?php echo esc_html($selected_account_phone); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($payment_confirmation) || !empty($payment_reference)): ?>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #ddd;">
                    <h3 style="margin: 15px 0 10px 0; color: #ccc634;"><?php _e("Información del Pago Realizado", "wvp"); ?></h3>
                    
                    <?php if (!empty($payment_confirmation)): ?>
                        <p><strong><?php _e("Confirmación:", "wvp"); ?></strong> 
                            <code style="background: #f0f0f0; padding: 3px 6px; border-radius: 3px;"><?php echo esc_html($payment_confirmation); ?></code>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($payment_reference) && $payment_reference !== $payment_confirmation): ?>
                        <p><strong><?php _e("Referencia:", "wvp"); ?></strong> 
                            <code style="background: #f0f0f0; padding: 3px 6px; border-radius: 3px;"><?php echo esc_html($payment_reference); ?></code>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($payment_from_bank)): ?>
                        <p><strong><?php _e("Banco emisor:", "wvp"); ?></strong> <?php echo esc_html($payment_from_bank); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($payment_from_phone)): ?>
                        <p><strong><?php _e("Teléfono emisor:", "wvp"); ?></strong> <?php echo esc_html($payment_from_phone); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($payment_date)): ?>
                        <p><strong><?php _e("Fecha de pago:", "wvp"); ?></strong> <?php echo esc_html($payment_date); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: #999; font-style: italic;"><?php _e("Esperando confirmación del cliente", "wvp"); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function add_order_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === "order_status") {
                $new_columns["cedula_rif"] = __("Cédula/RIF", "wvp");
                $new_columns["bcv_rate"] = __("Tasa BCV", "wvp");
                $new_columns["igtf_status"] = __("IGTF", "wvp");
            }
        }
        return $new_columns;
    }
    
    public function display_order_columns($column, $post_id) {
        $order = wc_get_order($post_id);
        if (!$order) {
            return;
        }
        
        switch ($column) {
            case "cedula_rif":
                $cedula_rif = get_post_meta($post_id, "_billing_cedula_rif", true);
                echo !empty($cedula_rif) ? esc_html($cedula_rif) : "—";
                break;
            case "bcv_rate":
                $bcv_rate = get_post_meta($post_id, "_bcv_rate_at_purchase", true);
                echo !empty($bcv_rate) ? esc_html(number_format($bcv_rate, 2, ",", ".")) : "—";
                break;
            case "igtf_status":
                $igtf_applied = get_post_meta($post_id, "_igtf_applied", true);
                echo $igtf_applied === "yes" ? "✓" : "—";
                break;
        }
    }
}
