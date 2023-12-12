<?php
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
        $wpdb->insert( $post_table, array('post_title'=>$title, 'post_author'=>1, 'post_name'=>$post_name ,'post_status'=> $post_status, 'post_type'=>$post_type, 'post_date'=>$current_date_time, 'post_date_gmt'=> $current_date_time, 'post_content'=>$post_content, 'post_excerpt'=> $post_content ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );
        $product_id = $wpdb->insert_id;

        if( $product_id ){
            $price = rand(400, 1000);;
            $regular_price = $price - 60;
            $post_meta_fields = array(
                '_price' => $price,
                '_regular_price'=>$regular_price,
                '_sale_price'=>$regular_price,
                '_stock_status'=>'instock',
                '_thumbnail_id'=>12526,
                '_sku'=>generateRandomString(8),
                '_product_version'=>'7.2.1',
                '_product_image_gallery'=>'12527,12497,12496',
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

