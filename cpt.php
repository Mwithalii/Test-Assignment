<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'yc_register_book_cpt', 10 );

function yc_register_book_cpt() {
    $labels = array(
        'name'                  => __( 'Books', 'the-storefront-woocommerce-child' ),
        'singular_name'         => __( 'Book', 'the-storefront-woocommerce-child' ),
        'menu_name'             => __( 'Books', 'the-storefront-woocommerce-child' ),
        'name_admin_bar'        => __( 'Book', 'the-storefront-woocommerce-child' ),
        'add_new'               => __( 'Add New', 'the-storefront-woocommerce-child' ),
        'add_new_item'          => __( 'Add New Book', 'the-storefront-woocommerce-child' ),
        'new_item'              => __( 'New Book', 'the-storefront-woocommerce-child' ),
        'edit_item'             => __( 'Edit Book', 'the-storefront-woocommerce-child' ),
        'view_item'             => __( 'View Book', 'the-storefront-woocommerce-child' ),
        'all_items'             => __( 'All Books', 'the-storefront-woocommerce-child' ),
        'search_items'          => __( 'Search Books', 'the-storefront-woocommerce-child' ),
        'not_found'             => __( 'No books found.', 'the-storefront-woocommerce-child' ),
        'not_found_in_trash'    => __( 'No books found in Trash.', 'the-storefront-woocommerce-child' ),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,               // public visibility
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-book',   // admin icon
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'has_archive'           => true,
        'rewrite'               => array( 'slug' => 'books' ),
        'show_in_rest'          => true,               // enables Gutenberg & REST API
        'capability_type'       => 'post',
        'hierarchical'          => false,
    );

    register_post_type( 'book', $args );
}

/**
 * Flush rewrite rules on theme activation so permalinks for the CPT work immediately.
 * Note: this will only run when the theme is switched/activated.
 */
add_action( 'after_switch_theme', 'yc_flush_rewrite_rules' );
function yc_flush_rewrite_rules() {
    flush_rewrite_rules();
}
