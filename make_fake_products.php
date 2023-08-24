<?php
/*
  Plugin Name: Woo - make dummy product
  Plugin URI: https://make_dummy_product.com/
  Description:Test products
  Requires at least: WP 4.9
  Tested up to: WP 6.2
  Author: Rubel777
  Author URI: https://abc.net/
  Version: 1.0.0
  Requires PHP: 7.4
  Text Domain: woo-make-dummy-product
  Domain Path: /languages
  WC requires at least: 3.6
  WC tested up to: 7.6
  Forum URI: https://abc.net/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
define( 'WOODUMMY_PATH', plugin_dir_path(__FILE__ ) );
add_action( 'admin_menu',  'my_plugin_menu' );

function my_plugin_menu(){
    add_menu_page(
        'Woo Dummy Products',
        'Woo Dummy Products',
        'manage_options',
        'woo-dummy-products',
         'woo_added_dummy_products'
    );
}

function woo_added_dummy_products(){
    include WOODUMMY_PATH."/make_product.php";
}
