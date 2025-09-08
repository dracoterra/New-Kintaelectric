<?php
/**
 * Theme functions and definitions
 *
 * @package kintaelectric
 */

use kintaelectric\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'kintaelectric_ELEMENTOR_VERSION', '2.0.0' );
define( 'EHP_THEME_SLUG', 'kintaelectric' );

define( 'kintaelectric_PATH', get_template_directory() );
define( 'kintaelectric_URL', get_template_directory_uri() );
define( 'kintaelectric_ASSETS_PATH', kintaelectric_PATH . '/assets/' );
define( 'kintaelectric_ASSETS_URL', kintaelectric_URL . '/assets/' );
define( 'kintaelectric_SCRIPTS_PATH', kintaelectric_ASSETS_PATH . 'js/' );
define( 'kintaelectric_SCRIPTS_URL', kintaelectric_ASSETS_URL . 'js/' );
define( 'kintaelectric_STYLE_PATH', kintaelectric_ASSETS_PATH . 'css/' );
define( 'kintaelectric_STYLE_URL', kintaelectric_ASSETS_URL . 'css/' );
define( 'kintaelectric_IMAGES_PATH', kintaelectric_ASSETS_PATH . 'images/' );
define( 'kintaelectric_IMAGES_URL', kintaelectric_ASSETS_URL . 'images/' );
define( 'kintaelectric_STARTER_IMAGES_PATH', kintaelectric_IMAGES_PATH . 'starter-content/' );
define( 'kintaelectric_STARTER_IMAGES_URL', kintaelectric_IMAGES_URL . 'starter-content/' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

// Init the Theme class
require kintaelectric_PATH . '/theme.php';

Theme::instance();

/**
 * Enqueue Electro theme assets
 */
function kintaelectric_enqueue_electro_assets() {
    // Google Fonts
    wp_enqueue_style( 'electro-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap', array(), '1.0.0' );
    
    // Electro Font Icons
    wp_enqueue_style( 'font-electro', kintaelectric_ASSETS_URL . 'css/font-electro.css', array(), '3.6.2' );
    
    // Animate CSS
    wp_enqueue_style( 'animate-css', kintaelectric_ASSETS_URL . 'vendor/animate.css/animate.min.css', array(), '3.6.2' );
    
    // Main Electro Styles - CRITICAL: This must load first
    wp_enqueue_style( 'electro-style', kintaelectric_URL . '/style.css', array(), '3.6.3' );
    
    // Font Electro
    wp_enqueue_style( 'electro-font', kintaelectric_ASSETS_URL . 'css/font-electro.css', array('electro-style'), '3.6.2' );
    
    // Elementor Styles
    wp_enqueue_style( 'electro-elementor-style', kintaelectric_URL . '/elementor.css', array('electro-style'), '3.6.2' );
    
    // FontAwesome
    wp_enqueue_style( 'ec-fontawesome', kintaelectric_ASSETS_URL . 'vendor/fontawesome/css/all.min.css', array(), '3.6.2' );
    
    // Dashicons for edit links
    wp_enqueue_style( 'dashicons' );
    
    // Custom Colors - Override CSS variables from customizer
    wp_enqueue_style( 'electro-custom-colors', kintaelectric_ASSETS_URL . 'css/custom-colors.css', array('electro-style'), '3.6.2' );
    
    // JavaScript Files - Solo archivos del tema Electro original
    wp_enqueue_script( 'bootstrap-bundle', kintaelectric_ASSETS_URL . 'js/bootstrap.bundle.min.js', array( 'jquery' ), '3.6.2', true );
    wp_enqueue_script( 'jquery-waypoints', kintaelectric_ASSETS_URL . 'js/jquery.waypoints.min.js', array( 'jquery' ), '3.6.2', true );
    wp_enqueue_script( 'waypoints-sticky', kintaelectric_ASSETS_URL . 'js/waypoints-sticky.min.js', array( 'jquery', 'jquery-waypoints' ), '4.0.1', true );
    wp_enqueue_script( 'owl-carousel', kintaelectric_ASSETS_URL . 'js/owl.carousel.min.js', array( 'jquery' ), '3.6.2', true );
    
    // Typeahead.js (Bloodhound) - REQUERIDO por electro.min.js
    wp_enqueue_script( 'typeahead-bundle', kintaelectric_ASSETS_URL . 'js/vendor/typeahead.bundle.min.js', array( 'jquery' ), '0.11.1', true );
    
    // Handlebars - REQUERIDO por electro.min.js
    wp_enqueue_script( 'handlebars', kintaelectric_ASSETS_URL . 'js/handlebars.min.js', array(), '4.7.8', true );
    
    wp_enqueue_script( 'electro-main', kintaelectric_ASSETS_URL . 'js/electro.min.js', array( 'jquery', 'bootstrap-bundle', 'owl-carousel', 'typeahead-bundle', 'waypoints-sticky', 'handlebars' ), '3.6.2', true );
    
    // Footer scripts
    wp_enqueue_script( 'kintaelectric-footer-scripts', kintaelectric_ASSETS_URL . 'js/footer-scripts.js', array( 'jquery' ), '1.0.0', true );
    
    // Localize script for AJAX
    wp_localize_script( 'electro-main', 'electro_options', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'ajax_loader_url' => kintaelectric_ASSETS_URL . 'images/ajax-loader.gif',
        'rtl' => is_rtl() ? '1' : '0',
        'enable_sticky_header' => '1',
        'enable_hh_sticky_header' => '1',
        'enable_live_search' => '1',
        'live_search_limit' => '5',
        'live_search_empty_msg' => __( 'No products found', 'kintaelectric' ),
        'live_search_template' => '<div class="search-suggestion"><a href="{{url}}"><img src="{{image}}" alt="{{title}}"><span>{{title}}</span><span class="price">{{price}}</span></a></div>',
        'typeahead_options' => array(
            'hint' => false,
            'highlight' => true,
            'minLength' => 1
        ),
        'deal_countdown_text' => array(
            'days_text' => __( 'Days', 'kintaelectric' ),
            'hours_text' => __( 'Hours', 'kintaelectric' ),
            'mins_text' => __( 'Mins', 'kintaelectric' ),
            'secs_text' => __( 'Secs', 'kintaelectric' )
        )
    ) );
}
add_action( 'wp_enqueue_scripts', 'kintaelectric_enqueue_electro_assets' );

