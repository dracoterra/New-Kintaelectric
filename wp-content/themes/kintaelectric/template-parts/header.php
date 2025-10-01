<?php
/**
 * Default header template part - Electro Style
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<!-- Top Bar -->
<div class="top-bar hidden-lg-down d-none d-xl-block">
	<div class="container clearfix">
		<ul class="nav nav-inline float-start electro-animate-dropdown flip">
			<li><a href="#"><?php esc_html_e( 'Welcome to Worldwide Electronics Store', 'kintaelectric' ); ?></a></li>
		</ul>
		<ul class="nav nav-inline float-end electro-animate-dropdown flip">
			<li><a href="#"><i class="ec ec-map-pointer"></i> <?php esc_html_e( 'Store Locator', 'kintaelectric' ); ?></a></li>
			<li><a href="#"><i class="ec ec-transport"></i> <?php esc_html_e( 'Track Your Order', 'kintaelectric' ); ?></a></li>
			<li><a href="#"><i class="ec ec-shopping-bag"></i> <?php esc_html_e( 'Shop', 'kintaelectric' ); ?></a></li>
			<li><a href="#"><i class="ec ec-user"></i> <?php esc_html_e( 'My Account', 'kintaelectric' ); ?></a></li>
		</ul>
	</div>
</div>

<header id="masthead" class="header-v2 stick-this site-header">
	<div class="container">
		<div class="header-logo-area d-flex justify-content-between align-items-center">
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
			
			<div class="header-search col-6">
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
			
			<div class="header-icons col-auto d-flex justify-content-end align-items-center">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<!-- Compare -->
					<div class="header-icon">
						<a href="#" class="navbar-compare-count">
							<i class="ec ec-compare"></i>
							<span class="navbar-compare-count count header-icon-counter">0</span>
						</a>
					</div>
					
					<!-- Wishlist -->
					<div class="header-icon">
						<a href="#" class="navbar-wishlist-count">
							<i class="ec ec-favorites"></i>
							<span class="navbar-wishlist-count count header-icon-counter">0</span>
						</a>
					</div>
					
					<!-- User Account -->
					<div class="header-icon header-icon__user-account dropdown animate-dropdown">
						<a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
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
					<div class="header-icon header-icon__cart animate-dropdown dropdown">
						<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="dropdown-toggle" data-bs-toggle="dropdown">
							<i class="ec ec-shopping-bag"></i>
							<span class="cart-items-count count header-icon-counter"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
						</a>
						<div class="dropdown-menu dropdown-menu-right">
							<?php woocommerce_mini_cart(); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Mobile Header -->
		<div class="handheld-header-wrap container hidden-xl-up d-xl-none">
			<div class="handheld-header-v2 row align-items-center handheld-stick-this">
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
<nav class="navbar navbar-expand-lg navbar-light bg-light">
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

<style>
/* Electro Header Styles */
.top-bar {
	background: #f8f9fa;
	border-bottom: 1px solid #e9ecef;
	padding: 8px 0;
	font-size: 13px;
}

.top-bar .nav {
	margin: 0;
}

.top-bar .nav li {
	margin-right: 20px;
}

.top-bar .nav a {
	color: #6c757d;
	text-decoration: none;
	padding: 0;
}

.top-bar .nav a:hover {
	color: #333;
}

.site-header {
	background: #fff;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	position: sticky;
	top: 0;
	z-index: 1000;
}

.header-logo-area {
	padding: 20px 0;
}

.header-logo img {
	max-height: 50px;
	width: auto;
}

