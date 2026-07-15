<?php
/**
 * WordPress test-suite configuration.
 *
 * Connection settings for the throwaway test database. Every value can be
 * overridden via `WP_TESTS_*` environment variables — locally these are set
 * in the gitignored `phpunit.xml`; CI can export them directly.
 *
 * @package StoreSuite
 */

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ? getenv( 'WP_TESTS_DB_NAME' ) : 'storesuite_tests' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ? getenv( 'WP_TESTS_DB_USER' ) : 'root' );
define( 'DB_PASSWORD', false !== getenv( 'WP_TESTS_DB_PASSWORD' ) ? getenv( 'WP_TESTS_DB_PASSWORD' ) : '' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ? getenv( 'WP_TESTS_DB_HOST' ) : '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'StoreSuite Tests' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WP_DEBUG', true );

if ( ! defined( 'ABSPATH' ) ) {
	// Default: the WordPress install this plugin lives in (plugin dir is wp-content/plugins/storesuite).
	$storesuite_abspath = getenv( 'WP_TESTS_ABSPATH' ) ? getenv( 'WP_TESTS_ABSPATH' ) : dirname( __DIR__, 4 );
	define( 'ABSPATH', rtrim( $storesuite_abspath, '/' ) . '/' );
}