/**
 * Add theme support for Electro features
 */
function kintaelectric_add_electro_support() {
    // WooCommerce Support
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    
    // Elementor Support
    add_theme_support( 'elementor' );
    add_theme_support( 'elementor-pro' );
    
    // Post Thumbnails
    add_theme_support( 'post-thumbnails' );
    
    // HTML5 Support
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );
    
    // Custom Logo
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 350,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    
    // Title Tag
    add_theme_support( 'title-tag' );
    
    // Feed Links
    add_theme_support( 'automatic-feed-links' );
}
add_action( 'after_setup_theme', 'kintaelectric_add_electro_support' );

/**
 * Register Electro theme menus
 */
function kintaelectric_register_menus() {
    register_nav_menus( array(
        // Solo menús esenciales
        'primary' => esc_html__( 'Primary Menu', 'kintaelectric' ),
        'all-departments' => esc_html__( 'All Departments Menu', 'kintaelectric' ),
    ) );
}
add_action( 'after_setup_theme', 'kintaelectric_register_menus' );

/**
 * Register Electro theme sidebars
 */
function kintaelectric_register_sidebars() {
    // Shop Sidebar
    register_sidebar( array(
        'name'          => esc_html__( 'Shop Sidebar', 'kintaelectric' ),
        'id'            => 'shop-sidebar',
        'description'   => esc_html__( 'Sidebar for shop pages', 'kintaelectric' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    // Blog Sidebar
    register_sidebar( array(
        'name'          => esc_html__( 'Blog Sidebar', 'kintaelectric' ),
        'id'            => 'blog-sidebar',
        'description'   => esc_html__( 'Sidebar for blog pages', 'kintaelectric' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    // Footer Bottom Widgets
    register_sidebar( array(
        'name'          => esc_html__( 'Footer Contact', 'kintaelectric' ),
        'id'            => 'footer-1',
        'description'   => esc_html__( 'Footer contact section (logo, call us, address, social icons)', 'kintaelectric' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Menu 1', 'kintaelectric' ),
        'id'            => 'footer-2',
        'description'   => esc_html__( 'First footer menu column', 'kintaelectric' ),
        'before_widget' => '<aside id="%1$s" class="widget clearfix %2$s"><div class="body">',
        'after_widget'  => '</div></aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Menu 2', 'kintaelectric' ),
        'id'            => 'footer-3',
        'description'   => esc_html__( 'Second footer menu column', 'kintaelectric' ),
        'before_widget' => '<aside id="%1$s" class="widget clearfix %2$s"><div class="body">',
        'after_widget'  => '</div></aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Menu 3', 'kintaelectric' ),
        'id'            => 'footer-4',
        'description'   => esc_html__( 'Third footer menu column', 'kintaelectric' ),
        'before_widget' => '<aside id="%1$s" class="widget clearfix %2$s"><div class="body">',
        'after_widget'  => '</div></aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    // Footer Newsletter Sidebar
    register_sidebar( array(
        'name'          => esc_html__( 'Footer Newsletter', 'kintaelectric' ),
        'id'            => 'footer-newsletter',
        'description'   => esc_html__( 'Newsletter widget area for footer', 'kintaelectric' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    // Top Footer Widget Areas
    for ( $i = 1; $i <= 4; $i++ ) {
        register_sidebar( array(
            'name'          => sprintf( esc_html__( 'Top Footer %d', 'kintaelectric' ), $i ),
            'id'            => 'top-footer-' . $i,
            'description'   => sprintf( esc_html__( 'Top Footer widget area %d', 'kintaelectric' ), $i ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
    }

    // Canvas Menu Widget Area
    register_sidebar( array(
        'name'          => esc_html__( 'Canvas Menu', 'kintaelectric' ),
        'id'            => 'canvas-menu',
        'description'   => esc_html__( 'Off-canvas navigation menu widget area', 'kintaelectric' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'kintaelectric_register_sidebars' );


/**
 * Electro theme customizer options
 */
function kintaelectric_customize_register( $wp_customize ) {
    // Header Section
    $wp_customize->add_section( 'kintaelectric_header', array(
        'title'    => esc_html__( 'Header Settings', 'kintaelectric' ),
        'priority' => 30,
    ) );

    // Header Style
    $wp_customize->add_setting( 'kintaelectric_header_style', array(
        'default'           => 'v1',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'kintaelectric_header_style', array(
        'label'       => esc_html__( 'Header Style', 'kintaelectric' ),
        'description' => esc_html__( 'Choose your preferred header layout', 'kintaelectric' ),
        'section'     => 'kintaelectric_header',
        'type'        => 'select',
        'choices'     => array(
            'v1' => esc_html__( 'Header v1 - Classic with Top Bar', 'kintaelectric' ),
            'v2' => esc_html__( 'Header v2 - Modern Layout', 'kintaelectric' ),
            'v3' => esc_html__( 'Header v3 - Minimalist', 'kintaelectric' ),
            'v4' => esc_html__( 'Header v4 - Top Bar + Header', 'kintaelectric' ),
            'v5' => esc_html__( 'Header v5 - Modern Off-Canvas', 'kintaelectric' ),
        ),
    ) );

    // Mode Switcher Setting
    $wp_customize->add_setting( 'kintaelectric_enable_mode_switcher', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );

    $wp_customize->add_control( 'kintaelectric_enable_mode_switcher', array(
        'label'       => esc_html__( 'Enable Dark/Light Mode Switcher', 'kintaelectric' ),
        'description' => esc_html__( 'Show the dark/light mode toggle button', 'kintaelectric' ),
        'section'     => 'kintaelectric_header',
        'type'        => 'checkbox',
    ) );


    // Footer Section
    $wp_customize->add_section( 'kintaelectric_footer', array(
        'title'    => esc_html__( 'Footer Settings', 'kintaelectric' ),
        'priority' => 35,
    ) );

    // Footer Style
    $wp_customize->add_setting( 'kintaelectric_footer_style', array(
        'default'           => 'v1',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'kintaelectric_footer_style', array(
        'label'       => esc_html__( 'Footer Style', 'kintaelectric' ),
        'description' => esc_html__( 'Choose your preferred footer layout', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'select',
        'choices'     => array(
            'v1' => esc_html__( 'Footer v1 - Classic with Newsletter', 'kintaelectric' ),
            'v2' => esc_html__( 'Footer v2 - Modern with Brand', 'kintaelectric' ),
            'v3' => esc_html__( 'Footer v3 - Minimalist', 'kintaelectric' ),
        ),
    ) );

    // Show Copyright
    $wp_customize->add_setting( 'kintaelectric_show_copyright', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );

    $wp_customize->add_control( 'kintaelectric_show_copyright', array(
        'label'       => esc_html__( 'Show Copyright Text', 'kintaelectric' ),
        'description' => esc_html__( 'Display copyright information in the footer', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'checkbox',
    ) );

    // Copyright Text
    $wp_customize->add_setting( 'kintaelectric_copyright_text', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );

    $wp_customize->add_control( 'kintaelectric_copyright_text', array(
        'label'       => esc_html__( 'Copyright Text', 'kintaelectric' ),
        'description' => esc_html__( 'Custom copyright text. Leave empty to use default. You can use HTML tags.', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'textarea',
    ) );

    // Show Payment Logo
    $wp_customize->add_setting( 'kintaelectric_show_payment', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );

    $wp_customize->add_control( 'kintaelectric_show_payment', array(
        'label'       => esc_html__( 'Show Payment Logo', 'kintaelectric' ),
        'description' => esc_html__( 'Display payment methods logo in the footer', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'checkbox',
    ) );

    // Payment Logo URL
    $wp_customize->add_setting( 'kintaelectric_payment_logo_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'kintaelectric_payment_logo_url', array(
        'label'       => esc_html__( 'Payment Logo URL', 'kintaelectric' ),
        'description' => esc_html__( 'URL of the payment methods logo image', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'url',
    ) );

    // Payment Logo Alt Text
    $wp_customize->add_setting( 'kintaelectric_payment_logo_alt', array(
        'default'           => esc_html__( 'Payment Methods', 'kintaelectric' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'kintaelectric_payment_logo_alt', array(
        'label'       => esc_html__( 'Payment Logo Alt Text', 'kintaelectric' ),
        'description' => esc_html__( 'Alternative text for the payment logo', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'text',
    ) );

    // Payment Logo Width
    $wp_customize->add_setting( 'kintaelectric_payment_logo_width', array(
        'default'           => 324,
        'sanitize_callback' => 'absint',
    ) );

    $wp_customize->add_control( 'kintaelectric_payment_logo_width', array(
        'label'       => esc_html__( 'Payment Logo Width', 'kintaelectric' ),
        'description' => esc_html__( 'Width of the payment logo in pixels', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'number',
        'input_attrs' => array(
            'min' => 1,
            'max' => 1000,
        ),
    ) );

    // Payment Logo Height
    $wp_customize->add_setting( 'kintaelectric_payment_logo_height', array(
        'default'           => 38,
        'sanitize_callback' => 'absint',
    ) );

    $wp_customize->add_control( 'kintaelectric_payment_logo_height', array(
        'label'       => esc_html__( 'Payment Logo Height', 'kintaelectric' ),
        'description' => esc_html__( 'Height of the payment logo in pixels', 'kintaelectric' ),
        'section'     => 'kintaelectric_footer',
        'type'        => 'number',
        'input_attrs' => array(
            'min' => 1,
            'max' => 200,
        ),
    ) );

    // Color Scheme Section
    $wp_customize->add_section( 'kintaelectric_colors', array(
        'title'    => esc_html__( 'Color Scheme', 'kintaelectric' ),
        'priority' => 25,
    ) );

    // Primary Color
    $wp_customize->add_setting( 'kintaelectric_primary_color', array(
        'default'           => '#fed700',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kintaelectric_primary_color', array(
        'label'       => esc_html__( 'Primary Color', 'kintaelectric' ),
        'description' => esc_html__( 'Main brand color used throughout the theme', 'kintaelectric' ),
        'section'     => 'kintaelectric_colors',
    ) ) );

    // Primary Dark Color
    $wp_customize->add_setting( 'kintaelectric_primary_dark', array(
        'default'           => '#e0a800',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kintaelectric_primary_dark', array(
        'label'       => esc_html__( 'Primary Dark Color', 'kintaelectric' ),
        'description' => esc_html__( 'Darker shade of primary color for hover states', 'kintaelectric' ),
        'section'     => 'kintaelectric_colors',
    ) ) );

    // Secondary Color
    $wp_customize->add_setting( 'kintaelectric_secondary_color', array(
        'default'           => '#6c757d',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kintaelectric_secondary_color', array(
        'label'       => esc_html__( 'Secondary Color', 'kintaelectric' ),
        'description' => esc_html__( 'Secondary brand color', 'kintaelectric' ),
        'section'     => 'kintaelectric_colors',
    ) ) );

    // Success Color
    $wp_customize->add_setting( 'kintaelectric_success_color', array(
        'default'           => '#198754',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kintaelectric_success_color', array(
        'label'       => esc_html__( 'Success Color', 'kintaelectric' ),
        'description' => esc_html__( 'Color for success messages and positive actions', 'kintaelectric' ),
        'section'     => 'kintaelectric_colors',
    ) ) );

    // Danger Color
    $wp_customize->add_setting( 'kintaelectric_danger_color', array(
        'default'           => '#dc3545',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kintaelectric_danger_color', array(
        'label'       => esc_html__( 'Danger Color', 'kintaelectric' ),
        'description' => esc_html__( 'Color for error messages and dangerous actions', 'kintaelectric' ),
        'section'     => 'kintaelectric_colors',
    ) ) );

    // Dark Color
    $wp_customize->add_setting( 'kintaelectric_dark_color', array(
        'default'           => '#333e48',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kintaelectric_dark_color', array(
        'label'       => esc_html__( 'Dark Color', 'kintaelectric' ),
        'description' => esc_html__( 'Dark color for text and backgrounds', 'kintaelectric' ),
        'section'     => 'kintaelectric_colors',
    ) ) );

    // Light Color
    $wp_customize->add_setting( 'kintaelectric_light_color', array(
        'default'           => '#f8f9fa',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kintaelectric_light_color', array(
        'label'       => esc_html__( 'Light Color', 'kintaelectric' ),
        'description' => esc_html__( 'Light color for backgrounds and subtle elements', 'kintaelectric' ),
        'section'     => 'kintaelectric_colors',
    ) ) );

    // Logo Settings
    $wp_customize->add_setting( 'kintaelectric_logo_width', array(
        'default'           => '200',
        'sanitize_callback' => 'absint',
    ) );

    $wp_customize->add_control( 'kintaelectric_logo_width', array(
        'label'       => esc_html__( 'Logo Width (px)', 'kintaelectric' ),
        'description' => esc_html__( 'Set the maximum width for your logo', 'kintaelectric' ),
        'section'     => 'kintaelectric_header',
        'type'        => 'number',
        'input_attrs' => array(
            'min' => 50,
            'max' => 500,
            'step' => 10,
        ),
    ) );

    // Homepage Templates Section


    // Social Media Links
    $wp_customize->add_section( 'kintaelectric_social', array(
        'title'    => esc_html__( 'Social Media', 'kintaelectric' ),
        'priority' => 40,
    ) );

    $social_networks = array(
        'facebook'  => 'Facebook',
        'twitter'   => 'Twitter',
        'instagram' => 'Instagram',
        'youtube'   => 'YouTube',
        'linkedin'  => 'LinkedIn',
    );

    foreach ( $social_networks as $network => $label ) {
        $wp_customize->add_setting( "kintaelectric_{$network}_url", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );

        $wp_customize->add_control( "kintaelectric_{$network}_url", array(
            'label'   => sprintf( esc_html__( '%s URL', 'kintaelectric' ), $label ),
            'section' => 'kintaelectric_social',
            'type'    => 'url',
        ) );
    }
}
add_action( 'customize_register', 'kintaelectric_customize_register' );

/**
 * Generate dynamic CSS based on customizer values
 */
function kintaelectric_generate_dynamic_css() {
    $primary_color = get_theme_mod( 'kintaelectric_primary_color', '#fed700' );
    $primary_dark = get_theme_mod( 'kintaelectric_primary_dark', '#e0a800' );
    $secondary_color = get_theme_mod( 'kintaelectric_secondary_color', '#6c757d' );
    $success_color = get_theme_mod( 'kintaelectric_success_color', '#198754' );
    $danger_color = get_theme_mod( 'kintaelectric_danger_color', '#dc3545' );
    $dark_color = get_theme_mod( 'kintaelectric_dark_color', '#333e48' );
    $light_color = get_theme_mod( 'kintaelectric_light_color', '#f8f9fa' );
    
    $css = "
    :root {
        --kintaelectric-primary-color: {$primary_color};
        --kintaelectric-primary-hover: {$primary_dark};
        --kintaelectric-secondary-color: {$secondary_color};
        --kintaelectric-success-color: {$success_color};
        --kintaelectric-danger-color: {$danger_color};
        --kintaelectric-dark-color: {$dark_color};
        --kintaelectric-light-color: {$light_color};
    }
    ";
    
    return $css;
}

/**
 * Add dynamic CSS to head
 */
function kintaelectric_add_dynamic_css() {
    $dynamic_css = kintaelectric_generate_dynamic_css();
    echo '<style type="text/css" id="kintaelectric-dynamic-css">' . $dynamic_css . '</style>';
}
add_action( 'wp_head', 'kintaelectric_add_dynamic_css' );


/**
 * WooCommerce integration functions
 */
function kintaelectric_woocommerce_setup() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Remove default WooCommerce styles
    add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

    // Add custom WooCommerce support
    add_theme_support( 'woocommerce', array(
        'thumbnail_image_width' => 300,
        'single_image_width'    => 600,
        'product_grid'          => array(
            'default_rows'    => 3,
            'min_rows'        => 2,
            'max_rows'        => 8,
            'default_columns' => 4,
            'min_columns'     => 2,
            'max_columns'     => 5,
        ),
    ) );
}
add_action( 'after_setup_theme', 'kintaelectric_woocommerce_setup' );

/**
 * Custom WooCommerce template functions
 */
function kintaelectric_woocommerce_template_functions() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Remove default WooCommerce actions
    remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
    remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
    remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

    // Add custom WooCommerce actions
    add_action( 'woocommerce_before_main_content', 'kintaelectric_woocommerce_wrapper_start', 10 );
    add_action( 'woocommerce_after_main_content', 'kintaelectric_woocommerce_wrapper_end', 10 );
}
add_action( 'init', 'kintaelectric_woocommerce_template_functions' );

/**
 * WooCommerce wrapper start
 */
function kintaelectric_woocommerce_wrapper_start() {
    echo '<div class="container"><div class="row"><div class="col-12">';
}

/**
 * WooCommerce wrapper end
 */
function kintaelectric_woocommerce_wrapper_end() {
    echo '</div></div></div>';
}

/**
 * Custom WooCommerce product loop
 */
function kintaelectric_woocommerce_product_loop() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Custom product loop template
    add_action( 'woocommerce_before_shop_loop_item', 'kintaelectric_product_loop_start', 5 );
    add_action( 'woocommerce_after_shop_loop_item', 'kintaelectric_product_loop_end', 25 );
}
add_action( 'init', 'kintaelectric_woocommerce_product_loop' );

/**
 * Product loop start
 */
function kintaelectric_product_loop_start() {
    echo '<div class="product-item">';
}

/**
 * Product loop end
 */
function kintaelectric_product_loop_end() {
    echo '</div>';
}

/**
 * Electro theme AJAX functions
 */
function kintaelectric_ajax_functions() {
    // Live search
    add_action( 'wp_ajax_kintaelectric_live_search', 'kintaelectric_live_search' );
    add_action( 'wp_ajax_nopriv_kintaelectric_live_search', 'kintaelectric_live_search' );
    
    // Products live search (for Electro theme compatibility)
    add_action( 'wp_ajax_products_live_search', 'kintaelectric_products_live_search' );
    add_action( 'wp_ajax_nopriv_products_live_search', 'kintaelectric_products_live_search' );
    
    // Fallback for simple AJAX search
    add_action( 'wp_ajax_get_ajax_search', 'kintaelectric_get_ajax_search' );
    add_action( 'wp_ajax_nopriv_get_ajax_search', 'kintaelectric_get_ajax_search' );

    // Add to cart
    add_action( 'wp_ajax_kintaelectric_add_to_cart', 'kintaelectric_add_to_cart' );
    add_action( 'wp_ajax_nopriv_kintaelectric_add_to_cart', 'kintaelectric_add_to_cart' );
}
add_action( 'init', 'kintaelectric_ajax_functions' );

/**
 * Live search function
 */
function kintaelectric_live_search() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_die();
    }

    $search_term = sanitize_text_field( $_POST['search_term'] );
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 5,
        's'              => $search_term,
        'post_status'    => 'publish',
    );

    $products = new WP_Query( $args );
    $results = array();

    if ( $products->have_posts() ) {
        while ( $products->have_posts() ) {
            $products->the_post();
            global $product;
            
            $results[] = array(
                'title' => get_the_title(),
                'url'   => get_permalink(),
                'image' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
                'price' => $product->get_price_html(),
            );
        }
    }

    wp_reset_postdata();
    wp_send_json( $results );
}

/**
 * Products live search function (for Electro theme compatibility)
 */
function kintaelectric_products_live_search() {
    // Enable error logging
    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'AJAX products_live_search called' );
        error_log( 'GET parameters: ' . print_r( $_GET, true ) );
        error_log( 'POST parameters: ' . print_r( $_POST, true ) );
    }

    if ( ! class_exists( 'WooCommerce' ) ) {
        if ( WP_DEBUG && WP_DEBUG_LOG ) {
            error_log( 'WooCommerce not active in products_live_search' );
        }
        wp_die( 'WooCommerce not active', 400 );
    }

    // Check if fn parameter is get_ajax_search (make it optional for now)
    if ( isset( $_GET['fn'] ) && $_GET['fn'] !== 'get_ajax_search' ) {
        if ( WP_DEBUG && WP_DEBUG_LOG ) {
            error_log( 'Invalid fn parameter: ' . $_GET['fn'] );
        }
        wp_die( 'Invalid function parameter', 400 );
    }

    // Get search term from POST or GET - try multiple parameter names
    $search_term = '';
    $search_params = array( 'search_term', 'q', 's', 'query', 'term' );
    
    foreach ( $search_params as $param ) {
        if ( isset( $_POST[ $param ] ) && ! empty( $_POST[ $param ] ) ) {
            $search_term = sanitize_text_field( $_POST[ $param ] );
            break;
        } elseif ( isset( $_GET[ $param ] ) && ! empty( $_GET[ $param ] ) ) {
            $search_term = sanitize_text_field( $_GET[ $param ] );
            break;
        }
    }

    // If no search term, return empty results instead of error
    if ( empty( $search_term ) ) {
        if ( WP_DEBUG && WP_DEBUG_LOG ) {
            error_log( 'Empty search term in products_live_search, returning empty results' );
        }
        wp_send_json( array() );
        return;
    }

    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'Search term: ' . $search_term );
    }
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 5,
        's'              => $search_term,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'slug',
                'terms'    => array( 'exclude-from-search' ),
                'operator' => 'NOT IN',
            ),
        ),
    );

    $products = new WP_Query( $args );
    $results = array();

    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'Found ' . $products->found_posts . ' products for search: ' . $search_term );
    }

    if ( $products->have_posts() ) {
        while ( $products->have_posts() ) {
            $products->the_post();
            global $product;
            
            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
            if ( ! $image_url ) {
                $image_url = wc_placeholder_img_src( 'thumbnail' );
            }
            
            $results[] = array(
                'title' => get_the_title(),
                'url'   => get_permalink(),
                'image' => $image_url,
                'price' => $product->get_price_html(),
            );
        }
    }

    wp_reset_postdata();
    
    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'Returning ' . count( $results ) . ' results' );
    }
    
    wp_send_json( $results );
}

/**
 * Simple AJAX search function (fallback)
 */
function kintaelectric_get_ajax_search() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_die( 'WooCommerce not active', 400 );
    }

    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'AJAX get_ajax_search called' );
        error_log( 'GET parameters: ' . print_r( $_GET, true ) );
        error_log( 'POST parameters: ' . print_r( $_POST, true ) );
    }

    // Get search term from various sources
    $search_term = '';
    $search_params = array( 'search_term', 'q', 's', 'query', 'term' );
    
    foreach ( $search_params as $param ) {
        if ( isset( $_POST[ $param ] ) && ! empty( $_POST[ $param ] ) ) {
            $search_term = sanitize_text_field( $_POST[ $param ] );
            break;
        } elseif ( isset( $_GET[ $param ] ) && ! empty( $_GET[ $param ] ) ) {
            $search_term = sanitize_text_field( $_GET[ $param ] );
            break;
        }
    }

    if ( empty( $search_term ) ) {
        if ( WP_DEBUG && WP_DEBUG_LOG ) {
            error_log( 'Empty search term in get_ajax_search, returning empty results' );
        }
        wp_send_json( array() );
        return;
    }

    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'Search term: ' . $search_term );
    }
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 5,
        's'              => $search_term,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'slug',
                'terms'    => array( 'exclude-from-search' ),
                'operator' => 'NOT IN',
            ),
        ),
    );

    $products = new WP_Query( $args );
    $results = array();

    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'Found ' . $products->found_posts . ' products for search: ' . $search_term );
    }

    if ( $products->have_posts() ) {
        while ( $products->have_posts() ) {
            $products->the_post();
            global $product;
            
            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
            if ( ! $image_url ) {
                $image_url = wc_placeholder_img_src( 'thumbnail' );
            }
            
            $results[] = array(
                'title' => get_the_title(),
                'url'   => get_permalink(),
                'image' => $image_url,
                'price' => $product->get_price_html(),
            );
        }
    }

    wp_reset_postdata();
    
    if ( WP_DEBUG && WP_DEBUG_LOG ) {
        error_log( 'Returning ' . count( $results ) . ' results' );
    }
    
    wp_send_json( $results );
}

