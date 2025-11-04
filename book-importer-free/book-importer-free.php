<?php
/*
Plugin Name: Book Importer Free
Description: Import Books CSV into Book CPT + create WooCommerce products (SKU = ISBN). 
Version: 1.0
Author: Mwithalii
*/

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
    add_menu_page('Book Importer','Book Importer','manage_options','book-importer-free','bif_importer_page');
});

function bif_importer_page(){
    if (!current_user_can('manage_options')) return;
    echo '<div class="wrap"><h1>Book Importer (Free)</h1>';
    echo '<p>Upload CSV to Media Library, paste URL below. CSV headers required: <code>book_title,author,isbn,price,description</code></p>';
    if (isset($_POST['bif_csv_url']) && check_admin_referer('bif_import_nonce')){
        $csv_url = esc_url_raw($_POST['bif_csv_url']);
        $delimiter = sanitize_text_field($_POST['bif_delimiter'] ?? ',');
        echo '<h2>Import Result</h2><pre>';
        $res = bif_run_import($csv_url, $delimiter);
        echo esc_html($res);
        echo '</pre>';
    }
    ?>
    <form method="post">
      <?php wp_nonce_field('bif_import_nonce'); ?>
      <table class="form-table">
        <tr><th>CSV URL (from Media Library)</th>
            <td><input style="width:70%" name="bif_csv_url" required></td></tr>
        <tr><th>Delimiter</th>
            <td>
              <select name="bif_delimiter">
                <option value=",">Comma (,)</option>
                <option value=";">Semicolon (;)</option>
                <option value="\t">Tab</option>
              </select>
              <p class="description">If unsure, use Comma.</p>
            </td></tr>
      </table>
      <p><input type="submit" class="button button-primary" value="Import"></p>
    </form>
    </div>
    <?php
}

function bif_run_import($csv_url, $delimiter = ','){
    if (!function_exists('wc_get_product')) return 'WooCommerce is required. Activate WooCommerce first.';
    $response = wp_remote_get($csv_url);
    if (is_wp_error($response)) return 'Could not fetch CSV: '. $response->get_error_message();
    $body = wp_remote_retrieve_body($response);
    if (empty($body)) return 'CSV file empty or not accessible.';
    // Normalize newlines
    $body = str_replace(["\r\n","\r"], "\n", $body);
    $lines = array_filter(array_map('trim', explode("\n", $body)));
    if (count($lines) < 2) return 'No data rows found. Ensure CSV has header + rows.';
    // detect delimiter if requested 'auto'
    if ($delimiter === 'auto'){
        $delimiter = bif_detect_delimiter($lines[0]);
    } elseif ($delimiter === '\t') { $delimiter = "\t"; }
    $header = str_getcsv(array_shift($lines), $delimiter);
    $header = array_map('trim', $header);
    $required = ['book_title','author','isbn','price'];
    foreach ($required as $req) {
        if (!in_array($req, $header)) return 'Missing required header: ' . $req;
    }
    $count = 0; $updated=0; $errors=[];
    foreach ($lines as $idx => $line){
        $row = str_getcsv($line, $delimiter);
        // Skip blank rows
        if (count($row) === 1 && $row[0] === '') continue;
        $data = array_combine($header, array_pad($row, count($header), ''));
        $isbn = trim($data['isbn'] ?? '');
        if (empty($isbn)) { $errors[] = "Row ".($idx+2)." missing ISBN — skipped."; continue; }
        $book_title = trim($data['book_title'] ?? '');
        $author = trim($data['author'] ?? '');
        $price = trim($data['price'] ?? '');
        $description = trim($data['description'] ?? '');

        // Check if product with this SKU exists
        $existing_product_id = wc_get_product_id_by_sku($isbn);
        if ($existing_product_id) {
            // Update product price & name if needed
            $product = wc_get_product($existing_product_id);
            if ($product) {
                $product->set_name( $book_title ?: $product->get_name() );
                if ($price !== '') $product->set_regular_price( (string) $price );
                $product->save();
                $product_id = $existing_product_id;
            } else {
                $product_id = null;
            }
        } else {
            // Create new simple product
            $p = new WC_Product_Simple();
            $p->set_name( $book_title );
            if ($price !== '') $p->set_regular_price( (string) $price );
            $p->set_sku( $isbn );
            $product_id = $p->save();
        }

        // Create or update Book CPT post
        // Try to find existing book by isbn meta
        $existing_book = get_posts([
            'post_type' => 'book',
            'meta_key' => 'isbn',
            'meta_value' => $isbn,
            'post_status' => 'any',
            'numberposts' => 1,
        ]);
        if (!empty($existing_book)){
            $book_id = $existing_book[0]->ID;
            wp_update_post(['ID'=>$book_id, 'post_title'=>$book_title, 'post_content'=>$description]);
            $is_new = false;
        } else {
            $book_id = wp_insert_post([
                'post_title' => $book_title,
                'post_content' => $description,
                'post_status' => 'publish',
                'post_type' => 'book'
            ]);
            $is_new = true;
        }
        if (!$book_id || is_wp_error($book_id)) { $errors[] = "Row ".($idx+2)." failed to create book."; continue; }

        // Save ACF fields if available, else meta
        if (function_exists('update_field')) {
            update_field('book_title', $book_title, $book_id);
            update_field('author', $author, $book_id);
            update_field('isbn', $isbn, $book_id);
            update_field('price', $price, $book_id);
        } else {
            update_post_meta($book_id, 'book_title', $book_title);
            update_post_meta($book_id, 'author', $author);
            update_post_meta($book_id, 'isbn', $isbn);
            update_post_meta($book_id, 'price', $price);
        }
        // Link product id to book
        if ($product_id) update_post_meta($book_id, '_linked_product_id', intval($product_id));

        if ($is_new) $count++; else $updated++;
    }

    $msg = sprintf("Imported: %d new books; Updated: %d. Errors: %d\n", $count, $updated, count($errors));
    if (!empty($errors)) $msg .= implode("\n", $errors);
    return $msg;
}

function bif_detect_delimiter($header_line){
    $candidates = [',',';',"\t",'|'];
    $best = ',';
    $max = 0;
    foreach ($candidates as $d){
        $n = substr_count($header_line, $d);
        if ($n > $max){ $max = $n; $best = $d; }
    }
    return $best;
}
