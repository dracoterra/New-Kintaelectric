<?php
/**
 * Default footer template part - Electro Style
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<!-- Newsletter Section -->
<section class="newsletter-section">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6">
				<div class="newsletter-content">
					<h3><?php esc_html_e( 'Sign up to Newsletter', 'kintaelectric' ); ?></h3>
					<p><?php esc_html_e( '...and receive $20 coupon for first shopping.', 'kintaelectric' ); ?></p>
				</div>
			</div>
			<div class="col-lg-6">
				<form class="newsletter-form" method="post" action="">
					<div class="input-group">
						<input type="email" class="form-control" placeholder="<?php esc_attr_e( 'Email address', 'kintaelectric' ); ?>" required>
						<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Sign Up', 'kintaelectric' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- Contact Info Section -->
<section class="contact-info-section">
	<div class="container">
		<div class="row">
			<div class="col-lg-4">
				<div class="contact-info">
					<h4><?php esc_html_e( 'Got questions? Call us 24/7!', 'kintaelectric' ); ?></h4>
					<p class="phone">(800) 8001-8588, (0600) 874 548</p>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="contact-info">
					<h4><?php esc_html_e( 'Contact info', 'kintaelectric' ); ?></h4>
					<p><?php esc_html_e( '17 Princess Road, London, Greater London NW1 8JR, UK', 'kintaelectric' ); ?></p>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="contact-info">
					<h4><?php esc_html_e( 'Find it Fast', 'kintaelectric' ); ?></h4>
					<ul class="quick-links">
						<li><a href="#"><?php esc_html_e( 'Laptops & Computers', 'kintaelectric' ); ?></a></li>
						<li><a href="#"><?php esc_html_e( 'Cameras & Photography', 'kintaelectric' ); ?></a></li>
						<li><a href="#"><?php esc_html_e( 'Smart Phones & Tablets', 'kintaelectric' ); ?></a></li>
						<li><a href="#"><?php esc_html_e( 'Video Games & Consoles', 'kintaelectric' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<footer id="colophon" class="site-footer electro-footer">
	<div class="container">
		<div class="footer-top">
			<div class="row">
				<div class="col-lg-3 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
							<?php dynamic_sidebar( 'footer-1' ); ?>
						<?php else : ?>
							<h4><?php esc_html_e( 'Find it Fast', 'kintaelectric' ); ?></h4>
							<ul>
								<li><a href="#"><?php esc_html_e( 'Laptops & Computers', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Cameras & Photography', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Smart Phones & Tablets', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Video Games & Consoles', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'TV & Audio', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Gadgets', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Car Electronic & GPS', 'kintaelectric' ); ?></a></li>
							</ul>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="col-lg-3 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
							<?php dynamic_sidebar( 'footer-2' ); ?>
						<?php else : ?>
							<h4><?php esc_html_e( 'Customer Care', 'kintaelectric' ); ?></h4>
							<ul>
								<li><a href="#"><?php esc_html_e( 'My Account', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Order Tracking', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Wish List', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Customer Service', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Returns / Exchange', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'FAQs', 'kintaelectric' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Product Support', 'kintaelectric' ); ?></a></li>
							</ul>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="col-lg-3 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
							<?php dynamic_sidebar( 'footer-3' ); ?>
						<?php else : ?>
							<h4><?php esc_html_e( 'Featured Brands', 'kintaelectric' ); ?></h4>
							<div class="brands-grid">
								<div class="brand-item">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/brand-1.png' ); ?>" alt="Brand 1">
								</div>
								<div class="brand-item">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/brand-2.png' ); ?>" alt="Brand 2">
								</div>
								<div class="brand-item">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/brand-3.png' ); ?>" alt="Brand 3">
								</div>
								<div class="brand-item">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/brand-4.png' ); ?>" alt="Brand 4">
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="col-lg-3 col-md-6">
					<div class="footer-widget">
						<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
							<?php dynamic_sidebar( 'footer-4' ); ?>
						<?php else : ?>
							<h4><?php esc_html_e( 'About Electro', 'kintaelectric' ); ?></h4>
							<p><?php esc_html_e( 'Electro is a modern and clean eCommerce theme built with Bootstrap 4. It\'s perfect for electronics, gadgets, and digital products stores.', 'kintaelectric' ); ?></p>
							<div class="footer-social">
								<a href="#" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
								<a href="#" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
								<a href="#" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
								<a href="#" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="footer-bottom">
			<div class="row align-items-center">
				<div class="col-lg-6">
					<div class="footer-copyright">
						<p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?> - <?php esc_html_e( 'All rights Reserved', 'kintaelectric' ); ?></p>
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

<style>
/* Newsletter Section */
.newsletter-section {
	background: #f8f9fa;
	padding: 60px 0;
	border-top: 1px solid #e9ecef;
}

