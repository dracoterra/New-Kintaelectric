<?php
/**
 * Electro Latest Products Widget
 * 
 * @package KintaElectric
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Electro_Latest_Products_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'electro_latest_products_widget',
            __('Electro Latest Products', 'kintaelectric'),
            array(
                'description' => __('Display latest products with custom styling', 'kintaelectric'),
            )
        );
    }

    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Latest Products';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_rating = !empty($instance['show_rating']) ? (bool) $instance['show_rating'] : true;
        $show_price = !empty($instance['show_price']) ? (bool) $instance['show_price'] : true;

        // Generar el HTML con la estructura exacta del original
        echo '<aside id="' . $this->id . '" class="widget woocommerce widget_products">';
        
        if ($title) {
            echo '<h3 class="widget-title">' . esc_html($title) . '</h3>';
        }

        $this->render_products($number, $show_rating, $show_price);
        
        echo '</aside>';
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Latest Products';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_rating = !empty($instance['show_rating']) ? (bool) $instance['show_rating'] : true;
        $show_price = !empty($instance['show_price']) ? (bool) $instance['show_price'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'kintaelectric'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of products to show:', 'kintaelectric'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_rating); ?> id="<?php echo esc_attr($this->get_field_id('show_rating')); ?>" name="<?php echo esc_attr($this->get_field_name('show_rating')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_rating')); ?>"><?php _e('Show rating', 'kintaelectric'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_price); ?> id="<?php echo esc_attr($this->get_field_id('show_price')); ?>" name="<?php echo esc_attr($this->get_field_name('show_price')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_price')); ?>"><?php _e('Show price', 'kintaelectric'); ?></label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        $instance['show_rating'] = (!empty($new_instance['show_rating'])) ? 1 : 0;
        $instance['show_price'] = (!empty($new_instance['show_price'])) ? 1 : 0;
        return $instance;
    }

    private function render_products($number, $show_rating, $show_price) {
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Obtener productos usando get_posts
        $products = get_posts(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'numberposts' => $number,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        if (empty($products)) {
            return;
        }

        echo '<ul class="product_list_widget">';

        foreach ($products as $product_post) {
            $product = wc_get_product($product_post->ID);
            if (!$product) {
                continue;
            }

            $product_id = $product->get_id();
            $product_title = $product->get_name();
            $product_url = get_permalink($product_id);
            
            // Imagen simple
            $product_image_url = wp_get_attachment_image_url(get_post_thumbnail_id($product_id), 'woocommerce_thumbnail');
            if (!$product_image_url) {
                $product_image_url = wc_placeholder_img_src('woocommerce_thumbnail');
            }

            echo '<li>';
            echo '<a href="' . esc_url($product_url) . '">';
            echo '<img loading="lazy" width="300" height="300" src="' . esc_url($product_image_url) . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="' . esc_attr($product_title) . '" decoding="async">';
            echo '<span class="product-title">' . esc_html($product_title) . '</span>';
            echo '</a>';

            if ($show_rating) {
                $rating = $product->get_average_rating();
                
                if ($rating > 0) {
                    $rating_percentage = ($rating / 5) * 100;
                    echo '<div class="star-rating" role="img" aria-label="Rated ' . esc_attr($rating) . ' out of 5">';
                    echo '<span style="width:' . esc_attr($rating_percentage) . '%">Rated <strong class="rating">' . esc_html($rating) . '</strong> out of 5</span>';
                    echo '</div>';
                } else {
                    // Mostrar estrellas vacías si no hay rating
                    echo '<div class="star-rating" role="img" aria-label="No rating yet">';
                    echo '<span style="width:0%">No rating yet</span>';
                    echo '</div>';
                }
            }

            if ($show_price) {
                $price_html = $product->get_price_html();
                if ($price_html) {
                    echo '<span class="electro-price">' . $price_html . '</span>';
                }
            }

            echo '</li>';
        }

        echo '</ul>';
    }
}
