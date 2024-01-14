<?php
/**
 * Plugin Name: My Shop Front
 * Plugin URI:  https://wordpress.org/plugins/my-shop-front/
 * Description: This plugin enable frontend store management system for WooCommerce simple type product.
 * Version: 0.0.1
 * Author: Aminur Islam Arnob
 * Author URI: https://wordpress.org/plugins/my-shop-front/
 * Text Domain: my-shop-front
 * WC requires at least: 5.0.0
 * Domain Path: /languages/
 * License: GPL-2.0+
 */
use WeLabs\MyShopFront\MyShopFront;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'MY_SHOP_FRONT_FILE' ) ) {
    define( 'MY_SHOP_FRONT_FILE', __FILE__ );
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Load My_Shop_Front Plugin when all plugins loaded
 *
 * @return \WeLabs\MyShopFront\MyShopFront
 */
function welabs_my_shop_front() {
    return MyShopFront::init();
}

// Lets Go....
welabs_my_shop_front();