.newsletter-content h3 {
	font-size: 24px;
	font-weight: 600;
	margin-bottom: 10px;
	color: #333;
}

.newsletter-content p {
	color: #666;
	margin: 0;
}

.newsletter-form .input-group {
	border-radius: 25px;
	overflow: hidden;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.newsletter-form .form-control {
	border: none;
	padding: 15px 20px;
	font-size: 14px;
}

.newsletter-form .form-control:focus {
	box-shadow: none;
	border: none;
}

.newsletter-form .btn {
	border: none;
	background: #333;
	color: #fff;
	padding: 15px 30px;
	border-radius: 0;
	font-weight: 500;
}

.newsletter-form .btn:hover {
	background: #555;
}

/* Contact Info Section */
.contact-info-section {
	background: #fff;
	padding: 40px 0;
	border-bottom: 1px solid #e9ecef;
}

.contact-info h4 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 15px;
	color: #333;
}

.contact-info p {
	color: #666;
	margin: 0;
}

.contact-info .phone {
	font-size: 18px;
	font-weight: 600;
	color: #333;
}

.quick-links {
	list-style: none;
	margin: 0;
	padding: 0;
}

.quick-links li {
	margin-bottom: 8px;
}

.quick-links a {
	color: #666;
	text-decoration: none;
	font-size: 14px;
	transition: color 0.3s ease;
}

.quick-links a:hover {
	color: #333;
}

/* Footer */
.electro-footer {
	background: #333;
	color: #fff;
}

.footer-top {
	padding: 60px 0 40px;
}

.footer-widget h4 {
	color: #fff;
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 20px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.footer-widget p {
	color: #ccc;
	line-height: 1.6;
	margin-bottom: 15px;
}

.footer-widget ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.footer-widget ul li {
	margin-bottom: 8px;
}

.footer-widget ul a {
	color: #ccc;
	text-decoration: none;
	font-size: 14px;
	transition: color 0.3s ease;
}

.footer-widget ul a:hover {
	color: #fff;
}

/* Brands Grid */
.brands-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 15px;
}

.brand-item {
	background: #fff;
	padding: 15px;
	border-radius: 8px;
	text-align: center;
	transition: transform 0.3s ease;
}

.brand-item:hover {
	transform: translateY(-2px);
}

.brand-item img {
	max-width: 100%;
	height: auto;
}

/* Footer Social */
.footer-social {
	display: flex;
	gap: 10px;
	margin-top: 20px;
}

.footer-social a {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 35px;
	height: 35px;
	background: #555;
	color: #fff;
	text-decoration: none;
	border-radius: 50%;
	transition: all 0.3s ease;
	font-size: 14px;
}

.footer-social a:hover {
	background: #fff;
	color: #333;
	transform: translateY(-2px);
}

/* Footer Bottom */
.footer-bottom {
	padding: 30px 0;
	border-top: 1px solid #555;
}

.footer-copyright p {
	margin: 0;
	color: #ccc;
	font-size: 14px;
}

.footer-payment {
	display: flex;
	justify-content: flex-end;
	align-items: center;
	gap: 10px;
}

.footer-payment img {
	height: 25px;
	width: auto;
	opacity: 0.7;
	transition: opacity 0.3s ease;
}

.footer-payment img:hover {
	opacity: 1;
}

/* Responsive */
@media (max-width: 768px) {
	.newsletter-section {
		padding: 40px 0;
	}
	
	.newsletter-content {
		text-align: center;
		margin-bottom: 30px;
	}
	
	.contact-info-section {
		padding: 30px 0;
	}
	
	.contact-info {
		text-align: center;
		margin-bottom: 30px;
	}
	
	.footer-top {
		padding: 40px 0 30px;
	}
	
	.footer-widget {
		margin-bottom: 30px;
	}
	
	.brands-grid {
		grid-template-columns: repeat(4, 1fr);
		gap: 10px;
	}
	
	.footer-payment {
		justify-content: center;
		margin-top: 20px;
	}
	
	.footer-copyright {
		text-align: center;
	}
}
</style>