/**
 * Add to cart function
 */
function kintaelectric_add_to_cart() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_die();
    }

    $product_id = intval( $_POST['product_id'] );
    $quantity = intval( $_POST['quantity'] );

    if ( $product_id && $quantity ) {
        WC()->cart->add_to_cart( $product_id, $quantity );
        wp_send_json_success( array(
            'message' => esc_html__( 'Product added to cart', 'kintaelectric' ),
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ) );
    } else {
        wp_send_json_error( array(
            'message' => esc_html__( 'Error adding product to cart', 'kintaelectric' ),
        ) );
    }
}

/**
 * Include custom widgets
 */
require_once kintaelectric_PATH . '/includes/widgets/class-newsletter-widget.php';
require_once kintaelectric_PATH . '/includes/widgets/class-products-widget.php';
require_once kintaelectric_PATH . '/includes/widgets/class-image-widget.php';
require_once kintaelectric_PATH . '/includes/widgets/class-footer-call-us-widget.php';
require_once kintaelectric_PATH . '/includes/widgets/class-footer-address-widget.php';
require_once kintaelectric_PATH . '/includes/widgets/class-footer-social-icons-widget.php';
require_once kintaelectric_PATH . '/includes/widgets/class-footer-menu-widget.php';
require_once kintaelectric_PATH . '/includes/widgets/class-canvas-menu-widget.php';


