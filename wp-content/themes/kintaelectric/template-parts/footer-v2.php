<?php
/**
 * Footer Template v2 - Modern Electro Style
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<footer id="colophon" class="site-footer electro-footer footer-v2">
	<div class="container">
		<div class="footer-top">
			<div class="row">
				<div class="col-lg-4 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
							<?php dynamic_sidebar( 'footer-1' ); ?>
						<?php else : ?>
							<div class="footer-brand">
								<div class="footer-logo">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
								</div>
								<p><?php esc_html_e( 'Your trusted partner for all electronic needs. Quality products, competitive prices, and excellent customer service.', 'kintaelectric' ); ?></p>
								<div class="footer-social">
									<a href="#" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
									<a href="#" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
									<a href="#" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
									<a href="#" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
									<a href="#" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="col-lg-2 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
							<?php dynamic_sidebar( 'footer-2' ); ?>
						<?php else : ?>
							<h4><?php esc_html_e( 'Quick Links', 'kintaelectric' ); ?></h4>
							<ul>
								<li><a href="#"><?php esc_html_e( 'About Us', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Contact', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Blog', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Careers', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Press', 'kintaelectric' ); ?></a></li>
							</ul>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="col-lg-2 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
							<?php dynamic_sidebar( 'footer-3' ); ?>
						<?php else : ?>
							<h4><?php esc_html_e( 'Customer Service', 'kintaelectric' ); ?></h4>
							<ul>
								<li><a href="#"><?php esc_html_e( 'Help Center', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Shipping Info', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Returns', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Size Guide', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Track Your Order', 'kintaelectric' ); ?></a></li>
							</ul>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
							<?php dynamic_sidebar( 'footer-4' ); ?>
						<?php else : ?>
							<h4><?php esc_html_e( 'Newsletter', 'kintaelectric' ); ?></h4>
							<p><?php esc_html_e( 'Subscribe to our newsletter and get 10% off your first order!', 'kintaelectric' ); ?></p>
							<form class="newsletter-form" method="post" action="">
								<div class="input-group">
									<input type="email" class="form-control" placeholder="<?php esc_attr_e( 'Enter your email', 'kintaelectric' ); ?>" required>
									<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Subscribe', 'kintaelectric' ); ?></button>
								</div>
							</form>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="footer-bottom">
			<div class="row align-items-center">
				<div class="col-lg-6">
					<div class="footer-copyright">
						<p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'kintaelectric' ); ?></p>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="footer-payment">
						<span class="payment-label"><?php esc_html_e( 'We Accept:', 'kintaelectric' ); ?></span>
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/credit-cards/visa.svg' ); ?>" alt="Visa">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/credit-cards/mastercard.svg' ); ?>" alt="Mastercard">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/credit-cards/amex.svg' ); ?>" alt="American Express">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/credit-cards/discover.svg' ); ?>" alt="Discover">
					</div>
				</div>
			</div>
		</div>
	</div>
</footer>

