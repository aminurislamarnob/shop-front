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

		// Capability gate: dashboard React app and inline globals are admin-only.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
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
		wp_set_script_translations( 'storesuite-dashboard', 'storesuite', STORESUITE_DIR . '/languages' );

		$dashboard_url = storesuite_get_navigation_url();
		$analytics_url = storesuite_get_navigation_url( 'analytics' );

		wp_add_inline_script(
			'storesuite-dashboard',
			'var storeSuiteDashboardConfig = ' . wp_json_encode(
				[
					'assetsPath'    => STORESUITE_PLUGIN_ASSET . '/build/',
					'dashboardUrl'  => $dashboard_url,
					'dashboardPath' => wp_parse_url( $dashboard_url, PHP_URL_PATH ),
					'analyticsUrl'  => $analytics_url,
					'reportsPath'   => wp_parse_url( $analytics_url, PHP_URL_PATH ),
					'canViewOrders' => current_user_can( 'read_private_shop_orders' ),
				]
			),
			'before'
		);

		// Skip preloading endpoints whose widgets are disabled in dashboard
		// settings — saves a REST round-trip and preload payload size.
		$needed = [];
		if ( $this->any_perf_widget_enabled() ) {
			$needed[] = 'performanceIndicators';
		}
		if ( $this->any_leaderboard_widget_enabled() ) {
			$needed[] = 'leaderboards';
		}

		$settings = ( new \PluginizeLab\StoreSuite\Analytics\Settings() )->get_settings( $needed );
		wp_add_inline_script(
			'storesuite-dashboard',
			'var storeSuiteDashboardSettings = ' . wp_json_encode( $settings ),
			'before'
		);
	}

	/**
	 * Whether at least one performance-indicator box is enabled in settings.
	 * When all are disabled there is no need to preload the indicators endpoint.
	 */
	private function any_perf_widget_enabled(): bool {
		$keys = [
			'storesuite_show_perf_revenue_total_sales',
			'storesuite_show_perf_revenue_gross_sales',
			'storesuite_show_perf_revenue_net_revenue',
			'storesuite_show_perf_orders_orders_count',
			'storesuite_show_perf_orders_avg_order_value',
			'storesuite_show_perf_products_items_sold',
			'storesuite_show_perf_variations_items_sold',
			'storesuite_show_perf_revenue_refunds',
			'storesuite_show_perf_coupons_orders_count',
			'storesuite_show_perf_coupons_amount',
			'storesuite_show_perf_taxes_total_tax',
			'storesuite_show_perf_taxes_order_tax',
			'storesuite_show_perf_taxes_shipping_tax',
			'storesuite_show_perf_revenue_shipping',
			'storesuite_show_perf_downloads_download_count',
		];
		return $this->any_widget_enabled( $keys );
	}

	private function any_leaderboard_widget_enabled(): bool {
		$keys = [
			'storesuite_show_widget_top_products_items_sold',
			'storesuite_show_widget_top_categories_items_sold',
			'storesuite_show_widget_top_customers_total_spend',
			'storesuite_show_widget_top_coupons_orders_count',
		];
		return $this->any_widget_enabled( $keys );
	}

	/**
	 * Settings store visibility flags as 'yes'/'no'; default-on when missing.
	 */
	private function any_widget_enabled( array $keys ): bool {
		foreach ( $keys as $key ) {
			$value = storesuite_get_option_by_key( $key );
			if ( '' === $value || null === $value || 'yes' === $value ) {
				return true;
			}
		}
		return false;
	}
}
