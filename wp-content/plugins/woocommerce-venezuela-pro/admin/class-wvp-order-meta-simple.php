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
        ?>
        <div class="wvp-order-meta">
            <p><strong><?php _e("Cédula/RIF:", "wvp"); ?></strong> 
                <?php echo !empty($cedula_rif) ? esc_html($cedula_rif) : __("No especificado", "wvp"); ?>
            </p>
            <p><strong><?php _e("Tasa BCV:", "wvp"); ?></strong> 
                <?php echo !empty($bcv_rate) ? esc_html(number_format($bcv_rate, 2, ",", ".")) . " Bs./USD" : __("No disponible", "wvp"); ?>
            </p>
            <p><strong><?php _e("IGTF Aplicado:", "wvp"); ?></strong> 
                <?php echo $igtf_applied === "yes" ? __("Sí", "wvp") : __("No", "wvp"); ?>
            </p>
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
