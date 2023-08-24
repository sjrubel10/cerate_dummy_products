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
        $wpdb->insert( $post_table, array('post_title'=>$title, 'post_name'=>$post_name ,'post_status'=> $post_status, 'post_type'=>$post_type, 'post_date'=>$current_date_time, 'post_date_gmt'=> $current_date_time, 'post_content'=>$post_content ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );
        $product_id = $wpdb->insert_id;

        if( $product_id ){
            $price = rand(400, 1000);;
            $regular_price = $price - 60;
            $post_meta_fields = array(
                '_price' => $price,
                '_regular_price'=>$regular_price,
                '_sale_price'=>$regular_price,
                '_stock_status'=>'instock',
                '_thumbnail_id'=>5,
                '_sku'=>generateRandomString(8),
                '_product_version'=>'7.2.1'
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
                wp_set_object_terms( $product_id, $product_type, 'product_type', true );
            }
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

