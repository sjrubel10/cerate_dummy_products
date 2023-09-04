<?php

define( 'WOOBENP_IMAGES_LINK', WOOBENP_LINK . 'views/images/' );
// Include WordPress core files
//require_once('/Applications/MAMP/htdocs/plugintest/wp-load.php');

// Include WooCommerce core files
if (class_exists('WooCommerce')) {
//    include_once('wp-content/plugins/woocommerce/woocommerce.php');
}
$product_data = array(
    'post_title'    => 'Sample Product',
    'post_content'  => 'This is a sample product description.',
    'post_status'   => 'publish',
    'post_type'     => 'product',
    'regular_price' => '10.00',
);


$product_id = wp_insert_post($product_data);

$image_path = WOOBENP_IMAGES_LINK."image3.jpeg"; // Replace with the actual path to your image



$image_id = media_handle_sideload(array('file' => $image_path), $product_id, 'product_image');

var_dump( $image_id );
die();

if (is_wp_error($image_id)) {
    // Handle error if image upload fails
} else {
    // Set the uploaded image as the product's featured image
    set_post_thumbnail($product_id, $image_id);
}


// Update additional product data
update_post_meta($product_id, '_sku', 'sample-sku');
update_post_meta($product_id, '_stock_status', 'instock');
// Add the product to a category (replace 'category_id' with the actual category ID)
wp_set_object_terms($product_id, 'category_id', 'product_cat');


$product_permalink = get_permalink($product_id);
if ($product_permalink) {
    wp_redirect($product_permalink);
    exit;
}
