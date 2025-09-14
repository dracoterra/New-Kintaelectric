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
    
    // Search suggestions styling
    wp_enqueue_style( 'kintaelectric-search-suggestions', kintaelectric_ASSETS_URL . 'css/search-suggestions.css', array(), '1.0.0' );
    
    // Compare integration (simple version)
    if ( class_exists( 'YITH_Woocompare' ) ) {
        wp_enqueue_script( 'kintaelectric-compare-integration', kintaelectric_ASSETS_URL . 'js/compare-integration.js', array( 'jquery' ), '1.0.0', true );
    }
    
    // Native cart updates
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_script( 'kintaelectric-native-cart', kintaelectric_ASSETS_URL . 'js/native-cart-update.js', array( 'jquery', 'wc-add-to-cart' ), '1.0.0', true );
        
        // Localizar script con ajaxurl y datos de WooCommerce
        wp_localize_script( 'kintaelectric-native-cart', 'cart_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'cart_nonce' )
        ));
    }
    
    // Electro Mode Switcher
    wp_enqueue_script( 'kintaelectric-mode-switcher', kintaelectric_ASSETS_URL . 'js/electro-mode-switcher.js', array( 'jquery' ), '1.0.0', true );
    
    // Shop View Switcher
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        wp_enqueue_script( 'kintaelectric-shop-view-switcher', kintaelectric_ASSETS_URL . 'js/shop-view-switcher.js', array( 'jquery' ), '1.0.0', true );
    }
    
    // WooCommerce Custom Styles
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_style( 'kintaelectric-woocommerce-custom', kintaelectric_ASSETS_URL . 'css/hello-commerce-woocommerce.css', array( 'electro-style' ), '1.0.0' );
    }
    
    // HideMaxListItems Script for Filters
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        wp_enqueue_script( 'kintaelectric-hidemaxlistitem', kintaelectric_ASSETS_URL . 'js/hidemaxlistitem.min.js', array( 'jquery' ), '1.0.0', true );
    }
    
    // Logo theme switching CSS
    wp_add_inline_style( 'kintaelectric-style', '
        /* Logo Theme Switching */
        .header-logo-light {
            display: block;
        }
        .header-logo-dark {
            display: none;
        }
        
        /* Dark mode logo switching */
        body.electro-dark .header-logo-light {
            display: none;
        }
        body.electro-dark .header-logo-dark {
            display: block;
        }
        
        /* Mobile logo always visible */
        .header-logo-mobile {
            display: block;
        }
        
        /* Logo placeholder styles */
        .header-logo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 4px;
            color: #6c757d;
            font-size: 12px;
            text-align: center;
            line-height: 1.4;
        }
        
        body.electro-dark .header-logo-placeholder {
            background-color: #343a40;
            border-color: #495057;
            color: #adb5bd;
        }
    ' );
    
    
    
    
    // Localize script for AJAX
    wp_localize_script( 'electro-main', 'electro_options', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'ajax_loader_url' => kintaelectric_ASSETS_URL . 'images/ajax-loader.gif',
        'rtl' => is_rtl() ? '1' : '0',
        'enable_sticky_header' => '1',
        'enable_hh_sticky_header' => '1',
        'enable_live_search' => '1',
        'live_search_limit' => '10',
        'live_search_empty_msg' => __( 'No products found', 'kintaelectric' ),
        'live_search_template' => '<div class="search-suggestion-item"><a href="{{url}}" class="search-suggestion-link"><div class="search-suggestion-image"><img src="{{image}}" alt="{{title}}"></div><div class="search-suggestion-content"><div class="search-suggestion-title">{{title}}</div><div class="search-suggestion-price">{{{price}}}</div></div></a></div>',
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

    // Logo Light (Desktop Light Mode)
    $wp_customize->add_setting( 'kintaelectric_logo_light', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ) );

    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'kintaelectric_logo_light', array(
        'label'       => esc_html__( 'Logo Light (Desktop Light Mode)', 'kintaelectric' ),
        'description' => esc_html__( 'Logo para el modo claro en desktop', 'kintaelectric' ),
        'section'     => 'title_tagline',
        'settings'    => 'kintaelectric_logo_light',
        'mime_type'   => 'image',
        'priority'    => 8,
    ) ) );

    // Logo Dark (Desktop Dark Mode)
    $wp_customize->add_setting( 'kintaelectric_logo_dark', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ) );

    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'kintaelectric_logo_dark', array(
        'label'       => esc_html__( 'Logo Dark (Desktop Dark Mode)', 'kintaelectric' ),
        'description' => esc_html__( 'Logo para el modo oscuro en desktop', 'kintaelectric' ),
        'section'     => 'title_tagline',
        'settings'    => 'kintaelectric_logo_dark',
        'mime_type'   => 'image',
        'priority'    => 9,
    ) ) );

    // Logo Mobile
    $wp_customize->add_setting( 'kintaelectric_logo_mobile', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ) );

    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'kintaelectric_logo_mobile', array(
        'label'       => esc_html__( 'Logo Mobile', 'kintaelectric' ),
        'description' => esc_html__( 'Logo para dispositivos móviles', 'kintaelectric' ),
        'section'     => 'title_tagline',
        'settings'    => 'kintaelectric_logo_mobile',
        'mime_type'   => 'image',
        'priority'    => 10,
    ) ) );

    // Remove default logo control
    $wp_customize->remove_control( 'custom_logo' );

    // Header Style for Homepage
    $wp_customize->add_setting( 'kintaelectric_header_style_homepage', array(
        'default'           => 'v2',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'kintaelectric_header_style_homepage', array(
        'label'       => esc_html__( 'Header Style for Homepage', 'kintaelectric' ),
        'description' => esc_html__( 'Choose your preferred header layout for the homepage', 'kintaelectric' ),
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

    // Header Style for Other Pages
    $wp_customize->add_setting( 'kintaelectric_header_style_other', array(
        'default'           => 'v1',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'kintaelectric_header_style_other', array(
        'label'       => esc_html__( 'Header Style for Other Pages', 'kintaelectric' ),
        'description' => esc_html__( 'Choose your preferred header layout for all other pages', 'kintaelectric' ),
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
        'posts_per_page' => 10,
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
 * Simplified version that works like the simple search
 */
function kintaelectric_products_live_search() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_die( 'WooCommerce not active', 400 );
    }
    
    // Get search term from POST or GET - try multiple parameter names
    $search_term = '';
    $search_params = array( 'search_term', 'q', 's', 'query', 'term', 'terms' );
    
    foreach ( $search_params as $param ) {
        if ( isset( $_POST[ $param ] ) && ! empty( $_POST[ $param ] ) ) {
            $search_term = sanitize_text_field( $_POST[ $param ] );
            break;
        } elseif ( isset( $_GET[ $param ] ) && ! empty( $_GET[ $param ] ) ) {
            $search_term = sanitize_text_field( $_GET[ $param ] );
            break;
        }
    }

    // If no search term, return empty results
    if ( empty( $search_term ) ) {
        wp_send_json( array() );
        return;
    }
    
    // Simple search without complex queries
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 10,
        's' => $search_term,
        'post_status' => 'publish',
    );
    
    $products = new WP_Query( $args );
    $results = array();
    
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
                'url' => get_permalink(),
                'image' => $image_url,
                'price' => $product->get_price_html(),
            );
        }
    }
    
    wp_reset_postdata();
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
        }
        wp_send_json( array() );
        return;
    }

    if ( WP_DEBUG && WP_DEBUG_LOG ) {
    }
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 10,
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
require_once kintaelectric_PATH . '/includes/class-yamm-walker.php';


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
 * Get header style based on current page
 */
