<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<?php
/**
 * Hook: woocommerce_before_main_content.
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="site-content-inner row">

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="vc_row wpb_row vc_row-fluid">
				<div class="wpb_column vc_column_container vc_col-sm-12">
					<div class="vc_column-inner">
						<div class="wpb_wrapper">
							
							<?php
							/**
							 * Hook: woocommerce_archive_description.
							 * @hooked woocommerce_taxonomy_archive_description - 10
							 * @hooked woocommerce_product_archive_description - 10
							 */
							do_action( 'woocommerce_archive_description' );
							?>
							
							<?php if ( woocommerce_product_loop() ) : ?>
								
								<header class="page-header">
									<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
									<?php woocommerce_result_count(); ?>
								</header>

								<div class="shop-control-bar">
									<div class="handheld-sidebar-toggle">
										<button class="btn sidebar-toggler" type="button">
											<i class="fas fa-sliders-h"></i><span>Filters</span>
										</button>
									</div>
									<ul class="shop-view-switcher nav nav-tabs" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" data-bs-toggle="tab" data-archive-class="grid" title="Grid View" href="#grid">
												<i class="fa fa-th"></i>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" data-archive-class="grid-extended" title="Grid Extended View" href="#grid-extended">
												<i class="fa fa-align-justify"></i>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" data-archive-class="list-view" title="List View" href="#list-view">
												<i class="fa fa-list"></i>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" data-archive-class="list-view-small" title="List View Small" href="#list-view-small">
												<i class="fa fa-th-list"></i>
											</a>
										</li>
									</ul>
									<?php woocommerce_catalog_ordering(); ?>
									<form method="POST" action="" class="form-electro-wc-ppp">
										<select name="ppp" onchange="this.form.submit()" class="electro-wc-wppp-select c-select">
											<option value="20">Show 20</option>
											<option value="40">Show 40</option>
											<option value="-1">Show All</option>
										</select>
									</form>
									<nav class="electro-advanced-pagination">
										<form method="post" class="form-adv-pagination">
											<input id="goto-page" size="2" min="1" max="3" step="1" type="number" class="form-control" value="1">
										</form>
										<?php woocommerce_pagination(); ?>
									</nav>
								</div>

								<?php
								/**
								 * Hook: woocommerce_before_shop_loop.
								 * @hooked woocommerce_output_all_notices - 10
								 */
								do_action( 'woocommerce_before_shop_loop' );
								?>
								
								<ul data-view="grid" data-bs-toggle="shop-products" class="products products list-unstyled row g-0 row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-5 row-cols-xxl-5">
									<?php
									if ( wc_get_loop_prop( 'total' ) ) {
										while ( have_posts() ) {
											the_post();
											do_action( 'woocommerce_shop_loop' );
											wc_get_template_part( 'content', 'product' );
										}
									}
									?>
								</ul>
								
								<?php
								/**
								 * Hook: woocommerce_after_shop_loop.
								 * @hooked woocommerce_pagination - 10
								 */
								do_action( 'woocommerce_after_shop_loop' );
								?>

							<?php else : ?>
								<?php do_action( 'woocommerce_no_products_found' ); ?>
							<?php endif; ?>

						</div>
					</div>
				</div>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

	<?php
	/**
	 * Shop Sidebar
	 */
	get_sidebar( 'shop' );
	?>

</div>

<?php
get_footer( 'shop' );
?>