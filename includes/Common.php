<?php

namespace PluginizeLab\ShopFront;

class Common {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_filter( 'the_title', array( $this, 'msf_page_endpoint_title' ) );
	}

	/**
	 * Replace a page title with the endpoint title.
	 *
	 * @param  string $title Post title.
	 * @return string
	 */
	public function msf_page_endpoint_title( $title ) {
		global $wp_query;

		if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_msf_endpoint_url() ) {
			$endpoint       = pluginizelab_shop_front()->get_msf_query()->get_current_endpoint();
			$action         = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$endpoint_title = pluginizelab_shop_front()->get_msf_query()->get_endpoint_title( $endpoint, $action );
			$title          = $endpoint_title ? $endpoint_title : $title;

			remove_filter( 'the_title', array( $this, 'msf_page_endpoint_title' ) );
		}

		return $title;
	}
}
