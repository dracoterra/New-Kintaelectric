<?php
/**
 * Header Template v2 - Modern Electro Style
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<header id="masthead" class="header-v2 stick-this site-header">
	<div class="container">
		<div class="header-top">
			<div class="row align-items-center">
				<div class="col-lg-3">
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
				
				<div class="col-lg-6">
					<div class="header-search">
						<form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<div class="input-group">
								<select class="form-control cate-dropdown" name="product_cat">
									<option value=""><?php esc_html_e( 'All Categories', 'kintaelectric' ); ?></option>
									<?php
									if ( class_exists( 'WooCommerce' ) ) {
										$categories = get_terms( array(
											'taxonomy' => 'product_cat',
											'hide_empty' => true,
										) );
										foreach ( $categories as $category ) {
											echo '<option value="' . esc_attr( $category->slug ) . '">' . esc_html( $category->name ) . '</option>';
										}
									}
									?>
								</select>
								<input type="search" class="form-control" placeholder="<?php echo esc_attr__( 'Search for products...', 'kintaelectric' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
								<button type="submit" class="btn btn-primary">
									<i class="ec ec-search"></i>
								</button>
								<input type="hidden" name="post_type" value="product" />
							</div>
						</form>
					</div>
				</div>
				
				<div class="col-lg-3">
					<div class="header-actions d-flex justify-content-end align-items-center">
						<?php if ( class_exists( 'WooCommerce' ) ) : ?>
							<!-- Compare -->
							<div class="header-action">
								<a href="#" class="action-link" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php esc_attr_e( 'Compare', 'kintaelectric' ); ?>">
									<i class="ec ec-compare"></i>
									<span class="action-count">0</span>
								</a>
							</div>
							
							<!-- Wishlist -->
							<div class="header-action">
								<a href="#" class="action-link" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php esc_attr_e( 'Wishlist', 'kintaelectric' ); ?>">
									<i class="ec ec-favorites"></i>
									<span class="action-count">0</span>
								</a>
							</div>
							
							<!-- User Account -->
							<div class="header-action dropdown">
								<a href="#" class="action-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php esc_attr_e( 'My Account', 'kintaelectric' ); ?>">
									<i class="ec ec-user"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<?php if ( is_user_logged_in() ) : ?>
										<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="dropdown-item"><?php esc_html_e( 'My Account', 'kintaelectric' ); ?></a>
										<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="dropdown-item"><?php esc_html_e( 'Logout', 'kintaelectric' ); ?></a>
									<?php else : ?>
										<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="dropdown-item"><?php esc_html_e( 'Login', 'kintaelectric' ); ?></a>
										<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="dropdown-item"><?php esc_html_e( 'Register', 'kintaelectric' ); ?></a>
									<?php endif; ?>
								</div>
							</div>
							
							<!-- Shopping Cart -->
							<div class="header-action dropdown">
								<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="action-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php esc_attr_e( 'Cart', 'kintaelectric' ); ?>">
									<i class="ec ec-shopping-bag"></i>
									<span class="action-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<?php woocommerce_mini_cart(); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Mobile Header -->
		<div class="handheld-header-wrap hidden-xl-up d-xl-none">
			<div class="handheld-header row align-items-center">
				<div class="col-4">
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
				<div class="col-8">
					<div class="handheld-header-links">
						<?php if ( class_exists( 'WooCommerce' ) ) : ?>
							<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="handheld-header-link">
								<i class="ec ec-shopping-bag"></i>
								<span class="cart-items-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
							</a>
						<?php endif; ?>
						<a href="#" class="handheld-header-link mobile-menu-toggle">
							<i class="ec ec-menu"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container">
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

