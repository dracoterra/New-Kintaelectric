<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$viewport_content = apply_filters( 'kintaelectric-theme/viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'kintaelectric-theme/enable_skip_link', true );
$skip_link_url = apply_filters( 'kintaelectric-theme/skip_link_url', '#content' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ( $enable_skip_link ) { ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>"><?php echo esc_html__( 'Skip to content', 'kintaelectric' ); ?></a>
<?php } ?>

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	/**
	 * Display default header filter.
	 *
	 * @param bool $display Display default header.
	 */
	if ( apply_filters( 'kintaelectric-theme/display-default-header', true ) ) {
		// Get header style from customizer
		$header_style = get_theme_mod( 'kintaelectric_header_style', 'v1' );
		
		// Load the appropriate header template
		$header_template = 'template-parts/header-' . $header_style;
		
		if ( locate_template( $header_template . '.php' ) ) {
			get_template_part( $header_template );
		} else {
			// Fallback to default header
			get_template_part( 'template-parts/header' );
		}
	}
}
