<?php
/**
 * Template part for displaying dynamic categories menu
 * 
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Verificar que WooCommerce esté activo
if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

// Obtener categorías principales (padres) - limitado a 11 como solicitado
$parent_categories = get_terms(array(
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'parent' => 0,
    'number' => 11
));

if ( is_wp_error( $parent_categories ) || empty( $parent_categories ) ) {
    return;
}
?>

<ul id="menu-all-departments-menu-1" class="dropdown-menu yamm">
    <?php foreach ( $parent_categories as $index => $category ) : 
        // Obtener subcategorías
        $subcategories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'parent' => $category->term_id,
            'number' => 10
        ));

        // Obtener imagen de la categoría
        $category_image = '';
        $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
        if ( $thumbnail_id ) {
            $image_url = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
            if ( $image_url ) {
                $category_image = $image_url;
            }
        }
    ?>

        <?php if ( !empty( $subcategories ) && !is_wp_error( $subcategories ) ) : ?>
            <!-- Categoría con subcategorías - manteniendo la estructura EXACTA del original -->
            <li id="menu-item-<?php echo ( 5220 + $index ); ?>" class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-<?php echo ( 5220 + $index ); ?> dropdown">
                <a title="<?php echo esc_attr( $category->name ); ?>" href="<?php echo esc_url( get_term_link( $category ) ); ?>" data-bs-toggle="dropdown-hover" class="dropdown-toggle" aria-haspopup="true"><?php echo esc_html( $category->name ); ?></a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-<?php echo ( 5310 + $index ); ?>" class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-<?php echo ( 5310 + $index ); ?>">
                        <div class="yamm-content">

                            <?php if ( $category_image ) : ?>
                                <!-- Imagen de la categoría con estructura EXACTA del original -->
                                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content bg-yamm-content-bottom bg-yamm-content-right">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">
                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div class="vc_single_image-wrapper vc_box_border_grey">
                                                            <img fetchpriority="high" width="540" height="460" 
                                                                 src="<?php echo esc_url( $category_image ); ?>" 
                                                                 class="vc_single_image-img attachment-full" 
                                                                 alt="<?php echo esc_attr( $category->name ); ?>" 
                                                                 title="<?php echo esc_attr( $category->name ); ?>" 
                                                                 decoding="async" 
                                                                 srcset="<?php echo esc_url( $category_image ); ?> 540w, <?php echo esc_url( $category_image ); ?> 300w" 
                                                                 sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Lista de subcategorías - primera columna con estructura EXACTA -->
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div class="wpb_wrapper">
                                            <div class="wpb_text_column wpb_content_element">
                                                <div class="wpb_wrapper">
                                                    <ul>
                                                        <li class="nav-title"><?php echo esc_html( $category->name ); ?></li>

                                                        <?php 
                                                        // Mostrar subcategorías (máximo 8 en la primera columna)
                                                        $first_column_subcats = array_slice( $subcategories, 0, 8 );
                                                        foreach ( $first_column_subcats as $subcategory ) : ?>
                                                            <li><a href="<?php echo esc_url( get_term_link( $subcategory ) ); ?>"><?php echo esc_html( $subcategory->name ); ?></a></li>
                                                        <?php endforeach; ?>

                                                        <li class="nav-divider"></li>
                                                        <li><a href="<?php echo esc_url( get_term_link( $category ) ); ?>">
                                                            <span class="nav-text">Ver todas las <?php echo esc_html( $category->name ); ?></span>
                                                            <span class="nav-subtext">Descubre más productos</span>
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ( count( $subcategories ) > 8 ) : ?>
                                    <!-- Segunda columna con más subcategorías si las hay - estructura EXACTA -->
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Más <?php echo esc_html( $category->name ); ?></li>

                                                            <?php 
                                                            $second_column_subcats = array_slice( $subcategories, 8 );
                                                            foreach ( $second_column_subcats as $subcategory ) : ?>
                                                                <li><a href="<?php echo esc_url( get_term_link( $subcategory ) ); ?>"><?php echo esc_html( $subcategory->name ); ?></a></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
        <?php else : ?>
            <!-- Categoría simple sin subcategorías -->
            <li id="menu-item-<?php echo ( 5349 + $index + 3 ); ?>" class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-<?php echo ( 5349 + $index + 3 ); ?>">
                <a title="<?php echo esc_attr( $category->name ); ?>" href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
            </li>
        <?php endif; ?>

    <?php endforeach; ?>
</ul>