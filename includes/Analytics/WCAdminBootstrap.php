<?php

namespace PluginizeLab\StoreSuite\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstraps WooCommerce admin scripts on the frontend so the analytics and
 * dashboard React bundles can resolve `window.wc.*` externals.
 *
 * `WCAdminAssets::register_scripts()` is admin-only — it transitively calls
 * `get_current_screen()` (admin-only) and triggers a `_doing_it_wrong` notice.
 * We load the screen API and suppress the notice for the duration of the call.
 *
 * Idempotent: subsequent calls in the same request are no-ops.
 */
class WCAdminBootstrap {
	private static bool $bootstrapped = false;

	public static function ensure(): void {
		if ( self::$bootstrapped ) {
			return;
		}
		self::$bootstrapped = true;

		if ( ! class_exists( '\Automattic\WooCommerce\Internal\Admin\WCAdminAssets' ) ) {
			return;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once ABSPATH . 'wp-admin/includes/screen.php';
		}

		add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
		\Automattic\WooCommerce\Internal\Admin\WCAdminAssets::get_instance()->register_scripts();
		remove_filter( 'doing_it_wrong_trigger_error', '__return_false' );
	}
}
