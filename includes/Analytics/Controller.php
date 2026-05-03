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
		if ( ! isset( $query_vars[ $endpoint ] ) ) {
			return;
		}

		// Block non-admins from receiving the analytics React mount or the
		// inlined preload globals. Anonymous traffic is already redirected by
		// the upstream dashboard access guard.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_safe_redirect( storesuite_get_navigation_url() );
			exit;
		}

		storesuite_get_template_part( 'analytics/analytics' );
	}
}
