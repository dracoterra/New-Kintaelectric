<?php
/**
 * Electro Product Categories Widget
 * 
 * @package KintaElectric
 */

if (!defined('ABSPATH')) {
    exit;
}

class Electro_Product_Categories_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'electro_product_categories',
            __('Electro Product Categories', 'kintaelectric'),
            array(
                'description' => __('Display product categories with custom styling and hierarchy', 'kintaelectric'),
            )
        );
    }

    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Browse Categories';
        $show_count = !empty($instance['show_count']) ? (bool) $instance['show_count'] : true;
        $hierarchical = !empty($instance['hierarchical']) ? (bool) $instance['hierarchical'] : true;
        $show_children_only = !empty($instance['show_children_only']) ? (bool) $instance['show_children_only'] : false;
        $hide_empty = !empty($instance['hide_empty']) ? (bool) $instance['hide_empty'] : false;

        // Generar el HTML con la estructura exacta del original
        echo '<aside id="' . $this->id . '" class="widget woocommerce widget_product_categories electro_widget_product_categories">';
        
        $this->render_categories($show_count, $hierarchical, $show_children_only, $hide_empty);
        
        echo '</aside>';
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Browse Categories';
        $show_count = !empty($instance['show_count']) ? (bool) $instance['show_count'] : true;
        $hierarchical = !empty($instance['hierarchical']) ? (bool) $instance['hierarchical'] : true;
        $show_children_only = !empty($instance['show_children_only']) ? (bool) $instance['show_children_only'] : false;
        $hide_empty = !empty($instance['hide_empty']) ? (bool) $instance['hide_empty'] : false;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'kintaelectric'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_count); ?> id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" />
            <label for="<?php echo $this->get_field_id('show_count'); ?>"><?php _e('Show product counts', 'kintaelectric'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($hierarchical); ?> id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>" />
            <label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e('Show hierarchy', 'kintaelectric'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_children_only); ?> id="<?php echo $this->get_field_id('show_children_only'); ?>" name="<?php echo $this->get_field_name('show_children_only'); ?>" />
            <label for="<?php echo $this->get_field_id('show_children_only'); ?>"><?php _e('Show children only', 'kintaelectric'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($hide_empty); ?> id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" />
            <label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e('Hide empty categories', 'kintaelectric'); ?></label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['show_count'] = !empty($new_instance['show_count']) ? 1 : 0;
        $instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
        $instance['show_children_only'] = !empty($new_instance['show_children_only']) ? 1 : 0;
        $instance['hide_empty'] = !empty($new_instance['hide_empty']) ? 1 : 0;
        return $instance;
    }

    private function render_categories($show_count, $hierarchical, $show_children_only, $hide_empty) {
        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => $hide_empty,
            'hierarchical' => $hierarchical,
            'parent' => $show_children_only ? 0 : '',
            'show_count' => $show_count,
        );

        $categories = get_terms($args);

        if (empty($categories) || is_wp_error($categories)) {
            return;
        }

        echo '<ul class="product-categories ">';
        echo '<li class="product_cat"><span>Browse Categories</span>';
        echo '<ul>';

        foreach ($categories as $category) {
            $this->render_category_item($category, $show_count, $hierarchical, $hide_empty);
        }

        echo '</ul>';
        echo '</li>';
        echo '</ul>';
    }

    private function render_category_item($category, $show_count, $hierarchical, $hide_empty) {
        $has_children = false;
        $children = array();

        if ($hierarchical) {
            $children = get_terms(array(
                'taxonomy' => 'product_cat',
                'parent' => $category->term_id,
                'hide_empty' => $hide_empty,
                'orderby' => 'name',
                'order' => 'ASC',
            ));
            $has_children = !empty($children) && !is_wp_error($children);
        }

        $count_text = $show_count ? ' <span class="count">(' . $category->count . ')</span>' : '';

        echo '<li class="cat-item cat-item-' . $category->term_id . '">';
        echo '<a href="' . get_term_link($category) . '">';
        echo esc_html($category->name);
        echo $count_text;
        echo '</a>';

        if ($has_children) {
            echo '<ul class=\'children\'>';
            foreach ($children as $child) {
                $this->render_category_item($child, $show_count, $hierarchical, $hide_empty);
            }
            echo '</ul>';
        }

        echo '</li>';
    }
}
