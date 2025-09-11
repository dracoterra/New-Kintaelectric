<?php
// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Checkout {
    
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
        add_filter("woocommerce_checkout_fields", array($this, "add_cedula_rif_field"));
        add_action("woocommerce_checkout_process", array($this, "validate_cedula_rif_field"));
        add_action("woocommerce_checkout_create_order", array($this, "save_cedula_rif_field_modern"));
    }
    
    public function add_cedula_rif_field($fields) {
        $fields["billing"]["billing_cedula_rif"] = array(
            "label" => __("Cédula o RIF", "wvp"),
            "placeholder" => __("V-12345678 o J-12345678-9", "wvp"),
            "required" => true,
            "class" => array("form-row-wide"),
            "clear" => true,
            "priority" => 25,
            "type" => "text"
        );
        return $fields;
    }
    
    public function validate_cedula_rif_field() {
        $cedula_rif = $_POST["billing_cedula_rif"] ?? "";
        if (empty($cedula_rif)) {
            wc_add_notice(__("El campo Cédula o RIF es obligatorio.", "wvp"), "error");
        }
    }
    
    public function save_cedula_rif_field($order_id) {
        if (!empty($_POST["billing_cedula_rif"])) {
            update_post_meta($order_id, "_billing_cedula_rif", sanitize_text_field($_POST["billing_cedula_rif"]));
        }
    }
    
    /**
     * Guardar campo de cédula/RIF usando método moderno (compatible con WC 8.0+)
     * 
     * @param WC_Order $order Pedido
     */
    public function save_cedula_rif_field_modern($order) {
        // Verificar que el pedido existe
        if (!$order || !is_a($order, 'WC_Order')) {
            return;
        }
        
        // Obtener datos del request actual
        $cedula_rif = '';
        
        // 1. Buscar en $_POST (checkout clásico)
        if (isset($_POST['billing_cedula_rif'])) {
            $cedula_rif = sanitize_text_field($_POST['billing_cedula_rif']);
        }
        
        // 2. Buscar en datos del pedido (WooCommerce Blocks)
        if (empty($cedula_rif)) {
            $cedula_rif = $order->get_meta('billing_cedula_rif');
        }
        
        // 3. Buscar en datos de facturación
        if (empty($cedula_rif)) {
            $cedula_rif = $order->get_meta('_billing_cedula_rif');
        }
        
        // Guardar si existe
        if (!empty($cedula_rif)) {
            $order->update_meta_data('_billing_cedula_rif', $cedula_rif);
            $order->save();
        }
    }
}
