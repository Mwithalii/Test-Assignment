<?php

add_action('acf/init', 'yc_acf_book_fields');
function yc_acf_book_fields() {
    if( function_exists('acf_add_local_field_group') ) {
        acf_add_local_field_group(array(
            'key' => 'group_books',
            'title' => 'Book Details',
            'fields' => array(
                array('key'=>'field_book_title','label'=>'Book Title','name'=>'book_title','type'=>'text'),
                array('key'=>'field_author','label'=>'Author','name'=>'author','type'=>'text'),
                array('key'=>'field_isbn','label'=>'ISBN','name'=>'isbn','type'=>'text'),
                array('key'=>'field_price','label'=>'Price','name'=>'price','type'=>'number'),
            ),
            'location' => array(array(array('param'=>'post_type','operator'=>'==','value'=>'book'))),
        ));
    }
}
