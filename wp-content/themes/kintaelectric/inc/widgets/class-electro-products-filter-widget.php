<?php
/**
 * Electro Products Filter Widget
 * 
 * @package KintaElectric
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Electro_Products_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'electro_products_filter_widget',
            __('Electro Products Filter', 'kintaelectric'),
            array(
                'description' => __('Display product filters with brands, colors, and price range', 'kintaelectric'),
            )
        );
    }

    public function widget($args, $instance) {
        if (!is_shop() && !is_product_taxonomy()) {
            return;
        }

        $title = !empty($instance['title']) ? $instance['title'] : 'Filters';
        $show_brands = !empty($instance['show_brands']) ? (bool) $instance['show_brands'] : true;
        $show_colors = !empty($instance['show_colors']) ? (bool) $instance['show_colors'] : true;
        $show_price = !empty($instance['show_price']) ? (bool) $instance['show_price'] : true;
        $max_items = !empty($instance['max_items']) ? absint($instance['max_items']) : 5;

        echo '<aside id="' . $this->id . '" class="widget widget_electro_products_filter">';
        
        if ($title) {
            echo '<h3 class="widget-title">' . esc_html($title) . '</h3>';
        }

        // Brands Filter
        if ($show_brands) {
            $this->render_brands_filter();
        }

        // Colors Filter
        if ($show_colors) {
            $this->render_colors_filter();
        }

        // Price Filter
        if ($show_price) {
            $this->render_price_filter();
        }

        // JavaScript para hideMaxListItems
        $this->render_filter_script($max_items);
        
        echo '</aside>';
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Filters';
        $show_brands = !empty($instance['show_brands']) ? (bool) $instance['show_brands'] : true;
        $show_colors = !empty($instance['show_colors']) ? (bool) $instance['show_colors'] : true;
        $show_price = !empty($instance['show_price']) ? (bool) $instance['show_price'] : true;
        $max_items = !empty($instance['max_items']) ? absint($instance['max_items']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'kintaelectric'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_brands); ?> id="<?php echo esc_attr($this->get_field_id('show_brands')); ?>" name="<?php echo esc_attr($this->get_field_name('show_brands')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_brands')); ?>"><?php _e('Show Brands Filter', 'kintaelectric'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_colors); ?> id="<?php echo esc_attr($this->get_field_id('show_colors')); ?>" name="<?php echo esc_attr($this->get_field_name('show_colors')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_colors')); ?>"><?php _e('Show Colors Filter', 'kintaelectric'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_price); ?> id="<?php echo esc_attr($this->get_field_id('show_price')); ?>" name="<?php echo esc_attr($this->get_field_name('show_price')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_price')); ?>"><?php _e('Show Price Filter', 'kintaelectric'); ?></label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('max_items')); ?>"><?php _e('Max items to show:', 'kintaelectric'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('max_items')); ?>" name="<?php echo esc_attr($this->get_field_name('max_items')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($max_items); ?>" size="3">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['show_brands'] = (!empty($new_instance['show_brands'])) ? 1 : 0;
        $instance['show_colors'] = (!empty($new_instance['show_colors'])) ? 1 : 0;
        $instance['show_price'] = (!empty($new_instance['show_price'])) ? 1 : 0;
        $instance['max_items'] = (!empty($new_instance['max_items'])) ? absint($new_instance['max_items']) : 5;
        return $instance;
    }

    private function render_brands_filter() {
        // Obtener marcas (brands) - asumiendo que es un atributo de producto
        $brands = $this->get_filter_terms('pa_brands');
        
        if (empty($brands)) {
            return;
        }

        echo '<aside id="woocommerce_layered_nav-1" class="widget woocommerce widget_layered_nav woocommerce-widget-layered-nav">';
        echo '<h3 class="widget-title">Brands</h3>';
        echo '<ul class="woocommerce-widget-layered-nav-list">';

        foreach ($brands as $brand) {
            $brand_url = add_query_arg('filter_brands', $brand->slug, $this->get_current_page_url());
            $brand_url = remove_query_arg('paged', $brand_url);
            
            echo '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term">';
            echo '<a rel="nofollow" href="' . esc_url($brand_url) . '">' . esc_html($brand->name) . '</a>';
            echo ' <span class="count">(' . $brand->count . ')</span>';
            echo '</li>';
        }

        echo '</ul>';
        echo '</aside>';
    }

    private function render_colors_filter() {
        // Obtener colores - asumiendo que es un atributo de producto
        $colors = $this->get_filter_terms('pa_color');
        
        if (empty($colors)) {
            return;
        }

        echo '<aside id="woocommerce_layered_nav-2" class="widget woocommerce widget_layered_nav woocommerce-widget-layered-nav">';
        echo '<h3 class="widget-title">Color</h3>';
        echo '<ul class="woocommerce-widget-layered-nav-list">';

        foreach ($colors as $color) {
            $color_url = add_query_arg('filter_color', $color->slug, $this->get_current_page_url());
            $color_url = remove_query_arg('paged', $color_url);
            
            echo '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term">';
            echo '<a rel="nofollow" href="' . esc_url($color_url) . '">' . esc_html($color->name) . '</a>';
            echo ' <span class="count">(' . $color->count . ')</span>';
            echo '</li>';
        }

        echo '</ul>';
        echo '</aside>';
    }

    private function render_price_filter() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Obtener precios mínimos y máximos
        $prices = $this->get_filtered_price();
        $min_price = $prices->min_price;
        $max_price = $prices->max_price;

        if ($min_price === $max_price) {
            return;
        }

        $current_min_price = isset($_GET['min_price']) ? wc_clean(wp_unslash($_GET['min_price'])) : $min_price;
        $current_max_price = isset($_GET['max_price']) ? wc_clean(wp_unslash($_GET['max_price'])) : $max_price;

        echo '<aside id="woocommerce_price_filter-1" class="widget woocommerce widget_price_filter">';
        echo '<h3 class="widget-title">Price</h3>';
        
        $form_action = wc_get_page_permalink('shop');
        echo '<form method="get" action="' . esc_url($form_action) . '">';
        echo '<div class="price_slider_wrapper">';
        echo '<div class="price_slider" style="display:none;"></div>';
        echo '<div class="price_slider_amount" data-step="10">';
        echo '<label class="screen-reader-text" for="min_price">Min price</label>';
        echo '<input type="text" id="min_price" name="min_price" value="' . esc_attr($current_min_price) . '" data-min="' . esc_attr($min_price) . '" placeholder="Min price">';
        echo '<label class="screen-reader-text" for="max_price">Max price</label>';
        echo '<input type="text" id="max_price" name="max_price" value="' . esc_attr($current_max_price) . '" data-max="' . esc_attr($max_price) . '" placeholder="Max price">';
        echo '<button type="submit" class="button">Filter</button>';
        echo '<div class="price_label" style="display:none;">Price: <span class="from"></span> &mdash; <span class="to"></span></div>';
        echo '<div class="clear"></div>';
        echo '</div>';
        echo '</div>';
        echo '</form>';
        echo '</aside>';

        // Enqueue scripts necesarios para el price slider
        wp_enqueue_script('wc-price-slider');
    }

    private function get_filter_terms($taxonomy) {
        if (!taxonomy_exists($taxonomy)) {
            return array();
        }

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        return is_wp_error($terms) ? array() : $terms;
    }

    private function get_filtered_price() {
        global $wpdb;

        $args = WC()->query->get_main_query()->query_vars;
        $meta_query = isset($args['meta_query']) ? $args['meta_query'] : array();
        $tax_query = isset($args['tax_query']) ? $args['tax_query'] : array();

        if (!is_post_type_archive('product') && !is_product_taxonomy()) {
            $meta_query[] = WC()->query->get_meta_query();
            $tax_query[] = WC()->query->get_tax_query();
        }

        $meta_query = new WP_Meta_Query($meta_query);
        $tax_query = new WP_Tax_Query($tax_query);

        $meta_query_sql = $meta_query->get_sql('post', $wpdb->posts, 'ID');
        $tax_query_sql = $tax_query->get_sql($wpdb->posts, 'ID');

        $sql = "
            SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, MAX( CEILING( price_meta.meta_value ) ) as max_price
            FROM {$wpdb->posts} as posts
            " . $tax_query_sql['join'] . $meta_query_sql['join'] . "
            LEFT JOIN {$wpdb->postmeta} as price_meta ON posts.ID = price_meta.post_id
            WHERE posts.post_type IN ('" . implode("','", array_map('esc_sql', wc_get_product_types())) . "')
            AND posts.post_status = 'publish'
            AND price_meta.meta_key IN ('" . implode("','", array_map('esc_sql', apply_filters('woocommerce_price_filter_meta_keys', array('_price')))) . "')
            AND price_meta.meta_value != ''
            " . $tax_query_sql['where'] . $meta_query_sql['where'];

        $prices = $wpdb->get_row($sql);

        return (object) array(
            'min_price' => floor($prices->min_price),
            'max_price' => ceil($prices->max_price)
        );
    }

    private function get_current_page_url() {
        if (defined('SHOP_IS_ON_FRONT')) {
            $link = home_url();
        } elseif (is_shop()) {
            $link = get_permalink(wc_get_page_id('shop'));
        } elseif (is_product_category()) {
            $link = get_term_link(get_queried_object());
        } elseif (is_product_tag()) {
            $link = get_term_link(get_queried_object());
        } else {
            $link = get_term_link(get_queried_object());
        }

        return $link;
    }

    private function render_filter_script($max_items) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('.widget_electro_products_filter .widget .widget-title + ul').hideMaxListItems({
                    'max': <?php echo esc_js($max_items); ?>,
                    'speed': 500,
                    'moreText': "+ Show more",
                    'lessText': "- Show less",
                });
                if (jQuery('.widget_electro_products_filter > .widget').length == 0) {
                    jQuery('.widget_electro_products_filter').hide();
                }
            });
        </script>
        <?php
    }
}
