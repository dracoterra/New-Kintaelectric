<?php
/**
 * KintaElectric Canvas Menu Widget
 * Optimized and refactored version
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class KintaElectric_Canvas_Menu_Widget extends WP_Widget {

    /**
     * Widget cache key prefix
     */
    const CACHE_PREFIX = 'kintaelectric_canvas_menu_';

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            'kintaelectric_canvas_menu_widget',
            esc_html__( 'KintaElectric Canvas Menu', 'kintaelectric' ),
            array( 
                'description' => esc_html__( 'Displays the off-canvas navigation menu with WooCommerce categories.', 'kintaelectric' ),
                'classname'   => 'kintaelectric-canvas-menu-widget'
            )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        // Early validation
        if ( ! $this->is_valid_environment() ) {
            return;
        }

        $instance = wp_parse_args( (array) $instance, $this->get_default_instance() );
        
        // Check cache first
        $cache_key = $this->get_cache_key( $instance );
        $cached_output = get_transient( $cache_key );
        
        if ( false !== $cached_output ) {
            echo $args['before_widget'];
            echo $cached_output;
            echo $args['after_widget'];
            return;
        }

        // Start output buffering for caching
        ob_start();
        
        echo $args['before_widget'];
        
        try {
            $categories = $this->get_categories( $instance );
            
            if ( empty( $categories ) ) {
                $this->render_no_categories_message();
            } else {
                $this->render_menu( $categories, $instance );
            }
        } catch ( Exception $e ) {
            error_log( 'Canvas Menu Widget Error: ' . $e->getMessage() );
            $this->render_error_message();
        }
        
        echo $args['after_widget'];
        
        // Cache the output
        $output = ob_get_clean();
        set_transient( $cache_key, $output, HOUR_IN_SECONDS );
        echo $output;
    }

    /**
     * Validate environment requirements
     *
     * @return bool
     */
    private function is_valid_environment() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<p class="widget-error">' . esc_html__( 'WooCommerce is not active.', 'kintaelectric' ) . '</p>';
            }
            return false;
        }

        if ( ! taxonomy_exists( 'product_cat' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<p class="widget-error">' . esc_html__( 'Product categories taxonomy not found.', 'kintaelectric' ) . '</p>';
            }
            return false;
        }

        return true;
    }

    /**
     * Get cache key for widget instance
     *
     * @param array $instance Widget instance.
     * @return string
     */
    private function get_cache_key( $instance ) {
        return self::CACHE_PREFIX . md5( serialize( $instance ) );
    }

    /**
     * Get WooCommerce categories with optimized query
     *
     * @param array $instance Widget instance.
     * @return array
     */
    private function get_categories( $instance ) {
        $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => $instance['orderby'],
            'order'        => $instance['order'],
            'hide_empty'   => $instance['hide_empty'],
            'number'       => $instance['number'],
            'parent'       => 0,
            'hierarchical' => false,
            'meta_query'   => array(
                'relation' => 'OR',
                array(
                    'key'     => 'thumbnail_id',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'thumbnail_id',
                    'compare' => 'NOT EXISTS'
                )
            )
        );

        $categories = get_terms( $args );
        
        if ( is_wp_error( $categories ) ) {
            error_log( 'Canvas Menu Widget - get_terms error: ' . $categories->get_error_message() );
            return array();
        }

        return $categories;
    }

    /**
     * Render no categories message
     */
    private function render_no_categories_message() {
        if ( current_user_can( 'manage_options' ) ) {
            echo '<p class="widget-info">' . esc_html__( 'No categories found. Add some product categories to see them here.', 'kintaelectric' ) . '</p>';
        }
    }

    /**
     * Render error message
     */
    private function render_error_message() {
        if ( current_user_can( 'manage_options' ) ) {
            echo '<p class="widget-error">' . esc_html__( 'An error occurred while loading the menu.', 'kintaelectric' ) . '</p>';
        }
    }

    /**
     * Render the main menu
     *
     * @param array $categories WooCommerce categories.
     * @param array $instance Widget instance.
     */
    private function render_menu( $categories, $instance ) {
        ?>
        <ul id="menu-all-departments-menu" class="nav nav-inline yamm">
            <?php
            // Render highlighted items if configured
            $this->render_highlighted_items( $instance );
            
            // Render main categories
            foreach ( $categories as $category ) {
                $this->render_category_item( $category, $instance );
            }
            ?>
        </ul>
        <?php
    }

    /**
     * Render highlighted items
     *
     * @param array $instance Widget instance.
     */
    private function render_highlighted_items( $instance ) {
        if ( empty( $instance['highlighted_items'] ) ) {
            return;
        }

        $items = array_filter( array_map( 'trim', explode( "\n", $instance['highlighted_items'] ) ) );
        
        foreach ( $items as $item ) {
            $parts = explode( '|', $item, 2 );
            $title = trim( $parts[0] ?? '' );
            $url = trim( $parts[1] ?? '#' );
            
            if ( empty( $title ) ) {
                continue;
            }
            
            ?>
            <li class="highlight menu-item">
                <a title="<?php echo esc_attr( $title ); ?>" href="<?php echo esc_url( $url ); ?>">
                    <?php echo esc_html( $title ); ?>
                </a>
            </li>
            <?php
        }
    }

    /**
     * Render category item
     *
     * @param WP_Term $category WooCommerce category.
     * @param array $instance Widget instance.
     */
    private function render_category_item( $category, $instance ) {
        $has_children = $this->has_category_children( $category->term_id );
        $menu_class = $has_children ? 'yamm-tfw menu-item menu-item-has-children dropdown' : 'menu-item';
        $term_link = get_term_link( $category );
        
        if ( is_wp_error( $term_link ) ) {
            $term_link = '#';
        }
        
        ?>
        <li class="<?php echo esc_attr( $menu_class ); ?>">
            <a title="<?php echo esc_attr( $category->name ); ?>" 
               href="<?php echo esc_url( $term_link ); ?>"
               <?php if ( $has_children ) : ?>
               data-bs-toggle="dropdown" class="dropdown-toggle" aria-haspopup="true"
               <?php endif; ?>>
                <?php echo esc_html( $category->name ); ?>
            </a>
            
            <?php if ( $has_children ) : ?>
                <ul role="menu" class="dropdown-menu">
                    <li class="menu-item">
                        <div class="yamm-content">
                            <?php $this->render_megamenu_content( $category, $instance ); ?>
                        </div>
                    </li>
                </ul>
            <?php endif; ?>
        </li>
        <?php
    }

    /**
     * Check if category has children
     *
     * @param int $category_id Category ID.
     * @return bool
     */
    private function has_category_children( $category_id ) {
        static $children_cache = array();
        
        if ( isset( $children_cache[ $category_id ] ) ) {
            return $children_cache[ $category_id ];
        }
        
        $children = get_terms( array(
            'taxonomy'     => 'product_cat',
            'parent'       => $category_id,
            'hide_empty'   => false,
            'number'       => 1,
            'hierarchical' => false,
            'fields'       => 'ids'
        ) );
        
        $has_children = ! empty( $children ) && ! is_wp_error( $children );
        $children_cache[ $category_id ] = $has_children;
        
        return $has_children;
    }


    /**
     * Render megamenu content
     *
     * @param WP_Term $category Main category.
     * @param array $instance Widget instance.
     */
    private function render_megamenu_content( $category, $instance ) {
        $subcategories = $this->get_subcategories( $category->term_id, $instance );
        $category_link = get_term_link( $category );
        
        if ( is_wp_error( $category_link ) ) {
            $category_link = '#';
        }

        // Category image if enabled
        if ( $instance['show_images'] ) {
            $this->render_category_image( $category );
        }

        ?>
        <div class="vc_row wpb_row vc_row-fluid">
            <div class="wpb_column vc_column_container vc_col-sm-6">
                <div class="vc_column-inner">
                    <div class="wpb_text_column wpb_content_element">
                        <div class="wpb_wrapper">
                            <ul>
                                <li class="nav-title">
                                    <a href="<?php echo esc_url( $category_link ); ?>">
                                        <?php echo esc_html( $category->name ); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo esc_url( $category_link ); ?>">
                                        <?php echo esc_html__( 'All', 'kintaelectric' ); ?> <?php echo esc_html( $category->name ); ?>
                                    </a>
                                </li>
                                
                                <?php $this->render_subcategories( $subcategories ); ?>
                                
                                <li class="nav-divider"></li>
                                <li>
                                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
                                        <span class="nav-text"><?php echo esc_html__( 'All Products', 'kintaelectric' ); ?></span>
                                        <span class="nav-subtext"><?php echo esc_html__( 'Discover more products', 'kintaelectric' ); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ( count( $subcategories ) > 5 ) : ?>
                <div class="wpb_column vc_column_container vc_col-sm-6">
                    <div class="vc_column-inner">
                        <div class="wpb_text_column wpb_content_element">
                            <div class="wpb_wrapper">
                                <ul>
                                    <li class="nav-title"><?php echo esc_html__( 'More Categories', 'kintaelectric' ); ?></li>
                                    <?php $this->render_subcategories( array_slice( $subcategories, 5 ) ); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get subcategories for a category
     *
     * @param int $category_id Category ID.
     * @param array $instance Widget instance.
     * @return array
     */
    private function get_subcategories( $category_id, $instance ) {
        $subcategories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'parent'     => $category_id,
            'hide_empty' => $instance['hide_empty'],
            'orderby'    => $instance['orderby'],
            'order'      => $instance['order'],
            'number'     => $instance['subcategories_limit'],
        ) );

        return is_wp_error( $subcategories ) ? array() : $subcategories;
    }

    /**
     * Render subcategories list
     *
     * @param array $subcategories Subcategories array.
     */
    private function render_subcategories( $subcategories ) {
        foreach ( $subcategories as $subcategory ) {
            $subcategory_link = get_term_link( $subcategory );
            
            if ( is_wp_error( $subcategory_link ) ) {
                $subcategory_link = '#';
            }
            
            ?>
            <li>
                <a href="<?php echo esc_url( $subcategory_link ); ?>">
                    <?php echo esc_html( $subcategory->name ); ?>
                </a>
            </li>
            <?php
        }
    }

    /**
     * Render category image
     *
     * @param WP_Term $category WooCommerce category.
     */
    private function render_category_image( $category ) {
        $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
        
        if ( ! $thumbnail_id ) {
            return;
        }
        
        $image = wp_get_attachment_image_src( $thumbnail_id, 'medium' );
        
        if ( ! $image ) {
            return;
        }
        
        ?>
        <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
            <div class="wpb_column vc_column_container vc_col-sm-12">
                <div class="vc_column-inner">
                    <div class="wpb_single_image wpb_content_element vc_align_left">
                        <figure class="wpb_wrapper vc_figure">
                            <div class="vc_single_image-wrapper vc_box_border_grey">
                                <img width="540" height="460"
                                     src="<?php echo esc_url( $image[0] ); ?>"
                                     class="vc_single_image-img attachment-medium"
                                     alt="<?php echo esc_attr( $category->name ); ?>"
                                     title="<?php echo esc_attr( $category->name ); ?>"
                                     loading="lazy"
                                     decoding="async">
                            </div>
                        </figure>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, $this->get_default_instance() );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'kintaelectric' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>">
                <?php esc_html_e( 'Number of categories to show:', 'kintaelectric' ); ?>
            </label>
            <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" 
                   type="number" step="1" min="1" value="<?php echo esc_attr( $instance['number'] ); ?>" size="3" />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'subcategories_limit' ) ); ?>">
                <?php esc_html_e( 'Number of subcategories to show:', 'kintaelectric' ); ?>
            </label>
            <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'subcategories_limit' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'subcategories_limit' ) ); ?>" 
                   type="number" step="1" min="1" value="<?php echo esc_attr( $instance['subcategories_limit'] ); ?>" size="3" />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>">
                <?php esc_html_e( 'Order by:', 'kintaelectric' ); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" 
                    name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
                <option value="name" <?php selected( $instance['orderby'], 'name' ); ?>>
                    <?php esc_html_e( 'Name', 'kintaelectric' ); ?>
                </option>
                <option value="count" <?php selected( $instance['orderby'], 'count' ); ?>>
                    <?php esc_html_e( 'Product Count', 'kintaelectric' ); ?>
                </option>
                <option value="slug" <?php selected( $instance['orderby'], 'slug' ); ?>>
                    <?php esc_html_e( 'Slug', 'kintaelectric' ); ?>
                </option>
                <option value="term_id" <?php selected( $instance['orderby'], 'term_id' ); ?>>
                    <?php esc_html_e( 'ID', 'kintaelectric' ); ?>
                </option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">
                <?php esc_html_e( 'Order:', 'kintaelectric' ); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" 
                    name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
                <option value="ASC" <?php selected( $instance['order'], 'ASC' ); ?>>
                    <?php esc_html_e( 'Ascending', 'kintaelectric' ); ?>
                </option>
                <option value="DESC" <?php selected( $instance['order'], 'DESC' ); ?>>
                    <?php esc_html_e( 'Descending', 'kintaelectric' ); ?>
                </option>
            </select>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['hide_empty'] ); ?> 
                   id="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>">
                <?php esc_html_e( 'Hide empty categories', 'kintaelectric' ); ?>
            </label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_images'] ); ?> 
                   id="<?php echo esc_attr( $this->get_field_id( 'show_images' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'show_images' ) ); ?>" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_images' ) ); ?>">
                <?php esc_html_e( 'Show category images', 'kintaelectric' ); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'highlighted_items' ) ); ?>">
                <?php esc_html_e( 'Highlighted items (one per line, format: Title|URL):', 'kintaelectric' ); ?>
            </label>
            <textarea class="widefat" rows="4" id="<?php echo esc_attr( $this->get_field_id( 'highlighted_items' ) ); ?>" 
                      name="<?php echo esc_attr( $this->get_field_name( 'highlighted_items' ) ); ?>"><?php echo esc_textarea( $instance['highlighted_items'] ); ?></textarea>
            <small><?php esc_html_e( 'Example: Value of the Day|/offers/ | Top 100 Offers|/top-offers/', 'kintaelectric' ); ?></small>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['number'] = absint( $new_instance['number'] );
        $instance['subcategories_limit'] = absint( $new_instance['subcategories_limit'] );
        $instance['orderby'] = sanitize_text_field( $new_instance['orderby'] );
        $instance['order'] = sanitize_text_field( $new_instance['order'] );
        $instance['hide_empty'] = isset( $new_instance['hide_empty'] ) ? (bool) $new_instance['hide_empty'] : false;
        $instance['show_images'] = isset( $new_instance['show_images'] ) ? (bool) $new_instance['show_images'] : false;
        $instance['highlighted_items'] = sanitize_textarea_field( $new_instance['highlighted_items'] );
        
        return $instance;
    }

    /**
     * Get default instance values
     *
     * @return array
     */
    private function get_default_instance() {
        return array(
            'title'                => '',
            'number'               => 20,
            'subcategories_limit'  => 15,
            'orderby'              => 'name',
            'order'                => 'ASC',
            'hide_empty'           => false,
            'show_images'          => true,
            'highlighted_items'    => '',
        );
    }

    /**
     * Clear widget cache
     */
    public static function clear_cache() {
        global $wpdb;
        
        $wpdb->query( 
            $wpdb->prepare( 
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_PREFIX . '%'
            )
        );
    }
}

// Hook to clear cache when categories are updated
add_action( 'created_product_cat', array( 'KintaElectric_Canvas_Menu_Widget', 'clear_cache' ) );
add_action( 'edited_product_cat', array( 'KintaElectric_Canvas_Menu_Widget', 'clear_cache' ) );
add_action( 'delete_product_cat', array( 'KintaElectric_Canvas_Menu_Widget', 'clear_cache' ) );