function kintaelectric_get_header_style() {
    // Check if we're on the homepage
    if ( is_front_page() || is_home() ) {
        return get_theme_mod( 'kintaelectric_header_style_homepage', 'v2' );
    } else {
        return get_theme_mod( 'kintaelectric_header_style_other', 'v1' );
    }
}

/**
 * Global breadcrumb function for the entire site
 * Uses WooCommerce breadcrumb when available, falls back to custom implementation
 */
function kintaelectric_breadcrumb() {
    // If WooCommerce is active, use its breadcrumb function
    if ( class_exists( 'WooCommerce' ) && function_exists( 'woocommerce_breadcrumb' ) ) {
        // Customize WooCommerce breadcrumb arguments
        $args = array(
            'delimiter'   => '<span class="delimiter"><i class="fa fa-angle-right"></i></span>',
            'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'kintaelectric' ) . '">',
            'wrap_after'  => '</nav>',
            'before'      => '',
            'after'       => '',
            'home'        => _x( 'Hogar', 'breadcrumb', 'kintaelectric' ),
        );
        
        woocommerce_breadcrumb( $args );
    } else {
        // Fallback breadcrumb for non-WooCommerce pages
        kintaelectric_custom_breadcrumb();
    }
}

/**
 * Custom breadcrumb implementation for non-WooCommerce pages
 */
