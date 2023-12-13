<?php

function get_images_for_product( $number_of_image ){
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
    );
    $attachments = get_posts($args);
    foreach ($attachments as $attachment) {
        $indexArray[] = $attachment->ID;
    }
    $randomKeys = array_rand($indexArray, $number_of_image); // Get 5 random keys from the array
    if( $number_of_image === 1 ){
        $randomElements[] = $indexArray[$randomKeys];
    }else{
        $randomElements = array_intersect_key($indexArray, array_flip($randomKeys));
    }

    if( count( $randomElements )> 0 ){
        $imagepointer_str = implode(',', $randomElements);
    }else{
        $imagepointer_str = '';
    }
    return $imagepointer_str;
}

function generateRandomString( $length ) {

    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    if( $length > 10){
        $outputString = '';
        for ($i = 0; $i < strlen($randomString); $i++) {
            if ($i % 8 === 0 && $i !== 0) {
                $outputString .= ' ';
            }
            if ($i === 0) {
                $outputString .= strtoupper($randomString[$i]);
            } else {
                $outputString .= strtolower($randomString[$i]);
            }
        }
    }else{
        $outputString = $randomString;
    }

    return $outputString;
}

function create_product_variation( $product_id, $variation_data ){
    // Get the Variable product object (parent)
    $product = wc_get_product( $product_id );
    $variation_post = array(
        'post_title'  => $product->get_name(),
        'post_name'   => 'product-'.$product_id.'-variation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => $product->get_permalink()
    );

    // Creating the product variation
    $variation_id = wp_insert_post( $variation_post );

    // Get an instance of the WC_Product_Variation object
    $variation = new WC_Product_Variation( $variation_id );

    // Iterating through the variations attributes
    foreach ($variation_data['attributes'] as $attribute => $term_name )
    {
        $taxonomy = 'pa_'.$attribute; // The attribute taxonomy

        // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
        if( ! taxonomy_exists( $taxonomy ) ){
            register_taxonomy(
                $taxonomy,
                'product_variation',
                array(
                    'hierarchical' => false,
                    'label' => ucfirst( $attribute ),
                    'query_var' => true,
                    'rewrite' => array( 'slug' => sanitize_title($attribute) ), // The base slug
                ),
            );
        }

        // Check if the Term name exist and if not we create it.
        if( ! term_exists( $term_name, $taxonomy ) )
            wp_insert_term( $term_name, $taxonomy ); // Create the term

        $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

        // Get the post Terms names from the parent variable product.
        $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

        // Check if the post term exist and if not we set it in the parent variable product.
        if( ! in_array( $term_name, $post_term_names ) )
            wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

        // Set/save the attribute data in the product variation
        update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
    }

    ## Set/save all other data

    // SKU
    if( ! empty( $variation_data['sku'] ) )
        $variation->set_sku( $variation_data['sku'] );

    // Prices
    if( empty( $variation_data['sale_price'] ) ){
        $variation->set_price( $variation_data['regular_price'] );
    } else {
        $variation->set_price( $variation_data['sale_price'] );
        $variation->set_sale_price( $variation_data['sale_price'] );
    }
    $variation->set_regular_price( $variation_data['regular_price'] );

    // Stock
    if( ! empty($variation_data['stock_qty']) ){
        $variation->set_stock_quantity( $variation_data['stock_qty'] );
        $variation->set_manage_stock(true);
        $variation->set_stock_status('');
    } else {
        $variation->set_manage_stock(false);
    }

    $variation->set_weight(''); // weight (reseting)

    $variation->save(); // Save the data
}

function make_variation_product( $parent_id ){
// The variation data
    $variation_data =  array(
        'attributes' => array(
            'size'  => 'M',
            'color' => 'Blue',
        ),
        'sku'           => '',
        'regular_price' => '22.00',
        'sale_price'    => '',
        'stock_qty'     => 10,
    );

// The function to be run
    create_product_variation( $parent_id, $variation_data );
}

