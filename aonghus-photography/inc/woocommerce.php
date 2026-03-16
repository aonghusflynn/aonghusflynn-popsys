<?php
/**
 * WooCommerce Integration
 *
 * Declares theme support and removes default WC styles.
 * All shop visuals are handled by assets/css/shop.css.
 *
 * @package Aonghus_Photography
 */

// Declare WooCommerce support so WC knows the theme is compatible.
add_action( 'after_setup_theme', function () {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
} );

// Remove all default WooCommerce stylesheet enqueues.
// Our shop.css handles all WC visual styling.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Keep breadcrumb but update delimiter to match minimal aesthetic.
add_filter( 'woocommerce_breadcrumb_defaults', function ( $defaults ) {
    $defaults['delimiter'] = ' &rsaquo; ';
    return $defaults;
} );