function kintaelectric_custom_breadcrumb() {
    $home_title = _x( 'Hogar', 'breadcrumb', 'kintaelectric' );
    $delimiter = '<span class="delimiter"><i class="fa fa-angle-right"></i></span>';
    
    echo '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'kintaelectric' ) . '">';
    
    // Home link
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $home_title ) . '</a>';
    
    if ( is_category() || is_single() ) {
        echo $delimiter;
        the_category( ' ' );
        if ( is_single() ) {
            echo $delimiter;
            the_title();
        }
    } elseif ( is_page() ) {
        echo $delimiter;
        echo the_title();
    } elseif ( is_search() ) {
        echo $delimiter;
        printf( esc_html__( 'Resultados de búsqueda para: %s', 'kintaelectric' ), get_search_query() );
    } elseif ( is_404() ) {
        echo $delimiter;
        esc_html_e( 'Error 404', 'kintaelectric' );
    } elseif ( is_archive() ) {
        echo $delimiter;
        if ( is_tag() ) {
            echo single_tag_title( '', false );
        } elseif ( is_author() ) {
            echo get_the_author();
        } elseif ( is_date() ) {
            if ( is_year() ) {
                echo get_the_date( 'Y' );
            } elseif ( is_month() ) {
                echo get_the_date( 'F Y' );
            } elseif ( is_day() ) {
                echo get_the_date();
            }
        }
    }
    
    echo '</nav>';
}

/**
 * Get logo based on context (light, dark, mobile)
 */
function kintaelectric_get_logo( $context = 'light' ) {
    $alt_text = get_bloginfo( 'name' );
    
    // Get custom logos from Customizer
    $light_logo_id = get_theme_mod( 'kintaelectric_logo_light' );
    $dark_logo_id = get_theme_mod( 'kintaelectric_logo_dark' );
    $mobile_logo_id = get_theme_mod( 'kintaelectric_logo_mobile' );
    
    // Fallback to default logo if no custom logos are set
    if ( ! $light_logo_id && ! $dark_logo_id && ! $mobile_logo_id && has_custom_logo() ) {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
        
        if ( $logo_url ) {
            return sprintf(
                '<img src="%s" alt="%s" class="header-logo-img header-logo-%s">',
                esc_url( $logo_url ),
                esc_attr( $alt_text ),
                esc_attr( $context )
            );
        }
    }
    
    // Get logo URLs
    $light_logo_url = $light_logo_id ? wp_get_attachment_image_url( $light_logo_id, 'full' ) : '';
    $dark_logo_url = $dark_logo_id ? wp_get_attachment_image_url( $dark_logo_id, 'full' ) : '';
    $mobile_logo_url = $mobile_logo_id ? wp_get_attachment_image_url( $mobile_logo_id, 'full' ) : '';
    
    if ( $context === 'mobile' ) {
        if ( $mobile_logo_url ) {
            return sprintf(
                '<img src="%s" alt="%s" class="header-logo-img header-logo-mobile">',
                esc_url( $mobile_logo_url ),
                esc_attr( $alt_text )
            );
        } else {
            return sprintf(
                '<div class="header-logo-placeholder">%s</div>',
                esc_html__( 'Configura el logo móvil en Personalizar > Identidad del sitio', 'kintaelectric' )
            );
        }
    }
    
    // For desktop, check if we have both logos
    if ( $light_logo_url && $dark_logo_url ) {
        return sprintf(
            '<img src="%s" alt="%s" class="header-logo-img header-logo-light">
             <img src="%s" alt="%s" class="header-logo-img header-logo-dark">',
            esc_url( $light_logo_url ),
            esc_attr( $alt_text ),
            esc_url( $dark_logo_url ),
            esc_attr( $alt_text )
        );
    } elseif ( $light_logo_url ) {
        return sprintf(
            '<img src="%s" alt="%s" class="header-logo-img header-logo-light">
             <div class="header-logo-placeholder">%s</div>',
            esc_url( $light_logo_url ),
            esc_attr( $alt_text ),
            esc_html__( 'Configura el logo dark en Personalizar', 'kintaelectric' )
        );
    } elseif ( $dark_logo_url ) {
        return sprintf(
            '<div class="header-logo-placeholder">%s</div>
             <img src="%s" alt="%s" class="header-logo-img header-logo-dark">',
            esc_html__( 'Configura el logo light en Personalizar', 'kintaelectric' ),
            esc_url( $dark_logo_url ),
            esc_attr( $alt_text )
        );
    } else {
        return sprintf(
            '<div class="header-logo-placeholder">%s</div>',
            esc_html__( 'Configura los logos en Personalizar > Identidad del sitio', 'kintaelectric' )
        );
    }
}

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
        
        // Add script to suppress console errors in admin
        wp_add_inline_script( 'jquery', '
            // Suppress WordPress.com tracking errors in local development
            if (window.location.protocol === "about:") {
                const originalError = console.error;
                console.error = function(...args) {
                    if (args[0] && args[0].includes && args[0].includes("about://pixel.wp.com")) {
                        return; // Suppress WordPress.com tracking errors
                    }
                    originalError.apply(console, args);
                };
            }
        ' );
    }
}
add_action( 'admin_enqueue_scripts', 'kintaelectric_admin_widget_scripts' );

