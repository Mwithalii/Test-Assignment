<?php
if (! defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', 'sfchild_enqueue_styles');
function sfchild_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'),
        wp_get_theme()->get('Version')
    );
}

// Include CPT registration (library/cpt.php)
if ( file_exists( get_stylesheet_directory() . '/library/cpt.php' ) ) {
    require_once get_stylesheet_directory() . '/library/cpt.php';
}

// Include ACF fields (library/acf-fields.php)
if ( file_exists( get_stylesheet_directory() . '/library/acf-fields.php' ) ) {
    require_once get_stylesheet_directory() . '/library/acf-fields.php';
}

// Change WooCommerce "Add to Cart" button text to "Buy with Stripe"
add_filter( 'woocommerce_product_add_to_cart_text', 'custom_buy_with_stripe_text' );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_buy_with_stripe_text' );

function custom_buy_with_stripe_text( $text ) {
    return 'Buy with Stripe';
}

