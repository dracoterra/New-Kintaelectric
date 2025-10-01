<?php
/**
 * The front page template file
 *
 * This is the template that displays the front page
 *
 * @package kintaelectric
 */

get_header(); ?>

<main id="main" class="site-main">
    
    <?php
    // Check if Elementor is active and if this page was built with Elementor
    if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'front-page' ) ) {
        // Elementor will handle the content
    } else {
        // Default front page content
        ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="front-page-content">
                        <h1><?php esc_html_e( 'Bienvenido a Kinta Electric Venezuela', 'kintaelectric' ); ?></h1>
                        <p><?php esc_html_e( 'Tu proveedor confiable de soluciones eléctricas', 'kintaelectric' ); ?></p>
                        
                        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                            <div class="front-page-shop-section">
                                <h2><?php esc_html_e( 'Nuestros Productos', 'kintaelectric' ); ?></h2>
                                <?php
                                // Display featured products
                                $featured_products = wc_get_featured_product_ids();
                                if ( ! empty( $featured_products ) ) {
                                    echo '<div class="featured-products">';
                                    foreach ( array_slice( $featured_products, 0, 4 ) as $product_id ) {
                                        $product = wc_get_product( $product_id );
                                        if ( $product ) {
                                            echo '<div class="product-item">';
                                            echo '<a href="' . esc_url( get_permalink( $product_id ) ) . '">';
                                            echo get_the_post_thumbnail( $product_id, 'medium' );
                                            echo '<h3>' . esc_html( $product->get_name() ) . '</h3>';
                                            echo '<span class="price">' . $product->get_price_html() . '</span>';
                                            echo '</a>';
                                            echo '</div>';
                                        }
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

</main>

<?php get_footer(); ?>

