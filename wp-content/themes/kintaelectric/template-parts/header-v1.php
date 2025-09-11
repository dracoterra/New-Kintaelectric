<?php
/**
 * Header Template v1 - Complete Electro Style
 * Recreated from original Electro theme with ALL structures
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="top-bar hidden-lg-down d-none d-xl-block">
	<div class="container clearfix">
		<ul id="menu-top-bar-left" class="nav nav-inline float-start electro-animate-dropdown flip">
			<li id="menu-item-5166" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5166"><a title="Welcome to Worldwide Electronics Store" href="#">Welcome to Worldwide Electronics
					Store</a></li>
		</ul>
		<ul id="menu-top-bar-right" class="nav nav-inline float-end electro-animate-dropdown flip">
			<li id="menu-item-5167" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5167"><a title="Store Locator" href="#"><i class="ec ec-map-pointer"></i>Store Locator</a></li>
			<li id="menu-item-5299" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5299"><a title="Track Your Order" href="track-your-order/index.htm"><i class="ec ec-transport"></i>Track Your Order</a></li>
			<li id="menu-item-5293" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5293"><a title="Shop" href="shop/index.htm"><i class="ec ec-shopping-bag"></i>Shop</a></li>
			<li id="menu-item-5294" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5294"><a title="Mi Cuenta" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><i class="ec ec-user"></i>Mi Cuenta</a>
			</li>
		</ul>
	</div>
</div>
<header id="masthead" class="site-header header-v1 stick-this">

				<div class="container hidden-lg-down d-none d-xl-block">
					<div class="masthead row align-items-center">
						<div class="header-logo-area d-flex justify-content-between align-items-center">
							<div class="header-site-branding" style="max-width: 170px">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-logo-link">
									<?php echo kintaelectric_get_logo( 'light' ); ?>
								</a>
							</div>
							<div class="off-canvas-navigation-wrapper ">
								<div class="off-canvas-navbar-toggle-buttons clearfix">
									<button class="navbar-toggler navbar-toggle-hamburger " type="button">
										<i class="ec ec-menu"></i>
									</button>
									<button class="navbar-toggler navbar-toggle-close " type="button">
										<i class="ec ec-close-remove"></i>
									</button>
								</div>

								<div class="off-canvas-navigation light" id="default-oc-header">
									<?php if ( is_active_sidebar( 'canvas-menu' ) ) : ?>
										<?php dynamic_sidebar( 'canvas-menu' ); ?>
									<?php else : ?>
										<div class="widget-placeholder text-center p-4">
											<p class="mb-2"><?php esc_html_e( 'Configure a widget', 'kintaelectric' ); ?></p>
											<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" 
											   class="btn btn-outline-primary btn-sm" 
											   title="<?php esc_attr_e( 'Configure Canvas Menu Widget', 'kintaelectric' ); ?>">
												<i class="fa fa-pencil"></i> <?php esc_html_e( 'Configure', 'kintaelectric' ); ?>
											</a>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<form class="navbar-search col" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
							autocomplete="off">
							<label class="sr-only screen-reader-text visually-hidden" for="search">Buscar:</label>
							<div class="input-group">
								<div class="input-search-field">
									<input type="text" id="search"
										class="form-control search-field product-search-field" dir="ltr" value=""
										name="s" placeholder="Buscar productos..." autocomplete="off">
								</div>
								<div class="input-group-addon search-categories d-flex">
									<select name='product_cat' id='electro_header_search_categories_dropdown'
										class='postform resizeselect'>
										<?php echo kintaelectric_get_product_categories(); ?>
									</select>
								</div>
								<div class="input-group-btn">
									<input type="hidden" id="search-param" name="post_type" value="product">
									<button type="submit" class="btn btn-secondary"><i
											class="fas fa-search"></i></button>
								</div>
							</div>
						</form>
						<div class="header-icons col-auto d-flex justify-content-end align-items-center">
							<?php if ( class_exists( 'YITH_Woocompare' ) ) : ?>
							<div style="position: relative;" class="header-icon" data-bs-toggle="tooltip"
								data-bs-placement="bottom" data-bs-title="<?php esc_attr_e( 'Compare', 'kintaelectric' ); ?>">
								<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'yith-woocompare-view-table', 'iframe' => 'yes' ), site_url() ) ); ?>"
									class="yith-woocompare-open">
									<i class="ec ec-compare"></i>
									<span id="navbar-compare-count"
										class="navbar-compare-count count header-icon-counter">0</span>
								</a>
							</div>
							<?php endif; ?>
							<div class="header-icon" data-bs-toggle="tooltip" data-bs-placement="bottom"
								data-bs-title="<?php esc_attr_e('Wishlist', 'kintaelectric'); ?>">
								<?php
								// Verificar si YITH Wishlist está activo
								if (defined('YITH_WCWL')) {
									$wishlist_url = YITH_WCWL()->get_wishlist_url();
									$wishlist_count = yith_wcwl_count_all_products();
									?>
									<a href="<?php echo esc_url($wishlist_url); ?>">
										<i class="ec ec-favorites"></i>
										<?php if ($wishlist_count > 0): ?>
											<span class="header-icon-counter"><?php echo esc_html($wishlist_count); ?></span>
										<?php endif; ?>
									</a>
									<?php
								} else {
									// Fallback si el plugin no está activo
									?>
									<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
										<i class="ec ec-favorites"></i>
									</a>
									<?php
								}
								?>
							</div>
							<div class="header-icon header-icon__user-account dropdown animate-dropdown"
								data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Mi Cuenta">
								<a class="dropdown-toggle" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" data-bs-toggle="dropdown"><i
										class="ec ec-user"></i></a>
								<ul class="dropdown-menu dropdown-menu-user-account">
									<li>
										<?php if (is_user_logged_in()) : ?>
											<div class="register-sign-in-dropdown-inner">
												<div class="sign-in">
													<p>Hola, <?php echo esc_html(wp_get_current_user()->display_name); ?>!</p>
													<div class="sign-in-action">
														<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="sign-in-button">
															Mi Cuenta
														</a>
													</div>
												</div>
												<div class="register">
													<p>¿Quieres salir?</p>
													<div class="register-action">
														<a href="<?php echo esc_url(wp_logout_url(wc_get_page_permalink('myaccount'))); ?>">
															Cerrar Sesión
														</a>
													</div>
												</div>
											</div>
										<?php else : ?>
											<div class="register-sign-in-dropdown-inner">
												<div class="sign-in">
													<p>¿Cliente frecuente?</p>
													<div class="sign-in-action">
														<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="sign-in-button">
															Iniciar Sesión
														</a>
													</div>
												</div>
												<div class="register">
													<p>¿No tienes cuenta?</p>
													<div class="register-action">
														<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
															Registrarse
														</a>
													</div>
												</div>
											</div>
										<?php endif; ?>
									</li>
								</ul>
							</div>
							<div class="header-icon header-icon__cart animate-dropdown dropdown" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php esc_attr_e('Cart', 'kintaelectric'); ?>">
								<a class="dropdown-toggle" href="<?php echo esc_url(wc_get_cart_url()); ?>" data-bs-toggle="dropdown">
									<i class="ec ec-shopping-bag"></i>
									<span class="cart-items-count count header-icon-counter"><?php echo class_exists('WooCommerce') ? WC()->cart->get_cart_contents_count() : '0'; ?></span>
									<span class="cart-items-total-price total-price"><?php echo class_exists('WooCommerce') ? WC()->cart->get_cart_total() : '$0.00'; ?></span>
								</a>
								<ul class="dropdown-menu dropdown-menu-mini-cart border-bottom-0-last-child">
									<li>
										<div class="widget_shopping_cart_content border-bottom-0-last-child">
											<?php
											// Mostrar el contenido del carrito usando la función nativa de WooCommerce
											if (class_exists('WooCommerce')) {
												woocommerce_mini_cart();
											} else {
												echo '<p class="woocommerce-mini-cart__empty-message">No products in the cart.</p>';
											}
											?>
										</div>
									</li>
								</ul>
							</div>
						</div><!-- /.header-icons -->
					</div>
					<div class="electro-navigation row yes-home">
						<div class="departments-menu-v2">
							<div class="dropdown show-dropdown">
								<a href="#" class="departments-menu-v2-title">
									<span><i class="departments-menu-v2-icon fa fa-list-ul"></i>Departamentos</span>
								</a>
								<?php get_template_part( 'template-parts/dynamic-categories-menu' ); ?>
							</div>
						</div>
						<div class="secondary-nav-menu col electro-animate-dropdown position-relative">
							<?php
							wp_nav_menu( array(
								'theme_location' => 'primary',
								'menu_class'     => 'secondary-nav yamm',
								'container'      => false,
								'fallback_cb'    => false,
								'walker'         => new KintaElectric_YAMM_Walker(),
							) );
							?>
						</div>
						<div class="col-auto align-self-center d-none d-xl-block">
							Ilumina tu Hogar con Nuestros Bombillos
						</div>
					</div>
				</div>

				<div class="handheld-header-wrap container hidden-xl-up d-xl-none">
					<div class="handheld-header-v2 row align-items-center handheld-stick-this ">
						<div class="off-canvas-navigation-wrapper ">
							<div class="off-canvas-navbar-toggle-buttons clearfix">
								<button class="navbar-toggler navbar-toggle-hamburger " type="button">
									<i class="ec ec-menu"></i>
								</button>
								<button class="navbar-toggler navbar-toggle-close " type="button">
									<i class="ec ec-close-remove"></i>
								</button>
							</div>

							<div class="off-canvas-navigation light" id="default-oc-header">
								<?php
								wp_nav_menu( array(
									'theme_location' => 'all-departments',
									'menu_class'     => 'nav nav-inline yamm',
									'container'      => false,
									'fallback_cb'    => false,
									'walker'         => new KintaElectric_YAMM_Walker(),
								) );
								?>
							</div>
						</div>
						<div class="header-logo">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-logo-link">
								<?php echo kintaelectric_get_logo( 'mobile' ); ?>
							</a>
						</div>
						<div class="handheld-header-links">
							<ul class="columns-3">
								<li class="search">
									<a href="">Buscar</a>
									<div class="site-search">
										<div class="widget woocommerce widget_product_search">
											<form role="search" method="get" class="woocommerce-product-search"
												action="<?php echo esc_url( home_url( '/' ) ); ?>">
												<label class="screen-reader-text"
													for="woocommerce-product-search-field-0">Buscar:</label>
												<input type="search" id="woocommerce-product-search-field-0"
													class="search-field" placeholder="Buscar productos..." value=""
													name="s">
												<button type="submit" value="Buscar" class="">Buscar</button>
												<input type="hidden" name="post_type" value="product">
											</form>
										</div>
									</div>
								</li>
								<li class="my-account">
									<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><i class="ec ec-user"></i></a>
								</li>
								<li class="cart">
									<a class="footer-cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>"
										title="<?php esc_attr_e('View your shopping cart', 'kintaelectric'); ?>">
										<i class="ec ec-shopping-bag"></i>
										<span class="cart-items-count count"><?php echo class_exists('WooCommerce') ? WC()->cart->get_cart_contents_count() : '0'; ?></span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>

			</header><!-- #masthead -->