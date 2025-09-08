<?php
/**
 * The template for displaying the footer.
 *
 * Contains the body & html closing tags.
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
	/**
	 * Display default footer filter.
	 *
	 * @param bool $display Display default footer.
	 */
	if ( apply_filters( 'kintaelectric-theme/display-default-footer', true ) ) {
		// Get footer style from customizer
		$footer_style = get_theme_mod( 'kintaelectric_footer_style', 'v1' );
		
		// Load the appropriate footer template
		$footer_template = 'template-parts/footer-' . $footer_style;
		
		if ( locate_template( $footer_template . '.php' ) ) {
			get_template_part( $footer_template );
		} else {
			// Fallback to default footer
			get_template_part( 'template-parts/footer' );
		}
	}
}
?>

<?php wp_footer(); ?>
<div class="back-to-top-wrapper position-absolute bottom-0 pe-none">
	<a href="#page"
		class="btn btn-secondary shadows rounded-cricle d-flex align-items-center justify-content-center p-0 pe-auto position-sticky position-fixed back-to-top-link "
		aria-label="Scroll to Top"><i class="fa fa-angle-up"></i></a>
</div>
<div class="electro-overlay"></div>
</body>
</html>