/**
 * Disable WordPress.com tracking in admin to prevent console errors
 */
function kintaelectric_disable_wpcom_tracking() {
    if ( is_admin() ) {
        // Remove WordPress.com stats tracking
        remove_action( 'wp_head', 'wpcom_stats_tracking_code', 100 );
        remove_action( 'wp_footer', 'wpcom_stats_tracking_code', 100 );
        
        // Disable Jetpack stats if active
        if ( class_exists( 'Jetpack' ) ) {
            add_filter( 'jetpack_implode_frontend_css', '__return_false' );
        }
    }
}
add_action( 'init', 'kintaelectric_disable_wpcom_tracking' );


/**
 * Suppress Gutenberg console errors in admin
 */
function kintaelectric_suppress_admin_errors() {
    if ( ! is_admin() ) {
        return;
    }
    
    add_action( 'admin_footer', function() {
        ?>
        <script>
        // Suppress specific console errors in admin
        (function() {
            const originalError = console.error;
            const originalWarn = console.warn;
            
            console.error = function(...args) {
                const message = args[0];
                if (typeof message === 'string') {
                    if (message.includes('already registered') || 
                        message.includes('Store "core/interface"') ||
                        message.includes('Cannot set property')) {
                        return; // Suppress these errors completely
                    }
                }
                originalError.apply(console, args);
            };
            
            console.warn = function(...args) {
                const message = args[0];
                if (typeof message === 'string' && 
                    (message.includes('already registered') || 
                     message.includes('Store "core/interface"'))) {
                    return; // Suppress these warnings completely
                }
                originalWarn.apply(console, args);
            };
        })();
        </script>
        <?php
    }, 1 );
}
add_action( 'admin_init', 'kintaelectric_suppress_admin_errors' );

/**
 * Disable problematic WordPress.com scripts in admin
 */
function kintaelectric_disable_problematic_scripts() {
    if ( ! is_admin() ) {
        return;
    }
    
    // Remove WordPress.com stats and tracking
    remove_action( 'wp_head', 'wpcom_stats_tracking_code', 100 );
    remove_action( 'wp_footer', 'wpcom_stats_tracking_code', 100 );
    
    // Disable WordPress.com admin bar stats
    add_filter( 'show_admin_bar', '__return_false' );
    
    // Remove WordPress.com admin bar stats
    add_action( 'wp_before_admin_bar_render', function() {
        global $wp_admin_bar;
        if ( $wp_admin_bar ) {
            $wp_admin_bar->remove_menu( 'wpcom-admin-bar-notes' );
            $wp_admin_bar->remove_menu( 'wpcom-admin-bar-notes-unread' );
        }
    } );
}
add_action( 'init', 'kintaelectric_disable_problematic_scripts' );

/**
 * Get product categories dynamically for search dropdown
 * Replaces hardcoded categories with dynamic WooCommerce categories
 */
