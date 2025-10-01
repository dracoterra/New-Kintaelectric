<?php
/**
 * Header Template v4 - Top Bar + Header Style
 * Dynamic version based on header-v1.php, header-v2.php and header-v3.php implementation
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="top-bar top-bar-v1 hidden-lg-down d-none d-xl-block">
	<div class="container clearfix">
		<ul id="menu-top-bar-left" class="nav nav-inline float-start electro-animate-dropdown flip">
			<li id="menu-item-5166"
				class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5166"><a
					title="Welcome to Worldwide Electronics Store" href="#">Welcome to Worldwide Electronics
					Store</a></li>
		</ul>
		<ul id="menu-top-bar-right" class="nav nav-inline float-end electro-animate-dropdown flip">
			<li id="menu-item-5167"
				class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5167"><a
					title="Store Locator" href="#"><i class="ec ec-map-pointer"></i>Store Locator</a></li>
			<li id="menu-item-5299"
				class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5299"><a
					title="Track Your Order" href="../track-your-order/index.htm"><i
						class="ec ec-transport"></i>Track Your Order</a></li>
			<li id="menu-item-5293"
				class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5293"><a
					title="Shop" href="../shop/index.htm"><i class="ec ec-shopping-bag"></i>Shop</a></li>
			<li id="menu-item-5294"
				class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5294"><a
					title="My Account" href="../my-account/index.htm"><i class="ec ec-user"></i>My
					Account</a></li>
		</ul>
	</div>
</div><!-- /.top-bar -->



<header id="masthead" class="site-header stick-this header-v6">
	<div class="container hidden-lg-down d-none d-xl-block">
		<div class="masthead row align-items-center">
			<div class="header-logo-area d-flex justify-content-between align-items-center">
				<div class="header-site-branding">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-logo-link">
						<?php echo kintaelectric_get_logo( 'light' ); ?>
					</a>
				</div>
				<div class="departments-menu-v2">
					<div class="dropdown">
						<a href="#" class="departments-menu-v2-title" data-bs-toggle="dropdown">
							<span><?php esc_html_e( 'Categories', 'kintaelectric' ); ?><i
									class="departments-menu-v2-icon ec ec-arrow-down-search"></i></span>
						</a>
						<ul id="menu-all-departments-menu" class="dropdown-menu yamm">
							<?php
							// All Departments Menu - using WordPress menu system
							wp_nav_menu( array(
								'theme_location' => 'all-departments',
								'menu_class'     => 'nav nav-inline yamm',
								'container'      => false,
								'fallback_cb'    => 'kintaelectric_all_departments_fallback',
								'depth'          => 3,
								'walker'         => new KintaElectric_YAMM_Walker(),
							) );
							?>
																	</ul>
																</div>
															</div>
														</div>

			<form class="navbar-search col" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
				autocomplete="off">
				<label class="sr-only screen-reader-text visually-hidden" for="search"><?php esc_html_e( 'Search for:', 'kintaelectric' ); ?></label>
				<div class="input-group">
					<div class="input-search-field">
						<input type="text" id="search"
							class="form-control search-field product-search-field" dir="ltr" value=""
							name="s" placeholder="<?php esc_attr_e( 'Search for Products', 'kintaelectric' ); ?>" autocomplete="off">
					</div>
					<div class="input-group-addon search-categories d-flex">
						<select name='product_cat' id='electro_header_search_categories_dropdown'
							class='postform resizeselect'>
							<option value='0' selected='selected'><?php esc_html_e( 'All Categories', 'kintaelectric' ); ?></option>
							<?php echo kintaelectric_get_product_categories(); ?>
						</select>
					</div>
					<div class="input-group-btn">
						<input type="hidden" id="search-param" name="post_type" value="product">
						<button type="submit" class="btn btn-secondary"><i
								class="ec ec-search"></i></button>
					</div>
				</div>
			</form>
			<div class="header-icons col-auto d-flex justify-content-end align-items-center">
				<?php if ( class_exists( 'YITH_Woocompare' ) ) : ?>
				<div style="position: relative;" class="header-icon">
					<a href="<?php echo esc_url( add_query_arg( 'action', 'yith-woocompare-view-table', home_url( '/' ) ) ); ?>"
						class="yith-woocompare-open">
						<i class="ec ec-compare"></i>
						<span id="navbar-compare-count"
							class="navbar-compare-count count header-icon-counter">0</span>
					</a>
				</div>
				<?php endif; ?>
				
				<div class="header-icon">
					<?php
					// Verificar si YITH Wishlist está activo
					if (class_exists('YITH_WCWL') && function_exists('yith_wcwl_count_products')) {
						$wishlist_url = YITH_WCWL()->get_wishlist_url();
						$wishlist_count = yith_wcwl_count_products();
						?>
						<a href="<?php echo esc_url($wishlist_url); ?>" 
						   class="yith-wcwl-wishlist-link" 
						   id="header-wishlist-link">
							<i class="ec ec-favorites"></i>
							<?php if ($wishlist_count > 0): ?>
								<span class="header-icon-counter" id="header-wishlist-count">
									<?php echo esc_html($wishlist_count); ?>
								</span>
							<?php else: ?>
								<span class="header-icon-counter" id="header-wishlist-count" style="display: none;">
									0
								</span>
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
				
				<div class="header-icon header-icon__user-account dropdown animate-dropdown">
					<a class="dropdown-toggle" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" data-bs-toggle="dropdown"><i
							class="ec ec-user"></i></a>
					<ul class="dropdown-menu dropdown-menu-user-account">
						<li>
							<div class="register-sign-in-dropdown-inner">
								<div class="sign-in">
									<p><?php esc_html_e( 'Returning Customer ?', 'kintaelectric' ); ?></p>
									<div class="sign-in-action"><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
											class="sign-in-button"><?php esc_html_e( 'Sign in', 'kintaelectric' ); ?></a></div>
								</div>
								<div class="register">
									<p><?php esc_html_e( 'Don\'t have an account ?', 'kintaelectric' ); ?></p>
									<div class="register-action"><a
											href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Register', 'kintaelectric' ); ?></a></div>
								</div>
							</div>
						</li>
					</ul>
				</div>
				
				<div class="header-icon header-icon__cart animate-dropdown dropdown">
					<a class="dropdown-toggle" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-bs-toggle="dropdown">
						<i class="ec ec-shopping-bag"></i>
						<span class="cart-items-count count header-icon-counter"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
						<span class="cart-items-total-price total-price"><?php echo WC()->cart->get_cart_total(); ?></span>
					</a>
					<ul class="dropdown-menu dropdown-menu-mini-cart border-bottom-0-last-child">
						<li>
							<div class="widget_shopping_cart_content border-bottom-0-last-child">
								<?php woocommerce_mini_cart(); ?>
							</div>
						</li>
					</ul>
				</div>
			</div><!-- /.header-icons -->
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
					<ul id="menu-all-departments-menu-1" class="nav nav-inline yamm">
						<?php
						// All Departments Menu - using WordPress menu system
						wp_nav_menu( array(
							'theme_location' => 'all-departments',
							'menu_class'     => 'nav nav-inline yamm',
							'container'      => false,
							'fallback_cb'    => 'kintaelectric_all_departments_fallback',
							'depth'          => 3,
							'walker'         => new KintaElectric_YAMM_Walker(),
						) );
						?>
					</ul>
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
						<a href=""><?php esc_html_e( 'Search', 'kintaelectric' ); ?></a>
						<div class="site-search">
							<div class="widget woocommerce widget_product_search">
								<form role="search" method="get" class="woocommerce-product-search"
									action="<?php echo esc_url( home_url( '/' ) ); ?>">
									<label class="screen-reader-text"
										for="woocommerce-product-search-field-0"><?php esc_html_e( 'Search for:', 'kintaelectric' ); ?></label>
									<input type="search" id="woocommerce-product-search-field-0"
										class="search-field" placeholder="<?php esc_attr_e( 'Search products…', 'kintaelectric' ); ?>" value=""
										name="s">
									<button type="submit" value="<?php esc_attr_e( 'Search', 'kintaelectric' ); ?>" class=""><?php esc_html_e( 'Search', 'kintaelectric' ); ?></button>
									<input type="hidden" name="post_type" value="product">
								</form>
							</div>
						</div>
					</li>
					<li class="my-account">
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><i class="ec ec-user"></i></a>
					</li>
					<li class="cart">
						<a class="footer-cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>"
							title="<?php esc_attr_e( 'View your shopping cart', 'kintaelectric' ); ?>">
							<i class="ec ec-shopping-bag"></i>
							<span class="cart-items-count count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>

</header><!-- #masthead -->