<?php
/**
 * Plugin Name: Shop Front
 * Plugin URI:  https://wordpress.org/plugins/shop-front/
 * Description: This plugin enable frontend store management system for WooCommerce simple type product.
 * Version: 0.0.1
 * Author: Aminur Islam Arnob
 * Author URI: https://wordpress.org/plugins/shop-front/
 * Text Domain: shop-front
 * WC requires at least: 5.0.0
 * Domain Path: /languages/
 * License: GPL2
 */
use PluginizeLab\ShopFront\ShopFront;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'SHOP_FRONT_FILE' ) ) {
    define( 'SHOP_FRONT_FILE', __FILE__ );
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Load Shop_Front Plugin when all plugins loaded
 *
 * @return \PluginizeLab\ShopFront\ShopFront
 */
function pluginizelab_shop_front() {
    return ShopFront::init();
}

// Lets Go....
pluginizelab_shop_front();
