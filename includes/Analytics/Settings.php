<?php

namespace PluginizeLab\StoreSuite\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	/**
	 * Transient TTL for the analytics preload payload (per user).
	 */
	private const PRELOAD_CACHE_TTL = 15 * MINUTE_IN_SECONDS;

	/**
	 * Build the JS settings payload for the React app.
	 *
	 * @param string[]|null $preload_endpoints Subset of preload endpoint keys to
	 *                                         hydrate (e.g. ['leaderboards']).
	 *                                         Null = all available endpoints.
	 * @return array
	 */
	public function get_settings( ?array $preload_endpoints = null ): array {
		$settings = [
			'stockStatuses'              => wc_get_product_stock_status_options(),
			'manageStock'                => get_option( 'woocommerce_manage_stock', 'no' ),
			'isAnalyticsEnabled'         => true,
			'currentUserData'            => $this->get_current_user_data(),
			'dateFormat'                 => get_option( 'date_format', 'F j, Y' ),
			'timeFormat'                 => get_option( 'time_format', 'g:i a' ),
			'timezone'                   => wp_timezone_string(),
			'orderStatuses'              => $this->get_order_statuses(),
			'unregisteredOrderStatuses'  => $this->get_unregistered_order_statuses(),
			'scheduledImport'            => $this->get_scheduled_import_config(),
			'preloadOptions'             => $this->get_preload_options(),
		];

		$settings = array_merge( $settings, $this->load_preload_endpoints( $preload_endpoints ) );

		return apply_filters( 'storesuite_analytics_settings', $settings, $preload_endpoints );
	}

	/**
	 * Registered WC order statuses keyed without the `wc-` prefix, matching the
	 * shape wc-admin exposes as `orderStatuses` (used by the Orders report
	 * advanced filter and the Analytics Settings status checkboxes).
	 *
	 * @return array<string,string>
	 */
	private function get_order_statuses(): array {
		$statuses = [];
		foreach ( wc_get_order_statuses() as $key => $label ) {
			$statuses[ preg_replace( '/^wc-/', '', $key ) ] = $label;
		}
		return $statuses;
	}

	/**
	 * Statuses referenced by the analytics status options but no longer
	 * registered (e.g. from a deactivated plugin), mirroring wc-admin's
	 * `unregisteredOrderStatuses` so saved selections stay visible/editable.
	 *
	 * @return array<string,string>
	 */
	private function get_unregistered_order_statuses(): array {
		$registered = $this->get_order_statuses();
		$saved      = array_merge(
			(array) get_option( 'woocommerce_excluded_report_order_statuses', [] ),
			(array) get_option( 'woocommerce_actionable_order_statuses', [] )
		);

		$unregistered = [];
		foreach ( $saved as $status ) {
			if ( is_string( $status ) && '' !== $status && ! isset( $registered[ $status ] ) ) {
				$unregistered[ $status ] = ucfirst( $status );
			}
		}
		return $unregistered;
	}

	/**
	 * Gating config for the "Data status" import bar, mirroring the two checks
	 * wc-admin performs before rendering it: the `analytics-scheduled-import`
	 * feature flag (which also registers the /wc-analytics/imports REST routes)
	 * and the `woocommerce_analytics_scheduled_import` option (scheduled vs.
	 * immediate mode). On the frontend `window.wcAdminFeatures` is not printed,
	 * so we resolve both server-side and inline them for the React bar.
	 *
	 * @return array{enabled:bool,mode:string}
	 */
	private function get_scheduled_import_config(): array {
		$features_class = '\Automattic\WooCommerce\Admin\Features\Features';
		$enabled        = class_exists( $features_class )
			&& $features_class::is_enabled( 'analytics-scheduled-import' );

		return [
			'enabled' => $enabled,
			'mode'    => get_option( 'woocommerce_analytics_scheduled_import', 'no' ),
		];
	}

	private function get_current_user_data(): array {
		$user = wp_get_current_user();
		return [
			'id'           => $user->ID,
			'roles'        => $user->roles,
			'display_name' => $user->display_name,
		];
	}

	/**
	 * Build the WC options payload that is hydrated into the @woocommerce/data
	 * options store via `withOptionsHydration` on the React side. Without this,
	 * the React app has to fetch each option via REST after mount.
	 *
	 * @return array<string,mixed>
	 */
	private function get_preload_options(): array {
		$options = apply_filters(
			'storesuite_analytics_preload_options',
			[
				'woocommerce_default_date_range',
				'woocommerce_excluded_report_order_statuses',
				'woocommerce_actionable_order_statuses',
				'woocommerce_date_type',
				'woocommerce_currency',
				'woocommerce_currency_pos',
				'woocommerce_price_thousand_sep',
				'woocommerce_price_decimal_sep',
				'woocommerce_price_num_decimals',
			]
		);

		$payload = [];
		foreach ( $options as $option ) {
			$payload[ $option ] = get_option( $option );
		}

		return $payload;
	}

	/**
	 * Resolve and cache the wc-analytics REST preload payload (performance
	 * indicators + leaderboards). Each endpoint is cached individually inside
	 * a single transient so cross-page visits share the cache; only missing
	 * endpoints trigger a REST preload. Cached per-user (response depends on
	 * capabilities) and busted on WC version changes.
	 *
	 * @param string[]|null $needed Endpoint keys to ensure are present. Null = all.
	 * @return array
	 */
	private function load_preload_endpoints( ?array $needed = null ): array {
		$base_endpoints = [
			'performanceIndicators' => '/wc-analytics/reports/performance-indicators/allowed',
			'leaderboards'          => '/wc-analytics/leaderboards/allowed',
		];

		// Apply Woo's filter first for compatibility with existing extensions,
		// then the StoreSuite-prefixed filter as the supported extension point.
		$all_endpoints = apply_filters( 'woocommerce_component_settings_preload_endpoints', $base_endpoints );
		$all_endpoints = apply_filters( 'storesuite_analytics_preload_endpoints', $all_endpoints );

		if ( null === $needed ) {
			$needed = array_keys( $all_endpoints );
		}

		$cache_key = $this->preload_cache_key();
		$cached    = get_transient( $cache_key );
		if ( ! is_array( $cached ) ) {
			$cached = [];
		}

		$missing = array_diff( $needed, array_keys( $cached ) );
		if ( ! empty( $missing ) ) {
			try {
				$endpoints_to_preload = array_intersect_key( $all_endpoints, array_flip( $missing ) );
				$preload_data         = array_reduce(
					array_values( $endpoints_to_preload ),
					'rest_preload_api_request',
					[]
				);

				foreach ( $endpoints_to_preload as $key => $endpoint ) {
					$cached[ $key ] = $preload_data[ $endpoint ]['body'] ?? [];
				}

				set_transient( $cache_key, $cached, self::PRELOAD_CACHE_TTL );
			} catch ( \Exception $e ) {
				storesuite_log( 'Analytics settings preload error: ' . $e->getMessage() );
			}
		}

		$data_endpoints = array_intersect_key( $cached, array_flip( $needed ) );

		return [ 'dataEndpoints' => $data_endpoints ];
	}

	/**
	 * Capability-set + WC-version cache key for the REST preload payload.
	 *
	 * Keying by the user's effective capabilities (rather than user ID) means
	 * all manage_woocommerce users share one transient instead of one per
	 * user — storage stays proportional to distinct role sets, not user count.
	 * Plugin version is mixed in so a release ships fresh data on update.
	 *
	 * @return string
	 */
	private function preload_cache_key(): string {
		$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : '0';
		$user       = wp_get_current_user();
		$caps       = is_object( $user ) && ! empty( $user->allcaps ) ? $user->allcaps : [];
		ksort( $caps );
		$caps_hash = md5( wp_json_encode( $caps ) );
		return 'storesuite_analytics_preload_' . md5( $wc_version . '|' . STORESUITE_PLUGIN_VERSION . '|' . $caps_hash );
	}

	/**
	 * Bust cached preload payloads when a user's role changes. Hooked into
	 * set_user_role / add_user_role / remove_user_role at registration time.
	 *
	 * The capability hash composition above means most invalidations happen
	 * naturally on the next request — but we also delete here so a downgraded
	 * user cannot read a stale payload that was cached against the old role.
	 *
	 * @param int $user_id Affected user ID.
	 */
	public static function invalidate_user_cache( $user_id ): void {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return;
		}
		$caps = ! empty( $user->allcaps ) ? $user->allcaps : [];
		ksort( $caps );
		$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : '0';
		$key        = 'storesuite_analytics_preload_' . md5( $wc_version . '|' . STORESUITE_PLUGIN_VERSION . '|' . md5( wp_json_encode( $caps ) ) );
		delete_transient( $key );
	}
}
