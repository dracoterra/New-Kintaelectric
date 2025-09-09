<?php
/**
 * Electro Products Filter Widget
 * 
 * Widget para mostrar filtros de atributos de productos de WooCommerce
 * con funcionalidad de "Show more/Show less" y conteos dinámicos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Electro_Products_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'electro_products_filter',
            __('Filtros de Productos Electro', 'kintaelectric'),
            array(
                'description' => __('Muestra filtros de atributos de productos con conteos dinámicos y funcionalidad de ver más/menos.', 'kintaelectric'),
                'classname' => 'widget_electro_products_filter',
            )
        );
    }

    public function widget($args, $instance) {
        if (!is_shop() && !is_product_taxonomy()) {
            return;
        }

        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        $title = !empty($instance['title']) ? $instance['title'] : 'Filtros';
        $selected_attributes = !empty($instance['selected_attributes']) ? $instance['selected_attributes'] : array();
        $max_items = !empty($instance['max_items']) ? absint($instance['max_items']) : 5;

        echo '<aside id="' . $this->id . '" class="widget widget_electro_products_filter">';
        
        if ($title) {
            echo '<h3 class="widget-title">' . esc_html($title) . '</h3>';
        }
        
        // Mostrar filtros activos
        $active_filters = $this->get_active_filters();
        if (!empty($active_filters)) {
            echo '<div class="active-filters">';
            echo '<h4>Filtros Activos:</h4>';
            echo '<ul class="active-filters-list">';
            foreach ($active_filters as $filter) {
                echo '<li>' . esc_html($filter['label']) . ': ' . esc_html($filter['values']) . ' <a href="' . esc_url($filter['clear_url']) . '">×</a></li>';
            }
            echo '</ul>';
            echo '<a href="' . esc_url(remove_query_arg(array_keys($_GET))) . '" class="clear-all-filters">Limpiar todos los filtros</a>';
            echo '</div>';
        }

        // Render selected attribute filters
        if (!empty($selected_attributes)) {
            foreach ($selected_attributes as $attribute_taxonomy) {
                $this->render_attribute_filter($attribute_taxonomy, $max_items);
            }
        }

        // JavaScript para hideMaxListItems
        $this->render_filter_script($max_items);
        
        echo '</aside>';
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Filtros';
        $selected_attributes = !empty($instance['selected_attributes']) ? $instance['selected_attributes'] : array();
        $max_items = !empty($instance['max_items']) ? absint($instance['max_items']) : 5;
        
        // Obtener todos los atributos de producto de WooCommerce
        if (!function_exists('wc_get_attribute_taxonomies')) {
            echo '<p>' . __('WooCommerce no está activo o no se ha cargado correctamente.', 'kintaelectric') . '</p>';
            return;
        }
        
        $product_attributes = wc_get_attribute_taxonomies();
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Título:', 'kintaelectric'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('selected_attributes')); ?>"><?php _e('Seleccionar Atributos a Mostrar:', 'kintaelectric'); ?></label>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                <?php foreach ($product_attributes as $attribute): 
                    $taxonomy = 'pa_' . $attribute->attribute_name;
                    $terms = get_terms(array(
                        'taxonomy' => $taxonomy,
                        'hide_empty' => true,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    
                    if (!empty($terms) && !is_wp_error($terms)): ?>
                        <p>
                            <input class="checkbox" type="checkbox" 
                                   id="<?php echo esc_attr($this->get_field_id('selected_attributes_' . $attribute->attribute_id)); ?>" 
                                   name="<?php echo esc_attr($this->get_field_name('selected_attributes')); ?>[]" 
                                   value="<?php echo esc_attr($taxonomy); ?>"
                                   <?php checked(in_array($taxonomy, $selected_attributes)); ?> />
                            <label for="<?php echo esc_attr($this->get_field_id('selected_attributes_' . $attribute->attribute_id)); ?>">
                                <?php echo esc_html($attribute->attribute_label); ?> (<?php echo count($terms); ?>)
                            </label>
                        </p>
                    <?php endif;
                endforeach; ?>
            </div>
            <small><?php _e('Selecciona qué atributos de producto mostrar como filtros. Solo se muestran atributos con productos.', 'kintaelectric'); ?></small>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('max_items')); ?>"><?php _e('Máximo de elementos por filtro:', 'kintaelectric'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('max_items')); ?>" name="<?php echo esc_attr($this->get_field_name('max_items')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($max_items); ?>" size="3">
            <small><?php _e('Número máximo de elementos a mostrar antes de que aparezca el botón "Ver más".', 'kintaelectric'); ?></small>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['selected_attributes'] = (!empty($new_instance['selected_attributes'])) ? array_map('sanitize_text_field', $new_instance['selected_attributes']) : array();
        $instance['max_items'] = (!empty($new_instance['max_items'])) ? absint($new_instance['max_items']) : 5;
        return $instance;
    }

    private function render_attribute_filter($attribute_taxonomy, $max_items) {
        // Obtener términos del atributo con conteos dinámicos
        $terms = $this->get_filtered_attribute_terms($attribute_taxonomy);
        
        if (empty($terms)) {
            return;
        }

        // Obtener el nombre del atributo
        $attribute_name = str_replace('pa_', '', $attribute_taxonomy);
        
        // Obtener el label del atributo
        $attribute_label = ucfirst($attribute_name);
        if (function_exists('wc_get_attribute_taxonomies')) {
            $attributes = wc_get_attribute_taxonomies();
            foreach ($attributes as $attr) {
                if ($attr->attribute_name === $attribute_name) {
                    $attribute_label = $attr->attribute_label;
                    break;
                }
            }
        }

        echo '<aside class="widget woocommerce widget_layered_nav woocommerce-widget-layered-nav">';
        echo '<h3 class="widget-title">' . esc_html($attribute_label) . '</h3>';
        
        // Crear formulario para filtros con checkboxes
        $filter_key = 'filter_' . $attribute_name;
        $current_filters = isset($_GET[$filter_key]) ? (array) $_GET[$filter_key] : array();
        
        echo '<form method="get" class="woocommerce-widget-layered-nav-list">';
        
        // Mantener otros parámetros de filtro
        foreach ($_GET as $key => $value) {
            if ($key !== $filter_key && $key !== 'paged') {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($val) . '">';
                    }
                } else {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }
        }
        
        echo '<ul class="woocommerce-widget-layered-nav-list">';

        $count = 0;
        $total_terms = count($terms);
        
        foreach ($terms as $term) {
            $is_checked = in_array($term->slug, $current_filters);
            $style = ($count >= $max_items) ? ' style="display: none;"' : '';
            $class = ($count >= $max_items) ? ' maxlist-hidden' : '';
            
            echo '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term' . $class . '"' . $style . '>';
            echo '<label class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term">';
            echo '<input type="checkbox" name="' . esc_attr($filter_key) . '[]" value="' . esc_attr($term->slug) . '"' . ($is_checked ? ' checked' : '') . ' class="woocommerce-widget-layered-nav-list__item-checkbox">';
            echo '<span class="woocommerce-widget-layered-nav-list__item-text">' . esc_html($term->name) . '</span>';
            echo ' <span class="count">(' . $term->count . ')</span>';
            echo '</label>';
            echo '</li>';
            
            $count++;
        }
        
        // Mostrar botón "Show more" si hay más elementos que el máximo
        if ($total_terms > $max_items) {
            echo '<li class="maxlist-more">';
            echo '<a href="#" class="show-more-link">+ Ver más</a>';
            echo '</li>';
        }

        echo '</ul>';
        
        // Botones de acción
        echo '<div class="filter-actions">';
        echo '<button type="submit" class="woocommerce-widget-layered-nav-list__submit">Aplicar Filtros</button>';
        
        // Botón para limpiar filtros de este atributo
        if (!empty($current_filters)) {
            $clear_url = remove_query_arg($filter_key);
            echo '<a href="' . esc_url($clear_url) . '" class="clear-filter-link">Limpiar</a>';
        }
        echo '</div>';
        
        echo '</form>';
        echo '</aside>';
    }

    private function get_filtered_attribute_terms($taxonomy) {
        if (!taxonomy_exists($taxonomy)) {
            return array();
        }

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        // Filtrar términos que no tienen productos visibles considerando filtros actuales
        $filtered_terms = array();
        foreach ($terms as $term) {
            $count = $this->get_attribute_term_product_count($taxonomy, $term->term_id);
            if ($count > 0) {
                $term->count = $count;
                $filtered_terms[] = $term;
            }
        }

        return $filtered_terms;
    }

    private function get_attribute_term_product_count($taxonomy, $term_id) {
        // Obtener el conteo de productos para el término considerando filtros actuales
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id,
                ),
            ),
        );

        // Aplicar filtros de categoría si estamos en una categoría específica
        if (is_product_category()) {
            $current_category = get_queried_object();
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $current_category->term_id,
            );
        }

        // Aplicar otros filtros de atributos si están activos
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'filter_') === 0 && $key !== 'filter_' . str_replace('pa_', '', $taxonomy)) {
                $attribute = str_replace('filter_', '', $key);
                $filter_taxonomy = 'pa_' . $attribute;
                
                if (taxonomy_exists($filter_taxonomy)) {
                    $args['tax_query'][] = array(
                        'taxonomy' => $filter_taxonomy,
                        'field' => 'slug',
                        'terms' => sanitize_text_field($value),
                    );
                }
            }
        }

        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }

        $query = new WP_Query($args);
        return $query->found_posts;
    }

    private function get_active_filters() {
        $active_filters = array();
        
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'filter_') === 0) {
                $attribute_name = str_replace('filter_', '', $key);
                $taxonomy = 'pa_' . $attribute_name;
                
                if (taxonomy_exists($taxonomy) && !empty($value)) {
                    $terms = is_array($value) ? $value : array($value);
                    $term_names = array();
                    
                    foreach ($terms as $term_slug) {
                        $term = get_term_by('slug', $term_slug, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $term_names[] = $term->name;
                        }
                    }
                    
                    if (!empty($term_names)) {
                        // Obtener el label del atributo
                        $attribute_label = ucfirst($attribute_name);
                        if (function_exists('wc_get_attribute_taxonomies')) {
                            $attributes = wc_get_attribute_taxonomies();
                            foreach ($attributes as $attr) {
                                if ($attr->attribute_name === $attribute_name) {
                                    $attribute_label = $attr->attribute_label;
                                    break;
                                }
                            }
                        }
                        
                        $active_filters[] = array(
                            'label' => $attribute_label,
                            'values' => implode(', ', $term_names),
                            'clear_url' => remove_query_arg($key)
                        );
                    }
                }
            }
        }
        
        return $active_filters;
    }

    private function get_current_page_url() {
        if (is_shop()) {
            $shop_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
            return $shop_page_id ? get_permalink($shop_page_id) : home_url('/shop/');
        } elseif (is_product_category() || is_product_tag()) {
            $term_link = get_term_link(get_queried_object());
            return is_wp_error($term_link) ? home_url() : $term_link;
        } else {
            return isset($_SERVER['REQUEST_URI']) ? home_url($_SERVER['REQUEST_URI']) : home_url();
        }
    }

    private function render_filter_script($max_items) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Script de filtros cargado');
            
            // Función para manejar show more/less
            $(document).on('click', '.widget_electro_products_filter .show-more-link', function(e) {
                e.preventDefault();
                console.log('Click en Ver más/Ver menos');
                
                var $this = $(this);
                var $list = $this.closest('ul');
                
                // Buscar elementos que pueden estar ocultos (tanto con clase maxlist-hidden como show)
                var $hiddenItems = $list.find('.maxlist-hidden, .show');
                
                console.log('Elementos ocultos encontrados:', $hiddenItems.length);
                console.log('Elementos ocultos:', $hiddenItems);
                
                // Verificar si los elementos están visibles o no
                var isExpanded = $this.text().includes('Ver menos');
                
                if (!isExpanded) {
                    // Mostrar elementos ocultos
                    console.log('Mostrando elementos ocultos');
                    $hiddenItems.removeClass('maxlist-hidden').addClass('show').slideDown(500);
                    $this.text('- Ver menos');
                } else {
                    // Ocultar elementos adicionales
                    console.log('Ocultando elementos adicionales');
                    $hiddenItems.slideUp(500, function() {
                        $(this).removeClass('show').addClass('maxlist-hidden');
                    });
                    $this.text('+ Ver más');
                }
            });
            
            // Auto-envío del formulario cuando se cambia un checkbox
            $('.widget_electro_products_filter').on('change', 'input[type="checkbox"]', function() {
                var $form = $(this).closest('form');
                $form.submit();
            });
            
            // Ocultar widget si no hay filtros
            if ($('.widget_electro_products_filter > .widget').length == 0) {
                $('.widget_electro_products_filter').hide();
            }
        });
        </script>
        <?php
    }
}