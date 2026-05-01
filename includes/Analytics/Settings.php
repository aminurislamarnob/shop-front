<?php

namespace PluginizeLab\StoreSuite\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	/**
	 * Build the JS settings payload for the analytics React app.
	 *
	 * @return array
	 */
	public function get_settings(): array {
		$settings = [
			'stockStatuses'      => wc_get_product_stock_status_options(),
			'manageStock'        => get_option( 'woocommerce_manage_stock', 'no' ),
			'isAnalyticsEnabled' => true,
			'currentUserData'    => $this->get_current_user_data(),
			'dateFormat'         => get_option( 'date_format', 'F j, Y' ),
			'timeFormat'         => get_option( 'time_format', 'g:i a' ),
			'timezone'           => wp_timezone_string(),
		];

		$settings = array_merge( $settings, $this->load_preload_endpoints() );

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

	private function load_preload_endpoints(): array {
		try {
			$raw_endpoints = apply_filters(
				'woocommerce_component_settings_preload_endpoints',
				[
					'performanceIndicators' => '/wc-analytics/reports/performance-indicators/allowed',
					'leaderboards'          => '/wc-analytics/leaderboards/allowed',
				]
			);

			$preload_data   = array_reduce(
				array_values( $raw_endpoints ),
				'rest_preload_api_request',
				[]
			);
			$data_endpoints = [];
			foreach ( $raw_endpoints as $key => $endpoint ) {
				$data_endpoints[ $key ] = $preload_data[ $endpoint ]['body'] ?? [];
			}

			return [ 'dataEndpoints' => $data_endpoints ];
		} catch ( \Exception $e ) {
			storesuite_log( 'Analytics settings preload error: ' . $e->getMessage() );
			return [];
		}
	}
}
