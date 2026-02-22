<?php

namespace PluginizeLab\StoreSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\StoreSuite\DashboardMenu as StoreSuiteDashboardMenu;

class TemplateParts {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'storesuite_dashboard_navigation', array( $this, 'add_dashboard_sidebar_logo' ), 1 );
		add_action( 'storesuite_dashboard_content_before', array( $this, 'dashboard_header_template' ), 1 );
		add_action( 'storesuite_dashboard_before_main_content', array( $this, 'storesuite_page_endpoint_title' ) );
	}

	public function add_dashboard_sidebar_logo() {
		storesuite_get_template_part( 'dashboard-logo' );
	}

	/**
	 * Load the dashboard header template.
	 *
	 * @return void
	 */
	public function dashboard_header_template() {
		storesuite_get_template_part( 'dashboard-header' );
	}

	/**
	 * Replace a page title with the endpoint title.
	 *
	 * @return string
	 */
	public function storesuite_page_endpoint_title() {
		global $wp_query;

		$title = '';

		if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_storesuite_endpoint_url() ) {
			$endpoint       = pluginizelab_storesuite()->get_storesuite_query()->get_current_endpoint();
			$action         = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$endpoint_title = pluginizelab_storesuite()->get_storesuite_query()->get_endpoint_title( $endpoint, $action );
			$title          = $endpoint_title ? $endpoint_title : $title;

			$dashboard_menu = new StoreSuiteDashboardMenu();

			// Customly set parent endpoint for sub pages.
			if ( 'add-new-category' === $endpoint || 'edit-category' === $endpoint ) {
				$endpoint = 'categories';
			} elseif ( 'add-new-tag' === $endpoint || 'edit-tag' === $endpoint ) {
				$endpoint = 'tags';
			} elseif ( 'add-new-brand' === $endpoint || 'edit-brand' === $endpoint ) {
				$endpoint = 'brands';
			} elseif ( 'add-new-coupon' === $endpoint || 'edit-coupon' === $endpoint ) {
				$endpoint = 'coupons';
			} elseif ( 'order-details' === $endpoint ) {
				$endpoint = 'orders';
			} elseif ( 'edit-account-details' === $endpoint ) {
				$endpoint = 'dashboard';
			}

			$parent_endpoint_title = $dashboard_menu->get_dashboard_menus()[ $endpoint ]['title'] ?? '';
			$parent_endpoint_url   = $dashboard_menu->get_dashboard_menus()[ $endpoint ]['url'] ?? '';

			$template_args = array(
				'page_title'            => $title,
				'parent_endpoint_title' => $parent_endpoint_title,
				'parent_endpoint_url'   => $parent_endpoint_url,
			);
			storesuite_get_template_part( 'dashboard-title', '', $template_args );
		}

		return $title;
	}
}
