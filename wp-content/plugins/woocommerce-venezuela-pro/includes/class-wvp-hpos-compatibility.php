<?php
/**
 * Clase para compatibilidad con HPOS (High-Performance Order Storage)
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_HPOS_Compatibility {
    
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
        // Registrar campos REST para WooCommerce Blocks
        add_action('rest_api_init', array($this, 'register_rest_fields'));
        
        // Añadir columnas HPOS a la lista de pedidos
        add_filter('woocommerce_shop_order_list_table_columns', array($this, 'add_hpos_columns'));
        add_action('woocommerce_shop_order_list_table_custom_column', array($this, 'render_hpos_columns'), 10, 2);
        
        // Hacer columnas ordenables en HPOS
        add_filter('woocommerce_shop_order_list_table_sortable_columns', array($this, 'make_hpos_columns_sortable'));
    }
    
    /**
     * Registrar campos REST para WooCommerce Blocks
     */
    public function register_rest_fields() {
        // Registrar campo de cédula/RIF
        register_rest_field('shop_order', 'billing_cedula_rif', array(
            'get_callback' => array($this, 'get_cedula_rif_rest_field'),
            'update_callback' => array($this, 'update_cedula_rif_rest_field'),
            'schema' => array(
                'description' => __('Cédula o RIF del cliente', 'wvp'),
                'type' => 'string',
                'context' => array('view', 'edit')
            )
        ));
        
        // Registrar campo de tasa BCV
        register_rest_field('shop_order', 'bcv_rate_at_purchase', array(
            'get_callback' => array($this, 'get_bcv_rate_rest_field'),
            'update_callback' => array($this, 'update_bcv_rate_rest_field'),
            'schema' => array(
                'description' => __('Tasa BCV al momento de la compra', 'wvp'),
                'type' => 'number',
                'context' => array('view', 'edit')
            )
        ));
        
        // Registrar campo de IGTF
        register_rest_field('shop_order', 'igtf_amount', array(
            'get_callback' => array($this, 'get_igtf_amount_rest_field'),
            'update_callback' => array($this, 'update_igtf_amount_rest_field'),
            'schema' => array(
                'description' => __('Monto de IGTF aplicado', 'wvp'),
                'type' => 'number',
                'context' => array('view', 'edit')
            )
        ));
    }
    
    /**
     * Obtener campo REST de cédula/RIF
     */
    public function get_cedula_rif_rest_field($object) {
        $order = wc_get_order($object['id']);
        return $order ? $order->get_meta('_billing_cedula_rif') : '';
    }
    
    /**
     * Actualizar campo REST de cédula/RIF
     */
    public function update_cedula_rif_rest_field($value, $object) {
        $order = wc_get_order($object->ID);
        if ($order) {
            $order->update_meta_data('_billing_cedula_rif', sanitize_text_field($value));
            $order->save();
        }
        return true;
    }
    
    /**
     * Obtener campo REST de tasa BCV
     */
    public function get_bcv_rate_rest_field($object) {
        $order = wc_get_order($object['id']);
        return $order ? $order->get_meta('_bcv_rate_at_purchase') : '';
    }
    
    /**
     * Actualizar campo REST de tasa BCV
     */
    public function update_bcv_rate_rest_field($value, $object) {
        $order = wc_get_order($object->ID);
        if ($order) {
            $order->update_meta_data('_bcv_rate_at_purchase', floatval($value));
            $order->save();
        }
        return true;
    }
    
    /**
     * Obtener campo REST de IGTF
     */
    public function get_igtf_amount_rest_field($object) {
        $order = wc_get_order($object['id']);
        return $order ? $order->get_meta('_igtf_amount') : '';
    }
    
    /**
     * Actualizar campo REST de IGTF
     */
    public function update_igtf_amount_rest_field($value, $object) {
        $order = wc_get_order($object->ID);
        if ($order) {
            $order->update_meta_data('_igtf_amount', floatval($value));
            $order->save();
        }
        return true;
    }
    
    /**
     * Añadir columnas HPOS a la lista de pedidos
     * 
     * @param array $columns Columnas existentes
     * @return array Columnas modificadas
     */
    public function add_hpos_columns($columns) {
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
     * Renderizar contenido de columnas HPOS personalizadas
     * 
     * @param string $column Nombre de la columna
     * @param WC_Order $order Pedido
     */
    public function render_hpos_columns($column, $order) {
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
     * Hacer columnas HPOS ordenables
     * 
     * @param array $columns Columnas ordenables
     * @return array Columnas modificadas
     */
    public function make_hpos_columns_sortable($columns) {
        $columns['cedula_rif'] = 'cedula_rif';
        $columns['bcv_rate'] = 'bcv_rate';
        $columns['igtf_amount'] = 'igtf_amount';
        $columns['payment_verified'] = 'payment_verified';
        
        return $columns;
    }
    
    /**
     * Verificar si HPOS está activo
     * 
     * @return bool True si HPOS está activo
     */
    public static function is_hpos_enabled() {
        return class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && 
               \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }
    
    /**
     * Obtener ID del pedido de forma compatible con HPOS
     * 
     * @param mixed $order Pedido o ID del pedido
     * @return int ID del pedido
     */
    public static function get_order_id($order) {
        if (is_numeric($order)) {
            return intval($order);
        }
        
        if (is_object($order) && method_exists($order, 'get_id')) {
            return $order->get_id();
        }
        
        return 0;
    }
    
    /**
     * Obtener pedido de forma compatible con HPOS
     * 
     * @param mixed $order Pedido o ID del pedido
     * @return WC_Order|null Pedido o null si no se encuentra
     */
    public static function get_order($order) {
        if (is_object($order) && $order instanceof WC_Order) {
            return $order;
        }
        
        if (is_numeric($order)) {
            return wc_get_order($order);
        }
        
        return null;
    }
}
