<?php
/**
 * Widget de Convertidor de Moneda
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Currency_Converter_Widget extends WP_Widget {
    
    /**
     * Constructor del widget
     */
    public function __construct() {
        parent::__construct(
            'wvp_currency_converter',
            __('Convertidor de Moneda WVP', 'wvp'),
            array(
                'description' => __('Widget para convertir entre USD y VES', 'wvp'),
                'classname' => 'wvp-currency-converter-widget'
            )
        );
    }
    
    /**
     * Mostrar widget en el frontend
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $from_currency = !empty($instance['from_currency']) ? $instance['from_currency'] : 'USD';
        $to_currency = !empty($instance['to_currency']) ? $instance['to_currency'] : 'VES';
        $default_amount = !empty($instance['default_amount']) ? $instance['default_amount'] : 1;
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        $this->display_converter($from_currency, $to_currency, $default_amount);
        
        echo $args['after_widget'];
    }
    
    /**
     * Formulario de configuración del widget
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Convertidor de Moneda', 'wvp');
        $from_currency = !empty($instance['from_currency']) ? $instance['from_currency'] : 'USD';
        $to_currency = !empty($instance['to_currency']) ? $instance['to_currency'] : 'VES';
        $default_amount = !empty($instance['default_amount']) ? $instance['default_amount'] : 1;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Título:', 'wvp'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('from_currency'); ?>"><?php _e('Moneda origen:', 'wvp'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('from_currency'); ?>" name="<?php echo $this->get_field_name('from_currency'); ?>">
                <option value="USD" <?php selected($from_currency, 'USD'); ?>><?php _e('USD', 'wvp'); ?></option>
                <option value="VES" <?php selected($from_currency, 'VES'); ?>><?php _e('VES', 'wvp'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('to_currency'); ?>"><?php _e('Moneda destino:', 'wvp'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('to_currency'); ?>" name="<?php echo $this->get_field_name('to_currency'); ?>">
                <option value="USD" <?php selected($to_currency, 'USD'); ?>><?php _e('USD', 'wvp'); ?></option>
                <option value="VES" <?php selected($to_currency, 'VES'); ?>><?php _e('VES', 'wvp'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('default_amount'); ?>"><?php _e('Cantidad por defecto:', 'wvp'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('default_amount'); ?>" name="<?php echo $this->get_field_name('default_amount'); ?>" type="number" step="0.01" value="<?php echo esc_attr($default_amount); ?>">
        </p>
        <?php
    }
    
    /**
     * Actualizar configuración del widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['from_currency'] = (!empty($new_instance['from_currency'])) ? strip_tags($new_instance['from_currency']) : 'USD';
        $instance['to_currency'] = (!empty($new_instance['to_currency'])) ? strip_tags($new_instance['to_currency']) : 'VES';
        $instance['default_amount'] = (!empty($new_instance['default_amount'])) ? floatval($new_instance['default_amount']) : 1;
        
        return $instance;
    }
    
    /**
     * Mostrar convertidor
     */
    private function display_converter($from_currency, $to_currency, $default_amount) {
        $rate = WVP_BCV_Integrator::get_rate();
        
        if (!$rate || $rate <= 0) {
            echo '<p>' . __('Tasa de cambio no disponible', 'wvp') . '</p>';
            return;
        }
        
        $converted_amount = $this->convert_amount($default_amount, $from_currency, $to_currency, $rate);
        ?>
        <div class="wvp-currency-converter-widget">
            <div class="wvp-converter-form">
                <div class="wvp-form-group">
                    <label for="wvp-amount-<?php echo $this->id; ?>"><?php _e('Cantidad:', 'wvp'); ?></label>
                    <input type="number" id="wvp-amount-<?php echo $this->id; ?>" class="wvp-form-control" value="<?php echo esc_attr($default_amount); ?>" step="0.01" min="0">
                </div>
                
                <div class="wvp-form-group">
                    <label for="wvp-from-<?php echo $this->id; ?>"><?php _e('De:', 'wvp'); ?></label>
                    <select id="wvp-from-<?php echo $this->id; ?>" class="wvp-form-control">
                        <option value="USD" <?php selected($from_currency, 'USD'); ?>><?php _e('USD', 'wvp'); ?></option>
                        <option value="VES" <?php selected($from_currency, 'VES'); ?>><?php _e('VES', 'wvp'); ?></option>
                    </select>
                </div>
                
                <div class="wvp-form-group">
                    <label for="wvp-to-<?php echo $this->id; ?>"><?php _e('A:', 'wvp'); ?></label>
                    <select id="wvp-to-<?php echo $this->id; ?>" class="wvp-form-control">
                        <option value="USD" <?php selected($to_currency, 'USD'); ?>><?php _e('USD', 'wvp'); ?></option>
                        <option value="VES" <?php selected($to_currency, 'VES'); ?>><?php _e('VES', 'wvp'); ?></option>
                    </select>
                </div>
                
                <div class="wvp-converter-result">
                    <strong><?php _e('Resultado:', 'wvp'); ?></strong>
                    <span id="wvp-result-<?php echo $this->id; ?>"><?php echo $this->format_currency($converted_amount, $to_currency); ?></span>
                </div>
                
                <div class="wvp-converter-rate">
                    <small><?php _e('Tasa BCV:', 'wvp'); ?> <?php echo number_format($rate, 2, ',', '.'); ?> Bs./USD</small>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var widgetId = '<?php echo $this->id; ?>';
            var rate = <?php echo $rate; ?>;
            
            function convert() {
                var amount = parseFloat($('#wvp-amount-' + widgetId).val()) || 0;
                var from = $('#wvp-from-' + widgetId).val();
                var to = $('#wvp-to-' + widgetId).val();
                
                if (amount > 0) {
                    var converted = convertAmount(amount, from, to, rate);
                    $('#wvp-result-' + widgetId).text(formatCurrency(converted, to));
                }
            }
            
            function convertAmount(amount, from, to, rate) {
                if (from === 'USD' && to === 'VES') {
                    return amount * rate;
                } else if (from === 'VES' && to === 'USD') {
                    return amount / rate;
                }
                return amount;
            }
            
            function formatCurrency(amount, currency) {
                if (currency === 'USD') {
                    return '$' + amount.toFixed(2);
                } else if (currency === 'VES') {
                    return 'Bs. ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
                return amount.toFixed(2);
            }
            
            $('#wvp-amount-' + widgetId + ', #wvp-from-' + widgetId + ', #wvp-to-' + widgetId).on('input change', convert);
        });
        </script>
        <?php
    }
    
    /**
     * Convertir cantidad
     */
    private function convert_amount($amount, $from, $to, $rate) {
        if ($from === 'USD' && $to === 'VES') {
            return $amount * $rate;
        } elseif ($from === 'VES' && $to === 'USD') {
            return $amount / $rate;
        }
        return $amount;
    }
    
    /**
     * Formatear moneda
     */
    private function format_currency($amount, $currency) {
        if ($currency === 'USD') {
            return '$' . number_format($amount, 2);
        } elseif ($currency === 'VES') {
            return 'Bs. ' . number_format($amount, 2, ',', '.');
        }
        return number_format($amount, 2);
    }
}