function insert_fake_post( $total_product, $product_type ){
    global $wpdb ;

    $post_table = $wpdb->prefix . "posts";
    $postmeta_table = $wpdb->prefix . "postmeta";

    if( $total_product === "" ){
        $total_product = 2 ;
    }

    $post_id = $wpdb->get_results("SELECT `ID` FROM $post_table ORDER BY `ID` DESC LIMIT 1");
    $id = (int)$post_id[0]->ID;
    for( $i = 1; $i<= $total_product; $i++ ){

        $product_dif = $id+$i;
        $current_date_time = date("Y-m-d h:i:s");
        $title = " Product Title $product_dif";
        $post_name = strtolower(str_replace(' ', '-', trim($title)));
        $post_status = "publish";
        $post_type = "product";
        $post_content = generateRandomString( 200 );
        $post_excerpt = generateRandomString( 50 );
        $wpdb->insert( $post_table, array('post_title'=>$title, 'post_author'=>1, 'post_name'=>$post_name ,'post_status'=> $post_status, 'post_type'=>$post_type, 'post_date'=>$current_date_time, 'post_date_gmt'=> $current_date_time, 'post_content'=>$post_content, 'post_excerpt'=> $post_excerpt ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );
        $product_id = $wpdb->insert_id;

        if( $product_id ){
            $price = rand(400, 1000);;
            $regular_price = $price - 60;
            $post_meta_fields = array(
                '_price' => $price,
                '_regular_price'=>$regular_price,
                '_sale_price'=>$regular_price,
                '_stock_status'=>'instock',
                '_stock'=>100,
                '_manage_stock'=>'yes',
                '_thumbnail_id'=>get_images_for_product(1),
                '_sku'=>generateRandomString(8),
                '_product_version'=>'7.2.1',
                '_product_image_gallery'=>get_images_for_product(3),
            );
            foreach ( $post_meta_fields as $key => $value ){

                if( !is_numeric( $key )){
                    $prepare = "%s";
                }else{
                    $prepare = "%d";
                }

                if( !is_numeric( $value )){
                    $prepare1 = "%s";
                }else{
                    $prepare1 = "%d";
                }

                $wpdb->insert( $postmeta_table, array('post_id'=>$product_id, 'meta_key'=>$key ,'meta_value'=> $value ), array( '%d', "'".$prepare."'", "'".$prepare1."'" ) );

            }
            if( $product_type !== ""){
                wp_set_object_terms( $product_id, $product_type, 'product_type' );
            }

            $cat_ids = array(16);
            $tag_ids = array(47);
            wp_set_object_terms( $product_id, $cat_ids, 'product_cat', true );
            wp_set_object_terms( $product_id, $tag_ids, 'product_tag', true );
        }

        if( $product_type === 'variable' ){
            make_variation_product( $product_id );
        }

        if( $i === $total_product){
            return $total_product;
        }
    }

}

function permanent_delete_post( $post_id ){

    $is_delete = wp_delete_post( $post_id );

    return $is_delete;

}

function wh_deleteProduct( $id, $force = FALSE )
{
    $product = wc_get_product($id);

    if(empty($product))
        return new WP_Error(999, sprintf(__('No %s is associated with #%d', 'woocommerce'), 'product', $id));

    // If we're forcing, then delete permanently.
    if ($force)
    {
        if ($product->is_type('variable'))
        {
            foreach ($product->get_children() as $child_id)
            {
                $child = wc_get_product($child_id);
                $child->delete(true);
            }
        }
        elseif ($product->is_type('grouped'))
        {
            foreach ($product->get_children() as $child_id)
            {
                $child = wc_get_product($child_id);
                $child->set_parent_id(0);
                $child->save();
            }
        }

        $product->delete(true);
        $result = $product->get_id() > 0 ? false : true;
    }
    else
    {
        $product->delete();
        $result = 'trash' === $product->get_status();
    }

    if (!$result)
    {
        return new WP_Error(999, sprintf(__('This %s cannot be deleted', 'woocommerce'), 'product'));
    }

    // Delete parent product transients.
    if ($parent_id = wp_get_post_parent_id($id))
    {
        wc_delete_product_transients($parent_id);
    }
    return true;
}

function create_simple_product() {
    // that's CRUD object
    $product = new \WC_Product_Simple();

    error_log( print_r( $product, true ) );
    die();

    $product->set_name( 'Single' ); // product title
    $product->set_id( 25 ); // product id

    $product->set_slug( 'woo-single' );
    $product->set_sku( 'woo-single' );

    $product->set_regular_price( 3.00 ); // in current shop currency
    $product->set_price( 3.00 ); // in current shop currency

    //you can also add a full product description
    $product->set_description( 'This is a simple, virtual product.' );

    $product->set_image_id( 90 );

    // let's suppose that our 'Accessories' category has ID = 19
    $product->set_category_ids( array( 20 ) );
    // you can also use $product->set_tag_ids() for tags, brands etc
    $product->set_tag_ids( array( 30, 40 ) );

    // Set Price
    $product->set_sale_price( 2.00 );
    // Sale schedule
    $product->set_date_on_sale_from( '2022-05-01' );
    $product->set_date_on_sale_to( '2022-05-31' );


    // You do not need it if you manage stock at product level (below)
    $product->set_stock_status( 'instock' ); // 'instock', 'outofstock' or 'onbackorder'

    // Stock management at product level
    $product->set_manage_stock( true );
    $product->set_stock_quantity( 5 );
    $product->set_backorders( 'no' ); // 'yes', 'no' or 'notify'
    $product->set_low_stock_amount( 2 );
    //
    $product->set_sold_individually( true );

    // Dimensions and Shipping
    $product->set_weight( 0.5 );
    $product->set_length( 50 );
    $product->set_width( 50 );
    $product->set_height( 30 );

    $product->save();

    return $product;
}