.header-search .input-group {
	border-radius: 25px;
	overflow: hidden;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-search .cate-dropdown {
	border: none;
	background: #f8f9fa;
	border-right: 1px solid #e9ecef;
	padding: 12px 15px;
	font-size: 14px;
	min-width: 150px;
}

.header-search .form-control {
	border: none;
	padding: 12px 20px;
	font-size: 14px;
}

.header-search .form-control:focus {
	box-shadow: none;
	border: none;
}

.header-search .btn {
	border: none;
	background: #333;
	color: #fff;
	padding: 12px 20px;
	border-radius: 0;
}

.header-search .btn:hover {
	background: #555;
}

.header-icons {
	gap: 15px;
}

.header-icon {
	position: relative;
}

.header-icon a {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	background: #f8f9fa;
	color: #333;
	text-decoration: none;
	border-radius: 50%;
	transition: all 0.3s ease;
}

.header-icon a:hover {
	background: #333;
	color: #fff;
}

.header-icon-counter {
	position: absolute;
	top: -5px;
	right: -5px;
	background: #ff4444;
	color: #fff;
	font-size: 11px;
	font-weight: 600;
	padding: 2px 6px;
	border-radius: 10px;
	min-width: 18px;
	text-align: center;
	line-height: 1.2;
}

.dropdown-menu {
	border: none;
	box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
	border-radius: 8px;
	padding: 10px 0;
	margin-top: 10px;
}

.dropdown-item {
	padding: 8px 20px;
	font-size: 14px;
	color: #333;
}

.dropdown-item:hover {
	background: #f8f9fa;
	color: #333;
}

/* Mobile Header */
.handheld-header-wrap {
	background: #fff;
	border-bottom: 1px solid #e9ecef;
	padding: 15px 0;
}

.handheld-header-links {
	display: flex;
	justify-content: flex-end;
	align-items: center;
	gap: 15px;
}

.handheld-header-link {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	background: #f8f9fa;
	color: #333;
	text-decoration: none;
	border-radius: 50%;
	transition: all 0.3s ease;
}

.handheld-header-link:hover {
	background: #333;
	color: #fff;
}

.handheld-header-link .cart-items-count {
	position: absolute;
	top: -5px;
	right: -5px;
	background: #ff4444;
	color: #fff;
	font-size: 11px;
	font-weight: 600;
	padding: 2px 6px;
	border-radius: 10px;
	min-width: 18px;
	text-align: center;
}

/* Navigation */
.navbar {
	background: #333 !important;
	padding: 0;
}

.navbar-nav .nav-link {
	color: #fff !important;
	padding: 15px 20px;
	font-weight: 500;
	transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
	background: #555;
	color: #fff !important;
}

.navbar-toggler {
	border: none;
	padding: 5px;
}

.navbar-toggler:focus {
	box-shadow: none;
}

.navbar-toggler-icon {
	background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Responsive */
@media (max-width: 1199px) {
	.header-search {
		display: none;
	}
	
	.header-icons {
		gap: 10px;
	}
}

@media (max-width: 768px) {
	.header-logo-area {
		padding: 15px 0;
	}
	
	.header-icons {
		gap: 8px;
	}
	
	.header-icon a {
		width: 35px;
		height: 35px;
	}
}

/* Electro Icons */
.ec {
	font-family: 'electro' !important;
	speak: none;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	text-transform: none;
	line-height: 1;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
}

.ec-search:before { content: "\e900"; }
.ec-compare:before { content: "\e901"; }
.ec-favorites:before { content: "\e902"; }
.ec-user:before { content: "\e903"; }
.ec-shopping-bag:before { content: "\e904"; }
.ec-menu:before { content: "\e905"; }
.ec-map-pointer:before { content: "\e906"; }
.ec-transport:before { content: "\e907"; }
</style>

<script>
jQuery(document).ready(function($) {
	// Mobile menu toggle
	$('.mobile-menu-toggle').on('click', function(e) {
		e.preventDefault();
		$('#navbarNav').collapse('toggle');
	});
	
	// Bootstrap dropdown initialization
	$('.dropdown-toggle').on('click', function(e) {
		e.preventDefault();
		$(this).next('.dropdown-menu').toggle();
	});
	
	// Close dropdowns when clicking outside
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.dropdown').length) {
			$('.dropdown-menu').hide();
		}
	});
	
	// Cart count update
	function updateCartCount() {
		if (typeof wc_add_to_cart_params !== 'undefined') {
			$.post(wc_add_to_cart_params.ajax_url, {
				action: 'kintaelectric_get_cart_count'
			}, function(response) {
				if (response.success) {
					$('.cart-items-count').text(response.data.count);
				}
			});
		}
	}
	
	// Update cart count on page load
	updateCartCount();
	
	// Update cart count when items are added/removed
	$(document.body).on('added_to_cart removed_from_cart', function() {
		updateCartCount();
	});
});
</script>