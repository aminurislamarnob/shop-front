<?php

namespace PluginizeLab\ShopFront\Admin;

use WP_Admin_Bar;

/**
 * WordPress settings API For Shop Front Admin Settings class
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
		add_action( 'wp_before_admin_bar_render', array( $this, 'msf_admin_toolbar' ) );

		if ( apply_filters( 'msf_admin_bar_visit_dashboard', true ) ) {
			add_action( 'admin_bar_menu', array( $this, 'msf_dashboard_visit_menu' ), 35 );
		}
	}

	/**
	 * Add Menu in Dashboard Top bar
	 *
	 * @return void
	 */
	public function msf_admin_toolbar() {
		global $wp_admin_bar;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$args = array(
			'id'    => 'shop-front',
			'title' => __( 'Shop Front', 'shop-front' ),
			'href'  => admin_url( 'admin.php?page=shop-front' ),
		);

		$wp_admin_bar->add_menu( $args );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'shop-front-general',
				'parent' => 'shop-front',
				'title'  => __( 'General', 'shop-front' ),
				'href'   => admin_url( 'admin.php?page=shop-front' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'shop-front-appearance',
				'parent' => 'shop-front',
				'title'  => __( 'Appearance', 'shop-front' ),
				'href'   => admin_url( 'admin.php?page=shop-front#/appearance-settings' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'shop-front-product',
				'parent' => 'shop-front',
				'title'  => __( 'Product', 'shop-front' ),
				'href'   => admin_url( 'admin.php?page=shop-front#/product-settings' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'shop-front-order',
				'parent' => 'shop-front',
				'title'  => __( 'Order', 'shop-front' ),
				'href'   => admin_url( 'admin.php?page=shop-front#/product-settings' ),
			)
		);

		/*
		 * Add new or remove toolbar
		 *
		 */
		do_action( 'msf_admin_bar_render', $wp_admin_bar );
	}

	/**
	 * Show visit shop front dashboard
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 *
	 * @return void
	 */
	public function msf_dashboard_visit_menu( $wp_admin_bar ) {
		if ( ! is_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$menus = $this->get_msf_admin_bar_menus();

		// Added admin menus for dokan in wp admin bar.
		foreach ( $menus as $menu ) {
			$wp_admin_bar->add_node( $menu );
		}
	}

	/**
	 * Get admin menus data for myshop front.
	 *
	 * @return array
	 */
	public function get_msf_admin_bar_menus() {
		$menus = array();

		if ( msfc_get_navigation_url() ) {
			$menus[] = array(
				'parent' => 'site-name',
				'id'     => 'view-shop-front-dashboard',
				'title'  => __( 'Visit Shop Front Dashboard', 'shop-front' ),
				'href'   => msfc_get_navigation_url(),
			);
		}

		return apply_filters( 'msf_admin_bar_site_menu', $menus );
	}
}
