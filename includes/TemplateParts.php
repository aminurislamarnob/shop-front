<?php

namespace PluginizeLab\ShopFront;

use PluginizeLab\ShopFront\DashboardMenu as ShopFrontDashboardMenu;

class TemplateParts {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'msf_dashboard_navigation', array( $this, 'add_dashboard_sidebar_logo' ), 1 );
		add_action( 'msf_dashboard_content_before', array( $this, 'dashboard_header_template' ), 1 );
		add_action( 'msf_dashboard_before_main_content', array( $this, 'msf_page_endpoint_title' ) );
	}

	public function add_dashboard_sidebar_logo() {
		msf_get_template_part( 'dashboard-logo' );
	}

	/**
	 * Load the dashboard header template.
	 *
	 * @return void
	 */
	public function dashboard_header_template() {
		msf_get_template_part( 'dashboard-header' );
	}

	/**
	 * Replace a page title with the endpoint title.
	 *
	 * @return string
	 */
	public function msf_page_endpoint_title() {
		global $wp_query;

		$title = '';

		if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_msf_endpoint_url() ) {
			$endpoint       = pluginizelab_shop_front()->get_msf_query()->get_current_endpoint();
			$action         = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$endpoint_title = pluginizelab_shop_front()->get_msf_query()->get_endpoint_title( $endpoint, $action );
			$title          = $endpoint_title ? $endpoint_title : $title;

			$dashboard_menu = new ShopFrontDashboardMenu();

			// Customly set parent endpoint for sub pages.
			if ( 'add-new-category' === $endpoint || 'edit-category' === $endpoint ) {
				$endpoint = 'categories';
			} elseif ( 'add-new-tag' === $endpoint || 'edit-tag' === $endpoint ) {
				$endpoint = 'tags';
			} elseif ( 'add-new-brand' === $endpoint || 'edit-brand' === $endpoint ) {
				$endpoint = 'brands';
			} elseif ( 'order-details' === $endpoint ) {
				$endpoint = 'orders';
			}

			$parent_endpoint_title = $dashboard_menu->get_dashboard_menus()[ $endpoint ]['title'] ?? '';
			$parent_endpoint_url   = $dashboard_menu->get_dashboard_menus()[ $endpoint ]['url'] ?? '';
		}

		$template_args = array(
			'page_title'            => $title,
			'parent_endpoint_title' => $parent_endpoint_title,
			'parent_endpoint_url'   => $parent_endpoint_url,
		);
		msf_get_template_part( 'dashboard-title', '', $template_args );
	}
}
