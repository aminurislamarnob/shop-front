<?php

namespace PluginizeLab\StoreSuite\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Controller {
	public function register_hooks(): void {
		add_action( 'storesuite_load_custom_template', [ $this, 'load_template' ] );
	}

	public function load_template( array $query_vars ): void {
		$endpoint = get_option( 'storesuite_myshop_analytics_endpoint', 'analytics' );
		if ( isset( $query_vars[ $endpoint ] ) ) {
			storesuite_get_template_part( 'analytics/analytics' );
		}
	}
}