/**
 * Register custom widgets
 */
function kintaelectric_register_widgets() {
    register_widget( 'KintaElectric_Newsletter_Widget' );
    register_widget( 'KintaElectric_Products_Widget' );
    register_widget( 'KintaElectric_Image_Widget' );
    register_widget( 'Footer_Call_Us_Widget' );
    register_widget( 'Footer_Address_Widget' );
    register_widget( 'Footer_Social_Icons_Widget' );
    register_widget( 'Footer_Menu_Widget' );
    register_widget( 'KintaElectric_Canvas_Menu_Widget' );
}
add_action( 'widgets_init', 'kintaelectric_register_widgets' );

/**
 * Enqueue admin scripts for widget functionality
 */
function kintaelectric_admin_widget_scripts( $hook ) {
    if ( $hook === 'widgets.php' || $hook === 'customize.php' ) {
        // Enqueue media uploader and jQuery
        wp_enqueue_media();
        wp_enqueue_script( 'jquery' );
        
        // Enqueue our custom admin widgets script
        wp_enqueue_script( 
            'kintaelectric-admin-widgets', 
            kintaelectric_ASSETS_URL . 'js/admin-widgets.js', 
            array( 'jquery', 'media-upload', 'media-views' ), 
            '1.0.0', 
            true 
        );
    }
}
add_action( 'admin_enqueue_scripts', 'kintaelectric_admin_widget_scripts' );
