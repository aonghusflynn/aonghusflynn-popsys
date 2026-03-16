<?php
/**
 * aonghus-photography functions and definitions.
 */

/**
 * Theme setup.
 */
function aonghus_photography_setup() {
    load_theme_textdomain( 'aonghus-photography', get_template_directory() . '/languages' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );

    register_nav_menus( [
        'primary' => esc_html__( 'Primary Menu', 'aonghus-photography' ),
    ] );
}
add_action( 'after_setup_theme', 'aonghus_photography_setup' );

/**
 * Set content width.
 */
function aonghus_photography_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'aonghus_photography_content_width', 1200 );
}
add_action( 'after_setup_theme', 'aonghus_photography_content_width', 0 );

/**
 * Enqueue scripts and styles.
 */
function aonghus_photography_scripts() {
    $v = wp_get_theme()->get( 'Version' );

    // main.css loads on every page
    wp_enqueue_style( 'aonghus-main', get_template_directory_uri() . '/assets/css/main.css', [], $v );

    // navigation.js loads on every page (mobile hamburger needed everywhere)
    wp_enqueue_script( 'aonghus-navigation', get_template_directory_uri() . '/assets/js/navigation.js', [], $v, true );

    // Homepage only
    if ( is_front_page() ) {
        wp_enqueue_style(  'aonghus-slideshow', get_template_directory_uri() . '/assets/css/slideshow.css', [ 'aonghus-main' ], $v );
        wp_enqueue_script( 'aonghus-slideshow', get_template_directory_uri() . '/assets/js/slideshow.js',  [], $v, true );
    }

    // Single project pages only
    if ( is_singular( 'photo_project' ) ) {
        wp_enqueue_style( 'aonghus-project', get_template_directory_uri() . '/assets/css/project.css', [ 'aonghus-main' ], $v );

        // GLightbox CDN — registered first so aonghus-lightbox can declare it as a dependency.
        // Pre-pulled from Task 8; no further GLightbox changes needed in that task.
        wp_enqueue_style(
            'glightbox',
            'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
            [],
            '3.3.0'
        );
        wp_enqueue_script(
            'glightbox',
            'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
            [],
            '3.3.0',
            true
        );

        wp_enqueue_script( 'aonghus-lightbox', get_template_directory_uri() . '/assets/js/lightbox.js', [ 'glightbox' ], $v, true );
    }

    // WooCommerce pages only
    if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
        wp_enqueue_style( 'aonghus-shop', get_template_directory_uri() . '/assets/css/shop.css', [ 'aonghus-main' ], $v );
    }
}
add_action( 'wp_enqueue_scripts', 'aonghus_photography_scripts' );

// Load theme components
require get_template_directory() . '/inc/post-types.php';
require get_template_directory() . '/inc/acf-fields.php';
require get_template_directory() . '/inc/woocommerce.php';
