<?php

namespace PluginizeLab\StoreSuite\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts(): void {
		if ( ! storesuite_is_dashboard_page() || storesuite_is_endpoint_url() ) {
			return;
		}

		// Bootstrap WC admin scripts on the frontend (suppressing the _doing_it_wrong notice).
		if ( class_exists( '\Automattic\WooCommerce\Internal\Admin\WCAdminAssets' ) ) {
			if ( ! function_exists( 'get_current_screen' ) ) {
				require_once ABSPATH . 'wp-admin/includes/screen.php';
			}
			add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
			\Automattic\WooCommerce\Internal\Admin\WCAdminAssets::get_instance()->register_scripts();
			remove_filter( 'doing_it_wrong_trigger_error', '__return_false' );
		}

		$asset_file = STORESUITE_DIR . '/assets/build/dashboard/index.asset.php';
		$asset      = file_exists( $asset_file ) ? include $asset_file : [];
		$deps       = $asset['dependencies'] ?? [];
		$version    = $asset['version'] ?? STORESUITE_PLUGIN_VERSION;

		$wc_deps = [
			'wc-components',
			'wc-admin-layout',
			'wc-experimental',
			'wc-customer-effort-score',
			'wp-components',
		];
		$deps    = array_unique( array_merge( $deps, $wc_deps ) );

		wp_register_script(
			'storesuite-dashboard',
			STORESUITE_PLUGIN_ASSET . '/build/dashboard/index.js',
			$deps,
			$version,
			true
		);

		wp_register_style(
			'storesuite-dashboard',
			STORESUITE_PLUGIN_ASSET . '/build/dashboard/index.css',
			[ 'wc-components', 'wp-components' ],
			$version
		);

		wp_enqueue_script( 'storesuite-dashboard' );
		wp_enqueue_style( 'storesuite-dashboard' );
		wp_set_script_translations( 'storesuite-dashboard', 'storesuite' );

		$dashboard_url = storesuite_get_navigation_url();
		$analytics_url = storesuite_get_navigation_url( 'analytics' );

		wp_add_inline_script(
			'storesuite-dashboard',
			'var storeSuiteDashboardConfig = ' . wp_json_encode( [
				'assetsPath'    => STORESUITE_PLUGIN_ASSET . '/build/',
				'dashboardUrl'  => $dashboard_url,
				'dashboardPath' => wp_parse_url( $dashboard_url, PHP_URL_PATH ),
				'analyticsUrl'  => $analytics_url,
				'reportsPath'   => wp_parse_url( $analytics_url, PHP_URL_PATH ),
			] ),
			'before'
		);

		$settings = ( new \PluginizeLab\StoreSuite\Analytics\Settings() )->get_settings();
		wp_add_inline_script(
			'storesuite-dashboard',
			'var storeSuiteDashboardSettings = ' . wp_json_encode( $settings ),
			'before'
		);
	}
}
