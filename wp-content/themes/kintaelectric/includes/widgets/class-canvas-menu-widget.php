<?php
/**
 * KintaElectric Canvas Menu Widget
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class KintaElectric_Canvas_Menu_Widget extends WP_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            'kintaelectric_canvas_menu_widget', // Base ID
            esc_html__( 'KintaElectric Canvas Menu', 'kintaelectric' ), // Name
            array( 'description' => esc_html__( 'Displays the off-canvas navigation menu with WooCommerce categories.', 'kintaelectric' ) ) // Args
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
        // Verificar que WooCommerce esté activo
        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<p>' . esc_html__( 'WooCommerce is not active.', 'kintaelectric' ) . '</p>';
            return;
        }

        // Verificar que la taxonomía de productos exista
        if ( ! taxonomy_exists( 'product_cat' ) ) {
            echo '<p>' . esc_html__( 'Product categories taxonomy not found.', 'kintaelectric' ) . '</p>';
            return;
        }

        $instance = wp_parse_args( (array) $instance, $this->get_default_instance() );
        
        echo $args['before_widget'];
        
        // Obtener categorías de WooCommerce
        $categories = $this->get_woocommerce_categories( $instance );
        
        if ( empty( $categories ) ) {
            echo '<p>' . esc_html__( 'No categories found.', 'kintaelectric' ) . '</p>';
            echo $args['after_widget'];
            return;
        }

        // Generar el menú dinámico
        $this->render_dynamic_menu( $categories, $instance );
        
        echo $args['after_widget'];
    }

    /**
     * Obtener categorías de WooCommerce
     *
     * @param array $instance Widget instance.
     * @return array
     */
    private function get_woocommerce_categories( $instance ) {
        // Método 1: Usar get_terms con parámetros específicos
        $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => $instance['orderby'],
            'order'        => $instance['order'],
            'hide_empty'   => $instance['hide_empty'],
            'number'       => $instance['number'],
            'parent'       => 0, // Solo categorías principales
            'hierarchical' => false, // No incluir subcategorías en esta consulta
        );

        $categories = get_terms( $args );
        
        if ( is_wp_error( $categories ) ) {
            return $this->get_categories_fallback( $instance );
        }

        // Si no hay categorías, intentar método alternativo
        if ( empty( $categories ) ) {
            return $this->get_categories_fallback( $instance );
        }

        return $categories;
    }

    /**
     * Método alternativo para obtener categorías si get_terms falla
     *
     * @param array $instance Widget instance.
     * @return array
     */
    private function get_categories_fallback( $instance ) {
        // Usar wp_list_categories como método alternativo
        $args = array(
            'taxonomy'     => 'product_cat',
            'hide_empty'   => $instance['hide_empty'],
            'parent'       => 0,
            'number'       => $instance['number'],
            'orderby'      => $instance['orderby'],
            'order'        => $instance['order'],
            'echo'         => false,
        );

        // Obtener IDs de categorías usando wp_list_categories
        $category_list = wp_list_categories( $args );
        
        if ( empty( $category_list ) ) {
            // Último recurso: obtener todas las categorías sin filtros
            $all_categories = get_terms( array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent' => 0,
            ) );
            
            if ( ! is_wp_error( $all_categories ) ) {
                // Aplicar límite manualmente
                return array_slice( $all_categories, 0, $instance['number'] );
            }
        }

        return array();
    }

    /**
     * Renderizar el menú dinámico
     *
     * @param array $categories Categorías de WooCommerce.
     * @param array $instance Widget instance.
     */
    private function render_dynamic_menu( $categories, $instance ) {
        ?>
        <ul id="menu-all-departments-menu" class="nav nav-inline yamm">
            <?php
            // Añadir elementos destacados si están configurados
            $this->render_highlighted_items( $instance );
            
            // Renderizar categorías principales
            foreach ( $categories as $index => $category ) {
                $this->render_category_item( $category, $index, $instance );
            }
            ?>
        </ul>
        <?php
    }

    /**
     * Renderizar elementos destacados
     *
     * @param array $instance Widget instance.
     */
    private function render_highlighted_items( $instance ) {
        if ( ! empty( $instance['highlighted_items'] ) ) {
            $items = explode( "\n", $instance['highlighted_items'] );
            foreach ( $items as $item ) {
                $item = trim( $item );
                if ( empty( $item ) ) continue;
                
                $parts = explode( '|', $item );
                $title = isset( $parts[0] ) ? trim( $parts[0] ) : '';
                $url = isset( $parts[1] ) ? trim( $parts[1] ) : '#';
                
                if ( ! empty( $title ) ) {
                    ?>
                    <li class="highlight menu-item">
                        <a title="<?php echo esc_attr( $title ); ?>" href="<?php echo esc_url( $url ); ?>">
                            <?php echo esc_html( $title ); ?>
                        </a>
                    </li>
                    <?php
                }
            }
        }
    }

    /**
     * Renderizar elemento de categoría
     *
     * @param WP_Term $category Categoría de WooCommerce.
     * @param int $index Índice de la categoría.
     * @param array $instance Widget instance.
     */
    private function render_category_item( $category, $index, $instance ) {
        $has_children = $this->has_category_children( $category->term_id );
        $menu_class = $has_children ? 'yamm-tfw menu-item menu-item-has-children dropdown' : 'menu-item';
        
        ?>
        <li class="<?php echo esc_attr( $menu_class ); ?>">
            <a title="<?php echo esc_attr( $category->name ); ?>" 
               href="<?php echo esc_url( get_term_link( $category ) ); ?>"
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
     * Verificar si la categoría tiene hijos
     *
     * @param int $category_id ID de la categoría.
     * @return bool
     */
    private function has_category_children( $category_id ) {
        $children = get_terms( array(
            'taxonomy'   => 'product_cat',
            'parent'     => $category_id,
            'hide_empty' => false,
            'number'     => 1,
            'hierarchical' => false,
        ) );
        
        return ! empty( $children ) && ! is_wp_error( $children );
    }


    /**
     * Renderizar contenido del megamenú
     *
     * @param WP_Term $category Categoría principal.
     * @param array $instance Widget instance.
     */
    private function render_megamenu_content( $category, $instance ) {
        // Obtener subcategorías
        $subcategories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'parent'     => $category->term_id,
            'hide_empty' => $instance['hide_empty'],
            'orderby'    => $instance['orderby'],
            'order'      => $instance['order'],
            'number'     => $instance['subcategories_limit'],
        ) );

        if ( is_wp_error( $subcategories ) ) {
            $subcategories = array();
        }

        // Imagen de la categoría si está habilitada
        if ( $instance['show_images'] ) {
            $this->render_category_image( $category );
        }

        // Contenido del megamenú
        ?>
        <div class="vc_row wpb_row vc_row-fluid">
            <div class="wpb_column vc_column_container vc_col-sm-6">
                <div class="vc_column-inner">
                    <div class="wpb_text_column wpb_content_element">
                        <div class="wpb_wrapper">
                            <ul>
                                <li class="nav-title">
                                    <a href="<?php echo esc_url( get_term_link( $category ) ); ?>">
                                        <?php echo esc_html( $category->name ); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo esc_url( get_term_link( $category ) ); ?>">
                                        <?php echo esc_html__( 'All', 'kintaelectric' ); ?> <?php echo esc_html( $category->name ); ?>
                                    </a>
                                </li>
                                
                                <?php if ( ! empty( $subcategories ) ) : ?>
                                    <?php foreach ( $subcategories as $subcategory ) : ?>
                                        <li>
                                            <a href="<?php echo esc_url( get_term_link( $subcategory ) ); ?>">
                                                <?php echo esc_html( $subcategory->name ); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
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
            
            <?php if ( ! empty( $subcategories ) && count( $subcategories ) > 5 ) : ?>
                <div class="wpb_column vc_column_container vc_col-sm-6">
                    <div class="vc_column-inner">
                        <div class="wpb_text_column wpb_content_element">
                            <div class="wpb_wrapper">
                                <ul>
                                    <li class="nav-title"><?php echo esc_html__( 'More Categories', 'kintaelectric' ); ?></li>
                                    <?php foreach ( array_slice( $subcategories, 5 ) as $subcategory ) : ?>
                                        <li>
                                            <a href="<?php echo esc_url( get_term_link( $subcategory ) ); ?>">
                                                <?php echo esc_html( $subcategory->name ); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
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
     * Renderizar imagen de la categoría
     *
     * @param WP_Term $category Categoría de WooCommerce.
     */
    private function render_category_image( $category ) {
        $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
        
        if ( $thumbnail_id ) {
            $image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
            if ( $image ) {
                ?>
                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                    <div class="wpb_column vc_column_container vc_col-sm-12">
                        <div class="vc_column-inner">
                            <div class="wpb_single_image wpb_content_element vc_align_left">
                                <figure class="wpb_wrapper vc_figure">
                                    <div class="vc_single_image-wrapper vc_box_border_grey">
                                        <img width="540" height="460"
                                             src="<?php echo esc_url( $image[0] ); ?>"
                                             class="vc_single_image-img attachment-full"
                                             alt="<?php echo esc_attr( $category->name ); ?>"
                                             title="<?php echo esc_attr( $category->name ); ?>"
                                             decoding="async">
                                    </div>
                                </figure>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
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
            'number'               => 20, // Aumentado de 8 a 20
            'subcategories_limit'  => 15, // Aumentado de 10 a 15
            'orderby'              => 'name',
            'order'                => 'ASC',
            'hide_empty'           => false, // Cambiado a false para mostrar todas las categorías
            'show_images'          => true,
            'highlighted_items'    => '',
        );
    }
}
