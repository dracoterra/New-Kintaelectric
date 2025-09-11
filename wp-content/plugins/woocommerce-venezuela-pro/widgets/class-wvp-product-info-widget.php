<?php
/**
 * Widget de Información de Producto
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Product_Info_Widget extends WP_Widget {
    
    /**
     * Constructor del widget
     */
    public function __construct() {
        parent::__construct(
            'wvp_product_info',
            __('Información de Producto WVP', 'wvp'),
            array(
                'description' => __('Widget para mostrar información de producto con conversión', 'wvp'),
                'classname' => 'wvp-product-info-widget'
            )
        );
    }
    
    /**
     * Mostrar widget en el frontend
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $product_id = !empty($instance['product_id']) ? $instance['product_id'] : 0;
        $show_price = !empty($instance['show_price']) ? $instance['show_price'] : 'yes';
        $show_conversion = !empty($instance['show_conversion']) ? $instance['show_conversion'] : 'yes';
        $show_igtf = !empty($instance['show_igtf']) ? $instance['show_igtf'] : 'yes';
        
        if (!$product_id) {
            return;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        $this->display_product_info($product, $show_price, $show_conversion, $show_igtf);
        
        echo $args['after_widget'];
    }
    
    /**
     * Formulario de configuración del widget
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Información de Producto', 'wvp');
        $product_id = !empty($instance['product_id']) ? $instance['product_id'] : 0;
        $show_price = !empty($instance['show_price']) ? $instance['show_price'] : 'yes';
        $show_conversion = !empty($instance['show_conversion']) ? $instance['show_conversion'] : 'yes';
        $show_igtf = !empty($instance['show_igtf']) ? $instance['show_igtf'] : 'yes';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Título:', 'wvp'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('product_id'); ?>"><?php _e('Producto:', 'wvp'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('product_id'); ?>" name="<?php echo $this->get_field_name('product_id'); ?>">
                <option value="0"><?php _e('Seleccionar producto...', 'wvp'); ?></option>
                <?php
                $products = wc_get_products(array('limit' => 100));
                foreach ($products as $product) {
                    $selected = selected($product_id, $product->get_id(), false);
                    echo '<option value="' . $product->get_id() . '" ' . $selected . '>' . $product->get_name() . '</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label>
                <input type="checkbox" id="<?php echo $this->get_field_id('show_price'); ?>" name="<?php echo $this->get_field_name('show_price'); ?>" value="yes" <?php checked($show_price, 'yes'); ?>>
                <?php _e('Mostrar precio', 'wvp'); ?>
            </label>
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
        $instance['product_id'] = (!empty($new_instance['product_id'])) ? intval($new_instance['product_id']) : 0;
        $instance['show_price'] = (!empty($new_instance['show_price'])) ? $new_instance['show_price'] : 'no';
        $instance['show_conversion'] = (!empty($new_instance['show_conversion'])) ? $new_instance['show_conversion'] : 'no';
        $instance['show_igtf'] = (!empty($new_instance['show_igtf'])) ? $new_instance['show_igtf'] : 'no';
        
        return $instance;
    }
    
    /**
     * Mostrar información del producto
     */
    private function display_product_info($product, $show_price, $show_conversion, $show_igtf) {
        $rate = WVP_BCV_Integrator::get_rate();
        ?>
        <div class="wvp-product-info-widget">
            <div class="wvp-product-name">
                <h4><?php echo $product->get_name(); ?></h4>
            </div>
            
            <?php if ($show_price === 'yes'): ?>
            <div class="wvp-product-price">
                <strong><?php _e('Precio USD:', 'wvp'); ?></strong>
                <span class="price"><?php echo $product->get_price_html(); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($show_conversion === 'yes' && $rate): ?>
            <div class="wvp-product-conversion">
                <strong><?php _e('Equivale a:', 'wvp'); ?></strong>
                <span class="conversion"><?php echo $this->format_ves_price($product->get_price() * $rate); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($show_igtf === 'yes' && class_exists('WVP_IGTF_Manager')): ?>
            <?php
            $igtf_manager = new WVP_IGTF_Manager();
            if ($igtf_manager->is_enabled()) {
                $igtf_amount = $igtf_manager->calculate_igtf($product->get_price());
                if ($igtf_amount > 0):
            ?>
            <div class="wvp-product-igtf">
                <strong><?php _e('IGTF:', 'wvp'); ?></strong>
                <span class="igtf">$<?php echo number_format($igtf_amount, 2); ?></span>
            </div>
            <div class="wvp-product-total">
                <strong><?php _e('Total con IGTF:', 'wvp'); ?></strong>
                <span class="total">$<?php echo number_format($product->get_price() + $igtf_amount, 2); ?></span>
            </div>
            <?php
                endif;
            }
            ?>
            <?php endif; ?>
            
            <div class="wvp-product-stock">
                <strong><?php _e('Stock:', 'wvp'); ?></strong>
                <span class="stock-status <?php echo $product->get_stock_status(); ?>">
                    <?php echo wc_get_stock_status_name($product->get_stock_status()); ?>
                </span>
            </div>
            
            <?php if ($rate): ?>
            <div class="wvp-product-rate">
                <small><?php _e('Tasa BCV:', 'wvp'); ?> <?php echo number_format($rate, 2, ',', '.'); ?> Bs./USD</small>
            </div>
            <?php endif; ?>
        </div>
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
