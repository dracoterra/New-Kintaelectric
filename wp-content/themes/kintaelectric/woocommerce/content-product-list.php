<?php
/**
 * The template for displaying product content in list view
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product-list.php.
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'list-view', $product ); ?>>
	<div class="product-outer product-item__outer">
		<div class="product-inner product-item__inner">
			<div class="product-loop-header product-item__header">
				<span class="loop-product-categories">
					<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<a href="%s" rel="tag">', '</a>' ); ?>
				</span>
				<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
					<h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>
					<div class="product-thumbnail product-item__thumbnail">
						<?php woocommerce_template_loop_product_thumbnail(); ?>
					</div>
				</a>
			</div><!-- /.product-loop-header -->
			
			<div class="product-loop-body product-item__body">
				<span class="loop-product-categories">
					<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<a href="%s" rel="tag">', '</a>' ); ?>
				</span>
				<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
					<h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>
					<?php
					/**
					 * Hook: woocommerce_after_shop_loop_item_title.
					 *
					 * @hooked woocommerce_template_loop_rating - 5
					 * @hooked woocommerce_template_loop_price - 10
					 */
					do_action( 'woocommerce_after_shop_loop_item_title' );
					?>
					<div class="product-short-description">
						<?php echo wp_kses_post( $product->get_short_description() ); ?>
					</div>
					<div class="product-sku">SKU: <?php echo esc_html( $product->get_sku() ); ?></div>
				</a>
			</div><!-- /.product-loop-body -->
			
			<div class="product-loop-footer product-item__footer">
				<div class="price-add-to-cart">
					<?php woocommerce_template_loop_price(); ?>
					<div class="add-to-cart-wrap" data-bs-toggle="tooltip" data-bs-title="Add to cart">
						<?php woocommerce_template_loop_add_to_cart(); ?>
					</div>
				</div><!-- /.price-add-to-cart -->
				<div class="hover-area">
					<div class="action-buttons">
						<?php
						/**
						 * Hook: woocommerce_after_shop_loop_item.
						 *
						 * @hooked woocommerce_template_loop_add_to_cart - 10
						 */
						do_action( 'woocommerce_after_shop_loop_item' );
						?>
					</div>
				</div>
			</div><!-- /.product-loop-footer -->
		</div><!-- /.product-inner -->
	</div><!-- /.product-outer -->
</li>