function kintaelectric_get_product_categories() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return '<option value="0" selected="selected">Todas las Categorías</option>';
    }
    
    $categories = get_terms( array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => 0,
        'orderby' => 'name',
        'order' => 'ASC'
    ) );
    
    if ( is_wp_error( $categories ) || empty( $categories ) ) {
        return '<option value="0" selected="selected">Todas las Categorías</option>';
    }
    
    $options = '<option value="0" selected="selected">Todas las Categorías</option>';
    
    foreach ( $categories as $category ) {
        $options .= sprintf(
            '<option class="level-0" value="%s">%s</option>',
            esc_attr( $category->slug ),
            esc_html( $category->name )
        );
    }
    
    return $options;
}



/**
 * AJAX handler simple para obtener contador y total del carrito
 */
function kintaelectric_get_cart_count() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'cart_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not active');
    }
    
    // Asegurar que el carrito esté inicializado
    if (!WC()->cart) {
        wp_send_json_error('Cart not initialized');
    }
    
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_total = WC()->cart->get_cart_total();
    
    wp_send_json_success(array(
        'count' => $cart_count,
        'total' => $cart_total
    ));
}
add_action('wp_ajax_kintaelectric_get_cart_count', 'kintaelectric_get_cart_count');
add_action('wp_ajax_nopriv_kintaelectric_get_cart_count', 'kintaelectric_get_cart_count');

/**
 * Register Shop Sidebar
 */
function kintaelectric_register_shop_sidebar() {
    register_sidebar( array(
        'name'          => esc_html__( 'Shop Sidebar', 'kintaelectric' ),
        'id'            => 'shop-sidebar',
        'description'   => esc_html__( 'Widgets added here will appear on the shop page.', 'kintaelectric' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'kintaelectric_register_shop_sidebar' );

// Incluir y registrar los widgets personalizados
require_once get_template_directory() . '/inc/widgets/class-electro-product-categories-widget.php';
require_once get_template_directory() . '/inc/widgets/class-electro-latest-products-widget.php';
require_once get_template_directory() . '/inc/widgets/class-electro-products-filter-widget.php';

function kintaelectric_register_custom_widgets() {
    register_widget('Electro_Product_Categories_Widget');
    register_widget('Electro_Latest_Products_Widget');
    register_widget('Electro_Products_Filter_Widget');
}

// Desregistrar widget anterior si existe para evitar conflictos
function kintaelectric_unregister_old_widgets() {
    unregister_widget('Electro_Products_Filter_Widget');
}
add_action('widgets_init', 'kintaelectric_unregister_old_widgets', 1);
add_action('widgets_init', 'kintaelectric_register_custom_widgets');

// Integrar filtros con WooCommerce
add_action('woocommerce_product_query', 'kintaelectric_apply_product_filters');

function kintaelectric_apply_product_filters($query) {
    try {
        // Solo aplicar en páginas de productos
        if (!is_shop() && !is_product_taxonomy() && !is_product_tag()) {
            return;
        }

        // Verificar que es una consulta principal
        if (!$query->is_main_query()) {
            return;
        }

        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

    $meta_query = $query->get('meta_query');
    $tax_query = $query->get('tax_query');

    if (!is_array($meta_query)) {
        $meta_query = array();
    }
    if (!is_array($tax_query)) {
        $tax_query = array();
    }

    // Procesar filtros de atributos
    if (isset($_GET) && is_array($_GET)) {
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'filter_') === 0) {
                $attribute_name = str_replace('filter_', '', $key);
                $taxonomy = 'pa_' . $attribute_name;
                
                if (taxonomy_exists($taxonomy) && !empty($value)) {
                    $terms = is_array($value) ? $value : array($value);
                    $terms = array_map('sanitize_text_field', $terms);
                    
                    // Verificar que los términos existen
                    $valid_terms = array();
                    foreach ($terms as $term_slug) {
                        if (term_exists($term_slug, $taxonomy)) {
                            $valid_terms[] = $term_slug;
                        }
                    }
                    
                    if (!empty($valid_terms)) {
                        $tax_query[] = array(
                            'taxonomy' => $taxonomy,
                            'field' => 'slug',
                            'terms' => $valid_terms,
                            'operator' => 'IN'
                        );
                    }
                }
            }
        }
    }

    // Aplicar filtros de precio
    if (isset($_GET['min_price']) || isset($_GET['max_price'])) {
        $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
        $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;
        
        // Validar que los precios sean válidos
        if ($min_price >= 0 && $max_price > $min_price) {
            $meta_query[] = array(
                'key' => '_price',
                'value' => array($min_price, $max_price),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }
    }

        // Aplicar las consultas modificadas
        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $query->set('tax_query', $tax_query);
        }
        
        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
            $query->set('meta_query', $meta_query);
        }
    } catch (Exception $e) {
        // Log del error pero no interrumpir la ejecución
    }
}

