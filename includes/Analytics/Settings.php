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
			'stockStatuses'      => wc_get_product_stock_status_options(),
			'manageStock'        => get_option( 'woocommerce_manage_stock', 'no' ),
			'isAnalyticsEnabled' => true,
			'currentUserData'    => $this->get_current_user_data(),
			'dateFormat'         => get_option( 'date_format', 'F j, Y' ),
			'timeFormat'         => get_option( 'time_format', 'g:i a' ),
			'timezone'           => wp_timezone_string(),
			'preloadOptions'     => $this->get_preload_options(),
		];

		$settings = array_merge( $settings, $this->load_preload_endpoints( $preload_endpoints ) );

		return $settings;
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
		$all_endpoints = apply_filters(
			'woocommerce_component_settings_preload_endpoints',
			[
				'performanceIndicators' => '/wc-analytics/reports/performance-indicators/allowed',
				'leaderboards'          => '/wc-analytics/leaderboards/allowed',
			]
		);

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
	 * Per-user, per-WC-version cache key for the REST preload payload.
	 *
	 * @return string
	 */
	private function preload_cache_key(): string {
		$wc_version = defined( 'WC_VERSION' ) ? WC_VERSION : '0';
		return 'storesuite_analytics_preload_' . get_current_user_id() . '_' . md5( $wc_version );
	}
}
