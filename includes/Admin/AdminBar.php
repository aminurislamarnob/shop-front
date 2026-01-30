<?php

namespace PluginizeLab\StoreSuite\Admin;

use WP_Admin_Bar;

/**
 * WordPress settings API For StoreSuite Admin Settings class
 *
 * @author Aminur Islam Arnob
 */
class AdminBar {

	/**
	 * Class constructor
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_before_admin_bar_render', array( $this, 'storesuite_admin_toolbar' ) );

		if ( apply_filters( 'storesuite_admin_bar_visit_dashboard', true ) ) {
			add_action( 'admin_bar_menu', array( $this, 'storesuite_dashboard_visit_menu' ), 35 );
		}
	}

	/**
	 * Add Menu in Dashboard Top bar
	 *
	 * @return void
	 */
	public function storesuite_admin_toolbar() {
		global $wp_admin_bar;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$args = array(
			'id'    => 'storesuite',
			'title' => __( 'StoreSuite', 'storesuite' ),
			'href'  => admin_url( 'admin.php?page=storesuite' ),
		);

		$wp_admin_bar->add_menu( $args );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'storesuite-general',
				'parent' => 'storesuite',
				'title'  => __( 'General', 'storesuite' ),
				'href'   => admin_url( 'admin.php?page=storesuite' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'storesuite-appearance',
				'parent' => 'storesuite',
				'title'  => __( 'Appearance', 'storesuite' ),
				'href'   => admin_url( 'admin.php?page=storesuite#/appearance-settings' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'storesuite-product',
				'parent' => 'storesuite',
				'title'  => __( 'Product', 'storesuite' ),
				'href'   => admin_url( 'admin.php?page=storesuite#/product-settings' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'storesuite-order',
				'parent' => 'storesuite',
				'title'  => __( 'Order', 'storesuite' ),
				'href'   => admin_url( 'admin.php?page=storesuite#/product-settings' ),
			)
		);

		/*
		 * Add new or remove toolbar
		 *
		 */
		do_action( 'storesuite_admin_bar_render', $wp_admin_bar );
	}

	/**
	 * Show visit store suite dashboard
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 *
	 * @return void
	 */
	public function storesuite_dashboard_visit_menu( $wp_admin_bar ) {
		if ( ! is_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$menus = $this->get_storesuite_admin_bar_menus();

		// Added admin menus for dokan in wp admin bar.
		foreach ( $menus as $menu ) {
			$wp_admin_bar->add_node( $menu );
		}
	}

	/**
	 * Get admin menus data for mystore suite.
	 *
	 * @return array
	 */
	public function get_storesuite_admin_bar_menus() {
		$menus = array();

		if ( storesuite_get_navigation_url() ) {
			$menus[] = array(
				'parent' => 'site-name',
				'id'     => 'view-storesuite-dashboard',
				'title'  => __( 'Visit StoreSuite Dashboard', 'storesuite' ),
				'href'   => storesuite_get_navigation_url(),
			);
		}

		return apply_filters( 'storesuite_admin_bar_site_menu', $menus );
	}
}
