<?php
/**
 * Widget de Estado de Pedido
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Order_Status_Widget extends WP_Widget {
    
    /**
     * Constructor del widget
     */
    public function __construct() {
        parent::__construct(
            'wvp_order_status',
            __('Estado de Pedido WVP', 'wvp'),
            array(
                'description' => __('Widget para mostrar estado de pedido con información venezolana', 'wvp'),
                'classname' => 'wvp-order-status-widget'
            )
        );
    }
    
    /**
     * Mostrar widget en el frontend
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $order_id = !empty($instance['order_id']) ? $instance['order_id'] : 0;
        $show_conversion = !empty($instance['show_conversion']) ? $instance['show_conversion'] : 'yes';
        $show_igtf = !empty($instance['show_igtf']) ? $instance['show_igtf'] : 'yes';
        
        if (!$order_id) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        $this->display_order_status($order, $show_conversion, $show_igtf);
        
        echo $args['after_widget'];
    }
    
    /**
     * Formulario de configuración del widget
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Estado de Pedido', 'wvp');
        $order_id = !empty($instance['order_id']) ? $instance['order_id'] : 0;
        $show_conversion = !empty($instance['show_conversion']) ? $instance['show_conversion'] : 'yes';
        $show_igtf = !empty($instance['show_igtf']) ? $instance['show_igtf'] : 'yes';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Título:', 'wvp'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('order_id'); ?>"><?php _e('ID del Pedido:', 'wvp'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('order_id'); ?>" name="<?php echo $this->get_field_name('order_id'); ?>" type="number" value="<?php echo esc_attr($order_id); ?>">
            <small><?php _e('Ingrese el ID del pedido a mostrar', 'wvp'); ?></small>
        </p>
        <p>
            <label>
                <input type="checkbox" id="<?php echo $this->get_field_id('show_conversion'); ?>" name="<?php echo $this->get_field_name('show_conversion'); ?>" value="yes" <?php checked($show_conversion, 'yes'); ?>>
                <?php _e('Mostrar conversión', 'wvp'); ?>
            </label>
        </p>
        <p>
            <label>
                <input type="checkbox" id="<?php echo $this->get_field_id('show_igtf'); ?>" name="<?php echo $this->get_field_name('show_igtf'); ?>" value="yes" <?php checked($show_igtf, 'yes'); ?>>
                <?php _e('Mostrar IGTF', 'wvp'); ?>
            </label>
        </p>
        <?php
    }
    
    /**
     * Actualizar configuración del widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['order_id'] = (!empty($new_instance['order_id'])) ? intval($new_instance['order_id']) : 0;
        $instance['show_conversion'] = (!empty($new_instance['show_conversion'])) ? $new_instance['show_conversion'] : 'no';
        $instance['show_igtf'] = (!empty($new_instance['show_igtf'])) ? $new_instance['show_igtf'] : 'no';
        
        return $instance;
    }
    
    /**
     * Mostrar estado del pedido
     */
    private function display_order_status($order, $show_conversion, $show_igtf) {
        $rate = $order->get_meta('_bcv_rate_at_purchase');
        ?>
        <div class="wvp-order-status-widget">
            <div class="wvp-order-header">
                <h4><?php printf(__('Pedido #%s', 'wvp'), $order->get_order_number()); ?></h4>
                <span class="order-date"><?php echo $order->get_date_created()->date_i18n(get_option('date_format')); ?></span>
            </div>
            
            <div class="wvp-order-status">
                <strong><?php _e('Estado:', 'wvp'); ?></strong>
                <span class="status status-<?php echo $order->get_status(); ?>">
                    <?php echo wc_get_order_status_name($order->get_status()); ?>
                </span>
            </div>
            
            <div class="wvp-order-total">
                <strong><?php _e('Total USD:', 'wvp'); ?></strong>
                <span class="total">$<?php echo number_format($order->get_total(), 2); ?></span>
            </div>
            
            <?php if ($show_conversion === 'yes' && $rate): ?>
            <div class="wvp-order-conversion">
                <strong><?php _e('Equivale a:', 'wvp'); ?></strong>
                <span class="conversion"><?php echo $this->format_ves_price($order->get_total() * $rate); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($show_igtf === 'yes'): ?>
            <?php
            $igtf_amount = $order->get_meta('_igtf_amount');
            if ($igtf_amount > 0):
            ?>
            <div class="wvp-order-igtf">
                <strong><?php _e('IGTF:', 'wvp'); ?></strong>
                <span class="igtf">$<?php echo number_format($igtf_amount, 2); ?></span>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <div class="wvp-order-payment">
                <strong><?php _e('Método de pago:', 'wvp'); ?></strong>
                <span class="payment-method"><?php echo $order->get_payment_method_title(); ?></span>
            </div>
            
            <div class="wvp-order-shipping">
                <strong><?php _e('Envío:', 'wvp'); ?></strong>
                <span class="shipping-method"><?php echo $order->get_shipping_method() ?: __('No especificado', 'wvp'); ?></span>
            </div>
            
            <?php if ($rate): ?>
            <div class="wvp-order-rate">
                <small><?php _e('Tasa BCV:', 'wvp'); ?> <?php echo number_format($rate, 2, ',', '.'); ?> Bs./USD</small>
            </div>
            <?php endif; ?>
            
            <div class="wvp-order-actions">
                <a href="<?php echo $order->get_view_order_url(); ?>" class="wvp-btn wvp-btn-primary wvp-btn-sm">
                    <?php _e('Ver Pedido', 'wvp'); ?>
                </a>
            </div>
        </div>
        
        <style>
        .wvp-order-status-widget {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: #fff;
        }
        
        .wvp-order-header h4 {
            margin: 0 0 5px 0;
            color: #0073aa;
        }
        
        .wvp-order-header .order-date {
            font-size: 0.9em;
            color: #666;
        }
        
        .wvp-order-status,
        .wvp-order-total,
        .wvp-order-conversion,
        .wvp-order-igtf,
        .wvp-order-payment,
        .wvp-order-shipping {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-processing {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .wvp-order-rate {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #666;
        }
        
        .wvp-order-actions {
            margin-top: 15px;
            text-align: center;
        }
        
        .wvp-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }
        
        .wvp-btn:hover {
            background-color: #005a87;
            color: #fff;
        }
        
        .wvp-btn-sm {
            padding: 6px 12px;
            font-size: 0.8em;
        }
        </style>
        <?php
    }
    
    /**
     * Formatear precio en VES
     */
    private function format_ves_price($ves_price) {
        if (!$ves_price) {
            return 'Bs. 0,00';
        }
        
        $formatted = number_format($ves_price, 2, ',', '.');
        return 'Bs. ' . $formatted;
    }
}
