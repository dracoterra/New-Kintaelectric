<?php
/**
 * Header Template v3 - Minimalist Electro Style
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<header id="masthead" class="header-v3 stick-this site-header">
	<div class="container">
		<div class="header-content">
			<div class="row align-items-center">
				<div class="col-lg-2">
					<div class="header-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-logo-link">
							<?php
							if ( has_custom_logo() ) {
								the_custom_logo();
							} else {
								?>
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="header-logo-img">
								<?php
							}
							?>
						</a>
					</div>
				</div>
				
				<div class="col-lg-8">
					<div class="header-search">
						<form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<div class="search-wrapper">
								<input type="search" class="form-control" placeholder="<?php echo esc_attr__( 'Search for products...', 'kintaelectric' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
								<button type="submit" class="search-btn">
									<i class="ec ec-search"></i>
								</button>
								<input type="hidden" name="post_type" value="product" />
							</div>
						</form>
					</div>
				</div>
				
				<div class="col-lg-2">
					<div class="header-actions d-flex justify-content-end align-items-center">
						<?php if ( class_exists( 'WooCommerce' ) ) : ?>
							<!-- Shopping Cart -->
							<div class="header-action">
								<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="action-link">
									<i class="ec ec-shopping-bag"></i>
									<span class="action-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
								</a>
							</div>
							
							<!-- User Account -->
							<div class="header-action">
								<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="action-link">
									<i class="ec ec-user"></i>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Navigation -->
		<nav class="main-navigation">
			<div class="nav-wrapper">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNav">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'primary-menu',
							'container'      => false,
							'menu_class'     => 'navbar-nav me-auto',
							'fallback_cb'    => 'wp_page_menu',
						)
					);
					?>
				</div>
			</div>
		</nav>
	</div>
</header>

