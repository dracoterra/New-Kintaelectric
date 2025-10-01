<?php
/**
 * Footer Template v3 - Minimalist Electro Style
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<footer id="colophon" class="site-footer electro-footer footer-v3">
	<div class="container">
		<div class="footer-content">
			<div class="row align-items-center">
				<div class="col-lg-4">
					<div class="footer-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
						</a>
					</div>
				</div>
				
				<div class="col-lg-4">
					<div class="footer-menu">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-2',
								'menu_id'        => 'footer-menu',
								'container'      => false,
								'menu_class'     => 'footer-nav',
								'fallback_cb'    => false,
							)
						);
						?>
					</div>
				</div>
				
				<div class="col-lg-4">
					<div class="footer-social">
						<a href="#" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
						<a href="#" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
						<a href="#" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
						<a href="#" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
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