// Incluir verificador de plantillas de WooCommerce
require_once kintaelectric_PATH . '/woocommerce/template-version-check.php';

// Incluir script de prueba de compatibilidad (solo en admin)
if (is_admin()) {
    require_once kintaelectric_PATH . '/test-template-compatibility.php';
}

/**
 * Integración AJAX con YITH WooCommerce Wishlist - Versión Optimizada
 */
if (class_exists('YITH_WCWL')) {
    add_action('wp_enqueue_scripts', 'kintaelectric_wishlist_ajax_script');
    
    function kintaelectric_wishlist_ajax_script() {
        if (is_admin()) {
            return;
        }
        
        wp_enqueue_script('jquery');
        
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                var lastWishlistCount = 0;
                var updateInterval;
                
                // Función para actualizar el contador del header
                function updateWishlistHeaderCounter() {
                    $.ajax({
                        url: "' . admin_url('admin-ajax.php') . '",
                        type: "POST",
                        data: {
                            action: "kintaelectric_get_wishlist_count",
                            nonce: "' . wp_create_nonce('wishlist_count_nonce') . '"
                        },
                        success: function(response) {
                            if (response.success && response.data.count !== undefined) {
                                var counter = $("#header-wishlist-count");
                                if (response.data.count > 0) {
                                    counter.text(response.data.count).show();
                                } else {
                                    counter.hide();
                                }
                            }
                        }
                    });
                }
                
                // Función para verificar cambios en la wishlist
                function checkWishlistChanges() {
                    $.ajax({
                        url: "' . admin_url('admin-ajax.php') . '",
                        type: "POST",
                        data: {
                            action: "kintaelectric_get_wishlist_count",
                            nonce: "' . wp_create_nonce('wishlist_count_nonce') . '"
                        },
                        success: function(response) {
                            if (response.success && response.data.count !== undefined) {
                                var currentCount = response.data.count;
                                if (currentCount !== lastWishlistCount) {
                                    var counter = $("#header-wishlist-count");
                                    if (currentCount > 0) {
                                        counter.text(currentCount).show();
                                    } else {
                                        counter.hide();
                                    }
                                    lastWishlistCount = currentCount;
                                }
                            }
                        }
                    });
                }
                
                // Escuchar eventos del plugin YITH Wishlist
                $("body").on("added_to_wishlist removed_from_wishlist", function() {
                    setTimeout(updateWishlistHeaderCounter, 500);
                });
                
                // Escuchar clics en botones de wishlist
                $(document).on("click", ".add_to_wishlist, .remove_from_wishlist", function() {
                    setTimeout(updateWishlistHeaderCounter, 1000);
                });
                
                // Verificación periódica cada 3 segundos (solo por 5 minutos)
                updateInterval = setInterval(checkWishlistChanges, 3000);
                setTimeout(function() {
                    clearInterval(updateInterval);
                }, 300000);
                
                // Actualizar contador al cargar la página
                setTimeout(updateWishlistHeaderCounter, 1000);
            });
        ');
    }
    
    // AJAX handler para obtener el conteo
    add_action('wp_ajax_kintaelectric_get_wishlist_count', 'kintaelectric_get_wishlist_count');
    add_action('wp_ajax_nopriv_kintaelectric_get_wishlist_count', 'kintaelectric_get_wishlist_count');
    
    function kintaelectric_get_wishlist_count() {
        if (!wp_verify_nonce($_POST['nonce'], 'wishlist_count_nonce')) {
            wp_die('Security check failed');
        }
        
        $count = yith_wcwl_count_products();
        
        wp_send_json_success(array(
            'count' => $count
        ));
    }
}

