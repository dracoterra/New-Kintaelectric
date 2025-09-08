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
			<li id="menu-item-5294" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5294"><a title="My Account" href="my-account/index.htm"><i class="ec ec-user"></i>My Account</a>
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
							<label class="sr-only screen-reader-text visually-hidden" for="search">Search for:</label>
							<div class="input-group">
								<div class="input-search-field">
									<input type="text" id="search"
										class="form-control search-field product-search-field" dir="ltr" value=""
										name="s" placeholder="Search for Products" autocomplete="off">
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
								data-bs-title="Wishlist">
								<a href="wishlist/index.htm">
									<i class="ec ec-favorites"></i>
								</a>
							</div>
							<div class="header-icon header-icon__user-account dropdown animate-dropdown"
								data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="My Account">
								<a class="dropdown-toggle" href="my-account/index.htm" data-bs-toggle="dropdown"><i
										class="ec ec-user"></i></a>
								<ul class="dropdown-menu dropdown-menu-user-account">
									<li>
										<div class="register-sign-in-dropdown-inner">
											<div class="sign-in">
												<p>Returning Customer ?</p>
												<div class="sign-in-action"><a href="my-account/index.htm"
														class="sign-in-button">Sign in</a></div>
											</div>
											<div class="register">
												<p>Don&#039;t have an account ?</p>
												<div class="register-action"><a href="my-account/index.htm">Register</a>
												</div>
											</div>
										</div>
									</li>
								</ul>
							</div>
							<div class="header-icon header-icon__cart animate-dropdown dropdown"
								data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Cart">
								<a class="dropdown-toggle" href="cart/index.htm" data-bs-toggle="dropdown">
									<i class="ec ec-shopping-bag"></i>
									<span class="cart-items-count count header-icon-counter">60</span>
									<span class="cart-items-total-price total-price"><span
											class="woocommerce-Price-amount amount"><bdi><span
													class="woocommerce-Price-currencySymbol">&#36;</span>61,127.98</bdi></span></span>
								</a>
							</div>
						</div><!-- /.header-icons -->
					</div>
					<div class="electro-navigation row yes-home">
						<div class="departments-menu-v2">
							<div class="dropdown 
							show-dropdown">
								<a href="#" class="departments-menu-v2-title">
									<span><i class="departments-menu-v2-icon fa fa-list-ul"></i>All Departments</span>
								</a>
								<ul id="menu-all-departments-menu-1" class="dropdown-menu yamm">
									<li id="menu-item-5349"
										class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5349">
										<a title="Value of the Day" href="home-v2/index.htm">Value of the Day</a></li>
									<li id="menu-item-5350"
										class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5350">
										<a title="Top 100 Offers" href="home-v3/index.htm">Top 100 Offers</a></li>
									<li id="menu-item-5351"
										class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5351">
										<a title="New Arrivals" href="home-v3-full-color-background/index.htm">New
											Arrivals</a></li>
									<li id="menu-item-5220"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5220 dropdown">
										<a title="Computers &amp; Accessories" href="#" data-bs-toggle="dropdown-hover"
											class="dropdown-toggle" aria-haspopup="true">Computers &#038;
											Accessories</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5310"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5310">
												<div class="yamm-content">
													<div
														class="vc_row wpb_row vc_row-fluid bg-yamm-content bg-yamm-content-bottom bg-yamm-content-right">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img fetchpriority="high" width="540"
																					height="460"
																					src="../../uploads/2016/03/megamenu-2-300x256.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-2"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-2-300x256.png 540w, ../../uploads/2016/03/megamenu-2-300x256.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Computers &amp;
																					Accessories</li>
																				<li><a href="#">All Computers &amp;
																						Accessories</a></li>
																				<li><a href="#">Laptops, Desktops &amp;
																						Monitors</a></li>
																				<li><a href="#">Printers &amp; Ink</a>
																				</li>
																				<li><a href="#">Networking &amp;
																						Internet Devices</a></li>
																				<li><a href="#">Computer Accessories</a>
																				</li>
																				<li><a href="#">Software</a></li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">All
																							Electronics</span><span
																							class="nav-subtext">Discover
																							more products</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Office &amp;
																					Stationery</li>
																				<li><a href="#">All Office &amp;
																						Stationery</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5221"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5221 dropdown">
										<a title="Cameras, Audio &amp; Video" href="#" data-bs-toggle="dropdown-hover"
											class="dropdown-toggle" aria-haspopup="true">Cameras, Audio &#038; Video</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5309"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5309">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img width="540" height="460"
																					src="../../uploads/2016/03/megamenu-3-300x256.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-3"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-3-300x256.png 540w, ../../uploads/2016/03/megamenu-3-300x256.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title"><a
																						href="#">Cameras &amp;
																						Photography</a></li>
																				<li><a href="#">Lenses</a></li>
																				<li><a href="#">Camera Accessories</a>
																				</li>
																				<li><a href="#">Security &amp;
																						Surveillance</a></li>
																				<li><a href="#">Binoculars &amp;
																						Telescopes</a></li>
																				<li><a href="#">Camcorders</a></li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">All
																							Electronics</span><span
																							class="nav-subtext">Discover
																							more products</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Audio &amp; Video
																				</li>
																				<li><a href="#">All Audio &amp;
																						Video</a></li>
																				<li><a href="#">Headphones &amp;
																						Speakers</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5222"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5222 dropdown">
										<a title="Mobiles &amp; Tablets" href="#" data-bs-toggle="dropdown-hover"
											class="dropdown-toggle" aria-haspopup="true">Mobiles &#038; Tablets</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5311"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5311">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element  bg-yamm-extend-outside">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img width="540" height="495"
																					src="../../uploads/2016/03/megamenu--300x275.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu--300x275.png 540w, ../../uploads/2016/03/megamenu--300x275.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Mobiles &amp;
																					Tablets</li>
																				<li><a href="#">All Mobile Phones</a>
																				</li>
																				<li><a href="#">Smartphones</a></li>
																				<li><a href="#">Refurbished Mobiles</a>
																				</li>
																				<li class="nav-divider"></li>
																				<li><a href="#">All Mobile
																						Accessories</a></li>
																				<li><a href="#">Cases &amp; Covers</a>
																				</li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">All
																							Electronics</span><span
																							class="nav-subtext">Discover
																							more products</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title"></li>
																				<li><a href="#">All Tablets</a></li>
																				<li><a href="#">Tablet Accessories</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5223"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5223 dropdown">
										<a title="Movies, Music &amp; Video Games" href="#"
											data-bs-toggle="dropdown-hover" class="dropdown-toggle"
											aria-haspopup="true">Movies, Music &#038; Video Games</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5312"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5312">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="485"
																					src="../../uploads/2016/03/megamenu-8.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-8"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-8.png 540w, ../../uploads/2016/03/megamenu-8-300x269.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Movies &amp; TV
																					Shows</li>
																				<li><a href="#">All Movies &amp; TV
																						Shows</a></li>
																				<li><a href="#">All English</a></li>
																				<li><a href="#">All Hindi</a></li>
																				<li class="nav-divider"></li>
																				<li class="nav-title">Video Games</li>
																				<li><a href="#">PC Games</a></li>
																				<li><a href="#">Consoles</a></li>
																				<li><a href="#">Accessories</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Music</li>
																				<li><a href="#">All Music</a></li>
																				<li><a href="#">Indian Classical</a>
																				</li>
																				<li><a href="#">Musical Instruments</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5226"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5226 dropdown">
										<a title="TV &amp; Audio" href="#" data-bs-toggle="dropdown-hover"
											class="dropdown-toggle" aria-haspopup="true">TV &#038; Audio</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5314"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5314">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="460"
																					src="../../uploads/2016/03/megamenu-4.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-4"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-4.png 540w, ../../uploads/2016/03/megamenu-4-300x256.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Audio &amp; Video
																				</li>
																				<li><a href="#">All Audio &amp;
																						Video</a></li>
																				<li><a href="#">Televisions</a></li>
																				<li><a href="#">Headphones</a></li>
																				<li><a href="#">Speakers</a></li>
																				<li><a href="#">Audio &amp; Video
																						Accessories</a></li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">Electro
																							Home Appliances</span><span
																							class="nav-subtext">Available
																							in select cities</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Music</li>
																				<li><a href="#">Televisions</a></li>
																				<li><a href="#">Headphones</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5224"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5224 dropdown">
										<a title="Watches &amp; Eyewear" href="#" data-bs-toggle="dropdown-hover"
											class="dropdown-toggle" aria-haspopup="true">Watches &#038; Eyewear</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5313"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5313">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="486"
																					src="../../uploads/2016/03/megamenu-7.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-7"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-7.png 540w, ../../uploads/2016/03/megamenu-7-300x270.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Watches</li>
																				<li><a href="#">All Watches</a></li>
																				<li><a href="#">Men's Watches</a></li>
																				<li><a href="#">Women's Watches</a></li>
																				<li><a href="#">Premium Watches</a></li>
																				<li><a href="#">Deals on Watches</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Eyewear</li>
																				<li><a href="#">Men's Sunglasses</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5225"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5225 dropdown">
										<a title="Car, Motorbike &amp; Industrial" href="#"
											data-bs-toggle="dropdown-hover" class="dropdown-toggle"
											aria-haspopup="true">Car, Motorbike &#038; Industrial</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5315"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5315">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="523"
																					src="../../uploads/2016/03/megamenu-9.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-9"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-9.png 540w, ../../uploads/2016/03/megamenu-9-300x291.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Car &amp;
																					Motorbike</li>
																				<li><a href="#">All Cars &amp; Bikes</a>
																				</li>
																				<li><a href="#">Car &amp; Bike Care</a>
																				</li>
																				<li><a href="#">Lubricants</a></li>
																				<li class="nav-divider"></li>
																				<li class="nav-title">Shop for Bike</li>
																				<li><a href="#">Helmets &amp; Gloves</a>
																				</li>
																				<li><a href="#">Bike Parts</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Industrial
																					Supplies</li>
																				<li><a href="#">All Industrial
																						Supplies</a></li>
																				<li><a href="#">Lab &amp; Scientific</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5227"
										class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5227 dropdown">
										<a title="Accessories" href="#" data-bs-toggle="dropdown-hover"
											class="dropdown-toggle" aria-haspopup="true">Accessories</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5228"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5228">
												<a title="Cases" href="#">Cases</a></li>
											<li id="menu-item-5229"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5229">
												<a title="Chargers" href="#">Chargers</a></li>
											<li id="menu-item-5230"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5230">
												<a title="Headphone Accessories" href="#">Headphone Accessories</a></li>
											<li id="menu-item-5231"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5231">
												<a title="Headphone Cases" href="#">Headphone Cases</a></li>
											<li id="menu-item-5232"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5232">
												<a title="Headphones" href="#">Headphones</a></li>
											<li id="menu-item-5233"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5233">
												<a title="Computer Accessories" href="#">Computer Accessories</a></li>
											<li id="menu-item-5234"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5234">
												<a title="Laptop Accessories" href="#">Laptop Accessories</a></li>
										</ul>
									</li>
								</ul>
							</div>
						</div>
						<div class="secondary-nav-menu col electro-animate-dropdown position-relative">
							<ul id="menu-secondary-nav" class="secondary-nav yamm">
								<li id="menu-item-5343"
									class="highlight yamm-fw menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-5343 dropdown">
									<a title="All Pages" href="home-v2/index.htm" class="dropdown-toggle"
										aria-haspopup="true" data-hover="dropdown">All Pages</a>
									<ul role="menu" class=" dropdown-menu">
										<li id="menu-item-5444"
											class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5444">
											<div class="yamm-content">
												<div class="vc_row wpb_row vc_row-fluid">
													<div class="wpb_column vc_column_container vc_col-sm-3">
														<div class="vc_column-inner">
															<div class="wpb_wrapper">
																<div class="vc_wp_custommenu wpb_content_element">
																	<div class="widget widget_nav_menu">
																		<div class="menu-pages-menu-1-container">
																			<ul id="menu-pages-menu-1" class="menu">
																				<li id="menu-item-5172"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5172">
																					<a href="#">Home Pages</a></li>
																				<li id="menu-item-5320"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-2139 current_page_item menu-item-5320">
																					<a href="index.htm"
																						aria-current="page">Home v1</a>
																				</li>
																				<li id="menu-item-5318"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5318">
																					<a href="home-v2/index.htm">Home
																						v2</a></li>
																				<li id="menu-item-5319"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5319">
																					<a href="home-v3/index.htm">Home
																						v3</a></li>
																				<li id="menu-item-5390"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5390">
																					<a
																						href="home-v3-full-color-background/index.htm">Home
																						v3.1</a></li>
																				<li id="menu-item-5363"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5363">
																					<a href="home-v4/index.htm">Home
																						v4</a></li>
																				<li id="menu-item-5362"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5362">
																					<a href="home-v5/index.htm">Home
																						v5</a></li>
																				<li id="menu-item-5431"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5431">
																					<a href="home-v6/index.htm">Home
																						v6</a></li>
																				<li id="menu-item-5430"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5430">
																					<a href="home-v7/index.htm">Home
																						v7</a></li>
																				<li id="menu-item-6023"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6023">
																					<a href="home-v8/index.htm">Home
																						v8</a></li>
																				<li id="menu-item-6024"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6024">
																					<a href="home-v9/index.htm">Home
																						v9</a></li>
																				<li id="menu-item-6807"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6807">
																					<a href="home-v10/index.htm">Home
																						v10</a></li>
																				<li id="menu-item-6806"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6806">
																					<a href="home-v11/index.htm">Home
																						v11</a></li>
																				<li id="menu-item-6960"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6960">
																					<a href="home-v12/index.htm">Home
																						v12</a></li>
																				<li id="menu-item-7422"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7422">
																					<a href="home-v13/index.htm">Home
																						v13</a></li>
																				<li id="menu-item-8263"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8263">
																					<a href="3x/index.htm">Home v14</a>
																				</li>
																				<li id="menu-item-8264"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8264">
																					<a href="3x/home-v15/index.htm">Home
																						v15</a></li>
																				<li id="menu-item-8430"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8430">
																					<a href="4x/index.htm">Home v16</a>
																				</li>
																				<li id="menu-item-8431"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8431">
																					<a href="4x/home-v17/index.htm">Home
																						v17</a></li>
																			</ul>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="wpb_column vc_column_container vc_col-sm-3">
														<div class="vc_column-inner">
															<div class="wpb_wrapper">
																<div class="vc_wp_custommenu wpb_content_element">
																	<div class="widget widget_nav_menu">
																		<div class="menu-pages-menu-2-container">
																			<ul id="menu-pages-menu-2" class="menu">
																				<li id="menu-item-5178"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5178">
																					<a href="#">Shop Pages</a></li>
																				<li id="menu-item-5179"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5179">
																					<a
																						href="product-category/smart-phones-tablets/index.htm#grid">Shop
																						Grid</a></li>
																				<li id="menu-item-5180"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5180">
																					<a
																						href="product-category/smart-phones-tablets/index.htm#grid-extended">Shop
																						Grid Extended</a></li>
																				<li id="menu-item-5184"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5184">
																					<a
																						href="product-category/smart-phones-tablets/index.htm#list-view">Shop
																						List View</a></li>
																				<li id="menu-item-5185"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5185">
																					<a
																						href="product-category/smart-phones-tablets/index.htm#list-view-small">Shop
																						List View Small</a></li>
																				<li id="menu-item-5197"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5197">
																					<a
																						href="electro-shop/index.htm?page_id=3030&#038;shop_columns=3&#038;shop_layout=left-sidebar">Shop
																						Left Sidebar</a></li>
																				<li id="menu-item-5195"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5195">
																					<a
																						href="electro-shop/index-1.htm?page_id=3030&#038;shop_columns=5&#038;shop_layout=full-width">Shop
																						Full width</a></li>
																				<li id="menu-item-5196"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5196">
																					<a
																						href="electro-shop/index-2.htm?page_id=3030&#038;shop_columns=3&#038;shop_layout=right-sidebar">Shop
																						Right Sidebar</a></li>
																				<li id="menu-item-5173"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5173">
																					<a href="#">Product Categories</a>
																				</li>
																				<li id="menu-item-5175"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5175">
																					<a
																						href="product-category/laptops-computers/index.htm?product_cat=laptops-computers&#038;cat_columns=4&#038;cat_layout=left-sidebar">4
																						Column Sidebar</a></li>
																				<li id="menu-item-5174"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5174">
																					<a
																						href="product-category/laptops-computers/index-1.htm?product_cat=laptops-computers&#038;cat_columns=5&#038;cat_layout=left-sidebar">5
																						Column Sidebar</a></li>
																				<li id="menu-item-5176"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5176">
																					<a
																						href="product-category/laptops-computers/index-2.htm?product_cat=laptops-computers&#038;cat_columns=6&#038;cat_layout=full-width">6
																						Column Full width</a></li>
																				<li id="menu-item-5177"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5177">
																					<a
																						href="product-category/laptops-computers/index-3.htm?product_cat=laptops-computers&#038;cat_columns=7&#038;cat_layout=full-width">7
																						Columns Full width</a></li>
																				<li id="menu-item-8271"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-8271">
																					<a href="#">Static Pages</a></li>
																				<li id="menu-item-8273"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8273">
																					<a href="about/index.htm">About</a>
																				</li>
																				<li id="menu-item-8277"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8277">
																					<a href="contact-v1/index.htm">Contact
																						v1</a></li>
																				<li id="menu-item-8276"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8276">
																					<a href="contact-v2/index.htm">Contact
																						v2</a></li>
																				<li id="menu-item-8275"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8275">
																					<a href="faq/index.htm">FAQ</a></li>
																				<li id="menu-item-8278"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8278">
																					<a href="store-directory/index.htm">Store
																						Directory</a></li>
																				<li id="menu-item-8274"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8274">
																					<a
																						href="terms-and-conditions/index.htm">Terms
																						and Conditions</a></li>
																				<li id="menu-item-8279"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8279">
																					<a
																						href="https://electro.madrasthemes.com/?page_id=12343434">404</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="wpb_column vc_column_container vc_col-sm-3">
														<div class="vc_column-inner">
															<div class="wpb_wrapper">
																<div class="vc_wp_custommenu wpb_content_element">
																	<div class="widget widget_nav_menu">
																		<div class="menu-pages-menu-3-container">
																			<ul id="menu-pages-menu-3" class="menu">
																				<li id="menu-item-5288"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5288">
																					<a href="#">Mobile Home Pages</a>
																				</li>
																				<li id="menu-item-5289"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5289">
																					<a target="_blank"
																						href="https://transvelo.github.io/electro/mobile.html">Home
																						v1</a></li>
																				<li id="menu-item-5290"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5290">
																					<a target="_blank"
																						href="https://transvelo.github.io/electro/mobile.html">Home
																						v2</a></li>
																				<li id="menu-item-5188"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5188">
																					<a href="#">Single Product Pages</a>
																				</li>
																				<li id="menu-item-5338"
																					class="menu-item menu-item-type-post_type menu-item-object-product menu-item-5338">
																					<a
																						href="product/ultra-wireless-s50-headphones-s50-with-bluetooth/index.htm">Single
																						Product Extended</a></li>
																				<li id="menu-item-5337"
																					class="menu-item menu-item-type-post_type menu-item-object-product menu-item-5337">
																					<a
																						href="product/ultra-wireless-s50-headphones-s50-with-bluetooth-2/index.htm">Single
																						Product Fullwidth</a></li>
																				<li id="menu-item-5336"
																					class="menu-item menu-item-type-post_type menu-item-object-product menu-item-5336">
																					<a
																						href="product/ultra-wireless-s50-headphones-s50-with-bluetooth-3/index.htm">Single
																						Product Sidebar</a></li>
																				<li id="menu-item-5187"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5187">
																					<a href="#">WooCommerce Pages</a>
																				</li>
																				<li id="menu-item-5329"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5329">
																					<a href="shop/index.htm">Shop</a>
																				</li>
																				<li id="menu-item-5326"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5326">
																					<a href="cart/index.htm">Cart</a>
																				</li>
																				<li id="menu-item-5327"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5327">
																					<a
																						href="cart/index.htm">Checkout</a>
																				</li>
																				<li id="menu-item-5328"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5328">
																					<a href="my-account/index.htm">My
																						Account</a></li>
																				<li id="menu-item-5330"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5330">
																					<a
																						href="track-your-order/index.htm">Track
																						your Order</a></li>
																				<li id="menu-item-5359"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5359">
																					<a
																						href="compare/index.htm">Compare</a>
																				</li>
																			</ul>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="wpb_column vc_column_container vc_col-sm-3">
														<div class="vc_column-inner">
															<div class="wpb_wrapper">
																<div class="vc_wp_custommenu wpb_content_element">
																	<div class="widget widget_nav_menu">
																		<div class="menu-pages-menu-4-container">
																			<ul id="menu-pages-menu-4" class="menu">
																				<li id="menu-item-5186"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5186">
																					<a href="#">Blog Pages</a></li>
																				<li id="menu-item-5331"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5331">
																					<a href="blog-v1/index.htm">Blog
																						v1</a></li>
																				<li id="menu-item-5332"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5332">
																					<a href="blog-v3/index.htm">Blog
																						v3</a></li>
																				<li id="menu-item-5333"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5333">
																					<a href="blog-v2/index.htm">Blog
																						v2</a></li>
																				<li id="menu-item-5334"
																					class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5334">
																					<a href="blog-full-width/index.htm">Blog
																						Full Width</a></li>
																				<li id="menu-item-5335"
																					class="menu-item menu-item-type-post_type menu-item-object-post menu-item-5335">
																					<a
																						href="blog/2016/03/01/robot-wars-now-closed/index.htm">Single
																						Blog Post</a></li>
																				<li id="menu-item-5292"
																					class="nav-title menu-item menu-item-type-post_type menu-item-object-page menu-item-5292">
																					<a href="shop/index.htm">Shop
																						Columns</a></li>
																				<li id="menu-item-5189"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5189">
																					<a
																						href="electro-shop/index-3.htm?page_id=3030&#038;shop_columns=7&#038;shop_layout=full-width">7
																						Columns Full width</a></li>
																				<li id="menu-item-5190"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5190">
																					<a
																						href="electro-shop/index-4.htm?page_id=3030&#038;shop_columns=6&#038;shop_layout=full-width">6
																						Columns Full width</a></li>
																				<li id="menu-item-5191"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5191">
																					<a
																						href="electro-shop/index-5.htm?page_id=3030&#038;shop_columns=5&#038;shop_layout=left-sidebar">5
																						Columns Sidebar</a></li>
																				<li id="menu-item-5192"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5192">
																					<a
																						href="electro-shop/index-6.htm?page_id=3030&#038;shop_columns=4&#038;shop_layout=left-sidebar">4
																						Columns Sidebar</a></li>
																				<li id="menu-item-5193"
																					class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5193">
																					<a
																						href="electro-shop/index.htm?page_id=3030&#038;shop_columns=3&#038;shop_layout=left-sidebar">3
																						Columns Sidebar</a></li>
																				<li id="menu-item-5291"
																					class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5291">
																					<a target="_blank"
																						href="store/multivendor/index.htm">Vendor
																						Demo</a></li>
																			</ul>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</li>
									</ul>
								</li>
								<li id="menu-item-5344"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5344"><a
										title="Featured Brands" href="home-v3/index.htm">Featured Brands</a></li>
								<li id="menu-item-5345"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5345"><a
										title="Trending Styles" href="home-v3-full-color-background/index.htm">Trending
										Styles</a></li>
								<li id="menu-item-5341"
									class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5341"><a
										title="Gift Cards" href="blog-v1/index.htm">Gift Cards</a></li>
								<li id="menu-item-5342"
									class="pull-end menu-item menu-item-type-post_type menu-item-object-page menu-item-5342">
									<a title="Free Shipping on Orders $50+" href="blog-v2/index.htm">Free Shipping on
										Orders $50+</a></li>
							</ul>
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

							<div class="off-canvas-navigation
							 light" id="default-oc-header">
								<ul id="menu-all-departments-menu-2" class="nav nav-inline yamm">
									<li id="menu-item-5349"
										class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5349">
										<a title="Value of the Day" href="home-v2/index.htm">Value of the Day</a></li>
									<li id="menu-item-5350"
										class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5350">
										<a title="Top 100 Offers" href="home-v3/index.htm">Top 100 Offers</a></li>
									<li id="menu-item-5351"
										class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5351">
										<a title="New Arrivals" href="home-v3-full-color-background/index.htm">New
											Arrivals</a></li>
									<li id="menu-item-5220"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5220 dropdown">
										<a title="Computers &amp; Accessories" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">Computers &#038;
											Accessories</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5310"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5310">
												<div class="yamm-content">
													<div
														class="vc_row wpb_row vc_row-fluid bg-yamm-content bg-yamm-content-bottom bg-yamm-content-right">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img fetchpriority="high" width="540"
																					height="460"
																					src="../../uploads/2016/03/megamenu-2-300x256.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-2"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-2-300x256.png 540w, ../../uploads/2016/03/megamenu-2-300x256.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Computers &amp;
																					Accessories</li>
																				<li><a href="#">All Computers &amp;
																						Accessories</a></li>
																				<li><a href="#">Laptops, Desktops &amp;
																						Monitors</a></li>
																				<li><a href="#">Printers &amp; Ink</a>
																				</li>
																				<li><a href="#">Networking &amp;
																						Internet Devices</a></li>
																				<li><a href="#">Computer Accessories</a>
																				</li>
																				<li><a href="#">Software</a></li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">All
																							Electronics</span><span
																							class="nav-subtext">Discover
																							more products</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Office &amp;
																					Stationery</li>
																				<li><a href="#">All Office &amp;
																						Stationery</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5221"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5221 dropdown">
										<a title="Cameras, Audio &amp; Video" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">Cameras, Audio &#038; Video</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5309"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5309">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img width="540" height="460"
																					src="../../uploads/2016/03/megamenu-3-300x256.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-3"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-3-300x256.png 540w, ../../uploads/2016/03/megamenu-3-300x256.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title"><a
																						href="#">Cameras &amp;
																						Photography</a></li>
																				<li><a href="#">Lenses</a></li>
																				<li><a href="#">Camera Accessories</a>
																				</li>
																				<li><a href="#">Security &amp;
																						Surveillance</a></li>
																				<li><a href="#">Binoculars &amp;
																						Telescopes</a></li>
																				<li><a href="#">Camcorders</a></li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">All
																							Electronics</span><span
																							class="nav-subtext">Discover
																							more products</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Audio &amp; Video
																				</li>
																				<li><a href="#">All Audio &amp;
																						Video</a></li>
																				<li><a href="#">Headphones &amp;
																						Speakers</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5222"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5222 dropdown">
										<a title="Mobiles &amp; Tablets" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">Mobiles &#038; Tablets</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5311"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5311">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element  bg-yamm-extend-outside">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img width="540" height="495"
																					src="../../uploads/2016/03/megamenu--300x275.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu--300x275.png 540w, ../../uploads/2016/03/megamenu--300x275.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Mobiles &amp;
																					Tablets</li>
																				<li><a href="#">All Mobile Phones</a>
																				</li>
																				<li><a href="#">Smartphones</a></li>
																				<li><a href="#">Refurbished Mobiles</a>
																				</li>
																				<li class="nav-divider"></li>
																				<li><a href="#">All Mobile
																						Accessories</a></li>
																				<li><a href="#">Cases &amp; Covers</a>
																				</li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">All
																							Electronics</span><span
																							class="nav-subtext">Discover
																							more products</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title"></li>
																				<li><a href="#">All Tablets</a></li>
																				<li><a href="#">Tablet Accessories</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5223"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5223 dropdown">
										<a title="Movies, Music &amp; Video Games" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">Movies, Music &#038; Video
											Games</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5312"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5312">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="485"
																					src="../../uploads/2016/03/megamenu-8.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-8"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-8.png 540w, ../../uploads/2016/03/megamenu-8-300x269.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Movies &amp; TV
																					Shows</li>
																				<li><a href="#">All Movies &amp; TV
																						Shows</a></li>
																				<li><a href="#">All English</a></li>
																				<li><a href="#">All Hindi</a></li>
																				<li class="nav-divider"></li>
																				<li class="nav-title">Video Games</li>
																				<li><a href="#">PC Games</a></li>
																				<li><a href="#">Consoles</a></li>
																				<li><a href="#">Accessories</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Music</li>
																				<li><a href="#">All Music</a></li>
																				<li><a href="#">Indian Classical</a>
																				</li>
																				<li><a href="#">Musical Instruments</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5226"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5226 dropdown">
										<a title="TV &amp; Audio" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">TV &#038; Audio</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5314"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5314">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="460"
																					src="../../uploads/2016/03/megamenu-4.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-4"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-4.png 540w, ../../uploads/2016/03/megamenu-4-300x256.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Audio &amp; Video
																				</li>
																				<li><a href="#">All Audio &amp;
																						Video</a></li>
																				<li><a href="#">Televisions</a></li>
																				<li><a href="#">Headphones</a></li>
																				<li><a href="#">Speakers</a></li>
																				<li><a href="#">Audio &amp; Video
																						Accessories</a></li>
																				<li class="nav-divider"></li>
																				<li><a href="#"><span
																							class="nav-text">Electro
																							Home Appliances</span><span
																							class="nav-subtext">Available
																							in select cities</span></a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Music</li>
																				<li><a href="#">Televisions</a></li>
																				<li><a href="#">Headphones</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5224"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5224 dropdown">
										<a title="Watches &amp; Eyewear" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">Watches &#038; Eyewear</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5313"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5313">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="486"
																					src="../../uploads/2016/03/megamenu-7.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-7"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-7.png 540w, ../../uploads/2016/03/megamenu-7-300x270.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Watches</li>
																				<li><a href="#">All Watches</a></li>
																				<li><a href="#">Men's Watches</a></li>
																				<li><a href="#">Women's Watches</a></li>
																				<li><a href="#">Premium Watches</a></li>
																				<li><a href="#">Deals on Watches</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Eyewear</li>
																				<li><a href="#">Men's Sunglasses</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5225"
										class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5225 dropdown">
										<a title="Car, Motorbike &amp; Industrial" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">Car, Motorbike &#038;
											Industrial</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5315"
												class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5315">
												<div class="yamm-content">
													<div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
														<div class="wpb_column vc_column_container vc_col-sm-12">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div
																		class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

																		<figure class="wpb_wrapper vc_figure">
																			<div
																				class="vc_single_image-wrapper   vc_box_border_grey">
																				<img loading="lazy" width="540"
																					height="523"
																					src="../../uploads/2016/03/megamenu-9.png"
																					class="vc_single_image-img attachment-full"
																					alt="" title="megamenu-9"
																					decoding="async"
																					srcset="../../uploads/2016/03/megamenu-9.png 540w, ../../uploads/2016/03/megamenu-9-300x291.png 300w"
																					sizes="(max-width: 540px) 100vw, 540px">
																			</div>
																		</figure>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="vc_row wpb_row vc_row-fluid">
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Car &amp;
																					Motorbike</li>
																				<li><a href="#">All Cars &amp; Bikes</a>
																				</li>
																				<li><a href="#">Car &amp; Bike Care</a>
																				</li>
																				<li><a href="#">Lubricants</a></li>
																				<li class="nav-divider"></li>
																				<li class="nav-title">Shop for Bike</li>
																				<li><a href="#">Helmets &amp; Gloves</a>
																				</li>
																				<li><a href="#">Bike Parts</a></li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="wpb_column vc_column_container vc_col-sm-6">
															<div class="vc_column-inner">
																<div class="wpb_wrapper">
																	<div class="wpb_text_column wpb_content_element">
																		<div class="wpb_wrapper">
																			<ul>
																				<li class="nav-title">Industrial
																					Supplies</li>
																				<li><a href="#">All Industrial
																						Supplies</a></li>
																				<li><a href="#">Lab &amp; Scientific</a>
																				</li>
																			</ul>

																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</li>
									<li id="menu-item-5227"
										class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5227 dropdown">
										<a title="Accessories" href="#" data-bs-toggle="dropdown"
											class="dropdown-toggle" aria-haspopup="true">Accessories</a>
										<ul role="menu" class=" dropdown-menu">
											<li id="menu-item-5228"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5228">
												<a title="Cases" href="#">Cases</a></li>
											<li id="menu-item-5229"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5229">
												<a title="Chargers" href="#">Chargers</a></li>
											<li id="menu-item-5230"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5230">
												<a title="Headphone Accessories" href="#">Headphone Accessories</a></li>
											<li id="menu-item-5231"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5231">
												<a title="Headphone Cases" href="#">Headphone Cases</a></li>
											<li id="menu-item-5232"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5232">
												<a title="Headphones" href="#">Headphones</a></li>
											<li id="menu-item-5233"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5233">
												<a title="Computer Accessories" href="#">Computer Accessories</a></li>
											<li id="menu-item-5234"
												class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5234">
												<a title="Laptop Accessories" href="#">Laptop Accessories</a></li>
										</ul>
									</li>
								</ul>
							</div>
						</div>
						<div class="header-logo">
							<a href="index.htm" class="header-logo-link">
								<svg version="1.1" x="0px" y="0px" width="175.748px" height="42.52px"
									viewbox="0 0 175.748 42.52" enable-background="new 0 0 175.748 42.52">
									<ellipse class="ellipse-bg" fill-rule="evenodd" clip-rule="evenodd" fill="#FDD700"
										cx="170.05" cy="36.341" rx="5.32" ry="5.367"></ellipse>
									<path fill-rule="evenodd" clip-rule="evenodd" fill="#333E48" d="M30.514,0.71c-0.034,0.003-0.066,0.008-0.056,0.056
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
	c5.976-0.568,9.574-3.936,11.816-8.354c-0.141-0.271-0.221-0.604-0.336-0.902C92.929,31.364,90.843,30.485,88.812,29.55z">
									</path>
								</svg> </a>
						</div>
						<div class="handheld-header-links">
							<ul class="columns-3">
								<li class="search">
									<a href="">Search</a>
									<div class="site-search">
										<div class="widget woocommerce widget_product_search">
											<form role="search" method="get" class="woocommerce-product-search"
												action="https://electro.madrasthemes.com/">
												<label class="screen-reader-text"
													for="woocommerce-product-search-field-0">Search for:</label>
												<input type="search" id="woocommerce-product-search-field-0"
													class="search-field" placeholder="Search products&hellip;" value=""
													name="s">
												<button type="submit" value="Search" class="">Search</button>
												<input type="hidden" name="post_type" value="product">
											</form>
										</div>
									</div>
								</li>
								<li class="my-account">
									<a href="my-account/index.htm"><i class="ec ec-user"></i></a>
								</li>
								<li class="cart">
									<a class="footer-cart-contents" href="cart/index.htm"
										title="View your shopping cart">
										<i class="ec ec-shopping-bag"></i>
										<span class="cart-items-count count">60</span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>

			</header><!-- #masthead -->