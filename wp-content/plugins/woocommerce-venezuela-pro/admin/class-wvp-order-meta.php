<?php
/**
 * Clase para mostrar metadatos venezolanos en pedidos
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Order_Meta {
    
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
        // Añadir meta box a pedidos
        add_action('add_meta_boxes', array($this, 'add_order_meta_boxes'));
        
        // Añadir columnas a la lista de pedidos
        add_filter('manage_shop_order_posts_columns', array($this, 'add_order_columns'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'render_order_columns'), 10, 2);
        
        // Hacer columnas ordenables
        add_filter('manage_edit-shop_order_sortable_columns', array($this, 'make_columns_sortable'));
        
        // Enqueue estilos administrativos
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    /**
     * Añadir meta boxes a pedidos
     */
    public function add_order_meta_boxes() {
        add_meta_box(
            'wvp-venezuela-data',
            __('Datos Venezuela', 'wvp'),
            array($this, 'render_meta_box_content'),
            'shop_order',
            'side',
            'high'
        );
    }
    
    /**
     * Renderizar contenido del meta box
     * 
     * @param WP_Post $post Post del pedido
     */
    public function render_meta_box_content($post) {
        $order = wc_get_order($post->ID);
        
        if (!$order) {
            return;
        }
        
        // Obtener metadatos
        $cedula_rif = $order->get_meta('_billing_cedula_rif');
        $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
        $payment_reference = $order->get_meta('_payment_reference');
        $igtf_amount = $order->get_meta('_igtf_amount');
        $igtf_rate = $order->get_meta('_igtf_rate');
        $payment_type = $order->get_meta('_payment_type');
        $payment_proof_url = $order->get_meta('_payment_proof_url');
        $seniat_control_number = $order->get_meta('_seniat_control_number');
        $payment_verified = $order->get_meta('_payment_verified');
        
        ?>
        <div class="wvp-order-meta-content">
            <style>
                .wvp-order-meta-content { font-size: 13px; }
                .wvp-meta-row { margin-bottom: 8px; padding: 5px 0; border-bottom: 1px solid #eee; }
                .wvp-meta-row:last-child { border-bottom: none; }
                .wvp-meta-label { font-weight: bold; color: #555; display: block; margin-bottom: 2px; }
                .wvp-meta-value { color: #333; }
                .wvp-meta-value.empty { color: #999; font-style: italic; }
                .wvp-proof-link { display: inline-block; padding: 3px 8px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px; font-size: 11px; }
                .wvp-proof-link:hover { background: #005a87; color: white; }
                .wvp-verified-badge { display: inline-block; padding: 2px 6px; background: #46b450; color: white; border-radius: 3px; font-size: 11px; font-weight: bold; }
                .wvp-control-number { font-family: monospace; background: #f1f1f1; padding: 2px 4px; border-radius: 2px; }
            </style>
            
            <!-- Cédula/RIF -->
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('Cédula/RIF:', 'wvp'); ?></span>
                <span class="wvp-meta-value <?php echo empty($cedula_rif) ? 'empty' : ''; ?>">
                    <?php echo $cedula_rif ? esc_html($cedula_rif) : __('No especificado', 'wvp'); ?>
                </span>
            </div>
            
            <!-- Tasa BCV -->
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('Tasa BCV:', 'wvp'); ?></span>
                <span class="wvp-meta-value <?php echo empty($bcv_rate) ? 'empty' : ''; ?>">
                    <?php if ($bcv_rate): ?>
                        <?php echo number_format($bcv_rate, 2, ',', '.'); ?> Bs./USD
                    <?php else: ?>
                        <?php _e('No disponible', 'wvp'); ?>
                    <?php endif; ?>
                </span>
            </div>
            
            <!-- Referencia de Pago -->
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('Referencia de Pago:', 'wvp'); ?></span>
                <span class="wvp-meta-value <?php echo empty($payment_reference) ? 'empty' : ''; ?>">
                    <?php if ($payment_reference): ?>
                        <code><?php echo esc_html($payment_reference); ?></code>
                    <?php else: ?>
                        <?php _e('No especificada', 'wvp'); ?>
                    <?php endif; ?>
                </span>
            </div>
            
            <!-- IGTF -->
            <?php if ($igtf_amount && $igtf_amount > 0): ?>
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('IGTF:', 'wvp'); ?></span>
                <span class="wvp-meta-value">
                    $<?php echo number_format($igtf_amount, 2, ',', '.'); ?>
                    <?php if ($igtf_rate): ?>
                        (<?php echo $igtf_rate; ?>%)
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Tipo de Pago -->
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('Tipo de Pago:', 'wvp'); ?></span>
                <span class="wvp-meta-value <?php echo empty($payment_type) ? 'empty' : ''; ?>">
                    <?php echo $this->get_payment_type_label($payment_type); ?>
                </span>
            </div>
            
            <!-- Número de Control SENIAT -->
            <?php if ($seniat_control_number): ?>
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('N° Control SENIAT:', 'wvp'); ?></span>
                <span class="wvp-meta-value">
                    <span class="wvp-control-number"><?php echo esc_html($seniat_control_number); ?></span>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Estado de Verificación -->
            <?php if ($payment_verified): ?>
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('Pago Verificado:', 'wvp'); ?></span>
                <span class="wvp-meta-value">
                    <span class="wvp-verified-badge"><?php _e('VERIFICADO', 'wvp'); ?></span>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Comprobante de Pago -->
            <?php if ($payment_proof_url): ?>
            <div class="wvp-meta-row">
                <span class="wvp-meta-label"><?php _e('Comprobante:', 'wvp'); ?></span>
                <span class="wvp-meta-value">
                    <a href="<?php echo esc_url($payment_proof_url); ?>" 
                       target="_blank" 
                       class="wvp-proof-link">
                        <?php _e('Ver Comprobante', 'wvp'); ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Botón para generar factura -->
            <div class="wvp-meta-row">
                <button type="button" 
                        id="wvp-generate-invoice" 
                        class="button button-secondary" 
                        data-order-id="<?php echo $order->get_id(); ?>">
                    <?php _e('Generar Factura PDF', 'wvp'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Obtener etiqueta del tipo de pago
     * 
     * @param string $payment_type Tipo de pago
     * @return string Etiqueta legible
     */
    private function get_payment_type_label($payment_type) {
        $labels = array(
            'transferencia_digital' => __('Transferencia Digital', 'wvp'),
            'efectivo_usd' => __('Efectivo (USD)', 'wvp'),
            'efectivo_bolivares' => __('Efectivo (Bolívares)', 'wvp'),
            'other' => __('Otro', 'wvp')
        );
        
        return isset($labels[$payment_type]) ? $labels[$payment_type] : __('No especificado', 'wvp');
    }
    
    /**
     * Añadir columnas a la lista de pedidos
     * 
     * @param array $columns Columnas existentes
     * @return array Columnas modificadas
     */
    public function add_order_columns($columns) {
        // Insertar columnas después de "Cliente"
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'billing_address') {
                $new_columns['cedula_rif'] = __('Cédula/RIF', 'wvp');
                $new_columns['bcv_rate'] = __('Tasa BCV', 'wvp');
                $new_columns['igtf_amount'] = __('IGTF', 'wvp');
                $new_columns['payment_verified'] = __('Verificado', 'wvp');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Renderizar contenido de columnas personalizadas
     * 
     * @param string $column Nombre de la columna
     * @param int $post_id ID del post
     */
    public function render_order_columns($column, $post_id) {
        $order = wc_get_order($post_id);
        
        if (!$order) {
            return;
        }
        
        switch ($column) {
            case 'cedula_rif':
                $cedula_rif = $order->get_meta('_billing_cedula_rif');
                echo $cedula_rif ? esc_html($cedula_rif) : '<em>' . __('N/A', 'wvp') . '</em>';
                break;
                
            case 'bcv_rate':
                $bcv_rate = $order->get_meta('_bcv_rate_at_purchase');
                if ($bcv_rate) {
                    echo number_format($bcv_rate, 2, ',', '.') . ' Bs.';
                } else {
                    echo '<em>' . __('N/A', 'wvp') . '</em>';
                }
                break;
                
            case 'igtf_amount':
                $igtf_amount = $order->get_meta('_igtf_amount');
                if ($igtf_amount && $igtf_amount > 0) {
                    echo '$' . number_format($igtf_amount, 2, ',', '.');
                } else {
                    echo '<em>' . __('N/A', 'wvp') . '</em>';
                }
                break;
                
            case 'payment_verified':
                $verified = $order->get_meta('_payment_verified');
                if ($verified) {
                    echo '<span style="color: #46b450; font-weight: bold;">✓</span>';
                } else {
                    echo '<span style="color: #999;">-</span>';
                }
                break;
        }
    }
    
    /**
     * Hacer columnas ordenables
     * 
     * @param array $columns Columnas ordenables
     * @return array Columnas modificadas
     */
    public function make_columns_sortable($columns) {
        $columns['cedula_rif'] = 'cedula_rif';
        $columns['bcv_rate'] = 'bcv_rate';
        $columns['igtf_amount'] = 'igtf_amount';
        $columns['payment_verified'] = 'payment_verified';
        
        return $columns;
    }
    
    /**
     * Enqueue estilos administrativos
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'edit.php' || get_post_type() !== 'shop_order') {
            return;
        }
        
        wp_enqueue_style(
            'wvp-order-meta-admin',
            WVP_PLUGIN_URL . 'assets/css/admin-orders.css',
            array(),
            WVP_VERSION
        );
    }
}
