<?php
/**
 * Footer Template v1 - Complete Electro Style
 * Recreated from original Electro theme with ALL structures
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<footer id="colophon" class="site-footer footer-v2">
	<div class="desktop-footer d-none d-lg-block container">
		<div class="footer-widgets row row-cols-lg-2 row-cols-xl-4">
			<!-- Footer Widget 1 -->
			<div class="widget-column col mb-lg-5 mb-xl-0">
				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'footer-1' ); ?>
				<?php else : ?>
					<!-- Empty widget area - configure in Appearance > Widgets -->
					<div class="empty-widget-area">
						<p class="text-muted"><?php esc_html_e( 'Configure a widget in Appearance > Widgets > Top Footer 1', 'kintaelectric' ); ?></p>
						<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" class="widget-edit-link" title="<?php esc_attr_e( 'Configure Top Footer 1 Widget', 'kintaelectric' ); ?>">
								<span class="dashicons dashicons-edit"></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Footer Widget 2 -->
			<div class="widget-column col mb-lg-5 mb-xl-0">
				<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
					<?php dynamic_sidebar( 'footer-2' ); ?>
				<?php else : ?>
					<!-- Empty widget area - configure in Appearance > Widgets -->
					<div class="empty-widget-area">
						<p class="text-muted"><?php esc_html_e( 'Configure a widget in Appearance > Widgets > Top Footer 2', 'kintaelectric' ); ?></p>
						<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" class="widget-edit-link" title="<?php esc_attr_e( 'Configure Top Footer 2 Widget', 'kintaelectric' ); ?>">
								<span class="dashicons dashicons-edit"></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Footer Widget 3 -->
			<div class="widget-column col mb-lg-5 mb-xl-0">
				<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
					<?php dynamic_sidebar( 'footer-3' ); ?>
				<?php else : ?>
					<!-- Empty widget area - configure in Appearance > Widgets -->
					<div class="empty-widget-area">
						<p class="text-muted"><?php esc_html_e( 'Configure a widget in Appearance > Widgets > Top Footer 3', 'kintaelectric' ); ?></p>
						<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" class="widget-edit-link" title="<?php esc_attr_e( 'Configure Top Footer 3 Widget', 'kintaelectric' ); ?>">
								<span class="dashicons dashicons-edit"></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Footer Widget 4 -->
			<div class="widget-column col mb-lg-5 mb-xl-0">
				<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
					<?php dynamic_sidebar( 'footer-4' ); ?>
				<?php else : ?>
					<!-- Empty widget area - configure in Appearance > Widgets -->
					<div class="empty-widget-area">
						<p class="text-muted"><?php esc_html_e( 'Configure a widget in Appearance > Widgets > Top Footer 4', 'kintaelectric' ); ?></p>
						<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" class="widget-edit-link" title="<?php esc_attr_e( 'Configure Top Footer 4 Widget', 'kintaelectric' ); ?>">
								<span class="dashicons dashicons-edit"></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<!-- Newsletter Section - Widget -->
		<?php if ( is_active_sidebar( 'footer-newsletter' ) ) : ?>
			<?php dynamic_sidebar( 'footer-newsletter' ); ?>
		<?php else : ?>
			<!-- Fallback Newsletter Section -->
			<div class="footer-newsletter">
				<div class="container">
					<div class="footer-newsletter-inner row">
						<div class="newsletter-content col-lg-7">
							<h5 class="newsletter-title"><?php esc_html_e( 'Sign up to Newsletter', 'kintaelectric' ); ?></h5>
							<span class="newsletter-marketing-text">
								<?php esc_html_e( '...and receive', 'kintaelectric' ); ?> <strong><?php esc_html_e( '$20 coupon for first shopping', 'kintaelectric' ); ?></strong>
							</span>
						</div>
						<div class="newsletter-form col-lg-5 align-self-center">
							<form class="newsletter-signup-form" method="post" action="#">
								<div class="input-group">
									<input type="email" class="form-control" placeholder="<?php esc_attr_e( 'Enter your email address', 'kintaelectric' ); ?>" required>
									<button type="submit" class="btn btn-primary">
										<?php esc_html_e( 'Subscribe', 'kintaelectric' ); ?>
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="footer-bottom-widgets">
			<div class="container">
				<div class="footer-bottom-widgets-inner row">
					<div class="footer-contact col-md-5">
						<div class="footer-logo">
							<svg version="1.1" x="0px" y="0px" width="156px" height="37px"
								viewbox="0 0 175.748 42.52" enable-background="new 0 0 175.748 42.52">
								<ellipse fill-rule="evenodd" clip-rule="evenodd" fill="#FDD700" cx="170.05"
									cy="36.341" rx="5.32" ry="5.367"></ellipse>
								<path fill-rule="evenodd" clip-rule="evenodd" fill="#333E48"
									d="M30.514,0.71c-0.034,0.003-0.066,0.008-0.056,0.056
							C30.263,0.995,29.876,1.181,29.79,1.5c-0.148,0.548,0,1.568,0,2.427v36.459c0.265,0.221,0.506,0.465,0.725,0.734h6.187
							c0.2-0.25,0.423-0.477,0.669-0.678V1.387C37.124,1.185,36.9,0.959,36.701,0.71H30.514z M117.517,12.731
							c-0.232-0.189-0.439-0.64-0.781-0.734c-0.754-0.209-2.039,0-3.121,0h-3.176V4.435c-0.232-0.189-0.439-0.639-0.781-0.733
							c-0.719-0.2-1.969,0-3.01,0h-3.01c-0.238,0.273-0.625,0.431-0.725,0.847c-0.203,0.852,0,2.399,0,3.725
							c0,1.393,0.045,2.748-0.055,3.725h-6.41c-0.184,0.237-0.629,0.434-0.725,0.791c-0.178,0.654,0,1.813,0,2.765v2.766
							c0.232,0.188,0.439,0.64,0.779,0.733c0.777,0.216,2.109,0,3.234,0c1.154,0,2.291-0.045,3.176,0.057v21.277
							c0.232,0.189,0.439,0.639,0.781,0.734c0.719,0.199,1.969,0,3.01,0h3.01c1.008-0.451,0.725-1.889,0.725-3.443
							c-0.002-6.164-0.047-12.867,0.055-18.625h6.299c0.182-0.236,0.627-0.434,0.725-0.79c0.176-0.653,0-1.813,0-2.765V12.731z
								M135.851,18.262c0.201-0.746,0-2.029,0-3.104v-3.104c-0.287-0.245-0.434-0.637-0.781-0.733c-0.824-0.229-1.992-0.044-2.898,0
							c-2.158,0.104-4.506,0.675-5.74,1.411c-0.146-0.362-0.451-0.853-0.893-0.96c-0.693-0.169-1.859,0-2.842,0h-2.842
							c-0.258,0.319-0.625,0.42-0.725,0.79c-0.223,0.82,0,2.338,0,3.443c0,8.109-0.002,16.635,0,24.381
							c0.232,0.189,0.439,0.639,0.779,0.734c0.707,0.195,1.93,0,2.955,0h3.01c0.918-0.463,0.725-1.352,0.725-2.822V36.21
							c-0.002-3.902-0.242-9.117,0-12.473c0.297-4.142,3.836-4.877,8.527-4.686C135.312,18.816,135.757,18.606,135.851,18.262z
								M14.796,11.376c-5.472,0.262-9.443,3.178-11.76,7.056c-2.435,4.075-2.789,10.62-0.501,15.126c2.043,4.023,5.91,7.115,10.701,7.9
							c6.051,0.992,10.992-1.219,14.324-3.838c-0.687-1.1-1.419-2.664-2.118-3.951c-0.398-0.734-0.652-1.486-1.616-1.467
							c-1.942,0.787-4.272,2.262-7.134,2.145c-3.791-0.154-6.659-1.842-7.524-4.91h19.452c0.146-2.793,0.22-5.338-0.279-7.563
							C26.961,15.728,22.503,11.008,14.796,11.376z M9,23.284c0.921-2.508,3.033-4.514,6.298-4.627c3.083-0.107,4.994,1.976,5.685,4.627
							C17.119,23.38,12.865,23.38,9,23.284z M52.418,11.376c-5.551,0.266-9.395,3.142-11.76,7.056
							c-2.476,4.097-2.829,10.493-0.557,15.069c1.997,4.021,5.895,7.156,10.646,7.957c6.068,1.023,11-1.227,14.379-3.781
							c-0.479-0.896-0.875-1.742-1.393-2.709c-0.312-0.582-1.024-2.234-1.561-2.539c-0.912-0.52-1.428,0.135-2.23,0.508
							c-0.564,0.262-1.223,0.523-1.672,0.676c-4.768,1.621-10.372,0.268-11.537-4.176h19.451c0.668-5.443-0.419-9.953-2.73-13.037
							C61.197,13.388,57.774,11.12,52.418,11.376z M46.622,23.343c0.708-2.553,3.161-4.578,6.242-4.686
							c3.08-0.107,5.08,1.953,5.686,4.686H46.622z M160.371,15.497c-2.455-2.453-6.143-4.291-10.869-4.064
							c-2.268,0.109-4.297,0.65-6.02,1.524c-1.719,0.873-3.092,1.957-4.234,3.217c-2.287,2.519-4.164,6.004-3.902,11.007
							c0.248,4.736,1.979,7.813,4.627,10.326c2.568,2.439,6.148,4.254,10.867,4.064c4.457-0.18,7.889-2.115,10.199-4.684
							c2.469-2.746,4.012-5.971,3.959-11.063C164.949,21.134,162.732,17.854,160.371,15.497z M149.558,33.952
							c-3.246-0.221-5.701-2.615-6.41-5.418c-0.174-0.689-0.26-1.25-0.4-2.166c-0.035-0.234,0.072-0.523-0.045-0.77
							c0.682-3.698,2.912-6.257,6.799-6.547c2.543-0.189,4.258,0.735,5.52,1.863c1.322,1.182,2.303,2.715,2.451,4.967
							C157.789,30.669,154.185,34.267,149.558,33.952z M88.812,29.55c-1.232,2.363-2.9,4.307-6.13,4.402
							c-4.729,0.141-8.038-3.16-8.025-7.563c0.004-1.412,0.324-2.65,0.947-3.726c1.197-2.061,3.507-3.688,6.633-3.612
							c3.222,0.079,4.966,1.708,6.632,3.668c1.328-1.059,2.529-1.948,3.9-2.99c0.416-0.315,1.076-0.688,1.227-1.072
							c0.404-1.031-0.365-1.502-0.891-2.088c-2.543-2.835-6.66-5.377-11.704-5.137c-6.02,0.288-10.218,3.697-12.484,7.846
							c-1.293,2.365-1.951,5.158-1.729,8.408c0.209,3.053,1.191,5.496,2.619,7.508c2.842,4.004,7.385,6.973,13.656,6.377
							c5.976-0.568,9.574-3.936,11.816-8.354c-0.141-0.271-0.221-0.604-0.336-0.902C92.929,31.364,90.843,30.485,88.812,29.55z"></path>
							</svg>
						</div>

						<div class="footer-call-us">
							<div class="media d-flex">
								<span class="media-left call-us-icon media-middle"><i
										class="ec ec-support"></i></span>
								<div class="media-body">
									<span class="call-us-text">Got Questions ? Call us 24/7!</span>
									<span class="call-us-number">(800) 8001-8588, (0600) 874 548</span>
								</div>
							</div>
						</div>


						<div class="footer-address">
							<strong class="footer-address-title">Contact Info</strong>
							<address>17 Princess Road, London, Greater London NW1 8JR, UK</address>
						</div>

						<div class="footer-social-icons">
							<ul class="social-icons list-unstyled nav align-items-center">
								<li><a class="fab fa-facebook" target="_blank"
										href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
								<li><a class="fab fa-whatsapp mobile" target="_blank"
										href="whatsapp://send?phone=919876543210"></a></li>
								<li><a class="fab fa-whatsapp desktop" target="_blank"
										href="https://web.whatsapp.com/send?phone=919876543210"></a></li>
								<li><a class="fab fa-pinterest" target="_blank"
										href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
								<li><a class="fab fa-linkedin" target="_blank"
										href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
								<li><a class="fab fa-instagram" target="_blank"
										href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
								<li><a class="fab fa-youtube" target="_blank"
										href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
								<li><a class="fas fa-rss" target="_blank" href="feed/index.htm"></a></li>
							</ul>
						</div>
					</div>
					<div class="footer-bottom-widgets-menu col-md">
						<div class="footer-bottom-widgets-menu-inner row g-0 row-cols-xl-3">
							<div class="columns col">
								<aside id="nav_menu-1" class="widget clearfix widget_nav_menu">
									<div class="body">
										<h4 class="widget-title">Find It Fast</h4>
										<div class="menu-footer-menu-1-container">
											<ul id="menu-footer-menu-1" class="menu">
												<li id="menu-item-5281"
													class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-5281">
													<a
														href="product-category/laptops-computers/index-4.htm">Laptops
														&amp; Computers</a></li>
												<li id="menu-item-5282"
													class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-5282">
													<a
														href="product-category/cameras-photography/index.htm">Cameras
														&amp; Photography</a></li>
												<li id="menu-item-5283"
													class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-5283">
													<a
														href="product-category/smart-phones-tablets/index.htm">Smart
														Phones &amp; Tablets</a></li>
												<li id="menu-item-5284"
													class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-5284">
													<a
														href="product-category/video-games-consoles/index.htm">Video
														Games &amp; Consoles</a></li>
												<li id="menu-item-5285"
													class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-5285">
													<a href="product-category/tv-audio/index.htm">TV &amp;
														Audio</a></li>
												<li id="menu-item-5286"
													class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-5286">
													<a href="product-category/gadgets/index.htm">Gadgets</a>
												</li>
												<li id="menu-item-5287"
													class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-5287">
													<a
														href="product-category/accessories/headphones/waterproof-headphones/index.htm">Waterproof
														Headphones</a></li>
											</ul>
										</div>
									</div>
								</aside>
							</div>
							<div class="columns col">
								<aside id="nav_menu-2" class="widget clearfix widget_nav_menu">
									<div class="body">
										<h4 class="widget-title">�</h4>
										<div class="menu-footer-menu-2-container">
											<ul id="menu-footer-menu-2" class="menu">
												<li id="menu-item-5399"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5399">
													<a href="about/index.htm">About</a></li>
												<li id="menu-item-5397"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5397">
													<a href="contact-v1/index.htm">Contact</a></li>
												<li id="menu-item-5414"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5414">
													<a href="wishlist/index.htm">Wishlist</a></li>
												<li id="menu-item-5415"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5415">
													<a href="compare/index.htm">Compare</a></li>
												<li id="menu-item-5398"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5398">
													<a href="faq/index.htm">FAQ</a></li>
												<li id="menu-item-5416"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5416">
													<a href="store-directory/index.htm">Store Directory</a>
												</li>
											</ul>
										</div>
									</div>
								</aside>
							</div>
							<div class="columns col">
								<aside id="nav_menu-3" class="widget clearfix widget_nav_menu">
									<div class="body">
										<h4 class="widget-title">Customer Care</h4>
										<div class="menu-footer-menu-3-container">
											<ul id="menu-footer-menu-3" class="menu">
												<li id="menu-item-5303"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5303">
													<a href="my-account/index.htm">My Account</a></li>
												<li id="menu-item-5304"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5304">
													<a href="track-your-order/index.htm">Track your
														Order</a></li>
												<li id="menu-item-5305"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5305">
													<a href="contact-v1/index.htm">Customer Service</a></li>
												<li id="menu-item-5306"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5306">
													<a href="contact-v2/index.htm">Returns/Exchange</a></li>
												<li id="menu-item-5307"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5307">
													<a href="faq/index.htm">FAQs</a></li>
												<li id="menu-item-5308"
													class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5308">
													<a href="terms-and-conditions/index.htm">Product
														Support</a></li>
											</ul>
										</div>
									</div>
								</aside>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="copyright-bar">
			<div class="container">
				<div class="float-start copyright">&copy; <a
						href="https://demo2.madrasthemes.com/electro/">Electro</a> - All Rights Reserved
				</div>
				<div class="float-end payment">
					<div class="footer-payment-logo">
						<ul class="nav cash-card card-inline">
							<li class="card-item"><img loading="lazy" class="h-auto"
									src="../../uploads/2021/03/patment-icon1.png" alt="" width="324"
									height="38"></li>
						</ul>
					</div><!-- /.payment-methods -->
				</div>
			</div>
		</div>
	</div>
	<div class="handheld-footer d-lg-none pt-3 v1 ">
		<div class="handheld-widget-menu container">
			<div class="columns">
				<aside id="nav_menu-4" class="widget widget_nav_menu">
					<div class="body">
						<h4 class="widget-title">We Recommend</h4>
						<div class="menu-mobile-footer-menu-1-container">
							<ul id="menu-mobile-footer-menu-1" class="menu">
								<li id="menu-item-5400"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-2139 current_page_item menu-item-5400">
									<a href="index.htm" aria-current="page">Home v1</a></li>
								<li id="menu-item-5401"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5401">
									<a href="home-v2/index.htm">Home v2</a></li>
								<li id="menu-item-5402"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5402">
									<a href="home-v3/index.htm">Home v3</a></li>
								<li id="menu-item-5403"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5403">
									<a href="home-v3-full-color-background/index.htm">Home v3.1</a></li>
								<li id="menu-item-5404"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5404">
									<a href="home-v4/index.htm">Home v4</a></li>
								<li id="menu-item-5405"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5405">
									<a href="home-v5/index.htm">Home v5</a></li>
								<li id="menu-item-5433"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5433">
									<a href="home-v6/index.htm">Home v6</a></li>
								<li id="menu-item-5432"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5432">
									<a href="home-v7/index.htm">Home v7</a></li>
								<li id="menu-item-6021"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6021">
									<a href="home-v8/index.htm">Home v8</a></li>
								<li id="menu-item-6022"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6022">
									<a href="home-v9/index.htm">Home v9</a></li>
							</ul>
						</div>
					</div>
				</aside>
			</div>
			<div class="columns">
				<aside id="nav_menu-5" class="widget widget_nav_menu">
					<div class="body">
						<h4 class="widget-title">My Account</h4>
						<div class="menu-mobile-footer-menu-2-container">
							<ul id="menu-mobile-footer-menu-2" class="menu">
								<li id="menu-item-5406"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5406">
									<a href="about/index.htm">About</a></li>
								<li id="menu-item-5407"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5407">
									<a href="contact-v1/index.htm">Contact v1</a></li>
								<li id="menu-item-5408"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5408">
									<a href="contact-v2/index.htm">Contact v2</a></li>
								<li id="menu-item-5409"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5409">
									<a href="faq/index.htm">FAQ</a></li>
							</ul>
						</div>
					</div>
				</aside>
			</div>
			<div class="columns">
				<aside id="nav_menu-6" class="widget widget_nav_menu">
					<div class="body">
						<h4 class="widget-title">Customer Care</h4>
						<div class="menu-mobile-footer-menu-3-container">
							<ul id="menu-mobile-footer-menu-3" class="menu">
								<li id="menu-item-5393"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5393">
									<a href="my-account/index.htm">My Account</a></li>
								<li id="menu-item-5394"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5394">
									<a href="track-your-order/index.htm">Track your Order</a></li>
								<li id="menu-item-5391"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5391">
									<a href="contact-v1/index.htm">Customer Service</a></li>
								<li id="menu-item-5392"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5392">
									<a href="contact-v2/index.htm">Returns/Exchange</a></li>
								<li id="menu-item-5395"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5395">
									<a href="faq/index.htm">FAQs</a></li>
								<li id="menu-item-5396"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5396">
									<a href="terms-and-conditions/index.htm">Product Support</a></li>
							</ul>
						</div>
					</div>
				</aside>
			</div>
			<div class="columns">
				<aside id="nav_menu-7" class="widget widget_nav_menu">
					<div class="body">
						<h4 class="widget-title">About Us</h4>
						<div class="menu-mobile-footer-menu-4-container">
							<ul id="menu-mobile-footer-menu-4" class="menu">
								<li id="menu-item-5410"
									class="menu-item menu-item-type-post_type menu-item-object-post menu-item-5410">
									<a
										href="blog/2016/03/04/robot-wars-now-closed-post-with-gallery/index.htm">Blog
										Single</a></li>
								<li id="menu-item-5413"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5413">
									<a href="store-directory/index.htm">Store Directory</a></li>
								<li id="menu-item-5411"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5411">
									<a href="wishlist/index.htm">Wishlist</a></li>
								<li id="menu-item-5412"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5412">
									<a href="compare/index.htm">Compare</a></li>
							</ul>
						</div>
					</div>
				</aside>
			</div>
		</div>
		<div class="footer-social-icons container text-center mb-0">
			<ul
				class="social-icons-color nav align-items-center row list-unstyled justify-content-center mb-0">
				<li><a class="fab fa-facebook" target="_blank"
						href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
				<li><a class="fab fa-whatsapp mobile" target="_blank"
						href="whatsapp://send?phone=919876543210"></a></li>
				<li><a class="fab fa-whatsapp desktop" target="_blank"
						href="https://web.whatsapp.com/send?phone=919876543210"></a></li>
				<li><a class="fab fa-pinterest" target="_blank"
						href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
				<li><a class="fab fa-linkedin" target="_blank"
						href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
				<li><a class="fab fa-instagram" target="_blank"
						href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
				<li><a class="fab fa-youtube" target="_blank"
						href="http://themeforest.net/user/madrasthemes/portfolio"></a></li>
				<li><a class="fas fa-rss" target="_blank" href="feed/index.htm"></a></li>
			</ul>
		</div>
		<div class="handheld-footer-bar">
			<div class="handheld-footer-bar-inner">
				<div class="footer-logo">
					<svg version="1.1" x="0px" y="0px" width="156px" height="37px"
						viewbox="0 0 175.748 42.52" enable-background="new 0 0 175.748 42.52">
						<ellipse fill-rule="evenodd" clip-rule="evenodd" fill="#FDD700" cx="170.05"
							cy="36.341" rx="5.32" ry="5.367"></ellipse>
						<path fill-rule="evenodd" clip-rule="evenodd" fill="#333E48"
							d="M30.514,0.71c-0.034,0.003-0.066,0.008-0.056,0.056
			C30.263,0.995,29.876,1.181,29.79,1.5c-0.148,0.548,0,1.568,0,2.427v36.459c0.265,0.221,0.506,0.465,0.725,0.734h6.187
			c0.2-0.25,0.423-0.477,0.669-0.678V1.387C37.124,1.185,36.9,0.959,36.701,0.71H30.514z M117.517,12.731
			c-0.232-0.189-0.439-0.64-0.781-0.734c-0.754-0.209-2.039,0-3.121,0h-3.176V4.435c-0.232-0.189-0.439-0.639-0.781-0.733
			c-0.719-0.2-1.969,0-3.01,0h-3.01c-0.238,0.273-0.625,0.431-0.725,0.847c-0.203,0.852,0,2.399,0,3.725
			c0,1.393,0.045,2.748-0.055,3.725h-6.41c-0.184,0.237-0.629,0.434-0.725,0.791c-0.178,0.654,0,1.813,0,2.765v2.766
			c0.232,0.188,0.439,0.64,0.779,0.733c0.777,0.216,2.109,0,3.234,0c1.154,0,2.291-0.045,3.176,0.057v21.277
			c0.232,0.189,0.439,0.639,0.781,0.734c0.719,0.199,1.969,0,3.01,0h3.01c1.008-0.451,0.725-1.889,0.725-3.443
			c-0.002-6.164-0.047-12.867,0.055-18.625h6.299c0.182-0.236,0.627-0.434,0.725-0.79c0.176-0.653,0-1.813,0-2.765V12.731z
				M135.851,18.262c0.201-0.746,0-2.029,0-3.104v-3.104c-0.287-0.245-0.434-0.637-0.781-0.733c-0.824-0.229-1.992-0.044-2.898,0
			c-2.158,0.104-4.506,0.675-5.74,1.411c-0.146-0.362-0.451-0.853-0.893-0.96c-0.693-0.169-1.859,0-2.842,0h-2.842
			c-0.258,0.319-0.625,0.42-0.725,0.79c-0.223,0.82,0,2.338,0,3.443c0,8.109-0.002,16.635,0,24.381
			c0.232,0.189,0.439,0.639,0.779,0.734c0.707,0.195,1.93,0,2.955,0h3.01c0.918-0.463,0.725-1.352,0.725-2.822V36.21
			c-0.002-3.902-0.242-9.117,0-12.473c0.297-4.142,3.836-4.877,8.527-4.686C135.312,18.816,135.757,18.606,135.851,18.262z
				M14.796,11.376c-5.472,0.262-9.443,3.178-11.76,7.056c-2.435,4.075-2.789,10.62-0.501,15.126c2.043,4.023,5.91,7.115,10.701,7.9
			c6.051,0.992,10.992-1.219,14.324-3.838c-0.687-1.1-1.419-2.664-2.118-3.951c-0.398-0.734-0.652-1.486-1.616-1.467
			c-1.942,0.787-4.272,2.262-7.134,2.145c-3.791-0.154-6.659-1.842-7.524-4.91h19.452c0.146-2.793,0.22-5.338-0.279-7.563
			C26.961,15.728,22.503,11.008,14.796,11.376z M9,23.284c0.921-2.508,3.033-4.514,6.298-4.627c3.083-0.107,4.994,1.976,5.685,4.627
			C17.119,23.38,12.865,23.38,9,23.284z M52.418,11.376c-5.551,0.266-9.395,3.142-11.76,7.056
			c-2.476,4.097-2.829,10.493-0.557,15.069c1.997,4.021,5.895,7.156,10.646,7.957c6.068,1.023,11-1.227,14.379-3.781
			c-0.479-0.896-0.875-1.742-1.393-2.709c-0.312-0.582-1.024-2.234-1.561-2.539c-0.912-0.52-1.428,0.135-2.23,0.508
			c-0.564,0.262-1.223,0.523-1.672,0.676c-4.768,1.621-10.372,0.268-11.537-4.176h19.451c0.668-5.443-0.419-9.953-2.73-13.037
			C61.197,13.388,57.774,11.12,52.418,11.376z M46.622,23.343c0.708-2.553,3.161-4.578,6.242-4.686
			c3.08-0.107,5.08,1.953,5.686,4.686H46.622z M160.371,15.497c-2.455-2.453-6.143-4.291-10.869-4.064
			c-2.268,0.109-4.297,0.65-6.02,1.524c-1.719,0.873-3.092,1.957-4.234,3.217c-2.287,2.519-4.164,6.004-3.902,11.007
			c0.248,4.736,1.979,7.813,4.627,10.326c2.568,2.439,6.148,4.254,10.867,4.064c4.457-0.18,7.889-2.115,10.199-4.684
			c2.469-2.746,4.012-5.971,3.959-11.063C164.949,21.134,162.732,17.854,160.371,15.497z M149.558,33.952
			c-3.246-0.221-5.701-2.615-6.41-5.418c-0.174-0.689-0.26-1.25-0.4-2.166c-0.035-0.234,0.072-0.523-0.045-0.77
			c0.682-3.698,2.912-6.257,6.799-6.547c2.543-0.189,4.258,0.735,5.52,1.863c1.322,1.182,2.303,2.715,2.451,4.967
			C157.789,30.669,154.185,34.267,149.558,33.952z M88.812,29.55c-1.232,2.363-2.9,4.307-6.13,4.402
			c-4.729,0.141-8.038-3.16-8.025-7.563c0.004-1.412,0.324-2.65,0.947-3.726c1.197-2.061,3.507-3.688,6.633-3.612
			c3.222,0.079,4.966,1.708,6.632,3.668c1.328-1.059,2.529-1.948,3.9-2.99c0.416-0.315,1.076-0.688,1.227-1.072
			c0.404-1.031-0.365-1.502-0.891-2.088c-2.543-2.835-6.66-5.377-11.704-5.137c-6.02,0.288-10.218,3.697-12.484,7.846
			c-1.293,2.365-1.951,5.158-1.729,8.408c0.209,3.053,1.191,5.496,2.619,7.508c2.842,4.004,7.385,6.973,13.656,6.377
			c5.976-0.568,9.574-3.936,11.816-8.354c-0.141-0.271-0.221-0.604-0.336-0.902C92.929,31.364,90.843,30.485,88.812,29.55z"></path>
					</svg>
				</div>

				<div class="footer-call-us">
					<span class="call-us-text">Got Questions ? Call us 24/7!</span>
					<span class="call-us-number">(800) 8001-8588, (0600) 874 548</span>
				</div>

			</div>
		</div>
	</div>

</footer><!-- #colophon -->