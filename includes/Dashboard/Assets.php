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

		\PluginizeLab\StoreSuite\Analytics\WCAdminBootstrap::ensure();

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
				'canViewOrders' => current_user_can( 'read_private_shop_orders' ),
			] ),
			'before'
		);

		// Dashboard root only consumes the leaderboards endpoint;
		// performance indicators come from getReportItems() at runtime.
		$settings = ( new \PluginizeLab\StoreSuite\Analytics\Settings() )->get_settings( [ 'leaderboards' ] );
		wp_add_inline_script(
			'storesuite-dashboard',
			'var storeSuiteDashboardSettings = ' . wp_json_encode( $settings ),
			'before'
		);
	}
}
