<?php
/**
 * Widget de Tasa de Conversión - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget para mostrar tasa de conversión en tiempo real
 */
class WCVS_Conversion_Rate_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'wcvs_conversion_rate_widget',
            'WCVS - Tasa de Conversión',
            array(
                'description' => 'Muestra la tasa de conversión USD/VES en tiempo real',
                'classname' => 'wcvs-conversion-rate-widget'
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
        $show_last_update = $instance['show_last_update'] ?? true;
        $show_source = $instance['show_source'] ?? true;
        $style = $instance['style'] ?? 'minimalist';
        $auto_refresh = $instance['auto_refresh'] ?? false;

        echo $args['before_widget'];

        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        echo '<div class="wcvs-conversion-rate-widget-content wcvs-style-' . esc_attr($style) . '"';
        if ($auto_refresh) {
            echo ' data-auto-refresh="true" data-refresh-interval="300000"'; // 5 minutos
        }
        echo '>';

        $this->display_conversion_rate($show_last_update, $show_source);

        echo '</div>';
        echo $args['after_widget'];
    }

    /**
     * Formulario de configuración del widget
     *
     * @param array $instance Instancia actual
     */
    public function form($instance) {
        $title = $instance['title'] ?? 'Tasa de Conversión';
        $show_last_update = $instance['show_last_update'] ?? true;
        $show_source = $instance['show_source'] ?? true;
        $style = $instance['style'] ?? 'minimalist';
        $auto_refresh = $instance['auto_refresh'] ?? false;

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo esc_attr($this->get_field_id('show_last_update')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_last_update')); ?>" 
                   <?php checked($show_last_update); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_last_update')); ?>">Mostrar última actualización</label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo esc_attr($this->get_field_id('show_source')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_source')); ?>" 
                   <?php checked($show_source); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_source')); ?>">Mostrar fuente</label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo esc_attr($this->get_field_id('auto_refresh')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('auto_refresh')); ?>" 
                   <?php checked($auto_refresh); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('auto_refresh')); ?>">Actualización automática</label>
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
        $instance['show_last_update'] = isset($new_instance['show_last_update']) ? true : false;
        $instance['show_source'] = isset($new_instance['show_source']) ? true : false;
        $instance['style'] = sanitize_text_field($new_instance['style']);
        $instance['auto_refresh'] = isset($new_instance['auto_refresh']) ? true : false;

        return $instance;
    }

    /**
     * Mostrar tasa de conversión
     *
     * @param bool $show_last_update Mostrar última actualización
     * @param bool $show_source Mostrar fuente
     */
    private function display_conversion_rate($show_last_update, $show_source) {
        $plugin = WCVS_Core::get_instance();
        $bcv_integration = $plugin->get_bcv_integration();
        
        if (!$bcv_integration) {
            echo '<div class="wcvs-rate-unavailable">Plugin no disponible</div>';
            return;
        }

        $rate_data = $bcv_integration->get_current_rate();
        
        if (!$rate_data || !isset($rate_data['rate']) || $rate_data['rate'] <= 0) {
            echo '<div class="wcvs-rate-unavailable">Tasa no disponible</div>';
            return;
        }

        $rate = $rate_data['rate'];
        $source = $rate_data['source'] ?? 'unknown';
        $timestamp = $rate_data['timestamp'] ?? current_time('mysql');

        echo '<div class="wcvs-rate-display">';
        
        // Valor principal
        echo '<div class="wcvs-rate-main">';
        echo '<div class="wcvs-rate-value">' . number_format($rate, 4, ',', '.') . '</div>';
        echo '<div class="wcvs-rate-currency">Bs./USD</div>';
        echo '</div>';

        // Información adicional
        if ($show_source || $show_last_update) {
            echo '<div class="wcvs-rate-info">';
            
            if ($show_source) {
                echo '<div class="wcvs-rate-source">';
                echo '<span class="wcvs-source-label">Fuente:</span> ';
                echo '<span class="wcvs-source-value">' . esc_html($this->get_source_label($source)) . '</span>';
                echo '</div>';
            }
            
            if ($show_last_update) {
                echo '<div class="wcvs-rate-update">';
                echo '<span class="wcvs-update-label">Actualizado:</span> ';
                echo '<span class="wcvs-update-value">' . esc_html($this->format_timestamp($timestamp)) . '</span>';
                echo '</div>';
            }
            
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Obtener etiqueta de fuente
     *
     * @param string $source Fuente de la tasa
     * @return string
     */
    private function get_source_label($source) {
        $labels = array(
            'bcv_plugin' => 'Plugin BCV',
            'bcv_database' => 'Base de Datos BCV',
            'wvp_fallback' => 'WVP Fallback',
            'configured_fallback' => 'Configuración',
            'direct_scraping' => 'Scraping Directo',
            'unknown' => 'Desconocida'
        );

        return $labels[$source] ?? 'Desconocida';
    }

    /**
     * Formatear timestamp
     *
     * @param string $timestamp Timestamp
     * @return string
     */
    private function format_timestamp($timestamp) {
        $time = strtotime($timestamp);
        $now = current_time('timestamp');
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Hace ' . $diff . ' segundos';
        } elseif ($diff < 3600) {
            return 'Hace ' . floor($diff / 60) . ' minutos';
        } elseif ($diff < 86400) {
            return 'Hace ' . floor($diff / 3600) . ' horas';
        } else {
            return date('d/m/Y H:i', $time);
        }
    }
}
