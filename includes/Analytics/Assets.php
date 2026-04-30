<?php

namespace PluginizeLab\StoreSuite\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts(): void {
		if ( ! storesuite_is_endpoint_url( 'analytics' ) ) {
			return;
		}

		// Bootstrap WC admin scripts on the frontend (suppressing the _doing_it_wrong notice).
		if ( class_exists( '\Automattic\WooCommerce\Internal\Admin\WCAdminAssets' ) ) {
			add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
			\Automattic\WooCommerce\Internal\Admin\WCAdminAssets::get_instance()->register_scripts();
			remove_filter( 'doing_it_wrong_trigger_error', '__return_false' );
		}

		$asset_file = STORESUITE_DIR . '/assets/build/analytics/index.asset.php';
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
			'storesuite-analytics',
			STORESUITE_PLUGIN_ASSET . '/build/analytics/index.js',
			$deps,
			$version,
			true
		);

		wp_register_style(
			'storesuite-analytics',
			STORESUITE_PLUGIN_ASSET . '/build/analytics/index.css',
			[ 'wc-components', 'wp-components' ],
			$version
		);

		wp_enqueue_script( 'storesuite-analytics' );
		wp_enqueue_style( 'storesuite-analytics' );
		wp_set_script_translations( 'storesuite-analytics', 'storesuite' );

		$analytics_url = storesuite_get_navigation_url( 'analytics' );
		$dashboard_url = storesuite_get_navigation_url();

		wp_add_inline_script(
			'storesuite-analytics',
			'var storeSuiteAnalyticsConfig = ' . wp_json_encode( [
				'assetsPath'    => STORESUITE_PLUGIN_ASSET . '/build/',
				'analyticsUrl'  => $analytics_url,
				'dashboardPath' => wp_parse_url( $dashboard_url, PHP_URL_PATH ),
				'reportsPath'   => wp_parse_url( $analytics_url, PHP_URL_PATH ),
			] ),
			'before'
		);

		$settings = ( new Settings() )->get_settings();
		wp_add_inline_script(
			'storesuite-analytics',
			'var storeSuiteAnalyticsSettings = ' . wp_json_encode( $settings ),
			'before'
		);
	}
}
