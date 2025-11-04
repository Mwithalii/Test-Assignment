<?php
// Displays Title, Author, ISBN, Price (with fallbacks) and an Add-to-Cart "Buy with Stripe" button.

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) : the_post();

    $book_id = get_the_ID();

    // Primary sources: ACF -> post meta -> post title
    $title  = function_exists('get_field') ? get_field('book_title', $book_id) : false;
    if ( empty($title) ) { $title = get_post_meta($book_id, 'book_title', true); }
    if ( empty($title) ) { $title = get_the_title($book_id); }

    $author = function_exists('get_field') ? get_field('author', $book_id) : get_post_meta($book_id, 'author', true);
    $isbn   = function_exists('get_field') ? get_field('isbn', $book_id)   : get_post_meta($book_id, 'isbn', true);
    $price_meta = function_exists('get_field') ? get_field('price', $book_id) : get_post_meta($book_id, 'price', true);

    // Try linked product ID (importer should have saved this meta)
    $product_id = get_post_meta($book_id, '_linked_product_id', true);

    // If no linked product id, try to find by SKU = ISBN
    if ( empty($product_id) && ! empty($isbn) ) {
        if ( function_exists('wc_get_product_id_by_sku') ) {
            $product_id = wc_get_product_id_by_sku( sanitize_text_field($isbn) );
        }
    }

    // Determine price: priority -> ACF/meta price -> Woo product price -> unknown
    $price = '';
    if ( $price_meta !== '' && $price_meta !== null ) {
        $price = floatval( $price_meta );
    } elseif ( ! empty($product_id) ) {
        $product = wc_get_product( intval($product_id) );
        if ( $product ) {
            $price = $product->get_price();
        }
    }

    // Format price with WooCommerce helper if available
    if ( $price !== '' && function_exists('wc_price') ) {
        $price_html = wc_price( $price );
    } elseif ( $price !== '' ) {
        $price_html = number_format( (float)$price, 2 );
    } else {
        $price_html = '<em>Price not set</em>';
    }

    // Build add-to-cart URL (standard WooCommerce query)
    $add_to_cart_url = '';
    if ( ! empty($product_id) ) {
        $add_to_cart_url = esc_url( add_query_arg( array( 'add-to-cart' => intval($product_id), 'quantity' => 1 ), home_url('/') ) );
    }

    ?>
    <main id="primary" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('book-post'); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php echo esc_html( $title ); ?></h1>
            </header>

            <div class="entry-content">
                <p><strong>Author:</strong> <?php echo esc_html( $author ?: 'Unknown' ); ?></p>
                <p><strong>ISBN:</strong> <?php echo esc_html( $isbn ?: 'Not set' ); ?></p>
                <p><strong>Price:</strong> <?php echo wp_kses_post( $price_html ); ?></p>

                <?php if ( $add_to_cart_url ) : ?>
                    <p>
                        <a class="button wc-forward" href="<?php echo $add_to_cart_url; ?>">
                            Buy with Stripe
                        </a>
                    </p>
                <?php else : ?>
                    <p><em>Product mapping not found. Check importer or product SKU.</em></p>
                <?php endif; ?>

                <hr>
                <div class="book-description">
                    <?php the_content(); ?>
                </div>
            </div>
        </article>
    </main>
    <?php

endwhile;

get_footer();
