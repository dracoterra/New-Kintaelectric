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
				<?php if ( is_active_sidebar( 'top-footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'top-footer-1' ); ?>
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
				<?php if ( is_active_sidebar( 'top-footer-2' ) ) : ?>
					<?php dynamic_sidebar( 'top-footer-2' ); ?>
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
				<?php if ( is_active_sidebar( 'top-footer-3' ) ) : ?>
					<?php dynamic_sidebar( 'top-footer-3' ); ?>
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
				<?php if ( is_active_sidebar( 'top-footer-4' ) ) : ?>
					<?php dynamic_sidebar( 'top-footer-4' ); ?>
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
								<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
									<?php dynamic_sidebar( 'footer-1' ); ?>
								<?php else : ?>
									<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
										<div class="empty-widget-area">
											<p>Configure un widget en <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-1' ) ); ?>">Footer Contact</a></p>
											<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-1' ) ); ?>" class="widget-edit-link" title="Configurar Footer Contact">
												<span class="dashicons dashicons-edit"></span>
											</a>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
							<div class="footer-bottom-widgets-menu col-md">
								<div class="footer-bottom-widgets-menu-inner row g-0 row-cols-xl-3">
									<div class="columns col">
										<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
											<?php dynamic_sidebar( 'footer-2' ); ?>
										<?php else : ?>
											<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
												<div class="empty-widget-area">
													<p>Configure un widget en <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-2' ) ); ?>">Footer Menu 1</a></p>
													<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-2' ) ); ?>" class="widget-edit-link" title="Configurar Footer Menu 1">
														<span class="dashicons dashicons-edit"></span>
													</a>
												</div>
											<?php endif; ?>
										<?php endif; ?>
									</div>
									<div class="columns col">
										<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
											<?php dynamic_sidebar( 'footer-3' ); ?>
										<?php else : ?>
											<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
												<div class="empty-widget-area">
													<p>Configure un widget en <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-3' ) ); ?>">Footer Menu 2</a></p>
													<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-3' ) ); ?>" class="widget-edit-link" title="Configurar Footer Menu 2">
														<span class="dashicons dashicons-edit"></span>
													</a>
												</div>
											<?php endif; ?>
										<?php endif; ?>
									</div>
									<div class="columns col">
										<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
											<?php dynamic_sidebar( 'footer-4' ); ?>
										<?php else : ?>
											<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
												<div class="empty-widget-area">
													<p>Configure un widget en <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-4' ) ); ?>">Footer Menu 3</a></p>
													<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar-widgets-footer-4' ) ); ?>" class="widget-edit-link" title="Configurar Footer Menu 3">
														<span class="dashicons dashicons-edit"></span>
													</a>
												</div>
											<?php endif; ?>
										<?php endif; ?>
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
			</footer><!-- #colophon -->
