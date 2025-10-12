<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @package WooCommerce\Templates
 * @version 8.6.0
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

	<?php
	/**
	 * Shop Sidebar - Lado izquierdo
	 */
	get_sidebar( 'shop' );
	?>

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
							
							<!-- Sección de Productos Recomendados -->
							<?php
							// Obtener productos destacados
							$recommended_products = wc_get_products(array(
								'featured' => true,
								'limit' => 12,
								'status' => 'publish'
							));

							// Filtrar productos válidos
							$recommended_products = array_filter($recommended_products, function($product) {
								return !empty($product) && is_object($product) && method_exists($product, 'get_id');
							});

							// Si no hay suficientes productos destacados, completar con productos recientes
							if (count($recommended_products) < 12) {
								$featured_count = count($recommended_products);
								$remaining_count = 12 - $featured_count;
								
								// Obtener IDs de productos ya incluidos
								$exclude_ids = array();
								foreach ($recommended_products as $product) {
									if ($product && method_exists($product, 'get_id')) {
										$exclude_ids[] = $product->get_id();
									}
								}
								
								$recent_products = wc_get_products(array(
									'limit' => $remaining_count,
									'status' => 'publish',
									'orderby' => 'date',
									'order' => 'DESC',
									'exclude' => $exclude_ids
								));
								
								// Filtrar productos recientes válidos
								$recent_products = array_filter($recent_products, function($product) {
									return !empty($product) && is_object($product) && method_exists($product, 'get_id');
								});
								
								// Combinar productos destacados con recientes
								$recommended_products = array_merge($recommended_products, $recent_products);
							}
							
							if (!empty($recommended_products)) : ?>
							<section class="section-products-carousel">
								<header>
									<h2 class="h1">Productos recomendados</h2>
									<div class="owl-nav">
										<a href="#products-carousel-prev" data-target="#products-carousel-recommended" class="slider-prev"><i class="fa fa-angle-left"></i></a>
										<a href="#products-carousel-next" data-target="#products-carousel-recommended" class="slider-next"><i class="fa fa-angle-right"></i></a>
									</div>
								</header>
								
								<div id="products-carousel-recommended" class="owl-carousel">
											<?php
											foreach ($recommended_products as $product) {
												// Verificar que el producto sea válido antes de usarlo
												if (empty($product) || !is_object($product) || !method_exists($product, 'get_id')) {
													continue;
												}
												
												// Obtener el ID del producto y verificar que sea válido
												$product_id = $product->get_id();
												if (empty($product_id) || !is_numeric($product_id)) {
													continue;
												}
												
												// Configurar el producto global para el template
												global $post;
												$post = get_post($product_id);
												
												// Verificar que get_post devuelva un resultado válido
												if (empty($post) || !is_object($post)) {
													continue;
												}
												
												setup_postdata($post);
												
												// Verificar visibilidad del producto (usar la variable local $product, no la global)
												if (! $product->is_visible() ) {
													wp_reset_postdata();
													continue;
												}
												
												echo '<div class="product type-product post-' . $product_id . ' status-publish instock';
												
												// Agregar clases de categorías
												$categories = wp_get_post_terms($product_id, 'product_cat');
												if (!empty($categories)) {
													foreach ($categories as $category) {
														echo ' product_cat-' . $category->slug;
													}
												}
												
												// Agregar clases adicionales
												if ($product->is_featured()) echo ' featured';
												if ($product->is_on_sale()) echo ' sale';
												if (has_post_thumbnail($product_id)) echo ' has-post-thumbnail';
												if ($product->is_purchasable()) echo ' purchasable';
												echo ' product-type-' . $product->get_type();
												echo '">';
												
												echo '<div class="product-outer product-item__outer">';
												echo '<div class="product-inner product-item__inner">';
												
												// Header del producto
												echo '<div class="product-loop-header product-item__header">';
												echo '<span class="loop-product-categories">';
												echo wc_get_product_category_list( $product_id, ', ', '<a href="%s" rel="tag">', '</a>' );
												echo '</span>';
												echo '<a href="' . esc_url( get_the_permalink() ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
												echo '<h2 class="woocommerce-loop-product__title">' . get_the_title() . '</h2>';
												echo '<div class="product-thumbnail product-item__thumbnail">';
												woocommerce_template_loop_product_thumbnail();
												echo '</div>';
												echo '</a>';
												echo '</div><!-- /.product-loop-header -->';
												
												// Body del producto
												echo '<div class="product-loop-body product-item__body">';
												echo '<span class="loop-product-categories">';
												echo wc_get_product_category_list( $product_id, ', ', '<a href="%s" rel="tag">', '</a>' );
												echo '</span>';
												echo '<a href="' . esc_url( get_the_permalink() ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
												echo '<h2 class="woocommerce-loop-product__title">' . get_the_title() . '</h2>';
												
												// Rating del producto
												echo '<div class="product-rating">';
												woocommerce_template_loop_rating();
												echo '</div>';
												
												echo '<div class="product-short-description">';
												echo '<div>';
												echo wp_kses_post( $product->get_short_description() );
												echo '</div>';
												echo '</div>';
												echo '<div class="product-sku">SKU: ' . esc_html( $product->get_sku() ) . '</div>';
												echo '</a>';
												echo '</div><!-- /.product-loop-body -->';
												
												// Footer del producto
												echo '<div class="product-loop-footer product-item__footer">';
												echo '<div class="price-add-to-cart">';
												woocommerce_template_loop_price();
												echo '<div class="add-to-cart-wrap">';
												woocommerce_template_loop_add_to_cart();
												echo '</div>';
												echo '</div><!-- /.price-add-to-cart -->';
												
												// Hover area
												echo '<div class="hover-area">';
												echo '<div class="action-buttons">';
												
												// YITH Wishlist - Función nativa del plugin
												if ( class_exists( 'YITH_WCWL_Frontend' ) ) {
													YITH_WCWL_Frontend::get_instance()->print_button();
												}
												
												// YITH Compare - Usar función nativa del plugin
												if ( class_exists( 'YITH_WooCompare_Frontend' ) ) {
													YITH_WooCompare_Frontend::instance()->output_button();
												}
												
												echo '</div>';
												echo '</div>';
												echo '</div><!-- /.product-loop-footer -->';
												
												echo '</div><!-- /.product-inner -->';
												echo '</div><!-- /.product-outer -->';
												echo '</div>'; // product
												
												wp_reset_postdata();
											}
											?>
								</div>
								
								<style>
								/* Asegurar que el carousel se muestre */
								#products-carousel-recommended {
									display: block !important;
									opacity: 1 !important;
								}
								
								/* Altura mínima para el contenedor del carousel */
								#products-carousel-recommended .owl-stage-outer {
									min-height: 436px;
								}
								</style>
								
								<script>
								jQuery(document).ready(function($) {
									// Esperar un poco para que el DOM esté completamente cargado
									setTimeout(function() {
										var $carousel = $("#products-carousel-recommended");
										var $products = $carousel.find('.product');
										
										if ($carousel.length > 0 && $products.length > 0) {
											console.log("Inicializando Owl Carousel - Productos encontrados:", $products.length);
											
											try {
												// Destruir cualquier instancia previa
												if ($carousel.hasClass('owl-loaded')) {
													$carousel.trigger('destroy.owl.carousel').removeClass('owl-loaded owl-drag');
												}
												
												// Limpiar clases y estilos
												$carousel.find('.owl-stage-outer').remove();
												$carousel.find('.owl-nav').remove();
												$carousel.find('.owl-dots').remove();
												
												// Inicializar con configuración mínima
												$carousel.owlCarousel({
													items: 5,
													nav: false,
													dots: true,
													margin: 0,
													responsive: {
														0: { items: 2 },
														480: { items: 3 },
														768: { items: 3 },
														992: { items: 4 },
														1200: { items: 5 }
													}
												});
												
												// Conectar botones de navegación personalizados
												$('.slider-prev[data-target="#products-carousel-recommended"]').on('click', function(e) {
													e.preventDefault();
													$carousel.trigger('prev.owl.carousel');
												});
												
												$('.slider-next[data-target="#products-carousel-recommended"]').on('click', function(e) {
													e.preventDefault();
													$carousel.trigger('next.owl.carousel');
												});
												
												console.log("Owl Carousel inicializado exitosamente con navegación");
												
											} catch (error) {
												console.log("Error al inicializar Owl Carousel:", error);
												// Fallback: mostrar productos sin carousel
												$carousel.css('display', 'block');
												$carousel.find('.product').css('display', 'inline-block');
											}
										} else {
											console.log("No se puede inicializar - Carousel:", $carousel.length, "Productos:", $products.length);
										}
									}, 500);
								});
								</script>
							</section>
							<?php else : ?>
							<div style="background: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;">
								<strong>Debug:</strong> No se encontraron productos para mostrar
							</div>
							<?php endif; ?>
							
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

</div>

<?php
get_footer( 'shop' );
?>