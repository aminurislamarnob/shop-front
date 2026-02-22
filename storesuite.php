<?php
/**
 * Plugin Name: StoreSuite
 * Plugin URI:  https://wordpress.org/plugins/storesuite/
 * Description: Manage your WooCommerce store easily from a front-end dashboard.
 * Version: 1.0.0
 * Author: Aminur Islam Arnob
 * Author URI: https://github.com/aminurislamarnob/
 * Text Domain: storesuite
 * WC requires at least: 10.4.3
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
