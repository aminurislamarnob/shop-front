<?php
/**
 * Plugin Name: StoreSuite
 * Plugin URI:  https://wordpress.org/plugins/storesuite/
 * Description: This plugin enable frontend store management system for WooCommerce simple type product.
 * Version: 0.0.1
 * Author: Aminur Islam Arnob
 * Author URI: https://wordpress.org/plugins/storesuite/
 * Text Domain: storesuite
 * WC requires at least: 5.0.0
 * Requires Plugins: woocommerce
 * License: GPL2
 */

use PluginizeLab\StoreSuite\StoreSuite;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'STORESUITE_FILE' ) ) {
	define( 'STORESUITE_FILE', __FILE__ );
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Load Store_Suite Plugin when all plugins loaded
 *
 * @return \PluginizeLab\StoreSuite\StoreSuite
 */
function pluginizelab_storesuite() {
	return StoreSuite::init();
}

// Lets Go....
pluginizelab_storesuite();
