<?php
namespace kintaelectric\Modules\Theme\Components;

use kintaelectric\Includes\Utils;
// Removed premium settings controller
use kintaelectric\Modules\Theme\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Theme_Support {

	/**
	 * @return void
	 */
	public function setup() {
		if ( is_admin() ) {
			$this->maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'kintaelectric-theme/register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'kintaelectric' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'kintaelectric' ) ] );
		}

		// Registrar áreas de widgets en un hook más tardío
		if ( apply_filters( 'kintaelectric-theme/register_widgets', true ) ) {
			add_action( 'widgets_init', [ $this, 'register_widget_areas' ] );
		}

		if ( apply_filters( 'kintaelectric-theme/post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'kintaelectric-theme/add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support( 'widgets' );
			add_theme_support( 'customize-selective-refresh-widgets' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
					'navigation-widgets',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( kintaelectric_STYLE_PATH . 'editor-styles.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );
			add_theme_support( 'editor-styles' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'kintaelectric-theme/add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );

				if ( Utils::is_woocommerce_active() ) {
					add_theme_support( 'hello-plus-menu-cart' );
				}
			}

			add_theme_support( 'starter-content', $this->get_starter_content() );
		}
	}

	public function get_starter_content(): array {
		$content = [
			'options' => [
				'page_on_front' => '{{home}}',
				'show_on_front' => 'page',
			],
			'posts' => [
				'home' => require kintaelectric_PATH . '/modules/theme/starter-content/home.php',
			],
		];

		return apply_filters( 'kintaelectric-theme/starter-content', $content );
	}

	protected function maybe_update_theme_version_in_db() {
		// The theme version saved in the database.
		$theme_db_version = get_option( Module::kintaelectric_THEME_VERSION_OPTION );

		// If the 'hello_plus_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
		if ( ! $theme_db_version || version_compare( $theme_db_version, kintaelectric_ELEMENTOR_VERSION, '<' ) ) {
			update_option( Module::kintaelectric_THEME_VERSION_OPTION, kintaelectric_ELEMENTOR_VERSION );
		}
	}

	public function add_description_meta_tag(): void {
		if ( false ) { // Always show description meta tag for Kinta Electric
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();

		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . PHP_EOL;
	}

	public function add_header_upsale_data( $data ) {
		$data['description'] = esc_html__( 'Add mega menus, custom menu carts, login buttons and more with  Elementor Pro', 'kintaelectric' );
		$data['upgrade_url'] = 'https://go.elementor.com/hello-plus-commerce-header-pro/';
		return $data;
	}

	/**
	 * Registrar áreas de widgets para Kinta Electric
	 */
	public function register_widget_areas() {
		// Sidebar principal
		register_sidebar( [
			'name'          => esc_html__( 'Sidebar Principal', 'kintaelectric' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Sidebar principal del sitio', 'kintaelectric' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		] );

		// Footer widgets
		register_sidebar( [
			'name'          => esc_html__( 'Footer 1', 'kintaelectric' ),
			'id'            => 'footer-1',
			'description'   => esc_html__( 'Primera columna del footer', 'kintaelectric' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		] );

		register_sidebar( [
			'name'          => esc_html__( 'Footer 2', 'kintaelectric' ),
			'id'            => 'footer-2',
			'description'   => esc_html__( 'Segunda columna del footer', 'kintaelectric' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		] );

		register_sidebar( [
			'name'          => esc_html__( 'Footer 3', 'kintaelectric' ),
			'id'            => 'footer-3',
			'description'   => esc_html__( 'Tercera columna del footer', 'kintaelectric' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		] );

		register_sidebar( [
			'name'          => esc_html__( 'Footer 4', 'kintaelectric' ),
			'id'            => 'footer-4',
			'description'   => esc_html__( 'Cuarta columna del footer', 'kintaelectric' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		] );

		// Widgets específicos para WooCommerce
		if ( class_exists( 'WooCommerce' ) ) {
			register_sidebar( [
				'name'          => esc_html__( 'Sidebar Tienda', 'kintaelectric' ),
				'id'            => 'shop-sidebar',
				'description'   => esc_html__( 'Sidebar para la tienda de WooCommerce', 'kintaelectric' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			] );
		}
	}

	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'setup' ] );
		add_action( 'wp_head', [ $this, 'add_description_meta_tag' ] );
		add_filter( 'helloplus_header_upsale_data', [ $this, 'add_header_upsale_data' ] );
	}
}
