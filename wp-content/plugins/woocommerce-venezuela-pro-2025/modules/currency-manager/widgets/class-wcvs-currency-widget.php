<?php
/**
 * Widget de Moneda - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget para mostrar información de moneda
 */
class WCVS_Currency_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'wcvs_currency_widget',
            'WCVS - Información de Moneda',
            array(
                'description' => 'Muestra información de conversión de moneda USD/VES',
                'classname' => 'wcvs-currency-widget'
            )
        );
    }

    /**
     * Mostrar widget
     *
     * @param array $args Argumentos del widget
     * @param array $instance Instancia del widget
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $show_rate = $instance['show_rate'] ?? true;
        $show_switcher = $instance['show_switcher'] ?? true;
        $style = $instance['style'] ?? 'minimalist';

        echo $args['before_widget'];

        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        echo '<div class="wcvs-currency-widget-content wcvs-style-' . esc_attr($style) . '">';

        // Mostrar tasa de conversión
        if ($show_rate) {
            $this->display_conversion_rate();
        }

        // Mostrar selector de moneda
        if ($show_switcher) {
            $this->display_currency_switcher();
        }

        echo '</div>';
        echo $args['after_widget'];
    }

    /**
     * Formulario de configuración del widget
     *
     * @param array $instance Instancia actual
     */
    public function form($instance) {
        $title = $instance['title'] ?? 'Información de Moneda';
        $show_rate = $instance['show_rate'] ?? true;
        $show_switcher = $instance['show_switcher'] ?? true;
        $style = $instance['style'] ?? 'minimalist';

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo esc_attr($this->get_field_id('show_rate')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_rate')); ?>" 
                   <?php checked($show_rate); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_rate')); ?>">Mostrar tasa de conversión</label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo esc_attr($this->get_field_id('show_switcher')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_switcher')); ?>" 
                   <?php checked($show_switcher); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_switcher')); ?>">Mostrar selector de moneda</label>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>">Estilo:</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('style')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <option value="minimalist" <?php selected($style, 'minimalist'); ?>>Minimalista</option>
                <option value="modern" <?php selected($style, 'modern'); ?>>Moderno</option>
                <option value="elegant" <?php selected($style, 'elegant'); ?>>Elegante</option>
                <option value="compact" <?php selected($style, 'compact'); ?>>Compacto</option>
            </select>
        </p>
        <?php
    }

    /**
     * Actualizar configuración del widget
     *
     * @param array $new_instance Nueva instancia
     * @param array $old_instance Instancia anterior
     * @return array
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['show_rate'] = isset($new_instance['show_rate']) ? true : false;
        $instance['show_switcher'] = isset($new_instance['show_switcher']) ? true : false;
        $instance['style'] = sanitize_text_field($new_instance['style']);

        return $instance;
    }

    /**
     * Mostrar tasa de conversión
     */
    private function display_conversion_rate() {
        $plugin = WCVS_Core::get_instance();
        $currency_manager = $plugin->get_module_manager()->get_module_instance('currency_manager');
        
        if (!$currency_manager) {
            return;
        }

        $rate = $currency_manager->get_conversion_rate();
        if (!$rate) {
            echo '<div class="wcvs-rate-unavailable">Tasa no disponible</div>';
            return;
        }

        echo '<div class="wcvs-conversion-rate">';
        echo '<div class="wcvs-rate-label">Tasa BCV</div>';
        echo '<div class="wcvs-rate-value">' . number_format($rate, 4, ',', '.') . '</div>';
        echo '<div class="wcvs-rate-currency">Bs./USD</div>';
        echo '</div>';
    }

    /**
     * Mostrar selector de moneda
     */
    private function display_currency_switcher() {
        echo '<div class="wcvs-currency-switcher">';
        echo '<button class="wcvs-currency-btn wcvs-btn-usd active" data-currency="USD">USD</button>';
        echo '<button class="wcvs-currency-btn wcvs-btn-ves" data-currency="VES">VES</button>';
        echo '</div>';
    }
}
