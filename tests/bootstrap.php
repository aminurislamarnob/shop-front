<?php
/**
 * PHPUnit bootstrap: loads the WordPress test suite (wp-phpunit) with
 * WooCommerce and StoreSuite active. Runs against a local MySQL — see
 * tests/wp-tests-config.php for the connection settings (`composer test`).
 *
 * @package StoreSuite
 */

$storesuite_plugin_root = dirname( __DIR__ );

require_once $storesuite_plugin_root . '/vendor/autoload.php';

$storesuite_wp_phpunit_dir = getenv( 'WP_PHPUNIT__DIR' );
if ( ! $storesuite_wp_phpunit_dir ) {
	$storesuite_wp_phpunit_dir = $storesuite_plugin_root . '/vendor/wp-phpunit/wp-phpunit';
}

if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/wp-tests-config.php' );
}

require_once $storesuite_wp_phpunit_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () use ( $storesuite_plugin_root ) {
		// WooCommerce must load first: StoreSuite bails on plugins_loaded when
		// the WooCommerce class is absent.
		require dirname( $storesuite_plugin_root ) . '/woocommerce/woocommerce.php';
		require $storesuite_plugin_root . '/storesuite.php';
	}
);

// WooCommerce normally installs on plugin activation, which never runs in the
// test suite — install manually so its roles/caps and tables exist.
tests_add_filter(
	'setup_theme',
	function () {
		WC_Install::install();
		$GLOBALS['wp_roles'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_roles();
	}
);

require $storesuite_wp_phpunit_dir . '/includes/bootstrap.php';

require __DIR__ . '/fixtures/FixtureModule.php';
